<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\PublicApi\Hooks;

use WPDataTables\PublicApi\Routes\PublicApiAdminRoutes;
use WPDataTables\PublicApi\Routes\PublicRoutes;
use WPDataTables\Services\Settings\SettingsService;
use WPDataTables\Vendor\DI\Container;

/**
 * Wires public REST API route registration and the enabled filter.
 *
 * @package WPDataTables\PublicApi\Hooks
 */
class RestApiHook
{
    /** @var SettingsService|null */
    private $settingsService;

    public function __construct(?SettingsService $settingsService = null)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * @return void
     */
    public function register()
    {
        add_action('wpdatatables/rest/register_public_routes', [$this, 'registerPublicRoutes'], 10, 2);
        add_action('wpdatatables/rest/register_additional_routes', [$this, 'registerAdminRoutes'], 10, 2);
        add_filter('wpdatatables/public_api/enabled', [$this, 'isPublicApiEnabled']);
    }

    /**
     * @param Container $container
     * @param string    $namespace
     * @return void
     */
    public function registerPublicRoutes(Container $container, string $namespace)
    {
        PublicRoutes::registerRoutes($container, $namespace);
    }

    /**
     * @param Container $container
     * @param string    $namespace
     * @return void
     */
    public function registerAdminRoutes(Container $container, string $namespace)
    {
        PublicApiAdminRoutes::registerRoutes($container, $namespace);
    }

    /**
     * Runtime toggle for the public API (build-time gate is the Developer integration folder).
     *
     * @param bool $enabled
     * @return bool
     */
    public function isPublicApiEnabled(bool $enabled)
    {
        if ($enabled) {
            return true;
        }

        if (!$this->settingsService) {
            return false;
        }

        $developer = $this->settingsService->getCategorySettings('developer');

        return !empty($developer['public_api_enabled']);
    }
}
