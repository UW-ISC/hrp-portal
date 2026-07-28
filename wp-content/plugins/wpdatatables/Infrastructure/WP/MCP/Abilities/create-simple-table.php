<?php
/**
 * Ability: wpdatatables/create-simple-table
 *
 * Creates a new "Simple" (spreadsheet) wpDataTable with the specified title,
 * column headers, and initial row data. Supports custom HTML in cells, cell
 * styling (colors, fonts, alignment), merged cells, and table templates
 * (pricing, pedigree, etc.).
 *
 * Available on all tiers (Free, Starter, Standard, Pro, Developer).
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_create_simple_table_ability' );

function wdtmcp_register_create_simple_table_ability() {
    wp_register_ability(
        'wpdatatables/create-simple-table',
        array(
            'label'       => __( 'Create Simple Table', 'wpdatatables' ),
            'description' => __( 'Creates a Simple wpDataTable (Handsontable editor, pure HTML, no sorting/filtering). Use when: static or manually curated content, heavy styling (pricing cards, comparison tables, pedigree). Do not use for external URLs or SQL-backed data. For images use {"attachment_id":123,"size":"medium","alt":"..."} in cells; get IDs via list-media or upload-media-from-url. For styled tables use {"data":"<div class=\'panel\'>...</div>","meta":[]} and custom_css. Supports merged_cells, table_settings. WHEN TO CALL: user wants a designed/layout-heavy HTML table, pricing comparison, pedigree chart, or any non-data-source static content. DO NOT USE for: CSV/JSON/API data (use create-table-from-source), database queries (use create-table-from-query), or large sortable datasets. REQUIRED INPUT: title (string), columns (string[] headers). OPTIONAL: rows (array of row arrays — omit or pass [] to create an empty table skeleton for later editing in wp-admin), table_settings, merged_cells, custom_css. FOR IMAGES: first call list-media or upload-media-from-url, then put attachment_id in cell objects. RETURNS: table_id and shortcode. NEXT STEPS: update-simple-table-styles to refine layout; get-table-info to verify.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'title' => array(
                        'type'        => 'string',
                        'description' => 'Table title.',
                    ),
                    'columns' => array(
                        'type'        => 'array',
                        'description' => 'Column header labels.',
                        'items'       => array( 'type' => 'string' ),
                    ),
                    'rows' => array(
                        'type'        => 'array',
                        'description' => 'Optional array of rows. Omit or pass [] for an empty table (column headers only). Each cell: plain string, {"data":"HTML","meta":[]}, or {"attachment_id":123,"size":"medium","alt":"..."} for images. Use list-media or upload-media-from-url to get attachment_id.',
                    ),
                    'table_settings' => array(
                        'type'        => 'object',
                        'description' => 'Optional table layout and display settings.',
                        'properties'  => array(
                            'template_id'         => array( 'type' => 'integer', 'description' => '0=default, 3=pricing, 6=pedigree.' ),
                            'simple_header'       => array( 'type' => 'boolean', 'description' => 'First row as header (th).' ),
                            'stripe_table'        => array( 'type' => 'boolean', 'description' => 'Alternating row colors.' ),
                            'responsive'          => array( 'type' => 'boolean', 'description' => 'Responsive layout.' ),
                            'remove_borders'      => array( 'type' => 'boolean', 'description' => 'Hide cell borders.' ),
                            'cell_padding'        => array( 'type' => 'integer', 'description' => 'Cell padding in px.' ),
                            'show_title'          => array( 'type' => 'boolean', 'description' => 'Show table title above table.' ),
                            'show_table_description' => array( 'type' => 'boolean', 'description' => 'Show description.' ),
                            'table_description'   => array( 'type' => 'string',  'description' => 'Table description text.' ),
                        ),
                    ),
                    'merged_cells' => array(
                        'type'        => 'array',
                        'description' => 'Array of merged cell definitions. Each: {row, col, rowspan, colspan}. Row/col are 0-based.',
                    ),
                    'custom_css' => array(
                        'type'        => 'string',
                        'description' => 'Custom CSS for heavily styled tables. Use #wpdtSimpleTable-{TABLE_ID} as selector (replace {TABLE_ID} with the returned table_id). Add to page via Custom HTML block or Appearance > Customize > Additional CSS. See wpdatatables.com pricing table tutorials.',
                    ),
                ),
                'required' => array( 'title', 'columns' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_id'   => array( 'type' => 'integer', 'description' => 'Created table ID.' ),
                    'shortcode'  => array( 'type' => 'string',  'description' => 'WordPress shortcode to embed the table.' ),
                    'custom_css' => array( 'type' => 'string',  'description' => 'Custom CSS to add to the page (if provided). Use #wpdtSimpleTable-{id} with the actual table_id.' ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_create_simple_table',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'For static HTML tables only. Obtain image attachment_id via list-media or upload-media-from-url before referencing images in cells. Do not use for file, API, or SQL data sources.', 'wpdatatables' ),
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => false,
                ),
            ),
        )
    );
}

/**
 * Execute callback for wpdatatables/create-simple-table.
 *
 * @param array $input
 * @return array|WP_Error
 */
