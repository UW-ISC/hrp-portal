<?php

/**
 * Feature gate for wpDataTables MCP (AI assistants / Angie).
 *
 * @package wpDataTables
 */

namespace WDTMCP\Infrastructure\WP\MCP;

defined('ABSPATH') or die('Access denied.');

/**
 * Central opt-out for MCP bootstrap and Angie proxy enqueue.
 *
 * MCP is ON by default. Site owners can disable it via Settings → Main settings
 * ("Enable MCP for AI assistants") or force a value with the
 * {@see self::FILTER_ENABLED} filter (e.g. from an mu-plugin).
 */
class WdtmcpFeatureGate
{
    public const FILTER_ENABLED = 'wpdatatables/mcp/enabled';

    public const OPTION_KEY = 'wdtMcpEnabled';

    /**
     * Whether MCP should bootstrap routes/abilities and Angie registration.
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return (bool) apply_filters(self::FILTER_ENABLED, self::isEnabledInStoredSettings());
    }

    /**
     * Whether MCP is enabled in stored plugin settings (default ON).
     *
     * @return bool
     */
    private static function isEnabledInStoredSettings(): bool
    {
        if (!function_exists('get_option')) {
            return true;
        }

        // Default ON when the option has never been saved.
        $stored = get_option(self::OPTION_KEY, true);

        return self::coerceStoredEnabled($stored);
    }

    /**
     * Coerce a stored wdtMcpEnabled value to a boolean, defaulting to enabled.
     *
     * @param mixed $raw_enabled Raw option value.
     * @return bool
     */
    public static function coerceStoredEnabled($raw_enabled): bool
    {
        if (is_bool($raw_enabled)) {
            return $raw_enabled;
        }

        if (is_string($raw_enabled) && trim($raw_enabled) === '') {
            return true;
        }

        if (is_string($raw_enabled) || is_int($raw_enabled)) {
            return filter_var($raw_enabled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
        }

        return true;
    }
}
