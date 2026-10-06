<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Admin;

/**
 * Admin promo banners and rating notices on wpDataTables admin pages.
 *
 * @package WPDataTables\Services\Admin
 */
class AdminNoticeService
{
    /**
     * Whether the IvyForms promo banner should render for the current user.
     *
     * @return bool
     */
    public function shouldShowIvyFormsPromoForCurrentUser(): bool
    {
        if (get_option('wdtShowIvyFormsBanner', 'yes') != 'yes') {
            return false;
        }

        if ($this->isIvyFormsPluginActive()) {
            return false;
        }

        $user_id = (int) get_current_user_id();
        if ($user_id && '1' === (string) get_user_meta($user_id, 'wdt_ivyforms_promo_dismissed', true)) {
            return false;
        }

        return true;
    }

    /**
     * Render admin notices on wpDataTables admin pages.
     *
     * @return void
     */
    public function renderAdminNotices(): void
    {
        global $wpdb;
        $query = "SELECT COUNT(*) FROM {$wpdb->prefix}wpdatatables ORDER BY id";

        $allTables = $wpdb->get_var($query);

        $installDate = get_option('wdtInstallDate');
        $currentDate = date('Y-m-d');
        $tempIgnoreDate = get_option('wdtTempFutureDate');
        $wpdtPage = (isset($_GET['page']) && is_string($_GET['page'])) ? $_GET['page'] : '';
        $urlAddonsPage = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-add-ons';

        $tempIgnore = strtotime($currentDate) >= strtotime($tempIgnoreDate) ? true : false;
        $datetimeInstallDate = new \DateTime($installDate);
        $datetimeCurrentDate = new \DateTime($currentDate);
        $diffIntrval = round(($datetimeCurrentDate->format('U') - $datetimeInstallDate->format('U')) / (60 * 60 * 24));

        if (is_admin() && strpos($wpdtPage, 'wpdatatables') !== false && get_option('wdtHighchartsCdnNotice') == 'yes') {
            echo '<div class="notice notice-info is-dismissible wpdt-highcharts-cdn-notice">
            <p class="wpdt-highcharts-cdn"><strong style="color: #ffa63a; font-size: 16px;">Upcoming change! </strong> Highcharts CDN usage will change in the next update. Scripts will no longer be loaded via CDN, only the stable version will be included.</p>
        </div>';
        }

        if (is_admin() && strpos($wpdtPage, 'wpdatatables') !== false &&
            get_option('wdtBootstrapUpdateNotice') == 'yes') {
            echo '<div class="notice notice-info is-dismissible wpdt-bootstrap-update-notice">
             <p class="wpdt-bootstrap-update"><strong>Coming Soon!</strong> wpDataTables will <strong>update Bootstrap JS framework</strong> for improved performance and enhanced functionality.</p>
         </div>';
        }

        if (is_admin() && strpos($wpdtPage, 'wpdatatables') !== false &&
            $diffIntrval >= 14 && get_option('wdtRatingDiv') == 'no' && $tempIgnore && isset($allTables) && $allTables > 5) {
            include WDT_TEMPLATE_PATH . 'admin/common/ratingDiv.inc.php';
        }

        if (is_admin() && strpos($wpdtPage, 'wpdatatables') !== false && get_option('wdtMDNewsDiv') == 'no') {
            echo '<div class="notice notice-info is-dismissible wpdt-md-news-notice">
             <p class="wpdt-md-news">NEWS! wpDataTables just launched a new addon - Master-Detail Tables. You can find it in the <a href="' . esc_url($urlAddonsPage) . '">Addons page</a>, read more about it in our docs on this <a rel="nofollow" target="_blank" href="https://wpdatatables.com/documentation/addons/master-detail-tables/">link</a>.</p>
         </div>';
        }

        if (is_admin() && strpos($wpdtPage, 'wpdatatables') !== false &&
            get_option('wdtShowForminatorNotice') == 'yes' && defined('FORMINATOR_PLUGIN_BASENAME')
            && !defined('WDT_FRF_ROOT_PATH')) {
            echo '<div class="notice notice-info is-dismissible wpdt-forminator-news-notice">
             <p class="wpdt-forminator-news"><strong style="color: #ff8c00">NEWS!</strong> wpDataTables just launched a new <strong style="color: #ff8c00">FREE</strong> addon - <strong style="color: #ff8c00">wpDataTables integration for Forminator Forms</strong>. You can download it and read more about it on wp.org on this <a class="wdt-forminator-link" href="https://wordpress.org/plugins/wpdatatables-forminator/" style="color: #ff8c00" target="_blank">link</a>.</p>
         </div>';
        }

        if (is_admin() && strpos($wpdtPage, 'wpdatatables') !== false && !($wpdtPage == 'wpdatatables-add-ons') &&
            get_option('wdtShowBundlesNotice') == 'yes') {
            include WDT_TEMPLATE_PATH . 'admin/common/bundles_banner.inc.php';
            wp_enqueue_style('wdt-bundles-css', WDT_CSS_PATH . 'admin/bundles.css');
        }

        if (is_admin() && (strpos($wpdtPage, 'wpdatatables-dashboard') !== false) &&
            get_option('wdtShowAmeliaBanner') == 'yes' && $this->installedPluginsAmeliaPromotion()) {
            include WDT_TEMPLATE_PATH . 'admin/common/promote_amelia.php';
            wp_enqueue_style('wdt-promo-css', WDT_CSS_PATH . 'admin/amelia_promo_banner.css');
        }

        if (is_admin() && strpos($wpdtPage, 'wpdatatables') !== false &&
            $this->shouldShowIvyFormsPromoForCurrentUser()) {
            include WDT_TEMPLATE_PATH . 'admin/common/promote_ivyforms.php';
        }
    }

