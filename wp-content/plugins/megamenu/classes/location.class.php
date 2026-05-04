<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Location' ) ) :

	/**
	 * Represents a single menu location, its Mega Menu settings, and CSS generation.
	 *
	 * @since   3.9
	 * @package MegaMenu
	 */
	class Mega_Menu_Location {

		/**
		 * Location slug (e.g. 'primary', 'max_mega_menu_1').
		 *
		 * @var string
		 */
		public $id;

		/**
		 * Human-readable location title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * Per-location settings slice from the megamenu_settings option.
		 *
		 * @var array
		 */
		public $settings;


		/**
		 * Constructor.
		 *
		 * @param string $id       Location slug.
		 * @param string $title    Location title.
		 * @param array  $settings Per-location settings.
		 */
		public function __construct( $id, $title, $settings = [] ) {
			$this->id       = $id;
			$this->title    = $title;
			$this->settings = $settings;
		}


		/**
		 * Whether Mega Menu has been enabled for this location.
		 *
		 * @return bool
		 */
		public function is_enabled() {
			return isset( $this->settings['enabled'] ) && true === boolval( $this->settings['enabled'] );
		}


		/**
		 * Return the theme ID assigned to this location.
		 * Defaults to 'default' if none is saved.
		 *
		 * @return string
		 */
		public function get_theme_id() {
			return isset( $this->settings['theme'] ) ? $this->settings['theme'] : 'default';
		}


		/**
		 * Return the WP nav menu term ID assigned to this location.
		 * Returns 0 if no menu has been assigned.
		 *
		 * @return int
		 */
		public function get_menu_id() {
			$locations = get_nav_menu_locations();
			return isset( $locations[ $this->id ] ) ? (int) $locations[ $this->id ] : 0;
		}


		/**
		 * Return the full settings array for this location.
		 *
		 * @return array
		 */
		public function get_settings() {
			return $this->settings;
		}


		/**
		 * Generate compiled CSS for this location.
		 *
		 * An optional theme may be passed (e.g. for preview/test compilation). When
		 * omitted the location fetches its own assigned theme.
		 *
		 * @param  Mega_Menu_Theme|null $theme Optional theme override.
		 * @return string|WP_Error Compiled CSS string, or WP_Error on failure.
		 */
		public function generate_css( $theme = null ) {
			if ( null === $theme ) {
				$theme = Mega_Menu_Theme::find( $this->get_theme_id() );
			}

			if ( ( defined( 'MEGAMENU_PRO_VERSION' ) && version_compare( MEGAMENU_PRO_VERSION, '2.3.1' ) < 0 ) || ( defined( 'MEGAMENU_SCSS_COMPILER_COMPAT' ) && MEGAMENU_SCSS_COMPILER_COMPAT ) ) {
				return $this->compile_scss_old( $theme );
			}

			return $this->compile_scss_new( $theme );
		}


		// -------------------------------------------------------------------------
		// Private: SCSS compilation
		// -------------------------------------------------------------------------

		/**
		 * SCSS variable map for this location and theme (after `megamenu_scss_variables`).
		 *
		 * @param Mega_Menu_Theme $theme Active theme.
		 * @return array<string, string>
		 */
		public function get_scss_variables( Mega_Menu_Theme $theme ) {
			return $this->build_scss_variables( $theme );
		}


		/**
		 * Build the SCSS variables array for this location and theme.
		 *
		 * @param Mega_Menu_Theme $theme Active theme.
		 * @return array<string, string>
		 */
		private function build_scss_variables( Mega_Menu_Theme $theme ) {
			$location_id    = $this->id;
			$menu_id        = $this->get_menu_id();
			$theme_settings = $theme->settings;

			$sanitized_location = str_replace( apply_filters( 'megamenu_location_replacements', [ '-', ' ' ] ), '-', $location_id );

			$wrap_selector = apply_filters( 'megamenu_scss_wrap_selector', "#mega-menu-wrap-{$sanitized_location}", $menu_id, $location_id );
			$menu_selector = apply_filters( 'megamenu_scss_menu_selector', "#mega-menu-{$sanitized_location}", $menu_id, $location_id );

			$vars['date']                   = "'" . date( 'l jS F Y H:i:s e' ) . "'";
			$vars['time']                   = "'" . time() . "'";
			$vars['wrap']                   = "'$wrap_selector'";
			$vars['menu']                   = "'$menu_selector'";
			$vars['location']               = "'$sanitized_location'";
			$vars['menu_id']                = "'$menu_id'";
			$vars['elementor_pro_active']   = 'false';
			$vars['arrow_font']             = 'dashicons';
			$vars['arrow_font_weight']      = 'normal';
			$vars['close_icon_font']        = 'dashicons';
			$vars['close_icon_font_weight'] = 'normal';
			$vars['arrow_combinator']       = "'>'";
			$vars['css_type']               = isset( $theme_settings['use_flex_css'] ) && $theme_settings['use_flex_css'] == 'on' ? 'flex' : 'standard';

			$current_wp_theme = wp_get_theme();
			$vars['wp_theme'] = strtolower( str_replace( [ '.', ' ' ], '_', $current_wp_theme->template ) );

			if ( empty( $vars['wp_theme'] ) ) {
				$vars['wp_theme'] = 'unknown';
			}

			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
				$vars['elementor_pro_active'] = 'true';
			}

			if ( isset( $this->settings['effect_speed'] ) && absint( $this->settings['effect_speed'] ) > 0 ) {
				$vars['effect_speed'] = absint( $this->settings['effect_speed'] ) . 'ms';
			} else {
				$vars['effect_speed'] = '200ms';
			}

			if ( isset( $this->settings['effect_speed_mobile'] ) && absint( $this->settings['effect_speed_mobile'] ) > 0 ) {
				$vars['effect_speed_mobile'] = absint( $this->settings['effect_speed_mobile'] ) . 'ms';
			} else {
				$vars['effect_speed_mobile'] = '200ms';
			}

			$vars['effect_mobile'] = isset( $this->settings['effect_mobile'] ) ? $this->settings['effect_mobile'] : 'disabled';

			foreach ( $theme_settings as $name => $value ) {

				if ( in_array( $name, [ 'arrow_up', 'arrow_down', 'arrow_left', 'arrow_right', 'close_icon' ] ) ) {
					$parts         = explode( '-', $value );
					$code          = end( $parts );
					$vars[ $name ] = $code == 'disabled' ? "''" : "'\\" . $code . "'";
					continue;
				}

				if ( in_array( $name, [ 'menu_item_link_font', 'panel_font_family', 'panel_header_font', 'panel_second_level_font', 'panel_third_level_font', 'flyout_link_family', 'tabbed_link_family' ] ) ) {
					$vars[ $name ] = "'" . stripslashes( htmlspecialchars_decode( $value ) ) . "'";

					$font_name_with_single_quotes = $vars[ $name ];
					$font_name_with_no_quotes     = str_replace( "'", '', $font_name_with_single_quotes );
					$font_name_parts              = explode( ' ', $font_name_with_no_quotes );

					if ( is_array( $font_name_parts ) ) {
						foreach ( $font_name_parts as $part ) {
							if ( is_numeric( $part ) ) {
								$vars[ $name ] = "\"{$font_name_with_single_quotes}\"";
							}
						}
					}
					continue;
				}

				if ( in_array( $name, [ 'responsive_text' ] ) ) {
					$vars[ $name ] = strlen( $value ) ? "'" . do_shortcode( $value ) . "'" : "''";
					continue;
				}

				if ( in_array( $name, [ 'panel_width', 'panel_inner_width', 'mobile_menu_force_width_selector' ] ) ) {
					if ( preg_match( '/^\d/', $value ) !== 1 ) {
						$vars[ $name ] = '100%';
						continue;
					}
				}

				if ( $name != 'custom_css' ) {
					$vars[ $name ] = $value;
				}
			}

			// Non-standard characters in the title will break CSS compilation.
			unset( $vars['title'] );

			return apply_filters( 'megamenu_scss_variables', $vars, $location_id, $theme_settings, $menu_id, $theme->id );
		}


		/**
		 * Build the complete SCSS string for this location using the given theme.
		 *
		 * @param  Mega_Menu_Theme $theme The resolved theme.
		 * @return string Full SCSS ready for compilation.
		 */
		private function get_complete_scss( Mega_Menu_Theme $theme ) {
			$location_id    = $this->id;
			$menu_id        = $this->get_menu_id();
			$theme_settings = $theme->settings;

			$vars = $this->build_scss_variables( $theme );

			$scss = '';
			foreach ( $vars as $name => $value ) {
				$scss .= '$' . $name . ': ' . $value . ";\n";
			}

			$scss .= $this->load_scss_file( $theme );
			$scss .= stripslashes( html_entity_decode( $theme_settings['custom_css'], ENT_QUOTES ) );

			return apply_filters( 'megamenu_scss', $scss, $location_id, $theme_settings, $menu_id );
		}


		/**
		 * Compile SCSS using the legacy scssphp 0.0.12 compiler.
		 *
		 * @param  Mega_Menu_Theme $theme The resolved theme.
		 * @return string|WP_Error
		 */
		private function compile_scss_old( Mega_Menu_Theme $theme ) {
			if ( is_readable( MEGAMENU_PATH . 'classes/scss/0.0.12/scss.inc.php' ) && ! class_exists( 'scssc' ) ) {
				include_once MEGAMENU_PATH . 'classes/scss/0.0.12/scss.inc.php';
			}

			$scssc = new scssc();
			$scssc->setFormatter( 'scss_formatter' );

			foreach ( $this->scss_import_paths() as $path ) {
				$scssc->addImportPath( $path );
			}

			try {
				return $scssc->compile( $this->get_complete_scss( $theme ) );
			} catch ( Exception $e ) {
				$message = __( 'Warning: CSS compilation failed. Please check your changes or revert the theme.', 'megamenu' );
				return new WP_Error( 'scss_compile_fail', $message . '<br /><br />' . $e->getMessage() );
			}
		}


		/**
		 * Compile SCSS using the scssphp 1.11.1 compiler.
		 *
		 * @param  Mega_Menu_Theme $theme The resolved theme.
		 * @return string|WP_Error
		 */
		private function compile_scss_new( Mega_Menu_Theme $theme ) {
			if ( is_readable( MEGAMENU_PATH . 'classes/scss/1.11.1/scss.inc.php' ) && ! class_exists( 'MMMScssPhp\ScssPhp\Compiler' ) ) {
				require_once MEGAMENU_PATH . 'classes/scss/1.11.1/scss.inc.php';
			}

			$scssc = new \MMMScssPhp\ScssPhp\Compiler();
			$scssc->setCharset( false );

			foreach ( $this->scss_import_paths() as $path ) {
				$scssc->addImportPath( $path );
			}

			try {
				if ( method_exists( $scssc, 'compileString' ) ) {
					return $scssc->compileString( $this->get_complete_scss( $theme ) )->getCss();
				} elseif ( method_exists( $scssc, 'compile' ) ) {
					return $scssc->compile( $this->get_complete_scss( $theme ) );
				}
			} catch ( Exception $e ) {
				$message = __( 'Warning: CSS compilation failed. Please check your changes or revert the theme.', 'megamenu' );
				return new WP_Error( 'scss_compile_fail', $message . '<br /><br />' . $e->getMessage() );
			}
		}


		/**
		 * Return the SCSS import paths.
		 *
		 * @return array
		 */
		private function scss_import_paths() {
			return apply_filters(
				'megamenu_scss_import_paths',
				[
					trailingslashit( get_stylesheet_directory() ) . trailingslashit( 'megamenu' ),
					trailingslashit( get_stylesheet_directory() ),
					trailingslashit( get_template_directory() ) . trailingslashit( 'megamenu' ),
					trailingslashit( get_template_directory() ),
					trailingslashit( WP_PLUGIN_DIR ),
				]
			);
		}


		/**
		 * Load the SCSS file contents for the given theme, preferring child-theme /
		 * parent-theme overrides before falling back to the bundled plugin file.
		 *
		 * @param  Mega_Menu_Theme $theme The resolved theme.
		 * @return string Combined SCSS file contents.
		 */
		private function load_scss_file( Mega_Menu_Theme $theme ) {
			$scss  = file_get_contents( MEGAMENU_PATH . trailingslashit( 'css' ) . 'mixin.scss' );
			$scss .= file_get_contents( MEGAMENU_PATH . trailingslashit( 'css' ) . 'reset.scss' );

			foreach ( $this->possible_scss_file_locations( $theme ) as $path ) {
				if ( file_exists( $path ) ) {
					$scss .= file_get_contents( $path );
					// @todo: add break once custom SCSS file warning is in place.
				}
			}

			$scss .= file_get_contents( MEGAMENU_PATH . trailingslashit( 'css' ) . 'compatibility.scss' );

			return apply_filters( 'megamenu_load_scss_file_contents', $scss );
		}


		/**
		 * Return all possible SCSS file locations in priority order.
		 *
		 * @param  Mega_Menu_Theme $theme The resolved theme.
		 * @return array
		 */
		private function possible_scss_file_locations( Mega_Menu_Theme $theme ) {
			return apply_filters(
				'megamenu_scss_locations',
				[
					trailingslashit( get_stylesheet_directory() ) . trailingslashit( 'megamenu' ) . 'megamenu.scss', // child theme
					trailingslashit( get_template_directory() ) . trailingslashit( 'megamenu' ) . 'megamenu.scss', // parent theme
					$this->default_scss_file_location( $theme ),
				]
			);
		}


		/**
		 * Return the absolute path to the bundled SCSS file, chosen based on
		 * whether the theme uses flex CSS.
		 *
		 * @param  Mega_Menu_Theme $theme The resolved theme.
		 * @return string
		 */
		private function default_scss_file_location( Mega_Menu_Theme $theme ) {
			$use_flex = $theme->get( 'use_flex_css' ) === 'on';
			$filename = $use_flex ? 'megamenu.flex.scss' : 'megamenu.scss';
			return MEGAMENU_PATH . trailingslashit( 'css' ) . $filename;
		}


		// -------------------------------------------------------------------------
		// Static factories
		// -------------------------------------------------------------------------

		/**
		 * Return all registered menu locations as Mega_Menu_Location instances.
		 *
		 * Combines theme-registered locations (register_nav_menus) with any
		 * custom locations created via the Mega Menu admin.
		 *
		 * @return Mega_Menu_Location[]
		 */
		public static function get_all() {
			$all_settings = get_option( 'megamenu_settings', [] );

			// Theme-registered locations.
			$registered = get_registered_nav_menus();

			// Custom locations created within the Mega Menu admin.
			$custom = get_option( 'megamenu_locations', [] );

			if ( is_array( $custom ) ) {
				$registered = array_merge( $registered, $custom );
			}

			$locations = [];

			foreach ( $registered as $id => $title ) {
				$settings         = isset( $all_settings[ $id ] ) ? (array) $all_settings[ $id ] : [];
				$locations[ $id ] = new self( $id, $title, $settings );
			}

			return $locations;
		}


		/**
		 * Find a single location by its slug.
		 * Returns null if the location is not registered.
		 *
		 * @param  string $id Location slug.
		 * @return Mega_Menu_Location|null
		 */
		public static function find( $id ) {
			$all = self::get_all();
			return isset( $all[ $id ] ) ? $all[ $id ] : null;
		}

	}

endif;
