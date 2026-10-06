<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Rest\Table;

use WPDataTables\Controllers\Controller;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Common\Exceptions\NotFoundException;
use WPDataTables\Common\Exceptions\ValidationException;
use WPDataTables\Common\Rest\RestSanitizer;
use WPDataTables\Services\Table\TableConfigService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * PUT /wpdatatables/v1/tables/{id} — update an existing table.
 *
 * Same persistence path as {@see SaveTableController} (the shared
 * {@see TableConfigService::buildSaveResult()}), but the route id is forced onto the
 * config so `saveTableToDB()` updates the existing row rather than inserting.
 * 404 if the table does not exist.
 *
 * @package WPDataTables\Controllers\Rest\Table
 */
class UpdateTableController extends Controller
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
     * @throws InvalidArgumentException When the id/body is missing/invalid.
     * @throws NotFoundException        When no table exists for the id.
     * @throws ValidationException      When the config fails to build/save.
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Read the route id from the URL, not get_param(): the request body is a
        // table config that may carry its own `id`, and WP ranks JSON-body params
        // above URL params — so get_param('id') could return the body's id and
        // update the wrong table. get_url_params() keeps routing authoritative.
        $urlParams = $data->get_url_params();
        $tableId   = RestSanitizer::id($urlParams['id'] ?? null);

        if ($tableId === 0) {
            throw new InvalidArgumentException('A valid table id is required.');
        }

        // Uncached read for the existence check (the cached variant can return
        // stale state when many tables are loaded in one process).
        if (!$this->tableConfigService->loadTableFromDB($tableId, false)) {
            throw new NotFoundException('Table not found.');
        }

        $params = $data->get_json_params();

        if (empty($params) || !is_array($params)) {
            throw new InvalidArgumentException('A table configuration body is required.');
        }

        $table = json_decode(json_encode($params));

        // Force the route id so saveTableToDB() updates the existing row.
        $table->id = $tableId;

        $table = apply_filters('wpdatatables_before_save_table', $table);

        if (empty($table->table_type)) {
            throw new InvalidArgumentException('A "table_type" is required.');
        }

        if (!isset($table->file)) {
            $table->file = '';
        }
        if (!isset($table->fileSourceAction)) {
            $table->fileSourceAction = '';
        }

        $res = $this->tableConfigService->buildSaveResult($table);

        if (!empty($res->error)) {
            throw new ValidationException($res->error);
        }

        return new WP_REST_Response([
            'id'     => $tableId,
            'table'  => isset($res->table) ? $res->table : null,
            'config' => isset($res->wdtJsonConfig) ? $res->wdtJsonConfig : null,
        ], 200);
    }
}
