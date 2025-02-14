<?php

namespace WdtHighchartsChart;

defined('ABSPATH') or die('Access denied.');

use WDTException;
use WDTTools;
use WPDataChart;

class WdtHighchartsChart extends WPDataChart
{
    //Chart
    protected $_border_width = 0;
    protected $_border_color = '#4572A7';
    protected $_border_radius = 0;
    protected $_zoom_type = 'undefined';
    protected $_panning = false;
    protected $_pan_key = 'shift';
    protected $_plot_background_color = 'undefined';
    protected $_plot_background_image = 'undefined';
    protected $_plot_border_width = 0;
    protected $_plot_border_color = '#C0C0C0';

    //Axes
    protected $_highcharts_line_dash_style = 'Solid';
    protected $_horizontal_axis_crosshair = false;
    protected $_vertical_axis_crosshair = false;
    protected $_inverted = false;

    // Title
    protected $_title_floating = false;
    protected $_title_align = 'center';
    protected $_subtitle = 'undefined';
    protected $_subtitle_align = 'center';

    // Tooltip
    protected $_tooltip_background_color = 'rgba(255, 255, 255, 0.85)';
    protected $_tooltip_border_width = 1;
    protected $_tooltip_border_color = null;
    protected $_tooltip_border_radius = 3;
    protected $_tooltip_shared = false;
    protected $_tooltip_value_prefix = 'undefined';
    protected $_tooltip_value_suffix = 'undefined';

    // Legend
    protected $_show_legend = true;
    protected $_legend_background_color = '#FFFFFF';
    protected $_legend_title = '';
    protected $_legend_layout = 'horizontal';
    protected $_legend_align = 'center';
    protected $_legend_vertical_align = 'bottom';
    protected $_legend_border_width = 0;
    protected $_legend_border_color = '#909090';
    protected $_legend_border_radius = 0;

    // Exporting
    protected $_exporting = true;
    protected $_exporting_data_labels = false;
    protected $_exporting_file_name = 'Chart';
    protected $_exporting_width = 'undefined';
    protected $_exporting_button_align = 'right';
    protected $_exporting_button_vertical_align = 'top';
    protected $_exporting_button_color = '#666';
    protected $_exporting_button_text = null;

    // Credits
    protected $_credits = true;
    protected $_credits_href = 'https://www.highcharts.com';
    protected $_credits_text = 'Highcharts.com';

    //Render data
    protected $_highcharts_render_data = NULL;

    // Paths depending on "stable version"
    protected $_libSource = '//code.highcharts.com/highcharts.js';
    protected $_moreLibSource = '//code.highcharts.com/highcharts-more.js';
    protected $_threeDLibSource = '//code.highcharts.com/highcharts-3d.js';
    protected $_exportingDataLibSource = '//code.highcharts.com/modules/export-data.js';
    protected $_accessibilityLibSource = '//code.highcharts.com/modules/accessibility.js';
    protected $_exportingLibSource = '//code.highcharts.com/modules/exporting.js';
    protected $_cylinderLibSource = '//code.highcharts.com/modules/cylinder.js';
    protected $_heatMapLibSource = '//code.highcharts.com/modules/heatmap.js';
    protected $_funnelLibSource = '//code.highcharts.com/modules/funnel.js';
    protected $_funnel3DLibSource = '//code.highcharts.com/modules/funnel3d.js';
    protected $_treeMapLibSource = '//code.highcharts.com/modules/treemap.js';


    /**
     * @return int
     */
    public function getBorderWidth()
    {
        return $this->_border_width;
    }

    /**
     * @param int $border_width
     */
    public function setBorderWidth($border_width)
    {
        $this->_border_width = $border_width;
    }

    /**
     * @return string
     */
    public function getBorderColor()
    {
        return $this->_border_color;
    }

    /**
     * @param string $border_color
     */
    public function setBorderColor($border_color)
    {
        $this->_border_color = $border_color;
    }

    /**
     * @return int
     */
    public function getBorderRadius()
    {
        return $this->_border_radius;
    }

    /**
     * @param int $border_radius
     */
    public function setBorderRadius($border_radius)
    {
        $this->_border_radius = $border_radius;
    }

    /**
     * @return string
     */
    public function getZoomType()
    {
        return $this->_zoom_type;
    }

    /**
     * @param string $zoom_type
     */
    public function setZoomType($zoom_type)
    {
        $this->_zoom_type = $zoom_type;
    }

    /**
     * @return bool
     */
    public function isPanning()
    {
        return $this->_panning;
    }

    /**
     * @param bool $panning
     */
    public function setPanning($panning)
    {
        $this->_panning = (bool)$panning;
    }

    /**
     * @return string
     */
    public function getPanKey()
    {
        return $this->_pan_key;
    }

    /**
     * @param string $pan_key
     */
    public function setPanKey($pan_key)
    {
        $this->_pan_key = $pan_key;
    }

    /**
     * @return string
     */
    public function getPlotBackgroundColor()
    {
        return $this->_plot_background_color;
    }

    /**
     * @param string $plot_background_color
     */
    public function setPlotBackgroundColor($plot_background_color)
    {
        $this->_plot_background_color = $plot_background_color;
    }

    /**
     * @return string
     */
    public function getPlotBackgroundImage()
    {
        return $this->_plot_background_image;
    }

    /**
     * @param string $plot_background_image
     */
    public function setPlotBackgroundImage($plot_background_image)
    {
        $this->_plot_background_image = $plot_background_image;
    }

    /**
     * @return int
     */
    public function getPlotBorderWidth()
    {
        return $this->_plot_border_width;
    }

    /**
     * @param int $plot_border_width
     */
    public function setPlotBorderWidth($plot_border_width)
    {
        $this->_plot_border_width = $plot_border_width;
    }

    /**
     * @return string
     */
    public function getPlotBorderColor()
    {
        return $this->_plot_border_color;
    }

    /**
     * @param string $plot_border_color
     */
    public function setPlotBorderColor($plot_border_color)
    {
        $this->_plot_border_color = $plot_border_color;
    }

    /**
     * @return string
     */
    public function getHighchartsLineDashStyle()
    {
        return $this->_highcharts_line_dash_style;
    }

    /**
     * @param string $highcharts_line_dash_style
     */
    public function setHighchartsLineDashStyle($highcharts_line_dash_style)
    {
        $this->_highcharts_line_dash_style = $highcharts_line_dash_style;
    }

    /**
     * @return bool
     */
    public function isHorizontalAxisCrosshair()
    {
        return $this->_horizontal_axis_crosshair;
    }

    /**
     * @param bool $horizontal_axis_crosshair
     */
    public function setHorizontalAxisCrosshair($horizontal_axis_crosshair)
    {
        $this->_horizontal_axis_crosshair = (bool)$horizontal_axis_crosshair;
    }

    /**
     * @return bool
     */
    public function isVerticalAxisCrosshair()
    {
        return $this->_vertical_axis_crosshair;
    }

    /**
     * @param bool $vertical_axis_crosshair
     */
    public function setVerticalAxisCrosshair($vertical_axis_crosshair)
    {
        $this->_vertical_axis_crosshair = (bool)$vertical_axis_crosshair;
    }

    /**
     * @return bool
     */
    public function isInverted()
    {
        return $this->_inverted;
    }

    /**
     * @param bool $inverted
     */
    public function setInverted($inverted)
    {
        $this->_inverted = (bool)$inverted;
    }

