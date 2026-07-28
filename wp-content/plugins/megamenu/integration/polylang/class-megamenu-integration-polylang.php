<?php
/**
 * Polylang integration for Max Mega Menu.
 *
 * Hooks generic Mega Menu actions and filters; core plugin code stays integration-agnostic.
 *
 * @package MegaMenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Polylang-specific behaviour wired through {@see 'megamenu_normalize_registered_nav_menus'},
 * {@see 'megamenu_nav_metabox_location_sections'} (map Polylang fork slugs to base location cards),
 * {@see 'megamenu_location_card_description'},
 * {@see 'megamenu_location_assignment_summary_html'}, {@see 'megamenu_location_has_assigned_nav_menu'}, and CSS-related filters.
 */
final class Mega_Menu_Integration_Polylang {

	/**
	 * @var bool
	 */
	private static $booted = false;


	/**
	 * Boot when Polylang is available (after all plugins load).
	 *
	 * @return void
	 */
	public static function maybe_boot() {
		if ( self::$booted || ! function_exists( 'pll_default_language' ) ) {
			return;
		}

		self::$booted = true;

		add_action( 'megamenu_normalize_registered_nav_menus', [ __CLASS__, 'normalize_registered_nav_menus' ] );

		add_filter( 'megamenu_nav_metabox_location_sections', [ __CLASS__, 'filter_nav_metabox_location_sections' ], 10, 5 );

		add_filter( 'megamenu_location_card_description', [ __CLASS__, 'filter_location_card_description' ], 10, 2 );

		add_filter( 'megamenu_location_assignment_summary_html', [ __CLASS__, 'filter_location_assignment_summary_html' ], 10, 2 );
		add_filter( 'megamenu_location_has_assigned_nav_menu', [ __CLASS__, 'filter_location_has_assigned_nav_menu' ], 10, 2 );

		add_filter( 'megamenu_preview_url_args', [ __CLASS__, 'filter_preview_url_args' ], 10, 2 );
		add_action( 'megamenu_preview_before_render', [ __CLASS__, 'setup_preview_language' ] );

		add_filter( 'megamenu_include_location_in_compiled_css', [ __CLASS__, 'filter_include_location_in_compiled_css' ], 10, 2 );

		add_filter( 'megamenu_css_transient_key', [ __CLASS__, 'append_current_language_locale_to_css_key' ] );
		add_filter( 'megamenu_css_filename', [ __CLASS__, 'append_current_language_locale_to_css_filename' ] );
		add_action( 'megamenu_after_delete_cache', [ __CLASS__, 'delete_language_css_transients' ] );

	}


	/**
	 * Whether a slug uses Polylang's `base___lang` pattern.
	 *
	 * @param string $location Location identifier.
	 * @return bool
	 */
	public static function is_language_fork_location_slug( $location ) {
		return is_string( $location ) && false !== strpos( $location, '___' );
	}


	/**
	 * Base theme location slug without the `___lang` suffix.
	 *
	 * @param string $location Location identifier (may include "___").
	 * @return string
	 */
	public static function base_location_slug( $location ) {
		if ( ! is_string( $location ) ) {
			return '';
		}
		$parts = explode( '___', $location, 2 );
		return $parts[0];
	}


	/**
	 * Polylang registers temporary locations as `base___lang`. Remap those rows to the base slug so the meta box
	 * shows the same Max Mega Menu location card as for the default language (one card per theme location).
	 *
	 * @param array $sections            Default sections from Mega Menu.
	 * @param array $partition           `enabled` and `disabled` maps.
	 * @param array $saved_settings      Plugin settings.
	 * @param array $all_locations       Registered locations (already normalized on MMM screens).
	 * @param array $tagged_menu_locations Tagged locations for the current menu.
	 * @return array
	 */
	public static function filter_nav_metabox_location_sections( $sections, $partition, $saved_settings, $all_locations, $tagged_menu_locations ) {
		unset( $saved_settings, $tagged_menu_locations );

		if ( ! is_array( $sections ) || ! isset( $partition['enabled'], $partition['disabled'] ) || ! is_array( $sections['location_rows'] ) ) {
			return $sections;
		}

		if ( ! is_array( $all_locations ) ) {
			$all_locations = [];
		}

		list( $enabled, $disabled ) = self::remap_polylang_metabox_partitions_to_base_locations(
			$partition['enabled'],
			$partition['disabled'],
			$all_locations
		);

		$sections['location_rows']['enabled']  = $enabled;
		$sections['location_rows']['disabled'] = $disabled;

		return $sections;
	}


