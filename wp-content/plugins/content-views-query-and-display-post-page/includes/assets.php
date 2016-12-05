<?php

/**
 * Asset management
 *
 * Register, enqueue, localize asset functions
 *
 * @package   PT_Content_Views
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
if ( !class_exists( 'PT_CV_Asset' ) ) {

	/**
	 * @name PT_CV_Asset
	 * @todo Register, enqueue, localize asset functions
	 */
	class PT_CV_Asset {

		// Prefix for handle of all assets
		static $prefix	 = PT_CV_PREFIX;
		// Array of style & script
		static $assets	 = array();

		/**
		 * version of assets
		 * if an asset doesn't have configed version, it will get plugin version as asset version
		 */
		static $version = array(
			'bootstrap'			 => '3.3.5',
			'select2'			 => '3.4.5',
			'select2-bootstrap'	 => '3.4.5',
		);

		/**
		 * Register assets to enqueue later
		 */
		static function register() {
			self::$assets[ 'style' ]	 = self::style();
			self::$assets[ 'script' ]	 = self::script();
		}

		/**
		 * Enqueue registered assets
		 *
		 * @param string $name   Asset slug name
		 * @param string $type   Asset type (css/js)
		 * @param array  $data   Asset data (url, depends, version...)
		 * @param string $prefix Prefix string for asset
		 */
		static function enqueue( $name, $type = 'script', $data = '', $prefix = '' ) {

			// If asset is registered, get handle information $data
			if ( array_key_exists( $name, self::$assets[ $type ] ) ) {
				$data = self::$assets[ $type ][ $name ];
			}

			// Do action
			self::action( $name, (array) $data, $type, 'enqueue', $prefix );
		}

		/**
		 * Styles list
		 *
		 * @return array
		 */
		static function style() {
			return array(
				'bootstrap'			 => array(
					'src' => plugins_url( 'public/assets/css/bootstrap.custom.min.css', PT_CV_FILE ),
				),
				'select2'			 => array(
					'src' => plugins_url( 'assets/select2/select2.min.css', PT_CV_FILE ),
				),
				'select2-bootstrap'	 => array(
					'src' => plugins_url( 'assets/select2/select2-bootstrap.min.css', PT_CV_FILE ),
				),
			);
		}

		/**
		 * Scripts list
		 *
		 * @return array
		 */
		static function script() {
			return array(
				'bootstrap'	 => array(
					'src'	 => plugins_url( 'public/assets/js/bootstrap.custom.min.js', PT_CV_FILE ),
					'deps'	 => array( 'jquery' ),
				),
				'select2'	 => array(
					'src'	 => plugins_url( 'assets/select2/select2.min.js', PT_CV_FILE ),
					'deps'	 => array( 'jquery' ),
				),
			);
		}

		/**
		 * Get version of handle
		 *
		 * @param string $name Asset slug name
		 * @param array  $data Asset data (url, depends, version...)
		 *
		 * @return string
		 */
		static function get_version( $name, $data ) {

			if ( isset( $data[ 'ver' ] ) ) {
				return $data[ 'ver' ];
			}

			if ( array_key_exists( $name, self::$version ) ) {
				return self::$version[ $name ];
			}

			return PT_CV_Functions::plugin_info( PT_CV_FILE, 'Version' );
		}

		/**
		 * Register / Enqueue a style / script
		 *
		 * @param string $name   Asset slug name
		 * @param string $data   Asset information
		 * @param string $type   Asset type (css/js)
		 * @param string $action Action to executing: enqueue / register
		 * @param string $prefix Prefix string for asset
		 */
		static function action( $name, $data, $type, $action, $prefix ) {
			$prefix_ = !empty( $prefix ) ? $prefix : self::$prefix;
			$handle	 = $prefix_ . $name . '-' . $type;
			$src	 = isset( $data[ 'src' ] ) ? $data[ 'src' ] : '';
			$deps	 = isset( $data[ 'deps' ] ) ? $data[ 'deps' ] : '';
			$ver	 = self::get_version( $name, $data );

			if ( $type == 'style' ) {
				$last_param = isset( $data[ 'media' ] ) ? $data[ 'media' ] : 'all';
			} else {
				// Auto enqueue script in footer
				$last_param = isset( $data[ 'in_footer' ] ) ? $data[ 'in_footer' ] : self::load_script_in_footer();
			}
			$function = "wp_{$action}_{$type}";
			if ( function_exists( $function ) ) {
				$function( $handle, $src, $deps, $ver, $last_param );
			}
		}

		/**
		 * Localize script
		 *
		 * @param string $name              Asset slug name
		 * @param string $object_name       The name of the variable which will contain the data
		 * @param string $translation_array Array of translation strings
		 * @param string $prefix            Prefix string for asset
		 */
		static function localize_script( $name, $object_name, $translation_array, $prefix = '' ) {
			$type	 = 'script';
			$prefix_ = !empty( $prefix ) ? $prefix : self::$prefix;

			foreach ( (array) $name as $nm ) {
				$handle = $prefix_ . $nm . '-' . $type;
				wp_localize_script( $handle, $object_name, $translation_array );
			}
		}

		/**
		 * Include asset files directly in page
		 *
		 * @param string $name   Asset slug name
		 * @param string $scr    The link to asset file
		 * @param string $type   Asset type (css/js)
		 * @param string $prefix Prefix string for asset
		 */
		static function include_inline( $name, $src, $type, $prefix = '' ) {
			$prefix_ = !empty( $prefix ) ? $prefix : self::$prefix;
			$handle	 = $prefix_ . $name . '-' . $type;

			switch ( $type ) {
				case 'js':
					printf( "<script type='text/javascript' src='%s'></script>", $src );
					break;

				case 'css':
					printf( "<link rel='stylesheet' id='%s' href='%s' type='text/css' media='all' />", esc_attr( $handle ), esc_url( $src ) );
					break;
			}
		}

		/**
		 * Check if load script at footer (by default) or header (when there was JS error/issue caused by another script)
		 * @since 1.7.9
		 * @return type
		 */
		private static function load_script_in_footer() {
			return is_admin() ? true : !get_option( PT_CV_SOLVE_SCRIPT_ERROR );
		}

	}

}

// Call to run
PT_CV_Asset::register();
