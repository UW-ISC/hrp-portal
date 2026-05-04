<?php
/**
 * Plugin Name: Max Mega Menu
 * Plugin URI:  https://www.megamenu.com
 * Description: An easy to use mega menu plugin. Written the WordPress way.
 * Version:     3.9.2.1
 * Requires PHP: 7.4
 * Author:      megamenu.com
 * Author URI:  https://www.megamenu.com
 * License:     GPL-2.0+
 * Copyright:   2020 Tom Hemsley (https://www.megamenu.com)
 *
 * Max Mega Menu is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Max Mega Menu is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access.
}

if ( ! class_exists( 'Mega_Menu' ) ) :

	/**
	 * Main plugin class
	 */
	final class Mega_Menu {
		/**
		 * Current plugin version
		 *
		 * @var string
		 */
		public $version = '3.9.2.1';


		/**
		 * Identify the last version where a change was made to the CSS that requires the menu CSS to be rebuilt
		 *
		 * @var string
		 */
		public $scss_last_updated = '2.7';


		/**
		 * Init
		 *
		 * @since 1.0
		 */
		public static function init() {
			$plugin = new self();
		}


		/**
		 * Constructor
		 *
		 * @since 1.0
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();

			add_action( 'init', [ $this, 'load_plugin_textdomain' ] );
			add_action( 'init', [ $this, 'register_sidebar' ] );
			add_action( 'admin_init', [ $this, 'install_upgrade_check' ] );
			add_action( 'admin_notices', [ $this, 'admin_notices' ] );
			add_action( 'widgets_init', [ $this, 'register_widget' ] );
			add_filter( 'in_widget_form', [ $this, 'add_notice_to_nav_menu_widget' ], 10, 3 );

			add_action( 'after_setup_theme', [ $this, 'register_nav_menus' ] );

			add_filter( 'wp_nav_menu_args', [ $this, 'modify_nav_menu_args' ], 99999 );

			add_filter( 'wp_nav_menu_objects', [ $this, 'add_widgets_to_menu' ], apply_filters( 'megamenu_wp_nav_menu_objects_priority', 10 ), 2 );
			add_filter( 'megamenu_nav_menu_objects_before', [ $this, 'apply_depth_to_menu_items' ], 5, 2 );
			add_filter( 'megamenu_nav_menu_objects_before', [ $this, 'setup_menu_items' ], 5, 2 );
			add_filter( 'megamenu_nav_menu_objects_after', [ $this, 'reorder_menu_items_within_megamenus' ], 6, 2 );
			add_filter( 'megamenu_nav_menu_objects_after', [ $this, 'apply_classes_to_menu_items' ], 7, 2 );
			add_filter( 'megamenu_nav_menu_objects_after', [ $this, 'set_descriptions_if_enabled' ], 8, 2 );
			add_filter( 'body_class', [ $this, 'add_megamenu_body_classes' ], 10, 1 );

			add_filter( 'megamenu_nav_menu_css_class', [ $this, 'prefix_menu_classes' ], 10, 3 );
			add_filter( 'megamenu_nav_menu_css_class', [ $this, 'css_classes_never_highlight' ], 10, 3 );

			// plugin compatibility.
			add_filter( 'conditional_menus_theme_location', [ $this, 'conditional_menus_restore_theme_location' ], 10, 3 );
			add_filter( 'black_studio_tinymce_enable_pages', [ $this, 'megamenu_blackstudio_tinymce' ] );

			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ], 11 );
			add_filter( 'admin_body_class', [ $this, 'add_maxmegamenu_admin_body_class' ] );

			add_action( 'admin_print_footer_scripts-nav-menus.php', [ $this, 'admin_print_footer_scripts' ] );
			add_action( 'admin_print_scripts-nav-menus.php', [ $this, 'admin_print_scripts' ] );
			add_action( 'admin_print_styles-nav-menus.php', [ $this, 'admin_print_styles' ] );

			add_shortcode( 'maxmenu', [ $this, 'register_shortcode' ] );
			add_shortcode( 'maxmegamenu', [ $this, 'register_shortcode' ] );

			add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widget' ] );

			if ( is_admin() ) {
				$admin_classes = [
					'Mega_Menu_Preview',
					'Mega_Menu_Nav_Menus',
					'Mega_Menu_Widget_Manager',
					'Mega_Menu_Menu_Item_Manager',
					'Mega_Menu_Page',
					'Mega_Menu_General',
					'Mega_Menu_Locations',
					'Mega_Menu_Themes',
					'Mega_Menu_Tools',
					'Mega_Menu_Admin_Notices'
				];

				foreach ( $admin_classes as $class ) {
					if ( class_exists( $class ) ) {
						new $class();
					}
				}
			}

			if ( class_exists( 'Mega_Menu_Toggle_Blocks' ) ) {
				new Mega_Menu_Toggle_Blocks();
			}

			if ( class_exists( 'Mega_Menu_Style_Manager' ) ) {
				$mega_menu_style_manager = new Mega_Menu_Style_Manager();
				$mega_menu_style_manager->setup_actions();
			}

		}


		/**
		 * Add a body class for each active mega menu location.
		 *
		 * @since  2.3
		 * @param  array $classes current body classes.
		 * @return array
		 */
		public function add_megamenu_body_classes( $classes ) {
			foreach ( Mega_Menu_Location::get_all() as $location ) {
				if ( $location->is_enabled() && has_nav_menu( $location->id ) ) {
					$classes[] = 'mega-menu-' . str_replace( '_', '-', $location->id );
				}
			}

			return $classes;
		}

		/**
		 * Add `maxmegamenu-admin` to the admin body on Appearance > Menus and Max Mega Menu plugin screens
		 * so admin styles can target one class instead of multiple body selectors.
		 *
		 * @param string $classes Space-separated body classes.
		 * @return string
		 */
		public function add_maxmegamenu_admin_body_class( $classes ) {
			if ( false !== strpos( $classes, 'maxmegamenu-admin' ) ) {
				return $classes;
			}

			if ( isset( $GLOBALS['pagenow'] ) && 'nav-menus.php' === $GLOBALS['pagenow'] ) {
				return $classes . ' maxmegamenu-admin';
			}

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			if ( $screen && is_string( $screen->id ) ) {
				if ( 'toplevel_page_maxmegamenu' === $screen->id || false !== strpos( $screen->id, 'maxmegamenu' ) ) {
					return $classes . ' maxmegamenu-admin';
				}
			}

			return $classes;
		}


		/**
		 * Add custom actions to allow enqueuing scripts on specific pages
		 *
		 * @since 1.8.3
		 * @param string $hook page ID.
		 */
		public function admin_enqueue_scripts( $hook ) {
			if ( ! wp_script_is( 'maxmegamenu' ) ) {

				if ( in_array( $hook, [ 'nav-menus.php', 'gutenberg_page_gutenberg-navigation' ], true ) ) {
					// load widget scripts and styles first to allow us to dequeue conflicting colorbox scripts from other plugins.
					do_action( 'sidebar_admin_setup' );
					do_action( 'megamenu_nav_menus_scripts', $hook );
				}

				if ( strpos( $hook, 'maxmegamenu' ) !== false ) {
					do_action( 'megamenu_admin_scripts', $hook );
				}
			}
		}


		/**
		 * Print the widgets.php footer scripts on the nav-menus.php page. Required for 4.8 Core Media Widgets.
		 *
		 * @since 2.3.7
		 */
		public function admin_print_footer_scripts() {
			do_action( 'admin_footer-widgets.php' );
		}


		/**
		 * Print the widgets.php scripts on the nav-menus.php page. Required for 4.8 Core Media Widgets.
		 *
		 * @since 2.3.7
		 */
		public function admin_print_scripts() {
			do_action( 'admin_print_scripts-widgets.php' );
		}


		/**
		 * Print the widgets.php styles on the nav-menus.php page. Required for 4.8 Core Media Widgets.
		 *
		 * @since 2.3.7
		 */
		public function admin_print_styles() {
			do_action( 'admin_print_styles-widgets.php' );
		}


		/**
		 * Register menu locations created within Max Mega Menu.
		 *
		 * @since 1.8
		 */
		public function register_nav_menus() {

			$locations = get_option( 'megamenu_locations' );

			if ( is_array( $locations ) && count( $locations ) ) {
				foreach ( $locations as $key => $val ) {
					register_nav_menu( $key, $val );
				}
			}
		}


		/**
		 * Black Studio TinyMCE Compatibility.
		 * Load TinyMCE assets on nav-menus.php page.
		 *
		 * @since  1.8
		 * @param  array $pages Pages to load tinymce scripts on
		 * @return array $pages
		 */
		public function megamenu_blackstudio_tinymce( $pages ) {
			$pages[] = 'nav-menus.php';
			return $pages;
		}


		/**
		 * Detect new or updated installations and run actions accordingly.
		 *
		 * @since 1.3
		 */
		public function install_upgrade_check() {
			$version = get_option( 'megamenu_version' );
			$install_date = get_option( 'megamenu_install_date');

			if ( ! $install_date ) {
				add_option( 'megamenu_install_date', time() );
			}

			if ( $version ) {
				if ( version_compare( $this->version, $version, '!=' ) ) {
					update_option( 'megamenu_version', $this->version );
					do_action( 'megamenu_after_update' );
				}
			} else {
				add_option( 'megamenu_version', $this->version );
				add_option( 'megamenu_initial_version', $this->version );
				add_option( 'megamenu_multisite_share_themes', 'false' );

				do_action( 'megamenu_after_install' );

				$settings = get_option( 'megamenu_settings' );

				// set defaults.
				if ( ! $settings ) {
					$defaults['prefix']       = 'disabled';
					$defaults['descriptions'] = 'enabled';
					$defaults['second_click'] = 'go';

					add_option( 'megamenu_settings', $defaults );
				}
			}
		}


		/**
		 * Register widget
		 *
		 * @since 1.7.4
		 */
		public function register_widget() {
			if ( class_exists( 'Mega_Menu_Widget' ) ) {
				register_widget( 'Mega_Menu_Widget' );
			}

			if ( class_exists( 'Mega_Menu_Widget_Reusable_Block' ) ) {
				register_widget( 'Mega_Menu_Widget_Reusable_Block' );
			}

			// Check if Elementor installed and activated
			//if ( did_action( 'elementor/loaded' ) ) {
			//    register_widget( 'Mega_Menu_Widget_Elementor_Template' );
			//}
		}


		/**
		 * Register elementor widget
		 *
		 * @since 3.5
		 * @param object $widgets_manager Elementor widgets manager instance.
		 */
		public function register_elementor_widget( $widgets_manager ) {
			require_once( MEGAMENU_PATH . 'classes/widgets/widget-elementor.class.php' );
			$widgets_manager->register( new \Elementor_Max_Mega_Menu_Widget() );
		}


		/**
		 * Create our own widget area to store all mega menu widgets.
		 * All widgets from all menus are stored here, they are filtered later
		 * to ensure the correct widgets show under the correct menu item.
		 *
		 * @since 1.0
		 */
		public function register_sidebar() {

			register_sidebar(
				[
					'id'          => 'mega-menu',
					'name'        => __( 'Max Mega Menu Widgets', 'megamenu' ),
					'description' => __( 'This is where Max Mega Menu stores widgets that you have added to sub menus using the mega menu builder. You can edit existing widgets here, but new widgets must be added through the Mega Menu interface (under Appearance > Menus).', 'megamenu' ),
				]
			);
		}


		/**
		 * Shortcode used to display a menu.
		 *
		 * @since  1.3
		 * @param array $atts shortcode attributes.
		 * @return string
		 */
		public function register_shortcode( $atts ) {
			if ( ! isset( $atts['location'] ) ) {
				return false;
			}

			if ( has_nav_menu( $atts['location'] ) ) {
				return wp_nav_menu(
					[
						'theme_location' => $atts['location'],
						'echo'           => false,
					]
				);
			}

			return '<!-- menu not found [maxmegamenu] -->';

		}


		/**
		 * Initialise translations
		 *
		 * @since 1.0
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'megamenu', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}


		/**
		 * Define Mega Menu constants
		 *
		 * @since 1.0
		 */
		private function define_constants() {
			define( 'MEGAMENU_VERSION', $this->version );
			define( 'MEGAMENU_BASE_URL', trailingslashit( plugins_url( 'megamenu' ) ) );
			define( 'MEGAMENU_PATH', plugin_dir_path( __FILE__ ) );
		}


		/**
		 * All Mega Menu classes
		 *
		 * @since 1.0
		 * @return array Map of class name to file path.
		 */
		private function plugin_classes() {
			$classes = [
				'Mega_Menu_Location'                  => MEGAMENU_PATH . 'classes/location.class.php',
				'Mega_Menu_Preview'                   => MEGAMENU_PATH . 'classes/preview.class.php',
				'Mega_Menu_Theme'                     => MEGAMENU_PATH . 'classes/theme.class.php',
				'Mega_Menu_Walker'                    => MEGAMENU_PATH . 'classes/walker.class.php',
				'Mega_Menu_Widget_Manager'            => MEGAMENU_PATH . 'classes/widget-manager.class.php',
				'Mega_Menu_Menu_item_Manager'         => MEGAMENU_PATH . 'classes/menu-item-manager.class.php',
				'Mega_Menu_Nav_Menus'                 => MEGAMENU_PATH . 'classes/nav-menus.class.php',
				'Mega_Menu_Style_Manager'             => MEGAMENU_PATH . 'classes/style-manager.class.php',
				'Mega_Menu_Page'                      => MEGAMENU_PATH . 'classes/pages/page.php',
				'Mega_Menu_General'                   => MEGAMENU_PATH . 'classes/pages/general.php',
				'Mega_Menu_Locations'                 => MEGAMENU_PATH . 'classes/pages/locations.php',
				'Mega_Menu_Themes'                    => MEGAMENU_PATH . 'classes/pages/themes.php',
				'Mega_Menu_Tools'                     => MEGAMENU_PATH . 'classes/pages/tools.php',
				'Mega_Menu_Widget'                    => MEGAMENU_PATH . 'classes/widgets/widget.class.php',
				'Mega_Menu_Widget_Reusable_Block'     => MEGAMENU_PATH . 'classes/widgets/widget-reusable-block.class.php',
				'Mega_Menu_Widget_Elementor_Template' => MEGAMENU_PATH . 'classes/widgets/widget-elementor-template.class.php',
				'Mega_Menu_toggle_Blocks'             => MEGAMENU_PATH . 'classes/toggle-blocks.class.php',
				'Mega_Menu_Admin_Notices'             => MEGAMENU_PATH . 'classes/admin-notices.class.php'
			];

			return $classes;
		}


		/**
		 * Load required classes
		 *
		 * @since 1.0
		 * @return void
		 */
		private function includes() {
			foreach ( $this->plugin_classes() as $id => $path ) {
				if ( is_readable( $path ) && ! class_exists( $id ) ) {
					include_once $path;
				}
			}

			$template = get_template();

			if ( 'zerif-pro' === $template ) {
				$template = 'zerif';
			}

			switch ( $template ) {
				case 'twentyseventeen':
				case 'zerif':
					if ( is_readable( MEGAMENU_PATH . "integration/{$template}/functions.php" ) ) {
						include_once MEGAMENU_PATH . "integration/{$template}/functions.php";
					}
				default:
					break;
			}

		// gutenberg block
		include_once MEGAMENU_PATH . 'integration/block/location/block.php';

		require_once MEGAMENU_PATH . 'includes/functions.php';
	}


		/**
		 * Appends "mega-" to all menu classes.
		 * This is to help avoid theme CSS conflicts.
		 *
		 * @since 1.0
		 * @param array  $classes classes for this menu item.
		 * @param object $item The menu item object.
		 * @param object $args Menu arguments.
		 * @return array
		 */
		public function prefix_menu_classes( $classes, $item, $args ) {
			$return = [];

			foreach ( $classes as $class ) {
				$return[] = 'mega-' . $class;
			}

			$location_slug = $args->theme_location;
			$global_settings = get_option( 'megamenu_settings', [] );
			$location        = Mega_Menu_Location::find( $location_slug );

			$prefix = isset( $global_settings['prefix'] ) ? $global_settings['prefix'] : 'enabled';

			if ( $location ) {
				$location_settings = $location->get_settings();
				if ( isset( $location_settings['prefix'] ) ) {
					$prefix = $location_settings['prefix'];
				}
			}

			if ( 'disabled' === $prefix ) {
				// add in custom classes, sans 'mega-' prefix.
				foreach ( $classes as $class ) {

					// custom classes are added before the 'menu-item' class.
					if ( 'menu-item' === $class ) {
						break;
					}

					if ( in_array( $class, [ 'menu-column', 'menu-row', 'hide-on-mobile', 'hide-on-desktop' ], true ) ) { // these are always prefixed.
						continue;
					}

					if ( strpos( $class, 'menu-columns-' ) !== false ) { // mega-menu-columns-X-of-Y are always prefixed.
						continue;
					}

					$return[] = $class;
				}
			}

			return $return;
		}

		/**
		 * Remove the current menu item classes when a custom class of 'never-highlight' has been added to the menu item.
		 *
		 * @since  1.0
		 * @param  array  $classes CSS classes for this menu item.
		 * @param  object $item    The menu item object.
		 * @param  object $args    Menu arguments.
		 * @return array
		 */
		public function css_classes_never_highlight( $classes, $item, $args ) {
			if ( in_array( 'mega-never-highlight', $classes ) ) {
				if ( in_array( 'mega-current-menu-ancestor', $classes ) ) {
					$classes = array_diff( $classes, [ 'mega-current-menu-ancestor' ] );
				}

				if ( in_array( 'mega-current-menu-item', $classes ) ) {
					$classes = array_diff( $classes, [ 'mega-current-menu-item' ] );
				}

				if ( in_array( 'mega-current-page-ancestor', $classes ) ) {
					$classes = array_diff( $classes, [ 'mega-current-page-ancestor' ] );
				}
			}

			return $classes;
		}


		/**
		 * Add the html for the responsive toggle box to the menu.
		 *
		 * @since  1.3
		 * @param  array  $args          wp_nav_menu arguments.
		 * @param  array  $menu_settings Settings for the current menu location.
		 * @param  array  $menu_theme    Theme settings for the current menu.
		 * @return string
		 */
		public function get_mobile_toggle_bar( $args, $menu_settings, $menu_theme ) {
			$theme_id = isset( $menu_settings['theme'] ) ? $menu_settings['theme'] : 'default';
			$content = '';
			$nav_menu = '';

			$content = apply_filters( 'megamenu_toggle_bar_content', $content, $nav_menu, $args, $theme_id );

			return '<div class="mega-menu-toggle">' . $content . '</div>';
		}


		/**
		 * Return the html for mobile menu close button.
		 *
		 * @since  3.4
		 * @param  array  $args          wp_nav_menu arguments.
		 * @param  array  $menu_settings Settings for the current menu location.
		 * @param  array  $menu_theme    Theme settings for the current menu.
		 * @return string
		 */
		public function get_mobile_close_button( $args, $menu_settings, $menu_theme ) {
		    // Retrieve CSS version
		    $css_version = Mega_Menu_Style_Manager::get_css_version();

		    // Only proceed if CSS version is 3.4 or higher
		    if ( ! $css_version || version_compare( $css_version, '3.3.3', '<' ) ) {
		        return "";
		    }

		    // Check if mobile effect is set and is either 'slide_left' or 'slide_right'
		    if ( isset( $menu_settings['effect_mobile'] ) && in_array( $menu_settings['effect_mobile'], ['slide_left', 'slide_right'], true ) ) {

		    	$label = esc_attr( do_shortcode( $menu_theme['close_icon_label'] ) );

				$html = "<button class='mega-close'></button>";

				$processor = new WP_HTML_Tag_Processor( $html );

				if ( $processor->next_tag( 'button' ) ) {
				    $processor->set_attribute( 'aria-controls', 'mega-menu-' . $args['theme_location'] );
				    $processor->set_attribute( 'aria-label', $label );
				}

				$button_html = $processor->get_updated_html();

				return apply_filters( "megamenu_close_button", $button_html, $args, $menu_settings );
		    }

		    return "";
		}


		/**
		 * Append the widget objects to the menu array before the
		 * menu is processed by the walker.
		 *
		 * @since  1.0
		 * @param  array  $items - All menu item objects.
		 * @param  object $args wp_nav_menu arguments.
		 * @return array - Menu objects including widgets
		 */
		public function add_widgets_to_menu( $items, $args ) {

			$args = (object) $args;

			// make sure we're working with a Mega Menu.
			if ( ! $args->walker || ! is_a( $args->walker, 'Mega_Menu_Walker' ) ) {
				return $items;
			}

			$items = apply_filters( 'megamenu_nav_menu_objects_before', $items, $args );

			$widget_manager = new Mega_Menu_Widget_Manager();

			$rolling_dummy_id = 999999999;

			$items_to_move = [];

			foreach ( $items as $item ) {

				// populate standard (non-grid) sub menus.
				if ( property_exists( $item, 'depth' ) && 0 === $item->depth && 'megamenu' === $item->megamenu_settings['type'] || ( property_exists( $item, 'depth' ) && 1 === $item->depth && 'tabbed' === $item->parent_submenu_type && 'grid' !== $item->megamenu_settings['type'] ) ) {

					$panel_widgets = $widget_manager->get_widgets_for_menu_id( $item->ID, $args->menu );

					if ( ! in_array( 'menu-megamenu', $item->classes, true ) ) {
						$item->classes[] = 'menu-megamenu';
					}

					if ( count( $panel_widgets ) ) {

						$widget_position       = 0;
						$total_widgets_in_menu = count( $panel_widgets );
						$next_order            = $this->menu_order_of_next_sibling( $item->ID, $item->menu_item_parent, $items );

						if ( ! in_array( 'menu-item-has-children', $item->classes, true ) ) {
							$item->classes[] = 'menu-item-has-children';
						}

						foreach ( $panel_widgets as $widget ) {
							$widget_settings = array_merge(
								Mega_Menu_Nav_Menus::get_menu_item_defaults(),
								[
									'mega_menu_columns' => absint( $widget['columns'] ),
								]
							);

							$menu_item = [
								'type'                => 'widget',
								'parent_submenu_type' => 'megamenu',
								'title'               => $widget['id'],
								'content'             => $widget_manager->show_widget( $widget['id'] ),
								'menu_item_parent'    => $item->ID,
								'db_id'               => 0,
								'url'                 => '',
								'ID'                  => $widget['id'],
								'menu_order'          => $next_order - $total_widgets_in_menu + $widget_position,
								'megamenu_order'      => $widget['order'],
								'megamenu_settings'   => $widget_settings,
								'depth'               => 1,
								'classes'             => [
									'menu-item',
									'menu-item-type-widget',
									'menu-widget-class-' . $widget_manager->get_widget_class( $widget['id'] ),
								],
							];

							$items[] = (object) $menu_item;

							$widget_position++;
						}
					}
				}

				// populate grid sub menus.
				if ( property_exists( $item, 'depth' ) && 0 === $item->depth && 'grid' === $item->megamenu_settings['type'] || ( property_exists( $item, 'depth' ) && 1 === $item->depth && 'tabbed' === $item->parent_submenu_type && 'grid' === $item->megamenu_settings['type'] ) ) {

					$saved_grid = $widget_manager->get_grid_widgets_and_menu_items_for_menu_id( $item->ID, $args->menu->term_id, $items );

					$next_order = $this->menu_order_of_next_sibling( $item->ID, $item->menu_item_parent, $items ) - 999;

					foreach ( $saved_grid as $row => $row_data ) {

						$rolling_dummy_id++;
						$next_order++;

						if ( isset( $row_data['columns'] ) ) {

							if ( ! in_array( 'menu-item-has-children', $item->classes, true ) ) {
								$item->classes[] = 'menu-item-has-children';
							}

							if ( ! in_array( 'menu-megamenu', $item->classes, true ) ) {
								$item->classes[] = 'menu-megamenu';
							}

							if ( ! in_array( 'menu-grid', $item->classes, true ) ) {
								$item->classes[] = 'menu-grid';
							}

							$classes = [ 'menu-row'];

							if ( isset( $row_data['meta']['class'] ) ) {
								$classes = array_merge( $classes, array_unique( explode( ' ', $row_data['meta']['class'] ) ) );
							}

							if ( isset( $row_data['meta']['columns'] ) ) {
								$row_columns = $row_data['meta']['columns'];
							} else {
								$row_columns = 12;
							}

							$styles = ['--columns:' . $row_columns];

							$row_item = [
								'menu_item_parent'    => $item->ID,
								'type'                => 'mega_row',
								'title'               => 'Custom Row',
								'parent_submenu_type' => '',
								'menu_order'          => $next_order,
								'depth'               => 0,
								'ID'                  => "{$item->ID}-{$row}",
								'megamenu_settings'   => Mega_Menu_Nav_Menus::get_menu_item_defaults(),
								'db_id'               => $rolling_dummy_id,
								'url'                 => '',
								'classes'             => $classes,
								'styles'              => $styles
							];

							$items[] = (object) $row_item;

							$row_dummy_id = $rolling_dummy_id;

							foreach ( $row_data['columns'] as $col => $col_data ) {

								$rolling_dummy_id++;
								$next_order++;

								$classes = [ 'menu-column' ];
								$styles = [];

								if ( isset( $col_data['meta']['class'] ) ) {
									$classes = array_merge( $classes, array_unique( explode( ' ', $col_data['meta']['class'] ) ) );
								}

								if ( isset( $row_data['meta']['columns'] ) ) {
									$row_columns = $row_data['meta']['columns'];
								} else {
									$row_columns = 12;
								}

								$styles[] = "--columns:" . $row_columns;

								if ( isset( $col_data['meta']['span'] ) ) {
									$classes[] = "menu-columns-{$col_data['meta']['span']}-of-{$row_columns}";
									$styles[] = "--span:" . $col_data['meta']['span'];
								}

								if ( isset( $col_data['meta']['hide-on-mobile'] ) && 'true' === $col_data['meta']['hide-on-mobile'] ) {
									$classes[] = 'hide-on-mobile';
								}

								if ( isset( $col_data['meta']['hide-on-mobile'] ) && 'true' === $col_data['meta']['hide-on-desktop'] ) {
									$classes[] = 'hide-on-desktop';
								}

								$col_item = [
									'menu_item_parent'    => $row_dummy_id,
									'type'                => 'mega_column',
									'title'               => 'Custom Column',
									'parent_submenu_type' => '',
									'menu_order'          => $next_order,
									'depth'               => 0,
									'ID'                  => "{$item->ID}-{$row}-{$col}",
									'megamenu_settings'   => Mega_Menu_Nav_Menus::get_menu_item_defaults(),
									'db_id'               => $rolling_dummy_id,
									'url'                 => '',
									'classes'             => $classes,
									'styles'              => $styles
								];

								$items[] = (object) $col_item;

								if ( isset( $col_data['items'] ) ) {

									foreach ( $col_data['items'] as $key => $block ) {

										$next_order++;

										if ( 'widget' === $block['type'] ) {

											$widget_settings = array_merge( Mega_Menu_Nav_Menus::get_menu_item_defaults() );

											$menu_item = [
												'type'					=> 'widget',
												'parent_submenu_type'	=> '',
												'title'					=> $block['id'],
												'content'				=> $widget_manager->show_widget( $block['id'] ),
												'menu_item_parent'		=> $rolling_dummy_id,
												'db_id'					=> 0,
												'url'					=> '',
												'ID'					=> $block['id'],
												'menu_order'			=> $next_order,
												'megamenu_order'		=> 0,
												'megamenu_settings'		=> $widget_settings,
												'depth'					=> 1,
												'classes'				=> [
													'menu-item',
													'menu-item-type-widget',
													'menu-widget-class-' . $widget_manager->get_widget_class( $block['id'] ),
												],
											];

											$items[] = (object) $menu_item;

										} else {
											// mark this menu item to be moved into a new position.
											$items_to_move[ $block['id'] ] = [
												'new_parent' => $rolling_dummy_id,
												'new_order'  => $next_order,
											];
										}
									}
								}
							}
						}
					}
				}
			}

			if ( count( $items_to_move ) ) {
				foreach ( $items_to_move as $id => $new_parent ) {
					$items_to_find[] = $id;
				}

				foreach ( $items as $item ) {
					if ( in_array( $item->ID, $items_to_find, true ) ) {
						$item->menu_item_parent = $items_to_move[ $item->ID ]['new_parent'];
						$item->menu_order       = $items_to_move[ $item->ID ]['new_order'];
					}
				}
			}

			$items = apply_filters( 'megamenu_nav_menu_objects_after', $items, $args );

			return $items;
		}


		/**
		 * Return the menu order of the next sibling menu item.
		 * Eg, given A as the $item_id, the menu order of D will be returned
		 * Eg, given B as the $item_id, the menu order of C will be returned
		 * Eg, given D as the $item_id, the menu order of D + 1000 will be returned
		 *
		 * - A
		 * --- B
		 * --- C
		 * - D
		 *
		 * @since  2.0
		 * @param  int   $item_id ID of the menu item.
		 * @param  int   $menu_item_parent ID of the parent menu item.
		 * @param  array $items Array of all menu item objects.
		 * @return int
		 */
		private function menu_order_of_next_sibling( $item_id, $menu_item_parent, $items ) {

			$get_order_of_next_item = false;

			foreach ( $items as $key => $item ) {

				if ( $menu_item_parent !== $item->menu_item_parent ) {
					continue;
				}

				if ( 'widget' === $item->type ) {
					continue;
				}

				if ( $get_order_of_next_item ) {
					return $item->menu_order;
				}

				if ( $item->ID === $item_id ) {
					$get_order_of_next_item = true;
				}

				if ( isset( $item->menu_order ) ) {
					$rolling_last_menu_order = $item->menu_order;
				}
			}

			// there isn't a next sibling.
			return $rolling_last_menu_order + 1000;

		}


		/**
		 * Determine if menu item is a top level item or a second level item
		 *
		 * @since  2.7.7
		 * @param  array  $items - All menu item objects.
		 * @param  object $args wp_nav_menu arguments.
		 * @return array
		 */
		public function apply_depth_to_menu_items( $items, $args ) {
			$parents = [];

			foreach ( $items as $key => $item ) {
				if ( $item->menu_item_parent == 0 ) {
					$parents[]   = $item->ID;
					$item->depth = 0;
				}
			}

			if ( count( $parents ) ) {
				foreach ( $items as $key => $item ) {
					if ( in_array( $item->menu_item_parent, $parents ) ) {
						$item->depth = 1;
					}
				}
			}

			return $items;
		}


		/**
		 * Setup the mega menu settings for each menu item
		 *
		 * @since  2.0
		 * @param  array  $items - All menu item objects.
		 * @param  object $args wp_nav_menu arguments.
		 * @return array
		 */
		public function setup_menu_items( $items, $args ) {

			// apply saved metadata to each menu item.
			foreach ( $items as $item ) {
				$saved_settings = array_filter( (array) get_post_meta( $item->ID, '_megamenu', true ) );

				$item->megamenu_settings   = array_merge( Mega_Menu_Nav_Menus::get_menu_item_defaults(), $saved_settings );
				$item->megamenu_order      = isset( $item->megamenu_settings['mega_menu_order'][ $item->menu_item_parent ] ) ? $item->megamenu_settings['mega_menu_order'][ $item->menu_item_parent ] : 0;
				$item->parent_submenu_type = 'flyout';

				if ( isset( $item->menu_order ) ) {
					$item->menu_order = $item->menu_order * 1000;
				}

				// add parent mega menu type.
				if ( property_exists( $item, 'depth' ) && absint( $item->depth ) === 1 ) {
					$parent_settings = array_filter( (array) get_post_meta( $item->menu_item_parent, '_megamenu', true ) );

					if ( isset( $parent_settings['type'] ) ) {
						$item->parent_submenu_type = $parent_settings['type'];
					}
				}
			}

			return $items;
		}


		/**
		 * Reorder items within the mega menu.
		 *
		 * @since  2.0
		 * @param  array  $items - All menu item objects.
		 * @param  object $args wp_nav_menu arguments.
		 * @return array
		 */
		public function reorder_menu_items_within_megamenus( $items, $args ) {
			$new_items = [];

			// reorder menu items within mega menus based on internal ordering.
			foreach ( $items as $item ) {
				// items ordered with 'forced' ordering.
				if ( property_exists( $item, 'parent_submenu_type' ) && 'megamenu' === $item->parent_submenu_type && property_exists( $item, 'megamenu_order' ) && 0 !== $item->megamenu_order ) {
					if ( $parent_menu_item = get_post( $item->menu_item_parent ) ) {
						$item->menu_order = $parent_menu_item->menu_order * 1000 + $item->megamenu_order;
					}
				}
			}

			foreach ( $items as $item ) {
				if ( in_array( 'wpml-ls-item', $item->classes, true ) ) {
					$item->classes[] = 'menu-flyout';
				}

				$new_items[ $item->menu_order ] = $item;
			}

			ksort( $new_items );

			return $new_items;
		}


		/**
		 * If descriptions are enabled, create a new 'mega_description' property.
		 * This is for backwards compatibility for users who have used filters
		 * to display descriptions
		 *
		 * @since  2.3
		 * @param  array  $items All menu item objects.
		 * @param  object $args wp_nav_menu arguments.
		 * @return array
		 */
		public function set_descriptions_if_enabled( $items, $args ) {

			$location_slug   = $args->theme_location;
			$global_settings = get_option( 'megamenu_settings', [] );
			$location        = Mega_Menu_Location::find( $location_slug );

			$descriptions = isset( $global_settings['descriptions'] ) ? $global_settings['descriptions'] : 'disabled';

			if ( $location ) {
				$location_settings = $location->get_settings();
				if ( isset( $location_settings['descriptions'] ) ) {
					$descriptions = $location_settings['descriptions'];
				}
			}

			if ( 'enabled' === $descriptions ) {
				foreach ( $items as $item ) {
					if ( property_exists( $item, 'description' ) && is_string( $item->description ) && strlen( $item->description ) ) {
						$item->mega_description = $item->description;
						$item->classes[]        = 'has-description';
					}
				}
			}

			return $items;
		}


		/**
		 * Apply column and clear classes to menu items (inc. widgets)
		 *
		 * @since  2.0
		 * @param  array  $items All menu item objects.
		 * @param  object $args wp_nav_menu arguments.
		 * @return array
		 */
		public function apply_classes_to_menu_items( $items, $args ) {
			$parents = [];

			foreach ( $items as $item ) {

				if ( ! in_array( 'menu-row', $item->classes, true ) && ! in_array( 'menu-column', $item->classes, true ) ) {
					if ( property_exists( $item, 'depth' ) && 0 === $item->depth ) {
						$item->classes[] = 'align-' . $item->megamenu_settings['align'];
						$item->classes[] = 'menu-' . $item->megamenu_settings['type'];
					}

					if ( 'true' === $item->megamenu_settings['hide_arrow'] ) {
						$item->classes[] = 'hide-arrow';
					}

					if ( 'disabled' !== $item->megamenu_settings['icon'] ) {
						$item->classes[] = 'has-icon';
					}

					if ( 'disabled' !== $item->megamenu_settings['icon'] && isset( $item->megamenu_settings['icon_position'] ) ) {
						$item->classes[] = 'icon-' . $item->megamenu_settings['icon_position'];
					}

					if ( 'true' === $item->megamenu_settings['hide_text'] && 0 === $item->depth ) {
						$item->classes[] = 'hide-text';
					}

					if ( 'left' !== $item->megamenu_settings['item_align'] && 0 === $item->depth ) {
						$item->classes[] = 'item-align-' . $item->megamenu_settings['item_align'];
					}

					if ( 'true' === $item->megamenu_settings['hide_on_desktop'] ) {
						$item->classes[] = 'hide-on-desktop';
					}

					if ( 'true' === $item->megamenu_settings['hide_on_mobile'] ) {
						$item->classes[] = 'hide-on-mobile';
					}

					if ( 'true' === $item->megamenu_settings['close_after_click'] ) {
						$item->classes[] = 'close-after-click';
					}

					if ( 'true' === $item->megamenu_settings['hide_sub_menu_on_mobile'] ) {
						$item->classes[] = 'hide-sub-menu-on-mobile';
					}

					if ( 'true' === $item->megamenu_settings['disable_link'] ) {
						$item->classes[] = 'disable-link';
					}

					if ( 'true' === $item->megamenu_settings['collapse_children'] && $item->parent_submenu_type !== 'tabbed' ) {
						$item->classes[] = 'collapse-children';
					}

					if ( absint( $item->megamenu_settings['submenu_columns'] ) > 1 ) {
						$item->classes[] = absint( $item->megamenu_settings['submenu_columns'] ) . '-columns';
					}
				}

				// add column classes for second level menu items displayed in mega menus.
				if ( 'megamenu' === $item->parent_submenu_type ) {

					$parent_settings = array_filter( (array) get_post_meta( $item->menu_item_parent, '_megamenu', true ) );
					$parent_settings = array_merge( Mega_Menu_Nav_Menus::get_menu_item_defaults(), $parent_settings );

					$item->classes[] = "menu-column-standard";

					$span = $item->megamenu_settings['mega_menu_columns'];

					$total_columns = $parent_settings['panel_columns'];

					if ( $total_columns >= $span ) {
						$item->classes[] = "menu-columns-{$span}-of-{$total_columns}";
						$item->styles    = ["--columns:{$total_columns}","--span:{$span}"];
						$column_count    = $span;
					} else {
						$item->classes[] = "menu-columns-{$total_columns}-of-{$total_columns}";
						$item->styles    = ["--columns:{$total_columns}","--span:{$total_columns}"];
						$column_count    = $total_columns;
					}

					if ( ! isset( $parents[ $item->menu_item_parent ] ) ) {
						$parents[ $item->menu_item_parent ] = $column_count;
					} else {
						$parents[ $item->menu_item_parent ] = $parents[ $item->menu_item_parent ] + $column_count;

						if ( $parents[ $item->menu_item_parent ] > $total_columns ) {
							$parents[ $item->menu_item_parent ] = $column_count;
							$item->classes[]                    = 'menu-clear';
						}
					}
				}
			}

			return $items;
		}


		/**
		 * Use the Mega Menu walker to output the menu.
		 * Resets all parameters used in the wp_nav_menu call.
		 * Wraps the menu in mega-menu IDs and classes.
		 *
		 * @since  1.0
		 * @param  array $args wp_nav_menu arguments.
		 * @return array Modified wp_nav_menu arguments.
		 */
		public function modify_nav_menu_args( $args ) {

			if ( ! isset( $args['theme_location'] ) ) {
				return $args;
			}

			// internal action to use as a counter.
			do_action( 'megamenu_instance_counter_' . $args['theme_location'] );

			$num_times_called       = did_action( 'megamenu_instance_counter_' . $args['theme_location'] );
			$current_theme_location = $args['theme_location'];
			$location               = Mega_Menu_Location::find( $current_theme_location );

			if ( ! $location ) {
				return $args;
			}

			$location_settings = $location->get_settings();

			// Global settings still needed for keys that have no per-location equivalent.
			$global_settings = get_option( 'megamenu_settings', [] );

			$active_instance = isset( $location_settings['active_instance'] )
				? $location_settings['active_instance']
				: ( isset( $global_settings['instances'][ $current_theme_location ] ) ? $global_settings['instances'][ $current_theme_location ] : 0 );

			if ( $active_instance != '0' && strlen( $active_instance ) ) {
				if ( strpos( $active_instance, ',' ) || is_numeric( $active_instance ) ) {
					$active_instances = explode( ',', $active_instance );

					if ( ! in_array( $num_times_called, $active_instances ) ) {
						return $args;
					}
				} elseif ( isset( $args['container_id'] ) && $active_instance != $args['container_id'] ) {
					return $args;
				}
			}

			if ( ! $location->is_enabled() ) {
				return $args;
			}

			$menu_id = $location->get_menu_id();

			if ( ! $menu_id ) {
				return $args;
			}

			$theme = Mega_Menu_Theme::find( $location->get_theme_id() );

			$effect              = isset( $location_settings['effect'] ) ? $location_settings['effect'] : 'disabled';
			$effect_mobile       = isset( $location_settings['effect_mobile'] ) ? $location_settings['effect_mobile'] : 'disabled';
			$effect_speed_mobile = 'disabled' !== $effect_mobile && isset( $location_settings['effect_speed_mobile'] ) ? $location_settings['effect_speed_mobile'] : 0;
			$container           = isset( $location_settings['container'] ) ? $location_settings['container'] : 'div';
			$mobile_state        = isset( $location_settings['mobile_state'] ) ? $location_settings['mobile_state'] : 'collapse_all';

			// Per-location overrides fall back to global defaults.
			$vertical_behaviour = isset( $location_settings['mobile_behaviour'] )
				? $location_settings['mobile_behaviour']
				: ( isset( $global_settings['mobile_behaviour'] ) ? $global_settings['mobile_behaviour'] : 'standard' );

			$second_click = isset( $location_settings['second_click'] )
				? $location_settings['second_click']
				: ( isset( $global_settings['second_click'] ) ? $global_settings['second_click'] : 'go' );

			$unbind = isset( $location_settings['unbind'] )
				? $location_settings['unbind']
				: ( isset( $global_settings['unbind'] ) ? $global_settings['unbind'] : 'enabled' );

			$event = 'hover_intent';
			if ( isset( $location_settings['event'] ) ) {
				if ( 'hover' === $location_settings['event'] ) {
					$event = 'hover_intent';
				} elseif ( 'hover_' === $location_settings['event'] ) {
					$event = 'hover';
				} else {
					$event = $location_settings['event'];
				}
			}

			$mobile_force_width = 'false';
			if ( 'on' === $theme->get( 'mobile_menu_force_width' ) ) {
				$mobile_force_width = $theme->get( 'mobile_menu_force_width_selector', 'body' );
			}

			$hover_intent_defaults = [
				'timeout'  => 300,
				'interval' => 100,
			];

			$hover_intent_params = apply_filters(
				'megamenu_javascript_localisation', // backwards compatibility.
				$hover_intent_defaults
			);

			$wrap_attributes = apply_filters(
				'megamenu_wrap_attributes',
				[
					'id'                         => '%1$s',
					'class'                      => '%2$s mega-no-js',
					'data-event'                 => $event,
					'data-effect'                => $effect,
					'data-effect-speed'          => isset( $location_settings['effect_speed'] ) ? $location_settings['effect_speed'] : '200',
					'data-effect-mobile'         => $effect_mobile,
					'data-effect-speed-mobile'   => $effect_speed_mobile,
					'data-panel-width'           => (
						preg_match( '/^\d/', $theme->get( 'panel_width' ) ) !== 1 ||
						preg_match( '/^\d+(vw|vh|vmin|vmax)$/', $theme->get( 'panel_width' ) ) === 1
					) ? $theme->get( 'panel_width' ) : '',
					'data-panel-inner-width'     => substr( trim( $theme->get( 'panel_inner_width' ) ), -1 ) !== '%' ? $theme->get( 'panel_inner_width' ) : '',
					'data-mobile-force-width'    => $mobile_force_width,
					'data-second-click'          => $second_click,
					'data-document-click'        => 'collapse',
					'data-vertical-behaviour'    => $vertical_behaviour,
					'data-breakpoint'            => absint( $theme->get( 'responsive_breakpoint' ) ),
					'data-unbind'                => 'disabled' === $unbind ? 'false' : 'true',
					'data-mobile-state'          => $mobile_state,
					'data-mobile-direction'      => 'vertical',
					'data-hover-intent-timeout'  => absint( $hover_intent_params['timeout'] ) !== $hover_intent_defaults['timeout'] ? absint( $hover_intent_params['timeout'] ) : '',
					'data-hover-intent-interval' => absint( $hover_intent_params['interval'] ) !== $hover_intent_defaults['interval'] ? absint( $hover_intent_params['interval'] ) : '',
				],
				$menu_id,
				$location_settings,
				$global_settings,
				$current_theme_location
			);

			$attributes = '';

			foreach ( $wrap_attributes as $attribute => $value ) {
				if ( strlen( $value ) ) {
					$attributes .= ' ' . $attribute . '="' . esc_attr( $value ) . '"';
				}
			}

			$sanitized_location = str_replace( apply_filters( 'megamenu_location_replacements', [ '-', ' ' ] ), '-', $current_theme_location );

			$close_button = $this->get_mobile_close_button( $args, $location_settings, $theme->settings );
			$toggle_bar   = $this->get_mobile_toggle_bar( $args, $location_settings, $theme->settings );
			$toggle_bar   = str_replace( '%', '%%', $toggle_bar );

			$defaults = [
				'menu'            => wp_get_nav_menu_object( $menu_id ),
				'container'       => $container,
				'container_class' => 'mega-menu-wrap',
				'container_id'    => 'mega-menu-wrap-' . $sanitized_location,
				'menu_class'      => 'mega-menu max-mega-menu mega-menu-horizontal',
				'menu_id'         => 'mega-menu-' . $sanitized_location,
				'fallback_cb'     => 'wp_page_menu',
				'before'          => '',
				'after'           => '',
				'link_before'     => '',
				'link_after'      => '',
				'items_wrap'      => $toggle_bar . '<ul' . $attributes . '>%3$s</ul>' . $close_button,
				'depth'           => 0,
				'walker'          => new Mega_Menu_Walker(),
			];

			$args = array_merge( $args, apply_filters( 'megamenu_nav_menu_args', $defaults, $menu_id, $current_theme_location ) );

			return $args;
		}


		/**
		 * Display admin notices.
		 *
		 * @since 1.3
		 */
		public function admin_notices() {

			if ( ! $this->is_compatible_wordpress_version() ) :

				?>
			<div class="error">
				<p><?php esc_html_e( 'Max Mega Menu is not compatible with your version of WordPress. Please upgrade WordPress to the latest version or disable Max Mega Menu.', 'megamenu' ); ?></p>
			</div>
				<?php

			endif;

			if ( did_action( 'megamenu_after_install' ) === 1 ) :

				?>

				<?php

			endif;

			if ( defined( 'MEGAMENU_HIDE_CSS_NAG' ) && MEGAMENU_HIDE_CSS_NAG === true ) {
				return;
			}

			$css_version = Mega_Menu_Style_Manager::get_css_version();
			$css         = get_transient( 'megamenu_css' );

			if ( $css && version_compare( $this->scss_last_updated, $css_version, '>' ) ) :

				?>
			<div class="updated">
				<?php

				$clear_cache_url = esc_url(
					add_query_arg(
						[
							'action' => 'megamenu_clear_css_cache',
						],
						wp_nonce_url( admin_url( 'admin-post.php' ), 'megamenu_clear_css_cache' )
					)
				);

				$link_processor = new WP_HTML_Tag_Processor( '<a>' . __( 'clear the CSS cache', 'megamenu' ) . '</a>' );
				if ( $link_processor->next_tag( 'a' ) ) {
					$link_processor->set_attribute( 'href', $clear_cache_url );
				}
				$link = $link_processor->get_updated_html();

				$allowed_html = [
					'a' => [
						'href'  => [],
						'title' => [],
					],
				];

				?>

				<p>
					<?php
						printf(
							/* translators: %s is the link to clear the menu CSS cache */
							esc_html__( 'Max Mega Menu has been updated. Please %s to ensure maximum compatibility with the latest version.', 'megamenu' ),
							wp_kses( $link, $allowed_html )
						);
					?>
				</p>
			</div>
				<?php

			endif;
		}


		/**
		 * Checks this WordPress installation is v3.8 or above.
		 * 3.8 is needed for dashicons.
		 *
		 * @since  1.0
		 * @return bool
		 */
		public function is_compatible_wordpress_version() {
			global $wp_version;

			return $wp_version >= 3.8;
		}

		/**
		 * Add compatibility for conditional menus plugin.
		 *
		 * @since  2.2
		 * @param  string $location  The current theme location.
		 * @param  array  $new_args  New wp_nav_menu arguments.
		 * @param  array  $old_args  Original wp_nav_menu arguments.
		 * @return string
		 */
		public function conditional_menus_restore_theme_location( $location, $new_args, $old_args ) {
			return $old_args['theme_location'];
		}

		/**
		 * Add a note to the Navigation Widget to explain that Max Mega Menu will not work with it.
		 *
		 * @since  2.5.1
		 * @param  WP_Widget $widget   The widget instance.
		 * @param  null      $return   Return value (unused by WordPress core).
		 * @param  array     $instance Current widget instance settings.
		 */
		public function add_notice_to_nav_menu_widget( $widget, $return, $instance ) {
			if ( 'nav_menu' === $widget->id_base ) {
				$doc_link_processor = new WP_HTML_Tag_Processor( '<a>' . esc_html__( 'More information', 'megamenu' ) . '</a>' );
				if ( $doc_link_processor->next_tag( 'a' ) ) {
					$doc_link_processor->set_attribute( 'href', 'https://www.megamenu.com/documentation/widget/' );
					$doc_link_processor->set_attribute( 'target', '_blank' );
					$doc_link_processor->set_attribute( 'rel', 'noopener noreferrer' );
				}
				?>
					<p style="font-size: 11px; font-style: italic;">
						<?php esc_html_e( "If you are trying to display Max Mega Menu here, use the 'Max Mega Menu' widget instead.", 'megamenu' ); ?>
						<?php echo wp_kses( $doc_link_processor->get_updated_html(), [ 'a' => [ 'href' => true, 'target' => true, 'rel' => true ] ] ); ?>
					</p>
				<?php
			}
		}

	}

	add_action( 'plugins_loaded', [ 'Mega_Menu', 'init' ], 10 );

endif;

