<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Routes\Table;

use WPDataTables\Common\Rest\RestPermissions;
use WPDataTables\Controllers\Rest\Table\DeleteTableController;
use WPDataTables\Controllers\Rest\Table\GetTableController;
use WPDataTables\Controllers\Rest\Table\GetTableDataController;
use WPDataTables\Controllers\Rest\Table\GetTablesController;
use WPDataTables\Controllers\Rest\Table\SaveTableController;
use WPDataTables\Controllers\Rest\Table\UpdateTableController;
use WPDataTables\Vendor\DI\Container;
use WP_REST_Server;

/**
 * Table REST route group.
 *
 * @package WPDataTables\Routes\Table
 */
class Table
{
    /**
     * @param Container $container
     * @param string    $routeNamespace
     * @return void
     */
    public static function registerRoutes(Container $container, string $routeNamespace)
    {
        // GET /tables — list all tables. POST /tables — create a table.
        register_rest_route($routeNamespace, '/tables', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => $container->get(GetTablesController::class),
                'permission_callback' => static function () {
                    return RestPermissions::canListTables();
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => $container->get(SaveTableController::class),
                'permission_callback' => static function () {
                    return RestPermissions::canCreateTables();
                },
            ],
        ]);

        $idArg = [
            'id' => [
                'type'              => 'integer',
                'required'          => true,
                'sanitize_callback' => 'absint',
            ],
        ];

        // GET /tables/{id} — fetch config + columns. PUT /tables/{id} — update.
        // DELETE /tables/{id} — delete (preserves storage unless ?drop_storage=true).
        register_rest_route($routeNamespace, '/tables/(?P<id>\d+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => $container->get(GetTableController::class),
                'permission_callback' => static function ($request) {
                    return RestPermissions::canReadTableRequest($request);
                },
                'args'                => $idArg,
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => $container->get(UpdateTableController::class),
                'permission_callback' => static function ($request) {
                    return RestPermissions::canEditTableRequest($request);
                },
                'args'                => $idArg,
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => $container->get(DeleteTableController::class),
                'permission_callback' => static function ($request) {
                    return RestPermissions::canDeleteTableRequest($request);
                },
                'args'                => $idArg,
            ],
        ]);

        // GET /tables/{id}/data — the formatted data rows of a table.
        register_rest_route($routeNamespace, '/tables/(?P<id>\d+)/data', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => $container->get(GetTableDataController::class),
            'permission_callback' => static function ($request) {
                return RestPermissions::canReadTableRequest($request);
            },
            'args'                => $idArg,
        ]);
    }
}
