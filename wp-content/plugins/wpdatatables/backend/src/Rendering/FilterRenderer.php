<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Rendering;

use WPDataTable;

/**
 * Renders the "filter in form" markup for a standard wpDataTable.
 *
 * Renders the `templates/frontend/filter_form.inc.php` template, which was
 * previously `include`d inline from `table_main.inc.php`; that call site is now
 * a thin delegator (`WPDataTable::renderFilterForm()` → this service). Fires the
 * `wpdatatables_before_filtering_form` / `wpdatatables_after_filtering_form` /
 * `wpdatatables_filtering_form_search_button` actions and applies the
 * `wpdatatables_add_class_to_filter_in_form_element` filter.
 *
 * The template is rendered through {@see TemplateEngine}, which `include`s it
 * inside a `Closure` bound to the table so its `$this` references still resolve.
 * `filter_form.inc.php` reads no variables from the enclosing `table_main` scope
 * (it builds its own locals), so no `$vars` are required.
 *
 * @package WPDataTables\Rendering
 */
class FilterRenderer
{
    /** @var TemplateEngine */
    private $templateEngine;

    public function __construct(TemplateEngine $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    /**
     * Render the filter-in-form markup for the given table.
     *
     * @param WPDataTable $table the table being rendered
     *
     * @return string the rendered filter form HTML
     */
    public function render(WPDataTable $table)
    {
        return $this->templateEngine->render(
            $table,
            WDT_TEMPLATE_PATH . 'frontend/filter_form.inc.php'
        );
    }

    /**
     * Render the filtering-widget markup.
     *
     * Includes `frontend/filter_widget.inc.php`, which reads only the `$title`
     * local (already wrapped in the theme's `before_title`/`after_title` by the
     * caller) plus its own `do_action`s.
     *
     * @param string $title the widget title, pre-wrapped by the WP_Widget
     *
     * @return string the rendered filter-widget HTML
     */
    public function renderWidget($title)
    {
        ob_start();
        include WDT_TEMPLATE_PATH . 'frontend/filter_widget.inc.php';
        $filterWidgetHtml = ob_get_contents();
        ob_end_clean();

        return $filterWidgetHtml;
    }
}
