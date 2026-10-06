<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\ValueObjects\Column;

use WPDataTables\Common\Exceptions\InvalidArgumentException;

/**
 * Immutable settings for a persisted column row.
 *
 * Wraps the columns of the `wpdatatables_columns` table. This is the persistence
 * shape; per-type formatting, the `ColumnType`/`FilterType` enums and the editing
 * settings value objects live with the column formatter services.
 *
 * @package WPDataTables\ValueObjects\Column
 */
final class ColumnSettings
{
    /** @var int */
    public int $id;

    /** @var int */
    public int $tableId;

    /**
     * Raw column values keyed by `wpdatatables_columns` column name.
     *
     * @var array<string, mixed>
     */
    public array $attributes;

    /**
     * @param int                  $id
     * @param int                  $tableId
     * @param array<string, mixed> $attributes
     *
     * @throws InvalidArgumentException
     */
    public function __construct(int $id, int $tableId, array $attributes = [])
    {
        if ($id < 0) {
            throw new InvalidArgumentException('Column id must not be negative.');
        }

        $this->id = $id;
        $this->tableId = $tableId;
        $this->attributes = $attributes;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }
}
