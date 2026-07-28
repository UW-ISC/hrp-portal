<?php
/**
 * Ability: wpdatatables/update-global-settings
 *
 * Reads and/or updates wpDataTables global plugin settings: date/time format,
 * number format, default skin, CSV delimiter, custom CSS/JS, and feature toggles.
 *
 * Uses WDTSettingsController::getCurrentPluginConfig() for reading and
 * WDTSettingsController::saveSettings() for writing.
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_update_global_settings_ability' );

function wdtmcp_register_update_global_settings_ability() {
    wp_register_ability(
        'wpdatatables/update-global-settings',
        array(
            'label'       => __( 'Update Global Settings', 'wpdatatables' ),
            'description' => __( 'Reads or updates wpDataTables global plugin settings. When called with no settings to change, returns the current configuration. Provide key-value pairs to update specific settings (date format, number format, skin, CSV delimiter, etc.). WHEN TO CALL: to read site-wide wpDataTables defaults, or to change global formats/skins/CSS/JS that affect all tables. OPTIONAL INPUT: settings (object) — omit or pass {} to read only; pass key-value pairs to update. RETURNS: current_settings and updated_keys (empty on read-only call). NOTE: get-system-info also returns a settings snapshot — use update-global-settings when you need to change them. Requires manage_options capability.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'settings' => wdtmcp_get_updatable_global_settings_input_schema(),
                ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'current_settings' => array( 'type' => 'object', 'description' => 'Current global settings after any updates.' ),
                    'updated_keys'     => array( 'type' => 'array',  'description' => 'Keys that were changed (empty if read-only call).', 'items' => array( 'type' => 'string' ) ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_update_global_settings',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Pass an empty settings object {} to read current global configuration without changes. Pass key-value pairs to update site-wide wpDataTables defaults.', 'wpdatatables' ),
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
            ),
        )
    );
}

/**
 * JSON Schema property definitions for updatable global settings (source of truth).
 *
 * @return array<string, array{type: string, description: string}>
 */
