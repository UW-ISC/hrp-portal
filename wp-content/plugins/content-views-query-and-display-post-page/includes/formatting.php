<?php
/**
 * Formatting HTML
 *
 * @subpackage	Includes
 * @license		GPL-2.0+
 * @copyright	CVPro <http://www.contentviewspro.com/>
 * @since		1.9.1
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

/**
 * Sanitize View ID
 *
 * @since 1.9.1
 * @param string $view_id
 * @return string
 */
function cv_sanitize_vid( $view_id ) {
	return preg_replace( '/[\W]/', '', $view_id );
}

/**
 * Sanitize HTML data attribute=value
 *
 * @since 1.9.1
 * @param string $data
 * @return string
 */
function cv_sanitize_html_data( $data ) {
	return strip_tags( $data );
}

/**
 * Sanitize content of HTML tag
 *
 * @since 1.9.1
 * @param string $string
 * @return string
 */
function cv_sanitize_tag_content( $string, $remove_breaks = false ) {
	$string = preg_replace( '@<(script)[^>]*?>.*?</\\1>@si', '', $string );

	if ( $remove_breaks )
		$string = preg_replace( '/[\r\n\t ]+/', ' ', $string );

	return trim( $string );
}
