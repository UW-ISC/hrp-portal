<?php
/**
 * Adds hierarchical path specification to the IPTC/EXIF taxonomy mapping features,
 * and has tools to copy term definitions and assignments between taxonomies.
 *
 * For example, "/Root/Parent/Child" creates or locates:
 *  - A root term named "Root"
 *  - A child term named "Parent" under "Root"
 *  - A child term named "Child" under "Parent"
 *
 * Much more information is in the Settings/MLA Path Mapping Documentation tab.
 *
 * Inspired by support topic "Help with Custom Taxonomy"
 * opened on 11/25/2017 by "wesm".
 * https://wordpress.org/support/topic/help-with-custom-taxonomy/
 *
 * Enhanced (tools) for support topic "Taxonomy in the Assistant listing"
 * opened on 10/29/2023 by "ratamatcat".
 * https://wordpress.org/support/topic/taxonomy-in-the-assistant-listing/
 *
 * @package MLA Path Mapping Example
 * @version 1.10
 */

/*
Plugin Name: MLA Path Mapping Example
Plugin URI: http://davidlingren.com/
Description: Adds hierarchical path specification to the IPTC/EXIF taxonomy mapping features, and has tools to copy term definitions and assignments between taxonomies.
Author: David Lingren
Version: 1.10
Author URI: http://davidlingren.com/

Copyright 2018-2023 David Lingren

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
 * Class MLA Path Mapping Example Adds hierarchical path specification to the IPTC/EXIF taxonomy
 * mapping features, and has tools to copy term definitions and assignments between taxonomies.
 *
 * @package MLA Path Mapping Example
 * @since 1.00
 */
