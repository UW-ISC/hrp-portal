<?php
/**
 * IvyForms promotional admin notice (Promo-Campaigns Figma node 3332:9201).
 *
 * @package wpDataTables
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$can_install = current_user_can( 'install_plugins' );
?>
<div class="notice is-dismissible wpdt-ivyforms-promo-notice">
	<div class="wpdt-ivyforms-promo-outer">
		<div class="wpdt-ivyforms-promo-card">
			<div class="wpdt-ivyforms-promo-row">
				<div class="wpdt-ivyforms-promo-accent" aria-hidden="true"></div>
				<div class="wpdt-ivyforms-promo-content">
					<div class="wpdt-ivyforms-promo-left">
						<div class="wpdt-ivyforms-promo-kicker-row">
							<img class="wpdt-ivyforms-promo-mark" src="<?php echo esc_url( WDT_ROOT_URL . 'assets/img/ivyforms_promo_banner/melograno-mark.svg' ); ?>" width="21" height="23" alt="" aria-hidden="true">
							<p class="wpdt-ivyforms-promo-kicker"><?php esc_html_e( 'From Melograno Ventures', 'wpdatatables' ); ?></p>
						</div>
						<h2 class="wpdt-ivyforms-promo-title"><?php esc_html_e( 'Stop entering data by hand', 'wpdatatables' ); ?></h2>
						<p class="wpdt-ivyforms-promo-subtitle">
							<?php esc_html_e( 'Your visitors submit data directly with IvyForms and responses flow into tables automatically. No copy-pasting, no spreadsheets.', 'wpdatatables' ); ?>
						</p>
						<div class="wpdt-ivyforms-promo-actions">
							<?php if ( $can_install ) : ?>
								<button type="button" class="wpdt-ivyforms-promo-btn wpdt-ivyforms-promo-btn-primary" id="wpdt-ivyforms-install-btn">
									<?php esc_html_e( 'Install IvyForms', 'wpdatatables' ); ?>
								</button>
								<input type="hidden" id="wpdt-ivyforms-install-nonce" value="<?php echo esc_attr( wp_create_nonce( 'ivyforms_install' ) ); ?>">
							<?php endif; ?>
							<a class="wpdt-ivyforms-promo-btn wpdt-ivyforms-promo-btn-secondary" href="<?php echo esc_url( 'https://ivyforms.com/?utm_source=wpdt-cross-sell&utm_medium=banner-integration&utm_content=wpdt&utm_campaign=wpdt' ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Explore IvyForms', 'wpdatatables' ); ?>
							</a>
							<span class="wpdt-ivyforms-promo-install-status" id="wpdt-ivyforms-install-status" aria-live="polite"></span>
						</div>
					</div>
					<div class="wpdt-ivyforms-promo-visual" aria-hidden="true">
						<img class="wpdt-ivyforms-promo-mockup" src="<?php echo esc_url( WDT_ROOT_URL . 'assets/img/ivyforms_promo_banner/ivyforms-promo-mockup.svg' ); ?>" width="322" height="234" alt="">
					</div>
				</div>
			</div>
			<img class="wpdt-ivyforms-promo-integration" src="<?php echo esc_url( WDT_ROOT_URL . 'assets/img/ivyforms_promo_banner/ivy_wpdt_integration.svg' ); ?>" width="129" height="65" alt="" aria-hidden="true">
			<div class="wpdt-ivyforms-promo-dismiss">
				<input type="hidden" id="wpdt-ivyforms-promo-user-dismiss-nonce" value="<?php echo esc_attr( wp_create_nonce( 'wdt_ivyforms_promo_user_dismiss' ) ); ?>">
				<input type="hidden" id="wpdt-ivyforms-promo-dismiss-nonce" value="<?php echo esc_attr( wp_create_nonce( 'wdt_ivyforms_promo_dismiss' ) ); ?>">
				<button type="button" class="wpdt-ivyforms-promo-close" aria-label="<?php esc_attr_e( 'Dismiss this notice', 'wpdatatables' ); ?>">
					<span class="wpdt-ivyforms-promo-close-x" aria-hidden="true">&#215;</span>
				</button>
				<button type="button" class="wpdt-ivyforms-promo-never">
					<?php esc_html_e( 'Never show again', 'wpdatatables' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
