<?php
/**
 * Add / Edit Content Views
 *
 * @package   PT_Content_Views_Admin
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
// Check if using Wordpress version 3.7 or higher
$version_gt_37 = PT_CV_Functions::wp_version_compare( '3.7' );

$settings	 = array();
$id			 = 0;
$post_id	 = apply_filters( PT_CV_PREFIX_ . 'view_post_id', 0 );

// Check if this is edit View page
if ( !empty( $_GET[ 'id' ] ) ) {
	$id = cv_sanitize_vid( $_GET[ 'id' ] );

	if ( $id ) {
		global $pt_cv_admin_settings;
		$pt_cv_admin_settings	 = $settings				 = PT_CV_Functions::view_get_settings( $id, $post_id );
	}
}

// Submit handle
PT_CV_Functions::view_submit();
?>

<div class="wrap form-horizontal pt-wrap">
	<?php do_action( PT_CV_PREFIX_ . 'admin_view_header' ); ?>

	<h2><?php echo esc_html( $id ? __( 'Edit View', 'content-views-query-and-display-post-page' ) : get_admin_page_title()  ); ?></h2>

	<?php
	if ( $id ) {
		?>
		<div>
			<div class="view-code">For page content, text widget... <input class="form-control" style="width: 190px;background-color: #ADFFAD;margin-right: 50px;" type="text" value="[pt_view id=&quot;<?php echo $id ?>&quot;]" onclick="this.select()" readonly=""></div>
			<div class="view-code">For theme file <input class="form-control" style="width: 370px;" type="text" value='&lt;?php echo do_shortcode("[pt_view id=<?php echo $id ?>]"); ?&gt;' onclick="this.select()" readonly=""></div>
			<?php echo apply_filters( PT_CV_PREFIX_ . 'view_actions', '<a class="btn btn-info pull-right" target="_blank" href="https://www.contentviewspro.com/pricing/?utm_source=client&utm_medium=view_header&utm_campaign=gopro">get Pro version</a>', $id ) ?>
		</div>
		<div class="clear"></div>
		<?php
	}
	?>

	<div class="preview-wrapper">
		<?php
		// Preview
		$options = array(
			array(
				'label'	 => array(
					'text' => __( 'Preview' ),
				),
				'params' => array(
					array(
						'type'		 => 'html',
						'name'		 => 'preview',
						'content'	 => PT_CV_Html::html_preview_box(),
						'desc'		 => sprintf( __( 'To see live output, please click %s button', 'content-views-query-and-display-post-page' ), sprintf( '<code>%s</code>', __( 'Show Preview', 'content-views-query-and-display-post-page' ) ) ),
					),
				),
			),
		);
		echo PT_Options_Framework::do_settings( $options, $settings );
		?>
	</div>

	<!-- Show Preview -->
	<a class="btn btn-success" id="<?php echo esc_attr( PT_CV_PREFIX ); ?>show-preview"><?php _e( 'Show Preview', 'content-views-query-and-display-post-page' ); ?></a>

	<br>

	<!-- Settings form -->
	<form action="" method="POST" id="<?php echo esc_attr( PT_CV_PREFIX . 'form-view' ); ?>">

		<?php
		// Add nonce field
		wp_nonce_field( PT_CV_PREFIX_ . 'view_submit', PT_CV_PREFIX_ . 'form_nonce' );

		$view_object = $post_id ? get_post( $post_id ) : null;
		?>
		<!-- add hidden field -->
		<input type="hidden" name="<?php echo esc_attr( PT_CV_PREFIX . 'post-id' ); ?>" value="<?php echo esc_attr( $post_id ); ?>" />
		<input type="hidden" name="<?php echo esc_attr( PT_CV_PREFIX . 'view-id' ); ?>" value="<?php echo esc_attr( $id ); ?>" />
		<input type="hidden" name="<?php echo esc_attr( PT_CV_PREFIX . 'version' ); ?>" value="<?php echo esc_attr( apply_filters( PT_CV_PREFIX_ . 'view_version', 'free-' . PT_CV_VERSION ) ); ?>" />

		<?php
		// View title
		$options	 = array(
			array(
				'label'	 => array(
					'text' => __( 'Title' ),
				),
				'params' => array(
					array(
						'type'	 => 'text',
						'name'	 => 'view-title',
						'std'	 => isset( $view_object->post_title ) ? $view_object->post_title : '',
						'desc'	 => __( 'Enter a name to identify your views easily', 'content-views-query-and-display-post-page' ),
					),
				),
			),
		);
		echo PT_Options_Framework::do_settings( $options, $settings );
		?>
		<br>

		<!-- Save -->
		<div class="btn-cvp-action">
			<input type="submit" class="btn btn-primary pull-right <?php echo esc_attr( PT_CV_PREFIX ); ?>save-view" value="<?php _e( 'Save' ); ?>">
			<?php do_action( PT_CV_PREFIX_ . 'admin_more_buttons' ); ?>
		</div>

		<!-- Nav tabs -->
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#<?php echo esc_attr( PT_CV_PREFIX ); ?>filter-settings" data-toggle="tab"><span class="glyphicon glyphicon-search"></span><?php _e( 'Filter Settings', 'content-views-query-and-display-post-page' ); ?>
				</a>
			</li>
			<li>
				<a href="#<?php echo esc_attr( PT_CV_PREFIX ); ?>display-settings" data-toggle="tab"><span class="glyphicon glyphicon-th-large"></span><?php _e( 'Display Settings', 'content-views-query-and-display-post-page' ); ?>
				</a>
			</li>
			<?php do_action( PT_CV_PREFIX_ . 'setting_tabs_header', $settings ); ?>
		</ul>

		<!-- Tab panes -->
		<div class="tab-content">
			<!-- Filter Settings -->
			<div class="tab-pane active" id="<?php echo esc_attr( PT_CV_PREFIX ); ?>filter-settings">
				<?php
				$options	 = array(
					// Content type
					array(
						'label'	 => array(
							'text' => __( 'Content type', 'content-views-query-and-display-post-page' ),
						),
						'params' => array(
							apply_filters( PT_CV_PREFIX_ . 'contenttype_setting', array(
								'type'		 => 'radio',
								'name'		 => 'content-type',
								'options'	 => PT_CV_Values::post_types(),
								'std'		 => 'post',
							) ),
						),
					),
					// Upgrade to Pro: Custom post type
					!get_option( 'pt_cv_version_pro' ) ? PT_CV_Settings::get_cvpro( __( 'Filter custom post type (product, event...)', 'content-views-query-and-display-post-page' ) ) : '',
					apply_filters( PT_CV_PREFIX_ . 'custom_filters', array() ),
					// Common Filters
					array(
						'label'			 => array(
							'text' => __( 'Common', 'content-views-query-and-display-post-page' ),
						),
						'extra_setting'	 => array(
							'params' => array(
								'wrap-class' => PT_CV_Html::html_group_class(),
							),
						),
						'params'		 => array(
							array(
								'type'	 => 'group',
								'params' => array(
									apply_filters( PT_CV_PREFIX_ . 'sticky_posts_setting', array() ),
									// Includes
									array(
										'label'	 => array(
											'text' => __( 'Include only', 'content-views-query-and-display-post-page' ),
										),
										'params' => array(
											array(
												'type'	 => 'text',
												'name'	 => 'post__in',
												'std'	 => '',
												'desc'	 => apply_filters( PT_CV_PREFIX_ . 'setting_post_in', __( 'List of post IDs to show (comma-separated values, for example: 1,2,3)', 'content-views-query-and-display-post-page' ) ),
											),
										),
									),
									apply_filters( PT_CV_PREFIX_ . 'include_extra_settings', array() ),
									// Excludes
									array(
										'label'		 => array(
											'text' => __( 'Exclude', 'content-views-query-and-display-post-page' ),
										),
										'params'	 => array(
											array(
												'type'	 => 'text',
												'name'	 => 'post__not_in',
												'std'	 => '',
												'desc'	 => apply_filters( PT_CV_PREFIX_ . 'setting_post_not_in', __( 'List of post IDs to exclude (comma-separated values, for example: 1,2,3)', 'content-views-query-and-display-post-page' ) ),
											),
										),
										'dependence' => array( 'post__in', '' ),
									),
									apply_filters( PT_CV_PREFIX_ . 'exclude_extra_settings', array() ),
									// Parent page
									array(
										'label'		 => array(
											'text' => __( 'Parent page', 'content-views-query-and-display-post-page' ),
										),
										'params'	 => array(
											array(
												'type'	 => 'number',
												'name'	 => 'post_parent',
												'std'	 => '',
												'desc'	 => apply_filters( PT_CV_PREFIX_ . 'setting_parent_page', __( 'Enter ID of parent page to show its children', 'content-views-query-and-display-post-page' ) ),
											),
										),
										'dependence' => array( 'content-type', 'page' ),
									),
									apply_filters( PT_CV_PREFIX_ . 'post_parent_settings', array() ),
									// Limit
									array(
										'label'	 => array(
											'text' => __( 'Limit', 'content-views-query-and-display-post-page' ),
										),
										'params' => array(
											array(
												'type'	 => 'number',
												'name'	 => 'limit',
												'std'	 => '10',
												'min'	 => '1',
												'desc'	 => __( 'The number of posts to show. Leave empty to show all found posts', 'content-views-query-and-display-post-page' ),
											),
										),
									),
									apply_filters( PT_CV_PREFIX_ . 'after_limit_option', '' ),
								),
							),
						),
					), // End Common Filters
					// Advanced Filters
					array(
						'label'			 => array(
							'text' => __( 'Advance', 'content-views-query-and-display-post-page' ),
						),
						'extra_setting'	 => array(
							'params' => array(
								'wrap-class' => PT_CV_Html::html_group_class(),
								'wrap-id'	 => PT_CV_Html::html_group_id( 'advanced-params' ),
							),
						),
						'params'		 => array(
							array(
								'type'	 => 'group',
								'params' => array(
									array(
										'label'			 => array(
											'text' => '',
										),
										'extra_setting'	 => array(
											'params' => array(
												'width' => 12,
											),
										),
										'params'		 => array(
											array(
												'type'		 => 'checkbox',
												'name'		 => 'advanced-settings[]',
												'options'	 => PT_CV_Values::advanced_settings(),
												'std'		 => '',
												'class'		 => 'advanced-settings-item',
											),
										),
									),
								),
							),
						),
					), // End Advanced Filters
					// Settings of Advanced Filters options
					array(
						'label'			 => array(
							'text' => '',
						),
						'extra_setting'	 => array(
							'params' => array(
								'wrap-class' => PT_CV_Html::html_panel_group_class(),
								'wrap-id'	 => PT_CV_Html::html_panel_group_id( PT_CV_Functions::string_random() ),
							),
						),
						'params'		 => array(
							array(
								'type'	 => 'panel_group',
								'params' => apply_filters( PT_CV_PREFIX_ . 'advanced_settings_panel', array(
									// Taxonomies Settings
									'taxonomy'	 => array(
										'parent_label' => sprintf( __( 'Filter by %s', 'content-views-query-and-display-post-page' ), __( 'Taxonomy', 'content-views-query-and-display-post-page' ) ),
										// Taxonomies list
										array(
											'label'			 => array(
												'text' => __( 'Select taxonomy', 'content-views-query-and-display-post-page' ),
											),
											'extra_setting'	 => array(
												'params' => array(
													'wrap-class' => PT_CV_PREFIX . 'taxonomies',
												),
											),
											'params'		 => array(
												array(
													'type'		 => 'checkbox',
													'name'		 => 'taxonomy[]',
													'options'	 => PT_CV_Values::taxonomy_list(),
													'std'		 => '',
													'class'		 => 'taxonomy-item',
												),
											),
										),
										// Upgrade to Pro: Custom taxonomy
										!get_option( 'pt_cv_version_pro' ) ? PT_CV_Settings::get_cvpro( __( 'Filter by custom taxonomies', 'content-views-query-and-display-post-page' ) ) : '',
										// Terms list
										array(
											'label'			 => array(
												'text' => '',
											),
											'extra_setting'	 => array(
												'params' => array(
													'wrap-class' => PT_CV_Html::html_panel_group_class() . ' terms',
													'wrap-id'	 => PT_CV_Html::html_panel_group_id( PT_CV_Functions::string_random() ),
												),
											),
											'params'		 => array(
												array(
													'type'		 => 'panel_group',
													'settings'	 => array(
														'nice_name' => PT_CV_Values::taxonomy_list(),
													),
													'params'	 => PT_CV_Settings::terms_of_taxonomies(),
												),
											),
										),
										// Relation of taxonomies
										array(
											'label'	 => array(
												'text' => __( 'Relation', 'content-views-query-and-display-post-page' ),
											),
											'params' => array(
												array(
													'type'		 => 'select',
													'name'		 => 'taxonomy-relation',
													'options'	 => PT_CV_Values::taxonomy_relation(),
													'std'		 => PT_CV_Functions::array_get_first_key( PT_CV_Values::taxonomy_relation() ),
													'class'		 => 'taxonomy-relation',
												),
											),
										),
										!get_option( 'pt_cv_version_pro' ) ? PT_CV_Settings::get_cvpro( sprintf( __( 'In this lite version, when you select any term above, it will not replace posts layout in term page (for example: %s) with layout of this View', 'content-views-query-and-display-post-page' ), '<code style="font-size: 11px;">http://yourdomain/category/selected_term/</code>' ), 12, null, true ) : '',
										apply_filters( PT_CV_PREFIX_ . 'taxonomies_custom_settings', array() ),
									), // End Taxonomies Settings
									// Sort by Settings
									'order'		 => array(
										'parent_label' => __( 'Sort by', 'content-views-query-and-display-post-page' ),
										array(
											'label'			 => array(
												'text' => __( 'Sort by', 'content-views-query-and-display-post-page' ),
											),
											'extra_setting'	 => array(
												'params' => array(
													'width' => 12,
												),
											),
											'params'		 => array(
												array(
													'type'		 => 'panel_group',
													'settings'	 => array(
														'show_all' => 1,
													),
													'params'	 => PT_CV_Settings::orderby(),
												),
											),
										),
									), // End Order by Settings
									// Author Settings
									'author'	 => apply_filters( PT_CV_PREFIX_ . 'author_settings', array(
										'parent_label' => sprintf( __( 'Filter by %s', 'content-views-query-and-display-post-page' ), __( 'Author' ) ),
										array(
											'label'	 => array(
												'text' => __( 'By author', 'content-views-query-and-display-post-page' ),
											),
											'params' => array(
												array(
													'type'		 => 'select',
													'name'		 => 'author__in[]',
													'options'	 => PT_CV_Values::user_list(),
													'std'		 => '',
													'class'		 => 'select2',
													'multiple'	 => $version_gt_37 ? '1' : '0',
												),
											),
										),
										$version_gt_37 ?
											array(
											'label'	 => array(
												'text' => __( 'Not by author', 'content-views-query-and-display-post-page' ),
											),
											'params' => array(
												array(
													'type'		 => 'select',
													'name'		 => 'author__not_in[]',
													'options'	 => PT_CV_Values::user_list(),
													'std'		 => '',
													'class'		 => 'select2',
													'multiple'	 => $version_gt_37 ? '1' : '0',
												),
											),
											) : array(),
									) ), // End Author Settings
									// Status Settings
									'status'	 => array(
										'parent_label' => sprintf( __( 'Filter by %s', 'content-views-query-and-display-post-page' ), __( 'Status' ) ),
										array(
											'label'			 => array(
												'text' => '',
											),
											'extra_setting'	 => array(
												'params' => array(
													'width' => 12,
												),
											),
											'params'		 => array(
												array(
													'type'		 => 'select',
													'name'		 => 'post_status',
													'options'	 => PT_CV_Values::post_statuses(),
													'std'		 => 'publish',
													'class'		 => 'select2',
													'multiple'	 => '1',
													'desc'		 => __( 'Select post status', 'content-views-query-and-display-post-page' ),
												),
											),
										),
									), // End Status Settings
									// Keyword Settings
									'search'	 => array(
										'parent_label' => sprintf( __( 'Filter by %s', 'content-views-query-and-display-post-page' ), __( 'Keyword' ) ),
										array(
											'label'			 => array(
												'text' => '',
											),
											'extra_setting'	 => array(
												'params' => array(
													'width' => 12,
												),
											),
											'params'		 => array(
												array(
													'type'	 => 'text',
													'name'	 => 's',
													'std'	 => '',
													'desc'	 => __( 'Enter keyword to searching for posts', 'content-views-query-and-display-post-page' ) . apply_filters( PT_CV_PREFIX_ . 'searchby_keyword_desc', '' ) . '<br>' . __( 'It will search keyword in tile, excerpt, content of posts', 'content-views-query-and-display-post-page' ),
												),
											),
										),
									), // End Keyword Settings
									)
								),
							),
						),
					),
				);
				echo PT_Options_Framework::do_settings( $options, $settings );
				?>
			</div>
			<!-- end Filter Settings -->

			<!-- Display Settings -->
			<div class="tab-pane" id="<?php echo esc_attr( PT_CV_PREFIX ); ?>display-settings">
				<?php
				$options	 = array(
					// View Type
					array(
						'label'	 => array(
							'text' => __( 'Layout', 'content-views-query-and-display-post-page' ),
						),
						'params' => array(
							apply_filters( PT_CV_PREFIX_ . 'viewtype_setting', array(
								'type'		 => 'radio',
								'name'		 => 'view-type',
								'options'	 => PT_CV_Values::view_type(),
								'std'		 => PT_CV_Functions::array_get_first_key( PT_CV_Values::view_type() ),
							) ),
						),
					),
					// View settings
					array(
						'label'	 => array(
							'text' => '',
						),
						'params' => array(
							array(
								'type'		 => 'panel_group',
								'settings'	 => array(
									'no_panel'		 => 1,
									'show_only_one'	 => 1,
								),
								'params'	 => PT_CV_Values::view_type_settings(),
							),
						),
					),
					!get_option( 'pt_cv_version_pro' ) ? PT_CV_Settings::get_cvpro( __( 'More amazing layouts (Pinterest, Timeline...)', 'content-views-query-and-display-post-page' ), 10, 'margin-bottom:10px' ) : '',
					apply_filters( PT_CV_PREFIX_ . 'more_responsive_settings', array(
						'label'		 => array(
							'text' => __( 'Responsive', 'content-views-query-and-display-post-page' ),
						),
						'params'	 => array(
							array(
								'type'	 => 'group',
								'params' => array(
									array(
										'label'	 => array(
											'text' => sprintf( '%s (%s)', __( 'Items per row', 'content-views-query-and-display-post-page' ), __( 'Tablet', 'content-views-query-and-display-post-page' ) ),
										),
										'params' => array(
											array(
												'type'			 => 'number',
												'name'			 => 'resp-tablet-number-columns',
												'std'			 => '2',
												'append_text'	 => '1 &rarr; 4',
											),
										),
									),
									array(
										'label'	 => array(
											'text' => sprintf( '%s (%s)', __( 'Items per row', 'content-views-query-and-display-post-page' ), __( 'Mobile', 'content-views-query-and-display-post-page' ) ),
										),
										'params' => array(
											array(
												'type'			 => 'number',
												'name'			 => 'resp-number-columns',
												'std'			 => '1',
												'append_text'	 => '1 &rarr; 4',
											),
										),
									),
								),
							),
						),
						'dependence' => array( 'view-type', !get_option( 'pt_cv_version_pro' ) ? array( 'grid' ) : array( 'grid', 'scrollable', 'pinterest', 'glossary' ) ),
					) ),
					array(
						'label'			 => array(
							'text' => __( 'Format', 'content-views-query-and-display-post-page' ),
						),
						'extra_setting'	 => array(
							'params' => array(
								'wrap-class' => PT_CV_Html::html_group_class(),
							),
						),
						'params'		 => array(
							array(
								'type'	 => 'group',
								'params' => array(
									array(
										'label'			 => array(
											'text' => '',
										),
										'extra_setting'	 => array(
											'params' => array(
												'width' => 12,
											),
										),
										'params'		 => array(
											array(
												'type'		 => 'radio',
												'name'		 => 'layout-format',
												'options'	 => PT_CV_Values::layout_format(),
												'std'		 => PT_CV_Functions::array_get_first_key( PT_CV_Values::layout_format() ),
											),
										),
									),
									array(
										'label'			 => array(
											'text' => '',
										),
										'extra_setting'	 => array(
											'params' => array(
												'width' => 12,
											),
										),
										'params'		 => array(
											array(
												'type'		 => 'checkbox',
												'name'		 => 'lf-mobile-disable',
												'options'	 => PT_CV_Values::yes_no( 'yes', __( 'Disable this format on small screens (less than 481 pixels)', 'content-views-query-and-display-post-page' ) ),
												'std'		 => '',
											),
										),
										'dependence'	 => array( 'layout-format', '2-col' ),
									),
								),
							),
						),
					),
					// Fields settings
					array(
						'label'			 => array(
							'text' => __( 'Fields settings', 'content-views-query-and-display-post-page' ),
						),
						'extra_setting'	 => array(
							'params' => array(
								'wrap-class' => PT_CV_Html::html_group_class(),
								'wrap-id'	 => PT_CV_Html::html_group_id( 'field-settings' ),
							),
						),
						'params'		 => array(
							array(
								'type'	 => 'group',
								'params' => PT_CV_Settings::field_settings(),
							),
						),
					),
					// Pagination settings
					array(
						'label'			 => array(
							'text' => __( 'Pagination', 'content-views-query-and-display-post-page' ),
						),
						'extra_setting'	 => array(
							'params' => array(
								'wrap-class' => PT_CV_Html::html_group_class(),
							),
						),
						'params'		 => array(
							array(
								'type'	 => 'group',
								'params' => PT_CV_Settings::settings_pagination(),
							),
						),
					),
					// Other settings
					array(
						'label'			 => array(
							'text' => __( 'Others', 'content-views-query-and-display-post-page' ),
						),
						'extra_setting'	 => array(
							'params' => array(
								'wrap-class' => PT_CV_Html::html_group_class(),
							),
						),
						'params'		 => array(
							array(
								'type'	 => 'group',
								'params' => PT_CV_Settings::settings_other(),
							),
						),
					),
				);

				$options = apply_filters( PT_CV_PREFIX_ . 'display_settings', $options );
				echo PT_Options_Framework::do_settings( $options, $settings );
				?>
			</div>
			<!-- end Display Settings -->

			<?php
			do_action( PT_CV_PREFIX_ . 'setting_tabs_content', $settings );
			?>

		</div>

		<div class="clearfix"></div>
		<hr>
		<!-- Save -->
		<input type="submit" class="btn btn-primary pull-right <?php echo esc_attr( PT_CV_PREFIX ); ?>save-view" value="<?php _e( 'Save' ); ?>">
	</form>
</div>
