<?php

/**
 * Backward-compatibility global functions (Phase K).
 *
 * Replaces the legacy `controllers/wdt_*.php` delegators. Hook wiring lives in
 * {@see \WPDataTables\Plugin\Plugin} hook owners; these globals remain for
 * third-party callers and tier add-ons.
 *
 * @package WPDataTables\Legacy
 */

defined('ABSPATH') or die('Access denied.');

use WPDataTables\Common\Helpers\FormulaHelper;
use WPDataTables\Common\Helpers\QuerySanitizer;
use WPDataTables\Common\Helpers\SqlHelper;
use WPDataTables\Common\Helpers\TableEditHelper;
use WPDataTables\Plugin\Plugin;
use WPDataTables\Rendering\StyleBlockRenderer;
use WPDataTables\Services\InstallActions\ActivationHook;
use WPDataTables\Services\InstallActions\DeactivationHook;
use WPDataTables\Services\InstallActions\UninstallHook;

// --- Install / uninstall ---------------------------------------------------

function wdtActivationInsertTemplates()
{
    ActivationHook::insertTemplates();
}

function wdtActivationCreateTables()
{
    ActivationHook::createTables();
}

function wdtDeactivation()
{
    DeactivationHook::deactivate();
}

function wdtUninstallDelete()
{
    UninstallHook::delete();
}

function wdtCheckSimpleTemplatesActivation($rowCount, $tableName)
{
    ActivationHook::checkSimpleTemplatesActivation($rowCount, $tableName);
}

function wdtActivation($networkWide)
{
    ActivationHook::activate($networkWide);
}

function wdtUninstall()
{
    UninstallHook::uninstall();
}

// --- Admin menu page callbacks (BC) ------------------------------------------

function wdtAdminMenu()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\AdminMenu::class)->register();
}

function wdtAdminEnqueue($hook)
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\AdminAssets::class)->enqueueAdmin($hook);
}

function wdtDeregisterGravityTooltipScript()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\AdminAssets::class)->deregisterGravityTooltipScript();
}

function wdtBrowseTables()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\TablesPageController::class)->renderBrowseTables();
}

function wdtEdit()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\TablesPageController::class)->renderEdit();
}

function wdtMainDashboard()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\AdminPageController::class)->renderDashboard();
}

function wdtConstructor()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\TablesPageController::class)->renderConstructor();
}

function wdtBrowseCharts()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\ChartsPageController::class)->renderBrowseCharts();
}

function wdtChartWizard()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\ChartsPageController::class)->renderChartWizard();
}

function wdtSettings()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\AdminPageController::class)->renderSettings();
}

function wdtSupport()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\AdminPageController::class)->renderSupport();
}

function wdtWelcomePage()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\AdminPageController::class)->renderWelcomePage();
}

function wdtSystemInfo()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\AdminPageController::class)->renderSystemInfo();
}

function wdtGettingStarted()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\AdminPageController::class)->renderGettingStarted();
}

function wdtLiteVSPremium()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\AdminPageController::class)->renderLiteVSPremium();
}

function wdtAddOns()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\AdminPageController::class)->renderAddOns();
}

function wdtPermissions()
{
    Plugin::container()->get(\WPDataTables\Plugin\Admin\PermissionsPageController::class)->renderPage();
}

// --- Admin AJAX (BC) ---------------------------------------------------------

function wdtTestSeparateConnectionSettings()
{
    Plugin::container()->get(\WPDataTables\Controllers\Connection\ConnectionController::class)->testSeparateConnectionSettings();
}

function wdtGetConnectionTables()
{
    Plugin::container()->get(\WPDataTables\Controllers\Connection\ConnectionController::class)->getConnectionTables();
}

function wdtSaveTableWithColumns()
{
    Plugin::container()->get(\WPDataTables\Controllers\Table\TableConfigController::class)->saveTableWithColumns();
}

