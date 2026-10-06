<?php

defined('ABSPATH') or die('Access denied.');

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Class WDTPermissionsListTable
 *
 * Legacy alias retained for back-compat — the permissions admin screen
 * ({@see \WPDataTables\Plugin\Admin\PermissionsPageController}) require_once's
 * this file and instantiates `new WDTPermissionsListTable([...])`. The
 * implementation now lives in
 * {@see \WPDataTables\Plugin\Admin\ListTables\PermissionsListTable}; this
 * empty subclass keeps the historical global class name working.
 */
class WDTPermissionsListTable extends \WPDataTables\Plugin\Admin\ListTables\PermissionsListTable
{
}
