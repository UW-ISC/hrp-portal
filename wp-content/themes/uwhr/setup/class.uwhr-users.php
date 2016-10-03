<?php

/**
 * Users
 *
 * Configure user profiles, roles, and capabilities.
 * This is the UWHR way of doing things.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.3.0
 * @package UWHR
 */

class UWHR_Users {

    function __construct() {

        // These only need to run once, at theme activation or deactivation
        add_action( 'after_switch_theme', array( $this, 'clone_editor_create_publisher_role' ) );
        add_action( 'after_switch_theme', array( $this, 'setup_theme_caps' ) );
        add_action( 'switch_theme', array( $this, 'remove_theme_caps' ) );

        // Gotta run this every WordPress load
        add_action( 'admin_head', array( $this, 'disable_structure_elements' ) );
        add_action( 'admin_init', array( $this, 'hide_customize_menu_page' ) );
        add_action( 'admin_init', array( $this, 'set_revisionary_monitor_roles' ) );
        add_action( 'editable_roles', array( $this, 'hide_roles' ) );

        // Disable Add New for all users with edit_structure cap
        add_action( 'admin_menu', array( $this, 'hide_edit_menu_page') );
        add_action( 'admin_bar_menu', array( $this, 'modify_toolbar'), 999 );
        add_action( 'admin_head', array( $this, 'hide_add_new_button') );
        add_action( 'admin_menu', array( $this, 'admin_redirect') );
        add_action( 'admin_init', array( $this, 'permissions_notice') );

        add_action( 'transition_post_status', array( $this, 'notify_reviewers_on_publish' ), 10, 3 );

        add_action( 'admin_bar_menu', array( $this, 'remove_wp_logo'), 999 );
    }

    /**
     * Clone Editor and Create Publisher Role
     *
     * Make a copy of the editor role and ditch some capabilities. Then create
     * a special publisher role.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $wp_roles The WordPress registered roles
     * @global $UWHR Object
     */
    function clone_editor_create_publisher_role() {
        global $UWHR;
        global $wp_roles;

        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }
        $editor = $wp_roles->get_role( 'editor' );

        $role = $UWHR->get_publisher_role();
        $wp_roles->add_role( $role, ucfirst(strtolower( $role )), $editor->capabilities);

