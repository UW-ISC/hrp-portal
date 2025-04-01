<?php
/**
 * Manages the Settings/Media Library Assistant Images tab
 *
 * @package Media Library Assistant
 * @since 3.25
 */

/**
 * Class MLA (Media Library Assistant) Settings Image implements the
 * Settings/Media Library Assistant Images tab
 *
 * @package Media Library Assistant
 * @since 3.25
 */
class MLASettings_Image {
	/**
	 * Object name for localizing JavaScript - MLA Image List Table
	 *
	 * @since 3.25
	 *
	 * @var	string
	 */
	const JAVASCRIPT_INLINE_EDIT_IMAGE_OBJECT = 'mla_inline_edit_settings_vars';

	/**
	 * Load the tab's Javascript files
	 *
	 * @since 3.25
	 *
	 * @param string $page_hook Name of the page being loaded
	 */
	public static function mla_admin_enqueue_scripts( $page_hook ) {
		global $wpdb, $wp_locale;

		// Without a tab value that matches ours, there's nothing to do
		if ( empty( $_REQUEST['mla_tab'] ) || 'image' !== $_REQUEST['mla_tab'] ) {
			return;
		}

		// Initialize common script variables
		$script_variables = array(
			'error' => __( 'Error while making the changes.', 'media-library-assistant' ),
			'ntdeltitle' => __( 'Remove From Bulk Edit', 'media-library-assistant' ),
			'notitle' => '(' . __( 'no slug', 'media-library-assistant' ) . ')',
			'comma' => _x( ',', 'tag_delimiter', 'media-library-assistant' ),
			'useSpinnerClass' => true,
			'ajax_nonce' => wp_create_nonce( MLASettings::JAVASCRIPT_INLINE_EDIT_IMAGE_SLUG, MLACore::MLA_ADMIN_NONCE_NAME ),
			'tab' => 'image',
			'fields' => array( 'original_slug', 'slug', 'name', 'width', 'height', 'horizontal', 'vertical' ),
			'checkboxes' => array( 'crop', 'disabled' ),
			'ajax_action' => MLASettings::JAVASCRIPT_INLINE_EDIT_IMAGE_SLUG,
		);

		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( MLASettings::JAVASCRIPT_INLINE_EDIT_IMAGE_SLUG,
			MLA_PLUGIN_URL . "js/mla-inline-edit-settings-scripts{$suffix}.js", 
			array( 'wp-lists', 'suggest', 'jquery' ), MLACore::mla_script_version(), false );

		wp_localize_script( MLASettings::JAVASCRIPT_INLINE_EDIT_IMAGE_SLUG,
			self::JAVASCRIPT_INLINE_EDIT_IMAGE_OBJECT, $script_variables );
	} // mla_admin_enqueue_scripts

	/**
	 * Save Image settings to the options table
 	 *
	 * @since 3.25
	 *
	 * @return	array	Message(s) reflecting the results of the operation
	 */
	private static function _save_image_settings( ) {
		$message_list = '';

		foreach ( MLACoreOptions::$mla_option_definitions as $key => $value ) {
			if ( 'image' === $value['tab'] ) {
				$message_list .= MLASettings::mla_update_option_row( $key, $value );
			} // image option
		} // foreach mla_options

		$page_content = array(
			'message' => __( 'Image settings saved.', 'media-library-assistant' ) . "\r\n",
			'body' => '' 
		);

		// Uncomment this for debugging.
		// $page_content['message'] .= $message_list;

		return $page_content;
	} // _save_image_settings

	/**
	 * Get an HTML select element representing a crop location
	 *
	 * @since 3.25
	 *
	 * @param	array	Display template array
	 * @param	string	HTML name attribute value
	 * @param	string	'horizontal'|'vertical'
	 * @param	string	currently selected Icon Type
	 *
	 * @return string HTML select element or empty string on failure.
	 */
	private static function _get_crop_location_dropdown( $templates, $name, $dimension, $selection = 'center' ) {
		if ( 'horizontal' === $dimension ) {
			$options = array (
				'left' => __( 'Left', 'media-library-assistant' ),
				'center' => __( 'Center', 'media-library-assistant' ),
				'right' => __( 'Right', 'media-library-assistant' ),
			);
		} else {
			$options = array (
				'top' => __( 'Top', 'media-library-assistant' ),
				'center' => __( 'Center', 'media-library-assistant' ),
				'bottom' => __( 'Bottom', 'media-library-assistant' ),
			);
		}
		
		if ( empty( $selection ) ) {
			$selection = 'center';
		}
		
		$option_template = $templates['crop-type-select-option'];
		$option_text = '';
		foreach ( $options as $slug => $text ) {
			$option_values = array (
				'selected' => ( $slug === $selection ) ? 'selected="selected"' : '',
				'value' => $slug,
				'text' => $text
			);

			$option_text .= MLAData::mla_parse_template( $option_template, $option_values );					
		} // foreach icon_type

		return MLAData::mla_parse_template( $templates['crop-type-select'], array( 'name' => $name, 'dimension' => $dimension, 'options' => $option_text ) );
	}

	/**
	 * Validate an incoming $_REQUEST for the Quick Edit area
	 *
	 * @since 3.25
 	 *
	 * @return array Sanitized image item
	 */
	private static function _sanitize_inline_image_item() {
		$item = array();

		if ( isset( $_REQUEST['original_slug'] ) ) {
			$item['original_slug'] = sanitize_title( wp_unslash( $_REQUEST['original_slug'] ) );
		}
		
		$item['slug'] = isset( $_REQUEST['slug'] ) ? sanitize_title( wp_unslash( $_REQUEST['slug'] ) ) : '';
		$item['name'] = isset( $_REQUEST['name'] ) ? trim( wp_kses( wp_unslash( $_REQUEST['name'] ), 'post' ) ) : '';
		$item['width'] = isset( $_REQUEST['width'] ) ? absint( $_REQUEST['width'] ) : 0;
		$item['height'] = isset( $_REQUEST['height'] ) ? absint( $_REQUEST['height'] ) : 0;
		$item['crop'] = isset( $_REQUEST['crop'] );

		if ( in_array( $_REQUEST['horizontal'], array( 'left', 'right' ) ) ) {
			$item['horizontal'] = sanitize_title( wp_unslash( $_REQUEST['horizontal'] ) );
		} else {
			$item['horizontal'] = 'center';
		}

		if ( in_array( $_REQUEST['vertical'], array( 'top', 'bottom' ) ) ) {
			$item['vertical'] = sanitize_title( wp_unslash( $_REQUEST['vertical'] ) );
		} else {
			$item['vertical'] = 'center';
		}

		$item['disabled'] = isset( $_REQUEST['disabled'] );
		$item['description'] = isset( $_REQUEST['description'] ) ? trim( wp_kses( wp_unslash( $_REQUEST['description'] ), 'post' ) ) : '';
		$item['source'] = isset( $_REQUEST['source'] ) ? sanitize_title( wp_unslash( $_REQUEST['source'] ) ) : 'unknown';

		return $item;
	} // _sanitize_image_item

