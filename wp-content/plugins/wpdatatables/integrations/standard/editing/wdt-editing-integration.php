<?php

namespace WDTIntegration;

use WDTTools;

defined('ABSPATH') or die('Access denied.');

// Full url to the Editing root directory
define('WDT_EDIT_ROOT_URL', WDT_STANDARD_INTEGRATIONS_URL . 'editing/');
// Full path to the Editing root directory
define('WDT_EDIT_ROOT_PATH', WDT_STANDARD_INTEGRATIONS_PATH . 'editing/');
define('WDT_EDIT_INTEGRATION', true);


/**
 * Class EditingIntegration
 *
 * @package WDTIntegration
 */
class EditingIntegration
{
    public static function init()
    {
        // Editing options in table settings
        add_action('wpdatatables_add_table_editing_elements', array('WDTIntegration\EditingIntegration',
            'addTableOptionsOnEditingTab'));

        // Editing options in column settings
        add_action('wpdatatables_add_column_editing_elements', array('WDTIntegration\EditingIntegration',
            'addColumnOptionsOnEditingTab'));

        // Enqueue scripts
        add_action('wpdatatables_enqueue_on_edit_page', array('WDTIntegration\EditingIntegration',
            'adminEnqueueScripts'), 10);
        add_action('wpdatatables_enqueue_on_frontend', array('WDTIntegration\EditingIntegration',
            'frontendEnqueueScripts'), 10);
    }

    /**
     * Adds the Editing table settings options
     */
    public static function addTableOptionsOnEditingTab()
    {
        ob_start();
        include 'templates/editing_table_settings_block.inc.php';
        $optionsOnEditingTab = ob_get_contents();
        ob_end_clean();
        echo $optionsOnEditingTab;
    }

    /**
     * Adds the Editing column settings options
     */
    public static function addColumnOptionsOnEditingTab()
    {
        ob_start();
        include 'templates/editing_column_settings_block.inc.php';
        $optionsOnEditingTab = ob_get_contents();
        ob_end_clean();
        echo $optionsOnEditingTab;
    }


    public static function adminEnqueueScripts()
    {
        wp_enqueue_script('wdt-inline-editing', WDT_EDIT_ROOT_URL . 'js/wdt.inlineEditing.js', array(), WDT_CURRENT_VERSION, true);
    }

    public static function frontendEnqueueScripts($obj)
    {
        if ($obj->inlineEditingEnabled()) {
            wp_enqueue_script('wdt-inline-editing', WDT_EDIT_ROOT_URL . 'js/wdt.inlineEditing.js', array(), WDT_CURRENT_VERSION, true);
            wp_localize_script('wdt-inline-editing', 'wpdatatables_inline_strings', WDTTools::getTranslationStringsInlineEditing());
        }
    }
}

EditingIntegration::init();