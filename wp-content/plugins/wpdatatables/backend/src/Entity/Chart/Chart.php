<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Chart;

use WPDataTables\ValueObjects\Chart\ChartSettings;

/**
 * Mutable domain object for a chart.
 *
 * Replaces the persisted-state portion of the legacy `WPDataChart` hierarchy.
 * The provider behaviour decomposes into chart provider services later; this
 * entity is the persistence shape over the `wpdatacharts` table.
 *
 * @package WPDataTables\Entity\Chart
 */
class Chart
{
    /** @var ChartSettings */
    private ChartSettings $settings;

    public function __construct(ChartSettings $settings)
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

    public function getWpDataTableId(): int
    {
        return $this->settings->wpDataTableId;
    }

    public function getTitle(): string
    {
        return $this->settings->title;
    }

    public function getEngine(): string
    {
        return $this->settings->engine;
    }

    public function getType(): string
    {
        return $this->settings->type;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRenderData(): array
    {
        return $this->settings->renderData;
    }

    /**
     * The persisted `wpdatacharts` row, keyed by column name.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'               => $this->settings->id,
            'wpdatatable_id'   => $this->settings->wpDataTableId,
            'title'            => $this->settings->title,
            'engine'           => $this->settings->engine,
            'type'             => $this->settings->type,
            'json_render_data' => wp_json_encode($this->settings->renderData),
        ];
    }
}
