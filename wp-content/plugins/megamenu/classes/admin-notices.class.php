<?php
/**
 * Credit to PolyLang (https://polylang.pro)
 * https://plugins.trac.wordpress.org/browser/polylang/trunk/admin/admin-notices.php
 */

/**
 * A class to manage admin notices, displayed only to admins (based on
 * 'manage_options' capability) and only on dashboard, plugins, and
 * Max Mega Menu admin pages.
 *
 * @since   3.0
 * @package MegaMenu
 */
class Mega_Menu_Admin_Notices {
	/**
	 * Stores the plugin options.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Stores custom notices.
	 *
	 * @var string[]
	 */
	private static $notices = [];

	/**
	 * Constructor. Sets up actions for hiding and displaying notices.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'hide_notice' ] );
		add_action( 'admin_notices', [ $this, 'display_notices' ] );
	}

	/**
	 * Add a custom notice.
	 *
	 * @since 3.0
	 * @param string $name Notice name.
	 * @param string $html HTML content of the notice.
	 * @return void
	 */
	public static function add_notice( $name, $html ) {
		self::$notices[ $name ] = $html;
	}

	/**
	 * Get all registered custom notices.
	 *
	 * @since 3.0
	 * @return string[] Map of notice name to HTML content.
	 */
	public static function get_notices() {
		return self::$notices;
	}

	/**
	 * Has a notice been dismissed?
	 *
	 * @since 3.0
	 * @param string $notice Notice name.
	 * @return bool True if the notice has been dismissed, false otherwise.
	 */
	public static function is_dismissed( $notice ) {
		$dismissed = get_option( 'megamenu_dismissed_notices', [] );

		return in_array( $notice, $dismissed );
	}

	/**
	 * Should we display notices on this screen?
	 *
	 * @since 3.0
	 * @param  string $notice The notice name.
	 * @return bool True if the notice should be displayed on the current screen.
	 */
	protected function can_display_notice( $notice ) {
		$screen = get_current_screen();

		if ( empty( $screen ) ) {
			return false;
		}

		/**
		 * Filter admin notices which can be displayed
		 *
		 * @since 2.7.0
		 *
		 * @param bool   $display Whether the notice should be displayed or not.
		 * @param string $notice  The notice name.
		 */
		return apply_filters(
			'mmm_can_display_notice',
			in_array(
				$screen->id,
				[
					'dashboard',
					'plugins',
					'toplevel_page_maxmegamenu'
				]
			),
			$notice
		);
	}

	/**
	 * Stores a dismissed notice in the database.
	 *
	 * @since 3.0
	 * @param string $notice Notice name.
	 * @return void
	 */
	public static function dismiss( $notice ) {
		$dismissed = get_option( 'megamenu_dismissed_notices', [] );

		if ( ! in_array( $notice, $dismissed ) ) {
			$dismissed[] = $notice;
			update_option( 'megamenu_dismissed_notices', array_unique( $dismissed ) );
		}
	}

	/**
	 * Handle a click on the dismiss button.
	 *
	 * @since 3.0
	 * @return void
	 */
	public function hide_notice() {
		if ( isset( $_GET['mmm-hide-notice'], $_GET['_mmm_notice_nonce'] ) ) {
			$notice = sanitize_key( $_GET['mmm-hide-notice'] );
			check_admin_referer( $notice, '_mmm_notice_nonce' );
			self::dismiss( $notice );
			wp_safe_redirect( remove_query_arg( [ 'mmm-hide-notice', '_mmm_notice_nonce' ], wp_get_referer() ) );
			exit;
		}
	}

	/**
	 * Displays notices.
	 *
	 * @since 2.3.9
	 * @return void
	 */
	public function display_notices() {

		if ( ! $this->can_display_notice( 'review' ) ) {
			return;
		}

		if ( defined( 'MEGAMENU_PRO_VERSION' ) ) {
			return;
		}

		if ( $this->is_dismissed( 'review' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$install_date = get_option( 'megamenu_install_date' );

		if ( ! $install_date ) {
			return;
		}

		if ( time() > $install_date + ( 14 * DAY_IN_SECONDS ) ) {
			$this->review_notice();
		}
	}

	/**
	 * Displays a dismiss button.
	 *
	 * @since 3.0
	 * @param string $name Notice name.
	 * @return void
	 */
	public function dismiss_button( $name ) {
		$dismiss_href = esc_url( wp_nonce_url( add_query_arg( 'mmm-hide-notice', $name ), $name, '_mmm_notice_nonce' ) );
		$processor    = new WP_HTML_Tag_Processor( '<a><span class="screen-reader-text">' . esc_html__( 'Dismiss this notice.', 'megamenu' ) . '</span></a>' );
		if ( $processor->next_tag( 'a' ) ) {
			$processor->set_attribute( 'style', 'text-decoration: none;' );
			$processor->set_attribute( 'class', 'notice-dismiss' );
			$processor->set_attribute( 'href', $dismiss_href );
		}
		echo $processor->get_updated_html();
	}

	/**
	 * Displays a notice asking for a review.
	 *
	 * @since 3.0
	 * @return void
	 */
	private function review_notice() {
		?>
		<div class="mmm-notice notice notice-info" style="position: relative; margin-left: 0;">
		<?php $this->dismiss_button( 'review' ); ?>
			<p>
				<?php
				$review_processor = new WP_HTML_Tag_Processor( '<a>' . esc_html__( 'give us a 5 stars rating', 'megamenu' ) . '</a>' );
				if ( $review_processor->next_tag( 'a' ) ) {
					$review_processor->set_attribute( 'href', 'https://wordpress.org/support/plugin/megamenu/reviews/?rate=5#new-post' );
					$review_processor->set_attribute( 'target', '_blank' );
					$review_processor->set_attribute( 'rel', 'noopener noreferrer' );
				}
				printf(
					/* translators: %s: link to the plugin review form on WordPress.org */
					esc_html__( 'We have noticed that you have been using Max Mega Menu for some time. We hope you love it, and we would really appreciate it if you would %s.', 'megamenu' ),
					wp_kses(
						$review_processor->get_updated_html(),
						[
							'a' => [
								'href'   => true,
								'target' => true,
								'rel'    => true,
							],
						]
					)
				);
				?>
			</p>
		</div>
		<?php
	}
}
