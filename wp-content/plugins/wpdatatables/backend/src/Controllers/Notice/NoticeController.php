<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Notice;

/**
 * NoticeController — admin-ajax handlers for the admin notice / UI-state
 * dismissals (rating prompts, promo banners, "what's new" modals, etc.).
 *
 * The wire format ($_POST in, `echo json_encode(...)` / `wp_send_json_*` out,
 * then `exit`) is what the existing admin JS expects. These handlers are pure
 * WordPress glue (they only touch `update_option` / user-meta) and have no
 * backing service. The legacy global functions remain as one-line delegators
 * into this controller (public surface for third parties).
 *
 * Plain class by design (NOT extending {@see \WPDataTables\Controllers\Controller},
 * which is REST-shaped): admin-ajax controllers are ajax-shaped and converge with
 * REST at the service layer.
 *
 * @package WPDataTables\Controllers\Notice
 */
class NoticeController
{
    /**
     * Dismiss the "what's new" update modal.
     *
     * @return void
     */
    public function hideUpdateModal()
    {
        update_option('wdtHideUpdateModal', 1);
        echo json_encode(array("success"));
        exit;
    }

    /**
     * Permanently dismiss the rating prompt.
     *
     * @return void
     */
    public function hideRating()
    {
        update_option('wdtRatingDiv', 'yes');
        echo json_encode(array("success"));
        exit;
    }

    /**
     * Dismiss the Bootstrap update notice.
     *
     * @return void
     */
    public function removeBootstrapUpdateNotice()
    {
        update_option('wdtBootstrapUpdateNotice', 'no');
        echo json_encode(array("success"));
        exit;
    }

    /**
     * Dismiss the Forminator notice.
     *
     * @return void
     */
    public function removeForminatorNotice()
    {
        update_option('wdtShowForminatorNotice', 'no');
        echo json_encode(array("success"));
        exit;
    }

    /**
     * Dismiss the bundles notice.
     *
     * @return void
     */
    public function removeBundlesNotice()
    {
        update_option('wdtShowBundlesNotice', 'no');
        echo json_encode(array("success"));
        exit;
    }

    /**
     * Dismiss the Amelia promo banner.
     *
     * @return void
     */
    public function removeAmeliaPromoNotice()
    {
        update_option('wdtShowAmeliaBanner', 'no');
        echo json_encode(array("success"));
        exit;
    }

    /**
     * Permanently dismiss the IvyForms promo admin notice.
     *
     * @return void
     */
    public function removeIvyFormsPromoNotice()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wdt_ivyforms_promo_dismiss')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed.', 'wpdatatables')), 403);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Unauthorized', 'wpdatatables')), 403);
        }

        update_option('wdtShowIvyFormsBanner', 'no');
        wp_send_json_success();
    }

    /**
     * Dismiss the IvyForms promo for the current user only (stored in user meta;
     * no browser storage).
     *
     * @return void
     */
    public function dismissIvyFormsPromoUser()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wdt_ivyforms_promo_user_dismiss')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed.', 'wpdatatables')), 403);
        }

        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Unauthorized', 'wpdatatables')), 403);
        }

        update_user_meta(get_current_user_id(), 'wdt_ivyforms_promo_dismissed', '1');

        wp_send_json_success();
    }

    /**
     * Dismiss the Highcharts CDN notice.
     *
     * @return void
     */
    public function removeHighchartsCdnNotice()
    {
        update_option('wdtHighchartsCdnNotice', 'no');
        echo json_encode(array("success"));
        exit;
    }

    /**
     * Dismiss the simple-table alert.
     *
     * @return void
     */
    public function hideSimpleTableAlert()
    {
        update_option('wdtSimpleTableAlert', false);
        echo json_encode(array("success"));
        exit;
    }

    /**
     * Dismiss the Master-Detail news div.
     *
     * @return void
     */
    public function hideMDNewsDiv()
    {
        update_option('wdtMDNewsDiv', 'yes');
        echo json_encode(array("success"));
        exit;
    }

    /**
     * Temporarily hide the rating prompt for 7 days.
     *
     * @return void
     */
    public function tempHideRatingDiv()
    {
        $date = strtotime("+7 day");
        update_option('wdtTempFutureDate', date('Y-m-d', $date));
        echo json_encode(array("success"));
        exit;
    }

    /**
     * Dismiss the folders notice.
     *
     * @return void
     */
    public function dismissFoldersNotice()
    {
        update_option('wdtDismissFoldersNotice', 'yes');
        echo json_encode(array("success"));
        exit;
    }
}
