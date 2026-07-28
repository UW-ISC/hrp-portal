<?php
/**
 * Divi 5+ admin notice (Menu Locations) and related hooks.
 *
 * @package megamenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'MEGAMENU_DIVI_MENU_LOCATIONS_NOTICE' ) ) {
	define( 'MEGAMENU_DIVI_MENU_LOCATIONS_NOTICE', 'divi_menu_locations_header_module' );
}

if ( ! defined( 'MEGAMENU_DIVI_TUTORIAL_URL' ) ) {
	define( 'MEGAMENU_DIVI_TUTORIAL_URL', 'https://megamenu.com/divi/' );
}

/**
 * @return bool
 */
function megamenu_divi_is_max_mega_menu_menu_locations_screen() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	return $screen && 'toplevel_page_maxmegamenu' === $screen->id;
}

/**
 * @param string[] $keys
 * @return string[]
 */
function megamenu_divi_register_dismissible_notice_key( $keys ) {
	if ( ! is_array( $keys ) ) {
		return [ MEGAMENU_DIVI_MENU_LOCATIONS_NOTICE ];
	}

	$keys[] = MEGAMENU_DIVI_MENU_LOCATIONS_NOTICE;

	return array_values( array_unique( array_map( 'sanitize_key', $keys ) ) );
}

/**
 * @param string $hook
 * @return void
 */
function megamenu_divi_enqueue_divi_notice_assets( $hook ) {
	$load_divi_admin_css = (
		'toplevel_page_maxmegamenu' === $hook
		|| 'nav-menus.php' === $hook
		|| ( is_string( $hook ) && strpos( $hook, 'maxmegamenu' ) !== false )
	);

	if ( ! $load_divi_admin_css ) {
		return;
	}

	wp_enqueue_style(
		'megamenu-divi-admin',
		MEGAMENU_BASE_URL . 'integration/divi/admin.css',
		[],
		MEGAMENU_VERSION
	);
}

/**
 * @param Mega_Menu_Admin_Notices $_admin_notices
 * @return void
 */
function megamenu_divi_render_menu_locations_admin_notice( $_admin_notices ) {
	if ( ! megamenu_divi_is_max_mega_menu_menu_locations_screen() ) {
		return;
	}

	$cap = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );
	if ( ! current_user_can( $cap ) ) {
		return;
	}

	if ( Mega_Menu_Admin_Notices::is_dismissed( MEGAMENU_DIVI_MENU_LOCATIONS_NOTICE ) ) {
		return;
	}

	$before = '<div class="mmm-mega-notice-divi__row"><span class="mmm-mega-notice-divi__logo" aria-hidden="true"></span><div class="mmm-mega-notice-divi__main">';
	$after  = '<p class="mmm-mega-notice-divi__actions"><a href="' . esc_url( MEGAMENU_DIVI_TUTORIAL_URL ) . '" class="button button-secondary" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View tutorial', 'megamenu' ) . '</a></p></div></div>';

	Mega_Menu_Admin_Notices::output_persistent_dismissible_notice(
		'info',
		esc_html__(
			'Hi Divi user! To show Max Mega Menu in your header, open the Divi Theme Builder and add a Max Mega Menu module to your global header.',
			'megamenu'
		),
		MEGAMENU_DIVI_MENU_LOCATIONS_NOTICE,
		[
			'before_content'        => $before,
			'after_paragraph'       => $after,
			'wrapper_extra_classes' => 'mmm-mega-notice-divi',
		]
	);
}

/**
 * Clear the dismissed notice on deactivation so it re-appears on the next
 * activation. The plugin is still active when this fires, so this file is
 * loaded and the hook is registered.
 *
 * @return void
 */
function megamenu_divi_reset_notice_on_deactivation() {
	Mega_Menu_Admin_Notices::clear_dismissed_notice( MEGAMENU_DIVI_MENU_LOCATIONS_NOTICE );
}

/**
 * When switching away from Divi to another theme, clear the dismissed notice
 * immediately so it re-appears the next time Divi is the active theme.
 *
 * This file is only loaded when Divi is active at plugins_loaded, so this
 * hook is registered and fires correctly when leaving Divi.
 *
 * @param string         $new_name  Unused.
 * @param WP_Theme|false $new_theme
 * @param WP_Theme|false $old_theme
 * @return void
 */
function megamenu_divi_reset_notice_on_theme_switch( $new_name, $new_theme = null, $old_theme = null ) {
	unset( $new_name );

	if ( ! ( $old_theme instanceof WP_Theme ) || ! ( $new_theme instanceof WP_Theme ) ) {
		return;
	}

	if ( 'divi' !== strtolower( (string) $old_theme->get_template() ) ) {
		return;
	}

	if ( 'divi' === strtolower( (string) $new_theme->get_template() ) ) {
		return;
	}

	Mega_Menu_Admin_Notices::clear_dismissed_notice( MEGAMENU_DIVI_MENU_LOCATIONS_NOTICE );
}

add_filter( 'megamenu_dismissible_admin_notice_keys', 'megamenu_divi_register_dismissible_notice_key' );
add_action( 'admin_enqueue_scripts', 'megamenu_divi_enqueue_divi_notice_assets', 20 );
add_action( 'megamenu_admin_notices', 'megamenu_divi_render_menu_locations_admin_notice', 10, 1 );
add_action( 'megamenu_plugin_deactivation', 'megamenu_divi_reset_notice_on_deactivation' );
add_action( 'switch_theme', 'megamenu_divi_reset_notice_on_theme_switch', 10, 3 );
