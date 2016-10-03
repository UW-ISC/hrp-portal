<?php


/**
 * UW Button
 *
 * Button shortcode allows for styled buttons to be added to content
 * [button color='gold' type='type' url='link url' small='true']Button Text[/button]
 * optional small attribute makes the button small.  Assume regular if not present
 *
 * Options: 
 *      color: string Button color
 *      url: string The link href
 *      target: 'tab'|'' Target attribute
 *      type: string One available button type
 *      small: true|false Small button style
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.3.0
 * @package UWHR
 */

class UWHR_Button {

    private static $types = array('plus', 'go', 'external', 'play');

    function __construct() {
        add_shortcode('button', array($this, 'button_handler'));
    }

    function button_handler($atts, $content) {
        $attributes = shortcode_atts( array(
            'color' => 'none',
            'url'   => '#',
            'target' => '',
            'type'  => '',
        ), $atts );

        // Grab some values
        $color = $attributes['color'];
        $url = $attributes['url'];

        // Start the classes array
        $classes = array( 'uw-btn' );
        array_push( $classes, 'btn-' . $attributes['color'] );

        // Set btn type
        if (isset($attributes['type'])){
            $type = strtolower($attributes['type']);
            if (in_array($type, $this::$types)){
                array_push($classes, 'btn-' . $type);
            }
        }

        // Check if btn is small
        if ( in_array( 'small', $atts ) ) {
            array_push($classes, 'btn-sm');
        }

        // Link target
        if ( $attributes['target'] == 'tab' ) {
            $target = ' target="_blank"';
        } else {
            $target = '';
        }

        $class_string = implode($classes, ' ');

        // Fill with placeholder content if not text is in button
        if(empty($content)){
            $content = 'No text in this button';
        }

        return sprintf('<a class="%s" href="%s"%s>%s</a>', $class_string, $url, $target, $content);
    }
}
