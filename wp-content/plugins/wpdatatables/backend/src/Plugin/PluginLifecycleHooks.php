<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin;

use WPDT\Melograno\UsageTracker\Collectors\Plugin\WpDataTablesCollector;
use WPDT\Melograno\UsageTracker\Core\UsageTracker;
use WPDataTables\Services\Admin\AdminNoticeService;
use WPDataTables\Services\InstallActions\ActivationHook;

/**
 * Plugin lifecycle hooks: textdomain, multisite, version upgrade, plugin links.
 *
 * @package WPDataTables\Plugin
 */
class PluginLifecycleHooks
{
    /** @var AdminNoticeService */
    private $adminNoticeService;

    public function __construct(AdminNoticeService $adminNoticeService)
    {
        $this->adminNoticeService = $adminNoticeService;
    }

    /**
     * @return void
     */
    public function register(): void
    {
        add_action('plugins_loaded', array($this, 'loadTextdomain'));
        add_action('plugins_loaded', array($this, 'initUsageTracker'));
        add_action('plugins_loaded', array($this, 'maybeEnableMultipleConnections'), 1);
        add_action('plugins_loaded', array($this, 'maybeRunVersionUpgrade'), 5);
        add_action('wpmu_new_blog', array($this, 'onCreateSiteOnMultisiteNetwork'));
        add_filter('wpmu_drop_tables', array($this, 'onDeleteSiteOnMultisiteNetwork'));
        add_action('activated_plugin', array($this, 'welcomePageActivationRedirect'));
        add_filter('plugin_action_links_' . WDT_BASENAME, array($this, 'addPluginActionLinks'));
        add_filter('plugin_row_meta', array($this, 'addPluginRowMeta'), 10, 3);
        add_action('admin_notices', array($this->adminNoticeService, 'renderAdminNotices'));
        add_action('admin_enqueue_scripts', array($this->adminNoticeService, 'enqueueIvyFormsPromoAssets'));

        global $wp_version;
        if ($wp_version < 4.4) {
            add_filter('query', array($this, 'supportNulls'));
        }
    }

    /**
     * @return void
     */
    public function loadTextdomain(): void
    {
        load_plugin_textdomain('wpdatatables', false, dirname(plugin_basename(WDT_ROOT_PATH . 'wpdatatables.php')) . '/languages/' . get_locale() . '/');
    }

    /**
     * @return void
     */
    public function initUsageTracker(): void
    {
        UsageTracker::init(new WpDataTablesCollector(), WDT_ROOT_PATH . 'wpdatatables.php');
    }

    /**
     * @return void
     */
    public function maybeEnableMultipleConnections(): void
    {
        if (!is_admin()) {
            return;
        }

        if (get_option('wdtSeparateCon') === false) {
            $this->enableMultipleConnections();
        }
    }

