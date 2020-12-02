<?php
/**
 * Provides an example of hooking the filters provided by the MLA_List_Table class
 *
 * In this example:
 *
 * 	1. an Advanced Custom Fields "checkbox" custom field is added to the
 * 	   Media/Assistant submenu table, Quick Edit and Bulk Edit areas.
 *
 * 	2. an Advanced Custom Fields "repeater" custom field is analyzed to display
 * 	   "where used" information in the Media/Assistant submenu table.
 *
 * 	3. Advanced Custom Fields "image" custom field(s) are analyzed to display
 * 	   "where used" information in the Media/Assistant submenu table.
 *
 * 	4. Advanced Custom Fields "image" custom field(s) are made available as custom data substitution
 *     parameters, using the prefix "acf:", e.g., "acf:search_bar_image". Three format/option values
 *     can be added: 1) "acf:search_bar_image(count)" returns the number of item references,
 *     2) "acf:search_bar_image(present)" returns 1 if there are references present, and 3) a numeric
 *     value, e.g., "acf:search_bar_image(3)" returns the count only if it is equal to or greater than
 *     the number of references.
 *
 * You can turn each of the three field types on or off by setting the corresponding "_ENABLE" constant
 * to true or false. You can change the field names and labels by editing the corresponding constants.
 *
 * Created for support topic "Advanced Custom Fields repeater"
 * opened on 3/1/2015 by "ncj"
 * https://wordpress.org/support/topic/advanced-custom-fields-repeater/
 *
 * Enhanced for support topic "finding “where used” in custom field"
 * opened on 4/19/2020 by "maven1129"
 * https://wordpress.org/support/topic/finding-where-used-in-custom-field/
 *
 * @package MLA Advanced Custom Fields Example
 * @version 1.05
 */

/*
Plugin Name: MLA Advanced Custom Fields Example
Plugin URI: http://davidlingren.com/
Description: Supports an ACF checkbox, "where-used" in an ACF repeater and one or more ACF "image" variables.
Author: David Lingren
Version: 1.05
Author URI: http://davidlingren.com/

Copyright 2014 - 2020 David Lingren

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
 * Class MLA Advanced Custom Fields Example hooks some of the filters provided by the MLA_List_Table and MLAData classes
 *
 * Call it anything you want, but give it an unlikely and hopefully unique name. Hiding everything
 * else inside a class means this is the only name you have to worry about.
 *
 * @package MLA Advanced Custom Fields Example
 * @since 1.00
 */
class MLAACFExample {
	/**
	 * True to enable the checkbox field, false to disable
	 *
	 * @since 1.04
	 *
	 * @var	string
	 */
	const ACF_CHECKBOX_ENABLED = true;

	/**
	 * Field name of the checkbox field
	 *
	 * @since 1.02
	 *
	 * @var	string
	 */
	const ACF_CHECKBOX_FIELD = 'acf_checkbox';

	/**
	 * Field Label of the checkbox field
	 *
	 * @since 1.02
	 *
	 * @var	string
	 */
	const ACF_CHECKBOX_TITLE = 'ACF Checkbox';

	/**
	 * True to enable the repeater field, false to disable
	 *
	 * @since 1.04
	 *
	 * @var	string
	 */
	const ACF_REPEATER_ENABLED = true;

	/**
	 * Field name of the "parent" repeater field
	 *
	 * @since 1.02
	 *
	 * @var	string
	 */
	const ACF_REPEATER_FIELD = 'photos_alt';

	/**
	 * Field Label of the "parent" repeater field
	 *
	 * @since 1.02
	 *
	 * @var	string
	 */
	const ACF_REPEATER_TITLE = 'Photos Alt';

	/**
	 * Field name of the "child" image sub field
	 *
	 * @since 1.02
	 *
	 * @var	string
	 */
	const ACF_SUB_FIELD = 'photo';

	/**
	 * True to enable the image field(s), false to disable
	 *
	 * @since 1.04
	 *
	 * @var	string
	 */
	const ACF_IMAGE_ENABLED = true;

	/**
	 * Field name(s) of the image field(s)
	 *
	 * @since 1.04
	 *
	 * @var	string
	 */
	const ACF_IMAGE_FIELDS = 'search_bar_image,rates_search_bar_image';

