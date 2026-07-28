<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Writer\Xls\Style;

use WPDT\PhpOffice\PhpSpreadsheet\Style\Fill;
class CellFill
{
    /**
     * @var array<string, int>
     */
    protected static $fillStyleMap = [
        Fill::FILL_NONE => 0x0,
        Fill::FILL_SOLID => 0x1,
        Fill::FILL_PATTERN_MEDIUMGRAY => 0x2,
        Fill::FILL_PATTERN_DARKGRAY => 0x3,
        Fill::FILL_PATTERN_LIGHTGRAY => 0x4,
        Fill::FILL_PATTERN_DARKHORIZONTAL => 0x5,
        Fill::FILL_PATTERN_DARKVERTICAL => 0x6,
        Fill::FILL_PATTERN_DARKDOWN => 0x7,
        Fill::FILL_PATTERN_DARKUP => 0x8,
        Fill::FILL_PATTERN_DARKGRID => 0x9,
        Fill::FILL_PATTERN_DARKTRELLIS => 0xa,
        Fill::FILL_PATTERN_LIGHTHORIZONTAL => 0xb,
        Fill::FILL_PATTERN_LIGHTVERTICAL => 0xc,
        Fill::FILL_PATTERN_LIGHTDOWN => 0xd,
        Fill::FILL_PATTERN_LIGHTUP => 0xe,
        Fill::FILL_PATTERN_LIGHTGRID => 0xf,
        Fill::FILL_PATTERN_LIGHTTRELLIS => 0x10,
        Fill::FILL_PATTERN_GRAY125 => 0x11,
        Fill::FILL_PATTERN_GRAY0625 => 0x12,
        Fill::FILL_GRADIENT_LINEAR => 0x0,
        // does not exist in BIFF8
        Fill::FILL_GRADIENT_PATH => 0x0,
    ];
    public static function style(Fill $fill) : int
    {
        $fillStyle = $fill->getFillType();
        if (\is_string($fillStyle) && \array_key_exists($fillStyle, self::$fillStyleMap)) {
            return self::$fillStyleMap[$fillStyle];
        }
        return self::$fillStyleMap[Fill::FILL_NONE];
    }
}
