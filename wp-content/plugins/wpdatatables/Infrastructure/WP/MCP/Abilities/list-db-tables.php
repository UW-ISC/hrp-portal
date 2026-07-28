<?php
/**
 * Ability: wpdatatables/list-db-tables
 *
 * Lists the actual database tables available in a given connection (default WP
 * database or any registered separate connection). For each table returns its
 * name and optionally its columns with types.
 *
 * This lets the AI discover what data sources exist before building SQL queries.
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_list_db_tables_ability' );

function wdtmcp_register_list_db_tables_ability() {
    wp_register_ability(
        'wpdatatables/list-db-tables',
        array(
            'label'       => __( 'List Database Tables', 'wpdatatables' ),
            'description' => __( 'Lists all database tables (and optionally their columns) available on a given connection. Use connection="" or omit it for the default WordPress database. Provide a connection ID (from get-system-info) to list tables on a separate MySQL/MSSQL/PostgreSQL connection. The AI can use this to discover schemas before building SQL queries for create-table-from-query. WHEN TO CALL: before writing SQL or creating a query-based table — when you need to know which raw database tables exist. OPTIONAL INPUT: connection (string, empty = WordPress DB; use IDs from get-system-info), include_columns (boolean, default false — set true to get column names/types in one call). RETURNS: { connection, vendor, tables: [{ name, columns? }] }. TYPICAL WORKFLOW: get-system-info → list-db-tables → describe-db-table (for one table detail) → create-table-from-query. NOT for wpDataTables themselves — use list-tables for those.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'connection' => array(
                        'type'        => 'string',
                        'description' => 'Connection ID (empty string or omit for default WP database).',
                    ),
                    'include_columns' => array(
                        'type'        => 'boolean',
                        'description' => 'If true, include column names and types for each table. Defaults to false.',
                    ),
                ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'connection' => array( 'type' => 'string',  'description' => 'Connection ID used.' ),
                    'vendor'     => array( 'type' => 'string',  'description' => 'Database vendor (mysql, mssql, postgresql).' ),
                    'tables'     => array(
                        'type'        => 'array',
                        'description' => 'Database tables.',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'name'    => array( 'type' => 'string', 'description' => 'Table name.' ),
                                'columns' => array( 'type' => 'array',  'description' => 'Column definitions (if include_columns=true).', 'items' => array( 'type' => 'object' ) ),
                            ),
                        ),
                    ),
                    'count' => array( 'type' => 'integer', 'description' => 'Total number of tables.' ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_list_db_tables',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Optional connection from get-system-info; omit or use empty string for the WordPress database. Set include_columns=true to get column names in one call. Use before create-table-from-query.', 'wpdatatables' ),
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
            ),
        )
    );
}

/**
 * Execute callback for wpdatatables/list-db-tables.
 *
 * @param array $input
 * @return array|WP_Error
 */
function wdtmcp_execute_list_db_tables( $input ) {
    global $wpdb;

    $connection_id   = isset( $input['connection'] ) ? (string) $input['connection'] : '';
    $include_columns = ! empty( $input['include_columns'] );

    $conn_check = wdtmcp_assert_separate_db_connection_allowed( $connection_id, 'wpdatatables/list-db-tables' );
    if ( is_wp_error( $conn_check ) ) {
        return $conn_check;
    }

    $use_separate = ( '' !== $connection_id )
                    && class_exists( 'Connection' )
                    && \Connection::isSeparate( $connection_id );

    // Resolve vendor.
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

    // Fetch table names.
    $table_names = wdtmcp_list_tables_query( $vendor, $connection_id, $use_separate, $wpdb );
    if ( is_wp_error( $table_names ) ) {
        return $table_names;
    }

    // Build result.
    $tables = array();
    foreach ( $table_names as $name ) {
        $entry = array( 'name' => $name );

        if ( $include_columns ) {
            $cols = wdtmcp_columns_query( $vendor, $name, $connection_id, $use_separate, $wpdb );
            if ( is_wp_error( $cols ) ) {
                return new \WP_Error(
                    'wdtmcp_query_failed',
                    sprintf( __( 'Failed to describe table "%s".', 'wpdatatables' ), $name )
                );
            }
            $entry['columns'] = $cols;
        }

        $tables[] = $entry;
    }

    return array(
        'connection' => $connection_id,
        'vendor'     => $vendor,
        'tables'     => $tables,
        'count'      => count( $tables ),
    );
}

