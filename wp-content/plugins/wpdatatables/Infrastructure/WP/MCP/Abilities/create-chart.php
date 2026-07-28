<?php
/**
 * Ability: wpdatatables/create-chart
 *
 * Creates a new wpDataTables chart linked to an existing wpDataTable.
 *
 * Supports all chart engines available on the installation:
 *  - Google Charts (all tiers)
 *  - Chart.js (all tiers)
 *  - HighCharts (Standard+, requires WDT_HC_INTEGRATION)
 *  - ApexCharts (Standard+, requires WDT_AC_INTEGRATION)
 *  - HighCharts Stock (Pro+, requires WDT_HS_INTEGRATION)
 *
 * The AI should first call get-system-info to check which engines are available,
 * then list-tables / get-table-info to find the source table and its columns.
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_create_chart_ability' );

function wdtmcp_register_create_chart_ability() {
    wp_register_ability(
        'wpdatatables/create-chart',
        array(
            'label'       => __( 'Create Chart', 'wpdatatables' ),
            'description' => __( 'Creates a new chart from an existing wpDataTable. Specify the source table ID, chart engine (google, chartjs, highcharts, apexcharts), chart type (line, column, pie, area, bar, scatter, etc.), which columns to include, and optional display settings (title, dimensions, colours). Returns the chart ID and shortcode. Check get-system-info first to see which chart engines are available on this installation. WHEN TO CALL: user wants a visual chart from existing table data. DO NOT CALL before a source table exists — create or find the table first. REQUIRED INPUT: title, wpdatatable_id (from list-tables), engine (must be in chart_engines from get-system-info), type, selected_columns (orig_header strings from get-table-info). OPTIONAL: width, height, show_title, show_grid, range_type, row_range, and other display flags. TYPICAL WORKFLOW: get-system-info → list-tables → get-table-info → create-chart. RETURNS: chart_id and shortcode. VERIFY with list-charts and get-chart-info.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'title' => array(
                        'type'        => 'string',
                        'description' => 'Chart title.',
                    ),
                    'wpdatatable_id' => array(
                        'type'        => 'integer',
                        'description' => 'Source wpDataTable ID.',
                    ),
                    'engine' => array(
                        'type'        => 'string',
                        'description' => 'Chart rendering engine.',
                        'enum'        => array( 'google', 'chartjs', 'highcharts', 'apexcharts' ),
                    ),
                    'type' => array(
                        'type'        => 'string',
                        'description' => 'Chart type (e.g. line, column, pie, area, bar, scatter, donut, bubble, radar, polar, gauge, histogram, candlestick, waterfall, stepped_area, stacked_bar, stacked_column, stacked_area).',
                    ),
                    'selected_columns' => array(
                        'type'        => 'array',
                        'description' => 'Column orig_headers to include in the chart. First column is typically the X-axis label.',
                        'items'       => array( 'type' => 'string' ),
                    ),
                    'width' => array(
                        'type'        => 'integer',
                        'description' => 'Chart width in pixels (default 400).',
                    ),
                    'height' => array(
                        'type'        => 'integer',
                        'description' => 'Chart height in pixels (default 400).',
                    ),
                    'responsive_width' => array(
                        'type'        => 'boolean',
                        'description' => 'Use responsive width (default true).',
                    ),
                    'follow_filtering' => array(
                        'type'        => 'boolean',
                        'description' => 'Chart reacts to table front-end filters (default false).',
                    ),
                    'group_chart' => array(
                        'type'        => 'boolean',
                        'description' => 'Group rows with the same label (default false).',
                    ),
                ),
                'required' => array( 'title', 'wpdatatable_id', 'engine', 'type', 'selected_columns' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'chart_id'  => array( 'type' => 'integer', 'description' => 'Created chart ID.' ),
                    'shortcode' => array( 'type' => 'string',  'description' => 'WordPress shortcode to embed the chart.' ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_create_chart',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Call get-system-info for available engines, then get-table-info for column orig_header values. Requires wpdatatable_id, engine, type, and selected_columns.', 'wpdatatables' ),
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => false,
                ),
            ),
        )
    );
}

/**
 * Map a generic chart type to the engine-specific type used by wpDataTables.
 *
 * @param string $engine Chart engine key.
 * @param string $type   Generic or engine-specific chart type.
 * @return string
 */
