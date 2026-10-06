<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Helpers;

use WDTException;
use WPDataTables\Infrastructure\Http\HttpClient;

/**
 * CSV/Google Sheets/XML import and column type autodetection.
 *
 * @package WPDataTables\Common\Helpers
 */
class DataImportHelper
{
    /**
         * Helper function to find CSV delimiter
         *
         * @param $csv_url
         *
         * @return string
         */
        public static function detectCSVDelimiter($csv_url)
        {
    
            if (!file_exists($csv_url) || !is_readable($csv_url)) {
                throw new WDTException('Could not open ' . $csv_url . ' for reading! File does not exist.');
            }
            $fileResurce = fopen($csv_url, 'r');
    
            $delimiterList = [',', ':', ';', "\t", '|'];
            $counts = [];
            foreach ($delimiterList as $delimiter) {
                $counts[$delimiter] = [];
            }
    
            $lineNumber = 0;
            while (($line = fgets($fileResurce)) !== false && (++$lineNumber < 1000)) {
                $lineCount = [];
                for ($i = strlen($line) - 1; $i >= 0; --$i) {
                    $character = $line[$i];
                    if (isset($counts[$character])) {
                        if (!isset($lineCount[$character])) {
                            $lineCount[$character] = 0;
                        }
                        ++$lineCount[$character];
                    }
                }
                foreach ($delimiterList as $delimiter) {
                    $counts[$delimiter][] = isset($lineCount[$delimiter])
                        ? $lineCount[$delimiter]
                        : 0;
                }
            }
    
            $RMSD = [];
            $middleIdx = floor(($lineNumber - 1) / 2);
    
            foreach ($delimiterList as $delimiter) {
                $series = $counts[$delimiter];
                sort($series);
    
                $median = ($lineNumber % 2)
                    ? $series[$middleIdx]
                    : ($series[$middleIdx] + $series[$middleIdx + 1]) / 2;
    
                if ($median === 0) {
                    continue;
                }
    
                $RMSD[$delimiter] = array_reduce(
                        $series,
                        function ($sum, $value) use ($median) {
                            return $sum + pow($value - $median, 2);
                        }
                    ) / count($series);
            }
    
            $min = INF;
            $finalDelimiter = '';
            foreach ($delimiterList as $delimiter) {
                if (!isset($RMSD[$delimiter])) {
                    continue;
                }
    
                if ($RMSD[$delimiter] < $min) {
                    $min = $RMSD[$delimiter];
                    $finalDelimiter = $delimiter;
                }
            }
    
            if ($delimiter === null) {
                $finalDelimiter = reset($delimiterList);
            }
    
            return $finalDelimiter;
        }

    /**
         * Helper function that convert CSV file to Array
         *
         * @param $csv
         *
         * @return array
         */
        public static function csvToArray($csv)
        {
            $arr = array();
            $lines = explode("\r\n", $csv);
            foreach ($lines as $row) {
                $arr[] = str_getcsv($row, ",");
            }
            return self::gsArrayToWDTArray($arr);
        }

