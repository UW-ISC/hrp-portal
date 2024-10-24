<?php

namespace WDTIntegration;

use WDTBrowseChartsTable;
use WDTBrowseTable;
use WPDataFoldersFactory;

defined('ABSPATH') or die('Access denied.');

define('WDT_FOLDERS_INTEGRATION', true);

class WPDataFolders
{
    protected $_type = '';
    protected $_itemsPage = '';
    protected $_itemsDB = '';
    private $_foldersDB = 'wpdatatables_folders';
    private $_foldersMetaDB = 'wpdatatables_folders_meta';

    /**
     * @return string
     */
    protected function getType()
    {
        return $this->_type;
    }

    /**
     * @return mixed
     */
    public function getAllowedTypes()
    {
        return ['table', 'chart', 'report'];
    }

    public function getFoldersDB()
    {
        global $wpdb;
        return $wpdb->prefix . $this->_foldersDB;
    }

    public function getFoldersMetaDB()
    {
        global $wpdb;
        return $wpdb->prefix . $this->_foldersMetaDB;
    }

    public function __construct()
    {
        // Create and delete folders DB table on activate/uninstall plugin
        add_action('wpdatatables_after_activation_method', [$this, 'createDBTable']);
        add_action('wpdatatables_after_uninstall_method', [$this, 'deleteDBTable']);

        // Enqueue scripts and styles
        add_action('wpdatatables_browse_page', [$this, 'enqueueScript']);
        add_action('reportbuilder_browse_page', [$this, 'enqueueScript']);
        add_action('wpdatatables_browse_charts_page', [$this, 'enqueueScript']);

        // Add Enable Folders setting
        add_action('wpdatatables_add_browse_table_card', [$this, 'addBrowseTableCard']);
        add_action('reportbuilder_add_browse_table_card', [$this, 'addBrowseTableCard']);

        // Remove all known scripts from plugins that use jstree lib
        add_action('wp_print_scripts', [$this, 'dequeueScripts'], 10000);
        add_action('wp_print_styles', [$this, 'dequeueStyles'], 10000);
        add_action('admin_enqueue_scripts', [$this, 'dequeueStyles'], 10000);

        // Assign table/tables toFolder
        add_action('wp_ajax_wpdatatables_assign_items_in_folder', [$this, 'assignItemsToFolder']);
        add_action('wp_ajax_wpdatatables_assign_item_in_folder', [$this, 'assignItemToFolder']);

        // Update folders relations if item is deleted
        add_action('wpdatatables_after_delete_tables', [$this, 'deleteItem'], 10, 2);
        add_action('wpdatatables_after_delete_charts', [$this, 'deleteItem'], 10, 2);
        add_action('reportbuilder_after_delete_reports', [$this, 'deleteItem'], 10, 2);

        // Save Folder
        add_action('wp_ajax_wpdatatables_folder_actions', [$this, 'folderActions']);
        // Edit Folder
        add_action('wp_ajax_wpdatatables_rename_folder', [$this, 'renameFolder']);
        // Delete Folder
        add_action('wp_ajax_wpdatatables_delete_folder', [$this, 'deleteFolder']);
        // Remove Folder
        add_action('wp_ajax_wpdatatables_remove_folder_tag', [$this, 'removeFolderTag']);

        // Set sort order for folders
        add_action('wp_ajax_wpdatatables_sort_order_folders', [$this, 'sortFolders']);
        // Set show/collapse folders
        add_action('wp_ajax_wpdatatables_collapse_folders', [$this, 'collapseFolders']);
        // Set show children option for folders
        add_action('wp_ajax_wpdatatables_show_children_folders', [$this, 'showChildrenFolders']);
        // Set browse columns visibility
        add_action('wp_ajax_wpdatatables_save_browse_columns', [$this, 'hideColumns']);

        // Filter Chart Browse page
        add_filter('wpdatatables_filter_browse_charts_all_columns', [$this, 'filterAllColumns'], 10, 2);
        add_filter('wpdatatables_filter_browse_charts_query', [$this, 'filterQuery'], 10, 2);
        add_filter('wpdatatables_filter_browse_charts_order_current_url', [$this, 'filterPaginationCurrentUrl'], 10, 2);
        add_filter('wpdatatables_filter_browse_charts_column_headers', [$this, 'filterColumnHeaders'], 10, 2);
        add_filter('wpdatatables_filter_browse_charts_pagination_page_current_url', [$this, 'filterColumnHeaders'], 10, 2);

        // Filter Report Browse page
        add_filter('reportbuilder_filter_browse_reports_all_columns', [$this, 'filterAllColumns'], 10, 2);
        add_filter('reportbuilder_filter_browse_reports_query', [$this, 'filterQuery'], 10, 2);
        add_filter('reportbuilder_filter_browse_reports_count_query', [$this, 'filterCountQuery'], 10, 2);
        add_filter('reportbuilder_filter_browse_reports_order_current_url', [$this, 'filterPaginationCurrentUrl'], 10, 2);
        add_filter('reportbuilder_filter_browse_reports_column_headers', [$this, 'filterColumnHeaders'], 10, 2);
        add_filter('reportbuilder_filter_browse_reports_pagination_page_current_url', [$this, 'filterColumnHeaders'], 10, 2);

        // Filter Table Browse page
        add_filter('wpdatatables_filter_browse_tables_all_columns', [$this, 'filterAllColumns'], 10, 2);
        add_filter('wpdatatables_filter_browse_tables_query', [$this, 'filterQuery'], 10, 2);
        add_filter('wpdatatables_filter_browse_tables_count_query', [$this, 'filterCountQuery'], 10, 2);
        add_filter('wpdatatables_filter_browse_tables_pagination_page_url', [$this, 'filterPaginationUrl'], 10, 4);
        add_filter('wpdatatables_filter_browse_tables_pagination_page_current_url', [$this, 'filterPaginationCurrentUrl'], 10, 2);
        add_filter('wpdatatables_filter_browse_tables_order_current_url', [$this, 'filterPaginationCurrentUrl'], 10, 2);
        add_filter('wpdatatables_filter_browse_tables_column_headers', [$this, 'filterColumnHeaders'], 10, 2);
        // Add data in columns for browse table
        add_filter('wpdatatables_browse_tables_column_name_folders', [$this, 'addBrowseColumnFoldersData'], 10, 3);
        add_filter('wpdatatables_browse_tables_column_name_move', [$this, 'addBrowseColumnMoveData'], 10, 3);

        // Add data in columns for reports browse table
        add_filter('reportbuilder_browse_reports_column_name_folders', [$this, 'addBrowseColumnFoldersData'], 10, 3);
        add_filter('reportbuilder_browse_reports_column_name_move', [$this, 'addBrowseColumnMoveData'], 10, 3);

        // Reload browse lists table
        add_action('wp_ajax_wpdatatables_filter_browse_lists_table', [$this, 'getListTable']);

        $this->setSettings();
    }

