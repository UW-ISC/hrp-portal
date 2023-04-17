<?php

defined('ABSPATH') or die('Access denied.');

/**
 * Main wpDataTables functions
 * @package wpDataTables
 * @since 1.6.0
 */
?>
<?php

global $wp_version;

/**
 * The installation/activation method, installs the plugin table
 */
function wdtActivationCreateTables() {
    global $wpdb;

    $connection = Connection::enabledSeparate() ? 'abcdefghijk' : '';

    $tablesTableName = $wpdb->prefix . 'wpdatatables';
    $tablesSql = "CREATE TABLE {$tablesTableName} (
						id bigint(20) NOT NULL AUTO_INCREMENT,
						title varchar(255) NOT NULL,
                        show_title tinyint(1) NOT NULL default '1',
						table_type varchar(55) NOT NULL,
						file_location varchar(15) NOT NULL default '',
						connection varchar(55) NOT NULL DEFAULT '$connection',
						content text NOT NULL,
						filtering tinyint(1) NOT NULL default '1',
						filtering_form tinyint(1) NOT NULL default '0',
						cache_source_data tinyint(1) NOT NULL default '0',
						auto_update_cache tinyint(1) NOT NULL default '0',
						sorting tinyint(1) NOT NULL default '1',
						tools tinyint(1) NOT NULL default '1',
						server_side tinyint(1) NOT NULL default '0',
						editable tinyint(1) NOT NULL default '0',
						inline_editing tinyint(1) NOT NULL default '0',
						popover_tools tinyint(1) NOT NULL default '0',
						editor_roles varchar(255) NOT NULL default '',
						mysql_table_name text NOT NULL default '',
                        edit_only_own_rows tinyint(1) NOT NULL default 0,
                        userid_column_id int( 11 ) NOT NULL default 0,
						display_length int(3) NOT NULL default '10',
                        auto_refresh int(3) NOT NULL default 0,
						fixed_columns tinyint(1) NOT NULL default '-1',
						fixed_layout tinyint(1) NOT NULL default '0',
						responsive tinyint(1) NOT NULL default '0',
						scrollable tinyint(1) NOT NULL default '0',
						word_wrap tinyint(1) NOT NULL default '0',
						hide_before_load tinyint(1) NOT NULL default '0',
                        var1 VARCHAR( 255 ) NOT NULL default '',
                        var2 VARCHAR( 255 ) NOT NULL default '',
                        var3 VARCHAR( 255 ) NOT NULL default '',
                        var4 VARCHAR( 255 ) NOT NULL default '',
                        var5 VARCHAR( 255 ) NOT NULL default '',
                        var6 VARCHAR( 255 ) NOT NULL default '',
                        var7 VARCHAR( 255 ) NOT NULL default '',
                        var8 VARCHAR( 255 ) NOT NULL default '',
                        var9 VARCHAR( 255 ) NOT NULL default '',
                        tabletools_config VARCHAR( 255 ) NOT NULL default '',
						advanced_settings TEXT NOT NULL default '',
						UNIQUE KEY id (id)
						) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";

    $columnsTableName = $wpdb->prefix . 'wpdatatables_columns';
    $columnsSql = "CREATE TABLE {$columnsTableName} (
						id bigint(20) NOT NULL AUTO_INCREMENT,
						table_id bigint(20) NOT NULL,
						orig_header varchar(255) NOT NULL,
						display_header varchar(255) NOT NULL,
						filter_type enum('none','null_str','text','number','number-range','date-range','datetime-range','time-range','select','multiselect','checkbox') NOT NULL,
						column_type enum('autodetect','string','int','float','date','link','email','image','formula','datetime','time','masterdetail') NOT NULL,
						input_type enum('none','text','textarea','mce-editor','date','datetime','time','link','email','selectbox','multi-selectbox','attachment') NOT NULL default 'text',
						input_mandatory tinyint(1) NOT NULL default '0',
                        id_column tinyint(1) NOT NULL default '0',
						group_column tinyint(1) NOT NULL default '0',
						sort_column tinyint(1) NOT NULL default '0',
						hide_on_phones tinyint(1) NOT NULL default '0',
						hide_on_tablets tinyint(1) NOT NULL default '0',
						visible tinyint(1) NOT NULL default '1',
						sum_column tinyint(1) NOT NULL default '0',
						skip_thousands_separator tinyint(1) NOT NULL default '0',
						width VARCHAR( 4 ) NOT NULL default '',
						possible_values TEXT NOT NULL default '',
						default_value TEXT NOT NULL default '',
						css_class VARCHAR(255) NOT NULL default '',
						text_before VARCHAR(255) NOT NULL default '',
						text_after VARCHAR(255) NOT NULL default '',
                        formatting_rules TEXT NOT NULL default '',
                        calc_formula TEXT NOT NULL default '',
						color VARCHAR(255) NOT NULL default '',
						advanced_settings TEXT NOT NULL default '',
						pos int(11) NOT NULL default '0',
						UNIQUE KEY id (id)
						) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    $chartsTableName = $wpdb->prefix . 'wpdatacharts';
    $chartsSql = "CREATE TABLE {$chartsTableName} (
                                  id bigint(20) NOT NULL AUTO_INCREMENT,
                                  wpdatatable_id bigint(20) NOT NULL,
                                  title varchar(255) NOT NULL,
                                  engine enum('google','highcharts','chartjs','apexcharts') NOT NULL,
                                  type varchar(255) NOT NULL,
                                  json_render_data text NOT NULL,
                                  UNIQUE KEY id (id)
                                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    $rowsTableName = $wpdb->prefix . 'wpdatatables_rows';
    $rowsSql = "CREATE TABLE {$rowsTableName} (
                                  id bigint(20) NOT NULL AUTO_INCREMENT,
                                  table_id bigint(20) NOT NULL,
                                  data TEXT NOT NULL default '',
                                  UNIQUE KEY id (id)
                                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    $cacheTableName = $wpdb->prefix . 'wpdatatables_cache';
    $cacheSql = "CREATE TABLE {$cacheTableName} (
                                  id bigint(20) NOT NULL AUTO_INCREMENT,
                                  table_id bigint(20) NOT NULL,
                                  table_type varchar(55) NOT NULL default '',
                                  table_content text NOT NULL default '',
                                  auto_update tinyint(1) NOT NULL default 0,
                                  updated_time TIMESTAMP NOT NULL default CURRENT_TIMESTAMP,
                                  data LONGTEXT NOT NULL default '',
                                  log_errors text NOT NULL default '',
                                  UNIQUE KEY id (id)
                                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($tablesSql);
    dbDelta($columnsSql);
    dbDelta($chartsSql);
    dbDelta($rowsSql);
    dbDelta($cacheSql);
    if (!get_option('wdtUseSeparateCon')) {
        update_option('wdtUseSeparateCon', false);
    }
    if (!get_option('wdtSeparateCon')) {
        update_option('wdtSeparateCon', false);
    }
    if (!get_option('wdtRenderCharts')) {
        update_option('wdtRenderCharts', 'below');
    }
    if (!get_option('wdtRenderFilter')) {
        update_option('wdtRenderFilter', 'footer');
    }
    if (!get_option('wdtRenderFilter')) {
        update_option('wdtTopOffset', '0');
    }
    if (!get_option('wdtLeftOffset')) {
        update_option('wdtLeftOffset', '0');
    }
    if (!get_option('wdtBaseSkin')) {
        update_option('wdtBaseSkin', 'light');
    }
    if (get_option('wdtBaseSkin') && get_option('wdtBaseSkin') == 'skin0') {
        update_option('wdtBaseSkin', 'material');
    }
    if (get_option('wdtBaseSkin') && get_option('wdtBaseSkin') == 'skin1') {
        update_option('wdtBaseSkin', 'light');
    }
    if (get_option('wdtBaseSkin') && get_option('wdtBaseSkin') == 'skin2') {
        update_option('wdtBaseSkin', 'graphite');
    }
    if (!get_option('wdtTimeFormat')) {
        update_option('wdtTimeFormat', 'h:i A');
    }
    if (!get_option('wdtTimeFormat')) {
        update_option('wdtTimeFormat', 'h:i A');
    }
    if (!get_option('wdtInterfaceLanguage')) {
        update_option('wdtInterfaceLanguage', '');
    }
    if (!get_option('wdtTablesPerPage')) {
        update_option('wdtTablesPerPage', 10);
    }
    if (!get_option('wdtNumberFormat')) {
        update_option('wdtNumberFormat', 1);
    }
    if (!get_option('wdtDecimalPlaces')) {
        update_option('wdtDecimalPlaces', 2);
    }
    if (!get_option('wdtCSVDelimiter')) {
        update_option('wdtCSVDelimiter', ',');
    }
    if (!get_option('wdtSortingOrderBrowseTables')) {
        update_option('wdtSortingOrderBrowseTables', 'ASC');
    }
    if (!get_option('wdtDateFormat')) {
        update_option('wdtDateFormat', 'd/m/Y');
    }
    if (get_option('wdtAutoUpdateOption') === false) {
        update_option('wdtAutoUpdateOption', 0);
    }
    if (get_option('wdtParseShortcodes') === false) {
        update_option('wdtParseShortcodes', false);
    }
    if (get_option('wdtNumbersAlign') === false) {
        update_option('wdtNumbersAlign', true);
    }
    if (get_option('wdtBorderRemoval') === false) {
        update_option('wdtBorderRemoval', 0);
    }
    if (get_option('wdtBorderRemovalHeader') === false) {
        update_option('wdtBorderRemovalHeader', 0);
    }
    if (!get_option('wdtFontColorSettings')) {
        update_option('wdtFontColorSettings', '');
    }
    if (!get_option('wdtCustomJs')) {
        update_option('wdtCustomJs', '');
    }
    if (!get_option('wdtCustomCss')) {
        update_option('wdtCustomCss', '');
    }
    if (!get_option('wdtGoogleSettings')) {
        update_option('wdtGoogleSettings', '');
    }
    if (!get_option('wdtGoogleToken')) {
        update_option('wdtGoogleToken', '');
    }
    if (get_option('wdtMinifiedJs') === false) {
        update_option('wdtMinifiedJs', 1);
    }
    if (!get_option('wdtTabletWidth')) {
        update_option('wdtTabletWidth', 1024);
    }
    if (!get_option('wdtMobileWidth')) {
        update_option('wdtMobileWidth', 480);
    }
    if (get_option('wdtGettingStartedPageStatus') === false) {
        update_option('wdtGettingStartedPageStatus', 0 );
    }
    if (get_option('wdtLiteVSPremiumPageStatus') === false) {
        update_option('wdtLiteVSPremiumPageStatus', 0 );
    }
    if (get_option('wdtIncludeGoogleFonts') === false) {
        update_option('wdtIncludeGoogleFonts', true );
    }
    if (get_option('wdtIncludeBootstrap') === false) {
        update_option('wdtIncludeBootstrap', true);
    }
    if (get_option('wdtIncludeBootstrapBackEnd') === false) {
        update_option('wdtIncludeBootstrapBackEnd', true);
    }
    if (get_option('wdtPreventDeletingTables') === false) {
        update_option('wdtPreventDeletingTables', true);
    }
    if (!get_option('wdtActivated')) {
        update_option('wdtActivated', 0);
    }
    if (!get_option('wdtPurchaseCodeStore')) {
        update_option('wdtPurchaseCodeStore', '');
    }
    if (!get_option('wdtEnvatoTokenEmail')) {
        update_option('wdtEnvatoTokenEmail', '');
    }
    if (!get_option('wdtActivatedPowerful')) {
        update_option('wdtActivatedPowerful', 0);
    }
    if (!get_option('wdtPurchaseCodeStorePowerful')) {
        update_option('wdtPurchaseCodeStorePowerful', '');
    }
    if (!get_option('wdtEnvatoTokenEmailPowerful')) {
        update_option('wdtEnvatoTokenEmailPowerful', '');
    }
    if (!get_option('wdtActivatedReport')) {
        update_option('wdtActivatedReport', 0);
    }
    if (!get_option('wdtActivatedMasterDetail')) {
        update_option('wdtActivatedMasterDetail', 0);
    }
    if (!get_option('wdtPurchaseCodeStoreMasterDetail')) {
        update_option('wdtPurchaseCodeStoreMasterDetail', '');
    }
    if (!get_option('wdtPurchaseCodeStoreReport')) {
        update_option('wdtPurchaseCodeStoreReport', '');
    }
    if (!get_option('wdtEnvatoTokenEmailReport')) {
        update_option('wdtEnvatoTokenEmailReport', '');
    }
    if (!get_option('wdtActivatedGravity')) {
        update_option('wdtActivatedGravity', 0);
    }
    if (!get_option('wdtPurchaseCodeStoreGravity')) {
        update_option('wdtPurchaseCodeStoreGravity', '');
    }
    if (!get_option('wdtEnvatoTokenEmailGravity')) {
        update_option('wdtEnvatoTokenEmailGravity', '');
    }
    if (!get_option('wdtActivatedFormidable')) {
        update_option('wdtActivatedFormidable', 0);
    }
    if (!get_option('wdtPurchaseCodeStoreFormidable')) {
        update_option('wdtPurchaseCodeStoreFormidable', '');
    }
    if (!get_option('wdtEnvatoTokenEmailFormidable')) {
        update_option('wdtEnvatoTokenEmailFormidable', '');
    }
    if (get_option('wdtInstallDate') === false) {
        update_option('wdtInstallDate', date( 'Y-m-d' ));
    }
    if (get_option('wdtRatingDiv') === false) {
        update_option('wdtRatingDiv', 'no' );
    }
    if (get_option('wdtShowForminatorNotice') === false) {
        update_option('wdtShowForminatorNotice', 'yes' );
    }
    if (get_option('wdtMDNewsDiv') === false) {
        update_option('wdtMDNewsDiv', 'no' );
    }
    if (get_option('wdtSimpleTableAlert') === false) {
        update_option('wdtSimpleTableAlert', true );
    }
    if (get_option('wdtTempFutureDate') === false) {
        update_option('wdtTempFutureDate', date( 'Y-m-d'));
    }
    if (!get_option('wdtAutoUpdateHash')) {
        update_option('wdtAutoUpdateHash', bin2hex(openssl_random_pseudo_bytes(22)));
    }
}