function wdtSavePluginSettings()
{
    Plugin::container()->get(\WPDataTables\Controllers\Settings\SettingsController::class)->savePluginSettings();
}

function wdtSaveGoogleMapsApiKey()
{
    Plugin::container()->get(\WPDataTables\Controllers\Settings\SettingsController::class)->saveGoogleMapsApiKey();
}

function wdtDeleteLogErrorsCache()
{
    Plugin::container()->get(\WPDataTables\Controllers\Settings\SettingsController::class)->deleteLogErrorsCache();
}

function wdtDuplicateTable()
{
    Plugin::container()->get(\WPDataTables\Controllers\Duplicate\DuplicateController::class)->duplicateTable();
}

function wdtDuplicateChart()
{
    Plugin::container()->get(\WPDataTables\Controllers\Duplicate\DuplicateController::class)->duplicateChart();
}

function wdtCreateSimpleTable()
{
    Plugin::container()->get(\WPDataTables\Controllers\Table\SimpleTableController::class)->createSimpleTable();
}

function wdtGetHandsontableData()
{
    Plugin::container()->get(\WPDataTables\Controllers\Table\SimpleTableController::class)->getHandsontableData();
}

function wdtSaveDataSimpleTable()
{
    Plugin::container()->get(\WPDataTables\Controllers\Table\SimpleTableController::class)->saveDataSimpleTable();
}

function wdtCreateManualTable()
{
    Plugin::container()->get(\WPDataTables\Controllers\Table\ManualTableController::class)->createManualTable();
}

function wdtConstructorPreviewFileTable()
{
    Plugin::container()->get(\WPDataTables\Controllers\Table\ManualTableController::class)->constructorPreviewFileTable();
}

function wdtConstructorReadFileData()
{
    Plugin::container()->get(\WPDataTables\Controllers\Table\ManualTableController::class)->constructorReadFileData();
}

function wdtAddNewManualColumn()
{
    Plugin::container()->get(\WPDataTables\Controllers\Table\ManualTableController::class)->addNewManualColumn();
}

function wdtDeleteManualColumn()
{
    Plugin::container()->get(\WPDataTables\Controllers\Table\ManualTableController::class)->deleteManualColumn();
}

function wdtGetColumnsDataByTableId()
{
    Plugin::container()->get(\WPDataTables\Controllers\Table\TableConfigController::class)->getColumnsDataByTableId();
}

function wdtGetCompleteTableJSONById()
{
    Plugin::container()->get(\WPDataTables\Controllers\Table\TableConfigController::class)->getCompleteTableJSONById();
}

function wdtShowChartFromData()
{
    Plugin::container()->get(\WPDataTables\Controllers\Chart\ChartController::class)->showChartFromData();
}

function wdtSaveChart()
{
    Plugin::container()->get(\WPDataTables\Controllers\Chart\ChartController::class)->saveChart();
}

function wdtListAllTables()
{
    Plugin::container()->get(\WPDataTables\Controllers\Chart\ChartController::class)->listAllTables();
}

function wdtListAllCharts()
{
    Plugin::container()->get(\WPDataTables\Controllers\Chart\ChartController::class)->listAllCharts();
}

function wdtReadDistinctValuesFromTable()
{
    Plugin::container()->get(\WPDataTables\Controllers\Column\ColumnController::class)->readDistinctValuesFromTable();
}

function wdtGetNestedJsonRoots()
{
    Plugin::container()->get(\WPDataTables\Controllers\NestedJson\NestedJsonController::class)->getNestedJsonRoots();
}

function wdtPreviewFormulaResult()
{
    Plugin::container()->get(\WPDataTables\Controllers\Formula\FormulaController::class)->previewFormulaResult();
}

function wdtCheckFormulaResult()
{
    Plugin::container()->get(\WPDataTables\Controllers\Formula\FormulaController::class)->checkFormulaResult();
}

