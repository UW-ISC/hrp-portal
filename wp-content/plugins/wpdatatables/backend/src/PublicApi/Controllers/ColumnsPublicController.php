<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\PublicApi\Controllers;

use WPDataTables\Common\Exceptions\ForbiddenException;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Common\Exceptions\NotFoundException;
use WPDataTables\Common\Rest\RestSanitizer;
use WPDataTables\PublicApi\Permissions\PublicApiPermissions;
use WPDataTables\PublicApi\Routes\PublicRoutes;
use WPDataTables\Services\Rest\ColumnReadService;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Public REST controller for columns (read-only).
 *
 * @package WPDataTables\PublicApi\Controllers
 */
class ColumnsPublicController extends WP_REST_Controller
{
    /** @var ColumnReadService */
    private $columnReadService;

    /** @var PublicApiPermissions */
    private $permissions;

    public function __construct(ColumnReadService $columnReadService, PublicApiPermissions $permissions)
    {
        $this->namespace = PublicRoutes::NAMESPACE;
        $this->rest_base = 'tables/(?P<id>\d+)/columns';
        $this->columnReadService = $columnReadService;
        $this->permissions = $permissions;
    }

    /**
     * @return void
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/tables/(?P<id>\d+)/columns',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_items'],
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

        register_rest_route(
            $this->namespace,
            '/tables/(?P<id>\d+)/columns/(?P<columnId>\d+)',
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
                        'columnId' => [
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
        try {
            $urlParams = $request->get_url_params();
            $tableId   = RestSanitizer::id($urlParams['id'] ?? null);

            return rest_ensure_response([
                'data' => $this->columnReadService->listColumnsForPublicApi($tableId),
            ]);
        } catch (ForbiddenException $e) {
            return new WP_Error('wpdatatables_public_api_forbidden', $e->getMessage(), ['status' => 403]);
        } catch (NotFoundException $e) {
            return new WP_Error('wpdatatables_table_not_found', $e->getMessage(), ['status' => 404]);
        } catch (InvalidArgumentException $e) {
            return new WP_Error('wpdatatables_public_api_invalid', $e->getMessage(), ['status' => 400]);
        }
    }

    /**
     * @param WP_REST_Request $request
     * @return \WP_REST_Response|WP_Error
     */
    public function get_item($request)
    {
        try {
            $urlParams = $request->get_url_params();
            $tableId   = RestSanitizer::id($urlParams['id'] ?? null);
            $columnId  = RestSanitizer::id($urlParams['columnId'] ?? null);

            return rest_ensure_response([
                'data' => $this->columnReadService->getColumnForPublicApi($tableId, $columnId),
            ]);
        } catch (ForbiddenException $e) {
            return new WP_Error('wpdatatables_public_api_forbidden', $e->getMessage(), ['status' => 403]);
        } catch (NotFoundException $e) {
            return new WP_Error('wpdatatables_column_not_found', $e->getMessage(), ['status' => 404]);
        } catch (InvalidArgumentException $e) {
            return new WP_Error('wpdatatables_public_api_invalid', $e->getMessage(), ['status' => 400]);
        }
    }
}
