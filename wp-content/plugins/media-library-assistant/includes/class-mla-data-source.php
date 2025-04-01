<?php
/**
 * Manages access to data sources for custom field mapping and shortcode execution
 *
 * @package Media Library Assistant
 * @since 2.20
 */

/**
 * Class MLA (Media Library Assistant) Data Source manages data sources for
 * custom field mapping and shortcode execution
 *
 * @package Media Library Assistant
 * @since 2.20
 */
class MLAData_Source {
	/**
	 * Array of Data Source names for custom field mapping
	 *
	 * @since 2.20
	 *
	 * @var	array
	 */
	public static $custom_field_data_sources = array (
		'post_id',
		'post_author',
		'post_date',
		'post_date_gmt',
		'post_content',
		'post_title',
		'post_excerpt',
		'post_status',
		'comment_status',
		'ping_status',  
		'post_name',
		'post_modified',
		'post_modified_gmt',
		'post_content_filtered',
		'parent',
		'post_parent',
		'guid',
		'menu_order',
		'mime_type',
		'post_mime_type', 
		'comment_count',
		'alt_text',

		'site_url',
		'base_url',
		'base_dir',

		'current_timestamp',
		'current_datetime',
		'current_getdate',

		'absolute_path',
		'absolute_file_name',
		'base_file',
		'path',
		'file_name',
		'name_only',
		'extension',
		'file_size',
		'upload_date',

		'dimensions',
		'pixels',
		'width',
		'height',
		'orientation',
		'hwstring_small',
		'size_keys',
		'size_names',
		'size_bytes',
		'size_pixels',
		'size_dimensions',
		'size_name[size]',
		'size_bytes[size]',
		'size_pixels[size]',
		'size_dimensions[size]',

		'original_absolute_file_name',
		'original_base_file',
		'original_file_name',
		'original_name_only',
		'original_file_size',
		'original_dimensions',
		'original_pixels',
		'original_width',
		'original_height',

		'parent_date',
		'parent_type',
		'parent_title',
		'parent_issues',
		'reference_issues',
		'featured_in',
		'featured_in_title',
		'inserted_in',
		'inserted_in_title',
		'gallery_in',
		'gallery_in_title',
		'mla_gallery_in',
		'mla_gallery_in_title',

		'aperture',
		'credit',
		'camera',
		'caption',
		'created_timestamp',
		'copyright',
		'focal_length',
		'iso',
		'shutter_speed',
		'title'
	);

	/**
	 * Identify custom field mapping data source
	 *
	 * Determines whether a name matches any of the element-level data source dropdown options, i.e.,
	 * excludes "template:" and "meta:" values.
	 *
	 * @since 2.20
	 *
	 * @param	string 	candidate data source name
	 *
	 * @return	boolean	true if candidate name matches a data source
	 */
	public static function mla_is_data_source( $candidate_name ) {
		static $intermediate_sizes = NULL;

		/*
		 * The [size] elements are expanded with available image sizes;
		 * convert valid sizes back to the generic [size] value to match the list.
		 */
		$match_count = preg_match( '/(.+)\[(.+)\]/', $candidate_name, $matches );
		if ( 1 == $match_count ) {
			if ( NULL === $intermediate_sizes ) {
				$intermediate_sizes = get_intermediate_image_sizes();
			}

			if ( in_array( $matches[2], $intermediate_sizes ) ) {
				$candidate_name = $matches[1] . '[size]';
			} else {
				return false;
			}
		}

		return in_array( $candidate_name, MLAData_Source::$custom_field_data_sources );
	} // mla_is_data_source

	/**
	 * Get IPTC/EXIF/WP or custom field mapping data source
	 *
	 * Defined as public so MLA Mapping Hooks clients can call it.
	 * Isolates clients from changes to _evaluate_data_source().
	 *
	 * @since 2.20
	 *
	 * @param	integer	post->ID of attachment
	 * @param	string 	category/scope to evaluate against: custom_field_mapping or single_attachment_mapping
	 * @param	array	data source specification ( data_source, qualifier, meta_name, keep_existing, format, option )
	 * @param	array 	(optional) _wp_attachment_metadata, default NULL (use current postmeta database value)
	 *
	 * @return	string|array	data source value
	 */
	public static function mla_get_data_source( $post_id, $category, $data_value, $attachment_metadata = NULL ) {
		if ( !class_exists( 'MLAQuery' ) ) {
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-data-query.php' );
			MLAQuery::initialize();
		}

		if ( !class_exists( 'MLAData' ) ) {
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-data.php' );
			MLAData::initialize();
		}

		$default_arguments = array(
			'data_source' => 'none',
			'qualifier' => '',
			'meta_name' => '',
			'format' => 'native',
			'option' => 'text',
			'keep_existing' => true,
		);
		$data_value = shortcode_atts( $default_arguments, $data_value );

		return MLAData_Source::_evaluate_data_source( $post_id, $category, $data_value, $attachment_metadata );
	} // mla_get_data_source

