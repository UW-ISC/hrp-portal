<?php
/**
 * 1. Adds Multisite filter to MLA shortcodes:
 *    The "site_id=id[,id...]|all" parameter names one or more or all sites to query.
 * 2. Supports the "Multisite Global Media" plugin (https://github.com/bueltge/Multisite-Global-Media), 
 * 3. Adds a Settings screen that allows copying MLA option settings from site to site.
 * 4. Adds a Guide/Documentation tab that explains everything.
 *
 * This example plugin uses the WP 4.6+ terminology of Network and Site (not Blog).
 *
 * Created for support topic "Using Shortcodes to retrieve media from another sites media library"
 * opened on 7/12/2017 by "jeynon (@jeynon)".
 * https://wordpress.org/support/topic/using-shortcodes-to-retrieve-media-from-another-sites-media-library/
 *
 * Enhanced for support topic "MLA and Multisite Global Media plugin"
 * opened on 2/15/2022 by "rughjm (@rughjm)".
 * https://wordpress.org/support/topic/mla-and-multisite-global-media-plugin/
 *
 * Enhanced for support topic "Save and Import Settings for Multisite"
 * opened on 3/20/2023 by "Rhapsody348 (@rhapsody348)".
 * https://wordpress.org/support/topic/save-and-import-settings-for-multisite/
 *
 * @package MLA Multisite Extensions
 * @version 1.15
 */

/*
Plugin Name: MLA Multisite Extensions
Plugin URI: http://davidlingren.com/
Description: Adds Multisite filters to MLA shortcodes, supports the "Multisite Global Media" plugin, copies MLA option settings between sites.
Author: David Lingren
Version: 1.15
Author URI: http://davidlingren.com/

Copyright 2017-2023 David Lingren

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
 * Class MLA Multisite Extensions implements the features listed above.
 *
 * @package MLA Multisite Extensions
 * @since 1.00
 */
class MLAMultisiteExtensions {
	/**
	 * Current version number
	 *
	 * @since 1.10
	 *
	 * @var	string
	 */
	const PLUGIN_VERSION = '1.15';

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets, scripts and settings
	 *
	 * @since 1.10
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'MLAMultisiteExtensions';

	/**
	 * Constant to log this plugin's debug activity
	 *
	 * @since 1.10
	 *
	 * @var	integer
	 */
	const MLA_DEBUG_CATEGORY = 0x00008000;

	/**
	 * Settings Management object
	 *
	 * @since 1.10
	 *
	 * @var	array
	 */
	private static $plugin_settings = NULL;

	/**
	 * In-memory representation of the option settings
	 *
	 * @since 1.10
	 *
	 * @var array $_settings {
	 *     @see $_default_settings
	 *     }
	 */
	private static $_settings = NULL;

	/**
	 * Configuration values for the Settings Management object
	 *
	 * @since 1.10
	 *
	 * @var	array
	 */
	private static $settings_arguments = array(
				'slug_prefix' => self::SLUG_PREFIX,
				'plugin_title' => 'MLA Multisite Extensions',
				'menu_title' => 'MLA Multisite',
				'plugin_file_name_only' => 'mla-multisite-extensions',
				'plugin_version' => self::PLUGIN_VERSION,
				'template_file' => '/admin-settings-page.tpl', // Add the path at runtime, in initialize()
				'options' => array(
					// 'slug' => array( 'type' => 'checkbox|text|select|textarea', 'default' => 'text|boolean(0|1)' )
					// See the default values in $_default_settings below
				),
				'general_tab_values' => array(), // additional page_values for 'page-level-options' template
				'documentation_tab_values' => array(
					'plugin_title' => 'MLA Multisite Extensions',
					'settingsURL' => '', // Set at runtime in initialize()
				), // page_values for 'documentation-tab' template
			);

	/**
	 * Default processing options
	 *
	 * @since 1.10
	 *
	 * @var array $_default_settings {
	 *     }
	 */
	private static $_default_settings = array (
					);

	/**
	 * Template file for the Settings page(s) and parts
	 *
	 * This array contains all of the template parts for the Settings page(s). The array is built once
	 * each page load and cached for subsequent use.
	 *
	 * @since 1.10
	 *
	 * @var	array
	 */
	public static $page_template_array = NULL;

	/**
	 * Initialization function, similar to __construct()
	 *
	 * Installs filters and actions that handle the MLA hooks for uploading and mapping.
	 *
	 * @since 1.10
	 *
	 * @return	void
	 */
	public static function initialize() {
//error_log( __LINE__ . ' MLAMultisiteExtensions::initialize() request = ' . var_export( $_REQUEST, true ), 0 );
//error_log( __LINE__ . ' MLAMultisiteExtensions::initialize() get_blog_details( 1 ) = ' . var_export( get_blog_details( 1 ), true ), 0 );
//error_log( __LINE__ . ' MLAMultisiteExtensions::initialize() get_sites = ' . var_export( get_sites(), true ), 0 );

		// This plugin requires MLA
		if ( ! class_exists( 'MLACore', false ) ) {
			return;
		}

		// This plugin requires MLA
		if ( ! is_multisite() ) {
			self::$settings_arguments['messages'] = "ERROR: Multisite support not enabled.";
			//return;
		}

		if ( is_multisite() ) {
			// Post/Page UI elements
			add_shortcode( 'mme_source_site_options', 'MLAMultisiteExtensions::mme_source_site_options_shortcode' );
			add_shortcode( 'mme_destination_sites_items', 'MLAMultisiteExtensions::mme_destination_sites_items_shortcode' );
			add_shortcode( 'mme_terms_source_taxonomies', 'MLAMultisiteExtensions::mme_terms_source_taxonomies_shortcode' );
	
			// Tool actions
			add_shortcode( 'mme_copy_settings', 'MLAMultisiteExtensions::mme_copy_settings_shortcode' );
			add_shortcode( 'mme_copy_terms', 'MLAMultisiteExtensions::mme_copy_terms_shortcode' );
		}
		
		// The plugin settings class is shared with other MLA example plugins
		if ( ! class_exists( 'MLAExamplePluginSettings102', false ) ) {
			require_once( pathinfo( __FILE__, PATHINFO_DIRNAME ) . '/class-mla-example-plugin-settings-102.php' );
		}

		// Add the run-time values to the arguments
		self::$settings_arguments['template_file'] = dirname( __FILE__ ) . self::$settings_arguments['template_file'];
		self::$settings_arguments['documentation_tab_values']['settingsURL'] = admin_url('options-general.php');

		// Look for plugin-specific "tools" action 
		$current_options_source = '0';
		$current_options_destinations = array();
		$current_copy_defaults = false;
		$current_terms_taxonomies = array();
		
		if ( !empty( $_REQUEST[ self::SLUG_PREFIX . '_tools_copy_settings'] ) ) {
			$current_options_source = $_REQUEST[ self::SLUG_PREFIX . '_tools' ]['copy_options_source'];
			$current_options_destinations = $_REQUEST[ self::SLUG_PREFIX . '_tools' ]['copy_options_destinations'];
			$current_copy_defaults = isset( $_REQUEST[ self::SLUG_PREFIX . '_tools' ]['copy_defaults'] );
			self::$settings_arguments['messages'] = self::mme_copy_settings_action( $current_options_source, $current_options_destinations, $current_copy_defaults );
		}
		
		if ( !empty( $_REQUEST[ self::SLUG_PREFIX . '_tools_copy_terms'] ) ) {
			$current_options_source = $_REQUEST[ self::SLUG_PREFIX . '_tools' ]['copy_options_source'];
			$current_options_destinations = $_REQUEST[ self::SLUG_PREFIX . '_tools' ]['copy_options_destinations'];
			$current_terms_taxonomies = $_REQUEST[ self::SLUG_PREFIX . '_tools' ]['copy_terms_taxonomies'];
			self::$settings_arguments['messages'] = self::mme_copy_terms_action( $current_options_source, $current_options_destinations, $current_terms_taxonomies );
		}
		
		// Create our own settings object
		self::$plugin_settings = new MLAExamplePluginSettings102( self::$settings_arguments );

		if ( is_multisite() ) {
			add_filter( 'mla_gallery_attributes', 'MLAMultisiteExtensions::mla_gallery_attributes', 10, 1 );
			add_filter( 'mla_gallery_query_arguments', 'MLAMultisiteExtensions::mla_gallery_query_arguments', 10, 1 );
			add_action( 'mla_gallery_wp_query_object', 'MLAMultisiteExtensions::mla_gallery_wp_query_object', 10, 1 );
			add_filter( 'mla_gallery_the_attachments', 'MLAMultisiteExtensions::mla_gallery_the_attachments', 10, 2 );
			add_filter( 'mla_gallery_item_initial_values', 'MLAMultisiteExtensions::mla_gallery_item_initial_values', 10, 2 );
			add_filter( 'mla_gallery_item_values', 'MLAMultisiteExtensions::mla_gallery_item_values', 10, 1 );
	
			// Filter for detecting the Multisite Global Media plugin
			add_action( 'mla_media_modal_query_filtered_terms', 'MLAMultisiteExtensions::mla_media_modal_query_filtered_terms', 10, 2 );
		}

		// Load template array for front-end shortcodes
		self::$page_template_array = MLACore::mla_load_template( self::$settings_arguments['template_file'], 'path' );

		// The remaining filters are only useful in the admin section; exit if in the "front-end" posts/pages. 
		if ( ! is_admin() ) {
			return;
		}

		// Initialize page-level values andadd the run-time values to the settings
		$general_tab_values = self::$plugin_settings->get_plugin_argument('general_tab_values');
		$general_tab_values['copy_options_source'] = self::mme_source_site_options( $current_options_source );
		$general_tab_values['copy_options_destinations'] = self::mme_destination_sites_items( $current_options_destinations, 'MLAMultisiteExtensions_tools[copy_options_destinations]' );
		$general_tab_values['copy_defaults_checked'] = $current_copy_defaults ? 'checked="checked" ' : '';
		$general_tab_values['copy_terms_taxonomies'] = self::mme_terms_source_taxonomies( $current_terms_taxonomies, 'MLAMultisiteExtensions_tools[copy_terms_taxonomies]' );

		self::$plugin_settings->update_plugin_argument('general_tab_values', $general_tab_values );
	} // initialize

