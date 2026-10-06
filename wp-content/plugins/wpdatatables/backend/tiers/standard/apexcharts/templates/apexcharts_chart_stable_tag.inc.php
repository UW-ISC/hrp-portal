<?php defined('ABSPATH') or die('Access denied.'); ?>

<div id="wdt-apexcharts-stable-tag" class="col-sm-4 stable-tag">
    <h4 class="c-title-color m-b-2">
        <?php esc_html_e('Use stable ApexChart version', 'wpdatatables'); ?>
        <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
           title="<?php esc_attr_e('Choose weather to use the chart engine library directly from the CDN (as they get updated, some features may break), or use the latest version wpDataTables has been tested with. Leaving this option unchecked means the code is pulled from the CDN.', 'wpdatatables'); ?>"></i>
    </h4>

    <div class="fg-line">
        <div class="toggle-switch" data-ts-color="blue">
            <input id="wdt-use-apexcharts-stable-version" type="checkbox">
            <label for="wdt-use-apexcharts-stable-version"
                   class="ts-label form-control"><?php esc_html_e('Use stable version', 'wpdatatables'); ?></label>
        </div>
    </div>
</div>