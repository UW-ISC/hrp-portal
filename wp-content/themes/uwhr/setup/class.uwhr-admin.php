<?php

/**
 * UWHR Admin
 *
 * Add custom functionality for UWHR Admins in the WordPress admin pages
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

class UWHR_Admin {

    function __construct() {
        add_action( 'admin_notices',  array( $this, 'editing_menu') );
        add_action( 'admin_menu', array( $this, 'transient_tools_menu' ) );
        add_action( 'wp_dashboard_setup', array( $this,  'users_contacts_dashboard_widget' ) );
    }

    /**
     * Editing Menu Warning
     *
     * Print a warning to users when editing the site's menus. We must notify external
     * content authors of the changes for deployment in a static HTML environment.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
    function editing_menu() {
        $screen = get_current_screen();

        // Send to these folks
        // Joseph DeVore <devore@uw.edu>
        // Bryan Nelson <nelsonbm@uw.edu>

        if ($screen->id === 'nav-menus' ) {
            $class = "notice-warning notice";
            $message = "If changing menus, in any way, please send rendered markup to <a href='mailto:devore@uw.edu,nelsonbm@uw.edu?Subject=Updating%20the%20Menu%2D%20UW%20Human%20Resources'>UWHires & POD</a> to be deployed in a static HTML environment.";
            echo '<div class="' . $class . '"><h3>' . $message . '</h3></div>';
        }
    }

    /**
     * Transients Setting Page
     *
     * Adds a page to the Tool dropdown to manage UWHR created transients.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @global $UWHR Object
     */
    function transient_tools_menu() {
        global $UWHR;

        add_management_page( 'Transients',
            'Transients',
            $UWHR->get_admin_cap(),
            'uwhr-transients',
            array( $this, 'uwhr_options_page_callback' )
        );
    }

    /**
     * Transients Setting Call
     *
     * Populates page with _transient_uwhr's, provides delete, button and delete
     * transients when user clicks and has requisite permissions
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @global $UWHR Object
     */
    function uwhr_options_page_callback() {
        global $UWHR;

        if ( ! current_user_can( $UWHR->get_admin_cap() ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        global $wpdb;

        $table = $wpdb->prefix . ( ($wpdb->siteid != 1) ? $wpdb->siteid . '_' : '' ) . 'options';

        $uwhr_transients = $wpdb->get_results(
            "SELECT option_name AS name, option_value AS value FROM $table
            WHERE option_name LIKE '_transient_uwhr_%'"
        );

        ?>
            <div class="wrap">
                <h2>Transients</h2>

                <form action="tools.php?page=uwhr-transients" method="POST">
                    <input type="hidden" name="delete_transients" value="yes">
                    <h3>Deleting Transients</h3>

                    <?php

                    if ( $uwhr_transients AND ! isset($_POST['delete_transients']) ) {
                        echo '<p>Click this button to clear out the transients created in the theme.</p>';
                        echo '<p>This includes:</p>';

                        echo '<ul>';

                            foreach( $uwhr_transients as $transient ) {
                                echo '<li><code>' . $transient->name . '</code></li>';
                            }

                        echo '<ul>';

                        echo '<p>Please know what you are doing before doing this. You cannot undo it.</p>';

                        submit_button( 'Delete Transients', 'delete button-primary' );

                    } else {
                        echo '<p>There\'s no current transients to delete.</p>';
                    }
                    ?>

                </form>
            </div>
        <?php

        if ( isset($_POST['delete_transients']) AND $_POST['delete_transients'] == 'yes' ) {

            echo '<div class="updated">';
                echo '<p>Transients have been deleted</p>';
            echo '</div>';

            foreach( $uwhr_transients as $transient ) {
                $delete = str_replace('_transient_','',$transient->name );
                delete_transient( $delete );
            }

            do_action( 'uwhr_delete_transients' );
            uwhr_flush_page_cache_by_url('/',1);
        }
    }

    /**
     * User Contact Page
     *
     * A simple dashboard widget to display contact information of other users.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 1.0.6
     * @package UWHR
     */
    function users_contacts_dashboard_widget() {
        wp_add_dashboard_widget(
            'users_contacts_dashboard_widget',
            'Quick Contact',
            array( $this, 'users_contacts_dashboard_widget_cb' )
        );
    }

    /**
     * User Contacts Callback
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 1.0.6
     * @package UWHR
     */
    function users_contacts_dashboard_widget_cb() {
        $args = array(
            'role'         => 'publisher',
            'orderby'      => 'display_name',
        );
        $publishers = get_users( $args );

        echo '<h3><span class="dashicons dashicons-admin-users"></span> Publisher</h3>';

        if ( ! empty($publishers) ) {
            foreach ( $publishers as $publisher ) {
                echo '<p><a href="mailto:'.$publisher->user_email.'">' . $publisher->display_name . '</a></p>';
            }
        } else {
            echo '<p><em>You don\'t have any registered publishers on your site.</em></p>';
        }

        $args = array(
            'role'         => 'revisor',
            'orderby'      => 'display_name',
        );
        $revisors = get_users( $args );

        echo '<h3><span class="dashicons dashicons-admin-users"></span> Revisors</h3>';

        if ( ! empty($revisors) ) {
            foreach ( $revisors as $revisor ) {
                echo '<p><a href="mailto:'.$revisor->user_email.'">' . $revisor->display_name . '</a></p>';
            }
        } else {
            echo '<p><em>You don\'t have any registered revisors on your site.</em></p>';
        }

    }
}
