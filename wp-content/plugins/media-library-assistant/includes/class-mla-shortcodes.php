<?php
/**
 * Media Library Assistant Shortcode interface functions
 *
 * @package Media Library Assistant
 * @since 0.1
 */

/**
 * Class MLA (Media Library Assistant) Shortcodes defines the shortcodes available
 * to MLA users and loads the support class if the shortcodes are executed.
 *
 * @package Media Library Assistant
 * @since 0.20
 */
class MLAShortcodes {
	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 0.20
	 *
	 * @return	void
	 */
	public static function initialize() {
		global $sitepress, $polylang;

		/*
		 * Check for WPML/Polylang presence before loading language support class,
		 * then immediately initialize it since we're already in the "init" action.
		 */
		if ( is_object( $sitepress ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-wpml-shortcode-support.php';
			MLA_WPML_Shortcodes::initialize();
		} elseif ( is_object( $polylang ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-polylang-shortcode-support.php';
			MLA_Polylang_Shortcodes::initialize();
		}

		add_shortcode( 'mla_gallery', 'MLAShortcodes::mla_gallery_shortcode' );
		add_shortcode( 'mla_tag_cloud', 'MLAShortcodes::mla_tag_cloud_shortcode' );
		add_shortcode( 'mla_term_list', 'MLAShortcodes::mla_term_list_shortcode' );
		add_shortcode( 'mla_custom_list', 'MLAShortcodes::mla_custom_list_shortcode' );

		// Avoid wptexturize defect.
		if ( version_compare( get_bloginfo( 'version' ), '4.0', '>=' ) ) {
			add_filter( 'no_texturize_shortcodes', 'MLAShortcodes::mla_no_texturize_shortcodes_filter', 10, 1 );
		}
	}

	/**
	 * Prevents wptexturizing of the [mla_gallery] shortcode, avoiding a bug in WP 4.0.
	 *
	 * Defined as public because it's a filter.
	 *
	 * @since 1.94
	 *
	 * @param	array	$no_texturize_shortcodes list of "do not texturize" shortcodes
	 *
	 * @return	array	updated list of "do not texturize" shortcodes
	 */
	public static function mla_no_texturize_shortcodes_filter( $no_texturize_shortcodes ) {
		if ( ! in_array( 'mla_gallery', $no_texturize_shortcodes, true ) ) {
			$no_texturize_shortcodes[] = 'mla_gallery';
			$no_texturize_shortcodes[] = 'mla_tag_cloud';
			$no_texturize_shortcodes[] = 'mla_term_list';
			$no_texturize_shortcodes[] = 'mla_custom_list';
		}

		return $no_texturize_shortcodes;
	}

	/**
	 * MLA Gallery shortcode attribute validation.
	 *
	 * Compatibility shim for MLAShortcode_Support::mla_validate_attributes
	 *
	 * @since 2.95
	 *
	 * @param	mixed  $attr Array or string containing shortcode attributes.
	 * @param	string $content Optional content for enclosing shortcodes.
	 *
	 * @return	array	clean attributes array
	 */
	public static function mla_validate_attributes( $attr, $content = NULL ) {
		if ( !class_exists( 'MLAShortcode_Support' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-support.php';
		}
		
		return MLAShortcode_Support::mla_validate_attributes( $attr, $content );
	}

	/**
	 * The MLA Gallery shortcode.
	 *
	 * Compatibility shim for MLAShortcode_Support::mla_gallery_shortcode
	 *
	 * @since 0.50
	 *
	 * @param array $attr Attributes of the shortcode
	 * @param string $content Optional content for enclosing shortcodes; used with mla_alt_shortcode
	 *
	 * @return string HTML content to display gallery.
	 */
	public static function mla_gallery_shortcode( $attr, $content = NULL ) {
		if ( !class_exists( 'MLAShortcode_Support' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-support.php';
		}
		
		return MLAShortcode_Support::mla_gallery_shortcode( $attr, $content );
	}

	/**
	 * The MLA Tag Cloud shortcode.
	 *
	 * Compatibility shim for MLAShortcode_Support::mla_tag_cloud_shortcode
	 *
	 * @since 1.60
	 *
	 * @param array $attr Attributes of the shortcode.
	 * @param string $content Optional content for enclosing shortcodes
	 *
	 * @return string HTML content to display the tag cloud.
	 */
	public static function mla_tag_cloud_shortcode( $attr, $content = NULL ) {
		if ( !class_exists( 'MLAShortcode_Support' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-support.php';
		}

		//return MLAShortcode_Support::mla_tag_cloud_shortcode( $attr, $content );

		if ( !class_exists( 'MLATagCloud' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-tag-cloud.php';
		}

		return MLATagCloud::mla_tag_cloud_shortcode( $attr, $content );
	}

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
		if ( !class_exists( 'MLAShortcode_Support' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-support.php';
		}

		//return MLAShortcode_Support::mla_tag_cloud( $attr );

		if ( !class_exists( 'MLATagCloud' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-tag-cloud.php';
		}

		return MLATagCloud::mla_tag_cloud( $attr );
	}

	/**
	 * The MLA Term List shortcode.
	 *
	 * Compatibility shim for MLAShortcode_Support::mla_term_list_shortcode
	 *
	 * @since 2.25
	 *
	 * @param array $attr Attributes of the shortcode.
	 * @param string $content Optional content for enclosing shortcodes
	 *
	 * @return string HTML content to display the term list.
	 */
	public static function mla_term_list_shortcode( $attr, $content = NULL ) {
		if ( !class_exists( 'MLAShortcode_Support' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-support.php';
		}

		if ( !class_exists( 'MLATermList' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-term-list.php';
		}

		return MLATermList::mla_term_list_shortcode( $attr, $content );
	}

	/**
	 * The MLA Term List support function.
	 *
	 * This is an alternative to the WordPress wp_list_categories, wp_dropdown_categories
	 * and wp_terms_checklist functions, with additional options to customize the hyperlink
	 * behind each term.
	 *
	 * @since 2.25
	 *
	 * @param array $attr Attributes of the shortcode.
	 *
	 * @return void|string|string[] Void if 'echo' attribute is true, or on failure. Otherwise, term list markup as a string or an array of links, depending on 'mla_output' attribute.
	 */
	public static function mla_term_list( $attr ) {
		if ( !class_exists( 'MLAShortcode_Support' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-support.php';
		}

		if ( !class_exists( 'MLATermList' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-term-list.php';
		}

		return MLATermList::mla_term_list( $attr );
	}

	/**
	 * The MLA Custom Field List shortcode.
	 *
	 * Compatibility shim for MLACustomList::mla_custom_list_shortcode
	 *
	 * @since 3.11
	 *
	 * @param array $attr Attributes of the shortcode.
	 * @param string $content Optional content for enclosing shortcodes
	 *
	 * @return string HTML content to display the custom fiekd list.
	 */
	public static function mla_custom_list_shortcode( $attr, $content = NULL ) {
		if ( !class_exists( 'MLAShortcode_Support' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-support.php';
		}

		if ( !class_exists( 'MLACustomList' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-custom-list.php';
		}

		return MLACustomList::mla_custom_list_shortcode( $attr, $content );
	}

	/**
	 * The MLA Custom Field List support function.
	 *
	 * This is a variation on the [mla_tag_cloud] and [mla_term_list] shortcodes, composing
	 * a cloud, list or dropdown ontrol based on custom field values.
	 *
	 * @since 3.11
	 *
	 * @param array $attr Attributes of the shortcode.
	 *
	 * @return void|string|string[] Void if 'echo' attribute is true, or on failure. Otherwise, term list markup as a string or an array of links, depending on 'mla_output' attribute.
	 */
	public static function mla_custom_list( $attr ) {
		if ( !class_exists( 'MLAShortcode_Support' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-support.php';
		}

		if ( !class_exists( 'MLACustomList' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-custom-list.php';
		}

		return MLACustomList::mla_custom_list( $attr );
	}

	/**
	 * The WP_Query object used to select items for the gallery.
	 *
	 * Defined as a public, static variable so it can be inspected from the
	 * "mla_gallery_wp_query_object" action. Set to NULL at all other times.
	 *
	 * @since 1.51
	 *
	 * @var	object
	 */
	public static $mla_gallery_wp_query_object = NULL;

	/**
	 * Parses shortcode parameters and returns the gallery objects
	 *
	 * Compatibility shim for MLAShortcode_Support::mla_get_shortcode_attachments
	 *
	 * @since 0.50
	 *
	 * @param int ID of the post/page in which the shortcode appears; zero (0) if none
	 * @param array Attributes of the shortcode
	 * @param boolean Optional; true to calculate and return ['found_posts'] as an array element
	 * @param boolean Optional; true activate debug logging, false to suppress it.
	 *
	 * @return array WP_Post[]|int[] Array of post objects or post IDs.
	 */
	public static function mla_get_shortcode_attachments( $post_parent, $attr, $return_found_rows = NULL, $overide_debug = NULL ) {
		if ( !class_exists( 'MLAShortcode_Support' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-support.php';
		}

		return MLAShortcode_Support::mla_get_shortcode_attachments( $post_parent, $attr, $return_found_rows, $overide_debug );
	}

	/**
	 * Retrieve the terms in one or more taxonomies
	 *
	 * Compatibility shim for MLAShortcode_Support::mla_get_terms
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
	 * @since 1.60
	 *
	 * @param	array	taxonomies to search and query parameters
	 *
	 * @return	array	array of term objects, empty if none found
	 */
	public static function mla_get_terms( $attr ) {
		if ( !class_exists( 'MLAShortcode_Support' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-support.php';
		}

		return MLAShortcode_Support::mla_get_terms( $attr );
	}

	/**
	 * Get IPTC/EXIF/WP or custom field mapping data source; front end posts/pages mode
	 *
	 * Compatibility shim for MLAData_Source::mla_get_data_source.
	 *
	 * @since 1.70
	 *
	 * @param	integer	post->ID of attachment
	 * @param	string 	category/scope to evaluate against: custom_field_mapping or single_attachment_mapping
	 * @param	array	data source specification ( data_source, qualifier, meta_name, keep_existing, format, option )
	 * @param	array 	(optional) _wp_attachment_metadata, default NULL (use current postmeta database value)
	 *
	 * @return	string|array	data source value
	 */
	public static function mla_get_data_source( $post_id, $category, $data_value, $attachment_metadata = NULL ) {
		if ( !class_exists( 'MLAData_Source' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-data-source.php';
		}

		return MLAData_Source::mla_get_data_source( $post_id, $category, $data_value, $attachment_metadata );
	} // mla_get_data_source

	/**
	 * Identify custom field mapping data source; front end posts/pages mode
	 *
	 * Compatibility shim for MLAData_Source::mla_is_data_source.
	 *
	 * @since 1.80
	 *
	 * @param	string 	candidate data source name
	 *
	 * @return	boolean	true if candidate name matches a data source
	 */
	public static function mla_is_data_source( $candidate_name ) {
		if ( !class_exists( 'MLAData_Source' ) ) {
			require_once MLA_PLUGIN_PATH . 'includes/class-mla-data-source.php';
		}

		return MLAData_Source::mla_is_data_source( $candidate_name );
	}
} // Class MLAShortcodes
?>