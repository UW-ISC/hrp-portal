<?php
/**
 * Duplicate a Media Library item, including term assignments and custom fields
 *
 * Much more information is in the Settings/MLA Duplicate "Documentation" tab.
 *
 * Created for support topic "Feature request: Duplicate media"
 * opened on 7/6/2023 by "utrenkner".
 * https://wordpress.org/support/topic/feature-request-duplicate-media/
 *
 * @package MLA Duplicate Item
 * @version 1.02
 */

/*
Plugin Name: MLA Duplicate Item
Plugin URI: http://davidlingren.com/
Description: Duplicate a Media Library item, including term assignments and custom fields
Author: David Lingren
Version: 1.02
Author URI: http://davidlingren.com/

Copyright 2023 David Lingren

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
 * Class MLA Duplicate Item implements an empty Settings screen
 *
 * Call it anything you want, but give it an unlikely and hopefully unique name. Hiding everything
 * else inside a class means this is the only name you have to worry about.
 *
 * @package MLA Duplicate Item
 * @since 1.00
 */
class MLADuplicateItem {
	/**
	 * Current version number
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const PLUGIN_VERSION = '1.02';

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets, scripts and settings
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'MLADuplicateItem';

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
				'plugin_title' => 'MLA Duplicate Item',
				'menu_title' => 'MLA Duplicate',
				'plugin_file_name_only' => 'mla-duplicate-item',
				'plugin_version' => self::PLUGIN_VERSION,
				'template_file' => '/admin-settings-page.tpl', // Add the path at runtime, in initialize()
				'messages' => '',
				'options' => array(
					// 'slug' => array( 'type' => 'checkbox|text|select|textarea', 'default' => 'text|boolean(0|1)' )
					// See the default values in $_default_settings below
					'perform_mapping' => array( 'type' => 'checkbox', 'default' => true ),
					'duplicate_terms' => array( 'type' => 'checkbox', 'default' => true ),
					'duplicate_custom_fields' => array( 'type' => 'checkbox', 'default' => true ),
				),
				'general_tab_values' => array(), // additional page_values for 'page-level-options' template
				'documentation_tab_values' => array(
					'plugin_title' => 'MLA Duplicate Item',
					'settingsURL' => '', // Set at runtime in initialize()
				), // page_values for 'documentation-tab' template
			);

	/**
	 * Default processing options
	 *
	 * @since 1.00
	 *
	 * @var array $_default_settings {
	 *     @type boolean $perform_mapping Perform MLA mapping rules during new item sideload
	 *     @type boolean $duplicate_terms Duplicate taxonomy term assignments
	 *     @type boolean $duplicate_custom_fields Duplicate custom field values
	 *     }
	 */
	private static $_default_settings = array (
					'perform_mapping' => true,
					'duplicate_terms' => true,
					'duplicate_custom_fields' => true,
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

		// The plugin settings class is shared with other MLA example plugins
		if ( ! class_exists( 'MLAExamplePluginSettings102' ) ) {
			require_once( pathinfo( __FILE__, PATHINFO_DIRNAME ) . '/class-mla-example-plugin-settings-102.php' );
		}

		// Add the run-time values to the arguments
		self::$settings_arguments['template_file'] = dirname( __FILE__ ) . self::$settings_arguments['template_file'];
		self::$settings_arguments['documentation_tab_values']['settingsURL'] = admin_url('options-general.php');

		// Create our own settings object
		self::$plugin_settings = new MLAExamplePluginSettings102( self::$settings_arguments );
		
		// The remaining filters are only useful in the admin section; exit if in the "front-end" posts/pages. 
		if ( ! is_admin() ) {
			return;
		}

		// Load template array and initialize page-level values.
		self::$page_template_array = MLACore::mla_load_template( self::$settings_arguments['template_file'], 'path' );

		$general_tab_values = self::$plugin_settings->get_plugin_argument('general_tab_values');
		// Add the run-time values to the settings
		// self::$plugin_settings->update_plugin_argument('general_tab_values', $general_tab_values );
		
		add_filter( 'mla_list_table_build_rollover_actions', 'MLADuplicateItem::mla_list_table_build_rollover_actions', 10, 3 );

		// This action is run before any Media/Assistant output is generated
		add_filter( 'mla_list_table_custom_single_action', 'MLADuplicateItem::mla_list_table_custom_single_action', 10, 3 );
	} // initialize