function wdtmcp_get_updatable_global_setting_definitions() {
    return array(
        'wdtInterfaceLanguage'          => array(
            'type'        => 'string',
            'description' => 'Interface language file (e.g. de_DE.inc.php) or empty string for English default.',
        ),
        'wdtTablesPerPage'              => array(
            'type'        => 'integer',
            'description' => 'Number of tables shown per page in the admin browse screen (typically 10–50).',
        ),
        'wdtDateFormat'                 => array(
            'type'        => 'string',
            'description' => 'PHP date format for date columns (e.g. d/m/Y, Y-m-d).',
        ),
        'wdtTimeFormat'                 => array(
            'type'        => 'string',
            'description' => 'PHP time format for time/datetime columns (e.g. H:i, h:i A).',
        ),
        'wdtBaseSkin'                   => array(
            'type'        => 'string',
            'description' => 'Base table skin (material, light, graphite, aqua, purple, dark, etc.).',
        ),
        'wdtNumberFormat'               => array(
            'type'        => 'integer',
            'description' => 'Number format preset: 1 = 15.000,00; 2 = 15,000.00.',
        ),
        'wdtRenderFilter'               => array(
            'type'        => 'string',
            'description' => 'Where to render the advanced filter: header or footer.',
        ),
        'wdtDecimalPlaces'              => array(
            'type'        => 'integer',
            'description' => 'Decimal places for float number columns.',
        ),
        'wdtCSVDelimiter'               => array(
            'type'        => 'string',
            'description' => 'CSV export delimiter character (e.g. , ; | or tab).',
        ),
        'wdtSortingOrderBrowseTables'   => array(
            'type'        => 'string',
            'description' => 'Admin browse tables sort direction: ASC or DESC.',
        ),
        'wdtTabletWidth'                => array(
            'type'        => 'integer',
            'description' => 'Viewport width in pixels treated as tablet for responsive tables.',
        ),
        'wdtMobileWidth'                => array(
            'type'        => 'integer',
            'description' => 'Viewport width in pixels treated as mobile for responsive tables.',
        ),
        'wdtGettingStartedPageStatus'   => array(
            'type'        => 'integer',
            'description' => 'Getting Started admin page visibility (0 = hidden, 1 = shown).',
        ),
        'wdtLiteVSPremiumPageStatus'    => array(
            'type'        => 'integer',
            'description' => 'Lite vs Premium admin page visibility (0 = hidden, 1 = shown).',
        ),
        'wdtIncludeGoogleFonts'         => array(
            'type'        => 'integer',
            'description' => 'Load Google Fonts on the front end (0 = off, 1 = on).',
        ),
        'wdtIncludeBootstrap'           => array(
            'type'        => 'integer',
            'description' => 'Load Bootstrap on the front end (0 = off, 1 = on).',
        ),
        'wdtIncludeBootstrapBackEnd'    => array(
            'type'        => 'integer',
            'description' => 'Load Bootstrap in wp-admin (0 = off, 1 = on).',
        ),
        'wdtPreventDeletingTables'      => array(
            'type'        => 'integer',
            'description' => 'Prevent non-admin users from deleting tables (0 = off, 1 = on).',
        ),
        'wdtParseShortcodes'            => array(
            'type'        => 'integer',
            'description' => 'Parse WordPress shortcodes inside table cell strings (0 = off, 1 = on).',
        ),
        'wdtNumbersAlign'               => array(
            'type'        => 'integer',
            'description' => 'Right-align integer and float columns (0 = off, 1 = on).',
        ),
        'wdtGlobalTableLoader'          => array(
            'type'        => 'integer',
            'description' => 'Global default table loader/spinner style ID.',
        ),
        'wdtGlobalChartLoader'          => array(
            'type'        => 'integer',
            'description' => 'Global default chart loader/spinner style ID.',
        ),
        'wdtBorderRemoval'              => array(
            'type'        => 'integer',
            'description' => 'Remove table cell borders globally (0 = off, 1 = on).',
        ),
        'wdtBorderRemovalHeader'        => array(
            'type'        => 'integer',
            'description' => 'Remove table header borders globally (0 = off, 1 = on).',
        ),
        'wdtUseSeparateCon'             => array(
            'type'        => 'integer',
            'description' => 'Enable separate database connections feature (0 = off, 1 = on).',
        ),
        'wdtCustomCss'                  => array(
            'type'        => 'string',
            'description' => 'Custom CSS appended to wpDataTables front-end output.',
        ),
        'wdtCustomJs'                   => array(
            'type'        => 'string',
            'description' => 'Custom JavaScript for wpDataTables (requires unfiltered_html to save non-empty values).',
        ),
        'wdtMinifiedJs'                 => array(
            'type'        => 'integer',
            'description' => 'Serve minified plugin JS assets (0 = off, 1 = on).',
        ),
        'wdtSumFunctionsLabel'          => array(
            'type'        => 'string',
            'description' => 'Label for Sum aggregate functions (default Σ =).',
        ),
        'wdtAvgFunctionsLabel'          => array(
            'type'        => 'string',
            'description' => 'Label for Average aggregate functions.',
        ),
        'wdtMinFunctionsLabel'          => array(
            'type'        => 'string',
            'description' => 'Label for Min aggregate functions.',
        ),
        'wdtMaxFunctionsLabel'          => array(
            'type'        => 'string',
            'description' => 'Label for Max aggregate functions.',
        ),
        'wdtFontColorSettings'          => array(
            'type'                 => 'object',
            'description'          => 'Global font and color settings map (keys such as wdtTableFont, wdtFontSize, wdtFontColor).',
            'additionalProperties' => true,
        ),
        'wdtAutoUpdateOption'           => array(
            'type'        => 'integer',
            'description' => 'Auto-update table/chart cache (0 = off, 1 = on).',
        ),
        'wdtGoogleApiMaps'              => array(
            'type'        => 'string',
            'description' => 'Google Maps API key for map-type columns.',
        ),
    );
}

/**
 * JSON Schema for the settings patch object (built from {@see wdtmcp_get_updatable_global_setting_definitions()}).
 *
 * @return array<string, mixed>
 */
function wdtmcp_get_updatable_global_settings_input_schema() {
    return array(
        'type'                 => 'object',
        'description'          => 'Key-value pairs of settings to update. Omit or pass empty to read current settings without changes.',
        'properties'           => wdtmcp_get_updatable_global_setting_definitions(),
        'additionalProperties' => false,
    );
}

/**
 * Setting keys MCP clients may change. Excludes licences, Google service-account
 * blobs, separate-connection payloads, and internal version pins.
 *
 * @return string[]
 */
