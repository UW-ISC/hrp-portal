<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Rest;

use WP_REST_Request;
use WPDataTables\Plugin\Plugin;
use WPDataTables\Services\Permissions\PermissionsService;
use WPDataTables\Services\Permissions\WpdtAccess;

/**
 * Capability gate for the wpDataTables admin REST API.
 *
 * Admin REST (`wpdatatables/v1`) registers from {@see \WPDataTables\Plugin\Plugin}.
 * Authentication is WP-native; these helpers back `permission_callback` and
 * resolve through {@see PermissionsService}.
 *
 * @package WPDataTables\Common\Rest
 */
class RestPermissions
{
    /** @deprecated Use action-aware helpers; kept for BC filters. */
    const MANAGE_CAPABILITY = 'manage_options';

    /**
     * Legacy blanket gate (Settings and BC). Prefer action-aware methods.
     *
     * @return bool
     */
    public static function canManageTables()
    {
        /**
         * Filter the capability required for wpDataTables REST manage routes.
         *
         * @since 7.x
         * @param string $capability Default `manage_options`.
         */
        $capability = apply_filters('wpdatatables/rest/manage_capability', self::MANAGE_CAPABILITY);

        return current_user_can($capability);
    }

    /**
     * @return bool
     */
    public static function canListTables(): bool
    {
        return self::permissions()->canListTables();
    }

    /**
     * @return bool
     */
    public static function canCreateTables(): bool
    {
        return self::permissions()->canCreateTables();
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public static function canEditTableRequest(WP_REST_Request $request): bool
    {
        return self::permissions()->canEditTable(self::requestItemId($request));
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public static function canDeleteTableRequest(WP_REST_Request $request): bool
    {
        return self::permissions()->canDeleteTable(self::requestItemId($request));
    }

    /**
     * Read table config/data (edit for the item, or list within browse scope).
     *
     * Mirrors MCP {@see wdtmcp_can_read_table()}.
     *
     * @param WP_REST_Request $request
     * @return bool
     */
    public static function canReadTableRequest(WP_REST_Request $request): bool
    {
        $tableId = self::requestItemId($request);
        $permissions = self::permissions();

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

    /**
     * @return bool
     */
    public static function canListCharts(): bool
    {
        return self::permissions()->canListCharts();
    }

    /**
     * @return bool
     */
    public static function canCreateCharts(): bool
    {
        return self::permissions()->canCreateCharts();
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public static function canEditChartRequest(WP_REST_Request $request): bool
    {
        return self::permissions()->canEditChart(self::requestItemId($request));
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public static function canDeleteChartRequest(WP_REST_Request $request): bool
    {
        return self::permissions()->canDeleteChart(self::requestItemId($request));
    }

    /**
     * Read chart config (edit for the item, or list within browse scope).
     *
     * Mirrors MCP {@see wdtmcp_can_read_chart()}.
     *
     * @param WP_REST_Request $request
     * @return bool
     */
    public static function canReadChartRequest(WP_REST_Request $request): bool
    {
        $chartId = self::requestItemId($request);
        $permissions = self::permissions();

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

    /**
     * Settings REST stays elevated-admin only.
     *
     * @return bool
     */
    public static function canAccessSettings(): bool
    {
        return WpdtAccess::hasImplicitElevatedAccess() || self::canManageTables();
    }

    /**
     * Whether the current request may use the AI endpoints.
     *
     * AI is an admin-only feature on every licence tier (unlike the rest of the
     * REST API, which is Developer-tier-gated). The capability is the same
     * `manage_options` gate every wpDataTables admin surface uses, exposed under
     * its own filter so it can be tightened independently of the table routes.
     *
     * @return bool
     */
    public static function canUseAi()
    {
        /**
         * Filter the capability required for wpDataTables AI REST routes.
         *
         * @since 7.x
         * @param string $capability Default `manage_options`.
         */
        $capability = apply_filters('wpdatatables/rest/ai_capability', self::MANAGE_CAPABILITY);

        return current_user_can($capability);
    }

    /**
     * @param WP_REST_Request $request
     * @return int
     */
    private static function requestItemId(WP_REST_Request $request): int
    {
        return absint($request->get_param('id'));
    }

    /**
     * @return PermissionsService
     */
    private static function permissions(): PermissionsService
    {
        return Plugin::container()->get(PermissionsService::class);
    }
}
