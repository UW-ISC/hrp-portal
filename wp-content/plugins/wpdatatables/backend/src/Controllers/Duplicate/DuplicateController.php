<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Duplicate;

use Connection;
use WDTConfigController;
use Exception;
use WPDataTables\Services\Permissions\PermissionsService;

/**
 * DuplicateController — admin-ajax handlers for duplicating a table or a chart.
 *
 * The nonce/cap checks and the wire format ($_POST in, DB writes, then `exit`)
 * keep the existing admin JS unaffected. The bodies flow through the global
 * `WDTConfigController` facade (a shim into the layered config/repository
 * services) and the global `Connection` facade (a shim into
 * ConnectionService/ConnectionFactory). The legacy global functions remain as
 * one-line delegators into this controller.
 *
 * Plain class by design (NOT extending the REST-shaped
 * {@see \WPDataTables\Controllers\Controller}).
 *
 * @package WPDataTables\Controllers\Duplicate
 */
class DuplicateController
{
    /** @var PermissionsService */
    private $permissionsService;

    public function __construct(PermissionsService $permissionsService)
    {
        $this->permissionsService = $permissionsService;
    }

    /**
     * Duplicate the table.
     *
     * @return void
     * @throws Exception
     */
    public function duplicateTable()
    {
        global $wpdb;

        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtDuplicateTableNonce')) {
            exit();
        }

        $tableId = (int)$_POST['table_id'];
        if (empty($tableId)) {
            return false;
        }
        // Duplicate creates a new table from an existing one.
        if (!$this->permissionsService->canCreateTables()
            || !$this->permissionsService->canEditTable($tableId)
        ) {
            exit();
        }
        $manualDuplicateInput = (int)$_POST['manual_duplicate_input'];
        $newTableName = sanitize_text_field($_POST['new_table_name']);

        // Getting the table data
        $tableData = WDTConfigController::loadTableFromDB($tableId);
        $mySqlTableName = $tableData->mysql_table_name;
        $content = $tableData->content;

        if ($tableData->table_type != 'simple') {

            // Create duplicate version of input table if checkbox is selected
            if ($manualDuplicateInput && $tableData->table_type == 'manual') {

                // Generating new input table name
                $cnt = 1;
                $newNameGenerated = false;
                while (!$newNameGenerated) {
                    $id = $wpdb->get_var('SELECT id FROM ' . $wpdb->prefix . 'wpdatatables' . ' ORDER BY id DESC LIMIT 1') + 1;
                    $newName = $wpdb->prefix . 'wpdatatable_' . $id;
                    $checkTableQuery = "SHOW TABLES LIKE '{$newName}'";
                    if (!(Connection::isSeparate($tableData->connection))) {
                        $res = $wpdb->get_results($checkTableQuery);
                        if (!empty($res)) {
                            $newName = $wpdb->prefix . 'wpdatatable_' . $id . '_' . $cnt;
                            $checkTableQuery = "SHOW TABLES LIKE '{$newName}'";
                        }
                        $res = $wpdb->get_results($checkTableQuery);
                    } else {
                        $sql = Connection::getInstance($tableData->connection);
                        $res = $sql->getRow($checkTableQuery);
                        if (!empty($res)) {
                            $newName = $wpdb->prefix . 'wpdatatable_' . $id . '_' . $cnt;
                            $checkTableQuery = "SHOW TABLES LIKE '{$newName}'";
                        }
                        $res = $sql->getRow($checkTableQuery);
                    }
                    if (!empty($res)) {
                        $cnt++;
                    } else {
                        $newNameGenerated = true;
                    }
                }

                // Input table queries

                $vendor = Connection::getVendor($tableData->connection);
                $isMySql = $vendor === Connection::$MYSQL;
                $isMSSql = $vendor === Connection::$MSSQL;
                $isPostgreSql = $vendor === Connection::$POSTGRESQL;

                if ($isMySql) {
                    $query1 = "CREATE TABLE {$newName} LIKE {$tableData->mysql_table_name};";
                    $query2 = "INSERT INTO {$newName} SELECT * FROM {$tableData->mysql_table_name};";
                }

                if ($isMSSql || $isPostgreSql) {
                    $query1 = "SELECT * INTO {$newName} FROM {$tableData->mysql_table_name};";
                }

                if (!(Connection::isSeparate($tableData->connection))) {
                    $wpdb->query($query1);
                    $wpdb->query($query2);
                } else {
                    $sql->doQuery($query1);

                    if ($query2) {
                        $sql->doQuery($query2);
                    }
                }
                $mySqlTableName = $newName;

                if ($tableData->table_type != 'gravity') {
                    $content = str_replace($tableData->mysql_table_name, $newName, $tableData->content);
                } else {
                    $content = $tableData->content;
                }

            }
        }

