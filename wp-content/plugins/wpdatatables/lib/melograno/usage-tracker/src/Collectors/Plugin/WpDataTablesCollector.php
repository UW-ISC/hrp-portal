<?php

declare (strict_types=1);
namespace WPDT\Melograno\UsageTracker\Collectors\Plugin;

use WPDT\Melograno\UsageTracker\Collectors\BaseCollector;
use WPDT\Melograno\UsageTracker\Collectors\ConsentNoticeCollectorInterface;
class WpDataTablesCollector extends BaseCollector implements ConsentNoticeCollectorInterface
{
    /** @var list<string> */
    private const ALLOWED_TABLES = ['wpdatatables', 'wpdatacharts'];
    /** @var array<string, string> */
    private const LICENSE_TIER_MAP = ['lite' => 'lite', 'starter' => 'starter', 'standard' => 'standard', 'pro' => 'pro', 'developer' => 'developer'];
    /** @var array<string, string> */
    private const CONTENT_TYPE_TABLE_MAP = ['table' => 'wpdatatables', 'chart' => 'wpdatacharts'];
    public function getPluginSlug() : string
    {
        return 'wpdatatables';
    }
    public function getConsentOptionName() : string
    {
        return 'wpdatatables_usage_tracking_consent';
    }
    public function getNoticeOptionName() : string
    {
        return 'wpdatatables_show_usage_tracking_notice';
    }
    public function getOptInMigrationVersion() : ?string
    {
        return null;
    }
    public function shouldEnableConsentByDefault() : bool
    {
        return $this->isPaid();
    }
    public function shouldShowAdminNotice() : bool
    {
        return \true;
    }
    public function shouldMigrateConsentOnUpgrade() : bool
    {
        return \false;
    }
    public function getConsentNoticeAjaxPrefix() : string
    {
        return 'wpdt';
    }
    /**
     * @return array{
     *     accentColor: string,
     *     iconUrl: string,
     *     iconAlt: string,
     *     title: string,
     *     description: string,
     *     enableLabel: string,
     *     learnMoreLabel: string,
     *     learnMoreUrl: string,
     *     dismissLabel: string,
     *     spaPageId?: string|null,
     *     spaAppRootSelector?: string|null
     * }
     */
    public function getConsentNoticePresentation() : array
    {
        $pluginUrl = \defined('WDT_ROOT_URL') ? \WDT_ROOT_URL : '';
        return ['accentColor' => '#0088cc', 'iconUrl' => $pluginUrl . 'assets/img/logo-large.png', 'iconAlt' => 'wpDataTables', 'title' => \__('Improve wpDataTables', 'wpdatatables'), 'description' => \__('Help us improve wpDataTables by sharing anonymous data about your plugin usage. No personal data is collected.', 'wpdatatables'), 'enableLabel' => \__('Improve plugin', 'wpdatatables'), 'learnMoreLabel' => \__('Learn more', 'wpdatatables'), 'learnMoreUrl' => 'https://wpdatatables.com/usage-data-privacy/', 'dismissLabel' => \__('Dismiss this notice.', 'wpdatatables')];
    }
    public function renderConsentAdminNotice() : void
    {
        if (!$this->isOnWpDataTablesAdminPage()) {
            return;
        }
        $version = \defined('WDT_CURRENT_VERSION') ? \WDT_CURRENT_VERSION : '1.0.0';
        \wp_enqueue_style('wpdt-usage-tracking-notice-css', $this->getResourcesUrl() . '/usage-tracking-notice.css', [], $version);
        $usageTrackingCollector = $this;
        $usageTrackingNoticePresentation = $this->getConsentNoticePresentation();
        include \dirname(\dirname(\dirname(__DIR__))) . '/resources/usage-tracking-notice.php';
    }
    /**
     * Maps wpDataTables licence tier values to telemetry license slugs.
     */
    public static function normalizeLicenseTier(?string $licenseTier) : ?string
    {
        if ($licenseTier === null) {
            return null;
        }
        $key = \strtolower(\trim($licenseTier));
        return self::LICENSE_TIER_MAP[$key] ?? null;
    }
    /**
     * True for premium packages (not Lite). Does not require wdtActivated —
     * consent is initialized on first boot, before a purchase code exists.
     */
    protected function isPaid() : bool
    {
        return !$this->isLitePluginInstall();
    }
    /**
     * @return array<string, mixed>
     */
    protected function pluginPayload() : array
    {
        $data = ['plugin_version' => \defined('WDT_CURRENT_VERSION') ? \WDT_CURRENT_VERSION : null];
        if (!\function_exists('get_plugins')) {
            require_once \ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (\function_exists('get_plugin_data') && \defined('WDT_ROOT_PATH')) {
            $pluginData = \get_plugin_data(\WDT_ROOT_PATH . 'wpdatatables.php', \false, \false);
            if (!empty($pluginData['Version'])) {
                $data['plugin_version'] = $pluginData['Version'];
            }
        }
        $rawTier = $this->resolveLicenseTier();
        $data['license'] = self::normalizeLicenseTier($rawTier);
        $data['tables_count'] = $this->resolveContentCount('table');
        $data['charts_count'] = $this->resolveContentCount('chart');
        $tableTypes = $this->resolveTableTypeCounts();
        if ($tableTypes !== null && $tableTypes !== []) {
            $data['table_types'] = $tableTypes;
        }
        $chartTypes = $this->resolveChartTypeCounts();
        if ($chartTypes !== null && $chartTypes !== []) {
            $data['chart_types'] = $chartTypes;
        }
        $data['first_content_created_at'] = $this->resolveFirstContentCreatedAt();
        $data = \array_filter($data, static function ($value) {
            return $value !== null;
        });
        return $data;
    }
    protected function resolveContentCount(string $contentType) : ?int
    {
        if (\class_exists('WDTTools')) {
            return (int) \WDTTools::getTablesCount($contentType);
        }
        $table = self::CONTENT_TYPE_TABLE_MAP[$contentType] ?? '';
        return $this->countPluginRows($table);
    }
    /**
     * @return array<string, int>|null
     */
    protected function resolveTableTypeCounts() : ?array
    {
        return $this->countGroupedByColumn('wpdatatables', 'table_type');
    }
    /**
     * @return array<string, int>|null
     */
    protected function resolveChartTypeCounts() : ?array
    {
        return $this->countGroupedByColumn('wpdatacharts', 'type');
    }
    protected function resolveFirstContentCreatedAt() : ?string
    {
        return null;
    }
    protected function resolveLicenseTier() : ?string
    {
        if ($this->isLitePluginInstall()) {
            return 'lite';
        }
        $this->ensureTierDetectionLoaded();
        if (!\function_exists('wdtmcp_detect_integrations') || !\function_exists('wdtmcp_detect_tier_from_features')) {
            return null;
        }
        return \wdtmcp_detect_tier_from_features(\wdtmcp_detect_integrations());
    }
    private function isLitePluginInstall() : bool
    {
        if (\defined('WDT_INITIAL_LITE_VERSION')) {
            return \true;
        }
        return \false;
    }
    private function ensureTierDetectionLoaded() : void
    {
        if (\function_exists('wdtmcp_detect_tier_from_features') && \function_exists('wdtmcp_detect_integrations')) {
            return;
        }
        if (!\defined('WDT_ROOT_PATH')) {
            return;
        }
        $path = \WDT_ROOT_PATH . 'Infrastructure/WP/MCP/Abilities/get-system-info.php';
        if (\is_file($path)) {
            require_once $path;
        }
    }
    private function getResourcesUrl() : string
    {
        $resourcesDir = \dirname(\dirname(\dirname(__DIR__))) . '/resources';
        if (\defined('WDT_ROOT_PATH') && \defined('WDT_BASENAME')) {
            $relativePath = \ltrim(\str_replace('\\', '/', \str_replace(\WDT_ROOT_PATH, '', $resourcesDir)), '/');
            return \plugins_url($relativePath, \WDT_BASENAME);
        }
        $pluginUrl = \defined('WDT_ROOT_URL') ? \WDT_ROOT_URL : '';
        return $pluginUrl . 'lib/melograno/usage-tracker/resources';
    }
    private function isOnWpDataTablesAdminPage() : bool
    {
        $page = isset($_GET['page']) ? \sanitize_text_field(\wp_unslash((string) $_GET['page'])) : '';
        if ($page !== '' && \strpos($page, 'wpdatatables') === 0) {
            return \true;
        }
        $screen = \function_exists('get_current_screen') ? \get_current_screen() : null;
        return $screen !== null && \strpos($screen->id, 'wpdatatables') !== \false;
    }
    private function countPluginRows(string $table) : ?int
    {
        if (!\in_array($table, self::ALLOWED_TABLES, \true)) {
            return null;
        }
        global $wpdb;
        if (!isset($wpdb)) {
            return 0;
        }
        $count = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . $table);
        if ($count === null) {
            if (!empty($wpdb->last_error)) {
                \error_log('[melograno/usage-tracker] wpDataTables count failed: ' . $wpdb->last_error);
            }
            return null;
        }
        return (int) $count;
    }
    /**
     * @return array<string, int>|null
     */
    private function countGroupedByColumn(string $table, string $column) : ?array
    {
        if (!\in_array($table, self::ALLOWED_TABLES, \true)) {
            return null;
        }
        global $wpdb;
        if (!isset($wpdb)) {
            return [];
        }
        $output = \defined('ARRAY_A') ? ARRAY_A : 'ARRAY_A';
        $rows = $wpdb->get_results('SELECT `' . $column . '` AS type_key, COUNT(*) AS cnt FROM ' . $wpdb->prefix . $table . ' GROUP BY `' . $column . '`', $output);
        if ($rows === null) {
            if (!empty($wpdb->last_error)) {
                \error_log('[melograno/usage-tracker] wpDataTables type count failed: ' . $wpdb->last_error);
            }
            return null;
        }
        $counts = [];
        foreach ($rows as $row) {
            $key = isset($row['type_key']) ? \trim((string) $row['type_key']) : '';
            if ($key === '') {
                continue;
            }
            $counts[$key] = (int) ($row['cnt'] ?? 0);
        }
        return $counts;
    }
}
