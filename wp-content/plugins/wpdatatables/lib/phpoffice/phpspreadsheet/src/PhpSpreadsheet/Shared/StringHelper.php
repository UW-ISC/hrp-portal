<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Shared;

class StringHelper
{
    /**
     * Control characters array.
     *
     * @var string[]
     */
    private static $controlCharacters = [];
    /**
     * SYLK Characters array.
     *
     * @var array
     */
    private static $SYLKCharacters = [];
    /**
     * Decimal separator.
     *
     * @var ?string
     */
    private static $decimalSeparator;
    /**
     * Thousands separator.
     *
     * @var ?string
     */
    private static $thousandsSeparator;
    /**
     * Currency code.
     *
     * @var string
     */
    private static $currencyCode;
    /**
     * Is iconv extension avalable?
     *
     * @var ?bool
     */
    private static $isIconvEnabled;
    /**
     * iconv options.
     *
     * @var string
     */
    private static $iconvOptions = '//IGNORE//TRANSLIT';
    /**
     * Build control characters array.
     */
    private static function buildControlCharacters() : void
    {
        for ($i = 0; $i <= 31; ++$i) {
            if ($i != 9 && $i != 10 && $i != 13) {
                $find = '_x' . \sprintf('%04s', \strtoupper(\dechex($i))) . '_';
                $replace = \chr($i);
                self::$controlCharacters[$find] = $replace;
            }
        }
    }
    /**
     * Build SYLK characters array.
     */
    private static function buildSYLKCharacters() : void
    {
        self::$SYLKCharacters = [
            "\x1b 0" => \chr(0),
            "\x1b 1" => \chr(1),
            "\x1b 2" => \chr(2),
            "\x1b 3" => \chr(3),
            "\x1b 4" => \chr(4),
            "\x1b 5" => \chr(5),
            "\x1b 6" => \chr(6),
            "\x1b 7" => \chr(7),
            "\x1b 8" => \chr(8),
            "\x1b 9" => \chr(9),
            "\x1b :" => \chr(10),
            "\x1b ;" => \chr(11),
            "\x1b <" => \chr(12),
            "\x1b =" => \chr(13),
            "\x1b >" => \chr(14),
            "\x1b ?" => \chr(15),
            "\x1b!0" => \chr(16),
            "\x1b!1" => \chr(17),
            "\x1b!2" => \chr(18),
            "\x1b!3" => \chr(19),
            "\x1b!4" => \chr(20),
            "\x1b!5" => \chr(21),
            "\x1b!6" => \chr(22),
            "\x1b!7" => \chr(23),
            "\x1b!8" => \chr(24),
            "\x1b!9" => \chr(25),
            "\x1b!:" => \chr(26),
            "\x1b!;" => \chr(27),
            "\x1b!<" => \chr(28),
            "\x1b!=" => \chr(29),
            "\x1b!>" => \chr(30),
            "\x1b!?" => \chr(31),
            "\x1b'?" => \chr(127),
            "\x1b(0" => '€',
            // 128 in CP1252
            "\x1b(2" => '‚',
            // 130 in CP1252
            "\x1b(3" => 'ƒ',
            // 131 in CP1252
            "\x1b(4" => '„',
            // 132 in CP1252
            "\x1b(5" => '…',
            // 133 in CP1252
            "\x1b(6" => '†',
            // 134 in CP1252
            "\x1b(7" => '‡',
            // 135 in CP1252
            "\x1b(8" => 'ˆ',
            // 136 in CP1252
            "\x1b(9" => '‰',
            // 137 in CP1252
            "\x1b(:" => 'Š',
            // 138 in CP1252
            "\x1b(;" => '‹',
            // 139 in CP1252
            "\x1bNj" => 'Œ',
            // 140 in CP1252
            "\x1b(>" => 'Ž',
            // 142 in CP1252
            "\x1b)1" => '‘',
            // 145 in CP1252
            "\x1b)2" => '’',
            // 146 in CP1252
            "\x1b)3" => '“',
            // 147 in CP1252
            "\x1b)4" => '”',
            // 148 in CP1252
            "\x1b)5" => '•',
            // 149 in CP1252
            "\x1b)6" => '–',
            // 150 in CP1252
            "\x1b)7" => '—',
            // 151 in CP1252
            "\x1b)8" => '˜',
            // 152 in CP1252
            "\x1b)9" => '™',
            // 153 in CP1252
            "\x1b):" => 'š',
            // 154 in CP1252
            "\x1b);" => '›',
            // 155 in CP1252
            "\x1bNz" => 'œ',
            // 156 in CP1252
            "\x1b)>" => 'ž',
            // 158 in CP1252
            "\x1b)?" => 'Ÿ',
            // 159 in CP1252
            "\x1b*0" => ' ',
            // 160 in CP1252
            "\x1bN!" => '¡',
            // 161 in CP1252
            "\x1bN\"" => '¢',
            // 162 in CP1252
            "\x1bN#" => '£',
            // 163 in CP1252
            "\x1bN(" => '¤',
            // 164 in CP1252
            "\x1bN%" => '¥',
            // 165 in CP1252
            "\x1b*6" => '¦',
            // 166 in CP1252
            "\x1bN'" => '§',
            // 167 in CP1252
            "\x1bNH " => '¨',
            // 168 in CP1252
            "\x1bNS" => '©',
            // 169 in CP1252
            "\x1bNc" => 'ª',
            // 170 in CP1252
            "\x1bN+" => '«',
            // 171 in CP1252
            "\x1b*<" => '¬',
            // 172 in CP1252
            "\x1b*=" => '­',
            // 173 in CP1252
            "\x1bNR" => '®',
            // 174 in CP1252
            "\x1b*?" => '¯',
            // 175 in CP1252
            "\x1bN0" => '°',
            // 176 in CP1252
            "\x1bN1" => '±',
            // 177 in CP1252
            "\x1bN2" => '²',
            // 178 in CP1252
            "\x1bN3" => '³',
            // 179 in CP1252
            "\x1bNB " => '´',
            // 180 in CP1252
            "\x1bN5" => 'µ',
            // 181 in CP1252
            "\x1bN6" => '¶',
            // 182 in CP1252
            "\x1bN7" => '·',
            // 183 in CP1252
            "\x1b+8" => '¸',
            // 184 in CP1252
            "\x1bNQ" => '¹',
            // 185 in CP1252
            "\x1bNk" => 'º',
            // 186 in CP1252
            "\x1bN;" => '»',
            // 187 in CP1252
            "\x1bN<" => '¼',
            // 188 in CP1252
            "\x1bN=" => '½',
            // 189 in CP1252
            "\x1bN>" => '¾',
            // 190 in CP1252
            "\x1bN?" => '¿',
            // 191 in CP1252
            "\x1bNAA" => 'À',
            // 192 in CP1252
            "\x1bNBA" => 'Á',
            // 193 in CP1252
            "\x1bNCA" => 'Â',
            // 194 in CP1252
            "\x1bNDA" => 'Ã',
            // 195 in CP1252
            "\x1bNHA" => 'Ä',
            // 196 in CP1252
            "\x1bNJA" => 'Å',
            // 197 in CP1252
            "\x1bNa" => 'Æ',
            // 198 in CP1252
            "\x1bNKC" => 'Ç',
            // 199 in CP1252
            "\x1bNAE" => 'È',
            // 200 in CP1252
            "\x1bNBE" => 'É',
            // 201 in CP1252
            "\x1bNCE" => 'Ê',
            // 202 in CP1252
            "\x1bNHE" => 'Ë',
            // 203 in CP1252
            "\x1bNAI" => 'Ì',
            // 204 in CP1252
            "\x1bNBI" => 'Í',
            // 205 in CP1252
            "\x1bNCI" => 'Î',
            // 206 in CP1252
            "\x1bNHI" => 'Ï',
            // 207 in CP1252
            "\x1bNb" => 'Ð',
            // 208 in CP1252
            "\x1bNDN" => 'Ñ',
            // 209 in CP1252
            "\x1bNAO" => 'Ò',
            // 210 in CP1252
            "\x1bNBO" => 'Ó',
            // 211 in CP1252
            "\x1bNCO" => 'Ô',
            // 212 in CP1252
            "\x1bNDO" => 'Õ',
            // 213 in CP1252
            "\x1bNHO" => 'Ö',
            // 214 in CP1252
            "\x1b-7" => '×',
            // 215 in CP1252
            "\x1bNi" => 'Ø',
            // 216 in CP1252
            "\x1bNAU" => 'Ù',
            // 217 in CP1252
            "\x1bNBU" => 'Ú',
            // 218 in CP1252
            "\x1bNCU" => 'Û',
            // 219 in CP1252
            "\x1bNHU" => 'Ü',
            // 220 in CP1252
            "\x1b-=" => 'Ý',
            // 221 in CP1252
            "\x1bNl" => 'Þ',
            // 222 in CP1252
            "\x1bN{" => 'ß',
            // 223 in CP1252
            "\x1bNAa" => 'à',
            // 224 in CP1252
            "\x1bNBa" => 'á',
            // 225 in CP1252
            "\x1bNCa" => 'â',
            // 226 in CP1252
            "\x1bNDa" => 'ã',
            // 227 in CP1252
            "\x1bNHa" => 'ä',
            // 228 in CP1252
            "\x1bNJa" => 'å',
            // 229 in CP1252
            "\x1bNq" => 'æ',
            // 230 in CP1252
            "\x1bNKc" => 'ç',
            // 231 in CP1252
            "\x1bNAe" => 'è',
            // 232 in CP1252
            "\x1bNBe" => 'é',
            // 233 in CP1252
            "\x1bNCe" => 'ê',
            // 234 in CP1252
            "\x1bNHe" => 'ë',
            // 235 in CP1252
            "\x1bNAi" => 'ì',
            // 236 in CP1252
            "\x1bNBi" => 'í',
            // 237 in CP1252
            "\x1bNCi" => 'î',
            // 238 in CP1252
            "\x1bNHi" => 'ï',
            // 239 in CP1252
            "\x1bNs" => 'ð',
            // 240 in CP1252
            "\x1bNDn" => 'ñ',
            // 241 in CP1252
            "\x1bNAo" => 'ò',
            // 242 in CP1252
            "\x1bNBo" => 'ó',
            // 243 in CP1252
            "\x1bNCo" => 'ô',
            // 244 in CP1252
            "\x1bNDo" => 'õ',
            // 245 in CP1252
            "\x1bNHo" => 'ö',
            // 246 in CP1252
            "\x1b/7" => '÷',
            // 247 in CP1252
            "\x1bNy" => 'ø',
            // 248 in CP1252
            "\x1bNAu" => 'ù',
            // 249 in CP1252
            "\x1bNBu" => 'ú',
            // 250 in CP1252
            "\x1bNCu" => 'û',
            // 251 in CP1252
            "\x1bNHu" => 'ü',
            // 252 in CP1252
            "\x1b/=" => 'ý',
            // 253 in CP1252
            "\x1bN|" => 'þ',
            // 254 in CP1252
            "\x1bNHy" => 'ÿ',
        ];
    }
    /**
     * Get whether iconv extension is available.
     *
     * @return bool
     */
    public static function getIsIconvEnabled()
    {
        if (isset(self::$isIconvEnabled)) {
            return self::$isIconvEnabled;
        }
        // Assume no problems with iconv
        self::$isIconvEnabled = \true;
        // Fail if iconv doesn't exist
        if (!\function_exists('iconv')) {
            self::$isIconvEnabled = \false;
        } elseif (!@\iconv('UTF-8', 'UTF-16LE', 'x')) {
            // Sometimes iconv is not working, and e.g. iconv('UTF-8', 'UTF-16LE', 'x') just returns false,
            self::$isIconvEnabled = \false;
        } elseif (\defined('PHP_OS') && @\stristr(\PHP_OS, 'AIX') && \defined('ICONV_IMPL') && @\strcasecmp(\ICONV_IMPL, 'unknown') == 0 && \defined('ICONV_VERSION') && @\strcasecmp(\ICONV_VERSION, 'unknown') == 0) {
            // CUSTOM: IBM AIX iconv() does not work
            self::$isIconvEnabled = \false;
        }
        // Deactivate iconv default options if they fail (as seen on IMB i)
        if (self::$isIconvEnabled && !@\iconv('UTF-8', 'UTF-16LE' . self::$iconvOptions, 'x')) {
            self::$iconvOptions = '';
        }
        return self::$isIconvEnabled;
    }
    private static function buildCharacterSets() : void
    {
        if (empty(self::$controlCharacters)) {
            self::buildControlCharacters();
        }
        if (empty(self::$SYLKCharacters)) {
            self::buildSYLKCharacters();
        }
    }
    /**
     * Convert from OpenXML escaped control character to PHP control character.
     *
     * Excel 2007 team:
     * ----------------
     * That's correct, control characters are stored directly in the shared-strings table.
     * We do encode characters that cannot be represented in XML using the following escape sequence:
     * _xHHHH_ where H represents a hexadecimal character in the character's value...
     * So you could end up with something like _x0008_ in a string (either in a cell value (<v>)
     * element or in the shared string <t> element.
     *
     * @param string $textValue Value to unescape
     *
     * @return string
     */
    public static function controlCharacterOOXML2PHP($textValue)
    {
        self::buildCharacterSets();
        return \str_replace(\array_keys(self::$controlCharacters), \array_values(self::$controlCharacters), $textValue);
    }
    /**
     * Convert from PHP control character to OpenXML escaped control character.
     *
     * Excel 2007 team:
     * ----------------
     * That's correct, control characters are stored directly in the shared-strings table.
     * We do encode characters that cannot be represented in XML using the following escape sequence:
     * _xHHHH_ where H represents a hexadecimal character in the character's value...
     * So you could end up with something like _x0008_ in a string (either in a cell value (<v>)
     * element or in the shared string <t> element.
     *
     * @param string $textValue Value to escape
     *
     * @return string
     */
    public static function controlCharacterPHP2OOXML($textValue)
    {
        self::buildCharacterSets();
        return \str_replace(\array_values(self::$controlCharacters), \array_keys(self::$controlCharacters), $textValue);
    }
    /**
     * Try to sanitize UTF8, replacing invalid sequences with Unicode substitution characters.
     */
    public static function sanitizeUTF8(string $textValue) : string
    {
        $textValue = \str_replace(["￾", "￿"], "�", $textValue);
        $subst = \mb_substitute_character();
        // default is question mark
        \mb_substitute_character(65533);
        // Unicode substitution character
        // Phpstan does not think this can return false.
        $returnValue = \mb_convert_encoding($textValue, 'UTF-8', 'UTF-8');
        \mb_substitute_character(
            /** @scrutinizer ignore-type */
            $subst
        );
        return self::returnString($returnValue);
    }
    /**
     * Strictly to satisfy Scrutinizer.
     *
     * @param mixed $value
     */
    private static function returnString($value) : string
    {
        return \is_string($value) ? $value : '';
    }
    /**
     * Check if a string contains UTF8 data.
     */
    public static function isUTF8(string $textValue) : bool
    {
        return $textValue === self::sanitizeUTF8($textValue);
    }
    /**
     * Formats a numeric value as a string for output in various output writers forcing
     * point as decimal separator in case locale is other than English.
     *
     * @param float|int|string $numericValue
     */
    public static function formatNumber($numericValue) : string
    {
        if (\is_float($numericValue)) {
            return \str_replace(',', '.', (string) $numericValue);
        }
        return (string) $numericValue;
    }
    /**
     * Converts a UTF-8 string into BIFF8 Unicode string data (8-bit string length)
     * Writes the string using uncompressed notation, no rich text, no Asian phonetics
     * If mbstring extension is not available, ASCII is assumed, and compressed notation is used
     * although this will give wrong results for non-ASCII strings
     * see OpenOffice.org's Documentation of the Microsoft Excel File Format, sect. 2.5.3.
     *
     * @param string $textValue UTF-8 encoded string
     * @param mixed[] $arrcRuns Details of rich text runs in $value
     */
    public static function UTF8toBIFF8UnicodeShort(string $textValue, array $arrcRuns = []) : string
    {
        // character count
        $ln = self::countCharacters($textValue, 'UTF-8');
        // option flags
        if (empty($arrcRuns)) {
            $data = \pack('CC', $ln, 0x1);
            // characters
            $data .= self::convertEncoding($textValue, 'UTF-16LE', 'UTF-8');
        } else {
            $data = \pack('vC', $ln, 0x9);
            $data .= \pack('v', \count($arrcRuns));
            // characters
            $data .= self::convertEncoding($textValue, 'UTF-16LE', 'UTF-8');
            foreach ($arrcRuns as $cRun) {
                $data .= \pack('v', $cRun['strlen']);
                $data .= \pack('v', $cRun['fontidx']);
            }
        }
        return $data;
    }
    /**
     * Converts a UTF-8 string into BIFF8 Unicode string data (16-bit string length)
     * Writes the string using uncompressed notation, no rich text, no Asian phonetics
     * If mbstring extension is not available, ASCII is assumed, and compressed notation is used
     * although this will give wrong results for non-ASCII strings
     * see OpenOffice.org's Documentation of the Microsoft Excel File Format, sect. 2.5.3.
     *
     * @param string $textValue UTF-8 encoded string
     */
    public static function UTF8toBIFF8UnicodeLong(string $textValue) : string
    {
        // character count
        $ln = self::countCharacters($textValue, 'UTF-8');
        // characters
        $chars = self::convertEncoding($textValue, 'UTF-16LE', 'UTF-8');
        return \pack('vC', $ln, 0x1) . $chars;
    }
    /**
     * Convert string from one encoding to another.
     *
     * @param string $to Encoding to convert to, e.g. 'UTF-8'
     * @param string $from Encoding to convert from, e.g. 'UTF-16LE'
     */
    public static function convertEncoding(string $textValue, string $to, string $from) : string
    {
        if (self::getIsIconvEnabled()) {
            $result = \iconv($from, $to . self::$iconvOptions, $textValue);
            if (\false !== $result) {
                return $result;
            }
        }
        return self::returnString(\mb_convert_encoding($textValue, $to, $from));
    }
    /**
     * Get character count.
     *
     * @param string $encoding Encoding
     *
     * @return int Character count
     */
    public static function countCharacters(string $textValue, string $encoding = 'UTF-8') : int
    {
        return \mb_strlen($textValue, $encoding);
    }
    /**
     * Get character count using mb_strwidth rather than mb_strlen.
     *
     * @param string $encoding Encoding
     *
     * @return int Character count
     */
    public static function countCharactersDbcs(string $textValue, string $encoding = 'UTF-8') : int
    {
        return \mb_strwidth($textValue, $encoding);
    }
    /**
     * Get a substring of a UTF-8 encoded string.
     *
     * @param string $textValue UTF-8 encoded string
     * @param int $offset Start offset
     * @param ?int $length Maximum number of characters in substring
     */
    public static function substring(string $textValue, int $offset, ?int $length = 0) : string
    {
        return \mb_substr($textValue, $offset, $length, 'UTF-8');
    }
    /**
     * Convert a UTF-8 encoded string to upper case.
     *
     * @param string $textValue UTF-8 encoded string
     */
    public static function strToUpper(string $textValue) : string
    {
        return \mb_convert_case($textValue, \MB_CASE_UPPER, 'UTF-8');
    }
    /**
     * Convert a UTF-8 encoded string to lower case.
     *
     * @param string $textValue UTF-8 encoded string
     */
    public static function strToLower(string $textValue) : string
    {
        return \mb_convert_case($textValue, \MB_CASE_LOWER, 'UTF-8');
    }
    /**
     * Convert a UTF-8 encoded string to title/proper case
     * (uppercase every first character in each word, lower case all other characters).
     *
     * @param string $textValue UTF-8 encoded string
     */
    public static function strToTitle(string $textValue) : string
    {
        return \mb_convert_case($textValue, \MB_CASE_TITLE, 'UTF-8');
    }
    public static function mbIsUpper(string $character) : bool
    {
        return \mb_strtolower($character, 'UTF-8') !== $character;
    }
    /**
     * Splits a UTF-8 string into an array of individual characters.
     */
    public static function mbStrSplit(string $string) : array
    {
        // Split at all position not after the start: ^
        // and not before the end: $
        $split = \preg_split('/(?<!^)(?!$)/u', $string);
        return $split === \false ? [] : $split;
    }
    /**
     * Reverse the case of a string, so that all uppercase characters become lowercase
     * and all lowercase characters become uppercase.
     *
     * @param string $textValue UTF-8 encoded string
     */
    public static function strCaseReverse(string $textValue) : string
    {
        $characters = self::mbStrSplit($textValue);
        foreach ($characters as &$character) {
            if (self::mbIsUpper($character)) {
                $character = \mb_strtolower($character, 'UTF-8');
            } else {
                $character = \mb_strtoupper($character, 'UTF-8');
            }
        }
        return \implode('', $characters);
    }
    /**
     * Get the decimal separator. If it has not yet been set explicitly, try to obtain number
     * formatting information from locale.
     */
    public static function getDecimalSeparator() : string
    {
        if (!isset(self::$decimalSeparator)) {
            $localeconv = \localeconv();
            self::$decimalSeparator = $localeconv['decimal_point'] != '' ? $localeconv['decimal_point'] : $localeconv['mon_decimal_point'];
            if (self::$decimalSeparator == '') {
                // Default to .
                self::$decimalSeparator = '.';
            }
        }
        return self::$decimalSeparator;
    }
    /**
     * Set the decimal separator. Only used by NumberFormat::toFormattedString()
     * to format output by \PhpOffice\PhpSpreadsheet\Writer\Html and \PhpOffice\PhpSpreadsheet\Writer\Pdf.
     *
     * @param string $separator Character for decimal separator
     */
    public static function setDecimalSeparator(string $separator) : void
    {
        self::$decimalSeparator = $separator;
    }
    /**
     * Get the thousands separator. If it has not yet been set explicitly, try to obtain number
     * formatting information from locale.
     */
    public static function getThousandsSeparator() : string
    {
        if (!isset(self::$thousandsSeparator)) {
            $localeconv = \localeconv();
            self::$thousandsSeparator = $localeconv['thousands_sep'] != '' ? $localeconv['thousands_sep'] : $localeconv['mon_thousands_sep'];
            if (self::$thousandsSeparator == '') {
                // Default to .
                self::$thousandsSeparator = ',';
            }
        }
        return self::$thousandsSeparator;
    }
    /**
     * Set the thousands separator. Only used by NumberFormat::toFormattedString()
     * to format output by \PhpOffice\PhpSpreadsheet\Writer\Html and \PhpOffice\PhpSpreadsheet\Writer\Pdf.
     *
     * @param string $separator Character for thousands separator
     */
    public static function setThousandsSeparator(string $separator) : void
    {
        self::$thousandsSeparator = $separator;
    }
    /**
     *    Get the currency code. If it has not yet been set explicitly, try to obtain the
     *        symbol information from locale.
     */
    public static function getCurrencyCode() : string
    {
        if (!empty(self::$currencyCode)) {
            return self::$currencyCode;
        }
        self::$currencyCode = '$';
        $localeconv = \localeconv();
        if (!empty($localeconv['currency_symbol'])) {
            self::$currencyCode = $localeconv['currency_symbol'];
            return self::$currencyCode;
        }
        if (!empty($localeconv['int_curr_symbol'])) {
            self::$currencyCode = $localeconv['int_curr_symbol'];
            return self::$currencyCode;
        }
        return self::$currencyCode;
    }
    /**
     * Set the currency code. Only used by NumberFormat::toFormattedString()
     *        to format output by \PhpOffice\PhpSpreadsheet\Writer\Html and \PhpOffice\PhpSpreadsheet\Writer\Pdf.
     *
     * @param string $currencyCode Character for currency code
     */
    public static function setCurrencyCode(string $currencyCode) : void
    {
        self::$currencyCode = $currencyCode;
    }
    /**
     * Convert SYLK encoded string to UTF-8.
     *
     * @param string $textValue SYLK encoded string
     *
     * @return string UTF-8 encoded string
     */
    public static function SYLKtoUTF8(string $textValue) : string
    {
        self::buildCharacterSets();
        // If there is no escape character in the string there is nothing to do
        if (\strpos($textValue, '') === \false) {
            return $textValue;
        }
        foreach (self::$SYLKCharacters as $k => $v) {
            $textValue = \str_replace($k, $v, $textValue);
        }
        return $textValue;
    }
    /**
     * Retrieve any leading numeric part of a string, or return the full string if no leading numeric
     * (handles basic integer or float, but not exponent or non decimal).
     *
     * @param string $textValue
     *
     * @return mixed string or only the leading numeric part of the string
     */
    public static function testStringAsNumeric($textValue)
    {
        if (\is_numeric($textValue)) {
            return $textValue;
        }
        $v = (float) $textValue;
        return \is_numeric(\substr($textValue, 0, \strlen((string) $v))) ? $v : $textValue;
    }
}
