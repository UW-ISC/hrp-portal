<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Calculation\Engineering;

use WPDT\PhpOffice\PhpSpreadsheet\Calculation\ArrayEnabled;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Exception;
use WPDT\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
class ConvertUOM
{
    use ArrayEnabled;
    public const CATEGORY_WEIGHT_AND_MASS = 'Weight and Mass';
    public const CATEGORY_DISTANCE = 'Distance';
    public const CATEGORY_TIME = 'Time';
    public const CATEGORY_PRESSURE = 'Pressure';
    public const CATEGORY_FORCE = 'Force';
    public const CATEGORY_ENERGY = 'Energy';
    public const CATEGORY_POWER = 'Power';
    public const CATEGORY_MAGNETISM = 'Magnetism';
    public const CATEGORY_TEMPERATURE = 'Temperature';
    public const CATEGORY_VOLUME = 'Volume and Liquid Measure';
    public const CATEGORY_AREA = 'Area';
    public const CATEGORY_INFORMATION = 'Information';
    public const CATEGORY_SPEED = 'Speed';
    /**
     * Details of the Units of measure that can be used in CONVERTUOM().
     *
     * @var mixed[]
     */
    private static $conversionUnits = [
        // Weight and Mass
        'g' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'Gram', 'AllowPrefix' => \true],
        'sg' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'Slug', 'AllowPrefix' => \false],
        'lbm' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'Pound mass (avoirdupois)', 'AllowPrefix' => \false],
        'u' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'U (atomic mass unit)', 'AllowPrefix' => \true],
        'ozm' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'Ounce mass (avoirdupois)', 'AllowPrefix' => \false],
        'grain' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'Grain', 'AllowPrefix' => \false],
        'cwt' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'U.S. (short) hundredweight', 'AllowPrefix' => \false],
        'shweight' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'U.S. (short) hundredweight', 'AllowPrefix' => \false],
        'uk_cwt' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'Imperial hundredweight', 'AllowPrefix' => \false],
        'lcwt' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'Imperial hundredweight', 'AllowPrefix' => \false],
        'hweight' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'Imperial hundredweight', 'AllowPrefix' => \false],
        'stone' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'Stone', 'AllowPrefix' => \false],
        'ton' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'Ton', 'AllowPrefix' => \false],
        'uk_ton' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'Imperial ton', 'AllowPrefix' => \false],
        'LTON' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'Imperial ton', 'AllowPrefix' => \false],
        'brton' => ['Group' => self::CATEGORY_WEIGHT_AND_MASS, 'Unit Name' => 'Imperial ton', 'AllowPrefix' => \false],
        // Distance
        'm' => ['Group' => self::CATEGORY_DISTANCE, 'Unit Name' => 'Meter', 'AllowPrefix' => \true],
        'mi' => ['Group' => self::CATEGORY_DISTANCE, 'Unit Name' => 'Statute mile', 'AllowPrefix' => \false],
        'Nmi' => ['Group' => self::CATEGORY_DISTANCE, 'Unit Name' => 'Nautical mile', 'AllowPrefix' => \false],
        'in' => ['Group' => self::CATEGORY_DISTANCE, 'Unit Name' => 'Inch', 'AllowPrefix' => \false],
        'ft' => ['Group' => self::CATEGORY_DISTANCE, 'Unit Name' => 'Foot', 'AllowPrefix' => \false],
        'yd' => ['Group' => self::CATEGORY_DISTANCE, 'Unit Name' => 'Yard', 'AllowPrefix' => \false],
        'ang' => ['Group' => self::CATEGORY_DISTANCE, 'Unit Name' => 'Angstrom', 'AllowPrefix' => \true],
        'ell' => ['Group' => self::CATEGORY_DISTANCE, 'Unit Name' => 'Ell', 'AllowPrefix' => \false],
        'ly' => ['Group' => self::CATEGORY_DISTANCE, 'Unit Name' => 'Light Year', 'AllowPrefix' => \false],
        'parsec' => ['Group' => self::CATEGORY_DISTANCE, 'Unit Name' => 'Parsec', 'AllowPrefix' => \false],
        'pc' => ['Group' => self::CATEGORY_DISTANCE, 'Unit Name' => 'Parsec', 'AllowPrefix' => \false],
        'Pica' => ['Group' => self::CATEGORY_DISTANCE, 'Unit Name' => 'Pica (1/72 in)', 'AllowPrefix' => \false],
        'Picapt' => ['Group' => self::CATEGORY_DISTANCE, 'Unit Name' => 'Pica (1/72 in)', 'AllowPrefix' => \false],
        'pica' => ['Group' => self::CATEGORY_DISTANCE, 'Unit Name' => 'Pica (1/6 in)', 'AllowPrefix' => \false],
        'survey_mi' => ['Group' => self::CATEGORY_DISTANCE, 'Unit Name' => 'U.S survey mile (statute mile)', 'AllowPrefix' => \false],
        // Time
        'yr' => ['Group' => self::CATEGORY_TIME, 'Unit Name' => 'Year', 'AllowPrefix' => \false],
        'day' => ['Group' => self::CATEGORY_TIME, 'Unit Name' => 'Day', 'AllowPrefix' => \false],
        'd' => ['Group' => self::CATEGORY_TIME, 'Unit Name' => 'Day', 'AllowPrefix' => \false],
        'hr' => ['Group' => self::CATEGORY_TIME, 'Unit Name' => 'Hour', 'AllowPrefix' => \false],
        'mn' => ['Group' => self::CATEGORY_TIME, 'Unit Name' => 'Minute', 'AllowPrefix' => \false],
        'min' => ['Group' => self::CATEGORY_TIME, 'Unit Name' => 'Minute', 'AllowPrefix' => \false],
        'sec' => ['Group' => self::CATEGORY_TIME, 'Unit Name' => 'Second', 'AllowPrefix' => \true],
        's' => ['Group' => self::CATEGORY_TIME, 'Unit Name' => 'Second', 'AllowPrefix' => \true],
        // Pressure
        'Pa' => ['Group' => self::CATEGORY_PRESSURE, 'Unit Name' => 'Pascal', 'AllowPrefix' => \true],
        'p' => ['Group' => self::CATEGORY_PRESSURE, 'Unit Name' => 'Pascal', 'AllowPrefix' => \true],
        'atm' => ['Group' => self::CATEGORY_PRESSURE, 'Unit Name' => 'Atmosphere', 'AllowPrefix' => \true],
        'at' => ['Group' => self::CATEGORY_PRESSURE, 'Unit Name' => 'Atmosphere', 'AllowPrefix' => \true],
        'mmHg' => ['Group' => self::CATEGORY_PRESSURE, 'Unit Name' => 'mm of Mercury', 'AllowPrefix' => \true],
        'psi' => ['Group' => self::CATEGORY_PRESSURE, 'Unit Name' => 'PSI', 'AllowPrefix' => \true],
        'Torr' => ['Group' => self::CATEGORY_PRESSURE, 'Unit Name' => 'Torr', 'AllowPrefix' => \true],
        // Force
        'N' => ['Group' => self::CATEGORY_FORCE, 'Unit Name' => 'Newton', 'AllowPrefix' => \true],
        'dyn' => ['Group' => self::CATEGORY_FORCE, 'Unit Name' => 'Dyne', 'AllowPrefix' => \true],
        'dy' => ['Group' => self::CATEGORY_FORCE, 'Unit Name' => 'Dyne', 'AllowPrefix' => \true],
        'lbf' => ['Group' => self::CATEGORY_FORCE, 'Unit Name' => 'Pound force', 'AllowPrefix' => \false],
        'pond' => ['Group' => self::CATEGORY_FORCE, 'Unit Name' => 'Pond', 'AllowPrefix' => \true],
        // Energy
        'J' => ['Group' => self::CATEGORY_ENERGY, 'Unit Name' => 'Joule', 'AllowPrefix' => \true],
        'e' => ['Group' => self::CATEGORY_ENERGY, 'Unit Name' => 'Erg', 'AllowPrefix' => \true],
        'c' => ['Group' => self::CATEGORY_ENERGY, 'Unit Name' => 'Thermodynamic calorie', 'AllowPrefix' => \true],
        'cal' => ['Group' => self::CATEGORY_ENERGY, 'Unit Name' => 'IT calorie', 'AllowPrefix' => \true],
        'eV' => ['Group' => self::CATEGORY_ENERGY, 'Unit Name' => 'Electron volt', 'AllowPrefix' => \true],
        'ev' => ['Group' => self::CATEGORY_ENERGY, 'Unit Name' => 'Electron volt', 'AllowPrefix' => \true],
        'HPh' => ['Group' => self::CATEGORY_ENERGY, 'Unit Name' => 'Horsepower-hour', 'AllowPrefix' => \false],
        'hh' => ['Group' => self::CATEGORY_ENERGY, 'Unit Name' => 'Horsepower-hour', 'AllowPrefix' => \false],
        'Wh' => ['Group' => self::CATEGORY_ENERGY, 'Unit Name' => 'Watt-hour', 'AllowPrefix' => \true],
        'wh' => ['Group' => self::CATEGORY_ENERGY, 'Unit Name' => 'Watt-hour', 'AllowPrefix' => \true],
        'flb' => ['Group' => self::CATEGORY_ENERGY, 'Unit Name' => 'Foot-pound', 'AllowPrefix' => \false],
        'BTU' => ['Group' => self::CATEGORY_ENERGY, 'Unit Name' => 'BTU', 'AllowPrefix' => \false],
        'btu' => ['Group' => self::CATEGORY_ENERGY, 'Unit Name' => 'BTU', 'AllowPrefix' => \false],
        // Power
        'HP' => ['Group' => self::CATEGORY_POWER, 'Unit Name' => 'Horsepower', 'AllowPrefix' => \false],
        'h' => ['Group' => self::CATEGORY_POWER, 'Unit Name' => 'Horsepower', 'AllowPrefix' => \false],
        'W' => ['Group' => self::CATEGORY_POWER, 'Unit Name' => 'Watt', 'AllowPrefix' => \true],
        'w' => ['Group' => self::CATEGORY_POWER, 'Unit Name' => 'Watt', 'AllowPrefix' => \true],
        'PS' => ['Group' => self::CATEGORY_POWER, 'Unit Name' => 'Pferdestärke', 'AllowPrefix' => \false],
        // Magnetism
        'T' => ['Group' => self::CATEGORY_MAGNETISM, 'Unit Name' => 'Tesla', 'AllowPrefix' => \true],
        'ga' => ['Group' => self::CATEGORY_MAGNETISM, 'Unit Name' => 'Gauss', 'AllowPrefix' => \true],
        // Temperature
        'C' => ['Group' => self::CATEGORY_TEMPERATURE, 'Unit Name' => 'Degrees Celsius', 'AllowPrefix' => \false],
        'cel' => ['Group' => self::CATEGORY_TEMPERATURE, 'Unit Name' => 'Degrees Celsius', 'AllowPrefix' => \false],
        'F' => ['Group' => self::CATEGORY_TEMPERATURE, 'Unit Name' => 'Degrees Fahrenheit', 'AllowPrefix' => \false],
        'fah' => ['Group' => self::CATEGORY_TEMPERATURE, 'Unit Name' => 'Degrees Fahrenheit', 'AllowPrefix' => \false],
        'K' => ['Group' => self::CATEGORY_TEMPERATURE, 'Unit Name' => 'Kelvin', 'AllowPrefix' => \false],
        'kel' => ['Group' => self::CATEGORY_TEMPERATURE, 'Unit Name' => 'Kelvin', 'AllowPrefix' => \false],
        'Rank' => ['Group' => self::CATEGORY_TEMPERATURE, 'Unit Name' => 'Degrees Rankine', 'AllowPrefix' => \false],
        'Reau' => ['Group' => self::CATEGORY_TEMPERATURE, 'Unit Name' => 'Degrees Réaumur', 'AllowPrefix' => \false],
        // Volume
        'l' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Litre', 'AllowPrefix' => \true],
        'L' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Litre', 'AllowPrefix' => \true],
        'lt' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Litre', 'AllowPrefix' => \true],
        'tsp' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Teaspoon', 'AllowPrefix' => \false],
        'tspm' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Modern Teaspoon', 'AllowPrefix' => \false],
        'tbs' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Tablespoon', 'AllowPrefix' => \false],
        'oz' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Fluid Ounce', 'AllowPrefix' => \false],
        'cup' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cup', 'AllowPrefix' => \false],
        'pt' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'U.S. Pint', 'AllowPrefix' => \false],
        'us_pt' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'U.S. Pint', 'AllowPrefix' => \false],
        'uk_pt' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'U.K. Pint', 'AllowPrefix' => \false],
        'qt' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Quart', 'AllowPrefix' => \false],
        'uk_qt' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Imperial Quart (UK)', 'AllowPrefix' => \false],
        'gal' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Gallon', 'AllowPrefix' => \false],
        'uk_gal' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Imperial Gallon (UK)', 'AllowPrefix' => \false],
        'ang3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Angstrom', 'AllowPrefix' => \true],
        'ang^3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Angstrom', 'AllowPrefix' => \true],
        'barrel' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'US Oil Barrel', 'AllowPrefix' => \false],
        'bushel' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'US Bushel', 'AllowPrefix' => \false],
        'in3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Inch', 'AllowPrefix' => \false],
        'in^3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Inch', 'AllowPrefix' => \false],
        'ft3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Foot', 'AllowPrefix' => \false],
        'ft^3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Foot', 'AllowPrefix' => \false],
        'ly3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Light Year', 'AllowPrefix' => \false],
        'ly^3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Light Year', 'AllowPrefix' => \false],
        'm3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Meter', 'AllowPrefix' => \true],
        'm^3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Meter', 'AllowPrefix' => \true],
        'mi3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Mile', 'AllowPrefix' => \false],
        'mi^3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Mile', 'AllowPrefix' => \false],
        'yd3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Yard', 'AllowPrefix' => \false],
        'yd^3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Yard', 'AllowPrefix' => \false],
        'Nmi3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Nautical Mile', 'AllowPrefix' => \false],
        'Nmi^3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Nautical Mile', 'AllowPrefix' => \false],
        'Pica3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Pica', 'AllowPrefix' => \false],
        'Pica^3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Pica', 'AllowPrefix' => \false],
        'Picapt3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Pica', 'AllowPrefix' => \false],
        'Picapt^3' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Cubic Pica', 'AllowPrefix' => \false],
        'GRT' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Gross Registered Ton', 'AllowPrefix' => \false],
        'regton' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Gross Registered Ton', 'AllowPrefix' => \false],
        'MTON' => ['Group' => self::CATEGORY_VOLUME, 'Unit Name' => 'Measurement Ton (Freight Ton)', 'AllowPrefix' => \false],
        // Area
        'ha' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Hectare', 'AllowPrefix' => \true],
        'uk_acre' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'International Acre', 'AllowPrefix' => \false],
        'us_acre' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'US Survey/Statute Acre', 'AllowPrefix' => \false],
        'ang2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Angstrom', 'AllowPrefix' => \true],
        'ang^2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Angstrom', 'AllowPrefix' => \true],
        'ar' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Are', 'AllowPrefix' => \true],
        'ft2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Feet', 'AllowPrefix' => \false],
        'ft^2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Feet', 'AllowPrefix' => \false],
        'in2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Inches', 'AllowPrefix' => \false],
        'in^2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Inches', 'AllowPrefix' => \false],
        'ly2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Light Years', 'AllowPrefix' => \false],
        'ly^2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Light Years', 'AllowPrefix' => \false],
        'm2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Meters', 'AllowPrefix' => \true],
        'm^2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Meters', 'AllowPrefix' => \true],
        'Morgen' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Morgen', 'AllowPrefix' => \false],
        'mi2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Miles', 'AllowPrefix' => \false],
        'mi^2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Miles', 'AllowPrefix' => \false],
        'Nmi2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Nautical Miles', 'AllowPrefix' => \false],
        'Nmi^2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Nautical Miles', 'AllowPrefix' => \false],
        'Pica2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Pica', 'AllowPrefix' => \false],
        'Pica^2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Pica', 'AllowPrefix' => \false],
        'Picapt2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Pica', 'AllowPrefix' => \false],
        'Picapt^2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Pica', 'AllowPrefix' => \false],
        'yd2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Yards', 'AllowPrefix' => \false],
        'yd^2' => ['Group' => self::CATEGORY_AREA, 'Unit Name' => 'Square Yards', 'AllowPrefix' => \false],
        // Information
        'byte' => ['Group' => self::CATEGORY_INFORMATION, 'Unit Name' => 'Byte', 'AllowPrefix' => \true],
        'bit' => ['Group' => self::CATEGORY_INFORMATION, 'Unit Name' => 'Bit', 'AllowPrefix' => \true],
        // Speed
        'm/s' => ['Group' => self::CATEGORY_SPEED, 'Unit Name' => 'Meters per second', 'AllowPrefix' => \true],
        'm/sec' => ['Group' => self::CATEGORY_SPEED, 'Unit Name' => 'Meters per second', 'AllowPrefix' => \true],
        'm/h' => ['Group' => self::CATEGORY_SPEED, 'Unit Name' => 'Meters per hour', 'AllowPrefix' => \true],
        'm/hr' => ['Group' => self::CATEGORY_SPEED, 'Unit Name' => 'Meters per hour', 'AllowPrefix' => \true],
        'mph' => ['Group' => self::CATEGORY_SPEED, 'Unit Name' => 'Miles per hour', 'AllowPrefix' => \false],
        'admkn' => ['Group' => self::CATEGORY_SPEED, 'Unit Name' => 'Admiralty Knot', 'AllowPrefix' => \false],
        'kn' => ['Group' => self::CATEGORY_SPEED, 'Unit Name' => 'Knot', 'AllowPrefix' => \false],
    ];
    /**
     * Details of the Multiplier prefixes that can be used with Units of Measure in CONVERTUOM().
     *
     * @var mixed[]
     */
    private static $conversionMultipliers = ['Y' => ['multiplier' => 1.0E+24, 'name' => 'yotta'], 'Z' => ['multiplier' => 1.0E+21, 'name' => 'zetta'], 'E' => ['multiplier' => 1.0E+18, 'name' => 'exa'], 'P' => ['multiplier' => 1000000000000000.0, 'name' => 'peta'], 'T' => ['multiplier' => 1000000000000.0, 'name' => 'tera'], 'G' => ['multiplier' => 1000000000.0, 'name' => 'giga'], 'M' => ['multiplier' => 1000000.0, 'name' => 'mega'], 'k' => ['multiplier' => 1000.0, 'name' => 'kilo'], 'h' => ['multiplier' => 100.0, 'name' => 'hecto'], 'e' => ['multiplier' => 10.0, 'name' => 'dekao'], 'da' => ['multiplier' => 10.0, 'name' => 'dekao'], 'd' => ['multiplier' => 0.1, 'name' => 'deci'], 'c' => ['multiplier' => 0.01, 'name' => 'centi'], 'm' => ['multiplier' => 0.001, 'name' => 'milli'], 'u' => ['multiplier' => 1.0E-6, 'name' => 'micro'], 'n' => ['multiplier' => 1.0E-9, 'name' => 'nano'], 'p' => ['multiplier' => 1.0E-12, 'name' => 'pico'], 'f' => ['multiplier' => 1.0E-15, 'name' => 'femto'], 'a' => ['multiplier' => 1.0E-18, 'name' => 'atto'], 'z' => ['multiplier' => 9.999999999999999E-22, 'name' => 'zepto'], 'y' => ['multiplier' => 9.999999999999999E-25, 'name' => 'yocto']];
    /**
     * Details of the Multiplier prefixes that can be used with Units of Measure in CONVERTUOM().
     *
     * @var mixed[]
     */
    private static $binaryConversionMultipliers = ['Yi' => ['multiplier' => 2 ** 80, 'name' => 'yobi'], 'Zi' => ['multiplier' => 2 ** 70, 'name' => 'zebi'], 'Ei' => ['multiplier' => 2 ** 60, 'name' => 'exbi'], 'Pi' => ['multiplier' => 2 ** 50, 'name' => 'pebi'], 'Ti' => ['multiplier' => 2 ** 40, 'name' => 'tebi'], 'Gi' => ['multiplier' => 2 ** 30, 'name' => 'gibi'], 'Mi' => ['multiplier' => 2 ** 20, 'name' => 'mebi'], 'ki' => ['multiplier' => 2 ** 10, 'name' => 'kibi']];
    /**
     * Details of the Units of measure conversion factors, organised by group.
     *
     * @var mixed[]
     */
    private static $unitConversions = [
        // Conversion uses gram (g) as an intermediate unit
        self::CATEGORY_WEIGHT_AND_MASS => ['g' => 1.0, 'sg' => 6.852176585679181E-5, 'lbm' => 0.00220462262184878, 'u' => 6.02214179421676E+23, 'ozm' => 0.0352739619495804, 'grain' => 15.4323583529414, 'cwt' => 2.20462262184878E-5, 'shweight' => 2.20462262184878E-5, 'uk_cwt' => 1.96841305522212E-5, 'lcwt' => 1.96841305522212E-5, 'hweight' => 1.96841305522212E-5, 'stone' => 0.00015747304441777, 'ton' => 1.10231131092439E-6, 'uk_ton' => 9.842065276110609E-7, 'LTON' => 9.842065276110609E-7, 'brton' => 9.842065276110609E-7],
        // Conversion uses meter (m) as an intermediate unit
        self::CATEGORY_DISTANCE => ['m' => 1.0, 'mi' => 0.000621371192237334, 'Nmi' => 0.000539956803455724, 'in' => 39.3700787401575, 'ft' => 3.28083989501312, 'yd' => 1.09361329833771, 'ang' => 10000000000.0, 'ell' => 0.874890638670166, 'ly' => 1.05700083402462E-16, 'parsec' => 3.24077928966473E-17, 'pc' => 3.24077928966473E-17, 'Pica' => 2834.64566929134, 'Picapt' => 2834.64566929134, 'pica' => 236.220472440945, 'survey_mi' => 0.00062136994949495],
        // Conversion uses second (s) as an intermediate unit
        self::CATEGORY_TIME => ['yr' => 3.16880878140289E-8, 'day' => 1.15740740740741E-5, 'd' => 1.15740740740741E-5, 'hr' => 0.000277777777777778, 'mn' => 0.0166666666666667, 'min' => 0.0166666666666667, 'sec' => 1.0, 's' => 1.0],
        // Conversion uses Pascal (Pa) as an intermediate unit
        self::CATEGORY_PRESSURE => ['Pa' => 1.0, 'p' => 1.0, 'atm' => 9.86923266716013E-6, 'at' => 9.86923266716013E-6, 'mmHg' => 0.00750063755419211, 'psi' => 0.000145037737730209, 'Torr' => 0.0075006168270417],
        // Conversion uses Newton (N) as an intermediate unit
        self::CATEGORY_FORCE => ['N' => 1.0, 'dyn' => 100000.0, 'dy' => 100000.0, 'lbf' => 0.224808923655339, 'pond' => 101.971621297793],
        // Conversion uses Joule (J) as an intermediate unit
        self::CATEGORY_ENERGY => ['J' => 1.0, 'e' => 9999995.193432311, 'c' => 0.239006249473467, 'cal' => 0.238846190642017, 'eV' => 6.241457E+18, 'ev' => 6.241457E+18, 'HPh' => 3.72506430801E-7, 'hh' => 3.72506430801E-7, 'Wh' => 0.000277777916238711, 'wh' => 0.000277777916238711, 'flb' => 23.7304222192651, 'BTU' => 0.000947815067349015, 'btu' => 0.000947815067349015],
        // Conversion uses Horsepower (HP) as an intermediate unit
        self::CATEGORY_POWER => ['HP' => 1.0, 'h' => 1.0, 'W' => 745.69987158227, 'w' => 745.69987158227, 'PS' => 1.013869665424],
        // Conversion uses Tesla (T) as an intermediate unit
        self::CATEGORY_MAGNETISM => ['T' => 1.0, 'ga' => 10000.0],
        // Conversion uses litre (l) as an intermediate unit
        self::CATEGORY_VOLUME => ['l' => 1.0, 'L' => 1.0, 'lt' => 1.0, 'tsp' => 202.884136211058, 'tspm' => 200.0, 'tbs' => 67.628045403686, 'oz' => 33.814022701843, 'cup' => 4.22675283773038, 'pt' => 2.11337641886519, 'us_pt' => 2.11337641886519, 'uk_pt' => 1.7597539863927, 'qt' => 1.05668820943259, 'uk_qt' => 0.879876993196351, 'gal' => 0.264172052358148, 'uk_gal' => 0.219969248299088, 'ang3' => 1.0E+27, 'ang^3' => 1.0E+27, 'barrel' => 0.00628981077043211, 'bushel' => 0.0283775932584017, 'in3' => 61.0237440947323, 'in^3' => 61.0237440947323, 'ft3' => 0.0353146667214886, 'ft^3' => 0.0353146667214886, 'ly3' => 1.18093498844171E-51, 'ly^3' => 1.18093498844171E-51, 'm3' => 0.001, 'm^3' => 0.001, 'mi3' => 2.39912758578928E-13, 'mi^3' => 2.39912758578928E-13, 'yd3' => 0.00130795061931439, 'yd^3' => 0.00130795061931439, 'Nmi3' => 1.57426214685811E-13, 'Nmi^3' => 1.57426214685811E-13, 'Pica3' => 22776990.4358706, 'Pica^3' => 22776990.4358706, 'Picapt3' => 22776990.4358706, 'Picapt^3' => 22776990.4358706, 'GRT' => 0.000353146667214886, 'regton' => 0.000353146667214886, 'MTON' => 0.000882866668037215],
        // Conversion uses hectare (ha) as an intermediate unit
        self::CATEGORY_AREA => ['ha' => 1.0, 'uk_acre' => 2.47105381467165, 'us_acre' => 2.47104393046628, 'ang2' => 1.0E+24, 'ang^2' => 1.0E+24, 'ar' => 100.0, 'ft2' => 107639.104167097, 'ft^2' => 107639.104167097, 'in2' => 15500031.000062, 'in^2' => 15500031.000062, 'ly2' => 1.11725076312873E-28, 'ly^2' => 1.11725076312873E-28, 'm2' => 10000.0, 'm^2' => 10000.0, 'Morgen' => 4.0, 'mi2' => 0.00386102158542446, 'mi^2' => 0.00386102158542446, 'Nmi2' => 0.00291553349598123, 'Nmi^2' => 0.00291553349598123, 'Pica2' => 80352160704.3214, 'Pica^2' => 80352160704.3214, 'Picapt2' => 80352160704.3214, 'Picapt^2' => 80352160704.3214, 'yd2' => 11959.9004630108, 'yd^2' => 11959.9004630108],
        // Conversion uses bit (bit) as an intermediate unit
        self::CATEGORY_INFORMATION => ['bit' => 1.0, 'byte' => 0.125],
        // Conversion uses Meters per Second (m/s) as an intermediate unit
        self::CATEGORY_SPEED => ['m/s' => 1.0, 'm/sec' => 1.0, 'm/h' => 3600.0, 'm/hr' => 3600.0, 'mph' => 2.2369362920544, 'admkn' => 1.94260256941567, 'kn' => 1.9438444924406],
    ];
    /**
     *    getConversionGroups
     * Returns a list of the different conversion groups for UOM conversions.
     *
     * @return array
     */
    public static function getConversionCategories()
    {
        $conversionGroups = [];
        foreach (self::$conversionUnits as $conversionUnit) {
            $conversionGroups[] = $conversionUnit['Group'];
        }
        return \array_merge(\array_unique($conversionGroups));
    }
    /**
     *    getConversionGroupUnits
     * Returns an array of units of measure, for a specified conversion group, or for all groups.
     *
     * @param string $category The group whose units of measure you want to retrieve
     *
     * @return array
     */
    public static function getConversionCategoryUnits($category = null)
    {
        $conversionGroups = [];
        foreach (self::$conversionUnits as $conversionUnit => $conversionGroup) {
            if ($category === null || $conversionGroup['Group'] == $category) {
                $conversionGroups[$conversionGroup['Group']][] = $conversionUnit;
            }
        }
        return $conversionGroups;
    }
    /**
     * getConversionGroupUnitDetails.
     *
     * @param string $category The group whose units of measure you want to retrieve
     *
     * @return array
     */
    public static function getConversionCategoryUnitDetails($category = null)
    {
        $conversionGroups = [];
        foreach (self::$conversionUnits as $conversionUnit => $conversionGroup) {
            if ($category === null || $conversionGroup['Group'] == $category) {
                $conversionGroups[$conversionGroup['Group']][] = ['unit' => $conversionUnit, 'description' => $conversionGroup['Unit Name']];
            }
        }
        return $conversionGroups;
    }
    /**
     *    getConversionMultipliers
     * Returns an array of the Multiplier prefixes that can be used with Units of Measure in CONVERTUOM().
     *
     * @return mixed[]
     */
    public static function getConversionMultipliers()
    {
        return self::$conversionMultipliers;
    }
    /**
     *    getBinaryConversionMultipliers
     * Returns an array of the additional Multiplier prefixes that can be used with Information Units of Measure in CONVERTUOM().
     *
     * @return mixed[]
     */
    public static function getBinaryConversionMultipliers()
    {
        return self::$binaryConversionMultipliers;
    }
    /**
     * CONVERT.
     *
     * Converts a number from one measurement system to another.
     *    For example, CONVERT can translate a table of distances in miles to a table of distances
     * in kilometers.
     *
     *    Excel Function:
     *        CONVERT(value,fromUOM,toUOM)
     *
     * @param array|float|int|string $value the value in fromUOM to convert
     *                      Or can be an array of values
     * @param array|string $fromUOM the units for value
     *                      Or can be an array of values
     * @param array|string $toUOM the units for the result
     *                      Or can be an array of values
     *
     * @return array|float|string Result, or a string containing an error
     *         If an array of numbers is passed as an argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function CONVERT($value, $fromUOM, $toUOM)
    {
        if (\is_array($value) || \is_array($fromUOM) || \is_array($toUOM)) {
            return self::evaluateArrayArguments([self::class, __FUNCTION__], $value, $fromUOM, $toUOM);
        }
        if (!\is_numeric($value)) {
            return ExcelError::VALUE();
        }
        try {
            [$fromUOM, $fromCategory, $fromMultiplier] = self::getUOMDetails($fromUOM);
            [$toUOM, $toCategory, $toMultiplier] = self::getUOMDetails($toUOM);
        } catch (Exception $e) {
            return ExcelError::NA();
        }
        if ($fromCategory !== $toCategory) {
            return ExcelError::NA();
        }
        // @var float $value
        $value *= $fromMultiplier;
        if ($fromUOM === $toUOM && $fromMultiplier === $toMultiplier) {
            //    We've already factored $fromMultiplier into the value, so we need
            //        to reverse it again
            return $value / $fromMultiplier;
        } elseif ($fromUOM === $toUOM) {
            return $value / $toMultiplier;
        } elseif ($fromCategory === self::CATEGORY_TEMPERATURE) {
            return self::convertTemperature(
                $fromUOM,
                $toUOM,
                /** @scrutinizer ignore-type */
                $value
            );
        }
        $baseValue = $value * (1.0 / self::$unitConversions[$fromCategory][$fromUOM]);
        return $baseValue * self::$unitConversions[$fromCategory][$toUOM] / $toMultiplier;
    }
    private static function getUOMDetails(string $uom) : array
    {
        if (isset(self::$conversionUnits[$uom])) {
            $unitCategory = self::$conversionUnits[$uom]['Group'];
            return [$uom, $unitCategory, 1.0];
        }
        // Check 1-character standard metric multiplier prefixes
        $multiplierType = \substr($uom, 0, 1);
        $uom = \substr($uom, 1);
        if (isset(self::$conversionUnits[$uom], self::$conversionMultipliers[$multiplierType])) {
            if (self::$conversionUnits[$uom]['AllowPrefix'] === \false) {
                throw new Exception('Prefix not allowed for UoM');
            }
            $unitCategory = self::$conversionUnits[$uom]['Group'];
            return [$uom, $unitCategory, self::$conversionMultipliers[$multiplierType]['multiplier']];
        }
        $multiplierType .= \substr($uom, 0, 1);
        $uom = \substr($uom, 1);
        // Check 2-character standard metric multiplier prefixes
        if (isset(self::$conversionUnits[$uom], self::$conversionMultipliers[$multiplierType])) {
            if (self::$conversionUnits[$uom]['AllowPrefix'] === \false) {
                throw new Exception('Prefix not allowed for UoM');
            }
            $unitCategory = self::$conversionUnits[$uom]['Group'];
            return [$uom, $unitCategory, self::$conversionMultipliers[$multiplierType]['multiplier']];
        }
        // Check 2-character binary multiplier prefixes
        if (isset(self::$conversionUnits[$uom], self::$binaryConversionMultipliers[$multiplierType])) {
            if (self::$conversionUnits[$uom]['AllowPrefix'] === \false) {
                throw new Exception('Prefix not allowed for UoM');
            }
            $unitCategory = self::$conversionUnits[$uom]['Group'];
            if ($unitCategory !== 'Information') {
                throw new Exception('Binary Prefix is only allowed for Information UoM');
            }
            return [$uom, $unitCategory, self::$binaryConversionMultipliers[$multiplierType]['multiplier']];
        }
        throw new Exception('UoM Not Found');
    }
    /**
     * @param float|int $value
     *
     * @return float|int
     */
    protected static function convertTemperature(string $fromUOM, string $toUOM, $value)
    {
        $fromUOM = self::resolveTemperatureSynonyms($fromUOM);
        $toUOM = self::resolveTemperatureSynonyms($toUOM);
        if ($fromUOM === $toUOM) {
            return $value;
        }
        // Convert to Kelvin
        switch ($fromUOM) {
            case 'F':
                $value = ($value - 32) / 1.8 + 273.15;
                break;
            case 'C':
                $value += 273.15;
                break;
            case 'Rank':
                $value /= 1.8;
                break;
            case 'Reau':
                $value = $value * 1.25 + 273.15;
                break;
        }
        // Convert from Kelvin
        switch ($toUOM) {
            case 'F':
                $value = ($value - 273.15) * 1.8 + 32.0;
                break;
            case 'C':
                $value -= 273.15;
                break;
            case 'Rank':
                $value *= 1.8;
                break;
            case 'Reau':
                $value = ($value - 273.15) * 0.8;
                break;
        }
        return $value;
    }
    private static function resolveTemperatureSynonyms(string $uom) : string
    {
        switch ($uom) {
            case 'fah':
                return 'F';
            case 'cel':
                return 'C';
            case 'kel':
                return 'K';
        }
        return $uom;
    }
}
