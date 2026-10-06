<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Frontend;

use WDTConfigController;
use WPDataTables\Services\Tools\ToolsService;
use Connection;
use WPDataTable;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * TableEditController — frontend admin-ajax handlers for inline / front-end
 * editing of table data: save a row (standard editor), save edited cells
 * (Excel-like editor), delete a single row, and delete multiple rows.
 *
 * The nonce / capability checks, the action / filter hooks, the SQL written
 * through $wpdb (WordPress DB) or the separate-connection driver, and the wire
 * format ($_POST in, json_encode out, then exit) are what the existing frontend
 * editor JS expects. Each handler is registered on both `wp_ajax_` and
 * `wp_ajax_nopriv_` (frontend path); the legacy global functions remain one-line
 * delegators.
 *
 * Plain class by design (NOT extending the REST-shaped
 * {@see \WPDataTables\Controllers\Controller}): admin-ajax controllers are
 * ajax-shaped; convergence with REST happens at the service layer.
 *
 * @package WPDataTables\Controllers\Frontend
 */
class TableEditController
{
        /**
     * Mirrors {@see TableHydrationService}: manual tables are editable in wp-admin
     * even when the persisted `editable` flag is off.
     *
     * @param object $tableData
     * @return bool
     */
    private function isTableEditingAllowed($tableData): bool
    {
        if (!empty($tableData->editable)) {
            return true;
        }

        return is_admin()
            && isset($tableData->table_type)
            && $tableData->table_type === 'manual';
    }

    /**
     * Saves the table from frontend.
     *
     * @return void
     */
    public function saveTableFrontend()
    {
        global $wpdb, $wp_version;
        $formData = $_POST['formdata'];
        $isDuplicate = $_POST['isDuplicate'];

        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtFrontendEditTableNonce' . (int)$formData['table_id'])) {
            exit();
        }

        $returnResult = array('success' => '', 'error' => '', 'is_new' => false);

        $tableId = (int)$formData['table_id'];

        $tableData = WDTConfigController::loadTableFromDB($tableId);

        if (!$this->isTableEditingAllowed($tableData)) {
            exit();
        }

        // If current user cannot edit - do nothing
        if (!wdtCurrentUserCanEdit($tableData->editor_roles, $tableId)) {
            exit();
        }

        do_action('wpdatatables_before_frontend_edit_row', $formData, $returnResult, $tableId);

        unset($formData['table_id'], $formData['nonce']);

        $formData = apply_filters('wpdatatables_filter_frontend_formdata', $formData, $tableId);

        $mySqlTableName = ToolsService::applyPlaceholders($tableData->mysql_table_name);

        $advancedSettings = json_decode($tableData->advanced_settings);

        $columnsData = WDTConfigController::loadColumnsFromDB($tableId);
        $idKey = '';
        $idVal = '';
        $dateFormat = get_option('wdtDateFormat');
        $timeFormat = get_option('wdtTimeFormat');
        $currentTimeZone = get_option('timezone_string') !== "" ? get_option('timezone_string') : date_default_timezone_get();
        $timezone = new DateTimeZone($currentTimeZone);
        $currentDateTime = new DateTime('now', $timezone);
        $formattedDateTime = $currentDateTime->format($dateFormat . ' ' . $timeFormat);

        // NULL value for different wordpress versions
        $nullValue = (!$tableData->connection) ? NULL : "NULL";

        if ($wp_version < 4.4) {
            $nullValue = (!$tableData->connection) ? ToolsService::wrapQuotes('NULL', $tableData->connection) : "NULL";
        }

        if ($tableData->edit_only_own_rows) {
            $action = 'save';
            // Check if current user can update own rows not others
            ToolsService::checkCurrentUsersActionsPermissions($tableData, $mySqlTableName, $columnsData, $formData, $action);
        }

