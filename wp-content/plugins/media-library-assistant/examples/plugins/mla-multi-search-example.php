<?php
/**
 * Adds custom field search(es) to the [mla_gallery] keyword(s) search results
 *
 * In this example:
 *
 * 1. A custom "multi_search" parameter names one or more "search keys", e.g.
 *        multi_search="keyword:,custom:Country,custom:City"
 *    Where "keyword:" performs the usual Keyword(s) search
 *    and "custom:" searches the custom fields names in the list.
 *    You can code "custom:*" to search ALL custom fields without naming them.
 *
 * 2. Each custom field is queried for a LIKE match with the content of the "s" parameter.<br />
 *    Note that this is more limited than the logic provided by the Keyword(s) Search function.
 *
 * 3. Matches from the custom field search(es) are added to any keyword(s) search matches,
 *    i.e., all searches are joined by "OR".
 *
 * This example plugin uses two of the many filters available in the [mla_gallery] shortcode
 * and illustrates a technique you can use to customize the gallery display.
 *
 * Created for support topic "Gallery layout with thumbnails"
 * opened on 11/20/2016 by "marineb30".
 * https://wordpress.org/support/topic/gallery-layout-with-thumbnails/
 *
 * Enhanced for support topic "Searching custom fields in attachments"
 * opened on 3/25/2024 by "ernstwg".
 * https://wordpress.org/support/topic/searching-custom-fields-in-attachments/
 *
 * @package MLA Multi-search Example
 * @version 1.04
 */

/*
Plugin Name: MLA Multi-search Example
Plugin URI: http://davidlingren.com/
Description: Adds custom field search(es) to the [mla_gallery] keyword(s) search results
Author: David Lingren
Version: 1.04
Author URI: http://davidlingren.com/

Copyright 2016-2024 David Lingren

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
 * Class MLA Multi-search Example hooks all of the filters provided by the [mla_gallery] shortcode
 *
 * Call it anything you want, but give it an unlikely and hopefully unique name. Hiding everything
 * else inside a class means this is the only name you have to worry about.
 *
 * @package MLA Multi-search Example
 * @since 1.00
 */
class MLAMultiSearchExample {
	/**
	 * Constant to log this plugin's debug activity
	 *
	 * @since 1.03
	 *
	 * @var	integer
	 */
	const MLA_DEBUG_CATEGORY = 0x00008000;

	/**
	 * Constant to log this plugin's debug activity; additional details
	 *
	 * @since 1.03
	 *
	 * @var	integer
	 */
	const MLA_DEBUG_DETAILS = 0x00004000;

	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		// The filters are only useful for front-end posts/pages; exit if in the admin section
		if ( is_admin() )
			return;

