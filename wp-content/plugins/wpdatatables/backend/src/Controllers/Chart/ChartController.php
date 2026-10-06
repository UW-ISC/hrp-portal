<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Chart;

use WPDataChart;
use WPDataTable;
use WPDataTables\Services\Chart\ChartEngineService;
use WPDataTables\Services\Permissions\BrowseListScope;
use WPDataTables\Services\Permissions\PermissionsService;

/**
 * ChartController — admin-ajax handlers for the chart-wizard domain.
 *
 * @package WPDataTables\Controllers\Chart
 */
class ChartController
{
    /** @var ChartEngineService */
    private $chartEngineService;

    /** @var PermissionsService */
    private $permissionsService;

    public function __construct(
        ChartEngineService $chartEngineService,
        PermissionsService $permissionsService
    ) {
        $this->chartEngineService = $chartEngineService;
        $this->permissionsService = $permissionsService;
    }

    /**
     * Render a chart from posted chart data (chart-wizard live preview).
     *
     * @return void
     * @throws \WDTException
     */
    public function showChartFromData()
    {
        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtChartWizardNonce')) {
            exit();
        }

        $chartData = stripslashes_deep($_POST['chart_data']);
        $chartId = $this->extractChartIdFromPostedData($chartData);
        if (!$this->canAccessChartWizard($chartId)) {
            exit();
        }

        echo json_encode($this->chartEngineService->returnChartData($chartData));
        exit();
    }

    /**
     * Save a chart and return its id + shortcode.
     *
     * @return void
     */
    public function saveChart()
    {
        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtChartWizardNonce')) {
            exit();
        }

        $chartData = stripslashes_deep($_POST['chart_data']);
        $chartId = $this->extractChartIdFromPostedData($chartData);
        if ($chartId > 0) {
            if (!$this->permissionsService->canEditChart($chartId)) {
                exit();
            }
        } elseif (!$this->permissionsService->canCreateCharts()) {
            exit();
        }

        echo json_encode($this->chartEngineService->saveChart($chartData));
        exit();
    }

    /**
     * List all tables in JSON (chart-wizard table picker).
     *
     * @return void
     */
    public function listAllTables()
    {
        if (!$this->permissionsService->canCreateCharts()
            && !$this->permissionsService->canEditChart(null)
            && !$this->permissionsService->canListTables()
        ) {
            exit();
        }

        $tables = WPDataTable::getAllTables();
        $allowed = BrowseListScope::allowedTableIds();
        if ($allowed !== null) {
            $allowedLookup = array_fill_keys($allowed, true);
            $tables = array_values(array_filter(
                is_array($tables) ? $tables : [],
                static function ($table) use ($allowedLookup) {
                    $id = is_array($table) ? (int) ($table['id'] ?? 0) : (int) ($table->id ?? 0);

                    return isset($allowedLookup[$id]);
                }
            ));
        }

        echo json_encode($tables);
        exit();
    }

    /**
     * List all charts in JSON.
     *
     * @return void
     */
    public function listAllCharts()
    {
        if (!$this->permissionsService->canListCharts()) {
            exit();
        }

        $charts = $this->chartEngineService->getAll();
        $allowed = BrowseListScope::allowedChartIds();
        if ($allowed !== null) {
            $allowedLookup = array_fill_keys($allowed, true);
            $charts = array_values(array_filter(
                is_array($charts) ? $charts : [],
                static function ($chart) use ($allowedLookup) {
                    $id = is_array($chart) ? (int) ($chart['id'] ?? 0) : (int) ($chart->id ?? 0);

                    return isset($allowedLookup[$id]);
                }
            ));
        }

        echo json_encode($charts);
        exit();
    }

    /**
     * @param mixed $chartData
     * @return int
     */
    private function extractChartIdFromPostedData($chartData): int
    {
        if (is_string($chartData)) {
            $decoded = json_decode($chartData, true);
            if (is_array($decoded)) {
                return (int) ($decoded['id'] ?? $decoded['chart_id'] ?? 0);
            }
        }
        if (is_array($chartData)) {
            return (int) ($chartData['id'] ?? $chartData['chart_id'] ?? 0);
        }
        if (is_object($chartData)) {
            return (int) ($chartData->id ?? $chartData->chart_id ?? 0);
        }

        return 0;
    }

    /**
     * @param int $chartId
     * @return bool
     */
    private function canAccessChartWizard(int $chartId): bool
    {
        if ($chartId > 0) {
            return $this->permissionsService->canEditChart($chartId);
        }

        return $this->permissionsService->canCreateCharts()
            || $this->permissionsService->canEditChart(null);
    }
}
