<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Table;

use WPDataTable;
use WPExcelDataTable;
use WDTException;

/**
 * Server-side data builder — the shared core of the DataTables server-side
 * response.
 *
 * Holds the body of the `wdtGetAjaxData` handler (now
 * {@see \WPDataTables\Controllers\Frontend\DataController::getAjaxData}) from the
 * table/column load through the engine construction: it assembles the per-column
 * options, configures the runtime {@see WPDataTable} for server-side processing,
 * and runs `queryBasedConstruct()` (→ {@see TableService} →
 * {@see ServerSideProcessor} → {@see \WPDataTables\Services\DataSource\MySqlQueryDataSource}).
 *
 * It is the convergence point for server-side data: the admin-ajax hot path and
 * the REST `GET /tables/{id}/data?server_side=1` endpoint both call
 * `buildResponse()`, so they share one engine path. The method reads the
 * DataTables request from the superglobals (`$_GET`/`$_POST`) — callers populate
 * those (the ajax handler from the live request; the REST controller maps its
 * query params onto them).
 *
 * The `wpdatatables_filter_server_side_data` filter and the echo/exit are NOT
 * here — they stay with each caller, which differ in how they emit the result
 * (the ajax handler echoes + exits; REST returns a `WP_REST_Response`).
 *
 * @package WPDataTables\Services\Table
 */
class ServerSideDataService
{
    /** @var TableConfigService */
    private $tableConfigService;

    public function __construct(TableConfigService $tableConfigService)
    {
        $this->tableConfigService = $tableConfigService;
    }

