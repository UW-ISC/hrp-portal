<?php
/**
 * Ability: wpdatatables/update-table-settings
 *
 * Updates configuration properties of an existing wpDataTable: title, display
 * options (pagination, sorting, filtering, responsive, display_length), column
 * settings (visibility, labels, types, filter types, width), and behavioural
 * flags (server_side, editable, cache_source_data, etc.).
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_update_table_settings_ability' );

function wdtmcp_register_update_table_settings_ability() {
    wp_register_ability(
        'wpdatatables/update-table-settings',
        array(
            'label'       => __( 'Update Table Settings', 'wpdatatables' ),
            'description' => __( 'Updates display and behavioural settings of an existing wpDataTable (source-based, SQL, manual). Change title, pagination, sorting, filtering, display_length; set column types (date, int, string), filter types (date-range, select, checkbox, text), labels, visibility, width, prefix/suffix (or text_before/text_after) for currency/percentage formatting. Use get-table-info first to see current columns and orig_header values. WHEN TO CALL: after creating a table or when the user wants to change how a non-Simple table looks or behaves. NOT for Simple tables — use update-simple-table-styles instead. REQUIRED INPUT: table_id (integer). OPTIONAL: title, sorting, filtering, pagination, responsive, display_length, server_side, global_search, columns[] (each must include orig_header from get-table-info). TYPICAL WORKFLOW: get-table-info → update-table-settings → get-table-info to verify. Use orig_header (not display_header) to identify columns in the columns array.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_id' => array(
                        'type'        => 'integer',
                        'description' => 'The wpDataTable ID to update.',
                    ),
                    'title'          => array( 'type' => 'string',  'description' => 'New table title.' ),
                    'sorting'        => array( 'type' => 'boolean', 'description' => 'Enable/disable column sorting.' ),
                    'filtering'      => array( 'type' => 'boolean', 'description' => 'Enable/disable column filtering.' ),
                    'pagination'     => array( 'type' => 'boolean', 'description' => 'Enable/disable pagination.' ),
                    'responsive'     => array( 'type' => 'boolean', 'description' => 'Enable/disable responsive mode.' ),
                    'display_length' => array( 'type' => 'integer', 'description' => 'Rows per page.' ),
                    'server_side'    => array( 'type' => 'boolean', 'description' => 'Enable/disable server-side processing.' ),
                    'global_search'   => array( 'type' => 'boolean', 'description' => 'Enable/disable global search box.' ),
                    'columns'        => array(
                        'type'        => 'array',
                        'description' => 'Array of column updates. Each object must include orig_header and the properties to change. Use type for column data type (string, int, float, date, datetime, time, link, email, image). Use filter_type for filter widget: none, text, number, number-range, date-range, datetime-range, time-range, select, multiselect, checkbox.',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'orig_header'    => array( 'type' => 'string',  'description' => 'Column key (from get-table-info). Required.' ),
                                'type'           => array( 'type' => 'string',  'description' => 'Column data type: string, int, float, date, datetime, time, link, email, image.' ),
                                'filter_type'    => array( 'type' => 'string',  'description' => 'Filter widget: none, text, number, number-range, date-range, datetime-range, time-range, select, multiselect, checkbox.' ),
                                'display_header' => array( 'type' => 'string',  'description' => 'New display label.' ),
                                'visible'        => array( 'type' => 'boolean', 'description' => 'Column visibility.' ),
                                'width'          => array( 'type' => 'string',  'description' => 'Column width (e.g. "150px").' ),
                                'sorting'        => array( 'type' => 'boolean', 'description' => 'Enable/disable sorting for this column.' ),
                                'text_before'    => array( 'type' => 'string',  'description' => 'Cell content prefix (e.g. "$" for currency, shown before value).' ),
                                'text_after'     => array( 'type' => 'string',  'description' => 'Cell content suffix (e.g. "%" for percentages, shown after value).' ),
                                'prefix'         => array( 'type' => 'string',  'description' => 'Alias for text_before (cell content prefix, e.g. "$").' ),
                                'suffix'         => array( 'type' => 'string',  'description' => 'Alias for text_after (cell content suffix, e.g. "%").' ),
                            ),
                        ),
                    ),
                ),
                'required' => array( 'table_id' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_id' => array( 'type' => 'integer', 'description' => 'Updated table ID.' ),
                    'updated'  => array( 'type' => 'array',   'description' => 'List of properties that were changed.', 'items' => array( 'type' => 'string' ) ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_update_table_settings',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Not for Simple tables. Call get-table-info first and identify columns by orig_header. Re-applying the same settings is safe (idempotent).', 'wpdatatables' ),
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
            ),
        )
    );
}

/**
 * Valid column types (maps to wpdatatables_columns.column_type).
 *
 * @var array
 */