function wdtmcp_execute_create_simple_table( $input ) {
    global $wpdb;

    if ( ! class_exists( 'WPDataTableRows' ) || ! class_exists( 'WDTConfigController' ) ) {
        return new \WP_Error(
            'wdtmcp_missing_class',
            __( 'wpDataTables core classes WPDataTableRows and WDTConfigController are required.', 'wpdatatables' )
        );
    }

    $title   = isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : '';
    $columns = isset( $input['columns'] ) && is_array( $input['columns'] ) ? $input['columns'] : array();
    if ( isset( $input['rows'] ) && ! is_array( $input['rows'] ) ) {
        return new \WP_Error(
            'wdtmcp_invalid_rows',
            __( 'rows must be an array.', 'wpdatatables' )
        );
    }
    $rows = isset( $input['rows'] ) ? $input['rows'] : array();
    $table_settings = isset( $input['table_settings'] ) && is_array( $input['table_settings'] ) ? $input['table_settings'] : array();
    $merged_cells   = isset( $input['merged_cells'] ) && is_array( $input['merged_cells'] ) ? $input['merged_cells'] : array();

    if ( '' === $title ) {
        return new \WP_Error( 'wdtmcp_missing_param', __( 'title is required.', 'wpdatatables' ) );
    }

    $col_count = max( 1, count( $columns ) );
    $row_count = count( $rows );

    // Normalize column headers.
    $col_headers = array();
    for ( $i = 0; $i < $col_count; $i++ ) {
        $col_headers[] = isset( $columns[ $i ] ) ? sanitize_text_field( (string) $columns[ $i ] ) : ( 'Col' . ( $i + 1 ) );
    }

    $col_widths = array_fill( 0, $col_count, 100 );

    // Normalize merged cells to wpDataTables format.
    $merged_cells_normalized = array();
    foreach ( $merged_cells as $m ) {
        if ( ! isset( $m['row'], $m['col'], $m['rowspan'], $m['colspan'] ) ) {
            continue;
        }

        $row     = (int) $m['row'];
        $col     = (int) $m['col'];
        $rowspan = max( 1, (int) $m['rowspan'] );
        $colspan = max( 1, (int) $m['colspan'] );

        if ( $row < 0 || $col < 0 || $row >= $row_count || $col >= $col_count ) {
            continue;
        }

        if ( $row + $rowspan > $row_count ) {
            $rowspan = $row_count - $row;
        }
        if ( $col + $colspan > $col_count ) {
            $colspan = $col_count - $col;
        }

        if ( $rowspan < 1 || $colspan < 1 ) {
            continue;
        }

        $merged_cells_normalized[] = (object) array(
            'row'     => $row,
            'col'     => $col,
            'rowspan' => $rowspan,
            'colspan' => $colspan,
            'removed' => false,
        );
    }

    // Build tableData structure.
    $content = new \stdClass();
    $content->rowNumber    = $row_count;
    $content->colNumber   = $col_count;
    $content->colHeaders  = $col_headers;
    $content->colWidths   = $col_widths;
    $content->mergedCells = $merged_cells_normalized;
    $content->reloadCounter = 0;

    $table_data = new \stdClass();
    $table_data->title             = $title;
    $table_data->table_description = isset( $table_settings['table_description'] ) ? sanitize_textarea_field( $table_settings['table_description'] ) : '';
    $table_data->table_type        = 'simple';
    $table_data->content           = $content;

    $table_data = \WDTConfigController::sanitizeTableSettingsSimpleTable( $table_data );

    $wp_data_table_rows = new \WPDataTableRows( $table_data );

    // Table-level settings for advanced_settings.
    $template_id   = isset( $table_settings['template_id'] ) ? (int) $table_settings['template_id'] : 0;
    $simple_header = isset( $table_settings['simple_header'] ) ? (bool) $table_settings['simple_header'] : false;
    $stripe_table  = isset( $table_settings['stripe_table'] ) ? (bool) $table_settings['stripe_table'] : false;
    $responsive    = isset( $table_settings['responsive'] ) ? (bool) $table_settings['responsive'] : false;
    $remove_borders = isset( $table_settings['remove_borders'] ) ? (bool) $table_settings['remove_borders'] : false;
    $cell_padding  = isset( $table_settings['cell_padding'] ) ? (int) $table_settings['cell_padding'] : 10;
    $show_title    = isset( $table_settings['show_title'] ) ? (bool) $table_settings['show_title'] : true;
    $show_desc     = isset( $table_settings['show_table_description'] ) ? (bool) $table_settings['show_table_description'] : false;

    $table_content = new \stdClass();
    $table_content->rowNumber    = $wp_data_table_rows->getRowNumber();
    $table_content->colNumber    = $wp_data_table_rows->getColNumber();
    $table_content->colWidths   = $wp_data_table_rows->getColWidths();
    $table_content->colHeaders   = $wp_data_table_rows->getColHeaders();
    $table_content->reloadCounter = $wp_data_table_rows->getReloadCounter();
    $table_content->mergedCells = $wp_data_table_rows->getMergeCells();

    $advanced = array(
        'simpleResponsive'         => $responsive ? 1 : 0,
        'simpleHeader'             => $simple_header ? 1 : 0,
        'stripeTable'               => $stripe_table ? 1 : 0,
        'cellPadding'               => $cell_padding,
        'removeBorders'             => $remove_borders ? 1 : 0,
        'borderCollapse'            => 'collapse',
        'borderSpacing'             => 0,
        'verticalScroll'            => 0,
        'verticalScrollHeight'      => 600,
        'show_table_description'   => $show_desc,
        'show_title'               => $show_title ? 1 : 0,
        'table_description'         => sanitize_textarea_field( $wp_data_table_rows->getTableDescription() ),
        'fixed_header'              => 0,
        'fixed_header_offset'       => 0,
        'fixed_columns'             => 0,
        'fixed_left_columns_number' => 0,
        'fixed_right_columns_number' => 0,
        'simple_template_id'       => $template_id,
        'customRowDisplay'          => '',
        'index_column'              => 0,
    );

    foreach ( $rows as $row_index => $row ) {
        if ( ! is_array( $row ) ) {
            return new \WP_Error(
                'wdtmcp_invalid_row',
                sprintf(
                    /* translators: %d: row index */
                    __( 'rows[%d] must be an array of cells.', 'wpdatatables' ),
                    (int) $row_index
                )
            );
        }

        foreach ( $row as $cell_index => $cell ) {
            if ( ! wdtmcp_is_valid_simple_cell_input( $cell ) ) {
                return new \WP_Error(
                    'wdtmcp_invalid_cell',
                    sprintf(
                        /* translators: 1: row index, 2: cell index */
                        __( 'rows[%1$d][%2$d] must be a string, number, boolean, null, or cell object.', 'wpdatatables' ),
                        (int) $row_index,
                        (int) $cell_index
                    )
                );
            }
        }
    }

    $inserted = $wpdb->insert(
        $wpdb->prefix . 'wpdatatables',
        array(
            'title'             => sanitize_text_field( $wp_data_table_rows->getTableName() ),
            'table_type'        => $wp_data_table_rows->getTableType(),
            'connection'        => '',
            'content'           => wp_json_encode( $table_content ),
            'server_side'       => 0,
            'mysql_table_name'  => '',
            'tabletools_config' => serialize( array(
                'print' => 1,
                'copy'  => 1,
                'excel' => 1,
                'csv'   => 1,
                'pdf'   => 0,
            ) ),
            'advanced_settings' => wp_json_encode( $advanced ),
        )
    );

    if ( false === $inserted ) {
        return new \WP_Error(
            'wdtmcp_db_error',
            __( 'Failed to create table in database.', 'wpdatatables' )
        );
    }

    $table_id = (int) $wpdb->insert_id;

    $custom_css = '';
    if ( array_key_exists( 'custom_css', $input ) ) {
        $validated = wdtmcp_validate_custom_css_input( $input['custom_css'] );
        if ( is_wp_error( $validated ) ) {
            return $validated;
        }
        $custom_css = $validated;
    }
    $css_substituted = '';
    if ( '' !== $custom_css ) {
        $css_substituted = wdtmcp_prepare_table_custom_css_for_storage( $custom_css, $table_id );
        wdtmcp_set_table_custom_css( $table_id, $css_substituted );
    }

    // Save row data with optional styling and HTML.
    // Set explicit row height so Handsontable editor doesn't auto-size to huge content
    // (rich HTML in cells would otherwise make the first row fill the viewport).
    $default_row_height = 100;
    foreach ( $rows as $row_index => $row ) {
        $row_data = new \stdClass();
        $row_data->cells = array();
        $row_data->height = $default_row_height;

        for ( $j = 0; $j < $col_count; $j++ ) {
            $cell = wdtmcp_build_simple_cell( isset( $row[ $j ] ) ? $row[ $j ] : '', $j );
            $row_data->cells[ $j ] = $cell;
        }

        \WDTConfigController::saveRowData( $row_data, $table_id );
    }

    if ( 0 === $row_count ) {
        $wp_data_table_rows->saveTableWithEmptyData( $table_id );
    }

    $out = array(
        'table_id'  => $table_id,
        'shortcode' => '[wpdatatable id=' . $table_id . ']',
    );
    if ( '' !== $css_substituted ) {
        $out['custom_css'] = $css_substituted;
    }
    return $out;
}

