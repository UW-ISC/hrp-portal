<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Rest;

use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Common\Exceptions\NotFoundException;
use WPDataTables\Services\Chart\ChartEngineService;

/**
 * Shared read logic for chart REST endpoints (admin + public).
 *
 * @package WPDataTables\Services\Rest
 */
class ChartReadService
{
    /** @var ChartEngineService */
    private $chartEngineService;

    public function __construct(ChartEngineService $chartEngineService)
    {
        $this->chartEngineService = $chartEngineService;
    }

    /**
     * @return array<int, mixed>
     */
    public function listCharts()
    {
        $charts = $this->chartEngineService->getAll();

        return $charts ? $charts : [];
    }

    /**
     * @param int $chartId
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function getChart(int $chartId)
    {
        if ($chartId === 0) {
            throw new InvalidArgumentException('A valid chart id is required.');
        }

        $chart = $this->chartEngineService->getChartDataById($chartId);

        if (!$chart) {
            throw new NotFoundException('Chart not found.');
        }

        return ['chart' => $chart];
    }
}
