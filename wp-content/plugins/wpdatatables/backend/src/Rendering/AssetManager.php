<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Rendering;

use WPDataTables\Services\Tools\ToolsService;

use WPDataTable;

/**
 * Frontend asset enqueuing for a rendered wpDataTable.
 *
 * The legacy `WPDataTable::enqueueJSAndStyles()` is a thin delegator to
 * {@see enqueueFrontend()} — same handles, same dependencies, same
 * `wp_localize_script` payloads, same skin switch, same
 * `wpdatatables_enqueue_on_frontend` action.
 *
 * Reads table state exclusively through the existing public accessors (and
 * {@see \WPDataTable::getTableToolsConfig()}).
 *
 * @package WPDataTables\Rendering
 */
class AssetManager
{
    /**
     * Enqueue every JS/CSS asset (and localize the script strings) a standard
     * wpDataTable needs on the frontend.
     *
     * @param WPDataTable $table the table being rendered
     *
     * @return void
     */
    public function enqueueFrontend(WPDataTable $table)
    {
        $tableToolsConfig = $table->getTableToolsConfig();

        ToolsService::wdtUIKitEnqueueNotEdit();

        wp_enqueue_script('wdt-common', WDT_ROOT_URL . 'assets/js/wpdatatables/admin/common.js', array(), WDT_CURRENT_VERSION, true);
        if (get_option('wdtMinifiedJs')) {
            ToolsService::wdtUIKitEnqueue();

            if (defined('WDT_FCH_INTEGRATION')) {
                wp_enqueue_style('wdt-wpdatatables', WDT_CSS_PATH . 'wdt.frontend.min.css', array(), WDT_CURRENT_VERSION);
                wp_enqueue_script('wdt-wpdatatables', WDT_JS_PATH . 'wpdatatables/wdt.frontend.min.js', array('wdt-common'), WDT_CURRENT_VERSION, true);
            } else {
                wp_enqueue_style('wdt-wpdatatables', WDT_CSS_PATH . 'wdt.frontend-starter.min.css', array(), WDT_CURRENT_VERSION);
                wp_enqueue_script('wdt-wpdatatables', WDT_JS_PATH . 'wpdatatables/wdt.frontend-starter.min.js', array('wdt-common'), WDT_CURRENT_VERSION, true);
            }
            wp_localize_script('wdt-wpdatatables', 'wdt_ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
            wp_localize_script('wdt-wpdatatables', 'wpdatatables_inline_strings', ToolsService::getTranslationStringsInlineEditing());
            wp_localize_script('wdt-wpdatatables', 'wpdatatables_filter_strings', ToolsService::getTranslationStringsColumnFilter());
            wp_localize_script('wdt-common', 'wpdatatables_edit_strings', ToolsService::getTranslationStringsCommon());
            wp_localize_script('wdt-wpdatatables', 'wpdatatables_functions_strings', ToolsService::getTranslationStringsFunctions());
        } else {
            wp_enqueue_style('wdt-wpdatatables', WDT_CSS_PATH . 'wpdatatables.min.css', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-table-tools', WDT_CSS_PATH . 'TableTools.css', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-datatables-responsive', WDT_CSS_PATH . 'datatables.responsive.css', array(), WDT_CURRENT_VERSION);

            if (WDT_INCLUDE_DATATABLES_CORE) {
                wp_enqueue_script('wdt-datatables', WDT_JS_PATH . 'jquery-datatables/jquery.dataTables.min.js', array(), WDT_CURRENT_VERSION, true);

                if (defined('WDT_WOO_COMMERCE_INTEGRATION')) {
                    wp_enqueue_script('wdt-select', WDT_JS_PATH . 'jquery-datatables/dataTables.select.min.js', array(), WDT_CURRENT_VERSION, true);
                }
            }
            if ($table->filterEnabled() && $table->advancedFilterEnabled()) {
                ToolsService::wdtUIKitEnqueue();
                wp_enqueue_script('wdt-advanced-filter', WDT_JS_PATH . 'wpdatatables/wdt.columnFilter.js', array(), WDT_CURRENT_VERSION, true);
                wp_localize_script('wdt-advanced-filter', 'wdt_ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
                wp_localize_script('wdt-advanced-filter', 'wpdatatables_filter_strings', ToolsService::getTranslationStringsColumnFilter());
            }
            if ($table->groupingEnabled()) {
                wp_enqueue_script('wdt-row-grouping', WDT_JS_PATH . 'jquery-datatables/jquery.dataTables.rowGrouping.js', array('jquery',
                    'wdt-datatables'), WDT_CURRENT_VERSION, true);
            }
            if ($table->TTEnabled() || $table->isEditable()) {
                wp_enqueue_script('wdt-buttons', WDT_JS_PATH . 'export-tools/dataTables.buttons.min.js', array('jquery',
                    'wdt-datatables'), WDT_CURRENT_VERSION, true);
                if ($table->TTEnabled()) {
                    wp_enqueue_script('wdt-buttons-html5', WDT_JS_PATH . 'export-tools/buttons.html5.min.js', array('jquery',
                        'wdt-datatables'), WDT_CURRENT_VERSION, true);
                    !empty($tableToolsConfig['print']) ? wp_enqueue_script('wdt-button-print', WDT_JS_PATH . 'export-tools/buttons.print.min.js', array('jquery',
                        'wdt-datatables'), WDT_CURRENT_VERSION, true) : null;
                    !empty($tableToolsConfig['columns']) ? wp_enqueue_script('wdt-button-vis', WDT_JS_PATH . 'export-tools/buttons.colVis.min.js', array('jquery',
                        'wdt-datatables'), WDT_CURRENT_VERSION, true) : null;
                }
                if ($table->isEditable()) {
                    ToolsService::wdtUIKitEnqueue();
                    wp_localize_script('wdt-common', 'wpdatatables_edit_strings', ToolsService::getTranslationStringsCommon());
                }
            }
            if ($table->isResponsive()) {
                wp_enqueue_script('wdt-responsive', WDT_JS_PATH . 'responsive/datatables.responsive.js', array(), WDT_CURRENT_VERSION, true);
            }
            wp_enqueue_script('wdt-jquery-mask-money', WDT_JS_PATH . 'maskmoney/jquery.maskMoney.js', array('jquery'), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-funcs-js', WDT_JS_PATH . 'wpdatatables/wdt.funcs.js', array('jquery',
                'wdt-datatables',
                'wdt-common'), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-wpdatatables', WDT_JS_PATH . 'wpdatatables/wpdatatables.js', array('jquery',
                'wdt-datatables'), WDT_CURRENT_VERSION, true);
        }

        $skin = $table->getTableSkin();
        if (empty($skin)) {
            $skin = 'light';
        }
        switch ($skin) {
            case "material":
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/material.css';
                break;
            case "light":
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/light.css';
                break;
            case "graphite":
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/graphite.css';
                break;
            case "aqua":
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/aqua.css';
                break;
            case "purple":
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/purple.css';
                break;
            case "dark":
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/dark.css';
                break;
            case "raspberry-cream":
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/raspberry-cream.css';
                break;
            case "mojito":
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/mojito.css';
                break;
            case "dark-mojito":
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/dark-mojito.css';
                break;
            default:
                $renderSkin = WDT_ASSETS_PATH . 'css/wdt-skins/material.css';
                break;
        }
        if (get_option('wdtIncludeGoogleFonts')) {
            wp_enqueue_style('wdt-include-inter-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-include-roboto-google-fonts', 'https://fonts.googleapis.com/css?family=Roboto:wght@400;500&display=swap', array(), WDT_CURRENT_VERSION);
        }

        wp_enqueue_style('wdt-skin-' . $skin, $renderSkin, array(), WDT_CURRENT_VERSION);

        wp_enqueue_style('dashicons');

        wp_enqueue_script('underscore');
        !empty($tableToolsConfig['excel']) ? wp_enqueue_script('wdt-js-zip', WDT_JS_PATH . 'export-tools/jszip.min.js', array('jquery'), WDT_CURRENT_VERSION, true) : null;
        !empty($tableToolsConfig['pdf']) ? wp_enqueue_script('wdt-pdf-make', WDT_JS_PATH . 'export-tools/pdfmake.min.js', array('jquery'), WDT_CURRENT_VERSION, true) : null;
        !empty($tableToolsConfig['pdf']) ? wp_enqueue_script('wdt-vfs-fonts', WDT_JS_PATH . 'export-tools/vfs_fonts.js', array('jquery'), WDT_CURRENT_VERSION, true) : null;

        if (!(is_admin() &&
                function_exists('register_block_type') &&
                (substr($_SERVER['PHP_SELF'], '-8') == 'post.php' ||
                    substr($_SERVER['PHP_SELF'], '-12') == 'post-new.php')
            ) && $table->isEditable()) {
            wp_enqueue_media();
        }

        wp_localize_script('wdt-wpdatatables', 'wpdatatables_settings', ToolsService::getDateTimeSettings());
        wp_localize_script('wdt-wpdatatables', 'wpdatatables_frontend_strings', ToolsService::getTranslationStringsWpDataTables());
        wp_localize_script('wdt-wpdatatables', 'wdt_ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
        do_action_deprecated('wdt_enqueue_on_frontend', array($table), WDT_INITIAL_STARTER_VERSION, 'wpdatatables_enqueue_on_frontend');
        do_action('wpdatatables_enqueue_on_frontend', $table);
    }

    /**
     * Enqueue JS assets for an Excel/simple (Handsontable) table.
     *
     * The legacy {@see \WPExcelDataTable::renderWithJSAndStyles()} enqueue
     * block is a thin delegator into this method.
     *
     * @param \WPExcelDataTable $table
     *
     * @return void
     */
    public function enqueueExcel(\WPExcelDataTable $table)
    {
        $jsExt = get_option('wdtMinifiedJs') ? '.min.js' : '.js';

        ToolsService::wdtUIKitEnqueue();

        if (WDT_INCLUDE_DATATABLES_CORE) {
            wp_register_script('handsontable', WDT_JS_PATH . 'handsontable/handsontable.full' . $jsExt, array('jquery'), WDT_CURRENT_VERSION);
            wp_enqueue_script('handsontable');
        }

        wp_enqueue_script('wpdatatables-urijs', WDT_JS_PATH . 'urijs/URI.min.js', array(), WDT_CURRENT_VERSION);
        wp_enqueue_script('moment', WDT_JS_PATH . 'moment/moment.js', array(), WDT_CURRENT_VERSION);
        wp_enqueue_media();

        wp_register_script(
            'wpdatatables_excel',
            WDT_JS_PATH . 'wpdatatables/wdt.excel' . $jsExt,
            array('jquery', 'handsontable', 'wpdatatables-urijs'),
            WDT_CURRENT_VERSION
        );
        wp_enqueue_script(
            'wpdatatables_excel_plugin',
            WDT_JS_PATH . 'wpdatatables/wdt.excelPlugin' . $jsExt,
            array('jquery', 'handsontable'),
            WDT_CURRENT_VERSION
        );
        wp_enqueue_script('wpdatatables_excel');

        wp_localize_script('wpdatatables_excel', 'wpdatatables_excel_strings', ToolsService::getTranslationStringsExcel());
        wp_localize_script('wpdatatables_excel_plugin', 'wpdatatables_excel_strings', ToolsService::getTranslationStringsExcelPlugin());
    }
}
