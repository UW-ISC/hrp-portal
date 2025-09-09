<?php
/**
 * Downloads an array of checked items as a ZIP archive
 *
 * Includes 1) a shortcode that generates an HTML form Submit button and several hidden elements
 * that customize the ZIP archive. 2) An AJAX-based "action" function that creates the ZIP archive
 * and sends it to the client system.
 *
 * You can find more information about using all of the features of this plugin in the Documentation tab
 * on the Settings page.
 * 
 * Created for support topic "Select Multiple Files for Download"
 * opened on 1/19/2025 by "winework"
 * https://wordpress.org/support/topic/select-multiple-files-for-download/
 * 
 * @package MLA Gallery Download Checklist
 * @version 1.01
 */

/*
Plugin Name: MLA Gallery Download Checklist
Plugin URI: http://davidlingren.com/
Description: Generate HTML form elements and download checked items as a ZIP archive
Author: David Lingren
Version: 1.01
Author URI: http://davidlingren.com/

Copyright 2024 David Lingren

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
 * Class MLA Gallery Download Checklist downloads gallery items as a ZIP archive
 *
 * @package MLA Gallery Download Checklist
 * @since 1.00
 */
class MLAGalleryDownloadChecklist {
	/**
	 * Current version number
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const PLUGIN_VERSION = '1.01';

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets, scripts and settings
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'mlagallerydownloadchecklist';

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
				'plugin_title' => 'MLA Gallery Download Checklist',
				'menu_title' => 'MLA Download Checklist',
				'plugin_file_name_only' => 'mla-gallery-download-checklist',
				'plugin_version' => self::PLUGIN_VERSION,
				'template_file' => '/admin-settings-page.tpl', // Add the path at runtime, in initialize()
				'messages' => '',
				'options' => array(
					// 'slug' => array( 'type' => 'checkbox|text|select|textarea', 'default' => 'text|boolean(0|1)' )
					// See the default values in $_default_settings below
				),
				'general_tab_values' => array(), // additional page_values for 'page-level-options' template
				'documentation_tab_values' => array(
					'plugin_title' => 'MLA Gallery Download Checklist',
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

		// The plugin settings class is shared with other MLA example plugins
		if ( ! class_exists( 'MLAExamplePluginSettings102' ) ) {
			require_once( pathinfo( __FILE__, PATHINFO_DIRNAME ) . '/class-mla-example-plugin-settings-102.php' );
		}

		// Add the run-time values to the arguments
		self::$settings_arguments['template_file'] = dirname( __FILE__ ) . self::$settings_arguments['template_file'];
		self::$settings_arguments['documentation_tab_values']['settingsURL'] = admin_url('options-general.php');

		// Create our own settings object
		self::$plugin_settings = new MLAExamplePluginSettings102( self::$settings_arguments );

		// Are we processing the download link?
		if( defined('DOING_AJAX') && DOING_AJAX ) {
			// Defined here because the "admin_init" action is not called for item transfers
			if ( isset( $_REQUEST['action'] ) && self::SHORTCODE_NAME ===  $_REQUEST['action'] ) {
				add_action( 'wp_ajax_' . self::SHORTCODE_NAME, 'MLAGalleryDownloadChecklist::mla_download_checklist_action' );
				add_action( 'wp_ajax_nopriv_' . self::SHORTCODE_NAME, 'MLAGalleryDownloadChecklist::mla_download_checklist_action' );
			}

			return;
		}

		add_shortcode( self::SHORTCODE_NAME, 'MLAGalleryDownloadChecklist::mla_download_checklist_shortcode' );

		// The remaining filters are only useful in the admin section; exit if in the "front-end" posts/pages. 
		if ( ! is_admin() ) {
			return;
		}

		// Add the run-time values to the settings
		// Load template array and initialize page-level values.
		self::$page_template_array = MLACore::mla_load_template( self::$settings_arguments['template_file'], 'path' );

		//$general_tab_values = self::$plugin_settings->get_plugin_argument('general_tab_values');
		//MLACore::mla_debug_add( __LINE__ . " MLAGalleryDownloadChecklist::initialize \$general_tab_values = " . var_export( $general_tab_values, true ), self::MLA_DEBUG_CATEGORY );
	} // initialize

	/**
	 * Make sure $attr is an array, repair line-break damage, merge with $content,
	 * expand page-level, request: and query: substitution parameters
	 *
	 * @since 1.00
	 *
	 * @param	mixed	$attr Array or string containing shortcode attributes
	 * @param	string	$content Optional content for enclosing shortcodes
	 *
	 * @return	array	clean attributes array
	 */
	private static function _prepare_attributes( $attr, $content = NULL ) {
		global $post;

		if ( !class_exists( 'MLAShortcode_Support' ) ) {
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-support.php' );
		}

		// Create a clean array of shortcode parameters
		$attr = MLAShortcode_Support::mla_validate_attributes( $attr, $content );

		// Page values are already known, and can be used in data selection parameters
		$page_values = array(
			'id' => $post->ID,
			'page_ID' => $post->ID,
			'page_author' => $post->post_author,
			'page_date' => $post->post_date,
			'page_content' => $post->post_content,
			'page_title' => $post->post_title,
			'page_excerpt' => $post->post_excerpt,
			'page_status' => $post->post_status,
			'page_name' => $post->post_name,
			'page_modified' => $post->post_modified,
			'page_parent' => $post->post_parent,
			'page_guid' => $post->guid,
			'page_type' => $post->post_type,
			'page_mime_type' => $post->post_mime_type,
			'page_url' => get_page_link(),
		);

		// Look for page-level, 'request:' and 'query:' substitution parameters
		foreach ( $attr as $attr_key => $attr_value ) {
			$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr_value ) );
			$replacement_values = MLAData::mla_expand_field_level_parameters( $attr_value, $attr, $page_values );
			$attr[ $attr_key ] = MLAData::mla_parse_template( $attr_value, $replacement_values );
		}

		return $attr;
	}

	/**
	 * Passes PHP errors between mla_error_handler
	 * and mla_download_checklist_action
	 *
	 * @since 1.01
	 *
	 * @var	array
	 */
	private static $mla_errors = array();

	/**
	 * Intercept ZIP Archive errors
	 * 
	 * @since 1.01
	 *
	 * @param	int		the level of the error raised
	 * @param	string	the error message
	 * @param	string	the filename that the error was raised in
	 * @param	int		the line number the error was raised at
	 *
	 * @return	boolean	true, to bypass PHP error handler
	 */
	public static function mla_error_handler( $type, $string, $file, $line ) {
//error_log( 'DEBUG: mla_error_handler $type = ' . var_export( $type, true ), 0 );
//error_log( 'DEBUG: mla_error_handler $string = ' . var_export( $string, true ), 0 );
//error_log( 'DEBUG: mla_error_handler $file = ' . var_export( $file, true ), 0 );
//error_log( 'DEBUG: mla_error_handler $line = ' . var_export( $line, true ), 0 );

		switch ( $type ) {
			case E_ERROR:
				$level = 'E_ERROR';
				break;
			case E_WARNING:
				$level = 'E_WARNING';
				break;
			case E_NOTICE:
				$level = 'E_NOTICE';
				break;
			default:
				$level = 'OTHER';
		}

		$path_info = pathinfo( $file );
		$file_name = $path_info['basename'];
		MLAGalleryDownloadChecklist::$mla_errors[] = "Line {$line}: {$level} ({$type}) {$string}";

		/* Don't execute PHP internal error handler */
		return true;
	}

	/**
	 * Ajax handler to download an archie of Media Library items
	 *
	 * @since 1.00
	 *
	 * @return	void	echo HTML for file streaming or download, then exit()
	 */
	public static function mla_download_checklist_action() {
		global $post;
		MLACore::mla_debug_add( __LINE__ . " MLAGalleryDownloadChecklist::mla_download_checklist_action \$_REQUEST = " . var_export( $_REQUEST, true ), self::MLA_DEBUG_CATEGORY );

		$default_arguments = array(
			'action' => self::SHORTCODE_NAME,
			'archive_name' => 'MLA-Checklist-Archive',
			'input_array_name' => 'mla-checklist-archive-items',
			'disposition' => 'delete',
		);

		$arguments = shortcode_atts( $default_arguments, $_REQUEST );
		MLACore::mla_debug_add( __LINE__ . " MLAGalleryDownloadChecklist::mla_download_checklist_action \$arguments = " . var_export( $arguments, true ), self::MLA_DEBUG_CATEGORY );

		$disposition = ( 'keep' === trim( strtolower( $arguments['disposition'] ) ) ) ? 'keep' : 'delete';
		$archive_name = ( ! empty( $arguments['archive_name'] ) ) ? sanitize_title_with_dashes( trim( $arguments['archive_name'] ) ) : MLAGalleryDownloadChecklist::SHORTCODE_NAME;

		$attachments = array();
		if ( ! empty( $_REQUEST[ $arguments['input_array_name'] ] ) ) {
			if ( is_array( $_REQUEST[ $arguments['input_array_name'] ] ) ) {
				$attachments = array_map( 'absint', $_REQUEST[ $arguments['input_array_name'] ] );
			}
		}

		/*
		 * Create unique local names to handle the case where the same file name
		 * appears in multiple year/month/ directories.
		 */
		$file_names = array();
		foreach ( $attachments as $index => $attachment ) {
			$file_name = get_attached_file( $attachment );
			$path_info = pathinfo( $file_name  );
			$local_name = $path_info['basename'];
			$suffix = 0;
			while( array_key_exists( $local_name, $file_names ) ) {
				$suffix++;
				$local_name = $path_info['filename'] . $suffix . '.' . $path_info['extension'];
			}

			$file_names[ $local_name ] = $file_name;
		}
		MLACore::mla_debug_add( __LINE__ . " MLAGalleryDownloadChecklist::mla_download_checklist_action \$file_names = " . var_export( $file_names, true ), self::MLA_DEBUG_CATEGORY );

		// Create the ZIP archive
		$upload_dir = wp_upload_dir();
		$prefix = ( ! empty( $archive_name ) ) ? $archive_name : sanitize_title_with_dashes( trim( MLAGalleryDownloadChecklist::SHORTCODE_NAME ) );
		$date = date("Ymd_B");
		$archive_name = $upload_dir['basedir'] . '/' . "{$prefix}_{$date}.zip";

		if ( file_exists( $archive_name ) ) {
			@unlink( $archive_name );
		}

		// Make sure we have ZIP support
		if ( !class_exists( 'ZipArchive' ) ) {
			$mla_error = sprintf( 'ERROR: The ZIP archive ( %1$s ) could not be created; no ZipArchive support.', $archive_name );
		} else {
			$mla_error = '';

			set_error_handler( 'MLAGalleryDownloadChecklist::mla_error_handler' );
			try {
				$exception = NULL;
				$zip = new ZipArchive();
				MLACore::mla_debug_add( __LINE__ . " MLAGalleryDownloadChecklist::mla_download_checklist_action ZipArchive object created.", self::MLA_DEBUG_CATEGORY );
				if ( true !== $zip->open( $archive_name, ZIPARCHIVE::CREATE ) ) {
					$mla_error = sprintf( 'ERROR: The ZIP archive ( %1$s ) could not be created.', $archive_name );
				} else {
					foreach( $file_names as $local_name => $file_name ) {
						if ( true !== $zip->addFile( $file_name, $local_name ) ) {
							$mla_error = sprintf( 'ERROR: The file ( %1$s ) could not be added to the ZIP archive.', $file_name );
							break;
						}
					} // foreach file
		
					if ( true !== $zip->close() ) {
						$mla_error = sprintf( 'ERROR: The ZIP archive ( %1$s ) could not be closed.', $archive_name );
					}
				}

				MLACore::mla_debug_add( __LINE__ . " MLAGalleryDownloadChecklist::mla_download_checklist_action ZIP Archive created.", self::MLA_DEBUG_CATEGORY );
			} catch ( Throwable $e ) { // PHP 7
				$exception = $e;
				$exif_data = NULL;
			} catch ( Exception $e ) { // PHP 5
				$exception = $e;
				$exif_data = NULL;
			}
			restore_error_handler();

			if ( ! empty( $exception ) ) {
				MLAGalleryDownloadChecklist::$mla_errors[] = sprintf( '(%1$s) %2$s', $exception->getCode(), $exception->getMessage() );
			}

			// Combine exceptions with PHP notice/warning/error messages
			if ( ! empty( MLAGalleryDownloadChecklist::$mla_errors ) ) {
				$mla_error = sprintf( 'ERROR: ZIP archive exception(s) = %1$s', var_export( MLAGalleryDownloadChecklist::$mla_errors, true ) );
				MLAGalleryDownloadChecklist::$mla_errors = array();
			}
		}

		MLACore::mla_debug_add( __LINE__ . " MLAGalleryDownloadChecklist::mla_download_checklist_action mla_error = " . $mla_error, self::MLA_DEBUG_CATEGORY );

		if ( empty( $mla_error ) ) {
			if ( file_exists( $archive_name ) ) {
				$filemtime = filemtime ( $archive_name );
				$filesize = filesize( $archive_name );
			} else {
				$filemtime = time();
				$filesize = 0;
			}

			header('Pragma: public'); 	// required
			header('Expires: 0');		// no cache
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Last-Modified: '.gmdate ( 'D, d M Y H:i:s', $filemtime ).' GMT');
			header('Cache-Control: private',false);
			header('Content-Type: application/zip');
			header('Content-Disposition: attachment; filename="'.basename( $archive_name ).'"');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: '.$filesize);	// provide file size
			header('Connection: close');

			if ( 0 < $filesize ) {
				readfile( $archive_name );
			}

			if ( 'delete' === $disposition ) {
				@unlink( $archive_name );
			}
		} else {
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			echo '<html xmlns="http://www.w3.org/1999/xhtml">';
			echo '<head>';
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo '<title>Download Error</title>';
			echo '</head>';
			echo '';
			echo '<body>';
			echo $mla_error;
			echo '</body>';
			echo '</html> ';
		}

		exit();
	}

	/**
	 * WordPress Shortcode; downloads gallery items as a ZIP archive
	 *
	 * @since 1.00
	 *
	 * @param	array	shortcode parameters; defaults ( 'field_name' => DEFAULT_FIELD, 'ids' => '' )
	 *
	 * @return	string	post/page content to replace the shortcode
	 */
	public static function mla_download_checklist_shortcode( $attr, $content = NULL ) {
		global $post;
		MLACore::mla_debug_add( __LINE__ . " MLAGalleryDownloadChecklist::mla_download_checklist_shortcode \$attr = " . var_export( $attr, true ), self::MLA_DEBUG_CATEGORY );
		MLACore::mla_debug_add( __LINE__ . " MLAGalleryDownloadChecklist::mla_download_checklist_shortcode \$content = " . var_export( $content, true ), self::MLA_DEBUG_CATEGORY );

		$default_arguments = array(
			'archive_name' => 'MLA-Checklist-Archive',
			'input_array_name' => 'mla-checklist-archive-items',
			'button_attributes' => '',
			'button_class' => '',
			'button_text' => 'Download',
			'allow_empty_gallery' => 'true',
			'disposition' => 'delete',
			'empty_text' => '',
		);

		// Create a clean array of shortcode parameters
		$attr = self::_prepare_attributes( $attr, $content );

		// Combine parameters with defaults
		$arguments = shortcode_atts( $default_arguments, $attr );
		MLACore::mla_debug_add( __LINE__ . " MLAGalleryDownloadChecklist::mla_download_checklist_shortcode \$arguments = " . var_export( $arguments, true ), self::MLA_DEBUG_CATEGORY );

		// archive_name is required and must be unique in the post/page
		if ( empty( $arguments['archive_name'] ) ) {
			return '';
		}

		$archive_title = $arguments['archive_name'];
		$archive_name = sanitize_title_with_dashes( trim( $archive_title ) );

		$button_attributes = trim( $arguments['button_attributes'] );
		if ( !empty( $button_attributes ) ) {
			$button_attributes .= ' ';
		}

		$button_class = trim( $arguments['button_class'] );
		if ( !empty( $button_class ) ) {
			$button_class = 'class="' . $button_class . '" ';
		}

		$button_text = trim( $arguments['button_text'] );
		if ( empty( $button_text ) ) {
			$button_text = "Download {$archive_title}";
		}

		$allow_empty_gallery = 'true' === strtolower( trim( $arguments['allow_empty_gallery'] ) );
		$disposition = ( 'keep' === trim( strtolower( $arguments['disposition'] ) ) ) ? 'keep' : 'delete';

		// Check for the empty case, if not allowed
		if ( ! $allow_empty_gallery ) {
			// Create a clean array of shortcode parameters
			$attachments_attr = array_diff_key( $attr, $default_arguments );
			unset( $attachments_attr['numberposts'] );
			$attachments_attr['posts_per_page'] = 1;
			$attachments_attr['fields'] = 'ids';
			MLACore::mla_debug_add( __LINE__ . " MLAGalleryDownloadChecklist::mla_download_checklist_shortcode mla_get_shortcode_attachments( {$post->ID} ) \$attachments_attr = " . var_export( $attachments_attr, true ), self::MLA_DEBUG_CATEGORY );

			$attachments = 	MLAShortcode_Support::mla_get_shortcode_attachments( $post->ID, $attachments_attr, false );
			if ( empty( $attachments ) ) {
				$empty_text = trim( $arguments['empty_text'] );
				if ( empty( $empty_text ) ) {
					return '';
				}

				return $empty_text;
			} // found empty archive
		} // Prevent empty archive

		// AJAX-based link for forced downloads
		$args = array(
			'action' => self::SHORTCODE_NAME,
			'archive_name' => esc_attr( $arguments['archive_name'] ),
			'input_array_name' => esc_attr( $arguments['input_array_name'] ),
		);

		if ( 'keep' === $disposition ) {
			$args['disposition'] = 'keep';
		}

		$form_text = '';
		foreach ( $args as $key => $value ) {
			$input_id = sanitize_title_with_dashes( trim( $archive_title . '-' . $key ) );
			$form_text .= sprintf( '<input type="hidden" id="%1$s" name="%2$s" value="%3$s">', $input_id, esc_attr( $key ), esc_attr( $value ) );
		}

		$input_id = sanitize_title_with_dashes( trim( $archive_title . '-submit' ) );
		$form_text .= sprintf( '<input %1$s%2$stype="submit" id="%3$s" name="%4$s" value="%5$s">', $button_attributes, $button_class, $input_id, $archive_name, esc_attr( $button_text ) );

		return $form_text;
	} //mla_download_checklist_shortcode
} //MLAGalleryDownloadChecklist

// Install the shortcode at an early opportunity
add_action('init', 'MLAGalleryDownloadChecklist::initialize');
?>