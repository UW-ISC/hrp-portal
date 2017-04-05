<?php
/**
 * Manages the Settings/Media Library Assistant IPTC EXIF tab
 *
 * @package Media Library Assistant
 * @since 2.40
 */

/**
 * Class MLA (Media Library Assistant) Settings IPTC EXIF implements the
 * Settings/Media Library Assistant IPTC EXIF tab
 *
 * @package Media Library Assistant
 * @since 2.40
 */
class MLASettings_IPTCEXIF {
	/**
	 * Load the tab's Javascript files
	 *
	 * @since 2.40
	 *
	 * @param string $page_hook Name of the page being loaded
	 */
	public static function mla_admin_enqueue_scripts( $page_hook ) {
		global $wpdb;

		// Without a tab value that matches ours, there's nothing to do
		if ( empty( $_REQUEST['mla_tab'] ) || 'iptc_exif' !== $_REQUEST['mla_tab'] ) {
			return;
		}

		/*
		 * Initialize script variables
		 */
		$script_variables = array(
			'error' => __( 'Error while making the changes.', 'media-library-assistant' ),
			'ntdeltitle' => __( 'Remove From Bulk Edit', 'media-library-assistant' ),
			'notitle' => '(' . __( 'no slug', 'media-library-assistant' ) . ')',
			'comma' => _x( ',', 'tag_delimiter', 'media-library-assistant' ),
			'useSpinnerClass' => false,
			'ajax_nonce' => wp_create_nonce( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME ),
			'bulkChunkSize' => MLACore::mla_get_option( MLACoreOptions::MLA_BULK_CHUNK_SIZE ),
			'bulkWaiting' => __( 'Waiting', 'media-library-assistant' ),
			'bulkRunning' => __( 'Running', 'media-library-assistant' ),
			'bulkComplete' => __( 'Complete', 'media-library-assistant' ),
			'bulkUnchanged' => __( 'Unchanged', 'media-library-assistant' ),
			'bulkSuccess' => __( 'Succeeded', 'media-library-assistant' ),
			'bulkFailure' => __( 'Failed', 'media-library-assistant' ),
			'bulkSkip' => __( 'Skipped', 'media-library-assistant' ),
			'bulkRedone' => __( 'Reprocessed', 'media-library-assistant' ),
			'bulkPaused' => __( 'PAUSED', 'media-library-assistant' ),

			'page' => 'mla-settings-menu-iptc_exif',
			'mla_tab' => 'iptc_exif',
			'screen' => 'settings_page_mla-settings-menu-iptc_exif',
			'ajax_action' => MLASettings::JAVASCRIPT_INLINE_MAPPING_IPTC_EXIF_SLUG,
			'fieldsId' => '#mla-display-settings-iptc-exif-tab',
			'totalItems' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE `post_type` = 'attachment' AND ( `post_mime_type` LIKE 'image/%' OR `post_mime_type` LIKE 'application/%pdf%' )" )
		);

		if ( version_compare( get_bloginfo( 'version' ), '4.2', '>=' ) ) {
			$script_variables['useSpinnerClass'] = true;
		}

		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( MLASettings::JAVASCRIPT_INLINE_MAPPING_IPTC_EXIF_SLUG,
			MLA_PLUGIN_URL . "js/mla-inline-mapping-scripts{$suffix}.js", 
			array( 'jquery' ), MLACore::CURRENT_MLA_VERSION, false );

		wp_localize_script( MLASettings::JAVASCRIPT_INLINE_MAPPING_IPTC_EXIF_SLUG,
			MLASettings::JAVASCRIPT_INLINE_MAPPING_OBJECT, $script_variables );
	}

	/**
	 * Save IPTC/EXIF custom field settings to the options table
 	 *
	 * @since 1.30
	 *
	 * @param	array	specific iptc_exif_custom_mapping values 
	 *
	 * @return	array	Message(s) reflecting the results of the operation
	 */
	private static function _save_iptc_exif_custom_settings( $new_values ) {
		return array(
			'message' => MLAOptions::mla_iptc_exif_option_handler( 'update', 'iptc_exif_custom_mapping', MLACoreOptions::$mla_option_definitions['iptc_exif_mapping'], $new_values ),
			'body' => '' 
		);
	} // _save_iptc_exif_custom_settings

