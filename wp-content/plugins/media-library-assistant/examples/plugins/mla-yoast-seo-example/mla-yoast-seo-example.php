<?php
/**
 * Supports WordPress SEO by Yoast Page Analysis and XMP Sitemap generation.
 * It looks in each post and page for one or more [mla_gallery] shortcodes and
 * adds XML Sitemap entries for each item displayed by the shortcode(s). It will
 * work with shortcodes that do not require interactive input, i.e., do not have
 * request: or query: substitution parameters.
 *
 * Much more information is in the Settings/MLA CSV Data "Documentation" tab.
 *
 * The sitemap index file should be accessible at http://mysite.com/sitemap_index.xml.
 * Requests to /sitemap.xml should redirect here.
 * Click on an index entry to get the corresponding sitemap.
 * View the SOURCE of the map to see the entries.
 *
 * Created for support topic "MLA Conflicts with Yoast SEO Sitemap"
 * opened on  9/12/2015  by "blsfoto".
 * https://wordpress.org/support/topic/mla-conflicts-with-yoast-seo-sitemap/
 *
 * Enhanced (updates) for support topic "MLA no image attachments in sitemap"
 * opened on  4/9/2017  by "Hey You".
 * https://wordpress.org/support/topic/mla-no-image-attachments-in-sitemap/
 *
 * Enhanced (Schema support) for support topic "MLA Yoast SEO Example"
 * opened on 10/6/2022 by "ernstwg".
 * https://wordpress.org/support/topic/mla-no-image-attachments-in-sitemap/
 *
 * More Yoast information in support topic "MLA galleries and SEO"
 * opened on  3/16/2015  by "jhdean".
 * https://wordpress.org/support/topic/mla-galleries-and-seo/
 *
 * A do_shortcode() variation is in support topic "index images in yoast seo sitemap"
 * opened on  2/14/2019  by "Ibnul H.".
 * https://wordpress.org/support/topic/index-images-in-yoast-seo-sitemap/
 *
 * @package MLA Yoast SEO Example
 * @version 2.00
 */

/*
Plugin Name: MLA Yoast SEO Example
Plugin URI: http://davidlingren.com/
Description: Supports WordPress SEO by Yoast Page Schema and XMP Sitemap generation
Author: David Lingren
Version: 2.00
Author URI: http://davidlingren.com/

Copyright 2022 David Lingren

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
 * Class MLA Yoast SEO Example supports WordPress SEO by Yoast Page Analysis and XMP Sitemap generation
 *
 * @package MLA Yoast SEO Example
 * @since 2.00
 */
class MLAYoastSEOExample {
	/**
	 * Current version number
	 *
	 * @since 2.00
	 *
	 * @var	string
	 */
	const PLUGIN_VERSION = '2.00';

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets, scripts and settings
	 *
	 * @since 2.00
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'MLAYoastSEOExample';

	/**
	 * Constant to log this plugin's debug activity
	 *
	 * @since 2.00
	 *
	 * @var	integer
	 */
	const MLA_DEBUG_CATEGORY = 0x00008000;

	/**
	 * Settings Management object
	 *
	 * @since 2.00
	 *
	 * @var	array
	 */
	private static $plugin_settings = NULL;

