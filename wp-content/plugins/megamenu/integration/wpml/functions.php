<?php
/**
 * WPML integration bootstrap for Max Mega Menu.
 *
 * Uses only generic core hooks:
 *
 * - `megamenu_css_transient_key`, `megamenu_css_filename`, `megamenu_after_delete_cache` — CSS cache scoping per language.
 * - {@see 'megamenu_nav_menu_objects_after'} — add flyout class to WPML language-switcher items.
 *
 * @package MegaMenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-megamenu-integration-wpml.php';
require_once __DIR__ . '/scss.php';

add_action( 'plugins_loaded', [ 'Mega_Menu_Integration_Wpml', 'maybe_boot' ], 11 );