function wdtActivatePlugin()
{
    Plugin::container()->get(\WPDataTables\Controllers\Activation\ActivationController::class)->activatePlugin();
}

function wdtDeactivatePlugin()
{
    Plugin::container()->get(\WPDataTables\Controllers\Activation\ActivationController::class)->deactivatePlugin();
}

function wdtParseServerName()
{
    Plugin::container()->get(\WPDataTables\Controllers\Connection\ConnectionController::class)->parseServerName();
}

function get_table_type_by_id_ajax()
{
    Plugin::container()->get(\WPDataTables\Controllers\Table\TableConfigController::class)->getTableTypeById();
}

// --- Frontend AJAX (BC) ------------------------------------------------------

function wdtGetAjaxData()
{
    Plugin::container()->get(\WPDataTables\Controllers\Frontend\DataController::class)->getAjaxData();
}

function wdtSaveTableFrontend()
{
    Plugin::container()->get(\WPDataTables\Controllers\Frontend\TableEditController::class)->saveTableFrontend();
}

function wdtSaveTableCellsFrontend()
{
    Plugin::container()->get(\WPDataTables\Controllers\Frontend\TableEditController::class)->saveTableCellsFrontend();
}

function wdtDeleteTableRow()
{
    Plugin::container()->get(\WPDataTables\Controllers\Frontend\TableEditController::class)->deleteTableRow();
}

function wdtDeleteTableRows()
{
    Plugin::container()->get(\WPDataTables\Controllers\Frontend\TableEditController::class)->deleteTableRows();
}

function wdtGetColumnPossibleValues()
{
    Plugin::container()->get(\WPDataTables\Controllers\Column\ColumnController::class)->getColumnPossibleValues();
}

function wdtDoShortcode()
{
    Plugin::container()->get(\WPDataTables\Controllers\Frontend\ElementorController::class)->doShortcode();
}

// --- Notice dismiss AJAX (BC) ------------------------------------------------

function wdtHideUpdateModal()
{
    Plugin::container()->get(\WPDataTables\Controllers\Notice\NoticeController::class)->hideUpdateModal();
}

function wdtHideRating()
{
    Plugin::container()->get(\WPDataTables\Controllers\Notice\NoticeController::class)->hideRating();
}

function wdtRemoveBootstrapUpdateNotice()
{
    Plugin::container()->get(\WPDataTables\Controllers\Notice\NoticeController::class)->removeBootstrapUpdateNotice();
}

function wdtRemoveForminatorNotice()
{
    Plugin::container()->get(\WPDataTables\Controllers\Notice\NoticeController::class)->removeForminatorNotice();
}

function wdtRemoveBundlesNotice()
{
    Plugin::container()->get(\WPDataTables\Controllers\Notice\NoticeController::class)->removeBundlesNotice();
}

function wdtRemoveAmeliaPromoNotice()
{
    Plugin::container()->get(\WPDataTables\Controllers\Notice\NoticeController::class)->removeAmeliaPromoNotice();
}

function wdtRemoveIvyFormsPromoNotice()
{
    Plugin::container()->get(\WPDataTables\Controllers\Notice\NoticeController::class)->removeIvyFormsPromoNotice();
}

function wdt_dismiss_ivyforms_promo_user()
{
    Plugin::container()->get(\WPDataTables\Controllers\Notice\NoticeController::class)->dismissIvyFormsPromoUser();
}

function wdtRemoveHighchartsCdnNotice()
{
    Plugin::container()->get(\WPDataTables\Controllers\Notice\NoticeController::class)->removeHighchartsCdnNotice();
}

function wdtHideSimpleTableAlert()
{
    Plugin::container()->get(\WPDataTables\Controllers\Notice\NoticeController::class)->hideSimpleTableAlert();
}

function wpdtHideMDNewsDiv()
{
    Plugin::container()->get(\WPDataTables\Controllers\Notice\NoticeController::class)->hideMDNewsDiv();
}

