<?php defined('ABSPATH') or die('Access denied.'); ?>

<div id="wdt-highcharts-stable-tag" class="col-sm-3 stable-tag">
    <h4 class="c-title-color m-b-2">
        <?php esc_html_e('Use stable HighChart version', 'wpdatatables'); ?>
        <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
           title="<?php esc_attr_e('Choose weather to use the chart engine library directly from the CDN (as they get updated, some features may break), or use the latest version wpDataTables has been tested with. Leaving this option unchecked means the code is pulled from the CDN.', 'wpdatatables'); ?>"></i>
    </h4>

    <div class="fg-line">
        <div class="toggle-switch" data-ts-color="blue">
            <label for="wdt-use-highcharts-stable-version" class="ts-label">
                <i class='wpdt-icon-star-full m-r-5'
                   style='color: #091d70;'></i>
                <span class="opacity-6">
                    <?php esc_html_e('Use stable version', 'wpdatatables'); ?>
                </span>
            </label>
        </div>
    </div>
</div>

<div id="wdt-apexcharts-stable-tag" class="col-sm-3 stable-tag">
    <h4 class="c-title-color m-b-2">
        <?php esc_html_e('Use stable ApexChart version', 'wpdatatables'); ?>
        <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
           title="<?php esc_attr_e('Choose weather to use the chart engine library directly from the CDN (as they get updated, some features may break), or use the latest version wpDataTables has been tested with. Leaving this option unchecked means the code is pulled from the CDN.', 'wpdatatables'); ?>"></i>
    </h4>

    <div class="fg-line">
        <div class="toggle-switch" data-ts-color="blue">
            <label for="wdt-use-apexcharts-stable-version" class="ts-label">
                <i class='wpdt-icon-star-full m-r-5'
                   style='color: #091d70;'></i>
                <span class="opacity-6">
                    <?php esc_html_e('Use stable version', 'wpdatatables'); ?>
                </span>
            </label>
        </div>
    </div>
</div>

<div class="col-sm-2 m-b-16">
    <i class='wpdt-icon-star-full m-r-5' style='color: #091D70;'></i>
    <h4 class="c-title-color m-b-2 opacity-6" style="display: inline-block">
        <?php esc_html_e('Available from Standard licence', 'wpdatatables'); ?>
        <i style="line-height: 20px;"></i>
    </h4>
    <div class="fg-line">
        <a class="btn btn-primary wdt-upgrade-btn di-block w-100"
           href="https://wpdatatables.com/pricing/?utm_source=wpdt-admin&utm_medium=charts-stable-tag&utm_campaign=wpdt&utm_content=wpdt"
           rel="nofollow" target="_blank"><?php esc_html_e('Upgrade', 'wpdatatables'); ?></a>
    </div>
</div>
