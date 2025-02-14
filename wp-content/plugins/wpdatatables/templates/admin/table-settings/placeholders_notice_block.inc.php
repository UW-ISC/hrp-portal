<?php defined('ABSPATH') or die('Access denied.'); ?>

<!--Editing notice settings -->
<div class="row text-center">
    <i class="wpdt-icon-star-full m-r-5"
       style="color: #091D70;"></i><strong><?php esc_html_e('Available from Standard license', 'wpdatatables'); ?></strong>

    <p class="m-b-0 m-t-10"><?php esc_html_e('Placeholders act as predefined templates for \'search and replace,\' dynamically 
    substituted with actual values during execution,', 'wpdatatables'); ?>
    </p>
    <p class="m-b-0"><?php esc_html_e(' commonly utilized in MySQL queries but extendable to filtering and editing manual tables, 
    and exclusively for filtering in tables derived from XML, JSON, Excel, CSV, Google Spreadsheet, and PHP Serialized array.', 'wpdatatables'); ?></p>
    <p class="p-t-10">
        <a href="https://wpdatatables.com/pricing/?utm_source=wpdt-admin&utm_medium=placeholders&utm_campaign=wpdt&utm_content=wpdt"
           rel="nofollow" class="btn btn-primary wdt-upgrade-btn"
           target="_blank"><?php esc_html_e('Upgrade', 'wpdatatables'); ?></a>
    </p>
</div>
<div class="row notice-images">
    <div class="wpdt-custom-center-flex" style="align-items: center;">
        <img style="height: 300px;margin-right: 10px;"
             src="<?php echo WDT_ASSETS_PATH ?>img/feature-preview-notice/starter/placeholders-editing.gif"
             alt="Placeholders editing image notice"/>
        <img style="height: 300px;margin-right: 10px;"
             src="<?php echo WDT_ASSETS_PATH ?>img/feature-preview-notice/starter/placeholders-query.gif"
             alt="Placeholders query image notice"/>
        <img style="height: 300px;margin-right: 10px;"
             src="<?php echo WDT_ASSETS_PATH ?>img/feature-preview-notice/starter/placeholders-filters.gif"
             alt="Placeholders filtering image notice"/>
    </div>
</div>
<!--Editing notice settings -->