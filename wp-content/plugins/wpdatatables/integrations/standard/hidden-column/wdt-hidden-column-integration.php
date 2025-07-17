<?php

namespace WDTIntegration;

use WP_Post;

defined('ABSPATH') or die('Access denied.');

// Full url to the WDT Hidden Column root directory
define('WDT_HCOL_ROOT_URL', WDT_STANDARD_INTEGRATIONS_URL . 'hidden-column/');
// Full path to the WDT Hidden Column root directory
define('WDT_HCOL_ROOT_PATH', WDT_STANDARD_INTEGRATIONS_PATH . 'hidden-column/');
// Path to the assets directory of the Hidden Column integration
define('WDT_HCOL_ASSETS_URL', WDT_HCOL_ROOT_URL . 'assets/');

define('WDT_HCOL_INTEGRATION', true);

/**
 * Class HiddenColumn
 *
 * @package WDTIntegration
 */
class HiddenColumn
{
    public static function init()
    {
        // Formatting entry data for hidden column
        add_filter('wpdatatables_formatting_entry_data_custom_column_type_hidden', array('WDTIntegration\HiddenColumn',
            'formattingEntry'), 10, 5);

        // Import entry data from Google sheet
        add_filter('wpdatatables_import_default_entry_data_gsheet_column_type_hidden', array('WDTIntegration\HiddenColumn',
            'importPredefinedEntryForHiddenFromSource'), 10, 4);
        add_filter('wpdatatables_import_default_entry_data_other_source_column_type_hidden', array('WDTIntegration\HiddenColumn',
            'importPredefinedEntryForHiddenFromSource'), 10, 4);

        // Import entry data from other sources
        add_filter('wpdatatables_import_entry_data_gsheet_column_type_hidden', array('WDTIntegration\HiddenColumn',
            'overwriteColumnWithHidden'), 10, 8);
        add_filter('wpdatatables_import_entry_data_other_source_column_type_hidden', array('WDTIntegration\HiddenColumn',
            'overwriteColumnWithHidden'), 10, 8);

        // Include file that contains HiddenWDTColumn class in wpdt
        add_filter('wpdatatables_column_formatter_file_name', array('WDTIntegration\HiddenColumn',
            'columnFormatterFileName'), 10, 2);

        // Include hidden column as possible column type
        add_filter('wpdatatables_filter_possible_column_types', array('WDTIntegration\HiddenColumn',
            'filterPossibleColumnTypes'));

        // Extend column type value in DB
        add_action('wpdatatables_after_activation_method', array('WDTIntegration\HiddenColumn', 'extendColumnTypeDB'));

        // Extend column properties
        add_filter('wpdatatables_filter_column_properties', array('WDTIntegration\HiddenColumn',
            'filterColumnProperties'), 10, 3);

        // Extend column properties mapper
        add_filter('wpdatatables_filter_column_properties_mapper', array('WDTIntegration\HiddenColumn',
            'filterColumnPropertiesMapper'), 10, 3);

        // Filter column data for new column column
        add_filter('wpdatatables_filter_column_data_for_new_column', array('WDTIntegration\HiddenColumn',
            'filterColumnDefaultValue'), 10, 2);

        // Insert hidden columns blocks in constructor template
        add_action('wpdatatables_after_constructor_column_block', array('WDTIntegration\HiddenColumn',
            'insertHiddenColumnTemplate'));

        // Insert hidden columns blocks in add column modal
        add_action('wpdatatables_add_options_in_add_column_modal', array('WDTIntegration\HiddenColumn',
            'insertHiddenColumnAddColumnModal'));

        // Insert hidden columns blocks in constructor preview template
        add_action('wpdatatables_after_constructor_column_block_preview', array('WDTIntegration\HiddenColumn',
            'insertHiddenColumnTemplate'));

        add_action('wpdatatables_insert_field_in_edit_dialog_input_type_hidden', array('WDTIntegration\HiddenColumn',
            'insertHiddenColumnInputField'), 10, 4);

        // Add hidden column type option
        add_action('wpdatatables_add_custom_column_type_option', array('WDTIntegration\HiddenColumn',
            'addHiddenColumnType'));
    }

    public static function addHiddenColumnType()
    {
        ob_start();
        include 'templates/hidden_column_type_option.inc.php';
        $hiddenColumnType = ob_get_contents();
        ob_end_clean();
        echo $hiddenColumnType;

    }

    public static function insertHiddenColumnInputField($dataColumn, $dataColumnKey, $tableSelector, $tableID)
    {
        ob_start();
        include 'templates/hidden_column_input_block.inc.php';
        $hiddenInputTemplate = ob_get_contents();
        ob_end_clean();
        echo $hiddenInputTemplate;

    }

