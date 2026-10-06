<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Routes\Ai;

use WPDataTables\Common\Rest\RestPermissions;
use WPDataTables\Controllers\Rest\Ai\GenerateTableController;
use WPDataTables\Controllers\Rest\Ai\QueryAssistantController;
use WPDataTables\Controllers\Rest\Ai\QueryConstructorController;
use WPDataTables\Controllers\Rest\Ai\SuggestChartController;
use WPDataTables\Vendor\DI\Container;
use WP_REST_Server;

/**
 * AI REST route group.
 *
 * Unlike {@see \WPDataTables\Routes\Routes} (the Developer-tier-gated table API),
 * this group is registered on an **ungated** `rest_api_init` by
 * {@see \WPDataTables\Plugin\AiHooks} — the AI features are admin-only but ship
 * on every licence tier. Pure WP wiring: each route resolves its controller from
 * the container and gates on the AI capability.
 *
 * @package WPDataTables\Routes\Ai
 */
class Ai
{
    /**
     * @param Container $container
     * @param string    $routeNamespace
     * @return void
     */
    public static function registerRoutes(Container $container, string $routeNamespace)
    {
        $permission = static function () {
            return RestPermissions::canUseAi();
        };

        // POST /ai/generate-table — Feature 1, AI Table Generator.
        register_rest_route($routeNamespace, '/ai/generate-table', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => $container->get(GenerateTableController::class),
            'permission_callback' => $permission,
            'args'                => [
                'description'  => [
                    'type'              => 'string',
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
                'table_type'   => [
                    'type'              => 'string',
                    'default'           => 'manual',
                    'enum'              => ['manual', 'sql'],
                    'sanitize_callback' => 'sanitize_key',
                ],
                'connection'   => [
                    'type'              => 'string',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'model'        => [
                    'type'              => 'string',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                // Legacy — mapped in the controller for one release.
                'data_source'  => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'row_estimate' => [
                    'type'              => 'integer',
                    'default'           => 0,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Feature 2 temporarily disabled — re-enable with constructor_1_3 / constructor_1_4 hooks.
        // POST /ai/query-constructor — Feature 2, Query Constructor Assistant.
        // register_rest_route($routeNamespace, '/ai/query-constructor', [
        //     'methods'             => WP_REST_Server::CREATABLE,
        //     'callback'            => $container->get(QueryConstructorController::class),
        //     'permission_callback' => $permission,
        //     'args'                => [
        //         'builder_type' => [
        //             'type'              => 'string',
        //             'required'          => true,
        //             'enum'              => ['wp', 'mysql'],
        //             'sanitize_callback' => 'sanitize_key',
        //         ],
        //         'description'  => [
        //             'type'              => 'string',
        //             'required'          => true,
        //             'sanitize_callback' => 'sanitize_textarea_field',
        //         ],
        //         'connection'   => [
        //             'type'              => 'string',
        //             'default'           => '',
        //             'sanitize_callback' => 'sanitize_text_field',
        //         ],
        //         'model'        => [
        //             'type'              => 'string',
        //             'default'           => '',
        //             'sanitize_callback' => 'sanitize_text_field',
        //         ],
        //     ],
        // ]);

        // POST /ai/suggest-chart — Chart Type Suggester.
        register_rest_route($routeNamespace, '/ai/suggest-chart', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => $container->get(SuggestChartController::class),
            'permission_callback' => $permission,
            'args'                => [
                'table_id'    => [
                    'type'              => 'integer',
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                ],
                'description'       => [
                    'type'              => 'string',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
                'available_engines' => [
                    'type'    => 'array',
                    'default' => [],
                    'items'   => [
                        'type' => 'string',
                    ],
                ],
                'model'             => [
                    'type'              => 'string',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // POST /ai/query-assistant — SQL Query Assistant (generate / fix / improve / explain).
        register_rest_route($routeNamespace, '/ai/query-assistant', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => $container->get(QueryAssistantController::class),
            'permission_callback' => $permission,
            'args'                => [
                'mode'          => [
                    'type'              => 'string',
                    'required'          => true,
                    'enum'              => ['generate', 'fix', 'improve', 'explain'],
                    'sanitize_callback' => 'sanitize_key',
                ],
                'prompt'        => [
                    'type'              => 'string',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
                'current_query' => [
                    'type'              => 'string',
                    'default'           => '',
                    // Keep SQL characters (e.g. <, >); only enforce UTF-8.
                    'sanitize_callback' => static function ($value) {
                        return is_string($value) ? wp_check_invalid_utf8(wp_unslash($value), true) : '';
                    },
                ],
                'error_message' => [
                    'type'              => 'string',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
                'connection'    => [
                    'type'              => 'string',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'model'         => [
                    'type'              => 'string',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        /**
         * Allow integrations / add-ons to register additional AI REST routes.
         *
         * @since 7.x
         * @param Container $container The wpDataTables DI container.
         * @param string    $namespace The current REST namespace.
         */
        do_action('wpdatatables/rest/register_additional_ai_routes', $container, $routeNamespace);
    }
}
