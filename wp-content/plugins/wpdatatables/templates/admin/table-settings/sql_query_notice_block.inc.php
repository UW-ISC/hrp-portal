<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="wdt-mysql-query-notice-block hidden">

    <div class="col-sm-6 text-center p-relative" style="height: 500px">
        <div style="position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%);">
            <i class="wpdt-icon-star-full m-r-5"
               style="color: #091D70;"></i><strong><?php esc_html_e('Available from Standard license', 'wpdatatables'); ?></strong>

            <p class="m-b-0 m-t-10"><?php esc_html_e('wpDataTables is deeply integrated with the MySQL engine. 
            It allows you to display results of SQL query in a table, you can use MySQL server to do searches/filters/sorting/pagination for large datasets,
             it allows front-end (and back-end) editing of MySQL tables, creating MySQL tables from back-end, and much more.', 'wpdatatables'); ?></p>
            <p class="p-t-10">
                <a href="https://wpdatatables.com/pricing/?utm_source=wpdt-admin&utm_medium=sql-query&utm_campaign=wpdt&utm_content=wpdt"
                   rel="nofollow" target="_blank" class="btn btn-primary wdt-upgrade-btn">
                    <?php esc_html_e('Upgrade', 'wpdatatables'); ?>
                </a>
            </p>
        </div>
    </div>
    <div class="col-sm-6  notice-images">
        <div class="wpdt-custom-center-flex" style="align-items: center;">
            <img style="height: 500px;margin-right: 10px; width: 99%;"
                 src="<?php echo WDT_ASSETS_PATH ?>img/feature-preview-notice/starter/mysql-query.gif"
                 alt="SQL query image notice"/>
        </div>
    </div>
</div>
