<?php

defined('ABSPATH') or die('Access denied.');

use WPDataTables\Infrastructure\Excel\SpreadsheetReaderAdapter;
use WPDataTables\Plugin\Plugin;
use WPDataTables\Services\DataSource\FileImportParser;

/**
 * Backward-compatibility facade for CSV/Excel/Google Sheet import during table construction.
 *
 * The implementation lives in {@see FileImportParser}. This class is kept so
 * existing call sites, tier integrations, and filter callbacks that receive
 * `$this` as a `wpDataTableSourceFile` instance keep working.
 */
class wpDataTableSourceFile extends FileImportParser
{
    /**
     * @param mixed       $file
     * @param object      $tableData
     * @param array|null  $columnTypes
     * @param array|null  $columnDateInputFormat
     * @param string|null $fileSourceAction
     */
    public function __construct(
        $file,
        $tableData,
        $columnTypes = null,
        $columnDateInputFormat = null,
        $fileSourceAction = null
    ) {
        parent::__construct(
            Plugin::container()->get(SpreadsheetReaderAdapter::class),
            $file,
            $tableData,
            $columnTypes,
            $columnDateInputFormat,
            $fileSourceAction
        );
    }
}
