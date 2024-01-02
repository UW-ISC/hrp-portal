<?php

namespace WdtHighStockChart;
require_once('class.highcharts.wpdatachart.php');

use WDTException;
use DateTime;
use WdtHighchartsChart\WdtHighchartsChart;

class WdtHighstockChart extends WdtHighchartsChart
{

    protected $_highstock_render_data = NULL;

    // Path depending on "Stable version"
    protected $highChartStockSource = '//code.highcharts.com/stock/modules/stock.js';

    /**
     * @return string
     */
    public function getHighChartStockSource()
    {
        return $this->highChartStockSource;
    }

    /**
     * @param string $highChartStockSource
     */
    public function setHighChartStockSource($highChartStockSource)
    {
        $this->highChartStockSource = $highChartStockSource;
    }


    /**
     * WPDT Highcharts constructor.
     *
     * @param array $constructedChartData
     * @param bool $loadFromDB
     * @throws WDTException
     */
    public function __construct(array $constructedChartData, $loadFromDB = false)
    {
        parent::__construct($constructedChartData, $loadFromDB);
        if (get_option('wdtHighChartStableVersion')) {
            $this->setHighChartStockSource(WDT_HS_ASSETS_PATH . 'js/highcharts-stock.js');
        }
        $this->setEngine('highstock');
    }

    /**
     * @return array
     */
    public function prepareRenderOptions()
    {
        $highstockRender = array(
            'title' => array(
                'text' => $this->_show_title ? $this->getTitle() : ''
            ),
            'series' => array(),
            'xAxis' => array()
        );

        $majorAxisType = $this->_render_data['columns'][0]['type'];

        if (in_array(
            $this->_type,
            array(
                'highstock_line_with_markers_chart',
                'highstock_spline_chart',
                'highstock_line_chart',
                'highstock_stepline_chart',
                'highstock_area_chart',
                'highstock_area_spline_chart',
                'highstock_column_chart',
                'highstock_point_markers_only_chart'
            )
        )
        ) {
            for ($i = 1; $i < count($this->_render_data['columns']); $i++) {
                $seriesEntry = array(
                    'type' => isset($this->_render_data['options']['series'][$i - 1]['type']) ? $this->_render_data['options']['series'][$i - 1]['type'] : '',
                    'name' => $this->_render_data['series'][$i - 1]['label'],
                    'color' => isset($this->_render_data['options']['series'][$i - 1]['color']) ? $this->_render_data['options']['series'][$i - 1]['color'] : '',
                    'label' => $this->_render_data['series'][$i - 1]['label'],
                    'orig_header' => $this->_render_data['series'][$i - 1]['orig_header'],
                    'data' => array()
                );

                if ($majorAxisType == 'date') {
                    foreach ($this->_render_data['rows'] as $row) {
                        // Format for date-type axis
                        $formattedDate = DateTime::createFromFormat(
                            get_option('wdtDateFormat'),
                            $row[0])->format('d-m-Y');
                        $seriesEntry['data'][] = array(strtotime($formattedDate) * 1000, $row[$i]);
                    }
                } else {
                    foreach ($this->_render_data['rows'] as $row) {
                        // Format for datetime-type axis
                        $formattedDate = DateTime::createFromFormat(
                            get_option('wdtDateFormat') . ' ' . get_option('wdtTimeFormat'),
                            $row[0])->format('d-m-Y');
                        $seriesEntry['data'][] = array(strtotime($formattedDate) * 1000, $row[$i]);
                    }
                }

                // HighStock requires data to be sorted (asc)
                $this->sortNestedArrayByFirstElement($seriesEntry['data']);
                $highstockRender['series'][] = $seriesEntry;
            }
        } else if (in_array(
            $this->_type,
            array(
                'highstock_area_range_chart',
                'highstock_area_spline_range_chart',
                'highstock_column_range_chart'
            )
        )
        ) {
            $seriesEntry = array(
                'name' => isset($this->_render_data['options']['series'][0]) ?
                    $this->_render_data['options']['series'][0]['label'] : '',
                'color' => isset($this->_render_data['options']['series'][0]) ?
                    $this->_render_data['options']['series'][0]['color'] : '',
                'data' => array()
            );

            if ($majorAxisType == 'date') {
                foreach ($this->_render_data['rows'] as $row) {
                    $formattedDate = DateTime::createFromFormat(
                        get_option('wdtDateFormat'),
                        $row[0])->format('d-m-Y');
                    $seriesEntry['data'][] = array(strtotime($formattedDate) * 1000, $row[1], $row[2]);
                }
            } else {
                foreach ($this->_render_data['rows'] as $row) {
                    $formattedDate = DateTime::createFromFormat(
                        get_option('wdtDateFormat') . ' ' . get_option('wdtTimeFormat'),
                        $row[0])->format('d-m-Y');
                    $seriesEntry['data'][] = array(strtotime($formattedDate) * 1000, $row[1], $row[2]);
                }
            }
            $this->sortNestedArrayByFirstElement($seriesEntry['data']);
            $highstockRender['series'][] = $seriesEntry;
        } else if (in_array(
            $this->_type,
            array(
                'highstock_candlestick_chart',
                'highstock_ohlc_chart',
                'highstock_hlc_chart',
            )
        )
        ) {
            $seriesEntry = array(
                'name' => isset($this->_render_data['options']['series'][0]) ?
                    $this->_render_data['options']['series'][0]['label'] : '',
                'color' => isset($this->_render_data['options']['series'][0]) ?
                    $this->_render_data['options']['series'][0]['color'] : '',
                'data' => array()
            );

            if ($majorAxisType == 'date') {
                foreach ($this->_render_data['rows'] as $row) {
                    $formattedDate = DateTime::createFromFormat(
                        get_option('wdtDateFormat'),
                        $row[0])->format('d-m-Y');
                    $seriesEntry['data'][] = array(strtotime($formattedDate) * 1000, $row[1], $row[2], $row[3], $row[4]);
                }
            } else {
                foreach ($this->_render_data['rows'] as $row) {
                    $formattedDate = DateTime::createFromFormat(
                        get_option('wdtDateFormat') . ' ' . get_option('wdtTimeFormat'),
                        $row[0])->format('d-m-Y');
                    $seriesEntry['data'][] = array(strtotime($formattedDate) * 1000, $row[1], $row[2], $row[3], $row[4]);
                }
            }

            $this->sortNestedArrayByFirstElement($seriesEntry['data']);
            $highstockRender['series'][] = $seriesEntry;
        }

        return $highstockRender;
    }