function wpdtTempHideRatingDiv()
{
    Plugin::container()->get(\WPDataTables\Controllers\Notice\NoticeController::class)->tempHideRatingDiv();
}

function wdtDismissFoldersNotice()
{
    Plugin::container()->get(\WPDataTables\Controllers\Notice\NoticeController::class)->dismissFoldersNotice();
}

// --- Shortcodes (BC) ---------------------------------------------------------

function wdtOutputTable($id)
{
    echo wdtWpDataTableShortcodeHandler(array('id' => $id));
}

function wdtWpDataChartShortcodeHandler($atts, $content = null)
{
    return Plugin::container()->get(\WPDataTables\Plugin\ShortcodeController::class)->renderChart($atts, $content);
}

function wdtWpDataTableCellShortcodeHandler($atts, $content = null)
{
    return Plugin::container()->get(\WPDataTables\Plugin\ShortcodeController::class)->renderCell($atts, $content);
}

function wdtWpDataTableShortcodeHandler($atts, $content = null)
{
    return Plugin::container()->get(\WPDataTables\Plugin\ShortcodeController::class)->renderTable($atts, $content);
}

function wdtFuncsShortcodeHandler($atts, $content = null, $shortcode = null)
{
    return Plugin::container()->get(\WPDataTables\Plugin\ShortcodeController::class)->renderFunction($atts, $content, $shortcode);
}

// --- Shared helpers (BC) -----------------------------------------------------

function wdt_ivyforms_promo_should_show_for_current_user()
{
    return Plugin::container()->get(\WPDataTables\Services\Admin\AdminNoticeService::class)->shouldShowIvyFormsPromoForCurrentUser();
}

function wdt_is_ivyforms_plugin_active()
{
    return Plugin::container()->get(\WPDataTables\Services\Admin\AdminNoticeService::class)->isIvyFormsPluginActive();
}

function wdtIsPluginInstalled($plugin_path)
{
    return Plugin::container()->get(\WPDataTables\Services\Admin\AdminNoticeService::class)->isPluginInstalled($plugin_path);
}

function wdtInstalledPluginsAmeliaPromotion()
{
    return Plugin::container()->get(\WPDataTables\Services\Admin\AdminNoticeService::class)->installedPluginsAmeliaPromotion();
}

function formulaFormat($headersInFormulaColumn, $formulaColumn, $tableName, $leftSysIdentifier, $rightSysIdentifier)
{
    return FormulaHelper::format($headersInFormulaColumn, $formulaColumn, $tableName, $leftSysIdentifier, $rightSysIdentifier);
}

function wdtRenderScriptStyleBlock($tableID)
{
    return StyleBlockRenderer::renderScriptStyleBlock($tableID);
}

function wdtTableRenderScriptStyleBlock($obj)
{
    return StyleBlockRenderer::renderTableScriptStyleBlock($obj);
}

function wdtCurrentUserCanEdit($tableEditorRoles, $id)
{
    return TableEditHelper::currentUserCanEdit($tableEditorRoles, $id);
}

function wdtSanitizeQuery($query)
{
    return QuerySanitizer::sanitize((string) $query);
}

function wdtSanitizeSqlPlaceholderValue($value)
{
    return SqlHelper::sanitizeSqlPlaceholderValue($value);
}

function wdtAddBodyClass($classes)
{
    return $classes . ' wpdt-c';
}

function wdtLoadTextdomain()
{
    Plugin::container()->get(\WPDataTables\Plugin\PluginLifecycleHooks::class)->loadTextdomain();
}

function wdtEnableMultipleConnections()
{
    Plugin::container()->get(\WPDataTables\Plugin\PluginLifecycleHooks::class)->enableMultipleConnections();
}

function wdtOnCreateSiteOnMultisiteNetwork($blogId)
{
    Plugin::container()->get(\WPDataTables\Plugin\PluginLifecycleHooks::class)->onCreateSiteOnMultisiteNetwork($blogId);
}