	/**
	 * Validate an incoming $_REQUEST['mla_image_item']
	 *
	 * @since 3.25
 	 *
	 * @return array Sanitized image item
	 */
	private static function _sanitize_image_item() {
		$item = array();

		if ( isset( $_REQUEST['mla_image_item']['original_slug'] ) ) {
			$item['original_slug'] = sanitize_title( wp_unslash( $_REQUEST['mla_image_item']['original_slug'] ) );
		}
		
		$item['slug'] = isset( $_REQUEST['mla_image_item']['slug'] ) ? sanitize_title( wp_unslash( $_REQUEST['mla_image_item']['slug'] ) ) : '';
		$item['name'] = isset( $_REQUEST['mla_image_item']['name'] ) ? trim( wp_kses( wp_unslash( $_REQUEST['mla_image_item']['name'] ), 'post' ) ) : '';
		$item['width'] = isset( $_REQUEST['mla_image_item']['width'] ) ? absint( $_REQUEST['mla_image_item']['width'] ) : 0;
		$item['height'] = isset( $_REQUEST['mla_image_item']['height'] ) ? absint( $_REQUEST['mla_image_item']['height'] ) : 0;
		$item['crop'] = isset( $_REQUEST['mla_image_item']['crop'] );

		if ( in_array( $_REQUEST['mla_image_item']['horizontal'], array( 'left', 'right' ) ) ) {
			$item['horizontal'] = sanitize_title( wp_unslash( $_REQUEST['mla_image_item']['horizontal'] ) );
		} else {
			$item['horizontal'] = 'center';
		}

		if ( in_array( $_REQUEST['mla_image_item']['vertical'], array( 'top', 'bottom' ) ) ) {
			$item['vertical'] = sanitize_title( wp_unslash( $_REQUEST['mla_image_item']['vertical'] ) );
		} else {
			$item['vertical'] = 'center';
		}

		$item['disabled'] = isset( $_REQUEST['mla_image_item']['disabled'] );
		$item['description'] = isset( $_REQUEST['mla_image_item']['description'] ) ? trim( wp_kses( wp_unslash( $_REQUEST['mla_image_item']['description'] ), 'post' ) ) : '';
		$item['source'] = isset( $_REQUEST['mla_image_item']['source'] ) ? sanitize_title( wp_unslash( $_REQUEST['mla_image_item']['source'] ) ) : 'unknown';

		return $item;
	} // _sanitize_image_item

