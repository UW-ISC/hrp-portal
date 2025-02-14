<?php

namespace WDTIntegration;

defined('ABSPATH') or die('Access denied.');

// Full url to the HighCharts root directory
define('WDT_HC_ROOT_URL', WDT_STANDARD_INTEGRATIONS_URL . 'highcharts/');
// Full path to the  HighCharts root directory
define('WDT_HC_ROOT_PATH', WDT_STANDARD_INTEGRATIONS_PATH . 'highcharts/');
// Path to the assets directory of the HighCharts integration
define('WDT_HC_ASSETS_URL', WDT_HC_ROOT_URL . 'assets/');
define('WDT_HC_INTEGRATION', true);

/**
 * Class HighChartsIntegration
 *
 * @package WDTIntegration
 */
class HighChartsIntegration
{
    public static function init()
    {
        // Display the Highcharts chart picker in the Chart creation wizard
        add_action('wpdatatables_add_chart_picker', array('WDTIntegration\HighChartsIntegration',
            'addHighChartsChartPicker'));

        add_action('wpdatatables_add_chart_stable_tag_option', array('WDTIntegration\HighChartsIntegration',
            'addHighChartsStableTagOption'));

        // Enqueue scripts
        add_action('wpdatatables_enqueue_chart_wizard_scripts', array('WDTIntegration\HighChartsIntegration',
            'enqueueScripts'), 10);
    }

    /**
     * Adds the HighCharts chart type picker once "HighCharts" is selected as the engine
     */
    public static function addHighChartsChartPicker()
    {
        ob_start();
        include 'templates/highcharts_chart_picker.inc.php';
        $highChartsChartsPicker = ob_get_contents();
        ob_end_clean();
        echo $highChartsChartsPicker;

        // Hide the "HighCharts not available for basic licences" notification
        wp_enqueue_style('wdt-highcharts-css', WDT_HC_ASSETS_URL . 'css/wdt-highcharts.css', array(), WDT_CURRENT_VERSION);

    }

    /**
     * Adds the HighCharts stable tag option in global settings
     */
    public static function addHighChartsStableTagOption()
    {
        ob_start();
        include 'templates/highcharts_stable_tag.inc.php';
        $highChartsStableTag = ob_get_contents();
        ob_end_clean();
        echo $highChartsStableTag;

    }

    public static function enqueueScripts()
    {
        $highChartLibSource = get_option('wdtHighChartStableVersion') ? WDT_HC_ASSETS_URL . 'js/highcharts.js' : '//code.highcharts.com/highcharts.js';
        $highChartMoreLibSource = get_option('wdtHighChartStableVersion') ? WDT_HC_ASSETS_URL . 'js/highcharts-more.js' : '//code.highcharts.com/highcharts-more.js';
        $highChart3DLibSource = get_option('wdtHighChartStableVersion') ? WDT_HC_ASSETS_URL . 'js/highcharts-3D.js' : '//code.highcharts.com/highcharts-3d.js';
        $highChartCylinderLibSource = get_option('wdtHighChartStableVersion') ? WDT_HC_ASSETS_URL . 'js/highcharts-cylinder.js' : '//code.highcharts.com/modules/cylinder.js';
        $highChartHeatMapLibSource = get_option('wdtHighChartStableVersion') ? WDT_HC_ASSETS_URL . 'js/highcharts-heatmap.js' : '//code.highcharts.com/modules/heatmap.js';
        $highChartFunnelLibSource = get_option('wdtHighChartStableVersion') ? WDT_HC_ASSETS_URL . 'js/highcharts-funnel.js' : '//code.highcharts.com/modules/funnel.js';
        $highChartFunnel3DLibSource = get_option('wdtHighChartStableVersion') ? WDT_HC_ASSETS_URL . 'js/highcharts-funnel3D.js' : '//code.highcharts.com/modules/funnel3d.js';
        $highChartTreeMapLibSource = get_option('wdtHighChartStableVersion') ? WDT_HC_ASSETS_URL . 'js/highcharts-treemap.js' : '//code.highcharts.com/modules/treemap.js';
        $highChartExportingLibSource = get_option('wdtHighChartStableVersion') ? WDT_HC_ASSETS_URL . 'js/highcharts-exporting.js' : '//code.highcharts.com/modules/exporting.js';
        $highChartExportingDataLibSource = get_option('wdtHighChartStableVersion') ? WDT_HC_ASSETS_URL . 'js/highcharts-exporting-data.js' : '//code.highcharts.com/modules/export-data.js';
        $highChartAccessibilityLibSource = get_option('wdtHighChartStableVersion') ? WDT_HC_ASSETS_URL . 'js/highcharts-accessibility.js' : '//code.highcharts.com/modules/accessibility.js';

        wp_enqueue_script('wdt-highcharts', $highChartLibSource, array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-highcharts-more', $highChartMoreLibSource, array('wdt-highcharts'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-highcharts-3d', $highChart3DLibSource, array('wdt-highcharts'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-cylinder', $highChartCylinderLibSource, array('wdt-highcharts'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-heatmap', $highChartHeatMapLibSource, array('wdt-highcharts'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-funnel', $highChartFunnelLibSource, array('wdt-highcharts'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-funnel3d', $highChartFunnel3DLibSource, array('wdt-highcharts'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-treemap', $highChartTreeMapLibSource, array('wdt-highcharts'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-exporting', $highChartExportingLibSource, array('wdt-highcharts'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-exporting-data', $highChartExportingDataLibSource, array('wdt-highcharts'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-highcharts-accessibility', $highChartAccessibilityLibSource, array('wdt-highcharts'), WDT_CURRENT_VERSION, true);

        wp_enqueue_script('wdt-wp-highcharts', WDT_HC_ASSETS_URL . 'js/wdt.highcharts.js', array('wdt-highcharts'), WDT_CURRENT_VERSION, true);
    }
}

HighChartsIntegration::init();