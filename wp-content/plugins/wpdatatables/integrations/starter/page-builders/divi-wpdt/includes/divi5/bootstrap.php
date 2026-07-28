<?php
/**
 * Divi 5 module registration (native ModuleRegistration API).
 *
 * @package wpDataTables
 */

defined( 'ABSPATH' ) || exit;

if ( defined( 'DIVI_WPDT_D5_BOOTSTRAP_DONE' ) ) {
	return;
}
define( 'DIVI_WPDT_D5_BOOTSTRAP_DONE', true );

require_once dirname( __DIR__ ) . '/class-divi-wpdt-shortcode-helper.php';

if ( ! function_exists( 'et_builder_d5_enabled' ) || ! et_builder_d5_enabled() ) {
	return;
}

if ( ! class_exists( '\ET\Builder\Packages\ModuleLibrary\ModuleRegistration' ) ) {
	return;
}

if ( ! Divi_Wpdt_Shortcode_Helper::ensure_divi5_dependency_interface() ) {
	return;
}

require_once __DIR__ . '/class-wpdatatable-d5-module.php';
require_once __DIR__ . '/class-wpdatachart-d5-module.php';

add_action(
	'divi_module_library_modules_dependency_tree',
	static function ( $dependency_tree ) {
		$dependency_tree->add_dependency( new Divi_Wpdt_D5_WpDataTable_Module() );
		$dependency_tree->add_dependency( new Divi_Wpdt_D5_WpDataChart_Module() );
	},
	10,
	1
);

/**
 * Enqueue Divi 5 Visual Builder bundle when FB + D5 are active.
 *
 * @return void
 */
function divi_wpdt_d5_enqueue_visual_builder_assets() {
	if ( ! function_exists( 'et_core_is_fb_enabled' ) || ! et_core_is_fb_enabled() ) {
		return;
	}
	if ( ! function_exists( 'et_builder_d5_enabled' ) || ! et_builder_d5_enabled() ) {
		return;
	}
	if ( ! class_exists( '\ET\Builder\VisualBuilder\Assets\PackageBuildManager' ) ) {
		return;
	}
	$base_url = Divi_Wpdt_Shortcode_Helper::extension_base_url();

	Divi_Wpdt_Shortcode_Helper::enqueue_divi5_visual_builder_wpdatatable_assets();
	Divi_Wpdt_Shortcode_Helper::enqueue_divi5_visual_builder_wpdatachart_assets();

	if ( wp_style_is( 'wdt-bootstrap', 'enqueued' ) || wp_style_is( 'wdt-bootstrap', 'registered' ) ) {
		wp_add_inline_style(
			'wdt-bootstrap',
			'body:not(.modal-open) #wdt-frontend-modal,body:not(.modal-open) #wdt-delete-modal{display:none!important;pointer-events:none!important;visibility:hidden!important;}'
			. 'body:not(.modal-open) #wdt-frontend-modal.in,body:not(.modal-open) #wdt-delete-modal.in{display:none!important;}'
			. 'body.modal-open #wdt-frontend-modal.in,body.modal-open #wdt-delete-modal.in{display:block!important;position:fixed;top:0;right:0;bottom:0;left:0;z-index:100001!important;overflow-x:hidden;overflow-y:auto;pointer-events:auto!important;}'
			. 'body.modal-open .modal-backdrop.in{z-index:100000!important;}'
			. '.bootstrap-select.open .dropdown-menu{z-index:100050!important;}'
			. '#wdt-frontend-modal .bootstrap-select .dropdown-menu{z-index:100052!important;}'
			. '.divi-wpdt-d5-preview-html .wpDataTablesWrapper,.divi-wpdt-d5-preview-html table.wpDataTable{pointer-events:auto!important;position:relative;z-index:1;}'
		);
	}

	$tables = class_exists( 'WDTConfigController' )
		? WDTConfigController::getAllTablesAndChartsForPageBuilders( 'divi', 'tables' )
		: array( 0 => esc_attr__( 'Select a table', 'wpdatatables' ) );
	$charts = class_exists( 'WDTConfigController' )
		? WDTConfigController::getAllTablesAndChartsForPageBuilders( 'divi', 'charts' )
		: array( 0 => esc_attr__( 'Select a chart', 'wpdatatables' ) );

	wp_register_script(
		'divi-wpdt-d5-vb-config',
		false,
		array(),
		WDT_CURRENT_VERSION,
		true
	);
	wp_enqueue_script( 'divi-wpdt-d5-vb-config' );
	wp_add_inline_script(
		'divi-wpdt-d5-vb-config',
		'window.wpdtDivi5Preview=' . wp_json_encode(
			array(
				'restUrl'           => esc_url_raw( rest_url( 'wpdatatables/v1/divi5-preview' ) ),
				'nonce'             => wp_create_nonce( 'wp_rest' ),
				'tableOptions'      => divi_wpdt_d5_select_options_from_list( $tables ),
				'chartOptions'      => divi_wpdt_d5_select_options_from_list( $charts ),
				'createTableNotice' => class_exists( 'WDTConfigController' ) ? WDTConfigController::wdt_create_table_notice() : '',
				'selectTableNotice' => class_exists( 'WDTConfigController' ) ? WDTConfigController::wdt_select_table_notice() : '',
				'createChartNotice' => class_exists( 'WDTConfigController' ) ? WDTConfigController::wdt_create_chart_notice() : '',
				'selectChartNotice' => class_exists( 'WDTConfigController' ) ? WDTConfigController::wdt_select_chart_notice() : '',
			),
			JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
		) . ';',
		'after'
	);

	\ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
		array(
			'name'    => 'divi-wpdt-d5-modules',
			'version' => WDT_CURRENT_VERSION,
			'script'  => array(
				'src'                => $base_url . 'visual-builder/build/divi-wpdt-d5-modules.js',
				'deps'               => array(
					'react',
					'jquery',
					'divi-module-library',
					'wp-hooks',
					'divi-wpdt-d5-vb-config',
					'wdt-bootstrap',
					'wdt-wpdatatables',
					'wpdatatables-render-chart',
				),
				'enqueue_top_window' => false,
				'enqueue_app_window' => true,
			),
		)
	);
}

