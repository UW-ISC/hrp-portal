<?php

defined('ABSPATH') or die('Access denied.');

use Elementor\WPDataTables_Elementor_Widget;
use Elementor\WPDataCharts_Elementor_Widget;
use Elementor\Plugin;

final class WPDataTables_Elementor_Widgets {

    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function init() {
        if ($this->check_version()) {
            add_action('elementor/widgets/widgets_registered', [$this, 'register_widgets']);
        } else {
            add_action('elementor/widgets/register', [$this, 'register_widgets']);
        }
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'widget_styles']);
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'widget_styles']);
        add_action('elementor/elements/categories_registered', [$this, 'register_widget_categories']);
        if (defined('WDT_WOO_COMMERCE_INTEGRATION')) {
            add_action('elementor/editor/after_enqueue_scripts', array('WPDataTables_Elementor_Widgets', 'enqueueCustomElementorJs'));
        }
    }

    public function is_compatible() {
        return defined('ELEMENTOR_VERSION');
    }

    public function check_version() {
        return version_compare(ELEMENTOR_VERSION, '3.5.0', '<');
    }

    protected function __construct()
    {
        if ( $this->is_compatible() ) {
            add_action( 'elementor/init', [ $this, 'init' ] );
        }
    }

    public function includes()
    {
        require_once(WDT_STARTER_INTEGRATIONS_PATH . 'page-builders/elementor/widgets/class.wpDataTablesElementorWidget.php');
        require_once(WDT_STARTER_INTEGRATIONS_PATH . 'page-builders/elementor/widgets/class.wpDataChartsElementorWidget.php');
    }

    public function register_widgets($widgets_manager)
    {
        $this->includes();
        if ($this->check_version()){
            Plugin::instance()->widgets_manager->register_widget_type(new WPDataTables_Elementor_Widget());
            Plugin::instance()->widgets_manager->register_widget_type(new WPDataCharts_Elementor_Widget());
        } else {
            $widgets_manager->register(new WPDataTables_Elementor_Widget());
            $widgets_manager->register(new WPDataCharts_Elementor_Widget());
        }
    }

    public function widget_styles()
    {
        wp_register_style('wpdt-elementor-widget-font', WDT_STARTER_INTEGRATIONS_URL . 'page-builders/elementor/css/style.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wpdt-elementor-widget-font');
    }

    public function register_widget_categories($elements_manager)
    {
        $elements_manager->add_category(
            'wpdatatables-elementor',
            [
                'title' => 'wpDataTables',
                'icon' => 'wpdt-table-logo',
            ], 1);
    }

    public static function enqueueCustomElementorJs()
    {
        // Check if Elementor is in the editor mode
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            wp_enqueue_script('wdt-custom-elementor-js', plugin_dir_url(__FILE__) . 'js/wdt-custom-elementor-js.js', array('jquery'), WDT_CURRENT_VERSION, true);

            wp_localize_script('wdt-custom-elementor-js', 'wdt_ajax_object', array(
                'ajaxurl' => admin_url('admin-ajax.php')
            ));
        }
    }

}

\WPDataTables_Elementor_Widgets::instance();





