<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin;

use WPDataTables\Routes\Ai\Ai;
use WPDataTables\Routes\Routes;
use WPDataTables\Services\Ai\AiService;

/**
 * AiHooks — the WordPress glue for the AI features.
 *
 * The fourth registration seam (alongside FrontendHooks / AjaxHooks /
 * AdminHooks), wired once from {@see Plugin::registerHooks()}. It does two
 * things:
 *
 *  1. Registers the AI REST routes on `rest_api_init`. This is intentionally
 *     separate from the Developer-tier {@see Routes::registerRoutes()} bootstrap:
 *     AI is admin-only but ships on every licence tier, so its routes must
 *     always register (gated per-request by the `manage_options` capability).
 *  2. Enqueues the admin JS and localises `wpAiSettings` on the constructor and
 *     chart wizard screens. PHP renders the table-generator and chart-suggester
 *     panels; interactive features gate on `aiAvailable` in JS.
 *
 * @package WPDataTables\Plugin
 */
class AiHooks
{
    /** Admin page hook suffix for the constructor + linked-source table settings. */
    const CONSTRUCTOR_HOOK = 'wpdatatables_page_wpdatatables-constructor';

    /** Admin page hook suffix for the Create Chart wizard. */
    const CHART_WIZARD_HOOK = 'wpdatatables_page_wpdatatables-chart-wizard';

    /** @var AiService */
    private $ai;

