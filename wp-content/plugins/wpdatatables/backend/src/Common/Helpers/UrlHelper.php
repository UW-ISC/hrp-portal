<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Helpers;

/**
 * Upload URL/path and domain parsing helpers.
 *
 * @package WPDataTables\Common\Helpers
 */
class UrlHelper
{
    /**
         * Helper function which converts WP upload URL to Path
         *
         * @param $uploadUrl
         *
         * @return mixed
         */
        public static function urlToPath($uploadUrl)
        {
            $uploadsDir = wp_upload_dir();
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $uploadPath = str_replace($uploadsDir['baseurl'], str_replace('\\', '/', $uploadsDir['basedir']), $uploadUrl);
            } else {
                $uploadPath = str_replace($uploadsDir['baseurl'], $uploadsDir['basedir'], $uploadUrl);
            }
            return $uploadPath;
        }

    /**
         * Helper function which converts upload path to URL
         *
         * @param $uploadPath
         *
         * @return mixed
         */
        public static function pathToUrl($uploadPath)
        {
            $uploadsDir = wp_upload_dir();
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $uploadUrl = str_replace(str_replace('\\', '/', $uploadsDir['basedir']), $uploadsDir['baseurl'], $uploadPath);
            } else {
                $uploadUrl = str_replace($uploadsDir['basedir'], $uploadsDir['baseurl'], $uploadPath);
            }
            return $uploadUrl;
        }

    /**
         * Helper function that convert hex color to rgba
         *
         * @param $color
         * @param bool $opacity
         *
         * @return string
         */
        public static function hex2rgba($color, $opacity = false)
        {
    
            $default = 'rgb(0,0,0)';
    
            //Return default if no color provided
            if (empty($color))
                return $default;
    
            //Sanitize $color if "#" is provided
            if ($color[0] == '#') {
                $color = substr($color, 1);
            }
    
            //Check if color has 6 or 3 characters and get values
            if (strlen($color) == 6) {
                $hex = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
            } elseif (strlen($color) == 3) {
                $hex = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
            } else {
                return $default;
            }
    
            //Convert hexadec to rgb
            $rgb = array_map('hexdec', $hex);
    
            //Check if opacity is set(rgba or rgb)
            if ($opacity) {
                if (abs($opacity) > 1)
                    $opacity = 1.0;
                $output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
            } else {
                $output = 'rgb(' . implode(",", $rgb) . ')';
            }
    
            //Return rgb(a) color string
            return $output;
        }

    /**
         * Helper function that checks if given string is a valid color (hex, rgba, rgb, hsla)
         *
         * @param $color
         *
         * @return bool
         */
        public static function isStringAColor($color)
        {
    
            $regex = '/^(\#[\da-f]{3}|\#[\da-f]{6}|rgba\(((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*,\s*){2}((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*)(,\s*(0\.\d+|1))\)|hsla\(\s*((\d{1,2}|[1-2]\d{2}|3([0-5]\d|60)))\s*,\s*((\d{1,2}|100)\s*%)\s*,\s*((\d{1,2}|100)\s*%)(,\s*(0\.\d+|1))\)|rgb\((?:\s*\d+\s*,){2}\s*[\d]+\)|hsl\(\s*((\d{1,2}|[1-2]\d{2}|3([0-5]\d|60)))\s*,\s*((\d{1,2}|100)\s*%)\s*,\s*((\d{1,2}|100)\s*%)\))$/i';
    
            return preg_match($regex, $color);
        }

    /**
         * Extract Domain from user serve name
         *
         * @param $domain
         *
         * @return mixed
         */
        public static function extractDomain($domain)
        {
            if (!file_exists(WDT_ROOT_PATH . 'templates/admin/settings/top_level_domains_base.inc.php'))
                return '';
            $topLevelDomainsJSON = require(WDT_ROOT_PATH . 'templates/admin/settings/top_level_domains_base.inc.php');
            $topLevelDomains = json_decode($topLevelDomainsJSON, true);
            $tempDomain = '';
    
            $extractDomainArray = explode('.', $domain);
            for ($i = 0; $i <= count($extractDomainArray); $i++) {
                $slicedDomainArray = array_slice($extractDomainArray, $i);
                $slicedDomainString = implode('.', $slicedDomainArray);
    
                if (in_array($slicedDomainString, $topLevelDomains)) {
                    $tempDomain = array_slice($extractDomainArray, $i - 1);
                    break;
                }
            }
            if ($tempDomain == '') {
                $tempDomain = $extractDomainArray;
            }
    
            return implode('.', $tempDomain);
        }

    /**
         * Extract subomain from user serve name
         *
         * @param $domain
         *
         * @return string
         */
        public static function extractSubdomain($domain)
        {
            $host = explode('.', $domain);
            $domain = self::extractDomain($domain);
            $domain = explode('.', $domain);
            return implode('.', array_diff($host, $domain));
        }

    /**
         * Check if serve name is IPv4 or Ipv6
         *
         * @param $domain
         *
         * @return boolean
         */
        public static function isIP($domain)
        {
            if (preg_match("/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/", $domain) ||
                preg_match("/^((?:[0-9A-Fa-f]{1,4}))((?::[0-9A-Fa-f]{1,4}))*::((?:[0-9A-Fa-f]{1,4}))((?::[0-9A-Fa-f]{1,4}))*|((?:[0-9A-Fa-f]{1,4}))((?::[0-9A-Fa-f]{1,4})){7}$/", $domain)) {
                return true;
            } else {
                return false;
            }
        }

    /**
         * Remove all variants www from server name
         *
         * @param $url
         *
         * @return string
         */
        public static function removeWWW($url)
        {
            if (in_array(substr($url, 0, 5), ['www1.', 'www2.', 'www3.', 'www4.'])) {
                return substr_replace($url, "", 0, 5);
            } else if (substr($url, 0, 4) === 'www.') {
                return substr_replace($url, "", 0, 4);
            }
            return $url;
        }

    /**
         * Get filtered domain
         *
         * @param $domain
         *
         * @return string
         */
        public static function getDomain($domain)
        {
            $domain = self::isIP($domain) ? $domain : self::extractDomain(self::removeWWW($domain));
            return $domain;
        }

    /**
         * Get filtered subdomain
         *
         * @param $subdomain
         *
         * @return string
         */
        public static function getSubDomain($subdomain)
        {
            $subdomain = self::isIP($subdomain) ? '' : self::extractSubdomain(self::removeWWW($subdomain));
            return $subdomain;
        }
}