	/**
	 * Field Label(s) of the image field(s)
	 *
	 * @since 1.04
	 *
	 * @var	string
	 */
	const ACF_IMAGE_TITLES = 'Header Image,Header Image Alt';

	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		// The filters are only useful for the admin section; exit in the front-end posts/pages
		if ( ! is_admin() )
			return;

		// Defined in /media-library-assistant/includes/class-mla-main.php
		if ( self::ACF_CHECKBOX_ENABLED ) {
			add_filter( 'mla_list_table_inline_action', 'MLAACFExample::mla_list_table_inline_action', 10, 2 );
			add_filter( 'mla_list_table_bulk_action_initial_request', 'MLAACFExample::mla_list_table_bulk_action_initial_request', 10, 3 );
			add_filter( 'mla_list_table_bulk_action', 'MLAACFExample::mla_list_table_bulk_action', 10, 3 );
			add_filter( 'mla_list_table_inline_values', 'MLAACFExample::mla_list_table_inline_values', 10, 1 );
		}

		// Defined in /media-library-assistant/includes/class-mla-list-table.php
		add_filter( 'mla_list_table_get_columns', 'MLAACFExample::mla_list_table_get_columns', 10, 1 );
		add_filter( 'mla_list_table_column_default', 'MLAACFExample::mla_list_table_column_default', 10, 3 );

		if ( self::ACF_CHECKBOX_ENABLED ) {
			add_filter( 'mla_list_table_get_hidden_columns', 'MLAACFExample::mla_list_table_get_hidden_columns', 10, 1 );
			add_filter( 'mla_list_table_get_sortable_columns', 'MLAACFExample::mla_list_table_get_sortable_columns', 10, 1 );
			add_filter( 'mla_list_table_build_inline_data', 'MLAACFExample::mla_list_table_build_inline_data', 10, 2 );
		}

		// Defined in /media-library-assistant/includes/class-mla-data.php
		if ( self::ACF_IMAGE_ENABLED ) {
			add_filter( 'mla_expand_custom_prefix', 'MLAACFExample::mla_expand_custom_prefix', 10, 8 );
		}
	}

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
		if ( 'acf' !== strtolower( $value['prefix'] ) ) {
			return $custom_value;
		}
		
		//error_log( __LINE__ . " MLAACFExample::mla_expand_custom_prefix( {$key}, {$post_id}, {$keep_existing}, {$default_option} ) value = " . var_export( $value, true ), 0 );
		//error_log( __LINE__ . " MLAACFExample::mla_expand_custom_prefix( {$key}, {$post_id} ) query = " . var_export( $query, true ), 0 );
		//error_log( __LINE__ . " MLAACFExample::mla_expand_custom_prefix( {$key}, {$post_id} ) markup_values = " . var_export( $markup_values, true ), 0 );

		// Look for field/value qualifier
		$match_count = preg_match( '/^(.+)\((.+)\)/', $value['value'], $matches );
		if ( $match_count ) {
			$field = $matches[1];
			$qualifier = $matches[2];
		} else {
			$field = $value['value'];
			$qualifier = '';
		}
