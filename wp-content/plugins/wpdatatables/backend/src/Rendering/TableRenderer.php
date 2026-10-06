<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Rendering;

use WPDataTable;

/**
 * HTML assembly for a standard (non-Excel) wpDataTable.
 *
 * The legacy `WPDataTable::generateTable()`, `WPDataTable::renderWithJSAndStyles()`
 * and `WPDataTable::renderModal()` are thin delegators into this service — same
 * templates, same style block, same `wp_footer` modal registration, same
 * `wpdatatables_add_custom_modal` / `wpdatatables_add_custom_template_modal`
 * actions.
 *
 * `wrap_template.inc.php` and `table_main.inc.php` are rendered through
 * {@see TemplateEngine}, which `include`s them inside a `Closure` bound to the
 * table so their `$this` references still resolve. The modal templates are
 * included directly (they run in a static context and never reference `$this`).
 * Table state is read exclusively through the existing public accessors.
 *
 * @package WPDataTables\Rendering
 */
class TableRenderer
{
    /** @var TemplateEngine */
    private $templateEngine;

    /** @var AssetManager */
    private $assetManager;

    public function __construct(TemplateEngine $templateEngine, AssetManager $assetManager)
    {
        $this->templateEngine = $templateEngine;
        $this->assetManager   = $assetManager;
    }

    /**
     * Assemble the full table HTML: the wrapped table content, the per-table
     * `<style>` block, and the render/script-style blocks. Registers the shared
     * modal once on `wp_footer`.
     *
     * The polymorphic `WPDataTable::renderWithJSAndStyles()` call stays in the
     * `generateTable()` delegator (so subclass overrides are honoured); the
     * already-rendered table content is passed in here.
     *
     * @param WPDataTable $table        the table being rendered
     * @param string      $tableContent the output of `renderWithJSAndStyles()`
     *
     * @return string the complete table HTML
     */
    public function render(WPDataTable $table, $tableContent)
    {
        $returnData = $this->templateEngine->render(
            $table,
            WDT_TEMPLATE_PATH . 'frontend/wrap_template.inc.php',
            array('tableContent' => $tableContent)
        );

        $inlineModalHtml = '';
        if (!WPDataTable::$modalRendered) {
            $wantInlineModal = (
                $table->isPreviewMode()
                || apply_filters('wpdatatables_should_inline_frontend_modal', false)
            );
            /** @since split Divi 5 VB / REST preview mounts modal markup from AJAX instead of inlined HTML */
            $doInlineModalMarkup = apply_filters(
                'wpdatatables_should_inline_modal_in_table_markup',
                $wantInlineModal,
                $table
            );

            if ($wantInlineModal && !$doInlineModalMarkup) {
                WPDataTable::$modalRendered = true;
            } elseif ($doInlineModalMarkup) {
                $inlineModalHtml = WPDataTable::getModalHtml();
                WPDataTable::$modalRendered = true;
            } elseif (!is_admin()) {
                add_action('wp_footer', array('WPDataTable', 'renderModal'));
                WPDataTable::$modalRendered = true;
            }
        }

        if ($inlineModalHtml !== '') {
            $returnData .= $inlineModalHtml;
        }

        // Generate the style block
        $returnData .= "<style>\n";
        // Columns text before and after
        $returnData .= $table->getColumnsCSS();

        // Table layout
        $customCss = get_option('wdtCustomCss');

        $returnData .= $table->isFixedLayout() ? "table.wpDataTable { table-layout: fixed !important; }\n" : '';
        $returnData .= $table->isWordWrap() ? "table.wpDataTable td, table.wpDataTable th { white-space: normal !important; }\n" : '';

        if ($customCss) {
            $returnData .= stripslashes_deep($customCss);
        }
        if (get_option('wdtNumbersAlign')) {
            $returnData .= "table.wpDataTable td.numdata { text-align: right !important; }\n";
        }

        if (get_option('wdtBorderRemoval')) {
            $returnData .= ".wpDataTablesWrapper table.wpDataTable > tbody > tr > td{ border: none !important; }\n";
        }
        if (get_option('wdtBorderRemovalHeader')) {
            $returnData .= ".wpDataTablesWrapper table.wpDataTable > thead > tr > th{ border: none !important; }\n";
        }
        $returnData .= "</style>\n";

        $returnData .= wdtRenderScriptStyleBlock($table->getWpId());
        $returnData .= wdtTableRenderScriptStyleBlock($table);

        return $returnData;
    }

