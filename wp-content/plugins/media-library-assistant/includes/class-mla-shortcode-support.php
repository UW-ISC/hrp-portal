<?php
/**
 * Media Library Assistant Shortcode handler(s)
 *
 * @package Media Library Assistant
 * @since 2.20
 */

// The MLA database access functions aren't available to "front end" posts/pages
if ( !class_exists( 'MLAQuery' ) ) {
	require_once( MLA_PLUGIN_PATH . 'includes/class-mla-data-query.php' );
	MLAQuery::initialize();
}

if ( !class_exists( 'MLAData' ) ) {
	require_once( MLA_PLUGIN_PATH . 'includes/class-mla-data.php' );
	MLAData::initialize();
}

if ( !class_exists( 'MLATemplate_Support' ) ) {
	require_once( MLA_PLUGIN_PATH . 'includes/class-mla-template-support.php' );
}
//error_log( __LINE__ . ' DEBUG: MLAShortcode_Support $_REQUEST = ' . var_export( $_REQUEST, true ), 0 );

/**
 * Class MLA (Media Library Assistant) Shortcode Support provides the functions that
 * implement the [mla_gallery] and [mla_tag_cloud] shortcodes. It also implements the
 * mla_get_shortcode_attachments() and mla_get_terms() database access functions.
 *
 * @package Media Library Assistant
 * @since 2.20
 */
class MLAShortcode_Support {
	/**
	 * Verify the presence of Ghostscript for mla_viewer
	 *
	 * @since 2.20
	 *
	 * @param	string	Non-standard location to override default search, e.g.,
	 *					'C:\Program Files (x86)\gs\gs9.15\bin\gswin32c.exe'
	 * @param	boolean	Force ghostscript-only tests, used by 
	 *                  MLASettings_Shortcodes::mla_compose_shortcodes_tab()
	 *
	 * @return	boolean	true if Ghostscript available else false
	 */
	public static function mla_ghostscript_present( $explicit_path = '', $ghostscript_only = false ) {
		static $ghostscript_present = NULL;

		// If $ghostscript_only = false, let the mla_debug parameter control logging
		if ( $ghostscript_only ) {
			$mla_debug_category = MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL;
		} else {
			$mla_debug_category = NULL;
		}

		MLACore::mla_debug_add( __LINE__ . " MLAShortcode_Support::mla_ghostscript_present( {$ghostscript_only} ) explicit_path = " . var_export( $explicit_path, true ), MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL );
		MLACore::mla_debug_add( __LINE__ . " MLAShortcode_Support::mla_ghostscript_present( {$ghostscript_only} ) ghostscript_present = " . var_export( $ghostscript_present, true ), MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL );

		if ( ! $ghostscript_only ) {
			if ( isset( $ghostscript_present ) ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>MLAShortcode_Support::mla_ghostscript_present</strong>, ghostscript_present = ' . var_export( $ghostscript_present, true ), MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL );
				return $ghostscript_present;
			}

			if ( 'checked' != MLACore::mla_get_option( 'enable_ghostscript_check' ) ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>MLAShortcode_Support::mla_ghostscript_present</strong>, disabled', $mla_debug_category );
				return $ghostscript_present = true;
			}

			// Imagick must be installed as well
			if ( ! class_exists( 'Imagick' ) ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>MLAShortcode_Support::mla_ghostscript_present</strong>, Imagick missing', $mla_debug_category );
				return $ghostscript_present = false;
			}
		} // not ghostscript_only

		// Look for exec() - from http://stackoverflow.com/a/12980534/866618
		$blacklist = preg_split( '/,\s*/', ini_get('disable_functions') . ',' . ini_get('suhosin.executor.func.blacklist') );
		if ( in_array('exec', $blacklist) ) {
			MLACore::mla_debug_add( __LINE__ . ' <strong>MLAShortcode_Support::mla_ghostscript_present</strong>, exec in blacklist', $mla_debug_category );
			return $ghostscript_present = false;
		}

		if ( 'WIN' === strtoupper( substr( PHP_OS, 0, 3) ) ) {
			if ( ! empty( $explicit_path ) ) {
				$return = exec( 'dir /o:n/s/b "' . $explicit_path . '"' );
				MLACore::mla_debug_add( __LINE__ . ' <strong>MLAShortcode_Support::mla_ghostscript_present</strong>, WIN explicit path = ' . var_export( $return, true ), MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL );
				if ( ! empty( $return ) ) {
					return $ghostscript_present = true;
				} else {
					return $ghostscript_present = false;
				}
			}

			$return = getenv('GSC');
			MLACore::mla_debug_add( __LINE__ . ' <strong>MLAShortcode_Support::mla_ghostscript_present</strong>, getenv(GSC) = ' . var_export( $return, true ), MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL );
			if ( ! empty( $return ) ) {
				return $ghostscript_present = true;
			}

			$return = exec('where gswin*c.exe');
			MLACore::mla_debug_add( __LINE__ . ' <strong>MLAShortcode_Support::mla_ghostscript_present</strong>,  exec(where gswin*c.exe) = ' . var_export( $return, true ), MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL );
			if ( ! empty( $return ) ) {
				return $ghostscript_present = true;
			}

			$return = exec('dir /o:n/s/b "C:\Program Files\gs\*gswin*c.exe"');
			MLACore::mla_debug_add( __LINE__ . ' <strong>MLAShortcode_Support::mla_ghostscript_present</strong>,  exec(dir /o:n/s/b "C:\Program Files\gs\*gswin*c.exe") = ' . var_export( $return, true ), MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL );
			if ( ! empty( $return ) ) {
				return $ghostscript_present = true;
			}

			$return = exec('dir /o:n/s/b "C:\Program Files (x86)\gs\*gswin32c.exe"');
			MLACore::mla_debug_add( __LINE__ . ' <strong>MLAShortcode_Support::mla_ghostscript_present</strong>,  exec(dir /o:n/s/b "C:\Program Files (x86)\gs\*gswin32c.exe") = ' . var_export( $return, true ), MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL );
			if ( ! empty( $return ) ) {
				return $ghostscript_present = true;
			}

			MLACore::mla_debug_add( __LINE__ . ' <strong>MLAShortcode_Support::mla_ghostscript_present</strong>, WIN detection failed', $mla_debug_category );
			return $ghostscript_present = false;
		} // Windows platform

		if ( ! empty( $explicit_path ) ) {
			exec( 'test -e ' . $explicit_path, $dummy, $ghostscript_path );
			MLACore::mla_debug_add( __LINE__ . ' <strong>MLAShortcode_Support::mla_ghostscript_present</strong>, explicit path = ' . var_export( $explicit_path, true ) . ', ghostscript_path = ' . var_export( $ghostscript_path, true ), MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL );
			return ( $explicit_path === $ghostscript_path );
		}

		$return = exec('which gs');
		MLACore::mla_debug_add( __LINE__ . ' <strong>MLAShortcode_Support::mla_ghostscript_present</strong>, exec(which gs) = ' . var_export( $return, true ), MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL );
		if ( ! empty( $return ) ) {
			return $ghostscript_present = true;
		}

		$test_path = '/usr/bin/gs';
		$output = array();
		$return_arg = -1;
		$return = exec( 'test -e ' . $test_path, $output, $return_arg );
		MLACore::mla_debug_add( __LINE__ . ' <strong>MLAShortcode_Support::mla_ghostscript_present</strong>, test_path = ' . var_export( $test_path, true ) . ', return_arg = ' . var_export( $return_arg, true ), MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL );
		MLACore::mla_debug_add( __LINE__ . ' <strong>MLAShortcode_Support::mla_ghostscript_present</strong>, return = ' . var_export( $return, true ) . ', output = ' . var_export( $output, true ), MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL );
		return $ghostscript_present = ( $test_path === $return_arg );
	}

	/**
	 * Removes MIME type restriction from Photonic Gallery query
	 *
	 * @since 2.76
	 *
	 * @param	object	$wp_query Current WP_Query object
	 */
	public static function _photonic_pre_get_posts( $wp_query ) {
		// This has already been applied in MLA's database query
		$wp_query->set( 'post_mime_type', '' );
	}

	/**
	 * Informs _get_attachment_image_src() of the 'size=icon_feature' setting
	 *
	 * @since 2.90
	 *
	 * @var	string shortcode 'size' parameter value
	 */
	private static $size_parameter = '';

	/**
	 * Informs _get_attachment_image_src() of the 'size=icon_feature' setting
	 *
	 * @since 3.00
	 *
	 * @var	boolean 'mla_use_featured' parameter value
	 */
	private static $mla_use_featured = false;

