<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Frontend;

use WPDataTables\Plugin\Plugin;
use WPDataTables\Services\Table\ServerSideDataService;
use WDTPermissionsEnforcer;
use WDTException;

/**
 * DataController — the hot frontend server-side data path: the admin-ajax
 * handler behind the DataTables AJAX requests (`get_wdtable`). It performs the
 * nonce / view-permission checks, then hands off to {@see ServerSideDataService}
 * which loads the table + column config, builds the runtime WPDataTable (or
 * WPExcelDataTable), applies the column-options filters, and returns the
 * server-side JSON via queryBasedConstruct -> TableService -> ServerSideProcessor
 * -> MySqlQueryDataSource.
 *
 * Performs the nonce / view-permission checks, fires every `do_action` /
 * `apply_filters(_deprecated)` hook, applies the
 * `wpdatatables_filter_server_side_data` final filter, and uses the wire format
 * ($_GET/$_POST in, echo JSON, then exit) the existing DataTables JS expects. The
 * construction body lives in {@see ServerSideDataService} so the REST
 * `GET /tables/{id}/data?server_side=1` endpoint shares the exact same engine
 * path; this handler keeps its auth shell, the final filter and the echo/exit.
 * Registered on both `wp_ajax_` and `wp_ajax_nopriv_` (frontend path); the legacy
 * global function remains a one-line delegator.
 *
 * Plain class by design (NOT extending the REST-shaped
 * {@see \WPDataTables\Controllers\Controller}): admin-ajax controllers are
 * ajax-shaped; convergence with REST happens at the service layer.
 *
 * @package WPDataTables\Controllers\Frontend
 */
class DataController
{
    /**
     * Handler which returns the AJAX response (server-side DataTables data).
     *
     * @return void
     * @throws WDTException
     */
    public function getAjaxData()
    {
        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtFrontendServerSideNonce' . (int)$_GET['table_id'])) {
            exit();
        }

        $id = (int)$_GET['table_id'];

        if (!$id) {
            exit();
        }

        // Check permissions - user must have access to view this table
        if (!WDTPermissionsEnforcer::canUserViewTable($id)) {
            exit();
        }

        do_action('wpdatatables_get_ajax_data', $id);

        $result = Plugin::container()->get(ServerSideDataService::class)->buildResponse($id);

        // Addon table types echo their own output inside the service; nothing
        // more to do here (matches the legacy else-branch, which had no exit()).
        if ($result['type'] === 'json') {
            $json = apply_filters('wpdatatables_filter_server_side_data', $result['json'], $id, $_GET);

            echo $json;
            exit();
        }
    }
}
