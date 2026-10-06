<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin\Admin;

use WPDataChart;
use WDTBrowseChartsTable;
use Exception;
use WPDataTables\Services\Permissions\PermissionsService;

/**
 * ChartsPageController — renders the wpDataTables chart admin pages: the Browse
 * Charts list (which also handles chart deletion) and the Chart Wizard
 * (create / edit a chart).
 *
 * @package WPDataTables\Plugin\Admin
 */
class ChartsPageController
{
    /** @var PermissionsService */
    private $permissionsService;

    public function __construct(PermissionsService $permissionsService)
    {
        $this->permissionsService = $permissionsService;
    }

    /**
     * Render the Browse Charts (wpDataCharts) page and handle chart deletion.
     *
     * @return void
     */
    public function renderBrowseCharts()
    {
        if (!$this->permissionsService->canListCharts()) {
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
            $chartId = $_REQUEST['chart_id'] ?? null;

            if (!is_array($chartId)) {
                $chartId = absint($chartId);
                if ($chartId > 0 && $this->permissionsService->canDeleteChart($chartId)) {
                    WPDataChart::delete($chartId);
                }
            } else {
                foreach ($chartId as $singleChartId) {
                    $singleChartId = absint($singleChartId);
                    if ($singleChartId > 0 && $this->permissionsService->canDeleteChart($singleChartId)) {
                        WPDataChart::delete($singleChartId);
                    }
                }
            }
        }

        $wdtBrowseChartsTable = new WDTBrowseChartsTable();
        $wdtBrowseChartsTable->prepare_items();

        ob_start();
        $wdtBrowseChartsTable->display();
        $tableHTML = ob_get_contents();
        ob_end_clean();

        ob_start();
        include WDT_ROOT_PATH . 'templates/admin/browse/chart/browse.inc.php';
        $browseChartsPage = ob_get_contents();
        ob_end_clean();

        $browseChartsPage = apply_filters('wpdatatables_filter_charts_table_page', $browseChartsPage);

        echo $browseChartsPage;

        do_action('wpdatatables_browse_charts_page');
    }

    /**
     * Render the Chart Wizard (Create a Chart) page.
     *
     * @return void
     * @throws \WDTException
     */
    public function renderChartWizard()
    {
        $chartId = isset($_GET['chart_id']) ? (int)$_GET['chart_id'] : 0;
        $chartEngine = isset($_GET['engine']) ? sanitize_text_field($_GET['engine']) : '';

        if ($chartId > 0) {
            if (!$this->permissionsService->canEditChart($chartId)) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
        } elseif (!$this->permissionsService->canCreateCharts()) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        if (!empty($chartId)) {
            try {
                $chartData = [
                    'id' => $chartId,
                    'engine' => $chartEngine
                ];
                $chartObj = WPDataChart::build($chartData, true);
                $chartObj->prepareData();
                $chartObj->shiftXAxisColumnUp();
                $chartObj->prepareRender();
            } catch (Exception $e) {
                echo $e->getMessage();
                exit;
            }
        }

        ob_start();
        include WDT_ROOT_PATH . 'templates/admin/chart_wizard/chart_wizard.inc.php';
        $chartWizardPage = ob_get_contents();
        ob_end_clean();

        $chartWizardPage = apply_filters('wpdatatables_filter_chart_wizard_page', $chartWizardPage);
        echo $chartWizardPage;
    }
}
