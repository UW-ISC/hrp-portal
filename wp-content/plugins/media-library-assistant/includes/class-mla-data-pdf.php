<?php
/**
 * Meta data parsing functions for PDF documents
 *
 * @package Media Library Assistant
 * @since 2.10
 */

/**
 * Class MLA (Media Library Assistant) PDF extracts legacy and XMP meta data from PDF files
 *
 * @package Media Library Assistant
 * @since 2.10
 */
class MLAPDF {
	/**
	 * Array of PDF indirect objects
	 *
	 * This array contains all of the known indirect object offsets and lengths.
	 * The array key is ( object ID * 1000 ) + object generation.
	 * The array value is either:
	 *    uncompressed objects: array( number, generation, start, optional /length )
	 *    compressed objects: array( number, stream, index )
	 *
	 * @since 2.10
	 *
	 * @var	array
	 */
	private static $pdf_indirect_objects = array();

	/**
	 * Cache array of PDF object streams
	 *
	 * This array contains uncompressed and indexed versions of known object streams.
	 * The array key is object_number. Object generation is implicitly zero.
	 * Each array value contains: array( object_offsets => array( object_number => array( offset, length ), stream_content )
	 *
	 * @since 3.30
	 *
	 * @var	array
	 */
	private static $pdf_object_streams = array();

	/**
	 * Intercept flatedecode warnings and errors
	 * 
	 * @since 3.30
	 *
	 * @param	int		the level of the error raised
	 * @param	string	the error message
	 * @param	string	the filename that the error was raised in
	 * @param	int		the line number the error was raised at
	 *
	 * @return	boolean	true, to bypass PHP error handler
	 */
	public static function flatedecode_error_handler( $type, $string, $file, $line ) {
		MLACore::mla_debug_add( __LINE__ . " MLAPDF::flatedecode_error_handler( $type, $string, $file, $line )", MLACore::MLA_DEBUG_CATEGORY_METADATA );
		// Don't execute PHP internal error handler
		return true;
	}

	/**
	 * Decode streams
	 * 
	 * @since 3.30
	 *
	 * @param	string	Encoded data
	 * @param	integer	Optional. File offset of Encoded data
	 *
	 * @return	string	Decoded data
	 */
	private static function _decode_flatedecode_stream( $encodedData, $offset = -1 ) {
		if ( function_exists( 'gzuncompress' ) ) {
			set_error_handler( 'MLAPDF::flatedecode_error_handler' );

			$decoded = @gzuncompress( $encodedData );
			if ( $decoded === false ) {
				$decoded = @gzinflate( $encodedData );
				if ( $decoded === false ) {
					$stripped = substr( $encodedData, 2, -4 );
					$decoded = @gzinflate( $stripped );
				}
			}

			restore_error_handler();
		} else {
			MLACore::mla_debug_add( __LINE__ . " MLAPDF::_decode_flatedecode_stream no Zlib support", MLACore::MLA_DEBUG_CATEGORY_METADATA );
			$decoded = false;
		}

		if ( $decoded !== false ) {
			return $decoded;
		}

		MLACore::mla_debug_add( __LINE__ . " MLAPDF::_decode_flatedecode_stream at offset {$offset} failed ", MLACore::MLA_DEBUG_CATEGORY_METADATA );
    	return "";
	}

	/**
	 * Parse a PDF Big Endian integer
	 * 
	 * @since 3.30
	 *
	 * @param	string	PDF byte sequence
	 * @param	integer	sequence length
	 *
	 * @return	integer	Big Endian encoded integer
	 */
	private static function _parse_pdf_integer( &$source_string, $length ) {
		$output = 0;
		for ($index = 0; $index < $length; ) {
			$output = ( $output << 8 ) + ord( $source_string[ $index++ ] );
		}

		return $output;
	}

	/**
	 * Parse a cross-reference table subsection into the array of indirect object definitions
	 * 
	 * A cross-reference subsection is a sequence of 20-byte entries, each with offset and generation values.
	 * @since 2.10
	 *
	 * @param	string	buffer containing the subsection
	 * @param	integer	offset within the buffer of the first entry
	 * @param	integer	number of the first object in the subsection
	 * @param	integer	number of entries in the subsection
	 * 
	 * @return	void
	 */
	private static function _parse_pdf_xref_subsection( &$xref_section, $offset, $object_id, $count ) {

		while ( $count-- ) {
			$match_count = preg_match( '/(\d+) (\d+) (.)/', $xref_section, $matches, 0, $offset);

			if ( $match_count ) {
				if ( 'n' == $matches[3] ) {
					$key = ( $object_id * 1000 ) + $matches[2];
					if ( ! isset( self::$pdf_indirect_objects[ $key ] ) ) {
						self::$pdf_indirect_objects[ $key ] = array( 'number' => $object_id, 'generation' => (integer) $matches[2], 'start' => (integer) $matches[1] );
					}
				}

				$object_id++;
				$offset += 20;
			} else {
				break;
			}
		}
	}

	/**
	 * Parse a cross-reference table section into the array of indirect object definitions
	 * 
	 * Creates the array of indirect object offsets and lengths
	 * @since 2.10
	 *
	 * @param	string	full path and file name
	 * @param	integer	offset within the file of the xref id and count entry
	 * 
	 * @return	integer	length of the section
	 */
	private static function _parse_pdf_xref_section( $file_name, $file_offset ) {
		$xref_max = $chunksize = 16384;			
		$xref_section = file_get_contents( $file_name, true, NULL, $file_offset, $chunksize );
		$xref_length = 0;

		while ( preg_match( '/^[\x00-\x20]*(\d+) (\d+)[\x00-\x20]*/', substr($xref_section, $xref_length), $matches, 0 ) ) {
			$object_id = $matches[1];
			$count = $matches[2];
			$offset = $xref_length + strlen( $matches[0] );
			$xref_length = $offset + ( 20 * $count );

			if ( $xref_max < $xref_length ) {
				$xref_max += $chunksize;
				$xref_section = file_get_contents( $file_name, true, NULL, $file_offset, $xref_max );
			}

			self::_parse_pdf_xref_subsection( $xref_section, $offset, $object_id, $count );
		} // while preg_match subsection header

		return $xref_length;
	}

