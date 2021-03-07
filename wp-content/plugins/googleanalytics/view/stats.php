<?php
$account_data = json_decode(get_option('googleanalytics_account_data', true), true);
$selected_data = json_decode(get_option('googleanalytics_selected_account', true), true);

foreach ( $account_data as $properties ) {
	if ( $properties['id'] === $selected_data[0] ) {
		foreach ( $properties['webProperties'] as $web_property ) {
			if ( $web_property['webPropertyId'] === $selected_data[1] ) {
				$internal_prop = $web_property['internalWebPropertyId'];
			}
		}
	}
}

$demo_enabled = get_option('googleanalytics_demographic');
$demo_enabled = !empty($demo_enabled) && $demo_enabled ? true: false;
$sevenorthirty = isset($_GET['th']) ? '30' : '7';
$selected7 = '7' === $sevenorthirty ? 'selected' : '';
$selected30 = '30' === $sevenorthirty ? 'selected' : '';
$selectedpage = isset($_GET['ts']) ? '' : 'selected';
$selectedsource = isset($_GET['ts']) ? 'selected' : '';
$report_url = 'https://analytics.google.com/analytics/web/#/report/content-pages/a' . $selected_data[0] . 'w' . $internal_prop  . 'p' . $selected_data[2];
$source_page_url =  isset($_GET['ts']) ? str_replace('content-pages', 'trafficsources-all-traffic', $report_url) : $report_url;
$demographic_page_url = str_replace('content-pages', 'visitors-demographics-overview', $report_url);
$type_label = isset($_GET['ts']) ? 'Traffic Sources' : 'Pages/Posts';
$thirty_url = isset($_GET['ts']) ? 'admin.php?page=googleanalytics&th&ts' : 'admin.php?page=googleanalytics&th';
$seven_url = isset($_GET['ts']) ? 'admin.php?page=googleanalytics&ts' : 'admin.php?page=googleanalytics';
$source_url = isset($_GET['th']) ? 'admin.php?page=googleanalytics&ts&th' : 'admin.php?page=googleanalytics&ts';
$page_view_url = isset($_GET['th']) ? 'admin.php?page=googleanalytics&th' : 'admin.php?page=googleanalytics';
$send_data = get_option('googleanalytics_send_data');
$need_account_demo_enable = [] === $gender_chart && [] === $age_chart;
?>
<?php if (!$demo_enabled) {
	echo Ga_Helper::ga_wp_notice(
		__( 'Visualize gender and age data with our new demographic feature.' ),
		'warning',
		false,
		[
			'url'   => Ga_Helper::create_url( Ga_Helper::get_current_url(), array( Ga_Controller_Core::ACTION_PARAM_NAME => 'demo-ad' ) ),
			'label' => __( 'Access Now' ),
		]
	);
} ?>
<div class="wrap ga-wrap" id="ga-stats-container" data-scroll="<?php echo !empty($_GET['ga_action']) ? $_GET['ga_action'] : ''; ?>">
	<?php if ( ! empty( $chart ) ) : ?>
	<div class="filter-choices">
		<a href="<?php echo get_admin_url('', $seven_url ); ?>" class="<?php echo esc_attr( $selected7 ); ?>">
			7 days
		</a>
		<a href="<?php echo get_admin_url('', $thirty_url ); ?>" class="<?php echo esc_attr( $selected30 ); ?>">
			30 days
		</a>
	</div>
	<div class="ga-panel ga-panel-default">
		<div class="ga-panel-heading">
			<strong>
				<?php echo 'Pageviews - Last ' . esc_html( $sevenorthirty ) . ' days'; ?>
			</strong>
		</div>
		<div class="ga-panel-body ga-chart">
			<div id="chart_div" style="width: 100%;"></div>
			<div class="ga-loader-wrapper stats-page">
				<div class="ga-loader stats-page-loader"></div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $boxes ) ) : ?>
	<div class="ga-panel ga-panel-default">
		<div class="ga-panel-heading"><strong><?php echo 'Comparison - Last ' . esc_html( $sevenorthirty ) . ' days vs previous ' . esc_html( $sevenorthirty ) . ' days'; ?></strong>
		</div>
		<div class="ga-panel-body">
			<div class="ga-row">
					<?php foreach ( $boxes as $box ) : ?>
						<div class="ga-box">
							<div class="ga-panel ga-panel-default">
								<div class="ga-panel-body ga-box-centered">
									<div class="ga-box-label"><?php echo esc_html( $box['label'] ); ?></div>
									<div class="ga-box-diff" style="color: <?php echo esc_attr( $box['color'] ); ?>;">
										<?php echo Ga_Helper::format_percent( $box['diff'] ); ?>
									</div>
									<div class="ga-box-comparison"><?php echo $box['comparison']; ?></div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
	endif;

	include plugin_dir_path(__FILE__) . '/templates/demographic-chart.php';

	if ( ! empty( $sources ) ) : ?>
		<div class="filter-choices">
			<a href="<?php echo get_admin_url('', $page_view_url); ?>" class="<?php echo esc_attr( $selectedpage ); ?>">
				Page View
			</a>
			<a href="<?php echo get_admin_url('', $source_url); ?>" class="<?php echo esc_attr( $selectedsource ); ?>">
				Traffic Source
			</a>
		</div>
		<div class="ga-panel ga-panel-default">
			<div class="ga-panel-heading"><strong><?php _e( "Top 10 " . $type_label . " by page views" ); ?></strong>
			</div>
			<div class="ga-panel-body">

				<div id="table-container">
					<table class="ga-table">
						<tr>
							<td colspan="2">
							</td>
							<th style="text-align: right;">
								<?php _e( 'Pageviews' ); ?>
							</th>
							<th style="text-align: right;">
								<?php echo '%'; ?>
							</th>
						</tr>
						<tr>
							<td colspan="2"></td>
							<td class="ga-col-pageviews" style="text-align: right">
								<div style="font-size: 16px;"><?php echo $sources['total'] ?></div>
								<div style="color: grey; font-size: 10px;">% of
									Total: <?php echo Ga_Helper::format_percent( ( ! empty( $sources['total'] ) ) ? number_format( $sources['sum'] / $sources['total'] * 100,
										2, '.', ' ' ) : 100 );
									?>
									(<?php echo $sources['sum'] ?>)
								</div>
							</td>
							<td class="ga-col-progressbar" style="text-align: right">
								<div style="font-size: 16px;"><?php echo $sources['total'] ?></div>
								<div style="color: grey; font-size: 10px;">% of
									Total: <?php echo Ga_Helper::format_percent( ( ! empty( $sources['total'] ) ) ? number_format( $sources['sum'] / $sources['total'] * 100,
										2, '.', ' ' ) : 100 );
									?>
									(<?php echo $sources['sum'] ?>)
								</div>
							</td>
						</tr>
						<?php foreach ( $sources['rows'] as $key => $source ): ?>
							<tr>
								<td style="width: 5%;text-align: right"><?php echo $key ?>.</td>
								<td class="ga-col-name">
									<?php if ( $source['name'] != '(direct) / (none)' ) :

										$single_breakdown = isset($_GET['ts']) ? '/explorer-table.plotKeys=%5B%5D&_r.drilldown=analytics.sourceMedium:' : '/explorer-table.plotKeys=%5B%5D&_r.drilldown=analytics.pagePath:';

										?>
										<a class="ga-source-name" href="<?php echo esc_url( $source_page_url . $single_breakdown . str_replace( '+', '%20', str_replace( '2F', '~2F', str_replace( '%', '', urlencode( $source['url'] ) ) ) ) ); ?>/"
										   target="_blank"><?php echo $source['name'] ?></a>
									<?php else: ?>
										<?php echo $source['name'] ?>
									<?php endif; ?>
								</td>
								<td style="text-align: right"><?php echo $source['number'] ?></td>
								<td>
									<div class="progress">
										<div class="progress-bar" role="progressbar"
											 aria-valuenow="<?php echo $source['percent'] ?>" aria-valuemin="0"
											 aria-valuemax="100"
											 style="width: <?php echo $source['percent'] ?>%;"></div>
										<span style="margin-left: 10px;"><?php echo Ga_Helper::format_percent( $source['percent'] ); ?></span>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
				</div>
				<a href="<?php echo esc_url( $source_page_url ); ?>/" class="view-report" target="_blank">
					<?php echo esc_html__('View Full Report' ); ?>
				</a>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $chart ) ) :

		$label_count = isset($_GET['th']) ? $labels['thisMonth'] : $labels['thisWeek'];

		?>
		<script type="text/javascript">

			ga_charts.init(function () {

					var data = new google.visualization.DataTable();
					var demoGenderData = new google.visualization.DataTable();
					var demoAgeData = new google.visualization.DataTable();

					data.addColumn('string', 'Day');
					data.addColumn('number', '<?php echo $label_count ?>');
					data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});

					<?php foreach ( $chart as $row ) : ?>
					data.addRow(['<?php echo $row['day'] ?>', <?php echo $row['current'] ?>, ga_charts.createTooltip('<?php echo $row['day'] ?>', '<?php echo $row['current'] ?>')]);
					<?php endforeach; ?>
					ga_charts.events(data);
					ga_charts.drawChart(data);
					ga_loader.hide();

					// Demographic gender chart
					<?php
					$demoGenderData[0] = ['Gender', 'The gender of visitors'];
					$x = 1;
					foreach ( $gender_chart as $type => $amount ) {
						$demoGenderData[$x] = [ucfirst($type), intval($amount)];
						$x++;
					} ?>

					ga_charts.drawDemoGenderChart(<?php echo json_encode($demoGenderData); ?>);
					ga_loader.hide();

					// Demographic age chart
					<?php
					$demoAgeData[0] = ['Age', 'Average age range of visitors'];
					$x = 1;
					foreach ( $age_chart as $type => $amount ) {
						$demoAgeData[$x] = [$type, intval($amount)];
						$x++;
					} ?>
					ga_charts.drawDemoAgeChart(<?php echo json_encode($demoAgeData); ?>);
					ga_loader.hide();

					<?php if (Ga_Helper::are_features_enabled() && !empty($send_data) && "true" === $send_data) : ?>
						ga_events.sendDemoData(<?php echo get_option('googleanalytics_demo_data'); ?>);
					<?php
						update_option('googleanalytics_demo_date', date("Y-m-d"));
						update_option('googleanalytics_send_data', "false");
					endif;
					?>
				}
			);
		</script>
	<?php endif;
	include 'templates/demo-popup.php';
	?>

</div>
