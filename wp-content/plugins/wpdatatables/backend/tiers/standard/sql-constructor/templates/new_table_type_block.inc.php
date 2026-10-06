<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="row wpdt-flex wdt-third-row">
    <div class="wdt-constructor-type-selecter-block col-sm-6">
        <div class="card" data-value="wp">
            <div class="card-header">
                <img class="img-responsive"
                     src="<?php echo WDT_ASSETS_PATH ?>img/constructor/generate-query-to-wordpress-database.png">
            </div>
            <div class="card-body p-b-20 p-r-20 p-t-20">
                <h4 class="m-t-0 m-b-8 f-14"><?php esc_html_e('Generate a query to the WordPress database', 'wpdatatables'); ?>.</h4>
                <span><?php esc_html_e('Create a MySQL-query-based data table by generating a query to the WordPress database (posts, taxonomies, postmeta) with a GUI tool.', 'wpdatatables'); ?></span>
            </div>
        </div>
    </div>
    <div class="wdt-constructor-type-selecter-block col-sm-6">
        <div class="card" data-value="mysql">
            <div class="card-header">
                <img class="img-responsive"
                     src="<?php echo WDT_ASSETS_PATH ?>img/constructor/generate-query-to-mysql-database.png">
            </div>
            <div class="card-body p-b-20 p-r-20 p-t-20">
                <h4 class="m-t-0 m-b-8 f-14"><?php esc_html_e('Generate a query to the MySQL database', 'wpdatatables'); ?>.</h4>
                <span><?php esc_html_e('Create a SQL-query-based data table by generating a query to any custom SQL database with a GUI tool.', 'wpdatatables'); ?></span>
            </div>
        </div>
    </div>
</div>