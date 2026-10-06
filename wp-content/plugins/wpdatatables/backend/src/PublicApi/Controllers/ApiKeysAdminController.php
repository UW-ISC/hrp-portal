<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\PublicApi\Controllers;

use WPDataTables\Common\Exceptions\ForbiddenException;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Common\Sanitizer\Sanitizer;
use WPDataTables\PublicApi\Services\PublicApiKeyService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Admin CRUD for public API keys.
 *
 * @package WPDataTables\PublicApi\Controllers
 */
class ApiKeysAdminController
{
    /** @var PublicApiKeyService */
    private $keyService;

    public function __construct(PublicApiKeyService $keyService)
    {
        $this->keyService = $keyService;
    }

    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response|WP_Error
     */
    public function listKeys(WP_REST_Request $data)
    {
        try {
            Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

            return new WP_REST_Response([
                'data' => $this->keyService->listKeys(),
            ], 200);
        } catch (ForbiddenException $e) {
            return new WP_Error('wpdatatables_public_api_forbidden', $e->getMessage(), ['status' => 403]);
        } catch (InvalidArgumentException $e) {
            return new WP_Error('wpdatatables_public_api_invalid', $e->getMessage(), ['status' => 400]);
        }
    }

    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response|WP_Error
     */
    public function createKey(WP_REST_Request $data)
    {
        try {
            Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

            $params = $data->get_json_params();
            if (!is_array($params)) {
                $params = $data->get_params();
            }

            $label = sanitize_text_field((string) ($params['label'] ?? ''));
            if ($label === '') {
                throw new InvalidArgumentException(__('API key label is required.', 'wpdatatables'));
            }

            $scopes = $params['scopes'] ?? ['read'];
            if (!is_array($scopes)) {
                $scopes = ['read'];
            }

            $expiresAt = $this->resolveExpiration($params['expires_in_years'] ?? null);
            $created = $this->keyService->createKey($label, $scopes, $expiresAt);

            return new WP_REST_Response([
                'data' => [
                    'key'    => $created['key'],
                    'record' => $created['record'],
                ],
            ], 201);
        } catch (ForbiddenException $e) {
            return new WP_Error('wpdatatables_public_api_forbidden', $e->getMessage(), ['status' => 403]);
        } catch (InvalidArgumentException $e) {
            return new WP_Error('wpdatatables_public_api_invalid', $e->getMessage(), ['status' => 400]);
        }
    }

    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response|WP_Error
     */
    public function revokeKey(WP_REST_Request $data)
    {
        try {
            Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

            $id = sanitize_text_field((string) $data->get_param('id'));
            if ($id === '') {
                throw new InvalidArgumentException(__('API key id is required.', 'wpdatatables'));
            }

            $this->keyService->revokeKey($id);

            return new WP_REST_Response([
                'data' => ['revoked' => true],
            ], 200);
        } catch (ForbiddenException $e) {
            return new WP_Error('wpdatatables_public_api_forbidden', $e->getMessage(), ['status' => 403]);
        } catch (InvalidArgumentException $e) {
            return new WP_Error('wpdatatables_public_api_invalid', $e->getMessage(), ['status' => 400]);
        }
    }

    /**
     * @param mixed $expiresInYears
     * @return int|null
     */
    private function resolveExpiration($expiresInYears)
    {
        if ($expiresInYears === null || $expiresInYears === '' || $expiresInYears === 0) {
            return null;
        }

        $years = (int) $expiresInYears;
        if ($years < 1) {
            return null;
        }

        $timestamp = strtotime('+' . $years . ' years');

        return $timestamp === false ? null : $timestamp;
    }
}
