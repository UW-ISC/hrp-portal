<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Permissions;

use WPDataTables\Common\Exceptions\ValidationException;
use WPDataTables\Repository\Chart\ChartRepositoryInterface;
use WPDataTables\Repository\Table\TableRepositoryInterface;

/**
 * Validates and normalizes wpdt_access_rules payloads.
 *
 * @package WPDataTables\Services\Permissions
 */
class AccessRulesValidator
{
    /** @var TableRepositoryInterface */
    private $tableRepository;

    /** @var ChartRepositoryInterface */
    private $chartRepository;

    public function __construct(
        TableRepositoryInterface $tableRepository,
        ChartRepositoryInterface $chartRepository
    ) {
        $this->tableRepository = $tableRepository;
        $this->chartRepository = $chartRepository;
    }

    /**
     * @param mixed[] $rules
     * @return list<array<string,mixed>>
     *
     * @throws ValidationException
     */
    public function validateAndNormalize(array $rules): array
    {
        if (!$this->isList($rules)) {
            throw new ValidationException(
                __('Access rules payload must be a list of rules.', 'wpdatatables')
            );
        }

        $normalized   = [];
        $seenIds      = [];
        $ruleDataList = [];
        $tableIds     = [];
        $chartIds     = [];

        foreach ($rules as $index => $rule) {
            $ruleData       = $this->validateRuleBase($rule, (int) $index, $seenIds);
            $ruleDataList[] = $ruleData;

            if (!array_key_exists('item_ids', $ruleData) || $ruleData['item_ids'] === null) {
                continue;
            }
            if (!is_array($ruleData['item_ids'])) {
                continue;
            }

            $resource = (string) $ruleData['resource'];
            foreach ($ruleData['item_ids'] as $itemId) {
                if ($resource === PermissionCatalog::RESOURCE_CHARTS) {
                    $chartIds[] = (int) $itemId;
                    continue;
                }
                $tableIds[] = (int) $itemId;
            }
        }

        $existingTableIds = $this->buildExistingIdSet(
            $this->tableRepository->filterExistingIds($tableIds)
        );
        $existingChartIds = $this->buildExistingIdSet(
            $this->chartRepository->filterExistingIds($chartIds)
        );

        foreach ($ruleDataList as $ruleData) {
            $permissions = $this->normalizePermissions($ruleData);
            $itemIds     = $this->normalizeItemIds(
                $ruleData,
                (string) $ruleData['resource'] === PermissionCatalog::RESOURCE_CHARTS
                    ? $existingChartIds
                    : $existingTableIds
            );
            $timestamps = $this->resolveTimestamps($ruleData);

            if ($ruleData['type'] === 'role') {
                $normalized[] = $this->buildRoleRule($ruleData, $permissions, $itemIds, $timestamps);
                continue;
            }
            $normalized[] = $this->buildUserRule($ruleData, $permissions, $itemIds, $timestamps);
        }

        return $normalized;
    }

    /**
     * @param list<int> $ids
     * @return array<int, true>
     */
    private function buildExistingIdSet(array $ids): array
    {
        $set = [];
        foreach ($ids as $id) {
            $set[(int) $id] = true;
        }

        return $set;
    }

    /**
     * @param mixed              $rule
     * @param int                $index
     * @param array<string,bool> $seenIds
     * @return array<string,mixed>
     *
     * @throws ValidationException
     */
    private function validateRuleBase($rule, int $index, array &$seenIds): array
    {
        if (!is_array($rule)) {
            throw new ValidationException(
                sprintf(
                    /* translators: %d: rule index */
                    __('Access rule at index %d is invalid.', 'wpdatatables'),
                    $index
                )
            );
        }

        $type = isset($rule['type']) ? (string) $rule['type'] : '';
        if (!in_array($type, ['role', 'user'], true)) {
            throw new ValidationException(
                __('Each access rule must have type "role" or "user".', 'wpdatatables')
            );
        }

        $resource = isset($rule['resource']) ? (string) $rule['resource'] : '';
        if (!PermissionCatalog::isValidResource($resource)) {
            throw new ValidationException(
                __('Each access rule must have resource "tables" or "charts".', 'wpdatatables')
            );
        }

        $id = isset($rule['id']) ? (string) $rule['id'] : '';
        if ($id === '') {
            throw new ValidationException(
                __('Each access rule must have a non-empty id.', 'wpdatatables')
            );
        }
        if (isset($seenIds[$id])) {
            throw new ValidationException(
                __('Access rule ids must be unique.', 'wpdatatables')
            );
        }
        $seenIds[$id] = true;

        $rule['type']     = $type;
        $rule['resource'] = $resource;
        $rule['id']       = $id;

        return $rule;
    }

