<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Calculation\Engineering;

use WPDT\PhpOffice\PhpSpreadsheet\Calculation\ArrayEnabled;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Exception;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
class BesselY
{
    use ArrayEnabled;
    /**
     * BESSELY.
     *
     * Returns the Bessel function, which is also called the Weber function or the Neumann function.
     *
     *    Excel Function:
     *        BESSELY(x,ord)
     *
     * @param mixed $x A float value at which to evaluate the function.
     *                   If x is nonnumeric, BESSELY returns the #VALUE! error value.
     *                      Or can be an array of values
     * @param mixed $ord The integer order of the Bessel function.
     *                       If ord is not an integer, it is truncated.
     *                       If $ord is nonnumeric, BESSELY returns the #VALUE! error value.
     *                       If $ord < 0, BESSELY returns the #NUM! error value.
     *                      Or can be an array of values
     *
     * @return array|float|string Result, or a string containing an error
     *         If an array of numbers is passed as an argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function BESSELY($x, $ord)
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
        $fBy = self::calculate($x, $ord);
        return \is_nan($fBy) ? ExcelError::NAN() : $fBy;
    }
    private static function calculate(float $x, int $ord) : float
    {
        // special cases
        switch ($ord) {
            case 0:
                return self::besselY0($x);
            case 1:
                return self::besselY1($x);
        }
        return self::besselY2($x, $ord);
    }
    /**
     * Mollify Phpstan.
     *
     * @codeCoverageIgnore
     */
    private static function callBesselJ(float $x, int $ord) : float
    {
        $rslt = BesselJ::BESSELJ($x, $ord);
        if (!\is_float($rslt)) {
            throw new Exception('Unexpected array or string');
        }
        return $rslt;
    }
    private static function besselY0(float $x) : float
    {
        if ($x < 8.0) {
            $y = $x * $x;
            $ans1 = -2957821389.0 + $y * (7062834065.0 + $y * (-512359803.6 + $y * (10879881.29 + $y * (-86327.92757 + $y * 228.4622733))));
            $ans2 = 40076544269.0 + $y * (745249964.8 + $y * (7189466.438 + $y * (47447.2647 + $y * (226.1030244 + $y))));
            return $ans1 / $ans2 + 0.636619772 * self::callBesselJ($x, 0) * \log($x);
        }
        $z = 8.0 / $x;
        $y = $z * $z;
        $xx = $x - 0.785398164;
        $ans1 = 1 + $y * (-0.001098628627 + $y * (2.734510407E-5 + $y * (-2.073370639E-6 + $y * 2.093887211E-7)));
        $ans2 = -0.01562499995 + $y * (0.0001430488765 + $y * (-6.911147651E-6 + $y * (7.621095161000001E-7 + $y * -9.34945152E-8)));
        return \sqrt(0.636619772 / $x) * (\sin($xx) * $ans1 + $z * \cos($xx) * $ans2);
    }
    private static function besselY1(float $x) : float
    {
        if ($x < 8.0) {
            $y = $x * $x;
            $ans1 = $x * (-4900604943000.0 + $y * (1275274390000.0 + $y * (-51534381390.0 + $y * (734926455.1 + $y * (-4237922.726 + $y * 8511.937935)))));
            $ans2 = 24995805700000.0 + $y * (424441966400.0 + $y * (3733650367.0 + $y * (22459040.02 + $y * (102042.605 + $y * (354.9632885 + $y)))));
            return $ans1 / $ans2 + 0.636619772 * (self::callBesselJ($x, 1) * \log($x) - 1 / $x);
        }
        $z = 8.0 / $x;
        $y = $z * $z;
        $xx = $x - 2.356194491;
        $ans1 = 1.0 + $y * (0.00183105 + $y * (-3.516396496E-5 + $y * (2.457520174E-6 + $y * -2.40337019E-7)));
        $ans2 = 0.04687499995 + $y * (-0.0002002690873 + $y * (8.449199096E-6 + $y * (-8.8228987E-7 + $y * 1.05787412E-7)));
        return \sqrt(0.636619772 / $x) * (\sin($xx) * $ans1 + $z * \cos($xx) * $ans2);
    }
    private static function besselY2(float $x, int $ord) : float
    {
        $fTox = 2.0 / $x;
        $fBym = self::besselY0($x);
        $fBy = self::besselY1($x);
        for ($n = 1; $n < $ord; ++$n) {
            $fByp = $n * $fTox * $fBy - $fBym;
            $fBym = $fBy;
            $fBy = $fByp;
        }
        return $fBy;
    }
}
