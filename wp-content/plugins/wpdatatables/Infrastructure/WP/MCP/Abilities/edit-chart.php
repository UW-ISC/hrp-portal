<?php
/**
 * Ability: wpdatatables/edit-chart
 *
 * Updates an existing wpDataCharts configuration (title, type, dimensions, columns,
 * source table, axes, display flags). Chart engine cannot be changed — create a new chart instead.
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

defined( 'ABSPATH' ) or die('Access denied.');

add_action( 'wp_abilities_api_init', 'wdtmcp_register_edit_chart_ability' );

function wdtmcp_register_edit_chart_ability() {
    wp_register_ability(
        'wpdatatables/edit-chart',
        array(
            'label'       => __( 'Edit Chart', 'wpdatatables' ),
            'description' => __( 'Updates an existing wpDataChart. Change title, chart type (within the same engine), selected_columns, linked wpdatatable_id, size, responsive width, follow_filtering, group_chart, show_title, show_grid, tooltip, background_color, axis labels, range_type, row_range. Saves by rebuilding chart JSON from the merged settings (avoids empty Chart.js after type changes). Changing engine is blocked — create a new chart. REQUIRED: chart_id.', 'wpdatatables' ),
            'category'    => 'wpdatatables-data',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'chart_id' => array(
                        'type'        => 'integer',
                        'description' => 'Existing chart ID (from list-charts).',
                    ),
                    'title' => array(
                        'type'        => 'string',
                        'description' => 'Chart title.',
                    ),
                    'type' => array(
                        'type'        => 'string',
                        'description' => 'Chart type keyword (e.g. line, pie, column, bar) — resolved like create-chart. Stored JSON is rebuilt on save.',
                    ),
                    'wpdatatable_id' => array(
                        'type'        => 'integer',
                        'description' => 'Source table ID. All selected_columns must exist on the new table.',
                    ),
                    'selected_columns' => array(
                        'type'        => 'array',
                        'description' => 'Column orig_headers (min 2: categories + series).',
                        'items'       => array( 'type' => 'string' ),
                    ),
                    'width' => array( 'type' => 'integer', 'description' => 'Width in px.' ),
                    'height' => array( 'type' => 'integer', 'description' => 'Height in px.' ),
                    'responsive_width' => array( 'type' => 'boolean', 'description' => 'Responsive width.' ),
                    'follow_filtering' => array( 'type' => 'boolean', 'description' => 'React to table filters.' ),
                    'group_chart' => array( 'type' => 'boolean', 'description' => 'Group rows with same label.' ),
                    'show_title' => array( 'type' => 'boolean', 'description' => 'Show chart title.' ),
                    'show_grid' => array( 'type' => 'boolean', 'description' => 'Show grid lines.' ),
                    'tooltip_enabled' => array( 'type' => 'boolean', 'description' => 'Enable tooltips.' ),
                    'background_color' => array(
                        'type'        => 'string',
                        'description' => 'Background colour (hex, e.g. #FFFFFF).',
                    ),
                    'horizontal_axis_label' => array(
                        'type'        => 'string',
                        'description' => 'Major / horizontal axis title.',
                    ),
                    'vertical_axis_label' => array(
                        'type'        => 'string',
                        'description' => 'Minor / vertical axis title.',
                    ),
                    'range_type' => array(
                        'type'        => 'string',
                        'description' => 'Row range mode (e.g. all, pick_rows, range — same as wpDataTables chart UI).',
                    ),
                    'row_range' => array(
                        'type'        => 'array',
                        'description' => 'Row index boundaries when range_type is not "all".',
                        'items'       => array( 'type' => 'integer' ),
                    ),
                ),
                'required' => array( 'chart_id' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'chart_id'  => array( 'type' => 'integer', 'description' => 'Updated chart ID.' ),
                    'shortcode' => array( 'type' => 'string',  'description' => 'Embed shortcode.' ),
                    'updated'   => array(
                        'type'  => 'array',
                        'items' => array( 'type' => 'string' ),
                        'description' => 'Fields that were applied.',
                    ),
                ),
            ),

            'execute_callback' => 'wdtmcp_execute_edit_chart',

            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },

            'meta' => array(
                'annotations' => array(
                    'instructions' => __( 'get-chart-info → edit-chart. Engine is fixed. Use generic type names (pie, line) like create-chart.', 'wpdatatables' ),
                    'readonly'     => false,
                    'destructive'  => false,
                    'idempotent'   => true,
                ),
            ),
        )
    );
}

/**
 * Ensure selected column headers exist on the table.
 *
 * @param int   $wpdatatable_id
 * @param array $selected_columns
 * @return true|\WP_Error
 */
