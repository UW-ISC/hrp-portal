<?php

defined('ABSPATH') or die('Access denied.');

use WPDataTables\Services\Tools\ToolsService;

/**
 * WDTTools — backward-compatibility facade (Phase J).
 *
 * Implementation lives in ToolsService and decomposed helpers under backend/src/.
 */
class WDTTools
{
    /** @var array<string, mixed> */
    public static $jsVars = array();
public static function applyPlaceholders($string)
    {
        return ToolsService::applyPlaceholders($string);
    }
public static function checkCurrentUsersActionsPermissions($tableData, $mySqlTableName, $columnsData, $id, $action)
    {
        return ToolsService::checkCurrentUsersActionsPermissions($tableData, $mySqlTableName, $columnsData, $id, $action);
    }

    public static function convertPhpToMomentDateFormat($dateFormat)
    {
        return ToolsService::convertPhpToMomentDateFormat($dateFormat);
    }

    public static function convertXMLtoArr($xml, $root = true)
    {
        return ToolsService::convertXMLtoArr($xml, $root);
    }

    public static function csvToArray($csv)
    {
        return ToolsService::csvToArray($csv);
    }

    public static function curlGetData($url)
    {
        return ToolsService::curlGetData($url);
    }

    public static function deactivatePlugin($slug)
    {
        return ToolsService::deactivatePlugin($slug);
    }

    public static function defineDefaultValue($possible, $index, $default = '')
    {
        return ToolsService::defineDefaultValue($possible, $index, $default);
    }

    public static function detectCSVDelimiter($csv_url)
    {
        return ToolsService::detectCSVDelimiter($csv_url);
    }

    public static function detectColumnDataTypes($rawDataArr, $headerArr)
    {
        return ToolsService::detectColumnDataTypes($rawDataArr, $headerArr);
    }

    public static function exportJSVar($varName, $phpVar)
    {
        return ToolsService::exportJSVar($varName, $phpVar);
    }

    public static function extractDomain($domain)
    {
        return ToolsService::extractDomain($domain);
    }

    public static function extractGoogleSpreadsheetArray($url)
    {
        return ToolsService::extractGoogleSpreadsheetArray($url);
    }

    public static function extractHeaders($rawDataArr)
    {
        return ToolsService::extractHeaders($rawDataArr);
    }

    public static function extractSubdomain($domain)
    {
        return ToolsService::extractSubdomain($domain);
    }

    public static function generateMySQLColumnName($header, $existing_headers)
    {
        return ToolsService::generateMySQLColumnName($header, $existing_headers);
    }

    public static function getColHeadersInFormula($formula, $headers)
    {
        return ToolsService::getColHeadersInFormula($formula, $headers);
    }

    public static function getConvertedTableType($tableType)
    {
        return ToolsService::getConvertedTableType($tableType);
    }

    public static function getDateTimeSettings()
    {
        return ToolsService::getDateTimeSettings();
    }

    public static function getDeactivationInfo()
    {
        return ToolsService::getDeactivationInfo();
    }

    public static function getDomain($domain)
    {
        return ToolsService::getDomain($domain);
    }

    public static function getGoogleApiMapsKey()
    {
        return ToolsService::getGoogleApiMapsKey();
    }

    public static function getGoogleSpreadsheetID($url)
    {
        return ToolsService::getGoogleSpreadsheetID($url);
    }

    public static function getGoogleWorksheetsID($url)
    {
        return ToolsService::getGoogleWorksheetsID($url);
    }

    public static function getLastTableData($filter)
    {
        return ToolsService::getLastTableData($filter);
    }

    public static function getPossibleColumnTypes(): array
    {
        return ToolsService::getPossibleColumnTypes();
    }

    public static function getRemoteInformation($slug, $purchaseCode, $envatoTokenEmail)
    {
        return ToolsService::getRemoteInformation($slug, $purchaseCode, $envatoTokenEmail);
    }

    public static function getSubDomain($subdomain)
    {
        return ToolsService::getSubDomain($subdomain);
    }

    public static function getTablesCount($filter)
    {
        return ToolsService::getTablesCount($filter);
    }

    public static function getTranslationStringsAddRemoveColumn()
    {
        return ToolsService::getTranslationStringsAddRemoveColumn();
    }

    public static function getTranslationStringsBrowse()
    {
        return ToolsService::getTranslationStringsBrowse();
    }

    public static function getTranslationStringsChartWizard()
    {
        return ToolsService::getTranslationStringsChartWizard();
    }

    public static function getTranslationStringsColumnFilter()
    {
        return ToolsService::getTranslationStringsColumnFilter();
    }

    public static function getTranslationStringsCommon()
    {
        return ToolsService::getTranslationStringsCommon();
    }

    public static function getTranslationStringsConstructor()
    {
        return ToolsService::getTranslationStringsConstructor();
    }

    public static function getTranslationStringsExcel()
    {
        return ToolsService::getTranslationStringsExcel();
    }

    public static function getTranslationStringsExcelPlugin()
    {
        return ToolsService::getTranslationStringsExcelPlugin();
    }

    public static function getTranslationStringsFunctions()
    {
        return ToolsService::getTranslationStringsFunctions();
    }

