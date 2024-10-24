<?php

use WDTIntegration\WPDataFolders;

defined('ABSPATH') or die('Access denied.');
class WPDataChartsFolders extends WPDataFolders{
    private static $parentConstructorCalled = false;
    public function __construct() {
        if (!self::$parentConstructorCalled) {
            parent::__construct();
            self::$parentConstructorCalled = true;
        }
        $this->setItemsDB('wpdatacharts');
        $this->setItemsPage('wpdatatables-charts');
        $this->setType('chart');
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * @param string $itemsDB
     */
    public function setItemsDB($itemsDB)
    {
        $this->_itemsDB = $itemsDB;
    }

    /**
     * @param string $itemsPage
     */
    public function setItemsPage($itemsPage)
    {
        $this->_itemsPage = $itemsPage;
    }
    /**
     * @return string
     */
    public function getItemsDB()
    {
        global $wpdb;
        return $wpdb->prefix . $this->_itemsDB;
    }

    public function getItemsPage()
    {
        return $this->_itemsPage;
    }

    public static function getAll()
    {
        return WPDataChart::getAll();
    }
    public function filterBrowseAllColumns($allColumns)
    {
        $allColumns =  ["move" => '1'] + $allColumns;
        return array_slice($allColumns, 0, 3, true) +
            array("folders" => __('Folders', 'wpdatatables')) +
            array_slice($allColumns, 3, count($allColumns) - 1, true);
    }

    public function getPredefinedOrderByValue(){
        return ['id', 'title', 'engine', 'type'];
    }

    public function filterBrowseQuery()
    {

        $predifinedOrderByValue = $this->getPredefinedOrderByValue();
        $orderByValue = 'id';
        $defaultSortingOrder = get_option('wdtSortingOrderBrowseTables');

        $query = "SELECT * FROM
         (SELECT
              1 AS move,
              s.id,
              s.title,
              f.type AS typeFolders,
              tm.type AS typeMetaFolders,
              GROUP_CONCAT('(',f.id,')' ORDER BY f.id ASC SEPARATOR ', ') AS folderIDs,
              GROUP_CONCAT(f.name ORDER BY f.id ASC SEPARATOR ', ') AS folders,
              s.engine,
              s.type
          FROM {$this->getItemsDB()} AS s
            LEFT JOIN {$this->getFoldersMetaDB()} AS tm ON s.id = tm.type_id
               AND tm.type ='{$this->getType()}'
            LEFT JOIN {$this->getFoldersDB()} AS f ON tm.folder_id = f.id
          GROUP BY s.id, f.type, tm.type, s.engine, s.type) as t";


        if (isset($_REQUEST['s']) || (isset($_REQUEST['folderID']) && $_REQUEST['folderID'] != -1)) {
            $query .= ' WHERE ';
        }

        if (isset($_REQUEST['s'])) {
            if (is_numeric($_REQUEST['s'])) {
                $query .= " t.id LIKE '" . sanitize_text_field($_REQUEST['s']) . "'";
            } else {
                $query .= " t.title LIKE '%" . sanitize_text_field($_REQUEST['s']) . "%'";
            }
        }

        if (isset($_REQUEST['folderID']) && $_REQUEST['folderID'] != -1) {
            $folderID = intval($_REQUEST['folderID']);
            if (!substr($query, -strlen('WHERE ')) === 'WHERE ')
                $query .= " OR ";
            if ($folderID == 0) {
                $query .= " t.folderIDs IS NULL";
            } else {
                $query .= " t.folderIDs LIKE '%(" . $folderID . ")%'";
            }
        }

        if (isset($_REQUEST['orderby'])) {
            if (in_array($_REQUEST['orderby'], $predifinedOrderByValue)) {

                $requestOrderByValue = sanitize_text_field($_REQUEST['orderby']);
                foreach ($predifinedOrderByValue as $value) {
                    if ($requestOrderByValue === $value) {
                        $orderByValue = $value;
                    }
                }
                $query .= " ORDER BY " . $orderByValue;
                if ($_REQUEST['order'] == 'desc') {
                    $query .= " DESC";
                } else {
                    $query .= " ASC";
                }
            }
        } else {
            $query .= " ORDER BY t.id " . $defaultSortingOrder . ' ';
        }

        if (isset($_REQUEST['paged'])) {
            $paged = (int)$_REQUEST['paged'];
        } else {
            $paged = 1;
        }

        $tablesPerPage = get_option('wdtTablesPerPage') ? get_option('wdtTablesPerPage') : 10;
        $query .= " LIMIT " . ($paged - 1) * $tablesPerPage . ", " . $tablesPerPage;

        return $query;
    }

    public function filterBrowseCountQuery()
    {
        $query = "SELECT COUNT(*) FROM
         (SELECT
              s.id,
              s.title,
              f.type AS typeFolders,
              tm.type AS typeMetaFolders,
              GROUP_CONCAT('(', f.id, ')' ORDER BY f.id ASC SEPARATOR ', ') AS folderIDs,
              GROUP_CONCAT(f.name ORDER BY f.id ASC SEPARATOR ', ') AS folders,
              s.engine,
              s.type
          FROM {$this->getItemsDB()} AS s
            LEFT JOIN {$this->getFoldersMetaDB()} AS tm ON s.id = tm.type_id
              AND tm.type ='{$this->getType()}'
            LEFT JOIN {$this->getFoldersDB()} AS f ON tm.folder_id = f.id 
          GROUP BY s.id, s.title, f.type, tm.type, s.engine, s.type) as t";

        if (isset($_REQUEST['s']) || (isset($_REQUEST['folderID']) && $_REQUEST['folderID'] != -1)) {
            $query .= ' WHERE ';
        }

        if (isset($_REQUEST['s'])) {
            if (is_numeric($_REQUEST['s'])) {
                $query .= " t.id LIKE '" . sanitize_text_field($_REQUEST['s']) . "'";
            } else {
                $query .= " t.title LIKE '%" . sanitize_text_field($_REQUEST['s']) . "%'";
            }
        }

        if (isset($_REQUEST['folderID']) && $_REQUEST['folderID'] != -1) {
            $folderID = intval($_REQUEST['folderID']);
            if (!substr($query, -strlen('WHERE ')) === 'WHERE ')
                $query .= " OR ";
            if ($folderID == 0) {
                $query .= " t.folderIDs IS NULL ";
            } else {
                $query .= " t.folderIDs LIKE '%(" . $folderID . ")%' ";
            }
        }

        return $query;
    }
}
