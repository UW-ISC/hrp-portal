<?php

defined('ABSPATH') or die('Access denied.');

class WDTTools
{

    public static $jsVars = array();

    /**
     * Helper function that returns array of possible column types
     * @return array
     */
    public static function getPossibleColumnTypes(): array
    {
        return array(
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
    }

    /**
     * Helper function that sanitize column header
     * @param $headersInFormula
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
     * Helper function for applying placeholders(variables)
     * @param $string
     * @return mixed
     */
    public static function applyPlaceholders($string)
    {
        global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9, $wpdb;

        $table = isset($_POST['table']) ? json_decode(stripslashes($_POST['table'])) : null;

        // Placeholders
        if (strpos($string, '%CURRENT_USER_ID%') !== false) {
            if (isset($table->currentUserIdPlaceholder)) {
                $currentUserIdPlaceholder = $table->currentUserIdPlaceholder;
            } elseif (isset($_POST['currentUserId'])) {
                $currentUserIdPlaceholder = $_POST['currentUserId'];
            }

            $wdtCurUserId = $currentUserIdPlaceholder ?? get_current_user_id();

            $string = str_replace('%CURRENT_USER_ID%', $wdtCurUserId, $string);
        }
        if (strpos($string, '%CURRENT_USER_LOGIN%') !== false) {
            if (isset($table->currentUserLoginPlaceholder)) {
                $currentUserLoginPlaceholder = $table->currentUserLoginPlaceholder;
            } elseif (isset($_POST['currentUserLogin'])) {
                $currentUserLoginPlaceholder = $_POST['currentUserLogin'];
            }

            $wdtCurUserLogin = $currentUserLoginPlaceholder ?? wp_get_current_user()->user_login;

            $string = str_replace('%CURRENT_USER_LOGIN%', "{$wdtCurUserLogin}", $string);
        }
        if (strpos($string, '%CURRENT_POST_ID%') !== false) {
            if (isset($table->currentPostIdPlaceholder)) {
                $currentPostIdPlaceholder = $table->currentPostIdPlaceholder;
            } elseif (isset($_POST['currentPostIdPlaceholder'])) {
                $currentPostIdPlaceholder = $_POST['currentPostIdPlaceholder'];
            }

            $url = wp_get_referer();
            $postID = url_to_postid($url);

            $wdtCurPostId = $currentPostIdPlaceholder ?? (!empty($postID) ? $postID : get_the_ID());

            $string = str_replace('%CURRENT_POST_ID%', $wdtCurPostId, $string);
        }
        if (strpos($string, '%CURRENT_USER_DISPLAY_NAME%') !== false) {
            if (isset($table->currentUserDisplayNamePlaceholder)) {
                $currentUserDisplayNamePlaceholder = $table->currentUserDisplayNamePlaceholder;
            } elseif (isset($_POST['currentUserDisplayName'])) {
                $currentUserDisplayNamePlaceholder = $_POST['currentUserDisplayName'];
            }

            $wdtCurUserDisplayName = $currentUserDisplayNamePlaceholder ?? wp_get_current_user()->display_name;

            $string = str_replace('%CURRENT_USER_DISPLAY_NAME%', "{$wdtCurUserDisplayName}", $string);
        }
        if (strpos($string, '%CURRENT_USER_FIRST_NAME%') !== false) {
            if (isset($table->currentUserFirstNamePlaceholder)) {
                $currentUserFirstNamePlaceholder = $table->currentUserFirstNamePlaceholder;
            } elseif (isset($_POST['currentUserFirstName'])) {
                $currentUserFirstNamePlaceholder = $_POST['currentUserFirstName'];
            }

            $wdtCurUserFirstName = $currentUserFirstNamePlaceholder ?? wp_get_current_user()->user_firstname;

            $string = str_replace('%CURRENT_USER_FIRST_NAME%', "{$wdtCurUserFirstName}", $string);
        }
        if (strpos($string, '%CURRENT_USER_LAST_NAME%') !== false) {
            if (isset($table->currentUserLastNamePlaceholder)) {
                $currentUserLastNamePlaceholder = $table->currentUserLastNamePlaceholder;
            } elseif (isset($_POST['currentUserLastName'])) {
                $currentUserLastNamePlaceholder = $_POST['currentUserLastName'];
            }

            $wdtCurUserLastName = $currentUserLastNamePlaceholder ?? wp_get_current_user()->user_lastname;

            $string = str_replace('%CURRENT_USER_LAST_NAME%', "{$wdtCurUserLastName}", $string);
        }
        if (strpos($string, '%CURRENT_USER_EMAIL%') !== false) {
            if (isset($table->currentUserEmailPlaceholder)) {
                $currentUserEmailPlaceholder = $table->currentUserEmailPlaceholder;
            } elseif (isset($_POST['currentUserEmail'])) {
                $currentUserEmailPlaceholder = $_POST['currentUserEmail'];
            }

            $wdtCurUserEmail = $currentUserEmailPlaceholder ?? wp_get_current_user()->user_email;

            $string = str_replace('%CURRENT_USER_EMAIL%', "{$wdtCurUserEmail}", $string);
        }
        if (strpos($string, '%CURRENT_DATE%') !== false) {

            $wdtCurDate = current_time('Y-m-d');

            $string = str_replace('%CURRENT_DATE%', "{$wdtCurDate}", $string);
        }
        if (strpos($string, '%CURRENT_DATETIME%') !== false) {

            $wdtCurDateTime = current_time('Y-m-d') . ' ' . current_time('H:i');

            $string = str_replace('%CURRENT_DATETIME%', "{$wdtCurDateTime}", $string);
        }
        if (strpos($string, '%CURRENT_TIME%') !== false) {

            $wdtCurTime = current_time('H:i');

            $string = str_replace('%CURRENT_TIME%', "{$wdtCurTime}", $string);
        }
        if (strpos($string, '%WPDB%') !== false) {
            if (isset($table->wpdbPlaceholder)) {
                $wpdbPlaceholder = $table->wpdbPlaceholder;
            } elseif (isset($_POST['wpdbPlaceholder'])) {
                $wpdbPlaceholder = $_POST['wpdbPlaceholder'];
            }

            $wpdbPrefix = $wpdbPlaceholder ?? $wpdb->prefix;

            $string = str_replace('%WPDB%', $wpdbPrefix, $string);
        }
        // Shortcode VAR1
        if (strpos($string, '%VAR1%') !== false) {
            $string = str_replace('%VAR1%', addslashes($wdtVar1), $string);
        }

        // Shortcode VAR2
        if (strpos($string, '%VAR2%') !== false) {
            $string = str_replace('%VAR2%', addslashes($wdtVar2), $string);
        }

        // Shortcode VAR3
        if (strpos($string, '%VAR3%') !== false) {
            $string = str_replace('%VAR3%', addslashes($wdtVar3), $string);
        }

        // Shortcode VAR4
        if (strpos($string, '%VAR4%') !== false) {
            $string = str_replace('%VAR4%', addslashes($wdtVar4), $string);
        }

        // Shortcode VAR5
        if (strpos($string, '%VAR5%') !== false) {
            $string = str_replace('%VAR5%', addslashes($wdtVar5), $string);
        }

        // Shortcode VAR6
        if (strpos($string, '%VAR6%') !== false) {
            $string = str_replace('%VAR6%', addslashes($wdtVar6), $string);
        }

        // Shortcode VAR7
        if (strpos($string, '%VAR7%') !== false) {
            $string = str_replace('%VAR7%', addslashes($wdtVar7), $string);
        }

        // Shortcode VAR8
        if (strpos($string, '%VAR8%') !== false) {
            $string = str_replace('%VAR8%', addslashes($wdtVar8), $string);
        }

        // Shortcode VAR9
        if (strpos($string, '%VAR9%') !== false) {
            $string = str_replace('%VAR9%', addslashes($wdtVar9), $string);
        }

        return $string;

    }
    /**
     * Helper function for getting hidden dynamic value
     * @param $value
     * @return string
     */
    public static function getHiddenDefaultValues($value, $tableData): string
    {
        global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9;

        $arePlaceholdersInUrl = isset($_POST['wdtAjaxURL']) ? sanitize_url($_POST['wdtAjaxURL']) : '';
        if ( $tableData != null ){

            $wdtVar1 = $tableData->var1;
            $wdtVar2 = $tableData->var2;
            $wdtVar3 = $tableData->var3;
            $wdtVar4 = $tableData->var4;
            $wdtVar5 = $tableData->var5;
            $wdtVar6 = $tableData->var6;
            $wdtVar7 = $tableData->var7;
            $wdtVar8 = $tableData->var8;
            $wdtVar9 = $tableData->var9;

            if ($arePlaceholdersInUrl) {
                $urlComponents = parse_url($arePlaceholdersInUrl);
                parse_str($urlComponents['query'], $params);
                if (isset($params['wdt_var1']))
                    $wdtVar1 = sanitize_text_field(wdtSanitizeQuery($params['wdt_var1']));
                if (isset($params['wdt_var2']))
                    $wdtVar2 = sanitize_text_field(wdtSanitizeQuery($params['wdt_var2']));
                if (isset($params['wdt_var3']))
                    $wdtVar3 = sanitize_text_field(wdtSanitizeQuery($params['wdt_var3']));
                if (isset($params['wdt_var4']))
                    $wdtVar4 = sanitize_text_field(wdtSanitizeQuery($params['wdt_var4']));
                if (isset($params['wdt_var5']))
                    $wdtVar5 = sanitize_text_field(wdtSanitizeQuery($params['wdt_var5']));
                if (isset($params['wdt_var6']))
                    $wdtVar6 = sanitize_text_field(wdtSanitizeQuery($params['wdt_var6']));
                if (isset($params['wdt_var7']))
                    $wdtVar7 = sanitize_text_field(wdtSanitizeQuery($params['wdt_var7']));
                if (isset($params['wdt_var8']))
                    $wdtVar8 = sanitize_text_field(wdtSanitizeQuery($params['wdt_var8']));
                if (isset($params['wdt_var9']))
                    $wdtVar9 = sanitize_text_field(wdtSanitizeQuery($params['wdt_var9']));
            }
        }

        $defaultHiddenValue = '';
        $url          = wp_get_referer();
        $postID       = url_to_postid( $url );
        $embedUrl     = esc_url( get_permalink( $postID ) );
        if (strpos($value,'acf-data:') !== false){
            $defaultHiddenValue = $value;
            $value = 'acf-data';
        }
        if (strpos($value,'post-meta:') !== false){
            $defaultHiddenValue = $value;
            $value = 'post-meta';
        }
        if (strpos($value,'query-param:') !== false){
            $defaultHiddenValue = $value;
            $value = 'query-param';
        }

        switch ( $value ) {
            case 'user-ip':
                $defaultHiddenValue = self::getUserIP();
                break;
            case 'user-id':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%CURRENT_USER_ID%' );
                break;
            case 'user-display-name':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%CURRENT_USER_DISPLAY_NAME%' );
                break;
            case 'user-first-name':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%CURRENT_USER_FIRST_NAME%' );
                break;
            case 'user-last-name':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%CURRENT_USER_LAST_NAME%' );
                break;
            case 'user-email':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%CURRENT_USER_EMAIL%' );
                break;
            case 'user-login':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%CURRENT_USER_LOGIN%' );
                break;
            case 'date':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%CURRENT_DATE%' );
                break;
            case 'datetime':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%CURRENT_DATETIME%' );
                break;
            case 'time':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%CURRENT_TIME%' );
                break;
            case 'p-var1':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%VAR1%' );
                break;
            case 'p-var2':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%VAR2%' );
                break;
            case 'p-var3':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%VAR3%' );
                break;
            case 'p-var4':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%VAR4%' );
                break;
            case 'p-var5':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%VAR5%' );
                break;
            case 'p-var6':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%VAR6%' );
                break;
            case 'p-var7':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%VAR7%' );
                break;
            case 'p-var8':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%VAR8%' );
                break;
            case 'p-var9':
                $defaultHiddenValue = WDTTools::applyPlaceholders( '%VAR9%' );
                break;
            case 'post-id':
                $defaultHiddenValue = self::getPostData('ID');;
                break;
            case 'post-title':
                $defaultHiddenValue = self::getPostData( 'post_title' );
                break;
            case 'post-author':
                $defaultHiddenValue = self::getPostData( 'post_author' );
                break;
            case 'post-type':
                $defaultHiddenValue = self::getPostData( 'post_type' );
                break;
            case 'post-status':
                $defaultHiddenValue = self::getPostData( 'post_status' );
                break;
            case 'post-parent':
                $defaultHiddenValue = self::getPostData( 'post_parent' );
                break;
            case 'post-meta':
                $defaultHiddenValue = self::getPostMetaData($defaultHiddenValue);
                break;
            case 'acf-data':
                $defaultHiddenValue = self::getACFData($defaultHiddenValue);
                break;
            case 'post-url':
                $defaultHiddenValue = $embedUrl;
                break;
            case 'user-agent':
                $defaultHiddenValue = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash(( $_SERVER['HTTP_USER_AGENT'] ))) : '';
                break;
            case 'refer-url':
                $defaultHiddenValue = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_url( $_SERVER['HTTP_REFERER'] ) : $embedUrl;
                break;
            case 'query-param':
                $defaultHiddenValue = self::getQueryParam($defaultHiddenValue);
                break;
            default:
                break;
        }

        return apply_filters( 'wpdatatables_default_hidden_column_value', $defaultHiddenValue, $value );
    }

