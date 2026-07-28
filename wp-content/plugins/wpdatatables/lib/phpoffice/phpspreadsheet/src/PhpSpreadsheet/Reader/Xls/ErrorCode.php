<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Reader\Xls;

class ErrorCode
{
    private const ERROR_CODE_MAP = [0x0 => '#NULL!', 0x7 => '#DIV/0!', 0xf => '#VALUE!', 0x17 => '#REF!', 0x1d => '#NAME?', 0x24 => '#NUM!', 0x2a => '#N/A'];
    /**
     * Map error code, e.g. '#N/A'.
     *
     * @param int $code
     *
     * @return bool|string
     */
    public static function lookup($code)
    {
        return self::ERROR_CODE_MAP[$code] ?? \false;
    }
}
