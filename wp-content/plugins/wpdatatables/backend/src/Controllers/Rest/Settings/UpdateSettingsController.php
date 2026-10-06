<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Rest\Settings;

use WPDataTables\Controllers\Controller;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WDTSettingsController;
use WP_REST_Request;
use WP_REST_Response;

/**
 * PUT|PATCH /wpdatatables/v1/settings — update the plugin settings.
 *
 * Same persistence path as the admin `savePluginSettings` handler: the JSON body
 * is the settings map, run through the `wpdatatables_before_save_settings`
 * filter and handed to `WDTSettingsController::saveSettings()` (a shim into
 * {@see \WPDataTables\Services\Settings\SettingsService}). `saveSettings()`
 * persists each supplied key via `update_option()` (and sanitizes internally),
 * so send the full settings map as returned by `GET /settings`. Returns the
 * freshly-read config so the caller sees the applied state.
 *
 * @package WPDataTables\Controllers\Rest\Settings
 */
class UpdateSettingsController extends Controller
{
    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response
     * @throws InvalidArgumentException When the body is missing/invalid.
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        $params = $data->get_json_params();

        if (empty($params) || !is_array($params)) {
            throw new InvalidArgumentException('A settings body is required.');
        }

        // Filter then persist, same as the admin path. saveSettings()
        // applies stripslashes_deep + sanitizeSettings itself.
        $settings = apply_filters('wpdatatables_before_save_settings', $params);

        WDTSettingsController::saveSettings($settings);

        return new WP_REST_Response(WDTSettingsController::getCurrentPluginConfig(), 200);
    }
}
