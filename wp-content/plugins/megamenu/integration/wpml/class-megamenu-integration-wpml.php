<?php
/**
 * WPML integration for Max Mega Menu.
 *
 * Hooks generic Mega Menu actions and filters; core plugin code stays integration-agnostic.
 *
 * @package MegaMenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPML-specific behaviour: per-language CSS scoping, language-switcher item class,
 * and per-language assigned-menu summary on location cards (when "All Languages" is selected).
 *
 * Detection uses ICL_SITEPRESS_VERSION to distinguish real WPML from Polylang's
 * WPML compatibility layer (which also defines some WPML functions/constants).
 */
final class Mega_Menu_Integration_Wpml {

	/**
	 * @var bool
	 */
	private static $booted = false;


	/**
	 * Boot when WPML is active.
	 *
	 * @return void
	 */
	public static function maybe_boot() {
		if ( self::$booted || ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return;
		}

		self::$booted = true;

		add_filter( 'megamenu_css_transient_key',      [ __CLASS__, 'language_transient_key' ] );
		add_filter( 'megamenu_css_filename',           [ __CLASS__, 'language_css_filename' ] );
		add_action( 'megamenu_after_delete_cache',     [ __CLASS__, 'delete_language_css_transients' ] );
		add_filter( 'megamenu_nav_menu_objects_after', [ __CLASS__, 'add_flyout_to_language_switcher_items' ], 6, 2 );

		add_filter( 'megamenu_location_assignment_summary_html', [ __CLASS__, 'filter_location_assignment_summary_html' ], 10, 2 );
		add_filter( 'megamenu_location_has_assigned_nav_menu',   [ __CLASS__, 'filter_location_has_assigned_nav_menu' ], 10, 2 );
	}


	/**
	 * Make the CSS transient key unique to the current WPML language.
	 *
	 * @param string $key Base transient key.
	 * @return string
	 */
	public static function language_transient_key( $key ) {
		return $key . '_' . ICL_LANGUAGE_CODE;
	}


	/**
	 * Make the CSS filename unique to the current WPML language.
	 *
	 * @param string $filename Base CSS filename (without extension).
	 * @return string
	 */
	public static function language_css_filename( $filename ) {
		return $filename . '_' . ICL_LANGUAGE_CODE;
	}


	/**
	 * Delete all language-specific CSS transients.
	 *
	 * @return void
	 */
	public static function delete_language_css_transients() {
		$languages = icl_get_languages( 'skip_missing=N' );

		foreach ( $languages as $language ) {
			delete_transient( 'megamenu_css_' . $language['language_code'] );
		}
	}


	/**
	 * Ensure WPML language-switcher items render as flyouts.
	 *
	 * @param array  $items Menu item objects.
	 * @param object $args  wp_nav_menu arguments.
	 * @return array
	 */
	public static function add_flyout_to_language_switcher_items( $items, $args ) {
		foreach ( $items as $item ) {
			if ( in_array( 'wpml-ls-item', $item->classes, true ) ) {
				$item->classes[] = 'menu-flyout';
			}
		}

		return $items;
	}


	/**
	 * Replace the single-menu assignment summary with one line per WPML language.
	 *
	 * @param string $default_html Default Mega Menu HTML (possibly empty).
	 * @param string $location     Location slug.
	 * @return string
	 */
	public static function filter_location_assignment_summary_html( $default_html, $location ) {
		$multiline = self::build_per_language_assignment_summary_html( $location );
		if ( null !== $multiline && '' !== $multiline ) {
			return $multiline;
		}

		return $default_html;
	}


