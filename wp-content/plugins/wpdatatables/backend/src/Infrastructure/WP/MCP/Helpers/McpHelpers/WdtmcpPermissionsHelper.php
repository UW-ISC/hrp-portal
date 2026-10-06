<?php

/**
 * Permission helpers for wpDataTables MCP abilities.
 *
 * Single source of truth for MCP permission decisions (IvyForms-style):
 * AI assistant capabilities follow the same access rules as the admin UI / REST
 * layer via {@see PermissionsService}. Keep capability logic here — do not add
 * ad-hoc capability checks inside individual abilities.
 *
 * @package wpDataTables
 */

defined('ABSPATH') or die('Access denied.');

use WDTMCP\Infrastructure\WP\MCP\WdtmcpAbilityCatalog;
use WPDataTables\Plugin\Plugin;
use WPDataTables\Services\Permissions\PermissionCatalog;
use WPDataTables\Services\Permissions\PermissionsService;
use WPDataTables\Services\Permissions\WpdtAccess;

if (! function_exists('wdtmcp_permissions_service')) {
    /**
     * @return PermissionsService|null
     */
    function wdtmcp_permissions_service()
    {
        if (! class_exists(Plugin::class)) {
            return null;
        }

        try {
            return Plugin::container()->get(PermissionsService::class);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (! function_exists('wdtmcp_current_user_can_use_mcp')) {
    /**
     * Baseline MCP gate: elevated admins or any delegated wpDataTables permission.
     *
     * @return bool
     */
    function wdtmcp_current_user_can_use_mcp(): bool
    {
        if (! is_user_logged_in()) {
            return false;
        }

        if (WpdtAccess::hasImplicitElevatedAccess()) {
            return true;
        }

        if (WpdtAccess::currentUserCanAccessPlugin()) {
            return true;
        }

        $permissions = wdtmcp_permissions_service();
        if (! $permissions) {
            return false;
        }

        $user = wp_get_current_user();
        if (! $user instanceof \WP_User || (int) $user->ID <= 0) {
            return false;
        }

        return $permissions->userCanAnyOf($user, PermissionCatalog::keys());
    }
}

if (! function_exists('wdtmcp_user_can_use_mcp')) {
    /**
     * @param \WP_User $user
     * @return bool
     */
    function wdtmcp_user_can_use_mcp($user): bool
    {
        if (! $user instanceof \WP_User || (int) $user->ID <= 0) {
            return false;
        }

        if (WpdtAccess::userHasImplicitElevatedAccess($user)) {
            return true;
        }

        if (user_can($user, WpdtAccess::CAP_ACCESS_PLUGIN)) {
            return true;
        }

        $permissions = wdtmcp_permissions_service();
        if (! $permissions) {
            return false;
        }

        return $permissions->userCanAnyOf($user, PermissionCatalog::keys());
    }
}

if (! function_exists('wdtmcp_can_execute_ability')) {
    /**
     * Ability-aware permission decision for `permission_callback` wiring.
     *
     * Closures registered with `wp_register_ability` should call this helper with
     * the ability id rather than reimplementing capability logic.
     *
     * @param string     $abilityId
     * @param mixed|null $input     Optional ability input (for item-scoped checks).
     * @return bool
     */
    function wdtmcp_can_execute_ability(string $abilityId, $input = null): bool
    {
        if (! WdtmcpAbilityCatalog::isKnown($abilityId)) {
            return false;
        }

        if (! wdtmcp_current_user_can_use_mcp()) {
            return false;
        }

        if (WdtmcpAbilityCatalog::isAdminOnly($abilityId)) {
            // Settings / system-info stay administrator-only (never delegated).
            return WpdtAccess::currentUserCanAccessPermissionsSettings();
        }

        $gate = WdtmcpAbilityCatalog::permissionKey($abilityId);
        if ($gate === null) {
            // Informational abilities (e.g. changelog) require only baseline MCP access.
            return true;
        }

        $permissions = wdtmcp_permissions_service();
        if (! $permissions) {
            return current_user_can('manage_options');
        }

        switch ($gate) {
            case WdtmcpAbilityCatalog::GATE_READ_TABLE:
                return wdtmcp_can_read_table($input);

            case WdtmcpAbilityCatalog::GATE_READ_CHART:
                return wdtmcp_can_read_chart($input);

            case WdtmcpAbilityCatalog::GATE_MEDIA:
                return $permissions->canCreateTables() || $permissions->canEditTable(null);

            case 'list_tables':
                return $permissions->canListTables();

            case 'create_tables':
                return $permissions->canCreateTables();

            case 'edit_tables':
                $tableId = wdtmcp_permission_input_id($input, array('table_id', 'id'));
                return $permissions->canEditTable($tableId > 0 ? $tableId : null);

            case 'list_charts':
                return $permissions->canListCharts();

            case 'create_charts':
                return $permissions->canCreateCharts();

            case 'edit_charts':
                $chartId = wdtmcp_permission_input_id($input, array('chart_id', 'id'));
                return $permissions->canEditChart($chartId > 0 ? $chartId : null);

            default:
                return false;
        }
    }
}

if (! function_exists('wdtmcp_can_read_table')) {
    /**
     * Read table config/data: edit grant for the item, or list grant within browse scope.
     *
     * @param mixed $input
     * @return bool
     */
    function wdtmcp_can_read_table($input = null): bool
    {
        $permissions = wdtmcp_permissions_service();
        if (! $permissions) {
            return current_user_can('manage_options');
        }

        $tableId = wdtmcp_permission_input_id($input, array('table_id', 'id'));
        if ($tableId <= 0) {
            return $permissions->canListTables() || $permissions->canEditTable(null);
        }

        if ($permissions->canEditTable($tableId)) {
            return true;
        }

        if (! $permissions->canListTables()) {
            return false;
        }

        $allowed = $permissions->getAllowedTableIds();

        return $allowed === null || in_array($tableId, $allowed, true);
    }
}

if (! function_exists('wdtmcp_can_read_chart')) {
    /**
     * Read chart config: edit grant for the item, or list grant within browse scope.
     *
     * @param mixed $input
     * @return bool
     */
    function wdtmcp_can_read_chart($input = null): bool
    {
        $permissions = wdtmcp_permissions_service();
        if (! $permissions) {
            return current_user_can('manage_options');
        }

        $chartId = wdtmcp_permission_input_id($input, array('chart_id', 'id'));
        if ($chartId <= 0) {
            return $permissions->canListCharts() || $permissions->canEditChart(null);
        }

        if ($permissions->canEditChart($chartId)) {
            return true;
        }

        if (! $permissions->canListCharts()) {
            return false;
        }

        $allowed = $permissions->getAllowedChartIds();

        return $allowed === null || in_array($chartId, $allowed, true);
    }
}

if (! function_exists('wdtmcp_can_list_tables')) {
    /**
     * @param mixed $input
     * @return bool
     */
    function wdtmcp_can_list_tables($input = null): bool
    {
        return wdtmcp_can_execute_ability('wpdatatables/list-tables', $input);
    }
}

if (! function_exists('wdtmcp_can_create_tables')) {
    /**
     * @param mixed $input
     * @return bool
     */
    function wdtmcp_can_create_tables($input = null): bool
    {
        return wdtmcp_can_execute_ability('wpdatatables/create-table-from-source', $input);
    }
}

if (! function_exists('wdtmcp_can_edit_table')) {
    /**
     * @param mixed $input
     * @return bool
     */
    function wdtmcp_can_edit_table($input = null): bool
    {
        return wdtmcp_can_execute_ability('wpdatatables/edit-table', $input);
    }
}

if (! function_exists('wdtmcp_can_list_charts')) {
    /**
     * @param mixed $input
     * @return bool
     */
    function wdtmcp_can_list_charts($input = null): bool
    {
        return wdtmcp_can_execute_ability('wpdatatables/list-charts', $input);
    }
}

if (! function_exists('wdtmcp_can_create_charts')) {
    /**
     * @param mixed $input
     * @return bool
     */
    function wdtmcp_can_create_charts($input = null): bool
    {
        return wdtmcp_can_execute_ability('wpdatatables/create-chart', $input);
    }
}

if (! function_exists('wdtmcp_can_edit_chart')) {
    /**
     * @param mixed $input
     * @return bool
     */
    function wdtmcp_can_edit_chart($input = null): bool
    {
        return wdtmcp_can_execute_ability('wpdatatables/edit-chart', $input);
    }
}

if (! function_exists('wdtmcp_filter_allowed_table_rows')) {
    /**
     * Filter raw table rows to the current user's browse allow-list.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    function wdtmcp_filter_allowed_table_rows(array $rows): array
    {
        $permissions = wdtmcp_permissions_service();
        if (! $permissions) {
            return $rows;
        }

        $allowed = $permissions->getAllowedTableIds();
        if ($allowed === null) {
            return $rows;
        }

        if ($allowed === array()) {
            return array();
        }

        $allowedMap = array_fill_keys($allowed, true);

        return array_values(
            array_filter(
                $rows,
                static function ($row) use ($allowedMap) {
                    $id = isset($row['id']) ? (int) $row['id'] : 0;

                    return $id > 0 && isset($allowedMap[$id]);
                }
            )
        );
    }
}

if (! function_exists('wdtmcp_filter_allowed_chart_rows')) {
    /**
     * Filter raw chart rows to the current user's browse allow-list.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    function wdtmcp_filter_allowed_chart_rows(array $rows): array
    {
        $permissions = wdtmcp_permissions_service();
        if (! $permissions) {
            return $rows;
        }

        $allowed = $permissions->getAllowedChartIds();
        if ($allowed === null) {
            return $rows;
        }

        if ($allowed === array()) {
            return array();
        }

        $allowedMap = array_fill_keys($allowed, true);

        return array_values(
            array_filter(
                $rows,
                static function ($row) use ($allowedMap) {
                    $id = isset($row['id']) ? (int) $row['id'] : 0;

                    return $id > 0 && isset($allowedMap[$id]);
                }
            )
        );
    }
}

if (! function_exists('wdtmcp_permission_input_id')) {
    /**
     * @param mixed        $input
     * @param list<string> $keys
     * @return int
     */
    function wdtmcp_permission_input_id($input, array $keys): int
    {
        if (! is_array($input)) {
            return 0;
        }

        foreach ($keys as $key) {
            if (isset($input[$key])) {
                return (int) $input[$key];
            }
        }

        return 0;
    }
}
