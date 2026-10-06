<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin\Admin;

use WPDataTables\Services\Permissions\WpdtAccess;

/**
 * PermissionsPageController — renders the wpDataTables Permissions admin screen.
 *
 * Registered as an `add_submenu_page` callback by {@see AdminMenu} via the
 * legacy global `wdtPermissions()` delegator in `controllers/wdt_admin.php`.
 *
 * @package WPDataTables\Plugin\Admin
 */
class PermissionsPageController
{
    /**
     * Render the Permissions admin page.
     *
     * @return void
     */
    public function renderPage(): void
    {
        if (!WpdtAccess::currentUserCanAccessPermissionsSettings()) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpdatatables'));
        }

        $activeTab = isset($_GET['tab']) && 'charts' === sanitize_text_field(wp_unslash($_GET['tab']))
            ? 'charts'
            : 'tables';

        $permissionsTable = new \WDTPermissionsListTable(array('type' => $activeTab));
        $permissionsTable->prepare_items();

        include WDT_ROOT_PATH . 'templates/admin/permissions/permissions.inc.php';
    }
}
