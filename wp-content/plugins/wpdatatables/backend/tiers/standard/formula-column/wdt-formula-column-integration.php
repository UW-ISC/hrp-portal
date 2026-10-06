<?php

namespace WDTIntegration;

defined('ABSPATH') or die('Access denied.');

// Full url to the WDT Formula Column root directory
define('WDT_FCOL_ROOT_URL', WDT_STANDARD_INTEGRATIONS_URL . 'formula-column/');
// Full path to the WDT Formula Column root directory
define('WDT_FCOL_ROOT_PATH', WDT_STANDARD_INTEGRATIONS_PATH . 'formula-column/');
// Path to the assets directory of the Formula Column integration
define('WDT_FCOL_ASSETS_URL', WDT_FCOL_ROOT_URL . 'assets/');

define('WDT_FCOL_INTEGRATION', true);

/**
 * Class FormulaColumn
 *
 * @package WDTIntegration
 */
class FormulaColumn
{
    public static function init()
    {

        // Include file that contains FormulaWDTColumn class in wpdt
        add_filter('wpdatatables_column_formatter_file_name', array('WDTIntegration\FormulaColumn',
            'columnFormatterFileName'), 10, 2);

        // Add formula editor modal in admin area
        add_action('wpdatatables_admin_after_edit', array('WDTIntegration\FormulaColumn', 'addFormulaModal'));

        // Add formula column type option
        add_action('wpdatatables_add_custom_column_type_option', array('WDTIntegration\FormulaColumn',
            'addFormulaColumnType'));

    }

    public static function addFormulaColumnType()
    {
        ob_start();
        include 'templates/formula_column_type_option.inc.php';
        $columnTypeOption = ob_get_contents();
        ob_end_clean();
        echo $columnTypeOption;

    }

    public static function addFormulaModal($connection)
    {
        ob_start();
        include 'templates/formula_editor_modal.inc.php';
        $formulaModal = ob_get_contents();
        ob_end_clean();
        echo $formulaModal;

    }

    /**
     * Format file that contain column class
     *
     * @param $columnFormatterFileName
     * @param $wdtColumnType
     *
     * @return string
     */
    public static function columnFormatterFileName($columnFormatterFileName, $wdtColumnType)
    {
        if ($wdtColumnType == 'formula') {
            $columnFormatterFileName = WDT_FCOL_ROOT_PATH . 'source/' . $columnFormatterFileName;
        }
        return $columnFormatterFileName;
    }

}

FormulaColumn::init();