	/**
	 * Configuration values for the Settings Management object
	 *
	 * @since 2.00
	 *
	 * @var	array
	 */
	private static $settings_arguments = array(
				'slug_prefix' => self::SLUG_PREFIX,
				'plugin_title' => 'MLA Yoast SEO Example',
				'menu_title' => 'MLA Yoast SEO',
				'plugin_file_name_only' => 'mla-yoast-seo-example',
				'plugin_version' => self::PLUGIN_VERSION,
				'template_file' => '/admin-settings-page.tpl', // Add the path at runtime, in initialize()
				'options' => array(
					// 'slug' => array( 'type' => 'checkbox|text|select|textarea', 'default' => 'text|boolean(0|1)' )
					// See the default values in $_default_settings below
					'enable_xml_sitemap' => array( 'type' => 'checkbox', 'default' => true ),
					'always_use_full_size' => array( 'type' => 'checkbox', 'default' => true ),
					'maximum_sitemap_image_limit' => array( 'type' => 'text', 'default' => '0' ),
					'enable_development_mode' => array( 'type' => 'checkbox', 'default' => false ),
					'enable_article_piece' => array( 'type' => 'checkbox', 'default' => true ),
					'enable_webpage_piece' => array( 'type' => 'checkbox', 'default' => true ),
					'minimum_image_width' => array( 'type' => 'text', 'default' => '696' ),
					'maximum_image_limit' => array( 'type' => 'text', 'default' => '20' ),
					'image_piece_type' => array( 'type' => 'select', 'default' => 'minimal' ),
				),
				'general_tab_values' => array(), // additional page_values for 'page-level-options' template
				'documentation_tab_values' => array(
					'plugin_title' => 'MLA Yoast SEO Example',
					'settingsURL' => '', // Set at runtime in initialize()
				), // page_values for 'documentation-tab' template
			);

	/**
	 * Template file for the Settings page(s) and parts
	 *
	 * This array contains all of the template parts for the Settings page(s). The array is built once
	 * each page load and cached for subsequent use.
	 *
	 * @since 2.00
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
	 * @since 2.00
	 *
	 * @var	array
	 */
	private static $mla_tablist = array(
		'general' => array( 'title' => 'General', 'render' => array( 'MLAYoastSEOExample', '_compose_general_tab' ) ),
		'documentation' => array( 'title' => 'Documentation', 'render' => array( 'MLAYoastSEOExample', '_compose_documentation_tab' ) ),
		);

	/**
	 * Initialization function, similar to __construct()
	 *
	 * Installs filters and actions that handle the MLA hooks for uploading and mapping.
	 *
	 * @since 2.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		// This plugin requires MLA
		if ( ! class_exists( 'MLACore', false ) ) {
			return;
		}

		// The plugin settings class is shared with other MLA example plugins
		if ( ! class_exists( 'MLAExamplePluginSettings101' ) ) {
			require_once( pathinfo( __FILE__, PATHINFO_DIRNAME ) . '/class-mla-example-plugin-settings-101.php' );
		}

		// Add the run-time values to the arguments
		self::$settings_arguments['template_file'] = dirname( __FILE__ ) . self::$settings_arguments['template_file'];
		self::$settings_arguments['documentation_tab_values']['settingsURL'] = admin_url('options-general.php');

		// Create our own settings object
		self::$plugin_settings = new MLAExamplePluginSettings101( self::$settings_arguments );

		/*
		 * Filter: 'wpseo_sitemap_urlimages' - Allows updates to the list of images in the page/post
		 * Defined/applied in /wordpress-seo/inc/class-sitemaps.php or /wordpress-seo/inc/sitemaps/class-sitemap-image-parser.php
		 */
		if ( self::$plugin_settings->get_plugin_option('enable_xml_sitemap') ) {
			add_filter( 'wpseo_sitemap_urlimages', 'MLAYoastSEOExample::wpseo_sitemap_urlimages', 10, 2 );
		}

		/*
		 * Filter: 'wpseo_sitemap_entry' - adjusts the entire entry before it gets added to the sitemap
		 * Defined/applied in /wordpress-seo/inc/class-sitemaps.php or /wordpress-seo/inc/sitemaps/class-post-type-sitemap-provider.php
		 */
		// add_filter( 'wpseo_sitemap_entry', 'MLAYoastSEOExample::wpseo_sitemap_entry', 10, 3 );

		/*
		 * If you're working on Schema, it can be rather hard to read. To change that, you should toggle
		 * the yoast_seo_development_mode filter to true. At that point all the Schema that Yoast SEO
		 * outputs will be pretty printed.
		 */
		if ( self::$plugin_settings->get_plugin_option('enable_development_mode') ) {
			add_filter( 'yoast_seo_development_mode', '__return_true' );
		}

