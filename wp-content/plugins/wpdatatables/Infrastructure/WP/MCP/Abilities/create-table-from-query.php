<?php
/**
 * Ability: wpdatatables/create-table-from-query
 *
 * Creates a new SQL-based wpDataTable from a SELECT query. The AI can compose
 * the query after exploring schemas via list-db-tables / describe-db-table,
 * then pass it here to create a persistent wpDataTable.
 *
 * Requires: Standard tier or higher for SQL query tables + separate connections.
 *           Free tier supports MySQL query tables on the default WP connection
 *           (with class.constructor.php present, i.e. Full version).
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_create_table_from_query_ability' );

function wdtmcp_register_create_table_from_query_ability() {
    wp_register_ability(
        'wpdatatables/create-table-from-query',
        array(
            'label'       => __( 'Create Table from SQL Query', 'wpdatatables' ),
            'description' => __( 'Creates a DataTable from a SQL SELECT query. Use when: data comes from the database (MySQL, MSSQL, PostgreSQL). Provides sorting, filtering, server-side processing. Do not use for external URLs or static HTML. First use list-db-tables and describe-db-table to inspect schemas. Only SELECT queries accepted; INSERT/UPDATE/DELETE/DROP rejected. FROM must reference a simple table (no subqueries or derived tables). WHEN TO CALL: user wants a wpDataTable backed by database data with sorting/filtering. DO NOT USE for: files, APIs, or static HTML. CHECK get-system-info for tier and connection support. REQUIRED INPUT: title (string), query (SELECT only, e.g. SELECT * FROM wp_posts). OPTIONAL: connection (empty = WordPress DB), server_side (boolean, default true for large tables). TYPICAL WORKFLOW: get-system-info → list-db-tables → describe-db-table → create-table-from-query. RETURNS: table_id and shortcode. NEXT STEPS: get-table-info, update-table-settings, get-table-data (use search/column_search if server_side=true).', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'title' => array(
                        'type'        => 'string',
                        'description' => 'Table title.',
                    ),
                    'query' => array(
                        'type'        => 'string',
                        'description' => 'A valid SELECT SQL query. FROM must reference a simple table (e.g. SELECT * FROM table_name), not a subquery.',
                    ),
                    'connection' => array(
                        'type'        => 'string',
                        'description' => 'Connection ID (empty string for default WP database).',
                    ),
                    'server_side' => array(
                        'type'        => 'boolean',
                        'description' => 'Enable server-side processing (recommended for >1000 rows). Defaults to true.',
                    ),
                ),
                'required' => array( 'title', 'query' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_id'   => array( 'type' => 'integer', 'description' => 'Created table ID.' ),
                    'shortcode'  => array( 'type' => 'string',  'description' => 'WordPress shortcode.' ),
                    'column_count' => array( 'type' => 'integer', 'description' => 'Number of columns detected.' ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_create_table_from_query',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'SELECT queries only. Use list-db-tables and describe-db-table first. Set server_side=true for tables with more than ~1000 rows.', 'wpdatatables' ),
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => false,
                ),
            ),
        )
    );
}

/**
 * Root-level parser keys that are never allowed in MCP SELECT queries.
 *
 * @return string[]
 */
function wdtmcp_sql_forbidden_root_keys() {
    return array(
        'DELETE',
        'UPDATE',
        'INSERT',
        'DROP',
        'TRUNCATE',
        'CREATE',
        'ALTER',
        'REPLACE',
        'RENAME',
        'CALL',
        'EXEC',
        'EXECUTE',
        'EXPLAIN',
        'DESCRIBE',
        'DESC',
        'SHOW',
        'SET',
        'GRANT',
        'REVOKE',
        'LOCK',
        'UNLOCK',
        'LOAD',
        'HANDLER',
        'PREPARE',
        'DEALLOCATE',
        'INTO',
    );
}

/**
 * Parse SQL for MCP validation.
 *
 * @param string $query SQL query.
 * @return array|WP_Error Parsed statement structure.
 */
