<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin\Admin;

use WPDataTables\Services\Permissions\AdminMenuPermissionMapper;
use WPDataTables\Services\Permissions\WpdtAccess;

/**
 * AdminMenu — registers the wpDataTables admin menu + submenu pages (the
 * `admin_menu` callback).
 *
 * Page-render callbacks point at layered admin controllers. Legacy global
 * `wdt*` page functions remain as BC delegators in
 * {@see \WPDataTables\Legacy\GlobalFunctions}.
 *
 * Submenus that delegated managers may use register with
 * {@see WpdtAccess::CAP_ACCESS_PLUGIN}; Settings / System info / Permissions
 * stay on `manage_options`. Unauthorized items are omitted via
 * {@see AdminMenuPermissionMapper}.
 *
 * @package WPDataTables\Plugin\Admin
 */
class AdminMenu
{
    /** @var AdminPageController */
    private $adminPageController;

    /** @var TablesPageController */
    private $tablesPageController;

    /** @var ChartsPageController */
    private $chartsPageController;

    /** @var PermissionsPageController */
    private $permissionsPageController;

    /** @var AdminMenuPermissionMapper */
    private $menuPermissionMapper;

    public function __construct(
        AdminPageController $adminPageController,
        TablesPageController $tablesPageController,
        ChartsPageController $chartsPageController,
        PermissionsPageController $permissionsPageController,
        AdminMenuPermissionMapper $menuPermissionMapper
    ) {
        $this->adminPageController = $adminPageController;
        $this->tablesPageController = $tablesPageController;
        $this->chartsPageController = $chartsPageController;
        $this->permissionsPageController = $permissionsPageController;
        $this->menuPermissionMapper = $menuPermissionMapper;
    }

    /**
     * Add submenus and menu options (the `admin_menu` callback).
     *
     * @return void
     */
    public function register()
    {
        if (!WpdtAccess::hasImplicitElevatedAccess() && !WpdtAccess::currentUserCanAccessPlugin()) {
            return;
        }

        $parentCap = WpdtAccess::hasImplicitElevatedAccess()
            ? 'manage_options'
            : WpdtAccess::CAP_ACCESS_PLUGIN;

        add_menu_page(
            'wpDataTables',
            'wpDataTables',
            $parentCap,
            'wpdatatables-dashboard',
            array($this->adminPageController, 'renderDashboard'),
            'none'
        );

        $this->maybeAddSubmenu(
            'wpdatatables-dashboard',
            __('Dashboard', 'wpdatatables'),
            __('Dashboard', 'wpdatatables'),
            'wpdatatables-dashboard',
            array($this->adminPageController, 'renderDashboard')
        );
        $this->maybeAddSubmenu(
            'wpdatatables-dashboard',
            __('wpDataTables', 'wpdatatables'),
            __('wpDataTables', 'wpdatatables'),
            'wpdatatables-administration',
            array($this->tablesPageController, 'renderBrowseTables')
        );
        $this->maybeAddSubmenu(
            'wpdatatables-dashboard',
            __('Create a Table', 'wpdatatables'),
            __('Create a Table', 'wpdatatables'),
            'wpdatatables-constructor',
            array($this->tablesPageController, 'renderConstructor')
        );
        $this->maybeAddSubmenu(
            'wpdatatables-dashboard',
            __('wpDataCharts', 'wpdatatables'),
            __('wpDataCharts', 'wpdatatables'),
            'wpdatatables-charts',
            array($this->chartsPageController, 'renderBrowseCharts')
        );
        $this->maybeAddSubmenu(
            'wpdatatables-dashboard',
            __('Create a Chart', 'wpdatatables'),
            __('Create a Chart', 'wpdatatables'),
            'wpdatatables-chart-wizard',
            array($this->chartsPageController, 'renderChartWizard')
        );
        $this->maybeAddSubmenu(
            'wpdatatables-dashboard',
            __('Settings', 'wpdatatables'),
            __('Settings', 'wpdatatables'),
            'wpdatatables-settings',
            array($this->adminPageController, 'renderSettings')
        );
        $this->maybeAddSubmenu(
            'wpdatatables-dashboard',
            __('System info', 'wpdatatables'),
            __('System info', 'wpdatatables'),
            'wpdatatables-system-info',
            array($this->adminPageController, 'renderSystemInfo')
        );
        $this->maybeAddSubmenu(
            'wpdatatables-dashboard',
            __('Permissions', 'wpdatatables'),
            __('Permissions', 'wpdatatables'),
            'wpdatatables_permissions',
            array($this->permissionsPageController, 'renderPage')
        );
        $this->maybeAddSubmenu(
            get_option('wdtGettingStartedPageStatus') ? '' : 'wpdatatables-dashboard',
            __('Getting Started', 'wpdatatables'),
            __('Getting Started', 'wpdatatables'),
            'wpdatatables-getting-started',
            array($this->adminPageController, 'renderGettingStarted')
        );
        $this->maybeAddSubmenu(
            'wpdatatables-dashboard',
            __('Get Help', 'wpdatatables'),
            __('Get Help', 'wpdatatables'),
            'wpdatatables-support',
            array($this->adminPageController, 'renderSupport')
        );
        $this->maybeAddSubmenu(
            'wpdatatables-dashboard',
            __('Add-ons', 'wpdatatables'),
            '<span style="color: #ff8c00">' . __('Addons', 'wpdatatables') . '</span>',
            'wpdatatables-add-ons',
            array($this->adminPageController, 'renderAddOns')
        );

        $folderPathDev = WDT_DEVELOPER_INTEGRATIONS_PATH;

        if (!is_dir($folderPathDev)) {
            $this->maybeAddSubmenu(
                'wpdatatables-dashboard',
                __('Upgrade', 'wpdatatables'),
                '<span style="color: #ffffff; font-weight: bold;">' . __('Upgrade', 'wpdatatables') . '</span>',
                'wpdatatables-upgrade',
                array($this->adminPageController, 'renderLiteVSPremium')
            );
        }
        $this->maybeAddSubmenu(
            'wpdatatables-welcome-page',
            __('Welcome page', 'wpdatatables'),
            __('Welcome page', 'wpdatatables'),
            'wpdatatables-welcome-page',
            array($this->adminPageController, 'renderWelcomePage')
        );
    }

    /**
     * @param string        $parentSlug
     * @param string        $pageTitle
     * @param string        $menuTitle
     * @param string        $menuSlug
     * @param callable|array $callback
     * @return void
     */
    private function maybeAddSubmenu($parentSlug, string $pageTitle, string $menuTitle, string $menuSlug, $callback): void
    {
        if (!$this->menuPermissionMapper->currentUserCanAccessSlug($menuSlug)) {
            return;
        }

        add_submenu_page(
            $parentSlug,
            $pageTitle,
            $menuTitle,
            $this->menuPermissionMapper->capabilityForSlug($menuSlug),
            $menuSlug,
            $callback
        );
    }
}
