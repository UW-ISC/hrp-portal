<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\DataSource;

use Connection;
use Exception;
use WDTConfigController;
use WDTException;
use WPDataTables\Services\Tools\ToolsService;
use WPDataTables\Infrastructure\Excel\SpreadsheetReaderAdapter;
use wpDataTableConstructor;

/**
 * CSV/Excel/Google Sheet import for table construction and manual-table updates.
 *
 * Owns file-parser creation and the constructor-wizard preview/import orchestration
 * previously embedded in {@see wpDataTableConstructor} and
 * {@see wpDataTableSourceFile}.
 *
 * @package WPDataTables\Services\DataSource
 */
class FileImportService
{
    /**
     * Build a source-file parser for preview or manual-table import.
     *
     * Returns the legacy facade so filter hooks and tier integrations that expect
     * {@see \wpDataTableSourceFile} keep working.
     *
     * @param mixed       $file
     * @param object      $tableData
     * @param array|null  $columnTypes
     * @param array|null  $columnDateInputFormat
     * @param string|null $fileSourceAction
     *
     * @return \wpDataTableSourceFile
     */
    public function createParser(
        $file,
        $tableData,
        $columnTypes = null,
        $columnDateInputFormat = null,
        $fileSourceAction = null
    ) {
        return new \wpDataTableSourceFile(
            $file,
            $tableData,
            $columnTypes,
            $columnDateInputFormat,
            $fileSourceAction
        );
    }

    /**
     * Generate a file-based table preview (first rows) for the constructor wizard.
     *
     * @param array<string, mixed> $tableData
     *
     * @return array{result: string, message: string}
     * @throws WDTException
     */
    public function previewFileTable(array $tableData): array
    {
        try {
            if (!($file = wpDataTableConstructor::isUploadedFileEmpty($tableData['file']))) {
                throw new Exception(__('Empty file', 'wpdatatables'));
            }

            $tableDataObject = json_decode(json_encode($tableData), false);
            $objSourceFile = $this->createParser($file, $tableDataObject);

            $objSourceFile->setIsPreview(1);
            $objSourceFile->getTableTypeFromFile();
            $objSourceFile->prepareHeadingsArray();
        } catch (Exception $e) {
            return ['result' => 'error', 'message' => $e->getMessage()];
        }

        $namedDataArray = $objSourceFile->getNamedDataArray();
        $headingsArray = $objSourceFile->getHeadingsArray();

        /** @noinspection PhpUnusedLocalVariableInspection */
        $columnTypeArray = ToolsService::detectColumnDataTypes($namedDataArray, $headingsArray);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $possibleColumnTypes = ToolsService::getPossibleColumnTypes();

        $ret_val = '';

        if (!current_user_can('unfiltered_html')) {
            foreach ($namedDataArray as $key => &$nameData) {
                foreach ($headingsArray as &$heading) {
                    $heading = is_null($heading) ? sanitize_text_field($heading) : wp_kses_post($heading);
                    $nameData[$heading] = is_null($nameData[$heading]) ? sanitize_text_field($nameData[$heading]) : wp_kses_post($nameData[$heading]);
                }
            }
        }

        if (!empty($namedDataArray)) {
            ob_start();
            include WDT_TEMPLATE_PATH . 'admin/constructor/constructor_file_preview.inc.php';
            $ret_val = ob_get_contents();
            ob_end_clean();
        }

        return ['result' => 'success', 'message' => $ret_val];
    }

    /**
     * Read column types from constructor POST data and import file rows into a new manual table.
     *
     * @param array<string, mixed> $tableData
     * @param wpDataTableConstructor $constructor
     *
     * @return void
     * @throws WDTException
     * @throws Exception
     */
    public function importFileIntoManualTable(array $tableData, wpDataTableConstructor $constructor): void
    {
        $columnTypes = [];
        $columnDateInputFormat = [];
        $columnHeadersTemp = [];

        if (!($file = wpDataTableConstructor::isUploadedFileEmpty($tableData['file']))) {
            throw new Exception(__('Empty file', 'wpdatatables'));
        }

        for ($i = 0; $i < count($tableData['columns']); $i++) {
            if ($tableData['columns'][$i]['orig_header'] === '%%NEW_COLUMN%%') {
                $tableData['columns'][$i]['orig_header'] = 'column' . $i;
            }
            $columnHeader = ToolsService::generateMySQLColumnName(
                $tableData['columns'][$i]['orig_header'],
                $columnHeadersTemp
            );
            $columnTypes[$columnHeader] = sanitize_text_field($tableData['columns'][$i]['type']);
            $columnDateInputFormat[$columnHeader] = sanitize_text_field($tableData['columns'][$i]['dateInputFormat']);
            $columnHeadersTemp[] = $columnHeader;
        }

        $tableDataObject = json_decode(json_encode($tableData), false);
        $objSourceFile = $this->createParser($file, $tableDataObject, $columnTypes, $columnDateInputFormat, null);

        $objSourceFile->getTableTypeFromFile();
        $objSourceFile->prepareHeadingsArray();

        $constructor->generateManualTable($tableData);

        $columnHeaders = $constructor->getColumnHeaders();
        $columnHeaders = apply_filters_deprecated(
            'wpdt_insert_additional_column_header',
            [$columnHeaders],
            WDT_INITIAL_STARTER_VERSION,
            'wpdatatables_insert_additional_column_header'
        );
        $columnHeaders = apply_filters('wpdatatables_insert_additional_column_header', $columnHeaders);

        $vendor = Connection::getVendor($tableData['connection']);
        $columnQuoteStart = Connection::getLeftColumnQuote($vendor);
        $columnQuoteEnd = Connection::getRightColumnQuote($vendor);

        $insertStatementBeginning = WDTConfigController::createInsertStatement(
            $constructor->getTableName(),
            $columnHeaders,
            $columnQuoteStart,
            $columnQuoteEnd
        );

        $objSourceFile->prepareInsertBlocks(
            $insertStatementBeginning,
            $columnHeaders,
            $constructor->getTableName(),
            'import'
        );
    }

    /**
     * Full constructor-wizard import: create manual table + load file rows.
     *
     * @param array<string, mixed> $tableData
     *
     * @return array{tableId: int, link: string}
     * @throws WDTException
     * @throws Exception
     */
    public function importFileWizard(array $tableData): array
    {
        $constructor = new wpDataTableConstructor($tableData['connection']);
        $this->importFileIntoManualTable($tableData, $constructor);

        $tableId = (int) $constructor->getTableId();
        if ($tableId === 0) {
            throw new Exception(__('There was an error while trying to import table', 'wpdatatables'));
        }

        return [
            'tableId' => $tableId,
            'link'    => admin_url('admin.php?page=wpdatatables-constructor&source&table_id=' . $tableId),
        ];
    }
}
