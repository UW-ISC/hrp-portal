<?php
/*
Plugin Name: Divi WPDT
Plugin URI:  
Description: 
Version:     1.0.0
Author:      
Author URI:  
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: divi-divi-wpdt
Domain Path: /languages

Divi WPDT is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Divi WPDT is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Divi WPDT. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/


if ( ! function_exists( 'divi_wpdatatables_initialize_extension' ) ):
/**
 * Creates the extension's main class instance.
 *
 * @since 1.0.0
 */
function divi_wpdatatables_initialize_extension() {
	wp_register_style( 'wpdt-divi', plugins_url( 'styles/divi-wpdt.css', __FILE__ ), array(), WDT_CURRENT_VERSION );
	wp_enqueue_style( 'wpdt-divi' );

	if ( function_exists( 'et_builder_d5_enabled' ) && et_builder_d5_enabled() ) {
		return;
	}

	require_once plugin_dir_path( __FILE__ ) . 'includes/DiviWpdt.php';
	new DIVI_DiviWpdt();
}
add_action( 'divi_extensions_init', 'divi_wpdatatables_initialize_extension' );
endif;

if ( ! function_exists( 'divi_wpdatatables_load_divi5_bootstrap' ) ) {
	/**
	 * Load Divi 5 module registration when Divi exposes builder-5 APIs.
	 *
	 * @return void
	 */
	function divi_wpdatatables_load_divi5_bootstrap() {
		$bootstrap = plugin_dir_path( __FILE__ ) . 'includes/divi5/bootstrap.php';
		if ( is_readable( $bootstrap ) ) {
			require_once $bootstrap;
		}
	}
	add_action( 'init', 'divi_wpdatatables_load_divi5_bootstrap', 11 );
}
