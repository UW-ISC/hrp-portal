<?php
/**
 * Media Library Assistant Tag Cloud Shortcode
 *
 * @package Media Library Assistant
 * @since 3.13
 */

/**
 * Class MLA (Media Library Assistant) Tag Cloud Shortcode implements
 * the [mla_tag_cloud] shortcode.
 *
 * @package Media Library Assistant
 * @since 3.13
 */
class MLATagCloud {
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
	private static $valid_mla_output_values = array( 'flat', 'list', 'ulist', 'olist', 'dlist', 'grid', 'array', 'next_link', 'current_link', 'previous_link', 'next_page', 'previous_page', 'paginate_links' );

	/**
	 * Valid mla_output pagination values
	 *
	 * @since 3.13
	 *
	 * @var	array
	 */
	private static $valid_mla_output_pagination_values = array( 'previous_link', 'current_link', 'next_link', 'next_page', 'previous_page', 'paginate_links' );

	/**
	 * The MLA Tag Cloud support function.
	 *
	 * This is an alternative to the WordPress wp_tag_cloud function, with additional
	 * options to customize the hyperlink behind each term.
	 *
	 * @since 2.20
	 *
	 * @param array $attr Attributes of the shortcode.
	 *
	 * @return void|string|string[] Void if 'echo' attribute is true, or on failure. Otherwise, tag cloud markup as a string or an array of links, depending on 'mla_output' attribute.
	 */
	public static function mla_tag_cloud( $attr ) {
		global $post;

		// Some do_shortcode callers may not have a specific post in mind
		if ( ! is_object( $post ) ) {
			$post = MLAShortcode_Support::mla_get_default_post();
		}

		// $instance supports multiple clouds in one page/post	
		static $instance = 0;
		$instance++;

		// Some values are already known, and can be used in data selection parameters
		$upload_dir = wp_upload_dir();
		$page_values = array(
			'instance' => $instance,
			'selector' => "mla_tag_cloud-{$instance}",
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

		// These are the default parameters for tag cloud display
		$mla_item_specific_arguments = array(
			'mla_link_attributes' => '',
			'mla_link_class' => '',
			'mla_link_style' => '',
			'mla_link_href' => '',
			'mla_link_text' => '',
			'mla_nolink_text' => '',
			'mla_rollover_text' => '',
			'mla_caption' => '',
			'mla_item_value' => '',
		);

		$defaults = array_merge(
			MLAShortcode_Support::$mla_get_terms_parameters,
			array(
			'smallest' => 8,
			'largest' => 22,
			'default_size' => 12,
			'unit' => 'pt',
			'separator' => "\n",
			'single_text' => '%s item',
			'multiple_text' => '%s items',

			'echo' => false,
			'link' => 'view',
			'current_item' => '',
			'current_item_class' => 'mla_current_item',

			'itemtag' => 'ul',
			'termtag' => 'li',
			'captiontag' => '',
			'columns' => MLACore::mla_get_option('mla_tag_cloud_columns'),

			'mla_output' => 'flat',
			'mla_style' => NULL,
			'mla_markup' => NULL,
			'mla_float' => is_rtl() ? 'right' : 'left',
			'mla_itemwidth' => MLACore::mla_get_option('mla_tag_cloud_itemwidth'),
			'mla_margin' => MLACore::mla_get_option('mla_tag_cloud_margin'),
			'mla_target' => '',
			'mla_debug' => false,

			'option_all_text' => '',
			'option_all_value' => NULL,
			'option_no_terms_text' => '',
			'option_no_terms_value' => NULL,
			'option_any_terms_text' => '',
			'option_any_terms_value' => NULL,

			// Pagination parameters
			'term_id' => NULL,
			'mla_end_size'=> 1,
			'mla_mid_size' => 2,
			'mla_prev_text' => '&laquo; ' . __( 'Previous', 'media-library-assistant' ),
			'mla_next_text' => __( 'Next', 'media-library-assistant' ) . ' &raquo;',
			'mla_page_parameter' => 'mla_cloud_current',
			'mla_cloud_current' => 1,
			'mla_paginate_total' => NULL,
			'mla_paginate_type' => 'plain'),

			$mla_item_specific_arguments
		);

		// Filter the attributes before $mla_page_parameter and "request:" prefix processing.
		$attr = apply_filters( 'mla_tag_cloud_raw_attributes', $attr );

		/*
		 * The mla_paginate_current parameter can be changed to support
		 * multiple clouds per page.
		 */
		if ( ! isset( $attr['mla_page_parameter'] ) ) {
			$attr['mla_page_parameter'] = $defaults['mla_page_parameter'];
		}

		// The mla_page_parameter can contain page_level parameters like {+page_ID+}
		$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr['mla_page_parameter'] ) );
		$mla_page_parameter = MLAData::mla_parse_template( $attr_value, $page_values );
		 
