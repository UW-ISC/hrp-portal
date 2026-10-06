<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Rest\Chart;

use WPDataTables\Controllers\Controller;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Common\Exceptions\NotFoundException;
use WPDataTables\Common\Rest\RestSanitizer;
use WPDataTables\Services\Chart\ChartEngineService;
use WPDataTables\Services\Chart\ChartService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * DELETE /wpdatatables/v1/charts/{id} — delete a chart.
 *
 * Delegates to the auth-decoupled {@see ChartService::deleteChart()} (the same
 * engine the legacy admin browse-charts delete now wraps). 404 if the chart
 * does not exist.
 *
 * @package WPDataTables\Controllers\Rest\Chart
 */
class DeleteChartController extends Controller
{
    /** @var ChartService */
    private $chartService;

    /** @var ChartEngineService */
    private $chartEngineService;

    public function __construct(ChartService $chartService, ChartEngineService $chartEngineService)
    {
        $this->chartService = $chartService;
        $this->chartEngineService = $chartEngineService;
    }

    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response
     * @throws InvalidArgumentException When the id is missing/zero.
     * @throws NotFoundException        When no chart exists for the id.
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        $chartId = RestSanitizer::id($data->get_param('id'));

        if ($chartId === 0) {
            throw new InvalidArgumentException('A valid chart id is required.');
        }

        if (!$this->chartEngineService->getChartDataById($chartId)) {
            throw new NotFoundException('Chart not found.');
        }

        $deleted = $this->chartService->deleteChart($chartId);

        return new WP_REST_Response([
            'id'      => $chartId,
            'deleted' => $deleted,
        ], 200);
    }
}
