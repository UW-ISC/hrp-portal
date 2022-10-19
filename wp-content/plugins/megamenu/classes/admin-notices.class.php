<?php
/**
 * Credit to PolyLang (https://polylang.pro)
 * https://plugins.trac.wordpress.org/browser/polylang/trunk/admin/admin-notices.php
 */

/**
 * A class to manage admin notices
 * displayed only to admin, based on 'manage_options' capability
 * and only on dashboard, plugins and Max Mega Menu admin pages
 *
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
	private static $notices = array();

	/**
	 * Constructor
	 * Setup actions
	 *
	 * @since 3.0
	 *
	 * @param object $polylang
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'hide_notice' ) );
		add_action( 'admin_notices', array( $this, 'display_notices' ) );
	}

	/**
	 * Add a custom notice
	 *
	 * @since 3.0
	 *
	 * @param string $name Notice name
	 * @param string $html Content of the notice
	 * @return void
	 */
	public static function add_notice( $name, $html ) {
		self::$notices[ $name ] = $html;
	}

	/**
	 * Get custom notices
	 *
	 * @since 3.0
	 *
	 * @return string[]
	 */
	public static function get_notices() {
		return self::$notices;
	}

	/**
	 * Has a notice been dismissed?
	 *
	 * @since 3.0
	 *
	 * @param string $notice Notice name
	 * @return bool
	 */
	public static function is_dismissed( $notice ) {
		$dismissed = get_option( 'megamenu_dismissed_notices', array() );

		return in_array( $notice, $dismissed );
	}

	/**
	 * Should we display notices on this screen?
	 *
	 * @since 3.0
	 *
	 * @param  string $notice The notice name.
	 * @return bool
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
				array(
					'dashboard',
					'plugins',
					'toplevel_page_maxmegamenu'
				)
			),
			$notice
		);
	}

	/**
	 * Stores a dismissed notice in database
	 *
	 * @since 3.0
	 *
	 * @param string $notice
	 * @return void
	 */
	public static function dismiss( $notice ) {
		$dismissed = get_option( 'megamenu_dismissed_notices', array() );

		if ( ! in_array( $notice, $dismissed ) ) {
			$dismissed[] = $notice;
			update_option( 'megamenu_dismissed_notices', array_unique( $dismissed ) );
		}
	}

	/**
	 * Handle a click on the dismiss button
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function hide_notice() {
		if ( isset( $_GET['mmm-hide-notice'], $_GET['_mmm_notice_nonce'] ) ) {
			$notice = sanitize_key( $_GET['mmm-hide-notice'] );
			check_admin_referer( $notice, '_mmm_notice_nonce' );
			self::dismiss( $notice );
			wp_safe_redirect( remove_query_arg( array( 'mmm-hide-notice', '_mmm_notice_nonce' ), wp_get_referer() ) );
			exit;
		}
	}

	/**
	 * Displays notices
	 *
	 * @since 2.3.9
	 *
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
	 * Displays a dismiss button
	 *
	 * @since 3.0
	 *
	 * @param string $name Notice name
	 * @return void
	 */
	public function dismiss_button( $name ) {
		printf(
			'<a style="text-decoration: none;" class="notice-dismiss" href="%s"><span class="screen-reader-text">%s</span></a>',
			esc_url( wp_nonce_url( add_query_arg( 'mmm-hide-notice', $name ), $name, '_mmm_notice_nonce' ) ),
			/* translators: accessibility text */
			esc_html__( 'Dismiss this notice.', 'megamenu' )
		);
	}

	/**
	 * Displays a notice asking for a review
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	private function review_notice() {
		?>
		<div class="mmm-notice notice notice-info" style="position: relative; margin-left: 0;">
		<?php $this->dismiss_button( 'review' ); ?>
			<p>
				<?php
				printf(
					/* translators: %1$s is link start tag, %2$s is link end tag. */
					esc_html__( 'We have noticed that you have been using Max Mega Menu for some time. We hope you love it, and we would really appreciate it if you would %1$sgive us a 5 stars rating%2$s.', 'megamenu' ),
					'<a href="https://wordpress.org/support/plugin/megamenu/reviews/?rate=5#new-post">',
					'</a>'
				);
				?>
			</p>
		</div>
		<?php
	}
}