	/**
	 * Parse a cross-reference steam into the array of indirect object definitions
	 * 
	 * Creates the array of indirect object offsets and lengths
	 * @since 2.10
	 *
	 * @param	string	full path and file name
	 * @param	integer	offset within the file of the xref id and count entry
	 * @param	string	"/W" entry, representing the size of the fields in a single entry
	 * @param	string	"/Index" entry, representing the subsection(s) in the stream
	 * @param	boolean true if the stream is compressed with the FlateDecode Filter
	 * 
	 * @return	integer	length of the stream
	 */
	private static function _parse_pdf_xref_stream( $file_name, $file_offset, $entry_parms_string, $index_string, $compressed = false ) {
		$chunksize = 16384;			
		$xref_section = file_get_contents( $file_name, true, NULL, $file_offset, $chunksize );

		// If necessary and possible, expand the $xref_section until it contains the end tag
		$new_chunksize = $chunksize;
		if ( false === ( $end_tag = strpos( $xref_section, 'endstream' ) ) && ( $chunksize === strlen( $xref_section ) ) ) {
			$new_chunksize = $chunksize + $chunksize;
			$xref_section = file_get_contents( $file_name, true, NULL, $file_offset, $new_chunksize );
			while ( false === ( $end_tag = strpos( $xref_section, 'endstream' ) ) && ( $new_chunksize == strlen( $xref_section ) ) ) {
				$new_chunksize = $new_chunksize + $chunksize;
				$xref_section = file_get_contents( $file_name, true, NULL, $file_offset, $new_chunksize );
			} // while not found
		} // if not found

		if ( false === $end_tag ) {
			return 0;
		}

	    // Extract stream content
	    $pattern = '/\s*stream(\x0D\x0A|\x0A)(.*?)(\x0D\x0A|\x0A)endstream\s*/s';
	    if ( preg_match_all( $pattern, $xref_section, $matches ) ) {
			$length = strlen( $matches[2][0] );

			if ( $compressed ) {
				$xref_stream = self::_decode_flatedecode_stream( $matches[2][0] );
			} else {
            	$xref_stream = $matches[2][0];
			}

			if ( empty( $xref_stream ) ) {
				$length = 0;
			}
		} else {
			$length = 0;
		}

		$entry_parms = explode( ' ', $entry_parms_string );
		$length1 = absint( $entry_parms[0] );
		$length2 = absint( $entry_parms[1] );
		$length3 = absint( $entry_parms[2] );
		$entry_length = $length1 + $length2 + $length3;

		// Convert subsection index to object numbers
		$object_ids = array();
		$subsections = explode( ' ', $index_string );
		while ( 1 < count( $subsections ) ) {
			$first_object = (integer) array_shift( $subsections );
			$object_count = (integer) array_shift( $subsections );
			while ( $object_count-- ) {
				$object_ids[] = $first_object++;
			}
		}

		$xref_entries = array();
		$xref_index = 0;
		$offset = 0;
		while (	$offset < $length ) {
			$entry = substr( $xref_stream, $offset, $entry_length );
			$type = self::_parse_pdf_integer( $entry, $length1 );
			$entry = substr( $entry, $length1 );
			$number = self::_parse_pdf_integer( $entry, $length2 );
			$entry = substr( $entry, $length2 );
			$index = self::_parse_pdf_integer( $entry, $length3 );
			$xref_entries[] = array( 'type' => $type, 'number' => $number, 'index' => $index );

			// Record the entry in the Indirect Objects array
			switch ( $type ) {
				case 1:
					$object_id = $object_ids[ $xref_index ];
					$key = ( $object_id * 1000 ) + $index;
					self::$pdf_indirect_objects[ $key ] = array( 'number' => $object_id, 'generation' => $index, 'start' => $number );
					break;
				case 2:
					$object_id = $object_ids[ $xref_index ];
					$key = ( $object_id * 1000 ); // generation is implicitly zero
					self::$pdf_indirect_objects[ $key ] = array( 'number' => $object_id, 'stream' => $number, 'index' => $index );
					break;
				case 0:
				default:
					break;
			}

			$offset += $entry_length;
			$xref_index++;
		}

		return $length;
	}

	/**
	 * Build an array of indirect object definitions
	 * 
	 * Creates or updates the array of indirect object offsets and lengths
	 *
	 * @since 2.10
	 *
	 * @param	string	The entire PDF document, passsed by reference
	 *
	 * @return	void
	 */
	private static function _build_pdf_indirect_objects( &$string ) {
		$match_count = preg_match_all( '!(\d+)\\h+(\d+)\\h+obj|endobj|stream(\x0D\x0A|\x0A)|endstream!', $string, $matches, PREG_OFFSET_CAPTURE );

		$object_level = 0;
		$is_stream = false;
		for ( $index = 0; $index < $match_count; $index++ ) {
			if ( $is_stream ) {
				if ( 'endstream' == substr( $matches[0][ $index ][0], 0, 9 ) ) {
					$is_stream = false;
				}
			} elseif ( 'endobj' == substr( $matches[0][ $index ][0], 0, 6 ) ) {
				$object_level--;
				$object_entry['/length'] = $matches[0][ $index ][1] - $object_entry['start'];
				self::$pdf_indirect_objects[ ($object_entry['number'] * 1000) + $object_entry['generation'] ] = $object_entry;
			} elseif ( 'obj' == substr( $matches[0][ $index ][0], -3 ) ) {
				$object_level++;
				$object_entry = array( 
					'number' => $matches[1][ $index ][0],
					'generation' => $matches[2][ $index ][0],
					'start' => $matches[0][ $index ][1] + strlen( $matches[0][ $index ][0] )
					);
			} elseif ( 'stream' == substr( $matches[0][ $index ][0], 0, 6 ) ) {
				$is_stream = true;
			} else {
				/* translators: 1: ERROR tag 2: index */
				MLACore::mla_debug_add( sprintf( _x( '%1$s: _build_pdf_indirect_objects bad value at $index = "%2$d".', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), $index ), MLACore::MLA_DEBUG_CATEGORY_METADATA );
			}
		} // for each match
	}

