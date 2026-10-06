<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Factory\Column;

use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Entity\Column\Column;
use WPDataTables\ValueObjects\Column\ColumnSettings;

/**
 * Hydrates a {@see Column} entity from a raw `wpdatatables_columns` row.
 *
 * Referenced by `ColumnRepository::FACTORY`.
 *
 * @package WPDataTables\Factory\Column
 */
class ColumnFactory
{
    /**
     * @param array<string, mixed> $data
     *
     * @return Column
     *
     * @throws InvalidArgumentException
     */
    public static function create(array $data): Column
    {
        $id = (int) ($data['id'] ?? 0);
        $tableId = (int) ($data['table_id'] ?? 0);

        $attributes = $data;
        unset($attributes['id'], $attributes['table_id']);

        return new Column(new ColumnSettings($id, $tableId, $attributes));
    }
}
