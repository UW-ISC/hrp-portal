<?php

class WdtCustomBakery
{
    public static function init()
    {
        if (function_exists('vc_map') && defined('WDT_WOO_COMMERCE_INTEGRATION')) {
            add_action('wp_enqueue_scripts', 'enqueueCustomWpBakeryJs');
        }
    }

    public static function enqueueCustomWpBakeryJs()
    {
        wp_enqueue_script('wdt-custom-bakery-js', plugin_dir_url(__FILE__) . 'assets/js/wdt-custom-bakery-js.js', array('jquery'), WDT_CURRENT_VERSION, true);

        wp_localize_script('wdt-custom-bakery-js', 'wdt_ajax_object', array(
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }
}

WdtCustomBakery::init();