		/**
		 * Filter: 'wpseo_schema_graph_pieces' - Allows adding pieces to the graph.
		 * Defined/applied in /wordpress-seo/src/generators/schema-generator.php
		 *
		 * @param Meta_Tags_Context $context An object with context variables.
		 *
		 * @api array $pieces The schema pieces.
		 */
		// add_filter( 'wpseo_schema_graph_pieces', 'MLAYoastSEOExample::wpseo_schema_graph_pieces', 11, 2 );

		/**
		 * Allows filtering the graph piece by its schema type.
		 * Defined/applied in /wordpress-seo/src/generators/schema-generator.php
		 *
		 * Note: We removed the Abstract_Schema_Piece type-hint from the $graph_piece_generator argument, because
		 *       it caused conflicts with old code, Yoast SEO Video specifically.
		 *
		 * @param array                   $graph_piece            The graph piece we're filtering.
		 * @param string                  $identifier             The identifier of the graph piece that is being filtered.
		 * @param Meta_Tags_Context       $context                The meta tags context.
		 * @param Abstract_Schema_Piece   $graph_piece_generator  A value object with context variables.
		 * @param Abstract_Schema_Piece[] $graph_piece_generators A value object with context variables.
		 *
		 * @return array The filtered graph piece.
		 */
		if ( self::$plugin_settings->get_plugin_option('enable_article_piece') ) {
			add_filter( 'wpseo_schema_article', 'MLAYoastSEOExample::wpseo_schema_article', 11, 1 );
		}

		// add_filter( 'wpseo_schema_main_image', 'MLAYoastSEOExample::wpseo_schema_main_image', 11, 1 );

		if ( self::$plugin_settings->get_plugin_option('enable_webpage_piece') ) {
			add_filter( 'wpseo_schema_webpage', 'MLAYoastSEOExample::wpseo_schema_webpage', 11, 1 );
		}

		// The remaining filters are only useful in the admin section; exit if in the "front-end" posts/pages. 
		if ( ! is_admin() ) {
			return;
		}

		// Add the run-time values to the settings
		// Load template array and initialize page-level values.
		self::$page_template_array = MLACore::mla_load_template( self::$settings_arguments['template_file'], 'path' );

