<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel;

use WPDT\Composer\Pcre\Preg;
use Datetime;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\ArrayEnabled;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Functions;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
use WPDT\PhpOffice\PhpSpreadsheet\Shared\Date as SharedDateHelper;
class TimeValue
{
    use ArrayEnabled;
    private const EXTRACT_TIME = '/\\b' . '(\\d+)' . '(:' . '(\\d+' . '(:\\d+' . '([.]\\d+)?' . ')?' . ')' . '(\\s*(a|p))?' . ')' . '/i';
    /**
     * TIMEVALUE.
     *
     * Returns a value that represents a particular time.
     * Use TIMEVALUE to convert a time represented by a text string to an Excel or PHP date/time stamp
     * value.
     *
     * NOTE: When used in a Cell Formula, MS Excel changes the cell format so that it matches the time
     * format of your regional settings. PhpSpreadsheet does not change cell formatting in this way.
     *
     * Excel Function:
     *        TIMEVALUE(timeValue)
     *
     * @param null|array|string $timeValue A text string that represents a time in any one of the Microsoft
     *                                    Excel time formats; for example, "6:45 PM" and "18:45" text strings
     *                                    within quotation marks that represent time.
     *                                    Date information in time_text is ignored.
     *                         Or can be an array of date/time values
     *
     * @return mixed Excel date/time serial value, PHP date/time serial value or PHP date/time object,
     *                        depending on the value of the ReturnDateType flag
     *         If an array of numbers is passed as the argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function fromString($timeValue)
    {
        if (\is_array($timeValue)) {
            return self::evaluateSingleArgumentArray([self::class, __FUNCTION__], $timeValue);
        }
        // try to parse as time iff there is at least one digit
        if (\is_string($timeValue) && \preg_match('/\\d/', $timeValue) !== 1) {
            return ExcelError::VALUE();
        }
        $timeValue = \trim($timeValue ?? '', '"');
        if (Preg::isMatch(self::EXTRACT_TIME, $timeValue, $matches)) {
            if (empty($matches[6])) {
                // am/pm
                $hour = (int) $matches[1];
                $timeValue = $hour % 24 . $matches[2];
            } elseif ($matches[6] === $matches[7]) {
                // Excel wants space before am/pm
                return ExcelError::VALUE();
            } else {
                $timeValue = $matches[0] . 'm';
            }
        }
        $PHPDateArray = Helpers::dateParse($timeValue);
        $retValue = ExcelError::VALUE();
        if (Helpers::dateParseSucceeded($PHPDateArray)) {
            /** @var int */
            $hour = $PHPDateArray['hour'];
            /** @var int */
            $minute = $PHPDateArray['minute'];
            /** @var int */
            $second = $PHPDateArray['second'];
            // OpenOffice-specific code removed - it works just like Excel
            $excelDateValue = SharedDateHelper::formattedPHPToExcel(1900, 1, 1, $hour, $minute, $second) - 1;
            $retType = Functions::getReturnDateType();
            if ($retType === Functions::RETURNDATE_EXCEL) {
                $retValue = (float) $excelDateValue;
            } elseif ($retType === Functions::RETURNDATE_UNIX_TIMESTAMP) {
                $retValue = (int) SharedDateHelper::excelToTimestamp($excelDateValue + 25569) - 3600;
            } else {
                $retValue = new DateTime('1900-01-01 ' . $PHPDateArray['hour'] . ':' . $PHPDateArray['minute'] . ':' . $PHPDateArray['second']);
            }
        }
        return $retValue;
    }
}