	/**
	 * Find the offset, length and contents of an indirect object within a compressed object stream
	 *
	 * @since 3.30
	 *
	 * @param	string	full path and file name
	 * @param	array	array( number, stream, index )
	 *
	 * @return	mixed	NULL on failure else array( 'start' => offset in the file, 'length' => object length, 'content' => object contents )
	 */
	private static function _find_object_in_stream( $file_name, $indirect_object ) {
		if ( isset( self::$pdf_object_streams[ $indirect_object['stream'] ] ) ) {
			$object_offsets = self::$pdf_object_streams[ $indirect_object['stream'] ]['object_offsets'];
			$stream_content = self::$pdf_object_streams[ $indirect_object['stream'] ]['stream_content'];
		} else {
			// Process new object stream
			$object_dictionary = self::_find_pdf_indirect_dictionary( $file_name, $indirect_object['stream'] );
			$dictionary = self::_parse_pdf_dictionary( $object_dictionary['content'], 0 );
			$compressed = isset( $dictionary['Filter'] ) && 'FlateDecode' == $dictionary['Filter']['value']; 
			$object = file_get_contents( $file_name, false, NULL, $object_dictionary['start'], $dictionary['Length']['value'] + $dictionary['/length'] + 21 ); // 21 = stream and endstream markers

			// Extract stream content
			$pattern = '/\s*stream(\x0D\x0A|\x0A)(.*?)(\x0D\x0A|\x0A)endstream\s*/s';
			if ( preg_match_all( $pattern, $object, $matches ) ) {
				$length = strlen( $matches[2][0] );

				if ( $compressed ) {
					$stream = self::_decode_flatedecode_stream( $matches[2][0], $object_dictionary['start'] );
				} else {
					$stream = $matches[2][0];
				}

				if ( empty( $stream ) ) {
					$length = 0;
				}
			} else {
				$stream = '';
				$length = 0;
			}

			$stream_dictionary = explode( ' ', substr( $stream, 0, (integer) $dictionary['First']['value'] ) );
			$stream_content = substr( $stream, (integer) $dictionary['First']['value'] );

			$object_count = (integer) $dictionary['N']['value'];
			$object_offsets = array();
			$prior_id = 0;
			$prior_offset = 0;
			while ( $object_count-- ) {
				$current_id = (integer) array_shift( $stream_dictionary );
				$current_offset = (integer) array_shift( $stream_dictionary );
				$object_offsets[ $current_id ] = array( 'offset' => $current_offset );

				if ( $prior_id ) {
					$object_offsets[ $prior_id ]['length'] = $current_offset - $prior_offset;
				}

				$prior_id = $current_id;
				$prior_offset = $current_offset;
			}

			if ( $prior_id ) {
				$current_offset = strlen( $stream_content );
				$object_offsets[ $prior_id ]['length'] = $current_offset - $prior_offset;
			}

			self::$pdf_object_streams[ $indirect_object['stream'] ] = array( 'object_offsets' => $object_offsets, 'stream_content' => $stream_content );
		} // new object stream

		if ( isset( $object_offsets[ $indirect_object['number'] ] ) ) {
			$results = array( 'count' => 1, 'start' => 0, 'length' => $object_offsets[ $indirect_object['number'] ]['length'], 'content' => substr( $stream_content, $object_offsets[ $indirect_object['number'] ]['offset'], $object_offsets[ $indirect_object['number'] ]['length'] ) );

			return $results;
		}

		return NULL;
	}

