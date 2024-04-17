<?php
/**
 * Media Library Assistant Term List Shortcode
 *
 * @package Media Library Assistant
 * @since 3.13
 */

/**
 * Class MLA (Media Library Assistant) Term List Shortcode implements
 * the [mla_term_list] shortcode.
 *
 * @package Media Library Assistant
 * @since 3.13
 */
class MLATermList {
	/**
	 * Turn debug collection and display on or off
	 *
	 * @since 3.13
	 *
	 * @var	boolean
	 */
	private static $mla_debug = false;

	/**
	 * These are the default parameters for term list display
	 *
	 * @since 3.13
	 *
	 * @var	array
	 */
	private static $term_list_item_specific_arguments = array(
			'mla_link_attributes' => '',
			'mla_link_class' => '',
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
	 * Valid mla_output values
	 *
	 * @since 3.13
	 *
	 * @var	array
	 */
	private static $valid_mla_output_values = array( 'flat', 'list', 'ulist', 'olist', 'dlist', 'dropdown', 'checklist', 'array' );

	/**
	 * Compose one level of an mla_term_list
	 *
	 * Adds shortcode output text and term-specific links to arrays passed by reference.
	 *
	 * @since 3.13
	 *
	 * @param string $list Shortcode output text, by reference
	 * @param array $links Term-specific links for flat/array output, by reference
	 * @param array $terms Term objects, by reference
	 * @param array $markup_values Style and list-level substitution parameters, by reference
	 * @param array $arguments Shortcode parameters, including defaults, by reference
	 * @param array $attr Shortcode parameters, explicit, by reference
	 *
	 * @return boolean True if the list contains the "current_item"; appends to &$list, &$links
	 */
	public static function _compose_term_list( &$list, &$links, &$terms, &$markup_values, &$arguments, &$attr ) {
		$term = reset( $terms );
		$markup_values['current_level'] = $current_level = $term->level;
		if ( $current_level ) {
			$markup_values['itemtag_class'] = 'term-list term-list-taxonomy-' . $term->taxonomy . ' children'; 
			$markup_values['itemtag_id'] = $markup_values['selector'] . '-' . $term->parent;
		} else {
			$markup_values['itemtag_class'] = 'term-list term-list-taxonomy-' . $term->taxonomy; 
			$markup_values['itemtag_id'] = $markup_values['selector'];
		}

		$mla_item_parameter = $arguments['mla_item_parameter'];

		// Determine output type and templates
		$output_parameters = array_map( 'strtolower', array_map( 'trim', explode( ',', $arguments['mla_output'] ) ) );

		if ( !in_array( $output_parameters[0], self::$valid_mla_output_values ) ) {
			$output_parameters[0] = 'ulist';
		}

		$is_list = in_array( $output_parameters[0], array( 'list', 'ulist', 'olist', 'dlist' ) );
		$is_dropdown = 'dropdown' === $output_parameters[0];
		$is_checklist = 'checklist' === $output_parameters[0];
		$is_hierarchical = !( 'false' === $arguments['hierarchical'] );
		$combine_hierarchical = 'combine' === $arguments['hierarchical'];

		// Using the slug is a common practice and affects current_item
		if ( $is_dropdown || $is_checklist ) {
			$current_is_slug = in_array( $arguments['mla_option_value'], array( '{+slug+}', '[+slug+]' ) );
		} else {
			$current_is_slug = in_array( $arguments['mla_item_value'], array( '{+slug+}', '[+slug+]' ) );
		}

		if ( $is_list || $is_dropdown || $is_checklist ) {
			if ( $term->parent ) {
				$open_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'term-list', 'markup', 'child-open' );
			} else {
				$open_template = false;
			}

			if ( false === $open_template ) {
				$open_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'term-list', 'markup', 'open' );
			}

			// Fall back to default template if no Open section
			if ( false === $open_template ) {
				$markup_values['mla_markup'] = $arguments['default_mla_markup'];

				if ( $term->parent ) {
					$open_template = MLATemplate_support::mla_fetch_custom_template( $arguments['mla_markup'], 'term-list', 'markup', 'child-open' );
				} else {
					$open_template = false;
				}

				if ( false === $open_template ) {
					$open_template = MLATemplate_support::mla_fetch_custom_template( $arguments['mla_markup'], 'term-list', 'markup', 'open' );
				}
			}

			if ( empty( $open_template ) ) {
				$open_template = '';
			}

			if ( $term->parent ) {
				$item_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'term-list', 'markup', 'child-item' );
			} else {
				$item_template = false;
			}

