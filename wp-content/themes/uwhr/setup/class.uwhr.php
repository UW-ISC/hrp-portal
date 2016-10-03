<?php
/**
 * UWHR Object
 *
 * Initializes all the class objects for back-end functionality across sites
 *
 * The UWHR object is created in the functions.php file. All site functionality is built
 * into these objects created under that global object.
 * Additional functionality is built out through the inc/template-functions.php & inc/helper-functions.php file.
 * All classes should be accessible through global $UWHR object and object accessor ->
 * Constants and static variables/methods are accessible with UWHR::
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 *
 * ----------------------------------------------------------------------------------------
 *
 * TABLE OF CONTENT
 *
 * Theme
 * ---------------------
 * Install:             setup basic theme, settings, configuration, login page
 * Admin:               alter WP admin functionality for unique UW experience
 * Unit Settings:       register a settings page and settings for each unit site
 * Author:              configure the TinyMCE editor for custom authoring experience, additional author features
 * Scripts:             enqueue scripts used in the WP site, both front- and back-end
 * Styles:              enqueue styles used in the WP site, both front- and back-end
 * Images:              register image sizes and set if visible in Media Uploader
 * Filters:             filter hooks for content changes, body classes, admin filters, etc
 * Users:               register new user roles/capabilities, customize WP user capabilities
 *                      configure metaboxes and edit-* pages
 *
 * Theme Components
 * ---------------------
 * Menus:               register menus and menu location
 * Post:                deregister default WP 'post' post type
 * Page:                register taxonomies, adjust admin post table
 * Form:                register form custom post type and add custom meta boxes
 * Sidebar:             register sidebar and deactivate many WP widgets
 * Search:              the business logic of search, creates Search UI and Search Options
 * Search UI:           template functions for search related ui components
 * Search Options:      registers a settings page and several options for search
 *
 * Theme Utilities
 * ---------------------
 * Dropdown Walker:     traverse tree data to render markup;
 *                      interactions handled in @see js/componenets/uwhr.dropdowns.js
 * Mobile Walker:       traverse tree data to render markup;
 *                      interactions handled in @see js/componenets/uwhr.mobile-menu.js
 * Site Walker:         traverse tree data to render markup;
 *                      interactions handled in @see js/componenets/uwhr.accordion-menu.js
 *
 * Widgets
 * ---------------------
 * Jetpack Visibility:  fork of Jetpack's Widget Visibility
 *                      @link https://jetpack.me/support/widget-visibility/
 *
 * Shortcodes
 * ---------------------
 * Shortcodes:          includes and instantiates shortcode definitions
 *
 * Functions
 * ---------------------
 * Template Functions:  various functions used throughout theme templates
 *                      @see inc/template-functions.php
 * Helper Functions:    helper functions used in various template functions or theme template files
 *                      @see inc/helper-functions.php
 */

class UWHR {

    /**
     * Main Blog ID
     *
     * A constant to identify the main blog by ID. Used in template functions and beyond!
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
    const MAIN_BLOG_ID = 1;

    /**
     * Role / Capability Definitions
     *
     * These are used to section off admin, structure, and content sections of the site.
     * Admins are default WP roles
     * Structure roles can manage pages, custom post types, and widgets
     * Content roles can edit content, submit revisions
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     */
    private $admin_roles = array( 'administrator' );
    private $structure_roles = array( 'administrator', 'editor' );
    private $publisher_role = 'publisher';
    private $content_roles = array( 'administrator', 'editor', 'publisher', 'revisor' );
    private $admin_cap = 'manage_options';
    private $edit_structure_cap = 'edit_structure';

