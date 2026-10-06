<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Formula;

use WPDataTable;
use WPDataTables\Services\Permissions\PermissionsService;

/**
 * FormulaController — admin-ajax handlers for the formula-column preview domain
 * (compute the live preview for a formula column, validate a formula).
 *
 * @package WPDataTables\Controllers\Formula
 */
class FormulaController
{
    /** @var PermissionsService */
    private $permissionsService;

    public function __construct(PermissionsService $permissionsService)
    {
        $this->permissionsService = $permissionsService;
    }

    /**
     * Get the preview for a formula column.
     *
     * @return void
     * @throws \WDTException
     */
    public function previewFormulaResult()
    {
        $tableId = (int)$_POST['table_id'];
        if (!$this->permissionsService->canEditTable($tableId)) {
            exit();
        }

        $formula = sanitize_text_field($_POST['formula']);

        $wpDataTable = WPDataTable::loadWpDataTable($tableId);

        echo $wpDataTable->calcFormulaPreview($formula);
        exit();
    }

    /**
     * Validate a formula for a formula column.
     *
     * @return void
     */
    public function checkFormulaResult()
    {
        $tableId = (int)$_POST['table_id'];
        if (!$this->permissionsService->canEditTable($tableId)) {
            exit();
        }

        $formula = sanitize_text_field($_POST['formula']);

        $wpDataTable = WPDataTable::loadWpDataTable($tableId);

        echo $wpDataTable->checkFormulaPreview($formula);
        exit();
    }
}
