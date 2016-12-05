<?php
/**
 * Options framework
 *
 * Contain all functions to display setting options on page
 *
 * @package   PT_Options_Framework
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
if ( !class_exists( 'PT_Options_Framework' ) ) {

	class PT_Options_Framework {

		private static $dependence_info; // Store dependency information of options

		public function __construct() {

		}

		/**
		 * Check dependence information & generate random id for dependency elements
		 *
		 * @param array $param       Array of parameters of a setting option
		 * @param array $dependence_ Global dependence array
		 *
		 * @return string|null
		 */
		public static function _dependence_check( $param, &$dependence_ ) {
			if ( isset( $param[ 'dependence' ] ) ) {
				// Depend array: 3 params in order : name (of param this param depends), value (of param this param depends), operator
				$dependence	 = (array) $param[ 'dependence' ];
				$random_id	 = PT_CV_PREFIX . 'dependence_' . PT_CV_Functions::string_random();

				// Single dependency relationship
				if ( !is_array( $dependence[ 0 ] ) ) {
					self::_dependence_assign( $dependence, $random_id, $dependence_ );
				} else {
					// Multiple dependency relationships
					foreach ( $dependence as $dp ) {
						self::_dependence_assign( $dp, $random_id, $dependence_ );
					}
				}

				return $random_id;
			}

			return NULL;
		}

		/**
		 * Assign dependency relationship
		 *
		 * @param array  $dependence  Array of dependence attributes
		 * @param string $random_id   Random string
		 * @param array  $dependence_ Global dependence array
		 */
		public static function _dependence_assign( $dependence, $random_id, &$dependence_ ) {
			if ( !isset( $dependence[ 1 ] ) ) {
				return;
			}
			$dependence_[ $dependence[ 0 ] ][] = array( $random_id, $dependence[ 1 ], isset( $dependence[ 2 ] ) ? $dependence[ 2 ] : '=' );
		}

		/**
		 * Settings for group of options
		 *
		 * @param array $options List of setting options
		 * @param array $data    Stored data of settings
		 *
		 * @return string
		 */
		public static function do_settings( $options, $data = array() ) {
			$result		 = $dependence_ = array();
			if ( !$options ) {
				return '';
			}

			foreach ( $options as $group ) {
				$result[] = self::group( $group, $data, $dependence_ );
			}
			if ( $dependence_ ) {
				self::$dependence_info[] = $dependence_;
			}

			return implode( '', $result );
		}

		/**
		 * Group of options
		 *
		 * @param array $group
		 * @param array $data        Stored data of settings
		 * @param array $dependence_ Global dependence array
		 *
		 * @return string
		 */
		public static function group( $group, $data, &$dependence_ ) {
			if ( empty( $group[ 'label' ] ) && empty( $group[ 'params' ] ) ) {
				return '';
			}

			$extra_setting	 = isset( $group[ 'extra_setting' ] ) ? $group[ 'extra_setting' ] : array();
			$label			 = self::label( $group[ 'label' ], $extra_setting );
			$params			 = self::params( $group[ 'params' ], $data, $extra_setting );
			$random_id		 = self::_dependence_check( $group, $dependence_ );
			$id				 = '';
			$class			 = isset( $extra_setting[ 'params' ][ 'group-class' ] ) ? $extra_setting[ 'params' ][ 'group-class' ] : '';
			if ( $random_id ) {
				$id = "id='$random_id'";
				$class .= ' hidden';
			}

			return "<div class='form-group pt-form-group $class' $id>$label $params</div>";
		}

		/**
		 * Label
		 *
		 * @param string $label Text for label
		 */
		public static function label( $label = array(), $extra_setting = array() ) {
			$for	 = isset( $label[ 'for' ] ) ? "for='{$label[ 'for' ]}'" : '';
			$width	 = 12 - ( isset( $extra_setting[ 'params' ][ 'width' ] ) ? intval( $extra_setting[ 'params' ][ 'width' ] ) : 10 );
			if ( $width ) {
				$html = "<label $for class='col-md-$width control-label'>" . $label[ 'text' ] . '</label>';
			} else {
				$html = '';
			}

			return $html;
		}

		/**
		 * Print params next to label
		 *
		 * @param string $params Array of setting options in a group
		 */
		public static function params( $params, $data, $extra_setting ) {
			$params_html = array();
			foreach ( (array) $params as $param ) {
				$params_html[] = self::field_type( (array) $param, $data ) . "\n";
			}
			$html				 = implode( '', $params_html );
			$param_wrap_class	 = isset( $extra_setting[ 'params' ][ 'wrap-class' ] ) ? esc_attr( $extra_setting[ 'params' ][ 'wrap-class' ] ) : '';
			$param_wrap_id		 = isset( $extra_setting[ 'params' ][ 'wrap-id' ] ) ? "id='" . esc_attr( $extra_setting[ 'params' ][ 'wrap-id' ] ) . "'" : '';
			$width				 = isset( $extra_setting[ 'params' ][ 'width' ] ) ? intval( $extra_setting[ 'params' ][ 'width' ] ) : 10;

			return "<div class='col-md-$width pt-params $param_wrap_class' $param_wrap_id>$html</div>";
		}

		/**
		 * Get value of field
		 *
		 * @param array $data  Stored data of settings
		 * @param array $param Array of parameters of a setting option
		 *
		 * @return string
		 */
		public static function field_value( $data, $param, $name ) {
			// Get name without []
			$single_name = rtrim( $name, '[]' );

			// Get value of field
			if ( $data ) {
				$value = isset( $data[ $single_name ] ) ? $data[ $single_name ] : '';
			} else {
				$value = isset( $param[ 'std' ] ) ? $param[ 'std' ] : '';
			}

			if ( $value === '' && (isset( $param[ 'std' ] ) && $param[ 'std' ] !== '') ) {
				if ( in_array( $param[ 'type' ], array( 'number', 'text', 'color' ) ) ) {
					if ( $name !== PT_CV_PREFIX . 'limit' ) {
						$value = $param[ 'std' ];
					}
				} else if ( in_array( $param[ 'type' ], array( 'radio', 'select' ) ) ) {
					if ( !array_key_exists( '', $param[ 'options' ] ) ) {
						$value = $param[ 'std' ];
					}
				}
			}

			return $value;
		}

		/**
		 * Print HTML code of field type: input, select, textarea...
		 *
		 * @param array $param Array of parameters of a setting option
		 * @param array $data  Array of stored data
		 *
		 * @return string
		 */
		public static function field_type( $param, $data, $value_ = NULL ) {
			if ( !$param || !isset( $param[ 'type' ] ) ) {
				return '';
			}
			$html	 = $extend	 = '';
			$class	 = 'form-control ' . ( isset( $param[ 'class' ] ) ? ' ' . PT_CV_PREFIX . $param[ 'class' ] : '' );

			$type		 = esc_attr( $param[ 'type' ] );
			$name		 = !empty( $param[ 'name' ] ) ? PT_CV_PREFIX . esc_attr( $param[ 'name' ] ) : '';
			$id			 = !empty( $param[ 'id' ] ) ? "id='" . PT_CV_PREFIX . esc_attr( $param[ 'id' ] ) . "'" : '';
			$value		 = isset( $value_ ) ? $value_ : self::field_value( $data, $param, $name );
			$description = isset( $param[ 'desc' ] ) ? $param[ 'desc' ] : '';
			$placeholder = isset( $param[ 'placeholder' ] ) ? esc_attr( $param[ 'placeholder' ] ) : '';

			// Add extra information of option type
			switch ( $type ) {
				case 'number':
					$min	 = !empty( $param[ 'min' ] ) ? intval( $param[ 'min' ] ) : 0;
					$extend	 = 'min="' . $min . '"';
					break;

				case 'color':
					$class .= ' ' . PT_CV_PREFIX . 'color';
					break;

				case 'checkbox':
				case 'radio':
					// Remove form-control class in checkbox, radio
					$class = str_replace( 'form-control', '', $class );
					break;
			}

			$class = esc_attr( $class );

			// Show HTML of option type
			switch ( $type ) {
				case 'group':
					$html .= self::do_settings( $param[ 'params' ], $data );
					break;

				case 'text':
				case 'email':
				case 'password':
				case 'number':
				case 'url':
					$value = !empty( $value ) ? (($type === 'number') ? intval( $value ) : esc_attr( $value )) : $value;

					$prepend_text	 = !empty( $param[ 'prepend_text' ] ) ? $param[ 'prepend_text' ] : '';
					$append_text	 = !empty( $param[ 'append_text' ] ) ? $param[ 'append_text' ] : '';

					$input = "<input type='$type' name='$name' value='$value' class='$class' $id $extend placeholder='$placeholder'>";

					if ( !empty( $prepend_text ) || !empty( $append_text ) ) {
						$input = "<div class='input-group'>{$prepend_text}{$input}<span class='input-group-addon'>{$append_text}</span></div>";
					}

					$html .= $input;
					break;

				case 'color':
					$value = esc_attr( $value );

					$html .= "<input type='text' name='$name' value='$value' class='$class' $id $extend style='background-color:$value;'>";
					$html .= "<div class='" . PT_CV_PREFIX . "colorpicker' style='z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;'></div><br>";
					break;

				case 'textarea':
					$value = esc_textarea( $value );
					$html .= "<textarea name='$name' class='$class' $id $extend placeholder='$placeholder' rows=4>$value</textarea>";
					break;

				case 'checkbox':
				case 'radio':
					if ( !isset( $param[ 'options' ] ) ) {
						break;
					}

					$settings = isset( $param[ 'settings' ] ) ? $param[ 'settings' ] : array();
					foreach ( $param[ 'options' ] as $key => $text ) {
						// Append Html to $text, such as image...
						if ( $settings ) {
							$append = isset( $settings[ 'text-append' ] ) ? $settings[ 'text-append' ] : '';
							if ( $append == 'image' ) {
								$path = isset( $settings[ 'path' ] ) ? $settings[ 'path' ] : '';
								if ( $path ) {
									$text .= "<br> <img src='" . plugins_url( $path . "/$key.png", PT_CV_FILE ) . "' />";
								}
							}
						}

						$checked = ( in_array( $key, (array) $value ) || ( $value == 'all' ) ) ? 'checked' : '';
						$html .= "<div class='$type'><label><input type='$type' name='$name' value='$key' class='$class' $checked $id $extend>$text</label></div>";
					}

					break;

				case 'select':
					if ( !isset( $param[ 'options' ] ) ) {
						break;
					}

					if ( is_string( $value ) && strpos( $value, ',' ) !== false ) {
						$value = explode( ',', $value );
					}

					$options = '';
					foreach ( $param[ 'options' ] as $key => $text ) {
						$selected		 = ( in_array( str_replace( '&amp;', '&', $key ), (array) $value ) || ( $value == 'all' ) ) ? 'selected' : '';
						$option_class	 = isset( $param[ 'option_class_prefix' ] ) ? sprintf( "class='%s'", $param[ 'option_class_prefix' ] . esc_attr( sanitize_title( $key ) ) ) : '';
						$options .= "<option value='$key' $selected $option_class>$text</option>";
					}
					if ( empty( $options ) ) {
						$html .= PT_CV_Settings::setting_no_option( true );
					} else {
						$multiple = '';
						if ( ( isset( $param[ 'multiple' ] ) && $param[ 'multiple' ] == '1' ) || $value == 'all' ) {
							$multiple = 'multiple';
							// Auto add [] to name of select
							$name .= substr( $name, - 2 ) == '[]' ? '' : '[]';
						}
						$html .= "<select name='$name' class='$class' $multiple $id $extend>$options</select>";
					}
					break;

				case 'color_picker':
					$html .= self::field_type( $param[ 'options' ], $data );
					break;

				case 'html':
					if ( isset( $param[ 'content' ] ) ) {
						$html .= $param[ 'content' ];
					}
					break;

				case 'panel_group':
					// In format: key => array of params
					$parent_id	 = PT_CV_Functions::string_random( true );
					$settings	 = isset( $param[ 'settings' ] ) ? $param[ 'settings' ] : array();
					foreach ( $param[ 'params' ] as $key => $param_group ) {
						$html .= self::sub_panel_group( $key, $param_group, $data, $parent_id, $settings );
					}
					break;
			}

			$description = apply_filters( PT_CV_PREFIX_ . 'options_description', $description, $param );
			if ( !empty( $description ) ) {
				// Append dot to end of description
				if ( trim( strip_tags( $description ) ) != '' && substr( $description, -1 ) != '?' ) {
					$description .= '.';
				}
				// esc_html will break popover
				$html .= "<p class='text-muted'>$description</p>";
			}

			return $html;
		}

		/**
		 * HTML for group of params inside Panel group
		 *
		 * @param string $key
		 * @param array  $param_group Array of setting options in a group
		 * @param array  $data        Stored data of settings
		 * @param string $parent_id
		 * @param bool   $settings    : array of custom settings
		 *
		 * @return string
		 */
		static function sub_panel_group( $key, $param_group, $data, $parent_id, $settings = array() ) {

			// Content for body
			$content = self::do_settings( $param_group, $data );
			// Class for wrapper
			$class	 = PT_CV_Html::html_group_class();
			$class .= ( isset( $settings[ 'show_all' ] ) ? '' : ' hidden' );
			$class .= ( isset( $settings[ 'show_only_one' ] ) ? ' ' . PT_CV_PREFIX . 'only-one' : '' );
			$class .= ( isset( $settings[ 'no_panel' ] ) ? ' ' . PT_CV_PREFIX . 'no-panel' : '' );
			// Id for wrapper
			$id		 = PT_CV_Html::html_group_id( $key );

			if ( !isset( $settings[ 'no_panel' ] ) ) {
				if ( !empty( $param_group[ 'parent_label' ] ) ) {
					$heading = $param_group[ 'parent_label' ];
				} else {
					$heading = isset( $settings[ 'nice_name' ][ $key ] ) ? $settings[ 'nice_name' ][ $key ] : PT_CV_Functions::string_slug_to_text( $key );
				}

				$html = PT_CV_Html::html_collapse_one( $parent_id, $id . '-child', $heading, $content, true );
			} else {
				$html = $content;
			}

			return "<div class='$class' id='$id'>$html</div>";
		}

		/**
		 * Start Admin JS with dependency data
		 */
		public static function print_js() {
			?>
			<script>
				( function ( $ ) {
					"use strict";

					$( function () {
						new $.PT_CV_Admin( { _toggle_data: '<?php echo json_encode( self::$dependence_info ); ?>' } );
					} );
				}( jQuery ) );
			</script>
			<?php
		}

	}

}
