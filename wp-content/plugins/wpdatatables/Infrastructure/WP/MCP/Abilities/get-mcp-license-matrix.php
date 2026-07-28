<?php
/**
 * Ability: wpdatatables/get-mcp-license-matrix
 *
 * Read-only matrix of MCP abilities vs integration files / tier hints for this site.
 *
 * @package wpDataTables_MCP_Server
 */

defined('ABSPATH') || exit;

add_action('wp_abilities_api_init', 'wdtmcp_register_get_mcp_license_matrix_ability');

function wdtmcp_register_get_mcp_license_matrix_ability()
{
    wp_register_ability(
        'wpdatatables/get-mcp-license-matrix',
        array(
            'label' => __('Get MCP Licence Matrix', 'wpdatatables'),
            'description' => __(
                'Read-only map of wpDataTables MCP abilities vs this installation: which integration files are missing, tier hints, pricing URL, and whether each ability is fully supported here. WHEN TO CALL: after a wdtmcp_tier_required / integration_missing error, or before planning upgrades. WORKFLOW: get-system-info → get-mcp-license-matrix. PARAMETERS: none (pass {}). RETURNS: pricing_url, detected_tier, integrations map, supports_non_wp_db_connections, abilities[].',
                'wpdatatables'
            ),
            'category' => 'wpdatatables-data',

            'input_schema' => array(
                'type' => 'object',
                'properties' => array(),
            ),

            'output_schema' => array(
                'type' => 'object',
                'properties' => array(
                    'pricing_url' => array('type' => 'string'),
                    'detected_tier' => array('type' => 'string'),
                    'tier_order' => array(
                        'type' => 'array',
                        'items' => array('type' => 'string'),
                    ),
                    'upgrade_hint' => array('type' => 'string'),
                    'integrations' => array('type' => 'object'),
                    'supports_non_wp_db_connections' => array('type' => 'boolean'),
                    'abilities' => array('type' => 'array'),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_get_mcp_license_matrix',

            'permission_callback' => function () {
                return current_user_can('manage_options');
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __('Pass {}. Use when diagnosing licence/integration limits on MCP abilities.', 'wpdatatables'),
                    'readonly' => true,
                    'destructive' => false,
                    'idempotent' => true,
                ),
            ),
        )
    );
}

/**
 * Execute wpdatatables/get-mcp-license-matrix.
 *
 * @param array $input
 * @return array|\WP_Error
 */
function wdtmcp_execute_get_mcp_license_matrix($input)
{
    if (! function_exists('wdtmcp_detect_integrations') || ! function_exists('wdtmcp_detect_tier')) {
        return new \WP_Error(
            'wdtmcp_missing_helper',
            __('System integration detector is not loaded; ensure get-system-info ability is registered.', 'wpdatatables')
        );
    }

    if (! class_exists('\WDTMCP\Helpers\McpHelpers\WdtmcpLicenseGuideHelper')) {
        return new \WP_Error(
            'wdtmcp_missing_helper',
            __('Licence guide helper is not loaded.', 'wpdatatables')
        );
    }

    $integrations = wdtmcp_detect_integrations();
    $tier = wdtmcp_detect_tier($integrations);

    return \WDTMCP\Helpers\McpHelpers\WdtmcpLicenseGuideHelper::buildLicenseMatrixPayload(
        $integrations,
        $tier
    );
}
