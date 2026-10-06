<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Permissions;

use WP_User;
use WPDataTables\Repository\Permissions\AccessRulesRepository;

/**
 * Resolves wpDataTables capability keys from stored access rules.
 *
 * Union of grants from all rules matching the user (role then user) and item
 * scope. Global WordPress caps (mirrored from rules with item_ids null, or
 * granted via Members/URE) short-circuit to allow.
 *
 * @package WPDataTables\Services\Permissions
 */
class PermissionResolver
{
    /** @var AccessRulesRepository */
    private $rulesRepository;

    /** @var list<array<string,mixed>>|null */
    private $cachedRules = null;

    /** @var array<string, list<string>> */
    private $effectiveGrantsCache = [];

    public function __construct(AccessRulesRepository $rulesRepository)
    {
        $this->rulesRepository = $rulesRepository;
    }

    /**
     * @param string       $permissionKey Catalog logical key.
     * @param WP_User      $user
     * @param int|null     $itemId        Table or chart id; null = global-only match.
     * @return bool
     */
    public function userCan(string $permissionKey, $user, ?int $itemId = null): bool
    {
        if (!in_array($permissionKey, PermissionCatalog::keys(), true)) {
            return false;
        }

        if (!$user instanceof WP_User || (int) $user->ID <= 0) {
            return false;
        }

        if (WpdtAccess::userHasImplicitElevatedAccess($user)) {
            return true;
        }

        if (user_can($user, PermissionCatalog::wpCapability($permissionKey))) {
            return true;
        }

        $grants = $this->effectiveGrants($user, $itemId);

        return in_array($permissionKey, $grants, true);
    }

    /**
     * True when the user has at least one key via a global rule/cap or any item-scoped rule.
     *
     * Used for list routes where no single item id applies.
     *
     * @param WP_User      $user
     * @param list<string> $permissionKeys
     * @return bool
     */
    public function userCanAnyOf($user, array $permissionKeys): bool
    {
        if (!$user instanceof WP_User || (int) $user->ID <= 0) {
            return false;
        }

        if (WpdtAccess::userHasImplicitElevatedAccess($user)) {
            return true;
        }

        foreach ($permissionKeys as $permissionKey) {
            if (!in_array($permissionKey, PermissionCatalog::keys(), true)) {
                continue;
            }
            if (user_can($user, PermissionCatalog::wpCapability($permissionKey))) {
                return true;
            }
            if ($this->userCan($permissionKey, $user, null)) {
                return true;
            }
        }

        return ItemScopedGrantChecker::userHasAnyGrant($this->getRules(), $user, $permissionKeys);
    }

    /**
     * Like userCan(), but when $itemId is null also allows item-scoped rules (list/single-action gates).
     *
     * @param string   $permissionKey
     * @param WP_User  $user
     * @param int|null $itemId
     * @return bool
     */
    public function userCanIncludingItemScoped(string $permissionKey, $user, ?int $itemId = null): bool
    {
        if ($this->userCan($permissionKey, $user, $itemId)) {
            return true;
        }

        if ($itemId !== null) {
            return false;
        }

        return ItemScopedGrantChecker::userHasAnyGrant($this->getRules(), $user, [$permissionKey]);
    }

    /**
     * Whether the user has any matching grant for the key (global or scoped), regardless of item id.
     *
     * Used by the frontend enforcer to distinguish "restricted by rules" from "no rules".
     *
     * @param string  $permissionKey
     * @param WP_User $user
     * @return bool
     */
    public function userHasAnyGrantForKey(string $permissionKey, $user): bool
    {
        if (!$user instanceof WP_User || (int) $user->ID <= 0) {
            return false;
        }

        if (!in_array($permissionKey, PermissionCatalog::keys(), true)) {
            return false;
        }

        if (WpdtAccess::userHasImplicitElevatedAccess($user)) {
            return true;
        }

        if (user_can($user, PermissionCatalog::wpCapability($permissionKey))) {
            return true;
        }

        if ($this->userCan($permissionKey, $user, null)) {
            return true;
        }

        return ItemScopedGrantChecker::userHasAnyGrant($this->getRules(), $user, [$permissionKey]);
    }

    /**
     * @param WP_User  $user
     * @param int|null $itemId
     * @return list<string>
     */
    public function effectiveGrants($user, ?int $itemId = null): array
    {
        if (!$user instanceof WP_User || (int) $user->ID <= 0) {
            return [];
        }

        $cacheKey = (int) $user->ID . ':' . ($itemId === null ? 'null' : (string) $itemId);
        if (isset($this->effectiveGrantsCache[$cacheKey])) {
            return $this->effectiveGrantsCache[$cacheKey];
        }

        $rules = $this->getRules();
        if ($rules === []) {
            $this->effectiveGrantsCache[$cacheKey] = [];

            return [];
        }

        $orderedRules = $this->getRulesOrderedForMerge($rules, $user, $itemId);
        if ($orderedRules === []) {
            $this->effectiveGrantsCache[$cacheKey] = [];

            return [];
        }

        $grants = $this->extractPermissionSet($orderedRules);
        $this->effectiveGrantsCache[$cacheKey] = $grants;

        return $grants;
    }

