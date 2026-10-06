<?php

defined('ABSPATH') or die('Access denied.');

use WPDataTables\Plugin\Plugin;
use WPDataTables\Services\Table\TableConfigService;

/**
 * WDTConfigController
 *
 * Backward-compatibility facade. The implementation lives in
 * {@see WPDataTables\Services\Table\TableConfigService}. This class is kept as
 * a thin facade so existing call sites, templates, and third-party / pro add-ons
 * that reference `WDTConfigController::*` keep working.
 */
class WDTConfigController
{
    /**
     * Resolve the TableConfigService from the DI container.
     *
     * @return TableConfigService
     */
    private static function service()
    {
        return Plugin::container()->get(TableConfigService::class);
    }

    public static function buildSaveResult($tableData)
    {
        return self::service()->buildSaveResult($tableData);
    }

    public static function saveTableConfig($tableData)
    {
        return self::service()->saveTableConfig($tableData);
    }

    public static function loadTableConfig($tableId, $tableView = null)
    {
        return self::service()->loadTableConfig($tableId, $tableView);
    }

    public static function loadTableFromDB($tableId, $loadFromCache = true)
    {
        return self::service()->loadTableFromDB($tableId, $loadFromCache);
    }

    public static function loadColumnsFromDB($tableId, $columnNames = array())
    {
        return self::service()->loadColumnsFromDB($tableId, $columnNames);
    }

    public static function loadSingleColumnFromDB($columnId)
    {
        return self::service()->loadSingleColumnFromDB($columnId);
    }

    public static function saveTableToDB($table)
    {
        return self::service()->saveTableToDB($table);
    }

    public static function sanitizeTableConfig($table)
    {
        return self::service()->sanitizeTableConfig($table);
    }

    public static function sanitizeTableSettingsSimpleTable($table)
    {
        return self::service()->sanitizeTableSettingsSimpleTable($table);
    }

    public static function sanitizeNestedJsonParams($jsonParams)
    {
        return self::service()->sanitizeNestedJsonParams($jsonParams);
    }

    public static function sanitizeGeneratedSQLTableData($tableData)
    {
        return self::service()->sanitizeGeneratedSQLTableData($tableData);
    }

    public static function sanitizeRowDataSimpleTable($rowsData)
    {
        return self::service()->sanitizeRowDataSimpleTable($rowsData);
    }

    public static function sanitizeColumnsConfig($columns)
    {
        return self::service()->sanitizeColumnsConfig($columns);
    }

    public static function tryCreateTable($type, $content, $connection = null, $fileLocation = '')
    {
        return self::service()->tryCreateTable($type, $content, $connection, $fileLocation);
    }

    public static function saveColumns($frontendColumns, $table, $tableId)
    {
        return self::service()->saveColumns($frontendColumns, $table, $tableId);
    }

    public static function getFrontEndColumnConfig($frontendColumns, $columnOrigHeader)
    {
        return self::service()->getFrontEndColumnConfig($frontendColumns, $columnOrigHeader);
    }

    public static function prepareDBColumnConfig($column, $frontendColumns, $tableId, $pos = 0)
    {
        return self::service()->prepareDBColumnConfig($column, $frontendColumns, $tableId, $pos);
    }

    public static function saveSingleColumn($columnConfig)
    {
        return self::service()->saveSingleColumn($columnConfig);
    }

    public static function getColumnsConfig($tableId)
    {
        return self::service()->getColumnsConfig($tableId);
    }

    public static function prepareFEColumnConfig($dbColumn)
    {
        return self::service()->prepareFEColumnConfig($dbColumn);
    }

    public static function getConfigDefaults()
    {
        return self::service()->getConfigDefaults();
    }

    public static function loadSimpleTableConfig($tableID)
    {
        return self::service()->loadSimpleTableConfig($tableID);
    }

    public static function loadRowsDataFromDB($tableID)
    {
        return self::service()->loadRowsDataFromDB($tableID);
    }

    public static function loadRowsDataFromDBTemplateAll($tableID)
    {
        return self::service()->loadRowsDataFromDBTemplateAll($tableID);
    }

    public static function saveRowData($rowData, $tableID)
    {
        return self::service()->saveRowData($rowData, $tableID);
    }

    public static function createInsertStatement($tableName, $columnHeaders, $columnQuoteStart, $columnQuoteEnd)
    {
        return self::service()->createInsertStatement($tableName, $columnHeaders, $columnQuoteStart, $columnQuoteEnd);
    }

    public static function getAllTablesAndChartsForPageBuilders($builder, $type)
    {
        return self::service()->getAllTablesAndChartsForPageBuilders($builder, $type);
    }

    public static function wdt_create_chart_notice()
    {
        return self::service()->wdt_create_chart_notice();
    }

    public static function wdt_select_chart_notice()
    {
        return self::service()->wdt_select_chart_notice();
    }

    public static function wdt_create_table_notice()
    {
        return self::service()->wdt_create_table_notice();
    }

    public static function wdt_select_table_notice()
    {
        return self::service()->wdt_select_table_notice();
    }
}
