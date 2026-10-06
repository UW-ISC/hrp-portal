<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Chart;

use Exception;
use WPDataTables\Entity\Chart\RuntimeChart;
use WPDataTables\Repository\Chart\ChartRepositoryInterface;
use WDTException;

/**
 * Chart engine orchestration — build, data binding, save, and render entry points.
 *
 * Replaces the static factory/helpers that lived on legacy {@see \WPDataChart}.
 * Engine-specific render logic remains on {@see RuntimeChart} subclasses
 * ({@see \WPDataTables\Entity\Chart\GoogleRuntimeChart},
 * {@see \WPDataTables\Entity\Chart\ChartJsRuntimeChart}, tier engines).
 *
 * @package WPDataTables\Services\Chart
 */
class ChartEngineService
{
    /** @var bool */
    private static $chartAutoloaderRegistered = false;

    /** @var ChartRepositoryInterface */
    private $chartRepository;

    public function __construct(ChartRepositoryInterface $chartRepository)
    {
        $this->chartRepository = $chartRepository;
    }

    /**
     * Build a runtime chart instance for the requested engine.
     *
     * @param array<string, mixed> $constructedChartData
     * @param bool                 $loadFromDB
     *
     * @return RuntimeChart
     * @throws Exception
     */
    public function build(array $constructedChartData, bool $loadFromDB = false): RuntimeChart
    {
        $chartEngineMap = self::getChartEngineMap();
        $engine = isset($constructedChartData['engine'])
            ? strtolower(sanitize_text_field($constructedChartData['engine']))
            : 'google';

        if (!isset($chartEngineMap[$engine])) {
            $engine = 'google';
        }

        self::registerChartAutoloader();

        $chartClass = $chartEngineMap[$engine];
        if (!class_exists($chartClass, true)) {
            $chartClass = $chartEngineMap['google'];
            if (!class_exists($chartClass, true)) {
                throw new Exception('Unable to load chart class for engine: ' . $engine);
            }
            $engine = 'google';
        }

        $constructedChartData['engine'] = $engine;

        /** @var RuntimeChart $chart */
        $chart = new $chartClass($constructedChartData, $loadFromDB);

        return $chart;
    }

    /**
     * Save a chart configuration and return its id + shortcode.
     *
     * @param array<string, mixed> $chartData
     *
     * @return array{id: int|null, shortcode: string}
     * @throws WDTException
     */
    public function saveChart(array $chartData): array
    {
        $chart = $this->build($chartData);
        $chart->save();

        return [
            'id'        => $chart->getId(),
            'shortcode' => $chart->getShortCode(),
        ];
    }

    /**
     * Prepare chart render data from a wizard payload (live preview).
     *
     * @param array<string, mixed> $chartData
     *
     * @return mixed
     * @throws WDTException
     */
    public function returnChartData(array $chartData)
    {
        $chart = $this->build($chartData);

        return $chart->returnData();
    }

    /**
     * Render a chart to HTML (frontend shortcode / block).
     *
     * @param array<string, mixed> $chartData Must include `id` when loading from DB.
     *
     * @return string|false
     * @throws WDTException
     */
    public function renderChart(array $chartData)
    {
        $chart = $this->build($chartData, !empty($chartData['id']));

        return $chart->render();
    }

    /**
     * @param int $chartId
     *
     * @return object|false
     */
    public function getChartDataById(int $chartId)
    {
        if ($chartId === 0) {
            return false;
        }

        $chartData = $this->chartRepository->findRowById($chartId);

        if ($chartData === null) {
            return false;
        }

        return $chartData;
    }

    /**
     * @return array<int, mixed>|null
     */
    public function getAll()
    {
        return $this->chartRepository->findAllIdTitle();
    }

    /**
     * Maps supported chart engine keys to class names.
     *
     * @return array<string, string>
     */
    public static function getChartEngineMap(): array
    {
        return [
            'google'     => 'WdtGoogleChart\\WdtGoogleChart',
            'chartjs'    => 'WdtChartjsChart\\WdtChartjsChart',
            'highcharts' => 'WdtHighchartsChart\\WdtHighchartsChart',
            'apexcharts' => 'WdtApexchartsChart\\WdtApexchartsChart',
            'highstock'  => 'WdtHighStockChart\\WdtHighstockChart',
        ];
    }

    /**
     * Registers a class-map based autoloader for supported chart engines.
     */
    public static function registerChartAutoloader(): void
    {
        if (self::$chartAutoloaderRegistered) {
            return;
        }

        spl_autoload_register(static function ($className) {
            static $classMap = null;

            if (null === $classMap) {
                $classMap = [
                    'WdtGoogleChart\\WdtGoogleChart'   => WDT_LEGACY_FACADES_PATH . 'class.google.wpdatachart.php',
                    'WdtChartjsChart\\WdtChartjsChart' => WDT_LEGACY_FACADES_PATH . 'class.chartjs.wpdatachart.php',
                ];

                if (defined('WDT_HC_ROOT_PATH')) {
                    $classMap['WdtHighchartsChart\\WdtHighchartsChart'] = WDT_HC_ROOT_PATH . 'source/class.highcharts.wpdatachart.php';
                }

                if (defined('WDT_AC_ROOT_PATH')) {
                    $classMap['WdtApexchartsChart\\WdtApexchartsChart'] = WDT_AC_ROOT_PATH . 'source/class.apexcharts.wpdatachart.php';
                }

                if (defined('WDT_HS_ROOT_PATH')) {
                    $classMap['WdtHighStockChart\\WdtHighstockChart'] = WDT_HS_ROOT_PATH . 'source/class.highstock.wpdatachart.php';
                }
            }

            if (isset($classMap[$className]) && file_exists($classMap[$className])) {
                require_once $classMap[$className];
            }
        });

        self::$chartAutoloaderRegistered = true;
    }
}
