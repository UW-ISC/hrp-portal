<?php

defined('ABSPATH') or die('Access denied.');

use WPDataTables\Plugin\Plugin;
use WPDataTables\Rendering\ColumnDefinitionBuilder;
use WPDataTables\Rendering\ExcelTableRenderer;

/**
 * Excel/simple (Handsontable) table variant.
 *
 * Rendering is delegated to {@see ExcelTableRenderer}; this class keeps the
 * column class override and the ajax row-formatting hook.
 */
class WPExcelDataTable extends WPDataTable
{
    protected static $_columnClass = 'WDTExcelColumn';

    /**
     * Returns JSON object for table description.
     */
    public function getJsonDescription()
    {
        return Plugin::container()->get(ExcelTableRenderer::class)->buildJsonDescription($this);
    }

    public function getColumnDefinitions()
    {
        return Plugin::container()->get(ColumnDefinitionBuilder::class)->buildExcelColumnDefinitions($this);
    }

    /**
     * Formatting row data structure for ajax display table
     *
     * @param $row - key => value pairs as column name and cell value of a row
     *
     * @return array formatted row
     */
    public function formatAjaxQueryResultRow($row)
    {
        return $row;
    }
}
