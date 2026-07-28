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
 * Registers REST routes immediately, matching the timing workaround used by IvyForms.
 */
class WdtmcpMcpHttpTransport extends HttpTransport
{
    public function __construct(McpTransportContext $transportContext)
    {
        parent::__construct($transportContext);

        remove_action('rest_api_init', array($this, 'register_routes'), 16);
        $this->register_routes();
    }
}
