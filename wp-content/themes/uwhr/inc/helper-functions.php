<?php
/**
 * Helper functions used throughout the UWHR theme
 *
 * Most functionality is actually built out in the class/ directory through
 * the global $UWHR Object. These functions are used in the template functions files.
 *
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.2.0
 * @package UWHR
 */



/*********************************************************************************************************/



/**
 * Display or retrieve the current post short title with optional content.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 0.9.0
 * @package UWHR
 *
 * @param string $before Optional. Content to prepend to the title.
 * @param string $after  Optional. Content to append to the title.
 * @param bool   $echo   Optional, default to true.Whether to display or return.
 *
 * @return string|void String if $echo parameter is false.
 */
if ( ! function_exists( 'the_short_title' ) ) :
    function the_short_title( $before = '', $after = '', $echo = true ) {
        $title = get_the_short_title();

        if ( strlen($title) == 0 ) {
            return;
        }

        $title = $before . $title . $after;

        if ( $echo ) {
            echo $title;
        } else {
            return $title;
        }
    }
endif;

/**
 * Retrieve post short title, if exists, otherwise retrieve the post title
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 0.9.0
 * @package UWHR
 *
 * @param int|WP_Post $post Optional. Post ID or WP_Post object. Default is global $post.
 *
 * @return string
 */
if ( ! function_exists( 'get_the_short_title' ) ) :
    function get_the_short_title( $post = 0 ) {
        $post = get_post( $post );

        $short_title = get_post_meta( $post->ID, '_uwhr_page_short_title', true );
        $title = ! empty( $short_title ) ? esc_html($short_title) : get_the_title($post);
        $id = isset( $post->ID ) ? $post->ID : 0;

        return apply_filters( 'the_title', $title, $id );
    }
endif;

/**
 * Get Post's Feature Image URL
 *
 * Grab the post's featured image url or a default img
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.2.0
 * @package UWHR
 *
 * @return $url string The url of the featured image of WP Post $id by $size
 */
if ( ! function_exists( 'get_image_url_by_size' ) ) :
    function get_image_url_by_size( $id, $size = 'pano-small' ) {
        $url = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), $size )[0];

        if( ! $url ){
            $url = get_template_directory_uri() . '/assets/images/defaults/hero.jpg';
        }

        return $url;
    }
endif;

/**
 * Is Custom Post Type
 *
 * Is the current post a custom post type
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 *
 * @return boolean
 */
if ( ! function_exists('is_custom_post_type') ) :
    function is_custom_post_type() {
        return array_key_exists(  get_post_type(),  get_post_types( array( '_builtin'=>false) ) );
    }
endif;

/**
 * Get All Site IDs
 *
 * Grab all the site IDs
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 *
 * @return array $IDs An array of all the IDs in the multisite install
 */
if ( ! function_exists('get_all_site_ids') ) :
    function get_all_site_ids() {
        $IDs = array();
        $sites = wp_get_sites();
        foreach ( $sites as $site ) {
            $IDs[] = $site['blog_id'];
        }
        return $IDs;
    }
endif;

/**
 * Get Menu Excluded IDs
 *
 * Builds an array of pages that are tagged to be excluded from sidebar menu
 * Includes the front page.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.5.0
 * @package UWHR
 *
 * @return array $IDs An array of all the pages to be excluded from menu
 */
if ( ! function_exists('get_menu_excluded_ids') ) :
    function get_menu_excluded_ids() {
        $IDs = array( get_option( 'page_on_front' ) );

        $args = array(
            'post_type' => 'page',
            'hierarchical' => 0,
            'meta_key' => '_uwhr_page_exclude_from_sidebar_menu',
            'meta_value' => 1,
        );
        $pages = get_pages( $args );

        foreach ( $pages as $page ) {
            $IDs[] = $page->ID;
        }

        return $IDs;
    }
endif;

/**
 * Has Featured Pages
 *
 * Checks to see if the site has any featured pages
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 1.0.5
 * @package UWHR
 *
 * @return boolean If the site has any featured pages
 */
 if ( ! function_exists('has_featured_pages') ) :
    function has_featured_pages() {
        $args = array(
            'post_type' => 'page',
            'meta_key' => '_uwhr_page_featured',
            'meta_value' => 1,
            'hierarchical' => 0
        );
        $pages = get_pages( $args );

        if ( count($pages) >= 1 ) {
            return true;
        } else {
            return false;
        }
    }