    public static function insertHiddenColumnTemplate()
    {
        ob_start();
        include 'templates/constructor_hidden_column_block.inc.php';
        $hiddenTemplate = ob_get_contents();
        ob_end_clean();
        echo $hiddenTemplate;

    }

    public static function insertHiddenColumnAddColumnModal()
    {
        ob_start();
        include 'templates/add_column_modal_hidden_column_block.inc.php';
        $hiddenOptionModal = ob_get_contents();
        ob_end_clean();
        echo $hiddenOptionModal;

    }

    public static function filterColumnDefaultValue($columnData, $tableData)
    {
        $columnData['hidden_default_value'] = sanitize_text_field($columnData['hidden_default_value']);
        if ($columnData['type'] == 'hidden') {
            $columnData['default_value'] = \WDTTools::prepareStringCell(
                self::getHiddenDefaultValues($columnData['hidden_default_value'], $tableData), $tableData->connection);
        }

        return $columnData;
    }

    public static function filterColumnPropertiesMapper($columnPropertiesMapper, $column_header, $columnPropertiesConstruct)
    {
        $columnPropertiesMapper['VARCHAR']['hidden'] = [
            'editor_type' => 'hidden',
            'filter_type' => 'text',
            'column_type' => 'hidden',
            'create_block' => "{$column_header}  $columnPropertiesConstruct->ValueForDB $columnPropertiesConstruct->columnCollate "
        ];

        return $columnPropertiesMapper;
    }

    public static function filterColumnProperties($columnProperties, $column, $connection)
    {
        if ($column['type'] === 'hidden') {
            $columnProperties['visible'] = 0;
            $columnProperties['advanced_settings']['editingDefaultValue'] = sanitize_text_field($column['hidden_default_value']);
        }

        return $columnProperties;
    }

    public static function extendColumnTypeDB()
    {
        global $wpdb;
        $alterDBColumnTypeQuery = "ALTER TABLE {$wpdb->prefix}wpdatatables_columns 
                                MODIFY COLUMN column_type 
                                    enum('autodetect','string','int','float','date','link','email','image','formula','datetime','time','masterdetail','hidden','select','cart','index'),
                                MODIFY COLUMN input_type 
                                    enum('none','text','textarea','mce-editor','date','datetime','time','link','email','selectbox','multi-selectbox','attachment','hidden')";
        $wpdb->query($alterDBColumnTypeQuery);
    }

    public static function filterPossibleColumnTypes($possibleColumnTypes)
    {
        $newColVal = array('hidden' => __('Hidden (Dynamic)', 'wpdatatables'));

        return array_merge(
            array_slice($possibleColumnTypes, 0, 4),
            $newColVal, array_slice($possibleColumnTypes, 4)
        );
    }

    public static function importPredefinedEntryForHiddenFromSource($insertArrayColumn, $column, $column_headers, $insertArray): string
    {
        return "'" . esc_sql(self::getHiddenDefaultValues($column->hidden_default_value, null)) . "'";
    }

    public static function overwriteColumnWithHidden($nullData, $insertArray, $dataRows, $row, $dataColumnIndex, $dataColumnHeading, $columnType, $obj): string
    {
        $hidden_default_value = '';
        foreach ($obj->getTableData()->columns as $column) {
            if ($column->type == 'hidden' && $obj->getHeadingsArray()[$dataColumnIndex] == $column->orig_header) {
                $hidden_default_value = $column->hidden_default_value;
            }
        }
        return "'" . esc_sql(self::getHiddenDefaultValues($hidden_default_value, null)) . "'";
    }

    /**
     * Format file that contain column class
     *
     * @param $columnFormatterFileName
     * @param $wdtColumnType
     *
     * @return string
     */
    public static function columnFormatterFileName($columnFormatterFileName, $wdtColumnType)
    {
        if ($wdtColumnType == 'hidden') {
            $columnFormatterFileName = WDT_HCOL_ROOT_PATH . 'source/' . $columnFormatterFileName;
        }
        return $columnFormatterFileName;
    }

    public static function formattingEntry($formDataEntry, $formData, $advancedSettings, $column, $tableData)
    {
        $formData[$column->orig_header] = \WDTTools::prepareStringCell(
            self::getHiddenDefaultValues(
                $advancedSettings->editingDefaultValue,
                $tableData),
            $tableData->connection
        );

        return $formData[$column->orig_header];

    }

