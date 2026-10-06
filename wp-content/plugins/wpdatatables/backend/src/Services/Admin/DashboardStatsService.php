<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Admin;

use WDTConfigController;
use WPDataTable;

/**
 * Admin dashboard table/chart counts and metadata.
 *
 * @package WPDataTables\Services\Admin
 */
class DashboardStatsService
{
    /**
         * Helper function that returns an array with wpDataTables admin pages
         * @return array
         */
        public static function getWpDataTablesAdminPages()
        {
            return array(
                'dashboardUrl' => menu_page_url('wpdatatables-dashboard', false),
                'browseTablesUrl' => menu_page_url('wpdatatables-administration', false),
                'browseChartsUrl' => menu_page_url('wpdatatables-charts', false)
            );
        }

    /**
         * Get table count from database
         *
         * @param $filter
         *
         * @return null|string
         */
        public static function getTablesCount($filter)
        {
            global $wpdb;
            $filter === 'table' ? $tableFromDB = 'wpdatatables' : $tableFromDB = 'wpdatacharts';
            $query = "SELECT COUNT(*) FROM {$wpdb->prefix}$tableFromDB";
            return (int)$wpdb->get_var($query);
        }

    /**
         * Get data for last insert table from database
         *
         * @param $filter
         *
         * @return stdClass
         */
        public static function getLastTableData($filter)
        {
            global $wpdb;
            $filter === 'table' ? $tableFromDB = 'wpdatatables' : $tableFromDB = 'wpdatacharts';
            $query = "SELECT MAX(id) FROM {$wpdb->prefix}$tableFromDB";
            $lastID = $wpdb->get_var($query);
            $chartQuery = $wpdb->prepare(
                "SELECT * 
                            FROM " . $wpdb->prefix . "wpdatacharts 
                            WHERE id = %d",
                $lastID
            );
    
            if ($filter === 'table') {
                return WDTConfigController::loadTableFromDB($lastID);
            } else if ($filter === 'chart') {
                return $wpdb->get_row($chartQuery);
            }
    
        }

    /**
         * Convert Table type for readable content
         *
         * @param $tableType
         *
         * @return string
         */
        public static function getConvertedTableType($tableType)
        {
            switch ($tableType) {
                case 'mysql':
                case 'mssql':
                case 'postgresql':
                    return 'SQL';
                case 'manual':
                    return 'Manual';
                case 'xls':
                    return 'Excel';
                case 'csv':
                    return 'CSV';
                case 'xml':
                    return 'XML';
                case 'wp_posts_query':
                    return 'WP Posts';
                case 'woo_commerce':
                    return 'WooCommerce';
                case 'json':
                    return 'JSON';
                case 'nested_json':
                    return 'Nested JSON';
                case 'serialized':
                    return 'Serialized PHP array';
                case 'google_spreadsheet':
                    return 'Google sheet';
                case 'ivyforms':
                    if (!class_exists('IvyForms\\Services\\API\\IvyFormsAPI')) {
                        return 'Unknown';
                    }
                    return 'IvyForms';
                default:
                    if (in_array($tableType, WPDataTable::$allowedTableTypes)) {
                        return ucfirst($tableType);
                    }
                    return 'Unknown';
            }
    
        }
}
