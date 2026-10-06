<?php

namespace WDTIntegration;

use WDTIntegration\Webhooks\WebhooksTable;
use WDTIntegration\Webhooks\WebhookManagerService;
use WDTIntegration\Webhooks\WebhookDispatcherService;
use WDTIntegration\Webhooks\WebhookPayloadBuilderService;
use WDTIntegration\Webhooks\WebhookRepository;
use WDTIntegration\Webhooks\WebhooksAjaxController;
use WDTIntegration\Webhooks\WebhookDispatchHooks;

defined('ABSPATH') or die('Access denied.');

define('WDT_WEBHOOKS_ROOT_URL', WDT_PRO_INTEGRATIONS_URL . 'webhooks/');
define('WDT_WEBHOOKS_ROOT_PATH', WDT_PRO_INTEGRATIONS_PATH . 'webhooks/');
define('WDT_WEBHOOKS_ASSETS_URL', WDT_WEBHOOKS_ROOT_URL . 'assets/');
define('WDT_WEBHOOKS_INTEGRATION', true);

require_once WDT_WEBHOOKS_ROOT_PATH . 'DB/WebhooksTable.php';
require_once WDT_WEBHOOKS_ROOT_PATH . 'Repository/WebhookRepository.php';
require_once WDT_WEBHOOKS_ROOT_PATH . 'Services/WebhookManagerService.php';
require_once WDT_WEBHOOKS_ROOT_PATH . 'Services/WebhookPayloadBuilderService.php';
require_once WDT_WEBHOOKS_ROOT_PATH . 'Services/WebhookDispatcherService.php';
require_once WDT_WEBHOOKS_ROOT_PATH . 'Controllers/WebhooksAjaxController.php';
require_once WDT_WEBHOOKS_ROOT_PATH . 'ListTables/WebhooksListTable.php';
require_once WDT_WEBHOOKS_ROOT_PATH . 'Hooks/WebhookDispatchHooks.php';

/**
 * Pro-tier outbound Webhooks integration bootstrap.
 */
class WebhooksIntegration
{
    /** @var WebhooksAjaxController|null */
    private static $ajaxController = null;

    /** @var WebhookManagerService|null */
    private static $manager = null;

    /** @var WebhookDispatcherService|null */
    private static $dispatcher = null;

    /**
     * @return void
     */
    public static function init()
    {
        add_action('wpdatatables_after_activation_method', array(__CLASS__, 'createDBTable'));
        add_action('wpdatatables_after_uninstall_method', array(__CLASS__, 'deleteDBTable'));
        add_action('init', array(__CLASS__, 'maybeCreateTable'), 5);

        add_action('wpdatatables_add_table_configuration_tab', array(__CLASS__, 'addTab'));
        add_action('wpdatatables_add_table_configuration_tabpanel', array(__CLASS__, 'addTabPanel'));
        add_action('wpdatatables_enqueue_on_edit_page', array(__CLASS__, 'enqueueAssets'));

        add_action('wpdatatables_after_delete_tables', array(__CLASS__, 'onTableDeleted'), 10, 2);

        add_action('wp_ajax_wpdatatables_load_webhooks', array(__CLASS__, 'ajaxLoadWebhooks'));
        add_action('wp_ajax_wpdatatables_save_webhook', array(__CLASS__, 'ajaxSaveWebhook'));
        add_action('wp_ajax_wpdatatables_update_webhook', array(__CLASS__, 'ajaxUpdateWebhook'));
        add_action('wp_ajax_wpdatatables_delete_webhook', array(__CLASS__, 'ajaxDeleteWebhook'));
        add_action('wp_ajax_wpdatatables_update_webhook_status', array(__CLASS__, 'ajaxUpdateWebhookStatus'));
        add_action('wp_ajax_wpdatatables_get_webhook', array(__CLASS__, 'ajaxGetWebhook'));
        add_action('wp_ajax_wpdatatables_test_webhook', array(__CLASS__, 'ajaxTestWebhook'));

        add_action(WebhookDispatcherService::CRON_HOOK, array(__CLASS__, 'handleCronDispatch'), 10, 1);

        $hooks = new WebhookDispatchHooks(self::dispatcher());
        $hooks->register();
    }

