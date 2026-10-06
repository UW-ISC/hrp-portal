<?php
// Permissions enforcement helper class
if (!defined('ABSPATH')) die('Access denied.');

use WPDataTables\Plugin\Plugin;
use WPDataTables\Services\Permissions\PermissionsEnforcer;

/**
 * WDTPermissionsEnforcer
 *
 * Backward-compatibility facade. The implementation lives in
 * {@see WPDataTables\Services\Permissions\PermissionsEnforcer}. Kept as a thin
 * facade so existing call sites referencing `WDTPermissionsEnforcer::*` keep working.
 */
class WDTPermissionsEnforcer
{
    /**
     * Resolve the PermissionsEnforcer from the DI container.
     *
     * @return PermissionsEnforcer
     */
    private static function enforcer()
    {
        return Plugin::container()->get(PermissionsEnforcer::class);
    }

    /**
     * Check if current user can view a specific table.
     *
     * @param int $tableId The table ID to check
     * @return bool True if user can view, false otherwise
     */
    public static function canUserViewTable($tableId)
    {
        return self::enforcer()->canUserViewTable($tableId);
    }

    /**
     * Check if current user can view a specific chart.
     *
     * @param int $chartId The chart ID to check
     * @return bool True if user can view, false otherwise
     */
    public static function canUserViewChart($chartId)
    {
        return self::enforcer()->canUserViewChart($chartId);
    }
}
