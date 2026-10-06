<?php

namespace WDTIntegration;

defined('ABSPATH') or die('Access denied.');

// Full url to the Global Search for All Tables root directory
define('WDT_GSALL_ROOT_URL', WDT_STARTER_INTEGRATIONS_URL . 'global-search-for-all-tables/');
// Full path to the Global Search for All Tables root directory
define('WDT_GSALL_ROOT_PATH', WDT_STARTER_INTEGRATIONS_PATH . 'global-search-for-all-tables/');

define('WDT_GSALL_INTEGRATION', true);

class GlobalSearchAllTables
{
    private static $counter = 1;

    public static function init()
    {
        add_shortcode('wpdatatables_page_search', array('WDTIntegration\GlobalSearchAllTables',
            'wdtGlobalSearchHandler'));
    }

    public static function wdtGlobalSearchHandler($atts, $content = null)
    {
        extract(shortcode_atts(array(
            'class' => null,
            'placeholder' => null,
            'use_button' => 0,
            'button_class' => null,
            'button_placeholder' => null,
            'use_global_search_only' => 0
        ), $atts));

        $unique_id = self::$counter++;

        $class = is_null($class) ? '' : sanitize_text_field($class);
        $placeholder = is_null($placeholder) ? esc_html__('Search All Tables', 'wpdatatables') : sanitize_text_field($placeholder);
        $use_button = absint($use_button);
        $button_class = is_null($button_class) ? '' : sanitize_text_field($button_class);
        $button_placeholder = is_null($button_placeholder) ? esc_html__('Search', 'wpdatatables') : sanitize_text_field($button_placeholder);
        $use_global_search_only = absint($use_global_search_only);

        // Render search input field
        ob_start();
        include WDT_GSALL_ROOT_PATH . 'templates/global_search_all_tables.inc.php';
        $globalSearchAllTablesHtml = ob_get_contents();
        ob_end_clean();
        return $globalSearchAllTablesHtml;
    }
}

GlobalSearchAllTables::init();
