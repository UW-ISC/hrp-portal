<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! function_exists( 'mmm_get_theme_id_for_location' ) ) {
	/**
	 * Get the menu theme ID for a specific location.
	 *
	 * @since  2.1
	 * @param  string $location Theme location identifier.
	 * @return string|false Theme ID, or false if not found.
	 */
	function mmm_get_theme_id_for_location( $location = false ) {

		if ( ! $location ) {
			return false;
		}

		if ( ! has_nav_menu( $location ) ) {
			return false;
		}

		$loc = Mega_Menu_Location::find( $location );

		if ( $loc && $loc->is_enabled() ) {
			return $loc->get_theme_id();
		}

		return false;
	}
}

if ( ! function_exists( 'mmm_get_theme_for_location' ) ) {
	/**
	 * Get the theme assigned to a specified location.
	 *
	 * @since  2.0.2
	 * @param  string $location Theme location identifier.
	 * @return array|false Theme settings array, or false if not found.
	 */
	function mmm_get_theme_for_location( $location = false ) {

		if ( ! $location ) {
			return false;
		}

		if ( ! has_nav_menu( $location ) ) {
			return false;
		}

		$loc = Mega_Menu_Location::find( $location );

		if ( $loc && $loc->is_enabled() ) {
			return Mega_Menu_Theme::find( $loc->get_theme_id() )->settings;
		}

		return Mega_Menu_Theme::get_default()->settings;
	}
}


if ( ! function_exists( 'max_mega_menu_is_enabled' ) ) {
	/**
	 * Determines if Max Mega Menu has been enabled for a given menu location.
	 *
	 * Usage:
	 *
	 * Max Mega Menu is enabled:
	 * function_exists( 'max_mega_menu_is_enabled' )
	 *
	 * Max Mega Menu has been enabled for a theme location:
	 * function_exists( 'max_mega_menu_is_enabled' ) && max_mega_menu_is_enabled( $location )
	 *
	 * @since  1.8
	 * @param  string $location Theme location identifier.
	 * @return bool
	 */
	function max_mega_menu_is_enabled( $location = false ) {

		if ( ! $location ) {
			return true; // the plugin is enabled.
		}

		if ( ! has_nav_menu( $location ) ) {
			return false;
		}

		$loc = Mega_Menu_Location::find( $location );

		return $loc ? $loc->is_enabled() : false;
	}
}

if ( ! function_exists( 'max_mega_menu_share_themes_across_multisite' ) ) {
	/**
	 * In the first version of MMM, themes were (incorrectly) shared between all sites in a multi site network.
	 * Themes will not be shared across sites for new users installing v2.4.3 onwards, but they will be shared for existing (older) users.
	 *
	 * @since  2.3.7
	 * @return bool
	 */
	function max_mega_menu_share_themes_across_multisite() {

		if ( defined( 'MEGAMENU_SHARE_THEMES_MULTISITE' ) && MEGAMENU_SHARE_THEMES_MULTISITE === false ) {
			return false;
		}

		if ( defined( 'MEGAMENU_SHARE_THEMES_MULTISITE' ) && MEGAMENU_SHARE_THEMES_MULTISITE === true ) {
			return true;
		}

		if ( get_option( 'megamenu_multisite_share_themes' ) === 'false' ) { // only exists if initially installed version is 2.4.3+.
			return false;
		}

		return apply_filters( 'megamenu_share_themes_across_multisite', true );

	}
}

if ( ! function_exists( 'max_mega_menu_get_themes' ) ) {
	/**
	 * Return saved themes.
	 *
	 * @since  2.3.7
	 * @return array|false
	 */
	function max_mega_menu_get_themes() {

		if ( ! max_mega_menu_share_themes_across_multisite() ) {
			return get_option( 'megamenu_themes' );
		}

		return get_site_option( 'megamenu_themes' );

	}
}

if ( ! function_exists( 'max_mega_menu_save_themes' ) ) {
	/**
	 * Save menu themes.
	 *
	 * @since  2.3.7
	 * @param  array $themes Menu themes array.
	 * @return bool True on success, false on failure.
	 */
	function max_mega_menu_save_themes( $themes ) {

		if ( ! max_mega_menu_share_themes_across_multisite() ) {
			return update_option( 'megamenu_themes', $themes );
		}

		return update_site_option( 'megamenu_themes', $themes );

	}
}

