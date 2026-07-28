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

	$enabled_locations = [];
	foreach ( array_keys( get_registered_nav_menus() ) as $location_slug ) {
		$loc = Mega_Menu_Location::find( $location_slug );
		if ( $loc && $loc->is_enabled() && has_nav_menu( $location_slug ) ) {
			$enabled_locations[] = $location_slug;
		}
	}
	wp_localize_script( 'maxmegamenu-location-editor-script', 'max_mega_menu_enabled_locations', $enabled_locations );

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
 * Enqueue the menu stylesheet for the block editor (including iframe content).
 *
 * Front-end pages already load this via {@see Mega_Menu_Style_Manager::enqueue_styles()}
 * on `wp_enqueue_scripts`; this hook only targets admin/editor contexts where
 * `enqueue_block_assets` is required for the iframe canvas (WP 6.9+).
 *
 * @since 3.0
 * @see https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/
 */
function maxmegamenu_block_assets() {
	if ( ! is_admin() ) {
		return;
	}

	$style_manager = new Mega_Menu_Style_Manager();
	$style_manager->enqueue_fs_style();
}
add_action( 'enqueue_block_assets', 'maxmegamenu_block_assets' );

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
	$loc = isset( $attributes['location'] ) && strlen( $attributes['location'] ) ? Mega_Menu_Location::find( $attributes['location'] ) : false;

	if ( $loc && $loc->is_active() ) {
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