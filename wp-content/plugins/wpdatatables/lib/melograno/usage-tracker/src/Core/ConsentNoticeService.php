<?php

declare (strict_types=1);
namespace WPDT\Melograno\UsageTracker\Core;

use WPDT\Melograno\UsageTracker\Collectors\ConsentNoticeCollectorInterface;
/**
 * Decides consent/notice policy and owns the admin-notice option.
 *
 * This service never mutates consent state or touches cron scheduling; those
 * side effects belong to UsageTracker. It only answers questions ("should we
 * migrate?", "what is the default consent?", "should the notice show?") and
 * arms/dismisses the notice option it owns.
 */
class ConsentNoticeService
{
    private ConsentNoticeCollectorInterface $collector;
    private ConsentManager $consentManager;
    private NoticeManager $noticeManager;
    public function __construct(ConsentNoticeCollectorInterface $collector)
    {
        $this->collector = $collector;
        $this->consentManager = new ConsentManager($collector->getConsentOptionName());
        $this->noticeManager = new NoticeManager($collector->getNoticeOptionName());
    }
    public function shouldRunUpgradeMigration(string $savedVersion, string $currentVersion) : bool
    {
        if (!$this->collector->shouldMigrateConsentOnUpgrade()) {
            return \false;
        }
        $migrationVersion = $this->collector->getOptInMigrationVersion();
        if ($migrationVersion === null) {
            return \false;
        }
        return \version_compare($savedVersion, $migrationVersion, '<') && \version_compare($currentVersion, $migrationVersion, '>=');
    }
    public function defaultConsent() : bool
    {
        return $this->collector->shouldEnableConsentByDefault();
    }
    public function armNotice() : void
    {
        $this->noticeManager->arm();
    }
    /**
     * Arms the notice for a fresh install only when telemetry stays off by default
     * and the collector opts into showing the notice.
     */
    public function armNoticeForNewInstallation(bool $consentEnabled) : void
    {
        if (!$consentEnabled && $this->collector->shouldShowAdminNotice()) {
            $this->noticeManager->arm();
        }
    }
    public function canCurrentUserSeeAdminNotice() : bool
    {
        return \is_admin() && \current_user_can('manage_options');
    }
    public function shouldShowNotice() : bool
    {
        if (!$this->collector->shouldShowAdminNotice()) {
            return \false;
        }
        if (!$this->noticeManager->isArmed()) {
            return \false;
        }
        return !$this->consentManager->isEnabled();
    }
    public function dismissNotice() : void
    {
        $this->noticeManager->dismiss();
    }
    public function dismissNoticeIfArmed() : void
    {
        if ($this->noticeManager->isArmed()) {
            $this->noticeManager->dismiss();
        }
    }
}