function wdtmcp_parse_sql_query( $query ) {
    if ( ! class_exists( 'WPDT\PHPSQLParser\PHPSQLParser' ) ) {
        return new \WP_Error(
            'wdtmcp_parser_unavailable',
            __( 'SQL parser is not available; cannot validate the query.', 'wpdatatables' )
        );
    }

    try {
        $parser = new \WPDT\PHPSQLParser\PHPSQLParser( false, false );

        return $parser->parse( (string) $query, false );
    } catch ( \Exception $e ) {
        return new \WP_Error(
            'wdtmcp_invalid_query',
            sprintf(
                /* translators: %s: Parser error message */
                __( 'Query could not be parsed: %s', 'wpdatatables' ),
                $e->getMessage()
            )
        );
    }
}

/**
 * Validate a parsed SQL structure is a single SELECT (no UNION, no DML/DDL roots).
 *
 * @param array $parsed Output from wdtmcp_parse_sql_query().
 * @return bool|WP_Error True if valid, WP_Error if invalid.
 */
function wdtmcp_validate_parsed_select_statement( array $parsed ) {
    $forbidden = wdtmcp_sql_forbidden_root_keys();

    foreach ( array_keys( $parsed ) as $key ) {
        $upper = strtoupper( (string) $key );

        if ( 0 === strpos( $upper, 'UNION' ) ) {
            return new \WP_Error(
                'wdtmcp_invalid_query',
                __( 'UNION queries are not permitted. Use a single SELECT statement.', 'wpdatatables' )
            );
        }

        if ( in_array( $upper, $forbidden, true ) ) {
            return new \WP_Error(
                'wdtmcp_invalid_query',
                __( 'Only SELECT queries are allowed. INSERT, UPDATE, DELETE, DROP, TRUNCATE, CREATE, ALTER, EXPLAIN, DESCRIBE, and UNION are not permitted.', 'wpdatatables' )
            );
        }
    }

    if ( empty( $parsed['SELECT'] ) ) {
        return new \WP_Error(
            'wdtmcp_invalid_query',
            __( 'Query must be a SELECT statement.', 'wpdatatables' )
        );
    }

    return true;
}

/**
 * Reject stacked statements (semicolon followed by another SQL keyword).
 *
 * @param string $query SQL query.
 * @return bool|WP_Error True if valid, WP_Error if invalid.
 */
function wdtmcp_validate_no_stacked_sql( $query ) {
    if ( preg_match(
        '/;\s*(select|insert|update|delete|drop|create|alter|truncate|replace|call|show|explain|desc|describe|grant|revoke|set|use|lock|unlock|from)\b/i',
        $query
    ) ) {
        return new \WP_Error(
            'wdtmcp_invalid_query',
            __( 'Only a single SELECT statement is allowed. Multiple statements separated by semicolons are not permitted.', 'wpdatatables' )
        );
    }

    return true;
}

/**
 * Substring fallback when PHPSQLParser is unavailable.
 *
 * @param string $query SQL query.
 * @return bool|WP_Error True if valid, WP_Error if invalid.
 */
function wdtmcp_validate_select_only_query_fallback( $query ) {
    $lower = strtolower( trim( $query ) );

    if ( strpos( $lower, 'select' ) !== 0 && ! preg_match( '/^\/\*.*?\*\/\s*select/s', $lower ) ) {
        return new \WP_Error(
            'wdtmcp_invalid_query',
            __( 'Query must be a SELECT statement.', 'wpdatatables' )
        );
    }

    $forbidden = array(
        '; delete',
        '; update',
        '; insert',
        '; drop',
        '; truncate',
        '; create',
        '; alter',
        ' into outfile',
        ' into dumpfile',
        ' union ',
        ' union all ',
    );
    foreach ( $forbidden as $word ) {
        if ( strpos( $lower, $word ) !== false ) {
            return new \WP_Error(
                'wdtmcp_invalid_query',
                __( 'Only SELECT queries are allowed. INSERT, UPDATE, DELETE, DROP, TRUNCATE, CREATE, ALTER, EXPLAIN, DESCRIBE, and UNION are not permitted.', 'wpdatatables' )
            );
        }
    }

    if ( strpos( $lower, 'select' ) === false ) {
        return new \WP_Error(
            'wdtmcp_invalid_query',
            __( 'Query must be a SELECT statement.', 'wpdatatables' )
        );
    }

    return true;
}