if ( ! function_exists( 'max_mega_menu_save_last_updated_theme' ) ) {
	/**
	 * Save last updated theme.
	 *
	 * @since  2.3.7
	 * @param  string $theme The ID of the theme.
	 * @return bool True on success, false on failure.
	 */
	function max_mega_menu_save_last_updated_theme( $theme ) {

		if ( ! max_mega_menu_share_themes_across_multisite() ) {
			return update_option( 'megamenu_themes_last_updated', $theme );
		}

		return update_site_option( 'megamenu_themes_last_updated', $theme );

	}
}

if ( ! function_exists( 'max_mega_menu_get_last_updated_theme' ) ) {
	/**
	 * Return last updated theme.
	 *
	 * @since  2.3.7
	 * @return string|false Theme ID, or false if not set.
	 */
	function max_mega_menu_get_last_updated_theme() {

		if ( ! max_mega_menu_share_themes_across_multisite() ) {
			return get_option( 'megamenu_themes_last_updated' );
		}

		return get_site_option( 'megamenu_themes_last_updated' );

	}
}

if ( ! function_exists( 'max_mega_menu_get_toggle_blocks' ) ) {
	/**
	 * Return saved toggle blocks.
	 *
	 * @since  2.3.7
	 * @return array|false
	 */
	function max_mega_menu_get_toggle_blocks() {

		if ( ! max_mega_menu_share_themes_across_multisite() ) {
			return get_option( 'megamenu_toggle_blocks' );
		}

		return get_site_option( 'megamenu_toggle_blocks' );

	}
}

if ( ! function_exists( 'max_mega_menu_save_toggle_blocks' ) ) {
	/**
	 * Save toggle blocks.
	 *
	 * @since  2.3.7
	 * @param  array $saved_blocks Toggle blocks configuration array.
	 * @return bool True on success, false on failure.
	 */
	function max_mega_menu_save_toggle_blocks( $saved_blocks ) {

		if ( ! max_mega_menu_share_themes_across_multisite() ) {
			return update_option( 'megamenu_toggle_blocks', $saved_blocks );
		}

		return update_site_option( 'megamenu_toggle_blocks', $saved_blocks );

	}
}

if ( ! function_exists( 'max_mega_menu_delete_themes' ) ) {
	/**
	 * Delete saved themes.
	 *
	 * @since  2.3.7
	 * @return bool True on success, false on failure.
	 */
	function max_mega_menu_delete_themes() {

		if ( ! max_mega_menu_share_themes_across_multisite() ) {
			return delete_option( 'megamenu_themes' );
		}

		return delete_site_option( 'megamenu_themes' );

	}
}

if ( ! function_exists( 'max_mega_menu_get_active_caching_plugins' ) ) {
	/**
	 * Return list of active caching/CDN/minification plugins
	 *
	 * @since  2.4
	 * @return array
	 */
	function max_mega_menu_get_active_caching_plugins() {

		$caching_plugins = apply_filters(
			'megamenu_caching_plugins',
			[
				'litespeed-cache/litespeed-cache.php',
				'js-css-script-optimizer/js-css-script-optimizer.php',
				'merge-minify-refresh/merge-minify-refresh.php',
				'minify-html-markup/minify-html.php',
				'simple-cache/simple-cache.php',
				'w3-total-cache/w3-total-cache.php',
				'wp-fastest-cache/wpFastestCache.php',
				'wp-speed-of-light/wp-speed-of-light.php',
				'wp-super-cache/wp-cache.php',
				'wp-super-minify/wp-super-minify.php',
				'autoptimize/autoptimize.php',
				'bwp-minify/bwp-minify.php',
				'cache-enabler/cache-enabler.php',
				'cloudflare/cloudflare.php',
				'comet-cache/comet-cache.php',
				'css-optimizer/bpminifycss.php',
				'fast-velocity-minify/fvm.php',
				'hyper-cache/plugin.php',
				'remove-query-strings-littlebizzy/remove-query-strings.php',
				'remove-query-strings-from-static-resources/remove-query-strings.php',
				'query-strings-remover/query-strings-remover.php',
				'wp-rocket/wp-rocket.php',
				'hummingbird-performance/wp-hummingbird.php',
				'breeze/breeze.php',
			]
		);

		$active_plugins = [];

		foreach ( $caching_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$plugin_data      = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );
				$active_plugins[] = $plugin_data['Name'];
			}
		}

		return $active_plugins;
	}
}
