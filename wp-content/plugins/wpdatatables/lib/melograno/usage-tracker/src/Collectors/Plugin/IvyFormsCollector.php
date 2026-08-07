<?php

declare (strict_types=1);
namespace WPDT\Melograno\UsageTracker\Collectors\Plugin;

use WPDT\IvyForms\Common\Exceptions\QueryExecutionException;
use WPDT\IvyForms\Repository\Entry\EntryRepository;
use WPDT\IvyForms\Repository\Form\FormRepository;
use WPDT\IvyForms\Services\InstallActions\DB\Entry\EntriesTable;
use WPDT\IvyForms\Services\InstallActions\DB\Form\FormsTable;
use WPDT\Melograno\UsageTracker\Collectors\BaseCollector;
use WPDT\Melograno\UsageTracker\Collectors\ConsentNoticeCollectorInterface;
class IvyFormsCollector extends BaseCollector implements ConsentNoticeCollectorInterface
{
    /** @var array<string, string> */
    private const LICENSE_TIER_MAP = ['lite' => 'lite', 'essentials' => 'essentials', 'growth' => 'growth', 'agency' => 'agency'];
    public function getPluginSlug() : string
    {
        return 'ivyforms';
    }
    public function getConsentOptionName() : string
    {
        return 'ivyforms_usage_tracking_consent';
    }
    public function getNoticeOptionName() : string
    {
        return 'ivyforms_show_usage_tracking_notice';
    }
    public function getOptInMigrationVersion() : ?string
    {
        return null;
    }
    public function shouldEnableConsentByDefault() : bool
    {
        return \false;
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
        return 'ivyforms';
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
        $pluginUrl = \defined('IVYFORMS_URL') ? IVYFORMS_URL : '';
        return ['accentColor' => '#1BBEA0', 'iconUrl' => $pluginUrl . 'frontend/src/assets/images/logos/logo-only-admin.svg', 'iconAlt' => 'IvyForms', 'title' => \__('Improve IvyForms', 'ivyforms'), 'description' => \__('Help us improve IvyForms by sharing anonymous data about your plugin usage. No personal data is collected.', 'ivyforms'), 'enableLabel' => \__('Improve plugin', 'ivyforms'), 'learnMoreLabel' => \__('Learn more', 'ivyforms'), 'learnMoreUrl' => 'https://ivyforms.com/usage-data-privacy/', 'dismissLabel' => \__('Dismiss this notice.', 'ivyforms'), 'spaPageId' => 'ivyforms-app', 'spaAppRootSelector' => '#ivyforms-app'];
    }
    public function renderConsentAdminNotice() : void
    {
        if (!$this->isOnIvyFormsAdminPage()) {
            return;
        }
        \wp_enqueue_style('ivyforms-usage-tracking-notice-css', $this->getResourcesUrl() . '/usage-tracking-notice.css', [], IVYFORMS_VERSION);
        $usageTrackingCollector = $this;
        $usageTrackingNoticePresentation = $this->getConsentNoticePresentation();
        include \dirname(\dirname(\dirname(__DIR__))) . '/resources/usage-tracking-notice.php';
    }
    private function getResourcesUrl() : string
    {
        $resourcesDir = \dirname(\dirname(\dirname(__DIR__))) . '/resources';
        if (\defined('IVYFORMS_PATH') && \defined('IVYFORMS_FILE')) {
            $relativePath = \ltrim(\str_replace('\\', '/', \str_replace(IVYFORMS_PATH, '', $resourcesDir)), '/');
            return \plugins_url($relativePath, IVYFORMS_FILE);
        }
        $pluginUrl = \defined('IVYFORMS_URL') ? IVYFORMS_URL : '';
        return $pluginUrl . 'backend/scope-vendor/melograno/usage-tracker/resources';
    }
    /**
     * Maps IvyForms pro.license.plan values to telemetry license slugs.
     */
    public static function normalizeLicenseTier(?string $ivyFormsPlan) : ?string
    {
        if ($ivyFormsPlan === null) {
            return null;
        }
        $key = \strtolower(\trim($ivyFormsPlan));
        return self::LICENSE_TIER_MAP[$key] ?? null;
    }
    /**
     * @return array<string, mixed>
     */
    protected function pluginPayload() : array
    {
        $data = ['plugin_version' => \defined('IVYFORMS_VERSION') ? IVYFORMS_VERSION : null];
        $rawPlan = $this->resolveLicence();
        $licenseTier = self::normalizeLicenseTier($rawPlan);
        if ($licenseTier !== null) {
            $data['license'] = $licenseTier;
        }
        $data = \array_merge($data, $this->resolveRecordCounts());
        $data = \array_filter($data, static function ($value) {
            return $value !== null;
        });
        return $data;
    }
    /**
     * @return array<string, mixed>
     */
    protected function resolveRecordCounts() : array
    {
        try {
            $formRepository = new FormRepository(FormsTable::getTableName());
            $formCounts = $formRepository->getFilterCount();
            $activeCount = (int) ($formCounts['publishedTrueCount'] ?? 0);
            $inactiveCount = (int) ($formCounts['publishedFalseCount'] ?? 0);
            $data = ['active_forms_count' => $activeCount, 'forms_count' => $inactiveCount + $activeCount];
            $minCreated = $formRepository->getEarliestCreatedAt();
            if (!empty($minCreated)) {
                $timestamp = \strtotime($minCreated);
                if ($timestamp) {
                    $data['first_form_created_at'] = \gmdate('c', $timestamp);
                }
            }
            $entryRepository = new EntryRepository(EntriesTable::getTableName());
            $entryCounts = $entryRepository->getFilterCount();
            $data['submissions_count'] = (int) ($entryCounts['readTrueCount'] ?? 0) + (int) ($entryCounts['readFalseCount'] ?? 0);
            return $data;
        } catch (QueryExecutionException $exception) {
            return [];
        }
    }
    /**
     * Returns the raw IvyForms plan when readable, lite for non-Pro installs,
     * or null when Pro is active but license data is missing or malformed.
     */
    protected function resolveLicence() : ?string
    {
        if (!$this->isProPluginActive()) {
            return 'lite';
        }
        $raw = \get_option('ivyforms_settings');
        if (empty($raw)) {
            return null;
        }
        $settings = \json_decode($raw, \true);
        if (!\is_array($settings)) {
            return null;
        }
        $plan = $settings['pro']['license_plan'] ?? null;
        if (!\is_string($plan) || \trim($plan) === '') {
            return null;
        }
        return $plan;
    }
    protected function isProPluginActive() : bool
    {
        if (\defined('IVYFORMS_PRO_VERSION')) {
            return \true;
        }
        return \class_exists('WPDT\\IvyFormsPro\\Plugin\\Plugin', \false);
    }
    private function isOnIvyFormsAdminPage() : bool
    {
        $page = isset($_GET['page']) ? \sanitize_text_field(\wp_unslash((string) $_GET['page'])) : '';
        if ($page !== '' && \strpos($page, 'ivyforms') === 0) {
            return \true;
        }
        $screen = \function_exists('get_current_screen') ? \get_current_screen() : null;
        return $screen !== null && \strpos($screen->id, 'ivyforms') !== \false;
    }
}
