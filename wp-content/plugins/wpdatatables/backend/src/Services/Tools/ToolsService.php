<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Tools;

use WPDataTables\Common\Helpers\ColumnNamingHelper;
use WPDataTables\Common\Helpers\DataImportHelper;
use WPDataTables\Common\Helpers\DateTimeHelper;
use WPDataTables\Common\Helpers\UrlHelper;
use WPDataTables\Common\Sanitizer\CellSanitizer;
use WPDataTables\Infrastructure\Http\HttpClient;
use WPDataTables\Services\Activation\LicenseService;
use WPDataTables\Services\Admin\DashboardStatsService;
use WPDataTables\Services\Assets\UIKitAssetLoader;
use WPDataTables\Services\Localization\TranslationStringsProvider;
use WPDataTables\Services\Localization\TutorialStringsProvider;
use WPDataTables\Services\Permissions\TableRowActionPermissionsChecker;

/**
 * Aggregates decomposed WDTTools helpers (Phase J).
 *
 * @package WPDataTables\Services\Tools
 */
class ToolsService
{
public static function applyPlaceholders($string)
    {
        return HttpClient::applyPlaceholders($string);
    }
public static function checkCurrentUsersActionsPermissions($tableData, $mySqlTableName, $columnsData, $id, $action)
    {
        return TableRowActionPermissionsChecker::checkCurrentUsersActionsPermissions($tableData, $mySqlTableName, $columnsData, $id, $action);
    }

    public static function convertPhpToMomentDateFormat($dateFormat)
    {
        return DateTimeHelper::convertPhpToMomentDateFormat($dateFormat);
    }

    public static function convertXMLtoArr($xml, $root = true)
    {
        return DataImportHelper::convertXMLtoArr($xml, $root);
    }

    public static function csvToArray($csv)
    {
        return DataImportHelper::csvToArray($csv);
    }

    public static function curlGetData($url)
    {
        return HttpClient::curlGetData($url);
    }

    public static function deactivatePlugin($slug)
    {
        return LicenseService::deactivatePlugin($slug);
    }

    public static function defineDefaultValue($possible, $index, $default = '')
    {
        return DataImportHelper::defineDefaultValue($possible, $index, $default);
    }

    public static function detectCSVDelimiter($csv_url)
    {
        return DataImportHelper::detectCSVDelimiter($csv_url);
    }

    public static function detectColumnDataTypes($rawDataArr, $headerArr)
    {
        return DataImportHelper::detectColumnDataTypes($rawDataArr, $headerArr);
    }

    public static function exportJSVar($varName, $phpVar)
    {
        return UIKitAssetLoader::exportJSVar($varName, $phpVar);
    }

    public static function extractDomain($domain)
    {
        return UrlHelper::extractDomain($domain);
    }

    public static function extractGoogleSpreadsheetArray($url)
    {
        return DataImportHelper::extractGoogleSpreadsheetArray($url);
    }

    public static function extractHeaders($rawDataArr)
    {
        return DataImportHelper::extractHeaders($rawDataArr);
    }

    public static function extractSubdomain($domain)
    {
        return UrlHelper::extractSubdomain($domain);
    }

    public static function generateMySQLColumnName($header, $existing_headers)
    {
        return ColumnNamingHelper::generateMySQLColumnName($header, $existing_headers);
    }

    public static function getColHeadersInFormula($formula, $headers)
    {
        return ColumnNamingHelper::getColHeadersInFormula($formula, $headers);
    }

    public static function getConvertedTableType($tableType)
    {
        return DashboardStatsService::getConvertedTableType($tableType);
    }

    public static function getDateTimeSettings()
    {
        return DateTimeHelper::getDateTimeSettings();
    }

    public static function getDeactivationInfo()
    {
        return LicenseService::getDeactivationInfo();
    }

    public static function getDomain($domain)
    {
        return UrlHelper::getDomain($domain);
    }

    public static function getGoogleApiMapsKey()
    {
        return LicenseService::getGoogleApiMapsKey();
    }

