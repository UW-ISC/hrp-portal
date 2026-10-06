<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Permissions;

use WPDataTables\Common\Exceptions\ValidationException;
use WPDataTables\Services\Permissions\PermissionsAdminService;
use WPDataTables\Services\Permissions\WpdtAccess;

/**
 * PermissionsController — admin-ajax handlers for the permissions admin screen.
 *
 * The nonce/cap checks and the wire format ($_POST in, `wp_send_json_*` out)
 * match what {@see permissions-admin.js} expects. Wiring lives in
 * {@see \WPDataTables\Plugin\AjaxHooks}.
 *
 * @package WPDataTables\Controllers\Permissions
 */
class PermissionsController
{
    /** @var PermissionsAdminService */
    private PermissionsAdminService $permissionsAdminService;

    public function __construct(PermissionsAdminService $permissionsAdminService)
    {
        $this->permissionsAdminService = $permissionsAdminService;
    }

    /**
     * @return void
     */
    public function loadPermissions(): void
    {
        if (!$this->authorizeRequest()) {
            return;
        }

        $tab    = $this->getTabParam();
        $search = isset($_POST['s']) ? sanitize_text_field(wp_unslash($_POST['s'])) : '';

        wp_send_json_success(array(
            'html' => $this->permissionsAdminService->getPermissionsListHtml($tab, $search),
        ));
    }

    /**
     * @return void
     */
    public function savePermission(): void
    {
        if (!$this->authorizeRequest()) {
            return;
        }

        $tab             = $this->getTabParam();
        $targetType      = isset($_POST['target_type']) ? sanitize_key(wp_unslash($_POST['target_type'])) : '';
        $roleSlugs       = $this->getStringListParam('role_slugs');
        $userIds         = $this->getIntListParam('user_ids');
        $permissions     = $this->getStringListParam('permissions');
        $enableSpecific  = isset($_POST['enable_specific']) ? absint($_POST['enable_specific']) : 0;
        $itemIds         = $this->getIntListParam('item_ids');

        // Backward-compatible single user_id from older clients.
        if ($targetType === '' && isset($_POST['user_id'])) {
            $targetType = 'user';
            $userIds    = array(absint($_POST['user_id']));
        }

        try {
            $result = $this->permissionsAdminService->savePermission(
                $tab,
                $targetType,
                $roleSlugs,
                $userIds,
                $permissions,
                $enableSpecific,
                $itemIds
            );
        } catch (ValidationException $e) {
            wp_send_json_error(array('message' => $e->getMessage()));

            return;
        }

        wp_send_json_success(array(
            'message' => __('Permission saved successfully', 'wpdatatables'),
            'created' => $result['created'],
        ));
    }

    /**
     * @return void
     */
    public function updatePermission(): void
    {
        if (!$this->authorizeRequest()) {
            return;
        }

        $tab            = $this->getTabParam();
        $ruleId         = isset($_POST['rule_id']) ? sanitize_text_field(wp_unslash($_POST['rule_id'])) : '';
        $permissions    = $this->getStringListParam('permissions');
        $enableSpecific = isset($_POST['enable_specific']) ? absint($_POST['enable_specific']) : 0;
        $itemIds        = $this->getIntListParam('item_ids');

        if ($ruleId === '') {
            wp_send_json_error(array('message' => __('Invalid data', 'wpdatatables')));

            return;
        }

        try {
            $ok = $this->permissionsAdminService->updatePermission(
                $tab,
                $ruleId,
                $permissions,
                $enableSpecific,
                $itemIds
            );
        } catch (ValidationException $e) {
            wp_send_json_error(array('message' => $e->getMessage()));

            return;
        }

        if (!$ok) {
            wp_send_json_error(array('message' => __('Permission rule not found', 'wpdatatables')));

            return;
        }

        wp_send_json_success(array('message' => __('Permission updated successfully', 'wpdatatables')));
    }