    public function addBrowseTableCard()
    {
        ob_start();
        include_once WDT_PRO_INTEGRATIONS_PATH . 'folders/templates/browse_table_insert_card.inc.php';
        $browseTableCard = ob_get_contents();
        ob_end_clean();

        echo $browseTableCard;
    }

    public function dequeueScripts()
    {
        $wpdtPage = isset($_GET['page']) ? $_GET['page'] : '';
        if (is_admin() &&
            (strpos($wpdtPage, 'wpdatatables') !== false) ||
            (strpos($wpdtPage, 'wpdatareports') !== false)
        ) {
            wp_deregister_script("jquery-jstree");
            wp_deregister_script("wcp-folders-jstree");
            wp_deregister_script("wcp-folders-custom");
            wp_deregister_script("folders-overlayscrollbars");
            wp_deregister_script("folders-jstree");
            wp_deregister_script("folders-tree");
            wp_deregister_script("wcp-folders-media");
            wp_deregister_script("wcp-jquery-touch");
            wp_deregister_script("fbv-folder");
            wp_deregister_script("fbv-lib");

            wp_dequeue_script("jquery-jstree");
            wp_dequeue_script("wcp-folders-jstree");
            wp_dequeue_script("wcp-folders-custom");
            wp_dequeue_script("folders-overlayscrollbars");
            wp_dequeue_script("folders-jstree");
            wp_dequeue_script("folders-tree");
            wp_dequeue_script("wcp-folders-media");
            wp_dequeue_script("wcp-jquery-touch");
            wp_dequeue_script("fbv-folder");
            wp_dequeue_script("fbv-lib");

        }
    }

    public function dequeueStyles()
    {
        $wpdtPage = isset($_GET['page']) ? $_GET['page'] : '';
        if (is_admin() &&
            (strpos($wpdtPage, 'wpdatatables') !== false) ||
            (strpos($wpdtPage, 'wpdatareports') !== false)
        ) {
            wp_deregister_style("folders-media");
            wp_deregister_style('folders-jstree');
            wp_deregister_style('folder-overlayscrollbars');
            wp_deregister_style('folder-folders');
            wp_deregister_style('folders-media');
            wp_deregister_style('folder-icon');

            wp_deregister_style('wcp-folders-fa');
            wp_deregister_style('wcp-folders-admin');
            wp_deregister_style('wcp-folders-jstree');
            wp_deregister_style('folder-overlayscrollbars');
            wp_deregister_style('wcp-folders-css');

            wp_dequeue_style("folders-media");
            wp_dequeue_style('folders-jstree');
            wp_dequeue_style('folder-folders');
            wp_dequeue_style('folders-media');
            wp_dequeue_style('folder-icon');

            wp_dequeue_style("folders-media-css");
            wp_dequeue_style('folders-jstree-css');
            wp_dequeue_style('folder-overlayscrollbars-css');
            wp_dequeue_style('folder-folders-css');
            wp_dequeue_style('folders-media-css');
            wp_dequeue_style('folder-icon-css');

            wp_dequeue_style('wcp-folders-fa');
            wp_dequeue_style('wcp-folders-admin');
            wp_dequeue_style('wcp-folders-jstree');
            wp_dequeue_style('wcp-folders-css');
        }
    }

    public function getAllUnassigned()
    {
        global $wpdb;

        $query = "SELECT (
            SELECT COUNT(*) FROM {$this->getItemsDB()}) - (SELECT COUNT(DISTINCT type_id) 
            FROM {$this->getFoldersMetaDB()} 
            WHERE type ='{$this->getType()}') AS not_assigned";

        $res = $wpdb->get_row($query, ARRAY_A);

