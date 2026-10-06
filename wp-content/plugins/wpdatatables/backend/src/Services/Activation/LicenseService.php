<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Activation;

use WPDataTables\Common\Helpers\UrlHelper;

/**
 * Envato activation and plugin update metadata.
 *
 * @package WPDataTables\Services\Activation
 */
class LicenseService
{
    /**
     * Remote information cache.
     *
     * @var array<string, bool|object>
     */
    private static $_remoteInformationCache = array();

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
            $serverName = (defined('WP_CLI') && WP_CLI) ? php_uname('n') : $_SERVER['SERVER_NAME'];
            $slug = trim((string) $slug);
            $purchaseCode = trim((string) $purchaseCode);
            $envatoTokenEmail = trim((string) $envatoTokenEmail);
            $domain = UrlHelper::getDomain($serverName);
            $subdomain = UrlHelper::getSubDomain($serverName);

            $cacheKey = hash(
                'sha256',
                serialize(array($slug, $purchaseCode, $envatoTokenEmail, $domain, $subdomain))
            );

            if (array_key_exists($cacheKey, self::$_remoteInformationCache)) {
                return self::$_remoteInformationCache[$cacheKey];
            }

            $request = wp_remote_post(
                WDT_STORE_API_URL . 'autoupdate/info',
                [
                    'body' => [
                        'slug' => $slug,
                        'purchaseCode' => $purchaseCode,
                        'envatoTokenEmail' => $envatoTokenEmail,
                        'domain' => $domain,
                        'subdomain' => $subdomain
                    ]
                ]
            );

            $remoteInformation = false;

            if (!is_wp_error($request) && wp_remote_retrieve_response_code($request) === 200 && isset($request['body'])) {
                $body = json_decode($request['body']);
                $info = $body && isset($body->info)
                    ? unserialize($body->info, array('allowed_classes' => array(\stdClass::class)))
                    : false;

                if ($info instanceof \stdClass && !($info instanceof \__PHP_Incomplete_Class) && isset($info->new_version)) {
                    $remoteInformation = $info;
                }
            }

            self::$_remoteInformationCache[$cacheKey] = $remoteInformation;

            return $remoteInformation;
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
         * Helper function that returns all update info
         * TODO (Update before new versions)
         * @return array
         */
        public static function getUpdateInfo()
        {
            return array(
                'version'  => get_option('wdtVersion'),
                'release_date' => '09.09.2026.',
                'features' => [
                    0 => [
                        'text' => 'Added a public REST API for tables and data (Developer licence).',
                        'link' => ''
                    ],
                    1 => [
                        'text' => 'Added MCP support so AI assistants can work with wpDataTables using the user permission system.',
                        'link' => ''
                    ],
                    2 => [
                        'text' => 'Added role- and user-based permissions for tables and charts.',
                        'link' => ''
                    ],
                    3 => [
                        'text' => 'Added Angie AI (Elementor AI Agent) integration.',
                        'link' => ''
                    ],
                    4 => [
                        'text' => 'Added AI-powered SQL/query generation and chart suggestions.',
                        'link' => ''
                    ],
                    5 => [
                        'text' => 'Added webhooks for table data events.',
                        'link' => ''
                    ],
                    6 => [
                        'text' => 'Added server-side support for updating IvyForms entries from wpDataTables.',
                        'link' => ''
                    ],
                    7 => [
                        'text' => 'Added the ability to customize the "No matching records found" message per table via the Custom Strings tab in table settings.',
                        'link' => ''
                    ],
                ],
                'improvements' => [
                    // 0 => [
                    //     'text' => '',
                    //     'link' => ''
                    // ],
                ],
                'bugfixes' => [
                    0 => [
                        'text' => 'Fixed reflected XSS in Browse Tables pagination via an unsanitized search parameter.',
                        'link' => ''
                    ],
                    1 => [
                        'text' => 'Fixed unauthenticated SQL injection via aggregate columns (sum/avg/min/max) in server-side AJAX.',
                        'link' => ''
                    ],
                    2 => [
                        'text' => 'Fixed SQL column aliases breaking filtering on server-side processing tables.',
                        'link' => ''
                    ],
                    3 => [
                        'text' => 'Fixed separate database connection fields not displaying after enabling "Use separate connection".',
                        'link' => ''
                    ],
                    4 => [
                        'text' => 'Fixed a JavaScript error and page freeze with Fixed Header when Excel or Excel+Manual tables are on the same page.',
                        'link' => ''
                    ],
                    5 => [
                        'text' => 'Fixed same-domain URLs being corrupted in the Manual table HTML editor after save.',
                        'link' => ''
                    ],
                    6 => [
                        'text' => 'Fixed placeholder values containing the word "AND" being parsed as a SQL operator and breaking filters.',
                        'link' => ''
                    ],
                    7 => [
                        'text' => 'Fixed an MCP server error (create_server failed / ErrorLogMcpErrorHandler interface mismatch).',
                        'link' => ''
                    ],
                    8 => [
                        'text' => 'Fixed Forminator notice text contrast so the message remains readable.',
                        'link' => ''
                    ],
                    9 => [
                        'text' => 'Fixed a fatal PHP error on table shortcode render caused by incorrect WDTTools class name casing.',
                        'link' => ''
                    ],
                    10 => [
                        'text' => 'Fixed a fatal PHP error when fetching remote data caused by a missing Exception import in the HTTP client.',
                        'link' => ''
                    ],
                    11 => [
                        'text' => 'Fixed fatal PHP errors when loading public Google Sheets and other namespaced data-processing paths.',
                        'link' => ''
                    ],
                    12 => [
                        'text' => 'Fixed derived-table subqueries failing during server-side filtering and row counting.',
                        'link' => ''
                    ],
                    13 => [
                        'text' => 'Fixed own-row editing permissions failing because the database connection class was resolved incorrectly.',
                        'link' => ''
                    ],
                    14 => [
                        'text' => 'Fixed the User ID column selection not displaying after reload and not being possible to clear.',
                        'link' => ''
                    ],
                ],
            );
        }

