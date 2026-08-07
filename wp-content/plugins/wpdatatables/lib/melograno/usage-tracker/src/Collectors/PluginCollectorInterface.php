<?php

declare (strict_types=1);
namespace WPDT\Melograno\UsageTracker\Collectors;

interface PluginCollectorInterface
{
    public function getPluginSlug() : string;
    public function getConsentOptionName() : string;
    public function getCronHookName() : string;
    public function getCronSchedule() : string;
    /**
     * @return array<string, mixed>
     */
    public function collect() : array;
}
