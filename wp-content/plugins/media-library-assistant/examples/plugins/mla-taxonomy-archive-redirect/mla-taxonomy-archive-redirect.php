<?php
/**
 * Detect a "Taxonomy Archive Page" URL and redirect to a specified page with 
 * [mla_gallery] parameters
 *
 * You can find more information about using this plugin in the Documentation tab
 * on the Settings page.
 * 
 * Created for support topic "archive templates and dynamic galleries"
 * opened on 3/25/2025 by "rornatus"
 * https://wordpress.org/support/topic/archive-templates-and-dynamic-galleries/
 * 
 * @package MLA Taxonomy Archive Redirect
 * @version 1.00
 */

/*
Plugin Name: MLA Taxonomy Archive Redirect
Plugin URI: http://davidlingren.com/
Description: Detect a "Taxonomy Archive Page" URL and redirect to a specified page with [mla_gallery] parameters
Author: David Lingren
Version: 1.00
Author URI: http://davidlingren.com/

Copyright 2025 David Lingren

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You can get a copy of the GNU General Public License by writing to the
	Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110, USA
*/

/**
 * Class MLA Taxonomy Archive Redirect detects taxonomy archive page requests and redirects them
 *
 * @package MLA Taxonomy Archive Redirect
 * @since 1.00
 */
class MLATaxonomyArchiveRedirect {
	/**
	 * Current version number
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const PLUGIN_VERSION = '1.00';

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets, scripts and settings
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'MLATaxonomyArchiveRedirect';

	/**
	 * Constant to log this plugin's debug activity
	 *
	 * @since 1.00
	 *
	 * @var	integer
	 */
	const MLA_DEBUG_CATEGORY = 0x00008000;

	/**
	 * Settings Management object
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $plugin_settings = NULL;

	/**
	 * In-memory representation of the option settings
	 *
	 * @since 1.00
	 *
	 * @var array $_settings {
	 *     @see $_default_settings
	 *     }
	 */
	private static $_settings = NULL;

	/**
	 * Configuration values for the Settings Management object
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $settings_arguments = array(
				'slug_prefix' => self::SLUG_PREFIX,
				'plugin_title' => 'MLA Taxonomy Archive Redirect',
				'menu_title' => 'MLA Taxonomy Redirect',
				'plugin_file_name_only' => 'mla-taxonomy-archive-redirect',
				'plugin_version' => self::PLUGIN_VERSION,
				'template_file' => '/admin-settings-page.tpl', // Add the path at runtime, in initialize()
				'messages' => '',
				'options' => array(
					// 'slug' => array( 'type' => 'checkbox|text|select|textarea', 'default' => 'text|boolean(0|1)' )
					// See the default values in $_default_settings below
					'archive_page' => array( 'type' => 'text', 'default' => '', ),
					// dynamic settings added at runtime in the initialize() function
				),
				'general_tab_values' => array(
					'site_url' => '',
					'site_url_size' => '30',
				), // additional page_values for 'page-level-options' template
				'documentation_tab_values' => array(
					'plugin_title' => 'MLA Taxonomy Archive Redirect',
					'settingsURL' => '', // Set at runtime in initialize()
				), // page_values for 'documentation-tab' template
			);

	/**
	 * Default processing options
	 *
	 * @since 1.00
	 *
	 * @var array $_default_settings {
	 *     @type boolean $checkbox_slug Checkbox description
	 *     @type string  $text_slug Text field description
	 *     @type string  $static_select_slug Static dropdown control description
	 *     @type string  $textarea_slug Textarea description
	 *     }
	 */
	private static $_default_settings = array (
					'archive_page' => '',
					// dynamic settings added at runtime in the initialize() function
					);

	/**
	 * Template file for the Settings page(s) and parts
	 *
	 * This array contains all of the template parts for the Settings page(s). The array is built once
	 * each page load and cached for subsequent use.
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	public static $page_template_array = NULL;

	/**
	 * Name the shortcode
	 */
	const SHORTCODE_NAME = 'mla_download_checklist';

