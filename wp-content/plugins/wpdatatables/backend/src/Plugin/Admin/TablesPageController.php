<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin\Admin;

use WPDataTable;
use WDTBrowseTable;
use WDTConfigController;
use WPDataTables\Services\Permissions\PermissionsService;
use WPDataTables\Services\Tools\ToolsService;

/**
 * TablesPageController — renders the wpDataTables table admin pages: the Browse
 * Tables list (which also handles row/bulk deletion), the Edit page (add / edit
 * a table from a data source, incl. the Simple-table editor), and the
 * Constructor (Create a Table) page.
 *
 * @package WPDataTables\Plugin\Admin
 */
class TablesPageController
{
    /** @var PermissionsService */
    private $permissionsService;

    public function __construct(PermissionsService $permissionsService)
    {
        $this->permissionsService = $permissionsService;
    }

    /**
     * Render the Browse Tables (wpDataTables) page and handle table deletion.
     *
     * @throws \Exception
     * @return void
     */
    public function renderBrowseTables()
    {
        if (!$this->permissionsService->canListTables()) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $action = '';
        if (isset($_REQUEST['action']) && -1 != $_REQUEST['action']) {
            $action = $_REQUEST['action'];
        }
        if (isset($_REQUEST['action2']) && -1 != $_REQUEST['action2']) {
            $action = $_REQUEST['action2'];
        }

        if ($action === 'delete') {
            $tableId = $_REQUEST['table_id'] ?? null;

            if (!is_array($tableId)) {
                $tableId = absint($tableId);
                if ($tableId > 0 && $this->permissionsService->canDeleteTable($tableId)) {
                    WPDataTable::deleteTable($tableId);
                }
            } else {
                foreach ($tableId as $singleTableId) {
                    $singleTableId = absint($singleTableId);
                    if ($singleTableId > 0 && $this->permissionsService->canDeleteTable($singleTableId)) {
                        WPDataTable::deleteTable($singleTableId);
                    }
                }
            }
        }

        $wdtBrowseTable = new WDTBrowseTable();
        $wdtBrowseTable->prepare_items();

        ob_start();
        $wdtBrowseTable->display();
        $tableHTML = ob_get_contents();
        ob_end_clean();

        ob_start();
        include WDT_ROOT_PATH . 'templates/admin/browse/table/browse.inc.php';
        $browseTablesPage = ob_get_contents();
        ob_end_clean();

        $browseTablesPage = apply_filters('wpdatatables_filter_browse_page', $browseTablesPage);

        echo $browseTablesPage;

        do_action('wpdatatables_browse_page');
    }

    /**
     * Render the Edit page (add / edit a table from a data source).
     *
     * @throws \Exception
     * @return void
     */
    public function renderEdit()
    {
        $tableId = isset($_GET['table_id']) ? absint($_GET['table_id']) : 0;

        if ($tableId > 0) {
            if (!$this->permissionsService->canEditTable($tableId)) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
        } elseif (!$this->permissionsService->canCreateTables()) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        if (isset($_GET['table_id'])) {
            if (isset($_GET['simple'])) {
                $tableID = (int)$_GET['table_id'];
                $tableData = WDTConfigController::loadSimpleTableConfig($tableID);
            } else if (isset($_GET['table_view'])) {
                $tableData = WDTConfigController::loadTableConfig((int)$_GET['table_id'], $_GET['table_view']);
            } else {
                $tableData = WDTConfigController::loadTableConfig((int)$_GET['table_id']);
            }
            if (isset($tableData->error)) {
                echo ToolsService::wdtShowError($tableData->error);

                return;
            }
            ToolsService::exportJSVar('wpdatatable_init_config', $tableData->table);
        }

        if (isset($tableData) && isset($tableData->table)) {
            $connection = $tableData->table->connection;
        } elseif (isset($_GET['connection'])) {
            $connection = $_GET['connection'];
        } else {
            $connection = null;
        }


        ob_start();
        if (isset($_GET['table_id']) && isset($_GET['simple'])) {
            include WDT_ROOT_PATH . 'templates/admin/table-settings/edit_simple_table.inc.php';
            $editPage = ob_get_contents();
            ob_end_clean();

            $editPage = apply_filters('wpdatatables_filter_edit_page_simple_table', $editPage);
        } else {
            include WDT_ROOT_PATH . 'templates/admin/table-settings/edit_table.inc.php';
            $editPage = ob_get_contents();
            ob_end_clean();

            $editPage = apply_filters('wpdatatables_filter_edit_page', $editPage);
        }
        echo $editPage;
    }

    /**
     * Render the Constructor (Create a Table) page.
     *
     * @throws \Exception
     * @return void
     */
    public function renderConstructor()
    {
        if (isset($_GET['source'])) {
            $this->renderEdit();
            return;
        }

        if (!$this->permissionsService->canCreateTables()) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        ob_start();
        include WDT_ROOT_PATH . 'templates/admin/constructor/constructor.inc.php';
        $constructorPage = ob_get_contents();
        ob_end_clean();

        $constructorPage = apply_filters('wpdatatables_filter_constructor_page', $constructorPage);
        echo $constructorPage;
        do_action('wpdatatables_constructor_page');
    }
}
