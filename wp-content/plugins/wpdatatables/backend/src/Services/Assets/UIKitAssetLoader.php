<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Assets;

use WPDataTables\Services\Localization\TutorialStringsProvider;

/**
 * Bootstrap UI kit asset enqueue and JS var export.
 *
 * @package WPDataTables\Services\Assets
 */
class UIKitAssetLoader
{
    public static $jsVars = array();

    /**
         * Enqueue JS and CSS UI Kit files
         */
        public static function wdtUIKitEnqueue()
        {
            if (get_option('wdtIncludeGoogleFonts'))
                wp_enqueue_style('wdt-include-inter-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-bootstrap', WDT_CSS_PATH . 'bootstrap/wpdatatables-bootstrap.css', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-bootstrap-select', WDT_CSS_PATH . 'bootstrap/bootstrap-select/bootstrap-select.min.css', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-bootstrap-tagsinput', WDT_CSS_PATH . 'bootstrap/bootstrap-tagsinput/bootstrap-tagsinput.css', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-bootstrap-datetimepicker', WDT_CSS_PATH . 'bootstrap/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-bootstrap-nouislider', WDT_CSS_PATH . 'bootstrap/bootstrap-nouislider/bootstrap-nouislider.min.css', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-wp-bootstrap-datetimepicker', WDT_CSS_PATH . 'bootstrap/bootstrap-datetimepicker/wdt-bootstrap-datetimepicker.min.css', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-bootstrap-colorpicker', WDT_CSS_PATH . 'bootstrap/bootstrap-colorpicker/bootstrap-colorpicker.min.css', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-wpdt-icons', WDT_ROOT_URL . 'assets/css/style.min.css', array(), WDT_CURRENT_VERSION);
            if (is_admin() && (get_option('wdtGettingStartedPageStatus') != 1)) {
                wp_enqueue_style('wdt-bootstrap-tour-css', WDT_CSS_PATH . 'bootstrap/bootstrap-tour/bootstrap-tour.css', array(), WDT_CURRENT_VERSION);
                wp_enqueue_style('wdt-bootstrap-tour-guide-css', WDT_CSS_PATH . 'bootstrap/bootstrap-tour/bootstrap-tour-guide.css', array(), WDT_CURRENT_VERSION);
            }
    
            wp_enqueue_style('wdt-animate', WDT_CSS_PATH . 'animate/animate.min.css', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-uikit', WDT_CSS_PATH . 'uikit/uikit.css', array(), WDT_CURRENT_VERSION);
    
            if (!is_admin() && get_option('wdtIncludeBootstrap') == 1) {
                wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/bootstrap.min.js', array('jquery'), WDT_CURRENT_VERSION, true);
            } else if (is_admin() && get_option('wdtIncludeBootstrapBackEnd') == 1) {
                wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/bootstrap.min.js', array('jquery'), WDT_CURRENT_VERSION, true);
            } else {
                wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/noconf.bootstrap.min.js', array('jquery'), WDT_CURRENT_VERSION, true);
            }
    
            if (is_admin() && (get_option('wdtGettingStartedPageStatus') != 1)) {
                wp_enqueue_script('wdt-bootstrap-tour', WDT_JS_PATH . 'bootstrap/bootstrap-tour/bootstrap-tour.js', array('jquery'), WDT_CURRENT_VERSION, true);
                wp_enqueue_script('wdt-bootstrap-tour-guide', WDT_JS_PATH . 'bootstrap/bootstrap-tour/bootstrap-tour-guide.js', array('jquery'), WDT_CURRENT_VERSION, true);
                wp_localize_script('wdt-bootstrap-tour-guide', 'wpdtTutorialStrings', TutorialStringsProvider::getTutorialsTranslationStrings());
            }
            wp_enqueue_script('wdt-bootstrap-tagsinput', WDT_JS_PATH . 'bootstrap/bootstrap-tagsinput/bootstrap-tagsinput.js', array(), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-moment', WDT_JS_PATH . 'moment/moment.js', array(), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-bootstrap-datetimepicker', WDT_JS_PATH . 'bootstrap/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js', array(), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-bootstrap-nouislider', WDT_JS_PATH . 'bootstrap/bootstrap-nouislider/bootstrap-nouislider.min.js', array(), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-wNumb', WDT_JS_PATH . 'bootstrap/bootstrap-nouislider/wNumb.min.js', array(), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-bootstrap-colorpicker', WDT_JS_PATH . 'bootstrap/bootstrap-colorpicker/bootstrap-colorpicker.min.js', array(), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-bootstrap-growl', WDT_JS_PATH . 'bootstrap/bootstrap-growl/bootstrap-growl.min.js', array(), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-bootstrap-select', WDT_JS_PATH . 'bootstrap/bootstrap-select/bootstrap-select.min.js', array('jquery', 'wdt-bootstrap'), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-bootstrap-ajax-select', WDT_JS_PATH . 'bootstrap/bootstrap-select/ajax-bootstrap-select.min.js', array(), WDT_CURRENT_VERSION, true);
        }

    public static function wdtUIKitEnqueueNotEdit()
        {
            if (get_option('wdtIncludeGoogleFonts'))
                wp_enqueue_style('wdt-include-inter-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-bootstrap', WDT_CSS_PATH . 'bootstrap/wpdatatables-bootstrap.css', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-bootstrap-select', WDT_CSS_PATH . 'bootstrap/bootstrap-select/bootstrap-select.min.css', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-animate', WDT_CSS_PATH . 'animate/animate.min.css', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-uikit', WDT_CSS_PATH . 'uikit/uikit.css', array(), WDT_CURRENT_VERSION);
    
            if (!is_admin() && get_option('wdtIncludeBootstrap') == 1) {
                wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/bootstrap.min.js', array('jquery',
                    'wdt-bootstrap-select'), WDT_CURRENT_VERSION, true);
            } else if (is_admin() && get_option('wdtIncludeBootstrapBackEnd') == 1) {
                wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/bootstrap.min.js', array('jquery',
                    'wdt-bootstrap-select'), WDT_CURRENT_VERSION, true);
            } else {
                wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/noconf.bootstrap.min.js', array('jquery',
                    'wdt-bootstrap-select'), WDT_CURRENT_VERSION, true);
            }
    
            wp_enqueue_script('wdt-bootstrap-select', WDT_JS_PATH . 'bootstrap/bootstrap-select/bootstrap-select.min.js', array(), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-bootstrap-ajax-select', WDT_JS_PATH . 'bootstrap/bootstrap-select/ajax-bootstrap-select.min.js', array(), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-moment', WDT_JS_PATH . 'moment/moment.js', array(), WDT_CURRENT_VERSION, true);
        }

    /**
         * Helper method to add PHP vars to JS vars
         *
         * @param $varName
         * @param $phpVar
         */
        public static function exportJSVar($varName, $phpVar)
        {
            self::$jsVars[$varName] = $phpVar;
        }

    /**
         * Helper method to print PHP vars to JS vars
         */
        public static function printJSVars()
        {
            if (!empty(self::$jsVars)) {
                $jsBlock = '<script type="text/javascript">';
                foreach (self::$jsVars as $varName => $jsVar) {
                    $jsBlock .= "var {$varName} = " . json_encode($jsVar) . ";";
                }
                $jsBlock .= '</script>';
                echo $jsBlock;
            }
        }
}