    /**
     * @return bool
     */
    public function isTitleFloating()
    {
        return $this->_title_floating;
    }

    /**
     * @param bool $title_floating
     */
    public function setTitleFloating($title_floating)
    {
        $this->_title_floating = (bool)$title_floating;
    }

    /**
     * @return string
     */
    public function getTitleAlign()
    {
        return $this->_title_align;
    }

    /**
     * @param string $title_align
     */
    public function setTitleAlign($title_align)
    {
        $this->_title_align = $title_align;
    }

    /**
     * @return string
     */
    public function getSubtitle()
    {
        return $this->_subtitle;
    }

    /**
     * @param string $subtitle
     */
    public function setSubtitle($subtitle)
    {
        $this->_subtitle = $subtitle;
    }

    /**
     * @return string
     */
    public function getSubtitleAlign()
    {
        return $this->_subtitle_align;
    }

    /**
     * @param string $subtitle_align
     */
    public function setSubtitleAlign($subtitle_align)
    {
        $this->_subtitle_align = $subtitle_align;
    }

    /**
     * @return string
     */
    public function getTooltipBackgroundColor()
    {
        return $this->_tooltip_background_color;
    }

    /**
     * @param string $tooltip_background_color
     */
    public function setTooltipBackgroundColor($tooltip_background_color)
    {
        $this->_tooltip_background_color = $tooltip_background_color;
    }

    /**
     * @return int
     */
    public function getTooltipBorderWidth()
    {
        return $this->_tooltip_border_width;
    }

    /**
     * @param int $tooltip_border_width
     */
    public function setTooltipBorderWidth($tooltip_border_width)
    {
        $this->_tooltip_border_width = $tooltip_border_width;
    }

    /**
     * @return null
     */
    public function getTooltipBorderColor()
    {
        return $this->_tooltip_border_color;
    }

    /**
     * @param null $tooltip_border_color
     */
    public function setTooltipBorderColor($tooltip_border_color)
    {
        $this->_tooltip_border_color = $tooltip_border_color;
    }

    /**
     * @return int
     */
    public function getTooltipBorderRadius()
    {
        return $this->_tooltip_border_radius;
    }

    /**
     * @param int $tooltip_border_radius
     */
    public function setTooltipBorderRadius($tooltip_border_radius)
    {
        $this->_tooltip_border_radius = $tooltip_border_radius;
    }

    /**
     * @return bool
     */
    public function isTooltipShared()
    {
        return $this->_tooltip_shared;
    }

    /**
     * @param bool $tooltip_shared
     */
    public function setTooltipShared($tooltip_shared)
    {
        $this->_tooltip_shared = (bool)$tooltip_shared;
    }

    /**
     * @return string
     */
    public function getTooltipValuePrefix()
    {
        return $this->_tooltip_value_prefix;
    }

    /**
     * @param string $tooltip_value_prefix
     */
    public function setTooltipValuePrefix($tooltip_value_prefix)
    {
        $this->_tooltip_value_prefix = $tooltip_value_prefix;
    }

    /**
     * @return string
     */
    public function getTooltipValueSuffix()
    {
        return $this->_tooltip_value_suffix;
    }

    /**
     * @param string $tooltip_value_suffix
     */
    public function setTooltipValueSuffix($tooltip_value_suffix)
    {
        $this->_tooltip_value_suffix = $tooltip_value_suffix;
    }

    /**
     * @return bool
     */
    public function isShowLegend()
    {
        return $this->_show_legend;
    }

    /**
     * @param bool $show_legend
     */
    public function setShowLegend($show_legend)
    {
        $this->_show_legend = (bool)$show_legend;
    }

    /**
     * @return string
     */
    public function getLegendBackgroundColor()
    {
        return $this->_legend_background_color;
    }

    /**
     * @param string $legend_background_color
     */
    public function setLegendBackgroundColor($legend_background_color)
    {
        $this->_legend_background_color = $legend_background_color;
    }

    /**
     * @return string
     */
    public function getLegendTitle()
    {
        return $this->_legend_title;
    }

    /**
     * @param string $legend_title
     */
    public function setLegendTitle($legend_title)
    {
        $this->_legend_title = $legend_title;
    }

    /**
     * @return string
     */
    public function getLegendLayout()
    {
        return $this->_legend_layout;
    }

    /**
     * @param string $legend_layout
     */
    public function setLegendLayout($legend_layout)
    {
        $this->_legend_layout = $legend_layout;
    }

    /**
     * @return string
     */
    public function getLegendAlign()
    {
        return $this->_legend_align;
    }

    /**
     * @param string $legend_align
     */
    public function setLegendAlign($legend_align)
    {
        $this->_legend_align = $legend_align;
    }

    /**
     * @return string
     */
    public function getLegendVerticalAlign()
    {
        return $this->_legend_vertical_align;
    }

    /**
     * @param string $legend_vertical_align
     */
    public function setLegendVerticalAlign($legend_vertical_align)
    {
        $this->_legend_vertical_align = $legend_vertical_align;
    }

    /**
     * @return int
     */
    public function getLegendBorderWidth()
    {
        return $this->_legend_border_width;
    }

    /**
     * @param int $legend_border_width
     */
    public function setLegendBorderWidth($legend_border_width)
    {
        $this->_legend_border_width = $legend_border_width;
    }

    /**
     * @return string
     */
    public function getLegendBorderColor()
    {
        return $this->_legend_border_color;
    }

    /**
     * @param string $legend_border_color
     */
    public function setLegendBorderColor($legend_border_color)
    {
        $this->_legend_border_color = $legend_border_color;
    }

    /**
     * @return int
     */
    public function getLegendBorderRadius()
    {
        return $this->_legend_border_radius;
    }

    /**
     * @param int $legend_border_radius
     */
    public function setLegendBorderRadius($legend_border_radius)
    {
        $this->_legend_border_radius = $legend_border_radius;
    }

    /**
     * @return bool
     */
    public function isExporting()
    {
        return $this->_exporting;
    }

    /**
     * @param bool $exporting
     */
    public function setExporting($exporting)
    {
        $this->_exporting = (bool)$exporting;
    }

    /**
     * @return bool
     */
    public function isExportingDataLabels()
    {
        return $this->_exporting_data_labels;
    }

    /**
     * @param bool $exporting_data_labels
     */
    public function setExportingDataLabels($exporting_data_labels)
    {
        $this->_exporting_data_labels = (bool)$exporting_data_labels;
    }

    /**
     * @return string
     */
    public function getExportingFileName()
    {
        return $this->_exporting_file_name;
    }

    /**
     * @param string $exporting_file_name
     */
    public function setExportingFileName($exporting_file_name)
    {
        $this->_exporting_file_name = $exporting_file_name;
    }

    /**
     * @return string
     */
    public function getExportingWidth()
    {
        return $this->_exporting_width;
    }

    /**
     * @param string $exporting_width
     */
    public function setExportingWidth($exporting_width)
    {
        $this->_exporting_width = $exporting_width;
    }

    /**
     * @return string
     */
    public function getExportingButtonAlign()
    {
        return $this->_exporting_button_align;
    }

    /**
     * @param string $exporting_button_align
     */
    public function setExportingButtonAlign($exporting_button_align)
    {
        $this->_exporting_button_align = $exporting_button_align;
    }

    /**
     * @return string
     */
    public function getExportingButtonVerticalAlign()
    {
        return $this->_exporting_button_vertical_align;
    }

