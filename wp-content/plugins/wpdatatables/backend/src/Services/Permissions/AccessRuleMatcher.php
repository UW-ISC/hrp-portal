<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Permissions;

use WP_User;

/**
 * Item-scope and principal matching helpers for stored access rules.
 *
 * @package WPDataTables\Services\Permissions
 */
final class AccessRuleMatcher
{
    /**
     * @param mixed[]  $rule
     * @param int|null $itemId
     * @return bool
     */
    public static function appliesToItem(array $rule, ?int $itemId): bool
    {
        $itemIds = $rule['item_ids'] ?? null;
        if ($itemIds === null) {
            return true;
        }
        if (!is_array($itemIds)) {
            return false;
        }
        if ($itemId === null) {
            return false;
        }

        return in_array((int) $itemId, array_map('intval', $itemIds), true);
    }

    /**
     * @param mixed[] $rule
     * @param WP_User $user
     * @return bool
     */
    public static function matchesUser(array $rule, WP_User $user): bool
    {
        $id = isset($rule['user_id']) ? (int) $rule['user_id'] : 0;

        return $id > 0 && $id === (int) $user->ID;
    }

    /**
     * @param mixed[] $rule
     * @param WP_User $user
     * @return bool
     */
    public static function matchesRole(array $rule, WP_User $user): bool
    {
        $slug = isset($rule['role_slug']) ? strtolower(trim((string) $rule['role_slug'])) : '';
        if ($slug === '') {
            return false;
        }

        return in_array($slug, self::getUserRoleSlugs($user), true);
    }

    /**
     * Stable merge order: older updated_at / created_at first (user rules applied after role).
     *
     * @param list<array<string,mixed>> $rules
     * @return list<array<string,mixed>>
     */
    public static function sortForMerge(array $rules): array
    {
        usort(
            $rules,
            static function (array $leftRule, array $rightRule): int {
                $leftUpdatedAt  = isset($leftRule['updated_at']) ? (int) $leftRule['updated_at'] : 0;
                $rightUpdatedAt = isset($rightRule['updated_at']) ? (int) $rightRule['updated_at'] : 0;
                if ($leftUpdatedAt !== $rightUpdatedAt) {
                    return $leftUpdatedAt <=> $rightUpdatedAt;
                }
                $leftCreatedAt  = isset($leftRule['created_at']) ? (int) $leftRule['created_at'] : 0;
                $rightCreatedAt = isset($rightRule['created_at']) ? (int) $rightRule['created_at'] : 0;

                return $leftCreatedAt <=> $rightCreatedAt;
            }
        );

        return $rules;
    }

    /**
     * @param WP_User $user
     * @return list<string>
     */
    private static function getUserRoleSlugs(WP_User $user): array
    {
        $roleSet = [];

        foreach ((array) $user->roles as $role) {
            if (!is_string($role) || $role === '') {
                continue;
            }
            $roleSet[strtolower($role)] = true;
        }

        foreach ((array) $user->caps as $cap => $enabled) {
            if (!$enabled || !is_string($cap) || $cap === '') {
                continue;
            }
            if (!wp_roles()->is_role($cap)) {
                continue;
            }
            $roleSet[strtolower($cap)] = true;
        }

        return array_keys($roleSet);
    }
}