//error_log( __LINE__ . " MLAACFExample::mla_expand_custom_prefix( {$key}, {$post_id} ) field = {$field}, qualifier = {$qualifier}", 0 );

		// Set debug mode
		$debug_active = isset( $query['mla_debug'] ) && ( 'false' !== trim( strtolower( $query['mla_debug'] ) ) );
		if ( $debug_active ) {
			$old_mode = MLACore::mla_debug_mode( 'log' );
			MLACore::mla_debug_add( __LINE__ . " MLAACFExample::mla_expand_custom_prefix( {$key}, {$post_id}, {$keep_existing}, {$default_option} ) \$_REQUEST = " . var_export( $_REQUEST, true ) );
			MLACore::mla_debug_add( __LINE__ . " MLAACFExample::mla_expand_custom_prefix( {$field}, {$qualifier} ) \$value = " . var_export( $value, true ) );
			MLACore::mla_debug_add( __LINE__ . " MLAACFExample::mla_expand_custom_prefix() \$query = " . var_export( $query, true ) );
			MLACore::mla_debug_add( __LINE__ . " MLAACFExample::mla_expand_custom_prefix() \$markup_values = " . var_export( $markup_values, true ) );
		}

		$posts = self::_find_field_references( $field, $post_id );
		//error_log( __LINE__ . " MLAACFExample::mla_expand_custom_prefix( {$key}, {$post_id} ) posts = " . var_export( $posts, true ), 0 );
		if (  !empty( $posts ) ) {
			switch ( $qualifier ) {
				case 'count':
					$custom_value = count( $posts );
					break;
				case 'present':
					$custom_value = ( count( $posts ) ) ? 1 : 0;
					break;
				default:
					$low_bound = absint( $qualifier );
					if ( $low_bound ) {
						$custom_value = count( $posts );
						if ( $low_bound > $custom_value ) {
							$custom_value = NULL;
						}

						break;
					}
					
					$custom_value = '';
					$item = get_post( $post_id );
		
					foreach ( $posts as $post_id => $post ) {
						$reference = self::$field_parents[ $post ];
						$status = self::_format_post_status( $reference->post_status );
		
						if ( $post_id == $item->post_parent ) {
							$parent = ', ' . __( 'PARENT', 'media-library-assistant' );
						} else {
							$parent = '';
						}
		
						$custom_value .= sprintf( '%1$s (%2$s %3$s%4$s%5$s), ',
							/*%1$s*/ esc_attr( $reference->post_title ),
							/*%2$s*/ esc_attr( $reference->post_type ),
							/*%3$s*/ $post_id,
							/*%4$s*/ $status,
							/*%5$s*/ $parent );
					} // foreach $reference
			}
		}
		
