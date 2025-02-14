<?php

namespace WDTIntegration;

defined('ABSPATH') or die('Access denied.');

// Full url to the SQL query root directory
define('WDT_SQLQ_ROOT_URL', WDT_STANDARD_INTEGRATIONS_URL . 'foreign-key/');
// Full path to the SQL query root directory
define('WDT_SQLQ_ROOT_PATH', WDT_STANDARD_INTEGRATIONS_PATH . 'foreign-key/');
// SQL query const
define('WDT_SQLQ_INTEGRATION', true);


/**
 * Class SQLQueryIntegration
 *
 * @package WDTIntegration
 */
class SQLQueryIntegration
{
    public static function init()
    {
        // Add SQL query settings block in column settings
        add_action('wpdatatables_add_mysql_settings_block', array('WDTIntegration\SQLQueryIntegration',
            'addSettingsBlock'));
    }

    /**
     * Adds SQL query block in column settings
     */
    public static function addSettingsBlock($connection)
    {
        ob_start();
        include 'templates/sql_query_settings_block.inc.php';
        $settingsBlock = ob_get_contents();
        ob_end_clean();
        echo $settingsBlock;
    }
}

SQLQueryIntegration::init();