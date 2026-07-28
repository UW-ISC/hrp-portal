<?php
/**
 * Ability: wpdatatables/get-table-info
 *
 * Returns the full configuration of a single wpDataTable,
 * including column definitions, data-source details, and behavioural flags.
 *
 * Uses WDTConfigController::loadTableFromDB() to stay in sync with
 * wpDataTables' own config layer.
 *
 * @package wpDataTables_MCP_Server
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_get_table_info_ability' );

function wdtmcp_register_get_table_info_ability() {
    wp_register_ability(
        'wpdatatables/get-table-info',
        array(
            'label'       => __( 'Get wpDataTable Info', 'wpdatatables' ),
            'description' => __( 'Returns the full configuration of a single wpDataTable by ID, including title, source type, SQL query or file source, database connection, column definitions (name, type, label, visibility, position, filter type, sorting, text_before/text_after for prefix/suffix), and behavioural flags. Use this after list-tables to understand a table\'s structure before querying its data. WHEN TO CALL: before reading rows, changing settings, or creating a chart from a table — whenever you need column names (orig_header), types, filters, or whether server_side is enabled. REQUIRED INPUT: table_id (integer) from list-tables. RETURNS: full table config including columns[] with orig_header, display_header, type, filter_type, visible, server_side, pagination, sorting, filtering, and more. TYPICAL WORKFLOW: list-tables → get-table-info → get-table-data (use orig_header values for order_by and column_search). For Simple tables (table_type=simple), use create-simple-table / update-simple-table-styles instead of update-table-settings.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_id' => array(
                        'type'        => 'integer',
                        'description' => 'The unique wpDataTable ID (as returned by list-tables).',
                    ),
                ),
                'required' => array( 'table_id' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'id'               => array( 'type' => 'integer',  'description' => 'Table ID.' ),
                    'title'            => array( 'type' => 'string',   'description' => 'Table title.' ),
                    'description'      => array( 'type' => 'string',   'description' => 'Optional table description.' ),
                    'table_type'       => array( 'type' => 'string',   'description' => 'Source type (mysql, csv, xls, google_spreadsheet, json, xml, serialized, simple, manual).' ),
                    'connection'       => array( 'type' => 'string',   'description' => 'Database connection identifier (empty for default WP connection).' ),
                    'mysql_table_name' => array( 'type' => 'string',   'description' => 'Underlying MySQL table name (for SQL-based tables).' ),
                    'content'          => array( 'type' => 'string',   'description' => 'SQL query or file path / URL that provides the table data.' ),
                    'server_side'      => array( 'type' => 'boolean',  'description' => 'Whether server-side processing is enabled.' ),
                    'editable'         => array( 'type' => 'boolean',  'description' => 'Whether front-end editing is enabled.' ),
                    'inline_editing'   => array( 'type' => 'boolean',  'description' => 'Whether inline (cell) editing is enabled.' ),
                    'filtering'        => array( 'type' => 'boolean',  'description' => 'Whether column filtering is enabled.' ),
                    'global_search'    => array( 'type' => 'boolean',  'description' => 'Whether the global search box is shown.' ),
                    'sorting'          => array( 'type' => 'boolean',  'description' => 'Whether column sorting is enabled.' ),
                    'responsive'       => array( 'type' => 'boolean',  'description' => 'Whether responsive mode is enabled.' ),
                    'pagination'       => array( 'type' => 'boolean',  'description' => 'Whether pagination is enabled.' ),
                    'display_length'   => array( 'type' => 'integer',  'description' => 'Default number of rows per page.' ),
                    'cache_source_data' => array( 'type' => 'boolean', 'description' => 'Whether source data caching is enabled.' ),
                    'columns' => array(
                        'type'        => 'array',
                        'description' => 'Column definitions ordered by position.',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'id'             => array( 'type' => 'integer', 'description' => 'Column ID.' ),
                                'orig_header'    => array( 'type' => 'string',  'description' => 'Original (internal) column header / key.' ),
                                'display_header' => array( 'type' => 'string',  'description' => 'Display label shown to users.' ),
                                'type'           => array( 'type' => 'string',  'description' => 'Column data type (string, int, float, date, datetime, time, link, email, image, formula, select).' ),
                                'filter_type'    => array( 'type' => 'string',  'description' => 'Filter widget type (none, text, number, number-range, date-range, select, checkbox, multiselect).' ),
                                'visible'        => array( 'type' => 'boolean', 'description' => 'Whether the column is visible by default.' ),
                                'position'       => array( 'type' => 'integer', 'description' => 'Column display position (0-based).' ),
                                'sorting'        => array( 'type' => 'boolean', 'description' => 'Whether sorting is enabled for this column.' ),
                                'formula'        => array( 'type' => 'string',  'description' => 'Calculation formula (for formula columns, empty otherwise).' ),
                                'text_before'    => array( 'type' => 'string',  'description' => 'Cell content prefix (e.g. $ for currency), shown before value.' ),
                                'text_after'     => array( 'type' => 'string',  'description' => 'Cell content suffix (e.g. % for percentages), shown after value.' ),
                                'prefix'         => array( 'type' => 'string',  'description' => 'Alias for text_before (cell content prefix).' ),
                                'suffix'         => array( 'type' => 'string',  'description' => 'Alias for text_after (cell content suffix).' ),
                            ),
                        ),
                    ),
                    'column_count' => array( 'type' => 'integer', 'description' => 'Total number of columns.' ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_get_table_info',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Requires table_id from list-tables. Inspect columns.orig_header, table_type, and server_side before calling get-table-data, update-table-settings, or create-chart.', 'wpdatatables' ),
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
            ),
        )
    );
}

/**
 * Execute callback for wpdatatables/get-table-info.
 *
 * @param array $input { table_id: int }
 * @return array|WP_Error
 */
