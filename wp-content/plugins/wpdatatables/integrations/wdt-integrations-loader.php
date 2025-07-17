<?php

namespace WDTIntegration;

defined('ABSPATH') or die('Access denied.');

/**
 * Class WDTIntegrationsLoader
 */
class WDTIntegrationsLoader
{
    public static function init()
    {

        // Include page builders
        require_once(WDT_STARTER_INTEGRATIONS_PATH . 'page-builders/gutenberg/GutenbergBlock.php');
        require_once(WDT_STARTER_INTEGRATIONS_PATH . 'page-builders/gutenberg/WpDataTablesGutenbergBlock.php');
        require_once(WDT_STARTER_INTEGRATIONS_PATH . 'page-builders/gutenberg/WpDataChartsGutenbergBlock.php');
        require_once(WDT_STARTER_INTEGRATIONS_PATH . 'page-builders/elementor/class.wdtelementorblock.php');
        require_once(WDT_STARTER_INTEGRATIONS_PATH . 'page-builders/divi-wpdt/divi-wpdt.php');
        require_once(WDT_STARTER_INTEGRATIONS_PATH . 'page-builders/avada/class.wdtavadaelements.php');
        require_once(WDT_STARTER_INTEGRATIONS_PATH . 'page-builders/wpbakery/wdtBakeryBlock.php');
        require_once(WDT_STARTER_INTEGRATIONS_PATH . 'page-builders/wpbakery/wdtCustomBakery.php');

        // Include Global Page Search
        if (is_file(WDT_STARTER_INTEGRATIONS_PATH . 'global-search-for-all-tables/wdt-global-search-all-tables-integration.php')) {
            require_once(WDT_STARTER_INTEGRATIONS_PATH . 'global-search-for-all-tables/wdt-global-search-all-tables-integration.php');
        }

        // Include Separate DB connection
        if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'separate-db-connection/source/class.pgsql.connection.php'))
            require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'separate-db-connection/source/class.pgsql.connection.php');
        if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'separate-db-connection/source/class.sql.php'))
            require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'separate-db-connection/source/class.sql.php');
        if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'separate-db-connection/source/class.sql.pdo.php'))
            require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'separate-db-connection/source/class.sql.pdo.php');
        if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'separate-db-connection/wdt-separate-connection-integration.php')) {
            require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'separate-db-connection/wdt-separate-connection-integration.php');
        }

        // Include HighStock
        if (is_file(WDT_PRO_INTEGRATIONS_PATH . 'highstock/wdt-highstock-integration.php')) {
            require_once(WDT_PRO_INTEGRATIONS_PATH . 'highstock/wdt-highstock-integration.php');
        }

        // Include HighCharts
        if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'highcharts/wdt-highcharts-integration.php')) {
            require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'highcharts/wdt-highcharts-integration.php');
        }

        // Include ApexCharts
        if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'apexcharts/wdt-apexcharts-integration.php')) {
            require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'apexcharts/wdt-apexcharts-integration.php');
        }

        // Include Placeholders
        if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'placeholders/wdt-placeholders-integration.php')) {
            require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'placeholders/wdt-placeholders-integration.php');
        }

        // Include SQL Query
        if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'sql-query/wdt-sql-query-integration.php')) {
            require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'sql-query/wdt-sql-query-integration.php');
        }

        // Include Fixed Columns and Headers
        if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'fixed-columns-and-headers/wdt-fixed-ch-integration.php')) {
            require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'fixed-columns-and-headers/wdt-fixed-ch-integration.php');
        }
        // Include Index Column
        if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'index-column/wdt-index-column-integration.php')) {
            require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'index-column/wdt-index-column-integration.php');
        }
        // Include Hidden Column
        if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'hidden-column/wdt-hidden-column-integration.php')) {
            require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'hidden-column/wdt-hidden-column-integration.php');
        }

        // Include Formula Column
        if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'formula-column/wdt-formula-column-integration.php')) {
            require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'formula-column/wdt-formula-column-integration.php');
        }

        // Include Table Editing
        if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'editing/wdt-editing-integration.php')) {
            require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'editing/wdt-editing-integration.php');
        }


        if (is_admin()) {

            // Include SQL Constructor
            if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'sql-constructor/wdt-sql-constructor-integration.php')) {
                require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'sql-constructor/wdt-sql-constructor-integration.php');
            }
            if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'sql-constructor/source/class.sql.constructor.php')) {
                require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'sql-constructor/source/class.sql.constructor.php');
            }

            // Include Google Sheet API settings
            if (is_file(WDT_STARTER_INTEGRATIONS_PATH . 'google-sheet-api/wdt-google-sheet-api-integration.php')) {
                require_once(WDT_STARTER_INTEGRATIONS_PATH . 'google-sheet-api/wdt-google-sheet-api-integration.php');
            }

            // Include Foreign Key
            if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'foreign-key/wdt-foreign-key-integration.php')) {
                require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'foreign-key/wdt-foreign-key-integration.php');
            }

            // Include Update manual tables
            if (is_file(WDT_STANDARD_INTEGRATIONS_PATH . 'update-manual-from-file/wdt-update-manual-from-file-integration.php')) {
                require_once(WDT_STANDARD_INTEGRATIONS_PATH . 'update-manual-from-file/wdt-update-manual-from-file-integration.php');
            }

            // Include Folders
            if (is_file(WDT_PRO_INTEGRATIONS_PATH . 'folders/source/class.wpdatafolders.php'))
                require_once(WDT_PRO_INTEGRATIONS_PATH . 'folders/source/class.wpdatafolders.php');
            if (is_file(WDT_PRO_INTEGRATIONS_PATH . 'folders/source/class.factory.wpdatafolders.php'))
                require_once(WDT_PRO_INTEGRATIONS_PATH . 'folders/source/class.factory.wpdatafolders.php');
            if (is_file(WDT_PRO_INTEGRATIONS_PATH . 'folders/source/class.tables.wpdatafolders.php'))
                require_once(WDT_PRO_INTEGRATIONS_PATH . 'folders/source/class.tables.wpdatafolders.php');
            if (is_file(WDT_PRO_INTEGRATIONS_PATH . 'folders/source/class.charts.wpdatafolders.php'))
                require_once(WDT_PRO_INTEGRATIONS_PATH . 'folders/source/class.charts.wpdatafolders.php');
            if (is_file(WDT_PRO_INTEGRATIONS_PATH . 'folders/source/class.reports.wpdatafolders.php'))
                require_once(WDT_PRO_INTEGRATIONS_PATH . 'folders/source/class.reports.wpdatafolders.php');

            add_action('wpdatatables_add_chart_picker', array('WDTIntegration\WDTIntegrationsLoader',
                'addChartPickerStepNotice'));

            add_action('wpdatatables_add_table_configuration_tabpanel', array('WDTIntegration\WDTIntegrationsLoader',
                'addNewFixedHeaderAndColumnsOptions'));

            add_action('wpdatatables_add_table_editing_elements', array('WDTIntegration\WDTIntegrationsLoader',
                'addNoticeEditingOptions'));

            add_action('wpdatatables_add_column_editing_elements', array('WDTIntegration\WDTIntegrationsLoader',
                'addNoticeEditingOptions'));

            add_action('wpdatatables_add_table_placeholders_elements', array('WDTIntegration\WDTIntegrationsLoader',
                'addNoticePlaceholdersOptions'));

            add_action('wpdatatables_add_constructor_step_in_wizard', array('WDTIntegration\WDTIntegrationsLoader',
                'addNewTableTypesInConstructor'));

            add_action('wpdatatables_add_tab_in_main_settings', array('WDTIntegration\WDTIntegrationsLoader',
                'addSeparateConnectionSettings'));

            add_action('wpdatatables_add_tab_in_main_settings', array('WDTIntegration\WDTIntegrationsLoader',
                'addGoogleSheetAPISettings'));

            add_action('wpdatatables_add_foreign_key_block', array('WDTIntegration\WDTIntegrationsLoader',
                'addForeignKeySettings'));

            add_action('wpdatatables_admin_after_edit', array('WDTIntegration\WDTIntegrationsLoader',
                'addFormulaEditorModal'));

            add_action('wpdatatables_add_mysql_settings_block', array('WDTIntegration\WDTIntegrationsLoader',
                'addSQLQueryNotice'));

            add_action('wpdatatables_add_data_from_source_file_block', array('WDTIntegration\WDTIntegrationsLoader',
                'addUpdateManualNoticeBlock'));

            add_action('wpdatatables_add_browse_table_notice_info', array('WDTIntegration\WDTIntegrationsLoader',
                'addFolderNotice'));

            add_action('wpdatatables_add_browse_chart_notice_info', array('WDTIntegration\WDTIntegrationsLoader',
                'addFolderNotice'));

            add_action('wpdatatables_add_custom_column_type_option', array('WDTIntegration\WDTIntegrationsLoader',
                'addHiddenColumnTypeNotice'));

            add_filter('wpdatatables_filter_possible_column_types', array('WDTIntegration\WDTIntegrationsLoader',
                'filterPossibleColumnTypes'));

            add_action('wpdatatables_add_chart_stable_tag_option', array('WDTIntegration\WDTIntegrationsLoader',
                'addChartsStableTagNotice'));

            add_action('wpdatatables_add_options_in_add_column_modal', array('WDTIntegration\WDTIntegrationsLoader',
                'addHiddenColumnAddModalNotice'));

            add_action('wpdatatables_after_constructor_column_block', array('WDTIntegration\WDTIntegrationsLoader',
                'addHiddenColumnConstructorNotice'));

            add_action('wpdatatables_after_constructor_column_block_preview', array('WDTIntegration\WDTIntegrationsLoader',
                'addHiddenColumnConstructorNotice'));
        }

        // Include WP Posts Builder
        if (is_file(WDT_PRO_INTEGRATIONS_PATH . 'query-builder/source/wdt-query-builder.php')) {
            require_once(WDT_PRO_INTEGRATIONS_PATH . 'query-builder/source/wdt-query-builder.php');
        }

        // Include WooCommerce Integration
        if (is_file(WDT_PRO_INTEGRATIONS_PATH . 'woo-commerce/source/wdt-woo-commerce.php')) {
            require_once(WDT_PRO_INTEGRATIONS_PATH . 'woo-commerce/source/wdt-woo-commerce.php');
        }

    }

    public static function addChartPickerStepNotice()
    {
        if (!defined('WDT_HC_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/chart_wizard/steps/charts_pick/highcharts.inc.php';
            $highChartsNotice = ob_get_contents();
            ob_end_clean();
            echo $highChartsNotice;
        }

        if (!defined('WDT_HS_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/chart_wizard/steps/charts_pick/highstock.inc.php';
            $highStockChartsNotice = ob_get_contents();
            ob_end_clean();
            echo $highStockChartsNotice;
        }

        if (!defined('WDT_AC_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/chart_wizard/steps/charts_pick/apexcharts.inc.php';
            $apexChartsNotice = ob_get_contents();
            ob_end_clean();
            echo $apexChartsNotice;
        }

    }

    public static function addNoticePlaceholdersOptions()
    {
        if (!defined('WDT_PH_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/placeholders_notice_block.inc.php';
            $placeholdersNotice = ob_get_contents();
            ob_end_clean();
            echo $placeholdersNotice;
        }
    }

    public static function addChartsStableTagNotice()
    {
        if (!defined('WDT_HC_INTEGRATION') || !defined('WDT_AC_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/charts_stable_tag_notice_block.inc.php';
            $placeholdersNotice = ob_get_contents();
            ob_end_clean();
            echo $placeholdersNotice;
        }
    }

    public static function addHiddenColumnTypeNotice()
    {
        if (!defined('WDT_HCOL_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/hidden_column_type_option_notice.inc.php';
            $hiddenColumnNotice = ob_get_contents();
            ob_end_clean();
            echo $hiddenColumnNotice;
        }
    }

    public static function addHiddenColumnConstructorNotice()
    {
        if (!defined('WDT_HCOL_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/hidden_column_constructor_notice_block.inc.php';
            $hiddenColumnConstructorNotice = ob_get_contents();
            ob_end_clean();
            echo $hiddenColumnConstructorNotice;
        }
    }

    public static function addHiddenColumnAddModalNotice()
    {
        if (!defined('WDT_HCOL_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/hidden_column_add_modal_notice_block.inc.php';
            $hiddenColumnAddModalNotice = ob_get_contents();
            ob_end_clean();
            echo $hiddenColumnAddModalNotice;
        }
    }

    public static function filterPossibleColumnTypes($possibleColumnTypes)
    {
        if (!defined('WDT_HCOL_INTEGRATION')) {
            $newColVal =
                array('hidden' => __('Hidden (Dynamic) - Available from Standard Licence', 'wpdatatables'));

            return array_merge(
                array_slice($possibleColumnTypes, 0, 4),
                $newColVal, array_slice($possibleColumnTypes, 4)
            );
        }

        return $possibleColumnTypes;
    }

    public static function addFolderNotice()
    {
        if (!defined('WDT_FOLDERS_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/folders_notice_block.inc.php';
            $placeholdersNotice = ob_get_contents();
            ob_end_clean();
            echo $placeholdersNotice;
        }
    }

    public static function addUpdateManualNoticeBlock()
    {
        if (!defined('WDT_UMFF_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/update_manual_notice_block.inc.php';
            $placeholdersNotice = ob_get_contents();
            ob_end_clean();
            echo $placeholdersNotice;
        }
    }

    public static function addSQLQueryNotice($connection)
    {
        if (!defined('WDT_SQLQ_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/sql_query_notice_block.inc.php';
            $sqlQueryNotice = ob_get_contents();
            ob_end_clean();
            echo $sqlQueryNotice;
        }
    }

    public static function addNoticeEditingOptions()
    {
        if (!defined('WDT_EDIT_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/editing_notice_block.inc.php';
            $editingNotice = ob_get_contents();
            ob_end_clean();
            echo $editingNotice;
        }
    }

    public static function addNewFixedHeaderAndColumnsOptions()
    {
        if (!defined('WDT_FCH_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/fixed_headers_and_columns_notice_block.inc.php';
            $fchNotice = ob_get_contents();
            ob_end_clean();
            echo $fchNotice;
        }
    }

    public static function addNewTableTypesInConstructor()
    {
        if (!defined('WDT_SQLC_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/sql_integration_notice_block.inc.php';
            $sqlConstructorNotice = ob_get_contents();
            ob_end_clean();
            echo $sqlConstructorNotice;
        }

        if (!defined('WDT_WP_QUERY_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/wp_posts_integration_notice_block.inc.php';
            $wpPostsConstructorNotice = ob_get_contents();
            ob_end_clean();
            echo $wpPostsConstructorNotice;
        }
    }

    public static function addSeparateConnectionSettings()
    {
        if (!defined('WDT_SDBC_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/settings/tabs/separate_connection.php';
            $addElements = ob_get_contents();
            ob_end_clean();
            echo $addElements;
        }
    }

    public static function addGoogleSheetAPISettings()
    {
        if (!defined('WDT_GSAPI_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/settings/tabs/google_sheet_settings.php';
            $addGSAPIElements = ob_get_contents();
            ob_end_clean();
            echo $addGSAPIElements;
        }
    }

    public static function addForeignKeySettings()
    {
        if (!defined('WDT_FKEY_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/foreign_key_settings_block_notice.inc.php';
            $addForeignKeysElements = ob_get_contents();
            ob_end_clean();
            echo $addForeignKeysElements;
        }
    }

    public static function addFormulaEditorModal($connection)
    {
        if (!defined('WDT_FCOL_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/formula_editor_modal_notice.inc.php';
            $formulaEditorModal = ob_get_contents();
            ob_end_clean();
            echo $formulaEditorModal;
        }
    }
}

WDTIntegrationsLoader::init();