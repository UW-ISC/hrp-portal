<?php
/**
 * Ability: wpdatatables/upload-data-file
 *
 * Uploads a data file (CSV, Excel, JSON, XML) to the WordPress Media Library via
 * base64-encoded content. Designed for MCP clients that cannot stream multipart
 * uploads over the MCP connection.
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_upload_data_file_ability' );

function wdtmcp_register_upload_data_file_ability() {
    wp_register_ability(
        'wpdatatables/upload-data-file',
        array(
            'label'       => __( 'Upload Data File', 'wpdatatables' ),
            'description' => __( 'Uploads a CSV, Excel, JSON, or XML file to the WordPress Media Library using base64-encoded file content. Use this when the user provides a local file path or raw file bytes and MCP cannot POST multipart to /wp/v2/media. RETURNS attachment_id and source_url — pass attachment_id to create-table-from-source or import-table-from-source. REQUIRED: filename (with extension), content_base64. OPTIONAL: title. Supported extensions: csv, xls, xlsx, ods, json, xml. For images use upload-media-from-url instead.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'filename' => array(
                        'type'        => 'string',
                        'description' => 'Filename with extension, e.g. report.csv or data.xlsx.',
                    ),
                    'content_base64' => array(
                        'type'        => 'string',
                        'description' => 'Base64-encoded raw file bytes (standard or URL-safe base64).',
                    ),
                    'title' => array(
                        'type'        => 'string',
                        'description' => 'Optional Media Library title.',
                    ),
                ),
                'required' => array( 'filename', 'content_base64' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'attachment_id' => array( 'type' => 'integer', 'description' => 'WordPress attachment ID.' ),
                    'source_url'    => array( 'type' => 'string',  'description' => 'Public URL of the uploaded file.' ),
                    'title'         => array( 'type' => 'string',  'description' => 'Attachment title.' ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_upload_data_file',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Preferred MCP workflow for local files: upload-data-file → create-table-from-source or import-table-from-source with attachment_id.', 'wpdatatables' ),
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => false,
                ),
            ),
        )
    );
}

/**
 * Execute callback for wpdatatables/upload-data-file.
 *
 * @param array $input
 * @return array|WP_Error
 */
function wdtmcp_execute_upload_data_file( $input ) {
    $filename = isset( $input['filename'] ) ? sanitize_file_name( (string) $input['filename'] ) : '';
    $encoded  = isset( $input['content_base64'] ) ? trim( (string) $input['content_base64'] ) : '';
    $title    = isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : '';

    if ( '' === $filename ) {
        return new \WP_Error( 'wdtmcp_missing_param', __( 'filename is required.', 'wpdatatables' ) );
    }
    if ( '' === $encoded ) {
        return new \WP_Error( 'wdtmcp_missing_param', __( 'content_base64 is required.', 'wpdatatables' ) );
    }

    if ( preg_match( '/^data:[^;]+;base64,/', $encoded ) ) {
        $encoded = substr( $encoded, strpos( $encoded, ',' ) + 1 );
    }

    $encoded = strtr( $encoded, '-_', '+/' );
    $binary  = base64_decode( $encoded, true );
    if ( false === $binary ) {
        return new \WP_Error( 'wdtmcp_invalid_base64', __( 'content_base64 is not valid base64.', 'wpdatatables' ) );
    }

    $attachment_id = wdtmcp_upload_data_file_to_media_library( $binary, $filename, $title );
    if ( is_wp_error( $attachment_id ) ) {
        return $attachment_id;
    }

    $source_url = wp_get_attachment_url( $attachment_id );
    if ( ! $source_url ) {
        wp_delete_attachment( $attachment_id, true );
        return new \WP_Error( 'wdtmcp_upload_failed', __( 'Failed to get attachment URL after upload.', 'wpdatatables' ) );
    }

    return array(
        'attachment_id' => (int) $attachment_id,
        'source_url'    => $source_url,
        'title'         => get_the_title( $attachment_id ),
    );
}
