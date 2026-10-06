<?php

/**
 * PHP-DI service bindings.
 *
 * Multi-dependency services and controllers are wired explicitly here;
 * dependency-free / auto-wirable services may be omitted (PHP-DI autowires
 * them on first `get()`). Mirrors the ivyforms `services.php` pattern.
 *
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

use WPDataTables\Repository\Cache\CacheRepositoryInterface;
use WPDataTables\Services\Cache\CacheManager;
use WPDataTables\Services\Connection\ConnectionFactory;
use WPDataTables\Services\Connection\ConnectionService;
use WPDataTables\Services\DataSource\DataSourceFactory;
use WPDataTables\Services\DataSource\ExcelDataSource;
use WPDataTables\Services\DataSource\FileImportService;
use WPDataTables\Services\DataSource\TableConstructorService;
use WPDataTables\Services\DataSource\MySqlQueryDataSource;
use WPDataTables\Infrastructure\Excel\SpreadsheetReaderAdapter;
use WPDataTables\Services\Permissions\AccessRulesMigrator;
use WPDataTables\Services\Permissions\AccessRulesValidator;
use WPDataTables\Services\Permissions\AdminMenuPermissionMapper;
use WPDataTables\Services\Permissions\PermissionResolver;
use WPDataTables\Services\Permissions\PermissionsEnforcer;
use WPDataTables\Services\Permissions\PermissionsService;
use WPDataTables\Services\Permissions\PermissionsAdminService;
use WPDataTables\Repository\Permissions\AccessRulesRepository;
use WPDataTables\Services\Settings\SettingsService;
use WPDataTables\Services\Integration\IntegrationService;
use WPDataTables\Services\Integration\UpsellNoticeRenderer;
use WPDataTables\Repository\Column\ColumnRepositoryInterface;
use WPDataTables\Repository\Rows\RowsRepositoryInterface;
use WPDataTables\Repository\Table\TableRepositoryInterface;
use WPDataTables\Repository\Template\TemplateRepositoryInterface;
use WPDataTables\Services\Table\FilterService;
use WPDataTables\Services\Table\PaginationService;
use WPDataTables\Services\Table\ServerSideProcessor;
use WPDataTables\Services\Table\SortService;
use WPDataTables\Services\Table\SummaryService;
use WPDataTables\Factory\Column\ColumnTypeFactory;
use WPDataTables\Services\Table\TableConfigService;
use WPDataTables\Services\Table\TableService;
use WPDataTables\Services\Table\TableLoadService;
use WPDataTables\Services\Table\TableArrayBuilderService;
use WPDataTables\Services\Table\TableHydrationService;
use WPDataTables\Services\Table\ServerSideDataService;
use WPDataTables\Services\Rest\TableReadService;
use WPDataTables\Services\Rest\ChartReadService;
use WPDataTables\Services\Rest\ColumnReadService;
use WPDataTables\Services\Rest\TableDataWriteService;
use WPDataTables\PublicApi\Hooks\RestApiHook;
use WPDataTables\Services\Chart\ChartEngineService;
use WPDataTables\Services\Chart\ChartService;
use WPDataTables\Repository\Chart\ChartRepositoryInterface;
use WPDataTables\Rendering\AssetManager;
use WPDataTables\Rendering\TableRenderer;
use WPDataTables\Rendering\TemplateEngine;
use WPDataTables\Rendering\FilterRenderer;
use WPDataTables\Rendering\EditDialogRenderer;
use WPDataTables\Rendering\ColumnCssBuilder;
use WPDataTables\Rendering\ColumnDefinitionBuilder;
use WPDataTables\Rendering\ExcelTableRenderer;
use WPDataTables\Plugin\ShortcodeController;
use WPDataTables\Plugin\FrontendHooks;
use WPDataTables\Plugin\AjaxHooks;
use WPDataTables\Plugin\AdminHooks;
use WPDataTables\Plugin\PluginLifecycleHooks;
use WPDataTables\Plugin\EditorHooks;
use WPDataTables\Plugin\PluginUpdateHooks;
use WPDataTables\Plugin\Admin\AdminMenu;
use WPDataTables\Plugin\Admin\AdminAssets;
use WPDataTables\Plugin\Admin\AdminPageController;
use WPDataTables\Plugin\Admin\TablesPageController;
use WPDataTables\Plugin\Admin\ChartsPageController;
use WPDataTables\Plugin\Admin\PermissionsPageController;
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
use WPDataTables\Services\Admin\AdminNoticeService;
use WPDataTables\Controllers\Rest\Table\SaveTableController;
use WPDataTables\Controllers\Rest\Table\UpdateTableController;
use WPDataTables\Services\Tools\ToolsService;
use WPDataTables\Plugin\AiHooks;
use WPDataTables\Services\Ai\AiService;
use WPDataTables\Services\Ai\PromptBuilder as AiPromptBuilder;
use WPDataTables\Services\Ai\OutputValidator as AiOutputValidator;
use WPDataTables\Services\Ai\TableMetaService as AiTableMetaService;
use WPDataTables\Services\Ai\SchemaContextService as AiSchemaContextService;
use WPDataTables\Controllers\Rest\Ai\GenerateTableController;
use WPDataTables\Controllers\Rest\Ai\QueryAssistantController;
use WPDataTables\Controllers\Rest\Ai\QueryConstructorController;
use WPDataTables\Controllers\Rest\Ai\SuggestChartController;

return [
    // Settings & Permissions leaf domains.
    SettingsService::class => function () {
        return new SettingsService();
    },

    // Phase J — decomposed WDTTools helpers (static API; registered for DI consumers).
    ToolsService::class => function () {
        return new ToolsService();
    },

    AdminNoticeService::class => function () {
        return new AdminNoticeService();
    },

    DeactivationFeedbackController::class => function () {
        return new DeactivationFeedbackController();
    },

    PluginLifecycleHooks::class => function ($container) {
        return new PluginLifecycleHooks(
            $container->get(AdminNoticeService::class)
        );
    },

    EditorHooks::class => function () {
        return new EditorHooks();
    },

    PluginUpdateHooks::class => function () {
        return new PluginUpdateHooks();
    },

    // Typed runtime column formatters (Phase E). All whitelisted types live in
    // backend/src/Entity/Column/*; createLegacyColumn() is an add-on fallback only.
    ColumnTypeFactory::class => function () {
        return new ColumnTypeFactory();
    },

    FileImportService::class => function () {
        return new FileImportService();
    },

    TableConstructorService::class => function ($container) {
        return new TableConstructorService(
            $container->get(FileImportService::class)
        );
    },

    // Integration registry. {@see Plugin::bootIntegrations()} calls
    // IntegrationService::init(); UpsellNoticeRenderer owns the admin "upgrade" notice
    // callbacks (dependency-free).
    UpsellNoticeRenderer::class => function () {
        return new UpsellNoticeRenderer();
    },

    IntegrationService::class => function ($container) {
        return new IntegrationService(
            $container->get(UpsellNoticeRenderer::class)
        );
    },

    PermissionResolver::class => function ($container) {
        return new PermissionResolver(
            $container->get(AccessRulesRepository::class)
        );
    },

    PermissionsEnforcer::class => function ($container) {
        return new PermissionsEnforcer(
            $container->get(PermissionResolver::class)
        );
    },

    PermissionsService::class => function ($container) {
        return new PermissionsService(
            $container->get(PermissionsEnforcer::class),
            $container->get(PermissionResolver::class)
        );
    },

    PermissionsAdminService::class => function ($container) {
        return new PermissionsAdminService(
            $container->get(AccessRulesRepository::class),
            $container->get(AccessRulesValidator::class)
        );
    },

    AdminMenuPermissionMapper::class => function ($container) {
        return new AdminMenuPermissionMapper(
            $container->get(PermissionsService::class)
        );
    },

    AccessRulesValidator::class => function ($container) {
        return new AccessRulesValidator(
            $container->get(TableRepositoryInterface::class),
            $container->get(ChartRepositoryInterface::class)
        );
    },

    AccessRulesMigrator::class => function ($container) {
        return new AccessRulesMigrator(
            $container->get(AccessRulesRepository::class)
        );
    },

    // AccessRulesRepository is bound in repository.php; synchronizer is static.

    // Persistence spine (cache service over CacheRepository).
    CacheManager::class => function ($container) {
        return new CacheManager(
            $container->get(CacheRepositoryInterface::class)
        );
    },

    // Connection layer (external DB factory + metadata).
    ConnectionService::class => function () {
        return new ConnectionService();
    },

    ConnectionFactory::class => function ($container) {
        return new ConnectionFactory(
            $container->get(ConnectionService::class)
        );
    },

    // DataSource adapters. The dependency-free string/JSON/Google/XML
    // strategies are autowired on demand; the factory and the Excel adapter
    // (which depends on the spreadsheet reader) are wired explicitly.
    DataSourceFactory::class => function ($container) {
        return new DataSourceFactory($container);
    },

    ExcelDataSource::class => function ($container) {
        return new ExcelDataSource(
            $container->get(SpreadsheetReaderAdapter::class)
        );
    },

    // Table engine. FilterService / SummaryService / SortService /
    // PaginationService are dependency-free and autowired on demand; the
    // multi-dependency engine classes are wired explicitly.
    MySqlQueryDataSource::class => function ($container) {
        return new MySqlQueryDataSource(
            $container->get(FilterService::class),
            $container->get(SummaryService::class),
            $container->get(PaginationService::class),
            $container->get(SortService::class),
            $container->get(TableLoadService::class),
            $container->get(TableArrayBuilderService::class)
        );
    },

    TableLoadService::class => function ($container) {
        return new TableLoadService(
            $container->get(TableConfigService::class),
            $container->get(TableHydrationService::class)
        );
    },

    ServerSideProcessor::class => function ($container) {
        return new ServerSideProcessor(
            $container->get(MySqlQueryDataSource::class)
        );
    },

    TableConfigService::class => function ($container) {
        return new TableConfigService(
            $container->get(TableRepositoryInterface::class),
            $container->get(ColumnRepositoryInterface::class),
            $container->get(RowsRepositoryInterface::class),
            $container->get(TemplateRepositoryInterface::class)
        );
    },

    TableService::class => function ($container) {
        return new TableService(
            $container->get(ServerSideProcessor::class),
            $container->get(TableConfigService::class),
            $container->get(TableRenderer::class),
            $container->get(ExcelTableRenderer::class),
            $container->get(TableHydrationService::class),
            $container->get(TableArrayBuilderService::class),
            $container->get(TableLoadService::class)
        );
    },

    ServerSideDataService::class => function ($container) {
        return new ServerSideDataService(
            $container->get(TableConfigService::class)
        );
    },

    TableReadService::class => function ($container) {
        return new TableReadService(
            $container->get(ServerSideDataService::class),
            $container->get(TableConfigService::class),
            $container->get(TableLoadService::class)
        );
    },

    ChartReadService::class => function ($container) {
        return new ChartReadService(
            $container->get(ChartEngineService::class)
        );
    },

    ColumnReadService::class => function ($container) {
        return new ColumnReadService(
            $container->get(TableConfigService::class)
        );
    },

    TableDataWriteService::class => function ($container) {
        return new TableDataWriteService(
            $container->get(TableConfigService::class)
        );
    },

    SaveTableController::class => function ($container) {
        return new SaveTableController(
            $container->get(TableConfigService::class)
        );
    },

    UpdateTableController::class => function ($container) {
        return new UpdateTableController(
            $container->get(TableConfigService::class)
        );
    },

    RestApiHook::class => function ($container) {
        return new RestApiHook(
            $container->get(SettingsService::class)
        );
    },

    // Chart engine (build / save / render) + auth-decoupled delete.
    ChartEngineService::class => function ($container) {
        return new ChartEngineService(
            $container->get(ChartRepositoryInterface::class)
        );
    },

    ChartService::class => function ($container) {
        return new ChartService(
            $container->get(ChartRepositoryInterface::class)
        );
    },

    // Rendering layer (frontend asset enqueuing + template engine).
    // Both are dependency-free; bound explicitly for discoverability.
    AssetManager::class => function () {
        return new AssetManager();
    },

    TemplateEngine::class => function () {
        return new TemplateEngine();
    },

    // Rendering layer (table HTML assembly). Owns the generateTable /
    // renderWithJSAndStyles / renderModal bodies; depends on the template engine
    // (legacy includes) and the asset manager (frontend enqueue).
    TableRenderer::class => function ($container) {
        return new TableRenderer(
            $container->get(TemplateEngine::class),
            $container->get(AssetManager::class)
        );
    },

    // Rendering layer (filter-form + edit-dialog markup). Each renders its
    // legacy template through the template engine; the WPDataTable facade's
    // renderFilterForm() / renderEditDialog() delegate here.
    FilterRenderer::class => function ($container) {
        return new FilterRenderer(
            $container->get(TemplateEngine::class)
        );
    },

    EditDialogRenderer::class => function ($container) {
        return new EditDialogRenderer(
            $container->get(TemplateEngine::class)
        );
    },

    // Rendering layer (per-column CSS + DataTables/Handsontable column JSON).
    ColumnCssBuilder::class => function () {
        return new ColumnCssBuilder();
    },

    ColumnDefinitionBuilder::class => function () {
        return new ColumnDefinitionBuilder();
    },

    // Rendering layer (Excel/simple Handsontable tables).
    ExcelTableRenderer::class => function ($container) {
        return new ExcelTableRenderer(
            $container->get(TemplateEngine::class),
            $container->get(AssetManager::class),
            $container->get(ColumnDefinitionBuilder::class)
        );
    },

    // Frontend WP glue. ShortcodeController owns the shortcode-handler bodies
    // (dependency-free — it calls the static facades, exactly as the legacy
    // global functions did); FrontendHooks wires the shortcodes + filtering
    // widget and is fired once from Plugin.
    ShortcodeController::class => function ($container) {
        return new ShortcodeController(
            $container->get(TableLoadService::class)
        );
    },

    FrontendHooks::class => function ($container) {
        return new FrontendHooks(
            $container->get(ShortcodeController::class)
        );
    },

    // Admin-ajax WP glue. NoticeController owns the notice-dismissal handler
    // bodies (dependency-free — pure update_option / user-meta, exactly as the
    // legacy global functions did); AjaxHooks wires the `wp_ajax_*` actions to it
    // and is fired once from Plugin.
    NoticeController::class => function () {
        return new NoticeController();
    },

    // Plugin-settings admin-ajax handlers. Dependency-free (the bodies call the
    // global WDTSettingsController facade, itself a shim into SettingsService);
    // autowirable, bound for discoverability.
    SettingsController::class => function () {
        return new SettingsController();
    },

    // Separate-database connection admin-ajax handlers. The bodies call the
    // global Connection facade (a shim into ConnectionService/ConnectionFactory)
    // + wpDataTableConstructor/WDTTools; dependency-free, autowirable, bound for
    // discoverability.
    ConnectionController::class => function () {
        return new ConnectionController();
    },

    // Formula-column preview admin-ajax handlers. The bodies call the global
    // WPDataTable engine (loadWpDataTable + calcFormulaPreview/
    // checkFormulaPreview); dependency-free, autowirable, bound for
    // discoverability.
    FormulaController::class => function ($container) {
        return new FormulaController(
            $container->get(PermissionsService::class)
        );
    },

    // Duplicate-table/chart admin-ajax handlers. The bodies call the global
    // WDTConfigController + Connection facades (shims into the layered config/
    // connection services) + $wpdb; dependency-free, autowirable, bound for
    // discoverability.
    DuplicateController::class => function ($container) {
        return new DuplicateController(
            $container->get(PermissionsService::class)
        );
    },

    // Table-configuration admin-ajax handlers. The bodies call the global
    // WDTConfigController facade (a shim into the layered config/repository
    // services) + the WPDataTable engine; dependency-free, autowirable, bound
    // for discoverability.
    TableConfigController::class => function ($container) {
        return new TableConfigController(
            $container->get(PermissionsService::class)
        );
    },

    // Simple-table (handsontable) constructor CRUD handlers. The bodies call the
    // global WPDataTableRows engine + the WDTConfigController facade (a shim into
    // the layered config/repository services); dependency-free, autowirable,
    // bound for discoverability.
    SimpleTableController::class => function ($container) {
        return new SimpleTableController(
            $container->get(PermissionsService::class)
        );
    },

    // Manual- / file-based table constructor CRUD handlers. The bodies call the
    // global wpDataTableConstructor engine; dependency-free, autowirable, bound
    // for discoverability.
    ManualTableController::class => function ($container) {
        return new ManualTableController(
            $container->get(TableConstructorService::class),
            $container->get(FileImportService::class),
            $container->get(PermissionsService::class)
        );
    },

    // Column distinct-values lookup (WPDataTable/WDTColumn) and Nested-JSON roots
    // (WDTNestedJson). Both dependency-free, autowirable, bound for
    // discoverability.
    ColumnController::class => function () {
        return new ColumnController();
    },

    NestedJsonController::class => function ($container) {
        return new NestedJsonController(
            $container->get(PermissionsService::class)
        );
    },

    // Chart-wizard admin-ajax handlers — delegate to ChartEngineService.
    ChartController::class => function ($container) {
        return new ChartController(
            $container->get(ChartEngineService::class),
            $container->get(PermissionsService::class)
        );
    },

    // Plugin/add-on licence activation admin-ajax handlers. The bodies talk to
    // the Melograno Store over curl + WDTTools; dependency-free, autowirable,
    // bound for discoverability.
    ActivationController::class => function () {
        return new ActivationController();
    },

    // Elementor do-shortcode frontend handler. Dependency-free (body calls
    // WDTPermissionsEnforcer + WPDataTable + do_shortcode), autowirable, bound
    // for discoverability.
    ElementorController::class => function () {
        return new ElementorController();
    },

    // Frontend editing CRUD handlers. The bodies call the global
    // WDTConfigController / Connection / WDTTools / WPDataTable facades + $wpdb;
    // dependency-free, autowirable, bound for discoverability.
    TableEditController::class => function () {
        return new TableEditController();
    },

    // The hot frontend server-side data path (DataTables AJAX data request). The
    // body calls the global WDTConfigController / WPDataTable / WPExcelDataTable /
    // WDTPermissionsEnforcer facades + the queryBasedConstruct -> TableService
    // engine; dependency-free, autowirable, bound for discoverability.
    DataController::class => function () {
        return new DataController();
    },

    // Permissions admin-ajax handlers. Bodies delegate to PermissionsAdminService.
    PermissionsController::class => function ($container) {
        return new PermissionsController(
            $container->get(PermissionsAdminService::class)
        );
    },

    // Admin menu registration. AdminMenu owns the wdtAdminMenu body
    // (dependency-free — the page-render callbacks are legacy global functions
    // referenced by name); AdminHooks wires the `admin_menu` hook to it and is
    // fired once from Plugin.
    AdminMenu::class => function ($container) {
        return new AdminMenu(
            $container->get(AdminPageController::class),
            $container->get(TablesPageController::class),
            $container->get(ChartsPageController::class),
            $container->get(PermissionsPageController::class),
            $container->get(AdminMenuPermissionMapper::class)
        );
    },

    // Admin CSS/JS enqueuing. AdminAssets owns the wdtAdminEnqueue + per-page
    // enqueue bodies (dependency-free — the bodies call WP enqueue functions +
    // the WDTTools / WDTSettingsController facades); AdminHooks wires the
    // `admin_enqueue_scripts` + `wpdatatables_enqueue_on_admin_pages` hooks to it.
    AdminAssets::class => function () {
        return new AdminAssets();
    },

    // The simple/informational admin page renderers (Dashboard, Settings,
    // Support, Welcome, System Info, Getting Started, Lite VS Premium, Add-ons).
    // Dependency-free template-include bodies; the legacy `wdt*` page callbacks
    // (referenced by name in AdminMenu's add_submenu_page calls) remain one-line
    // delegator shims.
    AdminPageController::class => function () {
        return new AdminPageController();
    },

    // The table admin page renderers (Browse Tables + delete, Edit / Simple-table
    // editor, Constructor). Dependency-free bodies (calling the WPDataTable /
    // WDTConfigController / WDTTools facades + the WDTBrowseTable list-table); the
    // legacy `wdt*` page callbacks remain one-line delegator shims, so AdminMenu's
    // add_submenu_page wiring is untouched.
    TablesPageController::class => function ($container) {
        return new TablesPageController(
            $container->get(PermissionsService::class)
        );
    },

    // The chart admin page renderers (Browse Charts + delete, Chart Wizard).
    // Dependency-free bodies (calling the premium WPDataChart engine + the
    // WDTBrowseChartsTable list-table); the legacy `wdt*` page callbacks remain
    // one-line delegator shims, so AdminMenu's add_submenu_page wiring is
    // untouched.
    ChartsPageController::class => function ($container) {
        return new ChartsPageController(
            $container->get(PermissionsService::class)
        );
    },

    PermissionsPageController::class => function () {
        return new PermissionsPageController();
    },

    AdminHooks::class => function ($container) {
        return new AdminHooks(
            $container->get(AdminMenu::class),
            $container->get(AdminAssets::class),
            $container->get(PermissionsAdminService::class),
            $container->get(DeactivationFeedbackController::class)
        );
    },

    // AI features (admin-only, all tiers). AiService is the boundary over the WP
    // 7.0 `wp_ai_client_prompt()` builder; PromptBuilder / OutputValidator /
    // TableMetaService are dependency-free helpers. The REST controllers compose
    // them; AiHooks registers the ungated `rest_api_init` AI routes + the
    // constructor-page asset enqueue, and is fired once from Plugin.
    AiService::class => function () {
        return new AiService();
    },

    AiPromptBuilder::class => function () {
        return new AiPromptBuilder();
    },

    AiOutputValidator::class => function () {
        return new AiOutputValidator();
    },

    AiTableMetaService::class => function ($container) {
        return new AiTableMetaService(
            $container->get(TableConfigService::class),
            $container->get(TableLoadService::class)
        );
    },

    AiSchemaContextService::class => function ($container) {
        return new AiSchemaContextService(
            $container->get(ConnectionService::class)
        );
    },

    GenerateTableController::class => function ($container) {
        return new GenerateTableController(
            $container->get(AiService::class),
            $container->get(AiPromptBuilder::class),
            $container->get(AiOutputValidator::class),
            $container->get(AiSchemaContextService::class)
        );
    },

    QueryConstructorController::class => function ($container) {
        return new QueryConstructorController(
            $container->get(AiService::class),
            $container->get(AiPromptBuilder::class),
            $container->get(AiOutputValidator::class),
            $container->get(AiSchemaContextService::class)
        );
    },

    QueryAssistantController::class => function ($container) {
        return new QueryAssistantController(
            $container->get(AiService::class),
            $container->get(AiPromptBuilder::class),
            $container->get(AiOutputValidator::class),
            $container->get(AiSchemaContextService::class)
        );
    },

    SuggestChartController::class => function ($container) {
        return new SuggestChartController(
            $container->get(AiService::class),
            $container->get(AiPromptBuilder::class),
            $container->get(AiOutputValidator::class),
            $container->get(AiTableMetaService::class)
        );
    },

    AiHooks::class => function ($container) {
        return new AiHooks(
            $container->get(AiService::class)
        );
    },

    AjaxHooks::class => function ($container) {
        return new AjaxHooks(
            $container->get(NoticeController::class),
            $container->get(SettingsController::class),
            $container->get(ConnectionController::class),
            $container->get(FormulaController::class),
            $container->get(DuplicateController::class),
            $container->get(TableConfigController::class),
            $container->get(SimpleTableController::class),
            $container->get(ManualTableController::class),
            $container->get(ColumnController::class),
            $container->get(NestedJsonController::class),
            $container->get(ChartController::class),
            $container->get(ActivationController::class),
            $container->get(ElementorController::class),
            $container->get(TableEditController::class),
            $container->get(DataController::class),
            $container->get(PermissionsController::class),
            $container->get(DeactivationFeedbackController::class)
        );
    },
];
