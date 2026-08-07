<?php

declare (strict_types=1);
namespace WPDT\Melograno\UsageTracker\Core;

use WPDT\Melograno\UsageTracker\Collectors\ConsentNoticeCollectorInterface;
use WPDT\Melograno\UsageTracker\Collectors\PluginCollectorInterface;
/**
 * Entry point and orchestrator for plugin usage telemetry.
 *
 * Lifecycle:
 *  - init() registers one tracker per collector slug, registers WordPress hooks per
 *    host plugin file, runs any upgrade migration, and boots scheduling once per slug.
 *  - The static facade (setConsent/isConsentEnabled/getSettings/updateSettings/
 *    renderConsentAdminNotice/deleteStoredOptions) resolves the tracker for a single
 *    registered slug automatically, or accepts an explicit collector when several
 *    Melograno plugins share this library in one request.
 *
 * This class is the only place that mutates consent state and (un)schedules the
 * cron event. ConsentNoticeService only makes decisions and owns the notice option.
 */
class UsageTracker
{
    private const DEFAULT_ENDPOINT = 'https://bi.melograno.io/v1/usage';
    /** @var array<string, bool> */
    private static array $bootstrapped = [];
    /** @var array<string, self> */
    private static array $instances = [];
    private PluginCollectorInterface $collector;
    private ConsentManager $consentManager;
    private Anonymizer $anonymizer;
    private HttpClient $httpClient;
    private ?ConsentNoticeService $consentNoticeService;
    public function __construct(PluginCollectorInterface $collector)
    {
        $this->collector = $collector;
        $this->consentManager = new ConsentManager($collector->getConsentOptionName());
        $this->anonymizer = new Anonymizer();
        $this->httpClient = new HttpClient();
        $this->consentNoticeService = $collector instanceof ConsentNoticeCollectorInterface ? new ConsentNoticeService($collector) : null;
    }
    /**
     * @param string|null $pluginFile Host plugin main file path; registers deactivation hooks when set.
     * @param string|null $savedVersion Host plugin version stored before upgrade; runs consent migration when it differs from $currentVersion.
     * @param string|null $currentVersion Host plugin version after upgrade.
     */
    public static function init(PluginCollectorInterface $collector, ?string $pluginFile = null, ?string $savedVersion = null, ?string $currentVersion = null) : void
    {
        $slug = $collector->getPluginSlug();
        $tracker = new self($collector);
        self::$instances[$slug] = $tracker;
        if ($pluginFile !== null) {
            $tracker->registerLifecycleHooks($pluginFile);
            $tracker->registerConsentNoticeHooks();
            $tracker->runUpgradeMigration($savedVersion, $currentVersion);
        }
        if (isset(self::$bootstrapped[$slug])) {
            return;
        }
        self::$bootstrapped[$slug] = \true;
        $tracker->boot();
    }
    public static function renderConsentAdminNotice(?PluginCollectorInterface $collector = null) : void
    {
        self::instance($collector)->renderAdminNotice();
    }
    public static function deleteStoredOptions(?PluginCollectorInterface $collector = null) : void
    {
        self::instance($collector)->deleteOptions();
    }
    public static function setConsent(bool $enabled, ?PluginCollectorInterface $collector = null, bool $armNoticeOnDisable = \false) : void
    {
        self::instance($collector)->applyConsent($enabled, $armNoticeOnDisable);
    }
    public static function isConsentEnabled(?PluginCollectorInterface $collector = null) : bool
    {
        return self::instance($collector)->consentManager->isEnabled();
    }
    /**
     * @return array<string, mixed>
     */
    public static function getSettings(?PluginCollectorInterface $collector = null) : array
    {
        return ['usageTrackingEnabled' => self::isConsentEnabled($collector)];
    }
    /**
     * Applies all library-managed settings from the incoming array
     * and removes the handled keys so they don't leak into plugin storage.
     */
    /**
     * @param bool $armNoticeOnDisable When consent is turned off, arm the admin notice (e.g. welcome wizard).
     *                                 Otherwise the notice is dismissed as a definitive opt-out.
     */
    public static function updateSettings(array &$settings, ?PluginCollectorInterface $collector = null, bool $armNoticeOnDisable = \false) : void
    {
        if (\array_key_exists('usageTrackingEnabled', $settings)) {
            self::setConsent((bool) $settings['usageTrackingEnabled'], $collector, $armNoticeOnDisable);
            unset($settings['usageTrackingEnabled']);
            unset($settings['armUsageTrackingNoticeOnDisable']);
        }
    }
    public function boot() : void
    {
        \add_action($this->collector->getCronHookName(), [$this, 'send']);
        if (!$this->consentManager->isConfigured()) {
            $this->initializeConsentForNewInstallation();
        }
        if ($this->consentManager->isEnabled()) {
            $this->enableScheduling();
        }
    }
    public function enableScheduling() : void
    {
        $hook = $this->collector->getCronHookName();
        if (!\wp_next_scheduled($hook)) {
            $this->send();
            \wp_schedule_event(\time(), $this->collector->getCronSchedule(), $hook);
        }
    }
    public function send() : void
    {
        if (!$this->consentManager->isEnabled()) {
            return;
        }
        $payload = $this->anonymizer->anonymize($this->collector->collect());
        $this->httpClient->post($this->endpoint(), $payload);
    }
    /**
     * Single source of truth for consent writes and the cron scheduling that follows.
     *
     * @param bool $armNoticeOnDisable Arm the admin notice when disabling consent (welcome wizard).
     *                                 Otherwise disabling dismisses the notice as a definitive opt-out.
     */
    public function applyConsent(bool $enabled, bool $armNoticeOnDisable = \false) : void
    {
        if ($enabled === $this->consentManager->isEnabled() && ($enabled || $this->consentManager->isConfigured())) {
            if ($this->consentNoticeService !== null && ($enabled || !$armNoticeOnDisable)) {
                $this->consentNoticeService->dismissNoticeIfArmed();
            }
            return;
        }
        if ($enabled) {
            $this->consentManager->enable();
            $this->enableScheduling();
            if ($this->consentNoticeService !== null) {
                $this->consentNoticeService->dismissNoticeIfArmed();
            }
        } else {
            $this->consentManager->disable();
            $this->unschedule();
            if ($this->consentNoticeService === null) {
                return;
            }
            if ($armNoticeOnDisable && $this->collector instanceof ConsentNoticeCollectorInterface && $this->collector->shouldShowAdminNotice()) {
                $this->consentNoticeService->armNotice();
            } else {
                $this->consentNoticeService->dismissNotice();
            }
        }
    }
    /**
     * Applies the collector's default consent the first time the option is stored,
     * arming the admin notice when telemetry stays off by default.
     */
    private function initializeConsentForNewInstallation() : void
    {
        if ($this->consentNoticeService === null) {
            return;
        }
        $enabled = $this->consentNoticeService->defaultConsent();
        $this->applyConsent($enabled);
        $this->consentNoticeService->armNoticeForNewInstallation($enabled);
    }
    /**
     * Disables consent for eligible users upgrading into the opt-in release and arms the notice.
     */
    private function runUpgradeMigration(?string $savedVersion, ?string $currentVersion) : void
    {
        if ($this->consentNoticeService === null) {
            return;
        }
        if (empty($savedVersion) || empty($currentVersion) || $savedVersion === $currentVersion) {
            return;
        }
        if (!$this->consentNoticeService->shouldRunUpgradeMigration($savedVersion, $currentVersion)) {
            return;
        }
        $this->applyConsent(\false);
        $this->consentNoticeService->armNotice();
    }
    private function registerLifecycleHooks(string $pluginFile) : void
    {
        \register_deactivation_hook($pluginFile, function () : void {
            $this->unschedule();
        });
    }
    private function registerConsentNoticeHooks() : void
    {
        if ($this->consentNoticeService === null || !$this->collector instanceof ConsentNoticeCollectorInterface) {
            return;
        }
        \add_action('admin_footer', function () : void {
            $this->renderAdminNotice();
        });
        $ajaxPrefix = $this->collector->getConsentNoticeAjaxPrefix();
        $nonceAction = $ajaxPrefix . '_usage_tracking_consent';
        \add_action('wp_ajax_' . $ajaxPrefix . '_enable_usage_tracking', function () use($nonceAction) : void {
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error();
            }
            \check_ajax_referer($nonceAction);
            $this->applyConsent(\true);
            \wp_send_json_success();
        });
        \add_action('wp_ajax_' . $ajaxPrefix . '_dismiss_usage_tracking_notice', function () use($nonceAction) : void {
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error();
            }
            \check_ajax_referer($nonceAction);
            if ($this->consentNoticeService !== null) {
                $this->consentNoticeService->dismissNotice();
            }
            \wp_send_json_success();
        });
    }
    private function renderAdminNotice() : void
    {
        $service = $this->consentNoticeService;
        if ($service === null) {
            return;
        }
        if (!$service->canCurrentUserSeeAdminNotice() || !$service->shouldShowNotice()) {
            return;
        }
        if ($this->collector instanceof ConsentNoticeCollectorInterface) {
            $this->collector->renderConsentAdminNotice();
        }
    }
    private function deleteOptions() : void
    {
        $this->consentManager->delete();
        if ($this->collector instanceof ConsentNoticeCollectorInterface) {
            (new NoticeManager($this->collector->getNoticeOptionName()))->delete();
        }
    }
    private function unschedule() : void
    {
        \wp_clear_scheduled_hook($this->collector->getCronHookName());
    }
    private function endpoint() : string
    {
        if (\defined('MELOGRANO_BI_GATE_URL')) {
            return \MELOGRANO_BI_GATE_URL . '/v1/usage';
        }
        return self::DEFAULT_ENDPOINT;
    }
    private static function instance(?PluginCollectorInterface $collector = null) : self
    {
        if ($collector !== null) {
            $slug = $collector->getPluginSlug();
            if (isset(self::$instances[$slug])) {
                return self::$instances[$slug];
            }
            throw new \RuntimeException('UsageTracker is not initialized for collector slug: ' . $slug);
        }
        if (\count(self::$instances) === 1) {
            return \reset(self::$instances);
        }
        if (\count(self::$instances) === 0) {
            throw new \RuntimeException('UsageTracker is not initialized.');
        }
        throw new \RuntimeException('Multiple usage trackers are registered; pass the collector to resolve the correct instance.');
    }
}
