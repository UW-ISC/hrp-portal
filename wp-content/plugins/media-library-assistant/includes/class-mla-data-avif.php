<?php
/**
 * Meta data parsing functions for HEIC/AVIF image files
 *
 * @package Media Library Assistant
 * @since 3.27
 */

/**
 * Class MLA (Media Library Assistant) AVIF extracts meta data from HEIC/AVIF image files
 *
 * @package Media Library Assistant
 * @since 3.27
 */
class MLAAVIF {
	/**
	 * Extract Metadata from an HEIC/AVIF image
	 * 
	 * @since 3.27
	 *
	 * @param	string	full path to the desired file
	 *
	 * @return	array	( 'avif' => array( key => value ) ) for each metadata field, in string format
	 */
	public static function mla_extract_AVIF_metadata( $file_name ) {
		$metadata = array();

		if ( ! file_exists( $file_name ) ) {
			$metadata['mla_avif_exif_errors'] = "File does not exist: $filepath";
		} else {
			$metadata = self::parseHeifBoxes( $file_name );
		}
		
		return $metadata;
	}

	/**
	 * Decode the box structure of an HEIC/AVIF image
	 * 
	 * @since 3.27
	 *
	 * @param	string	full path to the desired file
	 *
	 * @return	array	( 'mso' => array( key => value ) ) for each metadata field, in string format
	 */
	private static function parseHeifBoxes( string $filepath ) {
		$results = array();

		$fp = fopen( $filepath, 'rb' );
		if ( !$fp ) {
			$results['mla_avif_exif_errors'] = "Unable to open file: $filepath";
			return $results;
		}

		$filesize = filesize( $filepath );
    	$boxes = self::parseBoxLevel( $fp, 0, $filesize );
//error_log( __LINE__ . "  MLAAVIF::parseHeifBoxes boxes = " . var_export( $boxes, true ), 0 );

		$ilocItems = array();
		$infeEntries = array();
		$item_properties = array();
		$width = 1;
		$height = 1;

		foreach( $boxes as $box ) {
			if ( 'meta' === $box['type'] ) {
				foreach( $box['children'] as $child ) {
					switch( $child['type'] ) {
						case 'iloc': // item location
							$ilocItems = $child['iloc'];
							break;
						case 'iinf': // item information
							foreach( $child['iinf'] as $iinf ) {
								$infeEntries[ $iinf['item_id'] ] = $iinf;
							}
							break;
						case 'iprp': // item properties
							foreach( $child['ipco'] as $ipco ) {
								$item_properties[ $ipco['type'] ] = $ipco['data'];
								
								if ( 'ispe' === $ipco['type'] ) {
									$width = $ipco['data']['width'];
									$height = $ipco['data']['height'];
								}
							}
							break;
						default:
					}
				}
			}
		}
		
		$exif = self::extractItemsByType( $fp, $ilocItems, $infeEntries, 'exif' );
		if ( ! empty( $exif ) ) {
			// AVIF raw data has an unknown 4-byte prefix
			$exif_array = MLAData::mla_convert_raw_exif_metadata( substr( $exif['binary'], 4 ), $width, $height );

			// Replace the exif.jpg values with the original avif file values
			$exif_array['exif_metadata']['FileName'] = pathinfo( $filepath, PATHINFO_BASENAME );
			$exif_array['exif_metadata']['FileDateTime'] = (integer) filectime( $filepath ); // Date uploaded
			$exif_array['exif_metadata']['FileSize'] = filesize( $filepath );
			$exif_array['exif_metadata']['MimeType'] = 'image/avif';
			$exif_array['exif_metadata']['COMPUTED']['IsColor'] = 1;
		} else {
			$exif_array = array();
		}
		
		if ( ! empty( $exif_array['errors'] ) ) {
			$results['mla_avif_exif_errors'] = $exif_array['errors'];
		}
		
		if ( ! empty( $exif_array['exif_metadata'] ) ) {
			$results['mla_avif_exif'] = $exif_array['exif_metadata'];
		}
		
		if ( ! empty( $item_properties ) ) {
			$results['mla_avif_item_properties'] = $item_properties;
		}
		
		$results['mla_avif_boxes'] = $boxes;
//error_log( __LINE__ . " MLAAVIF::parseHeifBoxes results = " . var_export( $results, true ), 0 );
		return $results;
		
		// NOTHING BELOW THIS LINE IS EXECUTED	
		rewind( $fp );

		while ( !feof( $fp ) ) {
			$boxStart = ftell( $fp );
	
			// Read box size and type
			$header = fread( $fp, 8 );
			if ( strlen($header) < 8 ) {
				$results['mla_avif_exif_errors'] = "Truncated file header: $filepath";
				 break;
			}
//error_log( __LINE__ . "  MLAAVIF::parseHeifBoxes header dump = \r\n" . MLAData::mla_hex_dump( $header, 0, 32, 0 ), 0 );
			$data = unpack( "Nsize/a4type", $header );
			$size = $data['size'];
			$type = $data['type'];
	
			// Handle extended size (size == 1)
			if ( $size === 1 ) {
				$largeSize = fread($fp, 8);
				$data = unpack("J", $largeSize);
				$size = $data[1];
				$headerSize = 16;
			} else {
				$headerSize = 8;
			}
	
			$boxInfo = array(
				'offset' => $boxStart,
				'type' => $type,
				'size' => $size,
			);
	
			// If this is the ftyp box, extract brand info
			if ( $type === 'ftyp' ) {
				$ftypData = fread( $fp, $size - $headerSize );
				$brandInfo = unpack( "a4majorBrand/NminorVersion", substr( $ftypData, 0, 8 ) );
				$compatibleBrands = array();
	
				for ( $i = 8; $i < strlen( $ftypData ); $i += 4 ) {
					$brand = substr( $ftypData, $i, 4 );
					if ( strlen($brand) === 4 ) {
						$compatibleBrands[] = $brand;
					}
				}
	
				$boxInfo['major_brand'] = $brandInfo['majorBrand'];
				$boxInfo['minor_version'] = $brandInfo['minorVersion'];
				$boxInfo['compatible_brands'] = $compatibleBrands;
			} else {
				// the 'mdat' box has length = 0; finish up
				if ( 0 === $size ) {
					$results[] = $boxInfo;
					break;
				}
				
				// Skip the box payload
				fseek( $fp, $boxStart + $size, SEEK_SET );
			}
	
			$results[] = $boxInfo;
		}
	
		fclose( $fp );
		return $results;
	}

