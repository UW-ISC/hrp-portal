<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Infrastructure\Http;

use Exception;

/**
 * Remote HTTP fetch and placeholder application.
 *
 * @package WPDataTables\Infrastructure\Http
 */
class HttpClient
{
    /**
         * Helper function for applying placeholders(variables)
         *
         * @param $string
         *
         * @return mixed
         */
        public static function applyPlaceholders($string)
        {
            if (defined('WDT_PH_INTEGRATION')) {
                return \WDTIntegration\Placeholders::maybeApply($string);
            }
    
            return $string;
    
        }

    /**
         * Helper function that returns curl data
         *
         * @param $url
         *
         * @return mixed|null
         * @throws Exception
         */
        public static function curlGetData($url)
        {
            $ch = curl_init();
            $timeout = 100;
            $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36';
    
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_USERAGENT, $agent);
            curl_setopt($ch, CURLOPT_REFERER, site_url());
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
            $data = apply_filters('wpdatatables_curl_get_data', null, $ch, $url);
            if (null === $data) {
                $data = curl_exec($ch);
                if (curl_error($ch)) {
                    $error = curl_error($ch);
                    curl_close($ch);
    
                    throw new Exception($error);
                }
                if (strpos($data, '<TITLE>Moved Temporarily</TITLE>') ||
                    strpos($data, 'Error 400 (Bad Request)')) {
                    throw new Exception(__('wpDataTables was unable to read your Google Spreadsheet, as it\'s not been published correctly. <br/> You can publish it by going to <b>File ->Share -> Publish to the web</b> ', 'wpdatatables'));
                }
                $info = curl_getinfo($ch);
                curl_close($ch);
    
                if ($info['http_code'] === 404) {
                    return NULL;
                }
                if ($info['http_code'] === 401) {
                    throw new Exception(__('wpDataTables was unable to access data. Unauthorized access. Please make file accessible.', 'wpdatatables'));
                }
    
                $data = apply_filters('wpdatatables_curl_get_data_complete', $data, $url);
            }
            return $data;
        }
}
