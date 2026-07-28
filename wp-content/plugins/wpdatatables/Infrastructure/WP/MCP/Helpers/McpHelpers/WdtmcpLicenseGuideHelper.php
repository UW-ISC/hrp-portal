<?php

/**
 * Licence / tier guidance for wpDataTables MCP abilities (upgrade hints, matrix).
 *
 * @package wpDataTables
 */

namespace WDTMCP\Helpers\McpHelpers {

defined('ABSPATH') or die('Access denied.');

class WdtmcpLicenseGuideHelper
{
    public const PRICING_URL = 'https://wpdatatables.com/pricing/';

    public static function pricingUrl(): string
    {
        return self::PRICING_URL;
    }

    /**
     * Compare tiers (free < starter < standard < pro < developer).
     */
    public static function tierRank(string $tier): int
    {
        $tier = strtolower($tier);
        $map = array(
            'free' => 0,
            'starter' => 1,
            'standard' => 2,
            'pro' => 3,
            'developer' => 4,
        );

        return isset($map[$tier]) ? $map[$tier] : 0;
    }

    public static function tierLabel(string $tier): string
    {
        $tier = strtolower($tier);
        $labels = array(
            'free' => 'Free',
            'starter' => 'Starter',
            'standard' => 'Standard',
            'pro' => 'Pro',
            'developer' => 'Developer',
        );

        return isset($labels[$tier]) ? $labels[$tier] : ucfirst($tier);
    }

    /**
     * Minimum commercial tier that historically bundles an integration directory entry.
     *
     * @return array<string, string> integration_key => tier slug
     */
    public static function integrationMinimumTierMap(): array
    {
        return array(
            'google_sheet_api' => 'starter',
            'separate_db' => 'standard',
            'sql_constructor' => 'standard',
            'sql_query' => 'standard',
            'highcharts' => 'standard',
            'apexcharts' => 'standard',
            'editing' => 'standard',
            'placeholders' => 'standard',
            'foreign_key' => 'standard',
            'formula_column' => 'standard',
            'hidden_column' => 'standard',
            'fixed_col_header' => 'standard',
            'index_column' => 'standard',
            'update_manual_file' => 'standard',
            'highstock' => 'pro',
            'folders' => 'pro',
            'wp_posts_builder' => 'pro',
            'woocommerce' => 'pro',
        );
    }

    /**
     * Highest tier among a list of integration keys.
     *
     * @param string[] $integrationKeys
     */
    public static function highestTierForIntegrations(array $integrationKeys): string
    {
        $map = self::integrationMinimumTierMap();
        $best = 'free';
        foreach ($integrationKeys as $key) {
            $key = (string) $key;
            if (! isset($map[$key])) {
                continue;
            }
            $candidate = $map[$key];
            if (self::tierRank($candidate) > self::tierRank($best)) {
                $best = $candidate;
            }
        }

        return $best;
    }

