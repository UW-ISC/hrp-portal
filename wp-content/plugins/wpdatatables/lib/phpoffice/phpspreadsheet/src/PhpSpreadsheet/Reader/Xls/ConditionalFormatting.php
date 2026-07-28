<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Reader\Xls;

use WPDT\PhpOffice\PhpSpreadsheet\Style\Conditional;
class ConditionalFormatting
{
    /**
     * @var array<int, string>
     */
    private static $types = [0x1 => Conditional::CONDITION_CELLIS, 0x2 => Conditional::CONDITION_EXPRESSION];
    /**
     * @var array<int, string>
     */
    private static $operators = [0x0 => Conditional::OPERATOR_NONE, 0x1 => Conditional::OPERATOR_BETWEEN, 0x2 => Conditional::OPERATOR_NOTBETWEEN, 0x3 => Conditional::OPERATOR_EQUAL, 0x4 => Conditional::OPERATOR_NOTEQUAL, 0x5 => Conditional::OPERATOR_GREATERTHAN, 0x6 => Conditional::OPERATOR_LESSTHAN, 0x7 => Conditional::OPERATOR_GREATERTHANOREQUAL, 0x8 => Conditional::OPERATOR_LESSTHANOREQUAL];
    public static function type(int $type) : ?string
    {
        if (isset(self::$types[$type])) {
            return self::$types[$type];
        }
        return null;
    }
    public static function operator(int $operator) : ?string
    {
        if (isset(self::$operators[$operator])) {
            return self::$operators[$operator];
        }
        return null;
    }
}
