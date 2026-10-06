<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\InstallActions;

use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Services\InstallActions\ActivationCapabilitiesHook;
use WPDataTables\Services\InstallActions\DB\CacheTable;
use WPDataTables\Services\Permissions\AccessRulesMigrator;
use WPDataTables\Services\InstallActions\DB\ChartsTable;
use WPDataTables\Services\InstallActions\DB\ColumnsTable;
use WPDataTables\Services\InstallActions\DB\RowsTable;
use WPDataTables\Services\InstallActions\DB\TablesTable;
use WPDataTables\Services\InstallActions\DB\TemplatesTable;

/**
 * Plugin activation actions.
 *
 * Owns the install/activation routine: creates (or upgrades via `dbDelta`) the
 * six `wpdatatables_*` tables through the `DB\*Table` descriptors, seeds default
 * options, and installs the standard simple-table templates. The legacy
 * `wdtActivation*` functions in {@see \WPDataTables\Legacy\GlobalFunctions} are thin shims
 * delegating here. Multisite/network-activation behaviour is preserved.
 *
 * @package WPDataTables\Services\InstallActions
 */
class ActivationHook
{
    /**
     * Activation entry point.
     *
     * @param bool $networkWide Whether the plugin is being network-activated.
     * @throws InvalidArgumentException
     */
    public static function activate($networkWide): void
    {
        global $wpdb;

        if (function_exists('is_multisite') && is_multisite()) {
            // Check if it is a network activation; if so run for each blog id.
            if ($networkWide) {
                $oldBlog = $wpdb->blogid;
                // Get all blog ids
                $blogIds = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

                foreach ($blogIds as $blogId) {
                    switch_to_blog($blogId);
                    // Create database tables if not exists
                    self::createTables();
                    // Insert standard templates if not exists
                    $tableName = TemplatesTable::getTableName();
                    $rowCount = $wpdb->get_var("SELECT COUNT(*) FROM {$tableName}");
                    self::checkSimpleTemplatesActivation($rowCount, $tableName);
                }
                switch_to_blog($oldBlog);

                return;
            }
        }
        // Create database tables if not exists
        self::createTables();
        // Insert standard templates if not exists
        $tableName = TemplatesTable::getTableName();
        $rowCount = $wpdb->get_var("SELECT COUNT(*) FROM {$tableName}");
        self::checkSimpleTemplatesActivation($rowCount, $tableName);
    }

    /**
     * Create/upgrade the plugin tables and seed default options.
     *
     * @throws InvalidArgumentException
     */
    public static function createTables(): void
    {
        TablesTable::init();
        ColumnsTable::init();
        ChartsTable::init();
        RowsTable::init();
        CacheTable::init();
        TemplatesTable::init();

        self::setDefaultOptions();
        ActivationCapabilitiesHook::ensureAdministratorCaps();
        AccessRulesMigrator::runIfNeeded();

        do_action('wpdatatables_after_activation_method');
    }

