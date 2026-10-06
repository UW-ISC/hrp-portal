<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\PublicApi\Controllers;

use WPDataTables\Common\Exceptions\ForbiddenException;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Common\Sanitizer\Sanitizer;
use WPDataTables\Services\Settings\SettingsService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Admin endpoints for the public API feature toggle.
 *
 * @package WPDataTables\PublicApi\Controllers
 */
class PublicApiSettingsAdminController
{
    /** @var SettingsService */
    private $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response|WP_Error
     */
    public function getSettings(WP_REST_Request $data)
    {
        try {
            Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

            return new WP_REST_Response([
                'data' => $this->buildSettingsPayload(),
            ], 200);
        } catch (ForbiddenException $e) {
            return new WP_Error('wpdatatables_public_api_forbidden', $e->getMessage(), ['status' => 403]);
        }
    }

    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response|WP_Error
     */
    public function updateSettings(WP_REST_Request $data)
    {
        try {
            Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

            $params = $data->get_json_params();
            if (!is_array($params)) {
                $params = $data->get_params();
            }

            if (!array_key_exists('public_api_enabled', $params)) {
                throw new InvalidArgumentException(__('public_api_enabled is required.', 'wpdatatables'));
            }

            $developer = $this->settingsService->getCategorySettings('developer');
            if (!is_array($developer)) {
                $developer = [];
            }

            $developer['public_api_enabled'] = !empty($params['public_api_enabled']);
            $this->settingsService->setCategorySettings('developer', $developer);

            return new WP_REST_Response([
                'data' => $this->buildSettingsPayload(),
            ], 200);
        } catch (ForbiddenException $e) {
            return new WP_Error('wpdatatables_public_api_forbidden', $e->getMessage(), ['status' => 403]);
        } catch (InvalidArgumentException $e) {
            return new WP_Error('wpdatatables_public_api_invalid', $e->getMessage(), ['status' => 400]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSettingsPayload()
    {
        $developer = $this->settingsService->getCategorySettings('developer');

        return [
            'public_api_enabled' => !empty($developer['public_api_enabled']),
            'base_url'           => rest_url('wpdatatables-public/v1/'),
        ];
    }
}