	/**
	 * Collapse `primary-menu___fr`-style keys onto `primary-menu` for the Appearance → Menus meta box.
	 *
	 * If the same base appears in both partitions, the disabled map wins (no “prefer enabled”).
	 *
	 * @param array<string,string> $enabled       Location slug => label (MMM-enabled first).
	 * @param array<string,string> $disabled      Location slug => label.
	 * @param array<string,string> $all_locations Locations from {@see Mega_Menu_Locations::get_registered_locations()}.
	 * @return array{0: array<string,string>, 1: array<string,string>}
	 */
	private static function remap_polylang_metabox_partitions_to_base_locations( $enabled, $disabled, array $all_locations ) {
		$out_enabled  = [];
		$out_disabled = [];

		$push = static function ( $map, $to_disabled ) use ( &$out_enabled, &$out_disabled, $all_locations ) {
			if ( ! is_array( $map ) ) {
				return;
			}
			foreach ( $map as $loc => $desc ) {
				if ( ! is_string( $loc ) ) {
					continue;
				}
				$base  = self::is_language_fork_location_slug( $loc ) ? self::base_location_slug( $loc ) : $loc;
				$base  = ( '' !== $base ) ? $base : $loc;
				$label = isset( $all_locations[ $base ] ) ? (string) $all_locations[ $base ] : (string) $desc;
				if ( $to_disabled ) {
					$out_disabled[ $base ] = $label;
					unset( $out_enabled[ $base ] );
				} else {
					$out_enabled[ $base ] = $label;
				}
			}
		};

		$push( $enabled, false );
		$push( $disabled, true );

		self::sort_location_label_map( $out_enabled );
		self::sort_location_label_map( $out_disabled );

		return [ $out_enabled, $out_disabled ];
	}


	/**
	 * Sort location slug => label maps like {@see Mega_Menu_Locations} does for meta box rows.
	 *
	 * @param array<string,string> $locations Map; sorted in place.
	 * @return void
	 */
	private static function sort_location_label_map( array &$locations ) {
		uksort(
			$locations,
			static function ( $id_a, $id_b ) use ( $locations ) {
				$cmp = strnatcasecmp( (string) $locations[ $id_a ], (string) $locations[ $id_b ] );
				return 0 !== $cmp ? $cmp : strcmp( $id_a, $id_b );
			}
		);
	}


	/**
	 * Strip default-language suffix from MMM location cards only (not global registration).
	 *
	 * @param string $description   Card label.
	 * @param string $location      Location slug.
	 * @return string
	 */
	public static function filter_location_card_description( $description, $location ) {
		if ( self::is_language_fork_location_slug( $location ) ) {
			return $description;
		}

		return self::strip_default_language_suffix_from_string( (string) $description );
	}


	/**
	 * Polylang `nav_menus` option: menu term IDs per language for this theme location.
	 *
	 * @param string $location Location slug (canonical, no "___").
	 * @return array<string,int>|null Map of language slug to menu term ID, or null if Polylang has no data for this location.
	 */
	public static function get_polylang_nav_menu_location_map( $location ) {
		if ( ! function_exists( 'PLL' ) || ! is_object( PLL() ) || ! isset( PLL()->options ) ) {
			return null;
		}

		$options = PLL()->options;
		if ( ! is_object( $options ) || ! method_exists( $options, 'get' ) ) {
			return null;
		}

		$nav_menus = $options->get( 'nav_menus' );
		if ( ! is_array( $nav_menus ) ) {
			return null;
		}

		$stylesheet = get_option( 'stylesheet' );
		if ( ! isset( $nav_menus[ $stylesheet ][ $location ] ) ) {
			return null;
		}

		$row = $nav_menus[ $stylesheet ][ $location ];
		if ( ! is_array( $row ) || ! count( $row ) ) {
			return null;
		}

		return $row;
	}


	/**
	 * Append the current Polylang language to the preview URL so the iframe renders the correct language.
	 *
	 * @param array  $args     URL query args.
	 * @param string $location Location slug.
	 * @return array
	 */
	public static function filter_preview_url_args( $args, $location ) {
		unset( $location );
		$lang = pll_current_language();
		if ( $lang ) {
			$args['lang'] = $lang;
		}
		return $args;
	}


	/**
	 * Ensure the preview iframe renders the menu for the requested language.
	 *
	 * admin-post.php runs in admin mode so Polylang's frontend menu-language filter is not
	 * active. We remap the location via theme_mod_nav_menu_locations at PHP_INT_MAX so that
	 * Mega Menu's get_valid_menu_id() (called inside modify_nav_menu_args at priority 99999)
	 * resolves the language-specific menu. Mega Menu then sets args['menu'] to the correct
	 * WP_Term itself, keeping add_widgets_to_menu consistent.
	 *
	 * @param string $location Location slug.
	 * @return void
	 */
	public static function setup_preview_language( $location ) {
		$lang = isset( $_GET['lang'] ) ? sanitize_key( $_GET['lang'] ) : '';
		if ( '' === $lang ) {
			return;
		}

		$map = self::get_polylang_nav_menu_location_map( $location );
		if ( ! is_array( $map ) || ! isset( $map[ $lang ] ) ) {
			return;
		}

		$menu_id = (int) $map[ $lang ];
		if ( $menu_id <= 0 ) {
			return;
		}

		// Remap the location before Mega Menu's modify_nav_menu_args (priority 99999) calls
		// get_valid_menu_id(), so it resolves the language-specific menu and sets args['menu']
		// to the correct WP_Term itself. Avoids touching wp_nav_menu_args directly.
		add_filter(
			'theme_mod_nav_menu_locations',
			static function( $locations ) use ( $location, $menu_id ) {
				$locations[ $location ] = $menu_id;
				return $locations;
			},
			PHP_INT_MAX
		);
	}