function wdtmcp_execute_get_table_info( $input ) {
    $table_id = isset( $input['table_id'] ) ? (int) $input['table_id'] : 0;

    if ( $table_id <= 0 ) {
        return new \WP_Error(
            'wdtmcp_invalid_input',
            __( 'A valid table_id (positive integer) is required.', 'wpdatatables' )
        );
    }

    if ( ! class_exists( 'WDTConfigController' ) ) {
        return new \WP_Error(
            'wdtmcp_missing_class',
            __( 'wpDataTables core class WDTConfigController is not available.', 'wpdatatables' )
        );
    }

    try {
        $table_data = \WDTConfigController::loadTableFromDB( $table_id );
    } catch ( \Exception $e ) {
        return new \WP_Error(
            'wdtmcp_load_error',
            sprintf(
                __( 'Error loading table %d: %s', 'wpdatatables' ),
                $table_id,
                $e->getMessage()
            )
        );
    }

    if ( empty( $table_data ) ) {
        return new \WP_Error(
            'wdtmcp_not_found',
            sprintf( __( 'Table with ID %d was not found.', 'wpdatatables' ), $table_id )
        );
    }

    $columns = array();
    if ( ! empty( $table_data->columns ) && is_array( $table_data->columns ) ) {
        foreach ( $table_data->columns as $col ) {
            $columns[] = array(
                'id'             => isset( $col->id ) ? (int) $col->id : 0,
                'orig_header'    => isset( $col->orig_header ) ? (string) $col->orig_header : '',
                'display_header' => isset( $col->display_header ) ? (string) $col->display_header : '',
                'type'           => isset( $col->type ) ? (string) $col->type : '',
                'filter_type'    => isset( $col->filter_type ) ? (string) $col->filter_type : 'none',
                'visible'        => ! empty( $col->visible ),
                'position'       => isset( $col->pos ) ? (int) $col->pos : 0,
                'sorting'        => isset( $col->sorting ) ? ! empty( $col->sorting ) : true,
                'formula'        => isset( $col->formula ) ? (string) $col->formula : '',
                'text_before'    => isset( $col->text_before ) ? (string) $col->text_before : '',
                'text_after'     => isset( $col->text_after ) ? (string) $col->text_after : '',
                'prefix'         => isset( $col->text_before ) ? (string) $col->text_before : '',
                'suffix'         => isset( $col->text_after ) ? (string) $col->text_after : '',
            );
        }
    }

    $table_type_raw = isset( $table_data->table_type ) ? $table_data->table_type : '';
    $table_type     = ( $table_type_raw === 'mysql' ) ? 'SQL' : $table_type_raw;

    $out = array(
        'id'                => (int) $table_data->id,
        'title'             => (string) $table_data->title,
        'description'       => isset( $table_data->table_description ) ? (string) $table_data->table_description : '',
        'table_type'        => $table_type,
        'connection'        => isset( $table_data->connection ) ? (string) $table_data->connection : '',
        'mysql_table_name'  => isset( $table_data->mysql_table_name ) ? (string) $table_data->mysql_table_name : '',
        'content'           => isset( $table_data->content ) ? (string) $table_data->content : '',
        'server_side'       => ! empty( $table_data->server_side ),
        'editable'          => ! empty( $table_data->editable ),
        'inline_editing'    => ! empty( $table_data->inline_editing ),
        'filtering'         => ! empty( $table_data->filtering ),
        'global_search'     => ! empty( $table_data->global_search ),
        'sorting'           => ! empty( $table_data->sorting ),
        'responsive'        => ! empty( $table_data->responsive ),
        'pagination'        => ! empty( $table_data->pagination ),
        'display_length'    => isset( $table_data->display_length ) ? (int) $table_data->display_length : 10,
        'cache_source_data' => ! empty( $table_data->cache_source_data ),
        'columns'           => $columns,
        'column_count'      => count( $columns ),
    );
    $stored_css = function_exists( 'wdtmcp_get_table_custom_css' ) ? wdtmcp_get_table_custom_css( $table_id ) : '';
    if ( '' !== $stored_css ) {
        $out['custom_css'] = $stored_css;
    }
    return $out;
}
