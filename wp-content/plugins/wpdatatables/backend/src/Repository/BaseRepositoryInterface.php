<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Repository;

/**
 * Contract shared by every wpDataTables repository.
 *
 * @package WPDataTables\Repository
 */
interface BaseRepositoryInterface
{
    /**
     * @param int $id
     * @return object|null
     */
    public function getById(int $id): ?object;

    /**
     * @return mixed[]
     */
    public function getAll(): array;

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int;

    /**
     * @param array<int> $ids
     * @return int
     */
    public function deleteMany(array $ids): int;

    /**
     * @param array<string, mixed>|null $params
     * @return array<string, mixed>
     */
    public function search(?array $params): array;
}
