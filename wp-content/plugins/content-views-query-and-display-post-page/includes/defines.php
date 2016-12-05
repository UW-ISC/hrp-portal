<?php

/**
 * Defines common constant
 *
 * @package   PT_Content_Views
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
define( 'PT_CV_DOMAIN', 'content-views' );
define( 'PT_CV_TEXTDOMAIN', 'content-views-query-and-display-post-page' );
define( 'PT_CV_PREFIX', 'pt-cv-' );
define( 'PT_CV_PREFIX_', 'pt_cv_' );
define( 'PT_CV_PREFIX_UPPER', 'PT_CV_' );

// Custom post type
define( 'PT_CV_POST_TYPE', 'pt_view' );

// Options
define( 'PT_CV_OPTION_VERSION', PT_CV_PREFIX_ . 'version' );
define( 'PT_CV_OPTION_NAME', PT_CV_PREFIX_ . 'options' );

// Custom fields
define( 'PT_CV_META_ID', '_' . PT_CV_PREFIX_ . 'id' );
define( 'PT_CV_META_SETTINGS', '_' . PT_CV_PREFIX_ . 'settings' );

// Public assets directory
define( 'PT_CV_PUBLIC_ASSETS', PT_CV_PATH . 'public/assets/' );

// Public assets uri
define( 'PT_CV_PUBLIC_ASSETS_URI', plugins_url( 'public/assets/', PT_CV_FILE ) );

// View type directory (HTML + CSS + JS)
define( 'PT_CV_VIEW_TYPE_OUTPUT', PT_CV_PATH . 'public/templates/' );

// Enable/Disable debug mode
define( 'PT_CV_DEBUG', false );

// Script error
define( 'PT_CV_SOLVE_SCRIPT_ERROR', 'cv_solve_script_error_181' );
/**
 * Check if CV layout was damaged by theme/another plugin's style
 * @since 1.8.7
 * @return bool
 */
function cv_is_damaged_style() {
	# Plugin: Divi Builder, v1.3.8
	return apply_filters( PT_CV_PREFIX_ . 'damaged_style', defined( 'ET_BUILDER_PLUGIN_VERSION' ) );
}
