<?php

namespace WDTIntegration\Webhooks;

defined('ABSPATH') or die('Access denied.');

/**
 * CRUD + validation for outbound webhooks.
 */
class WebhookManagerService
{
    const ALLOWED_METHODS = array('GET', 'POST', 'PUT', 'PATCH', 'DELETE');
    const ALLOWED_FORMATS = array('json', 'form-data');
    const ALLOWED_EVENTS = array(
        'row.created',
        'row.updated',
        'row.deleted',
        'rows.bulk_imported',
    );

    /** @var WebhookRepository */
    private $repository;

    public function __construct(WebhookRepository $repository = null)
    {
        $this->repository = $repository ?: new WebhookRepository();
    }

    /**
     * @return WebhookRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param int $tableId
     * @return array<int, array<string, mixed>>
     */
    public function listForTable($tableId)
    {
        return $this->repository->findByTableId((int) $tableId);
    }

    /**
     * @param int $id
     * @param bool $includeSecret
     * @return array<string, mixed>|null
     */
    public function get($id, $includeSecret = false)
    {
        $webhook = $this->repository->findById((int) $id);
        if (!$webhook) {
            return null;
        }

        if (!$includeSecret) {
            unset($webhook['secret']);
        }

        return $webhook;
    }

    /**
     * @param array<string, mixed> $input
     * @return array{success:bool,id?:int,message?:string,errors?:array}
     */
    public function create(array $input)
    {
        $validated = $this->validate($input);
        if (!$validated['success']) {
            return $validated;
        }

        $id = $this->repository->create($validated['data']);

        return array(
            'success' => true,
            'id' => $id,
            'message' => __('Webhook created.', 'wpdatatables'),
        );
    }

    /**
     * @param int $id
     * @param array<string, mixed> $input
     * @return array{success:bool,message?:string,errors?:array}
     */
    public function update($id, array $input)
    {
        $existing = $this->repository->findById((int) $id);
        if (!$existing) {
            return array(
                'success' => false,
                'message' => __('Webhook not found.', 'wpdatatables'),
            );
        }

        $validated = $this->validate($input, true, $existing);
        if (!$validated['success']) {
            return $validated;
        }

        // Keep existing secret when the form leaves it blank.
        if ($validated['data']['secret'] === '' && $existing['secret'] !== '') {
            unset($validated['data']['secret']);
        }

        $this->repository->update((int) $id, $validated['data']);

        return array(
            'success' => true,
            'message' => __('Webhook updated.', 'wpdatatables'),
        );
    }

    /**
     * @param int $id
     * @return array{success:bool,message:string}
     */
    public function delete($id)
    {
        $existing = $this->repository->findById((int) $id);
        if (!$existing) {
            return array(
                'success' => false,
                'message' => __('Webhook not found.', 'wpdatatables'),
            );
        }

        $this->repository->delete((int) $id);

        return array(
            'success' => true,
            'message' => __('Webhook deleted.', 'wpdatatables'),
        );
    }

    /**
     * @param int $id
     * @param bool $enabled
     * @return array{success:bool,message:string}
     */
    public function updateStatus($id, $enabled)
    {
        $existing = $this->repository->findById((int) $id);
        if (!$existing) {
            return array(
                'success' => false,
                'message' => __('Webhook not found.', 'wpdatatables'),
            );
        }

        $this->repository->updateStatus((int) $id, (bool) $enabled);

        return array(
            'success' => true,
            'message' => __('Webhook status updated.', 'wpdatatables'),
        );
    }

    /**
     * @param int $tableId
     * @return void
     */
    public function deleteForTable($tableId)
    {
        $this->repository->deleteByTableId((int) $tableId);
    }

