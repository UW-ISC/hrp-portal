<?php

/**
 * Enqueues the Angie AI browser proxy for wpDataTables MCP.
 *
 * @package wpDataTables
 */

namespace WDTMCP\Infrastructure\WP\MCP;

defined('ABSPATH') or die('Access denied.');

/**
 * Loads the built ES module that registers wpDataTables MCP tools with Angie AI
 * when the Angie plugin is active.
 */
class WdtmcpAngieRegistrar
{
    /**
     * Hook the proxy module enqueue and the Angie plugin declaration.
     *
     * @return void
     */
    public static function init(): void
    {
        add_action('admin_enqueue_scripts', array(self::class, 'enqueue'));
        add_filter('angie_mcp_plugins', array(self::class, 'registerAngiePlugin'));
    }

    /**
     * Announce wpDataTables in `window.angieConfig.plugins`.
     *
     * This map is informational for us: Angie reads it only to decide whether to register
     * its own bundled servers (ACF, Elementor, WooCommerce, Gutenberg). Servers registered
     * through the SDK — like ours — are advertised regardless of this entry.
     *
     * @param mixed $plugins Plugin map collected by Angie, keyed by plugin slug.
     * @return mixed
     */
    public static function registerAngiePlugin($plugins)
    {
        if (!is_array($plugins) || !self::isAvailable()) {
            return $plugins;
        }

        $plugins['wpdatatables'] = array(
            'mcpServerName' => 'wpDataTables',
            'version' => defined('WDT_CURRENT_VERSION') ? WDT_CURRENT_VERSION : '',
        );

        return $plugins;
    }

    /**
     * Whether the Angie integration may run for the current request and user.
     *
     * @return bool
     */
    private static function isAvailable(): bool
    {
        if (!is_admin()) {
            return false;
        }

        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if (!is_plugin_active('angie/angie.php')) {
            return false;
        }

        if (class_exists(WdtmcpFeatureGate::class) && !WdtmcpFeatureGate::isEnabled()) {
            return false;
        }

        if (!function_exists('wdtmcp_current_user_can_use_mcp') || !wdtmcp_current_user_can_use_mcp()) {
            return false;
        }

        return version_compare(get_bloginfo('version'), '6.5', '>=');
    }

    /**
     * Enqueue the Angie MCP proxy script module on admin screens.
     *
     * @return void
     */
    public static function enqueue(): void
    {
        if (!self::isAvailable()) {
            return;
        }

        if (!function_exists('wp_enqueue_script_module')) {
            return;
        }

        $relative_path = 'assets/js/angie/wpdatatables-angie.js';
        $absolute_path = WDT_ROOT_PATH . $relative_path;

        if (!file_exists($absolute_path)) {
            return;
        }

        // Script modules cannot use wp_localize_script(); ensure core wpApiSettings
        // (root + nonce) exists on every admin page where Angie loads this module.
        wp_enqueue_script('wp-api-request');

        wp_enqueue_script_module(
            'wpdatatables-angie-mcp',
            WDT_ROOT_URL . $relative_path,
            array(),
            (string) filemtime($absolute_path)
        );
    }
}
