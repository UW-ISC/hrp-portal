<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Calculation\Engineering;

use WPDT\PhpOffice\PhpSpreadsheet\Calculation\ArrayEnabled;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Exception;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Functions;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
class BesselK
{
    use ArrayEnabled;
    /**
     * BESSELK.
     *
     *    Returns the modified Bessel function Kn(x), which is equivalent to the Bessel functions evaluated
     *        for purely imaginary arguments.
     *
     *    Excel Function:
     *        BESSELK(x,ord)
     *
     * @param mixed $x A float value at which to evaluate the function.
     *                                If x is nonnumeric, BESSELK returns the #VALUE! error value.
     *                      Or can be an array of values
     * @param mixed $ord The integer order of the Bessel function.
     *                       If ord is not an integer, it is truncated.
     *                                If $ord is nonnumeric, BESSELK returns the #VALUE! error value.
     *                       If $ord < 0, BESSELKI returns the #NUM! error value.
     *                      Or can be an array of values
     *
     * @return array|float|string Result, or a string containing an error
     *         If an array of numbers is passed as an argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function BESSELK($x, $ord)
    {
        if (\is_array($x) || \is_array($ord)) {
            return self::evaluateArrayArguments([self::class, __FUNCTION__], $x, $ord);
        }
        try {
            $x = EngineeringValidations::validateFloat($x);
            $ord = EngineeringValidations::validateInt($ord);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if ($ord < 0 || $x <= 0.0) {
            return ExcelError::NAN();
        }
        $fBk = self::calculate($x, $ord);
        return \is_nan($fBk) ? ExcelError::NAN() : $fBk;
    }
    private static function calculate(float $x, int $ord) : float
    {
        // special cases
        switch ($ord) {
            case 0:
                return self::besselK0($x);
            case 1:
                return self::besselK1($x);
        }
        return self::besselK2($x, $ord);
    }
    /**
     * Mollify Phpstan.
     *
     * @codeCoverageIgnore
     */
    private static function callBesselI(float $x, int $ord) : float
    {
        $rslt = BesselI::BESSELI($x, $ord);
        if (!\is_float($rslt)) {
            throw new Exception('Unexpected array or string');
        }
        return $rslt;
    }
    private static function besselK0(float $x) : float
    {
        if ($x <= 2) {
            $fNum2 = $x * 0.5;
            $y = $fNum2 * $fNum2;
            return -\log($fNum2) * self::callBesselI($x, 0) + (-0.57721566 + $y * (0.4227842 + $y * (0.23069756 + $y * (0.0348859 + $y * (0.00262698 + $y * (0.0001075 + $y * 7.4E-6))))));
        }
        $y = 2 / $x;
        return \exp(-$x) / \sqrt($x) * (1.25331414 + $y * (-0.07832358 + $y * (0.02189568 + $y * (-0.01062446 + $y * (0.00587872 + $y * (-0.0025154 + $y * 0.00053208))))));
    }
    private static function besselK1(float $x) : float
    {
        if ($x <= 2) {
            $fNum2 = $x * 0.5;
            $y = $fNum2 * $fNum2;
            return \log($fNum2) * self::callBesselI($x, 1) + (1 + $y * (0.15443144 + $y * (-0.6727857900000001 + $y * (-0.18156897 + $y * (-0.01919402 + $y * (-0.00110404 + $y * -4.686E-5)))))) / $x;
        }
        $y = 2 / $x;
        return \exp(-$x) / \sqrt($x) * (1.25331414 + $y * (0.23498619 + $y * (-0.0365562 + $y * (0.01504268 + $y * (-0.00780353 + $y * (0.00325614 + $y * -0.00068245))))));
    }
    private static function besselK2(float $x, int $ord) : float
    {
        $fTox = 2 / $x;
        $fBkm = self::besselK0($x);
        $fBk = self::besselK1($x);
        for ($n = 1; $n < $ord; ++$n) {
            $fBkp = $fBkm + $n * $fTox * $fBk;
            $fBkm = $fBk;
            $fBk = $fBkp;
        }
        return $fBk;
    }
}
