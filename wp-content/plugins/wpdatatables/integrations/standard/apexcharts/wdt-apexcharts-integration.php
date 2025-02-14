<?php

namespace WDTIntegration;

defined('ABSPATH') or die('Access denied.');

// Full url to the ApexCharts root directory
define('WDT_AC_ROOT_URL', WDT_STANDARD_INTEGRATIONS_URL . 'apexcharts/');
// Full path to the  ApexCharts root directory
define('WDT_AC_ROOT_PATH', WDT_STANDARD_INTEGRATIONS_PATH . 'apexcharts/');
// Path to the assets directory of the ApexCharts integration
define('WDT_AC_ASSETS_URL', WDT_AC_ROOT_URL . 'assets/');
define('WDT_AC_INTEGRATION', true);

/**
 * Class ApexChartsIntegration
 *
 * @package WDTIntegration
 */
class ApexChartsIntegration
{
    public static function init()
    {
        // Display the ApexCharts chart picker in the Chart creation wizard
        add_action('wpdatatables_add_chart_picker', array('WDTIntegration\ApexChartsIntegration',
            'addApexChartsChartPicker'));

        add_action('wpdatatables_add_chart_stable_tag_option', array('WDTIntegration\ApexChartsIntegration',
            'addApexChartsStableTagOption'));

        // Enqueue scripts
        add_action('wpdatatables_enqueue_chart_wizard_scripts', array('WDTIntegration\ApexChartsIntegration',
            'enqueueScripts'), 10);
    }

    /**
     * Adds the ApexCharts chart stable tag option in global settings
     */
    public static function addApexChartsStableTagOption()
    {
        ob_start();
        include 'templates/apexcharts_chart_stable_tag.inc.php';
        $apexChartsStableTag = ob_get_contents();
        ob_end_clean();
        echo $apexChartsStableTag;

    }

    /**
     * Adds the ApexCharts chart type picker once "ApexCharts" is selected as the engine
     */
    public static function addApexChartsChartPicker()
    {
        ob_start();
        include 'templates/apexcharts_chart_picker.inc.php';
        $apexChartsChartsPicker = ob_get_contents();
        ob_end_clean();
        echo $apexChartsChartsPicker;

        // Hide the "ApexCharts not available for basic licences" notification
        wp_enqueue_style('wdt-apexcharts-css', WDT_AC_ASSETS_URL . 'css/wdt-apexcharts.css', array(), WDT_CURRENT_VERSION);

    }

    public static function enqueueScripts()
    {
        $apexChartLibSource = get_option('wdtApexStableVersion') ? WDT_AC_ASSETS_URL . 'js/apexcharts.js' : '//cdn.jsdelivr.net/npm/apexcharts';
        wp_enqueue_script('wdt-apexcharts', $apexChartLibSource, array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-wp-apexcharts', WDT_AC_ASSETS_URL . 'js/wdt.apexcharts.js', array('wdt-apexcharts'), WDT_CURRENT_VERSION, true);
    }
}

ApexChartsIntegration::init();