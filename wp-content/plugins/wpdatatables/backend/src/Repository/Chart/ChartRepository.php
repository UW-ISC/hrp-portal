<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Repository\Chart;

use WPDataTables\Factory\Chart\ChartFactory;
use WPDataTables\Repository\AbstractRepository;

/**
 * Repository over the `wpdatacharts` table.
 *
 * Encapsulates the raw `$wpdb` reads/writes the legacy `WPDataChart::save()`,
 * `getChartDataById()`, `deleteChart()` and `getAll()` performed inline. The
 * render-data preparation, nonce/cap checks and `do_action` hooks stay in the
 * legacy `WPDataChart` shim.
 *
 * @package WPDataTables\Repository\Chart
 */
class ChartRepository extends AbstractRepository implements ChartRepositoryInterface
{
    public const FACTORY = ChartFactory::class;

    /**
     * @param int $id
     *
     * @return object|null
     */
    public function findRowById(int $id): ?object
    {
        $query = $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id);

        return $this->wpdb->get_row($query);
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return int
     */
    public function insert(array $row): int
    {
        $this->wpdb->insert($this->table, $row);

        return (int) $this->wpdb->insert_id;
    }

    /**
     * @param int                  $id
     * @param array<string, mixed> $row
     *
     * @return void
     */
    public function updateById(int $id, array $row): void
    {
        $this->wpdb->update($this->table, $row, ['id' => $id]);
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function deleteById(int $id): void
    {
        $this->wpdb->delete($this->table, ['id' => $id]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAllIdTitle(): array
    {
        return (array) $this->wpdb->get_results("SELECT id, title FROM {$this->table} ", ARRAY_A);
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