	/**
	 * Decode the box structure of an HEIC/AVIF image
	 * 
	 * @since 3.27
	 *
	 * @param	resource file pointer
	 * @param	integer  box(es) offset within the file
	 * @param	integer  payload length
	 *
	 * @return	array	box descriptor(s)
	 */
	private static function parseBoxLevel( $fp, int $start, int $length ) {
		$boxes = array();
		$position = $start;
	
		fseek( $fp, $start );
	
		while ($position < $start + $length) {
			fseek( $fp, $position );
	
			$header = fread( $fp, 8 );
			if ( strlen($header) < 8 ) {
				$boxes['mla_avif_exif_errors'] = "Truncated box header at offset $position";
				 break;
			}
	
			$data = unpack("Nsize/a4type", $header);
			$size = $data['size'];
			$type = $data['type'];
			$headerSize = 8;
	
			// Extended size (size == 1)
			if ( $size === 1 ) {
				$extended = fread( $fp, 8 );
				$data = unpack("J", $largeSize);
				$size = $data[1];
				$headerSize = 16;
			}
	
			if ( $size === 0 ) {
				// box extends to end of file
				$size = $length - ($position - $start);
			}
	
			$box = array(
				'offset' => $position,
				'type' => $type,
				'size' => $size,
			);

			switch( $type ) {
				case 'ftyp':
					$payload = fread( $fp, $size - $headerSize );
					$brandInfo = unpack( "a4major/Nminor", substr( $payload, 0, 8 ) );
					$compatibleBrands = array();
		
					for ( $i = 8; $i < strlen( $payload ); $i += 4 ) {
						$brand = substr( $payload, $i, 4 );
						if (strlen( $brand ) === 4) {
							$compatibleBrands[] = $brand;
						}
					}
		
					$box['major_brand'] = $brandInfo['major'];
					$box['minor_version'] = $brandInfo['minor'];
					$box['compatible_brands'] = $compatibleBrands;
					break;
				case 'iloc':
					fseek( $fp, $position + $headerSize );
					$iloc = fread( $fp, $size - $headerSize );
					$box['iloc'] = self::parseIlocBox( $iloc );
					break;
			
				case 'iinf':
					fseek( $fp, $position + $headerSize );
					$iinf = fread( $fp, $size - $headerSize );
					$box['iinf'] = self::parseIinfBox( $iinf );
					break;
			
				case 'iprp':
					$childOffset = $position + $headerSize;
					$childLength = $size - $headerSize;
					$box['iprp'] = self::parseBoxLevel( $fp, $childOffset, $childLength );
					
					foreach ( $box['iprp'] as $child ) {
						if ($child['type'] === 'ipco') {
							$box['ipco'] = self::parseIpcoBox($fp, $child);
						} elseif ($child['type'] === 'ipma') {
							$box['ipma'] = self::parseIpmaBox($fp, $child);
						}
					}
					
					break;
				default:	
					// Boxes that may contain children (e.g., meta, moov, iprp, etc.)
					$containerBoxes = array( 'meta', 'moov', 'trak', 'mdia', 'minf', 'dinf', 'stbl', 'iprp', 'iloc', 'iinf' );
					if ( in_array( $type, $containerBoxes ) ) {
						// meta box has a 4-byte version/flags field
						$versionOffset = ( $type === 'meta' ) ? 4 : 0;
						fseek( $fp, $position + $headerSize + $versionOffset );
						$childOffset = $position + $headerSize + $versionOffset;
						$childLength = $size - $headerSize - $versionOffset;
						$box['children'] = self::parseBoxLevel( $fp, $childOffset, $childLength );
					}
			}
	
			$boxes[] = $box;
			$position += $size;
		}
	
		return $boxes;
	}

