<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin;

/**
 * Gutenberg block + TinyMCE editor integration hooks.
 *
 * @package WPDataTables\Plugin
 */
class EditorHooks
{
    /**
     * @return void
     */
    public function register(): void
    {
        add_action('plugins_loaded', array($this, 'initGutenbergBlocks'));
        add_action('init', array($this, 'registerMceButtons'));
    }

    /**
     * @return void
     */
    public function initGutenbergBlocks(): void
    {
        \WpDataTablesGutenbergBlock::init();
        \WpDataChartsGutenbergBlock::init();
        add_filter('block_categories_all', array($this, 'addBlockCategory'), 10, 2);
    }

    /**
     * @param array $categories Block categories.
     * @param mixed $post       Current post (unused).
     * @return array
     */
    public function addBlockCategory($categories, $post): array
    {
        return array_merge(
            array(
                array(
                    'slug' => 'wpdatatables-blocks',
                    'title' => 'wpDataTables',
                ),
            ),
            $categories
        );
    }

    /**
     * @return void
     */
    public function registerMceButtons(): void
    {
        add_filter('mce_external_plugins', array($this, 'addMceButtons'));
        add_filter('mce_buttons', array($this, 'registerMceButtonNames'));
    }

    /**
     * @param array $pluginArray MCE plugin list.
     * @return array
     */
    public function addMceButtons($pluginArray): array
    {
        $pluginArray['wpdatatables'] = WDT_JS_PATH . '/wpdatatables/wdt.mce.js';

        return $pluginArray;
    }

    /**
     * @param array $buttons MCE toolbar buttons.
     * @return array
     */
    public function registerMceButtonNames($buttons): array
    {
        $buttons[] = 'wpdatatable';
        $buttons[] = 'wpdatachart';

        return $buttons;
    }
}
