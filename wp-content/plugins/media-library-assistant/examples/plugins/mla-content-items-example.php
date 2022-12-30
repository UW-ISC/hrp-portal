<?php
/**
 * Provides "content_items", a custom substitution parameter value with the IDs of Media Library items in the post_content field
 *
 * Created for support topic "Filter tag cloud to tags on current post?"
 * opened on 10/17/2022 by "lastqa":
 * https://wordpress.org/support/topic/filter-tag-cloud-to-tags-on-current-post/
 *
 * @package MLA Content Items Example
 * @version 1.00
 */

/*
Plugin Name: MLA Content Items Example
Plugin URI: http://davidlingren.com/
Description: Provides "content_items", a custom substitution parameter value with the IDs of Media Library items in the post_content field
Author: David Lingren
Version: 1.00
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
 * Class MLA Content Items Example hooks four of the filters provided
 * by the "Field-level substitution parameter filters (Hooks)"
 *
 * @package MLA Content Items Example
 * @since 1.00
 */
class MLAContentItemsExample {
	/**
	 * Constant to log this plugin's debug activity
	 *
	 * @since 1.00
	 *
	 * @var	integer
	 */
	const MLA_DEBUG_CATEGORY = 0x00008000;

	/**
	 * Initialization function, similar to __construct()
	 *
	 * Installs filters and actions that handle the MLA hooks for the
	 * "Field-level substitution parameters"
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		// Defined in /media-library-assistant/includes/class-mla-data.php
		add_filter( 'mla_expand_custom_data_source', 'MLAContentItemsExample::mla_expand_custom_data_source', 10, 9 );
		//add_filter( 'mla_expand_custom_prefix', 'MLAContentItemsExample::mla_expand_custom_prefix', 10, 8 );
		//add_filter( 'mla_apply_custom_format', 'MLAContentItemsExample::mla_apply_custom_format', 10, 2 );

		// Defined in /media-library-assistant/includes/class-mla-data-source.php
		//add_filter( 'mla_evaluate_custom_data_source', 'MLAContentItemsExample::mla_evaluate_custom_data_source', 10, 5 );
	} // initialize

	/**
	 * Evaluate parent_terms: or page_terms: values
	 *
	 * @since 1.00
	 *
	 * @param	array	data-source components; prefix, value, option, format and args (if present)
	 * @param	array	Item markup template values
	 *
	 * @return	mixed	String or array 
	 */
	private static function _evaluate_content( $value, $markup_values ) {
		static $current_post = 0, $current_items = NULL;
		
		if ( $markup_values['id'] === $current_post ) {
			return $current_items;
		}

		$current_post = $markup_values['id'];
		$current_items = array();
		$post_content = $markup_values['page_content'];
		
		$match_count = preg_match_all( '/\<\!-- wp:([^ ]+) (\{[^\}]*?\})/', $post_content, $matches );
		MLACore::mla_debug_add( __LINE__ . " MLAContentItemsExample::_evaluate_content( {$current_post} ) count = {$match_count}, \$matches = " . var_export( $matches, true ), self::MLA_DEBUG_CATEGORY );
		if ( $match_count ) {
			foreach( $matches[1] as $index => $match ) {
error_log( __LINE__ . " MLAContentItemsExample::_evaluate_content( {$current_post}, {$index} ) match = " . var_export( $match, true ), 0 );
				if ( false !== strpos( $match, 'image' ) ) {
					$json_array = json_decode( stripslashes( $matches[2][ $index ] ) );
					MLACore::mla_debug_add( __LINE__ . " MLAContentItemsExample::_evaluate_content( {$current_post}, {$index} ) json_array = " . var_export( $json_array, true ), self::MLA_DEBUG_CATEGORY );
					if ( is_object( $json_array ) && !empty( $json_array->id ) ) {
						$current_items[] = $json_array->id;
					}
				}
			}
		}

		MLACore::mla_debug_add( __LINE__ . " MLAContentItemsExample::_evaluate_content( {$current_post} ) current_items = " . var_export( $current_items, true ), self::MLA_DEBUG_CATEGORY );
		$current_items = implode( ',', $current_items );

		return $current_items;
	} // _evaluate_content

