<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin;

use Exception;
use WPDataTables\Routes\Routes;
use WPDataTables\Services\Integration\IntegrationService;
use WPDataTables\Vendor\DI\Container;
use WPDataTables\Vendor\DI\ContainerBuilder;

/**
 * Plugin bootstrap singleton.
 *
 * Builds the PHP-DI container from the Config definition files, fires the
 * `wpdatatables/boot/extend_container_builder` action so integrations can add
 * their own definitions, then exposes the built container. WordPress hooks are
 * wired here via AdminHooks / FrontendHooks / AjaxHooks.
 *
 * @package WPDataTables\Plugin
 */
class Plugin
{
    /** @var Plugin|null */
    private static ?Plugin $instance = null;

    /** @var ContainerBuilder */
    private ContainerBuilder $builder;

    /** @var Container|null */
    private ?Container $container = null;

    /**
     * @return Plugin
     * @throws Exception
     */
    public static function getInstance(): Plugin
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $configPath = WDT_ROOT_PATH . 'backend/src/Config/';

        $this->builder = new ContainerBuilder();
        $this->builder->addDefinitions($configPath . 'repository.php');
        $this->builder->addDefinitions($configPath . 'services.php');

        /**
         * Allow integrations to extend the container before it is built.
         *
         * @since 7.5.0
         * @param ContainerBuilder $builder The wpDataTables DI container builder.
         */
        do_action('wpdatatables/boot/extend_container_builder', $this->builder);

        $this->container = $this->builder->build();

        require_once WDT_ROOT_PATH . 'backend/src/Legacy/GlobalFunctions.php';

        $this->registerHooks();
    }

    /**
     * Wire the WordPress hooks that the layered code owns: FrontendHooks
     * (shortcodes + filtering widget), AjaxHooks (`wp_ajax_*` glue →
     * controllers), and AdminHooks (admin-screen glue).
     *
     * @return void
     */
    private function registerHooks(): void
    {
        $this->container->get(FrontendHooks::class)->register();
        $this->container->get(AjaxHooks::class)->register();
        $this->container->get(AdminHooks::class)->register();
        $this->container->get(PluginLifecycleHooks::class)->register();
        $this->container->get(EditorHooks::class)->register();

        //[<-- Full version -->]//
        $this->container->get(PluginUpdateHooks::class)->register();
        //[<--/ Full version -->]//

        $this->container->get(AiHooks::class)->register();

        add_action('rest_api_init', static function () {
            if (!class_exists(Routes::class)) {
                return;
            }

            Routes::registerRoutes(Plugin::container());
        });
    }

    /**
     * Instance accessor for the built container.
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Static convenience accessor used by the legacy delegating shims to reach
     * services without their own container reference.
     *
     * @return Container
     * @throws Exception
     */
    public static function container(): Container
    {
        return self::getInstance()->getContainer();
    }

    /**
     * Load licence-tier integration bootstrap files via IntegrationService.
     *
     * Called after the DI container is built. Tier modules may reference
     * legacy facade classes; Composer classmap autoloads them on demand.
     *
     * @return void
     */
    public static function bootIntegrations(): void
    {
        self::container()
            ->get(IntegrationService::class)
            ->init();
    }
}
