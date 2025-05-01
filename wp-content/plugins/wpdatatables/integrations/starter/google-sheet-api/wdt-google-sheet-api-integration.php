<?php

namespace WDTIntegration;

use WDTSettingsController;

defined('ABSPATH') or die('Access denied.');

// Full url to the Separate DB connection root directory
define('WDT_GSAPI_ROOT_URL', WDT_STARTER_INTEGRATIONS_URL . 'google-sheet-api/');
// Full path to the Separate DB connection root directory
define('WDT_GSAPI_ROOT_PATH', WDT_STARTER_INTEGRATIONS_PATH . 'google-sheet-api/');
// Separate DB connection const
define('WDT_GSAPI_INTEGRATION', true);

/**
 * Class GoogleSheetAPI
 *
 * @package WDTIntegration
 */
class GoogleSheetAPI
{
    public static function init()
    {
        // Display tab of Google Sheet API
        add_action('wpdatatables_add_tab_in_main_settings', array('WDTIntegration\GoogleSheetAPI',
            'addGoogleSheetAPIConfiguration'));

        // Enqueue scripts
        add_action('wpdatatables_enqueue_on_settings_page', array('WDTIntegration\GoogleSheetAPI',
            'adminEnqueueScripts'));

        add_action('wp_ajax_wpdatatables_save_google_settings', array('WDTIntegration\GoogleSheetAPI', 'saveSettings'));
        add_action('wp_ajax_wpdatatables_delete_google_settings', array('WDTIntegration\GoogleSheetAPI',
            'deleteSettings'));
    }

    /**
     * Add template for adding new connection configuration
     */
    public static function addGoogleSheetAPIConfiguration()
    {
        ob_start();
        include 'templates/add_google_sheet_api_configuration.inc.php';
        $connectionConfigurationElements = ob_get_contents();
        ob_end_clean();
        echo $connectionConfigurationElements;

    }

    public static function adminEnqueueScripts()
    {
        wp_enqueue_script('wdt-gsapi-backend', WDT_GSAPI_ROOT_URL . 'js/wdt-gsapi-settings.js', array(), WDT_CURRENT_VERSION, true);
    }

    /**
     * Save Google Sheet API settings
     */
    public static function saveSettings()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')) {
            exit();
        }
        $result = [];
        $settings = json_decode(stripslashes_deep($_POST['settings']), true);
        if (json_last_error() === JSON_ERROR_NONE) {
            WDTSettingsController::saveGoogleSettings($settings);
            $result['link'] = admin_url('admin.php?page=wpdatatables-settings#google-sheet-api-settings');
        } else {
            $result['error'] = 'Data don\'t have valid JSON format';
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Delete Google Sheet API settings
     */
    public static function deleteSettings()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')) {
            exit();
        }

        update_option('wdtGoogleSettings', '');
        update_option('wdtGoogleToken', '');

        echo admin_url('admin.php?page=wpdatatables-settings#google-sheet-api-settings');
        exit();
    }
}

GoogleSheetAPI::init();