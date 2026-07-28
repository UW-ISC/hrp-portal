<?php
/**
 * Ability: wpdatatables/describe-db-table
 *
 * Returns the full schema of a single database table: column names, data types,
 * nullability, keys, and a sample of rows. Supports both the default WP
 * connection and separate connections.
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_describe_db_table_ability' );

function wdtmcp_register_describe_db_table_ability() {
    wp_register_ability(
        'wpdatatables/describe-db-table',
        array(
            'label'       => __( 'Describe Database Table', 'wpdatatables' ),
            'description' => __( 'Returns the full column schema (name, type, nullable, key, default) and an optional sample of rows for a single database table on a given connection. Use this to understand a table\'s structure before writing SQL queries. WHEN TO CALL: after list-db-tables when you need column names, types, or sample data from one MySQL/MSSQL/PostgreSQL table before composing a SELECT for create-table-from-query. REQUIRED INPUT: table_name (string). OPTIONAL INPUT: connection (string, empty = WordPress DB), sample_rows (integer 0–10, default 3). RETURNS: table_name, connection, vendor, columns[], sample_rows[]. TYPICAL WORKFLOW: get-system-info → list-db-tables → describe-db-table → create-table-from-query. Use column names from the response in your SELECT query.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'connection' => array(
                        'type'        => 'string',
                        'description' => 'Connection ID (empty string or omit for default WP database).',
                    ),
                    'table_name' => array(
                        'type'        => 'string',
                        'description' => 'The database table name to describe.',
                    ),
                    'sample_rows' => array(
                        'type'        => 'integer',
                        'description' => 'Number of sample rows to include (0-10, default 3).',
                    ),
                ),
                'required' => array( 'table_name' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_name' => array( 'type' => 'string', 'description' => 'Table name.' ),
                    'connection' => array( 'type' => 'string', 'description' => 'Connection ID used.' ),
                    'vendor'     => array( 'type' => 'string', 'description' => 'Database vendor.' ),
                    'columns'    => array(
                        'type' => 'array',
                        'items' => array(
                            'type' => 'object',
                            'properties' => array(
                                'name'     => array( 'type' => 'string' ),
                                'type'     => array( 'type' => 'string' ),
                                'nullable' => array( 'type' => 'boolean' ),
                                'key'      => array( 'type' => 'string' ),
                                'default'  => array( 'type' => 'string' ),
                            ),
                        ),
                    ),
                    'sample'    => array( 'type' => 'array', 'description' => 'Sample rows.', 'items' => array( 'type' => 'object' ) ),
                    'row_count' => array( 'type' => 'integer', 'description' => 'Approximate total row count.' ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_describe_db_table',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Requires table_name. Optional connection and sample_rows (0-10). Use column names from the response when composing a SELECT for create-table-from-query.', 'wpdatatables' ),
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
            ),
        )
    );
}

/**
 * Execute callback for wpdatatables/describe-db-table.
 *
 * @param array $input
 * @return array|WP_Error
 */
function wdtmcp_execute_describe_db_table( $input ) {
    global $wpdb;

    $table_name    = isset( $input['table_name'] ) ? (string) $input['table_name'] : '';
    $connection_id = isset( $input['connection'] ) ? (string) $input['connection'] : '';
    $sample_rows   = isset( $input['sample_rows'] ) ? (int) $input['sample_rows'] : 3;
    $sample_rows   = max( 0, min( 10, $sample_rows ) );

    $conn_check = wdtmcp_assert_separate_db_connection_allowed( $connection_id, 'wpdatatables/describe-db-table' );
    if ( is_wp_error( $conn_check ) ) {
        return $conn_check;
    }

    $use_separate = ( '' !== $connection_id )
                    && class_exists( 'Connection' )
                    && \Connection::isSeparate( $connection_id );

    $vendor = 'mysql';
    if ( $use_separate ) {
        try {
            $vendor = \Connection::getVendor( $connection_id );
        } catch ( \Exception $e ) {
            return new \WP_Error(
                'wdtmcp_invalid_connection',
                sprintf( __( 'Connection "%s" not found.', 'wpdatatables' ), $connection_id )
            );
        }
    }

    $validated = wdtmcp_resolve_validated_db_table( $table_name, $vendor, $connection_id, $use_separate, $wpdb );
    if ( is_wp_error( $validated ) ) {
        return $validated;
    }
    $table_name = $validated;

    // ── Column schema ────────────────────────────────────────────
    // Reuse the helper defined in list-db-tables.php.
    $columns = wdtmcp_columns_query( $vendor, $table_name, $connection_id, $use_separate, $wpdb );
    if ( is_wp_error( $columns ) ) {
        return $columns;
    }

    // ── Row count ────────────────────────────────────────────────
    $row_count = wdtmcp_row_count_query( $vendor, $table_name, $connection_id, $use_separate, $wpdb );

    // ── Sample rows ──────────────────────────────────────────────
    $sample = array();
    if ( $sample_rows > 0 ) {
        $sample = wdtmcp_sample_rows_query( $vendor, $table_name, $sample_rows, $connection_id, $use_separate, $wpdb );
        if ( is_wp_error( $sample ) ) {
            $sample = array();
        }
    }

    return array(
        'table_name' => $table_name,
        'connection' => $connection_id,
        'vendor'     => $vendor,
        'columns'    => $columns,
        'sample'     => $sample,
        'row_count'  => $row_count,
    );
}