    /**
     * Helper function for getting hidden dynamic value
     *
     * @param $value
     *
     * @return string
     */
    public static function getHiddenDefaultValues($value, $tableData): string
    {
        global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9;

        if ($tableData != null) {
            $wdtVar1 = $tableData->var1;
            $wdtVar2 = $tableData->var2;
            $wdtVar3 = $tableData->var3;
            $wdtVar4 = $tableData->var4;
            $wdtVar5 = $tableData->var5;
            $wdtVar6 = $tableData->var6;
            $wdtVar7 = $tableData->var7;
            $wdtVar8 = $tableData->var8;
            $wdtVar9 = $tableData->var9;
        }

        $arePlaceholdersInUrl = isset($_POST['wdtAjaxURL']) ? sanitize_url($_POST['wdtAjaxURL']) : '';
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

        $defaultHiddenValue = '';
        $url = wp_get_referer();
        $postID = url_to_postid($url);
        $embedUrl = esc_url(get_permalink($postID));
        if (strpos($value, 'acf-data:') !== false) {
            $defaultHiddenValue = $value;
            $value = 'acf-data';
        }
        if (strpos($value, 'post-meta:') !== false) {
            $defaultHiddenValue = $value;
            $value = 'post-meta';
        }
        if (strpos($value, 'query-param:') !== false) {
            $defaultHiddenValue = $value;
            $value = 'query-param';
        }

        switch ($value) {
            case 'user-ip':
                $defaultHiddenValue = self::_getUserIP();
                break;
            case 'user-id':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%CURRENT_USER_ID%');
                break;
            case 'user-display-name':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%CURRENT_USER_DISPLAY_NAME%');
                break;
            case 'user-first-name':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%CURRENT_USER_FIRST_NAME%');
                break;
            case 'user-last-name':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%CURRENT_USER_LAST_NAME%');
                break;
            case 'user-email':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%CURRENT_USER_EMAIL%');
                break;
            case 'user-login':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%CURRENT_USER_LOGIN%');
                break;
            case 'date':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%CURRENT_DATE%');
                break;
            case 'datetime':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%CURRENT_DATETIME%');
                break;
            case 'time':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%CURRENT_TIME%');
                break;
            case 'p-var1':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%VAR1%');
                break;
            case 'p-var2':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%VAR2%');
                break;
            case 'p-var3':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%VAR3%');
                break;
            case 'p-var4':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%VAR4%');
                break;
            case 'p-var5':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%VAR5%');
                break;
            case 'p-var6':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%VAR6%');
                break;
            case 'p-var7':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%VAR7%');
                break;
            case 'p-var8':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%VAR8%');
                break;
            case 'p-var9':
                $defaultHiddenValue = \WDTTools::applyPlaceholders('%VAR9%');
                break;
            case 'post-id':
                $defaultHiddenValue = self::_getPostData('ID');;
                break;
            case 'post-title':
                $defaultHiddenValue = self::_getPostData('post_title');
                break;
            case 'post-author':
                $defaultHiddenValue = self::_getPostData('post_author');
                break;
            case 'post-type':
                $defaultHiddenValue = self::_getPostData('post_type');
                break;
            case 'post-status':
                $defaultHiddenValue = self::_getPostData('post_status');
                break;
            case 'post-parent':
                $defaultHiddenValue = self::_getPostData('post_parent');
                break;
            case 'post-meta':
                $defaultHiddenValue = self::_getPostMetaData($defaultHiddenValue);
                break;
            case 'acf-data':
                $defaultHiddenValue = self::_getACFData($defaultHiddenValue);
                break;
            case 'post-url':
                $defaultHiddenValue = $embedUrl;
                break;
            case 'user-agent':
                $defaultHiddenValue = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash(($_SERVER['HTTP_USER_AGENT']))) : '';
                break;
            case 'refer-url':
                $defaultHiddenValue = isset($_SERVER['HTTP_REFERER']) ? sanitize_url($_SERVER['HTTP_REFERER']) : $embedUrl;
                break;
            case 'query-param':
                $defaultHiddenValue = self::_getQueryParam($defaultHiddenValue);
                break;
            default:
                break;
        }