	/**
	 * Decode the item location (iloc) box in an HEIC/AVIF image
	 * 
	 * @since 3.27
	 *
	 * @param	string  box payload
	 *
	 * @return	array	box descriptor(s)
	 */
	private static function parseIlocBox( string $data ) {
//error_log( __LINE__ . "  MLAAVIF::parseIlocBox data dump = \r\n" . MLAData::mla_hex_dump( $data, 0, 32, 0 ), 0 );
		$result = array();
	
		$offset = 0;
		$version = ord( $data[$offset] );
		$offset += 1;
		$offset += 3; // flags
	
		$offsetSize = ( ord( $data[$offset] ) & 0xF0 ) >> 4;
		$lengthSize = ord( $data[$offset] ) & 0x0F;
		$offset++;
		$baseOffsetSize = ( ord( $data[ $offset ] ) & 0xF0 ) >> 4;
		$indexSize = ( $version === 1 || $version === 2 ) ? ( ord( $data[ $offset ] ) & 0x0F ) : null;
		$offset++;
	
		if ( $version < 2 ) {
			$itemCount = unpack( "n", substr($data, $offset, 2 ) );
			$itemCount = $itemCount[1];
			$offset += 2;
		} else {
			$itemCount = unpack( "n", substr($data, $offset, 4 ) );
			$itemCount = $itemCount[1];
			$offset += 4;
		}

		for ( $i = 0; $i < $itemCount; $i++ ) {
			if ( $version < 2 ) {
				$itemId = unpack( "n", substr($data, $offset, 2 ) );
				$itemId = $itemId[1];
				$offset += 2;
			} else {
				$itemId = unpack( "n", substr($data, $offset, 4 ) );
				$itemId = $itemId[1];
				$offset += 4;
			}
	
			if ($version >= 1) {
				$offset += 2; // construction_method
			}
	
			$offset += 2; // data_reference_index

			if ( $baseOffsetSize ) {
				$baseOffset = unpack( "N", substr( $data, $offset, 4 ) ); // assume 4-byte baseOffset
				$baseOffset = $baseOffset[1];
				$offset += 4;
			} else {
				$baseOffset = 0;
			}
	
			$extentCount = unpack( "n", substr( $data, $offset, 2 ) );
			$extentCount = $extentCount[1];
			$offset += 2;

			$extents = array();
			for ( $j = 0; $j < $extentCount; $j++ ) {
				if ( $version >= 1 && $indexSize !== null ) {
					$offset += $indexSize; // extent_index
				}
	
				$extentOffset = unpack( "N", substr( $data, $offset, $offsetSize ) );
				$extentOffset = $extentOffset[1];
				$offset += $offsetSize;
				$extentLength = unpack( "N", substr( $data, $offset, $lengthSize ) );
				$extentLength = $extentLength[1];
				$offset += $lengthSize;
	
				$extents[] = array( 'offset' => $extentOffset, 'length' => $extentLength, 'absolute_offset' => $baseOffset + $extentOffset );
			}
	
			$result[] = array(
				'item_id' => $itemId,
				'base_offset' => $baseOffset,
				'extents' => $extents
			);
		}
	
		return $result;
	}

