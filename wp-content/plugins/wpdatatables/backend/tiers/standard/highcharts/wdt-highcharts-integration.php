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

    public static function enqueueScripts()
    {
        $highChartLibSource = WDT_HC_ASSETS_URL . 'js/highcharts.js';
        $highChartMoreLibSource = WDT_HC_ASSETS_URL . 'js/highcharts-more.js';
        $highChart3DLibSource = WDT_HC_ASSETS_URL . 'js/highcharts-3D.js';
        $highChartCylinderLibSource = WDT_HC_ASSETS_URL . 'js/highcharts-cylinder.js';
        $highChartHeatMapLibSource = WDT_HC_ASSETS_URL . 'js/highcharts-heatmap.js';
        $highChartFunnelLibSource = WDT_HC_ASSETS_URL . 'js/highcharts-funnel.js';
        $highChartFunnel3DLibSource = WDT_HC_ASSETS_URL . 'js/highcharts-funnel3D.js';
        $highChartTreeMapLibSource = WDT_HC_ASSETS_URL . 'js/highcharts-treemap.js';
        $highChartExportingLibSource = WDT_HC_ASSETS_URL . 'js/highcharts-exporting.js';
        $highChartExportingDataLibSource = WDT_HC_ASSETS_URL . 'js/highcharts-exporting-data.js';
        $highChartAccessibilityLibSource = WDT_HC_ASSETS_URL . 'js/highcharts-accessibility.js';

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