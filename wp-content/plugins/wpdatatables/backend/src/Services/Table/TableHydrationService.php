<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Table;

use WPDataTable;
use WDTException;
use Exception;
use WPDataTables\Plugin\Plugin;
use WPDataTables\Rendering\ColumnCssBuilder;

/**
 * Hydrates a live {@see WPDataTable} from persisted table/column config.
 *
 * @package WPDataTables\Services\Table
 */
class TableHydrationService
{
    /**
     * Populate table runtime state from DB metadata and dispatch data-source construction.
     *
     * @param WPDataTable $table
     * @param mixed       $tableData
     * @param array       $columnData
     *
     * @return void
     * @throws WDTException
     * @throws Exception
     */
    public function fillFromData(WPDataTable $table, $tableData, $columnData)
    {
                if (empty($tableData->table_type)) {
                    return;
                }

                global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9;

                // Set placeholders
                $wdtVar1 = $wdtVar1 === '' ? $tableData->var1 : $wdtVar1;
                $wdtVar2 = $wdtVar2 === '' ? $tableData->var2 : $wdtVar2;
                $wdtVar3 = $wdtVar3 === '' ? $tableData->var3 : $wdtVar3;
                $wdtVar4 = $wdtVar4 === '' ? $tableData->var4 : $wdtVar4;
                $wdtVar5 = $wdtVar5 === '' ? $tableData->var5 : $wdtVar5;
                $wdtVar6 = $wdtVar6 === '' ? $tableData->var6 : $wdtVar6;
                $wdtVar7 = $wdtVar7 === '' ? $tableData->var7 : $wdtVar7;
                $wdtVar8 = $wdtVar8 === '' ? $tableData->var8 : $wdtVar8;
                $wdtVar9 = $wdtVar9 === '' ? $tableData->var9 : $wdtVar9;

                // Defining column parameters if provided
                $params = array();
                if (isset($tableData->limit)) {
                    $params['limit'] = $tableData->limit;
                }
                if (isset($tableData->table_type)) {
                    $params['tableType'] = $tableData->table_type;
                }
                if (isset($columnData['columnTypes'])) {
                    $params['data_types'] = $columnData['columnTypes'];
                }
                if (isset($columnData['columnTitles'])) {
                    $params['columnTitles'] = $columnData['columnTitles'];
                }
                if (isset($columnData['columnFormulas'])) {
                    $params['columnFormulas'] = $columnData['columnFormulas'];
                }
                if (isset($columnData['sorting'])) {
                    $params['sorting'] = $columnData['sorting'];
                }
                if (isset($columnData['decimalPlaces'])) {
                    $params['decimalPlaces'] = $columnData['decimalPlaces'];
                }
                if (isset($columnData['exactFiltering'])) {
                    $params['exactFiltering'] = $columnData['exactFiltering'];
                }
                if (isset($columnData['globalSearchColumn'])) {
                    $params['globalSearchColumn'] = $columnData['globalSearchColumn'];
                }
                if (isset($columnData['searchInSelectBox'])) {
                    $params['searchInSelectBox'] = $columnData['searchInSelectBox'];
                }
                if (isset($columnData['searchInSelectBoxEditing'])) {
                    $params['searchInSelectBoxEditing'] = $columnData['searchInSelectBoxEditing'];
                }
                if (isset($columnData['rangeSlider'])) {
                    $params['rangeSlider'] = $columnData['rangeSlider'];
                }
                if (isset($columnData['rangeMaxValueDisplay'])) {
                    $params['rangeMaxValueDisplay'] = $columnData['rangeMaxValueDisplay'];
                }
                if (isset($columnData['customMaxRangeValue'])) {
                    $params['customMaxRangeValue'] = $columnData['customMaxRangeValue'];
                }
                if (isset($columnData['filterDefaultValue'])) {
                    $params['filterDefaultValue'] = $columnData['filterDefaultValue'];
                }
                if (isset($columnData['filterLabel'])) {
                    $params['filterLabel'] = $columnData['filterLabel'];
                }
                if (isset($columnData['checkboxesInModal'])) {
                    $params['checkboxesInModal'] = $columnData['checkboxesInModal'];
                }
                if (isset($columnData['andLogic'])) {
                    $params['andLogic'] = $columnData['andLogic'];
                }
                if (isset($columnData['possibleValuesType'])) {
                    $params['possibleValuesType'] = $columnData['possibleValuesType'];
                }
                if (isset($columnData['possibleValuesAddEmpty'])) {
                    $params['possibleValuesAddEmpty'] = $columnData['possibleValuesAddEmpty'];
                }
                if (isset($columnData['possibleValuesAjax'])) {
                    $params['possibleValuesAjax'] = $columnData['possibleValuesAjax'];
                }
                if (isset($columnData['foreignKeyRule'])) {
                    $params['foreignKeyRule'] = $columnData['foreignKeyRule'];
                }
                if (isset($columnData['editingDefaultValue'])) {
                    $params['editingDefaultValue'] = $columnData['editingDefaultValue'];
                }
                if (isset($columnData['dateInputFormat'])) {
                    $params['dateInputFormat'] = $columnData['dateInputFormat'];
                }
                if (isset($columnData['linkTargetAttribute'])) {
                    $params['linkTargetAttribute'] = $columnData['linkTargetAttribute'];
                }
                if (isset($columnData['linkNoFollowAttribute'])) {
                    $params['linkNoFollowAttribute'] = $columnData['linkNoFollowAttribute'];
                }
                if (isset($columnData['linkNoreferrerAttribute'])) {
                    $params['linkNoreferrerAttribute'] = $columnData['linkNoreferrerAttribute'];
                }
                if (isset($columnData['linkSponsoredAttribute'])) {
                    $params['linkSponsoredAttribute'] = $columnData['linkSponsoredAttribute'];
                }
                if (isset($columnData['linkButtonAttribute'])) {
                    $params['linkButtonAttribute'] = $columnData['linkButtonAttribute'];
                }
                if (isset($columnData['linkButtonLabel'])) {
                    $params['linkButtonLabel'] = $columnData['linkButtonLabel'];
                }
                if (isset($columnData['linkButtonClass'])) {
                    $params['linkButtonClass'] = $columnData['linkButtonClass'];
                }

                $params = apply_filters_deprecated(
                    'wpdt_filter_column_params',
                    array($params, $columnData),
                    WDT_INITIAL_STARTER_VERSION,
                    'wpdatatables_filter_column_params'
                );
                $params = apply_filters('wpdatatables_filter_column_params', $params, $columnData);

                if (isset($tableData->display_length)) {
                    $table->setDisplayLength($tableData->display_length);
                } else {
                    $table->disablePagination();
                }
                if (isset($tableData->file_location)) {
                    $table->setFileLocation($tableData->file_location);
                }
                $table->setCacheSourceData(!empty($tableData->cache_source_data));
                $table->setAutoUpdateCache(!empty($tableData->auto_update_cache));

                switch ($tableData->table_type) {
                    //[<-- Full version -->]//
                    case 'mysql' :
                    case 'manual' :
                        if (!empty($tableData->server_side)) {
                            $table->enableServerProcessing();
                            if (!empty($tableData->auto_refresh)) {
                                $table->setAutoRefresh((int)$tableData->auto_refresh);
                            }
                        }
                        if (!empty($tableData->editable)) {
                            $editor_roles = isset($tableData->editor_roles) ? $tableData->editor_roles : '';
                            if (wdtCurrentUserCanEdit($editor_roles, $table->getWpId())) {
                                $table->enableEditing();
                                if (!empty($tableData->inline_editing)) {
                                    $table->enableInlineEditing();
                                }
                                if (!empty($tableData->popover_tools)) {
                                    $table->enablePopoverTools();
                                }
                            }
                            if (!empty($tableData->edit_only_own_rows)) {
                                if (empty($columnData['userIdColumnHeader'])) {
                                    throw new WDTException(
                                        __('Users see and edit only their own data is enabled, but a User ID column is not configured.', 'wpdatatables')
                                    );
                                }
                                $table->setOnlyOwnRows(true);
                                $table->setUserIdColumn($columnData['userIdColumnHeader']);
                                if (isset($tableData->advanced_settings)) {
                                    $advancedSettingsTable = json_decode($tableData->advanced_settings);
                                    $table->setShowAllRows($advancedSettingsTable->showAllRows);
                                } else {
                                    $table->setShowAllRows(false);
                                }
                            }
                        }
                        if (is_admin() && $tableData->table_type == 'manual') {
                            $table->enableEditing();
                        }

                        $disableLimit = apply_filters_deprecated(
                            'wpdt_filter_sql_disable_limit',
                            array(!empty($tableData->disable_limit), $table->connection),
                            WDT_INITIAL_STARTER_VERSION,
                            'wpdatatables_filter_sql_disable_limit'
                        );
                        $disableLimit = apply_filters('wpdatatables_filter_sql_disable_limit', !empty($tableData->disable_limit), $table->connection);

                        $params['disable_limit'] = $disableLimit;


                        $table->queryBasedConstruct(
                            $tableData->content,
                            array(),
                            $params,
                            isset($tableData->init_read)
                        );
                        break;
                    //[<--/ Full version -->]//
                    case 'xls':
                    case 'csv':
                        $table->excelBasedConstruct(
                            $tableData->content,
                            $params
                        );
                        break;
                    case 'xml':
                        $table->XMLBasedConstruct(
                            $tableData->content,
                            $params
                        );
                        break;
                    case 'json':
                        $table->jsonBasedConstruct(
                            $tableData->content,
                            $params
                        );
                        break;
                    case 'nested_json':
                        $table->nestedJsonBasedConstruct(
                            $tableData->content,
                            $params
                        );
                        break;
                    case 'serialized':
                        $table->serializedPHPBasedConstruct(
                            $tableData->content,
                            $params
                        );
                        break;
                    case 'google_spreadsheet':
                        $table->googleSheetBasedConstruct(
                            $tableData->content,
                            $params
                        );
                        break;
                    default:
                        // Solution for addons
                        $table->customBasedConstruct(
                            $tableData,
                            $params
                        );
                        break;
                }
                if (!empty($tableData->content)) {
                    $table->setTableContent($tableData->content);
                }
                if (!empty($tableData->table_type)) {
                    $table->setTableType($tableData->table_type);
                }
                if (!empty($tableData->title)) {
                    $table->setTitle($tableData->title);
                }
                if (!empty($tableData->table_description)) {
                    $table->setDescription($tableData->table_description);
                }
                if (!empty($tableData->hide_before_load)) {
                    $table->hideBeforeLoad();
                } else {
                    $table->showBeforeLoad();
                }
                if (!empty($tableData->fixed_layout)) {
                    $table->setFixedLayout(true);
                }
                if (!empty($tableData->word_wrap)) {
                    $table->setWordWrap(true);
                }
                $table->setFilteringForm(!empty($tableData->filtering_form));

                $table->setClearFilters(!empty($tableData->clearFilters));

                if (!empty($tableData->responsive)) {
                    $table->setResponsive(true);
                }
                if (!empty($tableData->scrollable)) {
                    $table->setScrollable(true);
                }
                if (empty($tableData->sorting)) {
                    $table->sortDisable();
                }
                if (empty($tableData->tools)) {
                    $table->disableTT();
                } else {
                    $table->enableTT();
                    if (isset($tableData->tabletools_config)) {
                        $table->getRuntimeTable()->setTableToolsConfig($tableData->tabletools_config);
                    } else {
                        $table->getRuntimeTable()->setTableToolsConfig(array(
                            'print' => 1,
                            'copy' => 1,
                            'excel' => 1,
                            'csv' => 1,
                            'pdf' => 0
                        ));
                    }
                }
                if (get_option('wdtInterfaceLanguage') != '') {
                    $table->setInterfaceLanguage(get_option('wdtInterfaceLanguage'));
                }
                if (!empty($tableData->filtering)) {
                    $table->enableAdvancedFilter();
                }

                if (!empty($tableData->advanced_settings)) {
                    $advancedSettings = json_decode($tableData->advanced_settings);
                    isset($advancedSettings->info_block) ? $table->setInfoBlock($advancedSettings->info_block) : $table->setInfoBlock(true);
                    isset($advancedSettings->global_search) ? $table->setGlobalSearch($advancedSettings->global_search) : $table->setGlobalSearch(true);
                    isset($advancedSettings->showRowsPerPage) ? $table->setShowRowsPerPage($advancedSettings->showRowsPerPage) : $table->setShowRowsPerPage(true);
                    isset($advancedSettings->showAllRows) ? $table->setShowAllRows($advancedSettings->showAllRows) : $table->setShowAllRows(false);
                    isset($advancedSettings->simpleResponsive) ? $table->setSimpleResponsive($advancedSettings->simpleResponsive) : $table->setSimpleResponsive(false);
                    isset($advancedSettings->simpleHeader) ? $table->setSimpleHeader($advancedSettings->simpleHeader) : $table->setSimpleHeader(false);
                    isset($advancedSettings->stripeTable) ? $table->setStripeTable($advancedSettings->stripeTable) : $table->setStripeTable(false);
                    isset($advancedSettings->cellPadding) ? $table->setCellPadding($advancedSettings->cellPadding) : $table->setCellPadding(10);
                    isset($advancedSettings->removeBorders) ? $table->setRemoveBorders($advancedSettings->removeBorders) : $table->setRemoveBorders(false);
                    isset($advancedSettings->borderCollapse) ? $table->setBorderCollapse($advancedSettings->borderCollapse) : $table->setBorderCollapse('collapse');
                    isset($advancedSettings->borderSpacing) ? $table->setBorderSpacing($advancedSettings->borderSpacing) : $table->setBorderSpacing(0);
                    isset($advancedSettings->verticalScroll) ? $table->setVerticalScroll($advancedSettings->verticalScroll) : $table->setVerticalScroll(false);
                    isset($advancedSettings->verticalScrollHeight) ? $table->setVerticalScrollHeight($advancedSettings->verticalScrollHeight) : $table->setVerticalScrollHeight(600);
                    isset($advancedSettings->responsiveAction) ? $table->setResponsiveAction($advancedSettings->responsiveAction) : $table->setResponsiveAction('icon');
                    isset($advancedSettings->pagination_top) ? $table->setPaginationOnTop($advancedSettings->pagination_top) : $table->setPaginationOnTop(0);
                    isset($advancedSettings->pagination) ? $table->setPagination($advancedSettings->pagination) : $table->setPagination(true);
                    isset($advancedSettings->paginationAlign) ? $table->setPaginationAlign($advancedSettings->paginationAlign) : $table->setPaginationAlign('right');
                    isset($advancedSettings->paginationLayout) ? $table->setPaginationLayout($advancedSettings->paginationLayout) : $table->setPaginationLayout('full_numbers');
                    isset($advancedSettings->paginationLayoutMobile) ? $table->setPaginationLayoutMobile($advancedSettings->paginationLayoutMobile) : $table->setPaginationLayoutMobile('simple');
                    isset($advancedSettings->editButtonsDisplayed) ? $table->setEditButtonsDisplayed($advancedSettings->editButtonsDisplayed) : $table->setEditButtonsDisplayed(array('all'));
                    isset($advancedSettings->enableDuplicateButton) ? $table->setEnableDuplicateButton($advancedSettings->enableDuplicateButton) : $table->setEnableDuplicateButton(false);
                    (isset($advancedSettings->language) && $advancedSettings->language != '' ? $table->setInterfaceLanguage($advancedSettings->language) : get_option('wdtInterfaceLanguage') != '') ? $table->setInterfaceLanguage(get_option('wdtInterfaceLanguage')) : '';
                    isset($advancedSettings->tableSkin) ? $table->setTableSkin($advancedSettings->tableSkin) : $table->setTableSkin(get_option('wdtBaseSkin'));
                    isset($advancedSettings->table_wcag) ? $table->setTableWCAG($advancedSettings->table_wcag) : $table->setTableWCAG(0);
                    isset($advancedSettings->advanced_filter_option) ? $table->setAdvancedFilterOption($advancedSettings->advanced_filter_option) : $table->setAdvancedFilterOption(0);
                    isset($advancedSettings->simple_template_id) ? $table->setSimpleTemplateId($advancedSettings->simple_template_id) : $table->setSimpleTemplateId(0);
                    isset($advancedSettings->tableFontColorSettings) ? $table->setTableFontColorSettings($advancedSettings->tableFontColorSettings) : $table->setTableFontColorSettings(get_option('wdtFontColorSettings'));
                    isset($advancedSettings->tableBorderRemoval) ? $table->setTableBorderRemoval($advancedSettings->tableBorderRemoval) : $table->setTableBorderRemoval(get_option('wdtBorderRemoval'));
                    isset($advancedSettings->tableBorderRemovalHeader) ? $table->setTableBorderRemovalHeader($advancedSettings->tableBorderRemovalHeader) : $table->setTableBorderRemovalHeader(get_option('wdtBorderRemovalHeader'));
                    isset($advancedSettings->tableCustomCss) ? $table->setTableCustomCss($advancedSettings->tableCustomCss) : $table->setTableCustomCss('');
                    isset($advancedSettings->pdfPaperSize) ? $table->setPdfPaperSize($advancedSettings->pdfPaperSize) : $table->setPdfPaperSize('A4');
                    isset($advancedSettings->pdfPageOrientation) ? $table->setPdfPageOrientation($advancedSettings->pdfPageOrientation) : $table->setPdfPageOrientation('portrait');
                    isset($advancedSettings->showTableToolsIncludeHTML) ? $table->setTableToolsIncludeHTML($advancedSettings->showTableToolsIncludeHTML) : $table->setTableToolsIncludeHTML(false);
                    isset($advancedSettings->showTableToolsIncludeTitle) ? $table->setTableToolsIncludeTitle($advancedSettings->showTableToolsIncludeTitle) : $table->setTableToolsIncludeTitle(false);
                    isset($advancedSettings->show_table_description) ? $table->setShowDescription($advancedSettings->show_table_description) : $table->setShowDescription(false);
                    isset($advancedSettings->table_description) ? $table->setDescription($advancedSettings->table_description) : $table->setDescription('');
                    isset($advancedSettings->fixed_columns) ? $table->setFixedColumns($advancedSettings->fixed_columns) : $table->setFixedColumns(false);
                    isset($advancedSettings->fixed_left_columns_number) ? $table->setLeftFixedColumnsNumber($advancedSettings->fixed_left_columns_number) : $table->setLeftFixedColumnsNumber(0);
                    isset($advancedSettings->fixed_right_columns_number) ? $table->setRightFixedColumnsNumber($advancedSettings->fixed_right_columns_number) : $table->setRightFixedColumnsNumber(0);
                    isset($advancedSettings->fixed_header) ? $table->setFixedHeaders($advancedSettings->fixed_header) : $table->setFixedHeaders(false);
                    isset($advancedSettings->fixed_header_offset) ? $table->setFixedHeadersOffset($advancedSettings->fixed_header_offset) : $table->setFixedHeadersOffset(0);
                    isset($advancedSettings->customRowDisplay) ? $table->setCustomDisplayLength($advancedSettings->customRowDisplay) : $table->setCustomDisplayLength('');
                    isset($advancedSettings->customStringEmptyFiltering) ? $table->setCustomStringEmptyFiltering($advancedSettings->customStringEmptyFiltering) : $table->setCustomStringEmptyFiltering('');
                    isset($advancedSettings->loader) ? $table->setLoader($advancedSettings->loader) : $table->setLoader(get_option('wdtGlobalTableLoader'));
                    isset($advancedSettings->showCartInformation) ? $table->setshowCartInformation($advancedSettings->showCartInformation) : $table->setshowCartInformation(1);
                    isset($advancedSettings->index_column) ? $table->setIndexColumn($advancedSettings->index_column) : $table->setIndexColumn(0);
                } else {
                    $table->setInfoBlock(true);
                    $table->setGlobalSearch(true);
                    $table->setShowRowsPerPage(true);
                    $table->setShowAllRows(false);
                    $table->setSimpleHeader(false);
                    $table->setSimpleResponsive(false);
                    $table->setStripeTable(false);
                    $table->setCellPadding(10);
                    $table->setRemoveBorders(false);
                    $table->setBorderCollapse('collapse');
                    $table->setBorderSpacing(0);
                    $table->setVerticalScroll(false);
                    $table->setVerticalScrollHeight(600);
                    $table->setPaginationOnTop(0);
                    $table->setPagination(true);
                    $table->setPaginationAlign('right');
                    $table->setPaginationLayout('full_numbers');
                    $table->setPaginationLayoutMobile('simple');
                    $table->setEditButtonsDisplayed(array('all'));
                    $table->setEnableDuplicateButton(false);
                    $table->setTableSkin(get_option('wdtBaseSkin'));
                    $table->setTableWCAG(0);
                    get_option('wdtInterfaceLanguage') != '' ? $table->setInterfaceLanguage(get_option('wdtInterfaceLanguage')) : '';
                    $table->setTableFontColorSettings(get_option('wdtFontColorSettings'));
                    $table->setTableBorderRemoval(get_option('wdtBorderRemoval'));
                    $table->setTableBorderRemovalHeader(get_option('wdtBorderRemovalHeader'));
                    $table->setTableCustomCss('');
                    $table->setPdfPaperSize('A4');
                    $table->setPdfPageOrientation('portrait');
                    $table->setTableToolsIncludeHTML(false);
                    $table->setTableToolsIncludeTitle(false);
                    $table->setShowDescription(false);
                    $table->setDescription('');
                    $table->setFixedColumns(false);
                    $table->setLeftFixedColumnsNumber(0);
                    $table->setRightFixedColumnsNumber(0);
                    $table->setFixedHeaders(false);
                    $table->setFixedHeadersOffset(0);
                    $table->setCustomDisplayLength('');
                    $table->setLoader(get_option('wdtGlobalTableLoader'));
                }
                if (!empty($columnData['columnOrder'])) {
                    $table->reorderColumns($columnData['columnOrder']);
                }
                if (!empty($columnData['columnWidths'])) {
                    $table->wdtDefineColumnsWidth($columnData['columnWidths']);
                }
                if (!empty($columnData['possibleValues'])) {
                    $table->setColumnsPossibleValues($columnData['possibleValues']);
                }
                if (!empty($tableData->columns)) {
                    $this->applyRenderingRules($table, $tableData->columns);
                }

                do_action_deprecated('wdt_extend_wpdatatable_object', array($table,
                    $tableData), WDT_INITIAL_STARTER_VERSION, 'wpdatatables_extend_wpdatatable_object');
                do_action('wpdatatables_extend_wpdatatable_object', $table, $tableData);

    }

