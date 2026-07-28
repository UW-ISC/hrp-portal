<?php
/**
 * Appends Divi integration SCSS (loaded only when Divi 5+ is active — see functions.php).
 *
 * @package megamenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Append the Divi integration SCSS to the compiled stylesheet.
 *
 * @since 3.9
 * @param string $scss Existing SCSS content.
 * @return string SCSS content with the integration stylesheet appended when applicable.
 */
function megamenu_divi_style( $scss ) {
	$path     = __DIR__ . '/style.scss';
	$contents = is_readable( $path ) ? file_get_contents( $path ) : false;

	if ( ! is_string( $contents ) ) {
		return $scss;
	}

	return $scss . $contents;
}

add_filter( 'megamenu_load_scss_file_contents', 'megamenu_divi_style', 9999 );
