<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Permissions;

use Connection;

/**
 * Own-row edit/delete permission enforcement.
 *
 * @package WPDataTables\Services\Permissions
 */
class TableRowActionPermissionsChecker
{
    /**
         * Check if current user can update and delete own rows not others
         *
         * @param $tableData
         * @param $mySqlTableName
         * @param $columnsData
         * @param $id
         * @param $action
         *
         */
        public static function checkCurrentUsersActionsPermissions($tableData, $mySqlTableName, $columnsData, $id, $action)
        {
            global $wpdb;
            $idValCheck = 0;
            $idColumnName = '';
            $userIDColumnName = '';
            foreach ($columnsData as $column) {
                if ($column->id_column) {
                    $idColumnName = $column->orig_header;
                    $idValCheck = $action == 'delete' ? $id : (int)$id[$idColumnName];
                } else {
                    // Defining the values for User ID columns and for "none" input types
                    if ($column->id == $tableData->userid_column_id) {
                        $userIDColumnName = $column->orig_header;
                    }
                }
            }
    
            // Own-row editing without a User ID column cannot match rows against a user,
            // so deny the action instead of leaving every row writable
            if ($userIDColumnName === '') {
                if ($action == 'delete') {
                    $returnResult['error'] = __('User do not have permissions to delete this row! ', 'wpdatatables');
                } else {
                    $returnResult['error'] = __('User do not have permission to update data!', 'wpdatatables');
                }
                echo json_encode($returnResult);
                exit();
            }
    
            if (!(Connection::isSeparate($tableData->connection))) {
                if ($idValCheck != '0') {
                    $res = $wpdb->query($wpdb->prepare("SELECT `{$idColumnName}` FROM {$mySqlTableName} WHERE `{$idColumnName}` = %d AND `{$userIDColumnName}` = %d", $idValCheck, get_current_user_id()));
                    if (!$res) {
                        if ($action == 'delete') {
                            $returnResult['error'] = __('User do not have permissions to delete this row! ', 'wpdatatables');
                        } else {
                            $returnResult['error'] = __('User do not have permission to update data!', 'wpdatatables');
                        }
                        echo json_encode($returnResult);
                        exit();
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
                if ($idValCheck != '0') {
                    $query = "SELECT {$leftSysIdentifier}{$idColumnName}{$rightSysIdentifier} FROM {$mySqlTableName} WHERE {$leftSysIdentifier}{$idColumnName}{$rightSysIdentifier} = {$idValCheck} AND {$leftSysIdentifier}{$userIDColumnName}{$rightSysIdentifier} =" . get_current_user_id();
                    if (!$sql->getField($query)) {
                        if ($action == 'delete') {
                            $returnResult['error'] = __('User does not have permissions to delete this row! ', 'wpdatatables');
                        } else {
                            $returnResult['error'] = __('User does not have permission to update data!', 'wpdatatables');
                        }
                        echo json_encode($returnResult);
                        exit();
                    }
                }
            }
        }
}
