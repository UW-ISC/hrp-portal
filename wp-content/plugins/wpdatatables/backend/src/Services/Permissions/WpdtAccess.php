<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Permissions;

use WP_User;

/**
 * Site-wide access helpers for wpDataTables admin (menus, pages, AJAX).
 *
 * Implicit elevated access (no wpdt_* caps required):
 * - Multisite: super admins only.
 * - Single site: users who can manage_options (typically Administrators).
 *
 * @package WPDataTables\Services\Permissions
 */
final class WpdtAccess
{
    /**
     * Parent-menu / coarse WP capability for delegated managers.
     *
     * Granted by {@see AccessRulesRoleCapabilitySynchronizer} whenever a role
     * or user has any access-rule delegation (global or item-scoped), so
     * `add_menu_page` can register without mirroring blanket CRUD caps.
     */
    public const CAP_ACCESS_PLUGIN = 'wpdt_access_plugin';

    /**
     * @return bool
     */
    public static function hasImplicitElevatedAccess(): bool
    {
        if (!function_exists('wp_get_current_user')) {
            return false;
        }

        if (function_exists('is_multisite') && is_multisite()) {
            return function_exists('is_super_admin') && is_super_admin();
        }

        return current_user_can('manage_options');
    }

    /**
     * Whether the current user may see the wpDataTables admin parent menu.
     *
     * @return bool
     */
    public static function currentUserCanAccessPlugin(): bool
    {
        if (self::hasImplicitElevatedAccess()) {
            return true;
        }

        return function_exists('current_user_can')
            && current_user_can(self::CAP_ACCESS_PLUGIN);
    }

    /**
     * @param WP_User $user
     * @return bool
     */
    public static function userHasImplicitElevatedAccess(WP_User $user): bool
    {
        if ((int) $user->ID <= 0) {
            return false;
        }

        if (function_exists('is_multisite') && is_multisite()) {
            return function_exists('is_super_admin') && is_super_admin((int) $user->ID);
        }

        return user_can($user, 'manage_options');
    }

    /**
     * Users who already have site-wide admin access (exclude from permission pickers).
     *
     * @param WP_User $user
     * @return bool
     */
    public static function userShouldBeExcludedFromPermissionsPicker(WP_User $user): bool
    {
        if (self::userHasImplicitElevatedAccess($user)) {
            return true;
        }

        return self::userHasAdministratorRole($user);
    }

    /**
     * Whether the current user may access the Permissions admin page and its AJAX.
     *
     * Admin-only: elevated access or the administrator role. Delegated managers
     * with only wpdt_* grants are excluded.
     *
     * @return bool
     */
    public static function currentUserCanAccessPermissionsSettings(): bool
    {
        if (self::hasImplicitElevatedAccess()) {
            return true;
        }

        if (!function_exists('wp_get_current_user')) {
            return false;
        }

        return self::userHasAdministratorRole(wp_get_current_user());
    }

    /**
     * @param WP_User $user
     * @return bool
     */
    public static function userHasAdministratorRole(WP_User $user): bool
    {
        if ((int) $user->ID <= 0) {
            return false;
        }

        return in_array('administrator', (array) $user->roles, true);
    }
}