/**
 * Whether a cell value is acceptable for {@see wdtmcp_build_simple_cell()}.
 *
 * @param mixed $cell_input
 * @return bool
 */
function wdtmcp_is_valid_simple_cell_input( $cell_input ) {
    return null === $cell_input || is_scalar( $cell_input ) || is_array( $cell_input ) || is_object( $cell_input );
}

/**
 * Build a simple table cell from input (string or object with data/type/meta or attachment_id).
 *
 * @param mixed $cell_input String, or object with data/type/meta, or attachment_id/size/alt.
 * @param int   $col_index  Column index (unused, for future use).
 * @return stdClass
 */
function wdtmcp_build_simple_cell( $cell_input, $col_index = 0 ) {
    $cell = new \stdClass();
    $cell->hidden = false;
    $cell->type   = 'text';

    $is_array = is_array( $cell_input );
    $is_obj   = is_object( $cell_input );
    $aid      = $is_array ? ( $cell_input['attachment_id'] ?? null ) : ( $is_obj ? ( $cell_input->attachment_id ?? null ) : null );

    if ( null !== $aid && (int) $aid > 0 ) {
        $img_html = wdtmcp_attachment_to_img( (int) $aid, $cell_input );
        $cell->data = $img_html ? wdtmcp_sanitize_cell_html( $img_html ) : '';
        $meta = $is_array ? ( $cell_input['meta'] ?? array() ) : ( $cell_input->meta ?? array() );
        $cell->meta = is_array( $meta ) ? wdtmcp_sanitize_cell_meta( $meta ) : array();
        return $cell;
    }

    $is_cell_obj = $is_obj || ( $is_array && isset( $cell_input['data'] ) );
    if ( $is_cell_obj ) {
        $data = $is_obj ? ( $cell_input->data ?? '' ) : ( $cell_input['data'] ?? '' );
        $cell->data = wdtmcp_sanitize_cell_html( (string) $data );
        $type = $is_obj ? ( $cell_input->type ?? 'text' ) : ( $cell_input['type'] ?? 'text' );
        if ( in_array( $type, array( 'text', 'link' ), true ) ) {
            $cell->type = $type;
        }
        $meta = $is_obj ? ( $cell_input->meta ?? array() ) : ( $cell_input['meta'] ?? array() );
        $cell->meta = is_array( $meta ) ? wdtmcp_sanitize_cell_meta( $meta ) : array();
    } else {
        $cell->data = wdtmcp_sanitize_cell_html( (string) $cell_input );
        $cell->meta = array();
    }

    return $cell;
}