//error_log( __LINE__ . " MLAACFExample::mla_expand_custom_prefix( {$key}, {$post_id} ) custom_value = " . var_export( $custom_value, true ), 0 );
		return $custom_value;
	} // mla_expand_custom_prefix

	/**
	 * Process an MLA_List_Table inline action, i.e., Quick Edit 
	 *
	 * This filter gives you an opportunity to pre-process an MLA_List_Table "Quick Edit"
	 * action before the MLA handler.
	 *
	 * @since 1.00
	 *
	 * @param	array	$item_content	NULL, to indicate no handler.
	 * @param	integer	$post_id		the affected attachment.
	 *
	 * @return	object	updated $item_content. NULL if no handler, otherwise
	 *					( 'message' => error or status message(s), 'body' => '',
	 *					  'prevent_default' => true to bypass the MLA handler )
	 */
	public static function mla_list_table_inline_action( $item_content, $post_id ) {
		// Convert the comma-delimited string of "checked" checkbox values back to an ACF-compatible array
		if ( isset( $_REQUEST['custom_updates'] ) && isset( $_REQUEST['custom_updates'][ self::ACF_CHECKBOX_FIELD ] ) ) {
			if ( ! empty( $_REQUEST['custom_updates'][ self::ACF_CHECKBOX_FIELD ] ) ) {
				$_REQUEST['custom_updates'][ self::ACF_CHECKBOX_FIELD ] = explode( ',', $_REQUEST['custom_updates'][ self::ACF_CHECKBOX_FIELD ] );
			}
		}

		return $item_content;
	} // mla_list_table_inline_action

	/**
	 * Pre-filter MLA_List_Table bulk action request parameters
	 *
	 * This filter gives you an opportunity to pre-process the request parameters for a bulk action
	 * before the action begins. DO NOT assume parameters come from the $_REQUEST super array!
	 *
	 * @since 1.01
	 *
	 * @param	array	$request		bulk action request parameters, including ['mla_bulk_action_do_cleanup'].
	 * @param	string	$bulk_action	the requested action.
	 * @param	array	$custom_field_map	[ slug => field_name ]
	 *
	 * @return	array	updated bulk action request parameters
	 */
	public static function mla_list_table_bulk_action_initial_request( $request, $bulk_action, $custom_field_map ) {
		/*
		 * If the field is present, save the field value for our own update process and remove it
		 * from the $request array to prevent MLA's default update processing.
		 */
		if ( false !== $slug = array_search( self::ACF_CHECKBOX_FIELD, $custom_field_map ) ) {
			if ( ! empty( $request[ $slug ] ) ) {
				self::$acf_checkbox_value = trim( $request[ $slug ] );
				$request[ $slug ] = '';
			}
		}

		return $request;
	} // mla_list_table_bulk_action_initial_request

	/**
	 * Holds the new ACF checkbox value for the duration of a Bulk Edit action
	 *
	 * @since 1.01
	 *
	 * @var	string
	 */
	private static $acf_checkbox_value = NULL;

	/**
	 * Process an MLA_List_Table bulk action
	 *
	 * This filter gives you an opportunity to pre-process an MLA_List_Table page-level
	 * or single-item action, standard or custom, before the MLA handler.
	 * The filter is called once for each of the items in $_REQUEST['cb_attachment'].
	 *
	 * @since 1.00
	 *
	 * @param	array	$item_content	NULL, to indicate no handler.
	 * @param	string	$bulk_action	the requested action.
	 * @param	integer	$post_id		the affected attachment.
	 *
	 * @return	object	updated $item_content. NULL if no handler, otherwise
	 *					( 'message' => error or status message(s), 'body' => '',
	 *					  'prevent_default' => true to bypass the MLA handler )
	 */
	public static function mla_list_table_bulk_action( $item_content, $bulk_action, $post_id ) {
		/*
		 * If the field is present, apply our own update process. Note the
		 * special 'empty' value to bulk-delete the custom field entirely.
		 */
		if ( ! empty( self::$acf_checkbox_value ) ) {
			if ( 'empty' == self::$acf_checkbox_value ) {
				delete_post_meta( $post_id, self::ACF_CHECKBOX_FIELD );
				$item_content = array( 'message' => sprintf( __( 'Deleting %1$s', 'media-library-assistant' ) . '<br>', self::ACF_CHECKBOX_FIELD ) );
			} else {
				update_post_meta( $post_id, self::ACF_CHECKBOX_FIELD, explode( ',', self::$acf_checkbox_value ) );
				$item_content = array( 'message' => sprintf( __( 'Adding %1$s = %2$s', 'media-library-assistant' ) . '<br>', self::ACF_CHECKBOX_FIELD, self::$acf_checkbox_value ) );
			}
		}

		return $item_content;
	} // mla_list_table_bulk_action

	/**
	 * MLA_List_Table inline edit item values
	 *
	 * This filter gives you a chance to modify and extend the substitution values
	 * for the Quick and Bulk Edit forms.
	 *
	 * @since 1.00
	 *
	 * @param	array	$item_values parameter_name => parameter_value pairs
	 *
	 * @return	array	updated substitution parameter name => value pairs
	 */
	public static function mla_list_table_inline_values( $item_values ) {
		// Replace the ACF Field Name with a more friendly Field Label
		$item_values['custom_fields'] = str_replace( '>acf_checkbox<', '>' . self::ACF_CHECKBOX_TITLE . '<', $item_values['custom_fields'] );
		$item_values['bulk_custom_fields'] = str_replace( '>acf_checkbox<', '>' . self::ACF_CHECKBOX_TITLE . '<', $item_values['bulk_custom_fields'] );

		return $item_values;
	} // mla_list_table_inline_values

	/**
	 * Holds the ISC custom field name to column "slug" mapping values
	 *
	 * @since 1.01
	 *
	 * @var	array
	 */
	private static $field_slugs = array();

	/**
	 * Filter the MLA_List_Table columns
	 *
	 * This MLA-specific filter gives you an opportunity to filter the list table columns.
	 *
	 * @since 1.00
	 *
	 * @param	array	$columns An array of columns.
	 *					format: column_slug => Column Label
	 *
	 * @return	array	updated array of columns.
	 */
	public static function mla_list_table_get_columns( $columns ) {
		if ( self::ACF_CHECKBOX_ENABLED ) {
			/*
			 * The Quick and Bulk Edit forms substitute arbitrary "slugs" for the
			 * custom field names. Remember them for table column and bulk update processing.
			 */
			if ( false !== $slug = array_search( self::ACF_CHECKBOX_FIELD, $columns ) ) {
				self::$field_slugs[ self::ACF_CHECKBOX_FIELD ] = $slug;

				/*
				 * Change the column slug so we can provide our own friendly content.
				 * Replace the entry for the column we're capturing, preserving its place in the list
				 */
				$new_columns = array();

				foreach ( $columns as $key => $value ) {
					if ( $key == $slug ) {
						$new_columns[ self::ACF_CHECKBOX_FIELD ] = self::ACF_CHECKBOX_TITLE;
					} else {
						$new_columns[ $key ] = $value;
					}
				} // foreach column

				$columns = $new_columns;
			}
		}

		// Add a column of our own for the repeater "where-used" information
		if ( self::ACF_REPEATER_ENABLED ) {
			$columns[ 'acf_' . self::ACF_REPEATER_FIELD ] = self::ACF_REPEATER_TITLE;
		}

		// Add columns of our own for the image "where-used" information
		if ( self::ACF_IMAGE_ENABLED ) {
			$image_fields = explode( ',', self::ACF_IMAGE_FIELDS );
			$image_titles = explode( ',', self::ACF_IMAGE_TITLES );

			if ( count( $image_fields ) === count( $image_titles ) ) {
				foreach( $image_fields as $index => $field_name ) {
					self::$field_slugs[ 'acf_' . $field_name ] = $field_name;
					$columns[ 'acf_' . $field_name ] = $image_titles[ $index ];
				}
			}
		}

		return $columns;
	} // mla_list_table_get_columns

	/**
	 * Filter the MLA_List_Table hidden columns
	 *
	 * This MLA-specific filter gives you an opportunity to filter the hidden list table columns.
	 *
	 * @since 1.00
	 *
	 * @param	array	$hidden_columns An array of columns.
	 *					format: index => column_slug
	 *
	 * @return	array	updated array of columns.
	 */
	public static function mla_list_table_get_hidden_columns( $hidden_columns ) {
		// Replace the MLA custom field slug with our own slug value
		if ( isset( self::$field_slugs[ self::ACF_CHECKBOX_FIELD ] ) ) {
			$index = array_search( self::$field_slugs[ self::ACF_CHECKBOX_FIELD ], $hidden_columns );
			if ( false !== $index ) {
				$hidden_columns[ $index ] = self::ACF_CHECKBOX_FIELD;
			}
		}

		return $hidden_columns;
	} // mla_list_table_get_hidden_columns

	/**
	 * Filter the MLA_List_Table sortable columns
	 *
	 * This MLA-specific filter gives you an opportunity to filter the sortable list table
	 * columns; a good alternative to the 'manage_media_page_mla_menu_sortable_columns' filter.
	 *
	 * @since 1.00
	 *
	 * @param	array	$sortable_columns	An array of columns.
	 *										Format: 'column_slug' => 'orderby'
	 *										or 'column_slug' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending.
	 *
	 * @return	array	updated array of columns.
	 */
	public static function mla_list_table_get_sortable_columns( $sortable_columns ) {
		// Replace the slug for the column we've captured, preserving its place in the list
		if ( isset( self::$field_slugs[ self::ACF_CHECKBOX_FIELD ] ) ) {
			$slug = self::$field_slugs[ self::ACF_CHECKBOX_FIELD ];
			if ( isset( $sortable_columns[ $slug ] ) ) {
				$new_columns = array();

				foreach ( $sortable_columns as $key => $value ) {
					if ( $key == $slug ) {
						$new_columns[ self::ACF_CHECKBOX_FIELD ] = $value;
					} else {
						$new_columns[ $key ] = $value;
					}
				} // foreach column

				$sortable_columns = $new_columns;
			} // slug found
		} // slug exists

		return $sortable_columns;
	} // mla_list_table_get_sortable_columns

	/**
	 * Translate post_status 'future', 'pending', 'draft' and 'trash' to label
	 *
	 * @since 1.02
	 * 
	 * @param	string	post_status
	 *
	 * @return	string	Status label or empty string
	 */
	protected static function _format_post_status( $post_status ) {
		$flag = ',<br>';
		switch ( $post_status ) {
			case 'draft' :
				$flag .= __('Draft');
				break;
			case 'future' :
				$flag .= __('Scheduled');
				break;
			case 'pending' :
				$flag .= _x('Pending', 'post state');
				break;
			case 'trash' :
				$flag .= __('Trash');
				break;
			default:
				$flag = '';
		}

	return $flag;
	}

	/**
	 * Supply a column value if no column-specific function has been defined
	 *
	 * Called when the MLA_List_Table can't find a value for a given column.
	 *
	 * @since 1.00
	 *
	 * @param	string	NULL, indicating no default content
	 * @param	array	A singular item (one full row's worth of data)
	 * @param	array	The name/slug of the column to be processed
	 * @return	string	Text or HTML to be placed inside the column
	 */
	public static function mla_list_table_column_default( $content, $item, $column_name ) {
		// Convert the ACF-compatible array to a comma-delimited list of "checked" checkbox values.
		if ( self::ACF_CHECKBOX_FIELD == $column_name ) {
			$values = isset( $item->mla_item_acf_checkbox ) ? $item->mla_item_acf_checkbox : '';
			if ( empty( $values ) ) {
				return '';
			} elseif ( is_array( $values ) ) {
				return '[' . implode( '],[', $values ) . ']';
			}

			return $values;
		}

		// Retrieve and format the repeater field "where-used" information
		if ( ( 'acf_' . self::ACF_REPEATER_FIELD ) == $column_name ) {
			global $wpdb;

			$where_clause = self::ACF_REPEATER_FIELD . '_%_' . self::ACF_SUB_FIELD;
			$references = $wpdb->get_results( 
				"
				SELECT *
				FROM {$wpdb->postmeta}
				WHERE meta_key LIKE '{$where_clause}' AND meta_value = {$item->ID}
				"
			);

			$content = '';
			if ( ! empty( $references ) ) {
				$parents = array();
				foreach ( $references as $reference ) {
					// key on post_id to remove duplicates
					$parents[ $reference->post_id ] = $reference->post_id;
				}

				$parents = implode( ',', $parents );
				$references = $wpdb->get_results(
					"
					SELECT ID, post_type, post_status, post_title
					FROM {$wpdb->posts}
					WHERE ( post_type <> 'revision' ) AND ( ID IN ({$parents}) )
					"
				);

				foreach ( $references as $reference ) {
					$status = self::_format_post_status( $reference->post_status );

					if ( $reference->ID == $item->post_parent ) {
						$parent = ',<br>' . __( 'PARENT', 'media-library-assistant' );
					} else {
						$parent = '';
					}

					$content .= sprintf( '<a href="%1$s" title="' . __( 'Edit', 'media-library-assistant' ) . ' &#8220;%2$s&#8221;">%2$s</a> (%3$s %4$s%5$s%6$s), ',
						/*%1$s*/ esc_url( add_query_arg( array('post' => $reference->ID, 'action' => 'edit'), 'post.php' ) ),
						/*%2$s*/ esc_attr( $reference->post_title ),
						/*%3$s*/ esc_attr( $reference->post_type ),
						/*%4$s*/ $reference->ID,
						/*%5$s*/ $status,
						/*%6$s*/ $parent ) . "<br>\r\n";
				} // foreach $reference
			} // found $references

			return $content;
		} // found repeater column


		// Retrieve and format the image field(s) "where-used" information
		if ( array_key_exists( $column_name, self::$field_slugs ) ) {
			$content = '';

			$posts = self::_find_field_references( self::$field_slugs[ $column_name ], $item->ID );
			foreach ( $posts as $post_id => $post ) {
				$reference = self::$field_parents[ $post ];
				$status = self::_format_post_status( $reference->post_status );

				if ( $post_id == $item->post_parent ) {
					$parent = ',<br>' . __( 'PARENT', 'media-library-assistant' );
				} else {
					$parent = '';
				}

				$content .= sprintf( '<a href="%1$s" title="' . __( 'Edit', 'media-library-assistant' ) . ' &#8220;%2$s&#8221;">%2$s</a> (%3$s %4$s%5$s%6$s), ',
					/*%1$s*/ esc_url( add_query_arg( array('post' => $post_id, 'action' => 'edit'), 'post.php' ) ),
					/*%2$s*/ esc_attr( $reference->post_title ),
					/*%3$s*/ esc_attr( $reference->post_type ),
					/*%4$s*/ $post_id,
					/*%5$s*/ $status,
					/*%6$s*/ $parent ) . "<br>\r\n";
			} // foreach $reference

			return $content;
		} // found image column

		return $content;
	} // mla_list_table_column_default

	/**
	 * Filter the data for inline (Quick and Bulk) editing
	 *
	 * This filter gives you an opportunity to filter the data passed to the
	 * JavaScript functions for Quick and Bulk editing.
	 *
	 * @since 1.00
	 *
	 * @param	string	$inline_data	The HTML markup for inline data.
	 * @param	object	$item			The current Media Library item.
	 *
	 * @return	string	updated HTML markup for inline data.
	 */
	public static function mla_list_table_build_inline_data( $inline_data, $item ) {
		// See if the field is present
		if ( ! isset( self::$field_slugs[ self::ACF_CHECKBOX_FIELD ] ) ) {
			return $inline_data;
		}

		// Convert the ACF-compatible array to a comma-delimited list of "checked" checkbox values.
		$match_count = preg_match_all( '/\<div class="' . self::$field_slugs[ self::ACF_CHECKBOX_FIELD ] . '"\>(.*)\<\/div\>/', $inline_data, $matches, PREG_OFFSET_CAPTURE );
		if ( ( $match_count == false ) || ( $match_count == 0 ) ) {
			return $inline_data;
		}

		if ( isset( $item->mla_item_acf_checkbox ) ) {
			$value = $item->mla_item_acf_checkbox;
			if ( is_array( $value ) ) {
				$head = substr( $inline_data, 0, $matches[1][0][1] );
				$value = esc_html( implode( ',', $value ) );
				$tail = substr( $inline_data, ( $matches[1][0][1] + strlen( $matches[1][0][0] ) ) );
				$inline_data = $head . $value . $tail;
			}
		}

		return $inline_data;
	} // mla_list_table_build_inline_data

	/**
	 * Cached values for the ACF Image fields
	 *
	 * @since 1.05
	 *
	 * @var	array( field_slug => array( item_ID => array( reference_ID => reference_ID ) ) )
	 */
	private static $field_instances = array();

	/**
	 * Cached "parent post" values for the ACF Image fields
	 *
	 * @since 1.05
	 *
	 * @var	array( post_ID => array( post_type, post_status, post_title ) )
	 */
	private static $field_parents = array();

	/**
	 * Filter the data for inline (Quick and Bulk) editing
	 *
	 * This filter gives you an opportunity to filter the data passed to the
	 * JavaScript functions for Quick and Bulk editing.
	 *
	 * @since 1.00
	 *
	 * @param	string	$field_slug	ACF Image variable slug.
	 * @param	integer	$item_ID	Media Library item ID.
	 *
	 * @return	array	post_id values referencing the item.
	 */
	private static function _find_field_references( $field_slug, $item_ID ) {
		global $wpdb;

		if ( !isset( self::$field_instances[ $field_slug ] ) ) {
			$references = $wpdb->get_results( 
				"
				SELECT post_id, meta_value
				FROM {$wpdb->postmeta}
				WHERE meta_key LIKE '{$field_slug}'
				"
			);

			$post_ids = array();
			foreach ( $references as $reference ) {
				$post_id = (int) $reference->post_id;
				$meta_value = (int) $reference->meta_value;
				self::$field_instances[ $field_slug ][ $meta_value ][ $post_id ] = $post_id;
				$post_ids[ $post_id ] = $post_id;
			}

			// Find the post information, excluding revisions
			$parents = implode( ',', $post_ids );
			$references = $wpdb->get_results(
				"
				SELECT ID, post_type, post_status, post_title
				FROM {$wpdb->posts}
				WHERE ( post_type <> 'revision' ) AND ( ID IN ({$parents}) )
				"
			);

			foreach ( $references as $reference ) {
				$post_id = (int) $reference->ID;
				unset( $reference->ID );
				self::$field_parents[ $post_id ] = $reference;
			}
//error_log( __LINE__ . " MLAACFExample::_find_field_references( {$field_slug} ) field_parents = " . var_export( self::$field_parents, true ), 0 );

			// Remove the revisions from the field references
			foreach ( self::$field_instances[ $field_slug ] as $meta_value => $post_ids ) {
				$references = array();
				foreach ( $post_ids as $post_id ) {
					if ( array_key_exists(  $post_id, self::$field_parents ) ) {
						$references[ $post_id ] = $post_id;
					}
				}

				if ( count( $references ) ) {
					self::$field_instances[ $field_slug ][ $meta_value ] = $references;
				} else {
					unset( self::$field_instances[ $field_slug ][ $meta_value ] );
				}
			}
//error_log( __LINE__ . " MLAACFExample::_find_field_references( {$field_slug} ) field_instances = " . var_export( self::$field_instances, true ), 0 );
		} // !isset( self::$field_instances[ $field_slug ] )
		
		if ( isset( self::$field_instances[ $field_slug ][ $item_ID ] ) ) {
			return self::$field_instances[ $field_slug ][ $item_ID ];
		}

		return array();
	} // _find_field_references
} // Class MLAACFExample

// Install the filters at an early opportunity
add_action('init', 'MLAACFExample::initialize');
?>