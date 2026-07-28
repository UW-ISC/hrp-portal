<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Reader\Xls\Style;

use WPDT\PhpOffice\PhpSpreadsheet\Style\Font;
class CellFont
{
    public static function escapement(Font $font, int $escapement) : void
    {
        switch ($escapement) {
            case 0x1:
                $font->setSuperscript(\true);
                break;
            case 0x2:
                $font->setSubscript(\true);
                break;
        }
    }
    /**
     * @var array<int, string>
     */
    protected static $underlineMap = [0x1 => Font::UNDERLINE_SINGLE, 0x2 => Font::UNDERLINE_DOUBLE, 0x21 => Font::UNDERLINE_SINGLEACCOUNTING, 0x22 => Font::UNDERLINE_DOUBLEACCOUNTING];
    public static function underline(Font $font, int $underline) : void
    {
        if (\array_key_exists($underline, self::$underlineMap)) {
            $font->setUnderline(self::$underlineMap[$underline]);
        }
    }
}
