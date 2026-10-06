<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Permissions;

/**
 * Decides which wpDataTables admin submenu slugs the current user may see.
 *
 * WordPress registers each item with {@see WpdtAccess::CAP_ACCESS_PLUGIN} (or
 * `manage_options` for admin-only pages). Fine-grained visibility uses
 * {@see PermissionsService}.
 *
 * @package WPDataTables\Services\Permissions
 */
class AdminMenuPermissionMapper
{
    /** @var PermissionsService */
    private $permissionsService;

    public function __construct(PermissionsService $permissionsService)
    {
        $this->permissionsService = $permissionsService;
    }

    /**
     * Capability string passed to `add_menu_page` / `add_submenu_page`.
     *
     * @param string $menuSlug Admin page slug.
     * @return string
     */
    public function capabilityForSlug(string $menuSlug): string
    {
        if ($this->isManageOptionsOnlySlug($menuSlug)) {
            return 'manage_options';
        }

        return WpdtAccess::CAP_ACCESS_PLUGIN;
    }

    /**
     * Whether the current user should see / open this menu slug.
     *
     * @param string $menuSlug
     * @return bool
     */
    public function currentUserCanAccessSlug(string $menuSlug): bool
    {
        if (WpdtAccess::hasImplicitElevatedAccess()) {
            return true;
        }

        if ($this->isManageOptionsOnlySlug($menuSlug)) {
            return current_user_can('manage_options')
                || WpdtAccess::currentUserCanAccessPermissionsSettings();
        }

        if (!WpdtAccess::currentUserCanAccessPlugin()) {
            return false;
        }

        switch ($menuSlug) {
            case 'wpdatatables-dashboard':
            case 'wpdatatables-getting-started':
            case 'wpdatatables-support':
            case 'wpdatatables-add-ons':
            case 'wpdatatables-upgrade':
            case 'wpdatatables-welcome-page':
                return true;

            case 'wpdatatables-administration':
                return $this->permissionsService->canListTables();

            case 'wpdatatables-constructor':
                return $this->permissionsService->canCreateTables()
                    || $this->permissionsService->canEditTable(null);

            case 'wpdatatables-charts':
                return $this->permissionsService->canListCharts();

            case 'wpdatatables-chart-wizard':
                return $this->permissionsService->canCreateCharts()
                    || $this->permissionsService->canEditChart(null);

            default:
                return false;
        }
    }

    /**
     * @param string $menuSlug
     * @return bool
     */
    private function isManageOptionsOnlySlug(string $menuSlug): bool
    {
        return in_array(
            $menuSlug,
            [
                'wpdatatables-settings',
                'wpdatatables-system-info',
                'wpdatatables_permissions',
            ],
            true
        );
    }
}
