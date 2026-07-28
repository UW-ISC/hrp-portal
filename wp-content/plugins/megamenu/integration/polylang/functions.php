<?php
/**
 * Polylang integration bootstrap for Max Mega Menu.
 *
 * Uses only generic core hooks (other multilingual plugins may hook the same):
 *
 * - {@see 'megamenu_normalize_registered_nav_menus'} — adjust registered locations before Mega Menu reads them.
 * - {@see 'megamenu_nav_metabox_location_sections'} — map Polylang `location___lang` rows onto base location cards in the Appearance → Menus meta box.
 * - {@see 'megamenu_include_location_in_compiled_css'} — exclude locations from compiled CSS.
 * - {@see 'megamenu_location_card_description'} — card-only labels on Mega Menu location rows.
 * - {@see 'megamenu_location_assignment_summary_html'} — assigned menus on location cards (e.g. per language, one row).
 * - {@see 'megamenu_location_has_assigned_nav_menu'} — whether a location has any assigned menu (multilingual).
 * - `megamenu_css_transient_key`, `megamenu_css_filename`, `megamenu_after_delete_cache` — CSS cache scoping.
 *
 * @package MegaMenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-megamenu-integration-polylang.php';

add_action( 'plugins_loaded', [ 'Mega_Menu_Integration_Polylang', 'maybe_boot' ], 11 );