	/**
	 * Decode the item info (iinf) box in an HEIC/AVIF image
	 * 
	 * @since 3.27
	 *
	 * @param	string  box payload
	 *

	 * @return	array	box descriptor(s)
	 */
	private static function parseIinfBox( string $data ) {
//error_log( __LINE__ . "  MLAAVIF::parseIinfBox data dump = \r\n" . MLAData::mla_hex_dump( $data, 0, 32, 0 ), 0 );
		$offset = 0;
		$version = ord( $data[ $offset ] );
		$offset += 1;
		$offset += 3; // flags
	
		$entryCount = unpack("n", substr($data, $offset, 2));
		$entryCount = $entryCount[1];
		$offset += 2;
	
		$entries = array();
		for ( $i = 0; $i < $entryCount; $i++ ) {
			$itemLength = unpack("N", substr($data, $offset, 4));
			$itemLength = $itemLength[1];
			$itemEnd = $offset + $itemLength;
			$offset += 4;
			
			$entryType = substr( $data, $offset, 4 );
			$offset += 4;
			
			$itemVersion = ord( $data[ $offset ] );
			$offset += 1;
			$offset += 3; // flags
	
			$itemId = unpack("n", substr($data, $offset, 2));
			$itemId = $itemId[1];
			$offset += 2;
			$offset += 2; // protection_index uint16?  was += 1

			if ( $itemVersion === 2 || $itemVersion === 3) {
				$itemType = substr( $data, $offset, 4 );
				$offset += 4;
			} else {
				$itemType = '';
			}
			
			$nameLen = strpos( $data, "\0", $offset ) - $offset;
			$itemName = substr( $data, $offset, $nameLen );
			$offset += $nameLen + 1;

			if ( $offset < $itemEnd ) {
					$nameLen = strpos( $data, "\0", $offset ) - $offset;
					$itemContentType = substr( $data, $offset, $nameLen );
					$offset += $nameLen + 1;
			} else {
					$itemContentType = '';
			}

			if ( $offset < $itemEnd ) {
					$nameLen = strpos( $data, "\0", $offset ) - $offset;
					$itemContentEncoding = substr( $data, $offset, $nameLen );
					$offset += $nameLen + 1;
			} else {
					$itemContentEncoding = '';
			}
			
			$entries[] = array(
				'item_id' => $itemId,
				'type' => $itemType,
				'name' => $itemName,
				'content_type' => $itemContentType,
				'encoding' => $itemContentEncoding,
			);
		}
	
		return $entries;
	}

