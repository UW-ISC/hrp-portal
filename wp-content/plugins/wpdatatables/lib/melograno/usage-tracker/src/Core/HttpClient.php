<?php

declare (strict_types=1);
namespace WPDT\Melograno\UsageTracker\Core;

class HttpClient
{
    /**
     * @param array<string, mixed> $data
     */
    public function post(string $url, array $data) : bool
    {
        if (!\function_exists('wp_remote_post')) {
            return \false;
        }
        $body = \function_exists('wp_json_encode') ? \wp_json_encode($data) : \json_encode($data);
        $response = \wp_remote_post($url, ['timeout' => 15, 'headers' => ['Content-Type' => 'application/json'], 'body' => $body]);
        if (\is_wp_error($response)) {
            if (\defined('WP_DEBUG') && \WP_DEBUG) {
                \error_log('[melograno/usage-tracker] HTTP error: ' . $response->get_error_message());
            }
            return \false;
        }
        $code = (int) \wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            if (\defined('WP_DEBUG') && \WP_DEBUG) {
                $responseBody = \wp_remote_retrieve_body($response);
                \error_log('[melograno/usage-tracker] HTTP status: ' . $code . ($responseBody !== '' ? ' body: ' . $responseBody : ''));
            }
            return \false;
        }
        return \true;
    }
}
