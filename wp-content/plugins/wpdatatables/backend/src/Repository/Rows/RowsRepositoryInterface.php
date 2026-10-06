<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Repository\Rows;

use WPDataTables\Repository\BaseRepositoryInterface;

/**
 * Persistence contract for the `wpdatatables_rows` (manual-data) table.
 *
 * Manual rows have no domain entity (they hold an opaque JSON blob per row);
 * the repository exposes the raw read/write the legacy
 * `WDTConfigController::loadRowsDataFromDB()` / `saveRowData()` performed.
 *
 * @package WPDataTables\Repository\Rows
 */
interface RowsRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Fetch the `data` column of every row for a table, ordered by id.
     *
     * @param int $tableId
     *
     * @return array<int, object>
     */
    public function findDataByTableId(int $tableId): array;

    /**
     * Insert a prepared row.
     *
     * @param array<string, mixed> $row
     *
     * @return void
     */
    public function insertRow(array $row): void;
}
