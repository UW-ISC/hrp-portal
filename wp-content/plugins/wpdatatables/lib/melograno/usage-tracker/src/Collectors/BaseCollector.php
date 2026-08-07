<?php

declare (strict_types=1);
namespace WPDT\Melograno\UsageTracker\Collectors;

use WPDT\Melograno\UsageTracker\Collectors\Common\ActivationCollector;
use WPDT\Melograno\UsageTracker\Collectors\Common\WpEnvironmentCollector;
abstract class BaseCollector implements PluginCollectorInterface
{
    /** @var WpEnvironmentCollector */
    private $environmentCollector;
    /** @var ActivationCollector */
    private $activationCollector;
    public function __construct(?WpEnvironmentCollector $environmentCollector = null, ?ActivationCollector $activationCollector = null)
    {
        $this->environmentCollector = $environmentCollector ?? new WpEnvironmentCollector();
        $this->activationCollector = $activationCollector ?? new ActivationCollector();
    }
    public function getCronHookName() : string
    {
        return self::cronHookNameForSlug($this->getPluginSlug());
    }
    public function getCronSchedule() : string
    {
        return 'weekly';
    }
    public static function cronHookNameForSlug(string $pluginSlug) : string
    {
        return 'melograno_usage_tracker_' . $pluginSlug . '_send';
    }
    public function collect() : array
    {
        return ['schema_version' => 1, 'sent_at' => \gmdate('c'), 'plugin' => $this->getPluginSlug(), 'common' => $this->commonPayload(), 'plugin_data' => $this->pluginPayload()];
    }
    /**
     * @return array<string, mixed>
     */
    protected function commonPayload() : array
    {
        return ['environment' => $this->environmentCollector->collect(), 'activation' => $this->activationCollector->collect()];
    }
    /**
     * @return array<string, mixed>
     */
    protected abstract function pluginPayload() : array;
}