		$general_tab_values = self::$plugin_settings->get_plugin_argument('general_tab_values');
		$general_tab_values['minimal_selected'] = 'minimal' === self::$plugin_settings->get_plugin_option('image_piece_type') ? 'selected=selected' : '';
		$general_tab_values['simple_selected'] = 'simple' === self::$plugin_settings->get_plugin_option('image_piece_type') ? 'selected=selected' : '';
		$general_tab_values['complete_selected'] = 'complete' === self::$plugin_settings->get_plugin_option('image_piece_type') ? 'selected=selected' : '';
		self::$plugin_settings->update_plugin_argument('general_tab_values', $general_tab_values );
	} // initialize

	/**
	 * Attachment information passed from mla_gallery_item_values to _build_attachment_array
	 *
	 * @since 2.00
	 *
	 * @var array	Selected values from the [mla_gallery] item_values array.
	 */
	private static $_item_values = NULL;

	/**
	 * Add [mla_gallery] output to 'images' array for SEO analysis
	 *
	 * @since 2.00
	 *
	 * @param	integer	$post_id ID of the current post
	 */
	private static function _build_attachment_array( $post_id ) {
		global $post;
		static $current_post = 0;

		if ( $post_id === $current_post ) {
			return;
		}

		$post = get_post( $post_id ); // Set the parent post/page; used in [mla_gallery]
		$current_post = $post_id;
//error_log( __LINE__ . " _build_attachment_array( {$post_id} ) post_content = " . var_export( $post->post_content, true ), 0 );


		self::$_item_values = array();
		add_filter( 'mla_gallery_item_values', 'MLAYoastSEOExample::mla_gallery_item_values', 10, 1 );

		// Look for and process enclosing shortcode ayntax first
		if ( strpos( $post->post_content, '[/mla_gallery]' ) ) {
			$count = preg_match_all( "/\\[mla_gallery([^\\]]*)\\](.*?)(\\[\\/mla_gallery\\])/s", $post->post_content, $matches ); //, PREG_PATTERN_ORDER + PREG_OFFSET_CAPTURE );
			if ( $count ) {
				foreach ( $matches[0] as $index => $match ) {
//error_log( __LINE__ . " _build_attachment_array( {$post_id} ) enclosing shortcode match = " . var_export( $match, true ), 0 );
					$the_gallery = do_shortcode( $match );
				} // foreach match
			} // $count
		} // enclosing shortcode(s)

		if ( $count = preg_match_all( "/\\[mla_gallery([^\\]]*)\\]/", $post->post_content, $matches ) ) {
			foreach( $matches[0] as $index => $match ) {
//error_log( __LINE__ . " _build_attachment_array( {$post_id} ) do_shortcode match = " . var_export( $match, true ), 0 );
				$tail = $matches[1][ $index ];

				// Only process shortcodes that are an exact match
				if ( empty( $tail ) || ( ' ' == substr( $tail, 0, 1 ) ) ) {
					$the_gallery = do_shortcode( $match );
				} // exact match
			}
			unset( $count, $matches );
		} // found matche(s)

		remove_filter( 'mla_gallery_item_values', 'MLAYoastSEOExample::mla_gallery_item_values', 10 );
//error_log( __LINE__ . " _build_attachment_array( {$post_id} ) self::\$_item_values = " . var_export( self::$_item_values, true ), 0 );
	} // _build_attachment_array

	/**
	 * MLA Gallery Item Values
	 *
	 * @since 2.00
	 *
	 * @param	array	parameter_name => parameter_value pairs
	 */
	public static function mla_gallery_item_values( $item_values ) {
		//error_log( 'MLAYoastSEOExample::mla_gallery_item_values $item_values = ' . var_export( $item_values, true ), 0 );

		/*
		  'page_url' => 'http://l.wpectest/28-2/',
		  'size_class' => 'thumbnail',
		  'attachment_ID' => 9,
		  'mime_type' => 'image/jpeg',
		  'width' => 1024,
		  'height' => 768,
		  'image_alt' => '',
		  'file_url' => 'http://l.wpectest/wp-content/uploads/2022/09/Day1GCN010-1024x768-1.jpg',
		  'caption' => 'Big Slide',
		  'thumbnail_width' => '150',
		  'thumbnail_height' => '113',
		  'thumbnail_url' => 'http://l.wpectest/wp-content/uploads/2022/09/Day1GCN010-1024x768-1-150x113.jpg',
		 */

		if ( 0 === strpos( $item_values['mime_type'], 'image' ) ) {
			self::$_item_values[ $item_values['attachment_ID'] ] = array(
				'mime_type' => $item_values['mime_type'],
				'width' => (integer) $item_values['width'],
				'image_alt' => $item_values['image_alt'],
				'file_url' => $item_values['file_url'],
				'caption' => $item_values['caption'],
				'thumbnail_width' => (integer) $item_values['thumbnail_width'],
				'thumbnail_url' => $item_values['thumbnail_url'],
			);
		}

		return $item_values;
	} // mla_gallery_item_values

	/**
	 * Add [mla_gallery] output to 'images' array for SEO analysis
	 *
	 * @since 2.00
	 *
	 * @param	array	$url ( [index] => array( 'src' => URL of image file, 'alt' => ALT Text ) )
	 * @param	integer	$post_id ID of the current post
	 */
	public static function wpseo_sitemap_urlimages( $url, $post_id ) {
		self::_build_attachment_array( $post_id );

		$maximum_limit = (integer) self::$plugin_settings->get_plugin_option('maximum_sitemap_image_limit');
		$test_maximum = ( $maximum_limit > 0 );

		if ( $test_maximum ) {
			if ( $maximum_limit < count( $url ) ) {
				return $url;
			}
			
			$maximum_limit -= count( $url );
		}

		$use_full_size = self::$plugin_settings->get_plugin_option('always_use_full_size');
		$mla_urls = array();
		foreach ( self::$_item_values as $ID => $values ) {
			if ( $test_maximum && ( 0 === $maximum_limit-- ) ) {
				break;
			}

			if ( $use_full_size ) {
				$entry = array( 'src' => $values['file_url'] );
			} else {
				$entry = array( 'src' => $values['thumbnail_url'] );
			}

			if ( !empty( $values['image_alt'] ) ) {
				$entry['alt'] = $values['image_alt'];
			}

			$mla_urls[] = $entry;
		}

		$url = array_merge( $url, $mla_urls );

//error_log( __LINE__ . " wpseo_sitemap_urlimages( {$post_id} ) final url = " . var_export( $url, true ), 0 );
		return $url;
	} // wpseo_sitemap_urlimages

	/**
	 * Filter URL entry before it gets added to the sitemap.
	 *
	 * @since 2.00
	 *
	 * @param	array	$url  Array of URL parts, e.g. 'loc', 'mod', 'images'.
	 * @param	string	$post_type URL type: user, post or term
	 * @param	object	$the_post The user/post/term object.
	 */
	public static function wpseo_sitemap_entry( $url, $post_type, $the_post ) {
//error_log( __LINE__ . " wpseo_sitemap_entry( {$post_type}, {$the_post->ID} ) initial url array = " . var_export( $url, true ), 0 );
//error_log( __LINE__ . " wpseo_sitemap_entry( {$post_type}, {$the_post->ID} ) the_post = " . var_export( $the_post, true ), 0 );

		return $url;
	} // wpseo_sitemap_entry

	/**
	 * Adds a custom graph piece to the schema collector.
	 *
	 * @since 2.00
	 *
	 * @param array  $pieces  The current graph pieces.
	 * @param object $context The current context.
	 *
	 * @return array The graph pieces.
	 */
	public static function wpseo_schema_graph_pieces( $pieces, $context ) {
//error_log( __LINE__ . " wpseo_schema_graph_pieces pieces = " . var_export( $pieces, true ), 0 );

		return $pieces;
	} // wpseo_schema_graph_pieces

	/**
	 * Adds images to Article Schema data.
	 *
	 * @since 2.00
	 *
	 * @param array $data Schema.org Article data array.
	 *
	 * @return array Schema.org Article data array.
	 */
	public static function wpseo_schema_article( $data ) {
		global $post;
//error_log( __LINE__ . " wpseo_schema_article post = " . var_export( $post, true ), 0 );
//error_log( __LINE__ . " wpseo_schema_article data = " . var_export( $data, true ), 0 );

		self::_build_attachment_array( $post->ID );
		if ( empty( self::$_item_values ) ) {
			return $data;
		}

		if ( !empty( $data['image'] ) ) {
			$primary = $data['image'];
			$data['image'] = array();
			$data['image'][] = $primary;
		} else {
			$data['image'] = array();
		}

		$schema_id     = $data['isPartOf']['@id'] . '#schema/image/';
		$piece_type = self::$plugin_settings->get_plugin_option('image_piece_type');
		$minimum_width = (integer) self::$plugin_settings->get_plugin_option('minimum_image_width');
		$maximum_limit = (integer) self::$plugin_settings->get_plugin_option('maximum_image_limit');
		$test_maximum = ( $maximum_limit > 0 );
		
		foreach ( self::$_item_values as $ID => $values ) {
			if ( isset( $values['width'] ) && ( $values['width'] < $minimum_width ) ) {
				continue;
			}

			if ( $test_maximum && ( 0 === $maximum_limit-- ) ) {
				break;
			}

			$item_id = $schema_id . $ID;
			self::$_item_values[ $ID ]['@id'] = $item_id;

			switch ( $piece_type ) {
				case 'complete':
					$data['image'][] = YoastSEO()->helpers->schema->image->generate_from_attachment_id( $item_id, $ID, $values['caption'] );
					break; 
				case 'simple':
					$data['image'][] = YoastSEO()->helpers->schema->image->simple_image_object( $item_id, $values['file_url'] );
					break; 
				case 'minimal':
				default:
					self::$_item_values[ $ID ]['@id'] = $values['file_url'];
					$data['image'][] = array( '@id' => $values['file_url'] );
			}
		}

//		$data['image'][] = YoastSEO()->helpers->schema->image->generate_from_url( $schema_id . '2', 'http://l.wpectest/wp-content/uploads/2022/09/staff-photographer-example-150x102.jpg' );

//error_log( __LINE__ . " wpseo_schema_article data = " . var_export( $data, true ), 0 );
		return $data;
	} // wpseo_schema_article

	/**
	 * Adds images to Main_Image Schema data.
	 *
	 * @since 2.00
	 *
	 * @param array $data Schema.org Main_Image data array.
	 *
	 * @return array Schema.org Main_Image data array.
	 */
	public static function wpseo_schema_main_image( $data ) {
//error_log( __LINE__ . " wpseo_schema_main_image data = " . var_export( $data, true ), 0 );
		return $data;
	} // wpseo_schema_main_image

	/**
	 * Adds images to WebPage Schema data.
	 *
	 * @since 2.00
	 *
	 * @param array $data Schema.org WebPage data array.
	 *
	 * @return array Schema.org WebPage data array.
	 */
	public static function wpseo_schema_webpage( $data ) {
		global $post;

		self::_build_attachment_array( $post->ID );
		if ( empty( self::$_item_values ) ) {
//error_log( __LINE__ . " wpseo_schema_webpage unchanged data = " . var_export( $data, true ), 0 );
			return $data;
		}

		if ( !empty( $data['image'] ) ) {
			$primary = $data['image'];
			$data['image'] = array();
			$data['image'][] = $primary;
		} else {
			$data['image'] = array();
		}

		$schema_id     = $data['isPartOf']['@id'] . '#schema/image/';
		$piece_type = self::$plugin_settings->get_plugin_option('image_piece_type');
		$minimum_width = (integer) self::$plugin_settings->get_plugin_option('minimum_image_width');
		$maximum_limit = (integer) self::$plugin_settings->get_plugin_option('maximum_image_limit');
		$test_maximum = ( $maximum_limit > 0 );

//error_log( __LINE__ . " wpseo_schema_webpage( {$post->ID} ) self::\$_item_values = " . var_export( self::$_item_values, true ), 0 );
		foreach ( self::$_item_values as $ID => $values ) {
			if ( isset( $values['width'] ) && ( $values['width'] < $minimum_width ) ) {
				continue;
			}

			if ( $test_maximum && ( 0 === $maximum_limit-- ) ) {
				break;
			}

			if ( isset( $values['@id'] ) ) {
				$data['image'][] = array( '@id' => $values['@id'] );
				continue;
			}

			$item_id = $schema_id . $ID;
			self::$_item_values[ $ID ]['@id'] = $item_id;

			switch ( $piece_type ) {
				case 'complete':
					$data['image'][] = YoastSEO()->helpers->schema->image->generate_from_attachment_id( $item_id, $ID, $values['caption'] );
					break; 
				case 'simple':
					$data['image'][] = YoastSEO()->helpers->schema->image->simple_image_object( $item_id, $values['file_url'] );
					break; 
				case 'minimal':
				default:
					self::$_item_values[ $ID ]['@id'] = $values['file_url'];
					$data['image'][] = array( '@id' => $values['file_url'] );
			}
		}

//error_log( __LINE__ . " wpseo_schema_webpage updated data = " . var_export( $data, true ), 0 );
		return $data;
	} // wpseo_schema_webpage
} //MLAYoastSEOExample

// Install the filters at an early opportunity
add_action('init', 'MLAYoastSEOExample::initialize');
?>