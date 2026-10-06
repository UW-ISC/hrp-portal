<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Rest\Column;

use WPDataTables\Controllers\Controller;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Common\Exceptions\NotFoundException;
use WPDataTables\Common\Rest\RestSanitizer;
use WPDataTables\Services\Table\TableConfigService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * PUT /wpdatatables/v1/tables/{id}/columns/{columnId} — update a column config.
 *
 * Reuses {@see TableConfigService::saveSingleColumn()} (the same per-column persist
 * the table-save flow uses). The route ids are forced onto the body so the
 * update can only touch the addressed column of the addressed table — a body
 * copied from another column can never re-point it. 404 if the column is missing
 * or not in the table. Returns the reloaded column.
 *
 * @package WPDataTables\Controllers\Rest\Column
 */
class UpdateColumnController extends Controller
{
    /** @var TableConfigService */
    private $tableConfigService;

    public function __construct(TableConfigService $tableConfigService)
    {
        $this->tableConfigService = $tableConfigService;
    }

    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response
     * @throws InvalidArgumentException When an id/body is missing/invalid.
     * @throws NotFoundException        When the column is missing / not in the table.
     * @throws \Exception               When the persist fails (DB error).
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Route-captured ids come from the URL, not get_param(): the column body
        // contains its own `id`/`table_id`, and WP ranks JSON-body params above
        // URL params, so get_param('id') would return the column id, not the
        // table id. Reading the URL params keeps routing and payload separate.
        $urlParams = $data->get_url_params();
        $tableId   = RestSanitizer::id($urlParams['id'] ?? null);
        $columnId  = RestSanitizer::id($urlParams['columnId'] ?? null);

        if ($tableId === 0 || $columnId === 0) {
            throw new InvalidArgumentException('A valid table id and column id are required.');
        }

        $existing = $this->tableConfigService->loadSingleColumnFromDB($columnId);

        if (!$existing || (int)$existing['table_id'] !== $tableId) {
            throw new NotFoundException('Column not found.');
        }

        $params = $data->get_json_params();

        if (empty($params) || !is_array($params)) {
            throw new InvalidArgumentException('A column configuration body is required.');
        }

        // Force the route ids so saveSingleColumn() updates the addressed row of
        // the addressed table (its update branch keys on id; table_id is used by
        // the wpdatatables_filter_update_column_array filter).
        $params['id']       = $columnId;
        $params['table_id'] = $tableId;

        $this->tableConfigService->saveSingleColumn($params);

        $column = $this->tableConfigService->loadSingleColumnFromDB($columnId);

        return new WP_REST_Response(['column' => $column], 200);
    }
}
