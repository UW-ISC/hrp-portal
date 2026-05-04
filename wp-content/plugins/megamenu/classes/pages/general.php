<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access.
}

if ( ! class_exists( 'Mega_Menu_General' ) ) :

	/**
	 * Handles the Mega Menu > General Settings admin page.
	 *
	 * @since   1.0
	 * @package MegaMenu
	 */
	class Mega_Menu_General {

		/**
		 * Constructor. Registers form submission and tab hooks.
		 *
		 * @since  1.0
		 * @return void
		 */
		public function __construct() {
			add_action( 'admin_post_megamenu_save_settings', [ $this, 'save_settings' ] );

			add_filter( 'megamenu_menu_tabs', [ $this, 'add_general_tab' ], 4 );
			add_action( 'megamenu_page_general_settings', [ $this, 'general_settings_page' ] );
		}


		/**
		 * Add the General Settings tab to the available admin tabs.
		 *
		 * @since  2.8
		 * @param  array $tabs Existing tabs.
		 * @return array Tabs with the General Settings tab appended.
		 */
		public function add_general_tab( $tabs ) {
			$tabs['general_settings'] = __( 'General Settings', 'megamenu' );
			return $tabs;
		}

		/**
		 * Recursively sanitize a multidimensional array.
		 *
		 * @since  2.7.5
		 * @param  array $array Array to sanitize (passed by reference).
		 * @return array Sanitized array.
		 */
		public function sanitize_array( &$array ) {
			foreach ( $array as &$value ) {
				if ( ! is_array( $value ) ) {
					$value = sanitize_textarea_field( $value );
				} else {
					$this->sanitize_array( $value );
				}
			}
			return $array;
		}


		/**
		 * Save menu general settings.
		 *
		 * @since  1.0
		 * @return void
		 */
		public function save_settings() {
			check_admin_referer( 'megamenu_save_settings' );

			if ( isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ) {
				$settings           = $this->sanitize_array( $_POST['settings'] );
				$submitted_settings = apply_filters( 'megamenu_submitted_settings', $settings );
				$existing_settings  = get_option( 'megamenu_settings' );
				$new_settings       = array_merge( (array) $existing_settings, $submitted_settings );

				update_option( 'megamenu_settings', $new_settings );
			}

			delete_option( 'megamenu_failed_to_write_css_to_filesystem' );

			do_action( 'megamenu_after_save_general_settings' );
			do_action( 'megamenu_delete_cache' );

			$url = isset( $_POST['_wp_http_referer'] ) ? $_POST['_wp_http_referer'] : admin_url( 'admin.php?page=maxmegamenu&saved=true' );

			$this->redirect( $url );
		}


		/**
		 * Redirect and exit.
		 *
		 * @since  1.8
		 * @param  string $url URL to redirect to.
		 * @return void
		 */
		public function redirect( $url ) {
			wp_redirect( $url );
			exit;
		}


		/**
		 * Content for the General Settings tab.
		 *
		 * @since  1.4
		 * @param  array $saved_settings Saved plugin settings.
		 * @return void
		 */
		public function general_settings_page( $saved_settings ) {

			$css = isset( $saved_settings['css'] ) ? $saved_settings['css'] : 'fs';

			$locations = get_registered_nav_menus();

			?>

		<div class='menu_settings menu_settings_general_settings'>

			<form action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="megamenu_save_settings" />
				<?php wp_nonce_field( 'megamenu_save_settings' ); ?>

				<h3 class='first'><?php esc_html_e( 'General Settings', 'megamenu' ); ?></h3>

				<table class="mmm-settings-table">
					<tr>
						<td class='mega-name'>
							<div class='mega-name-title'><?php esc_html_e( 'CSS Output', 'megamenu' ); ?></div>
							<div class='mega-description'>
							</div>
						</td>
						<td class='mega-value'>
							<select name='settings[css]' id='mega_css'>
								<option value='fs' <?php echo selected( 'fs' === $css ); ?>><?php esc_html_e( 'Save to filesystem', 'megamenu' ); ?>
									<?php
									if ( get_option( 'megamenu_failed_to_write_css_to_filesystem' ) ) {
										echo ' ' . esc_html( '(Action required: Check upload folder permissions)', 'megamenu' );
									}
									?>
								</option>
								<option value='head' <?php echo selected( 'head' === $css ); ?>><?php esc_html_e( 'Output in &lt;head&gt;', 'megamenu' ); ?></option>
								<option value='disabled' <?php echo selected( 'disabled' === $css ); ?>><?php esc_html_e( "Don't output CSS", 'megamenu' ); ?></option>
							<select>
							<div class='mega-description'>
								<div class='fs' style='display: <?php echo 'fs' === $css ? 'block' : 'none'; ?>'><?php esc_html_e( 'CSS will be saved to wp-content/uploads/maxmegamenu/style.css and enqueued from there.', 'megamenu' ); ?></div>
								<div class='head' style='display: <?php echo 'head' === $css ? 'block' : 'none'; ?>'><?php esc_html_e( 'CSS will be loaded from the cache in a &lt;style&gt; tag in the &lt;head&gt; of the page.', 'megamenu' ); ?></div>
								<div class='disabled' style='display: <?php echo 'disabled' === $css ? 'block' : 'none'; ?>'>
									<?php esc_html_e( 'CSS will not be output, you must enqueue the CSS for the menu manually.', 'megamenu' ); ?>
									<div class="notice notice-error is-dismissible">
										<p><?php esc_html_e( 'Selecting this option will effectively disable the theme editor and many of the features available in Max Mega Menu and Max Mega Menu Pro. Only enable this option if you fully understand the consequences.', 'megamenu' ); ?></p>
									</div>
								</div>
							</div>
						</td>
					</tr>
				</table>

					<?php do_action( 'megamenu_general_settings', $saved_settings ); ?>

					<?php echo get_submit_button( '', 'primary button-compact' ); ?>
			</form>
		</div>

			<?php
		}

	}

endif;
