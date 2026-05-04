<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mega_Menu_Preview' ) ) :

	/**
	 * Menu preview (admin-post iframe) and modal dialog markup / preview controls.
	 *
	 * Shared by the Menu Locations settings screen and Appearance > Menus meta box.
	 *
	 * @package MegaMenu
	 */
	class Mega_Menu_Preview {

		/**
		 * @var bool
		 */
		private static $dialog_printed = false;

		/**
		 * Whether the preview iframe should load core admin notice styles and minimal dismiss JS.
		 *
		 * Loads dashicons + common.css for notice chrome. Does not load {@see wp_enqueue_script( 'common' )}:
		 * admin `common.js` also pins the left admin menu and adds a `sticky-menu` body class when no menu
		 * exists in the iframe, which breaks the preview layout.
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
			add_action( 'admin_footer', [ $this, 'maybe_print_dialog' ], 5 );
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
		 * Root-relative (see {@see admin_url()} with scheme `relative`) so the iframe always
		 * loads on the same host and port as the active admin screen. Absolute URLs from
		 * `siteurl` often omit a dev proxy port (e.g. Local), which breaks cookies and nonces.
		 *
		 * Use this when passing the URL into {@see WP_HTML_Tag_Processor::set_attribute()};
		 * that API encodes attribute values once. Feeding it an already-escaped URL from
		 * {@see esc_url()} double-encodes `&` and breaks `data-*` / iframe `src`.
		 *
		 * @param string $location Location slug.
		 * @return string URL with unencoded query separators (safe to pass to set_attribute).
		 */
		private static function get_preview_url_raw( $location ) {
			return add_query_arg(
				[
					'action'   => 'megamenu_preview',
					'location' => $location,
					'_wpnonce' => wp_create_nonce( self::nonce_action( $location ) ),
				],
				admin_url( 'admin-post.php', 'relative' )
			);
		}

		/**
		 * Preview URL for a location (nonce-protected admin-post URL), HTML-escaped.
		 *
		 * @param string $location Location slug.
		 * @return string Escaped URL.
		 */
		public static function get_preview_url( $location ) {
			return esc_url( self::get_preview_url_raw( $location ) );
		}

		/**
		 * Assigned navigation menu name for a theme location (empty if none).
		 *
		 * @param string $location Location slug.
		 * @return string
		 */
		public static function get_assigned_menu_name_for_location( $location ) {
			if ( ! has_nav_menu( $location ) ) {
				return '';
			}

			$locations = get_nav_menu_locations();

			if ( empty( $locations[ $location ] ) ) {
				return '';
			}

			$menu = wp_get_nav_menu_object( $locations[ $location ] );

			return ( $menu && isset( $menu->name ) ) ? (string) $menu->name : '';
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

			$plugin_settings = get_option( 'megamenu_settings', [] );

			if ( ! is_array( $plugin_settings ) ) {
				$plugin_settings = [];
			}

			$location_settings = isset( $plugin_settings[ $location ] ) && is_array( $plugin_settings[ $location ] )
				? $plugin_settings[ $location ]
				: [];

			$theme_id   = isset( $location_settings['theme'] ) ? $location_settings['theme'] : 'default';
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
		 * Markup for a button that opens the preview in the shared modal.
		 *
		 * @param string $location       Location slug.
		 * @param string $location_label Human-readable menu location name (dialog heading + iframe title context).
		 * @param array  $args {
		 *     Optional.
		 *
		 *     @type bool   $inactive When true, output a disabled control (e.g. no menu assigned).
		 *     @type string $tooltip  Optional. Overrides the default "Preview" string for `aria-label`.
		 *     @type bool   $icon_only When true, output a compact icon button (e.g. theme editor toolbar) using `mega-theme-editor-action` styling; when false, output the location card footer icon button.
		 *     @type bool   $save_theme_before_preview When true (active controls only), click saves the theme editor form via AJAX then opens preview (theme editor).
		 *     @type bool   $mega_tooltip When true, output `data-mega-tooltip` (location cards pass false; theme editor toolbar uses default true).
		 * }
		 * @return string HTML.
		 */
		public static function render_preview_link( $location, $location_label = '', $args = [] ) {
			$args = wp_parse_args(
				$args,
				[
					'inactive'                   => false,
					'tooltip'                    => '',
					'icon_only'                  => false,
					'save_theme_before_preview'  => false,
					'mega_tooltip'               => true,
				]
			);

			$default_tip    = __( 'Preview', 'megamenu' );
			$tooltip_attr   = '' !== $args['tooltip'] ? $args['tooltip'] : $default_tip;
			$preview_title  = self::get_preview_title( $location, $location_label );
			$location_heading = '' !== $location_label ? wp_strip_all_tags( (string) $location_label ) : (string) $location;

			if ( ! empty( $args['icon_only'] ) ) {
				$button_inner = '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>';
				$classes      = 'mega-theme-editor-action megamenu-preview-open mega-theme-editor-action--preview';
			} else {
				$button_inner = '<span class="dashicons dashicons-visibility" aria-hidden="true"></span> ' . esc_html__( 'Preview', 'megamenu' );
				$classes      = 'button button-secondary button-compact megamenu-preview-open';
			}

			$processor = new WP_HTML_Tag_Processor( '<button type="button">' . $button_inner . '</button>' );

			if ( ! $processor->next_tag( 'button' ) ) {
				return '';
			}

			$assigned_menu = self::get_assigned_menu_name_for_location( $location );
			$breakpoint_px = self::get_responsive_breakpoint_px_for_location( $location );

			if ( $args['inactive'] ) {
				$processor->set_attribute( 'type', 'button' );
				$processor->set_attribute( 'class', $classes );
				$processor->set_attribute( 'disabled', true );
				$processor->set_attribute( 'aria-label', wp_strip_all_tags( $tooltip_attr ) );
				if ( ! empty( $args['mega_tooltip'] ) ) {
					$processor->set_attribute( 'data-mega-tooltip', $tooltip_attr );
				}
				if ( has_nav_menu( $location ) ) {
					$processor->set_attribute( 'data-preview-url', self::get_preview_url_raw( $location ) );
					$processor->set_attribute( 'data-preview-title', $preview_title );
					$processor->set_attribute( 'data-preview-location-label', $location_heading );
					$processor->set_attribute( 'data-responsive-breakpoint', (string) $breakpoint_px );
					if ( '' !== $assigned_menu ) {
						$processor->set_attribute( 'data-preview-assigned-menu', $assigned_menu );
					}
				}
				return $processor->get_updated_html();
			}

			$url = self::get_preview_url_raw( $location );

			$processor->set_attribute( 'data-preview-url', $url );
			$processor->set_attribute( 'class', $classes );
			$processor->set_attribute( 'data-preview-title', $preview_title );
			$processor->set_attribute( 'data-preview-location-label', $location_heading );
			$processor->set_attribute( 'data-responsive-breakpoint', (string) $breakpoint_px );
			if ( '' !== $assigned_menu ) {
				$processor->set_attribute( 'data-preview-assigned-menu', $assigned_menu );
			}
			$processor->set_attribute( 'aria-label', wp_strip_all_tags( $tooltip_attr ) );
			if ( ! empty( $args['mega_tooltip'] ) ) {
				$processor->set_attribute( 'data-mega-tooltip', $tooltip_attr );
			}

			if ( ! empty( $args['save_theme_before_preview'] ) ) {
				$processor->set_attribute( 'data-megamenu-save-theme-then-preview', '1' );
			}

			return $processor->get_updated_html();
		}

		/**
		 * Output the modal dialog once on screens that may contain preview links.
		 *
		 * @return void
		 */
		public function maybe_print_dialog() {
			if ( self::$dialog_printed ) {
				return;
			}

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$screen = get_current_screen();

			if ( ! $screen ) {
				return;
			}

			$allowed = ( 'nav-menus' === $screen->base )
				|| ( 'toplevel_page_maxmegamenu' === $screen->id )
				|| ( false !== strpos( $screen->id, 'maxmegamenu' ) );

			if ( ! $allowed ) {
				return;
			}

			self::$dialog_printed = true;
			self::render_dialog_markup();
		}

		/**
		 * Echo the preview modal as a text/html script template (mounted to body by js/admin/dialog-preview.js).
		 *
		 * @return void
		 */
		public static function render_dialog_markup() {
			?>
			<script type="text/html" id="megamenu-preview-dialog-template">
			<div id="megamenu-preview-dialog" class="megamenu-admin-modal megamenu-preview-dialog" hidden data-megamenu-expand-storage-key="megamenu_admin_modal_wpcontent_expanded" data-i18n-assigned-menu-prefix="<?php echo esc_attr__( 'Assigned menu:', 'megamenu' ); ?>" data-i18n-mobile-preview-disabled="<?php echo esc_attr__( 'Mobile width preview is unavailable because the responsive breakpoint is set to 0px in the menu theme (mobile menu is off).', 'megamenu' ); ?>" data-i18n-location-preview-title-tpl="<?php echo esc_attr__( 'Location Preview: %s', 'megamenu' ); ?>" data-i18n-modal-expand="<?php echo esc_attr__( 'Expand preview to fill workspace', 'megamenu' ); ?>" data-i18n-modal-collapse="<?php echo esc_attr__( 'Restore default preview size', 'megamenu' ); ?>">
				<button type="button" class="megamenu-admin-modal__backdrop" aria-label="<?php esc_attr_e( 'Close preview', 'megamenu' ); ?>"></button>
				<div class="megamenu-admin-modal__panel" role="dialog" aria-modal="true" aria-labelledby="megamenu-preview-dialog-title" tabindex="-1">
					<div class="megamenu-admin-modal__header">
						<div class="megamenu-admin-modal__title-group">
							<h2 id="megamenu-preview-dialog-title" class="megamenu-admin-modal__title">
								<span class="megamenu-admin-modal__title-text"></span>
							</h2>
							<p id="megamenu-preview-dialog-subtitle" class="megamenu_subtitle" hidden></p>
						</div>
						<div class="megamenu-admin-modal__header-actions">
							<button type="button" class="megamenu-admin-modal__expand-btn" aria-expanded="false" aria-label="<?php esc_attr_e( 'Expand preview to fill workspace', 'megamenu' ); ?>">
								<span class="dashicons dashicons-fullscreen-alt megamenu-admin-modal__expand-icon megamenu-admin-modal__expand-icon--expand" aria-hidden="true"></span>
								<span class="dashicons dashicons-fullscreen-exit-alt megamenu-admin-modal__expand-icon megamenu-admin-modal__expand-icon--contract" aria-hidden="true"></span>
							</button>
							<button type="button" class="megamenu-preview-dialog__refresh-btn" aria-label="<?php esc_attr_e( 'Reload preview', 'megamenu' ); ?>">
								<span class="dashicons dashicons-update" aria-hidden="true"></span>
							</button>
							<button type="button" class="megamenu-modal-close" aria-label="<?php echo esc_attr__( 'Close', 'megamenu' ); ?>">
								<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
							</button>
						</div>
					</div>
					<div class="megamenu-admin-modal__body megamenu-preview-dialog__body megamenu_outer_wrap">
						<div class="megamenu-preview-dialog__iframe-shell megamenu-admin-modal__loading-host">
							<div class="megamenu-admin-modal__loading-overlay" role="status" aria-live="polite">
								<span class="megamenu-admin-modal__loading-spinner" aria-hidden="true"></span>
								<span class="screen-reader-text"><?php esc_html_e( 'Loading preview.', 'megamenu' ); ?></span>
							</div>
							<iframe class="megamenu-preview-dialog__iframe" title="<?php esc_attr_e( 'Location preview', 'megamenu' ); ?>" src="about:blank"></iframe>
						</div>
					</div>
					<div class="megamenu-admin-modal__footer megamenu-preview-dialog__footer">
						<div class="megamenu-preview-dialog__viewport-toggle" role="toolbar" aria-label="<?php esc_attr_e( 'Preview width', 'megamenu' ); ?>">
							<button type="button" class="megamenu-preview-dialog__viewport-btn megamenu-preview-dialog__viewport-btn--desktop megamenu-preview-dialog__viewport-btn--active" aria-pressed="true">
								<span class="dashicons dashicons-desktop" aria-hidden="true"></span>
								<span class="screen-reader-text"><?php esc_html_e( 'Desktop width', 'megamenu' ); ?></span>
							</button>
							<button type="button" class="megamenu-preview-dialog__viewport-btn megamenu-preview-dialog__viewport-btn--mobile" aria-pressed="false">
								<span class="dashicons dashicons-smartphone" aria-hidden="true"></span>
								<span class="screen-reader-text"><?php esc_html_e( 'Mobile breakpoint width', 'megamenu' ); ?></span>
							</button>
						</div>
					</div>
				</div>
			</div>
			</script>
			<?php
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

			remove_action( 'wp_head', '_admin_bar_bump_cb' );

			$plugin_settings   = get_option( 'megamenu_settings' );
			$location_settings = isset( $plugin_settings[ $location ] ) ? $plugin_settings[ $location ] : [];
			$theme_id          = isset( $location_settings['theme'] ) ? $location_settings['theme'] : 'default';
			$menu_theme        = Mega_Menu_Theme::find( $theme_id );
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

				// Rebuild queue with Mega Menu assets only (see Mega_Menu_Style_Manager::enqueue_scripts).
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

				// Rebuild queue with Mega Menu styles only (see Mega_Menu_Style_Manager::enqueue_styles).
				do_action( 'megamenu_enqueue_styles' );

				if ( self::$preview_enqueue_wp_notice_assets ) {
					wp_enqueue_style( 'dashicons' );
					wp_enqueue_style( 'common' );
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

			$css_length = '/^((\d+(\.\d+)?(px|%|em|rem|vw|vh|ch|ex|cm|mm|in|pt|pc))|auto)$/i';
			$viewport_only = '/^\d+(vw|vh|vmin|vmax)$/i';

			$panel_width_in_data = $panel_width !== ''
				&& ( preg_match( '/^\d/', $panel_width ) !== 1 || preg_match( '/^\d+(vw|vh|vmin|vmax)$/', $panel_width ) === 1 );

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

if ( class_exists( 'Mega_Menu_Preview', false ) && ! class_exists( 'Mega_Menu_Sandbox', false ) ) {
	class_alias( 'Mega_Menu_Preview', 'Mega_Menu_Sandbox' );
}