class MLAPathMappingExample {
	/**
	 * Current version number
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const PLUGIN_VERSION = '1.10';

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets, scripts and settings
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'mlapathmap';

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
				'plugin_title' => 'MLA Path Mapping Example',
				'menu_title' => 'MLA Path Mapping',
				'plugin_file_name_only' => 'mla-path-mapping-example',
				'plugin_version' => self::PLUGIN_VERSION,
				'template_file' => '/admin-settings-page.tpl', // Add the path at runtime, in initialize()
				'options' => array(
					// 'slug' => array( 'type' => 'checkbox|text|select|textarea', 'default' => 'text|boolean(0|1)' )
					// See the default values in $_default_settings below
					'assign_parents' => array( 'type' => 'checkbox', 'default' => false, ),
					'assign_rule_parent' => array( 'type' => 'checkbox', 'default' => false, ),
					'path_delimiter' => array( 'type' => 'text', 'default' => '/', ),
				),
				'general_tab_values' => array(), // additional page_values for 'page-level-options' template
				'documentation_tab_values' => array(
					'plugin_title' => 'MLA Path Mapping Example',
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
		'assign_parents' => false,
		'assign_rule_parent' => false,
		'path_delimiter' => '/',
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
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		// This plugin requires MLA
		if ( ! class_exists( 'MLACore', false ) ) {
			return;
		}

		// The filters are only useful in the admin section; exit if in the "front-end" posts/pages. 
		if ( ! is_admin() )
			return;

		if ( ! ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'heartbeat' ) ) {
			MLACore::mla_debug_add( __LINE__ . ' MLAPathMappingExample::initialize() request = ' . var_export( $_REQUEST, true ), self::MLA_DEBUG_CATEGORY );
		}

		// add filters for IPTC/EXIF mapping rule execution
		add_filter( 'mla_mapping_rule', 'MLAPathMappingExample::mla_mapping_rule', 10, 4 );
		add_filter( 'mla_mapping_new_text', 'MLAPathMappingExample::mla_mapping_new_text', 10, 5 );
		add_filter( 'mla_mapping_updates', 'MLAPathMappingExample::mla_mapping_updates', 10, 5 );

		// The plugin settings class is shared with other MLA example plugins
		if ( ! class_exists( 'MLAExamplePluginSettings102', false ) ) {
			require_once( pathinfo( __FILE__, PATHINFO_DIRNAME ) . '/class-mla-example-plugin-settings-102.php' );
		}

		// Add the run-time values to the arguments
		self::$settings_arguments['template_file'] = dirname( __FILE__ ) . self::$settings_arguments['template_file'];
		self::$settings_arguments['documentation_tab_values']['settingsURL'] = admin_url('options-general.php');

		// Look for plugin-specific "tools" action 
		$current_source_taxonomy = '0';
		$current_destination_taxonomy = '0';

		if ( !empty( $_REQUEST[ self::SLUG_PREFIX . '_tools_copy_definitions'] ) ) {
			$current_source_taxonomy = $_REQUEST[ self::SLUG_PREFIX . '_tools' ]['source_taxonomy'];
			$current_destination_taxonomy = $_REQUEST[ self::SLUG_PREFIX . '_tools' ]['destination_taxonomy'];
			self::$settings_arguments['messages'] = self::mpm_copy_definitions_action( $current_source_taxonomy, $current_destination_taxonomy );
		}

		if ( !empty( $_REQUEST[ self::SLUG_PREFIX . '_tools_copy_assignments'] ) ) {
			$current_source_taxonomy = $_REQUEST[ self::SLUG_PREFIX . '_tools' ]['source_taxonomy'];
			$current_destination_taxonomy = $_REQUEST[ self::SLUG_PREFIX . '_tools' ]['destination_taxonomy'];
			self::$settings_arguments['messages'] = self::mpm_copy_assignments_action( $current_source_taxonomy, $current_destination_taxonomy );
		}

		// Create our own settings object
		self::$plugin_settings = new MLAExamplePluginSettings102( self::$settings_arguments );

		// Load template array for front-end shortcodes
		self::$page_template_array = MLACore::mla_load_template( self::$settings_arguments['template_file'], 'path' );

		// Initialize page-level values and add the run-time values to the settings
		$general_tab_values = self::$plugin_settings->get_plugin_argument('general_tab_values');

		$general_tab_values['source_taxonomy'] = self::mpm_taxonomy_options( $current_source_taxonomy );
		$general_tab_values['destination_taxonomy'] = self::mpm_taxonomy_options( $current_destination_taxonomy );
		$general_tab_values['path_delimiter'] = self::$plugin_settings->get_plugin_option('path_delimiter');

		self::$plugin_settings->update_plugin_argument('general_tab_values', $general_tab_values );
	}

	/**
	 * Compose HTML markup for taxonomy select field options
 	 *
	 * @since 1.10
	 *
	 * @param	string	$current_value Optional current selected value, default ''
	 *
	 * @return	string	HTML markup for the select field options
	 */
	public static function mpm_taxonomy_options( $current_value = '' ) {
		// Avoid fatal errors, e.g., for some AJAX calls such as "heartbeat"
		if ( ! class_exists( 'MLAData' ) ) {
			return '';
		}

		// Default option if no taxonomies exist or there is no current selection
		$option_values = array(
			'value' => '0',
			'text' => '&mdash; Pick a taxonomy &mdash;',
			'selected' => '',
		);
		$select_options = MLAData::mla_parse_template( self::$page_template_array['select-option'], $option_values );

		// Build an array of the supported taxonomies
		$supported_taxonomies = MLACore::mla_supported_taxonomies('support');
		foreach ( $supported_taxonomies as $tax_name ) {
			$tax_object = get_taxonomy( $tax_name );

			if ( false !== $tax_object ) {
				$option_values = array(
					'value' => $tax_name,
					'text' => esc_attr( $tax_object->label ),
					'selected' => $current_value === $tax_name ? 'selected=selected' : '',
				);

				$select_options .= MLAData::mla_parse_template( self::$page_template_array['select-option'], $option_values );
			}
		}

		return $select_options;
	} // mpm_taxonomy_options

	/**
	 * Assemble the full path name for a term ID, currently unused
	 *
	 * @since 1.10
	 *
	 * @param	integer	$term_id ID of the lowest-level term
	 * @param	string	$delimiter Delimiter between path components
	 *
	 * @return	string	Full path from lowest-level term up to the root
	 */
	private static function _get_term_path( $term_id, $delimiter ) {

		$current_term = self::$source_terms[ $term_id ];
		$term_path = $delimiter . $current_term['name'];

		while ( 0 !== $current_term['parent'] ) {
			$current_term = self::$source_terms[ $current_term['parent'] ];
			$term_path = $delimiter . $current_term['name'] . $term_path;
		}

		return $term_path;	
	} // _get_term_path