endif;

/**
 * Mime Type Formatting
 *
 * This function accepts a file mime type string and a desired format and then
 * switches between mime types to output a format string.
 * The whitelisted formats are a class appended to cards, a small element to
 * appear in another tag, or a link-file-* class to append to an a tag.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 0.7.0
 * @package UWHR
 *
 * @param string $mime_type The mime type of the file
 * @param string $param The type of format desired to be returned
 *
 * @return string $$p A string in desired format with file mime type
 */
if ( ! function_exists('uwhr_mime_type_format') ) :
    function uwhr_mime_type_format( $mime_type, $param = 'link' ) {

        $whitelist = array('card', 'small', 'link');

        if ( in_array( $param, $whitelist ) ) {
            $p = $param;
        } else {
            return '';
        }

        switch ($mime_type) {
            case 'application/msword':
                $card = 'card-file-word';
                $small = '<small>(MS Word)</small>';
                $link = 'link-file-word';
                break;
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                $card = 'card-file-word';
                $small = '<small>(MS Word)</small>';
                $link = 'link-file-word';
                break;
            case 'application/pdf':
                $card = 'card-file-pdf';
                $small = '<small>(PDF)</small>';
                $link = 'link-file-pdf';
                break;
            case 'application/vnd.ms-excel':
                $card = 'card-file-excel';
                $small = '<small>(Excel)</small>';
                $link = 'link-file-excel';
                break;
            default:
                $card = '';
                $small = '';
                $link = '';
                break;
        }

        return $$p;
    }
endif;

/**
 * Grab transient or Make API request
 *
 * Check if a transient exists then return that, otherwise handle the call to remote
 * api to build new transient and return new data.
 *
 * @see uwhr_display_calendar_feed() & uwhr_display_twitter_feed()
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.3.0
 * @package UWHR
 *
 * @param $transient string The name of the desired transient and function call
 * @param $timeout int The length of time for the transient to last if does not already exist
 * @param $api_call_check boolean Whether or not to return a check if api is called
 *
 * @return $data array|object Data source saved into a WP DB transient. Optional is_api_called flag.
 */
if ( ! function_exists( 'grab_transient_or_api_request' ) ):
    function grab_transient_or_api_request( $transient, $timeout = DAY_IN_SECONDS, $api_call_check = false ) {

        if ( !isset($transient) ) {
            return false;
        }

        $data = get_transient( 'uwhr_' . $transient );

        // Yep! Just return it and we're done.
        if( ! empty( $data ) ) {

            // The function will return here every time after the first time it is run, until the transient expires.
            if ( $api_call_check ) {
                return (object) array(
                    'data' => $data,
                    'is_api_called' => false
                );
            } else {
                return $data;
            }

        // Nope! We gotta make a call.
        } else {

            // Call the transient function to build the data
            $data = $transient();

            // Save the API response so we don't have to call again until $timeout passes.
            set_transient( 'uwhr_' . $transient , $data, $timeout );

            if ( $api_call_check ) {
                return (object) array(
                    'data' => $data,
                    'is_api_called' => true
                );
            } else {
                return $data;
            }
        }
    }
endif;

/**
 * Flush page caches identified by ID
 *
 * Check if a transient exists then return that, otherwise handle the call to remote
 * api to build new transient and return new data.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 1.0.4
 * @package UWHR
 *
 * @param $postid int The ID of the post to bust
 */
if ( ! function_exists( 'uwhr_flush_page_cache_by_id' ) ):
    function uwhr_flush_page_cache_by_id( $postid ) {
        if (function_exists('w3tc_pgcache_flush_post')) {
            w3tc_pgcache_flush_post($postid);
        }
    }
endif;

/**
 * Flush page caches identified by URL
 *
 * Check if a transient exists then return that, otherwise handle the call to remote
 * api to build new transient and return new data.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 1.0.4
 * @package UWHR
 *
 * @param $postid int The ID of the post to bust
 */
if ( ! function_exists( 'uwhr_flush_page_cache_by_url' ) ):
    function uwhr_flush_page_cache_by_url( $url, $blogid = null ) {
        if (function_exists('w3tc_pgcache_flush_post')) {
            w3tc_pgcache_flush_url( get_site_url($blogid,$url) );
        }
    }
endif;
