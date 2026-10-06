<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Integration;

/**
 * IntegrationInterface — the first-class contract a (typically third-party /
 * add-on) integration implements to plug into wpDataTables.
 *
 * It is the structured alternative to registering a raw bootstrap file path via
 * {@see IntegrationRegistry::add()}: an integration object is registered with
 * {@see IntegrationRegistry::addIntegration()} (usually from the
 * `wpdatatables/integrations/register` action) and {@see IntegrationService}
 * calls `register()` on it during boot — but only when `isAvailable()` returns
 * true, so an integration can self-gate on its own dependencies.
 *
 * The bundled, tier-stripped integrations under `backend/tiers/<tier>/`
 * deliberately remain plain file entries (the Jenkins build strips them by
 * directory, which the file registry tolerates); this interface is the
 * conversion target for integrations that live outside those stripped
 * directories.
 *
 * @package WPDataTables\Services\Integration
 */
interface IntegrationInterface
{
    /**
     * Whether this integration should load in the current environment
     * (e.g. a required plugin/class is present, or the licence tier allows it).
     *
     * @return bool
     */
    public function isAvailable();

    /**
     * Wire the integration into wpDataTables (register hooks, types, assets…).
     * Called once during boot, only when {@see isAvailable()} is true.
     *
     * @return void
     */
    public function register();
}
