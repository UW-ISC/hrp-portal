<?php
/**
 * Media Library Assistant Term List Shortcode
 *
 * @package Media Library Assistant
 * @since 3.13
 */

/**
 * Class MLA (Media Library Assistant) Custom Field List Shortcode implements
 * the [mla_custom_list] shortcode.
 *
 * @package Media Library Assistant
 * @since 3.13
 */
class MLACustomList {
	/**
	 * Turn debug collection and display on or off
	 *
	 * @since 3.13
	 *
	 * @var	boolean
	 */
	private static $mla_debug = false;

	/**
	 * These are the default parameters for custom field list display
	 *
	 * @since 3.13
	 *
	 * @var	array
	 */
	private static $item_specific_arguments = array(
			'mla_link_attributes' => '',
			'mla_link_class' => '',
			'mla_link_style' => '',
			'mla_link_href' => '',
			'mla_link_text' => '',
			'mla_rollover_text' => '',
			'mla_caption' => '',
			'mla_item_value' => '',

			'mla_control_name' => '',
			'mla_option_text' => '',
			'mla_option_value' => '',
		);

	/**
	 * Data selection parameters for [mla_tag_cloud], [mla_term_list]
	 *
	 * @since 3.13
	 *
	 * @var	array
	 */
	private static $mla_get_custom_values_parameters = array(
		'meta_key' => '',
		'post_mime_type' => 'image',
		'post_type' => 'attachment',
		'post_status' => 'inherit',
		'ids' => array(),
		'fields' => 'm.meta_value',
		'no_count' => false,
		'include' => '',
		'exclude' => '',
		'minimum' => 0,
		'number' => 0,
		'orderby' => 'meta_value',
		'order' => 'ASC',
		'no_orderby' => false,
		'preserve_case' => false,
		'limit' => 0,
		'offset' => 0
	);

	/**
	 * Valid mla_output values
	 *
	 * @since 3.13
	 *
	 * @var	array
	 */
	private static $valid_mla_output_values = array( 'flat', 'ulist', 'olist', 'dlist', 'grid', 'dropdown', 'checklist', 'array' );

	/**
	 * Valid mla_output pagination values
	 *
	 * @since 3.13
	 *
	 * @var	array
	 */
	private static $valid_mla_output_pagination_values = array( 'previous_link', 'current_link', 'next_link', 'next_page', 'previous_page', 'paginate_links' );

	/**
	 * Data selection parameters for mla_get_all_none_value_counts()
	 *
	 * @since 3.13
	 *
	 * @var	array
	 */
	private static $mla_get_all_none_value_counts = array(
		'meta_key' => '',
		'post_mime_type' => 'image',
		'post_type' => 'attachment',
		'post_status' => 'inherit',
	);

	/**
	 * Retrieve the "ignore.", "no.", and "any." "values.assigned" counts for one custom field
	 *
	 * meta_key - string containing a custom field name.
	 *
	 * post_mime_type - MIME type(s) of the items to include in the term-specific counts. Default 'all'.
	 *
	 * post_type - The post type(s) of the items to include in the term-specific counts.
	 * The default is "attachment". 
	 *
	 * post_status - The post status value(s) of the items to include in the term-specific counts.
	 * The default is "inherit".
	 *
	 * @since 3.13
	 *
	 * @param	array	custom field to search and query parameters
	 *
	 * @return	array	( 'ignore.values.assigned' => count, 'no.values.assigned' => count, 'any.values.assigned' => count )
	 */
	public static function mla_get_all_none_value_counts( $attr ) {
		global $wpdb;

		// Make sure $attr is an array, even if it's empty
		if ( empty( $attr ) ) {
			$attr = array();
		} elseif ( is_string( $attr ) ) {
			$attr = shortcode_parse_atts( $attr );
		}

		// Merge input arguments with defaults
		$attr = apply_filters( 'mla_get_custom_values_query_attributes', $attr );
		$arguments = shortcode_atts( self::$mla_get_all_none_value_counts, $attr );
		$arguments = apply_filters( 'mla_get_custom_values_query_arguments', $arguments );

		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug attributes', 'media-library-assistant' ) . '</strong> = ' . var_export( $attr, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug arguments', 'media-library-assistant' ) . '</strong> = ' . var_export( $arguments, true ) );
		}

		$clause = array( 'SELECT' );
		$clause[] = 'COUNT( p.ID) as `ignore.values.assigned`, COUNT( sq.post_id ) as `any.values.assigned`';
		$clause[] = 'FROM ' . $wpdb->posts . ' AS p';
		$clause[] = 'LEFT JOIN ( ';
		$clause[] = 'SELECT DISTINCT pm.post_id FROM ' . $wpdb->postmeta . ' as pm';

		$placeholders = array( '%s' );
		$clause_parameters = array( $arguments['meta_key'] );
		$clause[] = $wpdb->prepare( 'WHERE pm.meta_key = \'' . join( ',', $placeholders ) . '\'', $clause_parameters ); // phpcs:ignore

