<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Admin;

use WPDataTables\Services\Tools\ToolsService;

/**
 * Deactivation feedback modal admin-ajax + asset enqueue.
 *
 * @package WPDataTables\Controllers\Admin
 */
class DeactivationFeedbackController
{
    /**
     * Save deactivation feedback from the plugins screen modal.
     *
     * @return void
     */
    public function saveDeactivationInfo(): void
    {
        if (!is_admin() || !wp_verify_nonce($_POST['wdtNonce'], 'wdtDeactivationNonce')) {
            wp_send_json_error();
        }

        if (!current_user_can('activate_plugins')) {
            wp_send_json_error();
        }

        $reason = $this->getSuperGlobalValue($_POST, 'choice') ?? '';
        $reason_caption = $this->getSuperGlobalValue($_POST, 'textareaDescription') ?? '';

        $reason = sanitize_key($reason);
        $reason_caption = sanitize_textarea_field($reason_caption);

        \WPDataTablesFeedback::wdtSendFeedback($reason, $reason_caption);

        wp_send_json_success();
    }

    /**
     * Enqueue the deactivation modal script on the plugins screen.
     *
     * @return void
     */
    public function enqueueDeactivationModal(): void
    {
        if ((strpos($_SERVER['REQUEST_URI'], 'plugins.php') !== false)) {
            wp_enqueue_script('wdt-deactivate-info-js', WDT_ROOT_URL . 'assets/js/deactivation/deactivation-modal.js', array('jquery', 'wp-i18n'), WDT_CURRENT_VERSION, true);
            wp_localize_script('wdt-deactivate-info-js', 'wpdatatables_deactivate_info', ToolsService::getDeactivationInfo());
            wp_set_script_translations('wdt-deactivate-info-js', 'wpdatatables', WDT_ROOT_PATH . 'languages');
        }
    }

    /**
     * @param array  $super_global Source superglobal.
     * @param string $key          Key to read.
     * @return mixed|null
     */
    private function getSuperGlobalValue($super_global, $key)
    {
        if (!isset($super_global[$key])) {
            return null;
        }

        if ($_FILES === $super_global) {
            return isset($super_global[$key]['name']) ?
                sanitize_file_name($super_global[$key]) :
                $this->sanitizeMultiUpload($super_global[$key]);
        }

        return wp_kses_post_deep(wp_unslash($super_global[$key]));
    }

    /**
     * @param array $fields Uploaded file field names.
     * @return array
     */
    private function sanitizeMultiUpload($fields): array
    {
        return array_map(function ($field) {
            return array_map('sanitize_file_name', $field);
        }, $fields);
    }
}
