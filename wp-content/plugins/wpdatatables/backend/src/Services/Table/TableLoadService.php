<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Table;

use WPDataTable;
use WPExcelDataTable;

/**
 * Loads and hydrates a {@see WPDataTable} from persisted metadata.
 *
 * Kept separate from {@see TableService} so {@see \WPDataTables\Services\DataSource\MySqlQueryDataSource}
 * can resolve foreign-key joined tables without a circular DI dependency
 * (TableService → ServerSideProcessor → MySqlQueryDataSource).
 *
 * @package WPDataTables\Services\Table
 */
class TableLoadService
{
    /** @var TableConfigService */
    private $tableConfigService;

    /** @var TableHydrationService */
    private $tableHydrationService;

    public function __construct(
        TableConfigService $tableConfigService,
        TableHydrationService $tableHydrationService
    ) {
        $this->tableConfigService = $tableConfigService;
        $this->tableHydrationService = $tableHydrationService;
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
        $loadFromCache = (isset($_POST['fileSourceAction']) && $_POST['fileSourceAction'] == 'replaceTable') ? false : true;
        $tableData = $this->tableConfigService->loadTableFromDB($tableId, $loadFromCache);

        if ($tableData) {
            $tableData->disable_limit = $disableLimit;
        }

        $useExcelClass = ($tableView === 'excel' && $tableData && $tableData->table_type !== 'woo_commerce');

        $wpDataTable = $useExcelClass
            ? new WPExcelDataTable($tableData->connection)
            : new WPDataTable($tableData->connection);
        $wpDataTable->setWpId($tableId);

        $columnDataPrepared = $this->tableConfigService->prepareColumnData($tableData);
        $this->tableHydrationService->fillFromData($wpDataTable, $tableData, $columnDataPrepared);

        return $wpDataTable;
    }
}
