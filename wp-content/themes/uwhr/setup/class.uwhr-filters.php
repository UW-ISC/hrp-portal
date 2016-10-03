<?php

/**
 * Filters
 *
 * Filter hooks that alter or provide additional functionality to rendered content.
 * Mostly on the front-end but some admin stuff here too.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

class UWHR_Filters {

    function __construct() {
        add_filter( 'the_content', array( $this, 'add_ids_to_header_tags' ) );
        add_filter( 'the_content', array( $this, 'switch_imgsrc_to_https' ) );
        add_filter( 'wp_calculate_image_srcset', array( $this, 'switch_imgsrcset_to_https' ) );
        add_filter( 'body_class', array( $this, 'body_classes') );
        add_filter( 'widget_text', array( $this, 'do_shortcode') );
        add_filter( 'edit_post_link', array( $this, 'edit_post_link') );
        add_filter( 'excerpt_length', array( $this, 'change_excerpt_length' ), 999 );
        add_filter( 'user_contactmethods', array( $this, 'modify_contact_fields'), 10, 1 );
        add_filter( 'post_mime_types', array( $this, 'modify_post_mime_types' ) );
        add_filter( 'page_link', array( $this, 'create_external_permalink' ), 10, 2 );
        add_filter( 'wp_kses_allowed_html', array( $this, 'filter_allowed_html' ), 10, 2);
        add_filter( 'style_loader_tag', array( $this, 'style_loader_tag' ), 10, 2 );
    }

    /**
     * Header Tags
     *
     * Add ids to all header tags if the post has _page_navigation_meta_key toggled true.
     * Used in Page Navigation meta box of class.uwhr-post-type-page.php
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @global $post WP_Post_Object
     *
     * @param $content string The content of the post
     * @return $content string The amended content of the post
     */
    public function add_ids_to_header_tags( $content ) {
        global $post;

        if ( ! get_post_meta( $post->ID, '_uwhr_page_anchor_linking_active', true ) ) {
            return $content;
        }

        $pattern = '#(?P<full_tag><(?P<tag_name>h4)>(?P<tag_contents>[^<]*)</h4>)#i';
        if ( preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER ) ) {
            $find = array();
            $replace = array();
            $top = '';
            foreach( $matches as $match ) {
                // if ( strlen( $match['tag_extra'] ) && false !== stripos( $match['tag_extra'], 'id=' ) ) {
                //     continue;
                // }
                $find[]    = $match['full_tag'];
                $id        = sanitize_title( $match['tag_contents'] );
                $id_attr   = sprintf( ' id="%s"', $id );
                $replace[] = sprintf( '%1$s<%2$s%3$s>%4$s</%2$s>', $top, $match['tag_name'], $id_attr, $match['tag_contents']);
                $top = '<p class="uwhr-toc-top-btn"><a href="#toc">Return to top</a></p>';
            }
            $content = str_replace( $find, $replace, $content ) . '<p class="uwhr-toc-top-btn"><a href="#toc">Return to top</a></p>';
        }
        return $content;
    }

    /**
     * Switch Image SRC's to HTTPS
     *
     * If we are operating under HTTPS, then we need to serve up images under HTTPS.
     * Here, we do a simple loop through the content to find the SRC attribute and
     * switch that bad boy from HTTP to HTTPS if we need the s.
     *
     * @author The Thomas Winston Thorpe <twthorpe@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @param $content string The content of the post
     * @return $content string The amended content of the post
     */
    public function switch_imgsrc_to_https( $content ) {
        if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
            $content = str_replace('src="http://','src="https://',$content);
        }
        return $content;
    }

    /**
     * Switch Image SRCSET's to HTTPS
     *
     * If we are operating under HTTPS, then we need to serve up images under HTTPS.
     * Here, we do a simple loop through the content to find the SRCSET attribute and
     * switch that bad boy from HTTP to HTTPS if we need the s.
     *
     * @author The Thomas Winston Thorpe <twthorpe@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @global $post WP_Post_Object
     *
     * @param $sources string The sourcesets of the image
     * @return $sources string The amended sourcesets of the image
     */
    public function switch_imgsrcset_to_https( $sources ) {
        if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
            foreach($sources as &$source){
                if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
                    $source['url'] = set_url_scheme($source['url'],'https');
                }
            }
        }
        return $sources;
    }


    /**
     * Body classes
     *
     * Provides a whitelist of WP generated classes then added multisite classes
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @param $classes array[] List of all classes
     * @return $classes array[] Filtered list of classes
     */
    function body_classes( $classes ) {
        // List of the only WP generated classes allowed
        $whitelist = array( 'page', 'home', 'search', 'error404', 'logged-in', 'admin-bar' );
        $classes = array_intersect( $classes, $whitelist );

        $id = get_current_blog_id();
        $slug = sanitize_title( get_bloginfo('name'));
        $classes[] = 'site-name-'.$slug;
        $classes[] = 'site-id-'.$id;

        return $classes;
    }

    /**
     * Do Shortcode
     *
     * Exceculte shortcode functions in widget text
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     *
     * @param $text string The widget text
     * @return $text string The widget text with excuted shortcodes
     */
    function do_shortcode( $text ) {
        return do_shortcode( $text );
    }

    /**
     * Post Edit Link
     *
     * This function allows us to have a pretty, Bootstrap styled edit button.
     *
     * @author Thomas Winston Thorpe <twthorpe@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param $output string The markup
     * @return $output string The markup with additional classes.
     */
    function edit_post_link( $output ) {
        $output = str_replace( 'class="post-edit-link"','class="btn btn-danger"', $output );
        $output = str_replace( 'Edit This','<i class="fa fa-pencil-square-o fa-lg"></i> Edit', $output );
        $output = '<hr><p class="article-admin-meta text-right">' . $output . '</p>';
        return $output;
    }

    /**
     * Excerpt Length
     *
     * Adjust the excerpt length
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param $length int The word length
     * @return int The new word length
     */
    function change_excerpt_length( $length ) {
        return 30;
    }

    /**
     * Contact Fields
     *
     * Remove some unneeded contact fields
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @param $contactmethods array A list of contact fields
     * @return $contactmethods array An amended list of contact fields
     */
    function modify_contact_fields( $contactmethods ) {
        unset( $contactmethods['yim'] );
        unset( $contactmethods['aim'] );
        unset( $contactmethods['jabber'] );
        return $contactmethods;
    }

    /**
     * Modify Mime Types
     *
     * Remove and add a couple filter types in the media uploader
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     *
     * @param $post_mime_types Array
     * @return $post_mime_types Array
     */
    function modify_post_mime_types( $post_mime_types ) {
        $post_mime_types['application/pdf'] = array( __( 'PDFs' ), __( 'Manage PDFs' ), _n_noop( 'PDF <span class="count">(%s)</span>', 'PDFs <span class="count">(%s)</span>' ) );
        $post_mime_types['application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document'] = array( __( 'Documents' ), __( 'Manage Documents' ), _n_noop( 'Documents <span class="count">(%s)</span>', 'Documents <span class="count">(%s)</span>' ) );

        unset($post_mime_types['audio']);
        unset($post_mime_types['video']);

        return $post_mime_types;
    }

    /**
     * External Links
     *
     * Parse post link and replace it with meta value, or the 'external_page_link' field.
     * This is used for the WP Pages with post format Link.
     *
     * @param $link string The page permalink
     * @param  $id int The WP Post Object ID
     *
     * @return $link string The page permalink or an external link
     */
    function create_external_permalink( $link, $id ) {
        if ( has_post_format('link',$id) ) {
            $meta = get_field( 'external_page_link', $id );
            $url  = esc_url( filter_var( $meta, FILTER_VALIDATE_URL ) );
            $link = $url;
        }
        return $link;
    }

    /**
     * Filter Allowed HTML
     *
     * Allow Bootstrap-y attributes and other elements inside the WYSIWYG.
     * Feel free to whitelist any additional elements or attributes here that you would
     * feel safe exposing to all users.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.9.0
     * @package UWHR
     *
     * @param $allowed array Allowed tags, attributes, and/or entities
     * @param $context string Context to judge allowed tags by
     *
     * @return $allowed array Allowed tags, attributes, and/or entities
     */
    function filter_allowed_html($allowed, $context) {
        if (is_array($context)) {
            return $allowed;
        }

        if ($context === 'post') {
            $allowed['div']['role'] = true;
            $allowed['div']['aria-multiselectable'] = true;
            $allowed['div']['aria-labelledby'] = true;
            $allowed['a']['data-toggle'] = true;
            $allowed['a']['data-parent'] = true;
            $allowed['a']['aria-expanded'] = true;
            $allowed['a']['aria-controls'] = true;

            $allowed['select']['id'] = true;
            $allowed['select']['class'] = true;
            $allowed['option']['value'] = true;
        }

        return $allowed;
    }

    /**
     * Style Loader Tag
     *
     * In order to avoid processing the Google Fonts external stylesheet for IE8- support,
     * add the data-norem attribute to the tag.
     *
     * @link https://github.com/chuckcarpenter/REM-unit-polyfill
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.9.0
     * @package UWHR
     *
     * @param $link string The link tag
     * @param $handle string Handle of the enqueued style
     *
     * @return $link string The link tag
     */
    function style_loader_tag( $link, $handle ) {
        if( 'google-font-open' === $handle ) {
            $link = str_replace( '/>', ' data-norem />', $link );
        }
        return $link;
    }
}