function wdtmcp_validate_chart_columns_for_table( $wpdatatable_id, array $selected_columns ) {
    if ( count( $selected_columns ) < 2 ) {
        return new \WP_Error(
            'wdtmcp_invalid_input',
            __( 'At least two selected_columns are required (label and value).', 'wpdatatables' )
        );
    }

    if ( ! class_exists( 'WPDataTable' ) ) {
        return new \WP_Error( 'wdtmcp_missing_class', __( 'WPDataTable is not available.', 'wpdatatables' ) );
    }

    try {
        $table = \WPDataTable::loadWpDataTable( (int) $wpdatatable_id, null, true );
    } catch ( \Exception $e ) {
        return new \WP_Error(
            'wdtmcp_table_load_failed',
            sprintf( __( 'Could not load source table: %s', 'wpdatatables' ), $e->getMessage() )
        );
    }

    $headers = array();
    foreach ( $table->getColumns() as $column ) {
        $headers[] = $column->getOriginalHeader();
    }

    foreach ( $selected_columns as $column_header ) {
        if ( ! in_array( $column_header, $headers, true ) ) {
            return new \WP_Error(
                'wdtmcp_invalid_input',
                sprintf(
                    /* translators: 1: column, 2: table id */
                    __( 'Column "%1$s" was not found on table %2$d.', 'wpdatatables' ),
                    $column_header,
                    $wpdatatable_id
                )
            );
        }
    }

    return true;
}

/**
 * Export current chart object state to the array shape expected by WPDataChart::build( ..., false ).
 * Used to fully regenerate json_render_data after loadFromDB + setters (avoids pie/bar desync).
 *
 * @param \WPDataChart $chart Chart instance (already patched).
 * @return array
 */
function wdtmcp_chart_construct_array_from_instance( \WPDataChart $chart ) {
    $out = array(
        'id'                    => (int) $chart->getId(),
        'title'                 => (string) $chart->getTitle(),
        'wpdatatable_id'        => (int) $chart->getwpDataTableId(),
        'engine'                => (string) $chart->getEngine(),
        'type'                  => (string) $chart->getType(),
        'selected_columns'      => $chart->getSelectedColumns(),
        'range_type'            => (string) $chart->getRangeType(),
        'follow_filtering'      => (bool) $chart->getFollowFiltering(),
        'width'                 => (int) $chart->getWidth(),
        'height'                => (int) $chart->getHeight(),
        'responsive_width'      => (bool) $chart->isResponsiveWidth(),
        'group_chart'           => (bool) $chart->isGroupChart(),
        'background_color'      => (string) $chart->getBackgroundColor(),
        'show_grid'             => (bool) $chart->isShowGrid(),
        'show_title'            => (bool) $chart->isShowTitle(),
        'tooltip_enabled'       => (bool) $chart->isTooltipEnabled(),
        'horizontal_axis_label' => (string) $chart->getMajorAxisLabel(),
        'vertical_axis_label'   => (string) $chart->getMinorAxisLabel(),
        'vertical_axis_min'     => null !== $chart->getVerticalAxisMin() ? (string) $chart->getVerticalAxisMin() : '',
        'vertical_axis_max'     => null !== $chart->getVerticalAxisMax() ? (string) $chart->getVerticalAxisMax() : '',
        'series_type'           => (string) $chart->getSeriesType(),
        'loader'                => (bool) $chart->isLoaderVisible(),
    );

    $rr = $chart->getRowRange();
    if ( ! empty( $rr ) ) {
        $out['range_data'] = $rr;
    }

    $series_data = $chart->getUserDefinedSeriesData();
    if ( ! empty( $series_data ) && is_array( $series_data ) ) {
        $out['series_data'] = $series_data;
    }

    $loader_cs = $chart->getChartLoaderColorSettings();
    if ( '' !== (string) $loader_cs ) {
        $out['chartLoaderColorSettings'] = $loader_cs;
    }

    $loader_anim = $chart->getChartLoaderAnimationColorSettings();
    if ( '' !== (string) $loader_anim ) {
        $out['chartLoaderAnimationColorSettings'] = $loader_anim;
    }

    return $out;
}

