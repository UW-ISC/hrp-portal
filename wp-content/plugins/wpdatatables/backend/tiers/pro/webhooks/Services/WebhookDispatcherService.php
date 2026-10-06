<?php

namespace WDTIntegration\Webhooks;

defined('ABSPATH') or die('Access denied.');

/**
 * Schedules and delivers outbound webhook HTTP requests.
 */
class WebhookDispatcherService
{
    const CRON_HOOK = 'wpdatatables/webhooks/dispatch';
    const TRANSIENT_PREFIX = 'wdt_webhook_dispatch_';

    /** @var WebhookRepository */
    private $repository;

    /** @var WebhookPayloadBuilderService */
    private $payloadBuilder;

    /** @var int */
    private $maxRetries = 2;

    /** @var int */
    private $timeoutSeconds = 30;

    /** @var int */
    private $testTimeoutSeconds = 10;

    public function __construct(
        WebhookRepository $repository = null,
        WebhookPayloadBuilderService $payloadBuilder = null
    ) {
        $this->repository = $repository ?: new WebhookRepository();
        $this->payloadBuilder = $payloadBuilder ?: new WebhookPayloadBuilderService();
    }

    /**
     * Queue async dispatch for an event on a table.
     *
     * @param string $event
     * @param int $tableId
     * @param array<string, mixed> $context
     * @return void
     */
    public function schedule($event, $tableId, array $context = array())
    {
        $webhooks = $this->repository->findEnabledByTableIdAndEvent((int) $tableId, $event);
        if (empty($webhooks)) {
            return;
        }

        $payload = $this->payloadBuilder->build($event, (int) $tableId, $context);
        $token = wp_generate_password(20, false, false);
        $transientKey = self::TRANSIENT_PREFIX . $token;

        set_transient(
            $transientKey,
            array(
                'table_id' => (int) $tableId,
                'event' => $event,
                'payload' => $payload,
                'webhook_ids' => array_map(
                    static function ($webhook) {
                        return (int) $webhook['id'];
                    },
                    $webhooks
                ),
            ),
            15 * MINUTE_IN_SECONDS
        );

        if (!wp_next_scheduled(self::CRON_HOOK, array($token))) {
            wp_schedule_single_event(time(), self::CRON_HOOK, array($token));
        }

        spawn_cron();
    }

    /**
     * Cron callback: deliver queued webhooks.
     *
     * @param string $token
     * @return void
     */
    public function handleAsyncDispatch($token)
    {
        $transientKey = self::TRANSIENT_PREFIX . sanitize_key($token);
        $job = get_transient($transientKey);
        delete_transient($transientKey);

        if (!is_array($job) || empty($job['webhook_ids']) || empty($job['payload'])) {
            return;
        }

        foreach ($job['webhook_ids'] as $webhookId) {
            $webhook = $this->repository->findById((int) $webhookId);
            if (!$webhook || empty($webhook['enabled'])) {
                continue;
            }

            $this->dispatchWebhook($webhook, $job['payload'], false);
        }
    }

    /**
     * Synchronous test delivery for the admin UI.
     *
     * @param array<string, mixed> $webhook
     * @return array{success:bool,status:string,message:string,code?:int}
     */
    public function test(array $webhook)
    {
        $payload = $this->payloadBuilder->buildTestPayload($webhook);
        $result = $this->dispatchWebhook($webhook, $payload, true);

        return $result;
    }

    /**
     * @param array<string, mixed> $webhook
     * @param array<string, mixed> $payload
     * @param bool $isTest
     * @return array{success:bool,status:string,message:string,code?:int}
     */
    private function dispatchWebhook(array $webhook, array $payload, $isTest)
    {
        $attempts = $isTest ? 1 : ($this->maxRetries + 1);
        $timeout = $isTest ? $this->testTimeoutSeconds : $this->timeoutSeconds;
        $lastCode = 0;
        $lastMessage = '';
        $success = false;

        for ($i = 0; $i < $attempts; $i++) {
            $response = $this->sendRequest($webhook, $payload, $timeout);

            if (is_wp_error($response)) {
                $lastMessage = $response->get_error_message();
                $lastCode = 0;
                continue;
            }

            $lastCode = (int) wp_remote_retrieve_response_code($response);
            $lastMessage = wp_remote_retrieve_response_message($response);

            if ($lastCode >= 200 && $lastCode < 300) {
                $success = true;
                break;
            }
        }

        $status = $success ? 'success' : ('failed:' . ($lastCode ?: 'error'));

        if (!$isTest && !empty($webhook['id'])) {
            $this->repository->updateLastRun((int) $webhook['id'], $status);
        } elseif ($isTest && !empty($webhook['id'])) {
            $this->repository->updateLastRun((int) $webhook['id'], $status);
        }

        return array(
            'success' => $success,
            'status' => $status,
            'message' => $success
                ? sprintf(__('Delivered successfully (HTTP %d).', 'wpdatatables'), $lastCode)
                : sprintf(
                    __('Delivery failed%s.', 'wpdatatables'),
                    $lastMessage !== '' ? ': ' . $lastMessage : ''
                ),
            'code' => $lastCode,
        );
    }

    /**
     * @param array<string, mixed> $webhook
     * @param array<string, mixed> $payload
     * @param int $timeout
     * @return array|\WP_Error
     */
    private function sendRequest(array $webhook, array $payload, $timeout)
    {
        $method = strtoupper($webhook['method']);
        $url = $webhook['url'];
        $format = $webhook['format'];
        $headers = isset($webhook['headers']) && is_array($webhook['headers']) ? $webhook['headers'] : array();

        $body = '';
        if ($format === 'form-data') {
            $body = http_build_query($this->flattenPayload($payload));
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        } else {
            $body = wp_json_encode($payload);
            $headers['Content-Type'] = 'application/json';
        }

        if ($method === 'GET') {
            $url = add_query_arg($this->flattenPayload($payload), $url);
            $body = null;
            unset($headers['Content-Type']);
        }

        if (!empty($webhook['secret']) && $body !== null) {
            $headers['X-WPDataTables-Signature'] = hash_hmac('sha256', (string) $body, $webhook['secret']);
        }

        $args = array(
            'method' => $method,
            'timeout' => $timeout,
            'headers' => $headers,
            'redirection' => 3,
            'sslverify' => true,
        );

        if ($body !== null) {
            $args['body'] = $body;
        }

        return wp_remote_request($url, $args);
    }

    /**
     * Flatten nested arrays for form-data / GET query params.
     *
     * @param array<string, mixed> $payload
     * @param string $prefix
     * @return array<string, mixed>
     */
    private function flattenPayload(array $payload, $prefix = '')
    {
        $flat = array();

        foreach ($payload as $key => $value) {
            $fullKey = $prefix === '' ? (string) $key : $prefix . '[' . $key . ']';

            if (is_array($value)) {
                $flat = array_merge($flat, $this->flattenPayload($value, $fullKey));
            } elseif (is_bool($value)) {
                $flat[$fullKey] = $value ? '1' : '0';
            } elseif ($value === null) {
                $flat[$fullKey] = '';
            } else {
                $flat[$fullKey] = $value;
            }
        }

        return $flat;
    }
}
