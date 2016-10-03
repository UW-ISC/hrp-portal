<?php

/**
 * Unit Settings
 *
 * Creates a settings page and registers some settings for each unit site.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 0.6.0
 * @package UWHR
 */

class UWHR_Unit_Settings {

    /**
     * Constructor
     *
     * Adds actions to the WP admin hooks to register a menu pages, settings, and forms
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     */
    function __construct() {
        add_action( 'admin_menu', array( $this, 'add_page' ) );
        add_action( 'admin_bar_menu', array( $this, 'add_toolbar_items' ), 100);
        add_action( 'admin_init', array( $this, 'settings_init' ) );
    }

    /**
     * Add Toolbar Help Button
     *
     * Creates a help button on the admin toolbar for all users
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 1.0.0
     * @package UWHR
     */
    function add_toolbar_items( $wp_admin_bar ) {
        $args = array(
            'id'        => 'get_help',
            'title'     => 'Get Help',
            'href'      => admin_url( 'admin.php?page=help' ),
            'meta'      => array( 'class' => 'get-help-icon' )
        );
        $wp_admin_bar->add_node( $args );
    }

    /**
     * Add Settings Page and Get Help Page
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     */
    function add_page() {
        add_menu_page(
            'Unit Settings',
            'Unit Settings',
            'manage_options',
            'unit_settings',
            array( $this, 'create_unit_settings_page_callback' ),
            'dashicons-admin-home',
            59
        );

        add_submenu_page(
            'unit_settings',
            'Get Help',
            'Get Help',
            'read',
            'help',
            array($this, 'get_help_page_callback')
        );
    }

    /**
     * Create Unit Settings Page
     *
     * The callback function for creating the settings page
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     */
    function create_unit_settings_page_callback() {
        ?>
            <div class="wrap">
                <h2>Unit Settings</h2>

                <form method="post" action="options.php">
                    <?php
                        settings_fields( 'unitSettings' );
                        do_settings_sections( 'unitSettings' );
                        submit_button();
                    ?>
                </form>
            </div>
        <?php
    }

    /**
     * Settings
     *
     * Registers the actual settings used. These are stored in the database and accessible
     * with get_option( 'unit_settings' ).
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     */
    function settings_init() {
        register_setting( 'unitSettings', 'unit_settings' );

        add_settings_section(
            'unit_meta_section',
            'Unit Meta',
            '',
            'unitSettings'
        );

        add_settings_field(
            'unit_name',
            'Unit Name',
            array( $this, 'unit_name_cb' ),
            'unitSettings',
            'unit_meta_section'
        );

        add_settings_field(
            'unit_description',
            'Unit Description',
            array( $this, 'unit_description_cb' ),
            'unitSettings',
            'unit_meta_section'
        );

        add_settings_field(
            'unit_id',
            'Unit ID',
            array( $this, 'unit_id_cb' ),
            'unitSettings',
            'unit_meta_section'
        );

        add_settings_section(
            'unit_extra_section',
            'Unit Extras',
            '',
            'unitSettings'
        );

        add_settings_field(
            'unit_calendar',
            'Calendar',
            array( $this, 'calendar_cb' ),
            'unitSettings',
            'unit_extra_section'
        );

        add_settings_section(
            'unit_web_help_section',
            'Get Help',
            '',
            'unitSettings'
        );

        add_settings_field(
            'unit_web_help_general_email',
            'Get Help Email',
            array( $this, 'web_help_general_email_cb' ),
            'unitSettings',
            'unit_web_help_section'
        );

        add_settings_field(
            'unit_web_help_forms_email',
            'Forms Help Email',
            array( $this, 'web_help_forms_email_cb' ),
            'unitSettings',
            'unit_web_help_section'
        );

        add_settings_section(
            'unit_notify_reviewers_section',
            'Notify Reviewers',
            '',
            'unitSettings'
        );

        add_settings_field(
            'unit_notify_reviewers_toggle',
            'Notify Reviewers?',
            array( $this, 'notify_reviewers_on_publish_toggle_cb' ),
            'unitSettings',
            'unit_notify_reviewers_section'
        );

        add_settings_field(
            'unit_notify_reviewers_email',
            'Reviewer Email',
            array( $this, 'notify_reviewers_email_cb' ),
            'unitSettings',
            'unit_notify_reviewers_section'
        );

        do_action( 'uwhr_add_unit_settings' );
    }

