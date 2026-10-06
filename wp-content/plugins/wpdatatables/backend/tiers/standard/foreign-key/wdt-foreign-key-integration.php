<?php

namespace WDTIntegration;

defined('ABSPATH') or die('Access denied.');

// Full url to the Foreign Key root directory
define('WDT_FKEY_ROOT_URL', WDT_STANDARD_INTEGRATIONS_URL . 'foreign-key/');
// Full path to the Foreign Key root directory
define('WDT_FKEY_ROOT_PATH', WDT_STANDARD_INTEGRATIONS_PATH . 'foreign-key/');
// Foreign Key const
define('WDT_FKEY_INTEGRATION', true);


/**
 * Class ForeignKeyIntegration
 *
 * @package WDTIntegration
 */
class ForeignKeyIntegration
{
    public static function init()
    {
        // Add foreign key settings block in column settings
        add_action('wpdatatables_add_foreign_key_block', array('WDTIntegration\ForeignKeyIntegration',
            'addSettingsBlock'));

        // Add foreign key configure modal in admin area
        add_action('wpdatatables_admin_after_edit', array('WDTIntegration\ForeignKeyIntegration', 'addConfigureModal'));

    }

    /**
     * Adds foreign key block in column settings
     */
    public static function addSettingsBlock()
    {
        ob_start();
        include 'templates/foreign_key_settings_block.inc.php';
        $settingsBlock = ob_get_contents();
        ob_end_clean();
        echo $settingsBlock;
    }

    /**
     * Adds Configure foreign key modal
     */
    public static function addConfigureModal($connection)
    {
        ob_start();
        include 'templates/foreign_key_config.inc.php';
        $configureModal = ob_get_contents();
        ob_end_clean();
        echo $configureModal;
    }
}

ForeignKeyIntegration::init();