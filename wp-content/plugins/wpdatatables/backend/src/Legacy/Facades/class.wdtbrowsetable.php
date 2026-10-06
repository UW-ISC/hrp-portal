<?php

defined('ABSPATH') or die('Access denied.');

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Class WDTBrowseTable
 *
 * Legacy alias retained for back-compat — the table admin page controller and premium add-ons (e.g. Folders)
 * instantiate `new WDTBrowseTable()` directly. The implementation now lives in
 * {@see \WPDataTables\Plugin\Admin\ListTables\BrowseTablesListTable}; this empty
 * subclass keeps the historical global class name working (same pattern as the
 * filtering widget).
 */
class WDTBrowseTable extends \WPDataTables\Plugin\Admin\ListTables\BrowseTablesListTable
{
}
