<?php
/**
 * Ability: wpdatatables/edit-table
 *
 * Single entry point for editing an existing wpDataTable: dispatches to Simple-table
 * content/layout updates or standard table display/column settings based on table_type.
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_edit_table_ability' );

function wdtmcp_register_edit_table_ability() {
    wp_register_ability(
        'wpdatatables/edit-table',
        array(
            'label'       => __( 'Edit Table', 'wpdatatables' ),
            'description' => __( 'Edits an existing wpDataTable. Automatically applies the correct update path: Simple tables (table_type=simple) — cell content, merges, template, custom_css (same as update-simple-table-styles); all other types (CSV, Excel, SQL, etc.) — title, pagination, sorting, filtering, column types/labels (same as update-table-settings). REQUIRED: table_id. Load table type with get-table-info first. Pass only fields relevant to that table kind. RETURNS: table_id, updated[] (and custom_css for Simple when set).', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_id' => array(
                        'type'        => 'integer',
                        'description' => 'The wpDataTable ID to edit.',
                    ),
                    'title' => array(
                        'type'        => 'string',
                        'description' => 'New table title (non-Simple tables only).',
                    ),
                    'sorting' => array(
                        'type'        => 'boolean',
                        'description' => 'Enable column sorting (non-Simple).',
                    ),
                    'filtering' => array(
                        'type'        => 'boolean',
                        'description' => 'Enable filtering (non-Simple).',
                    ),
                    'pagination' => array(
                        'type'        => 'boolean',
                        'description' => 'Enable pagination (non-Simple).',
                    ),
                    'responsive' => array(
                        'type'        => 'boolean',
                        'description' => 'Responsive mode (non-Simple).',
                    ),
                    'display_length' => array(
                        'type'        => 'integer',
                        'description' => 'Rows per page (non-Simple).',
                    ),
                    'server_side' => array(
                        'type'        => 'boolean',
                        'description' => 'Server-side processing (non-Simple).',
                    ),
                    'global_search' => array(
                        'type'        => 'boolean',
                        'description' => 'Global search box (non-Simple).',
                    ),
                    'columns' => array(
                        'type'        => 'array',
                        'description' => 'Column updates for non-Simple tables; each entry needs orig_header from get-table-info.',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'orig_header'    => array( 'type' => 'string' ),
                                'type'           => array( 'type' => 'string' ),
                                'filter_type'    => array( 'type' => 'string' ),
                                'display_header' => array( 'type' => 'string' ),
                                'visible'        => array( 'type' => 'boolean' ),
                                'width'          => array( 'type' => 'string' ),
                                'sorting'        => array( 'type' => 'boolean' ),
                                'text_before'    => array( 'type' => 'string' ),
                                'text_after'     => array( 'type' => 'string' ),
                                'prefix'         => array( 'type' => 'string' ),
                                'suffix'         => array( 'type' => 'string' ),
                            ),
                        ),
                    ),
                    'table_settings' => array(
                        'type'        => 'object',
                        'description' => 'Simple table layout flags (template, stripes, etc.).',
                        'properties'  => array(
                            'template_id'              => array( 'type' => 'integer' ),
                            'simple_header'            => array( 'type' => 'boolean' ),
                            'stripe_table'             => array( 'type' => 'boolean' ),
                            'responsive'               => array( 'type' => 'boolean' ),
                            'remove_borders'           => array( 'type' => 'boolean' ),
                            'cell_padding'             => array( 'type' => 'integer' ),
                            'show_title'               => array( 'type' => 'boolean' ),
                            'show_table_description'   => array( 'type' => 'boolean' ),
                            'table_description'        => array( 'type' => 'string' ),
                        ),
                    ),
                    'cell_updates' => array(
                        'type'        => 'array',
                        'description' => 'Simple table: {row_index, col_index, data?, attachment_id?, meta?}.',
                    ),
                    'merged_cells' => array(
                        'type'        => 'array',
                        'description' => 'Simple table: merged cells {row, col, rowspan, colspan}; empty array clears.',
                    ),
                    'custom_css' => array(
                        'type'        => 'string',
                        'description' => 'Simple table: CSS with #wpdtSimpleTable-{table_id}; empty string clears.',
                    ),
                    'row_heights' => array(
                        'type'        => 'array',
                        'description' => 'Simple table: row heights in px per row index.',
                        'items'       => array( 'type' => 'integer' ),
                    ),
                ),
                'required' => array( 'table_id' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_id' => array( 'type' => 'integer', 'description' => 'Edited table ID.' ),
                    'updated'  => array(
                        'type'        => 'array',
                        'items'       => array( 'type' => 'string' ),
                        'description' => 'List of fields or cells changed.',
                    ),
                    'custom_css' => array(
                        'type'        => 'string',
                        'description' => 'Substituted CSS (Simple tables only, when custom_css was sent).',
                    ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_edit_table',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Call get-table-info for table_type. Simple → use cell_updates, table_settings, merged_cells, custom_css. Other types → use title, columns, sorting, etc.', 'wpdatatables' ),
                    'readonly'     => false,
                    'destructive'  => false,
                    'idempotent'   => false,
                ),
            ),
        )
    );
}

/**
 * Execute wpdatatables/edit-table: route by table_type.
 *
 * @param array $input Same shape as update-table-settings or update-simple-table-styles.
 * @return array|\WP_Error
 */
function wdtmcp_execute_edit_table( $input ) {
    global $wpdb;

    $table_id = isset( $input['table_id'] ) ? (int) $input['table_id'] : 0;
    if ( $table_id <= 0 ) {
        return new \WP_Error(
            'wdtmcp_invalid_input',
            __( 'table_id is required and must be a positive integer.', 'wpdatatables' )
        );
    }

    $table_type = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT table_type FROM {$wpdb->prefix}wpdatatables WHERE id = %d",
            $table_id
        )
    );

    if ( null === $table_type ) {
        return new \WP_Error(
            'wdtmcp_not_found',
            sprintf(
                /* translators: %d: table ID */
                __( 'Table with ID %d was not found.', 'wpdatatables' ),
                $table_id
            )
        );
    }

    if ( 'simple' === $table_type ) {
        if ( ! function_exists( 'wdtmcp_execute_update_simple_table_styles' ) ) {
            return new \WP_Error(
                'wdtmcp_missing_handler',
                __( 'Simple table update handler is not loaded.', 'wpdatatables' )
            );
        }

        return wdtmcp_execute_update_simple_table_styles( $input );
    }

    if ( ! function_exists( 'wdtmcp_execute_update_table_settings' ) ) {
        return new \WP_Error(
            'wdtmcp_missing_handler',
            __( 'Table settings update handler is not loaded.', 'wpdatatables' )
        );
    }

    return wdtmcp_execute_update_table_settings( $input );
}
