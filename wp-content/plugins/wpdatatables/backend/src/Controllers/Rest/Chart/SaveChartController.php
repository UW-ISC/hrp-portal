<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Rest\Chart;

use WPDataTables\Controllers\Controller;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Services\Chart\ChartEngineService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * POST /wpdatatables/v1/charts — create a chart.
 *
 * Same persistence path as the admin chart wizard ({@see ChartEngineService::saveChart()}):
 * the JSON body is the chart-data structure the wizard posts as `chart_data`.
 * Any incoming `id` is stripped so a create can never update an existing chart.
 *
 * @package WPDataTables\Controllers\Rest\Chart
 */
class SaveChartController extends Controller
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
     * @throws InvalidArgumentException When the body is missing/invalid.
     * @throws \WDTException
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        $chartData = $data->get_json_params();

        if (empty($chartData) || !is_array($chartData)) {
            throw new InvalidArgumentException('A chart configuration body is required.');
        }

        // Create: strip any inherited id so save() inserts rather than updates.
        unset($chartData['id']);

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
