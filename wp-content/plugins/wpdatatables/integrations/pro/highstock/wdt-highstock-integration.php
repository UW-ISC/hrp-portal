<?php

namespace WDTIntegration;

defined('ABSPATH') or die('Access denied.');

// Full url to the WDT HighStock root directory
define('WDT_HS_ROOT_URL', WDT_PRO_INTEGRATIONS_URL . 'highstock/');
// Full path to the WDT HighStock root directory
define('WDT_HS_ROOT_PATH', WDT_PRO_INTEGRATIONS_PATH . 'highstock/');
// Path to the assets directory of the HighStock integration
define('WDT_HS_ASSETS_URL', WDT_HS_ROOT_URL . 'assets/');

define('WDT_HS_INTEGRATION', true);

/**
 * Class HighStockIntegration
 *
 * @package WDTIntegration
 */
class HighStockIntegration
{
    public static function init()
    {
        // Display the Highstock chart picker in the Chart creation wizard
        add_action('wpdatatables_add_chart_picker', array('WDTIntegration\HighStockIntegration', 'addHighStockChartPicker'));

        // Enqueue scripts
        add_action('wpdatatables_enqueue_chart_wizard_scripts', array('WDTIntegration\HighStockIntegration', 'enqueueScripts'), 11);

        add_action('wp_enqueue_scripts', array('WDTIntegration\HighStockIntegration', 'enqueueCustomStyles'));
    }

    /**
     * Adds the HighStock chart type picker once "HighStock" is selected as the engine
     */
    public static function addHighStockChartPicker()
    {
        ob_start();
        include 'templates/highstock_chart_picker.inc.php';
        $highStockChartsPicker = ob_get_contents();
        ob_end_clean();
        echo $highStockChartsPicker;

        // Hide the "HighStock not available for basic licences" notification
        wp_enqueue_style('wdt-highstock-css', WDT_HS_ASSETS_URL . 'css/wdt-highstock.css', array(), WDT_CURRENT_VERSION);

    }

    public static function enqueueScripts()
    {
        $highChartStockSource = get_option('wdtHighChartStableVersion') ? WDT_HS_ASSETS_URL . 'js/highcharts-stock.js' : '//code.highcharts.com/stock/modules/stock.js';
        wp_enqueue_script('wdt-highstock', $highChartStockSource, array('wdt-highcharts'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-wp-highstock', WDT_HS_ASSETS_URL . 'js/wdt.highstock.js', array('wdt-highcharts'), WDT_CURRENT_VERSION, true);
    }

    public static function enqueueCustomStyles() {
        wp_register_style('highcharts-custom-styles', false);
        wp_enqueue_style('highcharts-custom-styles');
        $customCss = "
    div[class^='highstock_'],
    .highcharts-root,
    .highcharts-container {
        overflow: visible !important;
    }";
        wp_add_inline_style('highcharts-custom-styles', $customCss);
    }
}

HighStockIntegration::init();