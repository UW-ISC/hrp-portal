<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Routes\Chart;

use WPDataTables\Common\Rest\RestPermissions;
use WPDataTables\Controllers\Rest\Chart\DeleteChartController;
use WPDataTables\Controllers\Rest\Chart\GetChartController;
use WPDataTables\Controllers\Rest\Chart\GetChartsController;
use WPDataTables\Controllers\Rest\Chart\SaveChartController;
use WPDataTables\Controllers\Rest\Chart\UpdateChartController;
use WPDataTables\Vendor\DI\Container;
use WP_REST_Server;

/**
 * Chart REST route group.
 *
 * @package WPDataTables\Routes\Chart
 */
class Chart
{
    /**
     * @param Container $container
     * @param string    $routeNamespace
     * @return void
     */
    public static function registerRoutes(Container $container, string $routeNamespace)
    {
        // GET /charts — list all charts. POST /charts — create a chart.
        register_rest_route($routeNamespace, '/charts', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => $container->get(GetChartsController::class),
                'permission_callback' => static function () {
                    return RestPermissions::canListCharts();
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => $container->get(SaveChartController::class),
                'permission_callback' => static function () {
                    return RestPermissions::canCreateCharts();
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

        // GET /charts/{id} — fetch config. PUT /charts/{id} — update.
        // DELETE /charts/{id} — delete.
        register_rest_route($routeNamespace, '/charts/(?P<id>\d+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => $container->get(GetChartController::class),
                'permission_callback' => static function ($request) {
                    return RestPermissions::canReadChartRequest($request);
                },
                'args'                => $idArg,
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => $container->get(UpdateChartController::class),
                'permission_callback' => static function ($request) {
                    return RestPermissions::canEditChartRequest($request);
                },
                'args'                => $idArg,
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => $container->get(DeleteChartController::class),
                'permission_callback' => static function ($request) {
                    return RestPermissions::canDeleteChartRequest($request);
                },
                'args'                => $idArg,
            ],
        ]);
    }
}