/**
 * Get the approximate row count for a table.
 *
 * @param string $vendor
 * @param string $table_name
 * @param string $connection_id
 * @param bool   $use_separate
 * @param wpdb   $wpdb
 * @return int
 */
function wdtmcp_row_count_query( $vendor, $table_name, $connection_id, $use_separate, $wpdb ) {
    $lq = '`';
    $rq = '`';
    if ( $use_separate ) {
        $lq = \Connection::getLeftColumnQuote( $vendor );
        $rq = \Connection::getRightColumnQuote( $vendor );
    }

    $quoted = wdtmcp_quote_db_identifier( $table_name, $lq, $rq );
    $query  = 'SELECT COUNT(*) AS cnt FROM ' . $quoted;

    if ( ! $use_separate ) {
        $val = $wpdb->get_var( $query );
        return ( null !== $val ) ? (int) $val : 0;
    }

    $sql_conn = \Connection::getInstance( $connection_id );
    if ( ! $sql_conn ) {
        return 0;
    }

    $val = $sql_conn->getField( $query );
    if ( false === $val || null === $val ) {
        return 0;
    }

    return (int) $val;
}

/**
 * Fetch a limited sample of rows from a table.
 *
 * @param string $vendor
 * @param string $table_name
 * @param int    $limit
 * @param string $connection_id
 * @param bool   $use_separate
 * @param wpdb   $wpdb
 * @return array|WP_Error
 */
function wdtmcp_sample_rows_query( $vendor, $table_name, $limit, $connection_id, $use_separate, $wpdb ) {
    $lq = '`';
    $rq = '`';
    if ( $use_separate ) {
        $lq = \Connection::getLeftColumnQuote( $vendor );
        $rq = \Connection::getRightColumnQuote( $vendor );
    }

    $quoted = wdtmcp_quote_db_identifier( $table_name, $lq, $rq );
    $limit  = (int) $limit;

    switch ( $vendor ) {
        case 'mssql':
            $query = "SELECT TOP {$limit} * FROM {$quoted}";
            break;
        default: // mysql, postgresql
            $query = "SELECT * FROM {$quoted} LIMIT {$limit}";
            break;
    }

    if ( ! $use_separate ) {
        $rows = $wpdb->get_results( $query, ARRAY_A );
        if ( null === $rows ) {
            return new \WP_Error( 'wdtmcp_query_failed', __( 'Failed to fetch sample rows.', 'wpdatatables' ) );
        }
        return $rows;
    }

    $sql_conn = \Connection::getInstance( $connection_id );
    if ( ! $sql_conn ) {
        return new \WP_Error( 'wdtmcp_connection_failed', __( 'Could not create database connection.', 'wpdatatables' ) );
    }

    $rows = $sql_conn->getAssoc( $query );

    if ( false === $rows || null === $rows ) {
        $err = method_exists( $sql_conn, 'getLastError' ) ? $sql_conn->getLastError() : '';
        if ( $err ) {
            return new \WP_Error( 'wdtmcp_query_failed', $err );
        }
        return array();
    }

    return is_array( $rows ) ? $rows : array();
}