function wdtmcp_resolve_chart_type( $engine, $type ) {
    $engine = strtolower( sanitize_text_field( $engine ) );
    $type   = strtolower( sanitize_text_field( $type ) );

    if ( '' === $type ) {
        return '';
    }

    if ( strpos( $type, $engine . '_' ) === 0 ) {
        return $type;
    }

    $aliases = array(
        'google' => array(
            'line'           => 'google_line_chart',
            'area'           => 'google_area_chart',
            'stepped_area'   => 'google_stepped_area_chart',
            'column'         => 'google_column_chart',
            'histogram'      => 'google_histogram',
            'bar'            => 'google_bar_chart',
            'stacked_bar'    => 'google_stacked_bar_chart',
            'pie'            => 'google_pie_chart',
            'donut'          => 'google_donut_chart',
            'bubble'         => 'google_bubble_chart',
            'scatter'        => 'google_scatter_chart',
            'gauge'          => 'google_gauge_chart',
            'candlestick'    => 'google_candlestick_chart',
            'waterfall'      => 'google_waterfall_chart',
            'geo'            => 'google_geo_chart',
        ),
        'chartjs' => array(
            'line'    => 'chartjs_line_chart',
            'bar'     => 'chartjs_bar_chart',
            'pie'     => 'chartjs_pie_chart',
            'doughnut'=> 'chartjs_doughnut_chart',
            'donut'   => 'chartjs_doughnut_chart',
            'radar'   => 'chartjs_radar_chart',
            'polar'   => 'chartjs_polar_area_chart',
            'bubble'  => 'chartjs_bubble_chart',
            'scatter' => 'chartjs_scatter_chart',
        ),
        'highcharts' => array(
            'line'           => 'highcharts_line_chart',
            'area'           => 'highcharts_area_chart',
            'column'         => 'highcharts_column_chart',
            'bar'            => 'highcharts_basic_bar_chart',
            'stacked_bar'    => 'highcharts_stacked_bar_chart',
            'stacked_column' => 'highcharts_stacked_column_chart',
            'pie'            => 'highcharts_pie_chart',
            'donut'          => 'highcharts_donut_chart',
            'scatter'        => 'highcharts_scatter_plot',
        ),
        'apexcharts' => array(
            'line'           => 'apexcharts_basic_line_chart',
            'area'           => 'apexcharts_basic_area_chart',
            'column'         => 'apexcharts_column_chart',
            'bar'            => 'apexcharts_stacked_bar_chart',
            'stacked_bar'    => 'apexcharts_stacked_bar_chart',
            'stacked_column' => 'apexcharts_stacked_column_chart',
            'pie'            => 'apexcharts_pie_chart',
            'donut'          => 'apexcharts_donut_chart',
            'radar'          => 'apexcharts_radar_chart',
            'scatter'        => 'apexcharts_scatter_chart',
        ),
    );

    if ( isset( $aliases[ $engine ][ $type ] ) ) {
        return $aliases[ $engine ][ $type ];
    }

    if ( substr( $type, -6 ) === '_chart' ) {
        return $engine . '_' . $type;
    }

    return $engine . '_' . $type . '_chart';
}

/**
 * Execute callback for wpdatatables/create-chart.
 *
 * @param array $input
 * @return array|WP_Error
 */
