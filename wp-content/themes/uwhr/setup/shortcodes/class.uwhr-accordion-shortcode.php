<?php


/**
 * UWHR Accordion
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.3.0
 * @package UWHR
 */

class UWHR_Accordion {
    function __construct() {
        add_shortcode( 'accordion', array( $this, 'accordion_handler' ) );
        add_shortcode( 'panel', array( $this, 'panel_handler' ) );
    }

    function accordion_handler($atts, $content) {
        $attributes = shortcode_atts( array(
            'icon'  => true,
            'branded'   => false
        ), $atts );

        $classes = array( 'uwhr-accordion' );

        if ( ! $attributes['icon'] ) {
            array_push( $classes, 'accordion-iconless' );
        }

        if ( $attributes['branded'] ) {
            array_push( $classes, 'accordion-branded' );
        }

        $class_string = implode($classes, ' ');

        $accordion = '<div class="' . $class_string . '" id="accordion">' . do_shortcode($content) . '</div>';

        return $accordion;
    }

    function panel_handler($atts, $content = null) {
        $attributes = shortcode_atts( array(
            'title' => '',
        ), $atts );

        $panel = '<div class="panel panel-default">';
            $panel .= '<div id="headingTwo" class="panel-heading">';
                $panel .= '<h4 class="panel-title"><a class="panel-link collapsed" href="#collapseTwo" data-toggle="collapse" data-parent="#accordion">' . $attributes['title'];
                    $panel .= ' <i class="fa panel-title-icon"></i>';
                $panel .= '</a></h4>';
            $panel .= '</div>';
            $panel .= '<div id="collapseTwo" class="panel-collapse collapse">';
                $panel .= $content;
            $panel .= '</div>';
        $panel .= '</div>';

        return $panel;
    }
}
