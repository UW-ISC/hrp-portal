<?php
/**
 * @package wpDataTables
 * @version 8.0.2
 */
/*
Plugin Name: wpDataTables
Plugin URI: https://wpdatatables.com/
Description: Add interactive tables easily from any input source
//[<-- Full version -->]//
Version: 8.0.2
//[<--/ Full version -->]//
//[<-- Full version insertion #27 -->]//
Author: Melograno Ventures
Author URI: https://www.melograno.io/
Text Domain: wpdatatables
Domain Path: /languages
*/
?>
<?php


defined('ABSPATH') or die('Access denied');

/******************************
 * Includes and configuration *
 ******************************/

define('WDT_ROOT_PATH', plugin_dir_path(__FILE__)); // full path to the wpDataTables root directory
define('WDT_ROOT_URL', plugin_dir_url(__FILE__)); // URL of wpDataTables plugin
if (!defined('WDT_BASENAME')) {
    define('WDT_BASENAME', plugin_basename(__FILE__)); // Base name for wpDataTables plugin
}
if (!defined('WDTMCP_VERSION')) {
    define('WDTMCP_VERSION', '1.0.0');
}
if (!defined('WDTMCP_MIN_WP_VERSION')) {
    define('WDTMCP_MIN_WP_VERSION', '6.9');
}

// Config file
require_once(WDT_ROOT_PATH . 'config/config.inc.php');

// Check PHP version
if (version_compare(WDT_PHP_SERVER_VERSION, WDT_REQUIRED_PHP_VERSION, '<')) {

    if (!function_exists('is_plugin_active')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }

    if (is_plugin_active(WDT_BASENAME)) {
        deactivate_plugins(WDT_BASENAME);
    }
    add_action('admin_notices',
            function () {
                $message = sprintf(
                        esc_attr__('Our plugin requires %1$s PHP Version or higher. Your Version: %2$s. See %3$s for details.', 'wpdatatables'),
                        WDT_REQUIRED_PHP_VERSION,
                        WDT_PHP_SERVER_VERSION,
                        '<a href="https://wordpress.org/about/requirements/" target="_blank">' . esc_html__('WordPress Requirements', 'wpdatatables') . '</a>'
                );
                ?>
                <div class="notice notice-error">
                    <p>
                        <strong> <?php esc_html_e('Warning:', 'wpdatatables') ?></strong>
                        <?php
                        esc_html_e('Your site is running an insecure version of PHP that is no longer supported.', 'wpdatatables')
                        ?>
                        <br><br>
                        <?php
                        echo $message;
                        ?>
                        <br><br><strong> <?php esc_html_e('Note:', 'wpdatatables') ?></strong>
                        <?php
                        esc_html_e('The wpDataTables plugin is disabled on your site until you fix the issue.', 'wpdatatables')
                        ?>
                    </p>
                </div>
                <?php
            });
    return;
}

// Load dependencies
require_once WDT_ROOT_PATH . 'lib/autoload.php';

use WPDT\Melograno\UsageTracker\Collectors\Plugin\WpDataTablesCollector;
use WPDT\Melograno\UsageTracker\Core\UsageTracker;

if (!defined('MELOGRANO_BI_GATE_URL')) {
    define('MELOGRANO_BI_GATE_URL', 'https://bi.melograno.io');
}

// Load the layered backend: scoped PHP-DI (WPDataTables\Vendor\) first, then
// the PSR-4 WPDataTables\ source.
require_once WDT_ROOT_PATH . 'backend/scope-vendor/autoload.php';
require_once WDT_ROOT_PATH . 'backend/vendor/autoload.php';

// Boot the layered plugin: builds the DI container, registers hook owners, and
// loads backward-compatibility global functions (Phase K).
try {
    WPDataTables\Plugin\Plugin::getInstance();
    WPDataTables\Plugin\Plugin::bootIntegrations();
} catch (Exception $e) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('wpDataTables backend boot failed: ' . $e->getMessage());
    }
}


//[<-- Full version -->]//
// Globals for the shortcode variables
$wdtVar1 = '';
$wdtVar2 = '';
$wdtVar3 = '';
$wdtVar4 = '';
$wdtVar5 = '';
$wdtVar6 = '';
$wdtVar7 = '';
$wdtVar8 = '';
$wdtVar9 = '';

//[<--/ Full version -->]//

/********
 * Hooks *
 ********/
register_activation_hook(__FILE__, 'wdtActivation');
register_deactivation_hook(__FILE__, 'wdtDeactivation');
register_uninstall_hook(__FILE__, 'wdtUninstall');

/**
 * Show an admin notice when MCP integration cannot be loaded.
 *
 * @param string $message Human-readable reason (already translated).
 */
