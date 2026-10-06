<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin\Admin;

use WPDataTables\Services\Permissions\WpdtAccess;

/**
 * AdminPageController — renders the simple, informational wpDataTables admin
 * pages: Dashboard, Settings, Support, Welcome, System Info, Getting Started,
 * Lite VS Premium, and Add-ons.
 *
 * Settings and System Info stay elevated (`manage_options`). Other informational
 * pages allow delegated managers with {@see WpdtAccess::CAP_ACCESS_PLUGIN}.
 *
 * @package WPDataTables\Plugin\Admin
 */
class AdminPageController
{
    /**
     * Render the Dashboard page.
     *
     * @return void
     */
    public function renderDashboard()
    {
        $this->assertCanAccessPluginPages();

        ob_start();
        include WDT_ROOT_PATH . 'templates/admin/dashboard/dashboard.inc.php';
        $dashboardPage = ob_get_contents();
        ob_end_clean();

        $dashboardPage = apply_filters('wpdatatables_filter_dashboard_page', $dashboardPage);
        echo $dashboardPage;

        do_action('wpdatatables_dashboard_page');
    }

    /**
     * Render the Settings page.
     *
     * @return void
     */
    public function renderSettings()
    {
        $this->assertElevatedOnly();

        ob_start();
        include WDT_ROOT_PATH . 'templates/admin/settings/settings.inc.php';
        $settingsPage = ob_get_contents();
        ob_end_clean();

        $settingsPage = apply_filters('wpdatatables_filter_settings_page', $settingsPage);
        echo $settingsPage;

        do_action('wpdatatables_settings_page');
    }

    /**
     * Render the Support Center page.
     *
     * @return void
     */
    public function renderSupport()
    {
        $this->assertCanAccessPluginPages();

        ob_start();
        include WDT_ROOT_PATH . 'templates/admin/support/support.inc.php';
        $settingsPage = ob_get_contents();
        ob_end_clean();

        $settingsPage = apply_filters('wpdatatables_filter_support_page', $settingsPage);
        echo $settingsPage;

        do_action('wpdatatables_support_page');
    }

    /**
     * Render the Welcome page.
     *
     * @return void
     */
    public function renderWelcomePage()
    {
        $this->assertCanAccessPluginPages();

        ob_start();
        include WDT_ROOT_PATH . 'templates/admin/welcome_page/welcome_page.inc.php';
        $welcomePage = ob_get_contents();
        ob_end_clean();

        $settingsPage = apply_filters('wpdatatables_filter_welcome_page', $welcomePage);
        echo $welcomePage;

        do_action('wpdatatables_welcome_page');
    }

    /**
     * Render the System Info page.
     *
     * @return void
     */
    public function renderSystemInfo()
    {
        $this->assertElevatedOnly();

        ob_start();
        include WDT_ROOT_PATH . 'templates/admin/system-info/system_info.inc.php';
        $settingsPage = ob_get_contents();
        ob_end_clean();

        $settingsPage = apply_filters('wpdatatables_filter_system_info_page', $settingsPage);
        echo $settingsPage;

        do_action('wpdatatables_system_info_page');
    }

    /**
     * Render the Getting Started page.
     *
     * @return void
     */
    public function renderGettingStarted()
    {
        $this->assertCanAccessPluginPages();

        ob_start();
        include WDT_ROOT_PATH . 'templates/admin/getting-started/getting_started.inc.php';
        $settingsPage = ob_get_contents();
        ob_end_clean();

        $settingsPage = apply_filters('wpdatatables_filter_getting_started_page', $settingsPage);
        echo $settingsPage;

        do_action('wpdatatables_getting_started_page');
    }

    /**
     * Render the Lite VS Premium page.
     *
     * @return void
     */
    public function renderLiteVSPremium()
    {
        $this->assertCanAccessPluginPages();

        ob_start();
        include WDT_ROOT_PATH . 'templates/admin/lite-vs-premium/lite_vs_premium.inc.php';
        $settingsPage = ob_get_contents();
        ob_end_clean();

        $settingsPage = apply_filters('wpdatatables_filter_lite_vs_premium_page', $settingsPage);
        echo $settingsPage;

        do_action('wpdatatables_lite_vs_premium_page');
    }

    /**
     * Render the Add-ons page.
     *
     * @return void
     */
    public function renderAddOns()
    {
        $this->assertCanAccessPluginPages();

        ob_start();
        include WDT_ROOT_PATH . 'templates/admin/addons/addons.inc.php';
        $addonsPage = ob_get_contents();
        ob_end_clean();

        $addonsPage = apply_filters('wpdatatables_filter_addons_page', $addonsPage);
        echo $addonsPage;
    }

    /**
     * @return void
     */
    private function assertElevatedOnly(): void
    {
        if (!WpdtAccess::hasImplicitElevatedAccess() && !current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
    }

    /**
     * @return void
     */
    private function assertCanAccessPluginPages(): void
    {
        if (WpdtAccess::hasImplicitElevatedAccess() || WpdtAccess::currentUserCanAccessPlugin()) {
            return;
        }

        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
}