	/**
	 * Save IPTC/EXIF settings to the options table
 	 *
	 * @since 1.00
	 *
	 * @uses $_REQUEST
	 *
	 * @return	array	Message(s) reflecting the results of the operation
	 */
	private static function _save_iptc_exif_settings( ) {
		$message_list = '';
		$option_messages = '';

		/*
		 * Start with any page-level options
		 */
		foreach ( MLACoreOptions::$mla_option_definitions as $key => $value ) {
			if ( 'iptc_exif' == $value['tab'] ) {
				$option_messages .= MLASettings::mla_update_option_row( $key, $value );
			}
		}

		/*
		 * Uncomment this for debugging.
		 */
		//$message_list = $option_messages . '<br>';

		/*
		 * Add mapping options
		 */
		$new_values = ( isset( $_REQUEST['iptc_exif_mapping'] ) ) ? $_REQUEST['iptc_exif_mapping'] : array( 'standard' => array(), 'taxonomy' => array(), 'custom' => array() );

		return array(
			'message' => $message_list . MLAOptions::mla_iptc_exif_option_handler( 'update', 'iptc_exif_mapping', MLACoreOptions::$mla_option_definitions['iptc_exif_mapping'], $new_values ),
			'body' => '' 
		);
	} // _save_iptc_exif_settings

	/**
	 * Process IPTC/EXIF standard field settings against all image attachments
	 * without saving the settings to the mla_option
 	 *
	 * @since 1.00
	 *
	 * @uses $_REQUEST
	 *
	 * @param	integer			offset for chunk mapping 
	 * @param	integer			length for chunk mapping
	 *
	 * @return	array	Message(s) reflecting the results of the operation
	 */
	private static function _process_iptc_exif_standard( $offset = 0, $length = 0 ) {
		if ( ! isset( $_REQUEST['iptc_exif_mapping']['standard'] ) ) {
			return array(
				/* translators: 1: ERROR tag 2: field type */
				'message' => sprintf( __( '%1$s: No %2$s settings to process.', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), __( 'Standard field', 'media-library-assistant' ) ),
				'body' => '',
				'processed' => 0,
				'unchanged' => 0,
				'success' => 0,
			);
		}

		$examine_count = 0;
		$update_count = 0;
		$query = array( 'orderby' => 'none', 'post_parent' => 'all', 'post_mime_type' => 'image,application/*pdf*' );

		if ( $length > 0 ) {
			$query['numberposts'] = $length;
			$query['offset'] = $offset;
		}

		do_action( 'mla_begin_mapping', 'iptc_exif_standard', NULL );
		$posts = MLAShortcodes::mla_get_shortcode_attachments( 0, $query );

		if ( is_string( $posts ) ) {
			return array(
				'message' => $posts,
				'body' => '' 
			);
		}

		foreach ( $posts as $key => $post ) {
			$updates = MLAOptions::mla_evaluate_iptc_exif_mapping( $post, 'iptc_exif_standard_mapping', $_REQUEST['iptc_exif_mapping'] );

			$examine_count += 1;
			if ( ! empty( $updates ) ) {
				$results = MLAData::mla_update_single_item( $post->ID, $updates );
				if ( stripos( $results['message'], __( 'updated.', 'media-library-assistant' ) ) ) {
					$update_count += 1;
				}
			}
		} // foreach post
		do_action( 'mla_end_mapping' );

		if ( $update_count ) {
			/* translators: 1: field type 2: examined count 3: updated count */
			$message = sprintf( __( '%1$s mapping completed; %2$d attachment(s) examined, %3$d updated.' ), 'IPTC/EXIF ' . __( 'Standard field', 'media-library-assistant' ), $examine_count, $update_count ) . "\r\n";
		} else {
			/* translators: 1: field type 2: examined count */
			$message = sprintf( __( '%1$s mapping completed; %2$d attachment(s) examined, no changes detected.' ), 'IPTC/EXIF ' . __( 'Standard field', 'media-library-assistant' ), $examine_count ) . "\r\n";
		}

		return array(
			'message' => $message,
			'body' => '',
			'processed' => $examine_count,
			'unchanged' => $examine_count - $update_count,
			'success' => $update_count
		);
	} // _process_iptc_exif_standard

