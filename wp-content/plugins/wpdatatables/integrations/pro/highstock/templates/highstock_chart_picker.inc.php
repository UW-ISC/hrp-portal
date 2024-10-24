<?php defined('ABSPATH') or die('Access denied.'); ?>

<!-- div.highstock-charts-type -->
<div class="charts-type highstock-charts-type col-sm-12 col-md-12">

<div class="row">
    <div class="wdt-chart-wizard-chart-selecter-block col-sm-2">
        <div class="card" data-type="highstock_line_chart" data-min_columns="2" data-max_columns="0">
            <div class="card-header">
                <div class="wdt-chart-wizard-overlay"></div>
                <img class="img-responsive"
                     src="<?php echo WDT_HS_ASSETS_URL ?>img/highstock-line-chart.png">
            </div>
            <div class="card-body">
                <h4 class="f-14"><?php esc_html_e('Line chart', 'wpdatatables'); ?></h4>
            </div>
        </div>
    </div>

    <div class="wdt-chart-wizard-chart-selecter-block col-sm-2">
        <div class="card" data-type="highstock_spline_chart" data-min_columns="2" data-max_columns="0">
            <div class="card-header">
                <div class="wdt-chart-wizard-overlay"></div>
                <img class="img-responsive"
                     src="<?php echo WDT_HS_ASSETS_URL ?>img/highstock-spline-chart.png">
            </div>
            <div class="card-body">
                <h4 class="f-14"><?php esc_html_e('Spline chart', 'wpdatatables'); ?></h4>
            </div>
        </div>
    </div>

    <div class="wdt-chart-wizard-chart-selecter-block col-sm-2">
        <div class="card" data-type="highstock_stepline_chart" data-min_columns="2" data-max_columns="0">
            <div class="card-header">
                <div class="wdt-chart-wizard-overlay"></div>
                <img class="img-responsive"
                     src="<?php echo WDT_HS_ASSETS_URL ?>img/highstock-stepline-chart.png">
            </div>
            <div class="card-body">
                <h4 class="f-14"><?php esc_html_e('Stepline chart', 'wpdatatables'); ?></h4>
            </div>
        </div>
    </div>

    <div class="wdt-chart-wizard-chart-selecter-block col-sm-2">
        <div class="card" data-type="highstock_line_with_markers_chart" data-min_columns="2" data-max_columns="0">
            <div class="card-header">
                <div class="wdt-chart-wizard-overlay"></div>
                <img class="img-responsive"
                     src="<?php echo WDT_HS_ASSETS_URL ?>img/highstock-line-with-markers-chart.png">
            </div>
            <div class="card-body">
                <h4 class="f-14"><?php esc_html_e('Line with markers chart', 'wpdatatables'); ?></h4>
            </div>
        </div>
    </div>

    <div class="wdt-chart-wizard-chart-selecter-block col-sm-2">
        <div class="card" data-type="highstock_point_markers_only_chart" data-min_columns="2" data-max_columns="0">
            <div class="card-header">
                <div class="wdt-chart-wizard-overlay"></div>
                <img class="img-responsive"
                     src="<?php echo WDT_HS_ASSETS_URL ?>img/highstock-point-markers-chart.png">
            </div>
            <div class="card-body">
                <h4 class="f-14"><?php esc_html_e('Point markers only chart', 'wpdatatables'); ?></h4>
            </div>
        </div>
    </div>

    <div class="wdt-chart-wizard-chart-selecter-block col-sm-2">
        <div class="card" data-type="highstock_area_chart" data-min_columns="2" data-max_columns="0">
            <div class="card-header">
                <div class="wdt-chart-wizard-overlay"></div>
                <img class="img-responsive"
                     src="<?php echo WDT_HS_ASSETS_URL ?>img/highstock-area-chart.png">
            </div>
            <div class="card-body">
                <h4 class="f-14"><?php esc_html_e('Area chart', 'wpdatatables'); ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="wdt-chart-wizard-chart-selecter-block col-sm-2">
        <div class="card" data-type="highstock_area_spline_chart" data-min_columns="2" data-max_columns="0">
            <div class="card-header">
                <div class="wdt-chart-wizard-overlay"></div>
                <img class="img-responsive"
                     src="<?php echo WDT_HS_ASSETS_URL ?>img/highstock-area-spline-chart.png">
            </div>
            <div class="card-body">
                <h4 class="f-14"><?php esc_html_e('Area spline chart', 'wpdatatables'); ?></h4>
            </div>
        </div>
    </div>

    <div class="wdt-chart-wizard-chart-selecter-block col-sm-2">
        <div class="card" data-type="highstock_area_range_chart" data-min_columns="3" data-max_columns="3">
            <div class="card-header">
                <div class="wdt-chart-wizard-overlay"></div>
                <img class="img-responsive"
                     src="<?php echo WDT_HS_ASSETS_URL ?>img/highstock-area-range-chart.png">
            </div>
            <div class="card-body">
                <h4 class="f-14"><?php esc_html_e('Area range chart', 'wpdatatables'); ?></h4>
            </div>
        </div>
    </div>

    <div class="wdt-chart-wizard-chart-selecter-block col-sm-2">
        <div class="card" data-type="highstock_area_spline_range_chart" data-min_columns="3" data-max_columns="3">
            <div class="card-header">
                <div class="wdt-chart-wizard-overlay"></div>
                <img class="img-responsive"
                     src="<?php echo WDT_HS_ASSETS_URL ?>img/highstock-area-spline-range-chart.png">
            </div>
            <div class="card-body">
                <h4 class="f-14"><?php esc_html_e('Area spline range chart', 'wpdatatables'); ?></h4>
            </div>
        </div>
    </div>

    <div class="wdt-chart-wizard-chart-selecter-block col-sm-2">
        <div class="card" data-type="highstock_column_chart" data-min_columns="2" data-max_columns="0">
            <div class="card-header">
                <div class="wdt-chart-wizard-overlay"></div>
                <img class="img-responsive"
                     src="<?php echo WDT_HS_ASSETS_URL ?>img/highstock-column-chart.png">
            </div>
            <div class="card-body">
                <h4 class="f-14"><?php esc_html_e('Column chart', 'wpdatatables'); ?></h4>
            </div>
        </div>
    </div>

    <div class="wdt-chart-wizard-chart-selecter-block col-sm-2">
        <div class="card" data-type="highstock_column_range_chart" data-min_columns="3" data-max_columns="3">
            <div class="card-header">
                <div class="wdt-chart-wizard-overlay"></div>
                <img class="img-responsive"
                     src="<?php echo WDT_HS_ASSETS_URL ?>img/highstock-column-range-chart.png">
            </div>
            <div class="card-body">
                <h4 class="f-14"><?php esc_html_e('Column range chart', 'wpdatatables'); ?></h4>
            </div>
        </div>
    </div>

    <div class="wdt-chart-wizard-chart-selecter-block col-sm-2">
        <div class="card" data-type="highstock_candlestick_chart" data-min_columns="5" data-max_columns="5">
            <div class="card-header">
                <div class="wdt-chart-wizard-overlay"></div>
                <img class="img-responsive"
                     src="<?php echo WDT_HS_ASSETS_URL ?>img/highstock-candlestick-chart.png">
            </div>
            <div class="card-body">
                <h4 class="f-14"><?php esc_html_e('Candlestick chart', 'wpdatatables'); ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="wdt-chart-wizard-chart-selecter-block col-sm-2">
        <div class="card" data-type="highstock_hlc_chart" data-min_columns="5" data-max_columns="5">
            <div class="card-header">
                <div class="wdt-chart-wizard-overlay"></div>
                <img class="img-responsive"
                     src="<?php echo WDT_HS_ASSETS_URL ?>img/highstock-hlc-chart.png">
            </div>
            <div class="card-body">
                <h4 class="f-14"><?php esc_html_e('HLC chart', 'wpdatatables'); ?></h4>
            </div>
        </div>
    </div>

    <div class="wdt-chart-wizard-chart-selecter-block col-sm-2">
        <div class="card" data-type="highstock_ohlc_chart" data-min_columns="5" data-max_columns="5">
            <div class="card-header">
                <div class="wdt-chart-wizard-overlay"></div>
                <img class="img-responsive"
                     src="<?php echo WDT_HS_ASSETS_URL ?>img/highstock-ohlc-chart.png">
            </div>
            <div class="card-body">
                <h4 class="f-14"><?php esc_html_e('OHLC chart', 'wpdatatables'); ?></h4>
            </div>
        </div>
    </div>
</div>

</div>
<!-- /div.highstock-charts-type -->