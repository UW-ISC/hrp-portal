<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="col p-0 wdt-constructor-step wdt-constructor-query-data-step hidden bg-white" data-step="1-5">
    <div class="row">
        <div class="col-sm-6 wdt-constructor-wp-posts-table-name-block">
            <h4 class="c-title-color m-b-2">
                <?php esc_html_e('Table name', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('What is the header of the table that will be visible to the site visitors?', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <label for="wdt-constructor-wp-posts-table-name"></label>
                    <input type="text" class="form-control input-sm" value="New wpDataTable"
                           id="wdt-constructor-wp-posts-table-name">
                </div>
            </div>
        </div>
        <div class="col-sm-6 wdt-constructor-wp-query-table-description-block">
            <h4 class="c-title-color m-b-2">
                <?php esc_html_e('Table description', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('What is the description of the table? (optional)', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <label for="wdt-constructor-wp-posts-table-description"></label>
                    <textarea class="form-control" value="" id="wdt-constructor-wp-posts-table-description"
                              placeholder="<?php esc_attr_e('Insert description of your wpDataTable', 'wpdatatables'); ?>"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="card-body bg-white">
            <?php include WDT_TEMPLATE_PATH . 'admin/constructor/steps/query_builder_parts/constructor_2_posts.inc.php'; ?>

            <?php include WDT_TEMPLATE_PATH . 'admin/constructor/steps/query_builder_parts/query_preview.inc.php'; ?>
        </div>
    </div>
</div>