$GLOBALS['wdtmcp_valid_column_types'] = array( 'string', 'int', 'float', 'date', 'datetime', 'time', 'link', 'email', 'image', 'formula', 'select', 'autodetect' );

/**
 * Valid filter types (maps to wpdatatables_columns.filter_type).
 *
 * @var array
 */
$GLOBALS['wdtmcp_valid_filter_types'] = array( 'none', 'null_str', 'text', 'number', 'number-range', 'date-range', 'datetime-range', 'time-range', 'select', 'multiselect', 'checkbox' );

/**
 * Execute callback for wpdatatables/update-table-settings.
 *
 * @param array $input
 * @return array|WP_Error
 */
function wdtmcp_execute_update_table_settings( $input ) {
    global $wpdb;

    $table_id = isset( $input['table_id'] ) ? (int) $input['table_id'] : 0;
    if ( $table_id <= 0 ) {
        return new \WP_Error( 'wdtmcp_invalid_input', __( 'table_id is required and must be a positive integer.', 'wpdatatables' ) );
    }

    if ( ! class_exists( 'WDTConfigController' ) ) {
        return new \WP_Error( 'wdtmcp_missing_class', __( 'WDTConfigController is not available.', 'wpdatatables' ) );
    }

    try {
        $table_data = \WDTConfigController::loadTableFromDB( $table_id, false );
    } catch ( \Exception $e ) {
        return new \WP_Error( 'wdtmcp_load_error', sprintf( __( 'Error loading table %d: %s', 'wpdatatables' ), $table_id, $e->getMessage() ) );
    }

    if ( empty( $table_data ) ) {
        return new \WP_Error( 'wdtmcp_not_found', sprintf( __( 'Table with ID %d was not found.', 'wpdatatables' ), $table_id ) );
    }

    $updated = array();

    // Table-level updates.
    $table_updates = array();
    if ( isset( $input['title'] ) ) {
        $table_updates['title'] = sanitize_text_field( $input['title'] );
        $updated[] = 'title';
    }
    if ( isset( $input['sorting'] ) ) {
        $table_updates['sorting'] = $input['sorting'] ? 1 : 0;
        $updated[] = 'sorting';
    }
    if ( isset( $input['filtering'] ) ) {
        $table_updates['filtering'] = $input['filtering'] ? 1 : 0;
        $updated[] = 'filtering';
    }
    if ( isset( $input['display_length'] ) ) {
        $table_updates['display_length'] = max( 1, min( 1000, (int) $input['display_length'] ) );
        $updated[] = 'display_length';
    }
    if ( isset( $input['responsive'] ) ) {
        $table_updates['responsive'] = $input['responsive'] ? 1 : 0;
        $updated[] = 'responsive';
    }
    if ( isset( $input['server_side'] ) ) {
        $table_updates['server_side'] = $input['server_side'] ? 1 : 0;
        $updated[] = 'server_side';
    }

    if ( ! empty( $table_updates ) ) {
        $result = $wpdb->update(
            $wpdb->prefix . 'wpdatatables',
            $table_updates,
            array( 'id' => $table_id ),
            null,
            array( '%d' )
        );
        if ( false === $result ) {
            return new \WP_Error(
                'wdtmcp_db_error',
                $wpdb->last_error ?: __( 'Failed to update table settings.', 'wpdatatables' )
            );
        }
    }

    // Advanced settings (stored in JSON).
    $adv_updates = array();
    if ( isset( $input['pagination'] ) ) {
        $adv_updates['pagination'] = $input['pagination'] ? 1 : 0;
        $updated[] = 'pagination';
    }
    if ( isset( $input['global_search'] ) ) {
        $adv_updates['global_search'] = $input['global_search'] ? 1 : 0;
        $updated[] = 'global_search';
    }

    if ( ! empty( $adv_updates ) ) {
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT advanced_settings FROM {$wpdb->prefix}wpdatatables WHERE id = %d", $table_id ), ARRAY_A );
        if ( $row ) {
            $adv = ! empty( $row['advanced_settings'] ) ? json_decode( $row['advanced_settings'], true ) : array();
            if ( ! is_array( $adv ) ) {
                $adv = array();
            }
            foreach ( $adv_updates as $k => $v ) {
                $adv[ $k ] = $v;
            }
            $result = $wpdb->update(
                $wpdb->prefix . 'wpdatatables',
                array( 'advanced_settings' => wp_json_encode( $adv ) ),
                array( 'id' => $table_id ),
                array( '%s' ),
                array( '%d' )
            );
            if ( false === $result ) {
                return new \WP_Error(
                    'wdtmcp_db_error',
                    $wpdb->last_error ?: __( 'Failed to update advanced table settings.', 'wpdatatables' )
                );
            }
        }
    }

    // Column-level updates.
    $columns_input = isset( $input['columns'] ) && is_array( $input['columns'] ) ? $input['columns'] : array();
    $valid_col_types = $GLOBALS['wdtmcp_valid_column_types'];
    $valid_filter_types = $GLOBALS['wdtmcp_valid_filter_types'];

    foreach ( $columns_input as $col ) {
        $orig_header = isset( $col['orig_header'] ) ? trim( (string) $col['orig_header'] ) : '';
        if ( '' === $orig_header ) {
            continue;
        }

        $col_updates = array();

        if ( isset( $col['type'] ) ) {
            $t = sanitize_text_field( $col['type'] );
            if ( in_array( $t, $valid_col_types, true ) ) {
                $col_updates['column_type'] = $t;
                $updated[] = "column:{$orig_header}:type";
            }
        }
        if ( isset( $col['filter_type'] ) ) {
            $ft = sanitize_text_field( $col['filter_type'] );
            if ( in_array( $ft, $valid_filter_types, true ) ) {
                $col_updates['filter_type'] = $ft;
                $updated[] = "column:{$orig_header}:filter_type";
            }
        }
        if ( isset( $col['display_header'] ) ) {
            $col_updates['display_header'] = sanitize_text_field( $col['display_header'] );
            $updated[] = "column:{$orig_header}:display_header";
        }
        if ( isset( $col['visible'] ) ) {
            $col_updates['visible'] = $col['visible'] ? 1 : 0;
            $updated[] = "column:{$orig_header}:visible";
        }
        if ( isset( $col['width'] ) ) {
            $col_updates['width'] = sanitize_text_field( $col['width'] );
            $updated[] = "column:{$orig_header}:width";
        }
        if ( isset( $col['sorting'] ) ) {
            $col_row = $wpdb->get_row( $wpdb->prepare(
                "SELECT id, advanced_settings FROM {$wpdb->prefix}wpdatatables_columns WHERE table_id = %d AND orig_header = %s",
                $table_id,
                $orig_header
            ), ARRAY_A );
            if ( $col_row ) {
                $adv = ! empty( $col_row['advanced_settings'] ) ? json_decode( $col_row['advanced_settings'], true ) : array();
                if ( ! is_array( $adv ) ) {
                    $adv = array();
                }
                $adv['sorting'] = $col['sorting'] ? 1 : 0;
                $col_updates['advanced_settings'] = wp_json_encode( $adv );
                $updated[] = "column:{$orig_header}:sorting";
            }
        }
        if ( isset( $col['text_before'] ) ) {
            $col_updates['text_before'] = sanitize_text_field( $col['text_before'] );
            $updated[] = "column:{$orig_header}:text_before";
        } elseif ( isset( $col['prefix'] ) ) {
            $col_updates['text_before'] = sanitize_text_field( $col['prefix'] );
            $updated[] = "column:{$orig_header}:text_before";
        }
        if ( isset( $col['text_after'] ) ) {
            $col_updates['text_after'] = sanitize_text_field( $col['text_after'] );
            $updated[] = "column:{$orig_header}:text_after";
        } elseif ( isset( $col['suffix'] ) ) {
            $col_updates['text_after'] = sanitize_text_field( $col['suffix'] );
            $updated[] = "column:{$orig_header}:text_after";
        }

        if ( ! empty( $col_updates ) ) {
            $result = $wpdb->update(
                $wpdb->prefix . 'wpdatatables_columns',
                $col_updates,
                array(
                    'table_id'   => $table_id,
                    'orig_header' => $orig_header,
                ),
                null,
                array( '%d', '%s' )
            );
            if ( false === $result ) {
                return new \WP_Error(
                    'wdtmcp_db_error',
                    $wpdb->last_error ?: __( 'Failed to update column settings.', 'wpdatatables' )
                );
            }
        }
    }

    return array(
        'table_id' => $table_id,
        'updated'  => array_values( array_unique( $updated ) ),
    );
}
