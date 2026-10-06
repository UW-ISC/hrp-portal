<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

use WPDataTables\ValueObjects\Column\ColumnSettings;

/**
 * Mutable domain object for a single table column.
 *
 * The persistence entity {@see Column} wraps DB rows; per-type runtime
 * formatters live in {@see RuntimeColumn} subclasses (e.g. {@see StringColumn}).
 *
 * @package WPDataTables\Entity\Column
 */
class Column
{
    /** @var ColumnSettings */
    private ColumnSettings $settings;

    public function __construct(ColumnSettings $settings)
    {
        $this->settings = $settings;
    }

    public function getId(): int
    {
        return $this->settings->id;
    }

    public function setId(int $id): void
    {
        $this->settings->id = $id;
    }

    public function getTableId(): int
    {
        return $this->settings->tableId;
    }

    public function getOrigHeader(): string
    {
        return (string) $this->settings->get('orig_header', '');
    }

    public function getDisplayHeader(): string
    {
        return (string) $this->settings->get('display_header', '');
    }

    public function getColumnType(): string
    {
        return (string) $this->settings->get('column_type', '');
    }

    public function getPos(): int
    {
        return (int) $this->settings->get('pos', 0);
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->settings->get($key, $default);
    }

    /**
     * The persisted `wpdatatables_columns` row, keyed by column name.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $attributes = $this->settings->attributes;
        $attributes['id'] = $this->settings->id;
        $attributes['table_id'] = $this->settings->tableId;

        return $attributes;
    }
}