	/**
	 * Decode the item property container (ipco) box in an HEIC/AVIF image
	 * 
	 * The ipco (Item Property Container) box contains a sequence of properties.
	 * Each property is itself a box.
	 *
	 * @since 3.27
	 *
	 * @param	resource file pointer
	 * @param	array    box descriptor
	 *
	 * @return	array	box descriptor(s)
	 */
	private static function parseIpcoBox( $fp, $ipcoBox ) {
		$properties = self::parseBoxLevel( $fp, $ipcoBox['offset'] + 8, $ipcoBox['size'] - 8 );
//error_log( __LINE__ . "  MLAAVIF::parseIpcoBox properties = " . var_export( $properties, true ), 0 );
	
		$result = array();
		foreach ( $properties as $property ) {
			$propertyData =array();
	
			switch ( $property['type'] ) {
				case 'ispe': // Image spatial extents property
					fseek( $fp, $property['offset'] + 8 );
					$ispeData = fread( $fp, $property['size'] - 8 );
//error_log( __LINE__ . "  MLAAVIF::parseIpcoBox ispeData dump = \r\n" . MLAData::mla_hex_dump( $ispeData, 0, 32, 0 ), 0 );
					$spatial = unpack( "Nunknown/Nwidth/Nheight", $ispeData );
					$propertyData = array(
						'unknown' => $spatial['unknown'],
						'width' => $spatial['width'],
						'height' => $spatial['height']
					);
					break;
				case 'irot': // Image rotation property
					fseek( $fp, $property['offset'] + 8 );
					$irotData = fread( $fp, 1 );
					$angle = ( ord($irotData) & 0x03 ) * 90; // 2 bits for rotation
					$propertyData = array(
						'rotation_angle' => $angle
					);
					break;
				case 'colr': // Color information
					fseek( $fp, $property['offset'] + 8 );
					$colrData = fread( $fp, $property['size'] - 8 );
					$colorType = substr( $colrData, 0, 4 );
					$propertyData = array(
						'color_type' => $colorType,
						'colrData' => bin2hex( $colrData ) // Raw color data as hex
					);
					break;
				case 'pixi': // Pixel information (e.g., number of color components)
					fseek( $fp, $property['offset'] + 8 );
					$pixiData = fread( $fp, $property['size'] - 8 );
					$offset =0;

					$version = ord( $pixiData[ $offset ]);
					$offset += 4; // version + flags
				
					$numChannels = ord( $pixiData[$offset] );
					$offset += 1;
				
					$bitsPerChannel = array();
					for ( $i = 0; $i < $numChannels; $i++ ) {
						$bitsPerChannel[] = ord( $pixiData[ $offset + $i ] );
					}
				
					$propertyData = array(
						'version' => $version,
						'num_channels' => $numChannels,
						'bits_per_channel' => $bitsPerChannel,
						'pixiData' => bin2hex( $pixiData ) // Raw pixel data as hex
					);
					break;
				case 'av1C': // AV1 codec configuration
					fseek( $fp, $property['offset'] + 8 );
					$av1CData = fread( $fp, $property['size'] - 8 );

    				$config = unpack('CconfigOBU', $av1CData[0]); // just the first byte
	
					$propertyData = array(
						'config_OBU' => $config['configOBU'],
						'av1CData' => bin2hex( $av1CData ) // Raw codec data as hex
					);
					break;
				case 'clap': // clean aperture (crop rectangle)
					fseek( $fp, $property['offset'] + 8 );
					$clapData = fread( $fp, $property['size'] - 8 );
//error_log( __LINE__ . "  MLAAVIF::parseIpcoBox clapData dump = \r\n" . MLAData::mla_hex_dump( $clapData, 0, 32, 0 ), 0 );

					$fields = unpack(
						'Nwidth/NwidthDen/Nheight/NheightDen/NhorizOff/NhorizOffDen/NvertOff/NvertOffDen',
						substr($clapData, 0, 32)
					);
//error_log( __LINE__ . "  MLAAVIF::parseIpcoBox fields = " . var_export( $fields, true ), 0 );

					$propertyData = array(
						'clean_aperture_width' => $fields['width'] / $fields['widthDen'],
						'clean_aperture_height' => $fields['height'] / $fields['heightDen'],
						'horiz_offset' => $fields['horizOff'] / $fields['horizOffDen'],
						'vert_offset' => $fields['vertOff'] / $fields['vertOffDen'],
						'clapData' => bin2hex( $clapData ) // Raw crop data as hex
					);
					break;
				default:
					$propertyData = array('raw_data' => 'Unsupported property type');
			}
	
			$result[] = array(
				'type' => $property['type'],
				'data' => $propertyData
			);
		}
	
//error_log( __LINE__ . "  MLAAVIF::parseIpcoBox result = " . var_export( $result, true ), 0 );
		return $result;
	}

