<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Routes;

use WPDataTables\Common\Rest\RestPermissions;
use WPDataTables\Vendor\DI\Container;

/**
 * REST route aggregator.
 *
 * Mirrors the ivyforms `Routes\Routes` pattern: a single static
 * `registerRoutes(Container)` that wires every domain route group under one
 * namespace and then fires an extension action so integrations can add their
 * own routes. Admin REST (`wpdatatables/v1`) registers on `rest_api_init`
 * from {@see \WPDataTables\Plugin\Plugin} on every tier. Public REST
 * (`wpdatatables-public/v1`) registers via
 * `wpdatatables/rest/register_public_routes` when the Developer integration
 * bootstrap is present.
 *
 * @package WPDataTables\Routes
 */
class Routes
{
    /** REST namespace + version, e.g. `/wp-json/wpdatatables/v1/...`. */
    public static $routeNamespace = 'wpdatatables/v1';

    /** Public developer API namespace, e.g. `/wp-json/wpdatatables-public/v1/...`. */
    public static $publicRouteNamespace = 'wpdatatables-public/v1';

    /**
     * Register all wpDataTables REST routes.
     *
     * @param Container $container The built PHP-DI container; route groups
     *                             resolve their controllers from it.
     * @return void
     */
    public static function registerRoutes(Container $container)
    {
        Table\Table::registerRoutes($container, self::$routeNamespace);
        Chart\Chart::registerRoutes($container, self::$routeNamespace);
        Column\Column::registerRoutes($container, self::$routeNamespace);
        Settings\Settings::registerRoutes($container, self::$routeNamespace);

        /**
         * Allow integrations / add-ons to register additional REST routes.
         *
         * @since 7.x
         * @param Container $container The wpDataTables DI container.
         * @param string    $namespace The current REST namespace (e.g. `wpdatatables/v1`).
         */
        do_action('wpdatatables/rest/register_additional_routes', $container, self::$routeNamespace);
    }

    /**
     * Register the public developer REST API routes.
     *
     * Fires `wpdatatables/rest/register_public_routes` so the Developer
     * integration can wire API-key-authenticated read routes under
     * {@see self::$publicRouteNamespace} without coupling the core aggregator
     * to key management.
     *
     * @param Container $container The built PHP-DI container.
     * @return void
     */
    public static function registerPublicRoutes(Container $container)
    {
        /**
         * Allow integrations / add-ons to register public REST routes.
         *
         * @since 7.x
         * @param Container $container The wpDataTables DI container.
         * @param string    $namespace The public REST namespace (e.g. `wpdatatables-public/v1`).
         */
        do_action('wpdatatables/rest/register_public_routes', $container, self::$publicRouteNamespace);
    }
}
