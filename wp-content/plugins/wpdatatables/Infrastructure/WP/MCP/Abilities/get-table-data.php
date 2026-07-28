<?php
/**
 * Ability: wpdatatables/get-table-data
 *
 * Returns actual row data from a wpDataTable.
 *
 * Behaviour varies by table mode:
 *  - Non-server-side tables: returns ALL rows in a single response,
 *    together with column metadata so the AI can format them.
 *  - Server-side tables: accepts optional pagination (page, per_page),
 *    sorting (order_by, order_dir), and search (search, column_search)
 *    parameters and returns one page of results plus totals.
 *  - Simple (spreadsheet) tables: returns every cell via
 *    WPDataTableRows::loadWpDataTableRows().
 *
 * @package wpDataTables_MCP_Server
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_get_table_data_ability' );

function wdtmcp_register_get_table_data_ability() {
    wp_register_ability(
        'wpdatatables/get-table-data',
        array(
            'label'       => __( 'Get wpDataTable Data', 'wpdatatables' ),
            'description' => __( 'Returns row data from a wpDataTable. For non-server-side tables the full dataset is returned in one call. For server-side tables (server_side=true in get-table-info) you MUST use the search and column_search parameters to filter data server-side rather than paginating through all rows manually. Use "search" for a global text search across all columns, or "column_search" to filter specific columns (e.g. {"Name": "bob"} to find rows where the Name column contains "bob"). You can also paginate (page, per_page), and sort (order_by, order_dir). The response includes column_headers with key/label/type so you know how to interpret the rows. Use get-table-info first to discover column names (orig_header) and whether the table is server-side. WHEN TO CALL: when the user asks to see, analyze, export, or answer questions about table contents. REQUIRED INPUT: table_id (integer). OPTIONAL: page, per_page, order_by (orig_header string), order_dir (asc|desc), search (global text), column_search (object mapping orig_header → value). DO NOT paginate through every page of a large server-side table — filter with search/column_search instead. TYPICAL WORKFLOW: list-tables → get-table-info → get-table-data. RETURNS: rows[], column_headers[], total_rows, page info, and server_side flag.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_id' => array(
                        'type'        => 'integer',
                        'description' => 'The unique wpDataTable ID.',
                    ),
                    'page' => array(
                        'type'        => 'integer',
                        'description' => 'Page number (1-based). Only used for server-side tables. Defaults to 1.',
                    ),
                    'per_page' => array(
                        'type'        => 'integer',
                        'description' => 'Rows per page (max 500). Only used for server-side tables. Defaults to the table\'s display_length setting.',
                    ),
                    'order_by' => array(
                        'type'        => 'string',
                        'description' => 'Column orig_header to sort by. Only used for server-side tables.',
                    ),
                    'order_dir' => array(
                        'type'        => 'string',
                        'description' => 'Sort direction: "asc" or "desc". Defaults to "asc". Only used for server-side tables.',
                        'enum'        => array( 'asc', 'desc' ),
                    ),
                    'search' => array(
                        'type'        => 'string',
                        'description' => 'Global search term: filters rows where ANY column contains this text (case-insensitive LIKE match). For server-side tables only. Example: "bob" finds all rows containing "bob" in any column.',
                    ),
                    'column_search' => array(
                        'type'        => 'object',
                        'description' => 'Per-column filters: an object mapping column orig_header names to search values. Each filter matches rows where that column contains the value (LIKE match). For server-side tables only. Example: {"Name": "bob", "CountryCode": "USA"} finds rows where Name contains "bob" AND CountryCode contains "USA".',
                        'additionalProperties' => array( 'type' => 'string' ),
                    ),
                ),
                'required' => array( 'table_id' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_id' => array(
                        'type'        => 'integer',
                        'description' => 'The table ID that was queried.',
                    ),
                    'table_type' => array(
                        'type'        => 'string',
                        'description' => 'Source type of this table.',
                    ),
                    'server_side' => array(
                        'type'        => 'boolean',
                        'description' => 'Whether server-side processing is active.',
                    ),
                    'column_headers' => array(
                        'type'        => 'array',
                        'description' => 'Ordered list of columns with their key, display label, and data type.',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'key'     => array( 'type' => 'string', 'description' => 'Column orig_header (use as row object key).' ),
                                'label'   => array( 'type' => 'string', 'description' => 'Human-readable display header.' ),
                                'type'    => array( 'type' => 'string', 'description' => 'Data type (string, int, float, date, datetime, time, etc.).' ),
                            ),
                        ),
                    ),
                    'rows' => array(
                        'type'        => 'array',
                        'description' => 'Array of row objects keyed by column orig_header.',
                        'items'       => array( 'type' => 'object' ),
                    ),
                    'total_rows' => array(
                        'type'        => 'integer',
                        'description' => 'Total number of rows (before pagination/filtering).',
                    ),
                    'filtered_rows' => array(
                        'type'        => 'integer',
                        'description' => 'Number of rows matching the current filters (server-side only; equals total_rows otherwise).',
                    ),
                    'page' => array(
                        'type'        => 'integer',
                        'description' => 'Current page number (server-side only; 1 otherwise).',
                    ),
                    'per_page' => array(
                        'type'        => 'integer',
                        'description' => 'Rows per page used (server-side only; equals total_rows otherwise).',
                    ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_get_table_data',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Requires table_id. Call get-table-info first. For server_side=true tables, filter with search or column_search instead of paginating through every page.', 'wpdatatables' ),
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
            ),
        )
    );
}

/**
 * Execute callback for wpdatatables/get-table-data.
 *
 * @param array $input
 * @return array|WP_Error
 */
