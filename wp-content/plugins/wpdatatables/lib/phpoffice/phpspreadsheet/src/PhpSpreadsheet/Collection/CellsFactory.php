<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Collection;

use WPDT\PhpOffice\PhpSpreadsheet\Settings;
use WPDT\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
abstract class CellsFactory
{
    /**
     * Initialise the cache storage.
     *
     * @param Worksheet $worksheet Enable cell caching for this worksheet
     *
     * */
    public static function getInstance(Worksheet $worksheet) : Cells
    {
        return new Cells($worksheet, Settings::getCache());
    }
}