    /**
     * @param string $exporting_button_vertical_align
     */
    public function setExportingButtonVerticalAlign($exporting_button_vertical_align)
    {
        $this->_exporting_button_vertical_align = $exporting_button_vertical_align;
    }

    /**
     * @return string
     */
    public function getExportingButtonColor()
    {
        return $this->_exporting_button_color;
    }

    /**
     * @param string $exporting_button_color
     */
    public function setExportingButtonColor($exporting_button_color)
    {
        $this->_exporting_button_color = $exporting_button_color;
    }

    /**
     * @return null
     */
    public function getExportingButtonText()
    {
        return $this->_exporting_button_text;
    }

    /**
     * @param null $exporting_button_text
     */
    public function setExportingButtonText($exporting_button_text)
    {
        $this->_exporting_button_text = $exporting_button_text;
    }

    /**
     * @return bool
     */
    public function isCredits()
    {
        return $this->_credits;
    }

    /**
     * @param bool $credits
     */
    public function setCredits($credits)
    {
        $this->_credits = (bool)$credits;
    }

    /**
     * @return string
     */
    public function getCreditsHref()
    {
        return $this->_credits_href;
    }

    /**
     * @param string $credits_href
     */
    public function setCreditsHref($credits_href)
    {
        $this->_credits_href = $credits_href;
    }

    /**
     * @return string
     */
    public function getCreditsText()
    {
        return $this->_credits_text;
    }

    /**
     * @param string $credits_text
     */
    public function setCreditsText($credits_text)
    {
        $this->_credits_text = $credits_text;
    }

    /**
     * @return string
     */
    public function getLibSource()
    {
        return $this->_libSource;
    }

    /**
     * @param string $libSource
     */
    public function setLibSource($libSource)
    {
        $this->_libSource = $libSource;
    }

    /**
     * @return string
     */
    public function getMoreLibSource()
    {
        return $this->_moreLibSource;
    }

    /**
     * @param string $moreLibSource
     */
    public function setMoreLibSource($moreLibSource)
    {
        $this->_moreLibSource = $moreLibSource;
    }

    /**
     * @return string
     */
    public function getThreeDLibSource()
    {
        return $this->_threeDLibSource;
    }

    /**
     * @param string $threeDLibSource
     */
    public function setThreeDLibSource($threeDLibSource)
    {
        $this->_threeDLibSource = $threeDLibSource;
    }

    /**
     * @return string
     */
    public function getExportingDataLibSource()
    {
        return $this->_exportingDataLibSource;
    }

    /**
     * @param string $exportingDataLibSource
     */
    public function setExportingDataLibSource($exportingDataLibSource)
    {
        $this->_exportingDataLibSource = $exportingDataLibSource;
    }

    /**
     * @return string
     */
    public function getAccessibilityLibSource()
    {
        return $this->_accessibilityLibSource;
    }

    /**
     * @param string $accessibilityLibSource
     */
    public function setAccessibilityLibSource($accessibilityLibSource)
    {
        $this->_accessibilityLibSource = $accessibilityLibSource;
    }

    /**
     * @return string
     */
    public function getExportingLibSource()
    {
        return $this->_exportingLibSource;
    }

    /**
     * @param string $exportingLibSource
     */
    public function setExportingLibSource($exportingLibSource)
    {
        $this->_exportingLibSource = $exportingLibSource;
    }

    /**
     * @return string
     */
    public function getCylinderLibSource()
    {
        return $this->_cylinderLibSource;
    }

    /**
     * @param string $cylinderLibSource
     */
    public function setCylinderLibSource($cylinderLibSource)
    {
        $this->_cylinderLibSource = $cylinderLibSource;
    }

    /**
     * @return string
     */
    public function getHeatMapLibSource()
    {
        return $this->_heatMapLibSource;
    }

    /**
     * @param string $heatMapLibSource
     */
    public function setHeatMapLibSource($heatMapLibSource)
    {
        $this->_heatMapLibSource = $heatMapLibSource;
    }

    /**
     * @return string
     */
    public function getFunnelLibSource()
    {
        return $this->_funnelLibSource;
    }

    /**
     * @param string $funnelLibSource
     */
    public function setFunnelLibSource($funnelLibSource)
    {
        $this->_funnelLibSource = $funnelLibSource;
    }

    /**
     * @return string
     */
    public function getFunnel3DLibSource()
    {
        return $this->_funnel3DLibSource;
    }

    /**
     * @param string $funnel3DLibSource
     */
    public function setFunnel3DLibSource($funnel3DLibSource)
    {
        $this->_funnel3DLibSource = $funnel3DLibSource;
    }

    /**
     * @return string
     */
    public function getTreeMapLibSource()
    {
        return $this->_treeMapLibSource;
    }

    /**
     * @param string $treeMapLibSource
     */
    public function setTreeMapLibSource($treeMapLibSource)
    {
        $this->_treeMapLibSource = $treeMapLibSource;
    }


