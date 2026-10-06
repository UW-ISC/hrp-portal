<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\DataSource;

use Exception;
use wpDataTableConstructor;
use WDTException;

/**
 * Admin table-constructor wizard orchestration.
 *
 * Thin service layer over the legacy {@see wpDataTableConstructor} engine so
 * {@see \WPDataTables\Controllers\Table\ManualTableController} and future REST
 * routes converge here instead of instantiating the global class directly.
 *
 * @package WPDataTables\Services\DataSource
 */
class TableConstructorService
{
    /** @var FileImportService */
    private FileImportService $fileImportService;

    public function __construct(FileImportService $fileImportService)
    {
        $this->fileImportService = $fileImportService;
    }

    /**
     * Create an empty manual table from constructor wizard POST data.
     *
     * @param array<string, mixed> $tableData
     *
     * @return int|WDTException New table id, or a WDTException on validation failure.
     */
    public function createManualTable(array $tableData)
    {
        $connection = $tableData['connection'] ?? null;
        $constructor = new wpDataTableConstructor($connection);

        return $constructor->generateManualTable($tableData);
    }

    /**
     * Import rows from an uploaded file into a new manual table.
     *
     * @param array<string, mixed> $tableData
     *
     * @return array{tableId: int, link: string}
     * @throws Exception
     * @throws WDTException
     */
    public function importFromFile(array $tableData): array
    {
        return $this->fileImportService->importFileWizard($tableData);
    }
}
