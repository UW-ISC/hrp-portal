<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\PublicApi\Routes;

use WPDataTables\Common\Rest\RestPermissions;
use WPDataTables\PublicApi\Controllers\ApiKeysAdminController;
use WPDataTables\PublicApi\Controllers\PublicApiSettingsAdminController;
use WPDataTables\PublicApi\Permissions\PublicApiPermissions;
use WPDataTables\Vendor\DI\Container;
use WP_REST_Server;

/**
 * Admin routes for public API key management.
 *
 * @package WPDataTables\PublicApi\Routes
 */
class PublicApiAdminRoutes
{
    /**
     * @param Container $container
     * @param string    $namespace
     * @return void
     */
    public static function registerRoutes(Container $container, string $namespace)
    {
        $keysController = $container->get(ApiKeysAdminController::class);
        $settingsController = $container->get(PublicApiSettingsAdminController::class);

        register_rest_route(
            $namespace,
            '/developer/public-api/settings',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$settingsController, 'getSettings'],
                    'permission_callback' => [self::class, 'canManageSettings'],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [$settingsController, 'updateSettings'],
                    'permission_callback' => [self::class, 'canManageSettings'],
                ],
            ]
        );

        register_rest_route(
            $namespace,
            '/developer/api-keys',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$keysController, 'listKeys'],
                    'permission_callback' => [self::class, 'canManageKeys'],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [$keysController, 'createKey'],
                    'permission_callback' => [self::class, 'canManageKeys'],
                ],
            ]
        );

        register_rest_route(
            $namespace,
            '/developer/api-keys/(?P<id>[a-zA-Z0-9\-]+)',
            [
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [$keysController, 'revokeKey'],
                    'permission_callback' => [self::class, 'canManageKeys'],
                    'args'                => [
                        'id' => [
                            'required'          => true,
                            'type'              => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @return bool
     */
    public static function canManageSettings()
    {
        return RestPermissions::canManageTables();
    }

    /**
     * @return bool
     */
    public static function canManageKeys()
    {
        return RestPermissions::canManageTables() && PublicApiPermissions::isEnabled();
    }
}