	/**
	 * True when WPML has at least one valid menu assigned to this location in any language.
	 *
	 * @param bool|null $pre      Prior filter value (null = not handled).
	 * @param string    $location Location slug.
	 * @return bool|null
	 */
	public static function filter_location_has_assigned_nav_menu( $pre, $location ) {
		if ( null !== $pre ) {
			return $pre;
		}

		$row = self::get_wpml_nav_menu_location_map( $location );
		if ( null === $row ) {
			return null;
		}

		foreach ( $row as $menu_id ) {
			$mid = (int) $menu_id;
			if ( $mid > 0 ) {
				$menu = wp_get_nav_menu_object( $mid );
				if ( $menu && isset( $menu->name ) ) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Build a lang_code => menu_term_id map for a given location.
	 *
	 * get_nav_menu_locations() is filtered by WPML to return the current-language menu, so
	 * we normalise whatever ID it returns back to the default language first, then resolve
	 * translations for every active language from that known baseline.
	 *
	 * @param string $location Location slug.
	 * @return array<string,int>|null Map of language code to menu term ID, or null if unavailable.
	 */
	private static function get_wpml_nav_menu_location_map( $location ) {
		global $sitepress;

		if ( ! isset( $sitepress ) || ! is_object( $sitepress ) ) {
			return null;
		}

		$locations    = get_nav_menu_locations();
		$raw_menu_id  = isset( $locations[ $location ] ) ? (int) $locations[ $location ] : 0;

		if ( $raw_menu_id <= 0 ) {
			return null;
		}

		$active_languages = $sitepress->get_active_languages();
		if ( empty( $active_languages ) ) {
			return null;
		}

		$default_lang = $sitepress->get_default_language();

		// Normalise to the default-language menu so translations resolve correctly
		// regardless of which language is currently selected in the admin bar.
		$base_menu_id = (int) apply_filters( 'wpml_object_id', $raw_menu_id, 'nav_menu', true, $default_lang );

		if ( $base_menu_id <= 0 ) {
			return null;
		}

		$map = [];

		foreach ( $active_languages as $lang ) {
			$lang_code = isset( $lang['code'] ) ? (string) $lang['code'] : '';
			if ( '' === $lang_code ) {
				continue;
			}

			if ( $lang_code === $default_lang ) {
				$map[ $lang_code ] = $base_menu_id;
			} else {
				$translated_id = (int) apply_filters( 'wpml_object_id', $base_menu_id, 'nav_menu', false, $lang_code );
				if ( $translated_id > 0 ) {
					$map[ $lang_code ] = $translated_id;
				}
			}
		}

		return empty( $map ) ? null : $map;
	}


	/**
	 * Build "Assigned menu" HTML: all languages on one row (edit link or "Not assigned" each).
	 * Each language is prefixed with a short tag from the WPML language code, e.g. [en], [fr].
	 *
	 * @param string $location Location slug.
	 * @return string|null HTML string, or null to fall back to default behaviour.
	 */
	private static function build_per_language_assignment_summary_html( $location ) {
		global $sitepress;

		if ( ! isset( $sitepress ) || ! is_object( $sitepress ) ) {
			return null;
		}

		$row = self::get_wpml_nav_menu_location_map( $location );
		if ( null === $row ) {
			return null;
		}

		$active_languages = $sitepress->get_active_languages();
		if ( empty( $active_languages ) ) {
			return null;
		}

		$parts = [];

		foreach ( $active_languages as $lang ) {
			$lang_code = isset( $lang['code'] ) ? (string) $lang['code'] : '';
			if ( '' === $lang_code ) {
				continue;
			}

			$lang_tag = '[' . esc_html( strtolower( $lang_code ) ) . ']';
			$mid      = isset( $row[ $lang_code ] ) ? (int) $row[ $lang_code ] : 0;

			if ( $mid > 0 ) {
				$menu = wp_get_nav_menu_object( $mid );
				if ( $menu && isset( $menu->name ) ) {
					$url     = admin_url( 'nav-menus.php?action=edit&menu=' . $mid );
					$parts[] = sprintf(
						'<span class="mega-location__assigned-lang">%1$s</span> <a class="mega-location__assigned-link" href="%2$s">%3$s</a>',
						esc_html( $lang_tag ),
						esc_url( $url ),
						esc_html( $menu->name )
					);
				} else {
					$parts[] = sprintf(
						'<span class="mega-location__assigned-lang">%1$s</span> <span class="mega-location__assigned-none">%2$s</span>',
						esc_html( $lang_tag ),
						esc_html__( 'Invalid menu', 'megamenu' )
					);
				}
			} else {
				$parts[] = sprintf(
					'<span class="mega-location__assigned-lang">%1$s</span> <span class="mega-location__assigned-none">%2$s</span>',
					esc_html( $lang_tag ),
					esc_html__( 'Not assigned', 'megamenu' )
				);
			}
		}

		if ( ! count( $parts ) ) {
			return null;
		}

		$header = '<span class="mega-location__assigned-heading">' . esc_html__( 'Assigned menu:', 'megamenu' ) . '</span>';
		$sep    = ' <span class="mega-location__assigned-sep" aria-hidden="true">·</span> ';

		return '<span class="mega-location__assigned-inline">' . $header . ' ' . implode( $sep, $parts ) . '</span>';
	}
}
