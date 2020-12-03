<?php
/**
 * Populates one or more data sources from a CSV file for use in mapping rules, etc.
 *
 * Much more information is in the Settings/MLA CSV Data "Documentation" tab.
 *
 * Inspired by several support topics, such as:
 *
 * "Update image meta data from CSV"
 * opened on 5/10/2020 by "eledris".
 * https://wordpress.org/support/topic/update-image-meta-data-from-csv/
 *
 * "Export Settings?"
 * opened on 5/5/2020 by "redkite".
 * https://wordpress.org/support/topic/export-settings-25/
 *
 * "Migrating Data From One Site to Another"
 * opened on 11/26/2019 by "digisavvy".
 * https://wordpress.org/support/topic/migrating-data-from-one-site-to-another/
 *
 * "Export/Import Att. Data"
 * opened on 8/15/2019 by "customle".
 * https://wordpress.org/support/topic/migrating-data-from-one-site-to-another/
 *
 * "Batch upload of media (not bulk upload)"
 * opened on 3/30/2018 by "yassermkali".
 * https://wordpress.org/support/topic/batch-upload-of-media-not-bulk-upload/
 *
 * "Import/Export Funcionality"
 * opened on 10/15/2017 by "polymathy".
 * https://wordpress.org/support/topic/batch-upload-of-media-not-bulk-upload/
 *
 * "Bulk Add Unique Captions"*
 * opened on 2/17/2016 by "hunterscreate".
 * https://wordpress.org/support/topic/batch-upload-of-media-not-bulk-upload/
 *
 * "How to migrate image attributes during site migration"
 * opened on 8/30/2015 by "cliffmama".
 * https://wordpress.org/support/topic/how-to-migrate-image-attributes-during-site-migration/
 *
 * "Export / Import Att. Categories"
 * opened on 6/4/2014 by "thitz" and "vipoxofni".
 * https://wordpress.org/support/topic/export-import-att-categories/
 *
 * "export / import settings"
 * opened on 9/23/2013 by "bhensler".
 * https://wordpress.org/support/topic/batch-upload-of-media-not-bulk-upload/
 *
 * "What about a media library export/import function"
 * opened on 9/25/2012 by "brentwz".
 * https://wordpress.org/support/topic/plugin-media-library-assistant-what-about-a-media-library-exportimport-function/
 *
 * @package MLA CSV Data Source Example
 * @version 1.01
 */

/*
Plugin Name: MLA CSV Data Source Example
Plugin URI: http://davidlingren.com/
Description: Populates one or more data sources from a CSV file
Author: David Lingren
Version: 1.01
Author URI: http://davidlingren.com/

Copyright 2020 David Lingren

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
 * Class MLA CSV Data Source Example hooks one of the filters provided by the IPTC/EXIF and Custom Field mapping features
 *
 * Call it anything you want, but give it an unlikely and hopefully unique name. Hiding everything
 * else inside a class means this is the only name you have to worry about.
 *
 * @package MLA CSV Data Source Example
 * @since 1.00
 */
