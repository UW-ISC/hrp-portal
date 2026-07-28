<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Calculation\Financial\CashFlow;

use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Exception;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Financial\Constants as FinancialConstants;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Financial\FinancialValidations;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
class CashFlowValidations extends FinancialValidations
{
    /**
     * @param mixed $rate
     */
    public static function validateRate($rate) : float
    {
        $rate = self::validateFloat($rate);
        return $rate;
    }
    /**
     * @param mixed $type
     */
    public static function validatePeriodType($type) : int
    {
        $rate = self::validateInt($type);
        if ($type !== FinancialConstants::PAYMENT_END_OF_PERIOD && $type !== FinancialConstants::PAYMENT_BEGINNING_OF_PERIOD) {
            throw new Exception(ExcelError::NAN());
        }
        return $rate;
    }
    /**
     * @param mixed $presentValue
     */
    public static function validatePresentValue($presentValue) : float
    {
        return self::validateFloat($presentValue);
    }
    /**
     * @param mixed $futureValue
     */
    public static function validateFutureValue($futureValue) : float
    {
        return self::validateFloat($futureValue);
    }
}