    /**
     * Build the server-side response for a table from the current request.
     *
     * @param int $id The wpDataTables table id.
     *
     * @return array{type: string, json?: string}
     *               `['type' => 'json', 'json' => <engine JSON string>]` for
     *               mysql/manual tables; `['type' => 'addon']` when an addon
     *               table type handled (and already echoed) the response.
     * @throws WDTException When the table type is unknown / its addon is inactive.
     * @throws \Exception
     */
    public function buildResponse(int $id): array
    {
        global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9;

        $tableData = $this->tableConfigService->loadTableFromDB($id);
        $columnData = $this->tableConfigService->loadColumnsFromDB($id);

        $avgColumns = array();
        $columnEditorTypes = array();
        $columnFilterTypes = array();
        $columnFormulas = array();
        $columnOrder = array();
        $columnTitles = array();
        $columnTypes = array();
        $decimalPlaces = array();
        $exactFiltering = array();
        $globalSearchColumn = array();
        $searchInSelectBox = array();
        $searchInSelectBoxEditing = array();
        $andLogic = array();
        $foreignKeyRule = array();
        $idColumn = '';
        $linkTargetAttribute = array();
        $linkNoFollowAttribute = array();
        $linkNoreferrerAttribute = array();
        $linkSponsoredAttribute = array();
        $linkButtonAttribute = array();
        $linkButtonLabel = array();
        $linkButtonClass = array();
        $maxColumns = array();
        $minColumns = array();
        $possibleValuesAddEmpty = array();
        $skipThousands = array();
        $sumColumns = array();
        $userIdColumnHeader = '';
        $filterDefaultValue = array();
        $editingDefaultValue = array();
        $rangeSlider = array();
        $rangeMaxValueDisplay = array();
        $customMaxRangeValue = array();

        $wdtVar1 = isset($_GET['wdt_var1']) ? wdtSanitizeSqlPlaceholderValue($_GET['wdt_var1']) : $tableData->var1;
        $wdtVar2 = isset($_GET['wdt_var2']) ? wdtSanitizeSqlPlaceholderValue($_GET['wdt_var2']) : $tableData->var2;
        $wdtVar3 = isset($_GET['wdt_var3']) ? wdtSanitizeSqlPlaceholderValue($_GET['wdt_var3']) : $tableData->var3;
        $wdtVar4 = isset($_GET['wdt_var4']) ? wdtSanitizeSqlPlaceholderValue($_GET['wdt_var4']) : $tableData->var4;
        $wdtVar5 = isset($_GET['wdt_var5']) ? wdtSanitizeSqlPlaceholderValue($_GET['wdt_var5']) : $tableData->var5;
        $wdtVar6 = isset($_GET['wdt_var6']) ? wdtSanitizeSqlPlaceholderValue($_GET['wdt_var6']) : $tableData->var6;
        $wdtVar7 = isset($_GET['wdt_var7']) ? wdtSanitizeSqlPlaceholderValue($_GET['wdt_var7']) : $tableData->var7;
        $wdtVar8 = isset($_GET['wdt_var8']) ? wdtSanitizeSqlPlaceholderValue($_GET['wdt_var8']) : $tableData->var8;
        $wdtVar9 = isset($_GET['wdt_var9']) ? wdtSanitizeSqlPlaceholderValue($_GET['wdt_var9']) : $tableData->var9;

        $tableView = isset($_POST['table']) ? sanitize_text_field($_POST['table']) : '';

        foreach ($columnData as $column) {
            $advancedSettings = json_decode($column->advanced_settings);

            if (isset($advancedSettings->possibleValuesType) && $advancedSettings->possibleValuesType == 'foreignkey') {
                $advancedSettings->possibleValuesAjax = -1;
            }

            $columnOrder[(int)$column->pos] = $column->orig_header;
            if ($column->display_header != '') {
                $columnTitles[$column->orig_header] = $column->display_header;
            } else {
                $columnTitles[$column->orig_header] = $column->orig_header;
            }
            if ($column->column_type != 'autodetect') {
                $columnTypes[$column->orig_header] = $column->column_type;
                if ($column->column_type == 'formula') {
                    $columnFormulas[$column->orig_header] = $column->calc_formula;
                }
                if ($column->column_type == 'int' && $column->skip_thousands_separator) {
                    $skipThousands[] = $column->orig_header;
                }
            } else {
                $columnTypes[$column->orig_header] = 'string';
            }
            if ($column->id_column) {
                $idColumn = $column->orig_header;
            }
            $columnFilterTypes[$column->orig_header] = $column->filter_type;
            $columnEditorTypes[$column->orig_header] = $column->input_type;
            if ($tableData->edit_only_own_rows
                && ($tableData->userid_column_id == $column->id)
            ) {
                $userIdColumnHeader = $column->orig_header;
            }
            if ($column->sum_column) {
                $sumColumns[] = $column->orig_header;
            }

            if (isset($advancedSettings->calculateAvg) && $advancedSettings->calculateAvg == 1) {
                $avgColumns[] = $column->orig_header;
            }
            if (isset($advancedSettings->calculateMin) && $advancedSettings->calculateMin == 1) {
                $minColumns[] = $column->orig_header;
            }
            if (isset($advancedSettings->calculateMax) && $advancedSettings->calculateMax == 1) {
                $maxColumns[] = $column->orig_header;
            }

            if (isset($column->default_value)) {
                if (isset($_GET['wdt_column_filter']) && is_array($_GET['wdt_column_filter'])) {
                    foreach ($_GET['wdt_column_filter'] as $fltColKey => $fltDefVal) {
                        if (!is_scalar($fltColKey) || !is_scalar($fltDefVal)) {
                            continue;
                        }

                        $fltColKeyRaw = wp_unslash($fltColKey);
                        $fltDefVal = sanitize_text_field(wp_unslash($fltDefVal));

                        if (is_numeric($fltColKeyRaw)) {
                            if (intval($column->pos) === intval($fltColKeyRaw)) {
                                $column->default_value = $fltDefVal;
                            }
                        } elseif ($column->orig_header === sanitize_text_field($fltColKeyRaw)) {
                            $column->default_value = $fltDefVal;
                        }
                    }
                }
                $column->default_value = apply_filters_deprecated(
                    'wpdt_filter_filtering_default_value',
                    array($column->default_value, $column->orig_header, $column->table_id),
                    WDT_INITIAL_STARTER_VERSION,
                    'wpdatatables_filter_filtering_default_value');
                $column->default_value = apply_filters('wpdatatables_filter_filtering_default_value', $column->default_value, $column->orig_header, $column->table_id);

                $filterDefaultValue[] = $column->default_value;
            }

            $decimalPlaces[$column->orig_header] = $advancedSettings->decimalPlaces ?? null;
            $exactFiltering[$column->orig_header] = $advancedSettings->exactFiltering ?? null;
            $globalSearchColumn[$column->orig_header] = $advancedSettings->globalSearchColumn ?? null;
            $searchInSelectBox[$column->orig_header] = $advancedSettings->searchInSelectBox ?? null;
            $searchInSelectBoxEditing[$column->orig_header] = $advancedSettings->searchInSelectBoxEditing ?? null;
            $andLogic[$column->orig_header] = $advancedSettings->andLogic ?? null;
            $linkTargetAttribute[$column->orig_header] = $advancedSettings->linkTargetAttribute ?? null;
            $linkNoFollowAttribute[$column->orig_header] = $advancedSettings->linkNoFollowAttribute ?? null;
            $linkNoreferrerAttribute[$column->orig_header] = $advancedSettings->linkNoreferrerAttribute ?? null;
            $linkSponsoredAttribute[$column->orig_header] = $advancedSettings->linkSponsoredAttribute ?? null;
            $linkButtonAttribute[$column->orig_header] = $advancedSettings->linkButtonAttribute ?? null;
            $linkButtonLabel[$column->orig_header] = $advancedSettings->linkButtonLabel ?? null;
            $linkButtonClass[$column->orig_header] = $advancedSettings->linkButtonClass ?? null;
            $possibleValuesAddEmpty[$column->orig_header] = $advancedSettings->possibleValuesAddEmpty ?? null;
            $rangeSlider[$column->orig_header] = $advancedSettings->rangeSlider ?? null;
            $rangeMaxValueDisplay[$column->orig_header] = $advancedSettings->rangeMaxValueDisplay ?? null;
            $customMaxRangeValue[$column->orig_header] = $advancedSettings->customMaxRangeValue ?? null;
            $foreignKeyRule[$column->orig_header] = $advancedSettings->foreignKeyRule ?? null;
            $editingDefaultValue[$column->orig_header] = $advancedSettings->editingDefaultValue ?? '';
        }

        if ($tableView == 'excel') {
            $tbl = new WPExcelDataTable($tableData->connection);
        } else {
            $tbl = new WPDataTable($tableData->connection);
        }

        $tbl->setSumFooterColumns($sumColumns);
        $tbl->setAvgFooterColumns($avgColumns);
        $tbl->setMinFooterColumns($minColumns);
        $tbl->setMaxFooterColumns($maxColumns);

        $validColumnHeaders = array_map('strval', array_keys($columnTitles));

        if (isset($_POST['sumColumns']) && is_array($_POST['sumColumns'])) {
            foreach ($_POST['sumColumns'] as $sumColumnHeader) {
                if (!is_scalar($sumColumnHeader)) {
                    continue;
                }
                $sumColumnHeader = sanitize_text_field(wp_unslash($sumColumnHeader));
                if (in_array($sumColumnHeader, $validColumnHeaders, true) && !in_array($sumColumnHeader, $sumColumns, true)) {
                    $sumColumns[] = $sumColumnHeader;
                }
            }
        }

        if (isset($_POST['avgColumns']) && is_array($_POST['avgColumns'])) {
            foreach ($_POST['avgColumns'] as $avgColumnHeader) {
                if (!is_scalar($avgColumnHeader)) {
                    continue;
                }
                $avgColumnHeader = sanitize_text_field(wp_unslash($avgColumnHeader));
                if (in_array($avgColumnHeader, $validColumnHeaders, true) && !in_array($avgColumnHeader, $avgColumns, true)) {
                    $avgColumns[] = $avgColumnHeader;
                }
            }
        }

        if (isset($_POST['minColumns']) && is_array($_POST['minColumns'])) {
            foreach ($_POST['minColumns'] as $minColumnHeader) {
                if (!is_scalar($minColumnHeader)) {
                    continue;
                }
                $minColumnHeader = sanitize_text_field(wp_unslash($minColumnHeader));
                if (in_array($minColumnHeader, $validColumnHeaders, true) && !in_array($minColumnHeader, $minColumns, true)) {
                    $minColumns[] = $minColumnHeader;
                }
            }
        }

        if (isset($_POST['maxColumns']) && is_array($_POST['maxColumns'])) {
            foreach ($_POST['maxColumns'] as $maxColumnHeader) {
                if (!is_scalar($maxColumnHeader)) {
                    continue;
                }
                $maxColumnHeader = sanitize_text_field(wp_unslash($maxColumnHeader));
                if (in_array($maxColumnHeader, $validColumnHeaders, true) && !in_array($maxColumnHeader, $maxColumns, true)) {
                    $maxColumns[] = $maxColumnHeader;
                }
            }
        }
        $tbl->setWpId($id);
        $tbl->setTableType($tableData->table_type);
        $tbl->setTableContent($tableData->content);
        $tbl->enableServerProcessing();
        if ($tableData->edit_only_own_rows) {
            if ($userIdColumnHeader === '') {
                throw new WDTException(
                    __('Users see and edit only their own data is enabled, but a User ID column is not configured.', 'wpdatatables')
                );
            }
            $tbl->setOnlyOwnRows(true);
            $tbl->setUserIdColumn($userIdColumnHeader);
            $tbl->setShowAllRows($tableData->showAllRows);
        }
        $tbl->setSumColumns($sumColumns);
        $tbl->setAvgColumns($avgColumns);
        $tbl->setMinColumns($minColumns);
        $tbl->setMaxColumns($maxColumns);
        $tbl->setAjaxReturn(true);

        $columnOptions = array(
            'columnFormulas' => $columnFormulas,
            'columnOrder' => $columnOrder,
            'columnTitles' => $columnTitles,
            'data_types' => $columnTypes,
            'decimalPlaces' => $decimalPlaces,
            'exactFiltering' => $exactFiltering,
            'globalSearchColumn' => $globalSearchColumn,
            'searchInSelectBox' => $searchInSelectBox,
            'searchInSelectBoxEditing' => $searchInSelectBoxEditing,
            'andLogic' => $andLogic,
            'filterTypes' => $columnFilterTypes,
            'foreignKeyRule' => $foreignKeyRule,
            'idColumn' => $idColumn,
            'input_types' => $columnEditorTypes,
            'linkTargetAttribute' => $linkTargetAttribute,
            'linkNoFollowAttribute' => $linkNoFollowAttribute,
            'linkNoreferrerAttribute' => $linkNoreferrerAttribute,
            'linkSponsoredAttribute' => $linkSponsoredAttribute,
            'linkButtonAttribute' => $linkButtonAttribute,
            'linkButtonLabel' => $linkButtonLabel,
            'linkButtonClass' => $linkButtonClass,
            'skip_thousands' => $skipThousands,
            'rangeSlider' => $rangeSlider,
            'rangeMaxValueDisplay' => $rangeMaxValueDisplay,
            'customMaxRangeValue' => $customMaxRangeValue,
            'filterDefaultValue' => $filterDefaultValue,
            'editingDefaultValue' => $editingDefaultValue
        );

        $columnOptions = apply_filters_deprecated(
            'wpdt_filter_column_options',
            array($columnOptions, $columnData, $tbl),
            WDT_INITIAL_STARTER_VERSION,
            'wpdatatables_filter_column_options'
        );
        $columnOptions = apply_filters('wpdatatables_filter_column_options', $columnOptions, $columnData, $tbl);

        if (in_array($tbl->getTableType(), ['mysql', 'manual'], true)) {
            $json = $tbl->queryBasedConstruct(
                $tbl->getTableContent(),
                array(),
                $columnOptions
            );

            return ['type' => 'json', 'json' => $json];
        } else {
            if (has_action('wpdatatables_generate_' . $tableData->table_type)) {
                do_action(
                    'wpdatatables_generate_' . $tableData->table_type,
                    $tbl,
                    $tbl->getTableContent(),
                    $columnOptions
                );

                return ['type' => 'addon'];
            } else {
                throw new WDTException(__('You are trying to load a table of an unknown type. Probably you did not activate the addon which is required to use this table type.', 'wpdatatables'));
            }
        }
    }
}
