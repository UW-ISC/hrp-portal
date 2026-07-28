<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mega_Menu_Dashicons' ) ) :

	/**
	 * Dashicons icon tab and payload for the menu item dialog.
	 *
	 * @package MegaMenu
	 */
	class Mega_Menu_Dashicons {

		/**
		 * Constructor - register icon tab filter before Material Symbols (priority 10).
		 */
		public function __construct() {
			add_filter( 'megamenu_icon_tabs', [ $this, 'prepend_dashicons_tab' ], 5, 5 );
			add_filter( 'megamenu_theme_arrow_icons', [ $this, 'add_arrow_icons' ] );
			add_filter( 'megamenu_theme_close_icons', [ $this, 'add_close_icons' ] );
			add_filter( 'megamenu_theme_toggle_icons', [ $this, 'add_toggle_icons' ] );
			add_action( 'megamenu_enqueue_public_scripts', [ $this, 'enqueue_styles' ] );
		}

		/**
		 * Enqueue the Dashicons stylesheet on the frontend.
		 */
		public function enqueue_styles() {
			wp_enqueue_style( 'dashicons' );
		}

		/**
		 * Add Dashicons arrow styles to the theme editor.
		 *
		 * @param array $styles Current registered styles.
		 * @return array
		 */
		public function add_arrow_icons( $icons ) {
			$icons['dashicons'] = [
				'label' => __( 'Dashicons', 'megamenu' ),
				'icons' => array_merge( $icons['dashicons']['icons'] ?? [], [
					'dashicons-standard' => [
						'label' => __( 'Dashicons (Standard)', 'megamenu' ),
						'class' => 'dashicons',
						'icons' => [
							'up'    => [ 'value' => 'dash-f142', 'icon' => 'dashicons-arrow-up'    ],
							'down'  => [ 'value' => 'dash-f140', 'icon' => 'dashicons-arrow-down'  ],
							'left'  => [ 'value' => 'dash-f141', 'icon' => 'dashicons-arrow-left'  ],
							'right' => [ 'value' => 'dash-f139', 'icon' => 'dashicons-arrow-right' ],
						],
					],
					'dashicons-alt' => [
						'label' => __( 'Dashicons (Alt)', 'megamenu' ),
						'class' => 'dashicons',
						'icons' => [
							'up'    => [ 'value' => 'dash-f342', 'icon' => 'dashicons-arrow-up-alt'    ],
							'down'  => [ 'value' => 'dash-f346', 'icon' => 'dashicons-arrow-down-alt'  ],
							'left'  => [ 'value' => 'dash-f340', 'icon' => 'dashicons-arrow-left-alt'  ],
							'right' => [ 'value' => 'dash-f344', 'icon' => 'dashicons-arrow-right-alt' ],
						],
					],
					'dashicons-alt2' => [
						'label' => __( 'Dashicons (Alt 2)', 'megamenu' ),
						'class' => 'dashicons',
						'icons' => [
							'up'    => [ 'value' => 'dash-f343', 'icon' => 'dashicons-arrow-up-alt2'    ],
							'down'  => [ 'value' => 'dash-f347', 'icon' => 'dashicons-arrow-down-alt2'  ],
							'left'  => [ 'value' => 'dash-f341', 'icon' => 'dashicons-arrow-left-alt2'  ],
							'right' => [ 'value' => 'dash-f345', 'icon' => 'dashicons-arrow-right-alt2' ],
						],
					],
				] ),
			];

			return $icons;
		}

		/**
		 * Add Dashicons close icon styles to the theme editor.
		 *
		 * @param array $icons Current registered icons.
		 * @return array
		 */
		public function add_close_icons( $icons ) {
			$icons['dashicons'] = [
				'label' => __( 'Dashicons', 'megamenu' ),
				'icons' => array_merge( $icons['dashicons']['icons'] ?? [], [
					'dash-f158' => [ 'label' => __( 'No',               'megamenu' ), 'class' => 'dashicons-no'              ],
					'dash-f335' => [ 'label' => __( 'No Alt',           'megamenu' ), 'class' => 'dashicons-no-alt'          ],
					'dash-f153' => [ 'label' => __( 'Dismiss',          'megamenu' ), 'class' => 'dashicons-dismiss'         ],
					'dash-f460' => [ 'label' => __( 'Minus',            'megamenu' ), 'class' => 'dashicons-minus'           ],
					'dash-f14f' => [ 'label' => __( 'Remove',           'megamenu' ), 'class' => 'dashicons-remove'          ],
					'dash-f171' => [ 'label' => __( 'Undo',             'megamenu' ), 'class' => 'dashicons-undo'            ],
					'dash-f518' => [ 'label' => __( 'Controls Back',    'megamenu' ), 'class' => 'dashicons-controls-back'   ],
					'dash-f340' => [ 'label' => __( 'Arrow Left Alt',   'megamenu' ), 'class' => 'dashicons-arrow-left-alt'  ],
					'dash-f341' => [ 'label' => __( 'Arrow Left Alt 2', 'megamenu' ), 'class' => 'dashicons-arrow-left-alt2' ],
					'dash-f148' => [ 'label' => __( 'Admin Collapse',   'megamenu' ), 'class' => 'dashicons-admin-collapse'  ],
				] ),
			];

			return $icons;
		}

		/**
		 * Add Dashicon options to the toggle icon selector.
		 *
		 * @param array $styles Existing registered styles.
		 * @return array
		 */
		public function add_toggle_icons( $styles ) {
			$styles['dashicons'] = [
				'label' => __( 'Dashicons', 'megamenu' ),
				'icons' => array_merge( $styles['dashicons']['icons'] ?? [], [
					'dash-f333' => [ 'label' => __( 'Menu',             'megamenu' ), 'class' => 'dashicons-menu'            ],
					'dash-f228' => [ 'label' => __( 'Menu Alt',         'megamenu' ), 'class' => 'dashicons-menu-alt'        ],
					'dash-f329' => [ 'label' => __( 'Menu Alt 2',       'megamenu' ), 'class' => 'dashicons-menu-alt2'       ],
					'dash-f349' => [ 'label' => __( 'Menu Alt 3',       'megamenu' ), 'class' => 'dashicons-menu-alt3'       ],
					'dash-f214' => [ 'label' => __( 'Justify',          'megamenu' ), 'class' => 'dashicons-editor-justify'  ],
					'dash-f158' => [ 'label' => __( 'No',               'megamenu' ), 'class' => 'dashicons-no'              ],
					'dash-f335' => [ 'label' => __( 'No Alt',           'megamenu' ), 'class' => 'dashicons-no-alt'          ],
					'dash-f153' => [ 'label' => __( 'Dismiss',          'megamenu' ), 'class' => 'dashicons-dismiss'         ],
					'dash-f132' => [ 'label' => __( 'Plus',             'megamenu' ), 'class' => 'dashicons-plus'            ],
					'dash-f502' => [ 'label' => __( 'Plus Alt',         'megamenu' ), 'class' => 'dashicons-plus-alt'        ],
					'dash-f460' => [ 'label' => __( 'Minus',            'megamenu' ), 'class' => 'dashicons-minus'           ],
					'dash-f142' => [ 'label' => __( 'Arrow Up',         'megamenu' ), 'class' => 'dashicons-arrow-up'        ],
					'dash-f140' => [ 'label' => __( 'Arrow Down',       'megamenu' ), 'class' => 'dashicons-arrow-down'      ],
					'dash-f342' => [ 'label' => __( 'Arrow Up Alt',     'megamenu' ), 'class' => 'dashicons-arrow-up-alt'    ],
					'dash-f346' => [ 'label' => __( 'Arrow Down Alt',   'megamenu' ), 'class' => 'dashicons-arrow-down-alt'  ],
					'dash-f343' => [ 'label' => __( 'Arrow Up Alt 2',   'megamenu' ), 'class' => 'dashicons-arrow-up-alt2'   ],
					'dash-f347' => [ 'label' => __( 'Arrow Down Alt 2', 'megamenu' ), 'class' => 'dashicons-arrow-down-alt2' ],
				] ),
			];

			return $styles;
		}

		/**
		 * Prepend the Dashicons tab to the icon picker.
		 *
		 * @param array $icon_tabs       Tabs from {@see Mega_Menu_Menu_Item_Manager::add_icon_tab()}.
		 * @param int   $menu_item_id    Menu item ID.
		 * @param int   $menu_id         Menu term ID.
		 * @param int   $menu_item_depth Item depth.
		 * @param array $menu_item_meta  Saved mega menu settings for the item.
		 * @return array
		 */
		public function prepend_dashicons_tab( $icon_tabs, $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta ) {
			$is_active = ! isset( $menu_item_meta['icon'] ) || ( isset( $menu_item_meta['icon'] ) && ( substr( (string) $menu_item_meta['icon'], 0, strlen( 'dash' ) ) === 'dash' || $menu_item_meta['icon'] === 'disabled' ) );

			$dashicons_tab = [
				'dashicons' => [
					'title'        => __( 'Dashicons', 'megamenu' ),
					'active'       => $is_active,
					'content'      => $this->dashicon_selector_lazy_shell( $menu_item_meta ),
					'icon_payload' => [
						'dashicons' => $this->dashicons_icon_payload_list(),
					],
				],
			];

			return array_merge( $dashicons_tab, $icon_tabs );
		}

		/**
		 * Dashicon picker shell (lazy): "no icon" row + placeholder; icons built client-side from payload.
		 *
		 * @param array $menu_item_meta Saved settings.
		 * @return string HTML.
		 */
		private function dashicon_selector_lazy_shell( $menu_item_meta ) {
			$icon = isset( $menu_item_meta['icon'] ) ? (string) $menu_item_meta['icon'] : 'disabled';
			$return  = "<div class='disabled'><input id='disabled' class='radio' type='radio' rel='disabled' name='settings[icon]' value='disabled' " . checked( $icon, 'disabled', false ) . ' />';
			$return .= "<label for='disabled'></label></div>";
			$return .= '<div class="mmm-icon-grid-host" data-mmm-icon-lazy="dashicons" aria-busy="true"></div>';

			return $return;
		}

		/**
		 * Dashicon data for the menu item dialog {@see 'icon_payload'} (built into DOM in JS).
		 *
		 * @return array<int, array{type: string, hex: string, value: string}>
		 */
		private function dashicons_icon_payload_list() {
			$list = [];

			foreach ( self::all_icons() as $code => $class ) {
				$bits = explode( '-', $code, 2 );
				if ( count( $bits ) < 2 ) {
					continue;
				}
				$list[] = [
					'type'  => $bits[0],
					'hex'   => $bits[1],
					'value' => $class,
				];
			}

			return $list;
		}

		/**
		 * List of all available Dashicon classes.
		 *
		 * @since 1.0
		 * @return array Sorted map of hex code keys to Dashicon CSS class names.
		 */
		public static function all_icons() {
			$icons = [
				'dash-f333' => 'dashicons-menu',
				'dash-f228' => 'dashicons-menu-alt',
				'dash-f329' => 'dashicons-menu-alt2',
				'dash-f349' => 'dashicons-menu-alt3',
				'dash-f319' => 'dashicons-admin-site',
				'dash-f11d' => 'dashicons-admin-site-alt',
				'dash-f11e' => 'dashicons-admin-site-alt2',
				'dash-f11f' => 'dashicons-admin-site-alt3',
				'dash-f226' => 'dashicons-dashboard',
				'dash-f109' => 'dashicons-admin-post',
				'dash-f104' => 'dashicons-admin-media',
				'dash-f103' => 'dashicons-admin-links',
				'dash-f105' => 'dashicons-admin-page',
				'dash-f101' => 'dashicons-admin-comments',
				'dash-f100' => 'dashicons-admin-appearance',
				'dash-f106' => 'dashicons-admin-plugins',
				'dash-f485' => 'dashicons-plugins-checked',
				'dash-f110' => 'dashicons-admin-users',
				'dash-f107' => 'dashicons-admin-tools',
				'dash-f108' => 'dashicons-admin-settings',
				'dash-f112' => 'dashicons-admin-network',
				'dash-f102' => 'dashicons-admin-home',
				'dash-f111' => 'dashicons-admin-generic',
				'dash-f148' => 'dashicons-admin-collapse',
				'dash-f536' => 'dashicons-filter',
				'dash-f540' => 'dashicons-admin-customizer',
				'dash-f541' => 'dashicons-admin-multisite',
				'dash-f119' => 'dashicons-welcome-write-blog',
				'dash-f133' => 'dashicons-welcome-add-page',
				'dash-f115' => 'dashicons-welcome-view-site',
				'dash-f116' => 'dashicons-welcome-widgets-menus',
				'dash-f117' => 'dashicons-welcome-comments',
				'dash-f118' => 'dashicons-welcome-learn-more',
				'dash-f123' => 'dashicons-format-aside',
				'dash-f128' => 'dashicons-format-image',
				'dash-f161' => 'dashicons-format-gallery',
				'dash-f126' => 'dashicons-format-video',
				'dash-f130' => 'dashicons-format-status',
				'dash-f122' => 'dashicons-format-quote',
				'dash-f125' => 'dashicons-format-chat',
				'dash-f127' => 'dashicons-format-audio',
				'dash-f306' => 'dashicons-camera',
				'dash-f129' => 'dashicons-camera-alt',
				'dash-f232' => 'dashicons-images-alt',
				'dash-f233' => 'dashicons-images-alt2',
				'dash-f234' => 'dashicons-video-alt',
				'dash-f235' => 'dashicons-video-alt2',
				'dash-f236' => 'dashicons-video-alt3',
				'dash-f501' => 'dashicons-media-archive',
				'dash-f500' => 'dashicons-media-audio',
				'dash-f499' => 'dashicons-media-code',
				'dash-f498' => 'dashicons-media-default',
				'dash-f497' => 'dashicons-media-document',
				'dash-f496' => 'dashicons-media-interactive',
				'dash-f495' => 'dashicons-media-spreadsheet',
				'dash-f491' => 'dashicons-media-text',
				'dash-f490' => 'dashicons-media-video',
				'dash-f492' => 'dashicons-playlist-audio',
				'dash-f493' => 'dashicons-playlist-video',
				'dash-f522' => 'dashicons-controls-play',
				'dash-f523' => 'dashicons-controls-pause',
				'dash-f519' => 'dashicons-controls-forward',
				'dash-f517' => 'dashicons-controls-skipforward',
				'dash-f518' => 'dashicons-controls-back',
				'dash-f516' => 'dashicons-controls-skipback',
				'dash-f515' => 'dashicons-controls-repeat',
				'dash-f521' => 'dashicons-controls-volumeon',
				'dash-f520' => 'dashicons-controls-volumeoff',
				'dash-f165' => 'dashicons-image-crop',
				'dash-f531' => 'dashicons-image-rotate',
				'dash-f166' => 'dashicons-image-rotate-left',
				'dash-f167' => 'dashicons-image-rotate-right',
				'dash-f168' => 'dashicons-image-flip-vertical',
				'dash-f169' => 'dashicons-image-flip-horizontal',
				'dash-f533' => 'dashicons-image-filter',
				'dash-f171' => 'dashicons-undo',
				'dash-f172' => 'dashicons-redo',
				'dash-f170' => 'dashicons-database-add',
				'dash-f17e' => 'dashicons-database',
				'dash-f17a' => 'dashicons-database-export',
				'dash-f17b' => 'dashicons-database-import',
				'dash-f17c' => 'dashicons-database-remove',
				'dash-f17d' => 'dashicons-database-view',
				'dash-f134' => 'dashicons-align-full-width',
				'dash-f10a' => 'dashicons-align-pull-left',
				'dash-f10b' => 'dashicons-align-pull-right',
				'dash-f11b' => 'dashicons-align-wide',
				'dash-f12b' => 'dashicons-block-default',
				'dash-f11a' => 'dashicons-button',
				'dash-f137' => 'dashicons-cloud-saved',
				'dash-f13b' => 'dashicons-cloud-upload',
				'dash-f13c' => 'dashicons-columns',
				'dash-f13d' => 'dashicons-cover-image',
				'dash-f11c' => 'dashicons-ellipsis',
				'dash-f13e' => 'dashicons-embed-audio',
				'dash-f13f' => 'dashicons-embed-generic',
				'dash-f144' => 'dashicons-embed-photo',
				'dash-f146' => 'dashicons-embed-post',
				'dash-f149' => 'dashicons-embed-video',
				'dash-f14a' => 'dashicons-exit',
				'dash-f10e' => 'dashicons-heading',
				'dash-f14b' => 'dashicons-html',
				'dash-f14c' => 'dashicons-info-outline',
				'dash-f10f' => 'dashicons-insert',
				'dash-f14d' => 'dashicons-insert-after',
				'dash-f14e' => 'dashicons-insert-before',
				'dash-f14f' => 'dashicons-remove',
				'dash-f15e' => 'dashicons-saved',
				'dash-f150' => 'dashicons-shortcode',
				'dash-f151' => 'dashicons-table-col-after',
				'dash-f152' => 'dashicons-table-col-before',
				'dash-f15a' => 'dashicons-table-col-delete',
				'dash-f15b' => 'dashicons-table-row-after',
				'dash-f15c' => 'dashicons-table-row-before',
				'dash-f15d' => 'dashicons-table-row-delete',
				'dash-f200' => 'dashicons-editor-bold',
				'dash-f201' => 'dashicons-editor-italic',
				'dash-f203' => 'dashicons-editor-ul',
				'dash-f204' => 'dashicons-editor-ol',
				'dash-f12c' => 'dashicons-editor-ol-rtl',
				'dash-f205' => 'dashicons-editor-quote',
				'dash-f206' => 'dashicons-editor-alignleft',
				'dash-f207' => 'dashicons-editor-aligncenter',
				'dash-f208' => 'dashicons-editor-alignright',
				'dash-f209' => 'dashicons-editor-insertmore',
				'dash-f210' => 'dashicons-editor-spellcheck',
				'dash-f211' => 'dashicons-editor-expand',
				'dash-f506' => 'dashicons-editor-contract',
				'dash-f212' => 'dashicons-editor-kitchensink',
				'dash-f213' => 'dashicons-editor-underline',
				'dash-f214' => 'dashicons-editor-justify',
				'dash-f215' => 'dashicons-editor-textcolor',
				'dash-f216' => 'dashicons-editor-paste-word',
				'dash-f217' => 'dashicons-editor-paste-text',
				'dash-f218' => 'dashicons-editor-removeformatting',
				'dash-f219' => 'dashicons-editor-video',
				'dash-f220' => 'dashicons-editor-customchar',
				'dash-f221' => 'dashicons-editor-outdent',
				'dash-f222' => 'dashicons-editor-indent',
				'dash-f223' => 'dashicons-editor-help',
				'dash-f224' => 'dashicons-editor-strikethrough',
				'dash-f225' => 'dashicons-editor-unlink',
				'dash-f320' => 'dashicons-editor-rtl',
				'dash-f10c' => 'dashicons-editor-ltr',
				'dash-f474' => 'dashicons-editor-break',
				'dash-f475' => 'dashicons-editor-code',
				'dash-f476' => 'dashicons-editor-paragraph',
				'dash-f535' => 'dashicons-editor-table',
				'dash-f135' => 'dashicons-align-left',
				'dash-f136' => 'dashicons-align-right',
				'dash-f134' => 'dashicons-align-center',
				'dash-f138' => 'dashicons-align-none',
				'dash-f160' => 'dashicons-lock',
				'dash-f528' => 'dashicons-unlock',
				'dash-f145' => 'dashicons-calendar',
				'dash-f508' => 'dashicons-calendar-alt',
				'dash-f177' => 'dashicons-visibility',
				'dash-f530' => 'dashicons-hidden',
				'dash-f173' => 'dashicons-post-status',
				'dash-f464' => 'dashicons-edit',
				'dash-f182' => 'dashicons-trash',
				'dash-f537' => 'dashicons-sticky',
				'dash-f504' => 'dashicons-external',
				'dash-f142' => 'dashicons-arrow-up',
				'dash-f140' => 'dashicons-arrow-down',
				'dash-f139' => 'dashicons-arrow-right',
				'dash-f141' => 'dashicons-arrow-left',
				'dash-f342' => 'dashicons-arrow-up-alt',
				'dash-f346' => 'dashicons-arrow-down-alt',
				'dash-f344' => 'dashicons-arrow-right-alt',
				'dash-f340' => 'dashicons-arrow-left-alt',
				'dash-f343' => 'dashicons-arrow-up-alt2',
				'dash-f347' => 'dashicons-arrow-down-alt2',
				'dash-f345' => 'dashicons-arrow-right-alt2',
				'dash-f341' => 'dashicons-arrow-left-alt2',
				'dash-f156' => 'dashicons-sort',
				'dash-f229' => 'dashicons-leftright',
				'dash-f503' => 'dashicons-randomize',
				'dash-f163' => 'dashicons-list-view',
				'dash-f164' => 'dashicons-excerpt-view',
				'dash-f509' => 'dashicons-grid-view',
				'dash-f545' => 'dashicons-move',
				'dash-f237' => 'dashicons-share',
				'dash-f240' => 'dashicons-share-alt',
				'dash-f242' => 'dashicons-share-alt2',
				'dash-f303' => 'dashicons-rss',
				'dash-f465' => 'dashicons-email',
				'dash-f466' => 'dashicons-email-alt',
				'dash-f467' => 'dashicons-email-alt2',
				'dash-f325' => 'dashicons-networking',
				'dash-f162' => 'dashicons-amazon',
				'dash-f304' => 'dashicons-facebook',
				'dash-f305' => 'dashicons-facebook-alt',
				'dash-f18b' => 'dashicons-google',
				'dash-f462' => 'dashicons-googleplus',
				'dash-f12d' => 'dashicons-instagram',
				'dash-f18d' => 'dashicons-linkedin',
				'dash-f192' => 'dashicons-pinterest',
				'dash-f19c' => 'dashicons-podio',
				'dash-f195' => 'dashicons-reddit',
				'dash-f196' => 'dashicons-spotify',
				'dash-f199' => 'dashicons-twitch',
				'dash-f301' => 'dashicons-twitter',
				'dash-f302' => 'dashicons-twitter-alt',
				'dash-f19a' => 'dashicons-whatsapp',
				'dash-f19d' => 'dashicons-xing',
				'dash-f19b' => 'dashicons-youtube',
				'dash-f308' => 'dashicons-hammer',
				'dash-f309' => 'dashicons-art',
				'dash-f310' => 'dashicons-migrate',
				'dash-f311' => 'dashicons-performance',
				'dash-f483' => 'dashicons-universal-access',
				'dash-f507' => 'dashicons-universal-access-alt',
				'dash-f486' => 'dashicons-tickets',
				'dash-f484' => 'dashicons-nametag',
				'dash-f481' => 'dashicons-clipboard',
				'dash-f487' => 'dashicons-heart',
				'dash-f488' => 'dashicons-megaphone',
				'dash-f489' => 'dashicons-schedule',
				'dash-f10d' => 'dashicons-tide',
				'dash-f124' => 'dashicons-rest-api',
				'dash-f13a' => 'dashicons-code-standards',
				'dash-f452' => 'dashicons-buddicons-activity',
				'dash-f477' => 'dashicons-buddicons-bbpress-logo',
				'dash-f448' => 'dashicons-buddicons-buddypress-logo',
				'dash-f453' => 'dashicons-buddicons-community',
				'dash-f449' => 'dashicons-buddicons-forums',
				'dash-f454' => 'dashicons-buddicons-friends',
				'dash-f456' => 'dashicons-buddicons-groups',
				'dash-f457' => 'dashicons-buddicons-pm',
				'dash-f451' => 'dashicons-buddicons-replies',
				'dash-f450' => 'dashicons-buddicons-topics',
				'dash-f455' => 'dashicons-buddicons-tracking',
				'dash-f120' => 'dashicons-wordpress',
				'dash-f324' => 'dashicons-wordpress-alt',
				'dash-f157' => 'dashicons-pressthis',
				'dash-f463' => 'dashicons-update',
				'dash-f113' => 'dashicons-update-alt',
				'dash-f180' => 'dashicons-screenoptions',
				'dash-f348' => 'dashicons-info',
				'dash-f174' => 'dashicons-cart',
				'dash-f175' => 'dashicons-feedback',
				'dash-f176' => 'dashicons-cloud',
				'dash-f326' => 'dashicons-translation',
				'dash-f323' => 'dashicons-tag',
				'dash-f318' => 'dashicons-category',
				'dash-f480' => 'dashicons-archive',
				'dash-f479' => 'dashicons-tagcloud',
				'dash-f478' => 'dashicons-text',
				'dash-f16d' => 'dashicons-bell',
				'dash-f147' => 'dashicons-yes',
				'dash-f12a' => 'dashicons-yes-alt',
				'dash-f158' => 'dashicons-no',
				'dash-f335' => 'dashicons-no-alt',
				'dash-f132' => 'dashicons-plus',
				'dash-f502' => 'dashicons-plus-alt',
				'dash-f543' => 'dashicons-plus-alt2',
				'dash-f460' => 'dashicons-minus',
				'dash-f153' => 'dashicons-dismiss',
				'dash-f159' => 'dashicons-marker',
				'dash-f155' => 'dashicons-star-filled',
				'dash-f459' => 'dashicons-star-half',
				'dash-f154' => 'dashicons-star-empty',
				'dash-f227' => 'dashicons-flag',
				'dash-f534' => 'dashicons-warning',
				'dash-f230' => 'dashicons-location',
				'dash-f231' => 'dashicons-location-alt',
				'dash-f178' => 'dashicons-vault',
				'dash-f332' => 'dashicons-shield',
				'dash-f334' => 'dashicons-shield-alt',
				'dash-f468' => 'dashicons-sos',
				'dash-f179' => 'dashicons-search',
				'dash-f181' => 'dashicons-slides',
				'dash-f121' => 'dashicons-text-page',
				'dash-f183' => 'dashicons-analytics',
				'dash-f184' => 'dashicons-chart-pie',
				'dash-f185' => 'dashicons-chart-bar',
				'dash-f238' => 'dashicons-chart-line',
				'dash-f239' => 'dashicons-chart-area',
				'dash-f307' => 'dashicons-groups',
				'dash-f338' => 'dashicons-businessman',
				'dash-f12f' => 'dashicons-businesswoman',
				'dash-f12e' => 'dashicons-businessperson',
				'dash-f336' => 'dashicons-id',
				'dash-f337' => 'dashicons-id-alt',
				'dash-f312' => 'dashicons-products',
				'dash-f313' => 'dashicons-awards',
				'dash-f314' => 'dashicons-forms',
				'dash-f473' => 'dashicons-testimonial',
				'dash-f322' => 'dashicons-portfolio',
				'dash-f330' => 'dashicons-book',
				'dash-f331' => 'dashicons-book-alt',
				'dash-f316' => 'dashicons-download',
				'dash-f317' => 'dashicons-upload',
				'dash-f321' => 'dashicons-backup',
				'dash-f469' => 'dashicons-clock',
				'dash-f339' => 'dashicons-lightbulb',
				'dash-f482' => 'dashicons-microphone',
				'dash-f472' => 'dashicons-desktop',
				'dash-f547' => 'dashicons-laptop',
				'dash-f471' => 'dashicons-tablet',
				'dash-f470' => 'dashicons-smartphone',
				'dash-f525' => 'dashicons-phone',
				'dash-f510' => 'dashicons-index-card',
				'dash-f511' => 'dashicons-carrot',
				'dash-f512' => 'dashicons-building',
				'dash-f513' => 'dashicons-store',
				'dash-f514' => 'dashicons-album',
				'dash-f527' => 'dashicons-palmtree',
				'dash-f524' => 'dashicons-tickets-alt',
				'dash-f526' => 'dashicons-money',
				'dash-f18e' => 'dashicons-money-alt',
				'dash-f328' => 'dashicons-smiley',
				'dash-f529' => 'dashicons-thumbs-up',
				'dash-f542' => 'dashicons-thumbs-down',
				'dash-f538' => 'dashicons-layout',
				'dash-f546' => 'dashicons-paperclip',
				'dash-f131' => 'dashicons-color-picker',
				'dash-f327' => 'dashicons-edit-large',
				'dash-f186' => 'dashicons-edit-page',
				'dash-f15f' => 'dashicons-airplane',
				'dash-f16a' => 'dashicons-bank',
				'dash-f16c' => 'dashicons-beer',
				'dash-f16e' => 'dashicons-calculator',
				'dash-f16b' => 'dashicons-car',
				'dash-f16f' => 'dashicons-coffee',
				'dash-f17f' => 'dashicons-drumstick',
				'dash-f187' => 'dashicons-food',
				'dash-f188' => 'dashicons-fullscreen-alt',
				'dash-f189' => 'dashicons-fullscreen-exit-alt',
				'dash-f18a' => 'dashicons-games',
				'dash-f18c' => 'dashicons-hourglass',
				'dash-f18f' => 'dashicons-open-folder',
				'dash-f190' => 'dashicons-pdf',
				'dash-f191' => 'dashicons-pets',
				'dash-f193' => 'dashicons-printer',
				'dash-f194' => 'dashicons-privacy',
				'dash-f198' => 'dashicons-superhero',
				'dash-f197' => 'dashicons-superhero-alt',
			];

			$icons = apply_filters( 'megamenu_dashicons', $icons );

			ksort( $icons );

			return $icons;
		}
	}

endif;
