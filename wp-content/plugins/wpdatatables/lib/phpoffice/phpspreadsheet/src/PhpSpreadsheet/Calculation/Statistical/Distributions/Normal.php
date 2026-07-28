<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions;

use WPDT\PhpOffice\PhpSpreadsheet\Calculation\ArrayEnabled;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Engineering;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Exception;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
class Normal
{
    use ArrayEnabled;
    public const SQRT2PI = 2.5066282746310007;
    /**
     * NORMDIST.
     *
     * Returns the normal distribution for the specified mean and standard deviation. This
     * function has a very wide range of applications in statistics, including hypothesis
     * testing.
     *
     * @param mixed $value Float value for which we want the probability
     *                      Or can be an array of values
     * @param mixed $mean Mean value as a float
     *                      Or can be an array of values
     * @param mixed $stdDev Standard Deviation as a float
     *                      Or can be an array of values
     * @param mixed $cumulative Boolean value indicating if we want the cdf (true) or the pdf (false)
     *                      Or can be an array of values
     *
     * @return array|float|string The result, or a string containing an error
     *         If an array of numbers is passed as an argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function distribution($value, $mean, $stdDev, $cumulative)
    {
        if (\is_array($value) || \is_array($mean) || \is_array($stdDev) || \is_array($cumulative)) {
            return self::evaluateArrayArguments([self::class, __FUNCTION__], $value, $mean, $stdDev, $cumulative);
        }
        try {
            $value = DistributionValidations::validateFloat($value);
            $mean = DistributionValidations::validateFloat($mean);
            $stdDev = DistributionValidations::validateFloat($stdDev);
            $cumulative = DistributionValidations::validateBool($cumulative);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if ($stdDev < 0) {
            return ExcelError::NAN();
        }
        if ($cumulative) {
            return 0.5 * (1 + Engineering\Erf::erfValue(($value - $mean) / ($stdDev * \sqrt(2))));
        }
        return 1 / (self::SQRT2PI * $stdDev) * \exp(0 - ($value - $mean) ** 2 / (2 * ($stdDev * $stdDev)));
    }
    /**
     * NORMINV.
     *
     * Returns the inverse of the normal cumulative distribution for the specified mean and standard deviation.
     *
     * @param mixed $probability Float probability for which we want the value
     *                      Or can be an array of values
     * @param mixed $mean Mean Value as a float
     *                      Or can be an array of values
     * @param mixed $stdDev Standard Deviation as a float
     *                      Or can be an array of values
     *
     * @return array|float|string The result, or a string containing an error
     *         If an array of numbers is passed as an argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function inverse($probability, $mean, $stdDev)
    {
        if (\is_array($probability) || \is_array($mean) || \is_array($stdDev)) {
            return self::evaluateArrayArguments([self::class, __FUNCTION__], $probability, $mean, $stdDev);
        }
        try {
            $probability = DistributionValidations::validateProbability($probability);
            $mean = DistributionValidations::validateFloat($mean);
            $stdDev = DistributionValidations::validateFloat($stdDev);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if ($stdDev < 0) {
            return ExcelError::NAN();
        }
        return self::inverseNcdf($probability) * $stdDev + $mean;
    }
    /*
     *                                inverse_ncdf.php
     *                            -------------------
     *    begin                : Friday, January 16, 2004
     *    copyright            : (C) 2004 Michael Nickerson
     *    email                : nickersonm@yahoo.com
     *
     */
    private static function inverseNcdf(float $p) : float
    {
        //    Inverse ncdf approximation by Peter J. Acklam, implementation adapted to
        //    PHP by Michael Nickerson, using Dr. Thomas Ziegler's C implementation as
        //    a guide. http://home.online.no/~pjacklam/notes/invnorm/index.html
        //    I have not checked the accuracy of this implementation. Be aware that PHP
        //    will truncate the coeficcients to 14 digits.
        //    You have permission to use and distribute this function freely for
        //    whatever purpose you want, but please show common courtesy and give credit
        //    where credit is due.
        //    Input paramater is $p - probability - where 0 < p < 1.
        //    Coefficients in rational approximations
        static $a = [1 => -39.69683028665376, 2 => 220.9460984245205, 3 => -275.9285104469687, 4 => 138.357751867269, 5 => -30.66479806614716, 6 => 2.506628277459239];
        static $b = [1 => -54.47609879822406, 2 => 161.5858368580409, 3 => -155.6989798598866, 4 => 66.80131188771972, 5 => -13.28068155288572];
        static $c = [1 => -0.007784894002430293, 2 => -0.3223964580411365, 3 => -2.400758277161838, 4 => -2.549732539343734, 5 => 4.374664141464968, 6 => 2.938163982698783];
        static $d = [1 => 0.007784695709041462, 2 => 0.3224671290700398, 3 => 2.445134137142996, 4 => 3.754408661907416];
        //    Define lower and upper region break-points.
        $p_low = 0.02425;
        //Use lower region approx. below this
        $p_high = 1 - $p_low;
        //Use upper region approx. above this
        if (0 < $p && $p < $p_low) {
            //    Rational approximation for lower region.
            $q = \sqrt(-2 * \log($p));
            return ((((($c[1] * $q + $c[2]) * $q + $c[3]) * $q + $c[4]) * $q + $c[5]) * $q + $c[6]) / (((($d[1] * $q + $d[2]) * $q + $d[3]) * $q + $d[4]) * $q + 1);
        } elseif ($p_high < $p && $p < 1) {
            //    Rational approximation for upper region.
            $q = \sqrt(-2 * \log(1 - $p));
            return -((((($c[1] * $q + $c[2]) * $q + $c[3]) * $q + $c[4]) * $q + $c[5]) * $q + $c[6]) / (((($d[1] * $q + $d[2]) * $q + $d[3]) * $q + $d[4]) * $q + 1);
        }
        //    Rational approximation for central region.
        $q = $p - 0.5;
        $r = $q * $q;
        return ((((($a[1] * $r + $a[2]) * $r + $a[3]) * $r + $a[4]) * $r + $a[5]) * $r + $a[6]) * $q / ((((($b[1] * $r + $b[2]) * $r + $b[3]) * $r + $b[4]) * $r + $b[5]) * $r + 1);
    }
}
