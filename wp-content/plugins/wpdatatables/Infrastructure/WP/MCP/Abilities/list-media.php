<?php
/**
 * Ability: wpdatatables/list-media
 *
 * Lists media items from the WordPress media library for use in tables.
 * Returns attachment IDs, URLs, and metadata so AI can reference images in cells.
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_list_media_ability' );

function wdtmcp_register_list_media_ability() {
    wp_register_ability(
        'wpdatatables/list-media',
        array(
            'label'       => __( 'List Media Library', 'wpdatatables' ),
            'description' => __( 'Lists media items from the WordPress media library. Returns attachment_id, source_url, alt_text, title for use in table cells. Use search to find images by title, or media_type to filter (image, video, etc.). Use attachment_id in cell data with create-simple-table or update-simple-table-styles to insert images. WHEN TO CALL: before putting images into Simple table cells — when you need an existing attachment_id from the Media Library. OPTIONAL INPUT: search (string, match title/filename), media_type (image|video|audio|application), page (default 1), per_page (default 20, max 100). RETURNS: { media: [{ attachment_id, source_url, alt_text, title, mime_type }], total, page }. IF NO MATCH: use upload-media-from-url to import a remote image first. TYPICAL WORKFLOW: list-media → use attachment_id in create-simple-table or update-simple-table-styles cell data as {"attachment_id":123,"size":"medium","alt":"..."}.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'search' => array(
                        'type'        => 'string',
                        'description' => 'Search media by title or filename.',
                    ),
                    'media_type' => array(
                        'type'        => 'string',
                        'description' => 'Filter by type: image, video, audio, application.',
                    ),
                    'per_page' => array(
                        'type'        => 'integer',
                        'description' => 'Number of items to return (default 20, max 100).',
                    ),
                    'page' => array(
                        'type'        => 'integer',
                        'description' => 'Page number for pagination (default 1).',
                    ),
                ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'media' => array(
                        'type'        => 'array',
                        'description' => 'Array of media items.',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'attachment_id' => array( 'type' => 'integer', 'description' => 'WordPress attachment ID.' ),
                                'source_url'    => array( 'type' => 'string',  'description' => 'URL to the media file.' ),
                                'title'         => array( 'type' => 'string',  'description' => 'Attachment title.' ),
                                'alt_text'      => array( 'type' => 'string',  'description' => 'Alt text for images.' ),
                                'media_type'    => array( 'type' => 'string',  'description' => 'image, video, audio, etc.' ),
                                'mime_type'     => array( 'type' => 'string',  'description' => 'MIME type.' ),
                            ),
                        ),
                    ),
                    'total' => array( 'type' => 'integer', 'description' => 'Total matching items.' ),
                    'page'  => array( 'type' => 'integer', 'description' => 'Current page number.' ),

                ),
            ),

            'execute_callback' => 'wdtmcp_execute_list_media',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Optional search and media_type filters. Use returned attachment_id in create-simple-table or update-simple-table-styles cell objects.', 'wpdatatables' ),
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
            ),
        )
    );
}

/**
 * Execute callback for wpdatatables/list-media.
 *
 * @param array $input
 * @return array|WP_Error
 */
function wdtmcp_execute_list_media( $input ) {
    $search    = isset( $input['search'] ) ? sanitize_text_field( $input['search'] ) : '';
    $type      = isset( $input['media_type'] ) ? sanitize_text_field( $input['media_type'] ) : '';
    $per_page  = isset( $input['per_page'] ) ? min( 100, max( 1, (int) $input['per_page'] ) ) : 20;
    $page      = isset( $input['page'] ) ? max( 1, (int) $input['page'] ) : 1;

    $args = array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    if ( '' !== $search ) {
        $args['s'] = $search;
    }

    if ( '' !== $type ) {
        $args['post_mime_type'] = $type;
        if ( 'image' === $type ) {
            $args['post_mime_type'] = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml' );
        }
    }

    $query = new \WP_Query( $args );
    $posts = $query->posts;
    $total = (int) $query->found_posts;

    $media = array();
    foreach ( $posts as $post ) {
        $url = wp_get_attachment_url( $post->ID );
        if ( ! $url ) {
            continue;
        }
        $media[] = array(
            'attachment_id' => (int) $post->ID,
            'source_url'    => $url,
            'title'         => $post->post_title,
            'alt_text'      => (string) get_post_meta( $post->ID, '_wp_attachment_image_alt', true ),
            'media_type'    => (string) ( wp_attachment_is_image( $post->ID ) ? 'image' : $post->post_mime_type ),
            'mime_type'     => (string) $post->post_mime_type,
        );
    }

    return array(
        'media' => $media,
        'total' => $total,
        'page'  => $page,
    );
}
