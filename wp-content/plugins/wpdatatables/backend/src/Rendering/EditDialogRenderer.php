<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Rendering;

use WPDataTable;

/**
 * Renders the edit-dialog / inline-editing markup for a standard wpDataTable.
 *
 * Renders the `templates/frontend/edit_dialog.inc.php` template, which was
 * previously `include`d inline from `table_main.inc.php`; that call site is now
 * a thin delegator (`WPDataTable::renderEditDialog()` → this service). Fires the
 * `wpdatatables_before_editor_dialog` / `wpdatatables_after_editor_dialog` /
 * `wpdatatables_insert_field_in_edit_dialog_input_type_*` actions.
 *
 * The template is rendered through {@see TemplateEngine}, which `include`s it
 * inside a `Closure` bound to the table so its `$this` references still resolve.
 * `edit_dialog.inc.php` reads no variables from the enclosing `table_main` scope
 * (it builds its own locals), so no `$vars` are required.
 *
 * @package WPDataTables\Rendering
 */
class EditDialogRenderer
{
    /** @var TemplateEngine */
    private $templateEngine;

    public function __construct(TemplateEngine $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    /**
     * Render the edit-dialog / inline-editing markup for the given table.
     *
     * @param WPDataTable $table the table being rendered
     *
     * @return string the rendered edit-dialog HTML
     */
    public function render(WPDataTable $table)
    {
        return $this->templateEngine->render(
            $table,
            WDT_TEMPLATE_PATH . 'frontend/edit_dialog.inc.php'
        );
    }
}
