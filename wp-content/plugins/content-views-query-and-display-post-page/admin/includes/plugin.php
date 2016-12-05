<?php
/**
 * Form, option group, option name, option fields
 *
 * @package   PT_Content_Views_Admin
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
if ( !class_exists( 'PT_CV_Plugin' ) ) {

	/**
	 * @name PT_CV_Plugin
	 */
	class PT_CV_Plugin {

		/**
		 * Holds the values to be used in the fields callbacks
		 */
		static $options;

		/**
		 * Add custom filters/actions
		 */
		static function init() {

			// Action
			add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		}

		/**
		 * Content Views Settings page : section 1
		 */
		public static function settings_page_section_one() {

			$file_path = PT_CV_PATH . 'admin/includes/templates/settings-section-one.php';

			$text = PT_CV_Functions::file_include_content( $file_path );

			$text = apply_filters( PT_CV_PREFIX_ . 'settings_page_section_one', $text );

			echo $text;
		}

		/**
		 * Content Views Settings page : section 2
		 */
		public static function settings_page_section_two() {

			$file_path = PT_CV_PATH . 'admin/includes/templates/settings-section-two.php';

			$text = PT_CV_Functions::file_include_content( $file_path );

			$text = apply_filters( PT_CV_PREFIX_ . 'settings_page_section_two', $text );

			echo $text;
		}

		/**
		 * Form in Settings page
		 */
		public static function settings_page_form() {
			ob_start();

			self::$options	 = get_option( PT_CV_OPTION_NAME );
			?>
			<form method="post" action="options.php" class="cvform">
				<?php
				// This prints out all hidden setting fields
				settings_fields( PT_CV_OPTION_NAME . '_group' );
				do_settings_sections( PT_CV_DOMAIN );
				submit_button();
				?>
			</form>
			<?php
			$text			 = ob_get_clean();

			echo $text;
		}

		/**
		 * Register option group, option name, option fields
		 */
		public static function register_settings() {

			register_setting(
				PT_CV_OPTION_NAME . '_group', PT_CV_OPTION_NAME, array( __CLASS__, 'field_sanitize' )
			);

			// Common setting Section
			$this_section = 'setting_frontend_assets';
			add_settings_section(
				$this_section, '', array( __CLASS__, 'section_callback_setting_frontend_assets' ), PT_CV_DOMAIN
			);

			// Define Common setting fields
			$frontend_assets_fields = array();

			// Filter Frontend assets option
			$frontend_assets_fields = apply_filters( PT_CV_PREFIX_ . 'frontend_assets_fields', $frontend_assets_fields );

			// Add classes to find callback function for extra options
			$defined_in_class = (array) apply_filters( PT_CV_PREFIX_ . 'defined_in_class', array() );

			// Register Common setting fields
			foreach ( $frontend_assets_fields as $field ) {
				$class = ( array_key_exists( $field[ 'id' ], $defined_in_class ) ) ? $defined_in_class[ $field[ 'id' ] ] : __CLASS__;
				self::field_register( $field, $this_section, $class );
			}

			do_action( PT_CV_PREFIX_ . 'settings_page' );
		}

		/**
		 * Sanitize each setting field as needed
		 *
		 * @param array $input Contains all settings fields as array keys
		 */
		public static function field_sanitize( $input ) {
			$new_input = array();

			foreach ( $input as $key => $value ) {
				$type				 = apply_filters( PT_CV_PREFIX_ . 'settings_page_field_sanitize', 'input', $key );
				$new_input[ $key ]	 = ($type === 'input') ? sanitize_text_field( $value ) : $value;
			}

			return $new_input;
		}

		/**
		 * Add settings field
		 *
		 * @param array  $field_info Field information
		 * @param string $section    Id of setting section
		 * @param string $class      Class name to find the callback function
		 */
		public static function field_register( $field_info, $section, $class = __CLASS__ ) {
			if ( !$field_info ) {
				return false;
			}

			add_settings_field(
				$field_info[ 'id' ], $field_info[ 'title' ], array( $class, 'field_callback_' . $field_info[ 'id' ] ), PT_CV_DOMAIN, $section
			);
		}

		/**
		 * Print any field
		 *
		 * @param string $field_name The ID of field
		 * @param string $field_type The type of field
		 * @param string $text       The label of field
		 * @param string $desc       Description text
		 */
		static function _field_print( $field_name, $field_type = 'text', $text = '', $desc = '' ) {

			// Get Saved value
			$field_value = isset( self::$options[ $field_name ] ) ? esc_attr( self::$options[ $field_name ] ) : '';
			$checked	 = '';

			if ( in_array( $field_type, array( 'checkbox', 'radio' ) ) ) {
				$checked	 = checked( 1, $field_value, false );
				// Reassign value for this option
				$field_value = 1;
			}

			$field_id = esc_attr( $field_name );

			printf(
				'<input type="%1$s" id="%2$s" name="%3$s[%2$s]" value="%4$s" %5$s /> ', esc_attr( $field_type ), $field_id, PT_CV_OPTION_NAME, $field_value, $checked
			);

			// For radio, checkbox field
			if ( !empty( $text ) ) {
				printf( '<label for="%s" class="label-for-option">%s</label>', $field_id, $text );
			}

			// Show description
			if ( !empty( $desc ) ) {
				printf( '<p class="description">%s</p>', $desc );
			}
		}

		/**
		 * Print the text for Common setting Section
		 */
		public static function section_callback_setting_frontend_assets() {

		}

	}

}