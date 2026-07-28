<?php
/**
 * Ability: wpdatatables/create-table-from-source
 *
 * Creates a new wpDataTable linked to an external data source: a URL to a CSV,
 * JSON, XML file, a nested-JSON API endpoint, an Excel file path, or a Google
 * Spreadsheet URL. The table reads data live from the source on each render.
 *
 * Available on all tiers for public sources. Private Google Sheets and nested
 * JSON with authentication require Starter+.
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_create_table_from_source_ability' );

function wdtmcp_register_create_table_from_source_ability() {
    wp_register_ability(
        'wpdatatables/create-table-from-source',
        array(
            'label'       => __( 'Create Table from Data Source', 'wpdatatables' ),
            'description' => __( 'Creates a sortable DataTable from CSV/Excel/JSON/XML/Google Sheets. For local files use upload-data-file (base64 via MCP) or upload via Media Library and pass attachment_id. Use source_url only for public remote URLs. Google Sheets requires a public sheet URL or configured Google API. Large CSV files may take 30–60s. WHEN TO CALL: user has tabular data in a file or API and wants a live, sortable/filterable wpDataTable. REQUIRED: title, source_type. PROVIDE ONE OF: attachment_id OR source_url (public remote). FOR nested_json: nested_json_params.root_path. RETURNS: table_id and shortcode.', 'wpdatatables' ),
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
                        'description' => 'Data source type. json = array at root [{...}]. nested_json = nested structure, use root_path. xls = Excel; remote URLs are auto-downloaded to Media Library. google_spreadsheet = URL or API.',
                        'enum'        => array( 'csv', 'xls', 'json', 'nested_json', 'xml', 'google_spreadsheet' ),
                    ),
                    'source_url' => array(
                        'type'        => 'string',
                        'description' => 'Public URL only (remote files/APIs). NO localhost, NO 127.0.0.1. For local files use attachment_id instead.',
                    ),
                    'attachment_id' => array(
                        'type'        => 'integer',
                        'description' => 'WordPress media attachment ID. Upload first via upload-data-file (base64), WordPress admin Media → Add New, or REST /wp/v2/media, then pass the returned id here.',
                    ),
                    'nested_json_params' => array(
                        'type'        => 'object',
                        'description' => 'For nested_json only: url (or use source_url), root_path (required: path to rows array using -> e.g. data->items or results->data), http_method (GET|POST), auth_headers [{name,value}], auth_option, username, password.',
                    ),
                    'cache_source_data' => array(
                        'type'        => 'boolean',
                        'description' => 'Whether to cache the source data (default false).',
                    ),
                    'header_row' => array(
                        'type'        => 'integer',
                        'description' => '1-based row number containing column headers (default 1). Use when the file has title rows above headers, e.g. 4 for worklog exports where row 4 is Category, Project, Issue, etc.',
                        'minimum'     => 1,
                    ),
                ),
                'required' => array( 'title', 'source_type' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'table_id'  => array( 'type' => 'integer', 'description' => 'Created table ID.' ),
                    'shortcode' => array( 'type' => 'string',  'description' => 'WordPress shortcode.' ),
                    'table_type' => array( 'type' => 'string', 'description' => 'Resulting table type.' ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_create_table_from_source',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'Call get-system-info first. Provide source_url for public remote files or attachment_id for Media Library uploads. Creates a live table that re-reads the source on each page load.', 'wpdatatables' ),
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => false,
                ),
            ),
        )
    );
}

/**
 * Build default table config for source-based tables.
 *
 * @return stdClass
 */
