<?php
/**
 * Provides an example of hooking the filters provided by the [mla_archive_list] shortcode
 *
 * In this example, the CSS style attributes for each flat/cloud item are modified to include a "color" attribute,
 * giving each term a color related to its associated count. The example documents ALL the filters
 * available in the [mla_archive_list] shortcode.
 *
 * @package MLA Archive List Hooks Example
 * @version 1.00
 */

/*
Plugin Name: MLA Archive List Hooks Example
Plugin URI: http://davidlingren.com/
Description: Provides an example of hooking the filters provided by the [mla_archive_list] shortcode
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
 * Class MLA Archive List Hooks Example hooks all of the filters provided by the [mla_archive_list] shortcode
 *
 * Call it anything you want, but give it an unlikely and hopefully unique name. Hiding everything
 * else inside a class means this is the only name you have to worry about.
 *
 * @package MLA Archive List Hooks Example
 * @since 1.00
 */
class MLAArchiveListHooksExample {
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

		/*
		 * add parameters:
		 * $tag - name of the hook you're filtering; defined by [mla_archive_list]
		 * $function_to_add - function to be called when [mla_archive_list] applies the filter
		 * $priority - default 10; lower runs earlier, higher runs later
		 * $accepted_args - number of arguments your function accepts
		 *
		 * Comment out the filters you don't need; save them for future use
		 */
		add_filter('mla_archive_list_raw_attributes', 'MLAArchiveListHooksExample::mla_archive_list_raw_attributes', 10, 1 );
		add_filter('mla_archive_list_attributes', 'MLAArchiveListHooksExample::mla_archive_list_attributes', 10, 1 );
		add_filter('mla_archive_list_arguments', 'MLAArchiveListHooksExample::mla_archive_list_arguments', 10, 1 );

		add_filter('mla_get_archive_values_query_arguments', 'MLAArchiveListHooksExample::mla_get_archive_values_query_arguments', 10, 2 );
		add_filter('mla_get_archive_values_query', 'MLAArchiveListHooksExample::mla_get_archive_values_query', 10, 2 );
		add_filter('mla_get_archive_values_query_results', 'MLAArchiveListHooksExample::mla_get_archive_values_query_results', 10, 1 );

		add_filter('mla_archive_list_scale', 'MLAArchiveListHooksExample::mla_archive_list_scale', 10, 4 );

		add_filter('use_mla_archive_list_style', 'MLAArchiveListHooksExample::use_mla_archive_list_style', 10, 2 );

		add_filter('mla_archive_list_style_values', 'MLAArchiveListHooksExample::mla_archive_list_style_values', 10, 1 );
		add_filter('mla_archive_list_style_template', 'MLAArchiveListHooksExample::mla_archive_list_style_template', 10, 1 );
		add_filter('mla_archive_list_style_parse', 'MLAArchiveListHooksExample::mla_archive_list_style_parse', 10, 3 );

		add_filter('mla_archive_list_open_values', 'MLAArchiveListHooksExample::mla_archive_list_open_values', 10, 1 );
		add_filter('mla_archive_list_open_template', 'MLAArchiveListHooksExample::mla_archive_list_open_template', 10, 1 );
		add_filter('mla_archive_list_open_parse', 'MLAArchiveListHooksExample::mla_archive_list_open_parse', 10, 3 );

		add_filter('mla_archive_list_item_values', 'MLAArchiveListHooksExample::mla_archive_list_item_values', 10, 2 );
		add_filter('mla_archive_list_item_template', 'MLAArchiveListHooksExample::mla_archive_list_item_template', 10, 1 );
		add_filter('mla_archive_list_item_parse', 'MLAArchiveListHooksExample::mla_archive_list_item_parse', 10, 3 );