        foreach ($columnsData as $column) {
            $advancedSettings = json_decode($column->advanced_settings);
            if ($column->id_column) {
                $idKey = $column->orig_header;
                $idVal = $isDuplicate == 'true' ? 0 : (int)$formData[$idKey];
                unset($formData[$idKey]);
            } else if ($column->orig_header == "wdt_created_by") {
                $formData[$column->orig_header] = $formData[$column->orig_header] == "" ? (is_user_logged_in() ? ToolsService::wrapQuotes(sanitize_text_field(wp_get_current_user()->data->user_login), $tableData->connection) : 'anonymous_user') : ToolsService::wrapQuotes(sanitize_text_field($formData[$column->orig_header]), $tableData->connection);
            } else if ($column->orig_header == "wdt_created_at") {
                $formData[$column->orig_header] = $formData[$column->orig_header] == "" ? ToolsService::wrapQuotes(DateTime::createFromFormat($dateFormat . ' ' . $timeFormat, $formattedDateTime)->format('Y-m-d H:i:s'), $tableData->connection) : ToolsService::wrapQuotes(DateTime::createFromFormat($dateFormat . ' ' . $timeFormat, $formData[$column->orig_header])->format('Y-m-d H:i:s'), $tableData->connection);
            } else if ($column->orig_header == "wdt_last_edited_by") {
                $formData[$column->orig_header] = is_user_logged_in() ? ToolsService::wrapQuotes(sanitize_text_field(wp_get_current_user()->data->user_login), $tableData->connection) : 'anonymous_user';
            } else if ($column->orig_header == "wdt_last_edited_at") {
                $formData[$column->orig_header] = ToolsService::wrapQuotes(DateTime::createFromFormat($dateFormat . ' ' . $timeFormat, $formattedDateTime)->format('Y-m-d H:i:s'), $tableData->connection);
            } else {
                // Defining the values for User ID columns and for "none" input types
                if ($column->id == $tableData->userid_column_id) {
                    $formData[$column->orig_header] = get_current_user_id();
                } elseif ($column->input_type == 'none') {
                    if ($idVal == '0') {
                        // For new values we take the default value (if defined)
                        if (!empty($advancedSettings->editingDefaultValue)) {
                            $formData[$column->orig_header] = $advancedSettings->editingDefaultValue;
                        } else {
                            unset($formData[$column->orig_header]);
                        }
                    } else {
                        // For updating values we do not modify the cell at all
                        unset($formData[$column->orig_header]);
                    }
                }

                if (isset($formData[$column->orig_header])) {

                    // Sanitize data
                    $formData[$column->orig_header] = wp_kses_post(
                        $formData[$column->orig_header]
                    );

                    // Formatting for DB based on column type
                    switch ($column->column_type) {
                        case 'date':
                            if ($formData[$column->orig_header] != '') {

                                $formData[$column->orig_header] =
                                    ToolsService::wrapQuotes(
                                        DateTime::createFromFormat(
                                            $dateFormat,
                                            $formData[$column->orig_header]
                                        )->format('Y-m-d'),
                                        $tableData->connection
                                    );
                            } else {
                                $formData[$column->orig_header] = $nullValue;
                            }
                            break;
                        case 'datetime':
                            if ($formData[$column->orig_header] != '') {

                                $formData[$column->orig_header] =
                                    ToolsService::wrapQuotes(
                                        DateTime::createFromFormat(
                                            $dateFormat . ' ' . $timeFormat,
                                            $formData[$column->orig_header]
                                        )->format('Y-m-d H:i:s'),
                                        $tableData->connection
                                    );
                            } else {
                                $formData[$column->orig_header] = $nullValue;
                            }
                            break;
                        case 'float':
                            $number_format = get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1;
                            if ($number_format == 1) {
                                $formData[$column->orig_header] = str_replace('.', '', $formData[$column->orig_header]);
                                $formData[$column->orig_header] = str_replace(',', '.', $formData[$column->orig_header]);
                            } else {
                                $formData[$column->orig_header] = str_replace(',', '', $formData[$column->orig_header]);
                            }
                            $value = ToolsService::wrapQuotes((float)$formData[$column->orig_header], $tableData->connection);
                            if ($formData[$column->orig_header] === '') {
                                $value = $nullValue;
                            }
                            $formData[$column->orig_header] = $value;
                            break;
                        case 'int':
                            $value = ToolsService::wrapQuotes((int)$formData[$column->orig_header], $tableData->connection);
                            if ($formData[$column->orig_header] === '') {
                                $value = $nullValue;
                            }
                            $formData[$column->orig_header] = $value;
                            break;
                        case 'email':
                            $formData[$column->orig_header] = ToolsService::sanitizeEmailCellValueForDb($formData[$column->orig_header]);
                            $formData[$column->orig_header] = ToolsService::prepareStringCell($formData[$column->orig_header], $tableData->connection);
                            break;
                        case 'string':
                            if ($column->input_type === 'textarea') {
                                $formData[$column->orig_header] = str_replace("\n", '<br/>', $formData[$column->orig_header]);
                            }
                            if ($formData[$column->orig_header] === '') {
                                $value = $nullValue;
                            } else {
                                $value = ToolsService::prepareStringCell($formData[$column->orig_header], $tableData->connection);
                            }
                            $formData[$column->orig_header] = $value;
                            break;
                        case 'link':
                            $formData[$column->orig_header] = ToolsService::sanitizeLinkCellValueForDb($formData[$column->orig_header]);
                            $formData[$column->orig_header] = ToolsService::prepareStringCell($formData[$column->orig_header], $tableData->connection);
                            break;
                        case 'image':
                            $formData[$column->orig_header] = ToolsService::sanitizeImageCellValueForDb($formData[$column->orig_header]);
                            $formData[$column->orig_header] = ToolsService::prepareStringCell($formData[$column->orig_header], $tableData->connection);
                            break;
                        case 'time':
                            if ($formData[$column->orig_header] != '') {
                                $formData[$column->orig_header] =
                                    ToolsService::wrapQuotes(
                                        DateTime::createFromFormat(
                                            $timeFormat,
                                            $formData[$column->orig_header]
                                        )->format('H:i:s'),
                                        $tableData->connection
                                    );
                            } else {
                                $formData[$column->orig_header] = $nullValue;
                            }
                            break;
                        default:
                            if (has_filter('wpdatatables_formatting_entry_data_custom_column_type_' . $column->column_type)) {
                                $formData[$column->orig_header] = apply_filters(
                                    'wpdatatables_formatting_entry_data_custom_column_type_' . $column->column_type,
                                    $formData[$column->orig_header], $formData, $advancedSettings, $column, $tableData
                                );
                            } else {
                                $formData[$column->orig_header] = ToolsService::wrapQuotes(sanitize_text_field($formData[$column->orig_header]), $tableData->connection);
                            }
                            break;
                    }

                }

            }
        }