	/**
	 * Find the offset, length and contents of an indirect object containing a string or array
	 *
	 * The function searches the entire file, if necessary, to find the last/most recent copy of the object.
	 * This is required because Adobe Acrobat does NOT increment the generation number when it reuses an object.
	 * 
	 * @since 3.06
	 *
	 * @param	string	full path and file name
	 * @param	integer	The object number
	 * @param	integer	The object generation number; default zero (0)
	 * @param	integer	The desired object instance (when multiple instances are present); default "highest/latest"
	 *
	 * @return	mixed	NULL on failure else array( 'start' => offset in the file, 'length' => object length, 'content' => object contents )
	 */
	private static function _find_pdf_indirect_value( $file_name, $object, $generation = 0, $instance = NULL ) {
		$chunksize = 16384;
		$key = ( $object * 1000 ) + $generation;
		if ( isset( self::$pdf_indirect_objects ) && isset( self::$pdf_indirect_objects[ $key ] ) ) {
			if ( isset( self::$pdf_indirect_objects[ $key ]['start'] ) ) {
				$file_offset = self::$pdf_indirect_objects[ $key ]['start'];
			} else {
				return self::_find_object_in_stream( $file_name, self::$pdf_indirect_objects[ $key ] );
			}
		} else { // found object location
			$file_offset = 0;
		}

		$object_starts = array();
		$object_content = file_get_contents( $file_name, true, NULL, $file_offset, $chunksize );

		// Match the object header
		$pattern = sprintf( '!%1$d\\h+%2$d\\h+obj[\\x00-\\x20]*([\(|\[])!', $object, $generation );
		$match_count = preg_match( $pattern, $object_content, $matches, PREG_OFFSET_CAPTURE );
		if ( $match_count ) {
			$object_starts[] = array( 'offset' => $file_offset, 'start' => $matches[1][1]);
			$match_count = 0;
		}

		// If necessary and possible, advance the $object_content through the file until it contains the start tag
		if ( 0 == $match_count && ( $chunksize == strlen( $object_content ) ) ) {
			$file_offset += ( $chunksize - 16 );
			$object_content = file_get_contents( $file_name, true, NULL, $file_offset, $chunksize );
			$match_count = preg_match( $pattern, $object_content, $matches, PREG_OFFSET_CAPTURE );

			if ( $match_count ) {
				$object_starts[] = array( 'offset' => $file_offset, 'start' => $matches[1][1]);
				$match_count = 0;
			}

			while ( 0 == $match_count && ( $chunksize == strlen( $object_content ) ) ) {
				$file_offset += ( $chunksize - 16 );
				$object_content = file_get_contents( $file_name, true, NULL, $file_offset, $chunksize );
				$match_count = preg_match( $pattern, $object_content, $matches, PREG_OFFSET_CAPTURE );

				if ( $match_count ) {
					$object_starts[] = array( 'offset' => $file_offset, 'start' => $matches[1][1]);
					$match_count = 0;
				}
			} // while not found
		} // if not found

		// Return the highest/latest instance unless a specific instance is requested
		$object_count = count( $object_starts );
		if ( is_null( $instance ) ) {
			$object_start = array_pop( $object_starts );
		} else {
			$instance = absint( $instance );
			$object_start = isset( $object_starts[ $instance ] ) ? $object_starts[ $instance ] : NULL;
		}

		if ( is_null( $object_start ) ) {
			return NULL;
		} else {
			$file_offset = $object_start['offset'];
			$object_content = file_get_contents( $file_name, true, NULL, $file_offset, $chunksize );
			$start = $object_start['start'];
		}

		// If necessary and possible, expand the $object_content until it contains the end tag
		$pattern = '!([\)|\]])!';
		$match_count = preg_match( $pattern, $object_content, $matches, PREG_OFFSET_CAPTURE, $start );
		if ( 0 == $match_count && ( $chunksize == strlen( $object_content ) ) ) {
			$file_offset = $file_offset + $start;
			$start = 0;
			$new_chunksize = $chunksize + $chunksize;
			$object_content = file_get_contents( $file_name, true, NULL, $file_offset, $new_chunksize );
			$match_count = preg_match( $pattern, $object_content, $matches, PREG_OFFSET_CAPTURE, $start );

			while ( 0 == $match_count && ( $new_chunksize == strlen( $object_content ) ) ) {
				$new_chunksize = $new_chunksize + $chunksize;
				$object_content = file_get_contents( $file_name, true, NULL, $file_offset, $new_chunksize );
				$match_count = preg_match( $pattern, $object_content, $matches, PREG_OFFSET_CAPTURE, $start );
			} // while not found
		} // if not found

		if ( 0 == $match_count ) {
			return NULL;
		}

		if ($match_count) {
			$results = array( 'count' => $object_count, 'start' => $file_offset + $start, 'length' => ($matches[0][1] + 1) - $start );
			$results['content'] = substr( $object_content, $start, $results['length'] );
			return $results;
		} // found trailer

		return NULL; 
	}

	/**
	 * Find the offset, length and contents of an indirect object containing a dictionary
	 *
	 * The function searches the entire file, if necessary, to find the last/most recent copy of the object.
	 * This is required because Adobe Acrobat does NOT increment the generation number when it reuses an object.
	 * 
	 * @since 2.10
	 *
	 * @param	string	full path and file name
	 * @param	integer	The object number
	 * @param	integer	The object generation number; default zero (0)
	 * @param	integer	The desired object instance (when multiple instances are present); default "highest/latest"
	 *
	 * @return	mixed	NULL on failure else array( 'count' => $object_count, 'start' => offset in the file, 'length' => object length, 'content' => dictionary contents )
	 */
	private static function _find_pdf_indirect_dictionary( $file_name, $object, $generation = 0, $instance = NULL ) {
		$chunksize = 16384;			
		$key = ( $object * 1000 ) + $generation;
		if ( isset( self::$pdf_indirect_objects[ $key ] ) ) {
			if ( isset( self::$pdf_indirect_objects[ $key ]['start'] ) ) {
				$file_offset = self::$pdf_indirect_objects[ $key ]['start'];
			} else {
				return self::_find_object_in_stream( $file_name, self::$pdf_indirect_objects[ $key ] );
			}
		} else { // found object location
			$file_offset = 0;
		}

		$object_starts = array();
		$object_content = file_get_contents( $file_name, true, NULL, $file_offset, $chunksize );

		// Match the object header
		$pattern = sprintf( '!%1$d\\h+%2$d\\h+obj[\\x00-\\x20]*(<<)!', $object, $generation );
		$match_count = preg_match( $pattern, $object_content, $matches, PREG_OFFSET_CAPTURE );
		if ( $match_count ) {
			$object_starts[] = array( 'offset' => $file_offset, 'start' => $matches[1][1]);
			$match_count = 0;
		}

		// If necessary and possible, advance the $object_content through the file until it contains the start tag
		if ( 0 == $match_count && ( $chunksize == strlen( $object_content ) ) ) {
			$file_offset += ( $chunksize - 16 );
			$object_content = file_get_contents( $file_name, true, NULL, $file_offset, $chunksize );
			$match_count = preg_match( $pattern, $object_content, $matches, PREG_OFFSET_CAPTURE );

			if ( $match_count ) {
				$object_starts[] = array( 'offset' => $file_offset, 'start' => $matches[1][1]);
				$match_count = 0;
			}

			while ( 0 == $match_count && ( $chunksize == strlen( $object_content ) ) ) {
				$file_offset += ( $chunksize - 16 );
				$object_content = file_get_contents( $file_name, true, NULL, $file_offset, $chunksize );
				$match_count = preg_match( $pattern, $object_content, $matches, PREG_OFFSET_CAPTURE );

				if ( $match_count ) {
					$object_starts[] = array( 'offset' => $file_offset, 'start' => $matches[1][1]);
					$match_count = 0;
				}
			} // while not found
		} // if not found

		// Return the highest/latest instance unless a specific instance is requested
		$object_count = count( $object_starts );
		if ( is_null( $instance ) ) {
			$object_start = array_pop( $object_starts );
		} else {
			$instance = absint( $instance );
			$object_start = isset( $object_starts[ $instance ] ) ? $object_starts[ $instance ] : NULL;
		}

		if ( is_null( $object_start ) ) {
			return NULL;
		} else {
			$file_offset = $object_start['offset'];
			$object_content = file_get_contents( $file_name, true, NULL, $file_offset, $chunksize );
			$start = $object_start['start'];
		}

		// If necessary and possible, expand the $object_content until it contains the end tag
		$pattern = '!>>[\\x00-\\x20]*[endobj|stream]!';
		$match_count = preg_match( $pattern, $object_content, $matches, PREG_OFFSET_CAPTURE, $start );
		if ( 0 == $match_count && ( $chunksize == strlen( $object_content ) ) ) {
			$file_offset = $file_offset + $start;
			$start = 0;
			$new_chunksize = $chunksize + $chunksize;
			$object_content = file_get_contents( $file_name, true, NULL, $file_offset, $new_chunksize );
			$match_count = preg_match( $pattern, $object_content, $matches, PREG_OFFSET_CAPTURE, $start );

			while ( 0 == $match_count && ( $new_chunksize == strlen( $object_content ) ) ) {
				$new_chunksize = $new_chunksize + $chunksize;
				$object_content = file_get_contents( $file_name, true, NULL, $file_offset, $new_chunksize );
				$match_count = preg_match( $pattern, $object_content, $matches, PREG_OFFSET_CAPTURE, $start );
			} // while not found
		} // if not found

		if ( 0 == $match_count ) {
			return NULL;
		}

		if ($match_count) {
			$results = array( 'count' => $object_count, 'start' => $file_offset + $start, 'length' => ($matches[0][1] + 2) - $start );
			$results['content'] = substr( $object_content, $start, $results['length'] );
			return $results;
		} // found trailer

		return NULL; 
	}

