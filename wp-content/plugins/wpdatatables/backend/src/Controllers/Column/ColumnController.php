<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Column;

use WDTPermissionsEnforcer;
use WPDataTable;
use WDTConfigController;
use WDTColumn;

/**
 * ColumnController — admin-ajax handlers for the column domain. Currently the
 * distinct-values lookup used to populate the possible-values list for
 * server-side tables.
 *
 * The wire format ($_POST in, `echo`/`exit` out) keeps the existing admin JS
 * unaffected. The body flows through the global `WPDataTable` engine + the
 * `WDTConfigController` facade + `WDTColumn`. The legacy global function remains
 * a one-line delegator.
 *
 * NOTE: `readDistinctValuesFromTable` performs NO nonce/cap check.
 *
 * Plain class by design (NOT extending the REST-shaped
 * {@see \WPDataTables\Controllers\Controller}).
 *
 * @package WPDataTables\Controllers\Column
 */
class ColumnController
{
    /**
     * Read the distinct values for a column (populates possible-values lists for
     * server-side tables).
     *
     * @return void
     * @throws \Exception
     * @throws \WDTException
     */
    public function readDistinctValuesFromTable()
    {
        $tableId = (int)$_POST['tableId'];
        $columnId = (int)$_POST['columnId'];

        $wpDataTable = WPDataTable::loadWpDataTable($tableId);
        $tableData = WDTConfigController::loadTableFromDB($tableId);

        $columnData = WDTConfigController::loadSingleColumnFromDB($columnId);
        $column = $wpDataTable->getColumn($columnData['orig_header']);

        $distValues = WDTColumn::getPossibleValuesRead($column, false, $tableData);
        echo json_encode($distValues);
        exit();
    }
    /**
     * AJAX loading for a column's possible values (select2 search source).
     *
     * @return void
     * @throws \WDTException
     */
    public function getColumnPossibleValues()
    {
        $result = [];
        $tableId = isset($_POST['tableId']) ? (int) $_POST['tableId'] : 0;
        $originalHeader = isset($_POST['originalHeader']) ? sanitize_text_field(wp_unslash($_POST['originalHeader'])) : '';

        if (!$tableId || $originalHeader === '') {
            exit();
        }

        $nonce = isset($_POST['wdtNonce']) ? sanitize_text_field(wp_unslash($_POST['wdtNonce'])) : '';
        $isAdminRequest = wp_verify_nonce($nonce, 'wdtEditNonce')
            && (
                current_user_can('manage_options')
                || \WPDataTables\Plugin\Plugin::container()
                    ->get(\WPDataTables\Services\Permissions\PermissionsService::class)
                    ->canEditTable($tableId)
            );
        $nonceValid = $isAdminRequest
            || wp_verify_nonce($nonce, 'wdtFrontendServerSideNonce' . $tableId)
            || wp_verify_nonce($nonce, 'wdtFrontendElementorNonce' . $tableId);
        if (!$nonceValid) {
            exit();
        }

        if (!$isAdminRequest && !WDTPermissionsEnforcer::canUserViewTable($tableId)) {
            exit();
        }

        $wpDataTable = WPDataTable::loadWpDataTable($tableId);
        /** @var WDTColumn $wpDataColumn */
        $wpDataColumn = $wpDataTable->getColumn($originalHeader);

        $values = $wpDataColumn->getPossibleValues();

        $isPostType = in_array($wpDataTable->getTableType(), ['wp_posts_query', 'woo_commerce']);

        // Filter logic for searching
        if (!empty($_POST['q'])) {
            if ($wpDataColumn->getForeignKeyRule()) {
                if ($wpDataColumn->getParentTable()->serverSide()) {
                    $values = array_filter($values, function ($value) {
                        return stripos(addslashes($value['text']), $_POST['q']) !== false;
                    });
                } else {
                    $values = array_filter($values, function ($value) {
                        return stripos(addslashes($value), $_POST['q']) !== false;
                    });
                }
            } else {
                $values = array_filter($values, function ($value) {
                    return stripos(addslashes($value), $_POST['q']) !== false;
                });
            }
        }

        if ($wpDataColumn->getPossibleValuesAjax() !== -1) {
            $values = array_slice($values, 0, $wpDataColumn->getPossibleValuesAjax());
        } else {
            $values = array_values(array_filter($values));
        }

        foreach ($values as $key => $value) {
            if ($isPostType) {
                $result[$key]['value'] = $value;
                $result[$key]['text'] = strip_tags($value);
            } else {
                if (is_array($value)) {
                    $result[$key]['value'] = $value['value'];
                    $result[$key]['text'] = $value['text'];
                } else {
                    $result[$key]['value'] = $value;
                    $result[$key]['text'] = $value;
                }
            }
        }

        echo json_encode($result);
        exit();
    }
}