    /**
     * @param array<string, mixed> $input
     * @param bool $isUpdate
     * @param array<string, mixed>|null $existing
     * @return array{success:bool,data?:array,message?:string,errors?:array}
     */
    public function validate(array $input, $isUpdate = false, $existing = null)
    {
        $errors = array();

        $tableId = isset($input['table_id']) ? absint($input['table_id']) : 0;
        if ($tableId < 1) {
            $errors['table_id'] = __('Table ID is required.', 'wpdatatables');
        }

        $name = isset($input['name']) ? sanitize_text_field(wp_unslash($input['name'])) : '';
        if ($name === '') {
            $errors['name'] = __('Name is required.', 'wpdatatables');
        }

        $urlRaw = isset($input['url']) ? trim(wp_unslash($input['url'])) : '';
        $url = esc_url_raw($urlRaw);
        if ($url === '' || !preg_match('#^https?://#i', $url)) {
            $errors['url'] = __('A valid HTTP(S) URL is required.', 'wpdatatables');
        } elseif (!$this->isUrlAllowed($url)) {
            $errors['url'] = __('This URL is not allowed.', 'wpdatatables');
        }

        $method = isset($input['method']) ? strtoupper(sanitize_text_field(wp_unslash($input['method']))) : 'POST';
        if (!in_array($method, self::ALLOWED_METHODS, true)) {
            $errors['method'] = __('Invalid HTTP method.', 'wpdatatables');
        }

        $format = isset($input['format']) ? sanitize_text_field(wp_unslash($input['format'])) : 'json';
        if (!in_array($format, self::ALLOWED_FORMATS, true)) {
            $errors['format'] = __('Invalid payload format.', 'wpdatatables');
        }

        $event = isset($input['event']) ? sanitize_text_field(wp_unslash($input['event'])) : '';
        if (!in_array($event, self::ALLOWED_EVENTS, true)) {
            $errors['event'] = __('Invalid event.', 'wpdatatables');
        }

        $secret = '';
        if (isset($input['secret'])) {
            $secret = sanitize_text_field(wp_unslash($input['secret']));
        }

        $headers = $this->sanitizeHeaders(isset($input['headers']) ? $input['headers'] : array());
        $enabled = !empty($input['enabled']);

        if (!empty($errors)) {
            return array(
                'success' => false,
                'message' => __('Validation failed.', 'wpdatatables'),
                'errors' => $errors,
            );
        }

        return array(
            'success' => true,
            'data' => array(
                'table_id' => $tableId,
                'name' => $name,
                'url' => $url,
                'method' => $method,
                'format' => $format,
                'headers' => wp_json_encode($headers),
                'secret' => $secret,
                'event' => $event,
                'enabled' => $enabled,
            ),
        );
    }

    /**
     * @param mixed $headers
     * @return array<string, string>
     */
    private function sanitizeHeaders($headers)
    {
        if (is_string($headers)) {
            $decoded = json_decode(wp_unslash($headers), true);
            $headers = is_array($decoded) ? $decoded : array();
        }

        if (!is_array($headers)) {
            return array();
        }

        $clean = array();
        foreach ($headers as $key => $value) {
            if (is_array($value) && isset($value['key'], $value['value'])) {
                $headerKey = sanitize_text_field($value['key']);
                $headerValue = sanitize_text_field($value['value']);
            } else {
                $headerKey = sanitize_text_field((string) $key);
                $headerValue = sanitize_text_field((string) $value);
            }

            if ($headerKey === '' || $headerValue === '') {
                continue;
            }

            $clean[$headerKey] = $headerValue;
        }

        return $clean;
    }

    /**
     * Basic SSRF hardening: block localhost / private IP literals.
     *
     * @param string $url
     * @return bool
     */
    public function isUrlAllowed($url)
    {
        $host = wp_parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return false;
        }

        $host = strtolower($host);
        if (in_array($host, array('localhost', '127.0.0.1', '::1', '0.0.0.0'), true)) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return (bool) filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
        }

        return true;
    }

    /**
     * Human-readable event labels.
     *
     * @return array<string, string>
     */
    public static function eventLabels()
    {
        return array(
            'row.created' => __('Row created', 'wpdatatables'),
            'row.updated' => __('Row updated', 'wpdatatables'),
            'row.deleted' => __('Row deleted', 'wpdatatables'),
            'rows.bulk_imported' => __('Row(s) bulk imported', 'wpdatatables'),
        );
    }
}