	/**
	 * Intercept filesize() warnings and errors
	 * 
	 * @since 3.24
	 *
	 * @param	int		the level of the error raised
	 * @param	string	the error message
	 * @param	string	the filename that the error was raised in
	 * @param	int		the line number the error was raised at
	 *
	 * @return	boolean	true, to bypass PHP error handler
	 */
	public static function filesize_error_handler( $type, $string, $file, $line ) {
		MLACore::mla_debug_add( __LINE__ . " MLAData_Source::filesize_error_handler( $type, $string, $file, $line )", MLACore::MLA_DEBUG_CATEGORY_ANY );
		// Don't execute PHP internal error handler
		return true;
	}

	/**
	 * Silent PHP filesize() wrapped with warning and error handlers
	 * 
	 * @since 3.24
	 *
	 * @param	string	the absolute path and filename
	 *
	 * @return	mixed	integer file size or false if failure
	 */
	private static function _get_filesize( $file ) {
		set_error_handler( 'MLAData_Source::filesize_error_handler' );
		try {
			$filesize = @filesize( $file );
		} catch ( Throwable $e ) { // PHP 7
			$filesize = false;
		} catch ( Exception $e ) { // PHP 5
			$filesize = false;
		}
		restore_error_handler();

		return $filesize;
	}

	/**
	 * Evaluate file information for custom field mapping
 	 *
	 * @since 2.20
	 *
	 * @param	string	absolute path the the uploads base directory
	 * @param	array	_wp_attached_file meta_value array, indexed by post_id
	 * @param	array	_wp_attachment_metadata meta_value array, indexed by post_id
	 * @param	integer	post->ID of attachment
	 *
	 * @return	array	absolute_path_raw, absolute_path, absolute_file_name_raw, absolute_file_name, absolute_file, base_file, path, file_name, extension, dimensions, width, height, hwstring_small, array of intermediate sizes
	 */
	private static function _evaluate_file_information( $upload_dir, &$wp_attached_files, &$wp_attachment_metadata, $post_id ) {
		$results = array(
			'absolute_path_raw' => '',
			'absolute_path' => '',
			'absolute_file_name_raw' => '',
			'absolute_file_name' => '',
			'base_file' => '',
			'path' => '',
			'file_name' => '',
			'name_only' => '',
			'extension' => '',
			'width' => '',
			'height' => '',
			'orientation' => '',
			'hwstring_small' => '',
			'filesize' => '',
			'sizes' => array(),
			'original_absolute_file_name_raw' => '',
			'original_absolute_file_name' => '',
			'original_base_file' => '',
			'original_file_name' => '',
			'original_name_only' => '',
			'original_width' => '',
			'original_height' => '',
			'original_file_size' => '',
		);

		$base_file = isset( $wp_attached_files[ $post_id ]->meta_value ) ? $wp_attached_files[ $post_id ]->meta_value : '';
		$sizes = array();

		if ( isset( $wp_attachment_metadata[ $post_id ]->meta_value ) ) {
			$attachment_metadata =  @unserialize( $wp_attachment_metadata[ $post_id ]->meta_value );
			if ( ! is_array( $attachment_metadata ) ) {
				$attachment_metadata = array();
			}
		} else {
			$attachment_metadata = array();
		}

		if ( !empty( $attachment_metadata ) ) {
			if ( isset( $attachment_metadata['image_meta'] ) ) {
				foreach ( $attachment_metadata['image_meta'] as $key => $value )
					$results[ $key ] = $value;
			}

			if ( isset( $attachment_metadata['filesize'] ) ) {
				$results['filesize'] = $attachment_metadata['filesize'];
			}

			$sizes = isset( $attachment_metadata['sizes'] ) ? $attachment_metadata['sizes'] : array();

			if ( isset( $attachment_metadata['width'] ) ) {
				$results['width'] = $attachment_metadata['width'];
				$width = absint( $results['width'] );
			} else {
				$width = 0;
			}

			if ( isset( $attachment_metadata['height'] ) ) {
				$results['height'] = $attachment_metadata['height'];
				$height = absint( $results['height'] );
			} else {
				$height = 0;
			}

			if ( $width && $height ) {
				$results['orientation'] = ( $height > $width ) ? 'portrait' : 'landscape';
			}

			$results['hwstring_small'] = isset( $attachment_metadata['hwstring_small'] ) ? $attachment_metadata['hwstring_small'] : '';
		}

		if ( ! empty( $base_file ) ) {
			$pathinfo = pathinfo( $base_file );
			$results['base_file'] = $base_file;
			if ( '.' === $pathinfo['dirname'] ) {
				$results['absolute_path_raw'] = $upload_dir;
				$results['absolute_path'] = str_replace( '\\', '/', $upload_dir );
				$results['path'] = '';
			} else {
				$results['absolute_path_raw'] = $upload_dir . $pathinfo['dirname'] . '/';
				$results['absolute_path'] = str_replace( '\\', '/', $results['absolute_path_raw'] );
				$results['path'] = $pathinfo['dirname'] . '/';
			}

			$results['absolute_file_name_raw'] = $results['absolute_path_raw'] . $pathinfo['basename'];
			$results['absolute_file_name'] = str_replace( '\\', '/', $results['absolute_file_name_raw'] );
			$results['file_name'] = $pathinfo['basename'];
			$results['name_only'] = $pathinfo['filename'];
			$results['extension'] = $pathinfo['extension'];

			if ( isset( $attachment_metadata['original_image'] ) ) {
				$original_file_name = $attachment_metadata['original_image'];
				$results['original_absolute_file_name_raw'] = $results['absolute_path_raw'] . $original_file_name;
				$results['original_absolute_file_name'] = str_replace( '\\', '/', $results['original_absolute_file_name_raw'] );
				$results['original_base_file'] = $results['path'] . $original_file_name;
				$results['original_file_name'] = $original_file_name;
				$results['original_name_only'] = pathinfo( $original_file_name, PATHINFO_FILENAME );

				$size = getimagesize( $results['original_absolute_file_name_raw'] );
				if ( false !== $size ) {
					$results['original_width'] = $size[0];
					$results['original_height'] = $size[1];
				}

				$filesize = self::_get_filesize( $results['original_absolute_file_name_raw'] );
				if ( false !== $filesize ) {
					$results['original_file_size'] = $filesize;
				}
			} else {
				// Default to original values for unscaled images
				$results['original_absolute_file_name_raw'] = $results['absolute_file_name_raw'];
				$results['original_absolute_file_name'] = $results['absolute_file_name_raw'];
				$results['original_base_file'] = $results['base_file'];
				$results['original_file_name'] = $results['file_name'];
				$results['original_name_only'] = $results['name_only'];
				$results['original_width'] = $results['width'];
				$results['original_height'] = $results['height'];

				if ( !empty( $results['filesize'] ) ) {
					$results['original_file_size'] = $results['filesize'];
				} else {
					$filesize = self::_get_filesize( $results['absolute_file_name_raw'] );
					if ( false !== $filesize ) {
						$results['original_file_size'] = $filesize;
					}
				}
			}
		}

		$results['sizes'] = $sizes;
		return $results;
	} // _evaluate_file_information

