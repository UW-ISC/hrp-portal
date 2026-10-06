<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Table;

use WPDataTableRows;
use WDTConfigController;
use stdClass;
use Exception;
use WPDataTables\Services\Permissions\PermissionsService;

/**
 * SimpleTableController — admin-ajax handlers for the Simple-table (handsontable)
 * constructor CRUD: create a Simple table (optionally from a template), load its
 * handsontable data, and save its data.
 *
 * The nonce/cap checks and the wire format ($_POST in, `echo`/`exit` out) are
 * what the existing admin JS expects. The bodies flow through the global
 * `WPDataTableRows` engine and the `WDTConfigController` facade (a shim into the
 * layered config/repository services), so admin-ajax and a future REST path
 * converge at the service layer. The legacy global functions remain as one-line
 * delegators.
 *
 * The private {@see self::generateSimpleTableID()} is the former global helper of
 * the same name; its single caller is createSimpleTable.
 *
 * Plain class by design (NOT extending the REST-shaped
 * {@see \WPDataTables\Controllers\Controller}): admin-ajax controllers are
 * ajax-shaped.
 *
 * @package WPDataTables\Controllers\Table
 */
class SimpleTableController
{
    /** @var PermissionsService */
    private $permissionsService;

    public function __construct(PermissionsService $permissionsService)
    {
        $this->permissionsService = $permissionsService;
    }

    /**
     * Create a Simple table (optionally seeded from a template) and return the
     * constructor edit link.
     *
     * @return void
     */
    public function createSimpleTable()
    {
        if (!$this->permissionsService->canCreateTables()
            || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')
        ) {
            exit();
        }
        $tableTemplateID = 0;
        if ($_POST['templateId']) {
            $tableTemplateID = (int)$_POST['templateId'];
        }
        $tableData = apply_filters(
            'wpdatatables_before_create_simple_table',
            json_decode(
                stripslashes_deep(
                    $_POST['tableData']
                )
            )
        );
        if ($tableTemplateID) {
            $wpDataTableRowsAll = WDTConfigController::loadRowsDataFromDBTemplateAll($tableTemplateID);
            $tableData->content = $wpDataTableRowsAll[0]->content;
            $tableData = WDTConfigController::sanitizeTableSettingsSimpleTable($tableData);
        } else {
            $tableData = WDTConfigController::sanitizeTableSettingsSimpleTable($tableData);
        }
        $wpDataTableRows = new WPDataTableRows($tableData);

        // Generate new id and save settings in wpdatatables table in DB
        if ($tableTemplateID) {
            $newTableId = $this->generateSimpleTableID($wpDataTableRows, $wpDataTableRowsAll[0]->settings, $tableTemplateID);
            for ($i = 0; $i < count($wpDataTableRowsAll); $i++) {
                WDTConfigController::saveRowData($wpDataTableRowsAll[$i]->data, $newTableId);
            }
        } else {
            $newTableId = $this->generateSimpleTableID($wpDataTableRows);
            // Save table with empty data
            $wpDataTableRows->saveTableWithEmptyData($newTableId);
        }

        // Generate a link for new table
        echo admin_url('admin.php?page=wpdatatables-constructor&source&simple&table_id=' . $newTableId);

        exit();
    }

    /**
     * Return the handsontable data + meta for a Simple table.
     *
     * @return void
     */
    public function getHandsontableData()
    {
        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtEditNonce')) {
            exit();
        }

        $tableID = (int)$_POST['tableID'];
        if (!$this->permissionsService->canEditTable($tableID)) {
            exit();
        }
        $res = new stdClass();