    /**
     * Seed plugin default options (only where not already set).
     */
    private static function setDefaultOptions(): void
    {
        if (!get_option('wdtUseSeparateCon')) {
            update_option('wdtUseSeparateCon', false);
        }
        if (!get_option('wdtSeparateCon')) {
            update_option('wdtSeparateCon', false);
        }
        if (!get_option('wdtRenderCharts')) {
            update_option('wdtRenderCharts', 'below');
        }
        if (!get_option('wdtRenderFilter')) {
            update_option('wdtRenderFilter', 'footer');
        }
        if (!get_option('wdtRenderFilter')) {
            update_option('wdtTopOffset', '0');
        }
        if (!get_option('wdtLeftOffset')) {
            update_option('wdtLeftOffset', '0');
        }
        if (!get_option('wdtBaseSkin')) {
            update_option('wdtBaseSkin', 'light');
        }
        if (get_option('wdtBaseSkin') && get_option('wdtBaseSkin') == 'skin0') {
            update_option('wdtBaseSkin', 'material');
        }
        if (get_option('wdtBaseSkin') && get_option('wdtBaseSkin') == 'skin1') {
            update_option('wdtBaseSkin', 'light');
        }
        if (get_option('wdtBaseSkin') && get_option('wdtBaseSkin') == 'skin2') {
            update_option('wdtBaseSkin', 'graphite');
        }
        if (!get_option('wdtTimeFormat')) {
            update_option('wdtTimeFormat', 'h:i A');
        }
        if (!get_option('wdtTimeFormat')) {
            update_option('wdtTimeFormat', 'h:i A');
        }
        if (!get_option('wdtInterfaceLanguage')) {
            update_option('wdtInterfaceLanguage', '');
        }
        if (!get_option('wdtTablesPerPage')) {
            update_option('wdtTablesPerPage', 10);
        }
        if (!get_option('wdtNumberFormat')) {
            update_option('wdtNumberFormat', 1);
        }
        if (!get_option('wdtDecimalPlaces')) {
            update_option('wdtDecimalPlaces', 2);
        }
        if (!get_option('wdtCSVDelimiter')) {
            update_option('wdtCSVDelimiter', ',');
        }
        if (!get_option('wdtSortingOrderBrowseTables')) {
            update_option('wdtSortingOrderBrowseTables', 'ASC');
        }
        if (!get_option('wdtDateFormat')) {
            update_option('wdtDateFormat', 'd/m/Y');
        }
        if (get_option('wdtAutoUpdateOption') === false) {
            update_option('wdtAutoUpdateOption', 0);
        }
        if (get_option('wdtParseShortcodes') === false) {
            update_option('wdtParseShortcodes', false);
        }
        if (get_option('wdtNumbersAlign') === false) {
            update_option('wdtNumbersAlign', true);
        }
        if (get_option('wdtBorderRemoval') === false) {
            update_option('wdtBorderRemoval', 0);
        }
        if (get_option('wdtBorderRemovalHeader') === false) {
            update_option('wdtBorderRemovalHeader', 0);
        }
        if (!get_option('wdtFontColorSettings')) {
            update_option('wdtFontColorSettings', '');
        }
        if (!get_option('wdtCustomJs')) {
            update_option('wdtCustomJs', '');
        }
        if (!get_option('wdtCustomCss')) {
            update_option('wdtCustomCss', '');
        }
        if (!get_option('wdtGoogleSettings')) {
            update_option('wdtGoogleSettings', '');
        }
        if (!get_option('wdtGoogleToken')) {
            update_option('wdtGoogleToken', '');
        }
        if (get_option('wdtMinifiedJs') === false) {
            update_option('wdtMinifiedJs', 1);
        }
        if (!get_option('wdtTabletWidth')) {
            update_option('wdtTabletWidth', 1024);
        }
        if (!get_option('wdtMobileWidth')) {
            update_option('wdtMobileWidth', 480);
        }
        if (get_option('wdtGettingStartedPageStatus') === false) {
            update_option('wdtGettingStartedPageStatus', 0);
        }
        if (get_option('wdtLiteVSPremiumPageStatus') === false) {
            update_option('wdtLiteVSPremiumPageStatus', 0);
        }
        if (get_option('wdtIncludeGoogleFonts') === false) {
            update_option('wdtIncludeGoogleFonts', true);
        }
        if (get_option('wdtIncludeBootstrap') === false) {
            update_option('wdtIncludeBootstrap', true);
        }
        if (get_option('wdtIncludeBootstrapBackEnd') === false) {
            update_option('wdtIncludeBootstrapBackEnd', true);
        }
        if (get_option('wdtPreventDeletingTables') === false) {
            update_option('wdtPreventDeletingTables', true);
        }
        if (!get_option('wdtActivated')) {
            update_option('wdtActivated', 0);
        }
        if (!get_option('wdtPurchaseCodeStore')) {
            update_option('wdtPurchaseCodeStore', '');
        }
        if (!get_option('wdtEnvatoTokenEmail')) {
            update_option('wdtEnvatoTokenEmail', '');
        }
        if (!get_option('wdtActivatedPowerful')) {
            update_option('wdtActivatedPowerful', 0);
        }
        if (!get_option('wdtPurchaseCodeStorePowerful')) {
            update_option('wdtPurchaseCodeStorePowerful', '');
        }
        if (!get_option('wdtEnvatoTokenEmailPowerful')) {
            update_option('wdtEnvatoTokenEmailPowerful', '');
        }
        if (!get_option('wdtActivatedReport')) {
            update_option('wdtActivatedReport', 0);
        }
        if (!get_option('wdtActivatedMasterDetail')) {
            update_option('wdtActivatedMasterDetail', 0);
        }
        if (!get_option('wdtPurchaseCodeStoreMasterDetail')) {
            update_option('wdtPurchaseCodeStoreMasterDetail', '');
        }
        if (!get_option('wdtPurchaseCodeStoreReport')) {
            update_option('wdtPurchaseCodeStoreReport', '');
        }
        if (!get_option('wdtEnvatoTokenEmailReport')) {
            update_option('wdtEnvatoTokenEmailReport', '');
        }
        if (!get_option('wdtActivatedGravity')) {
            update_option('wdtActivatedGravity', 0);
        }
        if (!get_option('wdtPurchaseCodeStoreGravity')) {
            update_option('wdtPurchaseCodeStoreGravity', '');
        }
        if (!get_option('wdtEnvatoTokenEmailGravity')) {
            update_option('wdtEnvatoTokenEmailGravity', '');
        }
        if (!get_option('wdtActivatedFormidable')) {
            update_option('wdtActivatedFormidable', 0);
        }
        if (!get_option('wdtPurchaseCodeStoreFormidable')) {
            update_option('wdtPurchaseCodeStoreFormidable', '');
        }
        if (!get_option('wdtEnvatoTokenEmailFormidable')) {
            update_option('wdtEnvatoTokenEmailFormidable', '');
        }
        if (get_option('wdtInstallDate') === false) {
            update_option('wdtInstallDate', date('Y-m-d'));
        }
        if (get_option('wdtRatingDiv') === false) {
            update_option('wdtRatingDiv', 'no');
        }
        if (get_option('wdtShowForminatorNotice') === false) {
            update_option('wdtShowForminatorNotice', 'yes');
        }
        if (get_option('wdtMDNewsDiv') === false) {
            update_option('wdtMDNewsDiv', 'no');
        }
        if (get_option('wdtSimpleTableAlert') === false) {
            update_option('wdtSimpleTableAlert', true);
        }
        if (get_option('wdtTempFutureDate') === false) {
            update_option('wdtTempFutureDate', date('Y-m-d'));
        }
        if (!get_option('wdtAutoUpdateHash')) {
            update_option('wdtAutoUpdateHash', bin2hex(openssl_random_pseudo_bytes(22)));
        }
        if (get_option('wdtGoogleStableVersion') === false) {
            update_option('wdtGoogleStableVersion', 1);
        }
        if (get_option('wdtApexStableVersion') === false) {
            update_option('wdtApexStableVersion', 1);
        }
        if (get_option('wdtShowBundlesNotice') === false) {
            update_option('wdtShowBundlesNotice', 'yes');
        }
        if (get_option('wdtShowAmeliaBanner') === false) {
            update_option('wdtShowAmeliaBanner', 'yes');
        }
        if (!get_option('wdtGoogleApiMaps')) {
            update_option('wdtGoogleApiMaps', '');
        }
        if (!get_option('wdtGoogleApiMapsValidated')) {
            update_option('wdtGoogleApiMapsValidated', 0);
        }
        if (!get_option('wdtActivationSimpleTableTemplates')) {
            update_option('wdtActivationSimpleTableTemplates', 'no');
        }
        if (!get_option('wdtGlobalTableLoader')) {
            update_option('wdtGlobalTableLoader', 1);
        }
        if (!get_option('wdtGlobalChartLoader')) {
            update_option('wdtGlobalChartLoader', 1);
        }
        if (!get_option('wdtDismissFoldersNotice')) {
            update_option('wdtDismissFoldersNotice', 'no');
        }
        update_option('wdtHideUpdateModal', 0);

        if (get_option('wdtBootstrapUpdateNotice') === false) {
            update_option('wdtBootstrapUpdateNotice', 'yes');
        }
        if (get_option('wdtHighchartsCdnNotice') === false) {
            update_option('wdtHighchartsCdnNotice', 'yes');
        }
        delete_option('wdtGeneratedTablesCount');
    }

    /**
     * Insert the standard simple-table templates.
     */
    public static function insertTemplates(): void
    {
        \WPDataTablesTemplates::importStandardSimpleTemplates();
    }

    /**
     * Install standard templates on first activation (truncating any stale rows).
     *
     * @param int|string $rowCount  Current template row count.
     * @param string     $tableName Templates table name.
     */
    public static function checkSimpleTemplatesActivation($rowCount, $tableName): void
    {
        global $wpdb;

        if (get_option('wdtActivationSimpleTableTemplates') !== 'yes') {
            if ((int)$rowCount !== 0) {
                $wpdb->query("TRUNCATE TABLE {$tableName}");
            }
            self::insertTemplates();
            update_option('wdtActivationSimpleTableTemplates', 'yes');
        }
    }
}