			if ( false === $item_template ) {
				$item_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'term-list', 'markup', 'item' );
			}

			if ( empty( $item_template ) ) {
				$item_template = '';
			}

			if ( $term->parent ) {
				$close_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'term-list', 'markup', 'child-close' );
			} else {
				$close_template = false;
			}

			if ( false === $close_template ) {
				$close_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'term-list', 'markup', 'close' );
			}

			if ( empty( $close_template ) ) {
				$close_template = '';
			}

			if ( $is_list || ( ( 0 === $current_level ) && $is_dropdown ) || $is_checklist ) {
				// Look for gallery-level markup substitution parameters
				$new_text = $open_template . $close_template;
				$markup_values = MLAData::mla_expand_field_level_parameters( $new_text, $attr, $markup_values );

				$markup_values = apply_filters( 'mla_term_list_open_values', $markup_values );
				$open_template = apply_filters( 'mla_term_list_open_template', $open_template );
				if ( empty( $open_template ) ) {
					$gallery_open = '';
				} else {
					$gallery_open = MLAData::mla_parse_template( $open_template, $markup_values );
				}

				$list .=  apply_filters( 'mla_term_list_open_parse', $gallery_open, $open_template, $markup_values );
			}
		} // $is_list || $is_dropdown || $is_checklist

		// Find delimiter for currentlink, currentlink_url
		if ( strpos( $markup_values['page_url'], '?' ) ) {
			$current_item_delimiter = '&';
		} else {
			$current_item_delimiter = '?';
		}

		$has_active = false;
		foreach ( $terms as $key => $term ) {
			$item_values = $markup_values;
			$is_active = false;

			// fill in item-specific elements
			$item_values['key'] = $key;
			$item_values['term_id'] = $term->term_id;
			$item_values['name'] = wptexturize( $term->name );
			$item_values['slug'] = $term->slug;
			$item_values['term_group'] = $term->term_group;
			$item_values['term_taxonomy_id'] = $term->term_taxonomy_id;
			$item_values['taxonomy'] = $term->taxonomy;
			$item_values['description'] = wptexturize( $term->description );
			$item_values['parent'] = $term->parent;
			$item_values['count'] = isset ( $term->count ) ? (integer) $term->count : 0; 
			$item_values['term_count'] = isset ( $term->term_count ) ? (integer) $term->term_count : 0; 
			$item_values['link_url'] = $term->link;
			$item_values['currentlink_url'] = sprintf( '%1$s%2$scurrent_item=%3$d', $item_values['page_url'], $current_item_delimiter, $item_values['term_id'] );
			$item_values['editlink_url'] = $term->edit_link;
			$item_values['termlink_url'] = $term->term_link;
			$item_values['children'] = '';
			$item_values['termtag_attributes'] = '';
			$item_values['termtag_class'] = $term->parent ? 'term-list-term children' : 'term-list-term';
			$item_values['termtag_id'] = sprintf( '%1$s-%2$d', $item_values['taxonomy'], $item_values['term_id'] );
			// Added in the code below:
			$item_values['caption'] = '';
			$item_values['link_attributes'] = '';
			$item_values['active_item_class'] = '';
			$item_values['current_item_class'] = '';
			$item_values['rollover_text'] = '';
			$item_values['link_style'] = '';
			$item_values['link_text'] = '';
			$item_values['currentlink'] = '';
			$item_values['editlink'] = '';
			$item_values['termlink'] = '';
			$item_values['thelink'] = '';

			if ( ! empty( $arguments[ $mla_item_parameter ] ) ) {
				foreach ( $arguments[ $mla_item_parameter ] as $current_item ) {
					// Check for multi-taxonomy taxonomy.term compound values
					$value = explode( '.', $current_item );
					if ( 2 === count( $value ) ) {
						if ( $value[0] !== $term->taxonomy ) {
							continue;
						}

						$current_item = $value[1];
					}

					// Must work for special values, e.g., any.terms.assigned or -2
					if ( $current_is_slug || !is_numeric( $current_item ) ) {
						if ( sanitize_title_for_query( $term->slug ) === sanitize_title_for_query( $current_item ) ) {
							$is_active = true;
							$item_values['current_item_class'] = $arguments['current_item_class'];
							break;
						}
					} else {
						if ( (integer) $term->term_id === (integer) $current_item ) {
							$is_active = true;
							$item_values['current_item_class'] = $arguments['current_item_class'];
							break;
						}
					}
				}
			}

			// Add item_specific field-level substitution parameters
			$new_text = isset( $item_template ) ? $item_template : '';
			foreach( self::$term_list_item_specific_arguments as $index => $value ) {
				$new_text .= str_replace( '{+', '[+', str_replace( '+}', '+]', $arguments[ $index ] ) );
			}

			$item_values = MLAData::mla_expand_field_level_parameters( $new_text, $attr, $item_values );

			if ( $item_values['captiontag'] ) {
				$item_values['caption'] = wptexturize( $term->description );
				if ( ! empty( $arguments['mla_caption'] ) ) {
					$item_values['caption'] = wptexturize( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_caption'], $item_values ) );
				}
			} else {
				$item_values['caption'] = '';
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

			if ( ! empty( $arguments['mla_link_class'] ) ) {
				$link_attributes .= 'class="' . esc_attr( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_link_class'], $item_values ) ) . '" ';
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

			if ( ! empty( $arguments['mla_link_text'] ) ) {
				$item_values['link_text'] = wp_kses( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_link_text'], $item_values ), 'post' );
			} else {
				$item_values['link_text'] = $item_values['name'];
			}

			if ( ! empty( $arguments['show_count'] ) && ( 'true' === strtolower( $arguments['show_count'] ) ) ) {
				// Ignore option- all,any_terms,no_terms
				if ( -1 !== $item_values['count'] ) {
					$item_values['link_text'] .= ' (' . $item_values['count'] . ')';
				}
			}

			if ( empty( $arguments['mla_item_value'] ) ) {
				$item_values['thevalue'] = $item_values['term_id'];
			} else {
				$item_values['thevalue'] = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_item_value'], $item_values );
			}

			// Currentlink, editlink, termlink and thelink  TODO - link style
			$item_values['currentlink'] = sprintf( '<a %1$shref="%2$s%3$s%4$s=%5$s" title="%6$s" style="%7$s">%8$s</a>', $link_attributes, $item_values['page_url'], $current_item_delimiter, $mla_item_parameter, $item_values['thevalue'], $item_values['rollover_text'], '', $item_values['link_text'] );
			$item_values['editlink'] = sprintf( '<a %1$shref="%2$s" title="%3$s" style="%4$s">%5$s</a>', $link_attributes, $item_values['editlink_url'], $item_values['rollover_text'], '', $item_values['link_text'] );
			$item_values['termlink'] = sprintf( '<a %1$shref="%2$s" title="%3$s" style="%4$s">%5$s</a>', $link_attributes, $item_values['termlink_url'], $item_values['rollover_text'], '', $item_values['link_text'] );

			if ( ! empty( $link_href ) ) {
				$item_values['thelink'] = sprintf( '<a %1$shref="%2$s" title="%3$s" style="%4$s">%5$s</a>', $link_attributes, $link_href, $item_values['rollover_text'], '', $item_values['link_text'] );
			} elseif ( 'current' === $arguments['link'] ) {
				$item_values['link_url'] = $item_values['currentlink_url'];
				$item_values['thelink'] = $item_values['currentlink'];
			} elseif ( 'edit' === $arguments['link'] ) {
				$item_values['thelink'] = $item_values['editlink'];
			} elseif ( 'view' === $arguments['link'] ) {
				$item_values['thelink'] = $item_values['termlink'];
			} elseif ( 'span' === $arguments['link'] ) {
				$item_values['thelink'] = sprintf( '<span %1$sstyle="%2$s">%3$s</span>', $link_attributes, '', $item_values['link_text'] );
			} else {
				$item_values['thelink'] = $item_values['link_text'];
			}

			if ( $is_dropdown || $is_checklist ) {
				// Indent the dropdown list
				if ( $is_dropdown && $current_level && $is_hierarchical ) {
					$pad = str_repeat('&nbsp;', $current_level * 3);
				} else {
					$pad = '';
				}

				if ( empty( $arguments['mla_option_text'] ) ) {
					$item_values['thelabel'] = $pad . $item_values['link_text'];
				} else {
					$item_values['thelabel'] = $pad . MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_option_text'], $item_values );
				}

				if ( empty( $arguments['mla_option_value'] ) ) {
					$item_values['thevalue'] = $item_values['term_id'];

					// Combined hierarchical multi-taxonomy controls generate compound taxonomy.term values 
					if ( ( $is_dropdown || $is_checklist ) && 1 < count( $arguments['taxonomy'] ) ) {
						if ( !( $is_hierarchical && !$combine_hierarchical ) ) {
							$item_values['thevalue'] = $item_values['taxonomy'] . '.' . $item_values['term_id'];
						}
					}
				} else {
					$item_values['thevalue'] = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_option_value'], $item_values );
				}

				$item_values['popular'] = ''; // TODO Calculate 'term-list-popular'

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

			$child_links = array();
			$child_active = false;
			if ( $is_hierarchical && ! empty( $term->children ) ) {
				$child_active = self::_compose_term_list( $item_values['children'], $child_links, $term->children, $markup_values, $arguments, $attr );
				$markup_values['current_level'] = $current_level; // Changed in _compose_term_list
			}

			if ( $is_active || $child_active ) {
				$has_active = true;
				$item_values['active_item_class'] = $arguments['active_item_class'];
			}

			if ( $is_list || $is_dropdown || $is_checklist ) {
				// item markup
				$item_values = apply_filters( 'mla_term_list_item_values', $item_values );
				$item_template = apply_filters( 'mla_term_list_item_template', $item_template );
				$parse_value = MLAData::mla_parse_template( $item_template, $item_values );
				$list .= apply_filters( 'mla_term_list_item_parse', $parse_value, $item_template, $item_values );
			} else {
				$item_values = apply_filters( 'mla_term_list_item_values', $item_values );
				$links[] = apply_filters( 'mla_term_list_item_parse', $item_values['thelink'], NULL, $item_values );

				if ( $is_hierarchical && ! empty( $child_links ) ) {
					$links = array_merge( $links, $child_links );
				}
			} 
		} // foreach tag

		// If the current item isn't in the term list, remove it to prevent "stale" [mla_gallery] content
		if ( ( 0 === $current_level ) && ( false === $has_active ) ) {
			$mla_control_name = $markup_values['thename'];

			// Does not handle default 'tax_input[[+taxonomy+]][]' values
			if ( false === strpos( $mla_control_name, '[]' ) ) {
				unset( $_REQUEST[ $mla_item_parameter ] );
				unset( $_REQUEST[ $mla_control_name ] );
			}
		}

		if ( $is_list || $is_dropdown || $is_checklist ) {
			if ( $is_list || ( ( 0 === $current_level ) && $is_dropdown ) || $is_checklist ) {
				$markup_values = apply_filters( 'mla_term_list_close_values', $markup_values );
				$close_template = apply_filters( 'mla_term_list_close_template', $close_template );
				$parse_value = MLAData::mla_parse_template( $close_template, $markup_values );
				$list .= apply_filters( 'mla_term_list_close_parse', $parse_value, $close_template, $markup_values );
			}
		} else {
			switch ( $markup_values['mla_output'] ) {
			case 'array' :
				$list =& $links;
				break;
			case 'flat' :
			default :
				$list .= join( $markup_values['separator'], $links );
				break;
			} // switch format
		}

		return $has_active;
	} // _compose_term_list

	/**
	 * The MLA Term List support function.
	 *
	 * This is an alternative to the WordPress wp_list_categories, wp_dropdown_categories
	 * and wp_terms_checklist functions, with additional options to customize the hyperlink
	 * behind each term.
	 *
	 * @since 3.13
	 *
	 * @param array $attr Attributes of the shortcode.
	 *
	 * @return void|string|string[] Void if 'echo' attribute is true, or on failure. Otherwise, term list markup as a string or an array of links, depending on 'mla_output' attribute.
	 */
	public static function mla_term_list( $attr ) {
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
			'selector' => "mla_term_list-{$instance}",
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
			MLAShortcode_Support::$mla_get_terms_parameters,
			array(
			'echo' => false,
			'mla_debug' => false,
			'mla_output' => 'ulist',
			'hierarchical' => 'true',

			'separator' => "\n",
			'single_text' => '%s item',
			'multiple_text' => '%s items',
			'link' => 'current',
			'current_item' => '',
			'active_item_class' => 'mla_active_item',
			'current_item_class' => 'mla_current_item',
			'mla_item_parameter' => 'current_item',
			'show_count' => false,

			'mla_style' => NULL,
			'mla_markup' => NULL,
			'itemtag' => 'ul',
			'termtag' => 'li',
			'captiontag' => '',
			'mla_multi_select' => '',

			'mla_nolink_text' => '',
			'mla_target' => '',
			'hide_if_empty' => false,

			'option_all_text' => '',
			'option_all_value' => NULL,
			'option_no_terms_text' => '',
			'option_no_terms_value' => NULL,
			'option_any_terms_text' => '',
			'option_any_terms_value' => NULL,

			'option_none_text' => '',
			'option_none_value' => NULL,

			'depth' => 0,
			'child_of' => 0,
			'include_tree' => NULL,
			'exclude_tree' => NULL,
			),
			self::$term_list_item_specific_arguments
		);

		// Filter the attributes before $mla_item_parameter and "request:" prefix processing.
		$attr = apply_filters( 'mla_term_list_raw_attributes', $attr );

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
				$attr[ $mla_item_parameter ] = sanitize_text_field( wp_unslash( $_REQUEST[ $mla_item_parameter ] ) );
			}
		}
		 
		// Determine markup template to get default arguments
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

		if ( $arguments['mla_markup'] ) {
			$template = $arguments['mla_markup'];
			if ( ! MLATemplate_Support::mla_fetch_custom_template( $template, 'term-list', 'markup', '[exists]' ) ) {
				$template = NULL;
			}
		} else {
			$template = NULL;
		}

		if ( empty( $template ) ) {
			$output_parameters = array_map( 'strtolower', array_map( 'trim', explode( ',', $arguments['mla_output'] ) ) );

			if ( !in_array( $output_parameters[0], array( 'flat', 'list', 'ulist', 'olist', 'dlist', 'dropdown', 'checklist', 'array' ) ) ) {
				$output_parameters[0] = 'ulist';
			}

			if ( in_array( $output_parameters[0], array( 'list', 'ulist', 'olist', 'dlist' ) ) ) {
				if ( ( 'dlist' === $output_parameters[0] ) || ('list' === $output_parameters[0] && 'dd' === $arguments['captiontag'] ) ) {
					$template = 'term-list-dl';
				} else {
					$template = 'term-list-ul';
				}
			} elseif ( 'dropdown' === $output_parameters[0] ) {
				$template = 'term-list-dropdown';
			} elseif ( 'checklist' === $output_parameters[0] ) {
				$template = 'term-list-checklist';
			}
		}

		// Apply default arguments set in the markup template
		$arguments = MLATemplate_Support::mla_fetch_custom_template( $template, 'term-list', 'markup', 'arguments' );
		if ( ! empty( $arguments ) ) {
			$attr = wp_parse_args( $attr, MLAShortcode_Support::mla_validate_attributes( array(), $arguments ) );
		}

		// Adjust data selection arguments; remove pagination-specific arguments
		unset( $attr['limit'] );
		unset( $attr['offset'] );

		/*
		 * Look for page-level, 'request:' and 'query:' substitution parameters,
		 * which can be added to any input parameter
		 */
		foreach ( $attr as $attr_key => $attr_value ) {
			/*
			 * item-specific Display Content parameters must be evaluated
			 * later, when all of the information is available.
			 */
			if ( array_key_exists( $attr_key, self::$term_list_item_specific_arguments ) ) {
				continue;
			}

			$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr_value ) );
			$replacement_values = MLAData::mla_expand_field_level_parameters( $attr_value, $attr, $page_values );
			$attr[ $attr_key ] = MLAData::mla_parse_template( $attr_value, $replacement_values );
		}

		$attr = apply_filters( 'mla_term_list_attributes', $attr );
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

		// Clean up the current_item(s) to separate term_id from slug
		if ( ! empty( $arguments[ $mla_item_parameter ] ) ) {
			if ( is_string( $arguments[ $mla_item_parameter ] ) ) {
				$arguments[ $mla_item_parameter ] = explode( ',', $arguments[ $mla_item_parameter ] );
			}
			foreach( $arguments[ $mla_item_parameter ] as $index => $value ) {
				if ( ctype_digit( $value ) ) {
					$arguments[ $mla_item_parameter ][ $index ] = absint( $value );
				}
			}
		}

		$arguments = apply_filters( 'mla_term_list_arguments', $arguments );

		// Clean up hierarchical parameter to simplify later processing
		$arguments['hierarchical'] = strtolower( trim( $arguments['hierarchical'] ) ) ;
		if ( !in_array( $arguments['hierarchical'], array( 'true', 'combine' ) ) ) {
			$arguments['hierarchical'] = 'false';
		}

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
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug REQUEST', 'media-library-assistant' ) . '</strong> = ' . var_export( $_REQUEST, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug attributes', 'media-library-assistant' ) . '</strong> = ' . var_export( $attr, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug arguments', 'media-library-assistant' ) . '</strong> = ' . var_export( $arguments, true ) );
		}

		// Determine templates and output type
		if ( $arguments['mla_style'] && ( 'none' !== $arguments['mla_style'] ) ) {
			if ( !MLATemplate_Support::mla_fetch_custom_template( $arguments['mla_style'], 'term-list', 'style', '[exists]' ) ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>mla_term_list mla_style</strong> "' . $arguments['mla_style'] . '" ' . __( 'not found', 'media-library-assistant' ), MLACore::MLA_DEBUG_CATEGORY_ANY );
				$arguments['mla_style'] = NULL;
			}
		}

		if ( $arguments['mla_markup'] ) {
			if ( !MLATemplate_Support::mla_fetch_custom_template( $arguments['mla_markup'], 'term-list', 'markup', '[exists]' ) ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>mla_term_list mla_markup</strong> "' . $arguments['mla_markup'] . '" ' . __( 'not found', 'media-library-assistant' ), MLACore::MLA_DEBUG_CATEGORY_ANY );
				$arguments['mla_markup'] = NULL;
			}
		}

		$output_parameters = array_map( 'strtolower', array_map( 'trim', explode( ',', $arguments['mla_output'] ) ) );

		if ( !in_array( $output_parameters[0], array( 'flat', 'list', 'ulist', 'olist', 'dlist', 'dropdown', 'checklist', 'array' ) ) ) {
			$output_parameters[0] = 'ulist';
		}

		$default_style = 'term-list';
		$default_markup = 'term-list-ul';

		if ( $is_list = in_array( $output_parameters[0], array( 'list', 'ulist', 'olist', 'dlist' ) ) ) {

			if ( 'list' === $output_parameters[0] && 'dd' === $arguments['captiontag'] ) {
				$default_markup = 'term-list-dl';
				$arguments['itemtag'] = 'dl';
				$arguments['termtag'] = 'dt';
			} else {
				$default_markup = 'term-list-ul';
				$arguments['termtag'] = 'li';
				$arguments['captiontag'] = '';

				switch ( $output_parameters[0] ) {
					case 'ulist':
						$arguments['itemtag'] = 'ul';
						break;
					case 'olist':
						$arguments['itemtag'] = 'ol';
						break;
					case 'dlist':
						$default_markup = 'term-list-dl';
						$arguments['itemtag'] = 'dl';
						$arguments['termtag'] = 'dt';
						$arguments['captiontag'] = 'dd';
					break;
					default:
						$arguments['itemtag'] = 'ul';
				}
			}
		}

		if ( $is_dropdown = 'dropdown' === $output_parameters[0] ) {
			$default_markup = 'term-list-dropdown';
			$arguments['itemtag'] = empty( $attr['itemtag'] ) ? 'select' : $attr['itemtag'];
			$arguments['termtag'] = 'option';
		}

		if ( $is_checklist = 'checklist' === $output_parameters[0] ) {
			$default_markup = 'term-list-checklist';
			$arguments['termtag'] = 'li';
		}

		$arguments['default_mla_style'] = $default_style;
		if ( NULL === $arguments['mla_style'] ) {
			$arguments['mla_style'] = $default_style;
		}

		$arguments['default_mla_markup'] = $default_markup;
		if ( NULL === $arguments['mla_markup'] ) {
			$arguments['mla_markup'] = $default_markup;
		}

		$mla_multi_select = ! empty( $arguments['mla_multi_select'] ) && ( 'true' === strtolower( $arguments['mla_multi_select'] ) );

		$is_hierarchical = !( 'false' === $arguments['hierarchical'] );
		$combine_hierarchical = 'combine' === $arguments['hierarchical'];

		// Convert lists to arrays
		if ( is_string( $arguments['taxonomy'] ) ) {
			$arguments['taxonomy'] = explode( ',', $arguments['taxonomy'] );
		}

		if ( is_string( $arguments['post_type'] ) ) {
			$arguments['post_type'] = explode( ',', $arguments['post_type'] );
		}

		if ( is_string( $arguments['post_status'] ) ) {
			$arguments['post_status'] = explode( ',', $arguments['post_status'] );
		}

		// Hierarchical exclude is done in _get_term_tree to exclude children
		if ( $is_hierarchical && isset( $arguments['exclude'] ) ) {
			$exclude_later = $arguments['exclude'];
			unset( $arguments['exclude'] );
		} else {
			$exclude_later = NULL;
		}

		$tags = MLAShortcode_Support::mla_get_terms( $arguments );
		if ( ! empty( $exclude_later ) ) {
			$arguments['exclude'] = $exclude_later;
		}

		// Invalid taxonomy names return WP_Error
		if ( is_wp_error( $tags ) ) {
			$list =  '<strong>' . __( 'ERROR', 'media-library-assistant' ) . ': ' . $tags->get_error_message() . '</strong>, ' . $tags->get_error_data( $tags->get_error_code() );

			if ( 'array' === $arguments['mla_output'] ) {
				return array( $list );
			}

			if ( empty($arguments['echo']) ) {
				return $list;
			}

			echo $list; // phpcs:ignore
			return;
		}

		// Fill in the item_specific link properties, calculate list parameters
		if ( isset( $tags['found_rows'] ) ) {
			$found_rows = $tags['found_rows'];
			unset( $tags['found_rows'] );
		} else {
			$found_rows = count( $tags );
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
				if ( empty( $arguments['option_none_text'] ) ) {
					$arguments['option_none_text'] = __( 'no-terms', 'media-library-assistant' );
				}

				if ( ! empty( $arguments['option_none_value'] ) ) {
					$option_none_value = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['option_none_value'], $page_values );
					if ( is_numeric( $option_none_value ) ) {
						$option_none_id = (int) $option_none_value;
						$option_none_slug = sanitize_title( $arguments['option_none_text'] );
					} else {
						$option_none_id = -1;
						$option_none_slug = sanitize_title( $option_none_value );
					}
				} else {
					$option_none_id = -1;
					$option_none_slug = sanitize_title( $arguments['option_none_text'] );
				}

				$tags[0] = ( object ) array(
					'term_id' => $option_none_id,
					'name' => $arguments['option_none_text'],
					'slug' => $option_none_slug,
					'term_group' => '0',
					'term_taxonomy_id' => $option_none_id,
					'taxonomy' => reset( $arguments['taxonomy'] ),
					'description' => '',
					'parent' => '0',
					'count' => 0,
					'level' => 0,
					'edit_link' => '',
					'term_link' => '',
					'link' => '',
				);

				$is_hierarchical = false;
				$found_rows = 1;
			} else {
				$list .= wp_kses( $arguments['mla_nolink_text'], 'post' );

				if ( empty($arguments['echo']) ) {
					return $list;
				}

				echo $list; // phpcs:ignore
				return;
			}
		}

		if ( self::$mla_debug ) {
			$list = MLACore::mla_debug_flush();
		} else {
			$list = '';
		}

		if ( !$show_empty ) {
			$add_all_option = ! empty( $arguments['option_all_text'] );
			$add_any_terms_option = ! empty( $arguments['option_any_terms_text'] );
			$add_no_terms_option = ! empty( $arguments['option_no_terms_text'] );
		} else {
			$add_all_option = false;
			$add_any_terms_option = false;
			$add_no_terms_option = false;
		}

		if ( $add_all_option || $add_any_terms_option || $add_no_terms_option ) {
			$terms_assigned_counts = MLAShortcode_Support::mla_get_all_none_term_counts( $arguments );
		} else {
			$terms_assigned_counts = array( 'ignore.terms.assigned' => 0, 'no.terms.assigned' => 0, 'any.terms.assigned' => 0 );
		}

		// Using the slug is a common practice and affects option_ all/any_terms/no_terms _value(s)
		$option_all_id = -3;
		$option_all_slug = 'ignore.terms.assigned';
		if ( $add_all_option ) {
			if ( ! empty( $arguments['option_all_value'] ) ) {
				$option_all_value = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['option_all_value'], $page_values );
				if ( is_numeric( $option_all_value ) ) {
					$option_all_id = (integer) $option_all_value;
					$option_all_slug = sanitize_title( $arguments['option_all_text'] );
				} else {
					$option_all_slug = sanitize_title( $option_all_value );
				}
			}
		}

		$option_any_terms_id = -2;
		$option_any_terms_slug = 'any.terms.assigned';
		if ( $add_any_terms_option ) {
			if ( ! empty( $arguments['option_any_terms_value'] ) ) {
				$option_any_terms_value = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['option_any_terms_value'], $page_values );
				if ( is_numeric( $option_any_terms_value ) ) {
					$option_any_terms_id = (integer) $option_any_terms_value;
					$option_any_terms_slug = sanitize_title( $arguments['option_any_terms_text'] );
				} else {
					$option_any_terms_slug = sanitize_title( $option_any_terms_value );
				}
			}
		}

		$option_no_terms_id = -1;
		$option_no_terms_slug = 'no.terms.assigned';
		if ( $add_no_terms_option ) {
			if ( ! empty( $arguments['option_no_terms_value'] ) ) {
				$option_no_terms_value = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['option_no_terms_value'], $page_values );
				if ( is_numeric( $option_no_terms_value ) ) {
					$option_no_terms_id = (integer) $option_no_terms_value;
					$option_no_terms_slug = sanitize_title( $arguments['option_no_terms_text'] );
				} else {
					$option_no_terms_slug = sanitize_title( $option_no_terms_value );
				}
			}
		}

		if ( $is_hierarchical ) {
			$tags = self::_get_term_tree( $tags, $arguments );

			if ( is_wp_error( $tags ) ) {
				$list =  '<strong>' . __( 'ERROR', 'media-library-assistant' ) . ': ' . $tags->get_error_message() . '</strong>, ' . $tags->get_error_data( $tags->get_error_code() );

				if ( 'array' === $arguments['mla_output'] ) {
					return array( $list );
				}

				if ( empty($arguments['echo']) ) {
					return $list;
				}

				echo $list; // phpcs:ignore
				return;
			}

			if ( isset( $tags['found_rows'] ) ) {
				$found_rows = $tags['found_rows'];
				unset( $tags['found_rows'] );
			} else {
				$found_rows = count( $tags );
			}

			if ( ( 0 === $found_rows ) && !empty( $arguments['mla_control_name'] ) ) {
				// Remove the current item from the parameters to prevent "stale" [mla_gallery] content
				$mla_control_name = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_control_name'], $page_values );
	
				// Does not handle default 'tax_input[[+taxonomy+]][]' values
				unset( $_REQUEST[ $mla_item_parameter ] );
				unset( $_REQUEST[ $mla_control_name ] );
			}
		} else {
			if ( !$show_empty ) {
				foreach ( $tags as $key => $tag ) {
					$tags[ $key ]->level = 0;
					$link = get_edit_tag_link( $tag->term_id, $tag->taxonomy );
					if ( ! is_wp_error( $link ) ) {
						$tags[ $key ]->edit_link = $link;
						$link = get_term_link( (int) $tag->term_id, $tag->taxonomy );
						$tags[ $key ]->term_link = $link;
					}

					if ( is_wp_error( $link ) ) {
						$list =  '<strong>' . __( 'ERROR', 'media-library-assistant' ) . ': ' . $link->get_error_message() . '</strong>, ' . $link->get_error_data( $link->get_error_code() );

						if ( 'array' === $arguments['mla_output'] ) {
							return array( $list );
						}

						if ( empty($arguments['echo']) ) {
							return $list;
						}

						echo $list; // phpcs:ignore
						return;
					}

					if ( 'edit' === $arguments['link'] ) {
						$tags[ $key ]->link = $tags[ $key ]->edit_link;
					} else {
						$tags[ $key ]->link = $tags[ $key ]->term_link;
					}
				} // foreach tag
			} // !show_empty
		}// !is_hierarchical

		if ( $add_all_option ) {
			$found_rows += 1;
		}
		if ( $add_any_terms_option ) {
			$found_rows += 1;
		}
		if ( $add_no_terms_option ) {
			$found_rows += 1;
		}

		$style_values = array_merge( $page_values, array(
			'mla_output' => $arguments['mla_output'],
			'mla_style' => $arguments['mla_style'],
			'mla_markup' => $arguments['mla_markup'],
			'taxonomy' => implode( '-', $arguments['taxonomy'] ),
			'current_item' => $arguments['current_item'],
			'itemtag' => tag_escape( $arguments['itemtag'] ),
			'termtag' => tag_escape( $arguments['termtag'] ),
			'captiontag' => tag_escape( $arguments['captiontag'] ),
			'multiple' => $arguments['mla_multi_select'] ? 'multiple' : '',
			'itemtag_attributes' => '',
			'itemtag_class' => 'term-list term-list-taxonomy-' . implode( '-', $arguments['taxonomy'] ), 
			'itemtag_id' => $page_values['selector'],
			'all_found_rows' => $found_rows,
			'found_rows' => $found_rows,
			'separator' => $arguments['separator'],
			'single_text' => $arguments['single_text'],
			'multiple_text' => $arguments['multiple_text'],
			'echo' => $arguments['echo'],
			'link' => $arguments['link']
		) );

		$style_template = $gallery_style = '';
		$use_mla_term_list_style = 'none' != strtolower( $style_values['mla_style'] );
		if ( apply_filters( 'use_mla_term_list_style', $use_mla_term_list_style, $style_values['mla_style'] ) ) {
			$style_template = MLATemplate_support::mla_fetch_custom_template( $style_values['mla_style'], 'term-list', 'style' );
			if ( empty( $style_template ) ) {
				$style_values['mla_style'] = $default_style;
				$style_template = MLATemplate_support::mla_fetch_custom_template( $default_style, 'term-list', 'style' );
			}

			if ( ! empty ( $style_template ) ) {
				// Look for 'query' and 'request' substitution parameters
				$style_values = MLAData::mla_expand_field_level_parameters( $style_template, $attr, $style_values );

				$style_values = apply_filters( 'mla_term_list_style_values', $style_values );
				$style_template = apply_filters( 'mla_term_list_style_template', $style_template );
				$gallery_style = MLAData::mla_parse_template( $style_template, $style_values );
				$gallery_style = apply_filters( 'mla_term_list_style_parse', $gallery_style, $style_template, $style_values );
			} // !empty template
		} // use_mla_term_list_style

		$list .= $gallery_style;
		$markup_values = $style_values;

		if ( empty( $arguments['mla_control_name'] ) ) {
			$mla_control_name = 'tax_input[[+taxonomy+]][]';
		} else {
			$mla_control_name = $arguments['mla_control_name'];;
		}

		// Accumulate links for flat and array output
		$tag_links = array();

		if ( $is_hierarchical ) {
			if ( $combine_hierarchical ) {
				$combined_tags = array();
				foreach( $tags as $taxonomy => $root_terms ) {
					$combined_tags = array_merge( $combined_tags, $root_terms );
				}
				$tags = array( $markup_values['taxonomy'] => $combined_tags );
			} // $combine_hierarchical

			foreach( $tags as $taxonomy => $root_terms ) {
				$markup_values['taxonomy'] = $taxonomy;
				$markup_values['thename'] = MLAShortcode_Support::mla_process_shortcode_parameter( $mla_control_name, $markup_values );


				// Add the optional 'all-terms', 'any-terms' and/or 'no-terms' option(s), if requested
				$add_to_found_rows = 0;
				if ( $add_any_terms_option ) {
					$new_term = ( object ) array(
						'term_id' => $option_any_terms_id,
						'name' => $arguments['option_any_terms_text'],
						'slug' => $option_any_terms_slug,
						'term_group' => '0',
						'term_taxonomy_id' => $option_any_terms_id,
						'taxonomy' => $taxonomy,
						'description' => '',
						'parent' => '0',
						'count' => $terms_assigned_counts['any.terms.assigned'],
						'level' => 0,
						'edit_link' => '',
						'term_link' => '',
						'link' => '',
					);

					array_unshift( $root_terms, $new_term );
					$add_to_found_rows += 1;
				}

				if ( $add_no_terms_option ) {
					$new_term = ( object ) array(
						'term_id' => $option_no_terms_id,
						'name' => $arguments['option_no_terms_text'],
						'slug' => $option_no_terms_slug,
						'term_group' => '0',
						'term_taxonomy_id' => $option_no_terms_id,
						'taxonomy' => $taxonomy,
						'description' => '',
						'parent' => '0',
						'count' => $terms_assigned_counts['no.terms.assigned'],
						'level' => 0,
						'edit_link' => '',
						'term_link' => '',
						'link' => '',
					);

					array_unshift( $root_terms, $new_term );
					$add_to_found_rows += 1;
				}

				if ( $add_all_option ) {
					$new_term = ( object ) array(
						'term_id' => $option_all_id,
						'name' => $arguments['option_all_text'],
						'slug' => $option_all_slug,
						'term_group' => '0',
						'term_taxonomy_id' => $option_all_id,
						'taxonomy' => $taxonomy,
						'description' => '',
						'parent' => '0',
						'count' => $terms_assigned_counts['ignore.terms.assigned'],
						'level' => 0,
						'edit_link' => '',
						'term_link' => '',
						'link' => '',
					);

					array_unshift( $root_terms, $new_term );
					$add_to_found_rows += 1;
				}

				if ( isset( $root_terms['found_rows'] ) ) {
					$markup_values['found_rows'] = $add_to_found_rows + $root_terms['found_rows'];
					unset( $root_terms['found_rows'] );
				} else {
					$markup_values['found_rows'] = count( $root_terms );
				}

				if ( count( $root_terms ) ) {
					self::_compose_term_list( $list, $tag_links, $root_terms, $markup_values, $arguments, $attr );
				}
			}
		} else {
			$markup_values['thename'] = MLAShortcode_Support::mla_process_shortcode_parameter( $mla_control_name, $markup_values );

			// Add the optional 'all-terms', 'any-terms' and/or 'no-terms' option(s), if requested
			if ( $add_any_terms_option ) {
				$new_term = ( object ) array(
					'term_id' => $option_any_terms_id,
					'name' => $arguments['option_any_terms_text'],
					'slug' => $option_any_terms_slug,
					'term_group' => '0',
					'term_taxonomy_id' => $option_any_terms_id,
					'taxonomy' => $taxonomy,
					'description' => '',
					'parent' => '0',
					'count' => -1,
					'level' => 0,
					'edit_link' => '',
					'term_link' => '',
					'link' => '',
				);

				array_unshift( $tags, $new_term );
			}

			if ( $add_no_terms_option ) {
				$new_term = ( object ) array(
					'term_id' => $option_no_terms_id,
					'name' => $arguments['option_no_terms_text'],
					'slug' => $option_no_terms_slug,
					'term_group' => '0',
					'term_taxonomy_id' => $option_no_terms_id,
					'taxonomy' => $taxonomy,
					'description' => '',
					'parent' => '0',
					'count' => -1,
					'level' => 0,
					'edit_link' => '',
					'term_link' => '',
					'link' => '',
				);

				array_unshift( $tags, $new_term );
			}

			if ( $add_all_option ) {
				$new_term = ( object ) array(
					'term_id' => $option_all_id,
					'name' => $arguments['option_all_text'],
					'slug' => $option_all_slug,
					'term_group' => '0',
					'term_taxonomy_id' => $option_all_id,
					'taxonomy' => $taxonomy,
					'description' => '',
					'parent' => '0',
					'count' => -1,
					'level' => 0,
					'edit_link' => '',
					'term_link' => '',
					'link' => '',
				);

				array_unshift( $tags, $new_term );
			}

			if ( count( $tags ) ) {
				self::_compose_term_list( $list, $tag_links, $tags, $markup_values, $arguments, $attr );
			}
		}

		if ( 'array' === $arguments['mla_output'] || empty($arguments['echo']) ) {
			return $list;
		}

		echo $list; // phpcs:ignore
	} // mla_term_list
	
	/**
	 * The MLA Term List shortcode.
	 *
	 * This is an interface to the mla_term_list function.
	 *
	 * @since 3.13
	 *
	 * @param array $attr Attributes of the shortcode.
	 * @param string $content Optional content for enclosing shortcodes
	 *
	 * @return string HTML content to display the term list.
	 */
	public static function mla_term_list_shortcode( $attr, $content = NULL ) {
//error_log( __LINE__ . " mla_term_list_shortcode() _REQUEST = " . var_export( $_REQUEST, true ), 0 );
//error_log( __LINE__ . " mla_term_list_shortcode() attr = " . var_export( $attr, true ), 0 );
//error_log( __LINE__ . " mla_term_list_shortcode() content = " . var_export( $content, true ), 0 );
		/*
		 * Make sure $attr is an array, even if it's empty,
		 * and repair damage caused by link-breaks in the source text
		 */
		$attr = MLAShortcode_Support::mla_validate_attributes( $attr, $content );

		// The 'array' format makes no sense in a shortcode
		if ( isset( $attr['mla_output'] ) && 'array' === $attr['mla_output'] ) {
			$attr['mla_output'] = 'flat';
		}
			 
		// A shortcode must return its content to the caller, so "echo" makes no sense
		$attr['echo'] = false;

		if ( ! empty( $attr['mla_output'] ) ) {
			switch ( $attr['mla_output'] ) {
				case 'wp_list_categories':
					return wp_list_categories( $attr );
				case 'wp_dropdown_categories':
					return wp_dropdown_categories( $attr );
				case 'wp_terms_checklist':
					require_once( ABSPATH . 'wp-admin/includes/template.php' );
					return wp_terms_checklist( 0, $attr );
			}
		}

		return self::mla_term_list( $attr );
	} // mla_term_list_shortcode

	/**
	 * Walk a list of terms and find hierarchy, preserving source order.
	 *
	 * @since 3.13
	 *
	 * @param	array	$terms Term objects, by reference
	 * @param	array	$arguments Shortcode arguments, including defaults
	 *
	 * @return	array	( [taxonomy] => array( [root terms] => array( [fields], array( 'children' => [child terms] )
	 */
	private static function _get_term_tree( &$terms, $arguments = array() ) {
		$term = current( $terms );

		if ( empty( $term ) or ! isset( $term->parent ) ) {
			return array();
		}

		// Set found_rows aside to be restored later
		if ( isset( $terms['found_rows'] ) ) {
			$found_rows = $terms['found_rows'];
			unset( $terms['found_rows'] );
		} else {
			$found_rows = NULL;
		}

		$child_of = ! empty( $arguments['child_of'] ) ? absint( $arguments['child_of'] ) : NULL;
		$include_tree = ! empty( $arguments['include_tree'] ) ? wp_parse_id_list( $arguments['include_tree'] ) : NULL;
		$exclude_tree = empty( $include_tree ) && ! empty( $arguments['exclude_tree'] ) ? wp_parse_id_list( $arguments['exclude_tree'] ) : NULL;

		$depth = ! empty( $arguments['depth'] ) ? absint( $arguments['depth'] ) : 0;
		$term_tree = array();
		$root_ids = array();
		$parents = array();
		$child_ids = array();
		foreach( $terms as $index => $term ) {
			// Preserve order for sorting later
			$term->original_index = $index;

			// TODO Make this conditional on $arguments['link']
			$link = get_edit_tag_link( $term->term_id, $term->taxonomy );
			if ( ! is_wp_error( $link ) ) {
				$term->edit_link = $link;
				$link = get_term_link( (int) $term->term_id, $term->taxonomy );
				$term->term_link = $link;
			}

			if ( is_wp_error( $link ) ) {
				return $link;
			}

			if ( 'edit' === $arguments['link'] ) {
				$term->link = $term->edit_link;
			} else {
				$term->link = $term->term_link;
			}

			$term->children = array();
			$parent = absint( $term->parent );
			if ( 0 === $parent ) {
				$term_tree[ $term->taxonomy ][] = $term;
				$root_ids[ $term->taxonomy ][ $term->term_id ] = count( $term_tree[ $term->taxonomy ] ) - 1;
			} else {
				$parents[ $term->taxonomy ][ $term->parent ][] = $term;
				$child_ids[ $term->taxonomy ][ $term->term_id ] = absint( $term->parent );
			}
		}

		// Collapse multi-level children
		foreach ( $parents as $taxonomy => $tax_parents ) {
			if ( ! isset( $term_tree[ $taxonomy ] ) ) {
				$term_tree[ $taxonomy ] = array();
				$root_ids[ $taxonomy ] = array();
			}

			while ( ! empty( $tax_parents ) ) {
				foreach( $tax_parents as $parent_id => $children ) {
					foreach( $children as $index => $child ) {
						if ( ! array_key_exists( $child->term_id, $tax_parents ) ) {

							if ( array_key_exists( $child->parent, $root_ids[ $taxonomy ] ) ) {
								// Found a root node - attach the leaf
								$term_tree[ $taxonomy ][ $root_ids[ $taxonomy ][ $child->parent ] ]->children[] = $child;
							} elseif ( isset( $child_ids[ $taxonomy ][ $child->parent ] ) ) {
								// Found a non-root parent node - attach the leaf
								$the_parent = $child_ids[ $taxonomy ][ $child->parent ];
								foreach( $tax_parents[ $the_parent ] as $candidate_index => $candidate ) {
									if ( $candidate->term_id === $child->parent ) {
										$parents[ $taxonomy ][ $the_parent ][ $candidate_index ]->children[] = $child;
										break;
									}
								} // foreach candidate
							} else {
								// No parent exists; make this a root node
								$term_tree[ $taxonomy ][] = $child;
								$root_ids[ $taxonomy ][ $child->term_id ] = count( $term_tree[ $taxonomy ] ) - 1;
							} // Move the leaf node

							unset( $tax_parents[ $parent_id ][ $index ] );
							if ( empty( $tax_parents[ $parent_id ] ) ) {
								unset( $tax_parents[ $parent_id ] );
							}
						} // leaf node; no children
					} // foreach child
				} // foreach parent_id
			} // has parents
		} // foreach taxonomy

		// Calculate and potentially trim parent/child tree
		$all_terms_count = 0;
		foreach ( array_keys( $term_tree ) as $taxonomy ) {
			if ( $include_tree ) {
				$result = self::_find_include_tree( $term_tree[ $taxonomy ], $include_tree );
				if ( false !== $result ) {
					$term_tree[ $taxonomy ] = $result;
				} else {
					$term_tree[ $taxonomy ] = array();
					continue;
				}
			} // $include_tree

			if ( $exclude_tree ) {
				self::_remove_exclude_tree( $term_tree[ $taxonomy ], $exclude_tree );
			}

			if ( $child_of ) {
				$result = self::_find_child_of( $term_tree[ $taxonomy ], $child_of );

				if ( false !== $result ) {
					$term_tree[ $taxonomy ] = $result->children;
				} else {
					$term_tree[ $taxonomy ] = array();
					continue;
				}
			} // $child_of

			$term_count = 0;
			$root_limit = count( $term_tree[ $taxonomy ] );

			if ( $root_limit ) {
				for ( $root_index = 0; $root_index < $root_limit; $root_index++ ) {
					if ( isset( $term_tree[ $taxonomy ][ $root_index ] ) ) {
						$term_count++;
						$term_tree[ $taxonomy ][ $root_index ]->level = 0;
						if ( ! empty( $term_tree[ $taxonomy ][ $root_index ]->children ) ) {
							$term_count += self::_count_term_children( $term_tree[ $taxonomy ][ $root_index ], $depth );
						}
					} else {
						$root_limit++;
					}
				}
			}

			// Restore original sort order
			$term_tree[ $taxonomy ] = self::_sort_by_original_index( $term_tree[ $taxonomy ] );

			$term_tree[ $taxonomy ]['found_rows'] = $term_count;
			$all_terms_count += $term_count;
		} // foreach taxonomy

		$term_tree['found_rows'] = $all_terms_count;
		return $term_tree;
	} // _get_term_tree

	/**
	 * Sort an array of terms (and their children) by original source order
	 *
	 * @since 3.13
	 *
	 * @param	array	$unsorted_tree unsorted term array, by reference
	 *
	 * @return	array	term array sorted by original source order
	 */
	private static function _sort_by_original_index( &$unsorted_tree ) {
		$sorted_tree = array();

		foreach ( $unsorted_tree as $unsorted_term ) {
			if ( ! empty( $unsorted_term->children ) ) {
				$unsorted_term->children = self::_sort_by_original_index( $unsorted_term->children );
			}

			$sorted_tree[ $unsorted_term->original_index ] = $unsorted_term;
		}

		ksort( $sorted_tree );		
		return $sorted_tree;
	} // _sort_by_original_index

	/**
	 * Find a term that matches $child_of
	 *
	 * @since 3.13
	 *
	 * @param	array	$parents Potential parent Term objects, by reference
	 * @param	integer	$parent_id Term_id of the desired parent
	 *
	 * @return	mixed	Term object of the desired parent else false
	 */
	private static function _find_child_of( &$parents, $parent_id ) {
		foreach( $parents as $parent ) {
			if ( $parent_id === (integer) $parent->term_id ) {
				return $parent;
			}

			$result = self::_find_child_of( $parent->children, $parent_id );
			if ( false !== $result ) {
				return $result;
			}
		}

		return false;
	} // _find_child_of

	/**
	 * Find the term(s) that match $include_tree
	 *
	 * @since 3.13
	 *
	 * @param	array	$terms Potential term objects, by reference
	 * @param	array	$include_tree term_id(s) of the desired terms
	 *
	 * @return	mixed	Term object(s) of the desired terms else false
	 */
	private static function _find_include_tree( &$terms, $include_tree ) {
		$new_tree = array();

		foreach( $terms as $term ) {
			if ( in_array( $term->term_id, $include_tree ) ) {
				$new_tree[] = $term;
			} elseif ( ! empty( $term->children ) ) {
				$result = self::_find_include_tree( $term->children, $include_tree );
				if ( false !== $result ) {
					$new_tree = array_merge( $new_tree, $result );
				}
			}
		}

		if ( empty( $new_tree ) ) {		
			return false;
		}

		return $new_tree;
	} // _find_include_tree

	/**
	 * Remove the term(s) that match $exclude_tree
	 *
	 * @since 3.13
	 *
	 * @param	array	$terms Potential term objects, by reference
	 * @param	array	$exclude_tree term_id(s) of the desired terms
	 *
	 * @return	void	Term object(s) are removed from the &parents array
	 */
	private static function _remove_exclude_tree( &$terms, $exclude_tree ) {
		foreach( $terms as $index => $term ) {
			if ( in_array( $term->term_id, $exclude_tree ) ) {
				unset( $terms[ $index ] );
			} elseif ( ! empty( $term->children ) ) {
				self::_remove_exclude_tree( $term->children, $exclude_tree );
			}
		}
	} // _remove_exclude_tree

	/**
	 * Add level to term children and count them, all levels.
	 *
	 * Recalculates term counts by including items from child terms. Assumes all
	 * relevant children are already in the $terms argument.
	 *
	 * @since 3.13
	 *
	 * @param	object	$parent Parent Term objects, by reference
	 * @param	integer	$depth Maximum depth of parent/child relationship
	 *
	 * @return	integer	Total number of children, all levels
	 */
	private static function _count_term_children( &$parent, $depth = 0 ) {
		$term_count = 0;
		$child_level = $parent->level + 1;

		// level is zero-based, depth is one-based
		if ( $depth && $child_level >= $depth ) {
			$parent->children = array();
			return 0;
		}

		$child_limit = count( $parent->children );
		for ( $child_index = 0; $child_index < $child_limit; $child_index++ ) {
				if ( isset( $parent->children[ $child_index ] ) ) {
					$term_count++;
					$parent->children[ $child_index ]->level = $child_level;
					if ( ! empty( $parent->children[ $child_index ]->children ) ) {
						$term_count += self::_count_term_children( $parent->children[ $child_index ], $depth );
					}
				} else {
					$child_limit++;
				}
			}

		return $term_count;
	} // _count_term_children
} // Class MLATermList
?>