function wdtmcp_get_updatable_global_setting_keys() {
    return array_keys( wdtmcp_get_updatable_global_setting_definitions() );
}

/**
 * Return only MCP-readable global settings from a full plugin config payload.
 *
 * Uses {@see wdtmcp_get_updatable_global_setting_keys()} plus read-only MCP keys
 * so licence codes, tokens, separate-connection credentials, and other internal
 * keys from {@see WDTSettingsController::getCurrentPluginConfig()} are never returned.
 * The Google Maps API key value is omitted (presence is indicated via read-only
 * wdtGoogleApiMapsValidated).
 *
 * @param array $config Result of {@see WDTSettingsController::getCurrentPluginConfig()}.
 * @return array
 */
function wdtmcp_redact_plugin_config_for_mcp( array $config ) {
    $safe = array();

    foreach ( wdtmcp_get_updatable_global_setting_keys() as $key ) {
        if ( ! array_key_exists( $key, $config ) ) {
            continue;
        }

        if ( 'wdtGoogleApiMaps' === $key ) {
            continue;
        }

        $safe[ $key ] = $config[ $key ];
    }

    if ( array_key_exists( 'wdtGoogleApiMapsValidated', $config ) ) {
        $safe['wdtGoogleApiMapsValidated'] = $config['wdtGoogleApiMapsValidated'];
    }

    return $safe;
}

/**
 * Keys whose persisted values differ between two plugin config snapshots.
 *
 * @param array $before Config from {@see WDTSettingsController::getCurrentPluginConfig()} before save.
 * @param array $after  Config after save.
 * @return string[]
 */
function wdtmcp_get_changed_global_setting_keys( array $before, array $after ) {
    $changed = array();

    foreach ( wdtmcp_get_updatable_global_setting_keys() as $key ) {
        $prev = array_key_exists( $key, $before ) ? $before[ $key ] : null;
        $next = array_key_exists( $key, $after ) ? $after[ $key ] : null;

        if ( serialize( $prev ) !== serialize( $next ) ) {
            $changed[] = $key;
        }
    }

    return $changed;
}

/**
 * Execute callback for wpdatatables/update-global-settings.
 *
 * @param array $input
 * @return array|\WP_Error
 */
function wdtmcp_execute_update_global_settings( $input ) {
    if ( ! class_exists( 'WDTSettingsController' ) ) {
        return new \WP_Error(
            'wdtmcp_missing_class',
            __( 'WDTSettingsController is required.', 'wpdatatables' )
        );
    }

    $patch = array();
    if ( isset( $input['settings'] ) && is_array( $input['settings'] ) ) {
        $patch = $input['settings'];
    }

    $allowed = array_flip( wdtmcp_get_updatable_global_setting_keys() );
    $current = \WDTSettingsController::getCurrentPluginConfig();
    if ( ! is_array( $current ) ) {
        return new \WP_Error(
            'wdtmcp_config_invalid',
            __( 'Could not read current plugin configuration.', 'wpdatatables' )
        );
    }

    foreach ( array_keys( $patch ) as $key ) {
        if ( ! isset( $allowed[ $key ] ) ) {
            return new \WP_Error(
                'wdtmcp_invalid_setting_key',
                sprintf(
                    /* translators: %s: setting option key */
                    __( 'Unknown or disallowed setting key: %s', 'wpdatatables' ),
                    (string) $key
                )
            );
        }
    }

    if ( empty( $patch ) ) {
        return array(
            'current_settings' => wdtmcp_redact_plugin_config_for_mcp( $current ),
            'updated_keys'     => array(),
        );
    }

    $merged = $current;
    foreach ( $patch as $key => $value ) {
        $merged[ $key ] = $value;
    }

    \WDTSettingsController::saveSettings( $merged );

    $after = \WDTSettingsController::getCurrentPluginConfig();
    if ( ! is_array( $after ) ) {
        return new \WP_Error(
            'wdtmcp_config_invalid',
            __( 'Could not read plugin configuration after save.', 'wpdatatables' )
        );
    }

    return array(
        'current_settings' => wdtmcp_redact_plugin_config_for_mcp( $after ),
        'updated_keys'     => wdtmcp_get_changed_global_setting_keys( $current, $after ),
    );
}
