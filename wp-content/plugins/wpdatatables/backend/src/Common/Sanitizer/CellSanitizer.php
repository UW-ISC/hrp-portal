<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Sanitizer;

use Connection;
use DOMDocument;
use DOMElement;
use PgSqlConnection;

/**
 * Frontend cell value and HTML sanitization.
 *
 * @package WPDataTables\Common\Sanitizer
 */
class CellSanitizer
{
    /**
         * True if URL is safe for stored link/image cells (http/https with a real host, or root-relative path).
         * Rejects junk like http://alert(1) produced when javascript: is mangled by esc_url().
         *
         * @param string $url
         * @return bool
         */
        public static function isWpdtSafeWebUrl($url)
        {
            $url = trim((string) $url);
            if ($url === '') {
                return false;
            }
            $parts = @parse_url($url);
            if ($parts === false) {
                return false;
            }
            // Root-relative path only (no scheme/host)
            if (empty($parts['scheme']) && isset($parts['path']) && strpos($parts['path'], '/') === 0 && strpos($parts['path'], '//') !== 0) {
                return true;
            }
            if (empty($parts['scheme']) || empty($parts['host'])) {
                return false;
            }
            $scheme = strtolower($parts['scheme']);
            if (!in_array($scheme, array('http', 'https'), true)) {
                return false;
            }
            $host = strtolower($parts['host']);
            if ($host === 'localhost') {
                return true;
            }
            $ipCandidate = $host;
            if (strlen($host) >= 2 && $host[0] === '[' && substr($host, -1) === ']') {
                $ipCandidate = substr($host, 1, -1);
            }
            if (filter_var($ipCandidate, FILTER_VALIDATE_IP)) {
                return true;
            }
            if (strpos($host, '.') === false) {
                return false;
            }
    
            return true;
        }

    /**
         * Rich-text string cells (stripJsAttributes): remove href only for scriptable / unsafe URL shapes.
         * Stricter host checks remain on src|poster|formaction via isWpdtSafeWebUrl in sanitizeCellHtmlUrlAttribute.
         *
         * @param string $url Already passed through esc_url().
         * @return bool True if the href attribute should be removed.
         */
        private static function cellHtmlRichHrefMustStrip($url)
        {
            $url = trim((string) $url);
            if ($url === '') {
                return true;
            }
            if (strpos($url, '//') === 0) {
                return true;
            }
            $parts = @parse_url($url);
            if ($parts === false) {
                return true;
            }
            $scheme = isset($parts['scheme']) ? strtolower((string) $parts['scheme']) : '';
            if ($scheme === '') {
                return false;
            }
            if (in_array($scheme, array('javascript', 'vbscript', 'file'), true)) {
                return true;
            }
            if ($scheme === 'data') {
                return true;
            }
    
            return false;
        }

    /**
         * Normalize href/src for HTML from string cells; returns empty string to mean remove attribute.
         * For src, poster, and formaction only http(s) URLs or safe root-relative paths (isWpdtSafeWebUrl).
         * For href in generic string-cell HTML (stripJsAttributes), only dangerous schemes are removed after esc_url;
         * dedicated link/image columns still use isWpdtSafeWebUrl in sanitizeLinkCellValueForDb / similar.
         *
         * @param string $attrLower lowercase attribute name
         * @param string $raw
         * @return string
         */
        public static function sanitizeCellHtmlUrlAttribute($attrLower, $raw)
        {
            $raw = trim((string) $raw);
            if ($raw === '') {
                return '';
            }
            if (preg_match('#^\s*(javascript|vbscript)\s*:#iu', $raw) || preg_match('#^\s*data\s*:\s*text\/html#iu', $raw)) {
                return '';
            }
            $sanitized = esc_url($raw);
            if ($sanitized === '') {
                return '';
            }
            if (in_array($attrLower, array('src', 'poster', 'formaction'), true)) {
                return self::isWpdtSafeWebUrl($sanitized) ? $sanitized : '';
            }
    
            if ($attrLower === 'href') {
                return self::cellHtmlRichHrefMustStrip($sanitized) ? '' : $sanitized;
            }
    
            return $sanitized;
        }

    /**
         * Sanitize URL Link column value before DB storage (frontend Excel / similar).
         * Supports "url" or "url||label" (CVE-2026-8277: attribute breakouts, javascript: URIs).
         *
         * @param string $value
         * @return string
         */
        public static function sanitizeLinkCellValueForDb($value)
        {
            if ($value === null || $value === '') {
                return '';
            }
            $value = wp_strip_all_tags(wp_unslash((string) $value));
            $parts = explode('||', $value, 2);
            $url = esc_url_raw(trim($parts[0]));
            if ($url === '' || !self::isWpdtSafeWebUrl($url)) {
                return '';
            }
            if (!isset($parts[1]) || trim((string) $parts[1]) === '') {
                return $url;
            }
            $label = sanitize_text_field(wp_strip_all_tags($parts[1]));
    
            return $label !== '' ? $url . '||' . $label : $url;
        }

    /**
         * Sanitize E-mail column value ("email" or "email||label").
         *
         * @param string $value
         * @return string
         */
        public static function sanitizeEmailCellValueForDb($value)
        {
            if ($value === null || $value === '') {
                return '';
            }
            $value = wp_strip_all_tags(wp_unslash((string) $value));
            $parts = explode('||', $value, 2);
            $email = sanitize_email(trim($parts[0]));
            if ($email === '') {
                return '';
            }
            if (!isset($parts[1]) || trim((string) $parts[1]) === '') {
                return $email;
            }
            $label = sanitize_text_field(wp_strip_all_tags($parts[1]));
    
            return $label !== '' ? $email . '||' . $label : $email;
        }

