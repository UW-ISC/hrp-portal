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

        $table = null;
        if (isset($_POST['table']) && is_string($_POST['table'])) {
            $decoded = json_decode(wp_unslash($_POST['table']));
            if (is_object($decoded)) {
                $table = $decoded;
            }
        }

        if (strpos($string, '%CURRENT_USER_ID%') !== false) {
            $currentUserIdPlaceholder = null;
            if (is_object($table) && isset($table->currentUserIdPlaceholder)) {
                $candidate = absint($table->currentUserIdPlaceholder);
                if (self::isTrustedUserIdPlaceholder($candidate)) {
                    $currentUserIdPlaceholder = $candidate;
                }
            }
            if (null === $currentUserIdPlaceholder && isset($_POST['currentUserId'])) {
                $candidate = absint(wp_unslash($_POST['currentUserId']));
                if (self::isTrustedUserIdPlaceholder($candidate)) {
                    $currentUserIdPlaceholder = $candidate;
                }
            }

            $wdtCurUserId = null !== $currentUserIdPlaceholder ? $currentUserIdPlaceholder : get_current_user_id();

            $string = str_replace('%CURRENT_USER_ID%', (string) absint($wdtCurUserId), $string);
        }
        if (strpos($string, '%CURRENT_USER_LOGIN%') !== false) {
            $currentUserLoginPlaceholder = null;
            if (is_object($table) && isset($table->currentUserLoginPlaceholder)) {
                $candidate = sanitize_text_field(wp_unslash((string) $table->currentUserLoginPlaceholder));
                if (self::isTrustedUserLoginPlaceholder($candidate)) {
                    $currentUserLoginPlaceholder = $candidate;
                }
            }
            if (null === $currentUserLoginPlaceholder && isset($_POST['currentUserLogin'])) {
                $candidate = sanitize_text_field(wp_unslash((string) $_POST['currentUserLogin']));
                if (self::isTrustedUserLoginPlaceholder($candidate)) {
                    $currentUserLoginPlaceholder = $candidate;
                }
            }

            $wdtCurUserLogin = null !== $currentUserLoginPlaceholder ? $currentUserLoginPlaceholder : wp_get_current_user()->user_login;

            $string = str_replace('%CURRENT_USER_LOGIN%', esc_sql($wdtCurUserLogin), $string);
        }
        if (strpos($string, '%CURRENT_POST_ID%') !== false) {
            $server_post_id = get_the_ID();
            if (!$server_post_id && isset($GLOBALS['post']) && is_object($GLOBALS['post']) && isset($GLOBALS['post']->ID)) {
                $server_post_id = (int) $GLOBALS['post']->ID;
            }
            $server_post_id = (int) $server_post_id;

            $currentPostIdPlaceholder = null;
            if (is_object($table) && isset($table->currentPostIdPlaceholder)) {
                $candidate = absint($table->currentPostIdPlaceholder);
                if (self::isTrustedPostIdPlaceholder($candidate, $server_post_id)) {
                    $currentPostIdPlaceholder = $candidate;
                }
            }
            if (null === $currentPostIdPlaceholder && isset($_POST['currentPostIdPlaceholder'])) {
                $candidate = absint(wp_unslash($_POST['currentPostIdPlaceholder']));
                if (self::isTrustedPostIdPlaceholder($candidate, $server_post_id)) {
                    $currentPostIdPlaceholder = $candidate;
                }
            }

            $wdtCurPostId = null !== $currentPostIdPlaceholder ? $currentPostIdPlaceholder : $server_post_id;

            if (!$wdtCurPostId && isset($GLOBALS['post']) && is_object($GLOBALS['post']) && isset($GLOBALS['post']->ID)) {
                $wdtCurPostId = (int) $GLOBALS['post']->ID;
            }

            $string = str_replace('%CURRENT_POST_ID%', (string) absint($wdtCurPostId), $string);
        }
        if (strpos($string, '%CURRENT_USER_DISPLAY_NAME%') !== false) {
            $currentUserDisplayNamePlaceholder = null;
            if (is_object($table) && isset($table->currentUserDisplayNamePlaceholder)) {
                $candidate = sanitize_text_field(wp_unslash((string) $table->currentUserDisplayNamePlaceholder));
                if (self::isTrustedUserDisplayNamePlaceholder($candidate)) {
                    $currentUserDisplayNamePlaceholder = $candidate;
                }
            }
            if (null === $currentUserDisplayNamePlaceholder && isset($_POST['currentUserDisplayName'])) {
                $candidate = sanitize_text_field(wp_unslash((string) $_POST['currentUserDisplayName']));
                if (self::isTrustedUserDisplayNamePlaceholder($candidate)) {
                    $currentUserDisplayNamePlaceholder = $candidate;
                }
            }

            $wdtCurUserDisplayName = null !== $currentUserDisplayNamePlaceholder ? $currentUserDisplayNamePlaceholder : wp_get_current_user()->display_name;

            $string = str_replace('%CURRENT_USER_DISPLAY_NAME%', esc_sql($wdtCurUserDisplayName), $string);
        }
        if (strpos($string, '%CURRENT_USER_FIRST_NAME%') !== false) {
            $currentUserFirstNamePlaceholder = null;
            if (is_object($table) && isset($table->currentUserFirstNamePlaceholder)) {
                $candidate = sanitize_text_field(wp_unslash((string) $table->currentUserFirstNamePlaceholder));
                if (self::isTrustedUserFirstNamePlaceholder($candidate)) {
                    $currentUserFirstNamePlaceholder = $candidate;
                }
            }
            if (null === $currentUserFirstNamePlaceholder && isset($_POST['currentUserFirstName'])) {
                $candidate = sanitize_text_field(wp_unslash((string) $_POST['currentUserFirstName']));
                if (self::isTrustedUserFirstNamePlaceholder($candidate)) {
                    $currentUserFirstNamePlaceholder = $candidate;
                }
            }

            $wdtCurUserFirstName = null !== $currentUserFirstNamePlaceholder ? $currentUserFirstNamePlaceholder : wp_get_current_user()->user_firstname;

            $string = str_replace('%CURRENT_USER_FIRST_NAME%', esc_sql($wdtCurUserFirstName), $string);
        }
        if (strpos($string, '%CURRENT_USER_LAST_NAME%') !== false) {
            $currentUserLastNamePlaceholder = null;
            if (is_object($table) && isset($table->currentUserLastNamePlaceholder)) {
                $candidate = sanitize_text_field(wp_unslash((string) $table->currentUserLastNamePlaceholder));
                if (self::isTrustedUserLastNamePlaceholder($candidate)) {
                    $currentUserLastNamePlaceholder = $candidate;
                }
            }
            if (null === $currentUserLastNamePlaceholder && isset($_POST['currentUserLastName'])) {
                $candidate = sanitize_text_field(wp_unslash((string) $_POST['currentUserLastName']));
                if (self::isTrustedUserLastNamePlaceholder($candidate)) {
                    $currentUserLastNamePlaceholder = $candidate;
                }
            }

            $wdtCurUserLastName = null !== $currentUserLastNamePlaceholder ? $currentUserLastNamePlaceholder : wp_get_current_user()->user_lastname;

            $string = str_replace('%CURRENT_USER_LAST_NAME%', esc_sql($wdtCurUserLastName), $string);
        }
        if (strpos($string, '%CURRENT_USER_EMAIL%') !== false) {
            $currentUserEmailPlaceholder = null;
            if (is_object($table) && isset($table->currentUserEmailPlaceholder)) {
                $candidate = sanitize_email(wp_unslash((string) $table->currentUserEmailPlaceholder));
                if (self::isTrustedUserEmailPlaceholder($candidate)) {
                    $currentUserEmailPlaceholder = $candidate;
                }
            }
            if (null === $currentUserEmailPlaceholder && isset($_POST['currentUserEmail'])) {
                $candidate = sanitize_email(wp_unslash((string) $_POST['currentUserEmail']));
                if (self::isTrustedUserEmailPlaceholder($candidate)) {
                    $currentUserEmailPlaceholder = $candidate;
                }
            }

            $wdtCurUserEmail = null !== $currentUserEmailPlaceholder && '' !== $currentUserEmailPlaceholder
                ? $currentUserEmailPlaceholder
                : wp_get_current_user()->user_email;

            $string = str_replace('%CURRENT_USER_EMAIL%', esc_sql($wdtCurUserEmail), $string);
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
            $wpdb_prefix = $wpdb->prefix;
            if (self::canUseUntrustedPlaceholderOverrides()) {
                $raw_override = null;
                if (is_object($table) && isset($table->wpdbPlaceholder)) {
                    $raw_override = (string) wp_unslash($table->wpdbPlaceholder);
                } elseif (isset($_POST['wpdbPlaceholder'])) {
                    $raw_override = (string) wp_unslash($_POST['wpdbPlaceholder']);
                }

                $validated = self::sanitizeDbTablePrefixCandidate($raw_override);
                if (null !== $validated) {
                    $wpdb_prefix = $validated;
                }
            }

            $string = str_replace('%WPDB%', esc_sql($wpdb_prefix), $string);
        }
        // Shortcode VAR1
        if (strpos($string, '%VAR1%') !== false) {
            $string = str_replace('%VAR1%', esc_sql(self::sanitizeSqlVar($wdtVar1)), $string);
        }

        // Shortcode VAR2
        if (strpos($string, '%VAR2%') !== false) {
            $string = str_replace('%VAR2%', esc_sql(self::sanitizeSqlVar($wdtVar2)), $string);
        }

        // Shortcode VAR3
        if (strpos($string, '%VAR3%') !== false) {
            $string = str_replace('%VAR3%', esc_sql(self::sanitizeSqlVar($wdtVar3)), $string);
        }

        // Shortcode VAR4
        if (strpos($string, '%VAR4%') !== false) {
            $string = str_replace('%VAR4%', esc_sql(self::sanitizeSqlVar($wdtVar4)), $string);
        }

        // Shortcode VAR5
        if (strpos($string, '%VAR5%') !== false) {
            $string = str_replace('%VAR5%', esc_sql(self::sanitizeSqlVar($wdtVar5)), $string);
        }

        // Shortcode VAR6
        if (strpos($string, '%VAR6%') !== false) {
            $string = str_replace('%VAR6%', esc_sql(self::sanitizeSqlVar($wdtVar6)), $string);
        }

        // Shortcode VAR7
        if (strpos($string, '%VAR7%') !== false) {
            $string = str_replace('%VAR7%', esc_sql(self::sanitizeSqlVar($wdtVar7)), $string);
        }

        // Shortcode VAR8
        if (strpos($string, '%VAR8%') !== false) {
            $string = str_replace('%VAR8%', esc_sql(self::sanitizeSqlVar($wdtVar8)), $string);
        }

        // Shortcode VAR9
        if (strpos($string, '%VAR9%') !== false) {
            $string = str_replace('%VAR9%', esc_sql(self::sanitizeSqlVar($wdtVar9)), $string);
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
                $currentPostId = get_the_ID();
                if (!$currentPostId && isset($GLOBALS['post']) && is_object($GLOBALS['post']) && isset($GLOBALS['post']->ID)) {
                    $currentPostId = (int) $GLOBALS['post']->ID;
                }
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
                $value = str_replace('%VAR1%', self::sanitizeSqlVar($wdtVar1), $value);
            }// Shortcode VAR2
            if (strpos($value, '%VAR2%') !== false) {
                $value = str_replace('%VAR2%', self::sanitizeSqlVar($wdtVar2), $value);
            }// Shortcode VAR3
            if (strpos($value, '%VAR3%') !== false) {
                $value = str_replace('%VAR3%', self::sanitizeSqlVar($wdtVar3), $value);
            }// Shortcode VAR4
            if (strpos($value, '%VAR4%') !== false) {
                $value = str_replace('%VAR4%', self::sanitizeSqlVar($wdtVar4), $value);
            }// Shortcode VAR5
            if (strpos($value, '%VAR5%') !== false) {
                $value = str_replace('%VAR5%', self::sanitizeSqlVar($wdtVar5), $value);
            }// Shortcode VAR6
            if (strpos($value, '%VAR6%') !== false) {
                $value = str_replace('%VAR6%', self::sanitizeSqlVar($wdtVar6), $value);
            }// Shortcode VAR7
            if (strpos($value, '%VAR7%') !== false) {
                $value = str_replace('%VAR7%', self::sanitizeSqlVar($wdtVar7), $value);
            }// Shortcode VAR8
            if (strpos($value, '%VAR8%') !== false) {
                $value = str_replace('%VAR8%', self::sanitizeSqlVar($wdtVar8), $value);
            }// Shortcode VAR9
            if (strpos($value, '%VAR9%') !== false) {
                $value = str_replace('%VAR9%', self::sanitizeSqlVar($wdtVar9), $value);
            }
        }

        return $value;
    }

    /**
     * @param mixed $value Raw VAR placeholder value.
     * @return string
     */
    private static function sanitizeSqlVar($value)
    {
        return \wdtSanitizeSqlPlaceholderValue($value);
    }

    /**
     * Whether client-supplied placeholder overrides are allowed without matching the logged-in user / post context.
     *
     * @return bool
     */
    private static function canUseUntrustedPlaceholderOverrides()
    {
        return current_user_can('manage_options');
    }

    /**
     * @param int $candidate Sanitized candidate user ID.
     * @return bool
     */
    private static function isTrustedUserIdPlaceholder($candidate)
    {
        if (self::canUseUntrustedPlaceholderOverrides()) {
            return true;
        }

        return (int) $candidate === (int) get_current_user_id();
    }

    /**
     * @param string $candidate Sanitized candidate login.
     * @return bool
     */
    private static function isTrustedUserLoginPlaceholder($candidate)
    {
        if (self::canUseUntrustedPlaceholderOverrides()) {
            return true;
        }

        $user = wp_get_current_user();
        if (!$user || !$user->exists()) {
            return '' === $candidate;
        }

        return hash_equals($user->user_login, $candidate);
    }

    /**
     * @param int $candidate Sanitized candidate post ID.
     * @param int $server_post_id Resolved post ID from WordPress context.
     * @return bool
     */
    private static function isTrustedPostIdPlaceholder($candidate, $server_post_id)
    {
        if (self::canUseUntrustedPlaceholderOverrides()) {
            return true;
        }

        return (int) $candidate === (int) $server_post_id;
    }

    /**
     * @param string $candidate Sanitized candidate display name.
     * @return bool
     */
    private static function isTrustedUserDisplayNamePlaceholder($candidate)
    {
        if (self::canUseUntrustedPlaceholderOverrides()) {
            return true;
        }

        $user = wp_get_current_user();
        if (!$user || !$user->exists()) {
            return '' === $candidate;
        }

        return hash_equals((string) $user->display_name, $candidate);
    }

    /**
     * @param string $candidate Sanitized candidate first name.
     * @return bool
     */
    private static function isTrustedUserFirstNamePlaceholder($candidate)
    {
        if (self::canUseUntrustedPlaceholderOverrides()) {
            return true;
        }

        $user = wp_get_current_user();
        if (!$user || !$user->exists()) {
            return '' === $candidate;
        }

        return hash_equals((string) $user->user_firstname, $candidate);
    }

    /**
     * @param string $candidate Sanitized candidate last name.
     * @return bool
     */
    private static function isTrustedUserLastNamePlaceholder($candidate)
    {
        if (self::canUseUntrustedPlaceholderOverrides()) {
            return true;
        }

        $user = wp_get_current_user();
        if (!$user || !$user->exists()) {
            return '' === $candidate;
        }

        return hash_equals((string) $user->user_lastname, $candidate);
    }

    /**
     * @param string $candidate Sanitized candidate email.
     * @return bool
     */
    private static function isTrustedUserEmailPlaceholder($candidate)
    {
        if (self::canUseUntrustedPlaceholderOverrides()) {
            return true;
        }

        $user = wp_get_current_user();
        if (!$user || !$user->exists()) {
            return '' === $candidate;
        }

        if ('' === $candidate) {
            return false;
        }

        return 0 === strcasecmp((string) $user->user_email, $candidate);
    }

    /**
     * Validate a table prefix override for %WPDB% (admin-only callers).
     *
     * @param string|null $candidate Raw prefix from request.
     * @return string|null Valid prefix or null if invalid.
     */
    private static function sanitizeDbTablePrefixCandidate($candidate)
    {
        if (null === $candidate || '' === $candidate) {
            return null;
        }

        $candidate = trim($candidate);
        if (strlen($candidate) > 64 || !preg_match('/^[A-Za-z0-9_]+$/', $candidate)) {
            return null;
        }

        return $candidate;
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