	/**
	 * Filters the image src result, returning the "Featured Image" or an icon to represent a non-image attachment.
	 *
	 * @since 2.76
	 *
	 * @param array|false  $image         Either array with src, width & height, icon src, or false.
	 * @param int          $attachment_id Image attachment ID.
	 * @param string|array $size          Size of image. Image size or array of width and height values
	 *                                    (in that order). Default 'thumbnail'.
	 * @param bool         $icon          Whether the image should be treated as an icon. Default false.
  	 */
	public static function _get_attachment_image_src( $image, $attachment_id, $size, $icon ) {
		static $nested_call = false;

		if ( $nested_call ) {
			return $image;
		}

		if ( 'none' === self::$size_parameter ) {
			return false;
		} elseif ( ( 'icon_only' === self::$size_parameter ) ||  ( 'icon_feature' === self::$size_parameter ) ) {
			// No native images allowed
			$image = false;
		}

		// Look for the "Featured Image" as an alternate thumbnail for PDFs, etc.
		if ( self::$mla_use_featured && ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ENABLE_FEATURED_IMAGE ) ) ) {
			$nested_call = true;
			$feature = get_the_post_thumbnail( $attachment_id, $size, array( 'class' => 'attachment-thumbnail' ) );
			$nested_call = false;

			if ( ! empty( $feature ) ) {
				$match_count = preg_match_all( '# width=\"([^\"]+)\" height=\"([^\"]+)\" src=\"([^\"]+)\" #', $feature, $matches, PREG_OFFSET_CAPTURE );
				if ( ! ( ( $match_count == false ) || ( $match_count == 0 ) ) ) {
					$image = array( $matches[3][0][0], $matches[1][0][0], $matches[2][0][0] );
					return $image;
				}
			}
		} // enable_featured_image

		// If a native image exists, we're done
		if ( false !== $image ) {
			return $image;
		}

		// Look for the "Featured Image" as an alternate thumbnail for PDFs, etc.
		if ( ( 'icon_only' !== self::$size_parameter ) && ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ENABLE_FEATURED_IMAGE ) ) ) {
			$nested_call = true;
			$feature = get_the_post_thumbnail( $attachment_id, $size, array( 'class' => 'attachment-thumbnail' ) );
			$nested_call = false;

			if ( ! empty( $feature ) ) {
				$match_count = preg_match_all( '# width=\"([^\"]+)\" height=\"([^\"]+)\" src=\"([^\"]+)\" #', $feature, $matches, PREG_OFFSET_CAPTURE );
				if ( ! ( ( $match_count == false ) || ( $match_count == 0 ) ) ) {
					$image = array( $matches[3][0][0], $matches[1][0][0], $matches[2][0][0] );
					return $image;
				}
			}
		} // enable_featured_image

		// For any of the three "icon" variations, try to substitute an icon image
		if ( 0 === strpos( self::$size_parameter, 'icon' )  ) {
			if ( $src = wp_mime_type_icon( $attachment_id ) ) {
				/** This filter is documented in wp-includes/post.php */
				$icon_dir = apply_filters( 'icon_dir', ABSPATH . WPINC . '/images/media' );

				$src_file = $icon_dir . '/' . wp_basename( $src );
				@list( $width, $height ) = getimagesize( $src_file );
			}

			if ( $src && $width && $height ) {
				$image = array( $src, $width, $height );
			}
		}

		return $image;
	}

	/**
	 * Errors found in function mla_validate_attributes()
	 *
	 * @since 2.80
	 *
	 * @var	array
	 */
	private static $attributes_errors = array();

	/**
	 * Make sure $attr does not contain any HTML Event Attributes
	 *
	 * @since 3.14
	 *
	 * @param	string	$attr One or more HTML attributes to be validated
	 *
	 * @return	string	clean attributes or an error "message"
	 */
	public static function mla_esc_attr( $attr ) {
		$raw_attr = shortcode_parse_atts( $attr );
		$valid_attr = '';

		foreach ( $raw_attr as $attribute => $value ) {
			if ( 0 === strpos( strtolower( $attribute ), 'on' ) ) {
				return 'mla-error="HTML Event Attributes are not allowed"';
			}

			$valid_attr .= $attribute . '="' . esc_attr( $value ) . '"';
		}
			
		return $valid_attr;
	}

	/**
	 * Make sure $attr is an array, repair line-break damage, merge with $content
	 *
	 * @since 2.20
	 *
	 * @param	mixed	$attr Array or string containing shortcode attributes
	 * @param	string	$content Optional content for enclosing shortcodes
	 *
	 * @return	array	clean attributes array
	 */
	public static function mla_validate_attributes( $attr, $content = NULL ) {
//error_log( __LINE__ . " mla_validate_attributes() attr = " . var_export( $attr, true ), 0 );
//error_log( __LINE__ . " mla_validate_attributes() content = " . var_export( $content, true ), 0 );

		if ( is_string( $attr ) ) {
			$attr = shortcode_parse_atts( $attr );
		}

		if ( empty( $attr ) ) {
			$attr = array();
		}
//error_log( __LINE__ . " mla_validate_attributes() attr = " . var_export( $attr, true ), 0 );

		// Numeric keys indicate parse errors
		$not_valid = false;
		foreach ( $attr as $key => $value ) {
			// Clean up damage caused by the Visual Editor 
			$attr[ $key ] = wp_specialchars_decode( $value );

			if ( is_numeric( $key ) ) {
				$not_valid = true;
				break;
			}
		}
//error_log( __LINE__ . " mla_validate_attributes() attr = " . var_export( $attr, true ), 0 );

		if ( $not_valid ) {
			/*
			 * Found an error, e.g., line break(s) among the atttributes.
			 * Try to reconstruct the input string without them.
			 */
			$new_attr = '';
			foreach ( $attr as $key => $value ) {
				$value = str_replace( array( '&#038;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8242;', '&#8243;', '&amp;', '<br />', '<br>', '<p>', '</p>', "\r", "\n", "\t" ),
		 	                            array( '&',      '\'',      '\'',      '"',       '"',       '\'',      '"',       '&',     ' ',      ' ',    ' ',   ' ',    ' ',  ' ',  ' ' ), $value );
//error_log( __LINE__ . " mla_validate_attributes() [{$key}] value = " . var_export( $value, true ), 0 );
				$break_tag = strpos( $value, '<br' );
				if ( ( false !== $break_tag ) && ( ($break_tag + 3) == strlen( $value ) ) ) {
					$value = substr( $value, 0, ( strlen( $value ) - 3) );
				}

				if ( is_numeric( $key ) ) {
					if ( '/>' !== $value ) {
						$new_attr .= $value . ' ';
					}
				} else {
					$delimiter = ( false === strpos( $value, '"' ) ) ? '"' : "'";
					$new_attr .= $key . '=' . $delimiter . $value . $delimiter . ' ';
				}
			}

			$attr = shortcode_parse_atts( $new_attr );

			// Remove empty values and still-invalid parameters
			$new_attr = array();
			foreach ( $attr as $key => $value ) {
				if ( is_numeric( $key ) || empty( $value ) ) {
					self::$attributes_errors['raw'][] = '[' . $key . '] => ' . $value;
					self::$attributes_errors['escaped'][] = '[' . $key . '] => ' . esc_html( $value );
					continue;
				}

				$new_attr[ $key ] = $value;
			}

			$attr = $new_attr;
		} // not_valid
//error_log( __LINE__ . " mla_validate_attributes() attr = " . var_export( $attr, true ), 0 );

		// Look for parameters in an enclosing shortcode
		if ( ! ( empty( $content ) || isset( $attr['mla_alt_shortcode'] ) ) ) {
			$content = str_replace( array( '&#038;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8242;', '&#8243;', '&amp;', '<br />', '<br>', '<p>', '</p>', "\r", "\n", "\t" ),
			                          array( '&',      '\'',      '\'',      '"',       '"',       '\'',      '"',       '&',     ' ',      ' ',    ' ',   ' ',    ' ',  ' ',  ' ' ), $content );
			$content_attr = shortcode_parse_atts( $content );
//error_log( __LINE__ . " mla_validate_attributes() content_attr = " . var_export( $content_attr, true ), 0 );
			if ( is_array( $content_attr ) ) {
				// Remove empty values and still-invalid parameters
				$new_attr = array();
				foreach ( $content_attr as $key => $value ) {
					if ( is_numeric( $key ) || ( 0 === strlen( $value ) ) ) { // empty( $value ) ) {
						self::$attributes_errors['raw'][] = 'content [' . $key . '] => ' . $value;
						self::$attributes_errors['escaped'][] = 'content [' . $key . '] => ' . esc_html( $value );
						continue;
					}

					$new_attr[ $key ] = $value;
				}

				$attr = array_merge( $attr, $new_attr );
			}
		}

		return $attr;
	}

	/**
	 * Turn debug collection and display on or off
	 *
	 * @since 2.20
	 *
	 * @var	boolean
	 */
	private static $mla_debug = false;

	/**
	 * Default values when global $post is not set
	 *
	 * @since 2.40
	 *
	 * @var	array
	 */
	private static $empty_post = array( 
				'ID' => 0,
				'post_author' => 0,
				'post_date' => '0000-00-00 00:00:00',
				'post_date_gmt' => '0000-00-00 00:00:00',
				'post_content' => '',
				'post_title' => '',
				'post_excerpt' => '',
				'post_status' => 'publish',
				'comment_status' => 'open',
				'ping_status' => 'open',
				'post_name' => '',
				'to_ping' => 'None',
				'pinged' => 'None',
				'post_modified' => '0000-00-00 00:00:00',
				'post_modified_gmt' => '0000-00-00 00:00:00',
				'post_content_filtered' => 'None',
				'post_parent' => 0,
				'guid' => '',
				'menu_order' => 0,
				'post_type' => 'post',
				'post_mime_type' => '',
				'comment_count' => 0,
			);

	/**
	 * Default post object generator
	 *
	 * @since 3.10
	 *
	 * @return object Post objct with default  or "quthor" information
	 */
	public static function mla_get_default_post() {
		if ( is_author() ) {
			$author_post = (object) self::$empty_post;

			$author_post->ID = get_the_author_meta( 'ID' );
			$author_post->post_author = get_the_author_meta( 'ID' );
			$author_post->post_title = get_the_author_meta( 'display_name' );
			$author_post->post_excerpt = get_the_author_meta( 'nickname' );
			$author_post->post_name = get_the_author_meta( 'user_nicename' );
			$author_post->post_content = get_the_author_meta( 'description' );
			$author_post->post_type = 'author';

			return $author_post;
		}

		return (object) self::$empty_post;
	}

	/**
	 * The MLA Gallery shortcode.
	 *
	 * This is a superset of the WordPress Gallery shortcode for displaying images on a post,
	 * page or custom post type. It is adapted from /wp-includes/media.php gallery_shortcode.
	 * Enhancements include many additional selection parameters and full taxonomy support.
	 *
	 * @since 2.20
	 *
	 * @param array $attr Attributes of the shortcode
	 * @param string $content Optional content for enclosing shortcodes
	 *
	 * @return string HTML content to display gallery.
	 */
	public static function mla_gallery_shortcode( $attr, $content = NULL ) {
//error_log( __LINE__ . " mla_gallery_shortcode() _REQUEST = " . var_export( $_REQUEST, true ), 0 );
//error_log( __LINE__ . " mla_gallery_shortcode() attr = " . var_export( $attr, true ), 0 );
//error_log( __LINE__ . " mla_gallery_shortcode() content = " . var_export( $content, true ), 0 );
		global $post;

		// Some do_shortcode callers may not have a specific post in mind
		if ( ! is_object( $post ) ) {
			$post = self::mla_get_default_post();
		}

		// $instance supports multiple galleries in one page/post	
		static $instance = 0;
		$instance++;

		// Some values are already known, and can be used in data selection parameters
		$upload_dir = wp_upload_dir();
		$page_values = array(
			'instance' => $instance,
			'selector' => "mla_gallery-{$instance}",
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
			'page_parent' => $post->post_parent,
			'page_guid' => $post->guid,
			'page_type' => $post->post_type,
			'page_mime_type' => $post->post_mime_type,
			'page_url' => get_page_link(),
		);

		/*
		 * Make sure $attr is an array, even if it's empty,
		 * and repair damage caused by link-breaks in the source text
		 */
		$attr = self::mla_validate_attributes( $attr, $content );

		// Filter the attributes before $mla_page_parameter and "request:" prefix processing.
		$attr = apply_filters( 'mla_gallery_raw_attributes', $attr );

		/*
		 * The mla_paginate_current parameter can be changed to support
		 * multiple galleries per page.
		 */
		if ( ! isset( $attr['mla_page_parameter'] ) ) {
			$attr['mla_page_parameter'] = self::$mla_get_shortcode_attachments_parameters['mla_page_parameter'];
		}

		// The mla_page_parameter can contain page_level parameters like {+page_ID+}
		$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr['mla_page_parameter'] ) );
		$mla_page_parameter = MLAData::mla_parse_template( $attr_value, $page_values );

		/*
		 * Special handling of the mla_paginate_current parameter to make
		 * "MLA pagination" easier. Look for this parameter in $_REQUEST
		 * if it's not present in the shortcode itself.
		 */
		if ( ! isset( $attr[ $mla_page_parameter ] ) ) {
			if ( isset( $_REQUEST[ $mla_page_parameter ] ) ) {
				$attr[ $mla_page_parameter ] = sanitize_text_field( wp_unslash( $_REQUEST[ $mla_page_parameter ] ) );
			}
		}

		// These are the parameters for gallery display
		$mla_item_specific_arguments = array(
			'mla_link_attributes' => '',
			'mla_link_class' => '',
			'mla_link_href' => '',
			'mla_link_text' => '',
			'mla_nolink_text' => '',
			'mla_rollover_text' => '',
			'mla_image_class' => '',
			'mla_image_alt' => '',
			'mla_image_attributes' => '',
			'mla_caption' => '',
			'mla_alt_ids_value' => NULL,
		);

		// These arguments must not be passed on to alternate gallery shortcodes
		$mla_arguments = array_merge( array(
			'mla_minimum' => '0',
			'mla_output' => 'gallery',
			'mla_style' => MLACore::mla_get_option('default_style'),
			'mla_markup' => MLACore::mla_get_option('default_markup'),
			'mla_float' => 'none', // before v2.90: is_rtl() ? 'right' : 'left',
			'mla_itemwidth' => MLACore::mla_get_option('mla_gallery_itemwidth'),
			'mla_margin' => MLACore::mla_get_option('mla_gallery_margin'),
			'mla_target' => '',
			'mla_debug' => false,
			'mla_allow_rml' => false,
			'mla_rml_folder' => NULL,
			'mla_rml_include_children' => false,
			'mla_allow_catf' => true,
			'mla_catf_folder' => NULL,

			'mla_named_transfer' => false,
			'mla_use_featured' => false,
			'mla_viewer' => false,
			'mla_single_thread' => false,
			'mla_viewer_extensions' => 'ai,eps,pdf,ps',
			'mla_viewer_limit' => '0',
			'mla_viewer_width' => '0',
			'mla_viewer_height' => '0',
			'mla_viewer_best_fit' => NULL,
			'mla_viewer_page' => '1',
			'mla_viewer_resolution' => '0',
			'mla_viewer_quality' => '0',
			'mla_viewer_type' => '',

			'mla_alt_shortcode' => NULL,
			'mla_alt_ids_name' => 'ids',
			'mla_alt_ids_template' => NULL,
			'mla_alt_parameters' => NULL,
			// paginatation arguments defined in $mla_get_shortcode_attachments_parameters
			// 'mla_page_parameter' => 'mla_paginate_current', handled in code with $mla_page_parameter
			// 'mla_paginate_current' => NULL,
			// 'mla_paginate_total' => NULL,
			// 'id' => NULL,

			'mla_end_size'=> 1,
			'mla_mid_size' => 2,
			'mla_prev_text' => '&laquo; ' . __( 'Previous', 'media-library-assistant' ),
			'mla_next_text' => __( 'Next', 'media-library-assistant' ) . ' &raquo;',
			'mla_paginate_type' => 'plain',
			'mla_paginate_rows' => NULL ),
			$mla_item_specific_arguments
		);

		$html5 = current_theme_supports( 'html5', 'gallery' );
		$default_arguments = array_merge( array(
			'size' => 'thumbnail', // or 'medium', 'large', 'full' or registered size
			'itemtag' => $html5 ? 'figure' : 'dl',
			'icontag' => $html5 ? 'div' : 'dt',
			'captiontag' => $html5 ? 'figcaption' : 'dd',
			'columns' => MLACore::mla_get_option('mla_gallery_columns'),
			'link' => 'permalink', // or 'post', 'file', a registered size, etc.
			// Photonic-specific
			'id' => NULL,
			'style' => NULL,
			'type' => 'default', // also used by WordPress.com Jetpack!
			'thumb_width' => 75,
			'thumb_height' => 75,
			'thumbnail_size' => 'thumbnail',
			'slide_size' => 'large',
			'slideshow_height' => 500,
			'fx' => 'fade',
			'timeout' => 4000,
			'speed' => 1000,
			'pause' => NULL),
			$mla_arguments
		);

		// Convert to boolean
		$arguments['mla_named_transfer'] = 'true' === ( ( ! empty( $arguments['mla_named_transfer'] ) ) ? trim( strtolower( $arguments['mla_named_transfer'] ) ) : 'false' );

		// Apply default arguments set in the markup template
		$template = $mla_arguments['mla_markup'];
		if ( isset( $attr['mla_markup'] ) && MLATemplate_Support::mla_fetch_custom_template( $attr['mla_markup'], 'gallery', 'markup', '[exists]' ) ) {
			$template = $attr['mla_markup'];
		}

		$arguments = MLATemplate_Support::mla_fetch_custom_template( $template, 'gallery', 'markup', 'arguments' );
		if ( ! empty( $arguments ) ) {
			$attr = wp_parse_args( $attr, self::mla_validate_attributes( array(), $arguments ) );
		}

		/*
		 * Look for page-level, 'request:' and 'query:' substitution parameters,
		 * which can be added to any input parameter
		 */
		foreach ( $attr as $attr_key => $attr_value ) {
			/*
			 * attachment-specific Gallery Display Content parameters must be evaluated
			 * later, when all of the information is available.
			 */
			if ( array_key_exists( $attr_key, $mla_item_specific_arguments ) ) {
				continue;
			}

			// Don't expand anything passed along to the alternate shortcode
			if ( 'mla_alt_parameters' === $attr_key ) {
				continue;
			}

			$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr_value ) );
			$replacement_values = MLAData::mla_expand_field_level_parameters( $attr_value, $attr, $page_values );
			$attr[ $attr_key ] = MLAData::mla_parse_template( $attr_value, $replacement_values );
		}

		// Merge gallery arguments with defaults, pass the query arguments on to mla_get_shortcode_attachments.
		$attr = apply_filters( 'mla_gallery_attributes', $attr );
		$content = apply_filters( 'mla_gallery_initial_content', $content, $attr );
		$arguments = shortcode_atts( $default_arguments, $attr );
		$arguments = apply_filters( 'mla_gallery_arguments', $arguments );

		// Decide which templates to use
		if ( ( 'none' !== $arguments['mla_style'] ) && ( 'theme' !== $arguments['mla_style'] ) ) {
			if ( !MLATemplate_Support::mla_fetch_custom_template( $arguments['mla_style'], 'gallery', 'style', '[exists]' ) ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>mla_gallery mla_style</strong> "' . $arguments['mla_style'] . '" ' . __( 'not found', 'media-library-assistant' ), MLACore::MLA_DEBUG_CATEGORY_ANY );
				$arguments['mla_style'] = $default_arguments['mla_style'];
			}
		}

		if ( !MLATemplate_Support::mla_fetch_custom_template( $arguments['mla_markup'], 'gallery', 'markup', '[exists]' ) ) {
			MLACore::mla_debug_add( __LINE__ . ' <strong>mla_gallery mla_markup</strong> "' . $arguments['mla_markup'] . '" ' . __( 'not found', 'media-library-assistant' ), MLACore::MLA_DEBUG_CATEGORY_ANY );
			$arguments['mla_markup'] = $default_arguments['mla_markup'];
		}

		// Look for alternate gallery shortcode special cases
		if ( is_string( $arguments['mla_alt_shortcode'] ) ) {
			// Special value to avoid Justified Image Grid conflict
			if ( 'yes' === $arguments['mla_alt_shortcode'] ) {
				$arguments['mla_alt_shortcode'] = 'mla_gallery';
			} elseif ( in_array( $arguments['mla_alt_shortcode'], array( 'mla_gallery', 'no' ) ) ) {
				// Handle "no effect" alternate gallery shortcode to support plugins such as Justified Image Grid
				$arguments['mla_alt_shortcode'] = NULL;
				$arguments['mla_alt_ids_name'] = 'ids';
				$arguments['mla_alt_ids_value'] = NULL;
				$arguments['mla_alt_ids_template'] = NULL;
				$arguments['mla_alt_parameters'] = NULL;
			}
		}

		self::$mla_debug = ( ! empty( $arguments['mla_debug'] ) ) ? trim( strtolower( $arguments['mla_debug'] ) ) : false;
		if ( self::$mla_debug ) {
			if ( 'true' == self::$mla_debug ) {
				MLACore::mla_debug_mode( 'buffer' );
			} elseif ( 'log' == self::$mla_debug ) {
				MLACore::mla_debug_mode( 'log' );
			} else {
				self::$mla_debug = false;
			}
		}

		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug REQUEST', 'media-library-assistant' ) . '</strong> = ' . var_export( $_REQUEST, true ) );

			if ( ! empty( self::$attributes_errors ) ) {
				if ( 'log' == self::$mla_debug ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug attributes_errors', 'media-library-assistant' ) . '</strong> = ' . var_export( self::$attributes_errors['raw'], true ) );
				} else {
				MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug attributes_errors', 'media-library-assistant' ) . '</strong> = ' . var_export( self::$attributes_errors['escaped'], true ) );
				}

				self::$attributes_errors = array();
			}

			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug attributes', 'media-library-assistant' ) . '</strong> = ' . var_export( $attr, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug arguments', 'media-library-assistant' ) . '</strong> = ' . var_export( $arguments, true ) );
		}

		// Determine output type
		$output_parameters = array_map( 'strtolower', array_map( 'trim', explode( ',', $arguments['mla_output'] ) ) );
		if ( ! in_array( $output_parameters[0], array( 'gallery', 'next_link', 'current_link', 'previous_link', 'next_page', 'previous_page', 'paginate_links' ) ) ) {
			$output_parameters[0] = 'gallery';
		}

		$is_gallery = 'gallery' == $output_parameters[0];
		$is_pagination = in_array( $output_parameters[0], array( 'previous_page', 'next_page', 'paginate_links' ) ); 

		if ( $is_pagination && ( NULL !== $arguments['mla_paginate_rows'] ) ) {
			$attachments['found_rows'] = absint( $arguments['mla_paginate_rows'] );
		} else {
			// Look for negative phrases in keyword search
			if ( ! empty( $attr['s'] ) ) {
				$term_delimiter = /* ! empty( $attr['mla_term_delimiter'] ) ? $attr['mla_term_delimiter'] : */ ',';
				$negative_delimiter = ! empty( $attr['mla_negative_delimiter'] ) ? $attr['mla_negative_delimiter'] : '/';
				$search_phrases = MLAQuery::mla_divide_search_string( $attr['s'], $term_delimiter, $negative_delimiter );

				if ( ! empty( $search_phrases['negative'] ) ) {
					$negative_arguments = $attr;
					unset( $negative_arguments[ $mla_page_parameter ] );
					unset( $negative_arguments['nopaging'] );
					unset( $negative_arguments['offset'] );
					unset( $negative_arguments['paged'] );
					$negative_arguments['orderby'] = 'none';
					$save_excludes = explode( ',', ! empty( $negative_arguments['exclude'] ) ? $negative_arguments['exclude'] : '' );
					$negative_arguments['exclude'] = '';

					$negative_arguments['s'] = $search_phrases['negative'];
					$negative_arguments['fields'] = 'ids';
					$excluded_items = self::mla_get_shortcode_attachments( $post->ID, $negative_arguments, false );

					$attr['s'] = $search_phrases['positive'];
					$attr['exclude'] = implode( ',', array_merge( $save_excludes, $excluded_items ) );
				}
			}

			// Look for negative phrases in taxonomy term keyword search
			if ( ! empty( $attr['mla_terms_phrases'] ) ) {
				$term_delimiter = ! empty( $attr['mla_term_delimiter'] ) ? $attr['mla_term_delimiter'] : ',';
				$negative_delimiter = ! empty( $attr['mla_negative_delimiter'] ) ? $attr['mla_negative_delimiter'] : '/';
				$search_phrases = MLAQuery::mla_divide_search_string( $attr['mla_terms_phrases'], $term_delimiter, $negative_delimiter );

				if ( ! empty( $search_phrases['negative'] ) ) {
					$negative_arguments = $attr;
					unset( $negative_arguments[ $mla_page_parameter ] );
					unset( $negative_arguments['nopaging'] );
					unset( $negative_arguments['offset'] );
					unset( $negative_arguments['paged'] );
					$negative_arguments['orderby'] = 'none';
					$save_excludes = explode( ',', ! empty( $negative_arguments['exclude'] ) ? $negative_arguments['exclude'] : '' );
					$negative_arguments['exclude'] = '';

					$negative_arguments['mla_terms_phrases'] = $search_phrases['negative'];
					$negative_arguments['fields'] = 'ids';
					$excluded_items = self::mla_get_shortcode_attachments( $post->ID, $negative_arguments, false );

					$attr['mla_terms_phrases'] = $search_phrases['positive'];
					$attr['exclude'] = implode( ',', array_merge( $save_excludes, $excluded_items ) );
				}
			}

			$attachments = self::mla_get_shortcode_attachments( $post->ID, $attr, true );
		}

		if ( is_string( $attachments ) ) {
			return $attachments;
		}

		$current_rows = count( $attachments );

		if ( isset( $attachments['max_num_pages'] ) ) {
			$max_num_pages = $attachments['max_num_pages'];
			unset( $attachments['max_num_pages'] );
			$current_rows--;
		} else {
			$max_num_pages = 1;
		}

		if ( isset( $attachments['found_rows'] ) ) {
			$found_rows = $attachments['found_rows'];
			unset( $attachments['found_rows'] );
			$current_rows--;
		} else {
			$found_rows = $current_rows;
		}

		$mla_minimum = absint( $arguments['mla_minimum'] );
		if ( 0 < $mla_minimum ) {
			if ( $is_gallery && ( empty($attachments) || count($attachments) < $mla_minimum ) ) {
				$attachments = array();
			}
			
			if ( $is_pagination && empty( $found_rows ) || $found_rows < $mla_minimum ) {
				$found_rows = 0;
			}
		}
		
		if ( ( $is_gallery && empty($attachments) ) || ( $is_pagination && empty( $found_rows ) ) ) {
			if ( self::$mla_debug ) {
				MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug empty gallery', 'media-library-assistant' ) . '</strong>, query = ' . var_export( $attr, true ) );
				$output = MLACore::mla_debug_flush();
			} else {
				$output =  '';
			}

			if ( ! empty( $arguments['mla_nolink_text'] ) ) {
				$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $arguments['mla_nolink_text'] ) );
				$replacement_values = MLAData::mla_expand_field_level_parameters( $attr_value, $attr, $page_values );
				$attr_value = wp_kses( $attr_value, 'post' );
				$output .= MLAData::mla_parse_template( $attr_value, $replacement_values );
			}

			return $output;
		} // empty $attachments

		// Pass size argument to _get_attachment_image_src() and replace special values
		$size = strtolower( $arguments['size'] );
		self::$size_parameter = $size;

		// Pass mla_use_featured argument to _get_attachment_image_src() and replace special values
		self::$mla_use_featured = !empty( $arguments['mla_use_featured'] ) ? 'true' === strtolower( $arguments['mla_use_featured'] ) : false;

		if ( ( 'icon_only' === $size ) || ( 'icon_feature' === $size ) ) {
			$size = 'icon';
		}

		$size_class = $size;

		// Look for Photonic-enhanced gallery; use the [gallery] shortcode if found
		global $photonic;

		$is_photonic = false;
		if ( is_object( $photonic ) && ! empty( $arguments['style'] ) && empty( $arguments['mla_alt_shortcode'] ) ) {
			if ( 'default' != strtolower( $arguments['type'] ) )  {
				return '<p>' . __( '<strong>Photonic-enhanced [mla_gallery]</strong> type must be <strong>default</strong>, query = ', 'media-library-assistant' ) . var_export( $attr, true ) . '</p>';
			}

			if ( isset( $arguments['pause'] ) && ( 'false' == $arguments['pause'] ) ) {
				$arguments['pause'] = NULL;
			}

			$arguments['mla_alt_shortcode'] = 'gallery';
			$is_photonic = true;
		}

		// Look for user-specified alternate gallery shortcode
		$processing_alt_ids_value = false;
		if ( is_string( $arguments['mla_alt_shortcode'] ) ) {
			// Replace data-selection parameters with the "ids" list
			$blacklist = array_merge( self::$mla_get_shortcode_attachments_parameters, self::$mla_get_shortcode_dynamic_attachments_parameters );

			// Other MLA shortcodes use some of the same parameters, e.g., mla_link_href, so let them thru
			if ( !in_array( $arguments['mla_alt_shortcode'], array( 'mla_tag_cloud', 'mla_term_list' ) ) ) {
				$blacklist = array_merge( $mla_arguments, $blacklist );
			}

			// Suppress mla_alt... shortcode arguments in second mla_gallery
			if ( 'mla_gallery' === $arguments['mla_alt_shortcode'] ) {
				$blacklist = array_merge( $blacklist, array( 
					'mla_alt_shortcode' => NULL,
					'mla_alt_ids_name' => 'ids',
					'mla_alt_ids_value' => NULL,
					'mla_alt_ids_template' => NULL,
					'mla_alt_parameters' => NULL,
					) 
 				);
			}

			$blacklist = apply_filters( 'mla_gallery_alt_shortcode_blacklist', $blacklist );
			$alt_attr = apply_filters( 'mla_gallery_alt_shortcode_attributes', $attr );
//error_log( __LINE__ . " alt_attr = " . var_export( $alt_attr, true ), 0 );

			// Allow for overide of blacklist values, e.g., post_mime_type
			$alt_parameters = array();
			if ( !empty( $alt_attr['mla_alt_parameters'] ) ) {
				$alt_parameters = self::mla_validate_attributes( $alt_attr['mla_alt_parameters'] );
//error_log( __LINE__ . " alt_parameters = " . var_export( $alt_parameters, true ), 0 );
				unset( $alt_attr['mla_alt_parameters'] );
				$alt_attr = array_merge( $alt_attr, $alt_parameters );
			}
//error_log( __LINE__ . " alt_attr = " . var_export( $alt_attr, true ), 0 );

			$mla_alt_shortcode_args = array();
			foreach ( $alt_attr as $key => $value ) {
				if ( array_key_exists( $key, $blacklist ) && ( !array_key_exists( $key, $alt_parameters ) ) ) {
					continue;
				}

				$slashed = addcslashes( $value, chr(0).chr(7).chr(8)."\f\n\r\t\v\"\\\$" );
				if ( ( false !== strpos( $value, ' ' ) ) || ( false !== strpos( $value, '\'' ) ) || ( $slashed != $value ) ) {
					$value = '"' . $slashed . '"';
				}

				$mla_alt_shortcode_args[] = $key . '=' . $value;
			} // foreach $attr
//error_log( __LINE__ . " mla_alt_shortcode_args = " . var_export( $mla_alt_shortcode_args, true ), 0 );


			$mla_alt_shortcode_args = implode( ' ', $mla_alt_shortcode_args );

			// Restore original delimiters
			$mla_alt_shortcode_args = str_replace( '[+', '{+', str_replace( '+]', '+}', $mla_alt_shortcode_args ) );
//error_log( __LINE__ . " mla_alt_shortcode_args = " . var_export( $mla_alt_shortcode_args, true ), 0 );

			/*
			 * If an alternate value has been specified we must delay alt shortcode execution
			 * and accumulate $mla_alt_shortcode_ids in the template Item section.
			 */
			$mla_alt_ids_value = is_null( $arguments['mla_alt_ids_value'] ) ? NULL : str_replace( '{+', '[+', str_replace( '+}', '+]', $arguments['mla_alt_ids_value'] ) );
			$mla_alt_shortcode_ids = array();
			$mla_alt_ids_template = is_null( $arguments['mla_alt_ids_template'] ) ? NULL : str_replace( '{+', '[+', str_replace( '+}', '+]', $arguments['mla_alt_ids_template'] ) );

			if ( is_null( $mla_alt_ids_value ) ) {
				$mla_alt_shortcode_ids = apply_filters_ref_array( 'mla_gallery_alt_shortcode_ids', array( $mla_alt_shortcode_ids, $arguments['mla_alt_ids_name'], &$attachments ) );
				if ( is_array( $mla_alt_shortcode_ids ) ) {
					if ( 0 == count( $mla_alt_shortcode_ids ) ) {
						foreach ( $attachments as $value ) {
							$mla_alt_shortcode_ids[] = $value->ID;
						} // foreach $attachments
					}

					// Apply the template when mla_alt_ids_template present
					if ( is_string( $mla_alt_ids_template ) ) {
						$page_values['alt_ids'] = implode( ',', $mla_alt_shortcode_ids );
						$replacement_values = MLAData::mla_expand_field_level_parameters( $mla_alt_ids_template, $attr, $page_values );
						$mla_alt_shortcode_ids = $arguments['mla_alt_ids_name'] . '="' . MLAData::mla_parse_template( $mla_alt_ids_template, $replacement_values ) . '"';
						unset( $page_values['alt_ids'] );
					} else {
						$mla_alt_shortcode_ids = $arguments['mla_alt_ids_name'] . '="' . implode( ',', $mla_alt_shortcode_ids ) . '"';
					}
				}

				if ( self::$mla_debug ) {
					$output = MLACore::mla_debug_flush();
				} else {
					$output = '';
				}

				// Execute the alternate gallery shortcode with the new parameters
				$content = apply_filters( 'mla_gallery_final_content', $content );

				if ( $is_photonic  ) {
					add_filter( 'pre_get_posts', 'MLAShortcode_Support::_photonic_pre_get_posts', 10, 4 );
					add_filter( 'wp_get_attachment_image_src', 'MLAShortcode_Support::_get_attachment_image_src', 10, 4 );
				}

				if ( ! empty( $content ) ) {
					$output .= do_shortcode( sprintf( '[%1$s %2$s %3$s]%4$s[/%1$s]', $arguments['mla_alt_shortcode'], $mla_alt_shortcode_ids, $mla_alt_shortcode_args, $content ) );
				} else {
					$output .= do_shortcode( sprintf( '[%1$s %2$s %3$s]', $arguments['mla_alt_shortcode'], $mla_alt_shortcode_ids, $mla_alt_shortcode_args ) );
				}

				if ( $is_photonic  ) {
					remove_filter( 'wp_get_attachment_image_src', 'MLAShortcode_Support::_get_attachment_image_src' );
					remove_filter( 'pre_get_posts', 'MLAShortcode_Support::_photonic_pre_get_posts' );
				}

				do_action( 'mla_gallery_end_alt_shortcode' );
				return $output;
			} /* is_null( $mla_alt_ids_value ) */ else {
				/*
				 * If an alternate value has been specified we must delay alt shortcode execution
				 * and accumulate $mla_alt_shortcode_ids in the template Item section.
				 */
				$processing_alt_ids_value = true;
			}
		} // mla_alt_shortcode

		if ( 'icon' == strtolower( $size ) ) {
			if ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ENABLE_MLA_ICONS ) ) {
				$size = array( 64, 64 );
			} else {
				$size = array( 60, 60 );
			}
		}

		// Feeds such as RSS, Atom or RDF do not require styled and formatted output
		if ( is_feed() ) {
			$output = "\n";
			foreach ( $attachments as $att_id => $attachment )
				$output .= wp_get_attachment_link( $att_id, $size, true ) . "\n";
			return $output;
		}

		// Check for Imagick thumbnail generation arguments
		$mla_viewer_required = false;
		if ( 'checked' == MLACore::mla_get_option( 'enable_mla_viewer' ) ) {
			if ( ! empty( $arguments['mla_viewer'] ) ) {
				// Split out the required suffix
				$mla_viewer_args = explode( ',', strtolower( $arguments['mla_viewer'] ) ) ;
				$mla_viewer_required = ( 1 < count( $mla_viewer_args ) && 'required' == $mla_viewer_args[1] );

				if ( 'single' == $mla_viewer_args[0] ) {
					$arguments['mla_single_thread'] = true;	
					$arguments['mla_viewer'] = true;
				} elseif ( 'true' == $mla_viewer_args[0] ) {
					$arguments['mla_viewer'] = true;
				} elseif ( 'required' == $mla_viewer_args[0] ) {
					$mla_viewer_required = true;
					$arguments['mla_viewer'] = true;
				} else {
					$arguments['mla_viewer'] = false;
				}
			}
		} else {
			$arguments['mla_viewer'] = false;
		}

		if ( $arguments['mla_viewer'] ) {
			// Test for Ghostscript here so debug messages can be recorded
			$ghostscript_path = MLACore::mla_get_option( 'ghostscript_path' );
			if ( self::mla_ghostscript_present( $ghostscript_path ) ) {
				$arguments['mla_viewer_extensions'] = array_filter( array_map( 'trim', explode( ',', $arguments['mla_viewer_extensions'] ) ) );
			} else {
				$arguments['mla_viewer_extensions'] = array();
			}

			// Convert limit (in MB) to float
			$arguments['mla_viewer_limit'] = abs( 0.0 + $arguments['mla_viewer_limit'] );

			// Fill width and/or height from explicit intermediate size
			if ( ( empty( $attr['mla_viewer_width'] ) && empty( $attr['mla_viewer_height'] ) ) && ! empty( $attr['size'] ) ) {
				$registered_dimensions = self::_registered_dimensions();
				if ( isset( $registered_dimensions[ $attr['size'] ] ) ) {
					$arguments['mla_viewer_width'] = absint( $registered_dimensions[ $attr['size'] ][0] );
					$arguments['mla_viewer_height'] = absint( $registered_dimensions[ $attr['size'] ][1] );

					if ( empty( $attr['mla_viewer_best_fit'] ) ) {
						$arguments['mla_viewer_best_fit'] = 'true';
					}
				}
			}

			$arguments['mla_viewer_width'] = absint( $arguments['mla_viewer_width'] );
			$arguments['mla_viewer_height'] = absint( $arguments['mla_viewer_height'] );
			$arguments['mla_viewer_page'] = absint( $arguments['mla_viewer_page'] );

			if ( isset( $arguments['mla_viewer_best_fit'] ) ) {
				$arguments['mla_viewer_best_fit'] = 'true' == strtolower( $arguments['mla_viewer_best_fit'] );
			}

			$arguments['mla_viewer_resolution'] = absint( $arguments['mla_viewer_resolution'] );
			$arguments['mla_viewer_quality'] = absint( $arguments['mla_viewer_quality'] );
		}

		/*
		 * The default MLA style template includes "margin: 1.5%" to put a bit of
		 * minimum space between the columns. "mla_margin" can be used to change
		 * this. "mla_itemwidth" can be used with "columns=0" to achieve a "responsive"
		 * layout.
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
			$float = 'none';  // before v2.90: is_rtl() ? 'right' : 'left';
		}

		$style_values = array_merge( $page_values, array(
			'mla_style' => $arguments['mla_style'],
			'mla_markup' => $arguments['mla_markup'],
			'itemtag' => tag_escape( $arguments['itemtag'] ),
			'icontag' => tag_escape( $arguments['icontag'] ),
			'captiontag' => tag_escape( $arguments['captiontag'] ),
			'columns' => $columns,
			'itemwidth' => $width_string,
			'margin' => $margin_string,
			'float' => $float,
			'size_class' => sanitize_html_class( $size_class ),
			'found_rows' => $found_rows,
			'current_rows' => $current_rows,
			'max_num_pages' => $max_num_pages,
		) );

		$style_template = $gallery_style = '';

		if ( 'theme' == strtolower( $style_values['mla_style'] ) ) {
			$use_mla_gallery_style = apply_filters( 'use_default_gallery_style', ! $html5 );
		} else {
			$use_mla_gallery_style = ( 'none' != strtolower( $style_values['mla_style'] ) );
		}

		if ( apply_filters( 'use_mla_gallery_style', $use_mla_gallery_style, $style_values['mla_style'] ) ) {
			$style_template = MLATemplate_support::mla_fetch_custom_template( $style_values['mla_style'], 'gallery', 'style' );
			if ( empty( $style_template ) ) {
				$style_values['mla_style'] = 'default';
				$style_template = MLATemplate_support::mla_fetch_custom_template( 'default', 'gallery', 'style' );
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

				$style_values = apply_filters( 'mla_gallery_style_values', $style_values );
				$style_template = apply_filters( 'mla_gallery_style_template', $style_template );
				$gallery_style = MLAData::mla_parse_template( $style_template, $style_values );
				$gallery_style = apply_filters( 'mla_gallery_style_parse', $gallery_style, $style_template, $style_values );

				// Clean up the styles to resolve extra "%" suffixes on width or margin (pre v1.42 values)
				$preg_pattern = array( '/([margin|width]:[^\%]*)\%\%/', '/([margin|width]:.*)auto\%/', '/([margin|width]:.*)inherit\%/' );
				$preg_replacement = array( '${1}%', '${1}auto', '${1}inherit',  );
				$gallery_style = preg_replace( $preg_pattern, $preg_replacement, $gallery_style );
			} // !empty template
		} // use_mla_gallery_style

		$markup_values = $style_values;

		$open_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'gallery', 'markup', 'open' );
		if ( empty( $open_template ) ) {
			$open_template = '';
		}

		// Emulate [gallery] handling of row open markup for the default template only
		if ( $html5 && ( 'default' == $markup_values['mla_markup'] ) ) {
			$row_open_template = '';
		} else{
			$row_open_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'gallery', 'markup', 'row-open' );

			if ( empty( $row_open_template ) ) {
				$row_open_template = '';
			}
		}

		$item_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'gallery', 'markup', 'item' );
		if ( empty( $item_template ) ) {
			$item_template = '';
		}

		// Emulate [gallery] handling of row close markup for the default template only
		if ( $html5 && ( 'default' == $markup_values['mla_markup'] ) ) {
			$row_close_template = '';
		} else{
			$row_close_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'gallery', 'markup', 'row-close' );

			if ( empty( $row_close_template ) ) {
				$row_close_template = '';
			}
		}

		$close_template = MLATemplate_support::mla_fetch_custom_template( $markup_values['mla_markup'], 'gallery', 'markup', 'close' );
		if ( empty( $close_template ) ) {
			$close_template = '';
		}

		// Look for gallery-level markup substitution parameters
		$new_text = $open_template . $row_open_template . $row_close_template . $close_template;
		$markup_values = MLAData::mla_expand_field_level_parameters( $new_text, $attr, $markup_values );

		if ( self::$mla_debug ) {
			$output = MLACore::mla_debug_flush();
		} else {
			$output = '';
		}

		// These $markup_values are used for both pagination and gallery output
		$markup_values = apply_filters( 'mla_gallery_open_values', $markup_values );

		if ( $is_gallery ) {
			$open_template = apply_filters( 'mla_gallery_open_template', $open_template );
			if ( empty( $open_template ) ) {
				$gallery_open = '';
			} else {
				$gallery_open = MLAData::mla_parse_template( $open_template, $markup_values );
			}

			$gallery_open = apply_filters( 'mla_gallery_open_parse', $gallery_open, $open_template, $markup_values );
			if ( ! $processing_alt_ids_value ) {
				$output .= apply_filters( 'mla_gallery_style', $gallery_style . $gallery_open, $style_values, $markup_values, $style_template, $open_template );
			}
		} else {
			// Handle 'previous_page', 'next_page', and 'paginate_links'
			$pagination_result = self::mla_process_pagination_output_types( $output_parameters, $markup_values, $arguments, $attr, $found_rows, $output );
			if ( false !== $pagination_result ) {
				return $pagination_result;
			}
		}

		/*
		 * For "previous_link", "current_link" and "next_link",
		 * discard all of the $attachments except the appropriate choice
		 */
		if ( ! $is_gallery ) {
			$link_type = $output_parameters[0];

			if ( ! in_array( $link_type, array ( 'previous_link', 'current_link', 'next_link' ) ) ) {
				return ''; // unknown output type
			}

			$is_wrap = isset( $output_parameters[1] ) && 'wrap' == $output_parameters[1];
			$current_id = empty( $arguments['id'] ) ? $markup_values['id'] : $arguments['id'];

			$pagination_index = 1;
			foreach ( $attachments as $id => $attachment ) {
				if ( $attachment->ID == $current_id ) {
					break;
				}

				$pagination_index++;
			}

			$target = NULL;
			if ( isset( $id ) ) {
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

				if ( isset( $attachments[ $target_id ] ) ) {
					$target = $attachments[ $target_id ];
				} elseif ( $is_wrap ) {
					switch ( $link_type ) {
						case 'previous_link':
							$target = array_pop( $attachments );
							break;
						case 'next_link':
							$target = array_shift( $attachments );
					} // link_type
				} // is_wrap
			} // isset id

			if ( isset( $target ) ) {
				$attachments = array( $target );			
			} elseif ( ! empty( $arguments['mla_nolink_text'] ) ) {
				return wp_kses( self::mla_process_shortcode_parameter( $arguments['mla_nolink_text'], $markup_values ), 'post');
			} else {
				return '';
			}
		} else { // ! is_gallery
			$link_type= '';
		}

		$column_index = 0;
		foreach ( $attachments as $id => $attachment ) {
			$item_values = apply_filters( 'mla_gallery_item_initial_values', $markup_values, $attachment );

			// fill in item-specific elements
			$item_values['index'] = (string) $is_gallery ? 1 + $column_index : $pagination_index;
			$item_values['last_in_row'] = '';

			$item_values['excerpt'] = wptexturize( $attachment->post_excerpt );
			$item_values['attachment_ID'] = $attachment->ID;
			$item_values['mime_type'] = $attachment->post_mime_type;
			$item_values['menu_order'] = $attachment->menu_order;
			$item_values['date'] = $attachment->post_date;
			$item_values['modified'] = $attachment->post_modified;
			$item_values['parent'] = $attachment->post_parent;
			$item_values['parent_name'] = '';
			$item_values['parent_type'] = '';
			$item_values['parent_title'] = '(' . __( 'Unattached', 'media-library-assistant' ) . ')';
			$item_values['parent_date'] = '';
			$item_values['parent_permalink'] = '';
			$item_values['title'] = wptexturize( $attachment->post_title );
			$item_values['slug'] = $attachment->post_name;
			$item_values['width'] = '';
			$item_values['height'] = '';
			$item_values['orientation'] = '';
			$item_values['image_meta'] = '';
			$item_values['image_alt'] = '';
			$item_values['base_file'] = '';
			$item_values['path'] = '';
			$item_values['file'] = '';
			$item_values['description'] = wptexturize( $attachment->post_content );
			$item_values['file_url'] = wp_get_attachment_url( $attachment->ID );
			$item_values['author_id'] = $attachment->post_author;
			$item_values['author'] = '';
			$item_values['caption'] = '';
			$item_values['captiontag_content'] = '';

			$user = get_user_by( 'id', $attachment->post_author );
			if ( isset( $user->data->display_name ) ) {
				$item_values['author'] = wptexturize( $user->data->display_name );
			} else {
				$item_values['author'] = __( 'unknown', 'media-library-assistant' );
			}

			$post_meta = MLAQuery::mla_fetch_attachment_metadata( $attachment->ID );
			$base_file = isset( $post_meta['mla_wp_attached_file'] ) ? $post_meta['mla_wp_attached_file'] : '';
			$original_image = isset( $post_meta['mla_wp_attachment_metadata']['original_image'] ) ? $post_meta['mla_wp_attachment_metadata']['original_image'] : '';
			$sizes = isset( $post_meta['mla_wp_attachment_metadata']['sizes'] ) ? $post_meta['mla_wp_attachment_metadata']['sizes'] : array();

			if ( ! empty( $post_meta['mla_wp_attachment_metadata']['width'] ) ) {
				$item_values['width'] = $post_meta['mla_wp_attachment_metadata']['width'];
				$width = absint( $item_values['width'] );
			} else {
				$width = 0;
			}

			if ( ! empty( $post_meta['mla_wp_attachment_metadata']['height'] ) ) {
				$item_values['height'] = $post_meta['mla_wp_attachment_metadata']['height'];
				$height = absint( $item_values['height'] );
			} else {
				$height = 0;
			}

			if ( $width && $height ) {
				$item_values['orientation'] = ( $height > $width ) ? 'portrait' : 'landscape';
			}

			if ( ! empty( $post_meta['mla_wp_attachment_metadata']['image_meta'] ) ) {
				$item_values['image_meta'] = var_export( $post_meta['mla_wp_attachment_metadata']['image_meta'], true );
			}

			if ( ! empty( $post_meta['mla_wp_attachment_image_alt'] ) ) {
				if ( is_array( $post_meta['mla_wp_attachment_image_alt'] ) ) {
					$item_values['image_alt'] = wptexturize( $post_meta['mla_wp_attachment_image_alt'][0] );
				} else {
					$item_values['image_alt'] = wptexturize( $post_meta['mla_wp_attachment_image_alt'] );
				}
			}

			if ( ! empty( $base_file ) ) {
				$last_slash = strrpos( $base_file, '/' );
				if ( false === $last_slash ) {
					$file_name = $base_file;
					$item_values['base_file'] = $base_file;
					$item_values['file'] = $base_file;
				} else {
					$file_name = substr( $base_file, $last_slash + 1 );
					$item_values['base_file'] = $base_file;;
					$item_values['path'] = substr( $base_file, 0, $last_slash + 1 );
					$item_values['file'] = $file_name;
				}
			} else {
				$file_name = '';
			}

			if ( 0 < $attachment->post_parent ) {
				$parent_info = MLAQuery::mla_fetch_attachment_parent_data( $attachment->post_parent );
				if ( isset( $parent_info['parent_name'] ) ) {
					$item_values['parent_name'] = $parent_info['parent_name'];
				}

				if ( isset( $parent_info['parent_type'] ) ) {
					$item_values['parent_type'] = wptexturize( $parent_info['parent_type'] );
				}

				if ( isset( $parent_info['parent_title'] ) ) {
					$item_values['parent_title'] = wptexturize( $parent_info['parent_title'] );
				}

				if ( isset( $parent_info['parent_date'] ) ) {
					$item_values['parent_date'] = wptexturize( $parent_info['parent_date'] );
				}

				$permalink = get_permalink( $attachment->post_parent );
				if ( false !== $permalink ) {
					$item_values['parent_permalink'] = $permalink;
				}
			} // has parent

			// Add attachment-specific field-level substitution parameters
			$new_text = isset( $item_template ) ? $item_template : '';
			foreach( $mla_item_specific_arguments as $index => $value ) {
				if ( !empty( $arguments[ $index ] ) ) {
					$new_text .= str_replace( '{+', '[+', str_replace( '+}', '+]', $arguments[ $index ] ) );
				}
			}
			$item_values = MLAData::mla_expand_field_level_parameters( $new_text, $attr, $item_values, $attachment->ID );

			if ( $item_values['captiontag'] ) {
				$item_values['caption'] = wptexturize( $attachment->post_excerpt );
				if ( ! empty( $arguments['mla_caption'] ) ) {
					$item_values['caption'] = wptexturize( self::mla_process_shortcode_parameter( $arguments['mla_caption'], $item_values ) );
				}
			} else {
				$item_values['caption'] = '';
			}

			if ( ! empty( $arguments['mla_link_text'] ) ) {
				$link_text = wp_kses( self::mla_process_shortcode_parameter( $arguments['mla_link_text'], $item_values ), 'post' );
			} else {
				$link_text = false;
			}

			/*
			 * As of WP 3.7, this function returns "<a href='$url'>$link_text</a>", where
			 * $link_text can be an image thumbnail or a text link. The "title=" attribute
			 * was dropped. The function is defined in /wp-includes/post-template.php.
			 *
			 * As of WP 4.1, this function has an additional optional parameter, an "Array or
			 * string of attributes", used in the [gallery] shortcode to tie the link to a
			 * caption with 'aria-describedby'. The caption has a matching 'id' attribute
			 * "$selector-#id". See below for the MLA equivalent processing.
			 */
			if ( 'attachment' == $attachment->post_type ) {
				// Avoid native PDF thumbnails, if specified
				if ( $mla_viewer_required && in_array( $attachment->post_mime_type, array( 'application/pdf' ) ) ) {
					$item_values['pagelink'] = sprintf( '<a href=\'%1$s\'>%2$s</a>', get_permalink( $attachment->ID ), $attachment->post_title );
					$item_values['filelink'] = sprintf( '<a href=\'%1$s\'>%2$s</a>', $attachment->guid, $attachment->post_title );
				} else {
					add_filter( 'wp_get_attachment_image_src', 'MLAShortcode_Support::_get_attachment_image_src', 10, 4 );

					// The fourth argument, "show icon" is always false because we handle it in _get_attachment_image_src()
					$item_values['pagelink'] = wp_get_attachment_link($attachment->ID, $size, true, false, $link_text);
					$item_values['filelink'] = wp_get_attachment_link($attachment->ID, $size, false, false, $link_text);
					
					self::$size_parameter = 'icon_only';
					$item_values['icon_pagelink'] = wp_get_attachment_link($attachment->ID, 'icon', true, false, $link_text);
					$item_values['icon_filelink'] = wp_get_attachment_link($attachment->ID, 'icon', false, false, $link_text);
					self::$size_parameter = strtolower( $arguments['size'] );

					remove_filter( 'wp_get_attachment_image_src', 'MLAShortcode_Support::_get_attachment_image_src' );
				}
			} else {
				$thumbnail_content = $attachment->post_title;

				if ( ( 'none' !== $arguments['size'] ) && ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ENABLE_FEATURED_IMAGE ) ) ) {
					// Look for the "Featured Image" as an alternate thumbnail for PDFs, etc.
					$thumb = get_the_post_thumbnail( $attachment->ID, $size, array( 'class' => 'attachment-thumbnail' ) );
					$thumb = apply_filters( 'mla_gallery_featured_image', $thumb, $attachment, $size, $item_values );

					if ( ! empty( $thumb ) ) {
						$thumbnail_content = $thumb;
					}
				}

				$item_values['pagelink'] = sprintf( '<a href=\'%1$s\'>%2$s</a>', $attachment->guid, $thumbnail_content );
				$item_values['filelink'] = sprintf( '<a href=\'%1$s\'>%2$s</a>', get_permalink( $attachment->ID ), $thumbnail_content );
				$item_values['icon_pagelink'] = '';
				$item_values['icon_filelink'] = '';
			}

			if ( in_array( $attachment->post_mime_type, array( 'image/svg+xml' ) ) ) {
				$registered_dimensions = self::_registered_dimensions();
				if ( isset( $registered_dimensions[ $size_class ] ) ) {
					$dimensions = $registered_dimensions[ $size_class ];
				} else {
					$dimensions = $registered_dimensions['thumbnail'];
				}

				$thumb = preg_replace( '/width=\"[^\"]*\"/', sprintf( 'width="%1$d"', $dimensions[1] ), $item_values['pagelink'] );
				$item_values['pagelink'] = preg_replace( '/height=\"[^\"]*\"/', sprintf( 'height="%1$d"', $dimensions[0] ), $thumb );
				$thumb = preg_replace( '/width=\"[^\"]*\"/', sprintf( 'width="%1$d"', $dimensions[1] ), $item_values['filelink'] );
				$item_values['filelink'] = preg_replace( '/height=\"[^\"]*\"/', sprintf( 'height="%1$d"', $dimensions[0] ), $thumb );
			} // SVG thumbnail

			/*
			 * Apply the Gallery Display Content parameters.
			 * Note that $link_attributes and $rollover_text
			 * are used in the Google Viewer code below
			 */
			$link_attributes = '';
			if ( ! empty( $arguments['mla_rollover_text'] ) ) {
				$rollover_text = esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_rollover_text'], $item_values ) );

				/*
				 * The "title=" attribute was removed in WP 3.7+, but look for it anyway.
				 * If it's not there, add the "title=" value to the link attributes.
				 */
				if ( false === strpos( $item_values['pagelink'], ' title=' ) ) {
					$link_attributes .= 'title="' . $rollover_text . '" ';
				}else {
					// Replace single- and double-quote delimited values
					$item_values['pagelink'] = preg_replace('# title=\'([^\']*)\'#', " title='{$rollover_text}'", $item_values['pagelink'] );
					$item_values['pagelink'] = preg_replace('# title=\"([^\"]*)\"#', " title=\"{$rollover_text}\"", $item_values['pagelink'] );
					$item_values['filelink'] = preg_replace('# title=\'([^\']*)\'#', " title='{$rollover_text}'", $item_values['filelink'] );
					$item_values['filelink'] = preg_replace('# title=\"([^\"]*)\"#', " title=\"{$rollover_text}\"", $item_values['filelink'] );
				}
			} else {
				$rollover_text = esc_attr( $item_values['title'] );
			}

			if ( ! empty( $arguments['mla_target'] ) ) {
				$link_attributes .= 'target="' . esc_attr( $arguments['mla_target'] ) . '" ';
			}

			if ( ! empty( $arguments['mla_link_attributes'] ) ) {
				$link_attributes .= MLAShortcode_Support::mla_esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_link_attributes'], $item_values ) ) . ' ';
			}

			if ( ! empty( $arguments['mla_link_class'] ) ) {
				$link_attributes .= 'class="' . esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_link_class'], $item_values ) ) . '" ';
			}

			if ( ! empty( $link_attributes ) ) {
				$item_values['pagelink'] = preg_replace( '#<a( .*)href=#', '<a$1' . $link_attributes . 'href=', $item_values['pagelink'] );
				$item_values['filelink'] = preg_replace( '#<a( .*)href=#', '<a$1' . $link_attributes . 'href=', $item_values['filelink'] );
			}

			/*
			 * Process the <img> tag, if present
			 * Note that $image_attributes, $image_class and $image_alt
			 * are used in the Google Viewer code below
			 */
			if ( ! empty( $arguments['mla_image_attributes'] ) ) {
				$image_attributes = MLAShortcode_Support::mla_esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_image_attributes'], $item_values ) ) . ' ';
			} else {
				$image_attributes = '';
			}

			/*
			 * WordPress 4.1 ties the <img> tag to the caption with 'aria-describedby'
			 * has a matching 'id' attribute "$selector-#id".
			 */
			if ( trim( $item_values['caption'] ) && ( false === strpos( $image_attributes, 'aria-describedby=' ) ) && ( 'default' == $item_values['mla_markup'] ) ) {
				$image_attributes .= 'aria-describedby="' . $item_values['selector'] . '-' . $item_values['attachment_ID'] . '" ';
			}

			if ( ! empty( $arguments['mla_image_class'] ) ) {
				$image_class = esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_image_class'], $item_values ) );
			} else {
				$image_class = '';
			}

			if ( ! empty( $arguments['mla_image_alt'] ) ) {
				$image_alt = esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_image_alt'], $item_values ) );
			} else {
				$image_alt = '';
			}

			/*
			 * Look for alt= and class= attributes in $image_attributes. If found,
			 * they override and completely replace the corresponding values.
			 */
			$class_replace = false;
			if ( ! empty( $image_attributes ) ) {
				$match_count = preg_match( '#alt=(([\'\"])([^\']+?)\2)#', $image_attributes, $matches, PREG_OFFSET_CAPTURE );
				if ( 1 === $match_count ) {
					$image_alt = $matches[3][0];
					$image_attributes = substr_replace( $image_attributes, '', $matches[0][1], strlen( $matches[0][0] ) );
				}

				$match_count = preg_match( '#class=(([\'\"])([^\']+?)\2)#', $image_attributes, $matches, PREG_OFFSET_CAPTURE );
				if ( 1 === $match_count ) {
					$class_replace = true;
					$image_class = $matches[3][0];
					$image_attributes = substr_replace( $image_attributes, '', $matches[0][1], strlen( $matches[0][0] ) );
				}

				$image_attributes = trim( $image_attributes );
				if ( ! empty( $image_attributes ) ) {
					$image_attributes .= ' ';
				}
			}

			if ( false !== strpos( $item_values['pagelink'], '<img ' ) ) {
				if ( ! empty( $image_attributes ) ) {
					$item_values['pagelink'] = str_replace( '<img ', '<img ' . $image_attributes, $item_values['pagelink'] );
					$item_values['filelink'] = str_replace( '<img ', '<img ' . $image_attributes, $item_values['filelink'] );
				}

				// Extract existing class values and add to them
				if ( ! empty( $image_class ) ) {
					$match_count = preg_match_all( '# class=\"([^\"]+)\" #', $item_values['pagelink'], $matches, PREG_OFFSET_CAPTURE );
					if ( ! ( $class_replace || ( $match_count == false ) || ( $match_count == 0 ) ) ) {
						$class = $matches[1][0][0] . ' ' . $image_class;
					} else {
						$class = $image_class;
					}

					$item_values['pagelink'] = preg_replace('# class=\"([^\"]*)\"#', " class=\"{$class}\"", $item_values['pagelink'] );
					$item_values['filelink'] = preg_replace('# class=\"([^\"]*)\"#', " class=\"{$class}\"", $item_values['filelink'] );
				}

				if ( ! empty( $image_alt ) ) {
					$item_values['pagelink'] = preg_replace('# alt=\"([^\"]*)\"#', " alt=\"{$image_alt}\"", $item_values['pagelink'] );
					$item_values['filelink'] = preg_replace('# alt=\"([^\"]*)\"#', " alt=\"{$image_alt}\"", $item_values['filelink'] );
				}
			} // process <img> tag

			// Create download and named transfer links with all Content Parameters
			$match_count = preg_match( '#href=\'([^\']+)\'#', $item_values['filelink'], $matches, PREG_OFFSET_CAPTURE );
			if ( ! ( ( $match_count == false ) || ( $match_count == 0 ) ) ) {
				/*/ Forced download link - NO LONGER ALLOWED, SEE BELOW
				$args = array(
					'mla_download_file' => urlencode( $item_values['base_dir'] . '/' . $item_values['base_file'] ),
					'mla_download_type' => $item_values['mime_type']
				);

				if ( 'log' == $arguments['mla_debug'] ) {
					$args['mla_debug'] = 'log';
				}

				$item_values['downloadlink_url'] = add_query_arg( $args, MLA_PLUGIN_URL . 'includes/mla-file-downloader.php' );
				$item_values['downloadlink'] = preg_replace( '"' . $matches[0][0] . '"', sprintf( 'href=\'%1$s\'', $item_values['downloadlink_url'] ), $item_values['filelink'] ); // */

				// AJAX-based Named Transfer link
				$args = array(
					'action' => 'mla_named_transfer',
					'mla_item' => $attachment->post_name,
					'mla_disposition' => ( 'download' === $arguments['link'] ) ? 'attachment' : 'inline',
				);

				if ( 'log' == $arguments['mla_debug'] ) {
					$args['mla_debug'] = 'log';
				}

				$item_values['transferlink_url'] = add_query_arg( $args, admin_url( 'admin-ajax.php' ) );
				$item_values['transferlink'] = preg_replace( '"' . $matches[0][0] . '"', sprintf( 'href=\'%1$s\'', $item_values['transferlink_url'] ), $item_values['filelink'] );

				// AJAX-based Named Transfer link for forced downloads
				$args = array(
					'action' => 'mla_named_transfer',
					'mla_item' => $attachment->post_name,
					'mla_disposition' => 'attachment',
				);

				if ( 'log' == $arguments['mla_debug'] ) {
					$args['mla_debug'] = 'log';
				}

				$item_values['downloadlink_url'] = add_query_arg( $args, admin_url( 'admin-ajax.php' ) );
				$item_values['downloadlink'] = preg_replace( '"' . $matches[0][0] . '"', sprintf( 'href=\'%1$s\'', $item_values['transferlink_url'] ), $item_values['filelink'] );
			} else {
				$item_values['downloadlink_url'] = $item_values['filelink_url'];
				$item_values['downloadlink'] = $item_values['filelink'];

				$item_values['transferlink_url'] = $item_values['filelink_url'];
				$item_values['transferlink'] = $item_values['filelink'];
			}

			switch ( $arguments['link'] ) {
				case 'permalink':
				case 'post':
					$item_values['link'] = $item_values['pagelink'];
					break;
				case 'original':
					if ( !empty( $original_image ) ) {
						$item_values['link'] = str_replace( $file_name, $original_image, $item_values['filelink'] );
					} else {
						$item_values['link'] = $item_values['filelink'];
					}
					
					break;
				case 'file':
				case 'full':
					$item_values['link'] = $item_values['filelink'];
					break;
				case 'download':
					$item_values['link'] = $item_values['downloadlink'];
					break;
				default:
					$item_values['link'] = $item_values['filelink'];

					// Check for link to specific (registered) file size, image types only
					if ( array_key_exists( $arguments['link'], $sizes ) ) {
						if ( 0 === strpos( $attachment->post_mime_type, 'image/' ) ) {
							$target_file = $sizes[ $arguments['link'] ]['file'];
							$item_values['link'] = str_replace( $file_name, $target_file, $item_values['filelink'] );
						}
					}
			} // switch 'link'

			// Replace link with AJAX-based item transfer using post slug
			if ( $arguments['mla_named_transfer'] ) {
				$item_values['link'] = $item_values['transferlink'];
			}

			// Extract target and thumbnail fields
			$match_count = preg_match_all( '#href=\'([^\']+)\'#', $item_values['pagelink'], $matches, PREG_OFFSET_CAPTURE );
 			if ( ! ( ( $match_count == false ) || ( $match_count == 0 ) ) ) {
				$item_values['pagelink_url'] = $matches[1][0][0];
			} else {
				$item_values['pagelink_url'] = '';
			}

			$match_count = preg_match_all( '#href=\'([^\']+)\'#', $item_values['filelink'], $matches, PREG_OFFSET_CAPTURE );
			if ( ! ( ( $match_count == false ) || ( $match_count == 0 ) ) ) {
				$item_values['filelink_url'] = $matches[1][0][0];
			} else {
				$item_values['filelink_url'] = '';
			}

			// Extract icon image tag and src URL
			$match_count = preg_match_all( '#(\<img [^\>]+\>)#', $item_values['icon_filelink'], $matches, PREG_OFFSET_CAPTURE );
			if ( ! ( ( $match_count == false ) || ( $match_count == 0 ) ) ) {
				$item_values['icon_img'] = $matches[1][0][0];
			} else {
				$item_values['icon_img'] = '';
			}

			$match_count = preg_match_all( '#src=\"([^\"]+)\"#', $item_values['icon_img'], $matches, PREG_OFFSET_CAPTURE );
			if ( ! ( ( $match_count == false ) || ( $match_count == 0 ) ) ) {
				$item_values['icon_src'] = $matches[1][0][0];
			} else {
				$item_values['icon_src'] = '';
			}

			/*
			 * Override the link value; leave filelink and pagelink unchanged
			 * Note that $link_href is used in the Google Viewer code below
			 */
			if ( ! empty( $arguments['mla_link_href'] ) ) {
				$link_href = esc_url( self::mla_process_shortcode_parameter( $arguments['mla_link_href'], $item_values ) );

				// Replace single- and double-quote delimited values
				$item_values['link'] = preg_replace('# href=\'([^\']*)\'#', " href='{$link_href}'", $item_values['link'] );
				$item_values['link'] = preg_replace('# href=\"([^\"]*)\"#', " href=\"{$link_href}\"", $item_values['link'] );
			} else {
				$link_href = '';
			}

			$match_count = preg_match_all( '#href=\'([^\']+)\'#', $item_values['link'], $matches, PREG_OFFSET_CAPTURE );
			if ( ! ( ( $match_count == false ) || ( $match_count == 0 ) ) ) {
				$item_values['link_url'] = $matches[1][0][0];
			} else {
				$item_values['link_url'] = '';
			}

			$match_count = preg_match_all( '#(\<a [^\>]+\>)(.*)\</a\>#', $item_values['link'], $matches, PREG_OFFSET_CAPTURE );
			if ( ! ( ( $match_count == false ) || ( $match_count == 0 ) ) ) {
				$link_tag = $matches[1][0][0];
				$item_values['thumbnail_content'] = $matches[2][0][0];
			} else {
				$link_tag = '';
				$item_values['thumbnail_content'] = '';
			}

			$match_count = preg_match_all( '# width=\"([^\"]+)\" height=\"([^\"]+)\" src=\"([^\"]+)\" #', $item_values['link'], $matches, PREG_OFFSET_CAPTURE );
			if ( ! ( ( $match_count == false ) || ( $match_count == 0 ) ) ) {
				$item_values['thumbnail_width'] = $matches[1][0][0];
				$item_values['thumbnail_height'] = $matches[2][0][0];
				$item_values['thumbnail_url'] = $matches[3][0][0];
			} else {
				$item_values['thumbnail_width'] = '';
				$item_values['thumbnail_height'] = '';
				$item_values['thumbnail_url'] = '';

				/* Replaced by logic in _get_attachment_image_src v2.90
				if ( ( 'none' !== $arguments['size'] ) && ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ENABLE_FEATURED_IMAGE ) ) ) {
					// Look for the "Featured Image" as an alternate thumbnail for PDFs, etc.
					$feature = get_the_post_thumbnail( $attachment->ID, $size, array( 'class' => 'attachment-thumbnail' ) );
					$feature = apply_filters( 'mla_gallery_featured_image', $feature, $attachment, $size, $item_values );

					if ( ! empty( $feature ) ) {
						$match_count = preg_match_all( '# width=\"([^\"]+)\" height=\"([^\"]+)\" src=\"([^\"]+)\" #', $feature, $matches, PREG_OFFSET_CAPTURE );
						if ( ! ( ( $match_count == false ) || ( $match_count == 0 ) ) ) {
							$item_values['link'] = $link_tag . $feature . '</a>';
							$item_values['thumbnail_content'] = $feature;
							$item_values['thumbnail_width'] = $matches[1][0][0];
							$item_values['thumbnail_height'] = $matches[2][0][0];
							$item_values['thumbnail_url'] = $matches[3][0][0];
						}
					}
				} // enable_featured_image */
			}

			// Now that we have thumbnail_content we can check for 'span' and 'none'
			if ( 'none' == $arguments['link'] ) {
				$item_values['link'] = $item_values['thumbnail_content'];
			} elseif ( 'span' == $arguments['link'] ) {
				$item_values['link'] = sprintf( '<span %1$s>%2$s</span>', $link_attributes, $item_values['thumbnail_content'] );
			}

			/*
			 * Check for Imagick thumbnail generation, uses above-defined
			 * $link_attributes (includes target), $rollover_text, $link_href (link only),
			 * $image_attributes, $image_class, $image_alt
			 */
			if ( $arguments['mla_viewer'] && empty( $item_values['thumbnail_url'] ) ) {
				// Check for a match on file extension
				$last_dot = strrpos( $item_values['file'], '.' );
				if ( !( false === $last_dot) ) {
					$extension = substr( $item_values['file'], $last_dot + 1 );
					if ( in_array( $extension, $arguments['mla_viewer_extensions'] ) ) {
						// Default to an icon if thumbnail generation is not available
						$icon_url = wp_mime_type_icon( $attachment->ID );
						$upload_dir = wp_upload_dir();
						$args = array(
							'mla_stream_file' => urlencode( 'file://' . $upload_dir['basedir'] . '/' . $item_values['base_file'] ),
						);

						if ( 'log' == $arguments['mla_debug'] ) {
							$args['mla_debug'] = 'log';
						}

						if ( $arguments['mla_single_thread'] ) {
							$args['mla_single_thread'] = 'true';
						}

						if ( $arguments['mla_viewer_width'] ) {
							$args['mla_stream_width'] = $arguments['mla_viewer_width'];
						}

						if ( $arguments['mla_viewer_height'] ) {
							$args['mla_stream_height'] = $arguments['mla_viewer_height'];
						}

						if ( isset( $arguments['mla_viewer_best_fit'] ) ) {
							$args['mla_stream_fit'] = $arguments['mla_viewer_best_fit'] ? '1' : '0';
						}

						/*
						 * Non-standard location, if not empty. Write the value to a file that can be
						 * found by the stand-alone (no WordPress) image stream processor.
						 */
						$ghostscript_path = MLACore::mla_get_option( 'ghostscript_path' );
						if ( ! empty( $ghostscript_path ) ) {
							if ( false !== @file_put_contents( dirname( __FILE__ ) . '/' . 'mla-ghostscript-path.txt', $ghostscript_path ) ) {
								$args['mla_ghostscript_path'] = 'custom';
							}
						}

						if ( self::mla_ghostscript_present( $ghostscript_path ) ) {
							// Optional upper limit (in MB) on file size
							if ( $limit = ( 1024 * 1024 ) * $arguments['mla_viewer_limit'] ) {
								$file_size = 0 + @filesize( $item_values['base_dir'] . '/' . $item_values['base_file'] );
								if ( ( 0 < $file_size ) && ( $file_size > $limit ) ) {
									$file_size = 0;
								}
							} else {
								$file_size = 1;
							}

							// Generate "real" thumbnail
							if ( $file_size ) {
								$frame = ( 0 < $arguments['mla_viewer_page'] ) ? $arguments['mla_viewer_page'] - 1 : 0;
								if ( $frame ) {
									$args['mla_stream_frame'] = $frame;
								}

								if ( $arguments['mla_viewer_resolution'] ) {
									$args['mla_stream_resolution'] = $arguments['mla_viewer_resolution'];
								}

								if ( $arguments['mla_viewer_quality'] ) {
									$args['mla_stream_quality'] = $arguments['mla_viewer_quality'];
								}

								if ( ! empty( $arguments['mla_viewer_type'] ) ) {
									$args['mla_stream_type'] = $arguments['mla_viewer_type'];
								}

								// For efficiency, image streaming is done outside WordPress
								$icon_url = add_query_arg( $args, MLA_PLUGIN_URL . 'includes/mla-stream-image.php' );
							}
						}

						// <img> tag (thumbnail_text)
						if ( ! empty( $image_class ) ) {
							$image_class = ' class="' . $image_class . '"';
						}

						if ( ! empty( $image_alt ) ) {
							$image_alt = ' alt="' . $image_alt . '"';
						} elseif ( ! empty( $item_values['caption'] ) ) {
							$image_alt = ' alt="' . $item_values['caption'] . '"';
						}

						$item_values['thumbnail_content'] = sprintf( '<img %1$ssrc="%2$s"%3$s%4$s>', $image_attributes, $icon_url, $image_class, $image_alt );

						// Filelink, pagelink and link. The "title=" attribute is in $link_attributes for WP 3.7+.
						if ( false === strpos( $link_attributes, 'title=' ) ) {
							$item_values['pagelink'] = sprintf( '<a %1$shref="%2$s" title="%3$s">%4$s</a>', $link_attributes, $item_values['pagelink_url'], $rollover_text, $item_values['thumbnail_content'] );
							$item_values['filelink'] = sprintf( '<a %1$shref="%2$s" title="%3$s">%4$s</a>', $link_attributes, $item_values['filelink_url'], $rollover_text, $item_values['thumbnail_content'] );
							$item_values['downloadlink'] = sprintf( '<a %1$shref="%2$s" title="%3$s">%4$s</a>', $link_attributes, $item_values['downloadlink_url'], $rollover_text, $item_values['thumbnail_content'] );
						} else {
							$item_values['pagelink'] = sprintf( '<a %1$shref="%2$s">%3$s</a>', $link_attributes, $item_values['pagelink_url'], $item_values['thumbnail_content'] );
							$item_values['filelink'] = sprintf( '<a %1$shref="%2$s">%3$s</a>', $link_attributes, $item_values['filelink_url'], $item_values['thumbnail_content'] );
							$item_values['downloadlink'] = sprintf( '<a %1$shref="%2$s">%3$s</a>', $link_attributes, $item_values['downloadlink_url'], $item_values['thumbnail_content'] );
						}
						if ( ! empty( $link_href ) ) {
							$item_values['link'] = sprintf( '<a %1$shref="%2$s" title="%3$s">%4$s</a>', $link_attributes, $link_href, $rollover_text, $item_values['thumbnail_content'] );
						} elseif ( 'permalink' == $arguments['link'] || 'post' == $arguments['link'] ) {
							$item_values['link'] = $item_values['pagelink'];
						} elseif ( 'file' == $arguments['link'] || 'full' == $arguments['link'] ) {
							$item_values['link'] = $item_values['filelink'];
						} elseif ( 'download' == $arguments['link'] ) {
							$item_values['link'] = $item_values['downloadlink'];
						} elseif ( 'span' == $arguments['link'] ) {
							$item_values['link'] = sprintf( '<a %1$s>%2$s</a>', $link_attributes, $item_values['thumbnail_content'] );
						} else {
							$item_values['link'] = $item_values['thumbnail_content'];
						}
					} // viewer extension
				} // has extension
			} // mla_viewer

			if ( $is_gallery ) {
				// Start of row markup
				if ( $markup_values['columns'] > 0 && $column_index % $markup_values['columns'] == 0 ) {
					$markup_values = apply_filters( 'mla_gallery_row_open_values', $markup_values );
					$row_open_template = apply_filters( 'mla_gallery_row_open_template', $row_open_template );
					$parse_value = MLAData::mla_parse_template( $row_open_template, $markup_values );
					if ( ! $processing_alt_ids_value ) {
						$output .= apply_filters( 'mla_gallery_row_open_parse', $parse_value, $row_open_template, $markup_values );
					}
				}

				// item markup
				$column_index++;
				if ( $item_values['columns'] > 0 && $column_index % $item_values['columns'] == 0 ) {
					$item_values['last_in_row'] = 'last_in_row';
				} else {
					$item_values['last_in_row'] = '';
				}

				// Conditional caption tag to replicate WP 4.1+, now used in the default markup template.
				if ( $item_values['captiontag'] && trim( $item_values['caption'] ) ) {
//					$item_values['captiontag_content'] = '<' . $item_values['captiontag'] . " class='wp-caption-text gallery-caption' id='" . $item_values['selector'] . '-' . $item_values['attachment_ID'] . "'>\n\t\t" . $item_values['caption'] . "\n\t</" . $item_values['captiontag'] . ">\n";
					$item_values['captiontag_content'] = '<' . $item_values['captiontag'] . " class='wp-caption-text gallery-caption' id='" . $item_values['selector'] . '-' . $item_values['attachment_ID'] . "'>\n\t" . $item_values['caption'] . "\n\t</" . $item_values['captiontag'] . ">";
				} else {
					$item_values['captiontag_content'] = '';
				}

				$item_values = apply_filters( 'mla_gallery_item_values', $item_values );

				// Accumulate mla_alt_shortcode_ids when mla_alt_ids_value present
				if ( $processing_alt_ids_value ) {
					$item_values = MLAData::mla_expand_field_level_parameters( $mla_alt_ids_value, $attr, $item_values );
					$mla_alt_shortcode_ids[] = MLAData::mla_parse_template( $mla_alt_ids_value, $item_values );
					continue;
				}

				$item_template = apply_filters( 'mla_gallery_item_template', $item_template );
				$parse_value = MLAData::mla_parse_template( $item_template, $item_values );
				$output .= apply_filters( 'mla_gallery_item_parse', $parse_value, $item_template, $item_values );

				// End of row markup
				if ( $markup_values['columns'] > 0 && $column_index % $markup_values['columns'] == 0 ) {
					$markup_values = apply_filters( 'mla_gallery_row_close_values', $markup_values );
					$row_close_template = apply_filters( 'mla_gallery_row_close_template', $row_close_template );
					$parse_value = MLAData::mla_parse_template( $row_close_template, $markup_values );
					$output .= apply_filters( 'mla_gallery_row_close_parse', $parse_value, $row_close_template, $markup_values );
				}
			} // is_gallery
			elseif ( ! empty( $link_type ) ) {
				return $item_values['link'];
			}
		} // foreach attachment

		// Execute the alternate gallery shortcode with the new parameters
		if ( $processing_alt_ids_value ) {
			// Apply the template when mla_alt_ids_template present
			if ( is_string( $mla_alt_ids_template ) ) {
				$markup_values['alt_ids'] = implode( ',', $mla_alt_shortcode_ids );
				$replacement_values = MLAData::mla_expand_field_level_parameters( $mla_alt_ids_template, $attr, $markup_values );
				$mla_alt_shortcode_ids = $arguments['mla_alt_ids_name'] . '="' . MLAData::mla_parse_template( $mla_alt_ids_template, $replacement_values ) . '"';
				unset( $markup_values['alt_ids'] );
			} else {
				$mla_alt_shortcode_ids = $arguments['mla_alt_ids_name'] . '="' . implode( ',', $mla_alt_shortcode_ids ) . '"';
			}

			$content = apply_filters( 'mla_gallery_final_content', $content );
			if ( ! empty( $content ) ) {
				return $output . do_shortcode( sprintf( '[%1$s %2$s %3$s]%4$s[/%1$s]', $arguments['mla_alt_shortcode'], $mla_alt_shortcode_ids, $mla_alt_shortcode_args, $content ) );
			} else {
				return $output . do_shortcode( sprintf( '[%1$s %2$s %3$s]', $arguments['mla_alt_shortcode'], $mla_alt_shortcode_ids, $mla_alt_shortcode_args ) );
			}
		}

		if ( $is_gallery ) {
			// Close out partial row
			if ( ! ($markup_values['columns'] > 0 && $column_index % $markup_values['columns'] == 0 ) ) {
				$markup_values = apply_filters( 'mla_gallery_row_close_values', $markup_values );
				$row_close_template = apply_filters( 'mla_gallery_row_close_template', $row_close_template );
				$parse_value = MLAData::mla_parse_template( $row_close_template, $markup_values );
				$output .= apply_filters( 'mla_gallery_row_close_parse', $parse_value, $row_close_template, $markup_values );
			}

			$markup_values = apply_filters( 'mla_gallery_close_values', $markup_values );
			$close_template = apply_filters( 'mla_gallery_close_template', $close_template );
			$parse_value = MLAData::mla_parse_template( $close_template, $markup_values );
			$output .= apply_filters( 'mla_gallery_close_parse', $parse_value, $close_template, $markup_values );
		} // is_gallery

		return $output;
	}

	/**
	 * Computes image dimensions for scalable graphics, e.g., SVG 
	 *
	 * @since 2.20
	 *
	 * @return array 
	 */
	private static function _registered_dimensions() {
		global $_wp_additional_image_sizes;

		if ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ENABLE_MLA_ICONS ) ) {
			$sizes = array( 'icon' => array( 64, 64 ) );
		} else {
			$sizes = array( 'icon' => array( 60, 60 ) );
		}

		foreach( get_intermediate_image_sizes() as $s ) {
			$sizes[ $s ] = array( 0, 0 );

			if( in_array( $s, array( 'thumbnail', 'medium', 'large' ) ) ) {
				$sizes[ $s ][0] = get_option( $s . '_size_w' );
				$sizes[ $s ][1] = get_option( $s . '_size_h' );
			} else {
				if( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[ $s ] ) ) {
					$sizes[ $s ] = array( $_wp_additional_image_sizes[ $s ]['width'], $_wp_additional_image_sizes[ $s ]['height'], );
				}
			}
		}
 
		return $sizes;
	} // _registered_dimensions

	/**
	 * Handles brace/bracket escaping and parses template for a shortcode parameter
	 *
	 * @since 2.20
	 *
	 * @param string raw shortcode parameter, e.g., "text {+field+} {brackets} \\{braces\\}"
	 * @param string template substitution values, e.g., ('instance' => '1', ...  )
	 *
	 * @return string parameter with brackets, braces, substitution parameters and templates processed
	 */
	public static function mla_process_shortcode_parameter( $text, $markup_values ) {
		$new_text = str_replace( '{\+', '\[\+', str_replace( '+\}', '+\\\\]', $text ) );
		$new_text = str_replace( '{', '[', str_replace( '}', ']', $new_text ) );
		$new_text = str_replace( '\[', '{', str_replace( '\]', '}', $new_text ) );
		return MLAData::mla_parse_template( $new_text, $markup_values );
	}

	/**
	 * Replaces or removes a query argument, preserving url encoding
	 *
	 * @since 2.84
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
		if ( !empty( $parts['fragment'] ) ) {
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
		if ( !empty( $clean_query ) ) {
			return $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . '?' . $clean_query . $parts['fragment'];
		} else {
			return $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . $parts['fragment'];
		}
	} // _replace_query_parameter

	/**
	 * Handles pagnation output types 'previous_page', 'next_page', and 'paginate_links'
	 *
	 * @since 2.20
	 *
	 * @param array	value(s) for mla_output_type parameter
	 * @param array template substitution values, e.g., ('instance' => '1', ...  )
	 * @param array merged default and passed shortcode parameter values
	 * @param integer number of attachments in the gallery, without pagination
	 * @param string output text so far, may include debug values
	 *
	 * @return string empty string, mla_nolink_text or string with HTML for pagination output types
	 */
	private static function _paginate_links( $output_parameters, $markup_values, $arguments, $found_rows, $output = '' ) {
//error_log( __LINE__ . ' _paginate_links output_parameters = ' . var_export( $output_parameters, true ), 0 );
//error_log( __LINE__ . ' _paginate_links markup_values = ' . var_export( $markup_values, true ), 0 );
//error_log( __LINE__ . ' _paginate_links arguments = ' . var_export( $arguments, true ), 0 );
		if ( 2 > $markup_values['last_page'] ) {
			if ( ! empty( $arguments['mla_nolink_text'] ) ) {
				return wp_kses( self::mla_process_shortcode_parameter( $arguments['mla_nolink_text'], $markup_values ), 'post' );
			} else {
				return '';
			}
		}

		$show_all = $prev_next = false;

		if ( isset ( $output_parameters[1] ) ) {
				switch ( $output_parameters[1] ) {
				case 'show_all':
					$show_all = true;
					break;
				case 'prev_next':
					$prev_next = true;
			}
		}

		$mla_page_parameter = $arguments['mla_page_parameter'];
		$current_page = $markup_values['current_page'];
		$last_page = $markup_values['last_page'];
		$end_size = absint( $arguments['mla_end_size'] );
		$mid_size = absint( $arguments['mla_mid_size'] );
		$posts_per_page = $markup_values['posts_per_page'];

		$new_target = ( ! empty( $arguments['mla_target'] ) ) ? 'target="' . esc_attr( $arguments['mla_target'] ) . '" ' : '';

		// these will add to the default classes
		$new_class = ( ! empty( $arguments['mla_link_class'] ) ) ? ' ' . esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_link_class'], $markup_values ) ) : '';

		$new_attributes = ( ! empty( $arguments['mla_link_attributes'] ) ) ? MLAShortcode_Support::mla_esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_link_attributes'], $markup_values ) ) . ' ' : '';

		$new_base =  ( ! empty( $arguments['mla_link_href'] ) ) ? esc_url( self::mla_process_shortcode_parameter( $arguments['mla_link_href'], $markup_values ) ) : $markup_values['new_url'];

		// Build the array of page links
		$page_links = array();
		$dots = false;

		if ( $prev_next && $current_page && 1 < $current_page ) {
			$markup_values['new_page'] = $current_page - 1;
			$new_title = ( ! empty( $arguments['mla_rollover_text'] ) ) ? 'title="' . esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_rollover_text'], $markup_values ) ) . '" ' : '';
			$new_url = self::_replace_query_parameter( $mla_page_parameter, $current_page - 1, $new_base );
			$prev_text = ( ! empty( $arguments['mla_prev_text'] ) ) ? esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_prev_text'], $markup_values ) ) : '&laquo; ' . __( 'Previous', 'media-library-assistant' );
			$page_links[] = sprintf( '<a %1$sclass="prev page-numbers%2$s" %3$s%4$shref="%5$s">%6$s</a>',
				/* %1$s */ $new_target,
				/* %2$s */ $new_class,
				/* %3$s */ $new_attributes,
				/* %4$s */ $new_title,
				/* %5$s */ $new_url,
				/* %6$s */ $prev_text );
		}

		for ( $new_page = 1; $new_page <= $last_page; $new_page++ ) {
			$new_page_display = number_format_i18n( $new_page );
			$markup_values['new_page'] = $new_page;
			$new_title = ( ! empty( $arguments['mla_rollover_text'] ) ) ? 'title="' . esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_rollover_text'], $markup_values ) ) . '" ' : '';

			if ( $new_page == $current_page ) {
				// build current page span
				$page_links[] = sprintf( '<span class="page-numbers current%1$s">%2$s</span>',
					/* %1$s */ $new_class,
					/* %2$s */ $new_page_display );
				$dots = true;
			} else {
				if ( $show_all || ( $new_page <= $end_size || ( $current_page && $new_page >= $current_page - $mid_size && $new_page <= $current_page + $mid_size ) || $new_page > $last_page - $end_size ) ) {
					// build link
					$new_url = self::_replace_query_parameter( $mla_page_parameter, $new_page, $new_base );
					$page_links[] = sprintf( '<a %1$sclass="page-numbers%2$s" %3$s%4$shref="%5$s">%6$s</a>',
						/* %1$s */ $new_target,
						/* %2$s */ $new_class,
						/* %3$s */ $new_attributes,
						/* %4$s */ $new_title,
						/* %5$s */ $new_url,
						/* %6$s */ $new_page_display );
					$dots = true;
				} elseif ( $dots && ! $show_all ) {
					// build link
					$page_links[] = sprintf( '<span class="page-numbers dots%1$s">&hellip;</span>',
						/* %1$s */ $new_class );
					$dots = false;
				}
			} // ! current
		} // for $new_page

		if ( $prev_next && $current_page && ( $current_page < $last_page || -1 == $last_page ) ) {
			// build next link
			$markup_values['new_page'] = $current_page + 1;
			$new_title = ( ! empty( $arguments['mla_rollover_text'] ) ) ? 'title="' . esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_rollover_text'], $markup_values ) ) . '" ' : '';
			$new_url = self::_replace_query_parameter( $mla_page_parameter, $current_page + 1, $new_base );
			$next_text = ( ! empty( $arguments['mla_next_text'] ) ) ? esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_next_text'], $markup_values ) ) : __( 'Next', 'media-library-assistant' ) . ' &raquo;';
			$page_links[] = sprintf( '<a %1$sclass="next page-numbers%2$s" %3$s%4$shref="%5$s">%6$s</a>',
				/* %1$s */ $new_target,
				/* %2$s */ $new_class,
				/* %3$s */ $new_attributes,
				/* %4$s */ $new_title,
				/* %5$s */ $new_url,
				/* %6$s */ $next_text );
		}

		switch ( strtolower( trim( $arguments['mla_paginate_type'] ) ) ) {
			case 'list':
				$results = "<ul class='page-numbers'>\n\t<li>";
				$results .= join("</li>\n\t<li>", $page_links);
				$results .= "</li>\n</ul>\n";
				break;
			case 'plain':
			default:
				$results = join("\n", $page_links);
		} // mla_paginate_type

		return $output . $results;
	}

	/**
	 * Handles pagnation output types 'previous_page', 'next_page', and 'paginate_links'
	 *
	 * @since 2.20
	 *
	 * @param array	value(s) for mla_output_type parameter
	 * @param array template substitution values, e.g., ('instance' => '1', ...  )
	 * @param array merged default and passed shortcode parameter values
	 * @param array raw passed shortcode parameter values
	 * @param integer number of attachments in the gallery, without pagination
	 * @param string output text so far, may include debug values
	 *
	 * @return mixed	false or string with HTML for pagination output types
	 */
	public static function mla_process_pagination_output_types( $output_parameters, $markup_values, $arguments, $attr, $found_rows, $output = NULL ) {
//error_log( __LINE__ . ' mla_process_pagination_output_types output_parameters = ' . var_export( $output_parameters, true ), 0 );
//error_log( __LINE__ . ' mla_process_pagination_output_types markup_values = ' . var_export( $markup_values, true ), 0 );
//error_log( __LINE__ . ' mla_process_pagination_output_types arguments = ' . var_export( $arguments, true ), 0 );
//error_log( __LINE__ . ' mla_process_pagination_output_types attr = ' . var_export( $attr, true ), 0 );
		if ( ! in_array( $output_parameters[0], array( 'previous_page', 'next_page', 'paginate_links' ) ) ) {
			return false;
		}

		// Trick to detect [mla_gallery], only shortcode that supplies $output
		if ( NULL !== $output ) {
			// Add [mla_gallery] data selection parameters to gallery-specific and mla_gallery-specific parameters
			$arguments = array_merge( $arguments, shortcode_atts( self::$mla_get_shortcode_attachments_parameters, $attr ) );
			$output = '';
		}
		
		$posts_per_page = absint( $arguments['posts_per_page'] );
		$mla_page_parameter = $arguments['mla_page_parameter'];

		/*
		 * $mla_page_parameter, if set, doesn't make it through the shortcode_atts filter,
		 * so we handle it separately
		 */
		if ( ! isset( $arguments[ $mla_page_parameter ] ) ) {
			if ( isset( $attr[ $mla_page_parameter ] ) ) {
				$arguments[ $mla_page_parameter ] = $attr[ $mla_page_parameter ];
			} else {
				$arguments[ $mla_page_parameter ] = '';
			}
		}
//error_log( __LINE__ . ' mla_process_pagination_output_types arguments = ' . var_export( $arguments, true ), 0 );

		if ( 0 === $posts_per_page ) {
			$posts_per_page = absint( $arguments['numberposts'] );
		}

		if ( 0 === $posts_per_page ) {
			$posts_per_page = absint( get_option('posts_per_page') );
		}

		if ( 0 < $posts_per_page ) {
			$max_page = (integer) floor( $found_rows / $posts_per_page );
			if ( $max_page < ( $found_rows / $posts_per_page ) ) {
				$max_page++;
			}
		} else {
			$max_page = 1;
		}

		if ( isset( $arguments['mla_paginate_total'] )  && $max_page > absint( $arguments['mla_paginate_total'] ) ) {
			$max_page = absint( $arguments['mla_paginate_total'] );
		}

		if ( isset( $arguments[ $mla_page_parameter ] ) ) {
			$paged = absint( $arguments[ $mla_page_parameter ] );
		} else {
			$paged = absint( $arguments['paged'] );
		}

		if ( 0 == $paged ) {
			$paged = 1;
		}

		if ( $max_page < $paged ) {
			$paged = $max_page;
		}

		switch ( $output_parameters[0] ) {
			case 'previous_page':
				if ( 1 < $paged ) {
					$new_page = $paged - 1;
				} else {
					$new_page = 0;

					if ( isset ( $output_parameters[1] ) ) {
						switch ( $output_parameters[1] ) {
							case 'wrap':
								$new_page = $max_page;
								break;
							case 'first':
								$new_page = 1;
						}
					}
				}

				break;
			case 'next_page':
				if ( $paged < $max_page ) {
					$new_page = $paged + 1;
				} else {
					$new_page = 0;

					if ( isset ( $output_parameters[1] ) ) {
						switch ( $output_parameters[1] ) {
							case 'last':
								$new_page = $max_page;
								break;
							case 'wrap':
								$new_page = 1;
						}
					}
				}

				break;
			case 'paginate_links':
				$new_page = 0;
		}

		$markup_values['current_page'] = $paged;
		$markup_values['new_page'] = $new_page;
		$markup_values['last_page'] = $max_page;
		$markup_values['posts_per_page'] = $posts_per_page;
		$markup_values['found_rows'] = $found_rows;

		if ( $paged ) {
			$markup_values['current_offset'] = ( $paged - 1 ) * $posts_per_page;
		} else {
			$markup_values['current_offset'] = 0;
		}

		if ( $new_page ) {
			$markup_values['new_offset'] = ( $new_page - 1 ) * $posts_per_page;
		} else {
			$markup_values['new_offset'] = 0;
		}

		$markup_values['current_page_text'] = $mla_page_parameter . '="[+current_page+]"';
		$markup_values['new_page_text'] = $mla_page_parameter . '="[+new_page+]"';
		$markup_values['last_page_text'] = 'mla_paginate_total="[+last_page+]"';
		$markup_values['posts_per_page_text'] = 'posts_per_page="[+posts_per_page+]"';

		if ( 'HTTPS' == substr( $_SERVER["SERVER_PROTOCOL"], 0, 5 ) ) { // phpcs:ignore
			$markup_values['scheme'] = 'https://';
		} else {
			$markup_values['scheme'] = 'http://';
		}

		$markup_values['http_host'] = $_SERVER['HTTP_HOST']; // phpcs:ignore

		$parts = wp_parse_url( $_SERVER['REQUEST_URI'] ); // phpcs:ignore
		$uri_path = empty( $parts['path'] ) ? '' : $parts['path'];
		$uri_query = empty( $parts['query'] ) ? '' : $parts['query'];

		// Add or replace the current page parameter
		if ( 0 < $new_page ) {
			$uri_query = remove_query_arg( $mla_page_parameter, $uri_query );
			$uri_query = add_query_arg( array(  $mla_page_parameter  => $new_page ), $uri_query );	
		}

		if ( ( 0 < strlen( $uri_query ) ) && ( '?' !== $uri_query[0] ) ) {
			$uri_query = '?' . $uri_query;
		}

		// Validate the query arguments to prevent cross-site scripting (reflection) attacks
		$test_query = array();
		parse_str( strval( $uri_query ), $test_query );

		$clean_query = array();
		foreach ( $test_query as $test_key => $test_value ) {
			// Query argument names cannot have URL special characters
			if ( $test_key === urldecode( $test_key ) ) {
				$clean_query[ $test_key ] = $test_value;
			}
		}

		$clean_query = urlencode_deep( $clean_query );
		$clean_query = build_query( $clean_query );
		$markup_values['query_string'] = $clean_query;

		if ( !empty( $clean_query ) ) {
//			$markup_values['request_uri'] = $uri_path .  '?' . $clean_query;	
			$markup_values['request_uri'] = $uri_path . $clean_query;	
		} else {
			$markup_values['request_uri'] = $uri_path;
		}

		$markup_values['new_url'] = set_url_scheme( $markup_values['scheme'] . $markup_values['http_host'] . $markup_values['request_uri'] );
		$markup_values = apply_filters( 'mla_gallery_pagination_values', $markup_values );

		/*
		 * Expand pagination-specific Gallery Display Content parameters,
		 * which can contain request: and query: arguments.
		 */
		$pagination_arguments = array( 'mla_nolink_text', 'mla_link_class', 'mla_rollover_text', 'mla_link_attributes', 'mla_link_href', 'mla_link_text', 'mla_prev_text', 'mla_next_text' );

		$new_text = '';
		foreach( $pagination_arguments as $value ) {
			$new_text .= str_replace( '{+', '[+', str_replace( '+}', '+]', $arguments[ $value ] ) );
		}

		$markup_values = MLAData::mla_expand_field_level_parameters( $new_text, $attr, $markup_values );

		// Build the new link, applying Gallery Display Content parameters
		if ( 'paginate_links' == $output_parameters[0] ) {
			return self::_paginate_links( $output_parameters, $markup_values, $arguments, $found_rows, $output );
		}

		if ( 0 == $new_page ) {
			if ( ! empty( $arguments['mla_nolink_text'] ) ) {
				return wp_kses( self::mla_process_shortcode_parameter( $arguments['mla_nolink_text'] . 'page', $markup_values ), 'post' );
			} else {
				return '';
			}
		}

		$new_link = '<a ';

		if ( ! empty( $arguments['mla_target'] ) ) {
			$new_link .= 'target="' . esc_attr( $arguments['mla_target'] ) . '" ';
		}

		if ( ! empty( $arguments['mla_link_class'] ) ) {
			$new_link .= 'class="' . esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_link_class'], $markup_values ) ) . '" ';
		}

		if ( ! empty( $arguments['mla_rollover_text'] ) ) {
			$new_link .= 'title="' . esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_rollover_text'], $markup_values ) ) . '" ';
		}

		if ( ! empty( $arguments['mla_link_attributes'] ) ) {
			$new_link .= MLAShortcode_Support::mla_esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_link_attributes'], $markup_values ) ) . ' ';
		}

		if ( ! empty( $arguments['mla_link_href'] ) ) {
			$new_link .= 'href="' . esc_url( self::mla_process_shortcode_parameter( $arguments['mla_link_href'], $markup_values ) ) . '" >';
		} else {
			$new_link .= 'href="' . $markup_values['new_url'] . '" >';
		}

		if ( ! empty( $arguments['mla_link_text'] ) ) {
			$new_link .= wp_kses( self::mla_process_shortcode_parameter( $arguments['mla_link_text'], $markup_values ), 'post' ) . '</a>';
		} else {
			if ( 'previous_page' == $output_parameters[0] ) {
				if ( isset( $arguments['mla_prev_text'] ) ) {
					$new_text = esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_prev_text'], $markup_values ) );
				} else {
					$new_text = '&laquo; ' . __( 'Previous', 'media-library-assistant' );
				}
			} else {
				if ( isset( $arguments['mla_next_text'] ) ) {
					$new_text = esc_attr( self::mla_process_shortcode_parameter( $arguments['mla_next_text'], $markup_values ) );
				} else {
					$new_text = __( 'Next', 'media-library-assistant' ) . ' &raquo;';
				}
			}

			$new_link .= $new_text . '</a>';
		}

		return $new_link;
	}

	/**
	 * WP_Query filter "parameters"
	 *
	 * This array defines parameters for the query's join, where and orderby filters.
	 * The parameters are set up in the mla_get_shortcode_attachments function, and
	 * any further logic required to translate those values is contained in the filter.
	 *
	 * Array index values are: orderby, post_parent
	 *
	 * @since 2.20
	 *
	 * @var	array
	 */
	private static $query_parameters = array();

	/**
	 * Error details from _validate_array_specification()
	 *
	 * @since 2.94
	 *
	 * @var	string
	 */
	private static $array_specification_error = '';

	/**
	 * Checks for valid, perhaps nested PHP array specification
	 *
	 * @since 2.82
	 *
	 * @param string $specification query specification; PHP nested arrays
	 * @param array $interor_arrays Optional. Values for nested arrays, by reference.
	 *
	 * @return boolean true if specification is a valid PHP array else false
	 */
	private static function _validate_array_specification( $specification, &$interor_arrays = array() ) {
//error_log( __LINE__ . " _validate_array_specification() specification = " . var_export( $specification, true ), 0 );
//error_log( __LINE__ . " _validate_array_specification() interor_arrays = " . var_export( $interor_arrays, true ), 0 );
		self::$array_specification_error = '';

		// Check for outer array specification(s) and reject anything else.
		if ( 1 !== preg_match( '/^array\s*\((.*)\)[\s\,]*$/', $specification, $matches ) ) {
//error_log( __LINE__ . " _validate_array_specification() specification = " . var_export( $specification, true ), 0 );
			self::$array_specification_error = " FAILED outer array = " . var_export( $specification, true );
			return false;
		}

		$converted_array = array();
		
		$interior = trim( $matches[1], ', ' );
		while ( strlen( $interior ) ) {
//error_log( __LINE__ . " _validate_array_specification() converted_array = " . var_export( $converted_array, true ), 0 );
//error_log( __LINE__ . " _validate_array_specification() interior = " . var_export( $interior, true ), 0 );
			$interior_array = array();
			
			// Recursive matching required for nested and multiple arrays
			while ( preg_match_all( '/(?x)array\s*\( ( (?>[^()]+) | (?R) )* \)/', $interior, $matches ) ) {
//error_log( __LINE__ . " _validate_array_specification() recursion matches = " . var_export( $matches, true ), 0 );
				foreach ( $matches[0] as $search ) {
					// Replace valid arrays with a harmless literal value
					$interior_array = self::_validate_array_specification( $search, $interor_arrays );
					if ( false === $interior_array ) {
						self::$array_specification_error = " FAILED nested array = " . var_export( $search, true );
//error_log( __LINE__ . " _validate_array_specification() search = " . var_export( $search, true ), 0 );
						return false;
					}

					$interor_arrays[] = $interior_array;
					$interior = str_replace( $search, sprintf( 'ARRAY%1$03d', ( count( $interor_arrays ) - 1 ) ), $interior );
				}
//error_log( __LINE__ . " _validate_array_specification() recursion interior = " . var_export( $interior, true ), 0 );
//error_log( __LINE__ . " _validate_array_specification() recursion interor_arrays = " . var_export( $interor_arrays, true ), 0 );
			}

			// Look for a nested array
			if ( 1 === preg_match( '/^(array\s*\(.*\))(.*)$/', $interior, $matches ) ) {
//error_log( __LINE__ . " _validate_array_specification() nested matches = " . var_export( $matches, true ), 0 );
				$interior_array = self::_validate_array_specification( $matches[1], $interor_arrays );
				if ( false === $interior_array ) {
					return false;
				}

				$converted_array[] = $interior_array;
				$interior = trim( $matches[2], ' ,' );
				continue;
			}

			// PHP "undefined constant" pattern: [a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*
			/* Look for 'key' => value
			 *  0 Entire interior
			 *  1 key and assignment literal, e.g., '\'key\' => '
			 *  2 quoted string | integer | undefined constant
			 *  3 string key with enclosing quotes
			 *  4 string key without quotes
			 *  5 integer key
			 *  6 undefined constant key
			 *  7 quoted string | integer | ARRAY999 placeholder | 4- or 5-letter word 
			 *  8 string value with quotes
			 *  9 string value without quotes
			 * 10 integer value
			 * 11 ARRAY999 placeholder
			 * 12 tail portion following ARRAY999 placeholder
			 * 13 4 or 5 letter word, e.g. true or false
			 * 14 tail portion following string, integer or word/boolean value
			 */
			if ( 1 === preg_match( '/^((([\'\"](.+?)[\'\"])|(\d+)|([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*))\s*\=\>\s*)(([\'\"](.*?)[\'\"])|(\d+)|(ARRAY...)(.*)|(\w{4,5}))(.*)$/', $interior, $matches /*, PREG_OFFSET_CAPTURE */ ) ) {
//error_log( __LINE__ . " _validate_array_specification() key => value matches = " . var_export( $matches, true ), 0 );

				// Validate boolean values
				if ( ! empty( $matches[13] ) ) {
//error_log( __LINE__ . " _validate_array_specification() boolean and array() matches = " . var_export( $matches[10], true ), 0 );
//error_log( __LINE__ . " _validate_array_specification() boolean and array() interior_array = " . var_export( $interior_array, true ), 0 );
					if ( false === in_array( strtolower( $matches[13] ), array( 'false', 'true' ) ) ) {
//error_log( __LINE__ . " _validate_array_specification() FAILED boolean matches = " . var_export( $matches[7], true ), 0 );
						self::$array_specification_error = " FAILED boolean matches = " . var_export( $matches[7], true );
						return false;
					}
				}

				if ( ! empty( $matches[5] ) ) {
					$key = (integer) $matches[5];
				} else {
					$key = trim( $matches[2], '"\'' );
				}

				if ( 8 === strlen( $matches[11] ) ) {
					$simple_index = substr( $matches[11], 5, 3 );
					if ( 'XXX' !== $simple_index ) {
						$converted_array[ $key ] = $interor_arrays[ (integer) $simple_index ];
					}

					$interior = trim( $matches[12], ' ,' );
				} else {
					if ( ! empty( $matches[10] ) ) {
						$converted_array[ $key ] = (integer) $matches[10];
					} elseif ( ! empty( $matches[13] ) ) {
						$converted_array[ $key ] = ( 'true' === strtolower( $matches[13] ) );
					} else {
						$converted_array[ $key ] = trim( $matches[7], '"\'' );
					}
					
					$interior = trim( $matches[14], ' ,' );
				}
				
				continue;
			}

			/*
			 * Look for already-validated array match, simple quoted string, integer value or "undefined constant", e.g., in 'terms' =>
			 *     0 Entire interior
			 *     1 string | integer | constant
			 *     2 string with quotes
			 *     3 string trimmed of quotes
			 *     4 integer
			 *     5 undefined constant or ARRAY999 placeholder
			 *     6 tail portion of interior
			 */
			if ( 1 === preg_match( '/^(([\'\"](.+?)[\'\"])|(-{0,1}\d+)|([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*))(.*)$/', $interior, $matches ) ) {
//error_log( __LINE__ . " _validate_array_specification() simple matches = " . var_export( $matches, true ), 0 );
				if ( 0 === strpos( $interior, 'ARRAY' ) ) {
					$simple_index = substr( $interior, 5, 3 );
					if ( 'XXX' !== $simple_index ) {
						$converted_array[] = $interor_arrays[ (integer) $simple_index ];
					}
				} else {
					if ( !empty( $matches[4] ) ) {
						$converted_array[] = (integer) $matches[4];
					} else {
						$converted_array[] = trim( $matches[1], '"\'' );
					}
				}
				
				$interior = trim( $matches[6], ' ,' );
				continue;
			}

//error_log( __LINE__ . " _validate_array_specification() FAILED interior = " . var_export( $interior, true ), 0 );
			self::$array_specification_error = " FAILED interior = " . var_export( $interior, true );
			return false;
		}
//error_log( __LINE__ . " _validate_array_specification() GOOD interior = " . var_export( $interior, true ), 0 );

//error_log( __LINE__ . " _validate_array_specification() GOOD converted_array = " . var_export( $converted_array, true ), 0 );
		return $converted_array;
	}

	/**
	 * Cleans up damage caused by the Visual Editor to the tax_query and meta_query specifications,
	 * then checks for valid PHP array specification to avoid remote code execution attacks.
	 *
	 * @since 2.20
	 *
	 * @param string query specification; PHP nested arrays
	 *
	 * @return string query specification with HTML escape sequences and line breaks removed
	 */
	private static function _sanitize_query_specification( $specification ) {
//error_log( __LINE__ . " _sanitize_query_specification() specification = " . var_export( $specification, true ), 0 );
		// Clean up the text
		$candidate = wp_specialchars_decode( $specification );

		$candidate = str_replace( array( '&#038;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8242;', '&#8243;', '&amp;', '<br />', '<br>', '<p>', '</p>', "\r", "\n", "\t" ),
		                          array( '&',      '\'',      '\'',      '"',       '"',       '\'',      '"',       '&',     ' ',      ' ',    ' ',   ' ',    ' ',  ' ',  ' ' ), $candidate );

		$candidate = trim( $candidate, ' ,"\`' );
//error_log( __LINE__ . " _sanitize_query_specification() candidate = " . var_export( $candidate, true ), 0 );

		// Check for nested array specification(s) and reject anything else.
		$result = self::_validate_array_specification( $candidate );
		if ( true === $result ) {
			return $candidate;
		}

//error_log( __LINE__ . " _sanitize_query_specification() FAILED array_specification_error = " . var_export( self::$array_specification_error, true ), 0 );
		return 'false';
	}

	/**
	 * Translates query parameters to a valid SQL order by clause.
	 *
	 * Accepts one or more valid columns, with or without ASC/DESC.
	 * Enhanced version of /wp-includes/formatting.php function sanitize_sql_orderby().
	 *
	 * @since 2.20
	 *
	 * @param array Validated query parameters; 'order', 'orderby', 'meta_key', 'post__in'.
	 * @param string Optional. Database table prefix; can be empty. Default taken from $wpdb->posts.
	 * @param array Optional. Field names (keys) and database column equivalents (values). Defaults from [mla_gallery].
	 * @param array Optional. Field names (values) that require a BINARY prefix to preserve case order. Default array()
	 * @return string|bool Returns the orderby clause if present, false otherwise.
	 */
	public static function mla_validate_sql_orderby( $query_parameters, $table_prefix = NULL, $allowed_keys = NULL, $binary_keys = array() ){
		global $wpdb;

		$results = array ();
		$order = isset( $query_parameters['order'] ) ? ' ' . trim( strtoupper( $query_parameters['order'] ) ) : '';
		$orderby = isset( $query_parameters['orderby'] ) ? $query_parameters['orderby'] : '';
		$meta_key = isset( $query_parameters['meta_key'] ) ? $query_parameters['meta_key'] : '';

		if ( is_null( $table_prefix ) ) {
			$table_prefix = $wpdb->posts . '.';
		}

		if ( is_null( $allowed_keys ) ) {
			$allowed_keys = array(
				'empty_orderby_default' => 'post_date',
				'explicit_orderby_field' => 'post__in',
				'explicit_orderby_column' => 'ID',
				'id' => 'ID',
				'author' => 'post_author',
				'date' => 'post_date',
				'description' => 'post_content',
				'content' => 'post_content',
				'title' => 'post_title',
				'caption' => 'post_excerpt',
				'excerpt' => 'post_excerpt',
				'slug' => 'post_name',
				'name' => 'post_name',
				'modified' => 'post_modified',
				'parent' => 'post_parent',
				'menu_order' => 'menu_order',
				'mime_type' => 'post_mime_type',
				'comment_count' => 'post_content',
				'rand' => 'RAND()',
			);
		}

		if ( empty( $orderby ) ) {
			if ( ! empty( $allowed_keys['empty_orderby_default'] ) ) {
				return $table_prefix . $allowed_keys['empty_orderby_default'] . " {$order}";
			} else {
				return "{$table_prefix}post_date {$order}";
			}
		} elseif ( 'none' == $orderby ) {
			return '';
		} elseif ( ! empty( $allowed_keys['explicit_orderby_field'] ) ) {
			$explicit_field = $allowed_keys['explicit_orderby_field'];
			if ( $orderby == $explicit_field ) {
				if ( ! empty( $query_parameters[ $explicit_field ] ) ) {
					$explicit_order = implode(',', array_map( 'absint', $query_parameters[ $explicit_field ] ) );

					if ( ! empty( $explicit_order ) ) {
						$explicit_column = $allowed_keys['explicit_orderby_column'];
						return "FIELD( {$table_prefix}{$explicit_column}, {$explicit_order} )";
					} else {
						return '';
					}
				}
			}
		}

		if ( ! empty( $meta_key ) ) {
			$allowed_keys[ $meta_key ] = "$wpdb->postmeta.meta_value";
			$allowed_keys['meta_value'] = "$wpdb->postmeta.meta_value";
			$allowed_keys['meta_value_num'] = "$wpdb->postmeta.meta_value+0";
		}

		$obmatches = preg_split('/\s*,\s*/', trim($query_parameters['orderby']));
		foreach ( $obmatches as $index => $value ) {
			$count = preg_match('/([a-z0-9_]+)(\s+(ASC|DESC))?/i', $value, $matches);
			if ( $count && ( $value == $matches[0] ) ) {
				$matches[1] = strtolower( $matches[1] );
				if ( isset( $matches[2] ) ) {
					$matches[2] = strtoupper( $matches[2] );
				}

				if ( array_key_exists( $matches[1], $allowed_keys ) ) {
					if ( ( 'rand' == $matches[1] ) || ( 'random' == $matches[1] ) ){
							$results[] = 'RAND()';
					} else {
						switch ( $matches[1] ) {
							case $meta_key:
							case 'meta_value':
								$matches[1] = "$wpdb->postmeta.meta_value";
								break;
							case 'meta_value_num':
								$matches[1] = "$wpdb->postmeta.meta_value+0";
								break;
							default:
								if ( in_array( $matches[1], $binary_keys ) ) {
									$matches[1] = 'BINARY ' . $table_prefix . $allowed_keys[ $matches[1] ];
								} else {
									$matches[1] = $table_prefix . $allowed_keys[ $matches[1] ];
								}
						} // switch $matches[1]

						$results[] = isset( $matches[2] ) ? $matches[1] . $matches[2] : $matches[1] . $order;
					} // not 'rand'
				} // allowed key
			} // valid column specification
		} // foreach $obmatches

		$orderby = implode( ', ', $results );
		if ( empty( $orderby ) ) {
			return false;
		}

		return $orderby;
	}

	/**
	 * Data selection parameters for the WP_Query in [mla_gallery]
	 *
	 * @since 2.20
	 *
	 * @var	array
	 */
	private static $mla_get_shortcode_attachments_parameters = array(
			'order' => 'ASC', // or 'DESC' or 'RAND'
			'orderby' => 'menu_order,ID',
			'id' => NULL,
			'ids' => array(),
			'include' => array(),
			'exclude' => array(),
			// MLA extensions, from WP_Query
			// Force 'get_children' style query
			'post_parent' => NULL, // post/page ID, 'none', 'any', 'current' or 'all'
			// Author
			'author' => NULL,
			'author_name' => '',
			// Category
			'cat' => 0,
			'category_name' => '',
			'category__and' => array(),
			'category__in' => array(),
			'category__not_in' => array(),
			// Tag
			'tag' => '',
			'tag_id' => 0,
			'tag__and' => array(),
			'tag__in' => array(),
			'tag__not_in' => array(),
			'tag_slug__and' => array(),
			'tag_slug__in' => array(),
			// Taxonomy parameters are handled separately
			// {tax_slug} => 'term' | array ( 'term', 'term', ... )
			// 'tax_query' => ''
			// 'tax_input' => ''
			// 'tax_relation' => 'OR', 'AND' (default),
			// 'tax_operator' => 'OR' (default), 'IN', 'NOT IN', 'AND',
			// 'tax_include_children' => true (default), false
			// Post 
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'post_mime_type' => 'image',
			// Pagination - no default for most of these
			'nopaging' => true,
			'numberposts' => 0,
			'posts_per_page' => 0,
			'posts_per_archive_page' => 0,
			'paged' => NULL, // page number or 'current'
			'offset' => NULL,
			'mla_page_parameter' => 'mla_paginate_current',
			'mla_paginate_current' => NULL,
			'mla_paginate_total' => NULL,
			// Date and Time Queries
			'year' => '',
			'monthnum' => '',
			'w' => '',
			'day' => '',
			'm' => '',
			'date_query' => '',
			// Custom Field
			'meta_key' => '',
			'meta_value' => '',
			'meta_value_num' => NULL,
			'meta_compare' => '',
			'meta_query' => '',
			// Terms Search
			'mla_terms_phrases' => '',
			'mla_terms_taxonomies' => '',
			'mla_phrase_delimiter' => '',
			'mla_phrase_connector' => '',
			'mla_term_delimiter' => '',
			'mla_term_connector' => '',
			// Search
			's' => '',
			'mla_search_fields' => '',
			'mla_search_connector' => '',
			'whole_word' => '',
			'sentence' => '',
			'exact' => '',
			// Returned fields, for support topic "Adding 'fields' to function mla_get_shortcode_attachments" by leoloso
			'fields' => '',
			// Caching parameters, for support topic "Lag in attachment categories" by Ruriko
			'cache_results' => NULL,
			'update_post_meta_cache' => NULL,
			'update_post_term_cache' => NULL,
			// WordPress Real Media Library plugin support
			'mla_allow_rml' => false,
			'mla_rml_folder' => NULL,
			'mla_rml_include_children' => false,
			// WordPress CatFolders plugin support
			'mla_allow_catf' => true,
			'mla_catf_folder' => NULL,
		);

	/**
	 * Data selection parameters for the WP_Query in [mla_gallery]
	 *
	 * @since 2.40
	 *
	 * @var	array
	 */
	private static $mla_get_shortcode_dynamic_attachments_parameters = array(
			// Taxonomy parameters are handled separately
			// {tax_slug} => 'term' | array ( 'term', 'term', ... )
			// 'tax_query' => ''
			// 'tax_relation' => 'OR', 'AND' (default),
			// 'tax_operator' => 'OR' (default), 'IN', 'NOT IN', 'AND',
			// 'tax_include_children' => true (default), false
		);

	/**
	 * For Paid Memberships Pro plugin, Hide the 'attachment' post type
	 * from searches and archives if membership is required to access.
	 *
	 * @since 2.84
	 *
	 * @param array Post types to be screened for membership
	 */

	public static function mla_pmp_hide_attachments_filter( $post_types ) {
		$post_types[] = 'attachment';
		return $post_types;
	}

	/**
	 * Convert a taxonomy, date or meta query parameter to an array
	 *
	 * @since 2.99
	 *
	 * @param string $query_type 'tax_query', 'date_query' or 'meta_query'.
	 * @param mixed $query_string Array specification in text or array format, e.g., array of arrays.
	 * @param array $where_used_alternative Harmless substitute for invalid "where-used" queries.
	 *
	 * @return mixed An array on success, error message string on failure
	 */
	private static function _convert_query_parameter( $query_type, $query_string, $where_used_alternative ) {
//error_log( __LINE__ . " _convert_query_parameter( {$query_type} ) query_string = " . var_export( $query_string, true ), 0 );
		if ( is_array( $query_string ) ) {
			return $query_string;
		}

		// Clean up damage caused by the Visual Editor 
		$candidate = wp_specialchars_decode( $query_string );

		$candidate = str_replace( array( '&#038;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8242;', '&#8243;', '&amp;', '<br />', '<br>', '<p>', '</p>', "\r", "\n", "\t" ),
		                          array( '&',      '\'',      '\'',      '"',       '"',       '\'',      '"',       '&',     ' ',      ' ',    ' ',   ' ',    ' ',  ' ',  ' ' ), $candidate );

		$candidate = trim( $candidate, ' ,"\`' );
//error_log( __LINE__ . " _convert_query_parameter( {$query_type} ) candidate = " . var_export( $candidate, true ), 0 );

		// Unexpanded substitution parameters are not allowed
		if ( false !== strpos( $candidate, '{+' ) ) {
			$converted_result = false;
			self::$array_specification_error = 'FAILED substitution parameter in ' . $candidate;
		} else {
			// Check for nested array specification(s) and reject anything else.
			$converted_result = self::_validate_array_specification( $candidate );
		}
//error_log( __LINE__ . " _convert_query_parameter( {$query_type} ) converted_result = " . var_export( $converted_result, true ), 0 );

		if ( false === $converted_result ) {
			// Replace invalid queries from "where-used" callers with a harmless equivalent
			if ( !empty( $where_used_alternative ) ) {
				return $where_used_alternative;
			}

			return '<p>' . __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'Invalid mla_gallery', 'media-library-assistant' ) . " {$query_type} = " . self::$array_specification_error . '</p>';
		}
		
