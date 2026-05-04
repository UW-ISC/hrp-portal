<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Themes' ) ) :

	/**
	 * Handles all admin related functionality.
	 */
	class Mega_Menu_Themes {


		/**
		 * All themes (default and custom)
		 */
		public $themes = [];


		/**
		 * Active theme
		 */
		public $active_theme = [];


		/**
		 * Active theme ID
		 */
		public $id = '';


		/**
		 * Constructor
		 *
		 * @since 1.0
		 */
		public function __construct() {

			add_action( 'wp_ajax_megamenu_save_theme', [ $this, 'ajax_save_theme' ] );
			add_action( 'wp_ajax_megamenu_get_theme_scss_variables', [ $this, 'ajax_get_theme_scss_variables' ] );
			add_action( 'admin_post_megamenu_save_theme', [ $this, 'save_theme' ] );
			add_action( 'admin_post_megamenu_add_theme', [ $this, 'create_theme' ] );
			add_action( 'admin_post_megamenu_delete_theme', [ $this, 'delete_theme' ] );
			add_action( 'admin_post_megamenu_revert_theme', [ $this, 'revert_theme' ] );
			add_action( 'admin_post_megamenu_duplicate_theme', [ $this, 'duplicate_theme' ] );
			add_action( 'admin_post_megamenu_import_theme', [ $this, 'import_theme' ] );

			add_filter( 'megamenu_menu_tabs', [ $this, 'add_themes_tab' ], 2 );
			add_action( 'megamenu_page_theme_editor', [ $this, 'theme_editor_page' ] );

			add_filter( 'wp_code_editor_settings', [ $this, 'codemirror_disable_lint' ], 99 );
		}

		/**
		 * Divi turns on code linting. This turns it off.
		 *
		 * @since 2.8
		 */
		public function codemirror_disable_lint( $settings ) {
			if ( isset( $_GET['page'] ) && 'maxmegamenu_theme_editor' === $_GET['page'] ) { // @codingStandardsIgnoreLine
				$settings['codemirror']['lint']    = false;
				$settings['codemirror']['gutters'] = [];
			}

			return $settings;
		}

		/**
		 * Add the Menu Locations tab to our available tabs
		 *
		 * @param array $tabs
		 * @since 2.8
		 */
		public function add_themes_tab( $tabs ) {
			$tabs['theme_editor'] = __( 'Menu Themes', 'megamenu' );
			return $tabs;
		}

		/**
		 *
		 * @since 1.4
		 */
		public function init() {
			if ( class_exists( 'Mega_Menu_Theme' ) ) {
				$theme_objects = Mega_Menu_Theme::get_all();
				foreach ( $theme_objects as $id => $theme ) {
					$this->themes[ $id ] = $theme->settings;
				}

				$last_updated      = max_mega_menu_get_last_updated_theme();
				$preselected_theme = isset( $this->themes[ $last_updated ] ) ? $last_updated : 'default';

				if ( isset( $_GET['theme'] ) ) { // @codingStandardsIgnoreLine
					$theme_id = sanitize_text_field( wp_unslash( $_GET['theme'] ) );
				} else {
					// Last theme saved in the editor (see max_mega_menu_save_last_updated_theme).
					$theme_id = $preselected_theme;
				}

				if ( isset( $this->themes[ $theme_id ] ) ) {
					$this->id = $theme_id;
				} else {
					$this->id = $preselected_theme;
				}

				$this->active_theme = $this->themes[ $this->id ];

			}
		}

		/**
		 * Returns the next available custom theme ID
		 *
		 * @since 1.0
		 */
		public function get_next_theme_id() {
			$last_id = 0;

			if ( $saved_themes = max_mega_menu_get_themes() ) {
				foreach ( $saved_themes as $key => $value ) {
					if ( strpos( $key, 'custom_theme' ) !== false ) {
						$parts    = explode( '_', $key );
						$theme_id = end( $parts );

						if ( $theme_id > $last_id ) {
							$last_id = $theme_id;
						}
					}
				}
			}

			$next_id = $last_id + 1;

			return $next_id;
		}


		/**
		 * Return SCSS variable names and resolved values for the current theme editor form (unsaved values supported).
		 *
		 * @since 3.9
		 */
		public function ajax_get_theme_scss_variables() {
			check_ajax_referer( 'megamenu_save_theme' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				wp_send_json_error(
					[
						'message' => __( 'Sorry, you are not allowed to do that.', 'megamenu' ),
					]
				);
			}

			if ( ! isset( $_POST['settings'] ) || ! is_array( $_POST['settings'] ) ) {
				wp_send_json_error(
					[
						'message' => __( 'Invalid request.', 'megamenu' ),
					]
				);
			}

			$prepared = $this->get_prepared_theme_for_saving();
			$theme_id = isset( $_POST['theme_id'] ) ? sanitize_text_field( wp_unslash( $_POST['theme_id'] ) ) : 'default';
			$theme    = new Mega_Menu_Theme( $theme_id, $prepared );
			$location = new Mega_Menu_Location( 'test', 'Test', [] );
			$vars     = $location->get_scss_variables( $theme );

			wp_send_json_success( [ 'variables' => $vars ] );
		}


		public function ajax_save_theme() {
			check_ajax_referer( 'megamenu_save_theme' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$test = Mega_Menu_Theme::from_settings( $this->get_prepared_theme_for_saving() )->test_compilation();

			if ( is_wp_error( $test ) ) {
				wp_send_json_error( $test->get_error_message() );
			} else {
				$this->save_theme( true );
				wp_send_json_success( 'Saved' );
			}

			wp_die();
		}


		/**
		 *
		 * @since 2.4.1
		 */
		public function get_prepared_theme_for_saving() {

			$submitted_settings = $_POST['settings'];

			if ( isset( $_POST['checkboxes'] ) ) {
				foreach ( $_POST['checkboxes'] as $checkbox => $value ) {
					if ( isset( $submitted_settings[ $checkbox ] ) ) {
						$submitted_settings[ $checkbox ] = 'on';
					} else {
						$submitted_settings[ $checkbox ] = 'off';
					}
				}
			}

			if ( is_numeric( $submitted_settings['responsive_breakpoint'] ) ) {
				$submitted_settings['responsive_breakpoint'] = $submitted_settings['responsive_breakpoint'] . 'px';
			}

			if ( isset( $submitted_settings['toggle_blocks'] ) ) {
				unset( $submitted_settings['toggle_blocks'] );
			}

			if ( isset( $submitted_settings['panel_width'] ) ) {
				$submitted_settings['panel_width'] = trim( $submitted_settings['panel_width'] );
			}

			if ( isset( $submitted_settings['panel_inner_width'] ) ) {
				$submitted_settings['panel_inner_width'] = trim( $submitted_settings['panel_inner_width'] );
			}

			$theme = array_map( 'esc_attr', $submitted_settings );

			return $theme;
		}


		/**
		 * Save changes to an exiting theme.
		 *
		 * @since 1.0
		 */
		public function save_theme( $is_ajax = false ) {

			check_admin_referer( 'megamenu_save_theme' );

			$theme = esc_attr( $_POST['theme_id'] );

			$saved_themes = max_mega_menu_get_themes();

			if ( isset( $saved_themes[ $theme ] ) ) {
				unset( $saved_themes[ $theme ] );
			}

			$prepared_theme = $this->get_prepared_theme_for_saving();

			$saved_themes[ $theme ] = $prepared_theme;

			max_mega_menu_save_themes( $saved_themes );
			max_mega_menu_save_last_updated_theme( $theme );

			do_action( 'megamenu_after_theme_save' );
			do_action( 'megamenu_delete_cache' );

			if ( ! $is_ajax ) {
				$this->redirect( admin_url( "admin.php?page=maxmegamenu_theme_editor&theme={$theme}&saved=true" ) );
				return;
			}

			return $prepared_theme;
		}


		/**
		 * Duplicate an existing theme.
		 *
		 * @since 1.0
		 */
		public function duplicate_theme() {

			check_admin_referer( 'megamenu_duplicate_theme' );

			$this->init();

			$theme = esc_attr( $_GET['theme_id'] );

			$copy = $this->themes[ $theme ];

			$saved_themes = max_mega_menu_get_themes();

			$next_id = $this->get_next_theme_id();

			$copy['title'] = $copy['title'] . ' ' . __( 'Copy', 'megamenu' );
			$copy['plugin_version'] = MEGAMENU_VERSION;

			$new_theme_id = 'custom_theme_' . $next_id;

			$saved_themes[ $new_theme_id ] = $copy;

			max_mega_menu_save_themes( $saved_themes );

			do_action( 'megamenu_after_theme_duplicate' );

			$this->redirect( admin_url( "admin.php?page=maxmegamenu_theme_editor&theme={$new_theme_id}&duplicated=true" ) );

		}


		/**
		 * Delete a theme
		 *
		 * @since 1.0
		 */
		public function delete_theme() {

			check_admin_referer( 'megamenu_delete_theme' );

			$theme = esc_attr( $_GET['theme_id'] );

			if ( $this->theme_is_being_used_by_location( $theme ) ) {

				$this->redirect( admin_url( "admin.php?page=maxmegamenu_theme_editor&theme={$theme}&deleted=false" ) );
				return;
			}

			$saved_themes = max_mega_menu_get_themes();

			if ( isset( $saved_themes[ $theme ] ) ) {
				unset( $saved_themes[ $theme ] );
			}

			max_mega_menu_save_themes( $saved_themes );

			do_action( 'megamenu_after_theme_delete' );

			do_action( 'megamenu_delete_cache' );

			$this->redirect( admin_url( 'admin.php?page=maxmegamenu_theme_editor&theme=default&deleted=true' ) );

		}


		/**
		 * Revert a theme (only available for default themes, you can't revert a custom theme)
		 *
		 * @since 1.0
		 */
		public function revert_theme() {

			check_admin_referer( 'megamenu_revert_theme' );

			$theme = esc_attr( $_GET['theme_id'] );

			$saved_themes = max_mega_menu_get_themes();

			if ( isset( $saved_themes[ $theme ] ) ) {
				unset( $saved_themes[ $theme ] );
			}

			max_mega_menu_save_themes( $saved_themes );

			do_action( 'megamenu_after_theme_revert' );

			do_action( 'megamenu_delete_cache' );

			$this->redirect( admin_url( "admin.php?page=maxmegamenu_theme_editor&theme={$theme}&reverted=true" ) );

		}


		/**
		 * Create a new custom theme
		 *
		 * @since 1.0
		 */
		public function create_theme() {

			check_admin_referer( 'megamenu_create_theme' );

			$this->init();

			$saved_themes = max_mega_menu_get_themes();

			$next_id = $this->get_next_theme_id();

			$new_theme_id = 'custom_theme_' . $next_id;

			$new_theme = Mega_Menu_Theme::get_default()->settings;

			$new_theme['title'] = "Custom {$next_id}";
			$new_theme['plugin_version'] = MEGAMENU_VERSION;

			$saved_themes[ $new_theme_id ] = $new_theme;

			max_mega_menu_save_themes( $saved_themes );

			do_action( 'megamenu_after_theme_create' );

			$this->redirect( admin_url( "admin.php?page=maxmegamenu_theme_editor&theme={$new_theme_id}&created=true" ) );

		}

		/**
		* Duplicate an existing theme.
		*
		* @since 1.8
		*/
		public function import_theme() {
			check_admin_referer( 'megamenu_import_theme' );

			$import = json_decode( stripslashes( $_POST['data'] ), true );

			$sanitized = [];

			foreach ( $import as $key => $value ) {
				if ( $key == 'custom_css' ) {
					$sanitized[ $key ] = sanitize_textarea_field( $value );
				} else {
					$sanitized[ $key ] = sanitize_text_field( $value );
				}
			}

			$import = $sanitized;

			if ( is_array( $import ) ) {
				$saved_themes                  = max_mega_menu_get_themes();
				$next_id                       = $this->get_next_theme_id();
				$import['title']               = $import['title'] . ' ' . __( ' - Imported', 'megamenu' );
				$import['plugin_version']      = MEGAMENU_VERSION;
				$new_theme_id                  = 'custom_theme_' . $next_id;
				$saved_themes[ $new_theme_id ] = $import;
				max_mega_menu_save_themes( $saved_themes );
				do_action( 'megamenu_after_theme_import' );

				$url = add_query_arg(
					[
						'page'     => 'maxmegamenu_theme_editor',
						'theme'    => $new_theme_id,
						'imported' => 'true',
					],
					admin_url( 'admin.php' )
				);

			} else {
				$url = add_query_arg(
					[
						'page'     => 'maxmegamenu_theme_editor',
						'imported' => 'false',
					],
					admin_url( 'admin.php' )
				);
			}

			$this->redirect( $url );
		}


		/**
		 * Redirect and exit
		 *
		 * @since 1.8
		 */
		public function redirect( $url ) {
			wp_redirect( $url );
			exit;
		}


		/**
		 * Active menu locations where this theme is selected, mega menu is enabled, and a menu is assigned.
		 *
		 * @since 3.9.0
		 * @param string $theme Theme ID.
		 * @return array<int, array{location: string, label: string}> List of location slug and registered label.
		 */
		public function get_theme_active_locations( $theme ) {
			$settings = get_option( 'megamenu_settings' );

			if ( ! $settings ) {
				return [];
			}

			$locations = get_nav_menu_locations();
			$menus     = get_registered_nav_menus();
			$out       = [];

			if ( count( $locations ) ) {
				foreach ( $locations as $location => $menu_id ) {
					if ( has_nav_menu( $location ) && max_mega_menu_is_enabled( $location ) && isset( $settings[ $location ]['theme'] ) && $settings[ $location ]['theme'] == $theme ) {
						$out[] = [
							'location' => $location,
							'label'    => isset( $menus[ $location ] ) ? $menus[ $location ] : $location,
						];
					}
				}
			}

			return $out;
		}


		/**
		 * Checks to see if a certain theme is in use.
		 *
		 * @since 1.0
		 * @param string $theme
		 * @return array<int, string>|false Location labels, or false if unused.
		 */
		public function theme_is_being_used_by_location( $theme ) {
			$rows = $this->get_theme_active_locations( $theme );

			if ( empty( $rows ) ) {
				return false;
			}

			return array_map(
				function ( $row ) {
					return $row['label'];
				},
				$rows
			);
		}


		/**
		 * Display messages to the user
		 *
		 * @since 1.0
		 */
		public function print_messages() {

			$this->init();

			$test = Mega_Menu_Theme::from_settings( $this->active_theme )->test_compilation();

			if ( is_wp_error( $test ) ) {
				?>
				<div class="notice notice-error is-dismissible"> 
					<p><?php echo $test->get_error_message(); ?></p>
				</div>
				<?php
			}

			if ( isset( $_GET['deleted'] ) && $_GET['deleted'] == 'false' ) {
				?>
				<div class="notice notice-error is-dismissible"> 
					<p><?php _e( 'Failed to delete theme. The theme is in use by a menu.', 'megamenu' ) ?></p>
				</div>
				<?php
			}

			if ( isset( $_GET['deleted'] ) && $_GET['deleted'] == 'true' ) {
				?>
				<div class="notice notice-success is-dismissible"> 
					<p><?php _e( 'Theme Deleted', 'megamenu' ) ?></p>
				</div>
				<?php
			}

			if ( isset( $_GET['duplicated'] ) ) {
				?>
				<div class="notice notice-success is-dismissible"> 
					<p><?php _e( 'Theme Duplicated', 'megamenu' ) ?></p>
				</div>
				<?php
			}

			if ( isset( $_GET['reverted'] ) ) {
				?>
				<div class="notice notice-success is-dismissible"> 
					<p><?php _e( 'Theme Reverted', 'megamenu' ) ?></p>
				</div>
				<?php
			}

			if ( isset( $_GET['created'] ) ) {
				?>
				<div class="notice notice-success is-dismissible"> 
					<p><?php _e( "New Theme Created. To apply this theme to a menu location, go to <i>Mega Menu > Menu Locations</i> and select this theme from the 'Theme' dropdown.", 'megamenu' ) ?></p>
				</div>
				<?php
			}

			do_action( 'megamenu_print_messages' );

		}


		/**
		 * Lists the available themes
		 *
		 * @since 1.0
		 */
		public function theme_selector() {

			$list_items = "<select id='theme_selector'>";

			foreach ( $this->themes as $id => $theme ) {

				$locations = $this->theme_is_being_used_by_location( $id );

				$selected = $id == $this->id ? 'selected=selected' : '';

				$list_items .= "<option {$selected} value='" . admin_url( "admin.php?page=maxmegamenu_theme_editor&theme={$id}" ) . "'>";

				$title = $theme['title'];

				if ( is_array( $locations ) ) {
					$title .= ' (' . implode( ', ', $locations ) . ')';
				}

				$list_items .= esc_html( $title );

				$list_items .= '</option>';
			}

			return $list_items .= '</select>';

		}

		/**
		 * Checks to see if a given string contains any of the provided search terms
		 *
		 * @param srgin $key
		 * @param array $needles
		 * @since 1.0
		 */
		private function string_contains( $key, $needles ) {

			foreach ( $needles as $needle ) {

				if ( strpos( $key, $needle ) !== false ) {
					return true;
				}
			}

			return false;

		}


		/**
		 * Documentation link for the “Highlight Current Item” theme setting.
		 *
		 * @since 3.9.0
		 * @return string
		 */
		private function get_documentation_highlight_current_item_link_html() {
			$processor = new WP_HTML_Tag_Processor( '<a>' . __( 'Documentation: Highlighting Menu Items', 'megamenu' ) . '</a>' );
			if ( $processor->next_tag( 'a' ) ) {
				$processor->set_attribute( 'href', 'https://www.megamenu.com/documentation/highlight-active-menu-items/' );
				$processor->set_attribute( 'target', '_blank' );
				$processor->set_attribute( 'rel', 'noopener noreferrer' );
			}
			return $processor->get_updated_html();
		}


		/**
		 * Documentation link for the “Panel Width” theme setting.
		 *
		 * @since 3.9.0
		 * @return string
		 */
		private function get_documentation_sub_menu_width_link_html() {
			$processor = new WP_HTML_Tag_Processor( '<a>' . __( 'Documentation: Configuring the sub menu width', 'megamenu' ) . '</a>' );
			if ( $processor->next_tag( 'a' ) ) {
				$processor->set_attribute( 'href', 'https://www.megamenu.com/documentation/adjust-sub-menu-width/' );
				$processor->set_attribute( 'target', '_blank' );
				$processor->set_attribute( 'rel', 'noopener noreferrer' );
			}
			return $processor->get_updated_html();
		}


		/**
		 * Link to the Sass language guide (custom styling tips).
		 *
		 * @since 3.9.0
		 * @return string
		 */
		private function get_sass_guide_link_html() {
			$processor = new WP_HTML_Tag_Processor( '<a>SCSS</a>' );
			if ( $processor->next_tag( 'a' ) ) {
				$processor->set_attribute( 'href', 'https://sass-lang.com/guide' );
				$processor->set_attribute( 'target', '_blank' );
				$processor->set_attribute( 'rel', 'noopener noreferrer' );
			}
			return $processor->get_updated_html();
		}


		/**
		 *
		 * @since 2.9
		 */
		public function export_theme() {
			$default_theme = Mega_Menu_Theme::get_default()->settings;

			$theme_to_export = $this->active_theme;

			$diff = [];

			foreach ( $default_theme as $key => $value ) {
				if ( isset( $theme_to_export[ $key ] ) && $theme_to_export[ $key ] != $value || $key == 'title' ) {
					$diff[ $key ] = $theme_to_export[ $key ];
				}
			}

			?>

		<div class='menu_settings menu_settings_menu_themes'>
			<h3 class='first'><?php _e( 'Export Theme', 'megamenu' ); ?></h3>
			<table class="mmm-settings-table">
				<tr>
					<td class='mega-name'>
						<div class='mega-name-title'><?php _e( 'JSON Format', 'megamenu' ); ?></div>
						<div class='mega-description'><?php _e( 'Log into the site you wish to import the theme to. Go to Mega Menu > Menu Themes, click the "Import Theme" icon and paste this into the text area:', 'megamenu' ); ?></div>
					</td>
					<td class='mega-value'>
						<?php echo "<textarea class='mega-export'>" . sanitize_textarea_field( htmlentities( json_encode( $diff ) ) ) . '</textarea>'; ?>
					</td>
				</tr>
				<tr>
					<td class='mega-name'>
						<div class='mega-name-title'><?php _e( 'PHP Format', 'megamenu' ); ?></div>
						<div class='mega-description'><?php _e( 'Paste this code into your themes functions.php file:', 'megamenu' ); ?></div>
					</td>
					<td class='mega-value'>
					   <?php
							$key  = strtolower( str_replace( ' ', '_', $theme_to_export['title'] ) );
							$key .= '_' . time();
							echo "<textarea class='mega-export'>";
							echo 'function megamenu_add_theme_' . $key . '($themes) {';
							echo "\n" . '    $themes["' . $key . '"] = array(';

						foreach ( $diff as $theme_key => $value ) {
							echo "\n        '" . $theme_key . "' => '" . $value . "',";
						}

							echo "\n" . '    );';
							echo "\n" . '    return $themes;';
							echo "\n" . '}';
							echo "\n" . 'add_filter("megamenu_themes", "megamenu_add_theme_' . $key . '");';
							echo '</textarea>';
						?>
					</td>
				</tr>
			</table>
		</div>

			<?php
		}

		public function import_theme_page() {

			?>

		<div class='menu_settings menu_settings_menu_themes'>
			<h3 class='first'><?php _e( 'Import Theme', 'megamenu' ); ?></h3>
			<table class="mmm-settings-table">
				<tr>
					<td class='mega-name'>
						<div class='mega-name-title'><?php _e( 'Import Theme', 'megamenu' ); ?></div>
						<div class='mega-description'><?php _e( 'Import a menu theme in JSON format', 'megamenu' ); ?></div>
					</td>
					<td class='mega-value'>
					   <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
							<?php wp_nonce_field( 'megamenu_import_theme' ); ?>
							<input type="hidden" name="action" value="megamenu_import_theme" />
							<textarea name='data'></textarea>
							<p class="submit">
								<button type="submit" name="submit" id="submit" class="button button-primary button-compact"><?php echo esc_html__( 'Import Theme', 'megamenu' ); ?></button>
							</p>
						</form>
					</td>
				</tr>
			</table>
		</div>

			<?php
		}


		/**
		 * Displays the theme editor form.
		 *
		 * @since 1.0
		 */
		public function theme_editor_page( $saved_settings ) {

			$this->init();

			if ( isset( $_GET['export'] ) ) {
				$this->export_theme();
				return;
			}

			if ( isset( $_GET['import'] ) ) {
				$this->import_theme_page();
				return;
			}

			$create_url = esc_url(
				add_query_arg(
					[
						'action' => 'megamenu_add_theme',
					],
					wp_nonce_url( admin_url( 'admin-post.php' ), 'megamenu_create_theme' )
				)
			);

			$duplicate_url = esc_url(
				add_query_arg(
					[
						'action'   => 'megamenu_duplicate_theme',
						'theme_id' => $this->id,
					],
					wp_nonce_url( admin_url( 'admin-post.php' ), 'megamenu_duplicate_theme' )
				)
			);

			$delete_url = esc_url(
				add_query_arg(
					[
						'action'   => 'megamenu_delete_theme',
						'theme_id' => $this->id,
					],
					wp_nonce_url( admin_url( 'admin-post.php' ), 'megamenu_delete_theme' )
				)
			);

			$revert_url = esc_url(
				add_query_arg(
					[
						'action'   => 'megamenu_revert_theme',
						'theme_id' => $this->id,
					],
					wp_nonce_url( admin_url( 'admin-post.php' ), 'megamenu_revert_theme' )
				)
			);

			$export_url = esc_url(
				add_query_arg(
					[
						'page'   => 'maxmegamenu_theme_editor',
						'theme'  => $this->id,
						'export' => 'true',
					],
					admin_url( 'admin.php' )
				)
			);

			$import_url = esc_url(
				add_query_arg(
					[
						'page'   => 'maxmegamenu_theme_editor',
						'import' => 'true',
					],
					admin_url( 'admin.php' )
				)
			);

			?>

			<?php $this->print_messages(); ?>

		<div class='menu_settings menu_settings_menu_themes'>

			<div class='theme_selector'>
				<?php $theme_active_locations = $this->get_theme_active_locations( $this->id ); ?>
				<div class='theme-selector-field'>
					<label for='theme_selector' class='mega-short-desc'><?php esc_html_e( 'Select theme to edit', 'megamenu' ); ?></label>
					<?php echo $this->theme_selector(); ?>
				</div>

				<div class='theme-editor-actions-group'>
					<p class='mega-short-desc' id='mega-theme-editor-actions-heading'><?php esc_html_e( 'Theme actions', 'megamenu' ); ?></p>
					<div class='mega-theme-editor-actions' role='toolbar' aria-labelledby='mega-theme-editor-actions-heading'>
					<?php
					$theme_action_label_create   = __( 'Add new theme', 'megamenu' );
					$theme_action_label_duplicate = __( 'Duplicate theme', 'megamenu' );
					$theme_action_label_export   = __( 'Export theme', 'megamenu' );
					$theme_action_label_import   = __( 'Import a theme', 'megamenu' );
					$theme_action_label_delete   = __( 'Delete theme', 'megamenu' );
					$theme_action_label_revert   = __( 'Revert theme', 'megamenu' );
					?>
					<a href="<?php echo esc_url( $create_url ); ?>" class="mega-theme-editor-action mega-theme-editor-action--create" data-mega-tooltip="<?php echo esc_attr( $theme_action_label_create ); ?>" aria-label="<?php echo esc_attr( $theme_action_label_create ); ?>">
						<span class="dashicons dashicons-welcome-add-page" aria-hidden="true"></span>
					</a>
					<a href="<?php echo esc_url( $duplicate_url ); ?>" class="mega-theme-editor-action mega-theme-editor-action--duplicate" data-mega-tooltip="<?php echo esc_attr( $theme_action_label_duplicate ); ?>" aria-label="<?php echo esc_attr( $theme_action_label_duplicate ); ?>">
						<span class="dashicons dashicons-images-alt2" aria-hidden="true"></span>
					</a>
					<a href="<?php echo esc_url( $export_url ); ?>" class="mega-theme-editor-action mega-theme-editor-action--export" data-mega-tooltip="<?php echo esc_attr( $theme_action_label_export ); ?>" aria-label="<?php echo esc_attr( $theme_action_label_export ); ?>">
						<span class="dashicons dashicons-upload" aria-hidden="true"></span>
					</a>
					<a href="<?php echo esc_url( $import_url ); ?>" class="mega-theme-editor-action mega-theme-editor-action--import" data-mega-tooltip="<?php echo esc_attr( $theme_action_label_import ); ?>" aria-label="<?php echo esc_attr( $theme_action_label_import ); ?>">
						<span class="dashicons dashicons-download" aria-hidden="true"></span>
					</a>
					<?php if ( $this->string_contains( $this->id, [ 'custom' ] ) ) : ?>
						<a href="<?php echo esc_url( $delete_url ); ?>" class="mega-theme-editor-action mega-theme-editor-action--delete delete megamenu-destructive-confirm" data-mega-tooltip="<?php echo esc_attr( $theme_action_label_delete ); ?>" aria-label="<?php echo esc_attr( $theme_action_label_delete ); ?>">
							<span class="dashicons dashicons-trash" aria-hidden="true"></span>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( $revert_url ); ?>" class="mega-theme-editor-action mega-theme-editor-action--revert megamenu-destructive-confirm" data-mega-tooltip="<?php echo esc_attr( $theme_action_label_revert ); ?>" aria-label="<?php echo esc_attr( $theme_action_label_revert ); ?>">
							<span class="dashicons dashicons-update-alt" aria-hidden="true"></span>
						</a>
					<?php endif; ?>
					</div>
				</div>

				<?php if ( ! empty( $theme_active_locations ) ) : ?>
				<div class='theme-editor-preview-group'>
					<p class='mega-short-desc' id='mega-theme-editor-preview-heading'><?php esc_html_e( 'Preview', 'megamenu' ); ?></p>
					<div class='mega-theme-editor-actions' role='toolbar' aria-labelledby='mega-theme-editor-preview-heading'>
					<?php
					foreach ( $theme_active_locations as $loc_row ) {
						$loc_slug  = $loc_row['location'];
						$loc_label = isset( $loc_row['label'] ) ? (string) $loc_row['label'] : $loc_slug;
						$safe_name = wp_strip_all_tags( $loc_label );
						$preview_tooltip = sprintf(
							/* translators: %s: menu location name (e.g. Primary Menu). */
							__( 'Save theme and Preview location: %s', 'megamenu' ),
							$safe_name
						);
						echo Mega_Menu_Preview::render_preview_link(
							$loc_slug,
							$loc_label,
							[
								'inactive'                   => ! Mega_Menu_Preview::is_previewable( $loc_slug ),
								'icon_only'                  => true,
								'tooltip'                    => $preview_tooltip,
								'save_theme_before_preview'  => true,
							]
						); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup from Mega_Menu_Preview::render_preview_link().
					}
					?>
					</div>
				</div>
				<?php endif; ?>

			</div>

			<?php

			$saved_settings = get_option( 'megamenu_settings' );

			if ( isset( $saved_settings['css'] ) && $saved_settings['css'] == 'disabled' ) {
				?>
					<div class="notice notice-error is-dismissible">
						<p><?php esc_html_e( 'CSS Output (under Mega Menu > General Settings) has been disabled. Therefore, changes made within the theme editor will not be applied to your menu.', 'megamenu' ); ?></p>
					</div>
				<?php
			}

			$locations = $this->theme_is_being_used_by_location( $this->id );

			if ( ! $locations && ! isset( $_GET['created'] ) ) {
				?>
					<div class="notice notice-warning is-dismissible">
						<p>
						<?php
						echo wp_kses(
							__( "This menu theme is not currently active as it has not been applied to any menu locations. You may wish to check you are editing the correct menu theme - you can choose a different theme to edit using the 'Select theme to edit' selector above. Alternatively, to apply this theme to a menu go to <i>Appearance > Menus > Max Mega Menu Settings</i> and select this theme from the 'Theme' dropdown.", 'megamenu' ),
							array( 'i' => array() )
						);
						?>
						</p>
					</div>
				<?php
			}

			?>

			<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post" class="theme_editor">
				<input type="hidden" name="theme_id" value="<?php echo esc_attr( $this->id ); ?>" />
				<input type="hidden" name="action" value="megamenu_save_theme" />
				<?php wp_nonce_field( 'megamenu_save_theme' ); ?>

				<?php

					$settings = apply_filters(
						'megamenu_theme_editor_settings',
						[

							'general'        => [
								'title'    => __( 'General Settings', 'megamenu' ),
								'settings' => [
									'title'       => [
										'priority'    => 10,
										'title'       => __( 'Theme Title', 'megamenu' ),
										'description' => '',
										'settings'    => [
											[
												'title' => '',
												'type'  => 'freetext',
												'key'   => 'title',
											],
										],
									],
									'arrow'       => [
										'priority'    => 20,
										'title'       => __( 'Arrows', 'megamenu' ),
										'description' => __( 'Select the arrow icons used within the menu.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Up', 'megamenu' ),
												'type'  => 'arrow',
												'key'   => 'arrow_up',
											],
											[
												'title' => __( 'Down', 'megamenu' ),
												'type'  => 'arrow',
												'key'   => 'arrow_down',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'arrow',
												'key'   => 'arrow_left',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'arrow',
												'key'   => 'arrow_right',
											]
										],
									],
									'line_height' => [
										'priority'    => 30,
										'title'       => __( 'Line Height', 'megamenu' ),
										'description' => __( 'Set the general line height to use in the sub menu contents.', 'megamenu' ),
										'settings'    => [
											[
												'title' => '',
												'type'  => 'freetext',
												'key'   => 'line_height',
											],
										],
									],
									'z_index'     => [
										'priority'    => 40,
										'title'       => __( 'Z Index', 'megamenu' ),
										'description' => __( 'Set the z-index to ensure the sub menus appear ontop of other content.', 'megamenu' ),
										'settings'    => [
											[
												'title' => '',
												'type'  => 'freetext',
												'key'   => 'z_index',
												'validation' => 'int',
											],
										],
									],
									'shadow'      => [
										'priority'    => 50,
										'title'       => __( 'Shadow', 'megamenu' ),
										'description' => __( 'Apply a shadow to mega and flyout menus.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Enabled', 'megamenu' ),
												'type'  => 'checkbox',
												'key'   => 'shadow',
											],
											[
												'title' => __( 'Horizontal', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'shadow_horizontal',
												'validation' => 'px',
											],
											[
												'title' => __( 'Vertical', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'shadow_vertical',
												'validation' => 'px',
											],
											[
												'title' => __( 'Blur', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'shadow_blur',
												'validation' => 'px',
											],
											[
												'title' => __( 'Spread', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'shadow_spread',
												'validation' => 'px',
											],
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'shadow_color',
											],
										],
									],
									'keyboard_highlight'      => [
										'priority'    => 55,
										'title'       => __( 'Keyboard Highlight Outline', 'megamenu' ),
										'description' => __( 'Set the outline style for menu items when they receive focus using keyboard navigation.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'keyboard_highlight_color',
											],
											[
												'title' => __( 'Width', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'keyboard_highlight_width',
												'validation' => 'px',
											],
											[
												'title' => __( 'Offset', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'keyboard_highlight_offset',
												'validation' => 'px',
											],
										],
									],
									'transitions' => [
										'priority'    => 60,
										'title'       => __( 'Hover Transitions', 'megamenu' ),
										'description' => __( 'Apply hover transitions to menu items. Note: Transitions will not apply to gradient backgrounds.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Enabled', 'megamenu' ),
												'type'  => 'checkbox',
												'key'   => 'transitions',
											],
										],
									],
									'resets'      => [
										'priority'    => 70,
										'title'       => __( 'Reset Widget Styling', 'megamenu' ),
										'description' => __( 'Caution: Reset the styling of widgets within the mega menu? This may break the styling of widgets that you have added to your sub menus. Default: Disabled.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Enabled', 'megamenu' ),
												'type'  => 'checkbox',
												'key'   => 'resets',
											],
										],
									],
									'use_flex_css'      => [
										'priority'    => 80,
										'title'       => __( 'Use Flex CSS', 'megamenu' ),
										'description' => __( 'Experimental: Use flexbox to style the menu. Testing purposes only. Default: Disabled.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Enabled', 'megamenu' ),
												'type'  => 'checkbox',
												'key'   => 'use_flex_css',
											],
										],
									],
								],
							],
							'menu_bar'       => [
								'title'    => __( 'Menu Bar', 'megamenu' ),
								'settings' => [
									'menu_item_height'     => [
										'priority'    => 05,
										'title'       => __( 'Menu Height', 'megamenu' ),
										'description' => __( 'Define the height of each top level menu item link. This value plus the Menu Padding (top and bottom) settings define the overall height of the menu bar.', 'megamenu' ),
										'settings'    => [
											[
												'title' => '',
												'type'  => 'freetext',
												'key'   => 'menu_item_link_height',
												'validation' => 'px',
											],
										],
									],
									'menu_background'      => [
										'priority'    => 10,
										'title'       => __( 'Menu Background', 'megamenu' ),
										'description' => __( "The background color for the main menu bar. Set each value to transparent for a 'button' style menu.", 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'From', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'container_background_from',
											],
											[
												'title' => __( 'Copy', 'megamenu' ),
												'type'  => 'copy_color',
												'key'   => 'copy_color',
											],
											[
												'title' => __( 'To', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'container_background_to',
											],
										],
									],
									'menu_padding'         => [
										'priority'    => 20,
										'title'       => __( 'Menu Padding', 'megamenu' ),
										'description' => __( 'Padding for the main menu bar.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'container_padding_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'container_padding_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'container_padding_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'container_padding_left',
												'validation' => 'px',
											],
										],
									],
									'menu_border_radius'   => [
										'priority'    => 30,
										'title'       => __( 'Menu Border Radius', 'megamenu' ),
										'description' => __( 'Set a border radius on the main menu bar.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'container_border_radius_top_left',
												'validation' => 'px',
											],
											[
												'title' => __( 'Top Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'container_border_radius_top_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'container_border_radius_bottom_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'container_border_radius_bottom_left',
												'validation' => 'px',
											],
										],
									],
									'top_level_menu_items' => [
										'priority'    => 50,
										'title'       => __( 'Top Level Menu Items', 'megamenu' ),
										'description' => '',
									],
									'menu_item_align'      => [
										'priority'    => 55,
										'title'       => __( 'Menu Items Align', 'megamenu' ),
										'description' => __( 'Align <i>all</i> menu items to the left (default), centrally or to the right.', 'megamenu' ),
										'info'        => [ __( "This option will apply to all menu items. To align an individual menu item to the right, edit the menu item itself and set 'Menu Item Align' to 'Right'.", 'megamenu' ) ],
										'settings'    => [
											[
												'title' => '',
												'type'  => 'align',
												'key'   => 'menu_item_align',
											],
										],
									],
									'menu_item_font'       => [
										'priority'    => 60,
										'title'       => __( 'Item Font', 'megamenu' ),
										'description' => __( 'The font to use for each top level menu item.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'menu_item_link_color',
											],
											[
												'title' => __( 'Size', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'menu_item_link_font_size',
												'validation' => 'px',
											],
											[
												'title' => __( 'Family', 'megamenu' ),
												'type'  => 'font',
												'key'   => 'menu_item_link_font',
											],
											[
												'title' => __( 'Transform', 'megamenu' ),
												'type'  => 'transform',
												'key'   => 'menu_item_link_text_transform',
											],
											[
												'title' => __( 'Weight', 'megamenu' ),
												'type'  => 'weight',
												'key'   => 'menu_item_link_weight',
											],
											[
												'title' => __( 'Decoration', 'megamenu' ),
												'type'  => 'decoration',
												'key'   => 'menu_item_link_text_decoration',
											],
											[
												'title' => __( 'Align', 'megamenu' ),
												'type'  => 'align',
												'key'   => 'menu_item_link_text_align',
											],
										],
									],
									'menu_item_font_hover' => [
										'priority'    => 65,
										'title'       => __( 'Item Font (Hover)', 'megamenu' ),
										'description' => __( 'Set the font to use for each top level menu item (on hover).', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'menu_item_link_color_hover',
											],
											[
												'title' => __( 'Weight', 'megamenu' ),
												'type'  => 'weight',
												'key'   => 'menu_item_link_weight_hover',
											],
											[
												'title' => __( 'Decoration', 'megamenu' ),
												'type'  => 'decoration',
												'key'   => 'menu_item_link_text_decoration_hover',
											],
										],
									],
									'menu_item_background' => [
										'priority'    => 70,
										'title'       => __( 'Item Background', 'megamenu' ),
										'description' => __( "The background color for each top level menu item. Tip: Set these values to transparent if you've already set a background color on the menu bar.", 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'From', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'menu_item_background_from',
											],
											[
												'title' => __( 'Copy', 'megamenu' ),
												'type'  => 'copy_color',
												'key'   => 'copy_color',
											],
											[
												'title' => __( 'To', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'menu_item_background_to',
											],
										],
									],
									'menu_item_background_hover' => [
										'priority'    => 75,
										'title'       => __( 'Item Background (Hover)', 'megamenu' ),
										'description' => __( 'The background color for a top level menu item (on hover).', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'From', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'menu_item_background_hover_from',
											],
											[
												'title' => __( 'Copy', 'megamenu' ),
												'type'  => 'copy_color',
												'key'   => 'copy_color',
											],
											[
												'title' => __( 'To', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'menu_item_background_hover_to',
											],
										],
									],
									'menu_item_spacing'    => [
										'priority'    => 80,
										'title'       => __( 'Item Spacing', 'megamenu' ),
										'description' => __( 'Define the size of the gap between each top level menu item.', 'megamenu' ),
										'settings'    => [
											[
												'title' => '',
												'type'  => 'freetext',
												'key'   => 'menu_item_spacing',
												'validation' => 'px',
											],
										],
									],

									'menu_item_padding'    => [
										'priority'    => 85,
										'title'       => __( 'Item Padding', 'megamenu' ),
										'description' => __( 'Set the padding for each top level menu item.', 'megamenu' ),
										'info'        => [ __( "Generally we advise against using the Top and Bottom options here. Use the 'Menu Height' setting to determine the height of your top level menu items.", 'megamenu' ) ],
										'settings'    => [
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'menu_item_link_padding_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'menu_item_link_padding_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'menu_item_link_padding_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'menu_item_link_padding_left',
												'validation' => 'px',
											],
										],
									],
									'menu_item_border'     => [
										'priority'    => 90,
										'title'       => __( 'Item Border', 'megamenu' ),
										'description' => __( 'Set the border to display on each top level menu item.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'menu_item_border_color',
											],
											[
												'title' => __( 'Color (Hover)', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'menu_item_border_color_hover',
											],
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'menu_item_border_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'menu_item_border_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'menu_item_border_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'menu_item_border_left',
												'validation' => 'px',
											],
										],
									],
									'menu_item_border_radius' => [
										'priority'    => 95,
										'title'       => __( 'Item Border Radius', 'megamenu' ),
										'description' => __( 'Set rounded corners for each top level menu item.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'menu_item_link_border_radius_top_left',
												'validation' => 'px',
											],
											[
												'title' => __( 'Top Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'menu_item_link_border_radius_top_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'menu_item_link_border_radius_bottom_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'menu_item_link_border_radius_bottom_left',
												'validation' => 'px',
											],
										],
									],
									'menu_item_divider'    => [
										'priority'    => 160,
										'title'       => __( 'Item Divider', 'megamenu' ),
										'description' => __( 'Show a small divider bar between each menu item.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Enabled', 'megamenu' ),
												'type'  => 'checkbox',
												'key'   => 'menu_item_divider',
											],
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'menu_item_divider_color',
											],
											[
												'title' => __( 'Glow Opacity', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'menu_item_divider_glow_opacity',
												'validation' => 'float',
											],
										],
									],
									'menu_item_highlight'  => [
										'priority'    => 170,
										'title'       => __( 'Highlight Current Item', 'megamenu' ),
										'description' => __( "Apply the 'hover' styling to current menu items. Applies to top level menu items only.", 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Enabled', 'megamenu' ),
												'type'  => 'checkbox',
												'key'   => 'menu_item_highlight_current',
											],
										],
										'info'        => [
											$this->get_documentation_highlight_current_item_link_html(),
										],
									],
								],
							],
							'mega_panels'    => [
								'title'    => __( 'Mega Menus', 'megamenu' ),
								'settings' => [
									'panel_width'          => [
										'priority'    => 10,
										'title'       => __( 'Panel Width', 'megamenu' ),
										'description' => __( 'Configure the width of the sub menu.', 'megamenu' ),
										'info'        => [
											__( "Set the Outer Width to 100vw for a full width sub menu", 'megamenu' ),
											__( 'Set the Outer Width to 100% to make the sub menu the same width as the menu bar', 'megamenu' ),
											__( 'Set the Outer Width to a jQuery selector (e.g. body, #container, .page) to align the sub menu with existing page element', 'megamenu' ),
											$this->get_documentation_sub_menu_width_link_html(),
										],
										'settings'    => [
											[
												'title' => __( 'Outer Width', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_width',
											],
											[
												'title' => __( 'Inner Width', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_inner_width',
											],
										],
									],
									'panel_background'     => [
										'priority'    => 20,
										'title'       => __( 'Panel Background', 'megamenu' ),
										'description' => __( 'Set a background color for a whole sub menu.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'From', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_background_from',
											],
											[
												'title' => __( 'Copy', 'megamenu' ),
												'type'  => 'copy_color',
												'key'   => 'copy_color',
											],
											[
												'title' => __( 'To', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_background_to',
											],
										],
									],
									'panel_padding'        => [
										'priority'    => 30,
										'title'       => __( 'Panel Padding', 'megamenu' ),
										'description' => __( 'Set the padding for the whole sub menu. Set these values 0px if you wish your sub menu content to go edge-to-edge.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_padding_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_padding_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_padding_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_padding_left',
												'validation' => 'px',
											],
										],
									],
									'panel_border'         => [
										'priority'    => 40,
										'title'       => __( 'Panel Border', 'megamenu' ),
										'description' => __( 'Set the border to display on the sub menu.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_border_color',
											],
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_border_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_border_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_border_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_border_left',
												'validation' => 'px',
											],
										],
									],
									'panel_border_radius'  => [
										'priority'    => 50,
										'title'       => __( 'Panel Border Radius', 'megamenu' ),
										'description' => __( 'Set rounded corners for the sub menu.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_border_radius_top_left',
												'validation' => 'px',
											],
											[
												'title' => __( 'Top Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_border_radius_top_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_border_radius_bottom_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_border_radius_bottom_left',
												'validation' => 'px',
											],
										],
									],
									'widget_padding'       => [
										'priority'    => 60,
										'title'       => __( 'Column Padding', 'megamenu' ),
										'description' => __( 'Use this to define the amount of space around each widget / set of menu items within the sub menu.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_widget_padding_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_widget_padding_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_widget_padding_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_widget_padding_left',
												'validation' => 'px',
											],
										],
									],
									'mega_menu_widgets'    => [
										'priority'    => 65,
										'title'       => __( 'Widgets', 'megamenu' ),
										'description' => '',
									],
									'widget_heading_font'  => [
										'priority'    => 70,
										'title'       => __( 'Title Font', 'megamenu' ),
										'description' => __( 'Set the font to use Widget headers in the mega menu. Tip: set this to the same style as the Second Level Menu Item Font to keep your styling consistent.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_header_color',
											],
											[
												'title' => __( 'Size', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_header_font_size',
												'validation' => 'px',
											],
											[
												'title' => __( 'Family', 'megamenu' ),
												'type'  => 'font',
												'key'   => 'panel_header_font',
											],
											[
												'title' => __( 'Transform', 'megamenu' ),
												'type'  => 'transform',
												'key'   => 'panel_header_text_transform',
											],
											[
												'title' => __( 'Weight', 'megamenu' ),
												'type'  => 'weight',
												'key'   => 'panel_header_font_weight',
											],
											[
												'title' => __( 'Decoration', 'megamenu' ),
												'type'  => 'decoration',
												'key'   => 'panel_header_text_decoration',
											],
											[
												'title' => __( 'Align', 'megamenu' ),
												'type'  => 'align',
												'key'   => 'panel_header_text_align',
											],
										],
									],
									'widget_heading_padding' => [
										'priority'    => 90,
										'title'       => __( 'Title Padding', 'megamenu' ),
										'description' => __( 'Set the padding for the widget headings.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_header_padding_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_header_padding_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_header_padding_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_header_padding_left',
												'validation' => 'px',
											],
										],
									],
									'widget_heading_margin' => [
										'priority'    => 100,
										'title'       => __( 'Title Margin', 'megamenu' ),
										'description' => __( 'Set the margin for the widget headings.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_header_margin_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_header_margin_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_header_margin_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_header_margin_left',
												'validation' => 'px',
											],
										],
									],
									'widget_header_border' => [
										'priority'    => 110,
										'title'       => __( 'Title Border', 'megamenu' ),
										'description' => __( 'Set the border for the widget headings.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_header_border_color',
											],
											[
												'title' => __( 'Color (Hover)', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_header_border_color_hover',
											],
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_header_border_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_header_border_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_header_border_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_header_border_left',
												'validation' => 'px',
											],
										],
									],
									'widget_content_font'  => [
										'priority'    => 115,
										'title'       => __( 'Content Font', 'megamenu' ),
										'description' => __( 'Set the font to use for panel contents.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_font_color',
											],
											[
												'title' => __( 'Size', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_font_size',
												'validation' => 'px',
											],
											[
												'title' => __( 'Family', 'megamenu' ),
												'type'  => 'font',
												'key'   => 'panel_font_family',
											],
										],
									],
									'second_level_menu_items' => [
										'priority'    => 120,
										'title'       => __( 'Second Level Menu Items', 'megamenu' ),
										'description' => '',
									],
									'second_level_font'    => [
										'priority'    => 130,
										'title'       => __( 'Item Font', 'megamenu' ),
										'description' => __( "Set the font for second level menu items when they're displayed in a Mega Menu.", 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_second_level_font_color',
											],
											[
												'title' => __( 'Size', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_second_level_font_size',
												'validation' => 'px',
											],
											[
												'title' => __( 'Family', 'megamenu' ),
												'type'  => 'font',
												'key'   => 'panel_second_level_font',
											],
											[
												'title' => __( 'Transform', 'megamenu' ),
												'type'  => 'transform',
												'key'   => 'panel_second_level_text_transform',
											],
											[
												'title' => __( 'Weight', 'megamenu' ),
												'type'  => 'weight',
												'key'   => 'panel_second_level_font_weight',
											],
											[
												'title' => __( 'Decoration', 'megamenu' ),
												'type'  => 'decoration',
												'key'   => 'panel_second_level_text_decoration',
											],
											[
												'title' => __( 'Align', 'megamenu' ),
												'type'  => 'align',
												'key'   => 'panel_second_level_text_align',
											],
										],
									],
									'second_level_font_hover' => [
										'priority'    => 140,
										'title'       => __( 'Item Font (Hover)', 'megamenu' ),
										'description' => __( 'Set the font style on hover.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_second_level_font_color_hover',
											],
											[
												'title' => __( 'Weight', 'megamenu' ),
												'type'  => 'weight',
												'key'   => 'panel_second_level_font_weight_hover',
											],
											[
												'title' => __( 'Decoration', 'megamenu' ),
												'type'  => 'decoration',
												'key'   => 'panel_second_level_text_decoration_hover',
											],
										],
									],
									'second_level_background_hover' => [
										'priority'    => 150,
										'title'       => __( 'Item Background (Hover)', 'megamenu' ),
										'description' => __( 'Set the background hover color for second level menu items.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'From', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_second_level_background_hover_from',
											],
											[
												'title' => __( 'Copy', 'megamenu' ),
												'type'  => 'copy_color',
												'key'   => 'copy_color',
											],
											[
												'title' => __( 'To', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_second_level_background_hover_to',
											],
										],
									],
									'second_level_padding' => [
										'priority'    => 160,
										'title'       => __( 'Item Padding', 'megamenu' ),
										'description' => __( 'Set the padding for the second level menu items.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_second_level_padding_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_second_level_padding_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_second_level_padding_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_second_level_padding_left',
												'validation' => 'px',
											],
										],
									],
									'second_level_margin'  => [
										'priority'    => 170,
										'title'       => __( 'Item Margin', 'megamenu' ),
										'description' => __( 'Set the margin for the second level menu items.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_second_level_margin_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_second_level_margin_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_second_level_margin_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_second_level_margin_left',
												'validation' => 'px',
											],
										],
									],
									'second_level_border'  => [
										'priority'    => 180,
										'title'       => __( 'Item Border', 'megamenu' ),
										'description' => __( 'Set the border for the second level menu items.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_second_level_border_color',
											],
											[
												'title' => __( 'Color (Hover)', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_second_level_border_color_hover',
											],
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_second_level_border_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_second_level_border_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_second_level_border_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_second_level_border_left',
												'validation' => 'px',
											],
										],
									],
									'third_level_menu_items' => [
										'priority'    => 190,
										'title'       => __( 'Third Level Menu Items', 'megamenu' ),
										'description' => '',
									],
									'third_level_font'     => [
										'priority'    => 200,
										'title'       => __( 'Item Font', 'megamenu' ),
										'description' => __( "Set the font for third level menu items when they're displayed in a Mega Menu.", 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_third_level_font_color',
											],
											[
												'title' => __( 'Size', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_third_level_font_size',
												'validation' => 'px',
											],
											[
												'title' => __( 'Family', 'megamenu' ),
												'type'  => 'font',
												'key'   => 'panel_third_level_font',
											],
											[
												'title' => __( 'Transform', 'megamenu' ),
												'type'  => 'transform',
												'key'   => 'panel_third_level_text_transform',
											],
											[
												'title' => __( 'Weight', 'megamenu' ),
												'type'  => 'weight',
												'key'   => 'panel_third_level_font_weight',
											],
											[
												'title' => __( 'Decoration', 'megamenu' ),
												'type'  => 'decoration',
												'key'   => 'panel_third_level_text_decoration',
											],
											[
												'title' => __( 'Align', 'megamenu' ),
												'type'  => 'align',
												'key'   => 'panel_third_level_text_align',
											],
										],
									],
									'third_level_font_hover' => [
										'priority'    => 210,
										'title'       => __( 'Item Font (Hover)', 'megamenu' ),
										'description' => __( 'Set the font style on hover.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_third_level_font_color_hover',
											],
											[
												'title' => __( 'Weight', 'megamenu' ),
												'type'  => 'weight',
												'key'   => 'panel_third_level_font_weight_hover',
											],
											[
												'title' => __( 'Decoration', 'megamenu' ),
												'type'  => 'decoration',
												'key'   => 'panel_third_level_text_decoration_hover',
											],
										],
									],
									'third_level_background_hover' => [
										'priority'    => 220,
										'title'       => __( 'Item Background (Hover)', 'megamenu' ),
										'description' => __( 'Set the background hover color for third level menu items.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'From', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_third_level_background_hover_from',
											],
											[
												'title' => __( 'Copy', 'megamenu' ),
												'type'  => 'copy_color',
												'key'   => 'copy_color',
											],
											[
												'title' => __( 'To', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_third_level_background_hover_to',
											],
										],
									],
									'third_level_padding'  => [
										'priority'    => 230,
										'title'       => __( 'Item Padding', 'megamenu' ),
										'description' => __( 'Set the padding for the third level menu items.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_third_level_padding_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_third_level_padding_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_third_level_padding_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_third_level_padding_left',
												'validation' => 'px',
											],
										],
									],

									'third_level_margin'   => [
										'priority'    => 235,
										'title'       => __( 'Item Margin', 'megamenu' ),
										'description' => __( 'Set the margin for the third level menu items.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_third_level_margin_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_third_level_margin_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_third_level_margin_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_third_level_margin_left',
												'validation' => 'px',
											],
										],
									],
									'third_level_border'   => [
										'priority'    => 237,
										'title'       => __( 'Item Border', 'megamenu' ),
										'description' => __( 'Set the border for the third level menu items.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_third_level_border_color',
											],
											[
												'title' => __( 'Color (Hover)', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'panel_third_level_border_color_hover',
											],
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_third_level_border_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_third_level_border_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_third_level_border_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'panel_third_level_border_left',
												'validation' => 'px',
											],
										],
									],
								],
							],
							'flyout_menus'   => [
								'title'    => __( 'Flyout Menus', 'megamenu' ),
								'settings' => [
									'flyout_menu_background' => [
										'priority'    => 10,
										'title'       => __( 'Sub Menu Background', 'megamenu' ),
										'description' => __( 'Set the background color for the flyout menu.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'From', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'flyout_menu_background_from',
											],
											[
												'title' => __( 'Copy', 'megamenu' ),
												'type'  => 'copy_color',
												'key'   => 'copy_color',
											],
											[
												'title' => __( 'To', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'flyout_menu_background_to',
											],
										],
									],
									'flyout_menu_width'   => [
										'priority'    => 20,
										'title'       => __( 'Sub Menu Width', 'megamenu' ),
										'description' => __( 'The width of each flyout menu.', 'megamenu' ),
										'info'        => [
											__( "For a flexible sub menu width set this value to 'max-content'", 'megamenu' ),
											__( "For a fixed sub menu width use a value such as 250px, 15rem, 10vw etc.", 'megamenu' ),
										],
										'settings'    => [
											[
												'title' => '',
												'type'  => 'freetext',
												'key'   => 'flyout_width',
												'validation' => 'px',
											],
										],
									],
									'flyout_menu_padding' => [
										'priority'    => 30,
										'title'       => __( 'Sub Menu Padding', 'megamenu' ),
										'description' => __( 'Set the padding for the whole flyout menu.', 'megamenu' ),
										'info'        => [ __( "Only suitable for single level flyout menus. If you're using multi level flyout menus set these values to 0px.", 'megamenu' ) ],
										'settings'    => [
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_padding_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_padding_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_padding_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_padding_left',
												'validation' => 'px',
											],
										],
									],
									'flyout_menu_border'  => [
										'priority'    => 40,
										'title'       => __( 'Sub Menu Border', 'megamenu' ),
										'description' => __( 'Set the border for the flyout menu.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'flyout_border_color',
											],
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_border_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_border_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_border_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_border_left',
												'validation' => 'px',
											],
										],
									],
									'flyout_menu_border_radius' => [
										'priority'    => 50,
										'title'       => __( 'Sub Menu Border Radius', 'megamenu' ),
										'description' => __( 'Set rounded corners for flyout menus. Rounded corners will be applied to all flyout menu levels.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_border_radius_top_left',
												'validation' => 'px',
											],
											[
												'title' => __( 'Top Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_border_radius_top_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_border_radius_bottom_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_border_radius_bottom_left',
												'validation' => 'px',
											],
										],
									],
									'flyout_menu_item_background' => [
										'priority'    => 60,
										'title'       => __( 'Menu Item Background', 'megamenu' ),
										'description' => __( 'Set the background color for a flyout menu item.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'From', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'flyout_background_from',
											],
											[
												'title' => __( 'Copy', 'megamenu' ),
												'type'  => 'copy_color',
												'key'   => 'copy_color',
											],
											[
												'title' => __( 'To', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'flyout_background_to',
											],
										],
									],
									'flyout_menu_item_background_hover' => [
										'priority'    => 70,
										'title'       => __( 'Menu Item Background (Hover)', 'megamenu' ),
										'description' => __( 'Set the background color for a flyout menu item (on hover).', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'From', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'flyout_background_hover_from',
											],
											[
												'title' => __( 'Copy', 'megamenu' ),
												'type'  => 'copy_color',
												'key'   => 'copy_color',
											],
											[
												'title' => __( 'To', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'flyout_background_hover_to',
											],
										],
									],
									'flyout_menu_item_height' => [
										'priority'    => 80,
										'title'       => __( 'Menu Item Height', 'megamenu' ),
										'description' => __( 'The height of each flyout menu item.', 'megamenu' ),
										'settings'    => [
											[
												'title' => '',
												'type'  => 'freetext',
												'key'   => 'flyout_link_height',
												'validation' => 'px',
											],
										],
									],
									'flyout_menu_item_padding' => [
										'priority'    => 90,
										'title'       => __( 'Menu Item Padding', 'megamenu' ),
										'description' => __( 'Set the padding for each flyout menu item.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_link_padding_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_link_padding_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_link_padding_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_link_padding_left',
												'validation' => 'px',
											],
										],
									],
									'flyout_menu_item_font' => [
										'priority'    => 100,
										'title'       => __( 'Menu Item Font', 'megamenu' ),
										'description' => __( 'Set the font for the flyout menu items.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'flyout_link_color',
											],
											[
												'title' => __( 'Size', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'flyout_link_size',
												'validation' => 'px',
											],
											[
												'title' => __( 'Family', 'megamenu' ),
												'type'  => 'font',
												'key'   => 'flyout_link_family',
											],
											[
												'title' => __( 'Transform', 'megamenu' ),
												'type'  => 'transform',
												'key'   => 'flyout_link_text_transform',
											],
											[
												'title' => __( 'Weight', 'megamenu' ),
												'type'  => 'weight',
												'key'   => 'flyout_link_weight',
											],
											[
												'title' => __( 'Decoration', 'megamenu' ),
												'type'  => 'decoration',
												'key'   => 'flyout_link_text_decoration',
											],
										],
									],
									'flyout_menu_item_font_hover' => [
										'priority'    => 110,
										'title'       => __( 'Menu Item Font (Hover)', 'megamenu' ),
										'description' => __( 'Set the font for the flyout menu items.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'flyout_link_color_hover',
											],
											[
												'title' => __( 'Weight', 'megamenu' ),
												'type'  => 'weight',
												'key'   => 'flyout_link_weight_hover',
											],
											[
												'title' => __( 'Decoration', 'megamenu' ),
												'type'  => 'decoration',
												'key'   => 'flyout_link_text_decoration_hover',
											],
										],
									],
									'flyout_menu_item_divider' => [
										'priority'    => 120,
										'title'       => __( 'Menu Item Divider', 'megamenu' ),
										'description' => __( 'Show a line divider below each menu item.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Enabled', 'megamenu' ),
												'type'  => 'checkbox',
												'key'   => 'flyout_menu_item_divider',
											],
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'flyout_menu_item_divider_color',
											],
										],
									],
								],
							],
							'mobile_menu'    => [
								'title'    => __( 'Mobile Menu', 'megamenu' ),
								'settings' => [
									'responsive_breakpoint' => [
										'priority'    => 2,
										'title'       => __( 'Responsive Breakpoint', 'megamenu' ),
										'description' => __( 'The menu will be converted to a mobile menu when the browser width is below this value.', 'megamenu' ),
										'settings'    => [
											[
												'title' => '',
												'type'  => 'freetext',
												'key'   => 'responsive_breakpoint',
												'validation' => 'px',
											],
										],
									],
									'responsive_breakpoint_disabled' => [
										'priority'    => 3,
										'title'       => __( "The 'Responsive Breakpoint' option has been set to 0px. The desktop version of the menu will be displayed for all browsers (regardless of the browser width), so the following options are disabled.", 'megamenu' ),
										'description' => '',
									],
									'mobile_toggle_bar'   => [
										'priority'    => 4,
										'title'       => __( 'Mobile Toggle Bar', 'megamenu' ),
										'description' => '',
									],
									'mobile_toggle_disabled' => [
										'priority'    => 5,
										'title'       => __( "The 'Disable Mobile Toggle Bar' option has been enabled. The following options are disabled as the mobile toggle bar will not be displayed.", 'megamenu' ),
										'description' => '',
									],
									'toggle_bar_background' => [
										'priority'    => 20,
										'title'       => __( 'Toggle Bar Background', 'megamenu' ),
										'description' => __( 'Set the background color for the mobile menu toggle bar.', 'megamenu' ),
										'info'        => [
											__( "Don't forget to update the Menu toggle block text and icon color in the Toggle Bar Designer above!", 'megamenu' ),
										],
										'settings'    => [
											[
												'title' => __( 'From', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'toggle_background_from',
											],
											[
												'title' => __( 'Copy', 'megamenu' ),
												'type'  => 'copy_color',
												'key'   => 'copy_color',
											],
											[
												'title' => __( 'To', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'toggle_background_to',
											],
										],
									],
									'toggle_bar_height'   => [
										'priority'    => 25,
										'title'       => __( 'Toggle Bar Height', 'megamenu' ),
										'description' => __( 'Set the height of the mobile menu toggle bar.', 'megamenu' ),
										'settings'    => [
											[
												'title' => '',
												'type'  => 'freetext',
												'key'   => 'toggle_bar_height',
											],
										],
									],
									'toggle_bar_border_radius' => [
										'priority'    => 26,
										'title'       => __( 'Toggle Bar Border Radius', 'megamenu' ),
										'description' => __( 'Set a border radius on the mobile toggle bar.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'toggle_bar_border_radius_top_left',
												'validation' => 'px',
											],
											[
												'title' => __( 'Top Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'toggle_bar_border_radius_top_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'toggle_bar_border_radius_bottom_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'toggle_bar_border_radius_bottom_left',
												'validation' => 'px',
											],
										],
									],
									'disable_mobile_toggle' => [
										'priority'    => 28,
										'title'       => __( 'Disable Mobile Toggle Bar', 'megamenu' ),
										'description' => __( "Hide the toggle bar and display the menu in it's open state by default.", 'megamenu' ),
										'settings'    => [
											[
												'title' => '',
												'type'  => 'checkbox',
												'key'   => 'disable_mobile_toggle',
											],
										],
									],
									'mobile_submenu_header' => [
										'priority'    => 33,
										'title'       => __( 'Mobile Sub Menu', 'megamenu' ),
										'description' => '',
									],
									'mobile_menu_overlay' => [
										'priority'    => 34,
										'title'       => __( 'Overlay Content', 'megamenu' ),
										'description' => __( 'If enabled, the mobile sub menu will overlay the page content (instead of pushing the page content down). This will only work if the menu/header is not sticky.', 'megamenu' ),
										'settings'    => [
											[
												'title' => '',
												'type'  => 'checkbox',
												'key'   => 'mobile_menu_overlay',
											],
										],
									],
									'mobile_menu_force_width' => [
										'priority'    => 35,
										'title'       => __( 'Force Full Width', 'megamenu' ),
										'description' => __( "If enabled, the mobile sub menu will match the width and position on the given page element (rather than being limited to the width of the toggle bar). For a full width sub menu, leave the 'Selector' value set to 'body'. This setting does not apply to Off Canvas menus (see below).", 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Enabled', 'megamenu' ),
												'type'  => 'checkbox',
												'key'   => 'mobile_menu_force_width',
											],
											[
												'title' => __( 'Selector', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'mobile_menu_force_width_selector',
											],
										],
									],
									'mobile_menu_item_height' => [
										'priority'    => 38,
										'title'       => __( 'Menu Item Height', 'megamenu' ),
										'description' => __( 'Height of each top level item in the mobile menu.', 'megamenu' ),
										'settings'    => [
											[
												'title' => '',
												'type'  => 'freetext',
												'key'   => 'mobile_menu_item_height',
											],
										],
									],
									'mobile_menu_padding' => [
										'priority'    => 39,
										'title'       => __( 'Menu Padding', 'megamenu' ),
										'description' => __( 'Padding for the mobile sub menu.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Top', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'mobile_menu_padding_top',
												'validation' => 'px',
											],
											[
												'title' => __( 'Right', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'mobile_menu_padding_right',
												'validation' => 'px',
											],
											[
												'title' => __( 'Bottom', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'mobile_menu_padding_bottom',
												'validation' => 'px',
											],
											[
												'title' => __( 'Left', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'mobile_menu_padding_left',
												'validation' => 'px',
											],
										],
									],
									'mobile_background'   => [
										'priority'    => 40,
										'title'       => __( 'Menu Background', 'megamenu' ),
										'description' => __( 'The background color for the mobile menu.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'From', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'mobile_background_from',
											],
											[
												'title' => __( 'Copy', 'megamenu' ),
												'type'  => 'copy_color',
												'key'   => 'copy_color',
											],
											[
												'title' => __( 'To', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'mobile_background_to',
											],
										],
									],
									'mobile_background_hover' => [
										'priority'    => 45,
										'title'       => __( 'Menu Item Background (Active)', 'megamenu' ),
										'description' => __( 'The background color for each top level menu item in the mobile menu when the sub menu is open.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'From', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'mobile_menu_item_background_hover_from',
											],
											[
												'title' => __( 'Copy', 'megamenu' ),
												'type'  => 'copy_color',
												'key'   => 'copy_color',
											],
											[
												'title' => __( 'To', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'mobile_menu_item_background_hover_to',
											],
										],
									],
									'mobile_menu_item_font' => [
										'priority'    => 50,
										'title'       => __( 'Font', 'megamenu' ),
										'description' => __( 'The font to use for each top level menu item in the mobile menu.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'mobile_menu_item_link_color',
											],
											[
												'title' => __( 'Size', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'mobile_menu_item_link_font_size',
												'validation' => 'px',
											],
											[
												'title' => __( 'Align', 'megamenu' ),
												'type'  => 'align',
												'key'   => 'mobile_menu_item_link_text_align',
											],
										],
									],
									'mobile_menu_item_font_hover' => [
										'priority'    => 55,
										'title'       => __( 'Font (Active)', 'megamenu' ),
										'description' => __( 'The font color for each top level menu item in the mobile menu when the sub menu is open.', 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'mobile_menu_item_link_color_hover',
											],
										],
									],
									'off_canvas_header' => [
										'priority'    => 60,
										'title'       => __( 'Off Canvas Settings', 'megamenu' ),
										'description' => '',
									],
									'mobile_menu_off_canvas_width' => [
										'priority'    => 65,
										'title'       => __( 'Off Canvas Width', 'megamenu' ),
										'description' => __( "The width of the sub menu if the Mobile Effect is set to 'Off Canvas'.", 'megamenu' ),
										'settings'    => [
											[
												'title' => '',
												'type'  => 'freetext',
												'key'   => 'mobile_menu_off_canvas_width',
												'validation' => 'px',
											],
										],
									],
									'mobile_menu_off_canvas_close_button' => [
										'priority'    => 70,
										'title'       => __( 'Close Button', 'megamenu' ),
										'description' => __( "Style of the 'close' button when the Mobile Menu option is set to 'Off Canvas'.", 'megamenu' ),
										'settings'    => [
											[
												'title' => __( 'Icon', 'megamenu' ),
												'type'  => 'close',
												'key'   => 'close_icon',
											],
											[
												'title' => __( 'Icon Size', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'close_icon_font_size',
												'validation' => 'px',
											],
											[
												'title' => __( 'Icon Color', 'megamenu' ),
												'type'  => 'color',
												'key'   => 'close_icon_color',
											],
											[
												'title' => __( 'Aria-label', 'megamenu' ),
												'type'  => 'freetext',
												'key'   => 'close_icon_label'
											],
										],
									],
									'mega_menus_header'   => [
										'priority'    => 75,
										'title'       => __( 'Mega Menus', 'megamenu' ),
										'description' => '',
									],
									'mobile_columns'      => [
										'priority'    => 80,
										'title'       => __( 'Mega Menu Columns', 'megamenu' ),
										'description' => __( 'Collapse mega menu content into this many columns on mobile.', 'megamenu' ),
										'settings'    => [
											[
												'title' => '',
												'type'  => 'mobile_columns',
												'key'   => 'mobile_columns',
											],
										],
									],
								],
							],
							'custom_styling' => [
								'title'    => __( 'Custom Styling', 'megamenu' ),
								'settings' => [
									'custom_styling' => [
										'priority'    => 40,
										'title'       => __( 'CSS Editor', 'megamenu' ),
										'description' => __( 'Define any custom CSS you wish to add to menus using this theme. You can use standard CSS or SCSS.', 'megamenu' ),
										'settings'    => [
											[
												'title' => '',
												'type'  => 'textarea',
												'key'   => 'custom_css',
											],
										],
									],
								],
							],
						]
					);

					

					if ( ! $this->should_show_flex_option() ) { // plugin version only started being stored in v3.6 onwards
						unset($settings['general']['settings']['use_flex_css']);
					}
	
					echo "<nav class='nav-tab-wrapper megamenu-nav-tab-wrapper' role='tablist'>";

					$is_first = true;

				foreach ( $settings as $section_id => $section ) {

					if ( $is_first ) {
						$active   = 'nav-tab-active ';
						$is_first = false;
					} else {
						$active = '';
					}

					$tab_id    = 'megamenu-theme-editor-tab-' . sanitize_key( (string) $section_id );
					$panel_id  = 'megamenu-theme-editor-panel-' . sanitize_key( (string) $section_id );

					$theme_tab_processor = new WP_HTML_Tag_Processor( '<button type="button">' . $section['title'] . '</button>' );
					if ( $theme_tab_processor->next_tag() ) {
						$theme_tab_processor->set_attribute( 'class', 'mega-tab ' . trim( $active ) );
						$theme_tab_processor->set_attribute( 'id', $tab_id );
						$theme_tab_processor->set_attribute( 'data-tab', 'mega-tab-content-' . $section_id );
						$theme_tab_processor->set_attribute( 'role', 'tab' );
						$theme_tab_processor->set_attribute( 'aria-controls', $panel_id );
						$theme_tab_processor->set_attribute( 'aria-selected', ! empty( trim( $active ) ) ? 'true' : 'false' );
					}
					echo $theme_tab_processor->get_updated_html();

				}
					echo '<span class="nav-tab-slider"></span>';
					echo '</nav>';

					$is_first = true;

				foreach ( $settings as $section_id => $section ) {

					if ( $is_first ) {
							$display  = 'block';
							$is_first = false;
					} else {
						$display = 'none';
					}

					$tab_id   = 'megamenu-theme-editor-tab-' . sanitize_key( (string) $section_id );
					$panel_id = 'megamenu-theme-editor-panel-' . sanitize_key( (string) $section_id );

						echo '<div class="mega-tab-content mega-tab-content-' . esc_attr( (string) $section_id ) . '" id="' . esc_attr( $panel_id ) . '" role="tabpanel" aria-labelledby="' . esc_attr( $tab_id ) . '" style="display: ' . esc_attr( $display ) . '">';
						echo "            <table class='{$section_id} mmm-settings-table'>";

						// order the fields by priority
						uasort( $section['settings'], [ $this, 'compare_elems' ] );

					foreach ( $section['settings'] as $group_id => $group ) {

						echo "<tr class='mega-{$group_id}'>";

						if ( isset( $group['settings'] ) ) {

							echo "<td class='mega-name'><div class='mega-name-title'>" . $group['title'] . "</div><div class='mega-description'>" . $group['description'] . '</div></td>';
							echo "<td class='mega-value'>";

							foreach ( $group['settings'] as $setting_id => $setting ) {

								$is_textarea        = ( 'textarea' === $setting['type'] );
								$textarea_dom_id    = $is_textarea ? 'megamenu-theme-textarea-' . sanitize_key( (string) $setting['key'] ) : '';
								$textarea_for_attr  = $is_textarea ? ' for="' . esc_attr( $textarea_dom_id ) . '"' : '';

								if ( isset( $setting['validation'] ) ) {
									echo "<label class='mega-{$setting['key']}' data-validation='{$setting['validation']}'{$textarea_for_attr}>";
								} else {
									echo "<label class='mega-{$setting['key']}'{$textarea_for_attr}>";
								}
								echo "<span class='mega-short-desc'>{$setting['title']}</span>";

								// Label wraps only the title so block-level textarea + help can use full cell width (not constrained by <label display>).
								if ( $is_textarea ) {
									echo '</label>';
								}

								switch ( $setting['type'] ) {
									case 'freetext':
										$this->print_theme_freetext_option( $setting['key'] );
										break;
									case 'textarea':
										$this->print_theme_textarea_option( $setting['key'] );
										break;
									case 'align':
										$this->print_theme_align_option( $setting['key'] );
										break;
									case 'checkbox':
										$this->print_theme_checkbox_option( $setting['key'] );
										break;
									case 'arrow':
										$this->print_theme_arrow_option( $setting['key'] );
										break;
									case 'close':
										$this->print_theme_close_option( $setting['key'] );
										break;
									case 'color':
										$this->print_theme_color_option( $setting['key'] );
										break;
									case 'weight':
										$this->print_theme_weight_option( $setting['key'] );
										break;
									case 'font':
										$this->print_theme_font_option( $setting['key'] );
										break;
									case 'transform':
										$this->print_theme_transform_option( $setting['key'] );
										break;
									case 'decoration':
										$this->print_theme_text_decoration_option( $setting['key'] );
										break;
									case 'mobile_columns':
										$this->print_theme_mobile_columns_option( $setting['key'] );
										break;
									case 'copy_color':
										$this->print_theme_copy_color_option( $setting['key'] );
										break;
									default:
										do_action( "megamenu_print_theme_option_{$setting['type']}", $setting['key'], $this->id );
										break;
								}

								if ( ! $is_textarea ) {
									echo '</label>';
								}

							}

							if ( isset( $group['info'] ) ) {
								foreach ( $group['info'] as $paragraph ) {
									echo "<div class='mega-info'>{$paragraph}</div>";
								}
							}

							foreach ( $group['settings'] as $setting_id => $setting ) {
								if ( isset( $setting['validation'] ) ) {

									echo "<div class='mega-validation-message mega-validation-message-mega-{$setting['key']}'>";

									if ( $setting['validation'] == 'int' ) {
										$message = __( 'Enter a whole number (e.g. 1, 5, 100, 999)' );
									}

									if ( $setting['validation'] == 'px' ) {
										$message = __( 'Enter a value including a unit (e.g. 10px, 10rem, 10%)' );
									}

									if ( $setting['validation'] == 'float' ) {
										$message = __( 'Enter a valid number (e.g. 0.1, 1, 10, 999)' );
									}

									if ( strlen( $setting['title'] ) ) {
										echo $setting['title'] . ': ' . $message;
									} else {
										echo $message;
									}

									echo '</div>';
								}
							}

							echo '</td>';
						} else {
							echo "<td colspan='2'><h5>{$group['title']}</h5></td>";
						}

						echo '</tr>';

					}

						echo '</table>';
						echo '</div>';
				}

				?>


				<div class='megamenu_submit'>
					<p class="submit">
						<button type="submit" name="submit" id="submit" class="button button-primary button-compact"><?php echo esc_html__( 'Save Changes', 'default' ); ?></button>
					</p>
				</div>

				<?php $this->show_cache_warning(); ?>
			</form>
		</div>

			<?php self::render_scss_variables_dialog_markup(); ?>

			<?php

		}

		/**
		 * Echo the SCSS variables modal as a text/html script template (mounted to body by js/admin/theme-editor.js).
		 *
		 * @return void
		 */
		public static function render_scss_variables_dialog_markup() {
			?>
			<script type="text/html" id="megamenu-scss-variables-dialog-template">
			<div id="megamenu-scss-variables-dialog" class="megamenu-admin-modal megamenu-scss-variables-dialog" hidden data-megamenu-expand-storage-key="megamenu_admin_modal_wpcontent_expanded" data-i18n-modal-expand="<?php echo esc_attr__( 'Expand to fill workspace', 'megamenu' ); ?>" data-i18n-modal-collapse="<?php echo esc_attr__( 'Restore default size', 'megamenu' ); ?>">
				<button type="button" class="megamenu-admin-modal__backdrop" aria-label="<?php esc_attr_e( 'Close', 'megamenu' ); ?>"></button>
				<div class="megamenu-admin-modal__panel" role="dialog" aria-modal="true" aria-labelledby="megamenu-scss-variables-dialog-title" tabindex="-1">
					<div class="megamenu-admin-modal__header">
						<div class="megamenu-admin-modal__title-group">
							<h2 id="megamenu-scss-variables-dialog-title" class="megamenu-admin-modal__title">
								<span class="megamenu-admin-modal__title-text"><?php esc_html_e( 'SCSS variables for this theme', 'megamenu' ); ?></span>
							</h2>
						</div>
						<div class="megamenu-admin-modal__header-actions">
							<button type="button" class="megamenu-admin-modal__expand-btn" aria-expanded="false" aria-label="<?php echo esc_attr__( 'Expand to fill workspace', 'megamenu' ); ?>">
								<span class="dashicons dashicons-fullscreen-alt megamenu-admin-modal__expand-icon megamenu-admin-modal__expand-icon--expand" aria-hidden="true"></span>
								<span class="dashicons dashicons-fullscreen-exit-alt megamenu-admin-modal__expand-icon megamenu-admin-modal__expand-icon--contract" aria-hidden="true"></span>
							</button>
							<button type="button" class="megamenu-modal-close" aria-label="<?php esc_attr_e( 'Close', 'megamenu' ); ?>">
								<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
							</button>
						</div>
					</div>
					<div class="megamenu-admin-modal__body megamenu-admin-modal__loading-host">
						<div class="megamenu-admin-modal__loading-overlay" role="status" aria-live="polite">
							<span class="megamenu-admin-modal__loading-spinner" aria-hidden="true"></span>
							<span class="screen-reader-text"><?php esc_html_e( 'Loading.', 'megamenu' ); ?></span>
						</div>
						<div id="megamenu-scss-variables-dialog-body" class="megamenu-admin-modal__body-slot megamenu-scss-variables-dialog__slot">
							<p class="megamenu-scss-variables-dialog__intro description">
								<?php esc_html_e( 'Values reflect the current settings in this form (including unsaved changes). Selectors such as wrap and menu use a preview location slug.', 'megamenu' ); ?>
							</p>
							<p class="megamenu-scss-variables-dialog__error notice notice-error" hidden></p>
							<dl id="megamenu-scss-variables-list" class="megamenu-scss-vars-list"></dl>
						</div>
					</div>
				</div>
			</div>
			</script>
			<?php
		}

		/**
		 * 
		 */
		public function should_show_flex_option() {

			$initial_version = get_option('megamenu_initial_version');

			// always show the option for new installations of 3.6 onwards
			if ( $initial_version && version_compare( $initial_version, '3.6', '>=' ) ) {
				return true;
			}

			if ( defined( 'MEGAMENU_ENABLE_FLEX_CSS_OPTION' ) && MEGAMENU_ENABLE_FLEX_CSS_OPTION ) {
				return true;
			}

			return false;
		}


		/**
		 * Check for installed caching/minification/CDN plugins and output a warning if one is found to be
		 * installed and activated
		 */
		public function show_cache_warning() {

			$active_plugins = max_mega_menu_get_active_caching_plugins();

			if ( count( $active_plugins ) ) :

				?>

		<div>

			<h3><?php _e( 'Changes not showing up?', 'megamenu' ); ?></h3>

			<p><?php echo _n( 'We have detected the following plugin that may prevent changes made within the theme editor from being applied to the menu.', 'We have detected the following plugins that may prevent changes made within the theme editor from being applied to the menu.', count( $active_plugins ), 'megamenu' ); ?></p>

			<ul class='ul-disc'>
				<?php
				foreach ( $active_plugins as $name ) {
					echo '<li>' . $name . '</li>';
				}
				?>
			</ul>

			<p><?php echo _n( 'Try clearing the cache of the above plugin if your changes are not being applied to the menu.', 'Try clearing the caches of the above plugins if your changes are not being applied to the menu.', count( $active_plugins ), 'megamenu' ); ?></p>

		</div>

				<?php

			endif;
		}


		/**
		 * Compare array values
		 *
		 * @param array $elem1
		 * @param array $elem2
		 * @return bool
		 * @since 2.1
		 */
		private function compare_elems( $elem1, $elem2 ) {
			if ( $elem1['priority'] > $elem2['priority'] ) {
				return 1;
			}

			if ( $elem1['priority'] == $elem2['priority'] ) {
				return 0;
			}

			return -1;
		}


		/**
		 * Print a select dropdown with left, center and right options
		 *
		 * @since 1.6.1
		 * @param string $key
		 * @param string $value
		 */
		public function print_theme_align_option( $key ) {

			$value = $this->active_theme[ $key ];

			$use_flex_css = $this->active_theme[ 'use_flex_css' ];
			?>

			<select name='settings[<?php echo $key; ?>]'>
				<option value='left' <?php selected( $value, 'left' ); ?>><?php _e( 'Left', 'megamenu' ); ?></option>
				<option value='center' <?php selected( $value, 'center' ); ?>><?php _e( 'Center', 'megamenu' ); ?></option>
				<option value='right' <?php selected( $value, 'right' ); ?>><?php _e( 'Right', 'megamenu' ); ?></option>

				<?php if ($key == 'menu_item_align' && $use_flex_css == 'on'): ?>
					<option value='space-around' <?php selected( $value, 'space-around' ); ?>><?php _e( 'space-around', 'megamenu' ); ?></option>
					<option value='space-between' <?php selected( $value, 'space-between' ); ?>><?php _e( 'space-between', 'megamenu' ); ?></option>
					<option value='space-evenly' <?php selected( $value, 'space-evenly' ); ?>><?php _e( 'space-evenly', 'megamenu' ); ?></option>
				<?php endif; ?>
			</select>

			<?php
		}


		/**
		 * Print a copy icon
		 *
		 * @since 2.2.3
		 * @param string $key
		 * @param string $value
		 */
		public function print_theme_copy_color_option( $key ) {

			?>

			<span class='dashicons dashicons-arrow-right-alt'></span>

			<?php
		}


		/**
		 * Print a select dropdown with 1 and 2 options
		 *
		 * @since 1.2.0
		 * @param string $key
		 * @param string $value
		 */
		public function print_theme_mobile_columns_option( $key ) {

			$value = $this->active_theme[ $key ];

			?>

			<select name='settings[<?php echo $key; ?>]'>
				<option value='1' <?php selected( $value, '1' ); ?>><?php _e( '1 Column', 'megamenu' ); ?></option>
				<option value='2' <?php selected( $value, '2' ); ?>><?php _e( '2 Columns', 'megamenu' ); ?></option>
			</select>

			<?php
		}


		/**
		 * Print a select dropdown with text decoration options
		 *
		 * @since 1.6.1
		 * @param string $key
		 * @param string $value
		 */
		public function print_theme_text_decoration_option( $key ) {

			$value = $this->active_theme[ $key ];

			?>

			<select name='settings[<?php echo $key; ?>]'>
				<option value='none' <?php selected( $value, 'none' ); ?>><?php _e( 'None', 'megamenu' ); ?></option>
				<option value='underline' <?php selected( $value, 'underline' ); ?>><?php _e( 'Underline', 'megamenu' ); ?></option>
			</select>

			<?php
		}


		/**
		 * Print a checkbox option
		 *
		 * @since 1.6.1
		 * @param string $key
		 * @param string $value
		 */
		public function print_theme_checkbox_option( $key ) {

			$value = $this->active_theme[ $key ];

			?>

			<input type='hidden' name='checkboxes[<?php echo $key; ?>]' />
			<input type='checkbox' name='settings[<?php echo $key; ?>]' <?php checked( $value, 'on' ); ?> />

			<?php
		}


		/**
		 * Print an arrow dropdown selection box
		 *
		 * @since 1.0
		 * @param string $key
		 * @param string $value
		 */
		public function print_theme_arrow_option( $key ) {

			$value = $this->active_theme[ $key ];

			$arrow_icons = $this->arrow_icons();

			?>
			<select class='icon_dropdown' name='settings[<?php echo $key; ?>]'>
				<?php

					echo "<option value='disabled'>" . __( 'Disabled', 'megamenu' ) . '</option>';

				foreach ( $arrow_icons as $code => $class ) {
					$name = str_replace( 'dashicons-', '', $class );
					$name = ucwords( str_replace( [ '-', 'arrow' ], ' ', $name ) );
					echo "<option data-class='{$class}' value='{$code}' " . selected( $value, $code, false ) . '>' . esc_html( $name ) . '</option>';
				}

				?>
			</select>

			<?php
		}


		/**
		 * Print an arrow dropdown selection box
		 *
		 * @since 1.0
		 * @param string $key
		 * @param string $value
		 */
		public function print_theme_close_option( $key ) {

			$value = $this->active_theme[ $key ];

			$arrow_icons = $this->close_icons();

			?>
			<select class='icon_dropdown' name='settings[<?php echo $key; ?>]'>
				<?php

					echo "<option value='disabled'>" . __( 'Disabled', 'megamenu' ) . '</option>';

				foreach ( $arrow_icons as $code => $class ) {
					$name = str_replace( 'dashicons-', '', $class );
					$name = ucwords( str_replace( [ '-', 'arrow' ], ' ', $name ) );
					echo "<option data-class='{$class}' value='{$code}' " . selected( $value, $code, false ) . '>' . esc_html( $name ) . '</option>';
				}

				?>
			</select>

			<?php
		}



		/**
		 * Print a colorpicker
		 *
		 * @since 1.0
		 * @param string $key
		 * @param string $value
		 */
		public function print_theme_color_option( $key ) {

			$value = $this->active_theme[ $key ];

			if ( $value == 'transparent' ) {
				$value = 'rgba(0,0,0,0)';
			}

			if ( $value == 'rgba(0,0,0,0)' ) {
				$value_text = 'transparent';
			} else {
				$value_text = $value;
			}

			echo "<input type='text' class='mega-color-picker-input' name='settings[$key]' value='" . esc_attr( $value ) . "' />";

		}


		/**
		 * Print a font weight selector
		 *
		 * @since 1.0
		 * @param string $key
		 * @param string $value
		 */
		public function print_theme_weight_option( $key ) {

			$value = $this->active_theme[ $key ];

			$options = apply_filters(
				'megamenu_font_weights',
				[
					'inherit' => __( 'Theme Default', 'megamenu' ),
					'300'     => __( 'Light (300)', 'megamenu' ),
					'normal'  => __( 'Normal (400)', 'megamenu' ),
					'bold'    => __( 'Bold (700)', 'megamenu' ),
				]
			);

			/**
			 *   '100' => __("Thin (100)", "megamenu"),
			 *   '200' => __("Extra Light (200)", "megamenu"),
			 *   '300' => __("Light (300)", "megamenu"),
			 *   'normal' => __("Normal (400)", "megamenu"),
			 *   '500' => __("Medium (500)", "megamenu"),
			 *   '600' => __("Semi Bold (600)", "megamenu"),
			 *   'bold' => __("Bold (700)", "megamenu"),
			 *   '800' => __("Extra Bold (800)", "megamenu"),
			 *   '900' => __("Black (900)", "megamenu")
			*/

			echo "<select name='settings[$key]'>";

			foreach ( $options as $weight => $name ) {
				echo "<option value='" . esc_attr( $weight ) . "' " . selected( $value, $weight, false ) . '>' . esc_html( $name ) . '</option>';
			}

			echo '</select>';

		}


		/**
		 * Print a font transform selector
		 *
		 * @since 1.0
		 * @param string $key
		 * @param string $value
		 */
		public function print_theme_transform_option( $key ) {

			$value = $this->active_theme[ $key ];

			echo "<select name='settings[$key]'>";
			echo "    <option value='none' " . selected( $value, 'none', false ) . '>' . __( 'Normal', 'megamenu' ) . '</option>';
			echo "    <option value='capitalize'" . selected( $value, 'capitalize', false ) . '>' . __( 'Capitalize', 'megamenu' ) . '</option>';
			echo "    <option value='uppercase'" . selected( $value, 'uppercase', false ) . '>' . __( 'UPPERCASE', 'megamenu' ) . '</option>';
			echo "    <option value='lowercase'" . selected( $value, 'lowercase', false ) . '>' . __( 'lowercase', 'megamenu' ) . '</option>';
			echo '</select>';

		}


		/**
		 * Print a textarea
		 *
		 * @since 1.0
		 * @param string $key
		 * @param string $value
		 */
		public function print_theme_textarea_option( $key ) {

			$value    = sanitize_textarea_field( $this->active_theme[ $key ] );
			$field_id = 'megamenu-theme-textarea-' . sanitize_key( (string) $key );

			?>

		<textarea id="<?php echo esc_attr( $field_id ); ?>" name="settings[<?php echo esc_attr( $key ); ?>]"><?php echo stripslashes( $value ); ?></textarea>

		<p><b><?php _e( 'Custom Styling Tips', 'megamenu' ); ?></b></p>
		<p><?php
			$scss_link      = $this->get_sass_guide_link_html();
			$variables_link = '<a href="#" id="megamenu-open-scss-variables" class="megamenu-open-scss-variables">' . esc_html__( 'variables', 'megamenu' ) . '</a>';
			echo wp_kses(
				sprintf(
					/* translators: 1: link to sass-lang.com guide (link text: SCSS), 2: link opening the SCSS variables dialog (link text: variables) */
					__( 'You can enter standard CSS or %1$s into the custom styling area. If using SCSS there are %2$s and mixins you can use:', 'megamenu' ),
					$scss_link,
					$variables_link
				),
				[
					'a' => [
						'href'   => true,
						'target' => true,
						'rel'    => true,
						'id'     => true,
						'class'  => true,
					],
				]
			);
		?></p>

		<ul class='custom_styling_tips'>
			<li><code>#{$wrap}</code> <?php _e( 'converts to the ID selector of the menu wrapper, e.g. div#mega-menu-wrap-primary', 'megamenu' ); ?></li>
			<li><code>#{$menu}</code> <?php _e( 'converts to the ID selector of the menu, e.g. ul#mega-menu-primary', 'megamenu' ); ?></li>
			<li><code>@include mobile|desktop { .. }</code> <?php _e( 'wraps the CSS within a media query based on the configured Responsive Breakpoint (see example CSS)', 'megamenu' ); ?></li>
			<?php
				$string = __( 'Using the %wrap% and %menu% variables makes your theme portable (allowing you to apply the same theme to multiple menu locations)', 'megamenu' );
				$string = str_replace( '%wrap%', '<code>#{$wrap}</code>', $string );
				$string = str_replace( '%menu%', '<code>#{$menu}</code>', $string );
			?>
			<li><?php echo $string; ?></li>
			<li><?php _e( 'Example CSS', 'megamenu' ); ?>:</li>
			<code>/** <?php _e( 'Add text shadow to top level menu items on desktop AND mobile', 'megamenu' ); ?> **/
				<br />#{$wrap} #{$menu} > li.mega-menu-item > a.mega-menu-link {
				<br />&nbsp;&nbsp;&nbsp;&nbsp;text-shadow: 1px 1px #000000;
				<br />}
			</code>
			<br /><br />
			<code>/** <?php _e( 'Add text shadow to top level menu items on desktop only', 'megamenu' ); ?> **/
				<br />@include desktop {
				<br />&nbsp;&nbsp;&nbsp;&nbsp;#{$wrap} #{$menu} > li.mega-menu-item > a.mega-menu-link {
				<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;text-shadow: 1px 1px #000000;
				<br />&nbsp;&nbsp;&nbsp;&nbsp;}
				<br />}
			</code></li>
		</ul>

			<?php

		}


		/**
		 * Print a font selector
		 *
		 * @since 1.0
		 * @param string $key
		 * @param string $value
		 */
		public function print_theme_font_option( $key ) {

			$value = $this->active_theme[ $key ];

			echo "<select name='settings[$key]'>";

			echo "<option value='inherit'>" . __( 'Theme Default', 'megamenu' ) . '</option>';

			foreach ( $this->fonts() as $font ) {
				$orig_font = $font;
				$font      = esc_attr( stripslashes( $font ) );
				$parts     = explode( ',', $font );
				$font_name = trim( $parts[0] );
				echo "<option value=\"{$font}\" " . selected( $orig_font, htmlspecialchars_decode( $value ) ) . '>' . esc_html( $font_name ) . '</option>';
			}

			echo '</select>';
		}


		/**
		 * Print a text input
		 *
		 * @since 1.0
		 * @param string $key
		 * @param string $value
		 */
		public function print_theme_freetext_option( $key ) {

			$value = $this->active_theme[ $key ];

			echo "<input class='mega-setting-{$key}' type='text' name='settings[$key]' value='" . esc_attr( $value ) . "' />";

		}


		/**
		 * Returns a list of available fonts.
		 *
		 * @since 1.0
		 */
		public function fonts() {

			$fonts = [
				'Georgia, serif',
				'Palatino Linotype, Book Antiqua, Palatino, serif',
				'Times New Roman, Times, serif',
				'Arial, Helvetica, sans-serif',
				'Arial Black, Gadget, sans-serif',
				'Comic Sans MS, cursive, sans-serif',
				'Impact, Charcoal, sans-serif',
				'Lucida Sans Unicode, Lucida Grande, sans-serif',
				'Tahoma, Geneva, sans-serif',
				'Trebuchet MS, Helvetica, sans-serif',
				'Verdana, Geneva, sans-serif',
				'Courier New, Courier, monospace',
				'Lucida Console, Monaco, monospace',
			];

			$fonts = apply_filters( 'megamenu_fonts', $fonts );

			return $fonts;

		}


		/**
		 * List of all available arrow DashIcon classes.
		 *
		 * @since 1.0
		 * @return array - Sorted list of icon classes
		 */
		private function arrow_icons() {

			$icons = [
				'dash-f142' => 'dashicons-arrow-up',
				'dash-f140' => 'dashicons-arrow-down',
				'dash-f141' => 'dashicons-arrow-left',
				'dash-f139' => 'dashicons-arrow-right',
				'dash-f342' => 'dashicons-arrow-up-alt',
				'dash-f346' => 'dashicons-arrow-down-alt',
				'dash-f340' => 'dashicons-arrow-left-alt',
				'dash-f344' => 'dashicons-arrow-right-alt',
				'dash-f343' => 'dashicons-arrow-up-alt2',
				'dash-f347' => 'dashicons-arrow-down-alt2',
				'dash-f341' => 'dashicons-arrow-left-alt2',
				'dash-f345' => 'dashicons-arrow-right-alt2',
				'dash-f132' => 'dashicons-plus',
				'dash-f460' => 'dashicons-minus',
				'dash-f158' => 'dashicons-no',
				'dash-f335' => 'dashicons-no-alt',

			];

			$icons = apply_filters( 'megamenu_arrow_icons', $icons );

			return $icons;

		}

		/**
		 * List of all available arrow DashIcon classes.
		 *
		 * @since 1.0
		 * @return array - Sorted list of icon classes
		 */
		private function close_icons() {

			$icons = [
				'dash-f171' => 'dashicons-undo',
				'dash-f518' => 'dashicons-controls-back',
				'dash-f14f' => 'dashicons-remove',
				'dash-f340' => 'dashicons-arrow-left-alt',
				'dash-f341' => 'dashicons-arrow-left-alt2',
				'dash-f460' => 'dashicons-minus',
				'dash-f158' => 'dashicons-no',
				'dash-f335' => 'dashicons-no-alt',
				'dash-f148' => 'dashicons-admin-collapse',
				'dash-f158' => 'dashicons-no',
				'dash-f335' => 'dashicons-no-alt',

			];

			$icons = apply_filters( 'megamenu_close_icons', $icons );

			return $icons;

		}

	}

endif;
