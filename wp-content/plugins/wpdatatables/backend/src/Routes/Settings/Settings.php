<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Routes\Settings;

use WPDataTables\Common\Rest\RestPermissions;
use WPDataTables\Controllers\Rest\Settings\GetSettingsController;
use WPDataTables\Controllers\Rest\Settings\UpdateSettingsController;
use WPDataTables\Vendor\DI\Container;
use WP_REST_Server;

/**
 * Settings REST route group.
 *
 * Settings are a singleton resource — one flat `/settings` endpoint, no id. Pure
 * WP wiring (mirrors {@see \WPDataTables\Routes\Table\Table}): controllers
 * resolved from the container, gated on the manage capability.
 *
 * @package WPDataTables\Routes\Settings
 */
class Settings
{
    /**
     * @param Container $container
     * @param string    $routeNamespace
     * @return void
     */
    public static function registerRoutes(Container $container, string $routeNamespace)
    {
        $permission = static function () {
            return RestPermissions::canAccessSettings();
        };

        // GET /settings — read the plugin settings.
        // PUT|PATCH /settings — update them (EDITABLE covers POST/PUT/PATCH).
        register_rest_route($routeNamespace, '/settings', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => $container->get(GetSettingsController::class),
                'permission_callback' => $permission,
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => $container->get(UpdateSettingsController::class),
                'permission_callback' => $permission,
            ],
        ]);
    }
}
