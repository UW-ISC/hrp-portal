<?php
/**
 * Ability: wpdatatables/get-chart-info
 *
 * Returns the full configuration of a single wpDataTables chart by ID,
 * including chart engine, type, linked table, selected columns, series,
 * display options, and layout properties.
 *
 * Uses WPDataChart::getChartDataById() to stay in sync with the parent plugin.
 *
 * @package wpDataTables_MCP_Server
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_get_chart_info_ability' );

function wdtmcp_register_get_chart_info_ability() {
    wp_register_ability(
        'wpdatatables/get-chart-info',
        array(
            'label'       => __( 'Get wpDataChart Info', 'wpdatatables' ),
            'description' => __( 'Returns the full configuration of a single wpDataTables chart by ID, including its rendering engine, chart type, linked source table, selected columns, series configuration, axis labels, dimensions, and display flags. Use this after list-charts to understand a chart\'s structure. WHEN TO CALL: when you need details about an existing chart — which table it uses, which columns are plotted, engine (google, chartjs, highcharts, apexcharts), type (line, pie, column, etc.), or layout settings. REQUIRED INPUT: chart_id (integer) from list-charts. RETURNS: id, title, engine, type, wpdatatable_id, selected_columns, range_type, dimensions, axes, series, and display flags. TYPICAL WORKFLOW: list-charts → get-chart-info. To see the underlying data, also call get-table-info / get-table-data using wpdatatable_id from the response.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'chart_id' => array(
                        'type'        => 'integer',
                        'description' => 'The unique chart ID (as returned by list-charts).',
                    ),
                ),
                'required' => array( 'chart_id' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'id'               => array( 'type' => 'integer', 'description' => 'Chart ID.' ),
                    'title'            => array( 'type' => 'string',  'description' => 'Chart title.' ),
                    'engine'           => array( 'type' => 'string',  'description' => 'Rendering engine (google, chartjs, highcharts, apexcharts).' ),
                    'type'             => array( 'type' => 'string',  'description' => 'Chart type (line, area, column, bar, pie, scatter, bubble, etc.).' ),
                    'wpdatatable_id'   => array( 'type' => 'integer', 'description' => 'ID of the source wpDataTable this chart visualises.' ),
                    'selected_columns' => array(
                        'type'        => 'array',
                        'description' => 'Column original headers selected for this chart.',
                        'items'       => array( 'type' => 'string' ),
                    ),
                    'range_type'       => array( 'type' => 'string',  'description' => 'Row range type: all, pick_rows, range.' ),
                    'row_range'        => array(
                        'type'        => 'array',
                        'description' => 'Row range boundaries (varies by range_type).',
                        'items'       => array( 'type' => 'integer' ),
                    ),
                    'follow_filtering' => array( 'type' => 'boolean', 'description' => 'Whether the chart reacts to table front-end filters.' ),
                    'group_chart'      => array( 'type' => 'boolean', 'description' => 'Whether data grouping is enabled.' ),
                    'series_type'      => array( 'type' => 'string',  'description' => 'Series type (empty for default, or combo type name).' ),
                    'show_title'       => array( 'type' => 'boolean', 'description' => 'Whether the chart title is displayed.' ),
                    'show_grid'        => array( 'type' => 'boolean', 'description' => 'Whether grid lines are shown.' ),
                    'tooltip_enabled'  => array( 'type' => 'boolean', 'description' => 'Whether tooltips are enabled.' ),
                    'responsive_width' => array( 'type' => 'boolean', 'description' => 'Whether the chart uses responsive width.' ),
                    'width'            => array( 'type' => 'integer', 'description' => 'Chart width in pixels.' ),
                    'height'           => array( 'type' => 'integer', 'description' => 'Chart height in pixels.' ),
                    'background_color' => array( 'type' => 'string',  'description' => 'Chart background colour (hex).' ),
                    'axes' => array(
                        'type'       => 'object',
                        'description' => 'Axis label configuration.',
                        'properties' => array(
                            'major_label' => array( 'type' => 'string', 'description' => 'Major (X / horizontal) axis label.' ),
                            'minor_label' => array( 'type' => 'string', 'description' => 'Minor (Y / vertical) axis label.' ),
                        ),
                    ),
                    'series' => array(
                        'type'        => 'array',
                        'description' => 'Per-series customisation (colours, labels, types).',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'color' => array( 'type' => 'string', 'description' => 'Series colour.' ),
                                'label' => array( 'type' => 'string', 'description' => 'Series label override.' ),
                                'type'  => array( 'type' => 'string', 'description' => 'Series chart sub-type (for combo charts).' ),
                            ),
                        ),
                    ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_get_chart_info',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Requires chart_id from list-charts. Use wpdatatable_id from the response to inspect the linked source table.', 'wpdatatables' ),
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
            ),
        )
    );
}

/**
 * Execute callback for wpdatatables/get-chart-info.
 *
 * @param array $input { chart_id: int }
 * @return array|WP_Error
 */
