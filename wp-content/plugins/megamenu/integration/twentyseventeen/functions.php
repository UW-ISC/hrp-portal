<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access
}

/**
 * Append the Twenty Seventeen integration SCSS to the compiled stylesheet.
 *
 * @since  1.0
 * @param  string $scss Existing SCSS content.
 * @return string SCSS content with the integration stylesheet appended.
 */
function megamenu_twentyseventeen_style($scss) {
    $path = trailingslashit( plugin_dir_path( __FILE__ ) ) . 'style.scss';
    $contents = file_get_contents( $path );
    return $scss . $contents;
}
add_filter( 'megamenu_load_scss_file_contents', 'megamenu_twentyseventeen_style', 9999 );


/**
 * Enqueue the Twenty Seventeen JavaScript integration helper.
 *
 * @since  1.0
 * @return void
 */
function megamenu_twentyseventeen_script() {
    wp_enqueue_script( "megamenu-twentyseventeen", plugins_url( 'script.js' , __FILE__ ), ['megamenu'], MEGAMENU_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'megamenu_twentyseventeen_script', 999 );


/**
 * Restore the menu-item class on menu items, required for the sticky menu to work.
 *
 * @since  1.0
 * @param  array $classes Existing CSS classes for the menu item.
 * @return array CSS classes with 'menu-item' appended.
 */
function megamenu_twentyseventeen_add_menu_item_class($classes) {
    $classes[] = 'menu-item';
    return $classes;
}
add_filter( 'megamenu_nav_menu_css_class', 'megamenu_twentyseventeen_add_menu_item_class', 9999 );
