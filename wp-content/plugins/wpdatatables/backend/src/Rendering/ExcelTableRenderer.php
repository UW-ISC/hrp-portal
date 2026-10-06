<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Rendering;

use WPDataTables\Services\Tools\ToolsService;

use WPExcelDataTable;

/**
 * HTML assembly and JSON description for Excel/simple (Handsontable) tables.
 *
 * The legacy {@see \WPExcelDataTable::generateTable()},
 * {@see \WPExcelDataTable::renderWithJSAndStyles()} and
 * {@see \WPExcelDataTable::getJsonDescription()} are thin delegators into
 * this service.
 *
 * @package WPDataTables\Rendering
 */
class ExcelTableRenderer
{
    /** @var TemplateEngine */
    private $templateEngine;

    /** @var AssetManager */
    private $assetManager;

    /** @var ColumnDefinitionBuilder */
    private $columnDefinitionBuilder;

    public function __construct(
        TemplateEngine $templateEngine,
        AssetManager $assetManager,
        ColumnDefinitionBuilder $columnDefinitionBuilder
    ) {
        $this->templateEngine          = $templateEngine;
        $this->assetManager            = $assetManager;
        $this->columnDefinitionBuilder = $columnDefinitionBuilder;
    }

    /**
     * Enqueue Excel assets and render the `excel_table_main` template.
     *
     * @param WPExcelDataTable $table
     *
     * @return string
     */
    public function renderWithAssets(WPExcelDataTable $table)
    {
        $this->assetManager->enqueueExcel($table);

        $table->addCSSClass('data-t');

        return $this->templateEngine->render(
            $table,
            WDT_TEMPLATE_PATH . 'frontend/excel_table_main.inc.php'
        );
    }

    /**
     * Assemble the full Excel-table HTML (wrap template + filter hook).
     *
     * @param WPExcelDataTable $table
     *
     * @return string
     */
    public function generate(WPExcelDataTable $table)
    {
        $cssArray = array(
            'wpdatatables-handsontable-min' => WDT_CSS_PATH . 'handsontable.full.min.css',
            'wpdatatables-excel-min'        => WDT_CSS_PATH . 'wpdatatables-excel.min.css',
        );
        foreach ($cssArray as $cssKey => $cssFile) {
            wp_enqueue_style($cssKey, $cssFile, array(), WDT_CURRENT_VERSION);
        }

        $tableContent = $this->renderWithAssets($table);

        $returnData = $this->templateEngine->render(
            $table,
            WDT_TEMPLATE_PATH . 'frontend/wrap_template.inc.php',
            array('tableContent' => $tableContent)
        );

        return apply_filters('wpdatatables_excel_filter_table_template', $returnData, $table->getWpId());
    }

    /**
     * Build the Handsontable table-description JSON.
     *
     * @param WPExcelDataTable $table
     *
     * @return string
     */
    public function buildJsonDescription(WPExcelDataTable $table)
    {
        global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9;

        $obj = new \stdClass();
        $obj->tableId = $table->getId();
        $obj->selector = '#' . $table->getId();
        $obj->tableWpId = $table->getWpId();
        $obj->responsive = $table->isResponsive();
        $obj->editable = $table->isEditable();

        $obj->decimalPlaces = (int)(get_option('wdtDecimalPlaces') ? get_option('wdtDecimalPlaces') : 2);

        $obj->dataTableParams = new \StdClass();
        $obj->dataTableParams->number_format = (int)(get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1);
        $obj->dataTableParams->readOnly = !$table->isEditable();
        $obj->dataTableParams->allowInvalid = false;

        $init_date_format = get_option('wdtDateFormat');
        $obj->dataTableParams->displayDateFormat = ToolsService::convertPhpToMomentDateFormat($init_date_format);
        $obj->dataTableParams->dataSourceDateFormat = ToolsService::convertPhpToMomentDateFormat('Y-m-d');
        $timeFormat = get_option('wdtTimeFormat');

        $obj->dataTableParams->origTimeFormat = $timeFormat;
        $obj->dataTableParams->timepickTimeFormat = str_replace('H', 'HH', $timeFormat);
        $obj->dataTableParams->momentTimeFormat = str_replace('i', 'mm', $timeFormat);

        if ($table->isEditable()) {
            $obj->dataTableParams->adminAjaxBaseUrl = site_url() . '/wp-admin/admin-ajax.php';
            $obj->dataTableParams->idColumnIndex = $table->getColumnHeaderOffset($table->getIdColumnKey());
            $obj->dataTableParams->idColumnKey = $table->getIdColumnKey();
            $obj->dataTableParams->dateFormat = $obj->dataTableParams->displayDateFormat;
            $obj->dataTableParams->datePickerConfig = array('format' => $obj->dataTableParams->displayDateFormat);
            $obj->dataTableParams->dataSourceDateFormat = ToolsService::convertPhpToMomentDateFormat('Y-m-d');
        }
        $obj->dataTableParams->columns = $this->columnDefinitionBuilder->buildExcelColumnDefinitions($table);

        if ($table->sortEnabled()) {
            $sort_column = 0;
            $sort_direction = true;

            if (!is_null($table->getDefaultSortColumn())) {
                $sort_column = $table->getDefaultSortColumn();

                if (strtolower($table->getDefaultSortDirection()) == 'desc') {
                    $sort_direction = false;
                }
            }

            $obj->dataTableParams->columnSorting = array('column' => $sort_column, 'sortOrder' => $sort_direction);
            $obj->dataTableParams->sortIndicator = true;
        } else {
            $obj->dataTableParams->columnSorting = false;
        }

        if ($table->serverSide()) {
            $obj->serverSide = true;
            $obj->dataTableParams->serverSide = true;

            $obj->dataTableParams->ajax = array(
                'url'  => site_url() . '/wp-admin/admin-ajax.php?action=get_wdtable&table_id=' . $table->getWpId(),
                'type' => 'POST',
            );
            if (!empty($wdtVar1)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var1=' . urlencode($wdtVar1);
            }
            if (!empty($wdtVar2)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var2=' . urlencode($wdtVar2);
            }
            if (!empty($wdtVar3)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var3=' . urlencode($wdtVar3);
            }
            if (!empty($wdtVar4)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var4=' . urlencode($wdtVar4);
            }
            if (!empty($wdtVar5)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var5=' . urlencode($wdtVar5);
            }
            if (!empty($wdtVar6)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var6=' . urlencode($wdtVar6);
            }
            if (!empty($wdtVar7)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var7=' . urlencode($wdtVar7);
            }
            if (!empty($wdtVar8)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var8=' . urlencode($wdtVar8);
            }
            if (!empty($wdtVar9)) {
                $obj->dataTableParams->ajax['url'] .= '&wdt_var9=' . urlencode($wdtVar9);
            }
        } else {
            $obj->serverSide = false;
        }

        if (get_option('wdtTabletWidth')) {
            $obj->tabletWidth = get_option('wdtTabletWidth');
        }
        if (get_option('wdtMobileWidth')) {
            $obj->mobileWidth = get_option('wdtMobileWidth');
        }

        $obj->dataTableParams->search = true;
        $obj->dataTableParams->searchDefaultValue = $table->getDefaultSearchValue();

        $obj = apply_filters('wpdatatables_excel_filter_table_description', $obj, $table->getWpId());

        return json_encode($obj, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP);
    }
}
