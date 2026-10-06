<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Repository\Cache;

use WPDataTables\Repository\AbstractRepository;

/**
 * Repository over the `wpdatatables_cache` table.
 *
 * Reproduces the raw `$wpdb` operations the legacy `WPDataTableCache`
 * performed. The cache has no entity, so the factory-backed `getById()` /
 * `getAll()` are overridden to return raw rows.
 *
 * @package WPDataTables\Repository\Cache
 */
class CacheRepository extends AbstractRepository implements CacheRepositoryInterface
{
    /**
     * @param int $tableId
     *
     * @return object|null
     */
    public function findDataRow(int $tableId): ?object
    {
        $query = $this->wpdb->prepare(
            "SELECT data FROM {$this->table} WHERE table_id = %d",
            $tableId
        );

        return $this->wpdb->get_row($query);
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    public function findAutoUpdateRows(): ?array
    {
        $query = "SELECT table_id, table_type, table_content, updated_time, data
                  FROM {$this->table}
                  WHERE auto_update = 1
                  ORDER BY id";

        return $this->wpdb->get_results($query, ARRAY_A);
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return void
     */
    public function insertCache(array $row): void
    {
        $this->wpdb->insert($this->table, $row);
    }

    /**
     * @param int                  $tableId
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function updateData(int $tableId, array $data): void
    {
        $this->wpdb->update($this->table, $data, ['table_id' => $tableId]);
    }

    /**
     * @param int $tableId
     *
     * @return void
     */
    public function deleteByTableId(int $tableId): void
    {
        $this->wpdb->delete($this->table, ['table_id' => $tableId], ['%d']);
    }

    /**
     * @param int    $tableId
     * @param string $logError
     *
     * @return void
     */
    public function updateLogErrors(int $tableId, string $logError): void
    {
        $this->wpdb->query(
            $this->wpdb->prepare(
                "UPDATE {$this->table} SET log_errors = %s WHERE table_id = %d",
                $logError,
                $tableId
            )
        );
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
