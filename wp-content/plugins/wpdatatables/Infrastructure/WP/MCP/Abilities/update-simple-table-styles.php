<?php
/**
 * Ability: wpdatatables/update-simple-table-styles
 *
 * Updates styling and layout settings of an existing Simple wpDataTable.
 * Supports table-level settings (template, responsive, striped, borders) and
 * cell-level styling (colors, fonts, alignment) for specific cells.
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_update_simple_table_styles_ability' );

function wdtmcp_register_update_simple_table_styles_ability() {
    wp_register_ability(
        'wpdatatables/update-simple-table-styles',
        array(
            'label'       => __( 'Update Simple Table Styles', 'wpdatatables' ),
            'description' => __( 'Updates styling and layout of an existing Simple wpDataTable. Change table-level settings (template, responsive, striped, borders, padding), update cells with HTML and meta classes, or set custom_css for heavily styled tables. Use #wpdtSimpleTable-{table_id} in custom_css. See wpdatatables.com pricing table tutorials. Use get-table-info first to inspect the table. WHEN TO CALL: after create-simple-table when you need to adjust design, change template (pricing/pedigree), update cell HTML, add images, or apply custom_css. ONLY FOR Simple tables (table_type=simple) — use update-table-settings for SQL/source tables. REQUIRED INPUT: table_id (integer). OPTIONAL: table_settings, cell_updates (row_index, col_index, data/attachment_id/meta), merged_cells, custom_css. TYPICAL WORKFLOW: get-table-info → update-simple-table-styles. FOR IMAGES in cells: list-media or upload-media-from-url first, then pass attachment_id in cell_updates.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_id' => array(
                        'type'        => 'integer',
                        'description' => 'The Simple wpDataTable ID to update.',
                    ),
                    'table_settings' => array(
                        'type'        => 'object',
                        'description' => 'Optional table-level settings to change.',
                        'properties'  => array(
                            'template_id'         => array( 'type' => 'integer' ),
                            'simple_header'       => array( 'type' => 'boolean' ),
                            'stripe_table'        => array( 'type' => 'boolean' ),
                            'responsive'          => array( 'type' => 'boolean' ),
                            'remove_borders'      => array( 'type' => 'boolean' ),
                            'cell_padding'        => array( 'type' => 'integer' ),
                            'show_title'          => array( 'type' => 'boolean' ),
                            'show_table_description' => array( 'type' => 'boolean' ),
                            'table_description'   => array( 'type' => 'string' ),
                        ),
                    ),
                    'cell_updates' => array(
                        'type'        => 'array',
                        'description' => 'Each: {row_index, col_index, data?, attachment_id?, meta?}. Use data for HTML, or attachment_id (with optional size, alt) to insert a media library image. For styled tables, data must include HTML with classes your custom_css targets.',
                    ),
                    'merged_cells' => array(
                        'type'        => 'array',
                        'description' => 'Optional. Replace merged cells. Each: {row, col, rowspan, colspan}. Pass empty array to clear all merges.',
                    ),
                    'custom_css' => array(
                        'type'        => 'string',
                        'description' => 'Custom CSS for heavily styled tables. Use #wpdtSimpleTable-{table_id} in selectors. Pass empty string to clear stored CSS. Stored CSS is auto-injected when table is on page.',
                    ),
                    'row_heights' => array(
                        'type'        => 'array',
                        'description' => 'Fix Handsontable editor "only first row visible": set explicit row heights (px). E.g. [100, 100, 100] for 3 rows. Prevents auto-sizing to huge content.',
                        'items'       => array( 'type' => 'integer' ),
                    ),
                ),
                'required' => array( 'table_id' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_id'   => array( 'type' => 'integer', 'description' => 'Updated table ID.' ),
                    'updated'    => array( 'type' => 'array', 'description' => 'List of what was changed.', 'items' => array( 'type' => 'string' ) ),
                    'custom_css' => array( 'type' => 'string',  'description' => 'Custom CSS to add to page (if provided).' ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_update_simple_table_styles',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Only for Simple tables (table_type=simple). Call get-table-info first. Use table_id plus optional table_settings, cell_updates, merged_cells, or custom_css.', 'wpdatatables' ),
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => false,
                ),
            ),
        )
    );
}

/**
 * Execute callback for wpdatatables/update-simple-table-styles.
 *
 * @param array $input
 * @return array|WP_Error
 */