		add_filter('mla_archive_list_close_values', 'MLAArchiveListHooksExample::mla_archive_list_close_values', 10, 1 );
		add_filter('mla_archive_list_close_template', 'MLAArchiveListHooksExample::mla_archive_list_close_template', 10, 1 );
		add_filter('mla_archive_list_close_parse', 'MLAArchiveListHooksExample::mla_archive_list_close_parse', 10, 3 );
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
	 * MLA Archive List Raw (Display) Attributes
	 *
	 * This filter gives you an opportunity to record or modify the arguments passed in to the shortcode
	 * before they pass through the logic to handle the 'mla_page_parameter' and "request:" prefix processing.
	 *
	 * The $shortcode_attributes array is where you will find any of your own parameters that are coded in the
	 * shortcode, e.g., [mla_archive_list my_parameter="my value"].
	 *
	 * @since 1.00
	 *
	 * @param	array	the raw shortcode parameters passed in to the shortcode
	 *
	 * @return	array	updated shortcode attributes
	 */
	public static function mla_archive_list_raw_attributes( $shortcode_attributes ) {
		// Uncomment the error_log statements in any of the filters to see what's passed in
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_raw_attributes $shortcode_attributes = ' . var_export( $shortcode_attributes, true ), 0 );

		/*
		 * Note that the global $post; object is available here and in all later filters.
		 * It contains the post/page on which the [mla_archive_list] appears.
		 * Some [mla_archive_list] invocations are not associated with a post/page; these will
		 * have a substitute $post object with $post->ID == 0.
		 */
		global $post;
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_raw_attributes $post->ID = ' . var_export( $post->ID, true ), 0 );

		return $shortcode_attributes;
	} // mla_archive_list_raw_attributes

	/**
	 * MLA Archive List (Display) Attributes
	 *
	 * This filter gives you an opportunity to record or modify the arguments passed in to the shortcode
	 * before they are merged with the default arguments used for the gallery display.
	 *
	 * The $shortcode_attributes array is where you will find any of your own parameters that are coded in the
	 * shortcode, e.g., [mla_archive_list my_parameter="my value"].
	 *
	 * @since 1.00
	 * @uses MLAArchiveListHooksExample::$shortcode_attributes
	 *
	 * @param	array	the shortcode parameters passed in to the shortcode
	 *
	 * @return	array	updated shortcode attributes
	 */
	public static function mla_archive_list_attributes( $shortcode_attributes ) {
		// Uncomment the error_log statements in any of the filters to see what's passed in
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_attributes $shortcode_attributes = ' . var_export( $shortcode_attributes, true ), 0 );

		// Save the attributes for use in the later filters
		self::$shortcode_attributes = $shortcode_attributes;

		// Filters must return the first argument passed in, unchanged or updated
		return $shortcode_attributes;
	} // mla_archive_list_attributes

	/**
	 * Save the shortcode arguments
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $all_display_arguments = array();

	/**
	 * MLA Archive List (Display) Arguments
	 *
	 * This filter gives you an opportunity to record or modify the gallery display arguments
	 * after the shortcode attributes are merged with the default arguments.
	 *
	 * Note that the values in this array are input or default values, not the final computed values
	 * used for the gallery display.  The computed values are in the $style_values, $markup_values and
	 * $item_values arrays passed to later filters below.
	 *
	 * @since 1.00
	 *
	 * @uses MLAArchiveListHooksExample::$all_display_arguments
	 *
	 * @param	array	shortcode arguments merged with gallery display defaults, so every parameter is present
	 *
	 * @return	array	updated gallery display arguments
	 */
	public static function mla_archive_list_arguments( $all_display_arguments ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_arguments $all_display_arguments = ' . var_export( $all_display_arguments, true ), 0 );

		self::$all_display_arguments = $all_display_arguments;
		return $all_display_arguments;
	} // mla_archive_list_arguments

	/**
	 * Save the query arguments
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $all_query_arguments = array();

	/**
	 * MLA Archive List Query Arguments
	 *
	 * This filter gives you an opportunity to record or modify the attachment query arguments
	 * after the shortcode attributes are merged with the default arguments.
	 *
	 * @since 1.00
	 * @uses MLAArchiveListHooksExample::$all_query_arguments
	 *
	 * @param	array	shortcode arguments merged with attachment selection defaults, so every parameter is present
	 * @param string[] $pieces Associative array of the pieces of the query.
	 *
	 * @return	array	updated attachment query arguments
	 */
	public static function mla_get_archive_values_query_arguments( $all_query_arguments, $pieces ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_get_archive_values_query_arguments $all_query_arguments = ' . var_export( $all_query_arguments, true ), 0 );
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_get_archive_values_query_arguments $pieces = ' . var_export( $pieces, true ), 0 );

		self::$all_query_arguments = $all_query_arguments;
		return $all_query_arguments;
	} // mla_get_archive_values_query_arguments