        $new_role = get_role( $role );
        $new_role->remove_cap('delete_pages');
        $new_role->remove_cap('delete_others_pages');
        $new_role->remove_cap('delete_published_pages');
        $new_role->remove_cap('delete_posts');
        $new_role->remove_cap('delete_others_posts');
        $new_role->remove_cap('delete_published_posts');
    }

    /**
     * Theme Capabilities
     *
     * Give structure roles the edit structure capability and access
     * to the apperance menu
     *
     * Give all roles the ability to upload image files
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $UWHR Object
     */
    function setup_theme_caps() {
		global $UWHR;
        global $wp_roles;

        $cap = $UWHR->get_edit_structure_cap();
		foreach( $UWHR->get_structure_roles() as $r ) {
			$role = get_role( $r );
			$role->add_cap( $cap );
            $role->add_cap( 'edit_theme_options' );
		}

        $all_roles = $wp_roles->get_names();
        foreach( $all_roles as $k => $v ) {
            $role = get_role( $k );
            $role->add_cap('upload_files');
        }
	}

    /**
     * Remove Theme Capabilities
     *
     * @see setup_theme_caps()
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $UWHR Object
     */
    function remove_theme_caps() {
        global $UWHR;
        global $wp_roles;

        $cap = $UWHR->get_edit_structure_cap();
        foreach( $UWHR->get_structure_roles() as $r ) {
            $role = get_role( $r );
            $role->remove_cap( $cap );
            $role->remove_cap( 'edit_theme_options' );
        }

        $all_roles = $wp_roles->get_names();
        foreach( $all_roles as $k => $v ) {
            $role = get_role( $k );
            $role->remove_cap('upload_files');
        }
    }

    /**
     * Adds some css to pages to make sure non-strucutre users can't fiddle with stuff
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 1.0.6
     * @package UWHR
     *
     * @global $UWHR Object
     */
    function disable_structure_elements() {
        global $UWHR;

        if ( ! current_user_can( $UWHR->get_edit_structure_cap() ) ) {
            echo '<style>#edit-slug-box { display: none; }</style>';
            echo '<style>.has-row-actions .inline.hide-if-no-js { display: none; }</style>';
        }
    }

    /**
     * Remove Customizer Menu
     *
     * Remove for all users.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $submenu array
     */
    function hide_customize_menu_page() {
        global $submenu;
        unset($submenu['themes.php'][6]);
    }

    /**
     * Set Revisionary Monitoring Role
     *
     * This function sets up the Revisionary plugin to work with our publishing strategy.
     * Revisors should be monitored by publishers, not editors.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @link https://wordpress.org/plugins/revisionary/
     *
     * @global $UWHR Object
     */
    function set_revisionary_monitor_roles() {
        global $UWHR;
        if ( current_user_can( 'revisor' ) ) {
            defined('RVY_MONITOR_ROLES') or define('RVY_MONITOR_ROLES',$UWHR->get_publisher_role());
        }
    }

    /**
     * Remove Roles
     *
     * Non-destructively remove some of the roles we don't need from the
     * role assignment box.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $UWHR Object
     *
     * @param $all_roles array All roles.
     * @return $all_roles array Filtered list of roles.
     */
    function hide_roles( $all_roles ) {
        global $UWHR;

        $roles = $UWHR->get_content_roles();
        foreach( $all_roles as $key => $value ) {
            if ( ! in_array( $key, $roles ) ) {
                unset($all_roles[$key]);
            }
        }
        return $all_roles;
    }

    /**
     * Remove Edit Page
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $submenu array
     * @global $UWHR Object
     */
    function hide_edit_menu_page() {
        global $submenu;
        global $UWHR;
        if ( ! current_user_can( $UWHR->get_edit_structure_cap() ) ) {
            unset($submenu['edit.php?post_type=page'][10]);
        }
    }

    /**
     * Remove New Page from Toolbar
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $wp_admin_bar array
     * @global $UWHR Object
     */
    function modify_toolbar() {
        global $wp_admin_bar;
        global $UWHR;
        if ( ! current_user_can( $UWHR->get_edit_structure_cap() ) ) {
            $wp_admin_bar->remove_node( 'new-page' );
        }
    }

    /**
     * Hide Add New Button
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $current_screen object
     * @global $UWHR Object
     */
    function hide_add_new_button() {
        global $current_screen;
        global $UWHR;

        if( $current_screen->id == 'edit-page' && ! current_user_can( $UWHR->get_edit_structure_cap() ) ) {
            echo '<style>h1 a {display: none;}</style>';
        }

        if( $current_screen->parent_base == 'edit' && ! current_user_can( $UWHR->get_edit_structure_cap() ) ) {
            echo '<style>h1 a {display: none;}</style>';
        }
    }

    /**
     * Block Access to New Post page
     *
     * If user doesn't have edit structure priviledge, send them to admin screen
     * with permissions_error flag set to true.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $UWHR Object
     */
    function admin_redirect() {
        global $UWHR;
        $result = stripos($_SERVER['REQUEST_URI'], 'post-new.php?post_type=page');
        if ( $result !== false && ! current_user_can( $UWHR->get_edit_structure_cap() ) ) {
            wp_redirect( get_option('siteurl') . '/wp-admin/index.php?permissions_error=true');
        }
    }

    /**
     * Post Notice
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     */
    function create_posts_notification() {
        echo '<div id="permissions-warning" class="error fade"><p><strong>' . __('You do not have permission to access that page.') . '</strong></p></div>';
    }

    /**
     * Post Notice Check
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     */
    function permissions_notice() {
        if( isset($_GET['permissions_error'] ) ) {
            add_action('admin_notices', array( $this, 'create_posts_notification') );
        }
    }

    /**
     * Notify Reviewers of Publish
     *
     * This is part of the UWHR editorial strategy. Each time a page is updated or changes status,
     * the reviewer will receive an email outlining the change and who did it. The reviewer can work
     * through a backlog and check the edits.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 1.1.0
     * @package UWHR
     */
    function notify_reviewers_on_publish( $new_status, $old_status, $post ) {
        if ( ! isset( get_option( 'unit_settings' )['notify_reviewers_toggle'] ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $post_types = array( 'page' );
        if ( ! in_array($post->post_type, $post_types) ) {
            return;
        }

        $action = '';

        if ( 'publish' == $old_status && 'publish' != $new_status ) {
            $action = 'Unpublished';
        }

        if ( 'publish' != $old_status && 'publish' == $new_status ) {
            $action = 'Published';
        }

        if ( 'publish' == $old_status && 'publish' == $new_status ) {
            $action = 'Updated';
        }

        $user = wp_get_current_user();
        $user_display_name = $user->display_name;
        $user_email = $user->user_email;

        $id = $post->ID;
        $title = $post->post_title;
        $link = get_permalink( $id );
        $edit_link = get_edit_post_link( $id );
        $revisions = wp_get_post_revisions( $id );
        $date = current_time( get_option('date_format') );
        $time = current_time( get_option('time_format') );
        $blog_id = get_current_blog_id();
        $blog_title = get_bloginfo('name');
        $blog_url = get_bloginfo('url');

        $to = ( isset( get_option( 'unit_settings' )['notify_reviewers_email'] ) ) ? get_option( 'unit_settings' )['notify_reviewers_email'] : 'uwhred@uw.edu';
        $subject = 'UWHR ' . ucfirst($post->post_type) . ' ' . $action . ': ' . $title;

        $message = '<p>A ' . $post->post_type . ' on <a href="'.$blog_url.'">' . $blog_title . '</a> was recently updated. Information about the edited page and who edited it is listed below. You may contact the editor directly by replying to this email.</p>';

        $message .= '<p><strong>' . $title . '</strong><br><a href="'.$link.'">View</a> | <a href="'.$edit_link.'">Edit</a>';

        if ( ! empty($revisions) ) {
            $keys = array_keys($revisions);
            $message .= ' | <a href="'.get_admin_url( $blog_id ) . 'revision.php?revision='.$keys[0].'">Compare</a>';
        }

        $message .= '</p>';

        $message .= '<p>' . $user_display_name . '<br><a href="mailto:'.$user_email.'">' . $user_email . '</a></p>';

        $message .= '<p>Time: ' . $time . '<br>Date: ' . $date . '</p>';

        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: '.$user_display_name.' <'.$user_email.'>';

        wp_mail( $to, $subject, $message, $headers );
    }

    /**
     * Remove WP Logo
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     */
    function remove_wp_logo( $wp_admin_bar ) {
        $wp_admin_bar->remove_node( 'wp-logo' );
    }
}
