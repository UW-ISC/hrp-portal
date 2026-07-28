<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Reader\Xls\Style;

use WPDT\PhpOffice\PhpSpreadsheet\Style\Border as StyleBorder;
class Border
{
    /**
     * @var array<int, string>
     */
    protected static $borderStyleMap = [0x0 => StyleBorder::BORDER_NONE, 0x1 => StyleBorder::BORDER_THIN, 0x2 => StyleBorder::BORDER_MEDIUM, 0x3 => StyleBorder::BORDER_DASHED, 0x4 => StyleBorder::BORDER_DOTTED, 0x5 => StyleBorder::BORDER_THICK, 0x6 => StyleBorder::BORDER_DOUBLE, 0x7 => StyleBorder::BORDER_HAIR, 0x8 => StyleBorder::BORDER_MEDIUMDASHED, 0x9 => StyleBorder::BORDER_DASHDOT, 0xa => StyleBorder::BORDER_MEDIUMDASHDOT, 0xb => StyleBorder::BORDER_DASHDOTDOT, 0xc => StyleBorder::BORDER_MEDIUMDASHDOTDOT, 0xd => StyleBorder::BORDER_SLANTDASHDOT];
    public static function lookup(int $index) : string
    {
        if (isset(self::$borderStyleMap[$index])) {
            return self::$borderStyleMap[$index];
        }
        return StyleBorder::BORDER_NONE;
    }
}
