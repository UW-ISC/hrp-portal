<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Repository\Chart;

use WPDataTables\Repository\BaseRepositoryInterface;

/**
 * Persistence contract for the `wpdatacharts` table.
 *
 * @package WPDataTables\Repository\Chart
 */
interface ChartRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Fetch a raw chart row by id.
     *
     * @param int $id
     *
     * @return object|null
     */
    public function findRowById(int $id): ?object;

    /**
     * Insert a prepared chart row, returning the new id.
     *
     * @param array<string, mixed> $row
     *
     * @return int
     */
    public function insert(array $row): int;

    /**
     * Update a prepared chart row by id.
     *
     * @param int                  $id
     * @param array<string, mixed> $row
     *
     * @return void
     */
    public function updateById(int $id, array $row): void;

    /**
     * Delete a chart row by id.
     *
     * @param int $id
     *
     * @return void
     */
    public function deleteById(int $id): void;

    /**
     * Fetch `id`/`title` of every chart (non-paged, for editors).
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAllIdTitle(): array;

    /**
     * Return the subset of ids that exist in the charts table.
     *
     * @param list<int> $ids
     *
     * @return list<int>
     */
    public function filterExistingIds(array $ids): array;
}
