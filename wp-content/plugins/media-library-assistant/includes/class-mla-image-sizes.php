<?php
/**
 * Media Library Assistant Image Size Support
 *
 * @package Media Library Assistant
 * @since 3.25
 */

/**
 * Class MLA (Media Library Assistant) Image filters WordPress Image Size functions and supports
 * the Images Settings tab
 *
 * @package Media Library Assistant
 * @since 3.25
 */
class MLAImage_Size {
	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 3.25
	 *
	 * @return	void
	 */
	public static function initialize() {
		self::mla_localize_default_image_size_columns();

		// Check for active debug setting
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'heartbeat' ) {
			self::$mla_debug_active = false;
		} else {
			self::$mla_debug_active = ( MLACore::$mla_debug_level & 1 ) && ( MLACore::$mla_debug_level & MLACore::MLA_DEBUG_CATEGORY_IMAGE_SIZE );
		}
		
		if ( 'checked' === MLACore::mla_get_option( MLACoreOptions::MLA_ENABLE_IMAGE_SIZES ) ) {
			add_filter( 'image_size_names_choose', 'MLAImage_Size::mla_setup_image_size_names' );
			self::mla_setup_image_sizes();
		}
	} // initialize

	/**
	 * Execute MLACore::mla_debug_add() statements to record debug information
	 *
	 * @since 3.25
	 *
	 * @var	boolean
	 */
	private static $mla_debug_active = false;

	/**
	 * Disable Image Size filtering during option initialization
	 *
	 * @since 3.25
	 *
	 * @var	boolean
	 */
	private static $disable_mla_filtering = false;

	/**
	 * Initialize custom image sizes
	 *
	 * Defined as public because it may be called as an action.
	 *
	 * @since 3.25
	 *
	 */
	public static function mla_setup_image_sizes() {
		static $hook_needed = true;
		
		// WordPress can't register sizes before the after_setup_theme action
		if ( ! did_action( 'after_setup_theme' ) ) {
			if ( $hook_needed ) {
				add_action( 'after_setup_theme', 'MLAImage_Size::mla_setup_image_sizes', 10 );
				$hook_needed = false;
			}
			
			return;
		}

		self::_get_image_size_templates( true );
		foreach( self::$mla_image_size_templates as $slug => $value ) {
			if ( $value['source'] === 'custom' && $value['disabled'] === false ) {
				if ( $crop = $value['crop'] ) {
					// Look for non-default crop position
					if ( in_array( $value['horizontal'], array( 'left', 'right' ) ) ) {
						$horizontal = $value['horizontal'];
					} else {
						$horizontal = 'center';
					}

					if ( in_array( $value['vertical'], array( 'top', 'bottom' ) ) ) {
						$vertical = $value['vertical'];
					} else {
						$vertical = 'center';
					}
					
					if ( ( 'center' !== $horizontal ) || ( 'center' !== $vertical ) ) {
						$crop = array ( $horizontal, $vertical );
					}
				}

				// The delete action is processed after this filter is executed
				if ( isset( $_REQUEST['mla_admin_action'] ) && ( 'single_item_delete' === $_REQUEST['mla_admin_action'] ) ) {
					continue;
				} else {
					add_image_size( $slug, absint( $value['width'] ), absint( $value['height'] ), $crop );
				}

				//Update WordPress reserved sizes
				if ( in_array( $slug, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
					update_option( $slug . '_size_w', absint( $value['width'] ) );
					update_option( $slug . '_size_h', absint( $value['height'] ) );

					if ( ! empty( $crop ) ) {
						// WordPress does not allow arrays in its crop settings
						update_option( $slug . '_crop', 1 );
					} else {
						update_option( $slug . '_crop', '' );
					}
				}
			} // source === custom
		}
	} // mla_setup_image_sizes

	/**
	 * Initialize the custom Image Size Names
	 *
	 * Defined as public because it's a filter.
	 *
	 * @since 3.25
	 *
	 * @param	array	Image Size names, keyed by the size slug
	 */
	public static function mla_setup_image_size_names( $size_names ) {
		if ( self::$disable_mla_filtering ) {
			return $size_names;
		}

		self::_get_image_size_templates( true );
		foreach( self::$mla_image_size_templates as $slug => $value ) {
			if ( $value['source'] === 'custom' && ! empty( $value['name'] ) ) {
				$size_names[ $slug ] = $value['name'];
			}
		}

		return $size_names;
	} // mla_setup_image_size_names

	/**
	 * Table column definitions, Settings/Images tab table
	 *
	 * This array defines table columns and titles where the key is the column slug
	 * (and class) and the value is the column's title text.
	 * 
	 * All of the columns are added to this array by 
	 * MLAImage_Size::_localize_default_image_size_columns.
	 *
	 * @since 3.25
	 *
	 * @var	array
	 */
	public static $default_image_size_columns = array();

	/**
	 * Sortable column definitions, Settings/Images tab table
	 *
	 * This array defines the table columns that can be sorted. The array key
	 * is the column slug that needs to be sortable, and the value is database column
	 * to sort by. Often, the key and value will be the same, but this is not always
	 * the case (as the value is a column name from the database, not the list table).
	 *
	 * The array value also contains a boolean which is 'true' if the initial sort order
	 * for the column is DESC/Descending.
	 *
	 * @since 3.25
	 *
	 * @var	array
	 */
	public static $default_sortable_image_size_columns = array(
		'slug' => array('slug',false),
		'name' => array('name',false),
		'width' => array('width',false),
		'height' => array('height',false),
		'crop' => array('crop',true),
		'horizontal' => array('horizontal',false),
		'vertical' => array('vertical',false),
		'status' => array('status',false),
		'description' => array('description',false),
		'source' => array('source',false),
        );

	/**
	 * Default values for hidden columns
	 *
	 * This array is used when the user-level option is not set, i.e.,
	 * the user has not altered the selection of hidden columns.
	 *
	 * The value on the right-hand side must match the column slug, e.g.,
	 * array(0 => 'ID_parent, 1 => 'title_name').
	 * 
	 * @since 3.25
	 *
	 * @var	array
	 */
	public static $default_hidden_image_size_columns = array(
		// 'slug',
		// 'name',
		// 'width',
		// 'height',
		// 'crop',
		// 'horizontal',
		// 'vertical',
		// 'status',
		'description',
		'source',
	);

	/**
	 * Builds the $default_image_size_columns array with translated source texts.
	 *
	 * @since 3.25
	 *
	 * @return	void
	 */
	public static function mla_localize_default_image_size_columns( ) {
		static $hook_needed = true;
		
		// WordPress can't call _load_textdomain_just_in_time() before the init action
		if ( ! did_action( 'init' ) ) {
			if ( $hook_needed ) {
				add_action( 'init', 'MLAImage_Size::mla_localize_default_image_size_columns', 10 );
				$hook_needed = false;
			}
			
			return;
		}

		// Build the default columns array at runtime to accomodate calls to the localization functions
		self::$default_image_size_columns = array(
			'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
			'slug' => _x( 'Slug', 'list_table_column', 'media-library-assistant' ),
			'name' => _x( 'Name', 'list_table_column', 'media-library-assistant' ),
			'width' => _x( 'Width', 'list_table_column', 'media-library-assistant' ),
			'height'  => _x( 'Height', 'list_table_column', 'media-library-assistant' ),
			'crop' => _x( 'Crop', 'list_table_column', 'media-library-assistant' ),
			'horizontal' => _x( 'Horizontal Position', 'list_table_column', 'media-library-assistant' ),
			'vertical' => _x( 'Vertical Position', 'list_table_column', 'media-library-assistant' ),
			'status'  => _x( 'Status', 'list_table_column', 'media-library-assistant' ),
			'description' => _x( 'Description', 'list_table_column', 'media-library-assistant' ),
			'source' => _x( 'Source', 'list_table_column', 'media-library-assistant' ),
		);
	}

	/**
	 * Return the names and orderby values of the sortable columns
	 *
	 * @since 3.25
	 *
	 * @return	array	column_slug => array( orderby value, initial_descending_sort ) for sortable columns
	 */
	private static function _get_sortable_image_size_columns( ) {
		$results = array();
		foreach ( self::$default_sortable_image_size_columns as $key => $value ) {
			$results[ $value[0] ] = $value[0];
		}
		
		return $results;
	}

	/**
	 * Sanitize and expand query arguments from request variables
	 *
	 * @since 3.25
	 *
	 * @param	array	query parameters from web page, usually found in $_REQUEST
	 * @param	int		Optional number of rows (default 0) to skip over to reach desired page
	 * @param	int		Optional number of rows on each page (0 = all rows, default)
	 *
	 * @return	array	revised arguments suitable for query
	 */
	private static function _prepare_image_size_items_query( $raw_request, $offset = 0, $count = 0 ) {
		/*
		 * Go through the $raw_request, take only the arguments that are used in the query and
		 * sanitize or validate them.
		 */
		if ( ! is_array( $raw_request ) ) {
			/* translators: 1: ERROR tag 2: function name 3: non-array value */
			MLACore::mla_debug_add( sprintf( _x( '%1$s: %2$s non-array "%3$s"', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), 'MLAImage_Size::_prepare_image_size_items_query', var_export( $raw_request, true ) ), MLACore::MLA_DEBUG_CATEGORY_ANY );
			return NULL;
		}

		$clean_request = array (
			's' => '',
			'mla_image_view' => 'all',
			'mla_image_status' => 'any',
			'source' => '',
			'orderby' => 'slug',
			'order' => 'ASC',
		);

		foreach ( $raw_request as $key => $value ) {
			switch ( $key ) {
				// ['s'] - Search Sizes by one or more keywords
				case 's':
					$clean_request[ $key ] = sanitize_text_field( stripslashes( trim( $value ) ) );
					break;
				case 'mla_image_view':
				case 'mla_image_status':
				case 'source':
					$clean_request[ $key ] = sanitize_title( strtolower( $value ) );
					break;
				case 'orderby':
					if ( 'none' === $value ) {
						$clean_request[ $key ] = $value;
					} else {
						if ( array_key_exists( $value, self::_get_sortable_image_size_columns() ) ) {
							$clean_request[ $key ] = $value;
						}
					}
					break;
				case 'order':
					switch ( $value = trim( strtoupper ( $value ) ) ){
						case 'ASC':
						case 'DESC':
							$clean_request[ $key ] = $value;
							break;
						default:
							$clean_request[ $key ] = 'ASC';
					}
					break;
				default:
					// ignore anything else in $raw_request
			} // switch $key
		} // foreach $raw_request

		// Ignore incoming paged value; use offset and count instead
		if ( ( (int) $count ) > 0 ) {
			$clean_request['offset'] = $offset;
			$clean_request['posts_per_page'] = $count;
		}

		return $clean_request;
	}

	/**
	 * Add filters, run query, remove filters
	 *
	 * @since 3.25
	 *
	 * @param	array	query parameters from web page, usually found in $_REQUEST
	 *
	 * @return	array	query results; array of MLA post_mime_type objects
	 */
	private static function _execute_image_size_items_query( $request ) {
		if ( ! self::_get_image_size_templates() ) {
			return array ();
		}

		// Sort and filter the list
		$keyword = isset( $request['s'] ) ? $request['s'] : '';
		$view = isset( $request['mla_image_view'] ) ? $request['mla_image_view'] : 'all';
		$status = isset( $request['mla_image_status'] ) ? $request['mla_image_status'] : 'any';
		$source = isset( $request['source'] ) ? $request['source'] : '';
		$index = 0;
		$sorted_types = array();

		foreach ( self::$mla_image_size_templates as $slug => $value ) {
			$index++;
			
			if ( ! empty( $keyword ) ) {
				$found  = false !== stripos( $slug, $keyword );
				$found |= false !== stripos( $value['name'], $keyword );
				$found |= false !== stripos( $value['width'], $keyword );
				$found |= false !== stripos( $value['height'], $keyword );
				$found |= false !== stripos( $value['horizontal'], $keyword );
				$found |= false !== stripos( $value['vertical'], $keyword );
				$found |= false !== stripos( $value['description'], $keyword );
				$found |= false !== stripos( $value['source'], $keyword );
			
				if ( ! $found ) {
					continue;
				}
			}

			switch( $view ) {
				case 'core':
				case 'other':
				case 'custom':
					$found = $view === $value['source'];
					break;
				default:
					$found = true;
			}// $view

			if ( ! $found ) {
				continue;
			}

			switch( $status ) {
				case 'active':
					$found = ! $value['disabled'];
					break;
				case 'inactive':
					$found = $value['disabled'];
					break;
				default:
					$found = true;
			}// $status

			if ( ! $found ) {
				continue;
			}

			if ( ! empty( $source ) && ( $source !== $value['source'] ) ) {
				continue;
			}

			$value['slug'] = $slug;
			$value['post_ID'] = $index;
			$suffix = str_pad( (string) $index, 5, '0', STR_PAD_LEFT );
			switch ( $request['orderby'] ) {
				case 'slug':
					$sorted_types[ $slug ] = (object) $value;
					break;
				case 'name':
					$sorted_types[ ( empty( $value['name'] ) ? chr(1) : $value['name'] ) . $suffix ] = (object) $value;
					break;
				case 'width':
					$sorted_types[ empty( $value['width'] ) ? $index : ( 100 * $value['width'] ) + $index ] = (object) $value;
					break;
				case 'height':
					$sorted_types[ empty( $value['height'] ) ? $index : ( 100 * $value['height'] ) + $index ] = (object) $value;
					break;
				case 'crop':
					$sorted_types[ ( $value['crop'] ? 'yes' : 'no' ) . $suffix ] = (object) $value;
					break;
				case 'horizontal':
					$sorted_types[ ( empty( $value['horizontal'] ) ? 'center' : $value['horizontal'] ) . $suffix ] = (object) $value;
					break;
				case 'vertical':
					$sorted_types[ ( empty( $value['vertical'] ) ? 'center' : $value['vertical'] ) . $suffix ] = (object) $value;
					break;
				case 'status':
					$sorted_types[ ( $value['disabled'] ? 'inactive' : 'active' ) . $suffix ] = (object) $value;
					break;
				case 'description':
					$sorted_types[ ( empty( $value['description'] ) ? chr(1) : $value['description'] ) . $suffix ] = (object) $value;
					break;
				case 'source':
					$sorted_types[ ( empty( $value['source'] ) ? chr(1) : $value['source'] ) . $suffix ] = (object) $value;
					break;
				default:
					$sorted_types[ $slug ] = (object) $value;
					break;
			} //orderby
		}
		ksort( $sorted_types );

		if ( 'DESC' === $request['order'] ) {
			$sorted_types = array_reverse( $sorted_types, true );
		}
		// Paginate the sorted list
		$results = array();
		$offset = isset( $request['offset'] ) ? absint( $request['offset'] ) : 0;
		$count = isset( $request['posts_per_page'] ) ? absint( $request['posts_per_page'] ) : -1;
		foreach ( $sorted_types as $value ) {
			if ( $offset ) {
				$offset--;
			} elseif ( $count-- ) {
				$results[] = $value;
			} else {
				break;
			}
		}

		return $results;
	}

	/**
	 * Get the total number of MLA post_mime_type objects
	 *
	 * @since 3.25
	 *
	 * @param	array	Query variables, e.g., from $_REQUEST
	 *
	 * @return	integer	Number of MLA post_mime_type objects
	 */
	public static function mla_count_image_size_items( $request ) {
		$request = self::_prepare_image_size_items_query( $request );
		$results = self::_execute_image_size_items_query( $request );
		return count( $results );
	}

	/**
	 * Retrieve MLA post_mime_type objects for list table display
	 *
	 * @since 3.25
	 *
	 * @param	array	query parameters from web page, usually found in $_REQUEST
	 * @param	int		number of rows to skip over to reach desired page
	 * @param	int		number of rows on each page
	 *
	 * @return	array	MLA post_mime_type objects
	 */
	public static function mla_query_image_size_items( $request, $offset = NULL, $count = NULL ) {
		$request = self::_prepare_image_size_items_query( $request, $offset, $count );
		$results = self::_execute_image_size_items_query( $request );
		return $results;
	}

	/**
	 * In-memory representation of the Intermediate Image Sizes
	 *
	 * @since 3.25
	 *
	 * @var array $mla_image_size_templates
	 * @phpstan-var	array{
	 * 		slug: array{
	 * 			name: string
	 *			width: integer,
	 *			height: integer,
	 *			crop: boolean,
	 *			horizontal: ''|'left'|'center'|'right',
	 *			vertical: ''|'top'|'center'|'bottom',
	 *			disabled: boolean,
	 *			description: string
	 *			source: 'core'|'other'|'custom'
	 *		}
	 * } $mla_image_size_templates
	 */
	private static $mla_image_size_templates = NULL;

	/**
	 * Highest existing Intermediate Image Size ID value
	 *
	 * @since 3.25
	 *
	 * @var	integer
	 */
	private static $mla_image_size_highest_ID = 0;

	/**
	 * Get information about available image sizes
	 * Adapted from wp_get_registered_image_subsizes
	 *
	 * @since 3.25
	 *
	 * @return	array $all_sizes
	 * @phpstan-var	array{
	 * 		slug: array{
	 *			name: string
	 *			width: integer,
	 *			height: integer,
	 *			crop: boolean,
	 *			horizontal: ''|'left'|'center'|'right',
	 *			vertical: ''|'top'|'center'|'bottom',
	 *			disabled: false,
	 *			description: ''
	 *			source: 'core'|'other'
	 *		}
	 * } $all_sizes
	 */
	public static function mla_get_registered_image_subsizes( $delete_slug = '') {
		self::$disable_mla_filtering = true;
		$custom_sizes = MLACore::mla_get_option( MLACoreOptions::MLA_IMAGE_SIZES, false, true );
		$original_settings = NULL;
		if ( isset( $custom_sizes[ $delete_slug ] ) ) {
			if ( isset( $custom_sizes[ $delete_slug ]['original_settings'] ) ) {
				$original_settings = $custom_sizes[ $delete_slug ]['original_settings'];
			}
			
			unset( $custom_sizes[ $delete_slug ] );
			MLACore::mla_update_option( MLACoreOptions::MLA_IMAGE_SIZES, $custom_sizes );
		}

		$additional_sizes = wp_get_additional_image_sizes();
		if ( isset( $additional_sizes[ $delete_slug ] ) ) {
			if ( ! empty( $original_settings ) && ( 'other' === $original_settings['source'] ) ) {
				$additional_sizes[ $delete_slug ]['width'] = $original_settings['width'];
				$additional_sizes[ $delete_slug ]['height'] = $original_settings['height'];
				$additional_sizes[ $delete_slug ]['crop'] = $original_settings['crop'];
			} else {
				unset( $additional_sizes[ $delete_slug ] );
			}
		}

		$all_sizes = array();

		/** This filter is documented in wp-admin/includes/media.php */
		$size_names = apply_filters( 'image_size_names_choose',
			array(
				'thumbnail'    => __( 'Thumbnail' ),
				'medium'       => __( 'Medium' ),
				'medium_large' => __( 'Medium Large' ),
				'large'        => __( 'Large' ),
				'full'         => __( 'Full Size' ),
			)
		);

		foreach ( get_intermediate_image_sizes() as $size_slug ) {
			$size_data = array(
				'name' => '',
				'width'  => 0,
				'height' => 0,
				'crop'   => false,
				'horizontal' => '',
				'vertical' => '',
				'disabled' => false,
				'description' => '',
				'source' => 'core',
			);

			if ( isset( $custom_sizes[ $size_slug ] ) ) {
				continue;
			}

			if ( isset( $size_names[ $size_slug ] ) ) {
				// For sizes added by WordPress, plugins and themes.
				$size_data['name'] = $size_names[ $size_slug ];
			}
	
			if ( isset( $additional_sizes[ $size_slug ]['width'] ) ) {
				// For sizes added by plugins and themes.
				$size_data['width'] = (int) $additional_sizes[ $size_slug ]['width'];
			} else {
				// For default sizes set in options.
				$size_data['width'] = (int) get_option( "{$size_slug}_size_w" );
			}
	
			if ( isset( $additional_sizes[ $size_slug ]['height'] ) ) {
				$size_data['height'] = (int) $additional_sizes[ $size_slug ]['height'];
			} else {
				$size_data['height'] = (int) get_option( "{$size_slug}_size_h" );
			}
	
			if ( empty( $size_data['width'] ) && empty( $size_data['height'] ) ) {
				// This size isn't set.
				continue;
			}
	
			if ( isset( $additional_sizes[ $size_slug ]['crop'] ) ) {
				$size_data['crop'] = $additional_sizes[ $size_slug ]['crop'];
			} else {
				$size_data['crop'] = get_option( "{$size_slug}_crop" );
			}
	
			if ( ! is_array( $size_data['crop'] ) || empty( $size_data['crop'] ) ) {
				$size_data['crop'] = (bool) $size_data['crop'];
			} elseif ( is_array( $size_data['crop'] ) ) {
				$size_data['horizontal'] = $size_data['crop'][0];
				$size_data['vertical'] = $size_data['crop'][1];
				$size_data['crop'] = true;
			}

			if ( isset( $additional_sizes[ $size_slug ] ) ) {
				$size_data['source'] = 'other';
			}

			$all_sizes[ $size_slug ] = $size_data;
		}
	
		self::$disable_mla_filtering = false;
		return $all_sizes;
	}

	/**
	 * Assemble the in-memory representation of the Intermediate Image Sizes 
	 *
	 * @since 3.25
	 *
	 * @param	boolean	Force a reload/recalculation of types
	 * @param	slug of deleted (custom) item
	 *
	 * @return	boolean	Success (true) or failure (false) of the operation
	 */
	private static function _get_image_size_templates( $force_refresh = false, $delete_slug = '' ) {
		if ( false === $force_refresh && NULL !== self::$mla_image_size_templates ) {
			return true;
		}

		// Find the existing core and other sizes
		$existing_sizes = self::mla_get_registered_image_subsizes( $delete_slug );
		if ( ! is_array( $existing_sizes ) ) {
			$existing_sizes = array ();
		}

		// Add the MLA custom sizes
		$custom_sizes = MLACore::mla_get_option( MLACoreOptions::MLA_IMAGE_SIZES, false, true );
		if ( is_array( $custom_sizes ) ) {
			if ( isset( $custom_sizes[ $delete_slug ] ) ) {
				unset( $custom_sizes[ $delete_slug ] );
			}

			$all_sizes = array_merge( $existing_sizes, $custom_sizes );
		} else {
			$all_sizes = $existing_sizes;
		}

		self::$mla_image_size_templates = array();
		self::$mla_image_size_highest_ID = 0;

		// Load and number the entries
		foreach ( $all_sizes as $slug => $value ) {
			self::$mla_image_size_templates[ $slug ] = $value;
			self::$mla_image_size_templates[ $slug ]['post_ID'] = ++self::$mla_image_size_highest_ID;
			}

		self::_put_image_size_templates();
		return true;
	}

	/**
	 * Store the custom entries of the Intermediate Image Sizes 
	 *
	 * @since 3.25
	 *
	 * @return	boolean	Success (true) or failure (false) of the operation
	 */
	private static function _put_image_size_templates() {
		// Find the existing core and other sizes
		$existing_sizes = self::mla_get_registered_image_subsizes();
		if ( ! is_array( $existing_sizes ) ) {
			$existing_sizes = array ();
		}

		$custom_sizes = array ();
		foreach ( self::$mla_image_size_templates as $slug => $value ) {
			unset( $value['post_ID'] );
			if ( isset ( $existing_sizes[ $slug ] ) && $value === $existing_sizes[ $slug ] ) {
				continue;
			}

			$custom_sizes[ $slug ] = $value;
		}

		if ( empty( $custom_sizes ) ) {
			MLACore::mla_delete_option( MLACoreOptions::MLA_IMAGE_SIZES );
		} else {
			MLACore::mla_update_option( MLACoreOptions::MLA_IMAGE_SIZES, $custom_sizes );
		}

		return true;
	}

	/**
	 * Add an MLA Image Size object
	 *
	 * @since 3.25
	 *
	 * @param	array	Query variables for a single object, including slug
	 *
	 * @return	array	Message(s) reflecting the results of the operation
	 */
	public static function mla_add_image_size( $request ) {
		if ( ! self::_get_image_size_templates() ) {
			self::$mla_image_size_templates = array ();
		}

		$messages = '';
		$errors = '';

		// Sanitize slug value
		$slug = sanitize_title( $request['slug'] );
		if ( $slug !== $request['slug'] ) {
			/* translators: 1: element name 2: bad_value 3: good_value */
			$messages .= sprintf( __( '<br>' . 'Changing %1$s "%2$s" to valid value "%3$s"', 'media-library-assistant' ), __( 'Slug', 'media-library-assistant' ), $request['slug'], $slug );
		}

		// Make sure new slug is unique
		if ( isset( self::$mla_image_size_templates[ $slug ] ) ) {
			/* translators: 1: ERROR tag 2: slug */
			$errors .= '<br>' . sprintf( __( '%1$s: Could not add Slug "%2$s"; value already exists', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), $slug );
		}

		if ( ! empty( $errors ) ) {
			return array(
				'message' => substr( $errors . $messages, 4),
				'body' => ''
			);
		}

		$new_size = array();
		$new_size['name'] = sanitize_text_field( $request['name'] );
		$new_size['width'] = absint( $request['width'] );
		$new_size['height'] = absint( $request['height'] );
		$new_size['horizontal'] = '';
		$new_size['vertical'] = '';
		
		if ( empty( $request['crop'] ) ) {
			$new_size['crop'] = false;
		} else {
			$new_size['crop'] = true;

			// Look for non-default crop position
			if ( in_array( $request['horizontal'], array( 'left', 'right' ) ) ) {
				$horizontal = $request['horizontal'];
			} else {
				$horizontal = 'center';
			}

			if ( in_array( $request['vertical'], array( 'top', 'bottom' ) ) ) {
				$vertical = $request['vertical'];
			} else {
				$vertical = 'center';
			}
			
			if ( ( 'center' !== $horizontal ) || ( 'center' !== $vertical ) ) {
				$new_size['horizontal'] = $horizontal;
				$new_size['vertical'] = $vertical;
			}
		} // crop

		$new_size['disabled'] = ! empty( $request['disabled'] );
		$new_size['description'] = sanitize_text_field( $request['description'] );
		$new_size['source'] = 'custom';
		$new_size['post_ID'] = ++self::$mla_image_size_highest_ID;

		self::$mla_image_size_templates[ $slug ] = $new_size;
		self::_put_image_size_templates();

		return array(
			/* translators: 1: slug */
			'message' => substr( $messages . '<br>' . sprintf( __( 'Edit size "%1$s"; added', 'media-library-assistant' ), $slug ), 4),
			'body' => ''
		);
	} // mla_add_image_size

	/**
	 * Update an MLA Image Size object
	 *
	 * @since 3.25
	 *
	 * @param	array	Query variables for new object values, including optional original_slug
	 *
	 * @return	array	Message(s) reflecting the results of the operation
	 */
	public static function mla_update_image_size( $request ) {
		if ( ! self::_get_image_size_templates() ) {
			self::$mla_image_size_templates = array ();
		}

		$messages = '';
		$errors = '';
		$original_settings = NULL;
		$slug = sanitize_title( $request['slug'] );
		$original_slug = isset( $request['original_slug'] ) ? $request['original_slug'] : $slug;
		unset( $request['original_slug'] );

		if ( isset( self::$mla_image_size_templates[ $original_slug ] ) ) {
			$original_size = self::$mla_image_size_templates[ $original_slug ];
			unset( $original_size['post_ID'] );

			// If changing a core size, preserve the original settings
			if ( isset( $original_size['original_settings'] ) && ( $slug === $original_slug ) ) {
				$original_settings = $original_size['original_settings'];
				unset( $original_size['original_settings'] );
			}
		} else {
			$original_size = array(
				'name'  => '',
				'width'  => 0,
				'height' => 0,
				'crop'   => false,
				'horizontal' => '',
				'vertical' => '',
				'disabled' => false,
				'description' => '',
				'source' => 'core',
			);
		}

		// Validate changed slug value
		if ( $slug !== $original_slug ) {
			if ( $slug !== $request['slug'] ) {
				/* translators: 1: element name 2: bad_value 3: good_value */
				$messages .= sprintf( __( '<br>' . 'Changing new %1$s "%2$s" to valid value "%3$s"', 'media-library-assistant' ), __( 'Slug', 'media-library-assistant' ), $request['slug'], $slug );
			}

			// Make sure new slug is unique
			if ( isset( self::$mla_image_size_templates[ $slug ] ) ) {
				/* translators: 1: ERROR tag 2: slug */
				$errors .= '<br>' . sprintf( __( '%1$s: Could not add Slug "%2$s"; value already exists', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), $slug );
			} else {
				/* translators: 1: element name 2: old_value 3: new_value */
				$messages .= sprintf( '<br>' . __( 'Changing %1$s from "%2$s" to "%3$s"', 'media-library-assistant' ), __( 'Slug', 'media-library-assistant' ), $original_slug, $slug );

			$original_settings = NULL;
			}
		}

		if ( ! empty( $errors ) ) {
			return array(
				'message' => substr( $errors . $messages, 4),
				'body' => ''
			);
		}

		$new_size = array();
		$new_size['name'] = sanitize_text_field( $request['name'] );
		$new_size['width'] = absint( $request['width'] );
		$new_size['height'] = absint( $request['height'] );
		$new_size['horizontal'] = '';
		$new_size['vertical'] = '';
		
		if ( empty( $request['crop'] ) ) {
			$new_size['crop'] = false;
		} else {
			$new_size['crop'] = true;

			// Look for non-default crop position
			if ( in_array( $request['horizontal'], array( 'left', 'right' ) ) ) {
				$horizontal = $request['horizontal'];
			} else {
				$horizontal = 'center';
			}

			if ( in_array( $request['vertical'], array( 'top', 'bottom' ) ) ) {
				$vertical = $request['vertical'];
			} else {
				$vertical = 'center';
			}
			
			if ( ( 'center' !== $horizontal ) || ( 'center' !== $vertical ) ) {
				$new_size['horizontal'] = $horizontal;
				$new_size['vertical'] = $vertical;
			}
		} // crop

		$new_size['disabled'] = ! empty( $request['disabled'] );
		$new_size['description'] = sanitize_text_field( $request['description'] );
		$new_size['source'] = 'custom';

		if ( ( $slug === $original_slug ) && ( $original_size === $new_size ) ) {
			return array(
				/* translators: 1: slug */
				'message' => substr( $messages . '<br>' . sprintf( __( 'Edit view "%1$s"; no changes detected', 'media-library-assistant' ), $slug ), 4),
				'body' => ''
			);
		}

		// Preserve the original source settings
		if ( ! empty( $original_settings ) ) {
			$new_size['original_settings'] = $original_settings;
		} elseif ( ( 'custom' !== $original_size['source'] )  && ( $slug === $original_slug ) ) {
			$new_size['original_settings'] = array (
				'width'  => $original_size['width'],
				'height' => $original_size['height'],
				'crop'   => $original_size['crop'],
				'source'   => $original_size['source'],
			);
		}

		self::$mla_image_size_templates[ $slug ] = $new_size;

		if ( $slug !== $original_slug ) {
			unset( self::$mla_image_size_templates[ $original_slug ] );
		}

		self::_put_image_size_templates();
		self::_get_image_size_templates( true );
		return array(
			/* translators: 1: slug */
			'message' => $messages = substr( $messages . '<br>' . sprintf( __( 'Edit view "%1$s"; updated', 'media-library-assistant' ), $slug ), 4),
			'body' => ''
		);
	} // mla_update_image_size(

	/**
	 * Retrieve an MLA Image Size slug given a post_ID
	 *
	 * @since 3.25
	 *
	 * @param	integer	MLA Image Size post_ID
	 *
	 * @return	mixed	string with slug of the requested object; false if object not found
	 */
	public static function mla_get_image_size_slug( $post_ID ) {
		if ( ! self::_get_image_size_templates() ) {
			self::$mla_image_size_templates = array ();
		}

		foreach ( self::$mla_image_size_templates as $slug => $value ) {
			if ( $post_ID === $value['post_ID'] ) {
				return $slug;
			}
		}

		return false;
	} // mla_get_image_size_slug

	/**
	 * Retrieve an MLA Image Size object
	 *
	 * @since 3.25
	 *
	 * @param	string	MLA Image Size slug
	 *
	 * @return	mixed	Array of elements, including slug, for the requested object; false if object not found
	 */
	public static function mla_get_image_size( $slug ) {
		if ( ! self::_get_image_size_templates() ) {
			self::$mla_image_size_templates = array ();
		}

		if ( isset( self::$mla_image_size_templates[ $slug ] ) ) {
			$matched_value = self::$mla_image_size_templates[ $slug ];
			$matched_value['slug'] = $slug;
			return $matched_value;
		}

		return false;
	} // mla_get_image_size

	/**
	 * Delete an MLA Image Size object
	 *
	 * @since 3.25
	 *
	 * @param	string	MLA Image Size slug
	 *
	 * @return	array	Message(s) reflecting the results of the operation
	 */
	public static function mla_delete_image_size( $slug ) {
		if ( ! self::_get_image_size_templates() ) {
			self::$mla_image_size_templates = array ();
		}

		if ( isset( self::$mla_image_size_templates[ $slug ] ) ) {
			// Restore original option settings for core sizes
			if ( ! empty( self::$mla_image_size_templates[ $slug ]['original_settings'] ) ) {
				$original_settings = self::$mla_image_size_templates[ $slug ]['original_settings'];

				if ( in_array( $slug, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
					update_option( $slug . '_size_w', absint( $original_settings['width'] ) );
					update_option( $slug . '_size_h', absint( $original_settings['height'] ) );

					if ( ! empty( $original_settings['crop'] ) ) {
						// WordPress does not allow arrays in its crop settings
						update_option( $slug . '_crop', 1 );
					} else {
						update_option( $slug . '_crop', '' );
					}
				}
			}

			self::_get_image_size_templates( true, $slug );
			if ( isset( self::$mla_image_size_templates[ $slug ] ) ) {
				return array(
					/* translators: 1: slug */
					'message' => sprintf( __( 'Image size "%1$s" reverted to standard', 'media-library-assistant' ), $slug ),
					'body' => ''
				);
			} else {
				return array(
					/* translators: 1: slug */
					'message' => sprintf( __( 'Image size "%1$s" deleted', 'media-library-assistant' ), $slug ),
					'body' => ''
				);
			}
		}

		return array(
			/* translators: 1: ERROR tag 2: slug */
			'message' => sprintf( __( '%1$s: Did not find image size "%2$s"', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), $slug ),
			'body' => ''
		);
	} // mla_delete_image_size

	/**
	 * Tabulate MLA Image Size objects by view for list table display
	 *
	 * @since 3.25
	 *
	 * @param	string	keyword search criterion, optional
	 *
	 * @return	array	( 'singular' label, 'plural' label, 'count' of items )
	 */
	public static function mla_tabulate_items( $s = '' ) {
		if ( empty( $s ) ) {
			$request = array();
		} else {
			$request = array( 's' => $s );
		}

		$items = self::mla_query_image_size_items( $request, 0, 0 );

		$image_items = array(
			'all' => array(
				'singular' => _x( 'All', 'table_image_size_singular', 'media-library-assistant' ),
				'plural' => _x( 'All', 'table_image_size_plural', 'media-library-assistant' ),
				'count' => 0 ),
			'core' => array(
				'singular' => _x( 'WordPress', 'table_image_size_singular', 'media-library-assistant' ),
				'plural' => _x( 'WordPress', 'table_image_size_plural', 'media-library-assistant' ),
				'count' => 0 ),
			'other' => array(
				'singular' => _x( 'Other Theme/Plugin', 'table_image_size_singular', 'media-library-assistant' ),
				'plural' => _x( 'Other Theme/Plugin', 'table_image_size_plural', 'media-library-assistant' ),
				'count' => 0 ),
			'custom' => array(
				'singular' => _x( 'Custom', 'table_image_size_singular', 'media-library-assistant' ),
				'plural' => _x( 'Custom', 'table_image_size_plural', 'media-library-assistant' ),
				'count' => 0 ),
		);

		foreach ( $items as $value ) {
			$image_items['all']['count']++;
			$image_items[ $value->source ]['count']++;
		}

		return $image_items;
	} // mla_tabulate_image_sizes
} //Class MLAImage_Size
?>