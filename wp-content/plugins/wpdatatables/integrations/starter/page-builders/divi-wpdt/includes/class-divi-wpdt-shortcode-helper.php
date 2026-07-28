<?php
/**
 * Shared shortcode building and rendering for Divi 4 / Divi 5 wpDataTable modules.
 *
 * @package wpDataTables
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Divi_Wpdt_Shortcode_Helper
 */
class Divi_Wpdt_Shortcode_Helper {

	/**
	 * Normalize table/chart id from Divi select string "(id: 123)" or numeric string.
	 *
	 * @param mixed $raw_id Raw attribute value.
	 * @return int
	 */
	public static function normalize_numeric_id( $raw_id ) {
		$table_id = $raw_id;
		if ( ! is_numeric( $table_id ) ) {
			if ( is_string( $table_id ) && false !== strpos( $table_id, '(id:' ) ) {
				$table_id = substr( $table_id, strrpos( $table_id, '(id:' ) + 4 );
				$table_id = substr( $table_id, 0, strrpos( $table_id, ')' ) );
			}
		}

		return (int) $table_id;
	}

	/**
	 * Escape a value for use inside a double-quoted shortcode attribute.
	 *
	 * @param mixed $value Attribute value.
	 * @return string
	 */
	public static function escape_shortcode_attr( $value ) {
		return str_replace(
			array( '\\', '"' ),
			array( '\\\\', '\\"' ),
			(string) $value
		);
	}

	/**
	 * Build [wpdatatable ...] shortcode string (no do_shortcode).
	 *
	 * @param array $props Keys: id, view, var1..var9, export_file_name (optional).
	 * @return string
	 */
	public static function build_wpdatatable_shortcode( array $props ) {
		$table_id = self::normalize_numeric_id( isset( $props['id'] ) ? $props['id'] : 0 );
		$view      = isset( $props['view'] ) ? $props['view'] : 'regular';

		$shortcode = '[wpdatatable id=' . $table_id;
		$shortcode .= 'excel-like' === $view ? ' table_view=excel' : ' table_view=regular';

		for ( $i = 1; $i <= 9; $i++ ) {
			$key = 'var' . $i;
			if ( ! empty( $props[ $key ] ) ) {
				$shortcode .= ' var' . $i . '="' . self::escape_shortcode_attr( $props[ $key ] ) . '"';
			}
		}
		if ( ! empty( $props['export_file_name'] ) ) {
			$shortcode .= ' export_file_name="' . self::escape_shortcode_attr( $props['export_file_name'] ) . '"';
		}
		$shortcode .= ']';

		return $shortcode;
	}

	/**
	 * Build [wpdatachart ...] shortcode string.
	 *
	 * @param array $props Keys: id.
	 * @return string
	 */
	public static function build_wpdatachart_shortcode( array $props ) {
		$chart_id = self::normalize_numeric_id( isset( $props['id'] ) ? $props['id'] : 0 );

		return '[wpdatachart id=' . $chart_id . ']';
	}

	/**
	 * Render wpDataTable block from props (notices or shortcode output).
	 *
	 * @param array $props Same shape as Divi module props / attrs flat map.
	 * @return string
	 */
	public static function render_wpdatatable_from_props( array $props ) {
		$table_id = self::normalize_numeric_id( isset( $props['id'] ) ? $props['id'] : 0 );

		if ( ! $table_id ) {
			$tables = WDTConfigController::getAllTablesAndChartsForPageBuilders( 'divi', 'tables' );
			if ( count( $tables ) === 1 ) {
				return WDTConfigController::wdt_create_table_notice();
			}

			return WDTConfigController::wdt_select_table_notice();
		}

		return do_shortcode( self::build_wpdatatable_shortcode( $props ) );
	}

	/**
	 * Render wpDataChart from props.
	 *
	 * @param array $props Keys: id.
	 * @return string
	 */
	public static function render_wpdatachart_from_props( array $props ) {
		$chart_id = self::normalize_numeric_id( isset( $props['id'] ) ? $props['id'] : 0 );

		if ( ! $chart_id ) {
			$charts = WDTConfigController::getAllTablesAndChartsForPageBuilders( 'divi', 'charts' );
			if ( count( $charts ) === 1 ) {
				return WDTConfigController::wdt_create_chart_notice();
			}

			return WDTConfigController::wdt_select_chart_notice();
		}

		return do_shortcode( self::build_wpdatachart_shortcode( $props ) );
	}

