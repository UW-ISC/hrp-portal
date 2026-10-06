<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\PublicApi\Permissions;

use WPDataTables\PublicApi\Services\PublicApiKeyService;
use WP_Error;
use WP_REST_Request;

/**
 * Permission callbacks for the wpdatatables-public/v1 developer API.
 *
 * @package WPDataTables\PublicApi\Permissions
 */
class PublicApiPermissions
{
    /** @var PublicApiKeyService */
    private $keyService;

    public function __construct(PublicApiKeyService $keyService)
    {
        $this->keyService = $keyService;
    }

    /**
     * @return bool
     */
    public static function isEnabled()
    {
        return (bool) apply_filters('wpdatatables/public_api/enabled', false);
    }

    /**
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function canRead(WP_REST_Request $request)
    {
        if (!self::isEnabled()) {
            return $this->disabledError();
        }

        if ($this->keyService->hasScope($request, 'read')) {
            return true;
        }

        return $this->invalidKeyError();
    }

    /**
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function canEdit(WP_REST_Request $request)
    {
        if (!self::isEnabled()) {
            return $this->disabledError();
        }

        if ($this->keyService->hasScope($request, 'edit')) {
            return true;
        }

        return $this->invalidKeyError();
    }

    /**
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function canDelete(WP_REST_Request $request)
    {
        if (!self::isEnabled()) {
            return $this->disabledError();
        }

        if ($this->keyService->hasScope($request, 'delete')) {
            return true;
        }

        return $this->invalidKeyError();
    }

    /**
     * @return WP_Error
     */
    private function disabledError()
    {
        return new WP_Error(
            'wpdatatables_public_api_disabled',
            __('Public REST API is disabled.', 'wpdatatables'),
            ['status' => 403]
        );
    }

    /**
     * @return WP_Error
     */
    private function invalidKeyError()
    {
        return new WP_Error(
            'wpdatatables_invalid_api_key',
            __('Invalid or missing API key.', 'wpdatatables'),
            ['status' => 401]
        );
    }
}