    /**
     * @return WebhookManagerService
     */
    private static function manager()
    {
        if (!self::$manager) {
            self::$manager = new WebhookManagerService(new WebhookRepository());
        }
        return self::$manager;
    }

    /**
     * @return WebhookDispatcherService
     */
    private static function dispatcher()
    {
        if (!self::$dispatcher) {
            self::$dispatcher = new WebhookDispatcherService(
                new WebhookRepository(),
                new WebhookPayloadBuilderService()
            );
        }
        return self::$dispatcher;
    }

    /**
     * @return WebhooksAjaxController
     */
    private static function ajax()
    {
        if (!self::$ajaxController) {
            self::$ajaxController = new WebhooksAjaxController(self::manager(), self::dispatcher());
        }
        return self::$ajaxController;
    }

    /**
     * @return void
     */
    public static function createDBTable()
    {
        WebhooksTable::init();
    }

    /**
     * @return void
     */
    public static function deleteDBTable()
    {
        WebhooksTable::delete();
    }

    /**
     * @return void
     */
    public static function maybeCreateTable()
    {
        WebhooksTable::maybeInit();
    }

    /**
     * @return void
     */
    public static function addTab()
    {
        include WDT_WEBHOOKS_ROOT_PATH . 'templates/webhooks_tab.inc.php';
    }

    /**
     * @return void
     */
    public static function addTabPanel()
    {
        include WDT_WEBHOOKS_ROOT_PATH . 'templates/webhooks_tab_panel.inc.php';
    }

    /**
     * @return void
     */
    public static function enqueueAssets()
    {
        $tableId = isset($_GET['table_id']) ? absint($_GET['table_id']) : 0;

        wp_enqueue_style(
            'wdt-webhooks-css',
            WDT_WEBHOOKS_ASSETS_URL . 'css/webhooks.css',
            array(),
            WDT_CURRENT_VERSION
        );

        wp_enqueue_script(
            'wdt-webhooks-js',
            WDT_WEBHOOKS_ASSETS_URL . 'js/webhooks-admin.js',
            array('jquery', 'wdt-common'),
            WDT_CURRENT_VERSION,
            true
        );

        wp_localize_script(
            'wdt-webhooks-js',
            'wdtWebhooks',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wdt_webhooks_nonce'),
                'table_id' => $tableId,
                'i18n' => array(
                    'addTitle' => __('Add webhook', 'wpdatatables'),
                    'editTitle' => __('Edit webhook', 'wpdatatables'),
                    'testSuccess' => __('Webhook test', 'wpdatatables'),
                    'testFail' => __('Webhook test failed', 'wpdatatables'),
                ),
            )
        );
    }

    /**
     * Cascade-delete webhooks when a table is removed.
     *
     * @param int|string $id
     * @param string $type
     * @return void
     */
    public static function onTableDeleted($id, $type = 'table')
    {
        if ($type !== 'table') {
            return;
        }
        self::manager()->deleteForTable((int) $id);
    }

    /**
     * @param string $token
     * @return void
     */
    public static function handleCronDispatch($token)
    {
        self::dispatcher()->handleAsyncDispatch($token);
    }

    /** @return void */
    public static function ajaxLoadWebhooks()
    {
        self::ajax()->loadWebhooks();
    }

    /** @return void */
    public static function ajaxSaveWebhook()
    {
        self::ajax()->saveWebhook();
    }

    /** @return void */
    public static function ajaxUpdateWebhook()
    {
        self::ajax()->updateWebhook();
    }

    /** @return void */
    public static function ajaxDeleteWebhook()
    {
        self::ajax()->deleteWebhook();
    }

    /** @return void */
    public static function ajaxUpdateWebhookStatus()
    {
        self::ajax()->updateWebhookStatus();
    }

    /** @return void */
    public static function ajaxGetWebhook()
    {
        self::ajax()->getWebhook();
    }

    /** @return void */
    public static function ajaxTestWebhook()
    {
        self::ajax()->testWebhook();
    }
}

WebhooksIntegration::init();
