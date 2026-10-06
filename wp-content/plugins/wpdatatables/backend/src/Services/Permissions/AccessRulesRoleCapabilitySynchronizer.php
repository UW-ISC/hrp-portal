<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Permissions;

use WP_User;

/**
 * Maps validated access rules to WordPress capabilities (wpdt_*).
 *
 * Only rules with global item scope (`item_ids` null) mirror catalog wpdt_* onto
 * roles or users. Item-scoped grants must not become `user_can()` caps, or later
 * resolvers would short-circuit and allow actions outside the allowed items.
 * Scoped access stays option-based only.
 *
 * {@see WpdtAccess::CAP_ACCESS_PLUGIN} is still granted when any delegation
 * exists for that principal so the parent admin menu can register.
 *
 * Never strips or rewrites Administrator role / elevated users.
 *
 * @package WPDataTables\Services\Permissions
 */
final class AccessRulesRoleCapabilitySynchronizer
{
    /**
     * @param list<array<string,mixed>> $rules
     * @param list<int>                 $previousUserIds User ids that had type "user" rules before the last save.
     * @return void
     */
    public static function syncFromRules(array $rules, array $previousUserIds = []): void
    {
        $roleSync = self::aggregateRoleSyncStateBySlug($rules);
        self::stripWpdtCapsFromNonAdministratorRoles();
        self::applyCapsToRoles($roleSync);

        $userSync = self::aggregateUserSyncStateByUserId($rules);
        self::syncUserCaps($userSync, $previousUserIds);
    }

    /**
     * @param list<array<string,mixed>> $rules
     * @return list<int>
     */
    public static function extractUserIdsFromUserRules(array $rules): array
    {
        $ids = [];
        foreach ($rules as $rule) {
            if (!is_array($rule) || ($rule['type'] ?? '') !== 'user') {
                continue;
            }
            $uid = (int) ($rule['user_id'] ?? 0);
            if ($uid > 0) {
                $ids[$uid] = true;
            }
        }

        return array_map('intval', array_keys($ids));
    }

    /**
     * @param mixed[] $rule
     * @return bool
     */
    private static function ruleHasGlobalItemScope(array $rule): bool
    {
        return !array_key_exists('item_ids', $rule) || $rule['item_ids'] === null;
    }

    /**
     * @param array<string,mixed> $rule
     * @param string              $principalType `role` or `user`
     * @param array               $accumulated
     * @return void
     */
    private static function accumulateDelegationRule(array $rule, string $principalType, array &$accumulated): void
    {
        if (($rule['type'] ?? '') !== $principalType) {
            return;
        }
        $principalKey = self::principalKeyForDelegationRule($rule, $principalType);
        if ($principalKey === null) {
            return;
        }
        $permissions = $rule['permissions'] ?? [];
        if (!is_array($permissions)) {
            return;
        }
        $sanitized = PermissionCatalog::sanitize($permissions);
        if ($sanitized === []) {
            return;
        }
        if (!isset($accumulated[$principalKey])) {
            $accumulated[$principalKey] = [
                'global_caps'      => [],
                'needs_plugin_cap' => false,
            ];
        }
        // Any valid rule (global or scoped) unlocks the parent admin menu.
        $accumulated[$principalKey]['needs_plugin_cap'] = true;
        if (!self::ruleHasGlobalItemScope($rule)) {
            return;
        }
        foreach ($sanitized as $logical) {
            $accumulated[$principalKey]['global_caps'][PermissionCatalog::wpCapability($logical)] = true;
        }
    }

    /**
     * @param array<string,mixed> $rule
     * @param string              $principalType
     * @return string|int|null
     */
    private static function principalKeyForDelegationRule(array $rule, string $principalType)
    {
        if ($principalType === 'role') {
            $slug = strtolower(trim((string) ($rule['role_slug'] ?? '')));
            if ($slug === '' || $slug === 'administrator') {
                return null;
            }

            return $slug;
        }

        $userId = (int) ($rule['user_id'] ?? 0);

        return $userId > 0 ? $userId : null;
    }

    /**
     * @param list<array<string,mixed>> $rules
     * @return array<string, array{global_caps: array<string, true>, needs_plugin_cap: bool}>
     */
    private static function aggregateRoleSyncStateBySlug(array $rules): array
    {
        $byRole = [];
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            self::accumulateDelegationRule($rule, 'role', $byRole);
        }

