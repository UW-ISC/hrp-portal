<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Permissions;

use WP_User;

/**
 * Application-facing permissions façade.
 *
 * Admin/CRUD gates use {@see PermissionResolver}. Frontend shortcode visibility
 * stays on {@see PermissionsEnforcer} via {@see canUserViewTable()} /
 * {@see canUserViewChart()}.
 *
 * @package WPDataTables\Services\Permissions
 */
class PermissionsService
{
    /** @var PermissionsEnforcer */
    private $enforcer;

    /** @var PermissionResolver */
    private $resolver;

    public function __construct(PermissionsEnforcer $enforcer, PermissionResolver $resolver)
    {
        $this->enforcer = $enforcer;
        $this->resolver = $resolver;
    }

    /**
     * Frontend: current user may view this table shortcode (allow-by-default).
     *
     * @param int $tableId
     * @return bool
     */
    public function canUserViewTable($tableId): bool
    {
        return $this->enforcer->canUserViewTable($tableId);
    }

    /**
     * Frontend: current user may view this chart shortcode (allow-by-default).
     *
     * @param int $chartId
     * @return bool
     */
    public function canUserViewChart($chartId): bool
    {
        return $this->enforcer->canUserViewChart($chartId);
    }

    /**
     * @return bool
     */
    public function canListTables(): bool
    {
        return $this->currentUserCanAnyIncludingScoped([
            'list_tables',
            'create_tables',
            'edit_tables',
            'delete_tables',
        ]);
    }

    /**
     * @return bool
     */
    public function canCreateTables(): bool
    {
        return $this->currentUserCan('create_tables', null);
    }

    /**
     * @param int|null $tableId
     * @return bool
     */
    public function canEditTable(?int $tableId = null): bool
    {
        return $this->currentUserCan('edit_tables', $tableId);
    }

    /**
     * @param int|null $tableId
     * @return bool
     */
    public function canDeleteTable(?int $tableId = null): bool
    {
        return $this->currentUserCan('delete_tables', $tableId);
    }

    /**
     * Admin/resolver view_tables grant (not the frontend allow-by-default path).
     *
     * @param int|null $tableId
     * @return bool
     */
    public function canViewTable(?int $tableId = null): bool
    {
        return $this->currentUserCan('view_tables', $tableId);
    }

    /**
     * @return bool
     */
    public function canListCharts(): bool
    {
        return $this->currentUserCanAnyIncludingScoped([
            'list_charts',
            'create_charts',
            'edit_charts',
            'delete_charts',
        ]);
    }

    /**
     * @return bool
     */
    public function canCreateCharts(): bool
    {
        return $this->currentUserCan('create_charts', null);
    }

    /**
     * @param int|null $chartId
     * @return bool
     */
    public function canEditChart(?int $chartId = null): bool
    {
        return $this->currentUserCan('edit_charts', $chartId);
    }

    /**
     * @param int|null $chartId
     * @return bool
     */
    public function canDeleteChart(?int $chartId = null): bool
    {
        return $this->currentUserCan('delete_charts', $chartId);
    }

    /**
     * @param int|null $chartId
     * @return bool
     */
    public function canViewChart(?int $chartId = null): bool
    {
        return $this->currentUserCan('view_charts', $chartId);
    }

    /**
     * @param string   $permissionKey
     * @param WP_User  $user
     * @param int|null $itemId
     * @return bool
     */
    public function userCan(string $permissionKey, WP_User $user, ?int $itemId = null): bool
    {
        return $this->resolver->userCan($permissionKey, $user, $itemId);
    }

    /**
     * Whether the user holds any of the given permission keys (global or item-scoped).
     *
     * @param WP_User      $user
     * @param list<string> $permissionKeys
     * @return bool
     */
    public function userCanAnyOf(WP_User $user, array $permissionKeys): bool
    {
        return $this->resolver->userCanAnyOf($user, $permissionKeys);
    }

    /**
     * Browse allow-list for tables. Null = all; int[] = scoped; [] = none.
     *
     * @return list<int>|null
     */
    public function getAllowedTableIds(): ?array
    {
        return $this->getAllowedItemIdsForCurrentUser(
            PermissionCatalog::RESOURCE_TABLES,
            ['list_tables', 'create_tables', 'edit_tables', 'delete_tables']
        );
    }

    /**
     * Browse allow-list for charts. Null = all; int[] = scoped; [] = none.
     *
     * @return list<int>|null
     */
    public function getAllowedChartIds(): ?array
    {
        return $this->getAllowedItemIdsForCurrentUser(
            PermissionCatalog::RESOURCE_CHARTS,
            ['list_charts', 'create_charts', 'edit_charts', 'delete_charts']
        );
    }

    /**
     * @param string       $resource
     * @param list<string> $permissionKeys
     * @return list<int>|null
     */
    private function getAllowedItemIdsForCurrentUser(string $resource, array $permissionKeys): ?array
    {
        if (WpdtAccess::hasImplicitElevatedAccess()) {
            return null;
        }

        $user = $this->getCurrentUser();
        if ($user === null) {
            return [];
        }

        return $this->resolver->getAllowedItemIds($resource, $permissionKeys, $user);
    }

    /**
     * @param string   $permissionKey
     * @param int|null $itemId
     * @return bool
     */
    private function currentUserCan(string $permissionKey, ?int $itemId): bool
    {
        if (WpdtAccess::hasImplicitElevatedAccess()) {
            return true;
        }

        $user = $this->getCurrentUser();
        if ($user === null) {
            return false;
        }

        return $this->resolver->userCanIncludingItemScoped($permissionKey, $user, $itemId);
    }

    /**
     * @param list<string> $permissionKeys
     * @return bool
     */
    private function currentUserCanAnyIncludingScoped(array $permissionKeys): bool
    {
        if (WpdtAccess::hasImplicitElevatedAccess()) {
            return true;
        }

        $user = $this->getCurrentUser();
        if ($user === null) {
            return false;
        }

        return $this->resolver->userCanAnyOf($user, $permissionKeys);
    }

    /**
     * @return WP_User|null
     */
    private function getCurrentUser(): ?WP_User
    {
        $user = wp_get_current_user();
        if (!$user instanceof WP_User || (int) $user->ID <= 0) {
            return null;
        }

        return $user;
    }
}
