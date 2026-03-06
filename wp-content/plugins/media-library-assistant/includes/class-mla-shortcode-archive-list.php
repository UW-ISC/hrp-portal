<?php
/**
 * Media Library Assistant Archive List Shortcode
 *
 * @package Media Library Assistant
 * @since 3.31
 */

/**
 * Class MLA (Media Library Assistant) Archive List Shortcode implements
 * the [mla_archive_list] shortcode.
 *
 * @package Media Library Assistant
 * @since 3.31
 */
class MLAArchiveList {
	/**
	 * Turn debug collection and display on or off
	 *
	 * @since 3.31
	 *
	 * @var	boolean
	 */
	private static $mla_debug = false;

	/**
	 * These are the default parameters for archive list display
	 *
	 * @since 3.31
	 *
	 * @var	array
	 */
	private static $item_specific_arguments = array(
		'itemtag_id' => '',
		'itemtag_class' => 'archive-list-item',
		'itemtag_attributes' => '',
		'itemtag_value' => '',
		'itemtag_label' => '',

		'mla_link_id' => '',
		'mla_link_class' => '',
		'mla_link_style' => '',
		'mla_rollover_text' => '',
		'mla_link_attributes' => '',
		'mla_link_href' => '',
		'mla_link_text' => '',
		'mla_nolink_text' => 'No archives',
		'mla_link_href' => '',
	);

	/**
	 * Valid mla_output values
	 *
	 * @since 3.31
	 *
	 * @var	array
	 */
	private static $valid_mla_output_values = array( 'flat', 'flat_div', 'ulist', 'olist', 'dropdown', 'array' );

	/**
	 * Valid mla_output pagination values
	 *
	 * @since 3.31
	 *
	 * @var	array
	 */
	private static $valid_mla_output_pagination_values = array( 'previous_link', 'current_link', 'next_link', 'paginate_values', 'next_page', 'previous_page', 'paginate_links' );

	/**
	 * Replaces or removes a query argument, preserving url encoding
	 *
	 * @since 3.31
	 *
	 * @param string argument name
	 * @param mixed argument value (string) or false to remove argument
	 * @param string url
	 *
	 * @return string url with argument replaced
	 */
	private static function _replace_query_parameter( $key, $value, $url ) {
		$parts = wp_parse_url( $url );
		if ( empty( $parts['path'] ) ) {
			$parts['path'] = '';
		}

		// Fragments must come at the end of the URL and be preceded by a #
		if ( ! empty( $parts['fragment'] ) ) {
			$parts['fragment'] = '#' . $parts['fragment'];
		} else {
			$parts['fragment'] = '';
		}

		$clean_query = array();
		if ( empty( $parts['query'] ) ) {
			// No existing query arguments; create query if requested
			if ( false !== $value ) {
				$clean_query[ $key ] = $value;
			}
		} else {
			parse_str( $parts['query'], $query );

			$add_it = true;
			foreach ( $query as $query_key => $query_value ) {
				// Query argument names cannot have URL special characters
				if ( $query_key === urldecode( $query_key ) ) {
					if ( $key === $query_key ) {
						$add_it = false;
						// Deleting argument?
						if ( false === $value ) {
							continue;
						}

						$query_value = $value;
					}

					$clean_query[ $query_key ] = $query_value;
				}
			}

			if ( $add_it && ( false !== $value ) ) {
				$clean_query[ $key ] = $value;
			}
		}

		$clean_query = urlencode_deep( $clean_query );
		$clean_query = build_query( $clean_query );

		// Query arguments must come before the fragment, if any
		if ( ! empty( $clean_query ) ) {
			return $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . '?' . $clean_query . $parts['fragment'];
		} else {
			return $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . $parts['fragment'];
		}
	}

	/**
	 * Handles 'paginate_values' output type
	 *
	 * @since 3.31
	 *
	 * @uses array	self::$archive_list_atttr Shortcode parameters, explicit, used by reference
	 * @uses array	self::$archive_list_items List item objects, used by reference
	 *
	 * @param array $markup_values Style and list-level substitution parameters, by reference
	 * @param integer $target_key Index in self::$archive_list_items of the current archive item
	 * @param string $list Shortcode output text, by reference
	 *
	 * @return boolean false; appends to &$list
	 */
	private static function _paginate_values( $markup_values, $target_key, &$list ) {
		$items = &self::$archive_list_items;
		$attr = &self::$archive_list_attr;
		$show_all = $prev_next = false;

		if ( ! empty( $markup_values['mla_output_qualifier'] ) ) {
				switch ( $markup_values['mla_output_qualifier'] ) {
				case 'show_all':
					$show_all = true;
					break;
				case 'prev_next':
					$prev_next = true;
			}
		}

		if ( ( 1 > count( $items ) ) ) {
			if ( ! empty( $markup_values['option_none_label'] ) ) {
				$list = self::_process_shortcode_parameter( $markup_values['option_none_label'], $markup_values );
			}

			return false;
		}

		$last_key = count( $items ) - 1;
		$end_size = absint( $markup_values['mla_end_size'] );
		$mid_size = absint( $markup_values['mla_mid_size'] );
		$mla_archive_parameter = $markup_values['mla_archive_parameter'];

		// Build the array of page links
		$page_links = array();
		$dots = false;

		if ( $prev_next && ( 0 < $target_key ) ) {
			$item_values = array_merge( $markup_values, (array) $items[ $target_key - 1 ] );
			$item_values = apply_filters( 'mla_archive_list_item_values', $item_values, 'paginate_prev' );

			// these will add to the default classes
			$new_class = ( ! empty( $item_values['mla_link_class'] ) ) ? ' ' . sanitize_html_class( self::_process_shortcode_parameter( $item_values['mla_link_class'], $item_values ) ) : '';

			$new_attributes = ( ! empty( $item_values['mla_link_attributes'] ) ) ? MLAShortcode_Support::mla_esc_attr( self::_process_shortcode_parameter( $item_values['mla_link_attributes'], $item_values ) ) . ' ' : '';

			$new_base =  ( ! empty( $item_values['mla_link_href'] ) ) ? esc_url( self::_process_shortcode_parameter( $item_values['mla_link_href'], $item_values ) ) : $item_values['page_url'];

			$new_title = ( ! empty( $item_values['mla_rollover_text'] ) ) ? 'title="' . esc_attr( self::_process_shortcode_parameter( $item_values['mla_rollover_text'], $item_values ) ) . '" ' : '';

			if ( $item_values['append_current_item'] ) {
				$new_url = self::_replace_query_parameter( $mla_archive_parameter, $item_values['current_value'], $new_base );
			} else {
				$new_url = $new_base;
			}

			$prev_text = ( ! empty( $item_values['mla_prev_text'] ) ) ? esc_attr( self::_process_shortcode_parameter( $item_values['mla_prev_text'], $item_values ) ) : '&laquo; ' . __( 'Previous', 'media-library-assistant' );
			$page_links[] = sprintf( '<a class="prev paginate-archive%1$s" %2$s%3$shref="%4$s">%5$s</a>',
				/* %1$s */ $new_class,
				/* %2$s */ $new_attributes,
				/* %3$s */ $new_title,
				/* %4$s */ $new_url,
				/* %5$s */ $prev_text );
		}

		foreach ( $items as $key => $item ) {
			$item_values = array_merge( $markup_values, (array) $item );
			$item_values = apply_filters( 'mla_archive_list_item_values', $item_values, 'paginate_item' );

			// these will add to the default classes
			$new_class = ( ! empty( $item_values['mla_link_class'] ) ) ? ' ' . sanitize_html_class( self::_process_shortcode_parameter( $item_values['mla_link_class'], $item_values ) ) : '';

			$new_attributes = ( ! empty( $item_values['mla_link_attributes'] ) ) ? MLAShortcode_Support::mla_esc_attr( self::_process_shortcode_parameter( $item_values['mla_link_attributes'], $item_values ) ) . ' ' : '';

			$new_base =  ( ! empty( $item_values['mla_link_href'] ) ) ? esc_url( self::_process_shortcode_parameter( $item_values['mla_link_href'], $item_values ) ) : $item_values['page_url'];

			$new_title = ( ! empty( $item_values['mla_rollover_text'] ) ) ? 'title="' . esc_attr( self::_process_shortcode_parameter( $item_values['mla_rollover_text'], $item_values ) ) . '" ' : '';

			if ( $key === $target_key ) {
				// build current item span
				$page_links[] = sprintf( '<span class="paginate-archive current%1$s">%2$s</span>',
					/* %1$s */ $new_class,
					/* %2$s */ $item_values['current_label'] );
				$dots = true;
			} else {
				if ( $show_all || ( $key < $end_size || ( $key >= $target_key - $mid_size && $key <= $target_key + $mid_size ) || $key > $last_key - $end_size ) ) {
					// build link
					if ( $item_values['append_current_item'] ) {
						$new_url = self::_replace_query_parameter( $mla_archive_parameter, $item_values['current_value'], $new_base );
					} else {
						$new_url = $new_base;
					}

					$page_links[] = sprintf( '<a class="paginate-archive%1$s" %2$s%3$shref="%4$s">%5$s</a>',
						/* %1$s */ $new_class,
						/* %2$s */ $new_attributes,
						/* %3$s */ $new_title,
						/* %4$s */ $new_url,
						/* %5$s */ $item_values['current_label'] );
					$dots = true;
				} elseif ( $dots && ! $show_all ) {
					// build link
					$page_links[] = sprintf( '<span class="paginate-archive dots%1$s">&hellip;</span>',
						/* %1$s */ $new_class );
					$dots = false;
				}
			} // ! current
		} // foreach $item

		if ( $prev_next && ( 0 <= $target_key ) && ( $target_key < $last_key ) ) {
			// build next link
			$item_values = array_merge( $markup_values, (array) $items[ $target_key + 1 ] );
			$item_values = apply_filters( 'mla_archive_list_item_values', $item_values, 'paginate_next' );

			// these will add to the default classes
			$new_class = ( ! empty( $item_values['mla_link_class'] ) ) ? ' ' . sanitize_html_class( self::_process_shortcode_parameter( $item_values['mla_link_class'], $item_values ) ) : '';

			$new_attributes = ( ! empty( $item_values['mla_link_attributes'] ) ) ? MLAShortcode_Support::mla_esc_attr( self::_process_shortcode_parameter( $item_values['mla_link_attributes'], $item_values ) ) . ' ' : '';

			$new_base =  ( ! empty( $item_values['mla_link_href'] ) ) ? esc_url( self::_process_shortcode_parameter( $item_values['mla_link_href'], $item_values ) ) : $item_values['page_url'];

			$new_title = ( ! empty( $item_values['mla_rollover_text'] ) ) ? 'title="' . esc_attr( self::_process_shortcode_parameter( $item_values['mla_rollover_text'], $item_values ) ) . '" ' : '';
			if ( $item_values['append_current_item'] ) {
				$new_url = self::_replace_query_parameter( $mla_archive_parameter, $item_values['current_value'], $new_base );
			} else {
				$new_url = $new_base;
			}

			$next_text = ( ! empty( $item_values['mla_next_text'] ) ) ? esc_attr( self::_process_shortcode_parameter( $item_values['mla_next_text'], $item_values ) ) : __( 'Next', 'media-library-assistant' ) . ' &raquo;';
			$page_links[] = sprintf( '<a class="next paginate-archive%1$s" %2$s%3$shref="%4$s">%5$s</a>',
				/* %1$s */ $new_class,
				/* %2$s */ $new_attributes,
				/* %3$s */ $new_title,
				/* %4$s */ $new_url,
				/* %5$s */ $next_text );
		}

		switch ( strtolower( trim( $item_values['mla_paginate_type'] ) ) ) {
			case 'list':
				$list .= "<ul class='page-numbers'>\n\t<li>";
				$list .= join("</li>\n\t<li>", $page_links);
				$list .= "</li>\n</ul>\n";
				break;
			case 'plain':
			default:
				$list .= join("\n", $page_links);
		} // mla_paginate_type

		return false;
	}

