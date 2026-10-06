<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Repository\Table;

use WPDataTables\Common\Exceptions\QueryExecutionException;
use WPDataTables\Factory\Table\TableFactory;
use WPDataTables\Repository\AbstractRepository;

/**
 * Repository over the `wpdatatables` table.
 *
 * Encapsulates the raw `$wpdb` reads/writes that
 * `WDTConfigController::loadTableFromDB()` / `saveTableToDB()` performed inline.
 * The surrounding hooks, defaults and sanitisation stay in the
 * `WDTConfigController` facade; only the persistence call lives here.
 *
 * @package WPDataTables\Repository\Table
 */
class TableRepository extends AbstractRepository implements TableRepositoryInterface
{
    public const FACTORY = TableFactory::class;

    /**
     * @param int $id
     *
     * @return object|null
     *
     * @throws QueryExecutionException
     */
    public function findRowById(int $id): ?object
    {
        $query = $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id);
        $row = $this->wpdb->get_row($query);

        if (!empty($this->wpdb->last_error)) {
            throw new QueryExecutionException(
                __('There was an error trying to fetch the table data: ', 'wpdatatables') . $this->wpdb->last_error
            );
        }

        return $row;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return int
     */
    public function insert(array $config): int
    {
        $this->wpdb->insert($this->table, $config);

        return (int) $this->wpdb->insert_id;
    }

    /**
     * @param int                  $id
     * @param array<string, mixed> $config
     *
     * @return void
     */
    public function updateById(int $id, array $config): void
    {
        $this->wpdb->update($this->table, $config, ['id' => $id]);
    }

    /**
     * @param list<int> $ids
     *
     * @return list<int>
     */
    public function filterExistingIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static function (int $id): bool {
                return $id > 0;
            }
        )));

        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $query        = $this->wpdb->prepare(
            "SELECT id FROM {$this->table} WHERE id IN ({$placeholders})",
            ...$ids
        );
        $rows = $this->wpdb->get_col($query);

        return array_map('intval', (array) $rows);
    }
}