class MLACSVDataSourceExample {
	/**
	 * Current version number
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const CURRENT_VERSION = '1.01';

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets, scripts and settings
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'mlacsvdatasource';

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
		if ( !empty( $_REQUEST['mla_csv_data_source_options_export'] ) ) {
			// Match Keys download will be handled in the admin_init filter
			check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );
			self::export_match_keys_action( );
			return;
		}
		
		// Defined in /media-library-assistant/includes/class-mla-data.php
		add_filter( 'mla_expand_custom_prefix', 'MLACSVDataSourceExample::mla_expand_custom_prefix', 10, 8 );

		// The remaining filters are only useful in the admin section; exit if in the "front-end" posts/pages. 
		if ( ! is_admin() )
			return;

		// Add submenu page in the "Settings" section
		add_action( 'admin_menu', 'MLACSVDataSourceExample::admin_menu' );
	} // initialize

	/**
	 * Add submenu page in the "Settings" section
	 *
	 * @since 1.00
	 */
	public static function admin_menu( ) {
		/*
		 * We need a tab-specific page ID to manage the screen options on the General tab.
		 * Use the URL suffix, if present. If the URL doesn't have a tab suffix, use '-general'.
		 * This hack is required to pass the WordPress "referer" validation.
		 */
		 if ( isset( $_REQUEST['page'] ) && is_string( $_REQUEST['page'] ) && ( self::SLUG_PREFIX . '-settings-' == substr( $_REQUEST['page'], 0, strlen( self::SLUG_PREFIX . '-settings-' ) ) ) ) {
			$tab = substr( $_REQUEST['page'], strlen( self::SLUG_PREFIX . '-settings-' ) );
		 } else {
			$tab = 'general';
		 }

		$tab = self::_get_options_tablist( $tab ) ? '-' . $tab : '-general';
		add_submenu_page( 'options-general.php', 'MLA CSV Data Source Example', 'MLA CSV Data', 'manage_options', self::SLUG_PREFIX . '-settings' . $tab, 'MLACSVDataSourceExample::add_submenu_page' );
		add_filter( 'plugin_action_links', 'MLACSVDataSourceExample::plugin_action_links', 10, 2 );
	} // admin_menu

