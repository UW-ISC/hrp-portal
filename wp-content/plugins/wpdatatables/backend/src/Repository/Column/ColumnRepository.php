<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Repository\Column;

use WPDataTables\Factory\Column\ColumnFactory;
use WPDataTables\Repository\AbstractRepository;

/**
 * Repository over the `wpdatatables_columns` table.
 *
 * Encapsulates the raw `$wpdb` reads/writes the legacy
 * `WDTConfigController::loadColumnsFromDB()` / `loadSingleColumnFromDB()` /
 * `saveSingleColumn()` performed inline. The `apply_filters` /
 * `masterdetail` post-processing stays in the legacy shim.
 *
 * @package WPDataTables\Repository\Column
 */
class ColumnRepository extends AbstractRepository implements ColumnRepositoryInterface
{
    public const FACTORY = ColumnFactory::class;

    /**
     * @param int           $tableId
     * @param array<string> $columnNames
     *
     * @return array<int, object>
     */
    public function findRowsByTableId(int $tableId, array $columnNames = []): array
    {
        $params = [$tableId];

        $qWhere = '';
        foreach ($columnNames as $column) {
            if ($qWhere !== '') {
                $qWhere .= ', ';
            }
            $qWhere .= '%s';
            $params[] = $column;
        }

        if ($qWhere !== '') {
            $qWhere = " AND orig_header IN ( $qWhere )";
        }

        $query = $this->wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE table_id = %d " . $qWhere . ' ORDER BY pos',
            $params
        );

        return (array) $this->wpdb->get_results($query);
    }

    /**
     * @param int $id
     *
     * @return array<string, mixed>|null
     */
    public function findRowById(int $id): ?array
    {
        $query = $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id);

        return $this->wpdb->get_row($query, ARRAY_A);
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
        $this->wpdb->update($this->table, $config, ['id' => $id], null, ['%d']);
    }
}
