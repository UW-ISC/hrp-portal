<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Repository\Table;

use WPDataTables\Repository\BaseRepositoryInterface;

/**
 * Persistence contract for the `wpdatatables` table.
 *
 * @package WPDataTables\Repository\Table
 */
interface TableRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Fetch a raw table row by id (legacy shape, OBJECT).
     *
     * @param int $id
     *
     * @return object|null
     */
    public function findRowById(int $id): ?object;

    /**
     * Insert a prepared table-config row, returning the new id.
     *
     * @param array<string, mixed> $config
     *
     * @return int
     */
    public function insert(array $config): int;

    /**
     * Update a prepared table-config row by id.
     *
     * @param int                  $id
     * @param array<string, mixed> $config
     *
     * @return void
     */
    public function updateById(int $id, array $config): void;

    /**
     * Return the subset of ids that exist in the tables table.
     *
     * @param list<int> $ids
     *
     * @return list<int>
     */
    public function filterExistingIds(array $ids): array;
}
