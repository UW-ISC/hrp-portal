<?php
/*
  Plugin Name: CM Tooltip Glossary Pro+
  Plugin URI: https://www.cminds.com/
  Description: PRO+ Version! Parses posts for defined glossary terms and adds links to the static glossary page containing the definition and a tooltip with the definition.
  Version: 3.5.7
  Text Domain: cm-tooltip-glossary
  Author: CreativeMindsSolutions
  Author URI: https://www.cminds.com/
 */

if ( !ini_get( 'max_execution_time' ) || ini_get( 'max_execution_time' ) < 300 ) {
	/*
	 * Setup the high max_execution_time to avoid timeouts during lenghty operations like importing big glossaries,
	 * or rebuilding related articles index
	 */
	ini_set( 'max_execution_time', 300 );
	$disabled = explode( ',', ini_get( 'disable_functions' ) );
	if ( !in_array( 'set_time_limit', $disabled ) ) {
		set_time_limit( 300 );
	}
}

/**
 * Define Plugin Version
 *
 * @since 1.0
 */
if ( !defined( 'CMTT_VERSION' ) ) {
	define( 'CMTT_VERSION', '3.5.7' );
}

/**
 * Define Plugin name
 *
 * @since 1.0
 */
if ( !defined( 'CMTT_NAME' ) ) {
	define( 'CMTT_NAME', 'CM Tooltip Glossary Pro+' );
}

/**
 * Define Plugin canonical name
 *
 * @since 1.0
 */
if ( !defined( 'CMTT_CANONICAL_NAME' ) ) {
	define( 'CMTT_CANONICAL_NAME', 'CM Tooltip Glossary Pro' );
}

/**
 * Define Plugin license name
 *
 * @since 1.0
 */
if ( !defined( 'CMTT_LICENSE_NAME' ) ) {
	define( 'CMTT_LICENSE_NAME', 'CM Tooltip Glossary Pro+' );
}

/**
 * Define Plugin File Name
 *
 * @since 1.0
 */
if ( !defined( 'CMTT_PLUGIN_FILE' ) ) {
	define( 'CMTT_PLUGIN_FILE', __FILE__ );
}

/**
 * Define Plugin URL
 *
 * @since 1.0
 */
if ( !defined( 'CMTT_URL' ) ) {
	define( 'CMTT_URL', 'https://www.cminds.com/store/tooltipglossary/' );
}

/**
 * Define Plugin release notes url
 *
 * @since 1.0
 */
if ( !defined( 'CMTT_RELEASE_NOTES' ) ) {
	define( 'CMTT_RELEASE_NOTES', 'https://tooltip.cminds.com/release-notes-pro-plugin/' );
}

include_once plugin_dir_path( __FILE__ ) . "glossaryPlus.php";
CMTT_Glossary_Plus::init();

include_once plugin_dir_path( __FILE__ ) . "glossaryPro.php";
register_activation_hook( __FILE__, array( 'CMTT_Pro', '_install' ) );
register_activation_hook( __FILE__, array( 'CMTT_Pro', '_flush_rewrite_rules' ) );

CMTT_Pro::init();

CMTT_Glossary_Plus::after();