	/**
	 * Compose the Edit Image tab content for the Settings subpage
	 *
	 * @since 3.25
	 *
	 * @param	array	data values for the item
	 * @param	string	Display templates
 	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private static function _compose_edit_image_tab( $item, $templates ) {
		$page_values = array(
			'Edit Image Size' => __( 'Edit Image Size', 'media-library-assistant' ),
			'form_url' => admin_url( 'options-general.php' ) . '?page=mla-settings-menu-image&mla_tab=image',
			'action' => MLACore::MLA_ADMIN_SINGLE_EDIT_UPDATE,
			'original_slug' => $item['slug'],
			'source' => $item['source'],
			'_wpnonce' => wp_nonce_field( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME, true, false ),
			'Slug' => __( 'Slug', 'media-library-assistant' ),
			'The slug' => __( 'The &#8220;slug&#8221; is the URL-friendly, unique key for the image size. It must be all lowercase and contain only letters, numbers, periods (.), slashes (/) and hyphens (-).', 'media-library-assistant' ),
			'Name' => __( 'Name', 'media-library-assistant' ),
			'The name' => __( 'The name is used for the size selection dropdown control and other display purposes.', 'media-library-assistant' ),
			'Width' => __( 'Width', 'media-library-assistant' ),
			'The width' => __( 'A positive integer setting the maximum image width in pixels.', 'media-library-assistant' ) . __( 'Use zero or leave blank to set no limit.', 'media-library-assistant' ),
			'Height' => __( 'Height', 'media-library-assistant' ),
			'The height' => __( 'A positive integer setting the maximum image height in pixels.', 'media-library-assistant' ) . __( 'Use zero or leave blank to set no limit.', 'media-library-assistant' ),
			'Crop' => __( 'Crop', 'media-library-assistant' ),
			'Check crop' => __( 'Check this box if you want to crop the image to an exact width and height.', 'media-library-assistant' ),
			'Horizontal' => __( 'Horizontal', 'media-library-assistant' ),
			'horizontal_dropdown' => self::_get_crop_location_dropdown( $templates, 'mla_image_item[horizontal]', 'horizontal', $item['horizontal'] ),
			'The horizontal' => __( 'Select a value for the crop location.', 'media-library-assistant' ),
			'Vertical' => __( 'Vertical', 'media-library-assistant' ),
			'vertical_dropdown' => self::_get_crop_location_dropdown( $templates, 'mla_image_item[vertical]', 'vertical', $item['vertical'] ),
			'The vertical' => __( 'Select a value for the crop location.', 'media-library-assistant' ),
			'Inactive' => __( 'Inactive', 'media-library-assistant' ),
			'Check inactive' => __( 'Check this box if you want to remove this entry from the list of sizes.', 'media-library-assistant' ),
			'Description' => __( 'Description', 'media-library-assistant' ),
			'The description' => __( 'The description can contain any documentation or notes you need to understand or use the item.', 'media-library-assistant' ),
			'Update' => __( 'Update', 'media-library-assistant' ),
			'Cancel' => __( 'Cancel', 'media-library-assistant' ),
		);

		foreach ( $item as $key => $value ) {
			switch ( $key ) {
				case 'crop':
				case 'disabled':
					$page_values[ $key ] = $value ? 'checked="checked"' : '';
					break;
				default:
					$page_values[ $key ] = $value;
			}
		}

		return array(
			'message' => '',
			'body' => MLAData::mla_parse_template( $templates['single-item-edit'], $page_values )
		);
	} // _compose_edit_image_tab

	/**
	 * Compose the Post MIME Type Images tab content for the Settings subpage
	 *
	 * @since 3.25
 	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	public static function mla_compose_image_tab( ) {
		$page_template_array = MLACore::mla_load_template( 'admin-display-settings-image-tab.tpl' );
		if ( ! is_array( $page_template_array ) ) {
			/* translators: 1: ERROR tag 2: function name 3: non-array value */
			$message = sprintf( _x( '%1$s: %2$s non-array "%3$s"', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), 'MLASettings_Image::mla_compose_image_tab', var_export( $page_template_array, true ) );
			MLACore::mla_debug_add( $message, MLACore::MLA_DEBUG_CATEGORY_ANY );
			return array( 'message' => $message, 'body' => '' );
		}

		// Set default values, check for Add New Post MIME Type Image button
		$add_form_values = array (
			'slug' => '',
			'name'  => '',
			'width'  => 0,
			'height' => 0,
			'crop'   => '',
			'horizontal' => '',
			'vertical' => '',
			'disabled' => '',
			'description' => '',
			'source' => 'core',
			);

		if ( !empty( $_REQUEST['mla-image-options-save'] ) ) {
			check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );
			$page_content = self::_save_image_settings( );
		} elseif ( !empty( $_REQUEST['mla-add-image-submit'] ) ) {
			check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );
			$page_content = MLAImage_Size::mla_add_image_size( self::_sanitize_image_item() );
			if ( false !== strpos( $page_content['message'], __( 'ERROR', 'media-library-assistant' ) ) ) {
				// If the add action failed, retain the settings and display the message(s)
				$add_form_values = self::_sanitize_image_item();
				$add_form_values['crop'] = $add_form_values['crop'] ? 'checked="checked"' : '';
				$add_form_values['disabled'] = $add_form_values['disabled'] ? 'checked="checked"' : '';
			}
		} else {
			$page_content = array(
				'message' => '',
				'body' => '' 
			);
		}

		// Process bulk actions that affect an array of items
		$bulk_action = MLASettings::mla_current_bulk_action();
		if ( $bulk_action && ( $bulk_action !== 'none' ) ) {
			if ( isset( $_REQUEST['cb_mla_item_ID'] ) ) {
				// Convert post-ID to slug; separate loop required because delete changes post_IDs
				$slugs = array();
				$post_ids = !empty( $_REQUEST['cb_mla_item_ID'] ) ? array_map( 'absint', stripslashes_deep( $_REQUEST['cb_mla_item_ID'] ) ) : array();
				foreach ( $post_ids as $post_ID ) {
					$slugs[] = MLAImage_Size::mla_get_image_size_slug( $post_ID );
				}

				foreach ( $slugs as $slug ) {
					switch ( $bulk_action ) {
						case 'delete':
							$item_content = MLAImage_Size::mla_delete_image_size( $slug );
							break;
						case 'edit':
							$request = array( 'slug' => $slug );
							if ( isset( $_REQUEST['crop'] ) && '-1' !== $_REQUEST['crop'] ) {
								$request['crop'] = '1' === $_REQUEST['crop'];
							}
							if ( isset( $_REQUEST['horizontal'] ) && '-1' !== $_REQUEST['horizontal'] ) {
								$request['horizontal'] = $_REQUEST['horizontal'];
							}
							if ( isset( $_REQUEST['vertical'] ) && '-1' !== $_REQUEST['vertical'] ) {
								$request['vertical'] = $_REQUEST['vertical'];
							}
							if ( isset( $_REQUEST['disabled'] ) && '-1' !== $_REQUEST['disabled'] ) {
								$request['disabled'] = '1' === $_REQUEST['disabled'];
							}
							$item_content = MLAImage_Size::mla_update_image_size( $request );
							break;
						default:
							$item_content = array(
								/* translators: 1: bulk_action, e.g., delete, edit, restore, trash */
								 'message' => sprintf( __( 'Unknown bulk action %1$s', 'media-library-assistant' ), $bulk_action ),
								'body' => '' 
							);
					} // switch $bulk_action

					$page_content['message'] .= $item_content['message'] . '<br>';
				} // foreach cb_attachment
			} // isset cb_attachment
			else {
				/* translators: 1: action name, e.g., edit */
				$page_content['message'] = sprintf( __( 'Bulk Action %1$s - no items selected.', 'media-library-assistant' ), $bulk_action );
			}
		} // $bulk_action

		// Process row-level actions that affect a single item
		if ( !empty( $_REQUEST['mla_admin_action'] ) ) {
			check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );
			$mla_item_slug = isset( $_REQUEST['mla_item_slug'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mla_item_slug'] ) ) : '';

			switch ( $_REQUEST['mla_admin_action'] ) {
				case MLACore::MLA_ADMIN_SINGLE_DELETE:
					$page_content = MLAImage_Size::mla_delete_image_size( $mla_item_slug );
					break;
				case MLACore::MLA_ADMIN_SINGLE_EDIT_DISPLAY:
					$item = MLAImage_Size::mla_get_image_size( $mla_item_slug );
					$page_content = self::_compose_edit_image_tab( $item, $page_template_array );
					break;
				case MLACore::MLA_ADMIN_SINGLE_EDIT_UPDATE:
					$mla_image_item = self::_sanitize_image_item();
					if ( !empty( $_REQUEST['update'] ) ) {
						$page_content = MLAImage_Size::mla_update_image_size( $mla_image_item );
						if ( false !== strpos( $page_content['message'], __( 'ERROR', 'media-library-assistant' ) ) ) {
							$message = $page_content['message'];
							$page_content = self::_compose_edit_image_tab( $mla_image_item, $page_template_array );
							$page_content['message'] = $message;
						}
					} else {
						$page_content = array(
							/* translators: 1: size name/slug */
							'message' => sprintf( __( 'Edit Image Size "%1$s" cancelled.', 'media-library-assistant' ), $mla_image_item['original_slug'] ),
							'body' => '' 
						);
					}
					break;
				default:
					$page_content = array(
						/* translators: 1: bulk_action, e.g., single_item_delete, single_item_edit */
						 'message' => sprintf( __( 'Unknown mla_admin_action - "%1$s"', 'media-library-assistant' ), sanitize_title( wp_unslash( $_REQUEST['mla_admin_action'] ) ) ),
						'body' => '' 
					);
					break;
			} // switch ($_REQUEST['mla_admin_action'])
		} // (!empty($_REQUEST['mla_admin_action'])

		if ( !empty( $page_content['body'] ) ) {
			return $page_content;
		}

		// Check for disabled status
		if ( 'checked' !== MLACore::mla_get_option( MLACoreOptions::MLA_ENABLE_IMAGE_SIZES ) ) {
			// Fill in with any page-level options
			$options_list = '';
			foreach ( MLACoreOptions::$mla_option_definitions as $key => $value ) {
				if ( MLACoreOptions::MLA_ENABLE_IMAGE_SIZES === $key ) {
					$options_list .= MLASettings::mla_compose_option_row( $key, $value );
				}
			}

			$page_values = array(
				'Support is disabled' => __( 'Image Size Support is disabled', 'media-library-assistant' ),
				'form_url' => admin_url( 'options-general.php' ) . '?page=mla-settings-menu-image&mla_tab=image',
				'options_list' => $options_list,
				'Save Changes' => __( 'Save Changes', 'media-library-assistant' ),
				'_wpnonce' => wp_nonce_field( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME, true, false ),
			);

			$page_content['body'] .= MLAData::mla_parse_template( $page_template_array['image-disabled'], $page_values );
			return $page_content;
		}

		// Display the Image Table
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$_SERVER['REQUEST_URI'] = remove_query_arg( array(
				'mla_admin_action',
				'mla_item_slug',
				'mla_item_ID',
				'_wpnonce',
				'_wp_http_referer',
				'action',
				'action2',
				'cb_mla_item_ID',
				'mla-edit-image-cancel',
				'mla-edit-image-submit',
				'mla-image-options-save',
			), $_SERVER['REQUEST_URI'] ); // phpcs:ignore
		}

		//	Create an instance of our package class
		$MLAListImageTable = new MLA_Image_List_Table();

		//	Fetch, prepare, sort, and filter our data
		$MLAListImageTable->prepare_items();

		// Start with any page-level options
		$options_list = '';
		foreach ( MLACoreOptions::$mla_option_definitions as $key => $value ) {
			if ( 'image' === $value['tab'] ) {
				$options_list .= MLASettings::mla_compose_option_row( $key, $value );
			}
		}

		// WPML requires that lang be the first argument after page
		$view_arguments = MLA_Image_List_Table::mla_submenu_arguments();
		$form_language = isset( $view_arguments['lang'] ) ? '&lang=' . $view_arguments['lang'] : '';
		$form_arguments = '?page=mla-settings-menu-image' . $form_language . '&mla_tab=image';

		// We need to remember all the view arguments
		$view_args = '';
		foreach ( $view_arguments as $key => $value ) {
			// 'lang' has already been added to the form action attribute
			if ( in_array( $key, array( 'lang' ) ) ) {
				continue;
			}

			if ( is_array( $value ) ) {
				foreach ( $value as $element_key => $element_value )
					$view_args .= "\t" . sprintf( '<input type="hidden" name="%1$s[%2$s]" value="%3$s" />', $key, $element_key, esc_attr( urldecode( $element_value ) ) ) . "\n";
			} else {
				$view_args .= "\t" . sprintf( '<input type="hidden" name="%1$s" value="%2$s" />', $key, esc_attr( urldecode( $value ) ) ) . "\n";
			}
		}

		$page_values = array(
			'Image Sizes Processing' => __( 'Image Sizes Processing', 'media-library-assistant' ),
			'In this tab' => __( 'In this tab you can manage the list of "Intermediate Image Sizes", which are used by WordPress to generate and access intermediate image sizes for Media Library items.', 'media-library-assistant' ),
			/* translators: 1: Documentation hyperlink */
			'You can find' => __( 'You can find more information about managing image sizes by clicking the <strong>"Help"</strong> tab in the upper-right corner of this screen.', 'media-library-assistant' ),
			'settingsURL' => admin_url('options-general.php'),
			'form_url' => admin_url( 'options-general.php' ) . '?page=mla-settings-menu-image&mla_tab=image',
			'view_args' => $view_args,
			'_wpnonce' => wp_nonce_field( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME, true, false ),
			'results' => ! empty( $_REQUEST['s'] ) ? '<h2 class="alignleft">' . __( 'Displaying search results for', 'media-library-assistant' ) . ': "' . esc_html( trim( wp_kses( wp_unslash( $_REQUEST['s'] ), 'post' ) ) ) . '"</h2>' : '',
			'Search Sizes' => __( 'Search Sizes', 'media-library-assistant' ),
			's' => isset( $_REQUEST['s'] ) ? esc_attr( trim( wp_kses( wp_unslash( $_REQUEST['s'] ), 'post' ) ) ) : '',
			'options_list' => $options_list,
			'Save Changes' => __( 'Save Changes', 'media-library-assistant' ),
			/* translators: %s: add new Image */
			'Add New Size' => sprintf( __( 'Add New %1$s', 'media-library-assistant' ), __( 'Image Size', 'media-library-assistant' ) ),
			'Slug' => __( 'Slug', 'media-library-assistant' ),
			'The slug' => __( 'The &#8220;slug&#8221; is the URL-friendly, unique key for the image size. It must be all lowercase and contain only letters, numbers, periods (.), slashes (/) and hyphens (-).', 'media-library-assistant' ),
			'Name' => __( 'Name', 'media-library-assistant' ),
			'The name' => __( 'The name is used for the size selection dropdown control and other display purposes.', 'media-library-assistant' ),
			'Width' => __( 'Width', 'media-library-assistant' ),
			'The width' => __( 'A positive integer setting the maximum image width in pixels.', 'media-library-assistant' ) . __( 'Use zero or leave blank to set no limit.', 'media-library-assistant' ),
			'Height' => __( 'Height', 'media-library-assistant' ),
			'The height' => __( 'A positive integer setting the maximum image height in pixels.', 'media-library-assistant' ) . __( 'Use zero or leave blank to set no limit.', 'media-library-assistant' ),
			'Crop' => __( 'Crop', 'media-library-assistant' ),
			'Check crop' => __( 'Check this box if you want to crop the image to an exact width and height.', 'media-library-assistant' ),
			'Horizontal' => __( 'Horizontal', 'media-library-assistant' ),
			'Left' => __( 'Left', 'media-library-assistant' ),
			'Center' => __( 'Center', 'media-library-assistant' ),
			'Right' => __( 'Right', 'media-library-assistant' ),
			'horizontal_dropdown' => self::_get_crop_location_dropdown( $page_template_array, 'mla_image_item[horizontal]', 'horizontal', '' ),
			'horizontal_dropdown_inline' => self::_get_crop_location_dropdown( $page_template_array, 'horizontal', 'horizontal', '' ),
			'The horizontal' => __( 'Select a value for the crop location.', 'media-library-assistant' ),
			'Vertical' => __( 'Vertical', 'media-library-assistant' ),
			'Top' => __( 'Top', 'media-library-assistant' ),
			'Bottom' => __( 'Bottom', 'media-library-assistant' ),
			'vertical_dropdown' => self::_get_crop_location_dropdown( $page_template_array, 'mla_image_item[vertical]', 'vertical', '' ),
			'vertical_dropdown_inline' => self::_get_crop_location_dropdown( $page_template_array, 'vertical', 'vertical', '' ),
			'The vertical' => __( 'Select a value for the crop location.', 'media-library-assistant' ),
			'Status' => __( 'Status', 'media-library-assistant' ),
			'Active' => __( 'Active', 'media-library-assistant' ),
			'Inactive' => __( 'Inactive', 'media-library-assistant' ),
			'Check inactive' => __( 'Check this box if you want to remove this entry from the list of sizes.', 'media-library-assistant' ),
			'Description' => __( 'Description', 'media-library-assistant' ),
			'The description' => __( 'The description can contain any documentation or notes you need to understand or use the item.', 'media-library-assistant' ),
			'Add Size' => __( 'Add Size', 'media-library-assistant' ),
			'colspan' => $MLAListImageTable->get_column_count(),
			'Quick Edit' => __( '<strong>Quick Edit</strong>', 'media-library-assistant' ),
			'Cancel' => __( 'Cancel', 'media-library-assistant' ),
			'Update' => __( 'Update', 'media-library-assistant' ),
			'Bulk Edit' => __( 'Bulk Edit', 'media-library-assistant' ),
			'No Change' => __( 'No Change', 'media-library-assistant' ),
			'No' => __( 'No', 'media-library-assistant' ),
			'Yes' => __( 'Yes', 'media-library-assistant' ),
		);

		foreach ( $add_form_values as $key => $value ) {
			$page_values[ $key ] = $value;
		}
		$page_content['body'] = MLAData::mla_parse_template( $page_template_array['before-table'], $page_values );

		//	 Now we can render the completed list table
		ob_start();
		$MLAListImageTable->views();
		$MLAListImageTable->display();
		$page_content['body'] .= ob_get_contents();
		ob_end_clean();

		$page_content['body'] .= MLAData::mla_parse_template( $page_template_array['after-table'], $page_values );

		return $page_content;
	} // mla_compose_image_tab

	/**
	 * Ajax handler for Post MIME Types inline editing (quick edit)
	 *
	 * Adapted from wp_ajax_inline_save in /wp-admin/includes/ajax-actions.php
	 *
	 * @since 3.25
	 *
	 * @return	void	echo HTML <tr> markup for updated row or error message, then die()
	 */
	public static function mla_inline_edit_image_action() {
		if ( isset( $_REQUEST['screen'] ) ) {
			set_current_screen( sanitize_text_field( wp_unslash( $_REQUEST['screen'] ) ) );
		}

		check_ajax_referer( MLASettings::JAVASCRIPT_INLINE_EDIT_IMAGE_SLUG, MLACore::MLA_ADMIN_NONCE_NAME );

		if ( empty( $_REQUEST['original_slug'] ) ) {
			$message = __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'No image slug found', 'media-library-assistant' );
			MLACore::mla_debug_add( $message, MLACore::MLA_DEBUG_CATEGORY_ANY );
			echo esc_html( $message );
			die();
		}

		$request = self::_sanitize_inline_image_item();
		$results = MLAImage_Size::mla_update_image_size( $request );

		if ( false === strpos( $results['message'], __( 'ERROR', 'media-library-assistant' ) ) ) {
			$new_item = (object) MLAImage_Size::mla_get_image_size( $request['slug'] );
		} else {
			$new_item = (object) MLAImage_Size::mla_get_image_size( $request['original_slug'] );
		}

		//	Create an instance of our package class and echo the new HTML
		$MLAListImageTable = new MLA_Image_List_Table();
		$MLAListImageTable->single_row( $new_item );
		die(); // this is required to return a proper result
	} // mla_inline_edit_image_action
} // MLASettings_Image

