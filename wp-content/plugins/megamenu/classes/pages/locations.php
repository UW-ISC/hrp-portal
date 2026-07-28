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

			$plugin_settings = $this->get_plugin_settings();

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

			$existing_settings = $this->get_plugin_settings();
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
					if ( $this->is_setting_enabled( $raw ) ) {
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

			$new_settings = array_merge( $this->get_plugin_settings(), $submitted_settings );
			update_option( 'megamenu_settings', $new_settings );

			do_action( 'megamenu_after_save_settings' );
			do_action( 'megamenu_delete_cache' );
		}


		/**
		 * Validate a location-scoped AJAX request: nonce, capability, and registered location.
		 * Calls wp_send_json_error() (which terminates) on any failure.
		 *
		 * @return string Sanitized location slug.
		 */
		private function validate_ajax_location_request(): string {
			check_ajax_referer( 'megamenu_edit', 'nonce' );

			if ( ! current_user_can( apply_filters( 'megamenu_options_capability', 'edit_theme_options' ) ) ) {
				wp_send_json_error();
			}

			$location = isset( $_POST['location'] ) ? sanitize_key( wp_unslash( $_POST['location'] ) ) : '';

			if ( ! $location ) {
				wp_send_json_error();
			}

			if ( ! isset( $this->get_registered_locations()[ $location ] ) ) {
				wp_send_json_error();
			}

			return $location;
		}


		/**
		 * AJAX: HTML for the location settings dialog (tabs + fields).
		 *
		 * POST: `location` (slug), optional `cards_context` (`page`|`meta`), optional `editing_menu_id` (int, Appearance → Menus).
		 * Response data includes `html`, `has_nav_menu`, `mmm_enabled`, `location_label`, `assigned_summary_html`, `menu_id`.
		 *
		 * @return void
		 */
		public function ajax_get_location_settings_html() {
			$location      = $this->validate_ajax_location_request();
			$all_locations = $this->get_registered_locations();

			$raw_ctx = isset( $_POST['cards_context'] ) ? sanitize_key( wp_unslash( $_POST['cards_context'] ) ) : '';
			$cards_context = ( 'meta' === $raw_ctx ) ? 'meta' : 'page';

			$editing_menu_id = isset( $_POST['editing_menu_id'] ) ? absint( wp_unslash( $_POST['editing_menu_id'] ) ) : 0;

			$description = $all_locations[ $location ];

			$location_label = apply_filters( 'megamenu_location_card_description', $description, $location, $cards_context );

			$assigned_summary_html = $this->render_location_assignment_summary( $location );

			$menu_id = ( 'meta' === $cards_context && $editing_menu_id > 0 )
				? $editing_menu_id
				: (int) $this->get_menu_id_for_location( $location );

			ob_start();
			$this->render_location_settings_form( $all_locations, $location, $description );
			$form_html = ob_get_clean();

			$html = '<div class="megamenu-location-settings-dialog__surface megamenu_outer_wrap">' . $form_html . '</div>';

			wp_send_json_success(
				[
					'html'                   => $html,
					'has_nav_menu'           => $this->location_has_valid_assigned_menu( $location ),
					'mmm_enabled'            => ( $loc = Mega_Menu_Location::find( $location ) ) && $loc->is_active(),
					'location_label'         => $location_label,
					'assigned_summary_html'  => $assigned_summary_html,
					'menu_id'                => $menu_id,
				]
			);
		}


		/**
		 * AJAX: Save settings for one location from the dialog form.
		 *
		 * @return void
		 */
		public function ajax_save_location_settings() {
			$location = $this->validate_ajax_location_request();

			if ( ! isset( $_POST['megamenu_meta'][ $location ] ) || ! is_array( $_POST['megamenu_meta'][ $location ] ) ) {
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
			$location = $this->validate_ajax_location_request();

			if ( 0 !== strpos( $location, 'max_mega_menu_' ) ) {
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
					'preview_title' => Mega_Menu_Location_Preview::get_preview_title( $location, $title ),
				]
			);
		}


		/**
		 * AJAX: Toggle Max Mega Menu on/off for a location (pill control on Menu Locations).
		 *
		 * @return void
		 */
		public function ajax_toggle_location_mmm() {
			$location = $this->validate_ajax_location_request();

			$raw_on = isset( $_POST['enabled'] ) ? wp_unslash( $_POST['enabled'] ) : '0';
			$on     = $this->is_setting_enabled( $raw_on );

			$settings = $this->get_plugin_settings();

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
			<div id="megamenu-location-settings-dialog" class="megamenu-admin-modal megamenu-location-settings-dialog" hidden data-megamenu-expand-storage-key="megamenu_admin_modal_wpcontent_expanded" data-i18n-settings-expand="<?php echo esc_attr__( 'Expand to fill workspace', 'megamenu' ); ?>" data-i18n-settings-collapse="<?php echo esc_attr__( 'Restore default size', 'megamenu' ); ?>" data-i18n-preview-expand="<?php echo esc_attr__( 'Expand preview to fill workspace', 'megamenu' ); ?>" data-i18n-preview-collapse="<?php echo esc_attr__( 'Restore default preview size', 'megamenu' ); ?>" data-i18n-modal-expand="<?php echo esc_attr__( 'Expand to fill workspace', 'megamenu' ); ?>" data-i18n-modal-collapse="<?php echo esc_attr__( 'Restore default size', 'megamenu' ); ?>" data-i18n-mobile-preview-disabled="<?php echo esc_attr__( 'Mobile width preview is unavailable because the responsive breakpoint is set to 0px in the menu theme (mobile menu is off).', 'megamenu' ); ?>">
				<button type="button" class="megamenu-admin-modal__backdrop" aria-label="<?php esc_attr_e( 'Close', 'megamenu' ); ?>"></button>
				<div class="megamenu-admin-modal__panel" role="dialog" aria-modal="true" aria-labelledby="megamenu-location-settings-dialog-title" tabindex="-1">
					<div class="megamenu-admin-modal__header megamenu-location-settings-dialog__header">
						<div class="megamenu-admin-modal__header-top">
							<div class="megamenu-admin-modal__title-group">
								<div class="megamenu-location-settings-dialog__title-start">
									<h2 id="megamenu-location-settings-dialog-title" class="megamenu-admin-modal__title megamenu-location-settings-dialog__title-heading">
										<span class="megamenu-location-settings-dialog__title-cluster">
											<span class="megamenu-location-settings-dialog__title-pin dashicons dashicons-location" aria-hidden="true"></span>
											<span class="megamenu-admin-modal__title-text">
												<span class="megamenu-location-title"></span>
											</span>
										</span>
									</h2>
									<p class="description megamenu-location-settings-dialog__assigned" id="megamenu-location-settings-dialog-assigned" aria-live="polite"></p>
								</div>
									<div class="megamenu-location-settings-dialog__mode-pill megamenu-location-settings-dialog__mode-pill--settings-preview" role="toolbar" aria-label="<?php esc_attr_e( 'Switch between settings and preview', 'megamenu' ); ?>">
										<span class="megamenu-location-settings-dialog__mode-pill-slider" aria-hidden="true"></span>
										<button type="button" class="button button-secondary button-compact megamenu-location-toolbar-btn megamenu-location-settings-dialog__mode-btn megamenu-location-settings-dialog__mode-btn--settings is-active" aria-pressed="true">
											<span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
											<?php esc_html_e( 'Settings', 'megamenu' ); ?>
										</button>
										<button type="button" class="button button-secondary button-compact megamenu-location-toolbar-btn megamenu-location-settings-dialog__mode-btn megamenu-location-settings-dialog__mode-btn--preview" aria-pressed="false">
											<span class="dashicons dashicons-visibility megamenu-location-settings-dialog__preview-mode-icon megamenu-location-settings-dialog__preview-mode-icon--idle" aria-hidden="true"></span>
											<span class="dashicons dashicons-update megamenu-location-settings-dialog__preview-mode-icon megamenu-location-settings-dialog__preview-mode-icon--active" aria-hidden="true"></span>
											<?php esc_html_e( 'Preview', 'megamenu' ); ?>
										</button>
									</div>
							<div class="megamenu-admin-modal__header-actions">
								<button type="button" class="button button-secondary button-compact megamenu-admin-modal-icon-btn megamenu-admin-modal__expand-btn" aria-expanded="false" aria-label="<?php echo esc_attr__( 'Expand to fill workspace', 'megamenu' ); ?>">
									<span class="dashicons dashicons-fullscreen-alt megamenu-admin-modal__expand-icon megamenu-admin-modal__expand-icon--expand" aria-hidden="true"></span>
									<span class="dashicons dashicons-fullscreen-exit-alt megamenu-admin-modal__expand-icon megamenu-admin-modal__expand-icon--contract" aria-hidden="true"></span>
								</button>
								<button type="button" class="button button-secondary button-compact megamenu-admin-modal-icon-btn megamenu-modal-close" aria-label="<?php echo esc_attr__( 'Close', 'megamenu' ); ?>">
									<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
								</button>
							</div>
							</div>
						</div>
					</div>
					<div class="megamenu-admin-modal__body megamenu-location-settings-dialog__body-stack">
						<div class="megamenu-location-settings-dialog__settings-view megamenu-admin-modal__loading-host">
							<div id="megamenu-location-settings-dialog-body" class="megamenu-admin-modal__body-slot"></div>
							<button type="button" class="mmm-scroll-hint" hidden aria-hidden="true">
								<span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
								<span><?php esc_html_e( 'Scroll down', 'megamenu' ); ?></span>
							</button>
						</div>
						<div class="megamenu-location-settings-dialog__preview-view" hidden>
							<div class="megamenu-location-settings-dialog__preview-panel">
								<div class="megamenu-preview-dialog__iframe-shell megamenu-admin-modal__loading-host">
									<div class="megamenu-admin-modal__loading-overlay" role="status" aria-live="polite">
										<span class="megamenu-admin-modal__loading-spinner" aria-hidden="true"></span>
										<span class="screen-reader-text"><?php echo esc_html__( 'Loading preview.', 'megamenu' ); ?></span>
									</div>
									<iframe class="megamenu-preview-dialog__iframe" title="<?php esc_attr_e( 'Location preview', 'megamenu' ); ?>" src="about:blank"></iframe>
								</div>
							</div>
						</div>
					</div>
					<div class="megamenu-admin-modal__footer megamenu-location-settings-dialog__footer">
						<div class="megamenu-location-settings-dialog__footer-settings">
							<p class="submit">
								<button type="button" class="button button-primary button-compact megamenu-location-settings-dialog-save"><?php esc_html_e( 'Save', 'megamenu' ); ?></button>
							</p>
						</div>
						<div class="megamenu-location-settings-dialog__footer-preview" hidden>
							<div class="megamenu-location-settings-dialog__preview-toolbar">
								<div class="megamenu-preview-dialog__viewport-toggle megamenu-location-settings-dialog__mode-pill megamenu-location-settings-dialog__mode-pill--viewport-preview" role="toolbar" aria-label="<?php esc_attr_e( 'Preview width', 'megamenu' ); ?>">
									<span class="megamenu-location-settings-dialog__mode-pill-slider" aria-hidden="true"></span>
									<button type="button" class="button button-secondary button-compact megamenu-location-toolbar-btn megamenu-location-settings-dialog__mode-btn megamenu-preview-dialog__viewport-btn megamenu-preview-dialog__viewport-btn--mobile" aria-pressed="false">
										<span class="dashicons dashicons-smartphone" aria-hidden="true"></span>
										<span class="screen-reader-text"><?php esc_html_e( 'Mobile breakpoint width', 'megamenu' ); ?></span>
									</button>
									<button type="button" class="button button-secondary button-compact megamenu-location-toolbar-btn megamenu-location-settings-dialog__mode-btn megamenu-preview-dialog__viewport-btn megamenu-preview-dialog__viewport-btn--desktop is-active" aria-pressed="true">
										<span class="dashicons dashicons-desktop" aria-hidden="true"></span>
										<span class="screen-reader-text"><?php esc_html_e( 'Desktop width', 'megamenu' ); ?></span>
									</button>
								</div>
								<div class="megamenu-preview-dialog__preview-bg-field">
									<div class="megamenu-preview-dialog__bg-custom-cell">
										<input type="text" class="mega-color-picker-input megamenu-preview-dialog__bg-custom-color-input" value="transparent" tabindex="-1" autocomplete="off" aria-hidden="true" />
										<button type="button" class="button button-secondary button-compact megamenu-location-toolbar-btn megamenu-location-settings-dialog__mode-btn megamenu-preview-dialog__bg-open-picker" aria-pressed="false">
											<span class="megamenu-preview-dialog__bg-swatch megamenu-preview-dialog__bg-swatch--preview" aria-hidden="true"></span>
											<span class="screen-reader-text"><?php esc_html_e( 'Preview background color', 'megamenu' ); ?></span>
										</button>
									</div>
								</div>
							</div>
						</div>
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

			if ( ! wp_script_is( 'mega-colorpicker', 'registered' ) ) {
				wp_register_script(
					'mega-colorpicker',
					MEGAMENU_BASE_URL . 'js/colorpicker/colorpicker.js',
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
				[ 'jquery', 'dialog-tabs', 'dialog-modal-expand', 'mega-colorpicker' ],
				MEGAMENU_VERSION,
				true
			);

			if ( ! wp_style_is( 'mega-colorpicker', 'enqueued' ) ) {
				wp_enqueue_style(
					'mega-colorpicker',
					MEGAMENU_BASE_URL . 'js/colorpicker/colorpicker.css',
					[],
					MEGAMENU_VERSION
				);
			}

			global $pagenow;

			wp_localize_script(
				'dialog-location-settings',
				'megamenu_location_dialog',
				[
					// Root-relative so requests use the browser origin (e.g. :10003) when siteurl omits the port.
					'ajaxurl'                      => admin_url( 'admin-ajax.php', 'relative' ),
					'nonce'                        => wp_create_nonce( 'megamenu_edit' ),
					'cards_context'                => ( isset( $pagenow ) && 'nav-menus.php' === $pagenow ) ? 'meta' : 'page',
					'toggle_location_action'       => 'megamenu_toggle_location_mmm',
					'delete_location_action'       => 'megamenu_delete_menu_location',
					'initial_open_location'        => $initial,
					'highlight_new_location'       => $highlight_new_location,
					'nav_menus_locations_url'      => admin_url( 'nav-menus.php?action=locations', 'relative' ),
					'nav_menus_edit_menu_url_base' => admin_url( 'nav-menus.php?action=edit&menu=', 'relative' ),
					'i18n'                         => [
						'load_error'            => __( 'Could not load location settings.', 'megamenu' ),
						'save_error'            => __( 'Could not save settings.', 'megamenu' ),
						'saving'                => __( 'Saving', 'megamenu' ),
						'saved'                 => __( 'Settings saved.', 'megamenu' ),
						'saved_button'          => __( 'Saved', 'megamenu' ),
						'assign_menu'           => __( 'Assign a menu to this location before changing settings.', 'megamenu' ),
						'toggle_error'          => __( 'Could not update Max Mega Menu for this location.', 'megamenu' ),
						'delete_confirm'        => __( 'Delete this menu location? This cannot be undone.', 'megamenu' ),
						'delete_error'          => __( 'Could not delete this menu location.', 'megamenu' ),
						'title_save_error'      => __( 'Could not save the location name.', 'megamenu' ),
						'assigned_menu_colon'   => __( 'Assigned menu:', 'megamenu' ),
						'select_a_menu'         => __( 'Select a menu', 'megamenu' ),
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
				$loc = Mega_Menu_Location::find( $id );
				if ( $loc && $loc->is_active() ) {
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

			$saved_settings = $this->get_plugin_settings();

			global $nav_menu_selected_id;
			$metabox_editing_menu_id = isset( $nav_menu_selected_id ) ? (int) $nav_menu_selected_id : 0;

			$all_locations = $this->get_registered_locations();

			list( $enabled_locations, $disabled_locations ) = $this->partition_locations_by_mmm_active_state( $tagged_menu_locations );

			$default_sections = [
				'location_rows' => [
					'enabled'  => $enabled_locations,
					'disabled' => $disabled_locations,
				],
				'after_rows'    => [],
			];

			/**
			 * Filter how the Max Mega Menu meta box on Appearance → Menus renders location cards.
			 *
			 * @since 3.9.x
			 * @param array $sections {
			 *     @type array $location_rows {
			 *         @type array<string,string> $enabled  Location slug => label (MMM-enabled rows first).
			 *         @type array<string,string> $disabled Location slug => label.
			 *     }
			 *     @type array[] $after_rows Items appended after those rows. Each item may include:
			 *         `callback` (callable): receives (Mega_Menu_Locations $locations_page, array $item) and echoes output.
			 * }
			 * @param array $default_partition Copy of the default enabled/disabled maps before filtering.
			 * @param array $saved_settings    `megamenu_settings` option.
			 * @param array $all_locations     All locations from {@see get_registered_locations()}.
			 * @param array $tagged_menu_locations Locations assigned to the menu being edited.
			 */
			$filtered = apply_filters(
				'megamenu_nav_metabox_location_sections',
				$default_sections,
				[
					'enabled'  => $enabled_locations,
					'disabled' => $disabled_locations,
				],
				$saved_settings,
				$all_locations,
				$tagged_menu_locations
			);

			$sections = [
				'location_rows' => [
					'enabled'  => isset( $filtered['location_rows']['enabled'] ) && is_array( $filtered['location_rows']['enabled'] )
						? $filtered['location_rows']['enabled']
						: $default_sections['location_rows']['enabled'],
					'disabled' => isset( $filtered['location_rows']['disabled'] ) && is_array( $filtered['location_rows']['disabled'] )
						? $filtered['location_rows']['disabled']
						: $default_sections['location_rows']['disabled'],
				],
				'after_rows'    => isset( $filtered['after_rows'] ) && is_array( $filtered['after_rows'] )
					? $filtered['after_rows']
					: [],
			];

			self::print_location_cards_shell_open( 'meta' );

			foreach ( $sections['location_rows']['enabled'] as $location => $description ) {
				$this->show_location_row( $all_locations, $location, $description, $saved_settings, 'meta', $metabox_editing_menu_id );
			}
			foreach ( $sections['location_rows']['disabled'] as $location => $description ) {
				$this->show_location_row( $all_locations, $location, $description, $saved_settings, 'meta', $metabox_editing_menu_id );
			}
			foreach ( $sections['after_rows'] as $item ) {
				if ( isset( $item['callback'] ) && is_callable( $item['callback'] ) ) {
					call_user_func( $item['callback'], $this, $item );
				}
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

				<p class="description"><?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: HTML anchor linking to Appearance > Menus (link text is "Appearance > Menus"). */
						__(
							'This is an overview of the menu locations registered by your theme. A menu location acts as a placeholder (or \'slot\') for where a menu can be displayed on your site. Menus (created on the %s page) are assigned to a Menu Location.',
							'megamenu'
						),
						'<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Appearance > Menus', 'megamenu' ) . '</a>'
					)
				);
				?></p>
				<p class="description"><?php esc_html_e( 'Use the toggle to enable Max Mega Menu for a specific menu location, then click the settings button to customize its behaviour.', 'megamenu' ); ?></p>

				<?php
				if ( ! count( $enabled_locations + $disabled_locations ) ) {
					echo '<p class="description">';
					esc_html_e( 'Add a new menu location below, then display the menu using the Max Mega Menu block, widget or shortcode.', 'megamenu' );
					echo '</p>';
				}
				?>

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
		 * Button that opens the shared location settings dialog (or hidden opener on cards).
		 *
		 * Only outputs {@see data-location}; labels, assigned-menu line, and menu id are returned by
		 * {@see ajax_get_location_settings_html()} when the dialog loads.
		 *
		 * @param string $location Location slug.
		 * @param array  $args {
		 *     @type bool $requires_menu When true, the control is disabled until a menu is assigned.
		 *     @type bool $hidden        When true, output a non-visible button used as the card click target.
		 * }
		 * @return string HTML.
		 */
		public static function render_location_settings_trigger( $location, $args = [] ) {
			$args = wp_parse_args(
				$args,
				[
					'requires_menu' => false,
					'hidden'        => false,
				]
			);

			$is_hidden     = ! empty( $args['hidden'] );
			$visible_label = __( 'Settings', 'megamenu' );
			$classes       = 'button button-secondary button-compact megamenu-location-toolbar-btn mega-location-settings-open';

			if ( $is_hidden ) {
				$classes = 'mega-location-settings-open mega-location-settings-open--hidden';
			}

			$button_inner = '<span class="dashicons dashicons-admin-generic" aria-hidden="true"></span> ' . esc_html( $visible_label );
			if ( $is_hidden ) {
				$button_inner = '';
			}

			$processor = new WP_HTML_Tag_Processor( '<button type="button">' . $button_inner . '</button>' );

			if ( $processor->next_tag( 'button' ) ) {
				if ( $args['requires_menu'] ) {
					$classes .= ' mega-location-settings-open--needs-menu';
				}
				$processor->set_attribute( 'class', $classes );
				if ( ! $is_hidden ) {
					$processor->set_attribute( 'aria-label', wp_strip_all_tags( $visible_label ) );
				} else {
					$processor->set_attribute( 'hidden', true );
					$processor->set_attribute( 'aria-hidden', 'true' );
					$processor->set_attribute( 'tabindex', '-1' );
				}
				$processor->set_attribute( 'data-location', $location );

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
		 * @param bool   $disabled  When true, the control is non-interactive (reserved; callers pass false so MMM can be toggled without an assigned menu).
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
		 * Whether a real nav menu is assigned (theme_mod can hold a stale ID after the menu is deleted).
		 *
		 * @param string $location Location slug.
		 * @return bool
		 */
		private function location_has_valid_assigned_menu( $location ) {
			/**
			 * Short-circuit whether a location has any assigned navigation menu (e.g. multilingual plugins).
			 *
			 * @since 3.9.x
			 * @param bool|null $has_menu Pass true/false to override; null to use default Mega Menu logic.
			 * @param string    $location Location slug.
			 */
			$handled = apply_filters( 'megamenu_location_has_assigned_nav_menu', null, $location );
			if ( null !== $handled ) {
				return (bool) $handled;
			}

			$loc = Mega_Menu_Location::find( $location );
			return (bool) ( $loc && $loc->get_valid_menu_id() > 0 );
		}


		/**
		 * Allowed HTML for {@see render_location_assignment_summary()} output (default and filtered).
		 *
		 * @return array<string, array<string, bool>>
		 */
		private static function get_location_assignment_summary_allowed_tags() {
			return [
				'a'    => [
					'class' => true,
					'href'  => true,
				],
	'span' => [
					'class'       => true,
					'aria-hidden' => true,
				],
			];
		}


		/**
		 * Human-readable assigned-menu line for the Menu Locations card (linked menu name).
		 *
		 * @param string $location Location slug.
		 * @return string Safe HTML (empty when no menu is assigned).
		 */
		private function render_location_assignment_summary( $location ) {
			$menu_id = $this->get_menu_id_for_location( $location );
			$name    = $this->get_menu_name_for_location( $location );

			$default_html = '';
			if ( $menu_id && $name ) {
				$href = admin_url( 'nav-menus.php?action=edit&menu=' . (int) $menu_id );

				$default_html = sprintf(
					'%s <a class="mega-location__assigned-link" href="%s">%s</a>',
					esc_html__( 'Assigned menu:', 'megamenu' ),
					esc_url( $href ),
					esc_html( $name )
				);
			}

			/**
			 * Filters the “Assigned menu” HTML on Max Mega Menu location cards.
			 *
			 * @since 3.9.x
			 * @param string $default_html Default summary HTML (possibly empty).
			 * @param string $location       Location slug.
			 */
			$html = apply_filters( 'megamenu_location_assignment_summary_html', $default_html, $location );

			if ( '' === $html ) {
				return '';
			}

			return wp_kses( $html, self::get_location_assignment_summary_allowed_tags() );
		}

		/**
		 * Same line layout as {@see render_location_assignment_summary()} when no menu is assigned:
		 * "Assigned menu:" + link to Appearance > Menus (Manage Locations) with the label "Select a menu".
		 *
		 * @return string Safe HTML.
		 */
		private function render_location_unassigned_menu_prompt() {
			$href = admin_url( 'nav-menus.php?action=locations' );
			$html = sprintf(
				'%s <a class="mega-location__assigned-link" href="%s">%s</a>',
				esc_html__( 'Assigned menu:', 'megamenu' ),
				esc_url( $href ),
				esc_html__( 'Select a menu', 'megamenu' )
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
		 * @param  int    $metabox_editing_menu_id When `cards_context` is `meta` and this is a positive menu term ID, passed to the settings trigger as the menu being edited (Polylang fork locations share one base card).
		 * @return void
		 */
		public function show_location_row( $locations, $location, $description, $saved_settings, $cards_context = 'page', $metabox_editing_menu_id = 0 ) {
			/**
			 * Filters the label shown on Max Mega Menu location cards only (Mega Menu → Menu Locations and the Appearance → Menus meta box). Does not change global `register_nav_menu` data.
			 *
			 * @since 3.9.x
			 * @param string $description   Human-readable location description for the card.
			 * @param string $location      Location slug.
			 * @param string $cards_context `page` or `meta`.
			 */
			$description = apply_filters( 'megamenu_location_card_description', $description, $location, $cards_context );

			$loc = Mega_Menu_Location::find( $location );

			$is_enabled_class = ( $loc && $loc->is_active() ) ? 'mega-location-enabled' : 'mega-location-disabled';

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

			<div class="postbox mega-location <?php echo esc_attr( $is_enabled_class ); ?> <?php echo esc_attr( $mmm_row_class ); ?><?php echo esc_attr( $has_active_location_class ); ?>" data-mega-location="<?php echo esc_attr( $location ); ?>" data-has-nav-menu="<?php echo $this->location_has_valid_assigned_menu( $location ) ? '1' : '0'; ?>" data-mmm-plain-label="<?php echo esc_attr( $description ); ?>"<?php echo ( 'meta' === $cards_context && (int) $metabox_editing_menu_id > 0 ) ? ' data-mmm-editing-menu-id="' . esc_attr( (string) (int) $metabox_editing_menu_id ) . '"' : ''; ?>>
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
										<button type="button" class="button button-secondary button-compact mega-location__title-edit" aria-label="<?php esc_attr_e( 'Edit location name', 'megamenu' ); ?>">
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
								echo self::render_mmm_enable_toggle( $location, $mmm_on, 'loc-' . $location, false );
								?>
							</div>
						</div>
					</header>
					<div class="mega-location__meta">
						<?php if ( $active_instance > 0 ) : ?>
						<p class="description mega-location__description"><?php echo esc_html( sprintf( __( 'Active for instance %d.', 'megamenu' ), (int) $active_instance ) ); ?></p>
						<?php endif; ?>
						<?php if ( $this->location_has_valid_assigned_menu( $location ) ) : ?>
						<p class="description mega-location__assigned"><?php echo $this->render_location_assignment_summary( $location ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<?php else : ?>
						<p class="description mega-location__assigned"><?php echo $this->render_location_unassigned_menu_prompt(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<?php endif; ?>
					</div>
					<?php
					echo self::render_location_settings_trigger(
						$location,
						[
							'hidden' => true,
						]
					);
					$preview_inactive = ! Mega_Menu_Location_Preview::is_previewable( $location )
						|| ! ( $loc && $loc->is_active() );
					echo Mega_Menu_Location_Preview::render_preview_link(
						$location,
						$description,
						[
							'inactive' => $preview_inactive,
							'hidden'   => true,
						]
					);
					?>
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
		 * Preview-related warnings for the location modal (first tab only); visibility toggled in dialog-location-settings.js.
		 *
		 * @return void
		 */
		private static function echo_location_settings_dialog_notices() {
			?>
			<div class="megamenu-location-settings-dialog__notices">
				<div class="notice notice-warning megamenu-location-settings-dialog__notice megamenu-location-settings-dialog__notice--no-menu" hidden>
					<p><?php esc_html_e( 'Assign a menu to this location under Appearance > Menus.', 'megamenu' ); ?></p>
				</div>
				<div class="notice notice-warning megamenu-location-settings-dialog__notice megamenu-location-settings-dialog__notice--mmm-off" hidden>
					<p><?php esc_html_e( 'Max Mega Menu is not enabled for this location. To enable it, close this window and switch on the toggle.', 'megamenu' ); ?></p>
				</div>
			</div>
			<?php
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
			$plugin_settings = $this->get_plugin_settings();
			$settings        = $this->build_location_settings_structure( $location, $plugin_settings );

			uasort( $settings, [ $this, 'compare_elems' ] );

			?>

			<form class="megamenu-location-settings-dialog-form" method="post" action="#" data-location="<?php echo esc_attr( $location ); ?>" data-custom-location="<?php echo $is_custom_location ? '1' : '0'; ?>">
				<?php wp_nonce_field( 'megamenu_edit', 'nonce' ); ?>
				<input type="hidden" name="location" value="<?php echo esc_attr( $location ); ?>" />
				<div class='megamenu-dialog-rail'>
					<?php $this->render_location_settings_tabs( $settings ); ?>
					<?php $this->render_location_settings_panels( $settings, $location, $is_custom_location, $plugin_settings ); ?>
				</div>
			</form>

			<?php
		}


		/**
		 * Build the tabbed settings structure array for a location's settings dialog.
		 *
		 * @param string $location        Location slug.
		 * @param array  $plugin_settings Full plugin settings array (passed to filter).
		 * @return array Filtered settings structure.
		 */
		private function build_location_settings_structure( string $location, array $plugin_settings ): array {
			$loc = Mega_Menu_Location::find( $location );
			return apply_filters(
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
												'value' => $loc->get_setting( 'event', 'hover' ),
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
												'value' => $loc->get_setting( 'effect', 'fade_up' ),
												'title' => __( 'Animation', 'megamenu' ),
											],
											[
												'type'  => 'effect_speed',
												'key'   => 'effect_speed',
												'value' => $loc->get_setting( 'effect_speed', '200' ),
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
												'value' => $loc->get_setting( 'effect_mobile', 'slide_right' ),
												'title' => __( 'Type', 'megamenu' ),
											],
											[
												'type'  => 'effect_mobile_direction',
												'key'   => 'effect_mobile_direction',
												'value' => $loc->get_setting( 'effect_mobile_direction', 'vertical' ),
												'title' => __( 'Submenu Style', 'megamenu' ),
											],
											[
												'type'  => 'effect_speed_mobile',
												'key'   => 'effect_speed_mobile',
												'value' => $loc->get_setting( 'effect_speed_mobile', '200' ),
												'title' => __( 'Speed', 'megamenu' ),
											],
										],
									],
									'mobile_behaviour' => [
										'priority'    => 20,
										'title'       => __( 'Accordion Behaviour', 'megamenu' ),
										'description' => __( 'Define the sub menu toggle behaviour for the mobile menu.', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'mobile_behaviour',
												'key'   => 'mobile_behaviour',
												'value' => null,
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
												'value' => null,
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
												'value' => $loc->get_setting( 'theme', 'default' ),
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
												'value' => null,
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
												'value' => null,
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
												'value' => null,
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
												'value' => null,
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
												'value' => null,
											],
										],
									],
									'clean_classes'    => [
										'priority'    => 35,
										'title'       => __( 'Clean Up Menu Item Classes', 'megamenu' ),
										'description' => __( 'Remove WordPress default type, object, ID, and legacy page classes from menu items.', 'megamenu' ),
										'settings'    => [
											[
												'type'  => 'clean_classes',
												'key'   => 'clean_classes',
												'value' => null,
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
		}


		/**
		 * Render the tab navigation for the location settings dialog.
		 *
		 * @param array $settings Sorted settings structure.
		 * @return void
		 */
		private function render_location_settings_tabs( array $settings ): void {
			echo '<div class="megamenu-dialog-tablist mega-tablist" role="tablist">';

			$is_first = true;

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
		}


		/**
		 * Render the settings panel content for the location settings dialog.
		 *
		 * @param array  $settings           Sorted settings structure.
		 * @param string $location           Location slug.
		 * @param bool   $is_custom_location Whether this is a custom MMM location.
		 * @param array  $plugin_settings    Full plugin settings array.
		 * @return void
		 */
		private function render_location_settings_panels( array $settings, string $location, bool $is_custom_location, array $plugin_settings ): void {
			echo '<div class="megamenu-dialog-panels">';

				$is_first              = true;
				$print_dialog_notices = true;

				foreach ( $settings as $section_id => $section ) {

					if ( $is_first ) {
							$display  = 'block';
							$is_first = false;
					} else {
						$display = 'none';
					}

						echo "<div class='mega-tab-content mega-tab-content-{$section_id}' style='display: {$display}'>";

					if ( $print_dialog_notices ) {
						self::echo_location_settings_dialog_notices();
						$print_dialog_notices = false;
					}

					if ( $section_id == 'output_options' && ! $is_custom_location ) {
						echo '<div class="notice notice-warning inline"><p>';
						echo esc_html__( 'This menu location is registered by your theme. Your theme should already include the code required to display this menu location on your site.', 'megamenu' );
						echo '</p></div>';
					}

					$use_output_options_stack = ( 'output_options' === $section_id );

					if ( $use_output_options_stack ) {
						echo '<div class="' . esc_attr( $section_id ) . ' mmm-settings-table mmm-location-output-options" role="group" aria-label="' . esc_attr__( 'Display options', 'megamenu' ) . '">';
					} else {
						echo "<table class='" . esc_attr( $section_id ) . " mmm-settings-table'>";
					}

						// order the fields by priority
						uasort( $section['settings'], [ $this, 'compare_elems' ] );

					foreach ( $section['settings'] as $group_id => $group ) {

						if ( $use_output_options_stack ) {
							echo '<div class="mmm-location-output-options__row mega-' . esc_attr( $group_id ) . '">';
						} else {
							echo "<tr class='" . esc_attr( 'mega-' . $group_id ) . "'>";
						}

						if ( isset( $group['settings'] ) ) {

							if ( $use_output_options_stack ) {
								echo '<div class="mmm-location-output-options__name mega-name"><div class="mega-name-title">';
							} else {
								echo "<td class='mega-name'><div class='mega-name-title'>";
							}
							if ( isset( $group['icon'] ) ) {
								echo "<span class='dashicons dashicons-" . esc_html( $group['icon'] ) . "'></span>";
							}
							echo esc_html( $group['title'] );
							echo "</div><div class='mega-description'>" . esc_html( $group['description'] ) . '</div>';
							if ( $use_output_options_stack ) {
								echo '</div>';
							} else {
								echo '</td>';
							}
							if ( $use_output_options_stack ) {
								echo '<div class="mmm-location-output-options__value mega-value">';
							} else {
								echo "<td class='mega-value'>";
							}

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
											$ps = $this->get_plugin_settings();
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
										$this->print_active_instance_option( $location );
										break;
									case 'click_behaviour':
										$this->print_click_behaviour_option( $location );
										break;
									case 'mobile_behaviour':
										$this->print_mobile_behaviour_option( $location );
										break;
									case 'mobile_state':
										$this->print_mobile_state_option( $location );
										break;
									case 'container':
										$this->print_container_option( $location );
										break;
									case 'descriptions':
										$this->print_descriptions_option( $location );
										break;
									case 'unbind':
										$this->print_unbind_option( $location );
										break;
									case 'prefix':
										$this->print_prefix_option( $location, $setting['value'] );
										break;
									case 'clean_classes':
										$this->print_clean_classes_option( $location );
										break;
									default:
										/**
										 * Print output for a custom location setting `type` (third-party integrations).
										 *
										 * @param string $key      Setting key.
										 * @param string $location Menu location slug.
										 * @param array  $setting  Full setting row (includes `type`, `key`, `value`, etc.).
										 */
										do_action( "megamenu_print_location_option_{$setting['type']}", $setting['key'], $location, $setting );
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

							if ( $use_output_options_stack ) {
								echo '</div>';
							} else {
								echo '</td>';
							}
						} elseif ( $use_output_options_stack ) {
							echo '<div class="mmm-location-output-options__row mmm-location-output-options__row--heading mega-' . esc_attr( $group_id ) . '">';
							echo '<div class="mmm-location-output-options__heading"><h5>' . esc_html( $group['title'] ) . '</h5></div></div>';
						} else {
							echo '<td colspan="2"><h5>' . esc_html( $group['title'] ) . '</h5></td>';
						}

						if ( isset( $group['settings'] ) ) {
							if ( $use_output_options_stack ) {
								echo '</div>';
							} else {
								echo '</tr>';
							}
						} elseif ( ! $use_output_options_stack ) {
							echo '</tr>';
						}

					}

					if ( $section_id == 'general' ) {
						do_action( 'megamenu_settings_table', $location, $plugin_settings );
					}

					if ( $use_output_options_stack ) {
						echo '</div>';
					} else {
						echo '</table>';
					}
						echo '</div>';
				}

			echo '</div>';
		}


		/**
		 * Return a list of all registered menu locations, including custom MMM locations.
		 *
		 * @since  2.8
		 * @return array Map of location identifier to description.
		 */
		private function get_plugin_settings(): array {
			$settings = get_option( 'megamenu_settings', [] );
			return is_array( $settings ) ? $settings : [];
		}


		/**
		 * Whether a raw form/stored value represents an enabled/on state.
		 *
		 * @param mixed $value Raw value.
		 * @return bool
		 */
		private function is_setting_enabled( $value ): bool {
			return '1' === (string) $value || 1 === $value || true === $value || 'true' === $value || 'on' === $value;
		}


		public function get_registered_locations() {
			/**
			 * Fires before Max Mega Menu reads registered nav menu locations.
			 *
			 * Multilingual and other integrations may unregister temporary locations or adjust labels.
			 *
			 * @since 3.9.x
			 */
			do_action( 'megamenu_normalize_registered_nav_menus' );
			$all_locations = get_registered_nav_menus();

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
			$is_on = $this->is_setting_enabled( $value );
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
		public function print_active_instance_option( $location ) {
			$loc             = Mega_Menu_Location::find( $location );
			$active_instance = $loc ? $loc->get_setting( 'active_instance', 0 ) : 0;
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
		public function print_click_behaviour_option( $location ) {
			$loc          = Mega_Menu_Location::find( $location );
			$second_click = $loc ? $loc->get_setting( 'second_click', 'go' ) : 'go';
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
		public function print_mobile_behaviour_option( $location ) {
			$loc              = Mega_Menu_Location::find( $location );
			$mobile_behaviour = $loc ? $loc->get_setting( 'mobile_behaviour', 'standard' ) : 'standard';
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
		public function print_mobile_state_option( $location ) {
			$loc          = Mega_Menu_Location::find( $location );
			$mobile_state = $loc ? $loc->get_setting( 'mobile_state', 'collapse_all' ) : 'collapse_all';
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
		public function print_container_option( $location ) {
			$loc       = Mega_Menu_Location::find( $location );
			$container = $loc ? $loc->get_setting( 'container', 'div' ) : 'div';
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
		public function print_descriptions_option( $location ) {
			$plugin_settings = get_option( 'megamenu_settings', [] );
			$descriptions    = 'disabled';

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
		public function print_prefix_option( $location, $plugin_settings = [] ) {
			$prefix = 'disabled';

			if ( isset( $plugin_settings[ $location ]['prefix'] ) ) {
				$prefix = $plugin_settings[ $location ]['prefix'];
			} elseif ( isset( $plugin_settings['prefix'] ) ) {
				$prefix = $plugin_settings['prefix'];
			}

			$this->print_location_dialog_pill_checkbox( $location, 'prefix', 'enabled', 'enabled' === $prefix );
		}


		/**
		 * Print the checkbox option for cleaning up WordPress default menu item classes.
		 *
		 * @param  string $location Location identifier.
		 * @return void
		 */
		public function print_clean_classes_option( $location ) {
			$loc           = Mega_Menu_Location::find( $location );
			$clean_classes = $loc ? $loc->get_setting( 'clean_classes', 'disabled' ) : 'disabled';
			$this->print_location_dialog_pill_checkbox( $location, 'clean_classes', 'enabled', 'enabled' === $clean_classes );
		}


		/**
		 * Print the checkbox option for the Unbind JavaScript Events option.
		 *
		 * @since  2.8
		 * @param  string $location        Location identifier.
		 * @param  array  $plugin_settings Saved plugin settings.
		 * @return void
		 */
		public function print_unbind_option( $location ) {
			$loc    = Mega_Menu_Location::find( $location );
			$unbind = $loc ? $loc->get_setting( 'unbind', 'enabled' ) : 'enabled';
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
						'label'    => __( 'Show / Hide', 'megamenu' ),
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
						'label'    => __( 'Accordion', 'megamenu' ),
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
				'<button type="button" class="button button-secondary button-compact megamenu-location-settings-dialog-edit-theme" aria-label="%1$s"><span class="dashicons dashicons-external" aria-hidden="true"></span></button></span>',
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
			echo '<p class="mmm-location-output-instruction">' . esc_html__( "Add the 'Max Mega Menu' widget to a widget area.", 'megamenu' ) . '</p>';
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
			echo '<p class="mmm-location-output-instruction">' . esc_html__( "Add the 'Max Mega Menu' block to any block enabled area.", 'megamenu' ) . '</p>';
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