	/**
	 * Add the "Settings" and "Guide" links to the Plugins section entry
	 *
	 * @since 1.00
	 *
	 * @param	array 	array of links for the Plugin, e.g., "Activate"
	 * @param	string 	Directory and name of the plugin Index file
	 *
	 * @return	array	Updated array of links for the Plugin
	 */
	public static function plugin_action_links( $links, $file ) {
		if ( $file == 'mla-csv-data-source-example/mla-csv-data-source-example.php' ) {
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . self::SLUG_PREFIX . '-settings-documentation&mla_tab=documentation' ), 'Guide' );
			array_unshift( $links, $settings_link );
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . self::SLUG_PREFIX . '-settings-general' ), 'Settings' );
			array_unshift( $links, $settings_link );
		}

		return $links;
	} // plugin_action_links

	/**
	 * Render (echo) the "MLA CSV Data" submenu in the Settings section
	 *
	 * @since 1.00
	 *
	 * @return	void Echoes HTML markup for the submenu page
	 */
	public static function add_submenu_page() {
//error_log( __LINE__ . " MLACSVDataSourceExample:add_submenu_page _REQUEST = " . var_export( $_REQUEST, true ), 0 );

		if ( !current_user_can( 'manage_options' ) ) {
			echo "<h2>MLA CSV Data Source Example - Error</h2>\n";
			wp_die( 'You do not have permission to manage plugin settings.' );
		}

		// Load template array and initialize page-level values.
		self::$page_template_array = MLACore::mla_load_template( dirname( __FILE__ ) . '/admin-settings-page.tpl', 'path' );
		$current_tab_slug = isset( $_REQUEST['mla_tab'] ) ? $_REQUEST['mla_tab']: 'general';
		$current_tab = self::_get_options_tablist( $current_tab_slug );
		$page_values = array(
			'version' => 'v' . self::CURRENT_VERSION,
			'messages' => '',
			'tablist' => self::_compose_settings_tabs( $current_tab_slug ),
			'tab_content' => '',
		);

		// Compose tab content
		if ( $current_tab ) {
			if ( isset( $current_tab['render'] ) ) {
				$handler = $current_tab['render'];
				$page_content = call_user_func( $handler );
			} else {
				$page_content = array( 'message' => 'ERROR: Cannot render content tab', 'body' => '' );
			}
		} else {
			$page_content = array( 'message' => 'ERROR: Unknown content tab', 'body' => '' );
		}

		if ( ! empty( $page_content['message'] ) ) {
			if ( false !== strpos( $page_content['message'], 'ERROR' ) ) {
				$messages_class = 'updated error';
				$dismiss_button = '';
			} else {
				$messages_class = 'updated notice is-dismissible';
				$dismiss_button = "  <button class=\"notice-dismiss\" type=\"button\"><span class=\"screen-reader-text\">[+dismiss_text+].</span></button>\n";
			}

			$page_values['messages'] = MLAData::mla_parse_template( self::$page_template_array['messages'], array(
				 'mla_messages_class' => $messages_class ,
				 'messages' => $page_content['message'],
				 'dismiss_button' => $dismiss_button,
				 'dismiss_text' => 'Dismiss this notice',
			) );
		}

		$page_values['tab_content'] = $page_content['body'];
		echo MLAData::mla_parse_template( self::$page_template_array['page'], $page_values );
	} // add_submenu_page

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
	 * Definitions for Settings page tab ids, titles and handlers
	 * Each tab is defined by an array with the following elements:
	 *
	 * array key => HTML id/name attribute and option database key (OMIT MLA_OPTION_PREFIX)
	 *
	 * title => tab label / heading text
	 * render => rendering function for tab messages and content. Usage:
	 *     $tab_content = ['render']( );
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $mla_tablist = array(
		'general' => array( 'title' => 'General', 'render' => array( 'MLACSVDataSourceExample', '_compose_general_tab' ) ),
		'documentation' => array( 'title' => 'Documentation', 'render' => array( 'MLACSVDataSourceExample', '_compose_documentation_tab' ) ),
		);

	/**
	 * Retrieve the list of options tabs or a specific tab value
	 *
	 * @since 1.00
	 *
	 * @param	string	Tab slug, to retrieve a single entry
	 *
	 * @return	array|false	The entire tablist ( $tab = NULL ), a single tab entry or false if not found/not allowed
	 */
	private static function _get_options_tablist( $tab = NULL ) {
		if ( is_string( $tab ) ) {
			if ( isset( self::$mla_tablist[ $tab ] ) ) {
				$results = self::$mla_tablist[ $tab ];
			} else {
				$results = false;
			}
		} else {
			$results = self::$mla_tablist;
		}

		return $results;
	} // _get_options_tablist

	/**
	 * Compose the navigation tabs for the Settings subpage
	 *
	 * @since 1.00
	 * @uses $page_template_array contains tablist and tablist-item templates
 	 *
	 * @param	string	Optional data-tab-id value for the active tab, default 'general'
	 *
	 * @return	string	HTML markup for the Settings subpage navigation tabs
	 */
	private static function _compose_settings_tabs( $active_tab = 'general' ) {
		$tablist_item = self::$page_template_array['tablist-item'];
		$tabs = '';
		foreach ( self::_get_options_tablist() as $key => $item ) {
			$item_values = array(
				'data-tab-id' => $key,
				'nav-tab-active' => ( $active_tab == $key ) ? 'nav-tab-active' : '',
				'settings-page' => self::SLUG_PREFIX . '-settings-' . $key,
				'title' => $item['title']
			);

			$tabs .= MLAData::mla_parse_template( $tablist_item, $item_values );
		} // foreach $item

		$tablist_values = array( 'tablist' => $tabs );
		return MLAData::mla_parse_template( self::$page_template_array['tablist'], $tablist_values );
	} // _compose_settings_tabs

	/**
	 * Compose HTML markup for the source file options if any text/csv files exist
 	 *
	 * @since 1.00
	 *
	 * @param	integer	Optional item ID value for the current source file, default 0
	 *
	 * @return	string	HTML markup for the Source file <select> list, if any
	 */
	private static function _compose_source_file_options( $current_id = 0 ) {
		// Default option if no files exist or there is no current selection
		$option_values = array(
			'value' => '0',
			'text' => '&mdash; select a source (CSV) file &mdash;',
			'selected' => ''
		);
		$select_options = MLAData::mla_parse_template( self::$page_template_array['select-option'], $option_values );

		// Find all CSV files in the Media Library
		$args = array(
			'post_type'  => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'text/csv',
			'nopaging' => true,
		);
		$query = new WP_Query( $args );

		foreach ( $query->posts as $post ) {
			$option_values = array(
				'value' => $post->ID,
				'text' => esc_attr( $post->post_title ),
				'selected' => $current_id === $post->ID ? 'selected=selected' : '',
			);

			$select_options .= MLAData::mla_parse_template( self::$page_template_array['select-option'], $option_values );
		}

		$option_values = array(
			'key' => 'mla-import-settings-file',
			'options' => $select_options
		);

		return $select_options;
	} // _compose_source_file_options

	/**
	 * Compose the General tab content for the Settings subpage
	 *
	 * @since 1.00
	 * @uses $page_template_array contains tab content template(s)
 	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private static function _compose_general_tab( ) {
		$page_content = array( 'message' => '', 'body' => '' );

		// Initialize page messages and content, check for page-level Save Changes and Export Match Keys
		if ( !empty( $_REQUEST['mla_csv_data_source_options_save'] ) ) {
			check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );
			$page_content = self::_save_setting_changes( );
		}

		if ( !empty( $page_content['body'] ) ) {
			return $page_content;
		}

		// Display the General tab
		$_SERVER['REQUEST_URI'] = remove_query_arg( array(
			'mla_csv_data_source_options',
			'_wpnonce',
			'_wp_http_referer',
			'mla_csv_data_source_options_save',
		), $_SERVER['REQUEST_URI'] );

		// Compose page-level options
		$page_values = array(
			'source_options' => self::_compose_source_file_options( self::_get_plugin_option('source') ),
			'id_selected' => 'id' === self::_get_plugin_option('match') ? 'selected=selected' : '',
			'base_file_selected' => 'base_file' === self::_get_plugin_option('match') ? 'selected=selected' : '',
			'file_name_selected' => 'file_name' === self::_get_plugin_option('match') ? 'selected=selected' : '',
			'delimiter' => esc_attr( self::_get_plugin_option('delimiter') ),
			'enclosure' => esc_attr( self::_get_plugin_option('enclosure') ),
			'escape' => esc_attr( self::_get_plugin_option('escape') ),
		);
		$options_list = MLAData::mla_parse_template( self::$page_template_array['page-level-options'], $page_values );

		$form_arguments = '?page=' . self::SLUG_PREFIX . '-settings-general&mla_tab=general';

		$page_values = array(
			'form_url' => admin_url( 'options-general.php' ) . $form_arguments,
			'_wpnonce' => wp_nonce_field( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME, true, false ),
			'options_list' => $options_list,
		);

		$page_content['body'] .= MLAData::mla_parse_template( self::$page_template_array['general-tab'], $page_values );

		return $page_content;
	} // _compose_general_tab

	/**
	 * Compose the General tab content for the Settings subpage
	 *
	 * @since 1.00
	 * @uses $page_template_array contains tab content template(s)
 	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private static function _compose_documentation_tab( ) {
		$page_content = array( 'message' => '', 'body' => '' );
		$page_values = array(
			'example_url' => MLACore::mla_nonce_url( '?page=mla-settings-menu-documentation&mla_tab=documentation&mla-example-search=Search', MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME ),
			'settingsURL' => admin_url('options-general.php'),
		);

		$page_content['body'] = MLAData::mla_parse_template( self::$page_template_array['documentation-tab'], $page_values );
		return $page_content;
	} // _compose_documentation_tab

	/**
	 * Export Base Name, File Name and ID to a CSV file
	 *
	 * @since 1.01
	 *
	 * @return	none	terminates execution with exit();
	 */
	public static function export_match_keys_action() {//
		global $wpdb;
	
		check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );

		if( ini_get( 'zlib.output_compression' ) ) { 
			ini_set( 'zlib.output_compression', 'Off' );
		}
		
		$message = '';
		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['basedir'];
		$date = date("Ymd_B");
		$filename = "{$upload_dir}/match_keys_{$date}.csv";

		$file_handle = @fopen( $filename, 'w' );
		if ( ! $file_handle ) {
			$message = sprintf( 'ERROR: The export file ( %1$s ) could not be opened.', $filename );
		} else {
			if (false === @fwrite( $file_handle, "Base Name,File Name,ID\r\n")) {
				$error_info = error_get_last();

			if ( false !== ( $tail = strpos( $error_info['message'], '</a>]: ' ) ) ) {
				$php_errormsg = ':<br>' . substr( $error_info['message'], $tail + 7 );
			} else {
				$php_errormsg = '.';
			}

			$message = sprintf( 'ERROR: Writing the settings file ( %1$s ) "%2$s".', $filename, $php_errormsg );
			}
		}

		if ( empty( $message ) ) {
			// Compile the data we need to populate the file
			$query = sprintf( 'SELECT post_id, meta_value FROM %1$s WHERE %1$s.meta_key = \'%2$s\'', $wpdb->postmeta, '_wp_attached_file' );
			$items = $wpdb->get_results( $query, OBJECT );

			foreach( $items as $item ) {
				$pathinfo = pathinfo( $item->meta_value );
				$file_name = $pathinfo['basename'];
				$fields = array( $item->meta_value, $file_name, $item->post_id );
				@fputcsv( $file_handle, $fields );
			}
		}
		
		fclose($file_handle);

		if ( empty( $message ) ) {
			header('Pragma: public'); 	// required
			header('Expires: 0');		// no cache
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Last-Modified: '.gmdate ( 'D, d M Y H:i:s', filemtime ( $filename ) ).' GMT');
			header('Cache-Control: private',false);
			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="'.basename( $filename ).'"');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: ' . filesize( $filename ));
			header('Connection: close');

			readfile( $filename );
		} else {
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			echo '<html xmlns="http://www.w3.org/1999/xhtml">';
			echo '<head>';
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo '<title>Download Error</title>';
			echo '</head>';
			echo '';
			echo '<body>';
			echo $message;
			echo '</body>';
			echo '</html> ';
		}

		@unlink( $filename );
		exit();
	} // export_match_keys_action

	/**
	 * Save settings as a WordPress wp_options entry
	 *
	 * @since 1.00
	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private static function _save_setting_changes() {
		$page_content = array( 'message' => 'Settings unchanged.', 'body' => '' );

		$changed  = self::_update_plugin_option( 'source', absint( $_REQUEST[ 'mla_csv_data_source_options' ]['source'] ) );
		$changed |= self::_update_plugin_option( 'match', stripslashes( $_REQUEST[ 'mla_csv_data_source_options' ]['match'] ) );

		if ( empty( $_REQUEST[ 'mla_csv_data_source_options' ]['delimiter'] ) ) {
			$changed |= self::_update_plugin_option( 'delimiter', self::$_default_settings['delimiter'] );
		} else {
			$changed |= self::_update_plugin_option( 'delimiter', stripslashes( $_REQUEST[ 'mla_csv_data_source_options' ]['delimiter'] ) );
		}

		if ( empty( $_REQUEST[ 'mla_csv_data_source_options' ]['enclosure'] ) ) {
			$changed |= self::_update_plugin_option( 'enclosure', self::$_default_settings['enclosure'] );
		} else {
			$changed |= self::_update_plugin_option( 'enclosure', stripslashes( $_REQUEST[ 'mla_csv_data_source_options' ]['enclosure'] ) );
		}

		if ( empty( $_REQUEST[ 'mla_csv_data_source_options' ]['escape'] ) ) {
			// An empty escape character is allowed as of PHP 7.4; is disables the escape mechanism.
			if ( version_compare( phpversion(), '7.4.0', '>=' ) ) {
				$changed |= self::_update_plugin_option( 'escape', '' );
			} else {
				$changed |= self::_update_plugin_option( 'escape', self::$_default_settings['escape'] );
			}
		} else {
			$changed |= self::_update_plugin_option( 'escape', stripslashes( $_REQUEST[ 'mla_csv_data_source_options' ]['escape'] ) );
		}

		if ( $changed ) {
			// No reason to save defaults in the database
			if ( self::$_settings === self::$_default_settings ) {
				delete_option( self::SLUG_PREFIX . '-settings' ); 
			} else {
				$changed = update_option( self::SLUG_PREFIX . '-settings', self::$_settings, false );
			}

			if ( $changed ) {
				$page_content['message'] = "Settings have been updated.";
			} else {
				$page_content['message'] = "Settings update failed.";
			}
		}

		if ( false === self::_validate_match_selection( false ) ) {
			$page_content['message'] .= "<br />WARNING: Match variable not found in source file.";
		}

		return $page_content;		
	} // _save_setting_changes

	/**
	 * Assemble the in-memory representation of the custom feed settings
	 *
	 * @since 1.00
	 *
	 * @param boolean $force_refresh Optional. Force a reload of rules. Default false.
	 * @return boolean Success (true) or failure (false) of the operation
	 */
	private static function _get_plugin_option_settings( $force_refresh = false ) {
		if ( false == $force_refresh && NULL != self::$_settings ) {
			return true;
		}

		// Update the plugin options from the wp_options table or set defaults
		self::$_settings = get_option( self::SLUG_PREFIX . '-settings' );
		if ( !is_array( self::$_settings ) ) {
			self::$_settings = self::$_default_settings;
		}

		return true;
	} // _get_plugin_option_settings

	/**
	 * In-memory representation of the option settings
	 *
	 * @since 1.00
	 *
	 * @var array $_settings {
	 *     @type boolean $assign_parents Assign all terms in path, not just the last (leaf) term
	 *     @type boolean $assign_rule_parent Assign the Rule Parent (if any) in addition to terms in path
	 *     @type string  $path_delimiter The delimiter that separates path components
	 *     }
	 */
	private static $_settings = NULL;

	/**
	 * Default processing options
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $_default_settings = array (
						'source' => 0,
						'match' => 'id',
						'delimiter' => ',',
						'enclosure' => '"',
						'escape' => '\\',
					);

	/**
	 * Get a custom feed option setting
	 *
	 * @since 1.00
	 *
	 * @param string	$name Option name
	 *
	 * @return	mixed	Option value, if it exists else NULL
	 */
	private static function _get_plugin_option( $name ) {
		if ( !self::_get_plugin_option_settings() ) {
			return NULL;
		}

		if ( !isset( self::$_settings[ $name ] ) ) {
			return NULL;
		}

		return self::$_settings[ $name ];
	} // _get_plugin_option

	/**
	 * Update a custom feed option setting
	 *
	 * @since 1.00
	 *
	 * @param string $name Option name
	 * @param mixed	$new_value Option value
	 *
	 * @return mixed True if option value changed, false if value unchanged, NULL if failure
	 */
	private static function _update_plugin_option( $name, $new_value ) {
		if ( !self::_get_plugin_option_settings() ) {
			return NULL;
		}

		$old_value = isset( self::$_settings[ $name ] ) ? self::$_settings[ $name ] : NULL;

		if ( $new_value === $old_value ) {
			return false;
		}

		self::$_settings[ $name ] = $new_value;
		return true;
	} // _update_plugin_option

	/**
	 * In-memory representation of the CSV variables,
	 * indexed by the "match" column value; ID, Base Name or File Name.
	 *
	 * @since 1.00
	 *
	 * @var array $_csv_variables { $match_value => 
	 *     @type array  $variables The item's value for each data source, except for $match_value
	 *     }
	 */
	private static $_csv_variables = NULL;

	/**
	 * In-memory representation of the CSV variable namess, from the
	 * first line of the file. Indexed by column position in the CSV file.
	 *
	 * @since 1.00
	 *
	 * @var array $_csv_variables { $index => 
	 *     @type array  $names The variable name for each data source
	 *     }
	 */
	private static $_csv_variable_names = NULL;

	/**
	 * Name of the "match" variable
	 *
	 * @since 1.00
	 *
	 * @var string "ID", ".ID", "Base Name" or "File Name"
	 */
	private static $_csv_match_variable= '';

	/**
	 * Name of the "match" variable
	 *
	 * @since 1.00
	 *
	 * @var mixed	index of CSV match variable, if found, else false.
	 */
	private static $_csv_match_index = false;

	/**
	 * Verify existance of match variable in the CSV file
	 *
	 * @since 1.00
	 *
	 * @param	boolean	Optional, Use existing values, if present. Default true.
	 *
	 * @return	boolean	True if variable exists in file else false.
	 */
	private static function _validate_match_selection( $use_existing = true ) {
		self::_load_csv_file( $use_existing, true );

		if ( !empty( self::$_csv_variable_names ) ) {
			$match = self::_get_plugin_option('match');

			switch ( $match ) {
				case 'id':
					if ( false !== array_search( 'ID', self::$_csv_variable_names ) ) {
						return true;
					}	elseif ( false !== array_search( '.ID', self::$_csv_variable_names ) ) {
						// Special case; Excel does not allow "ID" as the first column name
						return true;
					}
					break;
				case 'base_file':
					if ( false !== array_search( 'Base File', self::$_csv_variable_names ) ) {
						return true;
					}
					break;
				case 'file_name':
					if ( false !== array_search( 'File Name', self::$_csv_variable_names ) ) {
						return true;
					}
					break;
			}
		}

		return false;
	} // _validate_match_selection

	/**
	 * Load a CSV File into memory
	 *
	 * @since 1.00
	 *
	 * @param	boolean	Optional, Use existing values, if present. Default true.
	 * @param	boolean	Optional, Load names only, from first line of the file. Default false.
	 */
	private static function _load_csv_file( $use_existing = true, $names_only = false ) {
		if ( $use_existing && !empty( self::$_csv_variables ) ) {
			return;
		}

		// Populate if possible else no values available
		self::$_csv_variables = array();
		self::$_csv_variable_names = array();
		self::$_csv_match_variable = '';
		self::$_csv_match_index = false;

		$ID = self::_get_plugin_option('source');
		$match = self::_get_plugin_option('match');
		$delimiter = self::_get_plugin_option('delimiter');
		$enclosure = self::_get_plugin_option('enclosure');
		$escape = self::_get_plugin_option('escape');

		// Find the file attached to the selected item
		$path = get_attached_file( $ID );

		if ( !empty( $path ) ) {		
			$handle = fopen( $path, "r" );
			if ( false !== $handle ) {
				$match_index = false;
				self::$_csv_variable_names = fgetcsv( $handle, 0, $delimiter, $enclosure, $escape );

				if ( ( false === $names_only) && ( !empty( self::$_csv_variable_names ) ) ) {
					switch ( $match ) {
						case 'id':
							$match_index = array_search( 'ID', self::$_csv_variable_names );
							if ( false === $match_index ) {
								// Special case; Excel does not allow "ID" as the first column name
								$match_index = array_search( '.ID', self::$_csv_variable_names );
							}
							break;
						case 'base_file':
							$match_index = array_search( 'Base File', self::$_csv_variable_names );
							break;
						case 'file_name':
							$match_index = array_search( 'File Name', self::$_csv_variable_names );
							break;
					} // case $match
				} // have variable names

				// Load the file if we have a valid match variable
				if ( false !== $match_index ) {
					self::$_csv_match_variable = $match;
					self::$_csv_match_index = $match_index;

					while ( false !== ( $data = fgetcsv( $handle, 0, $delimiter, $enclosure, $escape ) ) ) {
						if ( 'id' === $match ) {
							$match_value = absint( $data[ $match_index ] );
						} else {
							$match_value = $data[ $match_index ];
						}

						// Don't store the match value twice
						unset( $data[ $match_index ] );

						self::$_csv_variables[ $match_value ] = $data;
					}
				}

				fclose($handle);
			} // file exists
		} // !empty( $path )
	} // _load_csv_file

	/**
	 * Find CSV variables for a given Media Library item
	 *
	 * @since 1.00
	 *
	 * @param	integer	attachment ID for attachment-specific values
	 *
	 * @return	mixed	array of CSV variables, if found, else false
	 */
	private static function _csv_search( $post_id ) {
		switch ( self::$_csv_match_variable ) {
			case 'id':
				$key = $post_id;
				break;
			case 'base_file':
				$key = get_post_meta( $post_id, '_wp_attached_file', true );
				break;
			case 'file_name':
				$pathinfo = pathinfo( get_post_meta( $post_id, '_wp_attached_file', true ) );
				$key = $pathinfo['basename'];
				break;
			default:
				$key = '';
		} // case $match

		if ( isset( self::$_csv_variables[ $key ] ) ) {
			$values = self::$_csv_variables[ $key ];
			// Restore the match value to the array
			$values[ self::$_csv_match_index ] = (string) $key;
			return $values;
		}

		return false;
	} // _csv_search

	/**
	 * MLA Expand Custom Prefix Filter
	 *
	 * Gives you an opportunity to generate your custom data value when a parameter's prefix value is not recognized.
	 *
	 * @since 1.00
	 *
	 * @param	string	NULL, indicating that by default, no custom value is available
	 * @param	string	the data-source name 
	 * @param	array	data-source components; prefix (empty), value, option, format and args (if present)
	 * @param	array	values from the query, if any, e.g. shortcode parameters
	 * @param	array	item-level markup template values, if any
	 * @param	integer	attachment ID for attachment-specific values
	 * @param	boolean	for option 'multi', retain existing values
	 * @param	string	default option value
	 */
	public static function mla_expand_custom_prefix( $custom_value, $key, $value, $query, $markup_values, $post_id, $keep_existing, $default_option ) {
		static $match_cache = null;

		if ( 'csv' !== strtolower( $value['prefix'] ) ) {
			return $custom_value;
		}

		if ( is_null( $match_cache ) ) {
			self::_load_csv_file();
			$match_cache = array();
		}

		// Look for field/value qualifier
		$match_count = preg_match( '/^(.+)\((.+)\)/', $value['value'], $matches );
		if ( $match_count ) {
			$field = $matches[1];
			$qualifier = $matches[2];
		} else {
			$field = $value['value'];
			$qualifier = '';
		}

		// Set debug mode
		$debug_active = isset( $query['mla_debug'] ) && ( 'false' !== trim( strtolower( $query['mla_debug'] ) ) );
		if ( $debug_active ) {
			$old_mode = MLACore::mla_debug_mode( 'log' );
			MLACore::mla_debug_add( __LINE__ . " MLACSVDataSourceExample::mla_expand_custom_prefix( {$key}, {$post_id}, {$keep_existing}, {$default_option} ) \$_REQUEST = " . var_export( $_REQUEST, true ) );
			MLACore::mla_debug_add( __LINE__ . " MLACSVDataSourceExample::mla_expand_custom_prefix( {$field}, {$qualifier} ) \$value = " . var_export( $value, true ) );
			MLACore::mla_debug_add( __LINE__ . " MLACSVDataSourceExample::mla_expand_custom_prefix() \$query = " . var_export( $query, true ) );
			MLACore::mla_debug_add( __LINE__ . " MLACSVDataSourceExample::mla_expand_custom_prefix() \$markup_values = " . var_export( $markup_values, true ) );
		}

		$match_index = array_search( $field, self::$_csv_variable_names );

		if ( false === $match_index ) {
			return '';
		}

		$csv_variables = false;
		if ( isset( $match_cache[ $post_id ] ) ) {
			$csv_variables = $match_cache[ $post_id ];
		} else {
			$csv_variables = self::_csv_search( $post_id );
			if ( false !== $csv_variables ) {
				$match_cache[ $post_id ] = $csv_variables;
			}
		}

		if ( ( false === $csv_variables ) || empty( $csv_variables[ $match_index ] ) ){
			return '';
		}

		return $csv_variables[ $match_index ];
	} // mla_expand_custom_prefix
} //MLACSVDataSourceExample

// Install the filters at an early opportunity
add_action('init', 'MLACSVDataSourceExample::initialize');
?>