    /**
     * @param array<string,mixed> $rule
     * @return list<string>
     *
     * @throws ValidationException
     */
    private function normalizePermissions(array $rule): array
    {
        $raw = isset($rule['permissions']) && is_array($rule['permissions'])
            ? $rule['permissions']
            : [];
        $permissions = PermissionCatalog::sanitizeWithDependenciesForResource(
            $raw,
            (string) $rule['resource']
        );
        if ($permissions === []) {
            throw new ValidationException(
                __('Each access rule must grant at least one permission.', 'wpdatatables')
            );
        }

        return $permissions;
    }

    /**
     * @param array<string,mixed> $rule
     * @param array<int, true>    $existingItemIds
     * @return list<int>|null
     *
     * @throws ValidationException
     */
    private function normalizeItemIds(array $rule, array $existingItemIds): ?array
    {
        if (!array_key_exists('item_ids', $rule) || $rule['item_ids'] === null) {
            return null;
        }
        if (!is_array($rule['item_ids'])) {
            throw new ValidationException(
                __('item_ids must be null or an array of positive integers.', 'wpdatatables')
            );
        }

        $requestedIds = [];
        $itemIds      = [];
        foreach ($rule['item_ids'] as $itemId) {
            $normalizedId = (int) $itemId;
            if ($normalizedId <= 0) {
                throw new ValidationException(
                    __('Each item_ids value must be a positive integer.', 'wpdatatables')
                );
            }
            $requestedIds[] = $normalizedId;
            if (!isset($existingItemIds[$normalizedId])) {
                continue;
            }
            $itemIds[] = $normalizedId;
        }

        $itemIds = array_values(array_unique($itemIds));
        if ($requestedIds !== [] && $itemIds === []) {
            $missing = array_values(array_unique($requestedIds));
            throw new ValidationException(
                sprintf(
                    /* translators: %s: comma-separated missing item ids */
                    __('The following item ids no longer exist: %s', 'wpdatatables'),
                    implode(', ', array_map('strval', $missing))
                )
            );
        }

        return $itemIds;
    }

    /**
     * @param array<string,mixed> $rule
     * @return array{created_at:int,updated_at:int}
     */
    private function resolveTimestamps(array $rule): array
    {
        $createdAt = isset($rule['created_at']) ? (int) $rule['created_at'] : 0;
        $updatedAt = isset($rule['updated_at']) ? (int) $rule['updated_at'] : 0;
        $now       = time();
        if ($createdAt <= 0) {
            $createdAt = $now;
        }
        if ($updatedAt <= 0) {
            $updatedAt = $now;
        }

        return [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * @param array<string,mixed>          $rule
     * @param list<string>                 $permissions
     * @param list<int>|null               $itemIds
     * @param array{created_at:int,updated_at:int} $timestamps
     * @return array<string,mixed>
     *
     * @throws ValidationException
     */
    private function buildRoleRule(array $rule, array $permissions, ?array $itemIds, array $timestamps): array
    {
        $slug = isset($rule['role_slug']) ? (string) $rule['role_slug'] : '';
        if ($slug === '' || !wp_roles()->is_role($slug)) {
            throw new ValidationException(
                __('Access rule role_slug is invalid.', 'wpdatatables')
            );
        }

        return [
            'id'          => $rule['id'],
            'type'        => 'role',
            'role_slug'   => $slug,
            'resource'    => $rule['resource'],
            'permissions' => $permissions,
            'item_ids'    => $itemIds,
            'created_at'  => $timestamps['created_at'],
            'updated_at'  => $timestamps['updated_at'],
        ];
    }

    /**
     * @param array<string,mixed>          $rule
     * @param list<string>                 $permissions
     * @param list<int>|null               $itemIds
     * @param array{created_at:int,updated_at:int} $timestamps
     * @return array<string,mixed>
     *
     * @throws ValidationException
     */
    private function buildUserRule(array $rule, array $permissions, ?array $itemIds, array $timestamps): array
    {
        $userId = isset($rule['user_id']) ? (int) $rule['user_id'] : 0;
        if ($userId <= 0 || !get_user_by('id', $userId)) {
            throw new ValidationException(
                __('Access rule user_id is invalid.', 'wpdatatables')
            );
        }

        return [
            'id'          => $rule['id'],
            'type'        => 'user',
            'user_id'     => $userId,
            'resource'    => $rule['resource'],
            'permissions' => $permissions,
            'item_ids'    => $itemIds,
            'created_at'  => $timestamps['created_at'],
            'updated_at'  => $timestamps['updated_at'],
        ];
    }

    /**
     * PHP 7.4-compatible array_is_list().
     *
     * @param array $array
     * @return bool
     */
    private function isList(array $array): bool
    {
        if (function_exists('array_is_list')) {
            return array_is_list($array);
        }

        if ($array === []) {
            return true;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }
}
