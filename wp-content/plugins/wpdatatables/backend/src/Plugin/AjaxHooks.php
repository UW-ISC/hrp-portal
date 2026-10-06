<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin;

use WPDataTables\Controllers\Notice\NoticeController;
use WPDataTables\Controllers\Settings\SettingsController;
use WPDataTables\Controllers\Connection\ConnectionController;
use WPDataTables\Controllers\Formula\FormulaController;
use WPDataTables\Controllers\Duplicate\DuplicateController;
use WPDataTables\Controllers\Table\TableConfigController;
use WPDataTables\Controllers\Table\SimpleTableController;
use WPDataTables\Controllers\Table\ManualTableController;
use WPDataTables\Controllers\Column\ColumnController;
use WPDataTables\Controllers\NestedJson\NestedJsonController;
use WPDataTables\Controllers\Chart\ChartController;
use WPDataTables\Controllers\Activation\ActivationController;
use WPDataTables\Controllers\Frontend\ElementorController;
use WPDataTables\Controllers\Frontend\TableEditController;
use WPDataTables\Controllers\Frontend\DataController;
use WPDataTables\Controllers\Permissions\PermissionsController;
use WPDataTables\Controllers\Admin\DeactivationFeedbackController;

/**
 * AjaxHooks — the single place where the legacy `wp_ajax_*` glue is wired to the
 * layered controllers. Mirrors {@see FrontendHooks}: `register()` keeps every
 * existing action name (so the admin JS is unaffected) and points each at the
 * matching controller method; the legacy global handler functions remain as
 * one-line delegators (public surface for third parties).
 *
 * Fired once from {@see Plugin} after the container is built.
 *
 * @package WPDataTables\Plugin
 */
class AjaxHooks
{
    /** @var NoticeController */
    private $noticeController;

    /** @var SettingsController */
    private $settingsController;

    /** @var ConnectionController */
    private $connectionController;

    /** @var FormulaController */
    private $formulaController;

    /** @var DuplicateController */
    private $duplicateController;

    /** @var TableConfigController */
    private $tableConfigController;

    /** @var SimpleTableController */
    private $simpleTableController;

    /** @var ManualTableController */
    private $manualTableController;

    /** @var ColumnController */
    private $columnController;

    /** @var NestedJsonController */
    private $nestedJsonController;

    /** @var ChartController */
    private $chartController;

    /** @var ActivationController */
    private $activationController;

    /** @var ElementorController */
    private $elementorController;

    /** @var TableEditController */
    private $tableEditController;

    /** @var DataController */
    private $dataController;

    /** @var PermissionsController */
    private $permissionsController;

    /** @var DeactivationFeedbackController */
    private $deactivationFeedbackController;

    public function __construct(
        NoticeController $noticeController,
        SettingsController $settingsController,
        ConnectionController $connectionController,
        FormulaController $formulaController,
        DuplicateController $duplicateController,
        TableConfigController $tableConfigController,
        SimpleTableController $simpleTableController,
        ManualTableController $manualTableController,
        ColumnController $columnController,
        NestedJsonController $nestedJsonController,
        ChartController $chartController,
        ActivationController $activationController,
        ElementorController $elementorController,
        TableEditController $tableEditController,
        DataController $dataController,
        PermissionsController $permissionsController,
        DeactivationFeedbackController $deactivationFeedbackController
    ) {
        $this->noticeController = $noticeController;
        $this->settingsController = $settingsController;
        $this->connectionController = $connectionController;
        $this->formulaController = $formulaController;
        $this->duplicateController = $duplicateController;
        $this->tableConfigController = $tableConfigController;
        $this->simpleTableController = $simpleTableController;
        $this->manualTableController = $manualTableController;
        $this->columnController = $columnController;
        $this->nestedJsonController = $nestedJsonController;
        $this->chartController = $chartController;
        $this->activationController = $activationController;
        $this->elementorController = $elementorController;
        $this->tableEditController = $tableEditController;
        $this->dataController = $dataController;
        $this->permissionsController = $permissionsController;
        $this->deactivationFeedbackController = $deactivationFeedbackController;
    }