    /**
     * @return void
     */
    public function maybeRunVersionUpgrade(): void
    {
        if (!is_admin()) {
            return;
        }

        if (WDT_CURRENT_VERSION !== get_option('wdtVersion')) {
            if (!function_exists('is_plugin_active_for_network')) {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            ActivationHook::activate(is_plugin_active_for_network(WDT_BASENAME));
            update_option('wdtVersion', WDT_CURRENT_VERSION);
        }
    }

    /**
     * @return void
     */
    public function enableMultipleConnections(): void
    {
        update_option('wdtSeparateCon', json_encode(array(
            array(
                'id' => 'abcdefghijk',
                'host' => get_option('wdtMySqlHost') ?: '',
                'database' => get_option('wdtMySqlDB') ?: '',
                'user' => get_option('wdtMySqlUser') ?: '',
                'password' => get_option('wdtMySqlPwd') ?: '',
                'port' => get_option('wdtMySqlPort') ?: '',
                'vendor' => 'mysql',
                'driver' => 'dblib',
                'name' => 'MYSQL',
                'default' => get_option('wdtUseSeparateCon') ?: '',
            ),
        )));

        delete_option('wdtMySqlHost');
        delete_option('wdtMySqlDB');
        delete_option('wdtMySqlUser');
        delete_option('wdtMySqlPwd');
        delete_option('wdtMySqlPort');
    }

    /**
     * @param int $blogId New blog ID.
     * @return void
     */
    public function onCreateSiteOnMultisiteNetwork($blogId): void
    {
        global $wpdb;
        if (is_plugin_active_for_network('wpdatatables/wpdatatables.php')) {
            switch_to_blog($blogId);
            ActivationHook::createTables();
            $tableName = $wpdb->prefix . 'wpdatatables_templates';
            $rowCount = $wpdb->get_var("SELECT COUNT(*) FROM {$tableName}");
            ActivationHook::checkSimpleTemplatesActivation($rowCount, $tableName);
            restore_current_blog();
        }
    }

    /**
     * @param array $tables Tables WordPress will drop.
     * @return array
     */
    public function onDeleteSiteOnMultisiteNetwork($tables): array
    {
        global $wpdb;
        $tables[] = $wpdb->prefix . 'wpdatatables';
        $tables[] = $wpdb->prefix . 'wpdatatables_columns';
        $tables[] = $wpdb->prefix . 'wpdatacharts';
        $tables[] = $wpdb->prefix . 'wpdatatables_cache';
        $tables[] = $wpdb->prefix . 'wpdatatables_folders';
        $tables[] = $wpdb->prefix . 'wpdatatables_folders_meta';
        $tables[] = $wpdb->prefix . 'wpdatatables_rows';
        $tables[] = $wpdb->prefix . 'wpdatatables_templates';

        return $tables;
    }

    /**
     * @param string $query SQL query.
     * @return string
     */
    public function supportNulls($query): string
    {
        $query = str_ireplace("'NULL'", 'NULL', $query);
        $query = str_replace('null_str', 'null', $query);

        return $query;
    }

    /**
     * @param string $plugin Activated plugin basename.
     * @return void
     */
    public function welcomePageActivationRedirect($plugin): void
    {
        if ($plugin == plugin_basename(WDT_BASENAME) && (isset($_GET['action']) && $_GET['action'] == 'activate')) {
            exit(wp_redirect(admin_url('admin.php?page=wpdatatables-welcome-page')));
        }
    }

    /**
     * @param array $links Plugin action links.
     * @return array
     */
    public function addPluginActionLinks($links): array
    {
        $action_links['settings'] = '<a href="' . admin_url('admin.php?page=wpdatatables-settings') . '" aria-label="' . esc_attr__('Go to Settings', 'wpdatatables') . '">' . esc_html__('Settings', 'wpdatatables') . '</a>';
        $action_links['addons'] = '<a href="' . esc_url('https://wpdatatables.com/addons/') . '" aria-label="' . esc_attr__('Add-ons', 'wpdatatables') . '" style="color: #ff8c00;" target="_blank">' . esc_html__('Add-ons', 'wpdatatables') . '</a>';
        $action_links['docs'] = '<a href="' . esc_url('https://wpdatatables.com/documentation/general/features-overview/') . '" aria-label="' . esc_attr__('Docs', 'wpdatatables') . '" target="_blank">' . esc_html__('Docs', 'wpdatatables') . '</a>';

        return array_merge($action_links, $links);
    }

    /**
     * @param array  $links       Plugin row meta links.
     * @param string $file        Plugin basename.
     * @param array  $plugin_data Plugin header data.
     * @return array
     */
    public function addPluginRowMeta($links, $file, $plugin_data): array
    {
        if (WDT_BASENAME === $file) {
            if (is_network_admin()) {
                return $links;
            }

            if (isset($links[1])) {
                $author_uri = sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    $plugin_data['AuthorURI'],
                    $plugin_data['Author']
                );
                $links[1] = sprintf(__('By %s'), $author_uri);
            }

            $row_meta['docs'] = '<a href="' . esc_url('https://wpdatatables.com/documentation/general/features-overview/') . '" aria-label="' . esc_attr__('Docs', 'wpdatatables') . '" target="_blank">' . esc_html__('Docs', 'wpdatatables') . '</a>';
            $row_meta['support'] = '<a href="' . admin_url('admin.php?page=wpdatatables-support') . '" aria-label="' . esc_attr__('Support Center', 'wpdatatables') . '" target="_blank">' . esc_html__('Support Center', 'wpdatatables') . '</a>';

            return array_merge($links, $row_meta);
        }

        return $links;
    }
}
