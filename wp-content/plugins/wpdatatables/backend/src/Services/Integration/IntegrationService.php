<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Integration;

/**
 * IntegrationService — loads licence-tier bootstrap files from `backend/tiers/`.
 *
 * `init()` is a data-driven loader: it builds an {@see IntegrationRegistry} with
 * the ordered set of integration bootstrap files (each with its `is_file()`
 * guard and `is_admin()` gating), fires the `wpdatatables/integrations/register`
 * action so add-ons can append their own entries, requires every (existing)
 * entry, then registers the admin upsell hooks and fires
 * `wpdatatables/integrations/registered`.
 *
 * TIER-BUILD SAFETY: the Jenkins release job builds the four licence tiers by
 * `rm -rf`-ing whole tier directories (`backend/tiers/standard|pro|developer`)
 * before zipping. Every entry under one of those directories is registered as
 * `optional` (loaded only `if (is_file(...))`), so a stripped-tier zip boots
 * without fatals — exactly as the legacy loader did. Only files under
 * `backend/tiers/starter` (never stripped) are registered non-optional, matching
 * the legacy unconditional `require_once`s. This service lives under `backend/`,
 * which the build never strips, so it ships in all four zips.
 *
 * Boot is triggered by {@see Plugin::bootIntegrations()} after the legacy source
 * tree is loaded (some tier modules instantiate legacy classes at load time).
 *
 * @package WPDataTables\Services\Integration
 */
class IntegrationService
{
    /** @var UpsellNoticeRenderer */
    private $upsellNoticeRenderer;

    public function __construct(UpsellNoticeRenderer $upsellNoticeRenderer)
    {
        $this->upsellNoticeRenderer = $upsellNoticeRenderer;
    }

    /**
     * Build the registry, load every integration, and wire the admin hooks.
     *
     * @return void
     */
    public function init()
    {
        $registry = new IntegrationRegistry();
        $this->registerCore($registry);

        /**
         * Let add-ons append their own integration bootstrap files.
         *
         * @since 7.x
         * @param IntegrationRegistry $registry The wpDataTables integration registry.
         */
        do_action('wpdatatables/integrations/register', $registry);

        foreach ($registry->all() as $entry) {
            if ($entry['admin'] && !is_admin()) {
                continue;
            }
            if ($entry['optional']) {
                if (is_file($entry['file'])) {
                    require_once($entry['file']);
                }
            } else {
                require_once($entry['file']);
            }
        }

        if (is_admin()) {
            $this->registerAdminHooks();
        }

        // Boot first-class integration objects (the structured alternative to
        // file entries — see IntegrationInterface). Each self-gates via
        // isAvailable(). Empty for the bundled file-based integrations.
        foreach ($registry->integrations() as $integration) {
            if ($integration->isAvailable()) {
                $integration->register();
            }
        }

        /**
         * Fires after all integration bootstrap files have been loaded.
         *
         * @since 7.x
         */
        do_action('wpdatatables/integrations/registered');
    }

    /**
     * Register the built-in integration entries in the exact order and with the
     * exact guards the legacy loader used.
     *
     * @param IntegrationRegistry $registry
     * @return void
     */
    private function registerCore(IntegrationRegistry $registry)
    {
        $starter = WDT_STARTER_INTEGRATIONS_PATH;
        $standard = WDT_STANDARD_INTEGRATIONS_PATH;
        $pro = WDT_PRO_INTEGRATIONS_PATH;
        $developer = WDT_DEVELOPER_INTEGRATIONS_PATH;

        // Page builders (starter tier — never stripped, so unconditional).
        $registry->add($starter . 'page-builders/gutenberg/GutenbergBlock.php', false);
        $registry->add($starter . 'page-builders/gutenberg/WpDataTablesGutenbergBlock.php', false);
        $registry->add($starter . 'page-builders/gutenberg/WpDataChartsGutenbergBlock.php', false);
        $registry->add($starter . 'page-builders/elementor/class.wdtelementorblock.php', false);
        $registry->add($starter . 'page-builders/divi-wpdt/divi-wpdt.php', false);
        $registry->add($starter . 'page-builders/avada/class.wdtavadaelements.php', false);
        $registry->add($starter . 'page-builders/wpbakery/wdtBakeryBlock.php', false);
        $registry->add($starter . 'page-builders/wpbakery/wdtCustomBakery.php', false);

        // IvyForms (starter tier — unconditional; preserves the legacy leading slash).
        $registry->add($starter . '/ivyforms/ivyforms-integration.php', false);

        // Global Page Search (starter tier, guarded).
        $registry->add($starter . 'global-search-for-all-tables/wdt-global-search-all-tables-integration.php');

        // Separate DB connection (standard tier).
        $registry->add($standard . 'separate-db-connection/source/class.pgsql.connection.php');
        $registry->add($standard . 'separate-db-connection/source/class.sql.php');
        $registry->add($standard . 'separate-db-connection/source/class.sql.pdo.php');
        $registry->add($standard . 'separate-db-connection/wdt-separate-connection-integration.php');

        // Charts: HighStock (pro), HighCharts + ApexCharts (standard).
        $registry->add($pro . 'highstock/wdt-highstock-integration.php');
        $registry->add($standard . 'highcharts/wdt-highcharts-integration.php');
        $registry->add($standard . 'apexcharts/wdt-apexcharts-integration.php');

        // Standard column / table feature integrations.
        $registry->add($standard . 'placeholders/wdt-placeholders-integration.php');
        $registry->add($standard . 'sql-query/wdt-sql-query-integration.php');
        $registry->add($standard . 'fixed-columns-and-headers/wdt-fixed-ch-integration.php');
        $registry->add($standard . 'index-column/wdt-index-column-integration.php');
        $registry->add($standard . 'hidden-column/wdt-hidden-column-integration.php');
        $registry->add($standard . 'formula-column/wdt-formula-column-integration.php');
        $registry->add($standard . 'editing/wdt-editing-integration.php');

        // Admin-only integrations.
        $registry->add($standard . 'sql-constructor/wdt-sql-constructor-integration.php', true, true);
        $registry->add($standard . 'sql-constructor/source/class.sql.constructor.php', true, true);
        $registry->add($starter . 'google-sheet-api/wdt-google-sheet-api-integration.php', true, true);
        $registry->add($standard . 'foreign-key/wdt-foreign-key-integration.php', true, true);
        $registry->add($standard . 'update-manual-from-file/wdt-update-manual-from-file-integration.php', true, true);
        $registry->add($pro . 'folders/source/class.wpdatafolders.php', true, true);
        $registry->add($pro . 'folders/source/class.factory.wpdatafolders.php', true, true);
        $registry->add($pro . 'folders/source/class.tables.wpdatafolders.php', true, true);
        $registry->add($pro . 'folders/source/class.charts.wpdatafolders.php', true, true);
        $registry->add($pro . 'folders/source/class.reports.wpdatafolders.php', true, true);

        // WP Posts (query builder) + WooCommerce (pro) — loaded on all contexts,
        // after the admin block in the legacy loader.
        $registry->add($pro . 'query-builder/source/wdt-query-builder.php');
        $registry->add($pro . 'woo-commerce/source/wdt-woo-commerce.php');
        $registry->add($pro . 'webhooks/wdt-webhooks-integration.php');

        // Public REST API — Developer tier only. Admin REST (`wpdatatables/v1`)
        // registers from core Plugin bootstrap on every tier; public routes and
        // key management ship only in the Developer-licence build.
        $registry->add($developer . 'public-api/wdt-public-api-hooks.php');
        $registry->add($developer . 'public-api/wdt-public-api-admin.php', true, true);
    }

