<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin;

use WPDataTables\Plugin\Admin\AdminMenu;
use WPDataTables\Plugin\Admin\AdminAssets;
use WPDataTables\Services\Permissions\PermissionsAdminService;
use WPDataTables\Controllers\Admin\DeactivationFeedbackController;

/**
 * AdminHooks — the single place where the wpDataTables admin-screen WordPress
 * glue is wired (menu registration and admin asset enqueuing). The admin-screen
 * analog of {@see FrontendHooks} (shortcodes/widget) and {@see AjaxHooks}
 * (`wp_ajax_*`).
 *
 * Fired once from {@see Plugin} after the container is built. The admin hooks
 * (`admin_menu`, `admin_enqueue_scripts`, …) only fire in an admin-request
 * context, so registering them on every request is behaviourally identical to
 * admin-only registration — exactly the reasoning {@see AjaxHooks} uses for
 * `wp_ajax_*`. The legacy global functions remain one-line delegators.
 *
 * @package WPDataTables\Plugin
 */
class AdminHooks
{
    /** @var AdminMenu */
    private $adminMenu;

    /** @var AdminAssets */
    private $adminAssets;

    /** @var PermissionsAdminService */
    private $permissionsAdminService;

    /** @var DeactivationFeedbackController */
    private $deactivationFeedbackController;

    public function __construct(
        AdminMenu $adminMenu,
        AdminAssets $adminAssets,
        PermissionsAdminService $permissionsAdminService,
        DeactivationFeedbackController $deactivationFeedbackController
    ) {
        $this->adminMenu = $adminMenu;
        $this->adminAssets = $adminAssets;
        $this->permissionsAdminService = $permissionsAdminService;
        $this->deactivationFeedbackController = $deactivationFeedbackController;
    }

    /**
     * Wire the admin-screen hooks. Called once from Plugin after the container
     * is built.
     *
     * @return void
     */
    public function register()
    {
        // The wpDataTables menu + submenu pages.
        add_action('admin_menu', array($this->adminMenu, 'register'));

        // Admin CSS/JS enqueuing (+ the Gravity-tooltip deregister on the
        // wpDataTables admin-page enqueue hook).
        add_action('admin_enqueue_scripts', array($this->adminAssets, 'enqueueAdmin'));
        add_action('wpdatatables_enqueue_on_admin_pages', array($this->adminAssets, 'deregisterGravityTooltipScript'));
        add_action('admin_enqueue_scripts', array($this->deactivationFeedbackController, 'enqueueDeactivationModal'));

        // wpDataTables custom capabilities (role editors + administrator defaults).
        add_action('init', array($this->permissionsAdminService, 'registerCapabilities'), 1);
    }
}
