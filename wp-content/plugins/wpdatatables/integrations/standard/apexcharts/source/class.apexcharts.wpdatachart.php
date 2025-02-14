<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace WdtApexchartsChart;

use WDTException;
use WDTTools;
use WPDataChart;

class WdtApexchartsChart extends WPDataChart
{
    //Chart
    protected $_zoom_type = 'undefined';
    protected $_enable_animation = false;
    protected $_line_background_image = '';
    protected $_plot_background_image = 'undefined';
    protected $_show_data_labels = false;
    protected $_start_angle = 0;
    protected $_end_angle = 360;
    protected $_monochrome = false;
    protected $_monochrome_color = '#255aee';
    protected $_enable_color_palette = false;
    protected $_color_palette = 'palette1';
    protected $_enable_dropshadow = false;
    protected $_dropshadow_blur = 3;
    protected $_dropshadow_opacity = 35;
    protected $_dropshadow_color = '#000000';
    protected $_dropshadow_top = 5;
    protected $_dropshadow_left = 5;
    protected $_text_color = '#373d3f';

    //Axes
    protected $_grid_color = '#000000';
    protected $_grid_stroke = 1;
    protected $_grid_position = 'back';
    protected $_grid_axes = array();
    protected $_horizontal_axis_crosshair = false;
    protected $_vertical_axis_crosshair = false;
    protected $_marker_size = 0;
    protected $_stroke_width = 2;
    protected $_tick_amount = 0;
    protected $_reversed = false;

    //Title
    protected $_title_align = 'center';
    protected $_title_floating = false;
    protected $_subtitle = 'undefined';
    protected $_font_style = 'normal';

    // Tooltip
    protected $_tooltip_enabled = true;
    protected $_follow_cursor = false;
    protected $_fill_series_color = false;
    protected $_subtitle_align = 'center';

    // Legend
    protected $_show_legend = true;
    protected $_legend_position = 'top';

    //Toolbar
    protected $_show_toolbar = false;
    protected $_toolbar_buttons = array();
    protected $_exporting_file_name = 'Chart';

    //Render data
    protected $_apexcharts_render_data = NULL;

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
    public function isEnableAnimation()
    {
        return $this->_enable_animation;
    }

    /**
     * @param bool $enable_animation
     */
    public function setEnableAnimation($enable_animation)
    {
        $this->_enable_animation = (bool)$enable_animation;
    }

    /**
     * @return string
     */
    public function getLineBackgroundImage()
    {
        return $this->_line_background_image;
    }

