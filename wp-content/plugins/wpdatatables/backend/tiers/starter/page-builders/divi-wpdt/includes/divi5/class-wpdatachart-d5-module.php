<?php
/**
 * Divi 5 server-side module: wpDataChart.
 *
 * @package wpDataTables
 */

defined( 'ABSPATH' ) || exit;

use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Class Divi_Wpdt_D5_WpDataChart_Module
 */
class Divi_Wpdt_D5_WpDataChart_Module implements \ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface {

	/**
	 * {@inheritdoc}
	 */
	public function load() {
		add_action( 'init', array( __CLASS__, 'register_module' ) );
	}

	/**
	 * Register module with Divi 5.
	 *
	 * @return void
	 */
	public static function register_module() {
		$module_json_folder_path = dirname( __DIR__, 2 ) . '/visual-builder/src/wpdatachart';
		if ( ! is_dir( $module_json_folder_path ) ) {
			return;
		}
		ModuleRegistration::register_module(
			$module_json_folder_path,
			array(
				'render_callback' => array( __CLASS__, 'render_callback' ),
			)
		);
	}

	/**
	 * Module styles.
	 *
	 * @param array $args Args.
	 * @return void
	 */
	public static function module_styles( $args ) {
		$elements = $args['elements'];

		Style::add(
			array(
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => array(
					$elements->style(
						array(
							'attrName'   => 'module',
							'styleProps' => array(
								'disabledOn' => array(
									'disabledModuleVisibility' => $args['settings']['disabledModuleVisibility'] ?? null,
								),
							),
						)
					),
				),
			)
		);
	}

	/**
	 * Script data.
	 *
	 * @param array $args Args.
	 * @return void
	 */
	public static function module_script_data( $args ) {
		$elements = $args['elements'];
		$elements->script_data(
			array(
				'attrName' => 'module',
			)
		);
	}

	/**
	 * Classnames.
	 *
	 * @param array $args Args.
	 * @return void
	 */
	public static function module_classnames( $args ) {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		$classnames_instance->add(
			ElementClassnames::classnames(
				array(
					'attrs' => $attrs['module']['decoration'] ?? array(),
				)
			)
		);
	}

	/**
	 * Render callback.
	 *
	 * @param array  $attrs    Attributes.
	 * @param string $content  Inner content.
	 * @param object $block    Parsed block.
	 * @param object $elements Elements API.
	 * @return string
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {
		$props = Divi_Wpdt_Shortcode_Helper::flatten_wpdatachart_attrs_for_render( is_array( $attrs ) ? $attrs : array() );
		$inner = Divi_Wpdt_Shortcode_Helper::render_wpdatachart_from_props( $props );

		$module_inner = HTMLUtility::render(
			array(
				'tag'               => 'div',
				'attributes'        => array(
					'class' => 'et_pb_module_inner',
				),
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $inner,
			)
		);

		$module_elements = $elements->style_components(
			array(
				'attrName' => 'module',
			)
		);

		$module_container_children = $module_elements . $module_inner;

		return Module::render(
			array(
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],
				'attrs'               => $attrs,
				'elements'            => $elements,
				'id'                  => $block->parsed_block['id'],
				'moduleClassName'     => 'divi_wpdt_wpdatachart',
				'name'                => $block->block_type->name,
				'classnamesFunction'  => array( __CLASS__, 'module_classnames' ),
				'moduleCategory'      => $block->block_type->category,
				'stylesComponent'     => array( __CLASS__, 'module_styles' ),
				'scriptDataComponent' => array( __CLASS__, 'module_script_data' ),
				'children'            => $module_container_children,
			)
		);
	}
}
