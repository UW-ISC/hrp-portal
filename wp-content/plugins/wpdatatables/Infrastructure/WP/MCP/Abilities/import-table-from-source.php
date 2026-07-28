<?php
/**
 * Ability: wpdatatables/import-table-from-source
 *
 * Imports data from a file or URL into a MySQL table once. Unlike create-table-from-source
 * (which reads from the source on every page load), import reads the file once and stores
 * data in the database. The resulting table is editable and performs better for large datasets.
 *
 * Supported sources: CSV, XLS/XLSX, Google Spreadsheet. JSON/XML not supported for import.
 * Requires Standard tier (WDT_UMFF_INTEGRATION).
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_import_table_from_source_ability' );

function wdtmcp_register_import_table_from_source_ability() {
    wp_register_ability(
        'wpdatatables/import-table-from-source',
        array(
            'label'       => __( 'Import Table from File/URL', 'wpdatatables' ),
            'description' => __( 'Imports CSV, Excel, or Google Spreadsheet data into a MySQL table once. Result is editable and better for large datasets than create-table-from-source. For local files use upload-data-file (base64) or attachment_id from Media Library. Use source_url for public remote URLs. Requires Standard tier (update-manual-from-file integration). WHEN TO CALL: user wants file data copied into the database once. REQUIRED: title, source_type (csv|xls|google_spreadsheet). PROVIDE ONE OF: attachment_id OR source_url.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'title' => array(
                        'type'        => 'string',
                        'description' => 'Table title.',
                    ),
                    'source_type' => array(
                        'type'        => 'string',
                        'description' => 'Data source type. csv, xls, or google_spreadsheet. JSON/XML not supported for import.',
                        'enum'        => array( 'csv', 'xls', 'google_spreadsheet' ),
                    ),
                    'source_url' => array(
                        'type'        => 'string',
                        'description' => 'Public URL only (remote files/APIs). NO localhost, NO 127.0.0.1. For local files use attachment_id.',
                    ),
                    'attachment_id' => array(
                        'type'        => 'integer',
                        'description' => 'WordPress media attachment ID. For local files: POST to {WP_API_URL}/wp/v2/media, then pass returned id. Or use list-media after user uploads.',
                    ),
                    'connection' => array(
                        'type'        => 'string',
                        'description' => 'Database connection name. Default WordPress connection if empty.',
                    ),
                ),
                'required' => array( 'title', 'source_type' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_id'  => array( 'type' => 'integer', 'description' => 'Created table ID.' ),
                    'shortcode' => array( 'type' => 'string',  'description' => 'WordPress shortcode.' ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_import_table_from_source',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Requires Standard tier (check get-system-info). Imports file data into MySQL once. Use attachment_id or source_url; prefer this over create-table-from-source for large editable datasets.', 'wpdatatables' ),
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => false,
                ),
            ),
        )
    );
}

/**
 * Execute callback for wpdatatables/import-table-from-source.
 *
 * @param array $input
 * @return array|WP_Error
 */
