<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin;

/**
 * FrontendHooks — the single place where the frontend WordPress glue is wired:
 * the wpDataTables shortcodes and the filtering widget.
 *
 * Fired once from {@see Plugin} after the container is built. The shortcode
 * callbacks resolve to {@see ShortcodeController}; the legacy global
 * `wdt*ShortcodeHandler()` functions remain as public delegators into the same
 * controller, so third-party callers are unaffected.
 *
 * Widget registration is deferred to `widgets_init` (as before) and guarded by
 * `class_exists('wdtFilterWidget')`: that legacy class is only loaded in the
 * full build (its `require_once` is inside the `//[<-- Full version -->]//`
 * markers), so the guard reproduces the exact premium-gating that the markers
 * gave the old `add_action('widgets_init', ...)` line — the widget is not
 * registered in the Lite build, and no fatal is raised there.
 *
 * @package WPDataTables\Plugin
 */
class FrontendHooks
{
    /** @var ShortcodeController */
    private $shortcodeController;

    public function __construct(ShortcodeController $shortcodeController)
    {
        $this->shortcodeController = $shortcodeController;
    }

    /**
     * Wire the frontend hooks. Called once from Plugin after the container is
     * built.
     *
     * @return void
     */
    public function register()
    {
        add_shortcode('wpdatatable', array($this->shortcodeController, 'renderTable'));
        add_shortcode('wpdatachart', array($this->shortcodeController, 'renderChart'));
        add_shortcode('wpdatatable_cell', array($this->shortcodeController, 'renderCell'));
        add_shortcode('wpdatatable_sum', array($this->shortcodeController, 'renderFunction'));
        add_shortcode('wpdatatable_avg', array($this->shortcodeController, 'renderFunction'));
        add_shortcode('wpdatatable_min', array($this->shortcodeController, 'renderFunction'));
        add_shortcode('wpdatatable_max', array($this->shortcodeController, 'renderFunction'));

        add_action('widgets_init', array($this, 'registerWidget'));
    }

    /**
     * Register the filtering widget. Guarded by the legacy class so the Lite
     * build (where `wdtFilterWidget` is stripped) does not register a widget.
     *
     * @return void
     */
    public function registerWidget()
    {
        if (class_exists('wdtFilterWidget')) {
            register_widget('wdtFilterWidget');
        }
    }
}
