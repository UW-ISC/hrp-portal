<?php

defined('ABSPATH') or die('Access denied.');

/**
 * Class wdtFilterWidget is used to create filtering widget for wpDataTables.
 *
 * Behaviour now lives in {@see \WPDataTables\Plugin\Widgets\FilterWidget}; this
 * global class is kept as a permanent thin facade for backward-compatibility
 * (it remains the registered widget class — see
 * {@see \WPDataTables\Plugin\FrontendHooks} — which keeps the widget `id_base`
 * as `wdtfilterwidget`, so existing placed widgets are unaffected).
 *
 * @author Alexander Gilmanov
 *
 * @since March 2014
 */
class wdtFilterWidget extends \WPDataTables\Plugin\Widgets\FilterWidget
{

}

?>