function wdtmcp_execute_import_table_from_source( $input ) {
    $bootstrap = wdtmcp_bootstrap_file_abilities();
    if ( is_wp_error( $bootstrap ) ) {
        return $bootstrap;
    }

    if ( ! wdtmcp_is_integration_file_available( 'update_manual_file' ) ) {
        return wdtmcp_license_wp_error(
            'wdtmcp_tier_required',
            __(
                'Import from file requires wpDataTables Standard tier (Update Manual From File integration). Use create-table-from-source for linked tables on Free/Starter.',
                'wpdatatables'
            ),
            'standard',
            array( 'update_manual_file' ),
            'wpdatatables/import-table-from-source'
        );
    }

    if ( ! class_exists( 'wpDataTableConstructor' ) || ! class_exists( 'wpDataTableSourceFile' ) || ! class_exists( 'WDTTools' ) ) {
        return new \WP_Error(
            'wdtmcp_missing_class',
            __( 'wpDataTables constructor classes are required.', 'wpdatatables' )
        );
    }

    $allowed_types = array( 'csv', 'xls', 'google_spreadsheet' );
    $source_type   = isset( $input['source_type'] ) ? sanitize_text_field( $input['source_type'] ) : '';
    $source_url   = isset( $input['source_url'] ) ? trim( (string) $input['source_url'] ) : '';
    $attachment_id = isset( $input['attachment_id'] ) ? (int) $input['attachment_id'] : 0;
    $title        = isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : '';
    $connection   = isset( $input['connection'] ) ? sanitize_text_field( $input['connection'] ) : '';

    if ( '' === $title ) {
        return new \WP_Error( 'wdtmcp_missing_param', __( 'title is required.', 'wpdatatables' ) );
    }
    if ( ! in_array( $source_type, $allowed_types, true ) ) {
        return new \WP_Error( 'wdtmcp_invalid_type', __( 'Invalid source_type. Use: csv, xls, google_spreadsheet. JSON/XML not supported for import.', 'wpdatatables' ) );
    }

    if ( 'google_spreadsheet' === $source_type && ! wdtmcp_is_integration_file_available( 'google_sheet_api' ) ) {
        return wdtmcp_license_wp_error(
            'wdtmcp_integration_missing',
            __(
                'Google Sheets integration files are not available on this installation.',
                'wpdatatables'
            ),
            'starter',
            array( 'google_sheet_api' ),
            'wpdatatables/import-table-from-source'
        );
    }

    if ( $attachment_id > 0 ) {
        if ( '' !== $source_url ) {
            return new \WP_Error( 'wdtmcp_conflict', __( 'Provide either source_url or attachment_id, not both.', 'wpdatatables' ) );
        }
        $file_url = wp_get_attachment_url( $attachment_id );
        if ( ! $file_url ) {
            return new \WP_Error( 'wdtmcp_invalid_attachment', __( 'attachment_id not found or file not in media library.', 'wpdatatables' ) );
        }
        $source_url = $file_url;
    } elseif ( '' === $source_url ) {
        return new \WP_Error( 'wdtmcp_missing_param', __( 'source_url or attachment_id is required.', 'wpdatatables' ) );
    }

    if ( '' !== $source_url && $attachment_id <= 0 ) {
        $url_check = wdtmcp_validate_public_remote_source_url( $source_url );
        if ( is_wp_error( $url_check ) ) {
            return $url_check;
        }

        $file_types = array( 'csv', 'xls' );
        if ( in_array( $source_type, $file_types, true ) && function_exists( 'wdtmcp_download_data_file_to_media_library' ) ) {
            $downloaded_id = wdtmcp_download_data_file_to_media_library( $source_url, $title, $source_type );
            if ( ! is_wp_error( $downloaded_id ) && $downloaded_id > 0 ) {
                $source_url = wp_get_attachment_url( $downloaded_id );
            }
        }
    }

    $file_path_or_url = $source_url;
    if ( $attachment_id > 0 ) {
        $local_path = wdtmcp_get_attachment_local_path( $attachment_id );
        if ( $local_path ) {
            $file_path_or_url = $local_path;
        }
    }

    $resolved = wpDataTableConstructor::isUploadedFileEmpty( $file_path_or_url );
    if ( ! $resolved ) {
        return new \WP_Error( 'wdtmcp_empty_file', __( 'File is empty or could not be resolved to a valid path/URL.', 'wpdatatables' ) );
    }

    $dummy_table = new stdClass();
    $dummy_table->connection = $connection;

    $objSourceFile = new wpDataTableSourceFile( $resolved, $dummy_table, null, null, null );
    $objSourceFile->getTableTypeFromFile();

    if ( 'invalid' === $objSourceFile->getTableType() ) {
        return new \WP_Error( 'wdtmcp_invalid_source', __( 'Source path or URL is not valid. Supported: CSV, XLS, XLSX, ODS, or Google Spreadsheet URL.', 'wpdatatables' ) );
    }

    try {
        $objSourceFile->prepareHeadingsArray();
    } catch ( Exception $e ) {
        return new \WP_Error( 'wdtmcp_read_error', $e->getMessage() );
    }

    $headingsArray    = $objSourceFile->getHeadingsArray();
    $columnOrigHeaders = $objSourceFile->getColumnOrigHeaders();
    $namedDataArray   = $objSourceFile->getNamedDataArray();
    $is_google        = 'google' === $objSourceFile->getTableType();
    $columnTypes      = WDTTools::detectColumnDataTypes( $namedDataArray, $is_google ? $headingsArray : $columnOrigHeaders );

    if ( empty( $columnTypes ) ) {
        foreach ( $columnOrigHeaders as $h ) {
            $columnTypes[ $h ] = 'string';
        }
    }

    $date_format     = get_option( 'wdtDateFormat' ) ? get_option( 'wdtDateFormat' ) : 'd/m/Y';
    $time_format     = get_option( 'wdtTimeFormat' ) ? get_option( 'wdtTimeFormat' ) : 'H:i:s';
    $datetime_format = $date_format . ' ' . $time_format;

    $columns = array();
    foreach ( $headingsArray as $i => $raw ) {
        $mysql_name = isset( $columnOrigHeaders[ $i ] ) ? $columnOrigHeaders[ $i ] : WDTTools::generateMySQLColumnName( $raw, array_column( $columns, 'orig_header' ) );
        $type       = isset( $columnTypes[ $mysql_name ] ) ? $columnTypes[ $mysql_name ] : 'string';
        $date_input = '';
        if ( in_array( $type, array( 'date', 'datetime', 'time' ), true ) ) {
            $date_input = ( 'datetime' === $type ) ? $datetime_format : ( ( 'time' === $type ) ? $time_format : $date_format );
        }
        $columns[] = array(
            'name'            => $raw,
            'orig_header'      => $raw,
            'type'             => $type,
            'dateInputFormat'  => $date_input,
        );
    }

    $table_data = array(
        'name'              => $title,
        'connection'        => $connection,
        'method'            => 'file',
        'columnCount'       => count( $columns ),
        'name_in_database'  => '',
        'is_used_prefix_for_db_name' => 1,
        'file'              => $file_path_or_url,
        'columns'           => $columns,
        'table_description' => '',
    );

    $constructor = new wpDataTableConstructor( $connection );

    try {
        $constructor->readFileData( $table_data );
    } catch ( Exception $e ) {
        return new \WP_Error( 'wdtmcp_import_error', $e->getMessage() );
    }

    $table_id = $constructor->getTableId();
    if ( $table_id <= 0 ) {
        return new \WP_Error( 'wdtmcp_import_error', __( 'Import completed but table ID could not be retrieved.', 'wpdatatables' ) );
    }

    return array(
        'table_id'  => (int) $table_id,
        'shortcode' => '[wpdatatable id=' . $table_id . ']',
    );
}
