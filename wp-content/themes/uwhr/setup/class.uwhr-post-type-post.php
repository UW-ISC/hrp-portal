<?php

/**
 * Post
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

class UWHR_Post {

	function __construct() {
		add_action( 'admin_menu', array($this, 'remove_tags' ) );
		add_action( 'manage_edit-post_columns', array( $this, 'add_columns') );
	}

	/**
     * Remove tags from admin menu and tag metabox from post type admin screen
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.5.0
     * @package UWHR
     */
	function remove_tags() {
    	remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=post_tag');
        remove_meta_box( 'tagsdiv-post_tag', 'post', 'normal' );
	}

	/**
     * Add admin table columns
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.5.0
     * @package UWHR
     *
     * @param array $columns
     *
     * @return array $columns
     */
    function add_columns( $columns ) {
        $auth = $columns['author'];
        $date = $columns['date'];
        unset($columns['date']);
        unset($columns['tags']);
        $columns['author'] = $auth;
        $columns['date'] = $date;
        return $columns;
    }
}