add_action( 'divi_visual_builder_assets_before_enqueue_scripts', 'divi_wpdt_d5_enqueue_visual_builder_assets' );

/**
 * Convert page-builder list (id => label) to Divi 5 select options shape.
 *
 * @param array $list Options from getAllTablesAndChartsForPageBuilders().
 * @return array
 */
function divi_wpdt_d5_select_options_from_list( $list ) {
	$options = array();
	if ( ! is_array( $list ) ) {
		return $options;
	}
	foreach ( $list as $value => $label ) {
		$options[ (string) $value ] = array(
			'label' => (string) $label,
		);
	}

	return $options;
}

/**
 * Stylesheet link for Divi VB preview (iframe does not run wp_enqueue_style from shortcode).
 *
 * @param string $url Absolute stylesheet URL.
 * @return string
 */
function divi_wpdt_d5_preview_link_stylesheet( $url ) {
	if ( ! is_string( $url ) || $url === '' ) {
		return '';
	}
	if ( strpos( $url, 'fonts.googleapis.com' ) !== false ) {
		$href = $url;
	} else {
		$sep  = ( strpos( $url, '?' ) !== false ) ? '&' : '?';
		$href = $url . $sep . 'ver=' . rawurlencode( (string) WDT_CURRENT_VERSION );
	}

	return '<link rel="stylesheet" href="' . esc_url( $href ) . '" />';
}

/**
 * Skin CSS URL (same mapping as WPDataTable::enqueueJSAndStyles).
 *
 * @param string $skin Skin slug.
 * @return string
 */
function divi_wpdt_d5_preview_skin_stylesheet_url( $skin ) {
	$skin = is_string( $skin ) ? $skin : '';
	if ( $skin === '' ) {
		$skin = 'light';
	}
	switch ( $skin ) {
		case 'material':
			return WDT_ASSETS_PATH . 'css/wdt-skins/material.css';
		case 'light':
			return WDT_ASSETS_PATH . 'css/wdt-skins/light.css';
		case 'graphite':
			return WDT_ASSETS_PATH . 'css/wdt-skins/graphite.css';
		case 'aqua':
			return WDT_ASSETS_PATH . 'css/wdt-skins/aqua.css';
		case 'purple':
			return WDT_ASSETS_PATH . 'css/wdt-skins/purple.css';
		case 'dark':
			return WDT_ASSETS_PATH . 'css/wdt-skins/dark.css';
		case 'raspberry-cream':
			return WDT_ASSETS_PATH . 'css/wdt-skins/raspberry-cream.css';
		case 'mojito':
			return WDT_ASSETS_PATH . 'css/wdt-skins/mojito.css';
		case 'dark-mojito':
			return WDT_ASSETS_PATH . 'css/wdt-skins/dark-mojito.css';
		default:
			return WDT_ASSETS_PATH . 'css/wdt-skins/material.css';
	}
}

/**
 * Build <link> markup for frontend CSS so the VB canvas matches a normal shortcode render.
 *
 * @param string $shortcode Shortcode string being previewed.
 * @return string
 */
