<?php

namespace WDTIntegration\Webhooks;

defined('ABSPATH') or die('Access denied.');

/**
 * AJAX handlers for webhook CRUD / test in the table editor.
 */
class WebhooksAjaxController
{
    /** @var WebhookManagerService */
    private $manager;

    /** @var WebhookDispatcherService */
    private $dispatcher;

    public function __construct(
        WebhookManagerService $manager = null,
        WebhookDispatcherService $dispatcher = null
    ) {
        $this->manager = $manager ?: new WebhookManagerService();
        $this->dispatcher = $dispatcher ?: new WebhookDispatcherService();
    }

    /**
     * @return void
     */
    public function loadWebhooks()
    {
        $this->authorize();

        $tableId = isset($_POST['table_id']) ? absint($_POST['table_id']) : 0;
        if ($tableId < 1) {
            wp_send_json_error(array('message' => __('Invalid table ID.', 'wpdatatables')));
        }

        $listTable = new WebhooksListTable(
            array(
                'table_id' => $tableId,
                'manager' => $this->manager,
            )
        );

        wp_send_json_success(
            array(
                'html' => $listTable->getRowsHtml(),
            )
        );
    }

    /**
     * @return void
     */
    public function saveWebhook()
    {
        $this->authorize();
        $input = $this->collectInput();
        $result = $this->manager->create($input);

        if (empty($result['success'])) {
            wp_send_json_error($result);
        }

        wp_send_json_success($result);
    }

    /**
     * @return void
     */
    public function updateWebhook()
    {
        $this->authorize();

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        if ($id < 1) {
            wp_send_json_error(array('message' => __('Invalid webhook ID.', 'wpdatatables')));
        }

        $input = $this->collectInput();
        $result = $this->manager->update($id, $input);

        if (empty($result['success'])) {
            wp_send_json_error($result);
        }

        wp_send_json_success($result);
    }

    /**
     * @return void
     */
    public function deleteWebhook()
    {
        $this->authorize();

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $result = $this->manager->delete($id);

        if (empty($result['success'])) {
            wp_send_json_error($result);
        }

        wp_send_json_success($result);
    }

    /**
     * @return void
     */
    public function updateWebhookStatus()
    {
        $this->authorize();

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $enabled = !empty($_POST['enabled']);
        $result = $this->manager->updateStatus($id, $enabled);

        if (empty($result['success'])) {
            wp_send_json_error($result);
        }

        wp_send_json_success($result);
    }

    /**
     * @return void
     */
    public function getWebhook()
    {
        $this->authorize();

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $webhook = $this->manager->get($id, false);

        if (!$webhook) {
            wp_send_json_error(array('message' => __('Webhook not found.', 'wpdatatables')));
        }

        // Never return the raw secret — only whether one is set.
        $webhook['secret'] = '';
        wp_send_json_success($webhook);
    }

    /**
     * @return void
     */
    public function testWebhook()
    {
        $this->authorize();

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $webhook = $this->manager->getRepository()->findById($id);

        if (!$webhook) {
            wp_send_json_error(array('message' => __('Webhook not found.', 'wpdatatables')));
        }

        $result = $this->dispatcher->test($webhook);

        if (empty($result['success'])) {
            wp_send_json_error($result);
        }

        wp_send_json_success($result);
    }

    /**
     * @return void
     */
    private function authorize()
    {
        if (
            !isset($_POST['nonce'])
            || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wdt_webhooks_nonce')
        ) {
            wp_send_json_error(array('message' => __('Security check failed.', 'wpdatatables')), 403);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized.', 'wpdatatables')), 403);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function collectInput()
    {
        $headers = array();
        if (isset($_POST['headers'])) {
            if (is_string($_POST['headers'])) {
                $headers = wp_unslash($_POST['headers']);
            } elseif (is_array($_POST['headers'])) {
                $headers = wp_unslash($_POST['headers']);
            }
        }

        return array(
            'table_id' => isset($_POST['table_id']) ? absint($_POST['table_id']) : 0,
            'name' => isset($_POST['name']) ? $_POST['name'] : '',
            'url' => isset($_POST['url']) ? $_POST['url'] : '',
            'method' => isset($_POST['method']) ? $_POST['method'] : 'POST',
            'format' => isset($_POST['format']) ? $_POST['format'] : 'json',
            'event' => isset($_POST['event']) ? $_POST['event'] : '',
            'secret' => isset($_POST['secret']) ? $_POST['secret'] : '',
            'headers' => $headers,
            'enabled' => !empty($_POST['enabled']),
        );
    }
}
