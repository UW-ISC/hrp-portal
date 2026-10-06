<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Routes\Column;

use WPDataTables\Common\Rest\RestPermissions;
use WPDataTables\Controllers\Rest\Column\DeleteColumnController;
use WPDataTables\Controllers\Rest\Column\GetColumnController;
use WPDataTables\Controllers\Rest\Column\GetColumnsController;
use WPDataTables\Controllers\Rest\Column\UpdateColumnController;
use WPDataTables\Vendor\DI\Container;
use WP_REST_Server;

/**
 * Column REST route group.
 *
 * @package WPDataTables\Routes\Column
 */
class Column
{
    /**
     * @param Container $container
     * @param string    $routeNamespace
     * @return void
     */
    public static function registerRoutes(Container $container, string $routeNamespace)
    {
        $tableIdArg = [
            'id' => [
                'type'              => 'integer',
                'required'          => true,
                'sanitize_callback' => 'absint',
            ],
        ];

        // GET /tables/{id}/columns — list the table's column configs.
        register_rest_route($routeNamespace, '/tables/(?P<id>\d+)/columns', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => $container->get(GetColumnsController::class),
                'permission_callback' => static function ($request) {
                    return RestPermissions::canReadTableRequest($request);
                },
                'args'                => $tableIdArg,
            ],
        ]);

        $columnArgs = $tableIdArg + [
            'columnId' => [
                'type'              => 'integer',
                'required'          => true,
                'sanitize_callback' => 'absint',
            ],
        ];

        // GET /tables/{id}/columns/{columnId} — fetch. PUT — update config.
        // DELETE — delete (Manual tables only).
        register_rest_route($routeNamespace, '/tables/(?P<id>\d+)/columns/(?P<columnId>\d+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => $container->get(GetColumnController::class),
                'permission_callback' => static function ($request) {
                    return RestPermissions::canReadTableRequest($request);
                },
                'args'                => $columnArgs,
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => $container->get(UpdateColumnController::class),
                'permission_callback' => static function ($request) {
                    return RestPermissions::canEditTableRequest($request);
                },
                'args'                => $columnArgs,
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => $container->get(DeleteColumnController::class),
                'permission_callback' => static function ($request) {
                    return RestPermissions::canEditTableRequest($request);
                },
                'args'                => $columnArgs,
            ],
        ]);
    }
}