    public static function getGoogleSpreadsheetID($url)
    {
        return DataImportHelper::getGoogleSpreadsheetID($url);
    }

    public static function getGoogleWorksheetsID($url)
    {
        return DataImportHelper::getGoogleWorksheetsID($url);
    }

    public static function getLastTableData($filter)
    {
        return DashboardStatsService::getLastTableData($filter);
    }

    public static function getPossibleColumnTypes(): array
    {
        return ColumnNamingHelper::getPossibleColumnTypes();
    }

    public static function getRemoteInformation($slug, $purchaseCode, $envatoTokenEmail)
    {
        return LicenseService::getRemoteInformation($slug, $purchaseCode, $envatoTokenEmail);
    }

    public static function getSubDomain($subdomain)
    {
        return UrlHelper::getSubDomain($subdomain);
    }

    public static function getTablesCount($filter)
    {
        return DashboardStatsService::getTablesCount($filter);
    }

    public static function getTranslationStringsAddRemoveColumn()
    {
        return TranslationStringsProvider::getTranslationStringsAddRemoveColumn();
    }

    public static function getTranslationStringsBrowse()
    {
        return TranslationStringsProvider::getTranslationStringsBrowse();
    }

    public static function getTranslationStringsChartWizard()
    {
        return TranslationStringsProvider::getTranslationStringsChartWizard();
    }

    public static function getTranslationStringsColumnFilter()
    {
        return TranslationStringsProvider::getTranslationStringsColumnFilter();
    }

    public static function getTranslationStringsCommon()
    {
        return TranslationStringsProvider::getTranslationStringsCommon();
    }

    public static function getTranslationStringsConstructor()
    {
        return TranslationStringsProvider::getTranslationStringsConstructor();
    }

    public static function getTranslationStringsExcel()
    {
        return TranslationStringsProvider::getTranslationStringsExcel();
    }

    public static function getTranslationStringsExcelPlugin()
    {
        return TranslationStringsProvider::getTranslationStringsExcelPlugin();
    }

    public static function getTranslationStringsFunctions()
    {
        return TranslationStringsProvider::getTranslationStringsFunctions();
    }

    public static function getTranslationStringsInlineEditing()
    {
        return TranslationStringsProvider::getTranslationStringsInlineEditing();
    }

    public static function getTranslationStringsPlugin()
    {
        return TranslationStringsProvider::getTranslationStringsPlugin();
    }

    public static function getTranslationStringsSimpleTable()
    {
        return TranslationStringsProvider::getTranslationStringsSimpleTable();
    }

    public static function getTranslationStringsTableSettingsMain()
    {
        return TranslationStringsProvider::getTranslationStringsTableSettingsMain();
    }

    public static function getTranslationStringsWpDataTables()
    {
        return TranslationStringsProvider::getTranslationStringsWpDataTables();
    }

    public static function getTranslationStringsFolders()
    {
        return TranslationStringsProvider::getTranslationStringsFolders();
    }

    public static function getTutorialsTranslationStrings()
    {
        return TutorialStringsProvider::getTutorialsTranslationStrings();
    }

    public static function getUpdateInfo()
    {
        return LicenseService::getUpdateInfo();
    }

    public static function getWpDataTablesAdminPages()
    {
        return DashboardStatsService::getWpDataTablesAdminPages();
    }

    public static function gsArrayToWDTArray($arr)
    {
        return DataImportHelper::gsArrayToWDTArray($arr);
    }

    public static function hex2rgba($color, $opacity = false)
    {
        return UrlHelper::hex2rgba($color, $opacity);
    }

    public static function isArrayAssoc($arr)
    {
        return DataImportHelper::isArrayAssoc($arr);
    }

    public static function isHtml($string)
    {
        return CellSanitizer::isHtml($string);
    }

    public static function isIP($domain)
    {
        return UrlHelper::isIP($domain);
    }

    public static function isStringAColor($color)
    {
        return UrlHelper::isStringAColor($color);
    }

    public static function isWpdtSafeWebUrl($url)
    {
        return CellSanitizer::isWpdtSafeWebUrl($url);
    }

