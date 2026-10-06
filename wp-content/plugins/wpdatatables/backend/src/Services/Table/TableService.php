<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Table;

use WPDataTable;
use WPExcelDataTable;
use WPDataTables\Entity\Table\RuntimeTable;
use WPDataTables\Rendering\ExcelTableRenderer;
use WPDataTables\Rendering\TableRenderer;
use Connection;

/**
 * Table engine orchestrator.
 *
 * The single application-layer entry the global {@see \WPDataTable} facade
 * delegates its data-engine work to. It coordinates the engine collaborators —
 * currently the {@see ServerSideProcessor} for query-based construction; the
 * filter / sort / pagination / summary seams hang off the same engine and the
 * REST controllers resolve this one service.
 *
 * `constructFromQuery()` forwards the `queryBasedConstruct()` call straight
 * through to the server-side processor, preserving the per-request hot path (one
 * pre-built singleton lookup, no extra hydration).
 *
 * @package WPDataTables\Services\Table
 */
class TableService
{
    /** @var ServerSideProcessor */
    private $serverSideProcessor;

    /** @var TableConfigService */
    private $tableConfigService;

    /** @var TableRenderer */
    private $tableRenderer;

    /** @var ExcelTableRenderer */
    private $excelTableRenderer;

    /** @var TableHydrationService */
    private $tableHydrationService;

    /** @var TableArrayBuilderService */
    private $tableArrayBuilderService;

    /** @var TableLoadService */
    private $tableLoadService;

    public function __construct(
        ServerSideProcessor $serverSideProcessor,
        TableConfigService $tableConfigService,
        TableRenderer $tableRenderer,
        ExcelTableRenderer $excelTableRenderer,
        TableHydrationService $tableHydrationService,
        TableArrayBuilderService $tableArrayBuilderService,
        TableLoadService $tableLoadService
    ) {
        $this->serverSideProcessor = $serverSideProcessor;
        $this->tableConfigService = $tableConfigService;
        $this->tableRenderer = $tableRenderer;
        $this->excelTableRenderer = $excelTableRenderer;
        $this->tableHydrationService = $tableHydrationService;
        $this->tableArrayBuilderService = $tableArrayBuilderService;
        $this->tableLoadService = $tableLoadService;
    }

    /**
     * Load and hydrate a table by id (the {@see WPDataTable::loadWpDataTable()} path).
     *
     * @param int         $tableId
     * @param string|null $tableView
     * @param bool        $disableLimit
     *
     * @return WPDataTable|WPExcelDataTable|false
     * @throws \Exception
     */
    public function loadTable($tableId, $tableView = null, $disableLimit = false)
    {
        return $this->tableLoadService->loadTable($tableId, $tableView, $disableLimit);
    }

    /**
     * Hydrate runtime state from persisted config (delegates to {@see TableHydrationService}).
     *
     * @param WPDataTable $table
     * @param mixed       $tableData
     * @param array       $columnData
     *
     * @return void
     */
    public function fillFromData(WPDataTable $table, $tableData, $columnData)
    {
        $this->tableHydrationService->fillFromData($table, $tableData, $columnData);
    }

    /**
     * Build table structure from a raw data array.
     *
     * @param WPDataTable $table
     * @param array       $rawDataArr
     * @param array       $wdtParameters
     *
     * @return bool
     */
    public function buildFromArray(WPDataTable $table, $rawDataArr, $wdtParameters)
    {
        return $this->tableArrayBuilderService->buildFromArray($table, $rawDataArr, $wdtParameters);
    }

    /**
     * Prepare supplementary column objects for server-side output formatting.
     *
     * @param WPDataTable $table
     * @param array       $wdtParameters
     *
     * @return array
     */
    public function prepareColumns(WPDataTable $table, $wdtParameters)
    {
        return $this->tableArrayBuilderService->prepareColumns($table, $wdtParameters);
    }

    /**
     * Reformat server-side query rows for DataTables JSON output.
     *
     * @param WPDataTable $table
     * @param array       $main_res_dataRows
     * @param array       $wdtParameters
     * @param array       $colObjs
     *
     * @return mixed
     */
    public function prepareOutputData(WPDataTable $table, $main_res_dataRows, $wdtParameters, $colObjs)
    {
        return $this->tableArrayBuilderService->prepareOutputData($table, $main_res_dataRows, $wdtParameters, $colObjs);
    }

