<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Theme' ) ) :

	/**
	 * Represents a single Mega Menu theme — its settings and persistence.
	 * CSS generation lives in Mega_Menu_Location::generate_css().
	 *
	 * @since   3.9
	 * @package MegaMenu
	 */
	class Mega_Menu_Theme {

		/**
		 * Theme ID (e.g. 'default', 'custom_theme_1').
		 *
		 * @var string
		 */
		public $id;

		/**
		 * Theme settings array (~100 keys).
		 *
		 * @var array
		 */
		public $settings;


		/**
		 * Constructor.
		 *
		 * @param string $id       Theme ID.
		 * @param array  $settings Theme settings.
		 */
		public function __construct( $id, $settings ) {
			$this->id       = $id;
			$this->settings = $settings;
		}


		/**
		 * Get a single setting value by key.
		 *
		 * @param  string $key     Setting key.
		 * @param  mixed  $default Default value if key is not set.
		 * @return mixed
		 */
		public function get( $key, $default = null ) {
			return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;
		}


		/**
		 * Test that this theme compiles to valid CSS, using a dummy location.
		 * Used to validate before saving.
		 *
		 * @return string|WP_Error Compiled CSS on success, or WP_Error on failure.
		 */
		public function test_compilation() {
			$test_location = new Mega_Menu_Location( 'test', 'Test', [] );
			return $test_location->generate_css( $this );
		}


		/**
		 * Return the default theme settings array.
		 *
		 * @return self
		 */
		public static function get_default() {
			$settings = apply_filters(
				'megamenu_default_theme',
				[
					'title'                                => __( 'Default', 'megamenu' ),
					'container_background_from'            => '#222',
					'container_background_to'              => '#222',
					'container_padding_left'               => '0px',
					'container_padding_right'              => '0px',
					'container_padding_top'                => '0px',
					'container_padding_bottom'             => '0px',
					'container_border_radius_top_left'     => '0px',
					'container_border_radius_top_right'    => '0px',
					'container_border_radius_bottom_left'  => '0px',
					'container_border_radius_bottom_right' => '0px',
					'arrow_up'                             => 'dash-f142',
					'arrow_down'                           => 'dash-f140',
					'arrow_left'                           => 'dash-f141',
					'arrow_right'                          => 'dash-f139',
					'close_icon'                           => 'dash-f158',
					'close_icon_font_size'                 => '16px',
					'close_icon_color'                     => '#fff',
					'close_icon_label'                     => 'Close',
					'font_size'                            => '14px', // deprecated
					'font_color'                           => '#666', // deprecated
					'font_family'                          => 'inherit', // deprecated
					'menu_item_align'                      => 'left',
					'menu_item_background_from'            => 'rgba(0,0,0,0)',
					'menu_item_background_to'              => 'rgba(0,0,0,0)',
					'menu_item_background_hover_from'      => '#333',
					'menu_item_background_hover_to'        => '#333',
					'menu_item_spacing'                    => '0px',
					'menu_item_link_font'                  => 'inherit',
					'menu_item_link_font_size'             => '14px',
					'menu_item_link_height'                => '40px',
					'menu_item_link_color'                 => '#ffffff',
					'menu_item_link_weight'                => 'normal',
					'menu_item_link_text_transform'        => 'none',
					'menu_item_link_text_decoration'       => 'none',
					'menu_item_link_text_align'            => 'left',
					'menu_item_link_color_hover'           => '#ffffff',
					'menu_item_link_weight_hover'          => 'normal',
					'menu_item_link_text_decoration_hover' => 'none',
					'menu_item_link_padding_left'          => '10px',
					'menu_item_link_padding_right'         => '10px',
					'menu_item_link_padding_top'           => '0px',
					'menu_item_link_padding_bottom'        => '0px',
					'menu_item_link_border_radius_top_left' => '0px',
					'menu_item_link_border_radius_top_right' => '0px',
					'menu_item_link_border_radius_bottom_left' => '0px',
					'menu_item_link_border_radius_bottom_right' => '0px',
					'menu_item_border_color'               => '#fff',
					'menu_item_border_left'                => '0px',
					'menu_item_border_right'               => '0px',
					'menu_item_border_top'                 => '0px',
					'menu_item_border_bottom'              => '0px',
					'menu_item_border_color_hover'         => '#fff',
					'menu_item_highlight_current'          => 'on',
					'menu_item_divider'                    => 'off',
					'menu_item_divider_color'              => 'rgba(255, 255, 255, 0.1)',
					'menu_item_divider_glow_opacity'       => '0.1',
					'panel_background_from'                => '#f1f1f1',
					'panel_background_to'                  => '#f1f1f1',
					'panel_width'                          => '100%',
					'panel_inner_width'                    => '100%',
					'panel_border_color'                   => '#fff',
					'panel_border_left'                    => '0px',
					'panel_border_right'                   => '0px',
					'panel_border_top'                     => '0px',
					'panel_border_bottom'                  => '0px',
					'panel_border_radius_top_left'         => '0px',
					'panel_border_radius_top_right'        => '0px',
					'panel_border_radius_bottom_left'      => '0px',
					'panel_border_radius_bottom_right'     => '0px',
					'panel_header_color'                   => '#555',
					'panel_header_text_transform'          => 'uppercase',
					'panel_header_text_align'              => 'left',
					'panel_header_font'                    => 'inherit',
					'panel_header_font_size'               => '16px',
					'panel_header_font_weight'             => 'bold',
					'panel_header_text_decoration'         => 'none',
					'panel_header_padding_top'             => '0px',
					'panel_header_padding_right'           => '0px',
					'panel_header_padding_bottom'          => '5px',
					'panel_header_padding_left'            => '0px',
					'panel_header_margin_top'              => '0px',
					'panel_header_margin_right'            => '0px',
					'panel_header_margin_bottom'           => '0px',
					'panel_header_margin_left'             => '0px',
					'panel_header_border_color'            => 'rgba(0,0,0,0)',
					'panel_header_border_color_hover'      => 'rgba(0,0,0,0)',
					'panel_header_border_left'             => '0px',
					'panel_header_border_right'            => '0px',
					'panel_header_border_top'              => '0px',
					'panel_header_border_bottom'           => '0px',
					'panel_padding_left'                   => '0px',
					'panel_padding_right'                  => '0px',
					'panel_padding_top'                    => '0px',
					'panel_padding_bottom'                 => '0px',
					'panel_widget_padding_left'            => '15px',
					'panel_widget_padding_right'           => '15px',
					'panel_widget_padding_top'             => '15px',
					'panel_widget_padding_bottom'          => '15px',
					'panel_font_size'                      => 'font_size',
					'panel_font_color'                     => 'font_color',
					'panel_font_family'                    => 'font_family',
					'panel_second_level_font_color'        => 'panel_header_color',
					'panel_second_level_font_color_hover'  => 'panel_header_color',
					'panel_second_level_text_transform'    => 'panel_header_text_transform',
					'panel_second_level_text_align'        => 'left',
					'panel_second_level_font'              => 'panel_header_font',
					'panel_second_level_font_size'         => 'panel_header_font_size',
					'panel_second_level_font_weight'       => 'panel_header_font_weight',
					'panel_second_level_font_weight_hover' => 'panel_header_font_weight',
					'panel_second_level_text_decoration'   => 'panel_header_text_decoration',
					'panel_second_level_text_decoration_hover' => 'panel_header_text_decoration',
					'panel_second_level_background_hover_from' => 'rgba(0,0,0,0)',
					'panel_second_level_background_hover_to' => 'rgba(0,0,0,0)',
					'panel_second_level_padding_left'      => '0px',
					'panel_second_level_padding_right'     => '0px',
					'panel_second_level_padding_top'       => '0px',
					'panel_second_level_padding_bottom'    => '0px',
					'panel_second_level_margin_left'       => '0px',
					'panel_second_level_margin_right'      => '0px',
					'panel_second_level_margin_top'        => '0px',
					'panel_second_level_margin_bottom'     => '0px',
					'panel_second_level_border_color'      => 'rgba(0,0,0,0)',
					'panel_second_level_border_color_hover' => 'rgba(0,0,0,0)',
					'panel_second_level_border_left'       => '0px',
					'panel_second_level_border_right'      => '0px',
					'panel_second_level_border_top'        => '0px',
					'panel_second_level_border_bottom'     => '0px',
					'panel_third_level_font_color'         => 'panel_font_color',
					'panel_third_level_font_color_hover'   => 'panel_font_color',
					'panel_third_level_text_transform'     => 'none',
					'panel_third_level_text_align'         => 'left',
					'panel_third_level_font'               => 'panel_font_family',
					'panel_third_level_font_size'          => 'panel_font_size',
					'panel_third_level_font_weight'        => 'normal',
					'panel_third_level_font_weight_hover'  => 'normal',
					'panel_third_level_text_decoration'    => 'none',
					'panel_third_level_text_decoration_hover' => 'none',
					'panel_third_level_background_hover_from' => 'rgba(0,0,0,0)',
					'panel_third_level_background_hover_to' => 'rgba(0,0,0,0)',
					'panel_third_level_padding_left'       => '0px',
					'panel_third_level_padding_right'      => '0px',
					'panel_third_level_padding_top'        => '0px',
					'panel_third_level_padding_bottom'     => '0px',
					'panel_third_level_margin_left'        => '0px',
					'panel_third_level_margin_right'       => '0px',
					'panel_third_level_margin_top'         => '0px',
					'panel_third_level_margin_bottom'      => '0px',
					'panel_third_level_border_color'       => 'rgba(0,0,0,0)',
					'panel_third_level_border_color_hover' => 'rgba(0,0,0,0)',
					'panel_third_level_border_left'        => '0px',
					'panel_third_level_border_right'       => '0px',
					'panel_third_level_border_top'         => '0px',
					'panel_third_level_border_bottom'      => '0px',
					'flyout_width'                         => '250px',
					'flyout_menu_background_from'          => '#f1f1f1',
					'flyout_menu_background_to'            => '#f1f1f1',
					'flyout_border_color'                  => '#ffffff',
					'flyout_border_left'                   => '0px',
					'flyout_border_right'                  => '0px',
					'flyout_border_top'                    => '0px',
					'flyout_border_bottom'                 => '0px',
					'flyout_border_radius_top_left'        => '0px',
					'flyout_border_radius_top_right'       => '0px',
					'flyout_border_radius_bottom_left'     => '0px',
					'flyout_border_radius_bottom_right'    => '0px',
					'flyout_menu_item_divider'             => 'off',
					'flyout_menu_item_divider_color'       => 'rgba(255, 255, 255, 0.1)',
					'flyout_padding_top'                   => '0px',
					'flyout_padding_right'                 => '0px',
					'flyout_padding_bottom'                => '0px',
					'flyout_padding_left'                  => '0px',
					'flyout_link_padding_left'             => '10px',
					'flyout_link_padding_right'            => '10px',
					'flyout_link_padding_top'              => '0px',
					'flyout_link_padding_bottom'           => '0px',
					'flyout_link_weight'                   => 'normal',
					'flyout_link_weight_hover'             => 'normal',
					'flyout_link_height'                   => '35px',
					'flyout_link_text_decoration'          => 'none',
					'flyout_link_text_decoration_hover'    => 'none',
					'flyout_background_from'               => '#f1f1f1',
					'flyout_background_to'                 => '#f1f1f1',
					'flyout_background_hover_from'         => '#dddddd',
					'flyout_background_hover_to'           => '#dddddd',
					'flyout_link_size'                     => 'font_size',
					'flyout_link_color'                    => 'font_color',
					'flyout_link_color_hover'              => 'font_color',
					'flyout_link_family'                   => 'font_family',
					'flyout_link_text_transform'           => 'none',
					'responsive_breakpoint'                => '768px',
					'responsive_text'                      => 'MENU', // deprecated
					'line_height'                          => '1.7',
					'z_index'                              => '999',
					'shadow'                               => 'off',
					'shadow_horizontal'                    => '0px',
					'shadow_vertical'                      => '0px',
					'shadow_blur'                          => '5px',
					'shadow_spread'                        => '0px',
					'shadow_color'                         => 'rgba(0, 0, 0, 0.1)',
					'transitions'                          => 'off',
					'keyboard_highlight_color'             => '#109cde',
					'keyboard_highlight_width'             => '3px',
					'keyboard_highlight_offset'            => '-3px',
					'resets'                               => 'off',
					'mobile_columns'                       => '1',
					'toggle_background_from'               => 'container_background_from',
					'toggle_background_to'                 => 'container_background_to',
					'toggle_font_color'                    => 'rgb(221, 221, 221)', // deprecated
					'toggle_bar_height'                    => '40px',
					'toggle_bar_border_radius_top_left'    => '2px',
					'toggle_bar_border_radius_top_right'   => '2px',
					'toggle_bar_border_radius_bottom_left' => '2px',
					'toggle_bar_border_radius_bottom_right' => '2px',
					'mobile_menu_padding_left'             => '0px',
					'mobile_menu_padding_right'            => '0px',
					'mobile_menu_padding_top'              => '0px',
					'mobile_menu_padding_bottom'           => '0px',
					'mobile_menu_item_height'              => '40px',
					'mobile_menu_overlay'                  => 'off',
					'mobile_menu_force_width'              => 'off',
					'mobile_menu_force_width_selector'     => 'body',
					'mobile_background_from'               => 'container_background_from',
					'mobile_background_to'                 => 'container_background_to',
					'mobile_menu_item_link_font_size'      => 'menu_item_link_font_size',
					'mobile_menu_item_link_color'          => 'menu_item_link_color',
					'mobile_menu_item_link_text_align'     => 'menu_item_link_text_align',
					'mobile_menu_item_link_color_hover'    => 'menu_item_link_color_hover',
					'mobile_menu_item_background_hover_from' => 'menu_item_background_hover_from',
					'mobile_menu_item_background_hover_to' => 'menu_item_background_hover_to',
					'mobile_menu_off_canvas_width'         => '300px',
					'disable_mobile_toggle'                => 'off',
					'use_flex_css'                         => 'off',
					'custom_css'                           => '/** Push menu onto new line **/
#{$wrap} {
    clear: both;
}',
				]
			);

			return new self( 'default', $settings );
		}


		/**
		 * Build and return all themes: default + saved custom themes, fully merged and sorted.
		 *
		 * @return Mega_Menu_Theme[] Map of theme ID to Mega_Menu_Theme instance.
		 */
		public static function get_all() {
			$default      = self::get_default();
			$all_settings = [ 'default' => $default->settings ];

			$all_settings = apply_filters( 'megamenu_themes', $all_settings );

			// Merge in saved themes from DB.
			if ( $saved = max_mega_menu_get_themes() ) {
				foreach ( $saved as $id => $settings ) {
					if ( isset( $all_settings[ $id ] ) ) {
						$all_settings[ $id ] = array_merge( $all_settings[ $id ], $settings );
					} else {
						$all_settings[ $id ] = $settings;
					}
				}
			}

			// Ensure every theme has all default keys.
			$default_settings = $default->settings;
			foreach ( $all_settings as $id => $settings ) {
				$all_settings[ $id ] = array_merge( $default_settings, $settings );
			}

			// Resolve deprecated value references (e.g. 'font_color' → actual colour).
			foreach ( $all_settings as $id => $settings ) {
				foreach ( $settings as $var => $val ) {
					if ( ! is_array( $val ) && isset( $all_settings[ $id ][ $val ] ) ) {
						$all_settings[ $id ][ $var ] = $all_settings[ $id ][ $val ];
					}
				}
			}

			// Wrap in instances and sort by title.
			$themes = [];
			foreach ( $all_settings as $id => $settings ) {
				$themes[ $id ] = new self( $id, $settings );
			}

			uasort( $themes, function( $a, $b ) {
				return strcmp( $a->get( 'title', '' ), $b->get( 'title', '' ) );
			} );

			return $themes;
		}


		/**
		 * Find a single theme by ID, falling back to the default theme if not found.
		 *
		 * @param  string $id Theme ID.
		 * @return self
		 */
		public static function find( $id ) {
			$all = self::get_all();
			return isset( $all[ $id ] ) ? $all[ $id ] : $all['default'];
		}


		/**
		 * Create a theme instance from a raw settings array without an ID.
		 * Used when testing theme compilation before saving.
		 *
		 * @param  array $settings Raw settings array.
		 * @return self
		 */
		public static function from_settings( $settings ) {
			return new self( '_preview', $settings );
		}


	}

endif;