	/**
	 * Handles brace/bracket escaping and parses template for a shortcode parameter
	 *
	 * @since 3.31
	 *
	 * @param string raw shortcode parameter, e.g., "text {+field+} {brackets} \\{braces\\}"
	 * @param array  template substitution values, e.g., ('instance' => '1', ...  )
	 *
	 * @return string parameter with brackets, braces, substitution parameters and templates processed
	 */
	private static function _process_shortcode_parameter( $text, $markup_values ) {
		$new_text = str_replace( '{\+', '\[\+', str_replace( '+\}', '+\\\\]', $text ) );
		$new_text = str_replace( '{', '[', str_replace( '}', ']', $new_text ) );
		$new_text = str_replace( '\[', '{', str_replace( '\]', '}', $new_text ) );
		return MLAData::mla_parse_template( $new_text, $markup_values );
	}

	/**
	 * Compose an mla_archive_list
	 *
	 * Adds shortcode output text and item-specific links to arrays passed by reference.
	 *
	 * @since 3.31
	 *
	 * @uses array	self::$archive_list_attr Shortcode parameters, explicit, used by reference
	 * @uses array	self::$archive_list_items List item objects, used by reference
	 *
	 * @param string $list Shortcode output text, by reference
	 * @param array $links Term-specific links for flat/array output, by reference
	 * @param array $markup_values Style and list-level substitution parameters, by reference
	 *
	 * @return boolean True if the list contains the "current_item"; appends to &$list, &$links
	 */
	private static function _compose_archive_list( &$list, &$links, &$markup_values ) {
		static $page_content = NULL; // for mla_debug_add

		$items = &self::$archive_list_items;
		$attr = &self::$archive_list_attr;
		$arguments = self::$archive_list_arguments;
		$found_rows = $items['found_rows'];
		unset( $items['found_rows'] );

		$mla_archive_parameter = $markup_values['mla_archive_parameter'];
		$is_flat = 'flat' === $markup_values['mla_output'];
		$is_flat_div = $is_flat && ( 'div' === $markup_values['mla_output_qualifier'] );
		$is_dropdown = 'dropdown' === $markup_values['mla_output'];
		$is_list = in_array( $markup_values['mla_output'], array( 'olist', 'ulist' ) );
		$is_pagination = in_array( $markup_values['mla_output'], self::$valid_mla_output_pagination_values );

		// Handle an empty, hidden archive
		if ( ( 0 === $found_rows ) && $markup_values['hide_if_empty'] ) {
			$option_none_label = self::_process_shortcode_parameter( $markup_values['option_none_label'], $markup_values );

			if ( $is_list || $is_dropdown ) {
				$list .= $option_none_label;
			} else {
				if ( ! empty( $option_none_label ) ) {
					$links[] = $option_none_label;
				}
			}

			return false;
		} // empty archive

		// Load the appropriate templates
		if ( $is_list || $is_dropdown || $is_flat_div ) {
			$open_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'archive-list', 'markup', 'open' );
			$item_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'archive-list', 'markup', 'item' );
			$close_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'archive-list', 'markup', 'close' );

			if ( empty( $open_template ) ) {
				$open_template = '';
			}

			if ( empty( $item_template ) ) {
				$item_template = '';
			}

			if ( empty( $close_template ) ) {
				$close_template = '';
			}

			// Look for gallery-level markup substitution parameters
			$new_text = $open_template . $close_template;
			$markup_values = MLAData::mla_expand_field_level_parameters( $new_text, $attr, $markup_values );

			$markup_values = apply_filters( 'mla_archive_list_open_values', $markup_values );
			$open_template = apply_filters( 'mla_archive_list_open_template', $open_template );
			if ( ! empty( $open_template ) ) {
				$open_content = MLAData::mla_parse_template( $open_template, $markup_values );
				$list .= apply_filters( 'mla_archive_list_open_parse', $open_content, $open_template, $markup_values );
			}
		} // is_list || is_dropdown

		// Find delimiter for currentlink, currentlink_url
		if ( strpos( $markup_values['page_url'], '?' ) ) {
			$current_item_delimiter = '&';
		} else {
			$current_item_delimiter = '?';
		}

		// Item values vary by archive type; make sure all elements are defined
		$item_default_values = array_merge( $markup_values, array(
			'current_value' => '',
			'current_label_short' => '',
			'current_label_long' => '',
			'current_label' => '',
			'items' => 0,
			'year' => '',
			'month' => '',
			'week' => '',
			'day' => '',
			'm' => '',
			'yyyymmdd'  => '',
			'month_long' => '',
			'month_short' => '',
			'week_start_raw' => 0,
			'week_start_short' => '',
			'week_start' => '',
			'week_end_raw' => 0,
			'week_end_short' => '',
			'week_end' => '',

			'item_id' => '',
			'item_class' => '',
			'item_attributes' => '',
			'item_selected' => '',
			'item_label' => '',
			'scaled_count' => 0,
			'font_size' => '',

			'item_link_id' => '',
			'item_link_class' => '',
			'item_link_rollover' => '',
			'item_link_style' => '',
			'item_link_attributes' => '',
			'item_link_text' => '',

			'thelink' => '',
			'currentlink' => '',
			'viewlink' => '',
			'link_url' => '',
			'currentlink_url' => '',
			'viewlink_url' => '',
		) );

		$has_active = false;

		// mla_nolink_text is used for pure pagination values
		if ( ! empty( $item_default_values['option_none_label'] ) ) {
			$arguments['mla_nolink_text'] = esc_attr( self::_process_shortcode_parameter( $item_default_values['option_none_label'], $item_default_values ) );
		}

		// Handle an empty, visible archive
		if ( 0 === $found_rows ) {
			// Apply the Display Content parameters
			$attributes = array();

			if ( ! empty( $item_default_values['option_none_value'] ) ) {
				$item_default_values['current_value'] = esc_attr( self::_process_shortcode_parameter( $item_default_values['option_none_value'], $item_default_values ) );
			} else {
				$item_default_values['current_value'] = 'no-archives';
			}

			if ( ! empty( $item_default_values['option_none_label'] ) ) {
				$item_default_values['item_label'] = esc_attr( self::_process_shortcode_parameter( $item_default_values['option_none_label'], $item_default_values ) );
			} else {
				$item_default_values['item_label'] = 'No archives';
			}

			if ( ! empty( $item_default_values['itemtag_id'] ) ) {
				$item_default_values['item_id'] = esc_attr( self::_process_shortcode_parameter( $item_default_values['itemtag_id'], $item_default_values ) );
				$attributes[] = 'id="' . $item_default_values['item_id'] . '"';
			}

			if ( ! empty( $item_default_values['item_selected'] ) ) {
				$item_default_values['item_class'] .= ' ' . sanitize_html_class( $item_default_values['current_archive_class'] );
			}

			if ( ! empty( $item_default_values['itemtag_class'] ) ) {
				$item_default_values['item_class'] .= ' ' .  sanitize_html_class( self::_process_shortcode_parameter( $item_default_values['itemtag_class'], $item_default_values ) );
			}

			$item_default_values['item_class'] = trim ( $item_default_values['item_class'] );

			if ( ! empty( $item_default_values['item_class'] ) ) {
				$attributes[] = 'class="' . $item_default_values['item_class'] . '"';
			}

			if ( ! empty( $item_default_values['itemtag_attributes'] ) ) {
				$attributes[] = MLAShortcode_Support::mla_esc_attr( self::_process_shortcode_parameter( $item_default_values['itemtag_attributes'], $item_default_values ) );
			}

			if ( ! empty( $attributes ) ) {
				$item_default_values['item_attributes'] = implode( ' ', $attributes ) . ' ';
			}

			$item_default_values['thelink'] = $item_default_values['item_label'];

			if ( $is_list || $is_dropdown ) {
				$item_values = apply_filters( 'mla_archive_list_item_values', $item_default_values, 'empty_visible' );
				$item_template = apply_filters( 'mla_archive_list_item_template', $item_template );
				$item_content = MLAData::mla_parse_template( $item_template, $item_values );
				$list .= apply_filters( 'mla_archive_list_item_parse', $item_content, $item_template, $item_values );

				$markup_values = apply_filters( 'mla_archive_list_close_values', $markup_values );
				$close_template = apply_filters( 'mla_archive_list_close_template', $close_template );
				$close_content = MLAData::mla_parse_template( $close_template, $markup_values );
				$list .= apply_filters( 'mla_archive_list_close_parse', $close_content, $close_template, $markup_values );
			} else {
				$links[] = $item_default_values['item_label'];
				$list .= join( wp_kses( $markup_values['separator'], 'post' ), $links );
			}

			if ( self::$mla_debug ) {
				MLACore::mla_debug_add( __LINE__ . " _compose_archive_list() empty archive, item_default_values = " . var_export( $item_default_values, true ) );
				MLACore::mla_debug_add( __LINE__ . " _compose_archive_list() empty archive, list = " . var_export( $list, true ) );
				MLACore::mla_debug_add( __LINE__ . " _compose_archive_list() empty archive, links = " . var_export( $links, true ) );
			}

			return false;
		} else { // Empty, visible archive

			// Add the "option all" element, if specified
			if ( ! empty( $item_default_values['option_all_label'] ) ) {
				// Apply the Display Content parameters
				$attributes = array();

				$item_default_values['item_label'] = esc_attr( self::_process_shortcode_parameter( $item_default_values['option_all_label'], $item_default_values ) );

				if ( ! empty( $item_default_values['option_all_value'] ) ) {
					$item_default_values['current_value'] = esc_attr( self::_process_shortcode_parameter( $item_default_values['option_all_value'], $item_default_values ) );
				} else {
					$item_default_values['current_value'] = '';
				}

				if ( ! empty( $item_default_values['itemtag_id'] ) ) {
					$item_default_values['item_id'] = esc_attr( self::_process_shortcode_parameter( $item_default_values['itemtag_id'], $item_default_values ) );
					$attributes[] = 'id="' . $item_default_values['item_id'] . '"';
				}

				if ( ! empty( $item_default_values['item_selected'] ) ) {
					$item_default_values['item_class'] .= ' ' . sanitize_html_class( $item_default_values['current_archive_class'] );
				}

				if ( ! empty( $item_default_values['itemtag_class'] ) ) {
					$item_default_values['item_class'] .= ' ' .  sanitize_html_class( self::_process_shortcode_parameter( $item_default_values['itemtag_class'], $item_default_values ) );
				}

				$item_default_values['item_class'] = trim ( $item_default_values['item_class'] );

				if ( ! empty( $item_default_values['item_class'] ) ) {
					$attributes[] = 'class="' . $item_default_values['item_class'] . '"';
				}

				if ( ! empty( $item_default_values['itemtag_attributes'] ) ) {
					$attributes[] = MLAShortcode_Support::mla_esc_attr( self::_process_shortcode_parameter( $item_default_values['itemtag_attributes'], $item_default_values ) );
				}

				if ( ! empty( $attributes ) ) {
					$item_default_values['item_attributes'] = implode( ' ', $attributes ) . ' ';
				}

				$item_default_values['thelink'] = $item_default_values['item_label'];

				$item_values = apply_filters( 'mla_archive_list_item_values', $item_default_values, 'option_all' );
				if ( $is_list || $is_dropdown ) {
					$item_template = apply_filters( 'mla_archive_list_item_template', $item_template );
					$item_content = MLAData::mla_parse_template( $item_template, $item_values );
					$list .= apply_filters( 'mla_archive_list_item_parse', $item_content, $item_template, $item_values );
				} else {
					$links[] = $item_values['item_label'];
				} 
			} // Option all value
		} // non-empty, visible archive

		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . " _compose_archive_list() markup_values[ $mla_archive_parameter ] = " . var_export( $markup_values[ $mla_archive_parameter ], true ) );
		}

		// Do this once per page load only through MLA Reporting
		if ( NULL === $page_content ) {
			$page_content = $item_default_values['page_content'];

			if ( self::$mla_debug ) {
				MLACore::mla_debug_add( __LINE__ . " _compose_archive_list() page content = " . var_export( $page_content, true ) );
			}
		}

		// Handle true pagination outputs
		if ( in_array( $markup_values['mla_output'], array( 'previous_page', 'next_page', 'paginate_links' ) ) ) {
			// posts_per_page and numberposts are used in mla_process_pagination_output_types
			if ( isset( $attr['limit'] ) ) {
				$arguments['posts_per_page'] = $attr['limit'];
				$arguments['numberposts'] = $attr['limit'];
			} else {
				$arguments['posts_per_page'] = 0;
				$arguments['numberposts'] = 0;
			}

			$output_parameters = array( $arguments['mla_output'], $arguments['mla_output_qualifier'] );
			$pagination_result = MLAShortcode_Support::mla_process_pagination_output_types( $output_parameters, $item_default_values, $arguments, $attr, $found_rows );

			if ( false !== $pagination_result ) {
				$list .= $pagination_result;
			}

			return false;
		}

		// Expand the items with values needed for all output types including $is_pagination types
		foreach ( $items as $key => $item ) {
			$item->scaled_count = apply_filters( 'mla_archive_list_scale', round(log10($item->items + 1) * 100), $attr, $arguments, $item );
			$item->font_size = str_replace( ',', '.', ( $markup_values['smallest'] + ( ( $item->scaled_count - $markup_values['min_scaled_count'] ) * $markup_values['font_step'] ) ) );

			$month_stamp = 0;
			if ( ! empty( $item->month ) ) {
				$item->m = sprintf( '%1$04d%2$02d', (integer) $item->year, (integer) $item->month );

				if ( ! empty( $item->day ) ) {
					$item->yyyymmdd = sprintf( '%1$04d-%2$02d-%3$02d', (integer) $item->year, (integer) $item->month, (integer) $item->day );
					$month_stamp = strtotime( $item->yyyymmdd );
				} else {
					$month_stamp = strtotime( $item->m . '01' );
				}
			} elseif ( ! empty( $item->week_start_short ) ) {
				$month_stamp = strtotime( $item->week_start_short );
			}

			if ( $month_stamp ) {
				$item->month_long = date( 'F', $month_stamp );
				$item->month_short = date( 'M', $month_stamp );
			}

			// Compute the current_value and current_labels based on the archive type
			switch ( $markup_values['archive_type'] ) {
				case 'daily':
					$item->current_value = sprintf( 'D(%1$04d%2$02d%3$02d)', (integer) $item->year, (integer) $item->month, (integer) $item->day );
					$item->current_label_short = sprintf( '%1$04d/%2$02d/%3$02d', (integer) $item->year, (integer) $item->month, (integer) $item->day );
					$item->current_label_long = sprintf( '%1$s %2$02d, %3$04d', $item->month_long, (integer) $item->day, (integer) $item->year );
					$item->viewlink_url = get_day_link( (integer) $item->year, (integer) $item->month, (integer) $item->day );
					break;
				case 'weekly':
					$item->current_value = sprintf( 'W(%1$04d%2$02d)', (integer) $item->year, (integer) $item->week );
					$item->current_label_short = $item->week_start_short;
					$item->current_label_long = $item->week_start;
					$item->viewlink_url = add_query_arg(
						array(
							'm' => $item->year,
							'w' => $item->week,
						),
						home_url( '/' )
					);
					break;
				case 'monthly':
					$item->current_value = sprintf( 'M(%1$04d%2$02d)', (integer) $item->year, (integer) $item->month );
					$item->current_label_short = sprintf( '%1$s %2$s', $item->month_short, $item->year );
					$item->current_label_long = sprintf( '%1$s %2$s', $item->month_long, $item->year );
					$item->viewlink_url = get_month_link( (integer) $item->year, (integer) $item->month );
					break;
				case 'yearly':
				default:
					$item->current_value = sprintf( 'Y(%1$04d)', (integer) $item->year );
					$item->current_label_short = sprintf( '%1$04d', (integer) $item->year );
					$item->current_label_long = sprintf( '%1$04d', (integer) $item->year );
					$item->viewlink_url = get_year_link( (integer) $item->year );
			}

			// Add the archive source to the current value
			$current_args = ',' . $item->current_value;
			if ( 'custom' === $markup_values['archive_source'] ) {
				$item->current_value = 'custom:' . $markup_values['archive_key'] . $current_args;
			} else {
				$item->current_value = $markup_values['archive_source'] . $current_args;
			}

			$item->current_label = ( 'short' === $markup_values['archive_label'] ) ? $item->current_label_short : $item->current_label_long;
			$item->currentlink_url = sprintf( '%1$s%2$s%3$s=%4$s', $markup_values['page_url'], $current_item_delimiter, $mla_archive_parameter, urlencode( $item->current_value ) );
			$items[ $key ] = $item;
		}

		// For link outputs, discard all of the items except the appropriate choice
		if ( $is_pagination ) {
			// Remove the style template from the $list
			$list = ''; 
			$link_type = $item_default_values['mla_output'];
			$is_wrap = in_array( $item_default_values['mla_output_qualifier'], array( 'wrap', 'always_wrap' ) );

			$target_value = $item_default_values[ $mla_archive_parameter ];
			$target_key = NULL;
			if ( ! empty( $target_value ) ) {
				foreach ( $items as $key => $item ) {
					if ( $item->current_value === $target_value ) {
						$target_key = $key;
						break;
					}
				} // foreach $item
			}

			// Default to empty/option_none_label
			if ( NULL === $target_key ) {
				$target_key = -2;
			}

			switch ( $link_type ) {
				case 'paginate_values':
					return self::_paginate_values( $item_default_values, $target_key, $list, $found_rows );
				case 'previous_link':
					$target_key = $target_key - 1;
					break;
				case 'next_link':
					$target_key = $target_key + 1;
					break;
				case 'current_link':
				default:
					// no change
			} // link_type

			$item = NULL;
			if ( isset( $items[ $target_key ] ) ) {
				$item = $items[ $target_key ];
			} elseif ( $is_wrap && ( ! empty( $target_value ) || ( 'always_wrap' === $item_default_values['mla_output_qualifier'] ) ) ) {
				switch ( $link_type ) {
					case 'previous_link':
						$item = array_pop( $items );
						break;
					case 'next_link':
						$item = array_shift( $items );
				} // link_type
			} // is_wrap

			if ( ! empty( $item ) ) {
				$items = array( $item );
			} elseif ( ! empty( $item_default_values['option_none_label'] ) ) {
				$list = self::_process_shortcode_parameter( $item_default_values['option_none_label'], $item_default_values );
				return false;
			} else {
				return false;
			}
		}// $is_pagination

		foreach ( $items as $key => $item ) {
			// fill in item-specific elements
			$item_values = array_merge( $item_default_values, (array) $item );

			if ( ! empty( $item_default_values['itemtag_id'] ) ) {
				$item_values['itemtag_id'] = esc_attr( $item_default_values['itemtag_id'] );
			} else {
				$item_values['itemtag_id'] = esc_attr( $item_values['listtag_id'] . '-' . $item_values['current_value'] );
			}

			if ( ! empty( $item_values[ $mla_archive_parameter ] ) ) {
				if ( $item_values['current_value'] === $item_values[ $mla_archive_parameter ] ) {
					$has_active = true;
					$item_values['item_selected'] = 'selected=selected';
				}
			}

			// Add item_specific field-level substitution parameters
			$new_text = isset( $item_template ) ? $item_template : '';
			foreach( self::$item_specific_arguments as $index => $value ) {
				$new_text .= str_replace( '{+', '[+', str_replace( '+}', '+]', $item_values[ $index ] ) );
			}

			$item_values = MLAData::mla_expand_field_level_parameters( $new_text, $attr, $item_values );

			// Apply the Display Content parameters
			$attributes = array();

			if ( ! empty( $item_values['itemtag_id'] ) ) {
				$item_values['item_id'] = esc_attr( self::_process_shortcode_parameter( $item_values['itemtag_id'], $item_values ) );
				$attributes[] = 'id="' . $item_values['item_id'] . '"';
			}

			if ( ! empty( $item_values['item_selected'] ) ) {
				$item_values['item_class'] .= ' ' . sanitize_html_class( $item_values['current_archive_class'] );
			}

			if ( ! empty( $item_values['itemtag_class'] ) ) {
				$item_values['item_class'] .= ' ' .  sanitize_html_class( self::_process_shortcode_parameter( $item_values['itemtag_class'], $item_values ) );
			}

			$item_values['item_class'] = trim ( $item_values['item_class'] );

			if ( ! empty( $item_values['item_class'] ) ) {
				$attributes[] = 'class="' . $item_values['item_class'] . '"';
			}

			if ( ! empty( $item_values['itemtag_attributes'] ) ) {
				$attributes[] = MLAShortcode_Support::mla_esc_attr( self::_process_shortcode_parameter( $item_values['itemtag_attributes'], $item_values ) );
			}

			if ( ! empty( $attributes ) ) {
				$item_values['item_attributes'] = implode( ' ', $attributes ) . ' ';
			}

			if ( ! empty( $item_values['itemtag_value'] ) ) {
				$item_values['current_value'] = esc_attr( self::_process_shortcode_parameter( $item_values['itemtag_value'], $item_values ) );
			}

			if ( ! empty( $item_values['itemtag_label'] ) ) {
				$item_values['item_label'] = esc_attr( self::_process_shortcode_parameter( $item_values['itemtag_label'], $item_values ) );
			} else {
				$item_values['item_label'] = $item_values['current_label'];
			}

			// Build the link components
			$attributes = array();

			if ( ! empty( $item_values['mla_link_id'] ) ) {
				$item_values['item_link_id'] = esc_attr( self::_process_shortcode_parameter( $item_values['mla_link_id'], $item_values ) );
				$attributes[] = 'id="' . $item_values['item_link_id'] . '"';
			} elseif ( 'flat' === $item_values['mla_output'] && ! empty( $item_values['item_id'] ) ) {
				$attributes[] = 'id="' . $item_values['item_id'] . '"';
			}

			if ( ! empty( $item_values['mla_link_class'] ) ) {
				$item_values['item_link_class'] = sanitize_html_class( self::_process_shortcode_parameter( $item_values['mla_link_class'], $item_values ) );
				$attributes[] = 'class="' . $item_values['item_link_class'] . '"';
			} elseif ( 'flat' === $item_values['mla_output'] && ! empty( $item_values['item_class'] ) ) {
				$attributes[] = 'class="' . $item_values['item_class'] . '"';
			}

			if ( ! empty( $item_values['mla_rollover_text'] ) ) {
				$item_values['item_link_rollover'] = esc_attr( self::_process_shortcode_parameter( $item_values['mla_rollover_text'], $item_values ) );
				$attributes[] = 'title="' . $item_values['item_link_rollover'] . '"';
			}

			if ( ! empty( $arguments['mla_link_style'] ) ) {
				$item_values['item_link_style'] = esc_attr( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_link_style'], $item_values ) );
			} else {
				$item_values['item_link_style'] = 'font-size: ' . $item_values['font_size'] . $item_values['unit'];
			}

			$attributes[] = 'style="' . $item_values['item_link_style'] . '"';

			if ( ! empty( $item_values['mla_link_attributes'] ) ) {
				$attributes[] = MLAShortcode_Support::mla_esc_attr( self::_process_shortcode_parameter( $item_values['mla_link_attributes'], $item_values ) );
			}

			if ( ! empty( $attributes ) ) {
				$item_values['item_link_attributes'] = implode( ' ', $attributes ) . ' ';
			}

			if ( ! empty( $item_values['mla_link_text'] ) ) {
				$item_values['item_link_text'] = esc_attr( self::_process_shortcode_parameter( $item_values['mla_link_text'], $item_values ) );
			} else {
				$item_values['item_link_text'] = $item_values['current_label'];
			}

			if ( ! empty( $item_values['show_count'] ) && ( 'true' === strtolower( $item_values['show_count'] ) ) ) {
				// Ignore option-all
				if ( -1 !== $item_values['items'] ) {
					$item_values['item_label'] .= ' (' . $item_values['items'] . ')';
					$item_values['item_link_text'] .= ' (' . $item_values['items'] . ')';
				}
			}

			if ( ! empty( $item_values['mla_link_href'] ) ) {
				$item_values['link_url'] = esc_url( self::_process_shortcode_parameter( $item_values['mla_link_href'], $item_values ) );
			}

			// currentlink, viewlink and thelink
			$item_values['currentlink'] = sprintf( '<a %1$shref="%2$s">%3$s</a>', $item_values['item_link_attributes'], $item_values['currentlink_url'], $item_values['item_link_text'] );
			$item_values['viewlink'] = sprintf( '<a %1$shref="%2$s">%3$s</a>', $item_values['item_link_attributes'], $item_values['viewlink_url'], $item_values['item_link_text'] );

			if ( ! empty( $item_values['link_url'] ) ) {
				$item_values['thelink'] = sprintf( '<a %1$shref="%2$s">%3$s</a>', $item_values['item_link_attributes'], $item_values['link_url'], $item_values['item_link_text'] );
			} elseif ( 'current' === $item_values['link'] ) {
				$item_values['link_url'] = $item_values['currentlink_url'];
				$item_values['thelink'] = $item_values['currentlink'];
			} elseif ( 'view' === $item_values['link'] ) {
				$item_values['link_url'] = $item_values['viewlink_url'];
				$item_values['thelink'] = $item_values['viewlink'];
			} elseif ( 'span' === $item_values['link'] ) {
				$item_values['thelink'] = sprintf( '<span %1$s>%2$s</span>', $item_values['item_link_attributes'], $item_values['item_link_text'] );
			} else {
				$item_values['thelink'] = $item_values['mla_link_text'];
			}

			// Page content has already been logged and cached
			$item_values['page_content'] = '';
			//MLACore::mla_debug_add( __LINE__ . " _compose_archive_list() item_values = " . var_export( $item_values, true ) );
			$item_values['page_content'] = $page_content;

			$item_values = apply_filters( 'mla_archive_list_item_values', $item_values, 'list_item' );
			if ( $is_list || $is_dropdown ) {
				$item_template = apply_filters( 'mla_archive_list_item_template', $item_template );
				$item_content = MLAData::mla_parse_template( $item_template, $item_values );
				$list .= apply_filters( 'mla_archive_list_item_parse', $item_content, $item_template, $item_values );
			} else {
				$links[] = $item_values['thelink'];
			} 
		} // foreach item

		if ( $is_list || $is_dropdown || $is_flat_div ) {
			if ( $is_flat_div ) {
				$list .= join( wp_kses( $markup_values['separator'], 'post' ), $links );
			}


			$markup_values = apply_filters( 'mla_archive_list_close_values', $markup_values );
			$close_template = apply_filters( 'mla_archive_list_close_template', $close_template );
			$close_content = MLAData::mla_parse_template( $close_template, $markup_values );
			$list .= apply_filters( 'mla_archive_list_close_parse', $close_content, $close_template, $markup_values );
		} else {
			switch ( $markup_values['mla_output'] ) {
			case 'array' :
				$list =& $links;
				break;
			case 'flat' :
			default :
				$list .= join( wp_kses( $markup_values['separator'], 'post' ), $links );
				break;
			} // switch format
		}

		if ( self::$mla_debug ) {
			if ( 'true' === $markup_values['mla_debug'] ) {
				MLACore::mla_debug_add( __LINE__ . " _compose_archive_list() list = " . var_export( esc_html( $list ), true ) );
			} else {
				MLACore::mla_debug_add( __LINE__ . " _compose_archive_list() list = " . var_export( $list, true ) );
			}
		}

		return $has_active;
	} // _compose_archive_list

	/**
	 * Explicit Shortcode Attributes for mla_archive_list
	 *
	 * @since 3.31
	 *
	 * @var	array
	 */
	private static $archive_list_attr = NULL;

	/**
	 * Shortcode Arguments for mla_archive_list
	 *
	 * @since 3.31
	 *
	 * @var	array
	 */
	private static $archive_list_arguments = NULL;

	/**
	 * Query results for mla_archive_list
	 *
	 * @since 3.31
	 *
	 * @var	array
	 * 
	 * yearly => 
	 * (object) array(
	 *  'year' => '2019',
	 *  'items' => '2',
	 * ),
	 * 
	 * monthly => 
	 * (object) array(
	 *  'year' => '2019',
	 *  'month' => '9',
	 *  'items' => '2',
	 * ),
	 * 
	 * weekly => 
	 * (object) array(
	 *  'year' => '2019',
	 *  'week' => '38',
	 *  'yyyymmdd' => '2019-09-21',
	 *  'items' => '2',
	 *  'week_start_raw' => 1568592000,
	 *  'week_start_short' => '2019-09-16',
	 *  'week_start' => 'September 16, 2019',
	 *  'week_end_raw' => 1569196799,
	 *  'week_end_short' => '2019-09-22',
	 *  'week_end' => 'September 22, 2019',
	 * ),
	 * 
	 * daily => 
	 * (object) array(
	 *  'year' => '2019',
	 *  'month' => '9',
	 *  'day' => '21',
	 *  'items' => '2',
	 * ),
	 * 
	 */
	private static $archive_list_items = NULL;

