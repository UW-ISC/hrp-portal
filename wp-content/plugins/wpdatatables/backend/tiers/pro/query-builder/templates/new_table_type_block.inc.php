<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="row wpdt-flex wdt-fourth-row">
    <div class="wdt-constructor-type-selecter-block col-sm-6">
        <div class="card" data-value="wp_posts_query">
            <div class="ribbon"><span>NEW</span></div>
            <div class="card-header">
                <img class="img-responsive"
                     src="<?php echo WDT_ASSETS_PATH ?>img/constructor/wp-query-builder-table.png">
            </div>
            <div class="card-body p-b-20 p-r-20 p-t-20">
                <h4 class="m-t-0 m-b-8 f-14"><?php esc_html_e('Build a WP Post Table', 'wpdatatables'); ?>.</h4>
                <span><?php esc_html_e('Automatically create a data table based on WordPress posts. Configure the columns to display post titles, authors, dates, and other post details, with data dynamically pulled from your site\'s content.', 'wpdatatables'); ?></span>
            </div>
        </div>
    </div>
    <?php echo $newTableType; ?>
</div>