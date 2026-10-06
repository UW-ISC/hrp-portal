<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Permissions;

use WP_User;

/**
 * Checks whether a user has permission keys from any item-scoped access rule.
 *
 * @package WPDataTables\Services\Permissions
 */
final class ItemScopedGrantChecker
{
    /**
     * @param list<array<string,mixed>> $rules
     * @param WP_User                   $user
     * @param list<string>              $permissionKeys
     * @return bool
     */
    public static function userHasAnyGrant(array $rules, WP_User $user, array $permissionKeys): bool
    {
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            if (self::ruleGrantsAny($rule, $user, $permissionKeys)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string,mixed> $rule
     * @param WP_User             $user
     * @param list<string>        $permissionKeys
     * @return bool
     */
    private static function ruleGrantsAny(array $rule, WP_User $user, array $permissionKeys): bool
    {
        if (!self::isItemScopedRule($rule) || !self::ruleMatchesPrincipal($rule, $user)) {
            return false;
        }

        return self::ruleGrantsAnyKey($rule, $permissionKeys);
    }

    /**
     * @param array<string,mixed> $rule
     * @return bool
     */
    private static function isItemScopedRule(array $rule): bool
    {
        $itemIds = $rule['item_ids'] ?? null;

        return is_array($itemIds) && $itemIds !== [];
    }

    /**
     * @param array<string,mixed> $rule
     * @param WP_User             $user
     * @return bool
     */
    private static function ruleMatchesPrincipal(array $rule, WP_User $user): bool
    {
        $ruleType = (string) ($rule['type'] ?? '');
        if ($ruleType === 'user') {
            return AccessRuleMatcher::matchesUser($rule, $user);
        }
        if ($ruleType === 'role') {
            return AccessRuleMatcher::matchesRole($rule, $user);
        }

        return false;
    }

    /**
     * @param array<string,mixed> $rule
     * @param list<string>        $permissionKeys
     * @return bool
     */
    private static function ruleGrantsAnyKey(array $rule, array $permissionKeys): bool
    {
        $permissions = isset($rule['permissions']) && is_array($rule['permissions'])
            ? PermissionCatalog::sanitize($rule['permissions'])
            : [];

        foreach ($permissionKeys as $permissionKey) {
            if (in_array($permissionKey, $permissions, true)) {
                return true;
            }
        }

        return false;
    }
}