    public static function getTranslationStringsInlineEditing()
    {
        return ToolsService::getTranslationStringsInlineEditing();
    }

    public static function getTranslationStringsPlugin()
    {
        return ToolsService::getTranslationStringsPlugin();
    }

    public static function getTranslationStringsSimpleTable()
    {
        return ToolsService::getTranslationStringsSimpleTable();
    }

    public static function getTranslationStringsTableSettingsMain()
    {
        return ToolsService::getTranslationStringsTableSettingsMain();
    }

    public static function getTranslationStringsWpDataTables()
    {
        return ToolsService::getTranslationStringsWpDataTables();
    }

    public static function getTranslationStringsFolders()
    {
        return ToolsService::getTranslationStringsFolders();
    }

    public static function getTutorialsTranslationStrings()
    {
        return ToolsService::getTutorialsTranslationStrings();
    }

    public static function getUpdateInfo()
    {
        return ToolsService::getUpdateInfo();
    }

    public static function getWpDataTablesAdminPages()
    {
        return ToolsService::getWpDataTablesAdminPages();
    }

    public static function gsArrayToWDTArray($arr)
    {
        return ToolsService::gsArrayToWDTArray($arr);
    }

    public static function hex2rgba($color, $opacity = false)
    {
        return ToolsService::hex2rgba($color, $opacity);
    }

    public static function isArrayAssoc($arr)
    {
        return ToolsService::isArrayAssoc($arr);
    }

    public static function isHtml($string)
    {
        return ToolsService::isHtml($string);
    }

    public static function isIP($domain)
    {
        return ToolsService::isIP($domain);
    }

    public static function isStringAColor($color)
    {
        return ToolsService::isStringAColor($color);
    }

    public static function isWpdtSafeWebUrl($url)
    {
        return ToolsService::isWpdtSafeWebUrl($url);
    }

    public static function pathToUrl($uploadPath)
    {
        return ToolsService::pathToUrl($uploadPath);
    }

    public static function prepareStringCell($string, $connection)
    {
        return ToolsService::prepareStringCell($string, $connection);
    }

    public static function filterFormDataToKnownColumns($formData, $columnsData)
    {
        return ToolsService::filterFormDataToKnownColumns($formData, $columnsData);
    }

    public static function formatSqlWhereIdValue($value, $columnType, $connection)
    {
        return ToolsService::formatSqlWhereIdValue($value, $columnType, $connection);
    }

    public static function prepareSearchLiteral($value, $connection)
    {
        return ToolsService::prepareSearchLiteral($value, $connection);
    }

    public static function buildLikeComparison($qualifiedColumn, $rawValue, $connection, $prefix = '%', $suffix = '%', $useLowerCast = false)
    {
        return ToolsService::buildLikeComparison($qualifiedColumn, $rawValue, $connection, $prefix, $suffix, $useLowerCast);
    }

    public static function buildInListFromKeys($values, $connection)
    {
        return ToolsService::buildInListFromKeys($values, $connection);
    }

    public static function printJSVars()
    {
        return ToolsService::printJSVars();
    }

    public static function removeWWW($url)
    {
        return ToolsService::removeWWW($url);
    }

    public static function sanitizeCellHtmlUrlAttribute($attrLower, $raw)
    {
        return ToolsService::sanitizeCellHtmlUrlAttribute($attrLower, $raw);
    }

    public static function sanitizeEmailCellValueForDb($value)
    {
        return ToolsService::sanitizeEmailCellValueForDb($value);
    }

    public static function sanitizeHeaders($headersInFormula): array
    {
        return ToolsService::sanitizeHeaders($headersInFormula);
    }

    public static function sanitizeImageCellValueForDb($value)
    {
        return ToolsService::sanitizeImageCellValueForDb($value);
    }

    public static function sanitizeLinkCellValueForDb($value)
    {
        return ToolsService::sanitizeLinkCellValueForDb($value);
    }

    public static function slugify($text)
    {
        return ToolsService::slugify($text);
    }

    public static function stripJsAttributes($htmlString)
    {
        return ToolsService::stripJsAttributes($htmlString);
    }

    public static function urlToPath($uploadUrl)
    {
        return ToolsService::urlToPath($uploadUrl);
    }

    public static function wdtConvertStringToUnixTimestamp($dateString, $dateFormat)
    {
        return ToolsService::wdtConvertStringToUnixTimestamp($dateString, $dateFormat);
    }

    public static function wdtConvertUnixTimestampToString($columnType, $displayColumnNameData)
    {
        return ToolsService::wdtConvertUnixTimestampToString($columnType, $displayColumnNameData);
    }

    public static function wdtUIKitEnqueue()
    {
        return ToolsService::wdtUIKitEnqueue();
    }

    public static function wdtUIKitEnqueueNotEdit()
    {
        return ToolsService::wdtUIKitEnqueueNotEdit();
    }

    public static function wrapQuotes($value, $connection)
    {
        return ToolsService::wrapQuotes($value, $connection);
    }

    public static function wdtShowError($errorMessage)
    {
        return ToolsService::wdtShowError($errorMessage);
    }
}

add_action('admin_footer', array('WDTTools', 'printJSVars'), 100);
add_action('wp_footer', array('WDTTools', 'printJSVars'), 100);
