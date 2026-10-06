<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Infrastructure\Excel;

use WPDT\PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * phpspreadsheet read filter limiting a load to the first five rows.
 *
 * Used when only a small preview of a spreadsheet is required (e.g. detecting
 * headers / column types in the table constructor) so the whole file is not
 * read into memory. The legacy `wpDataTableLimitReadFilter` extends this class
 * as a backward-compatible shim.
 *
 * @package WPDataTables\Infrastructure\Excel
 */
class LimitReadFilter implements IReadFilter
{
    /**
     * @param int    $column
     * @param int    $row
     * @param string $worksheetName
     *
     * @return bool
     */
    public function readCell($column, $row, $worksheetName = ''): bool
    {
        //  Read rows 1 to 5 only
        if ($row >= 1 && $row <= 5) {
            return true;
        }

        return false;
    }
}