    /**
     * UWHR Constructor
     *
     * Let's get things started!
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
    function __construct() {
        $this->include_files();
        $this->initialize();
    }

    /**
     * Includes
     *
     * Grab all the files
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
    private function include_files() {
        $d  = get_template_directory() . '/';
        $dir = $d . 'setup/';

        // Include Theme
        require_once($dir . 'class.uwhr-install.php');
        require_once($dir . 'class.uwhr-admin.php');
        require_once($dir . 'class.uwhr-unit-settings.php');
        require_once($dir . 'class.uwhr-author.php');
        require_once($dir . 'class.uwhr-scripts.php');
        require_once($dir . 'class.uwhr-styles.php');
        require_once($dir . 'class.uwhr-images.php');
        require_once($dir . 'class.uwhr-filters.php');
        require_once($dir . 'class.uwhr-users.php');

        // Include Theme Components
        require_once($dir . 'class.uwhr-menu.php');
        require_once($dir . 'class.uwhr-post-type-post.php');
        require_once($dir . 'class.uwhr-post-type-page.php');
        require_once($dir . 'class.uwhr-post-type-form.php');
        require_once($dir . 'class.uwhr-sidebar.php');
        require_once($dir . 'class.uwhr-search.php');
        require_once($dir . 'class.uwhr-search-ui.php');
        require_once($dir . 'class.uwhr-search-options.php');

        // Include Theme Utilities
        require_once($dir . 'class.uwhr-menu-walker-dropdowns.php');
        require_once($dir . 'class.uwhr-menu-walker-dropdowns-mobile.php');
        require_once($dir . 'class.uwhr-menu-walker-site.php');

        // Include Widgets
        foreach ( glob( $dir . 'widgets/*.php') as $filename ) {
            include $filename;
        }

        // Include Shortcodes
        require_once($dir . 'shortcodes/class.uwhr-shortcodes.php');

        // Include Theme Template Functions
        require_once($d . 'inc/template-functions.php' );

        // Include Theme Helper Functions
        require_once($d . 'inc/helper-functions.php' );
    }

    /**
     * Initialize
     *
     * Initialize all the parts
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
    private function initialize() {

        // Initialize Theme
        $this->Install                  = new UWHR_Install;
        $this->Admin                    = new UWHR_Admin;
        $this->Settings                 = new UWHR_Unit_Settings;
        $this->Author                   = new UWHR_Author;
        $this->Scripts                  = new UWHR_Scripts;
        $this->Styles                   = new UWHR_Styles;
        $this->Images                   = new UWHR_Images;
        $this->Filters                  = new UWHR_Filters;
        $this->Users                    = new UWHR_Users;

        // Initialize Components
        $this->Dropdowns_Menu           = new UWHR_Menu( 'White Bar', 'white-bar', true );
        $this->Global_Menu              = new UWHR_Menu( 'Global Menu', 'global-menu', true );
        $this->Footer_Quick_Links_Menu  = new UWHR_Menu( 'Footer Quick Links', 'footer-quick-links', true );
        $this->Unit_Menu                = new UWHR_Menu( 'Unit Menu', 'unit-menu' );
        $this->Post                     = new UWHR_Post;
        $this->Page                     = new UWHR_Page;
        $this->Form                     = new UWHR_Form;

        $this->Sidebar                  = new UWHR_Sidebar;
        $this->Search                   = new UWHR_Search;

        // Initialize Shortcodes
        $this->Shortcodes               = new UWHR_Shortcodes;
    }

    /**
     * Admin Roles
     *
     * Grab the admin role definitions
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @return $admin_roles array A list of admin roles
     */
    public function get_admin_roles() {
        return $this->admin_roles;
    }

    /**
     * Structure Roles
     *
     * Grab the structure role definitions
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @return $structure_roles array A list of structure roles
     */
    public function get_structure_roles() {
        return $this->structure_roles;
    }

    /**
     * Publisher Role
     *
     * Grab the publisher role
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @return $publisher_role string The publisher role
     */
    public function get_publisher_role() {
        return $this->publisher_role;
    }

    /**
     * Content Roles
     *
     * Grab the content role definitions
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @return $content_roles array A list of content roles
     */
    public function get_content_roles() {
        return $this->content_roles;
    }

    /**
     * Admin Capability
     *
     * Grab the admin capability
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @return $admin_cap string The admin capability
     */
    public function get_admin_cap() {
        return $this->admin_cap;
    }

    /**
     * Structure Capability
     *
     * Grab the structure capability
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @return $edit_structure_cap string A structure capability
     */
    public function get_edit_structure_cap() {
        return $this->edit_structure_cap;
    }
}