	/**
	 * Fetch term definitions from a source taxonomy.
	 *
	 * @since 1.10
	 *
	 * @param	string	$source_taxonomy Name/slug value for selected source taxonomy
	 *
	 * @return	integer	Count of source terms
	 */
	private static function _get_source_terms( $source_taxonomy ) {

		self::$source_terms = array();
		$source_count = 0;

		$terms = get_terms( array( 'taxonomy' => $source_taxonomy, 'hide_empty' => false, ) );
		if ( is_wp_error( $terms ) ) {
			MLACore::mla_debug_add( __LINE__ . " MLAPathMappingExample::_get_source_terms( {$source_taxonomy} ) bad taxonomy error = " . var_export( $terms ), self::MLA_DEBUG_CATEGORY );

			return $source_count;	
		}

		foreach( $terms as $term ) {
			self::$source_terms[ $term->term_id ] = array( 'name' => $term->name, 'slug' => $term->slug, 'description' => $term->description, 'parent' => $term->parent, );
			$source_count++;
		}

		// Sort by term id
		if ( ! empty( $terms ) ) {
			ksort( self::$source_terms );
		}

		MLACore::mla_debug_add( __LINE__ . " _get_source_terms( {$source_count} ) source_terms = " . var_export( self::$source_terms, true ), self::MLA_DEBUG_CATEGORY );
		return $source_count;	
	} // _get_source_terms

	/**
	 * Source taxonomy term definitions to facilitate parent term creation
	 *
	 * @since 1.10
	 *
	 * @var array $_term_slugs ( term_id => array( 'slug' -> slug, 'parent' => parent, 'description' => description )
	 *     }
	 */
	private static $source_terms = array();	

	/**
	 * Insert a destination taxonomy term and its parent(s) if they do not already exist
	 *
	 * @since 1.10
	 *
	 * @param	string	$destination_taxonomy taxonomy slug for this term
	 * @param	integer	$term_id ID/index for self::$source_terms
	 * @param	boolean	$return_object True to retirn entire term object, false (default) to return term_id.
	 *
	 * @return	integer/array	Destination term ID for this term or array( 'term_id' => $destination_term->term_id, 'term_taxonomy_id' => $destination_term->term_taxonomy_id, )
	 */
	private static function _maybe_insert_destination_term( $destination_taxonomy, $term_id, $return_array = false ) {
		$term = self::$source_terms[ $term_id ];

		$destination_term = get_term_by( 'slug', $term['slug'], $destination_taxonomy, $output = OBJECT, $filter = 'raw' );
		if( $destination_term ) {
			if ( $return_array ) {
				return array( 'term_id' => $destination_term->term_id, 'term_taxonomy_id' => $destination_term->term_taxonomy_id, );
			}

			return $destination_term->term_id;
		}

		// Make sure the full path to this term exists
		if ( 0 < $term['parent'] ) {
			$parent = self::_maybe_insert_destination_term( $destination_taxonomy, $term['parent'] );
		} else {
			$parent = 0;
		}

		// Insert the term
		$args = array(
			'description' => $term['description'],
			'slug' => $term['slug'],
			'parent' => $parent,
		);

		$results = wp_insert_term( $term['name'], $destination_taxonomy, $args );
		MLACore::mla_debug_add( __LINE__ . " MLAPathMappingExample::_maybe_insert_destination_term( {$destination_taxonomy}, {$term_id}, {$return_array} ) wp_insert_term() results = " . var_export( $results, true ), self::MLA_DEBUG_CATEGORY );

		if ( is_wp_error( $results ) ) {
			if ( $return_array ) {
				return array( 'term_id' => 0, 'term_taxonomy_id' => 0, );
			}

			return 0;
		}

		if ( $return_array ) {
			return $results;
		}

		return $results['term_id'];
	} // _maybe_insert_destination_term

