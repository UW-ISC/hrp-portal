<?php

/**
 * Layout Name: Grid
 *
 * @package   PT_Content_Views
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
$html			 = array();
$o_fields_html	 = $fields_html;
$layout			 = $dargs[ 'layout-format' ];

if ( $layout == '2-col' && !isset( $dargs[ 'field-settings' ][ 'thumbnail' ] ) ) {
	$layout = '1-col';
}

if ( $layout == '2-col' ) {
	$thumbnail = $fields_html[ 'thumbnail' ];
	unset( $fields_html[ 'thumbnail' ] );

	if ( apply_filters( PT_CV_PREFIX_ . '2col_separate', false ) ) {
		$fields_html = array( sprintf( '<div class="%s">%s</div>', PT_CV_PREFIX . '2colse', implode( '', $fields_html ) ) );
	}

	array_unshift( $fields_html, $thumbnail );
}

$html = $fields_html;
echo apply_filters( PT_CV_PREFIX_ . 'grid_item', implode( "\n", $html ), $o_fields_html );
