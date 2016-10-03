<?php

/**
 * Styles
 *
 * Register and enqueue styles for the front-end and admin of the site
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

class UWHR_Styles {

    public $styles;

    function __construct() {

        $ver = wp_get_theme('uwhr')->version;

        $this->styles = array(

            'google-font-open' => array(
                'id'      => 'google-font-open',
                'url'     => 'https://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700',
                'deps'    => array(),
                'version' => '3.6',
                'admin'   => true
            ),

            'uwhr-admin' => array (
                'id'      => 'uwhr-admin',
                'url'     => get_template_directory_uri() . '/assets/admin/css/admin.css',
                'deps'    => array(),
                'version' => $ver,
                'admin'   => true
            ),

            'main' => array (
                'id'      => 'main',
                'url'     => get_template_directory_uri() . '/assets/main.min.css',
                'deps'    => array(),
                'version' => $ver
            ),

        );

        add_action( 'wp_enqueue_scripts', array( $this, 'register_default_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_default_styles' ) );
        add_action( 'admin_head', array( $this, 'enqueue_admin_styles' ) );
    }

    function register_default_styles() {
        foreach ( $this->styles as $style ) {
            $style = (object) $style;

            wp_register_style(
                $style->id,
                $style->url,
                $style->deps,
                $style->version
            );

        }
    }

    function enqueue_default_styles() {
        foreach ( $this->styles as $style ) {
            $style = (object) $style;
            wp_enqueue_style( $style->id );
        }
    }

    function enqueue_admin_styles() {
        if ( ! is_admin() ) {
            return;
        }

        foreach ( $this->styles as $style ) {
            $style = (object) $style;

            if ( array_key_exists( 'admin', $style) && $style->admin ) {
                wp_register_style(
                    $style->id,
                    $style->url,
                    $style->deps,
                    $style->version
                );

                wp_enqueue_style( $style->id );
            }
        }
    }
}