function wdtDeactivation() {

}

/**
 * Table and option deleting upon plugin deleting
 */
function wdtUninstallDelete() {
    global $wpdb;
    if (get_option('wdtPreventDeletingTables') == false) {
        delete_option('wdtUseSeparateCon');
        delete_option('wdtSeparateCon');
        delete_option('wdtTimepickerRange');
        delete_option('wdtTimeFormat');
        delete_option('wdtTabletWidth');
        delete_option('wdtTablesPerPage');
        delete_option('wdtSumFunctionsLabel');
        delete_option('wdtRenderFilter');
        delete_option('wdtRenderCharts');
        delete_option('wdtGettingStartedPageStatus');
        delete_option('wdtLiteVSPremiumPageStatus');
        delete_option('wdtIncludeGoogleFonts');
        delete_option('wdtIncludeBootstrap');
        delete_option('wdtIncludeBootstrapBackEnd');
        delete_option('wdtPreventDeletingTables');
        delete_option('wdtParseShortcodes');
        delete_option('wdtNumbersAlign');
        delete_option('wdtBorderRemoval');
        delete_option('wdtBorderRemovalHeader');
        delete_option('wdtNumberFormat');
        delete_option('wdtMobileWidth');
        delete_option('wdtMinifiedJs');
        delete_option('wdtMinFunctionsLabel');
        delete_option('wdtMaxFunctionsLabel');
        delete_option('wdtLeftOffset');
        delete_option('wdtTopOffset');
        delete_option('wdtInterfaceLanguage');
        delete_option('wdtGeneratedTablesCount');
        delete_option('wdtFontColorSettings');
        delete_option('wdtDecimalPlaces');
        delete_option('wdtCSVDelimiter');
        delete_option('wdtDateFormat');
        delete_option('wdtAutoUpdateOption');
        delete_option('wdtCustomJs');
        delete_option('wdtGoogleSettings');
        delete_option('wdtGoogleToken');
        delete_option('wdtCustomCss');
        delete_option('wdtBaseSkin');
        delete_option('wdtAvgFunctionsLabel');
        delete_option('wdtInstallDate');
        delete_option('wdtRatingDiv');
        delete_option('wdtShowForminatorNotice');
        delete_option('wdtMDNewsDiv');
        delete_option('wdtTempFutureDate');
        delete_option('wdtSimpleTableAlert');
        delete_option('wdtActivated');
        delete_option('wdtAutoUpdateHash');
        delete_option('wdtPurchaseCodeStore');
        delete_option('wdtEnvatoTokenEmail');
        delete_option('wdtActivatedPowerful');
        delete_option('wdtPurchaseCodeStorePowerful');
        delete_option('wdtEnvatoTokenEmailPowerful');
        delete_option('wdtActivatedMasterDetail');
        delete_option('wdtPurchaseCodeStoreMasterDetail');
        delete_option('wdtActivatedReport');
        delete_option('wdtPurchaseCodeStoreReport');
        delete_option('wdtEnvatoTokenEmailReport');
        delete_option('wdtActivatedGravity');
        delete_option('wdtPurchaseCodeStoreGravity');
        delete_option('wdtEnvatoTokenEmailGravity');
        delete_option('wdtActivatedFormidable');
        delete_option('wdtPurchaseCodeStoreFormidable');
        delete_option('wdtEnvatoTokenEmailFormidable');

        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpdatatables");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpdatatables_columns");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpdatacharts");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpdatatables_rows");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpdatatables_cache");
    }
}

