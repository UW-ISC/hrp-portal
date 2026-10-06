<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Integration;

/**
 * IntegrationRegistry — an ordered, mutable list of integration bootstrap files
 * to be `require_once`'d by {@see IntegrationService}.
 *
 * Each entry records the absolute file path plus two flags that reproduce the
 * exact semantics of the legacy tier loader:
 *
 *  - `optional` — when true the file is loaded only `if (is_file($path))`. This
 *    is REQUIRED for any file under a tier directory that the Jenkins build
 *    strips per licence tier (`backend/tiers/standard`, `backend/tiers/pro`,
 *    `backend/tiers/developer`); those directories are `rm -rf`'d for lower tiers,
 *    so the loader must tolerate their absence. Files under
 *    `backend/tiers/starter` are never stripped, so they may be non-optional
 *    (matching the legacy unconditional `require_once`s).
 *  - `admin` — when true the file is loaded only in `is_admin()` context.
 *
 * The registry is passed to the `wpdatatables/integrations/register` action so
 * third-party / add-on integrations can append their own entries.
 *
 * @package WPDataTables\Services\Integration
 */
class IntegrationRegistry
{
    /** @var array<int,array{file:string,optional:bool,admin:bool}> */
    private $entries = array();

    /** @var IntegrationInterface[] */
    private $integrations = array();

    /**
     * Append an integration bootstrap file.
     *
     * @param string $file     Absolute path to the integration's PHP entry file.
     * @param bool   $optional Load only if the file exists (tier-strippable files).
     * @param bool   $admin    Load only in admin context.
     * @return self
     */
    public function add($file, $optional = true, $admin = false)
    {
        $this->entries[] = array(
            'file' => $file,
            'optional' => (bool)$optional,
            'admin' => (bool)$admin,
        );

        return $this;
    }

    /**
     * @return array<int,array{file:string,optional:bool,admin:bool}>
     */
    public function all()
    {
        return $this->entries;
    }

    /**
     * Register a first-class integration object. The structured alternative to
     * {@see add()} for integrations that implement {@see IntegrationInterface}
     * (typically third-party / add-on integrations hooking
     * `wpdatatables/integrations/register`).
     *
     * @param IntegrationInterface $integration
     * @return self
     */
    public function addIntegration(IntegrationInterface $integration)
    {
        $this->integrations[] = $integration;

        return $this;
    }

    /**
     * @return IntegrationInterface[]
     */
    public function integrations()
    {
        return $this->integrations;
    }
}
