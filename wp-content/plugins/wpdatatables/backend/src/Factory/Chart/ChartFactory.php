<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Factory\Chart;

use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Entity\Chart\Chart;
use WPDataTables\ValueObjects\Chart\ChartSettings;

/**
 * Hydrates a {@see Chart} entity from a raw `wpdatacharts` row.
 *
 * Referenced by `ChartRepository::FACTORY`. Decodes the `json_render_data`
 * blob into an array.
 *
 * @package WPDataTables\Factory\Chart
 */
class ChartFactory
{
    /**
     * @param array<string, mixed> $data
     *
     * @return Chart
     *
     * @throws InvalidArgumentException
     */
    public static function create(array $data): Chart
    {
        $renderData = [];
        if (isset($data['json_render_data']) && $data['json_render_data'] !== '') {
            $decoded = json_decode((string) $data['json_render_data'], true);
            if (is_array($decoded)) {
                $renderData = $decoded;
            }
        }

        return new Chart(
            new ChartSettings(
                (int) ($data['id'] ?? 0),
                (int) ($data['wpdatatable_id'] ?? 0),
                (string) ($data['title'] ?? ''),
                (string) ($data['engine'] ?? ''),
                (string) ($data['type'] ?? ''),
                $renderData
            )
        );
    }
}
