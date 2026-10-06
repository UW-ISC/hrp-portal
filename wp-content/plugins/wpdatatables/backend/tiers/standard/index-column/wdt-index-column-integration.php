<?php

namespace WDTIntegration;

use WDTConfigController;
use WDTColumn;

defined('ABSPATH') or die('Access denied.');

// Full url to the WDT Index Column root directory
define('WDT_ICOL_ROOT_URL', WDT_STANDARD_INTEGRATIONS_URL . 'index-column/');
// Full path to the WDT Index Column root directory
define('WDT_ICOL_ROOT_PATH', WDT_STANDARD_INTEGRATIONS_PATH . 'index-column/');
// Full url to the WDT Index Column root directory
define('WDT_ICOL_ROOT_SOURCE', plugin_dir_path(__FILE__));


/**
 * Class FormulaColumn
 *
 * @package WDTIntegration
 */
class IndexColumn
{
    public static function init()
    {
        add_filter('wpdatatables_column_formatter_file_name', array('WDTIntegration\IndexColumn', 'columnFormatterFileName'), 10, 2);
        // Enqueue scripts
        add_action('wpdatatables_enqueue_on_edit_page', array('WDTIntegration\IndexColumn', 'adminEnqueueScripts'));
        // Add index column type option
        add_action('wpdatatables_add_custom_column_type_option', array('WDTIntegration\IndexColumn', 'addIndexColumnType'));

    }

    public static function adminEnqueueScripts()
    {
        wp_enqueue_script('wdt-index-column-backend', WDT_ICOL_ROOT_URL . 'js/wdt-index-column-settings.js', array(), WDT_CURRENT_VERSION, true);
    }
    public static function columnFormatterFileName($columnFormatterFileName, $wdtColumnType)
    {
        if ($wdtColumnType == 'index') {
            $columnFormatterFileName = WDT_ICOL_ROOT_PATH . 'source/' . $columnFormatterFileName;
        }
        return $columnFormatterFileName;
    }
    public static function addIndexColumnType()
    {
        ob_start();
        include 'templates/index_column_type_option.inc.php';
        $columnTypeOption = ob_get_contents();
        ob_end_clean();
        echo $columnTypeOption;

    }

}

IndexColumn::init();