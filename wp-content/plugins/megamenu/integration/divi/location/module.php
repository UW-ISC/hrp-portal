<?php
/**
 * Divi 5 module integration for Max Mega Menu.
 *
 * @since 3.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook into Divi's dependency tree at plugin-load time (before the action fires
 * during theme loading). Loading server/index.php inside the callback means Divi's
 * autoloader is already set up when the class definitions are parsed.
 */
add_action(
	'divi_module_library_modules_dependency_tree',
	function ( $dependency_tree ) {
		if ( ! function_exists( 'et_builder_d5_enabled' ) || ! et_builder_d5_enabled() ) {
			return;
		}

		$server_file = __DIR__ . '/server/index.php';

		if ( is_readable( $server_file ) ) {
			require_once $server_file;
		}

		$dependency_tree->add_dependency( new \MaxMegaMenu\Divi\MaxMegaMenuLocationModule() );
	}
);


/**
 * REST endpoint used by the Visual Builder canvas to preview the rendered menu.
 */
add_action( 'rest_api_init', function () {
	register_rest_route(
		'maxmegamenu/v1',
		'/render-menu',
		[
			'methods'             => 'GET',
			'permission_callback' => function () {
				return current_user_can( apply_filters( 'megamenu_options_capability', 'edit_theme_options' ) );
			},
			'callback'            => function ( WP_REST_Request $request ) {
				$location = sanitize_key( $request->get_param( 'location' ) );

				if ( ! $location ) {
					return new WP_REST_Response( [ 'html' => '' ], 200 );
				}

				$menu_html = '';
				if ( has_nav_menu( $location ) ) {
					$menu_html = wp_nav_menu( [ 'theme_location' => $location, 'echo' => false ] );
				}

				return new WP_REST_Response( [ 'html' => (string) $menu_html ], 200 );
			},
		]
	);
} );


/**
 * Enqueue the compiled Visual Builder bundle.
 */
add_action( 'divi_visual_builder_assets_before_enqueue_scripts', function () {
	if ( ! function_exists( 'et_core_is_fb_enabled' ) || ! et_core_is_fb_enabled() ) {
		return;
	}

	if ( ! function_exists( 'et_builder_d5_enabled' ) || ! et_builder_d5_enabled() ) {
		return;
	}

	$bundle_path = __DIR__ . '/visual-builder/build/maxmegamenu-location-module.js';

	if ( ! file_exists( $bundle_path ) ) {
		return;
	}

	$registered_menus = get_registered_nav_menus();

	$mmm_enabled_locations = [];
	foreach ( array_keys( $registered_menus ) as $location_slug ) {
		$loc = Mega_Menu_Location::find( $location_slug );
		if ( $loc && $loc->is_enabled() && has_nav_menu( $location_slug ) ) {
			$mmm_enabled_locations[] = $location_slug;
		}
	}

	wp_register_script( 'maxmegamenu-divi-locations-data', false, [], MEGAMENU_VERSION );
	wp_add_inline_script(
		'maxmegamenu-divi-locations-data',
		'window.maxMegaMenuLocations = ' . wp_json_encode( $registered_menus ) . ';' .
		'window.maxMegaMenuEnabledLocations = ' . wp_json_encode( $mmm_enabled_locations ) . ';' .
		'window.maxMegaMenuRestUrl = ' . wp_json_encode( rest_url( 'maxmegamenu/v1/' ) ) . ';' .
		'window.maxMegaMenuNonce = ' . wp_json_encode( wp_create_nonce( 'wp_rest' ) ) . ';'
	);

	\ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
		[
			'name'    => 'maxmegamenu-divi-location-module',
			'version' => MEGAMENU_VERSION,
			'script'  => [
				'src'                => MEGAMENU_BASE_URL . 'integration/divi/location/visual-builder/build/maxmegamenu-location-module.js',
				'deps'               => [ 'react', 'divi-module-library', 'wp-hooks', 'maxmegamenu-divi-locations-data' ],
				'enqueue_top_window' => false,
				'enqueue_app_window' => true,
			],
		]
	);
} );