    /**
     * WPDT Highcharts constructor.
     *
     * @param array $constructedChartData
     * @param bool $loadFromDB
     *
     * @throws WDTException
     */
    public function __construct(array $constructedChartData, $loadFromDB = false)
    {
        if ($loadFromDB) {
            if (isset($constructedChartData['id'])) {
                $this->setId((int)$constructedChartData['id']);
            }
            $this->loadFromDB();
        } else {
            parent::__construct($constructedChartData, $loadFromDB);

            $this->setEngine('highcharts');

            // Chart
            $this->setBorderWidth((int)WDTTools::defineDefaultValue($constructedChartData, 'border_width', 0));
            $this->setBorderColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'border_color', '#FFFFFF')));
            $this->setBorderRadius((int)WDTTools::defineDefaultValue($constructedChartData, 'border_radius', 0));
            $this->setZoomType(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'zoom_type', 'undefined')));
            $this->setPanning((bool)(WDTTools::defineDefaultValue($constructedChartData, 'panning', false)));
            $this->setPanKey(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'pan_key', 'shift')));
            $this->setPlotBackgroundColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'plot_background_color', '#FFFFFF')));
            $this->setPlotBackgroundImage(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'plot_background_image', null)));
            $this->setPlotBorderWidth((int)WDTTools::defineDefaultValue($constructedChartData, 'plot_border_width', 0));
            $this->setPlotBorderColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'plot_border_color', '#C0C0C0')));

            // Axes
            $this->setHighchartsLineDashStyle(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'highcharts_line_dash_style', 'Solid')));
            $this->setHorizontalAxisCrosshair((bool)(WDTTools::defineDefaultValue($constructedChartData, 'horizontal_axis_crosshair', false)));
            $this->setVerticalAxisCrosshair((bool)(WDTTools::defineDefaultValue($constructedChartData, 'vertical_axis_crosshair', false)));
            $this->setInverted((bool)(WDTTools::defineDefaultValue($constructedChartData, 'inverted', false)));

            // Title
            $this->setShowTitle((bool)(WDTTools::defineDefaultValue($constructedChartData, 'show_title', true)));
            $this->setTitleFloating((bool)(WDTTools::defineDefaultValue($constructedChartData, 'title_floating', false)));
            $this->setTitleAlign(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'title_align', 'center')));
            $this->setSubtitle(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'subtitle', '')));
            $this->setSubtitleAlign(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'subtitle_align', 'center')));

            // Tooltip
            $this->setTooltipEnabled((bool)(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_enabled', true)));
            $this->setTooltipBackgroundColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_background_color', 'rgba(255, 255, 255, 0.85)')));
            $this->setTooltipBorderWidth(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_border_width', 1)));
            $this->setTooltipBorderColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_border_color', null)));
            $this->setTooltipBorderRadius(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_border_radius', 3)));
            $this->setTooltipShared((bool)(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_shared', false)));
            $this->setTooltipValuePrefix(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_value_prefix', '')));
            $this->setTooltipValueSuffix(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_value_suffix', '')));

            // Legend
            $this->setShowLegend((bool)(WDTTools::defineDefaultValue($constructedChartData, 'show_legend', true)));
            $this->setLegendBackgroundColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_background_color', '#FFFFFF')));
            $this->setLegendTitle(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_title')));
            $this->setLegendLayout(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_layout', 'horizontal')));
            $this->setLegendAlign(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_align', 'center')));
            $this->setLegendVerticalAlign(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_vertical_align', 'bottom')));
            $this->setLegendBorderWidth(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_border_width', 0)));
            $this->setLegendBorderColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_border_color', '#909090')));
            $this->setLegendBorderRadius(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_border_radius', 0)));

            // Exporting
            $this->setExporting((bool)(WDTTools::defineDefaultValue($constructedChartData, 'exporting', true)));
            $this->setExportingDataLabels((bool)(WDTTools::defineDefaultValue($constructedChartData, 'exporting_data_labels', false)));
            $this->setExportingFileName(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'exporting_file_name', 'Chart')));
            $this->setExportingWidth(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'exporting_width', 'undefined')));
            $this->setExportingButtonAlign(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'exporting_button_align', 'right')));
            $this->setExportingButtonVerticalAlign(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'exporting_button_vertical_align', 'top')));
            $this->setExportingButtonColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'exporting_button_color', '#666')));
            $this->setExportingButtonText(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'exporting_button_text', null)));

            // Credits
            $this->setCredits((bool)(WDTTools::defineDefaultValue($constructedChartData, 'credits', true)));
            $this->setCreditsHref(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'credits_href', 'https://www.highcharts.com')));
            $this->setCreditsText(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'credits_text', 'Highcharts.com')));

            // Script paths depending on "stable version"
            if (get_option('wdtHighChartStableVersion')) {
                $this->setLibSource(WDT_JS_PATH . 'wdtcharts/highcharts/highcharts.js');
                $this->setMoreLibSource(WDT_JS_PATH . 'wdtcharts/highcharts/highcharts-more.js');
                $this->setThreeDLibSource(WDT_JS_PATH . 'wdtcharts/highcharts/highcharts-3D.js');
                $this->setCylinderLibSource(WDT_JS_PATH . 'wdtcharts/highcharts/highcharts-cylinder.js');
                $this->setHeatMapLibSource(WDT_JS_PATH . 'wdtcharts/highcharts/highcharts-heatmap.js');
                $this->setFunnelLibSource(WDT_JS_PATH . 'wdtcharts/highcharts/highcharts-funnel.js');
                $this->setFunnel3DLibSource(WDT_JS_PATH . 'wdtcharts/highcharts/highcharts-funnel3D.js');
                $this->setTreeMapLibSource(WDT_JS_PATH . 'wdtcharts/highcharts/highcharts-treemap.js');
                $this->setExportingLibSource(WDT_JS_PATH . 'wdtcharts/highcharts/highcharts-exporting.js');
                $this->setExportingDataLibSource(WDT_JS_PATH . 'wdtcharts/highcharts/highcharts-exporting-data.js');
                $this->setAccessibilityLibSource(WDT_JS_PATH . 'wdtcharts/highcharts/highcharts-accessibility.js');
            }
        }

    }

    /**
     * @return void
     */
    public function defineSeries()
    {
        if ($this->_type == 'highcharts_basic_column_chart' ||
            $this->_type == 'highcharts_line_chart' ||
            $this->_type == 'highcharts_basic_bar_chart' ||
            $this->_type == 'highcharts_spline_chart' ||
            $this->_type == 'highcharts_basic_area_chart' ||
            $this->_type == 'highcharts_polar_chart' ||
            $this->_type == 'highcharts_spiderweb_chart'
        ) {

            if (!empty($this->_user_defined_series_data)) {
                $seriesIndex = 0;
                $i = 1;
                foreach ($this->_user_defined_series_data as $series_data) {
                    if (!empty($series_data['color']) || !empty($series_data['type']) || isset($series_data['yAxis'])) {
                        $this->_render_data['options']['series'][$seriesIndex] = array(
                            'color' => $series_data['color'],
                            'type' => $series_data['type'],
                            'yAxis' => $series_data['yAxis'] == 1 ? $i : 0
                        );
                        if ($series_data['yAxis']) {
                            $i++;
                        }
                    }
                    $seriesIndex++;
                }
            }
        } else {
            parent::defineSeries();
        }
    }

    /**
     * @return void
     */
    public function prepareRender()
    {
        $renderData = '_' . $this->getEngine() . '_render_data';
        $this->{$renderData}['wdtNumberFormat'] = get_option('wdtNumberFormat');
        $this->{$renderData}['options'] = $this->prepareRenderOptions();
        $this->{$renderData}['type'] = $this->getType();
        if ($this->_follow_filtering) {
            if (isset($this->_render_data['column_indexes'])) {
                $this->{$renderData}['column_indexes'] = $this->_render_data['column_indexes'];
            }
        }

        // Chart
        if (!$this->_responsiveWidth) {
            $this->{$renderData}['width'] = $this->getWidth();
        }
        $this->{$renderData}['height'] = $this->getHeight();
        $this->{$renderData}['options']['chart']['backgroundColor'] = $this->getBackgroundColor();
        $this->{$renderData}['options']['chart']['borderWidth'] = $this->getBorderWidth();
        $this->{$renderData}['options']['chart']['borderColor'] = $this->getBorderColor();
        $this->{$renderData}['options']['chart']['borderRadius'] = $this->getBorderRadius();
        $this->{$renderData}['options']['chart']['zoomType'] = $this->getZoomType();
        $this->{$renderData}['options']['chart']['panning'] = $this->isPanning();
        $this->{$renderData}['options']['chart']['panKey'] = $this->getPanKey();
        $this->{$renderData}['options']['chart']['plotBackgroundColor'] = $this->getPlotBackgroundColor();
        $this->{$renderData}['options']['chart']['plotBackgroundImage'] = $this->getPlotBackgroundImage();
        $this->{$renderData}['options']['chart']['plotBorderColor'] = $this->getPlotBorderColor();
        $this->{$renderData}['options']['chart']['plotBorderWidth'] = $this->getPlotBorderWidth();

        // Axes
        if ($this->_type == 'highcharts_spiderweb_chart') {
            $this->{$renderData}['options']['xAxis']['tickmarkPlacement'] = 'on';
            $this->{$renderData}['options']['xAxis']['lineWidth'] = 0;
        }

        if (!$this->_show_grid) {
            $this->{$renderData}['options']['xAxis']['lineWidth'] = 0;
            $this->{$renderData}['options']['xAxis']['minorGridLineWidth'] = 0;
            $this->{$renderData}['options']['xAxis']['lineColor'] = 'transparent';
            $this->{$renderData}['options']['xAxis']['minorTickLength'] = 0;
            $this->{$renderData}['options']['xAxis']['tickLength'] = 0;
            if ($this->getSeriesType() != '') {
                $this->{$renderData}['options']['yAxis'][0]['lineWidth'] = 0;
                $this->{$renderData}['options']['yAxis'][0]['gridLineWidth'] = 0;
                $this->{$renderData}['options']['yAxis'][0]['minorGridLineWidth'] = 0;
                $this->{$renderData}['options']['yAxis'][0]['lineColor'] = 'transparent';
                $this->{$renderData}['options']['yAxis'][0]['labels'] = array('enabled' => false);
                $this->{$renderData}['options']['yAxis'][0]['minorTickLength'] = 0;
                $this->{$renderData}['options']['yAxis'][0]['tickLength'] = 0;
            } else {
                $this->{$renderData}['options']['yAxis']['lineWidth'] = 0;
                $this->{$renderData}['options']['yAxis']['gridLineWidth'] = 0;
                $this->{$renderData}['options']['yAxis']['minorGridLineWidth'] = 0;
                $this->{$renderData}['options']['yAxis']['lineColor'] = 'transparent';
                $this->{$renderData}['options']['yAxis']['labels'] = array('enabled' => false);
                $this->{$renderData}['options']['yAxis']['minorTickLength'] = 0;
                $this->{$renderData}['options']['yAxis']['tickLength'] = 0;
            }
        }
        if ($this->getSeriesType() != '') {
            $this->{$renderData}['options']['yAxis'][0]['gridLineDashStyle'] = $this->getHighchartsLineDashStyle();
        } else {
            $this->{$renderData}['options']['yAxis']['gridLineDashStyle'] = $this->getHighchartsLineDashStyle();
        }
        if (!empty($this->_render_data['options']['hAxis']['title'])) {
            $this->{$renderData}['options']['xAxis']['title']['text'] = $this->_render_data['options']['hAxis']['title'];
        }
        $this->{$renderData}['options']['xAxis']['crosshair'] = $this->isHorizontalAxisCrosshair();
        if ($this->getSeriesType() != '') {
            $this->{$renderData}['options']['yAxis'][0]['title']['text'] = !empty($this->_render_data['options']['vAxis']['title']) ?
                $this->_render_data['options']['vAxis']['title'] : "";
        } else {
            $this->{$renderData}['options']['yAxis']['title']['text'] = !empty($this->_render_data['options']['vAxis']['title']) ?
                $this->_render_data['options']['vAxis']['title'] : "";
        }
        if ($this->getSeriesType() != '') {
            $this->{$renderData}['options']['yAxis'][0]['crosshair'] = $this->isVerticalAxisCrosshair();
        } else {
            $this->{$renderData}['options']['yAxis']['crosshair'] = $this->isVerticalAxisCrosshair();
        }

        if ($this->getVerticalAxisMin() != '') {
            if ($this->getSeriesType() != '') {
                $this->{$renderData}['options']['yAxis'][0]['min'] = (float)$this->getVerticalAxisMin();
            } else {
                $this->{$renderData}['options']['yAxis']['min'] = (float)$this->getVerticalAxisMin();
            }
        }
        if ($this->getVerticalAxisMax() != '') {
            if ($this->getSeriesType() != '') {
                $this->{$renderData}['options']['yAxis'][0]['max'] = (float)$this->getVerticalAxisMax();
            } else {
                $this->{$renderData}['options']['yAxis']['max'] = (float)$this->getVerticalAxisMax();
            }
        }
        $this->{$renderData}['options']['chart']['inverted'] = $this->isInverted();

        // Title
        if ($this->isShowTitle()) {
            $this->{$renderData}['options']['title']['text'] = $this->getTitle();
        } else {
            $this->{$renderData}['options']['title']['text'] = '';
        }
        $this->{$renderData}['options']['title']['floating'] = $this->isTitleFloating();
        $this->{$renderData}['options']['title']['align'] = $this->getTitleAlign();
        $this->{$renderData}['options']['subtitle']['text'] = $this->getSubtitle();
        $this->{$renderData}['options']['subtitle']['align'] = $this->getSubtitleAlign();

        // Tooltip
        $this->{$renderData}['options']['tooltip']['enabled'] = $this->isTooltipEnabled();
        $this->{$renderData}['options']['tooltip']['backgroundColor'] = ($this->getTooltipBackgroundColor() != '') ? $this->getTooltipBackgroundColor() : 'rgba(255, 255, 255, 0.85)';
        $this->{$renderData}['options']['tooltip']['borderWidth'] = $this->getTooltipBorderWidth();
        $this->{$renderData}['options']['tooltip']['borderColor'] = $this->getTooltipBorderColor();
        $this->{$renderData}['options']['tooltip']['borderRadius'] = $this->getTooltipBorderRadius();
        $this->{$renderData}['options']['tooltip']['shared'] = $this->isTooltipShared();
        $this->{$renderData}['options']['tooltip']['valuePrefix'] = $this->getTooltipValuePrefix();
        $this->{$renderData}['options']['tooltip']['valueSuffix'] = $this->getTooltipValueSuffix();

        // Legend
        if ($this->{$renderData}['type'] == 'highcharts_treemap_level_chart') {
            $this->{$renderData}['options']['legend']['enabled'] = false;
        } else {
            $this->{$renderData}['options']['legend']['enabled'] = $this->isShowLegend();
        }
        $this->{$renderData}['options']['legend']['backgroundColor'] = $this->getLegendBackgroundColor();
        $this->{$renderData}['options']['legend']['title']['text'] = $this->getLegendTitle();
        $this->{$renderData}['options']['legend']['layout'] = $this->getLegendLayout();
        $this->{$renderData}['options']['legend']['align'] = $this->getLegendAlign();
        $this->{$renderData}['options']['legend']['verticalAlign'] = $this->getLegendVerticalAlign();
        $this->{$renderData}['options']['legend']['borderWidth'] = $this->getLegendBorderWidth();
        $this->{$renderData}['options']['legend']['borderColor'] = $this->getLegendBorderColor();
        $this->{$renderData}['options']['legend']['borderRadius'] = $this->getLegendBorderRadius();

        // Exporting
        $this->{$renderData}['options']['exporting']['enabled'] = $this->isExporting();
        $this->{$renderData}['options']['exporting']['chartOptions']['plotOptions']['series']['dataLabels']['enabled'] = $this->isExportingDataLabels();
        $this->{$renderData}['options']['exporting']['filename'] = $this->getExportingFileName();
        $this->{$renderData}['options']['exporting']['width'] = $this->getExportingWidth();
        $this->{$renderData}['options']['exporting']['buttons']['contextButton']['align'] = $this->getExportingButtonAlign();
        $this->{$renderData}['options']['exporting']['buttons']['contextButton']['verticalAlign'] = $this->getExportingButtonVerticalAlign();
        if ($this->getExportingButtonColor() == '') {
            $this->{$renderData}['options']['exporting']['buttons']['contextButton']['symbolStroke'] = '#666';
        } else {
            $this->{$renderData}['options']['exporting']['buttons']['contextButton']['symbolStroke'] = $this->getExportingButtonColor();
        }
        $this->{$renderData}['options']['exporting']['buttons']['contextButton']['text'] = $this->getExportingButtonText();
        if ($this->isExporting() && in_array($this->getType(), ['highcharts_treemap_level_chart',
                'highcharts_treemap_chart'])) {
            $this->{$renderData}['options']['exporting']['buttons']['contextButton']['menuItems'] = ['viewFullscreen',
                'printChart',
                'separator',
                'downloadPNG',
                'downloadJPEG',
                'downloadPDF',
                'downloadSVG'];
        }

        // Credits
        $this->{$renderData}['options']['credits']['enabled'] = $this->isCredits();
        $this->{$renderData}['options']['credits']['href'] = $this->getCreditsHref();
        $this->{$renderData}['options']['credits']['text'] = $this->getCreditsText();

        $this->{$renderData} = apply_filters('wpdatatables_filter_' . $this->getEngine() . '_render_data', $this->{$renderData}, $this->getId(), $this);

    }

    /**
     * @return array
     */
    public function prepareRenderOptions()
    {
        $highchartsRender = array(
            'title' => array(
                'text' => $this->_show_title ? $this->getTitle() : ''
            ),
            'series' => array(),
            'xAxis' => array()
        );

        if ($this->_type == 'highcharts_basic_column_chart') {
            $this->setSeriesType('column');
        } else if ($this->_type == 'highcharts_line_chart') {
            $this->setSeriesType('line');
        } else if ($this->_type == 'highcharts_basic_bar_chart') {
            $this->setSeriesType('bar');
        } else if ($this->_type == 'highcharts_spline_chart') {
            $this->setSeriesType('spline');
        } else if ($this->_type == 'highcharts_basic_area_chart') {
            $this->setSeriesType('area');
        }

        if (!in_array(
            $this->_type,
            array(
                'highcharts_pie_chart',
                'highcharts_pie_with_gradient_chart',
                'highcharts_donut_chart',
                'highcharts_3d_pie_chart',
                'highcharts_3d_donut_chart',
                'highcharts_treemap_chart',
                'highcharts_treemap_level_chart'
            )
        )
        ) {
            for ($i = 0; $i < count($this->_render_data['columns']); $i++) {
                if ($i == 0) {
                    $highchartsRender['xAxis']['categories'] = array();
                } else {
                    $seriesEntry = array(
                        'type' => isset($this->_render_data['options']['series'][$i - 1]['type']) ? $this->_render_data['options']['series'][$i - 1]['type'] : '',
                        'name' => $this->_render_data['series'][$i - 1]['label'],
                        'color' => isset($this->_render_data['options']['series'][$i - 1]['color']) ? $this->_render_data['options']['series'][$i - 1]['color'] : '',
                        'label' => $this->_render_data['series'][$i - 1]['label'],
                        'orig_header' => $this->_render_data['series'][$i - 1]['orig_header'],
                        'data' => array()
                    );

                    if ($this->_type == 'highcharts_polar_chart' || $this->_type == 'highcharts_spiderweb_chart') {
                        $seriesEntry['pointPlacement'] = 'on';
                    }

                    if ($this->getSeriesType() != '') {
                        $seriesEntry['yAxis'] = isset($this->_render_data['options']['series'][$i - 1]['yAxis']) ? $this->_render_data['options']['series'][$i - 1]['yAxis'] : 0;

                        if (isset($this->_render_data['options']['series'][$i - 1]['yAxis']) && $this->_render_data['options']['series'][$i - 1]['yAxis']) {
                            $yAxis = [
                                'title' => [
                                    'text' => $this->_render_data['series'][$i - 1]['label']
                                ],
                                'opposite' => true
                            ];
                        } else {
                            $yAxis = [];
                        }

                        $highchartsRender['yAxis'][] = $yAxis;

                    }
                }
                foreach ($this->_render_data['rows'] as $row) {
                    if ($i == 0) {
                        if ($this->_type != 'highcharts_scatter_plot' || ($this->_render_data['columns'][0]['type'] != 'number')) {
                            $highchartsRender['xAxis']['categories'][] = $row[$i];
                        }
                    } else {
                        if ($this->_type == 'highcharts_scatter_plot' && ($this->_render_data['columns'][0]['type'] == 'number')) {
                            $seriesEntry['data'][] = [$row[$i - 1], $row[$i]];
                        } else {
                            $seriesEntry['data'][] = $row[$i];
                        }
                    }
                }
                if ($i != 0) {
                    $highchartsRender['series'][] = $seriesEntry;
                }
            }
        } else {
            if (
                in_array(
                    $this->_type,
                    array(
                        'highcharts_pie_chart',
                        'highcharts_pie_with_gradient_chart',
                        'highcharts_donut_chart',
                        'highcharts_3d_pie_chart',
                        'highcharts_3d_donut_chart'
                    )
                )
            ) {
                $highchartsRender['series'] = array(
                    array(
                        'type' => 'pie',
                        'name' => $this->_render_data['columns'][1]['label'],
                        'data' => $this->_render_data['rows']
                    )
                );
                unset($highchartsRender['xAxis']);
            } else if ($this->_type == 'highcharts_treemap_chart') {
                $data = [];
                foreach ($this->_render_data['rows'] as $row) {
                    $data[] = [
                        'name' => $row[0],
                        'value' => $row[1],
                        'colorValue' => $row[1]
                    ];
                }
                $highchartsRender['series'] = array(
                    array(
                        'type' => 'treemap',
                        'layoutAlgorithm' => 'squarified',
                        'name' => $this->_render_data['columns'][1]['label'],
                        'data' => $data
                    )
                );
                unset($highchartsRender['xAxis']);
                $highchartsRender['colorAxis'] = array(

                    'minColor' => '#FFFFBF',
                    'maxColor' => '#006837'
                );
            } else if ($this->_type == 'highcharts_treemap_level_chart') {
                $data = [];

                for ($i = 0; $i < count($this->_render_data['columns']); $i++) {
                    if (!empty($this->_render_data['rows'])) {
                        foreach ($this->_render_data['rows'] as $row) {
                            if ($i > 0) {
                                if ($this->_render_data['columns'][$i]['type'] == 'number') {
                                    $column_name[$i - 1] = $this->_render_data['columns'][$i]['label'];
                                }

                            } else {
                                $helperArr[] = $row[$i];
                            }
                        }

                        foreach ($helperArr as $helperKey => $helperValue) {
                            if ($i == 0) {
                                $data[] = [
                                    'id' => 'id_' . $helperKey,
                                    'name' => $helperValue,
                                    'color' => '#006837'
                                ];
                            }
                        }
                    }
                }
                $row_numbers = $this->_render_data['rows'];
                for ($y = 0; $y < count($row_numbers); $y++) {
                    for ($z = 0; $z < count($row_numbers[$y]) - 1; $z++) {

                        $data[] = [
                            'id' => 'id_' . $y . '_' . $z,
                            'name' => $column_name[$z],
                            'parent' => 'id_' . $y,
                            'value' => $this->_render_data['rows'][$y][$z + 1],
                            'colorValue' => $this->_render_data['rows'][$y][$z + 1],
                        ];

                    }
                }

                $highchartsRender['colorAxis'] = array(
                    'minColor' => '#FFFFBF',
                    'maxColor' => '#006837'

                );

                $highchartsRender['series'] = array(
                    array(
                        'type' => 'treemap',
                        'layoutAlgorithm' => 'squarified',
                        'allowDrillToNode' => true,
                        'animationLimit' => 1000,
                        'dataLabels' => [
                            'enabled' => false
                        ],
                        'drillUpButton' => [
                            'text' => '< Back'
                        ],
                        'levelIsConstant' => false,
                        'levels' => [
                            [
                                'level' => 1,
                                'dataLabels' => [
                                    'enabled' => true
                                ],
                                'borderWidth' => 0,
                                'states' => [
                                    'hover' => [
                                        'brightness' => 0.1
                                    ]
                                ]
                            ],

                        ],
                        'data' => $data,
                    )
                );

                unset($highchartsRender['xAxis']);
            }
        }
        return $highchartsRender;
    }

    /**
     * @param $js_ext
     *
     * @return false|string
     */
    public function enqueueChartSpecificScripts($js_ext)
    {
        $this->prepareRender();

        wp_enqueue_script('wdt-highcharts', $this->getLibSource(), array(), WDT_CURRENT_VERSION);
        wp_enqueue_script('wdt-highcharts-more', $this->getMoreLibSource(), array('wdt-highcharts'), WDT_CURRENT_VERSION);
        wp_enqueue_script('wdt-highcharts3d', $this->getThreeDLibSource(), array('wdt-highcharts'), WDT_CURRENT_VERSION);
        if (in_array($this->getType(), ['highcharts_treemap_level_chart', 'highcharts_treemap_chart'])) {
            wp_enqueue_script('wdt-heatmap', $this->getHeatMapLibSource(), array('wdt-highcharts'), WDT_CURRENT_VERSION);
            wp_enqueue_script('wdt-treemap', $this->getTreeMapLibSource(), array('wdt-highcharts'), WDT_CURRENT_VERSION);
        }
        if ($this->getType() == 'highcharts_funnel3d_chart') {
            wp_enqueue_script('wdt-cylinder', $this->getCylinderLibSource(), array('wdt-highcharts'), WDT_CURRENT_VERSION);
            wp_enqueue_script('wdt-funnel', $this->getFunnelLibSource(), array('wdt-highcharts'), WDT_CURRENT_VERSION);
            wp_enqueue_script('wdt-funnel3d', $this->getFunnel3DLibSource(), array('wdt-highcharts'), WDT_CURRENT_VERSION);
        }
        if ($this->getType() == 'highcharts_funnel_chart') {
            wp_enqueue_script('wdt-funnel', $this->getFunnelLibSource(), array('wdt-highcharts'), WDT_CURRENT_VERSION);
        }
        if ($this->isExporting()) {
            wp_enqueue_script('wdt-exporting', $this->getExportingLibSource(), array('wdt-highcharts'), WDT_CURRENT_VERSION);
            if (!in_array($this->getType(), ['highcharts_treemap_level_chart', 'highcharts_treemap_chart'])) {
                wp_enqueue_script('wdt-export-data', $this->getExportingDataLibSource(), array('wdt-highcharts'), WDT_CURRENT_VERSION);
            }
        }
        wp_enqueue_script('wdt-highcharts-accessibility', $this->getAccessibilityLibSource(), array('wdt-highcharts'), WDT_CURRENT_VERSION);
        // Highchart wpDataTable JS library
        wp_enqueue_script('wpdatatables-highcharts', WDT_HC_ASSETS_URL . 'js/wdt.highcharts' . $js_ext, array('jquery',
            'wdt-highcharts'), WDT_CURRENT_VERSION);

        return json_encode($this->_highcharts_render_data);
    }

    /**
     * @param $renderData
     *
     * @return void
     */
    public function setSpecificChartProperties($renderData)
    {
        $renderChartData = $this->getEngine() . '_render_data';
        // Chart
        $this->setBackgroundColor(isset($renderData[$renderChartData]['options']['chart']['backgroundColor']) ? $renderData[$renderChartData]['options']['chart']['backgroundColor'] : '#FFFFFF');
        $this->setBorderWidth(isset($renderData[$renderChartData]['options']['chart']['borderWidth']) ? $renderData[$renderChartData]['options']['chart']['borderWidth'] : 0);
        $this->setBorderColor(isset($renderData[$renderChartData]['options']['chart']['borderColor']) ? $renderData[$renderChartData]['options']['chart']['borderColor'] : '#4572A7');
        $this->setBorderRadius(isset($renderData[$renderChartData]['options']['chart']['borderRadius']) ? $renderData[$renderChartData]['options']['chart']['borderRadius'] : 0);
        $this->setZoomType(isset($renderData[$renderChartData]['options']['chart']['zoomType']) ? $renderData[$renderChartData]['options']['chart']['zoomType'] : 'undefined');
        $this->setPanning(isset($renderData[$renderChartData]['options']['chart']['panning']) ? $renderData[$renderChartData]['options']['chart']['panning'] : false);
        $this->setPanKey(isset($renderData[$renderChartData]['options']['chart']['panKey']) ? $renderData[$renderChartData]['options']['chart']['panKey'] : 'shift');
        $this->setPlotBackgroundColor(isset($renderData[$renderChartData]['options']['chart']['plotBackgroundColor']) ? $renderData[$renderChartData]['options']['chart']['plotBackgroundColor'] : '');
        $this->setPlotBackgroundImage(isset($renderData[$renderChartData]['options']['chart']['plotBackgroundImage']) ? $renderData[$renderChartData]['options']['chart']['plotBackgroundImage'] : '');
        $this->setPlotBorderWidth(isset($renderData[$renderChartData]['options']['chart']['plotBorderWidth']) ? $renderData[$renderChartData]['options']['chart']['plotBorderWidth'] : 0);
        $this->setPlotBorderColor(isset($renderData[$renderChartData]['options']['chart']['plotBorderColor']) ? $renderData[$renderChartData]['options']['chart']['plotBorderColor'] : '#C0C0C0');
        // Axes
        if ($this->getSeriesType() != '') {
            $this->setHighchartsLineDashStyle(isset($renderData[$renderChartData]['options']['yAxis'][0]['gridLineDashStyle']) ? $renderData[$renderChartData]['options']['yAxis'][0]['gridLineDashStyle'] : 'Solid');
            $this->setVerticalAxisCrosshair(isset($renderData[$renderChartData]['options']['yAxis'][0]['crosshair']) ? $renderData[$renderChartData]['options']['yAxis'][0]['crosshair'] : false);
            $this->setVerticalAxisMin(isset($renderData[$renderChartData]['options']['yAxis'][0]['min']) ? (float)$renderData[$renderChartData]['options']['yAxis'][0]['min'] : '');
            $this->setVerticalAxisMax(isset($renderData[$renderChartData]['options']['yAxis'][0]['max']) ? (float)$renderData[$renderChartData]['options']['yAxis'][0]['max'] : '');
        } else {
            $this->setHighchartsLineDashStyle(isset($renderData[$renderChartData]['options']['yAxis']['gridLineDashStyle']) ? $renderData[$renderChartData]['options']['yAxis']['gridLineDashStyle'] : 'Solid');
            $this->setVerticalAxisCrosshair(isset($renderData[$renderChartData]['options']['yAxis']['crosshair']) ? $renderData[$renderChartData]['options']['yAxis']['crosshair'] : false);
            $this->setVerticalAxisMin(isset($renderData[$renderChartData]['options']['yAxis']['min']) ? (float)$renderData[$renderChartData]['options']['yAxis']['min'] : '');
            $this->setVerticalAxisMax(isset($renderData[$renderChartData]['options']['yAxis']['max']) ? (float)$renderData[$renderChartData]['options']['yAxis']['max'] : '');
        }
        $this->setHorizontalAxisCrosshair(isset($renderData[$renderChartData]['options']['xAxis']['crosshair']) ? $renderData[$renderChartData]['options']['xAxis']['crosshair'] : false);
        $this->setInverted(isset($renderData[$renderChartData]['options']['chart']['inverted']) ? $renderData[$renderChartData]['options']['chart']['inverted'] : false);
        // Title
        $this->setTitleFloating(isset($renderData[$renderChartData]['options']['title']['floating']) ? $renderData[$renderChartData]['options']['title']['floating'] : false);
        $this->setTitleAlign(isset($renderData[$renderChartData]['options']['title']['align']) ? $renderData[$renderChartData]['options']['title']['align'] : 'center');
        $this->setSubtitle(isset($renderData[$renderChartData]['options']['subtitle']['text']) ? $renderData[$renderChartData]['options']['subtitle']['text'] : '');
        $this->setSubtitleAlign(isset($renderData[$renderChartData]['options']['subtitle']['align']) ? $renderData[$renderChartData]['options']['subtitle']['align'] : 'center');
        // Tooltip
        $this->setTooltipEnabled(isset($renderData[$renderChartData]['options']['tooltip']['enabled']) ? $renderData[$renderChartData]['options']['tooltip']['enabled'] : true);
        $this->setTooltipBackgroundColor(!empty($renderData[$renderChartData]['options']['tooltip']['backgroundColor']) ? $renderData[$renderChartData]['options']['tooltip']['backgroundColor'] : 'rgba(255, 255, 255, 0.85)');
        $this->setTooltipBorderWidth(isset($renderData[$renderChartData]['options']['tooltip']['borderWidth']) ? $renderData[$renderChartData]['options']['tooltip']['borderWidth'] : 1);
        $this->setTooltipBorderColor(isset($renderData[$renderChartData]['options']['tooltip']['borderColor']) ? $renderData[$renderChartData]['options']['tooltip']['borderColor'] : null);
        $this->setTooltipBorderRadius(isset($renderData[$renderChartData]['options']['tooltip']['borderRadius']) ? $renderData[$renderChartData]['options']['tooltip']['borderRadius'] : 3);
        $this->setTooltipShared(isset($renderData[$renderChartData]['options']['tooltip']['shared']) ? $renderData[$renderChartData]['options']['tooltip']['shared'] : false);
        $this->setTooltipValuePrefix(isset($renderData[$renderChartData]['options']['tooltip']['valuePrefix']) ? $renderData[$renderChartData]['options']['tooltip']['valuePrefix'] : '');
        $this->setTooltipValueSuffix(isset($renderData[$renderChartData]['options']['tooltip']['valueSuffix']) ? $renderData[$renderChartData]['options']['tooltip']['valueSuffix'] : '');
        // Legend
        $this->setShowLegend(isset($renderData[$renderChartData]['options']['legend']['enabled']) ? $renderData[$renderChartData]['options']['legend']['enabled'] : true);
        $this->setLegendBackgroundColor(isset($renderData[$renderChartData]['options']['legend']['backgroundColor']) ? $renderData[$renderChartData]['options']['legend']['backgroundColor'] : '#FFFFFF');
        $this->setLegendTitle(isset($renderData[$renderChartData]['options']['legend']['title']['text']) ? $renderData[$renderChartData]['options']['legend']['title']['text'] : '');
        $this->setLegendLayout(isset($renderData[$renderChartData]['options']['legend']['layout']) ? $renderData[$renderChartData]['options']['legend']['layout'] : 'horizontal');
        $this->setLegendAlign(isset($renderData[$renderChartData]['options']['legend']['align']) ? $renderData[$renderChartData]['options']['legend']['align'] : 'right');
        $this->setLegendVerticalAlign(isset($renderData[$renderChartData]['options']['legend']['verticalAlign']) ? $renderData[$renderChartData]['options']['legend']['verticalAlign'] : 'bottom');
        $this->setLegendBorderWidth(isset($renderData[$renderChartData]['options']['legend']['borderWidth']) ? $renderData[$renderChartData]['options']['legend']['borderWidth'] : 0);
        $this->setLegendBorderColor(isset($renderData[$renderChartData]['options']['legend']['borderColor']) ? $renderData[$renderChartData]['options']['legend']['borderColor'] : '#909090');
        $this->setLegendBorderRadius(isset($renderData[$renderChartData]['options']['legend']['borderRadius']) ? $renderData[$renderChartData]['options']['legend']['borderRadius'] : 0);
        // Exporting
        $this->setExporting(isset($renderData[$renderChartData]['options']['exporting']['enabled']) ? $renderData[$renderChartData]['options']['exporting']['enabled'] : true);
        $this->setExportingDataLabels(isset($renderData[$renderChartData]['options']['exporting']['chartOptions']['plotOptions']['series']['dataLabels']['enabled']) ? $renderData[$renderChartData]['options']['exporting']['chartOptions']['plotOptions']['series']['dataLabels']['enabled'] : false);
        $this->setExportingFileName(isset($renderData[$renderChartData]['options']['exporting']['filename']) ? $renderData[$renderChartData]['options']['exporting']['filename'] : 'Chart');
        $this->setExportingWidth(isset($renderData[$renderChartData]['options']['exporting']['width']) ? $renderData[$renderChartData]['options']['exporting']['width'] : 'undefined');
        $this->setExportingButtonAlign(isset($renderData[$renderChartData]['options']['exporting']['buttons']['contextButton']['align']) ? $renderData[$renderChartData]['options']['exporting']['buttons']['contextButton']['align'] : 'right');
        $this->setExportingButtonVerticalAlign(isset($renderData[$renderChartData]['options']['exporting']['buttons']['contextButton']['verticalAlign']) ? $renderData[$renderChartData]['options']['exporting']['buttons']['contextButton']['verticalAlign'] : 'top');
        $this->setExportingButtonColor(isset($renderData[$renderChartData]['options']['exporting']['buttons']['contextButton']['symbolStroke']) ? $renderData[$renderChartData]['options']['exporting']['buttons']['contextButton']['symbolStroke'] : '#666');
        $this->setExportingButtonText(isset($renderData[$renderChartData]['options']['exporting']['buttons']['contextButton']['text']) ? $renderData[$renderChartData]['options']['exporting']['buttons']['contextButton']['text'] : null);
        // Credits
        $this->setCredits(isset($renderData[$renderChartData]['options']['credits']['enabled']) ? $renderData[$renderChartData]['options']['credits']['enabled'] : true);
        $this->setCreditsHref(isset($renderData[$renderChartData]['options']['credits']['href']) ? $renderData[$renderChartData]['options']['credits']['href'] : 'https://www.highcharts.com');
        $this->setCreditsText(isset($renderData[$renderChartData]['options']['credits']['text']) ? $renderData[$renderChartData]['options']['credits']['text'] : 'Highcharts.com');
    }

    /**
     * @return mixed|null
     */
    public function returnRenderData()
    {
        return $this->_highcharts_render_data;
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
            'highcharts_render_data' => $this->_highcharts_render_data,
            'show_grid' => $this->_show_grid,
            'show_title' => $this->_show_title,
            'series_type' => $this->getSeriesType()
        );
    }

    /**
     * @param $chartData
     *
     * @return mixed|null
     */
    public function setChartRenderData($chartData)
    {
        if (!empty($renderData['highcharts_render_data'])) {
            $this->_highcharts_render_data = $renderData['highcharts_render_data'];
        }
        return parent::setChartRenderData($chartData);
    }
}