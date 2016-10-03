<?php

/**
 * Sidebar
 *
 * Register sidebar and deactivate some unnecessary widgets.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

class UWHR_Sidebar {

    const NAME          = 'Sidebar';
    const ID            = 'sidebar';
    const DESCRIPTION   = 'An optional right sidebar for pages for widgets and a custom WYSIWYG editor';
    const BEFORE_WIDGET = '<div id="%1$s" class="widget %2$s">';
    const AFTER_WIDGET  = '</div>';
    const BEFORE_TITLE  = '<h4>';
    const AFTER_TITLE   = '</h4>';

    /**
     * Sidebar Constructor
     *
     * WordPress action hook to register sidebar and other widget actions
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
    function __construct() {
        add_action( 'widgets_init', array( $this, 'register_sidebar' ) );
        add_action( 'widgets_init', array( $this, 'remove_some_default_widgets' ) );
    }

    /**
     * Register Sidebar
     *
     * Registers sidebar for the sites
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
    function register_sidebar() {
        register_sidebar(array(
            'name'          => self::NAME,
            'id'            => self::ID,
            'description'   => self::DESCRIPTION,
            'before_widget' => self::BEFORE_WIDGET,
            'after_widget'  => self::AFTER_WIDGET,
            'before_title'  => self::BEFORE_TITLE,
            'after_title'   => self::AFTER_TITLE
        ));
    }

    /**
     * Remove Default Widgets
     *
     * We don't need some of the default WordPres Widgets
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
    function remove_some_default_widgets() {
        unregister_widget('WP_Widget_Pages');
        unregister_widget('WP_Widget_Calendar');
        unregister_widget('WP_Widget_Archives');
        unregister_widget('WP_Widget_Links');
        unregister_widget('WP_Widget_Meta');
        unregister_widget('WP_Widget_Search');
        unregister_widget('WP_Widget_Categories');
        unregister_widget('WP_Widget_Recent_Posts');
        unregister_widget('WP_Widget_Recent_Comments');
        unregister_widget('WP_Widget_RSS');
        unregister_widget('WP_Widget_Tag_Cloud');
        unregister_widget('WP_Nav_Menu_Widget');
    }
}