function wdtmcp_default_source_table_config() {
    $t = new stdClass();
    $t->id = null;
    $t->title = '';
    $t->show_title = 1;
    $t->table_description = '';
    $t->show_table_description = 0;
    $t->table_type = '';
    $t->connection = '';
    $t->content = '';
    $t->file_location = 'wp_any_url';
    $t->sorting = 1;
    $t->fixed_layout = 0;
    $t->word_wrap = 0;
    $t->tools = 1;
    $t->display_length = 10;
    $t->hide_before_load = 1;
    $t->tabletools_config = array( 'print' => 1, 'copy' => 1, 'excel' => 1, 'csv' => 1, 'pdf' => 0 );
    $t->filtering = 1;
    $t->filtering_form = 0;
    $t->cache_source_data = 0;
    $t->auto_update_cache = 0;
    $t->responsive = 1;
    $t->scrollable = 0;
    $t->server_side = 0;
    $t->auto_refresh = 0;
    $t->editable = 0;
    $t->inline_editing = 0;
    $t->popover_tools = 0;
    $t->editor_roles = '';
    $t->mysql_table_name = '';
    $t->edit_only_own_rows = 0;
    $t->userid_column_id = null;
    $t->var1 = $t->var2 = $t->var3 = $t->var4 = $t->var5 = '';
    $t->var6 = $t->var7 = $t->var8 = $t->var9 = '';
    $t->info_block = 1;
    $t->showTableToolsIncludeHTML = 0;
    $t->showTableToolsIncludeTitle = 0;
    $t->responsiveAction = 'icon';
    $t->pagination_top = 0;
    $t->pagination = 1;
    $t->paginationAlign = 'right';
    $t->paginationLayout = 'full_numbers';
    $t->paginationLayoutMobile = 'simple';
    $t->global_search = 1;
    $t->showRowsPerPage = true;
    $t->showAllRows = false;
    $t->clearFilters = 0;
    $t->simpleResponsive = 0;
    $t->simpleHeader = 0;
    $t->stripeTable = 0;
    $t->cellPadding = 10;
    $t->removeBorders = 0;
    $t->borderCollapse = 'collapse';
    $t->borderSpacing = 0;
    $t->verticalScroll = 0;
    $t->verticalScrollHeight = 600;
    $t->editButtonsDisplayed = array( 'all' );
    $t->enableDuplicateButton = 0;
    $t->language = '';
    $t->tableSkin = '';
    $t->table_wcag = 0;
    $t->tableBorderRemoval = 0;
    $t->tableBorderRemovalHeader = 0;
    $t->tableCustomCss = '';
    $t->tableFontColorSettings = array();
    $t->pdfPaperSize = 'A4';
    $t->pdfPageOrientation = 'portrait';
    $t->fixed_columns = 0;
    $t->fixed_left_columns_number = 0;
    $t->fixed_right_columns_number = 0;
    $t->fixed_header = 0;
    $t->fixed_header_offset = 0;
    $t->simple_template_id = 0;
    $t->customRowDisplay = '';
    $t->loader = (int) get_option( 'wdtGlobalTableLoader', 1 );
    $t->showCartInformation = 1;
    $t->index_column = 0;
    $t->columns = array();
    return $t;
}

/**
 * Ensure an http(s) URL is allowed for remote fetch (no SSRF to private/reserved ranges).
 *
 * Uses {@see wp_http_validate_url()}, then resolves the host to all A/AAAA addresses
 * (with IPv4 fallbacks) and rejects if any address is private, reserved, or invalid.
 *
 * @param string $url Full URL string.
 * @return true|\WP_Error True on success; \WP_Error with code wdtmcp_url_blocked on failure.
 */
