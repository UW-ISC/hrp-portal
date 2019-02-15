<?php
/**
 * /premium/redirects.php
 *
 * Handles straight redirects based on keywords.
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 * @license https://wordpress.org/about/gpl/ GNU General Public License
 * @see     https://www.relevanssi.com/
 */

add_action( 'template_redirect', 'relevanssi_redirects' );

/**
 * Handles the template redirects.
 */
function relevanssi_redirects() {
	$url       = false;
	$redirects = get_option( 'relevanssi_redirects', array() );
	if ( empty( $redirects ) || ! is_array( $redirects ) ) {
		return;
	}
	$query = relevanssi_strtolower( get_search_query() );
	foreach ( $redirects as $redirect ) {
		if ( $redirect['partial'] ) {
			if ( stristr( $query, $redirect['query'] ) ) {
				$url = $redirect['url'];
				break;
			}
		} else {
			if ( $query === $redirect['query'] ) {
				$url = $redirect['url'];
				break;
			}
		}
	}
	if ( $url ) {
		if ( wp_redirect( $url ) ) {
			exit();
		}
	}
}

/**
 * Reads the redirects from the request array and validates the URLs.
 *
 * All relative URLs are converted to absolute URLs for validation and redirects
 * with both the query and URL parameters are kept.
 *
 * @param array $request The options request array.
 *
 * @return array The redirect array.
 *
 * @since 2.2.3
 */
function relevanssi_process_redirects( $request ) {
	$redirects = array();
	foreach ( $request as $key => $value ) {
		if ( 'query' === substr( $key, 0, 5 ) ) {
			$suffix  = substr( $key, 5 );
			$query   = relevanssi_strtolower( $value );
			$partial = false;
			if ( isset( $request[ 'partial' . $suffix ] ) ) {
				$partial = true;
			}
			$url = null;
			if ( isset( $request[ 'url' . $suffix ] ) ) {
				$url = $request[ 'url' . $suffix ];
				if ( 'http' !== substr( $url, 0, 4 ) ) {
					// Relative URL, make absolute.
					if ( '/' !== substr( $url, 0, 1 ) ) {
						$url = '/' . $url;
					}
					$url = site_url() . $url;
				}
				$url = wp_http_validate_url( $url );
			}
			if ( ! empty( $url ) && ! empty( $query ) ) {
				$redirect    = array(
					'query'   => $query,
					'partial' => $partial,
					'url'     => $url,
				);
				$redirects[] = $redirect;
			}
		}
	}
	return $redirects;
}