/**
 * Validate that the query is SELECT-only. Rejects INSERT, UPDATE, DELETE, DROP, UNION, stacked queries, etc.
 *
 * Uses PHPSQLParser when available; falls back to substring checks otherwise.
 *
 * @param string $query SQL query.
 * @return bool|WP_Error True if valid, WP_Error if invalid.
 */
function wdtmcp_validate_select_only_query( $query ) {
    $query = trim( (string) $query );

    if ( '' === $query ) {
        return new \WP_Error( 'wdtmcp_missing_param', __( 'query is required.', 'wpdatatables' ) );
    }

    $stacked = wdtmcp_validate_no_stacked_sql( $query );
    if ( is_wp_error( $stacked ) ) {
        return $stacked;
    }

    if ( ! class_exists( 'WPDT\PHPSQLParser\PHPSQLParser' ) ) {
        return wdtmcp_validate_select_only_query_fallback( $query );
    }

    $parsed = wdtmcp_parse_sql_query( $query );
    if ( is_wp_error( $parsed ) ) {
        return $parsed;
    }

    if ( ! is_array( $parsed ) ) {
        return new \WP_Error(
            'wdtmcp_invalid_query',
            __( 'Query could not be parsed.', 'wpdatatables' )
        );
    }

    return wdtmcp_validate_parsed_select_statement( $parsed );
}

/**
 * Validate that the query has a simple table reference in FROM (no subqueries).
 * wpDataTables expects $parsedQuery['FROM'][0]['table'] to exist; subqueries
 * cause "Undefined array key 'table'" when opening the table.
 *
 * @param string     $query  Sanitized SQL query.
 * @param array|null $parsed Optional pre-parsed structure from wdtmcp_parse_sql_query().
 * @return bool|WP_Error True if valid, WP_Error if invalid.
 */
function wdtmcp_validate_from_has_table( $query, $parsed = null ) {
    if ( null === $parsed ) {
        if ( ! class_exists( 'WPDT\PHPSQLParser\PHPSQLParser' ) ) {
            return true;
        }

        $parsed = wdtmcp_parse_sql_query( $query );
        if ( is_wp_error( $parsed ) ) {
            return $parsed;
        }
    }

    if ( ! is_array( $parsed ) ) {
        return new \WP_Error(
            'wdtmcp_invalid_query',
            __( 'Query could not be parsed.', 'wpdatatables' )
        );
    }

    if ( empty( $parsed['FROM'] ) || ! isset( $parsed['FROM'][0] ) ) {
        return new \WP_Error(
            'wdtmcp_invalid_query',
            __( 'Query must have a FROM clause with a table reference.', 'wpdatatables' )
        );
    }
    if ( ! isset( $parsed['FROM'][0]['table'] ) ) {
        return new \WP_Error(
            'wdtmcp_invalid_query',
            __( 'wpDataTables does not support subqueries or derived tables in FROM. Use a simple table reference, e.g. SELECT * FROM table_name.', 'wpdatatables' )
        );
    }

    return true;
}

/**
 * Execute callback for wpdatatables/create-table-from-query.
 *
 * @param array $input
 * @return array|WP_Error
 */