        return $byRole;
    }

    /**
     * @param list<array<string,mixed>> $rules
     * @return array<int, array{global_caps: array<string, true>, needs_plugin_cap: bool}>
     */
    private static function aggregateUserSyncStateByUserId(array $rules): array
    {
        $byUser = [];
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            self::accumulateDelegationRule($rule, 'user', $byUser);
        }

        $normalized = [];
        foreach ($byUser as $uid => $state) {
            $normalized[(int) $uid] = $state;
        }

        return $normalized;
    }

    /**
     * @param array<int, array{global_caps: array<string, true>, needs_plugin_cap: bool}> $userSyncById
     * @param list<int>                                                                   $previousUserIds
     * @return void
     */
    private static function syncUserCaps(array $userSyncById, array $previousUserIds): void
    {
        $newUserIds      = array_map('intval', array_keys($userSyncById));
        $previousUserIds = array_map('intval', $previousUserIds);
        $affected        = array_values(array_unique(array_merge($previousUserIds, $newUserIds)));

        foreach ($affected as $userId) {
            $userId = (int) $userId;
            if ($userId <= 0) {
                continue;
            }
            clean_user_cache($userId);
            $user = new WP_User($userId);
            if ((int) $user->ID <= 0) {
                continue;
            }
            if (self::userShouldSkipUserCapSynchronization($user)) {
                continue;
            }
            if (!in_array($userId, $newUserIds, true)) {
                self::stripWpdtCapsFromUser($user);
                clean_user_cache($userId);
                continue;
            }
            self::applyCapsToUser(
                $user,
                $userSyncById[$userId]['global_caps'],
                (bool) $userSyncById[$userId]['needs_plugin_cap']
            );
            clean_user_cache($userId);
        }
    }

    /**
     * @param WP_User $user
     * @return bool
     */
    private static function userShouldSkipUserCapSynchronization(WP_User $user): bool
    {
        if (function_exists('is_multisite') && is_multisite()
            && function_exists('is_super_admin') && is_super_admin($user->ID)
        ) {
            return true;
        }

        if (user_can($user, 'manage_options')) {
            return true;
        }

        return in_array('administrator', (array) $user->roles, true);
    }

    /**
     * @param WP_User $user
     * @return void
     */
    private static function stripWpdtCapsFromUser(WP_User $user): void
    {
        foreach (self::capsToStrip() as $cap) {
            $user->remove_cap($cap);
        }
    }

    /**
     * @param WP_User             $user
     * @param array<string, true> $globalCaps wpdt_* from rules with item_ids null only
     * @param bool                $needsPluginCap
     * @return void
     */
    private static function applyCapsToUser(WP_User $user, array $globalCaps, bool $needsPluginCap): void
    {
        foreach (PermissionCatalog::keys() as $key) {
            $cap = PermissionCatalog::wpCapability($key);
            if (isset($globalCaps[$cap])) {
                $user->add_cap($cap);
                continue;
            }
            $user->remove_cap($cap);
        }
        if ($needsPluginCap) {
            $user->add_cap(WpdtAccess::CAP_ACCESS_PLUGIN);

            return;
        }
        $user->remove_cap(WpdtAccess::CAP_ACCESS_PLUGIN);
    }

    /**
     * @return list<string>
     */
    private static function capsToStrip(): array
    {
        $caps   = PermissionCatalog::wpCapabilities();
        $caps[] = WpdtAccess::CAP_ACCESS_PLUGIN;

        return $caps;
    }

    /**
     * @return void
     */
    private static function stripWpdtCapsFromNonAdministratorRoles(): void
    {
        $wpRoles = wp_roles();
        if (!$wpRoles instanceof \WP_Roles) {
            return;
        }

        $stripCaps = self::capsToStrip();

        foreach (array_keys($wpRoles->roles) as $slug) {
            if ($slug === 'administrator') {
                continue;
            }
            $role = get_role($slug);
            if ($role === null) {
                continue;
            }
            foreach ($stripCaps as $cap) {
                $role->remove_cap($cap);
            }
        }
    }

    /**
     * @param array<string, array{global_caps: array<string, true>, needs_plugin_cap: bool}> $roleSyncBySlug
     * @return void
     */
    private static function applyCapsToRoles(array $roleSyncBySlug): void
    {
        foreach ($roleSyncBySlug as $slug => $state) {
            $role = get_role($slug);
            if ($role === null) {
                continue;
            }
            foreach (array_keys($state['global_caps']) as $cap) {
                $role->add_cap($cap);
            }
            if (!empty($state['needs_plugin_cap'])) {
                $role->add_cap(WpdtAccess::CAP_ACCESS_PLUGIN);
            }
        }
    }
}