	/**
	 * Initialization function, similar to __construct()
	 *
	 * Adds the 'mla_featured_field' shortcode to WordPress
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		// This plugin requires MLA
		if ( ! class_exists( 'MLACore', false ) ) {
			return;
		}

		$uri = 'v' . self::PLUGIN_VERSION . isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		MLACore::mla_debug_add( __LINE__ . " MLATaxonomyArchiveRedirect::initialize( {$uri} ) \$_REQUEST = " . var_export( $_REQUEST, true ), self::MLA_DEBUG_CATEGORY );

		// The plugin settings class is shared with other MLA example plugins
		if ( ! class_exists( 'MLAExamplePluginSettings102' ) ) {
			require_once( pathinfo( __FILE__, PATHINFO_DIRNAME ) . '/class-mla-example-plugin-settings-102.php' );
		}

		// Add the run-time values to the arguments
		self::$settings_arguments['template_file'] = dirname( __FILE__ ) . self::$settings_arguments['template_file'];
		self::$settings_arguments['documentation_tab_values']['settingsURL'] = admin_url('options-general.php');

		// Add taxonomy-specific options
		$supported_taxonomies = MLACore::mla_supported_taxonomies('support');
		MLACore::mla_debug_add( __LINE__ . " MLATaxonomyArchiveRedirect::initialize \$supported_taxonomies = " . var_export( $supported_taxonomies, true ), self::MLA_DEBUG_CATEGORY );

		// Update the plugin options from the $_REQUEST array, wp_options table or defaults
		$current_settings = array();
		if ( !empty( $_REQUEST[ self::$settings_arguments['slug_prefix'] . '_options_save'] ) ) {
			if ( isset( $_REQUEST[ self::$settings_arguments['slug_prefix'] . '_options' ] ) ) {
				$current_settings = wp_unslash(  $_REQUEST[ self::$settings_arguments['slug_prefix'] . '_options' ] );
			}
		} elseif ( !empty( $_REQUEST[ self::$settings_arguments['slug_prefix'] . '_options_reset'] ) ) {
			$current_settings = array();
		} else {
			$current_settings = get_option( self::$settings_arguments['slug_prefix'] . '-settings' );
			if ( !is_array( $current_settings ) ) {
				$current_settings = array();
			}
		}
		MLACore::mla_debug_add( __LINE__ . " MLATaxonomyArchiveRedirect::initialize \$current_settings = " . var_export( $current_settings, true ), self::MLA_DEBUG_CATEGORY );

		$current_values = array();
		foreach ( $supported_taxonomies as $taxonomy ) {
			// Look for an existing setting
			if ( isset( $current_settings[ $taxonomy ] ) ) {
				$current_value = $current_settings[ $taxonomy ];
			} else {
				$current_value = '';
			}

			self::$settings_arguments['options'][ $taxonomy ] = array( 'type' => 'text', 'default' => '', );
			self::$_default_settings[ $taxonomy ] = '';
			$current_values[ $taxonomy ] = $current_value;
		}
		MLACore::mla_debug_add( __LINE__ . " MLATaxonomyArchiveRedirect::initialize current_values = " . var_export( $current_values, true ), self::MLA_DEBUG_CATEGORY );

		// Create our own settings object
		self::$plugin_settings = new MLAExamplePluginSettings102( self::$settings_arguments );

		add_action( 'parse_query', 'MLATaxonomyArchiveRedirect::mla_parse_query_action' );		

		// The remaining filters are only useful in the admin section; exit if in the "front-end" posts/pages. 
		if ( ! is_admin() ) {
			return;
		}

		// Add the run-time values to the settings
		// Load template array and initialize page-level values.
		self::$page_template_array = MLACore::mla_load_template( self::$settings_arguments['template_file'], 'path' );

		$general_tab_values = self::$plugin_settings->get_plugin_argument('general_tab_values');
		$general_tab_values['site_url'] = site_url();
		$general_tab_values['site_url_size'] = strlen( $general_tab_values['site_url'] ) - 5;

		// Compose dynamic option rows
		$page_template_array = MLACore::mla_load_template( self::$settings_arguments['template_file'], 'path' );
		$taxonomy_rows = '';
		$row_values = array( 'site_url' => $general_tab_values['site_url'] );
		foreach ( $supported_taxonomies as $taxonomy ) {
			$row_values['taxonomy'] = $taxonomy;
			$row_values['archive_page'] = $current_values[ $taxonomy ];
			$taxonomy_rows .= MLAData::mla_parse_template( $page_template_array['taxonomy-specific-row'], $row_values );
		}

		$general_tab_values['taxonomy_rows'] = $taxonomy_rows;

		self::$plugin_settings->update_plugin_argument('general_tab_values', $general_tab_values );
		MLACore::mla_debug_add( __LINE__ . " MLATaxonomyArchiveRedirect::initialize \$general_tab_values = " . var_export( $general_tab_values, true ), self::MLA_DEBUG_CATEGORY );
	} // initialize

	/**
	 * Fires after the main query vars have been parsed.
	 *
	 * @since 1.00
	 *
	 * @param WP_Query $query The WP_Query instance (passed by reference).
	 */
	public static function mla_parse_query_action( &$query ) {
		// Filter out [mla_gallery]
		if ( isset( $query->query_vars['post_type'] ) && ('mladisabletaxjoin' === $query->query_vars['post_type'] ) ) {
			return;
		}

		if ( $query->is_archive ) {

			// Parse out URL taxonomy archive request
			$request = array();
			if ( isset( $_SERVER['REQUEST_URI'] ) ) {
				$request = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
				$request = explode( '/', trim( $request, '/' ) );
				if ( 2 === count( $request ) ) {
					$request = array( 'taxonomy' => $request[0], 'term' => $request[1] );
				}
				MLACore::mla_debug_add( __LINE__ . " MLATaxonomyArchiveRedirect::mla_parse_query_action request = " . var_export( $request, true ), self::MLA_DEBUG_CATEGORY );
			}

			// Parse out query args
			$args = array();
			if ( $query->is_category ) {
				$args['taxonomy'] = 'category';
				$args['term'] = $query->query_vars['category_name'];
			} elseif ( $query->is_tag ) {
				$args['taxonomy'] = 'post_tag';
				$args['term'] = $query->query_vars['tag'];
			} else {
				foreach( get_object_taxonomies( 'attachment', 'objects' ) as $taxonomy ) {
					if ( MLACore::mla_taxonomy_support( $taxonomy->name, 'support' ) ) {
						if ( isset( $query->query_vars[ $taxonomy->name ] ) ) {
							$args['taxonomy'] = $taxonomy->name;
							$args['term'] = $query->query_vars[ $taxonomy->name ];
							break;
						}
					}
				} // foreach
			}
			MLACore::mla_debug_add( __LINE__ . " MLATaxonomyArchiveRedirect::mla_parse_query_action args = " . var_export( $args, true ), self::MLA_DEBUG_CATEGORY );

			// If it's a valid candidate, process the resulta
			if ( ! empty( $args ) ) {
				if ( $request === $args ) {
					if ( false === in_array( $args['taxonomy'], MLACore::mla_supported_taxonomies('support') ) ) {
						return;
					}
					
					add_filter( 'posts_results', 'MLATaxonomyArchiveRedirect::mla_posts_results_filter', 10, 2 );		
				}
			}
		}
	} // mla_parse_query_action

