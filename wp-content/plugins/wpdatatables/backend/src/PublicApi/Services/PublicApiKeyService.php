<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\PublicApi\Services;

use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Services\Settings\SettingsService;
use WP_REST_Request;

/**
 * API key storage, validation, and lifecycle for the public REST API.
 *
 * @package WPDataTables\PublicApi\Services
 */
class PublicApiKeyService
{
    const SETTINGS_CATEGORY = 'developer';
    const SETTINGS_KEY = 'public_api_keys';
    const MAX_KEYS = 25;
    private const KEYS_LOCK_NAME = 'wpdatatables_public_api_keys';

    /**
     * @var string[]
     */
    const ALLOWED_SCOPES = ['read', 'edit', 'delete'];

    /** @var SettingsService */
    private $settingsService;

    /**
     * Per-request auth cache: null = not checked, false = invalid, array = valid record.
     *
     * @var array<string, mixed>|false|null
     */
    private $authenticatedKey = null;

    /**
     * Header value the auth cache was computed for (resets cache when it changes).
     *
     * @var string|null
     */
    private $authenticatedHeader = null;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * @param string $plainKey
     * @return string
     */
    public function createHash(string $plainKey)
    {
        return hash('sha256', $plainKey);
    }

    /**
     * @param string   $label
     * @param string[] $scopes
     * @param int|null $expiresAt
     * @return array{key: string, record: array<string, mixed>}
     */
    public function createKey(string $label, array $scopes, ?int $expiresAt)
    {
        if (!$this->acquireKeysLock()) {
            throw new InvalidArgumentException('Unable to update API keys. Please try again.');
        }

        try {
            $keys = $this->getStoredKeys();
            if (count($keys) >= self::MAX_KEYS) {
                throw new InvalidArgumentException('Maximum number of API keys reached.');
            }

            $scopes = $this->normalizeScopes($scopes);
            $plainKey = $this->generatePlaintextKey();
            $record = new PublicApiKeyRecord(
                $this->generateKeyId(),
                $label,
                $this->createHash($plainKey),
                substr($plainKey, -4),
                $scopes,
                $expiresAt,
                time()
            );

            $keys[] = $record->toStorageArray();
            $this->persistKeys($keys);

            return [
                'key'    => $plainKey,
                'record' => $record->toPublicArray(),
            ];
        } finally {
            $this->releaseKeysLock();
        }
    }

    /**
     * @param WP_REST_Request $request
     * @return array<string, mixed>|null
     */
    public function authenticate(WP_REST_Request $request)
    {
        $headerKey = $request->get_header(self::headerName());
        $headerKey = ($headerKey === '' || $headerKey === null) ? null : (string) $headerKey;

        if ($this->authenticatedHeader !== $headerKey) {
            $this->authenticatedKey = null;
            $this->authenticatedHeader = $headerKey;
        }

        if ($this->authenticatedKey === false) {
            return null;
        }

        if (is_array($this->authenticatedKey)) {
            return $this->authenticatedKey;
        }

        if ($headerKey === null) {
            $this->authenticatedKey = false;

            return null;
        }

        $matched = $this->findValidKeyRecord($headerKey);
        if ($matched === null) {
            $this->authenticatedKey = false;

            return null;
        }

        /**
         * Allow custom validators to reject or enrich the matched key.
         *
         * @since 7.x
         * @param array<string, mixed> $matched
         * @param WP_REST_Request        $request
         */
        $filtered = apply_filters('wpdatatables/public_api/authenticated_key', $matched, $request);
        if (!is_array($filtered)) {
            $this->authenticatedKey = false;

            return null;
        }

        $this->authenticatedKey = $filtered;

        return $filtered;
    }

    /**
     * @param WP_REST_Request $request
     * @param string          $scope
     * @return bool
     */
    public function hasScope(WP_REST_Request $request, string $scope)
    {
        $record = $this->authenticate($request);
        if ($record === null) {
            return false;
        }

        $scopes = $record['scopes'] ?? [];

        return is_array($scopes) && in_array($scope, $scopes, true);
    }

    /**
     * @param string $id
     * @return void
     */
    public function revokeKey(string $id)
    {
        if (!$this->acquireKeysLock()) {
            throw new InvalidArgumentException('Unable to update API keys. Please try again.');
        }

        try {
            $keys = array_values(array_filter(
                $this->getStoredKeys(),
                static function (array $key) use ($id) {
                    return ($key['id'] ?? '') !== $id;
                }
            ));

            $this->persistKeys($keys);
        } finally {
            $this->releaseKeysLock();
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listKeys()
    {
        return array_map(
            static function (array $key) {
                return PublicApiKeyRecord::fromArray($key)->toPublicArray();
            },
            $this->getStoredKeys()
        );
    }

    /**
     * @return string
     */
    public static function headerName()
    {
        return 'x_wpdatatables_api_key';
    }

    /**
     * @return string
     */
    private function generateKeyId()
    {
        $uuid = function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : $this->fallbackUuid4();

        return 'wdk-' . $uuid;
    }

    /**
     * @return string
     */
    private function fallbackUuid4()
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    /**
     * @return string
     */
    private function generatePlaintextKey()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /**
     * @param string[] $scopes
     * @return string[]
     */
    private function normalizeScopes(array $scopes)
    {
        $normalized = array_values(array_unique(array_filter(
            array_map('strval', $scopes),
            static function ($scope) {
                return in_array($scope, self::ALLOWED_SCOPES, true);
            }
        )));

        if ($normalized === []) {
            return ['read'];
        }

        return $normalized;
    }

    /**
     * @param string $plainKey
     * @return array<string, mixed>|null
     */
    private function findValidKeyRecord(string $plainKey)
    {
        $hash = $this->createHash($plainKey);

        foreach ($this->getStoredKeys() as $stored) {
            $record = PublicApiKeyRecord::fromArray($stored);
            if (!hash_equals($record->getHash(), $hash)) {
                continue;
            }

            if ($record->isExpired()) {
                continue;
            }

            return $record->toStorageArray();
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getStoredKeys()
    {
        $developer = $this->settingsService->getCategorySettings(self::SETTINGS_CATEGORY);
        if (!is_array($developer)) {
            return [];
        }

        $keys = $developer[self::SETTINGS_KEY] ?? [];

        return is_array($keys) ? array_values($keys) : [];
    }

    /**
     * @param array<int, array<string, mixed>> $keys
     * @return void
     */
    private function persistKeys(array $keys)
    {
        $developer = $this->settingsService->getCategorySettings(self::SETTINGS_CATEGORY);
        if (!is_array($developer)) {
            $developer = [];
        }

        $developer[self::SETTINGS_KEY] = $keys;
        $this->settingsService->setCategorySettings(self::SETTINGS_CATEGORY, $developer);
    }

    /**
     * @return bool
     */
    private function acquireKeysLock()
    {
        global $wpdb;

        $result = $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, %d)', self::KEYS_LOCK_NAME, 2));

        return (int) $result === 1;
    }

    /**
     * @return void
     */
    private function releaseKeysLock()
    {
        global $wpdb;

        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', self::KEYS_LOCK_NAME));
    }
}
