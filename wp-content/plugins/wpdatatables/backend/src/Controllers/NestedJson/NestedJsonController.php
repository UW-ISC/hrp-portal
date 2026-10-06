<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\NestedJson;

use WDTConfigController;
use WDTNestedJson;
use WPDataTables\Services\Permissions\PermissionsService;

/**
 * NestedJsonController — admin-ajax handler for resolving the available roots of
 * a Nested-JSON data source URL.
 *
 * The nonce/cap check and the wire format ($_POST in, wp_send_json_* out) keep
 * the existing admin JS unaffected. The body flows through the
 * `WDTConfigController` facade + `WDTNestedJson`. The legacy global function
 * remains a one-line delegator. The $wdtVar1..9 placeholder globals are accessed
 * via `global`.
 *
 * Plain class by design (NOT extending the REST-shaped
 * {@see \WPDataTables\Controllers\Controller}).
 *
 * @package WPDataTables\Controllers\NestedJson
 */
class NestedJsonController
{
    /** @var PermissionsService */
    private $permissionsService;

    public function __construct(PermissionsService $permissionsService)
    {
        $this->permissionsService = $permissionsService;
    }

    /**
     * Get the roots from a Nested-JSON URL.
     *
     * @return void
     */
    public function getNestedJsonRoots()
    {
        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtEditNonce')) {
            exit();
        }
        global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9;
        $tableConfig = json_decode(stripslashes_deep($_POST['tableConfig']));
        $tableId = isset($tableConfig->id) ? (int) $tableConfig->id : 0;
        if ($tableId > 0) {
            if (!$this->permissionsService->canEditTable($tableId)) {
                exit();
            }
        } elseif (!$this->permissionsService->canCreateTables()
            && !$this->permissionsService->canEditTable(null)
        ) {
            exit();
        }
        // Set placeholders
        $wdtVar1 = $wdtVar1 === '' ? $tableConfig->var1 : $wdtVar1;
        $wdtVar2 = $wdtVar2 === '' ? $tableConfig->var2 : $wdtVar2;
        $wdtVar3 = $wdtVar3 === '' ? $tableConfig->var3 : $wdtVar3;
        $wdtVar4 = $wdtVar4 === '' ? $tableConfig->var4 : $wdtVar4;
        $wdtVar5 = $wdtVar5 === '' ? $tableConfig->var5 : $wdtVar5;
        $wdtVar6 = $wdtVar6 === '' ? $tableConfig->var6 : $wdtVar6;
        $wdtVar7 = $wdtVar7 === '' ? $tableConfig->var7 : $wdtVar7;
        $wdtVar8 = $wdtVar8 === '' ? $tableConfig->var8 : $wdtVar8;
        $wdtVar9 = $wdtVar9 === '' ? $tableConfig->var9 : $wdtVar9;

        $tableID = (int)$tableConfig->id;

        $params = json_decode(stripslashes_deep($_POST['params']));
        $params = WDTConfigController::sanitizeNestedJsonParams($params);
        $nestedJSON = new WDTNestedJson($params);
        $response = $nestedJSON->getResponse($tableID);

        if (!is_array($response)) {
            wp_send_json_error(array('msg' => $response));
        }

        $roots = $nestedJSON->prepareRoots('root', '', array(), $response);

        if (empty($roots)) {
            wp_send_json_error(array('msg' => esc_html__("Unable to retrieve data. Roots empty.", 'wpdatatables')));
        }

        wp_send_json_success(array('url' => $nestedJSON->getUrl(), 'roots' => $roots));
    }
}
