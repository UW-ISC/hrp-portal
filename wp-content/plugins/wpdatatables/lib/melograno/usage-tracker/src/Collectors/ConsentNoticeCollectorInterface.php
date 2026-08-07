<?php

declare (strict_types=1);
namespace WPDT\Melograno\UsageTracker\Collectors;

interface ConsentNoticeCollectorInterface extends PluginCollectorInterface
{
    public function getNoticeOptionName() : string;
    /**
     * Plugin version that introduces opt-in consent; null skips upgrade migration.
     */
    public function getOptInMigrationVersion() : ?string;
    public function shouldEnableConsentByDefault() : bool;
    public function shouldShowAdminNotice() : bool;
    public function shouldMigrateConsentOnUpgrade() : bool;
    /**
     * Prefix for wp_ajax actions: {prefix}_enable_usage_tracking, {prefix}_dismiss_usage_tracking_notice.
     * Nonce action for those handlers: {prefix}_usage_tracking_consent.
     */
    public function getConsentNoticeAjaxPrefix() : string;
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
    public function getConsentNoticePresentation() : array;
    public function renderConsentAdminNotice() : void;
}
