<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class wpDataTableLimitReadFilter implements IReadFilter
{
    public function readCell($column, $row, $worksheetName = '') {
        //  Read rows 1 to 5 only
        if ($row >= 1 && $row <= 5) {
            return true;
        }
        return false;
    }
}