<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Table;

use WPDataTables\Services\DataSource\FileImportService;
use WPDataTables\Services\DataSource\TableConstructorService;
use WPDataTables\Services\Permissions\PermissionsService;
use wpDataTableConstructor;
use stdClass;
use Exception;

/**
 * ManualTableController — admin-ajax handlers for the Manual- and file-based
 * table constructor CRUD: create a Manual table, preview a file-based table,
 * read a file into a table, and add / delete a column on a Manual table.
 *
 * The nonce/cap checks and the wire format ($_POST in, `echo`/`exit` out) are
 * what the existing admin JS expects. Wizard flows delegate to
 * {@see TableConstructorService} and {@see FileImportService}; column CRUD still
 * calls the global `wpDataTableConstructor` static helpers. The legacy global
 * functions remain as one-line delegators.
 *
 * Plain class by design (NOT extending the REST-shaped
 * {@see \WPDataTables\Controllers\Controller}): admin-ajax controllers are
 * ajax-shaped.
 *
 * @package WPDataTables\Controllers\Table
 */
class ManualTableController
{
    /** @var TableConstructorService */
    private TableConstructorService $tableConstructorService;

    /** @var FileImportService */
    private FileImportService $fileImportService;

    /** @var PermissionsService */
    private PermissionsService $permissionsService;

    public function __construct(
        TableConstructorService $tableConstructorService,
        FileImportService $fileImportService,
        PermissionsService $permissionsService
    ) {
        $this->tableConstructorService = $tableConstructorService;
        $this->fileImportService = $fileImportService;
        $this->permissionsService = $permissionsService;
    }

    /**
     * Create a manually built table and return its constructor edit link.
     *
     * @return void
     * @throws Exception
     */
    public function createManualTable()
    {

        if (!$this->permissionsService->canCreateTables()
            || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')
        ) {
            exit();
        }

        $tableData = stripslashes_deep($_POST['tableData']);
        $tableData = apply_filters('wpdatatables_before_create_manual_table', $tableData);
        $result = new stdClass();

        $newTableId = $this->tableConstructorService->createManualTable($tableData);

        if (!is_numeric($newTableId)) {
            $result->error = $newTableId->getMessage();
            echo json_encode($result);
            exit();
        }

        // Generate a link for new table
        $result->link = admin_url('admin.php?page=wpdatatables-constructor&source&table_id=' . $newTableId);
        echo json_encode($result);

        exit();
    }

    /**
     * Generate a file-based table preview (first 4 rows).
     *
     * @return void
     * @throws \WDTException
     */
    public function constructorPreviewFileTable()
    {
        if (!$this->permissionsService->canCreateTables()
            || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')
        ) {
            exit();
        }

        $tableData = $_POST['tableData'];
        // Sanitize table data
        $tableData['name'] = sanitize_text_field($tableData['name']);
        $tableData['method'] = sanitize_text_field($tableData['method']);
        $tableData['columnCount'] = sanitize_text_field($tableData['columnCount']);
        $tableData['connection'] = sanitize_text_field($tableData['connection']);
        $tableData['file'] = sanitize_text_field($tableData['file']);

        $tableData = apply_filters('wpdatatables_before_preview_file_table', $tableData);

        $result = $this->fileImportService->previewFileTable($tableData);

        echo json_encode($result);
        exit();
    }

    /**
     * Read data from a file and generate the table.
     *
     * @return void
     */
    public function constructorReadFileData()
    {
        if (!$this->permissionsService->canCreateTables()
            || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')
        ) {
            exit();
        }

        $result = array();
        $tableData = $_POST['tableData'];
        $tableData['name'] = sanitize_text_field($tableData['name']);
        $tableData['method'] = sanitize_text_field($tableData['method']);
        $tableData['columnCount'] = sanitize_text_field($tableData['columnCount']);
        $tableData['connection'] = sanitize_text_field($tableData['connection']);
        $tableData['file'] = sanitize_text_field($tableData['file']);

        if (isset($tableData['columns'])) {
            foreach ($tableData['columns'] as $columnKey => $column) {
                $tableData['columns'][$columnKey] = array_map('sanitize_text_field', $column);
            }
        }
        $tableData = apply_filters('wpdatatables_before_read_file_data', $tableData);

        try {
            $import = $this->tableConstructorService->importFromFile($tableData);
            $result['res'] = 'success';
            $result['link'] = $import['link'];
        } catch (Exception $e) {
            $result['res'] = 'error';
            $result['text'] = __('There was an error while trying to import table. Exception: ', 'wpdatatables') . $e->getMessage();
        }

        echo json_encode($result);
        exit();
    }

    /**
     * Add a column to a manually created table.
     *
     * @return void
     */
    public function addNewManualColumn()
    {
        $tableId = (int)$_POST['table_id'];
        if (!$this->permissionsService->canEditTable($tableId)
            || !wp_verify_nonce($_POST['wdtNonce'], 'wdtFrontendEditTableNonce' . $tableId)
        ) {
            exit();
        }

        $columnData = $_POST['column_data'];
        wpDataTableConstructor::addNewManualColumn($tableId, $columnData);

        exit();
    }

    /**
     * Delete a column from a manually created table.
     *
     * @return void
     * @throws Exception
     */
    public function deleteManualColumn()
    {
        $tableId = (int)$_POST['table_id'];
        if (!$this->permissionsService->canEditTable($tableId)
            || !wp_verify_nonce($_POST['wdtNonce'], 'wdtFrontendEditTableNonce' . $tableId)
        ) {
            exit();
        }

        $columnName = sanitize_text_field($_POST['column_name']);
        wpDataTableConstructor::deleteManualColumn($tableId, $columnName);

        exit();
    }
}