    /**
     * Apply per-column rendering rules after columns exist.
     *
     * @param WPDataTable $table
     * @param array       $columnData
     *
     * @return void
     */
    public function applyRenderingRules(WPDataTable $table, $columnData)
    {
                global $wpdb, $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9, $is_safari;
                $columnIndex = 1;
                // Check the search values passed from URL
                if (isset($_GET['wdt_search'])) {
                    $table->setDefaultSearchValue(
                        \WPDataTables\Common\Helpers\SqlHelper::sanitizeSearchQueryParam($_GET['wdt_search'])
                    );
                }
                if (isset($_GET['wdt_var1'])) {
                    $wdtVar1 = urldecode(sanitize_text_field($_GET['wdt_var1']));
                }
                if (isset($_GET['wdt_var2'])) {
                    $wdtVar2 = urldecode(sanitize_text_field($_GET['wdt_var2']));
                }
                if (isset($_GET['wdt_var3'])) {
                    $wdtVar3 = urldecode(sanitize_text_field($_GET['wdt_var3']));
                }
                if (isset($_GET['wdt_var4'])) {
                    $wdtVar4 = urldecode(sanitize_text_field($_GET['wdt_var4']));
                }
                if (isset($_GET['wdt_var5'])) {
                    $wdtVar5 = urldecode(sanitize_text_field($_GET['wdt_var5']));
                }
                if (isset($_GET['wdt_var6'])) {
                    $wdtVar6 = urldecode(sanitize_text_field($_GET['wdt_var6']));
                }
                if (isset($_GET['wdt_var7'])) {
                    $wdtVar7 = urldecode(sanitize_text_field($_GET['wdt_var7']));
                }
                if (isset($_GET['wdt_var8'])) {
                    $wdtVar8 = urldecode(sanitize_text_field($_GET['wdt_var8']));
                }
                if (isset($_GET['wdt_var9'])) {
                    $wdtVar9 = urldecode(sanitize_text_field($_GET['wdt_var9']));
                }
                $id = $table->getWpId();
                do_action('wpdatatables_before_placeholders_shortcode_url_filter', $id);

                // Define all column-dependent rendering rules
                foreach ($columnData as $key => $column) {

                    $table->column_id = $key;
                    // Set filter types
                    $table->getColumn($column->orig_header)->setFilterType($column->filter_type);
                    // Set CSS class
                    $table->getColumn($column->orig_header)->addCSSClass($column->css_class);
                    // Set visibility
                    if (!$column->visible) {
                        $table->getColumn($column->orig_header)->setIsVisible(false);
                    }
                    // Set default value
                    $table->getColumn($column->orig_header)->setFilterDefaultValue($column->filterDefaultValue);
                    // Set conditional formatting rules
                    if ($column->conditional_formatting && $column->conditional_formatting !== '[]') {
                        $table->getColumn($column->orig_header)
                            ->setConditionalFormattingData($column->conditional_formatting);
                        $table->addConditionalFormattingColumn($column->orig_header);
                    }

                    if (isset($column->transformValueText) && $column->transformValueText != '') {
                        $table->getColumn($column->orig_header)->setTransformValueText($column->transformValueText);
                        $table->addTransformValueColumn($column->orig_header);
                    }
                    //[<-- Full version -->]//
                    // Set SUM columns
                    if ($column->calculateTotal) {
                        $table->addSumColumn($column->orig_header);
                        $table->addSumFooterColumn($column->orig_header);
                    }

                    // Add AVG, MAX, MIN columns and Column decimal places
                    if (isset($column->calculateAvg) && $column->calculateAvg == 1) {
                        $table->addAvgColumn($column->orig_header);
                        $table->addAvgFooterColumn($column->orig_header);
                    }
                    if (isset($column->calculateMin) && $column->calculateMin == 1) {
                        $table->addMinColumn($column->orig_header);
                        $table->addMinFooterColumn($column->orig_header);
                    }
                    if (isset($column->calculateMax) && $column->calculateMax == 1) {
                        $table->addMaxColumn($column->orig_header);
                        $table->addMaxFooterColumn($column->orig_header);
                    }
                    if (isset ($column->decimalPlaces)) {
                        $table->addColumnsDecimalPlaces($column->orig_header, $column->decimalPlaces);
                    }

                    // Set hiding on phones and tablets for responsiveness
                    if ($table->isResponsive()) {
                        if ($column->hide_on_mobiles) {
                            $table->getColumn($column->orig_header)->setHiddenOnPhones(true);
                        }
                        if ($column->hide_on_tablets) {
                            $table->getColumn($column->orig_header)->setHiddenOnTablets(true);
                        }
                    }

                    // if grouping enabled for this column, passing it to table class
                    if ($column->groupColumn) {
                        $table->groupByColumn($column->orig_header);
                    }
                    if ($column->defaultSortingColumn != '0') {
                        $table->setDefaultSortColumn($column->orig_header);
                        if ($column->defaultSortingColumn == '1') {
                            $table->setDefaultSortDirection('ASC');
                        } elseif ($column->defaultSortingColumn == '2') {
                            $table->setDefaultSortDirection('DESC');
                        }
                    }
                    // If thousands separator is disabled or column is "ID column for editing"
                    // pass it to the column class instance
                    if ($column->type == 'int') {
                        if ($column->skip_thousands_separator || $column->id_column) {
                            $table->getColumn($column->orig_header)->setShowThousandsSeparator(false);
                            $table->addColumnsThousandsSeparator($column->orig_header, 0);
                        } else {
                            $table->addColumnsThousandsSeparator($column->orig_header, 1);
                        }
                    }

                    // Set ID column if specified
                    if ($column->id_column) {
                        $table->setIdColumnKey($column->orig_header);
                    }
                    if ($column->orig_header == 'wdt_created_by') {
                        $table->setUserColumnKey($column->orig_header);
                    }
                    if ($column->orig_header == 'wdt_created_at') {
                        $table->setDatecreatedColumnKey($column->orig_header);
                    }
                    if ($column->orig_header == 'wdt_last_edited_by') {
                        $table->setUserEditColumnKey($column->orig_header);
                    }
                    if ($column->orig_header == 'wdt_last_edited_at') {
                        $table->setDatecreatedEditColumnKey($column->orig_header);
                    }
                    // Set front-end editor input type
                    $table->getColumn($column->orig_header)
                        ->setInputType($column->editor_type);
                    // Define if input cannot be empty
                    $table->getColumn($column->orig_header)
                        ->setNotNull((bool)$column->input_mandatory);

                    // Get display before/after and color — per-column CSS lives in Rendering\ColumnCssBuilder.
                    $cssColumnHeader = Plugin::container()->get(ColumnCssBuilder::class)->resolveCssColumnHeader($column, $table->column_id);
                    $table->setColumnsCss(Plugin::container()->get(ColumnCssBuilder::class)->appendForColumn(
                        $table,
                        $column,
                        $cssColumnHeader,
                        $table->getColumnsCSS(),
                        (bool) $is_safari
                    ));

                    $columnIndex++;
                }


                // Check the default values passed from URL
                if (isset($_GET['wdt_column_filter']) && is_array($_GET['wdt_column_filter'])) {
                    foreach ($_GET['wdt_column_filter'] as $fltColKey => $fltDefVal) {
                        if (!is_scalar($fltColKey) || !is_scalar($fltDefVal)) {
                            continue;
                        }

                        $fltColKey = sanitize_text_field(wp_unslash($fltColKey));
                        $fltDefVal = sanitize_text_field(wp_unslash($fltDefVal));
                        $wdtCol = $table->getColumn($fltColKey);
                        if (!empty($wdtCol)) {
                            $table->getColumn($fltColKey)->setFilterDefaultValue($fltDefVal);
                        }
                    }
                }

    }
}
