<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Nav_Menus' ) ) :
	/**
	 * Handles all nav-menus.php admin-side functionality, including the meta box,
	 * script enqueueing, and SiteOrigin Page Builder compatibility.
	 *
	 * @since   1.0
	 * @package MegaMenu
	 */
	class Mega_Menu_Nav_Menus {

		/**
		 * Return the default settings for each menu item.
		 *
		 * @since 1.5
		 * @return array Default menu item meta values.
		 */
		public static function get_menu_item_defaults() {

			$defaults = [
				'type'                    => 'flyout',
				'align'                   => 'bottom-left',
				'icon'                    => 'disabled',
				'hide_text'               => 'false',
				'disable_link'            => 'false',
				'hide_on_mobile'          => 'false',
				'hide_on_desktop'         => 'false',
				'close_after_click'       => 'false',
				'hide_sub_menu_on_mobile' => 'false',
				'hide_arrow'              => 'false',
				'item_align'              => 'left',
				'icon_position'           => 'left',
				'panel_columns'           => 6, // total number of columns displayed in the panel.
				'mega_menu_columns'       => 1, // for sub menu items, how many columns to span in the panel.
				'mega_menu_order'         => 0,
				'collapse_children'       => 'false',
				'submenu_columns'         => 1,
			];

			return apply_filters( 'megamenu_menu_item_defaults', $defaults );

		}


		/**
		 * Constructor. Registers actions and filters for the nav-menus admin page.
		 *
		 * @since 1.0
		 */
		public function __construct() {
			add_action( 'admin_init', [ $this, 'register_nav_meta_box' ], 9 );
			add_action( 'megamenu_nav_menus_scripts', [ $this, 'enqueue_menu_page_scripts' ], 10 );
			add_action( 'admin_footer', [ $this, 'maybe_print_menu_item_dialog_markup' ], 5 );
			add_filter( 'hidden_meta_boxes', [ $this, 'show_mega_menu_metabox' ] );

			add_filter( 'siteorigin_panels_is_admin_page', [ $this, 'enable_site_origin_page_builder' ] );

			if ( function_exists( 'siteorigin_panels_admin_enqueue_scripts' ) ) {
				add_action( 'admin_print_scripts-nav-menus.php', [ $this, 'siteorigin_panels_admin_enqueue_scripts' ] );
			}

			if ( function_exists( 'siteorigin_panels_admin_enqueue_styles' ) ) {
				add_action( 'admin_print_styles-nav-menus.php', [ $this, 'siteorigin_panels_admin_enqueue_styles' ] );
			}

		}


		/**
		 * Enable SiteOrigin Page Builder scripts on the nav-menus page.
		 *
		 * @since 2.3.7
		 * @param bool $enabled Whether the Page Builder scripts should be loaded.
		 * @return bool True on the nav-menus screen, otherwise the original value.
		 */
		public function enable_site_origin_page_builder( $enabled ) {
			$screen = get_current_screen();

			if ( 'nav-menus' === $screen->base ) {
				return true;
			}

			return $enabled;
		}

		/**
		 * Enqueue SiteOrigin Page Builder scripts on the nav-menus page.
		 *
		 * @since 1.9
		 * @return void
		 */
		public function siteorigin_panels_admin_enqueue_scripts() {
			siteorigin_panels_admin_enqueue_scripts( '', true );
		}


		/**
		 * Enqueue SiteOrigin Page Builder styles on the nav-menus page.
		 *
		 * @since 1.9
		 * @return void
		 */
		public function siteorigin_panels_admin_enqueue_styles() {
			siteorigin_panels_admin_enqueue_styles( '', true );
		}


		/**
		 * By default the mega menu meta box is hidden — show it.
		 *
		 * @since 1.0
		 * @param array $hidden Meta box IDs that are hidden on the nav-menus.php page.
		 * @return array Updated array with the mega menu meta box removed from the hidden list.
		 */
		public function show_mega_menu_metabox( $hidden ) {

			if ( is_array( $hidden ) && count( $hidden ) > 0 ) {
				foreach ( $hidden as $key => $value ) {
					if ( 'mega_menu_meta_box' === $value ) {
						unset( $hidden[ $key ] );
					}
					if ( 'add-product_cat' === $value ) {
						unset( $hidden[ $key ] );
					}
					if ( 'add-product_tag' === $value ) {
						unset( $hidden[ $key ] );
					}
				}
			}

			return $hidden;
		}


		/**
		 * Adds the Max Mega Menu settings meta box to the nav-menus.php page.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function register_nav_meta_box() {
			global $pagenow;

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			if ( 'nav-menus.php' === $pagenow ) {
				add_meta_box(
					'mega_menu_meta_box',
					__( 'Max Mega Menu Locations', 'megamenu' ),
					[ $this, 'metabox_contents' ],
					'nav-menus',
					'side',
					'high'
				);
			}
		}


		/**
		 * Print the menu item settings modal shell on Appearance > Menus (same pattern as preview / location dialogs).
		 *
		 * @return void
		 */
		public function maybe_print_menu_item_dialog_markup() {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			if ( ! $screen || 'nav-menus' !== $screen->base ) {
				return;
			}

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			self::render_menu_item_dialog_markup();
		}


		/**
		 * Echo the menu item lightbox as a text/html script template (mounted to body by js/admin/dialog-menu-item-settings.js).
		 *
		 * @return void
		 */
		public static function render_menu_item_dialog_markup() {
			?>
			<script type="text/html" id="mmm-mega-menu-dialog-template">
			<div class="megamenu-admin-modal megamenu-menu-item-dialog" hidden data-megamenu-expand-storage-key="megamenu_admin_modal_wpcontent_expanded" data-i18n-modal-expand="<?php echo esc_attr__( 'Expand to fill workspace', 'megamenu' ); ?>" data-i18n-modal-collapse="<?php echo esc_attr__( 'Restore default size', 'megamenu' ); ?>">
				<button type="button" class="megamenu-admin-modal__backdrop" aria-label="<?php echo esc_attr__( 'Close', 'megamenu' ); ?>"></button>
				<div class="megamenu-admin-modal__panel" role="dialog" aria-modal="true" aria-labelledby="megamenu-menu-item-dialog-title" tabindex="-1">
					<div class="megamenu-admin-modal__header">
						<div class="megamenu-admin-modal__title-group">
							<h2 id="megamenu-menu-item-dialog-title" class="megamenu-admin-modal__title">
								<span class="megamenu-admin-modal__title-text"></span>
							</h2>
							<p id="megamenu-menu-item-dialog-breadcrumb" class="megamenu-menu-item-dialog-breadcrumb" hidden></p>
						</div>
						<div class="megamenu-admin-modal__header-meta"></div>
						<div class="megamenu-admin-modal__header-actions">
							<span class="megamenu-menu-item-dialog-saving-indicator" hidden aria-live="polite">
								<span class="dashicons dashicons-update megamenu-menu-item-dialog-saving-indicator__icon" aria-hidden="true"></span>
								<span class="screen-reader-text"><?php echo esc_html__( 'Saving…', 'megamenu' ); ?></span>
							</span>
							<button type="button" class="megamenu-admin-modal__expand-btn" aria-expanded="false" aria-label="<?php echo esc_attr__( 'Expand to fill workspace', 'megamenu' ); ?>">
								<span class="dashicons dashicons-fullscreen-alt megamenu-admin-modal__expand-icon megamenu-admin-modal__expand-icon--expand" aria-hidden="true"></span>
								<span class="dashicons dashicons-fullscreen-exit-alt megamenu-admin-modal__expand-icon megamenu-admin-modal__expand-icon--contract" aria-hidden="true"></span>
							</button>
							<button type="button" class="megamenu-modal-close" aria-label="<?php echo esc_attr__( 'Close', 'megamenu' ); ?>">
								<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
							</button>
						</div>
					</div>
					<div class="megamenu-admin-modal__body megamenu-admin-modal__loading-host">
						<div class="megamenu-admin-modal__loading-overlay" role="status" aria-live="polite">
							<span class="megamenu-admin-modal__loading-spinner" aria-hidden="true"></span>
							<span class="screen-reader-text"><?php echo esc_html__( 'Loading.', 'megamenu' ); ?></span>
						</div>
						<div class="megamenu_outer_wrap megamenu-dialog-rail"></div>
					</div>
				</div>
			</div>
			</script>
			<?php
		}


		/**
		 * Enqueue required CSS and JS for the mega menu lightbox and meta options.
		 *
		 * @since 1.0
		 * @param string $hook The current admin page hook suffix.
		 * @return void
		 */
		public function enqueue_menu_page_scripts( $hook ) {
			if ( ! in_array( $hook, [ 'nav-menus.php' ] ) ) {
				return;
			}

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			// Compatibility fix for TemplatesNext ToolKit
			wp_deregister_script( 'tx-main' );
			wp_deregister_style( 'tx-toolkit-admin-style' );

			wp_enqueue_style( 'maxmegamenu', MEGAMENU_BASE_URL . 'css/admin/admin.css', array( 'wp-components' ), MEGAMENU_VERSION );

			if ( ! wp_script_is( 'dialog-modal-expand', 'registered' ) ) {
				wp_register_script(
					'dialog-modal-expand',
					MEGAMENU_BASE_URL . 'js/admin/dialog-modal-expand.js',
					[ 'jquery' ],
					MEGAMENU_VERSION,
					true
				);
			}

			wp_enqueue_script( 'dialog-modal-expand' );

			wp_enqueue_script(
				'dialog-preview',
				MEGAMENU_BASE_URL . 'js/admin/dialog-preview.js',
				[ 'jquery', 'dialog-modal-expand' ],
				MEGAMENU_VERSION,
				true
			);

			if ( ! wp_script_is( 'dialog-tabs', 'registered' ) ) {
				wp_register_script(
					'dialog-tabs',
					MEGAMENU_BASE_URL . 'js/admin/dialog-tabs.js',
					[],
					MEGAMENU_VERSION,
					true
				);
			}

			if ( class_exists( 'Mega_Menu_Locations' ) ) {
				Mega_Menu_Locations::register_and_localize_location_settings_dialog();
			}

			wp_enqueue_script(
				'dialog-menu-item-settings',
				MEGAMENU_BASE_URL . 'js/admin/dialog-menu-item-settings.js',
				[
					'jquery',
					'jquery-ui-core',
					'jquery-ui-sortable',
					'dialog-tabs',
					'dialog-modal-expand',
				],
				MEGAMENU_VERSION,
				true
			);

			wp_enqueue_script(
				'maxmegamenu',
				MEGAMENU_BASE_URL . 'js/admin/nav-menus.js',
				[
					'jquery',
					'jquery-ui-core',
					'jquery-ui-sortable',
					'dialog-menu-item-settings',
					'dialog-preview',
					'dialog-tabs',
					'dialog-location-settings',
				],
				MEGAMENU_VERSION,
				true
			);

			$settings = get_option( 'megamenu_settings' );

			$prefix = isset( $settings['prefix'] ) ? $settings['prefix'] : 'true';

			$initial_version = get_option( 'megamenu_initial_version' );
			if ( $initial_version && version_compare( $initial_version, '3.9.2', '>=' ) ) {
				$prefix = false;
			}

			wp_localize_script(
				'maxmegamenu',
				'megamenu',
				[
					'launch_lightbox'    => __( 'Mega Menu', 'megamenu' ),
					'is_disabled_error'  => __( 'Please enable Max Mega Menu for this location under Mega Menu > Menu Locations.', 'megamenu' ),
					'save_menu'          => __( 'Please save the menu structure to enable this option.', 'megamenu' ),
					'unsaved_changes'    => __( 'The changes you made will be lost if you navigate away from this page.', 'megamenu'),
					'unsaved_changes_tab_hint' => __( 'This tab has unsaved changes.', 'megamenu' ),
					'saving'             => __( 'Saving', 'megamenu' ),
					'nonce'              => wp_create_nonce( 'megamenu_edit' ),
					'nonce_check_failed' => __( 'Oops. Something went wrong. Please reload the page.', 'megamenu' ),
					'css_prefix'         => $prefix,
					'css_prefix_message' => false === $prefix ? '' : __( "Custom CSS Classes will be prefixed with 'mega-'", 'megamenu' ),
					'row_is_full'        => __( 'There is not enough space in this row to add a new column. Make space by reducing the width of the columns within the row or create a new row.', 'megamenu' ),
					'delete_menu_item'   => __( 'To remove this menu item, close this window and delete it directly from the menu structure.', 'megamenu' ),
					'close'                => __( 'Close', 'megamenu' ),
					'console_save_ok'      => __( 'Saved successfully.', 'megamenu' ),
					'console_request_failed' => __( 'Request failed.', 'megamenu' ),
				]
			);

			do_action( 'megamenu_enqueue_admin_scripts' );
		}

		/**
		 * Output the Mega Menu settings meta box contents.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function metabox_contents() {
			// Same value as wp-admin/nav-menus.php (resolved before meta boxes render).
			global $nav_menu_selected_id;

			$menu_id = isset( $nav_menu_selected_id ) ? (int) $nav_menu_selected_id : 0;
			$this->print_enable_megamenu_options( $menu_id );
		}


		/**
		 * Print the custom meta box settings for the given menu.
		 *
		 * @since 1.0
		 * @param int $menu_id The ID of the currently selected menu.
		 * @return void
		 */
		public function print_enable_megamenu_options( $menu_id ) {
			$tagged_menu_locations = $this->get_tagged_theme_locations_for_menu_id( $menu_id );
			$theme_locations       = get_registered_nav_menus();

			if ( ! count( $theme_locations ) ) {
				echo "<div style='padding: 15px;'>";
				$menu_locations_url = admin_url( 'admin.php?page=maxmegamenu' );
				$locations_link_processor = new WP_HTML_Tag_Processor( '<a>' . esc_html__( 'Mega Menu > Menu Locations', 'megamenu' ) . '</a>' );
				if ( $locations_link_processor->next_tag( 'a' ) ) {
					$locations_link_processor->set_attribute( 'href', esc_url( $menu_locations_url ) );
				}
				$locations_link_html = $locations_link_processor->get_updated_html();
				$allowed_anchor      = [
					'a' => [
						'href' => true,
					],
				];
				echo '<p>' . esc_html__( 'There are currently no menu locations registered by your theme.', 'megamenu' ) . '</p>';
				echo '<p>' . wp_kses(
					sprintf(
						/* translators: %s: "Mega Menu > Menu Locations" link to the plugin admin screen */
						__( 'Go to %s to create a new menu location.', 'megamenu' ),
						wp_kses( $locations_link_html, $allowed_anchor )
					),
					$allowed_anchor
				) . '</p>';
				echo '<p>' . esc_html__( 'Then use the Max Mega Menu block, widget or shortcode to output the menu location on your site.', 'megamenu' ) . '</p>';
				echo "</div>";
			} elseif ( ! count( $tagged_menu_locations ) ) {
				echo "<div style='padding: 15px;'>";
				echo '<p>' . esc_html__( 'Please assign this menu to a theme location to enable the Mega Menu settings.', 'megamenu' ) . '</p>';
				echo '<p>' . esc_html__( "To assign this menu to a theme location, scroll to the bottom of this page and tag the menu to a 'Display location'.", 'megamenu' ) . '</p>';
				echo "</div>";

			} else {
				/**
				 * Output Menu Locations–style cards for this menu’s assigned theme locations only.
				 *
				 * @param array $tagged_menu_locations Map of location slug => registered label.
				 */
				do_action( 'megamenu_nav_metabox_location_cards', $tagged_menu_locations );
			}
		}


		/**
		 * Return the theme locations that a specific menu ID has been assigned to.
		 *
		 * @since 1.0
		 * @param int $menu_id The menu's term ID.
		 * @return array Map of location slug to location name.
		 */
		public function get_tagged_theme_locations_for_menu_id( $menu_id ) {

			$locations = [];

			$nav_menu_locations = get_nav_menu_locations();

			foreach ( get_registered_nav_menus() as $id => $name ) {

				if ( isset( $nav_menu_locations[ $id ] ) && $nav_menu_locations[ $id ] == $menu_id ) {
					$locations[ $id ] = $name;
				}
			}

			return $locations;
		}
	}

endif;
