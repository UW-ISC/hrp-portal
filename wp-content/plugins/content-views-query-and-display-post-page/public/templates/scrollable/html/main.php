<?php

/**
 * Layout Name: Scrollable List
 *
 * @package   PT_Content_Views
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
$html = array();

$ex_cap_cls = PT_CV_PREFIX . 'cap-w-img';

$img = strip_tags( isset( $fields_html[ 'thumbnail' ] ) ? $fields_html[ 'thumbnail' ] : '', '<img>' );
if ( !empty( $img ) ) {
	// Thumbnail html
	$html[] = $fields_html[ 'thumbnail' ];
	unset( $fields_html[ 'thumbnail' ] );
} else {
	$ex_cap_cls = PT_CV_PREFIX . 'cap-wo-img';
}

// Other fields html
$others_html = implode( "\n", $fields_html );

// Get wrapper class of caption
$caption_class	 = apply_filters( PT_CV_PREFIX_ . 'scrollable_caption_class', array( PT_CV_PREFIX . 'carousel-caption', $ex_cap_cls ) );
$html[]			 = sprintf( '<div class="%s">%s</div>', esc_attr( implode( ' ', array_filter( $caption_class ) ) ), $others_html );

echo implode( "\n", $html );