	/**
	 * Decode the item property assicoation (ipma) box in an HEIC/AVIF image
	 * 
	 * 	The ipma (Item Property Association) box links items to properties.
	 *
	 * @since 3.27
	 *
	 * @param	resource file pointer
	 * @param	array    box descriptor
	 *
	 * @return	array	box descriptor(s)
	 */
	private static function parseIpmaBox( $fp, $ipmaBox ) {
		fseek( $fp, $ipmaBox['offset'] + 8 );
		$ipmaData = fread( $fp, $ipmaBox['size'] - 8 );
//error_log( __LINE__ . "  MLAAVIF::parseIpmaBox ipmaData dump = \r\n" . MLAData::mla_hex_dump( $ipmaData, 0, 32, 0 ), 0 );
	
		$offset = 0;
		$version = ord( $ipmaData[ $offset ] );
		$offset += 1;
		$flags = unpack( "N", "\0" . substr( $ipmaData, $offset, 3 ) );
		$flags = $flags[1];
		$offset += 3;
//error_log( __LINE__ . "  MLAAVIF::parseIpmaBox version = " . var_export( $version, true ), 0 );
//error_log( __LINE__ . "  MLAAVIF::parseIpmaBox flags = " . var_export( $flags, true ), 0 );

		$offset += 2; // unknown element
	
		$entryCount = unpack( "n", substr( $ipmaData, $offset, 2 ) );
		$entryCount = $entryCount[1];
		$offset += 2;
//error_log( __LINE__ . "  MLAAVIF::parseIpmaBox entryCount = " . var_export( $entryCount, true ), 0 );
	
		$entries = array();
		for ( $i = 0; $i < $entryCount; $i++ ) {
			if ( $version < 1 ) {
				$itemId = unpack( "n", substr( $ipmaData, $offset, 2 ) );
				$itemId = $itemId[1];
			} else {
				$itemId = unpack( "N", substr( $ipmaData, $offset, 4 ) );
				$itemId = $itemId[1];
			}

			$offset += ($version < 1) ? 2 : 4;
	
			$assocCount = ord( $ipmaData[$offset] );
			$offset += 1;
	
			$associations = array();
			for ( $j = 0; $j < $assocCount; $j++ ) {
				$entry = ord( $ipmaData[$offset] );
				$offset += 1;
	
				$essential = ( $entry & 0x80 ) >> 7;
				$propertyIndex = $entry & 0x7F;
	
				$associations[] = array(
					'essential' => $essential,
					'property_index' => $propertyIndex
				);
			}
	
			$entries[] = array(
				'item_id' => $itemId,
				'associations' => $associations
			);
		}
	
		return $entries;
	}

	private static function extractItemsByType( $fp, $ilocItems, $infeEntries, $desiredType ) {
		$results = array();
	
		foreach ( $ilocItems as $item ) {
			$itemId = $item['item_id'];
	
			if ( !isset( $infeEntries[ $itemId ] ) ) {
				continue; // no info
			}
	
			$infe = $infeEntries[ $itemId ];
	
			// Match by item_type (v2/v3) or MIME type (v0 fallback)
			$matchesType = false;
			if (isset( $infe['type'] ) ) {
				$matchesType = strtolower( $infe['type'] ) === strtolower( $desiredType );
			} elseif (isset( $infe['content_type'] ) ) {
				// crude match: e.g., 'application/octet-stream' may be Exif
				$matchesType = stripos( $infe['content_type'], $desiredType ) !== false;
			}
	
			if ( !$matchesType ) {
				continue;
			}
	
			$item_name = ! empty( $infe[ 'item_name' ] ) ? $infe[ 'item_name' ] : 'unnamed';
			$item_type = ! empty( $infe[ 'content_type' ] ) ? $infe[ 'content_type' ] : 'unnamed';

			// Extract data using extents
			$binary = '';
			foreach ( $item['extents'] as $extent ) {
				fseek( $fp, $extent['absolute_offset'] );
				$binary .= fread( $fp, $extent['length'] );
			}
	
			$results = array(
				'item_id' => $itemId,
				'name' => $item_name,
				'type' => $item_type,
				'binary' => $binary,
			);
		}
	
		return $results;
	}
} // class MLAAVIF
?>