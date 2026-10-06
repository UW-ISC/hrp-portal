<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Rest\Table;

use WPDataTables\Controllers\Controller;
use WPDataTables\Services\Rest\TableReadService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * GET /wpdatatables/v1/tables — list all tables.
 *
 * @package WPDataTables\Controllers\Rest\Table
 */
class GetTablesController extends Controller
{
    /** @var TableReadService */
    private $tableReadService;

    public function __construct(TableReadService $tableReadService)
    {
        $this->tableReadService = $tableReadService;
    }

    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        return new WP_REST_Response($this->tableReadService->listTables(), 200);
    }
}
