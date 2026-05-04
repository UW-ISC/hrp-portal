<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Page' ) ) :

	/**
	 * Handles the Max Mega Menu admin page shell, including the menu structure and shared header.
	 *
	 * @since   1.0
	 * @package MegaMenu
	 */
	class Mega_Menu_Page {

		/**
		 * Constructor. Registers admin menu and script enqueueing hooks.
		 *
		 * @since  1.0
		 * @return void
		 */
		public function __construct() {
			add_action( 'admin_menu', [ $this, 'megamenu_settings_page' ] );
			add_action( 'megamenu_admin_scripts', [ $this, 'enqueue_scripts' ] );
		}


		/**
		 * “Not installed” link for the Pro extension row in the header version line.
		 *
		 * @since 3.9.0
		 * @return string
		 */
		private function get_pro_extension_not_installed_link_html() {
			$processor = new WP_HTML_Tag_Processor( '<a>not installed</a>' );
			if ( $processor->next_tag( 'a' ) ) {
				$processor->set_attribute( 'href', 'https://www.megamenu.com/upgrade/?utm_source=free&utm_medium=settings&utm_campaign=pro' );
				$processor->set_attribute( 'target', '_mmmpro' );
			}
			return $processor->get_updated_html();
		}


		/**
		 * SVG plugin icon as a data URL (same asset as the top-level admin menu item).
		 *
		 * @since 3.9.0
		 * @return string
		 */
		private function get_admin_menu_icon_data_url() {
			$svg = 'PHN2ZyB2ZXJzaW9uPSIxLjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEyNy4wMDAwMDBwdCIgaGVpZ2h0PSIxMjcuMDAwMDAwcHQiIHZpZXdCb3g9IjAgMCAxMjcuMDAwMDAwIDEyNy4wMDAwMDAiIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIG1lZXQiPgogICAgICAgICAgICAgICAgICAgIDxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAuMDAwMDAwLDEyNy4wMDAwMDApIHNjYWxlKDAuMTAwMDAwLC0wLjEwMDAwMCkiIGZpbGw9IiMwMDAwMDAiIHN0cm9rZT0ibm9uZSI+CiAgICAgICAgICAgICAgICAgICAgICAgIDxwYXRoIGQ9Ik0zMzAgMTEyNyBsLTI0NSAtMTQzIC03IC0xODAgYy01IC05OCAtNyAtMjUzIC01IC0zNDQgbDIgLTE2NSAxMzAKICAgICAgICAgICAgICAgICAgICAgICAgLTc2IGMyOTUgLTE3MyAzNDUgLTIwNCAzNDUgLTIxMSAwIC00IDI0IC04IDU0IC04IDQ4IDAgNjUgNyAxNjcgNjYgMjIzIDEyOQogICAgICAgICAgICAgICAgICAgICAgICAzNzYgMjI0IDM5MCAyNDAgMTggMjEgMjYgNTkzIDEwIDYzNyAtMTIgMzAgLTczIDcyIC0yNzYgMTkwIC03MSA0MiAtMTUyIDkwCiAgICAgICAgICAgICAgICAgICAgICAgIC0xNzkgMTA2IC0zNiAyMyAtNjAgMzEgLTk1IDMxIC00MSAwIC03MiAtMTYgLTI5MSAtMTQzeiBtNDEwIC03NyBjMTMxIC03NgogICAgICAgICAgICAgICAgICAgICAgICAxNDEgLTg1IDExNSAtMTA1IC00MyAtMzEgLTIyMSAtMTI1IC0yMzkgLTEyNSAtMjEgMCAtMjE3IDExMiAtMjM1IDEzNCAtOCAxMAogICAgICAgICAgICAgICAgICAgICAgICAtNiAxNyA3IDI4IDM3IDMyIDIwNyAxMjggMjI2IDEyOCAxMiAwIDY4IC0yNyAxMjYgLTYweiBtLTM2MSAtMjc5IGM4OCAtNTAKICAgICAgICAgICAgICAgICAgICAgICAgMTgxIC05OSAyMDcgLTExMCBsNDcgLTIxIDEyMSA2OSBjMTY4IDk2IDI1NSAxNDEgMjcyIDE0MSAxMiAwIDE0IC0zOCAxNCAtMjI4CiAgICAgICAgICAgICAgICAgICAgICAgIGwwIC0yMjggLTc3IC00NyAtNzggLTQ3IC03IDE0NiBjLTMgODAgLTggMTQ3IC0xMCAxNDkgLTIgMiAtNTMgLTI1IC0xMTMgLTYwCiAgICAgICAgICAgICAgICAgICAgICAgIC02MSAtMzUgLTExOSAtNjQgLTEyOSAtNjUgLTExIDAgLTcwIDI3IC0xMzIgNjAgLTYyIDMzIC0xMTUgNjAgLTExNyA2MCAtMyAwCiAgICAgICAgICAgICAgICAgICAgICAgIC04IC02MyAtMTIgLTE0MCAtNCAtNzcgLTExIC0xNDAgLTE2IC0xNDAgLTQgMCAtMzkgMTkgLTc4IDQyIGwtNzAgNDIgLTMgMTI2CiAgICAgICAgICAgICAgICAgICAgICAgIGMtNCAxODIgMSAzNDAgMTIgMzQwIDUgMCA4MSAtNDAgMTY5IC04OXogbTE5NSAtNDU4IGw1NSAtMjcgNDEgMjggYzIzIDE1IDQ4CiAgICAgICAgICAgICAgICAgICAgICAgIDI1IDU2IDIyIDE1IC02IDIwIC03OSA4IC0xMTEgLTcgLTE4IC05NCAtNjUgLTEyMSAtNjUgLTIyIDAgLTgzIDM1IC0xMDAgNTgKICAgICAgICAgICAgICAgICAgICAgICAgLTE4IDIyIC0xNSAxMjIgMyAxMjIgMiAwIDI4IC0xMiA1OCAtMjd6Ii8+CiAgICAgICAgICAgICAgICAgICAgPC9nPgogICAgICAgICAgICAgICAgPC9zdmc+';

			return 'data:image/svg+xml;base64,' . $svg;
		}


		/**
		 * Adds the "Menu Themes" menu item and page.
		 *
		 * @since  1.0
		 * @return void
		 */
		public function megamenu_settings_page() {

			$icon = $this->get_admin_menu_icon_data_url();

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			$page = add_menu_page( __( 'Max Mega Menu', 'megamenu' ), __( 'Mega Menu', 'megamenu' ), $capability, 'maxmegamenu', [ $this, 'page' ], $icon );

			$tabs = apply_filters( 'megamenu_menu_tabs', [] );

			foreach ( $tabs as $key => $title ) {
				if ( $key == 'menu_locations' ) {
					add_submenu_page( 'maxmegamenu', __( 'Max Mega Menu', 'megamenu' ) . ' - ' . $title, $title, $capability, 'maxmegamenu', [ $this, 'page' ] );
				} else {
					add_submenu_page( 'maxmegamenu', __( 'Max Mega Menu', 'megamenu' ) . ' - ' . $title, $title, $capability, 'maxmegamenu_' . $key, [ $this, 'page' ] );
				}
			}

		}



		/**
		 * Main settings page wrapper.
		 *
		 * @since  1.4
		 * @return void
		 */
		public function page() {

			$tab = isset( $_GET['page'] ) ? substr( $_GET['page'], 12 ) : false;

		// backwards compatibility
		if ( isset( $_GET['tab'] ) ) {
			$tab = sanitize_key( $_GET['tab'] );
		}

			if ( ! $tab ) {
				$tab = 'menu_locations';
			}

			$header_links = apply_filters(
				'megamenu_header_links',
				[
					'homepage'        => [
						'url'    => 'https://www.megamenu.com/?utm_source=free&amp;utm_medium=settings&amp;utm_campaign=pro',
						'target' => '_mmmpro',
						'text'   => __( 'Homepage', 'megamenu' ),
						'class'  => '',
					],
					'documentation'   => [
						'url'    => 'https://www.megamenu.com/documentation/installation/?utm_source=free&amp;utm_medium=settings&amp;utm_campaign=pro',
						'text'   => __( 'Documentation', 'megamenu' ),
						'target' => '_mmmpro',
						'class'  => '',
					],
					'troubleshooting' => [
						'url'    => 'https://www.megamenu.com/articles/troubleshooting/?utm_source=free&amp;utm_medium=settings&amp;utm_campaign=pro',
						'text'   => __( 'Troubleshooting', 'megamenu' ),
						'target' => '_mmmpro',
						'class'  => '',
					],
				]
			);

			if ( ! is_plugin_active( 'megamenu-pro/megamenu-pro.php' ) ) {
				$header_links['pro'] = [
					'url'    => 'https://www.megamenu.com/upgrade/?utm_source=free&amp;utm_medium=settings&amp;utm_campaign=pro',
					'target' => '_mmmpro',
					'text'   => __( 'Upgrade to Pro', 'megamenu' ),
					'class'  => 'mega-highlight',
				];
			}

			$versions = apply_filters(
				'megamenu_versions',
				[
					'core' => [
						'version' => MEGAMENU_VERSION,
						'text'    => __( 'Core version', 'megamenu' ),
					],
					'pro'  => [
						'version' => $this->get_pro_extension_not_installed_link_html(),
						'text'    => __( 'Pro extension', 'megamenu' ),
					],
				]
			);

			?>

		<div class='megamenu_outer_wrap'>
			<div class='megamenu_header'>
				<div class='megamenu_header_left'>
					<div class='megamenu_header_title_row'>
						<img
							class='megamenu_header_icon'
							src='<?php echo esc_attr( $this->get_admin_menu_icon_data_url() ); ?>'
							width='36'
							height='36'
							alt=''
							decoding='async'
						/>
						<h2><?php esc_html_e( 'Max Mega Menu', 'megamenu' ); ?></h2>
					</div>
					<div class='version'>
						<?php

							$total     = count( $versions );
							$count     = 0;
							$separator = ' - ';

					$allowed_version_html = [ 'a' => [ 'href' => [], 'target' => [] ] ];
					foreach ( $versions as $id => $data ) {
						echo esc_html( $data['text'] ) . ': <b>' . wp_kses( $data['version'], $allowed_version_html ) . '</b>';

							$count = $count + 1;

							if ( $total > 0 && $count != $total ) {
								echo $separator;
							}
						}
						?>
					</div>
				</div>
				<nav class='megamenu_header_right' aria-label="<?php esc_attr_e( 'Max Mega Menu links', 'megamenu' ); ?>">
					<ul>
					<?php
					foreach ( $header_links as $id => $data ) {
						$header_link_processor = new WP_HTML_Tag_Processor( '<a>' . esc_html( $data['text'] ) . '</a>' );
						if ( $header_link_processor->next_tag( 'a' ) ) {
							$header_link_processor->set_attribute( 'href', $data['url'] );
							$header_link_processor->set_attribute( 'target', $data['target'] );
						}
						printf(
							'<li class="%s">%s</li>',
							esc_attr( $data['class'] ),
							$header_link_processor->get_updated_html()
						);
					}
					?>
					</ul>
				</nav>

			<?php
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && isset( $_GET['debug'] ) && current_user_can( 'manage_options' ) ) {
					echo '<textarea style="width: 100%; height: 400px;">';
					echo esc_html( var_export( get_option( 'megamenu_settings' ), true ) );
					echo '</textarea>';
				}
			?>
			</div>

			<div class='megamenu_wrap megamenu-dialog-rail'>
					<div class='megamenu-dialog-tablist mega-tablist'>
						<nav class='mega-page-navigation' aria-label="<?php esc_attr_e( 'Max Mega Menu settings sections', 'megamenu' ); ?>">
						<ul>
							<?php
								$tabs = apply_filters( 'megamenu_menu_tabs', [] );

							foreach ( $tabs as $key => $title ) {
								$class = $tab === $key ? 'is-active' : '';

								if ( $key == 'menu_locations' ) {
									$args = [ 'page' => 'maxmegamenu' ];
								} else {
									$args = [ 'page' => 'maxmegamenu_' . $key ];
								}

								$url = esc_url( add_query_arg( $args, admin_url( 'admin.php' ) ) );

								$tab_link_processor = new WP_HTML_Tag_Processor( '<a>' . esc_html( $title ) . '</a>' );
								if ( $tab_link_processor->next_tag( 'a' ) ) {
									$tab_link_processor->set_attribute( 'href', $url );
									if ( $class ) {
										$tab_link_processor->set_attribute( 'class', $class );
									}
								}
								printf(
									'<li class="%s">%s</li>',
									esc_attr( $key ),
									$tab_link_processor->get_updated_html()
								);
							}
							?>
						</ul>
						</nav>
					</div>
					<div class='megamenu-dialog-panels'>
							<?php $this->print_messages(); ?>

							<?php

							$saved_settings = get_option( 'megamenu_settings' );

							if ( has_action( "megamenu_page_{$tab}" ) ) {
								do_action( "megamenu_page_{$tab}", $saved_settings );
							} else {
								do_action( 'megamenu_page_menu_locations', $saved_settings );
							}

							?>
					</div>
			</div>



		</div>

			<?php
		}


		/**
		 * Redirect and exit.
		 *
		 * @since  1.8
		 * @param  string $url URL to redirect to.
		 * @return void
		 */
	public function redirect( $url ) {
		wp_safe_redirect( $url );
		exit;
	}


		/**
		 * Display messages to the user.
		 *
		 * @since  1.0
		 * @return void
		 */
		public function print_messages() {

			if ( is_plugin_active( 'clearfy/clearfy.php' ) ) {
				if ( $clearfy_options = get_option( 'wbcr_clearfy_cache_options' ) ) {
					if ( $clearfy_options['disable_dashicons'] == true ) {
						printf(
							'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
							esc_html( __( 'Please enable Dashicons in the Clearfy plugin options. Max Mega Menu requires Dashicons.', 'megamenu' ) )
						);
					}
				}
			}

			do_action( 'megamenu_print_messages' );

		}


		/**
		 * Enqueue admin scripts.
		 *
		 * @since  1.8.3
		 * @param  string $hook The current admin page hook suffix.
		 * @return void
		 */
		public function enqueue_scripts( $hook ) {

			wp_deregister_style( 'select2' );
			wp_deregister_script( 'select2' );
			
			wp_enqueue_style( 'select2', MEGAMENU_BASE_URL . 'js/select2/select2.css', false, MEGAMENU_VERSION );
			wp_enqueue_script( 'mega-menu-select2', MEGAMENU_BASE_URL . 'js/select2/select2.js', [], MEGAMENU_VERSION );

			wp_enqueue_style( 'mega-menu-settings', MEGAMENU_BASE_URL . 'css/admin/admin.css', array( 'wp-components' ), MEGAMENU_VERSION );

			wp_enqueue_style( 'mega-colorpicker', MEGAMENU_BASE_URL . 'js/colorpicker/colorpicker.css', false, MEGAMENU_VERSION );
			wp_enqueue_script( 'mega-colorpicker', MEGAMENU_BASE_URL . 'js/colorpicker/colorpicker.js', [ 'jquery' ], MEGAMENU_VERSION );


			wp_localize_script(
				'spectrum',
				'megamenu_spectrum_settings',
				apply_filters( 'megamenu_spectrum_localisation', [] )
			);

			wp_enqueue_script( 'dialog-modal-expand', MEGAMENU_BASE_URL . 'js/admin/dialog-modal-expand.js', [ 'jquery' ], MEGAMENU_VERSION, true );
			wp_enqueue_script( 'dialog-preview', MEGAMENU_BASE_URL . 'js/admin/dialog-preview.js', [ 'jquery', 'dialog-modal-expand' ], MEGAMENU_VERSION, true );
			wp_enqueue_script(
				'mega-menu-theme-editor',
				MEGAMENU_BASE_URL . 'js/admin/theme-editor.js',
				[
					'jquery',
					'jquery-ui-sortable',
					'mega-menu-select2',
					'mega-colorpicker',
					'code-editor',
					'dialog-preview',
				],
				MEGAMENU_VERSION
			);

			wp_localize_script(
				'mega-menu-theme-editor',
				'megamenu_settings',
				[
					'edit_nonce'                        => wp_create_nonce( 'megamenu_edit' ),
					'saving'                            => __( 'Saving', 'megamenu' ),
					'confirm_destructive_action'        => __( 'Are you sure?', 'megamenu' ),
					'confirm'                           => __( 'Are you sure?', 'megamenu' ),
					'theme_save_error'                  => __( 'Error saving theme.', 'megamenu' ),
					'theme_save_error_refresh'          => __( 'Please try refreshing the page.', 'megamenu' ),
					'theme_save_error_exhausted'        => __( 'The server ran out of memory whilst trying to regenerate the menu CSS.', 'megamenu' ),
					'theme_save_error_memory_limit'     => __( 'Try disabling unusued plugins to increase the available memory. Alternatively, for details on how to increase your server memory limit see:', 'megamenu' ),
					'theme_save_error_500'              => __( 'The server returned a 500 error. The server did not provide an error message (you should find details of the error in your server error log), but this is usually due to your server memory limit being reached.', 'megamenu' ),
					'increase_memory_limit_url'         => 'http://www.wpbeginner.com/wp-tutorials/fix-wordpress-memory-exhausted-error-increase-php-memory/',
					'increase_memory_limit_anchor_text' => 'How to increase the WordPress memory limit',
					'scss_vars_error'                   => __( 'Could not load SCSS variables.', 'megamenu' ),
				]
			);

			if ( function_exists( 'wp_enqueue_code_editor' ) ) {
				wp_deregister_style( 'codemirror' );
				wp_deregister_script( 'codemirror' );

				$cm_settings['codeEditor'] = wp_enqueue_code_editor( [ 'type' => 'text/x-scss' ] );
				wp_localize_script( 'mega-menu-theme-editor', 'cm_settings', $cm_settings );
				wp_enqueue_style( 'wp-codemirror' );
			}
		}

	}

endif;
