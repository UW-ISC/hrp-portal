<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Rest\Column;

use WPDataTables\Controllers\Controller;
use WPDataTables\Common\Rest\RestSanitizer;
use WPDataTables\Services\Rest\ColumnReadService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * GET /wpdatatables/v1/tables/{id}/columns — list a table's column configs.
 *
 * @package WPDataTables\Controllers\Rest\Column
 */
class GetColumnsController extends Controller
{
    /** @var ColumnReadService */
    private $columnReadService;

    public function __construct(ColumnReadService $columnReadService)
    {
        $this->columnReadService = $columnReadService;
    }

    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        $urlParams = $data->get_url_params();
        $tableId   = RestSanitizer::id($urlParams['id'] ?? null);

        return new WP_REST_Response($this->columnReadService->listColumns($tableId), 200);
    }
}