	/**
	 * MLA Archive List Query Clauses
	 *
	 * This action gives you a final opportunity to inspect or modify
	 * the SQL clauses for the data selection process.
	 *
	 * @since 1.00
	 *
	 * @param	array	SQL query content
	 * @param	array	shortcode arguments merged with attachment selection defaults, so every possible parameter is present
	 *
	 * @return	array	updated SQL query content
	 */
	public static function mla_get_archive_values_query( $query, $query_arguments ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_get_archive_values_query $query = ' . var_export( $query, true ), 0 );
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_get_archive_values_query $query_arguments = ' . var_export( $query_arguments, true ), 0 );

		return $query;
	} // mla_get_archive_values_query

	/**
	 * MLA Archive List Query Results
	 *
	 * This action gives you an opportunity to inspect, save, modify, reorder, etc.
	 * the array of list items returned from the data selection process.
	 *
	 * @since 1.00
	 *
	 * @param	array	list items
	 *
	 * @return	array	updated list items
	 */
	public static function mla_get_archive_values_query_results( $list_items ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_get_archive_values_query_results $list_items = ' . var_export( $list_items, true ), 0 );

		return $list_items;
	} // mla_get_archive_values_query_results

	/**
	 * MLA Archive List scale filter
	 *
	 * This filter gives you an opportunity to record or modify the scaled count value,
	 * for determining the font size assigned to each cloud term.
	 * The default fomula for scaling the count is round(log10($tag->count + 1) * 100).
	 * This filter is called once for each filter as the item-specific substitution parameters
	 * are calculated.
	 *
	 * @since 1.00
	 *
	 * @param	float	default scaled count for the tag
	 * @param	array	the shortcode parameters passed in to the shortcode
	 * @param	array	shortcode arguments merged with gallery display defaults, so every possible parameter is present
	 * @param	object	item object for the current item
	 *
	 * @return	array	updated scaled count
	 */
	public static function mla_archive_list_scale( $scaled_count, $shortcode_attributes, $all_display_arguments, $item ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_scale $scaled_count = ' . var_export( $scaled_count, true ), 0 );
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_scale $shortcode_attributes = ' . var_export( $shortcode_attributes, true ), 0 );
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_scale $all_display_arguments = ' . var_export( $all_display_arguments, true ), 0 );
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_scale $item = ' . var_export( $item, true ), 0 );

		return $scaled_count;
	} // mla_archive_list_scale

	/**
	 * Use MLA Archive List Style
	 *
	 * You can use this filter to allow or suppress the inclusion of CSS styles in the
	 * gallery output. Return 'true' to allow the styles, false to suppress them. You can also
	 * suppress styles by returning an empty string from the mla_archive_list_style_parse below.
	 *
	 * @since 1.00
	 *
	 * @param	boolean	true unless the mla_style parameter is "none"
	 * @param	string	value of the mla_style parameter
	 *
	 * @return	boolean	true to fetch and parse the style template, false to leave it empty
	 */
	public static function use_mla_archive_list_style( $use_style_template, $style_template_name ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::use_mla_archive_list_style $use_style_template = ' . var_export( $use_style_template, true ), 0 );
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::use_mla_archive_list_style $style_template_name = ' . var_export( $style_template_name, true ), 0 );

		return $use_style_template;
	} // use_mla_archive_list_style

	/**
	 * MLA Archive List Style Values
	 *
	 * The "Values" series of filters gives you a chance to modify the substitution parameter values
	 * before they are used to complete the associated template (in the corresponding "Parse" filter).
	 * It is called just before the values are used to parse the associated template.
	 * You can add, change or delete parameters as needed.
	 *
	 * @since 1.00
	 *
	 * @param	array	parameter_name => parameter_value pairs
	 *
	 * @return	array	updated substitution parameter name => value pairs
	 */
	public static function mla_archive_list_style_values( $style_values ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_style_values $style_values = ' . var_export( $style_values, true ), 0 );

		// You also have access to the PHP Super Globals, e.g., $_REQUEST, $_SERVER
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_style_values $_REQUEST = ' . var_export( $_REQUEST, true ), 0 );
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_style_values $_SERVER[ REQUEST_URI ] = ' . var_export( $_SERVER['REQUEST_URI'], true ), 0 );

		/*
		 * You can use the WordPress globals like $wp_query, $wpdb and $table_prefix as well.
		 * Note that $wp_query contains values for the post/page query, NOT the [mla_archive_list] query.
		 */
		global $wp_query;
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_style_values $wp_query->query = ' . var_export( $wp_query->query, true ), 0 );

		return $style_values;
	} // mla_archive_list_style_values

