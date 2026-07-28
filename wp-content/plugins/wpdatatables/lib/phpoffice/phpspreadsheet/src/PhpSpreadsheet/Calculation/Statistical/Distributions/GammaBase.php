<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions;

use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Functions;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
abstract class GammaBase
{
    private const LOG_GAMMA_X_MAX_VALUE = 2.55E+305;
    private const EPS = 2.22E-16;
    private const MAX_VALUE = 1.2E+308;
    private const SQRT2PI = 2.5066282746310007;
    private const MAX_ITERATIONS = 256;
    /** @return float|string */
    protected static function calculateDistribution(float $value, float $a, float $b, bool $cumulative)
    {
        if ($cumulative) {
            return self::incompleteGamma($a, $value / $b) / self::gammaValue($a);
        }
        return 1 / ($b ** $a * self::gammaValue($a)) * $value ** ($a - 1) * \exp(0 - $value / $b);
    }
    /** @return float|string */
    protected static function calculateInverse(float $probability, float $alpha, float $beta)
    {
        $xLo = 0;
        $xHi = $alpha * $beta * 5;
        $dx = 1024;
        $x = $xNew = 1;
        $i = 0;
        while (\abs($dx) > Functions::PRECISION && ++$i <= self::MAX_ITERATIONS) {
            // Apply Newton-Raphson step
            $result = self::calculateDistribution($x, $alpha, $beta, \true);
            if (!\is_float($result)) {
                return ExcelError::NA();
            }
            $error = $result - $probability;
            if ($error == 0.0) {
                $dx = 0;
            } elseif ($error < 0.0) {
                $xLo = $x;
            } else {
                $xHi = $x;
            }
            $pdf = self::calculateDistribution($x, $alpha, $beta, \false);
            // Avoid division by zero
            if (!\is_float($pdf)) {
                return ExcelError::NA();
            }
            if ($pdf !== 0.0) {
                $dx = $error / $pdf;
                $xNew = $x - $dx;
            }
            // If the NR fails to converge (which for example may be the
            // case if the initial guess is too rough) we apply a bisection
            // step to determine a more narrow interval around the root.
            if ($xNew < $xLo || $xNew > $xHi || $pdf == 0.0) {
                $xNew = ($xLo + $xHi) / 2;
                $dx = $xNew - $x;
            }
            $x = $xNew;
        }
        if ($i === self::MAX_ITERATIONS) {
            return ExcelError::NA();
        }
        return $x;
    }
    //
    //    Implementation of the incomplete Gamma function
    //
    public static function incompleteGamma(float $a, float $x) : float
    {
        static $max = 32;
        $summer = 0;
        for ($n = 0; $n <= $max; ++$n) {
            $divisor = $a;
            for ($i = 1; $i <= $n; ++$i) {
                $divisor *= $a + $i;
            }
            $summer += $x ** $n / $divisor;
        }
        return $x ** $a * \exp(0 - $x) * $summer;
    }
    //
    //    Implementation of the Gamma function
    //
    public static function gammaValue(float $value) : float
    {
        if ($value == 0.0) {
            return 0;
        }
        static $p0 = 1.000000000190015;
        static $p = [1 => 76.18009172947146, 2 => -86.50532032941678, 3 => 24.01409824083091, 4 => -1.231739572450155, 5 => 0.001208650973866179, 6 => -5.395239384953E-6];
        $y = $x = $value;
        $tmp = $x + 5.5;
        $tmp -= ($x + 0.5) * \log($tmp);
        $summer = $p0;
        for ($j = 1; $j <= 6; ++$j) {
            $summer += $p[$j] / ++$y;
        }
        return \exp(0 - $tmp + \log(self::SQRT2PI * $summer / $x));
    }
    private const LG_D1 = -0.5772156649015329;
    private const LG_D2 = 0.42278433509846713;
    private const LG_D4 = 1.791759469228055;
    private const LG_P1 = [4.945235359296727, 201.8112620856775, 2290.8383738313464, 11319.672059033808, 28557.246356716354, 38484.962284437934, 26377.487876241954, 7225.813979700288];
    private const LG_P2 = [4.974607845568932, 542.4138599891071, 15506.93864978365, 184793.29044456323, 1088204.7694688288, 3338152.96798703, 5106661.678927353, 3074109.0548505397];
    private const LG_P4 = [14745.0216605994, 2426813.3694867045, 121475557.40450932, 2663432449.630977, 29403789566.34554, 170266573776.5399, 492612579337.7431, 560625185622.3951];
    private const LG_Q1 = [67.48212550303778, 1113.3323938571993, 7738.757056935398, 27639.870744033407, 54993.102062261576, 61611.22180066002, 36351.2759150194, 8785.536302431014];
    private const LG_Q2 = [183.03283993705926, 7765.049321445006, 133190.38279660742, 1136705.8213219696, 5267964.117437947, 13467014.543111017, 17827365.303532742, 9533095.591844354];
    private const LG_Q4 = [2690.5301758708993, 639388.5654300093, 41355999.30241388, 1120872109.616148, 14886137286.788137, 101680358627.24382, 341747634550.73773, 446315818741.9713];
    private const LG_C = [-0.001910444077728, 0.0008417138778129501, -0.0005952379913043012, 0.0007936507935003503, -0.0027777777777776816, 0.08333333333333333, 0.0057083835261];
    // Rough estimate of the fourth root of logGamma_xBig
    private const LG_FRTBIG = 2.25E+76;
    private const PNT68 = 0.6796875;
    // Function cache for logGamma
    /** @var float */
    private static $logGammaCacheResult = 0.0;
    /** @var float */
    private static $logGammaCacheX = 0.0;
    /**
     * logGamma function.
     *
     * Original author was Jaco van Kooten. Ported to PHP by Paul Meagher.
     *
     * The natural logarithm of the gamma function. <br />
     * Based on public domain NETLIB (Fortran) code by W. J. Cody and L. Stoltz <br />
     * Applied Mathematics Division <br />
     * Argonne National Laboratory <br />
     * Argonne, IL 60439 <br />
     * <p>
     * References:
     * <ol>
     * <li>W. J. Cody and K. E. Hillstrom, 'Chebyshev Approximations for the Natural
     *     Logarithm of the Gamma Function,' Math. Comp. 21, 1967, pp. 198-203.</li>
     * <li>K. E. Hillstrom, ANL/AMD Program ANLC366S, DGAMMA/DLGAMA, May, 1969.</li>
     * <li>Hart, Et. Al., Computer Approximations, Wiley and sons, New York, 1968.</li>
     * </ol>
     * </p>
     * <p>
     * From the original documentation:
     * </p>
     * <p>
     * This routine calculates the LOG(GAMMA) function for a positive real argument X.
     * Computation is based on an algorithm outlined in references 1 and 2.
     * The program uses rational functions that theoretically approximate LOG(GAMMA)
     * to at least 18 significant decimal digits. The approximation for X > 12 is from
     * reference 3, while approximations for X < 12.0 are similar to those in reference
     * 1, but are unpublished. The accuracy achieved depends on the arithmetic system,
     * the compiler, the intrinsic functions, and proper selection of the
     * machine-dependent constants.
     * </p>
     * <p>
     * Error returns: <br />
     * The program returns the value XINF for X .LE. 0.0 or when overflow would occur.
     * The computation is believed to be free of underflow and overflow.
     * </p>
     *
     * @version 1.1
     *
     * @author Jaco van Kooten
     *
     * @return float MAX_VALUE for x < 0.0 or when overflow would occur, i.e. x > 2.55E305
     */
    public static function logGamma(float $x) : float
    {
        if ($x == self::$logGammaCacheX) {
            return self::$logGammaCacheResult;
        }
        $y = $x;
        if ($y > 0.0 && $y <= self::LOG_GAMMA_X_MAX_VALUE) {
            if ($y <= self::EPS) {
                $res = -\log($y);
            } elseif ($y <= 1.5) {
                $res = self::logGamma1($y);
            } elseif ($y <= 4.0) {
                $res = self::logGamma2($y);
            } elseif ($y <= 12.0) {
                $res = self::logGamma3($y);
            } else {
                $res = self::logGamma4($y);
            }
        } else {
            // --------------------------
            //    Return for bad arguments
            // --------------------------
            $res = self::MAX_VALUE;
        }
        // ------------------------------
        //    Final adjustments and return
        // ------------------------------
        self::$logGammaCacheX = $x;
        self::$logGammaCacheResult = $res;
        return $res;
    }
    private static function logGamma1(float $y) : float
    {
        // ---------------------
        //    EPS .LT. X .LE. 1.5
        // ---------------------
        if ($y < self::PNT68) {
            $corr = -\log($y);
            $xm1 = $y;
        } else {
            $corr = 0.0;
            $xm1 = $y - 1.0;
        }
        $xden = 1.0;
        $xnum = 0.0;
        if ($y <= 0.5 || $y >= self::PNT68) {
            for ($i = 0; $i < 8; ++$i) {
                $xnum = $xnum * $xm1 + self::LG_P1[$i];
                $xden = $xden * $xm1 + self::LG_Q1[$i];
            }
            return $corr + $xm1 * (self::LG_D1 + $xm1 * ($xnum / $xden));
        }
        $xm2 = $y - 1.0;
        for ($i = 0; $i < 8; ++$i) {
            $xnum = $xnum * $xm2 + self::LG_P2[$i];
            $xden = $xden * $xm2 + self::LG_Q2[$i];
        }
        return $corr + $xm2 * (self::LG_D2 + $xm2 * ($xnum / $xden));
    }
    private static function logGamma2(float $y) : float
    {
        // ---------------------
        //    1.5 .LT. X .LE. 4.0
        // ---------------------
        $xm2 = $y - 2.0;
        $xden = 1.0;
        $xnum = 0.0;
        for ($i = 0; $i < 8; ++$i) {
            $xnum = $xnum * $xm2 + self::LG_P2[$i];
            $xden = $xden * $xm2 + self::LG_Q2[$i];
        }
        return $xm2 * (self::LG_D2 + $xm2 * ($xnum / $xden));
    }
    protected static function logGamma3(float $y) : float
    {
        // ----------------------
        //    4.0 .LT. X .LE. 12.0
        // ----------------------
        $xm4 = $y - 4.0;
        $xden = -1.0;
        $xnum = 0.0;
        for ($i = 0; $i < 8; ++$i) {
            $xnum = $xnum * $xm4 + self::LG_P4[$i];
            $xden = $xden * $xm4 + self::LG_Q4[$i];
        }
        return self::LG_D4 + $xm4 * ($xnum / $xden);
    }
    protected static function logGamma4(float $y) : float
    {
        // ---------------------------------
        //    Evaluate for argument .GE. 12.0
        // ---------------------------------
        $res = 0.0;
        if ($y <= self::LG_FRTBIG) {
            $res = self::LG_C[6];
            $ysq = $y * $y;
            for ($i = 0; $i < 6; ++$i) {
                $res = $res / $ysq + self::LG_C[$i];
            }
            $res /= $y;
            $corr = \log($y);
            $res = $res + \log(self::SQRT2PI) - 0.5 * $corr;
            $res += $y * ($corr - 1.0);
        }
        return $res;
    }
}
