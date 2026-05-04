<?php
/**
 * Register the Max Mega Menu location block and localise its location data.
 *
 * @since  3.0
 * @see    https://developer.wordpress.org/reference/functions/register_block_type/
 * @return void
 */
function maxmegamenu_location_block_init() {
	register_block_type(
		__DIR__ . '/build',
		[
			'attributes' => [
				'location' => [
					'type'  => 'string'
				]
			],
			'render_callback' => 'maxmegamenu_render_callback',
		]
	);

	$locations = array_merge(
		[ "" => __('Select a location', 'megamenu') ],
		get_registered_nav_menus()
	);

	wp_localize_script( 'maxmegamenu-location-editor-script', 'max_mega_menu_locations', $locations );

	wp_localize_script(
		'maxmegamenu-location-editor-script',
		'max_mega_menu_block_admin',
		[
			'menu_locations_url' => admin_url( 'admin.php?page=maxmegamenu' ),
		]
	);
}
add_action( 'init', 'maxmegamenu_location_block_init' );


/**
 * Enqueue the menu stylesheet on block-enabled (Gutenberg editor) pages.
 *
 * @since  3.0
 * @return void
 */
function maxmegamenu_block_assets() {
	$style_manager = new Mega_Menu_Style_Manager;
	$style_manager->enqueue_fs_style();
}
add_action( 'enqueue_block_editor_assets', 'maxmegamenu_block_assets' );

/**
 * Render callback for the Max Mega Menu location block.
 *
 * @since  3.0
 * @param  array    $attributes The block attributes.
 * @param  string   $content    The block content.
 * @param  WP_Block $block      Block instance.
 * @return string   The rendered menu HTML, or a placeholder message.
 */
function maxmegamenu_render_callback( $attributes, $content, $block ) {
	if ( isset( $attributes['location'] ) && strlen( $attributes['location'] ) && function_exists("max_mega_menu_is_enabled") && max_mega_menu_is_enabled( $attributes['location'] ) ) {
		$menu = wp_nav_menu( [ 'theme_location' => $attributes['location'], 'echo' => false ] );
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
 * Detect whether the current request is a block editor preview (REST edit context).
 *
 * @since  3.0
 * @see    https://github.com/WordPress/gutenberg/issues/23810#issue-653709683
 * @return bool True when rendering inside the block editor, false otherwise.
 */
function maxmegamenu_is_editing_block_on_backend() {
	return defined('REST_REQUEST') && true === REST_REQUEST && 'edit' === filter_input( INPUT_GET, 'context', FILTER_SANITIZE_STRING );
}