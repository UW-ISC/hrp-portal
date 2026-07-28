<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mega_Menu_SVG_Icons' ) ) :

	/**
	 * SVG arrow and close icon support for the theme editor.
	 *
	 * @package MegaMenu
	 */
	class Mega_Menu_SVG_Icons {

		/**
		 * Constructor — registers filter hooks.
		 */
		public function __construct() {
			add_filter( 'megamenu_theme_arrow_icons', [ $this, 'add_arrow_icons' ], 5 );
			add_filter( 'megamenu_theme_close_icons', [ $this, 'add_close_icons' ] );
			add_filter( 'megamenu_theme_toggle_icons', [ $this, 'add_toggle_icons' ] );
			add_filter( 'megamenu_close_button', [ $this, 'inject_svg_close_icon' ], 10, 4 );
			add_filter( 'megamenu_toggle_menu_toggle_html', [ $this, 'inject_svg_toggle_icons' ], 10, 2 );
		}

		/**
		 * All predefined SVG icons.
		 *
		 * @return array
		 */
		public static function get_svg_arrows() {
			$s = 'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';

			$svgs = [
				// Chevrons — stroke-based; path proportions match Material Symbols optical margins in 0 0 24 24 space
				'svg-chevron-up'             => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><polyline points="18 15 12 9 6 15"></polyline></svg>',
				'svg-chevron-down'           => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><polyline points="6 9 12 15 18 9"></polyline></svg>',
				'svg-chevron-left'           => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><polyline points="15 18 9 12 15 6"></polyline></svg>',
				'svg-chevron-right'          => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><polyline points="9 18 15 12 9 6"></polyline></svg>',
				// Carets — filled triangles matching chevron bounds, all centered at (12,12)
				'svg-caret-up'               => '<svg class="mega-svg-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 9l-6 6h12z"/></svg>',
				'svg-caret-down'             => '<svg class="mega-svg-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M6 9h12l-6 6z"/></svg>',
				'svg-caret-left'             => '<svg class="mega-svg-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M15 6l-6 6 6 6z"/></svg>',
				'svg-caret-right'            => '<svg class="mega-svg-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M9 6l6 6-6 6z"/></svg>',
				// Arrows
				'svg-arrow-up'               => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>',
				'svg-arrow-down'             => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>',
				'svg-arrow-left'             => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
				'svg-arrow-right'            => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>',
				// Close
				'svg-close'                  => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
				'svg-close-circle'           => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
				// Hamburgers
				'svg-hamburger'              => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>',
				'svg-hamburger-taper'        => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="16" y2="12"></line><line x1="3" y1="18" x2="10" y2="18"></line></svg>',
				'svg-hamburger-taper-reverse'=> '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><line x1="3" y1="6" x2="10" y2="6"></line><line x1="3" y1="12" x2="16" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>',
				'svg-hamburger-center'       => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><line x1="3" y1="6" x2="21" y2="6"></line><line x1="6" y1="12" x2="18" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>',
				// 2-line menu icons
				'svg-menu-2'                 => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><line x1="3" y1="8" x2="21" y2="8"></line><line x1="3" y1="16" x2="21" y2="16"></line></svg>',
				'svg-menu-2-taper'           => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><line x1="3" y1="8" x2="21" y2="8"></line><line x1="3" y1="16" x2="14" y2="16"></line></svg>',
				'svg-menu-2-taper-reverse'   => '<svg class="mega-svg-icon" viewBox="0 0 24 24" ' . $s . '><line x1="3" y1="8" x2="14" y2="8"></line><line x1="3" y1="16" x2="21" y2="16"></line></svg>',
			];

			return apply_filters( 'megamenu_svg_arrows', $svgs );
		}

		/**
		 * Convert an SVG string to a URL-encoded value for use in a CSS data URI.
		 * Returns the encoded SVG content only — caller wraps in data:image/svg+xml,...
		 *
		 * @param string $svg Raw SVG markup.
		 * @return string
		 */
		public static function svg_to_data_uri( string $svg ): string {
			$svg = preg_replace( '/\s*class="[^"]*"/', '', $svg );
			if ( strpos( $svg, 'xmlns' ) === false ) {
				$svg = str_replace( '<svg', '<svg xmlns="http://www.w3.org/2000/svg"', $svg );
			}
			$svg = str_replace( [ '"', '#', '<', '>' ], [ "'", '%23', '%3C', '%3E' ], $svg );
			return $svg;
		}

		/**
		 * Add SVG arrow presets to the theme editor arrow selector.
		 *
		 * @param array $styles Existing registered styles.
		 * @return array
		 */
		public function add_arrow_icons( $icons ) {
			$svgs = self::get_svg_arrows();

			$icons['svg'] = [
				'label' => __( 'SVG', 'megamenu' ),
				'icons' => array_merge( $icons['svg']['icons'] ?? [], [
					'svg-chevron' => [
						'label' => __( 'SVG Chevron', 'megamenu' ),
						'icons' => [
							'up'    => [ 'value' => 'svg-chevron-up',    'svg' => $svgs['svg-chevron-up']    ],
							'down'  => [ 'value' => 'svg-chevron-down',  'svg' => $svgs['svg-chevron-down']  ],
							'left'  => [ 'value' => 'svg-chevron-left',  'svg' => $svgs['svg-chevron-left']  ],
							'right' => [ 'value' => 'svg-chevron-right', 'svg' => $svgs['svg-chevron-right'] ],
						],
					],
					'svg-caret' => [
						'label' => __( 'SVG Caret', 'megamenu' ),
						'icons' => [
							'up'    => [ 'value' => 'svg-caret-up',    'svg' => $svgs['svg-caret-up']    ],
							'down'  => [ 'value' => 'svg-caret-down',  'svg' => $svgs['svg-caret-down']  ],
							'left'  => [ 'value' => 'svg-caret-left',  'svg' => $svgs['svg-caret-left']  ],
							'right' => [ 'value' => 'svg-caret-right', 'svg' => $svgs['svg-caret-right'] ],
						],
					],
					'svg-arrow' => [
						'label' => __( 'SVG Arrow', 'megamenu' ),
						'icons' => [
							'up'    => [ 'value' => 'svg-arrow-up',    'svg' => $svgs['svg-arrow-up']    ],
							'down'  => [ 'value' => 'svg-arrow-down',  'svg' => $svgs['svg-arrow-down']  ],
							'left'  => [ 'value' => 'svg-arrow-left',  'svg' => $svgs['svg-arrow-left']  ],
							'right' => [ 'value' => 'svg-arrow-right', 'svg' => $svgs['svg-arrow-right'] ],
						],
					],
				] ),
			];

			return $icons;
		}

		/**
		 * Add SVG options to the toggle icon selector.
		 *
		 * @param array $styles Existing registered styles.
		 * @return array
		 */
		public function add_toggle_icons( $styles ) {
			$svgs = self::get_svg_arrows();

			$styles['svg'] = [
				'label' => __( 'SVG', 'megamenu' ),
				'icons' => array_merge( $styles['svg']['icons'] ?? [], [
					'svg-hamburger'               => [ 'label' => __( 'Hamburger',               'megamenu' ), 'svg' => $svgs['svg-hamburger']               ],
					'svg-hamburger-taper'         => [ 'label' => __( 'Hamburger Taper',         'megamenu' ), 'svg' => $svgs['svg-hamburger-taper']         ],
					'svg-hamburger-taper-reverse' => [ 'label' => __( 'Hamburger Taper Reverse', 'megamenu' ), 'svg' => $svgs['svg-hamburger-taper-reverse'] ],
					'svg-hamburger-center'        => [ 'label' => __( 'Hamburger Center',        'megamenu' ), 'svg' => $svgs['svg-hamburger-center']        ],
					'svg-menu-2'                  => [ 'label' => __( 'Menu 2 Line',             'megamenu' ), 'svg' => $svgs['svg-menu-2']                  ],
					'svg-menu-2-taper'            => [ 'label' => __( 'Menu 2 Line Taper',       'megamenu' ), 'svg' => $svgs['svg-menu-2-taper']            ],
					'svg-menu-2-taper-reverse'    => [ 'label' => __( 'Menu 2 Line Taper Reverse', 'megamenu' ), 'svg' => $svgs['svg-menu-2-taper-reverse'] ],
					'svg-close'                   => [ 'label' => __( 'X',                       'megamenu' ), 'svg' => $svgs['svg-close']                   ],
					'svg-close-circle'            => [ 'label' => __( 'X Circle',                'megamenu' ), 'svg' => $svgs['svg-close-circle']            ],
				] ),
			];

			return $styles;
		}

		/**
		 * Add SVG icons to the theme editor close icon selector.
		 *
		 * @param array $styles Existing registered styles.
		 * @return array
		 */
		public function add_close_icons( $icons ) {
			$svgs = self::get_svg_arrows();

			$icons['svg'] = [
				'label' => __( 'SVG', 'megamenu' ),
				'icons' => array_merge( $icons['svg']['icons'] ?? [], [
					'svg-close'        => [ 'label' => __( 'X',        'megamenu' ), 'svg' => $svgs['svg-close']        ],
					'svg-close-circle' => [ 'label' => __( 'X Circle', 'megamenu' ), 'svg' => $svgs['svg-close-circle'] ],
				] ),
			];

			return $icons;
		}

		/**
		 * Inject SVG HTML into the standard toggle button for SVG open/closed icons.
		 *
		 * @param string $html     The toggle button HTML.
		 * @param array  $settings Toggle block settings.
		 * @return string
		 */
		public function inject_svg_toggle_icons( $html, $settings ) {
			$closed_icon = isset( $settings['closed_icon'] ) ? $settings['closed_icon'] : '';
			$open_icon   = isset( $settings['open_icon'] ) ? $settings['open_icon'] : '';

			$closed_is_svg = strpos( $closed_icon, 'svg-' ) === 0;
			$open_is_svg   = strpos( $open_icon, 'svg-' ) === 0;

			if ( ! $closed_is_svg && ! $open_is_svg ) {
				return $html;
			}

			$icons     = self::flat_icons( apply_filters( 'megamenu_theme_toggle_icons', [] ) );
			$injection = '';

			if ( $closed_is_svg && isset( $icons[ $closed_icon ]['svg'] ) ) {
				$injection .= '<span class="mega-svg-icon mega-svg-icon-closed">' . $icons[ $closed_icon ]['svg'] . '</span>';
			}

			if ( $open_is_svg && isset( $icons[ $open_icon ]['svg'] ) ) {
				$injection .= '<span class="mega-svg-icon mega-svg-icon-open">' . $icons[ $open_icon ]['svg'] . '</span>';
			}

			if ( ! $injection ) {
				return $html;
			}

			return str_replace( '</button>', $injection . '</button>', $html );
		}

		/**
		 * Inject SVG HTML into the mobile close button when an SVG close icon is selected.
		 *
		 * @param string $button_html       The close button HTML.
		 * @param array  $args              wp_nav_menu arguments.
		 * @param array  $location_settings Location settings.
		 * @param array  $menu_theme        Theme settings for the current menu.
		 * @return string
		 */
		public function inject_svg_close_icon( $button_html, $args, $location_settings, $menu_theme ) {
			$close_icon = isset( $menu_theme['close_icon'] ) ? $menu_theme['close_icon'] : '';

			if ( strpos( $close_icon, 'svg-' ) !== 0 ) {
				return $button_html;
			}

			$icons = self::flat_icons( apply_filters( 'megamenu_theme_close_icons', [] ) );

			if ( ! isset( $icons[ $close_icon ]['svg'] ) ) {
				return $button_html;
			}

			return str_replace( '</button>', $icons[ $close_icon ]['svg'] . '</button>', $button_html );
		}

		/**
		 * Flatten a grouped icon array into a single key → entry map.
		 *
		 * @param array $groups [ 'group-id' => [ 'label' => '...', 'icons' => [ 'icon-key' => [...] ] ] ]
		 * @return array
		 */
		private static function flat_icons( array $groups ) {
			$flat = [];
			foreach ( $groups as $group ) {
				$flat = array_merge( $flat, $group['icons'] ?? [] );
			}
			return $flat;
		}
	}

endif;
