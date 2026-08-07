<?php

declare (strict_types=1);
namespace WPDT\Melograno\UsageTracker\Collectors\Plugin;

use WPDT\AmeliaBooking\Domain\Services\Settings\SettingsService;
use WPDT\AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Bookable\ExtrasTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Bookable\PackagesTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Bookable\ResourcesTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Bookable\ServicesTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Coupon\CouponsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\CustomField\CustomFieldsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Location\LocationsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Tax\TaxesTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking\AppointmentsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking\CustomerBookingsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking\EventsPeriodsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking\EventsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking\EventsTagsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking\EventsTicketsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Notification\NotificationsLogTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Notification\NotificationsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Payment\PaymentsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\User\Provider\ProvidersGoogleCalendarTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\User\Provider\ProvidersOutlookCalendarTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\User\UsersTable;
/**
 * Phase 2 Amelia F&I telemetry: report enabled features with in-use signals from DB/settings.
 */
final class AmeliaFeatureTelemetry
{
    /** @var list<string> */
    private const FEATURE_CODES = ['customFields', 'customNotifications', 'tax', 'invoices', 'coupons', 'depositPayment', 'timezones', 'extras', 'recurringAppointments', 'recurringEvents', 'packages', 'cart', 'waitingListAppointments', 'waitingList', 'tickets', 'eTickets', 'resources', 'employeeBadge', 'eventTags', 'noShowTag', 'webhooks', 'apis'];
    /** @var list<string> */
    private const INTEGRATION_CODES = ['googleCalendar', 'appleCalendar', 'outlookCalendar', 'ivy', 'zoom', 'facebookPixel', 'googleAnalytics', 'lessonSpace', 'recaptcha', 'whatsapp', 'googleSocialLogin', 'facebookSocialLogin', 'mailchimp', 'buddyboss'];
    /** @var list<string> */
    private const PAYMENT_GATEWAY_CODES = ['stripe', 'payPal', 'mollie', 'square', 'razorpay', 'barion', 'wc'];
    /**
     * @return array{
     *     features: array<string, bool>,
     *     feature_metrics: array<string, array{usage_count: int}>
     * }
     */
    public static function collect(SettingsService $settingsService, Container $container) : array
    {
        $features = [];
        $featureMetrics = [];
        foreach (self::FEATURE_CODES as $code) {
            if ($settingsService->isFeatureEnabled($code)) {
                $features[$code] = self::isFeatureInUse($code, $settingsService, $container);
            }
        }
        if ($settingsService->isFeatureEnabled('customPricing')) {
            $customPricingUsage = self::collectCustomPricingUsage($container);
            $features['customPricingByDuration'] = $customPricingUsage['byDuration'];
            $features['customPricingByNumberOfPeople'] = $customPricingUsage['byNumberOfPeople'];
            $features['customPricingByDateAndTime'] = $customPricingUsage['byDateAndTime'];
        }
        // No settings toggle: group booking is inferred from a service allowing more than 1 person per booking.
        if (self::hasGroupBookingUsage($container)) {
            $features['groupBooking'] = \true;
        }
        // No settings toggle: multilingual support is inferred from additional languages enabled in global settings.
        if (self::hasMultilingualSupportUsage($settingsService)) {
            $features['multilingualSupport'] = \true;
        }
        // No settings toggle: multiple locations is inferred from at least two locations in DB.
        if (self::hasMultipleLocationsUsage($container)) {
            $features['multipleLocations'] = \true;
        }
        // No settings toggle: SMTP is inferred from configured SMTP mail service in notification settings.
        if (self::hasSmtpUsage($settingsService)) {
            $features['smtp'] = \true;
        }
        foreach (self::INTEGRATION_CODES as $code) {
            if ($settingsService->isFeatureEnabled($code)) {
                $features[$code] = self::isFeatureInUse($code, $settingsService, $container);
            }
        }
        foreach (self::PAYMENT_GATEWAY_CODES as $code) {
            if ($settingsService->isFeatureEnabled($code)) {
                $features[$code] = self::paymentGatewayHasCredentials($settingsService, $code);
                $featureMetrics[$code] = ['usage_count' => self::paymentGatewayTransactionCount($container, $code)];
            }
        }
        if (self::shouldReportOnSite($settingsService)) {
            $features['onSite'] = self::isFeatureInUse('onSite', $settingsService, $container);
            $featureMetrics['onSite'] = ['usage_count' => self::paymentGatewayTransactionCount($container, 'onSite')];
        }
        return ['features' => $features, 'feature_metrics' => $featureMetrics];
    }
    private static function isFeatureInUse(string $code, SettingsService $settingsService, Container $container) : bool
    {
        try {
            switch ($code) {
                case 'coupons':
                    return self::tableHasRecords($container->getDatabaseConnection(), CouponsTable::getTableName());
                case 'packages':
                    return self::tableHasRecords($container->getDatabaseConnection(), PackagesTable::getTableName());
                case 'tax':
                    return self::tableHasRecords($container->getDatabaseConnection(), TaxesTable::getTableName());
                case 'resources':
                    return self::tableHasRecords($container->getDatabaseConnection(), ResourcesTable::getTableName());
                case 'customFields':
                    return self::tableHasRecords($container->getDatabaseConnection(), CustomFieldsTable::getTableName());
                case 'customNotifications':
                    return self::tableHasRecords($container->getDatabaseConnection(), NotificationsTable::getTableName(), 'customName IS NOT NULL');
                case 'extras':
                    return self::tableHasRecords($container->getDatabaseConnection(), ExtrasTable::getTableName());
                case 'eventTags':
                    return self::tableHasRecords($container->getDatabaseConnection(), EventsTagsTable::getTableName());
                case 'tickets':
                    return self::tableHasRecords($container->getDatabaseConnection(), EventsTicketsTable::getTableName());
                case 'invoices':
                    return self::tableHasRecords($container->getDatabaseConnection(), PaymentsTable::getTableName(), 'invoiceNumber IS NOT NULL');
                case 'depositPayment':
                    return self::depositPaymentInUse($container);
                case 'recurringAppointments':
                    return self::tableHasRecords($container->getDatabaseConnection(), ServicesTable::getTableName(), "recurringCycle IS NOT NULL AND recurringCycle != '' AND recurringCycle != 'disabled'");
                case 'recurringEvents':
                    return self::tableHasRecords($container->getDatabaseConnection(), EventsTable::getTableName(), "recurringCycle IS NOT NULL AND recurringCycle != ''");
                case 'waitingListAppointments':
                    return self::tableHasRecords($container->getDatabaseConnection(), ServicesTable::getTableName(), self::waitingListEnabledInSettingsCondition());
                case 'waitingList':
                    return self::tableHasRecords($container->getDatabaseConnection(), EventsTable::getTableName(), self::waitingListEnabledInSettingsCondition());
                case 'eTickets':
                    return self::tableHasRecords($container->getDatabaseConnection(), CustomerBookingsTable::getTableName(), 'qrCodes IS NOT NULL AND qrCodes != \'\'');
                case 'employeeBadge':
                    return self::tableHasRecords($container->getDatabaseConnection(), UsersTable::getTableName(), "type = 'provider' AND badgeId IS NOT NULL");
                case 'noShowTag':
                    return self::tableHasRecords($container->getDatabaseConnection(), CustomerBookingsTable::getTableName(), "status = 'no-show'");
                case 'webhooks':
                    return self::webhooksInUse($settingsService);
                case 'apis':
                    return self::apisInUse($settingsService);
                case 'onSite':
                    return self::tableHasRecords($container->getDatabaseConnection(), PaymentsTable::getTableName(), "gateway = 'onSite'");
                case 'cart':
                    return self::cartInUse($container);
                case 'timezones':
                    return self::timezonesInUse($settingsService, $container);
                case 'googleCalendar':
                    return self::tableHasRecords($container->getDatabaseConnection(), ProvidersGoogleCalendarTable::getTableName());
                case 'outlookCalendar':
                    return self::tableHasRecords($container->getDatabaseConnection(), ProvidersOutlookCalendarTable::getTableName());
                case 'appleCalendar':
                    return self::tableHasRecords($container->getDatabaseConnection(), UsersTable::getTableName(), "type = 'provider' AND appleCalendarId IS NOT NULL AND appleCalendarId != ''");
                case 'zoom':
                    return self::zoomInUse($container);
                case 'lessonSpace':
                    return self::lessonSpaceInUse($container);
                case 'mailchimp':
                    return self::mailchimpConfigured($settingsService);
                case 'facebookPixel':
                    return self::nonEmpty($settingsService->getSetting('facebookPixel', 'id'));
                case 'googleAnalytics':
                    return self::nonEmpty($settingsService->getSetting('googleAnalytics', 'id'));
                case 'recaptcha':
                    return self::recaptchaConfigured($settingsService);
                case 'whatsapp':
                    return self::whatsappInUse($container);
                case 'googleSocialLogin':
                    return !empty($settingsService->getSetting('socialLogin', 'enableGoogleLogin'));
                case 'facebookSocialLogin':
                    return !empty($settingsService->getSetting('socialLogin', 'enableFacebookLogin'));
                case 'ivy':
                    return self::ivyInUse();
                case 'buddyboss':
                    return self::buddyBossInUse();
                default:
                    return \false;
            }
        } catch (\Throwable $e) {
            self::logDebugException("Amelia feature in-use check failed ({$code})", $e);
            return \false;
        }
    }
    private static function logDebugException(string $context, \Throwable $e) : void
    {
        if (!\defined('WP_DEBUG') || !\WP_DEBUG) {
            return;
        }
        \error_log('[melograno/usage-tracker] ' . $context . ': ' . $e->getMessage());
    }
    private static function shouldReportOnSite(SettingsService $settingsService) : bool
    {
        return !empty($settingsService->getSetting('payments', 'onSite'));
    }
    private static function depositPaymentInUse(Container $container) : bool
    {
        $connection = $container->getDatabaseConnection();
        $condition = "depositPayment IS NOT NULL AND depositPayment != 'disabled'";
        return self::tableHasRecords($connection, ServicesTable::getTableName(), $condition) || self::tableHasRecords($connection, PackagesTable::getTableName(), $condition) || self::tableHasRecords($connection, EventsTable::getTableName(), $condition);
    }
    private static function cartInUse(Container $container) : bool
    {
        $appointments = AppointmentsTable::getTableName();
        $services = ServicesTable::getTableName();
        return self::tableHasRecords($container->getDatabaseConnection(), $appointments, "EXISTS (\n                SELECT 1\n                FROM {$appointments} child\n                INNER JOIN {$appointments} parent ON child.parentId = parent.id\n                LEFT JOIN {$services} s ON child.serviceId = s.id\n                WHERE child.parentId IS NOT NULL\n                  AND (\n                    child.serviceId != parent.serviceId\n                    OR s.recurringCycle IS NULL\n                    OR s.recurringCycle = ''\n                    OR s.recurringCycle = 'disabled'\n                  )\n            )");
    }
    private static function waitingListEnabledInSettingsCondition() : string
    {
        return "settings IS NOT NULL\n            AND settings != ''\n            AND settings REGEXP '\"waitingList\":\\\\{[^}]*\"enabled\"[[:space:]]*:[[:space:]]*true'";
    }
    private static function timezonesInUse(SettingsService $settingsService, Container $container) : bool
    {
        $defaultTimeZone = self::getDefaultTimeZone($settingsService);
        $table = UsersTable::getTableName();
        return self::executeQuery($container->getDatabaseConnection(), "SELECT EXISTS (\n                SELECT 1\n                FROM {$table}\n                WHERE type = 'provider'\n                  AND timeZone IS NOT NULL\n                  AND timeZone != ''\n                  AND timeZone != :defaultTimeZone\n                LIMIT 1\n            ) AS found", [':defaultTimeZone' => $defaultTimeZone]);
    }
    private static function getDefaultTimeZone(SettingsService $settingsService) : string
    {
        return (string) $settingsService->getSetting('wordpress', 'timeZoneString');
    }
    /**
     * @return array{byDuration: bool, byNumberOfPeople: bool, byDateAndTime: bool}
     */
    private static function collectCustomPricingUsage(Container $container) : array
    {
        $usage = ['byDuration' => \false, 'byNumberOfPeople' => \false, 'byDateAndTime' => \false];
        try {
            $table = ServicesTable::getTableName();
            $rows = self::fetchAllRows($container->getDatabaseConnection(), "SELECT customPricing FROM {$table}\n                 WHERE customPricing IS NOT NULL\n                   AND customPricing != ''");
            foreach ($rows as $row) {
                $customPricing = \json_decode((string) ($row['customPricing'] ?? ''), \true);
                if (!\is_array($customPricing)) {
                    continue;
                }
                if (!$usage['byDuration'] && self::isDurationCustomPricingInUse($customPricing)) {
                    $usage['byDuration'] = \true;
                }
                if (!$usage['byNumberOfPeople'] && self::isNumberOfPeopleCustomPricingInUse($customPricing)) {
                    $usage['byNumberOfPeople'] = \true;
                }
                if (!$usage['byDateAndTime'] && self::isDateAndTimeCustomPricingInUse($customPricing)) {
                    $usage['byDateAndTime'] = \true;
                }
                if ($usage['byDuration'] && $usage['byNumberOfPeople'] && $usage['byDateAndTime']) {
                    break;
                }
            }
        } catch (\Throwable $e) {
            self::logDebugException('Amelia custom pricing check failed', $e);
        }
        return $usage;
    }
    /**
     * @param array<string, mixed> $customPricing
     */
    private static function isDurationCustomPricingInUse(array $customPricing) : bool
    {
        $enabled = $customPricing['enabled'] ?? null;
        if ($enabled !== \true && $enabled !== 'duration') {
            return \false;
        }
        return !empty($customPricing['durations']);
    }
    /**
     * @param array<string, mixed> $customPricing
     */
    private static function isNumberOfPeopleCustomPricingInUse(array $customPricing) : bool
    {
        if (($customPricing['enabled'] ?? null) !== 'person') {
            return \false;
        }
        return !empty($customPricing['persons']);
    }
    /**
     * @param array<string, mixed> $customPricing
     */
    private static function isDateAndTimeCustomPricingInUse(array $customPricing) : bool
    {
        if (($customPricing['enabled'] ?? null) !== 'period') {
            return \false;
        }
        $periods = $customPricing['periods'] ?? null;
        if (!\is_array($periods)) {
            return \false;
        }
        foreach ($periods['default'] ?? [] as $defaultPeriod) {
            if (!\is_array($defaultPeriod)) {
                continue;
            }
            if (!empty($defaultPeriod['days'])) {
                return \true;
            }
        }
        foreach ($periods['custom'] ?? [] as $customPeriod) {
            if (!\is_array($customPeriod)) {
                continue;
            }
            if (self::customPricingPeriodHasDates($customPeriod['dates'] ?? null)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param mixed $dates
     */
    private static function customPricingPeriodHasDates($dates) : bool
    {
        if (!\is_array($dates)) {
            return \false;
        }
        if (isset($dates['start'], $dates['end'])) {
            return $dates['start'] !== '' && $dates['end'] !== '';
        }
        return isset($dates[0], $dates[1]) && $dates[0] !== '' && $dates[1] !== '';
    }
    private static function zoomInUse(Container $container) : bool
    {
        $connection = $container->getDatabaseConnection();
        return self::tableHasRecords($connection, UsersTable::getTableName(), "type = 'provider' AND zoomUserId IS NOT NULL AND zoomUserId != ''") || self::tableHasRecords($connection, EventsTable::getTableName(), 'zoomUserId IS NOT NULL AND zoomUserId != \'\'');
    }
    private static function lessonSpaceInUse(Container $container) : bool
    {
        $connection = $container->getDatabaseConnection();
        return self::tableHasRecords($connection, AppointmentsTable::getTableName(), "lessonSpace IS NOT NULL AND lessonSpace != ''") || self::tableHasRecords($connection, EventsPeriodsTable::getTableName(), "lessonSpace IS NOT NULL AND lessonSpace != ''");
    }
    private static function webhooksInUse(SettingsService $settingsService) : bool
    {
        $webhooks = $settingsService->getCategorySettings('webHooks');
        return !empty($webhooks);
    }
    private static function apisInUse(SettingsService $settingsService) : bool
    {
        $apiKeys = $settingsService->getSetting('apiKeys', 'apiKeys');
        return !empty($apiKeys);
    }
    private static function mailchimpConfigured(SettingsService $settingsService) : bool
    {
        return self::nonEmpty($settingsService->getSetting('mailchimp', 'accessToken')) && self::nonEmpty($settingsService->getSetting('mailchimp', 'list')) && self::nonEmpty($settingsService->getSetting('mailchimp', 'server'));
    }
    private static function recaptchaConfigured(SettingsService $settingsService) : bool
    {
        $recaptcha = $settingsService->getSetting('general', 'googleRecaptcha');
        if (!\is_array($recaptcha)) {
            return \false;
        }
        return !empty($recaptcha['enabled']) && self::nonEmpty($recaptcha['siteKey'] ?? null) && self::nonEmpty($recaptcha['secret'] ?? null);
    }
    private static function whatsappInUse(Container $container) : bool
    {
        return self::tableHasRecords($container->getDatabaseConnection(), NotificationsLogTable::getTableName(), "messageId IS NOT NULL AND messageId != ''");
    }
    private static function ivyInUse() : bool
    {
        if (!\class_exists(\AmeliaBooking\Infrastructure\WP\Integrations\PluginInstaller::class)) {
            return \false;
        }
        if (!\AmeliaBooking\Infrastructure\WP\Integrations\PluginInstaller::isPluginActive('ivyforms')) {
            return \false;
        }
        if (\class_exists(\AmeliaBooking\Infrastructure\WP\Integrations\IvyForms\IvyFormsService::class)) {
            $forms = \AmeliaBooking\Infrastructure\WP\Integrations\IvyForms\IvyFormsService::getForms();
            return \is_array($forms) && $forms !== [];
        }
        return \true;
    }
    private static function buddyBossInUse() : bool
    {
        if (\function_exists('Amelia_BB_Platform_is_active')) {
            return \Amelia_BB_Platform_is_active();
        }
        return \false;
    }
    private static function hasGroupBookingUsage(Container $container) : bool
    {
        try {
            return self::tableHasRecords($container->getDatabaseConnection(), ServicesTable::getTableName(), 'maxCapacity > 1');
        } catch (\Throwable $e) {
            self::logDebugException('Amelia group booking check failed', $e);
            return \false;
        }
    }
    private static function hasMultilingualSupportUsage(SettingsService $settingsService) : bool
    {
        try {
            $usedLanguages = $settingsService->getSetting('general', 'usedLanguages');
            return !empty($usedLanguages);
        } catch (\Throwable $e) {
            self::logDebugException('Amelia multilingual support check failed', $e);
            return \false;
        }
    }
    private static function hasMultipleLocationsUsage(Container $container) : bool
    {
        try {
            $table = LocationsTable::getTableName();
            return self::executeQuery($container->getDatabaseConnection(), "SELECT COUNT(*) >= 2 AS found FROM {$table}");
        } catch (\Throwable $e) {
            self::logDebugException('Amelia multiple locations check failed', $e);
            return \false;
        }
    }
    private static function hasSmtpUsage(SettingsService $settingsService) : bool
    {
        try {
            if ($settingsService->getSetting('notifications', 'mailService') !== 'smtp') {
                return \false;
            }
            return self::nonEmpty($settingsService->getSetting('notifications', 'smtpHost')) && self::nonEmpty($settingsService->getSetting('notifications', 'smtpPort')) && self::nonEmpty($settingsService->getSetting('notifications', 'smtpUsername')) && self::nonEmpty($settingsService->getSetting('notifications', 'smtpPassword'));
        } catch (\Throwable $e) {
            self::logDebugException('Amelia SMTP check failed', $e);
            return \false;
        }
    }
    /**
     * @param object $connection
     */
    private static function tableHasRecords($connection, string $table, string $where = '1=1') : bool
    {
        return self::executeQuery($connection, "SELECT EXISTS (SELECT 1 FROM {$table} WHERE {$where} LIMIT 1) AS found");
    }
    /**
     * @param object $connection
     * @param array<int|string, mixed> $params
     * @return array<string, mixed>|false
     */
    private static function fetchRow($connection, string $sql, array $params = [])
    {
        $statement = $connection->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();
        return \is_array($row) ? $row : \false;
    }
    /**
     * @param object $connection
     * @param array<int|string, mixed> $params
     * @return list<array<string, mixed>>
     */
    private static function fetchAllRows($connection, string $sql, array $params = []) : array
    {
        $statement = $connection->prepare($sql);
        $statement->execute($params);
        if (!\method_exists($statement, 'fetchAll')) {
            return [];
        }
        $rows = $statement->fetchAll();
        return \is_array($rows) ? $rows : [];
    }
    /**
     * @param object $connection
     * @param array<int|string, mixed> $params
     */
    private static function executeQuery($connection, string $sql, array $params = []) : bool
    {
        $row = self::fetchRow($connection, $sql, $params);
        return $row !== \false && !empty($row['found']);
    }
    /**
     * @param object $connection
     */
    private static function tableRecordCount($connection, string $table, string $where = '1=1') : int
    {
        try {
            $row = self::fetchRow($connection, "SELECT COUNT(*) AS cnt FROM {$table} WHERE {$where}");
            return $row !== \false ? (int) ($row['cnt'] ?? 0) : 0;
        } catch (\Throwable $e) {
            self::logDebugException('Amelia table record count failed', $e);
            return 0;
        }
    }
    private static function paymentGatewayTransactionCount(Container $container, string $gateway) : int
    {
        try {
            return self::tableRecordCount($container->getDatabaseConnection(), PaymentsTable::getTableName(), "gateway = '{$gateway}'");
        } catch (\Throwable $e) {
            self::logDebugException("Amelia payment gateway transaction count failed ({$gateway})", $e);
            return 0;
        }
    }
    private static function paymentGatewayHasCredentials(SettingsService $settingsService, string $code) : bool
    {
        $settings = $settingsService->getSetting('payments', $code);
        if (!\is_array($settings) || empty($settings['enabled'])) {
            return \false;
        }
        switch ($code) {
            case 'stripe':
                return self::hasStripeCredentials($settings);
            case 'payPal':
                return self::hasPayPalCredentials($settings);
            case 'mollie':
                return self::hasMollieCredentials($settings);
            case 'square':
                return self::hasSquareCredentials($settings);
            case 'razorpay':
                return self::hasRazorpayCredentials($settings);
            case 'barion':
                return self::hasBarionCredentials($settings);
            case 'wc':
                return \true;
            default:
                return \false;
        }
    }
    /**
     * @param array<string, mixed> $settings
     */
    private static function hasStripeCredentials(array $settings) : bool
    {
        if (!empty($settings['testMode'])) {
            return self::nonEmpty($settings['testPublishableKey'] ?? null) && self::nonEmpty($settings['testSecretKey'] ?? null);
        }
        return self::nonEmpty($settings['livePublishableKey'] ?? null) && self::nonEmpty($settings['liveSecretKey'] ?? null);
    }
    /**
     * @param array<string, mixed> $settings
     */
    private static function hasPayPalCredentials(array $settings) : bool
    {
        if (!empty($settings['sandboxMode'])) {
            return self::nonEmpty($settings['testApiClientId'] ?? null) && self::nonEmpty($settings['testApiSecret'] ?? null);
        }
        return self::nonEmpty($settings['liveApiClientId'] ?? null) && self::nonEmpty($settings['liveApiSecret'] ?? null);
    }
    /**
     * @param array<string, mixed> $settings
     */
    private static function hasMollieCredentials(array $settings) : bool
    {
        if (!empty($settings['testMode'])) {
            return self::nonEmpty($settings['testApiKey'] ?? null);
        }
        return self::nonEmpty($settings['liveApiKey'] ?? null);
    }
    /**
     * @param array<string, mixed> $settings
     */
    private static function hasSquareCredentials(array $settings) : bool
    {
        return self::nonEmpty($settings['accessToken'] ?? null) && self::nonEmpty($settings['locationId'] ?? null);
    }
    /**
     * @param array<string, mixed> $settings
     */
    private static function hasRazorpayCredentials(array $settings) : bool
    {
        if (!empty($settings['testMode'])) {
            return self::nonEmpty($settings['testKeyId'] ?? null) && self::nonEmpty($settings['testKeySecret'] ?? null);
        }
        return self::nonEmpty($settings['liveKeyId'] ?? null) && self::nonEmpty($settings['liveKeySecret'] ?? null);
    }
    /**
     * @param array<string, mixed> $settings
     */
    private static function hasBarionCredentials(array $settings) : bool
    {
        if (!empty($settings['sandboxMode'])) {
            return self::nonEmpty($settings['sandboxPOSKey'] ?? null) && self::nonEmpty($settings['payeeEmail'] ?? null);
        }
        return self::nonEmpty($settings['livePOSKey'] ?? null) && self::nonEmpty($settings['payeeEmail'] ?? null);
    }
    /**
     * @param mixed $value
     */
    private static function nonEmpty($value) : bool
    {
        if ($value === null || $value === \false) {
            return \false;
        }
        if (\is_string($value)) {
            return \trim($value) !== '';
        }
        return $value !== '';
    }
}