		/*
		 * Special handling of mla_page_parameter to make "MLA pagination" easier.
		 * Look for this parameter in $_REQUEST if it's not present in the shortcode itself.
		 */
		if ( ! isset( $attr[ $mla_page_parameter ] ) ) {
			if ( isset( $_REQUEST[ $mla_page_parameter ] ) ) {
				$attr[ $mla_page_parameter ] = sanitize_text_field( wp_unslash( $_REQUEST[ $mla_page_parameter ] ) );
			}
		}
		 
		// Special handling of current_item; look for this parameter in $_REQUEST if it's not present in the shortcode itself.
		if ( ! isset( $attr['current_item'] ) ) {
			if ( isset( $_REQUEST['current_item'] ) ) {
				$attr['current_item'] = sanitize_text_field( wp_unslash( $_REQUEST['current_item'] ) );
			}
		}
		 
		// Determine markup template to get default arguments
		$arguments = shortcode_atts( $defaults, $attr );
		if ( $arguments['mla_markup'] ) {
			$template = $arguments['mla_markup'];
			if ( ! MLATemplate_Support::mla_fetch_custom_template( $template, 'tag-cloud', 'markup', '[exists]' ) ) {
				$template = NULL;
			}
		} else {
			$template = NULL;
		}

		if ( empty( $template ) ) {
			$output_parameters = array_map( 'strtolower', array_map( 'trim', explode( ',', $arguments['mla_output'] ) ) );

			if ( !in_array( $output_parameters[0], array_merge( self::$valid_mla_output_values, self::$valid_mla_output_pagination_values ) ) ) {
				$output_parameters[0] = 'flat';
			}

			if ( 'grid' === $output_parameters[0] ) {
				$template = MLACore::mla_get_option('default_tag_cloud_markup');
			} elseif ( in_array( $output_parameters[0], array( 'list', 'ulist', 'olist', 'dlist' ) ) ) {
				if ( ( 'dlist' === $output_parameters[0] ) || ('list' === $output_parameters[0] && 'dd' === $arguments['captiontag'] ) ) {
					$template = 'tag-cloud-dl';
				} else {
					$template = 'tag-cloud-ul';
				}
			} else {
				$template = NULL;
			}
		}

		// Apply default arguments set in the markup template
		if ( ! empty( $template ) ) {
			$arguments = MLATemplate_Support::mla_fetch_custom_template( $template, 'tag-cloud', 'markup', 'arguments' );
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
			if ( array_key_exists( $attr_key, $mla_item_specific_arguments ) ) {
				continue;
			}

			$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr_value ) );
			$replacement_values = MLAData::mla_expand_field_level_parameters( $attr_value, $attr, $page_values );
			$attr[ $attr_key ] = MLAData::mla_parse_template( $attr_value, $replacement_values );
		}

		$attr = apply_filters( 'mla_tag_cloud_attributes', $attr );
		$arguments = shortcode_atts( $defaults, $attr );

		/*
		 * $mla_page_parameter, if non-default, doesn't make it through the shortcode_atts
		 * filter, so we handle it separately
		 */
		if ( empty( $arguments[ $mla_page_parameter ] ) ) {
			if ( !empty( $attr[ $mla_page_parameter ] ) ) {
				$arguments[ $mla_page_parameter ] = $attr[ $mla_page_parameter ];
			} else {
				$arguments[ $mla_page_parameter ] = $defaults['mla_cloud_current'];

			}
		}

		// Process the pagination parameter, if present
		if ( isset( $arguments[ $mla_page_parameter ] ) ) {
			$arguments['offset'] = absint( $arguments['limit'] ) * ( absint( $arguments[ $mla_page_parameter ] ) - 1);
		}

		// Using the slug is a common practice and affects current_item
		$current_is_slug = in_array( $arguments['mla_item_value'], array( '{+slug+}', '[+slug+]' ) );

		// Clean up the current_item to separate term_id from slug
