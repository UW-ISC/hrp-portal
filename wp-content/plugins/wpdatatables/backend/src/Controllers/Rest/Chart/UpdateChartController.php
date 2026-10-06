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
use WP_REST_Request;
use WP_REST_Response;

/**
 * PUT /wpdatatables/v1/charts/{id} — update an existing chart.
 *
 * Same persistence path as {@see SaveChartController} ({@see ChartEngineService::saveChart()}),
 * but the route id is forced onto the chart data so the existing row is updated.
 * 404 if the chart does not exist.
 *
 * @package WPDataTables\Controllers\Rest\Chart
 */
class UpdateChartController extends Controller
{
    /** @var ChartEngineService */
    private $chartEngineService;

    public function __construct(ChartEngineService $chartEngineService)
    {
        $this->chartEngineService = $chartEngineService;
    }

    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response
     * @throws InvalidArgumentException When the id/body is missing/invalid.
     * @throws NotFoundException        When no chart exists for the id.
     * @throws \WDTException
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

        $chartData = $data->get_json_params();

        if (empty($chartData) || !is_array($chartData)) {
            throw new InvalidArgumentException('A chart configuration body is required.');
        }

        // Force the route id so save() updates the existing row.
        $chartData['id'] = $chartId;

        if (empty($chartData['wpdatatable_id'])) {
            throw new InvalidArgumentException('A "wpdatatable_id" is required.');
        }
        if (empty($chartData['type'])) {
            throw new InvalidArgumentException('A chart "type" is required.');
        }

        $result = $this->chartEngineService->saveChart($chartData);

        return new WP_REST_Response($result, 200);
    }
}
