<?php

namespace WDTIntegration;

defined('ABSPATH') or die('Access denied.');

// Full url to the Fixed Columns And Header root directory
define('WDT_FCH_ROOT_URL', WDT_STANDARD_INTEGRATIONS_URL . 'fixed-columns-and-headers/');
// Full path to the Fixed Columns And Header root directory
define('WDT_FCH_ROOT_PATH', WDT_STANDARD_INTEGRATIONS_PATH . 'fixed-columns-and-headers/');
define('WDT_FCH_INTEGRATION', true);


/**
 * Class FixedColumnsAndHeaderIntegration
 *
 * @package WDTIntegration
 */
class FixedColumnsAndHeaderIntegration
{
    public static function init()
    {
        // Display options in table settings
        add_action('wpdatatables_add_table_configuration_tabpanel', array('WDTIntegration\FixedColumnsAndHeaderIntegration',
            'addOptionsOnAdvancedTab'));

        // Enqueue scripts
        add_action('wpdatatables_enqueue_on_edit_page', array('WDTIntegration\FixedColumnsAndHeaderIntegration',
            'adminEnqueueScripts'), 10);
        add_action('wpdatatables_enqueue_on_frontend', array('WDTIntegration\FixedColumnsAndHeaderIntegration',
            'frontendEnqueueScripts'), 10);
    }

    /**
     * Adds the HighCharts chart type picker once "HighCharts" is selected as the engine
     */
    public static function addOptionsOnAdvancedTab()
    {
        ob_start();
        include 'templates/table_settings_block.php';
        $optionsOnAdvancedTab = ob_get_contents();
        ob_end_clean();
        echo $optionsOnAdvancedTab;
    }

    public static function adminEnqueueScripts()
    {
        wp_enqueue_style('wdt-datatables-fixedHeader', WDT_FCH_ROOT_URL . 'fixed-header/fixedHeader.dataTables.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-datatables-fixedColumn', WDT_FCH_ROOT_URL . 'fixed-column/fixedColumns.dataTables.min.css', array(), WDT_CURRENT_VERSION);

        wp_enqueue_script('wdt-fixed-columns', WDT_FCH_ROOT_URL . 'fixed-column/dataTables.fixedColumns.js', array('jquery',
            'wdt-datatables'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-fixed-header', WDT_FCH_ROOT_URL . 'fixed-header/dataTables.fixedHeader.js', array('jquery',
            'wdt-datatables'), WDT_CURRENT_VERSION, true);

        wp_enqueue_script('wdt-fch-backend', WDT_FCH_ROOT_URL . 'js/wdt-settings.js', array(), WDT_CURRENT_VERSION, true);
    }

    public static function frontendEnqueueScripts($obj)
    {
        if ($obj->isFixedHeaders()) {
            wp_enqueue_script('wdt-fixed-header', WDT_FCH_ROOT_URL . 'fixed-header/dataTables.fixedHeader.js', array('jquery',
                'wdt-datatables'), WDT_CURRENT_VERSION, true);
            wp_enqueue_style('wdt-datatables-fixedHeader', WDT_FCH_ROOT_URL . 'fixed-header/fixedHeader.dataTables.min.css', array(), WDT_CURRENT_VERSION);
        }
        if ($obj->isFixedColumns()) {
            wp_enqueue_script('wdt-fixed-columns', WDT_FCH_ROOT_URL . 'fixed-column/dataTables.fixedColumns.js', array('jquery',
                'wdt-datatables'), WDT_CURRENT_VERSION, true);
            wp_enqueue_style('wdt-datatables-fixedColumn', WDT_FCH_ROOT_URL . 'fixed-column/fixedColumns.dataTables.min.css', array(), WDT_CURRENT_VERSION);
        }
    }
}

FixedColumnsAndHeaderIntegration::init();