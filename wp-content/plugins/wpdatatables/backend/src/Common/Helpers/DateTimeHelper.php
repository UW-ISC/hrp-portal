<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Helpers;

use DateTime;

/**
 * Date/time format conversion for tables and JS.
 *
 * @package WPDataTables\Common\Helpers
 */
class DateTimeHelper
{
    /**
         * Helper function that converts PHP to Moment Date Format
         *
         * @param $dateFormat
         *
         * @return string
         */
        public static function convertPhpToMomentDateFormat($dateFormat)
        {
            $replacements = array(
                'd' => 'DD',
                'D' => 'ddd',
                'j' => 'D',
                'l' => 'dddd',
                'N' => 'E',
                'S' => 'o',
                'w' => 'e',
                'z' => 'DDD',
                'W' => 'W',
                'F' => 'MMMM',
                'm' => 'MM',
                'M' => 'MMM',
                'n' => 'M',
                't' => '', // no equivalent
                'L' => '', // no equivalent
                'o' => 'YYYY',
                'Y' => 'YYYY',
                'y' => 'YY',
                'a' => 'a',
                'A' => 'A',
                'B' => '', // no equivalent
                'g' => 'h',
                'G' => 'H',
                'h' => 'hh',
                'H' => 'HH',
                'i' => 'mm',
                's' => 'ss',
                'u' => 'SSS',
                'e' => 'zz', // deprecated since version 1.6.0 of moment.js
                'I' => '', // no equivalent
                'O' => '', // no equivalent
                'P' => '', // no equivalent
                'T' => '', // no equivalent
                'Z' => '', // no equivalent
                'c' => '', // no equivalent
                'r' => '', // no equivalent
                'U' => 'X',
            );
    
            return strtr($dateFormat, $replacements);
        }

    /**
         * Helper method that converts provided String to Unix Timestamp
         * based on provided date format
         *
         * @param $dateString
         * @param $dateFormat
         *
         * @return false|int
         */
        public static function wdtConvertStringToUnixTimestamp($dateString, $dateFormat)
        {
            if ($dateString == '') return null;
            if (!$dateFormat) $dateFormat = get_option('wdtDateFormat');
    
            if (null !== $dateFormat && substr($dateFormat, 0, 5) === 'd/m/Y') {
                $returnDate = strtotime(str_replace('/', '-', $dateString));
            } else if (null !== $dateFormat && in_array($dateFormat, ['m.d.Y',
                    'm-d-Y',
                    'm-d-y',
                    'd.m.y',
                    'Y.m.d',
                    'd-m-Y'])) {
                $returnDate = strtotime(str_replace(['.', '-'], '/', $dateString));
            } else if (null !== $dateFormat && $dateFormat == 'm/Y') {
                $dateObject = DateTime::createFromFormat($dateFormat, $dateString);
                if (!$dateObject) return strtotime($dateString);
                $returnDate = $dateObject->getTimestamp();
            } else {
                $returnDate = strtotime($dateString);
            }
    
            return $returnDate ?: '';
        }

    /**
         * Helper method that converts provided Unix Timestamp to string
         * based on provided date format
         *
         * @param $columnType
         * @param $displayColumnNameData
         */
        public static function wdtConvertUnixTimestampToString($columnType, $displayColumnNameData)
        {
            if ($columnType == 'date') {
                $displayColumnNameData = date(get_option('wdtDateFormat'), $displayColumnNameData);
            } else if ($columnType == 'datetime') {
                $displayColumnNameData = date(get_option('wdtDateFormat') . ' ' . get_option('wdtTimeFormat'), $displayColumnNameData);
            } else if ($columnType == 'time') {
                $displayColumnNameData = date(get_option('wdtTimeFormat'), $displayColumnNameData);
            }
    
            return $displayColumnNameData;
        }

    /**
         * Helper function that returns an array with date and time settings from wp_options
         * @return array
         */
        public static function getDateTimeSettings()
        {
            return array(
                'wdtDateFormat' => get_option('wdtDateFormat'),
                'wdtTimeFormat' => get_option('wdtTimeFormat'),
                'wdtNumberFormat' => get_option('wdtNumberFormat'),
                'wdtGlobalTableLoader' => get_option('wdtGlobalTableLoader'),
            );
        }
}