    /**
     * @param string[] $integrationKeys Keys from {@see wdtmcp_detect_integrations()}.
     */
    public static function integrationsSatisfied(array $integrations, array $integrationKeys): bool
    {
        foreach ($integrationKeys as $key) {
            if (empty($integrations[(string) $key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Attach licence-upgrade metadata for MCP clients (structured + human-readable suffix).
     *
     * @param string[] $integrationKeys
     */
    public static function licenseWpError(
        string $code,
        string $message,
        string $minimumTier,
        array $integrationKeys = array(),
        $relatedAbilityId = null
    ): \WP_Error {
        $pricing = self::pricingUrl();
        $tierLabel = self::tierLabel($minimumTier);
        $suffix = sprintf(
            /* translators: 1: tier label (e.g. Standard), 2: pricing URL */
            __(' Upgrade your wpDataTables license to %1$s (or higher) to unlock this. Pricing: %2$s', 'wpdatatables'),
            $tierLabel,
            $pricing
        );

        $data = array(
            'wdtmcp_license' => array(
                'pricing_url' => $pricing,
                'minimum_tier' => strtolower($minimumTier),
                'minimum_tier_label' => $tierLabel,
                'required_integration_keys' => array_values(array_unique(array_map('strval', $integrationKeys))),
                'related_ability_id' => $relatedAbilityId !== null ? (string) $relatedAbilityId : null,
                'investigate_ability_id' => 'wpdatatables/get-mcp-license-matrix',
                'investigate_hint' => __(
                    'Call wpdatatables/get-mcp-license-matrix for which MCP abilities are limited on this site and which integrations are missing.',
                    'wpdatatables'
                ),
            ),
        );

        return new \WP_Error($code, $message . $suffix, $data);
    }

    /**
     * Rows documented for get-mcp-license-matrix (AI-facing).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function abilityMatrixDefinitions(): array
    {
        return array(
            array(
                'ability_id' => 'wpdatatables/get-system-info',
                'label' => 'Get wpDataTables System Info',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => 'Always call first; exposes detected_tier and integration file flags.',
            ),
            array(
                'ability_id' => 'wpdatatables/get-mcp-license-matrix',
                'label' => 'Get MCP Licence Matrix',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => 'Explains MCP abilities vs this installation (readonly).',
            ),
            array(
                'ability_id' => 'wpdatatables/list-tables',
                'label' => 'List Tables',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => '',
            ),
            array(
                'ability_id' => 'wpdatatables/get-table-info',
                'label' => 'Get Table Info',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => '',
            ),
            array(
                'ability_id' => 'wpdatatables/get-table-data',
                'label' => 'Get Table Data',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => '',
            ),
            array(
                'ability_id' => 'wpdatatables/list-charts',
                'label' => 'List Charts',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => '',
            ),
            array(
                'ability_id' => 'wpdatatables/get-chart-info',
                'label' => 'Get Chart Info',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => '',
            ),
            array(
                'ability_id' => 'wpdatatables/create-chart',
                'label' => 'Create Chart',
                'minimum_tier_hint' => 'standard',
                'required_integration_keys' => array(),
                'notes' => 'Engines google/chartjs work broadly; highcharts requires highcharts integration files (Standard+). apexcharts requires apexcharts integration files (Standard+).',
            ),
            array(
                'ability_id' => 'wpdatatables/edit-chart',
                'label' => 'Edit Chart',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => 'Cannot change engine; existing HighCharts/Apex charts require those integrations to remain usable.',
            ),
            array(
                'ability_id' => 'wpdatatables/create-simple-table',
                'label' => 'Create Simple Table',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => '',
            ),
            array(
                'ability_id' => 'wpdatatables/update-simple-table-styles',
                'label' => 'Update Simple Table Styles',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => '',
            ),
            array(
                'ability_id' => 'wpdatatables/update-table-settings',
                'label' => 'Update Table Settings',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => 'Some column types/features inside wp-admin still depend on Standard integrations when enabled.',
            ),
            array(
                'ability_id' => 'wpdatatables/edit-table',
                'label' => 'Edit Table',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => 'Dispatches to simple vs standard updates.',
            ),
            array(
                'ability_id' => 'wpdatatables/update-global-settings',
                'label' => 'Update Global Settings',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => '',
            ),
            array(
                'ability_id' => 'wpdatatables/list-media',
                'label' => 'List Media',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => '',
            ),
            array(
                'ability_id' => 'wpdatatables/upload-media-from-url',
                'label' => 'Upload Media From URL',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => '',
            ),
            array(
                'ability_id' => 'wpdatatables/upload-data-file',
                'label' => 'Upload Data File',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => '',
            ),
            array(
                'ability_id' => 'wpdatatables/create-table-from-source',
                'label' => 'Create Table From Source',
                'minimum_tier_hint' => 'starter',
                'required_integration_keys' => array(),
                'notes' => 'google_spreadsheet source_type requires google_sheet_api integration (Starter+). Other source types typically work without Starter.',
            ),
            array(
                'ability_id' => 'wpdatatables/import-table-from-source',
                'label' => 'Import Table From Source',
                'minimum_tier_hint' => 'standard',
                'required_integration_keys' => array('update_manual_file'),
                'notes' => 'One-time MySQL import from CSV/XLS/Google Sheet. Google spreadsheet import also needs google_sheet_api (Starter+ files).',
            ),
            array(
                'ability_id' => 'wpdatatables/create-table-from-query',
                'label' => 'Create Table From Query',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => 'MySQL on the default WordPress connection works broadly; non-empty connection targets separate DB integration files (Standard+).',
            ),
            array(
                'ability_id' => 'wpdatatables/list-db-tables',
                'label' => 'List Database Tables',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => 'Default WP database always works; non-empty connection requires separate_db integration files (Standard+).',
            ),
            array(
                'ability_id' => 'wpdatatables/describe-db-table',
                'label' => 'Describe Database Table',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => 'Default WP database always works; non-empty connection requires separate_db integration files (Standard+).',
            ),
            array(
                'ability_id' => 'wpdatatables/get-changelog',
                'label' => 'Get Changelog',
                'minimum_tier_hint' => 'free',
                'required_integration_keys' => array(),
                'notes' => '',
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function buildLicenseMatrixPayload(array $integrations, string $detectedTier): array
    {
        $pricing = self::pricingUrl();
        $supportsSeparateDb = self::integrationsSatisfied($integrations, array('separate_db'));

        $rows = array();
        foreach (self::abilityMatrixDefinitions() as $def) {
            $required = isset($def['required_integration_keys']) && is_array($def['required_integration_keys'])
                ? $def['required_integration_keys']
                : array();

            $missing = array();
            foreach ($required as $key) {
                if (empty($integrations[(string) $key])) {
                    $missing[] = (string) $key;
                }
            }

            $rows[] = array(
                'ability_id' => $def['ability_id'],
                'label' => $def['label'],
                'minimum_tier_hint' => $def['minimum_tier_hint'],
                'required_integration_keys' => $required,
                'notes' => isset($def['notes']) ? (string) $def['notes'] : '',
                'fully_supported_on_this_site' => empty($missing),
                'missing_integration_keys' => $missing,
            );
        }

        return array(
            'pricing_url' => $pricing,
            'detected_tier' => $detectedTier,
            'tier_order' => array('free', 'starter', 'standard', 'pro', 'developer'),
            'upgrade_hint' => sprintf(
                /* translators: %s: pricing URL */
                __('Higher tiers bundle additional integration files on disk (detected via get-system-info). Compare tiers at %s.', 'wpdatatables'),
                $pricing
            ),
            'integrations' => $integrations,
            'supports_non_wp_db_connections' => $supportsSeparateDb,
            'abilities' => $rows,
        );
    }

    /**
     * Non-empty connection targets registered separate connections (Standard+ integration files).
     *
     * @return true|\WP_Error
     */
    public static function assertSeparateDbConnectionAllowed(string $connectionId, string $relatedAbilityId)
    {
        $connectionId = trim((string) $connectionId);
        if ('' === $connectionId) {
            return true;
        }

        if (function_exists('wdtmcp_is_integration_file_available')
            && wdtmcp_is_integration_file_available('separate_db')) {
            return true;
        }

        return self::licenseWpError(
            'wdtmcp_tier_required',
            __(
                'Separate database connections require wpDataTables Standard tier integration files (Separate DB Connection).',
                'wpdatatables'
            ),
            'standard',
            array('separate_db'),
            $relatedAbilityId
        );
    }

    /**
     * HighCharts / ApexCharts engines require Standard-tier integration files present on disk.
     *
     * @return true|\WP_Error
     */
    public static function assertChartEngineAvailable(string $engine, string $relatedAbilityId)
    {
        $engine = strtolower((string) $engine);
        if (in_array($engine, array('google', 'chartjs'), true)) {
            return true;
        }

        if (! function_exists('wdtmcp_detect_integrations')) {
            return true;
        }

        $integrations = wdtmcp_detect_integrations();

        if ('highcharts' === $engine) {
            if (! empty($integrations['highcharts'])) {
                return true;
            }

            return self::licenseWpError(
                'wdtmcp_integration_missing',
                __('HighCharts is not available on this installation (integration files missing).', 'wpdatatables'),
                'standard',
                array('highcharts'),
                $relatedAbilityId
            );
        }

        if ('apexcharts' === $engine) {
            if (! empty($integrations['apexcharts'])) {
                return true;
            }

            return self::licenseWpError(
                'wdtmcp_integration_missing',
                __('ApexCharts is not available on this installation (integration files missing).', 'wpdatatables'),
                'standard',
                array('apexcharts'),
                $relatedAbilityId
            );
        }

        return true;
    }
}

}

namespace {

use WDTMCP\Helpers\McpHelpers\WdtmcpLicenseGuideHelper;

if (! function_exists('wdtmcp_pricing_url')) {
    /**
     * Official wpDataTables pricing URL for MCP upgrade messaging.
     */
    function wdtmcp_pricing_url(): string
    {
        return WdtmcpLicenseGuideHelper::pricingUrl();
    }
}

if (! function_exists('wdtmcp_license_wp_error')) {
    /**
     * WP_Error with licence-upgrade metadata for MCP consumers.
     *
     * @param string[] $integration_keys
     */
    function wdtmcp_license_wp_error(
        string $code,
        string $message,
        string $minimum_tier,
        array $integration_keys = array(),
        $related_ability_id = null
    ): \WP_Error {
        return WdtmcpLicenseGuideHelper::licenseWpError(
            $code,
            $message,
            $minimum_tier,
            $integration_keys,
            $related_ability_id
        );
    }
}

if (! function_exists('wdtmcp_assert_separate_db_connection_allowed')) {
    /**
     * @param string $connection_id
     * @param string $related_ability_id
     * @return true|\WP_Error
     */
    function wdtmcp_assert_separate_db_connection_allowed($connection_id, $related_ability_id)
    {
        return WdtmcpLicenseGuideHelper::assertSeparateDbConnectionAllowed(
            (string) $connection_id,
            (string) $related_ability_id
        );
    }
}

if (! function_exists('wdtmcp_assert_chart_engine_available')) {
    /**
     * @param string $engine
     * @param string $related_ability_id
     * @return true|\WP_Error
     */
    function wdtmcp_assert_chart_engine_available($engine, $related_ability_id)
    {
        return WdtmcpLicenseGuideHelper::assertChartEngineAvailable(
            (string) $engine,
            (string) $related_ability_id
        );
    }
}

}
