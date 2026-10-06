<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Rest;

use WPDataTables\Common\Exceptions\ForbiddenException;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Common\Exceptions\NotFoundException;
use WPDataTables\Common\Rest\RestSanitizer;
use WPDataTables\PublicApi\Services\PublicApiTableAccess;
use WPDataTables\Services\Table\ServerSideDataService;
use WPDataTables\Services\Table\TableConfigService;
use WPDataTables\Services\Table\TableLoadService;
use WPDataTable;
use WP_REST_Request;

/**
 * Shared read logic for table REST endpoints (admin + public).
 *
 * @package WPDataTables\Services\Rest
 */
class TableReadService
{
    /** @var ServerSideDataService */
    private $serverSideDataService;

    /** @var TableConfigService */
    private $tableConfigService;

    /** @var TableLoadService */
    private $tableLoadService;

    public function __construct(
        ServerSideDataService $serverSideDataService,
        TableConfigService $tableConfigService,
        TableLoadService $tableLoadService
    ) {
        $this->serverSideDataService = $serverSideDataService;
        $this->tableConfigService = $tableConfigService;
        $this->tableLoadService = $tableLoadService;
    }

    /**
     * @return array<int, mixed>
     */
    public function listTables()
    {
        $tables = WPDataTable::getAllTables();

        return $tables ? $tables : [];
    }

    /**
     * @return array<int, mixed>
     */
    public function listTablesForPublicApi()
    {
        return PublicApiTableAccess::filterTableList($this->listTables());
    }

    /**
     * @param int $tableId
     * @return array<string, mixed>
     * @throws ForbiddenException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function getTableWithColumnsForPublicApi(int $tableId)
    {
        PublicApiTableAccess::assertCanAccess($tableId);

        return $this->getTableWithColumns($tableId);
    }

    /**
     * @param int             $tableId
     * @param WP_REST_Request $request
     * @return array<int|string, mixed>
     * @throws ForbiddenException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws \Exception
     */
    public function getTableDataForPublicApi(int $tableId, WP_REST_Request $request)
    {
        PublicApiTableAccess::assertCanAccess($tableId);

        return $this->getTableData($tableId, $request);
    }

    /**
     * @param int $tableId
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function getTableWithColumns(int $tableId)
    {
        if ($tableId === 0) {
            throw new InvalidArgumentException('A valid table id is required.');
        }

        $table = $this->tableConfigService->loadTableFromDB($tableId);

        if (!$table) {
            throw new NotFoundException('Table not found.');
        }

        $columns = $this->tableConfigService->loadColumnsFromDB($tableId);

        return [
            'table'   => $table,
            'columns' => $columns ? $columns : [],
        ];
    }

    /**
     * @param int             $tableId
     * @param WP_REST_Request $request
     * @return array<int|string, mixed>
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws \Exception
     */
    public function getTableData(int $tableId, WP_REST_Request $request)
    {
        if ($tableId === 0) {
            throw new InvalidArgumentException('A valid table id is required.');
        }

        $tableData = $this->tableConfigService->loadTableFromDB($tableId);

        if (!$tableData) {
            throw new NotFoundException('Table not found.');
        }

        if (RestSanitizer::boolean($request->get_param('server_side'))) {
            return $this->getServerSideData($request, $tableId, $tableData);
        }

        $wpDataTable = $this->tableLoadService->loadTable($tableId, null, true);

        return $wpDataTable->getDataRowsFormatted();
    }

    /**
     * @param WP_REST_Request $request
     * @param int             $tableId
     * @param object          $tableData
     * @return array<int|string, mixed>
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    private function getServerSideData(WP_REST_Request $request, int $tableId, $tableData)
    {
        if (empty($tableData->table_type) || !in_array($tableData->table_type, ['mysql', 'manual'], true)) {
            throw new InvalidArgumentException('Server-side data is only available for SQL and Manual tables.');
        }

        $columns = $this->tableConfigService->loadColumnsFromDB($tableId);
        $dtColumns = [];
        foreach (($columns ?: []) as $column) {
            $dtColumns[] = [
                'data'       => count($dtColumns),
                'name'       => $column->orig_header,
                'searchable' => 'true',
                'orderable'  => 'true',
                'search'     => ['value' => '', 'regex' => 'false'],
            ];
        }

        $draw   = (int) $request->get_param('draw') ?: 1;
        $start  = max(0, (int) $request->get_param('start'));
        $length = $request->get_param('length');
        $length = ($length === null || $length === '') ? 10 : (int) $length;
        $search = (string) $request->get_param('search');

        $touched = ['draw', 'start', 'length', 'search', 'columns', 'order'];
        $saved = [];
        foreach ($touched as $key) {
            $saved[$key] = $_POST[$key] ?? null;
        }

        try {
            $_POST['draw']    = $draw;
            $_POST['start']   = $start;
            $_POST['length']  = $length;
            $_POST['search']  = ['value' => $search, 'regex' => 'false'];
            $_POST['columns'] = $dtColumns;

            $orderColumn = $request->get_param('order_column');
            if ($orderColumn !== null && $orderColumn !== '' && $dtColumns) {
                $orderDir = strtolower((string) $request->get_param('order_dir')) === 'desc' ? 'desc' : 'asc';
                $_POST['order'] = [['column' => (int) $orderColumn, 'dir' => $orderDir]];
            } else {
                unset($_POST['order']);
            }

            do_action('wpdatatables_get_ajax_data', $tableId);

            $result = $this->serverSideDataService->buildResponse($tableId);
            $json   = isset($result['json']) ? $result['json'] : '';
            $json   = apply_filters('wpdatatables_filter_server_side_data', $json, $tableId, $request->get_params());
        } finally {
            foreach ($touched as $key) {
                if ($saved[$key] === null) {
                    unset($_POST[$key]);
                } else {
                    $_POST[$key] = $saved[$key];
                }
            }
        }

        $payload = json_decode($json, true);

        return $payload !== null ? $payload : [];
    }
}