    /**
     * Enqueue the table's JS/CSS and render the `table_main` template.
     *
     * The enqueue step is delegated to the AssetManager. Any output echoed by
     * `wpdatatables_add_custom_modal` handlers is captured into the returned
     * content via the `ob_start()` block.
     *
     * @param WPDataTable $table the table being rendered
     *
     * @return string the rendered table content
     */
    public function renderWithAssets(WPDataTable $table)
    {
        $this->assetManager->enqueueFrontend($table);

        $table->addCSSClass('data-t');

        $advancedFilterPosition = get_option('wdtRenderFilter');
        $wdtSumFunctionsLabel   = get_option('wdtSumFunctionsLabel');
        $wdtAvgFunctionsLabel   = get_option('wdtAvgFunctionsLabel');
        $wdtMinFunctionsLabel   = get_option('wdtMinFunctionsLabel');
        $wdtMaxFunctionsLabel   = get_option('wdtMaxFunctionsLabel');

        ob_start();
        echo $this->templateEngine->render(
            $table,
            WDT_TEMPLATE_PATH . 'frontend/table_main.inc.php',
            array(
                'advancedFilterPosition' => $advancedFilterPosition,
                'wdtSumFunctionsLabel'   => $wdtSumFunctionsLabel,
                'wdtAvgFunctionsLabel'   => $wdtAvgFunctionsLabel,
                'wdtMinFunctionsLabel'   => $wdtMinFunctionsLabel,
                'wdtMaxFunctionsLabel'   => $wdtMaxFunctionsLabel,
            )
        );
        do_action('wpdatatables_add_custom_modal', $table);
        $tableContent = ob_get_contents();
        ob_end_clean();

        return $tableContent;
    }

    /**
     * Render the shared filter/delete modals. Registered once on `wp_footer`
     * via the `WPDataTable::renderModal()` static facade (so the callback name
     * stays `array('WPDataTable', 'renderModal')`).
     *
     * @return void
     */
    public function renderModal()
    {
        include_once WDT_TEMPLATE_PATH . 'frontend/modal.inc.php';
        include_once WDT_TEMPLATE_PATH . 'common/delete_modal.inc.php';
        do_action('wpdatatables_add_custom_template_modal');
    }

