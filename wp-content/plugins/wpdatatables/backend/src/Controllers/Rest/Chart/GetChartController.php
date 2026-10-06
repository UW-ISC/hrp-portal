<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Rest\Chart;

use WPDataTables\Controllers\Controller;
use WPDataTables\Common\Rest\RestSanitizer;
use WPDataTables\Services\Rest\ChartReadService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * GET /wpdatatables/v1/charts/{id} — fetch a chart's stored configuration.
 *
 * @package WPDataTables\Controllers\Rest\Chart
 */
class GetChartController extends Controller
{
    /** @var ChartReadService */
    private $chartReadService;

    public function __construct(ChartReadService $chartReadService)
    {
        $this->chartReadService = $chartReadService;
    }

    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        $chartId = RestSanitizer::id($data->get_param('id'));

        return new WP_REST_Response($this->chartReadService->getChart($chartId), 200);
    }
}