	/**
	 * Process IPTC/EXIF taxonomy term settings against all image attachments
	 * without saving the settings to the mla_option
 	 *
	 * @since 1.00
	 *
	 * @uses $_REQUEST
	 *
	 * @param	integer			offset for chunk mapping 
	 * @param	integer			length for chunk mapping
	 *
	 * @return	array	Message(s) reflecting the results of the operation
	 */
	private static function _process_iptc_exif_taxonomy( $offset = 0, $length = 0 ) {
		if ( ! isset( $_REQUEST['iptc_exif_mapping']['taxonomy'] ) ) {
			return array(
				/* translators: 1: ERROR tag 2: field type */
				'message' => sprintf( __( '%1$s: No %2$s settings to process.', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), __( 'Taxonomy term', 'media-library-assistant' ) ),
				'body' => '',
				'processed' => 0,
				'unchanged' => 0,
				'success' => 0,
			);
		}

		$examine_count = 0;
		$update_count = 0;
		$query = array( 'orderby' => 'none', 'post_parent' => 'all', 'post_mime_type' => 'image,application/*pdf*' );

		if ( $length > 0 ) {
			$query['numberposts'] = $length;
			$query['offset'] = $offset;
		}

		do_action( 'mla_begin_mapping', 'iptc_exif_taxonomy', NULL );
		$posts = MLAShortcodes::mla_get_shortcode_attachments( 0, $query );

		if ( is_string( $posts ) ) {
			return array(
				'message' => $posts,
				'body' => '' 
			);
		}

		foreach ( $posts as $key => $post ) {

			$updates = MLAOptions::mla_evaluate_iptc_exif_mapping( $post, 'iptc_exif_taxonomy_mapping', $_REQUEST['iptc_exif_mapping'] );

			$examine_count += 1;
			if ( ! empty( $updates ) ) {
				$results = MLAData::mla_update_single_item( $post->ID, array(), $updates['taxonomy_updates']['inputs'], $updates['taxonomy_updates']['actions'] );
				if ( stripos( $results['message'], __( 'updated.', 'media-library-assistant' ) ) ) {
					$update_count += 1;
				}
			}
		} // foreach post
		do_action( 'mla_end_mapping' );

		if ( $update_count ) {
			/* translators: 1: field type 2: examined count 3: updated count */
			$message = sprintf( __( '%1$s mapping completed; %2$d attachment(s) examined, %3$d updated.' ), 'IPTC/EXIF ' . __( 'Taxonomy term', 'media-library-assistant' ), $examine_count, $update_count ) . "\r\n";
		} else {
			/* translators: 1: field type 2: examined count */
			$message = sprintf( __( '%1$s mapping completed; %2$d attachment(s) examined, no changes detected.' ), 'IPTC/EXIF ' . __( 'Taxonomy term', 'media-library-assistant' ), $examine_count ) . "\r\n";
		}

		return array(
			'message' => $message,
			'body' => '',
			'processed' => $examine_count,
			'unchanged' => $examine_count - $update_count,
			'success' => $update_count
		);
	} // _process_iptc_exif_taxonomy

