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
 * Integrations may output notices on the {@see 'megamenu_admin_notices'} action
 * and use {@see Mega_Menu_Admin_Notices::output_persistent_dismissible_notice()}
 * with {@see Mega_Menu_Admin_Notices::clear_dismissed_notice()} for dismissal lifecycle.
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
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_persistent_notice_dismiss_assets' ] );
		add_action( 'wp_ajax_megamenu_dismiss_admin_notice', [ $this, 'ajax_dismiss_admin_notice' ] );
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

		return in_array( $notice, $dismissed, true );
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

		if ( ! in_array( $notice, $dismissed, true ) ) {
			$dismissed[] = $notice;
			update_option( 'megamenu_dismissed_notices', array_unique( $dismissed ) );
		}
	}

	/**
	 * Allowed HTML in optional notice wrappers (`before_content` / `after_paragraph`).
	 *
	 * @since 3.9
	 * @return array<string, array<string, bool>>
	 */
	public static function get_persistent_notice_fragment_kses() {
		$defaults = [
			'div'  => [
				'class' => true,
			],
			'span' => [
				'class'       => true,
				'aria-hidden' => true,
			],
			'p'    => [
				'class' => true,
			],
			'a'    => [
				'href'   => true,
				'class'  => true,
				'target' => true,
				'rel'    => true,
			],
		];

		/**
		 * Filters kses rules for HTML fragments wrapped around persistent notice paragraphs.
		 *
		 * @since 3.9
		 *
		 * @param array<string, array<string, bool>> $defaults Kses rules.
		 */
		return apply_filters( 'megamenu_admin_persistent_notice_fragment_kses', $defaults );
	}

	/**
	 * Enqueue script for AJAX-dismiss of persistent admin notices (review, integrations).
	 *
	 * @since 3.9
	 * @param string $hook Current admin screen hook suffix.
	 * @return void
	 */
	public function enqueue_persistent_notice_dismiss_assets( $hook ) {
		$on_mmm_screen = ( false !== strpos( $hook, 'maxmegamenu' ) );
		$on_review     = in_array( $hook, [ 'index.php', 'plugins.php', 'plugins-network.php' ], true );

		if ( ! $on_mmm_screen && ! $on_review ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( apply_filters( 'megamenu_options_capability', 'edit_theme_options' ) ) ) {
			return;
		}

		wp_enqueue_script(
			'megamenu-admin-dismiss-notices',
			plugins_url( 'js/admin/dismiss-notices.js', MEGAMENU_PATH . 'megamenu.php' ),
			[ 'jquery' ],
			defined( 'MEGAMENU_VERSION' ) ? MEGAMENU_VERSION : false,
			true
		);

		wp_localize_script(
			'megamenu-admin-dismiss-notices',
			'megamenuDismissNotices',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			]
		);
	}

	/**
	 * Persist dismissal of a registered admin notice via AJAX.
	 *
	 * @since 3.9
	 * @return void
	 */
	public function ajax_dismiss_admin_notice() {
		check_ajax_referer( 'megamenu_dismiss_admin_notice', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( apply_filters( 'megamenu_options_capability', 'edit_theme_options' ) ) ) {
			wp_send_json_error( null, 403 );
		}

		$notice = isset( $_POST['notice'] ) ? sanitize_key( wp_unslash( $_POST['notice'] ) ) : '';
		if ( '' === $notice ) {
			wp_send_json_error( null, 400 );
		}

		/**
		 * Notice keys allowed to be dismissed via AJAX / GET.
		 *
		 * @since 3.9
		 *
		 * @param string[] $keys Sanitized notice identifiers.
		 */
		$allowed = apply_filters( 'megamenu_dismissible_admin_notice_keys', [ 'review' ] );

		if ( ! is_array( $allowed ) || ! in_array( $notice, array_map( 'sanitize_key', $allowed ), true ) ) {
			wp_send_json_error( null, 400 );
		}

		self::dismiss( $notice );
		wp_send_json_success();
	}

	/**
	 * Handle a click on the dismiss button.
	 *
	 * @since 3.0
	 * @return void
	 */
	public function hide_notice() {
		if ( isset( $_GET['mmm-hide-notice'], $_GET['_mmm_notice_nonce'] ) ) {
			$notice = sanitize_key( wp_unslash( $_GET['mmm-hide-notice'] ) );
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
		/**
		 * Fires when Max Mega Menu is about to output admin notices (before the built-in review notice).
		 *
		 * Use {@see Mega_Menu_Admin_Notices::output_persistent_dismissible_notice()} for core-style
		 * dismissible notices that persist dismissal via `megamenu_dismissed_notices`.
		 *
		 * @since 3.9
		 *
		 * @param Mega_Menu_Admin_Notices $admin_notices The notices manager instance.
		 */
		do_action( 'megamenu_admin_notices', $this );

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
	 * Remove a notice slug from `megamenu_dismissed_notices` so it can be shown again.
	 *
	 * @since 3.9
	 * @param string $notice Sanitized notice key (same as `mmm-hide-notice` / {@see dismiss()}).
	 * @return void
	 */
	public static function clear_dismissed_notice( $notice ) {
		$notice    = sanitize_key( $notice );
		$dismissed = get_option( 'megamenu_dismissed_notices', [] );

		if ( ! is_array( $dismissed ) || ! in_array( $notice, $dismissed, true ) ) {
			return;
		}

		$filtered = array_values( array_diff( $dismissed, [ $notice ] ) );

		if ( empty( $filtered ) ) {
			delete_option( 'megamenu_dismissed_notices' );
		} else {
			update_option( 'megamenu_dismissed_notices', $filtered );
		}
	}

	/**
	 * Default allowed inline HTML inside {@see output_persistent_dismissible_notice()} paragraphs.
	 *
	 * @since 3.9
	 * @return array<string, array<string, bool>>
	 */
	public static function get_persistent_notice_paragraph_kses() {
		$defaults = [
			'a'      => [
				'href'   => true,
				'target' => true,
				'rel'    => true,
			],
			'strong' => [],
			'em'     => [],
		];

		/**
		 * Filters allowed HTML inside persistent admin notice `<p>` content.
		 *
		 * @since 3.9
		 *
		 * @param array<string, array<string, bool>> $defaults Kses rules.
		 */
		return apply_filters( 'megamenu_admin_persistent_notice_paragraph_kses', $defaults );
	}

	/**
	 * Core-style dismissible admin notice with AJAX dismiss (persists via `megamenu_dismissed_notices`).
	 * Legacy GET `mmm-hide-notice` URLs are still handled in {@see hide_notice()}.
	 *
	 * @since 3.9
	 * @param string               $notice_type          One of `info`, `warning`, `error`, `success`.
	 * @param string               $paragraph_inner_html Message HTML for inside `<p>` (caller-escaped / trusted for kses).
	 * @param string               $dismiss_key          Notice key for storage / legacy query arg.
	 * @param array<string, mixed> $args {
	 *     Optional. Extra markup and classes.
	 *
	 *     @type string       $before_content     Markup after the opening `.notice` div, before `<p>`.
	 *     @type string       $after_paragraph    Markup after `</p>`, before the dismiss button.
	 *     @type string|array $wrapper_extra_classes Additional classes on the outer `.notice` div.
	 * }
	 * @return void
	 */
	public static function output_persistent_dismissible_notice( $notice_type, $paragraph_inner_html, $dismiss_key, $args = [] ) {
		$args = wp_parse_args(
			$args,
			[
				'before_content'        => '',
				'after_paragraph'       => '',
				'wrapper_extra_classes' => '',
			]
		);

		$allowed_types = [ 'info', 'warning', 'error', 'success' ];
		$notice_type   = sanitize_key( (string) $notice_type );
		if ( ! in_array( $notice_type, $allowed_types, true ) ) {
			$notice_type = 'info';
		}

		$dismiss_key = sanitize_key( $dismiss_key );

		$paragraph_kses = self::get_persistent_notice_paragraph_kses();
		$fragment_kses  = self::get_persistent_notice_fragment_kses();

		/**
		 * Filters whether a persistent admin notice should be printed.
		 *
		 * @since 3.9
		 *
		 * @param bool               $display              Whether to output markup.
		 * @param string             $notice_type          Notice class suffix.
		 * @param string             $dismiss_key          Dismiss / storage key.
		 * @param string             $paragraph_inner_html Inner HTML (escaped per caller).
		 * @param array<string,mixed> $args                Parsed arguments.
		 */
		if ( ! apply_filters( 'megamenu_should_output_persistent_admin_notice', true, $notice_type, $dismiss_key, $paragraph_inner_html, $args ) ) {
			return;
		}

		$extra_classes = $args['wrapper_extra_classes'];
		if ( is_array( $extra_classes ) ) {
			$extra_classes = implode( ' ', array_map( 'sanitize_html_class', $extra_classes ) );
		} else {
			$extra_classes = sanitize_text_field( (string) $extra_classes );
		}

		$classes = trim( 'notice notice-' . $notice_type . ' is-dismissible ' . $extra_classes );
		$nonce   = wp_create_nonce( 'megamenu_dismiss_admin_notice' );

		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php
			if ( $args['before_content'] ) {
				echo wp_kses( $args['before_content'], $fragment_kses );
			}
			?>
			<p><?php echo wp_kses( $paragraph_inner_html, $paragraph_kses ); ?></p>
			<?php
			if ( $args['after_paragraph'] ) {
				echo wp_kses( $args['after_paragraph'], $fragment_kses );
			}
			?>
			<button
				type="button"
				class="notice-dismiss"
				data-megamenu-dismiss="<?php echo esc_attr( $dismiss_key ); ?>"
				data-megamenu-dismiss-nonce="<?php echo esc_attr( $nonce ); ?>"
			>
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'megamenu' ); ?></span>
			</button>
		</div>
		<?php
	}

	/**
	 * Displays a notice asking for a review.
	 *
	 * @since 3.0
	 * @return void
	 */
	private function review_notice() {
		$review_processor = new WP_HTML_Tag_Processor( '<a>' . esc_html__( 'give us a 5 stars rating', 'megamenu' ) . '</a>' );
		if ( $review_processor->next_tag( 'a' ) ) {
			$review_processor->set_attribute( 'href', 'https://wordpress.org/support/plugin/megamenu/reviews/?rate=5#new-post' );
			$review_processor->set_attribute( 'target', '_blank' );
			$review_processor->set_attribute( 'rel', 'noopener noreferrer' );
		}
		$paragraph = sprintf(
			/* translators: %s: link to the plugin review form on WordPress.org */
			esc_html__( 'We have noticed that you have been using Max Mega Menu for some time. We hope you love it, and we would really appreciate it if you would %s.', 'megamenu' ),
			wp_kses(
				$review_processor->get_updated_html(),
				self::get_persistent_notice_paragraph_kses()
			)
		);
		self::output_persistent_dismissible_notice( 'info', $paragraph, 'review', [] );
	}
}
