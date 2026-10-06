<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin;

use Connection;
use Exception;
use WDTConfigController;
use WDTPermissionsEnforcer;
use WPDataTables\Services\Tools\ToolsService;
use WPDataChart;
use WPDataTable;
use WPDataTableRows;
use WPDataTables_Fusion_Elements;
use WPDataTables\Services\Table\TableLoadService;

/**
 * ShortcodeController — the single owner of the wpDataTables shortcode handlers.
 *
 * @package WPDataTables\Plugin
 */
class ShortcodeController
{
    /** @var TableLoadService */
    private $tableLoadService;

    public function __construct(TableLoadService $tableLoadService)
    {
        $this->tableLoadService = $tableLoadService;
    }

    /**
     * Handler for the chart shortcode ([wpdatachart]).
     *
     * @param array $atts
     * @param string|null $content
     *
     * @return bool|string
     * @throws Exception
     */
    public function renderChart($atts, $content = null)
{
    extract(shortcode_atts(array(
        'id' => '0'
    ), $atts));

    $id = absint($id);

    if (is_admin() && defined('AVADA_VERSION') && is_plugin_active('fusion-builder/fusion-builder.php') &&
        class_exists('Fusion_Element') && class_exists('WPDataTables_Fusion_Elements') &&
        isset($_POST['action']) && $_POST['action'] === 'get_shortcode_render') {
        return WPDataTables_Fusion_Elements::get_content_for_avada_live_builder($atts, 'chart');
    }

    /** @var mixed $id */
    if (!$id) {
        return false;
    }

    // Check permissions - user must have access to view this chart
    if (!WDTPermissionsEnforcer::canUserViewChart($id)) {
        return esc_html__('You do not have permission to view this chart.', 'wpdatatables');
    }

    try {
        $dbChartData = WPDataChart::getChartDataById($id);
        if (!$dbChartData) {
            return esc_html__('wpDataChart with provided ID not found!', 'wpdatatables');
        }
        $chartData = [
            'id' => $id,
            'engine' => $dbChartData->engine
        ];
        $wpDataChart = WPDataChart::build($chartData, true);
        $chartExists = $wpDataChart->getwpDataTableId();
        if (empty($chartExists)) {
            return esc_html__('wpDataChart with provided ID not found!', 'wpdatatables');
        }

        do_action('wpdatatables_before_render_chart', $wpDataChart->getId());

        return $wpDataChart->render();
    } catch (Exception $e) {
        return esc_html__('There was an issue displaying the chart. Please edit the chart in the admin area for more details.');
    }
}

