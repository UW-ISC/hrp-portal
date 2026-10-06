<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\ValueObjects\Chart;

use WPDataTables\Common\Exceptions\InvalidArgumentException;

/**
 * Immutable settings for a persisted chart row.
 *
 * Wraps the columns of the `wpdatacharts` table. The `json_render_data` blob is
 * kept decoded in `$renderData`; the provider hierarchy (ChartJs / Google) and
 * the `ChartType` / `ChartRange` value objects live with the chart provider
 * services.
 *
 * @package WPDataTables\ValueObjects\Chart
 */
final class ChartSettings
{
    /** @var int */
    public int $id;

    /** @var int */
    public int $wpDataTableId;

    /** @var string */
    public string $title;

    /** @var string */
    public string $engine;

    /** @var string */
    public string $type;

    /**
     * Decoded `json_render_data` blob.
     *
     * @var array<string, mixed>
     */
    public array $renderData;

    /**
     * @param int                  $id
     * @param int                  $wpDataTableId
     * @param string               $title
     * @param string               $engine
     * @param string               $type
     * @param array<string, mixed> $renderData
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        int $id,
        int $wpDataTableId,
        string $title,
        string $engine,
        string $type,
        array $renderData = []
    ) {
        if ($id < 0) {
            throw new InvalidArgumentException('Chart id must not be negative.');
        }

        $this->id = $id;
        $this->wpDataTableId = $wpDataTableId;
        $this->title = $title;
        $this->engine = $engine;
        $this->type = $type;
        $this->renderData = $renderData;
    }
}