	/**
	 * Store term definitions from an array of Source taxonomy terms.
	 *
	 * @since 1.10
	 *
	 * @param	$destination_taxonomy Destination taxonomy slug for this operation
	 *
	 * @uses	self::$source_terms	Source site term definition objects 
	 *
	 * @return	integer	Count of terms inserted
	 */
	private static function _put_destination_terms( $destination_taxonomy ) {
		$insert_count = 0;

		if ( MLACore::mla_taxonomy_support( $destination_taxonomy, 'support' ) ) {
			$tax_object = get_taxonomy( $destination_taxonomy );
			$flat_taxonomy = ! $tax_object->hierarchical;

			$old_count = get_terms( array( 'taxonomy' => $destination_taxonomy, 'hide_empty' => false, 'fields' => 'count', ) );
			if ( is_wp_error( $old_count ) ) {
				MLACore::mla_debug_add( __LINE__ . " MLAPathMappingExample::_put_destination_terms( {$destination_taxonomy} ) bad get_terms() error = " . var_export( $old_count ), self::MLA_DEBUG_CATEGORY );
				return 0;
			}

			$delimiter = $_REQUEST['mlapathmap_tools']['path_delimiter'];
			foreach( self::$source_terms as $term_id => $term ) {
				if ( $flat_taxonomy ) {
					self::$source_terms[ $term_id ]['parent'] = 0;
				}

				$new_id = self::_maybe_insert_destination_term( $destination_taxonomy, $term_id );
			} // foreach term

			$insert_count = get_terms( array( 'taxonomy' => $destination_taxonomy, 'hide_empty' => false, 'fields' => 'count', ) ) - $old_count;
		}

		return $insert_count;
	} // _put_destination_terms

	/**
	 * Copy term definitions from a source taxonomy to a destination taxonomy.
	 *
	 * @since 1.10
	 *
	 * @param	$source_taxonomy Source taxonomy slug for this operation
	 * @param	$destination_taxonomy Destination taxonomy slug for this operation
	 *
	 * @return	string	action-specific message(s), e.g., summary of results
	 */
	public static function mpm_copy_definitions_action( $source_taxonomy, $destination_taxonomy ) {
		MLACore::mla_debug_add( __LINE__ . " MLAPathMappingExample::mpm_copy_definitions_action( {$source_taxonomy}, $destination_taxonomy} )", self::MLA_DEBUG_CATEGORY );

		if ( '0' === $source_taxonomy ) {
			$messages = 'No Source Taxonomy selected, nothing copied.';
		} elseif ( $destination_taxonomy === $source_taxonomy ) {
			$messages = 'Source and Destination are the same, nothing copied.';
		} else {
			$messages = '';

			// MLAObjects::initialize hasn't run yet
			MLACore::mla_initialize_tax_checked_on_top();

			$source_count = self::_get_source_terms( $source_taxonomy );
			MLACore::mla_debug_add( __LINE__ . " MLAPathMappingExample::mpm_copy_definitions_action( {$source_count} )", self::MLA_DEBUG_CATEGORY );
			$messages .= sprintf( 'Source %1$s - gathered %2$d term definitions.<br />', $source_taxonomy, $source_count ) . "\r\n";

			if ( '0' === $destination_taxonomy ) {
				$messages .= 'No Destination Taxonomy selected, nothing copied.';
			} else {
				$term_inserts = self::_put_destination_terms( $destination_taxonomy );
				$messages .= sprintf( 'Destination %1$s - inserted %2$d term definitions.<br />', $destination_taxonomy, $term_inserts ) . "\r\n";
			}

			// Invalidate MLA's cached "Attachment" column counts because things have changed
			delete_transient( MLA_OPTION_PREFIX . 't_term_counts_' . $destination_taxonomy );
		} // good $source_taxonomy

		MLACore::mla_debug_add( __LINE__ . " MLAPathMappingExample::mme_copy_settings_action() return messages = " . var_export( $messages, true ), self::MLA_DEBUG_CATEGORY );
		return $messages;		
	} // mpm_copy_definitions_action

