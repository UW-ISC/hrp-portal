<?php

/**
 * Registers wpDataTables Abilities API abilities for MCP.
 *
 * @package wpDataTables
 */

namespace WDTMCP\Infrastructure\WP\MCP;

defined('ABSPATH') or die('Access denied.');

class WdtmcpAbilitiesRegistrar
{
    private const ABILITY_FILES = array(
        'list-tables.php' => 'wdtmcp_register_list_tables_ability',
        'get-table-info.php' => 'wdtmcp_register_get_table_info_ability',
        'get-table-data.php' => 'wdtmcp_register_get_table_data_ability',
        'list-charts.php' => 'wdtmcp_register_list_charts_ability',
        'get-chart-info.php' => 'wdtmcp_register_get_chart_info_ability',
        'get-system-info.php' => 'wdtmcp_register_get_system_info_ability',
        'get-mcp-license-matrix.php' => 'wdtmcp_register_get_mcp_license_matrix_ability',
        'get-changelog.php' => 'wdtmcp_register_get_changelog_ability',
        'list-db-tables.php' => 'wdtmcp_register_list_db_tables_ability',
        'describe-db-table.php' => 'wdtmcp_register_describe_db_table_ability',
        'create-simple-table.php' => 'wdtmcp_register_create_simple_table_ability',
        'update-simple-table-styles.php' => 'wdtmcp_register_update_simple_table_styles_ability',
        'list-media.php' => 'wdtmcp_register_list_media_ability',
        'upload-media-from-url.php' => 'wdtmcp_register_upload_media_from_url_ability',
        'upload-data-file.php' => 'wdtmcp_register_upload_data_file_ability',
        'create-table-from-source.php' => 'wdtmcp_register_create_table_from_source_ability',
        'import-table-from-source.php' => 'wdtmcp_register_import_table_from_source_ability',
        'create-table-from-query.php' => 'wdtmcp_register_create_table_from_query_ability',
        'update-table-settings.php' => 'wdtmcp_register_update_table_settings_ability',
        'edit-table.php' => 'wdtmcp_register_edit_table_ability',
        'update-global-settings.php' => 'wdtmcp_register_update_global_settings_ability',
        'create-chart.php' => 'wdtmcp_register_create_chart_ability',
        'edit-chart.php' => 'wdtmcp_register_edit_chart_ability',
    );

    public static function registerCategories(): void
    {
        if (! function_exists('wp_register_ability_category')) {
            return;
        }

        wp_register_ability_category('wpdatatables-data', array(
            'label' => __('wpDataTables - Data', 'wpdatatables'),
            'description' => __(
                'Abilities for interacting with wpDataTables data, configuration, media, table creation, and charts.',
                'wpdatatables'
            ),
        ));
    }

    public static function registerAbilities(): void
    {
        if (! function_exists('wp_register_ability')) {
            return;
        }

        require_once WDT_ROOT_PATH . 'Infrastructure/WP/MCP/Abilities/chart-save-mcp.php';

        foreach (self::ABILITY_FILES as $file => $registrationFunction) {
            require_once WDT_ROOT_PATH . 'Infrastructure/WP/MCP/Abilities/' . $file;

            if (function_exists($registrationFunction)) {
                $registrationFunction();
            }
        }
    }
}