    /**
     * @param string $line_background_image
     */
    public function setLineBackgroundImage($line_background_image)
    {
        $this->_line_background_image = $line_background_image;
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
     * @return bool
     */
    public function isShowDataLabels()
    {
        return $this->_show_data_labels;
    }

    /**
     * @param bool $show_data_labels
     */
    public function setShowDataLabels($show_data_labels)
    {
        $this->_show_data_labels = (bool)$show_data_labels;
    }

    /**
     * @return int
     */
    public function getStartAngle()
    {
        return $this->_start_angle;
    }

    /**
     * @param int $start_angle
     */
    public function setStartAngle($start_angle)
    {
        $this->_start_angle = $start_angle;
    }

    /**
     * @return int
     */
    public function getEndAngle()
    {
        return $this->_end_angle;
    }

    /**
     * @param int $end_angle
     */
    public function setEndAngle($end_angle)
    {
        $this->_end_angle = $end_angle;
    }

    /**
     * @return bool
     */
    public function isMonochrome()
    {
        return $this->_monochrome;
    }

    /**
     * @param bool $monochrome
     */
    public function setMonochrome($monochrome)
    {
        $this->_monochrome = (bool)$monochrome;
    }

    /**
     * @return string
     */
    public function getMonochromeColor()
    {
        return $this->_monochrome_color;
    }

    /**
     * @param string $monochrome_color
     */
    public function setMonochromeColor($monochrome_color)
    {
        $this->_monochrome_color = $monochrome_color;
    }

    /**
     * @return bool
     */
    public function isEnableColorPalette()
    {
        return $this->_enable_color_palette;
    }

    /**
     * @param bool $enable_color_palette
     */
    public function setEnableColorPalette($enable_color_palette)
    {
        $this->_enable_color_palette = (bool)$enable_color_palette;
    }

    /**
     * @return string
     */
    public function getColorPalette()
    {
        return $this->_color_palette;
    }

    /**
     * @param string $color_palette
     */
    public function setColorPalette($color_palette)
    {
        $this->_color_palette = $color_palette;
    }

    /**
     * @return bool
     */
    public function isEnableDropshadow()
    {
        return $this->_enable_dropshadow;
    }

    /**
     * @param bool $enable_dropshadow
     */
    public function setEnableDropshadow($enable_dropshadow)
    {
        $this->_enable_dropshadow = (bool)$enable_dropshadow;
    }

    /**
     * @return int
     */
    public function getDropshadowBlur()
    {
        return $this->_dropshadow_blur;
    }

    /**
     * @param int $dropshadow_blur
     */
    public function setDropshadowBlur($dropshadow_blur)
    {
        $this->_dropshadow_blur = $dropshadow_blur;
    }

    /**
     * @return int
     */
    public function getDropshadowOpacity()
    {
        return $this->_dropshadow_opacity;
    }

    /**
     * @param int $dropshadow_opacity
     */
    public function setDropshadowOpacity($dropshadow_opacity)
    {
        $this->_dropshadow_opacity = $dropshadow_opacity;
    }

    /**
     * @return string
     */
    public function getDropshadowColor()
    {
        return $this->_dropshadow_color;
    }

    /**
     * @param string $dropshadow_color
     */
    public function setDropshadowColor($dropshadow_color)
    {
        $this->_dropshadow_color = $dropshadow_color;
    }

    /**
     * @return int
     */
    public function getDropshadowTop()
    {
        return $this->_dropshadow_top;
    }

    /**
     * @param int $dropshadow_top
     */
    public function setDropshadowTop($dropshadow_top)
    {
        $this->_dropshadow_top = $dropshadow_top;
    }

    /**
     * @return int
     */
    public function getDropshadowLeft()
    {
        return $this->_dropshadow_left;
    }

    /**
     * @param int $dropshadow_left
     */
    public function setDropshadowLeft($dropshadow_left)
    {
        $this->_dropshadow_left = $dropshadow_left;
    }

    /**
     * @return string
     */
    public function getTextColor()
    {
        return $this->_text_color;
    }

    /**
     * @param string $text_color
     */
    public function setTextColor($text_color)
    {
        $this->_text_color = $text_color;
    }


    /**
     * @return string
     */
    public function getGridColor()
    {
        return $this->_grid_color;
    }

    /**
     * @param string $grid_color
     */
    public function setGridColor($grid_color)
    {
        $this->_grid_color = $grid_color;
    }

    /**
     * @return int
     */
    public function getGridStroke()
    {
        return $this->_grid_stroke;
    }

    /**
     * @param int $grid_stroke
     */
    public function setGridStroke($grid_stroke)
    {
        $this->_grid_stroke = $grid_stroke;
    }

    /**
     * @return string
     */
    public function getGridPosition()
    {
        return $this->_grid_position;
    }

    /**
     * @param string $grid_position
     */
    public function setGridPosition($grid_position)
    {
        $this->_grid_position = $grid_position;
    }

    /**
     * @return array
     */
    public function getGridAxes()
    {
        return $this->_grid_axes;
    }

    /**
     * @param array $grid_axes
     */
    public function setGridAxes($grid_axes)
    {
        $this->_grid_axes = $grid_axes;
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
     * @return int
     */
    public function getMarkerSize()
    {
        return $this->_marker_size;
    }

    /**
     * @param int $marker_size
     */
    public function setMarkerSize($marker_size)
    {
        $this->_marker_size = $marker_size;
    }

    /**
     * @return int
     */
    public function getStrokeWidth()
    {
        return $this->_stroke_width;
    }

    /**
     * @param int $stroke_width
     */
    public function setStrokeWidth($stroke_width)
    {
        $this->_stroke_width = $stroke_width;
    }

    /**
     * @return int
     */
    public function getTickAmount()
    {
        return $this->_tick_amount;
    }

    /**
     * @param int $tick_amount
     */
    public function setTickAmount($tick_amount)
    {
        $this->_tick_amount = $tick_amount;
    }

    /**
     * @return bool
     */
    public function isReversed()
    {
        return $this->_reversed;
    }

    /**
     * @param bool $reversed
     */
    public function setReversed($reversed)
    {
        $this->_reversed = (bool)$reversed;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->_title = $title;
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
    public function getFontStyle()
    {
        return $this->_font_style;
    }

    /**
     * @param string $font_style
     */
    public function setFontStyle($font_style)
    {
        $this->_font_style = $font_style;
    }

    /**
     * @return bool
     */
    public function isFollowCursor()
    {
        return $this->_follow_cursor;
    }

    /**
     * @param bool $follow_cursor
     */
    public function setFollowCursor($follow_cursor)
    {
        $this->_follow_cursor = (bool)$follow_cursor;
    }

    /**
     * @return bool
     */
    public function isFillSeriesColor()
    {
        return $this->_fill_series_color;
    }

    /**
     * @param bool $fill_series_color
     */
    public function setFillSeriesColor($fill_series_color)
    {
        $this->_fill_series_color = (bool)$fill_series_color;
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
    public function getLegendPosition()
    {
        return $this->_legend_position;
    }

    /**
     * @param string $legend_position
     */
    public function setLegendPosition($legend_position)
    {
        $this->_legend_position = $legend_position;
    }

    /**
     * @return bool
     */
    public function isShowToolbar()
    {
        return $this->_show_toolbar;
    }

    /**
     * @param bool $show_toolbar
     */
    public function setShowToolbar($show_toolbar)
    {
        $this->_show_toolbar = (bool)$show_toolbar;
    }

    /**
     * @return array
     */
    public function getToolbarButtons()
    {
        return $this->_toolbar_buttons;
    }

    /**
     * @param array $toolbar_buttons
     */
    public function setToolbarButtons($toolbar_buttons)
    {
        $this->_toolbar_buttons = $toolbar_buttons;
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
     * WPDT ApexChart constructor.
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

            $this->setEngine('apexcharts');

            // Chart
            $this->setEnableAnimation(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'enable_animation', false)));
            $this->setStartAngle((int)WDTTools::defineDefaultValue($constructedChartData, 'start_angle', 0));
            $this->setEndAngle((int)WDTTools::defineDefaultValue($constructedChartData, 'end_angle', 360));
            $this->setBackgroundColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'background_color', '#FFFFFF')));
            $this->setZoomType(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'zoom_type', 'undefined')));
            $this->setPlotBackgroundImage(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'plot_background_image')));
            $this->setLineBackgroundImage(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'line_background_image')));
            $this->setMonochrome(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'monochrome', false)));
            $this->setMonochromeColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'monochrome_color', '#255aee')));
            $this->setEnableColorPalette(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'enable_color_palette', false)));
            $this->setColorPalette(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'color_palette', 'palette1')));
            $this->setShowDataLabels(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'show_data_labels', false)));
            $this->setEnableDropshadow(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'enable_dropshadow', false)));
            $this->setDropshadowBlur((int)WDTTools::defineDefaultValue($constructedChartData, 'dropshadow_blur', 3));
            $this->setDropshadowOpacity((float)WDTTools::defineDefaultValue($constructedChartData, 'dropshadow_opacity', 35));
            $this->setDropshadowColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'dropshadow_color', '#000000')));
            $this->setDropshadowTop((int)WDTTools::defineDefaultValue($constructedChartData, 'dropshadow_top', 5));
            $this->setDropshadowLeft((int)WDTTools::defineDefaultValue($constructedChartData, 'dropshadow_left', 5));
            $this->setTextColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'text_color', '#373d3f')));

            // Axes
            $this->setGridColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'grid_color', '#000000')));
            $this->setGridStroke(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'grid_stroke', 1)));
            $this->setGridPosition(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'grid_position', 'back')));
            $this->setGridAxes((array)WDTTools::defineDefaultValue($constructedChartData, 'grid_axes', ['yaxis']));
            $this->setMajorAxisLabel(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'horizontal_axis_label')));
            $this->setHorizontalAxisCrosshair(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'horizontal_axis_crosshair', false)));
            $this->setMinorAxisLabel(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'vertical_axis_label')));
            $this->setVerticalAxisCrosshair(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'vertical_axis_crosshair', false)));
            $this->setMarkerSize((int)(WDTTools::defineDefaultValue($constructedChartData, 'marker_size', 0)));
            $this->setStrokeWidth(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'stroke_width', 2)));
            $this->setVerticalAxisMin(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'vertical_axis_min')));
            $this->setVerticalAxisMax(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'vertical_axis_max')));
            $this->setTickAmount(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tick_amount', 0)));
            $this->setReversed(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'reversed', false)));

            // Title
            $this->setShowTitle(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'show_title', true)));
            $this->setTitleFloating(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'title_floating', false)));
            $this->setTitleAlign(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'title_align', 'center')));
            $this->setSubtitle(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'subtitle', false)));
            $this->setSubtitleAlign(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'subtitle_align', 'center')));

            // Tooltip
            $this->setTooltipEnabled(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_enabled', true)));
            $this->setFollowCursor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'follow_cursor', false)));
            $this->setFillSeriesColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'fill_series_color', false)));

            // Legend
            $this->setShowLegend(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'show_legend', true)));
            $this->setLegendPosition(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_position_cjs', 'top')));

            // Exporting
            $this->setExportingFileName(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'exporting_file_name', 'Chart')));

            //Toolbar
            $this->setShowToolbar(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'show_toolbar', false)));
            if (isset($constructedChartData['toolbar_buttons'])) {
                $this->setToolbarButtons(array_map('sanitize_text_field', $constructedChartData['toolbar_buttons']));
            }
            $this->setExportingFileName(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'apex_exporting_file_name', 'Chart')));
        }
    }

    /**
     * @return void
     */
    public function defineSeries()
    {
        if (in_array($this->_type, [
                'apexcharts_straight_line_chart',
                'apexcharts_spline_chart',
                'apexcharts_stepline_chart',
                'apexcharts_spline_area_chart',
                'apexcharts_stepline_area_chart',
                'apexcharts_basic_area_chart',
                'apexcharts_column_chart'])
            && !empty($this->_user_defined_series_data)) {
            $seriesIndex = 0;
            $i = 1;
            foreach ($this->_user_defined_series_data as $series_data) {
                $this->_render_data['options']['series'][$seriesIndex] = array(
                    'label' => $series_data['color'],
                    'color' => $series_data['color'],
                    'type' => $series_data['type'],
                    'yAxis' => $series_data['yAxis'] == 1 ? $i : 0,
                    'chart_image' => $series_data['chart_image'],
                );
                if ($series_data['yAxis']) {
                    $i++;
                }
                $seriesIndex++;
            }
        } else if (in_array($this->_type, [
            'apexcharts_radar_chart',
            'apexcharts_grouped_bar_chart',
            'apexcharts_stacked_bar_chart',
            'apexcharts_100_stacked_bar_chart',
            'apexcharts_stacked_column_chart',
            'apexcharts_100_stacked_column_chart'])) {
            $seriesIndex = 0;
            foreach ($this->_user_defined_series_data as $series_data) {
                $this->_render_data['options']['series'][$seriesIndex] = array(
                    'color' => $series_data['color'],
                    'chart_image' => $series_data['chart_image'],
                );
                $seriesIndex++;
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
        $apexchartsRender = array(
            'series' => array(),
            'labels' => array(),
            'title' => array(
                'text' => $this->_show_title ? $this->getTitle() : '',
                'align' => 'center',
            ),
            'xaxis' => array(
                'type' => 'category',
            ),
            'colors' => array(),
            'fill' => array(
                'image' => array(
                    'src' => array(),
                ),
            ),
            'orig_header' => array(),
        );

        $this->_apexcharts_render_data['wdtNumberFormat'] = get_option('wdtNumberFormat');
        $this->_apexcharts_render_data['wdtDecimalPlaces'] = get_option('wdtDecimalPlaces');
        $this->_apexcharts_render_data['type'] = $this->getType();
        if ($this->_follow_filtering) {
            if (isset($this->_render_data['column_indexes'])) {
                $this->_apexcharts_render_data['column_indexes'] = $this->_render_data['column_indexes'];
            }
        }

        //default Apexcharts color palette
        $colors = ['#775DD0', '#008FFB', '#00E396', '#FEB019', '#FF4560', '#B6D05D', '#FB6C00', '#E3004D', '#45FFE4'];
        $colorsNum = count($colors);
        //Data
        if (!in_array(
            $this->_type,
            array(
                'apexcharts_pie_chart',
                'apexcharts_pie_with_gradient_chart',
                'apexcharts_donut_chart',
                'apexcharts_donut_with_gradient_chart',
                'apexcharts_radialbar_chart',
                'apexcharts_radialbar_gauge_chart',
                'apexcharts_radar_chart',
            )
        )
        ) {
            $apexchartsRender['fill']['type'] = array();
            for ($i = 0; $i < count($this->_render_data['columns']); $i++) {
                if ($i == 0) {
                    $apexchartsRender['xaxis']['categories'] = array();
                    foreach ($this->_render_data['rows'] as $row) {
                        $apexchartsRender['xaxis']['categories'][] = $row[0];
                    }
                } else {
                    $seriesColor = isset($this->_render_data['options']['series'][$i - 1]['color']) ? $this->_render_data['options']['series'][$i - 1]['color'] : $colors[$i % $colorsNum];
                    $seriesImage = (isset($this->_render_data['options']['series'][$i - 1]['chart_image']) && $this->_render_data['options']['series'][$i - 1]['chart_image'] !== '') ? $this->_render_data['options']['series'][$i - 1]['chart_image'] : '';
                    $fillType = $seriesImage == '' ? 'solid' : 'image';
                    $seriesEntry = array(
                        'orig_header' => $this->_render_data['series'][$i - 1]['orig_header'],
                        'name' => $this->_render_data['series'][$i - 1]['label'],
                        'data' => array(),
                        'color' => $seriesColor,
                        'chart_image' => $seriesImage,
                        'label' => $this->_render_data['series'][$i - 1]['label'],
                    );

                    if (!in_array(
                        $this->_type,
                        array('apexcharts_grouped_bar_chart',
                            'apexcharts_stacked_bar_chart',
                            'apexcharts_100_stacked_bar_chart'))
                    )
                        $seriesEntry['type'] = isset($this->_render_data['options']['series'][$i - 1]['type']) ? $this->_render_data['options']['series'][$i - 1]['type'] : $this->getApexSeriesType($this->getType());

                    if (isset($this->_render_data['series'][$i - 1]['opposite'])) $seriesEntry['opposite'] = $this->_render_data['series'][$i - 1]['opposite'];

                    foreach ($this->_render_data['rows'] as $row) {
                        $seriesEntry['data'][] = $row[$i];
                    }
                    $apexchartsRender['fill']['type'][] = $fillType;
                    $apexchartsRender['fill']['image']['src'][] = $seriesImage;
                    $apexchartsRender['colors'][] = $seriesColor;
                    $apexchartsRender['series'][] = $seriesEntry;
                }
            }
        } else if ($this->_type == 'apexcharts_radar_chart') {
            $apexchartsRender['options']['grid']['show'] = false;
            $apexchartsRender['fill']['type'] = array();
            for ($i = 0; $i < count($this->_render_data['columns']); $i++) {
                if ($i == 0) {
                    $apexchartsRender['xaxis']['categories'] = array();
                    foreach ($this->_render_data['rows'] as $row) {
                        $apexchartsRender['xaxis']['categories'][] = $row[0];
                    }
                } else {
                    $seriesColor = isset($this->_render_data['options']['series'][$i - 1]['color']) ? $this->_render_data['options']['series'][$i - 1]['color'] : $colors[$i % $colorsNum];
                    $seriesImage = (isset($this->_render_data['options']['series'][$i - 1]['chart_image']) && $this->_render_data['options']['series'][$i - 1]['chart_image'] !== '') ? $this->_render_data['options']['series'][$i - 1]['chart_image'] : '';
                    $fillType = $seriesImage == '' ? 'solid' : 'image';
                    $seriesEntry = array(
                        'orig_header' => $this->_render_data['series'][$i - 1]['orig_header'],
                        'name' => $this->_render_data['series'][$i - 1]['label'],
                        'data' => array(),
                        'color' => $seriesColor,
                        'chart_image' => $seriesImage,
                        'label' => $this->_render_data['series'][$i - 1]['label'],
                    );

                    if (isset($this->_render_data['options']['series'][$i - 1]['type'])) $seriesEntry['type'] = $this->_render_data['options']['series'][$i - 1]['type'];

                    foreach ($this->_render_data['rows'] as $row) {
                        $seriesEntry['data'][] = $row[$i];
                    }
                    $apexchartsRender['fill']['type'][] = $fillType;
                    $apexchartsRender['fill']['image']['src'][] = $seriesImage;
                    $apexchartsRender['colors'][] = $seriesColor;
                    $apexchartsRender['series'][] = $seriesEntry;
                }
            }
        } else {
            $isRadialType = $this->_type == 'apexcharts_radialbar_chart' || $this->_type == 'apexcharts_radialbar_gauge_chart';
            unset($apexchartsRender['xaxis']);
            for ($i = 0; $i < count($this->_render_data['columns']); $i++) {
                $seriesEntry = array(
                    'orig_header' => $this->_render_data['columns'][$i]['orig_header'],
                    'data' => array(),
                );
                $j = 0;
                foreach ($this->_render_data['rows'] as $row) {
                    if ($i == 0) {
                        $apexchartsRender['labels'][] = $row[$i];
                    } else {
                        $seriesEntry['data'][] = $row[$i];
                    }
                    if ($isRadialType) {
                        $apexchartsRender['colors'][$j] = $colors[($j + 1) % $colorsNum];
                        $j++;
                    }
                }

                if ($i != 0) {
                    $apexchartsRender['series'] = $seriesEntry['data'];
                    $apexchartsRender['orig_header'][] = $seriesEntry['orig_header'];
                    $seriesColor = $this->_render_data['series'][$i - 1]['color'] != '' ? $this->_render_data['series'][$i - 1]['color'] : $colors[$i % $colorsNum];
                    if (!$isRadialType) {
                        $apexchartsRender['colors'][] = $seriesColor;
                    }
                }
            }
        }

        $this->_apexcharts_render_data['options'] = $apexchartsRender;

        // Chart
        if (!$this->_responsiveWidth) {
            $this->_apexcharts_render_data['options']['chart']['width'] = $this->getWidth();
        }
        $this->_apexcharts_render_data['options']['chart']['height'] = $this->getHeight();
        $this->_apexcharts_render_data['options']['chart']['animations']['enabled'] = $this->isEnableAnimation();
        $this->_apexcharts_render_data['options']['chart']['background'] = ($this->getPlotBackgroundImage() != 'undefined' && $this->getPlotBackgroundImage() != '') ? $this->getPlotBackgroundImage() : $this->getBackgroundColor();
        if (in_array($this->_type, array('apexcharts_pie_chart',
            'apexcharts_pie_with_gradient_chart',
            'apexcharts_donut_chart',
            'apexcharts_donut_with_gradient_chart'))) {
            $this->_apexcharts_render_data['options']['theme']['monochrome'] = array();
            $this->_apexcharts_render_data['options']['theme']['monochrome']['enabled'] = $this->isMonochrome();
            $this->_apexcharts_render_data['options']['theme']['monochrome']['color'] = $this->getMonochromeColor();
            if ($this->_enable_color_palette) {
                $this->_apexcharts_render_data['options']['theme']['monochrome']['enabled'] = false;
                $this->_apexcharts_render_data['options']['theme']['palette'] = $this->getColorPalette();
            }
        }
        $this->_apexcharts_render_data['options']['plotOptions']['bar'] = array();
        $this->_apexcharts_render_data['options']['plotOptions']['bar']['horizontal'] = false;
        $this->_apexcharts_render_data['options']['plotOptions']['radialBar'] = array();
        $this->_apexcharts_render_data['options']['plotOptions']['radialBar']['startAngle'] = $this->getStartAngle();
        $this->_apexcharts_render_data['options']['plotOptions']['radialBar']['endAngle'] = $this->getEndAngle();
        $this->_apexcharts_render_data['options']['chart']['foreColor'] = $this->getTextColor();
        $this->_apexcharts_render_data['options']['chart']['zoom']['type'] = $this->getZoomType();
        if ($this->getLineBackgroundImage() != '') {
            $this->_apexcharts_render_data['options']['fill']['image']['src'] = $this->getLineBackgroundImage();
            $this->_apexcharts_render_data['options']['fill']['type'] = 'image';
        }
        if ($this->isShowDataLabels()) {
            $this->_apexcharts_render_data['options']['dataLabels']['enabled'] = $this->_apexcharts_render_data['options']['plotOptions']['radialBar']['dataLabels']['show'] = true;
            $this->_apexcharts_render_data['options']['plotOptions']['pie'] = array();
            $this->_apexcharts_render_data['options']['plotOptions']['pie']['dataLabels'] = array();
            $this->_apexcharts_render_data['options']['plotOptions']['pie']['dataLabels']['minAngleToShowLabel'] = 0;
        } else {
            $this->_apexcharts_render_data['options']['dataLabels']['enabled'] = $this->_apexcharts_render_data['options']['plotOptions']['radialBar']['dataLabels']['show'] = false;
        }
        $this->_apexcharts_render_data['options']['chart']['dropShadow']['enabled'] = $this->isEnableDropshadow();
        $this->_apexcharts_render_data['options']['chart']['dropShadow']['blur'] = $this->getDropshadowBlur();
        $this->_apexcharts_render_data['options']['chart']['dropShadow']['opacity'] = $this->getDropshadowOpacity();
        $this->_apexcharts_render_data['options']['chart']['dropShadow']['color'] = $this->getDropshadowColor();
        $this->_apexcharts_render_data['options']['chart']['dropShadow']['top'] = $this->getDropshadowTop();
        $this->_apexcharts_render_data['options']['chart']['dropShadow']['left'] = $this->getDropshadowLeft();

        // Axes
        $this->_apexcharts_render_data['options']['grid']['show'] = !!$this->isShowGrid();
        $this->_apexcharts_render_data['options']['grid']['borderColor'] = $this->getGridColor() == '' ? '#000000' : $this->getGridColor();
        $this->_apexcharts_render_data['options']['grid']['strokeDashArray'] = $this->getGridStroke();
        $this->_apexcharts_render_data['options']['grid']['position'] = $this->getGridPosition();
        $this->_apexcharts_render_data['options']['grid']['xaxis'] = $this->_apexcharts_render_data['options']['grid']['yaxis'] = array();
        $gridAxis = $this->getGridAxes();
        $this->_apexcharts_render_data['options']['grid']['xaxis']['lines']['show'] = in_array('xaxis', $gridAxis);
        $this->_apexcharts_render_data['options']['grid']['yaxis']['lines']['show'] = in_array('yaxis', $gridAxis);
        $this->_apexcharts_render_data['options']['xaxis']['crosshairs'] = array();
        $this->_apexcharts_render_data['options']['xaxis']['title'] = array();
        $this->_apexcharts_render_data['options']['xaxis']['crosshairs']['show'] = $this->isHorizontalAxisCrosshair();
        $this->_apexcharts_render_data['options']['xaxis']['tooltip']['enabled'] = $this->isHorizontalAxisCrosshair();
        $this->_apexcharts_render_data['options']['xaxis']['title']['text'] = $this->getMajorAxisLabel();
        $i = 0;
        if (!in_array($this->_type,
            array(
                'apexcharts_grouped_bar_chart',
                'apexcharts_100_stacked_bar_chart',
                'apexcharts_stacked_bar_chart',
                'apexcharts_100_stacked_column_chart',
                'apexcharts_stacked_column_chart',
                'apexcharts_radar_chart',
                'apexcharts_pie_chart',
                'apexcharts_pie_with_gradient_chart',
                'apexcharts_donut_chart',
                'apexcharts_donut_with_gradient_chart',
                'apexcharts_radialbar_chart',
                'apexcharts_radialbar_gauge_chart'))) {
            foreach ($apexchartsRender['series'] as $series) {
                $this->_apexcharts_render_data['options']['yaxis'][$i]['seriesName'] = $apexchartsRender['series'][0]['name'];
                $this->_apexcharts_render_data['options']['yaxis'][$i]['title']['text'] = $series['label'];
                $this->_apexcharts_render_data['options']['yaxis'][0]['title']['text'] = $this->getMinorAxisLabel() ? $this->getMinorAxisLabel() : "";
                if ($i != 0) $this->_apexcharts_render_data['options']['yaxis'][$i]['show'] = false;
                $this->_apexcharts_render_data['options']['yaxis'][$i]['crosshairs']['show'] = $this->_apexcharts_render_data['options']['yaxis'][$i]['tooltip']['enabled'] = $this->isVerticalAxisCrosshair();
                $this->_apexcharts_render_data['options']['yaxis'][$i]['reversed'] = $this->isReversed();
                if ($this->getVerticalAxisMin() !== '') {
                    $this->_apexcharts_render_data['options']['yaxis'][$i]['min'] = (int)$this->getVerticalAxisMin();
                }
                if ($this->getVerticalAxisMax() !== '') {
                    $this->_apexcharts_render_data['options']['yaxis'][$i]['max'] = (int)$this->getVerticalAxisMax();
                }
                if ($i != 0 && isset($this->_render_data['options']['series'][$i]['yAxis'])) {
                    $showOppositeAxis = (bool)$this->_render_data['options']['series'][$i]['yAxis'];
                    $this->_apexcharts_render_data['options']['yaxis'][$i]['opposite'] = $showOppositeAxis;
                    $this->_apexcharts_render_data['options']['yaxis'][$i]['show'] = $showOppositeAxis;
                    if ($showOppositeAxis && isset($this->_apexcharts_render_data['options']['yaxis'][$i]['orig_header'])) {
                        $this->_apexcharts_render_data['options']['yaxis'][$i]['seriesName'] = $this->_apexcharts_render_data['options']['yaxis'][$i]['orig_header'];
                    } else if (isset($this->_apexcharts_render_data['options']['yaxis'][0]['seriesName'])) {
                        $this->_apexcharts_render_data['options']['yaxis'][$i]['seriesName'] = $this->_apexcharts_render_data['options']['yaxis'][0]['seriesName'];
                    }
                }
                $this->_apexcharts_render_data['options']['yaxis'][$i]['tickAmount'] = $this->getTickAmount() ?: 0;
                $i++;
            }
        } else if (in_array($this->_type, array('apexcharts_grouped_bar_chart',
            'apexcharts_100_stacked_bar_chart',
            'apexcharts_stacked_bar_chart',
            'apexcharts_100_stacked_column_chart',
            'apexcharts_stacked_column_chart'))) {
            $this->_apexcharts_render_data['options']['yaxis']['seriesName'] = $apexchartsRender['series'][0]['name'];
            $this->_apexcharts_render_data['options']['yaxis']['title']['text'] = $this->getMinorAxisLabel() ?: $this->_render_data["series"][0]['label'];
            if ($this->getVerticalAxisMin() !== '') {
                $this->_apexcharts_render_data['options']['yaxis']['min'] = (int)$this->getVerticalAxisMin();
            }
            if ($this->getVerticalAxisMax() !== '') {
                $this->_apexcharts_render_data['options']['yaxis']['max'] = (int)$this->getVerticalAxisMax();
            }
            $this->_apexcharts_render_data['options']['yaxis']['reversed'] = $this->isReversed();
            if ($this->_type != 'apexcharts_100_stacked_column_chart')
                $this->_apexcharts_render_data['options']['yaxis']['tickAmount'] = $this->getTickAmount() ?: 0;
        }
        $this->_apexcharts_render_data['options']['markers']['size'] = $this->getMarkerSize();
        $this->_apexcharts_render_data['options']['markers']['hover']['sizeOffset'] = 1;
        $this->_apexcharts_render_data['options']['stroke']['width'] = $this->getStrokeWidth();
        // Title
        if ($this->isShowTitle()) {
            $this->_apexcharts_render_data['options']['title']['text'] = $this->getTitle();
        } else {
            $this->_apexcharts_render_data['options']['title']['text'] = '';
        }
        $this->_apexcharts_render_data['options']['title']['floating'] = $this->isTitleFloating();
        $this->_apexcharts_render_data['options']['title']['align'] = $this->getTitleAlign();
        $this->_apexcharts_render_data['options']['subtitle']['text'] = $this->getSubtitle();
        $this->_apexcharts_render_data['options']['subtitle']['align'] = $this->getSubtitleAlign();

        // Tooltip
        $this->_apexcharts_render_data['options']['tooltip']['enabled'] = $this->isTooltipEnabled();
        $this->_apexcharts_render_data['options']['tooltip']['shared'] = $this->isTooltipEnabled() && $this->getType() !== 'apexcharts_radar_chart';
        $this->_apexcharts_render_data['options']['tooltip']['followCursor'] = $this->isFollowCursor();
        $this->_apexcharts_render_data['options']['tooltip']['fillSeriesColor'] = $this->isFillSeriesColor();
        $this->_apexcharts_render_data['options']['tooltip']['intersect'] = $this->getType() == 'apexcharts_radar_chart';

        //Toolbar
        $this->_apexcharts_render_data['options']['chart']['toolbar']['show'] = $this->isShowToolbar();
        $this->_apexcharts_render_data['options']['chart']['toolbar']['tools'] = array();
        $this->_apexcharts_render_data['options']['chart']['selection'] = array();
        $this->_apexcharts_render_data['options']['chart']['selection']['enabled'] = true;
        $allToolbarButtons = ['download', 'selection', 'zoom', 'zoomin', 'zoomout', 'pan'];
        $notShownToolbarButtons = array_diff(array_values($allToolbarButtons), $this->getToolbarButtons());
        foreach ($allToolbarButtons as $button) {
            $this->_apexcharts_render_data['options']['chart']['toolbar']['tools'][$button] = !in_array($button, $notShownToolbarButtons);
        }
        $this->_apexcharts_render_data['options']['chart']['toolbar']['tools']['customIcons'] = array();
        $this->_apexcharts_render_data['options']['chart']['toolbar']['export']['csv']['filename'] =
        $this->_apexcharts_render_data['options']['chart']['toolbar']['export']['svg']['filename'] =
        $this->_apexcharts_render_data['options']['chart']['toolbar']['export']['png']['filename'] = $this->getExportingFileName();

        // Legend
        $this->_apexcharts_render_data['options']['legend']['showForSingleSeries'] = true;
        $this->_apexcharts_render_data['options']['legend']['show'] = $this->isShowLegend();
        $this->_apexcharts_render_data['options']['legend']['position'] = $this->getLegendPosition() != '' ? $this->getLegendPosition() : 'bottom';

        $this->_apexcharts_render_data = apply_filters('wpdatatables_filter_apexcharts_render_data', $this->_apexcharts_render_data, $this->getId(), $this);
    }

    /**
     * @param $js_ext
     *
     * @return false|string
     */
    public function enqueueChartSpecificScripts($js_ext)
    {

        $apexChartLibSource = get_option('wdtApexStableVersion') ? WDT_AC_ASSETS_URL . 'js/apexcharts.js' : '//cdn.jsdelivr.net/npm/apexcharts';
        $this->prepareRender();
        wp_enqueue_script('wdt-apexcharts', $apexChartLibSource, array(), WDT_CURRENT_VERSION);
        wp_enqueue_script('wpdatatables-apexcharts', WDT_AC_ASSETS_URL . 'js/wdt.apexcharts' . $js_ext, array('jquery'), WDT_CURRENT_VERSION);
        return json_encode($this->_apexcharts_render_data);
    }

    /**
     * @param $renderData
     *
     * @return void
     */
    public function setSpecificChartProperties($renderData)
    {
        // Chart
        $isBackgroundAColor = WDTTools::isStringAColor($renderData['apexcharts_render_data']['options']['chart']['background']);
        $this->setBackgroundColor((isset($renderData['apexcharts_render_data']['options']['chart']['background']) && $isBackgroundAColor) ? $renderData['apexcharts_render_data']['options']['chart']['background'] : '');
        $this->setEnableAnimation(isset($renderData['apexcharts_render_data']['options']['chart']['animations']['enabled']) ? $renderData['apexcharts_render_data']['options']['chart']['animations']['enabled'] : false);
        $this->setMonochrome(isset($renderData['apexcharts_render_data']['options']['theme']['monochrome']['enabled']) ? $renderData['apexcharts_render_data']['options']['theme']['monochrome']['enabled'] : false);
        $this->setMonochromeColor(isset($renderData['apexcharts_render_data']['options']['theme']['monochrome']['color']) ? $renderData['apexcharts_render_data']['options']['theme']['monochrome']['color'] : '#255aee');
        $this->setEnableColorPalette(isset($renderData['apexcharts_render_data']['options']['theme']['monochrome']['color']) && WDTTools::isStringAColor($renderData['apexcharts_render_data']['options']['theme']['monochrome']['color']) && !$this->isMonochrome());
        $this->setColorPalette(isset($renderData['apexcharts_render_data']['options']['theme']['palette']) ? $renderData['apexcharts_render_data']['options']['theme']['palette'] : 'palette1');
        $this->setStartAngle(isset($renderData['apexcharts_render_data']['options']['plotOptions']['radialBar']['startAngle']) ? $renderData['apexcharts_render_data']['options']['plotOptions']['radialBar']['startAngle'] : 0);
        $this->setEndAngle(isset($renderData['apexcharts_render_data']['options']['plotOptions']['radialBar']['endAngle']) ? $renderData['apexcharts_render_data']['options']['plotOptions']['radialBar']['endAngle'] : 360);
        $this->setTextColor(isset($renderData['apexcharts_render_data']['options']['chart']['foreColor']) ? $renderData['apexcharts_render_data']['options']['chart']['foreColor'] : '#373d3f');
        $this->setZoomType(isset($renderData['apexcharts_render_data']['options']['chart']['zoom']['type']) ? $renderData['apexcharts_render_data']['options']['chart']['zoom']['type'] : 'None');
        $this->setPlotBackgroundImage((isset($renderData['apexcharts_render_data']['options']['chart']['background']) && !$isBackgroundAColor && $renderData['apexcharts_render_data']['options']['chart']['background'] !== '') ? "url(" . $renderData['apexcharts_render_data']['options']['chart']['background'] . ") no-repeat center/cover" : '');
        $this->setLineBackgroundImage((isset($renderData['apexcharts_render_data']['options']['fill']['image']['src']) && !is_array($renderData['apexcharts_render_data']['options']['fill']['image']['src'])) ? $renderData['apexcharts_render_data']['options']['fill']['image']['src'] : '');
        $this->setShowDataLabels(isset($renderData['apexcharts_render_data']['options']['dataLabels']['enabled']) ? $renderData['apexcharts_render_data']['options']['dataLabels']['enabled'] : false);
        $this->setEnableDropshadow(isset($renderData['apexcharts_render_data']['options']['chart']['dropShadow']['enabled']) ? $renderData['apexcharts_render_data']['options']['chart']['dropShadow']['enabled'] : false);
        $this->setDropshadowBlur(isset($renderData['apexcharts_render_data']['options']['chart']['dropShadow']['blur']) ? $renderData['apexcharts_render_data']['options']['chart']['dropShadow']['blur'] : 3);
        $this->setDropshadowOpacity(isset($renderData['apexcharts_render_data']['options']['chart']['dropShadow']['opacity']) ? $renderData['apexcharts_render_data']['options']['chart']['dropShadow']['opacity'] : 35);
        $this->setDropshadowColor(isset($renderData['apexcharts_render_data']['options']['chart']['dropShadow']['color']) ? $renderData['apexcharts_render_data']['options']['chart']['dropShadow']['color'] : '#000');
        $this->setDropshadowTop(isset($renderData['apexcharts_render_data']['options']['chart']['dropShadow']['top']) ? $renderData['apexcharts_render_data']['options']['chart']['dropShadow']['top'] : 5);
        $this->setDropshadowLeft(isset($renderData['apexcharts_render_data']['options']['chart']['dropShadow']['left']) ? $renderData['apexcharts_render_data']['options']['chart']['dropShadow']['left'] : 5);

        // Axes
        $this->setGridColor(isset($renderData['apexcharts_render_data']['options']['grid']['borderColor']) ? $renderData['apexcharts_render_data']['options']['grid']['borderColor'] : '#000000');
        $this->setGridStroke(isset($renderData['apexcharts_render_data']['options']['grid']['strokeDashArray']) ? $renderData['apexcharts_render_data']['options']['grid']['strokeDashArray'] : 1);
        $this->setGridPosition(isset($renderData['apexcharts_render_data']['options']['grid']['position']) ? $renderData['apexcharts_render_data']['options']['grid']['position'] : 'back');
        $gridAxes = array();
        if (isset($renderData['apexcharts_render_data']['options']['grid'])) {
            if ($renderData['apexcharts_render_data']['options']['grid']['xaxis']['lines']['show']) $gridAxes[] = 'xaxis';
            if ($renderData['apexcharts_render_data']['options']['grid']['yaxis']['lines']['show']) $gridAxes[] = 'yaxis';
        }
        $this->setGridAxes($gridAxes);
        $this->setMajorAxisLabel(isset($renderData['apexcharts_render_data']['options']['xaxis']['title']['text']) ? $renderData['apexcharts_render_data']['options']['xaxis']['title']['text'] : '');
        if (in_array($this->_type, array('apexcharts_grouped_bar_chart',
            'apexcharts_100_stacked_bar_chart',
            'apexcharts_stacked_bar_chart',
            'apexcharts_100_stacked_column_chart',
            'apexcharts_stacked_column_chart'))) {
            $this->setMinorAxisLabel(isset($renderData['apexcharts_render_data']['options']['yaxis']['title']['text']) ? $renderData['apexcharts_render_data']['options']['yaxis']['title']['text'] : '');
            $this->setVerticalAxisMin(isset($renderData['apexcharts_render_data']['options']['yaxis']['min']) ? (int)$renderData['apexcharts_render_data']['options']['yaxis']['min'] : '');
            $this->setVerticalAxisMax(isset($renderData['apexcharts_render_data']['options']['yaxis']['max']) ? (int)$renderData['apexcharts_render_data']['options']['yaxis']['max'] : '');
            $this->setTickAmount((isset($renderData['apexcharts_render_data']['options']['yaxis']['tickAmount']) && $renderData['apexcharts_render_data']['options']['yaxis']['tickAmount'] != 0) ? (int)$renderData['apexcharts_render_data']['options']['yaxis']['tickAmount'] : 0);
            $this->setReversed(isset($renderData['apexcharts_render_data']['options']['yaxis']['reversed']) ? $renderData['apexcharts_render_data']['options']['yaxis']['reversed'] : false);
        } else {
            $this->setMinorAxisLabel(isset($renderData['apexcharts_render_data']['options']['yaxis'][0]['title']['text']) ? $renderData['apexcharts_render_data']['options']['yaxis'][0]['title']['text'] : '');
            $this->setVerticalAxisMin(isset($renderData['apexcharts_render_data']['options']['yaxis'][0]['min']) ? (int)$renderData['apexcharts_render_data']['options']['yaxis'][0]['min'] : '');
            $this->setVerticalAxisMax(isset($renderData['apexcharts_render_data']['options']['yaxis'][0]['max']) ? (int)$renderData['apexcharts_render_data']['options']['yaxis'][0]['max'] : '');
            $this->setTickAmount((isset($renderData['apexcharts_render_data']['options']['yaxis'][0]['tickAmount']) && $renderData['apexcharts_render_data']['options']['yaxis'][0]['tickAmount'] != 0) ? (int)$renderData['apexcharts_render_data']['options']['yaxis'][0]['tickAmount'] : 0);
            $this->setReversed(isset($renderData['apexcharts_render_data']['options']['yaxis'][0]['reversed']) ? $renderData['apexcharts_render_data']['options']['yaxis'][0]['reversed'] : false);

        }
        $this->setMarkerSize(isset($renderData['apexcharts_render_data']['options']['markers']['size']) ? $renderData['apexcharts_render_data']['options']['markers']['size'] : 0);
        $this->setStrokeWidth(isset($renderData['apexcharts_render_data']['options']['stroke']['width']) ? $renderData['apexcharts_render_data']['options']['stroke']['width'] : 2);
        $this->setVerticalAxisCrosshair(isset($renderData['apexcharts_render_data']['options']['yaxis'][0]['crosshairs']['show']) ? $renderData['apexcharts_render_data']['options']['yaxis'][0]['crosshairs']['show'] : false);
        $this->setHorizontalAxisCrosshair(isset($renderData['apexcharts_render_data']['options']['xaxis']['crosshairs']['show']) ? $renderData['apexcharts_render_data']['options']['xaxis']['crosshairs']['show'] : false);

        // Title
        $this->setTitleFloating(isset($renderData['apexcharts_render_data']['options']['title']['floating']) ? $renderData['apexcharts_render_data']['options']['title']['floating'] : false);
        $this->setTitleAlign(isset($renderData['apexcharts_render_data']['options']['title']['align']) ? $renderData['apexcharts_render_data']['options']['title']['align'] : 'center');
        $this->setFontStyle(isset($renderData['apexcharts_render_data']['options']['title']['style']['fontWeight']) ? $renderData['apexcharts_render_data']['options']['title']['style']['fontWeight'] : 'bold');
        $this->setSubtitle(isset($renderData['apexcharts_render_data']['options']['subtitle']['text']) ? $renderData['apexcharts_render_data']['options']['subtitle']['text'] : '');
        $this->setSubtitleAlign(isset($renderData['apexcharts_render_data']['options']['subtitle']['align']) ? $renderData['apexcharts_render_data']['options']['subtitle']['align'] : 'center');

        // Tooltip
        $this->setTooltipEnabled(isset($renderData['apexcharts_render_data']['options']['tooltip']['enabled']) ? $renderData['apexcharts_render_data']['options']['tooltip']['enabled'] : true);
        $this->setFollowCursor(isset($renderData['apexcharts_render_data']['options']['tooltip']['followCursor']) && $renderData['apexcharts_render_data']['options']['tooltip']['followCursor']);
        $this->setFillSeriesColor(isset($renderData['apexcharts_render_data']['options']['tooltip']['fillSeriesColor']) && $renderData['apexcharts_render_data']['options']['tooltip']['fillSeriesColor']);

        // Legend
        $this->setShowLegend(isset($renderData['apexcharts_render_data']['options']['legend']['show']) ? $renderData['apexcharts_render_data']['options']['legend']['show'] : true);
        $this->setLegendPosition(isset($renderData['apexcharts_render_data']['options']['legend']['position']) ? $renderData['apexcharts_render_data']['options']['legend']['position'] : 'bottom');

        //Toolbar
        $this->setShowToolbar(isset($renderData['apexcharts_render_data']['options']['chart']['toolbar']['show']) ? $renderData['apexcharts_render_data']['options']['chart']['toolbar']['show'] : false);

        if (isset($renderData['apexcharts_render_data']['options']['chart']['toolbar']['tools'])) {
            $toolbarButtons = array();
            foreach ($renderData['apexcharts_render_data']['options']['chart']['toolbar']['tools'] as $toolbarTool => $show) {
                if ($toolbarTool != 'customIcons' && $show) {
                    $toolbarButtons[] = $toolbarTool;
                }
            }
            $this->setToolbarButtons($toolbarButtons);
        } else $this->setToolbarButtons(['download', 'selection', 'zoomin', 'zoomout', 'zoom', 'pan']);
        $this->setExportingFileName((isset($renderData['apexcharts_render_data']['options']['chart']['toolbar']['export']['png']['filename']) && $renderData['apexcharts_render_data']['options']['chart']['toolbar']['export']['png']['filename'] != '') ? $renderData['apexcharts_render_data']['options']['chart']['toolbar']['export']['png']['filename'] : 'Chart');
    }

    /**
     * @param $type
     *
     * @return string
     */
    public function getApexSeriesType($type)
    {
        switch ($type) {
            case 'apexcharts_spline_area_chart':
            case 'apexcharts_stepline_area_chart':
            case 'apexcharts_basic_area_chart':
                $seriesType = 'area';
                break;
            case 'apexcharts_column_chart':
            case 'apexcharts_grouped_bar_chart':
            case 'apexcharts_stacked_bar_chart':
            case 'apexcharts_100_stacked_bar_chart':
            case 'apexcharts_stacked_column_chart':
            case 'apexcharts_100_stacked_column_chart':
                $seriesType = 'bar';
                break;
            case 'apexcharts_pie_with_gradient_chart':
            case 'apexcharts_pie_chart':
                $seriesType = 'pie';
                break;
            case 'apexcharts_donut_with_gradient_chart':
            case 'apexcharts_donut_chart':
                $seriesType = 'donut';
                break;
            case 'apexcharts_radar_chart':
                $seriesType = 'radar';
                break;
            case 'apexcharts_radialbar_gauge_chart':
            case 'apexcharts_radialbar_chart':
                $seriesType = 'radialBar';
                break;
            case 'apexcharts_polar_area_chart':
                $seriesType = 'polarArea';
                break;
            case 'apexcharts_straight_line_chart':
            case 'apexcharts_spline_chart':
            case 'apexcharts_stepline_chart':
            default:
                $seriesType = 'line';
                break;
        }

        return $seriesType;
    }

    /**
     * @return mixed|null
     */
    public function returnRenderData()
    {
        return $this->_apexcharts_render_data;
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
            'apexcharts_render_data' => $this->_apexcharts_render_data,
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
        if (!empty($renderData['apexcharts_render_data'])) {
            $this->_apexcharts_render_data = $renderData['apexcharts_render_data'];
        }
        return parent::setChartRenderData($chartData);
    }
}
