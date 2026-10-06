<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\InstallActions;

use WPDT\Melograno\UsageTracker\Collectors\Plugin\WpDataTablesCollector;
use WPDT\Melograno\UsageTracker\Core\UsageTracker;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Services\InstallActions\DB\CacheTable;
use WPDataTables\Services\InstallActions\DB\ChartsTable;
use WPDataTables\Services\InstallActions\DB\ColumnsTable;
use WPDataTables\Services\InstallActions\DB\RowsTable;
use WPDataTables\Services\InstallActions\DB\TablesTable;
use WPDataTables\Services\InstallActions\DB\TemplatesTable;

/**
 * Plugin uninstall actions.
 *
 * Owns the uninstall routine: deletes plugin options and drops the
 * `wpdatatables_*` tables, gated by the `wdtPreventDeletingTables` option. The
 * legacy `wdtUninstall()` / `wdtUninstallDelete()` functions are shims
 * delegating here. Multisite behaviour preserved.
 *
 * @package WPDataTables\Services\InstallActions
 */
class UninstallHook
{
    /**
     * Uninstall entry point.
     *
     * @throws InvalidArgumentException
     */
    public static function uninstall(): void
    {
        if (function_exists('is_multisite') && is_multisite()) {
            global $wpdb;
            $oldBlog = $wpdb->blogid;
            // Get all blog ids
            $blogIds = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

            foreach ($blogIds as $blogId) {
                switch_to_blog($blogId);
                self::delete();
            }
            switch_to_blog($oldBlog);
        } else {
            self::delete();
        }
    }

    /**
     * Delete plugin options and drop tables for the current blog, unless the
     * `wdtPreventDeletingTables` option prevents it.
     *
     * @throws InvalidArgumentException
     */
    public static function delete(): void
    {
        global $wpdb;

        if (get_option('wdtPreventDeletingTables') == false) {
            delete_option('wdtUseSeparateCon');
            delete_option('wdtSeparateCon');
            delete_option('wdtTimepickerRange');
            delete_option('wdtTimeFormat');
            delete_option('wdtTabletWidth');
            delete_option('wdtTablesPerPage');
            delete_option('wdtSumFunctionsLabel');
            delete_option('wdtRenderFilter');
            delete_option('wdtRenderCharts');
            delete_option('wdtGettingStartedPageStatus');
            delete_option('wdtLiteVSPremiumPageStatus');
            delete_option('wdtIncludeGoogleFonts');
            delete_option('wdtIncludeBootstrap');
            delete_option('wdtIncludeBootstrapBackEnd');
            delete_option('wdtPreventDeletingTables');
            delete_option('wdtParseShortcodes');
            delete_option('wdtNumbersAlign');
            delete_option('wdtBorderRemoval');
            delete_option('wdtBorderRemovalHeader');
            delete_option('wdtNumberFormat');
            delete_option('wdtMobileWidth');
            delete_option('wdtMinifiedJs');
            delete_option('wdtMinFunctionsLabel');
            delete_option('wdtMaxFunctionsLabel');
            delete_option('wdtLeftOffset');
            delete_option('wdtTopOffset');
            delete_option('wdtInterfaceLanguage');
            delete_option('wdtGeneratedTablesCount');
            delete_option('wdtFontColorSettings');
            delete_option('wdtDecimalPlaces');
            delete_option('wdtCSVDelimiter');
            delete_option('wdtDateFormat');
            delete_option('wdtAutoUpdateOption');
            delete_option('wdtCustomJs');
            delete_option('wdtGoogleSettings');
            delete_option('wdtGoogleToken');
            delete_option('wdtCustomCss');
            delete_option('wdtBaseSkin');
            delete_option('wdtAvgFunctionsLabel');
            delete_option('wdtInstallDate');
            delete_option('wdtRatingDiv');
            delete_option('wdtDismissFoldersNotice');
            delete_option('wdtShowForminatorNotice');
            delete_option('wdtMDNewsDiv');
            delete_option('wdtTempFutureDate');
            delete_option('wdtSimpleTableAlert');
            delete_option('wdtActivated');
            delete_option('wdtAutoUpdateHash');
            delete_option('wdtPurchaseCodeStore');
            delete_option('wdtEnvatoTokenEmail');
            delete_option('wdtActivatedPowerful');
            delete_option('wdtPurchaseCodeStorePowerful');
            delete_option('wdtEnvatoTokenEmailPowerful');
            delete_option('wdtActivatedMasterDetail');
            delete_option('wdtPurchaseCodeStoreMasterDetail');
            delete_option('wdtActivatedReport');
            delete_option('wdtPurchaseCodeStoreReport');
            delete_option('wdtEnvatoTokenEmailReport');
            delete_option('wdtActivatedGravity');
            delete_option('wdtPurchaseCodeStoreGravity');
            delete_option('wdtEnvatoTokenEmailGravity');
            delete_option('wdtActivatedFormidable');
            delete_option('wdtPurchaseCodeStoreFormidable');
            delete_option('wdtEnvatoTokenEmailFormidable');
            delete_option('wdtGoogleStableVersion');
            delete_option('wdtHighChartStableVersion');
            delete_option('wdtApexStableVersion');
            delete_option('wdtShowBundlesNotice');
            delete_option('wdtShowAmeliaBanner');
            delete_option('wdtShowIvyFormsBanner');
            delete_option('wdtGoogleApiMaps');
            delete_option('wdtGoogleApiMapsValidated');
            delete_option('wdtHideUpdateModal');
            delete_option('wdtBootstrapUpdateNotice');
            delete_option('wdtHighchartsCdnNotice');
            delete_option('wdtGlobalTableLoader');
            delete_option('wdtGlobalChartLoader');
            UsageTracker::deleteStoredOptions(new WpDataTablesCollector());
            delete_option('wpdatatables_usage_tracking_settings_optout_notice_handled');

            TablesTable::delete();
            ColumnsTable::delete();
            ChartsTable::delete();
            RowsTable::delete();
            CacheTable::delete();
            // The folders tables have no descriptor (never created here, only dropped).
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpdatatables_folders");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpdatatables_folders_meta");
            TemplatesTable::delete();

            do_action('wpdatatables_after_uninstall_method');
        }
    }
}
