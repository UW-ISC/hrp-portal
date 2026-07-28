<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Reader\Xls;

use WPDT\PhpOffice\PhpSpreadsheet\Cell\DataValidation;
class DataValidationHelper
{
    /**
     * @var array<int, string>
     */
    private static $types = [0x0 => DataValidation::TYPE_NONE, 0x1 => DataValidation::TYPE_WHOLE, 0x2 => DataValidation::TYPE_DECIMAL, 0x3 => DataValidation::TYPE_LIST, 0x4 => DataValidation::TYPE_DATE, 0x5 => DataValidation::TYPE_TIME, 0x6 => DataValidation::TYPE_TEXTLENGTH, 0x7 => DataValidation::TYPE_CUSTOM];
    /**
     * @var array<int, string>
     */
    private static $errorStyles = [0x0 => DataValidation::STYLE_STOP, 0x1 => DataValidation::STYLE_WARNING, 0x2 => DataValidation::STYLE_INFORMATION];
    /**
     * @var array<int, string>
     */
    private static $operators = [0x0 => DataValidation::OPERATOR_BETWEEN, 0x1 => DataValidation::OPERATOR_NOTBETWEEN, 0x2 => DataValidation::OPERATOR_EQUAL, 0x3 => DataValidation::OPERATOR_NOTEQUAL, 0x4 => DataValidation::OPERATOR_GREATERTHAN, 0x5 => DataValidation::OPERATOR_LESSTHAN, 0x6 => DataValidation::OPERATOR_GREATERTHANOREQUAL, 0x7 => DataValidation::OPERATOR_LESSTHANOREQUAL];
    public static function type(int $type) : ?string
    {
        if (isset(self::$types[$type])) {
            return self::$types[$type];
        }
        return null;
    }
    public static function errorStyle(int $errorStyle) : ?string
    {
        if (isset(self::$errorStyles[$errorStyle])) {
            return self::$errorStyles[$errorStyle];
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
