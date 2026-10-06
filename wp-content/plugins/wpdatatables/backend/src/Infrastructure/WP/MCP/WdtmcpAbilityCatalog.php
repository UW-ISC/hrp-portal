<?php

/**
 * Tiered catalog of wpDataTables MCP abilities and their permission gates.
 *
 * Maps each ability to the same logical permission key (or special gate) that
 * backs the matching admin REST/AJAX surface via {@see \WPDataTables\Services\Permissions\PermissionsService}.
 *
 * Keep this list aligned with {@see WdtmcpMcpServerRegistrar} ability IDs.
 *
 * @package wpDataTables
 */

namespace WDTMCP\Infrastructure\WP\MCP;

defined('ABSPATH') or die('Access denied.');

class WdtmcpAbilityCatalog
{
    /**
     * Elevated-admin only (Settings / System info / license matrix).
     */
    public const GATE_ADMIN = '__admin__';

    /**
     * Read table config/data: edit_tables for the item, or list_tables (mirrors RestPermissions::canReadTableRequest).
     */
    public const GATE_READ_TABLE = '__read_table__';

    /**
     * Read chart config: edit_charts for the item, or list_charts.
     */
    public const GATE_READ_CHART = '__read_chart__';

    /**
     * Media helpers used while creating/editing tables.
     */
    public const GATE_MEDIA = '__media__';

    /**
     * Maps an MCP ability to a permission key or special gate.
     *
     * Null = baseline MCP access only (any delegated plugin access / elevated admin).
     *
     * @var array<string, string|null>
     */
    private const ABILITY_PERMISSIONS = array(
        // Tables (read)
        'wpdatatables/list-tables' => 'list_tables',
        'wpdatatables/get-table-info' => self::GATE_READ_TABLE,
        'wpdatatables/get-table-data' => self::GATE_READ_TABLE,
        'wpdatatables/open-table-editor' => self::GATE_READ_TABLE,
        // Tables (write)
        'wpdatatables/create-table-from-source' => 'create_tables',
        'wpdatatables/import-table-from-source' => 'create_tables',
        'wpdatatables/create-simple-table' => 'create_tables',
        'wpdatatables/create-table-from-query' => 'create_tables',
        'wpdatatables/edit-table' => 'edit_tables',
        'wpdatatables/update-table-settings' => 'edit_tables',
        'wpdatatables/update-simple-table-styles' => 'edit_tables',
        // Charts
        'wpdatatables/list-charts' => 'list_charts',
        'wpdatatables/get-chart-info' => self::GATE_READ_CHART,
        'wpdatatables/open-chart-wizard' => self::GATE_READ_CHART,
        'wpdatatables/create-chart' => 'create_charts',
        'wpdatatables/edit-chart' => 'edit_charts',
        // DB discovery (SQL constructor workflow)
        'wpdatatables/list-db-tables' => 'create_tables',
        'wpdatatables/describe-db-table' => 'create_tables',
        // Media
        'wpdatatables/list-media' => self::GATE_MEDIA,
        'wpdatatables/upload-media-from-url' => self::GATE_MEDIA,
        'wpdatatables/upload-data-file' => 'create_tables',
        // Informational (baseline MCP access)
        'wpdatatables/get-changelog' => null,
        // Admin-only
        'wpdatatables/get-system-info' => self::GATE_ADMIN,
        'wpdatatables/get-mcp-license-matrix' => self::GATE_ADMIN,
        'wpdatatables/update-global-settings' => self::GATE_ADMIN,
    );

    /**
     * @var list<string>
     */
    public const ADMIN_ONLY_ABILITIES = array(
        'wpdatatables/get-system-info',
        'wpdatatables/get-mcp-license-matrix',
        'wpdatatables/update-global-settings',
    );

    /**
     * @param string $abilityId
     * @return bool
     */
    public static function isKnown(string $abilityId): bool
    {
        return array_key_exists($abilityId, self::ABILITY_PERMISSIONS);
    }

    /**
     * Permission key / special gate for the ability, or null for baseline-only.
     *
     * @param string $abilityId
     * @return string|null
     */
    public static function permissionKey(string $abilityId): ?string
    {
        if (!self::isKnown($abilityId)) {
            return self::GATE_ADMIN;
        }

        return self::ABILITY_PERMISSIONS[$abilityId];
    }

    /**
     * @param string $abilityId
     * @return bool
     */
    public static function isAdminOnly(string $abilityId): bool
    {
        return in_array($abilityId, self::ADMIN_ONLY_ABILITIES, true)
            || self::permissionKey($abilityId) === self::GATE_ADMIN;
    }
}