function wdtmcp_execute_get_table_data( $input ) {
    $table_id = isset( $input['table_id'] ) ? (int) $input['table_id'] : 0;
    if ( $table_id <= 0 ) {
        return new \WP_Error(
            'wdtmcp_invalid_input',
            __( 'A valid table_id (positive integer) is required.', 'wpdatatables' )
        );
    }

    if ( ! class_exists( 'WDTConfigController' ) ) {
        return new \WP_Error(
            'wdtmcp_missing_class',
            __( 'wpDataTables core class WDTConfigController is not available.', 'wpdatatables' )
        );
    }

    try {
        $table_config = \WDTConfigController::loadTableFromDB( $table_id );
    } catch ( \Exception $e ) {
        return new \WP_Error(
            'wdtmcp_load_error',
            sprintf( __( 'Error loading table %d: %s', 'wpdatatables' ), $table_id, $e->getMessage() )
        );
    }

    if ( empty( $table_config ) ) {
        return new \WP_Error(
            'wdtmcp_not_found',
            sprintf( __( 'Table with ID %d was not found.', 'wpdatatables' ), $table_id )
        );
    }

    $table_type = isset( $table_config->table_type ) ? $table_config->table_type : '';

    // Simple (spreadsheet) tables use a completely different storage model.
    if ( $table_type === 'simple' ) {
        return wdtmcp_get_simple_table_data( $table_id, $table_config );
    }

    $is_server_side = ! empty( $table_config->server_side );

    // Non-server-side: load everything via WPDataTable::loadWpDataTable().
    if ( ! $is_server_side ) {
        return wdtmcp_get_full_table_data( $table_id, $table_config );
    }

    // Server-side: build a controlled query with pagination/filter/sort.
    return wdtmcp_get_server_side_table_data( $table_id, $table_config, $input );
}

/**
 * Build column_headers metadata from the table config's columns array.
 */
function wdtmcp_build_column_headers( $table_config ) {
    $headers = array();
    if ( ! empty( $table_config->columns ) && is_array( $table_config->columns ) ) {
        foreach ( $table_config->columns as $col ) {
            if ( ! empty( $col->visible ) ) {
                $headers[] = array(
                    'key'   => isset( $col->orig_header ) ? (string) $col->orig_header : '',
                    'label' => isset( $col->display_header ) ? (string) $col->display_header : '',
                    'type'  => isset( $col->type ) ? (string) $col->type : 'string',
                );
            }
        }
    }
    return $headers;
}

/**
 * Full load for non-server-side tables (CSV, Excel, JSON, Google Sheets, SQL
 * without server-side, manual without server-side, etc.).
 */
