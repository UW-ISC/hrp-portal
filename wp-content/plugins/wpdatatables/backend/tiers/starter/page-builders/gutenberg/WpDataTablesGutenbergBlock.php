<?php


defined('ABSPATH') or die('Access denied.');

/**
 * Class WpDataTablesGutenbergBlock
 *
 */
class WpDataTablesGutenbergBlock extends GutenbergBlock
{
    /**
     * Register wpDataTables block for Gutenberg
     */
    public static function registerBlockType()
    {

        wp_enqueue_script(
            'wpdatatables-gutenberg-block',
            WDT_STARTER_INTEGRATIONS_URL . 'page-builders/gutenberg/js/wpdatatables-gutenberg-block.js',
            array('wp-blocks', 'wp-components', 'wp-element', 'wp-editor')
        );

        wp_localize_script(
            'wpdatatables-gutenberg-block',
            'wpdatatables',
            array(
                'title' => 'wpDataTables',
                'description' => __('Choose the table that you’ve just created in the dropdown below, and the shortcode will be inserted automatically. You are able to provide values for placeholders and also for Export file name.', 'wpdatatables'),
                'data' => WDTConfigController::getAllTablesAndChartsForPageBuilders('gutenberg', 'tables')
            )
        );

        register_block_type(
            'wpdatatables/wpdatatables-gutenberg-block',
            array('editor_script' => 'wpdatatables-gutenberg-block')
        );

        if (defined('WDT_WOO_COMMERCE_INTEGRATION')) {
            wp_enqueue_script('wdt-custom-gutenberg-js', plugin_dir_url(__FILE__) . 'js/wdt-custom-gutenberg-js.js', array('jquery'), WDT_CURRENT_VERSION, true);

            wp_localize_script('wdt-custom-gutenberg-js', 'wdt_ajax_object', array(
                'ajaxurl' => admin_url('admin-ajax.php')
            ));
        }

    }

}