    /**
     * Construct a table from its query (the `queryBasedConstruct` path).
     *
     * @param WPDataTable $table
     * @param string      $query
     * @param array       $queryParams
     * @param array       $wdtParameters
     * @param bool        $init_read
     *
     * @return mixed DataTables JSON (server-side) or the column-builder result.
     * @throws \Exception
     */
    public function constructFromQuery(WPDataTable $table, $query, array $queryParams = array(), array $wdtParameters = array(), $init_read = false)
    {
        return $this->serverSideProcessor->getData($table, $query, $queryParams, $wdtParameters, $init_read);
    }

    /**
     * Delete a table and its dependent records by id.
     *
     * Auth-decoupled extraction of {@see \WPDataTable::deleteTable()}. There is
     * no internal nonce/capability check: callers gate auth upstream (the REST
     * `permission_callback`, or the static wrapper which keeps its nonce check).
     * The physical `DROP TABLE` of a Manual table's backing MySQL storage only
     * runs when `$dropStorage` is true, so a delete can preserve the underlying
     * data.
     *
     * @param int  $id          The wpDataTables table id.
     * @param bool $dropStorage Whether to DROP a Manual table's backing storage.
     *
     * @return array{deleted: bool, storageDropped: bool, chartsDeleted: int}
     *               What was removed: whether the table row was deleted, whether
     *               the backing storage was dropped, and how many dependent
     *               charts cascaded.
     * @throws \Exception
     */
    public function deleteTable(int $id, bool $dropStorage = false): array
    {
        global $wpdb;

        $result = ['deleted' => false, 'storageDropped' => false, 'chartsDeleted' => 0];

        if (empty($id)) {
            return $result;
        }

        $table = $this->tableConfigService->loadTableFromDB($id);

        if (!$table) {
            return $result;
        }

        if ($dropStorage && !empty($table->table_type) && $table->table_type == 'manual') {
            if (!(Connection::isSeparate($table->connection))) {
                $wpdb->query("DROP TABLE {$table->mysql_table_name}");
            } else {
                $sql = Connection::getInstance($table->connection);
                $sql->doQuery("DROP TABLE {$table->mysql_table_name}");
            }
            $result['storageDropped'] = true;
        }

        $id = (int)$id;

        $wpdb->delete("{$wpdb->prefix}wpdatatables", array('id' => $id));
        $wpdb->delete("{$wpdb->prefix}wpdatatables_columns", array('table_id' => $id));
        $wpdb->delete("{$wpdb->prefix}wpdatatables_rows", array('table_id' => $id));
        $wpdb->delete("{$wpdb->prefix}wpdatatables_cache", array('table_id' => $id));
        $chartsDeleted = $wpdb->delete("{$wpdb->prefix}wpdatacharts", array('wpdatatable_id' => $id));

        do_action('wpdatatables_after_delete_tables', $id, 'table');

        $result['deleted'] = true;
        $result['chartsDeleted'] = (int)$chartsDeleted;

        return $result;
    }

    /**
     * Wrap a live table in its runtime engine entity.
     *
     * @param WPDataTable $table
     *
     * @return RuntimeTable
     */
    public function wrap(WPDataTable $table)
    {
        return new RuntimeTable($table);
    }

    /**
     * Public frontend render pipeline entry point.
     *
     * Routes Excel/simple tables to {@see ExcelTableRenderer}; standard tables
     * to {@see TableRenderer}. The global {@see \WPDataTable::generateTable()}
     * facade delegates here.
     *
     * @param WPDataTable $table
     *
     * @return string complete table HTML
     */
    public function generateTableHtml(WPDataTable $table)
    {
        if ($table instanceof WPExcelDataTable) {
            return $this->excelTableRenderer->generate($table);
        }

        return $this->tableRenderer->render(
            $table,
            $this->tableRenderer->renderWithAssets($table)
        );
    }

    /**
     * Render the shared filter/delete modals once on `wp_footer`.
     *
     * The static {@see \WPDataTable::renderModal()} facade delegates here.
     *
     * @return void
     */
    public function renderModal()
    {
        $this->tableRenderer->renderModal();
    }
}
