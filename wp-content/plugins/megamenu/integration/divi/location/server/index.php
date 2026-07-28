<?php
/**
 * Server-side renderer for the Max Mega Menu Divi 5 location module.
 *
 * @since 3.10
 */

namespace MaxMegaMenu\Divi;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Handles server-side rendering of the maxmegamenu/location Divi 5 module.
 */
class MaxMegaMenuLocationModule implements DependencyInterface {

	/**
	 * DependencyInterface entry point — called to initialise the module.
	 */
	public function load() {
		add_action( 'init', [ static::class, 'register_module' ] );
	}

	/**
	 * Register the module with Divi's module library.
	 */
	public static function register_module() {
		$module_json_folder_path = dirname( __DIR__ ) . '/visual-builder/src';

		ModuleRegistration::register_module(
			$module_json_folder_path,
			[
				'render_callback' => [ static::class, 'render_callback' ],
			]
		);
	}

	/**
	 * Render module styles.
	 *
	 * @param array $args Style arguments supplied by Divi.
	 */
	public static function module_styles( $args ) {
		$elements = $args['elements'];

		Style::add( [
			'id'            => $args['id'],
			'name'          => $args['name'],
			'orderIndex'    => $args['orderIndex'],
			'storeInstance' => $args['storeInstance'],
			'styles'        => [
				$elements->style( [
					'attrName'   => 'module',
					'styleProps' => [
						'disabledOn' => [
							'disabledModuleVisibility' => $args['settings']['disabledModuleVisibility'] ?? null,
						],
					],
				] ),
			],
		] );
	}

	/**
	 * Register module script data (animations, visibility, etc.).
	 *
	 * @param array $args Script data arguments supplied by Divi.
	 */
	public static function module_script_data( $args ) {
		$args['elements']->script_data( [ 'attrName' => 'module' ] );
	}

	/**
	 * Apply module classnames.
	 *
	 * @param array $args Classname arguments supplied by Divi.
	 */
	public static function module_classnames( $args ) {
		$args['classnamesInstance']->add(
			ElementClassnames::classnames( [
				'attrs' => $args['attrs']['module']['decoration'] ?? [],
			] )
		);
	}

	/**
	 * Render the module HTML on the frontend.
	 *
	 * @param array    $attrs    Module attributes from the builder.
	 * @param string   $content  Inner block content (unused).
	 * @param \WP_Block $block   Block instance.
	 * @param object   $elements Divi elements renderer.
	 * @return string  Rendered HTML.
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {
		$location_slug = isset( $attrs['location']['innerContent']['desktop']['value'] )
			? sanitize_key( (string) $attrs['location']['innerContent']['desktop']['value'] )
			: '';

		$menu_html = '';
		if ( $location_slug && has_nav_menu( $location_slug ) ) {
			$menu_html = wp_nav_menu( [ 'theme_location' => $location_slug, 'echo' => false ] ) ?: '';
		}

		$module_elements = $elements->style_components( [ 'attrName' => 'module' ] );

		return Module::render( [
			'orderIndex'          => $block->parsed_block['orderIndex'],
			'storeInstance'       => $block->parsed_block['storeInstance'],
			'attrs'               => $attrs,
			'elements'            => $elements,
			'id'                  => $block->parsed_block['id'],
			'moduleClassName'     => 'maxmegamenu_location',
			'name'                => $block->block_type->name,
			'classnamesFunction'  => [ static::class, 'module_classnames' ],
			'moduleCategory'      => $block->block_type->category,
			'stylesComponent'     => [ static::class, 'module_styles' ],
			'scriptDataComponent' => [ static::class, 'module_script_data' ],
			'children'            => $module_elements . $menu_html,
		] );
	}
}

// Dependency registration is handled in module.php, which hooks
// divi_module_library_modules_dependency_tree at plugin-load time
// (before the action fires during theme loading).