    /**
         * Sanitize Image column value ("src" or "thumb||full" URLs).
         *
         * @param string $value
         * @return string
         */
        public static function sanitizeImageCellValueForDb($value)
        {
            if ($value === null || $value === '') {
                return '';
            }
            $value = wp_strip_all_tags(wp_unslash((string) $value));
            $parts = explode('||', $value, 2);
            $thumb = esc_url_raw(trim($parts[0]));
            if ($thumb === '' || !self::isWpdtSafeWebUrl($thumb)) {
                return '';
            }
            if (!isset($parts[1]) || trim((string) $parts[1]) === '') {
                return $thumb;
            }
            $full = esc_url_raw(trim($parts[1]));
            if ($full === '' || !self::isWpdtSafeWebUrl($full)) {
                return $thumb;
            }
    
            return $thumb . '||' . $full;
        }

    /**
         * Sanitizes the cell string and wraps it with quotes
         *
         * @param $string
         * @param $connection
         *
         * @return string
         */
        public static function prepareStringCell($string, $connection)
        {
            global $wpdb;
            if (self::isHtml($string)) {
                $string = self::stripJsAttributes($string);
            }
    
            if ($connection) {
                $string = stripslashes($string);
                $vendor = Connection::getVendor($connection);
                $isMySql = $vendor === Connection::$MYSQL;
                $isMSSql = $vendor === Connection::$MSSQL;
                $isPostgreSql = $vendor === Connection::$POSTGRESQL;
    
                if ($isPostgreSql) {
                    if (version_compare(WDT_PHP_SERVER_VERSION, '8.1', '>')) {
                        $connectionPostgreSql = PgSqlConnection::getInstance($connection);
                        $string = pg_escape_string($connectionPostgreSql, $string);
                        $string = stripslashes($string);
                    } else {
                        $string = pg_escape_string($string);
                        $string = stripslashes($string);
                    }
                }
                if ($isMSSql) {
                    $string = str_replace("'", "''", $string);
                }
                if ($isMySql) {
                    $string = $wpdb->_real_escape($string);
                    $string = $wpdb->remove_placeholder_escape($string);
                }
            }
            $string = self::wrapQuotes($string, $connection);
            return $string;
        }

    /**
         * Check if passed string is HTML element
         *
         * @param $string
         *
         * @return bool
         */
        public static function isHtml($string)
        {
            return preg_match("/<[^<]+>/", $string, $m) != 0;
        }

    /**
         * Function that strip JS attributes to prevent XSS attacks
         *
         * @param $htmlString
         *
         * @return bool|string
         */
        public static function stripJsAttributes($htmlString)
        {
            $htmlString = stripcslashes($htmlString);
            $htmlString = '<div>' . $htmlString . '</div>';
            if (function_exists('mb_convert_encoding')) {
                $domd = new DOMDocument();
                $domd_status = @$domd->loadHTML(mb_convert_encoding($htmlString, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
                if ($domd_status) {
                    foreach ($domd->getElementsByTagName('*') as $node) {
                        $remove = array();
                        foreach ($node->attributes as $attributeName => $attribute) {
                            if (substr($attributeName, 0, 2) == 'on') {
                                $remove[] = $attributeName;
                            }
                        }
                        foreach ($remove as $i) {
                            $node->removeAttribute($i);
                        }
                    }
                    // iframe srcdoc can embed HTML/JS; not covered by href/src rules alone.
                    foreach ($domd->getElementsByTagName('*') as $node) {
                        if ($node instanceof DOMElement && $node->hasAttribute('srcdoc')) {
                            $node->removeAttribute('srcdoc');
                        }
                    }
                    $urlAttributeNames = array('href', 'src', 'poster', 'formaction');
                    foreach ($domd->getElementsByTagName('*') as $node) {
                        if (!$node->hasAttributes()) {
                            continue;
                        }
                        $attrsToSanitize = array();
                        foreach ($node->attributes as $attributeName => $attribute) {
                            if (in_array(strtolower($attributeName), $urlAttributeNames, true)) {
                                $attrsToSanitize[] = $attributeName;
                            }
                        }
                        foreach ($attrsToSanitize as $attrName) {
                            $raw = $node->getAttribute($attrName);
                            if ($raw === '') {
                                continue;
                            }
                            $attrLower = strtolower($attrName);
                            $sanitized = self::sanitizeCellHtmlUrlAttribute($attrLower, $raw);
                            if ($sanitized === '') {
                                $node->removeAttribute($attrName);
                            } else {
                                $node->setAttribute($attrName, $sanitized);
                            }
                        }
                    }
                    // After removing unsafe href, unwrap <a> so we do not store meaningless <a>text</a> (string columns).
                    $anchorNodes = array();
                    foreach ($domd->getElementsByTagName('a') as $anchor) {
                        $anchorNodes[] = $anchor;
                    }
                    foreach ($anchorNodes as $node) {
                        if (!($node instanceof DOMElement)) {
                            continue;
                        }
                        if (trim((string) $node->getAttribute('href')) !== '') {
                            continue;
                        }
                        $parent = $node->parentNode;
                        if (!$parent) {
                            continue;
                        }
                        while ($node->firstChild) {
                            $parent->insertBefore($node->firstChild, $node);
                        }
                        $parent->removeChild($node);
                    }
                    return substr($domd->saveHTML($domd->documentElement), 5, -6);
                }
            }
    
            // Fail closed: no mb_convert_encoding, loadHTML failed, or DOM unusable — never return raw markup.
            return wp_strip_all_tags($htmlString);
        }

    /**
         * Helper method to wrap values in quotes for DB
         */
        public static function wrapQuotes($value, $connection)
        {
            $valueQuote = $connection ? "'" : '';
            return $valueQuote . $value . $valueQuote;
        }
}
