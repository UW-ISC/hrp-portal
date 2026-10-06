<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin;

use WPDataTables\Services\Tools\ToolsService;

/**
 * Envato / Melograno Store plugin update checker hooks (full build).
 *
 * @package WPDataTables\Plugin
 */
class PluginUpdateHooks
{
    /** @var string */
    private $pluginSlug;

    public function __construct()
    {
        $filePath = plugin_basename(WDT_ROOT_PATH . 'wpdatatables.php');
        $filePathArr = explode('/', $filePath);
        $this->pluginSlug = $filePathArr[0] . '/wpdatatables.php';
    }

    /**
     * @return void
     */
    public function register(): void
    {
        add_filter('pre_set_site_transient_update_plugins', array($this, 'checkUpdate'));
        add_filter('plugins_api', array($this, 'checkInfo'), 10, 3);
        add_action('in_plugin_update_message-' . $this->pluginSlug, array($this, 'addMessageOnPluginsPage'));
        add_filter('upgrader_pre_download', array($this, 'addMessageOnUpdate'), 10, 3);
    }

    /**
     * @param object $transient Update transient.
     * @return object
     */
    public function checkUpdate($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $updateData = get_transient('wdt_update_data');
        $currentTime = time();

        if (!$updateData || ($currentTime - $updateData['last_checked']) > DAY_IN_SECONDS) {
            $purchaseCode = get_option('wdtPurchaseCodeStore');
            $envatoTokenEmail = get_option('wdtEnvatoTokenEmail');

            $remoteInformation = ToolsService::getRemoteInformation('wpdatatables', $purchaseCode, $envatoTokenEmail);

            if ($remoteInformation) {
                $updateData = array(
                    'last_checked' => $currentTime,
                    'remote_info' => $remoteInformation,
                );
                set_transient('wdt_update_data', $updateData, DAY_IN_SECONDS);
            }
        }

        if (isset($updateData['remote_info']) && $this->isValidRemoteInfo($updateData['remote_info'])) {
            $remoteInfo = $updateData['remote_info'];
            if (version_compare(WDT_CURRENT_VERSION, $remoteInfo->new_version, '<')) {
                $transient->response[$this->pluginSlug] = $this->normalizeRemoteInfo($remoteInfo);
            }
        } elseif (isset($updateData['remote_info'])) {
            delete_transient('wdt_update_data');
        }

        return $transient;
    }

    /**
     * @param mixed  $response API response.
     * @param string $action   plugins_api action.
     * @param object $args     Request args.
     * @return mixed
     */
    public function checkInfo($response, $action, $args)
    {
        if ('plugin_information' !== $action) {
            return $response;
        }

        if (empty($args->slug)) {
            return $response;
        }

        if (in_array($args->slug, array($this->pluginSlug, 'wpdatatables'), true)) {
            $updateData = get_transient('wdt_update_data');

            if ($updateData && isset($updateData['remote_info']) && $this->isValidRemoteInfo($updateData['remote_info'])) {
                return $updateData['remote_info'];
            }

            $purchaseCode = get_option('wdtPurchaseCodeStore');
            $envatoTokenEmail = get_option('wdtEnvatoTokenEmail');

            return ToolsService::getRemoteInformation('wpdatatables', $purchaseCode, $envatoTokenEmail);
        }

        return $response;
    }

    /**
     * @return void
     */
    public function addMessageOnPluginsPage(): void
    {
        $activated = get_option('wdtActivated');
        $url = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-settings&activeTab=activation';
        $redirect = '<a href="' . $url . '" target="_blank">' . esc_html__('settings', 'wpdatatables') . '</a>';

        if (!$activated) {
            echo sprintf(' ' . __('To receive automatic updates license activation is required. Please visit %s to activate wpDataTables.', 'wpdatatables'), $redirect);
        }
    }

    /**
     * @param mixed  $reply     Download reply.
     * @param mixed  $package   Package URL.
     * @param object $updater   Upgrader skin.
     * @return mixed
     */
    public function addMessageOnUpdate($reply, $package, $updater)
    {
        if (isset($updater->skin->plugin_info['Name']) && $updater->skin->plugin_info['Name'] === 'wpDataTables') {
            $url = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-settings&activeTab=activation';
            $redirect = '<a href="' . $url . '" target="_blank">' . esc_html__('settings', 'wpdatatables') . '</a>';

            if (!$package) {
                return new \WP_Error(
                    'wpdatatables_not_activated',
                    sprintf(' ' . __('To receive automatic updates license activation is required. Please visit %s to activate wpDataTables.', 'wpdatatables'), $redirect)
                );
            }

            return $reply;
        }

        return $reply;
    }

    /**
     * Ensure the update object carries the properties WordPress core expects on
     * items stored in the `update_plugins` transient. Without `plugin` (and
     * `slug`) core helpers such as wp_list_pluck() emit
     * "Undefined property: stdClass::$plugin" warnings in class-wp-list-util.php.
     *
     * @param object $remoteInfo Remote update metadata from the store API.
     * @return object
     */
    private function normalizeRemoteInfo($remoteInfo)
    {
        $normalized = clone $remoteInfo;

        if (empty($normalized->plugin)) {
            $normalized->plugin = $this->pluginSlug;
        }

        if (empty($normalized->slug)) {
            $normalized->slug = 'wpdatatables';
        }

        if (empty($normalized->id)) {
            $normalized->id = $this->pluginSlug;
        }

        return $normalized;
    }

    /**
     * @param mixed $remoteInfo Remote update metadata from the store API.
     * @return bool
     */
    private function isValidRemoteInfo($remoteInfo): bool
    {
        return is_object($remoteInfo)
            && !($remoteInfo instanceof \__PHP_Incomplete_Class)
            && isset($remoteInfo->new_version)
            && is_string($remoteInfo->new_version)
            && $remoteInfo->new_version !== '';
    }
}
