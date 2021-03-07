<?php
/**
 * Template for GDPR side bar ad in settings page.
 */
?>
<div class="sidebar-ad">
	<div id="gdpr-ad">
		<h2 style="text-decoration: underline;">
			<?php esc_html_e('Check out our new GDPR Compliance Tool!', 'googleanalytics'); ?>
		</h2>
		<div class="row">
			<div class="col-md-12">
				<img src="<?php echo $plugin_uri . 'assets/images/gdpr-ex.png'; ?>" />
			</div>
			<div class="col-md-6">
				<h3><?php esc_html_e('Confirm Consent', 'googleanalytics'); ?></h3>
				<p>
					<?php esc_html_e(
						'A simple and streamlined way to confirm a user’s initial acceptance or rejection of cookie collection',
						'googleanalytics'
					); ?>
				</p>
			</div>
			<div class="col-md-6">
				<h3><?php esc_html_e('Select Purpose', 'googleanalytics'); ?></h3>
				<p>
					<?php esc_html_e(
						'A transparent system of verifying the intent of collecting a user’s cookies, and giving the option to opt in or out',
						'googleanalytics'
					); ?>
				</p>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<h3><?php esc_html_e('Indicate Company', 'googleanalytics'); ?></h3>
				<p>
					<?php esc_html_e(
						'A comprehensive record of company-level information that allows users to monitor and control the recipients of cookie collection',
						'googleanalytics'
					); ?>
				</p>
			</div>
			<div class="col-md-6">
				<h3><?php esc_html_e('Access Data Rights', 'googleanalytics'); ?></h3>
				<p>
					<?php esc_html_e(
						'A centralized database where users can review the latest privacy policies and information pertaining to their cookie collection',
						'googleanalytics'
					); ?>
				</p>
			</div>
		</div>
		<div class="row register-section">
			<?php if ( Ga_Helper::are_features_enabled() ) : ?>
				<td>
					<button class="gdpr-enable"><?php esc_html_e('Enable'); ?></button>
				</td>
			<?php else : ?>
				<td>
					<label class="<?php echo ( ! Ga_Helper::are_features_enabled() ) ? 'label-grey ga-tooltip' : '' ?>">
						<button class="gdpr-enable" disabled="disabled"><?php esc_html_e('Enable'); ?></button>
						<span class="ga-tooltiptext ga-tt-abs"><?php _e( $tooltip ); ?></span>
					</label>
				</td>
			<?php endif; ?>
		</div>
	</div>
</div>
