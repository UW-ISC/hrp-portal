<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Permissions;

/**
 * Canonical permission keys stored on access rules and mirrored to WordPress
 * capabilities as wpdt_{key} (see {@see wpCapability()}).
 *
 * @package WPDataTables\Services\Permissions
 */
class PermissionCatalog
{
    public const RESOURCE_TABLES = 'tables';
    public const RESOURCE_CHARTS = 'charts';

    /** @var array<string, string>|null */
    private static $labelMap = null;

    /**
     * WordPress capability name for a canonical wpDataTables permission key.
     *
     * @param string $logicalKey
     * @return string
     */
    public static function wpCapability(string $logicalKey): string
    {
        return 'wpdt_' . $logicalKey;
    }

    /**
     * @return list<string>
     */
    public static function resources(): array
    {
        return [self::RESOURCE_TABLES, self::RESOURCE_CHARTS];
    }

    /**
     * @param string $resource
     * @return bool
     */
    public static function isValidResource(string $resource): bool
    {
        return in_array($resource, self::resources(), true);
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_merge(
            self::keysForResource(self::RESOURCE_TABLES),
            self::keysForResource(self::RESOURCE_CHARTS)
        );
    }

    /**
     * @param string $resource `tables` or `charts`.
     * @return list<string>
     */
    public static function keysForResource(string $resource): array
    {
        if ($resource === self::RESOURCE_CHARTS) {
            return [
                'list_charts',
                'create_charts',
                'edit_charts',
                'delete_charts',
                'view_charts',
            ];
        }

        return [
            'list_tables',
            'create_tables',
            'edit_tables',
            'delete_tables',
            'view_tables',
        ];
    }

    /**
     * @return list<array{key:string,label:string,resource:string}>
     */
    public static function definitions(): array
    {
        return array_merge(
            self::definitionsForResource(self::RESOURCE_TABLES),
            self::definitionsForResource(self::RESOURCE_CHARTS)
        );
    }

    /**
     * @param string $resource
     * @return list<array{key:string,label:string,resource:string}>
     */
    public static function definitionsForResource(string $resource): array
    {
        if ($resource === self::RESOURCE_CHARTS) {
            return [
                ['key' => 'list_charts', 'label' => __('List / browse charts', 'wpdatatables'), 'resource' => self::RESOURCE_CHARTS],
                ['key' => 'create_charts', 'label' => __('Create charts', 'wpdatatables'), 'resource' => self::RESOURCE_CHARTS],
                ['key' => 'edit_charts', 'label' => __('Edit charts', 'wpdatatables'), 'resource' => self::RESOURCE_CHARTS],
                ['key' => 'delete_charts', 'label' => __('Delete charts', 'wpdatatables'), 'resource' => self::RESOURCE_CHARTS],
                ['key' => 'view_charts', 'label' => __('View charts', 'wpdatatables'), 'resource' => self::RESOURCE_CHARTS],
            ];
        }

        return [
            ['key' => 'list_tables', 'label' => __('List / browse tables', 'wpdatatables'), 'resource' => self::RESOURCE_TABLES],
            ['key' => 'create_tables', 'label' => __('Create tables', 'wpdatatables'), 'resource' => self::RESOURCE_TABLES],
            ['key' => 'edit_tables', 'label' => __('Edit tables', 'wpdatatables'), 'resource' => self::RESOURCE_TABLES],
            ['key' => 'delete_tables', 'label' => __('Delete tables', 'wpdatatables'), 'resource' => self::RESOURCE_TABLES],
            ['key' => 'view_tables', 'label' => __('View tables', 'wpdatatables'), 'resource' => self::RESOURCE_TABLES],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labelMap(): array
    {
        if (self::$labelMap !== null) {
            return self::$labelMap;
        }

        self::$labelMap = [];
        foreach (self::definitions() as $definition) {
            self::$labelMap[$definition['key']] = $definition['label'];
        }

        return self::$labelMap;
    }

    /**
     * Permissions implied by other grants (expanded on save).
     *
     * `view_*` stays independent so frontend visibility can be granted without
     * admin browse access.
     *
     * @return array<string, list<string>>
     */
    public static function dependencyMap(): array
    {
        return [
            'create_tables' => ['list_tables'],
            'edit_tables'   => ['list_tables'],
            'delete_tables' => ['list_tables'],
            'create_charts' => ['list_charts'],
            'edit_charts'   => ['list_charts'],
            'delete_charts' => ['list_charts'],
        ];
    }

    /**
     * @param mixed[] $permissions
     * @return list<string>
     */
    public static function sanitize(array $permissions): array
    {
        return self::sanitizeAgainstAllowed($permissions, self::keys());
    }

    /**
     * @param mixed[] $permissions
     * @param string  $resource
     * @return list<string>
     */
    public static function sanitizeForResource(array $permissions, string $resource): array
    {
        return self::sanitizeAgainstAllowed($permissions, self::keysForResource($resource));
    }

    /**
     * Sanitize grants and auto-include implied permissions (canonical key order).
     *
     * @param mixed[] $permissions
     * @return list<string>
     */
    public static function sanitizeWithDependencies(array $permissions): array
    {
        return self::expandDependencies($permissions, self::keys());
    }

    /**
     * @param mixed[] $permissions
     * @param string  $resource
     * @return list<string>
     */
    public static function sanitizeWithDependenciesForResource(array $permissions, string $resource): array
    {
        return self::expandDependencies($permissions, self::keysForResource($resource));
    }

    /**
     * @return list<string>
     */
    public static function wpCapabilities(): array
    {
        $caps = [];
        foreach (self::keys() as $key) {
            $caps[] = self::wpCapability($key);
        }

        return $caps;
    }

    /**
     * @param mixed[]      $permissions
     * @param list<string> $allowedKeys
     * @return list<string>
     */
    private static function sanitizeAgainstAllowed(array $permissions, array $allowedKeys): array
    {
        $allowed = array_flip($allowedKeys);
        $out     = [];
        foreach ($permissions as $permission) {
            if (!is_string($permission)) {
                continue;
            }
            if (isset($allowed[$permission])) {
                $out[] = $permission;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param mixed[]      $permissions
     * @param list<string> $canonicalKeys
     * @return list<string>
     */
    private static function expandDependencies(array $permissions, array $canonicalKeys): array
    {
        $allowed = array_flip($canonicalKeys);
        $granted = array_flip(self::sanitizeAgainstAllowed($permissions, $canonicalKeys));

        foreach (self::dependencyMap() as $grant => $implied) {
            if (!isset($granted[$grant])) {
                continue;
            }
            foreach ($implied as $key) {
                if (isset($allowed[$key])) {
                    $granted[$key] = true;
                }
            }
        }

        $out = [];
        foreach ($canonicalKeys as $key) {
            if (isset($granted[$key])) {
                $out[] = $key;
            }
        }

        return $out;
    }
}