	/**
	 * Whether the string is exactly one wpDataTable/wpDataChart shortcode (no chaining or trailing content).
	 *
	 * @param string $shortcode Shortcode string.
	 * @return bool
	 */
	public static function is_valid_preview_shortcode( $shortcode ) {
		if ( ! is_string( $shortcode ) ) {
			return false;
		}

		$shortcode = trim( $shortcode );
		if ( strpos( $shortcode, '[' ) !== 0 ) {
			return false;
		}
		if ( strrpos( $shortcode, ']' ) !== strlen( $shortcode ) - 1 ) {
			return false;
		}
		if ( substr_count( $shortcode, '[' ) !== 1 || substr_count( $shortcode, ']' ) !== 1 ) {
			return false;
		}

		return (bool) preg_match( '/^\[(wpdatatable|wpdatachart)\b/i', $shortcode );
	}

	/**
	 * Parse a wpDataTables shortcode reference to its tag + numeric id.
	 *
	 * @param string $shortcode Shortcode string.
	 * @return array|null
	 */
	public static function parse_shortcode_reference( $shortcode ) {
		if ( ! is_string( $shortcode ) ) {
			return null;
		}

		$shortcode = trim( $shortcode );
		if ( ! self::is_valid_preview_shortcode( $shortcode ) ) {
			return null;
		}

		if ( ! preg_match( '/^\[(wpdatatable|wpdatachart)\b/i', $shortcode, $tag_match ) ) {
			return null;
		}

		if ( ! preg_match( '/\bid=(\d+)/i', $shortcode, $id_match ) ) {
			return null;
		}

		$item_id = absint( $id_match[1] );
		if ( ! $item_id ) {
			return null;
		}

		return array(
			'tag' => strtolower( $tag_match[1] ),
			'id'  => $item_id,
		);
	}

	/**
	 * Whether the current user can preview the provided shortcode in Divi.
	 *
	 * @param string $shortcode Shortcode string.
	 * @return bool
	 */
	public static function current_user_can_preview_shortcode( $shortcode ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		if ( ! self::is_valid_preview_shortcode( $shortcode ) ) {
			return false;
		}

		$shortcode_reference = self::parse_shortcode_reference( $shortcode );
		if ( null === $shortcode_reference || ! class_exists( 'WDTPermissionsEnforcer' ) ) {
			return false;
		}

		if ( 'wpdatatable' === $shortcode_reference['tag'] ) {
			return WDTPermissionsEnforcer::canUserViewTable( $shortcode_reference['id'] );
		}

		return WDTPermissionsEnforcer::canUserViewChart( $shortcode_reference['id'] );
	}

	/**
	 * Append preview mode flag to wpDataTable shortcodes.
	 *
	 * @param string $shortcode Shortcode string.
	 * @return string
	 */
	public static function enable_table_preview_mode_shortcode( $shortcode ) {
		$shortcode_reference = self::parse_shortcode_reference( $shortcode );
		if ( null === $shortcode_reference || 'wpdatatable' !== $shortcode_reference['tag'] ) {
			return $shortcode;
		}

		if ( preg_match( '/\bpreview_mode=/i', $shortcode ) ) {
			return preg_replace( '/\bpreview_mode=\S+/i', 'preview_mode=1', $shortcode, 1 );
		}

		return preg_replace( '/\]$/', ' preview_mode=1]', $shortcode, 1 );
	}

	/**
	 * Whether Divi 5 builder APIs are active (official helper when available).
	 *
	 * @return bool
	 */
	public static function is_divi_5_builder_context() {
		return function_exists( 'et_builder_d5_enabled' ) && et_builder_d5_enabled();
	}

	/**
	 * Load Divi 5 DependencyInterface if present (theme or Divi Builder plugin paths).
	 *
	 * @return bool True if interface is available.
	 */
	public static function ensure_divi5_dependency_interface() {
		if ( interface_exists( '\ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface' ) ) {
			return true;
		}
		$candidates = array(
			get_template_directory() . '/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php',
			get_stylesheet_directory() . '/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php',
			WP_PLUGIN_DIR . '/divi-builder/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php',
		);
		foreach ( $candidates as $path ) {
			if ( $path && file_exists( $path ) ) {
				require_once $path;
				break;
			}
		}

		return interface_exists( '\ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface' );
	}