function divi_wpdt_d5_preview_get_stylesheet_link_markup( $shortcode ) {
	if ( ! is_string( $shortcode ) || ! class_exists( 'WDTConfigController' ) ) {
		return '';
	}

	if ( preg_match( '/^\[wpdatachart\b/i', $shortcode ) ) {
		$markup = '';
		if ( defined( 'WDT_HC_ASSETS_URL' ) ) {
			$markup .= divi_wpdt_d5_preview_link_stylesheet( WDT_HC_ASSETS_URL . 'css/wdt-highcharts.css' );
		}
		return $markup;
	}

	if ( ! preg_match( '/^\[wpdatatable\b/i', $shortcode ) ) {
		return '';
	}

	if ( ! preg_match( '/\bid=(\d+)/i', $shortcode, $matches ) ) {
		return '';
	}

	$table_id = absint( $matches[1] );
	if ( ! $table_id ) {
		return '';
	}

	try {
		$table = WDTConfigController::loadTableFromDB( $table_id, true );
	} catch ( \Throwable $e ) {
		return '';
	}

	if ( empty( $table ) || ! is_object( $table ) ) {
		return '';
	}

	$markup = '';

	if ( get_option( 'wdtIncludeGoogleFonts' ) ) {
		$markup .= divi_wpdt_d5_preview_link_stylesheet( 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap' );
		$markup .= divi_wpdt_d5_preview_link_stylesheet( 'https://fonts.googleapis.com/css?family=Roboto:wght@400;500&display=swap' );
	}

	if ( isset( $table->table_type ) && $table->table_type === 'simple' ) {
		$markup .= divi_wpdt_d5_preview_link_stylesheet( WDT_CSS_PATH . 'wdt.simpleTable.css' );
		$markup .= divi_wpdt_d5_preview_link_stylesheet( WDT_CSS_PATH . 'style.min.css' );
		return $markup;
	}

	// Mirrors WDTTools::wdtUIKitEnqueueNotEdit + WPDataTable::enqueueJSAndStyles (CSS only).
	$markup .= divi_wpdt_d5_preview_link_stylesheet( WDT_CSS_PATH . 'bootstrap/wpdatatables-bootstrap.css' );
	$markup .= divi_wpdt_d5_preview_link_stylesheet( WDT_CSS_PATH . 'bootstrap/bootstrap-select/bootstrap-select.min.css' );
	$markup .= divi_wpdt_d5_preview_link_stylesheet( WDT_CSS_PATH . 'animate/animate.min.css' );
	$markup .= divi_wpdt_d5_preview_link_stylesheet( WDT_CSS_PATH . 'uikit/uikit.css' );

	if ( get_option( 'wdtMinifiedJs' ) ) {
		if ( defined( 'WDT_FCH_INTEGRATION' ) ) {
			$markup .= divi_wpdt_d5_preview_link_stylesheet( WDT_CSS_PATH . 'wdt.frontend.min.css' );
		} else {
			$markup .= divi_wpdt_d5_preview_link_stylesheet( WDT_CSS_PATH . 'wdt.frontend-starter.min.css' );
		}
	} else {
		$markup .= divi_wpdt_d5_preview_link_stylesheet( WDT_CSS_PATH . 'wpdatatables.min.css' );
		$markup .= divi_wpdt_d5_preview_link_stylesheet( WDT_CSS_PATH . 'TableTools.css' );
		$markup .= divi_wpdt_d5_preview_link_stylesheet( WDT_CSS_PATH . 'datatables.responsive.css' );
	}

	$skin = ! empty( $table->tableSkin ) ? $table->tableSkin : get_option( 'wdtBaseSkin' );
	if ( empty( $skin ) ) {
		$skin = 'light';
	}
	$markup .= divi_wpdt_d5_preview_link_stylesheet( divi_wpdt_d5_preview_skin_stylesheet_url( (string) $skin ) );
	$markup .= divi_wpdt_d5_preview_link_stylesheet( includes_url( 'css/dashicons.min.css' ) );

	return $markup;
}

/**
 * Remove frontend modal markup from HTML (modals are mounted on document.body in VB JS).
 *
 * @param string $html Table HTML.
 * @return string
 */
function divi_wpdt_d5_preview_strip_modals( $html ) {
	if ( ! is_string( $html ) || $html === '' ) {
		return $html;
	}

	$html = preg_replace(
		'/<div class="wpdt-c">[\s\S]*?<div id="wdt-frontend-modal"[\s\S]*?<!--\/ \.wpdt-c -->\s*/i',
		'',
		$html
	);
	$html = preg_replace(
		'/<div class="wpdt-c">[\s\S]*?<div id="wdt-delete-modal"[\s\S]*?<!--\/ \.wpdt-c -->\s*/i',
		'',
		$html
	);

	return $html;
}

/**
 * Prepare wpDataTables HTML for Divi 5 Visual Builder preview (innerHTML, no DataTables init).
 *
 * - Strip wdt-no-display (normally removed on first draw.dt).
 * - Prepend scoped CSS so the table is visible without wpDataTables frontend stylesheets in the iframe.
 *
 * @param string $html Shortcode-rendered HTML.
 * @return string
 */
function divi_wpdt_d5_preview_prepare_table_html( $html ) {
	if ( ! is_string( $html ) || $html === '' ) {
		return $html;
	}

	$html = preg_replace( '/\bwdt-no-display\b/', '', $html );

	$css = '<style class="divi-wpdt-d5-preview-table-fix">'
		. '.divi-wpdt-d5-preview-html table.wpDataTable{display:table!important;visibility:visible!important;opacity:1!important;}'
		. '.divi-wpdt-d5-preview-html .wpDataTablesWrapper{display:block!important;visibility:visible!important;}'
		. '.divi-wpdt-d5-preview-html .wdt-timeline-item{display:none!important;}'
		. '.divi-wpdt-d5-preview-html{position:relative;overflow:visible!important;pointer-events:auto!important;}'
		. '</style>';

	return $css . $html;
}

/**
 * Omit modal shells from Divi VB REST-rendered HTML; {@see WPDataTable::getModalHtml()} is mounted on body via JS instead.
 *
 * @param bool        $want_inline Whether inline markup was originally requested (preview mode / frontend filter).
 * @param mixed $table WPDataTable instance (unused).
 * @return bool
 */
function divi_wpdt_d5_preview_suppress_modal_inline_markup( $want_inline, $table ) {
	return false;
}

/**
 * REST: render wpDataTable / wpDataChart shortcode HTML for Divi 5 Visual Builder preview (authenticated editors only).
 *
 * @return void
 */
function divi_wpdt_d5_register_preview_rest_route() {
	register_rest_route(
		'wpdatatables/v1',
		'/divi5-preview',
		array(
			'methods'             => 'POST',
			'permission_callback' => static function ( \WP_REST_Request $request ) {
				return Divi_Wpdt_Shortcode_Helper::current_user_can_preview_shortcode( $request->get_param( 'shortcode' ) );
			},
			'callback'            => static function ( \WP_REST_Request $request ) {
				$shortcode = $request->get_param( 'shortcode' );
				if ( ! is_string( $shortcode ) ) {
					return new \WP_Error( 'invalid_shortcode', __( 'Invalid preview request.', 'wpdatatables' ), array( 'status' => 400 ) );
				}
				$shortcode = trim( $shortcode );
				if ( strlen( $shortcode ) > 2000 ) {
					return new \WP_Error( 'invalid_shortcode', __( 'Shortcode too long.', 'wpdatatables' ), array( 'status' => 400 ) );
				}
				if ( ! Divi_Wpdt_Shortcode_Helper::is_valid_preview_shortcode( $shortcode ) ) {
					return new \WP_Error( 'invalid_shortcode', __( 'Only wpDataTables shortcodes are allowed.', 'wpdatatables' ), array( 'status' => 400 ) );
				}

				$shortcode = Divi_Wpdt_Shortcode_Helper::enable_table_preview_mode_shortcode( $shortcode );

				add_filter(
					'wpdatatables_should_inline_modal_in_table_markup',
					'divi_wpdt_d5_preview_suppress_modal_inline_markup',
					5,
					2
				);
				$html = do_shortcode( $shortcode );
				remove_filter( 'wpdatatables_should_inline_modal_in_table_markup', 'divi_wpdt_d5_preview_suppress_modal_inline_markup', 5 );
				$modal_html = '';

				if ( false !== stripos( $shortcode, 'wpdatatable' ) && class_exists( 'WPDataTable' ) ) {
					$modal_html = WPDataTable::getModalHtml();
					$html       = divi_wpdt_d5_preview_strip_modals( $html );
				}

				$stylesheet_markup = divi_wpdt_d5_preview_get_stylesheet_link_markup( $shortcode );
				$html              = divi_wpdt_d5_preview_prepare_table_html( $stylesheet_markup . $html );

				return rest_ensure_response(
					array(
						'html'      => $html,
						'modalHtml' => $modal_html,
					)
				);
			},
		)
	);
}

add_filter(
	'wpdatatables_should_inline_frontend_modal',
	static function ( $inline ) {
		if ( $inline ) {
			return true;
		}

		return function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled();
	}
);

add_action( 'rest_api_init', 'divi_wpdt_d5_register_preview_rest_route' );
