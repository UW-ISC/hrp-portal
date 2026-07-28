<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Style_Manager' ) ) :

	/**
	 * Manages the plugin's CSS generation, caching, and enqueueing, including
	 * SCSS compilation and filesystem storage. WPML is handled in {@see Mega_Menu_Integration_Wpml}. Polylang is handled in {@see Mega_Menu_Integration_Polylang}.
	 *
	 * @since   1.0
	 * @package MegaMenu
	 */
	final class Mega_Menu_Style_Manager {

		/**
		 * Saved plugin settings from the database.
		 *
		 * @var array
		 */
		public $settings = [];


		/**
		 * When true, {@see delete_cache_after_nav_menu_locations_save} will run on shutdown.
		 *
		 * @var bool
		 */
		private $pending_delete_cache_after_nav_menu_locations = false;


		/**
		 * Constructor. Loads settings from the database.
		 *
		 * @since 1.0
		 */
		public function __construct() {
			$settings       = get_option( 'megamenu_settings', [] );
			$this->settings = is_array( $settings ) ? $settings : [];
		}


		/**
		 * Register all WordPress actions and filters used by the style manager.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function setup_actions() {
			add_action( 'megamenu_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			add_action( 'megamenu_enqueue_styles', [ $this, 'enqueue_styles' ] );

			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 999 );
			add_action( 'wp_head', [ $this, 'head_css' ], 9999 );
			add_action( 'megamenu_delete_cache', [ $this, 'delete_cache' ] );
			add_action( 'after_switch_theme', [ $this, 'delete_cache' ] );

			add_filter( 'pre_set_theme_mod_nav_menu_locations', [ $this, 'schedule_delete_cache_on_nav_menu_locations_change' ], 10, 2 );

			add_action( 'megamenu_head_css', [ $this, 'head_css' ], 999 );

			add_filter( 'megamenu_scripts_in_footer', [ $this, 'scripts_in_footer' ] );
			add_filter( "filesystem_method", [ $this, "use_direct_filesystem_method" ], 10, 4 );

		}


		/**
		 * Always use the 'direct' filesystem method when writing the style.css file.
		 *
		 * @since 3.0.1
		 * @param string $method                    Filesystem access method.
		 * @param mixed  $args                      Optional arguments passed to the filesystem method.
		 * @param string $context                   Full path to the directory that is tested for being writable.
		 * @param bool   $allow_relaxed_file_ownership Whether to allow group file ownership.
		 * @return string 'direct' within the plugin's upload directory, otherwise unchanged method.
		 */
		public function use_direct_filesystem_method( $method, $args, $context, $allow_relaxed_file_ownership ) {
			if ( $method != 'direct' && str_contains( $context, "/maxmegamenu" ) ) {
				return 'direct';
			}

		    return $method;
		}


		/**
		 * Determines whether to load JavaScript in the footer based on the configured option.
		 *
		 * @since 2.9
		 * @return bool True to load scripts in the footer, false to load in the head.
		 */
		public function scripts_in_footer() {
			return ( $this->settings['js'] ?? '' ) !== 'head';
		}


		/**
		 * When menu-location assignments change, invalidate CSS if Max Mega Menu is enabled for an affected location.
		 * Defer to `shutdown` so `nav_menu_locations` is saved before regeneration (see `set_theme_mod`).
		 *
		 * @param array|false $new_value New location => menu ID map.
		 * @param array|false $old_value Previous map or false.
		 * @return array Map passed through unchanged.
		 */
		public function schedule_delete_cache_on_nav_menu_locations_change( $new_value, $old_value ) {
			if ( ! is_array( $new_value ) ) {
				$new_value = [];
			}
			if ( ! is_array( $old_value ) ) {
				$old_value = [];
			}

			$keys = array_unique( array_merge( array_keys( $old_value ), array_keys( $new_value ) ) );

			foreach ( $keys as $location ) {
				$old_id = isset( $old_value[ $location ] ) ? (int) $old_value[ $location ] : 0;
				$new_id = isset( $new_value[ $location ] ) ? (int) $new_value[ $location ] : 0;

				if ( $old_id === $new_id ) {
					continue;
				}

				$mega = Mega_Menu_Location::find( $location );

				if ( $mega && $mega->is_enabled() ) {
					if ( ! $this->pending_delete_cache_after_nav_menu_locations ) {
						$this->pending_delete_cache_after_nav_menu_locations = true;
						add_action( 'shutdown', [ $this, 'delete_cache_after_nav_menu_locations_save' ], 999 );
					}
					break;
				}
			}

			return $new_value;
		}


		/**
		 * Run after theme mods are persisted so `get_nav_menu_locations()` matches the new assignments.
		 *
		 * @return void
		 */
		public function delete_cache_after_nav_menu_locations_save() {
			if ( ! $this->pending_delete_cache_after_nav_menu_locations ) {
				return;
			}

			$this->pending_delete_cache_after_nav_menu_locations = false;

			do_action( 'megamenu_delete_cache' );
		}


		/**
		 * Clear external plugin caches when CSS is updated or menu settings are changed.
		 *
		 * @since 2.0
		 * @return void
		 */
		public function clear_external_caches() {
			// Breeze: https://wordpress.org/plugins/breeze/
			do_action( 'breeze_clear_all_cache' );
		}


		/**
		 * Return the version of MMM that was used to generate the current CSS file.
		 *
		 * @since 1.0
		 * @return string|false Plugin version string, or false if not set.
		 */
		public static function get_css_version() {
			if ( $version = get_option('megamenu_css_version') ) {
				return $version;
			}

			return get_transient('megamenu_css_version');
		}


		/**
		 * Return the timestamp when the menu CSS was last generated.
		 *
		 * @since 1.0
		 * @return int|false Unix timestamp, or false if not set.
		 */
		public static function get_css_last_updated() {
			if ( $date = get_option('megamenu_css_last_updated') ) {
				return $date;
			}

			return get_transient('megamenu_css_last_updated');
		}


		/**
		 * Return the default menu theme settings array.
		 * Delegates to Mega_Menu_Theme::get_default() for backward compatibility.
		 *
		 * @since 1.0
		 * @return array Default theme settings.
		 */
		public function get_default_theme() {
			return Mega_Menu_Theme::get_default()->settings;
		}


		/**
		 * Return a filtered list of all themes (default + saved custom), fully merged.
		 * Delegates to Mega_Menu_Theme::get_all() for backward compatibility.
		 *
		 * @since 1.0
		 * @return array Map of theme ID to settings array.
		 */
		public function get_themes() {
			$theme_objects = Mega_Menu_Theme::get_all();
			$themes        = [];

			foreach ( $theme_objects as $id => $theme ) {
				$themes[ $id ] = $theme->settings;
			}

			return $themes;
		}


		/**
		 * Whether the plugin is running in debug mode.
		 *
		 * @since 1.3.1
		 * @return bool True if MEGAMENU_DEBUG is defined and true.
		 */
		private function is_debug_mode() {
			return ( defined( 'MEGAMENU_DEBUG' ) && MEGAMENU_DEBUG === true );
		}


		/**
		 * Return the menu CSS. Uses the transient cache when available.
		 *
		 * @since 1.3.1
		 * @return string Compiled CSS string.
		 */
		public function get_css() {

			if ( ( $css = $this->get_cached_css() ) && ! $this->is_debug_mode() ) {
				return $css;
			} else {
				return $this->generate_css();
			}
		}


		/**
		 * Compile CSS for all active menu locations (same rules as {@see generate_css()}).
		 *
		 * @since 3.9.3
		 * @param bool $fail_on_location_error When true, return a {@see WP_Error} if any location fails to compile.
		 *                                     When false, skip failed locations (legacy {@see generate_css()} behaviour).
		 * @return string|WP_Error Full CSS string including filters and `.wp-block {}` hack, or {@see WP_Error}.
		 *
		 * The {@see 'megamenu_include_location_in_compiled_css'} filter controls whether each location is compiled (default true).
		 */
		private function compile_active_menu_css( $fail_on_location_error = false ) {

			if ( function_exists( 'wp_raise_memory_limit' ) ) {
				wp_raise_memory_limit();
			}

			$settings       = get_option( 'megamenu_settings', [] );
			$this->settings = is_array( $settings ) ? $settings : [];

			if ( empty( $this->settings ) ) {
				if ( $fail_on_location_error ) {
					return new WP_Error(
						'megamenu_no_settings',
						__( 'CSS generation failed: no menu settings found.', 'megamenu' )
					);
				}

				return '/** CSS Generation Failed. No menu settings found **/';
			}

			$date = date( 'l jS F Y H:i:s e' );
			$time = time();

			$css = '@charset "UTF-8";' . "\n\n";
			$css .= "/** THIS FILE IS AUTOMATICALLY GENERATED - DO NOT MAKE MANUAL EDITS! **/\n";
			$css .= "/** Custom CSS should be added to Mega Menu > Menu Themes > Custom Styling **/\n\n";
			$css .= ".mega-menu-last-modified-{$time} { content: '{$date}'; }\n\n";

			foreach ( Mega_Menu_Location::get_all() as $location ) {
				if ( $location->is_active() && apply_filters( 'megamenu_include_location_in_compiled_css', true, $location->id ) ) {
					$compiled_css = $location->generate_css();

					if ( is_wp_error( $compiled_css ) ) {
						if ( $fail_on_location_error ) {
							return $compiled_css;
						}
						continue;
					}

					$css .= $compiled_css;
				}
			}

			if ( strlen( $css ) ) {
				$css = apply_filters( 'megamenu_compiled_css', $css );

				$css .= ".wp-block {}"; // hack required for loading CSS in site editor https://github.com/WordPress/gutenberg/issues/40603#issuecomment-1112807162
			}

			return $css;
		}


		/**
		 * Generate and cache the CSS for all active menus.
		 * CSS is compiled by scssphp using the file located in /css/megamenu.scss.
		 *
		 * @since 1.0
		 * @return string Compiled CSS string, or an error comment if generation failed.
		 */
		public function generate_css() {

			$css = $this->compile_active_menu_css( false );

			if ( is_wp_error( $css ) ) {
				return '';
			}

			if ( $this->settings && strlen( $css ) ) {
				$this->set_cached_css( $css );
				$this->save_to_filesystem( $css );
			}

			return $css;
		}

		/**
		 * Saves the generated CSS to the uploads folder.
		 *
		 * @since 1.6.1
		 * @param string $css The compiled CSS to write.
		 * @return void
		 */
		private function save_to_filesystem( $css ) {
			global $wp_filesystem;

			if ( ! $wp_filesystem ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			$upload_dir = wp_upload_dir();
			$filename   = $this->get_css_filename();

			$dir = trailingslashit( $upload_dir['basedir'] ) . 'maxmegamenu/';

			delete_option( 'megamenu_failed_to_write_css_to_filesystem' );

			WP_Filesystem(false, $dir);

			if ( ! $wp_filesystem->is_dir( $dir ) ) {
				$wp_filesystem->mkdir( $dir );
			}

			if ( ! $wp_filesystem->put_contents( $dir . $filename, $css ) ) {
				// File write failed.
				// Update CSS output option to 'head' to stop us from attempting to regenerate the CSS on every request.

				$method = $this->get_css_output_method();

				if ( in_array( $method, [ 'disabled' ] ) ) {
					return;
				}

				$this->settings['css'] = 'head';
				update_option( 'megamenu_settings', $this->settings );

				update_option( 'megamenu_failed_to_write_css_to_filesystem', 'true' );
			}

		}


		/**
		 * Before a theme is saved, attempt to compile it to verify it produces valid CSS.
		 * Delegates to Mega_Menu_Theme for backward compatibility.
		 *
		 * @since 1.3
		 * @param array $theme Theme settings array to test.
		 * @return string|WP_Error Compiled CSS on success, or WP_Error on failure.
		 */
		public function test_theme_compilation( $theme ) {
			return Mega_Menu_Theme::from_settings( $theme )->test_compilation();
		}


		/**
		 * Compiles raw SCSS into CSS for a particular menu location.
		 * Delegates to Mega_Menu_Location::generate_css() for backward compatibility.
		 *
		 * @since 1.3
		 * @param string $location The menu location slug.
		 * @param array  $theme    Theme settings array.
		 * @param int    $menu_id  Menu term ID.
		 * @return string|WP_Error Compiled CSS on success, or WP_Error on failure.
		 */
		public function generate_css_for_location( $location, $theme, $menu_id ) {
			$location_obj = Mega_Menu_Location::find( $location );

			if ( ! $location_obj ) {
				$location_obj = new Mega_Menu_Location( $location, $location );
			}

			return $location_obj->generate_css( Mega_Menu_Theme::from_settings( $theme ) );
		}

		/**
		 * Enqueue public CSS files required by Mega Menu.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function enqueue_styles() {
			if ( ! ( defined( 'MEGAMENU_PREVIEW' ) && MEGAMENU_PREVIEW ) && 'fs' === $this->get_css_output_method() ) {
				$this->enqueue_fs_style();
			}

			do_action( 'megamenu_enqueue_public_scripts' );

		}

		/**
		 * Enqueue public JavaScript files required by Mega Menu.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function enqueue_scripts() {
			$js_path = MEGAMENU_BASE_URL . 'js/maxmegamenu.js';

			$dependencies = apply_filters( 'megamenu_javascript_dependencies', [ 'jquery', 'hoverIntent' ] );

			$scripts_in_footer = apply_filters( 'megamenu_scripts_in_footer', true );

			if ( defined( 'MEGAMENU_SCRIPTS_IN_FOOTER' ) ) {
				$scripts_in_footer = MEGAMENU_SCRIPTS_IN_FOOTER;
			}

			$handle = apply_filters( 'megamenu_javascript_handle', 'megamenu' );

			wp_enqueue_script( $handle, $js_path, $dependencies, MEGAMENU_VERSION, $scripts_in_footer );

			$params = apply_filters( 'megamenu_javascript_localisation', [] );

			if ( count( $params) ) {
				wp_localize_script( $handle, 'megamenu', $params );
			}
		}



		/**
		 * Enqueue the stylesheet saved to the uploads filesystem.
		 *
		 * @since 1.6.1
		 * @return void
		 */
		public function enqueue_fs_style() {

			$upload_dir = wp_upload_dir();

			$filename = $this->get_css_filename();

			$filepath = trailingslashit( $upload_dir['basedir'] ) . 'maxmegamenu/' . $filename;

			if ( ! is_file( $filepath ) || $this->is_debug_mode() ) {
				// regenerate the CSS and save to filesystem.
				$this->generate_css();
			}

			// file should now exist.
			if ( is_file( $filepath ) ) {

				$css_url = trailingslashit( $upload_dir['baseurl'] ) . 'maxmegamenu/' . $filename;

				$protocol = is_ssl() ? 'https://' : 'http://';

				// ensure we're using the correct protocol.
				$css_url = str_replace( [ 'http://', 'https://' ], $protocol, $css_url );

				wp_enqueue_style( 'megamenu', $css_url, false, substr( md5( (string) filemtime( $filepath ) ), 0, 6 ) );

			}

		}


		/**
		 * Store compiled CSS in the transient cache and update version/timestamp options.
		 *
		 * @since 1.6.1
		 * @param string $css The compiled CSS to cache.
		 * @return void
		 */
		private function set_cached_css( $css ) {
			// set a far expiration date to prevent transient from being autoloaded.
			$hundred_years_in_seconds = 3153600000;

			set_transient( $this->get_transient_key(), $css, $hundred_years_in_seconds );
			update_option( 'megamenu_css_version', MEGAMENU_VERSION );
			update_option( 'megamenu_css_last_updated', time() );
		}


		/**
		 * Return the cached CSS if it exists.
		 *
		 * @since 1.9
		 * @return string|false Cached CSS string, or false if the transient has expired.
		 */
		private function get_cached_css() {
			return get_transient( $this->get_transient_key() );
		}


		/**
		 * Regenerate CSS, flush the filesystem directory, and repopulate the cache.
		 *
		 * @return bool True on success, false if CSS compilation produced a WP_Error.
		 */
		public function delete_cache() {
			global $wp_filesystem;

			$css = $this->compile_active_menu_css( true );

			if ( is_wp_error( $css ) ) {
				do_action( 'megamenu_delete_cache_failed', $css );
				return false;
			}

			if ( ! $wp_filesystem ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			$upload_dir = wp_upload_dir();
			$dir        = trailingslashit( $upload_dir['basedir'] ) . 'maxmegamenu/';

			WP_Filesystem(false, $dir);
			$wp_filesystem->rmdir( $dir, true );

			delete_transient( $this->get_transient_key() );

			if ( strlen( $css ) ) {
				$this->set_cached_css( $css );
				$this->save_to_filesystem( $css );
			}

			$this->clear_external_caches();

			do_action( 'megamenu_after_delete_cache' );

			return true;

		}


		/**
		 * Return the key to use for the CSS transient
		 *
		 * @since 1.9
		 * @return string
		 */
		private function get_transient_key() {
			return apply_filters( 'megamenu_css_transient_key', 'megamenu_css' );
		}


		/**
		 * Return the filename to use for the stylesheet.
		 * The filename is filtered to be unique on multisite setups.
		 *
		 * @since 1.6.1
		 * @return string CSS filename with .css extension.
		 */
		public function get_css_filename() {
			return apply_filters( 'megamenu_css_filename', 'style' ) . '.css';
		}


		/**
		 * Return the configured CSS output method.
		 *
		 * @since 1.0
		 * @return string 'fs' (filesystem), 'head', or 'disabled'.
		 */
		private function get_css_output_method() {
			return $this->settings['css'] ?? 'fs';
		}


		/**
		 * Output the Mega Menu CSS inline in the <head>.
		 *
		 * @since 1.3.1
		 * @return void
		 */
		public function head_css() {

			if ( defined( 'MEGAMENU_PREVIEW' ) && MEGAMENU_PREVIEW ) {
				$css = $this->get_css();
				echo '<style class="megamenu-css">' . str_replace( [ '  ', "\n" ], '', $css ) . "</style>\n";
				return;
			}

			$method = $this->get_css_output_method();

			// Filesystem and disabled modes use linked styles or no CSS — do not emit a
			// placeholder <style class="megamenu-css"> (breaks block editor iframe rules in WP 6.9+).
			if ( in_array( $method, [ 'disabled', 'fs' ], true ) ) {
				return;
			}

			$css = $this->get_css();

			echo '<style class="megamenu-css">' . str_replace( [ '  ', "\n" ], '', $css ) . "</style>\n";

		}


	}

endif;
