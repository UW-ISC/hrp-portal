<?php

defined('ABSPATH') or die('Access denied.');

use WPDataTables\Entity\Chart\RuntimeChart;
use WPDataTables\Plugin\Plugin;
use WPDataTables\Services\Chart\ChartEngineService;
use WPDataTables\Services\Chart\ChartService;

/**
 * WPDataChart
 *
 * Backward-compatibility facade. The implementation lives in
 * {@see RuntimeChart} (data binding + render pipeline) and
 * {@see ChartEngineService} (build / persistence helpers). Tier chart engines
 * (Highcharts, Apexcharts, Highstock) extend this class from tier folders.
 */
class WPDataChart extends RuntimeChart
{
    /**
     * @return ChartEngineService
     */
    private static function engineService()
    {
        return Plugin::container()->get(ChartEngineService::class);
    }

    /**
     * @param array<string, mixed> $constructedChartData
     * @param bool                 $loadFromDB
     *
     * @return RuntimeChart
     */
    public static function build($constructedChartData, $loadFromDB = false)
    {
        return self::engineService()->build((array) $constructedChartData, (bool) $loadFromDB);
    }

    /**
     * @param int $chartId
     *
     * @return object|false
     */
    public static function getChartDataById($chartId)
    {
        return self::engineService()->getChartDataById((int) $chartId);
    }

    /**
     * @return array<int, mixed>|null
     */
    public static function getAll()
    {
        return self::engineService()->getAll();
    }

    /**
     * Delete chart by ID (admin browse screen — keeps nonce/cap gate).
     *
     * @param int $chartId
     *
     * @return bool
     */
    public static function delete($chartId)
    {
        if (!isset($_REQUEST['wdtNonce']) || empty($chartId) || !current_user_can('manage_options')
            || !wp_verify_nonce($_REQUEST['wdtNonce'], 'wdtDeleteChartNonce')) {
            return false;
        }

        return Plugin::container()->get(ChartService::class)->deleteChart((int) $chartId);
    }
}
