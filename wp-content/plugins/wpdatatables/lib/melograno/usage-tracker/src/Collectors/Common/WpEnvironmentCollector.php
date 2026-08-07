<?php

declare (strict_types=1);
namespace WPDT\Melograno\UsageTracker\Collectors\Common;

class WpEnvironmentCollector
{
    /**
     * @return array<string, mixed>
     */
    public function collect() : array
    {
        global $wp_version;
        return \array_filter(['wp_version' => $wp_version ?? null, 'php_version' => \PHP_VERSION, 'locale' => \function_exists('get_locale') ? \get_locale() : null, 'is_multisite' => \function_exists('is_multisite') ? \is_multisite() : null, 'timezone' => \function_exists('wp_timezone_string') ? \wp_timezone_string() : null], static function ($value) {
            return $value !== null;
        });
    }
}
