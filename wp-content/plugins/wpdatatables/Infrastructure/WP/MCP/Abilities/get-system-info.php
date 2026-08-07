<?php
/**
 * Ability: wpdatatables/get-system-info
 *
 * Returns the wpDataTables installation environment: plugin version, active
 * edition/tier, which feature integrations are loaded (HighCharts, ApexCharts,
 * separate DB connections, SQL constructor, editing, etc.), available chart
 * engines, global settings, and the list of registered database connections.
 *
 * This is the AI's first step: understand what the installation can do before
 * attempting operations that may require a higher-tier licence.
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_get_system_info_ability' );

function wdtmcp_register_get_system_info_ability() {
    wp_register_ability(
        'wpdatatables/get-system-info',
        array(
            'label'       => __( 'Get wpDataTables System Info', 'wpdatatables' ),
            'description' => __( 'Returns the wpDataTables version, detected licence tier, enabled integrations (chart engines, separate DB connections, SQL constructor, editing, etc.), global settings, and registered database connections. Call this first to understand what capabilities are available before creating tables or charts. WHEN TO CALL: as the very first MCP call in a new session, or before any create/import/update operation — it tells you which features and licence limits apply on this site. No parameters are required — pass an empty object {}. RETURNS: version, detected_tier (free/starter/standard/pro/developer), integrations (feature flags), chart_engines[], connections[], settings, addons. USE THIS TO DECIDE: which chart engine to pass to create-chart; whether import-table-from-source or create-table-from-query is allowed; which connection IDs exist for list-db-tables / describe-db-table. AFTER LICENCE ERRORS: call wpdatatables/get-mcp-license-matrix for a structured ability × tier overview and the pricing URL. TYPICAL WORKFLOW: get-system-info → then list-tables, list-db-tables, get-mcp-license-matrix (when needed), or the appropriate create-* ability.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema'  => array(
                'type'       => 'object',
                'properties' => array(),
                'required'   => array(),
            ),
            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'version'        => array( 'type' => 'string',  'description' => 'wpDataTables plugin version.' ),
                    'detected_tier'  => array( 'type' => 'string',  'description' => 'Best-guess tier based on integration files present and an active main licence (wdtActivated). Returns free when integration files exist but the licence is not activated.' ),
                    'license_active' => array( 'type' => 'boolean', 'description' => 'Whether the main wpDataTables licence is activated on this site (Melograno Store or Envato).' ),
                    'integrations'   => array( 'type' => 'object',  'description' => 'Map of feature name => boolean indicating whether the integration is loaded.' ),
                    'chart_engines'  => array( 'type' => 'array',   'description' => 'Chart rendering engines available on this installation.', 'items' => array( 'type' => 'string' ) ),
                    'connections'    => array( 'type' => 'array',   'description' => 'Registered database connections (always includes the default WP connection).', 'items' => array( 'type' => 'object' ) ),
                    'settings'       => array( 'type' => 'object',  'description' => 'Relevant global settings (date format, number format, skin, etc.).' ),
                    'addons'         => array( 'type' => 'object',  'description' => 'Activation status of add-ons (Powerful Filters, Report Builder, Gravity, Formidable, Master-Detail).' ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_get_system_info',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Call first in a new session with an empty object {}. Check detected_tier, integrations, chart_engines, and connections before any create or update ability.', 'wpdatatables' ),
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
            ),
        )
    );
}

/**
 * Execute callback for wpdatatables/get-system-info.
 *
 * @return array|WP_Error
 */
function wdtmcp_execute_get_system_info() {

    // ── Version ──────────────────────────────────────────────────
    $version = defined( 'WDT_CURRENT_VERSION' ) ? WDT_CURRENT_VERSION : 'unknown';

    // ── Integration detection ────────────────────────────────────
    // Some integration constants are only defined inside is_admin()
    // (e.g. Google Sheet API, SQL Constructor, Folders), so they are
    // unavailable during REST/MCP requests. We use is_file() against the
    // entry-point files — the same approach wpDataTables' own loader uses.
    $integrations = wdtmcp_detect_integrations();

    // ── Tier heuristic ───────────────────────────────────────────
    $detected_tier = wdtmcp_detect_tier( $integrations );

    // ── Chart engines ────────────────────────────────────────────
    $chart_engines = array( 'google', 'chartjs' );
    if ( $integrations['highcharts'] )  { $chart_engines[] = 'highcharts'; }
    if ( $integrations['apexcharts'] )  { $chart_engines[] = 'apexcharts'; }
    if ( $integrations['highstock'] )   { $chart_engines[] = 'highstock'; }

    // ── Database connections ─────────────────────────────────────
    $connections = wdtmcp_get_connections( $integrations['separate_db'] );

    // ── Global settings (safe subset) ────────────────────────────
    $settings = wdtmcp_get_safe_settings();

    // ── Add-on activation ────────────────────────────────────────
    $addons = array(
        'powerful_filters' => (bool) get_option( 'wdtActivatedPowerful' ),
        'report_builder'   => (bool) get_option( 'wdtActivatedReport' ),
        'gravity_forms'    => (bool) get_option( 'wdtActivatedGravity' ),
        'formidable_forms' => (bool) get_option( 'wdtActivatedFormidable' ),
        'master_detail'    => (bool) get_option( 'wdtActivatedMasterDetail' ),
    );

    return array(
        'version'        => $version,
        'detected_tier'  => $detected_tier,
        'license_active' => wdtmcp_is_main_license_active(),
        'integrations'   => $integrations,
        'chart_engines' => $chart_engines,
        'connections'   => $connections,
        'settings'      => $settings,
        'addons'        => $addons,
    );
}

