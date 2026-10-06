<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\PublicApi\Routes;

use WPDataTables\PublicApi\Controllers\ChartsPublicController;
use WPDataTables\PublicApi\Controllers\ColumnsPublicController;
use WPDataTables\PublicApi\Controllers\TableDataPublicController;
use WPDataTables\PublicApi\Controllers\TablesPublicController;
use WPDataTables\Vendor\DI\Container;

/**
 * Registers wpdatatables-public/v1 REST routes.
 *
 * @package WPDataTables\PublicApi\Routes
 */
class PublicRoutes
{
    const NAMESPACE = 'wpdatatables-public/v1';

    /**
     * @param Container $container
     * @param string    $namespace
     * @return void
     */
    public static function registerRoutes(Container $container, string $namespace)
    {
        if ($namespace !== self::NAMESPACE) {
            return;
        }

        $container->get(TablesPublicController::class)->register_routes();
        $container->get(ColumnsPublicController::class)->register_routes();
        $container->get(ChartsPublicController::class)->register_routes();
        $container->get(TableDataPublicController::class)->register_routes();
    }
}