	/**
	 * MLA Expand Custom Data Source Filter
	 *
	 * For shortcode and Content Template processing, gives you an opportunity to generate a custom data value.
	 *
	 * @since 1.00
	 *
	 * @param	string	NULL, indicating that by default, no custom value is available
	 * @param	string	the entire data-source text including option/format and any arguments 
	 * @param	string	the data-source name 
	 * @param	array	data-source components; prefix (empty), value, option, format and args (if present)
	 * @param	array	values from the query, if any, e.g. shortcode parameters
	 * @param	array	item-level markup template values, if any
	 * @param	integer	attachment ID for attachment-specific values
	 * @param	boolean	for option 'multi', retain existing values
	 * @param	string	default option value
	 */
	public static function mla_expand_custom_data_source( $custom_value, $key, $candidate, $value, $query, $markup_values, $post_id, $keep_existing, $default_option ) {
		// Uncomment the error_log statements in any of the filters to see what's passed in
		//error_log( __LINE__ . " MLAContentItemsExample::mla_expand_custom_data_source( {$key}, {$candidate}, {$post_id}, {$keep_existing}, {$default_option} ) value = " . var_export( $value, true ), 0 );
		//error_log( __LINE__ . " MLAContentItemsExample::mla_expand_custom_data_source( {$candidate}, {$post_id} ) query = " . var_export( $query, true ), 0 );
		//error_log( __LINE__ . " MLAContentItemsExample::mla_expand_custom_data_source( {$candidate}, {$post_id} ) markup_values = " . var_export( $markup_values, true ), 0 );

		if ( 'content_items' === $candidate ) {
			return self::_evaluate_content( $value, $markup_values );
		}
		
		return $custom_value;
	} // mla_expand_custom_data_source

	/**
	 * MLA Expand Custom Prefix Filter
	 *
	 * Gives you an opportunity to generate your custom data value when a parameter's prefix value is not recognized.
	 *
	 * @since 1.00
	 *
	 * @param	string	NULL, indicating that by default, no custom value is available
	 * @param	string	the data-source name 
	 * @param	array	data-source components; prefix, value, option, format and args (if present)
	 * @param	array	values from the query, if any, e.g. shortcode parameters
	 * @param	array	item-level markup template values, if any
	 * @param	integer	attachment ID for attachment-specific values
	 * @param	boolean	for option 'multi', retain existing values
	 * @param	string	default option value
	 */
	public static function mla_expand_custom_prefix( $custom_value, $key, $value, $query, $markup_values, $post_id, $keep_existing, $default_option ) {
		//error_log( __LINE__ . " MLAContentItemsExample::mla_expand_custom_prefix( {$key}, {$post_id}, {$keep_existing}, {$default_option} ) value = " . var_export( $value, true ), 0 );
		//error_log( __LINE__ . " MLAContentItemsExample::mla_expand_custom_prefix( {$key}, {$post_id} ) query = " . var_export( $query, true ), 0 );
		//error_log( __LINE__ . " MLAContentItemsExample::mla_expand_custom_prefix( {$key}, {$post_id} ) markup_values = " . var_export( $markup_values, true ), 0 );

		return $custom_value;
	} // mla_expand_custom_prefix

	/**
	 * MLA Apply Custom Format Filter
	 *
	 * Gives you an opportunity to apply your custom option/format to the data value.
	 *
	 * @since 1.00
	 *
	 * @param	string	the data-source value
	 * @param	array	data-source components; prefix (empty), value, option, format and args (if present)
	 */
	public static function mla_apply_custom_format( $value, $args ) {
		//error_log( __LINE__ . " MLAContentItemsExample::mla_apply_custom_format( {$value} ) args = " . var_export( $args, true ), 0 );

		return $value;
	} // mla_apply_custom_format

	/**
	 * MLA Evaluate Custom Data Source Filter
	 *
	 * For metadata mapping rules, gives you an opportunity to generate a custom data value.
	 *
	 * @since 1.00
	 *
	 * @param	string	NULL, indicating that by default, no custom value is available
	 * @param	integer	attachment ID for attachment-specific values
	 * @param	string 	category/scope to evaluate against: custom_field_mapping or single_attachment_mapping
	 * @param	array	data source specification ( name, *data_source, *keep_existing, *format, mla_column, quick_edit, bulk_edit, *meta_name, *option, no_null )
	 * @param	array 	_wp_attachment_metadata, default NULL (use current postmeta database value)
	 */
	public static function mla_evaluate_custom_data_source( $custom_value, $post_id, $category, $data_value, $attachment_metadata ) {
		//error_log( __LINE__ . " MLAContentItemsExample::mla_evaluate_custom_data_source( {$post_id}, {$category} ) data_value = " . var_export( $data_value, true ), 0 );
		//error_log( __LINE__ . " MLAContentItemsExample::mla_evaluate_custom_data_source( {$post_id} ) attachment_metadata = " . var_export( $attachment_metadata, true ), 0 );

		return $custom_value;
	} // mla_evaluate_custom_data_source
} //MLAContentItemsExample

// Install the filters at an early opportunity
add_action('init', 'MLAContentItemsExample::initialize');
?>