<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Rest\Table;

use WPDataTables\Controllers\Controller;
use WPDataTables\Common\Rest\RestSanitizer;
use WPDataTables\Services\Rest\TableReadService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * GET /wpdatatables/v1/tables/{id} — fetch a table's configuration + columns.
 *
 * @package WPDataTables\Controllers\Rest\Table
 */
class GetTableController extends Controller
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
        $tableId = RestSanitizer::id($data->get_param('id'));

        return new WP_REST_Response($this->tableReadService->getTableWithColumns($tableId), 200);
    }
}
