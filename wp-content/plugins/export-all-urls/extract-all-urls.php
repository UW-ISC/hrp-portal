<?php

/*
Plugin Name: Export All URLs
Plugin URI: http://www.AtlasGondal.com/
Description: This plugin allows you to extract posts/pages Title, URL and Categories. You can write output in CSV or in dashboard.
Version: 3.0
Author: Atlas Gondal
Author URI: http://www.AtlasGondal.com/
License: GPL v2 or higher
License URI: License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


function extract_all_urls_nav(){

    add_options_page( 'Export All URLs', 'Export All URLs', 'manage_options', 'extract-all-urls-settings', 'include_settings_page' );

}


add_action( 'admin_menu', 'extract_all_urls_nav' );



function include_settings_page(){

    include(plugin_dir_path(__FILE__) . 'extract-all-urls-settings.php');

}