    /**
     * Helper function to get query param
     * @return string
     */
    public static function getQueryParam ($value): string
    {
        $queryKeyValue = '';
        $queryValue = str_replace('query-param:','', $value);

        if(isset( $_REQUEST['queryParams'] ) && is_array($_REQUEST['queryParams'])){
            $queryParamsArray = $_REQUEST['queryParams'];
            if ( $queryValue ) {
                if ( isset( $queryParamsArray[$queryValue] )) {
                    $queryKeyValue = sanitize_text_field(esc_sql(rawurldecode($queryParamsArray[$queryValue])));
                }
            }
        }

        return $queryKeyValue;
    }

    /**
     * Helper function to get post meta data
     * @return string
     */
    public static function getPostMetaData ($value): string
    {
        $metaValue = '';
        $metaKey = str_replace('post-meta:','', $value);
        $postID = self::getPostData('ID');
        if ($postID){
            if(get_post_meta( $postID, $metaKey, true )) {
                $metaValue = get_post_meta( $postID, $metaKey, true );
                $metaValue = sanitize_text_field($metaValue);
                if (!$metaValue)
                    return '';
            }
        }


        return $metaValue;
    }

    /**
     * Helper function to get ACF data
     * @return string
     */
    public static function getACFData ($value): string
    {
        $acfValue = '';
        $acfReturnValue = '';
        $postID = '';
        $acfKey = str_replace('acf-data:','', $value);
        if ($acfKey){
            $postID = self::getPostData('ID');
            if ($postID) {
                if (class_exists('ACF') && function_exists('get_field')) {
                    $acfReturnValue = get_field($acfKey, $postID);
                    if ($acfReturnValue) {
                        if (is_object($acfReturnValue)) {
                            if ($acfReturnValue instanceof WP_Post){
                                $acfValue = sanitize_text_field($acfReturnValue->post_title);
                            }
                        } else if (is_array($acfReturnValue)) {
                            $acfReturnValue = implode(', ', $acfReturnValue);
                            $acfValue = sanitize_text_field($acfReturnValue);

                            if (!$acfValue) {
                                return '';
                            }
                        } else {
                            $acfValue = sanitize_text_field($acfReturnValue);
                        }
                    }
                }
            } else {
                if (class_exists('ACF') && function_exists('acf_get_field')) {
                    $acfDefaultValues = acf_get_field($acfKey)['default_value'];
                    if ($acfDefaultValues) {
                        if (is_array($acfDefaultValues)) {
                            $acfValue = implode(',', $acfDefaultValues);
                        } else if (is_string($acfDefaultValues)) {
                            $acfValue = $acfDefaultValues;
                        }
                    }
                }
            }
        }

        return apply_filters( 'wpdatatables_default_hidden_acf_value', $acfValue, $acfKey, $acfReturnValue, $postID );
    }