function wdtmcp_execute_get_chart_info( $input ) {
    $chart_id = isset( $input['chart_id'] ) ? (int) $input['chart_id'] : 0;

    if ( $chart_id <= 0 ) {
        return new \WP_Error(
            'wdtmcp_invalid_input',
            __( 'A valid chart_id (positive integer) is required.', 'wpdatatables' )
        );
    }

    if ( ! class_exists( 'WPDataChart' ) ) {
        return new \WP_Error(
            'wdtmcp_missing_class',
            __( 'wpDataTables core class WPDataChart is not available.', 'wpdatatables' )
        );
    }

    $chart_data = \WPDataChart::getChartDataById( $chart_id );

    if ( empty( $chart_data ) ) {
        return new \WP_Error(
            'wdtmcp_not_found',
            sprintf( __( 'Chart with ID %d was not found.', 'wpdatatables' ), $chart_id )
        );
    }

    $render = array();
    if ( ! empty( $chart_data->json_render_data ) ) {
        $decoded = json_decode( $chart_data->json_render_data, true );
        if ( is_array( $decoded ) ) {
            $render = $decoded;
        }
    }

    $options = isset( $render['render_data']['options'] ) ? $render['render_data']['options'] : array();

    $selected_columns = array();
    if ( ! empty( $render['selected_columns'] ) && is_array( $render['selected_columns'] ) ) {
        $selected_columns = array_values( array_map( 'strval', $render['selected_columns'] ) );
    }

    $row_range = array();
    if ( ! empty( $render['row_range'] ) && is_array( $render['row_range'] ) ) {
        $row_range = array_values( array_map( 'intval', $render['row_range'] ) );
    }

    $series = array();
    if ( ! empty( $options['series'] ) && is_array( $options['series'] ) ) {
        foreach ( $options['series'] as $s ) {
            $series[] = array(
                'color' => isset( $s['color'] ) ? (string) $s['color'] : '',
                'label' => isset( $s['label'] ) ? (string) $s['label'] : '',
                'type'  => isset( $s['type'] )  ? (string) $s['type']  : '',
            );
        }
    }

    $width  = isset( $options['width'] )  ? (int) $options['width']  : 400;
    $height = isset( $options['height'] ) ? (int) $options['height'] : 400;

    $responsive_width = false;
    if ( isset( $options['responsive_width'] ) ) {
        $responsive_width = (bool) $options['responsive_width'];
    }

    $background_color = '#FFFFFF';
    if ( ! empty( $options['backgroundColor'] ) ) {
        $background_color = (string) $options['backgroundColor'];
    }

    $tooltip_enabled = true;
    if ( isset( $options['tooltip_enabled'] ) ) {
        $tooltip_enabled = (bool) $options['tooltip_enabled'];
    }

    $major_label = '';
    $minor_label = '';
    if ( ! empty( $options['hAxis']['title'] ) ) {
        $major_label = (string) $options['hAxis']['title'];
    }
    if ( ! empty( $options['vAxis']['title'] ) ) {
        $minor_label = (string) $options['vAxis']['title'];
    }

    return array(
        'id'               => (int) $chart_data->id,
        'title'            => (string) $chart_data->title,
        'engine'           => isset( $chart_data->engine ) ? (string) $chart_data->engine : '',
        'type'             => isset( $chart_data->type )   ? (string) $chart_data->type   : '',
        'wpdatatable_id'   => isset( $chart_data->wpdatatable_id ) ? (int) $chart_data->wpdatatable_id : 0,
        'selected_columns' => $selected_columns,
        'range_type'       => isset( $render['range_type'] )       ? (string) $render['range_type'] : 'all',
        'row_range'        => $row_range,
        'follow_filtering' => ! empty( $render['follow_filtering'] ),
        'group_chart'      => ! empty( $options['group_chart'] ),
        'series_type'      => isset( $render['series_type'] ) ? (string) $render['series_type'] : '',
        'show_title'       => ! empty( $render['show_title'] ),
        'show_grid'        => ! empty( $render['show_grid'] ),
        'tooltip_enabled'  => $tooltip_enabled,
        'responsive_width' => $responsive_width,
        'width'            => $width,
        'height'           => $height,
        'background_color' => $background_color,
        'axes'             => array(
            'major_label' => $major_label,
            'minor_label' => $minor_label,
        ),
        'series'           => $series,
    );
}