    /**
     * @return mixed|null
     */
    public function returnRenderData()
    {
        return $this->_highstock_render_data;
    }

    /**
     * @return array
     */
    public function formRenderDataForDb()
    {
        return array(
            'selected_columns' => $this->getSelectedColumns(),
            'range_type' => $this->getRangeType(),
            'row_range' => $this->getRowRange(),
            'follow_filtering' => $this->getFollowFiltering(),
            'render_data' => $this->_render_data,
            'highstock_render_data' => $this->_highstock_render_data,
            'show_grid' => $this->_show_grid,
            'show_title' => $this->_show_title,
            'series_type' => $this->getSeriesType()
        );
    }

    /**
     * @param $js_ext
     * @return false|string
     */
    public function enqueueChartSpecificScripts($js_ext)
    {
        $this->prepareRender();

        wp_enqueue_script('wdt-highcharts', $this->getLibSource(), array(), WDT_CURRENT_VERSION);
        wp_enqueue_script('wdt-highcharts-more', $this->getMoreLibSource(), array(), WDT_CURRENT_VERSION);
        if ($this->isExporting()) {
            wp_enqueue_script('wdt-exporting', $this->getExportingLibSource(), array(), WDT_CURRENT_VERSION);
            wp_enqueue_script('wdt-export-data', $this->getExportingDataLibSource(), array(), WDT_CURRENT_VERSION);
        }
        wp_enqueue_script('wdt-highcharts-accessibility', $this->getAccessibilityLibSource(), array(), WDT_CURRENT_VERSION);

        // Highchart wpDataTable JS library
        wp_enqueue_script('wpdatatables-highcharts', WDT_JS_PATH . 'wdtcharts/highcharts/wdt.highcharts' . $js_ext, array('jquery'), WDT_CURRENT_VERSION);

        wp_enqueue_script('wdt-highstock', $this->getHighChartStockSource(), array(), WDT_CURRENT_VERSION);
        wp_enqueue_script('wpdatatables-highstock', WDT_HS_ASSETS_PATH . 'js/wdt.highstock' . $js_ext, array('jquery'), WDT_CURRENT_VERSION);
        return json_encode($this->_highstock_render_data);
    }

    /**
     * @param $renderData
     * @return void
     */
    public function setSpecificChartProperties($renderData)
    {
        parent::setSpecificChartProperties($renderData);
    }

    /**
     * @return void
     */
    public function shiftXAxisColumnUp()
    {
        /**
         * Check if the date/datetime column is not in the beginning and move it up
         */
        if (count($this->_render_data['columns']) > 1) {
            $shiftNeeded = false;
            $shiftIndex = 0;
            for ($i = 1; $i < count($this->_render_data['columns']); $i++) {
                if ($this->_render_data['columns'][$i]['type'] == 'date'
                    || $this->_render_data['columns'][$i]['type'] == 'datetime') {
                    $shiftNeeded = true;
                    $shiftIndex = $i;
                    break;
                }
            }

            if ($shiftNeeded) {
                $this->shiftColumns($shiftIndex);
            }
        }

        $this->formatShiftedAxes();
    }

    public function setChartRenderData($chartData)
    {
        if (!empty($renderData['highstock_render_data'])) {
            $this->_highstock_render_data = $renderData['highstock_render_data'];
        }
        return parent::setChartRenderData($chartData);
    }

    public function sortNestedArrayByFirstElement(&$array)
    {
        usort($array, function ($a, $b) {
            return $a[0] - $b[0];
        });
    }
}