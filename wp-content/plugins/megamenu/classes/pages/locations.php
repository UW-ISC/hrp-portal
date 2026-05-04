<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access.
}

if ( ! class_exists( 'Mega_Menu_Locations' ) ) :

	/**
	 * Handles the Mega Menu > Menu Locations admin page.
	 *
	 * @since   2.8
	 * @package MegaMenu
	 */
	class Mega_Menu_Locations {

		/**
		 * Constructor. Registers form submission and tab hooks.
		 *
		 * @since  2.8
		 * @return void
		 */
		public function __construct() {
			add_action( 'admin_post_megamenu_add_menu_location', [ $this, 'add_menu_location' ] );
			add_action( 'admin_post_megamenu_delete_menu_location', [ $this, 'delete_menu_location' ] );

			add_action( 'wp_ajax_megamenu_get_location_settings_html', [ $this, 'ajax_get_location_settings_html' ] );
			add_action( 'wp_ajax_megamenu_save_location_settings', [ $this, 'ajax_save_location_settings' ] );
			add_action( 'wp_ajax_megamenu_save_custom_location_title', [ $this, 'ajax_save_custom_location_title' ] );
			add_action( 'wp_ajax_megamenu_toggle_location_mmm', [ $this, 'ajax_toggle_location_mmm' ] );
			add_action( 'wp_ajax_megamenu_delete_menu_location', [ $this, 'ajax_delete_menu_location' ] );

			add_filter( 'megamenu_menu_tabs', [ $this, 'add_locations_tab' ], 1 );
			add_action( 'megamenu_page_menu_locations', [ $this, 'menu_locations_page' ] );
			add_action( 'admin_footer', [ $this, 'maybe_print_location_settings_dialog' ], 6 );
			add_action( 'megamenu_admin_scripts', [ __CLASS__, 'enqueue_location_settings_dialog_script' ], 20 );
			add_action( 'megamenu_nav_metabox_location_cards', [ $this, 'echo_nav_metabox_location_cards' ], 10, 1 );
		}


		/**
		 * Add the Menu Locations tab to the available admin tabs.
		 *
		 * @since  2.8
		 * @param  array $tabs Existing tabs.
		 * @return array Tabs with the Menu Locations tab prepended.
		 */
		public function add_locations_tab( $tabs ) {
			$tabs['menu_locations'] = __( 'Menu Locations', 'megamenu' );
			return $tabs;
		}


		/**
		 * Add a new menu location.
		 *
		 * @since  2.8
		 * @return void
		 */
		public function add_menu_location() {
			check_admin_referer( 'megamenu_add_menu_location' );

			if ( ! current_user_can( apply_filters( 'megamenu_options_capability', 'edit_theme_options' ) ) ) {
				wp_die( -1, 403 );
			}

			$locations = get_option( 'megamenu_locations', [] );
			if ( ! is_array( $locations ) ) {
				$locations = [];
			}
			$is_first_custom_location = empty( $locations );
			$next_id                  = $this->get_next_menu_location_id();
			$new_menu_location_id     = 'max_mega_menu_' . $next_id;

			$title = 'Max Mega Menu Location ' . $next_id;

			if ( isset( $_POST['title'] ) ) {
				$title = esc_attr( wp_unslash( $_POST['title'] ) );
			}

			$locations[ $new_menu_location_id ] = esc_attr( $title );

			update_option( 'megamenu_locations', $locations );

			$plugin_settings = get_option( 'megamenu_settings', [] );
			if ( ! is_array( $plugin_settings ) ) {
				$plugin_settings = [];
			}

			$existing_for_location = isset( $plugin_settings[ $new_menu_location_id ] ) && is_array( $plugin_settings[ $new_menu_location_id ] )
				? $plugin_settings[ $new_menu_location_id ]
				: [];

			$defaults_for_location = $this->get_default_settings_for_new_location( $plugin_settings, $new_menu_location_id );

			$plugin_settings[ $new_menu_location_id ] = array_merge(
				$defaults_for_location,
				$existing_for_location
			);

			update_option( 'megamenu_settings', $plugin_settings );

			$menu_id = 0;

			if ( isset( $_POST['menu_id'] ) ) {
				$menu_id = absint( $_POST['menu_id'] );
			}

			if ( $menu_id > 0 ) {
				$locations = get_theme_mod( 'nav_menu_locations' );

				$locations[ $new_menu_location_id ] = $menu_id;
				set_theme_mod( 'nav_menu_locations', $locations );
			}

			do_action( 'megamenu_after_save_settings' );
			do_action( 'megamenu_after_add_menu_location' );

			if ( $menu_id <= 0 ) {
				do_action( 'megamenu_delete_cache' );
			}

			$query_args = [
				'page'           => 'maxmegamenu',
				'location_added' => 'true',
				'location'       => $new_menu_location_id,
			];
			if ( $is_first_custom_location ) {
				$query_args['first_mmm_location'] = '1';
			}
			$redirect_url = add_query_arg( $query_args, admin_url( 'admin.php' ) );

			$this->redirect( $redirect_url );
		}


		/**
		 * Delete a menu location.
		 *
		 * @since  2.8
		 * @return void
		 */
	public function delete_menu_location() {
		check_admin_referer( 'megamenu_delete_menu_location' );

		if ( ! current_user_can( apply_filters( 'megamenu_options_capability', 'edit_theme_options' ) ) ) {
			wp_die( -1, 403 );
		}

		$location_to_delete = isset( $_GET['location'] ) ? sanitize_key( wp_unslash( $_GET['location'] ) ) : '';

			$this->remove_saved_custom_menu_location( $location_to_delete );
			$this->fire_after_menu_location_deleted();

			$redirect_url = add_query_arg(
				[
					'page'            => 'maxmegamenu',
					'delete_location' => 'true',
				],
				admin_url( 'admin.php' )
			);

			$this->redirect( $redirect_url );
		}


		/**
		 * Remove a slug from the megamenu_locations option when present.
		 *
		 * @param string $location_to_delete Sanitized location slug.
		 * @return void
		 */
		private function remove_saved_custom_menu_location( $location_to_delete ) {
			$locations = get_option( 'megamenu_locations', [] );
			if ( ! is_array( $locations ) ) {
				$locations = [];
			}
			if ( isset( $locations[ $location_to_delete ] ) ) {
				unset( $locations[ $location_to_delete ] );
				update_option( 'megamenu_locations', $locations );
			}
		}


		/**
		 * Fires after attempting to delete a menu location (same as legacy admin-post flow).
		 *
		 * @return void
		 */
		private function fire_after_menu_location_deleted() {
			do_action( 'megamenu_after_delete_menu_location' );
			do_action( 'megamenu_delete_cache' );
		}


		/**
		 * AJAX: Delete a custom Max Mega Menu location (card trash control).
		 *
		 * @return void
		 */
		public function ajax_delete_menu_location() {
			check_ajax_referer( 'megamenu_edit', 'nonce' );

			if ( ! current_user_can( apply_filters( 'megamenu_options_capability', 'edit_theme_options' ) ) ) {
				wp_send_json_error();
			}

			$location = isset( $_POST['location'] ) ? sanitize_key( wp_unslash( $_POST['location'] ) ) : '';

			if ( ! $location || strpos( $location, 'max_mega_menu_' ) === false ) {
				wp_send_json_error();
			}

			$this->remove_saved_custom_menu_location( $location );
			$this->fire_after_menu_location_deleted();

			wp_send_json_success();
		}


		/**
		 * Redirect and exit.
		 *
		 * @since  2.8
		 * @param  string $url URL to redirect to.
		 * @return void
		 */
	public function redirect( $url ) {
		wp_safe_redirect( $url );
		exit;
	}


		/**
		 * Default megamenu_settings slice for a newly created menu location.
		 *
		 * Matches the Location Settings UI when no per-location values exist yet, including
		 * fallbacks to global option keys where the UI does the same.
		 *
		 * @since 3.8.2
		 *
		 * @param array  $plugin_settings Full megamenu_settings option.
		 * @param string $location_id     Location slug (for filters).
		 * @return array<string, mixed>
		 */
		private function get_default_settings_for_new_location( array $plugin_settings, $location_id = '' ) {
			$defaults = [
				'enabled'                 => '1',
				'event'                   => 'hover',
				'effect'                  => 'fade_up',
				'effect_speed'            => '200',
				'effect_mobile'           => 'slide_right',
				'effect_mobile_direction' => 'vertical',
				'effect_speed_mobile'     => '200',
				'theme'                   => 'default',
				'second_click'            => isset( $plugin_settings['second_click'] ) ? $plugin_settings['second_click'] : 'go',
				'mobile_behaviour'        => isset( $plugin_settings['mobile_behaviour'] ) ? $plugin_settings['mobile_behaviour'] : 'standard',
				'mobile_state'            => 'collapse_all',
				'container'               => 'div',
				'unbind'                  => isset( $plugin_settings['unbind'] ) ? $plugin_settings['unbind'] : 'enabled',
				'descriptions'            => isset( $plugin_settings['descriptions'] ) ? $plugin_settings['descriptions'] : 'disabled',
				'prefix'                  => isset( $plugin_settings['prefix'] ) ? $plugin_settings['prefix'] : 'disabled',
			];

			/**
			 * Filter default settings stored when a new custom menu location is created.
			 *
			 * @since 3.8.2
			 *
			 * @param array  $defaults        Default per-location settings.
			 * @param array  $plugin_settings Full megamenu_settings before the new location is merged.
			 * @param string $location_id     New location slug.
			 */
			return apply_filters( 'megamenu_new_location_default_settings', $defaults, $plugin_settings, $location_id );
		}


		/**
		 * Merge submitted megamenu_meta (location => settings) into the megamenu_settings option.
		 *
		 * @since 3.x
		 * @param array $megamenu_meta Submitted settings keyed by location slug.
		 * @return void
		 */
		public function persist_submitted_megamenu_meta( array $megamenu_meta ) {
			if ( ! count( $megamenu_meta ) ) {
				return;
			}

			// Backward compatibility: let extensions normalize raw submitted values
			// (e.g. unchecked checkboxes omitted from POST) before merge.
			$megamenu_meta = apply_filters( 'megamenu_submitted_settings_meta', $megamenu_meta );

			$existing_settings = get_option( 'megamenu_settings', [] );
			$merged_submit     = [];

			foreach ( $megamenu_meta as $loc => $loc_settings ) {
				if ( ! is_array( $loc_settings ) ) {
					continue;
				}

				$base = isset( $existing_settings[ $loc ] ) && is_array( $existing_settings[ $loc ] )
					? $existing_settings[ $loc ]
					: [];

				$merged = array_merge( $base, $loc_settings );

				// Only touch `enabled` when this request includes that key (checkbox / explicit field).
				// Omitting it — e.g. dialog save without an enabled field — must preserve the merged value.
				if ( array_key_exists( 'enabled', $loc_settings ) ) {
					$raw = $loc_settings['enabled'];
					if ( '1' === (string) $raw || 1 === $raw || true === $raw || 'true' === $raw || 'on' === $raw ) {
						$merged['enabled'] = '1';
					} else {
						unset( $merged['enabled'] );
					}
				}

				foreach ( [ 'descriptions', 'unbind', 'prefix' ] as $pill_key ) {
					if ( array_key_exists( $pill_key, $loc_settings ) ) {
						$raw_pill = $loc_settings[ $pill_key ];
						$merged[ $pill_key ] = ( 'enabled' === $raw_pill || 'true' === $raw_pill ) ? 'enabled' : 'disabled';
					} else {
						// Unchecked pills omit the key; omitted rows (e.g. prefix UI off) — default disabled.
						$merged[ $pill_key ] = 'disabled';
					}
				}

				$merged_submit[ $loc ] = $merged;
			}

			$submitted_settings = $merged_submit;

			if ( ! get_option( 'megamenu_settings' ) ) {
				update_option( 'megamenu_settings', $submitted_settings );
			} else {
				$existing_settings = get_option( 'megamenu_settings' );
				$new_settings      = array_merge( $existing_settings, $submitted_settings );
				update_option( 'megamenu_settings', $new_settings );
			}

			do_action( 'megamenu_after_save_settings' );
			do_action( 'megamenu_delete_cache' );
		}


		/**
		 * AJAX: HTML for the location settings dialog (tabs + fields).
		 *
		 * @return void
		 */
		public function ajax_get_location_settings_html() {
			check_ajax_referer( 'megamenu_edit', 'nonce' );

			if ( ! current_user_can( apply_filters( 'megamenu_options_capability', 'edit_theme_options' ) ) ) {
				wp_send_json_error();
			}

			$location = isset( $_POST['location'] ) ? sanitize_key( wp_unslash( $_POST['location'] ) ) : '';

			if ( ! $location ) {
				wp_send_json_error();
			}

			$all_locations = $this->get_registered_locations();

			if ( ! isset( $all_locations[ $location ] ) ) {
				wp_send_json_error();
			}

			$description = $all_locations[ $location ];

			ob_start();
			$this->render_location_settings_form( $all_locations, $location, $description );
			$form_html = ob_get_clean();

			$html = '<div class="megamenu-location-settings-dialog__surface megamenu_outer_wrap">' . $form_html . '</div>';

			wp_send_json_success( [ 'html' => $html ] );
		}


		/**
		 * AJAX: Save settings for one location from the dialog form.
		 *
		 * @return void
		 */
		public function ajax_save_location_settings() {
			check_ajax_referer( 'megamenu_edit', 'nonce' );

			if ( ! current_user_can( apply_filters( 'megamenu_options_capability', 'edit_theme_options' ) ) ) {
				wp_send_json_error();
			}

			$location = isset( $_POST['location'] ) ? sanitize_key( wp_unslash( $_POST['location'] ) ) : '';

			if ( ! $location || ! isset( $_POST['megamenu_meta'][ $location ] ) || ! is_array( $_POST['megamenu_meta'][ $location ] ) ) {
				wp_send_json_error();
			}

			$all_locations = $this->get_registered_locations();

			if ( ! isset( $all_locations[ $location ] ) ) {
				wp_send_json_error();
			}

			$this->persist_submitted_megamenu_meta(
				[
					$location => wp_unslash( $_POST['megamenu_meta'][ $location ] ),
				]
			);

			wp_send_json_success();
		}


		/**
		 * AJAX: Save the display title for a custom (max_mega_menu_*) location (dialog header or Menu Locations card).
		 *
		 * @return void
		 */
		public function ajax_save_custom_location_title() {
			check_ajax_referer( 'megamenu_edit', 'nonce' );

			if ( ! current_user_can( apply_filters( 'megamenu_options_capability', 'edit_theme_options' ) ) ) {
				wp_send_json_error();
			}

			$location = isset( $_POST['location'] ) ? sanitize_key( wp_unslash( $_POST['location'] ) ) : '';

			if ( ! $location || 0 !== strpos( $location, 'max_mega_menu_' ) ) {
				wp_send_json_error();
			}

			$all_locations = $this->get_registered_locations();

			if ( ! isset( $all_locations[ $location ] ) ) {
				wp_send_json_error();
			}

			$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';

			$locations = get_option( 'megamenu_locations', [] );

			if ( ! is_array( $locations ) ) {
				$locations = [];
			}

			$locations[ $location ] = $title;
			update_option( 'megamenu_locations', $locations );

			do_action( 'megamenu_after_save_settings' );
			do_action( 'megamenu_delete_cache' );

			wp_send_json_success(
				[
					'title'         => $title,
					'preview_title' => Mega_Menu_Preview::get_preview_title( $location, $title ),
				]
			);
		}


		/**
		 * AJAX: Toggle Max Mega Menu on/off for a location (pill control on Menu Locations).
		 *
		 * @return void
		 */
		public function ajax_toggle_location_mmm() {
			check_ajax_referer( 'megamenu_edit', 'nonce' );

			if ( ! current_user_can( apply_filters( 'megamenu_options_capability', 'edit_theme_options' ) ) ) {
				wp_send_json_error();
			}

			$location = isset( $_POST['location'] ) ? sanitize_key( wp_unslash( $_POST['location'] ) ) : '';

			if ( ! $location ) {
				wp_send_json_error();
			}

			$all_locations = $this->get_registered_locations();

			if ( ! isset( $all_locations[ $location ] ) ) {
				wp_send_json_error();
			}

			$raw_on = isset( $_POST['enabled'] ) ? wp_unslash( $_POST['enabled'] ) : '0';
			$on     = ( '1' === $raw_on || 'true' === $raw_on || true === $raw_on || 1 === $raw_on );

			$settings = get_option( 'megamenu_settings', [] );

			if ( ! isset( $settings[ $location ] ) || ! is_array( $settings[ $location ] ) ) {
				$settings[ $location ] = [];
			}

			if ( $on ) {
				$settings[ $location ]['enabled'] = '1';
			} else {
				unset( $settings[ $location ]['enabled'] );
			}

			update_option( 'megamenu_settings', $settings );

			do_action( 'megamenu_after_save_settings' );
			do_action( 'megamenu_delete_cache' );

			wp_send_json_success( [ 'enabled' => $on ] );
		}


		/**
		 * Print the shared location settings modal once per screen.
		 *
		 * @return void
		 */
		public function maybe_print_location_settings_dialog() {
			static $printed = false;

			if ( $printed ) {
				return;
			}

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			global $pagenow;

			$screen = get_current_screen();

			$allowed = ( isset( $pagenow ) && 'nav-menus.php' === $pagenow )
				|| ( $screen && 'nav-menus' === $screen->base )
				|| ( $screen && 'toplevel_page_maxmegamenu' === $screen->id )
				|| ( $screen && false !== strpos( $screen->id, 'maxmegamenu' ) );

			if ( ! $allowed ) {
				return;
			}

			$printed = true;
			self::render_location_settings_dialog_markup();
		}


		/**
		 * Echo the location settings modal as a text/html script template (mounted to body by js/admin/dialog-location-settings.js).
		 *
		 * @return void
		 */
		public static function render_location_settings_dialog_markup() {
			?>
			<script type="text/html" id="megamenu-location-settings-dialog-template">
			<div id="megamenu-location-settings-dialog" class="megamenu-admin-modal megamenu-location-settings-dialog" hidden data-megamenu-expand-storage-key="megamenu_admin_modal_wpcontent_expanded" data-i18n-modal-expand="<?php echo esc_attr__( 'Expand to fill workspace', 'megamenu' ); ?>" data-i18n-modal-collapse="<?php echo esc_attr__( 'Restore default size', 'megamenu' ); ?>">
				<button type="button" class="megamenu-admin-modal__backdrop" aria-label="<?php esc_attr_e( 'Close', 'megamenu' ); ?>"></button>
				<div class="megamenu-admin-modal__panel" role="dialog" aria-modal="true" aria-labelledby="megamenu-location-settings-dialog-title" tabindex="-1">
					<div class="megamenu-admin-modal__header">
						<div class="megamenu-admin-modal__title-group">
							<h2 id="megamenu-location-settings-dialog-title" class="megamenu-admin-modal__title">
								<span class="megamenu-admin-modal__title-text">
									<span class="megamenu-location-settings-title-prefix"></span><span class="megamenu-location-title"></span>
								</span>
							</h2>
							<p id="megamenu-location-settings-dialog-subtitle" class="megamenu_subtitle" hidden></p>
						</div>
						<div class="megamenu-admin-modal__header-actions">
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
						<div id="megamenu-location-settings-dialog-body" class="megamenu-admin-modal__body-slot"></div>
					</div>
					<div class="megamenu-admin-modal__footer megamenu-location-settings-dialog__footer">
						<p class="submit">
							<button type="button" class="button button-primary button-compact megamenu-location-settings-dialog-save"><?php esc_html_e( 'Save', 'megamenu' ); ?></button>
						</p>
					</div>
				</div>
			</div>
			</script>
			<?php
		}


		/**
		 * Enqueue dialog script on Mega Menu admin screens.
		 *
		 * @param string $hook Hook suffix.
		 * @return void
		 */
		public static function enqueue_location_settings_dialog_script( $hook ) {
			if ( false === strpos( $hook, 'maxmegamenu' ) ) {
				return;
			}

			self::register_and_localize_location_settings_dialog();
		}


		/**
		 * Register, localize, and enqueue the location settings dialog script.
		 *
		 * @return void
		 */
		public static function register_and_localize_location_settings_dialog() {
			if ( ! wp_script_is( 'dialog-tabs', 'registered' ) ) {
				wp_register_script(
					'dialog-tabs',
					MEGAMENU_BASE_URL . 'js/admin/dialog-tabs.js',
					[],
					MEGAMENU_VERSION,
					true
				);
			}

			if ( ! wp_script_is( 'dialog-modal-expand', 'registered' ) ) {
				wp_register_script(
					'dialog-modal-expand',
					MEGAMENU_BASE_URL . 'js/admin/dialog-modal-expand.js',
					[ 'jquery' ],
					MEGAMENU_VERSION,
					true
				);
			}

			if ( wp_script_is( 'dialog-location-settings', 'enqueued' ) ) {
				return;
			}

			$initial                = '';
			$highlight_new_location = '';

			if ( isset( $_GET['location'] ) ) {
				$loc = sanitize_key( wp_unslash( $_GET['location'] ) );
				// After creating a location, highlight the card — do not auto-open the settings dialog.
				if ( isset( $_GET['location_added'] ) && 'true' === $_GET['location_added'] ) {
					$highlight_new_location = $loc;
				} else {
					$initial = $loc;
				}
			}

			wp_enqueue_script(
				'dialog-location-settings',
				MEGAMENU_BASE_URL . 'js/admin/dialog-location-settings.js',
				[ 'jquery', 'dialog-tabs', 'dialog-modal-expand' ],
				MEGAMENU_VERSION,
				true
			);

			wp_localize_script(
				'dialog-location-settings',
				'megamenu_location_dialog',
				[
					// Root-relative so requests use the browser origin (e.g. :10003) when siteurl omits the port.
					'ajaxurl'                => admin_url( 'admin-ajax.php', 'relative' ),
					'nonce'                  => wp_create_nonce( 'megamenu_edit' ),
					'toggle_location_action' => 'megamenu_toggle_location_mmm',
					'delete_location_action' => 'megamenu_delete_menu_location',
					'initial_open_location' => $initial,
					'highlight_new_location' => $highlight_new_location,
					'i18n'                  => [
						'load_error'            => __( 'Could not load location settings.', 'megamenu' ),
						'save_error'            => __( 'Could not save settings.', 'megamenu' ),
						'saved'                 => __( 'Settings saved.', 'megamenu' ),
						'assign_menu'           => __( 'Assign a menu to this location before changing settings.', 'megamenu' ),
						'assigned_menu_prefix'  => __( 'Assigned menu:', 'megamenu' ),
						'toggle_error'           => __( 'Could not update Max Mega Menu for this location.', 'megamenu' ),
						/* translators: %s: menu location name (replaced in JavaScript). */
						'dialog_title_tpl'      => __( 'Location Settings: %s', 'megamenu' ),
						'delete_confirm'        => __( 'Delete this menu location? This cannot be undone.', 'megamenu' ),
						'delete_error'          => __( 'Could not delete this menu location.', 'megamenu' ),
						'title_save_error'      => __( 'Could not save the location name.', 'megamenu' ),
					],
				]
			);
		}


		/**
		 * Returns the next available menu location ID.
		 *
		 * @since  2.8
		 * @return int Next available integer ID.
		 */
		public function get_next_menu_location_id() {
			$last_id = 0;

			if ( $locations = get_option( 'megamenu_locations' ) ) {
				foreach ( $locations as $key => $value ) {
					if ( strpos( $key, 'max_mega_menu_' ) !== false ) {
						$parts   = explode( '_', $key );
						$menu_id = end( $parts );

						if ( $menu_id > $last_id ) {
							$last_id = $menu_id;
						}
					}
				}
			}

			$next_id = $last_id + 1;

			return $next_id;
		}


		/**
		 * Sort a map of location slug => label alphabetically by label (natural, case-insensitive), then by slug.
		 *
		 * @param array<string, string> $locations Location map; sorted in place.
		 */
		private function sort_locations_by_label( array &$locations ) {
			uksort(
				$locations,
				static function ( $id_a, $id_b ) use ( $locations ) {
					$cmp = strnatcasecmp( (string) $locations[ $id_a ], (string) $locations[ $id_b ] );
					return 0 !== $cmp ? $cmp : strcmp( $id_a, $id_b );
				}
			);
		}


		/**
		 * Split locations into MMM-enabled vs inactive buckets (same rule as the Menu Locations page), label-sorted each.
		 *
		 * @param array<string, string> $locations Location slug => registered description.
		 * @return array{0: array<string, string>, 1: array<string, string>} [ enabled locations, disabled locations ].
		 */
		private function partition_locations_by_mmm_active_state( array $locations ) {
			$enabled  = [];
			$disabled = [];

			foreach ( $locations as $id => $description ) {
				if ( max_mega_menu_is_enabled( $id ) ) {
					$enabled[ $id ] = $description;
				} else {
					$disabled[ $id ] = $description;
				}
			}

			$this->sort_locations_by_label( $enabled );
			$this->sort_locations_by_label( $disabled );

			return [ $enabled, $disabled ];
		}


		/**
		 * Open the shared location cards shell (same on Mega Menu > Menu Locations and Appearance > Menus).
		 *
		 * @param string $context Pass `meta` for the Appearance > Menus meta box (narrow layout + compact chrome); default `page` for Menu Locations.
		 * @return void
		 */
		private static function print_location_cards_shell_open( $context = 'page' ) {
			$classes = 'menu_settings menu_settings_menu_locations mega-location-cards-root';
			if ( 'meta' === $context ) {
				$classes .= ' mega-location-cards-root--meta';
			}
			echo '<div class="' . esc_attr( $classes ) . '">';
			echo "<div class='mega-location-cards'>";
		}


		/**
		 * Close the inner `.mega-location-cards` grid (location postboxes + optional “Add location” tile are inside).
		 *
		 * @return void
		 */
		private static function print_location_cards_grid_close() {
			echo '</div>';
		}


		/**
		 * Close the outer `.mega-location-cards-root` wrapper.
		 *
		 * @return void
		 */
		private static function print_location_cards_shell_close() {
			echo '</div>';
		}


		/**
		 * Output location cards inside the Max Mega Menu meta box on Appearance > Menus (subset only).
		 *
		 * @param array $tagged_menu_locations Map of location slug => registered label for locations assigned to the current menu.
		 * @return void
		 */
		public function echo_nav_metabox_location_cards( $tagged_menu_locations ) {
			if ( ! is_array( $tagged_menu_locations ) || ! count( $tagged_menu_locations ) ) {
				return;
			}

			$saved_settings = get_option( 'megamenu_settings', [] );
			if ( ! is_array( $saved_settings ) ) {
				$saved_settings = [];
			}

			$all_locations = $this->get_registered_locations();

			list( $enabled_locations, $disabled_locations ) = $this->partition_locations_by_mmm_active_state( $tagged_menu_locations );

			self::print_location_cards_shell_open( 'meta' );
			foreach ( $enabled_locations as $location => $description ) {
				$this->show_location_row( $all_locations, $location, $description, $saved_settings, 'meta' );
			}
			foreach ( $disabled_locations as $location => $description ) {
				$this->show_location_row( $all_locations, $location, $description, $saved_settings, 'meta' );
			}
			self::print_location_cards_grid_close();
			self::print_location_cards_shell_close();
		}


		/**
		 * Render the Menu Locations page content.
		 *
		 * @since  2.8
		 * @param  array $saved_settings Saved plugin settings.
		 * @return void
		 */
		public function menu_locations_page( $saved_settings ) {
			if ( isset( $_GET['add_location'] ) ) {
				$this->add_location_page();
				return;
			}

			$all_locations = $this->get_registered_locations();

			list( $enabled_locations, $disabled_locations ) = $this->partition_locations_by_mmm_active_state( $all_locations );
			?>

			<div class='menu_settings menu_settings_menu_locations'>

				<?php $this->print_messages(); ?>

				<h3 class='first'><?php esc_html_e( 'Menu Locations', 'megamenu' ); ?></h3>

				<div class="mega-menu-locations-page-intro">
					<p><?php esc_html_e( 'This is an overview of the menu locations registered by your theme.', 'megamenu' ); ?> <?php esc_html_e( "A menu location acts as a placeholder (or 'slot') for where a menu can be displayed on your site.", 'megamenu' ); ?>
						<?php
						printf(
							wp_kses_post(
								/* translators: %s: "Appearance > Menus" link (HTML) to the Menus admin screen. */
								__( 'Menus (created on the %s page) are assigned to a Menu Location.', 'megamenu' )
							),
							'<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Appearance > Menus', 'megamenu' ) . '</a>'
						);
						?>
					</p>
					<p><?php esc_html_e( "Use the toggle to enable Max Mega Menu for a specific menu location, then click the settings button to customize its behaviour.", 'megamenu' ); ?></p>

					<?php
					if ( ! count( $enabled_locations + $disabled_locations ) ) {
						echo '<p>';
						esc_html_e( 'Add a new menu location below, then display the menu using the Max Mega Menu block, widget or shortcode.', 'megamenu' );
						echo '</p>';
					}
					?>
				</div>

				<?php
				$add_location_url = add_query_arg(
					[
						'page'         => 'maxmegamenu',
						'add_location' => 'true',
					],
					admin_url( 'admin.php' )
				);

				self::print_location_cards_shell_open();
				foreach ( $enabled_locations as $location => $description ) {
					$this->show_location_row( $all_locations, $location, $description, $saved_settings, 'page' );
				}
				foreach ( $disabled_locations as $location => $description ) {
					$this->show_location_row( $all_locations, $location, $description, $saved_settings, 'page' );
				}

				$add_location_label = count( $all_locations ) > 0
					? __( 'Add another menu location', 'megamenu' )
					: __( 'Get started: Add your first menu location', 'megamenu' );

				printf(
					'<a class="mega-location-add-card" href="%1$s"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span><span class="mega-location-add-card__label">%2$s</span></a>',
					esc_url( $add_location_url ),
					esc_html( $add_location_label )
				);
				self::print_location_cards_grid_close();
				self::print_location_cards_shell_close();
				?>

				<?php do_action( 'megamenu_menu_locations', $saved_settings ); ?>

			</div>

			<?php
		}

		/**
		 * Button that opens the shared location settings dialog (location card footers).
		 *
		 * @param string $location       Location slug.
		 * @param string $location_label Human-readable name.
		 * @param array  $args {
		 *     @type bool $requires_menu When true, the control is disabled until a menu is assigned.
		 *     @type int  $menu_id       Optional; echoed as data-menu-id when greater than zero.
		 * }
		 * @return string HTML.
		 */
		public static function render_location_settings_trigger( $location, $location_label, $args = [] ) {
			$args = wp_parse_args(
				$args,
				[
					'requires_menu' => false,
					'menu_id'       => 0,
				]
			);

			$visible_label = __( 'Settings', 'megamenu' );
			$classes       = 'button button-secondary button-compact megamenu-location-settings-card-btn mega-location-settings-open';

			$button_inner = '<span class="dashicons dashicons-admin-generic" aria-hidden="true"></span> ' . esc_html( $visible_label );
			$processor    = new WP_HTML_Tag_Processor( '<button type="button">' . $button_inner . '</button>' );

			if ( $processor->next_tag( 'button' ) ) {
				if ( $args['requires_menu'] ) {
					$classes .= ' mega-location-settings-open--needs-menu';
				}
				$processor->set_attribute( 'class', $classes );
				$processor->set_attribute( 'aria-label', wp_strip_all_tags( $visible_label ) );
				$processor->set_attribute( 'data-location', $location );
				$processor->set_attribute( 'data-location-label', wp_strip_all_tags( $location_label ) );
				$processor->set_attribute( 'data-requires-menu', $args['requires_menu'] ? '1' : '0' );

				$assigned_menu_name = Mega_Menu_Preview::get_assigned_menu_name_for_location( $location );
				if ( '' !== $assigned_menu_name ) {
					$processor->set_attribute( 'data-assigned-menu', $assigned_menu_name );
				}

				if ( (int) $args['menu_id'] > 0 ) {
					$processor->set_attribute( 'data-menu-id', (string) (int) $args['menu_id'] );
				}

				if ( $args['requires_menu'] ) {
					$processor->set_attribute( 'disabled', true );
				}
			}

			return $processor->get_updated_html();
		}


		/**
		 * Gutenberg-style pill toggle: enable Max Mega Menu for a location (saved via AJAX).
		 *
		 * @param string $location Location slug.
		 * @param bool   $checked  Whether MMM is enabled in saved settings.
		 * @param string $id_suffix Unique suffix for the input id (e.g. "nav-primary" vs "loc-primary").
		 * @param bool   $disabled  When true, the control is non-interactive (no menu assigned to this location).
		 * @return string HTML.
		 */
		public static function render_mmm_enable_toggle( $location, $checked, $id_suffix = '', $disabled = false ) {
			$suffix = $id_suffix ? sanitize_key( $id_suffix ) : sanitize_key( $location );
			$id     = 'mmm-mmm-toggle-' . $suffix;

			$toggle_classes = 'components-form-toggle';
			if ( $checked ) {
				$toggle_classes .= ' is-checked';
			}
			if ( $disabled ) {
				$toggle_classes .= ' is-disabled';
			}

			ob_start();
			?>
			<label class="mega-mmm-enable-toggle">
				<span class="<?php echo esc_attr( $toggle_classes ); ?>">
					<input
						type="checkbox"
						id="<?php echo esc_attr( $id ); ?>"
						class="components-form-toggle__input megamenu_enabled"
						role="switch"
						value="1"
						data-mega-location="<?php echo esc_attr( $location ); ?>"
						<?php checked( $checked ); ?>
						<?php disabled( $disabled ); ?>
					/>
					<span class="components-form-toggle__track" aria-hidden="true"></span>
					<span class="components-form-toggle__thumb" aria-hidden="true"></span>
				</span>
				<span class="screen-reader-text"><?php esc_html_e( 'Enable Max Mega Menu for this menu location', 'megamenu' ); ?></span>
			</label>
			<?php
			return ob_get_clean();
		}


		/**
		 * Human-readable assigned-menu line for the Menu Locations card (linked menu name + item count).
		 *
		 * @param string $location Location slug.
		 * @return string Safe HTML (empty when no menu is assigned).
		 */
		private function render_location_assignment_summary( $location ) {
			$menu_id = $this->get_menu_id_for_location( $location );
			$name    = $this->get_menu_name_for_location( $location );

			if ( ! $menu_id || ! $name ) {
				return '';
			}

			$menu_obj = wp_get_nav_menu_object( (int) $menu_id );
			$count    = ( $menu_obj && isset( $menu_obj->count ) ) ? (int) $menu_obj->count : 0;
			$href     = admin_url( 'nav-menus.php?action=edit&menu=' . (int) $menu_id );

			$html = sprintf(
				'%s <a class="mega-location__assigned-link" href="%s">%s</a> (%d %s)',
				esc_html__( 'Assigned menu:', 'megamenu' ),
				esc_url( $href ),
				esc_html( $name ),
				$count,
				esc_html( _n( 'item', 'items', $count, 'megamenu' ) )
			);

			return wp_kses(
				$html,
				[
					'a' => [
						'class' => true,
						'href'  => true,
					],
				]
			);
		}

		/**
		 * Same line layout as {@see render_location_assignment_summary()} when no menu is assigned:
		 * "Assigned menu:" + link to Appearance > Menus (Manage Locations).
		 *
		 * @return string Safe HTML.
		 */
		private function render_location_unassigned_menu_prompt() {
			$href = admin_url( 'nav-menus.php?action=locations' );
			$html = sprintf(
				'%s <a class="mega-location__assigned-link" href="%s">%s</a>',
				esc_html__( 'Assigned menu:', 'megamenu' ),
				esc_url( $href ),
				esc_html__( 'Assign a menu', 'megamenu' )
			);

			return wp_kses(
				$html,
				[
					'a' => [
						'class' => true,
						'href'  => true,
					],
				]
			);
		}


		/**
		 * Output the HTML for a location row (card layout on Mega Menu > Menu Locations).
		 *
		 * @since  2.8
		 * @param  array  $locations      All registered menu locations.
		 * @param  string $location       The current location identifier.
		 * @param  string $description    Human-readable location description.
		 * @param  array  $saved_settings Saved plugin settings.
		 * @param  string $cards_context  `page` (Mega Menu > Menu Locations) or `meta` (Appearance > Menus meta box).
		 * @return void
		 */
		public function show_location_row( $locations, $location, $description, $saved_settings, $cards_context = 'page' ) {
			$is_enabled_class = 'mega-location-disabled';

			if ( max_mega_menu_is_enabled( $location ) ) {
				$is_enabled_class = 'mega-location-enabled';
			} elseif ( ! has_nav_menu( $location ) ) {
				$is_enabled_class .= ' mega-location-disabled-assign-menu';
			}

			$loc    = Mega_Menu_Location::find( $location );
			$mmm_on = $loc ? $loc->is_enabled() : false;

			$has_active_location_class = '';

			$active_instance = 0;

			if ( isset( $saved_settings[ $location ]['active_instance'] ) ) {
				$active_instance = $saved_settings[ $location ]['active_instance'];
			} elseif ( isset( $saved_settings['instances'][ $location ] ) ) {
				$active_instance = $saved_settings['instances'][ $location ];
			}

			if ( $active_instance > 0 ) {
				$has_active_location_class = ' mega-has-active-location';
			}

			$mmm_row_class = $mmm_on ? 'mega-location-mmm-on' : 'mega-location-mmm-off';

			?>

			<div class="postbox mega-location <?php echo esc_attr( $is_enabled_class ); ?> <?php echo esc_attr( $mmm_row_class ); ?><?php echo esc_attr( $has_active_location_class ); ?>" data-mega-location="<?php echo esc_attr( $location ); ?>" data-has-nav-menu="<?php echo has_nav_menu( $location ) ? '1' : '0'; ?>" data-mmm-plain-label="<?php echo esc_attr( $description ); ?>">
				<div class="mega-inside">
					<header class="mega-location__header">
						<div class="mega-location__header-row">
							<h2 class="mega-location__title<?php echo ( 'page' === $cards_context && strpos( $location, 'max_mega_menu_' ) === 0 ) ? ' mega-location__title--editable' : ''; ?>">
								<span class="dashicons dashicons-location mega-location__title-icon" aria-hidden="true"></span>
								<?php if ( 'page' === $cards_context && strpos( $location, 'max_mega_menu_' ) === 0 ) : ?>
									<span class="mega-location__title-cluster">
										<span class="mega-location__title-display">
											<span class="mega-location__title-text"><?php echo esc_html( $description ); ?></span>
											<span class="mega-location__title-edit-field" hidden>
												<?php
												$card_title_input_id = 'mega-location-card-title-' . sanitize_key( $location );
												?>
												<label class="screen-reader-text" for="<?php echo esc_attr( $card_title_input_id ); ?>"><?php esc_html_e( 'Location name', 'megamenu' ); ?></label>
												<input type="text" id="<?php echo esc_attr( $card_title_input_id ); ?>" class="mega-location-card-title-input regular-text" data-mega-location="<?php echo esc_attr( $location ); ?>" value="<?php echo esc_attr( $description ); ?>" autocomplete="off" />
											</span>
										</span>
										<button type="button" class="mega-location__title-edit" aria-label="<?php esc_attr_e( 'Edit location name', 'megamenu' ); ?>">
											<span class="dashicons dashicons-edit" aria-hidden="true"></span>
										</button>
									</span>
								<?php else : ?>
									<span class="mega-location__title-text"><?php echo esc_html( $description ); ?></span>
								<?php endif; ?>
							</h2>
							<div class="mega-location__header-actions">
								<?php
								if ( strpos( $location, 'max_mega_menu_' ) !== false ) {
									echo '<span class="delete mega-location__delete">' . self::delete_location_link( $location ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								echo self::render_mmm_enable_toggle( $location, $mmm_on, 'loc-' . $location, ! has_nav_menu( $location ) );
								?>
							</div>
						</div>
					</header>
					<div class="mega-location__meta">
						<?php if ( $active_instance > 0 ) : ?>
						<p class="description mega-location__description"><?php echo esc_html( sprintf( __( 'Active for instance %d.', 'megamenu' ), (int) $active_instance ) ); ?></p>
						<?php endif; ?>
						<?php if ( has_nav_menu( $location ) ) : ?>
						<p class="description mega-location__assigned"><?php echo $this->render_location_assignment_summary( $location ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<?php else : ?>
						<p class="description mega-location__assigned"><?php echo $this->render_location_unassigned_menu_prompt(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<?php endif; ?>
					</div>
					<footer class="mega-location__footer">
						<div class="wp-media-buttons">
							<?php
							$preview_inactive = ! Mega_Menu_Preview::is_previewable( $location )
								|| ! max_mega_menu_is_enabled( $location );
							echo self::render_location_settings_trigger(
								$location,
								$description,
								[
									'requires_menu' => ! has_nav_menu( $location ),
								]
							);
							echo Mega_Menu_Preview::render_preview_link(
								$location,
								$description,
								[
									'inactive'     => $preview_inactive,
									'mega_tooltip' => false,
								]
							);
							?>
						</div>
					</footer>
				</div>
			</div>
			<?php
		}

		/**
		 * Render the Add Menu Location form page.
		 *
		 * @since  2.8
		 * @return void
		 */
		public function add_location_page() {
			?>

			<div class='menu_settings menu_settings_add_location'>

				<h3 class='first'><?php esc_html_e( 'Add Menu Location', 'megamenu' ); ?></h3>

				<form action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<input type="hidden" name="action" value="megamenu_add_menu_location" />
					<?php wp_nonce_field( 'megamenu_add_menu_location' ); ?>

					<table class="mmm-settings-table">
						<tr>
							<td class='mega-name'>
								<div class='mega-name-title'><?php esc_html_e( 'Location Name', 'megamenu' ); ?></div>
								<div class='mega-description'>
									<p><?php esc_html_e( 'Give the location a name that describes where the menu will be displayed on your site.', 'megamenu' ); ?></p>
								</div>
							</td>
							<td class='mega-value mega-vertical-align-top'>
								<input class='wide' type='text' name='title' required='required' placeholder='<?php esc_attr_e( 'E.g. Footer, Blog Sidebar, Header', 'megamenu' ); ?>' />
							</td>
						</tr>
						<tr>
							<td class='mega-name'>
								<div class='mega-name-title'><?php esc_html_e( 'Assign a menu', 'megamenu' ); ?></div>
								<div class='mega-description'>
									<p><?php esc_html_e( 'Select a menu to be assigned to this location. This can be changed later using the Appearance > Menus > Manage Location page.', 'megamenu' ); ?></p>
								</div>
							</td>
							<td class='mega-value mega-vertical-align-top'>
								<?php

								$menus = wp_get_nav_menus();

								if ( count( $menus ) ) {
									foreach ( $menus as $menu ) {
										echo '<div class="mega-radio-row"><input type="radio" id="' . esc_attr( $menu->slug ) . '" name="menu_id" value="' . esc_attr( $menu->term_id ) . '" /><label for="' . esc_attr( $menu->slug ) . '">' . esc_attr( $menu->name ) . '</label></div>';
									}
								}

								echo '<div class="mega-radio-row"><input checked="checked" type="radio" id="0" name="menu_id" value="0" /><label for="0">' . esc_html__( "Skip - I'll assign a menu later", 'megamenu' ) . '</label></div>';
								?>
							</td>
						</tr>
					</table>
					<?php echo get_submit_button( __( 'Add menu location', 'megamenu' ), 'primary button-compact', 'submit', true ); ?>
				</form>
			</div>

			<?php
		}


		/**
		 * Return a link showing the menu assigned to the specified location.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @return string HTML anchor element.
		 */
		public function assigned_menu_link( $location ) {
			$menu_id = $this->get_menu_id_for_location( $location );

			$icon_span = '<span class="dashicons dashicons-menu-alt2"></span>';

			if ( $menu_id ) {
				$href = admin_url( "nav-menus.php?action=edit&menu={$menu_id}" );
				$name = $this->get_menu_name_for_location( $location );
			} else {
				$href = admin_url( 'nav-menus.php?action=locations' );
				$name = __( 'Assign a menu', 'megamenu' );
			}

			$label = esc_html(
				sprintf(
					/* translators: %s: assigned navigation menu title, or the phrase "Assign a menu" when none is set. */
					__( 'Assigned Menu: %s', 'megamenu' ),
					$name
				)
			);

			$inner     = $icon_span . '<span class="mega-location-assigned-menu-label">' . $label . '</span>';
			$processor   = new WP_HTML_Tag_Processor( '<a>' . $inner . '</a>' );

			if ( $processor->next_tag( 'a' ) ) {
				$processor->set_attribute( 'href', esc_url( $href ) );
				$processor->set_attribute( 'class', 'mega-location-assigned-menu-link' );
			}

			return $processor->get_updated_html();
		}

		/**
		 * Return a button to delete the specified custom menu location.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @return string HTML button element.
		 */
		public static function delete_location_link( $location ) {
			$delete_label = __( 'Delete location', 'megamenu' );
			$processor    = new WP_HTML_Tag_Processor( '<button type="button"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button>' );

			if ( $processor->next_tag( 'button' ) ) {
				$processor->set_attribute( 'type', 'button' );
				$processor->set_attribute( 'class', 'mega-location-delete-link' );
				$processor->set_attribute( 'data-location', $location );
				$processor->set_attribute( 'aria-label', esc_attr( $delete_label ) );
				$processor->set_attribute( 'title', esc_attr( $delete_label ) );
			}

			return $processor->get_updated_html();
		}

		/**
		 * Render the settings form for a specific menu location (tabs + fields).
		 *
		 * Output is used only in the location settings modal (AJAX save via {@see ajax_save_location_settings()}).
		 *
		 * @since  2.8
		 * @param  array  $all_locations All registered menu locations.
		 * @param  string $location      Location identifier.
		 * @param  string $description   Human-readable location description.
		 * @return void
		 */
		public function render_location_settings_form( $all_locations, $location, $description ) {

			$is_custom_location = strpos( $location, 'max_mega_menu_' ) !== false;
			$plugin_settings    = get_option( 'megamenu_settings' );
			$location_settings  = isset( $plugin_settings[ $location ] ) ? $plugin_settings[ $location ] : [];

			?>

			<form class="megamenu-location-settings-dialog-form" method="post" action="#" data-location="<?php echo esc_attr( $location ); ?>" data-custom-location="<?php echo $is_custom_location ? '1' : '0'; ?>">
				<?php wp_nonce_field( 'megamenu_edit', 'nonce' ); ?>
				<input type="hidden" name="location" value="<?php echo esc_attr( $location ); ?>" />
				<?php

					$settings = apply_filters(
						'megamenu_location_settings',
						[

							'general'        => [
								'priority' => 10,
								'title'    => __( 'Desktop', 'megamenu' ),
								'settings' => [
									'event'  => [
										'priority'    => 10,
										'title'       => __( 'Event', 'megamenu' ),
										'description' => __( 'Select the event to trigger sub menus', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'event',
												'key'   => 'event',
												'value' => isset( $location_settings['event'] ) ? $location_settings['event'] : 'hover',
											],
										],
									],
									'effect' => [
										'priority'    => 20,
										'title'       => __( 'Sub Menu Effect', 'megamenu' ),
										'description' => __( 'Select the desktop sub menu animation type', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'effect',
												'key'   => 'effect',
												'value' => isset( $location_settings['effect'] ) ? $location_settings['effect'] : 'fade_up',
												'title' => __( 'Animation', 'megamenu' ),
											],
											[
												'type'  => 'effect_speed',
												'key'   => 'effect_speed',
												'value' => isset( $location_settings['effect_speed'] ) ? $location_settings['effect_speed'] : '200',
												'title' => __( 'Speed', 'megamenu' ),
											],
										],
									],
								],
							],
							'mobile'         => [
								'priority' => 12,
								'title'    => __( 'Mobile', 'megamenu' ),
								'settings' => [
									'effect_mobile'    => [
										'priority'    => 10,
										'title'       => __( 'Mobile Menu', 'megamenu' ),
										'description' => __( 'Choose a style for your mobile menu', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'effect_mobile',
												'key'   => 'effect_mobile',
												'value' => isset( $location_settings['effect_mobile'] ) ? $location_settings['effect_mobile'] : 'slide_right',
												'title' => __( 'Type', 'megamenu' ),
											],
											[
												'type'  => 'effect_mobile_direction',
												'key'   => 'effect_mobile_direction',
												'value' => isset( $location_settings['effect_mobile_direction'] ) ? $location_settings['effect_mobile_direction'] : 'vertical',
												'title' => __( 'Submenu Style', 'megamenu' ),
											],
											[
												'type'  => 'effect_speed_mobile',
												'key'   => 'effect_speed_mobile',
												'value' => isset( $location_settings['effect_speed_mobile'] ) ? $location_settings['effect_speed_mobile'] : '200',
												'title' => __( 'Speed', 'megamenu' ),
											],
										],
									],
									'mobile_behaviour' => [
										'priority'    => 20,
										'title'       => __( 'Sub Menu Behaviour', 'megamenu' ),
										'description' => __( 'Define the sub menu toggle behaviour for the mobile menu.', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'mobile_behaviour',
												'key'   => 'mobile_behaviour',
												'value' => $plugin_settings,
											],
										],
									],
									'mobile_state'     => [
										'priority'    => 30,
										'title'       => __( 'Sub Menu Default State', 'megamenu' ),
										'description' => __( 'Define the default state of the sub menus when the mobile menu is visible.', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'mobile_state',
												'key'   => 'mobile_state',
												'value' => $plugin_settings,
											],
										],
									],
								],
							],
							'theme'          => [
								'priority' => 15,
								'title'    => __( 'Theme', 'megamenu' ),
								'settings' => [
									'theme' => [
										'priority'    => 10,
										'title'       => __( 'Menu Theme', 'megamenu' ),
										'description' => __( 'Choose a menu theme to be applied to this menu location.', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'theme_selector',
												'key'   => 'theme',
												'value' => isset( $location_settings['theme'] ) ? $location_settings['theme'] : 'default',
											],
										],
									],
								],
							],
							'advanced'       => [
								'priority' => 25,
								'title'    => __( 'Advanced', 'megamenu' ),
								'settings' => [
									'click_behaviour'  => [
										'priority'    => 10,
										'title'       => __( 'Click Event Behaviour', 'megamenu' ),
										'description' => __( "Define what should happen when the event is set to 'click'. This also applies to mobiles.", 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'click_behaviour',
												'key'   => 'click_behaviour',
												'value' => $plugin_settings,
											],
										],
									],
									'descriptions'     => [
										'priority'    => 20,
										'title'       => __( 'Menu Item Descriptions', 'megamenu' ),
										'description' => __( 'Enable output of menu item descriptions.', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'descriptions',
												'key'   => 'descriptions',
												'value' => $plugin_settings,
											],
										],
									],
									'unbind'           => [
										'priority'    => 20,
										'title'       => __( 'Unbind JavaScript Events', 'megamenu' ),
										'description' => __( 'To avoid conflicts with theme menu systems, JavaScript events which have been added to menu items will be removed by default.', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'unbind',
												'key'   => 'unbind',
												'value' => $plugin_settings,
											],
										],
									],
									'prefix'           => [
										'priority'    => 20,
										'title'       => __( 'Prefix Menu Item Classes', 'megamenu' ),
										'description' => __( "Prefix custom menu item classes with 'mega-'?", 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'prefix',
												'key'   => 'prefix',
												'value' => $plugin_settings,
											],
										],
									],
									'container'        => [
										'priority'    => 20,
										'title'       => __( 'Container', 'megamenu' ),
										'description' => __( 'Use nav or div element for menu wrapper?', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'container',
												'key'   => 'container',
												'value' => $plugin_settings,
											],
										],
									],
									'active_instance'  => [
										'priority'    => 30,
										'title'       => __( 'Active Menu Instance', 'megamenu' ),
										'info'        => [ __( '0: Apply to all instances. 1: Apply to first instance. 2: Apply to second instance', 'megamenu' ) . '…' ],
										'description' => __( 'Some themes will output this menu location multiple times on the same page. For example, it may be output once for the main menu, then again for the mobile menu. This setting can be used to make sure Max Mega Menu is only applied to one of those instances.', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'active_instance',
												'key'   => 'active_instance',
												'value' => $plugin_settings,
											],
										],
									],
								],
							],
							'output_options' => [
								'priority' => 30,
								'title'    => __( 'Display Options', 'megamenu' ),
								'settings' => [
									'location_block' => [
										'priority'    => 10,
										'title'       => __( 'Block (Gutenberg)', 'megamenu' ),
										'description' => __( 'Display this menu location in any block supported area.', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'location_block',
												'key'   => 'location_block',
												'value' => $location,
											],
										],
									],
									'location_php_function' => [
										'priority'    => 10,
										'title'       => __( 'PHP Function', 'megamenu' ),
										'description' => __( 'Display this menu location in a theme template (usually header.php).', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'location_php_function',
												'key'   => 'location_php_function',
												'value' => $location,
											],
										],
									],
									'location_shortcode' => [
										'priority'    => 20,
										'title'       => __( 'Shortcode', 'megamenu' ),
										'description' => __( 'Display this menu location in a post or page.', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'location_shortcode',
												'key'   => 'location_shortcode',
												'value' => $location,
											],
										],
									],
									'location_widget'    => [
										'priority'    => 30,
										'title'       => __( 'Widget', 'megamenu' ),
										'description' => __( 'Display this menu location in a widget area.', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'location_widget',
												'key'   => 'location_widget',
												'value' => $location,
											],
										],
									],
								],
							],
						],
						$location,
						$plugin_settings
					);

				$initial_version = get_option( 'megamenu_initial_version' );

				if ( $initial_version && version_compare( $initial_version, '3.9.2', '>=' ) ) {
					unset( $settings['advanced']['settings']['prefix'] );
				}

				echo "<div class='megamenu-dialog-rail'>";

				echo '<div class="megamenu-dialog-tablist mega-tablist" role="tablist">';

				$is_first = true;

				uasort( $settings, [ $this, 'compare_elems' ] );

				foreach ( $settings as $section_id => $section ) {

					if ( $is_first ) {
						$active   = 'is-active';
						$is_first = false;
					} else {
						$active = '';
					}

					$loc_tab_processor = new WP_HTML_Tag_Processor( '<button type="button">' . esc_html( $section['title'] ) . '</button>' );

					if ( $loc_tab_processor->next_tag( 'button' ) ) {
						$loc_tab_processor->set_attribute( 'class', 'megamenu-dialog-tab ' . trim( $active ) );
						$loc_tab_processor->set_attribute( 'data-tab', 'mega-tab-content-' . $section_id );
						$loc_tab_processor->set_attribute( 'data-tab-section', sanitize_key( (string) $section_id ) );
					}

					echo $loc_tab_processor->get_updated_html();

				}

				echo '</div>';

				echo '<div class="megamenu-dialog-panels">';

				$is_first = true;

				foreach ( $settings as $section_id => $section ) {

					if ( $is_first ) {
							$display  = 'block';
							$is_first = false;
					} else {
						$display = 'none';
					}

						echo "<div class='mega-tab-content mega-tab-content-{$section_id}' style='display: {$display}'>";

					if ( $section_id == 'output_options' && ! $is_custom_location ) {
						echo '<div class="notice notice-warning inline"><p>';
						echo esc_html__( 'This menu location is registered by your theme. Your theme should already include the code required to display this menu location on your site.', 'megamenu' );
						echo '</p></div>';
					}

						echo "<table class='{$section_id} mmm-settings-table'>";

						// order the fields by priority
						uasort( $section['settings'], [ $this, 'compare_elems' ] );

					foreach ( $section['settings'] as $group_id => $group ) {

						echo "<tr class='" . esc_attr( 'mega-' . $group_id ) . "'>";

						if ( isset( $group['settings'] ) ) {

							echo "<td class='mega-name'><div class='mega-name-title'>";
							if ( isset( $group['icon'] ) ) {
								echo "<span class='dashicons dashicons-" . esc_html( $group['icon'] ) . "'></span>";
							}
							echo esc_html( $group['title'] );
							echo "</div><div class='mega-description'>" . esc_html( $group['description'] ) . '</div></td>';
							echo "<td class='mega-value'>";

							foreach ( $group['settings'] as $setting_id => $setting ) {

								$pill_field_types = [ 'checkbox', 'checkbox_enabled', 'descriptions', 'prefix', 'unbind' ];
								$use_pill_wrapper = in_array( $setting['type'], $pill_field_types, true );

								if ( $use_pill_wrapper ) {
									echo '<div class="' . esc_attr( 'mega-' . $setting['key'] . ' mmm-settings-pill-field' ) . '">';
									echo '<label class="mmm-settings-pill-field-label">';
								} else {
									echo '<label class="' . esc_attr( 'mega-' . $setting['key'] ) . '">';
								}

								if ( isset( $setting['title'] ) ) {
									echo "<span class='mega-short-desc'>" . esc_html( $setting['title'] ) . '</span>';
								}

								switch ( $setting['type'] ) {
									case 'freetext':
										$this->print_location_freetext_option( $location, $setting['key'], $setting['value'] );
										break;
									case 'textarea':
										$ta_val = isset( $setting['value'] ) ? $setting['value'] : null;
										if ( ! is_string( $ta_val ) ) {
											$ps = get_option( 'megamenu_settings', [] );
											$ls = isset( $ps[ $location ] ) ? $ps[ $location ] : [];
											$ta_val = isset( $ls[ $setting['key'] ] ) ? (string) $ls[ $setting['key'] ] : '';
										}
										echo '<textarea class="' . esc_attr( 'mega-setting-' . $setting['key'] ) . '" name="' . esc_attr( 'megamenu_meta[' . $location . '][' . $setting['key'] . ']' ) . '">' . esc_textarea( stripslashes( $ta_val ) ) . '</textarea>';
										break;
									case 'checkbox_enabled':
										$this->print_location_enabled_option( $location, $setting['key'], $setting['value'] );
										break;
									case 'event':
										$this->print_location_event_option( $location, $setting['key'], $setting['value'] );
										break;
									case 'effect':
										$this->print_location_effect_option( $location, $setting['key'], $setting['value'] );
										break;
									case 'effect_speed':
										$this->print_location_effect_speed_option( $location, $setting['key'], $setting['value'] );
										break;
									case 'effect_mobile':
										$this->print_location_effect_mobile_option( $location, $setting['key'], $setting['value'] );
										break;
									case 'effect_mobile_direction':
										$this->print_location_effect_mobile_direction_option( $location, $setting['key'], $setting['value'] );
										break;
									case 'effect_speed_mobile':
										$this->print_location_effect_speed_mobile_option( $location, $setting['key'], $setting['value'] );
										break;
									case 'theme_selector':
										$this->print_location_theme_selector_option( $location, $setting['key'], $setting['value'] );
										break;
									case 'location_description':
										$this->print_location_description_option( $location, $setting['key'], $setting['value'] );
										break;
									case 'checkbox':
										$this->print_location_checkbox_option( $location, $setting['key'], $setting['value'] );
										break;
									case 'location_block':
										$this->print_location_block_option( $location, $setting['key'], $setting['value'] );
										break;
									case 'location_php_function':
										$this->print_location_php_function_option( $location, $setting['value'] );
										break;
									case 'location_shortcode':
										$this->print_location_shortcode_option( $location, $setting['value'] );
										break;
									case 'location_widget':
										$this->print_location_widget_option( $location, $setting['key'], $setting['value'] );
										break;
									case 'active_instance':
										$this->print_active_instance_option( $location, $setting['value'] );
										break;
									case 'click_behaviour':
										$this->print_click_behaviour_option( $location, $setting['value'] );
										break;
									case 'mobile_behaviour':
										$this->print_mobile_behaviour_option( $location, $setting['value'] );
										break;
									case 'mobile_state':
										$this->print_mobile_state_option( $location, $setting['value'] );
										break;
									case 'container':
										$this->print_container_option( $location, $setting['value'] );
										break;
									case 'descriptions':
										$this->print_descriptions_option( $location, $setting['value'] );
										break;
									case 'unbind':
										$this->print_unbind_option( $location, $setting['value'] );
										break;
									case 'prefix':
										$this->print_prefix_option( $location, $setting['value'] );
										break;
									default:
										do_action( "megamenu_print_location_option_{$setting['type']}", $setting['key'], $location );
										break;
								}

								if ( $use_pill_wrapper ) {
									echo '</label></div>';
								} else {
									echo '</label>';
								}

							}

							if ( isset( $group['info'] ) ) {
								foreach ( $group['info'] as $paragraph ) {
									echo '<div class="mega-info">' . esc_html( $paragraph ) . '</div>';
								}
							}

							echo '</td>';
						} else {
							echo '<td colspan="2"><h5>' . esc_html( $group['title'] ) . '</h5></td>';
						}
						echo '</tr>';

					}

					if ( $section_id == 'general' ) {
						do_action( 'megamenu_settings_table', $location, $plugin_settings );
					}

						echo '</table>';
						echo '</div>';
				}

				echo '</div>';

				?>
				
				</div>
			</form>

			<?php
		}


		/**
		 * Return a list of all registered menu locations, including custom MMM locations.
		 *
		 * @since  2.8
		 * @return array Map of location identifier to description.
		 */
		public function get_registered_locations() {
			$all_locations = get_registered_nav_menus();

			// PolyLang - remove auto created/translated menu locations
			if ( function_exists( 'pll_default_language' ) ) {
				$default_lang = pll_default_language( 'name' );

				foreach ( $all_locations as $loc => $description ) {
					if ( false !== strpos( $loc, '___' ) ) {
						// Remove locations created by Polylang
						unregister_nav_menu( $loc );
					} else {
						// Remove the language name appended to the original locations
						register_nav_menu( $loc, str_replace( ' ' . $default_lang, '', $description ) );
					}
				}

				$all_locations = get_registered_nav_menus();
			}

			$locations        = [];
			$custom_locations = get_option( 'megamenu_locations' );

			if ( is_array( $custom_locations ) ) {
				$all_locations = array_merge( $custom_locations, $all_locations );
			}

			if ( count( $all_locations ) ) {
				$megamenu_locations = [];

				// reorder locations so custom MMM locations are listed at the bottom
				foreach ( $all_locations as $location => $val ) {
					if ( strpos( $location, 'max_mega_menu_' ) === false ) {
						$locations[ $location ] = $val;
					} else {
						$megamenu_locations[ $location ] = $val;
					}
				}

				$locations = array_merge( $locations, $megamenu_locations );
			}

			return $locations;
		}


		/**
		 * Returns the menu ID for a specified menu location, defaults to 0.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @return int Menu term ID, or 0 if no menu is assigned.
		 */
		private function get_menu_id_for_location( $location ) {
			$locations = get_nav_menu_locations();
			$id        = isset( $locations[ $location ] ) ? $locations[ $location ] : 0;
			return $id;
		}


		/**
		 * Returns the menu name for a specified menu location.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @return string|false Menu name, or false if no menu is assigned.
		 */
		private function get_menu_name_for_location( $location ) {
			$id = $this->get_menu_id_for_location( $location );

			$menus = wp_get_nav_menus();

			foreach ( $menus as $menu ) {
				if ( $menu->term_id == $id ) {
					return $menu->name;
				}
			}

			return false;
		}


		/**
		 * Display messages to the user.
		 *
		 * @since  2.0
		 * @return void
		 */
		public function print_messages() {
			if ( isset( $_GET['location_added'] ) ) {
				$first_mmm_location = isset( $_GET['first_mmm_location'] )
					&& '1' === sanitize_text_field( wp_unslash( $_GET['first_mmm_location'] ) );
				$block_theme        = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'New Menu Location Created', 'megamenu' ); ?></p>
					<?php if ( $first_mmm_location && $block_theme ) : ?>
						<p><?php esc_html_e( 'Congratulations on adding your first menu location! Next, you need to display the Menu Location by adding a Max Mega Menu block to your site. Other display options for the menu location (shortcode, widget or PHP code) can be found by clicking the Location Settings button and opening the "Display Options" tab.', 'megamenu' ); ?></p>
					<?php endif; ?>
				</div>
				<?php
			}

			if ( isset( $_GET['delete_location'] ) ) {
				?>
				<div class="notice notice-success is-dismissible"> 
					<p><?php _e( 'Menu Location Deleted', 'megamenu' ) ?></p>
				</div>
				<?php
			}
		}


		/**
		 * Pill toggle control only (Gutenberg-style components-form-toggle) for the location settings modal.
		 * Call inside `<label class="mmm-settings-pill-field-label">` after `.mega-short-desc` (see settings table loop).
		 *
		 * @param string $location     Location slug.
		 * @param string $field_key    Key under megamenu_meta[ location ].
		 * @param string $submit_value Value submitted when the control is checked.
		 * @param bool   $is_checked   Whether the control is on.
		 * @return void
		 */
		private function print_location_dialog_pill_checkbox( $location, $field_key, $submit_value, $is_checked ) {
			$id = 'mega-location-pill-' . sanitize_key( $location ) . '-' . sanitize_key( $field_key );

			$toggle_classes = 'components-form-toggle';
			if ( $is_checked ) {
				$toggle_classes .= ' is-checked';
			}
			?>
				<span class="<?php echo esc_attr( $toggle_classes ); ?>">
					<input
						type="checkbox"
						id="<?php echo esc_attr( $id ); ?>"
						class="components-form-toggle__input"
						role="switch"
						name="<?php echo esc_attr( 'megamenu_meta[' . $location . '][' . $field_key . ']' ); ?>"
						value="<?php echo esc_attr( $submit_value ); ?>"
						<?php checked( $is_checked ); ?>
					/>
					<span class="components-form-toggle__track" aria-hidden="true"></span>
					<span class="components-form-toggle__thumb" aria-hidden="true"></span>
				</span>
			<?php
		}


		/**
		 * Print a checkbox option for enabling/disabling MMM for a specific location.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @param  string $key      Setting key.
		 * @param  string $value    Current setting value.
		 * @return void
		 */
		public function print_location_enabled_option( $location, $key, $value ) {
			$is_on = ( '1' === (string) $value || 1 === $value || true === $value );
			$this->print_location_dialog_pill_checkbox( $location, $key, '1', $is_on );
		}


		/**
		 * Print a generic checkbox option.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @param  string $key      Setting key.
		 * @param  string $value    Current setting value.
		 * @return void
		 */
		public function print_location_checkbox_option( $location, $key, $value ) {
			$on = ( 'true' === (string) $value || true === $value );
			$this->print_location_dialog_pill_checkbox( $location, $key, 'true', $on );
		}


		/**
		 * Print the active instance option.
		 *
		 * @since  2.8
		 * @param  string $location        Location identifier.
		 * @param  array  $plugin_settings Saved plugin settings.
		 * @return void
		 */
		public function print_active_instance_option( $location, $plugin_settings ) {
			$active_instance = 0;

			if ( isset( $plugin_settings[ $location ]['active_instance'] ) ) {
				$active_instance = $plugin_settings[ $location ]['active_instance'];
			} elseif ( isset( $plugin_settings['instances'][ $location ] ) ) {
				$active_instance = $plugin_settings['instances'][ $location ];
			}

			?>
				<input type='text' name='megamenu_meta[<?php echo esc_attr( $location ); ?>][active_instance]' value='<?php echo esc_attr( $active_instance ); ?>' />
			<?php
		}

		/**
		 * Print the click behaviour option.
		 *
		 * @since  2.8
		 * @param  string $location        Location identifier.
		 * @param  array  $plugin_settings Saved plugin settings.
		 * @return void
		 */
		public function print_click_behaviour_option( $location, $plugin_settings ) {
			$second_click = 'go';

			if ( isset( $plugin_settings[ $location ]['second_click'] ) ) {
				$second_click = $plugin_settings[ $location ]['second_click'];
			} elseif ( isset( $plugin_settings['second_click'] ) ) {
				$second_click = $plugin_settings['second_click'];
			}

			?>
				<select name='megamenu_meta[<?php echo esc_attr( $location ); ?>][second_click]'>
					<option value='close' <?php echo selected( $second_click == 'close' ); ?>><?php _e( 'First click will open the sub menu, second click will close the sub menu.', 'megamenu' ); ?></option>
					<option value='go' <?php echo selected( $second_click == 'go' ); ?>><?php _e( 'First click will open the sub menu, second click will follow the link.', 'megamenu' ); ?></option>
					<option value='disabled' <?php echo selected( $second_click == 'disabled' ); ?>><?php _e( 'First click will follow the link (the arrow must be used to toggle sub menu visiblity).', 'megamenu' ); ?></option>
				</select>
			<?php
		}


		/**
		 * Print the mobile menu behaviour option.
		 *
		 * @since  2.8
		 * @param  string $location        Location identifier.
		 * @param  array  $plugin_settings Saved plugin settings.
		 * @return void
		 */
		public function print_mobile_behaviour_option( $location, $plugin_settings ) {
			$mobile_behaviour = 'standard';

			if ( isset( $plugin_settings[ $location ]['mobile_behaviour'] ) ) {
				$mobile_behaviour = $plugin_settings[ $location ]['mobile_behaviour'];
			} elseif ( isset( $plugin_settings['mobile_behaviour'] ) ) {
				$mobile_behaviour = $plugin_settings['mobile_behaviour'];
			}

			?>
				<select name='megamenu_meta[<?php echo esc_attr( $location ); ?>][mobile_behaviour]'>
					<option value='standard' <?php echo selected( $mobile_behaviour == 'standard' ); ?>><?php _e( 'Standard - Open sub menus will remain open until closed by the user.', 'megamenu' ); ?></option>
					<option value='accordion' <?php echo selected( $mobile_behaviour == 'accordion' ); ?>><?php _e( 'Accordion - Open sub menus will automatically close when another one is opened.', 'megamenu' ); ?></option>
				</select>
			<?php
		}

		/**
		 * Print the mobile sub menu default state option.
		 *
		 * @since  2.8
		 * @param  string $location        Location identifier.
		 * @param  array  $plugin_settings Saved plugin settings.
		 * @return void
		 */
		public function print_mobile_state_option( $location, $plugin_settings ) {
			$mobile_state = 'collapse_all';

			if ( isset( $plugin_settings[ $location ]['mobile_state'] ) ) {
				$mobile_state = $plugin_settings[ $location ]['mobile_state'];
			}

			?>
				<select name='megamenu_meta[<?php echo esc_attr( $location ); ?>][mobile_state]'>
					<option value='collapse_all' <?php echo selected( $mobile_state == 'collapse_all' ); ?>><?php _e( 'Collapse all', 'megamenu' ); ?></option>
					<option value='expand_all' <?php echo selected( $mobile_state == 'expand_all' ); ?>><?php _e( 'Expand all', 'megamenu' ); ?></option>
					<option value='expand_active' <?php echo selected( $mobile_state == 'expand_active' ); ?>><?php _e( 'Expand active parents', 'megamenu' ); ?></option>
				</select>
			<?php
		}


		/**
		 * Print the container option select box.
		 *
		 * @since  2.9
		 * @param  string $location        Location identifier.
		 * @param  array  $plugin_settings Saved plugin settings.
		 * @return void
		 */
		public function print_container_option( $location, $plugin_settings ) {
			$container = 'div';

			if ( isset( $plugin_settings[ $location ]['container'] ) ) {
				$container = $plugin_settings[ $location ]['container'];
			}

			?>
				<select name='megamenu_meta[<?php echo esc_attr( $location ); ?>][container]'>
					<option value='div' <?php echo selected( $container == 'div' ); ?>>&lt;div&gt;</option>
					<option value='nav' <?php echo selected( $container == 'nav' ); ?>>&lt;nav&gt;</option>
				</select>
			<?php
		}


		/**
		 * Print the checkbox option for enabling menu item descriptions.
		 *
		 * @since  2.8
		 * @param  string $location        Location identifier.
		 * @param  array  $plugin_settings Saved plugin settings.
		 * @return void
		 */
		public function print_descriptions_option( $location, $plugin_settings ) {
			$descriptions = 'disabled';

			if ( isset( $plugin_settings[ $location ]['descriptions'] ) ) {
				$descriptions = $plugin_settings[ $location ]['descriptions'];
			} elseif ( isset( $plugin_settings['descriptions'] ) ) {
				$descriptions = $plugin_settings['descriptions'];
			}

			$this->print_location_dialog_pill_checkbox( $location, 'descriptions', 'enabled', 'enabled' === $descriptions );
		}


		/**
		 * Print the checkbox option for prefixing menu items with 'mega-'.
		 *
		 * @since  2.8
		 * @param  string $location        Location identifier.
		 * @param  array  $plugin_settings Saved plugin settings.
		 * @return void
		 */
		public function print_prefix_option( $location, $plugin_settings ) {
			$prefix = 'disabled';

			if ( isset( $plugin_settings[ $location ]['prefix'] ) ) {
				$prefix = $plugin_settings[ $location ]['prefix'];
			} elseif ( isset( $plugin_settings['prefix'] ) ) {
				$prefix = $plugin_settings['prefix'];
			}

			$this->print_location_dialog_pill_checkbox( $location, 'prefix', 'enabled', 'enabled' === $prefix );
		}


		/**
		 * Print the checkbox option for the Unbind JavaScript Events option.
		 *
		 * @since  2.8
		 * @param  string $location        Location identifier.
		 * @param  array  $plugin_settings Saved plugin settings.
		 * @return void
		 */
		public function print_unbind_option( $location, $plugin_settings ) {

			$unbind = 'enabled';

			if ( isset( $plugin_settings[ $location ]['unbind'] ) ) {
				$unbind = $plugin_settings[ $location ]['unbind'];
			} elseif ( isset( $plugin_settings['unbind'] ) ) {
				$unbind = $plugin_settings['unbind'];
			}

			$this->print_location_dialog_pill_checkbox( $location, 'unbind', 'enabled', 'enabled' === $unbind );
		}


		/**
		 * Print a select box containing all available sub menu trigger events.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @param  string $key      Setting key.
		 * @param  string $value    Current setting value.
		 * @return void
		 */
		public function print_location_event_option( $location, $key, $value ) {

			$options = apply_filters(
				'megamenu_event_options',
				[
					'hover'  => __( 'Hover Intent', 'megamenu' ),
					'hover_' => __( 'Hover', 'megamenu' ),
					'click'  => __( 'Click', 'megamenu' ),
				]
			);

			?>
			<select name='megamenu_meta[<?php echo esc_attr( $location ); ?>][<?php echo esc_attr( $key ); ?>]'>
			<?php

			foreach ( $options as $type => $name ) {
				echo "<option value='" . esc_attr( $type ) . "' " . selected( $value, $type, false ) . '>' . esc_html( $name ) . '</option>';
			}

			echo '</select>';

		}

		/**
		 * Print a select box containing all available sub menu animation options.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @param  string $key      Setting key.
		 * @param  string $value    Current setting value.
		 * @return void
		 */
		public function print_location_effect_option( $location, $key, $value ) {
			?>
			<select name='megamenu_meta[<?php echo esc_attr( $location ); ?>][<?php echo esc_attr( $key ); ?>]'>
			<?php

			$selected = strlen( $value ) ? $value : 'fade_up';

			$options = apply_filters(
				'megamenu_transition_effects',
				[
					'disabled' => [
						'label'    => __( 'None', 'megamenu' ),
						'selected' => $selected == 'disabled',
					],
					'fade'     => [
						'label'    => __( 'Fade', 'megamenu' ),
						'selected' => $selected == 'fade',
					],
					'fade_up'  => [
						'label'    => __( 'Fade Up', 'megamenu' ),
						'selected' => $selected == 'fade_up' || $selected == 'fadeUp',
					],
					'slide'    => [
						'label'    => __( 'Slide', 'megamenu' ),
						'selected' => $selected == 'slide',
					],
					'slide_up' => [
						'label'    => __( 'Slide Up', 'megamenu' ),
						'selected' => $selected == 'slide_up',
					],
				],
				$selected
			);

			foreach ( $options as $key => $value ) {
				echo "<option value='" . esc_attr( $key ) . "' " . selected( $value['selected'], true, false ) . '>' . esc_html( $value['label'] ) . '</option>';
			}

			echo '</select>';

		}


		/**
		 * Print a select box containing all available effect speeds (desktop).
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @param  string $key      Setting key.
		 * @param  string $value    Current setting value.
		 * @return void
		 */
		public function print_location_effect_speed_option( $location, $key, $value ) {
			?>
			<select name='megamenu_meta[<?php echo esc_attr( $location ); ?>][<?php echo esc_attr( $key ); ?>]'>
			<?php

			$selected = strlen( $value ) ? $value : '200';

			$options = apply_filters(
				'megamenu_effect_speed',
				[
					'600' => __( 'Slow', 'megamenu' ),
					'400' => __( 'Med', 'megamenu' ),
					'200' => __( 'Fast', 'megamenu' ),
				],
				$selected
			);

			ksort( $options );

			foreach ( $options as $key => $value ) {
				echo "<option value='" . esc_attr( $key ) . "' " . selected( $selected, $key, false ) . '>' . esc_html( $value ) . '</option>';
			}

			echo '</select>';

		}


		/**
		 * Print a select box containing the available mobile menu effect options.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @param  string $key      Setting key.
		 * @param  string $value    Current setting value.
		 * @return void
		 */
		public function print_location_effect_mobile_option( $location, $key, $value ) {
			?>
			<select name='megamenu_meta[<?php echo esc_attr( $location ); ?>][<?php echo esc_attr( $key ); ?>]'>
			<?php

			$selected = strlen( $value ) ? $value : 'slide_right';

			$options = apply_filters(
				'megamenu_transition_effects_mobile',
				[
					'disabled'    => [
						'label'    => __( 'None', 'megamenu' ),
						'selected' => $selected == 'disabled',
					],
					'slide'       => [
						'label'    => __( 'Slide Down', 'megamenu' ),
						'selected' => $selected == 'slide',
					],
					'slide_left'  => [
						'label'    => __( 'Off Canvas ←', 'megamenu' ),
						'selected' => $selected == 'slide_left',
					],
					'slide_right' => [
						'label'    => __( 'Off Canvas →', 'megamenu' ),
						'selected' => $selected == 'slide_right',
					],
				],
				$selected
			);

			foreach ( $options as $key => $value ) {
				echo "<option value='" . esc_attr( $key ) . "' " . selected( $value['selected'], true, false ) . '>' . esc_html( $value['label'] ) . '</option>';
			}

			echo '</select>';

		}

		/**
		 * Print a select box containing the available mobile sub menu direction options.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @param  string $key      Setting key.
		 * @param  string $value    Current setting value.
		 * @return void
		 */
		public function print_location_effect_mobile_direction_option( $location, $key, $value ) {
			?>
			<select name='megamenu_meta[<?php echo esc_attr( $location ); ?>][<?php echo esc_attr( $key ); ?>]'>
			<?php

			$selected = strlen( $value ) ? $value : 'vertical';

			$options = apply_filters(
				'megamenu_mobile_direction_options',
				[
					'vertical'    => [
						'label'    => __( 'Up / Down ↕', 'megamenu' ),
						'selected' => $selected == 'vertical',
						'disabled' => '',
					],
					/*'horizontal'       => array(
						'label'    => __( 'Left / Right ↔ (Pro)', 'megamenu' ),
						'selected' => $selected == 'horizontal',
						'disabled' => 'disabled',
					),*/
				],
				$selected
			);

			foreach ( $options as $key => $value ) {
				$disabled = isset($value['disabled']) && $value['disabled'] == 'disabled' ? 'disabled="disabled"' : '';
				echo "<option {$disabled} value='" . esc_attr( $key ) . "'" . selected( $value['selected'], true, false ) . ">" . esc_html( $value['label'] ) . "</option>";
			}

			echo '</select>';

		}


		/**
		 * Print a select box containing all available effect speeds (mobile).
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @param  string $key      Setting key.
		 * @param  string $value    Current setting value.
		 * @return void
		 */
		public function print_location_effect_speed_mobile_option( $location, $key, $value ) {
			?>
			<select name='megamenu_meta[<?php echo esc_attr( $location ); ?>][<?php echo esc_attr( $key ); ?>]'>
			<?php

			$selected = strlen( $value ) ? $value : '200';

			$options = apply_filters(
				'megamenu_effect_speed_mobile',
				[
					'600' => __( 'Slow', 'megamenu' ),
					'400' => __( 'Med', 'megamenu' ),
					'200' => __( 'Fast', 'megamenu' ),
				],
				$selected
			);

			ksort( $options );

			foreach ( $options as $key => $value ) {
				echo "<option value='" . esc_attr( $key ) . "' " . selected( $selected, $key, false ) . '>' . esc_html( $value ) . '</option>';
			}

			echo '</select>';

		}


		/**
		 * Print a select box containing all available menu themes.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @param  string $key      Setting key.
		 * @param  string $value    Currently selected theme ID.
		 * @return void
		 */
		public function print_location_theme_selector_option( $location, $key, $value ) {
			echo '<span class="megamenu-location-settings-dialog-theme-selector">';
			?>
			<select name='megamenu_meta[<?php echo esc_attr( $location ); ?>][<?php echo esc_attr( $key ); ?>]'>
			<?php

			$themes         = Mega_Menu_Theme::get_all();
			$selected_theme = strlen( $value ) ? $value : 'default';

			foreach ( $themes as $theme_id => $theme ) {
				$editor_url   = add_query_arg(
					[
						'page'  => 'maxmegamenu_theme_editor',
						'theme' => $theme_id,
					],
					admin_url( 'admin.php' )
				);
				$editor_attr = ' data-theme-editor-url="' . esc_url( $editor_url ) . '"';
				echo '<option value="' . esc_attr( $theme_id ) . '"' . $editor_attr . ' ' . selected( $selected_theme, $theme_id, false ) . '>' . esc_html( $theme->get( 'title' ) ) . '</option>';
			}

			echo '</select>';
			printf(
				'<button type="button" class="megamenu-location-settings-dialog-edit-theme" aria-label="%1$s"><span class="dashicons dashicons-external" aria-hidden="true"></span></button></span>',
				esc_attr__( 'Edit selected menu theme', 'megamenu' )
			);

		}


		/**
		 * Print the textarea containing the sample PHP code to output a menu location.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @param  string $value    Theme location value.
		 * @return void
		 */
		public function print_location_php_function_option( $location, $value ) {
			?>
			<textarea readonly="readonly">&lt;?php wp_nav_menu( array( 'theme_location' => '<?php echo esc_attr( $value ); ?>' ) ); ?&gt;</textarea>
			<?php
		}


		/**
		 * Print the textarea containing the sample shortcode to output a menu location.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @param  string $value    Theme location value.
		 * @return void
		 */
		public function print_location_shortcode_option( $location, $value ) {
			?>
			<textarea readonly="readonly">[maxmegamenu location=<?php echo esc_attr( $value ); ?>]</textarea>
			<?php
		}


		/**
		 * Print instructions on how to display this menu location using a widget.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @param  string $value    Theme location value.
		 * @return void
		 */
		public function print_location_widget_option( $location, $value ) {
			?>
			<textarea readonly="readonly"><?php _e( "Add the 'Max Mega Menu' widget to a widget area.", 'megamenu' ); ?></textarea>
			<?php
		}

		/**
		 * Print instructions on how to display this menu location using a Gutenberg block.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @param  string $value    Theme location value.
		 * @return void
		 */
		public function print_location_block_option( $location, $value ) {
			?>
			<textarea readonly="readonly"><?php _e( "Add the 'Max Mega Menu' block to any block enabled area.", 'megamenu' ); ?></textarea>
			<?php
		}


		/**
		 * Print a standard text input box.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @param  string $key      Setting key.
		 * @param  string $value    Current setting value.
		 * @return void
		 */
		public function print_location_freetext_option( $location, $key, $value ) {
			echo "<input class='" . esc_attr( 'mega-setting-' . $key ) . "' type='text' name='megamenu_meta[$location][$key]' value='" . esc_attr( $value ) . "' />";
		}


		/**
		 * Print a text input box allowing the user to change the name of a custom menu location.
		 *
		 * @since  2.8
		 * @param  string $location Location identifier.
		 * @param  string $key      Setting key.
		 * @param  string $value    Current location description.
		 * @return void
		 */
		public function print_location_description_option( $location, $key, $value ) {
			echo "<input class='" . esc_attr( 'mega-setting-' . $key ) . " wide' type='text' name='custom_location[$location]' value='" . esc_attr( $value ) . "' />";
		}


		/**
		 * Compare two elements by their priority key for usort.
		 *
		 * @since  2.8
		 * @param  array $elem1 First element.
		 * @param  array $elem2 Second element.
		 * @return int Positive if elem1 > elem2, otherwise 0.
		 */
		private function compare_elems( $elem1, $elem2 ) {
			if ( $elem1['priority'] > $elem2['priority'] ) {
				return 1;
			}

			return 0;
		}
	}

endif;
