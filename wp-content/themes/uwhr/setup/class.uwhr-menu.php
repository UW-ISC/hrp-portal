<?php

/**
 * Menu
 *
 * Register menus that appear throughout the website.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.3.0
 * @package UWHR
 */

class UWHR_Menu {

    private $name;
    private $location;

    /**
     * Menu Constructor
     *
     * WordPress action hook to register menu and set private members for UWHR_Menu Object
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @param $name string The name of the menu
     * @param $location string The location slug of the menu
     * @param $main boolean Is the menu for the main site only
     */
    function __construct( $name, $location, $main = false ) {
        add_action( 'after_setup_theme', array( $this, 'register_menu') );

        $this->name = $name;
        $this->location = $location;
        $this->main = $main;
    }

    /**
     * Register Menu
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $UWHR Object
     */
    function register_menu() {
        global $UWHR;

        if ( ( get_current_blog_id() != $UWHR::MAIN_BLOG_ID AND ! $this->main ) OR
             ( get_current_blog_id() == $UWHR::MAIN_BLOG_ID AND   $this->main ) ) {
            register_nav_menu( $this->location, $this->name );
        }
    }

    /**
     * Get Menu Location
     *
     * Retuns the location slug
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @return $location string The slug location of the menu
     */
    public function get_location() {
        return $this->location;
    }
}