        $formData = apply_filters('wpdatatables_filter_formdata_before_save', $formData, $tableId);

        $formData = ToolsService::filterFormDataToKnownColumns($formData, $columnsData);

        // If the plugin is using WP DB
        if (!(Connection::isSeparate($tableData->connection))) {
            $formData = stripslashes_deep($formData);
            if ($idVal != '0') {
                if (isset($advancedSettings->editButtonsDisplayed) &&
                    (!(in_array('all', $advancedSettings->editButtonsDisplayed) ||
                        in_array('edit', $advancedSettings->editButtonsDisplayed)))
                ) {
                    exit();
                }
                $res = $wpdb->update($mySqlTableName,
                    $formData,
                    array(
                        $idKey => $idVal
                    )
                );

                if (!$res) {
                    if (!empty($wpdb->last_error)) {
                        $returnResult['error'] = __('There was an error trying to update the row! Error: ', 'wpdatatables') . $wpdb->last_error;
                    } else {
                        $returnResult['success'] = $idVal;
                    }
                } else {
                    $returnResult['success'] = $idVal;
                }
            } else {
                if (isset($advancedSettings->editButtonsDisplayed) &&
                    (!(in_array('all', $advancedSettings->editButtonsDisplayed) ||
                        in_array('duplicate', $advancedSettings->editButtonsDisplayed) ||
                        in_array('new_entry', $advancedSettings->editButtonsDisplayed)))
                ) {
                    exit();
                }
                $returnResult['is_new'] = true;
                $res = $wpdb->insert($mySqlTableName,
                    $formData
                );
                if (!$res) {
                    $returnResult['error'] = __('There was an error trying to insert a new row! Error: ', 'wpdatatables') . $wpdb->last_error;
                } else {
                    $returnResult['success'] = $wpdb->insert_id;
                    $idVal = $wpdb->insert_id;
                }
            }
        } else {
            // If plugin is using a separate DB

            $vendor = Connection::getVendor($tableData->connection);
            $isMySql = $vendor === Connection::$MYSQL;
            $isMSSql = $vendor === Connection::$MSSQL;
            $isPostgreSql = $vendor === Connection::$POSTGRESQL;

            $leftSysIdentifier = Connection::getLeftColumnQuote($vendor);
            $rightSysIdentifier = Connection::getRightColumnQuote($vendor);

            $sql = Connection::getInstance($tableData->connection);
            if ($idVal != '0') {
                $query = 'UPDATE ' . $mySqlTableName . ' SET ';
                $i = 1;
                foreach ($formData as $columnKey => $columnValue) {
                    if ($columnValue == "''") {
                        $columnValue = $nullValue;
                    }
                    $query .= $leftSysIdentifier . $columnKey . $rightSysIdentifier . ' = ' . $columnValue . ' ';
                    if ($i < count($formData)) {
                        $query .= ', ';
                    }
                    $i++;
                }
                $query .= ' WHERE ' . $leftSysIdentifier . $idKey . $rightSysIdentifier . ' = ' . $idVal;
                $query = apply_filters('wpdatatables_query_before_save_frontend', $query, $tableId);
                if ($sql->doQuery($query)) {
                    if (!$isPostgreSql)
                        $idVal = $sql->getLastInsertId();
                    $returnResult['success'] = $idVal;
                } else {
                    if ($sql->getLastError() !== '') {
                        $returnResult['error'] = __('There was an error trying to update the row! Error: ', 'wpdatatables') . $sql->getLastError();
                    } else {
                        $returnResult['success'] = $idVal;
                    }
                }
            } else {
                $returnResult['is_new'] = true;
                $query = 'INSERT INTO ' . $mySqlTableName . ' ';
                $columns = array();
                $values = array();
                foreach ($formData as $columnKey => $columnValue) {
                    if ($columnValue == "''") {
                        $columnValue = $nullValue;
                    }
                    $columns[] = $leftSysIdentifier . $columnKey . $rightSysIdentifier;
                    $values[] = $columnValue;
                }
                $query .= ' (' . implode(',', $columns) . ') VALUES ';
                $query .= ' (' . implode(',', $values) . ')';
                $query = apply_filters('wpdatatables_query_before_save_frontend', $query, $tableId);
                $sql->doQuery($query);
                if ($sql->getLastError() == '') {
                    $returnResult['success'] = $sql->getLastInsertId();
                } else {
                    $returnResult['error'] = __('There was an error trying to insert a new row! Error: ', 'wpdatatables') . $sql->getLastError();
                }
            }
        }