/**
 * Query the list of table names on a connection.
 *
 * @param string $vendor
 * @param string $connection_id
 * @param bool   $use_separate
 * @param wpdb   $wpdb
 * @return array|WP_Error
 */
function wdtmcp_list_tables_query( $vendor, $connection_id, $use_separate, $wpdb ) {
    if ( ! $use_separate ) {
        $rows = $wpdb->get_col( 'SHOW TABLES' );
        if ( null === $rows ) {
            return new \WP_Error( 'wdtmcp_query_failed', __( 'Failed to list tables on the WordPress database.', 'wpdatatables' ) );
        }
        return $rows;
    }

    $sql_conn = \Connection::getInstance( $connection_id );
    if ( ! $sql_conn ) {
        return new \WP_Error( 'wdtmcp_connection_failed', __( 'Could not create database connection.', 'wpdatatables' ) );
    }

    switch ( $vendor ) {
        case 'mssql':
            $query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME";
            break;
        case 'postgresql':
            $query = "SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public' ORDER BY tablename";
            break;
        default: // mysql
            $query = 'SHOW TABLES';
            break;
    }

    $rows = $sql_conn->getAssoc( $query );

    if ( false === $rows || null === $rows ) {
        $err = method_exists( $sql_conn, 'getLastError' ) ? $sql_conn->getLastError() : '';
        if ( $err ) {
            return new \WP_Error( 'wdtmcp_query_failed', $err );
        }
        return array();
    }

    // Normalise: getAssoc returns array of assoc arrays; we need just the names.
    $names = array();
    foreach ( $rows as $row ) {
        if ( is_array( $row ) ) {
            $names[] = reset( $row ); // First column value.
        } elseif ( is_string( $row ) ) {
            $names[] = $row;
        }
    }

    return $names;
}

/**
 * Resolve a table name against tables visible on the connection (whitelist).
 *
 * @param string $table_name    Raw requested table name.
 * @param string $vendor        Database vendor.
 * @param string $connection_id Connection ID.
 * @param bool   $use_separate  Whether a separate connection is used.
 * @param wpdb   $wpdb          WordPress database object.
 * @return string|WP_Error Canonical table name from the server, or error if unknown.
 */
function wdtmcp_resolve_validated_db_table( $table_name, $vendor, $connection_id, $use_separate, $wpdb ) {
    $table_name = (string) $table_name;

    if ( '' === $table_name ) {
        return new \WP_Error( 'wdtmcp_missing_param', __( 'table_name is required.', 'wpdatatables' ) );
    }

    $allowed = wdtmcp_list_tables_query( $vendor, $connection_id, $use_separate, $wpdb );
    if ( is_wp_error( $allowed ) ) {
        return $allowed;
    }

    if ( in_array( $table_name, $allowed, true ) ) {
        return $table_name;
    }

    foreach ( $allowed as $name ) {
        if ( is_string( $name ) && strcasecmp( $table_name, $name ) === 0 ) {
            return $name;
        }
    }

    return new \WP_Error(
        'wdtmcp_invalid_table',
        sprintf( __( 'Table "%s" was not found on this connection.', 'wpdatatables' ), $table_name )
    );
}

/**
 * Quote a validated database identifier for safe SQL interpolation.
 *
 * @param string $identifier Validated table (or column) name.
 * @param string $lq         Left quote character.
 * @param string $rq         Right quote character.
 * @return string
 */
function wdtmcp_quote_db_identifier( $identifier, $lq, $rq ) {
    $escaped = str_replace( $lq, $lq . $lq, (string) $identifier );

    return $lq . $escaped . $rq;
}

/**
 * Query column definitions for a single table.
 *
 * @param string $vendor
 * @param string $table_name
 * @param string $connection_id
 * @param bool   $use_separate
 * @param wpdb   $wpdb
 * @return array|WP_Error
 */