    /**
     * Handler for the table cell shortcode ([wpdatatable_cell]).
     *
     * @param array $atts
     * @param string|null $content
     *
     * @return mixed|string
     * @throws Exception
     */
    public function renderCell($atts, $content = null)
{
    global $wpdb;
    extract(shortcode_atts(array(
        'table_id' => '0',
        'row_id' => '0',
        'column_key' => '%%no_val%%',
        'column_id' => '%%no_val%%',
        'column_id_value' => '%%no_val%%',
        'sort' => '1'
    ), $atts));

    $table_id = absint($table_id);
    $row_id = absint($row_id);
    $sort = absint($sort);

    /**
     * Protection
     * @var int $table_id
     */
    if (!$table_id)
        return esc_html__('wpDataTable with provided ID not found!', 'wpdatatables');

    /** @var int $row_id */
    $rowID = !$row_id ? 0 : $row_id;

    /** @var int $sort */
    $includeSort = $sort == 1;

    /** @var mixed $column_key */
    $columnKey = $column_key !== '%%no_val%%' ? $column_key : '';

    /** @var mixed $column_id */
    $columnID = $column_id !== '%%no_val%%' ? $column_id : '';

    /** @var mixed $column_id_value */
    if ($column_id_value !== '%%no_val%%') {
        if ($column_id_value === '%CURRENT_USER_ID%') {
            $columnIDValue = get_current_user_id();
        } else {
            $columnIDValue = $column_id_value;
        }
    } else {
        $columnIDValue = '';
    }

    $rowID = apply_filters('wpdatatables_cell_filter_row_id', $rowID, $columnKey, $columnID, $columnIDValue, $table_id);
    $columnKey = apply_filters('wpdatatables_cell_filter_column_key', $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
    $columnID = apply_filters('wpdatatables_cell_filter_column_id', $columnID, $columnKey, $rowID, $columnIDValue, $table_id);
    $columnIDValue = apply_filters('wpdatatables_cell_filter_column_id_value', $columnIDValue, $columnKey, $rowID, $columnID, $table_id);

    if ($columnKey == '')
        return esc_html__('Column key for provided table ID not found!', 'wpdatatables');

    $tableData = WDTConfigController::loadTableFromDB($table_id, false);

    if (empty($tableData->content))
        return esc_html__('wpDataTable with provided ID not found!', 'wpdatatables');

    if ($tableData->table_type === 'simple') {

        if ($columnIDValue != '' || $columnID != '')
            return esc_html__('For getting cell value from simple table, column_id and column_id_value are not supported. Please use row_id.', 'wpdatatables');

        if ($rowID == 0)
            return esc_html__('Row ID for provided table ID not found!', 'wpdatatables');

        try {
            $wpDataTableRows = WPDataTableRows::loadWpDataTableRows($table_id);
            $rowsData = $wpDataTableRows->getRowsData();
            $columnHeaders = array_flip($wpDataTableRows->getColHeaders());
            $columnKey = strtoupper($columnKey);
            if (isset($columnHeaders[$columnKey])) {
                $rowID = $rowID - 1;
                if (isset($rowsData[$rowID])) {
                    $columnKey = $columnHeaders[$columnKey];
                    $cellMetaClasses = array_unique($wpDataTableRows->getCellClassesByIndexes($rowsData, $rowID, $columnKey));
                    $cellValue = $wpDataTableRows->getCellDataByIndexes($rowsData, $rowID, $columnKey);
                    $cellValue = apply_filters('wpdatatables_cell_value_filter', $cellValue, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
                    $cellValueOutput = WPDataTableRows::prepareCellDataOutput($cellValue, $cellMetaClasses, $rowID, $columnKey, $table_id);
                } else {
                    return esc_html__('Row ID for provided table ID not found!', 'wpdatatables');
                }
            } else {
                return esc_html__('Column key for provided table ID not found!', 'wpdatatables');
            }
        } catch (Exception $e) {
            return ltrim($e->getMessage(), '<br/><br/>');
        }
    } else {
        try {
            $wpDataTable = $this->tableLoadService->loadTable($table_id);

            if (!isset($wpDataTable->getWdtColumnTypes()[$columnKey]))
                return esc_html__('Column key for provided table ID not found!', 'wpdatatables');

            if ($columnIDValue != '' || $columnID != '') {
                if ($columnID == '')
                    return esc_html__('Column ID for provided table ID not found!', 'wpdatatables');

                if ($columnIDValue == '')
                    return esc_html__('Column ID value for provided table ID not found!', 'wpdatatables');

                if (!isset($wpDataTable->getWdtColumnTypes()[$columnID]))
                    return esc_html__('Column ID for provided table ID not found!', 'wpdatatables');

                if (in_array($wpDataTable->getWdtColumnTypes()[$columnID], ['date',
                    'datetime',
                    'time',
                    'float',
                    'formula',
                    'select',
                    'index']))
                    return esc_html__('At the moment float, formula, date, datetime and time columns can not be used as column_id. Please use other column that contains unique identifiers.', 'wpdatatables');

                if ($columnKey == $columnID)
                    return esc_html__('Column Key an Column ID can not be the same!', 'wpdatatables');
            }

            $isTableSortable = $wpDataTable->sortEnabled();
            $doSort = false;
            if ($includeSort && $isTableSortable) {
                $doSort = true;
                $sortDirection = $wpDataTable->getDefaultSortDirection();
                if ($wpDataTable->getDefaultSortColumn()) {
                    $sortColumn = $wpDataTable->getColumns()[$wpDataTable->getDefaultSortColumn()]->getOriginalHeader();
                    $columnType = $wpDataTable->getColumns()[$wpDataTable->getDefaultSortColumn()]->getDataType();
                } else {
                    $sortColumn = $wpDataTable->getColumns()[0]->getOriginalheader();
                    $columnType = $wpDataTable->getColumns()[0]->getDataType();
                }
            }

            $isFormulaColumnKey = false;
            if (isset($wpDataTable->getWdtColumnTypes()[$columnKey])
                && $wpDataTable->getWdtColumnTypes()[$columnKey] == 'formula') {
                $isFormulaColumnKey = true;
                $formulaColumnKey = $wpDataTable->getColumn($column_key)->getFormula();
                $headersInFormulaColumnKey = $wpDataTable->detectHeadersInFormula($formulaColumnKey);
                $headersColumnKey = ToolsService::sanitizeHeaders($headersInFormulaColumnKey);
            }
            $isForeignColumnKey = false;
            if (isset($wpDataTable->getWdtColumnTypes()[$columnKey])
                && $wpDataTable->getColumn($columnKey)
                && $wpDataTable->getColumn($columnKey)->getForeignKeyRule()) {
                $isForeignColumnKey = true;
                $foreignKeyRuleForColumnKey = $wpDataTable->getColumn($columnKey)->getForeignKeyRule();
                $joinedTableForColumnKey = $this->tableLoadService->loadTable($foreignKeyRuleForColumnKey->tableId);
                $joinedTableContentForColumnKey = ToolsService::applyPlaceholders($joinedTableForColumnKey->getTableContent());
                $storeColumnForColumnKey = WDTConfigController::loadSingleColumnFromDB($foreignKeyRuleForColumnKey->storeColumnId);
                $displayColumnForColumnKey = WDTConfigController::loadSingleColumnFromDB($foreignKeyRuleForColumnKey->displayColumnId);
            }

            if (in_array($wpDataTable->getTableType(), ['manual', 'mysql'])) {
                $contentQuery = ToolsService::applyPlaceholders($wpDataTable->getTableContent());
                $tableDbName = (isset($tableData->mysql_table_name) && $tableData->mysql_table_name != '') ? $tableData->mysql_table_name : '';

                $vendor = Connection::getVendor($tableData->connection);
                $isMySql = $vendor === Connection::$MYSQL;
                $isMSSql = $vendor === Connection::$MSSQL;
                $isPostgreSql = $vendor === Connection::$POSTGRESQL;

                $leftSysIdentifier = Connection::getLeftColumnQuote($vendor);
                $rightSysIdentifier = Connection::getRightColumnQuote($vendor);

                if ($tableData->table_type == 'manual') {
                    $customQuery = "SELECT *,";
                    if ($isFormulaColumnKey) {
                        $formulaColumnKey = formulaFormat($headersInFormulaColumnKey, $formulaColumnKey, $tableDbName, $leftSysIdentifier, $rightSysIdentifier);
                        $customQuery .= "(" . $formulaColumnKey . ") as " . $columnKey . " FROM " . $tableDbName;
                    } else {
                        $customQuery = "SELECT "
                            . $tableDbName . '.'
                            . $leftSysIdentifier . $columnKey . $rightSysIdentifier
                            . ' FROM '
                            . $tableDbName;
                    }
                } else {
                    $customQuery = "SELECT *,";
                    if ($isFormulaColumnKey) {
                        $formulaColumnKey = formulaFormat($headersInFormulaColumnKey, $formulaColumnKey, 'wdt', $leftSysIdentifier, $rightSysIdentifier);
                        $customQuery .= "(" . $formulaColumnKey . ") as " . $columnKey . " FROM (" . $contentQuery . ") as wdt ";
                    } else {
                        $customQuery = "SELECT wdt."
                            . $leftSysIdentifier . $columnKey . $rightSysIdentifier
                            . " FROM (" . $contentQuery . ") as wdt ";
                    }
                }

                $tableDbNameBasedOnType = $tableData->table_type == 'manual' ? $tableDbName : 'wdt';

                if ($doSort && $isFormulaColumnKey && !$isMSSql)
                    $customQuery .= ' ORDER BY ' . $tableDbNameBasedOnType . '.' . $leftSysIdentifier . $sortColumn . $rightSysIdentifier . " " . $sortDirection . " ";

                if ($columnIDValue != '' || $columnID != '') {
                    $customColumnID = true;
                    $columnIDType = $wpDataTable->getWdtColumnTypes()[$columnID];

                    if ($columnIDType == 'int') {
                        if (get_option('wdtNumberFormat') == 1) {
                            $columnIDValue = str_replace(',', '.', str_replace('.', '', $columnIDValue));
                        } else {
                            $columnIDValue = str_replace(',', '', $columnIDValue);
                        }
                    } else {
                        $columnIDValue = addslashes($columnIDValue);
                    }

                    if ($isFormulaColumnKey) {
                        $customQuery = " SELECT wdt."
                            . $leftSysIdentifier . $columnKey . $rightSysIdentifier
                            . " FROM (" . $customQuery . ") as wdt"
                            . " WHERE wdt."
                            . $leftSysIdentifier . $columnID . $rightSysIdentifier
                            . "='" . $columnIDValue . "' ";
                        if ($doSort && $isMSSql) {
                            $customQuery .= ' ORDER BY '
                                . 'wdt.' . $leftSysIdentifier . $sortColumn . $rightSysIdentifier
                                . " " . $sortDirection . " ";
                        }
                    } else {
                        $customQuery .= " WHERE "
                            . $tableDbNameBasedOnType . "."
                            . $leftSysIdentifier . $columnID . $rightSysIdentifier
                            . "='" . $columnIDValue . "' ";
                        if ($doSort && $isMSSql && $isForeignColumnKey) {
                            $customQuery .= '';
                        } else if ($doSort) {
                            $customQuery .= ' ORDER BY '
                                . $tableDbNameBasedOnType . '.'
                                . $leftSysIdentifier . $sortColumn . $rightSysIdentifier
                                . " " . $sortDirection . " ";
                        }
                    }
                } else {
                    $customColumnID = false;
                    if ($rowID != 0) {
                        if ($isFormulaColumnKey) {
                            $customQuery = " SELECT wdt."
                                . $leftSysIdentifier . $columnKey . $rightSysIdentifier
                                . " FROM (" . $customQuery . ") as wdt"
                                . " WHERE wdt."
                                . $leftSysIdentifier;
                            if ($wpDataTable->getIdColumnKey() != '') {
                                $customQuery .= $wpDataTable->getIdColumnKey();
                            } else {
                                $customQuery .= $wpDataTable->getColumns()[0]->getOriginalheader();
                            }
                            $customQuery .= $rightSysIdentifier . "='" . $rowID . "'";
                            if ($doSort && $isMSSql) {
                                $customQuery .= ' ORDER BY '
                                    . 'wdt.' . $leftSysIdentifier . $sortColumn . $rightSysIdentifier
                                    . " " . $sortDirection . " ";
                            }
                        } else {
                            $customQuery .= " WHERE "
                                . $tableDbNameBasedOnType . "."
                                . $leftSysIdentifier;
                            if ($wpDataTable->getIdColumnKey() != '') {
                                $customQuery .= $wpDataTable->getIdColumnKey();
                            } else {
                                $customQuery .= $wpDataTable->getColumns()[0]->getOriginalheader();
                            }
                            $customQuery .= $rightSysIdentifier . "='" . $rowID . "'";
                            if ($doSort && $isMSSql && $isForeignColumnKey) {
                                $customQuery .= '';
                            } else if ($doSort) {
                                $customQuery .= ' ORDER BY ' . $tableDbNameBasedOnType . '.'
                                    . $leftSysIdentifier . $sortColumn . $rightSysIdentifier
                                    . " " . $sortDirection . " ";
                            }

                        }

                    } else {
                        return esc_html__('Row ID for provided table ID not found!', 'wpdatatables');
                    }
                }

                if ($isForeignColumnKey) {
                    $adoptCustomQuery = ($isPostgreSql) ? "(" . $customQuery . ")::INTEGER" : "(" . $customQuery . ")";
                    $customQuery = "SELECT "
                        . 'wdtForeign.'
                        . $leftSysIdentifier . $displayColumnForColumnKey['orig_header'] . $rightSysIdentifier
                        . ' FROM ('
                        . $joinedTableContentForColumnKey . ') as wdtForeign '
                        . "  WHERE wdtForeign." . $leftSysIdentifier . $storeColumnForColumnKey['orig_header'] . $rightSysIdentifier . "=" . $adoptCustomQuery;
                }

                $customQuery = wdtSanitizeQuery($customQuery);

                if (Connection::isSeparate($tableData->connection)) {
                    $sql = Connection::getInstance($tableData->connection);
                    if ($customColumnID) {

                        if ($isMySql || $isPostgreSql) {
                            $customQuery .= " LIMIT 1";
                        }

                        if ($isMSSql) {
                            if ($doSort && !$isForeignColumnKey) {
                                $customQuery .= " OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY";
                            } else {
                                $customQuery .= " ORDER BY(SELECT NULL) OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY";
                            }

                        }

                    }
                    $customQuery = apply_filters('wpdatatables_cell_filter_query', $customQuery, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
                    $cellValue = $sql->getField($customQuery);

                    if ($sql->getLastError() != '') {
                        return esc_html__('There was an error when trying to get cell value.', 'wpdatatables') . ' ' . ((current_user_can('administrator')) ? $sql->getLastError() : ' Please contact the administrator.');
                    }
                } else {
                    $customQuery = apply_filters('wpdatatables_cell_filter_query', $customQuery, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
                    $cellValue = $wpdb->get_var($customQuery);

                    if ($wpdb->last_error != '') {
                        return esc_html__('There was an error when trying to get cell value.', 'wpdatatables') . ': ' . ((current_user_can('administrator')) ? $wpdb->last_error : 'Please contact the administrator.');
                    }
                }
            } else if ($tableData->table_type == 'gravity' && $tableData->server_side) {
                $sorting = null;
                $content = json_decode($tableData->content);
                $form = \GFAPI::get_form($content->formId);
                $fieldsData = \WDTGravityIntegration\Plugin::getFieldsData($form, $content->fieldIds);
                foreach ($fieldsData as $fieldData) {
                    if ($fieldData['label'] == $columnKey) $columnKeyFieldData = $fieldData;
                    if ($includeSort && $isTableSortable) {
                        if ($fieldData['label'] == $sortColumn) {
                            $key = $fieldData['fieldIds'];
                            in_array($columnType, ['float', 'int'], true) ? $numeric = true : $numeric = null;
                            $sorting = array('key' => $key, 'direction' => $sortDirection, 'is_numeric' => $numeric);
                        }
                    }
                    if ($columnIDValue != '' || $columnID != '') {
                        if ($fieldData['label'] == $columnID) {
                            $searchCriteria['field_filters'][] = ['key' => $fieldData['fieldIds'],
                                'value' => $columnIDValue];
                        }
                    }
                }
                if (!($columnIDValue != '' || $columnID != '')) {
                    $rowID = (int)str_replace(array('.', ','), '', $rowID);
                    $searchCriteria['field_filters'][] = ['key' => 'id', 'value' => $rowID];
                }
                $entries = \GFAPI::get_entries($form['id'], $searchCriteria, $sorting, 100000000000);
                if ($columnIDValue != '' || $columnID != '') {
                    if ($entries == [])
                        return esc_html__('Column ID value for provided table ID not found!', 'wpdatatables');
                } else {
                    if ($entries == [])
                        return esc_html__('Row ID value for provided table ID not found!', 'wpdatatables');
                }
                if ($isFormulaColumnKey) {
                    try {
                        $cellValue =
                            WPDataTable::solveFormula(
                                $formulaColumnKey,
                                $headersColumnKey,
                                $entries[0]
                            );
                    } catch (Exception $e) {
                        return ltrim($e->getMessage(), '<br/><br/>');
                    }
                } else {
                    $cellValue = \WDTGravityIntegration\Plugin::prepareFieldsData($entries[0], $columnKeyFieldData);
                }

            } else {
                $dataRows = $wpDataTable->getDataRows();
                if ($dataRows == [])
                    return esc_html__('Table do not have data for provided table ID!', 'wpdatatables');
                if ($includeSort && $isTableSortable) {
                    $sortDirection = $sortDirection == 'ASC' ? SORT_ASC : SORT_DESC;
                    $sortingType = in_array($columnType, array('float',
                        'int',
                        'formula')) ? SORT_NUMERIC : SORT_REGULAR;
                    array_multisort(
                        array_column($dataRows, $sortColumn),
                        $sortDirection,
                        $sortingType,
                        $dataRows
                    );
                }

                $dataRows = apply_filters('wpdatatables_cell_data_rows_filter', $dataRows, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);

                if ($columnIDValue != '' || $columnID != '') {
                    $filteredData = array_filter($dataRows, function ($item) use ($columnIDValue, $columnID) {
                        if ($item[$columnID] == $columnIDValue) {
                            return true;
                        }
                        return false;
                    });
                    if ($filteredData == [])
                        return esc_html__('Column ID value for provided table ID not found!', 'wpdatatables');
                    $dataRows = array_values($filteredData);
                    $dataRows = apply_filters('wpdatatables_cell_filtered_data_rows_filter', $dataRows, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
                    $cellValue = $dataRows[0][$columnKey];
                } else {
                    if (in_array($tableData->table_type, ['gravity', 'formidable', 'forminator', 'ivyforms'])) {
                        $entryIdName = 'id';
                        if ($tableData->table_type == 'forminator')
                            $entryIdName = 'entryid';
                        if (!isset($dataRows[0][$entryIdName]))
                            return esc_html__('Entry ID not found! Please provide existing entry id from form.', 'wpdatatables');
                        $rowID = str_replace(array('.', ','), '', $rowID);
                        $filteredData = array_filter($dataRows, function ($item) use ($rowID, $entryIdName) {
                            if ($item[$entryIdName] == $rowID) {
                                return true;
                            }
                            return false;
                        });
                        if ($filteredData == [])
                            return esc_html__('Entry ID value for provided table ID not found!', 'wpdatatables');
                        $dataRows = array_values($filteredData);
                        $dataRows = apply_filters('wpdatatables_cell_filtered_data_rows_filter', $dataRows, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
                        $cellValue = $dataRows[0][$columnKey];
                    } else {
                        $rowID = $rowID != 0 ? $rowID - 1 : 0;
                        $wpDataTable->setDataRows($dataRows);
                        $cellValue = $wpDataTable->getCell($columnKey, $rowID);
                    }

                }
            }
            $cellValue = apply_filters('wpdatatables_cell_value_filter', $cellValue, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
            $cellValueOutput = $wpDataTable->getColumn($columnKey)->prepareCellOutput($cellValue);
        } catch (Exception $e) {
            return ltrim($e->getMessage(), '<br/><br/>');
        }
    }

    return apply_filters('wpdatatables_cell_output_filter', $cellValueOutput, $cellValue, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
}

    /**
     * Handler for the table shortcode ([wpdatatable]).
     *
     * @param array $atts
     * @param string|null $content
     *
     * @return mixed|string
     * @throws Exception
     */
    public function renderTable($atts, $content = null)
{
    global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9, $wdtExportFileName;

    extract(shortcode_atts(array(
        'id' => '0',
        'var1' => '%%no_val%%',
        'var2' => '%%no_val%%',
        'var3' => '%%no_val%%',
        'var4' => '%%no_val%%',
        'var5' => '%%no_val%%',
        'var6' => '%%no_val%%',
        'var7' => '%%no_val%%',
        'var8' => '%%no_val%%',
        'var9' => '%%no_val%%',
        'export_file_name' => '%%no_val%%',
        'table_view' => 'regular',
        'preview_mode' => '0'
    ), $atts));

    $id = absint($id);

    if (is_admin() && defined('AVADA_VERSION') && is_plugin_active('fusion-builder/fusion-builder.php') &&
        class_exists('Fusion_Element') && class_exists('WPDataTables_Fusion_Elements') &&
        isset($_POST['action']) && $_POST['action'] === 'get_shortcode_render') {
        return WPDataTables_Fusion_Elements::get_content_for_avada_live_builder($atts, 'table');
    }

    if (!$id) {
        return false;
    }

    // Check permissions - user must have access to view this table
    if (!WDTPermissionsEnforcer::canUserViewTable($id)) {
        return esc_html__('You do not have permission to view this table.', 'wpdatatables');
    }

    do_action('wpdatatables_before_render_table_config_data', $id);

    $tableData = WDTConfigController::loadTableFromDB($id);
    if (empty($tableData->content)) {
        return esc_html__('wpDataTable with provided ID not found!', 'wpdatatables');
    }

    do_action('wpdatatables_before_render_table', $id);

    /** @var mixed $var1 */
    $wdtVar1 = $var1 !== '%%no_val%%' ? $var1 : $tableData->var1;
    /** @var mixed $var2 */
    $wdtVar2 = $var2 !== '%%no_val%%' ? $var2 : $tableData->var2;
    /** @var mixed $var3 */
    $wdtVar3 = $var3 !== '%%no_val%%' ? $var3 : $tableData->var3;
    /** @var mixed $var4 */
    $wdtVar4 = $var4 !== '%%no_val%%' ? $var4 : $tableData->var4;
    /** @var mixed $var5 */
    $wdtVar5 = $var5 !== '%%no_val%%' ? $var5 : $tableData->var5;
    /** @var mixed $var6 */
    $wdtVar6 = $var6 !== '%%no_val%%' ? $var6 : $tableData->var6;
    /** @var mixed $var7 */
    $wdtVar7 = $var7 !== '%%no_val%%' ? $var7 : $tableData->var7;
    /** @var mixed $var8 */
    $wdtVar8 = $var8 !== '%%no_val%%' ? $var8 : $tableData->var8;
    /** @var mixed $var9 */
    $wdtVar9 = $var9 !== '%%no_val%%' ? $var9 : $tableData->var9;

    /** @var mixed $export_file_name */
    $wdtExportFileName = $export_file_name !== '%%no_val%%' ? $export_file_name : '';

    do_action('wpdatatables_before_get_table_metadata', $id);

    if ($tableData->table_type === 'simple') {
        try {
            $wpDataTableRows = WPDataTableRows::loadWpDataTableRows($id);
            $output = $wpDataTableRows->generateTable($id);
        } catch (Exception $e) {
            $output = ltrim($e->getMessage(), '<br/><br/>');
        }
    } else {
        try {
            $excelView = ($table_view == 'excel' && $tableData->table_type !== 'woo_commerce') ? 'excel' : null;
            $wpDataTable = $this->tableLoadService->loadTable($id, $excelView);
            /** @var mixed $preview_mode */
            $wpDataTable->setPreviewMode(absint($preview_mode) === 1);
            $wpDataTable = apply_filters('wpdatatables_filter_initial_table_construct', $wpDataTable);

            $output = '';
            if ($tableData->show_title && $tableData->title) {
                $output .= apply_filters('wpdatatables_filter_table_title', (empty($tableData->title) ? '' : '<h2 class="wpdt-c" id="wdt-table-title-' . $id . '">' . $tableData->title . '</h2>'), $id);
            }
            if ($tableData->show_table_description && $tableData->table_description) {
                $output .= apply_filters('wpdatatables_filter_table_description_text', (empty($tableData->table_description) ? '' : '<p class="wpdt-c" id="wdt-table-description-' . $id . '">' . $tableData->table_description . '</p>'), $id);
            }
            $output .= $wpDataTable->generateTable($tableData->connection);
        } catch (Exception $e) {
            $output = ToolsService::wdtShowError($e->getMessage());
        }
    }

    $output = apply_filters('wpdatatables_filter_rendered_table', $output, $id);

    return $output;
}

    /**
     * Handler for the SUM, AVG, MIN and MAX function shortcodes.
     *
     * @param array $atts
     * @param string|null $content
     * @param string|null $shortcode
     *
     * @return string
     * @throws \WDTException
     */
    public function renderFunction($atts, $content = null, $shortcode = null)
{

    $attributes = shortcode_atts(array(
        'table_id' => 0,
        'col_id' => 0,
        'label' => null,
        'value_only' => 0
    ), $atts);

    $table_id = absint($attributes['table_id']);
    $col_id = absint($attributes['col_id']);
    $label = is_null($attributes['label']) ? null : sanitize_text_field($attributes['label']);
    $value_only = absint($attributes['value_only']);

    if (!$table_id) {
        return esc_html__("Please provide table_id attribute for {$shortcode} shortcode!", 'wpdatatables');
    }
    if (!$col_id) {
        return esc_html__("Please provide col_id attribute for {$shortcode} shortcode!", 'wpdatatables');
    }

    $wpDataTable = $this->tableLoadService->loadTable($table_id, null, true);

    $wpDataTableColumns = $wpDataTable->getColumns();
    if (empty($wpDataTableColumns)) {
        return esc_html__('wpDataTable with provided ID not found!', 'wpdatatables');
    }

    $column = WDTConfigController::loadSingleColumnFromDB($col_id);

    $columnExists = (int)$column['table_id'] === $table_id;
    if ($columnExists === false) {
        return esc_html__("Column with ID {$col_id} is not found in table with ID {$table_id}!", 'wpdatatables');
    }
    if ($column['column_type'] !== 'int' && $column['column_type'] !== 'float' && $column['column_type'] !== 'formula') {
        return esc_html__('Provided column is not Integer or Float column type', 'wpdatatables');
    }

    if ($shortcode === 'wpdatatable_sum') {
        $function = 'sum';
        if (!isset($label)) {
            $label = get_option('wdtSumFunctionsLabel') ? get_option('wdtSumFunctionsLabel') : '&#8721; =';
        }
    } else if ($shortcode === 'wpdatatable_avg') {
        $function = 'avg';
        if (!isset($label)) {
            $label = get_option('wdtAvgFunctionsLabel') ? get_option('wdtAvgFunctionsLabel') : 'Avg =';
        }
    } else if ($shortcode === 'wpdatatable_min') {
        $function = 'min';
        if (!isset($label)) {
            $label = get_option('wdtMinFunctionsLabel') ? get_option('wdtMinFunctionsLabel') : 'Min =';
        }
    } else {
        $function = 'max';
        if (!isset($label)) {
            $label = get_option('wdtMaxFunctionsLabel') ? get_option('wdtMaxFunctionsLabel') : 'Max =';
        }
    }

    $funcResult = $wpDataTable->calcColumnFunction($column['orig_header'], $function);

    ob_start();
    include WDT_TEMPLATE_PATH . 'frontend/aggregate_functions.inc.php';
    $aggregateFunctionsHtml = ob_get_contents();
    ob_end_clean();

    return $aggregateFunctionsHtml;

}
}