        if (!empty($returnResult['success']) && empty($returnResult['error'])) {
            do_action(
                'wpdatatables_after_frontent_edit_row',
                $formData,
                $returnResult['success'],
                $tableId,
                !empty($returnResult['is_new'])
            );
        }

        echo json_encode($returnResult);

        exit();
    }

    /**
     * Save changes on excel table cells.
     *
     * @return void
     */
    public function saveTableCellsFrontend()
    {
        global $wpdb;

        // Permissions check
        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtFrontendEditTableNonce' . (int)$_POST['table_id'])) {
            exit();
        }

        $returnResult = array('success' => array(), 'error' => '', 'has_new' => false);

        $tableId = (int)$_POST['table_id'];

        $tableData = WDTConfigController::loadTableFromDB($tableId);

        if (!$this->isTableEditingAllowed($tableData)) {
            exit();
        }

        // If current user cannot edit - do nothing
        if (!wdtCurrentUserCanEdit($tableData->editor_roles, $tableId)) {
            exit();
        }

        $cellsData = apply_filters('wpdatatables_excel_filter_frontend_formdata', $_POST['cells'], $tableId);

        do_action('wpdatatables_excel_before_frontend_edit_row', $cellsData, $returnResult, $tableId);

        $mySqlTableName = $tableData->mysql_table_name;

        // If is turn on user can see and edit own data for excel-like tables
        // TODO: Implement users see and edit their own data on excel like tables
        if ($tableData->edit_only_own_rows) {
            $returnResult['error'] = __('At the moment option "Users see and edit only their own data" is not working with Excel like tables. Please turn it off to continue editing.', 'wpdatatables');
            echo json_encode($returnResult);
            exit();
        }

        //getting distinct column names from sent data for change
        $columnNames = call_user_func_array('array_merge', $cellsData);
        $columnNames = array_keys($columnNames);

        //taking meta for changing columns
        $columnsMeta = WDTConfigController::loadColumnsFromDB($tableId);
        $idColumnKey = null;

        $formulaColumns = array();
        $allColumnsNames = array();
        $allColumnsTypes = array();

        //extracting key for id column
        foreach ($columnsMeta as $columnMeta) {
            $allColumnsNames[] = $columnMeta->orig_header;
            $allColumnsTypes[$columnMeta->orig_header] = $columnMeta->column_type;

            if ($columnMeta->id_column) {
                $idColumnKey = $columnMeta->orig_header;
            }

            if ($columnMeta->column_type == 'formula') {
                $formulaColumns[] = $columnMeta;
            }
        }

        $nonExistingCols = array_diff($columnNames, $allColumnsNames);

        //if some column not exist, error is returned
        if (!empty($nonExistingCols)) {
            $returnResult['error'] = __('Bad column names supplied: ', 'wpdatatables') . implode(', ', $nonExistingCols);
        } else if (!in_array($idColumnKey, $columnNames)) { //if id column not found among sent data, error is returned
            $returnResult['error'] = __('ID column not supplied', 'wpdatatables');
        } else {
            foreach ($cellsData as $cellData) {
                //if there is no id column sent in cell data, error is returned
                if (!key_exists($idColumnKey, $cellData)) {
                    $returnResult['error'] = __('ID column not supplied for a cell', 'wpdatatables');
                    break;
                } else {
                    //this is id column's value of changing cell's row
                    $cellIdValue = $cellData[$idColumnKey];
                    $idColumnType = $allColumnsTypes[$idColumnKey];

                    unset($cellData[$idColumnKey]);
                    reset($cellData);

                    foreach (array_keys($cellData) as $columnName) {
                        $columnType = $allColumnsTypes[$columnName];
                        // Link / email / image must be plain text (URL or url||label), never rich HTML — avoids <a href="javascript:…"> surviving strip_tags allowlist.
                        if (in_array($columnType, array('link', 'email', 'image'), true)) {
                            $cellData[$columnName] = wp_strip_all_tags(wp_unslash($cellData[$columnName]));
                        } elseif ($columnType === 'string') {
                            $cellData[$columnName] = strip_tags(
                                $cellData[$columnName],
                                '<br/><br><b><strong><h1><h2><h3><a><i><em><ol><ul><li><img><blockquote><div><hr><p><span><select><option><sup><sub><iframe><pre><button>'
                            );
                        }
                        if ($columnType === 'link') {
                            $cellData[$columnName] = ToolsService::sanitizeLinkCellValueForDb($cellData[$columnName]);
                        } elseif ($columnType === 'email') {
                            $cellData[$columnName] = ToolsService::sanitizeEmailCellValueForDb($cellData[$columnName]);
                        } elseif ($columnType === 'image') {
                            $cellData[$columnName] = ToolsService::sanitizeImageCellValueForDb($cellData[$columnName]);
                        }
                        $cellData[$columnName] = ToolsService::prepareStringCell($cellData[$columnName], $tableData->connection);
                        if ($cellData[$columnName] == '') {
                            if (in_array($allColumnsTypes[$columnName], array('int',
                                'float',
                                'date',
                                'time',
                                'datetime'))) {
                                $cellData[$columnName] = (!$tableData->connection) ? NULL : "NULL";
                            }
                        }
                    }

                    if (empty($cellIdValue)) {
                        $qActionFlag = 'insert';
                    } else {
                        $qActionFlag = 'update';
                    }

                    if (Connection::isSeparate($tableData->connection)) {

                        $vendor = Connection::getVendor($tableData->connection);
                        $isMySql = $vendor === Connection::$MYSQL;
                        $isMSSql = $vendor === Connection::$MSSQL;
                        $isPostgreSql = $vendor === Connection::$POSTGRESQL;

                        if ($isMSSql) {
                            $leftSysIdentifier = '[';
                            $rightSysIdentifier = ']';
                        }

                        if ($isPostgreSql) {
                            $leftSysIdentifier = '"';
                            $rightSysIdentifier = '"';
                        }

                        if ($isMySql) {
                            $leftSysIdentifier = '`';
                            $rightSysIdentifier = '`';
                        }

                        if ($qActionFlag == 'insert') {
                            $insert_column_names = array_keys($cellData);
                            $qColumnNames = $leftSysIdentifier . implode('`,`', $insert_column_names) . $rightSysIdentifier;
                            $qValues = array_values($cellData);
                            $qValues = implode(",", $qValues);

                            $query = "INSERT INTO $mySqlTableName ($qColumnNames) VALUES ($qValues)";
                        } else {
                            $qSet = '';
                            foreach ($cellData as $cell_column_key => $cell_value) {
                                $qSet .= (!empty($qSet)) ? ', ' : '';
                                $cell_value = $cell_value == "''" ? "NULL" : $cell_value;
                                $qSet .= $leftSysIdentifier
                                    . $cell_column_key
                                    . $rightSysIdentifier
                                    . "= $cell_value";
                            }
                            $qWhere = $leftSysIdentifier
                                . $idColumnKey
                                . $rightSysIdentifier
                                . ' = '
                                . ToolsService::formatSqlWhereIdValue($cellIdValue, $idColumnType, $tableData->connection);

                            $query = "UPDATE $mySqlTableName SET $qSet WHERE $qWhere";
                        }

                        $query = apply_filters('wpdatatables_filter_excel_editor_query', $query, $tableId);

                        $sql = Connection::getInstance($tableData->connection);
                        $sql->doQuery($query);
                        $sqlLastError = $sql->getLastError();

                        if ($sqlLastError != '') {
                            $returnResult['error'] = __('There was an error trying to insert a new row! Error: ', 'wpdatatables') . $sqlLastError;
                            break;
                        }

                        $cellIdValue = ($qActionFlag == 'update') ? $cellIdValue : $sql->getLastInsertId();
                        $returnResult['success'][] = array(
                            "$idColumnKey" => $cellIdValue,
                            'action' => $qActionFlag
                        );

                        if ($qActionFlag == 'insert') {
                            $returnResult['has_new'] = true;
                        }

                        do_action('wpdatatables_excel_after_frontent_edit_row', $tableId, $cellIdValue, $cellData, $qActionFlag, $sqlLastError);
                    } else {

                        if ($qActionFlag == 'insert') {
                            $res = $wpdb->insert($mySqlTableName, array_map('stripslashes_deep', $cellData));
                        } else {
                            $res = $wpdb->update(
                                $mySqlTableName,
                                array_map('stripslashes_deep', $cellData),
                                array(
                                    $idColumnKey => $cellIdValue
                                )
                            );
                        }

                        if ($res === false) {
                            $returnResult['error'] = __('There was an error trying to update the row! Error: ', 'wpdatatables') . $wpdb->last_error;
                        } else {
                            $cellIdValue = ($qActionFlag == 'update') ? $cellIdValue : $wpdb->insert_id;
                            $returnResult['success'][] = array("$idColumnKey" => ($qActionFlag == 'update') ? $cellIdValue : $wpdb->insert_id,
                                'action' => $qActionFlag
                            );

                            if ($qActionFlag == 'insert') {
                                $returnResult['has_new'] = true;
                            }
                        }

                        do_action('wpdatatables_excel_after_frontent_edit_row', $tableId, $cellIdValue, $cellData, $qActionFlag, $wpdb->last_error);
                    }
                }
            }
        }


        if (empty($returnResult['error'])) {
            $calculatedFormulaRows = array();
            $rowsData = $_POST['rows'];

            if (!empty($formulaColumns) && !empty($rowsData)) {
                foreach ($formulaColumns as $formula_col) {
                    $formula = $formula_col->calc_formula;
                    $colKey = $formula_col->orig_header;

                    $headers = array();
                    $headersInFormula = ToolsService::getColHeadersInFormula($formula, $allColumnsNames);
                    $headers = ToolsService::sanitizeHeaders($headersInFormula);

                    foreach ($rowsData as $rowData) {
                        try {
                            $formulaValue =
                                WPDataTable::solveFormula(
                                    $formula,
                                    $headers,
                                    $rowData
                                );
                        } catch (Exception $e) {
                            $formulaValue = 0;
                        }

                        $idColValue = $rowData[$idColumnKey];

                        if (!isset($calculatedFormulaRows["$idColValue"])) {
                            $calculatedFormulaRows["$idColValue"] = array(
                                $idColumnKey => $idColValue,
                                $colKey => $formulaValue
                            );
                        } else {
                            $calculatedFormulaRows["$idColValue"][$colKey] = $formulaValue;
                        }
                    }
                }

            }
        }

        $returnResult['formula_cells'] = array_values($calculatedFormulaRows);

        do_action('wpdatatables_excel_after_frontent_edit_cells', $_POST['cells'], $returnResult, $tableId);

        echo json_encode($returnResult);

        exit();

    }

    /**
     * Handle table row delete.
     *
     * @return void
     */
    public function deleteTableRow()
    {
        global $wpdb;

        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtFrontendEditTableNonce' . (int)$_POST['table_id'])) {
            exit();
        }

        $tableId = (int)$_POST['table_id'];
        $idKey = sanitize_text_field($_POST['id_key']);
        $idVal = sanitize_text_field(wp_unslash($_POST['id_val']));

        $returnResult = array('success' => '', 'error' => '');

        $tableData = WDTConfigController::loadTableFromDB($tableId);
        $mySqlTableName = ToolsService::applyPlaceholders($tableData->mysql_table_name);
        $columnsData = WDTConfigController::loadColumnsFromDB($tableId);
        $idColumnType = 'int';

        $advancedSettings = json_decode($tableData->advanced_settings);
        if (isset($advancedSettings->editButtonsDisplayed) &&
            (!(in_array('all', $advancedSettings->editButtonsDisplayed) ||
                in_array('delete', $advancedSettings->editButtonsDisplayed)))
        ) {
            exit();
        }

        foreach ($columnsData as $column) {
            if ($column->id_column) {
                if ($idKey != $column->orig_header)
                    exit();
                $idKey = $column->orig_header;
                $idColumnType = $column->column_type;
                break;
            }
        }
        // If current user cannot edit - do nothing
        if (!wdtCurrentUserCanEdit($tableData->editor_roles, $tableId)) {
            exit();
        }

        if ($tableData->edit_only_own_rows) {
            $action = 'delete';
            // Check if current user can  delete own rows
            ToolsService::checkCurrentUsersActionsPermissions($tableData, $mySqlTableName, $columnsData, $idVal, $action);
        }

        do_action('wpdatatables_before_delete_row', $idVal, $tableId, $idKey);

        // If the plugin is using WP DB
        if (!(Connection::isSeparate($tableData->connection))) {
            $res = $wpdb->delete($mySqlTableName, array($idKey => $idVal));
            if (!$res) {
                if (!empty($wpdb->last_error)) {
                    $returnResult['error'] = __('There was an error trying to delete the row! Error: ', 'wpdatatables') . $wpdb->last_error;
                } else {
                    $returnResult['error'] = __('There was an error in your database when you are trying to delete the row! ', 'wpdatatables');
                }
            } else {
                $returnResult['success'] = true;
            }
        } else {
            $vendor = Connection::getVendor($tableData->connection);
            $leftSysIdentifier = Connection::getLeftColumnQuote($vendor);
            $rightSysIdentifier = Connection::getRightColumnQuote($vendor);
            $sql = Connection::getInstance($tableData->connection);
            $formattedId = ToolsService::formatSqlWhereIdValue($idVal, $idColumnType, $tableData->connection);
            $query = 'DELETE FROM ' . $mySqlTableName . ' WHERE ' . $leftSysIdentifier . $idKey . $rightSysIdentifier . ' = ' . $formattedId;
            $sql->doQuery($query);
            if ($sql->getLastError() !== '') {
                $returnResult['error'] = __('There was an error trying to delete the row! Error: ', 'wpdatatables') . $sql->getLastError();
            } else {
                $returnResult['success'] = true;
            }
        }

        if (!empty($returnResult['success'])) {
            do_action('wpdatatables_after_delete_row', $idVal, $tableId, $idKey);
        }

        echo json_encode($returnResult);

        exit();
    }

    /**
     * Handle table multiple rows delete.
     *
     * @return void
     */
    public function deleteTableRows()
    {
        global $wpdb;

        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtFrontendEditTableNonce' . (int)$_POST['table_id'])) {
            exit();
        }

        $returnResult = array('success' => array(), 'error' => '');

        $tableId = (int)$_POST['table_id'];

        $rows = apply_filters('wpdatatables_excel_filter_delete_rows', $_POST['rows'], $tableId);

        if (empty($rows)) {
            $returnResult['error'] = __('Nothing to delete.', 'wpdatatables');
            echo json_encode($returnResult);
            exit();
        } else if (!is_array($rows)) {
            $returnResult['error'] = __('Bad request format.', 'wpdatatables');
            echo json_encode($returnResult);
            exit();
        }

        //first key(should be only key) as a id column name
        reset($rows);
        $idColName = sanitize_text_field(key($rows));

        if (empty($rows[$idColName])) {
            $returnResult['error'] = __('Nothing to delete.', 'wpdatatables');
            echo json_encode($returnResult);
            exit();
        } else if (!is_array($rows[$idColName])) {
            $returnResult['error'] = __('Bad request format.', 'wpdatatables');
            echo json_encode($returnResult);
            exit();
        }

        $tableData = WDTConfigController::loadTableFromDB($tableId);
        $mySqlTableName = $tableData->mysql_table_name;

        // If current user cannot edit - do nothing
        if (!wdtCurrentUserCanEdit($tableData->editor_roles, $tableId)) {
            $returnResult['error'] = __('You don\'t have permission to change this table.', 'wpdatatables');
            echo json_encode($returnResult);
            exit();
        }

        // If is turn on user can see and edit own data for excel-like tables
        // TODO: Implement users see and edit their own data on excel like tables
        if ($tableData->edit_only_own_rows) {
            $returnResult['error'] = __('At the moment option "Users see and edit only their own data" is not working with Excel like tables. Please turn it off to continue editing.', 'wpdatatables');
            echo json_encode($returnResult);
            exit();
        }

        $columnsMeta = WDTConfigController::loadColumnsFromDB($tableId, array($idColName));

        if (count($columnsMeta) == 0) {
            $returnResult['error'] = __('Supplied id column not exist.', 'wpdatatables');
            echo json_encode($returnResult);
            exit();
        } else {
            $columnMeta = $columnsMeta[0];

            if ($columnMeta->id_column) {
                $idColumnKey = sanitize_text_field($columnMeta->orig_header);

                $deleteRowIds = $rows[$idColumnKey];

                foreach ($deleteRowIds as $rowId) {
                    $rowId = sanitize_text_field(wp_unslash($rowId));

                    if ($rowId === '' || $rowId === '0') {
                        continue;
                    }

                    do_action('wpdatatables_excel_before_delete_row', $rowId, $tableId);

                    // If the plugin is using WP DB
                    if (!(Connection::isSeparate($tableData->connection))) {
                        $res = $wpdb->delete($mySqlTableName, array($idColumnKey => $rowId));

                        if ($res === false) {
                            $returnResult['error'] = __('There was an error trying to delete row! Error: ', 'wpdatatables') . $wpdb->last_error;
                        } else {
                            if (!isset($returnResult['success']['deleted'])) {
                                $returnResult['success']['deleted'] = array();
                            }

                            $returnResult['success']['deleted'][] = $rowId;
                        }

                        do_action('wpdatatables_excel_after_delete_row', $rowId, $tableId, $wpdb->last_error);
                    } else {
                        $vendor = Connection::getVendor($tableData->connection);
                        $leftSysIdentifier = Connection::getLeftColumnQuote($vendor);
                        $rightSysIdentifier = Connection::getRightColumnQuote($vendor);
                        $sql = Connection::getInstance($tableData->connection);
                        $formattedId = ToolsService::formatSqlWhereIdValue($rowId, $columnMeta->column_type, $tableData->connection);
                        $query = 'DELETE FROM ' . $mySqlTableName . ' WHERE ' . $leftSysIdentifier . $idColumnKey . $rightSysIdentifier . ' = ' . $formattedId;
                        $sql->doQuery($query);
                        $sqlLastError = $sql->getLastError();
                        if ($sqlLastError != '') {
                            $returnResult['error'] = __('There was an error trying to delete row! Error: ', 'wpdatatables') . $sqlLastError;
                            break;
                        } else {
                            if (!isset($returnResult['success']['deleted'])) {
                                $returnResult['success']['deleted'] = array();
                            }

                            $returnResult['success']['deleted'][] = $rowId;
                        }

                        do_action('wpdatatables_excel_after_delete_row', $rowId, $tableId, $sqlLastError);
                    }
                }

                do_action('wpdatatables_excel_after_delete_all_rows', $tableId, $returnResult);
            } else {
                $returnResult['error'] = 'Supplied column is not id column.';
                echo json_encode($returnResult);
                exit();
            }
        }

        echo json_encode($returnResult);
        exit();

    }
}
