<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Repository\Rows;

use WPDataTables\Repository\AbstractRepository;

/**
 * Repository over the `wpdatatables_rows` table.
 *
 * Manual rows carry no entity, so the factory-backed `getById()` / `getAll()`
 * are overridden to return raw rows. The JSON decode and `apply_filters`
 * post-processing stay in the legacy shim.
 *
 * @package WPDataTables\Repository\Rows
 */
class RowsRepository extends AbstractRepository implements RowsRepositoryInterface
{
    /**
     * @param int $tableId
     *
     * @return array<int, object>
     */
    public function findDataByTableId(int $tableId): array
    {
        $query = $this->wpdb->prepare(
            "SELECT data FROM {$this->table} WHERE table_id = %d ORDER BY id ASC",
            $tableId
        );

        return (array) $this->wpdb->get_results($query);
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return void
     */
    public function insertRow(array $row): void
    {
        $this->wpdb->insert($this->table, $row);
    }

    /**
     * @param int $id
     *
     * @return object|null
     */
    public function getById(int $id): ?object
    {
        $query = $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id);

        return $this->wpdb->get_row($query);
    }

    /**
     * @return array<int, object>
     */
    public function getAll(): array
    {
        return (array) $this->wpdb->get_results("SELECT * FROM {$this->table}");
    }
}