    /**
     * Settings callback
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     */
    function unit_name_cb() {
        echo '<input type="text" class="regular-text" value="' . get_bloginfo('name') . '" disabled>';
        echo '<p class="description">Change on the <a href="'. get_admin_url( get_current_blog_id(), 'options-general.php') .'">General Settings</a> page.</p>';
    }

    /**
     * Settings callback
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     */
    function unit_description_cb() {
        echo '<input type="text" class="regular-text" value="' . get_bloginfo('description') . '" disabled>';
        echo '<p class="description">Change on the <a href="'. get_admin_url( get_current_blog_id(), 'options-general.php') .'">General Settings</a> page.</p>';
    }

    /**
     * Settings callback
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     */
    function unit_id_cb() {
        echo get_current_blog_id() ;
        echo '<p class="description">Cannot be changed.</p>';
    }

    /**
     * Settings callback
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     */
    function calendar_cb() {
        $option = ( isset( get_option( 'unit_settings' )['calendar'] ) ) ? get_option( 'unit_settings' )['calendar'] : '';
        echo '<input type="text" class="regular-text" name="unit_settings[calendar]" value="' . $option . '">';
        echo '<p class="description">Enter a trumba calendar slug. Ex: <em>sea_benefits</em></p>';
    }

    /**
     * Settings callback
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 1.0.6
     * @package UWHR
     */
    function web_help_general_email_cb() {
        $option = ( isset( get_option( 'unit_settings' )['web_help_general_email'] ) ) ? get_option( 'unit_settings' )['web_help_general_email'] : '';
        echo '<input type="email" class="regular-text" name="unit_settings[web_help_general_email]" value="' . $option . '" placeholder="uwhrweb@uw.edu">';
        echo '<p class="description">Users will send email help via the Get Help page. Defaults to uwhrweb@uw.edu.</p>';
    }

    /**
     * Settings callback
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 1.0.6
     * @package UWHR
     */
    function web_help_forms_email_cb() {
        $option = ( isset( get_option( 'unit_settings' )['web_help_forms_email'] ) ) ? get_option( 'unit_settings' )['web_help_forms_email'] : '';
        echo '<input type="email" class="regular-text" name="unit_settings[web_help_forms_email]" value="' . $option . '" placeholder="uwhrweb@uw.edu">';
        echo '<p class="description">Users will send email help via the Forms Instructions page. Defaults to uwhrweb@uw.edu.</p>';
    }

    /**
     * Settings callback
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 1.1.0
     * @package UWHR
     */
    function notify_reviewers_on_publish_toggle_cb() {
        $option = ( isset( get_option( 'unit_settings' )['notify_reviewers_toggle'] ) ) ? 1 : '';
        $checked = $option == 1 ? ' checked="checked"' : '';
        echo '<label for="notifyReviewersOnPublishToggle"><input type="checkbox" name="unit_settings[notify_reviewers_toggle]" value="1" id="notifyReviewersOnPublishToggle" ' . $checked . '> Notify reviewer on save</label>';
    }

    /**
     * Settings callback
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 1.1.0
     * @package UWHR
     */
    function notify_reviewers_email_cb() {
        $option = ( isset( get_option( 'unit_settings' )['notify_reviewers_email'] ) ) ? get_option( 'unit_settings' )['notify_reviewers_email'] : 'uwhred@uw.edu';
        echo '<input type="email" class="regular-text" name="unit_settings[notify_reviewers_email]" value="' . $option . '" placeholder="uwhred@uw.edu">';
    }