function wdtmcp_execute_update_simple_table_styles( $input ) {
    global $wpdb;

    if ( ! class_exists( 'WPDataTableRows' ) || ! class_exists( 'WDTConfigController' ) ) {
        return new \WP_Error(
            'wdtmcp_missing_class',
            __( 'wpDataTables core classes are required.', 'wpdatatables' )
        );
    }

    $table_id      = isset( $input['table_id'] ) ? (int) $input['table_id'] : 0;
    $table_settings = isset( $input['table_settings'] ) && is_array( $input['table_settings'] ) ? $input['table_settings'] : array();
    $cell_updates   = isset( $input['cell_updates'] ) && is_array( $input['cell_updates'] ) ? $input['cell_updates'] : array();
    $merged_cells   = isset( $input['merged_cells'] ) ? $input['merged_cells'] : null;
    $custom_css = null;
    if ( array_key_exists( 'custom_css', $input ) ) {
        $validated = wdtmcp_validate_custom_css_input( $input['custom_css'] );
        if ( is_wp_error( $validated ) ) {
            return $validated;
        }
        $custom_css = $validated;
    }
    $row_heights    = isset( $input['row_heights'] ) && is_array( $input['row_heights'] ) ? $input['row_heights'] : null;

    if ( $table_id <= 0 ) {
        return new \WP_Error( 'wdtmcp_missing_param', __( 'table_id is required.', 'wpdatatables' ) );
    }

    $updated = array();

    // Load existing table.
    $table_row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, title, table_type, content, advanced_settings FROM {$wpdb->prefix}wpdatatables WHERE id = %d",
            $table_id
        ),
        ARRAY_A
    );

    if ( ! $table_row || 'simple' !== $table_row['table_type'] ) {
        return new \WP_Error(
            'wdtmcp_invalid_table',
            __( 'Table not found or is not a Simple table.', 'wpdatatables' )
        );
    }

    // ── Update table-level settings (advanced_settings) ─────────────────
    $advanced = json_decode( $table_row['advanced_settings'], true );
    if ( ! is_array( $advanced ) ) {
        $advanced = array();
    }

    $settings_map = array(
        'template_id'           => array( 'key' => 'simple_template_id', 'int' => true ),
        'simple_header'         => array( 'key' => 'simpleHeader', 'bool' => true ),
        'stripe_table'          => array( 'key' => 'stripeTable', 'bool' => true ),
        'responsive'            => array( 'key' => 'simpleResponsive', 'bool' => true ),
        'remove_borders'        => array( 'key' => 'removeBorders', 'bool' => true ),
        'cell_padding'          => array( 'key' => 'cellPadding', 'int' => true ),
        'show_title'            => array( 'key' => 'show_title', 'bool' => true ),
        'show_table_description' => array( 'key' => 'show_table_description', 'bool' => true ),
        'table_description'     => array( 'key' => 'table_description', 'str' => true ),
    );

    foreach ( $table_settings as $k => $v ) {
        if ( ! isset( $settings_map[ $k ] ) ) {
            continue;
        }
        $m = $settings_map[ $k ];
        $adv_key = $m['key'];
        if ( ! empty( $m['bool'] ) ) {
            $advanced[ $adv_key ] = (bool) $v ? 1 : 0;
        } elseif ( ! empty( $m['int'] ) ) {
            $advanced[ $adv_key ] = (int) $v;
        } elseif ( ! empty( $m['str'] ) ) {
            $advanced[ $adv_key ] = sanitize_textarea_field( (string) $v );
        }
        $updated[] = "table_settings.{$k}";
    }

    if ( ! empty( $table_settings ) ) {
        $result = $wpdb->update(
            $wpdb->prefix . 'wpdatatables',
            array( 'advanced_settings' => wp_json_encode( $advanced ) ),
            array( 'id' => $table_id ),
            array( '%s' ),
            array( '%d' )
        );
        if ( false === $result ) {
            return new \WP_Error(
                'wdtmcp_db_error',
                $wpdb->last_error ?: __( 'Failed to update table settings.', 'wpdatatables' )
            );
        }
    }

    // ── Update content (merged cells) ──────────────────────────────────
    if ( null !== $merged_cells && is_array( $merged_cells ) ) {
        $content = json_decode( $table_row['content'], true );
        if ( is_array( $content ) ) {
            $merged_normalized = array();
            foreach ( $merged_cells as $m ) {
                if ( isset( $m['row'], $m['col'], $m['rowspan'], $m['colspan'] ) ) {
                    $merged_normalized[] = array(
                        'row'     => (int) $m['row'],
                        'col'     => (int) $m['col'],
                        'rowspan' => max( 1, (int) $m['rowspan'] ),
                        'colspan' => max( 1, (int) $m['colspan'] ),
                        'removed' => false,
                    );
                }
            }
            $content['mergedCells'] = $merged_normalized;
            $result = $wpdb->update(
                $wpdb->prefix . 'wpdatatables',
                array( 'content' => wp_json_encode( $content ) ),
                array( 'id' => $table_id ),
                array( '%s' ),
                array( '%d' )
            );
            if ( false === $result ) {
                return new \WP_Error(
                    'wdtmcp_db_error',
                    $wpdb->last_error ?: __( 'Failed to update merged cells.', 'wpdatatables' )
                );
            }
            $updated[] = 'merged_cells';
        }
    }

    // ── Update cell data and meta (rows) ─────────────────────────────────
    if ( ! empty( $cell_updates ) ) {
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, data FROM {$wpdb->prefix}wpdatatables_rows WHERE table_id = %d ORDER BY id",
                $table_id
            ),
            ARRAY_A
        );

        $cell_updates_by_row = array();
        foreach ( $cell_updates as $cu ) {
            $ri = isset( $cu['row_index'] ) ? (int) $cu['row_index'] : -1;
            if ( $ri < 0 || ! isset( $rows[ $ri ] ) ) {
                continue;
            }
            $cell_updates_by_row[ $ri ][] = $cu;
        }

        foreach ( $cell_updates_by_row as $ri => $row_cell_updates ) {
            $row_db   = $rows[ $ri ];
            $row_data = json_decode( $row_db['data'], true );
            if ( ! is_array( $row_data ) || ! isset( $row_data['cells'] ) ) {
                continue;
            }

            $row_changed = false;
            foreach ( $row_cell_updates as $cu ) {
                $ci = isset( $cu['col_index'] ) ? (int) $cu['col_index'] : -1;
                if ( $ci < 0 || ! isset( $row_data['cells'][ $ci ] ) ) {
                    continue;
                }

                $cell = &$row_data['cells'][ $ci ];
                if ( isset( $cu['attachment_id'] ) && (int) $cu['attachment_id'] > 0 ) {
                    $img_html     = wdtmcp_attachment_to_img( (int) $cu['attachment_id'], $cu );
                    $cell['data'] = $img_html ? wdtmcp_sanitize_cell_html( $img_html ) : ( $cell['data'] ?? '' );
                } elseif ( isset( $cu['data'] ) ) {
                    $cell['data'] = current_user_can( 'unfiltered_html' ) ? wp_unslash( (string) $cu['data'] ) : wp_kses_post( (string) $cu['data'] );
                }
                if ( isset( $cu['meta'] ) && is_array( $cu['meta'] ) ) {
                    $cell['meta'] = wdtmcp_sanitize_cell_meta( $cu['meta'] );
                }
                unset( $cell );

                $row_changed = true;
                $updated[]   = "cell.{$ri}.{$ci}";
            }

            if ( $row_changed ) {
                $encoded = wp_json_encode( $row_data );
                $result  = $wpdb->update(
                    $wpdb->prefix . 'wpdatatables_rows',
                    array( 'data' => $encoded ),
                    array( 'id' => (int) $row_db['id'] ),
                    array( '%s' ),
                    array( '%d' )
                );
                if ( false === $result ) {
                    return new \WP_Error(
                        'wdtmcp_db_error',
                        $wpdb->last_error ?: __( 'Failed to update table row.', 'wpdatatables' )
                    );
                }
                $rows[ $ri ]['data'] = $encoded;
            }
        }
    }

    // ── Update row heights (fixes Handsontable editor "only first row visible") ──
    if ( null !== $row_heights && ! empty( $row_heights ) ) {
        $rows_db = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, data FROM {$wpdb->prefix}wpdatatables_rows WHERE table_id = %d ORDER BY id",
                $table_id
            ),
            ARRAY_A
        );
        foreach ( $row_heights as $ri => $h ) {
            if ( $ri < 0 || ! isset( $rows_db[ $ri ] ) ) {
                continue;
            }
            $h = max( 20, min( 500, (int) $h ) );
            $row_db = $rows_db[ $ri ];
            $row_data = json_decode( $row_db['data'], true );
            if ( is_array( $row_data ) ) {
                $row_data['height'] = $h;
                $result = $wpdb->update(
                    $wpdb->prefix . 'wpdatatables_rows',
                    array( 'data' => wp_json_encode( $row_data ) ),
                    array( 'id' => (int) $row_db['id'] ),
                    array( '%s' ),
                    array( '%d' )
                );
                if ( false === $result ) {
                    return new \WP_Error(
                        'wdtmcp_db_error',
                        $wpdb->last_error ?: __( 'Failed to update row height.', 'wpdatatables' )
                    );
                }
                $updated[] = "row_height.{$ri}";
            }
        }
    }

    $css_substituted = '';
    if ( null !== $custom_css ) {
        $css_substituted = wdtmcp_prepare_table_custom_css_for_storage( $custom_css, $table_id );
        wdtmcp_set_table_custom_css( $table_id, $css_substituted );
        $updated[] = 'custom_css';
    }

    $out = array(
        'table_id' => $table_id,
        'updated'  => array_values( array_unique( $updated ) ),
    );
    if ( '' !== $css_substituted ) {
        $out['custom_css'] = $css_substituted;
    }
    return $out;
}