        // Creating new table
        $wpdb->insert(
            $wpdb->prefix . 'wpdatatables',
            array(
                'title' => $newTableName,
                'show_title' => $tableData->show_title,
                'table_type' => $tableData->table_type,
                'file_location' => $tableData->file_location,
                'connection' => $tableData->connection,
                'content' => $content,
                'filtering' => $tableData->filtering,
                'filtering_form' => $tableData->filtering_form,
                'cache_source_data' => $tableData->cache_source_data,
                'auto_update_cache' => $tableData->auto_update_cache,
                'sorting' => $tableData->sorting,
                'tools' => $tableData->tools,
                'server_side' => $tableData->server_side,
                'editable' => $tableData->editable,
                'inline_editing' => $tableData->inline_editing,
                'popover_tools' => $tableData->popover_tools,
                'editor_roles' => $tableData->editor_roles,
                'mysql_table_name' => $mySqlTableName,
                'edit_only_own_rows' => $tableData->edit_only_own_rows,
                'userid_column_id' => $tableData->userid_column_id,
                'display_length' => $tableData->display_length,
                'auto_refresh' => $tableData->auto_refresh,
                'fixed_columns' => $tableData->fixed_columns,
                'fixed_layout' => $tableData->fixed_layout,
                'responsive' => $tableData->responsive,
                'scrollable' => $tableData->scrollable,
                'word_wrap' => $tableData->word_wrap,
                'hide_before_load' => $tableData->hide_before_load,
                'var1' => $tableData->var1,
                'var2' => $tableData->var2,
                'var3' => $tableData->var3,
                'var4' => $tableData->var4,
                'var5' => $tableData->var5,
                'var6' => $tableData->var6,
                'var7' => $tableData->var7,
                'var8' => $tableData->var8,
                'var9' => $tableData->var9,
                'tabletools_config' => serialize($tableData->tabletools_config),
                'advanced_settings' => $tableData->advanced_settings
            )
        );

        $newTableId = $wpdb->insert_id;

        if ($tableData->table_type != 'simple') {
            // Getting the column data
            $columns = WDTConfigController::loadColumnsFromDB($tableId);

            // Creating new columns
            foreach ($columns as $column) {
                $wpdb->insert(
                    $wpdb->prefix . 'wpdatatables_columns',
                    array(
                        'table_id' => $newTableId,
                        'orig_header' => $column->orig_header,
                        'display_header' => $column->display_header,
                        'filter_type' => $column->filter_type,
                        'column_type' => $column->column_type,
                        'input_type' => $column->input_type,
                        'input_mandatory' => $column->input_mandatory,
                        'id_column' => $column->id_column,
                        'group_column' => $column->group_column,
                        'sort_column' => $column->sort_column,
                        'hide_on_phones' => $column->hide_on_phones,
                        'hide_on_tablets' => $column->hide_on_tablets,
                        'visible' => $column->visible,
                        'sum_column' => $column->sum_column,
                        'skip_thousands_separator' => $column->skip_thousands_separator,
                        'width' => $column->width,
                        'possible_values' => $column->possible_values,
                        'default_value' => $column->default_value,
                        'css_class' => $column->css_class,
                        'text_before' => $column->text_before,
                        'text_after' => $column->text_after,
                        'formatting_rules' => $column->formatting_rules,
                        'calc_formula' => $column->calc_formula,
                        'color' => $column->color,
                        'pos' => $column->pos,
                        'advanced_settings' => $column->advanced_settings,
                    )
                );

                if ($column->id == $tableData->userid_column_id) {
                    $userIdColumnNewId = $wpdb->insert_id;

                    $wpdb->update(
                        $wpdb->prefix . 'wpdatatables',
                        array('userid_column_id' => $userIdColumnNewId),
                        array('id' => $newTableId)
                    );
                }

            }
        } else {
            $rows = WDTConfigController::loadRowsDataFromDB($tableId);
            foreach ($rows as $row) {
                $wpdb->insert(
                    $wpdb->prefix . "wpdatatables_rows",
                    array(
                        'table_id' => $newTableId,
                        'data' => json_encode($row)
                    )
                );
            }
        }

        exit();
    }

    /**
     * Duplicate the chart.
     *
     * @return void
     */
    public function duplicateChart()
    {
        global $wpdb;

        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtDuplicateChartNonce')) {
            exit();
        }

        $chartId = (int)$_POST['chart_id'];
        if (empty($chartId)) {
            return false;
        }
        if (!$this->permissionsService->canCreateCharts()
            || !$this->permissionsService->canEditChart($chartId)
        ) {
            exit();
        }
        $newChartName = sanitize_text_field($_POST['new_chart_name']);

        $chartQuery = $wpdb->prepare(
            'SELECT * FROM ' . $wpdb->prefix . 'wpdatacharts WHERE id = %d',
            $chartId
        );

        $wpDataChart = $wpdb->get_row($chartQuery);

        // Creating new table
        $wpdb->insert(
            $wpdb->prefix . "wpdatacharts",
            array(
                'wpdatatable_id' => $wpDataChart->wpdatatable_id,
                'title' => $newChartName,
                'engine' => $wpDataChart->engine,
                'type' => $wpDataChart->type,
                'json_render_data' => $wpDataChart->json_render_data
            )
        );

        exit();
    }
}