function wdtmcp_register_unavailable_notice($message)
{
    if (!is_admin()) {
        return;
    }

    add_action('admin_notices', static function () use ($message) {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="notice notice-warning">
            <p>
                <strong><?php esc_html_e('wpDataTables MCP:', 'wpdatatables'); ?></strong>
                <?php echo esc_html($message); ?>
            </p>
        </div>
        <?php
    });
}

/**
 * Load and register the wpDataTables MCP Adapter integration.
 */
function wdtmcp_load_integration()
{
    global $wp_version;

    require_once WDT_ROOT_PATH . 'backend/src/Infrastructure/WP/MCP/WdtmcpFeatureGate.php';

    // Opt-out via Settings → Main settings or filter `wpdatatables/mcp/enabled`.
    if (!\WDTMCP\Infrastructure\WP\MCP\WdtmcpFeatureGate::isEnabled()) {
        return;
    }

    if (version_compare($wp_version, WDTMCP_MIN_WP_VERSION, '<')) {
        wdtmcp_register_unavailable_notice(
            sprintf(
                /* translators: 1: required WordPress version, 2: current WordPress version */
                __('MCP integration requires WordPress %1$s or newer. Your site is running WordPress %2$s.', 'wpdatatables'),
                WDTMCP_MIN_WP_VERSION,
                $wp_version
            )
        );

        return;
    }

    if (!function_exists('wp_register_ability') || !class_exists(\WP\MCP\Core\McpAdapter::class)) {
        $missing = array();

        if (!function_exists('wp_register_ability')) {
            $missing[] = __('WordPress Abilities API', 'wpdatatables');
        }

        if (!class_exists(\WP\MCP\Core\McpAdapter::class)) {
            $missing[] = __('MCP Adapter', 'wpdatatables');
        }

        wdtmcp_register_unavailable_notice(
            sprintf(
                /* translators: %s: comma-separated list of missing components */
                __('MCP integration is unavailable because required components are missing: %s. Try reinstalling or updating wpDataTables.', 'wpdatatables'),
                implode(', ', $missing)
            )
        );

        return;
    }

    require_once WDT_ROOT_PATH . 'backend/src/Infrastructure/WP/MCP/Helpers/McpHelpers/WdtmcpAbilitiesHelper.php';
    require_once WDT_ROOT_PATH . 'backend/src/Infrastructure/WP/MCP/WdtmcpAbilityCatalog.php';
    require_once WDT_ROOT_PATH . 'backend/src/Infrastructure/WP/MCP/Helpers/McpHelpers/WdtmcpPermissionsHelper.php';
    require_once WDT_ROOT_PATH . 'backend/src/Infrastructure/WP/MCP/Helpers/McpHelpers/WdtmcpLicenseGuideHelper.php';
    require_once WDT_ROOT_PATH . 'backend/src/Infrastructure/WP/MCP/Prompts/FileUploadWorkflowPrompt.php';
    require_once WDT_ROOT_PATH . 'backend/src/Infrastructure/WP/MCP/WdtmcpAbilitiesRegistrar.php';
    require_once WDT_ROOT_PATH . 'backend/src/Infrastructure/WP/MCP/WdtmcpMcpHttpTransport.php';
    require_once WDT_ROOT_PATH . 'backend/src/Infrastructure/WP/MCP/WdtmcpMcpServerRegistrar.php';
    require_once WDT_ROOT_PATH . 'backend/src/Infrastructure/WP/MCP/WdtmcpSessionMetaLock.php';
    require_once WDT_ROOT_PATH . 'backend/src/Infrastructure/WP/MCP/WdtmcpAngieRegistrar.php';

    \WDTMCP\Infrastructure\WP\MCP\WdtmcpSessionMetaLock::init();
    \WP\MCP\Core\McpAdapter::instance();

    add_action(
        'mcp_adapter_init',
        array('\WDTMCP\Infrastructure\WP\MCP\WdtmcpMcpServerRegistrar', 'init')
    );
    add_action(
        'wp_abilities_api_categories_init',
        array('\WDTMCP\Infrastructure\WP\MCP\WdtmcpAbilitiesRegistrar', 'registerCategories')
    );
    add_action(
        'wp_abilities_api_init',
        array('\WDTMCP\Infrastructure\WP\MCP\WdtmcpAbilitiesRegistrar', 'registerAbilities')
    );
    \WDTMCP\Infrastructure\WP\MCP\WdtmcpAngieRegistrar::init();
    add_action('init', 'wdtmcp_maybe_migrate_css_to_global', 99);
}

wdtmcp_load_integration();

?>
