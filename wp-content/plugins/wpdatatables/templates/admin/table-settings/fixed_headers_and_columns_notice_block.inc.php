<?php defined('ABSPATH') or die('Access denied.'); ?>

<!--Advanced table settings -->
<div role="tabpanel" class="tab-pane fade" id="advanced-table-settings">
    <div class="row text-center">
        <i class="wpdt-icon-star-full m-r-5"
           style="color: #091D70;"></i><strong><?php esc_html_e('Available from Standard license', 'wpdatatables'); ?></strong>

        <p class="m-b-0 m-t-10"><?php esc_html_e('Fixed headers and columns enhance user experience by ensuring that column and row labels remain visible while scrolling, facilitating easier data interpretation and reference. ', 'wpdatatables'); ?></p>
        <p><?php esc_html_e('This features improves usability, especially for large datasets, by providing constant navigation points without the need for manual scrolling back to the top or side of the table.', 'wpdatatables'); ?></p>
        <p class="p-t-10">
            <a href="https://wpdatatables.com/pricing/?utm_source=wpdt-admin&utm_medium=fixed-column-and-headers&utm_campaign=wpdt&utm_content=wpdt"
               rel="nofollow" target="_blank"
               class="btn btn-primary wdt-upgrade-btn"><?php esc_html_e('Upgrade', 'wpdatatables'); ?></a>
        </p>
    </div>
    <div class="row">
        <div class="wpdt-custom-center-flex" style="align-items: center;">
            <img src="<?php echo WDT_ASSETS_PATH ?>img/feature-preview-notice/starter/fixed-columns-preview.gif"
                 alt="Fixed columns"/>
            <img src="<?php echo WDT_ASSETS_PATH ?>img/feature-preview-notice/starter/fixed-header-preview.gif"
                 alt="Fixed header"/>
        </div>
    </div>
</div>
<!-- /Advanced table settings -->