	/**
	 * Compose HTML markup for Source Site select field options
 	 *
	 * @since 1.13
	 *
	 * @param	string	$current_value Optional current selected value, default ''
	 *
	 * @return	string	HTML markup for the select field options
	 */
	public static function mme_source_site_options( $current_value = '' ) {
		// Avoid fatal errors, e.g., for some AJAX calls such as "heartbeat"
		if ( ! class_exists( 'MLAData' ) ) {
			return '';
		}
		
		// Default option if no files exist or there is no current selection
		$option_values = array(
			'value' => '0',
			'text' => '&mdash; Pick a source site &mdash;',
			'selected' => '',
		);
		$select_options = MLAData::mla_parse_template( self::$page_template_array['select-option'], $option_values );

		if ( is_multisite() ) {
			// Build an array of the dynamic options
			foreach ( get_sites() as $site ) {
//error_log( __LINE__ . " mme_source_site_options( {$current_value} ) get_sites[] = " . var_export( $site, true ), 0 );
	
				$details = get_blog_details( $site->blog_id );
//error_log( __LINE__ . " mme_source_site_options( {$current_value} ) get_blog_details()= " . var_export( $details, true ), 0 );
				
				$option_values = array(
					'value' => $details->blog_id,
					'text' => esc_attr( $details->blog_id . ' - ' . $details->blogname ),
					'selected' => $current_value === $details->blog_id ? 'selected=selected' : '',
				);
	
				$select_options .= MLAData::mla_parse_template( self::$page_template_array['select-option'], $option_values );
			}
		}

//error_log( __LINE__ . " mme_source_site_options( {$current_value} ) select_options = " . var_export( $select_options, true ), 0 );
		return $select_options;
	} // mme_source_site_options

	/**
	 * Compose HTML markup for Destination Sites checklist items
 	 *
	 * @since 1.13
	 *
	 * @param	array	$current_value Optional current selected values, default array()
	 * @param	string	$checklist_name Optional HTML name attribute element, default 'copy_options_destinations'
	 *
	 * @return	string	HTML markup for the select field options
	 */
	public static function mme_destination_sites_items( $current_value = array(), $checklist_name = 'copy_options_destinations' ) {
//error_log( __LINE__ . " mme_destination_sites_items current_value = " . var_export( $current_value, true ), 0 );

		// Avoid fatal errors, e.g., for some AJAX calls such as "heartbeat"
		if ( ! class_exists( 'MLAData' ) ) {
			return '';
		}
		
		$option_values = array(
			'checklist_name' => esc_attr( $checklist_name ),
			'value' => 'all',
			'text' => '<strong>&nbsp;' . esc_attr( '* - ALL Destination Sites' ) . '</strong>',
			'checked' => in_array( 'all', $current_value ) ? 'checked=checked' : '',
		);

		$checklist_items = MLAData::mla_parse_template( self::$page_template_array['checklist-item'], $option_values );

		if ( is_multisite() ) {
			// Build an array of the sites
			foreach ( get_sites() as $site ) {
//error_log( __LINE__ . " mme_destination_sites_items() get_sites[] = " . var_export( $site, true ), 0 );
	
				$details = get_blog_details( $site->blog_id );
//error_log( __LINE__ . " mme_destination_sites_items() get_blog_details()= " . var_export( $details, true ), 0 );
	
				$option_values = array(
					'checklist_name' => esc_attr( $checklist_name ),
					'value' => $details->blog_id,
					'text' => esc_attr( $details->blog_id . ' - ' . $details->blogname ),
					'checked' => in_array( $details->blog_id, $current_value ) ? 'checked=checked' : '',
				);
	
				$checklist_items .= MLAData::mla_parse_template( self::$page_template_array['checklist-item'], $option_values );
			}
		}

//error_log( __LINE__ . " mme_destination_sites_items() checklist_items = " . var_export( $checklist_items, true ), 0 );
		return $checklist_items;
	} // mme_destination_sites_items