/**
 * Execute wpdatatables/edit-chart.
 *
 * @param array $input
 * @return array|\WP_Error
 */
function wdtmcp_execute_edit_chart( $input ) {
    $chart_id = isset( $input['chart_id'] ) ? (int) $input['chart_id'] : 0;
    if ( $chart_id <= 0 ) {
        return new \WP_Error( 'wdtmcp_invalid_input', __( 'chart_id is required.', 'wpdatatables' ) );
    }

    if ( ! class_exists( 'WPDataChart' ) || ! function_exists( 'wdtmcp_resolve_chart_type' ) ) {
        return new \WP_Error(
            'wdtmcp_missing_class',
            __( 'Chart helpers are not loaded (create-chart must be registered).', 'wpdatatables' )
        );
    }

    $row = \WPDataChart::getChartDataById( $chart_id );
    if ( empty( $row ) ) {
        return new \WP_Error(
            'wdtmcp_not_found',
            sprintf( __( 'Chart with ID %d was not found.', 'wpdatatables' ), $chart_id )
        );
    }

    $stored_engine = isset( $row->engine ) ? strtolower( (string) $row->engine ) : '';
    if ( isset( $input['engine'] ) ) {
        $req_engine = strtolower( sanitize_text_field( $input['engine'] ) );
        if ( $req_engine !== $stored_engine ) {
            return new \WP_Error(
                'wdtmcp_chart_engine_locked',
                __( 'Changing chart engine is not supported. Create a new chart with the desired engine.', 'wpdatatables' )
            );
        }
    }

    $built = \WPDataChart::build(
        array(
            'id'               => $chart_id,
            'wpdatatable_id'   => (int) $row->wpdatatable_id,
            'engine'           => $stored_engine,
        ),
        true
    );

    /** @var \WPDataChart $chart */
    $chart    = $built;
    $updated  = array();
    $table_id = (int) $chart->getwpDataTableId();

    if ( isset( $input['wpdatatable_id'] ) ) {
        $new_tid = (int) $input['wpdatatable_id'];
        if ( $new_tid > 0 && $new_tid !== $table_id ) {
            global $wpdb;
            $exists = (int) $wpdb->get_var(
                $wpdb->prepare(
                    'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wpdatatables WHERE id = %d',
                    $new_tid
                )
            );
            if ( $exists < 1 ) {
                return new \WP_Error(
                    'wdtmcp_not_found',
                    sprintf( __( 'Table with ID %d was not found.', 'wpdatatables' ), $new_tid )
                );
            }
            $chart->setwpDataTableId( $new_tid );
            $table_id = $new_tid;
            $chart->loadChildWPDataTable();
            $updated[] = 'wpdatatable_id';
        }
    }

    if ( isset( $input['title'] ) ) {
        $chart->setTitle( sanitize_text_field( $input['title'] ) );
        $updated[] = 'title';
    }

    if ( isset( $input['type'] ) && '' !== trim( (string) $input['type'] ) ) {
        $resolved = wdtmcp_resolve_chart_type( $stored_engine, (string) $input['type'] );
        if ( '' !== $resolved ) {
            $chart->setType( $resolved );
            $updated[] = 'type';
        }
    }

    $columns_to_check = null;
    if ( isset( $input['selected_columns'] ) && is_array( $input['selected_columns'] ) ) {
        $cols = array_values(
            array_filter(
                array_map( 'sanitize_text_field', $input['selected_columns'] ),
                function ( $h ) {
                    return '' !== $h;
                }
            )
        );
        $check              = wdtmcp_validate_chart_columns_for_table( $table_id, $cols );
        if ( is_wp_error( $check ) ) {
            return $check;
        }
        $chart->setSelectedColumns( $cols );
        $updated[] = 'selected_columns';
        $columns_to_check = $cols;
    }

    if ( isset( $input['width'] ) ) {
        $chart->setWidth( max( 50, min( 4000, (int) $input['width'] ) ) );
        $updated[] = 'width';
    }
    if ( isset( $input['height'] ) ) {
        $chart->setHeight( max( 50, min( 4000, (int) $input['height'] ) ) );
        $updated[] = 'height';
    }
    if ( isset( $input['responsive_width'] ) ) {
        $chart->setResponsiveWidth( (bool) $input['responsive_width'] );
        $updated[] = 'responsive_width';
    }
    if ( isset( $input['follow_filtering'] ) ) {
        $chart->setFollowFiltering( (bool) $input['follow_filtering'] );
        $updated[] = 'follow_filtering';
    }
    if ( isset( $input['group_chart'] ) ) {
        $chart->setGroupChart( (bool) $input['group_chart'] );
        $updated[] = 'group_chart';
    }
    if ( isset( $input['show_title'] ) ) {
        $chart->setShowTitle( (bool) $input['show_title'] );
        $updated[] = 'show_title';
    }
    if ( isset( $input['show_grid'] ) ) {
        $chart->setShowGrid( (bool) $input['show_grid'] );
        $updated[] = 'show_grid';
    }
    if ( isset( $input['tooltip_enabled'] ) ) {
        $chart->setTooltipEnabled( (bool) $input['tooltip_enabled'] );
        $updated[] = 'tooltip_enabled';
    }
    if ( isset( $input['background_color'] ) ) {
        $chart->setBackgroundColor( sanitize_text_field( $input['background_color'] ) );
        $updated[] = 'background_color';
    }
    if ( isset( $input['horizontal_axis_label'] ) ) {
        $chart->setMajorAxisLabel( sanitize_text_field( $input['horizontal_axis_label'] ) );
        $updated[] = 'horizontal_axis_label';
    }
    if ( isset( $input['vertical_axis_label'] ) ) {
        $chart->setMinorAxisLabel( sanitize_text_field( $input['vertical_axis_label'] ) );
        $updated[] = 'vertical_axis_label';
    }
    if ( isset( $input['range_type'] ) && '' !== trim( (string) $input['range_type'] ) ) {
        $chart->setRangeType( sanitize_text_field( $input['range_type'] ) );
        $updated[] = 'range_type';
    }
    if ( isset( $input['row_range'] ) && is_array( $input['row_range'] ) ) {
        $chart->setRowRange( array_values( array_map( 'intval', $input['row_range'] ) ) );
        $updated[] = 'row_range';
    }

    // If table link changed but columns not supplied, ensure current columns still valid.
    if ( in_array( 'wpdatatable_id', $updated, true ) && null === $columns_to_check ) {
        $current = $chart->getSelectedColumns();
        $check   = wdtmcp_validate_chart_columns_for_table( $table_id, $current );
        if ( is_wp_error( $check ) ) {
            return new \WP_Error(
                'wdtmcp_invalid_input',
                sprintf(
                    /* translators: %d: table id */
                    __( 'wpdatatable_id changed; existing columns do not match table %d. Pass selected_columns.', 'wpdatatables' ),
                    $table_id
                )
            );
        }
    }

    if ( empty( $updated ) ) {
        return new \WP_Error(
            'wdtmcp_invalid_input',
            __( 'No editable fields provided. Pass at least one property to update besides chart_id.', 'wpdatatables' )
        );
    }

    try {
        $construct = wdtmcp_chart_construct_array_from_instance( $chart );
        $chart     = \WPDataChart::build( $construct, false );
        if ( $chart->isResponsiveWidth() && (int) $chart->getWidth() < 200 ) {
            $chart->setWidth( 400 );
        }
        if ( function_exists( 'wdtmcp_chart_save_persisting_table_rows' ) ) {
            wdtmcp_chart_save_persisting_table_rows( $chart );
        } else {
            $chart->save();
        }
    } catch ( \Exception $e ) {
        return new \WP_Error(
            'wdtmcp_chart_save_failed',
            sprintf( __( 'Failed to save chart: %s', 'wpdatatables' ), $e->getMessage() )
        );
    }

    return array(
        'chart_id'  => $chart_id,
        'shortcode' => $chart->getShortCode(),
        'updated'   => array_values( array_unique( $updated ) ),
    );
}