	/**
	 * Copy term assignments for one attachment from a source taxonomy to a destination taxonomy.
	 *
	 * @since 1.10
	 *
	 * @param	integer	$attachment_id ID for the attachment
	 * @param	string	$source_taxonomy Source taxonomy slug for this operation
	 * @param	string	$destination_taxonomy Destination taxonomy slug for this operation
	 *
	 * @return	integer	Count of term assignments performed
	 */
	private static function _copy_term_assignments( $attachment_id, $source_taxonomy, $destination_taxonomy ) {
		static $flat_taxonomy = NULL;

		if ( NULL === $flat_taxonomy ) {
			if ( MLACore::mla_taxonomy_support( $destination_taxonomy, 'support' ) ) {
				$tax_object = get_taxonomy( $destination_taxonomy );
				$flat_taxonomy = ! $tax_object->hierarchical;
			} else {
				$flat_taxonomy = false;
			}
		}

		// Get the current terms
		$current_terms = get_object_term_cache( $attachment_id, $source_taxonomy );
		if ( false === $current_terms ) {
			$current_terms = wp_get_object_terms( $attachment_id, $source_taxonomy );
			wp_cache_add( $attachment_id, $current_terms, $source_taxonomy . '_relationships' );
		}
		MLACore::mla_debug_add( __LINE__ . " MLAPathMappingExample::_copy_term_assignments( {$attachment_id}, {$source_taxonomy} ) current terms = " . var_export( $current_terms, true ), self::MLA_DEBUG_CATEGORY );

		$destination_terms = array();
		foreach ( $current_terms as $index => $term ) {
			if ( isset( self::$source_terms[ $term->term_id ]['destination_ttid'] ) ) {
				$destination_terms[] = self::$source_terms[ $term->term_id ]['destination_ttid'];
			} else {
				$new_term = self::_maybe_insert_destination_term( $destination_taxonomy, $term->term_id, true );
				self::$source_terms[ $term->term_id ]['destination_ttid'] = $new_term['term_taxonomy_id'];
				$destination_terms[] = (integer) $new_term['term_taxonomy_id'];
			}
		}

		MLACore::mla_debug_add( __LINE__ . " MLAPathMappingExample::_copy_term_assignments() destination_terms = " . var_export( $destination_terms, true ), self::MLA_DEBUG_CATEGORY );
		$results = wp_set_post_terms( $attachment_id, $destination_terms, $destination_taxonomy, true );
		MLACore::mla_debug_add( __LINE__ . " MLAPathMappingExample::_copy_term_assignments( {$attachment_id}, {$destination_taxonomy} ) destination terms = " . var_export( $results, true ), self::MLA_DEBUG_CATEGORY );

		return count( $results );
	} // _copy_term_assignments

	/**
	 * Copy term assignments for all attachments from a source taxonomy to a destination taxonomy.
	 *
	 * @since 1.10
	 *
	 * @param	string	$source_taxonomy Source taxonomy slug for this operation
	 * @param	string	$destination_taxonomy Destination taxonomy slug for this operation
	 *
	 * @return	string	action-specific message(s), e.g., summary of results
	 */
	public static function mpm_copy_assignments_action( $source_taxonomy, $destination_taxonomy ) {
		global $wpdb;
		MLACore::mla_debug_add( __LINE__ . " MLAPathMappingExample::mpm_copy_assignments_action( {$source_taxonomy}, $destination_taxonomy} )", self::MLA_DEBUG_CATEGORY );

		if ( '0' === $source_taxonomy ) {
			$messages = 'No Source Taxonomy selected, nothing copied.';
		} elseif ( $destination_taxonomy === $source_taxonomy ) {
			$messages = 'Source and Destination are the same, nothing copied.';
		} else {
			$messages = '';

			$source_count = self::_get_source_terms( $source_taxonomy );
			MLACore::mla_debug_add( __LINE__ . " MLAPathMappingExample::mpm_copy_assignments_action( {$source_count} )", self::MLA_DEBUG_CATEGORY );

			$messages .= sprintf( 'Source %1$s - gathered %2$d term definitions.<br />', $source_taxonomy, $source_count ) . "\r\n";

			if ( '0' === $destination_taxonomy ) {
				$messages .= 'No Destination Taxonomy selected, nothing copied.';
			} else {
				// MLAObjects::initialize hasn't run yet
				MLACore::mla_initialize_tax_checked_on_top();

				$attachment_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_status = 'inherit'" );

				$terms_assigned = 0;
				foreach ( $attachment_ids as $attachment_id ) {
					$terms_assigned += self::_copy_term_assignments($attachment_id, $source_taxonomy, $destination_taxonomy );
				} // each ID

				$messages .= sprintf( 'Copied %1$d assignments from %2$s to %3$s for %4$d attachments.<br />', $terms_assigned, $source_taxonomy, $destination_taxonomy, count( $attachment_ids ) ) . "\r\n";

			// Invalidate MLA's cached "Attachment" column counts because things have changed
			delete_transient( MLA_OPTION_PREFIX . 't_term_counts_' . $destination_taxonomy );
			} // good destination taxonomy
		} // good $source_taxonomy

		MLACore::mla_debug_add( __LINE__ . " MLAPathMappingExample::mpm_copy_assignments_action() return messages = " . var_export( $messages, true ), self::MLA_DEBUG_CATEGORY );
		return $messages;		
	} // mpm_copy_assignments_action