	/**
	 * Copy non-default (or ALL) MLA option settings from a source site to one or more destination sites.
	 *
	 * @since 1.10
	 *
	 * @param	string	$options_source Source site
	 * @param	array	$options_destinations Destination site(s)
	 * @param	boolean	$copy_defaults Optional, default false. True to copy ALL settings, not just non-defaults.
	 *
	 * @return	string	action-specific message(s), e.g., summary of results
	 */
	public static function mme_copy_settings_action( $options_source, $options_destinations, $copy_defaults = false ) {
		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mme_copy_settings_action( {$options_source}, {$copy_defaults} ) options_destinations = " . var_export( $options_destinations, true ), self::MLA_DEBUG_CATEGORY );

		if ( '0' === $options_source ) {
			$messages = 'No Source Site selected, nothing copied.';
		} else {
			$messages = '';

			// MLAObjects::initialize hasn't run yet
			MLACore::mla_initialize_tax_checked_on_top();

			$blog_details = get_blog_details( $options_source );
			if ( $blog_details ) {
				switch_to_blog( $blog_details->blog_id );
				$source_settings = MLASettings::mla_get_export_settings( $copy_defaults );
				restore_current_blog();
				MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mme_copy_settings_action() source_settings = " . var_export( $source_settings, true ), self::MLA_DEBUG_CATEGORY );
	//error_log( __LINE__ . " mme_copy_settings_action() source_settings = " . var_export( $source_settings, true ), 0 );
				$source_count = count( $source_settings );

				if ( $copy_defaults ) {
					$messages .= sprintf( 'Source %1$s - %2$s exported all %3$d settings.<br />', $blog_details->blog_id, $blog_details->blogname, count( $source_settings['settings'] ) );
				} else {
					$messages .= sprintf( 'Source %1$s - %2$s exported %3$d non-default settings.<br />', $blog_details->blog_id, $blog_details->blogname, count( $source_settings['settings'] ) );
				}
				
				if ( in_array( 'all', $options_destinations ) ) {
					$options_destinations = array();
					
					foreach ( get_sites() as $site ) {
						$options_destinations[] = (string) $site->blog_id;
					}
				}
				
				foreach( $options_destinations as $site_id ) {
					if ( '0' === $site_id || $options_source === $site_id ) {
						continue;
					}
					
					$blog_details = get_blog_details( $site_id );
//error_log( __LINE__ . " mme_copy_settings_action() blog_details for {$site_id} = " . var_export( $blog_details, true ), 0 );
					if ( $blog_details ) {
						switch_to_blog( $site_id );
						$results = MLASettings::mla_put_export_settings( $source_settings['settings'] );
						restore_current_blog();
						MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mme_copy_settings_action() results for {$site_id} = " . var_export( $results, true ), self::MLA_DEBUG_CATEGORY );

						if ( $copy_defaults ) {
							$messages .= sprintf( 'Destination %1$s - %2$s updated %3$d settings.<br />', $blog_details->blog_id, $blog_details->blogname, $results['updated'] );
						} else {
							$messages .= sprintf( 'Destination %1$s - %2$s updated %3$d non-default settings.<br />', $blog_details->blog_id, $blog_details->blogname, $results['updated'] );
						}
					} else {
						$messages .= "ERROR: Invalid Destination Site: {$site_id}\r\n";
					}
				}
			} else {
				$messages = "ERROR: Invalid Source Site: {$options_source}";
			}
		} // good $options_source

		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mme_copy_settings_action() return messages = " . var_export( $messages, true ), self::MLA_DEBUG_CATEGORY );
		return $messages;		
	} // mme_copy_settings_action

	/**
	 * Fetch term definitions from an array of Source Site taxonomies.
	 *
	 * @since 1.12
	 *
	 * @param	array	$source_taxonomies Name/slug values for selected source site taxonomies
	 *
	 * @return	integer	Count of source terms
	 */
	private static function _get_source_site_terms( $source_taxonomies ) {

		self::$source_terms = array();
		$source_count = 0;
		
		foreach( $source_taxonomies as $tax_name ) {
			$terms = get_terms( array( 'taxonomy' => $tax_name, 'hide_empty' => false, ) );
			if ( is_wp_error( $terms ) ) {
				continue;
			}
			
			foreach( $terms as $term ) {
				self::$source_terms[ $tax_name ][ $term->term_id ] = array( 'name' => $term->name, 'slug' => $term->slug, 'description' => $term->description, 'parent' => $term->parent, );
				$source_count++;
			}

			// Sort by term id within each taxonomy
			if ( !empty( $terms ) ) {
				ksort( self::$source_terms[ $tax_name ] );
			}
		}
	
		return $source_count;	
	} // _get_source_site_terms

	/**
	 * Source site term definitions to facilitate parent term creation
	 *
	 * @since 1.12
	 *
	 * @var array $_term_slugs ( ID => array( 'slug' -> slug, 'parent' => parent, 'description' => description )
	 *     }
	 */
	private static $source_terms = array();	

	/**
	 * Insert a destination site term and its parent(s) if they do not already exist
	 *
	 * @since 1.12
	 *
	 * @param	$source_site_taxonomy taxonomy slug for this term
	 * @param	$source_site_term_id ID/index for self::$source_terms
	 *
	 * @return	integer	Destination site ID for this term
	 */
	private static function _maybe_insert_destination_site_term( $taxonomy, $source_site_term_id ) {
		$term = self::$source_terms[ $taxonomy ][ $source_site_term_id ];
//error_log( __LINE__ . " _maybe_insert_destination_site_term( {$taxonomy}, {$source_site_term_id} ) term = " . var_export( $term, true ), 0 );
		
		$destination_term = get_term_by( 'slug', $term['slug'], $taxonomy, $output = OBJECT, $filter = 'raw' );
		if( $destination_term ) {
			return $destination_term->term_id;
		}
		
		if ( 0 < $term['parent'] ) {
			$parent = self::_maybe_insert_destination_site_term( $taxonomy, $term['parent'] );
		} else {
			$parent = 0;
		}
		
		// Insert the term
		$args = array(
			'description' => $term['description'],
			'slug' => $term['slug'],
			'parent' => $parent,
		);
	
		$results = wp_insert_term( $term['name'], $taxonomy, $args );
//error_log( __LINE__ . " _maybe_insert_destination_site_term( {$taxonomy}, {$source_site_term_id} ) results = " . var_export( $results, true ), 0 );
		if ( is_wp_error( $results ) ) {
			return 0;
		}
		
		return $results['term_id'];
	} // _maybe_insert_destination_site_term
	
	/**
	 * Fetch term definitions from an array of Source Site taxonomies.
	 *
	 * @since 1.12
	 *
	 * @uses	self::$source_terms	Source site term definition objects 
	 *
	 * @return	array	Count of terms inserted per taxonomy
	 */
	private static function _put_destination_site_terms() {
		$insert_counts = array();

		foreach( self::$source_terms as $taxonomy => $source_terms ) {
			if ( MLACore::mla_taxonomy_support( $taxonomy, 'support' ) ) {
				$old_count = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'fields' => 'count', ) );
				if ( is_wp_error( $old_count ) ) {
					continue;
				}

				foreach( $source_terms as $term_id => $term ) {
					$new_id = self::_maybe_insert_destination_site_term( $taxonomy, $term_id );
				} // foreach term
				$insert_counts[ $taxonomy ] = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'fields' => 'count', ) ) - $old_count;
			}
		} // foreach taxonomy

