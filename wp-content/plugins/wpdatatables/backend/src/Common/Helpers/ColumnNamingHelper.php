<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Helpers;

/**
 * Column header sanitization and MySQL naming.
 *
 * @package WPDataTables\Common\Helpers
 */
class ColumnNamingHelper
{
    /**
         * Helper function that returns array of possible column types
         * @return array
         */
        public static function getPossibleColumnTypes(): array
        {
            $possibleColumnTypes = array(
                'input' => __('One line string', 'wpdatatables'),
                'memo' => __('Multi-line string', 'wpdatatables'),
                'select' => __('One-line selectbox', 'wpdatatables'),
                'multiselect' => __('Multi-line selectbox', 'wpdatatables'),
                'hidden' => __('Hidden (Dynamic)', 'wpdatatables'),
                'int' => __('Integer', 'wpdatatables'),
                'float' => __('Float', 'wpdatatables'),
                'date' => __('Date', 'wpdatatables'),
                'datetime' => __('Datetime', 'wpdatatables'),
                'time' => __('Time', 'wpdatatables'),
                'link' => __('URL Link', 'wpdatatables'),
                'email' => __('E-mail', 'wpdatatables'),
                'image' => __('Image', 'wpdatatables'),
                'file' => __('Attachment', 'wpdatatables')
            );
    
            return apply_filters('wpdatatables_filter_possible_column_types', $possibleColumnTypes);
        }

    /**
         * Helper function that sanitize column header
         *
         * @param $headersInFormula
         *
         * @return array
         */
        public static function sanitizeHeaders($headersInFormula): array
        {
    
            $headers = array();
            foreach ($headersInFormula as $key => $header) {
                $headers[$header] = str_replace(
                    range('0', '9'),
                    range('a', 'j'),
                    "wpdatacolumn" . $key
                );
            }
            return $headers;
        }

    /**
         * Helper method to detect the headers that are present in formula
         *
         * @param $formula
         * @param $headers
         *
         * @return array
         */
        public static function getColHeadersInFormula($formula, $headers)
        {
            $headersInFormula = array();
            foreach ($headers as $header) {
                if (strpos($formula, (string)$header) !== false) {
                    $headersInFormula[] = $header;
                }
            }
            return $headersInFormula;
        }

    /**
         * Helper function to generate unique MySQL column headers
         *
         * @param $header
         * @param $existing_headers
         *
         * @return mixed|string
         */
        public static function generateMySQLColumnName($header, $existing_headers)
        {
            // Prepare the column MySQL title
            $column_header = self::slugify($header);
    
            // Add index until column header becomes unique
            if (in_array($column_header, $existing_headers)) {
                $index = 0;
                do {
                    $index++;
                    $try_column_header = $column_header . $index;
                } while (in_array($try_column_header, $existing_headers));
                $column_header = $try_column_header;
            }
    
            return $column_header;
        }

    /**
         * Helper function to translate special UTF-8 to latin for MySQL
         *
         * @param $text
         *
         * @return mixed|string
         */
        public static function slugify($text)
        {
            // replace non letter or digits by _
            $text = preg_replace('#[^\\pL\d]+#u', '_', $text);
    
            // trim
            $text = trim($text, '_');
    
            // transliterate
            if (function_exists('iconv')) {
                $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            }
    
            // lowercase
            $text = strtolower($text);
    
            // remove unwanted characters
            $text = preg_replace('#[^-\w]+#', '', $text);
    
            // WP sanitize
            $text = str_replace(array('-', '_'), '', sanitize_title($text));
    
            if (empty($text) || is_numeric($text)) {
                return 'wdtcolumn';
            }
    
            return $text;
        }
}
