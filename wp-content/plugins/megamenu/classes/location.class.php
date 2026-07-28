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
		 * Whether Max Mega Menu should run for this location on the front end:
		 * the location toggle is on and a real nav menu is assigned (not a stale term ID).
		 *
		 * @since 3.9.3
		 * @return bool
		 */
		public function is_active() {
			if ( ! $this->is_enabled() ) {
				return false;
			}
			return $this->get_valid_menu_id() > 0;
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
		 * Return the assigned nav menu term ID only if that menu still exists.
		 * Theme locations can retain a stale ID after the nav menu is deleted.
		 *
		 * @since 3.9.3
		 * @return int Term ID, or 0 when none or invalid.
		 */
		public function get_valid_menu_id() {
			$menu_id = $this->get_menu_id();
			if ( ! $menu_id ) {
				return 0;
			}
			$menu = wp_get_nav_menu_object( $menu_id );
			return ( $menu && ! is_wp_error( $menu ) ) ? $menu_id : 0;
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
		 * Return a single setting for this location, or $default if not set.
		 *
		 * @param  string $key     Setting key.
		 * @param  mixed  $default Fallback value.
		 * @return mixed
		 */
		public function get_setting( string $key, $default = null ) {
			return $this->settings[ $key ] ?? $default;
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


		/**
		 * Return the full SCSS source that would be sent to the compiler for this
		 * location and theme, without compiling it. Useful for debugging.
		 *
		 * @param  Mega_Menu_Theme $theme The theme to use.
		 * @return string Full SCSS source string.
		 */
		public function get_scss( Mega_Menu_Theme $theme ) {
			return $this->get_complete_scss( $theme );
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
			$menu_id        = $this->get_valid_menu_id();
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
			$vars['megamenu_pro_active']    = 'false';
			$vars['megamenu_pro_version']   = 'false';
			$vars['arrow_font']             = 'dashicons';
			$vars['arrow_font_weight']      = 'normal';
			foreach ( [ 'arrow_up', 'arrow_down', 'arrow_left', 'arrow_right' ] as $arrow_key ) {
				if ( isset( $theme_settings[ $arrow_key ] ) ) {
					if ( strpos( $theme_settings[ $arrow_key ], 'svg-' ) === 0 ) {
						$vars['arrow_font'] = 'svg';
						break;
					} elseif ( strpos( $theme_settings[ $arrow_key ], 'mat-' ) === 0 ) {
						$vars['arrow_font'] = 'var(--wp--preset--font-family--material-symbols)';
						break;
					}
				}
			}
			$vars['close_icon_font']        = 'dashicons';
			if ( isset( $theme_settings['close_icon'] ) && strpos( $theme_settings['close_icon'], 'mat-' ) === 0 ) {
				$vars['close_icon_font'] = 'var(--wp--preset--font-family--material-symbols)';
			} elseif ( isset( $theme_settings['close_icon'] ) && strpos( $theme_settings['close_icon'], 'svg-' ) === 0 ) {
				$vars['close_icon_font'] = 'svg';
			}
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

			if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'megamenu-pro/megamenu-pro.php' ) ) {
				$vars['megamenu_pro_active'] = 'true';
				if ( defined( 'MEGAMENU_PRO_VERSION' ) ) {
					$parts = explode( '.', MEGAMENU_PRO_VERSION );
					$vars['megamenu_pro_version'] = $parts[0] . ( isset( $parts[1] ) ? '.' . $parts[1] : '' );
				}
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

			$valid_theme_settings = Mega_Menu_Theme::get_default()->settings;

			foreach ( $theme_settings as $name => $value ) {

				if ( ! array_key_exists( $name, $valid_theme_settings ) ) {
					continue;
				}


				if ( in_array( $name, [ 'arrow_up', 'arrow_down', 'arrow_left', 'arrow_right', 'close_icon' ] ) ) {
					if ( strpos( $value, 'svg-' ) === 0 ) {
						if ( $name !== 'close_icon' ) {
							$svgs          = Mega_Menu_SVG_Icons::get_svg_arrows();
							$vars[ $name ] = isset( $svgs[ $value ] )
								? '"data:image/svg+xml,' . Mega_Menu_SVG_Icons::svg_to_data_uri( $svgs[ $value ] ) . '"'
								: '""';
						} else {
							$vars[ $name ] = 'none';
						}
					} else {
						$parts         = explode( '-', $value );
						$code          = end( $parts );
						$vars[ $name ] = $code == 'disabled' ? "''" : "'\\" . $code . "'";
					}
					continue;
				}

				if ( in_array( $name, [ 'menu_font_family', 'menu_item_link_font', 'panel_font_family', 'panel_header_font', 'panel_second_level_font', 'panel_third_level_font', 'flyout_link_family', 'tabbed_link_family' ] ) ) {
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
			$menu_id        = $this->get_valid_menu_id();
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
		 * HTML body for a failed SCSS compile (notice-safe): warning, compiler message, full source for CodeMirror.
		 *
		 * @param Exception $e             Compiler exception.
		 * @param string    $scss_source   Full SCSS string passed to the compiler (stdin).
		 * @return string
		 */
		private function format_scss_compile_error_message( Exception $e, $scss_source ) {
			$intro = __( 'Warning: CSS compilation failed. Please check your changes or revert the theme.', 'megamenu' );
			$html  = '<p>' . esc_html( $intro ) . '</p>';
			$html .= '<p>' . esc_html( $e->getMessage() ) . '</p>';
			$html .= '<p><label for="megamenu-compile-failed-scss"><strong>' . esc_html__( 'Full SCSS sent to the compiler:', 'megamenu' ) . '</strong></label></p>';
			$html .= '<div class="megamenu-compile-failed-scss-editor">';
			$html .= '<textarea id="megamenu-compile-failed-scss" readonly="readonly" rows="18" class="large-text code megamenu-compile-failed-scss__textarea" style="width:100%;box-sizing:border-box;">';
			$html .= esc_textarea( $scss_source );
			$html .= '</textarea></div>';

			return $html;
		}


		/**
		 * Compile SCSS using the legacy scssphp 0.0.12 compiler.
		 *
		 * @param  Mega_Menu_Theme $theme The resolved theme.
		 * @return string|WP_Error
		 */
		private function compile_scss_old( Mega_Menu_Theme $theme ) {
			if ( is_readable( MEGAMENU_PATH . 'vendor/scss/0.0.12/scss.inc.php' ) && ! class_exists( 'scssc' ) ) {
				include_once MEGAMENU_PATH . 'vendor/scss/0.0.12/scss.inc.php';
			}

			$scssc = new scssc();
			$scssc->setFormatter( 'scss_formatter' );

			foreach ( $this->scss_import_paths() as $path ) {
				$scssc->addImportPath( $path );
			}

			$scss_source = $this->get_complete_scss( $theme );

			try {
				return $scssc->compile( $scss_source );
			} catch ( Exception $e ) {
				return new WP_Error( 'scss_compile_fail', $this->format_scss_compile_error_message( $e, $scss_source ) );
			}
		}


		/**
		 * Compile SCSS using the scssphp 1.11.1 compiler.
		 *
		 * @param  Mega_Menu_Theme $theme The resolved theme.
		 * @return string|WP_Error
		 */
		private function compile_scss_new( Mega_Menu_Theme $theme ) {
			if ( is_readable( MEGAMENU_PATH . 'vendor/scss/1.11.1/scss.inc.php' ) && ! class_exists( 'MMMScssPhp\ScssPhp\Compiler' ) ) {
				require_once MEGAMENU_PATH . 'vendor/scss/1.11.1/scss.inc.php';
			}

			$scssc = new \MMMScssPhp\ScssPhp\Compiler();
			$scssc->setCharset( false );

			foreach ( $this->scss_import_paths() as $path ) {
				$scssc->addImportPath( $path );
			}

			$scss_source = $this->get_complete_scss( $theme );

			try {
				if ( method_exists( $scssc, 'compileString' ) ) {
					return $scssc->compileString( $scss_source )->getCss();
				} elseif ( method_exists( $scssc, 'compile' ) ) {
					return $scssc->compile( $scss_source );
				}
			} catch ( Exception $e ) {
				return new WP_Error( 'scss_compile_fail', $this->format_scss_compile_error_message( $e, $scss_source ) );
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
			$scss = file_get_contents( MEGAMENU_PATH . trailingslashit( 'css' ) . 'mixin.scss' );

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


		/**
		 * Return a panel width value suitable for a data attribute (read by JS).
		 *
		 * Pixel/rem/em values are intentionally excluded — the JS doesn't need them
		 * and passing them causes conflicts with CSS-driven sizing. Viewport units and
		 * non-numeric values (e.g. 'auto') are passed through so JS can consume them.
		 *
		 * @param  string $value Raw panel_width theme setting.
		 * @return string The original value, or '' when JS should ignore it.
		 */
		private static function resolve_panel_width_attribute( string $value ): string {
			if ( preg_match( '/^\d/', $value ) !== 1 ) {
				return $value;
			}
			if ( preg_match( '/^\d+(vw|vh|vmin|vmax)$/', $value ) === 1 ) {
				return $value;
			}
			return '';
		}


		/**
		 * Build the data-* attribute array for the menu wrapper element.
		 *
		 * @param  Mega_Menu_Theme $theme Active theme for this location.
		 * @return array<string, string>
		 */
		public function get_wrap_attributes( Mega_Menu_Theme $theme ) {
			$menu_id = $this->get_valid_menu_id();

			// Location settings.
			$effect              = $this->get_setting( 'effect', 'disabled' );
			$effect_speed        = $this->get_setting( 'effect_speed', '200' );
			$effect_mobile       = $this->get_setting( 'effect_mobile', 'disabled' );
			$second_click        = $this->get_setting( 'second_click', 'go' );
			$vertical_behaviour  = $this->get_setting( 'mobile_behaviour', 'standard' );
			$mobile_state        = $this->get_setting( 'mobile_state', 'collapse_all' );
			$unbind              = 'disabled' === $this->get_setting( 'unbind', 'enabled' ) ? 'false' : 'true';
			$effect_speed_mobile = 'disabled' !== $effect_mobile ? $this->get_setting( 'effect_speed_mobile', 0 ) : 0;

			$raw_event = $this->get_setting( 'event', 'hover_intent' );
			if ( 'hover' === $raw_event ) {
				$event = 'hover_intent';
			} elseif ( 'hover_' === $raw_event ) {
				$event = 'hover';
			} else {
				$event = $raw_event;
			}

			// Theme settings.
			$panel_width       = self::resolve_panel_width_attribute( $theme->get( 'panel_width' ) );
			$panel_inner_width = substr( trim( $theme->get( 'panel_inner_width' ) ), -1 ) !== '%' ? $theme->get( 'panel_inner_width' ) : '';
			$mobile_force_width = 'on' === $theme->get( 'mobile_menu_force_width' )
				? $theme->get( 'mobile_menu_force_width_selector', 'body' )
				: 'false';
			$breakpoint = absint( $theme->get( 'responsive_breakpoint' ) );

			// Hover intent.
			$hover_intent_defaults = [ 'timeout' => 300, 'interval' => 100 ];
			$hover_intent_params   = apply_filters( 'megamenu_javascript_localisation', $hover_intent_defaults );
			$hover_intent_timeout  = absint( $hover_intent_params['timeout'] ) !== $hover_intent_defaults['timeout'] ? absint( $hover_intent_params['timeout'] ) : '';
			$hover_intent_interval = absint( $hover_intent_params['interval'] ) !== $hover_intent_defaults['interval'] ? absint( $hover_intent_params['interval'] ) : '';

			$global_settings = get_option( 'megamenu_settings', [] );

			return apply_filters(
				'megamenu_wrap_attributes',
				[
					'id'                         => '%1$s',
					'class'                      => '%2$s mega-no-js',
					'data-event'                 => $event,
					'data-effect'                => $effect,
					'data-effect-speed'          => $effect_speed,
					'data-effect-mobile'         => $effect_mobile,
					'data-effect-speed-mobile'   => $effect_speed_mobile,
					'data-panel-width'           => $panel_width,
					'data-panel-inner-width'     => $panel_inner_width,
					'data-mobile-force-width'    => $mobile_force_width,
					'data-second-click'          => $second_click,
					'data-document-click'        => 'collapse',
					'data-vertical-behaviour'    => $vertical_behaviour,
					'data-breakpoint'            => $breakpoint,
					'data-unbind'                => $unbind,
					'data-mobile-state'          => $mobile_state,
					'data-mobile-direction'      => 'vertical',
					'data-hover-intent-timeout'  => $hover_intent_timeout,
					'data-hover-intent-interval' => $hover_intent_interval,
				],
				$menu_id,
				$this->settings,
				$global_settings,
				$this->id
			);
		}


		// -------------------------------------------------------------------------
		// Static factories
		// -------------------------------------------------------------------------

		/**
		 * Register custom locations (created in the Mega Menu admin) with WordPress.
		 * Hooked to after_setup_theme.
		 *
		 * @return void
		 */
		public static function register_custom() {
			$locations = get_option( 'megamenu_locations' );
			if ( is_array( $locations ) && count( $locations ) ) {
				foreach ( $locations as $key => $val ) {
					register_nav_menu( $key, $val );
				}
			}
		}


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