	/**
	 * Parse a PDF Unicode (16-bit Big Endian) object
	 * 
	 * @since 2.10
	 *
	 * @param	string	PDF string of 16-bit characters
	 *
	 * @return	string	UTF-8 encoded string
	 */
	private static function _parse_pdf_UTF16BE( &$source_string ) {
		$output = '';
		for ($index = 2; $index < strlen( $source_string ); ) {
			$value = ( ord( $source_string[ $index++ ] ) << 8 ) + ord( $source_string[ $index++ ] );
 			if ( $value < 0x80 ) {
				$output .= chr( $value );
			} elseif ( $value < 0x100 ) {
				$output .= MLAData::$utf8_chars[ $value - 0x80 ];
			} else {
				$output .= '.'; // TODO encode the rest
			}
		}

		return $output;
	}

	/**
	 * Parse a PDF string object
	 * 
	 * Returns an array with one dictionary entry. The array also has a '/length' element containing
	 * the number of bytes occupied by the string in the source string, including the enclosing parentheses. 
	 *
	 * @since 2.10
	 *
	 * @param	string	data within which the string occurs
	 * @param	integer	offset within the source string of the opening '(' character.
	 *
	 * @return	array	( key => array( 'type' => type, 'value' => value, '/length' => length ) ) for the string
	 */
	private static function _parse_pdf_string( &$source_string, $offset ) {
		if ( '(' != $source_string[ $offset ] ) {
			return array( 'type' => 'unknown', 'value' => '', '/length' => 0 );
		}

		// Brute force, here we come...
		$output = '';
		$level = 0;
		$in_string = true;
		$index = $offset + 1;
		while ( $in_string ) {
			$byte = $source_string[ $index++ ];
			if ( '\\' == $byte ) {
				switch ( $source_string[ $index ] ) {
					case chr( 0x0A ):
						if ( chr( 0x0D ) == $source_string[ $index + 1 ] ) {
							$index++;
						}

						break;
					case chr( 0x0D ):
						if ( chr( 0x0A ) == $source_string[ $index + 1 ] ) {
							$index++;
						}

						break;
					case 'n':
						$output .= chr( 0x0A );
						break;
					case 'r':
						$output .= chr( 0x0D );
						break;
					case 't':
						$output .= chr( 0x09 );
						break;
					case 'b':
						$output .= chr( 0x08 );
						break;
					case 'f':
						$output .= chr( 0x0C );
						break;
					default: // could be a 1- to 3-digit octal value
						$digit_limit = $index + 3;
						$digit_index = $index;
						while ( $digit_index < $digit_limit ) {
							if ( ! ctype_digit( $source_string[ $digit_index ] ) ) {
								break;
							} else {
								$digit_index++;
							}
						}

						if ( $digit_count = $digit_index - $index ) {
							$output .= chr( octdec( substr( $source_string, $index, $digit_count ) ) );
							$index += $digit_count - 1;
						} else { // accept the character following the backslash
							$output .= $source_string[ $index ];
						}
				} // switch

				$index++;
			} else { // REVERSE SOLIDUS
				if ( '(' == $byte ) {
					$level++;
				} elseif ( ')' == $byte ) {
					if ( 0 == $level-- ) {
						$in_string = false;
						continue;
					}
				}

				$output .= $byte;
			} // just another 8-bit value, but check for balanced parentheses
		} // $in_string

		return array( 'type' => 'string', 'value' => $output, '/length' => $index - $offset );
	}

	/**
	 * Parse a PDF Linearization Parameter Dictionary object
	 * 
	 * Returns an array of dictionary contents, classified by object type: boolean, numeric, string, hex (string),
	 * indirect (object), name, array, dictionary, stream, and null.
	 * The array also has a '/length' element containing the number of bytes occupied by the
	 * dictionary in the source string, excluding the enclosing delimiters, if passed in.
	 * @since 2.10
	 *
	 * @param	string	data within which the object occurs, typically the start of a PDF document
	 * @param	integer	filesize of the PDF document, for validation purposes, or zero (0) to ignore filesize
	 *
	 * @return	mixed	array of dictionary objects on success, false on failure
	 */
	private static function _parse_pdf_LPD_dictionary( &$source_string, $filesize ) {
		$header = substr( $source_string, 0, 1024 );
		$match_count = preg_match( '!obj[\x00-\x20]*<<(/Linearized).*(>>)[\x00-\x20]*endobj!', $header, $matches, PREG_OFFSET_CAPTURE );

		if ( $match_count ) {
			$LPD = self::_parse_pdf_dictionary( $header, $matches[1][1] );
		}

		return false;
	}

