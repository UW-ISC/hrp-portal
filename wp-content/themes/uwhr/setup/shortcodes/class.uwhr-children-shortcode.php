<?php


/**
 * UWHR Children
 *
 * Displays children pages of current page
 *
 * Options:
 *      excerpt: true|false Show the excerpt
 *      image: true|false Show the featured image
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.4.0
 * @package UWHR
 */

class UWHR_Children {
    function __construct() {
        add_shortcode('children', array( $this, 'children_handler' ) );
    }

    function children_handler($atts, $content) {
        $attributes = shortcode_atts( array(
            'excerpt' => true,
            'image' => false,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'posts_per_page' => 100,
            'force' => ''
        ), $atts );

        $excluded_IDs = empty($attributes['force']) ? get_menu_excluded_ids() : array();

        global $post;

        $args = array(
            'posts_per_page'    => $attributes['posts_per_page'],
            'post_parent'       => $post->ID,
            'post_type'         => 'page',
            'post__not_in'      => $excluded_IDs,
            'orderby'           => $attributes['orderby'],
            'order'             => $attributes['order'],
        );
        $children = get_posts( $args );

        $html = '';

        $html .= '<div class="uwhr-child-pages">';

        foreach( $children as $child ) {
            $id = $child->ID;
            $html .= '<div class="uwhr-child-page"><h4 class="h6 m-b-0 m-t-0"><a href="' . get_permalink($id) . '">' . get_the_title($id) . '<i class="fa fa-angle-right m-l-xs"></i></a></h4>';

            if ( 1 == $attributes['excerpt'] AND ! empty(uwhr_get_the_excerpt($id) ) ) {
                $html .= '<p>' . uwhr_get_the_excerpt($id) . '</p>';
            }
            if ( $attributes['image'] ) {
                $html .= get_the_post_thumbnail($id, 'thumbnail-large');
            }

            $html .= '</div>';
        }

        $html .= '<div>';

        return $html;
    }
}