	/**
	 * Evaluate post information for custom field mapping
 	 *
	 * @since 2.20
	 *
	 * @param	integer	post->ID of attachment
	 * @param	string 	category/scope to evaluate against: custom_field_mapping or single_attachment_mapping
	 * @param	string	data source name ( post_date or post_parent )
	 *
	 * @return	mixed	(string)/'' or (integer)/0 depending on $data_source type
	 */
	private static function _evaluate_post_information( $post_id, $category, $data_source ) {
		global $wpdb;
		static $post_info = NULL;

		if ( 0 === $post_id ) {
			$value = NULL;
		} else {
			// Check for $post_id match
			if ( 'single_attachment_mapping' === $category && ! isset( $post_info[$post_id] ) ) {
				$post_info = NULL;
			}

			if ( NULL === $post_info ) {
				if ( 'custom_field_mapping' === $category ) {
					$post_info = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_type = 'attachment'", OBJECT_K ); // phpcs:ignore
				} else {
					$post_info = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE ID = '{$post_id}'", OBJECT_K ); // phpcs:ignore
				}
			}

			if ( 'post_id' === $data_source ) {
				$data_source = 'ID';
			}

			if ( isset( $post_info[$post_id] ) && property_exists( $post_info[$post_id], $data_source ) ) {
				$post_array = (array) $post_info[$post_id];
				$value = $post_array[ $data_source ];
			} else {
				$value = NULL;
			}
		}

		switch ( $data_source ) {
			case 'ID':
			case 'post_id':
			case 'post_author':
			case 'post_parent':
			case 'menu_order':
			case 'comment_count':
				return ( NULL !== $value ) ? $value : 0;
			default:
				return ( NULL !== $value ) ? $value : '';
		}

		return false;
	} // _evaluate_post_information

