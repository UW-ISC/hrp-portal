<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Permissions;

use WP_User;

/**
 * Frontend access-control enforcer.
 *
 * Owns the per-user table/chart visibility checks via {@see PermissionResolver}.
 * Logged-out visitors and users with no view_* rules remain allow-by-default
 * (preserves shortcode behaviour). Users with any view_* grant are restricted
 * to items allowed by those rules.
 *
 * Legacy user meta (`wpdt_table_access` / `wpdt_chart_access`) is no longer
 * consulted; migration writes equivalent access rules and cleans that meta.
 *
 * The legacy {@see WDTPermissionsEnforcer} delegates here.
 *
 * @package WPDataTables\Services\Permissions
 */
class PermissionsEnforcer
{
    /** @var PermissionResolver */
    private $resolver;

    public function __construct(PermissionResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Check if the current user can view a specific table.
     *
     * @param int $tableId The table ID to check.
     * @return bool True if the user can view, false otherwise.
     */
    public function canUserViewTable($tableId): bool
    {
        if (!is_user_logged_in()) {
            return true;
        }

        $user = wp_get_current_user();
        if (!$user instanceof WP_User || (int) $user->ID <= 0) {
            return true;
        }

        if (WpdtAccess::userHasImplicitElevatedAccess($user)) {
            return true;
        }

        $tableId = (int) $tableId;

        if ($this->resolver->userCan('view_tables', $user, $tableId)) {
            return true;
        }

        // Restricted by rules (scoped grant for other items, or denied for this id).
        if ($this->resolver->userHasAnyGrantForKey('view_tables', $user)) {
            return false;
        }

        // No view_tables rules → allow-by-default.
        return true;
    }

    /**
     * Check if the current user can view a specific chart.
     *
     * @param int $chartId The chart ID to check.
     * @return bool True if the user can view, false otherwise.
     */
    public function canUserViewChart($chartId): bool
    {
        if (!is_user_logged_in()) {
            return true;
        }

        $user = wp_get_current_user();
        if (!$user instanceof WP_User || (int) $user->ID <= 0) {
            return true;
        }

        if (WpdtAccess::userHasImplicitElevatedAccess($user)) {
            return true;
        }

        $chartId = (int) $chartId;

        if ($this->resolver->userCan('view_charts', $user, $chartId)) {
            return true;
        }

        if ($this->resolver->userHasAnyGrantForKey('view_charts', $user)) {
            return false;
        }

        // No view_charts rules → allow-by-default.
        return true;
    }
}