    /**
     * Build the DataTables description JSON for a standard (non-Excel) table.
     *
     * The legacy `WPDataTable::getJsonDescription()` is a thin delegator into
     * this method — same `$wdtVar*` server-side ajax wiring, same TableTools /
     * edit / advanced-filter button assembly, same
     * `wpdatatables_filter_table_description` filter.
     *
     * The `//[<-- Full version -->]//` markers must be preserved exactly: the
     * release strip pipeline keys off them. Table state is read through the
     * existing public accessors (`_tableToolsConfig` via `getTableToolsConfig()`,
     * `_columnsDecimalPlaces` / `_columnsThousandsSeparator` via their getters).
     *
     * `WPExcelDataTable::getJsonDescription()` is a separate override on the
     * handsontable path and is deliberately left in place.
     *
     * @param WPDataTable $table the table being described
     *
     * @return string the table-description JSON
     */
    public function buildJsonDescription(WPDataTable $table)
    {
        //[<-- Full version -->]//
        global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9;
        //[<--/ Full version -->]//
        global $wdtExportFileName;

        $tableToolsConfig = $table->getTableToolsConfig();

        $obj = new \stdClass();
        $obj->tableId = $table->getId();
        $obj->tableType = $table->getTableType();
        $obj->selector = '#' . $table->getId();
        //[<-- Full version -->]//
        $obj->responsive = $table->isResponsive();
        $obj->responsiveAction = $table->getResponsiveAction();
        $obj->editable = $table->isEditable();
        $obj->inlineEditing = $table->inlineEditingEnabled();
        $obj->infoBlock = $table->isInfoBlock();
        $obj->pagination_top = $table->getPaginationOnTop();
        $obj->pagination = $table->isPagination();
        $obj->paginationAlign = $table->getPaginationAlign();
        $obj->paginationLayout = $table->getPaginationLayout();
        $obj->paginationLayoutMobile = $table->getPaginationLayoutMobile();
        $obj->file_location = $table->getFileLocation();
        $obj->tableSkin = $table->getTableSkin();
        $obj->table_wcag = $table->isTableWCAG();
        $obj->advanced_filter_option = $table->isAdvancedFilterOption();
        $obj->simple_template_id = $table->getSimpleTemplateId();
        $obj->scrollable = $table->isScrollable();
        $obj->fixedLayout = $table->isFixedLayout();
        $obj->globalSearch = $table->isGlobalSearch();
        $obj->showRowsPerPage = $table->isShowRowsPerPage();
        $obj->popoverTools = $table->popoverToolsEnabled();
        $obj->loader = $table->isLoaderVisible();
        $obj->showCartInformation = $table->getShowCartInformation();
        //[<--/ Full version -->]//
        $obj->hideBeforeLoad = $table->doHideBeforeLoad();
        $obj->number_format = (int)(get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1);
        $obj->decimalPlaces = (int)(get_option('wdtDecimalPlaces') ? get_option('wdtDecimalPlaces') : 2);
        //[<-- Full version -->]//
        if ($table->isEditable()) {
            $obj->fileUploadBaseUrl = site_url() . '/wp-admin/admin-ajax.php?action=wdt_upload_file&table_id=' . $table->getWpId();
            $obj->adminAjaxBaseUrl = site_url() . '/wp-admin/admin-ajax.php';
            $obj->idColumnIndex = $table->getColumnHeaderOffset($table->getIdColumnKey());
            $obj->idColumnKey = $table->getIdColumnKey();
            $obj->showAllRows = $table->isShowAllRows();
        }
        //[<--/ Full version -->]//
        $obj->spinnerSrc = WDT_ASSETS_PATH . '/img/spinner.gif';
        $obj->index_column = $table->getIndexColumn();
        $obj->groupingEnabled = $table->groupingEnabled();
        if ($table->groupingEnabled()) {
            $obj->groupingColumnIndex = $table->groupingColumn();
        }
        $obj->tableWpId = $table->getWpId();
        $obj->dataTableParams = new \StdClass();

        $currentSkin = $table->getTableSkin();
        $infoBlock = ($obj->infoBlock == true) ? 'i' : '';
        $globalSearch = ($obj->globalSearch == true) ? 'f' : '';
        $showRowsPerPage = ($obj->showRowsPerPage == true) ? 'l' : '';
        $pagination = ($obj->pagination == true) ? 'p' : '';
        $scrollable = ($table->isScrollable() == true) ? "<'wdtscroll't>" : 't';
        if ($currentSkin === 'mojito' || $currentSkin === 'dark-mojito') {
            $obj->dataTableParams->sDom = "<'wdt_wrapper_for_buttons'{$globalSearch}{$showRowsPerPage}BT>{$scrollable}{$infoBlock}{$pagination}";
        } else {
            $obj->dataTableParams->sDom = "BT<'clear'>{$showRowsPerPage}{$globalSearch}{$scrollable}{$infoBlock}{$pagination}";
        }

        $obj->dataTableParams->bSortCellsTop = false;
        //[<-- Full version -->]//
        $obj->dataTableParams->bFilter = $table->filterEnabled();
        //[<--/ Full version -->]//
        if ($table->paginationEnabled()) {
            $obj->customRowDisplay = $table->getCusgtomDisplayLength();
            $obj->dataTableParams->bPaginate = true;
            $originalArray = explode(',', $obj->customRowDisplay);

            $trimArray = array_map(function ($value) {
                return ($value == -1) ? __('All', 'wpdatatables') : (int)$value;
            }, $originalArray);

            if (wp_is_mobile()) {
                $obj->dataTableParams->sPaginationType = $table->getPaginationLayoutMobile();
            } else {
                $obj->dataTableParams->sPaginationType = $table->getPaginationLayout();
            }
            $obj->dataTableParams->aLengthMenu = $obj->customRowDisplay != "" ?
                json_decode('[[' . $table->getCusgtomDisplayLength() . '], ' . json_encode($trimArray) . ']') :
                json_decode('[[1,5,10,25,50,100,-1],[1,5,10,25,50,100,"' . __('All', 'wpdatatables') . '"]]');
            $obj->dataTableParams->iDisplayLength = (int)$table->getDisplayLength();
        } else {
            $obj->customRowDisplay = $table->getCusgtomDisplayLength();
            $originalArray = explode(',', $obj->customRowDisplay);
            $trimArray = array_map(function ($value) {
                return ($value == -1) ? __('All', 'wpdatatables') : (int)$value;
            }, $originalArray);

            $obj->dataTableParams->aLengthMenu = $obj->customRowDisplay != "" ?
                json_decode('[[' . $table->getCusgtomDisplayLength() . '], ' . json_encode($trimArray) . ']') :
                json_decode('[[1,5,10,25,50,100,-1],[1,5,10,25,50,100,"' . __('All', 'wpdatatables') . '"]]');
            $obj->dataTableParams->iDisplayLength = (int)$table->getDisplayLength();
            if ($table->groupingEnabled()) {
                $obj->dataTableParams->aaSortingFixed = json_decode('[[' . $table->groupingColumn() . ', "asc"]]');
            }
        }
        if (get_option('wdtTabletWidth')) {
            $obj->tabletWidth = get_option('wdtTabletWidth');
        }
        if (get_option('wdtMobileWidth')) {
            $obj->mobileWidth = get_option('wdtMobileWidth');
        }
        if (get_option('wdtRenderFilter')) {
            $obj->renderFilter = get_option('wdtRenderFilter');
        }

        $obj->dataTableParams->columnDefs = json_decode('[' . $table->getColumnDefinitions() . ']');
        $obj->dataTableParams->bAutoWidth = false;

        if (!is_null($table->getDefaultSortColumn())) {
            $obj->dataTableParams->order = json_decode('[[' . $table->getDefaultSortColumn() . ', "' . strtolower($table->getDefaultSortDirection()) . '" ]]');
        } else {
            $orderColumn = 0;
            foreach ($obj->dataTableParams->columnDefs as $columnKey => $column) {
                if ($column->orderable === true) {
                    $orderColumn = $columnKey;
                    break;
                }
            }
            $obj->dataTableParams->order = json_decode('[[' . $orderColumn . ' ,"asc"]]');
        }

        if ($table->sortEnabled()) {
            $obj->dataTableParams->ordering = true;
        } else {
            $obj->dataTableParams->ordering = false;
        }
        if ($table->isFixedHeaders()) {
            $obj->dataTableParams->fixedHeader =
                array(
                    'header' => true,
                    'headerOffset' => $table->getFixedHeadersOffset(),
                );
        } else {
            $obj->dataTableParams->fixedHeader =
                array(
                    'header' => false,
                    'headerOffset' => 0,
                );
        }
        $obj->dataTableParams->fixedColumns = false;
        if ($table->isFixedColumns()) {
            $obj->dataTableParams->fixedColumns = new \stdClass();
            $obj->dataTableParams->fixedColumns->left = 1;
            if ($table->getLeftFixedColumnsNumber() !== 1)
                if ($table->getLeftFixedColumnsNumber() === 0 && $table->getRightFixedColumnsNumber() === 0) $obj->dataTableParams->fixedColumns->left = 1;
                else $obj->dataTableParams->fixedColumns->left = $table->getLeftFixedColumnsNumber();
            if ($table->getRightFixedColumnsNumber() !== 0)
                if ($table->getLeftFixedColumnsNumber() === 0) $obj->dataTableParams->fixedColumns->left = 0;
            $obj->dataTableParams->fixedColumns->right = $table->getRightFixedColumnsNumber();
        }

        if ($table->getInterfaceLanguage()) {
            $obj->dataTableParams->oLanguage = json_decode(file_get_contents($table->getInterfaceLanguage()));
        }

        if (empty($wdtExportFileName)) {
            if (!empty($table->getName())) {
                $wdtExportFileName = $table->getName();
            } else {
                $wdtExportFileName = 'wpdt_export';
            }
        }
        $currentSkin = $table->getTableSkin();
        $clearfiltersBttnText = $currentSkin == 'mojito' || $currentSkin == 'dark-mojito' ? '' : __('Clear filters', 'wpdatatables');
        if (!$table->getNoData() && $table->advancedFilterEnabled()) {
            $obj->advancedFilterEnabled = true;
            $obj->advancedFilterOptions = array();
            if (get_option('wdtRenderFilter') == 'header') {
                $obj->advancedFilterOptions['sPlaceHolder'] = "head:before";
            }
            if ($table->getFilteringForm()) {
                $obj->filterInForm = true;
            } else {
                $obj->filterInForm = false;
                if ($table->isClearFilters()) {
                    (!isset($obj->dataTableParams->buttons)) ? $obj->dataTableParams->buttons = array() : '';
                    $obj->dataTableParams->buttons[] =
                        array(
                            'text' => $clearfiltersBttnText,
                            'className' => 'wdt-clear-filters-button DTTT_button DTTT_button_clear_filters'
                        );
                }
            }
            $obj->advancedFilterOptions['aoColumns'] = json_decode('[' . $table->getColumnFilterDefinitions() . ']');
            $obj->advancedFilterOptions['bUseColVis'] = true;
        } else {
            $obj->advancedFilterEnabled = false;
        }

        $currentSkin = $table->getTableSkin();
        $skinsWithNewTableToolsButtons = ['aqua', 'purple', 'dark', 'raspberry-cream', 'mojito', 'dark-mojito'];
        $tableToolsIncludeHTML = !$table->getTableToolsIncludeHTML();
        $printBttnText = in_array($currentSkin, ['mojito',
            'raspberry-cream',
            'dark-mojito']) ? '' : __('Print', 'wpdatatables');
        $tableToolsExportTitle = $table->getTableToolsIncludeTitle() ? $table->getName() : null;
        $exportBttnText = $currentSkin == 'mojito' || $currentSkin == 'dark-mojito' ? '' : __('Export', 'wpdatatables');
        $pdfPaperSize = $table->getPdfPaperSize();
        $pdfPageOrientation = $table->getPdfPageOrientation();
        $columnsBttnText = $currentSkin == 'mojito' || $currentSkin == 'dark-mojito' ? '' : __('Columns', 'wpdatatables');


        if ($table->TTEnabled()) {
            (!isset($obj->dataTableParams->buttons)) ? $obj->dataTableParams->buttons = array() : '';
            if (in_array($currentSkin, $skinsWithNewTableToolsButtons)) {

                if (!empty($tableToolsConfig['columns'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'colvis',
                            'className' => 'DTTT_button DTTT_button_colvis',
                            'text' => $columnsBttnText,
                            'collectionLayout' => 'wdt-skin-' . $currentSkin
                        );
                }
                if (!empty($tableToolsConfig['print'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'print',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'className' => 'DTTT_button DTTT_button_print',
                            'text' => $printBttnText,
                            'title' => $wdtExportFileName
                        );
                }

                if (!empty($tableToolsConfig['excel'])) {
                    $exportButtons[] =
                        array(
                            'extend' => 'excelHtml5',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'filename' => $wdtExportFileName,
                            'title' => $tableToolsExportTitle,
                            'text' => __('Excel', 'wpdatatables')
                        );
                }
                if (!empty($tableToolsConfig['csv'])) {
                    $exportButtons[] =
                        array(
                            'extend' => 'csvHtml5',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'title' => $wdtExportFileName,
                            'text' => __('CSV', 'wpdatatables')
                        );
                }
                if (!empty($tableToolsConfig['copy'])) {
                    $exportButtons[] =
                        array(
                            'extend' => 'copyHtml5',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'filename' => $wdtExportFileName,
                            'title' => $tableToolsExportTitle,
                            'text' => __('Copy', 'wpdatatables')
                        );
                }
                if (!empty($tableToolsConfig['pdf'])) {
                    $exportButtons[] =
                        array(
                            'extend' => 'pdfHtml5',
                            'exportOptions' => array('columns' => ':visible'),
                            'orientation' => $pdfPageOrientation,
                            'pageSize' => $pdfPaperSize,
                            'title' => $wdtExportFileName,
                            'text' => __('PDF', 'wpdatatables')
                        );
                }

                if (!empty($exportButtons)) {
                    $obj->dataTableParams->buttons[] = array(
                        'extend' => 'collection',
                        'className' => 'DTTT_button DTTT_button_export',
                        'text' => $exportBttnText,
                        'buttons' => $exportButtons
                    );
                }

            } else {

                if (!empty($tableToolsConfig['columns'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'colvis',
                            'className' => 'DTTT_button DTTT_button_colvis',
                            'text' => $columnsBttnText,
                            'collectionLayout' => 'wdt-skin-' . $currentSkin
                        );
                }
                if (!empty($tableToolsConfig['print'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'print',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'className' => 'DTTT_button DTTT_button_print',
                            'title' => $wdtExportFileName,
                            'text' => $printBttnText,
                        );
                }

                if (!empty($tableToolsConfig['excel'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'excelHtml5',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'className' => 'DTTT_button DTTT_button_xls',
                            'filename' => $wdtExportFileName,
                            'title' => $tableToolsExportTitle,
                            'text' => __('Excel', 'wpdatatables')
                        );
                }
                if (!empty($tableToolsConfig['csv'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'csvHtml5',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'className' => 'DTTT_button DTTT_button_csv',
                            'title' => $wdtExportFileName,
                            'text' => __('CSV', 'wpdatatables')
                        );
                }
                if (!empty($tableToolsConfig['copy'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'copyHtml5',
                            'exportOptions' => array(
                                'columns' => ':visible',
                                'stripHtml' => $tableToolsIncludeHTML
                            ),
                            'className' => 'DTTT_button DTTT_button_copy',
                            'filename' => $wdtExportFileName,
                            'title' => $tableToolsExportTitle,
                            'text' => __('Copy', 'wpdatatables')
                        );
                }
                if (!empty($tableToolsConfig['pdf'])) {
                    $obj->dataTableParams->buttons[] =
                        array(
                            'extend' => 'pdfHtml5',
                            'exportOptions' => array('columns' => ':visible'),
                            'className' => 'DTTT_button DTTT_button_pdf',
                            'orientation' => $pdfPageOrientation,
                            'pageSize' => $pdfPaperSize,
                            'title' => $wdtExportFileName,
                            'text' => __('PDF', 'wpdatatables')
                        );
                }
            }
        }

        //[<-- Full version -->]//
        if ($table->isEditable()) {
            if (($currentSkin == 'mojito' || $currentSkin == 'dark-mojito') && $table->TTEnabled()) {
                $obj->dataTableParams->buttons[] = [
                    'text' => 'Spacer',
                    'className' => 'DTTT_button DTTT_button_spacer'
                ];
            }
            (!isset($obj->dataTableParams->buttons)) ? $obj->dataTableParams->buttons = array() : '';

            $obj->dataTableParams->editButtonsDisplayed = $table->getEditButtonsDisplayed();
            $deleteBttnText = in_array($currentSkin, ['mojito',
                'raspberry-cream',
                'dark-mojito']) ? '' : __('Delete', 'wpdatatables');
            $newEntryBttnText = $currentSkin == 'mojito' || $currentSkin == 'dark-mojito' ? '' : __('New entry', 'wpdatatables');
            $editBttnText = $currentSkin == 'mojito' || $currentSkin == 'dark-mojito' ? '' : __('Edit', 'wpdatatables');
            $duplicateBttnText = $currentSkin == 'mojito' || $currentSkin == 'dark-mojito' ? '' : __('Duplicate', 'wpdatatables');

            /** @var array $editButtons */
            $editButtons = array(
                'new_entry' => array(
                    'text' => $newEntryBttnText,
                    'className' => 'new_table_entry DTTT_button DTTT_button_new'
                ),
                'edit' => array(
                    'text' => $editBttnText,
                    'className' => 'edit_table DTTT_button DTTT_button_edit',
                    'enabled' => false
                ),
                'delete' => array(
                    'text' => $deleteBttnText,
                    'className' => 'delete_table_entry DTTT_button DTTT_button_delete',
                    'enabled' => false
                )
            );

            if ($obj->dataTableParams->editButtonsDisplayed === ['all']) {
                foreach ($editButtons as $editButton) {
                    $obj->dataTableParams->buttons[] = $editButton;
                }
            } else {
                foreach ($obj->dataTableParams->editButtonsDisplayed as $editButtonDisplayed) {
                    if (isset($editButtons[$editButtonDisplayed]))
                        $obj->dataTableParams->buttons[] = $editButtons[$editButtonDisplayed];
                }
            }

            if ($table->isEnableDuplicateButton() &&
                !empty(array_intersect(['all', 'duplicate'], $obj->dataTableParams->editButtonsDisplayed))) {
                $obj->dataTableParams->buttons[] = [
                    'text' => $duplicateBttnText,
                    'className' => 'duplicate_table_entry DTTT_button DTTT_button_duplicate',
                    'enabled' => false,
                ];
            }
            //Define the order for the edit buttons
            $order = 'text';
            $ordering = ['New Entry', 'Edit', 'Duplicate', 'Delete'];
            $compare = function ($a, $b) use ($order, $ordering) {
                $hasA = array_search($a[$order], $ordering);
                $hasB = array_search($b[$order], $ordering);
                if ($hasA === $hasB && $hasA === false) {
                    return 0;
                }
                if ($hasA !== false && $hasB !== false) {
                    return $hasA - $hasB;
                }

                return $hasA === false ? -1 : 1;
            };

            usort($obj->dataTableParams->buttons, $compare);

            $obj->advancedEditingOptions = array();
            $obj->advancedEditingOptions['aoColumns'] = json_decode('[' . $table->getColumnEditingDefinitions() . ']');
        }

        if (in_array($currentSkin, $skinsWithNewTableToolsButtons)) {

            if (!isset($obj->dataTableParams->oLanguage)) {
                $obj->dataTableParams->oLanguage = new \stdClass();
                $obj->dataTableParams->oLanguage->sSearchPlaceholder = __('Search table', 'wpdatatables');
            }

            $obj->dataTableParams->oLanguage->sSearch = '<span class="wdt-search-icon"></span>';

            if ($table->isEditable() || $table->TTEnabled() || $table->isClearFilters()) {
                if ($currentSkin != 'mojito' && $currentSkin != 'dark-mojito') {
                    $obj->dataTableParams->buttons[] = array(
                        'buttons' => ['pageLength'],
                        'className' => 'DTTT_button DTTT_button_spacer',
                        'text' => 'Spacer',
                    );
                }
            }
        } else {

            if (!isset($obj->dataTableParams->oLanguage)) {
                $obj->dataTableParams->oLanguage = new \stdClass();
            }

            $obj->dataTableParams->oLanguage->sSearchPlaceholder = '';
        }

        if ($table->getCustomStringEmptyFiltering() != '') {
            if (!isset($obj->dataTableParams->oLanguage)) {
                $obj->dataTableParams->oLanguage = new \stdClass();
            }
            $obj->dataTableParams->oLanguage->sZeroRecords = $table->getCustomStringEmptyFiltering();
        }

        //[<--/ Full version -->]//

        if (!isset($obj->dataTableParams->buttons)) {
            $obj->dataTableParams->buttons = array();
        }

        //[<-- Full version -->]//
        $obj->dataTableParams->bProcessing = false;
        if ($table->serverSide()) {
            $obj->serverSide = true;
            $obj->autoRefreshInterval = $table->getRefreshInterval();
            $obj->dataTableParams->serverSide = true;
            $obj->processing = true;
            $obj->dataTableParams->ajax = array(
                'url' => site_url() . '/wp-admin/admin-ajax.php?action=get_wdtable&table_id=' . $table->getWpId(),
                'type' => 'POST'
            );
            if (isset($wdtVar1) && $wdtVar1 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var1=' . urlencode($wdtVar1);
            }
            if (isset($wdtVar2) && $wdtVar2 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var2=' . urlencode($wdtVar2);
            }
            if (isset($wdtVar3) && $wdtVar3 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var3=' . urlencode($wdtVar3);
            }
            if (isset($wdtVar4) && $wdtVar4 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var4=' . urlencode($wdtVar4);
            }
            if (isset($wdtVar5) && $wdtVar5 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var5=' . urlencode($wdtVar5);
            }
            if (isset($wdtVar6) && $wdtVar6 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var6=' . urlencode($wdtVar6);
            }
            if (isset($wdtVar7) && $wdtVar7 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var7=' . urlencode($wdtVar7);
            }
            if (isset($wdtVar8) && $wdtVar8 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var8=' . urlencode($wdtVar8);
            }
            if (isset($wdtVar9) && $wdtVar9 !== '') {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var9=' . urlencode($wdtVar9);
            }
            if (isset($_GET['wdt_column_filter']) && is_array($_GET['wdt_column_filter'])) {
                foreach ($_GET['wdt_column_filter'] as $fltColKey => $fltDefVal) {
                    if (!is_scalar($fltColKey) || !is_scalar($fltDefVal)) {
                        continue;
                    }

                    $fltColKey = sanitize_text_field(wp_unslash($fltColKey));
                    $fltDefVal = sanitize_text_field(wp_unslash($fltDefVal));
                    $obj->dataTableParams->ajax['url'] .= '&wdt_column_filter[' . urlencode($fltColKey) . ']=' . urlencode($fltDefVal);
                }
            }
            $obj->fnServerData = true;
        } else {
            $obj->serverSide = false;
        }
        //[<--/ Full version -->]//
        $obj->columnsFixed = 0;
        //[<-- Full version -->]//
        $sumColumns = $table->getSumColumns();
        $avgColumns = $table->getAvgColumns();
        $minColumns = $table->getMinColumns();
        $maxColumns = $table->getMaxColumns();
        if (!empty($sumColumns)) {
            $obj->hasSumColumns = true;
            $obj->sumColumns = $table->getSumColumns();
        }
        if (!empty($avgColumns)) {
            $obj->hasAvgColumns = true;
            $obj->avgColumns = $table->getAvgColumns();
        }
        if (!empty($minColumns)) {
            $obj->hasMinColumns = true;
            $obj->minColumns = $table->getMinColumns();
        }
        if (!empty($maxColumns)) {
            $obj->hasMaxColumns = true;
            $obj->maxColumns = $table->getMaxColumns();
        }
        $obj->sumFunctionsLabel = get_option('wdtSumFunctionsLabel');
        $obj->avgFunctionsLabel = get_option('wdtAvgFunctionsLabel');
        $obj->minFunctionsLabel = get_option('wdtMinFunctionsLabel');
        $obj->maxFunctionsLabel = get_option('wdtMaxFunctionsLabel');
        $obj->columnsDecimalPlaces = $table->getColumnsDecimalPlaces();
        $obj->columnsThousandsSeparator = $table->getColumnsThousandsSeparator();
        $obj->sumColumns = isset($obj->sumColumns) ? $obj->sumColumns : array();
        $obj->avgColumns = isset($obj->avgColumns) ? $obj->avgColumns : array();
        $obj->sumAvgColumns = array_unique(array_merge($obj->sumColumns, $obj->avgColumns), SORT_REGULAR);

        if (!empty($table->getConditionalFormattingColumns())) {
            $obj->conditional_formatting_columns = $table->getConditionalFormattingColumns();
        }
        if (!empty($table->getTransformValueColumn())) {
            $obj->transform_value_columns = $table->getTransformValueColumn();
        }
        //[<--/ Full version -->]//
        $init_format = get_option('wdtDateFormat');
        $datepick_format = str_replace('d', 'dd', $init_format);
        $datepick_format = str_replace('m', 'mm', $datepick_format);
        $datepick_format = str_replace('Y', 'yy', $datepick_format);

        $obj->timeFormat = get_option('wdtTimeFormat');
        $obj->datepickFormat = $datepick_format;

        $obj->dataTableParams->oSearch = array(
            'bSmart' => false,
            'bRegex' => false,
            'sSearch' => $table->getDefaultSearchValue()
        );

        $obj = apply_filters('wpdatatables_filter_table_description', $obj, $table->getWpId(), $table);

        return json_encode($obj, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP);
    }
}