        try {
            $wpDataTableRows = WPDataTableRows::loadWpDataTableRows($tableID);
            $res->tableData = $wpDataTableRows->getRowsData();
            $res->tableMeta = $wpDataTableRows->getTableSettingsData()->content;
        } catch (Exception $e) {
            $res->error = ltrim($e->getMessage(), '<br/><br/>');
        }
        echo json_encode($res);
        exit();
    }

    /**
     * Save data in database for a Simple table.
     *
     * @return void
     */
    public function saveDataSimpleTable()
    {
        global $wpdb;

        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtEditNonce')) {
            exit();
        }
        $turnOffSimpleHeader = 0;
        $tableSettings = json_decode(stripslashes_deep($_POST['tableSettings']));
        $tableSettings = WDTConfigController::sanitizeTableConfig($tableSettings);
        $tableID = intval($tableSettings->id);
        if (!$this->permissionsService->canEditTable($tableID)) {
            exit();
        }
        $rowsData = json_decode(stripslashes_deep($_POST['rowsData']));
        $rowsData = WDTConfigController::sanitizeRowDataSimpleTable($rowsData);
        $result = new stdClass();

        if ($tableSettings->content->mergedCells) {
            $mergedCells = $tableSettings->content->mergedCells;
            foreach ($mergedCells as $mergedCell) {
                if ($mergedCell->row == 0 && $mergedCell->rowspan > 1) {
                    $turnOffSimpleHeader = 1;
                }
            }
        }

        $wpdb->update(
            $wpdb->prefix . "wpdatatables",
            array(
                'content' => json_encode($tableSettings->content),
                'scrollable' => $tableSettings->scrollable,
                'fixed_layout' => $tableSettings->fixed_layout,
                'word_wrap' => $tableSettings->word_wrap,
                'show_title' => $tableSettings->show_title,
                'title' => $tableSettings->title,
                'advanced_settings' => json_encode(
                    array(
                        'simpleResponsive' => $tableSettings->simpleResponsive,
                        'simpleHeader' => $turnOffSimpleHeader ? 0 : $tableSettings->simpleHeader,
                        'stripeTable' => $tableSettings->stripeTable,
                        'cellPadding' => $tableSettings->cellPadding,
                        'removeBorders' => $tableSettings->removeBorders,
                        'borderCollapse' => $tableSettings->borderCollapse,
                        'borderSpacing' => $tableSettings->borderSpacing,
                        'verticalScroll' => $tableSettings->verticalScroll,
                        'verticalScrollHeight' => $tableSettings->verticalScrollHeight,
                        'show_table_description' => $tableSettings->show_table_description,
                        'table_description' => $tableSettings->table_description,
                        'simple_template_id' => $tableSettings->simple_template_id
                    )
                ),

            ),
            array('id' => $tableID)
        );

        if ($wpdb->last_error == '') {
            try {
                $wpDataTableRows = new WPDataTableRows($tableSettings);

                if ($wpDataTableRows->checkIsExistTableID($tableID)) {
                    $wpDataTableRows->deleteRowsData($tableID);
                }
                foreach ($rowsData as $rowData) {
                    WDTConfigController::saveRowData($rowData, $tableID);
                }
                $wpDataTableRows = WPDataTableRows::loadWpDataTableRows($tableID);
                $result->reload = $wpDataTableRows->getTableSettingsData()->content->reloadCounter;
                $result->tableHTML = $wpDataTableRows->generateTable($tableID);
            } catch (Exception $e) {
                $result->error = ltrim($e->getMessage(), '<br/><br/>');
            }
        } else {
            $result->error = $wpdb->last_error;
        }

        echo json_encode($result);
        exit();
    }

    /**
     * Create the wpDataTable metadata row for a Simple table and return its new
     * id.
     *
     * @param WPDataTableRows $wpDataTableRows
     * @param object|null     $wpDataTableRowsSettings
     * @param int             $tableTemplateID
     * @return int
     */
    private function generateSimpleTableID($wpDataTableRows, $wpDataTableRowsSettings = null, $tableTemplateID = 0)
    {
        global $wpdb;
        $tableContent = new stdClass();
        $tableContent->rowNumber = $wpDataTableRows->getRowNumber();
        $tableContent->colNumber = $wpDataTableRows->getColNumber();
        $tableContent->colWidths = $wpDataTableRows->getColWidths();
        $tableContent->colHeaders = $wpDataTableRows->getColHeaders();
        $tableContent->reloadCounter = $wpDataTableRows->getReloadCounter();
        $tableContent->mergedCells = $wpDataTableRows->getMergeCells();
        if ($wpDataTableRowsSettings !== null) {
            $tableContent->settings = $wpDataTableRowsSettings;
        }
        // Create the wpDataTable metadata
        $wpdb->insert(
            $wpdb->prefix . "wpdatatables",
            array(
                'title' => ($tableTemplateID === 0) ? sanitize_text_field($wpDataTableRows->getTableName()) : $wpDataTableRowsSettings->name,
                'table_type' => $wpDataTableRows->getTableType(),
                'connection' => '',
                'content' => json_encode($tableContent),
                'server_side' => 0,
                'mysql_table_name' => '',
                'tabletools_config' => serialize(array(
                    'print' => 1,
                    'copy' => 1,
                    'excel' => 1,
                    'csv' => 1,
                    'pdf' => 0
                )),
                'advanced_settings' => json_encode(array(
                        'simpleResponsive' => ($tableTemplateID === 0) ? 0 : $wpDataTableRowsSettings->simpleResponsive,
                        'simpleHeader' => ($tableTemplateID === 0) ? 0 : $wpDataTableRowsSettings->simpleHeader,
                        'stripeTable' => ($tableTemplateID === 0) ? 0 : $wpDataTableRowsSettings->stripeTable,
                        'cellPadding' => ($tableTemplateID === 0) ? 10 : $wpDataTableRowsSettings->cellPadding,
                        'removeBorders' => ($tableTemplateID === 0) ? 0 : $wpDataTableRowsSettings->removeBorders,
                        'borderCollapse' => ($tableTemplateID === 0) ? 'collapse' : $wpDataTableRowsSettings->borderCollapse,
                        'borderSpacing' => ($tableTemplateID === 0) ? 0 : $wpDataTableRowsSettings->borderSpacing,
                        'verticalScroll' => ($tableTemplateID === 0) ? 0 : $wpDataTableRowsSettings->verticalScroll,
                        'verticalScrollHeight' => ($tableTemplateID === 0) ? 600 : $wpDataTableRowsSettings->verticalScrollHeight,
                        'show_table_description' => false,
                        'table_description' => sanitize_textarea_field($wpDataTableRows->getTableDescription()),
                        'fixed_header' => 0,
                        'fixed_header_offset' => 0,
                        'fixed_columns' => 0,
                        'fixed_left_columns_number' => 0,
                        'fixed_right_columns_number' => 0,
                        'simple_template_id' => $tableTemplateID,
                        'customRowDisplay' => '',
                        'customStringEmptyFiltering' => '',
                        'index_column' => 0,
                    )
                ),
            )
        );

        // Store the new table metadata ID
        return $wpdb->insert_id;
    }
}
