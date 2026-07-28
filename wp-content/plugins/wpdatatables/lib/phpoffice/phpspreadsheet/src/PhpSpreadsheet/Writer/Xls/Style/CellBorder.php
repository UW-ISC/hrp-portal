<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Writer\Xls\Style;

use WPDT\PhpOffice\PhpSpreadsheet\Style\Border;
class CellBorder
{
    /**
     * @var array<string, int>
     */
    protected static $styleMap = [Border::BORDER_NONE => 0x0, Border::BORDER_THIN => 0x1, Border::BORDER_MEDIUM => 0x2, Border::BORDER_DASHED => 0x3, Border::BORDER_DOTTED => 0x4, Border::BORDER_THICK => 0x5, Border::BORDER_DOUBLE => 0x6, Border::BORDER_HAIR => 0x7, Border::BORDER_MEDIUMDASHED => 0x8, Border::BORDER_DASHDOT => 0x9, Border::BORDER_MEDIUMDASHDOT => 0xa, Border::BORDER_DASHDOTDOT => 0xb, Border::BORDER_MEDIUMDASHDOTDOT => 0xc, Border::BORDER_SLANTDASHDOT => 0xd, Border::BORDER_OMIT => 0x0];
    public static function style(Border $border) : int
    {
        $borderStyle = $border->getBorderStyle();
        if (\is_string($borderStyle) && \array_key_exists($borderStyle, self::$styleMap)) {
            return self::$styleMap[$borderStyle];
        }
        return self::$styleMap[Border::BORDER_NONE];
    }
}
