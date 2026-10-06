<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Cache;

use WPDataTables\Repository\Cache\CacheRepositoryInterface;

/**
 * Application service over the table-data cache.
 *
 * Cache CRUD flows through {@see CacheRepositoryInterface}, while the
 * source-rendering and auto-update orchestration remain in `WPDataTableCache`.
 * Because the repository shares the global `$wpdb` handle, callers can still
 * inspect `$wpdb->last_error` after these calls.
 *
 * @package WPDataTables\Services\Cache
 */
class CacheManager
{
    /** @var CacheRepositoryInterface */
    private CacheRepositoryInterface $repository;

    public function __construct(CacheRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int $tableId
     *
     * @return object|null
     */
    public function getCachedDataRow(int $tableId): ?object
    {
        return $this->repository->findDataRow($tableId);
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    public function getAutoUpdateTables(): ?array
    {
        return $this->repository->findAutoUpdateRows();
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return void
     */
    public function saveCache(array $row): void
    {
        $this->repository->insertCache($row);
    }

    /**
     * @param int                  $tableId
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function updateData(int $tableId, array $data): void
    {
        $this->repository->updateData($tableId, $data);
    }

    /**
     * @param int $tableId
     *
     * @return void
     */
    public function delete(int $tableId): void
    {
        $this->repository->deleteByTableId($tableId);
    }

    /**
     * @param int    $tableId
     * @param string $logError
     *
     * @return void
     */
    public function logError(int $tableId, string $logError): void
    {
        $this->repository->updateLogErrors($tableId, $logError);
    }
}