/**
 * Resolve attachment ID to img HTML for table cells.
 *
 * @param int        $attachment_id WordPress attachment ID.
 * @param array|obj $opts          Optional size (thumbnail|medium|large|full), alt.
 * @return string Empty if attachment invalid.
 */
function wdtmcp_attachment_to_img( $attachment_id, $opts = array() ) {
    $post = get_post( $attachment_id );
    if ( ! $post || 'attachment' !== $post->post_type || ! wp_attachment_is_image( $attachment_id ) ) {
        return '';
    }
    $url = wp_get_attachment_image_url( $attachment_id, 'medium' );
    if ( ! $url ) {
        return '';
    }
    $is_arr = is_array( $opts );
    $size   = $is_arr ? ( $opts['size'] ?? 'medium' ) : ( $opts->size ?? 'medium' );
    $alt    = $is_arr ? ( $opts['alt'] ?? '' ) : ( $opts->alt ?? '' );
    if ( '' === $alt ) {
        $alt = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
    }
    $sizes = array( 'thumbnail', 'medium', 'medium_large', 'large', 'full' );
    if ( in_array( $size, $sizes, true ) ) {
        $url = wp_get_attachment_image_url( $attachment_id, $size ) ?: $url;
    }
    $alt_attr = ' alt="' . esc_attr( (string) $alt ) . '"';
    return '<img src="' . esc_url( $url ) . '"' . $alt_attr . '>';
}

