<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Repository\Column;

use WPDataTables\Repository\BaseRepositoryInterface;

/**
 * Persistence contract for the `wpdatatables_columns` table.
 *
 * @package WPDataTables\Repository\Column
 */
interface ColumnRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Fetch raw column rows for a table, ordered by position.
     *
     * @param int           $tableId
     * @param array<string> $columnNames Optional `orig_header` allowlist.
     *
     * @return array<int, object>
     */
    public function findRowsByTableId(int $tableId, array $columnNames = []): array;

    /**
     * Fetch a single raw column row by id (ARRAY_A).
     *
     * @param int $id
     *
     * @return array<string, mixed>|null
     */
    public function findRowById(int $id): ?array;

    /**
     * Insert a prepared column-config row, returning the new id.
     *
     * @param array<string, mixed> $config
     *
     * @return int
     */
    public function insert(array $config): int;

    /**
     * Update a prepared column-config row by id.
     *
     * @param int                  $id
     * @param array<string, mixed> $config
     *
     * @return void
     */
    public function updateById(int $id, array $config): void;
}