/**
 * Detect available integrations by checking whether the integration
 * entry-point files exist on disk.
 *
 * We cannot rely on defined() for WDT_*_INTEGRATION constants because
 * several are loaded only inside is_admin() — which is false during
 * REST/MCP requests.
 *
 * @return array Feature name => boolean map.
 */
function wdtmcp_detect_integrations() {
    $starter  = defined( 'WDT_STARTER_INTEGRATIONS_PATH' )  ? WDT_STARTER_INTEGRATIONS_PATH  : '';
    $standard = defined( 'WDT_STANDARD_INTEGRATIONS_PATH' ) ? WDT_STANDARD_INTEGRATIONS_PATH : '';
    $pro      = defined( 'WDT_PRO_INTEGRATIONS_PATH' )      ? WDT_PRO_INTEGRATIONS_PATH      : '';

    // feature => relative path from the tier integrations directory.
    $file_map = array(
        'google_sheet_api'    => array( $starter,  'google-sheet-api/wdt-google-sheet-api-integration.php' ),
        'separate_db'         => array( $standard, 'separate-db-connection/wdt-separate-connection-integration.php' ),
        'sql_constructor'     => array( $standard, 'sql-constructor/wdt-sql-constructor-integration.php' ),
        'sql_query'           => array( $standard, 'sql-query/wdt-sql-query-integration.php' ),
        'highcharts'          => array( $standard, 'highcharts/wdt-highcharts-integration.php' ),
        'apexcharts'          => array( $standard, 'apexcharts/wdt-apexcharts-integration.php' ),
        'editing'             => array( $standard, 'editing/wdt-editing-integration.php' ),
        'placeholders'        => array( $standard, 'placeholders/wdt-placeholders-integration.php' ),
        'foreign_key'         => array( $standard, 'foreign-key/wdt-foreign-key-integration.php' ),
        'formula_column'      => array( $standard, 'formula-column/wdt-formula-column-integration.php' ),
        'hidden_column'       => array( $standard, 'hidden-column/wdt-hidden-column-integration.php' ),
        'fixed_col_header'    => array( $standard, 'fixed-columns-and-headers/wdt-fixed-ch-integration.php' ),
        'index_column'        => array( $standard, 'index-column/wdt-index-column-integration.php' ),
        'update_manual_file'  => array( $standard, 'update-manual-from-file/wdt-update-manual-from-file-integration.php' ),
        'highstock'           => array( $pro,      'highstock/wdt-highstock-integration.php' ),
        'folders'             => array( $pro,      'folders/source/class.wpdatafolders.php' ),
        'wp_posts_builder'    => array( $pro,      'query-builder/source/wdt-query-builder.php' ),
        'woocommerce'         => array( $pro,      'woo-commerce/source/wdt-woo-commerce.php' ),
    );

    $integrations = array();
    foreach ( $file_map as $feature => $parts ) {
        $base = $parts[0];
        $file = $parts[1];
        $integrations[ $feature ] = ( '' !== $base && is_file( $base . $file ) );
    }

    return $integrations;
}

/**
 * Whether the main wpDataTables licence is activated on this site.
 *
 * Matches the plugin's own activation flag (Melograno Store purchase code or
 * Envato OAuth). Integration files on disk alone do not count as a valid licence.
 *
 * @return bool
 */
function wdtmcp_is_main_license_active() {
    return (bool) get_option( 'wdtActivated' );
}

