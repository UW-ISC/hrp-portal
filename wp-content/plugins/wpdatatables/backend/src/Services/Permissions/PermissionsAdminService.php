<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Permissions;

use WP_User;
use WP_User_Query;
use WPDataTables\Common\Exceptions\ValidationException;
use WPDataTables\Repository\Permissions\AccessRulesRepository;

/**
 * Admin permissions: access-rules CRUD and list HTML for the Permissions screen.
 *
 * @package WPDataTables\Services\Permissions
 */
class PermissionsAdminService
{
    public const CAPABILITY_VIEW_TABLES = 'wpdt_view_tables';
    public const CAPABILITY_VIEW_CHARTS = 'wpdt_view_charts';

    private const USERS_RESULT_LIMIT = 50;
    private const MIN_SEARCH_LENGTH = 2;

    /** @var AccessRulesRepository */
    private $rulesRepository;

    /** @var AccessRulesValidator */
    private $rulesValidator;

    public function __construct(
        AccessRulesRepository $rulesRepository,
        AccessRulesValidator $rulesValidator
    ) {
        $this->rulesRepository = $rulesRepository;
        $this->rulesValidator  = $rulesValidator;
    }

    /**
     * Register wpDataTables capabilities so role editors can assign them.
     *
     * @return void
     */
    public function registerCapabilities(): void
    {
        \WPDataTables\Services\InstallActions\ActivationCapabilitiesHook::ensureAdministratorCapsIfMissing();
        AccessRulesMigrator::runIfNeeded();
        $this->maybeResyncAccessPluginCapability();

        add_filter('members_get_capabilities', static function ($capabilities) {
            if (!is_array($capabilities)) {
                $capabilities = [];
            }

            foreach (PermissionCatalog::wpCapabilities() as $cap) {
                $capabilities[] = $cap;
            }
            $capabilities[] = WpdtAccess::CAP_ACCESS_PLUGIN;

            return array_values(array_unique($capabilities));
        });
    }

    /**
     * One-time re-sync so existing rules grant {@see WpdtAccess::CAP_ACCESS_PLUGIN}.
     *
     * @return void
     */
    private function maybeResyncAccessPluginCapability(): void
    {
        $optionKey = 'wpdt_access_plugin_cap_synced';
        if ((int) get_option($optionKey, 0) === 1) {
            return;
        }

        $payload = $this->rulesRepository->getPayload();
        $rules   = $payload['rules'] ?? [];
        if (!is_array($rules)) {
            $rules = [];
        }

        AccessRulesRoleCapabilitySynchronizer::syncFromRules(
            $rules,
            AccessRulesRoleCapabilitySynchronizer::extractUserIdsFromUserRules($rules)
        );
        update_option($optionKey, 1, false);
    }

    /**
     * Build the permissions table body HTML for the requested tab.
     *
     * @param string $tab    `tables` or `charts`.
     * @param string $search Optional search term.
     * @return string
     */
    public function getPermissionsListHtml(string $tab, string $search = ''): string
    {
        $resource = $this->normalizeResource($tab);
        $rules    = $this->rulesRepository->getRulesForResource($resource);
        $html     = '';
        $foundAny = false;

        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }

            $row = $this->buildListRowData($rule, $resource);
            if ($row === null) {
                continue;
            }

            if ($search !== '' && !$this->rowMatchesSearch($row, $search)) {
                continue;
            }

