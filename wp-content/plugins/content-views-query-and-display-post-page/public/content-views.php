<?php

/**
 * Content Views for Public
 *
 * @package   PT_Content_Views
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
class PT_Content_Views {

	/**
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = PT_CV_DOMAIN;
	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		// Load plugin text domain
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ), 11 );

		add_action( 'init', array( 'CV_Session', 'start' ) );

		// Register content
		add_action( 'init', array( $this, 'content_register' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Update view count of post
		add_action( 'wp_head', array( &$this, 'head_actions' ) );

		// Output assets content at footer of page
		add_action( 'wp_footer', array( 'PT_CV_Html', 'assets_of_view_types' ), 100 );

		// Load assets if they are not enqueued to 'wp_enqueue_scripts'
		add_action( 'wp_footer', array( $this, 'enqueue_assets' ), 2 );

		// Ajax action
		$action = 'pagination_request';
		add_action( 'wp_ajax_' . $action, array( 'PT_CV_Functions', 'ajax_callback_' . $action ) );
		add_action( 'wp_ajax_nopriv_' . $action, array( 'PT_CV_Functions', 'ajax_callback_' . $action ) );

		// Custom hooks for both preview & frontend
		PT_CV_Hooks::init();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide       True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();
			} else {
				self::single_activate();
			}
		} else {
			self::single_activate();
		}
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide       True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();
				}

				restore_current_blog();
			} else {
				self::single_deactivate();
			}
		} else {
			self::single_deactivate();
		}
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int $blog_id ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();
	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	public static function get_blog_ids() {

		global $wpdb;

		// Get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	public static function single_activate() {
		update_option( PT_CV_OPTION_VERSION, PT_CV_VERSION );
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	public static function single_deactivate() {
		delete_option( PT_CV_OPTION_VERSION );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		/* In v1.6.8.3, textdomain changed from "content-views" to "content-views-query-and-display-post-page"
		 */

		// WPLANG is no longer needed since 4.0
		$locale = get_locale();

		$old_lang_file	 = WP_LANG_DIR . "/content-views/content-views-{$locale}.mo";
		$lang_pack		 = WP_LANG_DIR . "/plugins/content-views-query-and-display-post-page-{$locale}.mo";
		$plugin_lang_dir = dirname( plugin_basename( PT_CV_FILE ) ) . '/languages/';

		if ( file_exists( $old_lang_file ) ) {
			load_textdomain( 'content-views-query-and-display-post-page', $old_lang_file );
		} elseif ( file_exists( $lang_pack ) ) {
			load_textdomain( 'content-views-query-and-display-post-page', $lang_pack );
		} else {
			load_plugin_textdomain( 'content-views-query-and-display-post-page', FALSE, $plugin_lang_dir );
		}
	}

	/**
	 * Content register: Create custom post type
	 */
	public function content_register() {

		/**
		 * Register custom post type : View
		 */
		$labels = array(
			'name'				 => _x( 'Views', 'post type general name', 'content-views-query-and-display-post-page' ),
			'singular_name'		 => _x( 'View', 'post type singular name', 'content-views-query-and-display-post-page' ),
			'menu_name'			 => _x( 'Views', 'admin menu', 'content-views-query-and-display-post-page' ),
			'name_admin_bar'	 => _x( 'View', 'add new on admin bar', 'content-views-query-and-display-post-page' ),
			'add_new'			 => _x( 'Add New', 'post' ),
			'add_new_item'		 => __( 'Add New View', 'content-views-query-and-display-post-page' ),
			'new_item'			 => __( 'New View', 'content-views-query-and-display-post-page' ),
			'edit_item'			 => __( 'Edit View', 'content-views-query-and-display-post-page' ),
			'view_item'			 => __( 'View View', 'content-views-query-and-display-post-page' ),
			'all_items'			 => __( 'All Views', 'content-views-query-and-display-post-page' ),
			'search_items'		 => __( 'Search Views', 'content-views-query-and-display-post-page' ),
			'parent_item_colon'	 => __( 'Parent Views:', 'content-views-query-and-display-post-page' ),
			'not_found'			 => __( 'No views found.', 'content-views-query-and-display-post-page' ),
			'not_found_in_trash' => __( 'No views found in Trash.', 'content-views-query-and-display-post-page' ),
		);

		$args = array(
			'labels'			 => $labels,
			'public'			 => false,
			// Hide in menu, but can see All Views page
			'show_ui'			 => true, // set "true" to fix "Invalid post type" error
			'show_in_menu'		 => false,
			'query_var'			 => true,
			'rewrite'			 => array( 'slug' => PT_CV_POST_TYPE ),
			'capability_type'	 => 'post',
			'has_archive'		 => true,
			'hierarchical'		 => false,
			'menu_position'		 => null,
			'supports'			 => array( 'title', 'author', 'custom-fields' ),
		);

		register_post_type( PT_CV_POST_TYPE, $args );

		/**
		 * Add shortcode
		 */
		add_shortcode( PT_CV_POST_TYPE, array( 'PT_CV_Functions', 'view_output' ) );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		if ( apply_filters( PT_CV_PREFIX_ . 'default_enqueue_assets', 1 ) ) {
			PT_CV_Html::frontend_styles();
		}
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if ( apply_filters( PT_CV_PREFIX_ . 'default_enqueue_assets', 1 ) ) {
			PT_CV_Html::frontend_scripts();
		}
	}

	public function enqueue_assets() {
		// Execute if assets were not enqueued in default way
		if ( !apply_filters( PT_CV_PREFIX_ . 'default_enqueue_assets', 1 ) ) {
			global $pt_cv_id;
			if ( !empty( $pt_cv_id ) || apply_filters( PT_CV_PREFIX_ . 'view_executed', 0 ) ) {
				PT_CV_Html::frontend_styles();
				PT_CV_Html::frontend_scripts();
			}
		}

		do_action( PT_CV_PREFIX_ . 'enqueue_assets' );
	}

	/**
	 * Custom actions at head
	 */
	public function head_actions() {
		// Initialize global variables
		global $pt_cv_glb, $pt_cv_views, $pt_cv_id;
		$pt_cv_glb	 = array();
		$pt_cv_views = array();
		$pt_cv_id	 = 0;
	}

}
