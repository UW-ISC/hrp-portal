<?php

namespace WDTIntegration;

defined('ABSPATH') or die('Access denied.');

use WDTConfigController;
use WDTSQLConstructor;

// Full url to the WDT SQL Constructor root directory
define('WDT_SQLC_ROOT_URL', WDT_STANDARD_INTEGRATIONS_URL . 'sql-constructor/');
// Full path to the WDT SQL Constructor root directory
define('WDT_SQLC_ROOT_PATH', WDT_STANDARD_INTEGRATIONS_PATH . 'sql-constructor/');
// WDT SQL Constructor const
define('WDT_SQLC_INTEGRATION', true);


/**
 * Class SqlConstructor
 *
 * @package WDTIntegration
 */
class SqlConstructor
{
    public static function init()
    {
        // Display new table types in table wizard
        add_action('wpdatatables_add_table_constructor_type_in_wizard', array('WDTIntegration\SqlConstructor',
            'addNewTableTypes'));
        // Display new constructor steps templates
        add_action('wpdatatables_add_constructor_step_in_wizard', array('WDTIntegration\SqlConstructor',
            'addNewConstructorStep'));
        // Ajax actions for new SQL Constructor
        add_action('wp_ajax_wpdatatables_generate_mysql_based_query', array('WDTIntegration\SqlConstructor',
            'wdtGenerateMySqlBasedQuery'));
        add_action('wp_ajax_wpdatatables_generate_wp_based_query', array('WDTIntegration\SqlConstructor',
            'wdtGenerateWPBasedQuery'));
        add_action('wp_ajax_wpdatatables_refresh_wp_query_preview', array('WDTIntegration\SqlConstructor',
            'wdtRefreshWPQueryPreview'));
        add_action('wp_ajax_wpdatatables_constructor_generate_wdt', array('WDTIntegration\SqlConstructor',
            'wdtConstructorGenerateWDT'));
        add_action('wp_ajax_wpdatatables_constructor_get_mysql_table_columns', array('WDTIntegration\SqlConstructor',
            'wdtConstructorGetMySqlTableColumns'));
    }

    /**
     * Adds new table types options in table wizard
     */
    public static function addNewTableTypes()
    {
        ob_start();
        include 'templates/new_table_type_block.inc.php';
        $newTableTypes = ob_get_contents();
        ob_end_clean();
        echo $newTableTypes;

    }

    /**
     * Adds new table types options in table wizard
     */
    public static function addNewConstructorStep()
    {
        ob_start();
        include 'templates/constructor_1_3.inc.php';
        include 'templates/constructor_1_4.inc.php';
        $newConstructorStep = ob_get_contents();
        ob_end_clean();
        echo $newConstructorStep;

    }

    /**
     * Action for generating a WP-based MySQL query
     */
    public static function wdtGenerateWPBasedQuery()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
            exit();
        }

        $tableData = WDTConfigController::sanitizeGeneratedSQLTableData($_POST['tableData']);
        $tableData = apply_filters('wpdatatables_before_generate_wp_based_query', $tableData);

        // $constructor = new wpDataTableConstructor();
        $constructor = new WDTSQLConstructor();
        $constructor->generateWPBasedQuery($tableData);
        $result = array(
            'query' => $constructor->getQuery(),
            'preview' => $constructor->getQueryPreview()
        );

        echo json_encode($result);
        exit();
    }

    /**
     * Action for refreshing the WP-based query
     */
    public static function wdtRefreshWPQueryPreview()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
            exit();
        }

        $query = $_POST['query'];

        //$constructor = new wpDataTableConstructor($_POST['connection']);
        $constructor = new WDTSQLConstructor($_POST['connection']);
        $constructor->setQuery($query);

        echo $constructor->getQueryPreview($_POST['connection']);
        exit();
    }

    /**
     * Action for generating the table from query/constructed table data
     */
    public static function wdtConstructorGenerateWDT()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
            exit();
        }

        $tableData = $_POST['table_data'];

        //$constructor = new wpDataTableConstructor($tableData['connection']);
        $constructor = new WDTSQLConstructor($tableData['connection']);
        $res = $constructor->generateWdtBasedOnQuery($tableData);
        if (empty($res->error)) {
            $res->link = get_admin_url() . "admin.php?page=wpdatatables-constructor&source&table_id={$res->table_id}";
        }

        echo json_encode($res);
        exit();
    }

    /**
     * Request the column list for the selected tables
     */
    public static function wdtConstructorGetMySqlTableColumns()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
            exit();
        }
        if (isset($_POST['tables'])) {
            $tables = array_map('sanitize_text_field', $_POST['tables']);
            $columns = WDTSQLConstructor::listMySQLColumns($tables, $_POST['connection']);
        } else {
            $columns = array('allColumns' => array(), 'sortedColumns' => array());
        }
        echo json_encode($columns);
        exit();
    }

    /**
     * Action for generating a WP-based MySQL query
     */
    public static function wdtGenerateMySqlBasedQuery()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
            exit();
        }

        $tableData = WDTConfigController::sanitizeGeneratedSQLTableData($_POST['tableData']);
        $tableData = apply_filters('wpdatatables_before_generate_mysql_based_query', $tableData);

        //$constructor = new wpDataTableConstructor($tableData['connection']);
        $constructor = new WDTSQLConstructor($tableData['connection']);
        $constructor->generateMySQLBasedQuery($tableData);
        $result = array(
            'query' => $constructor->getQuery(),
            'preview' => $constructor->getQueryPreview($tableData['connection'])
        );

        echo json_encode($result);
        exit();
    }

}

SqlConstructor::init();