<?php

namespace WDTIntegration;

defined('ABSPATH') or die('Access denied.');

// Full url to the Placeholders root directory
define('WDT_PH_ROOT_URL', WDT_STANDARD_INTEGRATIONS_URL . 'placeholders/');
// Full path to the Placeholders root directory
define('WDT_PH_ROOT_PATH', WDT_STANDARD_INTEGRATIONS_PATH . 'placeholders/');
// Placeholders const
define('WDT_PH_INTEGRATION', true);


/**
 * Class Placeholders
 *
 * @package WDTIntegration
 */
class Placeholders
{
    public static function init()
    {
        // Add placeholders settings block in table settings
        add_action('wpdatatables_add_table_placeholders_elements', array('WDTIntegration\Placeholders',
            'addSettingsBlock'));

    }

    public static function maybeApply($string)
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
            $currentPostIdPlaceholder = $table->currentPostIdPlaceholder ?? $_POST['currentPostIdPlaceholder'] ?? null;

            $wdtCurPostId = $currentPostIdPlaceholder ?? get_the_ID();

            // Fall back to global post object if necessary
            if (!$wdtCurPostId && isset($GLOBALS['post'])) {
                $wdtCurPostId = (int)$GLOBALS['post']->ID;
            }

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

    public static function maybeApplyInColumns($value)
    {
        global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9;

        if ($value && !is_array($value) && !is_object($value)) {
            // Current user ID
            if (strpos($value, '%CURRENT_USER_ID%') !== false) {
                $value = str_replace('%CURRENT_USER_ID%', get_current_user_id(), $value);
            }// Current user login
            if (strpos($value, '%CURRENT_USER_LOGIN%') !== false) {
                $value = str_replace('%CURRENT_USER_LOGIN%', wp_get_current_user()->user_login, $value);
            }// Current post id
            if (strpos($value, '%CURRENT_POST_ID%') !== false) {
                $currentPostId = get_the_ID() ?? ((int)$GLOBALS['post']->ID ?? null);
                $value = str_replace('%CURRENT_POST_ID%', $currentPostId, $value);
            }// Current user first name
            if (strpos($value, '%CURRENT_USER_FIRST_NAME%') !== false) {
                $value = str_replace('%CURRENT_USER_FIRST_NAME%', wp_get_current_user()->first_name, $value);
            }// Current user last name
            if (strpos($value, '%CURRENT_USER_LAST_NAME%') !== false) {
                $value = str_replace('%CURRENT_USER_LAST_NAME%', wp_get_current_user()->last_name, $value);
            }// Current user display name
            if (strpos($value, '%CURRENT_USER_DISPLAY_NAME%') !== false) {
                $value = str_replace('%CURRENT_USER_DISPLAY_NAME%', wp_get_current_user()->display_name, $value);
            }// Current user email
            if (strpos($value, '%CURRENT_USER_EMAIL%') !== false) {
                $value = str_replace('%CURRENT_USER_EMAIL%', wp_get_current_user()->user_email, $value);
            }// Current date
            if (strpos($value, '%CURRENT_DATE%') !== false) {
                $value = str_replace('%CURRENT_DATE%', current_time(get_option('wdtDateFormat')), $value);
            }// Current datetime
            if (strpos($value, '%CURRENT_DATETIME%') !== false) {
                $value = str_replace('%CURRENT_DATETIME%', current_time(get_option('wdtDateFormat')) . ' ' . current_time(get_option('wdtTimeFormat')), $value);
            }// Current time
            if (strpos($value, '%CURRENT_TIME%') !== false) {
                $value = str_replace('%CURRENT_TIME%', current_time(get_option('wdtTimeFormat')), $value);
            }// Shortcode VAR1
            if (strpos($value, '%VAR1%') !== false) {
                $value = str_replace('%VAR1%', $wdtVar1, $value);
            }// Shortcode VAR2
            if (strpos($value, '%VAR2%') !== false) {
                $value = str_replace('%VAR2%', $wdtVar2, $value);
            }// Shortcode VAR3
            if (strpos($value, '%VAR3%') !== false) {
                $value = str_replace('%VAR3%', $wdtVar3, $value);
            }// Shortcode VAR4
            if (strpos($value, '%VAR4%') !== false) {
                $value = str_replace('%VAR4%', $wdtVar4, $value);
            }// Shortcode VAR5
            if (strpos($value, '%VAR5%') !== false) {
                $value = str_replace('%VAR5%', $wdtVar5, $value);
            }// Shortcode VAR6
            if (strpos($value, '%VAR6%') !== false) {
                $value = str_replace('%VAR6%', $wdtVar6, $value);
            }// Shortcode VAR7
            if (strpos($value, '%VAR7%') !== false) {
                $value = str_replace('%VAR7%', $wdtVar7, $value);
            }// Shortcode VAR8
            if (strpos($value, '%VAR8%') !== false) {
                $value = str_replace('%VAR8%', $wdtVar8, $value);
            }// Shortcode VAR9
            if (strpos($value, '%VAR9%') !== false) {
                $value = str_replace('%VAR9%', $wdtVar9, $value);
            }
        }

        return $value;
    }

    /**
     * Adds placeholders block in column settings
     */
    public static function addSettingsBlock()
    {
        ob_start();
        include 'templates/placeholders_settings_block.inc.php';
        $settingsBlock = ob_get_contents();
        ob_end_clean();
        echo $settingsBlock;
    }
}

Placeholders::init();