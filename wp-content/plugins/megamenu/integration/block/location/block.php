<?php
/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function maxmegamenu_location_block_init() {
	register_block_type(
		__DIR__ . '/build',
		array(
			'attributes' => array(
				'location' => array(
					'type'  => 'string'
				)
			),
			'render_callback' => 'maxmegamenu_render_callback',
		)
	);

	$locations = array_merge(
		array( "" => __('Select a location', 'megamenu') ),
		get_registered_nav_menus()
	);

	wp_localize_script( 'maxmegamenu-location-editor-script', 'max_mega_menu_locations', $locations );
}
add_action( 'init', 'maxmegamenu_location_block_init' );


/**
 * Enqueue the menu style.css file on block enabled pages
 */
function maxmegamenu_block_assets() {
	$style_manager = new Mega_Menu_Style_Manager;
	$style_manager->enqueue_fs_style();
}
add_action( 'enqueue_block_editor_assets', 'maxmegamenu_block_assets' );

/**
 * Render callback function.
 *
 * @param array    $attributes The block attributes.
 * @param string   $content    The block content.
 * @param WP_Block $block      Block instance.
 *
 * @return string The rendered output.
 */
function maxmegamenu_render_callback( $attributes, $content, $block ) {
	if ( isset( $attributes['location'] ) && strlen( $attributes['location'] ) && function_exists("max_mega_menu_is_enabled") && max_mega_menu_is_enabled( $attributes['location'] ) ) {
		$menu = wp_nav_menu( array( 'theme_location' => $attributes['location'], 'echo' => false ) );
	} else {
		if ( maxmegamenu_is_editing_block_on_backend() ) {
			$menu = "<p>" . __("Go to Mega Menu > Menu Locations to enable Max Mega Menu for this location.", "megamenu") . "</p>";
		} else {
			$menu = "<!--" . __("Go to Mega Menu > Menu Locations to enable Max Mega Menu for this location.", "megamenu") . "-->";
		}
	}

	return $menu;
}

/**
 *  props: https://github.com/WordPress/gutenberg/issues/23810#issue-653709683
 */
 function maxmegamenu_is_editing_block_on_backend() {
	return defined('REST_REQUEST') && true === REST_REQUEST && 'edit' === filter_input( INPUT_GET, 'context', FILTER_SANITIZE_STRING );
}