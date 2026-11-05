<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Admin_Icons' ) ) :

	/**
	 * Icons for the plugin's admin screens on WordPress 7.2 and later.
	 *
	 * WordPress 7.2 replaced its admin icons with lighter, outlined ones and added an icon library ( wp_get_icon() ).
	 * There, the plugin's admin screens use icons from that library in place of dashicons, so they match: each icon is
	 * printed as a CSS variable holding its SVG ( --megamenu-icon-{key} ), drawn as a mask in the text colour by
	 * admin.scss, and the admin body gets the class `megamenu-wp-icons`. The few icons the library doesn't have are
	 * registered in the plugin's own `megamenu` collection, drawn in the same style. Before 7.2 nothing is output and
	 * the dashicons stay.
	 *
	 * @since 3.10.10
	 */
	class Mega_Menu_Admin_Icons {

		/**
		 * Constructor
		 *
		 * @since 3.10.10
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'register_icons' ] );
			add_filter( 'admin_body_class', [ $this, 'admin_body_class' ], 20 );
			add_action( 'admin_head', [ $this, 'print_icon_variables' ] );
		}


		/**
		 * Whether WordPress has the icon library and can use icon names for admin menu items (7.2 and later).
		 *
		 * @since 3.10.10
		 * @return bool
		 */
		public static function is_supported() {
			return function_exists( 'wp_is_valid_icon_name' ) && function_exists( 'wp_get_icon' ) && function_exists( 'wp_register_icon' );
		}


		/**
		 * The icons used in the admin screens: CSS variable key => icon name. admin.scss refers to them by key.
		 *
		 * @since 3.10.10
		 * @return array
		 */
		public static function get_icons() {
			return apply_filters(
				'megamenu_admin_icons',
				[
					'arrow-right'     => 'core/arrow-right',
					'block-table'     => 'core/block-table',
					'brush'           => 'core-admin/brush',
					'caution'         => 'core/caution',
					'check'           => 'core/check',
					'chevron-down'    => 'core/chevron-down',
					'chevron-left'    => 'core/chevron-left',
					'chevron-right'   => 'core/chevron-right',
					'chevron-up'      => 'core/chevron-up',
					'close'           => 'megamenu/close',
					'desktop'         => 'core/desktop',
					'download'        => 'core/download',
					'duplicate'       => 'megamenu/duplicate',
					'external'        => 'core/external',
					'fullscreen'      => 'megamenu/fullscreen',
					'fullscreen-exit' => 'megamenu/fullscreen-exit',
					'info'            => 'core/info',
					'location'        => 'core-admin/map-marker',
					'menu'            => 'core/menu',
					'mobile'          => 'core/mobile',
					'pencil'          => 'core/pencil',
					'people'          => 'core-admin/people',
					'pin'             => 'core-admin/pin',
					'plus'            => 'core/plus',
					'seen'            => 'megamenu/seen',
					'settings'        => 'core-admin/settings',
					'star'            => 'core/star-empty',
					'styles'          => 'core/styles',
					'tag'             => 'core/tag',
					'tool'            => 'core-admin/tool',
					'trash'           => 'megamenu/trash',
					'update'          => 'core-admin/update',
					'upload'          => 'core/upload',
				]
			);
		}


		/**
		 * Register the plugin's icon collection: the admin menu icon (an outlined version of the plugin icon) and the
		 * icons the WordPress library doesn't have, drawn in the same style (24px grid, 1.5px outline).
		 *
		 * @since 3.10.10
		 * @return void
		 */
		public function register_icons() {
			if ( ! self::is_supported() || ! function_exists( 'wp_register_icon_collection' ) ) {
				return;
			}

			wp_register_icon_collection(
				'megamenu',
				[
					'label'  => __( 'Max Mega Menu', 'megamenu' ),
					'public' => false,
				]
			);

			$open = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="fill: none" stroke="currentColor" stroke-width="1.5">';

			$icons = [
				'logo'            => [
					__( 'Max Mega Menu', 'megamenu' ),
					'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="fill: none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round"><path d="M11.38 4.35A1.25 1.25 0 0 1 12.62 4.35L18.37 7.64A1.25 1.25 0 0 1 19 8.73V15.27A1.25 1.25 0 0 1 18.37 16.36L12.62 19.65A1.25 1.25 0 0 1 11.38 19.65L5.63 16.36A1.25 1.25 0 0 1 5 15.27V8.73A1.25 1.25 0 0 1 5.63 7.64Z"/><path d="M5 8L12 12L19 8"/><path d="M8 17.71V13.71L12 16L16 13.71V17.71"/></svg>',
				],
				'close'           => [ __( 'Close', 'megamenu' ), $open . '<path d="M6.5 6.5L17.5 17.5M17.5 6.5L6.5 17.5"/></svg>' ],
				'trash'           => [ __( 'Delete', 'megamenu' ), $open . '<path d="M5 7.25H19M9.75 7.25V5.75C9.75 5.19772 10.1977 4.75 10.75 4.75H13.25C13.8023 4.75 14.25 5.19772 14.25 5.75V7.25M6.75 7.25L7.6 18.35C7.64 18.87 8.07 19.25 8.59 19.25H15.41C15.93 19.25 16.36 18.87 16.4 18.35L17.25 7.25M10.25 10.75V15.75M13.75 10.75V15.75"/></svg>' ],
				'fullscreen'      => [ __( 'Expand', 'megamenu' ), $open . '<path d="M5 9.5V5H9.5M14.5 5H19V9.5M19 14.5V19H14.5M9.5 19H5V14.5"/></svg>' ],
				'fullscreen-exit' => [ __( 'Collapse', 'megamenu' ), $open . '<path d="M9.5 5V9.5H5M19 9.5H14.5V5M14.5 19V14.5H19M5 14.5H9.5V19"/></svg>' ],
				'seen'            => [ __( 'Preview', 'megamenu' ), $open . '<path d="M4.75 12C6.25 8.75 8.85 7 12 7C15.15 7 17.75 8.75 19.25 12C17.75 15.25 15.15 17 12 17C8.85 17 6.25 15.25 4.75 12Z"/><path d="M14.25 12C14.25 13.2426 13.2426 14.25 12 14.25C10.7574 14.25 9.75 13.2426 9.75 12C9.75 10.7574 10.7574 9.75 12 9.75C13.2426 9.75 14.25 10.7574 14.25 12Z"/></svg>' ],
				'duplicate'       => [ __( 'Duplicate', 'megamenu' ), $open . '<path d="M8.75 8.75V5.75C8.75 5.19772 9.19772 4.75 9.75 4.75H18.25C18.8023 4.75 19.25 5.19772 19.25 5.75V14.25C19.25 14.8023 18.8023 15.25 18.25 15.25H15.25M5.75 8.75H14.25C14.8023 8.75 15.25 9.19772 15.25 9.75V18.25C15.25 18.8023 14.8023 19.25 14.25 19.25H5.75C5.19772 19.25 4.75 18.8023 4.75 18.25V9.75C4.75 9.19772 5.19772 8.75 5.75 8.75Z"/></svg>' ],
			];

			foreach ( $icons as $name => $icon ) {
				wp_register_icon(
					'megamenu/' . $name,
					[
						'label'   => $icon[0],
						'content' => $icon[1],
					]
				);
			}
		}


		/**
		 * Whether the current admin screen is one of the plugin's (Appearance > Menus or a Max Mega Menu page).
		 *
		 * @since 3.10.10
		 * @return bool
		 */
		private function is_plugin_screen() {
			if ( isset( $GLOBALS['pagenow'] ) && 'nav-menus.php' === $GLOBALS['pagenow'] ) {
				return true;
			}

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			return $screen && is_string( $screen->id ) && false !== strpos( $screen->id, 'maxmegamenu' );
		}


		/**
		 * Add `megamenu-wp-icons` to the admin body on the plugin's screens, which switches admin.scss to the icons.
		 *
		 * @since 3.10.10
		 * @param string $classes Space-separated body classes.
		 * @return string
		 */
		public function admin_body_class( $classes ) {
			if ( self::is_supported() && $this->is_plugin_screen() ) {
				$classes .= ' megamenu-wp-icons';
			}

			return $classes;
		}


		/**
		 * Print each icon as a CSS variable holding its SVG, on the plugin's screens.
		 *
		 * @since 3.10.10
		 * @return void
		 */
		public function print_icon_variables() {
			if ( ! self::is_supported() || ! $this->is_plugin_screen() ) {
				return;
			}

			$variables = '';

			foreach ( self::get_icons() as $key => $name ) {
				$svg = wp_get_icon( $name, [ 'size' => null ] );

				// wp_get_icon() lowercases attribute names, which is fine inline in HTML, but a data URL is parsed as
				// XML, where only `viewBox` scales the icon to its box.
				$svg = str_replace( ' viewbox=', ' viewBox=', $svg );

				if ( '' !== $svg ) {
					$variables .= '--megamenu-icon-' . sanitize_key( $key ) . ':url("data:image/svg+xml,' . rawurlencode( $svg ) . '");';
				}
			}

			if ( $variables ) {
				echo '<style id="megamenu-admin-icons">:root{' . $variables . '}</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- keys are sanitized, SVGs are URL-encoded.
			}
		}
	}

endif;