    /**
         * Helper function that returns an array with deactivation info
         * @return array
         */
        public static function getDeactivationInfo()
        {
            return array(
                'version' => get_option('wdtVersion'),
                'wdt_nonce' => wp_nonce_field('wdtDeactivationNonce', 'wdtNonce'),
                'titleDeactivation' => __('QUICK FEEDBACK', 'wpdatatables'),
                'captionDeactivation' => __('If you have a moment, please let us know why you are deactivating the wpDataTables plugin:', 'wpdatatables'),
                'captionDeactivationError' => __('Please select one option from the following list: ', 'wpdatatables'),
                'deactivate_reasons' => [
                    0 => [
                        'id' => 'feature_needed',
                        'title' => esc_html__('The plugin doesn’t have a feature that I need'),
                        'input_placeholder' => esc_html__('Please explain your use case and the feature you need: '),
                        'alert' => '',
                    ],
                    1 => [
                        'id' => 'premium_version',
                        'title' => esc_html__('I bought the premium version'),
                        'input_placeholder' => '',
                        'alert' => '',
                    ],
                    2 => [
                        'id' => 'stopped_working',
                        'title' => esc_html__('The plugin suddenly stopped working'),
                        'input_placeholder' => esc_html__('Tell us more… '),
                        'alert' => esc_html__('Have you reached out to our support team?'),
                    ],
                    3 => [
                        'id' => 'broke_my_site',
                        'title' => esc_html__('The plugin broke my site'),
                        'input_placeholder' => esc_html__('Tell us more… '),
                        'alert' => esc_html__('Have you reached out to our support team?'),
                    ],
                    4 => [
                        'id' => 'better_plugin',
                        'title' => esc_html__('I found a better plugin'),
                        'input_placeholder' => esc_html__('Please share which plugin: '),
                        'alert' => '',
                    ],
                    5 => [
                        'id' => 'temporary_deactivation',
                        'title' => esc_html__('It is a temporary deactivation - I’m troubleshooting an issue'),
                        'input_placeholder' => '',
                        'alert' => '',
                    ],
                    6 => [
                        'id' => 'able_to_work',
                        'title' => esc_html__('I haven’t been able to get the plugin to work'),
                        'input_placeholder' => esc_html__('Tell us more… '),
                        'alert' => esc_html__('Have you reached out to our support team?'),
                    ],
                    7 => [
                        'id' => 'no_longer_needed',
                        'title' => esc_html__('I no longer need the plugin'),
                        'input_placeholder' => esc_html__('Please share more about your use case: '),
                        'alert' => '',
                    ],
                    8 => [
                        'id' => 'conflict',
                        'title' => esc_html__('The plugin has a conflict with the theme or other plugin'),
                        'input_placeholder' => esc_html__('Please share which plugin/theme: '),
                        'alert' => esc_html__('Have you reached out to our support team?'),
                    ],
                    9 => [
                        'id' => 'other',
                        'title' => esc_html__('Other'),
                        'input_placeholder' => esc_html__('How could we improve? '),
                        'alert' => '',
                    ],
                ]
            );
        }

    public static function getGoogleApiMapsKey()
        {
            return array(
                'wdtGoogleApiMaps' => get_option('wdtGoogleApiMaps'),
                'wdtGoogleApiMapsValidated' => get_option('wdtGoogleApiMapsValidated'),
                'wdtGlobalChartLoader' => get_option('wdtGlobalChartLoader'),
            );
        }
}
