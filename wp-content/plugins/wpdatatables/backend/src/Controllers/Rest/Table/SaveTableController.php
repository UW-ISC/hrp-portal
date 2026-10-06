<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Rest\Table;

use WPDataTables\Controllers\Controller;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Common\Exceptions\ValidationException;
use WPDataTables\Services\Table\TableConfigService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * POST /wpdatatables/v1/tables — create a table.
 *
 * Accepts a full table-configuration object (the same shape the admin editor
 * posts) as the JSON body, applies the `wpdatatables_before_save_table` filter
 * (parity with the admin-ajax `saveTableWithColumns` handler), then persists via
 * {@see TableConfigService::buildSaveResult()} — the returning half of
 * `saveTableConfig()`, which REST reuses without the admin echo/exit. Admin-ajax
 * and REST therefore save through the identical path. `id` is stripped so the row
 * is inserted and the new id is returned.
 *
 * @package WPDataTables\Controllers\Rest\Table
 */
class SaveTableController extends Controller
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
     * @throws InvalidArgumentException When the body is missing/invalid.
     * @throws ValidationException      When the config fails to build/save.
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        $params = $data->get_json_params();

        if (empty($params) || !is_array($params)) {
            throw new InvalidArgumentException('A table configuration body is required.');
        }

        // Deep array → stdClass (buildSaveResult / sanitizeTableConfig read object props).
        $table = json_decode(json_encode($params));

        // Create: leave id unset so saveTableToDB() inserts and the new
        // insert_id is picked up (matches the admin new-table payload). Also
        // strip any inherited child ids / backing-storage name so a create
        // payload copied from an existing table can never re-point that table's
        // columns (saveColumns updates by column id) or alias its storage.
        unset($table->id);
        unset($table->mysql_table_name);
        if (isset($table->columns) && is_array($table->columns)) {
            foreach ($table->columns as $column) {
                if (is_object($column)) {
                    unset($column->id);
                }
            }
        }

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
            'id'     => isset($res->table->id) ? (int)$res->table->id : 0,
            'table'  => isset($res->table) ? $res->table : null,
            'config' => isset($res->wdtJsonConfig) ? $res->wdtJsonConfig : null,
        ], 200);
    }
}
