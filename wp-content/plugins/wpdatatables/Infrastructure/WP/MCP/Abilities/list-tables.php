<?php
/**
 * Ability: wpdatatables/list-tables
 *
 * Lists all wpDataTables tables with summary metadata.
 * Uses wpDataTable::getAllTables() to stay in sync with the parent plugin.
 *
 * @package wpDataTables_MCP_Server
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_list_tables_ability' );

function wdtmcp_register_list_tables_ability() {
    wp_register_ability(
        'wpdatatables/list-tables',
        array(
            'label'       => __( 'List wpDataTables', 'wpdatatables' ),
            'description' => __( 'Returns a list of all tables created in wpDataTables, including each table\'s ID, title, source type (SQL, CSV, Excel, Google Sheets, JSON, XML, serialized PHP array, simple, manual), database connection identifier, and whether server-side processing is enabled. Use this to discover available tables before inspecting or querying their data. WHEN TO CALL: start here whenever you need to find tables on this WordPress site, look up a table ID by title, or decide which table to inspect next. No parameters are required — pass an empty object {}. RETURNS: { tables: [{ id, title, table_type, connection, server_side }], count }. TYPICAL NEXT STEPS: call get-table-info with the chosen table_id to see columns and settings, then get-table-data to read rows. If you already know the table_id, you may skip straight to get-table-info or get-table-data.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(),
                'required'   => array(),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'tables' => array(
                        'type'        => 'array',
                        'description' => 'Array of table summaries.',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'id' => array(
                                    'type'        => 'integer',
                                    'description' => 'The unique table ID.',
                                ),
                                'title' => array(
                                    'type'        => 'string',
                                    'description' => 'The table title.',
                                ),
                                'table_type' => array(
                                    'type'        => 'string',
                                    'description' => 'The table source type (SQL, csv, xls, google_spreadsheet, json, xml, serialized, simple, manual).',
                                ),
                                'connection' => array(
                                    'type'        => 'string',
                                    'description' => 'The database connection identifier used by this table (empty string for the default WordPress connection).',
                                ),
                                'server_side' => array(
                                    'type'        => 'boolean',
                                    'description' => 'Whether server-side processing is enabled.',
                                ),
                            ),
                        ),
                    ),
                    'count' => array(
                        'type'        => 'integer',
                        'description' => 'Total number of tables.',
                    ),
                ),
            ),

            'execute_callback' => function () {
                if ( ! class_exists( 'WPDataTable' ) ) {
                    return new \WP_Error(
                        'wdtmcp_missing_class',
                        __( 'wpDataTables core class WPDataTable is not available.', 'wpdatatables' )
                    );
                }

                $rows = \WPDataTable::getAllTables();

                if ( ! is_array( $rows ) ) {
                    return new \WP_Error(
                        'wdtmcp_query_failed',
                        __( 'Failed to retrieve tables from wpDataTables.', 'wpdatatables' )
                    );
                }

                $tables = array_map( static function ( $row ) {
                    return array(
                        'id'          => (int) $row['id'],
                        'title'       => (string) $row['title'],
                        'table_type'  => (string) $row['table_type'],
                        'connection'  => isset( $row['connection'] ) ? (string) $row['connection'] : '',
                        'server_side' => ! empty( $row['server_side'] ),
                    );
                }, $rows );

                return array(
                    'tables' => $tables,
                    'count'  => count( $tables ),
                );
            },

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Call with an empty object {}. Use the returned table id with get-table-info or get-table-data. Start here when you do not yet know which tables exist.', 'wpdatatables' ),
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
            ),
        )
    );
}
