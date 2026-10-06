<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Permissions;

use WP_User;
use WPDataTables\Repository\Permissions\AccessRulesRepository;

/**
 * One-time migration of legacy user-meta view managers into wpdt_access_rules.
 *
 * For each non-admin user with a user-level wpdt_view_tables / wpdt_view_charts
 * capability, creates a type=user rule and maps meta scope to item_ids.
 * Then runs the capability synchronizer so scoped-only grants no longer mirror
 * blanket WP caps, and deletes leftover {@see META_TABLE_ACCESS} /
 * {@see META_CHART_ACCESS} user meta (Phase 5 cleanup).
 *
 * @package WPDataTables\Services\Permissions
 */
class AccessRulesMigrator
{
    public const OPTION_MIGRATED = 'wpdt_access_rules_migrated';
    public const OPTION_LEGACY_META_CLEANED = 'wpdt_legacy_access_meta_cleaned';

    public const META_TABLE_ACCESS = 'wpdt_table_access';
    public const META_CHART_ACCESS = 'wpdt_chart_access';

    /** @var AccessRulesRepository */
    private $rulesRepository;

    public function __construct(AccessRulesRepository $rulesRepository)
    {
        $this->rulesRepository = $rulesRepository;
    }

    /**
     * Static entry for activation / init without requiring the DI container.
     *
     * @return void
     */
    public static function runIfNeeded(): void
    {
        $migrator = new self(new AccessRulesRepository());
        $migrator->migrateIfNeeded();
        $migrator->cleanupLegacyMetaIfNeeded();
    }

    /**
     * @return void
     */
    public function migrateIfNeeded(): void
    {
        if ((int) get_option(self::OPTION_MIGRATED, 0) === 1) {
            return;
        }

        $existing   = $this->rulesRepository->getPayload()['rules'];
        $previousIds = AccessRulesRoleCapabilitySynchronizer::extractUserIdsFromUserRules($existing);

        $migrated = $this->buildRulesFromLegacyUsers();
        $merged   = $this->mergeRules($existing, $migrated);

        $this->rulesRepository->saveRules($merged);
        AccessRulesRoleCapabilitySynchronizer::syncFromRules($merged, $previousIds);

        update_option(self::OPTION_MIGRATED, 1, false);
        $this->deleteLegacyAccessMeta();
        update_option(self::OPTION_LEGACY_META_CLEANED, 1, false);
    }

    /**
     * Drop leftover legacy user meta after the rules migration has completed.
     *
     * Safe to call repeatedly; no-ops once {@see OPTION_LEGACY_META_CLEANED} is set.
     *
     * @return void
     */
    public function cleanupLegacyMetaIfNeeded(): void
    {
        if ((int) get_option(self::OPTION_LEGACY_META_CLEANED, 0) === 1) {
            return;
        }

        if ((int) get_option(self::OPTION_MIGRATED, 0) !== 1) {
            return;
        }

        $this->deleteLegacyAccessMeta();
        update_option(self::OPTION_LEGACY_META_CLEANED, 1, false);
    }

