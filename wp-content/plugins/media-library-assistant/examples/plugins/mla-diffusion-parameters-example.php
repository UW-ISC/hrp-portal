<?php
/**
 * Parses the "png:parameters" tEXt chunk into individual "png.diffusion." values
 *
 * Created for support topic "how to extract from the parameters field"
 * opened on 3/25/2023 by "reassure".
 * https://wordpress.org/support/topic/how-to-extract-from-the-parameters/
 *
 * @package MLA Diffusion Parameters Example
 * @version 1.00
 */

/*
Plugin Name: MLA Diffusion Parameters Example
Plugin URI: http://davidlingren.com/
Description: Parses the "png:parameters" tEXt chunk into individual "png.diffusion." values 
Author: David Lingren
Version: 1.00
Author URI: http://davidlingren.com/

Copyright 2023 David Lingren

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You can get a copy of the GNU General Public License by writing to the
	Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110, USA
*/

/**
 * Class Posts Per Page Example adjusts the [mla_gallery] posts_per_page value based on
 * WordPress conditional functions
 *
 * @package MLA Diffusion Parameters Example
 * @since 1.00
 */
class MLADiffusionParametersExample {
	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		add_filter( 'mla_fetch_attachment_image_metadata_final', 'MLADiffusionParametersExample::mla_fetch_attachment_image_metadata_final', 10, 3 );
	}

	/**
	 * Process the 'posts_per_front_page' custom parameter
	 *
	 * @since 1.00
	 *
	 * @param array $metadata The metadata MLA extracted from the attached file
	 * @param array $post_id The ID of the attachment representing the file
	 * @param array $path The location of the attached file
	 */
	public static function mla_fetch_attachment_image_metadata_final( $metadata, $post_id, $path ) {
		if ( empty( $metadata['mla_png_metadata'] ) ) {
			return $metadata;
		}
		
		if ( !empty( $metadata['mla_png_metadata']['parameters'] ) ) {
			// found diffusion image generator parameters
			$parameters = $metadata['mla_png_metadata']['parameters'];
			$diffusion = array();
			
			$match_count = preg_match_all( '/([^\r\n]*)[\r\n]*/', $parameters, $matches );

			$match_state = 1;
			$found_break = false;
			$prompt = '';
			$negative_prompt = '';
			$tail = '';
			foreach ( $matches[1] as $match ) {

				// BREAK seems to be an alternative to AND
				if ( $found_break ) {
					// go on to accept the current match as a continuation line
				} else {
					$found_break = ( 'BREAK' === $match );
					if ( $found_break ) {
						continue;
					}
				}
				
				switch ( $match_state ) {
					case 1:
						// Begin prompt
						$prompt = $match;
						$match_state = 2;
						break;
					case 2:
						// Prompt continuation line(s)
						if ( $found_break ) {
							$prompt .= "\nBREAK\n" . $match;
							$found_break = false;
							break;
						}
						
						if ( 0 === strpos( $match, 'AND' ) ) {
							$prompt .= "\n" . $match;
							break;
						} else {
							$match_state = 3;
							// fallthru
						}
					case 3:
						// Begin Negative prompt
						if ( 0 === strpos( $match, 'Negative prompt:' ) ) {
							$negative_prompt = $match;
							$match_state = 4;
						} else {
							// Missing negative prompt; look for other param4eters
							if ( false === strpos( $match, ':' ) ) {
								$match = 'Unknown: ' . $match;
							}
							
							$tail = trim( $match, ', ' ) . ', ';
							$match_state = 5;
						}

						break;
					case 4:
						// Negative prompt continuation line(s)
						if ( $found_break ) {
							$negative_prompt .= "\nBREAK\n" . $match;
							$found_break = false;
							break;
						}
						
						if ( 0 === strpos( $match, 'AND' ) ) {
							$negative_prompt .= "\n" . $match;
							break;
						} else {
							$match_state = 5;
							// fallthru
						}
					case 5:
						$tail .= $match;
				} // switch
			} // foreach line

			$metadata['mla_png_metadata']['diffusion']['Prompt'] = $prompt;
			$metadata['mla_png_metadata']['diffusion']['Negative prompt'] = $negative_prompt;
			$metadata['mla_png_metadata']['diffusion']['Other Parameters'] = $tail;

			// Parse out the other parameters
			$match_count = preg_match_all( '/([^,]*)[,]*/', $tail, $matches );
			
			$delimiter = false; // For assembling quoted text
			$quoted_text = '';
			foreach( $matches[1] as $text ) {
				// Continue a delimited value
				if ( $delimiter ) {
					$quoted_text .= $text . ','; // restore the comma removed by the regex
					
					if ( $delimiter === substr( $text, strlen( $text ) - 1 ) ) {
						$metadata['mla_png_metadata']['diffusion'][ $keyword ] = trim( $quoted_text, ', ' );
						$delimiter = false;
					}

					continue;
				}
				
				$separator = strpos( $text, ':' );
				if ( false !== $separator ) {
					$keyword = trim( substr( $text, 0, $separator++ ) );
					$text = trim( substr( $text, $separator ) );
					
					// Look for delimited value
					$candidate = substr( $text, 0, 1 );
					if ( '"' === $candidate || "'" === $candidate ) {
						$delimiter = $candidate;
						$quoted_text = $text;
					
						// Self-contained; no commas within the value
						if ( $delimiter === substr( $text, strlen( $text ) - 1 ) ) {
							$metadata['mla_png_metadata']['diffusion'][ $keyword ] = $quoted_text;
							$delimiter = false;
						}
					} else {
						$metadata['mla_png_metadata']['diffusion'][ $keyword ] = $text;
					}
				}
			}
		} // found diffusion image generator parameters
		
		return $metadata;
	} // mla_fetch_attachment_image_metadata_final
} // Class MLADiffusionParametersExample

// Install the filters at an early opportunity
add_action('init', 'MLADiffusionParametersExample::initialize');
?>