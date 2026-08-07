<?php

declare (strict_types=1);
namespace WPDT\Melograno\UsageTracker\Collectors\Plugin;

use WPDT\Melograno\UsageTracker\Collectors\BaseCollector;
use WPDT\Melograno\UsageTracker\Collectors\ConsentNoticeCollectorInterface;
class AmeliaCollector extends BaseCollector implements ConsentNoticeCollectorInterface
{
    /** @var array<string, string> */
    private const LICENSE_TIER_MAP = ['lite' => 'lite', 'starter' => 'starter', 'basic' => 'standard', 'pro' => 'pro', 'developer' => 'elite'];
    public function getPluginSlug() : string
    {
        return 'ameliabooking';
    }
    public function getConsentOptionName() : string
    {
        return 'amelia_usage_tracking_consent';
    }
    public function getNoticeOptionName() : string
    {
        return 'amelia_show_usage_tracking_notice';
    }
    public function getOptInMigrationVersion() : ?string
    {
        return '2.4.2';
    }
    public function shouldEnableConsentByDefault() : bool
    {
        return \WPDT\AmeliaBooking\Infrastructure\Licence\Licence::isPremium();
    }
    public function shouldShowAdminNotice() : bool
    {
        return \true;
    }
    public function shouldMigrateConsentOnUpgrade() : bool
    {
        $licence = \WPDT\AmeliaBooking\Infrastructure\Licence\Licence::getLicence();
        return \is_string($licence) && \strcasecmp($licence, \WPDT\AmeliaBooking\Infrastructure\Licence\LicenceConstants::LITE) === 0;
    }
    public function getConsentNoticeAjaxPrefix() : string
    {
        return 'amelia';
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
        return ['accentColor' => '#4a3bd6', 'iconUrl' => AMELIA_URL . 'public/img/amelia-logo-admin-icon.svg', 'iconAlt' => 'Amelia', 'title' => \AmeliaBooking\Infrastructure\WP\Translations\BackendStrings::get('improve_amelia'), 'description' => \AmeliaBooking\Infrastructure\WP\Translations\BackendStrings::get('usage_tracking_description'), 'enableLabel' => \AmeliaBooking\Infrastructure\WP\Translations\BackendStrings::get('improve_plugin'), 'learnMoreLabel' => \AmeliaBooking\Infrastructure\WP\Translations\BackendStrings::get('learn_more'), 'learnMoreUrl' => 'https://wpamelia.com/usage-data-privacy/', 'dismissLabel' => \__('Dismiss this notice.', 'wpamelia'), 'spaPageId' => 'amelia-redesign-page', 'spaAppRootSelector' => '#amelia-redesign'];
    }
    public function renderConsentAdminNotice() : void
    {
        if (!$this->isOnAmeliaAdminPage() || $this->isOnWelcomePage()) {
            return;
        }
        $resourcesUrl = AMELIA_URL . 'vendor/melograno/usage-tracker/resources';
        \wp_enqueue_style('amelia-usage-tracking-notice-css', $resourcesUrl . '/usage-tracking-notice.css', [], AMELIA_VERSION);
        $usageTrackingCollector = $this;
        $usageTrackingNoticePresentation = $this->getConsentNoticePresentation();
        include \dirname(\dirname(\dirname(__DIR__))) . '/resources/usage-tracking-notice.php';
    }
    /**
     * Maps Amelia activation.licence values to telemetry license slugs.
     */
    public static function normalizeLicenseTier(?string $ameliaLicence) : ?string
    {
        if ($ameliaLicence === null) {
            return null;
        }
        $trimmed = \trim($ameliaLicence);
        if ($trimmed === '') {
            return 'elite';
        }
        $key = \strtolower($trimmed);
        return self::LICENSE_TIER_MAP[$key] ?? null;
    }
    /**
     * @return array<string, mixed>
     */
    protected function pluginPayload() : array
    {
        $data = ['plugin_version' => AMELIA_VERSION];
        $rawLicence = $this->resolveAmeliaLicence();
        $licenseTier = self::normalizeLicenseTier($rawLicence);
        if ($licenseTier !== null) {
            $data['license'] = $licenseTier;
        }
        $data = \array_merge($data, $this->resolveBookingMetrics());
        $featureTelemetry = $this->resolveFeatures();
        if ($featureTelemetry !== null) {
            $data['features'] = $featureTelemetry['features'];
            if ($featureTelemetry['feature_metrics'] !== []) {
                $data['feature_metrics'] = $featureTelemetry['feature_metrics'];
            }
        }
        return \array_filter($data, static function ($value) {
            return $value !== null;
        });
    }
    /**
     * @return array<string, mixed>
     */
    protected function resolveBookingMetrics() : array
    {
        $container = (require AMELIA_PATH . '/src/Infrastructure/ContainerConfig/container.php');
        $customerBookingRepository = $container->get('domain.booking.customerBooking.repository');
        $appointmentRepository = $container->get('domain.booking.appointment.repository');
        $data = [];
        $minCreated = $customerBookingRepository->getEarliestCreatedAt();
        if ($minCreated !== null && $minCreated !== '') {
            $timestamp = \strtotime($minCreated);
            if ($timestamp !== \false) {
                $data['first_booking_created_at'] = \gmdate('c', $timestamp);
            }
        }
        $data['appointment_bookings_count'] = $customerBookingRepository->getAppointmentBookingsCount();
        $data['appointments_count'] = $appointmentRepository->getAppointmentsCount();
        $data['event_bookings_count'] = $customerBookingRepository->getEventBookingsCount();
        $data['approved_appointment_bookings_count'] = $customerBookingRepository->getApprovedAppointmentBookingsCount();
        $data['approved_appointments_count'] = $appointmentRepository->getApprovedAppointmentsCount();
        return $data;
    }
    /**
     * @return array{
     *     features: array<string, bool>,
     *     feature_metrics: array<string, array{usage_count: int}>
     * }|null null when collection fails
     */
    protected function resolveFeatures() : ?array
    {
        if (!\defined('AMELIA_PATH')) {
            return null;
        }
        try {
            $container = (require AMELIA_PATH . '/src/Infrastructure/ContainerConfig/container.php');
            /** @var \AmeliaBooking\Domain\Services\Settings\SettingsService $settingsService */
            $settingsService = $container->get('domain.settings.service');
            return AmeliaFeatureTelemetry::collect($settingsService, $container);
        } catch (\Throwable $e) {
            return null;
        }
    }
    protected function resolveAmeliaLicence() : ?string
    {
        $licence = \WPDT\AmeliaBooking\Infrastructure\Licence\Licence::getLicence();
        return \is_string($licence) ? $licence : null;
    }
    private function isOnAmeliaAdminPage() : bool
    {
        $page = $this->currentAdminPageSlug();
        if ($page !== '' && \strpos($page, 'wpamelia') === 0) {
            return \true;
        }
        $screen = \function_exists('get_current_screen') ? \get_current_screen() : null;
        return $screen !== null && \strpos($screen->id, 'wpamelia') !== \false;
    }
    private function isOnWelcomePage() : bool
    {
        return $this->currentAdminPageSlug() === 'wpamelia-welcome';
    }
    private function currentAdminPageSlug() : string
    {
        return isset($_GET['page']) ? \sanitize_text_field(\wp_unslash((string) $_GET['page'])) : '';
    }
}
