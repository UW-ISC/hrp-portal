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
use wpDataTableConstructor;
use WP_REST_Request;
use WP_REST_Response;

/**
 * DELETE /wpdatatables/v1/tables/{id}/columns/{columnId} — delete a column.
 *
 * Only Manual tables own their column storage, so a standalone column delete is
 * a Manual-table operation: it delegates to the same
 * `wpDataTableConstructor::deleteManualColumn()` the admin manual-table editor
 * uses — which `ALTER TABLE … DROP COLUMN`s the backing storage, removes the
 * `wpdatatables_columns` row and re-packs the column order. For non-Manual
 * tables (columns derive from the query/file) it returns 400. 404 if the column
 * is missing or not in the table.
 *
 * @package WPDataTables\Controllers\Rest\Column
 */
class DeleteColumnController extends Controller
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
     * @throws InvalidArgumentException When an id is missing/zero, or the table is not Manual.
     * @throws NotFoundException        When the column is missing / not in the table.
     * @throws \Exception
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Route-captured ids come from the URL, not get_param() — keeps routing
        // ids authoritative regardless of any request body.
        $urlParams = $data->get_url_params();
        $tableId   = RestSanitizer::id($urlParams['id'] ?? null);
        $columnId  = RestSanitizer::id($urlParams['columnId'] ?? null);

        if ($tableId === 0 || $columnId === 0) {
            throw new InvalidArgumentException('A valid table id and column id are required.');
        }

        $column = $this->tableConfigService->loadSingleColumnFromDB($columnId);

        if (!$column || (int)$column['table_id'] !== $tableId) {
            throw new NotFoundException('Column not found.');
        }

        $table = $this->tableConfigService->loadTableFromDB($tableId, false);

        if (!$table) {
            throw new NotFoundException('Table not found.');
        }

        if (empty($table->table_type) || $table->table_type !== 'manual') {
            throw new InvalidArgumentException(
                'Columns can only be deleted from Manual tables; other table types derive their columns from the data source.'
            );
        }

        // wpDataTableConstructor is autoloaded from backend/src/Legacy/Facades/.
        wpDataTableConstructor::deleteManualColumn($tableId, $column['orig_header']);

        return new WP_REST_Response([
            'tableId'  => $tableId,
            'columnId' => $columnId,
            'deleted'  => true,
        ], 200);
    }
}
