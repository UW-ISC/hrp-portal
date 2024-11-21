<?php defined('ABSPATH') or die('Access denied.'); ?>

<div role="tabpanel" class="tab-pane" id="separate-connection" data-count="">
    <div class="row">
        <div class="separate-conn-not-available-notice"
             style="max-width: 1440px;margin: 0 auto;padding: 10px;text-align: center;">
            <h4 class="f-14">
                <i class="wpdt-icon-star-full m-r-5" style="color: #091D70;"></i>
                <?php esc_html_e('Available from Standard licence', 'wpdatatables'); ?></h4>
            <p class="m-b-0"><?php esc_html_e('Add more than one separate database connection. Now every table can have its own separate database connection, so tables can pull data from multiple databases and servers.', 'wpdatatables'); ?></p>
            <p><?php esc_html_e('There are separate database connections for MySQL, MS SQL and PostgreSQL databases.', 'wpdatatables'); ?></p>
            <a rel="nofollow" target="_blank" class="btn btn-primary wdt-upgrade-btn m-b-20"
               href="https://wpdatatables.com/pricing/?utm_source=wpdt-admin&utm_medium=separate-connection&utm_campaign=wpdt&utm_content=wpdt"> <?php esc_html_e('Upgrade', 'wpdatatables'); ?></a>
            <img class="wdt-upgrade-img"
                 src="<?php echo WDT_ASSETS_PATH ?>img/feature-preview-notice/starter/separate-db-connection.gif"
                 alt="wpDataTables Separate connection preview">
        </div>
    </div>
</div>


