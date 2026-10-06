<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="col-sm-12 hidden mysql-settings-block">
    <div class="row">
        <div class="col-sm-6 wdt-mysql-query-editor-col">
            <h4 class="c-title-color m-b-2">
                <?php esc_html_e('SQL Query', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('Enter the text of your SQL query here - please make sure it returns actual data first. You can use a number of placeholders to make the dataset in the table flexible and be able to return different sets of data by calling it with different shortcodes.', 'wpdatatables'); ?>"></i>
                <div class="" data-placement="top"
                     style="color: gray; float: right;"><?php echo Connection::enabledSeparate() ? Connection::getName($connection) : '' ?>
                </div>
            </h4>
            <pre id="wdt-mysql-query" style="width: 100%; height: 250px"></pre>
        </div>
        <?php
        /**
         * Fires as the right column beside the Ace SQL editor — AI SQL Assistant (Feature 3).
         *
         * @since AI MVP
         */
        do_action('wpdatatables_ai_sql_assistant');
        ?>
    </div>
</div>