    /**
     * @return void
     */
    public function deletePermission(): void
    {
        if (!$this->authorizeRequest()) {
            return;
        }

        $tab    = $this->getTabParam();
        $ruleId = isset($_POST['rule_id']) ? sanitize_text_field(wp_unslash($_POST['rule_id'])) : '';

        // Backward-compatible: older UI sent user_id.
        if ($ruleId === '' && isset($_POST['user_id'])) {
            wp_send_json_error(array('message' => __('Invalid data', 'wpdatatables')));

            return;
        }

        if ($ruleId === '') {
            wp_send_json_error(array('message' => __('Invalid data', 'wpdatatables')));

            return;
        }

        try {
            $ok = $this->permissionsAdminService->deletePermission($tab, $ruleId);
        } catch (ValidationException $e) {
            wp_send_json_error(array('message' => $e->getMessage()));

            return;
        }

        if (!$ok) {
            wp_send_json_error(array('message' => __('Permission rule not found', 'wpdatatables')));

            return;
        }

        wp_send_json_success(array('message' => __('Permission deleted successfully', 'wpdatatables')));
    }

    /**
     * @return void
     */
    public function getPermission(): void
    {
        if (!$this->authorizeRequest()) {
            return;
        }

        $tab    = $this->getTabParam();
        $ruleId = isset($_POST['rule_id']) ? sanitize_text_field(wp_unslash($_POST['rule_id'])) : '';

        if ($ruleId === '') {
            wp_send_json_error(array('message' => __('Invalid data', 'wpdatatables')));

            return;
        }

        $result = $this->permissionsAdminService->getPermission($tab, $ruleId);
        if (null === $result) {
            wp_send_json_error(array('message' => __('Permission rule not found', 'wpdatatables')));

            return;
        }

        wp_send_json_success($result);
    }

    /**
     * Roles list + catalog labels + tables/charts for selects.
     *
     * @return void
     */
    public function getPermissionsMeta(): void
    {
        if (!$this->authorizeRequest()) {
            return;
        }

        $tab = $this->getTabParam();
        wp_send_json_success($this->permissionsAdminService->getPermissionsMeta($tab));
    }

    /**
     * Optional remote user search (IvyForms parity).
     *
     * @return void
     */
    public function searchPermissionUsers(): void
    {
        if (!$this->authorizeRequest()) {
            return;
        }

        $search = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        wp_send_json_success(array(
            'users' => $this->permissionsAdminService->searchPermissionUsers($search),
        ));
    }

    /**
     * @return bool
     */
    private function authorizeRequest(): bool
    {
        check_ajax_referer('wdt_permissions_nonce', 'nonce');

        if (!WpdtAccess::currentUserCanAccessPermissionsSettings()) {
            wp_send_json_error(array('message' => __('Unauthorized', 'wpdatatables')));

            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    private function getTabParam(): string
    {
        $tab = isset($_POST['tab']) ? sanitize_text_field(wp_unslash($_POST['tab'])) : 'tables';

        return $tab === 'charts' ? 'charts' : 'tables';
    }

    /**
     * @param string $key
     * @return list<string>
     */
    private function getStringListParam(string $key): array
    {
        if (!isset($_POST[$key])) {
            return array();
        }

        $raw = wp_unslash($_POST[$key]);
        if (!is_array($raw)) {
            $raw = array($raw);
        }

        $out = array();
        foreach ($raw as $value) {
            $value = sanitize_text_field((string) $value);
            if ($value !== '') {
                $out[] = $value;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param string $key
     * @return list<int>
     */
    private function getIntListParam(string $key): array
    {
        if (!isset($_POST[$key])) {
            return array();
        }

        $raw = wp_unslash($_POST[$key]);
        if (!is_array($raw)) {
            $raw = array($raw);
        }

        $out = array();
        foreach ($raw as $value) {
            $id = absint($value);
            if ($id > 0) {
                $out[$id] = true;
            }
        }

        return array_map('intval', array_keys($out));
    }
}
