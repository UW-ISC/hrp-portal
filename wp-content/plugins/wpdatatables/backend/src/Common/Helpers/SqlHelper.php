<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Helpers;

use Connection;
use WPDataTables\Services\Tools\ToolsService;

/**
 * SQL literal preparation and frontend form-data hardening.
 *
 * Legacy {@see WDTTools} methods and {@see wdtSanitizeSqlPlaceholderValue()}
 * delegate here.
 *
 * @package WPDataTables\Common\Helpers
 */
class SqlHelper
{
    /**
     * Sanitize a shortcode / AJAX placeholder value before SQL substitution.
     *
     * @param mixed $value Raw placeholder value.
     * @return string Safe value, or empty string if rejected.
     */
    public static function sanitizeSqlPlaceholderValue($value): string
    {
        if (null === $value || '' === $value) {
            return '';
        }

        $value = (string) wp_unslash($value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

        if (preg_match('/[`\'";()\\\\]|--|#|\/\*|\*\//', $value)) {
            return '';
        }

        if (preg_match(
            '/\b(or|and|union|select|insert|update|delete|drop|create|alter|truncate|sleep|benchmark|'
            . 'where|from|having|group|order|limit|offset|into|join|exists|information_schema|'
            . 'load_file|outfile|dumpfile|extractvalue|updatexml|substring|ascii|concat|char|mid|'
            . 'hex|unhex|pg_sleep|waitfor|delay|procedure|handler)\b/i',
            $value
        )) {
            return '';
        }

        return (string) apply_filters('wpdatatables_sanitize_sql_placeholder_value', $value);
    }

    /**
     * Restrict frontend form data to configured column headers.
     *
     * @param array $formData    Associative form data keyed by column name.
     * @param array $columnsData Column configuration objects from the database.
     * @return array
     */
    public static function filterFormDataToKnownColumns(array $formData, array $columnsData): array
    {
        $allowedHeaders = array();

        foreach ($columnsData as $column) {
            $allowedHeaders[] = $column->orig_header;
        }

        return array_intersect_key($formData, array_flip($allowedHeaders));
    }

    /**
     * Format a row ID for a raw SQL WHERE clause on external database connections.
     *
     * @param mixed  $value      Raw ID value from the request.
     * @param string $columnType wpDataTables column type.
     * @param mixed  $connection Connection identifier or null for WordPress DB.
     * @return string|int
     */
    public static function formatSqlWhereIdValue($value, string $columnType, $connection)
    {
        if ('int' === $columnType) {
            return (int) $value;
        }

        if ('float' === $columnType) {
            return (float) $value;
        }

        return ToolsService::prepareStringCell($value, $connection);
    }

    /**
     * Prepare a quoted SQL string literal for server-side search WHERE clauses.
     *
     * @param string $value      Raw search value.
     * @param mixed  $connection Connection id or null for WP MySQL.
     * @return string
     */
    public static function prepareSearchLiteral(string $value, $connection): string
    {
        $vendor = Connection::getVendor($connection);

        if ($vendor === Connection::$MYSQL && ! Connection::isSeparate($connection)) {
            global $wpdb;

            return $wpdb->prepare('%s', $value);
        }

        return ToolsService::prepareStringCell($value, $connection);
    }

    /**
     * Build a vendor-escaped IN (...) list from foreign-key store values.
     *
     * @param array $values Map of store-column keys (used as IN list members).
     * @param mixed $connection Connection id or null for WP MySQL.
     * @return string
     */
    public static function buildInListFromKeys(array $values, $connection): string
    {
        $literals = array();
        foreach (array_keys($values) as $key) {
            $literals[] = self::prepareSearchLiteral((string) $key, $connection);
        }

        return implode(', ', $literals);
    }

    /**
     * Build a connection-aware LIKE comparison SQL fragment.
     *
     * @param string $qualifiedColumn Fully qualified column reference.
     * @param string $rawValue        Raw search term (without wildcards).
     * @param mixed  $connection      Connection id or null for WP MySQL.
     * @param string $prefix          Wildcard prefix (e.g. '%').
     * @param string $suffix          Wildcard suffix (e.g. '%').
     * @param bool   $useLowerCast    Use LOWER(CAST(...)) for PostgreSQL text columns.
     * @return string
     */
    public static function buildLikeComparison(
        string $qualifiedColumn,
        string $rawValue,
        $connection,
        string $prefix = '%',
        string $suffix = '%',
        bool $useLowerCast = false
    ): string {
        $vendor = Connection::getVendor($connection);

        if ($vendor === Connection::$MYSQL && ! Connection::isSeparate($connection)) {
            global $wpdb;
            $pattern = $prefix . $wpdb->esc_like($rawValue) . $suffix;

            return $qualifiedColumn . ' LIKE ' . $wpdb->prepare('%s', $pattern);
        }

        if ($vendor === Connection::$MSSQL) {
            $pattern = $prefix . str_replace(
                array('\\', '%', '_', '[', ']'),
                array('\\\\', '\\%', '\\_', '\\[', '\\]'),
                $rawValue
            ) . $suffix;

            return $qualifiedColumn . ' LIKE ' . ToolsService::prepareStringCell($pattern, $connection) . " ESCAPE '\\'";
        }

        $pattern = $prefix . str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $rawValue) . $suffix;
        $literal = ToolsService::prepareStringCell($pattern, $connection);

        if ($vendor === Connection::$POSTGRESQL && $useLowerCast) {
            return 'LOWER(CAST(' . $qualifiedColumn . ' AS TEXT)) LIKE LOWER(' . $literal . ') ';
        }

        return $qualifiedColumn . ' LIKE ' . $literal;
    }

    /**
     * Sanitize wdt_search URL parameter with entity-decode loop (XSS defense-in-depth).
     *
     * @param mixed $raw Raw $_GET['wdt_search'] value.
     * @return string
     */
    public static function sanitizeSearchQueryParam($raw): string
    {
        $wdtSearch = wp_unslash((string) $raw);
        do {
            $previousSearch = $wdtSearch;
            $wdtSearch = html_entity_decode($wdtSearch, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } while ($wdtSearch !== $previousSearch);

        return sanitize_text_field($wdtSearch);
    }
}