	/**
	 * URL base for the Divi WPDT extension (directory containing divi-wpdt.php).
	 *
	 * @return string
	 */
	public static function extension_base_url() {
		return plugin_dir_url( dirname( __DIR__ ) . '/divi-wpdt.php' );
	}

	/**
	 * Enqueue WooCommerce integration script used by Divi wpDataTable module (D4/D5).
	 *
	 * @return void
	 */
	public static function enqueue_woo_commerce_divi_script() {
		if ( ! defined( 'WDT_WOO_COMMERCE_INTEGRATION' ) ) {
			return;
		}
		$src = self::extension_base_url() . 'includes/modules/WpDataTable/wdt-custom-divi-js.js';
		wp_enqueue_script( 'wdt-custom-divi-js', $src, array( 'jquery' ), WDT_CURRENT_VERSION, true );
		wp_localize_script(
			'wdt-custom-divi-js',
			'wdt_ajax_object',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Enqueue wpDataTables frontend scripts in Divi Visual Builder so DataTables can run after preview HTML is injected.
	 *
	 * Mirrors WPDataTable::enqueueJSAndStyles enough for canvas preview (document.ready runs before injected markup).
	 *
	 * @return void
	 */
	public static function enqueue_divi5_visual_builder_wpdatatable_assets() {
		if ( ! function_exists( 'et_core_is_fb_enabled' ) || ! et_core_is_fb_enabled() ) {
			return;
		}
		if ( ! defined( 'WDT_ROOT_URL' ) || ! class_exists( 'WDTTools' ) ) {
			return;
		}

		WDTTools::wdtUIKitEnqueueNotEdit();

		wp_enqueue_script(
			'wdt-common',
			WDT_ROOT_URL . 'assets/js/wpdatatables/admin/common.js',
			array(),
			WDT_CURRENT_VERSION,
			true
		);

		if ( get_option( 'wdtMinifiedJs' ) ) {
			WDTTools::wdtUIKitEnqueue();
			if ( defined( 'WDT_FCH_INTEGRATION' ) ) {
				wp_enqueue_style( 'wdt-wpdatatables', WDT_CSS_PATH . 'wdt.frontend.min.css', array(), WDT_CURRENT_VERSION );
				wp_enqueue_script(
					'wdt-wpdatatables',
					WDT_JS_PATH . 'wpdatatables/wdt.frontend.min.js',
					array( 'wdt-common', 'wdt-bootstrap' ),
					WDT_CURRENT_VERSION,
					true
				);
			} else {
				wp_enqueue_style( 'wdt-wpdatatables', WDT_CSS_PATH . 'wdt.frontend-starter.min.css', array(), WDT_CURRENT_VERSION );
				wp_enqueue_script(
					'wdt-wpdatatables',
					WDT_JS_PATH . 'wpdatatables/wdt.frontend-starter.min.js',
					array( 'wdt-common', 'wdt-bootstrap' ),
					WDT_CURRENT_VERSION,
					true
				);
			}
			wp_localize_script( 'wdt-wpdatatables', 'wdt_ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
			wp_localize_script( 'wdt-wpdatatables', 'wpdatatables_inline_strings', WDTTools::getTranslationStringsInlineEditing() );
			wp_localize_script( 'wdt-wpdatatables', 'wpdatatables_filter_strings', WDTTools::getTranslationStringsColumnFilter() );
			wp_localize_script( 'wdt-common', 'wpdatatables_edit_strings', WDTTools::getTranslationStringsCommon() );
			wp_localize_script( 'wdt-wpdatatables', 'wpdatatables_functions_strings', WDTTools::getTranslationStringsFunctions() );
		} else {
			wp_enqueue_style( 'wdt-wpdatatables', WDT_CSS_PATH . 'wpdatatables.min.css', array(), WDT_CURRENT_VERSION );
			wp_enqueue_style( 'wdt-table-tools', WDT_CSS_PATH . 'TableTools.css', array(), WDT_CURRENT_VERSION );
			wp_enqueue_style( 'wdt-datatables-responsive', WDT_CSS_PATH . 'datatables.responsive.css', array(), WDT_CURRENT_VERSION );

			if ( defined( 'WDT_INCLUDE_DATATABLES_CORE' ) && WDT_INCLUDE_DATATABLES_CORE ) {
				wp_enqueue_script(
					'wdt-datatables',
					WDT_JS_PATH . 'jquery-datatables/jquery.dataTables.min.js',
					array(),
					WDT_CURRENT_VERSION,
					true
				);
				if ( defined( 'WDT_WOO_COMMERCE_INTEGRATION' ) ) {
					wp_enqueue_script(
						'wdt-select',
						WDT_JS_PATH . 'jquery-datatables/dataTables.select.min.js',
						array(),
						WDT_CURRENT_VERSION,
						true
					);
				}
			}

			WDTTools::wdtUIKitEnqueue();
			wp_enqueue_script(
				'wdt-advanced-filter',
				WDT_JS_PATH . 'wpdatatables/wdt.columnFilter.js',
				array(),
				WDT_CURRENT_VERSION,
				true
			);
			wp_localize_script( 'wdt-advanced-filter', 'wdt_ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
			wp_localize_script( 'wdt-advanced-filter', 'wpdatatables_filter_strings', WDTTools::getTranslationStringsColumnFilter() );

			wp_enqueue_script(
				'wdt-row-grouping',
				WDT_JS_PATH . 'jquery-datatables/jquery.dataTables.rowGrouping.js',
				array( 'jquery', 'wdt-datatables' ),
				WDT_CURRENT_VERSION,
				true
			);
			wp_enqueue_script(
				'wdt-buttons',
				WDT_JS_PATH . 'export-tools/dataTables.buttons.min.js',
				array( 'jquery', 'wdt-datatables' ),
				WDT_CURRENT_VERSION,
				true
			);
			wp_enqueue_script(
				'wdt-buttons-html5',
				WDT_JS_PATH . 'export-tools/buttons.html5.min.js',
				array( 'jquery', 'wdt-datatables' ),
				WDT_CURRENT_VERSION,
				true
			);
			wp_enqueue_script(
				'wdt-button-print',
				WDT_JS_PATH . 'export-tools/buttons.print.min.js',
				array( 'jquery', 'wdt-datatables' ),
				WDT_CURRENT_VERSION,
				true
			);
			wp_enqueue_script(
				'wdt-button-vis',
				WDT_JS_PATH . 'export-tools/buttons.colVis.min.js',
				array( 'jquery', 'wdt-datatables' ),
				WDT_CURRENT_VERSION,
				true
			);
			wp_localize_script( 'wdt-common', 'wpdatatables_edit_strings', WDTTools::getTranslationStringsCommon() );
			wp_enqueue_script(
				'wdt-responsive',
				WDT_JS_PATH . 'responsive/datatables.responsive.js',
				array(),
				WDT_CURRENT_VERSION,
				true
			);
			wp_enqueue_script(
				'wdt-jquery-mask-money',
				WDT_JS_PATH . 'maskmoney/jquery.maskMoney.js',
				array( 'jquery' ),
				WDT_CURRENT_VERSION,
				true
			);
			wp_enqueue_script(
				'wdt-funcs-js',
				WDT_JS_PATH . 'wpdatatables/wdt.funcs.js',
				array( 'jquery', 'wdt-datatables', 'wdt-common' ),
				WDT_CURRENT_VERSION,
				true
			);
			wp_enqueue_script(
				'wdt-wpdatatables',
				WDT_JS_PATH . 'wpdatatables/wpdatatables.js',
				array( 'jquery', 'wdt-datatables' ),
				WDT_CURRENT_VERSION,
				true
			);
		}

		wp_enqueue_script( 'underscore' );
		wp_localize_script( 'wdt-wpdatatables', 'wpdatatables_settings', WDTTools::getDateTimeSettings() );
		wp_localize_script( 'wdt-wpdatatables', 'wpdatatables_frontend_strings', WDTTools::getTranslationStringsWpDataTables() );
		wp_localize_script( 'wdt-wpdatatables', 'wdt_ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}

	/**
	 * Enqueue wpDataCharts frontend scripts for Divi Visual Builder (chartsRender runs on window load before injected preview).
	 *
	 * @return void
	 */
	public static function enqueue_divi5_visual_builder_wpdatachart_assets() {
		if ( ! function_exists( 'et_core_is_fb_enabled' ) || ! et_core_is_fb_enabled() ) {
			return;
		}
		if ( ! defined( 'WDT_ROOT_URL' ) || ! class_exists( 'WDTTools' ) ) {
			return;
		}

		$js_ext      = get_option( 'wdtMinifiedJs' ) ? '.min.js' : '.js';
		$render_deps = array( 'jquery' );

		wp_enqueue_style(
			'wpdatatables-loader-chart',
			WDT_CSS_PATH . 'loaderChart.min.css',
			array(),
			WDT_CURRENT_VERSION
		);

		wp_enqueue_script(
			'wdt-chartjs',
			WDT_JS_PATH . 'wdtcharts/chartjs/Chart.js',
			array(),
			WDT_CURRENT_VERSION,
			true
		);
		wp_enqueue_script(
			'wpdatatables-chartjs',
			WDT_JS_PATH . 'wdtcharts/chartjs/wdt.chartJS' . $js_ext,
			array( 'jquery', 'wdt-chartjs' ),
			WDT_CURRENT_VERSION,
			true
		);
		$render_deps[] = 'wpdatatables-chartjs';

		$google_lib = get_option( 'wdtGoogleStableVersion' ) ? WDT_JS_PATH . 'wdtcharts/googlecharts/googlecharts.js' : 'https://www.gstatic.com/charts/loader.js';
		wp_enqueue_script( 'wdt-google-charts', $google_lib, array(), WDT_CURRENT_VERSION, true );
		wp_enqueue_script(
			'wpdatatables-google-chart',
			WDT_JS_PATH . 'wdtcharts/googlecharts/wdt.googleCharts' . $js_ext,
			array( 'jquery', 'wdt-google-charts' ),
			WDT_CURRENT_VERSION,
			true
		);
		$render_deps[] = 'wpdatatables-google-chart';

		if ( defined( 'WDT_HC_ASSETS_URL' ) ) {
			$stable_hc        = get_option( 'wdtHighChartStableVersion' );
			$hc_main          = $stable_hc ? WDT_HC_ASSETS_URL . 'js/highcharts.js' : 'https://code.highcharts.com/highcharts.js';
			$hc_more          = $stable_hc ? WDT_HC_ASSETS_URL . 'js/highcharts-more.js' : 'https://code.highcharts.com/highcharts-more.js';
			$hc_3d            = $stable_hc ? WDT_HC_ASSETS_URL . 'js/highcharts-3d.js' : 'https://code.highcharts.com/highcharts-3d.js';
			$hc_accessibility = $stable_hc ? WDT_HC_ASSETS_URL . 'js/accessibility.js' : 'https://code.highcharts.com/modules/accessibility.js';

			wp_enqueue_script( 'wdt-highcharts', $hc_main, array(), WDT_CURRENT_VERSION, true );
			wp_enqueue_script( 'wdt-highcharts-more', $hc_more, array( 'wdt-highcharts' ), WDT_CURRENT_VERSION, true );
			wp_enqueue_script( 'wdt-highcharts3d', $hc_3d, array( 'wdt-highcharts' ), WDT_CURRENT_VERSION, true );
			wp_enqueue_script( 'wdt-highcharts-accessibility', $hc_accessibility, array( 'wdt-highcharts' ), WDT_CURRENT_VERSION, true );
			wp_enqueue_script(
				'wpdatatables-highcharts',
				WDT_HC_ASSETS_URL . 'js/wdt.highcharts' . $js_ext,
				array( 'jquery', 'wdt-highcharts' ),
				WDT_CURRENT_VERSION,
				true
			);
			$render_deps[] = 'wpdatatables-highcharts';
		}

		if ( defined( 'WDT_AC_ASSETS_URL' ) ) {
			$stable_apex = get_option( 'wdtApexStableVersion' );
			$apex_lib    = $stable_apex ? WDT_AC_ASSETS_URL . 'js/apexcharts.js' : 'https://cdn.jsdelivr.net/npm/apexcharts';
			wp_enqueue_script( 'wdt-apexcharts', $apex_lib, array(), WDT_CURRENT_VERSION, true );
			wp_enqueue_script(
				'wpdatatables-apexcharts',
				WDT_AC_ASSETS_URL . 'js/wdt.apexcharts' . $js_ext,
				array( 'jquery', 'wdt-apexcharts' ),
				WDT_CURRENT_VERSION,
				true
			);
			$render_deps[] = 'wpdatatables-apexcharts';
		}

		if ( defined( 'WDT_HS_ASSETS_URL' ) && defined( 'WDT_HC_ASSETS_URL' ) ) {
			$stable_hs = get_option( 'wdtHighChartStableVersion' );
			$stock_src = $stable_hs ? WDT_HS_ASSETS_URL . 'js/highcharts-stock.js' : 'https://code.highcharts.com/stock/modules/stock.js';
			wp_enqueue_script( 'wdt-highstock-lib', $stock_src, array( 'wdt-highcharts' ), WDT_CURRENT_VERSION, true );
			wp_enqueue_script(
				'wpdatatables-highstock',
				WDT_HS_ASSETS_URL . 'js/wdt.highstock' . $js_ext,
				array( 'jquery', 'wdt-highcharts', 'wdt-highstock-lib', 'wpdatatables-highcharts' ),
				WDT_CURRENT_VERSION,
				true
			);
			$render_deps[] = 'wpdatatables-highstock';
		}

		wp_enqueue_script(
			'wpdatatables-render-chart',
			WDT_JS_PATH . 'wdtcharts/wdt.chartsRender' . $js_ext,
			$render_deps,
			WDT_CURRENT_VERSION,
			true
		);
		wp_localize_script(
			'wpdatatables-render-chart',
			'wpdatatables_mapsapikey',
			WDTTools::getGoogleApiMapsKey()
		);
	}

	/**
	 * Read innerContent desktop value from Divi 5 attrs tree.
	 *
	 * @param array  $attrs Divi 5 module attrs.
	 * @param string $key   Attribute key (e.g. "id", "view").
	 * @return string
	 */
	public static function divi5_inner_content_value( array $attrs, $key ) {
		if ( ! isset( $attrs[ $key ] ) || ! is_array( $attrs[ $key ] ) ) {
			return '';
		}
		$node = $attrs[ $key ];
		if ( ! isset( $node['innerContent'] ) || ! is_array( $node['innerContent'] ) ) {
			return '';
		}
		$ic = $node['innerContent'];
		foreach ( array( 'desktop', 'tablet', 'phone', 'mobile' ) as $bp ) {
			if ( isset( $ic[ $bp ]['value'] ) ) {
				$val = (string) $ic[ $bp ]['value'];
				if ( trim( $val ) !== '' ) {
					return $val;
				}
			}
		}
		if ( isset( $ic['value'] ) ) {
			return (string) $ic['value'];
		}

		return '';
	}

	/**
	 * Flatten Divi 5 attrs to legacy Divi 4-style props for wpDataTable.
	 *
	 * @param array $attrs Divi 5 attrs.
	 * @return array
	 */
	public static function flatten_wpdatatable_attrs_for_render( array $attrs ) {
		$props = array(
			'id'               => self::divi5_inner_content_value( $attrs, 'id' ),
			'view'             => self::divi5_inner_content_value( $attrs, 'view' ),
			'var1'             => self::divi5_inner_content_value( $attrs, 'var1' ),
			'var2'             => self::divi5_inner_content_value( $attrs, 'var2' ),
			'var3'             => self::divi5_inner_content_value( $attrs, 'var3' ),
			'var4'             => self::divi5_inner_content_value( $attrs, 'var4' ),
			'var5'             => self::divi5_inner_content_value( $attrs, 'var5' ),
			'var6'             => self::divi5_inner_content_value( $attrs, 'var6' ),
			'var7'             => self::divi5_inner_content_value( $attrs, 'var7' ),
			'var8'             => self::divi5_inner_content_value( $attrs, 'var8' ),
			'var9'             => self::divi5_inner_content_value( $attrs, 'var9' ),
			'export_file_name' => self::divi5_inner_content_value( $attrs, 'export_file_name' ),
		);
		if ( $props['view'] === '' ) {
			$props['view'] = 'regular';
		}

		return $props;
	}

	/**
	 * Flatten Divi 5 attrs for wpDataChart (chart id in `id` field).
	 *
	 * @param array $attrs Divi 5 attrs.
	 * @return array
	 */
	public static function flatten_wpdatachart_attrs_for_render( array $attrs ) {
		return array(
			'id' => self::divi5_inner_content_value( $attrs, 'id' ),
		);
	}
}