	/**
	 * Local variable
	 *
	 * @since 1.00
	 *
	 * @var mixed	index of CSV match variable, if found, else false.
	 */
	private static $_local_variable = false;

	/**
	 * MLA Mapping Settings Filter
	 *
	 * This filter is called before any mapping rules are executed.
	 * You can add, change or delete rules from the array.
	 *
	 * @since 1.00
	 *
	 * @param	array 	$settings mapping rules
	 * @param	integer $post_id ID of the attachment to be evaluated
	 * @param	string 	$category category/scope to evaluate against, e.g., iptc_exif_mapping or single_attachment_mapping
	 * @param	array 	$attachment_metadata attachment_metadata, default NULL
	 *
	 * @return	array	updated mapping rules
	 */
	public static function mla_mapping_settings( $settings, $post_id, $category, $attachment_metadata ) {
		//error_log( __LINE__ . " MLADuplicateItem::mla_mapping_settings( {$post_id}, {$category} ) settings keys = " . var_export( array_keys( $settings ), true ), 0 );
		//error_log( __LINE__ . " MLADuplicateItem::mla_mapping_settings( {$post_id}, {$category} ) attachment_metadata = " . var_export( $attachment_metadata, true ), 0 );

		switch ( $category ) {
			case 'iptc_exif_mapping':
				return array( 'standard' => array(), 'taxonomy' => array(), 'custom' => array() );
			case 'single_attachment_mapping':
				return array();
		}

		return $settings;
	} // mla_mapping_settings