	/**
	 * Parse a PDF dictionary object
	 * 
	 * Returns an array of dictionary contents, classified by object type: boolean, numeric, string, hex (string),
	 * indirect (object), name, array, dictionary, stream, and null.
	 * The array also has a '/length' element containing the number of bytes occupied by the
	 * dictionary in the source string, excluding the enclosing delimiters.
	 *
	 * @since 2.10
	 *
	 * @param	string	data within which the string occurs
	 * @param	integer	offset within the source string of the opening '<<' characters or the first content character.
	 *
	 * @return	array	( '/length' => length, key => array( 'type' => type, 'value' => value ) ) for each dictionary field
	 */
	private static function _parse_pdf_dictionary( &$source_string, $offset ) {
		static $byte_values = array ( '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, 'A' => 0xA, 'B' => 0xB, 'C' => 0xC, 'D' => 0xD, 'E' => 0xE, 'F' => 0xF, );

		// Find the end of the dictionary
		if ( '<<' == substr( $source_string, $offset, 2 ) ) {
			$nest = $offset + 2;
		} else {
			$nest = $offset;
		}

		$level = 1;
		do {
			$dictionary_end = strpos( $source_string, '>>', $nest );
			if ( false === $dictionary_end ) {
					/* translators: 1: ERROR tag 2: source offset 3: nest level */
				MLACore::mla_debug_add( sprintf( _x( '%1$s: _parse_pdf_dictionary offset = %2$d, nest = %3$d.', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), $offset, $nest ), MLACore::MLA_DEBUG_CATEGORY_METADATA );
					/* translators: 1: ERROR tag 2: dictionary excerpt */
				MLACore::mla_debug_add( sprintf( _x( '%1$s: _parse_pdf_dictionary no end delimiter dump = %2$s.', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), MLAData::mla_hex_dump( substr( $source_string, $offset, 128 ), 128, 16 ) ), MLACore::MLA_DEBUG_CATEGORY_METADATA );
				return array( '/length' => 0 );
			}

			$nest = strpos( $source_string, '<<', $nest );
			if ( false === $nest ) {
				$nest = $dictionary_end + 2;
				$level--;
			} elseif ( $nest < $dictionary_end ) {
				$nest += 2;
				$level++;
			} else {
				$nest = $dictionary_end + 2;
				$level--;
			}
		} while ( $level );

		$dictionary_length = $dictionary_end + 2 - $offset;
		$dictionary = array();

		// \x00-\x20 for whitespace
		// \(|\)|\<|\>|\[|\]|\{|\}|\/|\% for delimiters
		$match_count = preg_match_all( '!/([^\x00-\x20|\(|\)|\<|\>|\[|\]|\{|\}|\/|\%]*)([\x00-\x20]*)!', substr( $source_string, $offset, $dictionary_length ), $matches, PREG_OFFSET_CAPTURE );
		$end_data = -1;
		for ( $match_index = 0; $match_index < $match_count; $match_index++ ) {
			$name = $matches[1][ $match_index ][0];
			$value_start = $offset + $matches[2][ $match_index ][1] + strlen( $matches[2][ $match_index ][0] );

			// Skip over false matches within a string or nested dictionary
			if ( $value_start < $end_data ) {
				continue;
			}

			$end_data = -1;
			$value_count = preg_match(
				'!(\/?[^\/\x0D\x0A]*)!',
				substr( $source_string, $value_start, ($dictionary_end - $value_start ) ), $value_matches, PREG_OFFSET_CAPTURE );

			if ( 1 == $value_count ) {
				$value = trim( $value_matches[0][0] );
				$length = strlen( $value );
				$dictionary[ $name ]['value'] = $value;
				if ( ! isset( $value[0] ) ) {
					/* translators: 1: ERROR tag 2: entry name 3: value excerpt */
					MLACore::mla_debug_add( sprintf( _x( '%1$s: _parse_pdf_dictionary bad value [ %2$s ] dump = %3$s', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), $name, MLAData::mla_hex_dump( $value, 32, 16 ) ), MLACore::MLA_DEBUG_CATEGORY_METADATA );
					continue;
				}

				if ( in_array( $value, array( 'true', 'false' ) ) ) {
					$dictionary[ $name ]['type'] = 'boolean';
				} elseif ( is_numeric( $value ) ) {
					$dictionary[ $name ]['type'] = 'numeric';
				} elseif ( '(' == $value[0] ) {
					$dictionary[ $name ] = self::_parse_pdf_string( $source_string, $value_start );
					$end_data = $value_start + $dictionary[ $name ]['/length'];
					unset( $dictionary[ $name ]['/length'] );
				} elseif ( '<' == $value[0] ) {
					if ( '<' == $value[1] ) {
						$dictionary[ $name ]['value'] = self::_parse_pdf_dictionary( $source_string, $value_start );
						$dictionary[ $name ]['type'] = 'dictionary';
						$end_data = $value_start + 4 + $dictionary[ $name ]['value']['/length'];
						unset( $dictionary[ $name ]['value']['/length'] );
					} elseif ( 0 === strpos( $value, '<FEFF' ) ) {
						// UTF-16BE (big-endian) string encoding: 2 hex characters per byte TODO
						$utf_string = '';
						$limit = strlen( $value ) - 1;
						for ( $index = 1; $index < $limit; $index++ ) {
							// Encode upper digit
							$byte = 0x10 * $byte_values[ $value[ $index++ ] ];
							// Encode lower (or missing, assumed zero) digit
							if ( $index < $limit ) {
								$byte = $byte + $byte_values[ $value[ $index ] ];
							}

							$utf_string .= chr( $byte );
						}

						$dictionary[ $name ]['value'] = $utf_string;
						$dictionary[ $name ]['type'] = 'string';
					} else {
						$dictionary[ $name ]['type'] = 'hex';
					}
				} elseif ( '/' == $value[0] ) {
					$dictionary[ $name ]['value'] = substr( $value, 1 );
					$dictionary[ $name ]['type'] = 'name';
					$match_index++; // Skip to the next key
				} elseif ( '[' == $value[0] ) {
					$dictionary[ $name ]['type'] = 'array';
					$array_length = strpos( $source_string, ']', $value_start ) - ($value_start + 1);
					$dictionary[ $name ]['value'] = substr( $source_string, $value_start + 1, $array_length );
					$end_data = 2 + $value_start + $array_length;
				} elseif ( 'null' == $value ) {
					$dictionary[ $name ]['type'] = 'null';
				} elseif ( 'stream' == substr( $value, 0, 6 ) ) {
					$dictionary[ $name ]['type'] = 'stream';
				} else {
					$object_count = preg_match( '!(\d+)\h+(\d+)\h+R!', $value, $object_matches );

					if ( 1 == $object_count ) {
						$dictionary[ $name ]['type'] = 'indirect';
						$dictionary[ $name ]['object'] = $object_matches[1];
						$dictionary[ $name ]['generation'] = $object_matches[2];
					} else {
						$dictionary[ $name ]['type'] = 'unknown';
					}
				}
			} else {
				$dictionary[ $matches[1][ $match_index ][0] ] = array( 'value' => '' );
				$dictionary[ $matches[1][ $match_index ][0] ]['type'] = 'nomatch';
			}
		} // foreach match

		$dictionary['/length'] = $dictionary_length;
		return $dictionary;
	}

	/**
	 * Extract dictionary from traditional cross-reference + trailer documents
	 * 
	 * @since 2.10
	 *
	 * @param	string	full path to the desired file
	 * @param	integer	offset within file of the cross-reference table
	 *
	 * @return	mixed	array of "PDF dictionary arrays", newest first, or NULL on failure
	 */
	private static function _extract_pdf_trailer( $file_name, $file_offset ) {
		$chunksize = 16384; 
		$tail = file_get_contents( $file_name, true, NULL, $file_offset, $chunksize );
		$chunk_offset = 0;

		// look for traditional xref and trailer
		if ( 'xref' == substr( $tail, $chunk_offset, 4 ) ) {
			$xref_length =	self::_parse_pdf_xref_section( $file_name, $file_offset + $chunk_offset + 4 );
			$chunk_offset += 4 + $xref_length;

			if ( $chunk_offset > ( $chunksize - 1024 ) ) {
				$file_offset += $chunk_offset;
				$tail = file_get_contents( $file_name, true, NULL, $file_offset, $chunksize );
				$chunk_offset = 0; 
			}

			$match_count = preg_match( '/[\x00-\x20]*trailer[\x00-\x20]+/', $tail, $matches, PREG_OFFSET_CAPTURE, $chunk_offset );
			if ( $match_count ) {
				$chunk_offset = $matches[0][1] + strlen( $matches[0][0] );
				$dictionary = self::_parse_pdf_dictionary( $tail, $chunk_offset );

				if ( isset( $dictionary['Prev'] ) ) {
					$other_trailers =  self::_extract_pdf_trailer( $file_name, $dictionary['Prev']['value'] );
				} else {
					$other_trailers = NULL;
				}

				if ( is_array( $other_trailers ) ) {
					$other_trailers = array_merge( $other_trailers, array( $dictionary ) );
					return $other_trailers;
				} else {
					return array( $dictionary );
				}
			} // found 'trailer'
		} else { // found 'xref'
		// Look for a cross-reference stream
		$match_count = preg_match( '!(\d+)\\h+(\d+)\\h+obj[\x00-\x20]*!', $tail, $matches, PREG_OFFSET_CAPTURE );
		if ( $match_count ) {
			$chunk_offset = $matches[0][1] + strlen( $matches[0][0] );

			if ( '<<' == substr( $tail, $chunk_offset, 2) ) {
				$dictionary = self::_parse_pdf_dictionary( $tail, $chunk_offset );

				// Parse the cross-reference stream following the dictionary, if present
				if ( isset( $dictionary['Type'] ) && 'XRef' == $dictionary['Type']['value'] ) {
					$offset = $file_offset + $chunk_offset + (integer) $dictionary['/length'];
					$entry_parms_string = $dictionary['W']['value'];
					$index_string = isset( $dictionary['Index'] ) ? $dictionary['Index']['value'] : '0 ' . $dictionary['Size']['value'];
					$compressed = isset( $dictionary['Filter'] ) && 'FlateDecode' == $dictionary['Filter']['value']; 
		 			$xref_length =	self::_parse_pdf_xref_stream( $file_name, $offset, $entry_parms_string, $index_string, $compressed );
				}

				if ( isset( $dictionary['Prev'] ) ) {
					$other_trailers =  self::_extract_pdf_trailer( $file_name, $dictionary['Prev']['value'] );
				} else {
					$other_trailers = NULL;
				}

				if ( is_array( $other_trailers ) ) {
					$other_trailers = array_merge( array( $dictionary ), $other_trailers );
					return $other_trailers;
				} else {
					return array( $dictionary );
				}
			} // found cross-reference stream dictionary
		} // found cross-reference stream object
	}

		return NULL;
	}

	/**
	 * Extract Metadata from a PDF file
	 * 
	 * @since 2.10
	 *
	 * @param	string	full path to the desired file
	 *
	 * @return	array	( 'xmp' => array( key => value ), 'pdf' => array( key => value ) ) for each metadata field, in string format
	 */
	public static function mla_extract_pdf_metadata( $file_name ) {
		$xmp = NULL;
		$metadata = array();
		self::$pdf_indirect_objects = array();
		self::$pdf_object_streams = array();
		$chunksize = 16384; // 262144; // 

		if ( ! file_exists( $file_name ) ) {
			return array( 'xmp' => array(), 'pdf' => $metadata );
		}

		$filesize = filesize( $file_name );
		$file_offset = ( $chunksize < $filesize ) ? ( $filesize - $chunksize ) : 0;
		$tail = file_get_contents( $file_name, false, NULL, $file_offset );

		if ( 0 == $file_offset ) {
			$header = substr( $tail, 0, 128 );
		} else {
			$header = file_get_contents( $file_name, false, NULL, 0, 128 );
		}

		if ( '%PDF-' == substr( $header, 0, 5 ) ) {
			$metadata['PDF_Version'] = substr( $header, 1, 7 );
			$metadata['PDF_VersionNumber'] = substr( $header, 5, 3 );
		}

		// Find the xref and (optional) trailer
		$match_count = preg_match_all( '/startxref[\x00-\x20]+(\d+)[\x00-\x20]+\%\%EOF/', $tail, $matches, PREG_OFFSET_CAPTURE );
		if ( 0 == $match_count ) {
			/* translators: 1: ERROR tag 2: path and file */
			MLACore::mla_debug_add( sprintf( _x( '%1$s: File "%2$s", startxref not found.', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), $path ), MLACore::MLA_DEBUG_CATEGORY_METADATA );
			return array( 'xmp' => array(), 'pdf' => $metadata );
		}

		$startxref = (integer) $matches[1][ $match_count - 1 ][0];
		$trailer_dictionaries = self::_extract_pdf_trailer( $file_name, $startxref );
		if ( is_array( $trailer_dictionaries ) ) {
			$info_reference = NULL;
			foreach ( $trailer_dictionaries as $trailer_dictionary ) {
				if ( isset( $trailer_dictionary['Info'] ) ) {
					$info_reference = $trailer_dictionary['Info'];
					break;
				}
			}

			if ( isset( $info_reference ) ) {	
				$info_object = self::_find_pdf_indirect_dictionary( $file_name, $info_reference['object'], $info_reference['generation'] );

				// Handle single or multiple Info instances
				$info_objects = array();
				if ( $info_object ) {
					if ( 1 === $info_object['count'] ) {
						$info_objects[] = $info_object;
					} else {
						for ( $index = 0; $index < $info_object['count']; $index++ ) {
							$info_objects[] = self::_find_pdf_indirect_dictionary( $file_name, $info_reference['object'], $info_reference['generation'], $index );
						}
					}
				}

				foreach( $info_objects as $info_object ) {
					$info_dictionary = self::_parse_pdf_dictionary( $info_object['content'], 0 );
					unset( $info_dictionary['/length'] );

					foreach ( $info_dictionary as $name => $value ) {
						 if ( 'indirect' == $value['type'] ) { //TODO FIND INDIRECT VALUE, NOT DICTIONARY
							$indirect_value = self::_find_pdf_indirect_value( $file_name, $value['object'], $value['generation'] );
							if ( is_array( $indirect_value ) ) {
								$content = $indirect_value['content'];
								if ( '(' === $content[0] ) {
									$value['value'] = substr( $content, 1, strlen( $content ) - 2 );
									$value['type'] = 'string';
								} elseif ( '[' === $content[0] ) {
									$value['value'] = trim( substr( $content, 1, strlen( $content ) - 2 ) );
									$value['type'] = 'array';
								}
							}
						 }

						if ( 'string' == $value['type'] ) {
							$prefix = substr( $value['value'], 0, 2 );
							if ( 'D:' == $prefix ) {
								$metadata[ $name ] = MLAData::mla_parse_pdf_date( $value['value'] );
							} elseif ( ( chr(0xFE) . chr(0xFF) ) == $prefix )  {
								$metadata[ $name ] = self::_parse_pdf_UTF16BE( $value['value'] );
							} else {
								$metadata[ $name ] = $value['value'];
							}
						 } elseif ( 'indirect' == $value['type'] ) {
							// Failed to match the object to its value above
							$metadata[ $name ] = $value['value'];
						 } else {
							$metadata[ $name ] = $value['value'];
						 }
					} // each info entry
				} // foreach Info object

				// Remove spurious "Filter" dictionaries
				unset( $metadata['Filter'] );
				unset( $metadata['Length'] );
				unset( $metadata['Length1'] );
			} // found Info reference

			// Look for XMP Metadata
			$root_reference = NULL;
			foreach ( $trailer_dictionaries as $trailer_dictionary ) {
				if ( isset( $trailer_dictionary['Root'] ) ) {
					$root_reference = $trailer_dictionary['Root'];
					break;
				}
			}

			if ( isset( $root_reference ) ) {	
				$root_object = self::_find_pdf_indirect_dictionary( $file_name, $root_reference['object'], $root_reference['generation'] );
				if ( $root_object ) {
					$root_dictionary = self::_parse_pdf_dictionary( $root_object['content'], 0 );
					unset( $root_dictionary['/length'] );

					if ( isset( $root_dictionary['Metadata'] ) ) {
						$xmp_object = self::_find_pdf_indirect_dictionary( $file_name, $root_dictionary['Metadata']['object'], $root_dictionary['Metadata']['generation'] );
						$xmp = MLAData::mla_parse_xmp_metadata( $file_name, $xmp_object['start'] + $xmp_object['length'] );
						if ( is_array( $xmp ) ) {
							$metadata = array_merge( $metadata, $xmp );
						}
					} // found Metadata reference
				} // found Root object
			} // found Root reference
		} // found trailer_dictionaries

		// Last try for XML recovery
		if ( is_null( $xmp ) ) {
			$xmp = MLAData::mla_parse_xmp_metadata( $file_name, 0 );

			if ( is_array( $xmp ) ) {
				// Add scalar values to pdf: array to populate as many  D.I.D. entries as possible
				foreach ( $xmp as $key => $value ) {
					if ( is_scalar( $value ) ) {
						$metadata[ $key ] = $value;
					}
				}
			} else {
				$xmp = array();
			}
		}

		return array( 'xmp' => $xmp, 'pdf' => $metadata );
	}
} // class MLAPDF
?>