function wdtmcp_validate_public_remote_source_url( $url ) {
    $blocked = new \WP_Error(
        'wdtmcp_url_blocked',
        __(
            'This URL cannot be used. Only public http(s) URLs are allowed — local addresses, private networks, and invalid URLs are blocked. For local files, upload via Media Library or MCP and pass attachment_id.',
            'wpdatatables'
        )
    );

    if ( ! is_string( $url ) || '' === trim( $url ) ) {
        return $blocked;
    }

    if ( ! function_exists( 'wp_http_validate_url' ) ) {
        return $blocked;
    }

    $validated = wp_http_validate_url( trim( $url ) );
    if ( false === $validated ) {
        return $blocked;
    }

    $parsed = wp_parse_url( $validated );
    if ( empty( $parsed['host'] ) ) {
        return $blocked;
    }

    $host = $parsed['host'];
    if ( '[' === substr( $host, 0, 1 ) && ']' === substr( $host, -1 ) ) {
        $host = substr( $host, 1, -1 );
    }

    $ip_flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
    $ips      = array();

    if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
        $ips[] = $host;
    } elseif ( function_exists( 'dns_get_record' ) ) {
        $a = @dns_get_record( $host, DNS_A );
        if ( is_array( $a ) ) {
            foreach ( $a as $row ) {
                if ( ! empty( $row['ip'] ) ) {
                    $ips[] = $row['ip'];
                }
            }
        }
        $aaaa = @dns_get_record( $host, DNS_AAAA );
        if ( is_array( $aaaa ) ) {
            foreach ( $aaaa as $row ) {
                if ( ! empty( $row['ipv6'] ) ) {
                    $ips[] = $row['ipv6'];
                }
            }
        }
    }

    if ( empty( $ips ) && function_exists( 'gethostbynamel' ) ) {
        $list = @gethostbynamel( $host );
        if ( is_array( $list ) ) {
            foreach ( $list as $ipv4 ) {
                if ( is_string( $ipv4 ) && '' !== $ipv4 ) {
                    $ips[] = $ipv4;
                }
            }
        }
    }

    if ( empty( $ips ) ) {
        $gh = @gethostbyname( $host );
        if ( is_string( $gh ) && $gh !== $host && filter_var( $gh, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
            $ips[] = $gh;
        }
    }

    $ips = array_unique( $ips );

    if ( empty( $ips ) ) {
        return new \WP_Error(
            'wdtmcp_url_blocked',
            __(
                'Could not resolve the URL host to a public address. Check the hostname or use attachment_id for local files.',
                'wpdatatables'
            )
        );
    }

    foreach ( $ips as $ip ) {
        if ( false === filter_var( $ip, FILTER_VALIDATE_IP, $ip_flags ) ) {
            return $blocked;
        }
    }

    return true;
}

/**
 * Download a remote data file (CSV, Excel) to the WordPress Media Library.
 * wpDataTables can struggle with remote URLs (e.g. Dropbox, Google Drive).
 * Fetching and storing locally ensures reliable access.
 *
 * @param string $url Remote URL to .csv, .xls, .xlsx, or .ods file.
 * @param string $title Optional title for the media attachment.
 * @param string $source_type Optional source_type (csv, xls) for fallback extension.
 * @return int|WP_Error Attachment ID on success, WP_Error on failure.
 */
function wdtmcp_download_data_file_to_media_library( $url, $title = '', $source_type = '' ) {
    $url_check = wdtmcp_validate_public_remote_source_url( $url );
    if ( is_wp_error( $url_check ) ) {
        return $url_check;
    }

    $tmp = download_url( $url, 60 );
    if ( is_wp_error( $tmp ) ) {
        return $tmp;
    }

    $path_parts = pathinfo( $url );
    $ext        = isset( $path_parts['extension'] ) ? strtolower( $path_parts['extension'] ) : '';
    $allowed    = array( 'csv', 'xls', 'xlsx', 'ods' );
    if ( '' === $ext || ! in_array( $ext, $allowed, true ) ) {
        $ext = ( 'csv' === $source_type ) ? 'csv' : 'xlsx';
    }
    $filename = isset( $path_parts['filename'] ) ? sanitize_file_name( $path_parts['filename'] ) . '.' . $ext : 'data-import.' . $ext;

    $file_array = array(
        'name'     => $filename,
        'tmp_name' => $tmp,
    );

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $id = media_handle_sideload( $file_array, 0, $title );
    if ( is_wp_error( $id ) ) {
        @unlink( $tmp );
        return $id;
    }
    return $id;
}

/**
 * Execute callback for wpdatatables/create-table-from-source.
 *
 * @param array $input
 * @return array|WP_Error
 */
