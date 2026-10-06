<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\InstallActions;

/**
 * Plugin deactivation actions.
 *
 * Owns the deactivation routine. The legacy `wdtDeactivation()` function is a
 * no-op today and is preserved as such here; the shim delegates to this method
 * so future deactivation logic has a single home.
 *
 * @package WPDataTables\Services\InstallActions
 */
class DeactivationHook
{
    /**
     * Deactivation entry point (currently no-op, matching legacy behaviour).
     */
    public static function deactivate(): void
    {
    }
}
