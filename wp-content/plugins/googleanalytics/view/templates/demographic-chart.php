<?php if(!$demo_enabled) : ?>
	<div class="demo-ad ga-panel ga-panel-default">
		<div class="ga-panel-heading">
			<strong>
				<?php esc_html_e('Get Demographic Data!'); ?>
				<button id="demographic-popup">
					<?php esc_html_e('Click Here To Enable', 'googleanalytics'); ?>
				</button>
			</strong>
		</div>
		<img src="<?php echo trailingslashit(get_home_url()) . 'wp-content/plugins/googleanalytics/assets/images/demo-ad.png'; ?>" />
	</div>
<?php elseif ($need_account_demo_enable) : ?>
	<div class="demo-ad ga-panel ga-panel-default">
		<div class="ga-panel-heading">
			<strong>
				<?php esc_html_e('If no demographics data is shown, you\'ll most likely need to enable "Demographics" within Google Analytics or there\'s insufficient Demographic data to display.
We recommend viewing your Google Analytics account to determine best solution:'); ?>
				<br>
				<a href="<?php echo esc_url( $demographic_page_url ); ?>/" class="view-report" target="_blank">
					<?php echo esc_html__('Go to my account' ); ?>
				</a>
			</strong>
		</div>
	</div>
<?php else: ?>
	<div class="filter-choices">
		<a href="<?php echo get_admin_url('', $seven_url ); ?>" class="<?php echo esc_attr( $selected7 ); ?>">
			7 days
		</a>
		<a href="<?php echo get_admin_url('', $thirty_url ); ?>" class="<?php echo esc_attr( $selected30 ); ?>">
			30 days
		</a>
	</div>
	<div class="demo-ad ga-panel ga-panel-default">
		<div class="ga-panel-heading">
			<strong>
				<?php esc_html_e('Demographic by sessions'); ?>
			</strong>
		</div>
		<div class="ga-demo-chart">
			<div class="ga-panel-body ga-chart gender">
				<div id="demo_chart_gender_div" style="width: 100%;"></div>
				<div class="ga-loader-wrapper stats-page">
					<div class="ga-loader stats-page-loader"></div>
				</div>
			</div>
			<div class="ga-panel-body ga-chart gender">
				<div id="demo_chart_age_div" style="width: 100%;"></div>
				<div class="ga-loader-wrapper stats-page">
					<div class="ga-loader stats-page-loader"></div>
				</div>
			</div>
		</div>
	</div>
	<a href="<?php echo esc_url( $demographic_page_url ); ?>/" class="view-report" target="_blank">
		<?php echo esc_html__('View Full Report' ); ?>
	</a>
<hr>
<?php
endif;
