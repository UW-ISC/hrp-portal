<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\ValueObjects\Table;

use WPDataTables\Common\Exceptions\InvalidArgumentException;

/**
 * Immutable settings for a wpDataTable persisted row.
 *
 * Wraps the columns of the `wpdatatables` table. The scalar columns are kept in
 * `$attributes` (raw post-read shape) and the JSON `advanced_settings` blob is
 * decoded into `$advancedSettings`. This is the persistence value object behind
 * {@see \WPDataTables\Entity\Table\Table}; the runtime engine value objects
 * (FilterState / SortState / Pagination) live with the table engine services.
 *
 * @package WPDataTables\ValueObjects\Table
 */
final class TableSettings
{
    /** @var int */
    public int $id;

    /**
     * Raw scalar columns of the `wpdatatables` row, keyed by column name.
     *
     * @var array<string, mixed>
     */
    public array $attributes;

    /**
     * Decoded `advanced_settings` JSON blob.
     *
     * @var array<string, mixed>
     */
    public array $advancedSettings;

    /**
     * @param int                  $id
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $advancedSettings
     *
     * @throws InvalidArgumentException
     */
    public function __construct(int $id, array $attributes = [], array $advancedSettings = [])
    {
        if ($id < 0) {
            throw new InvalidArgumentException('Table id must not be negative.');
        }

        $this->id = $id;
        $this->attributes = $attributes;
        $this->advancedSettings = $advancedSettings;
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