/* * /
		try {
//error_log( __LINE__ . " _convert_query_parameter( {$query_type} ) candidate = " . var_export( $candidate, true ), 0 );
			$result = @eval( 'return ' . $candidate . ';' );
//error_log( __LINE__ . " _convert_query_parameter( {$query_type} ) result = " . var_export( $result, true ), 0 );
		} catch ( Throwable $e ) { // PHP 7+
			$result = NULL;
		} catch ( Exception $e ) { // PHP 5
			$result = NULL;
		} // */
//error_log( __LINE__ . " _convert_query_parameter( {$query_type} ) result = " . var_export( $result, true ), 0 );

//		if ( is_array( $result ) ) {
		if ( is_array( $converted_result ) ) {
/* * /
			if ( $converted_result != $result ) {
//error_log( __LINE__ . " _convert_query_parameter( {$query_type} ) loose failure converted_result = " . var_export( $converted_result, true ), 0 );
//error_log( __LINE__ . " _convert_query_parameter( {$query_type} ) loose failure eval result = " . var_export( $result, true ), 0 );
			}

			if ( $converted_result !== $result ) {
//error_log( __LINE__ . " _convert_query_parameter( {$query_type} ) strict failure converted_result = " . var_export( $converted_result, true ), 0 );
//error_log( __LINE__ . " _convert_query_parameter( {$query_type} ) strict failure eval result = " . var_export( $result, true ), 0 );
			} // */

//			return $result;
			return $converted_result;
		}
		
		return '<p>' . __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'Invalid mla_gallery', 'media-library-assistant' ) . " {$query_type} = " . var_export( $candidate, true ) . '</p>';
	}

	/**
	 * Parses shortcode parameters and returns the gallery objects
	 *
	 * @since 2.20
	 *
	 * @param int ID of the post/page in which the shortcode appears; zero (0) if none
	 * @param array Attributes of the shortcode
	 * @param boolean Optional; true to calculate and return ['found_rows', 'max_num_pages'] as array elements
	 * @param boolean Optional; true activate debug logging, false to suppress it.
	 *
	 * @return array List of attachments returned from WP_Query
	 */
	public static function mla_get_shortcode_attachments( $post_parent, $attr, $return_found_rows = NULL, $overide_debug = NULL ) {
		global $wp_query;

		// Set the local debug mode
		$old_debug_mode = self::$mla_debug;
		if ( NULL !== $overide_debug ) {
			self::$mla_debug = ( true === $overide_debug );
		}

		// Parameters passed to the join, where and orderby filter functions
		self::$query_parameters = array( MLAQuery::MLA_ALT_TEXT_SUBQUERY => false, MLAQuery::MLA_FILE_SUBQUERY => false, );

		// Parameters passed to the posts_search filter function in MLAData
		MLAQuery::$search_parameters = array( 'debug' => 'none' );

		// Make sure $attr is an array, even if it's empty
		if ( empty( $attr ) ) {
			$attr = array();
		} elseif ( is_string( $attr ) ) {
			$attr = shortcode_parse_atts( $attr );
		}

		/*
		 * The "where used" queries have no $_REQUEST context available to them,
		 * so tax_, date_ and meta_query evaluation will fail if they contain "{+request:"
		 * parameters. Ignore these errors.
		 */
		if ( isset( $attr['where_used_query'] ) && ( 'this-is-a-where-used-query' == $attr['where_used_query'] ) ) {
			$where_used_query = true;
			unset( $attr['where_used_query'] );

			// remove pagination parameters to get a complete result
			$attr['nopaging'] = true;
			unset( $attr['numberposts'] );
			unset( $attr['posts_per_page'] );
			unset( $attr['posts_per_archive_page'] );
			unset( $attr['paged'] );
			unset( $attr['offset'] );
			unset( $attr['mla_paginate_current'] );
			unset( $attr['mla_page_parameter'] );
			unset( $attr['mla_paginate_total'] );

			// There's no point in sorting the items
			$attr['orderby'] = 'none';
		} else {
			$where_used_query = false;
		}

		/*
		 * Merge input arguments with defaults, then extract the query arguments.
		 *
		 * $return_found_rows is used to indicate that the call comes from gallery_shortcode(),
		 * which is the only call that supplies it.
		 */
		if ( ! is_null( $return_found_rows ) ) {
			$attr = apply_filters( 'mla_gallery_query_attributes', $attr );
		}
		$arguments = shortcode_atts( self::$mla_get_shortcode_attachments_parameters, $attr );
		$mla_page_parameter = $arguments['mla_page_parameter'];
		unset( $arguments['mla_page_parameter'] );

		// Convert to boolean, detect mla_rml_folder
		$arguments['mla_allow_rml'] = 'true' === ( ( ! empty( $arguments['mla_allow_rml'] ) ) ? trim( strtolower( $arguments['mla_allow_rml'] ) ) : 'false' );
		$arguments['mla_rml_include_children'] = 'true' === ( ( ! empty( $arguments['mla_rml_include_children'] ) ) ? trim( strtolower( $arguments['mla_rml_include_children'] ) ) : 'false' );
		if ( ! empty( $arguments['mla_rml_folder'] ) ) {
			$arguments['mla_rml_folder'] = absint( $arguments['mla_rml_folder'] );
			$arguments['mla_allow_rml'] = 0 < $arguments['mla_rml_folder'];
		}

		$arguments['mla_allow_catf'] = 'true' === ( ( ! empty( $arguments['mla_allow_catf'] ) ) ? trim( strtolower( $arguments['mla_allow_catf'] ) ) : 'true' );
		if ( ! empty( $arguments['mla_catf_folder'] ) ) {
			$arguments['mla_catf_folder'] = intval( $arguments['mla_catf_folder'] );
			$arguments['mla_allow_catf'] = 0 < $arguments['mla_catf_folder'];
		}

		/*
		 * $mla_page_parameter, if set, doesn't make it through the shortcode_atts filter,
		 * so we handle it separately
		 */
		if ( ! isset( $arguments[ $mla_page_parameter ] ) ) {
			if ( isset( $attr[ $mla_page_parameter ] ) ) {
				$arguments[ $mla_page_parameter ] = $attr[ $mla_page_parameter ];
			} else {
				$arguments[ $mla_page_parameter ] = NULL;
			}
		}

		if ( ! empty( $arguments['ids'] ) ) {
			// 'ids' is explicitly ordered, unless you specify otherwise.
			if ( empty( $attr['orderby'] ) ) {
				$arguments['orderby'] = 'post__in';
			}

			$arguments['include'] = $arguments['ids'];
		}
		unset( $arguments['ids'] );

		if ( ! is_null( $return_found_rows ) ) {
			$arguments = apply_filters( 'mla_gallery_query_arguments', $arguments );
		}

		// Extract taxonomy arguments
		self::$mla_get_shortcode_dynamic_attachments_parameters = array();
		$query_arguments = array();
		$terms_assigned_query = false;
		if ( ! empty( $attr ) ) {
			$all_taxonomies = get_taxonomies( array ( 'show_ui' => true ), 'names' );
			$simple_tax_queries = array();
			foreach ( $attr as $key => $value ) {
				if ( 'tax_query' === $key ) {
					// An empty query should be ignored, as is an empty "simple tax query"
					if ( empty( $value ) ) {
						continue;
					}

					if ( is_array( $value ) ) {
						$query_arguments[ $key ] = $value;
						self::$mla_get_shortcode_dynamic_attachments_parameters[ $key ] = $value;
					} else {
						$tax_query = self::_convert_query_parameter( 'tax_query', $value, array( array( 'taxonomy' => 'none', 'field' => 'slug', 'terms' => 'none' ) ) );

						if ( is_array( $tax_query ) ) {
							// Check for ignore.terms.assigned/-3, no.terms.assigned/-1 or any.terms.assigned/-2
							foreach ( $tax_query as $tax_query_key => $tax_query_element ) {
								if ( !is_array( $tax_query_element ) ) {
									continue;
								}

								if ( isset( $tax_query_element['taxonomy'] ) ) {
									$tax_query_taxonomy = $tax_query_element['taxonomy'];
								} else {
									continue;
								}

								if ( isset( $tax_query_element['terms'] ) ) {
									$terms = $tax_query_element['terms'];

									if ( empty( $terms ) || ( $terms === '-3' ) || ( is_array( $terms ) && in_array( '-3', $terms ) ) ) {
										$terms = 'ignore.terms.assigned';
									}

									if ( ( $terms === 'ignore.terms.assigned' ) || ( is_array( $terms ) && in_array( 'ignore.terms.assigned', $terms ) ) ) {
										unset( $tax_query[ $tax_query_key ] );
										continue;
									}

									if ( ( $terms === '-1' ) || ( is_array( $terms ) && in_array( '-1', $terms ) ) ) {
										$terms = 'no.terms.assigned';
									}

									if ( ( $terms === 'no.terms.assigned' ) || ( is_array( $terms ) && in_array( 'no.terms.assigned', $terms ) ) ) {
										$tax_query[ $tax_query_key ] = array(
											'taxonomy' => $tax_query_taxonomy,
											'operator' => 'NOT EXISTS',
										);

										continue;
									}

									if ( ( $terms === '-2' ) || ( is_array( $terms ) && in_array( '-2', $terms ) ) ) {
										$terms = 'any.terms.assigned';
									}

									if ( ( $terms === 'any.terms.assigned' ) || ( is_array( $terms ) && in_array( 'any.terms.assigned', $terms ) ) ) {
										$tax_query[ $tax_query_key ] = array(
											'taxonomy' => $tax_query_taxonomy,
											'operator' => 'EXISTS',
										);

										continue;
									}
								} // isset( terms )
							} // is_array( $tax_query )

							$query_arguments[ $key ] = $tax_query;
							self::$mla_get_shortcode_dynamic_attachments_parameters[ $key ] = $value;
							break; // Done - the tax_query overrides all other taxonomy parameters
						} else {
							self::$mla_debug = $old_debug_mode;
							return '<p>' . __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'Invalid mla_gallery', 'media-library-assistant' ) . ' tax_query = ' . var_export( $value, true ) . '</p>';
						} // generated value is not an array
					} // $tax_query is a string, not array
				}  // attr is 'tax_query'
				elseif ( 'tax_input' == $key ) {
					if ( is_array( $value ) ) {
						$tax_queries = $value;
					} else {
						$tax_queries = array();
						$compound_values = array_filter( array_map( 'trim', explode( ',', $value ) ) );
						foreach ( $compound_values as $compound_value ) {
							$value = explode( '.', $compound_value );
							if ( 2 === count( $value ) ) {
								if ( array_key_exists( $value[0], $all_taxonomies ) ) {
									$tax_queries[ $value[0] ][] = $value[1];
								} // valid taxonomy
							} // valid coumpound value
						} // foreach compound_value
					} // string value

					foreach( $tax_queries as $key => $value ) {
						if ( is_string( $value ) ) {
							$value = explode( ',', $value );
						}

						$simple_tax_queries[ $key ] = implode(',', array_filter( array_map( 'trim', $value ) ) );
						if ( in_array( $simple_tax_queries[ $key ], array( 'ignore.terms.assigned', '-3', 'no.terms.assigned', '-1', 'any.terms.assigned', '-2' ) ) ) {
							$terms_assigned_query = true;
						}
					}
				} // tax_input
				elseif ( array_key_exists( $key, $all_taxonomies ) ) {
					if ( is_string( $value ) ) {
						$value = explode( ',', $value );
					}

					$simple_tax_queries[ $key ] = implode(',', array_filter( array_map( 'trim', $value ) ) );
					if ( in_array( $simple_tax_queries[ $key ], array( 'ignore.terms.assigned', '-3', 'no.terms.assigned', '-1', 'any.terms.assigned', '-2' ) ) ) {
						$terms_assigned_query = true;
					}
				} // array_key_exists
			} //foreach $attr

			/*
			 * One of five outcomes:
			 * 1) An explicit tax_query was found; use it and ignore all other taxonomy parameters
			 * 2) No tax query is present; no further processing required
			 * 3) Two or more simple tax queries are present; compose a tax_query
			 * 4) One simple tax query and (tax_operator or tax_include_children) are present; compose a tax_query
			 * 5) One simple tax query is present; use it as-is or convert 'category' to 'category_name'
			 */
			if ( isset( $query_arguments['tax_query'] ) || empty( $simple_tax_queries ) ) {
				// No further action required
			} elseif ( ( 1 < count( $simple_tax_queries ) ) || isset( $attr['tax_operator'] ) || isset( $attr['tax_include_children'] ) || $terms_assigned_query ) {
				// Build a tax_query
				if  ( 1 < count( $simple_tax_queries ) ) {
					$tax_relation = 'AND';
					if ( isset( $attr['tax_relation'] ) ) {
						if ( 'OR' == strtoupper( $attr['tax_relation'] ) ) {
							$tax_relation = 'OR';
						}
					}
					$tax_query = array ('relation' => $tax_relation );
				} else {
					$tax_query = array ();
				}

				// Validate other tax_query parameters or set defaults
				$tax_operator = 'IN';
				if ( isset( $attr['tax_operator'] ) ) {
					$attr_value = strtoupper( $attr['tax_operator'] );
					if ( in_array( $attr_value, array( 'IN', 'NOT IN', 'AND' ) ) ) {
						$tax_operator = $attr_value;
					}
				}

				$tax_include_children = true;
				if ( isset( $attr['tax_include_children'] ) ) {
					if ( 'false' == strtolower( $attr['tax_include_children'] ) ) {
						$tax_include_children = false;
					}
				}

				foreach( $simple_tax_queries as $key => $value ) {
					// simple queries with these values are ignored
					if ( empty( $value ) || ( 'ignore.terms.assigned' === $value ) || ( '-3' === $value ) ) {
						continue;
					}

					if ( ( 'no.terms.assigned' === $value ) || ( '-1' === $value ) ) {
						$tax_query[] = array(
							'taxonomy' => $key,
							'operator' => 'NOT EXISTS' 
						);

						continue;
					}

					if ( ( 'any.terms.assigned' === $value ) || ( '-2' === $value ) ) {
						$tax_query[] = array(
							'taxonomy' => $key,
							'operator' => 'EXISTS' 
						);

						continue;
					}

					$tax_query[] =	array( 'taxonomy' => $key, 'field' => 'slug', 'terms' => explode( ',', $value ), 'operator' => $tax_operator, 'include_children' => $tax_include_children );
				} // foreach simple_tax_queries

				$query_arguments['tax_query'] = $tax_query;
				self::$mla_get_shortcode_dynamic_attachments_parameters['tax_query'] = $tax_query;
			} else {
				// exactly one simple query is present
				if ( isset( $simple_tax_queries['category'] ) ) {
					$arguments['category_name'] = $simple_tax_queries['category'];
				} else {
					$query_arguments = $simple_tax_queries;
				}

				self::$mla_get_shortcode_dynamic_attachments_parameters = $simple_tax_queries;
			}
		} // ! empty

		// Finish building the dynamic parameters
		if ( isset( $attr['tax_relation'] ) ) {
			self::$mla_get_shortcode_dynamic_attachments_parameters['tax_relation'] = $attr['tax_relation'];
		}

		if ( isset( $attr['tax_operator'] ) ) {
			self::$mla_get_shortcode_dynamic_attachments_parameters['tax_operator'] = $attr['tax_operator'];
		}

		if ( isset( $attr['tax_include_children'] ) ) {
			self::$mla_get_shortcode_dynamic_attachments_parameters['tax_include_children'] = $attr['tax_include_children'];
		}

		// Convert lists to arrays, if they have more than one element
		if ( isset( $arguments['post_type'] ) && is_string( $arguments['post_type'] ) ) {
			$value = explode( ',', $arguments['post_type'] );
			if ( 1 < count( $value ) ) {
				$arguments['post_type'] = $value;
			}
		}

		if ( isset( $arguments['post_status'] ) && is_string( $arguments['post_status'] ) ) {
			$value = explode( ',', $arguments['post_status'] );
			if ( 1 < count( $value ) ) {
				$arguments['post_status'] = $value;
			}
		}

		// $query_arguments has been initialized in the taxonomy code above.
		$is_tax_query = ! ($use_children = empty( $query_arguments ));
		foreach ($arguments as $key => $value ) {
			/*
			 * There are several "fallthru" cases in this switch statement that decide 
			 * whether or not to limit the query to children of a specific post.
			 */
			$children_ok = true;
			switch ( $key ) {
			case 'post_parent':
				if ( NULL !== $value ) {
					switch ( strtolower( $value ) ) {
					case 'all':
						$value = NULL;
						$use_children = false;
						break;
					case 'any':
						self::$query_parameters['post_parent'] = 'any';
						$value = NULL;
						$use_children = false;
						break;
					case 'current':
						$value = $post_parent;
						$use_children = true;
						break;
					case 'none':
						self::$query_parameters['post_parent'] = 'none';
						$value = NULL;
						$use_children = false;
						break;
					default:
						if ( false !== strpos( $value, ',' ) ) {
							self::$query_parameters['post_parent'] = array_filter( array_map( 'absint', explode( ',', $value ) ) );
							$value = NULL;
							$use_children = false;
						}
					}
				}
				// fallthru
			case 'id':
				if ( is_numeric( $value ) ) {
					$query_arguments[ $key ] = (int) $value;
					if ( ! $children_ok ) {
						$use_children = false;
					}
				}
				unset( $arguments[ $key ] );
				break;
			case 'numberposts':
			case 'posts_per_page':
			case 'posts_per_archive_page':
				if ( is_numeric( $value ) ) {
					$value =  (int) $value;
					if ( ! empty( $value ) ) {
						$query_arguments[ $key ] = $value;
					}
				}
				unset( $arguments[ $key ] );
				break;
			case 'meta_value_num':
				$children_ok = false;
				// fallthru
			case 'offset':
				if ( is_numeric( $value ) ) {
					$query_arguments[ $key ] = (int) $value;
					if ( ! $children_ok ) {
						$use_children = false;
					}
				}
				unset( $arguments[ $key ] );
				break;
			case 'paged':
				// Avoid PHP deprecation warning about null strtolower argument
				if ( NULL !== $value ) {
					if ( 'current' == strtolower( $value ) ) {
						/*
						 * Note: The query variable 'page' holds the pagenumber for a single paginated
						 * Post or Page that includes the <!--nextpage--> Quicktag in the post content. 
						 */
						if ( get_query_var('page') ) {
							$query_arguments[ $key ] = get_query_var('page');
						} else {
							$query_arguments[ $key ] = (get_query_var('paged')) ? get_query_var('paged') : 1;
						}
					} elseif ( is_numeric( $value ) ) {
						$query_arguments[ $key ] = (int) $value;
					} elseif ( '' === $value ) {
						$query_arguments[ $key ] = 1;
					}
				}

				unset( $arguments[ $key ] );
				break;
			case  $mla_page_parameter :
			case 'mla_paginate_total':
				if ( is_numeric( $value ) ) {
					$query_arguments[ $key ] = (int) $value;
				} elseif ( '' === $value ) {
					$query_arguments[ $key ] = 1;
				}

				unset( $arguments[ $key ] );
				break;
			case 'author':
			case 'cat':
			case 'tag_id':
				if ( ! empty( $value ) ) {
					if ( is_array( $value ) ) {
						$query_arguments[ $key ] = array_filter( $value );
					} else {
						$query_arguments[ $key ] = array_filter( array_map( 'intval', explode( ",", $value ) ) );
					}

					if ( 1 == count( $query_arguments[ $key ] ) ) {
						$query_arguments[ $key ] = $query_arguments[ $key ][0];
					} else {
						$query_arguments[ $key ] = implode(',', $query_arguments[ $key ] );
					}

					$use_children = false;
				}
				unset( $arguments[ $key ] );
				break;
			case 'category__and':
			case 'category__in':
			case 'category__not_in':
			case 'tag__and':
			case 'tag__in':
			case 'tag__not_in':
			case 'include':
				$children_ok = false;
				// fallthru
			case 'exclude':
				if ( ! empty( $value ) ) {
					if ( is_array( $value ) ) {
						$value = array_filter( $value );
					} else {
						$value = array_filter( array_map( 'intval', explode( ",", $value ) ) );
					}

					if ( ! empty( $value ) ) {
						$query_arguments[ $key ] = $value;

						if ( ! $children_ok ) {
							$use_children = false;
						}
					}
				}
				unset( $arguments[ $key ] );
				break;
			case 'tag_slug__and':
			case 'tag_slug__in':
				if ( ! empty( $value ) ) {
					if ( is_array( $value ) ) {
						$query_arguments[ $key ] = $value;
					} else {
						$query_arguments[ $key ] = array_filter( array_map( 'trim', explode( ",", $value ) ) );
					}

					$use_children = false;
				}
				unset( $arguments[ $key ] );
				break;
			case 'nopaging': // boolean value, default false
				if ( ! empty( $value ) && ( 'false' != strtolower( $value ) ) ) {
					$query_arguments[ $key ] = true;
				}

				unset( $arguments[ $key ] );
				break;
			// boolean values, default true
			case 'cache_results':
			case 'update_post_meta_cache':
			case 'update_post_term_cache':
				if ( ! empty( $value ) && ( 'true' != strtolower( $value ) ) ) {
					$query_arguments[ $key ] = false;
				}

				unset( $arguments[ $key ] );
				break;
			case 'whole_word':
			case 'sentence':
			case 'exact':
				if ( ! empty( $value ) && ( 'true' == strtolower( $value ) ) ) {
					MLAQuery::$search_parameters[ $key ] = true;
				} else {
					MLAQuery::$search_parameters[ $key ] = false;
				}

				unset( $arguments[ $key ] );
				break;
			case 'mla_search_connector':
			case 'mla_phrase_connector':
			case 'mla_term_connector':
				if ( ! empty( $value ) && ( 'OR' == strtoupper( $value ) ) ) {
					MLAQuery::$search_parameters[ $key ] = 'OR';
				} else {
					MLAQuery::$search_parameters[ $key ] = 'AND';
				}

				unset( $arguments[ $key ] );
				break;
			case 'mla_phrase_delimiter':
			case 'mla_term_delimiter':
				if ( ! empty( $value ) ) {
					MLAQuery::$search_parameters[ $key ] = substr( $value, 0, 1 );
				}

				unset( $arguments[ $key ] );
				break;
			case 'mla_terms_phrases':
				$children_ok = false;
				$value = stripslashes( trim( $value ) );
				// fallthru
			case 'mla_terms_taxonomies':
			case 'mla_search_fields':
				if ( ! empty( $value ) ) {
					MLAQuery::$search_parameters[ $key ] = $value;

					if ( ! $children_ok ) {
						$use_children = false;
					}
				}

				unset( $arguments[ $key ] );
				break;
			case 's':
				MLAQuery::$search_parameters['s'] = stripslashes( trim( $value ) );
				// fallthru
			case 'author_name':
			case 'category_name':
			case 'tag':
			case 'meta_key':
			case 'meta_compare':
				$children_ok = false;
				// fallthru
			case 'post_type':
			case 'post_status':
			case 'post_mime_type':
			case 'orderby':
				if ( ! empty( $value ) ) {
					$query_arguments[ $key ] = $value;

					if ( ! $children_ok ) {
						$use_children = false;
					}
				}

				unset( $arguments[ $key ] );
				break;
			case 'meta_value':
				if ( ! empty( $value ) ) {
					if ( false !== strpos( $value, ',' ) ) {
						// WP_Query expects a real array for multiple values
						$query_arguments[ $key ] = explode( ',', $value );
					} else {
						$query_arguments[ $key ] = $value;
					}

					$use_children = false;
				}

				unset( $arguments[ $key ] );
				break;
			case 'order':
				if ( ! empty( $value ) ) {
					$value = strtoupper( $value );
					if ( in_array( $value, array( 'ASC', 'DESC' ) ) ) {
						$query_arguments[ $key ] = $value;
					}
				}

				unset( $arguments[ $key ] );
				break;
			case 'year': // 4 digit year, e.g., 2021
				if ( 4 === strlen( $value ) && is_numeric( $value ) ) {
					$query_arguments[ $key ] = (int) $value;
					$use_children = false;
				}

				unset( $arguments[ $key ] );
				break;
			case 'monthnum': // Month number (from 1 to 12)
				$value = absint( $value );
				if ( ( 0 < $value ) && ( 13 > $value ) ) {
					$query_arguments[ $key ] = $value;
					$use_children = false;
				}

				unset( $arguments[ $key ] );
				break;
			case 'w': // Week of the year (from 0 to 53). Uses MySQL WEEK command. The mode is dependent on the “start_of_week” option.
				$value = absint( $value );
				if ( ( 0 < $value ) && ( 54 > $value ) ) {
					$query_arguments[ $key ] = $value;
					$use_children = false;
				}

				unset( $arguments[ $key ] );
				break;
			case 'day': // Day of the month (from 1 to 31)
				$value = absint( $value );
				if ( ( 0 < $value ) && ( 32 > $value ) ) {
					$query_arguments[ $key ] = $value;
					$use_children = false;
				}

				unset( $arguments[ $key ] );
				break;
			case 'm': //YearMonth, e.g., 202101
				if ( 6 === strlen( $value ) && is_numeric( $value ) ) {
					$query_arguments[ $key ] = (int) $value;
					$use_children = false;
				}

				unset( $arguments[ $key ] );
				break;
			case 'date_query':
				if ( ! empty( $value ) ) {
					if ( is_array( $value ) ) {
						$query_arguments[ $key ] = $value;
					} else {
						$date_query = self::_convert_query_parameter( 'date_query', $value, ( $where_used_query ? array( array( 'key' => 'unlikely', 'value' => 'none or otherwise unlikely' ) ) : '' ) );
 
						if ( is_array( $date_query ) ) {
							$query_arguments[ $key ] = $date_query;
						} else {
							self::$mla_debug = $old_debug_mode;
							return $date_query;
						}
					} // not array

					$use_children = false;
				}

				unset( $arguments[ $key ] );
				break;
			case 'meta_query':
				if ( ! empty( $value ) ) {
					if ( is_array( $value ) ) {
						$query_arguments[ $key ] = $value;
					} else {
						$meta_query = self::_convert_query_parameter( 'meta_query', $value, ( $where_used_query ? array( array( 'key' => 'unlikely', 'value' => 'none or otherwise unlikely' ) ) : '' ) );

						if ( is_array( $meta_query ) ) {
							$query_arguments[ $key ] = $meta_query;
						} else {
							self::$mla_debug = $old_debug_mode;
							return $meta_query; // '<p>' . __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'Invalid mla_gallery', 'media-library-assistant' ) . ' meta_query = ' . var_export( $value, true ) . '</p>';
						}
					} // not array

					$use_children = false;
				}

				unset( $arguments[ $key ] );
				break;
			case 'fields':
				if ( ! empty( $value ) ) {
					$value = strtolower( $value );
					if ( in_array( $value, array( 'ids', 'id=>parent' ) ) ) {
						$query_arguments[ $key ] = $value;
					}
				}

				unset( $arguments[ $key ] );
				break;
			default:
				// ignore anything else
			} // switch $key
		} // foreach $arguments 

		// Process the ignore/no/any values assigned queries TODO
		if ( ! ( empty( $query_arguments['meta_key'] ) || empty( $query_arguments['meta_value'] ) ) ) {
			$special_key = '';
			foreach ( array( 'ignore.values.assigned', 'no.values.assigned', 'any.values.assigned' ) as $value ) {
				if ( is_array( $query_arguments['meta_value'] ) ) {
					if ( in_array( $value, $query_arguments['meta_value'] ) ) {
						$special_key = $value;
						break;
					}
				} elseif ( $value === $query_arguments['meta_value'] ) {
					$special_key = $value;
					break;
				}
			}
			
			switch ( $special_key ) {
				case 'ignore.values.assigned':
					$query_arguments['meta_key'] = '';
					$query_arguments['meta_value'] = '';
					$query_arguments['meta_compare'] = '';
					break;
				case 'no.values.assigned':
					$query_arguments['meta_value'] = '';
					$query_arguments['meta_compare'] = 'NOT EXISTS';
					break;
				case 'any.values.assigned':
					$query_arguments['meta_value'] = '';
					$query_arguments['meta_compare'] = 'EXISTS';
			}
		}
		
		// Decide whether to use a "get_children" style query
		self::$query_parameters['disable_tax_join'] = $is_tax_query && ! $use_children;
		if ( $use_children && ! isset( $query_arguments['post_parent'] ) ) {
			if ( ! isset( $query_arguments['id'] ) ) {
				$query_arguments['post_parent'] = $post_parent;
			} else {
				$query_arguments['post_parent'] = $query_arguments['id'];
			}

			unset( $query_arguments['id'] );
		}

		if ( isset( $query_arguments['numberposts'] ) && ! isset( $query_arguments['posts_per_page'] )) {
			$query_arguments['posts_per_page'] = $query_arguments['numberposts'];
		}
		unset( $query_arguments['numberposts'] );

		/*
		 * Apply the archive/search tests here because WP_Query doesn't apply them to galleries within
		 * search results or archive pages.
		 */
		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' <strong>mla_debug is_archive()</strong> = ' . var_export( is_archive(), true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>mla_debug is_search()</strong> = ' . var_export( is_search(), true ) );
		}

		if ( isset( $query_arguments['posts_per_archive_page'] ) && ( is_archive() || is_search() ) ) {
			$query_arguments['posts_per_page'] = $query_arguments['posts_per_archive_page'];
		}
		unset( $query_arguments['posts_per_archive_page'] );

		// MLA pagination will override WordPress pagination
		if ( isset( $query_arguments[ $mla_page_parameter ] ) ) {
			unset( $query_arguments['nopaging'] );
			unset( $query_arguments['offset'] );
			unset( $query_arguments['paged'] );

			if ( isset( $query_arguments['mla_paginate_total'] ) && ( $query_arguments[ $mla_page_parameter ] > $query_arguments['mla_paginate_total'] ) ) {
				$query_arguments['offset'] = 0x7FFFFFFF; // suppress further output
			} else {
				$query_arguments['paged'] = $query_arguments[ $mla_page_parameter ];
			}
		} else {
			if ( isset( $query_arguments['posts_per_page'] ) || isset( $query_arguments['posts_per_archive_page'] ) ||
				isset( $query_arguments['paged'] ) || isset( $query_arguments['offset'] ) ) {
				unset( $query_arguments['nopaging'] );
			}
		}
		unset( $query_arguments[ $mla_page_parameter ] );
		unset( $query_arguments['mla_paginate_total'] );

		if ( isset( $query_arguments['post_mime_type'] ) && ('all' == strtolower( $query_arguments['post_mime_type'] ) ) ) {
			unset( $query_arguments['post_mime_type'] );
		}

		if ( ! empty($query_arguments['include']) ) {
			$incposts = wp_parse_id_list( $query_arguments['include'] );

			if ( ! ( isset( $query_arguments['posts_per_page'] ) && ( 0 < $query_arguments['posts_per_page'] ) ) ) {
				$query_arguments['posts_per_page'] = count($incposts);  // only the number of posts included
			}

			$query_arguments['post__in'] = $incposts;
			unset( $query_arguments['include'] );
		} elseif ( ! empty($query_arguments['exclude']) ) {
			$query_arguments['post__not_in'] = wp_parse_id_list( $query_arguments['exclude'] );
			unset( $query_arguments['exclude'] );
		}

		$query_arguments['ignore_sticky_posts'] = true;
		$query_arguments['no_found_rows'] = is_null( $return_found_rows ) ? true : ! $return_found_rows;

		// We will always handle "orderby" in our filter
		self::$query_parameters['orderby'] = self::mla_validate_sql_orderby( $query_arguments );
		if ( false === self::$query_parameters['orderby'] ) {
			unset( self::$query_parameters['orderby'] );
		}

		// RML Pro overrides orderby if it's not present in the query arguments
		if ( false === defined('RML_FILE') ) {
			unset( $query_arguments['orderby'] );
			unset( $query_arguments['order'] );
		}
		
		if ( self::$mla_debug ) {
			add_filter( 'posts_clauses', 'MLAShortcode_Support::mla_shortcode_query_posts_clauses_filter', 0x7FFFFFFF, 1 );
			add_filter( 'posts_clauses_request', 'MLAShortcode_Support::mla_shortcode_query_posts_clauses_request_filter', 0x7FFFFFFF, 1 );
		}

		add_filter( 'posts_join', 'MLAShortcode_Support::mla_shortcode_query_posts_join_filter', 0x7FFFFFFF, 1 );
		add_filter( 'posts_where', 'MLAShortcode_Support::mla_shortcode_query_posts_where_filter', 0x7FFFFFFF, 1 );
		add_filter( 'posts_orderby', 'MLAShortcode_Support::mla_shortcode_query_posts_orderby_filter', 0x7FFFFFFF, 1 );

		/*
		 * Handle the keyword and terms search in the posts_search filter.
		 * One or both of 'mla_terms_phrases' and 's' must be present to
		 * trigger the search.
		 */
		if ( empty( MLAQuery::$search_parameters['mla_terms_phrases'] ) && empty( MLAQuery::$search_parameters['s'] ) ) {
			MLAQuery::$search_parameters = array( 'debug' => 'none' );
		} else {
			/*
			 * Convert Terms Search parameters to the filter's requirements.
			 * mla_terms_taxonomies is shared with keyword search.
			 */
			if ( empty( MLAQuery::$search_parameters['mla_terms_taxonomies'] ) ) {
				MLAQuery::$search_parameters['mla_terms_search']['taxonomies'] = MLACore::mla_supported_taxonomies( 'term-search' );
			} else {
				MLAQuery::$search_parameters['mla_terms_search']['taxonomies'] = array_filter( array_map( 'trim', explode( ',', MLAQuery::$search_parameters['mla_terms_taxonomies'] ) ) );
			}

			if ( ! empty( MLAQuery::$search_parameters['mla_terms_phrases'] ) ) {
				MLAQuery::$search_parameters['mla_terms_search']['phrases'] = MLAQuery::$search_parameters['mla_terms_phrases'];

				if ( empty( MLAQuery::$search_parameters['mla_phrase_delimiter'] ) ) {
					MLAQuery::$search_parameters['mla_terms_search']['phrase_delimiter'] = ' ';
				} else {
					MLAQuery::$search_parameters['mla_terms_search']['phrase_delimiter'] = MLAQuery::$search_parameters['mla_phrase_delimiter'];
				}

				if ( empty( MLAQuery::$search_parameters['mla_phrase_connector'] ) ) {
					MLAQuery::$search_parameters['mla_terms_search']['radio_phrases'] = 'AND';
				} else {
					MLAQuery::$search_parameters['mla_terms_search']['radio_phrases'] = MLAQuery::$search_parameters['mla_phrase_connector'];
				}

				if ( empty( MLAQuery::$search_parameters['mla_term_delimiter'] ) ) {
					MLAQuery::$search_parameters['mla_terms_search']['term_delimiter'] = ',';
				} else {
					MLAQuery::$search_parameters['mla_terms_search']['term_delimiter'] = MLAQuery::$search_parameters['mla_term_delimiter'];
				}

				if ( empty( MLAQuery::$search_parameters['mla_term_connector'] ) ) {
					MLAQuery::$search_parameters['mla_terms_search']['radio_terms'] = 'OR';
				} else {
					MLAQuery::$search_parameters['mla_terms_search']['radio_terms'] = MLAQuery::$search_parameters['mla_term_connector'];
				}

				MLAQuery::$search_parameters['mla_terms_search']['whole_word'] = ! empty( MLAQuery::$search_parameters['whole_word'] );
				MLAQuery::$search_parameters['mla_terms_search']['exact'] = ! empty( MLAQuery::$search_parameters['exact'] );
				MLAQuery::$search_parameters['mla_terms_search']['sentence'] = ! empty( MLAQuery::$search_parameters['sentence'] );
			}

			// Remove terms-search-specific parameters
			unset( MLAQuery::$search_parameters['mla_terms_phrases'] );
			unset( MLAQuery::$search_parameters['mla_terms_taxonomies'] );
			unset( MLAQuery::$search_parameters['mla_phrase_connector'] );
			unset( MLAQuery::$search_parameters['mla_term_connector'] );
			unset( MLAQuery::$search_parameters['whole_word'] );

			if ( empty( MLAQuery::$search_parameters['mla_search_fields'] ) ) {
				MLAQuery::$search_parameters['mla_search_fields'] = array( 'title', 'content' );
			} else {
				MLAQuery::$search_parameters['mla_search_fields'] = array_filter( array_map( 'trim', explode( ',', MLAQuery::$search_parameters['mla_search_fields'] ) ) );
				MLAQuery::$search_parameters['mla_search_fields'] = array_intersect( array( 'title', 'name', 'excerpt', 'content', 'alt-text', 'file', 'terms' ), MLAQuery::$search_parameters['mla_search_fields'] );

				if ( in_array( 'alt-text', MLAQuery::$search_parameters['mla_search_fields'] ) ) {
					self::$query_parameters[MLAQuery::MLA_ALT_TEXT_SUBQUERY] = true;
				}

				if ( in_array( 'file', MLAQuery::$search_parameters['mla_search_fields'] ) ) {
					self::$query_parameters[MLAQuery::MLA_FILE_SUBQUERY] = true;
				}

				// Look for keyword search including 'terms' 
				foreach ( MLAQuery::$search_parameters['mla_search_fields'] as $index => $field ) {
					if ( 'terms' == $field ) {
						if ( isset( MLAQuery::$search_parameters['mla_terms_search']['phrases'] ) ) {
							// The Terms Search overrides any terms-based keyword search for now; too complicated.
							unset ( MLAQuery::$search_parameters['mla_search_fields'][ $index ] );
						} else {
							MLAQuery::$search_parameters['mla_search_taxonomies'] = MLAQuery::$search_parameters['mla_terms_search']['taxonomies'];
							unset( MLAQuery::$search_parameters['mla_terms_search']['taxonomies'] );
						}
					} // terms in search fields
				}
			} // mla_search_fields present

			if ( empty( MLAQuery::$search_parameters['mla_search_connector'] ) ) {
				MLAQuery::$search_parameters['mla_search_connector'] = 'AND';
			}

			if ( empty( MLAQuery::$search_parameters['whole_word'] ) ) {
				MLAQuery::$search_parameters['whole_word'] = false;
			}

			if ( empty( MLAQuery::$search_parameters['sentence'] ) ) {
				MLAQuery::$search_parameters['sentence'] = false;
			}

			if ( empty( MLAQuery::$search_parameters['exact'] ) ) {
				MLAQuery::$search_parameters['exact'] = false;
			}

			MLAQuery::$search_parameters['debug'] = self::$mla_debug ? 'shortcode' : 'none';

			add_filter( 'posts_search', 'MLAQuery::mla_query_posts_search_filter', 10, 2 );
			add_filter( 'posts_groupby', 'MLAQuery::mla_query_posts_groupby_filter' );
		}

		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' <strong>mla_debug $wp_filter[posts_where]</strong> = ' . MLACore::mla_decode_wp_filter('posts_where') );
			MLACore::mla_debug_add( __LINE__ . ' <strong>mla_debug $wp_filter[posts_orderby]</strong> = ' . MLACore::mla_decode_wp_filter('posts_orderby') );
		}

		/*
		 * Disable Relevanssi - A Better Search, v3.2 by Mikko Saari 
		 * relevanssi_prevent_default_request( $request, $query )
		 * apply_filters('relevanssi_admin_search_ok', $admin_search_ok, $query );
		 * apply_filters('relevanssi_prevent_default_request', $prevent, $query );
		 */
		if ( function_exists( 'relevanssi_prevent_default_request' ) ) {
			add_filter( 'relevanssi_admin_search_ok', 'MLAQuery::mla_query_relevanssi_admin_search_ok_filter' );
			add_filter( 'relevanssi_prevent_default_request', 'MLAQuery::mla_query_relevanssi_prevent_default_request_filter' );
		}

		if ( class_exists( 'MLA_Polylang' ) ) {
			$query_arguments = apply_filters( 'mla_get_shortcode_attachments_final_terms', $query_arguments, $return_found_rows );
		}

		// Paid Memberships Pro support
		if( defined('PMPRO_VERSION') ) {
			add_filter( 'pmpro_search_filter_post_types', 'MLAShortcode_Support::mla_pmp_hide_attachments_filter' );
		}

		if ( isset( $query_arguments['post_type'] ) && is_string( $query_arguments['post_type'] ) && ( 'attachment' === $query_arguments['post_type'] ) ) {
			if ( self::$query_parameters['disable_tax_join'] ) {
				// Suppress WordPress WP_Query LEFT JOIN on post_parent, etc.
				$query_arguments['post_type'] = 'mladisabletaxjoin';
			} 

			if ( defined('RML_FILE') ) {
				if ( $arguments['mla_allow_rml'] ) {
					add_filter( 'posts_clauses', 'MLAShortcode_Support::mla_shortcode_query_posts_clauses_rml_filter', 9, 2 );

					if ( ! empty( $arguments['mla_rml_folder'] ) ) {
						unset( $query_arguments['post_parent'] );
						$query_arguments['rml_folder'] = $arguments['mla_rml_folder'];

						if ( $arguments['mla_rml_include_children'] ) {
							$query_arguments['rml_include_children'] = $arguments['mla_rml_include_children'];
						}
					}
				} else {
					// Suppress RML additions to MLA queries
					$query_arguments['post_type'] = 'mladisablerml';
				}
			}

			if ( defined('CATF_VERSION')){	
				if ( $arguments['mla_allow_catf'] ) {
					if ( ! empty( $arguments['mla_catf_folder'] ) ) {
						unset( $query_arguments['post_parent'] );
						$query_arguments['catf'] = $arguments['mla_catf_folder'];
					}
				}
			}

		} // post_type is attachment

		MLAShortcodes::$mla_gallery_wp_query_object = new WP_Query;
		$attachments = MLAShortcodes::$mla_gallery_wp_query_object->query( $query_arguments );

		// Paid Memberships Pro support
		if( defined('PMPRO_VERSION') ) {
			remove_filter( 'pmpro_search_filter_post_types', 'MLAShortcode_Support::mla_pmp_hide_attachments_filter' );
		}

		/*
		 * $return_found_rows is used to indicate that the call comes from gallery_shortcode(),
		 * which is the only call that supplies it.
		 */
		if ( is_null( $return_found_rows ) ) {
			$return_found_rows = false;
		} else  {
			do_action( 'mla_gallery_wp_query_object', $query_arguments );

			if ( $return_found_rows ) {
				$attachments['found_rows'] = absint( MLAShortcodes::$mla_gallery_wp_query_object->found_posts );
				$attachments['max_num_pages'] = absint( MLAShortcodes::$mla_gallery_wp_query_object->max_num_pages );
			}

			$filtered_attachments = apply_filters_ref_array( 'mla_gallery_the_attachments', array( NULL, &$attachments ) ) ;
			if ( !is_null( $filtered_attachments ) ) {
				$attachments = $filtered_attachments;
			}
		}

		if ( ! empty( MLAQuery::$search_parameters ) ) {
			remove_filter( 'posts_groupby', 'MLAQuery::mla_query_posts_groupby_filter' );
			remove_filter( 'posts_search', 'MLAQuery::mla_query_posts_search_filter' );
		}

		if ( function_exists( 'relevanssi_prevent_default_request' ) ) {
			remove_filter( 'relevanssi_admin_search_ok', 'MLAQuery::mla_query_relevanssi_admin_search_ok_filter' );
			remove_filter( 'relevanssi_prevent_default_request', 'MLAQuery::mla_query_relevanssi_prevent_default_request_filter' );
		}

		remove_filter( 'posts_join', 'MLAShortcode_Support::mla_shortcode_query_posts_join_filter', 0x7FFFFFFF );
		remove_filter( 'posts_where', 'MLAShortcode_Support::mla_shortcode_query_posts_where_filter', 0x7FFFFFFF );
		remove_filter( 'posts_orderby', 'MLAShortcode_Support::mla_shortcode_query_posts_orderby_filter', 0x7FFFFFFF );

		if ( self::$mla_debug ) {
			remove_filter( 'posts_clauses', 'MLAShortcode_Support::mla_shortcode_query_posts_clauses_filter', 0x7FFFFFFF );
			remove_filter( 'posts_clauses_request', 'MLAShortcode_Support::mla_shortcode_query_posts_clauses_request_filter', 0x7FFFFFFF );

			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug query', 'media-library-assistant' ) . '</strong> = ' . var_export( $query_arguments, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug request', 'media-library-assistant' ) . '</strong> = ' . var_export( MLAShortcodes::$mla_gallery_wp_query_object->request, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug query_vars', 'media-library-assistant' ) . '</strong> = ' . var_export( MLAShortcodes::$mla_gallery_wp_query_object->query_vars, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug post_count', 'media-library-assistant' ) . '</strong> = ' . var_export( MLAShortcodes::$mla_gallery_wp_query_object->post_count, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug found_posts', 'media-library-assistant' ) . '</strong> = ' . var_export( MLAShortcodes::$mla_gallery_wp_query_object->found_posts, true ) );
		}

		MLAQuery::$search_parameters = array( 'debug' => 'none' );
		MLAShortcodes::$mla_gallery_wp_query_object = NULL;

		self::$mla_debug = $old_debug_mode;
		return $attachments;
	}

	/**
	 * Filters the JOIN clause for shortcode queries
	 * 
	 * Defined as public because it's a filter.
	 *
	 * @since 2.20
	 *
	 * @param	string	query clause before modification
	 *
	 * @return	string	query clause after item modification
	 */
	public static function mla_shortcode_query_posts_join_filter( $join_clause ) {
		global $wpdb;

		if ( self::$mla_debug ) {
			$old_clause = $join_clause;
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug JOIN filter', 'media-library-assistant' ) . '</strong> = ' . var_export( $join_clause, true ) );
		}

		/*
		 * Set for taxonomy queries unless post_parent=current. If true, we must disable
		 * the LEFT JOIN clause that get_posts() adds to taxonomy queries.
		 * We leave the clause in because the WHERE clauses refer to "p2.".
		 * Replaced in MLA v2.94 by "post_type = 'mladisabletaxjoin'"
		 * /
		if ( self::$query_parameters['disable_tax_join'] ) {
			$join_clause = str_replace( " LEFT JOIN {$wpdb->posts} AS p2 ON ({$wpdb->posts}.post_parent = p2.ID) ", " LEFT JOIN {$wpdb->posts} AS p2 ON (p2.ID = p2.ID) ", $join_clause );
		} // */

		// These joins support the 'terms' search_field
		if ( isset( MLAQuery::$search_parameters['tax_terms_count'] ) ) {
			$tax_index = 0;
			$tax_clause = '';

			while ( $tax_index < MLAQuery::$search_parameters['tax_terms_count'] ) {
				$prefix = 'mlatt' . $tax_index++;
				$tax_clause .= sprintf( ' INNER JOIN %1$s AS %2$s ON (%3$s.ID = %2$s.object_id)', $wpdb->term_relationships, $prefix, $wpdb->posts );
			}

			$join_clause .= $tax_clause;
		}

		/*
		 * ALT Text and File Name searches use a subquery to build an intermediate table and
		 * modify the JOIN to include posts with no value for the metadata field.
		 */
		if ( self::$query_parameters[MLAQuery::MLA_ALT_TEXT_SUBQUERY] ) {
			$sub_query = sprintf( 'SELECT post_id, meta_value FROM %1$s WHERE %1$s.meta_key = \'%2$s\'', $wpdb->postmeta, '_wp_attachment_image_alt' );
			$join_clause .= sprintf( ' LEFT JOIN ( %1$s ) %2$s ON (%3$s.ID = %2$s.post_id)', $sub_query, MLAQuery::MLA_ALT_TEXT_SUBQUERY, $wpdb->posts );
		}

		if ( self::$query_parameters[MLAQuery::MLA_FILE_SUBQUERY] ) {
			$sub_query = sprintf( 'SELECT post_id, meta_value FROM %1$s WHERE %1$s.meta_key = \'%2$s\'', $wpdb->postmeta, '_wp_attached_file' );
			$join_clause .= sprintf( ' LEFT JOIN ( %1$s ) %2$s ON (%3$s.ID = %2$s.post_id)', $sub_query, MLAQuery::MLA_FILE_SUBQUERY, $wpdb->posts );
		}

		if ( self::$mla_debug && ( $old_clause != $join_clause ) ) {
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug modified JOIN filter', 'media-library-assistant' ) . '</strong> = ' . var_export( $join_clause, true ) );
		}

		return $join_clause;
	}

	/**
	 * Filters the WHERE clause for shortcode queries
	 * 
	 * Captures debug information. Adds whitespace to the post_type = 'attachment'
	 * phrase to circumvent subsequent Role Scoper modification of the clause.
	 * Handles post_parent "any" and "none" cases.
	 * Defined as public because it's a filter.
	 *
	 * @since 2.20
	 *
	 * @param	string	query clause before modification
	 *
	 * @return	string	query clause after modification
	 */
	public static function mla_shortcode_query_posts_where_filter( $where_clause ) {
		global $table_prefix;

		if ( self::$mla_debug ) {
			$old_clause = $where_clause;
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug WHERE filter', 'media-library-assistant' ) . '</strong> = ' . var_export( $where_clause, true ) );
		}

		// Reverse post_type modification used to avoid redundant LEFT JOIN insertion or RML folder insertion
		if ( strpos( $where_clause, "post_type = 'mladisabletaxjoin'" ) ) {
			$where_clause = str_replace( "post_type = 'mladisabletaxjoin'", "post_type = 'attachment'", $where_clause );
		} elseif ( strpos( $where_clause, "post_type = 'mladisablerml'" ) ) {
			$where_clause = str_replace( "post_type = 'mladisablerml'", "post_type = 'attachment'", $where_clause );
		}

		// Add whitespace to prevent Role Scoper plugin clause modification
		if ( strpos( $where_clause, "post_type = 'attachment'" ) ) {
			$where_clause = str_replace( "post_type = 'attachment'", "post_type  =  'attachment'", $where_clause );
		}

		if ( isset( self::$query_parameters['post_parent'] ) ) {
			if ( is_array( self::$query_parameters['post_parent'] ) ) {
				$parent_list = implode( ',', self::$query_parameters['post_parent'] );
				$where_clause .= " AND {$table_prefix}posts.post_parent IN ({$parent_list})";
			} else {
				switch ( self::$query_parameters['post_parent'] ) {
				case 'any':
					$where_clause .= " AND {$table_prefix}posts.post_parent > 0";
					break;
				case 'none':
					$where_clause .= " AND {$table_prefix}posts.post_parent < 1";
					break;
				}
			}
		}

		// Support Plugin Name: Featured Image from URL, "Media Library" option setting
	    if ( function_exists('fifu_is_off') && fifu_is_off('fifu_media_library')) {
			$where_clause .= " AND {$table_prefix}posts.post_author <> 77777";
		}

		if ( self::$mla_debug && ( $old_clause != $where_clause ) ) {
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug modified WHERE filter', 'media-library-assistant' ) . '</strong> = ' . var_export( $where_clause, true ) );
		}

		return $where_clause;
	}

	/**
	 * Filters the ORDERBY clause for shortcode queries
	 * 
	 * This is an enhanced version of the code found in wp-includes/query.php, function get_posts.
	 * Defined as public because it's a filter.
	 *
	 * @since 2.20
	 *
	 * @param	string	query clause before modification
	 *
	 * @return	string	query clause after modification
	 */
	public static function mla_shortcode_query_posts_orderby_filter( $orderby_clause ) {
		global $wpdb;

		if ( self::$mla_debug ) {
			$replacement = isset( self::$query_parameters['orderby'] ) ? var_export( self::$query_parameters['orderby'], true ) : 'none';
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug ORDER BY filter, incoming', 'media-library-assistant' ) . '</strong> = ' . var_export( $orderby_clause, true ) . '<br>' . __( 'Replacement ORDER BY clause', 'media-library-assistant' ) . ' = ' . $replacement );
		}

		if ( isset( self::$query_parameters['orderby'] ) ) {
			return self::$query_parameters['orderby'];
		}

		return $orderby_clause;
	}

	/**
	 * Reverse mladisabletaxjoin/mladisablerml overide for RML queries
	 * 
	 * @since 2.95
	 *
	 * @param	array	query clauses before modification
	 * @param	WP_Query query object
	 */
	public static function mla_shortcode_query_posts_clauses_rml_filter( $pieces, $query ) {
		remove_filter( 'posts_clauses', 'MLAShortcode_Support::mla_shortcode_query_posts_clauses_rml_filter', 9 );

        if ( $query->get( 'post_type' ) === 'mladisabletaxjoin' ) {
            $query->set( 'post_type', 'attachment' );
		}

		return $pieces;
	}

	/**
	 * Filters all clauses for shortcode queries, pre caching plugins
	 * 
	 * This is for debug purposes only.
	 * Defined as public because it's a filter.
	 *
	 * @since 2.20
	 *
	 * @param	array	query clauses before modification
	 *
	 * @return	array	query clauses after modification (none)
	 */
	public static function mla_shortcode_query_posts_clauses_filter( $pieces ) {
		MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug posts_clauses filter', 'media-library-assistant' ) . '</strong> = ' . var_export( $pieces, true ) );

		return $pieces;
	}

	/**
	 * Filters all clauses for shortcode queries, post caching plugins
	 * 
	 * This is for debug purposes only.
	 * Defined as public because it's a filter.
	 *
	 * @since 2.20
	 *
	 * @param	array	query clauses before modification
	 *
	 * @return	array	query clauses after modification (none)
	 */
	public static function mla_shortcode_query_posts_clauses_request_filter( $pieces ) {
		MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug posts_clauses_request filter', 'media-library-assistant' ) . '</strong> = ' . var_export( $pieces, true ) );

		return $pieces;
	}

	/**
	 * Data selection parameters for mla_get_all_none_term_counts()
	 *
	 * @since 2.96
	 *
	 * @var	array
	 */
	private static $mla_get_all_none_term_counts = array(
		'taxonomy' => 'post_tag',
		'post_mime_type' => 'image',
		'post_type' => 'attachment',
		'post_status' => 'inherit',
	);

	/**
	 * Retrieve the "ignore.", "no.", and "any." "terms.assigned" counts in one taxonomy
	 *
	 * taxonomy - string containing one or more (comma-delimited) taxonomy names
	 * or an array of taxonomy names. Default 'post_tag'. Only the first value is used.
	 *
	 * post_mime_type - MIME type(s) of the items to include in the term-specific counts. Default 'all'.
	 *
	 * post_type - The post type(s) of the items to include in the term-specific counts.
	 * The default is "attachment". 
	 *
	 * post_status - The post status value(s) of the items to include in the term-specific counts.
	 * The default is "inherit".
	 *
	 * @since 2.96
	 *
	 * @param	array	taxonomy to search and query parameters
	 *
	 * @return	array	( 'ignore.terms.assigned' => count, 'no.terms.assigned' => count, 'any.terms.assigned' => count )
	 */
	public static function mla_get_all_none_term_counts( $attr ) {
		global $wpdb;

		// Make sure $attr is an array, even if it's empty
		if ( empty( $attr ) ) {
			$attr = array();
		} elseif ( is_string( $attr ) ) {
			$attr = shortcode_parse_atts( $attr );
		}

		// Merge input arguments with defaults
		$attr = apply_filters( 'mla_get_terms_query_attributes', $attr );
		$arguments = shortcode_atts( self::$mla_get_all_none_term_counts, $attr );
		$arguments = apply_filters( 'mla_get_terms_query_arguments', $arguments );

		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug attributes', 'media-library-assistant' ) . '</strong> = ' . var_export( $attr, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug arguments', 'media-library-assistant' ) . '</strong> = ' . var_export( $arguments, true ) );
		}

		// Isolate the first taxonomy value
		if ( is_array( $arguments['taxonomy'] ) ) {
			$taxonomy = reset( $arguments['taxonomy'] );
		} else {
			$taxonomy = reset( explode( ',', $arguments['taxonomy'] ) );
		}

		$clause = array( 'SELECT' );
		$clause[] = 'COUNT( p.ID) as `ignore.terms.assigned`, COUNT( sq.object_id ) as `any.terms.assigned`';
		$clause[] = 'FROM ' . $wpdb->posts . ' AS p';
		$clause[] = 'LEFT JOIN ( ';
		$clause[] = 'SELECT DISTINCT tr.object_id FROM ' . $wpdb->term_relationships . ' as tr';
		$clause[] = 'INNER JOIN ' . $wpdb->term_taxonomy . ' as tt';
		$clause[] = 'ON tt.term_taxonomy_id = tr.term_taxonomy_id';
		$clause[] = 'AND tt.taxonomy = \'' . $taxonomy . '\'';
		$clause[] = ') AS sq';
		$clause[] = 'ON p.ID = sq.object_id';
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
				'ignore.terms.assigned' => 0,
				'no.terms.assigned' => 0,
				'any.terms.assigned' => 0,
				'wp_error_code' => $results->get_error_code(),
				'wp_error_message' => $results->get_error_message(),
				'wp_error_data' => $results->get_error_data( $results->get_error_code() ),
			);
		} elseif ( isset( $results[0] ) ) {
			$results = array_map( 'absint', (array) $results[0] );
			$results['no.terms.assigned'] = $results['ignore.terms.assigned'] - $results['any.terms.assigned'];
		} else {
			$results = array( 'ignore.terms.assigned' => 0, 'no.terms.assigned' => 0, 'any.terms.assigned' => 0 );
			$results['wpdb_last_error'] = $wpdb->last_error;
		}

		if ( self::$mla_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug query', 'media-library-assistant' ) . '</strong> = ' . var_export( $query, true ) );
			MLACore::mla_debug_add( __LINE__ . ' <strong>' . __( 'mla_debug results', 'media-library-assistant' ) . '</strong> = ' . var_export( $results, true ) );
		}

		return $results;
	} // mla_get_all_none_term_counts

	/**
	 * Data selection parameters for [mla_tag_cloud], [mla_term_list]
	 *
	 * @since 2.20
	 *
	 * @var	array
	 */
	public static $mla_get_terms_parameters = array(
		'taxonomy' => 'post_tag',
		'post_mime_type' => 'all',
		'post_type' => 'attachment',
		'post_status' => 'inherit',
		'ids' => array(),
		'fields' => 't.term_id, t.name, t.slug, t.term_group, tt.term_taxonomy_id, tt.taxonomy, tt.description, tt.parent, COUNT(p.ID) AS `count`',
		'include' => '',
		'exclude' => '',
		'parent' => '',
		'minimum' => 0,
		'no_count' => false,
		'number' => 0,
		'orderby' => 'name',
		'order' => 'ASC',
		'no_orderby' => false,
		'preserve_case' => false,
		'pad_counts' => false,
		'limit' => 0,
		'offset' => 0
	);

	/**
	 * Retrieve the terms in one or more taxonomies.
	 *
	 * Alternative to WordPress /wp-includes/taxonomy.php function get_terms() that provides
	 * an accurate count of attachments associated with each term.
	 *
	 * taxonomy - string containing one or more (comma-delimited) taxonomy names
	 * or an array of taxonomy names. Default 'post_tag'.
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
	 * include - An array, comma- or space-delimited string of term ids to include
	 * in the return array.
	 *
	 * exclude - An array, comma- or space-delimited string of term ids to exclude
	 * from the return array. If 'include' is non-empty, 'exclude' is ignored.
	 *
	 * parent - term_id of the terms' immediate parent; 0 for top-level terms.
	 *
	 * minimum - minimum number of attachments a term must have to be included. Default 0.
	 *
	 * no_count - 'true', 'false' (default) to suppress term-specific attachment-counting process.
	 *
	 * number - maximum number of term objects to return. Terms are ordered by count,
	 * descending and then by term_id before this value is applied. Default 0.
	 *
	 * orderby - 'count', 'id', 'name' (default), 'none', 'random', 'slug'
	 *
	 * order - 'ASC' (default), 'DESC'
	 *
	 * no_orderby - 'true', 'false' (default) to suppress ALL sorting clauses else false.
	 *
	 * preserve_case - 'true', 'false' (default) to make orderby case-sensitive.
	 *
	 * pad_counts - 'true', 'false' (default) to to include the count of all children in their parents' count.
	 *
	 * limit - final number of term objects to return, for pagination. Default 0.
	 *
	 * offset - number of term objects to skip, for pagination. Default 0.
	 *
	 * fields - string with fields for the SQL SELECT clause, e.g.,
	 *          't.term_id, t.name, t.slug, COUNT(p.ID) AS `count`'
	 *
	 * @since 2.20
	 *
	 * @param	array	taxonomies to search and query parameters
	 *
	 * @return	array	array of term objects, empty if none found
	 */
	public static function mla_get_terms( $attr ) {
		global $wpdb;

		// Make sure $attr is an array, even if it's empty
		if ( empty( $attr ) ) {
			$attr = array();
		} elseif ( is_string( $attr ) ) {
			$attr = shortcode_parse_atts( $attr );
		}

		// Merge input arguments with defaults
		$attr = apply_filters( 'mla_get_terms_query_attributes', $attr );
		$arguments = shortcode_atts( self::$mla_get_terms_parameters, $attr );
		$arguments = apply_filters( 'mla_get_terms_query_arguments', $arguments );

		// Build an array of individual clauses that can be filtered
		$clauses = array( 'fields' => '', 'join' => '', 'where' => '', 'orderby' => '', 'limits' => '', );

		/*
		 * If we're not counting attachments per term, strip
		 * post fields out of list and adjust the orderby value
		 */
		$no_count = true;
		$count_string = trim( strtolower( (string) $arguments['no_count'] ) );
		$field_array = explode( ',', $arguments['fields'] );

		switch ( $count_string ) {
			case 'true':
				foreach ( $field_array as $index => $field ) {
					if ( false !== strpos( $field, 'p.' ) ) {
						unset( $field_array[ $index ] );
					}
				}

				$arguments['minimum'] = 0;
				$arguments['post_mime_type'] = 'all';

				if ( 'count' == strtolower( $arguments['orderby'] ) ) {
					$arguments['orderby'] = 'none';
				}

				break;
			case 'internal':
				foreach ( $field_array as $index => $field ) {
					if ( false !== strpos( $field, 'p.' ) ) {
						unset( $field_array[ $index ] );
					}
				}

				$field_array[] = ' tt.count';
				$arguments['post_mime_type'] = 'all';
				break;
			default:
				$no_count = false;
		}

		// Support Simple Taxonomy Ordering plugin
		if ( 'tax_position' === strtolower( $arguments['orderby'] ) ) {
			if ( class_exists( 'Yikes_Custom_Taxonomy_Order', false ) ) {
				$field_array[] = ' term_meta.meta_value AS tax_position';
			} else {
				$arguments['orderby'] = 'name';
			}
		}

		// Support Simple Custom Post Order plugin
		if ( 'term_order' === strtolower( $arguments['orderby'] ) ) {
			if ( class_exists( 'SCPO_Engine', false ) ) {
				$field_array[] = ' t.term_order';
			} else {
				$arguments['orderby'] = 'name';
			}
		}

		$clauses['fields'] = implode( ',', $field_array );
		$clause = array ( 'INNER JOIN `' . $wpdb->term_taxonomy . '` AS tt ON t.term_id = tt.term_id' );
		$clause_parameters = array();

		if ( $no_count ) {
			// If no_count=internal we just omit the explicit count and use the WP-maintained count
			$no_count = 'true' === $count_string;

			if ( 'internal' === $count_string ) {
				// The ids parameter requires item-specific ID values
				if ( ! empty( $arguments['ids'] ) && empty( $arguments['include'] ) ) {
					$clause[] = 'LEFT JOIN `' . $wpdb->term_relationships . '` AS tr ON tt.term_taxonomy_id = tr.term_taxonomy_id';
				}
			}
		} else {
			$clause[] = 'LEFT JOIN `' . $wpdb->term_relationships . '` AS tr ON tt.term_taxonomy_id = tr.term_taxonomy_id';
			$clause[] = 'LEFT JOIN `' . $wpdb->posts . '` AS p ON ( tr.object_id = p.ID';

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
		if ( is_array( $arguments['taxonomy'] ) ) {
			$taxonomies = $arguments['taxonomy'];
		} else {
			$taxonomies = array( $arguments['taxonomy'] );
		}

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				$error = new WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy', 'media-library-assistant' ), $taxonomy );
				return $error;
			}
		}

		$clause_parameters = array();
		$placeholders = array();
		foreach ($taxonomies as $taxonomy) {
		    $placeholders[] = '%s';
			$clause_parameters[] = $taxonomy;
		}

		$clause = array( 'tt.taxonomy IN (' . join( ',', $placeholders ) . ')' );

		/*
		 * The "ids" parameter can build an item-specific cloud.
		 * Compile a list of all the terms assigned to the items.
		 */
		if ( ! empty( $arguments['ids'] ) ) {
			$ids = wp_parse_id_list( $arguments['ids'] );
		    $placeholders = implode( "','", $ids );
			$clause[] = "AND tr.object_id IN ( '{$placeholders}' )";

			$includes = array();
			foreach ( $ids as $id ) {
				foreach ($taxonomies as $taxonomy) {
					$terms = get_the_terms( $id, $taxonomy );
					if ( is_array( $terms ) ) {
						foreach( $terms as $term ) {
							$includes[ $term->term_id ] = $term->term_id;
						} // terms
					}
				} // taxonomies
			} // ids

			// Apply a non-empty argument before we replace it.
			if ( ! empty( $arguments['include'] ) ) {
				$includes = array_intersect( $includes, wp_parse_id_list( $arguments['include'] ) );
			}

			// If there are no terms we want an empty cloud
			if ( empty( $includes ) ) {
				$arguments['include'] = (string) 0x7FFFFFFF;
			} else {
				ksort( $includes );
				$arguments['include'] = implode( ',', $includes );
			}
		}

		// Add include/exclude and parent constraints to WHERE cluse
		if ( ! empty( $arguments['include'] ) ) {
		    $placeholders = implode( "','", wp_parse_id_list( $arguments['include'] ) );
			$clause[] = "AND t.term_id IN ( '{$placeholders}' )";
		} elseif ( ! empty( $arguments['exclude'] ) ) {
		    $placeholders = implode( "','", wp_parse_id_list( $arguments['exclude'] ) );
			$clause[] = "AND t.term_id NOT IN ( '{$placeholders}' )";
		}

		if ( '' !== $arguments['parent'] ) {
			$parent = (int) $arguments['parent'];
			$clause[] = "AND tt.parent = '{$parent}'";
		}

		if ( 'all' === strtolower( $arguments['post_mime_type'] ) ) {
			$post_mimes = '';
		} else {
			$post_mimes = wp_post_mime_type_where( $arguments['post_mime_type'], 'p' );
			$where = str_replace( '%', '%%', $post_mimes );

			if ( 0 == absint( $arguments['minimum'] ) ) {
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
		if ( $no_orderby = 'true' == (string) $arguments['no_orderby'] ) {
			$arguments['orderby'] = 'count';
			$arguments['order']  = 'DESC';
		}

		// Add sort order
		if ( 'none' !== strtolower( $arguments['orderby'] ) ) {
			if ( ( 'tax_position' === strtolower( $arguments['orderby'] ) ) && class_exists( 'Yikes_Custom_Taxonomy_Order', false ) ) {
				// Support Simple Taxonomy Ordering plugin
				$yikes_custom_taxonomy_order = Yikes_Custom_Taxonomy_Order::get_instance();
				$clauses = $yikes_custom_taxonomy_order->set_tax_order( $clauses, $taxonomies, array() );
				// Adjust the orderby clause to account for the subquery and the alias in the fields[] clause
				$clauses['orderby'] = 'ORDER BY CAST( tax_position AS UNSIGNED )';
			} elseif ( ( 'term_order' === strtolower( $arguments['orderby'] ) ) && class_exists( 'SCPO_Engine', false ) ) {
				// Support Simple Custom Post Order plugin
				$clauses['orderby'] = 'ORDER BY term_order';
			} else {
				if ( 'true' == strtolower( $arguments['preserve_case'] ) ) {
					$binary_keys = array( 'name', 'slug', );
				} else {
					$binary_keys = array();
				}

				$allowed_keys = array(
					'empty_orderby_default' => 'name',
					'count' => 'count',
					'id' => 'term_id',
					'name' => 'name',
					'random' => 'RAND()',
					'slug' => 'slug',
				);

				$clauses['orderby'] = 'ORDER BY ' . self::mla_validate_sql_orderby( $arguments, '', $allowed_keys, $binary_keys );
			} // not tax_position or term_order
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

		$clauses = apply_filters( 'mla_get_terms_clauses', $clauses );

		// Build the final query
		$query = array( 'SELECT' );
		$query[] = $clauses['fields'];
		$query[] = 'FROM `' . $wpdb->terms . '` AS t';
		$query[] = $clauses['join'];
		$query[] = 'WHERE (';
		$query[] = $clauses['where'];
		$query[] = ') GROUP BY tt.term_taxonomy_id';

		$clause_parameters = absint( $arguments['minimum'] );
		if ( 0 < $clause_parameters ) {
			$query[] = "HAVING count >= {$clause_parameters}";
		}

		/*
		 * Unless specifically told to omit the ORDER BY clause or the COUNT,
		 * supply a sort order for the initial/inner query only
		 */
		if ( ! ( $no_orderby || $no_count ) ) {
			$query[] = 'ORDER BY count DESC, t.term_id ASC';
		}

		// Limit the total number of terms returned
		$terms_limit = absint( $arguments['number'] );
		if ( 0 < $terms_limit ) {
			$query[] = "LIMIT {$terms_limit}";
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
		$tags = $wpdb->get_results(	$query ); // phpcs:ignore
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

		if ( 'true' == strtolower( trim( $arguments['pad_counts'] ) ) ) {
			self::_pad_term_counts( $tags, reset( $taxonomies ), $post_types, $post_stati, $post_mimes );
		}

		$tags['found_rows'] = $found_rows;
		$tags = apply_filters( 'mla_get_terms_query_results', $tags );
		return $tags;
	} // mla_get_terms

	/**
	 * Add count of children to parent count.
	 *
	 * Recalculates term counts by including items from child terms. Assumes all
	 * relevant children are already in the $terms argument.
	 *
	 * @since 3.13
	 *
	 * @param	array	Array of Term objects, by reference
	 * @param	string	Term Context
	 * @param	array	Qualifying post type value(s)
	 * @param	array	Qualifying post status value(s)
	 * @param	string	Qualifying post MIME type clause
	 * @return	null	Will break from function if conditions are not met.
	 */
	private static function _pad_term_counts( &$terms, $taxonomy, $post_types = NULL, $post_stati = NULL, $post_mimes = '' ) {
		global $wpdb;

		// This function only works for hierarchical taxonomies like post categories.
		if ( !is_taxonomy_hierarchical( $taxonomy ) ) {
			return;
		}

		$terms_by_id = array(); // key term_id, value = reference to term object
		$term_ids = array(); // key term_taxonomy_id, value = term_id
		$term_items = array(); // key term_id, value = array( object_id => reference_count )

		foreach ( $terms as $key => $term ) {
			if ( is_integer( $key ) ) {
				$terms_by_id[ $term->term_id ] = & $terms[ $key ];
				$term_ids[ $term->term_taxonomy_id ] = $term->term_id;
			}
		}

		if ( is_array( $post_stati ) ) {
			$post_stati = esc_sql( $post_stati );
		} else {
			$post_stati = array( 'inherit' );
		}

		if ( is_array( $post_types ) ) {
			$post_types = esc_sql( $post_types );
		} else {
			$tax_obj = get_taxonomy( $taxonomy );
			$post_types = esc_sql( $tax_obj->object_type );
		}

		// Get the object and term ids and stick them in a lookup table
		$results = $wpdb->get_results( "SELECT object_id, term_taxonomy_id FROM $wpdb->term_relationships INNER JOIN $wpdb->posts AS p ON object_id = ID WHERE term_taxonomy_id IN (" . implode( ',', array_keys($term_ids) ) . ") AND p.post_type IN ('" . implode( "', '", $post_types ) . "') AND p.post_status in ( '" . implode( "', '", $post_stati ) . "' )" . $post_mimes ); // phpcs:ignore
		foreach ( $results as $row ) {
			$id = $term_ids[ $row->term_taxonomy_id ];
			$term_items[ $id ][ $row->object_id ] = isset( $term_items[ $id ][ $row->object_id ] ) ? ++$term_items[ $id ][ $row->object_id ] : 1;
		}

		// Touch every ancestor's lookup row for each post in each term
		foreach ( $term_ids as $term_id ) {
			$child = $term_id;
			while ( ! empty( $terms_by_id[ $child] ) && $parent = $terms_by_id[ $child ]->parent ) {
				if ( ! empty( $term_items[ $term_id ] ) ) {
					foreach ( $term_items[ $term_id ] as $item_id => $touches ) {
						$term_items[ $parent ][ $item_id ] = isset( $term_items[ $parent ][ $item_id ] ) ? ++$term_items[ $parent ][ $item_id ]: 1;
					}
				}

				$child = $parent;
			}
		}

		// Transfer the touched cells, updating $terms by reference
		foreach ( (array) $term_items as $id => $items ) {
			if ( isset( $terms_by_id[ $id ] ) ) {
				$terms_by_id[ $id ]->term_count = (integer) $terms_by_id[ $id ]->count;
				$terms_by_id[ $id ]->count = count( $items );
			}
		}
	} // _pad_term_counts
} // Class MLAShortcode_Support
?>