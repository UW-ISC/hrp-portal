<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\PublicApi\Controllers;

use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Common\Exceptions\NotFoundException;
use WPDataTables\Common\Rest\RestSanitizer;
use WPDataTables\PublicApi\Permissions\PublicApiPermissions;
use WPDataTables\PublicApi\Routes\PublicRoutes;
use WPDataTables\Services\Rest\ChartReadService;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Public REST controller for charts (read-only).
 *
 * @package WPDataTables\PublicApi\Controllers
 */
class ChartsPublicController extends WP_REST_Controller
{
    /** @var ChartReadService */
    private $chartReadService;

    /** @var PublicApiPermissions */
    private $permissions;

    public function __construct(ChartReadService $chartReadService, PublicApiPermissions $permissions)
    {
        $this->namespace = PublicRoutes::NAMESPACE;
        $this->rest_base = 'charts';
        $this->chartReadService = $chartReadService;
        $this->permissions = $permissions;
    }

    /**
     * @return void
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_items'],
                    'permission_callback' => [$this->permissions, 'canRead'],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>\d+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_item'],
                    'permission_callback' => [$this->permissions, 'canRead'],
                    'args'                => [
                        'id' => [
                            'required'          => true,
                            'type'              => 'integer',
                            'sanitize_callback' => 'absint',
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @param WP_REST_Request $request
     * @return \WP_REST_Response|WP_Error
     */
    public function get_items($request)
    {
        return rest_ensure_response([
            'data' => $this->chartReadService->listCharts(),
        ]);
    }

    /**
     * @param WP_REST_Request $request
     * @return \WP_REST_Response|WP_Error
     */
    public function get_item($request)
    {
        try {
            $chartId = RestSanitizer::id($request->get_param('id'));

            return rest_ensure_response([
                'data' => $this->chartReadService->getChart($chartId),
            ]);
        } catch (NotFoundException $e) {
            return new WP_Error('wpdatatables_chart_not_found', $e->getMessage(), ['status' => 404]);
        } catch (InvalidArgumentException $e) {
            return new WP_Error('wpdatatables_public_api_invalid', $e->getMessage(), ['status' => 400]);
        }
    }
}
