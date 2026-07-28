<?php
/**
 * Divi integration bootstrap. Only included when the active template is 'divi'.
 * Sub-files load only for Divi 5+.
 *
 * @package megamenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$megamenu_divi_theme  = wp_get_theme();
$megamenu_divi_parent = $megamenu_divi_theme->parent();
$megamenu_divi_ver    = $megamenu_divi_parent instanceof WP_Theme
	? $megamenu_divi_parent->get( 'Version' )
	: $megamenu_divi_theme->get( 'Version' );

if ( ! is_string( $megamenu_divi_ver ) || '' === $megamenu_divi_ver || version_compare( $megamenu_divi_ver, '5', '<' ) ) {
	return;
}

require_once __DIR__ . '/scss.php';
require_once __DIR__ . '/admin-notice.php';
require_once __DIR__ . '/location-settings-display-options.php';
require_once __DIR__ . '/location/module.php';
