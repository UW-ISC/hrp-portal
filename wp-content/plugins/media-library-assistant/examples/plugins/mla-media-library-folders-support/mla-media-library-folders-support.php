<?php
/**
 * Support for Block Direct Access feature of Media Library Folders plugin
 *
 * Much more information is in the Settings/MLA MLF Support "Documentation" tab.
 *
 * Created for support topic "Customizing upload options"
 * opened on 10/22/2023 by "bhasic (@bhasic)".
 * https://wordpress.org/support/topic/customizing-upload-options/
 *
 * @package MLA Media Library Folders Support
 * @version 1.00
 */

/*
Plugin Name: MLA Media Library Folders Support
Plugin URI: http://davidlingren.com/
Description: Supports Media Library Folders in the Media/Assistant admin screen
Author: David Lingren
Version: 1.00
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
 * Class MLA Media Library Folders Support supports MLF in the Media/Assistant admin screen
 *
 * @package MLA Media Library Folders Support
 * @since 1.00
 */
class MLAMediaLibraryFoldersSupport {
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
	const SLUG_PREFIX = 'MLAMediaLibraryFoldersSupport';

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
				'plugin_title' => 'MLA Media Library Folders Support',
				'menu_title' => 'MLA MLF Support',
				'plugin_file_name_only' => 'mla-media-library-folders-support',
				'plugin_version' => self::PLUGIN_VERSION,
				'template_file' => '/admin-settings-page.tpl', // Add the path at runtime, in initialize()
				'messages' => '',
				'options' => array(
					// 'slug' => array( 'type' => 'checkbox|text|select|textarea', 'default' => 'text|boolean(0|1)' )
					// See the default values in $_default_settings below
					'replace_item_thumbnail' => array( 'type' => 'checkbox', 'default' => false ),
					'block_edit_media_page' => array( 'type' => 'checkbox', 'default' => true ),
					'block_assistant_downloads' => array( 'type' => 'checkbox', 'default' => true ),
					'add_toggle_access' => array( 'type' => 'checkbox', 'default' => false ),
					'allow_admin_access' => array( 'type' => 'checkbox', 'default' => false ),
				),
				'general_tab_values' => array(), // additional page_values for 'page-level-options' template
				'documentation_tab_values' => array(
					'plugin_title' => 'MLA Media Library Folders Support',
					'settingsURL' => '', // Set at runtime in initialize()
				), // page_values for 'documentation-tab' template
			);

	/**
	 * Default processing options
	 *
	 * @since 1.00
	 *
	 * @var array $_default_settings {
	 *     @type boolean $replace_item_thumbnail Replace Media/Assistant item thumbnail image source with inline data
	 *     @type boolean $block_edit_media_page Block access to the Media/Edit Media page from Media/Assistant thumbnail
	 *     @type boolean $block_assistant_downloads Disable download actions in the Media/Assistant page
	 *     @type boolean $add_toggle_access Add the "Toggle MLF File Access" Media/Assistant bulk action
	 *     @type boolean $allow_admin_access Allow access to blocked items for Administrators
	 *     }
	 */
	private static $_default_settings = array (
						'replace_item_thumbnail' => false,
						'block_edit_media_page' => true,
						'block_assistant_downloads' => true,
						'add_toggle_access' => false,
						'allow_admin_access' => false,
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

		// This plugin requires the Media Library Folders plugin
		if( ! defined('MLFP_PROTECTED_DIRECTORY') ) {
			return;
		}

		// The plugin settings class is shared with other MLA example plugins
		if ( ! class_exists( 'MLAExamplePluginSettings102', false ) ) {
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

		add_filter( 'mla_list_table_custom_bulk_action', 'MLAMediaLibraryFoldersSupport::mla_list_table_custom_bulk_action', 10, 3 );
		add_filter( 'mla_list_table_get_bulk_actions', 'MLAMediaLibraryFoldersSupport::mla_list_table_get_bulk_actions', 10, 1 );
		add_filter( 'mla_list_table_bulk_action_initial_request', 'MLAMediaLibraryFoldersSupport::mla_list_table_bulk_action_initial_request', 10, 3 );
		add_filter( 'mla_list_table_build_rollover_actions', 'MLAMediaLibraryFoldersSupport::mla_list_table_build_rollover_actions', 10, 3 );
		add_filter( 'mla_list_table_build_inline_data', 'MLAMediaLibraryFoldersSupport::mla_list_table_build_inline_data', 10, 2 );
		add_filter( 'mla_list_table_primary_column_link', 'MLAMediaLibraryFoldersSupport::mla_list_table_primary_column_link', 10, 3 );
		add_filter( 'mla_list_table_primary_column_content', 'MLAMediaLibraryFoldersSupport::mla_list_table_primary_column_content', 10, 7 );


		// Add the run-time values to the settings
		// Load template array and initialize page-level values.
		self::$page_template_array = MLACore::mla_load_template( self::$settings_arguments['template_file'], 'path' );

		//$general_tab_values = self::$plugin_settings->get_plugin_argument('general_tab_values');
		//self::$plugin_settings->update_plugin_argument('general_tab_values', $general_tab_values );
	} // initialize

	/**
	 * Find the MLF folder ID from a relative path
	 *
	 * @since 1.00
	 *
	 * @param	string	$path Folder path relative to WP uploads folder
	 *
	 * @return	mixed False if folder not found else integer ID of folder
	 */
	private static function _find_folder_id( $path ) {
	    global $wpdb;    
//error_log( 'MLAMediaLibraryFoldersSupport::_find_folder_id $path = ' . var_export( $path, true ), 0 );

		if ( false !== strpos( $path, MLFP_PROTECTED_DIRECTORY ) ) {
			$path = substr( $path, ( 1 + strlen( MLFP_PROTECTED_DIRECTORY ) ) );
		}
//error_log( 'MLAMediaLibraryFoldersSupport::_find_folder_id final $path = ' . var_export( $path, true ), 0 );

		$sql = "SELECT ID FROM {$wpdb->posts}
			LEFT JOIN {$wpdb->postmeta} AS pm ON pm.post_id = ID
			WHERE pm.meta_value = '$path' 
			and pm.meta_key = '_wp_attached_file'";

		$row = $wpdb->get_row($sql);
//error_log( 'MLAMediaLibraryFoldersSupport::_find_folder_id $row = ' . var_export( $row, true ), 0 );
		if(NULL === $row ) {
			return false;
		}

		return $row->ID;
	} // _find_folder_id

	/**
	 * Process the Media/Assistant "Toggle MLF File Access" bulk action.
	 *
	 * @since 1.00
	 *
	 * @param	NULL	$item_content	NULL, indicating no handler.
	 * @param	string	$bulk_action	The requested action.
	 * @param	integer	$post_id		The affected attachment.
	 */
	public static function mla_list_table_custom_bulk_action( $item_content, $bulk_action, $post_id ) {
//error_log( "MLAMediaLibraryFoldersSupport::mla_list_table_custom_bulk_action( {$bulk_action}, {$post_id} )", 0 );
		if ( 'toggle-file-access' !== $bulk_action ) {
			return $item_content;
		}

		$file_name = get_post_meta( $post_id, '_wp_attached_file', true );
		$path = pathinfo( $file_name, PATHINFO_DIRNAME );
		$folder_id = self::_find_folder_id( $path );
		$protected = ( false !== strpos( $file_name, MLFP_PROTECTED_DIRECTORY ) ) ? 1 : 0;
//error_log( "MLAMediaLibraryFoldersSupport::mla_list_table_custom_bulk_action( {$file_name}, {$path}, {$folder_id}, {$protected} )", 0 );

		// Defined in /plugins/media-library-plus/media-library-plus.php
		global $mg_media_library_folders;
		$mg_media_library_folders->move_to_protected_folder( $post_id, $folder_id, $protected );

		$item_content = array( 'message' => '',  'body' => '', );
		
		if ( $protected ) {
			$item_content['message'] = sprintf( 'Item %1$d, unblocked.', $post_id );
		} else {
			$item_content['message'] = sprintf( 'Item %1$d, blocked.', $post_id );
		}

		return $item_content;
	} // mla_list_table_custom_bulk_action

	/**
	 * Add the Media/Assistant "Toggle MLF File Access" bulk action.
	 *
	 * @since 1.00
	 *
	 * @param	array	$actions	An array of bulk actions.
	 *								Format: 'slug' => 'Label'
	 */
	public static function mla_list_table_get_bulk_actions( $actions ) {
		if ( false === self::$plugin_settings->get_plugin_option('add_toggle_access') ) {
			$allow_admin = false;
			if ( self::$plugin_settings->get_plugin_option('allow_admin_access') ) {
				$allow_admin = current_user_can( 'manage_options' );
			}

			if ( false === $allow_admin ) {
				return $actions;
			}
		}
		
		$actions['toggle-file-access'] = 'Toggle MLF File Access';
		return $actions;
	} // mla_list_table_get_bulk_actions

	/**
	 * Remove blocked items from the Media/Assistant "Download" bulk action.
	 *
	 * @since 1.00
	 *
	 * @param	array	$request		Bulk action request parameters, including ['mla_bulk_action_do_cleanup'].
	 * @param	string	$bulk_action	The requested action.
	 * @param	array	$custom_field_map	[ slug => field_name ]
	 */
	public static function mla_list_table_bulk_action_initial_request( $request, $bulk_action, $custom_field_map ) {
		//error_log( "MLAMediaLibraryFoldersSupport::mla_list_table_bulk_action_initial_request( {$bulk_action} ) request = " . var_export( $request, true ), 0 );
		//error_log( "MLAMediaLibraryFoldersSupport::mla_list_table_bulk_action_initial_request( {$bulk_action} ) custom_field_map = " . var_export( $custom_field_map, true ), 0 );
		if ( 'download-zip' !== $bulk_action ) {
			return $request;
		}

		if ( self::$plugin_settings->get_plugin_option('allow_admin_access') ) {
			if ( current_user_can( 'manage_options' ) ) {
				return $request;
			}
		}

		if ( false === self::$plugin_settings->get_plugin_option('block_assistant_downloads') ) {
			return $request;
		}
		
		if ( !empty( $request['cb_attachment'] ) ) {
			foreach ( $request['cb_attachment'] as $index => $post_id ) {
				$file_name = get_attached_file( $post_id );
				if ( false !== strpos( $file_name, MLFP_PROTECTED_DIRECTORY ) ) {
					unset( $request['cb_attachment'][ $index ] );
				}
			}
		}

		//error_log( "MLAMediaLibraryFoldersSupport::mla_list_table_bulk_action_initial_request( {$bulk_action} ) request = " . var_export( $request, true ), 0 );
		return $request;
	} // mla_list_table_bulk_action_initial_request

	/**
	 * Disable the Media/Assistant "Download" and "Edit" rollover actions for blocked items.
	 *
	 * @since 1.00
	 *
	 * @param	array	$actions	The list of item "Rollover" actions.
	 * @param	object	$item		The current Media Library item.
	 * @param	string	$column		The List Table column slug.
	 */
	public static function mla_list_table_build_rollover_actions( $actions, $item, $column ) {
		//error_log( "MLAMediaLibraryFoldersSupport::mla_list_table_build_rollover_actions ({$column}) \$actions = " . var_export( $actions, true ), 0 );
		//error_log( "MLAMediaLibraryFoldersSupport::mla_list_table_build_rollover_actions ({$column}) \$item = " . var_export( $item, true ), 0 );

		if ( self::$plugin_settings->get_plugin_option('allow_admin_access') ) {
			if ( current_user_can( 'manage_options' ) ) {
				return $actions;
			}
		}

		if ( ! empty( $item->mla_wp_attached_file ) ) {
			if ( false === strpos( $item->mla_wp_attached_file, MLFP_PROTECTED_DIRECTORY ) ) {
				return $actions;
			}

			if ( self::$plugin_settings->get_plugin_option('block_edit_media_page') ) {
				$actions['edit'] = '<a style="color: grey" title="Edit is Blocked">Edit</a>';
			}

			if ( self::$plugin_settings->get_plugin_option('block_assistant_downloads') ) {
				$actions['download'] = '<a style="color: grey" title="Download is Blocked">Download</a>';
			}
		}

		return $actions;
	} // mla_list_table_build_rollover_actions

	/**
	 * In the Quick Edit area, replace the protected item thumbnail image link with an inline copy of the image.
	 *
	 * This filter gives you an opportunity to filter the data passed to the
	 * JavaScript functions for Quick and Bulk editing.
	 *
	 * @since 1.00
	 *
	 * @param	string	$inline_data	The HTML markup for inline data.
	 * @param	object	$item			The current Media Library item.
	 */
	public static function mla_list_table_build_inline_data( $inline_data, $item ) {
		//error_log( 'MLAListTableHooksExample::mla_list_table_build_inline_data $inline_data = ' . var_export( $inline_data, true ), 0 );
		//error_log( 'MLAListTableHooksExample::mla_list_table_build_inline_data $item = ' . var_export( $item, true ), 0 );

		//check for protected folders
		if ( ! empty( $item->mla_wp_attached_file ) ) {
			if ( false === strpos( $item->mla_wp_attached_file, MLFP_PROTECTED_DIRECTORY ) ) {
				return $inline_data;
			}

			// Outline the image to denote protected file
			$inline_data = preg_replace( '/<img /', '<img style="outline: 2px solid red"', $inline_data );

			if ( self::$plugin_settings->get_plugin_option('replace_item_thumbnail') ) {
				$file = $item->mla_wp_attached_filename;
				if ( ! empty( $item->mla_wp_attachment_metadata ) ) {
					if ( ! empty( $item->mla_wp_attachment_metadata['sizes'] ) ) {
						if ( ! empty( $item->mla_wp_attachment_metadata['sizes']['thumbnail'] ) ) {
							$file = $item->mla_wp_attachment_metadata['sizes']['thumbnail']['file'];
						}
					}
				}
				
				$upload_dir_array = wp_upload_dir();
				$upload_dir = $upload_dir_array['basedir'] . '/';
				$file = $upload_dir . $item->mla_wp_attached_path . $file;

				if ( file_exists( $file ) ) {
					// Replace image source with inline data
					$data = file_get_contents( $file );
					$base64 = 'src="data:' . $item->post_mime_type . ';base64,' . base64_encode($data) . '">';  
					$inline_data = preg_replace( '/src=[^\>]*\>/', $base64, $inline_data );
				}
			} // replace_item_thumbnail
		}            

		return $inline_data;
	} // mla_list_table_build_inline_data

	/**
	 * Disable the Media/Assistant link to Media/Edit Media around the item thumbnail image.
	 *
	 * @since 1.00
	 *
	 * @param	boolean	$add_link	True to add hyperlink, false to omit it.
	 * @param	object	$item		The current Media Library item.
	 * @param	boolean	$is_trash	True if the current view is of the Media Trash.
	 */
	public static function mla_list_table_primary_column_link( $add_link, $item, $is_trash ) {
		//error_log( "MLAMediaLibraryFoldersSupport::mla_list_table_primary_column_link( add: {$add_link}, trash: {$is_trash} ) \$item = " . var_export( $item, true ), 0 );

		if ( self::$plugin_settings->get_plugin_option('allow_admin_access') ) {
			if ( current_user_can( 'manage_options' ) ) {
				return $add_link;
			}
		}

		//check for protected folders
		if ( ! empty( $item->mla_wp_attached_file ) ) {
			if ( false !== strpos( $item->mla_wp_attached_file, MLFP_PROTECTED_DIRECTORY ) ) {
				$add_link = ! self::$plugin_settings->get_plugin_option('block_edit_media_page');

			}
		}            

		return $add_link;
	} // mla_list_table_primary_column_link

	/**
	 * Replace the protected item thumbnail image link with an inline copy of the image.
	 *
	 * @since 1.00
	 *
	 * @param	boolean	$final_content True to add hyperlink, false to omit it.
	 * @param	object	$item		The current Media Library item.
	 * @param	boolean	$add_link	True to add hyperlink, false to omit it.
	 * @param	string	$thumb		IMG tag for the item thumbnail image.
	 * @param	string	$title		Item "draft or post" title.
	 * @param	string	$edit_url	URL for the Media/Edit Media page with view arguments.
	 * @param	string	$column_content Original content for the column, without MLA additions.
	 */
	public static function mla_list_table_primary_column_content( $final_content, $item, $add_link, $thumb, $title, $edit_url, $column_content ) {
		//error_log( "MLAMediaLibraryFoldersSupport::mla_list_table_primary_column_content( add: {$add_link}, {$title}, {$edit_url} ) \$item = " . var_export( $item, true ), 0 );
		//error_log( "MLAMediaLibraryFoldersSupport::mla_list_table_primary_column_content( add: {$add_link}, {$title}, {$edit_url} ) \$column_content = " . var_export( $column_content, true ), 0 );
		//error_log( "MLAMediaLibraryFoldersSupport::mla_list_table_primary_column_content( add: {$add_link}, {$title}, {$edit_url} ) \$final_content = " . var_export( $final_content, true ), 0 );

		//check for protected folders
		if ( ! empty( $item->mla_wp_attached_file ) ) {
			if ( false === strpos( $item->mla_wp_attached_file, MLFP_PROTECTED_DIRECTORY ) ) {
				return $final_content;
			}

			// Outline the image to denote protected file
			$final_content = preg_replace( '/<img /', '<img style="outline: 2px solid red"', $final_content );

			if ( self::$plugin_settings->get_plugin_option('replace_item_thumbnail') ) {
				$file = $item->mla_wp_attached_filename;
				if ( ! empty( $item->mla_wp_attachment_metadata ) ) {
					if ( ! empty( $item->mla_wp_attachment_metadata['sizes'] ) ) {
						if ( ! empty( $item->mla_wp_attachment_metadata['sizes']['thumbnail'] ) ) {
							$file = $item->mla_wp_attachment_metadata['sizes']['thumbnail']['file'];
						}
					}
				}
				
				$upload_dir_array = wp_upload_dir();
				$upload_dir = $upload_dir_array['basedir'] . '/';
				$file = $upload_dir . $item->mla_wp_attached_path . $file;

				if ( file_exists( $file ) ) {
					// Replace image source with inline data
					$data = file_get_contents( $file );
					$base64 = 'src="data:' . $item->post_mime_type . ';base64,' . base64_encode($data) . '">';  
					$final_content = preg_replace( '/src=[^\>]*\>/', $base64, $final_content );
				}
			} // replace_item_thumbnail
		}            

		return $final_content;
	} // mla_list_table_primary_column_content
} //MLAMediaLibraryFoldersSupport

// Install the filters at an early opportunity
add_action('init', 'MLAMediaLibraryFoldersSupport::initialize');
?>