    /**
     * Item ids the user may act on for the given permission keys on a resource.
     *
     * @param string       $resource       {@see PermissionCatalog::RESOURCE_TABLES} or RESOURCE_CHARTS.
     * @param list<string> $permissionKeys Keys that unlock browse/list visibility (typically list + CRUD).
     * @param WP_User      $user
     * @return list<int>|null Null = all items; int[] = scoped allow-list (may be empty).
     */
    public function getAllowedItemIds(string $resource, array $permissionKeys, $user): ?array
    {
        if (!PermissionCatalog::isValidResource($resource)) {
            return [];
        }

        if (!$user instanceof WP_User || (int) $user->ID <= 0) {
            return [];
        }

        if (WpdtAccess::userHasImplicitElevatedAccess($user)) {
            return null;
        }

        $keys = [];
        foreach ($permissionKeys as $permissionKey) {
            if (!in_array($permissionKey, PermissionCatalog::keysForResource($resource), true)) {
                continue;
            }
            $keys[] = $permissionKey;
        }
        if ($keys === []) {
            return [];
        }

        foreach ($keys as $permissionKey) {
            if (user_can($user, PermissionCatalog::wpCapability($permissionKey))) {
                return null;
            }
        }

        $allowed = [];
        $hasAnyMatchingRule = false;
        foreach ($this->getRules() as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            if (($rule['resource'] ?? '') !== $resource) {
                continue;
            }
            $ruleType = (string) ($rule['type'] ?? '');
            $matches  = ($ruleType === 'user' && AccessRuleMatcher::matchesUser($rule, $user))
                || ($ruleType === 'role' && AccessRuleMatcher::matchesRole($rule, $user));
            if (!$matches) {
                continue;
            }
            $permissions = isset($rule['permissions']) && is_array($rule['permissions'])
                ? PermissionCatalog::sanitize($rule['permissions'])
                : [];
            $grantsKey = false;
            foreach ($keys as $permissionKey) {
                if (in_array($permissionKey, $permissions, true)) {
                    $grantsKey = true;
                    break;
                }
            }
            if (!$grantsKey) {
                continue;
            }
            $hasAnyMatchingRule = true;
            $itemIds = $rule['item_ids'] ?? null;
            if ($itemIds === null) {
                return null;
            }
            if (!is_array($itemIds)) {
                continue;
            }
            foreach ($itemIds as $itemId) {
                $itemId = (int) $itemId;
                if ($itemId > 0) {
                    $allowed[$itemId] = true;
                }
            }
        }

        if (!$hasAnyMatchingRule) {
            return [];
        }

        return array_map('intval', array_keys($allowed));
    }

    /**
     * @return void
     */
    public function invalidateCache(): void
    {
        $this->cachedRules          = null;
        $this->effectiveGrantsCache = [];
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function getRules(): array
    {
        if ($this->cachedRules !== null) {
            return $this->cachedRules;
        }

        $payload           = $this->rulesRepository->getPayload();
        $this->cachedRules = $payload['rules'];

        return $this->cachedRules;
    }

    /**
     * @param list<array<string,mixed>> $rules
     * @param WP_User                   $user
     * @param int|null                  $itemId
     * @return list<array<string,mixed>>
     */
    private function getRulesOrderedForMerge(array $rules, $user, ?int $itemId): array
    {
        $roleMatches = [];
        $userMatches = [];
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            if (!AccessRuleMatcher::appliesToItem($rule, $itemId)) {
                continue;
            }
            $ruleType = (string) ($rule['type'] ?? '');
            if ($ruleType === 'user' && AccessRuleMatcher::matchesUser($rule, $user)) {
                $userMatches[] = $rule;
                continue;
            }
            if ($ruleType === 'role' && AccessRuleMatcher::matchesRole($rule, $user)) {
                $roleMatches[] = $rule;
            }
        }

        return array_merge(
            AccessRuleMatcher::sortForMerge($roleMatches),
            AccessRuleMatcher::sortForMerge($userMatches)
        );
    }

    /**
     * @param list<array<string,mixed>> $rules
     * @return list<string>
     */
    private function extractPermissionSet(array $rules): array
    {
        $permissionSet = [];
        foreach ($rules as $rule) {
            $permissions = $rule['permissions'] ?? [];
            if (!is_array($permissions)) {
                continue;
            }
            foreach (PermissionCatalog::sanitize($permissions) as $permissionKey) {
                $permissionSet[$permissionKey] = true;
            }
        }

        return array_keys($permissionSet);
    }
}
