<?php

/**
 * @package   PT_Content_Views
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 *
 * @wordpress-plugin
 * Plugin Name:       Content Views
 * Plugin URI:        http://wordpress.org/plugins/content-views-query-and-display-post-page/
 * Description:       Query and display <strong>posts, pages</strong> in awesome layouts (<strong>grid, scrollable list, collapsible list</strong>) easier than ever, without coding!
 * Version:           1.9.3
 * Author:            PT Guy
 * Author URI:        http://profiles.wordpress.org/pt-guy
 * Text Domain:       content-views-query-and-display-post-page
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/<owner>/<repo>
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	die;
}

// Define Constant
define( 'PT_CV_VERSION', '1.9.3' );
define( 'PT_CV_FILE', __FILE__ );
define( 'PT_CV_PATH', plugin_dir_path( __FILE__ ) );
include_once( PT_CV_PATH . 'includes/defines.php' );

// Include library files
include_once( PT_CV_PATH . 'includes/_session.php' );
include_once( PT_CV_PATH . 'includes/formatting.php' );
include_once( PT_CV_PATH . 'includes/assets.php' );
include_once( PT_CV_PATH . 'includes/compatibility.php' );
include_once( PT_CV_PATH . 'includes/functions.php' );
include_once( PT_CV_PATH . 'includes/hooks.php' );
include_once( PT_CV_PATH . 'includes/html-viewtype.php' );
include_once( PT_CV_PATH . 'includes/html.php' );
include_once( PT_CV_PATH . 'includes/settings.php' );
include_once( PT_CV_PATH . 'includes/update.php' );
include_once( PT_CV_PATH . 'includes/values.php' );

// Main file
include_once( PT_CV_PATH . 'public/content-views.php' );

// Register hooks when the plugin is activated or deactivated.
register_activation_hook( __FILE__, array( 'PT_Content_Views', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'PT_Content_Views', 'deactivate' ) );

// Load plugin
PT_Content_Views::get_instance();

// For Admin
if ( is_admin() ) {
	include_once( PT_CV_PATH . 'admin/includes/options.php' );
	include_once( PT_CV_PATH . 'admin/includes/plugin.php' );
	include_once( PT_CV_PATH . 'admin/content-views-admin.php' );

	PT_Content_Views_Admin::get_instance();
}

// Support for post thumbnails
add_theme_support( 'post-thumbnails' );

// Enable shortcode in content
add_filter( 'the_content', 'do_shortcode', 15 );

// Enable shortcodes in text widgets.
add_filter( 'widget_text', 'do_shortcode', 15 );