    /**
     * Register the admin upsell-notice hooks. Same hook names as the legacy
     * loader; the callbacks now target the {@see UpsellNoticeRenderer} instance.
     *
     * @return void
     */
    private function registerAdminHooks()
    {
        $host = $this->upsellNoticeRenderer;

        add_action('wpdatatables_add_chart_picker', array($host, 'addChartPickerStepNotice'));
        add_action('wpdatatables_add_table_configuration_tabpanel', array($host, 'addNewFixedHeaderAndColumnsOptions'));
        add_action('wpdatatables_add_table_configuration_tab', array($host, 'addWebhooksNoticeTab'));
        add_action('wpdatatables_add_table_configuration_tabpanel', array($host, 'addWebhooksNoticePanel'));
        add_action('wpdatatables_add_table_editing_elements', array($host, 'addNoticeEditingOptions'));
        add_action('wpdatatables_add_column_editing_elements', array($host, 'addNoticeEditingOptions'));
        add_action('wpdatatables_add_table_placeholders_elements', array($host, 'addNoticePlaceholdersOptions'));
        add_action('wpdatatables_add_constructor_step_in_wizard', array($host, 'addNewTableTypesInConstructor'));
        add_action('wpdatatables_add_tab_in_main_settings', array($host, 'addSeparateConnectionSettings'));
        add_action('wpdatatables_add_tab_in_main_settings', array($host, 'addGoogleSheetAPISettings'));
        add_action('wpdatatables_add_tab_nav_in_main_settings', array($host, 'addPublicRestApiSettingsTabNav'));
        add_action('wpdatatables_add_tab_in_main_settings', array($host, 'addPublicRestApiSettings'));
        add_action('wpdatatables_add_foreign_key_block', array($host, 'addForeignKeySettings'));
        add_action('wpdatatables_admin_after_edit', array($host, 'addFormulaEditorModal'));
        add_action('wpdatatables_add_mysql_settings_block', array($host, 'addSQLQueryNotice'));
        add_action('wpdatatables_add_data_from_source_file_block', array($host, 'addUpdateManualNoticeBlock'));
        add_action('wpdatatables_add_browse_table_notice_info', array($host, 'addFolderNotice'));
        add_action('wpdatatables_add_browse_chart_notice_info', array($host, 'addFolderNotice'));
        add_action('wpdatatables_add_custom_column_type_option', array($host, 'addHiddenColumnTypeNotice'));
        add_filter('wpdatatables_filter_possible_column_types', array($host, 'filterPossibleColumnTypes'));
        add_action('wpdatatables_add_chart_stable_tag_option', array($host, 'addChartsStableTagNotice'));
        add_action('wpdatatables_add_options_in_add_column_modal', array($host, 'addHiddenColumnAddModalNotice'));
        add_action('wpdatatables_after_constructor_column_block', array($host, 'addHiddenColumnConstructorNotice'));
        add_action('wpdatatables_after_constructor_column_block_preview', array($host, 'addHiddenColumnConstructorNotice'));
    }
}
