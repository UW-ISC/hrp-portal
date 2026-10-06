<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Rendering;

use Closure;
use WPDataTable;

/**
 * Thin wrapper over the legacy PHP `include` templates in `templates/`.
 *
 * The frontend templates (`table_main.inc.php`, `table_head/body/footer`,
 * `edit_dialog`, `filter_form`, `wrap_template`, …) were written to run inside
 * a {@see \WPDataTable} method, so they reference table state through `$this`
 * (e.g. `$this->getColumns()`, `$this->getJsonDescription()`).
 *
 * To run the render orchestration from the Rendering layer without touching the
 * templates, this engine `include`s each template from inside a {@see \Closure}
 * bound to the table instance. `$this` inside the template therefore still
 * resolves to the `WPDataTable`, and any method-local variables the template
 * expects are injected via `extract()`.
 *
 * @package WPDataTables\Rendering
 */
class TemplateEngine
{
    /**
     * Render a legacy template in the scope of the given table instance and
     * return its captured output.
     *
     * @param WPDataTable $table        bound as `$this` inside the template
     * @param string      $templatePath absolute path to the `.inc.php` template
     * @param array       $vars         variables extracted into the template's
     *                                  local scope (mirrors the method locals the
     *                                  legacy `include` relied on)
     *
     * @return string the buffered template output
     */
    public function render(WPDataTable $table, $templatePath, array $vars = array())
    {
        $renderer = Closure::bind(
            function () use ($templatePath, $vars) {
                if (!empty($vars)) {
                    extract($vars, EXTR_SKIP);
                }
                ob_start();
                include $templatePath;
                return ob_get_clean();
            },
            $table,
            WPDataTable::class
        );

        return $renderer();
    }
}
