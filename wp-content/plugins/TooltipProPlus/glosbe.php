<?php

class CMTT_Glosbe_API {

	public static function addShortcodes() {
		add_shortcode( 'cmtt_glosbe_dictionary', array( __CLASS__, 'dictionaryShortcode' ) );
		add_action( 'wp_ajax_cmtt_test_glosbe_dictionary_api', array( __CLASS__, 'testDictionary' ) );
	}

	public static function dictionaryShortcode( $atts ) {
		extract( shortcode_atts( array(
			'term' => '' ), $atts ) );

		$term		 = str_replace( array( '"', '\'' ), array( '', '' ), html_entity_decode( $term, ENT_COMPAT, 'utf-8' ) );
		$dictionary	 = self::get_dictionary( $term );

		return $dictionary;
	}

	public static function testDictionary() {

		$result = CMTT_Glosbe_API::get_dictionary( 'creative' );
		echo $result;
		die();
	}

	public static function get_dictionary( $term, $onGlossaryIndex = false, $postObject = null, $flushCache = false ) {

		if ( !empty( $term ) ) {
			$returnHtml		 = '';
			$term			 = mb_strtolower( trim( $term ) );
			$termCacheName	 = htmlspecialchars( $term );
			$result			 = false;
			$theMeaning		 = '';

			if ( !empty( $postObject ) ) {
				$result		 = CMTT_Pro::_get_meta( '_cmtt_glosbe_dictionary_cache', $postObject->ID );
				$resultTerm	 = CMTT_Pro::_get_meta( '_cmtt_glosbe_dictionary_term_cache', $postObject->ID );

				/*
				 * Invalidate the cache
				 */
				if ( !empty( $resultTerm ) && ($flushCache || empty( $resultTerm ) || $resultTerm !== $termCacheName ) ) {
					delete_post_meta( $postObject->ID, '_cmtt_glosbe_dictionary_cache' );
					$result = FALSE;
				}
			}

			if ( $result === false && (!$onGlossaryIndex || get_option( 'cmtt_glossaryRunApiCalls' ) == 1) ) {
				$uri			 = 'https://glosbe.com/gapi/translate?from=eng&dest=eng&format=json&phrase=' . urlencode( $termCacheName ) . '&pretty=true';
				$response		 = wp_remote_get( $uri );
				$firstMeaning	 = '';
				$longestMeaning	 = '';

				if ( !empty( $response ) ) {
					$api_response = json_decode( wp_remote_retrieve_body( $response ), true );

					if ( !empty( $api_response[ 'tuc' ][ 0 ][ 'meanings' ] ) && is_array( $api_response[ 'tuc' ][ 0 ][ 'meanings' ] ) ) {
						foreach ( $api_response[ 'tuc' ][ 0 ][ 'meanings' ] as $meaning ) {
							if ( isset( $meaning[ 'text' ] ) && strlen( $longestMeaning ) < strlen( $meaning[ 'text' ] ) ) {
								$longestMeaning = $meaning[ 'text' ];
							}
							if ( empty( $firstMeaning ) ) {
								$firstMeaning = $meaning[ 'text' ];
							}
						}
					}

					if ( !empty( $firstMeaning ) && !empty( $longestMeaning ) ) {
						$theMeaning = '<ul>';
						$theMeaning .= '<li>' . $firstMeaning . '</li>';
						$theMeaning .= '<li>' . $longestMeaning . '</li>';
						$theMeaning .= '</ul>';
					}
				}
			} else {
				$theMeaning = $result;
			}

			if ( !empty( $theMeaning ) ) {

				if ( !empty( $postObject ) ) {
					update_post_meta( $postObject->ID, '_cmtt_glosbe_dictionary_cache', $theMeaning );
					update_post_meta( $postObject->ID, '_cmtt_glosbe_dictionary_term_cache', $termCacheName );
				}
				$returnHtml = '<div class=mw-thesaurus-container> <!-- glosbe container start -->';
				$returnHtml .= '<div class=glossary_mw_dictionary>' . __( 'Glosbe Dictionary:', 'cm-tooltip-glossary' ) . '</div>';
				$returnHtml .= $theMeaning;
				$returnHtml .= '<div class=break></div>';
				$returnHtml .= '</div><!-- glosbe container end -->';
				return $returnHtml;
			}
		}

		return $term;
	}

	public static function dictionary_enabled() {
		return $source_id = get_option( 'cmtt_tooltip3RD_GlosbeDictionaryEnabled', 0 );
	}

	public static function dictionary_only_on_empty_content() {
		return $source_id = get_option( 'cmtt_tooltip3RD_GlosbeDictionaryAutoContent', 0 );
	}

	public static function dictionary_show_in_tooltip() {
		return $source_id = get_option( 'cmtt_tooltip3RD_GlosbeDictionaryTooltip', 0 );
	}

	public static function dictionary_show_in_term() {
		return $source_id = get_option( 'cmtt_tooltip3RD_GlosbeDictionaryTerm', 0 );
	}

}
