<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Infrastructure\Excel;

use WPDT\PhpOffice\PhpSpreadsheet\Reader\Csv;
use WPDT\PhpOffice\PhpSpreadsheet\Reader\IReader;
use WPDT\PhpOffice\PhpSpreadsheet\Reader\Ods;
use WPDT\PhpOffice\PhpSpreadsheet\Reader\Xls;
use WPDT\PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use WDTException;
use WPDataTables\Services\Tools\ToolsService;

/**
 * Isolates phpspreadsheet reader creation behind a seam.
 *
 * Inspects the file extension and returns the matching phpspreadsheet reader,
 * configuring the CSV delimiter from the `wdtCSVDelimiter` option (falling back
 * to auto-detection). The legacy `WPDataTable::createObjectReader()` static
 * method delegates here, so the existing callers (`WPDataTable`,
 * `WPDataTableCache`, `WDTSourceFile`) keep working unchanged.
 *
 * @package WPDataTables\Infrastructure\Excel
 */
class SpreadsheetReaderAdapter
{
    /**
     * Create a reader depending on the file extension.
     *
     * @param string $file
     *
     * @return Csv|Ods|Xls|Xlsx|IReader
     * @throws WDTException
     */
    public function createReader($file)
    {
        if (strpos(strtolower($file), '.xlsx')) {
            $objReader = new Xlsx();
        } elseif (strpos(strtolower($file), '.xls')) {
            $objReader = new Xls();
        } elseif (strpos(strtolower($file), '.ods')) {
            $objReader = new Ods();
        } elseif (strpos(strtolower($file), '.csv')) {
            $objReader = new Csv();
            $csvDelimiter = stripcslashes(get_option('wdtCSVDelimiter')) ? stripcslashes(get_option('wdtCSVDelimiter')) : ToolsService::detectCSVDelimiter($file);
            $objReader->setDelimiter($csvDelimiter);
        } else {
            throw new WDTException('File format not supported!');
        }

        return $objReader;
    }
}