function wdtmcp_execute_create_table_from_query( $input ) {
    global $wpdb;

    if ( ! class_exists( 'WDTConfigController' ) || ! class_exists( 'WPDataTable' ) ) {
        return new \WP_Error(
            'wdtmcp_missing_class',
            __( 'wpDataTables core classes are required.', 'wpdatatables' )
        );
    }

    $title      = isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : '';
    $query      = isset( $input['query'] ) ? trim( (string) $input['query'] ) : '';
    $connection = isset( $input['connection'] ) ? sanitize_text_field( $input['connection'] ) : '';
    $server_side = isset( $input['server_side'] ) ? (bool) $input['server_side'] : true;

    $conn_check = wdtmcp_assert_separate_db_connection_allowed( $connection, 'wpdatatables/create-table-from-query' );
    if ( is_wp_error( $conn_check ) ) {
        return $conn_check;
    }

    if ( '' === $title ) {
        return new \WP_Error( 'wdtmcp_missing_param', __( 'title is required.', 'wpdatatables' ) );
    }
    if ( '' === $query ) {
        return new \WP_Error( 'wdtmcp_missing_param', __( 'query is required.', 'wpdatatables' ) );
    }

    $valid = wdtmcp_validate_select_only_query( $query );
    if ( is_wp_error( $valid ) ) {
        return $valid;
    }

    if ( ! function_exists( 'wdtSanitizeQuery' ) ) {
        return new \WP_Error( 'wdtmcp_missing_function', __( 'wpDataTables wdtSanitizeQuery is required.', 'wpdatatables' ) );
    }

    $query = wdtSanitizeQuery( $query );

    $stacked = wdtmcp_validate_no_stacked_sql( $query );
    if ( is_wp_error( $stacked ) ) {
        return $stacked;
    }

    if ( class_exists( 'WPDT\PHPSQLParser\PHPSQLParser' ) ) {
        $parsed = wdtmcp_parse_sql_query( $query );
        if ( is_wp_error( $parsed ) ) {
            return $parsed;
        }

        $valid = wdtmcp_validate_parsed_select_statement( $parsed );
        if ( is_wp_error( $valid ) ) {
            return $valid;
        }

        $from_valid = wdtmcp_validate_from_has_table( $query, $parsed );
    } else {
        $valid = wdtmcp_validate_select_only_query_fallback( $query );
        if ( is_wp_error( $valid ) ) {
            return $valid;
        }

        $from_valid = wdtmcp_validate_from_has_table( $query );
    }

    if ( is_wp_error( $from_valid ) ) {
        return $from_valid;
    }

    $res = WDTConfigController::tryCreateTable( 'mysql', $query, $connection );

    if ( ! empty( $res->error ) ) {
        return new \WP_Error( 'wdtmcp_query_error', $res->error );
    }

    $table_array = array(
        'title' => $title,
        'table_type' => 'mysql',
        'connection' => $connection,
        'content' => $query,
        'filtering' => 1,
        'filtering_form' => 0,
        'cache_source_data' => 0,
        'auto_update_cache' => 0,
        'sorting' => 1,
        'fixed_layout' => 0,
        'responsive' => 0,
        'word_wrap' => 1,
        'tools' => 1,
        'display_length' => 10,
        'fixed_columns' => 0,
        'server_side' => $server_side ? 1 : 0,
        'editable' => 0,
        'editor_roles' => '',
        'mysql_table_name' => '',
        'hide_before_load' => 1,
        'tabletools_config' => serialize( array(
            'print' => 1,
            'copy' => 1,
            'excel' => 1,
            'csv' => 1,
            'pdf' => 0,
        ) ),
        'advanced_settings' => json_encode( array(
            'show_table_description' => false,
            'table_description' => '',
            'fixed_columns' => false,
            'fixed_left_columns_number' => 0,
            'fixed_right_columns_number' => 0,
            'fixed_header' => false,
            'index_column' => 0,
        ) ),
    );

    $wpdb->insert( $wpdb->prefix . 'wpdatatables', $table_array);
    $table_id = $wpdb->insert_id;

    if ( $table_id <= 0 ) {
        return new \WP_Error( 'wdtmcp_db_error', $wpdb->last_error ?: __( 'Failed to create table record.', 'wpdatatables' ) );
    }

    try {
        WDTConfigController::saveColumns( null, $res->table, $table_id );
    } catch ( Exception $e ) {
        $wpdb->delete( $wpdb->prefix . 'wpdatatables', array( 'id' => $table_id ) );
        return new \WP_Error( 'wdtmcp_columns_error', $e->getMessage() );
    }

    $row_count = count( $res->table->getDataRows() );
    if ( $row_count > 2000 ) {
        $result = $wpdb->update(
            $wpdb->prefix . 'wpdatatables',
            array( 'server_side' => 1 ),
            array( 'id' => $table_id )
        );
        if ( false === $result ) {
            return new \WP_Error(
                'wdtmcp_db_error',
                $wpdb->last_error ?: __( 'Failed to enable server-side processing.', 'wpdatatables' )
            );
        }
    }

    $column_count = count( $res->table->getColumns() );

    do_action( 'wpdatatables_after_save_table', $table_id );

    return array(
        'table_id'   => (int) $table_id,
        'shortcode'  => '[wpdatatable id=' . $table_id . ']',
        'column_count' => $column_count,
    );
}
