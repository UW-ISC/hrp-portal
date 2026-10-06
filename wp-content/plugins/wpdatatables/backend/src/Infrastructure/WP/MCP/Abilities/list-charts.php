<?php
/**
 * Ability: wpdatatables/list-charts
 *
 * Lists all wpDataTables charts with summary metadata.
 * Uses WPDataChart::getAll() to stay in sync with the parent plugin.
 *
 * @package wpDataTables_MCP_Server
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_list_charts_ability' );

function wdtmcp_register_list_charts_ability() {
    wp_register_ability(
        'wpdatatables/list-charts',
        array(
            'label'       => __( 'List wpDataTables Charts', 'wpdatatables' ),
            'description' => __( 'Returns a list of all charts created in wpDataTables, including each chart\'s ID and title. Use this to discover available charts before inspecting a specific chart with get-chart-info. WHEN TO CALL: when the user asks about existing charts, needs a chart ID, or wants to inspect or modify chart configuration. No parameters are required — pass an empty object {}. RETURNS: { charts: [{ id, title }], count }. TYPICAL NEXT STEP: call get-chart-info with chart_id to see engine, type, linked table, columns, and display options. To create a new chart, use create-chart instead (after get-system-info and get-table-info).', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(),
                'required'   => array(),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'charts' => array(
                        'type'        => 'array',
                        'description' => 'Array of chart summaries.',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'id' => array(
                                    'type'        => 'integer',
                                    'description' => 'The unique chart ID.',
                                ),
                                'title' => array(
                                    'type'        => 'string',
                                    'description' => 'The chart title.',
                                ),
                                'adminLink' => array(
                                    'type'        => 'string',
                                    'description' => 'Admin URL to open this chart in the wizard — render the title as a markdown link.',
                                ),
                            ),
                        ),
                    ),
                    'count' => array(
                        'type'        => 'integer',
                        'description' => 'Total number of charts.',
                    ),
                ),
            ),

            'execute_callback' => function () {
                if ( ! class_exists( 'WPDataChart' ) ) {
                    return new \WP_Error(
                        'wdtmcp_missing_class',
                        __( 'wpDataTables core class WPDataChart is not available.', 'wpdatatables' )
                    );
                }

                $rows = \WPDataChart::getAll();

                if ( ! is_array( $rows ) ) {
                    return new \WP_Error(
                        'wdtmcp_query_failed',
                        __( 'Failed to retrieve charts from wpDataTables.', 'wpdatatables' )
                    );
                }

                if ( function_exists( 'wdtmcp_filter_allowed_chart_rows' ) ) {
                    $rows = wdtmcp_filter_allowed_chart_rows( $rows );
                }

                $charts = array_map( static function ( $row ) {
                    $chart_id = (int) $row['id'];

                    return array(
                        'id'        => $chart_id,
                        'title'     => (string) $row['title'],
                        'adminLink' => function_exists( 'wdtmcp_admin_chart_url' )
                            ? wdtmcp_admin_chart_url( $chart_id )
                            : '',
                    );
                }, $rows );

                return array(
                    'charts' => $charts,
                    'count'  => count( $charts ),
                );
            },

            'permission_callback' => static function ( $input = null ) {
                return function_exists( 'wdtmcp_can_execute_ability' )
                    ? wdtmcp_can_execute_ability( 'wpdatatables/list-charts', $input )
                    : current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Call with an empty object {}. Render each chart title as a clickable markdown link using adminLink. Use the returned chart id with get-chart-info.', 'wpdatatables' ),
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
            ),
        )
    );
}