	/**
	 * MLA Mapping Rule Filter
	 *
	 * This filter is called once for each mapping rule, before the rule
	 * is evaluated. You can change the rule parameters, or prevent rule
	 * evaluation by returning $setting_value['data_source'] = 'none'; 
	 *
	 * @since 1.00
	 *
	 * @param	array 	custom_field_mapping rule
	 * @param	integer post ID to be evaluated
	 * @param	string 	category/scope to evaluate against: custom_field_mapping or single_attachment_mapping
	 * @param	array 	attachment_metadata, default NULL
	 *
	 * @return	array	updated custom_field_mapping rule
	 */
	public static function mla_mapping_rule( $setting_value, $post_id, $category, $attachment_metadata ) {
		if ( $setting_value['active'] ) {
			//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_rule_filter( {$post_id}, {$category} ) setting_value = " . var_export( $setting_value, true ), 0 );
			//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_rule_filter( {$post_id}, {$category} ) attachment_metadata = " . var_export( $attachment_metadata, true ), 0 );

			self::$current_rule = $setting_value;
		}

		return $setting_value;
	} // mla_mapping_rule_filter

	/**
	 * Save the mapping rule so mla_mapping_new_text can use, e.g., parent
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $current_rule = array();

	/**
	 * MLA Mapping New Text Filter
	 *
	 * This filter is called once for each IPTC/EXIF mapping rule, after the selection
	 * between the IPTC and EXIF values has been made. You can change the new value
	 * produced by the rule.
	 *
	 * @since 1.00
	 *
	 * @param	mixed 	string or array value returned by the rule
	 * @param	string 	rule key - standard field slug, taxonomy slug or custom field name
	 * @param	integer post ID to be evaluated
	 * @param	string 	category/scope to evaluate against: iptc_exif_standard_mapping, iptc_exif_taxonomy_mapping or iptc_exif_custom_mapping
	 * @param	array 	attachment_metadata, default NULL
	 *
	 * @return	array	updated rule EXIF/Template value
	 */
	public static function mla_mapping_new_text( $new_text, $setting_key, $post_id, $category, $attachment_metadata ) {
		// $term_cache = array( [ taxonomy/setting_key ][ parent term_id ][ term name ] => array( term_id, term_taxonomy_id )
		// $unqualified_cache = array( [ taxonomy/setting_key ][ term name ] => array( term_id, term_taxonomy_id )
		// $rule_parent = array( [ taxonomy/setting_key ] => WP_Term object )
		static $term_cache = array(), $unqualified_cache = array(), $rule_parent = array();

		// We only care about taxonomies
		if ( 'iptc_exif_taxonomy_mapping' !== $category ) {
			return  $new_text;
		}

		// We only care about hierarchical taxonomies
		if ( !is_taxonomy_hierarchical( $setting_key ) ) {
			return  $new_text;
		}

		//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_new_text_filter( {$setting_key}, {$post_id}, {$category} ) new_text = " . var_export( $new_text, true ), 0 );
		//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_new_text_filter( {$setting_key}, {$post_id}, {$category} ) attachment_metadata = " . var_export( $attachment_metadata, true ), 0 );

		// Make sure $new_text is an array, even if it's empty
		if ( empty( $new_text ) ) {
			$new_text = array();
		} elseif ( is_string( $new_text ) ) {
			$new_text = array( $new_text );
		}

		$term_parent = !empty( self::$current_rule['parent'] ) ? absint( self::$current_rule['parent'] ) : 0;
		$assign_rule_parent = $term_parent ? self::$plugin_settings->get_plugin_option('assign_rule_parent') : false;
		$assign_parents = self::$plugin_settings->get_plugin_option('assign_parents');
		$path_delimiter = self::$plugin_settings->get_plugin_option('path_delimiter');

		// Check for and validate Rule Parent
		if ( $assign_rule_parent && empty( $rule_parent[ $setting_key ] ) ) {
			$term = get_term( $term_parent, $setting_key );
			if ( !empty( $term ) && !is_wp_error( $term ) ) {
				$rule_parent[ $setting_key ] = $term;
			} else {
				$term_parent = 0;
			}
		}

		// We must build the "public" cache from scratch for each item
		MLAOptions::$mla_term_cache[ $setting_key ][ $term_parent ] = array();

		// Initialize the higher-level cache elements to simplify the code
		if ( !isset( $unqualified_cache[ $setting_key ] ) ) {
			$unqualified_cache[ $setting_key ] = array();
		}

		if ( !isset( $term_cache[ $setting_key ] ) ) {
			$term_cache[ $setting_key ] = array();
		}

		if ( !isset( $term_cache[ $setting_key ][ $term_parent ] ) ) {
			$term_cache[ $setting_key ][ $term_parent ] = array();
		}

		/*
		 * Every element in $new_text must be added, as entered, to the $mla_term_cache
		 * so it is matched in MLAOptions::_get_term_id.
		 * An internal $term_cache holds each "path component" with the actual parent location.
		 * The $unqualified_cache holds each "path component" when the parent is unknown.
		 */
		foreach ( $new_text as $term_name ) {
			// WordPress encodes special characters, e.g., "&" as HTML entities in term names
			$term_name = _wp_specialchars( $term_name );

			// Is this a simple, unqualified term already in the cache?
			if ( isset( $unqualified_cache[ $setting_key ][ $term_name ] ) ) {
//error_log( __LINE__ . " mla_mapping_new_text found $term_name in unqualified_cache", 0 );
				MLAOptions::$mla_term_cache[ $setting_key ][ $term_parent ][ $term_name ] = $unqualified_cache[ $setting_key ][ $term_name ]['term_id'];
				continue;
			}

			// Break the path, if present, into its component parts
			$path = explode( $path_delimiter, $term_name );
//error_log( __LINE__ . " mla_mapping_new_text ( {$path_delimiter}, {$term_name} ) path = " . var_export( $path, true ), 0 );

			// Check for an absolute path, initialize $current_id
			$unqualified_name = false;
			if ( empty( $path[0] ) ) {
				$current_parent = 0; // Root-level "parent"
			} elseif ( 0 === $term_parent ) {
				// Unqualified name - no parent restriction
				$unqualified_name = true;
				$current_parent = 'unqualified';
			} else {
				$current_parent = $term_parent;
			}
//error_log( __LINE__ . " mla_mapping_new_text parent = " . var_export( $current_parent, true ) . ", path = " . var_export( $path, true ), 0 );

			// Holder for the $assign_parents entries
			$assign_parents_entry = 0;

			// Find or create all the path components and add them to the cache
			foreach ( $path as $path_name ) {
				// Check for a parent assignment
				if ( 0 < $assign_parents_entry ) {
					// Generate an unlikely name, create a new entry
					$name = "assign_parent-term-{$assign_parents_entry}";
					MLAOptions::$mla_term_cache[ $setting_key ][ $term_parent ][ $name ] = $assign_parents_entry;
					$new_text[] = $name;
					$assign_parents_entry = 0;
				}

				// Ignore initial or duplicate delimiters
				if ( empty( $path_name ) ) {
//error_log( __LINE__ . " mla_mapping_new_text ignoring empty path_name", 0 );
					continue;
				}

				// Is this component in the cache?
				if ( isset( $term_cache[ $setting_key ][ $current_parent ][ $path_name ] ) ) {
//error_log( __LINE__ . " mla_mapping_new_text found $path_name under $current_parent in cache", 0 );
					$current_parent = $term_cache[ $setting_key ][ $current_parent ][ $path_name ]['term_id'];

					if ( $assign_parents ) {
						$assign_parents_entry = $current_parent;
					}

					continue;
				}

				// Does this component exist?
				if ( $unqualified_name ) {
					// Is this component in the cache?
					if ( isset( $unqualified_cache[ $setting_key ][ $path_name ] ) ) {
//error_log( __LINE__ . " mla_mapping_new_text found $path_name in unqualified cache", 0 );
						$path_term = $unqualified_cache[ $setting_key ][ $path_name ];
					} else {
						$path_term = term_exists( $path_name, $setting_key );

						if ( $path_term !== 0 && $path_term !== NULL ) {
//error_log( __LINE__ . " mla_mapping_new_text adding $path_name to unqualified cache", 0 );
							$unqualified_cache[ $setting_key ][ $path_name ] = $path_term;
						}
					}

					$unqualified_name = false;
				} else {
					$path_term = term_exists( $path_name, $setting_key, $current_parent );
				}

				if ( $path_term !== 0 && $path_term !== NULL ) {
//error_log( __LINE__ . " mla_mapping_new_text found $path_name under $current_parent in database = " . var_export( $path_term, true ), 0 );
					$term_cache[ $setting_key ][ $current_parent ][ $path_name ] = $path_term;
					$current_parent = absint( $path_term['term_id'] );

					if ( $assign_parents ) {
						$assign_parents_entry = $current_parent;
					}

					continue;
				}

				// Create the term 
				$path_term = wp_insert_term( $path_name, $setting_key, array( 'parent' => $current_parent ) );
				if ( ( ! is_wp_error( $path_term ) ) && isset( $path_term['term_id'] ) ) {
//error_log( __LINE__ . " mla_mapping_new_text created $path_name under $current_parent = " . var_export( $path_term, true ), 0 );
					$term_cache[ $setting_key ][ $current_parent ][ $path_name ] = $path_term;
					$current_parent = absint( $path_term['term_id'] );

					if ( $assign_parents ) {
						$assign_parents_entry = $current_parent;
					}
				}
			} // foreach path component

			// Finally, create a public entry based on the (path and) name as entered
			MLAOptions::$mla_term_cache[ $setting_key ][ $term_parent ][ $term_name ] = $current_parent;
		} // foreach term name

		// Create an entry with an unlikely name for the Rule Parent, if selected
		if ( $assign_rule_parent && isset( $rule_parent[ $setting_key ] ) ) {
			MLAOptions::$mla_term_cache[ $setting_key ][ $term_parent ]['rule_parent-term'] = $rule_parent[ $setting_key ]->term_id;
			$new_text[] = 'rule_parent-term';			
		}

//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_new_text_filter( {$setting_key}, {$post_id}, {$category} ) new text = " . var_export( $new_text, true ), 0 );
//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_new_text_filter( {$setting_key}, {$post_id}, {$category} ) mla term cache = " . var_export( MLAOptions::$mla_term_cache, true ), 0 );
		return  $new_text;
	} // mla_mapping_new_text_filter

