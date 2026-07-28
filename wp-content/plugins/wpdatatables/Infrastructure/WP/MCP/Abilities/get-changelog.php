<?php
/**
 * Ability: wpdatatables/get-changelog
 *
 * Returns the wpDataTables plugin changelog for the installed version:
 * release date, new features, improvements, and bug fixes.
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_get_changelog_ability' );

function wdtmcp_register_get_changelog_ability() {
    wp_register_ability(
        'wpdatatables/get-changelog',
        array(
            'label'       => __( 'Get wpDataTables Changelog', 'wpdatatables' ),
            'description' => __( 'Returns the wpDataTables plugin changelog for the installed version: current version, release date, new features, improvements, and bug fixes. Also includes a link to the full public changelog. No parameters are required — pass an empty object {}. WHEN TO CALL: when the user asks what changed in the current version, what is new, or wants release notes. RETURNS: version, release_date, features[], improvements[], bugfixes[], full_changelog_url.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(),
                'required'   => array(),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'version'             => array( 'type' => 'string', 'description' => 'Installed wpDataTables version.' ),
                    'release_date'        => array( 'type' => 'string', 'description' => 'Release date of the current version.' ),
                    'features'            => array(
                        'type'        => 'array',
                        'description' => 'New features in this version.',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'text' => array( 'type' => 'string' ),
                                'link' => array( 'type' => 'string' ),
                            ),
                        ),
                    ),
                    'improvements'        => array(
                        'type'        => 'array',
                        'description' => 'Improvements in this version.',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'text' => array( 'type' => 'string' ),
                                'link' => array( 'type' => 'string' ),
                            ),
                        ),
                    ),
                    'bugfixes'            => array(
                        'type'        => 'array',
                        'description' => 'Bug fixes in this version.',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'text' => array( 'type' => 'string' ),
                                'link' => array( 'type' => 'string' ),
                            ),
                        ),
                    ),
                    'full_changelog_url'  => array( 'type' => 'string', 'description' => 'URL to the full public changelog.' ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_get_changelog',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Call with an empty object {} to read release notes for the installed version.', 'wpdatatables' ),
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
            ),
        )
    );
}

/**
 * Normalize changelog entries from WDTTools::getUpdateInfo() into a flat list.
 *
 * @param mixed $entries Raw features/improvements/bugfixes array.
 * @return array<int, array{text: string, link: string}>
 */
function wdtmcp_normalize_changelog_entries( $entries ) {
    if ( ! is_array( $entries ) ) {
        return array();
    }

    $normalized = array();

    foreach ( $entries as $entry ) {
        if ( ! is_array( $entry ) || empty( $entry['text'] ) ) {
            continue;
        }

        $normalized[] = array(
            'text' => (string) $entry['text'],
            'link' => isset( $entry['link'] ) ? (string) $entry['link'] : '',
        );
    }

    return $normalized;
}

/**
 * Execute callback for wpdatatables/get-changelog.
 *
 * @return array|WP_Error
 */
function wdtmcp_execute_get_changelog() {
    if ( ! class_exists( 'WDTTools' ) ) {
        return new \WP_Error(
            'wdtmcp_missing_class',
            __( 'wpDataTables core class WDTTools is not available.', 'wpdatatables' )
        );
    }

    $update_info = \WDTTools::getUpdateInfo();

    if ( ! is_array( $update_info ) ) {
        return new \WP_Error(
            'wdtmcp_changelog_unavailable',
            __( 'Changelog data is not available.', 'wpdatatables' )
        );
    }

    $version = defined( 'WDT_CURRENT_VERSION' )
        ? (string) WDT_CURRENT_VERSION
        : (string) ( $update_info['version'] ?? 'unknown' );

    return array(
        'version'            => $version,
        'release_date'       => isset( $update_info['release_date'] ) ? (string) $update_info['release_date'] : '',
        'features'           => wdtmcp_normalize_changelog_entries( $update_info['features'] ?? array() ),
        'improvements'       => wdtmcp_normalize_changelog_entries( $update_info['improvements'] ?? array() ),
        'bugfixes'           => wdtmcp_normalize_changelog_entries( $update_info['bugfixes'] ?? array() ),
        'full_changelog_url' => 'https://wpdatatables.com/help/whats-new-changelog/',
    );
}