/**
 * Detect the wpDataTables tier from loaded integration constants
 *
 * Ignores licence activation — use this when the installed package/SKU matters
 * (e.g. usage telemetry). The tiers are cumulative — Developer ⊃ Pro ⊃ Standard
 * ⊃ Starter ⊃ Free. Developer has identical features to Pro but with unlimited
 * domain licensing; it is identified by developer_license.txt in the developer
 * integrations directory.
 *
 * @param array $integrations Feature => boolean map from {@see wdtmcp_detect_integrations()}.
 * @return string One of: developer, pro, standard, starter, free.
 */
function wdtmcp_detect_tier_from_features( array $integrations ) {
    $has_pro_feature = false;
    $pro_features    = array( 'highstock', 'folders', 'wp_posts_builder', 'woocommerce' );
    foreach ( $pro_features as $f ) {
        if ( ! empty( $integrations[ $f ] ) ) {
            $has_pro_feature = true;
            break;
        }
    }

    if ( $has_pro_feature ) {
        if ( defined( 'WDT_DEVELOPER_INTEGRATIONS_PATH' )
             && is_file( WDT_DEVELOPER_INTEGRATIONS_PATH . 'developer_license.txt' ) ) {
            return 'developer';
        }
        return 'pro';
    }

    $standard_features = array( 'separate_db', 'sql_constructor', 'highcharts', 'apexcharts', 'editing', 'placeholders', 'foreign_key' );
    foreach ( $standard_features as $f ) {
        if ( ! empty( $integrations[ $f ] ) ) {
            return 'standard';
        }
    }

    if ( ! empty( $integrations['google_sheet_api'] ) ) {
        return 'starter';
    }

    return 'free';
}

/**
 * Detect the wpDataTables tier for MCP / system-info consumers.
 *
 * Paid tiers require an activated main licence; copied integration files without
 * activation are reported as free. For file-only detection (no activation gate),
 * use {@see wdtmcp_detect_tier_from_features()}.
 *
 * @param array $integrations Feature => boolean map.
 * @return string One of: developer, pro, standard, starter, free.
 */
function wdtmcp_detect_tier( array $integrations ) {
    if ( ! wdtmcp_is_main_license_active() ) {
        return 'free';
    }

    return wdtmcp_detect_tier_from_features( $integrations );
}

/**
 * Build the list of database connections.
 *
 * Always includes the default WordPress connection. If separate DB
 * connections are enabled, appends each registered connection
 * (without exposing credentials).
 *
 * @param bool $separate_db_enabled Whether the separate DB integration is loaded.
 * @return array
 */
function wdtmcp_get_connections( $separate_db_enabled ) {
    global $wpdb;

    $connections = array(
        array(
            'id'       => '',
            'name'     => 'WordPress (default)',
            'vendor'   => 'mysql',
            'database' => $wpdb->dbname,
        ),
    );

    if ( $separate_db_enabled && class_exists( 'Connection' ) ) {
        $all = \Connection::getAll();
        if ( is_array( $all ) ) {
            foreach ( $all as $conn ) {
                $connections[] = array(
                    'id'       => isset( $conn['id'] )       ? (string) $conn['id']       : '',
                    'name'     => isset( $conn['name'] )     ? (string) $conn['name']     : '',
                    'vendor'   => isset( $conn['vendor'] )   ? (string) $conn['vendor']   : '',
                    'database' => isset( $conn['database'] ) ? (string) $conn['database'] : '',
                );
            }
        }
    }

    return $connections;
}

/**
 * Return a safe subset of global plugin settings.
 *
 * Excludes sensitive keys (purchase codes, tokens, passwords, custom JS).
 *
 * @return array
 */
function wdtmcp_get_safe_settings() {
    $safe_keys = array(
        'wdtDateFormat',
        'wdtTimeFormat',
        'wdtBaseSkin',
        'wdtNumberFormat',
        'wdtDecimalPlaces',
        'wdtCSVDelimiter',
        'wdtTablesPerPage',
        'wdtSortingOrderBrowseTables',
        'wdtTabletWidth',
        'wdtMobileWidth',
        'wdtIncludeBootstrap',
        'wdtPreventDeletingTables',
        'wdtParseShortcodes',
        'wdtNumbersAlign',
        'wdtBorderRemoval',
        'wdtBorderRemovalHeader',
        'wdtRenderFilter',
        'wdtInterfaceLanguage',
        'wdtSumFunctionsLabel',
        'wdtAvgFunctionsLabel',
        'wdtMinFunctionsLabel',
        'wdtMaxFunctionsLabel',
    );

    $settings = array();
    foreach ( $safe_keys as $key ) {
        $val = get_option( $key );
        $settings[ $key ] = ( false === $val ) ? null : $val;
    }

    $font_color = get_option( 'wdtFontColorSettings' );
    if ( $font_color && is_array( $font_color ) ) {
        $settings['wdtFontColorSettings'] = $font_color;
    }

    return $settings;
}