    /**
     * Enqueue IvyForms promo assets when the banner is eligible.
     *
     * @return void
     */
    public function enqueueIvyFormsPromoAssets(): void
    {
        if (!is_admin()) {
            return;
        }

        $wpdt_page = (isset($_GET['page']) && is_string($_GET['page'])) ? $_GET['page'] : '';
        if (strpos($wpdt_page, 'wpdatatables') === false) {
            return;
        }

        if (!$this->shouldShowIvyFormsPromoForCurrentUser()) {
            return;
        }

        wp_enqueue_style('wdt-ivyforms-promo-css', WDT_CSS_PATH . 'admin/ivyforms_promo_banner.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_script('wdt-ivyforms-promo', WDT_JS_PATH . 'wpdatatables/admin/wdtIvyformsPromo.js', array('jquery'), WDT_CURRENT_VERSION, true);
        wp_localize_script(
            'wdt-ivyforms-promo',
            'wdtIvyformsPromo',
            array(
                'install_failed' => __('Install failed.', 'wpdatatables'),
            )
        );
    }

    /**
     * Whether IvyForms is active.
     *
     * @return bool
     */
    public function isIvyFormsPluginActive(): bool
    {
        if (!class_exists('IvyForms\Services\API\IvyFormsAPI')) {
            return false;
        }

        if (!method_exists('IvyForms\Services\API\IvyFormsAPI', 'isPluginActive')) {
            return false;
        }

        return \IvyForms\Services\API\IvyFormsAPI::isPluginActive();
    }

    /**
     * Whether Amelia promo should show based on competing booking plugins.
     *
     * @return bool
     */
    public function installedPluginsAmeliaPromotion(): bool
    {
        $plugins_to_check = array(
            'bookly-responsive-appointment-booking-tool/main.php',
            'bookingpress-appointment-booking/bookingpress-appointment-booking.php',
            'latepoint-manager/latepoint_manager.php',
            'meeting-scheduler-by-vcita/vcita-scheduler.php',
            'wp-event-manager/wp-event-manager.php',
        );

        $installed_plugins = array();
        $installed_plugins_not_active = array();

        foreach ($plugins_to_check as $plugin) {
            if (is_plugin_active($plugin)) {
                $installed_plugins[] = $plugin;
            }
            if ($this->isPluginInstalled($plugin)) {
                $installed_plugins_not_active[] = $plugin;
            }
        }

        if (!empty($installed_plugins)) {
            if (is_plugin_inactive('ameliabooking/ameliabooking.php')) {
                return true;
            }
        }
        if (!empty($installed_plugins_not_active)) {
            if (!$this->isPluginInstalled('ameliabooking/ameliabooking.php')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $plugin_path Plugin basename path.
     * @return bool
     */
    public function isPluginInstalled($plugin_path): bool
    {
        $plugin_file = WP_PLUGIN_DIR . '/' . $plugin_path;

        return file_exists($plugin_file);
    }
}
