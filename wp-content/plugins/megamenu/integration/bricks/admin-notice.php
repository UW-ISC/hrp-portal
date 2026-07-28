<?php
/**
 * Bricks Builder admin notice (Menu Locations) and related hooks.
 *
 * @package megamenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'MEGAMENU_BRICKS_MENU_LOCATIONS_NOTICE' ) ) {
	define( 'MEGAMENU_BRICKS_MENU_LOCATIONS_NOTICE', 'bricks_menu_locations_header_element' );
}

if ( ! defined( 'MEGAMENU_BRICKS_TUTORIAL_URL' ) ) {
	define( 'MEGAMENU_BRICKS_TUTORIAL_URL', 'https://megamenu.com/bricks/' );
}

/**
 * @return bool
 */
function megamenu_bricks_is_max_mega_menu_menu_locations_screen() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	return $screen && 'toplevel_page_maxmegamenu' === $screen->id;
}

/**
 * @param string[] $keys
 * @return string[]
 */
function megamenu_bricks_register_dismissible_notice_key( $keys ) {
	if ( ! is_array( $keys ) ) {
		return [ MEGAMENU_BRICKS_MENU_LOCATIONS_NOTICE ];
	}

	$keys[] = MEGAMENU_BRICKS_MENU_LOCATIONS_NOTICE;

	return array_values( array_unique( array_map( 'sanitize_key', $keys ) ) );
}

/**
 * @param string $hook
 * @return void
 */
function megamenu_bricks_enqueue_notice_assets( $hook ) {
	$load = (
		'toplevel_page_maxmegamenu' === $hook
		|| 'nav-menus.php' === $hook
		|| ( is_string( $hook ) && strpos( $hook, 'maxmegamenu' ) !== false )
	);

	if ( ! $load ) {
		return;
	}

	wp_enqueue_style(
		'megamenu-bricks-admin',
		MEGAMENU_BASE_URL . 'integration/bricks/admin.css',
		[],
		MEGAMENU_VERSION
	);
}

/**
 * @param Mega_Menu_Admin_Notices $_admin_notices
 * @return void
 */
function megamenu_bricks_render_menu_locations_admin_notice( $_admin_notices ) {
	if ( ! megamenu_bricks_is_max_mega_menu_menu_locations_screen() ) {
		return;
	}

	$cap = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );
	if ( ! current_user_can( $cap ) ) {
		return;
	}

	if ( Mega_Menu_Admin_Notices::is_dismissed( MEGAMENU_BRICKS_MENU_LOCATIONS_NOTICE ) ) {
		return;
	}

	$before = '<div class="mmm-mega-notice-bricks__row"><span class="mmm-mega-notice-bricks__logo" aria-hidden="true"></span><div class="mmm-mega-notice-bricks__main">';
	$after  = '<p class="mmm-mega-notice-bricks__actions"><a href="' . esc_url( MEGAMENU_BRICKS_TUTORIAL_URL ) . '" class="button button-secondary" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View tutorial', 'megamenu' ) . '</a></p></div></div>';

	Mega_Menu_Admin_Notices::output_persistent_dismissible_notice(
		'info',
		esc_html__(
			'Hi Bricks user! To show Max Mega Menu in your header, open the Bricks Template editor and add a Max Mega Menu element to your header template.',
			'megamenu'
		),
		MEGAMENU_BRICKS_MENU_LOCATIONS_NOTICE,
		[
			'before_content'        => $before,
			'after_paragraph'       => $after,
			'wrapper_extra_classes' => 'mmm-mega-notice-bricks',
		]
	);
}

/**
 * @return void
 */
function megamenu_bricks_reset_notice_on_deactivation() {
	Mega_Menu_Admin_Notices::clear_dismissed_notice( MEGAMENU_BRICKS_MENU_LOCATIONS_NOTICE );
}

/**
 * @param string         $new_name  Unused.
 * @param WP_Theme|false $new_theme
 * @param WP_Theme|false $old_theme
 * @return void
 */
function megamenu_bricks_reset_notice_on_theme_switch( $new_name, $new_theme = null, $old_theme = null ) {
	unset( $new_name );

	if ( ! ( $old_theme instanceof WP_Theme ) || ! ( $new_theme instanceof WP_Theme ) ) {
		return;
	}

	if ( 'bricks' !== strtolower( (string) $old_theme->get_template() ) ) {
		return;
	}

	if ( 'bricks' === strtolower( (string) $new_theme->get_template() ) ) {
		return;
	}

	Mega_Menu_Admin_Notices::clear_dismissed_notice( MEGAMENU_BRICKS_MENU_LOCATIONS_NOTICE );
}

add_filter( 'megamenu_dismissible_admin_notice_keys', 'megamenu_bricks_register_dismissible_notice_key' );
add_action( 'admin_enqueue_scripts', 'megamenu_bricks_enqueue_notice_assets', 20 );
add_action( 'megamenu_admin_notices', 'megamenu_bricks_render_menu_locations_admin_notice', 10, 1 );
add_action( 'megamenu_plugin_deactivation', 'megamenu_bricks_reset_notice_on_deactivation' );
add_action( 'switch_theme', 'megamenu_bricks_reset_notice_on_theme_switch', 10, 3 );