	/**
	 * Creates new items from an existing item.
	 *
	 * @since 1.00
	 *
	 * @param	integer	$original_item_id the source attachment.
	 *
	 * @return	string	Error or status message(s)
	 */
	public static function mdi_duplicate_item( $original_item_id ) {
		$item_prefix = sprintf( 'Item %1$d', $original_item_id ) . ', ';

		$path = false;

		// WP 5.3+ produces "scaled" images without metadata; we need the original.
		if ( function_exists( 'wp_get_original_image_path' ) ) {
			$path = wp_get_original_image_path( $original_item_id );
		}

		if ( false === $path ) {
			$path = get_attached_file( $original_item_id );
		}

		if ( empty( $path ) ) {
			MLACore::mla_debug_add( __LINE__ . " MLADuplicateItem::mdi_duplicate_item( {$original_item_id} ) no attached file.", MLADuplicateItem::MLA_DEBUG_CATEGORY );
			return sprintf( 'ERROR: %1$sno attached file.', $item_prefix );
		} else {
			if ( !file_exists( $path ) ) {
				MLACore::mla_debug_add( __LINE__ . " MLADuplicateItem::mdi_duplicate_item( {$original_item_id}, {$path} ) file does not exist.", MLADuplicateItem::MLA_DEBUG_CATEGORY );
				return sprintf( 'ERROR: %1$s%2$sdoes not exist.', $item_prefix, $path );
			}
		}

		// wp_handle_sideload moves/deletes the original file  TODO Replace with copying the original before sideload
		$temp_file = wp_tempnam();
		copy( $path, $temp_file );

		// Get the file's post information as an associative array
		$item_data = get_post( $original_item_id, ARRAY_A );

		// array based on $_FILE as seen in PHP file uploads
		$file_info = array(
			'name' => basename( $path ),
			'type' => $item_data['post_mime_type'],
			'tmp_name' => $temp_file,
			'error' => 0,
			'size' => filesize( $path ),
		);
		MLACore::mla_debug_add( __LINE__ . " MLADuplicateItem::mdi_duplicate_item( {$original_item_id} ) file_info = " . var_export( $file_info, true ), MLADuplicateItem::MLA_DEBUG_CATEGORY );

		$overrides = array( 'test_form' => false, 'test_size' => true, 'test_upload' => true, );

		// copy the file into the uploads directory
		$results = wp_handle_sideload( $file_info, $overrides );
		MLACore::mla_debug_add( __LINE__ . " MLADuplicateItem::mdi_duplicate_item( {$original_item_id} ) sideload results = " . var_export( $results, true ), MLADuplicateItem::MLA_DEBUG_CATEGORY );

		// Eliminate obsolete elements of the old item
		unset( $item_data['ID'] );
		unset( $item_data['post_author'] );
		unset( $item_data['post_date'] );
		unset( $item_data['post_date_gmt'] );
		unset( $item_data['post_name'] );
		unset( $item_data['post_modified'] );
		unset( $item_data['post_modified_gmt'] );
		$item_parent = $item_data['post_parent'];
		unset( $item_data['post_parent'] );
		$item_data['guid'] = $results['url'];
		$item_data['post_mime_type'] = $results['type'];
		unset( $item_data['comment_count'] );
		unset( $item_data['ancestors'] );
		unset( $item_data['post_category'] );
		unset( $item_data['tags_input'] );
		MLACore::mla_debug_add( __LINE__ . " MLADuplicateItem::mdi_duplicate_item( {$original_item_id} ) retained item_data = " . var_export( $item_data, true ), MLADuplicateItem::MLA_DEBUG_CATEGORY );

		$perform_mapping = self::$plugin_settings->get_plugin_option('perform_mapping');
		if ( false === $perform_mapping ) {
			add_filter( 'mla_mapping_settings', 'MLADuplicateItem::mla_mapping_settings', 10, 4 );
		}

		// Insert the new attachment.
		$new_item_id = wp_insert_attachment( $item_data, $results['file'], $item_parent );
		if ( empty( $new_item_id ) ) {
			MLACore::mla_debug_add( __LINE__ . " MLADuplicateItem::mdi_duplicate_item( {$original_item_id}, {$path} ) wp_insert_attachment failed.", MLADuplicateItem::MLA_DEBUG_CATEGORY );
			return sprintf( 'ERROR: %2$swp_insert_attachment failed.', $item_prefix );
		}

		// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Update the metadata for the new attachment.
		$item_data = wp_generate_attachment_metadata( $new_item_id, $results['file']);
		MLACore::mla_debug_add( __LINE__ . " MLADuplicateItem::mdi_duplicate_item( {$original_item_id} ) item metadata = " . var_export( $item_data, true ), MLADuplicateItem::MLA_DEBUG_CATEGORY );
		wp_update_attachment_metadata( $new_item_id, $item_data );

		if ( false === $perform_mapping ) {
			remove_filter( 'mla_mapping_settings', 'MLADuplicateItem::mla_mapping_settings', 10 );
		}

		if ( self::$plugin_settings->get_plugin_option('duplicate_terms') ) {
			$supported_taxonomies = MLACore::mla_supported_taxonomies('support');
			foreach( $supported_taxonomies as $taxonomy ) {
				$terms = get_object_term_cache( $original_item_id, $taxonomy );
				if ( false === $terms ) {
					$terms = wp_get_object_terms( $original_item_id, $taxonomy );
					wp_cache_add( $original_item_id, $terms, $taxonomy . '_relationships' );
				}
			
				$terms = wp_list_pluck( $terms, 'slug' );
				MLACore::mla_debug_add( __LINE__ . " MLADuplicateItem::mdi_duplicate_item( {$original_item_id}, {$taxonomy} ) term slugs = " . var_export( $terms, true ), MLADuplicateItem::MLA_DEBUG_CATEGORY );
				$result = wp_set_object_terms( $new_item_id, $terms, $taxonomy );
				MLACore::mla_debug_add( __LINE__ . " MLADuplicateItem::mdi_duplicate_item( {$original_item_id}, {$taxonomy} ) term results = " . var_export( $result, true ), MLADuplicateItem::MLA_DEBUG_CATEGORY );
			}
		}

		if ( self::$plugin_settings->get_plugin_option('duplicate_custom_fields') ) {
			$post_meta = MLAQuery::mla_fetch_attachment_metadata( $original_item_id );
			MLACore::mla_debug_add( __LINE__ . " MLADuplicateItem::mdi_duplicate_item( {$original_item_id} ) MLA post_meta = " . var_export( $post_meta, true ), MLADuplicateItem::MLA_DEBUG_CATEGORY );

			foreach( $post_meta as $key => $value ) {
				switch ( $key ) {
					case 'mla_thumbnail_id':
						$key = '_thumbnail_id';
						break;
					case 'mla_wp_attachment_image_alt':
						$key = '_wp_attachment_image_alt';
						break;
					case 'mla_wp_attached_file':
					case 'mla_wp_attachment_metadata':
					case 'mla_wp_attached_path':
					case 'mla_wp_attached_filename':
						continue 2;
					default:
						if ( 0 === strpos( $key, 'mla_item_' ) ) {
							$key = substr( $key, 9 );
						}
				}
			
				$result = update_metadata( 'post', $new_item_id, $key, $value );
				MLACore::mla_debug_add( __LINE__ . " MLADuplicateItem::mdi_duplicate_item( {$new_item_id}, {$key} ) result = " . var_export( $result, true ), MLADuplicateItem::MLA_DEBUG_CATEGORY );
			}
		} else {
			$post_meta = get_metadata( 'post', $original_item_id, '_thumbnail_id' );
			if ( !empty( $post_meta[0] ) ) {
				MLACore::mla_debug_add( __LINE__ . " MLADuplicateItem::mdi_duplicate_item( {$original_item_id}, _thumbnail_id ) post_meta = " . var_export( $post_meta, true ), MLADuplicateItem::MLA_DEBUG_CATEGORY );

				$result = update_metadata( 'post', $new_item_id, '_thumbnail_id', $post_meta[0] );
				MLACore::mla_debug_add( __LINE__ . " MLADuplicateItem::mdi_duplicate_item( {$new_item_id}, _thumbnail_id ) result = " . var_export( $result, true ), MLADuplicateItem::MLA_DEBUG_CATEGORY );
			}
	
			$post_meta = get_metadata( 'post', $original_item_id, '_wp_attachment_image_alt' );
			MLACore::mla_debug_add( __LINE__ . " MLADuplicateItem::mdi_duplicate_item( {$original_item_id}, _wp_attachment_image_alt ) post_meta = " . var_export( $post_meta, true ), MLADuplicateItem::MLA_DEBUG_CATEGORY );

			if ( !empty( $post_meta[0] ) ) {
				$result = update_metadata( 'post', $new_item_id, '_wp_attachment_image_alt', $post_meta[0] );
				MLACore::mla_debug_add( __LINE__ . " MLADuplicateItem::mdi_duplicate_item( {$new_item_id}, _wp_attachment_image_alt ) result = " . var_export( $result, true ), MLADuplicateItem::MLA_DEBUG_CATEGORY );
			}
		}

		$item_content = sprintf( '%1$sduplicated as new item %2$d.', $item_prefix, $new_item_id );

		return $item_content;
	} // mdi_duplicate_item

