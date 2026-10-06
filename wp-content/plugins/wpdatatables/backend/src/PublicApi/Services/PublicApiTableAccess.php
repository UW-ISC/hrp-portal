<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\PublicApi\Services;

use WPDataTables\Common\Exceptions\ForbiddenException;

/**
 * Table access policy for the public REST API.
 *
 * v1 API keys grant site-wide table access by default. Per-user WordPress
 * table permissions ({@see \WPDataTables\Services\Permissions\PermissionsEnforcer})
 * do not apply to API-key authentication. Use the
 * `wpdatatables/public_api/can_access_table` filter to restrict individual tables.
 *
 * @package WPDataTables\PublicApi\Services
 */
class PublicApiTableAccess
{
    /**
     * @param int $tableId
     * @return void
     * @throws ForbiddenException
     */
    public static function assertCanAccess(int $tableId)
    {
        if (!self::canAccess($tableId)) {
            throw new ForbiddenException('Table access is not permitted via the public API.');
        }
    }

    /**
     * @param int $tableId
     * @return bool
     */
    public static function canAccess(int $tableId)
    {
        /**
         * Filter whether a table is accessible via the public REST API.
         *
         * @since 7.x
         * @param bool $allowed   Default true (admin-issued API keys grant site-wide access).
         * @param int  $tableId   wpDataTables table id.
         */
        return (bool) apply_filters('wpdatatables/public_api/can_access_table', true, $tableId);
    }

    /**
     * @param array<int, mixed> $tables
     * @return array<int, mixed>
     */
    public static function filterTableList(array $tables)
    {
        return array_values(array_filter($tables, static function ($table) {
            $tableId = 0;

            if (is_object($table) && isset($table->id)) {
                $tableId = (int) $table->id;
            } elseif (is_array($table) && isset($table['id'])) {
                $tableId = (int) $table['id'];
            }

            return $tableId > 0 && self::canAccess($tableId);
        }));
    }
}