function wdtmcp_columns_query( $vendor, $table_name, $connection_id, $use_separate, $wpdb ) {
    if ( ! $use_separate ) {
        $quoted = wdtmcp_quote_db_identifier( $table_name, '`', '`' );
        $rows   = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $quoted, ARRAY_A );
        if ( null === $rows ) {
            return new \WP_Error( 'wdtmcp_query_failed', __( 'Failed to describe table.', 'wpdatatables' ) );
        }
        return array_map( 'wdtmcp_normalize_mysql_column', $rows );
    }

    $sql_conn = \Connection::getInstance( $connection_id );
    if ( ! $sql_conn ) {
        return new \WP_Error( 'wdtmcp_connection_failed', __( 'Could not create database connection.', 'wpdatatables' ) );
    }

    $lq = \Connection::getLeftColumnQuote( $vendor );
    $rq = \Connection::getRightColumnQuote( $vendor );

    switch ( $vendor ) {
        case 'mssql':
            $query = $wpdb->prepare(
                "SELECT COLUMN_NAME AS name, DATA_TYPE AS type, IS_NULLABLE AS nullable, "
                . "COLUMNPROPERTY(OBJECT_ID(TABLE_SCHEMA + '.' + TABLE_NAME), COLUMN_NAME, 'IsIdentity') AS is_key, "
                . "COLUMN_DEFAULT AS [default] "
                . "FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = %s "
                . "ORDER BY ORDINAL_POSITION",
                $table_name
            );
            break;
        case 'postgresql':
            $query = $wpdb->prepare(
                "SELECT column_name AS name, data_type AS type, is_nullable AS nullable, "
                . "column_default AS default_val "
                . "FROM information_schema.columns "
                . "WHERE table_schema = 'public' AND table_name = %s "
                . "ORDER BY ordinal_position",
                $table_name
            );
            break;
        default: // mysql
            $query = 'SHOW COLUMNS FROM ' . wdtmcp_quote_db_identifier( $table_name, $lq, $rq );
            break;
    }

    $rows = $sql_conn->getAssoc( $query );

    if ( false === $rows || null === $rows ) {
        $err = method_exists( $sql_conn, 'getLastError' ) ? $sql_conn->getLastError() : '';
        if ( $err ) {
            return new \WP_Error( 'wdtmcp_query_failed', $err );
        }
        return array();
    }

    if ( 'mysql' === $vendor ) {
        return array_map( 'wdtmcp_normalize_mysql_column', $rows );
    }

    return array_map( static function ( $row ) use ( $vendor ) {
        $nullable_raw = isset( $row['nullable'] ) ? $row['nullable'] : ( isset( $row['is_nullable'] ) ? $row['is_nullable'] : 'YES' );
        $default_val  = isset( $row['default'] ) ? $row['default'] : ( isset( $row['default_val'] ) ? $row['default_val'] : null );
        return array(
            'name'     => isset( $row['name'] ) ? (string) $row['name'] : ( isset( $row['column_name'] ) ? (string) $row['column_name'] : '' ),
            'type'     => isset( $row['type'] ) ? (string) $row['type'] : ( isset( $row['data_type'] ) ? (string) $row['data_type'] : '' ),
            'nullable' => ( strtoupper( $nullable_raw ) === 'YES' ),
            'key'      => isset( $row['is_key'] ) && $row['is_key'] ? 'PRI' : '',
            'default'  => $default_val !== null ? (string) $default_val : '',
        );
    }, $rows );
}

/**
 * Normalize a MySQL SHOW COLUMNS row into a consistent shape.
 *
 * @param array $row
 * @return array
 */
function wdtmcp_normalize_mysql_column( $row ) {
    return array(
        'name'     => isset( $row['Field'] )   ? (string) $row['Field']   : '',
        'type'     => isset( $row['Type'] )     ? (string) $row['Type']    : '',
        'nullable' => ( isset( $row['Null'] ) && strtoupper( $row['Null'] ) === 'YES' ),
        'key'      => isset( $row['Key'] )      ? (string) $row['Key']     : '',
        'default'  => isset( $row['Default'] ) && $row['Default'] !== null ? (string) $row['Default'] : '',
    );
}
