<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Factory\Table;

use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Entity\Table\Table;
use WPDataTables\ValueObjects\Table\TableSettings;

/**
 * Hydrates a {@see Table} entity from a raw `wpdatatables` row.
 *
 * Referenced by `TableRepository::FACTORY`. Decodes the `advanced_settings`
 * JSON blob into an array and keeps the remaining scalar columns as the value
 * object's attributes.
 *
 * @package WPDataTables\Factory\Table
 */
class TableFactory
{
    /**
     * @param array<string, mixed> $data
     *
     * @return Table
     *
     * @throws InvalidArgumentException
     */
    public static function create(array $data): Table
    {
        $id = (int) ($data['id'] ?? 0);

        $advancedSettings = [];
        if (isset($data['advanced_settings']) && $data['advanced_settings'] !== '') {
            $decoded = json_decode((string) $data['advanced_settings'], true);
            if (is_array($decoded)) {
                $advancedSettings = $decoded;
            }
        }

        $attributes = $data;
        unset($attributes['id'], $attributes['advanced_settings']);

        return new Table(new TableSettings($id, $attributes, $advancedSettings));
    }
}
