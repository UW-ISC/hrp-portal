<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Rest\Chart;

use WPDataTables\Controllers\Controller;
use WPDataTables\Services\Rest\ChartReadService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * GET /wpdatatables/v1/charts — list all charts (id + title).
 *
 * @package WPDataTables\Controllers\Rest\Chart
 */
class GetChartsController extends Controller
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
        return new WP_REST_Response($this->chartReadService->listCharts(), 200);
    }
}
