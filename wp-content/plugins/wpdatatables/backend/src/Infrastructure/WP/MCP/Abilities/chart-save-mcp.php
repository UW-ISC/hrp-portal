<?php
/**
 * MCP-only chart persistence: same DB write as WPDataChart::save() but runs prepareData()
 * and groupData() so json_render_data includes table rows (Chart.js labels/datasets).
 *
 * @package wpDataTables
 */

defined( 'ABSPATH' ) or die('Access denied.');

/**
 * Chart.js pie/doughnut/polar must not use Cartesian x/y scales; core prepareRender only hides them when show_grid is off.
 *
 * @param \WPDataChart $chart
 * @return void
 */
function wdtmcp_chartjs_strip_cartesian_scales_for_radial( \WPDataChart $chart ) {
    if ( strtolower( (string) $chart->getEngine() ) !== 'chartjs' ) {
        return;
    }

    $radial_types = array(
        'chartjs_pie_chart',
        'chartjs_doughnut_chart',
        'chartjs_polar_area_chart',
    );

    if ( ! in_array( (string) $chart->getType(), $radial_types, true ) ) {
        return;
    }

    $ref = new \ReflectionObject( $chart );
    if ( ! $ref->hasProperty( '_chartjs_render_data' ) ) {
        return;
    }

    $prop = $ref->getProperty( '_chartjs_render_data' );
    $prop->setAccessible( true );
    $data = $prop->getValue( $chart );

    if ( ! is_array( $data ) || empty( $data['options']['options']['scales'] ) ) {
        return;
    }

    $scales = &$data['options']['options']['scales'];

    if ( isset( $scales['x'] ) && is_array( $scales['x'] ) ) {
        $scales['x']['display'] = false;
        if ( isset( $scales['x']['title'] ) && is_array( $scales['x']['title'] ) ) {
            $scales['x']['title']['display'] = false;
        }
    }

    if ( isset( $scales['y'] ) && is_array( $scales['y'] ) ) {
        $scales['y']['display'] = false;
        if ( isset( $scales['y']['title'] ) && is_array( $scales['y']['title'] ) ) {
            $scales['y']['title']['display'] = false;
        }
        foreach ( array( 'min', 'max', 'beginAtZero' ) as $junk ) {
            unset( $scales['y'][ $junk ] );
        }
    }

    $prop->setValue( $chart, $data );
}

/**
 * @param \WPDataChart $chart
 * @return void
 */
function wdtmcp_chart_save_persisting_table_rows( \WPDataChart $chart ) {
    global $wpdb;

    $chart->prepareSeriesData();
    $chart->prepareData();
    $chart->groupData();
    $chart->shiftXAxisColumnUp();
    $chart->prepareRender();

    wdtmcp_chartjs_strip_cartesian_scales_for_radial( $chart );

    $render_data = $chart->formRenderDataForDb();

    if ( empty( $chart->getId() ) ) {
        $ok = $wpdb->insert(
            $wpdb->prefix . 'wpdatacharts',
            array(
                'wpdatatable_id'   => $chart->getwpDataTableId(),
                'title'            => $chart->getTitle(),
                'engine'           => $chart->getEngine(),
                'type'             => $chart->getType(),
                'json_render_data' => json_encode( $render_data ),
            )
        );
        if ( false === $ok || (int) $wpdb->insert_id <= 0 ) {
            throw new \RuntimeException( 'Failed to insert chart row: ' . (string) $wpdb->last_error );
        }
        $chart->setId( (int) $wpdb->insert_id );
    } else {
        $ok = $wpdb->update(
            $wpdb->prefix . 'wpdatacharts',
            array(
                'wpdatatable_id'   => $chart->getwpDataTableId(),
                'title'            => $chart->getTitle(),
                'engine'           => $chart->getEngine(),
                'type'             => $chart->getType(),
                'json_render_data' => json_encode( $render_data ),
            ),
            array( 'id' => $chart->getId() )
        );
        if ( false === $ok ) {
            throw new \RuntimeException( 'Failed to update chart row: ' . (string) $wpdb->last_error );
        }
    }   
}
