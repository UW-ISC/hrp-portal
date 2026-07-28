<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Writer\Xls;

class ErrorCode
{
    /**
     * @var array<string, int>
     */
    protected static $errorCodeMap = ['#NULL!' => 0x0, '#DIV/0!' => 0x7, '#VALUE!' => 0xf, '#REF!' => 0x17, '#NAME?' => 0x1d, '#NUM!' => 0x24, '#N/A' => 0x2a];
    public static function error(string $errorCode) : int
    {
        if (\array_key_exists($errorCode, self::$errorCodeMap)) {
            return self::$errorCodeMap[$errorCode];
        }
        return 0;
    }
}
