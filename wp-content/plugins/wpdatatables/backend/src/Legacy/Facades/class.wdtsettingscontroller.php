<?php

defined('ABSPATH') or die('Access denied.');

use WPDataTables\Plugin\Plugin;
use WPDataTables\Services\Settings\SettingsService;

/**
 * WDTSettingsController
 *
 * Backward-compatibility facade. The implementation lives in
 * {@see WPDataTables\Services\Settings\SettingsService}. This class is kept as
 * a thin facade so existing call sites, templates, and third-party / pro add-ons
 * that reference `WDTSettingsController::*` keep working.
 */
class WDTSettingsController
{
    /**
     * Resolve the SettingsService from the DI container.
     *
     * @return SettingsService
     */
    private static function service()
    {
        return Plugin::container()->get(SettingsService::class);
    }

    public static function sanitizeSettings($settings)
    {
        return self::service()->sanitizeSettings($settings);
    }

    public static function saveGoogleSettings($settings)
    {
        return self::service()->saveGoogleSettings($settings);
    }

    public static function saveGoogleApiMaps($settings)
    {
        return self::service()->saveGoogleApiMaps($settings);
    }

    public static function saveSettings($settings)
    {
        return self::service()->saveSettings($settings);
    }

    public static function getCurrentPluginConfig()
    {
        return self::service()->getCurrentPluginConfig();
    }

    public static function getInterfaceLanguages()
    {
        return self::service()->getInterfaceLanguages();
    }

    public static function getArrInterfaceLanguages()
    {
        return self::service()->getArrInterfaceLanguages();
    }

    public static function wdtGetSystemFonts()
    {
        return self::service()->getSystemFonts();
    }
}