/**
 * Activation hook
 * @param $networkWide
 */
function wdtActivation($networkWide) {
    global $wpdb;

    // Check PHP version
    if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50600) {
        deactivate_plugins(WDT_BASENAME);
        wp_die(
            '<p>The <strong>wpDataTables</strong> plugin requires PHP version 5.6 or greater.</p>',
            'Plugin Activation Error',
            array('response' => 200, 'back_link' => TRUE)
        );
    }

    if (function_exists('is_multisite') && is_multisite()) {
        //check if it is network activation if so run the activation function for each id
        if ($networkWide) {
            $oldBlog = $wpdb->blogid;
            //Get all blog ids
            $blogIds = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

            foreach ($blogIds as $blogId) {
                switch_to_blog($blogId);
                //Create database table if not exists
                wdtActivationCreateTables();
            }
            switch_to_blog($oldBlog);

            return;
        }
    }
    //Create database table if not exists
    wdtActivationCreateTables();
}

/**
 * Uninstall hook
 */
function wdtUninstall() {
    if (function_exists('is_multisite') && is_multisite()) {
        global $wpdb;
        $oldBlog = $wpdb->blogid;
        //Get all blog ids
        $blogIds = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
        foreach ($blogIds as $blogId) {
            switch_to_blog($blogId);
            wdtUninstallDelete();
        }
        switch_to_blog($oldBlog);
    } else {
        wdtUninstallDelete();
    }
}


/**
 * Add rating massage on wpdt-admin pages after 2 weeks of using
 */
function wdtAdminRatingMessages() {
    global $wpdb;
    $query = "SELECT COUNT(*) FROM {$wpdb->prefix}wpdatatables ORDER BY id";

    $allTables = $wpdb->get_var($query);

    $installDate = get_option( 'wdtInstallDate' );
    $currentDate = date( 'Y-m-d' );
    $tempIgnoreDate = get_option( 'wdtTempFutureDate' );
    $wpdtPage = isset($_GET['page']) ? $_GET['page'] : '';
    $urlAddonsPage = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-add-ons';

    $tempIgnore = strtotime($currentDate) >= strtotime($tempIgnoreDate) ? true : false;
    $datetimeInstallDate = new DateTime( $installDate );
    $datetimeCurrentDate = new DateTime( $currentDate );
    $diffIntrval = round( ($datetimeCurrentDate->format( 'U' ) - $datetimeInstallDate->format( 'U' )) / (60 * 60 * 24) );

    if( is_admin() && strpos($wpdtPage,'wpdatatables') !== false &&
        $diffIntrval >= 14 && get_option( 'wdtRatingDiv' ) == "no" && $tempIgnore && isset($allTables) && $allTables > 5) {
        include WDT_TEMPLATE_PATH . 'admin/common/ratingDiv.inc.php';
    }

    if ( is_admin() && strpos($wpdtPage,'wpdatatables') !== false && get_option( 'wdtMDNewsDiv' ) == "no" ) {
        echo '<div class="notice notice-info is-dismissible wpdt-md-news-notice">
             <p class="wpdt-md-news">NEWS! wpDataTables just launched a new addon - Master-Detail Tables. You can find it in the <a href="'. $urlAddonsPage . '">Addons page</a>, read more about it in our docs on this <a href="https://wpdatatables.com/documentation/addons/master-detail-tables/">link</a>.</p>
         </div>';
    }

    if( is_admin() && strpos($wpdtPage,'wpdatatables') !== false &&
        get_option( 'wdtShowForminatorNotice' ) == "yes" && defined( 'FORMINATOR_PLUGIN_BASENAME' )
        && !defined('WDT_FRF_ROOT_PATH')) {
        echo '<div class="notice notice-info is-dismissible wpdt-forminator-news-notice">
             <p class="wpdt-forminator-news"><strong style="color: #ff8c00">NEWS!</strong> wpDataTables just launched a new <strong style="color: #ff8c00">FREE</strong> addon - <strong style="color: #ff8c00">wpDataTables integration for Forminator Forms</strong>. You can download it and read more about it on wp.org on this <a class="wdt-forminator-link" href="https://wordpress.org/plugins/wpdatatables-forminator/" style="color: #ff8c00" target="_blank">link</a>.</p>
         </div>';
    }
}

add_action( 'admin_notices', 'wdtAdminRatingMessages' );

/**
 * Remove rating message
 */
function wdtHideRating() {
    update_option( 'wdtRatingDiv', 'yes' );
    echo json_encode( array("success") );
    exit;
}

add_action( 'wp_ajax_wdtHideRating', 'wdtHideRating' );

/**
 * Remove Forminator notice message
 */
function wdtRemoveForminatorNotice() {
    update_option( 'wdtShowForminatorNotice', 'no' );
    echo json_encode( array("success") );
    exit;
}

add_action( 'wp_ajax_wdt_remove_forminator_notice', 'wdtRemoveForminatorNotice' );

/**
 * Remove Simple Table alert message
 */
function wdtHideSimpleTableAlert() {
        update_option( 'wdtSimpleTableAlert', false );
        echo json_encode( array("success") );
    exit;
}

add_action( 'wp_ajax_wdtHideSimpleTableAlert', 'wdtHideSimpleTableAlert' );

/**
 * Remove Master Detail news message
 */
function wpdtHideMDNewsDiv() {
    update_option( 'wdtMDNewsDiv', 'yes' );
    echo json_encode( array("success") );
    exit;
}

add_action( 'wp_ajax_wpdtHideMDNewsDiv', 'wpdtHideMDNewsDiv' );

/**
 * Temperary hide rating message for 7 days
 */
function wpdtTempHideRatingDiv() {
    $date = strtotime("+7 day");
    update_option('wdtTempFutureDate', date( 'Y-m-d', $date));
    echo json_encode( array("success") );
    exit;
}

add_action( 'wp_ajax_wdtTempHideRating', 'wpdtTempHideRatingDiv' );

/**
 * Create tables on every new site (multisite)
 * @param $blogId
 */
function wdtOnCreateSiteOnMultisiteNetwork($blogId) {
    if (is_plugin_active_for_network('wpdatatables/wpdatatables.php')) {
        switch_to_blog($blogId);
        wdtActivationCreateTables();
        restore_current_blog();
    }
}

add_action('wpmu_new_blog', 'wdtOnCreateSiteOnMultisiteNetwork');

/**
 * Delete table on site delete (multisite)
 * @param $tables
 * @return array
 */
function wdtOnDeleteSiteOnMultisiteNetwork($tables) {
    global $wpdb;
    $tables[] = $wpdb->prefix . 'wpdatatables';
    $tables[] = $wpdb->prefix . 'wpdatatables_columns';
    $tables[] = $wpdb->prefix . 'wpdatacharts';

    return $tables;
}

add_filter('wpmu_drop_tables', 'wdtOnDeleteSiteOnMultisiteNetwork');

function wdtAddBodyClass($classes) {

    $classes .= ' wpdt-c';

    return $classes;
}

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
    exit;
}

/**
 * Helper func that prints out the table
 * @param $id
 */
function wdtOutputTable($id) {
    echo wdtWpDataTableShortcodeHandler(array('id' => $id));
}

/**
 * Handler for the chart shortcode
 * @param $atts
 * @param null $content
 * @return bool|string
 */
function wdtWpDataChartShortcodeHandler($atts, $content = null) {
    extract(shortcode_atts(array(
        'id' => '0'
    ), $atts));

    $id = absint($id);

    if (is_admin() && defined( 'AVADA_VERSION' ) && is_plugin_active('fusion-builder/fusion-builder.php') &&
        class_exists('Fusion_Element') && class_exists('WPDataTables_Fusion_Elements') &&
        isset($_POST['action']) && $_POST['action'] === 'get_shortcode_render')
    {
        return WPDataTables_Fusion_Elements::get_content_for_avada_live_builder($atts, 'chart');
    }

    /** @var mixed $id */
    if (!$id) {
        return false;
    }
    try {
        $wpDataChart = new WPDataChart();
        $wpDataChart->setId($id);
        $wpDataChart->loadFromDB();

        $chartExists = $wpDataChart->getwpDataTableId();
        if (empty($chartExists)) {
            return esc_html__('wpDataChart with provided ID not found!', 'wpdatatables');
        }

        do_action('wpdatatables_before_render_chart', $wpDataChart->getId());

        return $wpDataChart->renderChart();
    } catch (Exception $e) {
        return esc_html__('There is some issue of displaying chart. Please edit chart in admin area for more details.');
    }
}

/**
 * Handler for the table cell shortcode
 * @param $atts
 * @param null $content
 * @return mixed|string
 * @throws Exception
 */
