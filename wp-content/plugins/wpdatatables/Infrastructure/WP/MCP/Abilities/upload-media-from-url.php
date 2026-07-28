<?php
/**
 * Ability: wpdatatables/upload-media-from-url
 *
 * Downloads an image from a URL and adds it to the WordPress media library.
 * Returns attachment_id and source_url for use in table cells.
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_upload_media_from_url_ability' );

function wdtmcp_register_upload_media_from_url_ability() {
    wp_register_ability(
        'wpdatatables/upload-media-from-url',
        array(
            'label'       => __( 'Upload Media from URL', 'wpdatatables' ),
            'description' => __( 'Downloads an image from a URL and adds it to the WordPress media library. Returns attachment_id and source_url for use in table cells via attachment_id or direct img src. Supports jpg, png, gif, webp. Use for product images, logos, etc. WHEN TO CALL: when the user provides a remote image URL and you need it in a Simple table cell, or when list-media finds no suitable existing image. REQUIRED INPUT: url (string, public http/https image URL). OPTIONAL: alt_text, title. RETURNS: attachment_id, source_url, title, alt_text. NEXT STEP: use attachment_id in create-simple-table or update-simple-table-styles as {"attachment_id":ID,"size":"medium","alt":"..."}. FOR LOCAL FILES on disk: do not use this tool — upload via WordPress REST API /wp/v2/media (see create-table-from-source description) or ask the user to upload via Media → Add New, then use list-media.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'url' => array(
                        'type'        => 'string',
                        'description' => 'Public URL of the image to download (http or https).',
                    ),
                    'alt_text' => array(
                        'type'        => 'string',
                        'description' => 'Alt text for the image (accessibility).',
                    ),
                    'title' => array(
                        'type'        => 'string',
                        'description' => 'Title for the media attachment.',
                    ),
                ),
                'required' => array( 'url' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'attachment_id' => array( 'type' => 'integer', 'description' => 'WordPress attachment ID.' ),
                    'source_url'    => array( 'type' => 'string',  'description' => 'URL to the uploaded file.' ),
                    'title'         => array( 'type' => 'string',  'description' => 'Attachment title.' ),
                    'alt_text'      => array( 'type' => 'string',  'description' => 'Alt text.' ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_upload_media_from_url',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Requires a public http/https image URL. Each call creates a new Media Library attachment; use attachment_id in Simple table cells afterward.', 'wpdatatables' ),
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => false,
                ),
            ),
        )
    );
}

/**
 * Execute callback for wpdatatables/upload-media-from-url.
 *
 * @param array $input
 * @return array|WP_Error
 */
function wdtmcp_execute_upload_media_from_url( $input ) {
    $url     = isset( $input['url'] ) ? esc_url_raw( trim( (string) $input['url'] ) ) : '';
    $alt     = isset( $input['alt_text'] ) ? sanitize_text_field( $input['alt_text'] ) : '';
    $title   = isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : '';

    if ( '' === $url ) {
        return new \WP_Error( 'wdtmcp_missing_param', __( 'url is required.', 'wpdatatables' ) );
    }

    $parsed = wp_parse_url( $url );
    if ( ! $parsed || empty( $parsed['scheme'] ) || ! in_array( strtolower( $parsed['scheme'] ), array( 'http', 'https' ), true ) ) {
        return new \WP_Error( 'wdtmcp_invalid_url', __( 'Invalid or unsupported URL scheme.', 'wpdatatables' ) );
    }

    $url_check = wdtmcp_validate_public_remote_source_url( $url );
    if ( is_wp_error( $url_check ) ) {
        return $url_check;
    }

    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = download_url( $url, 30 );
    if ( is_wp_error( $tmp ) ) {
        return $tmp;
    }

    $url_path = isset( $parsed['path'] ) ? $parsed['path'] : '';
    $filename = basename( $url_path );
    // If the URL has no path (unlikely for image URLs), fall back to the full URL.
    if ( '' === $filename ) {
        $filename = basename( $url );
    }

    $file_array = array(
        'name'     => $filename,
        'tmp_name' => $tmp,
    );

    $allowed = array( 'jpg', 'jpeg', 'jpe', 'png', 'gif', 'webp' );
    $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
    if ( ! in_array( $ext, $allowed, true ) ) {
        @unlink( $tmp );
        return new \WP_Error( 'wdtmcp_invalid_type', __( 'Only jpg, png, gif, webp images are allowed.', 'wpdatatables' ) );
    }

    $id = media_handle_sideload( $file_array, 0, $title );
    if ( is_wp_error( $id ) ) {
        @unlink( $tmp );
        return $id;
    }

    if ( '' !== $alt ) {
        update_post_meta( $id, '_wp_attachment_image_alt', $alt );
    }
    if ( '' !== $title ) {
        wp_update_post( array(
            'ID'         => $id,
            'post_title' => $title,
        ) );
    }

    $source_url = wp_get_attachment_url( $id );
    if ( ! $source_url ) {
        wp_delete_attachment( $id, true );
        return new \WP_Error( 'wdtmcp_upload_failed', __( 'Failed to get attachment URL after upload.', 'wpdatatables' ) );
    }

    return array(
        'attachment_id' => (int) $id,
        'source_url'    => $source_url,
        'title'         => get_the_title( $id ),
        'alt_text'      => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
    );
}
