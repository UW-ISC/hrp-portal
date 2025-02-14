<?php

namespace WDTIntegration;

defined('ABSPATH') or die('Access denied.');

// Full url to the Separate DB connection root directory
define('WDT_SDBC_ROOT_URL', WDT_STANDARD_INTEGRATIONS_URL . 'separate-db-connection/');
// Full path to the Separate DB connection root directory
define('WDT_SDBC_ROOT_PATH', WDT_STANDARD_INTEGRATIONS_PATH . 'separate-db-connection/');
// Separate DB connection const
define('WDT_SDBC_INTEGRATION', true);

/**
 * Class SeparateDBConnection
 *
 * @package WDTIntegration
 */
class SeparateDBConnection
{
    public static function init()
    {
        // Display select-box for separate db connection in wizard
        add_action('wpdatatables_add_separate_connection_element_in_wizard', array('WDTIntegration\SeparateDBConnection',
            'addConnectionElementInWizard'));
        // Display tab of separate db connection
        add_action('wpdatatables_add_tab_in_main_settings', array('WDTIntegration\SeparateDBConnection',
            'addConnectionConfiguration'));
    }

    /**
     * Add connection element in table wizard
     */
    public static function addConnectionElementInWizard()
    {
        ob_start();
        include 'templates/connection_element_table_wizard.inc.php';
        $connectionElement = ob_get_contents();
        ob_end_clean();
        echo $connectionElement;

    }

    /**
     * Add template for adding new connection configuration
     */
    public static function addConnectionConfiguration()
    {
        ob_start();
        include 'templates/add_connection_configuration.inc.php';
        $connectionConfigurationElements = ob_get_contents();
        ob_end_clean();
        echo $connectionConfigurationElements;

    }
}

SeparateDBConnection::init();