    /**
     * Wire the admin-ajax hooks. Called once from Plugin after the container is
     * built.
     *
     * @return void
     */
    public function register()
    {
        // Admin notice / UI-state dismissals.
        add_action('wp_ajax_wdtHideUpdateModal', array($this->noticeController, 'hideUpdateModal'));
        add_action('wp_ajax_wdtHideRating', array($this->noticeController, 'hideRating'));
        add_action('wp_ajax_wdt_remove_bootstrap_update_notice', array($this->noticeController, 'removeBootstrapUpdateNotice'));
        add_action('wp_ajax_wdt_remove_forminator_notice', array($this->noticeController, 'removeForminatorNotice'));
        add_action('wp_ajax_wdt_remove_bundles_notice', array($this->noticeController, 'removeBundlesNotice'));
        add_action('wp_ajax_wdt_remove_promo_amelia_notice', array($this->noticeController, 'removeAmeliaPromoNotice'));
        add_action('wp_ajax_wdt_remove_ivyforms_promo_notice', array($this->noticeController, 'removeIvyFormsPromoNotice'));
        add_action('wp_ajax_wdt_dismiss_ivyforms_promo_user', array($this->noticeController, 'dismissIvyFormsPromoUser'));
        add_action('wp_ajax_wdt_remove_highcharts_cdn_notice', array($this->noticeController, 'removeHighchartsCdnNotice'));
        add_action('wp_ajax_wdtHideSimpleTableAlert', array($this->noticeController, 'hideSimpleTableAlert'));
        add_action('wp_ajax_wpdtHideMDNewsDiv', array($this->noticeController, 'hideMDNewsDiv'));
        add_action('wp_ajax_wdtTempHideRating', array($this->noticeController, 'tempHideRatingDiv'));
        add_action('wp_ajax_wdtDismissFoldersNotice', array($this->noticeController, 'dismissFoldersNotice'));

        // Plugin-settings domain (save settings, Google Maps API key
        // validation, cache error-log clear).
        add_action('wp_ajax_wpdatatables_save_plugin_settings', array($this->settingsController, 'savePluginSettings'));
        add_action('wp_ajax_wpdatatables_save_google_maps_api_key', array($this->settingsController, 'saveGoogleMapsApiKey'));
        add_action('wp_ajax_wpdatatables_delete_log_errors_cache', array($this->settingsController, 'deleteLogErrorsCache'));

        // Separate-database connection domain (test a separate connection,
        // list its tables, parse a server name).
        add_action('wp_ajax_wpdatatables_test_separate_connection_settings', array($this->connectionController, 'testSeparateConnectionSettings'));
        add_action('wp_ajax_wpdatatables_get_connection_tables', array($this->connectionController, 'getConnectionTables'));
        add_action('wp_ajax_wpdatatables_parse_server_name', array($this->connectionController, 'parseServerName'));

        // Formula-column preview domain (compute the live preview for a
        // formula column, validate a formula).
        add_action('wp_ajax_wpdatatables_preview_formula_result', array($this->formulaController, 'previewFormulaResult'));
        add_action('wp_ajax_wpdatatables_check_formula_result', array($this->formulaController, 'checkFormulaResult'));

        // Duplicate a table / chart.
        add_action('wp_ajax_wpdatatables_duplicate_table', array($this->duplicateController, 'duplicateTable'));
        add_action('wp_ajax_wpdatatables_duplicate_chart', array($this->duplicateController, 'duplicateChart'));

        // Table configuration (save table + columns, fetch columns, fetch
        // complete table JSON for the range picker, and a public table-type
        // lookup for page-builder blocks — the last also nopriv).
        add_action('wp_ajax_wpdatatables_save_table_config', array($this->tableConfigController, 'saveTableWithColumns'));
        add_action('wp_ajax_wpdatatables_get_columns_data_by_table_id', array($this->tableConfigController, 'getColumnsDataByTableId'));
        add_action('wp_ajax_wpdatatables_get_complete_table_json_by_id', array($this->tableConfigController, 'getCompleteTableJSONById'));
        add_action('wp_ajax_get_table_type_by_id', array($this->tableConfigController, 'getTableTypeById'));
        add_action('wp_ajax_nopriv_get_table_type_by_id', array($this->tableConfigController, 'getTableTypeById'));

        // Simple-table (handsontable) constructor CRUD.
        add_action('wp_ajax_wpdatatables_create_simple_table', array($this->simpleTableController, 'createSimpleTable'));
        add_action('wp_ajax_wpdatatables_get_handsontable_data', array($this->simpleTableController, 'getHandsontableData'));
        add_action('wp_ajax_wpdatatables_save_simple_table_data', array($this->simpleTableController, 'saveDataSimpleTable'));

        // Manual- / file-based table constructor CRUD.
        add_action('wp_ajax_wpdatatables_create_manual_table', array($this->manualTableController, 'createManualTable'));
        add_action('wp_ajax_wpdatatables_preview_file_table', array($this->manualTableController, 'constructorPreviewFileTable'));
        add_action('wp_ajax_wpdatatables_constructor_read_file_data', array($this->manualTableController, 'constructorReadFileData'));
        add_action('wp_ajax_wpdatatables_add_new_manual_column', array($this->manualTableController, 'addNewManualColumn'));
        add_action('wp_ajax_wpdatatables_delete_manual_column', array($this->manualTableController, 'deleteManualColumn'));

        // Column distinct-values lookup + Nested-JSON roots.
        add_action('wp_ajax_wpdatatable_get_column_distinct_values', array($this->columnController, 'readDistinctValuesFromTable'));
        add_action('wp_ajax_wpdatatables_get_nested_json_roots', array($this->nestedJsonController, 'getNestedJsonRoots'));

        // Chart-wizard (premium WPDataChart): render from data, save (id +
        // shortcode), list all tables / all charts.
        add_action('wp_ajax_wpdatatable_show_chart_from_data', array($this->chartController, 'showChartFromData'));
        add_action('wp_ajax_wpdatatable_save_chart_get_shortcode', array($this->chartController, 'saveChart'));
        add_action('wp_ajax_wpdatatable_list_all_tables', array($this->chartController, 'listAllTables'));
        add_action('wp_ajax_wpdatatable_list_all_charts', array($this->chartController, 'listAllCharts'));

        // Plugin / add-on licence activation + deactivation.
        add_action('wp_ajax_wpdatatables_activate_plugin', array($this->activationController, 'activatePlugin'));
        add_action('wp_ajax_wpdatatables_deactivate_plugin', array($this->activationController, 'deactivatePlugin'));

        // Frontend data path: column possible-values lookup + Elementor
        // do-shortcode. Both have priv + nopriv variants (frontend).
        add_action('wp_ajax_wpdatatables_get_column_possible_values', array($this->columnController, 'getColumnPossibleValues'));
        add_action('wp_ajax_nopriv_wpdatatables_get_column_possible_values', array($this->columnController, 'getColumnPossibleValues'));
        add_action('wp_ajax_wpdatatables_do_shortcode_elementor', array($this->elementorController, 'doShortcode'));
        add_action('wp_ajax_nopriv_wpdatatables_do_shortcode_elementor', array($this->elementorController, 'doShortcode'));

        // Frontend editing CRUD: save a row (standard editor), save edited
        // cells (Excel-like editor), delete a single row, delete multiple rows.
        // All priv + nopriv (frontend path).
        add_action('wp_ajax_wdt_save_table_frontend', array($this->tableEditController, 'saveTableFrontend'));
        add_action('wp_ajax_nopriv_wdt_save_table_frontend', array($this->tableEditController, 'saveTableFrontend'));
        add_action('wp_ajax_wdt_save_table_cells_frontend', array($this->tableEditController, 'saveTableCellsFrontend'));
        add_action('wp_ajax_nopriv_wdt_save_table_cells_frontend', array($this->tableEditController, 'saveTableCellsFrontend'));
        add_action('wp_ajax_wdt_delete_table_row', array($this->tableEditController, 'deleteTableRow'));
        add_action('wp_ajax_nopriv_wdt_delete_table_row', array($this->tableEditController, 'deleteTableRow'));
        add_action('wp_ajax_wdt_delete_table_rows', array($this->tableEditController, 'deleteTableRows'));
        add_action('wp_ajax_nopriv_wdt_delete_table_rows', array($this->tableEditController, 'deleteTableRows'));

        // The hot frontend server-side data path: the DataTables AJAX data
        // request. priv + nopriv.
        add_action('wp_ajax_get_wdtable', array($this->dataController, 'getAjaxData'));
        add_action('wp_ajax_nopriv_get_wdtable', array($this->dataController, 'getAjaxData'));

        // Permissions admin screen CRUD.
        add_action('wp_ajax_wpdatatables_load_permissions', array($this->permissionsController, 'loadPermissions'));
        add_action('wp_ajax_wpdatatables_save_permission', array($this->permissionsController, 'savePermission'));
        add_action('wp_ajax_wpdatatables_update_permission', array($this->permissionsController, 'updatePermission'));
        add_action('wp_ajax_wpdatatables_delete_permission', array($this->permissionsController, 'deletePermission'));
        add_action('wp_ajax_wpdatatables_get_permission', array($this->permissionsController, 'getPermission'));
        add_action('wp_ajax_wpdatatables_permissions_meta', array($this->permissionsController, 'getPermissionsMeta'));
        add_action('wp_ajax_wpdatatables_search_permission_users', array($this->permissionsController, 'searchPermissionUsers'));

        add_action('wp_ajax_wdtSaveDeactivationinfo', array($this->deactivationFeedbackController, 'saveDeactivationInfo'));
    }
}
