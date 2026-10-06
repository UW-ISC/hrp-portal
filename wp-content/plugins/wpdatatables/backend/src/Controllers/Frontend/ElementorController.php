<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Frontend;

use WDTPermissionsEnforcer;
use WPDataTable;

/**
 * ElementorController — frontend admin-ajax handler that renders a table
 * shortcode (plus its modal) for the Elementor pop-up integration.
 *
 * The nonce/cap checks and the wire format ($_POST in, wp_send_json_* out) are
 * what the existing frontend JS expects. Registered on both `wp_ajax_` and
 * `wp_ajax_nopriv_` (frontend path). The legacy global function remains a
 * one-line delegator.
 *
 * Plain class by design (NOT extending the REST-shaped
 * {@see \WPDataTables\Controllers\Controller}): admin-ajax controllers are
 * ajax-shaped.
 *
 * @package WPDataTables\Controllers\Frontend
 */
class ElementorController
{
    /**
     * Do shortcode for the Elementor pop-up.
     *
     * @return void
     * @throws \WDTException
     */
    public function doShortcode()
    {
        if (!isset($_POST['formdata']['table_id']) || '' === $_POST['formdata']['table_id']) {
            wp_send_json_error(null, 400);
        }

        $table_id = (int)$_POST['formdata']['table_id'];

        if (!$table_id) {
            wp_send_json_error(null, 400);
        }

        if (!isset($_POST['wdtNonce'])
            || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wdtNonce'])), 'wdtFrontendElementorNonce' . $table_id)
        ) {
            wp_send_json_error(null, 403);
        }

        if (!WDTPermissionsEnforcer::canUserViewTable($table_id)) {
            wp_send_json_error(null, 403);
        }

        $shortcode = '[wpdatatable ';
        $shortcode .= 'id=' . $table_id;
        $shortcode .= ']';

        $output = do_shortcode($shortcode);

        ob_start();
        WPDataTable::renderModal();
        $modalHTML = ob_get_clean();

        $output .= $modalHTML;

        wp_send_json_success($output);
    }
}
