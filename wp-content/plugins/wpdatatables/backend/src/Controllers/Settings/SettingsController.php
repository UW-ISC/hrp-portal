<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Settings;

use WPDT\Melograno\UsageTracker\Collectors\Plugin\WpDataTablesCollector;
use WPDT\Melograno\UsageTracker\Core\UsageTracker;
use WDTSettingsController;

/**
 * SettingsController — admin-ajax handlers for the plugin-settings domain
 * (save settings, validate + save the Google Maps API key, clear the cache
 * error log).
 *
 * The nonce/cap checks and the wire format ($_POST in, `echo`/`exit` out) are
 * what the existing admin JS expects. The settings persistence flows through the
 * global `WDTSettingsController` facade (a shim into
 * {@see \WPDataTables\Services\Settings\SettingsService}), so REST and
 * admin-ajax converge at the service layer. The legacy global functions remain
 * as one-line delegators into this controller.
 *
 * Plain class by design (NOT extending the REST-shaped
 * {@see \WPDataTables\Controllers\Controller}): admin-ajax controllers are
 * ajax-shaped.
 *
 * @package WPDataTables\Controllers\Settings
 */
class SettingsController
{
    /**
     * Save the plugin settings.
     *
     * @return void
     */
    public function savePluginSettings()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')) {
            exit();
        }

        $settings = apply_filters('wpdatatables_before_save_settings', $_POST['settings']);

        if (is_array($settings) && array_key_exists('wdtUsageTrackingEnabled', $settings)) {
            $enabled = (bool) $settings['wdtUsageTrackingEnabled'];
            $armNotice = false;

            if (
                ! $enabled
                && get_option('wpdatatables_usage_tracking_settings_optout_notice_handled') !== 'yes'
            ) {
                $armNotice = true;
                update_option('wpdatatables_usage_tracking_settings_optout_notice_handled', 'yes', true);
            }

            $usageSettings = array(
                'usageTrackingEnabled' => $enabled,
            );
            UsageTracker::updateSettings($usageSettings, new WpDataTablesCollector(), $armNotice);
            unset($settings['wdtUsageTrackingEnabled']);
        }

        WDTSettingsController::saveSettings($settings);
        exit();
    }

    /**
     * Validate the Google Maps API key against the Geocoding API and persist it.
     *
     * @return void
     */
    public function saveGoogleMapsApiKey()
    {
        $settings = $_POST['apiKey'];
        // Construct the API request URL
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=New+York&key=' . $settings;

        // Make the API request
        $response = file_get_contents($url);

        if ($response !== false) {
            // Decode the JSON response
            $data = json_decode($response, true);

            // Check if the API key is valid
            if ($data && isset($data['status']) && $data['status'] === 'OK') {
                echo esc_html_e('API key is valid.', 'wpdatatables');
                update_option('wdtGoogleApiMapsValidated', true);
                update_option('wdtGoogleApiMaps', $settings);
            } elseif ($data && isset($data['status'])) {
                $settings = '';
                update_option('wdtGoogleApiMapsValidated', false);
                switch ($data['status']) {
                    case 'ZERO_RESULTS':
                        esc_html_e('No results found. The Google Maps Geocoding API may not be enabled.', 'wpdatatables');
                        break;
                    case 'OVER_QUERY_LIMIT':
                        esc_html_e('The Google Maps API usage limit has been exceeded. Check your billing status.', 'wpdatatables');
                        break;
                    case 'REQUEST_DENIED':
                        esc_html_e('The Google Maps API request was denied. Check your API key and check your billing status.', 'wpdatatables');
                        break;
                    case 'INVALID_REQUEST':
                        esc_html_e('Invalid request. Check your API key and parameters.', 'wpdatatables');
                        break;
                    default:
                        esc_html_e('Unknown error occurred.', 'wpdatatables');
                }
            } else {
                $settings = '';
                update_option('wdtGoogleApiMapsValidated', false);
                esc_html_e('API key is invalid or there was an error.', 'wpdatatables');
            }
        } else {
            $settings = '';
            update_option('wdtGoogleApiMapsValidated', false);
            esc_html_e('An error occurred while making the API request.', 'wpdatatables');
        }
        WDTSettingsController::saveGoogleApiMaps($settings);
    }

    /**
     * Clear the `log_errors` column in the cache table.
     *
     * @return void
     */
    public function deleteLogErrorsCache()
    {
        global $wpdb;

        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')) {
            exit();
        }
        $result = '';

        $wpdb->query("UPDATE " . $wpdb->prefix . "wpdatatables_cache SET log_errors = ''");

        if ($wpdb->last_error != '') {
            $result = 'Database error: ' . $wpdb->last_error;
        }

        echo $result;
        exit();
    }
}
