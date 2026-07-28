<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mega_Menu_Location_Preview' ) ) :

	/**
	 * Menu preview (admin-post iframe) and preview trigger markup for locations.
	 *
	 * Preview UI lives in the location settings modal ({@see Mega_Menu_Page_Locations::render_location_settings_dialog_markup()}).
	 *
	 * @package MegaMenu
	 */
	class Mega_Menu_Location_Preview {

		/**
		 * Whether the preview iframe should load core admin notice styles and minimal dismiss JS.
		 *
		 * @var bool
		 */
		private static $preview_enqueue_wp_notice_assets = false;

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'admin_post_megamenu_preview', [ $this, 'handle_request' ] );
			add_action( 'wp_print_scripts', [ $this, 'strip_scripts_on_preview' ] );
			add_action( 'wp_print_styles', [ $this, 'strip_styles_on_preview' ] );
		}

		/**
		 * Nonce action for a location.
		 *
		 * @param string $location Location slug.
		 * @return string
		 */
		public static function nonce_action( $location ) {
			return 'megamenu_preview_' . $location;
		}

		/**
		 * Whether the location can show a preview (menu assigned to the theme location).
		 *
		 * @param string $location Location slug.
		 * @return bool
		 */
		public static function is_previewable( $location ) {
			return has_nav_menu( $location );
		}

		/**
		 * Raw preview URL for a location (nonce-protected admin-post URL).
		 *
		 * @param string $location Location slug.
		 * @return string
		 */
		public static function get_preview_url_raw( $location ) {
			return add_query_arg(
				apply_filters(
					'megamenu_preview_url_args',
					[
						'action'   => 'megamenu_preview',
						'location' => $location,
						'_wpnonce' => wp_create_nonce( self::nonce_action( $location ) ),
					],
					$location
				),
				admin_url( 'admin-post.php', 'relative' )
			);
		}

		/**
		 * Assigned navigation menu name for a theme location (empty if none).
		 *
		 * @param string $location Location slug.
		 * @return string
		 */
		public static function get_assigned_menu_name_for_location( $location ) {
			$locations = get_nav_menu_locations();

			if ( empty( $locations[ $location ] ) ) {
				return '';
			}

			$menu = wp_get_nav_menu_object( $locations[ $location ] );

			return ( $menu && isset( $menu->name ) ) ? (string) $menu->name : '';
		}

		/**
		 * Assigned navigation menu term ID for a theme location (0 if none).
		 *
		 * @param string $location Location slug.
		 * @return int
		 */
		public static function get_assigned_menu_id_for_location( $location ) {
			$locations = get_nav_menu_locations();

			if ( empty( $locations[ $location ] ) ) {
				return 0;
			}

			return (int) $locations[ $location ];
		}

		/**
		 * Responsive breakpoint for a location's menu theme, in pixels (0 = mobile menu off in theme).
		 *
		 * @param string $location Location slug.
		 * @return int Non-negative integer width in px.
		 */
		public static function get_responsive_breakpoint_px_for_location( $location ) {
			if ( ! is_string( $location ) || '' === $location ) {
				return 0;
			}

			$loc        = Mega_Menu_Location::find( $location );
			$theme_id   = $loc ? $loc->get_setting( 'theme', 'default' ) : 'default';
			$menu_theme = Mega_Menu_Theme::find( $theme_id );

			return absint( $menu_theme->get( 'responsive_breakpoint' ) );
		}

		/**
		 * Accessible title for the preview iframe (location + optional menu name).
		 *
		 * @param string $location       Location slug.
		 * @param string $location_label Human-readable name (optional).
		 * @return string
		 */
		public static function get_preview_title( $location, $location_label = '' ) {
			$label = '' !== $location_label ? wp_strip_all_tags( $location_label ) : $location;
			$menu  = self::get_assigned_menu_name_for_location( $location );

			if ( '' !== $menu ) {
				return sprintf(
					/* translators: 1: menu location name, 2: assigned menu name. */
					__( 'Location Preview: %1$s (%2$s)', 'megamenu' ),
					$label,
					$menu
				);
			}

			return sprintf(
				/* translators: %s: menu location name. */
				__( 'Location Preview: %s', 'megamenu' ),
				$label
			);
		}

		/**
		 * Markup for a button that opens preview inside the location settings modal.
		 *
		 * @param string $location       Location slug.
		 * @param string $location_label Human-readable menu location name.
		 * @param array  $args {
		 *     Optional.
		 *
		 *     @type bool $inactive When true, output a disabled control (e.g. no menu assigned).
		 *     @type bool $hidden   When true, output a non-visible button that preserves preview data attributes.
		 * }
		 * @return string HTML.
		 */
		public static function render_preview_link( $location, $location_label = '', $args = [] ) {
			$args = wp_parse_args(
				$args,
				[
					'inactive' => false,
					'hidden'   => false,
				]
			);

			$is_hidden        = ! empty( $args['hidden'] );
			$is_inactive      = ! empty( $args['inactive'] );
			$location_heading = '' !== $location_label ? wp_strip_all_tags( (string) $location_label ) : (string) $location;
			$preview_title    = self::get_preview_title( $location, $location_label );

			if ( $is_hidden ) {
				$button_inner = '';
				$classes      = 'megamenu-preview-open megamenu-preview-open--hidden';
			} else {
				$button_inner = '<span class="dashicons dashicons-visibility" aria-hidden="true"></span> ' . esc_html__( 'Preview', 'megamenu' );
				$classes      = 'button button-secondary button-compact megamenu-location-toolbar-btn megamenu-preview-open';
			}

			$processor = new WP_HTML_Tag_Processor( '<button type="button">' . $button_inner . '</button>' );

			if ( ! $processor->next_tag( 'button' ) ) {
				return '';
			}

			$processor->set_attribute( 'class', $classes );
			$processor->set_attribute( 'data-location', $location );

			if ( $is_inactive ) {
				$processor->set_attribute( 'disabled', true );
			}

			if ( ! $is_hidden ) {
				$processor->set_attribute( 'aria-label', __( 'Preview', 'megamenu' ) );
			}

			// Preview data attributes — set whenever a menu is actually assigned.
			if ( has_nav_menu( $location ) ) {
				$assigned_menu    = self::get_assigned_menu_name_for_location( $location );
				$assigned_menu_id = self::get_assigned_menu_id_for_location( $location );
				$breakpoint_px    = self::get_responsive_breakpoint_px_for_location( $location );

				$processor->set_attribute( 'data-preview-url', self::get_preview_url_raw( $location ) );
				$processor->set_attribute( 'data-preview-title', $preview_title );
				$processor->set_attribute( 'data-preview-location-label', $location_heading );
				$processor->set_attribute( 'data-responsive-breakpoint', (string) $breakpoint_px );
				if ( '' !== $assigned_menu ) {
					$processor->set_attribute( 'data-preview-assigned-menu', $assigned_menu );
				}
				if ( $assigned_menu_id > 0 ) {
					$processor->set_attribute( 'data-preview-menu-id', (string) $assigned_menu_id );
				}
			}

			if ( $is_hidden ) {
				$processor->set_attribute( 'hidden', true );
				$processor->set_attribute( 'aria-hidden', 'true' );
				$processor->set_attribute( 'tabindex', '-1' );
			}

			return $processor->get_updated_html();
		}

		/**
		 * Drop theme/plugin `wp_footer` callbacks so the iframe stays menu-only, then restore core
		 * footer script printing (priority 20 matches {@see wp-includes/default-filters.php}).
		 *
		 * @return void
		 */
		private function prepare_preview_minimal_footer() {
			remove_all_actions( 'wp_footer' );
			add_action( 'wp_footer', 'wp_print_footer_scripts', 20 );
		}

		/**
		 * Output the jQuery panel width preview limitation as a core-style admin notice (dismissible via minimal JS).
		 *
		 * @return void
		 */
		private function print_jquery_panel_width_preview_notice() {
			$message = '<strong>' . esc_html__( 'Sub menu width preview', 'megamenu' ) . '</strong> ' . esc_html__( 'This menu theme uses jQuery selectors for sub menu panel width (Outer and/or Inner) via data attributes on the menu. Those selectors match elements from your full theme layout, which is not loaded in this preview, so sub menu widths and horizontal alignment here will not match the live site.', 'megamenu' );

			echo '<div class="wrap wp-core-ui">';

			if ( function_exists( 'wp_admin_notice' ) ) {
				wp_admin_notice(
					$message,
					[
						'type'        => 'warning',
						'dismissible' => true,
						'id'          => 'megamenu-preview-jquery-notice',
					]
				);
			} else {
				printf(
					'<div id="megamenu-preview-jquery-notice" class="notice notice-warning is-dismissible"><p>%s</p></div>',
					wp_kses(
						$message,
						[
							'strong' => [],
						]
					)
				);
			}

			echo '</div>';
		}

		/**
		 * Serve the preview iframe document.
		 *
		 * @return void
		 */
		public function handle_request() {
			self::$preview_enqueue_wp_notice_assets = false;

			$location = isset( $_GET['location'] ) ? sanitize_text_field( wp_unslash( $_GET['location'] ) ) : '';

			if ( '' === $location ) {
				die();
			}

			check_admin_referer( self::nonce_action( $location ) );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				die();
			}

			if ( ! has_nav_menu( $location ) ) {
				die();
			}

			if ( ! defined( 'MEGAMENU_PREVIEW' ) ) {
				define( 'MEGAMENU_PREVIEW', true );
			}

			do_action( 'megamenu_preview_before_render', $location );

			remove_action( 'wp_head', '_admin_bar_bump_cb' );

			$loc        = Mega_Menu_Location::find( $location );
			$theme_id   = $loc ? $loc->get_setting( 'theme', 'default' ) : 'default';
			$menu_theme = Mega_Menu_Theme::find( $theme_id );
			$show_jquery_notice = $this->menu_uses_jquery_panel_selectors( $menu_theme );

			self::$preview_enqueue_wp_notice_assets = $show_jquery_notice;

			?>
			<!DOCTYPE html>
			<html class="megamenu-preview-root">
				<head>
					<title><?php esc_html_e( 'Preview', 'megamenu' ); ?></title>
					<?php wp_head(); ?>
					<style class="megamenu-preview">
						/*
						 * After wp_head(): when core common.css is loaded for admin notices it sets html/body
						 * (height, background, min-width, etc.). These rules restore the preview frame layout.
						 */
						html.megamenu-preview-root,
						html.megamenu-preview-root body {
							margin: 0;
							height: auto;
							min-height: 200vh;
						}
						html.megamenu-preview-root {
							padding: 0;
							background: transparent;
						}
						html.megamenu-preview-root body {
							padding: 20px;
							box-sizing: border-box;
							min-width: 0;
							overflow-x: visible;
							font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
							background: transparent;
						}
						html.megamenu-preview-root #query-monitor-main {
							display: none;
						}
						html.megamenu-preview-root .menu_wrapper {
							max-width: 1280px;
							margin: 0 auto;
							margin-top: 20px;
						}
					</style>
				</head>
				<body>
					<?php if ( $show_jquery_notice ) : ?>
						<?php $this->print_jquery_panel_width_preview_notice(); ?>
					<?php endif; ?>
					<div class='menu_wrapper'>
						<?php wp_nav_menu( [ 'theme_location' => $location ] ); ?>
					</div>
					<?php
					$this->prepare_preview_minimal_footer();
					wp_footer();
					?>
				</body>
			</html>
			<?php

			die();
		}

		/**
		 * Remove unnecessary scripts from the preview page.
		 *
		 * @return void
		 */
		public function strip_scripts_on_preview() {
			if ( isset( $_GET['action'] ) && 'megamenu_preview' === $_GET['action'] ) {
				global $wp_scripts;

				$wp_scripts->queue = [];

				do_action( 'megamenu_enqueue_scripts' );

				if ( self::$preview_enqueue_wp_notice_assets ) {
					wp_register_script(
						'megamenu-preview-dismissible-notices',
						false,
						[ 'jquery' ],
						MEGAMENU_VERSION,
						true
					);
					wp_enqueue_script( 'megamenu-preview-dismissible-notices' );
					$dismiss_label = wp_json_encode( __( 'Dismiss this notice.', 'default' ) );
					wp_add_inline_script(
						'megamenu-preview-dismissible-notices',
						'(function($){$(\'.notice.is-dismissible\').each(function(){var el=$(this);if(el.find(\'.notice-dismiss\').length){return;}var btn=$(\'<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>\');btn.find(\'.screen-reader-text\').text(' . $dismiss_label . ');btn.on(\'click.wp-dismiss-notice\',function(e){e.preventDefault();el.fadeTo(100,0,function(){el.slideUp(100,function(){el.remove();});});});el.append(btn);});})(jQuery);'
					);
				}
			}
		}

		/**
		 * Remove unnecessary styles from the preview page.
		 *
		 * @return void
		 */
		public function strip_styles_on_preview() {
			if ( isset( $_GET['action'] ) && 'megamenu_preview' === $_GET['action'] ) {
				global $wp_styles;

				$wp_styles->queue = [];

				do_action( 'megamenu_enqueue_styles' );

				if ( self::$preview_enqueue_wp_notice_assets ) {
					wp_enqueue_style( 'dashicons' );
					wp_enqueue_style( 'common' );
				}

				if ( function_exists( 'wp_enqueue_global_styles' ) ) {
					wp_enqueue_global_styles();
				}
			}
		}

		/**
		 * Whether the theme uses jQuery-based panel width selectors (non-CSS lengths).
		 *
		 * @param Mega_Menu_Theme $theme Theme instance.
		 * @return bool
		 */
		private function menu_uses_jquery_panel_selectors( $theme ) {
			$panel_width       = trim( (string) $theme->get( 'panel_width' ) );
			$panel_inner_width = trim( (string) $theme->get( 'panel_inner_width' ) );

			$css_length    = '/^((\d+(\.\d+)?(px|%|em|rem|vw|vh|ch|ex|cm|mm|in|pt|pc))|auto)$/i';
			$viewport_only = '/^\d+(vw|vh|vmin|vmax)$/i';

			$panel_width_in_data = $panel_width !== ''
				&& ( preg_match( '/^\d/', $panel_width ) !== 1 || preg_match( $viewport_only, $panel_width ) === 1 );

			if ( $panel_width_in_data && ! preg_match( $viewport_only, $panel_width ) && ! preg_match( $css_length, $panel_width ) ) {
				return true;
			}

			$inner_in_data = $panel_inner_width !== '' && substr( $panel_inner_width, -1 ) !== '%';

			if ( $inner_in_data && ! preg_match( $css_length, $panel_inner_width ) ) {
				return true;
			}

			return false;
		}
	}

endif;