//		if ( ! empty( $arguments['current_item'] ) && is_numeric( $arguments['current_item'] ) ) {
		if ( ! ( empty( $arguments['current_item'] ) || $current_is_slug ) ) {
			$arguments['current_item'] = (integer) $arguments['current_item'];
		}

		$arguments = apply_filters( 'mla_tag_cloud_arguments', $arguments );

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
			if ( !MLATemplate_Support::mla_fetch_custom_template( $arguments['mla_style'], 'tag-cloud', 'style', '[exists]' ) ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>mla_tag_cloud mla_style</strong> "' . $arguments['mla_style'] . '" ' . __( 'not found', 'media-library-assistant' ), MLACore::MLA_DEBUG_CATEGORY_ANY );
				$arguments['mla_style'] = NULL;
			}
		}

		if ( $arguments['mla_markup'] ) {
			if ( !MLATemplate_Support::mla_fetch_custom_template( $arguments['mla_markup'], 'tag-cloud', 'markup', '[exists]' ) ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>mla_tag_cloud mla_markup</strong> "' . $arguments['mla_markup'] . '" ' . __( 'not found', 'media-library-assistant' ), MLACore::MLA_DEBUG_CATEGORY_ANY );
				$arguments['mla_markup'] = NULL;
			}
		}

		$output_parameters = array_map( 'strtolower', array_map( 'trim', explode( ',', $arguments['mla_output'] ) ) );

		if ( !in_array( $output_parameters[0], array_merge( self::$valid_mla_output_values, self::$valid_mla_output_pagination_values ) ) ) {
			$output_parameters[0] = 'flat';
		}

		if ( $is_grid = 'grid' === $output_parameters[0] ) {
			$default_style = MLACore::mla_get_option('default_tag_cloud_style');
			$default_markup = MLACore::mla_get_option('default_tag_cloud_markup');

			if ( NULL === $arguments['mla_style'] ) {
				$arguments['mla_style'] = $default_style;
			}

			if ( NULL === $arguments['mla_markup'] ) {
				$arguments['mla_markup'] = $default_markup;
			}

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

		if ( $is_list = in_array( $output_parameters[0], array( 'list', 'ulist', 'olist', 'dlist' ) ) ) {
			$default_style = 'none';

			if ( 'list' === $output_parameters[0] && 'dd' === $arguments['captiontag'] ) {
				$default_markup = 'tag-cloud-dl';
				$arguments['itemtag'] = 'dl';
				$arguments['termtag'] = 'dt';
			} else {
				$default_markup = 'tag-cloud-ul';
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
						$default_markup = 'tag-cloud-dl';
						$arguments['itemtag'] = 'dl';
						$arguments['termtag'] = 'dt';
						$arguments['captiontag'] = 'dd';
					break;
					default:
						$arguments['itemtag'] = 'ul';
				}
			}

			if ( NULL === $arguments['mla_style'] ) {
				$arguments['mla_style'] = $default_style;
			}

			if ( NULL === $arguments['mla_markup'] ) {
				$arguments['mla_markup'] = $default_markup;
			}
		}

		$is_pagination = in_array( $output_parameters[0], self::$valid_mla_output_pagination_values ); 

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

		$tags = MLAShortcode_Support::mla_get_terms( $arguments );

		// Invalid taxonomy names return WP_Error
		if ( is_wp_error( $tags ) ) {
			$cloud =  '<strong>' . __( 'ERROR', 'media-library-assistant' ) . ': ' . $tags->get_error_message() . '</strong>, ' . $tags->get_error_data( $tags->get_error_code() );

			if ( 'array' === $arguments['mla_output'] ) {
				return array( $cloud );
			}

			if ( empty($arguments['echo']) ) {
				return $cloud;
			}

			echo $cloud; // phpcs:ignore
			return;
		}

		if ( ! ( empty( $arguments['option_any_terms_text'] ) && empty( $arguments['option_no_terms_text'] ) && empty( $arguments['option_all_text'] ) ) ) {
			$terms_assigned_counts = MLAShortcode_Support::mla_get_all_none_term_counts( $arguments );
		} else {
			$terms_assigned_counts = array( 'ignore.terms.assigned' => 0, 'no.terms.assigned' => 0, 'any.terms.assigned' => 0 );
		}

		// Fill in the item_specific link properties, calculate cloud parameters
		if ( isset( $tags['found_rows'] ) ) {
			$found_rows = $tags['found_rows'];
			unset( $tags['found_rows'] );
		} else {
			$found_rows = count( $tags );
		}

		if ( 0 === $found_rows ) {
			if ( self::$mla_debug ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug empty cloud', 'media-library-assistant' ) . '</strong>, query = ' . var_export( $arguments, true ) );
				$cloud = MLACore::mla_debug_flush();

				if ( '<p></p>' === $cloud ) {
					$cloud = '';
				}
			} else {
				$cloud = '';
			}

			$cloud .= wp_kses( $arguments['mla_nolink_text'], 'post' );
			if ( 'array' === $arguments['mla_output'] ) {
				if ( empty( $cloud ) ) {
					return array();
				} else {
					return array( $cloud );
				}
			}

			if ( empty($arguments['echo']) ) {
				return $cloud;
			}

			echo $cloud; // phpcs:ignore
			return;
		} // Empty cloud

		if ( self::$mla_debug ) {
			$cloud = MLACore::mla_debug_flush();
		} else {
			$cloud = '';
		}

		$min_count = 0x7FFFFFFF;
		$max_count = 0;
		$min_scaled_count = 0x7FFFFFFF;
		$max_scaled_count = 0;
		foreach ( $tags as $key => $tag ) {
			$tag_count = isset ( $tag->count ) ? $tag->count : 0;
			$tag->scaled_count = apply_filters( 'mla_tag_cloud_scale', round(log10($tag_count + 1) * 100), $attr, $arguments, $tag );

			if ( $tag_count < $min_count ) {
				$min_count = $tag_count;
			}

			if ( $tag_count > $max_count ) {
				$max_count = $tag_count;
			}

			if ( $tag->scaled_count < $min_scaled_count ) {
				$min_scaled_count = $tag->scaled_count;
			}

			if ( $tag->scaled_count > $max_scaled_count ) {
				$max_scaled_count = $tag->scaled_count;
			}

			$link = get_edit_tag_link( $tag->term_id, $tag->taxonomy );
			if ( ! is_wp_error( $link ) ) {
				$tags[ $key ]->edit_link = $link;
				$link = get_term_link( (int) $tag->term_id, $tag->taxonomy );
				$tags[ $key ]->term_link = $link;
			}

			if ( is_wp_error( $link ) ) {
				$cloud =  '<strong>' . __( 'ERROR', 'media-library-assistant' ) . ': ' . $link->get_error_message() . '</strong>, ' . $link->get_error_data( $link->get_error_code() );

				if ( 'array' === $arguments['mla_output'] ) {
					return array( $cloud );
				}

				if ( empty($arguments['echo']) ) {
					return $cloud;
				}

				echo $cloud; // phpcs:ignore
				return;
			}

			if ( 'edit' === $arguments['link'] ) {
				$tags[ $key ]->link = $tags[ $key ]->edit_link;
			} else {
				$tags[ $key ]->link = $tags[ $key ]->term_link;
			}
		} // foreach tag

		// Add the optional 'all-terms', 'any-terms' and/or 'no-terms' option(s), if requested
		if ( ! empty( $arguments['option_any_terms_text'] ) ) {
			$new_term_id = -2;
			$new_term_slug = 'any.terms.assigned';

			if ( ! empty( $arguments['option_any_terms_value'] ) ) {
				$new_term_value = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['option_any_terms_value'], $page_values );
				if ( is_numeric( $new_term_value ) ) {
					$new_term_id = (integer) $new_term_value;
					$new_term_slug = sanitize_title( $arguments['option_any_terms_text'] );
				} else {
					$new_term_slug = sanitize_title( $new_term_value );
				}
			}

			$new_term = ( object ) array(
				'term_id' => $new_term_id,
				'name' => $arguments['option_any_terms_text'],
				'slug' => $new_term_slug,
				'term_group' => '0',
				'term_taxonomy_id' => $new_term_id,
				'taxonomy' => $arguments['taxonomy'][0],
				'description' => '',
				'parent' => '0',
				'count' => $terms_assigned_counts['any.terms.assigned'],
				'level' => 0,
				'edit_link' => '',
				'term_link' => '',
				'link' => '',
			);
			$new_term->scaled_count = apply_filters( 'mla_tag_cloud_scale', round( log10( 1 ) * 100 ), $attr, $arguments, $new_term );

			if ( self::$mla_debug ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug adding ANY terms', 'media-library-assistant' ) . '</strong> = ' . var_export( $new_term, true ) );
			}
			array_unshift( $tags, $new_term );
			$found_rows += 1;
		}

		if ( ! empty( $arguments['option_no_terms_text'] ) ) {
			$new_term_id = -1;
			$new_term_slug = 'no.terms.assigned';

			if ( ! empty( $arguments['option_no_terms_value'] ) ) {
				$new_term_value = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['option_no_terms_value'], $page_values );
				if ( is_numeric( $new_term_value ) ) {
					$new_term_id = (integer) $new_term_value;
					$new_term_slug = sanitize_title( $arguments['option_no_terms_text'] );
				} else {
					$new_term_slug = sanitize_title( $new_term_value );
				}
			}

			$new_term = ( object ) array(
				'term_id' => $new_term_id,
				'name' => $arguments['option_no_terms_text'],
				'slug' => $new_term_slug,
				'term_group' => '0',
				'term_taxonomy_id' => $new_term_id,
				'taxonomy' => $arguments['taxonomy'][0],
				'description' => '',
				'parent' => '0',
				'count' => $terms_assigned_counts['no.terms.assigned'],
				'level' => 0,
				'edit_link' => '',
				'term_link' => '',
				'link' => '',
			);
			$new_term->scaled_count = apply_filters( 'mla_tag_cloud_scale', round( log10( 1 ) * 100 ), $attr, $arguments, $new_term );

			if ( self::$mla_debug ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug adding NO terms', 'media-library-assistant' ) . '</strong> = ' . var_export( $new_term, true ) );
			}
			array_unshift( $tags, $new_term );
			$found_rows += 1;
		}

		if ( ! empty( $arguments['option_all_text'] ) ) {
			$new_term_id = -3;
			$new_term_slug = 'ignore.terms.assigned';

			if ( ! empty( $arguments['option_all_value'] ) ) {
				$new_term_value = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['option_all_value'], $page_values );
				if ( is_numeric( $new_term_value ) ) {
					$new_term_id = (integer) $new_term_value;
					$new_term_slug = sanitize_title( $arguments['option_all_text'] );
				} else {
					$new_term_slug = sanitize_title( $new_term_value );
				}
			}

			$new_term = ( object ) array(
				'term_id' => $new_term_id,
				'name' => $arguments['option_all_text'],
				'slug' => $new_term_slug,
				'term_group' => '0',
				'term_taxonomy_id' => $new_term_id,
				'taxonomy' => $arguments['taxonomy'][0],
				'description' => '',
				'parent' => '0',
				'count' => $terms_assigned_counts['ignore.terms.assigned'],
				'level' => 0,
				'edit_link' => '',
				'term_link' => '',
				'link' => '',
			);
			$new_term->scaled_count = apply_filters( 'mla_tag_cloud_scale', round( log10( 1 ) * 100 ), $attr, $arguments, $new_term );

			if ( self::$mla_debug ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug adding IGNORE terms', 'media-library-assistant' ) . '</strong> = ' . var_export( $new_term, true ) );
			}
			array_unshift( $tags, $new_term );
			$found_rows += 1;
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

		if ( '%' === substr( $margin_string, -1 ) ) {
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
			'mla_style' => $arguments['mla_style'],
			'mla_markup' => $arguments['mla_markup'],
			'taxonomy' => implode( '-', $arguments['taxonomy'] ),
			'current_item' => $arguments['current_item'],
			'itemtag' => tag_escape( $arguments['itemtag'] ),
			'termtag' => tag_escape( $arguments['termtag'] ),
			'captiontag' => tag_escape( $arguments['captiontag'] ),
			'columns' => $columns,
			'itemwidth' => $width_string,
			'margin' => $margin_string,
			'float' => $float,
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
		$use_mla_tag_cloud_style = ( $is_grid || $is_list ) && ( 'none' !== strtolower( $style_values['mla_style'] ) );
		if ( apply_filters( 'use_mla_tag_cloud_style', $use_mla_tag_cloud_style, $style_values['mla_style'] ) ) {
			$style_template = MLATemplate_support::mla_fetch_custom_template( $style_values['mla_style'], 'tag-cloud', 'style' );
			if ( empty( $style_template ) ) {
				$style_values['mla_style'] = $default_style;
				$style_template = MLATemplate_support::mla_fetch_custom_template( $default_style, 'tag-cloud', 'style' );
			}

			if ( ! empty ( $style_template ) ) {
				// Look for 'query' and 'request' substitution parameters
				$style_values = MLAData::mla_expand_field_level_parameters( $style_template, $attr, $style_values );

				// Clean up the template to resolve width or margin === 'none'
				if ( 'none' === $margin_string ) {
					$style_values['margin'] = '0';
					$style_template = preg_replace( '/margin:[\s]*\[\+margin\+\][\%]*[\;]*/', '', $style_template );
				}

				if ( 'none' === $width_string ) {
					$style_values['itemwidth'] = 'auto';
					$style_template = preg_replace( '/width:[\s]*\[\+itemwidth\+\][\%]*[\;]*/', '', $style_template );
				}

				$style_values = apply_filters( 'mla_tag_cloud_style_values', $style_values );
				$style_template = apply_filters( 'mla_tag_cloud_style_template', $style_template );
				$gallery_style = MLAData::mla_parse_template( $style_template, $style_values );
				$gallery_style = apply_filters( 'mla_tag_cloud_style_parse', $gallery_style, $style_template, $style_values );
			} // !empty template
		} // use_mla_tag_cloud_style

		$markup_values = $style_values;

		if ( $is_grid || $is_list ) {
			$open_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'tag-cloud', 'markup', 'open' );
			if ( false === $open_template ) {
				$markup_values['mla_markup'] = $default_markup;
				$open_template = MLATemplate_support::mla_fetch_custom_template( $default_markup, 'tag-cloud', 'markup', 'open' );
			}

			if ( empty( $open_template ) ) {
				$open_template = '';
			}

			if ( $is_grid ) {
				$row_open_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'tag-cloud', 'markup', 'row-open' );
				if ( empty( $row_open_template ) ) {
					$row_open_template = '';
				}
			} else {
				$row_open_template = '';
			}

			$item_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'tag-cloud', 'markup', 'item' );
			if ( empty( $item_template ) ) {
				$item_template = '';
			}

			if ( $is_grid ) {
				$row_close_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'tag-cloud', 'markup', 'row-close' );
				if ( empty( $row_close_template ) ) {
					$row_close_template = '';
					}
			} else {
				$row_close_template = '';
			}

			$close_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'tag-cloud', 'markup', 'close' );
			if ( empty( $close_template ) ) {
				$close_template = '';
			}

			// Look for gallery-level markup substitution parameters
			$new_text = $open_template . $row_open_template . $row_close_template . $close_template;
			$markup_values = MLAData::mla_expand_field_level_parameters( $new_text, $attr, $markup_values );

			$markup_values = apply_filters( 'mla_tag_cloud_open_values', $markup_values );
			$open_template = apply_filters( 'mla_tag_cloud_open_template', $open_template );
			if ( empty( $open_template ) ) {
				$gallery_open = '';
			} else {
				$gallery_open = MLAData::mla_parse_template( $open_template, $markup_values );
			}

			$gallery_open = apply_filters( 'mla_tag_cloud_open_parse', $gallery_open, $open_template, $markup_values );
			$cloud .= $gallery_style . $gallery_open;
		} // is_grid || is_list
		elseif ( $is_pagination ) {
			// Handle 'previous_page', 'next_page', and 'paginate_links'
			if ( isset( $attr['limit'] ) ) {
				$attr['posts_per_page'] = $attr['limit'];
				$arguments['posts_per_page'] = $attr['limit'];
			}

			$pagination_result = MLAShortcode_Support::mla_process_pagination_output_types( $output_parameters, $markup_values, $arguments, $attr, $found_rows );
			if ( false !== $pagination_result ) {
				return $pagination_result;
			}

			// For "previous_link", "current_link" and "next_link", discard all of the $tags except the appropriate choice
			$link_type = $output_parameters[0];

			if ( ! in_array( $link_type, array ( 'previous_link', 'current_link', 'next_link' ) ) ) {
				return ''; // unknown output type
			}

			$is_wrap = isset( $output_parameters[1] ) && 'wrap' === $output_parameters[1];
			if ( empty( $markup_values['current_item'] ) ) {
				$target_id = -2; // won't match anything
			} else {
				$current_id = $markup_values['current_item'];

				foreach ( $tags as $id => $tag ) {
					if ( $current_is_slug ) {
						if ( $tag->slug === $current_id ) {
							break;
						}
					} else {
						if ( $tag->term_id === $current_id ) {
							break;
						}
					}
				}

				switch ( $link_type ) {
					case 'previous_link':
						$target_id = $id - 1;
						break;
					case 'next_link':
						$target_id = $id + 1;
						break;
					case 'current_link':
					default:
						$target_id = $id;
				} // link_type
			}

			$target = NULL;
			if ( isset( $tags[ $target_id ] ) ) {
				$target = $tags[ $target_id ];
			} elseif ( $is_wrap ) {
				switch ( $link_type ) {
					case 'previous_link':
						$target = array_pop( $tags );
						break;
					case 'next_link':
						$target = array_shift( $tags );
				} // link_type
			} // is_wrap

			if ( isset( $target ) ) {
				$tags = array( $target );
			} elseif ( ! empty( $arguments['mla_nolink_text'] ) ) {
				return wp_kses( MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_nolink_text'], $markup_values ), 'post' );
			} else {
				return '';
			}
		} // is_pagination

		// Accumulate links for flat and array output
		$tag_links = array();

		// Find delimiter for currentlink, currentlink_url
		if ( strpos( $markup_values['page_url'], '?' ) ) {
			$current_item_delimiter = '&';
		} else {
			$current_item_delimiter = '?';
		}

		$mla_cloud_current = (integer) $arguments[ $mla_page_parameter ];
		$column_index = 0;
		foreach ( $tags as $key => $tag ) {
			$item_values = $markup_values;

			// fill in item-specific elements
			$item_values['index'] = (string) 1 + $column_index;
			if ( $columns > 0 && ( 1 + $column_index ) % $columns === 0 ) {
				$item_values['last_in_row'] = 'last_in_row';
			} else {
				$item_values['last_in_row'] = '';
			}

			$item_values['key'] = $key;
			$item_values['term_id'] = $tag->term_id;
			$item_values['name'] = wptexturize( $tag->name );
			$item_values['slug'] = $tag->slug;
			$item_values['term_group'] = $tag->term_group;
			$item_values['term_taxonomy_id'] = $tag->term_taxonomy_id;
			$item_values['taxonomy'] = $tag->taxonomy;
			$item_values['description'] = wptexturize( $tag->description );
			$item_values['parent'] = $tag->parent;
			$item_values['count'] = isset ( $tag->count ) ? (integer) $tag->count : 0; 
			$item_values['term_count'] = isset ( $tag->term_count ) ? (integer) $tag->term_count : 0; 
			$item_values['scaled_count'] = $tag->scaled_count;

			if ( in_array( $tag->slug, array( 'ignore.terms.assigned', 'no.terms.assigned', 'any.terms.assigned' ) ) ) {
				$item_values['font_size'] = absint( $arguments['default_size'] );
			} else {
				$item_values['font_size'] = str_replace( ',', '.', ( $item_values['smallest'] + ( ( $item_values['scaled_count'] - $item_values['min_scaled_count'] ) * $item_values['font_step'] ) ) );
			}

			if ( empty( $arguments['mla_item_value'] ) ) {
				$item_values['thevalue'] = $item_values['term_id'];
			} else {
				$item_values['thevalue'] = MLAShortcode_Support::mla_process_shortcode_parameter( $arguments['mla_item_value'], $item_values );
			}

			// Add current item and current page to query arguments
			$query_arguments = $current_item_delimiter . 'current_item=' . esc_attr( $item_values['thevalue'] );
			if ( 1 !== $mla_cloud_current ) {
				$query_arguments .= '&' . $mla_page_parameter . '=' . esc_attr( $mla_cloud_current );
			}
		
			$item_values['link_url'] = $tag->link;
			$item_values['currentlink_url'] = sprintf( '%1$s%2$s', $item_values['page_url'], $query_arguments );
			$item_values['editlink_url'] = $tag->edit_link;
			$item_values['termlink_url'] = $tag->term_link;
			// Added in the code below:
			$item_values['caption'] = '';
			$item_values['link_attributes'] = '';
			$item_values['current_item_class'] = '';
			$item_values['rollover_text'] = '';
			$item_values['link_style'] = '';
			$item_values['link_text'] = '';
			$item_values['currentlink'] = '';
			$item_values['editlink'] = '';
			$item_values['termlink'] = '';
			$item_values['thelink'] = '';

			if ( ! empty( $arguments['current_item'] ) ) {
				if ( is_integer( $arguments['current_item'] ) ) {
					if ( intval( $tag->term_id ) === $arguments['current_item'] ) {
						$item_values['current_item_class'] = $arguments['current_item_class'];
					}
				} else {
					if ( sanitize_title_for_query( $tag->slug ) === sanitize_title_for_query( $arguments['current_item'] ) ) {
						$item_values['current_item_class'] = $arguments['current_item_class'];
					}
				}
			}

			// Add item_specific field-level substitution parameters
			$new_text = isset( $item_template ) ? $item_template : '';
			foreach( $mla_item_specific_arguments as $index => $value ) {
				$new_text .= str_replace( '{+', '[+', str_replace( '+}', '+]', $arguments[ $index ] ) );
			}

			$item_values = MLAData::mla_expand_field_level_parameters( $new_text, $attr, $item_values );

			if ( $item_values['captiontag'] ) {
				$item_values['caption'] = wptexturize( $tag->description );
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
				$item_values['link_text'] = $item_values['name'];
			}

			// Currentlink, editlink, termlink and thelink
			$item_values['currentlink'] = sprintf( '<a %1$shref="%2$s" title="%3$s" style="%4$s">%5$s</a>', $link_attributes, $item_values['currentlink_url'], $item_values['rollover_text'], $item_values['link_style'], $item_values['link_text'] );
			$item_values['editlink'] = sprintf( '<a %1$shref="%2$s" title="%3$s" style="%4$s">%5$s</a>', $link_attributes, $item_values['editlink_url'], $item_values['rollover_text'], $item_values['link_style'], $item_values['link_text'] );
			$item_values['termlink'] = sprintf( '<a %1$shref="%2$s" title="%3$s" style="%4$s">%5$s</a>', $link_attributes, $item_values['termlink_url'], $item_values['rollover_text'], $item_values['link_style'], $item_values['link_text'] );

			if ( ! empty( $link_href ) ) {
				$item_values['thelink'] = sprintf( '<a %1$shref="%2$s" title="%3$s" style="%4$s">%5$s</a>', $link_attributes, $link_href, $item_values['rollover_text'], $item_values['link_style'], $item_values['link_text'] );
			} elseif ( 'current' === $arguments['link'] ) {
				$item_values['link_url'] = $item_values['currentlink_url'];
				$item_values['thelink'] = $item_values['currentlink'];
			} elseif ( 'edit' === $arguments['link'] ) {
				$item_values['thelink'] = $item_values['editlink'];
			} elseif ( 'view' === $arguments['link'] ) {
				$item_values['thelink'] = $item_values['termlink'];
			} elseif ( 'span' === $arguments['link'] ) {
				$item_values['thelink'] = sprintf( '<span %1$sstyle="%2$s">%3$s</span>', $link_attributes, $item_values['link_style'], $item_values['link_text'] );
			} else {
				$item_values['thelink'] = $item_values['link_text'];
			}

			if ( $is_grid || $is_list ) {
				// Start of row markup
				if ( $is_grid && ( $columns > 0 && $column_index % $columns === 0 ) ) {
					$markup_values = apply_filters( 'mla_tag_cloud_row_open_values', $markup_values );
					$row_open_template = apply_filters( 'mla_tag_cloud_row_open_template', $row_open_template );
					$parse_value = MLAData::mla_parse_template( $row_open_template, $markup_values );
					$cloud .= apply_filters( 'mla_tag_cloud_row_open_parse', $parse_value, $row_open_template, $markup_values );
				}

				// item markup
				$column_index++;
				$item_values = apply_filters( 'mla_tag_cloud_item_values', $item_values );
				$item_template = apply_filters( 'mla_tag_cloud_item_template', $item_template );
				$parse_value = MLAData::mla_parse_template( $item_template, $item_values );
				$cloud .= apply_filters( 'mla_tag_cloud_item_parse', $parse_value, $item_template, $item_values );

				// End of row markup
				if ( $is_grid && ( $columns > 0 && $column_index % $columns === 0 ) ) {
					$markup_values = apply_filters( 'mla_tag_cloud_row_close_values', $markup_values );
					$row_close_template = apply_filters( 'mla_tag_cloud_row_close_template', $row_close_template );
					$parse_value = MLAData::mla_parse_template( $row_close_template, $markup_values );
					$cloud .= apply_filters( 'mla_tag_cloud_row_close_parse', $parse_value, $row_close_template, $markup_values );
				}
			} // is_grid || is_list
			elseif ( $is_pagination ) {
				return $item_values['thelink'];
			} else {
				$column_index++;
				$item_values = apply_filters( 'mla_tag_cloud_item_values', $item_values );
				$tag_links[] = apply_filters( 'mla_tag_cloud_item_parse', $item_values['thelink'], NULL, $item_values );
			} 
		} // foreach tag

		if ($is_grid || $is_list ) {
			// Close out partial row
			if ( $is_grid && ( ! ($columns > 0 && $column_index % $columns === 0 ) ) ) {
				$markup_values = apply_filters( 'mla_tag_cloud_row_close_values', $markup_values );
				$row_close_template = apply_filters( 'mla_tag_cloud_row_close_template', $row_close_template );
				$parse_value = MLAData::mla_parse_template( $row_close_template, $markup_values );
				$cloud .= apply_filters( 'mla_tag_cloud_row_close_parse', $parse_value, $row_close_template, $markup_values );
			}

			$markup_values = apply_filters( 'mla_tag_cloud_close_values', $markup_values );
			$close_template = apply_filters( 'mla_tag_cloud_close_template', $close_template );
			$parse_value = MLAData::mla_parse_template( $close_template, $markup_values );
			$cloud .= apply_filters( 'mla_tag_cloud_close_parse', $parse_value, $close_template, $markup_values );
		} // is_grid || is_list
		else {
			switch ( $markup_values['mla_output'] ) {
			case 'array' :
				$cloud =& $tag_links;
				break;
			case 'flat' :
			default :
				$cloud .= join( $markup_values['separator'], $tag_links );
				break;
			} // switch format
		}

		if ( 'array' === $arguments['mla_output'] || empty($arguments['echo']) ) {
			return $cloud;
		}

		echo $cloud; // phpcs:ignore
	} // mla_tag_cloud

	/**
	 * The MLA Tag Cloud shortcode.
	 *
	 * This is an interface to the mla_tag_cloud function.
	 *
	 * @since 2.20
	 *
	 * @param array $attr Attributes of the shortcode.
	 * @param string $content Optional content for enclosing shortcodes
	 *
	 * @return string HTML content to display the tag cloud.
	 */
	public static function mla_tag_cloud_shortcode( $attr, $content = NULL ) {
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
		$attr['echo'] = false;
			 
		return self::mla_tag_cloud( $attr );
	} // mla_tag_cloud_shortcode
} // Class MLATagCloud
?>