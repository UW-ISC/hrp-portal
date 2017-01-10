<?php
/**
 *  Originally By:
 *       Update Noticication Script
 *       By Jeremy Clark
 *       http://clark-technet.com
 *       License: GPL
 *  
 *  Forked By:
 *       Dane Odekirk - UW Marketing
 *       @desc Changed update script to integrate with GitHub
 *             and stored all database information into an array 
 *             rather then separate variables.
 */

function theme_check_for_updates() {
    $current_page = get_current_screen();
    if( in_array($current_page->id, array("themes","dashboard")) ) {
        $options = get_option("uw_theme_settings");
        
        if( theme_needs_updating( $options )) {
            $url     = "http://github.com/api/v2/json/commits/list/uweb/uw-official-wp-theme/master";
            $file    = file_get_contents($url);
            $json    = json_decode($file);
            $key     = $json->commits[0]->id;
            if( $key != $options['_theme_update_key'] ) {
                $options['_theme_update_key'] = $key;
                $options['_theme_updated']    = time();
                update_option('uw_theme_settings', $options);
                add_action('admin_notices','new_theme_version');
            }
        }
    }
    // uncomment to force show the notification
    //add_action('admin_notices','new_theme_version');
}

function new_theme_version() {
    $current_page = get_current_screen();
    if( in_array($current_page->id, array("themes","dashboard")) ) {
		echo '<div id="message" class="updated fade">
                <p>
                    New <strong>'. get_current_theme() .'</strong> version available. <br/> 
                    Download the <a href="https://github.com/uweb/UW-Official-WP-Theme/tarball/master">tar file here</a> 
                    or download the <a href="https://github.com/uweb/UW-Official-WP-Theme/zipball/master">zip file here</a>.<br/>
                    Please visit the UW Theme FAQ for installation guidelines.
                </p>
              </div>';
    }
}

function theme_needs_updating($options){
    return (time() - $options['_theme_updated'] > $options['_theme_update_frequency']);
}
add_action('admin_head','theme_check_for_updates');