        return apply_filters('wpdatatables_default_hidden_column_value', $defaultHiddenValue, $value);
    }

    /**
     * Helper function to get query param
     *
     * @param $value
     *
     * @return string
     */
    private static function _getQueryParam($value): string
    {
        $queryKeyValue = '';
        $queryValue = str_replace('query-param:', '', $value);

        if (isset($_REQUEST['queryParams']) && is_array($_REQUEST['queryParams'])) {
            $queryParamsArray = $_REQUEST['queryParams'];
            if ($queryValue) {
                if (isset($queryParamsArray[$queryValue])) {
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
    private static function _getPostMetaData($value): string
    {
        $metaValue = '';
        $metaKey = str_replace('post-meta:', '', $value);
        $postID = self::_getPostData('ID');
        if ($postID) {
            if (get_post_meta($postID, $metaKey, true)) {
                $metaValue = get_post_meta($postID, $metaKey, true);
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
    private static function _getACFData($value): string
    {
        $acfValue = '';
        $acfReturnValue = '';
        $postID = '';
        $acfKey = str_replace('acf-data:', '', $value);
        if ($acfKey) {
            $postID = self::_getPostData('ID');
            if ($postID) {
                if (class_exists('ACF') && function_exists('get_field')) {
                    $acfReturnValue = get_field($acfKey, $postID);
                    if ($acfReturnValue) {
                        if (is_object($acfReturnValue)) {
                            if ($acfReturnValue instanceof WP_Post) {
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

        return apply_filters('wpdatatables_default_hidden_acf_value', $acfValue, $acfKey, $acfReturnValue, $postID);
    }

    /**
     * Helper function to get user IP
     * @return string|null
     */
    private static function _getUserIP(): ?string
    {
        $client = isset($_SERVER['HTTP_CLIENT_IP']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP'])) : null;
        $forward = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR'])) : null;
        $is_cf = self::_isCloudflare(); // Check if request is from CloudFlare.
        if ($is_cf) {
            $cf_ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_CF_CONNECTING_IP'])) : null; // We already make sure this is set in the checks.
            if (filter_var($cf_ip, FILTER_VALIDATE_IP)) {
                return $cf_ip;
            }
        } else {
            $remote = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : null;
        }
        $client_real = isset($_SERVER['HTTP_X_REAL_IP']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_X_REAL_IP'])) : null;
        $user_ip = $remote;
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $user_ip = $client;
        } elseif (filter_var($client_real, FILTER_VALIDATE_IP)) {
            $user_ip = $client_real;
        } elseif (!empty($forward)) {
            $forward = explode(',', $forward);
            $ip = array_shift($forward);
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
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
    private static function _isCloudflare()
    {
        $ip = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        }
        if (!empty($ip)) {
            $request_check = self::_cloudflareRequestsCheck();
            if (!$request_check) {
                return false;
            }

            $ip_check = self::_validateCloudflareIP($ip);

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
    private static function _validateCloudflareIP($ip): bool
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
        $is_cf_ip = false;
        foreach ($cloudflare_ips as $cloudflare_ip) {
            if (self::_cloudflareIPInRange($ip, $cloudflare_ip)) {
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
    private static function _cloudflareIPInRange($ip, $range)
    {
        if (strpos($range, '/') === false) {
            $range .= '/32';
        }

        // $range is in IP/CIDR format eg 127.0.0.1/24.
        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
        $netmask_decimal = ~$wildcard_decimal;

        return (($ip_decimal & $netmask_decimal) === ($range_decimal & $netmask_decimal));
    }

    /**
     * Check if there are any cloudflare headers in the request
     *
     * @return bool
     */
    private static function _cloudflareRequestsCheck()
    {
        $flag = true;

        if (!isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $flag = false;
        }
        if (!isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
            $flag = false;
        }
        if (!isset($_SERVER['HTTP_CF_RAY'])) {
            $flag = false;
        }
        if (!isset($_SERVER['HTTP_CF_VISITOR'])) {
            $flag = false;
        }

        return $flag;
    }

    /**
     * Helper function to get post data
     *
     * @param $property
     * @param $post_id
     * @param $default
     *
     * @return mixed|null
     */
    private static function _getPostData($property, $post_id = null, $default = '')
    {
        global $post;

        if ($post_id) {
            $post_object = get_post($post_id);
            if ($post_object instanceof WP_Post) {
                $post = $post_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
            }
        }

        if (!$post) {
            $wp_referer = wp_get_referer();
            if ($wp_referer) {
                $post_id = url_to_postid($wp_referer);
                if ($post_id) {
                    $post_object = get_post($post_id);
                    if ($post_object instanceof WP_Post) {
                        $post = $post_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                    }
                }
            }
        }

        $post_data = self::_objectToArray($post);

        return $post_data[$property] ?? $default;
    }

    /**
     * Convert object to array
     *
     * @param $object
     *
     * @return array
     */
    private static function _objectToArray($object)
    {
        $array = array();

        if (empty($object)) {
            return $array;
        }

        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }

        return $array;
    }
}

HiddenColumn::init();