	/**
	 * Filters the raw post results array, prior to status checks.
	 *
	 * @since 1.00
	 *
	 * @param WP_Post[] $posts Array of post objects.
	 * @param WP_Query  $query The WP_Query instance (passed by reference).
	 */
	public static function mla_posts_results_filter( $posts, $query ) {
		if ( $query->is_archive ) {

			// We only handle empty results
			if ( 0 < $query->found_posts ) {
				return $posts;
			}

			$args = array();
			if ( $query->is_category ) {
				$args['taxonomy'] = 'category';
				$args['term'] = $query->query_vars['category_name'];
			} elseif ( $query->is_tag ) {
				$args['taxonomy'] = 'post_tag';
				$args['term'] = $query->query_vars['tag'];
			} else {
				foreach( get_object_taxonomies( 'attachment', 'objects' ) as $taxonomy ) {
					if ( MLACore::mla_taxonomy_support( $taxonomy->name, 'support' ) ) {
						if ( isset( $query->query_vars[ $taxonomy->name ] ) ) {
							$args['taxonomy'] = $taxonomy->name;
							$args['term'] = $query->query_vars[ $taxonomy->name ];
							break;
						}
					}
				} // foreach
			}
			MLACore::mla_debug_add( __LINE__ . " MLATaxonomyArchiveRedirect::mla_posts_results_filter args = " . var_export( $args, true ), self::MLA_DEBUG_CATEGORY );

			if ( ! empty( $args ) ) {
				$archive_page = self::$plugin_settings->get_plugin_option( $args['taxonomy'] );
				if ( empty( $archive_page ) ) {
					$archive_page = self::$plugin_settings->get_plugin_option('archive_page');
				}

				if ( ! empty( $archive_page ) ) {
					$redirect_url = add_query_arg( $args, site_url() . $archive_page );
					MLACore::mla_debug_add( __LINE__ . " MLATaxonomyArchiveRedirect::mla_posts_results_filter redirect_url = " . var_export( $redirect_url, true ), self::MLA_DEBUG_CATEGORY );
	 				wp_safe_redirect( $redirect_url, 302 );
					exit;
				}
			}
		}

		return $posts;
	} // mla_posts_results_filter
} //MLATaxonomyArchiveRedirect

// Install the shortcode at an early opportunity
add_action('init', 'MLATaxonomyArchiveRedirect::initialize');
?>