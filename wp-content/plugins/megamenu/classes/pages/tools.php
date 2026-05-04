<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Tools' ) ) :

	/**
	 * Handles the Mega Menu > Tools admin page.
	 *
	 * @since   1.0
	 * @package MegaMenu
	 */
	class Mega_Menu_Tools {


		/**
		 * Constructor. Registers form submission and tab hooks.
		 *
		 * @since  1.0
		 * @return void
		 */
		public function __construct() {
			add_action( 'admin_post_megamenu_clear_css_cache', [ $this, 'tools_clear_css_cache' ] );
			add_action( 'admin_post_megamenu_delete_data', [ $this, 'delete_data' ] );

			add_filter( 'megamenu_menu_tabs', [ $this, 'add_tools_tab' ], 4 );
			add_action( 'megamenu_page_tools', [ $this, 'tools_page' ] );
		}

		/**
		 * Add the Tools tab to the available admin tabs.
		 *
		 * @since  2.8
		 * @param  array $tabs Existing tabs.
		 * @return array Tabs with the Tools tab appended.
		 */
		public function add_tools_tab( $tabs ) {
			$tabs['tools'] = __( 'Tools', 'megamenu' );
			return $tabs;
		}


		/**
		 * Clear the CSS cache.
		 *
		 * @since  1.5
		 * @return void
		 */
	public function tools_clear_css_cache() {
		check_admin_referer( 'megamenu_clear_css_cache' );

		if ( ! current_user_can( apply_filters( 'megamenu_options_capability', 'edit_theme_options' ) ) ) {
			wp_die( -1, 403 );
		}

		do_action( 'megamenu_delete_cache' );
			$this->redirect( admin_url( 'admin.php?page=maxmegamenu_tools&clear_css_cache=true' ) );
		}


		/**
		 * Deletes all Max Mega Menu data from the database.
		 *
		 * @since  1.5
		 * @return void
		 */
	public function delete_data() {

		check_admin_referer( 'megamenu_delete_data' );

		if ( ! current_user_can( apply_filters( 'megamenu_options_capability', 'edit_theme_options' ) ) ) {
			wp_die( -1, 403 );
		}

			do_action( 'megamenu_delete_cache' );

			// delete options
			delete_option( 'megamenu_settings' );
			delete_option( 'megamenu_locations' );
			delete_option( 'megamenu_toggle_blocks' );
			delete_option( 'megamenu_version' );
			delete_option( 'megamenu_initial_version' );
			delete_option( 'megamenu_themes_last_updated' );
			delete_option( 'megamenu_multisite_share_themes' );
			delete_option( 'megamenu_dismissed_notices' );
			delete_option( 'megamenu_install_date' );

			// delete all widgets assigned to menus
			$widget_manager = new Mega_Menu_Widget_Manager();

			if ( $mega_menu_widgets = $widget_manager->get_mega_menu_sidebar_widgets() ) {
				foreach ( $mega_menu_widgets as $widget_id ) {
					$widget_manager->delete_widget( $widget_id );
				}
			}

			// delete all mega menu metadata stored against menu items
			delete_metadata( 'post', 0, '_megamenu', '', true );

			// clear cache
			delete_transient( 'megamenu_css' );

			// delete custom themes
			max_mega_menu_delete_themes();

			$this->redirect( admin_url( 'admin.php?page=maxmegamenu_tools&delete_data=true' ) );
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
		 * Content for the Tools tab.
		 *
		 * @since  1.4
		 * @param  array $saved_settings Saved plugin settings.
		 * @return void
		 */
		public function tools_page( $saved_settings ) {
			$this->print_messages();

			if ( isset( $_GET['megamenu_confirm_delete_data'] ) && '1' === $_GET['megamenu_confirm_delete_data'] ) {
				if ( ! current_user_can( apply_filters( 'megamenu_options_capability', 'edit_theme_options' ) ) ) {
					wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'megamenu' ), '', [ 'response' => 403 ] );
				}
				$this->delete_data_confirmation_page();
				return;
			}

			$delete_data_confirm_url = add_query_arg(
				[
					'page'                         => 'maxmegamenu_tools',
					'megamenu_confirm_delete_data' => '1',
				],
				admin_url( 'admin.php' )
			);

			?>

		<div class='menu_settings menu_settings_tools'>
			<h3 class='first'><?php esc_html_e( 'Tools', 'megamenu' ); ?></h3>
			<table class="mmm-settings-table">
				<tr>
					<td class='mega-name'>
						<div class='mega-name-title'><?php esc_html_e( 'Cache', 'megamenu' ); ?></div>
						<div class='mega-description'><?php esc_html_e( 'The CSS for your menu is updated each time a menu or a menu theme is changed. You can force the menu CSS to be updated using this tool.', 'megamenu' ); ?></div>
					</td>
					<td class='mega-value'>
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
						<?php wp_nonce_field( 'megamenu_clear_css_cache' ); ?>
						<input type="hidden" name="action" value="megamenu_clear_css_cache" />

						<?php echo get_submit_button( __( 'Clear CSS Cache', 'megamenu' ), 'primary button-compact', 'submit', true ); ?>

							<?php if ( $date = get_option( 'megamenu_css_last_updated' ) ) : ?>
								<p><em><small><?php echo sprintf( __( 'The menu CSS was last updated on %s', 'megamenu' ), date( 'l jS F Y H:i:s', $date ) ); ?><small><em></p>
							<?php endif; ?>
						</form>
					</td>
				</tr>
				<tr>
					<td class='mega-name'>
						<div class='mega-name-title'><?php esc_html_e( 'Plugin Data', 'megamenu' ); ?></div>
						<div class='mega-description'><?php esc_html_e( 'Delete all saved Max Mega Menu plugin data from the database. Use with caution!', 'megamenu' ); ?></div>
					</td>
					<td class='mega-value'>
						<p>
							<a class="button button-secondary button-compact" href="<?php echo esc_url( $delete_data_confirm_url ); ?>"><?php esc_html_e( 'Delete Data', 'megamenu' ); ?></a>
						</p>
					</td>
				</tr>
			</table>
		</div>

			<?php
		}


		/**
		 * Second-step confirmation before deleting all plugin data (irreversible).
		 *
		 * @since 3.9.0
		 * @return void
		 */
		private function delete_data_confirmation_page() {
			$tools_url = admin_url( 'admin.php?page=maxmegamenu_tools' );
			?>

		<div class="menu_settings menu_settings_tools megamenu-delete-data-confirm">
			<h3 class="first"><?php esc_html_e( 'Delete all Max Mega Menu data', 'megamenu' ); ?></h3>

			<div class="notice notice-error inline">
				<p><strong><?php esc_html_e( 'This action is permanent. There is no way to undo it or restore your data from the plugin.', 'megamenu' ); ?></strong></p>
				<p><?php esc_html_e( 'All menu location settings, custom menu themes, mega menu widgets, menu item meta, cached CSS, and other data stored by Max Mega Menu will be removed. Your WordPress menus themselves are not deleted, but they will no longer use Mega Menu configuration until you set the plugin up again.', 'megamenu' ); ?></p>
			</div>

			<p class="megamenu-delete-data-confirm-actions">
				<a class="button button-primary button-compact" href="<?php echo esc_url( $tools_url ); ?>"><?php esc_html_e( 'Cancel', 'megamenu' ); ?></a>
			</p>

			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="megamenu-delete-data-final-form">
				<?php wp_nonce_field( 'megamenu_delete_data' ); ?>
				<input type="hidden" name="action" value="megamenu_delete_data" />
				<p>
					<button type="submit" class="button button-compact button-link-delete"><?php esc_html_e( 'Delete all data permanently', 'megamenu' ); ?></button>
				</p>
			</form>
		</div>

			<?php
		}


		/**
		 * Display messages to the user.
		 *
		 * @since  1.0
		 * @return void
		 */
		public function print_messages() {
			if ( isset( $_GET['clear_css_cache'] ) && 'true' === $_GET['clear_css_cache'] ) {
				?>
				<div class="notice notice-success is-dismissible"> 
					<p><?php esc_html_e( 'The cache has been cleared and the menu CSS has been regenerated.', 'megamenu' ) ?></p>

					<?php
						$theme_class = new Mega_Menu_Themes();

						$theme_class->show_cache_warning();
					?>
				</div>
				<?php
			}

		if ( isset( $_GET['delete_data'] ) && 'true' === $_GET['delete_data'] ) {
			?>
			<div class="notice notice-success is-dismissible"> 
				<p><?php esc_html_e( 'All plugin data removed', 'megamenu' ) ?></p>
			</div>
				<?php
			}
		}
	}

endif;
