<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Permissions;

use WPDataTables\Plugin\Plugin;

/**
 * SQL helpers for scoping browse list queries to allowed item ids.
 *
 * @package WPDataTables\Services\Permissions
 */
final class BrowseListScope
{
    /**
     * @return list<int>|null Null = all tables.
     */
    public static function allowedTableIds(): ?array
    {
        return self::permissions()->getAllowedTableIds();
    }

    /**
     * @return list<int>|null Null = all charts.
     */
    public static function allowedChartIds(): ?array
    {
        return self::permissions()->getAllowedChartIds();
    }

    /**
     * Append an id allow-list constraint before ORDER BY / LIMIT.
     *
     * @param string        $query
     * @param string        $idExpression Column expression, e.g. `t.id` or `id`.
     * @param list<int>|null $allowedIds
     * @return string
     */
    public static function constrainQueryByIds(string $query, string $idExpression, ?array $allowedIds): string
    {
        if ($allowedIds === null) {
            return $query;
        }

        if ($allowedIds === []) {
            $clause = '1=0';
        } else {
            $ids = implode(',', array_map('intval', $allowedIds));
            $clause = $idExpression . ' IN (' . $ids . ')';
        }

        return self::insertWhereClause($query, $clause);
    }

    /**
     * @param string $query
     * @param string $clause
     * @return string
     */
    private static function insertWhereClause(string $query, string $clause): string
    {
        $splitAt = null;
        foreach ([' ORDER BY ', ' LIMIT '] as $keyword) {
            $pos = stripos($query, $keyword);
            if ($pos !== false && ($splitAt === null || $pos < $splitAt)) {
                $splitAt = $pos;
            }
        }

        $head = $splitAt === null ? $query : substr($query, 0, $splitAt);
        $tail = $splitAt === null ? '' : substr($query, $splitAt);

        if (preg_match('/\bWHERE\b/i', $head)) {
            return $head . ' AND (' . $clause . ')' . $tail;
        }

        return $head . ' WHERE ' . $clause . $tail;
    }

    /**
     * @return PermissionsService
     */
    private static function permissions(): PermissionsService
    {
        return Plugin::container()->get(PermissionsService::class);
    }
}
