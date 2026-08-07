<?php

declare (strict_types=1);
namespace WPDT\Melograno\UsageTracker\Collectors\Common;

class ActivationCollector
{
    /**
     * @return array<string, mixed>
     */
    public function collect() : array
    {
        $data = ['active_plugin_count' => null, 'theme' => null];
        if (!\function_exists('get_plugins') || !\function_exists('is_plugin_active')) {
            require_once \ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (\function_exists('get_plugins') && \function_exists('is_plugin_active')) {
            $plugins = \get_plugins();
            $active = 0;
            foreach (\array_keys($plugins) as $plugin) {
                if (\is_plugin_active($plugin)) {
                    $active++;
                }
            }
            $data['active_plugin_count'] = $active;
        }
        if (\function_exists('wp_get_theme')) {
            $theme = \wp_get_theme();
            $data['theme'] = $theme->get('Name');
        }
        return \array_filter($data, static function ($value) {
            return $value !== null;
        });
    }
}