	/**
	 * MLA Archive List Style Template
	 *
	 * The "Template" series of filters gives you a chance to modify the template value before
	 * it is used to generate the HTML markup (in the corresponding "Parse" filter).
	 * It is called just before the template is used to generate the markup.
	 * You can modify the template as needed.
	 *
	 * @since 1.00
	 *
	 * @param	string	template used to generate the HTML markup
	 *
	 * @return	string	updated template
	 */
	public static function mla_archive_list_style_template( $style_template ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_style_template $style_template = ' . var_export( $style_template, true ), 0 );

		return $style_template;
	} // mla_archive_list_style_template

	/**
	 * MLA Archive List Style Parse
	 *
	 * The "Parse" series of filters gives you a chance to modify or replace the HTML markup
	 * that will be added to the [mla_archive_list] output. It is called just after the values array
	 * (updated in the corresponding "Values" filter) is combined (parsed) with the template.
	 * You can modify the HTML markup already prepared or start over with the template and the
	 * substitution values.
	 *
	 * @since 1.00
	 *
	 * @param	string	HTML markup returned by the template parser
	 * @param	string	template used to generate the HTML markup
	 * @param	array	parameter_name => parameter_value pairs
	 *
	 * @return	array	updated HTML markup for gallery output
	 */
	public static function mla_archive_list_style_parse( $html_markup, $style_template, $style_values ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_style_parse $html_markup = ' . var_export( $html_markup, true ), 0 );
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_style_parse $style_template = ' . var_export( $style_template, true ), 0 );
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_style_parse $style_values = ' . var_export( $style_values, true ), 0 );

		return $html_markup;
	} // mla_archive_list_style_parse

	/**
	 * MLA Archive List Open Values
	 *
	 * Note: The $markup_values array is shared among the open, row open, row close and close functions.
	 * It is also used to initialize the $item_values array.
	 *
	 * @since 1.00
	 *
	 * @param	array	parameter_name => parameter_value pairs
	 *
	 * @return	array	updated substitution parameter name => value pairs
	 */
	public static function mla_archive_list_open_values( $markup_values ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_open_values $markup_values = ' . var_export( $markup_values, true ), 0 );

		return $markup_values;
	} // mla_archive_list_open_values

	/**
	 * MLA Archive List Open Template
	 *
	 * @since 1.00
	 *
	 * @param	string	template used to generate the HTML markup
	 *
	 * @return	string	updated template
	 */
	public static function mla_archive_list_open_template( $open_template ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_open_template $open_template = ' . var_export( $open_template, true ), 0 );

		return $open_template;
	} // mla_archive_list_open_template

	/**
	 * MLA Archive List Open Parse
	 *
	 * @since 1.00
	 *
	 * @param	string	HTML markup returned by the template parser
	 * @param	string	template used to generate the HTML markup
	 * @param	array	parameter_name => parameter_value pairs
	 *
	 * @return	array	updated HTML markup for gallery output
	 */
	public static function mla_archive_list_open_parse( $html_markup, $open_template, $markup_values ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_open_parse $html_markup = ' . var_export( $html_markup, true ), 0 );
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_open_parse $open_template = ' . var_export( $open_template, true ), 0 );
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_open_parse $markup_values = ' . var_export( $markup_values, true ), 0 );

		return $html_markup;
	} // mla_archive_list_open_parse