function wdtmcp_execute_create_chart( $input ) {
    global $wpdb;

    if ( ! class_exists( 'WPDataChart' ) || ! class_exists( 'WPDataTable' ) ) {
        return new \WP_Error(
            'wdtmcp_missing_class',
            __( 'wpDataTables core classes WPDataChart and WPDataTable are required.', 'wpdatatables' )
        );
    }

    $title            = isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : '';
    $wpdatatable_id   = isset( $input['wpdatatable_id'] ) ? (int) $input['wpdatatable_id'] : 0;
    $engine           = isset( $input['engine'] ) ? strtolower( sanitize_text_field( $input['engine'] ) ) : '';
    $type             = isset( $input['type'] ) ? sanitize_text_field( $input['type'] ) : '';
    $selected_columns = isset( $input['selected_columns'] ) && is_array( $input['selected_columns'] )
        ? array_values( array_map( 'sanitize_text_field', $input['selected_columns'] ) )
        : array();

    $allowed_engines = array( 'google', 'chartjs', 'highcharts', 'apexcharts' );

    if ( '' === $title ) {
        return new \WP_Error( 'wdtmcp_invalid_input', __( 'A chart title is required.', 'wpdatatables' ) );
    }

    if ( $wpdatatable_id <= 0 ) {
        return new \WP_Error( 'wdtmcp_invalid_input', __( 'A valid wpdatatable_id is required.', 'wpdatatables' ) );
    }

    if ( ! in_array( $engine, $allowed_engines, true ) ) {
        return new \WP_Error(
            'wdtmcp_invalid_input',
            sprintf(
                /* translators: %s: comma-separated engine list */
                __( 'Engine must be one of: %s.', 'wpdatatables' ),
                implode( ', ', $allowed_engines )
            )
        );
    }

    $engine_license = wdtmcp_assert_chart_engine_available( $engine, 'wpdatatables/create-chart' );
    if ( is_wp_error( $engine_license ) ) {
        return $engine_license;
    }

    if ( '' === $type ) {
        return new \WP_Error( 'wdtmcp_invalid_input', __( 'A chart type is required.', 'wpdatatables' ) );
    }

    if ( count( $selected_columns ) < 2 ) {
        return new \WP_Error(
            'wdtmcp_invalid_input',
            __( 'At least two selected_columns are required (label column and value column).', 'wpdatatables' )
        );
    }

    $table_exists = (int) $wpdb->get_var(
        $wpdb->prepare(
            'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wpdatatables WHERE id = %d',
            $wpdatatable_id
        )
    );

    if ( $table_exists < 1 ) {
        return new \WP_Error(
            'wdtmcp_not_found',
            sprintf( __( 'Table with ID %d was not found.', 'wpdatatables' ), $wpdatatable_id )
        );
    }

    try {
        $table = \WPDataTable::loadWpDataTable( $wpdatatable_id, null, true );
    } catch ( \Exception $e ) {
        return new \WP_Error(
            'wdtmcp_table_load_failed',
            sprintf( __( 'Could not load source table: %s', 'wpdatatables' ), $e->getMessage() )
        );
    }

    $available_headers = array();
    foreach ( $table->getColumns() as $column ) {
        $available_headers[] = $column->getOriginalHeader();
    }

    foreach ( $selected_columns as $column_header ) {
        if ( ! in_array( $column_header, $available_headers, true ) ) {
            return new \WP_Error(
                'wdtmcp_invalid_input',
                sprintf(
                    /* translators: 1: column name, 2: table id */
                    __( 'Column "%1$s" was not found on table %2$d.', 'wpdatatables' ),
                    $column_header,
                    $wpdatatable_id
                )
            );
        }
    }

    $resolved_type = wdtmcp_resolve_chart_type( $engine, $type );

    $chart_data = array(
        'title'              => $title,
        'wpdatatable_id'     => $wpdatatable_id,
        'engine'             => $engine,
        'type'               => $resolved_type,
        'selected_columns'   => $selected_columns,
        'range_type'         => 'all_rows',
        'follow_filtering'   => isset( $input['follow_filtering'] ) ? (bool) $input['follow_filtering'] : false,
        'width'              => isset( $input['width'] ) ? (int) $input['width'] : 400,
        'height'             => isset( $input['height'] ) ? (int) $input['height'] : 400,
        'responsive_width'   => isset( $input['responsive_width'] ) ? (bool) $input['responsive_width'] : true,
        'group_chart'        => isset( $input['group_chart'] ) ? (bool) $input['group_chart'] : false,
        'show_grid'          => true,
        'show_title'         => true,
        'tooltip_enabled'    => true,
        'background_color'   => '#FFFFFF',
        'loader'             => (bool) get_option( 'wdtGlobalChartLoader' ),
    );

    try {
        $chart = \WPDataChart::build( $chart_data );
        if ( function_exists( 'wdtmcp_chart_save_persisting_table_rows' ) ) {
            wdtmcp_chart_save_persisting_table_rows( $chart );
        } else {
            $chart->save();
        }
    } catch ( \Exception $e ) {
        return new \WP_Error(
            'wdtmcp_chart_create_failed',
            sprintf( __( 'Failed to create chart: %s', 'wpdatatables' ), $e->getMessage() )
        );
    }

    $chart_id = (int) $chart->getId();
    if ( $chart_id <= 0 ) {
        return new \WP_Error( 'wdtmcp_chart_create_failed', __( 'Chart was not saved.', 'wpdatatables' ) );
    }

    return array(
        'chart_id'  => $chart_id,
        'shortcode' => $chart->getShortCode(),
    );
}
