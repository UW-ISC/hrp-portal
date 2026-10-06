<?php

/**
 * HTTP transport used by the wpDataTables MCP server.
 *
 * @package wpDataTables
 */

namespace WDTMCP\Infrastructure\WP\MCP;

use WP\MCP\Transport\HttpTransport;
use WP\MCP\Transport\Infrastructure\McpTransportContext;

defined('ABSPATH') or die('Access denied.');

/**
 * Same behavior as {@see HttpTransport} from wordpress/mcp-adapter, with an extra
 * immediate registration when the server is created during {@see 'rest_api_init'}.
 *
 * Stock {@see HttpTransport} schedules {@see register_routes()} on {@see 'rest_api_init'}
 * priority 16 while {@see \WP\MCP\Core\McpAdapter::init()} runs at priority 15. Registering
 * immediately covers that first pass without dropping the deferred callback.
 *
 * The deferred callback must stay registered: some plugins (for example Angie) recreate the
 * REST server mid-request by resetting {@code $wp_rest_server}. A second {@see 'rest_api_init'}
 * then needs to re-attach routes. Removing the deferred action left MCP at
 * {@code rest_no_route} (404) while plugins that kept stock transport still worked.
 *
 * REST endpoint: /wp-json/mcp/wpdatatables-mcp-server
 */
class WdtmcpMcpHttpTransport extends HttpTransport
{
    public function __construct(McpTransportContext $transportContext)
    {
        parent::__construct($transportContext);

        // Parent already scheduled register_routes() on rest_api_init@16 — keep that hook.
        if (doing_action('rest_api_init')) {
            $this->register_routes();
        }
    }
}