	/**
	 * Filter the list of item "Rollover" actions
	 *
	 * This filter gives you an opportunity to filter the list of "Rollover" actions
	 * giving item-level links such as "Quick Edit", "Move to Trash".
	 *
	 * @since 1.00
	 *
	 * @param	array	$actions	The list of item "Rollover" actions.
	 * @param	object	$item		The current Media Library item.
	 * @param	string	$column		The List Table column slug.
	 */
	public static function mla_list_table_build_rollover_actions( $actions, $item, $column ) {
		//error_log( __LINE__ . " MLADuplicateItem::mla_list_table_build_rollover_actions ({$column}) \$actions = " . var_export( $actions, true ), 0 );
		//error_log( __LINE__ . " MLADuplicateItem::mla_list_table_build_rollover_actions ({$column}) \$item = " . var_export( $item, true ), 0 );

		$query_args = array( 'page' => MLACore::ADMIN_PAGE_SLUG, 'mla_admin_action' => 'duplicate_item', 'mla_item_ID' => $item->ID );
		$actions['duplicate'] = '<a href="' . add_query_arg( $query_args, MLACore::mla_nonce_url( 'upload.php', MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME ) ) . '" title="Duplicate this item">Duplicate</a>';

		return $actions;
	} // mla_list_table_build_rollover_actions

	/**
	 * Process an MLA_List_Table custom admin action
	 *
	 * This filter gives you an opportunity to process an MLA_List_Table item-level action
	 * that MLA does not recognize. This filter is called before anything is output for the
	 * Media/Assistant submenu, so you can redirect to another admin screen if desired.
	 *
	 * @since 1.00
	 *
	 * @param	NULL	$page_content 		NULL, indicating no handler.
	 * @param	string	$mla_admin_action	The requested action.
	 * @param	integer	$mla_item_ID		Zero (0), or the affected attachment.
	 */
	public static function mla_list_table_custom_single_action( $page_content, $mla_admin_action, $mla_item_ID ) {
		//error_log( __LINE__ . " MLADuplicateItem::mla_list_table_custom_single_action( {$mla_admin_action}, {$mla_item_ID} )", 0 );
		
		if ( 'duplicate_item' === $mla_admin_action ) {
			$page_content = array(
				'message' => MLADuplicateItem::mdi_duplicate_item( $mla_item_ID ),
				'body' => '' 
			);
		}
		
		return $page_content;
	} // mla_list_table_custom_single_action
} //MLADuplicateItem

// Install the filters at an early opportunity
add_action('init', 'MLADuplicateItem::initialize');
?>