<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Ai;

use Throwable;
use WPDataTables\Services\Table\TableConfigService;
use WPDataTables\Services\Table\TableLoadService;

/**
 * Fetches a privacy-safe column schema for a table, for injection into AI prompts.
 *
 * Only column names + types (and whether a column is filterable) ever leave the
 * site — never the base query SQL (`content`), connection credentials, or row
 * data. This is the single source of "what the AI is allowed to know about a
 * table's structure", and the allow-list the {@see OutputValidator} checks AI
 * responses against.
 *
 * Chart suggestions also receive a capped sample via {@see getSampleData()}
 * (5 rows × 8 columns of scalar cell values only).
 *
 * @package WPDataTables\Services\Ai
 */
class TableMetaService
{
    /** Max rows included in an AI chart-suggestion sample. */
    const SAMPLE_MAX_ROWS = 5;

    /** Max columns included in an AI chart-suggestion sample. */
    const SAMPLE_MAX_COLS = 8;

    /** Max characters per sample cell value. */
    const SAMPLE_CELL_MAX_LEN = 80;

    /** @var TableConfigService */
    private $tableConfigService;

    /** @var TableLoadService */
    private $tableLoadService;

    public function __construct(TableConfigService $tableConfigService, TableLoadService $tableLoadService)
    {
        $this->tableConfigService = $tableConfigService;
        $this->tableLoadService   = $tableLoadService;
    }

    /**
     * @param int $tableId
     * @return array{type: string, columns: array<int, array{name: string, type: string, filterable: bool}>}|null
     *         Null when the table or its columns cannot be loaded.
     */
    public function getTableMeta(int $tableId): ?array
    {
        if ($tableId <= 0) {
            return null;
        }

        $table = $this->tableConfigService->loadTableFromDB($tableId);
        if (!$table) {
            return null;
        }

        $columns = $this->tableConfigService->loadColumnsFromDB($tableId);
        if (!$columns) {
            return null;
        }

        $mapped = [];
        foreach ($columns as $column) {
            $mapped[] = [
                'name'       => isset($column->orig_header) ? (string) $column->orig_header : '',
                'type'       => isset($column->column_type) ? (string) $column->column_type : 'string',
                'filterable' => !empty($column->filter_type),
            ];
        }

        return [
            'type'    => isset($table->table_type) ? (string) $table->table_type : '',
            'columns' => $mapped,
        ];
    }

    /**
     * Privacy-safe data sample for chart suggestions: at most 5 rows × 8 cols.
     *
     * Returns `[]` when the table cannot be loaded or has no rows — the AI can
     * still recommend from schema alone.
     *
     * @param int $tableId
     * @return array<int, array<string, scalar|null>>
     */
    public function getSampleData(int $tableId): array
    {
        if ($tableId <= 0) {
            return [];
        }

        $meta = $this->getTableMeta($tableId);
        if ($meta === null || empty($meta['columns'])) {
            return [];
        }

        $columnNames = [];
        foreach (array_slice($meta['columns'], 0, self::SAMPLE_MAX_COLS) as $column) {
            if (!empty($column['name'])) {
                $columnNames[] = (string) $column['name'];
            }
        }

        if ($columnNames === []) {
            return [];
        }

        try {
            $wpDataTable = $this->tableLoadService->loadTable($tableId);
        } catch (Throwable $e) {
            return [];
        }

        if (!$wpDataTable) {
            return [];
        }

        $rows = $wpDataTable->getDataRows();
        if (!is_array($rows) || $rows === []) {
            return [];
        }

        $sample = [];
        foreach (array_slice($rows, 0, self::SAMPLE_MAX_ROWS) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $out = [];
            foreach ($columnNames as $name) {
                $out[$name] = $this->sanitizeSampleCell($row[$name] ?? null);
            }
            $sample[] = $out;
        }

        return $sample;
    }

    /**
     * @param mixed $value
     * @return scalar|null
     */
    private function sanitizeSampleCell($value)
    {
        if ($value === null || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (!is_scalar($value)) {
            return null;
        }

        $text = (string) $value;
        if (strlen($text) > self::SAMPLE_CELL_MAX_LEN) {
            $text = substr($text, 0, self::SAMPLE_CELL_MAX_LEN) . '…';
        }

        return $text;
    }
}
