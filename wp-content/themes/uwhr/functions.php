<?php

/**
 * Theme Setup
 *
 * You aren't going to find anything in here except the global object instantiation.
 * Please, please don't clutter this file up. Let's keep everything clean and modular, in
 * small chunks.
 *
 * Put template functions inside inc/template-functions.php
 * Put helper functions inside inc/helper-functions.php
 * Put any other theme functionality in a class and include it via setup/class.uwhr.php
 *
 * The global UWHR object can also be extended in children themes using the extend_uwhr_object hook.
 *
 * @see inc/template-functions.php
 * @see inc/helper-functions.php
 * @see setup/class.uwhr.php
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

if ( ! function_exists( 'setup_uwhr_object' ) ) {
    function setup_uwhr_object() {
        require( get_template_directory() . '/setup/class.uwhr.php' );
        $UWHR = new UWHR();
        do_action( 'extend_uwhr_object', $UWHR );
        return $UWHR;
    }
}

// BOOM, LET'S GO
$UWHR = setup_uwhr_object();