            $foundAny = true;
            $html    .= $this->renderListRowHtml($row);
        }

        if (!$foundAny) {
            return '<tr><td colspan="7" style="text-align: center; padding: 24px;">' .
                esc_html__('No permissions assigned yet.', 'wpdatatables') .
                '</td></tr>';
        }

        return $html;
    }

    /**
     * Create one rule per principal (role slug or user id).
     *
     * @param string        $tab
     * @param string        $targetType `role` or `user`
     * @param list<string>  $roleSlugs
     * @param list<int>     $userIds
     * @param list<string>  $permissions
     * @param int           $enableSpecific
     * @param list<int>     $itemIds
     * @return array{created:int}
     *
     * @throws ValidationException
     */
    public function savePermission(
        string $tab,
        string $targetType,
        array $roleSlugs,
        array $userIds,
        array $permissions,
        int $enableSpecific,
        array $itemIds
    ): array {
        $resource = $this->normalizeResource($tab);
        $now      = time();
        $itemIdsNormalized = $enableSpecific ? array_values(array_unique(array_map('intval', $itemIds))) : null;
        if ($enableSpecific && ($itemIdsNormalized === null || $itemIdsNormalized === [])) {
            throw new ValidationException(
                __('Please select at least one item when limiting scope.', 'wpdatatables')
            );
        }

        $drafts = [];
        if ($targetType === 'role') {
            foreach ($this->sanitizeRoleSlugs($roleSlugs) as $slug) {
                $drafts[] = [
                    'id'          => $this->generateRuleId(),
                    'type'        => 'role',
                    'role_slug'   => $slug,
                    'resource'    => $resource,
                    'permissions' => $permissions,
                    'item_ids'    => $itemIdsNormalized,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
        } elseif ($targetType === 'user') {
            foreach ($this->sanitizeUserIds($userIds) as $userId) {
                $drafts[] = [
                    'id'          => $this->generateRuleId(),
                    'type'        => 'user',
                    'user_id'     => $userId,
                    'resource'    => $resource,
                    'permissions' => $permissions,
                    'item_ids'    => $itemIdsNormalized,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
        } else {
            throw new ValidationException(
                __('Target type must be role or user.', 'wpdatatables')
            );
        }

        if ($drafts === []) {
            throw new ValidationException(
                __('Please select at least one role or user.', 'wpdatatables')
            );
        }

        $existing = $this->rulesRepository->getPayload()['rules'];
        $previousUserIds = AccessRulesRoleCapabilitySynchronizer::extractUserIdsFromUserRules($existing);
        $merged = array_merge($existing, $drafts);
        $normalized = $this->rulesValidator->validateAndNormalize($merged);

        $this->rulesRepository->saveRules($normalized);
        AccessRulesRoleCapabilitySynchronizer::syncFromRules($normalized, $previousUserIds);

        return ['created' => count($drafts)];
    }

    /**
     * Update an existing rule by id (permissions + scope; principal locked).
     *
     * @param string       $tab
     * @param string       $ruleId
     * @param list<string> $permissions
     * @param int          $enableSpecific
     * @param list<int>    $itemIds
     * @return bool
     *
     * @throws ValidationException
     */
    public function updatePermission(
        string $tab,
        string $ruleId,
        array $permissions,
        int $enableSpecific,
        array $itemIds
    ): bool {
        $resource = $this->normalizeResource($tab);
        $rules    = $this->rulesRepository->getPayload()['rules'];
        $found    = false;
        $now      = time();

        $itemIdsNormalized = $enableSpecific ? array_values(array_unique(array_map('intval', $itemIds))) : null;
        if ($enableSpecific && ($itemIdsNormalized === null || $itemIdsNormalized === [])) {
            throw new ValidationException(
                __('Please select at least one item when limiting scope.', 'wpdatatables')
            );
        }

        foreach ($rules as $index => $rule) {
            if (!is_array($rule) || (string) ($rule['id'] ?? '') !== $ruleId) {
                continue;
            }
            if ((string) ($rule['resource'] ?? '') !== $resource) {
                throw new ValidationException(
                    __('Access rule resource does not match the current tab.', 'wpdatatables')
                );
            }

            $rules[$index]['permissions'] = $permissions;
            $rules[$index]['item_ids']    = $itemIdsNormalized;
            $rules[$index]['updated_at']  = $now;
            $found = true;
            break;
        }

        if (!$found) {
            return false;
        }

        $previousUserIds = AccessRulesRoleCapabilitySynchronizer::extractUserIdsFromUserRules($rules);
        $normalized = $this->rulesValidator->validateAndNormalize(array_values($rules));
        $this->rulesRepository->saveRules($normalized);
        AccessRulesRoleCapabilitySynchronizer::syncFromRules($normalized, $previousUserIds);

        return true;
    }

    /**
     * Delete a rule by id.
     *
     * @param string $tab
     * @param string $ruleId
     * @return bool
     */
    public function deletePermission(string $tab, string $ruleId): bool
    {
        $resource = $this->normalizeResource($tab);
        $rules    = $this->rulesRepository->getPayload()['rules'];
        $previousUserIds = AccessRulesRoleCapabilitySynchronizer::extractUserIdsFromUserRules($rules);
        $filtered = [];
        $found    = false;

        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            if ((string) ($rule['id'] ?? '') === $ruleId && (string) ($rule['resource'] ?? '') === $resource) {
                $found = true;
                continue;
            }
            $filtered[] = $rule;
        }

        if (!$found) {
            return false;
        }

        $normalized = $filtered === []
            ? []
            : $this->rulesValidator->validateAndNormalize($filtered);

        $this->rulesRepository->saveRules($normalized);
        AccessRulesRoleCapabilitySynchronizer::syncFromRules($normalized, $previousUserIds);

        return true;
    }

    /**
     * Fetch a rule for the edit modal.
     *
     * @param string $tab
     * @param string $ruleId
     * @return array<string,mixed>|null
     */
    public function getPermission(string $tab, string $ruleId): ?array
    {
        $resource = $this->normalizeResource($tab);

        foreach ($this->rulesRepository->getRulesForResource($resource) as $rule) {
            if (!is_array($rule) || (string) ($rule['id'] ?? '') !== $ruleId) {
                continue;
            }

            $type = (string) ($rule['type'] ?? '');
            $allItems = !array_key_exists('item_ids', $rule) || $rule['item_ids'] === null;
            $itemIds  = $allItems ? [] : array_map('intval', (array) $rule['item_ids']);

            $result = [
                'id'          => (string) $rule['id'],
                'type'        => $type,
                'resource'    => $resource,
                'permissions' => array_values((array) ($rule['permissions'] ?? [])),
                'all_items'   => $allItems,
                'item_ids'    => $itemIds,
            ];

            if ($type === 'role') {
                $slug = (string) ($rule['role_slug'] ?? '');
                $result['role_slug'] = $slug;
                $result['principal'] = $this->roleDisplayName($slug);
            } else {
                $userId = (int) ($rule['user_id'] ?? 0);
                $user   = $userId > 0 ? get_userdata($userId) : false;
                $result['user_id']   = $userId;
                $result['principal'] = $user ? $user->user_login : (string) $userId;
                $result['email']     = $user ? $user->user_email : '';
            }

            return $result;
        }

        return null;
    }

    /**
     * Meta for the permissions admin UI (roles, catalog, items).
     *
     * @param string $tab
     * @return array<string,mixed>
     */
    public function getPermissionsMeta(string $tab): array
    {
        $resource = $this->normalizeResource($tab);
        $roles    = [];

        foreach (wp_roles()->roles as $slug => $role) {
            if ($slug === 'administrator') {
                continue;
            }
            $roles[] = [
                'slug' => (string) $slug,
                'name' => translate_user_role((string) ($role['name'] ?? $slug)),
            ];
        }

        usort($roles, static function ($a, $b) {
            return strcasecmp((string) $a['name'], (string) $b['name']);
        });

        return [
            'resource' => $resource,
            'roles'    => $roles,
            'catalog'  => PermissionCatalog::definitionsForResource($resource),
            'items'    => $this->getItemsForResource($resource),
        ];
    }

    /**
     * Remote user search for the permission modal.
     *
     * @param string $search
     * @return list<array{id:int,login:string,email:string,label:string}>
     */
    public function searchPermissionUsers(string $search): array
    {
        $search = trim($search);
        $args   = [
            'orderby'      => 'user_login',
            'order'        => 'ASC',
            'fields'       => ['ID', 'user_login', 'user_email'],
            'role__not_in' => ['administrator'],
            'count_total'  => false,
            'number'       => self::USERS_RESULT_LIMIT,
        ];

        if ($search !== '' && strlen($search) >= self::MIN_SEARCH_LENGTH) {
            $args['search']         = '*' . $search . '*';
            $args['search_columns'] = ['user_login', 'user_email', 'display_name'];
        }

        $query  = new WP_User_Query($args);
        $users  = [];

        foreach ($query->get_results() as $row) {
            $userId = is_object($row) ? (int) ($row->ID ?? 0) : 0;
            if ($userId <= 0) {
                continue;
            }

            $user = get_userdata($userId);
            if (!$user instanceof WP_User) {
                continue;
            }
            if (WpdtAccess::userShouldBeExcludedFromPermissionsPicker($user)) {
                continue;
            }

            $login = (string) $user->user_login;
            $email = trim((string) $user->user_email);
            $users[] = [
                'id'    => $userId,
                'login' => $login,
                'email' => $email,
                'label' => $email !== '' ? $login . ' (' . $email . ')' : $login,
            ];
        }

        return $users;
    }

    /**
     * Resolve the capability constant for a tab slug (legacy helpers / migration).
     *
     * @param string $tab
     * @return string
     */
    public function getCapabilityForTab(string $tab): string
    {
        return $tab === 'charts' ? self::CAPABILITY_VIEW_CHARTS : self::CAPABILITY_VIEW_TABLES;
    }

    /**
     * Row data for PermissionsListTable (server-rendered headers / empty state).
     *
     * @param string $tab
     * @param string $search
     * @return list<array<string,mixed>>
     */
    public function getPermissionsListRows(string $tab, string $search = ''): array
    {
        $resource = $this->normalizeResource($tab);
        $rows     = [];

        foreach ($this->rulesRepository->getRulesForResource($resource) as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            $row = $this->buildListRowData($rule, $resource);
            if ($row === null) {
                continue;
            }
            if ($search !== '' && !$this->rowMatchesSearch($row, $search)) {
                continue;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param string $tab
     * @return string
     */
    private function normalizeResource(string $tab): string
    {
        return $tab === PermissionCatalog::RESOURCE_CHARTS
            ? PermissionCatalog::RESOURCE_CHARTS
            : PermissionCatalog::RESOURCE_TABLES;
    }

    /**
     * @param array<string,mixed> $rule
     * @param string              $resource
     * @return array<string,mixed>|null
     */
    private function buildListRowData(array $rule, string $resource): ?array
    {
        $type = (string) ($rule['type'] ?? '');
        if (!in_array($type, ['role', 'user'], true)) {
            return null;
        }

        $ruleId = (string) ($rule['id'] ?? '');
        if ($ruleId === '') {
            return null;
        }

        $principal = '';
        $email     = '—';
        $idDisplay = '—';

        if ($type === 'role') {
            $slug      = (string) ($rule['role_slug'] ?? '');
            $principal = $this->roleDisplayName($slug);
            $idDisplay = $slug !== '' ? $slug : '—';
        } else {
            $userId = (int) ($rule['user_id'] ?? 0);
            $user   = $userId > 0 ? get_userdata($userId) : false;
            if (!$user) {
                return null;
            }
            if (WpdtAccess::userShouldBeExcludedFromPermissionsPicker($user)) {
                return null;
            }
            $principal = (string) $user->user_login;
            $email     = (string) $user->user_email;
            $idDisplay = (string) $userId;
        }

        $permissions = array_values((array) ($rule['permissions'] ?? []));
        $labels      = [];
        $labelMap    = PermissionCatalog::labelMap();
        foreach ($permissions as $key) {
            if (isset($labelMap[$key])) {
                $labels[] = $labelMap[$key];
            }
        }

        return [
            'rule_id'     => $ruleId,
            'id'          => $idDisplay,
            'principal'   => $principal,
            'type'        => $type,
            'type_label'  => $type === 'role'
                ? __('Role', 'wpdatatables')
                : __('User', 'wpdatatables'),
            'email'       => $email,
            'items'       => $this->formatItemsDisplay($rule['item_ids'] ?? null, $resource),
            'permissions' => implode(', ', $labels),
            'search_blob' => strtolower($idDisplay . ' ' . $principal . ' ' . $email . ' ' . $type),
        ];
    }

    /**
     * @param array<string,mixed> $row
     * @return string
     */
    private function renderListRowHtml(array $row): string
    {
        $ruleId = esc_attr((string) $row['rule_id']);
        $html   = '<tr>';
        $html  .= '<td class="column-id">' . esc_html((string) $row['id']) . '</td>';
        $html  .= '<td class="column-principal"><a href="#">' . esc_html((string) $row['principal']) . '</a></td>';
        $html  .= '<td class="column-type">' . esc_html((string) $row['type_label']) . '</td>';
        $html  .= '<td class="column-email">' . esc_html((string) $row['email']) . '</td>';
        $html  .= '<td class="column-items">' . esc_html((string) $row['items']) . '</td>';
        $html  .= '<td class="column-permissions">' . esc_html((string) $row['permissions']) . '</td>';
        $html  .= '<td class="column-actions">';
        $html  .= '<div class="wdt-function-flex">';
        $html  .= '<a href="#" class="wdt-edit-permission" data-id="' . $ruleId . '" data-toggle="tooltip" title="' .
            esc_attr__('Edit', 'wpdatatables') . '"><i class="wpdt-icon-pen"></i></a>';
        $html  .= '<a href="#" class="wdt-delete-permission" data-id="' . $ruleId . '" data-toggle="tooltip" title="' .
            esc_attr__('Delete', 'wpdatatables') . '"><i class="wpdt-icon-trash"></i></a>';
        $html  .= '</div>';
        $html  .= '</td>';
        $html  .= '</tr>';

        return $html;
    }

    /**
     * @param array<string,mixed> $row
     * @param string              $search
     * @return bool
     */
    private function rowMatchesSearch(array $row, string $search): bool
    {
        $needle = strtolower($search);
        if (stripos((string) $row['search_blob'], $needle) !== false) {
            return true;
        }
        if (stripos((string) $row['rule_id'], $search) !== false) {
            return true;
        }
        if (stripos((string) $row['permissions'], $search) !== false) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed  $itemIds
     * @param string $resource
     * @return string
     */
    private function formatItemsDisplay($itemIds, string $resource): string
    {
        if ($itemIds === null) {
            return __('All', 'wpdatatables');
        }
        if (!is_array($itemIds) || $itemIds === []) {
            return '—';
        }

        $ids = array_values(array_unique(array_map('intval', $itemIds)));
        if ($ids === []) {
            return '—';
        }

        global $wpdb;
        $dbTable = $resource === PermissionCatalog::RESOURCE_CHARTS
            ? $wpdb->prefix . 'wpdatacharts'
            : $wpdb->prefix . 'wpdatatables';
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $query = $wpdb->prepare(
            "SELECT GROUP_CONCAT(title SEPARATOR ', ') as titles FROM {$dbTable} WHERE id IN ($placeholders)",
            ...$ids
        );
        $result = $wpdb->get_var($query);

        return $result ?: '—';
    }

    /**
     * @param string $resource
     * @return list<array{id:int,title:string}>
     */
    private function getItemsForResource(string $resource): array
    {
        global $wpdb;
        $dbTable = $resource === PermissionCatalog::RESOURCE_CHARTS
            ? $wpdb->prefix . 'wpdatacharts'
            : $wpdb->prefix . 'wpdatatables';
        $rows = $wpdb->get_results(
            "SELECT id, title FROM {$dbTable} ORDER BY title ASC",
            ARRAY_A
        );

        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'id'    => (int) $row['id'],
                'title' => (string) $row['title'],
            ];
        }

        return $out;
    }

    /**
     * @param string $slug
     * @return string
     */
    private function roleDisplayName(string $slug): string
    {
        if ($slug === '') {
            return '—';
        }
        $role = wp_roles()->get_role($slug);
        if ($role === null) {
            return $slug;
        }
        $names = wp_roles()->role_names;
        $name  = isset($names[$slug]) ? (string) $names[$slug] : $slug;

        return translate_user_role($name);
    }

    /**
     * @param mixed[] $roleSlugs
     * @return list<string>
     */
    private function sanitizeRoleSlugs(array $roleSlugs): array
    {
        $out = [];
        foreach ($roleSlugs as $slug) {
            $slug = sanitize_key((string) $slug);
            if ($slug === '' || $slug === 'administrator') {
                continue;
            }
            if (!wp_roles()->is_role($slug)) {
                continue;
            }
            $out[$slug] = true;
        }

        return array_keys($out);
    }

    /**
     * @param mixed[] $userIds
     * @return list<int>
     */
    private function sanitizeUserIds(array $userIds): array
    {
        $out = [];
        foreach ($userIds as $userId) {
            $id = (int) $userId;
            if ($id <= 0) {
                continue;
            }
            $user = get_userdata($id);
            if (!$user instanceof WP_User) {
                continue;
            }
            if (WpdtAccess::userShouldBeExcludedFromPermissionsPicker($user)) {
                continue;
            }
            $out[$id] = true;
        }

        return array_map('intval', array_keys($out));
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
