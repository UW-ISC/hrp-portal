<?php
/**
 * Ability: wpdatatables/open-chart-wizard
 *
 * Returns an adminLink to open a chart in the wpDataTables chart wizard,
 * or the empty wizard when no chart_id is provided.
 *
 * @package wpDataTables_MCP_Server
 */

defined( 'ABSPATH' ) or die( 'Access denied.' );

add_action( 'wp_abilities_api_init', 'wdtmcp_register_open_chart_wizard_ability' );

/**
 * Register open-chart-wizard ability.
 *
 * @return void
 */
function wdtmcp_register_open_chart_wizard_ability() {
    wp_register_ability(
        'wpdatatables/open-chart-wizard',
        array(
            'label'       => __( 'Open Chart Wizard', 'wpdatatables' ),
            'description' => __( 'Returns the admin URL to open the wpDataTables chart wizard. Pass chart_id to open an existing chart for editing; omit chart_id to open the create-chart wizard. Returns an adminLink the user can click.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'chart_id' => array(
                        'type'        => 'integer',
                        'description' => 'Optional chart ID from list-charts. When omitted, opens the empty chart wizard.',
                    ),
                    'engine'   => array(
                        'type'        => 'string',
                        'description' => 'Optional render engine (google, chartjs, highcharts, apexcharts). Looked up when omitted for an existing chart.',
                    ),
                ),
                'required'   => array(),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'chart_id'  => array( 'type' => array( 'integer', 'null' ) ),
                    'adminLink' => array(
                        'type'        => 'string',
                        'description' => 'Admin URL — open this link to navigate to the chart wizard.',
                    ),
                ),
            ),

            'execute_callback' => static function ( $input ) {
                $chart_id = isset( $input['chart_id'] ) ? (int) $input['chart_id'] : 0;
                $engine   = isset( $input['engine'] ) ? sanitize_text_field( (string) $input['engine'] ) : '';

                if ( $chart_id > 0 ) {
                    $admin_link = function_exists( 'wdtmcp_admin_chart_url' )
                        ? wdtmcp_admin_chart_url( $chart_id, $engine )
                        : '';

                    if ( '' === $admin_link ) {
                        return new \WP_Error(
                            'wdtmcp_not_found',
                            sprintf(
                                /* translators: %d: chart ID */
                                __( 'Chart with ID %d was not found.', 'wpdatatables' ),
                                $chart_id
                            )
                        );
                    }

                    return array(
                        'chart_id'  => $chart_id,
                        'adminLink' => $admin_link,
                    );
                }

                return array(
                    'chart_id'  => null,
                    'adminLink' => function_exists( 'wdtmcp_admin_chart_wizard_url' )
                        ? wdtmcp_admin_chart_wizard_url()
                        : admin_url( 'admin.php?page=wpdatatables-chart-wizard' ),
                );
            },

            'permission_callback' => static function ( $input = null ) {
                return function_exists( 'wdtmcp_can_execute_ability' )
                    ? wdtmcp_can_execute_ability( 'wpdatatables/open-chart-wizard', $input )
                    : current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Pass chart_id to open an existing chart, or omit it to open the create wizard. Return adminLink as a clickable markdown link.', 'wpdatatables' ),
                    'readonly'     => true,
                    'destructive'  => false,
                    'idempotent'   => true,
                ),
            ),
        )
    );
}
