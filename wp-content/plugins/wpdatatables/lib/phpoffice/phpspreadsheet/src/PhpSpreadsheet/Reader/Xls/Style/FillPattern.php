<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Reader\Xls\Style;

use WPDT\PhpOffice\PhpSpreadsheet\Style\Fill;
class FillPattern
{
    /**
     * @var array<int, string>
     */
    protected static $fillPatternMap = [0x0 => Fill::FILL_NONE, 0x1 => Fill::FILL_SOLID, 0x2 => Fill::FILL_PATTERN_MEDIUMGRAY, 0x3 => Fill::FILL_PATTERN_DARKGRAY, 0x4 => Fill::FILL_PATTERN_LIGHTGRAY, 0x5 => Fill::FILL_PATTERN_DARKHORIZONTAL, 0x6 => Fill::FILL_PATTERN_DARKVERTICAL, 0x7 => Fill::FILL_PATTERN_DARKDOWN, 0x8 => Fill::FILL_PATTERN_DARKUP, 0x9 => Fill::FILL_PATTERN_DARKGRID, 0xa => Fill::FILL_PATTERN_DARKTRELLIS, 0xb => Fill::FILL_PATTERN_LIGHTHORIZONTAL, 0xc => Fill::FILL_PATTERN_LIGHTVERTICAL, 0xd => Fill::FILL_PATTERN_LIGHTDOWN, 0xe => Fill::FILL_PATTERN_LIGHTUP, 0xf => Fill::FILL_PATTERN_LIGHTGRID, 0x10 => Fill::FILL_PATTERN_LIGHTTRELLIS, 0x11 => Fill::FILL_PATTERN_GRAY125, 0x12 => Fill::FILL_PATTERN_GRAY0625];
    /**
     * Get fill pattern from index
     * OpenOffice documentation: 2.5.12.
     *
     * @param int $index
     *
     * @return string
     */
    public static function lookup($index)
    {
        if (isset(self::$fillPatternMap[$index])) {
            return self::$fillPatternMap[$index];
        }
        return Fill::FILL_NONE;
    }
}