// The WP_List_Table class isn't automatically available to plugins
if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class MLA (Media Library Assistant) Image List Table implements the "Images"
 * admin settings submenu table
 *
 * Extends the core WP_List_Table class.
 *
 * @package Media Library Assistant
 * @since 3.25
 */
class MLA_Image_List_Table extends WP_List_Table {
	/**
	 * Initializes some properties from $_REQUEST variables, then
	 * calls the parent constructor to set some default configs.
	 *
	 * @since 3.25
	 *
	 * @return	void
	 */
	function __construct( ) {
		// MLA does not use this
		$this->modes = array(
			'list' => __( 'List Image' ),
		);

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'image_size', //singular name of the listed records
			'plural' => 'image_sizes', //plural name of the listed records
			'ajax' => true, //does this table support ajax?
			'screen' => 'settings_page_' . MLACoreOptions::MLA_SETTINGS_SLUG . '-image'
		) );

		// NOTE: There is one add_action call at the end of this source file.
	}

	/**
	 * Access the default list of hidden columns
	 *
	 * @since 3.25
	 *
	 * @return	array	default list of hidden columns
	 */
	private static function _default_hidden_columns( ) {
		return MLAImage_Size::$default_hidden_image_size_columns;
	}

	/**
	 * Handler for filter 'get_user_option_managesettings_page_mla-settings-menu-imagecolumnshidden'
	 *
	 * Required because the screen.php get_hidden_columns function only uses
	 * the get_user_option result. Set when the file is loaded because the object
	 * is not created in time for the call from screen.php.
	 *
	 * @since 3.25
	 *
	 * @param	mixed	false or array with current list of hidden columns, if any
	 * @param	string	'managesettings_page_mla-settings-menu-imagecolumnshidden'
	 * @param	object	WP_User object, if logged in
	 *
	 * @return	array	updated list of hidden columns
	 */
	public static function mla_manage_hidden_columns_filter( $result, $option, $user_data ) {
		if ( false !== $result ) {
			return $result;
		}

		return self::_default_hidden_columns();
	}

	/**
	 * Handler for filter 'manage_settings_page_mla-settings-menu_columns'
	 *
	 * This required filter dictates the table's columns and titles. Set when the
	 * file is loaded because the list_table object isn't created in time
	 * to affect the "screen options" setup.
	 *
	 * @since 3.25
	 *
	 * @return	array	list of table columns
	 */
	public static function mla_manage_columns_filter( ) {
		return MLAImage_Size::$default_image_size_columns;
	}

	/**
	 * Called in the admin_init action because the list_table object isn't
	 * created in time to affect the "screen options" setup.
	 *
	 * @since 3.25
	 *
	 * @return	void
	 */
	public static function mla_admin_init( ) {
		if ( isset( $_REQUEST['mla_tab'] ) && $_REQUEST['mla_tab'] === 'image' ) {
			add_filter( 'get_user_option_managesettings_page_' . MLACoreOptions::MLA_SETTINGS_SLUG . '-imagecolumnshidden', 'MLA_Image_List_Table::mla_manage_hidden_columns_filter', 10, 3 );
			add_filter( 'manage_settings_page_' . MLACoreOptions::MLA_SETTINGS_SLUG . '-image_columns', 'MLA_Image_List_Table::mla_manage_columns_filter', 10, 0 );
		}
	}

	/**
	 * Checks the current user's permissions
	 *
	 * @since 3.25
	 *
	 * @return bool
	 */
	public function ajax_user_can() {
		return current_user_can('manage_options');
	}

	/**
	 * Get the name of the default primary column.
	 *
	 * @since 3.25
	 * @access protected
	 *
	 * @return string Name of the default primary column
	 */
	protected function get_default_primary_column_name() {
		return 'slug';
	}

	/**
	 * Generate and display row actions links.
	 *
	 * @since 3.25
	 * @access protected
	 *
	 * @param object $item        Attachment being acted upon.
	 * @param string $column_name Current column name.
	 * @param string $primary     Primary column name.
	 * @return string Row actions output for media attachments.
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( $primary === $column_name ) {
			$actions = $this->row_actions( $this->_build_rollover_actions( $item, $column_name ) );
			$actions .= $this->_build_inline_data( $item );
			return $actions;
		}

		return '';
	}

	/**
	 * Supply a column value if no column-specific function has been defined
	 *
	 * Called when the parent class can't find a method specifically built for a
	 * given column. All columns should have a specific method, so this function
	 * returns a troubleshooting message.
	 *
	 * @since 3.25
	 *
	 * @param	array	A singular item (one full row's worth of data)
	 * @param	array	The name/slug of the column to be processed
	 * @return	string	Text or HTML to be placed inside the column
	 */
	function column_default( $item, $column_name ) {
		//Show the whole array for troubleshooting purposes
		/* translators: 1: column_name 2: column_values */
		return sprintf( __( 'column_default: %1$s, %2$s', 'media-library-assistant' ), $column_name, print_r( $item, true ) );
	}

	/**
	 * Displays checkboxes for using bulk actions. The 'cb' column
	 * is given special treatment when columns are processed.
	 *
	 * @since 3.25
	 * 
	 * @param	object	An MLA image_size object
	 * @return	string	HTML markup to be placed inside the column
	 */
	function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="cb_mla_item_ID[]" value="%1$s" />',
		/*%1$s*/ $item->post_ID
		);
	}

	/**
	 * Add rollover actions to a table column
	 *
	 * @since 3.25
	 * 
	 * @param	object	An MLA image_size object
	 * @param	string	Current column name
	 *
	 * @return	array	Names and URLs of row-level actions
	 */
	private function _build_rollover_actions( $item, $column ) {
		$actions = array();

		// Compose view arguments
		$view_args = array_merge( array(
			'page' => MLACoreOptions::MLA_SETTINGS_SLUG . '-image',
			'mla_tab' => 'image',
			'mla_item_slug' => urlencode( $item->slug )
		), MLA_Image_List_Table::mla_submenu_arguments() );

		if ( isset( $_REQUEST['paged'] ) ) {
			$view_args['paged'] = absint( $_REQUEST['paged'] );
		}

		$actions['edit'] = '<a href="' . add_query_arg( $view_args, MLACore::mla_nonce_url( '?mla_admin_action=' . MLACore::MLA_ADMIN_SINGLE_EDIT_DISPLAY, MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME ) ) . '" title="' . __( 'Edit this item', 'media-library-assistant' ) . '">' . __( 'Edit', 'media-library-assistant' ) . '</a>';

		$actions['inline hide-if-no-js'] = '<a class="editinline" href="#" title="' . __( 'Edit this item inline', 'media-library-assistant' ) . '">' . __( 'Quick Edit', 'media-library-assistant' ) . '</a>';

		if ( 'custom' === $item->source ) {
			if ( isset( $item->original_settings ) ) {
				$actions['delete'] = '<a class="delete-tag"' . ' href="' . add_query_arg( $view_args, MLACore::mla_nonce_url( '?mla_admin_action=' . MLACore::MLA_ADMIN_SINGLE_DELETE, MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME ) ) . '" title="' . __( 'Revert to standard item', 'media-library-assistant' ) . '">' . __( 'Revert to Standard', 'media-library-assistant' ) . '</a>';
			} else {
				$actions['delete'] = '<a class="delete-tag"' . ' href="' . add_query_arg( $view_args, MLACore::mla_nonce_url( '?mla_admin_action=' . MLACore::MLA_ADMIN_SINGLE_DELETE, MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME ) ) . '" title="' . __( 'Delete this item Permanently', 'media-library-assistant' ) . '">' . __( 'Delete Permanently', 'media-library-assistant' ) . '</a>';
			}
		}

		return $actions;
	} // _build_rollover_actions

	/**
	 * Add hidden fields with the data for use in the inline editor
	 *
	 * @since 3.25
	 * 
	 * @param	object	An MLA image_size object
	 *
	 * @return	string	HTML <div> with row data
	 */
	private function _build_inline_data( $item ) {
		// Supply default values for dropdown controls
		$horizontal = empty( $item->horizontal ) ? 'center' : $item->horizontal;
		$vertical = empty( $item->vertical ) ? 'center' : $item->vertical;

		$inline_data = "\r\n" . '<div class="hidden" id="inline_' . $item->post_ID . "\">\r\n";
		$inline_data .= '	<div class="original_slug">' . esc_attr( $item->slug ) . "</div>\r\n";
		$inline_data .= '	<div class="slug">' . esc_attr( $item->slug ) . "</div>\r\n";
		$inline_data .= '	<div class="name">' . esc_attr( $item->name ) . "</div>\r\n";
		$inline_data .= '	<div class="width">' . esc_attr( $item->width ) . "</div>\r\n";
		$inline_data .= '	<div class="height">' . esc_attr( $item->height ) . "</div>\r\n";
		$inline_data .= '	<div class="crop">' . esc_attr( $item->crop ) . "</div>\r\n";
		$inline_data .= '	<div class="horizontal">' . esc_attr( $horizontal ) . "</div>\r\n";
		$inline_data .= '	<div class="vertical">' . esc_attr( $vertical ) . "</div>\r\n";
		$inline_data .= '	<div class="disabled">' . esc_attr( $item->disabled ) . "</div>\r\n";
		$inline_data .= '	<div class="description">' . esc_attr( $item->description ) . "</div>\r\n";
		$inline_data .= '	<div class="source">' . esc_attr( $item->source ) . "</div>\r\n";
		$inline_data .= '	<div class="post_ID">' . esc_attr( $item->post_ID ) . "</div>\r\n";
		$inline_data .= "</div>\r\n";
		return $inline_data;
	}

	/**
	 * Supply the content for a custom column
	 *
	 * @since 3.25
	 * 
	 * @param	object	An MLA image_size object
	 * @return	string	HTML markup to be placed inside the column
	 */
	function column_slug( $item ) {
		if ( MLATest::$wp_4dot3_plus ) {
			return esc_attr( $item->slug );
		}

		$row_actions = self::_build_rollover_actions( $item, 'slug' );
		$slug = esc_attr( $item->slug );
		return sprintf( '%1$s<br>%2$s%3$s', /*%1$s*/ $slug, /*%2$s*/ $this->row_actions( $row_actions ), /*%3$s*/ $this->_build_inline_data( $item ) );
	}

	/**
	 * Supply the content for a custom column
	 *
	 * @since 3.25
	 * 
	 * @param	object	An MLA image_size object
	 * @return	string	HTML markup to be placed inside the column
	 */
	function column_name( $item ) {
		return esc_attr( $item->name );
	}

	/**
	 * Supply the content for a custom column
	 *
	 * @since 3.25
	 * 
	 * @param	object	An MLA image_size object
	 * @return	string	HTML markup to be placed inside the column
	 */
	function column_width( $item ) {
		return esc_attr( $item->width );
	}

	/**
	 * Supply the content for a custom column
	 *
	 * @since 3.25
	 * 
	 * @param	object	An MLA image_size object
	 * @return	string	HTML markup to be placed inside the column
	 */
	function column_height( $item ) {
		return esc_attr( $item->height );
	}

	/**
	 * Supply the content for a custom column
	 *
	 * @since 3.25
	 * 
	 * @param	object	An MLA image_size object
	 * @return	string	HTML markup to be placed inside the column
	 */
	function column_crop( $item ) {
		if ( $item->crop ) {
			return __( 'Yes', 'media-library-assistant' );
		} else {
			return __( 'No', 'media-library-assistant' );
		}
	}

	/**
	 * Supply the content for a custom column
	 *
	 * @since 3.25
	 * 
	 * @param	object	An MLA image_size object
	 * @return	string	HTML markup to be placed inside the column
	 */
	function column_horizontal( $item ) {
		return esc_attr( $item->horizontal );
	}

	/**
	 * Supply the content for a custom column
	 *
	 * @since 3.25
	 * 
	 * @param	object	An MLA image_size object
	 * @return	string	HTML markup to be placed inside the column
	 */
	function column_vertical( $item ) {
		return esc_attr( $item->vertical );
	}

	/**
	 * Supply the content for a custom column
	 *
	 * @since 3.25
	 * 
	 * @param	object	An MLA image_size object
	 * @return	string	HTML markup to be placed inside the column
	 */
	function column_status( $item ) {
		if ( $item->disabled ) {
			return __( 'Inactive', 'media-library-assistant' );
		} else {
			return __( 'Active', 'media-library-assistant' );
		}
	}

	/**
	 * Supply the content for a custom column
	 *
	 * @since 3.25
	 * 
	 * @param	object	An MLA image_size object
	 * @return	string	HTML markup to be placed inside the column
	 */
	function column_description( $item ) {
		return esc_attr( $item->description );
	}

	/**
	 * Supply the content for a custom column
	 *
	 * @since 3.25
	 * 
	 * @param	object	An MLA image_size object
	 * @return	string	HTML markup to be placed inside the column
	 */
	function column_source( $item ) {
		return esc_attr( $item->source );
	}

	/**
	 * Display the pagination, adding view, search and filter arguments
	 *
	 * @since 3.25
	 * 
	 * @param string	'top' | 'bottom'
	 */
	function pagination( $which ) {
		$save_uri = $_SERVER['REQUEST_URI']; // phpcs:ignore
		$_SERVER['REQUEST_URI'] = add_query_arg( MLA_Image_List_Table::mla_submenu_arguments(), $save_uri );
		parent::pagination( $which );
		$_SERVER['REQUEST_URI'] = $save_uri;
	}

	/**
	 * This method dictates the table's columns and titles
	 *
	 * @since 3.25
	 * 
	 * @return	array	Column information: 'slugs'=>'Visible Titles'
	 */
	function get_columns( ) {
		return $columns = MLA_Image_List_Table::mla_manage_columns_filter();
	}

	/**
	 * Returns the list of currently hidden columns from a user option or
	 * from default values if the option is not set
	 *
	 * @since 3.25
	 * 
	 * @return	array	Column information,e.g., array(0 => 'ID_parent, 1 => 'title_name')
	 */
	function get_hidden_columns( ) {
		$columns = get_user_option( 'managesettings_page_' . MLACoreOptions::MLA_SETTINGS_SLUG . '-imagecolumnshidden' );

		if ( is_array( $columns ) ) {
			return $columns;
		}

		return self::_default_hidden_columns();
	}

	/**
	 * Returns an array where the  key is the column that needs to be sortable
	 * and the value is db column to sort by.
	 *
	 * @since 3.25
	 * 
	 * @return	array	Sortable column information,e.g.,
	 * 					'slugs'=>array('data_values',boolean)
	 */
	function get_sortable_columns( ) {
		return MLAImage_Size::$default_sortable_image_size_columns;
	}

	/**
	 * Process $_REQUEST, building $submenu_arguments
	 *
	 * @since 3.25
	 *
	 * @param boolean $include_filters Optional. Include the "click filter" values in the results. Default true.
	 * @return array non-empty view, search, filter and sort arguments
	 */
	public static function mla_submenu_arguments( $include_filters = true ) {
		static $submenu_arguments = NULL, $has_filters = NULL;

		if ( is_array( $submenu_arguments ) && ( $has_filters === $include_filters ) ) {
			return $submenu_arguments;
		}

		$submenu_arguments = array();
		$has_filters = $include_filters;

		// Search box arguments
		if ( !empty( $_REQUEST['s'] ) ) {
			$submenu_arguments['s'] = urlencode( wp_kses( wp_unslash( $_REQUEST['s'] ), 'post' ) );
		}

		// View arguments - see also MLAImage_Size::mla_tabulate_items
		$field = sanitize_text_field( isset( $_REQUEST['mla_image_view'] ) ? wp_unslash( $_REQUEST['mla_image_view'] ) : 'all' );
		if ( in_array( $field, array( 'all', 'core', 'other', 'custom' ) ) ) {
			$submenu_arguments['mla_image_view'] = $field;
		}

		// Filter arguments (from table header)
		$field = strtolower( sanitize_text_field( isset( $_REQUEST['mla_image_status'] ) ? wp_unslash( $_REQUEST['mla_image_status'] ) : 'any' ) );
		if ( 'any' !== $field ) {
			if ( in_array( $field, array( 'active', 'inactive' ) ) ) {
				$submenu_arguments['mla_image_status'] = $field;
			}
		}

		// Sort arguments (from column header)
		if ( isset( $_REQUEST['order'] ) ) {
			$field = strtoupper( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) );
			$submenu_arguments['order'] = 'DESC' === $field ? 'DESC' : 'ASC';
		}

		$field = strtolower( sanitize_text_field( isset( $_REQUEST['orderby'] ) ? wp_unslash( $_REQUEST['orderby'] ) : '' ) );
		if ( array_key_exists( $field, MLAImage_Size::$default_sortable_image_size_columns ) ) {
			$submenu_arguments['orderby'] = $field;
		}

		return $submenu_arguments = apply_filters( 'mla_setting_table_submenu_arguments', $submenu_arguments, $include_filters, 'MLASettings_Image' );
	}

	/**
	 * Returns HTML markup for one view that can be used with this table
	 *
	 * @since 3.25
	 *
	 * @param	string	View slug
	 * @param	array	count and labels for the View
	 * @param	string	Slug for current view 
	 * 
	 * @return	string | false	HTML for link to display the view, false if count = zero
	 */
	function _get_view( $view_slug, $item, $current_view ) {
		static $base_url = NULL;

		$class = ( $view_slug === $current_view ) ? ' class="current"' : '';

		// Calculate the common values once per page load
		if ( is_null( $base_url ) ) {
			// Remember the view filters
			$base_url = 'options-general.php?page=' . MLACoreOptions::MLA_SETTINGS_SLUG . '-image&mla_tab=image';

			if ( isset( $_REQUEST['s'] ) ) {
				$base_url = add_query_arg( array( 's' => wp_kses( wp_unslash( $_REQUEST['s'] ), 'post' ) ), $base_url );
			}
		}

		$singular = sprintf('%s <span class="count">(%%s)</span>', $item['singular'] );
		$plural = sprintf('%s <span class="count">(%%s)</span>', $item['plural'] );
		$nooped_plural = _n_noop( $singular, $plural, 'media-library-assistant' );
		return "<a href='" . add_query_arg( array( 'mla_image_view' => $view_slug ), $base_url )
			. "'$class>" . sprintf( translate_nooped_plural( $nooped_plural, $item['count'], 'media-library-assistant' ), number_format_i18n( $item['count'] ) ) . '</a>';
	} // _get_view

	/**
	 * Returns an associative array listing all the views that can be used with this table.
	 * These are listed across the top of the page and managed by WordPress.
	 *
	 * @since 3.25
	 * 
	 * @return	array	View information,e.g., array ( id => link )
	 */
	function get_views( ) {
		// Find current view
		$current_view = isset( $_REQUEST['mla_image_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mla_image_view'] ) ) : 'all';

		// Generate the list of views, retaining keyword search criterion
		$s = wp_kses( isset( $_REQUEST['s'] ) ? wp_unslash( $_REQUEST['s'] ) : '', 'post' );
		$items = MLAImage_Size::mla_tabulate_items( $s );
		$view_links = array();
		foreach ( $items as $slug => $item )
			$view_links[ $slug ] = self::_get_view( $slug, $item, $current_view );

		return $view_links;
	}

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 * @since 3.25
	 * 
	 * @return	array	Contains all the bulk actions: 'slugs'=>'Visible Titles'
	 */
	function get_bulk_actions( ) {
		$actions = array();
		$view = isset( $_REQUEST['mla_image_view'] ) ? $_REQUEST['mla_image_view'] : 'all';

		$actions['edit'] = __( 'Edit', 'media-library-assistant' );
		
		if ( ( 'all' === $view ) || ( 'custom' === $view ) ) {
			$actions['delete'] = __( 'Delete Permanently', 'media-library-assistant' );
		}
		
		return $actions;
	}

	/**
	 * Get dropdown box of rule status values, i.e., Active/Inactive.
	 *
	 * @since 3.25
	 *
	 * @param string $selected Optional. Currently selected status. Default 'any'.
	 * @return string HTML markup for dropdown box.
	 */
	public static function mla_get_image_status_dropdown( $selected = 'any' ) {
		$dropdown  = '<select name="mla_image_status" class="postform" id="name">' . "\n";

		$selected_attribute = ( $selected === 'any' ) ? ' selected="selected"' : '';
		$dropdown .= "\t" . sprintf( '<option value="any"%1$s>%2$s</option>', $selected_attribute, _wp_specialchars( __( 'Any Status', 'media-library-assistant' ) ) ) . "\n";

		$selected_attribute = ( $selected === 'active' ) ? ' selected="selected"' : '';
		$dropdown .= "\t" . sprintf( '<option value="active"%1$s>%2$s</option>', $selected_attribute, _wp_specialchars( __( 'Active', 'media-library-assistant' ) ) ) . "\n";

		$selected_attribute = ( $selected === 'inactive' ) ? ' selected="selected"' : '';
		$dropdown .= "\t" . sprintf( '<option value="inactive"%1$s>%2$s</option>', $selected_attribute, _wp_specialchars( __( 'Inactive', 'media-library-assistant' ) ) ) . "\n";

		$dropdown .= '</select>';

		return $dropdown;
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * Modeled after class-wp-posts-list-table.php in wp-admin/includes.
	 *
	 * @since 3.25
	 * 
	 * @param	string	'top' or 'bottom', i.e., above or below the table rows
	 *
	 * @return	void
	 */
	function extra_tablenav( $which ) {
		// Decide which actions to show
		if ( 'top' === $which ) {
			$actions = array( 'mla_image_status', 'mla_filter' );
		} else {
			$actions = array();
		}

		if ( empty( $actions ) ) {
			return;
		}

		echo ( '<div class="alignleft actions">' );

		foreach ( $actions as $action ) {
			switch ( $action ) {
				case 'mla_image_status':
					echo self::mla_get_image_status_dropdown( isset( $_REQUEST['mla_image_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mla_image_status'] ) ) : 'any' ); // phpcs:ignore
					break;
				case 'mla_filter':
					submit_button( __( 'Filter', 'media-library-assistant' ), 'secondary', 'mla_filter', false, array( 'id' => 'template-query-submit' ) );
					break;
				default:
					// ignore anything else
			}
		}

		echo ( '</div>' );
	}

	/**
	 * Prepares the list of items for displaying
	 *
	 * This is where you prepare your data for display. This method will usually
	 * be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args().
	 *
	 * @since 3.25
	 *
	 * @return	void
	 */
	function prepare_items( ) {
		$this->_column_headers = array(
			$this->get_columns(),
			$this->get_hidden_columns(),
			$this->get_sortable_columns() 
		);

		// REQUIRED for pagination.
		$total_items = MLAImage_Size::mla_count_image_size_items( $_REQUEST );
		$user = get_current_user_id();
		$screen = get_current_screen();
		$option = $screen->get_option( 'per_page', 'option' );
		if ( is_string( $option ) ) {
			$per_page = get_user_meta( $user, $option, true );
		} else {
			$per_page = 10;
		}

		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}

		// REQUIRED. We also have to register our pagination options & calculations.
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page' => $per_page, 
			'total_pages' => ceil( $total_items / $per_page )
		) );

		$current_page = $this->get_pagenum();

		/*
		 * REQUIRED. Assign sorted and paginated data to the items property, where 
		 * it can be used by the rest of the class.
		 */
		$this->items = MLAImage_Size::mla_query_image_size_items( $_REQUEST, ( ( $current_page - 1 ) * $per_page ), $per_page );
	}

	/**
	 * Generates (echoes) content for a single row of the table
	 *
	 * @since 3.25
	 *
	 * @param object the current item
	 *
	 * @return void Echoes the row HTML
	 */
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class === '' ? ' class="alternate"' : '' );

		echo '<tr id="image-' . $item->post_ID . '"' . $row_class . '>'; // phpcs:ignore
		echo parent::single_row_columns( $item ); // phpcs:ignore
		echo '</tr>';
	}
} // class MLA_Image_List_Table

/*
 * Actions are added here, when the source file is loaded, because the MLA_Image_List_Table
 * object is created too late to be useful.
 */

add_action( 'admin_enqueue_scripts', 'MLASettings_Image::mla_admin_enqueue_scripts' );
add_action( 'admin_init', 'MLA_Image_List_Table::mla_admin_init' );
?>