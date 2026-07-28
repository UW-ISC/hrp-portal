<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Writer\Xls;

use WPDT\PhpOffice\PhpSpreadsheet\Cell\DataValidation;
class CellDataValidation
{
    /**
     * @var array<string, int>
     */
    protected static $validationTypeMap = [DataValidation::TYPE_NONE => 0x0, DataValidation::TYPE_WHOLE => 0x1, DataValidation::TYPE_DECIMAL => 0x2, DataValidation::TYPE_LIST => 0x3, DataValidation::TYPE_DATE => 0x4, DataValidation::TYPE_TIME => 0x5, DataValidation::TYPE_TEXTLENGTH => 0x6, DataValidation::TYPE_CUSTOM => 0x7];
    /**
     * @var array<string, int>
     */
    protected static $errorStyleMap = [DataValidation::STYLE_STOP => 0x0, DataValidation::STYLE_WARNING => 0x1, DataValidation::STYLE_INFORMATION => 0x2];
    /**
     * @var array<string, int>
     */
    protected static $operatorMap = [DataValidation::OPERATOR_BETWEEN => 0x0, DataValidation::OPERATOR_NOTBETWEEN => 0x1, DataValidation::OPERATOR_EQUAL => 0x2, DataValidation::OPERATOR_NOTEQUAL => 0x3, DataValidation::OPERATOR_GREATERTHAN => 0x4, DataValidation::OPERATOR_LESSTHAN => 0x5, DataValidation::OPERATOR_GREATERTHANOREQUAL => 0x6, DataValidation::OPERATOR_LESSTHANOREQUAL => 0x7];
    public static function type(DataValidation $dataValidation) : int
    {
        $validationType = $dataValidation->getType();
        if (\is_string($validationType) && \array_key_exists($validationType, self::$validationTypeMap)) {
            return self::$validationTypeMap[$validationType];
        }
        return self::$validationTypeMap[DataValidation::TYPE_NONE];
    }
    public static function errorStyle(DataValidation $dataValidation) : int
    {
        $errorStyle = $dataValidation->getErrorStyle();
        if (\is_string($errorStyle) && \array_key_exists($errorStyle, self::$errorStyleMap)) {
            return self::$errorStyleMap[$errorStyle];
        }
        return self::$errorStyleMap[DataValidation::STYLE_STOP];
    }
    public static function operator(DataValidation $dataValidation) : int
    {
        $operator = $dataValidation->getOperator();
        if (\is_string($operator) && \array_key_exists($operator, self::$operatorMap)) {
            return self::$operatorMap[$operator];
        }
        return self::$operatorMap[DataValidation::OPERATOR_BETWEEN];
    }
}
