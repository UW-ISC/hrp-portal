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
use WPDataTables\Services\Rest\TableDataWriteService;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Public REST controller for table row writes (edit/delete scopes).
 *
 * @package WPDataTables\PublicApi\Controllers
 */
class TableDataPublicController extends WP_REST_Controller
{
    /** @var TableDataWriteService */
    private $tableDataWriteService;

    /** @var PublicApiPermissions */
    private $permissions;

    public function __construct(TableDataWriteService $tableDataWriteService, PublicApiPermissions $permissions)
    {
        $this->namespace = PublicRoutes::NAMESPACE;
        $this->rest_base = 'tables';
        $this->tableDataWriteService = $tableDataWriteService;
        $this->permissions = $permissions;
    }

    /**
     * @return void
     */
    public function register_routes()
    {
        $tableIdArg = [
            'id' => [
                'required'          => true,
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
            ],
        ];

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>\d+)/data/rows',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [$this, 'create_item'],
                    'permission_callback' => [$this->permissions, 'canEdit'],
                    'args'                => $tableIdArg,
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [$this, 'delete_items'],
                    'permission_callback' => [$this->permissions, 'canDelete'],
                    'args'                => $tableIdArg,
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>\d+)/data/rows/(?P<rowId>[^/]+)',
            [
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [$this, 'update_item'],
                    'permission_callback' => [$this->permissions, 'canEdit'],
                    'args'                => array_merge($tableIdArg, [
                        'rowId' => [
                            'required'          => true,
                            'type'              => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ]),
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [$this, 'delete_item'],
                    'permission_callback' => [$this->permissions, 'canDelete'],
                    'args'                => array_merge($tableIdArg, [
                        'rowId' => [
                            'required'          => true,
                            'type'              => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ]),
                ],
            ]
        );
    }

    /**
     * @param WP_REST_Request $request
     * @return \WP_REST_Response|WP_Error
     */
    public function create_item($request)
    {
        try {
            $tableId = RestSanitizer::id($request->get_param('id'));
            $row = $this->extractRowPayload($request);

            return rest_ensure_response([
                'data' => $this->tableDataWriteService->createRow($tableId, $row),
            ]);
        } catch (NotFoundException $e) {
            return new WP_Error('wpdatatables_table_not_found', $e->getMessage(), ['status' => 404]);
        } catch (ForbiddenException $e) {
            return new WP_Error('wpdatatables_public_api_forbidden', $e->getMessage(), ['status' => 403]);
        } catch (InvalidArgumentException $e) {
            return new WP_Error('wpdatatables_public_api_invalid', $e->getMessage(), ['status' => 400]);
        }
    }

    /**
     * @param WP_REST_Request $request
     * @return \WP_REST_Response|WP_Error
     */
    public function update_item($request)
    {
        try {
            $tableId = RestSanitizer::id($request->get_param('id'));
            $rowId = sanitize_text_field((string) $request->get_param('rowId'));
            $row = $this->extractRowPayload($request);

            return rest_ensure_response([
                'data' => $this->tableDataWriteService->updateRow($tableId, $rowId, $row),
            ]);
        } catch (NotFoundException $e) {
            return new WP_Error('wpdatatables_table_not_found', $e->getMessage(), ['status' => 404]);
        } catch (ForbiddenException $e) {
            return new WP_Error('wpdatatables_public_api_forbidden', $e->getMessage(), ['status' => 403]);
        } catch (InvalidArgumentException $e) {
            return new WP_Error('wpdatatables_public_api_invalid', $e->getMessage(), ['status' => 400]);
        }
    }

    /**
     * @param WP_REST_Request $request
     * @return \WP_REST_Response|WP_Error
     */
    public function delete_item($request)
    {
        try {
            $tableId = RestSanitizer::id($request->get_param('id'));
            $rowId = sanitize_text_field((string) $request->get_param('rowId'));
            $this->tableDataWriteService->deleteRow($tableId, $rowId);

            return rest_ensure_response([
                'data' => [
                    'deleted' => true,
                    'id'      => $rowId,
                ],
            ]);
        } catch (NotFoundException $e) {
            return new WP_Error('wpdatatables_table_not_found', $e->getMessage(), ['status' => 404]);
        } catch (ForbiddenException $e) {
            return new WP_Error('wpdatatables_public_api_forbidden', $e->getMessage(), ['status' => 403]);
        } catch (InvalidArgumentException $e) {
            return new WP_Error('wpdatatables_public_api_invalid', $e->getMessage(), ['status' => 400]);
        }
    }

    /**
     * @param WP_REST_Request $request
     * @return \WP_REST_Response|WP_Error
     */
    public function delete_items($request)
    {
        try {
            $tableId = RestSanitizer::id($request->get_param('id'));
            $params = $request->get_json_params();
            if (!is_array($params)) {
                $params = $request->get_params();
            }

            $ids = $params['ids'] ?? [];
            if (!is_array($ids)) {
                throw new InvalidArgumentException('ids must be an array.');
            }

            return rest_ensure_response([
                'data' => $this->tableDataWriteService->deleteRows($tableId, $ids),
            ]);
        } catch (NotFoundException $e) {
            return new WP_Error('wpdatatables_table_not_found', $e->getMessage(), ['status' => 404]);
        } catch (ForbiddenException $e) {
            return new WP_Error('wpdatatables_public_api_forbidden', $e->getMessage(), ['status' => 403]);
        } catch (InvalidArgumentException $e) {
            return new WP_Error('wpdatatables_public_api_invalid', $e->getMessage(), ['status' => 400]);
        }
    }

    /**
     * @param WP_REST_Request $request
     * @return array<string, mixed>
     */
    private function extractRowPayload(WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = $request->get_params();
        }

        $row = $params['row'] ?? null;
        if (!is_array($row)) {
            throw new InvalidArgumentException('Request body must include a "row" object.');
        }

        return $row;
    }
}
