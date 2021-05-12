<?php
/**
 * Template for GDPR side bar ad in settings page.
 */
?>
<div class="sidebar-ad">
	<div id="demo-ad">
		<h2 style="text-decoration: underline;">
			<?php esc_html_e('Demographics data now available!', 'googleanalytics'); ?>
		</h2>
		<div class="row">
			<div class="col-md-12">
				<img src="<?php echo $plugin_uri . 'assets/images/demo-ex.png'; ?>" />
			</div>
			<div class="col-md-6">
				<p>
					<?php esc_html_e('Find out more about your target audience by enabling the demographics feature!', 'googleanalytics'); ?>
				</p>
				<h3><?php esc_html_e('Why Collect Demographics and Interests Data?', 'googleanalytics'); ?></h3>
				<p>
					<?php esc_html_e(
						'By viewing demographics data, you will learn more details about your visitors so you can deliver content/create products that address their needs!',
						'googleanalytics'
					); ?>
				</p>
			</div>
			<div class="col-md-6">
				<h3><?php esc_html_e('Gender', 'googleanalytics'); ?></h3>
				<p>
					<?php esc_html_e(
						'Understanding exactly which gender visits different areas of your website, could allow you to optimise those areas accordingly.',
						'googleanalytics'
					); ?>
				</p>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<h3><?php esc_html_e('Age', 'googleanalytics'); ?></h3>
				<p>
					<?php esc_html_e(
						'Determine your site\'s age demographic and make the site more friendly to use for that group.',
						'googleanalytics'
					); ?>
				</p>
			</div>
		</div>
		<div class="row register-section">
			<?php if ( Ga_Helper::are_features_enabled() ) : ?>
				<td>
					<button id="demographic-popup"><?php esc_html_e('Enable'); ?></button>
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