/**
 * Sanitize cell content (allows safe HTML for pricing tables, links, etc.).
 *
 * @param string $html
 * @return string
 */
function wdtmcp_sanitize_cell_html( $html ) {
    if ( current_user_can( 'unfiltered_html' ) ) {
        return wp_unslash( $html );
    }
    return wp_kses_post( $html );
}

/**
 * Sanitize cell meta class names (allow only known wpdt-* prefixes).
 *
 * @param array $meta
 * @return array
 */
function wdtmcp_sanitize_cell_meta( array $meta ) {
    $allowed_prefixes = array( 'wpdt-tc-', 'wpdt-bc-', 'wpdt-ff-', 'wpdt-fs-', 'wpdt-sc-', 'wpdt-align-', 'wpdt-valign-', 'wpdt-bold', 'wpdt-italic', 'wpdt-underline', 'wpdt-empty-cell', 'wpdt-merged-cell', 'wpdt-wrap-text', 'wpdt-overflow-text', 'wpdt-clip-text' );
    $out = array();
    foreach ( $meta as $m ) {
        $m = sanitize_html_class( (string) $m );
        if ( '' === $m ) {
            continue;
        }
        foreach ( $allowed_prefixes as $prefix ) {
            if ( strpos( $m, $prefix ) === 0 || $m === $prefix ) {
                $out[] = $m;
                break;
            }
        }
    }
    return $out;
}
