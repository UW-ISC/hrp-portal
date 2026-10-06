<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Rest\Table;

use WPDataTables\Controllers\Controller;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Common\Exceptions\NotFoundException;
use WPDataTables\Common\Rest\RestSanitizer;
use WPDataTables\Services\Table\TableConfigService;
use WPDataTables\Services\Table\TableService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * DELETE /wpdatatables/v1/tables/{id} — delete a table.
 *
 * Delegates to the auth-decoupled {@see TableService::deleteTable()} (the same
 * engine the legacy admin browse-screen delete now wraps). By default the
 * underlying MySQL storage of a Manual table is preserved; pass
 * `?drop_storage=true` to also `DROP TABLE` the backing storage. 404 if the
 * table does not exist.
 *
 * @package WPDataTables\Controllers\Rest\Table
 */
class DeleteTableController extends Controller
{
    /** @var TableService */
    private $tableService;

    /** @var TableConfigService */
    private $tableConfigService;

    public function __construct(TableService $tableService, TableConfigService $tableConfigService)
    {
        $this->tableService = $tableService;
        $this->tableConfigService = $tableConfigService;
    }

    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response
     * @throws InvalidArgumentException When the id is missing/zero.
     * @throws NotFoundException        When no table exists for the id.
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        $tableId = RestSanitizer::id($data->get_param('id'));

        if ($tableId === 0) {
            throw new InvalidArgumentException('A valid table id is required.');
        }

        // Uncached existence check (mirrors UpdateTableController — the cached
        // variant can return stale state when many tables load in one process).
        if (!$this->tableConfigService->loadTableFromDB($tableId, false)) {
            throw new NotFoundException('Table not found.');
        }

        // Default false: preserve the underlying MySQL table unless explicitly
        // asked to drop it. Only Manual tables have backing storage to drop.
        $dropStorage = RestSanitizer::boolean($data->get_param('drop_storage'));

        $result = $this->tableService->deleteTable($tableId, $dropStorage);

        return new WP_REST_Response([
            'id'             => $tableId,
            'deleted'        => $result['deleted'],
            'storageDropped' => $result['storageDropped'],
            'chartsDeleted'  => $result['chartsDeleted'],
        ], 200);
    }
}