	/**
	 * Process IPTC/EXIF custom field settings against all image attachments
	 * without saving the settings to the mla_option
 	 *
	 * @since 1.00
	 *
	 * @uses $_REQUEST if passed a NULL parameter
	 *
	 * @param	array | NULL	specific iptc_exif_custom_mapping values 
	 * @param	integer			offset for chunk mapping 
	 * @param	integer			length for chunk mapping
	 *
	 * @return	array	Message(s) reflecting the results of the operation
	 */
	private static function _process_iptc_exif_custom( $settings = NULL, $offset = 0, $length = 0 ) {
		if ( NULL == $settings ) {
			$source = 'iptc_exif_custom';
			$settings = ( isset( $_REQUEST['iptc_exif_mapping'] ) ) ? stripslashes_deep( $_REQUEST['iptc_exif_mapping'] ) : array();
			if ( isset( $settings['custom'][ MLACoreOptions::MLA_NEW_CUSTOM_FIELD ] ) ) {
				unset( $settings['custom'][ MLACoreOptions::MLA_NEW_CUSTOM_FIELD ] );
			}
			if ( isset( $settings['custom'][ MLACoreOptions::MLA_NEW_CUSTOM_RULE ] ) ) {
				unset( $settings['custom'][ MLACoreOptions::MLA_NEW_CUSTOM_RULE ] );
			}
		} else {
			$source = 'iptc_exif_custom_rule';
			$settings = stripslashes_deep( $settings );
		}

		if ( empty( $settings['custom'] ) ) {
			return array(
				/* translators: 1: ERROR tag 2: field type */
				'message' => sprintf( __( '%1$s: No %2$s settings to process.', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), __( 'Custom field', 'media-library-assistant' ) ),
				'body' => '',
				'processed' => 0,
				'unchanged' => 0,
				'success' => 0,
			);
		}

		$examine_count = 0;
		$update_count = 0;
		$query = array( 'orderby' => 'none', 'post_parent' => 'all', 'post_mime_type' => 'image,application/*pdf*' );

		if ( $length > 0 ) {
			$query['numberposts'] = $length;
			$query['offset'] = $offset;
		}

		do_action( 'mla_begin_mapping', $source, NULL );
		$posts = MLAShortcodes::mla_get_shortcode_attachments( 0, $query );

		if ( is_string( $posts ) ) {
			return array(
				'message' => $posts,
				'body' => '' 
			);
		}

		foreach ( $posts as $key => $post ) {
			$updates = MLAOptions::mla_evaluate_iptc_exif_mapping( $post, 'iptc_exif_custom_mapping', $settings );

			$examine_count += 1;
			if ( ! empty( $updates ) ) {
				$results = MLAData::mla_update_single_item( $post->ID, $updates );
				if ( stripos( $results['message'], __( 'updated.', 'media-library-assistant' ) ) ) {
					$update_count += 1;
				}
			}
		} // foreach post
		do_action( 'mla_end_mapping' );

		if ( $update_count ) {
			/* translators: 1: field type 2: examined count 3: updated count */
			$message = sprintf( __( '%1$s mapping completed; %2$d attachment(s) examined, %3$d updated.' ), 'IPTC/EXIF ' . __( 'Custom field', 'media-library-assistant' ), $examine_count, $update_count ) . "\r\n";
		} else {
			/* translators: 1: field type 2: examined count */
			$message = sprintf( __( '%1$s mapping completed; %2$d attachment(s) examined, no changes detected.' ), 'IPTC/EXIF ' . __( 'Custom field', 'media-library-assistant' ), $examine_count ) . "\r\n";
		}

		return array(
			'message' => $message,
			'body' => '',
			'processed' => $examine_count,
			'unchanged' => $examine_count - $update_count,
			'success' => $update_count
		);
	} // _process_iptc_exif_custom

	/**
	 * Compose the IPTC/EXIF tab content for the Settings subpage
	 *
	 * @since 1.00
	 * @uses $page_template_array contains tab content template(s)
 	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	public static function mla_compose_iptc_exif_tab( ) {
		/*
		 * Initialize page messages and content.
		 * Check for submit buttons to change or reset settings.
		 */
		$page_content = array(
			'message' => '',
			'body' => '' 
		);

		if ( isset( $_REQUEST['iptc_exif_mapping'] ) && is_array( $_REQUEST['iptc_exif_mapping'] ) ) {
			check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );

			if ( !empty( $_REQUEST['iptc-exif-options-save'] ) ) {
				$page_content = self::_save_iptc_exif_settings( );
			} elseif ( !empty( $_REQUEST['iptc-exif-options-process-standard'] ) ) {
				$page_content = self::_process_iptc_exif_standard( );
			} elseif ( !empty( $_REQUEST['iptc-exif-options-process-taxonomy'] ) ) {
				$page_content = self::_process_iptc_exif_taxonomy( );
			} elseif ( !empty( $_REQUEST['iptc-exif-options-process-custom'] ) ) {
				$page_content = self::_process_iptc_exif_custom( );
			} else {
				/*
				 * Check for single-rule action buttons
				 */
				foreach ( $_REQUEST['iptc_exif_mapping']['custom'] as $key => $value ) {
					$value = stripslashes_deep( $value );

					if ( isset( $value['action'] ) ) {
						$settings = array( 'custom' => array( $key => $value ) );
						foreach ( $value['action'] as $action => $label ) {
							switch( $action ) {
								case 'delete_field':
									$delete_result = MLASettings::mla_delete_custom_field( $value );
								case 'delete_rule':
								case 'add_rule':
								case 'add_field':
								case 'update_rule':
									$page_content = self::_save_iptc_exif_custom_settings( $settings );
									if ( isset( $delete_result ) ) {
										$page_content['message'] = $delete_result . $page_content['message'];
									}
									break;
								case 'map_now':
									$page_content = self::_process_iptc_exif_custom( $settings );
									break;
								case 'add_rule_map':
								case 'add_field_map':
									$page_content = self::_save_iptc_exif_custom_settings( $settings );
									if ( false === strpos( $page_content['message'], __( 'ERROR', 'media-library-assistant' ) ) ) {
										$current_values = MLACore::mla_get_option( 'iptc_exif_mapping' );
										$settings = array( 'custom' => array( $value['name'] => $current_values['custom'][$value['name']] ) );
										$map_content = self::_process_iptc_exif_custom( $settings );
										$page_content['message'] .= '<br>&nbsp;<br>' . $map_content['message'];
									}
									break;
								default:
									// ignore everything else
							} //switch action
						} // foreach action
					} /// isset action
				} // foreach rule
			}