function wdtWpDataTableCellShortcodeHandler($atts, $content = null) {
    global $wpdb;
    extract(shortcode_atts(array(
        'table_id' => '0',
        'row_id' => '0',
        'column_key' => '%%no_val%%',
        'column_id' => '%%no_val%%',
        'column_id_value' => '%%no_val%%',
        'sort' => '1'
    ), $atts));

    $table_id = absint($table_id);
    $row_id = absint($row_id);
    $sort = absint($sort);

    /**
     * Protection
     * @var int $table_id
     */
    if (!$table_id)
        return esc_html__('wpDataTable with provided ID not found!', 'wpdatatables');

    /** @var int $row_id */
    $rowID = !$row_id ? 0 : $row_id;

    /** @var int $sort */
    $includeSort = $sort == 1;

    /** @var mixed $column_key */
    $columnKey = $column_key !== '%%no_val%%' ? $column_key : '';

    /** @var mixed $column_id */
    $columnID = $column_id !== '%%no_val%%' ? $column_id : '';

    /** @var mixed $column_id_value */
    if ($column_id_value !== '%%no_val%%'){
        if ($column_id_value === '%CURRENT_USER_ID%'){
            $columnIDValue = get_current_user_id();
        } else {
            $columnIDValue = $column_id_value;
        }
    } else {
        $columnIDValue = '';
    }

    $rowID         = apply_filters('wpdatatables_cell_filter_row_id', $rowID, $columnKey, $columnID, $columnIDValue, $table_id);
    $columnKey     = apply_filters('wpdatatables_cell_filter_column_key', $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
    $columnID      = apply_filters('wpdatatables_cell_filter_column_id', $columnID, $columnKey, $rowID, $columnIDValue, $table_id);
    $columnIDValue = apply_filters('wpdatatables_cell_filter_column_id_value', $columnIDValue, $columnKey, $rowID, $columnID, $table_id);

    if ($columnKey == '')
        return esc_html__('Column key for provided table ID not found!', 'wpdatatables');

    $tableData = WDTConfigController::loadTableFromDB($table_id, false);

    if (empty($tableData->content))
        return esc_html__('wpDataTable with provided ID not found!', 'wpdatatables');

    if ($tableData->table_type === 'simple') {

        if ($columnIDValue != '' || $columnID != '')
            return esc_html__('For getting cell value from simple table, column_id and column_id_value are not supported. Please use row_id.', 'wpdatatables');

        if ($rowID == 0)
            return esc_html__('Row ID for provided table ID not found!', 'wpdatatables');

        try {
            $wpDataTableRows = WPDataTableRows::loadWpDataTableRows($table_id);
            $rowsData = $wpDataTableRows->getRowsData();
            $columnHeaders = array_flip($wpDataTableRows->getColHeaders());
            $columnKey = strtoupper($columnKey);
            if (isset($columnHeaders[$columnKey])) {
                $rowID = $rowID - 1;
                if(isset($rowsData[$rowID])){
                    $columnKey = $columnHeaders[$columnKey];
                    $cellMetaClasses = array_unique($wpDataTableRows->getCellClassesByIndexes($rowsData, $rowID, $columnKey));
                    $cellValue = $wpDataTableRows->getCellDataByIndexes($rowsData, $rowID, $columnKey);
                    $cellValue = apply_filters('wpdatatables_cell_value_filter', $cellValue, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
                    $cellValueOutput = WPDataTableRows::prepareCellDataOutput($cellValue, $cellMetaClasses, $rowID, $columnKey, $table_id);
                } else {
                    return esc_html__('Row ID for provided table ID not found!', 'wpdatatables');
                }
            } else {
                return esc_html__('Column key for provided table ID not found!', 'wpdatatables');
            }
        } catch (Exception $e) {
            return ltrim($e->getMessage(), '<br/><br/>');
        }
    } else {
        try {
            $wpDataTable = WPDataTable::loadWpDataTable($table_id);

            if (!isset($wpDataTable->getWdtColumnTypes()[$columnKey]))
                return esc_html__('Column key for provided table ID not found!', 'wpdatatables');

            if ($columnIDValue != '' || $columnID != ''){
                if ($columnID == '')
                    return esc_html__('Column ID for provided table ID not found!', 'wpdatatables');

                if ($columnIDValue == '')
                    return esc_html__('Column ID value for provided table ID not found!', 'wpdatatables');

                if (!isset($wpDataTable->getWdtColumnTypes()[$columnID]))
                    return esc_html__('Column ID for provided table ID not found!', 'wpdatatables');

                if (in_array($wpDataTable->getWdtColumnTypes()[$columnID],['date','datetime','time','float','formula']))
                    return esc_html__('At the moment float, formula, date, datetime and time columns can not be used as column_id. Please use other column that contains unique identifiers.', 'wpdatatables');

                if ($columnKey == $columnID)
                    return esc_html__('Column Key an Column ID can not be the same!', 'wpdatatables');
            }

            $isTableSortable = $wpDataTable->sortEnabled();
            $doSort = false;
            if ($includeSort && $isTableSortable){
                $doSort = true;
                $sortDirection = $wpDataTable->getDefaultSortDirection();
                if ( $wpDataTable->getDefaultSortColumn()){
                    $sortColumn = $wpDataTable->getColumns()[$wpDataTable->getDefaultSortColumn()]->getOriginalHeader();
                    $columnType = $wpDataTable->getColumns()[$wpDataTable->getDefaultSortColumn()]->getDataType();
                } else {
                    $sortColumn = $wpDataTable->getColumns()[0]->getOriginalheader();
                    $columnType = $wpDataTable->getColumns()[0]->getDataType();
                }
            }

            $isFormulaColumnKey = false;
            if (isset($wpDataTable->getWdtColumnTypes()[$columnKey])
                && $wpDataTable->getWdtColumnTypes()[$columnKey] == 'formula')
            {
                $isFormulaColumnKey = true;
                $formulaColumnKey = $wpDataTable->getColumn($column_key)->getFormula();
                $headersInFormulaColumnKey = $wpDataTable->detectHeadersInFormula($formulaColumnKey);
                $headersColumnKey = WDTTools::sanitizeHeaders($headersInFormulaColumnKey);
            }
            $isForeignColumnKey = false;
            if (isset($wpDataTable->getWdtColumnTypes()[$columnKey])
                && $wpDataTable->getColumn($columnKey)
                && $wpDataTable->getColumn($columnKey)->getForeignKeyRule())
            {
                $isForeignColumnKey = true;
                $foreignKeyRuleForColumnKey = $wpDataTable->getColumn($columnKey)->getForeignKeyRule();
                $joinedTableForColumnKey = WPDataTable::loadWpDataTable($foreignKeyRuleForColumnKey->tableId);
                $joinedTableContentForColumnKey = WDTTools::applyPlaceholders( $joinedTableForColumnKey->getTableContent());
                $storeColumnForColumnKey = WDTConfigController::loadSingleColumnFromDB($foreignKeyRuleForColumnKey->storeColumnId);
                $displayColumnForColumnKey = WDTConfigController::loadSingleColumnFromDB($foreignKeyRuleForColumnKey->displayColumnId);
            }

            if (in_array($wpDataTable->getTableType(),['manual','mysql'])) {
                $contentQuery = WDTTools::applyPlaceholders($wpDataTable->getTableContent());
                $tableDbName = (isset($tableData->mysql_table_name) && $tableData->mysql_table_name != '') ? $tableData->mysql_table_name : '';

                $vendor = Connection::getVendor($tableData->connection);
                $isMySql = $vendor === Connection::$MYSQL;
                $isMSSql = $vendor === Connection::$MSSQL;
                $isPostgreSql = $vendor === Connection::$POSTGRESQL;

                $leftSysIdentifier = Connection::getLeftColumnQuote($vendor);
                $rightSysIdentifier = Connection::getRightColumnQuote($vendor);

                if ($tableData->table_type == 'manual') {
                    $customQuery = "SELECT *,";
                    if ($isFormulaColumnKey) {
                        $formulaColumnKey = formulaFormat($headersInFormulaColumnKey, $formulaColumnKey, $tableDbName, $leftSysIdentifier, $rightSysIdentifier);
                        $customQuery .= "(" . $formulaColumnKey . ") as " . $columnKey . " FROM " . $tableDbName;
                    } else {
                        $customQuery = "SELECT "
                            . $tableDbName . '.'
                            . $leftSysIdentifier . $columnKey . $rightSysIdentifier
                            . ' FROM '
                            . $tableDbName;
                    }
                } else {
                    $customQuery = "SELECT *,";
                    if ($isFormulaColumnKey) {
                        $formulaColumnKey = formulaFormat($headersInFormulaColumnKey, $formulaColumnKey, 'wdt', $leftSysIdentifier, $rightSysIdentifier);
                        $customQuery .= "(" . $formulaColumnKey . ") as " . $columnKey . " FROM (" . $contentQuery . ") as wdt ";
                    } else {
                        $customQuery = "SELECT wdt."
                            . $leftSysIdentifier . $columnKey . $rightSysIdentifier
                            . " FROM (" . $contentQuery . ") as wdt ";
                    }
                }

                $tableDbNameBasedOnType = $tableData->table_type == 'manual' ? $tableDbName : 'wdt';

                if ($doSort && $isFormulaColumnKey && !$isMSSql)
                    $customQuery .= ' ORDER BY ' . $tableDbNameBasedOnType . '.' . $leftSysIdentifier. $sortColumn . $rightSysIdentifier . " " . $sortDirection . " ";

                if ($columnIDValue != '' || $columnID != '') {
                    $customColumnID = true;
                    $columnIDType = $wpDataTable->getWdtColumnTypes()[$columnID];

                    if ($columnIDType =='int'){
                        if (get_option('wdtNumberFormat') == 1) {
                            $columnIDValue = str_replace(',', '.', str_replace('.',  '', $columnIDValue));
                        } else {
                            $columnIDValue = str_replace(',',  '', $columnIDValue);
                        }
                    } else {
                        $columnIDValue = addslashes($columnIDValue);
                    }

                    if ($isFormulaColumnKey) {
                        $customQuery = " SELECT wdt."
                            . $leftSysIdentifier . $columnKey . $rightSysIdentifier
                            . " FROM (" . $customQuery . ") as wdt"
                            . " WHERE wdt."
                            . $leftSysIdentifier . $columnID . $rightSysIdentifier
                            . "='" . $columnIDValue . "' ";
                        if ($doSort && $isMSSql){
                            $customQuery .= ' ORDER BY '
                                . 'wdt.' . $leftSysIdentifier. $sortColumn . $rightSysIdentifier
                                . " " . $sortDirection . " ";
                        }
                    } else {
                        $customQuery .= " WHERE "
                            . $tableDbNameBasedOnType . "."
                            . $leftSysIdentifier . $columnID . $rightSysIdentifier
                            . "='" . $columnIDValue . "' ";
                        if ($doSort && $isMSSql && $isForeignColumnKey){
                            $customQuery .= '';
                        } else if ($doSort){
                            $customQuery .= ' ORDER BY '
                                . $tableDbNameBasedOnType . '.'
                                . $leftSysIdentifier. $sortColumn . $rightSysIdentifier
                                . " " . $sortDirection . " ";
                        }
                    }
                } else {
                    $customColumnID = false;
                    if ($rowID != 0) {
                        if ($isFormulaColumnKey) {
                            $customQuery = " SELECT wdt."
                                . $leftSysIdentifier . $columnKey . $rightSysIdentifier
                                . " FROM (" . $customQuery . ") as wdt"
                                . " WHERE wdt."
                                . $leftSysIdentifier;
                            if ($wpDataTable->getIdColumnKey() != '') {
                                $customQuery .= $wpDataTable->getIdColumnKey();
                            } else {
                                $customQuery .= $wpDataTable->getColumns()[0]->getOriginalheader();
                            }
                            $customQuery .= $rightSysIdentifier . "='" . $rowID . "'";
                            if ($doSort && $isMSSql){
                                $customQuery .= ' ORDER BY '
                                    . 'wdt.' . $leftSysIdentifier. $sortColumn . $rightSysIdentifier
                                    . " " . $sortDirection . " ";
                            }
                        } else {
                            $customQuery .= " WHERE "
                                . $tableDbNameBasedOnType . "."
                                . $leftSysIdentifier;
                            if ($wpDataTable->getIdColumnKey() != '') {
                                $customQuery .= $wpDataTable->getIdColumnKey();
                            } else {
                                $customQuery .= $wpDataTable->getColumns()[0]->getOriginalheader();
                            }
                            $customQuery .= $rightSysIdentifier . "='" . $rowID . "'";
                            if ($doSort && $isMSSql && $isForeignColumnKey){
                                $customQuery .= '';
                            } else if ($doSort){
                                $customQuery .= ' ORDER BY ' . $tableDbNameBasedOnType . '.'
                                    . $leftSysIdentifier. $sortColumn . $rightSysIdentifier
                                    . " " . $sortDirection . " ";
                            }

                        }

                    } else {
                        return esc_html__('Row ID for provided table ID not found!', 'wpdatatables');
                    }
                }

                if ($isForeignColumnKey){
                    $adoptCustomQuery = ($isPostgreSql) ? "(" . $customQuery . ")::INTEGER" : "(" . $customQuery . ")";
                    $customQuery = "SELECT "
                        . 'wdtForeign.'
                        . $leftSysIdentifier . $displayColumnForColumnKey['orig_header'] . $rightSysIdentifier
                        . ' FROM ('
                        . $joinedTableContentForColumnKey . ') as wdtForeign '
                        . "  WHERE wdtForeign." . $leftSysIdentifier . $storeColumnForColumnKey['orig_header'] . $rightSysIdentifier . "=" . $adoptCustomQuery;
                }

                $customQuery = wdtSanitizeQuery($customQuery);

                if (Connection::isSeparate($tableData->connection)) {
                    $sql = Connection::getInstance($tableData->connection);
                    if ($customColumnID) {

                        if ($isMySql || $isPostgreSql) {
                            $customQuery .= " LIMIT 1";
                        }

                        if ($isMSSql) {
                            if ($doSort && !$isForeignColumnKey){
                                $customQuery .= " OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY";
                            } else {
                                $customQuery .= " ORDER BY(SELECT NULL) OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY";
                            }

                        }

                    }
                    $customQuery = apply_filters('wpdatatables_cell_filter_query', $customQuery, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
                    $cellValue = $sql->getField($customQuery);

                    if ($sql->getLastError() != '') {
                        return esc_html__('There was an error when trying to get cell value.', 'wpdatatables') . ' ' . ((current_user_can( 'administrator' )) ? $sql->getLastError() : ' Please contact the administrator.');
                    }
                } else {
                    $customQuery = apply_filters('wpdatatables_cell_filter_query', $customQuery, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
                    $cellValue = $wpdb->get_var($customQuery);

                    if ($wpdb->last_error != '') {
                        return esc_html__('There was an error when trying to get cell value.', 'wpdatatables') . ': ' . ((current_user_can( 'administrator' )) ? $wpdb->last_error : 'Please contact the administrator.');
                    }
                }
            } else if ($tableData->table_type == 'gravity' && $tableData->server_side) {
                $sorting = null;
                $content = json_decode($tableData->content);
                $form = \GFAPI::get_form($content->formId);
                $fieldsData = WDTGravityIntegration\Plugin::getFieldsData($form, $content->fieldIds);
                foreach ($fieldsData as $fieldData) {
                    if ($fieldData['label'] == $columnKey) $columnKeyFieldData = $fieldData;
                    if ($includeSort && $isTableSortable){
                        if ($fieldData['label'] == $sortColumn) {
                            $key = $fieldData['fieldIds'];
                            in_array($columnType, ['float', 'int'], true) ? $numeric = true : $numeric = null;
                            $sorting = array('key' => $key, 'direction' => $sortDirection, 'is_numeric' => $numeric);
                        }
                    }
                    if ($columnIDValue != '' || $columnID != '') {
                        if ($fieldData['label'] == $columnID) {
                            $searchCriteria['field_filters'][] = ['key' => $fieldData['fieldIds'], 'value' => $columnIDValue];
                        }
                    }
                }
                if (!($columnIDValue != '' || $columnID != '')) {
                    $rowID = (int)str_replace(array('.', ','), '' , $rowID);
                    $searchCriteria['field_filters'][] = ['key' => 'id', 'value' => $rowID];
                }
                $entries = \GFAPI::get_entries($form['id'], $searchCriteria, $sorting, 100000000000);
                if ($columnIDValue != '' || $columnID != '') {
                    if ($entries == [])
                        return esc_html__('Column ID value for provided table ID not found!', 'wpdatatables');
                } else {
                    if ($entries == [])
                        return esc_html__('Row ID value for provided table ID not found!', 'wpdatatables');
                }
                if ($isFormulaColumnKey) {
                    try {
                        $cellValue =
                            WPDataTable::solveFormula(
                                $formulaColumnKey,
                                $headersColumnKey,
                                $entries[0]
                            );
                    } catch (Exception $e) {
                        return ltrim($e->getMessage(), '<br/><br/>');
                    }
                } else {
                    $cellValue = WDTGravityIntegration\Plugin::prepareFieldsData($entries[0], $columnKeyFieldData);
                }

            } else {
                $dataRows = $wpDataTable->getDataRows();
                if ($dataRows == [])
                    return esc_html__('Table do not have data for provided table ID!', 'wpdatatables');
                if ($includeSort && $isTableSortable){
                    $sortDirection = $sortDirection == 'ASC' ? SORT_ASC : SORT_DESC;
                    $sortingType = in_array($columnType, array('float', 'int', 'formula')) ? SORT_NUMERIC : SORT_REGULAR;
                    array_multisort(
                        array_column($dataRows, $sortColumn),
                        $sortDirection,
                        $sortingType,
                        $dataRows
                    );
                }

                $dataRows = apply_filters('wpdatatables_cell_data_rows_filter', $dataRows, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);

                if ($columnIDValue != '' || $columnID != '') {
                    $filteredData = array_filter($dataRows, function ($item) use ($columnIDValue, $columnID) {
                        if ($item[$columnID] == $columnIDValue) {
                            return true;
                        }
                        return false;
                    });
                    if ($filteredData == [])
                        return esc_html__('Column ID value for provided table ID not found!', 'wpdatatables');
                    $dataRows = array_values($filteredData);
                    $dataRows = apply_filters('wpdatatables_cell_filtered_data_rows_filter', $dataRows, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
                    $cellValue = $dataRows[0][$columnKey];
                } else {
                    if (in_array($tableData->table_type, ['gravity' ,'formidable','forminator'])) {
                        $entryIdName = 'id';
                        if ($tableData->table_type == 'forminator' )
                            $entryIdName = 'entryid';
                        if (!isset($dataRows[0][$entryIdName]))
                            return esc_html__('Entry ID not found! Please provide existing entry id from form.', 'wpdatatables');
                        $rowID = str_replace(array('.', ','), '' , $rowID);
                        $filteredData = array_filter($dataRows, function ($item) use ($rowID, $entryIdName) {
                            if ($item[$entryIdName] == $rowID) {
                                return true;
                            }
                            return false;
                        });
                        if ($filteredData == [])
                            return esc_html__('Entry ID value for provided table ID not found!', 'wpdatatables');
                        $dataRows = array_values($filteredData);
                        $dataRows = apply_filters('wpdatatables_cell_filtered_data_rows_filter', $dataRows, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
                        $cellValue = $dataRows[0][$columnKey];
                    } else {
                        $rowID = $rowID != 0 ? $rowID - 1 : 0;
                        $wpDataTable->setDataRows($dataRows);
                        $cellValue = $wpDataTable->getCell($columnKey, $rowID);
                    }

                }
            }
            $cellValue = apply_filters('wpdatatables_cell_value_filter', $cellValue, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
            $cellValueOutput = $wpDataTable->getColumn($columnKey)->prepareCellOutput($cellValue);
        } catch (Exception $e) {
            return ltrim($e->getMessage(), '<br/><br/>');
        }
    }

    return apply_filters('wpdatatables_cell_output_filter', $cellValueOutput, $cellValue, $columnKey, $rowID, $columnID, $columnIDValue, $table_id);
}

function formulaFormat($headersInFormulaColumn, $formulaColumn, $tableName, $leftSysIdentifier, $rightSysIdentifier) {
    foreach ($headersInFormulaColumn as $headerColumnKey) {
        $formulaColumn = str_replace(
            $headerColumnKey,
            $tableName . "." . $leftSysIdentifier . $headerColumnKey . $rightSysIdentifier,
            $formulaColumn
        );
    }
    return $formulaColumn;
}

/**
 * Handler for the table shortcode
 * @param $atts
 * @param null $content
 * @return mixed|string
 * @throws Exception
 */
function wdtWpDataTableShortcodeHandler($atts, $content = null) {
    global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9, $wdtExportFileName;

    extract(shortcode_atts(array(
        'id' => '0',
        'var1' => '%%no_val%%',
        'var2' => '%%no_val%%',
        'var3' => '%%no_val%%',
        'var4' => '%%no_val%%',
        'var5' => '%%no_val%%',
        'var6' => '%%no_val%%',
        'var7' => '%%no_val%%',
        'var8' => '%%no_val%%',
        'var9' => '%%no_val%%',
        'export_file_name' => '%%no_val%%',
        'table_view' => 'regular'
    ), $atts));

    $id = absint($id);

    if (is_admin() && defined( 'AVADA_VERSION' ) && is_plugin_active('fusion-builder/fusion-builder.php') &&
        class_exists('Fusion_Element') && class_exists('WPDataTables_Fusion_Elements') &&
        isset($_POST['action']) && $_POST['action'] === 'get_shortcode_render')
    {
        return WPDataTables_Fusion_Elements::get_content_for_avada_live_builder($atts, 'table');
    }

    if (!$id) {
        return false;
    }

    do_action('wpdatatables_before_render_table_config_data', $id);

    $tableData = WDTConfigController::loadTableFromDB($id);
    if (empty($tableData->content)) {
        return esc_html__('wpDataTable with provided ID not found!', 'wpdatatables');
    }

    do_action('wpdatatables_before_render_table', $id);

    /** @var mixed $var1 */
    $wdtVar1 = $var1 !== '%%no_val%%' ? $var1 : $tableData->var1;
    /** @var mixed $var2 */
    $wdtVar2 = $var2 !== '%%no_val%%' ? $var2 : $tableData->var2;
    /** @var mixed $var3 */
    $wdtVar3 = $var3 !== '%%no_val%%' ? $var3 : $tableData->var3;
    /** @var mixed $var4 */
    $wdtVar4 = $var4 !== '%%no_val%%' ? $var4 : $tableData->var4;
    /** @var mixed $var5 */
    $wdtVar5 = $var5 !== '%%no_val%%' ? $var5 : $tableData->var5;
    /** @var mixed $var6 */
    $wdtVar6 = $var6 !== '%%no_val%%' ? $var6 : $tableData->var6;
    /** @var mixed $var7 */
    $wdtVar7 = $var7 !== '%%no_val%%' ? $var7 : $tableData->var7;
    /** @var mixed $var8 */
    $wdtVar8 = $var8 !== '%%no_val%%' ? $var8 : $tableData->var8;
    /** @var mixed $var9 */
    $wdtVar9 = $var9 !== '%%no_val%%' ? $var9 : $tableData->var9;

    /** @var mixed $export_file_name */
    $wdtExportFileName = $export_file_name !== '%%no_val%%' ? $export_file_name : '';

    do_action('wpdatatables_before_get_table_metadata',$id);

    if ($tableData->table_type === 'simple'){
        try {
            $wpDataTableRows = WPDataTableRows::loadWpDataTableRows($id);
            $output = $wpDataTableRows->generateTable($id);
        } catch (Exception $e) {
            $output = ltrim($e->getMessage(), '<br/><br/>');
        }
    } else {
        try{
            /** @var mixed $table_view */
            if ($table_view == 'excel') {
                $wpDataTable = new WPExcelDataTable($tableData->connection);
            } else {
                $wpDataTable = new WPDataTable($tableData->connection);
            }
        } catch (Exception $e) {
            echo WDTTools::wdtShowError($e->getMessage());
            return;
        }


        $wpDataTable->setWpId($id);

        $columnDataPrepared = $wpDataTable->prepareColumnData($tableData);

        try {
            $wpDataTable->fillFromData($tableData, $columnDataPrepared);
            $wpDataTable = apply_filters('wpdatatables_filter_initial_table_construct', $wpDataTable);

            $output = '';
            if ($tableData->show_title && $tableData->title) {
                $output .= apply_filters('wpdatatables_filter_table_title', (empty($tableData->title) ? '' : '<h3 class="wpdt-c" id="wdt-table-title-'. $id .'">' . $tableData->title . '</h3>'), $id);
            }
	        if ($tableData->show_table_description && $tableData->table_description) {
		        $output .= apply_filters('wpdatatables_filter_table_description_text', (empty($tableData->table_description) ? '' : '<p class="wpdt-c" id="wdt-table-description-'. $id .'">' . $tableData->table_description . '</p>'), $id);
	        }
            $output .= $wpDataTable->generateTable($tableData->connection);
        } catch (Exception $e) {
            $output = WDTTools::wdtShowError($e->getMessage());
        }
    }

    $output = apply_filters('wpdatatables_filter_rendered_table', $output, $id);

    return $output;
}

/**
 * Handler for the SUM, AVG, MIN and MAX function shortcode
 * @param $atts
 * @param null $content
 * @param null $shortcode
 * @return string
 * @throws WDTException
 */
function wdtFuncsShortcodeHandler($atts, $content = null, $shortcode = null) {

    $attributes = shortcode_atts(array(
        'table_id' => 0,
        'col_id' => 0,
        'label' => null,
        'value_only' => 0
    ), $atts);

    $table_id   = absint($attributes['table_id']);
    $col_id     = absint($attributes['col_id']);
    $label      = is_null($attributes['label']) ? null : sanitize_text_field($attributes['label']);
    $value_only = absint($attributes['value_only']);

    if (!$table_id) {
        return esc_html__("Please provide table_id attribute for {$shortcode} shortcode!", 'wpdatatables');
    }
    if (!$col_id) {
        return esc_html__("Please provide col_id attribute for {$shortcode} shortcode!", 'wpdatatables');
    }

    $wpDataTable = WPDataTable::loadWpDataTable($table_id, null, true);

    $wpDataTableColumns = $wpDataTable->getColumns();
    if (empty($wpDataTableColumns)) {
        return esc_html__('wpDataTable with provided ID not found!', 'wpdatatables');
    }

    $column = WDTConfigController::loadSingleColumnFromDB($col_id);

    $columnExists = (int)$column['table_id'] === $table_id;
    if ($columnExists === false) {
        return esc_html__("Column with ID {$col_id} is not found in table with ID {$table_id}!", 'wpdatatables');
    }
    if ($column['column_type'] !== 'int' && $column['column_type'] !== 'float' && $column['column_type'] !== 'formula') {
        return esc_html__('Provided column is not Integer or Float column type', 'wpdatatables');
    }

    if ($shortcode === 'wpdatatable_sum') {
        $function = 'sum';
        if (!isset($label)) {
            $label = get_option('wdtSumFunctionsLabel') ? get_option('wdtSumFunctionsLabel') : '&#8721; =';
        }
    } else if ($shortcode === 'wpdatatable_avg') {
        $function = 'avg';
        if (!isset($label)) {
            $label = get_option('wdtAvgFunctionsLabel') ? get_option('wdtAvgFunctionsLabel') : 'Avg =';
        }
    } else if ($shortcode === 'wpdatatable_min') {
        $function = 'min';
        if (!isset($label)) {
            $label = get_option('wdtMinFunctionsLabel') ? get_option('wdtMinFunctionsLabel') : 'Min =';
        }
    } else {
        $function = 'max';
        if (!isset($label)) {
            $label = get_option('wdtMaxFunctionsLabel') ? get_option('wdtMaxFunctionsLabel') : 'Max =';
        }
    }

    $funcResult = $wpDataTable->calcColumnFunction($column['orig_header'], $function);

    ob_start();
    include WDT_TEMPLATE_PATH . 'frontend/aggregate_functions.inc.php';
    $aggregateFunctionsHtml = ob_get_contents();
    ob_end_clean();

    return $aggregateFunctionsHtml;

}

function wdtRenderScriptStyleBlock($tableID) {
    $customJs = get_option('wdtCustomJs');
    $scriptBlockHtml = '';
    $styleBlockHtml = '';
    $wpDataTable = WDTConfigController::loadTableFromDB($tableID,false);

    if ($customJs) {
        $scriptBlockHtml .= '<script type="text/javascript">' . stripslashes_deep(html_entity_decode($customJs)) . '</script>';
    }
    $returnHtml = $scriptBlockHtml;

    // Color and font settings
    $wdtFontColorSettings = get_option('wdtFontColorSettings');
    if (!empty($wdtFontColorSettings)) {
        /** @noinspection PhpUnusedLocalVariableInspection */
        ob_start();
        include WDT_TEMPLATE_PATH . 'frontend/style_block.inc.php';
        $styleBlockHtml = ob_get_contents();
        ob_end_clean();
        $styleBlockHtml = apply_filters('wpdatatables_filter_style_block', $styleBlockHtml, $wpDataTable->id);
    }

    $returnHtml .= $styleBlockHtml;
    return $returnHtml;
}
function wdtTableRenderScriptStyleBlock($obj) {

    // Generate the style block for table
    $styleBlockHtml = '';
    $returnData = "<style>\n";

    $tableCustomCss = $obj->getTableCustomCss();

    if ($tableCustomCss) {
        $returnData .= stripslashes_deep($tableCustomCss);
    }

    if ($obj->getTableBorderRemoval()) {
        $returnData .= ".wpDataTablesWrapper table.wpDataTable[data-wpdatatable_id='" .  $obj->getWpId() . "'] > tbody > tr > td{ border: none !important; }\n";
        $returnData .= ".wpDataTablesWrapper table.wpDataTable[data-wpdatatable_id='" .  $obj->getWpId() . "'] > thead { border: none !important; }\n";
        $returnData .= ".wpDataTablesWrapper table.wpDataTable[data-wpdatatable_id='" .  $obj->getWpId() . "'] > tfoot > tr > td{ border: none !important; }\n";
        $returnData .= ".wpDataTablesWrapper table.wpDataTable[data-wpdatatable_id='" .  $obj->getWpId() . "'] > tfoot { border: none !important; }\n";
    }
    if ($obj->getTableBorderRemovalHeader()) {
        $returnData .= ".wpDataTablesWrapper table.wpDataTable[data-wpdatatable_id='" .  $obj->getWpId() . "'] > thead > tr > th{ border: none !important; }\n";
    }

    $returnData .= "</style>\n";

    $returnHtml = $returnData;
    $wdtTableFontColorSettings = $obj->getTableFontColorSettings();

     //Color and font settings
    if (!empty($wdtTableFontColorSettings)) {
        /** @noinspection PhpUnusedLocalVariableInspection */
        ob_start();
        include WDT_TEMPLATE_PATH . 'frontend/style_table_block.inc.php';
        $styleBlockHtml = ob_get_contents();
        ob_end_clean();
        $styleBlockHtml = apply_filters('wpdatatables_filter_style_table_block', $styleBlockHtml, $obj->getWpId());
    }

    $returnHtml .= $styleBlockHtml;
    return $returnHtml;
}

/**
 * Checks if current user can edit table on the front-end
 * @param $tableEditorRoles
 * @param $id
 * @return bool|mixed
 */
function wdtCurrentUserCanEdit($tableEditorRoles, $id) {
    $wpRoles = new WP_Roles();
    $userCanEdit = false;

    $tableEditorRoles = strtolower($tableEditorRoles);
    $editorRoles = array();

    if (empty($tableEditorRoles)) {
        $userCanEdit = true;
    } else {
        $editorRoles = explode(',', $tableEditorRoles);

        $allRoles = $wpRoles->get_names();

        $currentUser = wp_get_current_user();
        if (!($currentUser instanceof WP_User)) {
            return false;
        }

        foreach ($currentUser->roles as $userRole) {
            if (in_array(strtolower($allRoles[$userRole]), $editorRoles)) {
                $userCanEdit = true;
                break;
            }
        }
    }

    return apply_filters('wpdatatables_allow_edit_table', $userCanEdit, $editorRoles, $id);
}

/**
 * Removes all dangerous strings from query
 * @param $query
 * @return mixed|string
 */
function wdtSanitizeQuery($query) {
    $query = str_replace('DELETE', '', $query);
    $query = str_replace('DELETE ', '', $query);
    $query = str_replace(' DELETE ', '', $query);
    $query = str_replace(' delete ', '', $query);
    $query = str_replace('DROP', '', $query);
    $query = str_replace('DROP ', '', $query);
    $query = str_replace(' DROP ', '', $query);
    $query = str_replace(' drop ', '', $query);
    $query = str_replace('INSERT ', '', $query);
    $query = str_replace(' INSERT ', '', $query);
    $query = str_replace(' insert ', '', $query);
    $query = str_replace('UPDATE ', '', $query);
    $query = str_replace(' UPDATE ', '', $query);
    $query = str_replace(' update ', '', $query);
    $query = str_replace('TRUNCATE', '', $query);
    $query = str_replace('TRUNCATE ', '', $query);
    $query = str_replace(' TRUNCATE ', '', $query);
    $query = str_replace(' truncate ', '', $query);
    $query = str_replace('CREATE', '', $query);
    $query = str_replace('CREATE ', '', $query);
    $query = str_replace(' CREATE ', '', $query);
    $query = str_replace(' create ', '', $query);
    $query = str_replace('ALTER', '', $query);
    $query = str_replace('ALTER ', '', $query);
    $query = str_replace(' ALTER ', '', $query);
    $query = str_replace(' alter ', '', $query);
    $query = stripslashes($query);
    $query = rtrim($query, "; \t\n");

    $query = apply_filters('wpdt_sanitize_query',$query);

    return $query;
}

/**
 * Init wpDataTabes block for Gutenberg
 */
function initGutenbergBlocks (){
    WpDataTablesGutenbergBlock::init();
    WpDataChartsGutenbergBlock::init();
    add_filter( 'block_categories_all', 'addWpDataTablesBlockCategory', 10, 2);
}
add_action('plugins_loaded', 'initGutenbergBlocks');

/**
 * Creating wpDataTables block category in Gutenberg
 */
function addWpDataTablesBlockCategory ($categories, $post) {
    return array_merge(
        array(
            array(
                'slug' => 'wpdatatables-blocks',
                'title' => 'wpDataTables',
            ),
        ),
        $categories
    );
}

/**
 * Buttons for "insert wpDataTable" and "insert wpDataCharts" in WP MCE editor
 */
function wdtMCEButtons() {
    add_filter("mce_external_plugins", "wdtAddButtons");
    add_filter('mce_buttons', 'wdtRegisterButtons');
}

add_action('init', 'wdtMCEButtons');

/**
 * Function that add buttons for MCE editor
 * @param $pluginArray
 * @return mixed
 */
function wdtAddButtons($pluginArray) {
    $pluginArray['wpdatatables'] = WDT_JS_PATH . '/wpdatatables/wdt.mce.js';

    return $pluginArray;
}

/**
 * Function that register buttons for MCE editor
 * @param $buttons
 * @return mixed
 */
function wdtRegisterButtons($buttons) {
    $buttons[] = 'wpdatatable';
    $buttons[] = 'wpdatachart';
    return $buttons;
}

/**
 * Loads the translations
 */
function wdtLoadTextdomain() {
    load_plugin_textdomain('wpdatatables', false, dirname(plugin_basename(dirname(__FILE__))) . '/languages/' . get_locale() . '/');
}

/**
 * Enable Multiple connection
 */
function wdtEnableMultipleConnections() {
    update_option('wdtSeparateCon', json_encode(array(
        array(
            "id"       => 'abcdefghijk',
            "host"     => get_option('wdtMySqlHost') ?: '',
            "database" => get_option('wdtMySqlDB') ?: '',
            "user"     => get_option('wdtMySqlUser') ?: '',
            "password" => get_option('wdtMySqlPwd') ?: '',
            "port"     => get_option('wdtMySqlPort') ?: '',
            "vendor"   => 'mysql',
            "driver"   => 'dblib',
            "name"     => "MYSQL",
            "default"  => get_option('wdtUseSeparateCon') ?: ''
        )
    )));

    delete_option('wdtMySqlHost');
    delete_option('wdtMySqlDB');
    delete_option('wdtMySqlUser');
    delete_option('wdtMySqlPwd');
    delete_option('wdtMySqlPort');
}

/**
 * Workaround for NULLs in WP
 */
if ($wp_version < 4.4) {
    add_filter('query', 'wdtSupportNulls');

    function wdtSupportNulls($query) {
        $query = str_ireplace("'NULL'", "NULL", $query);
        $query = str_replace('null_str', 'null', $query);

        return $query;
    }
}

global $wdtPluginSlug;

$filePath = plugin_basename(__FILE__);
$filePathArr = explode('/', $filePath);
$wdtPluginSlug = $filePathArr[0] . '/wpdatatables.php';

/**
 * @param $transient
 *
 * @return mixed
 */
function wdtCheckUpdate($transient)
{
    global $wdtPluginSlug;

    if (empty($transient->checked)) {
        return $transient;
    }

    $purchaseCode = get_option('wdtPurchaseCodeStore');

    $envatoTokenEmail = get_option('wdtEnvatoTokenEmail');

    // Get the remote info
    $remoteInformation = WDTTools::getRemoteInformation('wpdatatables', $purchaseCode, $envatoTokenEmail);

    // If a newer version is available, add the update
    if ($remoteInformation && version_compare(WDT_CURRENT_VERSION, $remoteInformation->new_version, '<')) {
        $transient->response[$wdtPluginSlug] = $remoteInformation;
    }

    return $transient;
}

add_filter('pre_set_site_transient_update_plugins', 'wdtCheckUpdate');

/**
 * @param $response
 * @param $action
 * @param $args
 *
 * @return bool|mixed
 */
function wdtCheckInfo($response, $action, $args)
{
    global $wdtPluginSlug;

    if ('plugin_information' !== $action) {
        return $response;
    }

    if (empty($args->slug)) {
        return $response;
    }

    $purchaseCode = get_option('wdtPurchaseCodeStore');

    $envatoTokenEmail = get_option('wdtEnvatoTokenEmail');

    if ($args->slug === $wdtPluginSlug) {
        return WDTTools::getRemoteInformation('wpdatatables', $purchaseCode, $envatoTokenEmail);
    }

    return $response;
}

add_filter('plugins_api', 'wdtCheckInfo', 10, 3);

function wdtAddMessageOnPluginsPage() {
    /** @var bool $activated */
    $activated = get_option('wdtActivated');

    /** @var string $url */
    $url = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-settings&activeTab=activation';

    /** @var string $redirect */
    $redirect = '<a href="' . $url . '" target="_blank">' . esc_html__('settings', 'wpdatatables') . '</a>';

    if (!$activated) {
        echo sprintf(' ' . __('To receive automatic updates license activation is required. Please visit %s to activate wpDataTables.', 'wpdatatables'), $redirect);
    }
}

add_action('in_plugin_update_message-' . $wdtPluginSlug, 'wdtAddMessageOnPluginsPage');

function wdtAddMessageOnUpdate($reply, $package, $updater) {
    if (isset($updater->skin->plugin_info['Name']) && $updater->skin->plugin_info['Name'] === 'wpDataTables') {
        /** @var string $url */
        $url = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-settings&activeTab=activation';

        /** @var string $redirect */
        $redirect = '<a href="' . $url . '" target="_blank">' . esc_html__('settings', 'wpdatatables') . '</a>';

        if (!$package) {
            return new WP_Error(
                'wpdatatables_not_activated',
                sprintf(' ' . __('To receive automatic updates license activation is required. Please visit %s to activate wpDataTables.', 'wpdatatables'), $redirect)
            );
        }

        return $reply;
    }

    return $reply;
}

add_filter('upgrader_pre_download', 'wdtAddMessageOnUpdate', 10, 4);

/**
 * Redirect on Welcome page after activate plugin
 */
function welcome_page_activation_redirect( $plugin ) {
    $filePath = plugin_basename(__FILE__);
    $filePathArr = explode('/', $filePath);
    $wdtPluginSlug = $filePathArr[0] . '/wpdatatables.php';

    if( $plugin == plugin_basename( $wdtPluginSlug ) && (isset($_GET['action']) && $_GET['action'] == 'activate')) {
        exit( wp_redirect( admin_url( 'admin.php?page=wpdatatables-welcome-page' ) ) );
    }
}

add_action( 'activated_plugin', 'welcome_page_activation_redirect' );



/**
 *  Add plugin action links Plugins page
 */
function wpdt_add_plugin_action_links( $links ) {

    // Settings link.
    $action_links['settings'] = '<a href="' . admin_url( 'admin.php?page=wpdatatables-settings' ) . '" aria-label="' . esc_attr__( 'Go to Settings', 'wpdatatables' ) . '">' . esc_html__( 'Settings', 'wpdatatables' ) . '</a>';

    // Add ons link.
    $action_links['addons'] = '<a href="' .  esc_url( 'https://wpdatatables.com/addons/' )  . '" aria-label="' . esc_attr__( 'Add-ons', 'wpdatatables' ) . '" style="color: #ff8c00;" target="_blank">' . esc_html__( 'Add-ons', 'wpdatatables' ) . '</a>';

    // Documentation link.
    $action_links['docs'] = '<a href="' . esc_url( 'https://wpdatatables.com/documentation/general/features-overview/' ) . '" aria-label="' . esc_attr__( 'Docs', 'wpdatatables' ) . '" target="_blank">' . esc_html__( 'Docs', 'wpdatatables' ) . '</a>';

    return array_merge( $action_links, $links );
}
add_filter( 'plugin_action_links_' . WDT_BASENAME , 'wpdt_add_plugin_action_links'  );


/**
 *  Add links next to plugin details on Plugins page
 */
function wpdt_plugin_row_meta( $links, $file, $plugin_data ) {

    if ( WDT_BASENAME === $file ) {
        // Show network meta links only when activated network wide.
        if ( is_network_admin() ) {
            return $links;
        }

        // Change AuthorURI link.
        if ( isset( $links[1] ) ){
            $author_uri = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                $plugin_data['AuthorURI'],
                $plugin_data['Author']
            );
            $links[1] = sprintf( __( 'By %s' ), $author_uri );
        }
        // Change View details link.
        if ( isset( $links[2] ) ) {
            $links[2] = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                esc_url( 'https://wpdatatables.com/features/'  ),
                esc_html__( 'View details' )
            );
        }
        // Add Docs and Premium support links
        $row_meta['docs'] = '<a href="' . esc_url( 'https://wpdatatables.com/documentation/general/features-overview/' ) . '" aria-label="' . esc_attr__( 'Docs', 'wpdatatables' ) . '" target="_blank">' . esc_html__( 'Docs', 'wpdatatables' ) . '</a>';
        $row_meta['support'] = '<a href="' . admin_url( 'admin.php?page=wpdatatables-support' ) . '" aria-label="' . esc_attr__( 'Support Center', 'wpdatatables' ) . '" target="_blank">' . esc_html__( 'Support Center', 'wpdatatables' ) . '</a>';

        return array_merge( $links, $row_meta );
    }

    return $links;

}

add_filter( 'plugin_row_meta', 'wpdt_plugin_row_meta' , 10, 3 );