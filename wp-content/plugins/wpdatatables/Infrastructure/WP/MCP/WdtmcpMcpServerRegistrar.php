<?php

/**
 * Registers the wpDataTables MCP server.
 *
 * @package wpDataTables
 */

namespace WDTMCP\Infrastructure\WP\MCP;

defined('ABSPATH') or die('Access denied.');

class WdtmcpMcpServerRegistrar
{
    private const ABILITY_IDS = array(
        'wpdatatables/create-table-from-source',
        'wpdatatables/import-table-from-source',
        'wpdatatables/update-table-settings',
        'wpdatatables/edit-table',
        'wpdatatables/list-media',
        'wpdatatables/list-tables',
        'wpdatatables/get-table-info',
        'wpdatatables/get-table-data',
        'wpdatatables/list-charts',
        'wpdatatables/get-chart-info',
        'wpdatatables/get-system-info',
        'wpdatatables/get-mcp-license-matrix',
        'wpdatatables/get-changelog',
        'wpdatatables/list-db-tables',
        'wpdatatables/describe-db-table',
        'wpdatatables/create-simple-table',
        'wpdatatables/create-table-from-query',
        'wpdatatables/update-simple-table-styles',
        'wpdatatables/upload-media-from-url',
        'wpdatatables/upload-data-file',
        'wpdatatables/update-global-settings',
        'wpdatatables/create-chart',
        'wpdatatables/edit-chart',
    );

    /**
     * @param mixed $adapter The adapter instance.
     */
    public static function init($adapter): void
    {
        $result = $adapter->create_server(
            'wpdatatables-mcp-server',
            'mcp',
            'wpdatatables-mcp-server',
            'wpDataTables MCP Server',
            'Exposes wpDataTables functionality as MCP tools for data discovery, table creation, configuration, media, and charts.',
            defined('WDT_CURRENT_VERSION') ? 'v' . WDT_CURRENT_VERSION : '1.0.0',
            array(WdtmcpMcpHttpTransport::class),
            \WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class,
            \WP\MCP\Infrastructure\Observability\NullMcpObservabilityHandler::class,
            self::ABILITY_IDS,
            array(),
            array('WDTMCP\Infrastructure\WP\MCP\Prompts\FileUploadWorkflowPrompt'),
            static function () {
                if (is_user_logged_in()) {
                    return current_user_can('manage_options');
                }

                $username = (string) (filter_input(INPUT_SERVER, 'PHP_AUTH_USER', FILTER_UNSAFE_RAW) ?? '');
                $password = (string) (filter_input(INPUT_SERVER, 'PHP_AUTH_PW', FILTER_UNSAFE_RAW) ?? '');
                $username = $username !== '' ? wp_unslash($username) : '';
                $password = $password !== '' ? wp_unslash($password) : '';

                if (! $username || ! $password || ! function_exists('wp_authenticate_application_password')) {
                    return false;
                }

                $user = wp_authenticate_application_password(null, $username, $password);

                return $user instanceof \WP_User && user_can($user, 'manage_options');
            }
        );

        if ($result instanceof \WP_Error) {
            $error_message = $result->get_error_message();
            error_log('wpDataTables MCP: create_server failed - ' . $error_message);

            // Also surface the failure to the current admin so it's not only in the PHP debug log.
            if (is_admin() && current_user_can('manage_options')) {
                add_action(
                    'admin_notices',
                    static function () use ($error_message): void {
                        echo '<div class="notice notice-error"><p>'
                            . esc_html('wpDataTables MCP: create_server failed - ' . $error_message)
                            . '</p></div>';
                    }
                );
            } else {
                // If we're not in wp-admin, at least let WP_DEBUG_DISPLAY show something useful.
                trigger_error('wpDataTables MCP: create_server failed - ' . $error_message, E_USER_WARNING);
            }
        }
    }
}