			if ( !empty( $page_content['body'] ) ) {
				return $page_content;
			}
		}

		$page_template_array = MLACore::mla_load_template( 'admin-display-settings-iptc-exif-tab.tpl' );
		if ( ! is_array( $page_template_array ) ) {
			/* translators: 1: ERROR tag 2: function name 3: non-array value */
			$page_content['message'] = sprintf( _x( '%1$s: %2$s non-array "%3$s"', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), 'MLASettings_IPTCEXIF::mla_compose_iptc_exif_tab', var_export( $page_template_array, true ) );
			return $page_content;
		}

		$page_values = array(
			'Mapping Progress' => __( 'IPTC &amp; EXIF Mapping Progress', 'media-library-assistant' ),
			'DO NOT' => __( 'DO NOT DO THE FOLLOWING (they will cause mapping to fail)', 'media-library-assistant' ),
			'DO NOT Close' => __( 'Close the window', 'media-library-assistant' ),
			'DO NOT Reload' => __( 'Reload the page', 'media-library-assistant' ),
			'DO NOT Click' => __( 'Click the browser&rsquo;s Stop, Back or forward buttons', 'media-library-assistant' ),
			'Progress' => __( 'Progress', 'media-library-assistant' ),
			'Pause' => __( 'Pause', 'media-library-assistant' ),
			'Cancel' => __( 'Cancel', 'media-library-assistant' ),
			'Resume' => __( 'Resume', 'media-library-assistant' ),
			'Close' => __( 'Close', 'media-library-assistant' ),
			'Refresh' => __( 'Refresh', 'media-library-assistant' ),
			'refresh_href' => '?page=mla-settings-menu-iptc_exif&mla_tab=iptc_exif',
		);

		$progress_div = MLAData::mla_parse_template( $page_template_array['mla-progress-div'], $page_values );

		$page_values = array(
			'mla-progress-div' => $progress_div,
			'IPTX/EXIF Options' => __( 'IPTC &amp; EXIF Processing Options', 'media-library-assistant' ),
			'In this tab' => __( 'In this tab you can define the rules for mapping IPTC (International Press Telecommunications Council) and EXIF (EXchangeable Image File) metadata to WordPress standard attachment fields, taxonomy terms and custom fields. <strong>NOTE:</strong> settings changes will not be made permanent until you click "Save Changes" at the bottom of this page.', 'media-library-assistant' ),
			/* translators: 1: Documentation hyperlink */
			'You can find' => sprintf( __( 'You can find more information about using the controls in this tab to define mapping rules and apply them in the %1$s section of the Documentation.', 'media-library-assistant' ), '<a href="[+settingsURL+]?page=mla-settings-menu-documentation&amp;mla_tab=documentation#mla_iptc_exif_mapping" title="' . __( 'IPTC/EXIF Options documentation', 'media-library-assistant' ) . '">' . __( 'IPTC &amp; EXIF Processing Options', 'media-library-assistant' ) . '</a>' ),
			'settingsURL' => admin_url('options-general.php'),
			'form_url' => admin_url( 'options-general.php' ) . '?page=mla-settings-menu-iptc_exif&mla_tab=iptc_exif',
			'options_list' => '',
			'Standard field mapping' => __( 'Standard field mapping', 'media-library-assistant' ),
			'Map Standard Fields' => __( 'Map All Attachments, Standard Fields Now', 'media-library-assistant' ),
			'standard_options_list' => '',
			'Taxonomy term mapping' => __( 'Taxonomy term mapping', 'media-library-assistant' ),
			'Map Taxonomy Terms' => __( 'Map All Attachments, Taxonomy Terms Now', 'media-library-assistant' ),
			'taxonomy_options_list' => '',
			'Custom field mapping' => __( 'Custom field mapping', 'media-library-assistant' ),
			'Map Custom Fields' => __( 'Map All Attachments, Custom Fields Now', 'media-library-assistant' ),
			'custom_options_list' => '',
			'Save Changes' => __( 'Save Changes', 'media-library-assistant' ),
			/* translators: 1: "Save Changes" */
			'Click Save Changes' => sprintf( __( 'Click %1$s to update the "Enable IPTC/EXIF mapping..." checkbox and/or all rule changes and additions at once. <strong>No rule mapping will be performed.</strong>', 'media-library-assistant' ), '<strong>' . __( 'Save Changes', 'media-library-assistant' ) . '</strong>' ),
			'_wpnonce' => wp_nonce_field( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME, true, false ),
			'_wp_http_referer' => wp_referer_field( false )
		);

		/*
		 * Start with any page-level options
		 */
		$options_list = '';
		foreach ( MLACoreOptions::$mla_option_definitions as $key => $value ) {
			if ( 'iptc_exif' == $value['tab'] ) {
				$options_list .= MLASettings::mla_compose_option_row( $key, $value );
			}
		}

		$page_values['options_list'] = $options_list;

		/*
		 * Add mapping options
		 */
		$page_values['standard_options_list'] = MLAOptions::mla_iptc_exif_option_handler( 'render', 'iptc_exif_standard_mapping', MLACoreOptions::$mla_option_definitions['iptc_exif_standard_mapping'] );

		$page_values['taxonomy_options_list'] = MLAOptions::mla_iptc_exif_option_handler( 'render', 'iptc_exif_taxonomy_mapping', MLACoreOptions::$mla_option_definitions['iptc_exif_taxonomy_mapping'] );

		$page_values['custom_options_list'] = MLAOptions::mla_iptc_exif_option_handler( 'render', 'iptc_exif_custom_mapping', MLACoreOptions::$mla_option_definitions['iptc_exif_custom_mapping'] );

		$page_content['body'] = MLAData::mla_parse_template( $page_template_array['iptc-exif-tab'], $page_values );
		return $page_content;
	} // mla_compose_iptc_exif_tab

	/**
	 * Ajax handler for IPTC/EXIF tab inline mapping
	 *
	 * @since 2.00
	 *
	 * @return	void	echo json response object, then die()
	 */
	public static function mla_inline_mapping_iptc_exif_action() {
		MLACore::mla_debug_add( 'MLASettings::mla_inline_mapping_iptc_exif_action $_REQUEST = ' . var_export( $_REQUEST, true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );
		set_current_screen( $_REQUEST['screen'] );
		check_ajax_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );

		/*
		 * Convert the ajax bulk_action back to the older Submit button equivalent
		 */
		if ( ! empty( $_REQUEST['bulk_action'] ) ) {
			switch ( $_REQUEST['bulk_action'] ) {
				case 'iptc-exif-options-process-standard':
				$_REQUEST['iptc-exif-options-process-standard'] = __( 'Map All Attachments, Standard Fields Now', 'media-library-assistant' );
					break;
				case 'iptc-exif-options-process-taxonomy':
				$_REQUEST['iptc-exif-options-process-taxonomy'] = __( 'Map All Attachments, Taxonomy Terms Now', 'media-library-assistant' );
					break;
				case 'iptc-exif-options-process-custom':
				$_REQUEST['iptc-exif-options-process-custom'] = __( 'Map All Attachments, Custom Fields Now', 'media-library-assistant' );
					break;
				default:
					$match_count = preg_match( '/iptc_exif_mapping\[custom\]\[(.*)\]\[(.*)\]\[(.*)\]/', $_REQUEST['bulk_action'], $matches );
					if ( $match_count ) {
						$_REQUEST['iptc_exif_mapping']['custom'][ $matches[1] ][ $matches[2] ][ $matches[3] ] = __( 'Map All Attachments', 'media-library-assistant' );
					}
			}
		}

		/*
		 * Check for action or submit buttons.
		 */
		if ( isset( $_REQUEST['iptc_exif_mapping'] ) && is_array( $_REQUEST['iptc_exif_mapping'] ) ) {
			/*
			 * Find the current chunk
			 */
			$offset = isset( $_REQUEST['offset'] ) ? $_REQUEST['offset'] : 0;
			$length = isset( $_REQUEST['length'] ) ? $_REQUEST['length'] : 0;

			/*
			 * Check for page-level submit button to map attachments.
			 */
			if ( !empty( $_REQUEST['iptc-exif-options-process-standard'] ) ) {
				$page_content = self::_process_iptc_exif_standard( $offset, $length );
			} elseif ( !empty( $_REQUEST['iptc-exif-options-process-taxonomy'] ) ) {
				$page_content = self::_process_iptc_exif_taxonomy( $offset, $length );
			} elseif ( !empty( $_REQUEST['iptc-exif-options-process-custom'] ) ) {
				$page_content = self::_process_iptc_exif_custom( NULL, $offset, $length );
			} else {
				$page_content = array(
					'message' => '',
					'body' => '',
					'processed' => 0,
					'unchanged' => 0,
					'success' =>  0
				);

				/*
				 * Check for single-rule action buttons
				 */
				foreach ( $_REQUEST['iptc_exif_mapping']['custom'] as $key => $value ) {
					$value = stripslashes_deep( $value );

					if ( isset( $value['action'] ) ) {
						$settings = array( 'custom' => array( $key => $value ) );
						foreach ( $value['action'] as $action => $label ) {
							switch( $action ) {
								case 'map_now':
									$page_content = self::_process_iptc_exif_custom( $settings, $offset, $length );
									break;
								case 'add_rule_map':
									if ( 'none' == $value['name'] ) {
										$page_content['message'] = __( 'IPTC/EXIF no mapping changes detected.', 'media-library-assistant' );
										break;
									}
									// fallthru
								case 'add_field_map':
									if ( '' == $value['name'] ) {
										$page_content['message'] = __( 'IPTC/EXIF no mapping changes detected.', 'media-library-assistant' );
										break;
									}

									if ( 0 == $offset ) {
										$page_content = self::_save_iptc_exif_custom_settings( $settings );
										if ( false !== strpos( $page_content['message'], __( 'ERROR', 'media-library-assistant' ) ) ) {
											$page_content['processed'] = 0;
											$page_content['unchanged'] = 0;
											$page_content['success'] = 0;
											break;
										}
									}

									$current_values = MLACore::mla_get_option( 'iptc_exif_mapping' );
									$settings = array( 'custom' => array( $value['name'] => $current_values['custom'][$value['name']] ) );
									$map_content = self::_process_iptc_exif_custom( $settings, $offset, $length );
									$page_content['message'] .= '<br>&nbsp;<br>' . $map_content['message'];
									$page_content['processed'] = $map_content['processed'];
									$page_content['unchanged'] = $map_content['unchanged'];
									$page_content['success'] = $map_content['success'];
									$page_content['refresh'] = true;
									break;
								default:
									// ignore everything else
							} //switch action
						} // foreach action
					} /// isset action
				} // foreach rule
			}
		} // isset custom_field_mapping
		else {
			$page_content = array(
				'message' => '',
				'body' => '',
				'processed' => 0,
				'unchanged' => 0,
				'success' =>  0
			);
		}

		$chunk_results = array( 
			'message' => $page_content['message'],
			'processed' => $page_content['processed'],
			'unchanged' => $page_content['unchanged'],
			'success' => $page_content['success'],
			'refresh' => isset( $page_content['refresh'] ) && true == $page_content['refresh'],
		);

		MLACore::mla_debug_add( 'MLASettings::mla_inline_mapping_iptc_exif_action $chunk_results = ' . var_export( $chunk_results, true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );
		wp_send_json_success( $chunk_results );
	} // mla_inline_mapping_iptc_exif_action
} // class MLASettings_IPTCEXIF

/*
 * Actions are added here, when the source file is loaded, because the mla_compose_iptc_exif_tab
 * function is called too late to be useful.
 */
add_action( 'admin_enqueue_scripts', 'MLASettings_IPTCEXIF::mla_admin_enqueue_scripts' );
?>