    /**
     * Get Help Page
     *
     * The actual page that users can navigate to. The page is a form with a couple fields
     * and Posts back to itself to send an email.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 1.0.0
     * @package UWHR
     */
    function get_help_page_callback() { ?>
        <div class="wrap">
            <h2>Get Help</h2>

            <p>Need help updating your unit's website? Fill out this form to send us an email. Want to talk through an idea before you get started? Call Kimberly Mishra, executive director for HR Marketing, Communications, & Engagement, at 206-685-3845.</p>

            <?php
                if ( isset($_POST['send_email']) AND 'sending' === $_POST['send_email'] ) {
                    $topic = isset($_POST['help_topic']) ? $_POST['help_topic'] : '';
                    $page = isset($_POST['help_page']) ? $_POST['help_page'] : '';
                    $message = isset($_POST['help_message']) ? $_POST['help_message'] : '';

                    $current_user = wp_get_current_user();
                    $user_login = $current_user->user_login;
                    $user_email = $current_user->user_email;
                    $display_name = $current_user->display_name;

                    $message_meta = 'Topic: ' . $topic . '<br />Page: ' . $page . '<br />Submitted by: ' . $user_login . '<br />Email: ' . $user_email . '<br />';

                    $help_general_email = ( !empty( get_option( 'unit_settings' )['web_help_general_email'] ) ) ? get_option( 'unit_settings' )['web_help_general_email'] : 'uwhrweb@uw.edu';
                    $site_name = get_bloginfo('name');

                    $to = $help_general_email;
                    $subject = 'Web Help Request - ' . $site_name;
                    $body = $message_meta . $message;
                    $headers[] = 'Content-Type: text/html; charset=UTF-8';
                    $headers[] = 'From: '.$display_name.' <'.$user_email.'>';
                    $headers[] = 'Cc: '.$display_name.' <'.$user_email.'>';

                    $success = wp_mail( $to, $subject , apply_filters( 'the_content', $body), $headers );

                    if ( $success ) {
                        echo '<div class="updated">';
                            echo '<p>Your email was successfully sent. We\'ve also sent you copy.</p>';
                        echo '</div>';
                    }
                }
            ?>

            <form action="<?php echo admin_url('admin.php?page=help'); ?>" method="POST">

                <input type="hidden"  name="send_email" value="sending">

                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="help_topic">Select a topic</label></th>
                            <td>
                                <?php
                                    $topics = array(
                                        'General Questions',
                                        'Editorial Review',
                                        'Feature Request - Accordion',
                                        'Feature Request - Anchor Linking',
                                        'Feature Request - Tables',
                                        'Feature Request - Buttons',
                                        'Feature Request - Custom JavaScript (map, charts, etc)'
                                    );
                                ?>
                                <select name="help_topic" id="help_topic">
                                    <?php foreach ( $topics as $topic ) { ?>
                                        <option value="<?php echo $topic ?>"><?php echo $topic; ?></option>
                                    <?php } ?>
                                </select>
                                <p class="description">Select a topic you need help with or a request you have.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="help_page">Select a page</label></th>
                            <td>
                                <?php $pages = get_pages(); ?>

                                <select name="help_page" id="help_page">
                                    <option value="select_a_page">— Select a Page —</option>
                                    <?php foreach ( $pages as $page ) { ?>
                                        <option value="<?php echo $page->post_title ?>"><?php echo $page->post_title; ?></option>
                                    <?php } ?>
                                </select>
                                <p class="description">Select a page that you want addressed. (optional)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="help_message">Type your message</label></th>
                            <td>
                                <?php
                                $settings = array(
                                    'wpautop' => false,
                                    'media_buttons' => false,
                                    'teeny' => true,
                                    'textarea_rows' => 8
                                );
                                wp_editor( '', 'help_message', $settings ); ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"></th>
                            <td><?php submit_button( 'Send Message' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
        <?php
    }
}