    /**
     * Helper function to get user IP
     * @return string|null
     */
    public static function getUserIP (): ?string
    {
        $client  = isset( $_SERVER['HTTP_CLIENT_IP'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) ) : null;
        $forward = isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) : null;
        $is_cf   = self::isCloudflare(); // Check if request is from CloudFlare.
        if ( $is_cf ) {
            $cf_ip = isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) : null; // We already make sure this is set in the checks.
            if ( filter_var( $cf_ip, FILTER_VALIDATE_IP ) ) {
                return  $cf_ip;
            }
        } else {
            $remote = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : null;
        }
        $client_real = isset( $_SERVER['HTTP_X_REAL_IP'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) ) : null;
        $user_ip     = $remote;
        if ( filter_var( $client, FILTER_VALIDATE_IP ) ) {
            $user_ip = $client;
        } elseif ( filter_var( $client_real, FILTER_VALIDATE_IP ) ) {
            $user_ip = $client_real;
        } elseif ( ! empty( $forward ) ) {
            $forward = explode( ',', $forward );
            $ip      = array_shift( $forward );
            $ip      = trim( $ip );
            if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                $user_ip = $ip;
            }
        }

        return $user_ip;

    }

    /**
     * Check if the request is from cloudflare. If it is, we get the IP
     *
     * @return bool
     */
    private static function isCloudflare() {
        $ip = '';
        if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
        } elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
        } elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }
        if ( ! empty( $ip ) ) {
            $request_check = self::_cloudflareRequestsCheck();
            if ( ! $request_check ) {
                return false;
            }

            $ip_check = self::_validateCloudflareIP( $ip );

            return $ip_check;
        }

        return false;
    }

    /**
     * Validates that the IP that made the request is from cloudflare
     *
     * @param String $ip - the ip to check.
     *
     * @return bool
     */
    private static function _validateCloudflareIP( $ip ): bool
    {
        $cloudflare_ips = array(
            '199.27.128.0/21',
            '173.245.48.0/20',
            '103.21.244.0/22',
            '103.22.200.0/22',
            '103.31.4.0/22',
            '141.101.64.0/18',
            '108.162.192.0/18',
            '190.93.240.0/20',
            '188.114.96.0/20',
            '197.234.240.0/22',
            '198.41.128.0/17',
            '162.158.0.0/15',
            '104.16.0.0/12',
        );
        $is_cf_ip       = false;
        foreach ( $cloudflare_ips as $cloudflare_ip ) {
            if ( self::_cloudflareIPInRange( $ip, $cloudflare_ip ) ) {
                $is_cf_ip = true;
                break;
            }
        }

        return $is_cf_ip;
    }

    /**
     * Check if the cloudflare IP is in range
     *
     * @param String $ip - the current IP.
     * @param String $range - the allowed range of cloudflare ips.
     *
     * @return bool
     */
    private static function _cloudflareIPInRange( $ip, $range ) {
        if ( strpos( $range, '/' ) === false ) {
            $range .= '/32';
        }

        // $range is in IP/CIDR format eg 127.0.0.1/24.
        list( $range, $netmask ) = explode( '/', $range, 2 );
        $range_decimal           = ip2long( $range );
        $ip_decimal              = ip2long( $ip );
        $wildcard_decimal        = pow( 2, ( 32 - $netmask ) ) - 1;
        $netmask_decimal         = ~$wildcard_decimal;

        return ( ( $ip_decimal & $netmask_decimal ) === ( $range_decimal & $netmask_decimal ) );
    }

    /**
     * Check if there are any cloudflare headers in the request
     *
     * @return bool
     */
    private static function _cloudflareRequestsCheck() {
        $flag = true;

        if ( ! isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
            $flag = false;
        }
        if ( ! isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
            $flag = false;
        }
        if ( ! isset( $_SERVER['HTTP_CF_RAY'] ) ) {
            $flag = false;
        }
        if ( ! isset( $_SERVER['HTTP_CF_VISITOR'] ) ) {
            $flag = false;
        }

        return $flag;
    }

    /**
     * Helper function to get post data
     * @param $property
     * @param $post_id
     * @param $default
     * @return mixed|null
     */
    public static function getPostData ($property, $post_id = null, $default = '' )
    {
        global $post;

        if ( $post_id ) {
            $post_object = get_post( $post_id );
            if ( $post_object instanceof WP_Post ) {
                $post = $post_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
            }
        }

        if ( ! $post ) {
            $wp_referer = wp_get_referer();
            if ( $wp_referer ) {
                $post_id = url_to_postid( $wp_referer );
                if ( $post_id ) {
                    $post_object = get_post( $post_id );
                    if ( $post_object instanceof WP_Post ) {
                        $post = $post_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                    }
                }
            }
        }

        $post_data = self::objectToArray( $post );

        return $post_data[$property] ?? $default;
    }

    /**
     * Convert object to array
     *
     * @param $object
     *
     * @return array
     */
    public static function objectToArray( $object ) {
        $array = array();

        if ( empty( $object ) ) {
            return $array;
        }

        foreach ( $object as $key => $value ) {
            $array[ $key ] = $value;
        }

        return $array;
    }

    /**
     * Helper function that returns curl data
     * @param $url
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
        if( null === $data ) {
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

    /**
     * Helper function to find CSV delimiter
     * @param $csv_url
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
     * @param $csv
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
     * @param $arr
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
            if (count($arr[$j]) < $countLabels){
                for ($k = 0; $k < $countLabels; $k++){
                    if(!isset($arr[$j][$k])){
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
     * @param $url
     * @return string
     */
    public static function getGoogleSpreadsheetID($url)
    {
        $url_arr = explode('/', $url);
        return $url_arr[count($url_arr) - 2];
    }
    /**
     * Helper function that extract Google Spreadsheet URL and get Worksheets ID
     * @param $url
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
     * @param $url
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
        $csv_url = "https://docs.google.com/spreadsheets/d/{$spreadsheet_key}/pub?hl=en_US&hl=en_US&single=true&output=csv";
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
        $csv_data = WDTTools::curlGetData($csv_url);
        if (!is_null($csv_data)) {
            return WDTTools::csvToArray($csv_data);
        }

        return array();
    }
    public static function getTranslationStringsBrowse()
    {
        return array(
            'deleteSelected' => __('Delete selected', 'wpdatatables'),
            'delete' => __('Delete', 'wpdatatables'),
            'copy' => __('Copy', 'wpdatatables'),
        );
    }
    public static function getTranslationStringsCommon()
    {
        return array(
            'success' => __('Success!', 'wpdatatables'),
            'error' => __('Error!', 'wpdatatables'),
            'settings_saved_error' => __('Unable to save settings of plugin. Please try again or contact us over Support page.', 'wpdatatables'),
            'close' => __('Close', 'wpdatatables'),
            'tableNameEmpty' => __('Table name can not be empty! Please provide a name for your table.', 'wpdatatables'),
            'tableSaved' => __('Table saved successfully!', 'wpdatatables'),
            'selectExcelCsv' => __('Select an Excel or CSV file', 'wpdatatables'),
            'choose_file' => __('Use selected file', 'wpdatatables'),
            'chooseFile' => __('Choose file', 'wpdatatables'),
            'shortcodeSaved' => __('Shortcode has been copied to the clipboard.', 'wpdatatables'),
            'dataSaved' => __('Data has been saved!', 'wpdatatables'),
            'databaseInsertError' => __('There was an error trying to insert a new row!', 'wpdatatables'),
            'databaseDeleteError' => __('There was an error trying to delete a row!', 'wpdatatables'),
            'rowDeleted' => __('Row has been deleted!', 'wpdatatables'),
            'systemInfoSaved' => __('System info data has been copied to the clipboard. You can now paste it in file or in support ticket.', 'wpdatatables'),
            'selected_replace_data_option' => __("<small>You've selected the <strong>'Replace rows with source data'</strong> option. This means that you're about to <strong>delete all the data</strong> you currently have in your table and replace it with data from your source file.<br><br> If you have any <strong>date type columns</strong> in your file, please make sure you set the <strong>date input format in Main settings of plugin</strong> to the one you're using in your source file first.<br><br>Please consider <strong>duplicating your table first</strong>, before updating.<br><br><strong>There is no undo.</strong></small> ", "wpdatatables"),
            'selected_add_data_option' => __("<small>You've selected the <strong>'Add data to current table data'</strong> option. This means that you're about to <strong>add data</strong> from the file source to your table.<br><br> If you have any <strong>date type columns</strong> in your file, please make sure you set the <strong>date input format in Main settings of plugin</strong> to the one you're using in your source file first.<br><br>Please consider <strong>duplicating your table first</strong>, before updating.<br><br><strong>There is no undo.</strong></small>", "wpdatatables"),
            'selected_replace_table_option' => __("<small>You've selected the <strong>'Replace entire table data'</strong> option. This means that you're about to <strong>delete your entire table data and current column settings</strong> and replace it with data from your source file with default settings for columns.<br><br> If you have any <strong>date type columns</strong> in your file, please make sure you set the <strong>date input format in Main settings of plugin</strong> to the one you're using in your source file first. <br><br>Please consider <strong>duplicating your table first</strong>, before updating.<br><br><strong>There is no undo.</strong></small> ", "wpdatatables"),
            'clear_table_data' => __('Clear table data', 'wpdatatables'),
            'delete' => __('Delete', 'wpdatatables'),
            'deleteSelected' => __('Delete selected', 'wpdatatables'),
            'getJsonRoots' => __('JSON roots are found!', 'wpdatatables'),
            'errorText' => __('Unable to retrieve results', 'wpdatatables'),
        );
    }
    public static function getTranslationStringsConstructor()
    {
        return array(
            'success' => __('Success!', 'wpdatatables'),
            'error' => __('Error!', 'wpdatatables'),
            'fileUploadEmptyFile' => __('Please upload or choose a file from Media Library!', 'wpdatatables'),
            'columnsEmpty' => __('Please select columns that you want to use in table', 'wpdatatables'),
            'tableNameEmpty' => __('Table name can not be empty! Please provide a name for your table.', 'wpdatatables'),
            'numberOfColumnsError' => __('Number of columns can not be empty or 0', 'wpdatatables'),
            'numberOfRowsError' => __('Number of rows can not be empty or 0', 'wpdatatables'),
            'newColumnName' => __('New column', 'wpdatatables'),
            'selectAll' => __('Select all', 'wpdatatables'),
            'deselectAll' => __('Deselect all', 'wpdatatables'),
        );
    }
    public static function getTranslationStringsSimpleTable()
    {
        return array(
            'success' => __('Success!', 'wpdatatables'),
            'error' => __('Error!', 'wpdatatables'),
            'tableSaved' => __('Table saved successfully!', 'wpdatatables'),
            'clear_table_data' => __('Clear table data', 'wpdatatables'),
            'star_rating' => __('Star rating', 'wpdatatables'),
            'shortcode' => __('Shortcode', 'wpdatatables'),
            'html_code' => __('HTML code', 'wpdatatables'),
            'media' => __('Media', 'wpdatatables'),
            'link' => __('Link', 'wpdatatables'),
            'clip' => __('Clip', 'wpdatatables'),
            'overflow' => __('Overflow', 'wpdatatables'),
            'wrap' => __('Wrap', 'wpdatatables'),
            'left' => __('Left', 'wpdatatables'),
            'center' => __('Center', 'wpdatatables'),
            'right' => __('Right', 'wpdatatables'),
            'justify' => __('Justify', 'wpdatatables'),
            'top' => __('Top', 'wpdatatables'),
            'middle' => __('Middle', 'wpdatatables'),
            'bottom' => __('Bottom', 'wpdatatables'),
            'insert_row_above' => __('Insert row above', 'wpdatatables'),
            'insert_row_below' => __('Insert row below', 'wpdatatables'),
            'remove_row' => __('Remove row', 'wpdatatables'),
            'insert_col_left' => __('Insert column left', 'wpdatatables'),
            'insert_col_right' => __('Insert column right', 'wpdatatables'),
            'remove_column' => __('Remove column', 'wpdatatables'),
            'alignment' => __('Alignment', 'wpdatatables'),
            'cut' => __('Cut', 'wpdatatables'),
            'insert_custom' => __('Insert custom', 'wpdatatables'),
            'undo' => __('Undo', 'wpdatatables'),
            'redo' => __('Redo', 'wpdatatables'),
            'text_wrapping' => __('Text wrapping', 'wpdatatables'),
            'merge_cells' => __('Merge cells', 'wpdatatables'),
            'copy' => __('Copy', 'wpdatatables'),
        );
    }
    public static function getTranslationStringsTableSettingsMain()
    {
        return array(
            'success' => __('Success!', 'wpdatatables'),
            'error' => __('Error!', 'wpdatatables'),
            'selected_replace_data_option' => __("<small>You've selected the <strong>'Replace rows with source data'</strong> option. This means that you're about to <strong>delete all the data</strong> you currently have in your table and replace it with data from your source file.<br><br> If you have any <strong>date type columns</strong> in your file, please make sure you set the <strong>date input format in Main settings of plugin</strong> to the one you're using in your source file first.<br><br>Please consider <strong>duplicating your table first</strong>, before updating.<br><br><strong>There is no undo.</strong></small> ", "wpdatatables"),
            'selected_add_data_option' => __("<small>You've selected the <strong>'Add data to current table data'</strong> option. This means that you're about to <strong>add data</strong> from the file source to your table.<br><br> If you have any <strong>date type columns</strong> in your file, please make sure you set the <strong>date input format in Main settings of plugin</strong> to the one you're using in your source file first.<br><br>Please consider <strong>duplicating your table first</strong>, before updating.<br><br><strong>There is no undo.</strong></small>", "wpdatatables"),
            'selected_replace_table_option' => __("<small>You've selected the <strong>'Replace entire table data'</strong> option. This means that you're about to <strong>delete your entire table data and current column settings</strong> and replace it with data from your source file with default settings for columns.<br><br> If you have any <strong>date type columns</strong> in your file, please make sure you set the <strong>date input format in Main settings of plugin</strong> to the one you're using in your source file first. <br><br>Please consider <strong>duplicating your table first</strong>, before updating.<br><br><strong>There is no undo.</strong></small> ", "wpdatatables"),
            'tableNameEmpty' => __('Table name can not be empty! Please provide a name for your table.', 'wpdatatables'),
            'tableSaved' => __('Table saved successfully!', 'wpdatatables'),
            'selectExcelCsv' => __('Select an Excel or CSV file', 'wpdatatables'),
            'chooseFile' => __('Choose file', 'wpdatatables'),
            'selectAll' => __('Select all', 'wpdatatables'),
            'deselectAll' => __('Deselect all', 'wpdatatables'),
            'getJsonRoots' => __('JSON roots are found!', 'wpdatatables'),
            'errorText' => __('Unable to retrieve results', 'wpdatatables'),
            'nothingSelected' => __('Nothing selected', 'wpdatatables'),
            'sLoadingRecords' => __('Loading...', 'wpdatatables'),
            'currentlySelected' => __('Currently selected', 'wpdatatables'),
            'search' => __('Search...', 'wpdatatables'),
            'statusInitialized' => __('Start typing a search query', 'wpdatatables'),
            'statusNoResults' => __('No Results', 'wpdatatables'),
            'statusTooShort' => __('Please enter more characters', 'wpdatatables'),
            'api_google_maps_ok' => __('Google Maps API key is valid!', 'wpdatatables'),
            'api_google_maps_not_ok' => __('There was an error while trying to save Google Maps API key!', 'wpdatatables'),
            'api_google_maps_removed' => __('Google Maps API key is removed!', 'wpdatatables'),
            'api_google_key_contains' => __('API key is valid', 'wpdatatables'),
            'validate_api' => __('Validate & Save', 'wpdatatables'),
            'remove_api' => __('Remove', 'wpdatatables'),
            'empty_api_google_key' => __('API key is not valid!', 'wpdatatables'),
            'settings_saved_successful' => __('Plugin settings saved successfully', 'wpdatatables'),
            'settings_saved_error' => __('Unable to save settings of plugin. Please try again or contact us over Support page.', 'wpdatatables'),
            'purchaseCodeInvalid' => __('The purchase code is invalid or it has expired', 'wpdatatables'),
            'activation_domains_limit' => __('You have reached maximum number of registered domains', 'wpdatatables'),
            'activation_envato_failed' => __('It seems you don\'t have a valid purchase of wpDataTables', 'wpdatatables'),
            'envato_failed_powerful' => __('It seems you don\'t have a valid purchase of Powerful Filters for wpDataTables', 'wpdatatables'), //*
            'envato_failed_report' => __('It seems you don\'t have a valid purchase of Report Builder for wpDataTables', 'wpdatatables'), //*
            'envato_failed_gravity' => __('It seems you don\'t have a valid purchase of Gravity Forms integration for wpDataTables', 'wpdatatables'), //*
            'envato_failed_formidable' => __('It seems you don\'t have a valid purchase of Formidable Forms integration for wpDataTables', 'wpdatatables'), //*
            'pluginActivated' => __('Plugin has been activated', 'wpdatatables'),
            'pluginDeactivated' => __('Plugin has been deactivated', 'wpdatatables'), //*
            'envato_api_activated' => __('Activated with Envato', 'wpdatatables'),
            'activateWithEnvato' => __('Activate with Envato', 'wpdatatables'),
            'unable_to_deactivate_plugin' => __('Unable to deactivate plugin. Please try again later.', 'wpdatatables'),
        );
    }
    public static function getTranslationStringsWpDataTables()
    {
        return array(
            'success' => __('Success!', 'wpdatatables'),
            'error' => __('Error!', 'wpdatatables'),
            'dataSaved' => __('Data has been saved!', 'wpdatatables'),
            'databaseInsertError' => __('There was an error trying to insert a new row!', 'wpdatatables'),
            'databaseDeleteError' => __('There was an error trying to delete a row!', 'wpdatatables'),
            'rowDeleted' => __('Row has been deleted!', 'wpdatatables'),
            'errorText' => __('Unable to retrieve results', 'wpdatatables'),
            'nothingSelected' => __('Nothing selected', 'wpdatatables'),
            'sLoadingRecords' => __('Loading...', 'wpdatatables'),
            'currentlySelected' => __('Currently selected', 'wpdatatables'),
            'search' => __('Search...', 'wpdatatables'),
            'statusInitialized' => __('Start typing a search query', 'wpdatatables'),
            'statusNoResults' => __('No Results', 'wpdatatables'),
            'statusTooShort' => __('Please enter more characters', 'wpdatatables'),
            'select_upload_file' => __('Select a file to use in table', 'wpdatatables'),
            'choose_file' => __('Use selected file', 'wpdatatables'),
            'chooseFile' => __('Choose file', 'wpdatatables'),
            'add_new_entry' => __('Add new entry', 'wpdatatables'),
            'duplicate_entry' => __('Duplicate entry', 'wpdatatables'),
            'edit_entry' => __('Edit entry', 'wpdatatables'),
            'invalid_email' => __('Please provide a valid e-mail address for field', 'wpdatatables'),
            'invalid_link' => __('Please provide a valid URL link for field', 'wpdatatables'),
            'cannot_be_empty' => __(' field cannot be empty!', 'wpdatatables'),
            'sInfo' => __('Showing _START_ to _END_ of _TOTAL_ entries', 'wpdatatables'),
            'sInfoEmpty' => __('Showing 0 to 0 of 0 entries', 'wpdatatables'),
            'sInfoFiltered' => __('(filtered from _MAX_ total entries)', 'wpdatatables'),
            'sInfoPostFix' => '',
            'sInfoThousands' => __(',', 'wpdatatables'),
            'sLengthMenu' => __('Show _MENU_ entries', 'wpdatatables'),
            'sLoadingRecords' => __('Loading...', 'wpdatatables'),
            'sProcessing' => __('Processing...', 'wpdatatables'),
            'sSearch' => __('Search: ', 'wpdatatables'),
            'sLengthMenu' => __('Show _MENU_ entries', 'wpdatatables'),
            'lengthMenu' => __('Show _MENU_ entries', 'wpdatatables'),
            'sEmptyTable' => __('No data available in table', 'wpdatatables'),
            'sZeroRecords' => __('No matching records found', 'wpdatatables'),
            'oAria' => array(
                'sSortAscending' => __(': activate to sort column ascending', 'wpdatatables'),
                'sSortDescending' => __(': activate to sort column descending', 'wpdatatables')
            ),
            'oPaginate' => array(
                'sFirst' => __('First', 'wpdatatables'),
                'sLast' => __('Last', 'wpdatatables'),
                'sNext' => __('Next', 'wpdatatables'),
                'sPrevious' => __('Previous', 'wpdatatables')
            ),
            'from' => __('From', 'wpdatatables'),
            'to' => __('To', 'wpdatatables'),
            'sortingError' => __('At least one show/hide sorting icon must be enabled!','wpdatatables'),
            'firstPageWCAG' => __('Navigate to First page', 'wpdatatables'),
            'lastPageWCAG' => __('Navigate to Last page', 'wpdatatables'),
            'nextPageWCAG' => __('Navigate to Next page', 'wpdatatables'),
            'previousPageWCAG' => __('Navigate to Previous page', 'wpdatatables'),
            'pageWCAG' => __('Navigate to wpDataTable Page ', 'wpdatatables'),
            'spacerWCAG' => __('Spacer', 'wpdatatables'),
            'printTableWCAG' => __('Print table', 'wpdatatables'),
            'exportTableWCAG' => __('Export table', 'wpdatatables'),
            'newEntryWCAG' => __('New entry', 'wpdatatables'),
            'deleteRowWCAG' => __('Delete row', 'wpdatatables'),
            'editRowWCAG' => __('Edit row', 'wpdatatables'),
            'duplicateRowWCAG' => __('Duplicate row', 'wpdatatables'),
            'clearFiltersWCAG' => __('Clear filters', 'wpdatatables'),
            'columnVisibilityWCAG' => __('Column visibility', 'wpdatatables'),
            'sInfoEmptyWCAG' => __('Showing 0 to 0 of 0 entries _COLUMN_ _DATA_', 'wpdatatables'),
            'sInfoWCAG' => __('Showing _START_ to _END_ of _TOTAL_ entries _COLUMN_ _DATA_', 'wpdatatables'),
            'masterDetailWCAG' => __('Master Detail', 'wpdatatables'),
            'globalSearchWCAG' => __('Global Search Table Input Field', 'wpdatatables'),
            'chooseExportWCAG' => __('Choose how to export table', 'wpdatatables'),
            'optionHideWCAG' => __('Option to either display or hide columns', 'wpdatatables'),
            'rowsPerPageWCAG' => __('Open dropdown menu for show rows per page', 'wpdatatables'),
            'forWCAG' => __('for ', 'wpdatatables'),
            'columnSearchWCAG' => __(' column searching for ', 'wpdatatables'),
            'valueFromWCAG' => __('value from ', 'wpdatatables'),
            'valueToWCAG' => __(' value to ', 'wpdatatables'),
            'andforWCAG' => __( ' and for ', 'wpdatatables'),
            'andforGloablWCAG' => __( ' and for Global search of value ', 'wpdatatables'),
            'forGloablWCAG' => __( 'for Global search of value ', 'wpdatatables'),
            'lenghtMenuWCAG' => __( 'Length menu:', 'wpdatatables'),
            'searchTableWCAG' => __( 'Search table:', 'wpdatatables'),
            'all' => __( 'All', 'wpdatatables'),
            'customDisplayError' => __( 'Invalid format of custom rows per page. Please enter a valid format like "1,2,3,4". If you use the number 0, it must be in the format 0 without any preceding zeros.', 'wpdatatables')
        );
    }
    public static function getTranslationStringsColumnFilter()
    {
        return array(
            'errorText' => __('Unable to retrieve results', 'wpdatatables'),
            'nothingSelected' => __('Nothing selected', 'wpdatatables'),
            'sLoadingRecords' => __('Loading...', 'wpdatatables'),
            'currentlySelected' => __('Currently selected', 'wpdatatables'),
            'search' => __('Search...', 'wpdatatables'),
            'statusInitialized' => __('Start typing a search query', 'wpdatatables'),
            'statusNoResults' => __('No Results', 'wpdatatables'),
            'statusTooShort' => __('Please enter more characters', 'wpdatatables'),
            'from' => __('From', 'wpdatatables'),
            'to' => __('To', 'wpdatatables'),
            'fromDate' => __('Date from', 'wpdatatables'),
            'toDate' => __('Date to', 'wpdatatables'),
            'fromDateTime' => __('DateTime from', 'wpdatatables'),
            'toDateTime' => __('DateTime to', 'wpdatatables'),
            'fromTime' => __('Time from', 'wpdatatables'),
            'toTime' => __('Time to', 'wpdatatables'),
            'filterInputString' => __('Filter input for ', 'wpdatatables'),
            'filterInputNumber' => __('Filter input for number range filter ', 'wpdatatables'),
            'filterInputDate' => __('Filter input for date picker ', 'wpdatatables'),
            'filterInputDateTime' => __('Filter input for datetime picker ', 'wpdatatables'),
            'filterInputTime' => __('Filter input for time picker ', 'wpdatatables'),
            'filterCheckbox' => __('Filter checkbox for ', 'wpdatatables'),
            'minValue' => __('Minimum Value: ', 'wpdatatables'),
            'maxValue' => __('Maximum Value: ', 'wpdatatables'),
            'multiSelectBoxOption' => __('MultiSelectBox option', 'wpdatatables'),
            'selectBoxOption' => __('SelectBox option', 'wpdatatables'),
            'dividerSearchBox' => __('This is divider between searchbox input and options to select', 'wpdatatables'),
        );
    }
    public static function getTranslationStringsExcel()
    {
        return array(
            'select_upload_file' => __('Select a file to use in table', 'wpdatatables'),
            'choose_file' => __('Use selected file', 'wpdatatables'),
            'chooseFile' => __('Choose file', 'wpdatatables'),
            'browse_file' => __('Browse', 'wpdatatables'),
            'detach_file' => __('detach', 'wpdatatables'),
        );
    }
    public static function getTranslationStringsExcelPlugin()
    {
        return array(
            'invalid_value' => __('You have entered invalid value. Press ESC to cancel.', 'wpdatatables'),
        );
    }
    public static function getTranslationStringsPlugin()
    {
        return array(
            'success' => __('Success!', 'wpdatatables'),
            'error' => __('Error!', 'wpdatatables'),
            'modalTitle' => __('Row details', 'wpdatatables'),
            'previousFilter' => __('Choose an option in previous filters', 'wpdatatables'),
        );
    }
    public static function getTranslationStringsFunctions()
    {
        return array(
            'sInfo' => __('Showing _START_ to _END_ of _TOTAL_ entries', 'wpdatatables'),
            'sInfoEmpty' => __('Showing 0 to 0 of 0 entries', 'wpdatatables'),
            'sInfoFiltered' => __('(filtered from _MAX_ total entries)', 'wpdatatables'),
            'sInfoPostFix' => '',
            'sInfoThousands' => __(',', 'wpdatatables'),
            'sLengthMenu' => __('Show _MENU_ entries', 'wpdatatables'),
            'sLoadingRecords' => __('Loading...', 'wpdatatables'),
            'sProcessing' => __('Processing...', 'wpdatatables'),
            'sSearch' => __('Search: ', 'wpdatatables'),
            'sLengthMenu' => __('Show _MENU_ entries', 'wpdatatables'),
            'lengthMenu' => __('Show _MENU_ entries', 'wpdatatables'),
            'sEmptyTable' => __('No data available in table', 'wpdatatables'),
            'sZeroRecords' => __('No matching records found', 'wpdatatables'),
            'oAria' => array(
                'sSortAscending' => __(': activate to sort column ascending', 'wpdatatables'),
                'sSortDescending' => __(': activate to sort column descending', 'wpdatatables')
            ),
            'oPaginate' => array(
                'sFirst' => __('First', 'wpdatatables'),
                'sLast' => __('Last', 'wpdatatables'),
                'sNext' => __('Next', 'wpdatatables'),
                'sPrevious' => __('Previous', 'wpdatatables')
            ),
            'nothingSelected' => __('Nothing selected', 'wpdatatables'),
        );
    }
    public static function getTranslationStringsInlineEditing()
    {
        return array(
            'invalid_email' => __('Please provide a valid e-mail address for field', 'wpdatatables'),
            'invalid_link' => __('Please provide a valid URL link for field', 'wpdatatables'),
            'cannot_be_empty' => __(' field cannot be empty!', 'wpdatatables'),
            'cannot_be_edit' => __('You can\'t edit this field', 'wpdatatables'),
            'errorText' => __('Unable to retrieve results', 'wpdatatables'),
            'nothingSelected' => __('Nothing selected', 'wpdatatables'),
            'sLoadingRecords' => __('Loading...', 'wpdatatables'),
            'currentlySelected' => __('Currently selected', 'wpdatatables'),
            'search' => __('Search...', 'wpdatatables'),
            'statusInitialized' => __('Start typing a search query', 'wpdatatables'),
            'statusNoResults' => __('No Results', 'wpdatatables'),
            'statusTooShort' => __('Please enter more characters', 'wpdatatables'),
            'selectFileAttachment' => __('Select file', 'wpdatatables'),
            'changeFileAttachment' => __('Change', 'wpdatatables'),
            'saveFileAttachment' => __('Save', 'wpdatatables'),
            'removeFileAttachment' => __('Remove', 'wpdatatables'),
            'select_upload_file' => __('Select a file to use in table', 'wpdatatables'),
            'choose_file' => __('Use selected file', 'wpdatatables'),
            'chooseFile' => __('Choose file', 'wpdatatables'),
            'inlineEditing' => __('Inline editing of the cell ', 'wpdatatables'),
        );
    }
    public static function getTranslationStringsAddRemoveColumn()
    {
        return array(
            'success' => __('Success!', 'wpdatatables'),
            'error' => __('Error!', 'wpdatatables'),
            'columnAdded' => __('Column has been added!', 'wpdatatables'),
            'columnHeaderEmpty' => __('Column header cannot be empty!', 'wpdatatables'),
            'outOfRangeTypeValue' => __('Type value is out-of-range!', 'wpdatatables'),
            'columnRemoveConfirm' => __('Please confirm column deletion!', 'wpdatatables'),
            'columnRemoved' => __('Column has been removed!', 'wpdatatables'),
            'columnsEmpty' => __('Please select columns that you want to use in table', 'wpdatatables'),
            'userId' => __('Current User ID', 'wpdatatables'),
            'userName' => __('Current User Name', 'wpdatatables'),
            'userFirstName' => __('Current User First Name', 'wpdatatables'),
            'userLastName' => __('Current User First Name', 'wpdatatables'),
            'userEmail' => __('Current User Email', 'wpdatatables'),
            'userLogin' => __('Current User Login', 'wpdatatables'),
            'userIPAddress' => __('Current User IP Address', 'wpdatatables'),
            'date' => __('Current Date', 'wpdatatables'),
            'datetime' => __('Current Datetime', 'wpdatatables'),
            'time' => __('Current Time', 'wpdatatables'),
            'pvar1' => __('Placeholder %VAR1%', 'wpdatatables'),
            'pvar2' => __('Placeholder %VAR2%', 'wpdatatables'),
            'pvar3' => __('Placeholder %VAR3%', 'wpdatatables'),
            'pvar4' => __('Placeholder %VAR4%', 'wpdatatables'),
            'pvar5' => __('Placeholder %VAR5%', 'wpdatatables'),
            'pvar6' => __('Placeholder %VAR6%', 'wpdatatables'),
            'pvar7' => __('Placeholder %VAR7%', 'wpdatatables'),
            'pvar8' => __('Placeholder %VAR8%', 'wpdatatables'),
            'pvar9' => __('Placeholder %VAR9%', 'wpdatatables'),
            'postId' => __('Post/Page ID', 'wpdatatables'),
            'postTitle' => __('Post/Page Title', 'wpdatatables'),
            'postCategory' => __('Post/Page Category', 'wpdatatables'),
            'postMeta' => __('Post/Page Meta Value', 'wpdatatables'),
            'postMetaString' => __('Post/Page Meta Value as string', 'wpdatatables'),
            'postTags' => __('Post/Page Tags', 'wpdatatables'),
            'postTerms' => __('Post/Page Terms', 'wpdatatables'),
            'loginUrl' => __('Login URL', 'wpdatatables'),
            'currentUrl' => __('Current URL', 'wpdatatables'),
            'userAgent' => __('HTTP User Agent', 'wpdatatables'),
            'referUrl' => __('HTTP Refer URL', 'wpdatatables'),
            'queryParam' => __('Query Parameter (GET)', 'wpdatatables'),
        );
    }
    public static function getTranslationStringsChartWizard()
    {
        return array(
            'selectAll' => __('Select all', 'wpdatatables'),
            'deselectAll' => __('Deselect all', 'wpdatatables'),
            'saveChart' => __('Save chart', 'wpdatatables'),
        );
    }
    /**
     * Helper function that returns array of translation strings used for localization of JavaScript files
     * @return array
     */
    public static function getTranslationStrings()
    {
        return array(
            'add_new_entry' => __('Add new entry', 'wpdatatables'),
            'duplicate_entry' => __('Duplicate entry', 'wpdatatables'),
            'back_to_date' => __('Back to date', 'wpdatatables'),
            'browse_file' => __('Browse', 'wpdatatables'),
            'cancel' => __('Cancel', 'wpdatatables'),
            'cannot_be_empty' => __(' field cannot be empty!', 'wpdatatables'),
            'cannot_be_edit' => __('You can\'t edit this field', 'wpdatatables'),
            'changeFileAttachment' => __('Change', 'wpdatatables'),
            'choose_file' => __('Use selected file', 'wpdatatables'),
            'chooseFile' => __('Choose file', 'wpdatatables'),
            'close' => __('Close', 'wpdatatables'),
            'columnAdded' => __('Column has been added!', 'wpdatatables'),
            'columnHeaderEmpty' => __('Column header cannot be empty!', 'wpdatatables'),
            'outOfRangeTypeValue' => __('Type value is out-of-range!', 'wpdatatables'),
            'columnRemoveConfirm' => __('Please confirm column deletion!', 'wpdatatables'),
            'columnRemoved' => __('Column has been removed!', 'wpdatatables'),
            'columnsEmpty' => __('Please select columns that you want to use in table', 'wpdatatables'),
            'copy' => __('Copy', 'wpdatatables'),
            'currentlySelected' => __('Currently selected', 'wpdatatables'),
            'databaseInsertError' => __('There was an error trying to insert a new row!', 'wpdatatables'),
            'databaseDeleteError' => __('There was an error trying to delete a row!', 'wpdatatables'),
            'dataSaved' => __('Data has been saved!', 'wpdatatables'),
            'delete' => __('Delete', 'wpdatatables'),
            'deleteSelected' => __('Delete selected', 'wpdatatables'),
            'detach_file' => __('detach', 'wpdatatables'),
            'edit_entry' => __('Edit entry', 'wpdatatables'),
            'error' => __('Error!', 'wpdatatables'),
            'errorText' => __('Unable to retrieve results', 'wpdatatables'),
            'fileUploadEmptyFile' => __('Please upload or choose a file from Media Library!', 'wpdatatables'),
            'from' => __('From', 'wpdatatables'),
            'getJsonRoots' => __('JSON roots are found!', 'wpdatatables'),
            'invalid_email' => __('Please provide a valid e-mail address for field', 'wpdatatables'),
            'invalid_link' => __('Please provide a valid URL link for field', 'wpdatatables'),
            'invalid_value' => __('You have entered invalid value. Press ESC to cancel.', 'wpdatatables'),
            'lengthMenu' => __('Show _MENU_ entries', 'wpdatatables'),
            'merge' => __('Merge', 'wpdatatables'),
            'modalTitle' => __('Row details', 'wpdatatables'),
            'newColumnName' => __('New column', 'wpdatatables'),
            'selectAll' => __('Select all', 'wpdatatables'),
            'deselectAll' => __('Deselect all', 'wpdatatables'),
            'nothingSelected' => __('Nothing selected', 'wpdatatables'),
            'numberOfColumnsError' => __('Number of columns can not be empty or 0', 'wpdatatables'),
            'numberOfRowsError' => __('Number of rows can not be empty or 0', 'wpdatatables'),
            'oAria' => array(
                'sSortAscending' => __(': activate to sort column ascending', 'wpdatatables'),
                'sSortDescending' => __(': activate to sort column descending', 'wpdatatables')
            ),
            'ok' => __('Ok', 'wpdatatables'),
            'oPaginate' => array(
                'sFirst' => __('First', 'wpdatatables'),
                'sLast' => __('Last', 'wpdatatables'),
                'sNext' => __('Next', 'wpdatatables'),
                'sPrevious' => __('Previous', 'wpdatatables')
            ),
            'previousFilter' => __('Choose an option in previous filters', 'wpdatatables'),
            'replace' => __('Replace', 'wpdatatables'),
            'removeFileAttachment' => __('Remove', 'wpdatatables'),
            'rowDeleted' => __('Row has been deleted!', 'wpdatatables'),
            'saveChart' => __('Save chart', 'wpdatatables'),
            'saveFileAttachment' => __('Save', 'wpdatatables'),
            'select_upload_file' => __('Select a file to use in table', 'wpdatatables'),
            'select_pdf_logo_file' => __('Select a file to use as the PDF logo', 'wpdatatables'),
            'selectFileAttachment' => __('Select file', 'wpdatatables'),
            'selectExcelCsv' => __('Select an Excel or CSV file', 'wpdatatables'),
            'sEmptyTable' => __('No data available in table', 'wpdatatables'),
            'settings_saved_successful' => __('Plugin settings saved successfully', 'wpdatatables'),
            'settings_saved_error' => __('Unable to save settings of plugin. Please try again or contact us over Support page.', 'wpdatatables'),
            'shortcodeSaved' => __('Shortcode has been copied to the clipboard.', 'wpdatatables'),
            'sInfo' => __('Showing _START_ to _END_ of _TOTAL_ entries', 'wpdatatables'),
            'sInfoEmpty' => __('Showing 0 to 0 of 0 entries', 'wpdatatables'),
            'sInfoFiltered' => __('(filtered from _MAX_ total entries)', 'wpdatatables'),
            'sInfoPostFix' => '',
            'sInfoThousands' => __(',', 'wpdatatables'),
            'sLengthMenu' => __('Show _MENU_ entries', 'wpdatatables'),
            'sLoadingRecords' => __('Loading...', 'wpdatatables'),
            'sProcessing' => __('Processing...', 'wpdatatables'),
            'sqlError' => __('SQL error', 'wpdatatables'),
            'sSearch' => __('Search: ', 'wpdatatables'),
            'statusInitialized' => __('Start typing a search query', 'wpdatatables'),
            'statusNoResults' => __('No Results', 'wpdatatables'),
            'statusTooShort' => __('Please enter more characters', 'wpdatatables'),
            'search' => __('Search...', 'wpdatatables'),
            'success' => __('Success!', 'wpdatatables'),
            'sZeroRecords' => __('No matching records found', 'wpdatatables'),
            'systemInfoSaved' => __('System info data has been copied to the clipboard. You can now paste it in file or in support ticket.', 'wpdatatables'),
            'tableSaved' => __('Table saved successfully!', 'wpdatatables'),
            'tableNameEmpty' => __('Table name can not be empty! Please provide a name for your table.', 'wpdatatables'),
            'to' => __('To', 'wpdatatables'),
            'purchaseCodeInvalid' => __('The purchase code is invalid or it has expired', 'wpdatatables'),
            'activation_domains_limit' => __('You have reached maximum number of registered domains', 'wpdatatables'),
            'activation_envato_failed' => __('It seems you don\'t have a valid purchase of wpDataTables', 'wpdatatables'),
            'envato_failed_powerful' => __('It seems you don\'t have a valid purchase of Powerful Filters for wpDataTables', 'wpdatatables'),
            'envato_failed_report' => __('It seems you don\'t have a valid purchase of Report Builder for wpDataTables', 'wpdatatables'),
            'envato_failed_gravity' => __('It seems you don\'t have a valid purchase of Gravity Forms integration for wpDataTables', 'wpdatatables'),
            'envato_failed_formidable' => __('It seems you don\'t have a valid purchase of Formidable Forms integration for wpDataTables', 'wpdatatables'),
            'pluginActivated' => __('Plugin has been activated', 'wpdatatables'),
            'pluginDeactivated' => __('Plugin has been deactivated', 'wpdatatables'),
            'envato_api_activated' => __('Activated with Envato', 'wpdatatables'),
            'activateWithEnvato' => __('Activate with Envato', 'wpdatatables'),
            'unable_to_deactivate_plugin' => __('Unable to deactivate plugin. Please try again later.', 'wpdatatables'),
            'selected_replace_data_option' => __("<small>You've selected the <strong>'Replace rows with source data'</strong> option. This means that you're about to <strong>delete all the data</strong> you currently have in your table and replace it with data from your source file.<br><br> If you have any <strong>date type columns</strong> in your file, please make sure you set the <strong>date input format in Main settings of plugin</strong> to the one you're using in your source file first.<br><br>Please consider <strong>duplicating your table first</strong>, before updating.<br><br><strong>There is no undo.</strong></small> ", "wpdatatables"),
            'selected_add_data_option' => __("<small>You've selected the <strong>'Add data to current table data'</strong> option. This means that you're about to <strong>add data</strong> from the file source to your table.<br><br> If you have any <strong>date type columns</strong> in your file, please make sure you set the <strong>date input format in Main settings of plugin</strong> to the one you're using in your source file first.<br><br>Please consider <strong>duplicating your table first</strong>, before updating.<br><br><strong>There is no undo.</strong></small>", "wpdatatables"),
            'selected_replace_table_option' => __("<small>You've selected the <strong>'Replace entire table data'</strong> option. This means that you're about to <strong>delete your entire table data and current column settings</strong> and replace it with data from your source file with default settings for columns.<br><br> If you have any <strong>date type columns</strong> in your file, please make sure you set the <strong>date input format in Main settings of plugin</strong> to the one you're using in your source file first. <br><br>Please consider <strong>duplicating your table first</strong>, before updating.<br><br><strong>There is no undo.</strong></small> ", "wpdatatables"),
            'clear_table_data' => __('Clear table data', 'wpdatatables'),
            'star_rating' => __('Star rating', 'wpdatatables'),
            'shortcode' => __('Shortcode', 'wpdatatables'),
            'html_code' => __('HTML code', 'wpdatatables'),
            'media' => __('Media', 'wpdatatables'),
            'link' => __('Link', 'wpdatatables'),
            'clip' => __('Clip', 'wpdatatables'),
            'overflow' => __('Overflow', 'wpdatatables'),
            'wrap' => __('Wrap', 'wpdatatables'),
            'left' => __('Left', 'wpdatatables'),
            'center' => __('Center', 'wpdatatables'),
            'right' => __('Right', 'wpdatatables'),
            'justify' => __('Justify', 'wpdatatables'),
            'top' => __('Top', 'wpdatatables'),
            'middle' => __('Middle', 'wpdatatables'),
            'bottom' => __('Bottom', 'wpdatatables'),
            'insert_row_above' => __('Insert row above', 'wpdatatables'),
            'insert_row_below' => __('Insert row below', 'wpdatatables'),
            'remove_row' => __('Remove row', 'wpdatatables'),
            'insert_col_left' => __('Insert column left', 'wpdatatables'),
            'insert_col_right' => __('Insert column right', 'wpdatatables'),
            'remove_column' => __('Remove column', 'wpdatatables'),
            'alignment' => __('Alignment', 'wpdatatables'),
            'cut' => __('Cut', 'wpdatatables'),
            'insert_custom' => __('Insert custom', 'wpdatatables'),
            'undo' => __('Undo', 'wpdatatables'),
            'redo' => __('Redo', 'wpdatatables'),
            'text_wrapping' => __('Text wrapping', 'wpdatatables'),
            'merge_cells' => __('Merge cells', 'wpdatatables'),
            'api_google_maps_ok' => __('Google Maps API key is valid!', 'wpdatatables'),
            'api_google_maps_not_ok' => __('There was an error while trying to save Google Maps API key!', 'wpdatatables'),
            'api_google_maps_removed' => __('Google Maps API key is removed!', 'wpdatatables'),
            'api_google_key_contains' => __('API key is valid', 'wpdatatables'),
            'validate_api' => __('Validate & Save', 'wpdatatables'),
            'remove_api' => __('Remove', 'wpdatatables'),
            'empty_api_google_key' => __('API key is not valid!', 'wpdatatables'),
            'sortingError' => __('At least one show/hide sorting icon must be enabled!','wpdatatables'),
            'firstPageWCAG' => __('First page', 'wpdatatables'),
            'lastPageWCAG' => __('Last page', 'wpdatatables'),
            'nextPageWCAG' => __('Next page', 'wpdatatables'),
            'previousPageWCAG' => __('Previous page', 'wpdatatables'),
            'pageWCAG' => __('wpDataTable Page ', 'wpdatatables'),
            'spacerWCAG' => __('Spacer', 'wpdatatables'),
            'printTableWCAG' => __('Print table', 'wpdatatables'),
            'exportTableWCAG' => __('Export table', 'wpdatatables'),
            'newEntryWCAG' => __('New entry', 'wpdatatables'),
            'deleteRowWCAG' => __('Delete row', 'wpdatatables'),
            'editRowWCAG' => __('Edit row', 'wpdatatables'),
            'duplicateRowWCAG' => __('Duplicate row', 'wpdatatables'),
            'clearFiltersWCAG' => __('Clear filters', 'wpdatatables'),
            );
    }
    /**
     * Helper function that returns all update info
     * TODO (Update before new versions)
     * @return array
     */
    public static function getUpdateInfo()
    {
        return array(
            'version'  => get_option('wdtVersion'),
            'features' => [
//                0 => [
//                    'text' => '',
//                    'link' => ''
//                ]
            ],
            'improvements' => [
//                0 => [
//                    'text' => '',
//                    'link' => ''
//                ]
            ],
            'bugfixes'=> [
                0 => [
                    'text' => 'Fixed issue with vulnerability for editable tables.',
                    'link' => ''
                ],
                1 => [
                    'text' => 'Fixed issue with empty columns (after last column data) from Excel and CSV files.',
                    'link' => ''
                ],
                2 => [
                    'text' => 'Fixed issue with saving table description for Simple tables.',
                    'link' => ''
                ],
                3 => [
                    'text' => 'Fixed issue with close element in modals for mojito skin.',
                    'link' => ''
                ],
                4 => [
                    'text' => 'Fixed typo in "Custom rows per page".',
                    'link' => ''
                ]
            ]
        );
    }

    /**
     * Helper function that returns an array with date and time settings from wp_options
     * @return array
     */
    public static function getDateTimeSettings()
    {
        return array(
            'wdtDateFormat'   => get_option('wdtDateFormat'),
            'wdtTimeFormat'   => get_option('wdtTimeFormat'),
            'wdtNumberFormat' => get_option('wdtNumberFormat')
        );
    }

    public static function getGoogleApiMapsKey()
    {
        return array(
            'wdtGoogleApiMaps'   => get_option('wdtGoogleApiMaps'),
            'wdtGoogleApiMapsValidated'   => get_option('wdtGoogleApiMapsValidated'),
        );
    }

    /**
     * Helper function that returns an array with wpDataTables admin pages
     * @return array
     */
    public static function getWpDataTablesAdminPages()
    {
        return array(
            'dashboardUrl'        => menu_page_url('wpdatatables-dashboard', false),
            'browseTablesUrl'     => menu_page_url('wpdatatables-administration', false),
            'browseChartsUrl'     => menu_page_url('wpdatatables-charts', false)
        );
    }

    /**
     * Helper function that returns an array of strings for tutorials
     * @return array
     */
    public static function getTutorialsTranslationStrings()
    {
        $guideTeacherIMG = '<img class="wdt-emoji-title" src="'. WDT_ROOT_URL . 'assets/img/male-teacher.png">';
        $waveIMG = '<img class="wdt-emoji-body" src="'. WDT_ROOT_URL . 'assets/img/wave.png">';
        $partyTitleIMG = '<img class="wdt-emoji-title" src="'. WDT_ROOT_URL . 'assets/img/party-popper.png">';
        $hourglassIMG = '<img class="wdt-emoji-title" src="'. WDT_ROOT_URL . 'assets/img/hourglass-not-done.png">';
        $raisedHandsIMG = '<img class="wdt-emoji-title m-l-5" src="'. WDT_ROOT_URL . 'assets/img/raising-hands.png">';
        $chartIMG = '<img class="wdt-emoji-title" src="'. WDT_ROOT_URL . 'assets/img/chart-increasing.png">';
        $username =  wp_get_current_user()->user_login;

        return array(
            'cannot_be_empty_field' => __('The field cannot be empty!', 'wpdatatables'),
            'cannot_be_empty_chart_type' => __('Please choose the chart type.', 'wpdatatables'),
            'cannot_be_empty_chart_table' => __('Please select wpDataTable from the dropdown.', 'wpdatatables'),
            'cannot_be_empty_chart_table_columns' => __('Columns field cannot be empty.', 'wpdatatables'),
            'cancel_button' => __('Cancel', 'wpdatatables'),
            'cancel_tour' => __('The tutorial is not canceled, closed, or end properly. Please cancel it by clicking on the Cancel button.', 'wpdatatables'),
            'error_data_source' => __('Please check the data source that you use for this table.', 'wpdatatables'),
            'finish_button' => __('Finish Tutorial', 'wpdatatables'),
            'next_button' => __('Continue', 'wpdatatables'),
            'start_button' => __('Start', 'wpdatatables'),
            'skip_button' => __('Skip Tutorial', 'wpdatatables'),
            'tour0' => array(
                'step0' => array(
                    'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                    'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG  .  __(', in this tutorial, we will show you how to create a simple table from scratch by choosing a custom number of columns and rows. How to customize each cell, merge cells and a lot more.', 'wpdatatables'),
                ),
                'step1' => array(
                    'title' => __(' Let\'s create a new wpDataTable from scratch!', 'wpdatatables'),
                    'content' => __('Click on \'Create a Table\' to access the wpDataTables Table Wizard.', 'wpdatatables'),
                ),
                'step2' => array(
                    'title' => __('Choose this option', 'wpdatatables'),
                    'content' => __('Please select \'Create a simple table from scratch\'.', 'wpdatatables'),
                ),
                'step3' => array(
                    'title' => __('Click Next', 'wpdatatables'),
                    'content' => __('Please click the \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step4' => array(
                    'title' => __('Welcome to the Simple table wizard!', 'wpdatatables'),
                    'content' => __('Please click \'Continue\' button to move on.', 'wpdatatables'),
                ),
                'step5' => array(
                    'title' => __('Choose a name for your table', 'wpdatatables'),
                    'content' => __('After inserting table name, click \'Continue\' to move on.', 'wpdatatables'),
                ),
                'step6' => array(
                    'title' => __('Choose the number of columns for your table', 'wpdatatables'),
                    'content' => __('Please choose how many columns it will have. Remember that you can always add or reduce the number of columns later. Click \'Continue\' when you finish.', 'wpdatatables'),
                ),
                'step7' => array(
                    'title' => __('Choose the number of rows for your table.', 'wpdatatables'),
                    'content' => __('Please choose how many rows it will have. Remember that you can always add or reduce the number of rows later. Click \'Continue\' when you finish.', 'wpdatatables'),
                ),
                'step8' => array(
                    'title' => __('Click on the \'Generate Table\' button', 'wpdatatables'),
                    'content' => __('When you click on the button, the empty table will be ready for you. ', 'wpdatatables'),
                ),
                'step9' => array(
                    'title' => $hourglassIMG .__('We are generating the table...', 'wpdatatables'),
                    'content' => __('Please, when you see the table, click \'Continue\' to move on.', 'wpdatatables'),
                ),
                'step10' => array(
                    'title' => __('Nice job! You just configured your table and it is ready to fill it with data.', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('Now we will guide you on how to insert data and check table layout throw Simple table editor, table toolbar and table preview. Please click \'Continue\' to move on.', 'wpdatatables'),
                ),
                'step11' => array(
                    'title' => __('This is Simple table editor', 'wpdatatables'),
                    'content' => __('Here you can populate your table with data. <br><br>You can move around the cells using keyboard arrows and the Tab button. <br><br>Rearrange columns or rows by drag and drop column or row headers. Easily resize column width and row height by dragging the right corner of the column header, or the bottom line of the row header. Click \'Continue\' to move on.', 'wpdatatables'),
                ),
                'step12' => array(
                    'title' => __('Check out the Simple table toolbar', 'wpdatatables'),
                    'content' => __('Here you can style and insert custom data for each cell or range of cells. You can add or delete columns and rows, merge cells, customize sections by colors, background, alignment, insert custom links, media, shortcodes, star ratings or custom HTML code.', 'wpdatatables'),
                ),
                'step13' => array(
                    'title' => __('Responsive table views', 'wpdatatables'),
                    'content' => __('You can switch between Desktop, Tablet or Mobile devices by clicking on the tab that you need, so you can make sure your table looks excellent across all devices. ', 'wpdatatables'),
                ),
                'step14' => array(
                    'title' => __('Real-time preview', 'wpdatatables'),
                    'content' => __('Here you will see how your table will look like on the page. Please click \'Continue\' to move on.', 'wpdatatables'),
                ),
                'step15' => array(
                    'title' =>$partyTitleIMG .  __('Congrats! Your table is ready.', 'wpdatatables'),
                    'content' => __('Now you can copy the shortcode for this table, and check out how it looks on your website when you paste it to a post or page. You can always come back and edit the table as you like.', 'wpdatatables'),
                )
            ),
            'tour1' => array(
                'step0' => array(
                    'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                    'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG  .  __(', in this tutorial we will show you how to create a wpDataTable linked to an existing data source. "Linked" in this context means that if you create a table, for example, based on an Excel file, it will read the data from this file every time it loads, making sure all table values changes are instantly reflected in the table.', 'wpdatatables'),
                ),
                'step1' => array(
                    'title' => __('Let\'s create a new wpDataTable!', 'wpdatatables'),
                    'content' => __('Click on \'Create a Table\' to access the wpDataTables Table Wizard.', 'wpdatatables'),
                ),
                'step2' => array(
                    'title' => __('Choose this option.', 'wpdatatables'),
                    'content' => __('Please select \'Create a table linked to an existing data source\'.', 'wpdatatables'),
                ),
                'step3' => array(
                    'title' => __('Click Next', 'wpdatatables'),
                    'content' => __('Please click the \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step4' => array(
                    'title' => __('Input data source type', 'wpdatatables'),
                    'content' => __('Please select a data source type that you need.', 'wpdatatables'),
                ),
                'step5' => array(
                    'title' => __('Select Data source type', 'wpdatatables'),
                    'content' => __('Please choose the data source that you need (SQL, Excel, CSV, JSON, Google Spreadsheet, or PHP array) and then click \'Continue\' button.', 'wpdatatables'),
                ),
                'step6' => array(
                    'title' => __('Input file path or URL', 'wpdatatables'),
                    'content' => __('Upload your file or provide the full URL here. When you finish click \'Continue\' button.', 'wpdatatables'),
                ),
                'step7' => array(
                    'title' => __('Write SQL query', 'wpdatatables'),
                    'content' => __('Please write your custom SQL query and then click \'Continue\' button.', 'wpdatatables'),
                ),
                'step8' => array(
                    'title' => __('Click Save Changes', 'wpdatatables'),
                    'content' => __('Please click on the \'Save Changes\' button to create a table.<br><br> If you get an error message after button click and you are not able to solve it, please contact us on our support platform and provide us this data source that you use for creating this table and copy error message as well and click Skip tutorial.', 'wpdatatables'),
                ),
                'step9' => array(
                    'title' => $hourglassIMG .__('The table is creating...', 'wpdatatables'),
                    'content' => __('Now the table is creating. Wait until you see it in the background and then click \'Continue\'.', 'wpdatatables'),
                ),
                'step10' => array(
                    'title' => $partyTitleIMG . __('Nice job! You just created your first wpDataTable!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('Now you can copy the shortcode for this table, and check out how it looks on your website when you paste it to a post or page.', 'wpdatatables'),
                )
            ),
            'tour2' => array(
                'step0' => array(
                    'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                    'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', in this tutorial we will show you how to create a table manually, fully in WordPress dashboard.', 'wpdatatables'),
                ),
                'step1' => array(
                    'title' => __('Let\'s create a new wpDataTable!', 'wpdatatables'),
                    'content' => __('Click on \'Create a Table\' to access wpDataTables Table Wizard.', 'wpdatatables'),
                ),
                'step2' => array(
                    'title' => __('Choose this option', 'wpdatatables'),
                    'content' => __('Please select \'Create a table manually\' to proceed.', 'wpdatatables'),
                ),
                'step3' => array(
                    'title' => __('Click NEXT', 'wpdatatables'),
                    'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step4' => array(
                    'title' => __('Welcome to manual table constructor', 'wpdatatables'),
                    'content' => __('This table constructor will help you to create a table from scratch. <br><br>  Click \'Continue\' to set section by section.', 'wpdatatables'),
                ),
                'step5' => array(
                    'title' => __('Choose names and number of columns.', 'wpdatatables'),
                    'content' => __('Give your table a name and choose how many columns it will have.<br><br>  Click the \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                ),
                'step6' => array(
                    'title' => __('Column creating wizard', 'wpdatatables'),
                    'content' => __('Here you can set the name, choose the type for each column, drag and drop to reorder them or remove a column.<br><br>  When you set everything, click the \'Continue\' button.', 'wpdatatables'),
                ),
                'step7' => array(
                    'title' => $partyTitleIMG . __('Congrats! You just configured your table.', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('To start working with it, finish the tutorial, click on the \'Create the table\' button and choose in which editor you would like to open it. ', 'wpdatatables'),
                )
            ),
            'tour3' => array(
                'step0' => array(
                    'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                    'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(',  this tutorial will show you how to create a table by importing data from the existing data source.', 'wpdatatables'),
                ),
                'step1' => array(
                    'title' => __('Let\'s create a new wpDataTable!', 'wpdatatables'),
                    'content' => __('Click on \'Create a Table\' to access wpDataTables Table Wizard.', 'wpdatatables'),
                ),
                'step2' => array(
                    'title' => __('Choose this option', 'wpdatatables'),
                    'content' => __('Please select \'Create a table by importing data from a data source\' to proceed.', 'wpdatatables'),
                ),
                'step3' => array(
                    'title' => __('Click Next', 'wpdatatables'),
                    'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step4' => array(
                    'title' => __('Choose your data source', 'wpdatatables'),
                    'content' => __('Upload your file or provide the full URL here. When you finish click \'Continue\' button.', 'wpdatatables'),
                ),
                'step5' => array(
                    'title' => __('Click \'Next\'', 'wpdatatables'),
                    'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step6' => array(
                    'title' => __('Now you can edit your table in the manual constructor', 'wpdatatables'),
                    'content' => __('This constructor will show you columns that will be saved in the database based on your file.<br><br>  Click \'Continue\' button to check section by section.', 'wpdatatables'),
                ),
                'step7' => array(
                    'title' => __('Choose a name for your table.', 'wpdatatables'),
                    'content' => __('Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                ),
                'step8' => array(
                    'title' => __('Welcome to Column Creating Wizard.', 'wpdatatables'),
                    'content' => __('Your data preview is here. You can change the name and type for each column, drag and drop to reorder them, remove or add a column.<br><br>  When you set everything, click \'Continue\' button.', 'wpdatatables'),
                ),
                'step9' => array(
                    'title' => $partyTitleIMG . __('Congrats! You are ready to create the table!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('To start working with it, finish the tutorial, click on the \'Create the table\' button and choose in which editor you would like to open it. ', 'wpdatatables'),
                )
            ),
            'tour4' => array(
                'step0' => array(
                    'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                    'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', in this tutorial, we will show you how to create a table by generating a query from WordPress posts.', 'wpdatatables'),
                ),
                'step1' => array(
                    'title' => __('Let\'s create a new wpDataTable!', 'wpdatatables'),
                    'content' => __('Click on \'Create a Table\' to access the wpDataTables Table Wizard.', 'wpdatatables'),
                ),
                'step2' => array(
                    'title' => __('Choose this option', 'wpdatatables'),
                    'content' => __('Please select \'Generate a query to the WordPress database\' to proceed.', 'wpdatatables'),
                ),
                'step3' => array(
                    'title' => __('Click Next', 'wpdatatables'),
                    'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step4' => array(
                    'title' => __('Choose a name for your table.', 'wpdatatables'),
                    'content' => __('Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                ),
                'step5' => array(
                    'title' => __('Welcome to MySQL query generator.', 'wpdatatables'),
                    'content' => __('Choose one or more post types from the lefthand side for your table, either by dragging and dropping the post type names or by selecting post types and clicking on the right arrow. Once the post types are marked as selected, \'All post properties\' section will be populated.<br><br>From this section, you need to choose the post properties, you can as well select the ones you want to show in the table either by dragging and dropping or by selecting and clicking on the right arrow. When you finish, click \'Continue\' to move forward.', 'wpdatatables'),
                ),
                'step6' => array(
                    'title' => __('Choose relations', 'wpdatatables'),
                    'content' => __('Define relations (joining rules) between post types.<br><br>  Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                ),
                'step7' => array(
                    'title' => __('Define post types relations', 'wpdatatables'),
                    'content' => __('When you define it toggle checkbox to have an inner join, uncheck to have left join. <br><br> Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                ),
                'step8' => array(
                    'title' => __('Set the conditions between the columns', 'wpdatatables'),
                    'content' => __('Add and define conditions between columns in your table by clicking the \'+ Add Condition\' button.<br><br>  When you are ready, click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step9' => array(
                    'title' => __('Set the grouping rules', 'wpdatatables'),
                    'content' => __('By the \'+ Add Grouping\' button, you can define the column grouping rules for the table.<br><br>  Once you\'re done, click "Continue" button to move on.', 'wpdatatables'),
                ),
                'step10' => array(
                    'title' => __('Click \'Next\'', 'wpdatatables'),
                    'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step11' => array(
                    'title' => $hourglassIMG . __('We are creating your query...', 'wpdatatables'),
                    'content' => __('Please wait until wpDataTables query constructor builds the query for you. Once done, it will show five lines of data as a preview. <br><br>Click \'Continue\' button when you see it in the background.', 'wpdatatables'),
                ),
                'step12' => array(
                    'title' => __('Your query preview is ready!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('Here you see the query that wpDataTables generated for you. If you are not completely satisfied with it, you can edit it straight from this window.<br><br> Click "Continue" button to move on.', 'wpdatatables'),
                ),
                'step13' => array(
                    'title' => __('Preview of the first 5 rows.', 'wpdatatables'),
                    'content' => __('Here you can see the first few rows the MySQL server returns using the query provided. If you see “No data”, it means that the MySQL server could not execute it, either because of an error in the query or because it is simply returning an empty data-set, even though the syntax is correct. <br>If this is the case, please click \'Skip Tutorial\' and try to create a new one. <br><br>If everything is fine, please click \'NEXT\' to continue.', 'wpdatatables'),
                ),
                'step14' => array(
                    'title' => $partyTitleIMG . __('Congrats! You are ready to create the table!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('To start working with it, finish the tutorial, click on the \'Create the table\' button and choose in which editor you would like to open it. ', 'wpdatatables'),
                )
            ),
            'tour5' => array(
                'step0' => array(
                    'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                    'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', this tutorial will show you how to create a table by generating a query from the MySQL database.', 'wpdatatables'),
                ),
                'step1' => array(
                    'title' => __('Let\'s create a new wpDataTable!', 'wpdatatables'),
                    'content' => __('Click on \'Create a Table\' to access wpDataTables Table Wizard.', 'wpdatatables'),
                ),
                'step2' => array(
                    'title' => __('Choose this option', 'wpdatatables'),
                    'content' => __('Please select \'Generate a query to the MySQL database\' to proceed.', 'wpdatatables'),
                ),
                'step3' => array(
                    'title' => __('Click Next', 'wpdatatables'),
                    'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step4' => array(
                    'title' => __('Choose a name for your table', 'wpdatatables'),
                    'content' => __('Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                ),
                'step5' => array(
                    'title' => __('Welcome to MySQL query generator', 'wpdatatables'),
                    'content' => __('Here you can choose one or more MySQL tables as data sources for your new wpDataTable, either by dragging and dropping the table names or by selecting the MySQL table and clicking the right arrow. \'All SQL columns\' section will be populated. Next, please choose which columns you would like to show in the wpDataTable - as well, either with drag and drop or by selecting columns and clicking on the right arrow to move them to the \'Selected SQL columns\' section.<br><br> When you finish, click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step6' => array(
                    'title' => __('Define SQL tables relations', 'wpdatatables'),
                    'content' => __('Once you configure the relations, mark the checkbox for using an inner join rule, or uncheck to use left join.<br><br> When you finish, click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step7' => array(
                    'title' => __('Set conditions between the columns.', 'wpdatatables'),
                    'content' => __('Add and define conditions between columns in your table by clicking on the button \'+ Add Condition\'.<br><br> When you finish, click on \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step8' => array(
                    'title' => __('Set grouping rules', 'wpdatatables'),
                    'content' => __('By the \'+ Add Grouping\' button, you can define the column grouping rules for the table.<br><br> Once you\'re done, click "Continue" button to move on.', 'wpdatatables'),
                ),
                'step9' => array(
                    'title' => __('Click \'Next\'', 'wpdatatables'),
                    'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step10' => array(
                    'title' => $hourglassIMG . __('We are creating your query...', 'wpdatatables'),
                    'content' => __('Please wait until wpDataTables query constructor builds the query for you. Once done, it will show five lines of data as a preview.<br><br> Click \'Continue\' button when you see it in the background.', 'wpdatatables'),
                ),
                'step11' => array(
                    'title' => __('Your query preview is ready!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('Here you see the query that wpDataTables generated for you. If you are not completely satisfied with it, you can edit it straight from this window.<br><br> Click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step12' => array(
                    'title' => __('Preview of the first 5 rows', 'wpdatatables'),
                    'content' => __('Here you can see the first few rows the MySQL server returns using the query provided. If you see “No data”, it means that the MySQL server could not execute it, either because of an error in the query or because it is simply returning an empty data-set, even though the syntax is correct. <br>If this is the case, please click \'Skip Tutorial\' and try to create a new one. <br>If everything is fine, please click \'Continue\' to move forward.', 'wpdatatables'),
                ),
                'step13' => array(
                    'title' => $partyTitleIMG . __('Congrats! You are ready to create the table!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('To start working with it, finish the tutorial, click on the \'Create the table\' button and choose in which editor you would like to open it. ', 'wpdatatables'),
                )
            ),
            'tour6' => array(
                'step0' => array(
                    'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                    'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', in this tutorial we will show you how to create a chart in wpDataTables plugin.', 'wpdatatables'),
                ),
                'step1' => array(
                    'title' => __('Let\'s create a new wpDataTables Chart!', 'wpdatatables'),
                    'content' => __('Click on \'Create a Chart\' to access the wpDataTables Chart Wizard.', 'wpdatatables'),
                ),
                'step2' => array(
                    'title' => $chartIMG . __('Welcome to the Chart Wizard!', 'wpdatatables'),
                    'content' => __('You are at the first step now; we will introduce you the wpDataTables Chart Wizard section by section.<br><br> Click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step3' => array(
                    'title' => __('Follow the steps in the Chart Wizard', 'wpdatatables'),
                    'content' => __('By following these steps, you will finish building your chart in the Chart Wizard. The current step will always be highlighted in blue.<br><br> Click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step4' => array(
                    'title' => __('Choose a name for your Chart', 'wpdatatables'),
                    'content' => __('Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                ),
                'step5' => array(
                    'title' => __('In wpDataTables you can find several charts render engines.', 'wpdatatables'),
                    'content' => __('Click on the dropdown, and you will see several options that you can choose from. <br><br>To continue, click on the dropdown.', 'wpdatatables'),
                ),
                'step6' => array(
                    'title' => __('Choose your desired chart engine.', 'wpdatatables'),
                    'content' => __('By clicking on one of the three options, you will choose the engine that will render your chart.<br><br> When you finish, please click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step7' => array(
                    'title' => __('Different charts types based on the engine you choose. ', 'wpdatatables'),
                    'content' => __('Here you can choose a chart type. Please, click on the chart type that you prefer.<br><br> When you finish, please click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step10' => array(
                    'title' => __('The first step is finished!', 'wpdatatables'),
                    'content' => __('Let\'s move on. Please, click \'Next\' to continue.', 'wpdatatables'),
                ),
                'step11' => array(
                    'title' => __('Now you need to choose a wpDataTable based on which we will build a chart for you', 'wpdatatables'),
                    'content' => __('Click on the dropdown, and all your tables will be listed. The columns of the table that you choose will be used for creating the chart.<br><br>If you didn\'t create a wpDataTable yet, then please click on the \'Skip Tutorial\' button and create wpDataTable that would contain the data to visualize first.', 'wpdatatables'),
                ),
                'step12' => array(
                    'title' => __('Pick your wpDataTable', 'wpdatatables'),
                    'content' => __('Pick a wpDataTable from which you want to render a chart and when you finish, please click \'Continue\' to move on.', 'wpdatatables'),
                ),
                'step13' => array(
                    'title' => __('The second step is finished!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('Let\'s see what is coming up next. <br><br> Please, click \'Next\' to continue.', 'wpdatatables'),
                ),
                'step14' => array(
                    'title' => __('Just a heads up!', 'wpdatatables'),
                    'content' => __('Here you will choose from which columns you will create a chart.<br><br> Please click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step15' => array(
                    'title' => __('Meet the wpDataTable Column Blocks', 'wpdatatables'),
                    'content' => __('Here you will choose columns you want to use in the chart. Drag and drop it, or click on the arrow to move the desired column to the \'Columns used in the chart\' section.<br><br> When you finish please, click \'Continue.\'', 'wpdatatables'),
                ),
                'step16' => array(
                    'title' => __('Well done!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('Just two more steps to go. Please click \'Next\' to continue.', 'wpdatatables'),
                ),
                'step17' => array(
                    'title' => __('Chart settings and chart preview.', 'wpdatatables'),
                    'content' => __('Here you can adjust chart settings, different parameters are grouped in section; adjusting the parameters will be reflected in the preview of your chart in real-time on the right-hand side.<br><br> Please click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step18' => array(
                    'title' => __('In this sidebar, you can find the chart settings section.', 'wpdatatables'),
                    'content' => __('By clicking on each section, you can set your desired parameters per section.<br><br> Please click \'Continue\' button to move on.', 'wpdatatables'),
                ),
                'step19' => array(
                    'title' => __('Here are the available chart options', 'wpdatatables'),
                    'content' => __('Set different chart options for the chosen section to get your desired chart look.<br><br> Please click \'Continue\' button to move on.', 'wpdatatables'),
                ),
                'step27' => array(
                    'title' => __('How your chart will look like on the page of your website', 'wpdatatables'),
                    'content' => __('Here you can see a preview of your chart based on the settings you have chosen.<br><br> Please click \'Continue\' button to move on.', 'wpdatatables'),
                ),
                'step28' => array(
                    'title' => __('You can save your chart now', 'wpdatatables'),
                    'content' => __('If you are satisfied with your chart appearance, click on the \'Save chart\' button and all your settings for this chart will be saved in the database.', 'wpdatatables'),
                ),
                'step29' => array(
                    'title' => $partyTitleIMG . __('Congrats! Your first chart is ready!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('Now you can copy the shortcode for this chart and paste it in any WP post or page. <br><br>You may now finish this tutorial. ', 'wpdatatables'),
                )
            )
        );
    }

    /**
     * Helper function that define default value
     * @param $possible
     * @param $index
     * @param string $default
     * @return string
     */
    public static function defineDefaultValue($possible, $index, $default = '')
    {
        return isset($possible[$index]) ? $possible[$index] : $default;
    }

    /**
     * Helper function that extract column headers in array
     * @param $rawDataArr
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
     * @param $rawDataArr
     * @param $headerArr
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
     * Helper function that convert XML to Array
     * @param $xml SimpleXMLElement
     * @param bool $root
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
     * @param $arr
     * @return bool
     */
    public static function isArrayAssoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Helper function that detect single column type
     * @param $values
     * @return string
     */
    private static function wdtDetectColumnType($values)
    {
        $array = array_filter($values);
        if (empty($array)) {
            return 'string';
        } else {

            if (self::_detect($values, 'WDTTools::wdtIsIP')) {
                return 'string';
            }
            if (self::_detect($values, 'WDTTools::wdtIsInteger')) {
                return 'int';
            }
            if (self::_detect($values, 'preg_match', WDT_TIME_12H_REGEX)
                || self::_detect($values, 'preg_match', WDT_TIME_24H_REGEX)
                || self::_detect($values, 'preg_match', WDT_TIME_WITH_SECONDS_REGEX)) {
                return 'time';
            }
            if (self::_detect($values, 'WDTTools::wdtIsDateTime')) {
                return 'datetime';
            }
            if (self::_detect($values, 'WDTTools::wdtIsDate')) {
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
            return 'string';
        }
    }


    /** @noinspection PhpUnusedPrivateMethodInspection
     * Function that checks if the passed value is integer
     * wdtIsInteger(23); //bool(true)
     * wdtIsInteger("23"); //bool(true)
     * @param $input
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
     * @param $values
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
     * @param $input
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
     * @param $input
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
     * @param $valuesArray
     * @param $checkFunction
     * @param string $regularExpression
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

    /**
     * Get information about the remote version.
     *
     * @param string $slug
     * @param string $purchaseCode
     * @param string $envatoTokenEmail
     *
     * @return bool|object
     */
    public static function getRemoteInformation($slug, $purchaseCode, $envatoTokenEmail)
    {
        $serverName =  ( defined( 'WP_CLI' ) && WP_CLI ) ? php_uname('n') : $_SERVER['SERVER_NAME'];
        $request = wp_remote_post(
            WDT_STORE_API_URL . 'autoupdate/info',
            [
                'body' => [
                    'slug' => $slug,
                    'purchaseCode' => $purchaseCode,
                    'envatoTokenEmail' => $envatoTokenEmail,
                    'domain' => self::getDomain(
                        $serverName
                    ),
                    'subdomain' => self::getSubDomain(
                        $serverName
                    )
                ]
            ]
        );

        if ((!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) && isset($request['body'])) {
            $body = json_decode($request['body']);

            return $body && isset($body->info) ? unserialize($body->info, ["allowed_classes" => [stdClass::class]]) : false;
        }

        return false;
    }

    /**
     * Helper function that converts PHP to Moment Date Format
     * @param $dateFormat
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
     * Helper method to wrap values in quotes for DB
     */
    public static function wrapQuotes($value, $connection)
    {
        $valueQuote = $connection ? "'" : '';
        return $valueQuote . $value . $valueQuote;
    }

    /**
     * Helper method to detect the headers that are present in formula
     * @param $formula
     * @param $headers
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
     * Helper function which converts WP upload URL to Path
     * @param $uploadUrl
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
     * @param $uploadPath
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
     * @param $color
     * @param bool $opacity
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
     * @param $color
     * @return bool
     */
    public static function isStringAColor($color) {

        $regex = '/^(\#[\da-f]{3}|\#[\da-f]{6}|rgba\(((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*,\s*){2}((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*)(,\s*(0\.\d+|1))\)|hsla\(\s*((\d{1,2}|[1-2]\d{2}|3([0-5]\d|60)))\s*,\s*((\d{1,2}|100)\s*%)\s*,\s*((\d{1,2}|100)\s*%)(,\s*(0\.\d+|1))\)|rgb\((?:\s*\d+\s*,){2}\s*[\d]+\)|hsl\(\s*((\d{1,2}|[1-2]\d{2}|3([0-5]\d|60)))\s*,\s*((\d{1,2}|100)\s*%)\s*,\s*((\d{1,2}|100)\s*%)\))$/i';

        return preg_match($regex, $color);
    }
    /**
     * Sanitizes the cell string and wraps it with quotes
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
                if (version_compare(WDT_PHP_SERVER_VERSION, '8.1', '>')){
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
     * @param $string
     * @return bool
     */
    public static function isHtml($string)
    {
        return preg_match("/<[^<]+>/", $string, $m) != 0;
    }

    /**
     * Function that strip JS attributes to prevent XSS attacks
     * @param $htmlString
     * @return bool|string
     */
    public static function stripJsAttributes($htmlString)
    {
        $htmlString = stripcslashes($htmlString);
        $htmlString = '<div>' . $htmlString . '</div>';
        if ( function_exists( 'mb_convert_encoding' ) ) {
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
                return substr($domd->saveHTML($domd->documentElement), 5, -6);
            }
        }
        return $htmlString;
    }

    /**
     * Enqueue JS and CSS UI Kit files
     */
    public static function wdtUIKitEnqueue()
    {
        if (get_option('wdtIncludeGoogleFonts'))
            wp_enqueue_style( 'wdt-include-inter-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap',  array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap', WDT_CSS_PATH . 'bootstrap/wpdatatables-bootstrap.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap-select', WDT_CSS_PATH . 'bootstrap/bootstrap-select/bootstrap-select.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap-tagsinput', WDT_CSS_PATH . 'bootstrap/bootstrap-tagsinput/bootstrap-tagsinput.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap-datetimepicker', WDT_CSS_PATH . 'bootstrap/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap-nouislider', WDT_CSS_PATH . 'bootstrap/bootstrap-nouislider/bootstrap-nouislider.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-wp-bootstrap-datetimepicker', WDT_CSS_PATH . 'bootstrap/bootstrap-datetimepicker/wdt-bootstrap-datetimepicker.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap-colorpicker', WDT_CSS_PATH . 'bootstrap/bootstrap-colorpicker/bootstrap-colorpicker.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-wpdt-icons', WDT_ROOT_URL . 'assets/css/style.min.css', array(), WDT_CURRENT_VERSION);
        if (is_admin() && (get_option('wdtGettingStartedPageStatus') != 1)) {
            wp_enqueue_style('wdt-bootstrap-tour-css', WDT_CSS_PATH . 'bootstrap/bootstrap-tour/bootstrap-tour.css', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-bootstrap-tour-guide-css', WDT_CSS_PATH . 'bootstrap/bootstrap-tour/bootstrap-tour-guide.css', array(), WDT_CURRENT_VERSION);
        }

        wp_enqueue_style('wdt-animate', WDT_CSS_PATH . 'animate/animate.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-uikit', WDT_CSS_PATH . 'uikit/uikit.css', array(), WDT_CURRENT_VERSION);

        if (!is_admin() && get_option('wdtIncludeBootstrap') == 1) {
            wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/bootstrap.min.js', array('jquery', 'wdt-bootstrap-select'), WDT_CURRENT_VERSION, true);
        } else if (is_admin() && get_option('wdtIncludeBootstrapBackEnd') == 1) {
            wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/bootstrap.min.js', array('jquery', 'wdt-bootstrap-select'), WDT_CURRENT_VERSION, true);
        } else {
            wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/noconf.bootstrap.min.js', array('jquery', 'wdt-bootstrap-select'), WDT_CURRENT_VERSION, true);
        }
        if (is_admin() && (get_option('wdtGettingStartedPageStatus') != 1)) {
            wp_enqueue_script('wdt-bootstrap-tour', WDT_JS_PATH . 'bootstrap/bootstrap-tour/bootstrap-tour.js', array('jquery'), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-bootstrap-tour-guide', WDT_JS_PATH . 'bootstrap/bootstrap-tour/bootstrap-tour-guide.js', array('jquery'), WDT_CURRENT_VERSION, true);
            wp_localize_script('wdt-bootstrap-tour-guide', 'wpdtTutorialStrings', WDTTools::getTutorialsTranslationStrings());
        }
        wp_enqueue_script('wdt-bootstrap-select', WDT_JS_PATH . 'bootstrap/bootstrap-select/bootstrap-select.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-bootstrap-ajax-select', WDT_JS_PATH . 'bootstrap/bootstrap-select/ajax-bootstrap-select.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-bootstrap-tagsinput', WDT_JS_PATH . 'bootstrap/bootstrap-tagsinput/bootstrap-tagsinput.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-moment', WDT_JS_PATH . 'moment/moment.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-bootstrap-datetimepicker', WDT_JS_PATH . 'bootstrap/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-bootstrap-nouislider', WDT_JS_PATH . 'bootstrap/bootstrap-nouislider/bootstrap-nouislider.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-wNumb', WDT_JS_PATH . 'bootstrap/bootstrap-nouislider/wNumb.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-bootstrap-colorpicker', WDT_JS_PATH . 'bootstrap/bootstrap-colorpicker/bootstrap-colorpicker.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-bootstrap-growl', WDT_JS_PATH . 'bootstrap/bootstrap-growl/bootstrap-growl.min.js', array(), WDT_CURRENT_VERSION, true);
    }

    public static function wdtUIKitEnqueueNotEdit()
    {
        if (get_option('wdtIncludeGoogleFonts'))
            wp_enqueue_style( 'wdt-include-inter-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap',  array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap', WDT_CSS_PATH . 'bootstrap/wpdatatables-bootstrap.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap-select', WDT_CSS_PATH . 'bootstrap/bootstrap-select/bootstrap-select.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-animate', WDT_CSS_PATH . 'animate/animate.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-uikit', WDT_CSS_PATH . 'uikit/uikit.css', array(), WDT_CURRENT_VERSION);

        if (!is_admin() && get_option('wdtIncludeBootstrap') == 1) {
            wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/bootstrap.min.js', array('jquery', 'wdt-bootstrap-select'), WDT_CURRENT_VERSION, true);
        } else if (is_admin() && get_option('wdtIncludeBootstrapBackEnd') == 1) {
            wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/bootstrap.min.js', array('jquery', 'wdt-bootstrap-select'), WDT_CURRENT_VERSION, true);
        } else {
            wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/noconf.bootstrap.min.js', array('jquery', 'wdt-bootstrap-select'), WDT_CURRENT_VERSION, true);
        }

        wp_enqueue_script('wdt-bootstrap-select', WDT_JS_PATH . 'bootstrap/bootstrap-select/bootstrap-select.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-bootstrap-ajax-select', WDT_JS_PATH . 'bootstrap/bootstrap-select/ajax-bootstrap-select.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-moment', WDT_JS_PATH . 'moment/moment.js', array(), WDT_CURRENT_VERSION, true);
    }

    /**
     * Helper method to add PHP vars to JS vars
     * @param $varName
     * @param $phpVar
     */
    public static function exportJSVar($varName, $phpVar)
    {
        self::$jsVars[$varName] = $phpVar;
    }

    /**
     * Helper method to print PHP vars to JS vars
     */
    public static function printJSVars()
    {
        if (!empty(self::$jsVars)) {
            $jsBlock = '<script type="text/javascript">';
            foreach (self::$jsVars as $varName => $jsVar) {
                $jsBlock .= "var {$varName} = " . json_encode($jsVar) . ";";
            }
            $jsBlock .= '</script>';
            echo $jsBlock;
        }
    }

    /**
     * Helper method that converts provided String to Unix Timestamp
     * based on provided date format
     * @param $dateString
     * @param $dateFormat
     * @return false|int
     */
    public static function wdtConvertStringToUnixTimestamp($dateString, $dateFormat)
    {
        if ($dateString == '') return null;
        if (!$dateFormat) $dateFormat = get_option('wdtDateFormat');

        if (null !== $dateFormat && substr($dateFormat, 0,5) === 'd/m/Y') {
            $returnDate = strtotime(str_replace('/', '-', $dateString));
        } else if (null !== $dateFormat && in_array($dateFormat, ['m.d.Y', 'm-d-Y', 'm-d-y','d.m.y','Y.m.d','d-m-Y'])) {
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
     * @param $columnType
     * @param $displayColumnNameData
     */
    public static function wdtConvertUnixTimestampToString($columnType, $displayColumnNameData)
    {
        if ($columnType == 'date'){
            $displayColumnNameData =  date(get_option('wdtDateFormat'), $displayColumnNameData);
        } else if ($columnType == 'datetime'){
            $displayColumnNameData =  date(get_option('wdtDateFormat') . ' ' . get_option('wdtTimeFormat'), $displayColumnNameData);
        } else if ($columnType == 'time'){
            $displayColumnNameData =  date(get_option('wdtTimeFormat'), $displayColumnNameData);
        }

        return $displayColumnNameData;
    }

    /**
     * Show error message
     * @param $errorMessage
     * @return string
     */
    public static function wdtShowError($errorMessage)
    {
        self::wdtUIKitEnqueue();
        ob_start();
        include WDT_ROOT_PATH . 'templates/common/error.inc.php';
        $errorBlock = ob_get_contents();
        ob_end_clean();
        return $errorBlock;
    }

    /**
     * Helper function to generate unique MySQL column headers
     * @param $header
     * @param $existing_headers
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
     * @param $slug
     */
    public static function deactivatePlugin($slug)
    {
        if ($slug === 'wpdatatables') {
            update_option('wdtPurchaseCodeStore', '');
            update_option('wdtEnvatoTokenEmail', '');
            update_option('wdtActivated', 0);
        } else if ($slug === 'wdt-powerful-filters') {
            update_option('wdtPurchaseCodeStorePowerful', '');
            update_option('wdtEnvatoTokenEmailPowerful', '');
            update_option('wdtActivatedPowerful', 0);
        } else if ($slug === 'reportbuilder') {
            update_option('wdtPurchaseCodeStoreReport', '');
            update_option('wdtEnvatoTokenEmailReport', '');
            update_option('wdtActivatedReport', 0);
        } else if ($slug === 'wdt-gravity-integration') {
            update_option('wdtPurchaseCodeStoreGravity', '');
            update_option('wdtEnvatoTokenEmailGravity', '');
            update_option('wdtActivatedGravity', 0);
        } else if ($slug === 'wdt-formidable-integration') {
            update_option('wdtPurchaseCodeStoreFormidable', '');
            update_option('wdtEnvatoTokenEmailFormidable', '');
            update_option('wdtActivatedFormidable', 0);
        } else if ($slug === 'wdt-master-detail') {
            update_option('wdtPurchaseCodeStoreMasterDetail', '');
            update_option('wdtActivatedMasterDetail', 0);
        }
    }

    /**
     * Helper function to translate special UTF-8 to latin for MySQL
     * @param $text
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

    /**
     * Get table count from database
     *
     * @param $filter
     * @return null|string
     */
    public static function getTablesCount($filter)
    {
        global $wpdb;
        $filter === 'table' ? $tableFromDB = 'wpdatatables' : $tableFromDB = 'wpdatacharts';
        $query = "SELECT COUNT(*) FROM {$wpdb->prefix}$tableFromDB";
        return (int)$wpdb->get_var($query);
    }

    /**
     * Get data for last insert table from database
     *
     * @param $filter
     * @return stdClass
     */
    public static function getLastTableData($filter)
    {
        global $wpdb;
        $filter === 'table' ? $tableFromDB = 'wpdatatables' : $tableFromDB = 'wpdatacharts';
        $query = "SELECT MAX(id) FROM {$wpdb->prefix}$tableFromDB";
        $lastID = $wpdb->get_var($query);
        $chartQuery = $wpdb->prepare(
            "SELECT * 
                        FROM " . $wpdb->prefix . "wpdatacharts 
                        WHERE id = %d",
            $lastID
        );

        if ($filter === 'table') {
            return WDTConfigController::loadTableFromDB($lastID);
        } else if ($filter === 'chart') {
            return $wpdb->get_row($chartQuery);
        }

    }

    /**
     * Convert Table type for readable content
     *
     * @param $tableType
     * @return string
     */
    public static function getConvertedTableType($tableType)
    {
        switch ($tableType) {
            case 'mysql':
            case 'mssql':
            case 'postgresql':
                return 'SQL';
                break;
            case 'manual':
                return 'Manual';
                break;
            case 'xls':
                return 'Excel';
                break;
            case 'csv':
                return 'CSV';
                break;
            case 'xml':
                return 'XML';
                break;
            case 'json':
                return 'JSON';
                break;
            case 'nested_json':
                return 'Nested JSON';
                break;
            case 'serialized':
                return 'Serialized PHP array';
                break;
            case 'google_spreadsheet':
                return 'Google sheet';
                break;
            default:
                if (in_array($tableType, WPDataTable::$allowedTableTypes)) {
                    return ucfirst($tableType);
                }
                return 'Unknown';
                break;
        }

    }

    /**
     * Check if current user can update and delete own rows not others
     *
     * @param $tableData
     * @param $mySqlTableName
     * @param $columnsData
     * @param $id
     * @param $action
     *
     */
    public static function checkCurrentUsersActionsPermissions($tableData, $mySqlTableName, $columnsData, $id, $action)
    {
        global $wpdb;
        $idValCheck = 0;
        $idColumnName = '';
        $userIDColumnName = '';
        foreach ($columnsData as $column) {
            if ($column->id_column) {
                $idColumnName = $column->orig_header;
                $idValCheck = $action == 'delete' ? $id : (int)$id[$idColumnName];
            } else {
                // Defining the values for User ID columns and for "none" input types
                if ($column->id == $tableData->userid_column_id) {
                    $userIDColumnName = $column->orig_header;
                }
            }
        }

        if (!(Connection::isSeparate($tableData->connection))) {
            if ($idValCheck != '0'){
                $res = $wpdb->query($wpdb->prepare( "SELECT `{$idColumnName}` FROM {$mySqlTableName} WHERE `{$idColumnName}` = %d AND `{$userIDColumnName}` = %d", $idValCheck, get_current_user_id() ));
                if ( !$res){
                    if ($action == 'delete'){
                        $returnResult['error'] = __('User do not have permissions to delete this row! ', 'wpdatatables');
                    } else {
                        $returnResult['error'] = __('User do not have permission to update data!', 'wpdatatables');
                    }
                    echo json_encode($returnResult);
                    exit();
                }
            }
        } else {
            // If plugin is using a separate DB

            $vendor = Connection::getVendor($tableData->connection);
            $isMySql = $vendor === Connection::$MYSQL;
            $isMSSql = $vendor === Connection::$MSSQL;
            $isPostgreSql = $vendor === Connection::$POSTGRESQL;

            $leftSysIdentifier = Connection::getLeftColumnQuote($vendor);
            $rightSysIdentifier = Connection::getRightColumnQuote($vendor);

            $sql = Connection::getInstance($tableData->connection);
            if ($idValCheck != '0') {
                $query = "SELECT {$leftSysIdentifier}{$idColumnName}{$rightSysIdentifier} FROM {$mySqlTableName} WHERE {$leftSysIdentifier}{$idColumnName}{$rightSysIdentifier} = {$idValCheck} AND {$leftSysIdentifier}{$userIDColumnName}{$rightSysIdentifier} =" . get_current_user_id();
                if (!$sql->getField($query)) {
                    if ($action == 'delete'){
                        $returnResult['error'] = __('User does not have permissions to delete this row! ', 'wpdatatables');
                    } else {
                        $returnResult['error'] = __('User does not have permission to update data!', 'wpdatatables');
                    }
                    echo json_encode($returnResult);
                    exit();
                }
            }
        }
    }
}

add_action('admin_footer', array('WDTTools', 'printJSVars'), 100);
add_action('wp_footer', array('WDTTools', 'printJSVars'), 100);
