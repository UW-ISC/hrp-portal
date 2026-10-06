<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Repository\Cache;

use WPDataTables\Repository\BaseRepositoryInterface;

/**
 * Persistence contract for the `wpdatatables_cache` table.
 *
 * The cache has no domain entity; it stores a serialised data snapshot per
 * table. These methods reproduce the raw `$wpdb` operations the legacy
 * `WPDataTableCache` performed; the caller keeps checking `$wpdb->last_error`
 * (same global handle) so its error logging is unchanged.
 *
 * @package WPDataTables\Repository\Cache
 */
interface CacheRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Fetch the cached `data` row for a table.
     *
     * @param int $tableId
     *
     * @return object|null
     */
    public function findDataRow(int $tableId): ?object;

    /**
     * Fetch all rows flagged for auto-update, ordered by id (ARRAY_A).
     *
     * @return array<int, array<string, mixed>>|null
     */
    public function findAutoUpdateRows(): ?array;

    /**
     * Insert a prepared cache row.
     *
     * @param array<string, mixed> $row
     *
     * @return void
     */
    public function insertCache(array $row): void;

    /**
     * Replace the cached data + updated_time for a table.
     *
     * @param int                   $tableId
     * @param array<string, mixed>  $data
     *
     * @return void
     */
    public function updateData(int $tableId, array $data): void;

    /**
     * Delete the cache row for a table.
     *
     * @param int $tableId
     *
     * @return void
     */
    public function deleteByTableId(int $tableId): void;

    /**
     * Persist a log message into the cache row for a table.
     *
     * @param int    $tableId
     * @param string $logError
     *
     * @return void
     */
    public function updateLogErrors(int $tableId, string $logError): void;
}