function wdtOnDeleteSiteOnMultisiteNetwork($tables)
{
    return Plugin::container()->get(\WPDataTables\Plugin\PluginLifecycleHooks::class)->onDeleteSiteOnMultisiteNetwork($tables);
}

function wdtSaveDeactivationinfo()
{
    Plugin::container()->get(\WPDataTables\Controllers\Admin\DeactivationFeedbackController::class)->saveDeactivationInfo();
}

function wdtEnqueueDeactivationModal()
{
    Plugin::container()->get(\WPDataTables\Controllers\Admin\DeactivationFeedbackController::class)->enqueueDeactivationModal();
}

function wdtAdminRatingMessages()
{
    Plugin::container()->get(\WPDataTables\Services\Admin\AdminNoticeService::class)->renderAdminNotices();
}

function wdt_ivyforms_promo_enqueue_admin_scripts()
{
    Plugin::container()->get(\WPDataTables\Services\Admin\AdminNoticeService::class)->enqueueIvyFormsPromoAssets();
}

function initGutenbergBlocks()
{
    Plugin::container()->get(\WPDataTables\Plugin\EditorHooks::class)->initGutenbergBlocks();
}

function addWpDataTablesBlockCategory($categories, $post)
{
    return Plugin::container()->get(\WPDataTables\Plugin\EditorHooks::class)->addBlockCategory($categories, $post);
}

function wdtMCEButtons()
{
    Plugin::container()->get(\WPDataTables\Plugin\EditorHooks::class)->registerMceButtons();
}

function wdtAddButtons($pluginArray)
{
    return Plugin::container()->get(\WPDataTables\Plugin\EditorHooks::class)->addMceButtons($pluginArray);
}

function wdtRegisterButtons($buttons)
{
    return Plugin::container()->get(\WPDataTables\Plugin\EditorHooks::class)->registerMceButtonNames($buttons);
}

function welcome_page_activation_redirect($plugin)
{
    Plugin::container()->get(\WPDataTables\Plugin\PluginLifecycleHooks::class)->welcomePageActivationRedirect($plugin);
}

function wpdt_add_plugin_action_links($links)
{
    return Plugin::container()->get(\WPDataTables\Plugin\PluginLifecycleHooks::class)->addPluginActionLinks($links);
}

function wpdt_plugin_row_meta($links, $file, $plugin_data)
{
    return Plugin::container()->get(\WPDataTables\Plugin\PluginLifecycleHooks::class)->addPluginRowMeta($links, $file, $plugin_data);
}

function wdtCheckUpdate($transient)
{
    return Plugin::container()->get(\WPDataTables\Plugin\PluginUpdateHooks::class)->checkUpdate($transient);
}

function wdtCheckInfo($response, $action, $args)
{
    return Plugin::container()->get(\WPDataTables\Plugin\PluginUpdateHooks::class)->checkInfo($response, $action, $args);
}

function wdtAddMessageOnPluginsPage()
{
    Plugin::container()->get(\WPDataTables\Plugin\PluginUpdateHooks::class)->addMessageOnPluginsPage();
}

function wdtAddMessageOnUpdate($reply, $package, $updater)
{
    return Plugin::container()->get(\WPDataTables\Plugin\PluginUpdateHooks::class)->addMessageOnUpdate($reply, $package, $updater);
}

function wdt_sanitize_multi_upload($fields)
{
    return array_map(function ($field) {
        return array_map('sanitize_file_name', $field);
    }, $fields);
}

function wdt_get_super_global_value($super_global, $key)
{
    if (!isset($super_global[$key])) {
        return null;
    }

    if ($_FILES === $super_global) {
        return isset($super_global[$key]['name']) ?
            sanitize_file_name($super_global[$key]) :
            wdt_sanitize_multi_upload($super_global[$key]);
    }

    return wp_kses_post_deep(wp_unslash($super_global[$key]));
}