//error_log( __LINE__ . " _put_destination_site_terms insert_counts = " . var_export( $insert_counts, true ), 0 );
		return $insert_counts;
	} // _put_destination_site_terms
	
	/**
	 * Compose HTML markup for copy terms taxonomies items
 	 *
	 * @since 1.13
	 *
	 * @param	array	$current_value Optional current selected values, default array()
	 * @param	string	$checklist_name Optional HTML name attribute element, default 'copy_terms_taxonomies'
	 *
	 * @return	string	HTML markup for the select field options
	 */
	public static function mme_terms_source_taxonomies( $current_value = array(), $checklist_name = 'copy_terms_taxonomies' ) {
//error_log( __LINE__ . " mme_destination_sites_items current_value = " . var_export( $current_value, true ), 0 );

		// Avoid fatal errors, e.g., for some AJAX calls such as "heartbeat"
		if ( ! class_exists( 'MLAData' ) ) {
			return '';
		}
		
		$checklist_items = '';

		// Build an array of the supported taxonomies
		$supported_taxonomies = MLACore::mla_supported_taxonomies('support');
		foreach ( $supported_taxonomies as $tax_name ) {
			$tax_object = get_taxonomy( $tax_name );
//error_log( __LINE__ . " mme_terms_source_taxonomies( {$tax_name} ) tax_object = " . var_export( $tax_object, true ), 0 );

			if ( false !== $tax_object ) {
				$option_values = array(
					'checklist_name' => esc_attr( $checklist_name ),
					'value' => $tax_name,
					'text' => esc_attr( $tax_object->label ),
					'checked' => in_array( $tax_name, $current_value ) ? 'checked=checked' : '',
				);
	
				$checklist_items .= MLAData::mla_parse_template( self::$page_template_array['checklist-item'], $option_values );
			}
		}

//error_log( __LINE__ . " mme_terms_source_taxonomies() checklist_items = " . var_export( $checklist_items, true ), 0 );
		return $checklist_items;
	} // mme_terms_source_taxonomies

	/**
	 * Copy non-default MLA options from a source site to one or more destination sites.
	 *
	 * @since 1.12
	 *
	 * @param	string	$terms_source Source site
	 * @param	array	$terms_destinations Destination site(s)
	 * @param	array	$terms_taxonomies Source Site taxonomies
	 *
	 * @return	string	action-specific message(s), e.g., summary of results
	 */
	public static function mme_copy_terms_action( $terms_source, $terms_destinations, $terms_taxonomies ) {
		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mme_copy_terms_action( {$terms_source} ) terms_destinations = " . var_export( $terms_destinations, true ), self::MLA_DEBUG_CATEGORY );
		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mme_copy_terms_action( {$terms_source} ) terms_taxonomies = " . var_export( $terms_taxonomies, true ), self::MLA_DEBUG_CATEGORY );

		if ( '0' === $terms_source ) {
			$messages = 'No Source Site selected, nothing copied.';
		} else {
			$messages = '';

			// MLAObjects::initialize hasn't run yet
			MLACore::mla_initialize_tax_checked_on_top();

			$blog_details = get_blog_details( $terms_source );
			if ( $blog_details ) {
				switch_to_blog( $blog_details->blog_id );
				$source_count = self::_get_source_site_terms( $terms_taxonomies );
				restore_current_blog();
				MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mme_copy_terms_action( {$source_count} ) source_terms = " . var_export( self::$source_terms, true ), self::MLA_DEBUG_CATEGORY );

				$messages .= sprintf( 'Source %1$s - %2$s gathered %3$d term definitions.<br />', $blog_details->blog_id, $blog_details->blogname, $source_count ) . "\r\n";

				if ( in_array( 'all', $terms_destinations ) ) {
					$terms_destinations = array();
					
					foreach ( get_sites() as $site ) {
						$terms_destinations[] = (string) $site->blog_id;
					}
				}
				
				foreach( $terms_destinations as $site_id ) {
					if ( '0' === $site_id || $terms_source === $site_id ) {
						continue;
					}
					
					$blog_details = get_blog_details( $site_id );
//error_log( __LINE__ . " mme_copy_terms_action() blog_details for {$site_id} = " . var_export( $blog_details, true ), 0 );
					if ( $blog_details ) {
						switch_to_blog( $site_id );
						$term_inserts = self::_put_destination_site_terms();
						restore_current_blog();
						MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mme_copy_terms_action() term_inserts for {$site_id} = " . var_export( $term_inserts, true ), self::MLA_DEBUG_CATEGORY );

						foreach ( $term_inserts as $taxonomy => $inserts ) {
							$messages .= sprintf( 'Destination %1$s - %2$s inserted %3$d %4$s term definitions.<br />', $blog_details->blog_id, $blog_details->blogname, $inserts, $taxonomy ) . "\r\n";
						}
						
					} else {
						$messages .= "ERROR: Invalid Destination Site: {$site_id}\r\n";
					}
				}
			} else {
				$messages = "ERROR: Invalid Source Site: {$options_source}";
			}
		} // good $options_source

		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mme_copy_terms_action() return messages = " . var_export( $messages, true ), self::MLA_DEBUG_CATEGORY );
		return $messages;		
	} // mme_copy_terms_action

	/**
	 * Load MLA files on which this plugin depends
	 *
	 * @since 1.13
	 *
	 */
	private static function _load_mla_classes() {
		static $first_call = true;
		
		if ( $first_call ) {
			// Template file and database access functions.
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-data-query.php' );
			MLAQuery::initialize();
	
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-data.php' );
			MLAData::initialize();
	
			// Shortcode shim functions
			//require_once( MLA_PLUGIN_PATH . 'includes/class-mla-shortcodes.php' );
			//MLAShortcodes::initialize();
	
			//require_once( MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-support.php' );
	
			// Plugin settings management
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-options.php' );
			MLAOptions::initialize();
			
			// Plugin settings management page
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-settings.php' );
			MLASettings::initialize();
		
			$first_call = false;
		}
	} // _load_mla_classes

	/**
	 * Shortcode; Generate HTML <option /> tags, e.g., for Source Site selection
	 *
	 * @since 1.13
	 *
	 * @param	array	$attr Shortcode parameters; see defaults in function code
	 * @param	string	$content Optional content for enclosing shortcodes
	 *
	 * @return	string	post/page content to replace the shortcode; HTML markup for the select field options
	 */
	public static function mme_source_site_options_shortcode( $attr, $content = NULL ) {
		self::_load_mla_classes();
		
		$default_arguments = array(
			'current_site' => '0',
			'select_name' => 'copy_options_source',
		);

		/*
		 * Make sure $attr is an array, even if it's empty,
		 * and repair damage caused by link-breaks in the source text
		 */
		$attr = MLAShortcodes::mla_validate_attributes( $attr, $content );

		// Look for 'request' substitution parameters, which can be added to any input parameter
		foreach ( $attr as $attr_key => $attr_value ) {
			// Only expand our own parameters
			if ( array_key_exists( $attr_key, $default_arguments ) ) {
				$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr_value ) );
				$replacement_values = MLAData::mla_expand_field_level_parameters( $attr_value );

				if ( ! empty( $replacement_values ) ) {
					$attr[ $attr_key ] = MLAData::mla_parse_template( $attr_value, $replacement_values );
				}
			}
		}

		// Accept only the attributes we need and supply defaults
		$arguments = shortcode_atts( $default_arguments, $attr );

		// Make the control sticky
		if ( ( '0' === $arguments['current_site'] ) && isset( $_REQUEST[ $arguments['select_name'] ] ) ) {
			$arguments['current_site'] = $_REQUEST[ $arguments['select_name'] ];
		}

		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mme_source_site_options_shortcode() arguments = " . var_export( $arguments, true ), self::MLA_DEBUG_CATEGORY );

		return self::mme_source_site_options( $arguments['current_site'] );
	} // mme_source_site_options_shortcode

	/**
	 * Shortcode; Generate HTML <li /> tags, e.g., for Destination Sites selection
	 *
	 * @since 1.13
	 *
	 * @param	array	$attr Shortcode parameters; see defaults in function code
	 * @param	string	$content Optional content for enclosing shortcodes
	 *
	 * @return	string	post/page content to replace the shortcode
	 */
	public static function mme_destination_sites_items_shortcode( $attr, $content = NULL ) {
		self::_load_mla_classes();
		
		$default_arguments = array(
			'current_sites' => '',
			'checklist_name' => 'copy_options_destinations',
		);

		/*
		 * Make sure $attr is an array, even if it's empty,
		 * and repair damage caused by link-breaks in the source text
		 */
		$attr = MLAShortcodes::mla_validate_attributes( $attr, $content );

		// Look for 'request' substitution parameters, which can be added to any input parameter
		foreach ( $attr as $attr_key => $attr_value ) {
			// Only expand our own parameters
			if ( array_key_exists( $attr_key, $default_arguments ) ) {
				$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr_value ) );
				$replacement_values = MLAData::mla_expand_field_level_parameters( $attr_value );

				if ( ! empty( $replacement_values ) ) {
					$attr[ $attr_key ] = MLAData::mla_parse_template( $attr_value, $replacement_values );
				}
			}
		}

		// Accept only the attributes we need and supply defaults
		$arguments = shortcode_atts( $default_arguments, $attr );

		// Make the control sticky
		if ( ( '' === $arguments['current_sites'] ) && isset( $_REQUEST[ $arguments['checklist_name'] ] ) ) {
			$arguments['current_sites'] = $_REQUEST[ $arguments['checklist_name'] ];
		} else {
			$arguments['current_sites'] = explode( ',', $arguments['current_sites'] );
		}

		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mme_source_site_options_shortcode() arguments = " . var_export( $arguments, true ), self::MLA_DEBUG_CATEGORY );

		return self::mme_destination_sites_items( $arguments['current_sites'], $arguments['checklist_name'] );
	} // mme_destination_sites_items_shortcode

	/**
	 * Shortcode; Generate HTML <li /> tags, e.g., for Source Taxonomies selection
	 *
	 * @since 1.13
	 *
	 * @param	array	$attr Shortcode parameters; see defaults in function code
	 * @param	string	$content Optional content for enclosing shortcodes
	 *
	 * @return	string	post/page content to replace the shortcode
	 */
	public static function mme_terms_source_taxonomies_shortcode( $attr, $content = NULL ) {
		self::_load_mla_classes();
		
		$default_arguments = array(
			'current_taxonomies' => '',
			'checklist_name' => 'copy_terms_taxonomies',
		);

		/*
		 * Make sure $attr is an array, even if it's empty,
		 * and repair damage caused by link-breaks in the source text
		 */
		$attr = MLAShortcodes::mla_validate_attributes( $attr, $content );

		// Look for 'request' substitution parameters, which can be added to any input parameter
		foreach ( $attr as $attr_key => $attr_value ) {
			// Only expand our own parameters
			if ( array_key_exists( $attr_key, $default_arguments ) ) {
				$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr_value ) );
				$replacement_values = MLAData::mla_expand_field_level_parameters( $attr_value );

				if ( ! empty( $replacement_values ) ) {
					$attr[ $attr_key ] = MLAData::mla_parse_template( $attr_value, $replacement_values );
				}
			}
		}

		// Accept only the attributes we need and supply defaults
		$arguments = shortcode_atts( $default_arguments, $attr );

		// Make the control sticky
		if ( ( '' === $arguments['current_taxonomies'] ) && isset( $_REQUEST[ $arguments['checklist_name'] ] ) ) {
			$arguments['current_taxonomies'] = $_REQUEST[ $arguments['checklist_name'] ];
		} else {
			$arguments['current_taxonomies'] = explode( ',', $arguments['current_taxonomies'] );
		}

		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mme_terms_source_taxonomies_shortcode() arguments = " . var_export( $arguments, true ), self::MLA_DEBUG_CATEGORY );

		return self::mme_terms_source_taxonomies( $arguments['current_taxonomies'], $arguments['checklist_name'] );
	} // mme_terms_source_taxonomies_shortcode

	/**
	 * Shortcode; Copy option settings from a Source Site to one or more Destination Sites
	 *
	 * @since 1.13
	 *
	 * @param	array	$attr Shortcode parameters; see defaults in function code
	 * @param	string	$content Optional content for enclosing shortcodes
	 *
	 * @return	string	post/page content to replace the shortcode
	 */
	public static function mme_copy_settings_shortcode( $attr, $content = NULL ) {
		self::_load_mla_classes();
		
		$default_arguments = array(
			'enforce_capability' => 'true',
			'action' => 'copy_settings',
			'no_action_text' => '',
			'source_site' => '0',
			'no_source_text' => '',
			'destination_sites' => '',
			'copy_defaults' => 'false',
		);

		/*
		 * Make sure $attr is an array, even if it's empty,
		 * and repair damage caused by link-breaks in the source text
		 */
		$attr = MLAShortcodes::mla_validate_attributes( $attr, $content );

		/*/ Make sure $attr is an array, even if it's empty
		if ( empty( $attr ) ) {
			$attr = array();
		} elseif ( is_string( $attr ) ) {
			$attr = shortcode_parse_atts( $attr );
		} // */

		// Look for 'request' substitution parameters, which can be added to any input parameter
		foreach ( $attr as $attr_key => $attr_value ) {
			// Only expand our own parameters
			if ( array_key_exists( $attr_key, $default_arguments ) ) {
				$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr_value ) );
				$replacement_values = MLAData::mla_expand_field_level_parameters( $attr_value );

				if ( ! empty( $replacement_values ) ) {
					$attr[ $attr_key ] = MLAData::mla_parse_template( $attr_value, $replacement_values );
				}
			}
		}

		// Accept only the attributes we need and supply defaults
		$arguments = shortcode_atts( $default_arguments, $attr );
		if ( empty( $arguments['destination_sites'] ) ) {
			$arguments['destination_sites'] = array();
		} else {
			$arguments['destination_sites'] = explode( ',', $arguments['destination_sites'] );
		}
		
		$arguments['copy_defaults'] = ( 'true' === strtolower( $arguments['copy_defaults'] ) );

		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mme_copy_settings_shortcode() arguments = " . var_export( $arguments, true ), self::MLA_DEBUG_CATEGORY );

		if ( !empty( $arguments['action'] ) && ( false === isset( $_REQUEST[ $arguments['action'] ] ) ) ) {
			return $arguments['no_action_text'];
		}
		
		if ( '' === $arguments['source_site'] ) {
			$arguments['source_site'] = '0'; 
		}

		if ( !empty( $arguments['no_source_text'] ) && ( '0' === $arguments['source_site'] ) ) {
			return $arguments['no_source_text'];
		}
		
		if ( 'false' !== strtolower( $arguments['enforce_capability'] ) ) {
			// Check if logged-in user can manage options
			$current_user = wp_get_current_user();
			if ( $current_user->ID ) {
				if ( !current_user_can('manage_options') ) {
					$arguments['source_site'] = '0';
				}
			} else {
				$arguments['source_site'] = '0';
			}
		} // enforce_capability
		
		return self::mme_copy_settings_action( $arguments['source_site'], $arguments['destination_sites'], $arguments['copy_defaults'] );
	} //mme_copy_settings_shortcode

	/**
	 * Shortcode; Copy term definitions from a Source Site to one or more Destination Sites
	 *
	 * @since 1.13
	 *
	 * @param	array	$attr Shortcode parameters; see defaults in function code
	 * @param	string	$content Optional content for enclosing shortcodes
	 *
	 * @return	string	post/page content to replace the shortcode
	 */
	public static function mme_copy_terms_shortcode( $attr, $content = NULL ) {
		self::_load_mla_classes();
		
		$default_arguments = array(
			'enforce_capability' => 'true',
			'action' => 'copy_terms',
			'no_action_text' => '',
			'source_site' => 0,
			'no_source_text' => '',
			'destination_sites' => '',
			'taxonomy' => '',
			'no_taxonomy_text' => '',
		);

		/*
		 * Make sure $attr is an array, even if it's empty,
		 * and repair damage caused by link-breaks in the source text
		 */
		$attr = MLAShortcodes::mla_validate_attributes( $attr, $content );

		// Look for 'request' substitution parameters, which can be added to any input parameter
		foreach ( $attr as $attr_key => $attr_value ) {
			// Only expand our own parameters
			if ( array_key_exists( $attr_key, $default_arguments ) ) {
				$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr_value ) );
				$replacement_values = MLAData::mla_expand_field_level_parameters( $attr_value );

				if ( ! empty( $replacement_values ) ) {
					$attr[ $attr_key ] = MLAData::mla_parse_template( $attr_value, $replacement_values );
				}
			}
		}

		// Accept only the attributes we need and supply defaults
		$arguments = shortcode_atts( $default_arguments, $attr );
		if ( empty( $arguments['destination_sites'] ) ) {
			$arguments['destination_sites'] = array();
		} else {
			$arguments['destination_sites'] = explode( ',', $arguments['destination_sites'] );
		}
		
		if ( empty( $arguments['taxonomy'] ) ) {
			$arguments['taxonomy'] = array();
		} else {
			$arguments['taxonomy'] = explode( ',', $arguments['taxonomy'] );
		}

		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mme_copy_terms_shortcode() arguments = " . var_export( $arguments, true ), self::MLA_DEBUG_CATEGORY );

		if ( !empty( $arguments['action'] ) && ( false === isset( $_REQUEST[ $arguments['action'] ] ) ) ) {
			return $arguments['no_action_text'];
		}
		
		if ( '' === $arguments['source_site'] ) {
			$arguments['source_site'] = '0'; 
		}

		if ( !empty( $arguments['no_source_text'] ) && ( '0' === $arguments['source_site'] ) ) {
			return $arguments['no_source_text'];
		}
		
		if ( !empty( $arguments['no_taxonomy_text'] ) && empty( $arguments['taxonomy'] ) ) {
			return $arguments['no_taxonomy_text'];
		}
		
		if ( 'false' !== strtolower( $arguments['enforce_capability'] ) ) {
			// Check if logged-in user can assign terms
			$current_user = wp_get_current_user();
			if ( $current_user->ID ) {
				foreach ( $arguments['taxonomy'] as $index => $taxonomy ) {
					if ( '0' === $taxonomy ) {
						continue;
					}
					
					$taxonomy_obj = get_taxonomy( $taxonomy );
MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mme_copy_terms_shortcode( $taxonomy ) taxonomy_obj = " . var_export( $taxonomy_obj, true ), self::MLA_DEBUG_CATEGORY );

					if ( !current_user_can( $taxonomy_obj->cap->assign_terms ) ) {
						unset( $arguments['taxonomy'][ $index ] );
					}
				} // foreach taxonomy
			} else {
				$arguments['taxonomy'] = array();
			}
		} // enforce_capability
		
		return self::mme_copy_terms_action( $arguments['source_site'], $arguments['destination_sites'], $arguments['taxonomy'] );
	} //mme_copy_terms_shortcode

	/**
	 * Save the shortcode attributes
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $shortcode_attributes = array();

	/**
	 * MLA Gallery (Display) Attributes
	 *
	 * This filter gives you an opportunity to record or modify the arguments passed in to the shortcode
	 * before they are merged with the default arguments used for the gallery display.
	 *
	 * The $shortcode_attributes array is where you will find any of your own parameters that are coded in the
	 * shortcode, e.g., [mla_gallery my_parameter="my value"].
	 *
	 * @since 1.00
	 *
	 * @param	array	the shortcode parameters passed in to the shortcode
	 *
	 * @return	array	updated shortcode attributes
	 */
	public static function mla_gallery_attributes( $shortcode_attributes ) {
		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mla_gallery_attributes() shortcode_attributes = " . var_export( $shortcode_attributes, true ), self::MLA_DEBUG_CATEGORY );

		// Save the original attributes for use in the later filters
		if ( !isset( self::$all_query_parameters['multi_site_query'] ) ) {
			self::$shortcode_attributes = $shortcode_attributes;
			unset( $shortcode_attributes['site_id'] );
		}
	
		return $shortcode_attributes;
	} // mla_gallery_attributes

	/**
	 * Save the query arguments
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $all_query_parameters = array();

	/**
	 * MLA Gallery Query Arguments
	 *
	 * This filter gives you an opportunity to record or modify the attachment query arguments
	 * after the shortcode attributes are merged with the default arguments.
	 *
	 * @since 1.00
	 *
	 * @param	array	shortcode arguments merged with attachment selection defaults, so every possible parameter is present
	 *
	 * @return	array	updated attachment query arguments
	 */
	public static function mla_gallery_query_arguments( $all_query_parameters ) {
		global $post;
		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mla_gallery_query_arguments() input all_query_parameters = " . var_export( $all_query_parameters, true ), self::MLA_DEBUG_CATEGORY );

		// Save the original parameters for use in the later filters
		if ( !isset( self::$all_query_parameters['multi_site_query'] ) ) {
			//error_log( __LINE__ . ' MLAMultisiteExtensions::mla_gallery_query_arguments self::$shortcode_attributes = ' . var_export( self::$shortcode_attributes, true ), 0 );

			// Taxonomy parameters are handled separately
			// {tax_slug} => 'term' | array ( 'term', 'term', ... )
			// 'tax_query' => ''
			// 'tax_input' => ''
			// 'tax_relation' => 'OR', 'AND' (default),
			// 'tax_operator' => 'OR' (default), 'IN', 'NOT IN', 'AND',
			// 'tax_include_children' => true (default), false
			$shortcode_attributes = self::$shortcode_attributes;

			$all_taxonomies = get_taxonomies( array ( 'show_ui' => true ), 'names' );
			foreach( $shortcode_attributes as $key => $value ) {
				if ( array_key_exists( $key, $all_taxonomies ) ) {
					$all_query_parameters[ $key ] = $shortcode_attributes[ $key ];
				}
			}

			if ( !empty( $shortcode_attributes['tax_query'] ) ) {
				$all_query_parameters['tax_query'] = $shortcode_attributes['tax_query'];
			}

			if ( !empty( $shortcode_attributes['tax_input'] ) ) {
				$all_query_parameters['tax_input'] = $shortcode_attributes['tax_input'];
			}

			if ( !empty( $shortcode_attributes['tax_relation'] ) ) {
				$all_query_parameters['tax_relation'] = $shortcode_attributes['tax_relation'];
			}

			if ( !empty( $shortcode_attributes['tax_operator'] ) ) {
				$all_query_parameters['tax_operator'] = $shortcode_attributes['v'];
			}

			if ( !empty( $shortcode_attributes['tax_include_children'] ) ) {
				$all_query_parameters['tax_include_children'] = $shortcode_attributes['tax_include_children'];
			}

			self::$all_query_parameters = $all_query_parameters;
		}
		
		if ( isset( self::$shortcode_attributes['site_id'] ) ) {
			if ( 'all' === trim( strtolower( self::$shortcode_attributes['site_id'] ) ) ) {
				$sites = get_sites( array( 'network_id' => 1 ) );
				$site_ids = array();
				foreach( $sites as $site ) {
					$site_ids[] = $site->blog_id;
				}
			} else {
				$site_ids = array_map( 'absint', explode( ',', self::$shortcode_attributes['site_id'] ) );
				foreach ( $site_ids as &$site ) {
					if ( 0 === $site ) {
						$site = get_current_blog_id();
					}
				}

				$site_ids = array_unique( $site_ids );
				self::$shortcode_attributes['site_id'] = implode( ',', $site_ids );
			}

			// Accumulate attachments from multiple blogs and short-circuit the normal query
			if ( 1 < count( $site_ids ) ) {
				// Save the site_id parameter, then remove it from the site-specific queries
				$save_site_id = self::$shortcode_attributes['site_id'];
				unset( self::$shortcode_attributes['site_id'] );

				// We must do the multi-site pagination
				if ( !empty( $all_query_parameters['posts_per_page'] ) ) {
					self::$all_query_parameters['multi_site_limit'] = absint( $all_query_parameters['posts_per_page'] );
				} elseif ( !empty( $all_query_parameters['numberposts'] ) ) {
					self::$all_query_parameters['multi_site_limit'] = absint( $all_query_parameters['numberposts'] );
				}
				
				if ( isset( self::$all_query_parameters['multi_site_limit'] ) ) {
					if ( !empty( $all_query_parameters['offset'] ) ) {
						self::$all_query_parameters['multi_site_offset'] = $all_query_parameters['offset'];
					} else {
						if ( !empty( $all_query_parameters[ self::$shortcode_attributes['mla_page_parameter' ] ] ) ) {
							$page = $all_query_parameters[ self::$shortcode_attributes['mla_page_parameter' ] ];
						} else {
							$page = 1;
						}

						self::$all_query_parameters['multi_site_offset'] = ( absint( $page ) - 1 ) * self::$all_query_parameters['multi_site_limit'];
					}

					// Remove pagination from site-specific queries
					$all_query_parameters['numberposts'] = 0;
					$all_query_parameters['posts_per_page'] = 0;
					$all_query_parameters['posts_per_archive_page'] = 0;
					$all_query_parameters['paged'] = NULL;
					$all_query_parameters['offset'] = NULL;
					$all_query_parameters['mla_paginate_current'] = NULL;
					$all_query_parameters['mla_paginate_total'] = NULL;
					$all_query_parameters[ self::$shortcode_attributes['mla_page_parameter' ] ] = NULL;
				}

				// Tell all filters this is not the original query
				self::$all_query_parameters['multi_site_query'] = true;
				self::$all_attachments = array();
				foreach( $site_ids as $site_id ) {
					$blog_details = get_blog_details( $site_id );
					if ( $blog_details ) {
						switch_to_blog( $site_id );
						$attachments = MLAShortcodes::mla_get_shortcode_attachments( $post->ID, $all_query_parameters, true );
						restore_current_blog();

						if ( is_array( $attachments ) ) {
							unset( $attachments['found_rows'] );
							unset( $attachments['max_num_pages'] );
							self::$all_attachments[ $site_id ] = $attachments;
						}
					} // $blog_details
				} // foreach $site_id
				unset( self::$all_query_parameters['multi_site_query'] );

				// Restore the site_id parameter, then replace the original query with a quick alternative that returns no attachments
				self::$shortcode_attributes['site_id'] = $save_site_id;
				return array( 'ids' => '1', );
			} // multi-blog query

			$blog_details = get_blog_details( reset( $site_ids ) );

			if ( $blog_details ) {
				switch_to_blog( current( $site_ids ));
			} else {
				unset( self::$shortcode_attributes['site_id'] );
			}
		} // isset( self::$shortcode_attributes['site_id'] )

		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mla_gallery_query_arguments() final all_query_parameters = " . var_export( $all_query_parameters, true ), self::MLA_DEBUG_CATEGORY );
		return $all_query_parameters;
	} // mla_gallery_query_arguments

	/**
	 * Save some of the WP_Query object properties
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $wp_query_properties = array();

	/**
	 * MLA Gallery WP Query Object
	 *
	 * This action gives you an opportunity (read-only) to record anything you need from the WP_Query object used
	 * to select the attachments for gallery display. This is the ONLY point at which the WP_Query object is defined.
	 *
	 * @since 1.00
	 * @uses MLAShortcodes::$mla_gallery_wp_query_object
	 *
	 * @param	array	$query_arguments Query arguments passed to WP_Query->query
	 */
	public static function mla_gallery_wp_query_object( $query_arguments ) {
		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mla_gallery_wp_query_object() query_arguments = " . var_export( $query_arguments, true ), self::MLA_DEBUG_CATEGORY );

		self::$wp_query_properties = array();
		self::$wp_query_properties ['request'] = MLAShortcodes::$mla_gallery_wp_query_object->request;
		//self::$wp_query_properties ['query_vars'] = MLAShortcodes::$mla_gallery_wp_query_object->query_vars;
		self::$wp_query_properties ['post_count'] = MLAShortcodes::$mla_gallery_wp_query_object->post_count;

		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mla_gallery_wp_query_object() wp_query_properties = " . var_export( self::$wp_query_properties, true ), self::MLA_DEBUG_CATEGORY );

		if ( isset( self::$shortcode_attributes['site_id'] ) ) {
			restore_current_blog();
			self::$current_site_id = get_current_blog_id();
		}
	} // mla_gallery_wp_query_object

	/**
	 * Translates query parameters to orderby rules.
	 *
	 * Accepts one or more valid columns, with or without ASC/DESC.
	 * Enhanced version of /wp-includes/formatting.php function sanitize_sql_orderby().
	 *
	 * @since 1.02
	 * @uses self::$all_query_parameters
	 *
	 * @return array Returns the orderby rules if present, empty array otherwise.
	 */
	private static function _validate_orderby(){

		$results = array ();
		$order = isset( self::$all_query_parameters['order'] ) ? trim( strtoupper( self::$all_query_parameters['order'] ) ) : 'ASC';
		$orderby = isset( self::$all_query_parameters['orderby'] ) ? self::$all_query_parameters['orderby'] : '';
		$meta_key = isset( self::$all_query_parameters['meta_key'] ) ? self::$all_query_parameters['meta_key'] : '';

		$allowed_keys = array(
			'empty_orderby_default' => 'site_id',
			'id' => 'ID',
			'author' => 'post_author',
			'date' => 'post_date',
			'description' => 'post_content',
			'content' => 'post_content',
			'title' => 'post_title',
			'caption' => 'post_excerpt',
			'excerpt' => 'post_excerpt',
			'slug' => 'post_name',
			'name' => 'post_name',
			'modified' => 'post_modified',
			'parent' => 'post_parent',
			'menu_order' => 'menu_order',
			'mime_type' => 'post_mime_type',
			'comment_count' => 'post_content',
			'site_id' => 'site_id',
			'rand' => 'rand',
		);

		if ( empty( $orderby ) ) {
			if ( ! empty( $allowed_keys['empty_orderby_default'] ) ) {
				return array( array( 'field' => $allowed_keys['empty_orderby_default'], 'order' => $order ) ) ;
			} else {
				return array( array( 'field' => 'site_id', 'order' => $order ) ) ;
			}
		} elseif ( 'none' == $orderby ) {
			return array();
		}

		if ( ! empty( $meta_key ) ) {
			$allowed_keys[ $meta_key ] = "custom:$meta_key";
			$allowed_keys['meta_value'] = "custom:$meta_key";
			$allowed_keys['meta_value_num'] = "custom:$meta_key";
		}

		$obmatches = preg_split('/\s*,\s*/', trim(self::$all_query_parameters['orderby']));
		foreach ( $obmatches as $index => $value ) {
			$count = preg_match('/([a-z0-9_]+)(\s+(ASC|DESC))?/i', $value, $matches);
			if ( $count && ( $value == $matches[0] ) ) {
				$matches[1] = strtolower( $matches[1] );
				if ( isset( $matches[2] ) ) {
					$matches[2] = strtoupper( $matches[2] );
				}

				if ( array_key_exists( $matches[1], $allowed_keys ) ) {
					$results[] = isset( $matches[2] ) ? array( 'field' => $allowed_keys[ $matches[1] ], 'order' => trim( $matches[2] ) ) : array( 'field' => $allowed_keys[ $matches[1] ], 'order' => $order );
				} // allowed key
			} // valid column specification
		} // foreach $obmatches

//error_log( __LINE__ . ' MLAMultisiteExtensions::_validate_orderby $results = ' . var_export( $results, true ), 0 );
		return $results;
	} // _validate_orderby

	/**
	 * Compare two attachments and return:
	 *     -1 if the first is lower than the second
	 *      0 if they are equal
	 *      1 if the second is lower than the first OR if the first is NULL
	 *
	 * @since 1.02
	 *
	 * @param array $orderby ( $index => array( $field, $order ) ... )
	 * @param object $first WP_Post object
	 * @param object $second WP_Post object
	 * @param integer $level Optional, default 0; index in $orderby to use for comparison
	 */
	private static function _compare_attachments( $orderby, $first, $second, $level = 0 ) {
		if ( NULL === $first ) {
			return 1;
		}

		if ( count( $orderby ) <= $level ) {
			return -1;
		}

		$field = $orderby[ $level ]['field'];
		$order = $orderby[ $level ]['order'];

		if ( 'rand' === $field ) {
			return 51 < rand( 1, 100 ) ? -1 : 1;
		}

		if ( 'custom:' === substr( $field, 0, 7 ) ) {
			$custom = substr( $field, 7 );

			switch_to_blog( $first->site_id );
			$first_field = get_post_meta( $first->ID, $custom, true );
			restore_current_blog();

			switch_to_blog( $second->site_id );
			$second_field = get_post_meta( $second->ID, $custom, true );
			restore_current_blog();
		} else {
			$first_field = $first->{$field};
			$second_field = $second->{$field};
		}

		if ( $first_field === $second_field ) {
			return self::_compare_attachments( $orderby, $first, $second, ++$level );
		}

		if ( $first_field > $second_field ) {
			return 'DESC' === $order ? -1 : 1;
		}

		return 'DESC' === $order ? 1 : -1;
	} // _compare_attachments

	/**
	 * MLA Gallery The Attachments
	 *
	 * This filter gives you an opportunity to record or modify the array of items
	 * returned by the query.
	 *
	 * @since 1.00
	 *
	 * @param NULL $filtered_attachments initially NULL, indicating no substitution.
	 * @param array $attachments WP_Post objects returned by WP_Query->query, passed by reference
	 */
	public static function mla_gallery_the_attachments( $filtered_attachments, $attachments ) {
		if ( isset( self::$shortcode_attributes['site_id'] ) ) {
			$total_sites = 0;
			$total_attachments = 0;
			
			if ( is_array( self::$all_attachments ) ) {
				$filtered_attachments = array();
				$orderby = self::_validate_orderby();
				
				if ( isset( self::$all_query_parameters['multi_site_limit'] ) ) {
					$offset = self::$all_query_parameters['multi_site_offset'];
					$limit = self::$all_query_parameters['multi_site_limit'];
				} else {
					$offset = 0;
					$limit = 0x7FFF;
				}

				foreach( self::$all_attachments as $site_id => &$attachments ) {
					$total_sites += 1;
					
					if ( count( $attachments ) ) {

						$primary_attachments = &$filtered_attachments;
						$first = array_shift( $primary_attachments );
						unset( $filtered_attachments );
						$filtered_attachments = array();

						foreach( $attachments as $attachment ) {
							$attachment->site_id = $site_id;

							while ( 1 !== self::_compare_attachments( $orderby, $first, $attachment ) ) {
								$filtered_attachments[] = $first;
								$first = array_shift( $primary_attachments );
							}

							$filtered_attachments[] = $attachment;
						} // foreach attachment

						while ( !empty( $first ) ) {
							$filtered_attachments[] = $first;
							$first = array_shift( $primary_attachments );
						}
					} // if count attachments
					
					if ( 0 === $limit ) {
						break;
					}
				} // foreach site_id

				$filtered_attachments = array_slice ( $filtered_attachments, $offset, $limit );
				$total_attachments = count( $filtered_attachments );
				self::$all_attachments = NULL;
				$filtered_attachments['found_rows'] = count( $filtered_attachments );
				$filtered_attachments['max_num_pages'] = 0;
			} else {
				$site_id = self::$shortcode_attributes['site_id'];
				self::$attachment_count = 0;
				foreach ( $attachments as $index => &$attachment ) {
					if ( is_integer( $index ) ) {
						self::$attachment_count++;
						$attachment->site_id = $site_id;
					}
				}

				$total_sites = 1;
				$total_attachments += self::$attachment_count;
			} // single-blog query
		} else {
			// Simple query, no site_id
			$total_sites = 1;
			// Subtract 'found_rows' and 'max_num_pages' from array element count
			$total_attachments = count( $attachments ) - 2;
		}

		if ( isset( self::$all_query_parameters['multi_site_query'] ) ) {
			MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mla_gallery_the_attachments( 'multi_site_query' ) returns interim {$total_attachments} item(s) from ($total_sites} site(s)", self::MLA_DEBUG_CATEGORY );
		} else {
			MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mla_gallery_the_attachments() returns {$total_attachments} item(s) from ($total_sites} site(s)", self::MLA_DEBUG_CATEGORY );
		}
		return $filtered_attachments;
	}

	/**
	 * Save the site_id from attachment to attachment
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $current_site_id = 0;

	/**
	 * Save the total number of attachments
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $attachment_count = 0;

	/**
	 * Save the attachments from a multi-blog query; key [blog][index]
	 *
	 * @since 1.01
	 *
	 * @var	array
	 */
	private static $all_attachments = NULL;

	/**
	 * MLA Gallery Item Initial Values
	 *
	 * This filter gives you an opportunity to add custom elements to each item
	 * returned by the query item-level processing occurs.
	 *
	 * @since 1.00
	 *
	 * @param array	$markup_values gallery-level parameter_name => parameter_value pairs
	 * @param array $attachment WP_Post object of the current item
	 */
	public static function mla_gallery_item_initial_values( $markup_values, $attachment ) {
		if ( isset( $attachment->site_id ) ) {
			if ( self::$current_site_id !== intval( $attachment->site_id ) ) {
				if ( ms_is_switched() ) {
					restore_current_blog();
				}

			self::$current_site_id = intval( $attachment->site_id );
			switch_to_blog( self::$current_site_id );
			}
		}

		return $markup_values;
	} // mla_gallery_item_initial_values

	/**
	 * MLA Gallery Item Values
	 *
	 * @since 1.00
	 *
	 * @param	array	parameter_name => parameter_value pairs
	 *
	 * @return	array	updated substitution parameter name => value pairs
	 */
	public static function mla_gallery_item_values( $item_values ) {
		if ( 0 === --self::$attachment_count ) {
			if ( ms_is_switched() ) {
				restore_current_blog();
			}
		}

		//error_log( 'MLAGalleryHooksExample::mla_gallery_item_values $item_values = ' . var_export( $item_values, true ), 0 );
		return $item_values;
	} // mla_gallery_item_values

	/**
	 * MLA Media Modal Query Filtered Terms
	 *
	 * @since 1.05
	 *
	 * @param	array	$query query parameters to be passed to WP_Query
	 * @param	array	$raw_query query parameters passed in to function
	 *
	 * @return	array	updated query parameters
	 */
	public static function mla_media_modal_query_filtered_terms( $query, $raw_query ) {
		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mla_media_modal_query_filtered_terms() query = " . var_export( $query, true ), self::MLA_DEBUG_CATEGORY );
		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mla_media_modal_query_filtered_terms() raw_query = " . var_export( $raw_query, true ), self::MLA_DEBUG_CATEGORY );

		if ( !empty( $raw_query['global_media'] ) ) {
			switch_to_blog( (integer) apply_filters( 'global_media.site_id', 1 ) );
			add_action( 'mla_media_modal_query_items', 'MLAMultisiteExtensions::mla_media_modal_query_items', 10, 5 );
		}

		return $query;
	} // mla_media_modal_query_filtered_terms

	/**
	 * MLA Media Modal Query Items
	 *
	 * @since 1.05
	 *
	 * @param	object	$attachments_query WP_Query results, passed by reference
	 * @param	array	$query query parameters passed to WP_Query
	 * @param	array	$raw_query query parameters passed in to function
	 * @param	integer	$offset parameter_name => parameter_value pairs
	 * @param	integer	$count parameter_name => parameter_value pairs
	 */
	public static function mla_media_modal_query_items( $attachments_query, $query, $raw_query, $offset, $count ) {
		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mla_media_modal_query_items( {$offset}, {$count} ) query = " . var_export( $query, true ), self::MLA_DEBUG_CATEGORY );
		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mla_media_modal_query_items() raw_query = " . var_export( $raw_query, true ), self::MLA_DEBUG_CATEGORY );
		MLACore::mla_debug_add( __LINE__ . " MLAMultisiteExtensions::mla_media_modal_query_items( {$attachments_query->post_count}, {$attachments_query->found_posts} ) query_vars = " . var_export( $attachments_query->query_vars, true ), self::MLA_DEBUG_CATEGORY );

		$posts_in = array();
		foreach ( $attachments_query->posts as $post ) {
			$posts_in[] = (string) $post->ID;
		}

		$_POST['query'] = array(
			'global_media' => 'true',
			'order' => 'ASC',
			'orderby' => 'post__in',
			'post__in' => $posts_in,
			'posts_per_page' => '-1',
			'post_mime_type' => 'image',
			's' => '',
		);

		$_REQUEST['query'] = $_POST['query'];
//error_log( __LINE__ . " MLAMultisiteExtensions::mla_media_modal_query_items( {$offset}, {$count} ) query = " . var_export( $_REQUEST['query'], true ), 0 );
		restore_current_blog();

		// Control never returns from this action, which sends the JSON response and dies.
		do_action( "wp_ajax_query-attachments" );
	} // mla_media_modal_query_items
} //MLAMultisiteExtensions

// Install the filters at an early opportunity, after default-priority actions
add_action('init', 'MLAMultisiteExtensions::initialize', 11);
?>