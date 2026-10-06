<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Table;

use WPDataTables\Services\Tools\ToolsService;

/**
 * Filtering helpers for the table engine.
 *
 * Owns the vendor-specific SQL fragments used when the server-side processor
 * builds a WHERE clause from a DataTables request: the per-vendor `LIKE`
 * expression and the per-vendor date/time casting expression used by range
 * filters.
 *
 * This is the seam owner for filter-clause construction. The interleaved
 * WHERE-building loop stays inside
 * {@see \WPDataTables\Services\DataSource\MySqlQueryDataSource}; only the
 * cleanly-separable vendor helpers live here. The global
 * `WPDataTable::getDateTimeExpression()` is preserved as a thin delegator onto
 * this service so existing callers and pro add-ons keep working.
 *
 * @package WPDataTables\Services\Table
 */
class FilterService
{
    /**
     * Return the vendor-specific LIKE expression.
     *
     * @param string $vendor
     * @param string $table   Already-quoted table identifier.
     * @param string $column  Already-quoted column identifier.
     * @param string $rawValue Raw search term without wildcards.
     * @param mixed  $connection Connection id or null for WP MySQL.
     * @param string $prefix Wildcard prefix.
     * @param string $suffix Wildcard suffix.
     *
     * @return string|void
     */
    public function getLikeExpression(
        $vendor,
        $table,
        $column,
        $rawValue,
        $connection = null,
        $prefix = '%',
        $suffix = '%'
    )
    {
        $qualified = $table . '.' . $column;

        return ToolsService::buildLikeComparison(
            $qualified,
            $rawValue,
            $connection,
            $prefix,
            $suffix,
            $vendor === \Connection::$POSTGRESQL
        );
    }

    /**
     * Return the vendor-specific date/time expression for a range filter value.
     *
     * @param string $vendor
     * @param string $filterType
     * @param string $value
     * @param mixed  $connection Connection id or null for WP MySQL.
     *
     * @return string|void
     */
    public function getDateTimeExpression($vendor, $filterType, $value, $connection = null)
    {
        $wpDateFormat = get_option('wdtDateFormat');

        $date_format = '';

        if ($vendor === \Connection::$MYSQL) {
            if ($filterType != 'time-range') {
                $date_format = str_replace('m', '%m', $wpDateFormat);
                $date_format = str_replace('M', '%M', $date_format);
                $date_format = str_replace('Y', '%Y', $date_format);
                $date_format = str_replace('y', '%y', $date_format);
                $date_format = str_replace('d', '%d', $date_format);
                $date_format = str_replace('F', '%M', $date_format);
                $date_format = str_replace('j', '%d', $date_format);
                $date_format = str_replace('D', '%W', $date_format);
            }
            if ($filterType == 'datetime-range'
                || $filterType == 'time-range'
            ) {
                $date_format .= ' ' . get_option('wdtTimeFormat');
                $date_format = str_replace('H', '%H', $date_format);
                $date_format = str_replace('h', '%h', $date_format);
                $date_format = str_replace('i', '%i', $date_format);
                $date_format = str_replace('A', '%p', $date_format);
                $date_format = str_replace('s', '%s', $date_format);
            }

            $literal = ToolsService::prepareSearchLiteral($value, $connection);

            return "STR_TO_DATE($literal, '$date_format')";
        }

        if ($vendor === \Connection::$MSSQL) {
            $type = $filterType === 'time-range' ? 'time' : 'datetime';
            $literal = ToolsService::prepareSearchLiteral($value, $connection);

            switch ($wpDateFormat) {
                case ('d/m/Y'):
                    return "CONVERT($type, $literal, 103)";
                case ('m/d/Y'):
                    return "CONVERT($type, $literal, 101)";
                case ('d.m.Y'):
                    return "CONVERT($type, $literal, 104)";
                case ('m.d.Y'):
                    return "CONVERT($type, REPLACE ($literal, '.' , '/') , 101)";
                case ('d-m-Y'):
                    return "CONVERT($type, $literal, 105)";
                case ('m-d-Y'):
                    return "CONVERT($type, $literal, 110)";
                case ('d.m.y'):
                    return "CONVERT($type, $literal, 4)";
                case ('d.m'):
                    return "LEFT(CONVERT($type, $literal, 4), 5)";
                case ('m.d.y'):
                    return "CONVERT($type, REPLACE ($literal, '.' , '-'), 10)";
                case ('d-m-y'):
                    return "CONVERT($type, $literal, 5)";
                case ('m-d-y'):
                    return "CONVERT($type, $literal, 10)";
                case ('d M Y'):
                    return "CONVERT($type, $literal, 106)";
                case ('M d, Y'):
                    return "CONVERT($type, $literal, 107)";
                case ('j F Y'):
                    return "CONVERT($type, $literal, 106)";
                case ('D, F j, Y'):
                    return "CONVERT($type, $literal, 107)";
                case ('D, M j, Y'):
                    return "CONVERT($type, $literal, 107)";
                case ('M Y'):
                    return "CONVERT($type, $literal, 23)";
                case ('F Y'):
                    return "CONVERT($type, $literal, 23)";
                case ('F j, Y'):
                    return "CONVERT($type, $literal, 107)";
                case ('j. F Y.'):
                    return "CONVERT($type, REPLACE ($literal, '.' , '') , 106)";
                case ('Y'):
                    return "CONVERT($type, $literal, 23)";
            }
        }

        if ($vendor === \Connection::$POSTGRESQL) {
            $type = $filterType === 'time-range' ? '::TIME' : '';

            if ($filterType != 'time-range') {
                $date_format = str_replace('M', 'Mon', $wpDateFormat);
                $date_format = str_replace('m', 'MM', $date_format);
                $date_format = str_replace('Y', 'YYYY', $date_format);
                $date_format = str_replace('y', 'YY', $date_format);
                $date_format = str_replace('d', 'DD', $date_format);
                $date_format = str_replace('F', 'Month', $date_format);
                $date_format = str_replace('j', 'DD', $date_format);
                $date_format = str_replace('D', 'Day', $date_format);
            }

            if ($filterType == 'datetime-range'
                || $filterType == 'time-range'
            ) {
                $date_format .= ' ' . get_option('wdtTimeFormat');
                $date_format = str_replace('H', 'HH24', $date_format);
                $date_format = str_replace('h:', 'HH12:', $date_format);
                $date_format = str_replace('i', 'MI', $date_format);
                $date_format = str_replace('s', 'SS', $date_format);

                if (substr($value, -2) === 'AM') {
                    $date_format = str_replace('A', 'AM', $date_format);
                }

                if (substr($value, -2) === 'PM') {
                    $date_format = str_replace('A', 'PM', $date_format);
                }
            }

            if ($filterType === 'time-range' && strlen(explode(':', $value)[0]) === 1) {
                $value = '0' . $value;
            }

            $date_format = trim($date_format);
            $literal = ToolsService::prepareSearchLiteral($value, $connection);

            return "to_timestamp($literal, '$date_format')$type";
        }
    }
}