	/**
	 * MLA Archive List Item Values
	 *
	 * @since 1.00
	 *
	 * @param	array	parameter_name => parameter_value pairs
	 * @param	string	'paginate_prev', 'paginate_item', 'paginate_next',
	 *                  'empty_visible', 'option_all', or 'list_item'
	 *
	 * @return	array	updated substitution parameter name => value pairs
	 */
	public static function mla_archive_list_item_values( $item_values, $item_type ) {
		//error_log( __LINE__ . " MLAArchiveListHooksExample::mla_archive_list_item_values( {$item_type} ) \$item_values = " . var_export( $item_values, true ), 0 );

		/*
		 * For this example, we color the "heat map" of cloud item values. We use a shortcode parameter of our
		 * own to do this on a list-by-list basis, leaving other [mla_archive_list] instances untouched.
		 */
		if ( isset( self::$shortcode_attributes['my_filter'] ) && 'color cloud' == self::$shortcode_attributes['my_filter'] && ( 'list_item' === $item_type ) ) {
			// Calculate color so smallest items are red, middle items are green and biggest items are blue
			$spread = (float) $item_values['max_scaled_count'] - $item_values['min_scaled_count'];
			$half_spread = (float) $spread / 2;
			$mid_point = (float) $item_values['min_scaled_count'] + $half_spread;
			$scaled_count = (float) $item_values['scaled_count'];

			$green = 255;
			$red = $blue = 0;
			if ( $half_spread ) {
				$green = (integer) 255.0 - ( 255.0 * ( absint( $scaled_count - $mid_point ) / $half_spread ) );
				if ( $scaled_count < $mid_point ) {
					$red = (integer) 255.0 * ( ( $mid_point - $scaled_count ) / $half_spread );
				} elseif ( $scaled_count > $mid_point ) {
					$blue = (integer) 255.0 * ( ( $scaled_count - $mid_point ) / $half_spread );
				}
			}
			 
			// 'currentlink' and 'thelink' are already composed at this point, so we must update both of them
			$old_style = $item_values['item_link_style'];
			$new_style = $old_style . sprintf( '; color: #%02x%02x%02x', $red, $green, $blue );
			$item_values['item_link_style'] = $new_style;
			$item_values['currentlink'] = str_replace( $old_style, $new_style, $item_values['currentlink'] );
			$item_values['thelink'] = str_replace( $old_style, $new_style, $item_values['thelink'] );

			//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_item_values new $item_values = ' . var_export( $item_values, true ), 0 );
		}

		return $item_values;
	} // mla_archive_list_item_values

	/**
	 * MLA Archive List Item Template
	 *
	 * @since 1.00
	 *
	 * @param	string	template used to generate the HTML markup
	 *
	 * @return	string	updated template
	 */
	public static function mla_archive_list_item_template( $item_template ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_item_template $item_template = ' . var_export( $item_template, true ), 0 );

		return $item_template;
	} // mla_archive_list_item_template

	/**
	 * MLA Archive List Item Parse
	 *
	 * @since 1.00
	 *
	 * @param	string	HTML markup returned by the template parser
	 * @param	string	template used to generate the HTML markup
	 * @param	array	parameter_name => parameter_value pairs
	 *
	 * @return	array	updated HTML markup for gallery output
	 */
	public static function mla_archive_list_item_parse( $html_markup, $item_template, $item_values ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_item_parse $html_markup = ' . var_export( $html_markup, true ), 0 );
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_item_parse $item_template = ' . var_export( $item_template, true ), 0 );
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_item_parse $item_values = ' . var_export( $item_values, true ), 0 );

		return $html_markup;
	} // mla_archive_list_item_parse

	/**
	 * MLA Archive List Close Values
	 *
	 * @since 1.00
	 *
	 * @param	array	parameter_name => parameter_value pairs
	 *
	 * @return	array	updated substitution parameter name => value pairs
	 */
	public static function mla_archive_list_close_values( $markup_values ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_close_values $markup_values = ' . var_export( $markup_values, true ), 0 );

		return $markup_values;
	} // mla_archive_list_close_values

	/**
	 * MLA Archive List Close Template
	 *
	 * @since 1.00
	 *
	 * @param	string	template used to generate the HTML markup
	 *
	 * @return	string	updated template
	 */
	public static function mla_archive_list_close_template( $close_template ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_close_template $close_template = ' . var_export( $close_template, true ), 0 );

		return $close_template;
	} // mla_archive_list_close_template

	/**
	 * MLA Archive List Close Parse
	 *
	 * @since 1.00
	 *
	 * @param	string	HTML markup returned by the template parser
	 * @param	string	template used to generate the HTML markup
	 * @param	array	parameter_name => parameter_value pairs
	 *
	 * @return	array	updated HTML markup for gallery output
	 */
	public static function mla_archive_list_close_parse( $html_markup, $close_template, $markup_values ) {
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_close_parse $html_markup = ' . var_export( $html_markup, true ), 0 );
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_close_parse $close_template = ' . var_export( $close_template, true ), 0 );
		//error_log( __LINE__ . ' MLAArchiveListHooksExample::mla_archive_list_close_parse $markup_values = ' . var_export( $markup_values, true ), 0 );

		return $html_markup;
	} // mla_archive_list_close_parse
} // Class MLAArchiveListHooksExample

// Install the filters at an early opportunity
add_action('init', 'MLAArchiveListHooksExample::initialize');
?>