function wdtmcp_get_full_table_data( $table_id, $table_config ) {
    if ( ! class_exists( 'WPDataTable' ) ) {
        return new \WP_Error( 'wdtmcp_missing_class', __( 'WPDataTable class is not available.', 'wpdatatables' ) );
    }

    try {
        $wdt = \WPDataTable::loadWpDataTable( $table_id, null, true );
    } catch ( \Exception $e ) {
        return new \WP_Error(
            'wdtmcp_load_error',
            sprintf( __( 'Error loading table data for table %d: %s', 'wpdatatables' ), $table_id, $e->getMessage() )
        );
    }

    $rows       = $wdt->getDataRows();
    $row_count  = is_array( $rows ) ? count( $rows ) : 0;
    $table_type = isset( $table_config->table_type ) ? $table_config->table_type : '';

    return array(
        'table_id'       => $table_id,
        'table_type'     => ( $table_type === 'mysql' ) ? 'SQL' : $table_type,
        'server_side'    => false,
        'column_headers' => wdtmcp_build_column_headers( $table_config ),
        'rows'           => is_array( $rows ) ? array_values( $rows ) : array(),
        'total_rows'     => $row_count,
        'filtered_rows'  => $row_count,
        'page'           => 1,
        'per_page'       => $row_count,
    );
}

/**
 * Paginated / filtered load for server-side tables.
 *
 * Instead of manipulating $_POST globals for wpDataTables' internal server-side
 * path, we run a controlled query directly against the table's SQL content. This
 * keeps things safe (our own parameter allowlisting) and avoids side-effects.
 *
 * Mirrors the relevant parts of WPDataTable::queryBasedConstruct():
 *  - Applies wpDataTables filter hooks so third-party code can modify queries.
 *  - Uses vendor-aware LIMIT/OFFSET syntax (MySQL, MSSQL, PostgreSQL).
 *  - Uses vendor-aware LIKE expressions (PostgreSQL LOWER(CAST(...))).
 */