//		$clause[] = 'WHERE pm.meta_key = \'' . $arguments['meta_key'] . '\'';
		$clause[] = ') AS sq';
		$clause[] = 'ON p.ID = sq.post_id';
		$clause[] = 'WHERE 1=1';

		// Add type and status constraints
		if ( is_array( $arguments['post_type'] ) ) {
			$post_types = $arguments['post_type'];
		} else {
			$post_types = array( $arguments['post_type'] );
		}

		$placeholders = array();
		$clause_parameters = array();
		foreach ( $post_types as $post_type ) {
			$placeholders[] = '%s';
			$clause_parameters[] = $post_type;
		}

		$clause[] = $wpdb->prepare( 'AND p.post_type IN ( ' . join( ',', $placeholders ) . ' )', $clause_parameters ); // phpcs:ignore

		if ( is_array( $arguments['post_status'] ) ) {
			$post_stati = $arguments['post_status'];
		} else {
			$post_stati = array( $arguments['post_status'] );
		}

		$placeholders = array();
		$clause_parameters = array();
		foreach ( $post_stati as $post_status ) {
			if ( ( 'private' != $post_status ) || is_user_logged_in() ) {
				$placeholders[] = '%s';
				$clause_parameters[] = $post_status;
			}
		}

		$clause[] = $wpdb->prepare( 'AND p.post_status IN ( ' . join( ',', $placeholders ) . ' )', $clause_parameters ); // phpcs:ignore

		// Add optional post_mime_type constraint
		if ( 'all' === strtolower( $arguments['post_mime_type'] ) ) {
			$post_mimes = '';
		} else {
			$post_mimes = wp_post_mime_type_where( $arguments['post_mime_type'], 'p' );
			$clause[] = str_replace( '%', '%%', $post_mimes );
		}

		$query =  join(' ', $clause);
		$results = $wpdb->get_results(	$query ); // phpcs:ignore

		if ( is_wp_error( $results ) ) {
			$results = array(
				'ignore.values.assigned' => 0,
				'no.values.assigned' => 0,
				'any.values.assigned' => 0,
				'wp_error_code' => $results->get_error_code(),
				'wp_error_message' => $results->get_error_message(),
				'wp_error_data' => $results->get_error_data( $results->get_error_code() ),
			);
		} elseif ( isset( $results[0] ) ) {
			$results = array_map( 'absint', (array) $results[0] );
			$results['no.values.assigned'] = $results['ignore.values.assigned'] - $results['any.values.assigned'];
		} else {
			$results = array( 'ignore.values.assigned' => 0, 'no.values.assigned' => 0, 'any.values.assigned' => 0 );
			$results['wpdb_last_error'] = $wpdb->last_error;
		}

		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug query', 'media-library-assistant' ) . '</strong> = ' . var_export( $query, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug results', 'media-library-assistant' ) . '</strong> = ' . var_export( $results, true ) );
		}

		return $results;
	} // mla_get_all_none_value_counts

	/**
	 * Compose item-specific markup values for a custom field list item
	 *
	 * Adds term-specific values to the array passed by reference.
	 *
	 * @since 3.13
	 *
	 * @param array $item_values Style and list-level substitution parameters, by reference
	 * @param string $item_template Item element of the markup template
	 * @param array $value Custom List object
	 * @param array $arguments Shortcode parameters, including defaults, by reference
	 * @param array $attr Shortcode parameters, explicit, by reference
	 *
	 * @return boolean True if the item is the "current_item"; updates &$markup_values
	 */
	private static function _compose_item_specific_values( &$item_values, $item_template, $value, &$arguments, &$attr ) {
//error_log( __LINE__ . ' _compose_item_specific_values value = ' . var_export( $value, true ), 0 );
//error_log( __LINE__ . ' _compose_item_specific_values attr = ' . var_export( $attr, true ), 0 );
//error_log( __LINE__ . ' _compose_item_specific_values arguments = ' . var_export( $arguments, true ), 0 );
		$column_index =  ( (integer) $item_values['index'] ) - 1;
		$mla_item_parameter = $arguments['mla_item_parameter'];
		$mla_page_parameter = $arguments['mla_page_parameter'];
		$mla_custom_list_current = (integer) $arguments[ $mla_page_parameter ];
		$is_list = in_array( $item_values['mla_output'], array( 'ulist', 'olist', 'dlist' ) );
		$is_dropdown = 'dropdown' === $item_values['mla_output'];
		$is_checklist = 'checklist' === $item_values['mla_output'];
		$is_current = false;

		if ( $item_values['columns'] > 0 && ( 1 + $column_index ) % $item_values['columns'] === 0 ) {
			$item_values['last_in_row'] = 'last_in_row';
		} else {
			$item_values['last_in_row'] = '';
		}

		$item_values['meta_value'] = wptexturize( $value->meta_value );
		$item_values['meta_text'] = wptexturize( $value->meta_value );
		$item_values['count'] = isset ( $value->count ) ? (integer) $value->count : 0; 
		$item_values['scaled_count'] = isset ( $value->scaled_count ) ? (integer) $value->scaled_count : 0; 

		if ( in_array( $value->meta_value, array( 'ignore.values.assigned', 'no.values.assigned', 'any.values.assigned' ) ) ) {
			$item_values['font_size'] = absint( $arguments['default_size'] );
		} else {
			$item_values['font_size'] = str_replace( ',', '.', ( $item_values['smallest'] + ( ( $item_values['scaled_count'] - $item_values['min_scaled_count'] ) * $item_values['font_step'] ) ) );
		}

		// Find delimiter for currentlink_url
		if ( strpos( $item_values['page_url'], '?' ) ) {
			$current_delimiter = '&';
		} else {
			$current_delimiter = '?';
		}

		// Add current item and current page to query arguments
		$query_arguments = $current_delimiter . $mla_item_parameter . '=' . esc_attr( $item_values['meta_value'] );
		if ( 1 !== $mla_custom_list_current ) {
			$query_arguments .= '&' . $mla_page_parameter . '=' . esc_attr( $mla_custom_list_current );
		}
		
		$item_values['currentlink_url'] = sprintf( '%1$s%2$s', $item_values['page_url'], $query_arguments );
		$item_values['valuetag_id'] = sanitize_title( $item_values['meta_value'] );
		// Added in the code below:
		$item_values['caption'] = '';
		$item_values['link_attributes'] = '';
		$item_values['current_item_class'] = '';
		$item_values['rollover_text'] = '';
		$item_values['link_style'] = '';
		$item_values['link_text'] = '';
		$item_values['currentlink'] = '';
		$item_values['thelink'] = '';

		if ( isset( $value->meta_text ) ) {
			$item_values['meta_text'] = wptexturize( $value->meta_text );
		}
			
		if ( ! empty( $arguments[ $mla_item_parameter ] ) ) {
			if ( is_array( $arguments[ $mla_item_parameter ] ) ) {
				foreach( $arguments[ $mla_item_parameter ] as $item ) {
					if ( sanitize_title_for_query( $value->meta_value ) === sanitize_title_for_query( $item ) ) {
						$is_current = true;
						$item_values['current_item_class'] = $arguments['current_item_class'];
						break;
					}
				}
			} else {
				// Must work for special values, e.g., any.values.assigned or -2
				if ( sanitize_title_for_query( $value->meta_value ) === sanitize_title_for_query( $arguments[ $mla_item_parameter ] ) ) {
					$is_current = true;
					$item_values['current_item_class'] = $arguments['current_item_class'];
				}
			}
		}

		// Add item_specific field-level substitution parameters  TODO
		$new_text = isset( $item_template ) ? $item_template : '';
		foreach( self::$item_specific_arguments as $index => $value ) {
			$new_text .= str_replace( '{+', '[+', str_replace( '+}', '+]', $arguments[ $index ] ) );
		}

		$item_values = MLAData::mla_expand_field_level_parameters( $new_text, $attr, $item_values );

		if ( $item_values['captiontag'] ) {
			if ( ! empty( $arguments['mla_caption'] ) ) {
				$item_values['caption'] = wptexturize( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_caption'], $item_values ) );
			}
		}

		// Apply the Display Content parameters.
		if ( ! empty( $arguments['mla_target'] ) ) {
			$link_attributes = 'target="' . esc_attr( $arguments['mla_target'] ) . '" ';
		} else {
			$link_attributes = '';
		}

		if ( ! empty( $arguments['mla_link_attributes'] ) ) {
			$link_attributes .= MLAShortcode_Support::mla_esc_attr( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_link_attributes'], $item_values ) ) . ' ';
		}

		if ( ! empty( $item_values['current_item_class'] ) ) {
			$class_attributes = $item_values['current_item_class'];
		} else {
			$class_attributes = '';
		}

		if ( ! empty( $arguments['mla_link_class'] ) ) {
			$class_attributes .= ' ' . esc_attr( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_link_class'], $item_values ) );
		}

		if ( ! empty( $class_attributes ) ) {
			$link_attributes .= ' class="' . trim( $class_attributes ) . '" ';
		}

		$item_values['link_attributes'] = $link_attributes;

		// Ignore option- all,any_terms,no_terms
		if ( -1 !== $item_values['count'] ) {
			$item_values['rollover_text'] = sprintf( _n( $item_values['single_text'], $item_values['multiple_text'], $item_values['count'], 'media-library-assistant' ), number_format_i18n( $item_values['count'] ) );

		}

		if ( ! empty( $arguments['mla_rollover_text'] ) ) {
			$item_values['rollover_text'] = esc_attr( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_rollover_text'], $item_values ) );
		}

		if ( ! empty( $arguments['mla_link_href'] ) ) {
			$link_href = esc_url( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_link_href'], $item_values ) );
			$item_values['link_url'] = $link_href;
		} else {
			$link_href = '';
		}

		if ( ! empty( $arguments['mla_link_style'] ) ) {
			$item_values['link_style'] = esc_attr( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_link_style'], $item_values ) );
		} else {
			$item_values['link_style'] = 'font-size: ' . $item_values['font_size'] . $item_values['unit'];
		}

		if ( ! empty( $arguments['mla_link_text'] ) ) {
			$item_values['link_text'] = wp_kses( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_link_text'], $item_values ), 'post' );
		} else {
			$item_values['link_text'] = $item_values['meta_text'];
		}

		if ( ! empty( $arguments['show_count'] ) && ( 'true' === strtolower( $arguments['show_count'] ) ) ) {
			// Ignore option- all,any_terms,no_terms
			if ( -1 !== $item_values['count'] ) {
				$item_values['link_text'] .= ' (' . $item_values['count'] . ')';
			}
		}

		if ( empty( $arguments['mla_item_value'] ) ) {
			$item_values['thevalue'] = esc_attr( $item_values['meta_value'] );
		} else {
			$item_values['thevalue'] = esc_attr( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_item_value'], $item_values ) );
		}

		// Currentlink and thelink
		$item_values['currentlink'] = sprintf( '<a %1$shref="%2$s" title="%3$s" style="%4$s">%5$s</a>', $link_attributes, $item_values['currentlink_url'], $item_values['rollover_text'], $item_values['link_style'], $item_values['link_text'] );
//error_log( __LINE__ . ' _compose_item_specific_values item_values = ' . var_export( $item_values, true ), 0 );

		if ( ! empty( $link_href ) ) {
			$item_values['thelink'] = sprintf( '<a %1$shref="%2$s" title="%3$s" style="%4$s">%5$s</a>', $link_attributes, $link_href, $item_values['rollover_text'], $item_values['link_style'], $item_values['link_text'] );
		} elseif ( 'current' === $arguments['link'] ) {
			$item_values['link_url'] = $item_values['currentlink_url'];
			$item_values['thelink'] = $item_values['currentlink'];
		} elseif ( 'span' === $arguments['link'] ) {
			$item_values['thelink'] = sprintf( '<span %1$sstyle="%2$s">%3$s</span>', $link_attributes, $item_values['link_style'], $item_values['link_text'] );
		} else {
			$item_values['thelink'] = $item_values['link_text'];
		}

		if ( $is_dropdown || $is_checklist ) {
			if ( empty( $arguments['mla_option_text'] ) ) {
				$item_values['thelabel'] = $item_values['link_text'];
			} else {
				$item_values['thelabel'] = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_option_text'], $item_values );
			}

			if ( empty( $arguments['mla_option_value'] ) ) {
				$item_values['thevalue'] = esc_attr( $item_values['meta_value'] );
			} else {
				$item_values['thevalue'] = esc_attr( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_option_value'], $item_values ) );
			}

			$item_values['popular'] = ''; // TODO Calculate 'custom-list-popular'

			if ( $item_values['current_item_class'] === $arguments['current_item_class'] ) {
				if ( $is_dropdown ) {
					$item_values['selected'] = 'selected=selected';
				} else {
					$item_values['selected'] = 'checked=checked';
				}
			} else {
				$item_values['selected'] = '';
			}
		}
//error_log( __LINE__ . ' _compose_item_specific_values item_values = ' . var_export( $item_values, true ), 0 );

		return $is_current;
	} // _compose_item_specific_values

	/**
	 * Compose pagination formats for a custom field list
	 *
	 * Adds shortcode output text and term-specific links to arrays passed by reference.
	 *
	 * @since 3.13
	 *
	 * @param string $list Shortcode output text, by reference
	 * @param array $links Term-specific links for flat/array output, by reference
	 * @param array $values Custom List objects, by reference
	 * @param array $markup_values Style and list-level substitution parameters, by reference
	 * @param array $arguments Shortcode parameters, including defaults, by reference
	 * @param array $attr Shortcode parameters, explicit, by reference
	 *
	 * @return boolean True if the list contains the "current_item"; appends to &$list, &$links
	 */
	private static function _compose_custom_pagination( &$list, &$links, &$values, &$markup_values, &$arguments, &$attr ) {
//error_log( __LINE__ . ' _compose_custom_pagination values = ' . var_export( $values, true ), 0 );
//error_log( __LINE__ . ' _compose_custom_pagination markup_values = ' . var_export( $markup_values, true ), 0 );
//error_log( __LINE__ . ' _compose_custom_pagination arguments = ' . var_export( $arguments, true ), 0 );
//error_log( __LINE__ . ' _compose_custom_pagination attr = ' . var_export( $attr, true ), 0 );

		$link_type = $arguments['mla_output'];

		// Handle 'previous_page', 'next_page', and 'paginate_links'
		if ( in_array( $link_type, array( 'previous_page', 'next_page', 'paginate_links' ) ) ) {
			if ( isset( $attr['limit'] ) ) {
				$arguments['posts_per_page'] = $attr['limit'];
				$arguments['numberposts'] = $attr['limit'];
			} else {
				$arguments['posts_per_page'] = 0;
				$arguments['numberposts'] = 0;
			}
	
			$output_parameters = array( $arguments['mla_output'], $arguments['mla_output_qualifier'] );
			$pagination_result = MLAShortcode_Support::mla_process_pagination_output_types( $output_parameters, $markup_values, $arguments, $attr, $markup_values['database_rows'] );
//error_log( __LINE__ . ' _compose_custom_pagination pagination_result = ' . var_export( $pagination_result, true ), 0 );
			if ( false !== $pagination_result ) {
				$list .= $pagination_result;
			}

			return false;
		}

		// For "previous_link", "current_link" and "next_link", discard all of the $tags except the appropriate choice
		if ( ! in_array( $link_type, array ( 'previous_link', 'current_link', 'next_link' ) ) ) {
			return ''; // unknown output type
		}

		$is_wrap = 'wrap' === $arguments['mla_output_qualifier'];
		if ( empty( $arguments['current_item'] ) ) {
			$target_index = -2; // won't match anything
		} else {
			$current_item = $arguments['current_item'];
//error_log( __LINE__ . ' _compose_custom_pagination current_item = ' . var_export( $current_item, true ), 0 );

			foreach ( $values as $index => $value ) {
				if ( $value->meta_value == $current_item ) {
					break;
				}
			}
//error_log( __LINE__ . ' _compose_custom_pagination index = ' . var_export( $index, true ), 0 );

			switch ( $link_type ) {
				case 'previous_link':
					$target_index = $index - 1;
					break;
				case 'next_link':
					$target_index = $index + 1;
					break;
				case 'current_link':
				default:
					$target_index = $index;
			} // link_type
		}
//error_log( __LINE__ . ' _compose_custom_pagination target_index = ' . var_export( $target_index, true ), 0 );

		$target = NULL;
		if ( isset( $values[ $target_index ] ) ) {
			$target = $values[ $target_index ];
		} elseif ( $is_wrap ) {
			switch ( $link_type ) {
				case 'previous_link':
					$target = array_pop( $values );
					break;
				case 'next_link':
					$target = array_shift( $values );
			} // link_type
		} // is_wrap
//error_log( __LINE__ . ' _compose_custom_pagination target = ' . var_export( $target, true ), 0 );

		if ( isset( $target ) ) {
			$values = array( $target );
		} elseif ( ! empty( $arguments['mla_nolink_text'] ) ) {
			$list .= wp_kses( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_nolink_text'], $markup_values ), 'post' );
			return false;
		}

		if ( !empty( $target ) ) {
			$item_values = $markup_values;
			$item_values['index'] = '1';
			$item_values['key'] = 0;
			$is_active = self::_compose_item_specific_values( $item_values, '', $target, $arguments, $attr );
			$item_values = apply_filters( 'mla_custom_list_item_values', $item_values );
			$list .= $item_values['thelink'];
		}
		
		return false;
	} // _compose_custom_pagination

	/**
	 * Compose a grid format custom field list
	 *
	 * Adds shortcode output text and term-specific links to arrays passed by reference.
	 *
	 * @since 3.13
	 *
	 * @param string $list Shortcode output text, by reference
	 * @param array $links Term-specific links for flat/array output, by reference
	 * @param array $values Custom List objects, by reference
	 * @param array $markup_values Style and list-level substitution parameters, by reference
	 * @param array $arguments Shortcode parameters, including defaults, by reference
	 * @param array $attr Shortcode parameters, explicit, by reference
	 *
	 * @return boolean True if the list contains the "current_item"; appends to &$list, &$links
	 */
	private static function _compose_custom_grid( &$list, &$links, &$values, &$markup_values, &$arguments, &$attr ) {
//error_log( __LINE__ . ' _compose_custom_grid values = ' . var_export( $values, true ), 0 );
//error_log( __LINE__ . ' _compose_custom_grid markup_values = ' . var_export( $markup_values, true ), 0 );
//error_log( __LINE__ . ' _compose_custom_grid arguments = ' . var_export( $arguments, true ), 0 );

		$mla_item_parameter = $arguments['mla_item_parameter'];
		$default_markup = 'custom-list-grid';

		$open_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'custom-list', 'markup', 'open' );
		if ( false === $open_template ) {
			$markup_values['mla_markup'] = $default_markup;
			$open_template = MLATemplate_support::mla_fetch_custom_template( $default_markup, 'custom-list', 'markup', 'open' );
		}

		if ( empty( $open_template ) ) {
			$open_template = '';
		}

		$row_open_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'custom-list', 'markup', 'row-open' );
		if ( empty( $row_open_template ) ) {
			$row_open_template = '';
		}

		$item_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'custom-list', 'markup', 'item' );
		if ( empty( $item_template ) ) {
			$item_template = '';
		}

		$row_close_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'custom-list', 'markup', 'row-close' );
		if ( empty( $row_close_template ) ) {
			$row_close_template = '';
		}

		$close_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'custom-list', 'markup', 'close' );
		if ( empty( $close_template ) ) {
			$close_template = '';
		}

		// Look for gallery-level markup substitution parameters
		$new_text = $open_template . $row_open_template . $row_close_template . $close_template;
		$markup_values = MLAData::mla_expand_field_level_parameters( $new_text, $attr, $markup_values );

		$markup_values = apply_filters( 'mla_custom_list_open_values', $markup_values );
		$open_template = apply_filters( 'mla_custom_list_open_template', $open_template );
		if ( empty( $open_template ) ) {
			$gallery_open = '';
		} else {
			$gallery_open = MLAData::mla_parse_template( $open_template, $markup_values );
		}

		$gallery_open = apply_filters( 'mla_custom_list_open_parse', $gallery_open, $open_template, $markup_values );
		$list .= $gallery_open;

		$column_index = 0;
		$has_active = false;
		foreach ( $values as $key => $value ) {
			// Start of row markup
			if ( $markup_values['columns'] > 0 && $column_index % $markup_values['columns'] === 0 ) {
				$markup_values = apply_filters( 'mla_custom_list_row_open_values', $markup_values );
				$row_open_template = apply_filters( 'mla_custom_list_row_open_template', $row_open_template );
				$parse_value = MLAData::mla_parse_template( $row_open_template, $markup_values );
				$list .= apply_filters( 'mla_custom_list_row_open_parse', $parse_value, $row_open_template, $markup_values );
			}

			// item markup
			$item_values = $markup_values;
			$item_values['index'] = (string) 1 + $column_index;
			$item_values['key'] = $key;
			$is_active = self::_compose_item_specific_values( $item_values, $item_template, $value, $arguments, $attr );
			$column_index++;
			$has_active |= $is_active;

			$item_values = apply_filters( 'mla_custom_list_item_values', $item_values );
			$item_template = apply_filters( 'mla_custom_list_item_template', $item_template );
			$parse_value = MLAData::mla_parse_template( $item_template, $item_values );
			$list .= apply_filters( 'mla_custom_list_item_parse', $parse_value, $item_template, $item_values );

			// End of row markup
			if ( $markup_values['columns'] > 0 && $column_index % $markup_values['columns'] === 0 ) {
				$markup_values = apply_filters( 'mla_custom_list_row_close_values', $markup_values );
				$row_close_template = apply_filters( 'mla_custom_list_row_close_template', $row_close_template );
				$parse_value = MLAData::mla_parse_template( $row_close_template, $markup_values );
				$list .= apply_filters( 'mla_custom_list_row_close_parse', $parse_value, $row_close_template, $markup_values );
			}
		} // foreach tag

		// Close out partial row
		if ( ! ($markup_values['columns'] > 0 && $column_index % $markup_values['columns'] === 0 ) ) {
			$markup_values = apply_filters( 'mla_custom_list_row_close_values', $markup_values );
			$row_close_template = apply_filters( 'mla_custom_list_row_close_template', $row_close_template );
			$parse_value = MLAData::mla_parse_template( $row_close_template, $markup_values );
			$list .= apply_filters( 'mla_custom_list_row_close_parse', $parse_value, $row_close_template, $markup_values );
		}

		$markup_values = apply_filters( 'mla_custom_list_close_values', $markup_values );
		$close_template = apply_filters( 'mla_custom_list_close_template', $close_template );
		$parse_value = MLAData::mla_parse_template( $close_template, $markup_values );
		$list .= apply_filters( 'mla_custom_list_close_parse', $parse_value, $close_template, $markup_values );

		return $has_active;
	} // _compose_custom_grid

	/**
	 * Compose a list, dropdown or checklist format custom field list
	 *
	 * Adds shortcode output text and term-specific links to arrays passed by reference.
	 *
	 * @since 3.13
	 *
	 * @param string $list Shortcode output text, by reference
	 * @param array $links Term-specific links for flat/array output, by reference
	 * @param array $values Custom List objects, by reference
	 * @param array $markup_values Style and list-level substitution parameters, by reference
	 * @param array $arguments Shortcode parameters, including defaults, by reference
	 * @param array $attr Shortcode parameters, explicit, by reference
	 *
	 * @return boolean True if the list contains the "current_item"; appends to &$list, &$links
	 */
	private static function _compose_custom_list( &$list, &$links, &$values, &$markup_values, &$arguments, &$attr ) {
//error_log( __LINE__ . ' _compose_custom_list values = ' . var_export( $values, true ), 0 );
//error_log( __LINE__ . ' _compose_custom_list markup_values = ' . var_export( $markup_values, true ), 0 );
//error_log( __LINE__ . ' _compose_custom_list arguments = ' . var_export( $arguments, true ), 0 );
		$value = reset( $values );
		$mla_item_parameter = $arguments['mla_item_parameter'];

		// Determine output type and templates
		$is_flat = 'flat' === $arguments['mla_output'];
		$is_flat_div = $is_flat && ( 'div' === $arguments['mla_output_qualifier'] );
		$is_list = in_array( $arguments['mla_output'], array( 'ulist', 'olist', 'dlist' ) );
		$is_dropdown = 'dropdown' === $arguments['mla_output'];
		$is_checklist = 'checklist' === $arguments['mla_output'];
		$is_checklist_div = $is_checklist && ( 'div' === $arguments['mla_output_qualifier'] );

		$open_template = '';
		$item_template = '';
		$close_template = '';

//		if ( $is_flat_div || $is_list || $is_dropdown || $is_checklist ) {
		if ( !empty( $markup_values['mla_markup'] ) ) {
			$open_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'custom-list', 'markup', 'open' );
			if ( empty( $open_template ) ) {
				$open_template = '';
			}

			$item_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'custom-list', 'markup', 'item' );
			if ( empty( $item_template ) ) {
				$item_template = '';
			}

			$close_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'custom-list', 'markup', 'close' );
			if ( empty( $close_template ) ) {
				$close_template = '';
			}
		}

		// Look for gallery-level markup substitution parameters
		$new_text = $open_template . $close_template;
		if ( !empty( $new_text ) ) {
			$markup_values = MLAData::mla_expand_field_level_parameters( $new_text, $attr, $markup_values );

			$markup_values = apply_filters( 'mla_custom_list_open_values', $markup_values );
			$open_template = apply_filters( 'mla_custom_list_open_template', $open_template );
			$gallery_open = MLAData::mla_parse_template( $open_template, $markup_values );
			$list .=  apply_filters( 'mla_custom_list_open_parse', $gallery_open, $open_template, $markup_values );
		}

		$column_index = 0;
		$has_active = false;
		foreach ( $values as $key => $value ) {
			$item_values = $markup_values;
			$item_values['index'] = (string) 1 + $column_index;
			$item_values['key'] = $key;
			$is_active = self::_compose_item_specific_values( $item_values, $item_template, $value, $arguments, $attr );
			$column_index++;
			$has_active |= $is_active;

			if ( $is_list || $is_dropdown || $is_checklist ) {
				$item_values = apply_filters( 'mla_custom_list_item_values', $item_values );
				$item_template = apply_filters( 'mla_custom_list_item_template', $item_template );
				$parse_value = MLAData::mla_parse_template( $item_template, $item_values );
				$list .= apply_filters( 'mla_custom_list_item_parse', $parse_value, $item_template, $item_values );
			} else {
				$item_values = apply_filters( 'mla_custom_list_item_values', $item_values );
				$links[] = apply_filters( 'mla_custom_list_item_parse', $item_values['thelink'], NULL, $item_values );
			} 
		} // foreach value

		// If the current item isn't in the term list, remove it to prevent "stale" [mla_gallery] content
		if ( false === $has_active ) {
			$mla_control_name = $markup_values['thename'];

			// Does not handle default 'tax_input[[+taxonomy+]][]' values
			if ( false === strpos( $mla_control_name, '[]' ) ) {
				unset( $_REQUEST[ $mla_item_parameter ] );
				unset( $_REQUEST[ $mla_control_name ] );
			}
		}

		if ( $is_flat_div ) {
			$list .= join( $markup_values['separator'], $links );
		}
		
		if ( $is_flat_div || $is_list || $is_dropdown || $is_checklist ) {
			$markup_values = apply_filters( 'mla_custom_list_close_values', $markup_values );
			$close_template = apply_filters( 'mla_custom_list_close_template', $close_template );
			$parse_value = MLAData::mla_parse_template( $close_template, $markup_values );
			$list .= apply_filters( 'mla_custom_list_close_parse', $parse_value, $close_template, $markup_values );
		} else {
			switch ( $markup_values['mla_output'] ) {
			case 'array' :
				$list =& $links;
				break;
			case 'flat' :
			default :
				$list .= join( $markup_values['separator'], $links );
			} // switch format
		}

		return $has_active;
	} // _compose_custom_list

	/**
	 * The MLA Custom Field List support function
	 *
	 * This is a variation on the [mla_tag_cloud] and [mla_term_list] shortcodes, composing
	 * a cloud, list or dropdown ontrol based on custom field values.
	 *
	 * @since 3.13
	 *
	 * @param array $attr Attributes of the shortcode.
	 *
	 * @return void|string|string[] Void if 'echo' attribute is true, or on failure. Otherwise, list markup as a string or an array of links, depending on 'mla_output' attribute.
	 */
	public static function mla_custom_list( $attr ) {
		global $post;

		// Some do_shortcode callers may not have a specific post in mind
		if ( ! is_object( $post ) ) {
			$post = MLAShortcode_Support::mla_get_default_post();
		}

		// $instance supports multiple lists in one page/post	
		static $instance = 0;
		$instance++;

		// Some values are already known, and can be used in data selection parameters
		$upload_dir = wp_upload_dir();
		$page_values = array(
			'instance' => $instance,
			'selector' => "mla_custom_list-{$instance}",
			'site_url' => site_url(),
			'base_url' => $upload_dir['baseurl'],
			'base_dir' => $upload_dir['basedir'],
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
			'page_guid' => $post->guid,
			'page_type' => $post->post_type,
			'page_url' => get_page_link(),
		);

		$defaults = array_merge(
			self::$mla_get_custom_values_parameters,
			array(
			'meta_value' => '',
			'mla_output' => 'flat',
			'mla_output_qualifier' => '',
			'echo' => false,

			'show_count' => false,
			'current_item' => '',
			'mla_item_parameter' => 'current_item',

			'smallest' => 8,
			'largest' => 22,
			'default_size' => 12,
			'unit' => 'pt',
			'separator' => "\n",

			'single_text' => '%s item',
			'multiple_text' => '%s items',
			'link' => 'current',
			'current_item_class' => 'mla_current_item',

			'mla_style' => NULL,
			'mla_markup' => NULL,

			'columns' => 3,
			'mla_float' => is_rtl() ? 'right' : 'left',
			'mla_margin' => '1.5%',
			'mla_itemwidth' => '33.3%',

			'itemtag' => 'ul',
			'valuetag' => 'li',
			'captiontag' => '',

			'mla_nolink_text' => '',
			'mla_target' => '',
			'hide_if_empty' => false,

			'option_all_text' => '',
			'option_all_value' => NULL,
			'option_no_values_text' => '',
			'option_no_values_value' => NULL,
			'option_any_values_text' => '',
			'option_any_valuess_value' => NULL,

			'mla_multi_select' => '',
			'option_none_text' => '',
			'option_none_value' => NULL,

			// Pagination parameters
			'numberposts' => 0,
			'posts_per_page' => 0,
			'mla_end_size'=> 1,
			'mla_mid_size' => 2,
			'mla_prev_text' => '&laquo; ' . __( 'Previous', 'media-library-assistant' ),
			'mla_next_text' => __( 'Next', 'media-library-assistant' ) . ' &raquo;',
			'mla_page_parameter' => 'mla_custom_list_current',
			'mla_custom_list_current' => 1,
			'mla_paginate_total' => NULL,
			'mla_paginate_rows' => NULL,
			'mla_paginate_type' => 'plain',

			'mla_debug' => false,
			),
			self::$item_specific_arguments
		);

		// Filter the attributes before $mla_item_parameter and "request:" prefix processing.
		$attr = apply_filters( 'mla_custom_list_raw_attributes', $attr );

		/*
		 * The current_item parameter can be changed to support
		 * multiple lists per page.
		 */
		if ( ! isset( $attr['mla_item_parameter'] ) ) {
			$attr['mla_item_parameter'] = $defaults['mla_item_parameter'];
		}

		// The mla_item_parameter can contain page_level parameters like {+page_ID+}
		$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr['mla_item_parameter'] ) );
		$mla_item_parameter = MLAData::mla_parse_template( $attr_value, $page_values );
		 
		/*
		 * Special handling of mla_item_parameter to make multiple lists per page easier.
		 * Look for this parameter in $_REQUEST if it's not present in the shortcode itself.
		 */
		if ( ! isset( $attr[ $mla_item_parameter ] ) ) {
			if ( isset( $_REQUEST[ $mla_item_parameter ] ) ) {
				if ( is_array( $_REQUEST[ $mla_item_parameter ] ) ) {
					foreach( array_map( 'sanitize_text_field', wp_unslash( $_REQUEST[ $mla_item_parameter ] ) ) as $item ) {
						$attr[ $mla_item_parameter ][] = $item;
					}
				} else {
					$attr[ $mla_item_parameter ] = sanitize_text_field( wp_unslash( $_REQUEST[ $mla_item_parameter ] ) );
				}
			}
		}

		/*
		 * The mla_custom_list_current parameter can be changed to support
		 * multiple galleries per page.
		 */
		if ( ! isset( $attr['mla_page_parameter'] ) ) {
			$attr['mla_page_parameter'] = $defaults['mla_page_parameter'];
		}

		// The mla_page_parameter can contain page_level parameters like {+page_ID+}
		$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr['mla_page_parameter'] ) );
		$mla_page_parameter = MLAData::mla_parse_template( $attr_value, $page_values );

		/*
		 * Special handling of the mla_custom_list_current parameter to make "MLA pagination"
		 * easier. Look for this parameter in $_REQUEST if it's not present in the shortcode itself.
		 */
		if ( ! isset( $attr[ $mla_page_parameter ] ) ) {
			if ( isset( $_REQUEST[ $mla_page_parameter ] ) ) {
				$attr[ $mla_page_parameter ] = sanitize_text_field( wp_unslash( $_REQUEST[ $mla_page_parameter ] ) );
			}
		}

		// Determine markup template to get template-based argument values
		if ( !empty( $attr['mla_markup'] ) ) {
			$template = $attr['mla_markup'];
			if ( ! MLATemplate_Support::mla_fetch_custom_template( $template, 'custom-list', 'markup', '[exists]' ) ) {
				$template = NULL;
			}
		} else {
			$template = NULL;
		}

		// Apply default arguments set in the markup template
		if ( !empty( $template ) ) {
			$arguments = MLATemplate_Support::mla_fetch_custom_template( $template, 'custom-list', 'markup', 'arguments' );
			if ( ! empty( $arguments ) ) {
				$attr = wp_parse_args( $attr, MLAShortcode_Support::mla_validate_attributes( array(), $arguments ) );
			}
		}

		// Update arguments with  final $attr content
		$arguments = shortcode_atts( $defaults, $attr );

		/*
		 * $mla_item_parameter, if non-default, doesn't make it through the shortcode_atts
		 * filter, so we handle it separately
		 */
		if ( ! isset( $arguments[ $mla_item_parameter ] ) ) {
			if ( isset( $attr[ $mla_item_parameter ] ) ) {
				$arguments[ $mla_item_parameter ] = $attr[ $mla_item_parameter ];
			} else {
				$arguments[ $mla_item_parameter ] = $defaults['current_item'];
			}
		}

		/*
		 * $mla_page_parameter, if non-default, doesn't make it through the shortcode_atts
		 * filter, so we handle it separately
		 */
		if ( ! isset( $arguments[ $mla_page_parameter ] ) ) {
			if ( isset( $attr[ $mla_page_parameter ] ) ) {
				$arguments[ $mla_page_parameter ] = $attr[ $mla_page_parameter ];
			} else {
				$arguments[ $mla_page_parameter ] = $defaults['mla_page_parameter'];
			}
		}

		$output_parameters = array_map( 'strtolower', array_map( 'trim', explode( ',', $arguments['mla_output'] ) ) );
		if ( !in_array( $output_parameters[0], array_merge( self::$valid_mla_output_values, self::$valid_mla_output_pagination_values ) ) ) {
			$output_parameters[0] = 'flat';
		}

		$arguments['mla_output'] = $output_parameters[0];
		$arguments['mla_output_qualifier'] = isset( $output_parameters[1] ) ? $output_parameters[1] : '';

		// Set default template if not specified in $attr
		if ( empty( $template ) ) {
			switch ( $arguments['mla_output'] ) {
				case 'ulist':
				case 'olist':
					$template = 'custom-list-ul';
					break;
				case 'dlist':
					$template = 'custom-list-dl';
					break;
				case 'grid':
					$template = 'custom-list-grid';
					break;
				case 'dropdown':
					$template = 'custom-list-dropdown';
					break;
				case 'checklist':
					$template = 'custom-list-checklist';
					break;
				default:
					$template = NULL;
			}
		}

		/*
		 * Look for page-level, 'request:' and 'query:' substitution parameters,
		 * which can be added to any input parameter
		 */
		foreach ( $attr as $attr_key => $attr_value ) {
			/*
			 * item-specific Display Content parameters must be evaluated
			 * later, when all of the information is available.
			 */
			if ( array_key_exists( $attr_key, self::$item_specific_arguments ) ) {
				continue;
			}

			if ( is_string( $attr_value ) ) {
				$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr_value ) );
				$replacement_values = MLAData::mla_expand_field_level_parameters( $attr_value, $attr, $page_values );
				$attr[ $attr_key ] = MLAData::mla_parse_template( $attr_value, $replacement_values );
			}
		}

		$attr = apply_filters( 'mla_custom_list_attributes', $attr );
		$arguments = shortcode_atts( $defaults, $attr );

		/*
		 * $mla_item_parameter, if non-default, doesn't make it through the shortcode_atts
		 * filter, so we handle it separately
		 */
		if ( ! isset( $arguments[ $mla_item_parameter ] ) ) {
			if ( isset( $attr[ $mla_item_parameter ] ) ) {
				$arguments[ $mla_item_parameter ] = $attr[ $mla_item_parameter ];
			} else {
				$arguments[ $mla_item_parameter ] = $defaults['current_item'];

			}
		}

		/*
		 * $mla_page_parameter, if non-default, doesn't make it through the shortcode_atts
		 * filter, so we handle it separately
		 */
		if ( ! isset( $arguments[ $mla_page_parameter ] ) ) {
			if ( isset( $attr[ $mla_page_parameter ] ) ) {
				$arguments[ $mla_page_parameter ] = $attr[ $mla_page_parameter ];
			} else {
				$arguments[ $mla_page_parameter ] = $defaults['mla_page_parameter'];

			}
		}

		// Process the pagination parameter, if present
		if ( isset( $arguments[ $mla_page_parameter ] ) ) {
			$arguments['offset'] = absint( $arguments['limit'] ) * ( absint( $arguments[ $mla_page_parameter ] ) - 1);
		}

		$output_parameters = array_map( 'strtolower', array_map( 'trim', explode( ',', $arguments['mla_output'] ) ) );
		if ( !in_array( $output_parameters[0], array_merge( self::$valid_mla_output_values, self::$valid_mla_output_pagination_values ) ) ) {
			$output_parameters[0] = 'flat';
		}

		$arguments['mla_output'] = $output_parameters[0];
		$arguments['mla_output_qualifier'] = isset( $output_parameters[1] ) ? $output_parameters[1] : '';

		$arguments = apply_filters( 'mla_custom_list_arguments', $arguments );

		self::$mla_debug = ( ! empty( $arguments['mla_debug'] ) ) ? trim( strtolower( $arguments['mla_debug'] ) ) : false;
		if ( self::$mla_debug ) {
			if ( 'true' === self::$mla_debug ) {
				MLACore::mla_debug_mode( 'buffer' );
			} elseif ( 'log' === self::$mla_debug ) {
				MLACore::mla_debug_mode( 'log' );
			} else {
				self::$mla_debug = false;
			}
		}

		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_custom_list REQUEST', 'media-library-assistant' ) . '</strong> = ' . var_export( $_REQUEST, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_custom_list attributes', 'media-library-assistant' ) . '</strong> = ' . var_export( $attr, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_custom_list arguments', 'media-library-assistant' ) . '</strong> = ' . var_export( $arguments, true ) );
		}

		// Determine templates and output type
		if ( $arguments['mla_style'] && ( 'none' !== $arguments['mla_style'] ) ) {
			if ( !MLATemplate_Support::mla_fetch_custom_template( $arguments['mla_style'], 'custom-list', 'style', '[exists]' ) ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>mla_custom_list mla_style</strong> "' . $arguments['mla_style'] . '" ' . __( 'not found', 'media-library-assistant' ), MLACore::MLA_DEBUG_CATEGORY_ANY );
				$arguments['mla_style'] = NULL;
			}
		}

		if ( $arguments['mla_markup'] ) {
			if ( !MLATemplate_Support::mla_fetch_custom_template( $arguments['mla_markup'], 'custom-list', 'markup', '[exists]' ) ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>mla_custom_list mla_markup</strong> "' . $arguments['mla_markup'] . '" ' . __( 'not found', 'media-library-assistant' ), MLACore::MLA_DEBUG_CATEGORY_ANY );
				$arguments['mla_markup'] = NULL;
			}
		}

		$is_pagination = in_array( $arguments['mla_output'], self::$valid_mla_output_pagination_values );
		$is_flat = 'flat' === $arguments['mla_output'];
		$is_flat_div = $is_flat && ( 'div' === $arguments['mla_output_qualifier'] );

		$default_style = 'custom-list';
		$default_markup = 'custom-list-ul';

		if ( $is_pagination ) {
			$default_style = 'none';
		}

		if ( $is_flat ) {
			if ( $is_flat_div ) {
				$default_style = 'custom-list-flat-div';
				$default_markup = 'custom-list-flat-div';
			} else {
				$default_style = 'none';
			}
		}

		if ( $is_grid = 'grid' === $arguments['mla_output'] ) {
			$default_markup = 'custom-list-grid';

			if ( empty( $attr['itemtag'] ) ) {
				$arguments['itemtag'] = 'dl';
			}

			if ( empty( $attr['termtag'] ) ) {
				$arguments['termtag'] = 'dt';
			}

			if ( empty( $attr['captiontag'] ) ) {
				$arguments['captiontag'] = 'dd';
			}
		}

		if ( $is_list = in_array( $arguments['mla_output'], array( 'dlist', 'olist', 'ulist' ) ) ) {
			$arguments['valuetag'] = 'li';
			$arguments['captiontag'] = '';

			switch ( $arguments['mla_output'] ) {
				case 'dlist':
					$default_markup = 'custom-list-dl';
					$arguments['itemtag'] = 'dl';
					$arguments['valuetag'] = 'dt';
					$arguments['captiontag'] = 'dd';
				break;
				case 'olist':
					$arguments['itemtag'] = 'ol';
					break;
				case 'ulist':
				default:
					$arguments['itemtag'] = 'ul';
			}
		}

		// Set default "cloud" arguments for non-flat output formats
		if ( !$is_flat ) {
			if ( empty( $attr['smallest'] ) ) {
				$arguments['smallest'] = $arguments['default_size'];
			}

			if ( empty( $attr['largest'] ) ) {
				$arguments['largest'] = $arguments['default_size'];
			}
		}
		
		if ( $is_dropdown = 'dropdown' === $arguments['mla_output'] ) {
			$default_markup = 'custom-list-dropdown';
			$arguments['itemtag'] = empty( $attr['itemtag'] ) ? 'select' : $attr['itemtag'];
			$arguments['valuetag'] = 'option';
		}

		if ( $is_checklist = 'checklist' === $arguments['mla_output'] ) {
			if ( 'div' === $arguments['mla_output_qualifier'] ) {
				$default_style = 'custom-list-checklist-div';
				$default_markup = 'custom-list-checklist-div';
			} else {
				$default_markup = 'custom-list-checklist';
			}
			
			$arguments['valuetag'] = 'li';
		}

		$arguments['default_mla_style'] = $default_style;
		if ( NULL === $arguments['mla_style'] ) {
			$arguments['mla_style'] = $default_style;
		}

		$arguments['default_mla_markup'] = $default_markup;
		if ( NULL === $arguments['mla_markup'] ) {
			$arguments['mla_markup'] = $default_markup;
		}

		$mla_multi_select = ( ! empty( $arguments['mla_multi_select'] ) ) && ( 'true' === trim( strtolower( $arguments['mla_multi_select'] ) ) );

		// Convert lists to arrays
		if ( is_string( $arguments['post_type'] ) ) {
			$arguments['post_type'] = explode( ',', $arguments['post_type'] );
		}

		if ( is_string( $arguments['post_status'] ) ) {
			$arguments['post_status'] = explode( ',', $arguments['post_status'] );
		}

		if ( $is_pagination && ( NULL !== $arguments['mla_paginate_rows'] ) ) {
			$values = array( 'found_rows' => absint( $arguments['mla_paginate_rows'] ) );
		} else {
			$values = self::mla_get_custom_values( $arguments );
	
			// Future possible return WP_Error
			if ( is_wp_error( $values ) ) {
				$list =  '<strong>' . __( 'ERROR', 'media-library-assistant' ) . ': ' . $values->get_error_message() . '</strong>, ' . $values->get_error_data( $values->get_error_code() );
	
				if ( 'array' === $arguments['mla_output'] ) {
					return array( $list );
				}
	
				if ( empty($arguments['echo']) ) {
					return $list;
				}
	
				echo $list; // phpcs:ignore
				return;
			}
		}

		// Fill in the item_specific link properties, calculate list parameters
		if ( isset( $values['found_rows'] ) ) {
			$found_rows = (integer) $values['found_rows'];
			unset( $values['found_rows'] );
		} else {
			$found_rows = count( $values );
		}

		$show_empty = false;
		if ( 0 === $found_rows ) {
			if ( !empty( $arguments['mla_control_name'] ) ) {
				// Remove the current item from the parameters to prevent "stale" [mla_gallery] content
				$mla_control_name = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_control_name'], $page_values );
	
				// Does not handle default 'tax_input[[+taxonomy+]][]' values
				unset( $_REQUEST[ $mla_item_parameter ] );
				unset( $_REQUEST[ $mla_control_name ] );
			}
	
			if ( self::$mla_debug ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug empty list', 'media-library-assistant' ) . '</strong>, query = ' . var_export( $arguments, true ) );
				$list = MLACore::mla_debug_flush();

				if ( '<p></p>' === $list ) {
					$list = '';
				}
			} else {
				$list = '';
			}

			if ( 'array' === $arguments['mla_output'] ) {
				$list .= $arguments['mla_nolink_text'];

				if ( empty( $list ) ) {
					return array();
				} else {
					return array( $list );
				}
			}

			$show_empty = empty( $arguments['hide_if_empty'] ) || ( 'true' !== strtolower( $arguments['hide_if_empty'] ) );
			if ( ( $is_checklist || $is_dropdown ) && $show_empty ) {
				if ( ! empty( $arguments['option_none_text'] ) ) {
					$arguments['option_none_text'] = esc_attr( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['option_none_text'], $page_values ) );
				} else {
					$arguments['option_none_text'] = esc_attr__( 'no-values', 'media-library-assistant' );
				}

				if ( ! empty( $arguments['option_none_value'] ) ) {
					$arguments['option_none_value'] = esc_attr( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['option_none_value'], $page_values ) );
				} else {
					$arguments['option_none_value'] = $arguments['option_none_text']; // already escaped
				}

				$values[0] = ( object ) array(
					'meta_key' => $arguments['meta_key'],
					'meta_value' => $arguments['option_none_value'],
					'meta_text' => $arguments['option_none_text'],
					'count' => 0,
				);

				$found_rows = 1;
			} else {
				$list .= wp_kses( $arguments['mla_nolink_text'], 'post' );

				if ( empty($arguments['echo']) ) {
					return $list;
				}

				echo $list; // phpcs:ignore
				return;
			}
		} // 0 === $found_rows

		if ( 'true' === self::$mla_debug ) {
			$list = MLACore::mla_debug_flush();
		} else {
			$list = '';
		}

		// Compute the "cloud" values
		$min_count = 0x7FFFFFFF;
		$max_count = 0;
		$min_scaled_count = 0x7FFFFFFF;
		$max_scaled_count = 0;
		foreach ( $values as $key => $value ) {
			$value_count = isset ( $value->count ) ? $value->count : 0;
			$value->scaled_count = apply_filters( 'mla_custom_list_scale', round(log10($value_count + 1) * 100), $attr, $arguments, $value );

			if ( $value_count < $min_count ) {
				$min_count = $value_count;
			}

			if ( $value_count > $max_count ) {
				$max_count = $value_count;
			}

			if ( $value->scaled_count < $min_scaled_count ) {
				$min_scaled_count = $value->scaled_count;
			}

			if ( $value->scaled_count > $max_scaled_count ) {
				$max_scaled_count = $value->scaled_count;
			}
		} // foreach value

		if ( !$show_empty ) {
			$add_all_option = ! empty( $arguments['option_all_text'] );
			$add_any_values_option = ! empty( $arguments['option_any_values_text'] );
			$add_no_values_option = ! empty( $arguments['option_no_values_text'] );
		} else {
			$add_all_option = false;
			$add_any_values_option = false;
			$add_no_values_option = false;
		}

		if ( $add_all_option || $add_any_values_option || $add_no_values_option ) {
			$values_assigned_counts = self::mla_get_all_none_value_counts( $arguments );
		} else {
			$values_assigned_counts = array( 'ignore.values.assigned' => 0, 'no.values.assigned' => 0, 'any.values.assigned' => 0 );
		}

		// Remember actual found rows for pagination calculations
		$database_rows = $found_rows;

		$option_all_value = 'ignore.values.assigned';
		$option_all_text = 'ignore.values.assigned';
		if ( $add_all_option ) {
			$found_rows += 1;
			$option_all_text = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['option_all_text'], $page_values );
			if ( ! empty( $arguments['option_all_value'] ) ) {
				$option_all_value = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['option_all_value'], $page_values );
			}
		}

		$option_any_values_value = 'any.values.assigned';
		$option_any_values_text = 'any.values.assigned';
		if ( $add_any_values_option ) {
			$found_rows += 1;
			$option_any_values_text = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['option_any_values_text'], $page_values );
			if ( ! empty( $arguments['option_any_values_value'] ) ) {
				$option_any_values_value = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['option_any_values_value'], $page_values );
			}
		}

		$option_no_values_value = 'no.values.assigned';
		$option_no_values_text = 'no.values.assigned';
		if ( $add_no_values_option ) {
			$found_rows += 1;
			$option_no_values_text = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['option_no_values_text'], $page_values );
			if ( ! empty( $arguments['option_no_values_value'] ) ) {
				$option_no_values_value = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['option_no_values_value'], $page_values );
			}
		}

		/*
		 * The default MLA style template includes "margin: 1.5%" to put a bit of
		 * minimum space between the columns. "mla_margin" can be used to change
		 * this. "mla_itemwidth" can be used with "columns=0" to achieve a
		 * "responsive" layout.
		 */

		$columns = absint( $arguments['columns'] );
		$margin_string = strtolower( trim( $arguments['mla_margin'] ) );

		if ( is_numeric( $margin_string ) && ( 0 != $margin_string) ) {
			$margin_string .= '%'; // Legacy values are always in percent
		}

		if ( '%' == substr( $margin_string, -1 ) ) {
			$margin_percent = (float) substr( $margin_string, 0, strlen( $margin_string ) - 1 );
		} else {
			$margin_percent = 0;
		}

		$width_string = strtolower( trim( $arguments['mla_itemwidth'] ) );
		if ( 'none' != $width_string ) {
			switch ( $width_string ) {
				case 'exact':
					$margin_percent = 0;
					// fallthru
				case 'calculate':
					$width_string = $columns > 0 ? (floor(1000/$columns)/10) - ( 2.0 * $margin_percent ) : 100 - ( 2.0 * $margin_percent );
					// fallthru
				default:
					if ( is_numeric( $width_string ) && ( 0 != $width_string) ) {
						$width_string .= '%'; // Legacy values are always in percent
					}
			}
		} // $use_width

		$float = strtolower( $arguments['mla_float'] );
		if ( ! in_array( $float, array( 'left', 'none', 'right' ) ) ) {
			$float = is_rtl() ? 'right' : 'left';
		}

		// Calculate cloud parameters
		$spread = $max_scaled_count - $min_scaled_count;
		if ( $spread <= 0 ) {
			$spread = 1;
		}

		$font_spread = $arguments['largest'] - $arguments['smallest'];
		if ( $font_spread < 0 ) {
			$font_spread = 1;
		}

		$font_step = $font_spread / $spread;

		$style_values = array_merge( $page_values, array(
			'mla_output' => $arguments['mla_output'],
			'mla_output_qualifier' => $arguments['mla_output_qualifier'],
			'mla_style' => $arguments['mla_style'],
			'mla_markup' => $arguments['mla_markup'],
			'meta_key' => $arguments['meta_key'],
			'current_item' => $arguments['current_item'],
			'itemtag' => tag_escape( $arguments['itemtag'] ),
			'itemtag_attributes' => '',
			'itemtag_class' => 'custom-list custom-list-key-' . sanitize_title( $arguments['meta_key'] ), 
			'itemtag_id' => $page_values['selector'],
			'valuetag' => tag_escape( $arguments['valuetag'] ),
			'captiontag' => tag_escape( $arguments['captiontag'] ),
			'multiple' => $mla_multi_select ? 'multiple' : '',
			'columns' => $columns,
			'itemwidth' => $width_string,
			'margin' => $margin_string,
			'float' => $float,
			'database_rows' => $database_rows,
			'found_rows' => $found_rows,
			'min_count' => $min_count,
			'max_count' => $max_count,
			'min_scaled_count' => $min_scaled_count,
			'max_scaled_count' => $max_scaled_count,
			'spread' => $spread,
			'smallest' => $arguments['smallest'],
			'largest' => $arguments['largest'],
			'unit' => $arguments['unit'],
			'font_spread' => $font_spread,
			'font_step' => $font_step,
			'separator' => $arguments['separator'],
			'single_text' => $arguments['single_text'],
			'multiple_text' => $arguments['multiple_text'],
			'echo' => $arguments['echo'],
			'link' => $arguments['link']
		) );

		$style_template = $gallery_style = '';
		$use_mla_custom_list_style = 'none' !== strtolower( $style_values['mla_style'] );
		if ( apply_filters( 'use_mla_custom_list_style', $use_mla_custom_list_style, $style_values['mla_style'] ) ) {
			$style_template = MLATemplate_support::mla_fetch_custom_template( $style_values['mla_style'], 'custom-list', 'style' );
			if ( empty( $style_template ) ) {
				$style_values['mla_style'] = $default_style;
				$style_template = MLATemplate_support::mla_fetch_custom_template( $default_style, 'custom-list', 'style' );
			}

			if ( ! empty ( $style_template ) ) {
				// Look for 'query' and 'request' substitution parameters
				$style_values = MLAData::mla_expand_field_level_parameters( $style_template, $attr, $style_values );

				// Clean up the template to resolve width or margin == 'none'
				if ( 'none' == $margin_string ) {
					$style_values['margin'] = '0';
					$style_template = preg_replace( '/margin:[\s]*\[\+margin\+\][\%]*[\;]*/', '', $style_template );
				}

				if ( 'none' == $width_string ) {
					$style_values['itemwidth'] = 'auto';
					$style_template = preg_replace( '/width:[\s]*\[\+itemwidth\+\][\%]*[\;]*/', '', $style_template );
				}

				$style_values = apply_filters( 'mla_custom_list_style_values', $style_values );
				$style_template = apply_filters( 'mla_custom_list_style_template', $style_template );
				$gallery_style = MLAData::mla_parse_template( $style_template, $style_values );
				$gallery_style = apply_filters( 'mla_custom_list_style_parse', $gallery_style, $style_template, $style_values );
			} // !empty template
		} // use_mla_custom_list_style

		$list .= $gallery_style;
		$markup_values = $style_values;

		if ( empty( $arguments['mla_control_name'] ) ) {
			if ( $is_checklist || $mla_multi_select ) {
//				$mla_control_name = 'current_items[]';
				$mla_control_name = 'current_item[]';
			} else {
				$mla_control_name = 'current_item';
			}
		} else {
			$mla_control_name = $arguments['mla_control_name'];;
		}

		// Accumulate links for flat and array output
		$value_links = array();

		$markup_values['thename'] = esc_attr( MLAShortcode_Support::mla_process_shortcode_parameter( $mla_control_name, $markup_values ) );

		// Add the optional 'all-terms', 'any-terms' and/or 'no-terms' option(s), if requested
		if ( $add_any_values_option ) {
			$new_value = ( object ) array(
				'meta_key' => $arguments['meta_key'],
				'meta_value' => $option_any_values_value,
				'meta_text' => $option_any_values_text,
				'count' => -1,
			);
			$new_value->scaled_count = apply_filters( 'mla_custom_list_scale', round( log10( 1 ) * 100 ), $attr, $arguments, $new_value );

			if ( self::$mla_debug ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug adding ANY valuess', 'media-library-assistant' ) . '</strong> = ' . var_export( $new_value, true ) );
			}
			array_unshift( $values, $new_value );
		}

		if ( $add_no_values_option ) {
			$new_value = ( object ) array(
				'meta_key' => $arguments['meta_key'],
				'meta_value' => $option_no_values_value,
				'meta_text' => $option_no_values_text,
				'count' => -1,
			);
			$new_value->scaled_count = apply_filters( 'mla_custom_list_scale', round( log10( 1 ) * 100 ), $attr, $arguments, $new_value );

			if ( self::$mla_debug ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug adding NO valuess', 'media-library-assistant' ) . '</strong> = ' . var_export( $new_value, true ) );
			}
			array_unshift( $values, $new_value );
		}

		if ( $add_all_option ) {
			$new_value = ( object ) array(
				'meta_key' => $arguments['meta_key'],
				'meta_value' => $option_all_value,
				'meta_text' => $option_all_text,
				'count' => -1,
			);
			$new_value->scaled_count = apply_filters( 'mla_custom_list_scale', round( log10( 1 ) * 100 ), $attr, $arguments, $new_value );

			if ( self::$mla_debug ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug adding ALL valuess', 'media-library-assistant' ) . '</strong> = ' . var_export( $new_value, true ) );
			}
			array_unshift( $values, $new_value );
		}

		if ( count( $values ) ) {
			if ( $is_grid ) {
				self::_compose_custom_grid( $list, $value_links, $values, $markup_values, $arguments, $attr );
			} elseif ( $is_pagination ) {
				self::_compose_custom_pagination( $list, $value_links, $values, $markup_values, $arguments, $attr );
			} else {
				self::_compose_custom_list( $list, $value_links, $values, $markup_values, $arguments, $attr );
			}
		}

		if ( 'array' === $arguments['mla_output'] || empty($arguments['echo']) ) {
			return $list;
		}

		echo $list; // phpcs:ignore
	} // mla_custom_list
	
	/**
	 * The MLA Custom Field List shortcode
	 *
	 * This is an interface to the mla_custom_list function.
	 *
	 * @since 3.13
	 *
	 * @param array $attr Attributes of the shortcode.
	 * @param string $content Optional content for enclosing shortcodes
	 *
	 * @return string HTML content to display the custom field list.
	 */
	public static function mla_custom_list_shortcode( $attr, $content = NULL ) {
		/*
		 * Make sure $attr is an array, even if it's empty,
		 * and repair damage caused by line breaks in the source text
		 */
		$attr = MLAShortcode_Support::mla_validate_attributes( $attr, $content );

		// The 'array' format makes no sense in a shortcode
		if ( isset( $attr['mla_output'] ) && 'array' === $attr['mla_output'] ) {
			$attr['mla_output'] = 'flat';
		}
			 
		// A shortcode must return its content to the caller, so "echo" makes no sense
		if ( isset( $attr['echo'] ) ) {
			$attr['echo'] = 'false';
		}
			 
		return self::mla_custom_list( $attr );
	}

	/**
	 * Retrieve the values  for one or more custom fields
	 *
	 * Alternative to WordPress meta query that provides
	 * an accurate count of attachments associated with each value.
	 *
	 * meta_key - string containing one custom field name. Default ''.
	 *
	 * fields - string with fields for the SQL SELECT clause, e.g.,
	 *          'm.meta_key, m.meta_value'
	 *          ', COUNT(p.ID) AS `count`' will be added to the end unless no_count=true
	 *          'm.meta_id, m.post_id, m.meta_key, m.meta_value, COUNT(p.ID) AS `count`'
	 *
	 * post_mime_type - MIME type(s) of the items to include in the term-specific counts. Default 'all'.
	 *
	 * post_type - The post type(s) of the items to include in the term-specific counts.
	 * The default is "attachment". 
	 *
	 * post_status - The post status value(s) of the items to include in the term-specific counts.
	 * The default is "inherit".
	 *
	 * ids - A comma-separated list of attachment ID values for an item-specific cloud.
	 *
	 * no_count - 'true', 'false' (default) to suppress term-specific attachment-counting process.
	 *
	 * include - An array or comma-delimited string of field values to include
	 * in the return array.
	 *
	 * exclude - An array or comma-delimited string of field values to exclude
	 * from the return array. If 'include' is non-empty, 'exclude' is ignored.
	 *
	 * minimum - minimum number of attachments a value must have to be included. Default 0.
	 *
	 * number - maximum number of value objects to return. Values are ordered by count,
	 * descending and then by meta_value before this number is applied. Default 0.
	 *
	 * orderby - 'count', 'meta_value' (default), 'none', 'random'
	 *
	 * order - 'ASC' (default), 'DESC'
	 *
	 * no_orderby - 'true', 'false' (default) to suppress ALL sorting clauses else false.
	 *
	 * preserve_case - 'true', 'false' (default) to make orderby case-sensitive.
	 *
	 * limit - final number of term objects to return, for pagination. Default 0.
	 *
	 * offset - number of term objects to skip, for pagination. Default 0.
	 *
	 * @since 3.13
	 *
	 * @param	array	taxonomies to search and query parameters
	 *
	 * @return	array	array of term objects, empty if none found
	 */
	public static function mla_get_custom_values( $attr ) {
		global $wpdb;

		// Make sure $attr is an array, even if it's empty
		if ( empty( $attr ) ) {
			$attr = array();
		} elseif ( is_string( $attr ) ) {
			$attr = shortcode_parse_atts( $attr );
		}

		// Merge input arguments with defaults
		$attr = apply_filters( 'mla_get_custom_values_query_attributes', $attr );
		$arguments = shortcode_atts( self::$mla_get_custom_values_parameters, $attr );
		$arguments = apply_filters( 'mla_get_custom_values_query_arguments', $arguments );

		// Build an array of individual clauses that can be filtered
		$clauses = array( 'fields' => '', 'join' => '', 'where' => '', 'orderby' => '', 'limits' => '', );

		/*
		 * If we're not counting attachments per term, strip
		 * post fields out of list and adjust the orderby value
		 */
		$no_count = false;
		$field_array = explode( ',', $arguments['fields'] );

		if ( 'true' === trim( strtolower( (string) $arguments['no_count'] ) ) ) {
			foreach ( $field_array as $index => $field ) {
				if ( false !== strpos( $field, 'p.' ) ) {
					unset( $field_array[ $index ] );
				}
			}

			$arguments['minimum'] = 0;
			$arguments['post_mime_type'] = 'all';

			if ( 'count' === strtolower( $arguments['orderby'] ) ) {
				$arguments['orderby'] = 'none';
			}

			$no_count = true;
		}

		$clauses['fields'] = implode( ',', $field_array );
		$clause = array ();
		$clause_parameters = array();

		if ( false === $no_count ) {
			$clauses['fields'] .= ', COUNT(p.ID) AS `count`';
			$clause[] = 'LEFT JOIN `' . $wpdb->posts . '` AS p ON ( m.post_id = p.ID';

			// Add type and status constraints
			if ( is_array( $arguments['post_type'] ) ) {
				$post_types = $arguments['post_type'];
			} else {
				$post_types = array( $arguments['post_type'] );
			}

			$placeholders = array();
			foreach ( $post_types as $post_type ) {
				$placeholders[] = '%s';
				$clause_parameters[] = $post_type;
			}

			$clause[] = 'AND p.post_type IN (' . join( ',', $placeholders ) . ')';

			if ( is_array( $arguments['post_status'] ) ) {
				$post_stati = $arguments['post_status'];
			} else {
				$post_stati = array( $arguments['post_status'] );
			}

			$placeholders = array();
			foreach ( $post_stati as $post_status ) {
				if ( ( 'private' != $post_status ) || is_user_logged_in() ) {
					$placeholders[] = '%s';
					$clause_parameters[] = $post_status;
				}
			}

			$clause[] = 'AND p.post_status IN (' . join( ',', $placeholders ) . ') )';
		}

		$clause =  join(' ', $clause);
		if ( ! empty( $clause_parameters ) ) {
			$clauses['join'] = $wpdb->prepare( $clause, $clause_parameters ); // phpcs:ignore
		} else {
			$clauses['join'] = $clause;
		}

		// Start WHERE clause with a taxonomy constraint
		$placeholders = array( '%s' );
		$clause_parameters = array( $arguments['meta_key'] );
		$clause = array ( $wpdb->prepare( 'm.meta_key = \'' . join( ',', $placeholders ) . '\'', $clause_parameters ) ); // phpcs:ignore
//		$clause = array( "m.meta_key = '" . $arguments['meta_key'] . "'" );

		$clause_parameters = array();
		$placeholders = array();

		/*
		 * The "ids" parameter can build an item-specific cloud.
		 * Compile a list of all the terms assigned to the items.
		 */
		if ( ! empty( $arguments['ids'] ) ) {
			$ids = wp_parse_id_list( $arguments['ids'] );
		    $placeholders = implode( "','", $ids );
			$clause[] = "AND m.post_id IN ( '{$placeholders}' )";

			$includes = array();
			foreach ( $ids as $id ) {
				$values = get_post_meta( $id, $arguments['meta_key'] );
				if ( is_array( $values ) ) {
					foreach( $values as $value ) {
						$includes[ $value ] = $value;
					}
				}
			} // ids

			// Apply a non-empty argument before we replace it.
			if ( ! empty( $arguments['include'] ) ) {
				$includes = array_intersect( $includes, wp_parse_id_list( $arguments['include'] ) );
			}

			// If there are no values we want an empty cloud
			if ( empty( $includes ) ) {
				$arguments['include'] = (string) 0x7FFFFFFF;
			} else {
				ksort( $includes );
				$arguments['include'] = implode( ',', $includes );
			}
		}

		// Add include/exclude and parent constraints to WHERE cluse
		if ( ! empty( $arguments['include'] ) ) {
		    $placeholders = implode( "','", str_getcsv( $arguments['include'] ) );
			$clause[] = "AND m.meta_value IN ( '{$placeholders}' )";
		} elseif ( ! empty( $arguments['exclude'] ) ) {
		    $placeholders = implode( "','", str_getcsv( $arguments['exclude'] ) );
			$clause[] = "AND m.meta_value NOT IN ( '{$placeholders}' )";
		}

		// Exclude MLA's "empty" value
		$clause[] = "AND m.meta_value NOT IN ( ' ' )";

		if ( 'all' === trim( strtolower( $arguments['post_mime_type'] ) ) ) {
			$post_mimes = '';
		} else {
			$post_mimes = wp_post_mime_type_where( $arguments['post_mime_type'], 'p' );
			$where = str_replace( '%', '%%', $post_mimes );

			if ( 0 === absint( $arguments['minimum'] ) ) {
				$clause[] = ' AND ( p.post_mime_type IS NULL OR ' . substr( $where, 6 );
			} else {
				$clause[] = $where;
			}
		}

		$clause =  join(' ', $clause);
		if ( ! empty( $clause_parameters ) ) {
			$clauses['where'] = $wpdb->prepare( $clause, $clause_parameters ); // phpcs:ignore
		} else {
			$clauses['where'] = $clause;
		}

		// For the inner/initial query, always select the most popular terms
		if ( $no_orderby = 'true' === trim( strtolower( (string) $arguments['no_orderby'] ) ) ) {
			$arguments['orderby'] = 'count';
			$arguments['order']  = 'DESC';
		}

		// Add sort order
		if ( 'none' !== strtolower( $arguments['orderby'] ) ) {
			if ( 'true' === strtolower( $arguments['preserve_case'] ) ) {
				$binary_keys = array( 'meta_value', );
			} else {
				$binary_keys = array();
			}

			$allowed_keys = array(
				'empty_orderby_default' => 'meta_value',
				'count' => 'count',
				'meta_value' => 'meta_value',
				'random' => 'RAND()',
			);

			$orderby_parameters = array( $arguments['orderby'], $arguments['order'],  );
			$clauses['orderby'] = 'ORDER BY ' . MLAShortcode_Support::mla_validate_sql_orderby( $orderby_parameters, '', $allowed_keys, $binary_keys );
		} else {
			$clauses['orderby'] = '';
		}

		// Add pagination
		$clauses['limits'] = '';
		$offset = absint( $arguments['offset'] );
		$limit = absint( $arguments['limit'] );
		if ( 0 < $offset && 0 < $limit ) {
			$clauses['limits'] = "LIMIT {$offset}, {$limit}";
		} elseif ( 0 < $limit ) {
			$clauses['limits'] = "LIMIT {$limit}";
		} elseif ( 0 < $offset ) {
			$clause_parameters = 0x7FFFFFFF;
			$clauses['limits'] = "LIMIT {$offset}, {$clause_parameters}";
		}

		$clauses = apply_filters( 'mla_get_custom_values_clauses', $clauses );

		// Build the final query
		$query = array( 'SELECT' );
		$query[] = $clauses['fields'];
		$query[] = 'FROM `' . $wpdb->postmeta . '` AS m';
		$query[] = $clauses['join'];
		$query[] = 'WHERE (';
		$query[] = $clauses['where'];
		$query[] = ') GROUP BY m.meta_value';

		$clause_parameters = absint( $arguments['minimum'] );
		if ( 0 < $clause_parameters ) {
			$query[] = "HAVING count >= {$clause_parameters}";
		}

		/*
		 * Unless specifically told to omit the ORDER BY clause or the COUNT,
		 * supply a sort order for the initial/inner query only
		 */
		if ( ! ( $no_orderby || $no_count ) ) {
			$query[] = 'ORDER BY count DESC, m.meta_value ASC';
		}

		// Limit the total number of terms returned
		$number = absint( $arguments['number'] );
		if ( 0 < $number ) {
			$query[] = "LIMIT {$number}";
		}

		// $final_clauses, if present, require an SQL subquery
		$final_clauses = array();

		if ( ! empty( $clauses['orderby'] ) && 'ORDER BY count DESC' != $clauses['orderby'] ) {
			$final_clauses[] = $clauses['orderby'];
		}

		if ( '' !== $clauses['limits'] ) {
			$final_clauses[] = $clauses['limits'];
		}

		// If we're paginating the final results, we need to get an accurate total count first
		if ( ! $no_count && ( 0 < $offset || 0 < $limit ) ) {
			$count_query = 'SELECT COUNT(*) as count FROM (' . join(' ', $query) . ' ) as subQuery';
			$count = $wpdb->get_results( $count_query ); // phpcs:ignore
			$found_rows = $count[0]->count;
		}

		if ( ! empty( $final_clauses ) ) {
			if ( ! $no_count ) {
			    array_unshift($query, 'SELECT * FROM (');
			    $query[] = ') AS subQuery';
			}

			$query = array_merge( $query, $final_clauses );
		}

		$query =  join(' ', $query);
		$values = $wpdb->get_results(	$query ); // phpcs:ignore
		if ( ! isset( $found_rows ) ) {
			$found_rows = $wpdb->num_rows;
		}

		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug query arguments', 'media-library-assistant' ) . '</strong> = ' . var_export( $arguments, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug last_query', 'media-library-assistant' ) . '</strong> = ' . var_export( $wpdb->last_query, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug last_error', 'media-library-assistant' ) . '</strong> = ' . var_export( $wpdb->last_error, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug num_rows', 'media-library-assistant' ) . '</strong> = ' . var_export( $wpdb->num_rows, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug found_rows', 'media-library-assistant' ) . '</strong> = ' . var_export( $found_rows, true ) );
		}

		$values['found_rows'] = $found_rows;
		$values = apply_filters( 'mla_get_custom_values_query_results', $values );
		return $values;
	} // mla_get_custom_values
} // Class MLACustomList
?>