		add_filter( 'mla_gallery_attributes', 'MLAMultiSearchExample::mla_gallery_attributes', 10, 1 );
		add_filter( 'mla_gallery_query_arguments', 'MLAMultiSearchExample::mla_gallery_query_arguments', 10, 1 );
	}

	/**
	 * Save the shortcode attributes
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $shortcode_attributes = array();

	/**
	 * Save the arguments passed in to the shortcode for use in the custom query
	 *
	 * @since 1.00
	 *
	 * @param	array	the shortcode parameters passed in to the shortcode
	 */
	public static function mla_gallery_attributes( $shortcode_attributes ) {
		if ( isset( $shortcode_attributes['multi_search'] ) ) {
			MLACore::mla_debug_add( __LINE__ . " MLAMultiSearchExample::mla_gallery_attributes \$shortcode_attributes = " . var_export( $shortcode_attributes, true ), self::MLA_DEBUG_CATEGORY );
		}

		// Save the attributes for use in the later filters
		self::$shortcode_attributes = $shortcode_attributes;
		unset( $shortcode_attributes['multi_search'] );

		return $shortcode_attributes;
	} // mla_gallery_attributes

	/**
	 * Generate a list of all (post) Custom Field names
	 *
	 * @since 1.02
	 *
	 * @return	array	Custom field names from the postmeta table
	 */
	private static function _get_custom_field_names( ) {
		global $wpdb;

		$limit = (int) apply_filters( 'postmeta_form_limit', 1000 );
		$keys = $wpdb->get_col( "SELECT meta_key FROM $wpdb->postmeta GROUP BY meta_key HAVING meta_key NOT LIKE '\_%' ORDER BY meta_key LIMIT $limit" ); // phpcs:ignore

		if ( $keys ) {
			natcasesort( $keys );
		}

		MLACore::mla_debug_add( __LINE__ . " MLAMultiSearchExample::_get_custom_field_names \$keys = " . var_export( $keys, true ), self::MLA_DEBUG_DETAILS );
		return $keys;
	} // _get_custom_field_names

	/**
	 * Look for the multi_search parameter and process it
	 *
	 * @since 1.00
	 *
	 * @param	array	shortcode arguments merged with attachment selection defaults, so every possible parameter is present
	 */
	public static function mla_gallery_query_arguments( $all_query_parameters ) {
		//error_log( 'MLAMultiSearchExample::mla_gallery_query_arguments self::$shortcode_attributes = ' . var_export( self::$shortcode_attributes, true ), 0 );
		//error_log( 'MLAMultiSearchExample::mla_gallery_query_arguments $all_query_parameters = ' . var_export( $all_query_parameters, true ), 0 );

		/*
		 * We use a shortcode parameter of our own to apply this filter on a gallery-by-gallery
		 * basis, leaving other [mla_gallery] instances untouched. If the "multi_search" parameter
		 * is not present, we have nothing to do. If the parameter IS present, perform one or more
		 * searches and combine the results.
		 */		
		if ( isset( self::$shortcode_attributes['multi_search'] ) ) {
			global $post;
			
			MLACore::mla_debug_add( __LINE__ . " MLAMultiSearchExample::mla_gallery_query_arguments \$all_query_parameters = " . var_export( $all_query_parameters, true ), self::MLA_DEBUG_CATEGORY );

			$multi_search = self::$shortcode_attributes['multi_search'];
			unset( self::$shortcode_attributes['multi_search'] );
			$attr = self::$shortcode_attributes;
			unset( $attr['s'] );
			
			// remove pagination and sort parameters
			if ( isset( $attr['mla_page_parameter'] ) ) {
				unset( $attr[ $attr['mla_page_parameter'] ] );
			}

			unset( $attr['mla_page_parameter'] );
			unset( $attr['numberposts'] );
			unset( $attr['posts_per_page'] );
			unset( $attr['posts_per_archive_page'] );
			unset( $attr['paged'] );
			unset( $attr['offset'] );
			unset( $attr['mla_paginate_current'] );
			unset( $attr['mla_paginate_total'] );
			$attr['nopaging'] = true;
			$attr['orderby'] = 'none';
			
			$results = array();
			$search_value = !empty( self::$shortcode_attributes['s'] ) ? trim( self::$shortcode_attributes['s'], ' \'"' ) : '';
			$search_keys = explode( ',', $multi_search );
			MLACore::mla_debug_add( __LINE__ . " MLAMultiSearchExample::mla_gallery_query_arguments \$search_keys = " . var_export( $search_keys, true ), self::MLA_DEBUG_CATEGORY );

			foreach( $search_keys as $index => $search_key ) {
				if ( 'custom:*' === $search_key ) {
					unset( $search_keys[ $index ] );
					$names = self::_get_custom_field_names();

					foreach( $names as $name ) {
						$search_keys[] = 'custom:' . $name;
					}
				}
			}

			foreach( $search_keys as $search_key ) {
				$tokens = array_map( 'trim', explode( ':', $search_key ) ); 
				MLACore::mla_debug_add( __LINE__ . " MLAMultiSearchExample::mla_gallery_query_arguments( {$search_key} ) \$tokens = " . var_export( $tokens, true ), self::MLA_DEBUG_DETAILS );
				switch ( $tokens[0] ) {
					case 'keyword':
						$attr['s'] = $search_value;
						$attachments = MLAShortcodes::mla_get_shortcode_attachments( $post->ID, $attr, true );
						unset( $attr['s'] );
						break;
					case 'custom':
						$attr['meta_key'] = $tokens[1];
						$attr['meta_value'] = $search_value;
						$attr['meta_compare'] = 'LIKE';
						$attachments = MLAShortcodes::mla_get_shortcode_attachments( $post->ID, $attr, true );
						unset( $attr['meta_compare'] );
						unset( $attr['meta_value'] );
						unset( $attr['meta_key'] );
						break;
					default:
						$attachments = array();
				} // switch tokens[0]

				if ( is_string( $attachments ) ) {
					MLACore::mla_debug_add( __LINE__ . " MLAMultiSearchExample::mla_gallery_query_arguments( {$search_key} ) \$attachments = " . var_export( $attachments, true ), self::MLA_DEBUG_CATEGORY );
					$attachments = array();
				} else {
					MLACore::mla_debug_add( __LINE__ . " MLAMultiSearchExample::mla_gallery_query_arguments( {$search_key} ) found_rows = " . var_export( $attachments['found_rows'], true ), self::MLA_DEBUG_CATEGORY );
				}
				
				unset( $attachments['found_rows'] );
				unset( $attachments['max_num_pages'] );

				foreach ( $attachments as $attachment ) {
					$results[ $attachment->ID ] = $attachment->ID;
				}
			}

			if ( count( $results ) ) {			
				$all_query_parameters['include'] = implode( ',', $results );
				MLACore::mla_debug_add( __LINE__ . " MLAMultiSearchExample::mla_gallery_query_arguments results count = " . var_export( count( $results ), true ), self::MLA_DEBUG_CATEGORY );
			} else {
				$all_query_parameters['include'] = '1';
			}
			
			$all_query_parameters['s'] = '';
			} // parameter "multi_search" is present

		return $all_query_parameters;
	} // mla_gallery_query_arguments
} // Class MLAMultiSearchExample

// Install the filters at an early opportunity
add_action('init', 'MLAMultiSearchExample::initialize');
?>