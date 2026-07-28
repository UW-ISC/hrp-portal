<?php
/**
 * Appends WPML integration SCSS (loaded only when WPML is active).
 *
 * @package megamenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Append the WPML integration SCSS to the compiled stylesheet.
 *
 * @since 3.9
 * @param string $scss Existing SCSS content.
 * @return string SCSS content with the integration stylesheet appended when applicable.
 */
function megamenu_wpml_style( $scss ) {
	if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
		return $scss;
	}

	$path     = __DIR__ . '/style.scss';
	$contents = is_readable( $path ) ? file_get_contents( $path ) : false;

	if ( ! is_string( $contents ) ) {
		return $scss;
	}

	return $scss . $contents;
}

add_filter( 'megamenu_load_scss_file_contents', 'megamenu_wpml_style' );