function wdtmcp_execute_create_table_from_source( $input ) {
    global $wpdb;

    $bootstrap = wdtmcp_bootstrap_file_abilities();
    if ( is_wp_error( $bootstrap ) ) {
        return $bootstrap;
    }

    if ( ! class_exists( 'WDTConfigController' ) || ! class_exists( 'WPDataTable' ) ) {
        return new \WP_Error(
            'wdtmcp_missing_class',
            __( 'wpDataTables core classes are required.', 'wpdatatables' )
        );
    }

    $allowed_types = array( 'csv', 'xls', 'json', 'nested_json', 'xml', 'google_spreadsheet' );
    $source_type   = isset( $input['source_type'] ) ? sanitize_text_field( $input['source_type'] ) : '';
    $source_url   = isset( $input['source_url'] ) ? trim( (string) $input['source_url'] ) : '';
    $attachment_id = isset( $input['attachment_id'] ) ? (int) $input['attachment_id'] : 0;
    $title        = isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : '';
    $cache        = isset( $input['cache_source_data'] ) ? (bool) $input['cache_source_data'] : false;
    $header_row   = isset( $input['header_row'] ) ? max( 1, (int) $input['header_row'] ) : 1;
    $nested_params = isset( $input['nested_json_params'] ) && is_array( $input['nested_json_params'] ) ? $input['nested_json_params'] : array();

    if ( '' === $title ) {
        return new \WP_Error( 'wdtmcp_missing_param', __( 'title is required.', 'wpdatatables' ) );
    }
    if ( ! in_array( $source_type, $allowed_types, true ) ) {
        return new \WP_Error( 'wdtmcp_invalid_type', __( 'Invalid source_type. Use: csv, xls, json, nested_json, xml, google_spreadsheet.', 'wpdatatables' ) );
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
            'wpdatatables/create-table-from-source'
        );
    }

    if ( 'nested_json' === $source_type ) {
        if ( $attachment_id > 0 ) {
            return new \WP_Error( 'wdtmcp_invalid_param', __( 'attachment_id is not supported for nested_json. Use source_url or nested_json_params.url.', 'wpdatatables' ) );
        }
        $nested_url = isset( $nested_params['url'] ) ? trim( (string) $nested_params['url'] ) : $source_url;
        if ( '' === $nested_url ) {
            return new \WP_Error( 'wdtmcp_missing_param', __( 'source_url or nested_json_params.url is required for nested_json.', 'wpdatatables' ) );
        }
        $root_path = isset( $nested_params['root_path'] ) ? trim( (string) $nested_params['root_path'] ) : '';
        if ( '' === $root_path ) {
            return new \WP_Error( 'wdtmcp_missing_param', __( 'nested_json_params.root_path is required for nested JSON. Use -> to separate keys (e.g. data->items). Use source_type json only when the API returns an array at root.', 'wpdatatables' ) );
        }
    } elseif ( $attachment_id > 0 ) {
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

    $url_to_check = $source_url;
    if ( 'nested_json' === $source_type && ! empty( $nested_params['url'] ) ) {
        $url_to_check = $nested_params['url'];
    }
    if ( '' !== $url_to_check && $attachment_id <= 0 ) {
        $url_check = wdtmcp_validate_public_remote_source_url( $url_to_check );
        if ( is_wp_error( $url_check ) ) {
            return $url_check;
        }
    }

    $use_media_lib = $attachment_id > 0;
    $normalized_tmp = '';

    $file_types = array( 'csv', 'xls' );
    if ( in_array( $source_type, $file_types, true ) && $attachment_id <= 0 && '' !== $source_url ) {
        $downloaded_id = wdtmcp_download_data_file_to_media_library( $source_url, $title, $source_type );
        if ( is_wp_error( $downloaded_id ) ) {
            return $downloaded_id;
        }
        if ( $downloaded_id > 0 ) {
            $source_url     = wp_get_attachment_url( $downloaded_id );
            $use_media_lib  = true;
        }
    }

    if ( $header_row > 1 && in_array( $source_type, array( 'csv', 'xls' ), true ) ) {
        $local_path = false;
        if ( $attachment_id > 0 ) {
            $local_path = wdtmcp_get_attachment_local_path( $attachment_id );
        } elseif ( '' !== $source_url && class_exists( 'wpDataTableConstructor' ) ) {
            $local_path = wpDataTableConstructor::isUploadedFileEmpty( $source_url );
        }

        if ( ! $local_path ) {
            return new \WP_Error(
                'wdtmcp_missing_local_file',
                __( 'header_row > 1 requires a local file (attachment_id or media library path).', 'wpdatatables' )
            );
        }

        $normalized = wdtmcp_normalize_tabular_file_header_row( $local_path, $header_row );
        if ( is_wp_error( $normalized ) ) {
            return $normalized;
        }

        if ( $normalized !== $local_path ) {
            $normalized_tmp = $normalized;
            $uploaded       = wdtmcp_upload_data_file_to_media_library(
                (string) file_get_contents( $normalized ),
                basename( $local_path ),
                $title
            );
            @unlink( $normalized );

            if ( is_wp_error( $uploaded ) ) {
                return $uploaded;
            }

            $attachment_id = (int) $uploaded;
            $source_url    = wp_get_attachment_url( $attachment_id );
            if ( ! $source_url ) {
                return new \WP_Error( 'wdtmcp_upload_failed', __( 'Failed to store normalized source file.', 'wpdatatables' ) );
            }
            $use_media_lib = true;
        }
    }

    $table = wdtmcp_default_source_table_config();
    $table->title = $title;
    $table->table_type = $source_type;
    $table->cache_source_data = $cache ? 1 : 0;
    $table->columns = array();

    if ( 'nested_json' === $source_type ) {
        $json_params = new stdClass();
        $json_params->url = isset( $nested_params['url'] ) ? esc_url_raw( trim( $nested_params['url'] ) ) : $source_url;
        $json_params->method = isset( $nested_params['http_method'] ) ? strtoupper( sanitize_text_field( $nested_params['http_method'] ) ) : 'GET';
        if ( ! in_array( $json_params->method, array( 'GET', 'POST' ), true ) ) {
            $json_params->method = 'GET';
        }
        $json_params->root = isset( $nested_params['root_path'] ) ? sanitize_text_field( $nested_params['root_path'] ) : '';
        $json_params->authOption = isset( $nested_params['auth_option'] ) ? sanitize_text_field( $nested_params['auth_option'] ) : '';
        $json_params->username = isset( $nested_params['username'] ) ? sanitize_text_field( $nested_params['username'] ) : '';
        $json_params->password = isset( $nested_params['password'] ) ? sanitize_text_field( $nested_params['password'] ) : '';
        $json_params->customHeaders = array();
        if ( ! empty( $nested_params['auth_headers'] ) && is_array( $nested_params['auth_headers'] ) ) {
            foreach ( $nested_params['auth_headers'] as $h ) {
                $obj = new stdClass();
                $obj->setKeyName = isset( $h['name'] ) ? sanitize_text_field( $h['name'] ) : ( isset( $h['key'] ) ? sanitize_text_field( $h['key'] ) : '' );
                $obj->setKeyValue = isset( $h['value'] ) ? sanitize_textarea_field( $h['value'] ) : '';
                if ( '' !== $obj->setKeyName ) {
                    $json_params->customHeaders[] = $obj;
                }
            }
        }
        $table->jsonAuthParams = $json_params;
    } else {
        $table->content = esc_url_raw( $source_url );
        $table->file_location = $use_media_lib ? 'wp_media_lib' : 'wp_any_url';
    }

    if ( ! isset( $_POST['file'] ) ) {
        $_POST['file'] = '';
    }
    if ( ! isset( $_POST['fileSourceAction'] ) ) {
        $_POST['fileSourceAction'] = '';
    }

    $table = WDTConfigController::sanitizeTableConfig( $table );

    $res = WDTConfigController::tryCreateTable(
        $table->table_type,
        $table->content,
        $table->connection,
        $table->file_location
    );

    if ( ! empty( $res->error ) ) {
        return new \WP_Error( 'wdtmcp_source_error', $res->error );
    }

    $table->title = $title;
    if ( ! $use_media_lib ) {
        $table->file_location = 'wp_any_url';
    }
    $table->cache_source_data = $cache ? 1 : 0;

    WDTConfigController::saveTableToDB( $table );

    if ( '' !== $wpdb->last_error ) {
        return new \WP_Error( 'wdtmcp_db_error', $wpdb->last_error );
    }

    $table_id = isset( $table->id ) ? (int) $table->id : (int) $wpdb->insert_id;
    if ( $table_id <= 0 ) {
        return new \WP_Error( 'wdtmcp_db_error', __( 'Failed to get created table ID.', 'wpdatatables' ) );
    }

    try {
        WDTConfigController::saveColumns( $table->columns, $res->table, $table_id );
    } catch ( Exception $e ) {
        return new \WP_Error( 'wdtmcp_columns_error', $e->getMessage() );
    }

    if ( count( $res->table->getDataRows() ) > 2000 ) {
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

    return array(
        'table_id'   => $table_id,
        'shortcode'  => '[wpdatatable id=' . $table_id . ']',
        'table_type' => $table->table_type,
    );
}