        return $res['not_assigned'];
    }


    private function setSettings()
    {
        if (!get_option('wdtFolderOptions')) {
            update_option('wdtFolderOptions', json_encode(array(
                'table' => [
                    "sort" => 'ASC',
                    "showChildren" => false,
                    "collapsed" => false,
                    "columnsToHide" => []
                ],
                'chart' => [
                    "sort" => 'ASC',
                    "showChildren" => false,
                    "collapsed" => false,
                    "columnsToHide" => []
                ],
                'report' => [
                    "sort" => 'ASC',
                    "showChildren" => false,
                    "collapsed" => false,
                    "columnsToHide" => []
                ],
            )));
        }
    }

    public function getSettings()
    {
        return (array)json_decode(get_option('wdtFolderOptions'), true);
    }

    private function getSortCondition($type)
    {
        $settings = $this->getSettings();
        $sort = isset($settings[$type]['sort']) ? $settings[$type]['sort'] : '';

        if ($sort == 'ASC') return 'f.name ASC';
        if ($sort == 'DESC') return 'f.name DESC';
        if ($sort == 'NEW') return 'f.id DESC';
        if ($sort == 'OLD') return 'f.id ASC';

        return 'f.name ASC';
    }

    public function filterAllColumns($allColumns, $type)
    {
        $instance = WPDataFoldersFactory::createTypeBased($type);
        if (!$instance)
            return $allColumns;

        return $instance->filterBrowseAllColumns($allColumns);
    }

    public function filterColumnHeaders($columns, $type)
    {
        if (!empty($columns['move'])) {
            $columns['move'] =
                "<div class='wpdt-move-multiple-" . $type . "s' 
                title='" . esc_html__('Move selected items', 'folders') . "'>
                    <i class='wpdt-icon-move'></i>
                </div>";
        }

        return $columns;
    }

    public function filterCountQuery($query, $type)
    {
        $instance = WPDataFoldersFactory::createTypeBased($type);
        if (!$instance)
            return $query;

        return $instance->filterBrowseCountQuery();
    }

    public function filterQuery($query, $type)
    {
        $instance = WPDataFoldersFactory::createTypeBased($type);
        if (!$instance)
            return $query;

        return $instance->filterBrowseQuery();
    }

    public function filterPaginationUrl($url, $link, $search_term_temp, $current_url)
    {
        return esc_url(add_query_arg("paged", $link . $search_term_temp, $current_url));
    }

    public function filterPaginationCurrentUrl($currentUrl, $type)
    {
        if (isset($_REQUEST['params'])) {
            $folderID = intval($_POST['folderID']);
            $params = $_POST['params'];
            $queryString = '';
            $instance = WPDataFoldersFactory::createTypeBased($type);
            if (!$instance)
                return $currentUrl;
            $predefinedOrderByValue = $instance->getPredefinedOrderByValue();
            foreach ($params as $key => $param) {
                if ($key == 'page') $queryString .= 'page=' . $instance->getItemsPage() . '&';
                if ($key == 'folderID') $queryString .= 'folderID=' . intval($param) . '&';
                if ($key == 'paged') $queryString .= 'paged=' . intval($param) . '&';
                if ($key == 'order') $queryString .= 'order=' . ($param == 'asc' ? 'asc' : 'desc') . '&';
                if ($key == 'orderby') $queryString .= 'orderby=' . in_array($param, $predefinedOrderByValue) ? sanitize_text_field($param) : 'id' . '&';
                if ($key == 's') $queryString .= 's=' . sanitize_text_field($param) . '&';
                if ($key == $instance->getType() . '_id') $queryString .= $instance->getType() . '_id=' . intval($param) . '&';
            }
            if (!isset($params['folderID'])) {
                $queryString .= 'folderID=' . $folderID . '&';
            }
            // Remove the trailing '&' character
            $queryString = substr_replace($queryString, "", -1);

            $currentUrl = admin_url() . 'admin.php?' . $queryString;
        }

        return $currentUrl;
    }

    public function setUrlArgs($request, $instance)
    {
        $tempData = [];
        $predifinedOrderByValue = $instance->getPredefinedOrderByValue();
        if (isset($request['params']))
            $request = $request['params'];

        if (isset($request['page']) && $request['page'] === $instance->getItemsPage())
            $tempData['page'] = $instance->getItemsPage();
        if (isset($request['s']))
            $tempData['s'] = sanitize_text_field($request['s']);
        if (isset($request['paged']))
            $tempData['paged'] = (int)($request['paged']);
        if (isset($request['order']))
            $tempData['order'] = ($request['order']) == 'desc' ? 'desc' : 'asc';
        if (isset($request['orderby']) && in_array($request['orderby'], $predifinedOrderByValue))
            $tempData['orderby'] = sanitize_text_field($request['orderby']);

        return $tempData;
    }

    public function addBrowseColumnFoldersData($columnName, $item, $type)
    {
        if (isset($item['folders']) && $item['folders']) {
            $return_string = '';
            $instance = WPDataFoldersFactory::createTypeBased($type);
            if (!$instance)
                return $return_string;
            $urlArg = $this->setUrlArgs($_REQUEST, $instance);
            if (strpos($item['folderIDs'], ',') !== false) {
                $tempFolderIDs = explode(',', $item['folderIDs']);
                $tempFolderNames = explode(',', $item['folders']);
                foreach ($tempFolderIDs as $key => $tempFolderID) {
                    $tempFolderID = str_replace(['(', ')'], '', $tempFolderID);
                    $urlArg['folderID'] = intval($tempFolderID);
                    $return_string .=
                        ' <span class="wpdt-folder-tag">
                            <a class="wpdt-folder-tag-link wpdt-node-class-id-' . intval($tempFolderID) . '" 
                            href="' . esc_url(add_query_arg($urlArg, 'admin.php?')) . '">' . trim(esc_attr($tempFolderNames[$key])) . '
                            </a>
                            <span class="wpdt-reassign-folder" 
                            data-folder-id="' . intval($tempFolderID) . '" 
                            data-' . $type . '-id="' . intval($item['id']) . '">
                            </span>
                        </span>';
                }
            } else {
                $item['folderIDs'] = str_replace(['(', ')'], '', $item['folderIDs']);
                $urlArg['folderID'] = intval($item['folderIDs']);
                $return_string .=
                    ' <span class="wpdt-folder-tag">
                        <a class="wpdt-folder-tag-link wpdt-node-class-id-' . intval($item['folderIDs']) . '" 
                        href="' . esc_url(add_query_arg($urlArg, 'admin.php?')) . '">' . esc_attr($item['folders']) . '
                        </a>
                        <span class="wpdt-reassign-folder" 
                        data-folder-id="' . intval($item['folderIDs']) . '" 
                        data-' . $type . '-id="' . intval($item['id']) . '">
                        </span>
                    </span>';
            }

            return rtrim($return_string, ",");
        }
    }

    public function addBrowseColumnMoveData($columnName, $item, $type)
    {
        if (isset($item['move']) && $item['move']) {
            $title = ($type == 'report') ? $item['name'] : $item['title'];
            return sprintf(
                '<div class="wpdt-move-%s" data-drag-%s-id="%d" data-folders-name="%s"><i class="wpdt-icon-move"></i><div class="wpdt-%s-info">%s ID:%d</div></div>', $type, $type, $item['id'], $item['folders'], $type, $title, $item['id']
            );
        }
    }


    public function getAllFolders()
    {
        global $wpdb;
        $res = $wpdb->get_results(
            "SELECT
                        f.id AS id,
                        f.type AS type,
                        IFNULL(f.parent_id , '#')  AS parent,
                        f.name AS text,
                        COUNT(t.id) AS item_count
                    FROM
                         {$this->getFoldersDB()} f
                    LEFT JOIN
                         {$this->getFoldersMetaDB()} t ON f.id = t.folder_id
                    LEFT JOIN
                         {$this->getItemsDB()} s ON s.id = t.type_id
                    WHERE f.type='{$this->getType()}'
                    GROUP BY
                        f.id, f.name
                    ORDER BY
                       {$this->getSortCondition($this->getType())}", OBJECT
        );
        if (empty($res))
            return [];
        return $res;
    }

    public function createDBTable()
    {
        $foldersTableName = $this->getFoldersDB();
        $foldersSql = "CREATE TABLE {$foldersTableName} (
                                  id bigint(20) NOT NULL AUTO_INCREMENT,
                                  type enum('table','chart','report') NOT NULL,
                                  parent_id int(11) DEFAULT NULL,
                                  name VARCHAR(50) NOT NULL,
                                  UNIQUE KEY id (id)
                                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
        $foldersToMetaName = $this->getFoldersMetaDB();
        $foldersMetaSql = "CREATE TABLE {$foldersToMetaName} (
                                  id bigint(20) NOT NULL AUTO_INCREMENT,
                                  type enum('table','chart','report') NOT NULL,
                                  folder_id int(11) NOT NULL,
                                  type_id int(11) NOT NULL,
                                  UNIQUE KEY id (id)
                                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($foldersSql);
        dbDelta($foldersMetaSql);
    }

    public function deleteDBTable()
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$this->getFoldersDB()}");
        $wpdb->query("DROP TABLE IF EXISTS {$this->getFoldersMetaDB()}");
    }

    public function enqueueScript()
    {
        $instance = '';
        if (isset($_GET['page'])) {
            $instance = WPDataFoldersFactory::createPageBased($_GET['page']);
        }
        if (!$instance) return;
        wp_enqueue_script('wpdt-folders-jstree-js', WDT_PRO_INTEGRATIONS_URL . 'folders/assets/js/jstree.min.js', array('jquery'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wpdt-overlayscroll-js', WDT_PRO_INTEGRATIONS_URL . 'folders/assets/js/overlayscrollbars.min.js', array('jquery'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wpdt-custom-folders-js', WDT_PRO_INTEGRATIONS_URL . 'folders/assets/js/wpdt-folders.min.js', array('jquery'), WDT_CURRENT_VERSION, true);

        wp_enqueue_style('wdt-wpdt-icons', WDT_ROOT_URL . 'assets/css/style.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wpdt-folders-jstree-css', WDT_PRO_INTEGRATIONS_URL . 'folders/assets/css/jstree.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wpdt-custom-folders-css', WDT_PRO_INTEGRATIONS_URL . 'folders/assets/css/wpdt-folders.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wpdt-overlayscrollbars-css', WDT_PRO_INTEGRATIONS_URL . 'folders/assets/css/overlayscrollbars.css', array(), WDT_CURRENT_VERSION);

        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');

        wp_localize_script(
            'wpdt-custom-folders-js',
            'wpdatatables_folders_data',
            [
                'all' => count($instance->getAll()),
                'unassigned' => $instance->getAllUnassigned(),
                'folders' => $instance->getAllFolders(),
                'settings' => $instance->getSettings(),
            ]
        );
    }

    public function assignItemsToFolder()
    {
        $response = [
            'error_status' => 0,
            'success' => '',
            'error' => '',
            'data' => []
        ];

        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtFoldersNonce')) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Unable to assign the table to a folder, your request is not valid!", 'wpdatatables');
        }

        if (!current_user_can("manage_options")) {
            $response['error_status'] = 1;
            $response['error'] = esc_html__("You have not permission to assign the table to a folder", 'wpdatatables');
        }

        if (!(isset($_POST['type']) && in_array($_POST['type'], $this->getAllowedTypes()))) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Unable to assign the table to a folder, your type is not valid!", 'wpdatatables');
        }

        if (!$response['error_status']) {
            global $wpdb;
            $instance = WPDataFoldersFactory::createTypeBased(
                sanitize_text_field($_POST['type'])
            );
            if ($instance == '') {
                $response['error_status'] = 1;
                $response['error'] =
                    esc_html__("Unable to assign the item to a folder, your type is not valid!", 'wpdatatables');
                echo json_encode($response);
                exit();
            }
            $itemIDs = array_map('intval', $_POST['item_ids']);
            $folderID = intval($_POST['folder_id']);
            $folderName = sanitize_text_field($_POST['folder_name']);

            $dbTable = $instance->getFoldersMetaDB();
            $type = $instance->getType();
            $alreadyAssignItems = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT type_id FROM " . $dbTable . "
                    WHERE type=%s 
                    AND folder_id = %d",
                    $type,
                    $folderID
                ), ARRAY_A
            );
            if ($wpdb->last_error !== '') {
                $response['error'] = $wpdb->last_error;
                echo json_encode($response);
                exit();
            }
            $alreadyAssignItemsFinal = [];
            foreach ($alreadyAssignItems as $item) {
                // Check if items are already assigned
                if (in_array($item['type_id'], $itemIDs)) {
                    $response['error'] = esc_html__("Table is already assigned to this folder!", 'wpdatatables');;
                    echo json_encode($response);
                    exit();
                }
                // Check if the 'value' key exists in the second level
                if (array_key_exists('type_id', $item)) {
                    // Extract and add the 'value' to the new array
                    $alreadyAssignItemsFinal[] = (int)$item['type_id'];
                }
            }
            $newItemIDs = array_diff($itemIDs, $alreadyAssignItemsFinal);

            foreach ($newItemIDs as $newItemID) {
                $wpdb->insert(
                    $dbTable,
                    array(
                        'type' => $type,
                        'type_id' => $newItemID,
                        'folder_id' => $folderID,
                    )
                );
            }

            if ($wpdb->last_error !== '') {
                $response['error'] = $wpdb->last_error;
                echo json_encode($response);
                exit();
            }
            $assignedItemsToFolder = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT COUNT(*) as itemCount FROM " . $dbTable . "
                    WHERE type=%s 
                    AND folder_id = %d",
                    $type,
                    $folderID
                )
            );
            if ($wpdb->last_error !== '') {
                $response['error'] = $wpdb->last_error;
                echo json_encode($response);
                exit();
            }

            $response['success'] = 1;
            $response['data'] = [
                'item_ids' => $newItemIDs,
                'item_page' => $instance->getItemsPage(),
                'folder_id' => $folderID,
                'folder_name' => $folderName,
                'item_count' => $assignedItemsToFolder->itemCount,
                'unassigned' => $instance->getAllUnassigned(),
                'type' => $type,
                'type_caps' => ucfirst($type),
            ];

        }

        echo json_encode($response);
        exit();
    }

    public function assignItemToFolder()
    {
        $response = [
            'error_status' => 0,
            'success' => '',
            'error' => '',
            'data' => []
        ];

        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtFoldersNonce')) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Unable to assign the table to a folder, your request is not valid!", 'wpdatatables');
        }

        if (!current_user_can("manage_options")) {
            $response['error_status'] = 1;
            $response['error'] = esc_html__("You have not permission to assign the table to a folder", 'wpdatatables');
        }

        if (!(isset($_POST['type']) && in_array($_POST['type'], $this->getAllowedTypes()))) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Unable to assign the table to a folder, your type is not valid!", 'wpdatatables');
        }

        if (!$response['error_status']) {
            global $wpdb;
            $instance = WPDataFoldersFactory::createTypeBased(
                sanitize_text_field($_POST['type'])
            );
            if ($instance == '') {
                $response['error_status'] = 1;
                $response['error'] =
                    esc_html__("Unable to assign the item to a folder, your type is not valid!", 'wpdatatables');
                echo json_encode($response);
                exit();
            }
            $itemID = intval($_POST['item_id']);
            $itemCount = intval($_POST['item_count']);
            $itemType = $instance->getType();
            $folderID = intval($_POST['folder_id']);
            $folderName = sanitize_text_field($_POST['folder_name']);

            $dbTable = $instance->getFoldersMetaDB();
            $alreadyAssign = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id FROM " . $dbTable . "
                    WHERE type=%s 
                    AND type_id = %d 
                    AND folder_id = %d",
                    $itemType,
                    $itemID,
                    $folderID
                )
            );
            if ($alreadyAssign) {
                $response['error'] = esc_html__("Table is already assigned to this folder!", 'wpdatatables');;
                echo json_encode($response);
                exit();
            }
            if ($wpdb->last_error !== '') {
                $response['error'] = $wpdb->last_error;
                echo json_encode($response);
                exit();
            }

            $wpdb->insert(
                $dbTable,
                array(
                    'type' => $itemType,
                    'type_id' => $itemID,
                    'folder_id' => $folderID,
                )
            );

            if ($wpdb->last_error !== '') {
                $response['error'] = $wpdb->last_error;
                echo json_encode($response);
                exit();
            }
            $assignedItemsToFolder = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT COUNT(*) as itemCount FROM " . $dbTable . "
                    WHERE type = %s 
                    AND folder_id = %d",
                    $itemType,
                    $folderID
                )
            );
            if ($wpdb->last_error !== '') {
                $response['error'] = $wpdb->last_error;
                echo json_encode($response);
                exit();
            }

            $response['success'] = 1;
            $response['data'] = [
                'item_id' => $itemID,
                'folder_id' => $folderID,
                'folder_name' => $folderName,
                'item_count' => $assignedItemsToFolder->itemCount,
                'unassigned' => $instance->getAllUnassigned(),
                'type' => $itemType,
                'type_caps' => ucfirst($itemType)
            ];

        }

        echo json_encode($response);
        exit();
    }

    public function folderActions()
    {
        $response = [
            'error_status' => 0,
            'success' => '',
            'error' => '',
            'data' => []
        ];
        $action = sanitize_text_field($_POST['folder_action']);

        if (!in_array($action, ['insert', 'update', 'delete'])) {
            $response['error_status'] = 1;
            $response['error'] = esc_html__("Unknown action, your request is not valid!", 'wpdatatables');
        }

        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtFoldersNonce')) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Unable to ", 'wpdatatables')
                . $action .
                esc_html__(" folder, your request is not valid!", 'wpdatatables');
        }

        if (!current_user_can("manage_options")) {
            $response['error_status'] = 1;
            $response['error'] = esc_html__("You have not permission to add folder", 'wpdatatables');
        }

        if (!(isset($_POST['type']) && in_array($_POST['type'], $this->getAllowedTypes()))) {
            $response['error_status'] = 1;
            $response['error'] = esc_html__("Unknown type, your request is not valid!", 'wpdatatables');
        }

        if (!$response['error_status']) {
            global $wpdb;
            $instance = WPDataFoldersFactory::createTypeBased(
                sanitize_text_field($_POST['type'])
            );
            if ($instance == '') {
                $response['error_status'] = 1;
                $response['error'] =
                    esc_html__("Unable to assign the item to a folder, your type is not valid!", 'wpdatatables');
                echo json_encode($response);
                exit();
            }
            $parentID = intval($_POST['parent_id']);
            $folderID = intval($_POST['folder_id']);
            $childrenIDs = [];
            if (!empty($_POST['children_ids'])) {
                $childrenIDs = array_map('intval', $_POST['children_ids']);
            }

            $folderName = sanitize_text_field($_POST['name']);
            $type = $instance->getType();
            $dbTable = $instance->getFoldersDB();
            if ($action !== 'delete') {
                $uniqueName = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT id FROM " . $dbTable . "
                      WHERE name = %s AND type = %s LIMIT 1",
                        $folderName,
                        $type
                    )
                );
                if ($wpdb->last_error !== '') {
                    $response['error'] = $wpdb->last_error;
                    echo json_encode($response);
                    exit();
                }

                if ($uniqueName > 0) {
                    $response['error'] = esc_html__("Folder name already exists! Please try different folder name.", 'wpdatatables');
                    echo json_encode($response);
                    exit();
                }

                if ($action == 'insert') {
                    $instance->addFolder($wpdb, $dbTable, $parentID, $type, $folderName);
                    $response['lastInsertID'] = $wpdb->insert_id;
                }

                if ($action == 'update') {
                    $instance->editFolder($wpdb, $dbTable, $parentID, $type, $folderName, $folderID);
                }
            }
            $data = $instance->getAllFolders();
            if ($action == 'delete') {
                $instance->deleteFolder($wpdb, $instance, $dbTable, $childrenIDs, $type, $folderID);
                $data = $instance->getAllUnassigned();
                if ($data == '0'){
                    $response['success'] = 1;
                    $response['data'] = 0;
                }
            }

            if (!empty($data)) {
                $response['success'] = 1;
                $response['data'] = $data;
            }

        }

        echo json_encode($response);
        exit();
    }

    public function addFolder($wpdb, $dbTable, $parentID, $type, $folderName)
    {
        $wpdb->insert(
            $dbTable,
            array(
                'parent_id' => $parentID == -1 ? null : $parentID,
                'type' => $type,
                'name' => $folderName,
            )
        );
        if ($wpdb->last_error !== '') {
            $response['error'] = $wpdb->last_error;
            echo json_encode($response);
            exit();
        }

    }

    public function editFolder($wpdb, $dbTable, $parentID, $type, $folderName, $folderID)
    {
        $wpdb->update(
            $dbTable,
            array(
                'parent_id' => $parentID == -1 ? null : $parentID,
                'type' => $type,
                'name' => $folderName,
            ),
            array('id' => $folderID)
        );
        if ($wpdb->last_error !== '') {
            $response['error'] = $wpdb->last_error;
            echo json_encode($response);
            exit();
        }
    }

    public function deleteFolder($wpdb, $instance, $dbTable, $childrenIDs, $type, $folderID)
    {
        array_push($childrenIDs, $folderID);
        foreach ($childrenIDs as $childrenID) {
            $wpdb->delete(
                $dbTable,
                array(
                    'id' => $childrenID
                ),
                array(
                    '%d'
                )
            );
            if ($wpdb->last_error !== '') {
                $response['error'] = $wpdb->last_error;
                echo json_encode($response);
                exit();
            }
            $wpdb->delete(
                $instance->getFoldersMetaDB(),
                array(
                    'type' => $type,
                    'folder_id' => $childrenID
                ),
                array(
                    '%s',
                    '%d',
                )
            );
            if ($wpdb->last_error !== '') {
                $response['error'] = $wpdb->last_error;
                echo json_encode($response);
                exit();
            }
        }
    }
    public function deleteItem($id, $type){
        global $wpdb;
        $wpdb->delete("{$this->getFoldersMetaDB()}",
            array(
                'type_id' => $id,
                'type'    => $type
            )
        );
    }

    public function removeFolderTag()
    {
        global $wpdb;
        $response = [
            'error_status' => 0,
            'success' => '',
            'error' => '',
            'data' => []
        ];
        $action = sanitize_text_field($_POST['folder_action']);

        if ($action != 'remove') {
            $response['error_status'] = 1;
            $response['error'] = esc_html__("Unknown action, your request is not valid!", 'wpdatatables');
        }

        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtFoldersNonce')) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Unable to ", 'wpdatatables')
                . $action .
                esc_html__(" folder, your request is not valid!", 'wpdatatables');
        }

        if (!current_user_can("manage_options")) {
            $response['error_status'] = 1;
            $response['error'] = esc_html__("You have not permission to add folder", 'wpdatatables');
        }

        if (!(isset($_POST['type']) && in_array($_POST['type'], $this->getAllowedTypes()))) {
            $response['error_status'] = 1;
            $response['error'] = esc_html__("Unknown type, your request is not valid!", 'wpdatatables');
        }

        if (!$response['error_status']) {
            $instance = WPDataFoldersFactory::createTypeBased(
                sanitize_text_field($_POST['type'])
            );
            if ($instance == '') {
                $response['error_status'] = 1;
                $response['error'] =
                    esc_html__("Unable to assign the item to a folder, your type is not valid!", 'wpdatatables');
                echo json_encode($response);
                exit();
            }
            $itemID = intval($_POST['item_id']);
            $folderID = intval($_POST['folder_id']);
            $type = $instance->getType();
            $dbTable = $instance->getFoldersMetaDB();
            $wpdb->delete(
                $dbTable,
                array(
                    'type' => $type,
                    'type_id' => $itemID,
                    'folder_id' => $folderID
                ),
                array(
                    '%s',
                    '%d',
                    '%d',
                )
            );
            if ($wpdb->last_error !== '') {
                $response['error'] = $wpdb->last_error;
                echo json_encode($response);
                exit();
            }
            $assignedItemsToFolder = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT COUNT(*) as itemCount FROM " . $dbTable . "
                    WHERE type = %s 
                    AND folder_id = %d",
                    $type,
                    $folderID
                )
            );
            if ($wpdb->last_error !== '') {
                $response['error'] = $wpdb->last_error;
                echo json_encode($response);
                exit();
            }
            $response['success'] = 1;
            $response['data'] = [
                'item_id' => $itemID,
                'folder_id' => $folderID,
                'item_count' => $assignedItemsToFolder->itemCount,
                'unassigned' => $instance->getAllUnassigned(),
                'type' => $type,
                'type_caps' => ucfirst($type),
            ];
        }

        echo json_encode($response);
        exit();

    }

    public function getListTable()
    {
        $response = [
            'error_status' => 0,
            'success' => '',
            'error' => '',
            'data' => []
        ];

        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtFoldersNonce')) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Your request is not valid!", 'wpdatatables');
        }

        if (!current_user_can("manage_options")) {
            $response['error_status'] = 1;
            $response['error'] = esc_html__("You have not permission to add folder", 'wpdatatables');
        }

        if (!(isset($_POST['type']) && in_array($_POST['type'], $this->getAllowedTypes()))) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Your folder type is not valid!", 'wpdatatables');
        }

        if (!$response['error_status']) {
            $type = sanitize_text_field($_POST['type']);
            $wdtBrowseTable = '';
            if ($type == 'table')
                $wdtBrowseTable = new WDTBrowseTable();
            if ($type == 'chart')
                $wdtBrowseTable = new WDTBrowseChartsTable();
            if ($type == 'report')
                if (!class_exists('BrowseReportsTable')) {
                    include_once(WDT_RB_ROOT_PATH . '/source/class.browsereports.php');
                    $wdtBrowseTable = new \WDTReportBuilder\BrowseReportsTable();
                }
            if (!$wdtBrowseTable) {
                $response['error_status'] = 1;
                $response['error'] =
                    esc_html__("Your folder type is not valid!", 'wpdatatables');
                echo json_encode($response);
                exit();
            }
            $wdtBrowseTable->prepare_items();

            ob_start();
            $wdtBrowseTable->display();
            $tableHTML = ob_get_contents();
            $tableHTML .= wp_nonce_field('wdtDeleteTableNonce', 'wdtNonce');
            ob_end_clean();
            $folderID = (int)$_POST['folderID'];
            $params = $_POST['params'];
            $queryString = '';
            foreach ($params as $key => $param) {
                $queryString .= sanitize_text_field($key) . '=' . sanitize_text_field($param) . '&';
            }
            if (!isset($params['folderID'])) {
                $queryString .= 'folderID=' . $folderID . '&';
            }
            // Remove the trailing '&' character
            $queryString = substr_replace($queryString, "#/", -1);

            $response = [
                'success' => '1',
                'data' => $tableHTML,
                'url' => admin_url() . 'admin.php?' . $queryString
            ];
        }

        echo json_encode($response);
        exit();
    }

    public function sortFolders()
    {
        $response = [
            'error_status' => 0,
            'success' => '',
            'error' => '',
            'data' => []
        ];

        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtFoldersNonce')) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Your request is not valid!", 'wpdatatables');
        }

        if (!current_user_can("manage_options")) {
            $response['error_status'] = 1;
            $response['error'] = esc_html__("You have not permission to add folder", 'wpdatatables');
        }

        if (!(isset($_POST['type']) && in_array($_POST['type'], $this->getAllowedTypes()))) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Your folder type is not valid!", 'wpdatatables');
        }

        if (!$response['error_status']) {
            $sortOrder = sanitize_text_field($_POST['sortOrder']);
            $type = sanitize_text_field($_POST['type']);
            $instance = WPDataFoldersFactory::createTypeBased($type);
            if (in_array($sortOrder, ['ASC', 'DESC', 'NEW', 'OLD'])) {
                $settings = $this->getSettings();
                $settings[$instance->getType()]['sort'] = $sortOrder;
                update_option('wdtFolderOptions', json_encode($settings));
            }

            $response = [
                'error_status' => 0,
                'success' => 1,
                'error' => '',
                'data' => $instance->getAllFolders()
            ];
        }

        echo json_encode($response);
        exit();
    }

    public function showChildrenFolders()
    {
        $response = [
            'error_status' => 0,
            'success' => '',
            'error' => '',
            'showChildren' => false
        ];

        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtFoldersNonce')) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Your request is not valid!", 'wpdatatables');
        }

        if (!current_user_can("manage_options")) {
            $response['error_status'] = 1;
            $response['error'] = esc_html__("You have not permission to add folder", 'wpdatatables');
        }

        if (!(isset($_POST['type']) && in_array($_POST['type'], $this->getAllowedTypes()))) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Your folder type is not valid!", 'wpdatatables');
        }

        if (!$response['error_status']) {
            $type = sanitize_text_field($_POST['type']);
            $showChildrenSettings = $_POST['showChildren'] == 'true';
            $settings = $this->getSettings();
            $settings[$type]['showChildren'] = $showChildrenSettings;
            update_option('wdtFolderOptions', json_encode($settings));


            $response = [
                'error_status' => 0,
                'success' => 1,
                'error' => '',
                'showChildren' => $showChildrenSettings
            ];
        }

        echo json_encode($response);
        exit();
    }

    public function collapseFolders()
    {
        $response = [
            'error_status' => 0,
            'success' => '',
            'error' => '',
            'collapsed' => false
        ];

        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtFoldersNonce')) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Your request is not valid!", 'wpdatatables');
        }

        if (!current_user_can("manage_options")) {
            $response['error_status'] = 1;
            $response['error'] = esc_html__("You have not permission to add folder", 'wpdatatables');
        }

        if (!(isset($_POST['type']) && in_array($_POST['type'], $this->getAllowedTypes()))) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Your folder type is not valid!", 'wpdatatables');
        }

        if (!$response['error_status']) {
            $type = sanitize_text_field($_POST['type']);
            $collapsedSettings = $_POST['collapsed'] == 'true';
            $settings = $this->getSettings();
            $settings[$type]['collapsed'] = $collapsedSettings;
            update_option('wdtFolderOptions', json_encode($settings));


            $response = [
                'error_status' => 0,
                'success' => 1,
                'error' => '',
                'collapsed' => $collapsedSettings
            ];
        }

        echo json_encode($response);
        exit();
    }

    public function hideColumns()
    {
        $response = [
            'error_status' => 0,
            'success' => '',
            'error' => '',
            'columnsToHide' => []
        ];

        if (!wp_verify_nonce($_POST['wdtNonce'], 'wdtFoldersNonce')) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Your request is not valid!", 'wpdatatables');
        }

        if (!current_user_can("manage_options")) {
            $response['error_status'] = 1;
            $response['error'] = esc_html__("You have not permission to add folder", 'wpdatatables');
        }

        if (!(isset($_POST['type']) && in_array($_POST['type'], $this->getAllowedTypes()))) {
            $response['error_status'] = 1;
            $response['error'] =
                esc_html__("Your folder type is not valid!", 'wpdatatables');
        }

        if (!$response['error_status']) {
            $allowedValues = [
                'id',
                'title',
                'name',
                'table_type',
                'table_description',
                'folders'
            ];

            if (get_option('wdtUseSeparateCon')){
                $allowedValues[] = 'connection';
            }

            $type = sanitize_text_field($_POST['type']);
            $settings = $this->getSettings();
            $columnsToHide = [];
            if (!empty($_POST['columnsToHide']) && is_array($_POST['columnsToHide'])) {
                $columnsToHide = $_POST['columnsToHide'];
                foreach ($columnsToHide as $key => &$setting) {
                    if (in_array($setting, $allowedValues)) {
                        $setting = sanitize_text_field($setting);
                    }
                }
            }

            $settings[$type]['columnsToHide'] = $columnsToHide;
            update_option('wdtFolderOptions', json_encode($settings));
            $response = [
                'error_status' => 0,
                'success' => 1,
                'error' => '',
                'columnsToHide' => $columnsToHide
            ];
        }

        echo json_encode($response);
        exit();
    }
}

new WPDataFolders();