	/**
	 * MLA Mapping Updates Filter
	 *
	 * This filter is called AFTER all mapping rules are applied.
	 * You can add, change or remove updates for the attachment's
	 * standard fields, taxonomies and/or custom fields.
	 *
	 * @since 1.00
	 *
	 * @param	array	updates for the attachment's standard fields, taxonomies and/or custom fields
	 * @param	integer post ID to be evaluated
	 * @param	string 	category/scope to evaluate against: custom_field_mapping or single_attachment_mapping
	 * @param	array 	mapping rules
	 * @param	array 	attachment_metadata, default NULL
	 *
	 * @return	array	updated attachment's updates
	 */
	public static function mla_mapping_updates( $updates, $post_id, $category, $settings, $attachment_metadata ) {
		//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_updates_filter( {$post_id}, {$category} ) updates = " . var_export( $updates, true ), 0 );
		//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_updates_filter( {$post_id}, {$category} ) settings = " . var_export( $settings, true ), 0 );
		//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_updates_filter( {$post_id}, {$category} ) attachment_metadata = " . var_export( $attachment_metadata, true ), 0 );

		// To stop this rule's updates, return an empty array, i.e., return array();
		return $updates;
	} // mla_mapping_updates_filter
} //MLAPathMappingExample

/*
 * Install the filters at an early opportunity
 */
add_action('init', 'MLAPathMappingExample::initialize');
?>