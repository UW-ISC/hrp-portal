<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Rest\Settings;

use WPDataTables\Controllers\Controller;
use WDTSettingsController;
use WP_REST_Request;
use WP_REST_Response;

/**
 * GET /wpdatatables/v1/settings — read the plugin settings.
 *
 * Settings are a singleton resource (no id). Reuses the same
 * `WDTSettingsController::getCurrentPluginConfig()` read the admin Settings
 * screen renders from (a shim into {@see \WPDataTables\Services\Settings\SettingsService}),
 * so admin and REST return the identical option set.
 *
 * @package WPDataTables\Controllers\Rest\Settings
 */
class GetSettingsController extends Controller
{
    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        return new WP_REST_Response(WDTSettingsController::getCurrentPluginConfig(), 200);
    }
}
