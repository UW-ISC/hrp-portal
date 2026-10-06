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
use WPDataTables\Services\Rest\TableReadService;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Public REST controller for tables (read-only).
 *
 * @package WPDataTables\PublicApi\Controllers
 */
class TablesPublicController extends WP_REST_Controller
{
    /** @var TableReadService */
    private $tableReadService;

    /** @var PublicApiPermissions */
    private $permissions;

    public function __construct(TableReadService $tableReadService, PublicApiPermissions $permissions)
    {
        $this->namespace = PublicRoutes::NAMESPACE;
        $this->rest_base = 'tables';
        $this->tableReadService = $tableReadService;
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

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>\d+)/data',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_item_data'],
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
            'data' => $this->tableReadService->listTablesForPublicApi(),
        ]);
    }

    /**
     * @param WP_REST_Request $request
     * @return \WP_REST_Response|WP_Error
     */
    public function get_item($request)
    {
        try {
            $tableId = RestSanitizer::id($request->get_param('id'));

            return rest_ensure_response([
                'data' => $this->tableReadService->getTableWithColumnsForPublicApi($tableId),
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
    public function get_item_data($request)
    {
        try {
            $tableId = RestSanitizer::id($request->get_param('id'));

            return rest_ensure_response([
                'data' => $this->tableReadService->getTableDataForPublicApi($tableId, $request),
            ]);
        } catch (ForbiddenException $e) {
            return new WP_Error('wpdatatables_public_api_forbidden', $e->getMessage(), ['status' => 403]);
        } catch (NotFoundException $e) {
            return new WP_Error('wpdatatables_table_not_found', $e->getMessage(), ['status' => 404]);
        } catch (InvalidArgumentException $e) {
            return new WP_Error('wpdatatables_public_api_invalid', $e->getMessage(), ['status' => 400]);
        } catch (\Exception $e) {
            return new WP_Error('wpdatatables_table_data_error', $e->getMessage(), ['status' => 500]);
        }
    }
}