    public function __construct(AiService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * @return void
     */
    public function register(): void
    {
        add_action('rest_api_init', static function () {
            Ai::registerRoutes(Plugin::container(), Routes::$routeNamespace);
        });

        add_action('wpdatatables_constructor_ai_table_generator', [$this, 'renderTableGeneratorSection']);
        add_action('wpdatatables_ai_sql_assistant', [$this, 'renderSqlAssistantSection']);
        add_action('wpdatatables_chart_wizard_ai_suggester', [$this, 'renderChartSuggesterSection']);
        // Feature 2 temporarily disabled — re-enable with constructor_1_3 / constructor_1_4 hooks.
        // add_action('wpdatatables_ai_query_constructor', [$this, 'renderQueryConstructorSection'], 10, 1);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdmin']);
    }

    /**
     * Render the AI table generator block on constructor step 1.
     *
     * Always outputs markup: an active form when AI is configured, or an
     * unavailable card with a link to WordPress AI settings.
     *
     * @return void
     */
    public function renderTableGeneratorSection(): void
    {
        $available = $this->ai->isAvailable();

        $wdt_ai_available           = $available;
        $wdt_ai_models              = $available ? $this->ai->listTextModels() : [];
        $wdt_ai_default_model       = $available ? $this->ai->defaultModel() : '';
        $wdt_ai_unavailable_message = $this->ai->getUnavailableMessage();
        $wdt_ai_settings_url        = $this->ai->getSettingsUrl();
        $wdt_ai_settings_action     = $this->ai->getSettingsActionLabel();

        include WDT_TEMPLATE_PATH . 'admin/constructor/steps/constructor_ai_table_generator.inc.php';
    }

    /**
     * Render the AI SQL Assistant above `#wdt-mysql-query` (SQL-linked tables).
     *
     * Only outputs when an AI provider is configured — same gate as Feature 1.
     *
     * @return void
     */
    public function renderSqlAssistantSection(): void
    {
        if (!$this->ai->isAvailable()) {
            return;
        }

        $wdt_ai_available     = true;
        $wdt_ai_models        = $this->ai->listTextModels();
        $wdt_ai_default_model = $this->ai->defaultModel();

        include WDT_TEMPLATE_PATH . 'admin/table-settings/ai_sql_assistant.inc.php';
    }

    /**
     * Render the Query Constructor Assistant on WP / MySQL GUI builder steps.
     *
     * @param string $builder_type `wp` or `mysql`.
     * @return void
     */
    public function renderQueryConstructorSection($builder_type = 'mysql'): void
    {
        if (!$this->ai->isAvailable()) {
            return;
        }

        $builder_type = sanitize_key((string) $builder_type);
        if (!in_array($builder_type, ['wp', 'mysql'], true)) {
            return;
        }

        $wdt_ai_available     = true;
        $wdt_ai_models        = $this->ai->listTextModels();
        $wdt_ai_default_model = $this->ai->defaultModel();
        $wdt_ai_builder_type  = $builder_type;

        include WDT_TEMPLATE_PATH . 'admin/constructor/steps/constructor_ai_query_constructor.inc.php';
    }

    /**
     * Render the AI Chart Type Suggester on chart wizard step 1.
     *
     * Always outputs markup: an active form when AI is configured, or an
     * unavailable card with a link to WordPress AI settings.
     *
     * @return void
     */
    public function renderChartSuggesterSection(): void
    {
        $available = $this->ai->isAvailable();

        $wdt_ai_available           = $available;
        $wdt_ai_models              = $available ? $this->ai->listTextModels() : [];
        $wdt_ai_default_model       = $available ? $this->ai->defaultModel() : '';
        $wdt_ai_unavailable_message = $this->ai->getUnavailableMessage();
        $wdt_ai_settings_url        = $this->ai->getSettingsUrl();
        $wdt_ai_settings_action     = $this->ai->getSettingsActionLabel();
        $wdt_ai_tables              = class_exists('WPDataTable')
            ? \WPDataTable::getAllTablesExceptSimple()
            : [];

        include WDT_TEMPLATE_PATH . 'admin/chart_wizard/chart_ai_suggester.inc.php';
    }

    /**
     * Enqueue AI scripts on constructor and chart wizard admin pages.
     *
     * @param string $hook Current admin page hook suffix.
     * @return void
     */
    public function enqueueAdmin($hook): void
    {
        if ($hook !== self::CONSTRUCTOR_HOOK && $hook !== self::CHART_WIZARD_HOOK) {
            return;
        }

        wp_enqueue_script(
            'wdt-ai',
            WDT_ROOT_URL . 'assets/js/ai/wdt-ai.js',
            ['jquery'],
            WDT_CURRENT_VERSION,
            true
        );

        if ($hook === self::CONSTRUCTOR_HOOK) {
            wp_enqueue_script(
                'wdt-ai-table-generator',
                WDT_ROOT_URL . 'assets/js/ai/wdt-ai-table-generator.js',
                ['jquery', 'wdt-ai', 'wdt-common'],
                WDT_CURRENT_VERSION,
                true
            );

            wp_enqueue_script(
                'wdt-ai-query-assistant',
                WDT_ROOT_URL . 'assets/js/ai/wdt-ai-query-assistant.js',
                ['jquery', 'wdt-ai', 'wdt-common', 'wdt-ace'],
                WDT_CURRENT_VERSION,
                true
            );

            // Feature 2 temporarily disabled — re-enable with constructor_1_3 / constructor_1_4 hooks.
            // wp_enqueue_script(
            //     'wdt-ai-query-constructor',
            //     WDT_ROOT_URL . 'assets/js/ai/wdt-ai-query-constructor.js',
            //     ['jquery', 'wdt-ai', 'wdt-common'],
            //     WDT_CURRENT_VERSION,
            //     true
            // );
        }

        if ($hook === self::CHART_WIZARD_HOOK) {
            $chartSuggesterPath = WDT_ROOT_PATH . 'assets/js/ai/wdt-ai-chart-suggester.js';
            wp_enqueue_script(
                'wdt-ai-chart-suggester',
                WDT_ROOT_URL . 'assets/js/ai/wdt-ai-chart-suggester.js',
                ['jquery', 'wdt-ai', 'wdt-bootstrap-select'],
                file_exists($chartSuggesterPath) ? (string) filemtime($chartSuggesterPath) : WDT_CURRENT_VERSION,
                true
            );
        }

        $available = $this->ai->isAvailable();

        wp_localize_script('wdt-ai', 'wpAiSettings', [
            'aiAvailable'        => $available,
            'aiSettingsUrl'      => $this->ai->getSettingsUrl(),
            'aiSettingsAction'   => $this->ai->getSettingsActionLabel(),
            'unavailableReason'  => $this->ai->getUnavailableReasonCode(),
            'nonce'              => wp_create_nonce('wp_rest'),
            'restUrl'            => rest_url(Routes::$routeNamespace . '/ai/'),
            'models'             => $available ? $this->ai->listTextModels() : [],
            'defaultModel'       => $available ? $this->ai->defaultModel() : '',
        ]);
    }
}