    /**
     * @return void
     */
    private function deleteLegacyAccessMeta(): void
    {
        delete_metadata('user', 0, self::META_TABLE_ACCESS, '', true);
        delete_metadata('user', 0, self::META_CHART_ACCESS, '', true);
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function buildRulesFromLegacyUsers(): array
    {
        $rules = [];
        $users = get_users([
            'fields' => 'all',
            'number' => -1,
        ]);

        if (!is_array($users)) {
            return [];
        }

        foreach ($users as $user) {
            if (!$user instanceof WP_User || (int) $user->ID <= 0) {
                continue;
            }
            if (WpdtAccess::userShouldBeExcludedFromPermissionsPicker($user)) {
                continue;
            }

            $tableRule = $this->ruleFromLegacyViewCap(
                $user,
                PermissionCatalog::RESOURCE_TABLES,
                PermissionsAdminService::CAPABILITY_VIEW_TABLES,
                'view_tables',
                self::META_TABLE_ACCESS,
                'all_tables',
                'table_ids'
            );
            if ($tableRule !== null) {
                $rules[] = $tableRule;
            }

            $chartRule = $this->ruleFromLegacyViewCap(
                $user,
                PermissionCatalog::RESOURCE_CHARTS,
                PermissionsAdminService::CAPABILITY_VIEW_CHARTS,
                'view_charts',
                self::META_CHART_ACCESS,
                'all_charts',
                'chart_ids'
            );
            if ($chartRule !== null) {
                $rules[] = $chartRule;
            }
        }

        return $rules;
    }

    /**
     * @param WP_User $user
     * @param string  $resource
     * @param string  $capability
     * @param string  $permissionKey
     * @param string  $metaKey
     * @param string  $allKey
     * @param string  $itemIdKey
     * @return array<string,mixed>|null
     */
    private function ruleFromLegacyViewCap(
        WP_User $user,
        string $resource,
        string $capability,
        string $permissionKey,
        string $metaKey,
        string $allKey,
        string $itemIdKey
    ): ?array {
        if (!$this->userHasUserLevelCap($user, $capability)) {
            return null;
        }

        $access  = get_user_meta((int) $user->ID, $metaKey, true);
        $itemIds = $this->itemIdsFromLegacyAccess($access, $allKey, $itemIdKey);
        $now     = time();

        return [
            'id'          => $this->generateRuleId(),
            'type'        => 'user',
            'user_id'     => (int) $user->ID,
            'resource'    => $resource,
            'permissions' => [$permissionKey],
            'item_ids'    => $itemIds,
            'created_at'  => $now,
            'updated_at'  => $now,
        ];
    }

    /**
     * User-level grant only (not role-inherited via Members / URE).
     *
     * @param WP_User $user
     * @param string  $capability
     * @return bool
     */
    private function userHasUserLevelCap(WP_User $user, string $capability): bool
    {
        $caps = (array) $user->caps;

        return !empty($caps[$capability]);
    }

    /**
     * @param mixed  $access
     * @param string $allKey
     * @param string $itemIdKey
     * @return list<int>|null null = all items
     */
    private function itemIdsFromLegacyAccess($access, string $allKey, string $itemIdKey): ?array
    {
        if (!is_array($access) || $access === []) {
            return null;
        }

        if (!empty($access[$allKey])) {
            return null;
        }

        if (!isset($access[$itemIdKey]) || !is_array($access[$itemIdKey])) {
            return null;
        }

        $ids = [];
        foreach ($access[$itemIdKey] as $id) {
            $normalized = (int) $id;
            if ($normalized > 0) {
                $ids[$normalized] = true;
            }
        }

        $list = array_map('intval', array_keys($ids));

        // Empty specific list treated as all (matches legacy empty-meta allow).
        return $list === [] ? null : $list;
    }

    /**
     * Append migrated rules when no equivalent user+resource rule already exists.
     *
     * @param list<array<string,mixed>> $existing
     * @param list<array<string,mixed>> $migrated
     * @return list<array<string,mixed>>
     */
    private function mergeRules(array $existing, array $migrated): array
    {
        $covered = [];
        foreach ($existing as $rule) {
            if (!is_array($rule) || ($rule['type'] ?? '') !== 'user') {
                continue;
            }
            $uid = (int) ($rule['user_id'] ?? 0);
            $resource = (string) ($rule['resource'] ?? '');
            if ($uid > 0 && $resource !== '') {
                $covered[$uid . ':' . $resource] = true;
            }
        }

        $merged = array_values(array_filter($existing, 'is_array'));
        foreach ($migrated as $rule) {
            $uid = (int) ($rule['user_id'] ?? 0);
            $resource = (string) ($rule['resource'] ?? '');
            $key = $uid . ':' . $resource;
            if (isset($covered[$key])) {
                continue;
            }
            $merged[] = $rule;
            $covered[$key] = true;
        }

        return $merged;
    }

    /**
     * @return string
     */
    private function generateRuleId(): string
    {
        if (function_exists('wp_generate_uuid4')) {
            return wp_generate_uuid4();
        }

        return uniqid('wpdt-rule-', true);
    }
}