    /**
         * Helper function that convert Google Sheet array to adopt in wpdt Array
         *
         * @param $arr
         *
         * @return array
         */
        public static function gsArrayToWDTArray($arr)
        {
            $count = count($arr) - 1;
            $labels = array_shift($arr);
            $countLabels = count($labels);
            $keys = array();
            foreach ($labels as $label) {
                $keys[] = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $label)));
            }
            $keys = array_map('trim', $keys);
            $returnArray = array();
            for ($j = 0; $j < $count; $j++) {
                if (count($arr[$j]) < $countLabels) {
                    for ($k = 0; $k < $countLabels; $k++) {
                        if (!isset($arr[$j][$k])) {
                            $arr[$j][$k] = '';
                        }
                    }
                }
                if (count($keys) == count($arr[$j])) {
                    $d = array_combine($keys, $arr[$j]);
                    $returnArray[$j] = $d;
                }
            }
            return $returnArray;
        }

    /**
         * Helper function that extract Google Spreadsheet URL and get ID
         *
         * @param $url
         *
         * @return string
         */
        public static function getGoogleSpreadsheetID($url)
        {
            $url_arr = explode('/', $url);
            return $url_arr[count($url_arr) - 2];
        }

    /**
         * Helper function that extract Google Spreadsheet URL and get Worksheets ID
         *
         * @param $url
         *
         * @return string
         */
        public static function getGoogleWorksheetsID($url)
        {
            if (strpos($url, '#') !== false) {
                $url_query = parse_url($url, PHP_URL_FRAGMENT);
            } else {
                $url_query = parse_url($url, PHP_URL_QUERY);
            }
    
            if (!empty($url_query)) {
                parse_str($url_query, $url_query_params);
                if (!empty($url_query_params['gid'])) {
                    return $url_query_params['gid'];
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        }

    /**
         * Helper function that extract Google Spreadsheet
         *
         * @param $url
         *
         * @return array|string
         * @throws Exception
         */
        public static function extractGoogleSpreadsheetArray($url)
        {
            if (empty($url)) {
                return '';
            }
            $url_arr = explode('/', $url);
            $spreadsheet_key = $url_arr[count($url_arr) - 2];
    
            if (strpos($url, '2PACX') !== false) {
                $csv_url = "https://docs.google.com/spreadsheets/d/e/{$spreadsheet_key}/pub?output=csv";
            } else {
                $csv_url = "https://docs.google.com/spreadsheets/d/{$spreadsheet_key}/pub?hl=en_US&hl=en_US&single=true&output=csv";
            }
    
            if (strpos($url, '#') !== false) {
                $url_query = parse_url($url, PHP_URL_FRAGMENT);
            } else {
                $url_query = parse_url($url, PHP_URL_QUERY);
            }
    
            if (!empty($url_query)) {
                parse_str($url_query, $url_query_params);
                if (!empty($url_query_params['gid'])) {
                    $csv_url .= '&gid=' . $url_query_params['gid'];
                } else {
                    $csv_url .= '&gid=0';
                }
            }
            $csv_data = HttpClient::curlGetData($csv_url);
            if (!is_null($csv_data)) {
                return self::csvToArray($csv_data);
            }
    
            return array();
        }

    /**
         * Helper function that convert XML to Array
         *
         * @param $xml SimpleXMLElement
         * @param bool $root
         *
         * @return array|string
         */
        public static function convertXMLtoArr($xml, $root = true)
        {
            if (!$xml->children()) {
                return (string)$xml;
            }
    
            $array = array();
            foreach ($xml->children() as $element => $node) {
                $totalElement = count($xml->{$element});
    
                // Has attributes
                if ($attributes = $node->attributes()) {
                    $data = array(
                        'attributes' => array(),
                        'value' => (count($node) > 0) ? self::convertXMLtoArr($node, false) : (string)$node
                    );
    
                    foreach ($attributes as $attr => $value) {
                        $data['attributes'][$attr] = (string)$value;
                    }
    
                    $array[] = $data['attributes'];
                } else {
                    if ($totalElement > 1) {
                        $array[][] = self::convertXMLtoArr($node, false);
                    } else {
                        $array[$element] = self::convertXMLtoArr($node, false);
                    }
                }
            }
    
            return $array;
        }

    /**
         * Helper function that check if the array is associative
         *
         * @param $arr
         *
         * @return bool
         */
        public static function isArrayAssoc($arr)
        {
            return array_keys($arr) !== range(0, count($arr) - 1);
        }

    /**
         * Helper function that define default value
         *
         * @param $possible
         * @param $index
         * @param string $default
         *
         * @return string
         */
        public static function defineDefaultValue($possible, $index, $default = '')
        {
            return isset($possible[$index]) ? $possible[$index] : $default;
        }

    /**
         * Helper function that extract column headers in array
         *
         * @param $rawDataArr
         *
         * @return array
         * @throws WDTException
         */
        public static function extractHeaders($rawDataArr)
        {
            reset($rawDataArr);
            if (!is_array($rawDataArr[key($rawDataArr)])) {
                throw new WDTException('Please provide a valid 2-dimensional array.');
            }
            return array_keys($rawDataArr[key($rawDataArr)]);
        }

    /**
         * Helper function that detect columns data type
         *
         * @param $rawDataArr
         * @param $headerArr
         *
         * @return array
         * @throws WDTException
         */
        public static function detectColumnDataTypes($rawDataArr, $headerArr)
        {
            $autodetectData = array();
            $autodetectRowsCount = (10 > count($rawDataArr)) ? count($rawDataArr) - 1 : 9;
            $wdtColumnTypes = array();
            for ($i = 0; $i <= $autodetectRowsCount; $i++) {
                foreach ($headerArr as $key) {
                    $cur_val = current($rawDataArr);
                    if (!is_array($cur_val[$key])) {
                        $autodetectData[$key][] = $cur_val[$key];
                    } else {
                        if (array_key_exists('value', $cur_val[$key])) {
                            $autodetectData[$key][] = $cur_val[$key]['value'];
                        } else {
                            throw new WDTException('Please provide a correct format for the cell.');
                        }
                    }
                }
                next($rawDataArr);
            }
            foreach ($headerArr as $key) {
                $wdtColumnTypes[$key] = self::wdtDetectColumnType($autodetectData[$key]);
            }
            return $wdtColumnTypes;
        }

    /**
         * Helper function that detect single column type
         *
         * @param $values
         *
         * @return string
         */
        private static function wdtDetectColumnType($values)
        {
            $array = array_filter($values);
            if (empty($array)) {
                return 'string';
            } else {
    
            if (self::_detect($values, 'WPDataTables\\Common\\Helpers\\DataImportHelper::wdtIsIP')) {
                return 'string';
            }
            if (self::_detect($values, 'WPDataTables\\Common\\Helpers\\DataImportHelper::wdtIsInteger')) {
                return 'int';
            }
            if (self::_detect($values, 'preg_match', WDT_TIME_12H_REGEX)
                || self::_detect($values, 'preg_match', WDT_TIME_24H_REGEX)
                || self::_detect($values, 'preg_match', WDT_TIME_WITH_SECONDS_REGEX)) {
                return 'time';
            }
            if (self::_detect($values, 'WPDataTables\\Common\\Helpers\\DataImportHelper::wdtIsDateTime')) {
                return 'datetime';
            }
            if (self::_detect($values, 'WPDataTables\\Common\\Helpers\\DataImportHelper::wdtIsDate')) {
                return 'date';
            }
            if (self::_detect($values, 'preg_match', WDT_CURRENCY_REGEX) || self::wdtIsFloat($values)) {
                return 'float';
            }
            if (self::_detect($values, 'preg_match', WDT_EMAIL_REGEX)) {
                return 'email';
            }
            if (self::_detect($values, 'preg_match', WDT_URL_REGEX)) {
                return 'link';
            }
            if (self::_detect($values, 'WPDataTables\\Common\\Helpers\\DataImportHelper::wdtIsSelect'))
                return 'select';
            if (self::_detect($values, 'WPDataTables\\Common\\Helpers\\DataImportHelper::wdtIsAddToCart'))
                return 'cart';
                return 'string';
            }
        }

    /** @noinspection PhpUnusedPrivateMethodInspection
         * Function that checks if the passed value is integer
         * wdtIsInteger(23); //bool(true)
         * wdtIsInteger("23"); //bool(true)
         *
         * @param $input
         *
         * @return bool
         */
        private static function wdtIsInteger($input)
        {
            return ctype_digit((string)$input);
        }

    private static function wdtIsIP($input)
        {
            return (bool)filter_var($input, FILTER_VALIDATE_IP);
        }

    /**
         * Function that checks if the passed values are float
         *
         * @param $values
         *
         * @return bool
         */
        private static function wdtIsFloat($values)
        {
            $count = 0;
            for ($i = 0; $i < count($values); $i++) {
                if (is_null($values[$i])) continue;
                if (is_numeric(str_replace(array('.', ','), '', $values[$i]))) {
                    $count++;
                }
            }
    
            return $count == count($values);
        }

    /** @noinspection PhpUnusedPrivateMethodInspection
         * Function that checks if the passed value is date
         *
         * @param $input
         *
         * @return bool
         */
        private static function wdtIsDate($input)
        {
            return strlen($input) > 5 &&
                (
                    strtotime($input) ||
                    strtotime(str_replace('/', '-', $input)) ||
                    strtotime(str_replace(array('.', '-'), '/', $input))
                );
        }

    /** @noinspection PhpUnusedPrivateMethodInspection
         * Function that checks if the passed values is datetime
         *
         * @param $input
         *
         * @return bool
         */
        private static function wdtIsDateTime($input)
        {
            return (
                    strtotime($input) ||
                    strtotime(str_replace('/', '-', $input)) ||
                    strtotime(str_replace(array('.', '-'), '/', $input))
                ) &&
                (
                    call_user_func('preg_match', WDT_TIME_12H_REGEX, substr($input, strpos($input, ':') - 2, 5)) ||
                    call_user_func('preg_match', WDT_TIME_24H_REGEX, substr($input, strpos($input, ':') - 2, 5)) ||
                    call_user_func('preg_match', WDT_AM_PM_TIME_REGEX, substr($input, strpos($input, ':') - 2))
                );
        }

    /**
         * Function that checks if the passed values match a Woo Table Select Column
         *
         * @param $input
         *
         * @return bool
         */
        private static function wdtIsSelect($input): bool
        {
            return $input == '<input type="checkbox" class="select-checkbox">';
        }

    /**
         * Function that checks if the passed values match a Woo Table Add To Cart Column
         *
         * @param $input
         *
         * @return bool
         */
        private static function wdtIsAddToCart($input): bool
        {
            return strpos($input, 'class="single_add_to_cart_button button alt ajax_add_to_cart"') !== false;
        }

    /**
         * @param $valuesArray
         * @param $checkFunction
         * @param string $regularExpression
         *
         * @return bool
         * @throws WDTException
         */
        private static function _detect($valuesArray, $checkFunction, $regularExpression = '')
        {
            if (!is_callable($checkFunction)) {
                throw new WDTException('Please provide a valid type detection function for wpDataTables');
            }
            $count = 0;
            for ($i = 0; $i < count($valuesArray); $i++) {
                if ($regularExpression != '') {
                    if ($valuesArray[$i] == null || call_user_func($checkFunction, $regularExpression, $valuesArray[$i])) {
                        $count++;
                    } else {
                        return false;
                    }
                } else {
                    if ($valuesArray[$i] == null || call_user_func($checkFunction, $valuesArray[$i])) {
                        $count++;
                    } else {
                        return false;
                    }
                }
            }
            if ($count == count($valuesArray)) {
                return true;
            }
            return false;
        }
}
