<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin\Admin;

use WPDataTables\Services\Tools\ToolsService;
use WDTSettingsController;

/**
 * AdminAssets — registers + enqueues the wpDataTables admin CSS/JS.
 *
 * `enqueueAdmin` is the `admin_enqueue_scripts` callback;
 * `deregisterGravityTooltipScript` is the `wpdatatables_enqueue_on_admin_pages`
 * callback. The per-page `enqueueX()` helpers are private. The `admin_body_class`
 * filter uses {@see AdminAssets::addBodyClass()}.
 *
 * Wired by {@see \WPDataTables\Plugin\AdminHooks}; the two legacy global hook
 * callbacks remain one-line delegators.
 *
 * @package WPDataTables\Plugin\Admin
 */
class AdminAssets
{
    /**
     * Enqueue JS and CSS files for the Admin pages (the `admin_enqueue_scripts` callback).
     *
     * @param string $hook Current admin page hook suffix.
     * @return void
     */
    public function enqueueAdmin($hook)
    {
        if (in_array($hook, array(
            'toplevel_page_wpdatareports',
            'report-builder_page_wpdatareports-wizard',
            'toplevel_page_wpdatatables-dashboard',
            'wpdatatables_page_wpdatatables-administration',
            'wpdatatables_page_wpdatatables-constructor',
            'wpdatatables_page_wpdatatables-charts',
            'wpdatatables_page_wpdatatables-chart-wizard',
            'wpdatatables_page_wpdatatables-settings',
            'wpdatatables_page_wpdatatables-support',
            'wpdatatables_page_wpdatatables-system-info',
            'wpdatatables_page_wpdatatables_permissions',
            'admin_page_wpdatatables-getting-started',
            'wpdatatables_page_wpdatatables-getting-started',
            'admin_page_wpdatatables-welcome-page',
            'admin_page_wpdatatables-upgrade',
            'wpdatatables_page_wpdatatables-upgrade',
            'wpdatatables_page_wpdatatables-add-ons'
        ))) {

            add_filter('admin_body_class', array($this, 'addBodyClass'));

            wp_enqueue_style('wdt-color-pickr-classic', WDT_CSS_PATH . 'color-pickr/classic-theme.min.css', array(), WDT_CURRENT_VERSION);

            wp_enqueue_script('media-upload');
            wp_enqueue_media();

            if (!in_array($hook, array('toplevel_page_wpdatareports', 'report-builder_page_wpdatareports-wizard'))) {
                $this->renderUpdateNoticeModal();
            }
        }
        wp_register_style('wdt-dragula', WDT_CSS_PATH . 'dragula/dragula.min.css', array(), WDT_CURRENT_VERSION);
        wp_register_style('wdt-browse-css', WDT_CSS_PATH . 'admin/browse.css', array(), WDT_CURRENT_VERSION);
        wp_register_style('wdt-wpdatatables', WDT_CSS_PATH . 'wpdatatables.min.css', array(), WDT_CURRENT_VERSION);
        wp_register_style('wdt-permissions-css', WDT_CSS_PATH . 'admin/permissions.css', array(), WDT_CURRENT_VERSION);

        wp_enqueue_style('wdt-admin', WDT_CSS_PATH . 'admin/admin.css', array(), WDT_CURRENT_VERSION);

        wp_register_script('wdt-jsrender', WDT_JS_PATH . 'jsrender/jsrender.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_register_script('wdt-dragula', WDT_JS_PATH . 'dragula/dragula.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_register_script('wdt-ace', WDT_JS_PATH . 'ace/ace.js', array(), WDT_CURRENT_VERSION, true);
        wp_register_script('wdt-color-pickr', WDT_JS_PATH . 'color-pickr/pickr.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_register_script('wdt-color-pickr-init', WDT_ROOT_URL . 'assets/js/wpdatatables/admin/wdt.color-picker-init.js', array(), WDT_CURRENT_VERSION, true);
        wp_register_script('wdt-common', WDT_ROOT_URL . 'assets/js/wpdatatables/admin/common.js', array(), WDT_CURRENT_VERSION, true);
        wp_register_script('wdt-funcs-js', WDT_JS_PATH . 'wpdatatables/wdt.funcs.js', array(
            'jquery',
            'wdt-common'
        ), WDT_CURRENT_VERSION, true);
        wp_register_script('wdt-doc-js', WDT_JS_PATH . 'wpdatatables/admin/doc.js', array(
            'jquery',
            'wdt-common'
        ), WDT_CURRENT_VERSION, true);

        wp_enqueue_script('wdt-rating', WDT_JS_PATH . 'wpdatatables/admin/wdtRating.js', array('jquery'), WDT_CURRENT_VERSION, true);

        wp_localize_script('wdt-common', 'wpdatatables_edit_strings', ToolsService::getTranslationStringsCommon());
        wp_localize_script('wdt-common', 'wpdatatables_settings', ToolsService::getDateTimeSettings());
        wp_localize_script('wdt-common', 'wpdatatables_mapsapikey', ToolsService::getGoogleApiMapsKey());
        wp_localize_script('wdt-common', 'wdtWpDataTablesPage', ToolsService::getWpDataTablesAdminPages());


        switch ($hook) {
            case 'toplevel_page_wpdatatables-dashboard':
                $this->enqueueDashboard();
                break;
            case 'wpdatatables_page_wpdatatables-administration':
                $this->enqueueBrowseTables();
                break;
            case 'wpdatatables_page_wpdatatables-constructor':
                isset($_REQUEST['source']) ? $this->enqueueEdit() : $this->enqueueConstructor();
                break;
            case 'wpdatatables_page_wpdatatables-charts':
                $this->enqueueBrowseCharts();
                break;
            case 'wpdatatables_page_wpdatatables-chart-wizard':
                $this->enqueueChartWizard();
                break;
            case 'wpdatatables_page_wpdatatables-settings':
                $this->enqueueSettings();
                break;
            case 'wpdatatables_page_wpdatatables_permissions':
                $this->enqueuePermissions();
                break;
            case 'wpdatatables_page_wpdatatables-support':
                $this->enqueueSupport();
                break;
            case 'wpdatatables_page_wpdatatables-system-info':
                $this->enqueueSystemInfo();
                break;
            case 'admin_page_wpdatatables-getting-started':
            case 'wpdatatables_page_wpdatatables-getting-started':
                $this->enqueueGettingStarted();
                break;
            case 'wpdatatables_page_wpdatatables-welcome-page':
            case 'admin_page_wpdatatables-welcome-page':
                $this->enqueueWelcomePage();
                break;
            case 'admin_page_wpdatatables-upgrade':
            case 'wpdatatables_page_wpdatatables-upgrade':
                $this->enqueueLiteVSPremium();
                break;
            case 'wpdatatables_page_wpdatatables-add-ons':
                $this->enqueueAddOns();
                break;
        }
        do_action('wpdatatables_enqueue_on_admin_pages');
    }

    /**
     * Fix conflict with Gravity Forms tooltips on the back-end when our add-on is not used.
     *
     * @return void
     */
    public function deregisterGravityTooltipScript()
    {
        if (!defined('WDT_GF_VERSION') && defined('GF_MIN_WP_VERSION') &&
            isset($_GET['page']) && ((strpos($_GET['page'], 'wpdatatables') !== false) || (strpos($_GET['page'], 'wpdatareports') !== false))) {
            wp_deregister_script('gform_tooltip_init');
        }
    }

    /**
     * Append the wpDataTables admin body class on plugin admin screens.
     *
     * @param string $classes Space-separated admin body classes.
     * @return string
     */
    public function addBodyClass($classes)
    {
        return $classes . ' wpdt-c';
    }

    /**
     * Browse (wpDataTables) page.
     *
     * @return void
     */
    private function enqueueBrowseTables()
    {
        ToolsService::wdtUIKitEnqueue();

        wp_enqueue_style('wdt-browse-css');

        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-browse-js', WDT_JS_PATH . 'wpdatatables/admin/browse/wdt.browse.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-doc-js');

        wp_localize_script('wdt-browse-js', 'wpdatatables_browse_strings', ToolsService::getTranslationStringsBrowse());
    }

    /**
     * Edit (add from data source) page.
     *
     * @return void
     */
    private function enqueueEdit()
    {
        $jsExt = get_option('wdtMinifiedJs') ? '.min.js' : '.js';
        ToolsService::wdtUIKitEnqueue();

        wp_enqueue_style('wdt-wpdatatables');
        wp_enqueue_style('wdt-edit-table-css', WDT_CSS_PATH . 'admin/edit_table.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-handsontable-css', WDT_CSS_PATH . 'handsontable.full.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-table-tools', WDT_CSS_PATH . 'TableTools.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-datatables-responsive', WDT_CSS_PATH . 'datatables.responsive.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-dragula');

        wp_enqueue_style('wdt-skin-material', WDT_ASSETS_PATH . 'css/wdt-skins/material.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-skin-light', WDT_ASSETS_PATH . 'css/wdt-skins/light.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-skin-graphite', WDT_ASSETS_PATH . 'css/wdt-skins/graphite.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-skin-aqua', WDT_ASSETS_PATH . 'css/wdt-skins/aqua.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-skin-purple', WDT_ASSETS_PATH . 'css/wdt-skins/purple.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-skin-dark', WDT_ASSETS_PATH . 'css/wdt-skins/dark.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-skin-raspberry-cream', WDT_ASSETS_PATH . 'css/wdt-skins/raspberry-cream.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-skin-mojito', WDT_ASSETS_PATH . 'css/wdt-skins/mojito.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-skin-dark-mojito', WDT_ASSETS_PATH . 'css/wdt-skins/dark-mojito.css', array(), WDT_CURRENT_VERSION);

        wp_enqueue_script('wdt-datatables', WDT_JS_PATH . 'jquery-datatables/jquery.dataTables.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-select', WDT_JS_PATH . 'jquery-datatables/dataTables.select.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-advanced-filter', WDT_JS_PATH . 'wpdatatables/wdt.columnFilter.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-row-grouping', WDT_JS_PATH . 'jquery-datatables/jquery.dataTables.rowGrouping.js', array('jquery',
            'wdt-datatables'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-buttons', WDT_JS_PATH . 'export-tools/dataTables.buttons.min.js', array('jquery',
            'wdt-datatables'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-buttons-html5', WDT_JS_PATH . 'export-tools/buttons.html5.min.js', array('jquery',
            'wdt-datatables'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-js-zip', WDT_JS_PATH . 'export-tools/jszip.min.js', array('jquery',
            'wdt-datatables'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-pdf-make', WDT_JS_PATH . 'export-tools/pdfmake.min.js', array('jquery',
            'wdt-datatables'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-vfs-fonts', WDT_JS_PATH . 'export-tools/vfs_fonts.js', array('jquery',
            'wdt-datatables'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-button-print', WDT_JS_PATH . 'export-tools/buttons.print.min.js', array('jquery',
            'wdt-datatables'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-button-vis', WDT_JS_PATH . 'export-tools/buttons.colVis.min.js', array('jquery',
            'wdt-datatables'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-funcs-js');
        wp_enqueue_script('wdt-wpdatatables', WDT_JS_PATH . 'wpdatatables/wpdatatables.js', array(
            'jquery',
            'wdt-datatables'
        ), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-responsive', WDT_JS_PATH . 'responsive/datatables.responsive.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-color-pickr');
        wp_enqueue_script('wdt-color-pickr-init');
        wp_enqueue_script('wdt-column-config', WDT_JS_PATH . 'wpdatatables/admin/table-settings/column_config_object.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-table-config', WDT_JS_PATH . 'wpdatatables/admin/table-settings/table_config_object.js', array(), WDT_CURRENT_VERSION, true);
        if (defined('WDT_WP_QUERY_INTEGRATION')) {
            wp_enqueue_script('wdt-wp-post-config', WDT_WP_QUERY_ASSETS_PATH . 'wp_posts_table_config.js', array('wdt-table-config'), WDT_CURRENT_VERSION, true);
        }
        wp_enqueue_script('wdt-edit-main-js', WDT_JS_PATH . 'wpdatatables/admin/table-settings/main.js', array(), WDT_CURRENT_VERSION, true);
        if (isset($_GET['table_id']) && isset($_GET['simple'])) {
            wp_enqueue_style('wdt-star-rating-css', WDT_CSS_PATH . 'admin/starRating.min.css', array(), WDT_CURRENT_VERSION);
            wp_register_script('handsontable-6.2.2', WDT_JS_PATH . 'handsontable/handsontable.full.6.2.2' . $jsExt, array('jquery'), WDT_CURRENT_VERSION);
            wp_enqueue_script('handsontable-6.2.2');
            wp_enqueue_script('underscore');
            wp_enqueue_script('wdt-star-rating-js', WDT_JS_PATH . 'wpdatatables/admin/starRating.min.js', array('jquery'), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-simple-table-js', WDT_JS_PATH . 'wpdatatables/admin/constructor/wdt.simpleTable.js', array('jquery'), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-simple-table-responsive-min-js', WDT_JS_PATH . 'responsive/wdt.simpleTable.responsive.min.js', array('jquery'), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-simple-table-responsive-js', WDT_JS_PATH . 'responsive/wdt.simpleTable.responsive.init.js', array('jquery'), WDT_CURRENT_VERSION, true);
            wp_dequeue_style('wdt-skin');
            wp_localize_script('wdt-wpdatatables', 'wpdatatables_admin_simple_table_strings', ToolsService::getTranslationStringsSimpleTable());
        } else {
            wp_register_script('handsontable', WDT_JS_PATH . 'handsontable/handsontable.full' . $jsExt, array('jquery'), WDT_CURRENT_VERSION);
            wp_enqueue_script('handsontable');
        }
        wp_enqueue_script('wdt-jsrender');
        wp_enqueue_script('wdt-jquery-mask-money', WDT_JS_PATH . 'maskmoney/jquery.maskMoney.js', array('jquery'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-add-remove-column', WDT_JS_PATH . 'wpdatatables/wdt.addRemoveColumn.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('jquery-effects-core');
        wp_enqueue_script('jquery-effects-fade');
        wp_enqueue_script('wdt-dragula');
        wp_enqueue_script('wdt-ace');
        wp_enqueue_script('wdt-doc-js');
        wp_localize_script('wdt-advanced-filter', 'wdt_ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
        wp_localize_script('wdt-add-remove-column', 'wpdatatables_add_remove_column_strings', ToolsService::getTranslationStringsAddRemoveColumn());
        wp_localize_script('wdt-wpdatatables', 'wpdatatables_frontend_strings', ToolsService::getTranslationStringsWpDataTables());
        wp_localize_script('wdt-wpdatatables', 'wpdatatables_functions_strings', ToolsService::getTranslationStringsFunctions());
        wp_localize_script('wdt-advanced-filter', 'wpdatatables_filter_strings', ToolsService::getTranslationStringsColumnFilter());

        do_action_deprecated('wdt_enqueue_on_edit_page', array(), WDT_INITIAL_STARTER_VERSION, 'wpdatatables_enqueue_on_edit_page');
        do_action('wpdatatables_enqueue_on_edit_page');
    }

    /**
     * Constructor (wpDataTable Constructor) page.
     *
     * @return void
     */
    private function enqueueConstructor()
    {
        ToolsService::wdtUIKitEnqueue();

        wp_enqueue_style('wdt-wpdatatables');
        wp_enqueue_style('wdt-constructor-css', WDT_CSS_PATH . 'admin/constructor.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-dragula');

        wp_enqueue_script('wdt-ace');
        wp_enqueue_script('wdt-jsrender');
        wp_enqueue_script('wdt-dragula');
        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-color-pickr');
        wp_enqueue_script('wdt-color-pickr-init');
        wp_enqueue_script('wdt-funcs-js');
        wp_enqueue_script('wdt-constructor-main-js', WDT_JS_PATH . 'wpdatatables/admin/constructor/wdt.constructor.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-doc-js');

        wp_localize_script('wdt-constructor-main-js', 'wpdatatables_constructor_strings', ToolsService::getTranslationStringsConstructor());
        do_action('wpdatatables_enqueue_constructor_scripts');
    }

    /**
     * Browse Charts (wpDataCharts) page.
     *
     * @return void
     */
    private function enqueueBrowseCharts()
    {
        ToolsService::wdtUIKitEnqueue();

        wp_enqueue_style('wdt-browse-css');

        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-browse-js', WDT_JS_PATH . 'wpdatatables/admin/browse/wdt.browse.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-doc-js');

        wp_localize_script('wdt-browse-js', 'wpdatatables_browse_strings', ToolsService::getTranslationStringsBrowse());
    }

    /**
     * Chart Wizard (Create Chart Wizard) page.
     *
     * @return void
     */
    private function enqueueChartWizard()
    {
        $googleLibSource = get_option('wdtGoogleStableVersion') ? WDT_JS_PATH . 'wdtcharts/googlecharts/googlecharts.js' : '//www.gstatic.com/charts/loader.js';

        ToolsService::wdtUIKitEnqueue();

        wp_enqueue_style('wdt-dragula');
        $chartWizardCss = WDT_ROOT_PATH . 'assets/css/admin/chart_wizard.css';
        wp_enqueue_style(
            'wdt-chart-wizard-css',
            WDT_CSS_PATH . 'admin/chart_wizard.css',
            array(),
            file_exists($chartWizardCss) ? (string) filemtime($chartWizardCss) : WDT_CURRENT_VERSION
        );

        wp_enqueue_script('wdt-jsrender');
        wp_enqueue_script('wdt-dragula');
        wp_enqueue_script('wdt-google-charts', $googleLibSource, array(), WDT_CURRENT_VERSION, true);

        wp_enqueue_script('wdt-chart-js', WDT_JS_PATH . 'wdtcharts/chartjs/Chart.js', array(), WDT_CURRENT_VERSION, true);

        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-color-pickr');
        wp_enqueue_script('wdt-color-pickr-init');
        wp_enqueue_script('wdt-chart-wizard', WDT_JS_PATH . 'wdtcharts/wdt.chartWizard.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-wp-google-chart', WDT_JS_PATH . 'wdtcharts/googlecharts/wdt.googleCharts.js', array(), WDT_CURRENT_VERSION, true);

        //wp_enqueue_script('wdt-wp-apexcharts', WDT_JS_PATH . 'wdtcharts/apexcharts/wdt.apexcharts.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-wp-chart-js', WDT_JS_PATH . 'wdtcharts/chartjs/wdt.chartJS.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-doc-js');

        do_action('wpdatatables_enqueue_chart_wizard_scripts');

        wp_localize_script('wdt-chart-wizard', 'wpdatatables_chart_wizard_strings', ToolsService::getTranslationStringsChartWizard());

    }

    /**
     * Settings page.
     *
     * @return void
     */
    private function enqueueSettings()
    {
        ToolsService::wdtUIKitEnqueue();

        wp_enqueue_style('wdt-settings-css', WDT_CSS_PATH . 'admin/settings.css', array(), WDT_CURRENT_VERSION);

        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-color-pickr');
        wp_enqueue_script('wdt-color-pickr-init');
        wp_enqueue_script('wdt-plugin-config', WDT_ROOT_URL . 'assets/js/wpdatatables/admin/plugin-settings/plugin_config_object.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-settings-main-js', WDT_ROOT_URL . 'assets/js/wpdatatables/admin/plugin-settings/main.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-settings-psl', WDT_ROOT_URL . 'assets/js/psl/psl.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-ace');
        wp_enqueue_script('wdt-doc-js');
        wp_enqueue_script('wdt-funcs-js');

        wp_localize_script('wdt-plugin-config', 'wdt_current_config', WDTSettingsController::getCurrentPluginConfig());
        wp_localize_script('wdt-plugin-config', 'wdtStore', [
            'url' => WDT_STORE_API_URL,
            'redirectUrl' => get_site_url()
        ]);

        wp_localize_script('wdt-settings-main-js', 'wpdatatables_settings_strings', ToolsService::getTranslationStringsTableSettingsMain());

        do_action_deprecated('wdt_enqueue_on_settings_page', array(), WDT_INITIAL_STARTER_VERSION, 'wpdatatables_enqueue_on_settings_page');
        do_action('wpdatatables_enqueue_on_settings_page');
    }

    /**
     * Permissions page.
     *
     * @return void
     */
    private function enqueuePermissions()
    {
        ToolsService::wdtUIKitEnqueue();

        wp_enqueue_style('wdt-permissions-css');
        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-doc-js');
        wp_enqueue_script('wdt-funcs-js');
        wp_enqueue_script(
            'wdt-permissions-js',
            WDT_ROOT_URL . 'assets/js/wpdatatables/admin/permissions/permissions-admin.js',
            array('jquery', 'wdt-common'),
            WDT_CURRENT_VERSION,
            true
        );

        $catalog = array(
            'tables' => \WPDataTables\Services\Permissions\PermissionCatalog::definitionsForResource('tables'),
            'charts' => \WPDataTables\Services\Permissions\PermissionCatalog::definitionsForResource('charts'),
        );

        wp_localize_script('wdt-permissions-js', 'wdtPermissions', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wdt_permissions_nonce'),
            'catalog' => $catalog,
            'i18n' => array(
                'add_permission' => __('Add Permission', 'wpdatatables'),
                'edit_permission' => __('Edit Permission', 'wpdatatables'),
                'limit_tables' => __('Limit to specific tables', 'wpdatatables'),
                'limit_charts' => __('Limit to specific charts', 'wpdatatables'),
                'limit_tables_help' => __('If unchecked, permissions apply to all tables.', 'wpdatatables'),
                'limit_charts_help' => __('If unchecked, permissions apply to all charts.', 'wpdatatables'),
                'select_tables' => __('Select tables', 'wpdatatables'),
                'select_charts' => __('Select charts', 'wpdatatables'),
            ),
        ));
    }

    /**
     * Dashboard page.
     *
     * @return void
     */
    private function enqueueDashboard()
    {
        ToolsService::wdtUIKitEnqueue();
        wp_enqueue_style('wdt-dashboard-css', WDT_CSS_PATH . 'admin/dashboard.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-doc-js');
        wp_enqueue_script('wdt-dashboard-psl', WDT_ROOT_URL . 'assets/js/psl/psl.min.js', array(), WDT_CURRENT_VERSION, true);

        do_action_deprecated('wdt_enqueue_on_dashboard_page', array(), WDT_INITIAL_STARTER_VERSION, 'wpdatatables_enqueue_on_dashboard_page');
        do_action('wpdatatables_enqueue_on_dashboard_page');
    }

    /**
     * Support page.
     *
     * @return void
     */
    private function enqueueSupport()
    {
        ToolsService::wdtUIKitEnqueue();
        wp_enqueue_style('wdt-support-css', WDT_CSS_PATH . 'admin/support.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-doc-js');
    }

    /**
     * Welcome page.
     *
     * @return void
     */
    private function enqueueWelcomePage()
    {
        ToolsService::wdtUIKitEnqueue();
        wp_enqueue_style('wdt-welcome-page-css', WDT_CSS_PATH . 'admin/welcome-page.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-doc-js');
        wp_enqueue_script('wdt-bootstrap-back', WDT_JS_PATH . 'bootstrap/bootstrap.min.js', array('jquery'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-welcome-page-js', WDT_ROOT_URL . 'assets/js/dashboard/welcome-page.js', array(), WDT_CURRENT_VERSION, true);
    }

    /**
     * Getting Started page.
     *
     * @return void
     */
    private function enqueueGettingStarted()
    {
        ToolsService::wdtUIKitEnqueue();
        wp_enqueue_style('wdt-getting-started-css', WDT_CSS_PATH . 'admin/getting-started.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-doc-js');
    }

    /**
     * System Info page.
     *
     * @return void
     */
    private function enqueueSystemInfo()
    {
        ToolsService::wdtUIKitEnqueue();
        wp_enqueue_style('wdt-system-info-css', WDT_CSS_PATH . 'admin/system-info.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-doc-js');
        wp_enqueue_script('wdt-system-info-js', WDT_ROOT_URL . 'assets/js/dashboard/system-info.js', array(), WDT_CURRENT_VERSION, true);
    }

    /**
     * Lite VS Premium page.
     *
     * @return void
     */
    private function enqueueLiteVSPremium()
    {
        ToolsService::wdtUIKitEnqueue();
        wp_enqueue_style('wdt-lite-vs-premium-css', WDT_CSS_PATH . 'admin/lite-vs-premium.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-doc-js');
    }

    /**
     * Add-ons page.
     *
     * @return void
     */
    private function enqueueAddOns()
    {
        ToolsService::wdtUIKitEnqueue();

        wp_enqueue_style('wdt-add-ons-css', WDT_CSS_PATH . 'admin/addons.css', array(), WDT_CURRENT_VERSION);

        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-doc-js');
        wp_enqueue_style('wdt-bundles-css', WDT_CSS_PATH . 'admin/bundles.css');
    }

    /**
     * Enqueue the plugin "what's new" update-details modal assets (when not
     * dismissed). Called from {@see enqueueAdmin}.
     *
     * @return void
     */
    private function renderUpdateNoticeModal()
    {
        $hideUpdateModal = get_option('wdtHideUpdateModal');
        if ($hideUpdateModal === "0") {
            wp_enqueue_script('wdt-update-info-js', WDT_ROOT_URL . 'assets/js/update/info.js', array(), WDT_CURRENT_VERSION, true);
            wp_localize_script('wdt-update-info-js', 'wpdatatables_update_info', ToolsService::getUpdateInfo());
        }

    }
}
