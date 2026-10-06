<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\InstallActions;

use WPDataTables\Services\Permissions\PermissionCatalog;
use WPDataTables\Services\Permissions\WpdtAccess;

/**
 * Registers wpdt_* capabilities on the Administrator role.
 *
 * @package WPDataTables\Services\InstallActions
 */
class ActivationCapabilitiesHook
{
    /**
     * Idempotent: safe to call on every activation and version upgrade.
     *
     * @return void
     */
    public static function ensureAdministratorCaps(): void
    {
        $role = get_role('administrator');
        if ($role === null) {
            return;
        }

        foreach (PermissionCatalog::wpCapabilities() as $cap) {
            $role->add_cap($cap);
        }
        $role->add_cap(WpdtAccess::CAP_ACCESS_PLUGIN);
    }

    /**
     * Backfill caps for sites upgraded without re-running the activation hook.
     *
     * Uses an existing view cap as the sentinel so a partial old install still
     * receives the full catalog once.
     *
     * @return void
     */
    public static function ensureAdministratorCapsIfMissing(): void
    {
        $role = get_role('administrator');
        if ($role === null) {
            return;
        }

        // Any missing catalog cap triggers a full backfill.
        foreach (PermissionCatalog::wpCapabilities() as $cap) {
            if (!$role->has_cap($cap)) {
                self::ensureAdministratorCaps();

                return;
            }
        }

        if (!$role->has_cap(WpdtAccess::CAP_ACCESS_PLUGIN)) {
            self::ensureAdministratorCaps();
        }
    }
}
