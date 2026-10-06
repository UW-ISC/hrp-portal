<?php
/**
 * Ability: wpdatatables/open-table-editor
 *
 * Returns an adminLink to open a table in the wpDataTables constructor/editor.
 *
 * @package wpDataTables_MCP_Server
 */

defined( 'ABSPATH' ) or die( 'Access denied.' );

add_action( 'wp_abilities_api_init', 'wdtmcp_register_open_table_editor_ability' );

/**
 * Register open-table-editor ability.
 *
 * @return void
 */
function wdtmcp_register_open_table_editor_ability() {
    wp_register_ability(
        'wpdatatables/open-table-editor',
        array(
            'label'       => __( 'Open Table Editor', 'wpdatatables' ),
            'description' => __( 'Returns the admin URL to open a specific table in the wpDataTables constructor/editor. Use when the user asks to open, edit, configure, or navigate to a table. Returns an adminLink the user can click.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_id' => array(
                        'type'        => 'integer',
                        'description' => 'The unique wpDataTable ID (as returned by list-tables).',
                    ),
                ),
                'required'   => array( 'table_id' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_id'  => array( 'type' => 'integer' ),
                    'adminLink' => array(
                        'type'        => 'string',
                        'description' => 'Admin URL — open this link to navigate to the table editor.',
                    ),
                ),
            ),

            'execute_callback' => static function ( $input ) {
                $table_id = isset( $input['table_id'] ) ? (int) $input['table_id'] : 0;

                if ( $table_id <= 0 ) {
                    return new \WP_Error(
                        'wdtmcp_invalid_input',
                        __( 'A valid table_id (positive integer) is required.', 'wpdatatables' )
                    );
                }

                $admin_link = function_exists( 'wdtmcp_admin_table_url' )
                    ? wdtmcp_admin_table_url( $table_id )
                    : '';

                if ( '' === $admin_link ) {
                    return new \WP_Error(
                        'wdtmcp_not_found',
                        sprintf(
                            /* translators: %d: table ID */
                            __( 'Table with ID %d was not found.', 'wpdatatables' ),
                            $table_id
                        )
                    );
                }

                return array(
                    'table_id'  => $table_id,
                    'adminLink' => $admin_link,
                );
            },

            'permission_callback' => static function ( $input = null ) {
                return function_exists( 'wdtmcp_can_execute_ability' )
                    ? wdtmcp_can_execute_ability( 'wpdatatables/open-table-editor', $input )
                    : current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Requires table_id. Return adminLink as a clickable markdown link so the user can open the table editor.', 'wpdatatables' ),
                    'readonly'     => true,
                    'destructive'  => false,
                    'idempotent'   => true,
                ),
            ),
        )
    );
}