function wdtmcp_get_server_side_table_data( $table_id, $table_config, $input ) {
    global $wpdb;

    $base_query = isset( $table_config->content ) ? $table_config->content : '';
    if ( empty( $base_query ) ) {
        return new \WP_Error(
            'wdtmcp_no_query',
            __( 'Server-side table has no SQL query content.', 'wpdatatables' )
        );
    }

    $column_headers = wdtmcp_build_column_headers( $table_config );
    $valid_columns  = array_column( $column_headers, 'key' );

    if ( empty( $valid_columns ) ) {
        return new \WP_Error( 'wdtmcp_no_columns', __( 'No visible columns found for this table.', 'wpdatatables' ) );
    }

    // Resolve connection vendor (MySQL is the default for the WP connection).
    $connection_name = isset( $table_config->connection ) ? $table_config->connection : '';
    $use_separate    = class_exists( 'Connection' ) && \Connection::isSeparate( $connection_name );

    $vendor = 'mysql';
    $lq     = '`';
    $rq     = '`';
    if ( $use_separate ) {
        $vendor = \Connection::getVendor( $connection_name );
        $lq     = \Connection::getLeftColumnQuote( $vendor );
        $rq     = \Connection::getRightColumnQuote( $vendor );
    }

    $is_mysql      = ( $vendor === 'mysql' );
    $is_mssql      = ( $vendor === 'mssql' );
    $is_postgresql = ( $vendor === 'postgresql' );

    // Apply placeholder variables ($wdtVar1 … $wdtVar9) and sanitize.
    if ( class_exists( 'WDTTools' ) ) {
        $base_query = \WDTTools::applyPlaceholders( $base_query );
    }
    if ( function_exists( 'wdtSanitizeQuery' ) ) {
        $base_query = wdtSanitizeQuery( $base_query );
    }

    // Hook: let third-party code modify the query before LIMIT is appended.
    $base_query = apply_filters( 'wpdatatables_filter_query_before_limit', $base_query, $table_id );

    $wrapped = "SELECT * FROM ({$base_query}) AS wdtmcp_data";

    // ----- WHERE clause --------------------------------------------------
    $where_parts = array();

    if ( ! empty( $input['search'] ) && is_string( $input['search'] ) ) {
        $like_parts = array();
        foreach ( $valid_columns as $col ) {
            $like_parts[] = wdtmcp_like_expr( $vendor, $lq, $rq, $col, $input['search'], $connection_name );
        }
        if ( ! empty( $like_parts ) ) {
            $where_parts[] = '(' . implode( ' OR ', $like_parts ) . ')';
        }
    }

    if ( ! empty( $input['column_search'] ) && is_array( $input['column_search'] ) ) {
        foreach ( $input['column_search'] as $col => $val ) {
            if ( ! in_array( $col, $valid_columns, true ) ) {
                continue;
            }
            $where_parts[] = wdtmcp_like_expr( $vendor, $lq, $rq, $col, $val, $connection_name );
        }
    }

    $where_sql = '';
    if ( ! empty( $where_parts ) ) {
        $where_sql = ' WHERE ' . implode( ' AND ', $where_parts );
    }

    // ----- ORDER BY ------------------------------------------------------
    $order_sql = '';
    if ( ! empty( $input['order_by'] ) && in_array( $input['order_by'], $valid_columns, true ) ) {
        $dir       = ( ! empty( $input['order_dir'] ) && strtolower( $input['order_dir'] ) === 'desc' ) ? 'DESC' : 'ASC';
        $order_sql = " ORDER BY {$lq}{$input['order_by']}{$rq} {$dir}";
    }

    // ----- Pagination (vendor-aware LIMIT/OFFSET) ------------------------
    $display_length = isset( $table_config->display_length ) ? (int) $table_config->display_length : 25;
    $per_page       = isset( $input['per_page'] ) ? min( max( (int) $input['per_page'], 1 ), 500 ) : $display_length;
    $page           = isset( $input['page'] ) ? max( (int) $input['page'], 1 ) : 1;
    $offset         = ( $page - 1 ) * $per_page;

    $limit_sql = wdtmcp_limit_expr( $vendor, $per_page, $offset, $order_sql );

    // ----- Build final SQL statements ------------------------------------
    $count_total_sql    = "SELECT COUNT(*) FROM ({$base_query}) AS wdtmcp_cnt";
    $count_filtered_sql = "SELECT COUNT(*) FROM ({$base_query}) AS wdtmcp_data{$where_sql}";
    $data_sql           = "{$wrapped}{$where_sql}{$order_sql}{$limit_sql}";

    // Hook: let third-party code modify the final data query.
    $data_sql = apply_filters( 'wpdatatables_filter_mysql_query', $data_sql, $table_id );

    // ----- Execute -------------------------------------------------------
    if ( $use_separate ) {
        $sql_link = \Connection::getInstance( $connection_name );

        $total_raw = $sql_link->getField( $count_total_sql );
        if ( false === $total_raw ) {
            $last_err = method_exists( $sql_link, 'getLastError' ) ? $sql_link->getLastError() : '';
            if ( ! empty( $last_err ) ) {
                return new \WP_Error( 'wdtmcp_query_error', 'Count query failed: ' . $last_err );
            }
            $total_raw = 0;
        }
        $total_rows = (int) $total_raw;

        $filtered_raw = $sql_link->getField( $count_filtered_sql );
        $filtered     = ( false !== $filtered_raw ) ? (int) $filtered_raw : 0;

        $rows = $sql_link->getAssoc( $data_sql );
        if ( false === $rows || ! is_array( $rows ) ) {
            $last_err = method_exists( $sql_link, 'getLastError' ) ? $sql_link->getLastError() : '';
            if ( ! empty( $last_err ) ) {
                return new \WP_Error( 'wdtmcp_query_error', 'Data query failed: ' . $last_err );
            }
            $rows = array();
        }
    } else {
        $total_rows = (int) $wpdb->get_var( $count_total_sql );
        $filtered   = (int) $wpdb->get_var( $count_filtered_sql );
        $rows       = $wpdb->get_results( $data_sql, ARRAY_A );

        if ( ! is_array( $rows ) ) {
            return new \WP_Error(
                'wdtmcp_query_error',
                __( 'Failed to retrieve data from the table.', 'wpdatatables' ) .
                ( ! empty( $wpdb->last_error ) ? ' ' . $wpdb->last_error : '' )
            );
        }
    }

    $table_type = isset( $table_config->table_type ) ? $table_config->table_type : '';

    return array(
        'table_id'       => $table_id,
        'table_type'     => ( $table_type === 'mysql' ) ? 'SQL' : $table_type,
        'server_side'    => true,
        'column_headers' => $column_headers,
        'rows'           => $rows,
        'total_rows'     => $total_rows,
        'filtered_rows'  => $filtered,
        'page'           => $page,
        'per_page'       => $per_page,
    );
}

