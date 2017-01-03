<?php
/**
 * Define settings for options
 *
 * @package   PT_Content_Views
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
if ( !class_exists( 'PT_CV_Settings' ) ) {

	/**
	 * @name PT_CV_Settings
	 * @todo Define settings for options
	 */
	class PT_CV_Settings {

		/**
		 * Get collection : Taxonomies => Terms
		 *
		 * @param string $taxonomies Array of taxonomies
		 * @param array  $args       Array of query parameters
		 */
		static function terms_of_taxonomies( $taxonomies = array(), $args = array() ) {
			$terms_of_taxonomies = $result				 = array();
			// Get taxonomies
			$taxonomies			 = PT_CV_Values::taxonomy_list();
			// Get slug list of taxonomies
			$taxonomies_slug	 = array_keys( $taxonomies );

			foreach ( $taxonomies_slug as $taxonomy_slug ) {
				PT_CV_Values::term_of_taxonomy( $taxonomy_slug, $terms_of_taxonomies, $args );
			}

			foreach ( $terms_of_taxonomies as $taxonomy_slug => $terms ) {

				$result[ $taxonomy_slug ] = array(
					// Select term to filter
					array(
						'label'	 => array(
							'text' => __( 'Select terms', 'content-views-query-and-display-post-page' ),
						),
						'params' => array(
							array(
								'type'		 => 'select',
								'name'		 => $taxonomy_slug . '-terms[]',
								'options'	 => $terms,
								'std'		 => '',
								'class'		 => apply_filters( PT_CV_PREFIX_ . 'select_term_class', 'select2' ),
								'multiple'	 => '1',
							),
						),
					),
					//Operator
					array(
						'label'		 => array(
							'text' => __( 'Operator', 'content-views-query-and-display-post-page' ),
						),
						'params'	 => array(
							array(
								'type'		 => 'select',
								'name'		 => $taxonomy_slug . '-operator',
								'options'	 => PT_CV_Values::taxonomy_operators(),
								'std'		 => 'IN',
							),
						),
						'dependence' => array( 'taxonomy-term-info', 'as_output', '!=' ),
					),
				);
			}

			return $result;
		}

		/**
		 * Order by options
		 *
		 * @return array
		 */
		static function orderby() {
			$result = array();

			$result[ 'common' ] = array(
				// Order By
				array(
					'label'	 => array(
						'text' => __( 'Sort by', 'content-views-query-and-display-post-page' ),
					),
					'params' => array(
						array(
							'type'		 => 'select',
							'name'		 => 'orderby',
							'options'	 => PT_CV_Values::post_regular_orderby(),
							'std'		 => '',
						),
					),
				),
				// Upgrade to Pro: More sort by options
				!get_option( 'pt_cv_version_pro' ) ? PT_CV_Settings::get_cvpro( __( 'Sort by drag & drop, custom field, slug, random order, menu order', 'content-views-query-and-display-post-page' ) ) : '',
				// Order
				apply_filters( PT_CV_PREFIX_ . 'orders', array(
					'label'	 => array(
						'text' => __( 'Order' ),
					),
					'params' => array(
						array(
							'type'		 => 'radio',
							'name'		 => 'order',
							'options'	 => PT_CV_Values::orders(),
							'std'		 => 'asc',
						),
					),
				) ),
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'orderby', $result );

			return $result;
		}

		/**
		 * Pagination settings
		 *
		 * @return array
		 */
		static function settings_pagination() {

			$prefix = 'pagination-';

			$result = array(
				// Pagination
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
							'name'		 => 'enable-pagination',
							'options'	 => PT_CV_Values::yes_no( 'yes', __( 'Enable' ) ),
							'std'		 => '',
						),
					),
				),
				// Items per page
				array(
					'label'			 => array(
						'text' => __( 'Items per page', 'content-views-query-and-display-post-page' ),
					),
					'extra_setting'	 => array(
						'params' => array(
							'wrap-class' => PT_CV_PREFIX . 'w200',
						),
					),
					'params'		 => array(
						array(
							'type'			 => 'number',
							'name'			 => $prefix . 'items-per-page',
							'std'			 => '5',
							'placeholder'	 => 'for example: 5',
							'desc'			 => sprintf( __( 'If value of %s setting is not empty, this value should be smaller than that', 'content-views-query-and-display-post-page' ), sprintf( '<code>%s</code>', __( 'Limit', 'content-views-query-and-display-post-page' ) ) ),
						),
					),
					'dependence'	 => array( 'enable-pagination', 'yes' ),
				),
				// Pagination Type
				array(
					'label'		 => array(
						'text' => __( 'Type' ),
					),
					'params'	 => array(
						array(
							'type'		 => 'radio',
							'name'		 => $prefix . 'type',
							'options'	 => PT_CV_Values::pagination_types(),
							'std'		 => 'ajax',
						),
					),
					'dependence' => array( 'enable-pagination', 'yes' ),
				),
				// Pagination Style
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
							'type'	 => 'group',
							'params' => array(
								array(
									'label'		 => array(
										'text' => __( 'Style', 'content-views-query-and-display-post-page' ),
									),
									'params'	 => array(
										array(
											'type'		 => 'radio',
											'name'		 => $prefix . 'style',
											'options'	 => PT_CV_Values::pagination_styles(),
											'std'		 => PT_CV_Functions::array_get_first_key( PT_CV_Values::pagination_styles() ),
										),
									),
									'dependence' => array( $prefix . 'type', 'normal', '!=' ),
								),
							),
						),
					),
					'dependence'	 => array( 'enable-pagination', 'yes' ),
				),
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'settings_pagination', $result, $prefix );

			return $result;
		}

		/**
		 * Other settings for All View Type
		 */
		static function settings_other() {

			$prefix = 'other-';

			$result = array(
				// Open an item in
				array(
					'label'	 => array(
						'text' => __( 'Open item in', 'content-views-query-and-display-post-page' ),
					),
					'params' => array(
						array(
							'type'		 => 'radio',
							'name'		 => $prefix . 'open-in',
							'options'	 => PT_CV_Values::open_in(),
							'std'		 => PT_CV_Functions::array_get_first_key( PT_CV_Values::open_in() ),
							'desc'		 => __( 'How to open item when click on Title, Thumbnail, Read-more button', 'content-views-query-and-display-post-page' ),
						),
					),
				),
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'settings_other', $result, $prefix );

			return $result;
		}

		/**
		 * Fields settings
		 */
		static function field_settings() {

			$prefix	 = 'field-';
			$prefix2 = 'show-' . $prefix;

			$result = array(
				// Fields display
				array(
					'label'			 => array(
						'text' => '',
					),
					'extra_setting'	 => array(
						'params' => array(
							'wrap-class' => PT_CV_Html::html_group_class(),
							'width'		 => 12,
						),
					),
					'params'		 => array(
						array(
							'type'	 => 'group',
							'params' => PT_CV_Settings::field_display_settings(),
						),
					),
				),
				// Upgrade to Pro: Drag & Drop
				!get_option( 'pt_cv_version_pro' ) ? PT_CV_Settings::get_cvpro( __( 'Show Custom Fields, show Title above Thumbnail...', 'content-views-query-and-display-post-page' ), 12, 'margin-top: -15px; margin-bottom: 5px; width: 100%;' ) : '',
				// Title settings
				apply_filters( PT_CV_PREFIX_ . 'settings_title_display', array(), $prefix, $prefix2 ),
				// Thumbnail settings
				array(
					'label'			 => array(
						'text' => __( 'Thumbnail' ),
					),
					'extra_setting'	 => array(
						'params' => array(
							'group-class'	 => PT_CV_PREFIX . 'field-setting',
							'wrap-class'	 => PT_CV_Html::html_group_class() . ' ' . PT_CV_PREFIX . 'thumbnail-setting' . ' ' . PT_CV_PREFIX . 'w50',
						),
					),
					'params'		 => array(
						array(
							'type'	 => 'group',
							'params' => PT_CV_Settings::field_thumbnail_settings( $prefix ),
						),
					),
					'dependence'	 => array( $prefix2 . 'thumbnail', 'yes' ),
				),
				// Content settings
				array(
					'label'			 => array(
						'text' => __( 'Content' ),
					),
					'extra_setting'	 => array(
						'params' => array(
							'group-class' => PT_CV_PREFIX . 'field-setting' . ' ' . PT_CV_PREFIX . 'content-setting',
						),
					),
					'params'		 => array(
						array(
							'type'		 => 'radio',
							'name'		 => $prefix . 'content-show',
							'options'	 => array(
								'full'		 => __( 'Show Full Content', 'content-views-query-and-display-post-page' ),
								'excerpt'	 => __( 'Show Excerpt', 'content-views-query-and-display-post-page' ),
							),
							'std'		 => 'excerpt',
						),
					),
					'dependence'	 => array( $prefix2 . 'content', 'yes' ),
				),
				// Excerpt settings
				array(
					'label'			 => array(
						'text' => '',
					),
					'extra_setting'	 => array(
						'params' => array(
							'width'			 => 12,
							'group-class'	 => PT_CV_PREFIX . 'field-setting' . ' ' . PT_CV_PREFIX . 'excerpt-setting',
							'wrap-id'		 => PT_CV_Html::html_group_id( 'excerpt-settings' ),
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
											'type'	 => 'group',
											'params' => apply_filters(
												PT_CV_PREFIX_ . 'excerpt_settings', array(
												// Excerpt length
												array(
													'label'	 => array(
														'text' => __( 'Excerpt settings', 'content-views-query-and-display-post-page' ),
													),
													'params' => array(
														array(
															'type'			 => 'number',
															'name'			 => $prefix . 'excerpt-length',
															'std'			 => '20',
															'placeholder'	 => 'for example: 20',
															'append_text'	 => 'words',
															'desc'			 => __( 'Generate excerpt by selecting the first X words of post content', 'content-views-query-and-display-post-page' ),
														),
													),
												),
												// Upgrade to Pro: Manual excerpt
												!get_option( 'pt_cv_version_pro' ) ? PT_CV_Settings::get_cvpro( __( 'Use manual excerpt', 'content-views-query-and-display-post-page' ) ) : '',
												// Allow HTML tags
												array(
													'label'	 => array(
														'text' => '',
													),
													'params' => array(
														array(
															'type'		 => 'checkbox',
															'name'		 => $prefix . 'excerpt-allow_html',
															'options'	 => PT_CV_Values::yes_no( 'yes', __( 'Allow HTML tags (a, br, strong, em, strike, i, ul, ol, li) in excerpt', 'content-views-query-and-display-post-page' ) ),
															'std'		 => '',
														),
													),
												),
												// Read more text
												!get_option( 'pt_cv_version_pro' ) ? array(
													'label'	 => array(
														'text' => __( 'Read More', 'content-views-query-and-display-post-page' ),
													),
													'params' => array(
														array(
															'type'	 => 'text',
															'name'	 => $prefix . 'excerpt-readmore-text',
															'std'	 => ucwords( rtrim( __( 'Read more...' ), '.' ) ),
															'desc'	 => __( 'Text for Read more', 'content-views-query-and-display-post-page' ),
														),
													),
													) : '',
												), $prefix . 'excerpt-'
											),
										),
									),
									'dependence'	 => array( $prefix . 'content-show', 'excerpt' ),
								),
							),
						),
					),
					'dependence'	 => array( $prefix2 . 'content', 'yes' ),
				),
				// Meta fields settings
				array(
					'label'			 => array(
						'text' => __( 'Meta fields', 'content-views-query-and-display-post-page' ),
					),
					'extra_setting'	 => array(
						'params' => array(
							'group-class'	 => PT_CV_PREFIX . 'field-setting' . ' ' . PT_CV_PREFIX . 'metafield-setting',
							'wrap-class'	 => PT_CV_Html::html_group_class() . ' ' . PT_CV_PREFIX . 'meta-fields-settings',
						),
					),
					'params'		 => array(
						array(
							'type'	 => 'group',
							'params' => PT_CV_Settings::field_meta_fields( 'meta-fields-' ),
							'desc'	 => apply_filters( PT_CV_PREFIX_ . 'settings_sort_text', '' ),
						),
					),
					'dependence'	 => array( $prefix2 . 'meta-fields', 'yes' ),
				),
				// Taxonomies settings
				apply_filters( PT_CV_PREFIX_ . 'settings_taxonomies_display', array(), 'meta-fields-' ),
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'field_settings', $result, $prefix2 );

			return $result;
		}

		/**
		 * Fields display
		 *
		 * @return array
		 */
		static function field_display_settings() {

			$field_display_settings = array(
				array(
					'label'			 => array(
						'text' => '',
					),
					'extra_setting'	 => array(
						'params' => array(
							'width'		 => 12,
							'wrap-class' => PT_CV_PREFIX . 'field-display',
						),
					),
					'params'		 => array(
						array(
							'type'	 => 'group',
							'params' => PT_CV_Settings::field_display(),
							'desc'	 => apply_filters( PT_CV_PREFIX_ . 'settings_sort_text', '' ),
						),
					),
				),
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'field_display_settings', $field_display_settings );

			return $result;
		}

		/**
		 * Options to check/uncheck to display fields
		 *
		 * @return array
		 */
		static function field_display() {

			$prefix = 'show-field-';

			$result = array(
				// Thumbnail position
				array(
					'label'			 => array(
						'text' => __( 'Thumbnail position', 'content-views-query-and-display-post-page' ),
					),
					'extra_setting'	 => array(
						'params' => array(
							'group-class'	 => PT_CV_PREFIX . 'thumb-position',
							'wrap-class'	 => PT_CV_PREFIX . 'w200',
						),
					),
					'params'		 => array(
						array(
							'type'		 => 'select',
							'name'		 => 'field-' . 'thumbnail-position',
							'options'	 => PT_CV_Values::thumbnail_position(),
							'std'		 => PT_CV_Functions::array_get_first_key( PT_CV_Values::thumbnail_position() ),
						),
					),
					'dependence'	 => array( 'layout-format', '2-col' ),
				),
				// Show Thumbnail
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
							'name'		 => $prefix . 'thumbnail',
							'options'	 => PT_CV_Values::yes_no( 'yes', __( 'Show Thumbnail', 'content-views-query-and-display-post-page' ) ),
							'std'		 => 'yes',
						),
					),
					'dependence'	 => array( 'layout-format', '1-col' ),
				),
				// Show Title
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
							'name'		 => $prefix . 'title',
							'options'	 => PT_CV_Values::yes_no( 'yes', __( 'Show Title', 'content-views-query-and-display-post-page' ) ),
							'std'		 => 'yes',
						),
					),
				),
				// Show Content
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
							'name'		 => $prefix . 'content',
							'options'	 => PT_CV_Values::yes_no( 'yes', __( 'Show Content', 'content-views-query-and-display-post-page' ) ),
							'std'		 => 'yes',
						),
					),
				),
				// Show Meta fields
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
							'name'		 => $prefix . 'meta-fields',
							'options'	 => PT_CV_Values::yes_no( 'yes', sprintf( '%s (%s, %s, %s, %s)', __( 'Show Meta Fields', 'content-views-query-and-display-post-page' ), __( 'Taxonomy', 'content-views-query-and-display-post-page' ), __( 'Author' ), __( 'Date' ), __( 'Comment' ) ) ),
							'std'		 => '',
						),
					),
				),
			);

			// Add/remove params
			$result = apply_filters( PT_CV_PREFIX_ . 'field_display', $result, $prefix );

			// Sort array of params by saved order
			$result = apply_filters( PT_CV_PREFIX_ . 'settings_sort', $result, PT_CV_PREFIX . $prefix );

			return $result;
		}

		/**
		 * Setting options for Field = Thumbnail
		 */
		static function field_thumbnail_settings( $prefix ) {

			$result = array(
				// Size
				array(
					'label'	 => array(
						'text' => __( 'Size' ),
					),
					'params' => array(
						array(
							'type'		 => 'select',
							'name'		 => $prefix . 'thumbnail-size',
							'options'	 => PT_CV_Values::field_thumbnail_sizes(),
							'std'		 => 'medium',
						),
					),
				),
				// Disable WP 4.4 responsive image
				!PT_CV_Functions::wp_version_compare( '4.4' ) ? '' :
					'disable-wp44-resimg' => array(
					'label'			 => array(
						'text' => '',
					),
					'extra_setting'	 => array(
						'params' => array(
							'wrap-class' => 'checkbox-inline',
						),
					),
					'params'		 => array(
						array(
							'type'		 => 'checkbox',
							'name'		 => $prefix . 'thumbnail-nowprpi',
							'options'	 => PT_CV_Values::yes_no( 'yes', __( 'Disable responsive image of WordPress', 'content-views-query-and-display-post-page' ) ),
							'std'		 => '',
							'desc'		 => __( 'Check this option if thumbnail looks blurry', 'content-views-query-and-display-post-page' ),
						),
					),
				),
				// Upgrade to Pro: Show image/video in content as thumbnail
				!get_option( 'pt_cv_version_pro' ) ? PT_CV_Settings::get_cvpro( sprintf( __( 'In this lite version, thumbnail is only shown if post has %s', 'content-views-query-and-display-post-page' ), sprintf( '<a target="_blank" href="https://codex.wordpress.org/Post_Thumbnails">%s</a>', __( 'Featured Image' ) ) ), 12, null, true ) : '',
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'field_thumbnail_settings', $result, $prefix );

			return $result;
		}

		/**
		 * Show settings of other fields
		 */
		static function field_meta_fields( $prefix ) {

			$result = array(
				// Date
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
							'name'		 => $prefix . 'date',
							'options'	 => PT_CV_Values::yes_no( 'yes', __( 'Show Date', 'content-views-query-and-display-post-page' ) ),
							'std'		 => 'yes',
						),
					),
				),
				// Author
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
							'name'		 => $prefix . 'author',
							'options'	 => PT_CV_Values::yes_no( 'yes', __( 'Show Author', 'content-views-query-and-display-post-page' ) ),
							'std'		 => '',
						),
					),
				),
				// Taxonomy
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
							'name'		 => $prefix . 'taxonomy',
							'options'	 => PT_CV_Values::yes_no( 'yes', sprintf( __( 'Show Taxonomies (%s, %s...)', 'content-views-query-and-display-post-page' ), __( 'Categories' ), __( 'Tags' ) ) ),
							'std'		 => 'yes',
						),
					),
					'dependence'	 => array( 'content-type', 'page', '!=' ),
				),
				// Comment
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
							'name'		 => $prefix . 'comment',
							'options'	 => PT_CV_Values::yes_no( 'yes', __( 'Show Comment Count', 'content-views-query-and-display-post-page' ) ),
							'std'		 => '',
						),
					),
				),
			);

			// Sort array of params by saved order
			$result = apply_filters( PT_CV_PREFIX_ . 'settings_sort', $result, PT_CV_PREFIX . $prefix );

			return $result;
		}

		/**
		 * Settings of View Type = Grid
		 *
		 * @return array
		 */
		static function view_type_settings_grid() {

			$prefix = 'grid-';

			$result = array(
				// Number of columns
				array(
					'label'		 => array(
						'text' => __( 'Items per row', 'content-views-query-and-display-post-page' ),
					),
					'params'	 => array(
						array(
							'type'			 => 'number',
							'name'			 => $prefix . 'number-columns',
							'std'			 => '2',
							'append_text'	 => '1 &rarr; 12',
						),
					),
					'dependence' => array( 'view-type', 'grid' ),
				),
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'view_type_settings_grid', $result );

			return $result;
		}

		/**
		 * Settings of View Type = Collapsible
		 *
		 * @return array
		 */
		static function view_type_settings_collapsible() {
			$prefix	 = 'collapsible-';
			$result	 = array(
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
							'name'		 => $prefix . 'open-first-item',
							'options'	 => PT_CV_Values::yes_no( 'yes', __( 'Open first item by default', 'content-views-query-and-display-post-page' ) ),
							'std'		 => 'yes',
						),
					),
				),
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'view_type_settings_collapsible_184', $result );

			return $result;
		}

		/**
		 * Settings of View Type = Scrollable
		 *
		 * @return array
		 */
		static function view_type_settings_scrollable() {

			$prefix = 'scrollable-';

			$result = array(
				PT_CV_Settings::setting_no_option(),
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'view_type_settings_scrollable', $result );

			return $result;
		}

		/**
		 * Setting with no option
		 *
		 * @return array
		 */
		static function setting_no_option( $only_text = false ) {
			$msg	 = !get_option( 'pt_cv_version_pro' ) ? '' : __( 'There is no option', 'content-views-query-and-display-post-page' );
			$class	 = PT_CV_PREFIX . 'text cv-noop-profe';
			$text	 = "<div class='$class' style='color:#999'>$msg</div>";

			return $only_text ? $text : array(
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
						'type'		 => 'html',
						'content'	 => $text,
					),
				),
			);
		}

		/**
		 * Show Get CVPro
		 *
		 * @param string $text
		 * @param int $width
		 * @param string $style
		 * @param bool $notice
		 * @return string
		 */
		static function get_cvpro( $text, $width = 10, $style = '', $notice = false ) {
			$url = sprintf( ' &raquo; <a href="%s" target="_blank">%s</a>', esc_url( 'https://www.contentviewspro.com/pricing/?utm_source=client&utm_medium=view_fields&utm_campaign=gopro' ), __( 'get Pro version', 'content-views-query-and-display-post-page' ) );

			return array(
				'label'			 => array(
					'text' => '',
				),
				'extra_setting'	 => array(
					'params' => array(
						'width' => $width,
					),
				),
				'params'		 => array(
					array(
						'type'		 => 'html',
						'content'	 => $notice ?
							sprintf( '<div class="alert alert-warning cvgopro"><strong>%s:</strong> %s</div>', __( 'Notice', 'content-views-query-and-display-post-page' ), $text . '.' ) :
							sprintf( '<p class="text-muted cvgopro" style="%s">&rarr; %s</p>', $style, $text . $url ),
					),
				),
			);
		}

	}

}