	/**
	 * Evaluate post information for custom field mapping
 	 *
	 * @since 2.20
	 *
	 * @param	array	field value(s)
	 * @param	string 	format option text|single|export|array|multi
	 * @param	boolean	keep existing value(s) - for 'multi' option
	 *
	 * @return	mixed	array for option = array|multi else string
	 */
	private static function _evaluate_array_result( $value, $option, $keep_existing ) {
		if ( empty( $value ) ) {
			return '';
		}

		if ( is_array( $value ) ) {
			if ( 'single' == $option || 1 == count( $value ) ) {
				return current( $value );
			} elseif ( 'export' == $option ) {
				return  var_export( $value, true );
			} elseif ( 'text' == $option ) {
				return implode( ',', $value );
			} elseif ( 'multi' == $option ) {
				$value[0x80000000] = $option;
				$value[0x80000001] = $keep_existing;
				return $value;
			}
		}

		// $option = array returns the array
		return $value;
	} // _evaluate_array_result

	/**
	 * Evaluate custom field mapping data source
	 *
	 * @since 2.20
	 *
	 * @param	integer	post->ID of attachment
	 * @param	string 	category/scope to evaluate against: custom_field_mapping or single_attachment_mapping
	 * @param	array	data source specification ( name, *data_source, *keep_existing, *format, mla_column, quick_edit, bulk_edit, *meta_name, *option, no_null )
	 * @param	array 	(optional) _wp_attachment_metadata, default NULL (use current postmeta database value)
	 *
	 * @return	string|array	data source value
	 */
	private static function _evaluate_data_source( $post_id, $category, $data_value, $attachment_metadata = NULL ) {
		global $wpdb;
		static $upload_dir_array, $upload_dir, $intermediate_sizes = NULL, $wp_attached_files = NULL, $wp_attachment_metadata = NULL;
		static $current_id = 0, $file_info = NULL, $parent_info = NULL, $references = NULL, $alt_text = NULL;
//error_log( __LINE__ . " _evaluate_data_source( $current_id, $post_id, $category ) data_value = " . var_export( $data_value, true ), 0 );

		if ( 'none' === $data_value['data_source'] ) {
			return '';
		}

		$data_source = $data_value['data_source'];
		$qualifier = isset( $data_value['qualifier'] ) && ( 0 < strlen( $data_value['qualifier'] ) ) ? $data_value['qualifier'] : '';

		// Do this once per page load; cache attachment metadata if mapping all attachments
		if ( NULL === $intermediate_sizes ) {
			$upload_dir_array = wp_upload_dir();
			$upload_dir = $upload_dir_array['basedir'] . '/';
			$intermediate_sizes = get_intermediate_image_sizes();

			if ( 'custom_field_mapping' === $category ) {
				if ( ! $table = _get_meta_table('post') ) {
					$wp_attached_files = array();
					$wp_attachment_metadata = array();
				} else {
					$wp_attachment_metadata = $wpdb->get_results( "SELECT post_id, meta_value FROM {$table} WHERE meta_key = '_wp_attachment_metadata'", OBJECT_K ); // phpcs:ignore
					$wp_attached_files = $wpdb->get_results( "SELECT post_id, meta_value FROM {$table} WHERE meta_key = '_wp_attached_file'", OBJECT_K ); // phpcs:ignore
				}
			} // custom_field_mapping, i.e., mapping all attachments
		} // first call after page load

		// Do this once per post. Simulate SQL results for $wp_attached_files and $wp_attachment_metadata.
		if ( $current_id !== $post_id ) {
			$current_id = $post_id;
			$parent_info = NULL;
			$references = NULL;
			$alt_text = NULL;
			MLAData::mla_reset_regex_matches( $post_id );

			if ( 'single_attachment_mapping' === $category ) {
				$metadata = get_metadata( 'post', $post_id, '_wp_attached_file' );
				if ( isset( $metadata[0] ) ) {
					$wp_attached_files = array( $post_id => (object) array( 'post_id' => $post_id, 'meta_value' =>  $metadata[0] ) );
				} else {
					$wp_attached_files = array();
				}

				if ( NULL === $attachment_metadata ) {
					$metadata = get_metadata( 'post', $post_id, '_wp_attachment_metadata' );
					if ( isset( $metadata[0] ) ) {
						$attachment_metadata = $metadata[0];
					}
				}

				if ( empty( $attachment_metadata ) ) {
					$attachment_metadata = array();
				}

				$wp_attachment_metadata = array( $post_id => (object) array( 'post_id' => $post_id, 'meta_value' => serialize( $attachment_metadata ) ) );
			}

 			$file_info = MLAData_Source::_evaluate_file_information( $upload_dir, $wp_attached_files, $wp_attachment_metadata, $post_id );
		}

		$size_info = array( 'file' => '', 'width' => '', 'height' => '', 'size' => '' );
		$match_count = preg_match( '/(.+)\[(.+)\]/', $data_source, $matches );
		if ( 1 === $match_count ) {
			$data_source = $matches[1] . '[size]';
			$size_info['size'] = $matches[2];
			if ( isset( $file_info['sizes'][ $matches[2] ] ) ) {
				$size_info = $file_info['sizes'][ $matches[2] ];
				$size_info['size'] = $matches[2];
			}
		}

		$result = '';

		switch( $data_source ) {
			case 'meta':
				$attachment_metadata = isset( $wp_attachment_metadata[ $post_id ]->meta_value ) ? maybe_unserialize( $wp_attachment_metadata[ $post_id ]->meta_value ) : array();
				$result = MLAData::mla_find_array_element( $data_value['meta_name'], $attachment_metadata, $data_value['option'], $data_value['keep_existing'] );
				break;
			case 'template':
				if ( in_array( $data_value['option'], array ( 'single', 'export', 'array', 'multi' ) ) ) {
					$default_option = 'array';
				} else {
					$default_option = 'text';
				}

				// Go through the template and expand the non-prefixed elements as Data Sources
				$item_values = array();
				
				// For page_ and parent_ prefixes
				$item_values['page_ID'] = 0;
				$item_values['parent'] = absint( MLAData_Source::_evaluate_post_information( $post_id, $category, 'post_parent' ) );

				$placeholders = MLAData::mla_get_template_placeholders( $data_value['meta_name'], $default_option );
				foreach ( $placeholders as $key => $placeholder ) {
					if ( empty( $placeholder['prefix'] ) ) {
						$field_value = $data_value;
						$field_value['data_source'] = $placeholder['value'];
						$field_value['qualifier'] = $placeholder['qualifier'];
						$field_value['meta_name'] = '';
						$field_value['option'] = $placeholder['option'];
						$field_value['format'] = $placeholder['format'];

						if ( isset( $placeholder['args'] ) ) {
							$field_value['args'] = $placeholder['args'];
						}
						
						$field_value = MLAData_Source::_evaluate_data_source( $post_id, $category, $field_value, $attachment_metadata );
						$item_values[ $key ] = MLAData::mla_apply_field_level_format( $field_value, $placeholder );
					} // Data Source
				} // foreach placeholder

				// Now expand the template using the above Data Source values
				$template = '[+template:' . $data_value['meta_name'] . '+]';
				$item_values = MLAData::mla_expand_field_level_parameters( $template, NULL, $item_values, $post_id, $data_value['keep_existing'], $default_option, $attachment_metadata );

				if ( 'array' ===  $default_option ) {
					$result = MLAData::mla_parse_array_template( $template, $item_values, $data_value['option'] );
					$result = MLAData_Source::_evaluate_array_result( $result, $data_value['option'], $data_value['keep_existing'] );
				} else {
					$result = MLAData::mla_parse_template( $template, $item_values );
				}
				break;
			case 'parent':
				$data_source = 'post_parent';
				/* fallthru */
			case 'ID':
			case 'post_id':
			case 'post_author':
			case 'post_parent':
			case 'menu_order':
			case 'comment_count':
				$result = absint( MLAData_Source::_evaluate_post_information( $post_id, $category, $data_source ) );
				break;
			case 'alt_text':
				if ( NULL == $alt_text ) {
					$metadata = get_metadata( 'post', $post_id, '_wp_attachment_image_alt' );
					if ( is_array( $metadata ) ) {
						if ( count( $metadata ) == 1 ) {
							$alt_text = maybe_unserialize( $metadata[0] );
						} else {
							$alt_text = array();
							foreach ( $metadata as $single_key => $single_value ) {
								$alt_text[ $single_key ] = maybe_unserialize( $single_value );
							}
						}
					}
				}

				if ( ! empty( $alt_text ) ) {
					$result = MLAData_Source::_evaluate_array_result( $alt_text, $data_value['option'], $data_value['keep_existing'] );
				}
				break;
			case 'mime_type': 
				$data_source = 'post_mime_type';
				/* fallthru */
			case 'post_date':
			case 'post_date_gmt':
			case 'post_content':
			case 'post_title':
			case 'post_excerpt':
			case 'post_status':
			case 'comment_status':
			case 'ping_status':  
			case 'post_name':
			case 'post_modified':
			case 'post_modified_gmt':
			case 'post_content_filtered':
			case 'guid':
			case 'post_mime_type': 
				$result = MLAData_Source::_evaluate_post_information( $post_id, $category, $data_source );
				break;
			case 'site_url': 
				$result = site_url();
				break;
			case 'base_url': 
				$result = $upload_dir_array['baseurl'];
				break;
			case 'base_dir': 
				$result = wptexturize( str_replace( '\\', '/', $upload_dir_array['basedir'] ) );
				break;
			case 'current_timestamp':
				switch ( strtolower( $qualifier ) ) {
					case 'gmt':
						$result = time();
						break;
					default:
						$result = current_time( 'timestamp' );
				}
				
				break;
			case 'current_datetime': 
				switch ( strtolower( $qualifier ) ) {
					case 'gmt':
						$result = time();
						break;
					default:
						$result = current_time( 'timestamp' );
				}
				
				$result = date( 'Y:m:d H:i:s', $result );
				break;
			case 'current_getdate': 
				$qualifier = strtolower( $qualifier );
				if ( 0 === strpos( $qualifier, 'gmt' ) ) {
					$result = time();
					$qualifier = substr( $qualifier, 3 );
				} else {
					$result = current_time( 'timestamp' );
				}
					
				$result = getdate( $result );

				if ( '0' === $qualifier ) {
					$result = $result[ 0 ];
				} elseif ( empty( $qualifier ) ) {
					$result = MLAData_Source::_evaluate_array_result( $result, $data_value['option'], $data_value['keep_existing'] );
				} elseif ( isset( $result[ $qualifier ] ) ) {
					$result = $result[ $qualifier ];
				} else {
					$result = '';
				}
				break;
			case 'absolute_path':
			case 'absolute_file_name':
			case 'original_absolute_file_name':
			case 'base_file':
			case 'original_base_file':
			case 'path':
			case 'file_name':
			case 'original_file_name':
			case 'name_only':
			case 'original_name_only':
			case 'extension':
			case 'width':
			case 'original_width':
			case 'height':
			case 'original_height':
			case 'orientation':
			case 'hwstring_small':
			case 'original_file_size':
			case 'aperture':
			case 'credit':
			case 'camera':
			case 'caption':
			case 'created_timestamp':
			case 'copyright':
			case 'focal_length':
			case 'iso':
			case 'shutter_speed':
			case 'title':
				if ( isset( $file_info[ $data_source ] ) ) {
					$result = $file_info[ $data_source ];
				}
				break;
			case 'file_size':
				if ( !empty( $file_info['filesize'] ) ) {
					$result = $file_info['filesize'];
					break;
				}

				if ( !empty( $file_info['absolute_file_name_raw'] ) ) {
					$filesize = self::_get_filesize( $file_info['absolute_file_name_raw'] );
					if ( false !== $filesize ) {
						$result = $filesize;
					}
				}
				break;
			case 'upload_date':
				$result = MLAData_Source::_evaluate_post_information( $post_id, $category, 'post_date' );
				break;
			case 'dimensions':
				$result = $file_info['width'] . 'x' . $file_info['height'];
				if ( 'x' === $result ) {
					$result = '';
				}
				break;
			case 'original_dimensions':
				$result = $file_info['original_width'] . 'x' . $file_info['original_height'];
				if ( 'x' === $result ) {
					$result = '';
				}
				break;
			case 'pixels':
				$result = absint( (int) $file_info['width'] * (int) $file_info['height'] );
				if ( 0 == $result ) {
					$result = '';
				} else {
					$result = (string) $result;
				}
				break;
			case 'original_pixels':
				$result = absint( (int) $file_info['original_width'] * (int) $file_info['original_height'] );
				if ( 0 == $result ) {
					$result = '';
				} else {
					$result = (string) $result;
				}
				break;
			case 'size_keys':
				$result = array();
				foreach ( $file_info['sizes'] as $key => $value )
					$result[] = $key;

				$result = MLAData_Source::_evaluate_array_result( $result, $data_value['option'], $data_value['keep_existing'] );
				break;
			case 'size_names':
				$result = array();
				foreach ( $file_info['sizes'] as $key => $value )
					$result[] = $value['file'];

				$result = MLAData_Source::_evaluate_array_result( $result, $data_value['option'], $data_value['keep_existing'] );
				break;
			case 'size_bytes':
				$result = array();
				foreach ( $file_info['sizes'] as $key => $value ) {
					if ( !empty( $value['filesize'] ) ) {
						$filesize = $value['filesize'];
					} else {
						$filesize = self::_get_filesize( $file_info['absolute_path_raw'] . $value['file'] );
					}

					if ( false === $filesize ) {
						$result[] = '?';
					} else {
						switch( $data_value['format'] ) {
							case 'commas':
								if ( is_numeric( $filesize ) ) {
									$filesize = number_format( (float)$filesize );
								}
								break;
							default:
								// no change
						} // format
						$result[] = $filesize;
					}
				}

				$result = MLAData_Source::_evaluate_array_result( $result, $data_value['option'], $data_value['keep_existing'] );
				break;
			case 'size_pixels':
				$result = array();
				foreach ( $file_info['sizes'] as $key => $value ) {
					$pixels = absint( (int) $value['width'] * (int) $value['height'] );

					switch( $data_value['format'] ) {
						case 'commas':
							if ( is_numeric( $pixels ) ) {
								$pixels = number_format( (float)$pixels );
							}
							break;
						default:
							// no change
					} // format
					$result[] = $pixels;
				}

				$result = MLAData_Source::_evaluate_array_result( $result, $data_value['option'], $data_value['keep_existing'] );
				break;
			case 'size_dimensions':
				$result = array();
				foreach ( $file_info['sizes'] as $key => $value ) {
					$result[] = $value['width'] . 'x' . $value['height'];
				}

				$result = MLAData_Source::_evaluate_array_result( $result, $data_value['option'], $data_value['keep_existing'] );
				break;
			case 'size_name[size]':
				$result = $size_info['file'];
				break;
			case 'size_bytes[size]':
				if ( !empty( $size_info['filesize'] ) ) {
					$result = $size_info['filesize'];
				} elseif ( !empty( $size_info['file'] ) ) {
					$result = self::_get_filesize( $file_info['absolute_path_raw'] . $size_info['file'] );
				} else {
					$result = false;
				}
				
				if ( false === $result ) {
					$result = '?';
				}

				break;
			case 'size_pixels[size]':
				$result = absint( (int) $size_info['width'] * (int) $size_info['height'] );
				break;
			case 'size_dimensions[size]':
				$result = $size_info['width'] . 'x' . $size_info['height'];
				if ( 'x' == $result ) {
					$result = '';
				}
				break;
			case 'parent_date':
			case 'parent_type':
			case 'parent_title':
				if ( is_null( $parent_info ) ) {
					$parent_info = MLAQuery::mla_fetch_attachment_parent_data( MLAData_Source::_evaluate_post_information( $post_id, $category, 'post_parent' ) );
				}

				if ( isset( $parent_info[ $data_source ] ) ) {
					$result = $parent_info[ $data_source ];
				}
				break;
			case 'parent_issues':
				if ( is_null( $references ) ) {
					$references = MLAQuery::mla_fetch_attachment_references( $post_id, MLAData_Source::_evaluate_post_information( $post_id, $category, 'post_parent' ) );
				}

				if ( !empty( $references['parent_errors'] ) ) {
					$result = $references['parent_errors'];
					// Remove (ORPHAN...
					$orphan_certain =  '(' . __( 'ORPHAN', 'media-library-assistant' ) . ')';
					$orphan_possible = '(' . __( 'ORPHAN', 'media-library-assistant' ) . '?)';

					if ( false !== strpos( $result, $orphan_certain ) ) {
						$result = trim( substr( $result, strlen( $orphan_certain ) ) );
					} elseif ( false !== strpos( $result, $orphan_possible ) ) {
						$result = trim( substr( $result, strlen( $orphan_possible ) ) );
					}
				}
				break;
			case 'reference_issues':
				if ( is_null( $references ) ) {
					$references = MLAQuery::mla_fetch_attachment_references( $post_id, MLAData_Source::_evaluate_post_information( $post_id, $category, 'post_parent' ) );
				}

				if ( !empty( $references['parent_errors'] ) ) {
					$result = $references['parent_errors'];
				}
				break;
			case 'featured_in':
			case 'featured_in_title':
				if ( is_null( $references ) ) {
					$references = MLAQuery::mla_fetch_attachment_references( $post_id, MLAData_Source::_evaluate_post_information( $post_id, $category, 'post_parent' ) );
				}

				if ( !empty( $references['features'] ) ) {
					$result = array();
					foreach ( $references['features'] as $ID => $value )
						if ( 'featured_in' == $data_source ) {
							$result[] = sprintf( '%1$s (%2$s %3$d)', $value->post_title, $value->post_type, $ID ); 
						} else {
							$result[] = $value->post_title; 
						}

					$result = MLAData_Source::_evaluate_array_result( $result, $data_value['option'], $data_value['keep_existing'] );
				} else {
					$result = '';
				}
				break;
			case 'inserted_in':
			case 'inserted_in_title':
				if ( is_null( $references ) ) {
					$references = MLAQuery::mla_fetch_attachment_references( $post_id, MLAData_Source::_evaluate_post_information( $post_id, $category, 'post_parent' ) );
				}

				if ( !empty( $references['inserts'] ) ) {
					$result = array();
					foreach ( $references['inserts'] as $base_file => $inserts )
						foreach ( $inserts as $value )
							if ( 'inserted_in' == $data_source ) {
								$result[] = sprintf( '%1$s (%2$s %3$d)', $value->post_title, $value->post_type, $value->ID ); 
							} else {
								$result[] = $value->post_title; 
							}

					ksort( $result );

					$result = MLAData_Source::_evaluate_array_result( $result, $data_value['option'], $data_value['keep_existing'] );
				} else {
					$result = '';
				}
				break;
			case 'gallery_in':
			case 'gallery_in_title':
				if ( is_null( $references ) ) {
					$references = MLAQuery::mla_fetch_attachment_references( $post_id, MLAData_Source::_evaluate_post_information( $post_id, $category, 'post_parent' ) );
				}

				if ( !empty( $references['galleries'] ) ) {
					$result = array();
					foreach ( $references['galleries'] as $ID => $value )
						if ( 'gallery_in' == $data_source ) {
							$result[] = sprintf( '%1$s (%2$s %3$d)', $value['post_title'], $value['post_type'], $ID ); 
						} else {
							$result[] = $value['post_title']; 
						}

					$result = MLAData_Source::_evaluate_array_result( $result, $data_value['option'], $data_value['keep_existing'] );
				} else {
					$result = '';
				}
				break;
			case 'mla_gallery_in':
			case 'mla_gallery_in_title':
				if ( is_null( $references ) ) {
					$references = MLAQuery::mla_fetch_attachment_references( $post_id, MLAData_Source::_evaluate_post_information( $post_id, $category, 'post_parent' ) );
				}

				if ( !empty( $references['mla_galleries'] ) ) {
					$result = array();
					foreach ( $references['mla_galleries'] as $ID => $value )
						if ( 'mla_gallery_in' == $data_source ) {
							$result[] = sprintf( '%1$s (%2$s %3$d)', $value['post_title'], $value['post_type'], $ID ); 
						} else {
							$result[] = $value['post_title']; 
						}

					$result = MLAData_Source::_evaluate_array_result( $result, $data_value['option'], $data_value['keep_existing'] );
				} else {
					$result = '';
				}
				break;
			case 'index':
				if ( class_exists( 'MLA' ) && !empty( MLA::$bulk_edit_data_source['cb_index'] ) ) {
					$result = MLA::$bulk_edit_data_source['cb_index'];
					if ( !empty( $data_value['format'] ) ) {
						$result += absint( $data_value['format'] );
					}
				}
				break;
			case 'reverse_index':
				if ( class_exists( 'MLA' ) && !empty( MLA::$bulk_edit_data_source['cb_index'] ) && !empty( MLA::$bulk_edit_data_source['cb_count'] ) ) {
					$result = 1 + MLA::$bulk_edit_data_source['cb_count'] - MLA::$bulk_edit_data_source['cb_index'];
					if ( !empty( $data_value['format'] ) ) {
						$result += absint( $data_value['format'] );
					}
				}
				break;
			case 'found_rows':
				if ( class_exists( 'MLA' ) && !empty( MLA::$bulk_edit_data_source['cb_count'] ) ) {
					$result = MLA::$bulk_edit_data_source['cb_count'];
				}
				break;
 			default:
				$custom_value = apply_filters( 'mla_evaluate_custom_data_source', NULL, $post_id, $category, $data_value, $attachment_metadata );
				if ( !is_null( $custom_value ) ) {
					return $custom_value;
				}

				return '';
		} // switch $data_source

		switch( $data_value['format'] ) {
			case 'raw':
				return $result;
			case 'commas':
				if ( is_numeric( $result ) ) {
					$result = str_pad( number_format( (float)$result ), 15, ' ', STR_PAD_LEFT );
				}
				break;
			case 'native':
			default:
				// Make some numeric values sortable as strings, make all value non-empty
				if ( in_array( $data_source, array( 'file_size', 'pixels', 'width', 'height' ) ) ) {
					$result = str_pad( $result, 15, ' ', STR_PAD_LEFT );
				} elseif ( empty( $result ) ) {
					$result = ' ';
				}
		} // format

		return $result;
	} // _evaluate_data_source
} // class MLAData_Source
?>