	/**
	 * Replace single-line assigned menu summary with one line per Polylang language.
	 *
	 * @param string $default_html Default Mega Menu HTML (possibly empty).
	 * @param string $location     Location slug.
	 * @return string
	 */
	public static function filter_location_assignment_summary_html( $default_html, $location ) {
		$multiline = self::build_polylang_per_language_assignment_summary_html( $location );
		if ( null !== $multiline && '' !== $multiline ) {
			return $multiline;
		}

		return $default_html;
	}


	/**
	 * True when Polylang has at least one valid menu assigned to this location in any language.
	 *
	 * @param bool|null $pre      Prior filter value (null = not handled).
	 * @param string    $location Location slug.
	 * @return bool|null
	 */
	public static function filter_location_has_assigned_nav_menu( $pre, $location ) {
		if ( null !== $pre ) {
			return $pre;
		}

		$row = self::get_polylang_nav_menu_location_map( $location );
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
	 * Build "Assigned menu" HTML: all languages on one row (edit link or "Not assigned" each).
	 * Each language is prefixed with a short tag from the Polylang slug, e.g. [en], [fr].
	 *
	 * @param string $location Location slug.
	 * @return string|null HTML string, or null to fall back to default behaviour.
	 */
	private static function build_polylang_per_language_assignment_summary_html( $location ) {
		$row = self::get_polylang_nav_menu_location_map( $location );
		if ( null === $row ) {
			return null;
		}

		if ( ! function_exists( 'PLL' ) || ! is_object( PLL() ) || ! isset( PLL()->model ) || ! is_object( PLL()->model ) ) {
			return null;
		}

		$languages = PLL()->model->get_languages_list();
		if ( empty( $languages ) ) {
			return null;
		}

		$parts = [];

		foreach ( $languages as $lang ) {
			$lang_slug = isset( $lang->slug ) ? (string) $lang->slug : '';
			if ( '' === $lang_slug ) {
				continue;
			}

			$mid      = isset( $row[ $lang_slug ] ) ? (int) $row[ $lang_slug ] : 0;
			$lang_tag = '[' . esc_html( strtolower( $lang_slug ) ) . ']';

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


	/**
	 * Remove trailing " {default language display name}" from a single label string.
	 *
	 * @param string $text Input label.
	 * @return string
	 */
	private static function strip_default_language_suffix_from_string( $text ) {
		$default_lang = pll_default_language( 'name' );
		if ( ! is_string( $default_lang ) || '' === $default_lang ) {
			return $text;
		}

		$clean = str_replace( ' ' . $default_lang, '', $text );

		return ( '' !== $clean ) ? $clean : $text;
	}


	/**
	 * Strip Polylang's duplicate `___` locations for Mega Menu's own location list (does not relabel base locations globally).
	 *
	 * @return void
	 */
	public static function normalize_registered_nav_menus() {
		if ( ! function_exists( 'pll_default_language' ) ) {
			return;
		}

		foreach ( get_registered_nav_menus() as $loc => $_description ) {
			if ( self::is_language_fork_location_slug( $loc ) ) {
				unregister_nav_menu( $loc );
			}
		}
	}


	/**
	 * Skip compiling CSS for synthetic fork locations (defensive; settings use canonical slugs).
	 *
	 * @param bool   $include     Whether to include this location.
	 * @param string $location_id Location slug.
	 * @return bool
	 */
	public static function filter_include_location_in_compiled_css( $include, $location_id ) {
		if ( ! $include ) {
			return false;
		}
		return ! self::is_language_fork_location_slug( $location_id );
	}


	/**
	 * Delete language-specific CSS transients Polylang may use.
	 *
	 * @return void
	 */
	public static function delete_language_css_transients() {
		if ( ! function_exists( 'PLL' ) || ! is_object( PLL() ) || ! isset( PLL()->model ) || ! is_object( PLL()->model ) ) {
			return;
		}

		foreach ( PLL()->model->get_languages_list() as $term ) {
			if ( isset( $term->locale ) ) {
				delete_transient( 'megamenu_css_' . strtolower( $term->locale ) );
			}
		}
	}


	/**
	 * Make the CSS transient key unique to the current Polylang language.
	 *
	 * @param string $key The base transient key.
	 * @return string
	 */
	public static function append_current_language_locale_to_css_key( $key ) {
		return self::append_locale_suffix( $key );
	}


	/**
	 * Make the CSS filename unique to the current Polylang language.
	 *
	 * @param string $filename The base CSS filename (without extension).
	 * @return string
	 */
	public static function append_current_language_locale_to_css_filename( $filename ) {
		return self::append_locale_suffix( $filename );
	}


	/**
	 * Append the current Polylang locale (lowercased) to a string, e.g. "style" → "style_en_us".
	 * Returns the string unchanged when no language is active.
	 *
	 * @param string $value Base string.
	 * @return string
	 */
	private static function append_locale_suffix( $value ) {
		$locale = strtolower( (string) pll_current_language( 'locale' ) );
		return $locale !== '' ? $value . '_' . $locale : $value;
	}
}
