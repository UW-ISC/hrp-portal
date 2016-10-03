<?php

/**
 * Scripts
 *
 * Register and enqueue scripts for the front-end and admin of the site
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

class UWHR_Scripts {

	/**
     * Scripts Array
     *
     * A public member of the Script object. Can be modified by external objects to
	 * register and enqueue other scripts.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
  	public $scripts;

  	function __construct() {

		$ver = wp_get_theme('uwhr')->version;

		$this->scripts = array(

			'modernizr' => array (
		        'id'      => 'modernizr',
		        'url'     => get_template_directory_uri() . '/assets/vendor/modernizr.js',
		        'deps'    => array(),
		        'version' => $ver,
		        'admin'   => false
		    ),

		    'vendor' => array (
		        'id'      => 'vendor',
		        'url'     => get_template_directory_uri() . '/assets/vendor.min.js',
		        'deps'    => array(),
		        'version' => $ver,
		        'admin'   => false
		    ),

			'main' => array (
		        'id'      => 'main',
		        'url'     => get_template_directory_uri() . '/assets/main.min.js',
		        'deps'    => array('vendor'),
		        'version' => $ver,
		        'admin'   => false
		    ),

			'alert' => array (
				'id'      => 'alert',
				'url'     => '//www.washington.edu/static/alert.js',
				'deps'    => array(),
				'version' => '1.0',
				'admin'   => false
      		),
		);

		add_action( 'wp_enqueue_scripts', array( $this, 'register_default_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_default_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'custom_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
  	}

	/**
     * Register Scripts
     *
     * Loops over each script in the $script array and registers.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
  	function register_default_scripts() {
	  	foreach ( $this->scripts as $script ) {
			$script = (object) $script;

			wp_register_script(
				$script->id,
				$script->url,
				$script->deps,
				$script->version,
				true
			);
	  	}

		wp_deregister_script('jquery');
  	}

	/**
     * Enqueue Scripts
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
  	function enqueue_default_scripts() {
	  	foreach ( $this->scripts as $script ) {
			$script = (object) $script;

			if ( ! $script->admin ) {
		  		wp_enqueue_script( $script->id );
		  	}
	  	}
  	}

	/**
     * Register and Enqueue Admin Scripts
     *
     * Loops over each script in the $script array and registers.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
  	function enqueue_admin_scripts() {
	  	if ( ! is_admin() ) {
			return;
		}

	  	foreach ( $this->scripts as $script ) {
			$script = (object) $script;

			if ( $script->admin ) {
				wp_register_script(
					$script->id,
					$script->url,
					$script->deps,
					$script->version,
					true
				);

				wp_enqueue_script( $script->id );
			}
	  	}
  	}

	/**
     * Enqueue custom scripts
     *
     * Enqueues scripts if there is a need for custom JS on any particular page.
	 * Enqueues trumba script for the calendar template page.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.9.0
     * @package UWHR
     */
  	function custom_scripts() {
  		if ( is_page() ) {
  			global $post;
			$slug = get_post( $post )->post_name;
			$data = get_post_meta( $post->ID, '_uwhr_page_custom_js', true );

			if ( $data == 1 ) {
				if ( file_exists(get_stylesheet_directory() . '/assets/js/custom/'.$slug.'.js') ) {
					wp_enqueue_script( $slug, get_stylesheet_directory_uri() . '/assets/js/custom/'.$slug.'.js', 'vendor', '', true );
				} else {
					wp_enqueue_script( $slug, get_template_directory_uri() . '/assets/js/custom/'.$slug.'.js', 'vendor', '', true );
				}
			}
  		}

		if ( is_page_template( 'templates/template-calendar.php' ) ) {
			wp_enqueue_script( 'trumba_spud_script', 'https://www.trumba.com/scripts/spuds.js' );
		}
  	}
}
