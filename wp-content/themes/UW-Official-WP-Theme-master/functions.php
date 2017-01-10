<?php

//Check for updates to the theme
include('update-notice.php');

if ( function_exists('register_sidebars') )
    register_sidebars(2);
    
add_filter( 'wp_feed_cache_transient_lifetime', create_function('$a', 'return 60;') );

function uw_page_menu( $args = array() ) {
	$defaults = array('sort_column' => 'menu_order, post_title', 'menu_class' => 'menu', 'echo' => true, 'link_before' => '', 'link_after' => '');
	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'wp_page_menu_args', $args );

	$menu = '';

	$list_args = $args;

	// Show Home in the menu
	if ( ! empty($args['show_home']) ) {
		if ( true === $args['show_home'] || '1' === $args['show_home'] || 1 === $args['show_home'] )
			$text = __('Home');
		else
			$text = $args['show_home'];
		$class = 'class="';
		if ( is_front_page() && !is_paged() ) {
            $class .= ' current_page_item';
        }
        $class .= ' selectedAccordion navSectionHead"';
		$menu .= '<li ' . $class . '><a href="' . home_url( '/' ) . ' " title="' . esc_attr($text) . '">' . $args['link_before'] . $text . $args['link_after'] . '</a></li>';
		// If the front page is a page, add it to the exclude list
		if (get_option('show_on_front') == 'page') {
			if ( !empty( $list_args['exclude'] ) ) {
				$list_args['exclude'] .= ',';
			} else {
				$list_args['exclude'] = '';
			}
			$list_args['exclude'] .= get_option('page_on_front');
		}
	}

	$list_args['echo'] = false;
	$list_args['title_li'] = '';
	$menu .= str_replace( array( "\r", "\n", "\t" ), '', wp_list_pages($list_args) );

	if ( $menu )
		$menu = '<ul id="menu">' . $menu . '</ul>';

	$menu = '<div class="' . esc_attr($args['menu_class']) . '">' . $menu . "</div>\n";
    $menu = apply_filters( 'wp_page_menu', $menu, $args );
	if ( $args['echo'] )
		echo $menu;
	else
		return $menu;
}

/** 
 * Adds the theme options page
*/
function uw_theme_options_init() {
	$uw_theme_options = add_theme_page(__('UW Theme Options', 'uw_theme'), __('UW Theme Options', 'uw_theme'), 'edit_theme_options', 'uw_theme_options', 'uw_theme_options');
	$uw_theme_faq = add_theme_page(__('UW Theme FAQs', 'uw_theme'), __('UW Theme FAQs', 'uw_theme'), 'edit_theme_options', 'uw_theme_faq', 'uw_theme_faq');
	
	wp_register_style('uw_theme-admin-style', get_template_directory_uri().'/admin/admin.css');
	
	add_action('admin_print_styles-'.$uw_theme_options, 'uw_theme_admin_options_style');
	
	do_action('uw_theme_options_init');
}
add_action('admin_menu', 'uw_theme_options_init', 8);

//Includes the UW Theme Options and FAQ html
include('admin/settings_page.php');
include('admin/faq_page.php');

/**
 * Registers custom scripts that the theme uses
*/
function uw_theme_register_scripts(){
	wp_register_script('site.js', get_template_directory_uri() . '/site.js', 'jquery', '1.0', true);	
	wp_register_script('jquery.uwaccessiblenav.js', get_template_directory_uri() . '/js/jquery.uwaccessiblenav.js', 'site.js', '1.0', true);	
	wp_register_script('jquery.uwaccessibleleftnav.js', get_template_directory_uri() . '/js/jquery.uwaccessibleleftnav.js', 'site.js', '1.0', true);	
}
add_action('init', 'uw_theme_register_scripts');

/**
 * Enqueues the custom scripts that the theme uses
*/
function uw_theme_enqueue_scripts(){
    wp_enqueue_script( 'jquery' );

	if ( ! is_admin() ) { // Front-end only
		wp_enqueue_script( 'site.js' ); // jQuery Tools, required for slider and comments/pingbacks tabs
	    wp_enqueue_script( 'jquery.uwaccessiblenav.js' );
	    wp_enqueue_script( 'jquery.uwaccessibleleftnav.js' );
    } else { //backend scripts

    }    
}
add_action('init', 'uw_theme_enqueue_scripts');

/**
 * Registers/Enqueues the custom styles that the theme uses
*/
function uw_theme_register_styles() {
    wp_register_style('jquery.uwaccessiblenav.css', get_bloginfo('template_directory').'/css/jquery.uwaccessiblenav.css', '', 1.0);
    wp_enqueue_style('jquery.uwaccessiblenav.css');
}
add_action('wp_print_styles', 'uw_theme_register_styles');

/**
 * Grabs the dropdown navigation off of http://uw.edu (UW Homepage)
 * after a certain amount of time has passed and stores it in the database. 
 *
 * @return The navigation HTML
 */ 
if (!function_exists('get_uw_dropdowns')) {
    function get_uw_dropdowns() {

        // check if we need to update the dropdowns from the UW homepage
        if(uw_dropdowns_need_updating()) {
            update_uw_dropdowns();
        } 

        // return the navigation HTML
        $options = get_option('uw_theme_settings');
        return $options['navigation'];
    }
}

/**
 * Echos out the navigation HTML
 */ 
if (!function_exists('uw_dropdowns')) {
    function uw_dropdowns() {
        echo get_uw_dropdowns();
    }
}

if (!function_exists('update_uw_dropdowns')) {
    function update_uw_dropdowns() {
        // include the necessary functions to scrap the homepage
        include_once('simple_html_dom.php');
        $navigation;
        // gather the options for the theme
        $options = get_option('uw_theme_settings');
        $html = file_get_dom('http://www.washington.edu/');
        $node = $html->find('#navg');
        // create the html from the dom element;
        foreach($node as $element) { 
            $navigation .= $element->innertext;
        }
        // also save the current time the new html was saved into the database
        $options['_updated'] = time();
        // get the new html and save it to the database
        $options['navigation'] = $navigation;
        update_option('uw_theme_settings', $options);
    }
}

if (!function_exists('uw_dropdowns_need_updating')) {
    function uw_dropdowns_need_updating() {
        $options = get_option('uw_theme_settings');
        return (time() - $options['_nav_updated'] > $options['_nav_update_frequency']);
    }
}
?>