    public static function pathToUrl($uploadPath)
    {
        return UrlHelper::pathToUrl($uploadPath);
    }

    public static function prepareStringCell($string, $connection)
    {
        return CellSanitizer::prepareStringCell($string, $connection);
    }

    public static function filterFormDataToKnownColumns(array $formData, array $columnsData): array
    {
        return \WPDataTables\Common\Helpers\SqlHelper::filterFormDataToKnownColumns($formData, $columnsData);
    }

    public static function formatSqlWhereIdValue($value, string $columnType, $connection)
    {
        return \WPDataTables\Common\Helpers\SqlHelper::formatSqlWhereIdValue($value, $columnType, $connection);
    }

    public static function prepareSearchLiteral(string $value, $connection): string
    {
        return \WPDataTables\Common\Helpers\SqlHelper::prepareSearchLiteral($value, $connection);
    }

    public static function buildLikeComparison(
        string $qualifiedColumn,
        string $rawValue,
        $connection,
        string $prefix = '%',
        string $suffix = '%',
        bool $useLowerCast = false
    ): string {
        return \WPDataTables\Common\Helpers\SqlHelper::buildLikeComparison(
            $qualifiedColumn,
            $rawValue,
            $connection,
            $prefix,
            $suffix,
            $useLowerCast
        );
    }

    public static function buildInListFromKeys(array $values, $connection): string
    {
        return \WPDataTables\Common\Helpers\SqlHelper::buildInListFromKeys($values, $connection);
    }

    public static function printJSVars()
    {
        return UIKitAssetLoader::printJSVars();
    }

    public static function removeWWW($url)
    {
        return UrlHelper::removeWWW($url);
    }

    public static function sanitizeCellHtmlUrlAttribute($attrLower, $raw)
    {
        return CellSanitizer::sanitizeCellHtmlUrlAttribute($attrLower, $raw);
    }

    public static function sanitizeEmailCellValueForDb($value)
    {
        return CellSanitizer::sanitizeEmailCellValueForDb($value);
    }

    public static function sanitizeHeaders($headersInFormula): array
    {
        return ColumnNamingHelper::sanitizeHeaders($headersInFormula);
    }

    public static function sanitizeImageCellValueForDb($value)
    {
        return CellSanitizer::sanitizeImageCellValueForDb($value);
    }

    public static function sanitizeLinkCellValueForDb($value)
    {
        return CellSanitizer::sanitizeLinkCellValueForDb($value);
    }

    public static function slugify($text)
    {
        return ColumnNamingHelper::slugify($text);
    }

    public static function stripJsAttributes($htmlString)
    {
        return CellSanitizer::stripJsAttributes($htmlString);
    }

    public static function urlToPath($uploadUrl)
    {
        return UrlHelper::urlToPath($uploadUrl);
    }

    public static function wdtConvertStringToUnixTimestamp($dateString, $dateFormat)
    {
        return DateTimeHelper::wdtConvertStringToUnixTimestamp($dateString, $dateFormat);
    }

    public static function wdtConvertUnixTimestampToString($columnType, $displayColumnNameData)
    {
        return DateTimeHelper::wdtConvertUnixTimestampToString($columnType, $displayColumnNameData);
    }

    public static function wdtUIKitEnqueue()
    {
        return UIKitAssetLoader::wdtUIKitEnqueue();
    }

    public static function wdtUIKitEnqueueNotEdit()
    {
        return UIKitAssetLoader::wdtUIKitEnqueueNotEdit();
    }

    public static function wrapQuotes($value, $connection)
    {
        return CellSanitizer::wrapQuotes($value, $connection);
    }

    /**
     * Show error message
     *
     * @param $errorMessage
     *
     * @return string
     */
    public static function wdtShowError($errorMessage)
    {
        UIKitAssetLoader::wdtUIKitEnqueue();
        ob_start();
        include WDT_ROOT_PATH . 'templates/common/error.inc.php';
        $errorBlock = ob_get_contents();
        ob_end_clean();
        return $errorBlock;
    }
}
