<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Table;

use WPDataTable;
use WDTConfigController;
use WPDataTables\Services\Permissions\PermissionsService;

/**
 * TableConfigController — admin-ajax handlers for the table-configuration domain
 * (save a table + its columns, fetch a table's columns, fetch a complete table
 * as JSON for the chart range picker, and a lightweight table-type lookup used
 * by page-builder blocks).
 *
 * @package WPDataTables\Controllers\Table
 */
class TableConfigController
{
    /** @var PermissionsService */
    private $permissionsService;

    public function __construct(PermissionsService $permissionsService)
    {
        $this->permissionsService = $permissionsService;
    }

    /**
     * Save the config for the table and columns.
     *
     * @return void
     * @throws \Exception
     */
    public function saveTableWithColumns()
    {
        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtEditNonce')) {
            exit();
        }

        $table = apply_filters(
            'wpdatatables_before_save_table',
            json_decode(
                stripslashes_deep($_POST['table'])
            )
        );

        $tableId = isset($table->id) ? (int) $table->id : 0;
        if ($tableId > 0) {
            if (!$this->permissionsService->canEditTable($tableId)) {
                exit();
            }
        } elseif (!$this->permissionsService->canCreateTables()) {
            exit();
        }

        $table->file = $_POST['file'];
        $table->fileSourceAction = $_POST['fileSourceAction'];

        WDTConfigController::saveTableConfig($table);
    }

    /**
     * Return all columns for a provided table.
     *
     * @return void
     */
    public function getColumnsDataByTableId()
    {
        if (!(wp_verify_nonce($_POST['wdtNonce'], 'wdtChartWizardNonce') ||
                wp_verify_nonce($_POST['wdtNonce'], 'wdtEditNonce'))
        ) {
            exit();
        }

        $tableId = (int)$_POST['table_id'];
        if (!$this->permissionsService->canEditTable($tableId)
            && !$this->permissionsService->canCreateCharts()
            && !$this->permissionsService->canEditChart(null)
        ) {
            exit();
        }

        echo json_encode(WDTConfigController::loadColumnsFromDB($tableId));
        exit();
    }

    /**
     * Returns the complete table for the range picker.
     *
     * @return void
     * @throws \WDTException
     */
    public function getCompleteTableJSONById()
    {
        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtChartWizardNonce')) {
            exit();
        }

        $tableId = (int)$_POST['table_id'];
        if (!$this->permissionsService->canEditTable($tableId)
            && !$this->permissionsService->canCreateCharts()
            && !$this->permissionsService->canEditChart(null)
        ) {
            exit();
        }

        $wpDataTable = WPDataTable::loadWpDataTable($tableId, null, true);

        echo json_encode($wpDataTable->getDataRowsFormatted());
        exit();
    }

    /**
     * Helper to get table type by ID.
     * Added for page builder blocks to only render certain parameters for
     * certain types.
     *
     * @return void
     * @throws \Exception
     */
    public function getTableTypeById()
    {
        $table_id = isset($_POST['table_id']) ? (int)($_POST['table_id']) : '';
        $table_type = WDTConfigController::loadTableFromDB($table_id)->table_type;

        // Return the table type
        echo json_encode(array('tableType' => $table_type));
        exit();
    }
}