/**
	 * Filters all query clauses at once, for convenience.
	 *
	 * For use by caching plugins.
	 *
	 * Covers the WHERE, GROUP BY, JOIN, ORDER BY, DISTINCT,
	 * fields (SELECT), and LIMITS clauses.
	 *
	 * @since 3.31
	 *
	 * @param string[] $pieces Associative array of the pieces of the query.
	 * @param WP_Query $wp_query   The WP_Query instance (passed by reference).
	 */
	public static function mla_archive_posts_clauses_request( $pieces, $wp_query ) {
		global $wpdb;

		if ( MLAShortcode_Support::$converting_meta_date_query ) {
			return $pieces;	
		}

		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . " mla_archive_posts_clauses_request() pieces = " . var_export( $pieces, true ) );
		}

		$where    = isset( $pieces['where'] ) ? $pieces['where'] : '';
		$join     = isset( $pieces['join'] ) ? $pieces['join'] : '';

		// exif:DateTimeOriginal  YYYY:MM:DD HH:MM:SS
		// iptc:DateCreated YYYYMMDD
		// xmp:CreateDate YYYY-MM-DD HH:MM:SS  two digit month and day, 24-hour clock

		$query_arguments = apply_filters( 'mla_get_archive_values_query_arguments', self::$archive_list_arguments, $pieces );

		// These will come from shortcode attributes
		if ( 'custom' === $query_arguments['archive_source'] ) {
			$key = esc_sql( $query_arguments['archive_key'] );
			$join .= " INNER JOIN {$wpdb->postmeta} as archive_meta ON ( $wpdb->posts.ID = archive_meta.post_id )";
			$where .= " AND ( archive_meta.meta_key = '{$key}' )";
			$field = 'archive_meta.meta_value';
		} else {
			$field = esc_sql( $query_arguments['archive_source'] );
		}

		$order = esc_sql( $query_arguments['archive_order'] );

		// Initialize clauses that vary by archive type
		switch ( $query_arguments['archive_type'] ) {
			case 'daily':
				$main_select = "sq.year AS `year`, sq.month AS `month`, sq.day AS `day`";
				$main_group_by = "sq.year, sq.month, sq.day";
				$main_order_by = "sq.year {$order}, sq.month {$order}, sq.day {$order}";
				$sq_select = "YEAR({$field}) AS `year`, MONTH({$field}) AS `month`, DAYOFMONTH({$field}) AS `day`";
				$sq_group_by = "YEAR({$field}), MONTH({$field}), DAYOFMONTH({$field})";
				break;
			case 'weekly':
				$week = _wp_mysql_week( "{$field}" );
				$main_select = "DISTINCT sq.year AS `year`, sq.week as `week`, sq.yyyymmdd AS `yyyymmdd`";
				$main_group_by = "sq.year, sq.week";
				$main_order_by = "sq.year {$order}, sq.week {$order}";
				$sq_select = "DISTINCT YEAR({$field}) AS `year`, {$week} as `week`, DATE_FORMAT( {$field}, '%Y-%m-%d' ) AS `yyyymmdd`";
				$sq_group_by = "YEAR({$field}), {$week}";
				break;
			case 'monthly':
				$main_select = "sq.year AS `year`, sq.month AS `month`";
				$main_group_by = "sq.year, sq.month";
				$main_order_by = "sq.year {$order}, sq.month {$order}";
				$sq_select = "YEAR({$field}) AS `year`, MONTH({$field}) AS `month`";
				$sq_group_by = "YEAR({$field}), MONTH({$field})";
				break;
			case 'yearly':
			default:
				$main_select = "sq.year AS `year`";
				$main_group_by = "sq.year";
				$main_order_by = "sq.year {$order}";
				$sq_select = "YEAR({$field}) AS `year`";
				$sq_group_by = "YEAR({$field})";
		}

		$sq_order_by = "{$field} {$order}";

		if ( $query_arguments['archive_limit'] ) {
			$limit = $wpdb->prepare( 'LIMIT %d', $query_arguments['archive_limit'] );
		} else {
			$limit = '';
		}

		$query   = "SELECT {$main_select}, count(sq.ID) as items FROM ( SELECT {$sq_select}, ID FROM $wpdb->posts {$join} WHERE 1=1 {$where} GROUP BY {$sq_group_by}, ID ORDER BY {$sq_order_by}) as sq GROUP BY {$main_group_by} ORDER BY {$main_order_by} {$limit}";
		$query = apply_filters( 'mla_get_archive_values_query', $query, $query_arguments );

		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . " mla_archive_posts_clauses_request() query = " . var_export( $query, true ) );
		}

		self::$archive_list_items = $wpdb->get_results( $query );

		// Adjust for pagination
		$found_rows = count ( self::$archive_list_items );
		$offset = absint( $query_arguments['offset'] );
		$limit = absint( $query_arguments['limit'] );
		if ( 0 < $offset || 0 < $limit ) {
			self::$archive_list_items = array_slice( self::$archive_list_items, $offset, $limit );
		}

		if ( 'weekly' === $query_arguments['archive_type'] ) {
			foreach( self::$archive_list_items as $index => $item ) {
				$arc_week = get_weekstartend( $item->yyyymmdd, get_option( 'start_of_week' ) );
				self::$archive_list_items[ $index ]->week_start_raw = $arc_week['start'];
				self::$archive_list_items[ $index ]->week_start_short = date_i18n( 'Y-m-d', $arc_week['start'] );
				self::$archive_list_items[ $index ]->week_start = date_i18n( get_option( 'date_format' ), $arc_week['start'] );
				self::$archive_list_items[ $index ]->week_end_raw = $arc_week['end'];
				self::$archive_list_items[ $index ]->week_end_short = date_i18n( 'Y-m-d', $arc_week['end'] );
				self::$archive_list_items[ $index ]->week_end = date_i18n( get_option( 'date_format' ), $arc_week['end'] );
			}
		}

		self::$archive_list_items['found_rows'] = $found_rows;
		self::$archive_list_items = apply_filters( 'mla_get_archive_values_query_results', self::$archive_list_items );

		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . " mla_archive_posts_clauses_request() archive_list_items = " . var_export( self::$archive_list_items, true ) );
		}

		return $pieces;
	}

	/**
	 * Filters the posts array before the query takes place.
	 *
	 * Return a non-null value to bypass WordPress' default post queries.
	 *
	 * Filtering functions that require pagination information are encouraged to set
	 * the `found_posts` and `max_num_pages` properties of the WP_Query object,
	 * passed to the filter by reference. If WP_Query does not perform a database
	 * query, it will not have enough information to generate these values itself.
	 *
	 * @since 3.31
	 *
	 * @param array|null $posts Return an array of post data to short-circuit WP's query,
	 *                          or null to allow WP to run its normal queries.
	 * @param WP_Query   $wp_query  The WP_Query instance (passed by reference).
	 */
	public static function mla_archive_posts_pre_query( $posts, $wp_query ) {
		return array( 0 );
	}

	/**
	 * The MLA Archive List shortcode.
	 *
	 * This is an interface to the mla_archive_list function.
	 *
	 * @since 3.31
	 *
	 * @param array $attr Attributes of the shortcode.
	 * @param string $content Optional content for enclosing shortcodes
	 *
	 * @return string HTML content to display the archive list.
	 */
	public static function mla_archive_list_shortcode( $attr, $content = NULL ) {
		/*
		 * Make sure $attr is an array, even if it's empty,
		 * and repair damage caused by link-breaks in the source text
		 */
		$raw_attr = $attr;
		$attr = MLAShortcodes::mla_validate_attributes( $attr, $content );

		self::$mla_debug = ( ! empty( $attr['mla_debug'] ) ) ? trim( strtolower( $attr['mla_debug'] ) ) : false;
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
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_archive_list REQUEST', 'media-library-assistant' ) . '</strong> = ' . var_export( $_REQUEST, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_archive_list shortcode attributes', 'media-library-assistant' ) . '</strong> = ' . var_export( $raw_attr, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_archive_list shortcode content', 'media-library-assistant' ) . '</strong> = ' . var_export( $content, true ) );
		}

		// The 'array' format makes no sense in a shortcode
		if ( isset( $attr['mla_output'] ) && 'array' === $attr['mla_output'] ) {
			$attr['mla_output'] = 'dropdown';
		}

		// A shortcode must return its content to the caller, so "echo" makes no sense
		if ( isset( $attr['echo'] ) ) {
			$attr['echo'] = 'false';
		}

		// The current_archive parameter can be changed to support multiple lists per page
		if ( isset( $attr['mla_archive_parameter'] ) ) {
			$mla_archive_parameter = $attr['mla_archive_parameter'];
		} else {
			$mla_archive_parameter = 'mla_archive_current';
		}

		if ( empty( $attr[ $mla_archive_parameter ] ) && ! empty( $_REQUEST[ $mla_archive_parameter ] ) ) {
			$attr[ $mla_archive_parameter ] = $_REQUEST[ $mla_archive_parameter ];
		}

		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_archive_list validated attributes', 'media-library-assistant' ) . '</strong> = ' . var_export( $attr, true ) );
		}

		return self::mla_archive_list( $attr );
	}

	/**
	 * Archive list support function
	 *
	 * This function generates a 'daily', 'weekly', 'monthly', or 'yearly' dropdown control, list or array
	 *
	 * @since 3.31
	 *
	 * @param	array	$attr the shortcode parameters
	 *
	 * @return	mixed	HTML markup (or array) for the generated control/list
	 */
	public static function mla_archive_list( $attr ) {
		global $wpdb, $post;

		// Make sure $attr is an array, even if it's empty
		if ( is_string( $attr ) ) {
			$attr = shortcode_parse_atts( $attr );
		}

		if ( empty( $attr ) ) {
			$attr = array();
		}

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
			'selector' => "mla_archive_list-{$instance}",
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

		$default_arguments = array_merge(
			array(
			'archive_type' => 'yearly',
			'archive_source' => 'post_date',
			'archive_key' => '', // for source = custom
			'link' => 'current',
			'mla_output' => 'dropdown',
			'mla_output_qualifier' => '',
			'separator' => "\n",

			'smallest' => 12, // 8
			'largest' => 12, // 22
			'default_size' => 12,
			'unit' => 'pt',

			'mla_archive_parameter' => 'mla_archive_current',
			'mla_archive_current' => '',
			'append_current_item' => 'true',
			'archive_order' => 'DESC',
			'archive_limit' => '0',
			'archive_label' => '', // 'short', 'long'
			'show_count' => 'true',
			'hide_if_empty' => 'false',

			'listtag' => '',
			'listtag_name' => 'mla_archive_current',
			'listtag_id' => $page_values['selector'],
			'listtag_class' => 'mla-archive-list',
			'listtag_attributes' => '',
			'itemtag' => '',
			'current_archive_class' => 'mla_archive_current',

			'mla_style' => NULL,
			'mla_markup' => NULL,

			'option_all_value' => '',
			'option_all_label' => '',
			'option_none_value' => '',
			'option_none_label' => '',

			// Pagination parameters
			'limit' => 0,
			'offset' => 0,
			'mla_end_size'=> 1,
			'mla_mid_size' => 2,
			'mla_prev_text' => '&laquo; ' . __( 'Previous', 'media-library-assistant' ),
			'mla_next_text' => __( 'Next', 'media-library-assistant' ) . ' &raquo;',
			'mla_page_parameter' => 'mla_archive_list_current',
			'mla_archive_list_current' => 1,
			'mla_paginate_total' => NULL,
			'mla_paginate_rows' => NULL,
			'mla_paginate_type' => 'plain',

			'mla_debug' => '',
			'echo' => 'false',
			),
			self::$item_specific_arguments
		);

		// Filter the attributes before $mla_item_parameter and "request:" prefix processing.
		$attr = apply_filters( 'mla_archive_list_raw_attributes', $attr );

		// The current_archive parameter can be changed to support multiple lists per page
		if ( ! isset( $attr['mla_archive_parameter'] ) ) {
			$attr['mla_archive_parameter'] = $default_arguments['mla_archive_parameter'];
		}

		// The mla_archive_parameter can contain page_level parameters like {+page_ID+}
		$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr['mla_archive_parameter'] ) );
		$mla_archive_parameter = MLAData::mla_parse_template( $attr_value, $page_values );
		 
		/*
		 * Special handling of mla_archive_parameter to make multiple lists per page easier.
		 * Look for this parameter in $_REQUEST if it's not present in the shortcode itself.
		 */
		if ( ! isset( $attr[ $mla_archive_parameter ] ) ) {
			if ( isset( $_REQUEST[ $mla_archive_parameter ] ) ) {
				$attr[ $mla_archive_parameter ] = sanitize_text_field( wp_unslash( $_REQUEST[ $mla_archive_parameter ] ) );
			}
		}
		 
		// The mla_archive_list_current parameter can be changed to support multiple galleries per page.
		if ( ! isset( $attr['mla_page_parameter'] ) ) {
			$attr['mla_page_parameter'] = $default_arguments['mla_page_parameter'];
		}

		// The mla_page_parameter can contain page_level parameters like {+page_ID+}
		$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr['mla_page_parameter'] ) );
		$mla_page_parameter = MLAData::mla_parse_template( $attr_value, $page_values );

		/*
		 * Special handling of the mla_archive_list_current parameter to make "MLA pagination"
		 * easier. Look for this parameter in $_REQUEST if it's not present in the shortcode itself.
		 */
		if ( ! isset( $attr[ $mla_page_parameter ] ) ) {
			if ( isset( $_REQUEST[ $mla_page_parameter ] ) ) {
				$attr[ $mla_page_parameter ] = sanitize_text_field( wp_unslash( $_REQUEST[ $mla_page_parameter ] ) );
			}
		}

		// Determine markup template to get template-based argument values
		if ( ! empty( $attr['mla_markup'] ) ) {
			$template = $attr['mla_markup'];
			if ( ! MLATemplate_Support::mla_fetch_custom_template( $template, 'archive-list', 'markup', '[exists]' ) ) {
				$template = NULL;
			}
		} else {
			$template = NULL;
		}

		// Apply default arguments set in the markup template
		if ( ! empty( $template ) ) {
			$arguments = MLATemplate_Support::mla_fetch_custom_template( $template, 'archive-list', 'markup', 'arguments' );
			if ( ! empty( $arguments ) ) {
				$attr = wp_parse_args( $attr, MLAShortcode_Support::mla_validate_attributes( array(), $arguments ) );
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
				$attr[ $attr_key ] = MLAData::mla_parse_array_template( $attr_value, $replacement_values, 'array' );
				if ( empty( $attr[ $attr_key ] ) ) {
					$attr[ $attr_key ] = '';
				}
			}
		}

		// Save the validated arguments for processing in posts_clauses_request, _compose_archive_list
		$attr = apply_filters( 'mla_archive_list_attributes', $attr );
		self::$archive_list_attr = $attr;

		// Accept only the attributes we need, supply defaults and validate
		$arguments = shortcode_atts( $default_arguments, $attr );

		/*
		 * $mla_archive_parameter, if non-default, doesn't make it through the shortcode_atts
		 * filter, so we handle it separately
		 */
		if ( isset( $attr[ $mla_archive_parameter ] ) ) {
			$arguments[ $mla_archive_parameter ] = $attr[ $mla_archive_parameter ];
		} else {
			$arguments[ $mla_archive_parameter ] = $default_arguments['mla_archive_current'];
		}

		/*
		 * $mla_page_parameter, if non-default, doesn't make it through the shortcode_atts
		 * filter, so we handle it separately
		 */
		if ( ! isset( $arguments[ $mla_page_parameter ] ) ) {
			if ( isset( $attr[ $mla_page_parameter ] ) ) {
				$arguments[ $mla_page_parameter ] = $attr[ $mla_page_parameter ];
			} else {
				$arguments[ $mla_page_parameter ] = $default_arguments['mla_page_parameter'];
			}
		}

		// Update default argument values to simplify template substitution parameters
		$arguments['mla_archive_parameter'] = $mla_archive_parameter;
		$arguments['mla_archive_current'] = $arguments[ $mla_archive_parameter ];
		$arguments['listtag_name'] = $mla_archive_parameter;
		$arguments['current_archive_class'] = $mla_archive_parameter;
		$arguments['mla_page_parameter'] = $mla_page_parameter;
		$arguments['mla_archive_list_current'] = $arguments[ $mla_page_parameter ];

		// Process the pagination parameter, if present
		if ( isset( $arguments[ $mla_page_parameter ] ) ) {
			$arguments['offset'] = absint( $arguments['limit'] ) * ( absint( $arguments[ $mla_page_parameter ] ) - 1);
		}

		// Separate output type from qualifier
		$value = array_map( 'strtolower', array_map( 'trim', explode( ',', $arguments['mla_output'] ) ) );
		$qualifier = isset( $value[1] ) ? $value[1] : '';
		if ( in_array( $qualifier, array( 'div', 'wrap', 'always_wrap', 'show_all', 'prev_next' ) ) ) {
			$arguments['mla_output_qualifier'] = $qualifier;
		} else {
			$arguments['mla_output_qualifier'] = '';
		}

		$value = $value[0];
		$is_pagination = in_array( $value, self::$valid_mla_output_pagination_values );
		if ( $is_pagination || in_array( $value, self::$valid_mla_output_values ) ) {
			$arguments['mla_output'] = $value;
			$attr['mla_output'] = $value; // Fix for array_diff_assoc() below
		} else {
			$arguments['mla_output'] = 'dropdown';
			$arguments['mla_output_qualifier'] = '';
		}

		// Set default template if not specified in $attr
		if ( empty( $template ) ) {
			switch ( $arguments['mla_output'] ) {
				case 'flat':
					if ( 'div' === $arguments['mla_output_qualifier'] ) {
						$template = 'archive-list-flat-div';
					}

					break;
				case 'ulist':
				case 'olist':
					if ( 'div' === $arguments['mla_output_qualifier'] ) {
						$template = 'archive-list-ul-div';
					} else {
						$template = 'archive-list-ul';
					}

					break;
				case 'dropdown':
					$template = 'archive-list-dropdown';
					break;
				default:
					$template = NULL;
			}

			$arguments['mla_markup'] = $template;
		}

		// Determine style templates
		if ( $arguments['mla_style'] && ( 'none' !== $arguments['mla_style'] ) ) {
			if ( !MLATemplate_Support::mla_fetch_custom_template( $arguments['mla_style'], 'archive-list', 'style', '[exists]' ) ) {
				$arguments['mla_style'] = NULL;
			}
		}

		if ( ( NULL === $arguments['mla_style'] ) && ( ! empty( $arguments['mla_markup'] ) ) ) {
			if ( 'div' === $arguments['mla_output_qualifier'] ) {
				if ( 'flat' === $arguments['mla_output'] ) {
					$arguments['mla_style'] = 'archive-list-flat-div';
				} else {
					$arguments['mla_style'] = 'archive-list-ul-div';
				}
			} else {
					$arguments['mla_style'] = 'archive-list';
			}
		}

		if ( empty( $arguments['archive_label'] ) ) {
			if ( 'paginate_values' === $arguments['mla_output'] ) {
				$arguments['archive_label'] = 'short';
			} else {
				$arguments['archive_label'] = 'long';
			}
		} else {
			$value = trim( strtolower( $arguments['archive_label'] ) );
			if ( 'short' === $value ) {
				$arguments['archive_label'] = $value;
			} else {
				$arguments['archive_label'] = 'long';
			}
		}

		switch ( $arguments['mla_output'] ) {
			case 'olist':
				$default_tag = 'ol';
				break;
			case 'dropdown':
				$default_tag = 'select';
				break;
			case 'ulist':
			default:
				$default_tag = 'ul';
		}

		$arguments['listtag'] = MLAShortcode_Support::mla_esc_tag( $arguments['listtag'], $default_tag );

		switch ( $arguments['mla_output'] ) {
			case 'dropdown':
				$default_tag = 'option';
				break;
			case 'ulist':
			case 'olist':
			default:
				$default_tag = 'li';
		}

		$arguments['itemtag'] = MLAShortcode_Support::mla_esc_tag( $arguments['itemtag'], $default_tag );

		$value = trim( strtolower( $arguments['hide_if_empty'] ) );
		$arguments['hide_if_empty'] = 'true' === $value;

		$value = trim( strtolower( $arguments['link'] ) );
		if ( in_array( $value, array( 'current', 'view', 'span', 'none', ) ) ) {
			$arguments['link'] = $value;
		} else {
			$arguments['link'] = 'current';
		}

		$value = trim( strtolower( $arguments['echo'] ) );
		$arguments['echo'] = 'true' === $value;

		$value = trim( strtolower( $arguments['archive_type'] ) );
		if ( in_array( $value, array( 'daily', 'weekly', 'monthly', 'yearly', ) ) ) {
			$arguments['archive_type'] = $value;
		} else {
			$arguments['archive_type'] = 'yearly';
		}

		$value = trim( strtolower( $arguments['archive_source'] ) );
		if ( in_array( $value, array( 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt', 'custom', ) ) ) {
			$arguments['archive_source'] = $value;
		} else {
			$arguments['archive_source'] = 'post_date';
		}

		if ( 'custom' === $value ) {
			$value = trim( $arguments['archive_key'] );
			if ( ! empty( $value ) ) {
				$arguments['archive_key'] = $value;
			} else {
				$arguments['archive_key'] = '';
				$arguments['archive_source'] = 'post_date';
			}
		} else {
			$arguments['archive_key'] = '';
		}

		$value = trim( strtolower( $arguments['append_current_item'] ) );
		$arguments['append_current_item'] = 'true' === $value;

		$value = trim( strtoupper( $arguments['archive_order'] ) );
		if ( in_array( $value, array( 'ASC', 'DESC', ) ) ) {
			$arguments['archive_order'] = $value;
		} else {
			$arguments['archive_order'] = 'DESC';
		}

		$arguments['archive_limit'] = absint( $arguments['archive_limit'] );

		$arguments = apply_filters( 'mla_archive_list_arguments', $arguments );

		if ( empty( $attr['smallest'] ) ) {
			$arguments['smallest'] = $arguments['default_size'];
		}

		if ( empty( $attr['largest'] ) ) {
			$arguments['largest'] = $arguments['default_size'];
		}


		// mla_debug controls output from this shortcode
		self::$mla_debug = ( ! empty( $arguments['mla_debug'] ) ) ? trim( strtolower( $arguments['mla_debug'] ) ) : false;
		if ( in_array( self::$mla_debug, array( 'false', 'log', 'true', ) ) ) {
			$arguments['mla_debug'] = self::$mla_debug;

			if ( 'true' === self::$mla_debug ) {
				MLACore::mla_debug_mode( 'buffer' );
			} elseif ( 'log' === self::$mla_debug ) {
				MLACore::mla_debug_mode( 'log' );
			} else {
				self::$mla_debug = false;
			}
		} else {
			$arguments['mla_debug'] = '';
		}

		// Save the validated arguments for processing in posts_clauses_request, _compose_archive_list
		self::$archive_list_arguments = $arguments;
		$other_arguments = array_diff_assoc( $attr, self::$archive_list_arguments );

		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . " mla_archive_list() _REQUEST = " . var_export( $_REQUEST, true ) );
			MLACore::mla_debug_add( __LINE__ . " mla_archive_list() self::\$archive_list_attr = " . var_export( self::$archive_list_attr, true ) );
			MLACore::mla_debug_add( __LINE__ . " mla_archive_list() self::\$archive_list_arguments = " . var_export( self::$archive_list_arguments, true ) );
			MLACore::mla_debug_add( __LINE__ . " mla_archive_list() other_arguments = " . var_export( $other_arguments, true ) );
		}

		// The other arguments can contain page_level parameters like {+page_ID+}, request: or query: parameters
		$markup_values = $page_values;
		foreach ( $other_arguments as $key => $value ) {
			if ( is_string( $value ) ) {
				$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $value ) );
				$markup_values = MLAData::mla_expand_field_level_parameters( $attr_value, $other_arguments, $markup_values );
				$value = MLAData::mla_parse_array_template( $attr_value, $markup_values, 'array' );
			}

			if ( empty( $value ) ) {
				unset( $other_arguments[ $key ] );
			} else {
				$other_arguments[ $key ] = $value;
			}
		}
 
 		if ( empty( $other_arguments['post_parent'] ) ) {
			$other_arguments['post_parent'] = 'all';
		}

		$shortcode_arguments = array_merge( $other_arguments, array(
			'no_found_rows' => true,
			'fields' => 'ids',
			'cache_results' => false,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		) );

		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . " mla_archive_list() shortcode_arguments = " . var_export( $shortcode_arguments, true ) );
		}

		if ( $is_pagination && ( NULL !== $arguments['mla_paginate_rows'] ) ) {
			self::$archive_list_items = array( 'found_rows' => absint( $arguments['mla_paginate_rows'] ) );

			if ( self::$mla_debug ) {
				MLACore::mla_debug_add( __LINE__ . " mla_paginate_rows present; archive_list_items = " . var_export( self::$archive_list_items, true ) );
			}
		} else {
			// posts_clauses_request will perform the actual query, and posts_pre_query will short-circuit WP_Query
			add_filter( 'posts_clauses_request', 'MLAArchiveList::mla_archive_posts_clauses_request', 10, 2 );
			add_filter( 'posts_pre_query', 'MLAArchiveList::mla_archive_posts_pre_query', 10, 2 );

			// Some do_shortcode callers may not have a specific post in mind
			$ID = is_object( $post ) ? $post->ID : 0;

			$mla_debug = ! empty( self::$mla_debug );

			/*
			 * The query is aborted in the mla_archive_posts_pre_query filter after
			 * the items are created in the mla_archive_posts_clauses_request filter
			 * and stored in the self::$archive_list_items array.
			 */
			$attachments = MLAShortcodes::mla_get_shortcode_attachments( $ID, $shortcode_arguments, false, $mla_debug );

			remove_filter( 'posts_clauses_request', 'MLAArchiveList::mla_archive_posts_clauses_request', 10 );
			remove_filter( 'posts_pre_query', 'MLAArchiveList::mla_archive_posts_pre_query', 10 );
		}

		// Compute the ->items-based "cloud" values
		$min_count = 0x7FFFFFFF;
		$max_count = 0;
		$min_scaled_count = 0x7FFFFFFF;
		$max_scaled_count = 0;
		foreach ( self::$archive_list_items as $key => $value ) {
			if ( 'found_rows' === $key ) {
				continue;
			}

			$value_count = isset ( $value->items ) ? $value->items : 0;
			$scaled_count = apply_filters( 'mla_archive_list_scale', round(log10($value_count + 1) * 100), $attr, $arguments, $value );

			if ( $value_count < $min_count ) {
				$min_count = $value_count;
			}

			if ( $value_count > $max_count ) {
				$max_count = $value_count;
			}

			if ( $scaled_count < $min_scaled_count ) {
				$min_scaled_count = $scaled_count;
			}

			if ( $scaled_count > $max_scaled_count ) {
				$max_scaled_count = $scaled_count;
			}
		} // foreach value

		// Calculate list-level cloud parameters
		$spread = $max_scaled_count - $min_scaled_count;
		if ( $spread <= 0 ) {
			$spread = 1;
		}

		$font_spread = $arguments['largest'] - $arguments['smallest'];
		if ( $font_spread < 0 ) {
			$font_spread = 1;
		}

		$font_step = $font_spread / $spread;

		$list_values = array_merge( $page_values, $arguments, array(
			'found_rows' => self::$archive_list_items['found_rows'],
			'min_count' => $min_count,
			'max_count' => $max_count,
			'min_scaled_count' => $min_scaled_count,
			'max_scaled_count' => $max_scaled_count,
			'spread' => $spread,
			'font_spread' => $font_spread,
			'font_step' => $font_step,
		) );

		// Expand list-level parameters
		$list_values['listtag_name'] = esc_attr( self::_process_shortcode_parameter( $list_values['listtag_name'], $list_values ) );
		$list_values['listtag_id'] = esc_attr( self::_process_shortcode_parameter( $list_values['listtag_id'], $list_values ) );
		$list_values['listtag_class'] = sanitize_html_class( self::_process_shortcode_parameter( $list_values['listtag_class'], $list_values ) );
		$list_values['listtag_attributes'] = MLAShortcode_Support::mla_esc_attr( self::_process_shortcode_parameter( $list_values['listtag_attributes'], $list_values ) );

		// Load style template and initialize page-level values.
		$use_mla_archive_list_style = $arguments['mla_style'] && ( 'none' !== strtolower( $arguments['mla_style'] ) );
		if ( apply_filters( 'use_mla_archive_list_style', $use_mla_archive_list_style, $arguments['mla_style'] ) ) {
			$style_template = MLATemplate_support::mla_fetch_custom_template( $arguments['mla_style'], 'archive-list', 'style' );
			$style_template = apply_filters( 'mla_archive_list_style_template', $style_template );
			$style_values = MLAData::mla_expand_field_level_parameters( $style_template, $attr, $list_values );
			$style_values = apply_filters( 'mla_archive_list_style_values', $style_values );
			$style_content = MLAData::mla_parse_template( $style_template, $style_values );
			$list = apply_filters( 'mla_archive_list_style_parse', $style_content, $style_template, $style_values );
		} else {
			$list = '';
		}

		$links = array();
		self::_compose_archive_list( $list, $links, $list_values );

		if ( 'array' === $arguments['mla_output'] ) {
			return $list;
		}

		if ( empty($arguments['echo']) ) {
			if ( 'true' === $arguments['mla_debug'] ) {
				$output = MLACore::mla_debug_flush();
			} else {
				$output = '';
			}

			return $output . $list;
		}

		echo $list; // phpcs:ignore
	} // mla_archive_list
} // Class MLAArchiveList
?>