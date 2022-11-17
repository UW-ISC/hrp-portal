<?php defined('ABSPATH') or die('Access denied.');
global $wpdb;
?>

<div role="tabpanel" class="tab-pane" id="cache-settings">

    <div class="row">
        <div class="col-sm-6 auto-update-cache-option">
            <h5 class="c-title-color m-b-2">
                <?php esc_html_e('Auto update cache for tables', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('When this is turned on along with the table option "Cache table", each non-serverside datatable (created from Excel, CSV, XML, JSON, Nested JSON, Google Spreadsheet or PHP array)  will have a new Auto update cache option which you can turn on for the datatables that you need. By default, it will be turned off.', 'wpdatatables'); ?>"></i>
            </h5>
            <div class="toggle-switch" data-ts-color="blue">
                <input type="checkbox" name="wdt-auto-update-option" id="wdt-auto-update-option"/>
                <label for="wdt-auto-update-option" class="ts-label"><?php esc_html_e('Enable for all non-server side tables', 'wpdatatables'); ?></label>
            </div>
        </div>
    </div>
    <div class="row auto-update-cache-instructions hidden">
        <div class="col-sm-12">
            <div class="alert alert-info alert-dismissible m-t-20" role="alert">
                <i class="wpdt-icon-info-circle-full"></i>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true"><i
                                class="wpdt-icon-times-full"></i></span></button>
                <span class="wdt-alert-title f-600">
                       <?php esc_html_e('Detailed instruction on how to set cache and auto update cache can be found on this', 'wpdatatables'); ?>
                        <a href="https://wpdatatables.com/documentation/table-features/caching-data-and-auto-update-cache-in-wordpress-tables/" target="_blank"><?php esc_html_e('link', 'wpdatatables'); ?></a>.
                    </span>
                <span class="wdt-alert-subtitle m-t-20">
                    <p>First, you will need to create a non-serverside table (created from Excel, CSV, XML, JSON, Nested JSON, Google Spreadsheet or PHP array). In the <strong>"Data source"</strong> tab in table settings you will find a new option <strong>"Cache Data"</strong>. When you turn it on, another new option <strong>"Auto update cache"</strong> will be shown. If this option is turned on, you will need to set the cron as explained below.</p>
                    <p>Auto update cache for tables requires a bit of additional configuration with your server cron scheduler. If you want to run a cron (do auto update cache) every 15 minutes, copy the command below and paste it into the cron file.</p>
                    <p class="m-l-20"><strong>*/15 * * * * <?php echo get_site_url(); ?>/wp-admin/admin-ajax.php?action=wdtable_update_cache&wdtable_cache_verify=<?php echo get_option('wdtAutoUpdateHash'); ?></strong></p>
                    <p>If that doesn't work, please try these as well:</p>
                    <p class="m-l-20"><strong>*/15 * * * * wget -q -O – "<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php?action=wdtable_update_cache&wdtable_cache_verify=<?php echo get_option('wdtAutoUpdateHash'); ?>"</strong></p>
                    <p class="m-l-20"><strong>*/15 * * * * /usr/local/bin/php ~/public_html/wp-cron.php –action='wdtable_update_cache' –wdtable_cache_verify='<?php echo get_option('wdtAutoUpdateHash'); ?>'</strong></p>
                    <p class="m-l-20"><strong>GET '<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php?action=wdtable_update_cache&wdtable_cache_verify=<?php echo get_option('wdtAutoUpdateHash'); ?>' > /dev/null</strong></p>
                    <p>If you need a different schedule time, you can see a more in–depth tutorial on how to set up cron jobs on this <a href="https://www.taniarascia.com/setting-up-a-basic-cron-job-in-linux/" target="_blank">link</a>.</p>
                    <p>If you are not familiar with this, you can use the plugin <a href="https://wordpress.org/plugins/wp-crontrol/" target="_blank">WP Crontrol</a> for creating a cronjob.</p>
                    <p>First, copy this: <?php echo get_site_url(); ?>/wp-admin/admin-ajax.php?action=wdtable_update_cache&wdtable_cache_verify=<?php echo get_option('wdtAutoUpdateHash'); ?></p>
                    <p>Then, go to <strong>Tools -> Cron Events</strong> admin panel, and click on the <strong>“Cron Schedules”</strong> tab. Add a 15-minute interval in this tab.</p>
                    <p>Now, go back to the <strong>“Cron Events”</strong> tab, and click on <strong>“Add New”</strong>.</p>
                    <p>Below it, select the <strong>“PHP Cron event”</strong> radio button, and add the URL you copied:</p>
                    <p class="m-l-20"><strong>file_get_contents("<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php?action=wdtable_update_cache&wdtable_cache_verify=<?php echo get_option('wdtAutoUpdateHash'); ?>");</strong></p>
                    <p>You don’t need to provide the PHP opening tag ("&lt;?php").</p>
                    <p>Set the <strong>Event name</strong>, and set the next run to be <strong>“At”</strong>, and a few minutes in the future. Select the recurrence (the event you've created in the first step, in WP Crontrol – the “every_15_minutes” interval) and that’s it.</p>
                    </span>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="alert alert-info alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true"><i
                                class="wpdt-icon-times-full"></i></span></button>
                <span class="wdt-alert-subtitle">
                       <p>If you use "Auto update cache for tables" option and your table cache is not updated, please follow these instructions for troubleshooting:</p>
                        <ul class="m-l-20" style="list-style: disc;">
                             <li>First, check if both options <strong>"Cache table"</strong> and <strong>"Auto update cache"</strong> in your table are turned on.</li>
                            <li>Then, you can run this URL manually in your browser:</li>
                            <strong><?php echo get_site_url(); ?>/wp-admin/admin-ajax.php?action=wdtable_update_cache&wdtable_cache_verify=<?php echo get_option('wdtAutoUpdateHash'); ?></strong>
                            <br>to check if auto update is working.
                            <li>If the data in your table is updated, check if your cron job is set properly with your hosting provider.</li>
                            <li>When that is set, you need to check if there are any errors after cron execution.</li>
                            <li>You can check out the error log on your server or check for errors in our table.</li>
                            <li>Go to WordPress admin, and open <strong>wpDataTables -> Create a table</strong></li>
                            <li>Click on <strong>Create a table linked to an existing data source</strong></li>
                            <li>Select <strong>SQL query</strong> in the "Input data source type" select-box</li>
                            <li>Paste the SQL query: <strong>SELECT table_id, updated_time, log_errors FROM <?php echo $wpdb->prefix;?>wpdatatables_cache</strong></li>
                            <li>Click <strong>"Save Changes"</strong> so wpDataTables could read the table structure.</li>
                            <li>Check the log_errors column to see when an error happened and the cause of the error.</li>
                            <li>If you are facing some issues, please export this table in CSV and provide it to our support agents on our support platform, where you can contact us using this
                                <a href="https://tmsplugins.ticksy.com/" target="_blank"><?php esc_html_e('link', 'wpdatatables'); ?></a>.
                            </li>
                        </ul>
                    </span>

            </div>
        </div>
        <div class="col-sm-12">
            <button id="wdt-delete-log-errors-cache" class="btn btn-danger">
                <?php esc_html_e('Delete errors log', 'wpdatatables'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Error modal -->
<?php include WDT_TEMPLATE_PATH . 'admin/common/error_modal.inc.php'; ?>
<!-- /Error modal -->