/**
 * Build a vendor-appropriate LIKE expression for a single column.
 *
 * Uses WDTTools::buildLikeComparison() so separate PostgreSQL/MSSQL
 * connections get native escaping (not MySQL-oriented $wpdb->prepare).
 *
 * @param string     $vendor     Connection vendor identifier.
 * @param string     $lq         Left column quote character.
 * @param string     $rq         Right column quote character.
 * @param string     $column     Column orig_header.
 * @param string     $value      Raw search value (will be escaped).
 * @param string|null $connection Connection id or empty for WP MySQL.
 * @return string SQL fragment.
 */
function wdtmcp_like_expr( $vendor, $lq, $rq, $column, $value, $connection = null ) {
    $qualified = $lq . $column . $rq;

    if ( class_exists( 'WDTTools' ) && method_exists( 'WDTTools', 'buildLikeComparison' ) ) {
        return \WDTTools::buildLikeComparison(
            $qualified,
            $value,
            $connection,
            '%',
            '%',
            $vendor === 'postgresql'
        );
    }

    global $wpdb;
    $like_value = '%' . $wpdb->esc_like( $value ) . '%';

    if ( $vendor === 'postgresql' ) {
        return $wpdb->prepare(
            "LOWER(CAST({$qualified} AS TEXT)) LIKE LOWER(%s)",
            $like_value
        );
    }

    return $wpdb->prepare(
        "{$qualified} LIKE %s",
        $like_value
    );
}

/**
 * Build a vendor-appropriate LIMIT/OFFSET clause.
 *
 * Matches the patterns used in WPDataTable::queryBasedConstruct():
 *  - MySQL      : LIMIT {per_page} OFFSET {offset}
 *  - PostgreSQL : LIMIT {per_page} OFFSET {offset}
 *  - MSSQL      : [ORDER BY (SELECT NULL)] OFFSET {offset} ROWS FETCH NEXT {per_page} ROWS ONLY
 *
 * @param string $vendor    Connection vendor identifier.
 * @param int    $per_page  Number of rows to return.
 * @param int    $offset    Number of rows to skip.
 * @param string $order_sql The ORDER BY clause already built (empty string if none).
 * @return string SQL fragment.
 */
function wdtmcp_limit_expr( $vendor, $per_page, $offset, $order_sql ) {
    if ( $vendor === 'mssql' ) {
        $needs_default_order = empty( $order_sql );
        return ( $needs_default_order ? ' ORDER BY (SELECT NULL)' : '' )
            . " OFFSET {$offset} ROWS FETCH NEXT {$per_page} ROWS ONLY";
    }

    // MySQL and PostgreSQL share the same LIMIT/OFFSET syntax.
    return " LIMIT {$per_page} OFFSET {$offset}";
}

/**
 * Simple (spreadsheet) tables store data in wp_wpdatatables_rows as JSON.
 */
function wdtmcp_get_simple_table_data( $table_id, $table_config ) {
    if ( ! class_exists( 'WPDataTableRows' ) ) {
        return new \WP_Error( 'wdtmcp_missing_class', __( 'WPDataTableRows class is not available.', 'wpdatatables' ) );
    }

    try {
        $wdt_rows = \WPDataTableRows::loadWpDataTableRows( $table_id );
    } catch ( \Exception $e ) {
        return new \WP_Error(
            'wdtmcp_load_error',
            sprintf( __( 'Error loading simple table %d: %s', 'wpdatatables' ), $table_id, $e->getMessage() )
        );
    }

    $col_headers_raw = $wdt_rows->getColHeaders();
    $column_headers  = array();
    foreach ( $col_headers_raw as $idx => $label ) {
        $column_headers[] = array(
            'key'   => 'col_' . $idx,
            'label' => (string) $label,
            'type'  => 'string',
        );
    }

    $raw_rows = $wdt_rows->getRowsData();
    $rows     = array();
    foreach ( $raw_rows as $row_obj ) {
        $row = array();
        if ( isset( $row_obj->cells ) && is_array( $row_obj->cells ) ) {
            foreach ( $row_obj->cells as $idx => $cell ) {
                $key         = 'col_' . $idx;
                $row[ $key ] = isset( $cell->data ) ? $cell->data : '';
            }
        }
        $rows[] = $row;
    }

    $row_count = count( $rows );

    return array(
        'table_id'       => $table_id,
        'table_type'     => 'simple',
        'server_side'    => false,
        'column_headers' => $column_headers,
        'rows'           => $rows,
        'total_rows'     => $row_count,
        'filtered_rows'  => $row_count,
        'page'           => 1,
        'per_page'       => $row_count,
    );
}
