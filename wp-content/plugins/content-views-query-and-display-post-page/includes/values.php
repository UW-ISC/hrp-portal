<?php
/**
 * Define values for input, select...
 *
 * @package   PT_Content_Views
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
if ( !class_exists( 'PT_CV_Values' ) ) {

	/**
	 * @name PT_CV_Values
	 * @todo Define values for input, select...
	 */
	class PT_CV_Values {

		/**
		 * Get Post Types
		 *
		 * @param array  $args      Array of query parameters
		 * @param string $excludes_ Array of slug of post types want to exclude from result
		 *
		 * @return array
		 */
		static function post_types( $args = array(), $excludes_ = array() ) {
			$excludes	 = array_merge( array( 'attachment' ), $excludes_ );
			$result		 = array();
			$args		 = array_merge( array( 'public' => true, '_builtin' => true ), $args );
			$args		 = apply_filters( PT_CV_PREFIX_ . 'post_types', $args );
			$post_types	 = get_post_types( $args, 'objects' );

			foreach ( $post_types as $post_type ) {
				if ( in_array( $post_type->name, $excludes ) ) {
					continue;
				}
				$result[ $post_type->name ] = $post_type->labels->singular_name;
			}

			$result = apply_filters( PT_CV_PREFIX_ . 'post_types_list', $result );

			return $result;
		}

		/**
		 * Get list of post types and related taxonomies
		 *
		 * @return array
		 */
		static function post_types_vs_taxonomies() {
			// Get post types
			$args		 = apply_filters( PT_CV_PREFIX_ . 'post_types', array( 'public' => true, 'show_ui' => true, '_builtin' => true ) );
			$post_types	 = get_post_types( $args );

			// Get taxonomies of post types
			$result = array();

			foreach ( $post_types as $post_type ) {
				$taxonomy_names			 = get_object_taxonomies( $post_type );
				$result[ $post_type ]	 = $taxonomy_names;
			}

			return apply_filters( PT_CV_PREFIX_ . 'post_types_taxonomies', $result );
		}

		/**
		 * Get list of taxonomies
		 *
		 * @param array $args Array of query parameters
		 *
		 * @return array
		 */
		static function taxonomy_list( $args = array() ) {
			$result		 = array();
			$args		 = array_merge( array( 'public' => true, 'show_ui' => true, '_builtin' => true ), $args );
			$args		 = apply_filters( PT_CV_PREFIX_ . 'taxonomy_query_args', $args );
			$taxonomies	 = get_taxonomies( $args, 'objects' );

			foreach ( $taxonomies as $taxonomy ) {
				$result[ $taxonomy->name ] = $taxonomy->labels->singular_name;
			}

			return $result;
		}

		/**
		 * The logical relationship between taxonomies
		 *
		 * @return array
		 */
		static function taxonomy_relation() {
			return array(
				'AND'	 => __( 'AND', 'content-views-query-and-display-post-page' ) . ' &#8212; ' . __( 'show posts which match all settings', 'content-views-query-and-display-post-page' ),
				'OR'	 => __( 'OR', 'content-views-query-and-display-post-page' ) . ' &#8212; ' . __( 'show posts which match one or more settings', 'content-views-query-and-display-post-page' ),
			);
		}

		/**
		 * Operator to join. Possible values are 'IN'(default), 'NOT IN', 'AND'.
		 * @return type
		 */
		static function taxonomy_operators() {
			return array(
				'IN'	 => __( 'IN', 'content-views-query-and-display-post-page' ) . ' &#8212; ' . __( 'show posts which associate with one or more of selected terms', 'content-views-query-and-display-post-page' ),
				'NOT IN' => __( 'NOT IN', 'content-views-query-and-display-post-page' ) . ' &#8212; ' . __( 'show posts which do not associate with any of selected terms', 'content-views-query-and-display-post-page' ),
				'AND'	 => __( 'AND', 'content-views-query-and-display-post-page' ) . ' &#8212; ' . __( 'show posts which associate with all of selected terms', 'content-views-query-and-display-post-page' ),
			);
		}

		/**
		 * Get taxonomy information
		 *
		 * @param string $taxonomy The name of the taxonomy
		 * @param string $info     Field of metadata want to retrieve
		 *
		 * @return string | array
		 */
		static function taxonomy_info( $taxonomy, $info ) {
			$data = get_taxonomy( $taxonomy );

			if ( isset( $data->$info ) ) {
				$result = $data->$info;
			} else {
				if ( isset( $data->labels->$info ) ) {
					$result = $data->labels->$info;
				}
			}

			return isset( $result ) ? $result : NULL;
		}

		/**
		 * Get terms of one/many taxonomies
		 *
		 * @param string $taxonomy            The name of the taxonomy
		 * @param string $terms_of_taxonomies Array of terms of taxonomies
		 * @param array  $args                Array of query parameters
		 */
		static function term_of_taxonomy( $taxonomy, &$terms_of_taxonomies, $args = array(), $data = 'name' ) {
			$args	 = array_merge( array( 'hide_empty' => false ), $args );
			$terms	 = get_terms( array( $taxonomy ), $args );

			$term_slug_name = array();
			foreach ( $terms as $term ) {
				$term_slug_name[ $term->slug ] = ($data === 'name') ? $term->name : $term;
			}

			// Sort values of param by saved order
			$term_slug_name = apply_filters( PT_CV_PREFIX_ . 'settings_sort_single', $term_slug_name, $taxonomy . '-' . 'terms' );

			$terms_of_taxonomies[ $taxonomy ] = array_filter( $term_slug_name ); /* prevent term with empty name */
		}

		/**
		 * Yes no options
		 *
		 * @return array
		 */
		static function yes_no( $key = '', $value = '' ) {
			$result = array(
				'yes'	 => __( 'Yes', 'content-views-query-and-display-post-page' ),
				'no'	 => __( 'No', 'content-views-query-and-display-post-page' ),
			);
			if ( !empty( $key ) ) {
				return array( $key => empty( $value ) ? $result[ $key ] : $value );
			}

			return $result;
		}

		/**
		 * Paging types
		 *
		 * @return array
		 */
		static function pagination_types() {
			$result = array(
				'ajax'	 => __( 'Ajax', 'content-views-query-and-display-post-page' ),
				'normal' => __( 'Normal', 'content-views-query-and-display-post-page' ),
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'pagination_types', $result );

			return $result;
		}

		/**
		 * Paging styles
		 *
		 * @return array
		 */
		static function pagination_styles() {
			$result = array(
				'regular' => __( 'Numbered pagination', 'content-views-query-and-display-post-page' ),
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'pagination_styles', $result );

			return $result;
		}

		/**
		 * Order options
		 *
		 * @return array
		 */
		static function orders() {
			return array(
				'asc'	 => __( 'Ascending' ),
				'desc'	 => __( 'Descending' ),
			);
		}

		/**
		 * List post status
		 */
		static function post_statuses() {
			return array(
				'publish'	 => __( 'Published' ),
				'pending'	 => __( 'Pending Review' ),
				'draft'		 => __( 'Draft' ),
				'future'	 => __( 'Scheduled' ),
				'private'	 => __( 'Privately Published' ),
				'trash'		 => __( 'Trash' ),
				'inherit'	 => __( 'Inherit', 'content-views-query-and-display-post-page' ),
			);
		}

		/**
		 * Advanced filters options
		 *
		 * @return array
		 */
		static function advanced_settings() {
			return apply_filters(
				PT_CV_PREFIX_ . 'advanced_settings', array(
				'taxonomy'	 => __( 'Taxonomy', 'content-views-query-and-display-post-page' ) . sprintf( ' (%s, %s...)', __( 'Categories' ), __( 'Tags' ) ),
				'status'	 => __( 'Status' ),
				'order'		 => __( 'Sort by', 'content-views-query-and-display-post-page' ),
				'search'	 => __( 'Keyword' ),
				'author'	 => __( 'Author' ),
				)
			);
		}

		/**
		 * Show WP author dropdown list
		 *
		 * @return array
		 */
		static function user_list() {

			$result	 = array();
			$show	 = 'display_name';

			$args = array(
				'fields'	 => array( 'ID', $show ),
				'orderby'	 => 'display_name',
				'order'		 => 'ASC',
			);

			$users = get_users( $args );
			foreach ( (array) $users as $user ) {
				$user->ID	 = (int) $user->ID;
				$display	 = !empty( $user->$show ) ? $user->$show : '(' . $user->user_login . ')';

				$result[ $user->ID ] = esc_html( $display );
			}

			return $result;
		}

		/**
		 * Get available filters for Order by Content item
		 */
		static function post_regular_orderby() {
			$regular_orderby = array(
				''			 => sprintf( '- %s -', __( 'Select' ) ),
				'ID'		 => __( 'ID', 'content-views-query-and-display-post-page' ),
				'title'		 => __( 'Title' ),
				'date'		 => __( 'Published date', 'content-views-query-and-display-post-page' ),
				'modified'	 => __( 'Modified date', 'content-views-query-and-display-post-page' ),
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'regular_orderby', $regular_orderby );

			return $result;
		}

		/**
		 * View type options
		 *
		 * @return array
		 */
		static function view_type() {

			$view_type = array(
				'grid'			 => __( 'Grid', 'content-views-query-and-display-post-page' ),
				'collapsible'	 => __( 'Collapsible List', 'content-views-query-and-display-post-page' ),
				'scrollable'	 => __( 'Scrollable List', 'content-views-query-and-display-post-page' ),
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'view_type', $view_type );

			return $result;
		}

		/**
		 * Settings of All View Types
		 *
		 * @return array
		 */
		static function view_type_settings() {

			$view_type_settings = array();

			// Settings of Grid type
			$view_type_settings[ 'grid' ] = PT_CV_Settings::view_type_settings_grid();

			// Settings of Collapsible type
			$view_type_settings[ 'collapsible' ] = PT_CV_Settings::view_type_settings_collapsible();

			// Settings of Scrollable type
			$view_type_settings[ 'scrollable' ] = PT_CV_Settings::view_type_settings_scrollable();

			$result = apply_filters( PT_CV_PREFIX_ . 'view_type_settings', $view_type_settings );

			return $result;
		}

		/**
		 * Layout format options
		 *
		 * @return array
		 */
		static function layout_format() {

			$result = array(
				'1-col'	 => __( 'Show thumbnail & text vertically', 'content-views-query-and-display-post-page' ),
				'2-col'	 => __( 'Show thumbnail on the left/right of text', 'content-views-query-and-display-post-page' ),
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'layout_format', $result );

			return $result;
		}

		/**
		 * Open in options
		 */
		static function open_in() {

			$open_in = array(
				'_self'	 => __( 'Current tab', 'content-views-query-and-display-post-page' ),
				'_blank' => __( 'New tab', 'content-views-query-and-display-post-page' ),
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'open_in', $open_in );

			return $result;
		}

		/**
		 * Get all thumbnail sizes
		 */
		static function field_thumbnail_sizes( $_size_name = '' ) {
			// All available thumbnail sizes
			global $_wp_additional_image_sizes;

			$result				 = $sizes_to_sort		 = $dimensions_to_sort	 = array();

			foreach ( get_intermediate_image_sizes() as $size_name ) {
				if ( in_array( $size_name, array( 'thumbnail', 'medium', 'large' ) ) ) {
					$this_size	 = array();
					$this_size[] = get_option( $size_name . '_size_w' );
					$this_size[] = get_option( $size_name . '_size_h' );

					// Add official sizes to result
					$result[ $size_name ] = ucfirst( $size_name ) . ' (' . implode( ' &times; ', $this_size ) . ')';
				} else {
					if ( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[ $size_name ] ) ) {

						$this_size				 = array();
						$this_size[ 'width' ]	 = $_wp_additional_image_sizes[ $size_name ][ 'width' ];
						$this_size[ 'height' ]	 = $_wp_additional_image_sizes[ $size_name ][ 'height' ];

						// Calculate sizes value for sorting
						$sizes_value = intval( $this_size[ 'width' ] ) * intval( $this_size[ 'height' ] ) + rand( 1, 10 );

						$dimensions_to_sort[ $sizes_value ] = $size_name;
					} else {
						$this_size = array( 0, 0 );
					}

					$sizes_to_sort[ $size_name ] = ucfirst( preg_replace( '/[\-_]/', ' ', $size_name ) ) . ' (' . implode( ' &times; ', $this_size ) . ')';
				}

				if ( !empty( $_size_name ) && $_size_name == $size_name ) {
					return $this_size;
				}
			}
			// Add full sizes
			$result[ 'full' ] = __( 'Full Size' );

			// Sort custom sizes by index (width * height)
			krsort( $dimensions_to_sort );

			// Get array element in ASC sorted order
			foreach ( array_reverse( $dimensions_to_sort ) as $size_name ) {
				$result[ $size_name ] = $sizes_to_sort[ $size_name ];
			}

			$result = apply_filters( PT_CV_PREFIX_ . 'field_thumbnail_sizes', $result );

			return $result;
		}

		/**
		 * Thumbnail Position
		 *
		 * @return array
		 */
		static function thumbnail_position() {

			$thumbnail_position = array(
				'left'	 => __( 'Left' ),
				'right'	 => __( 'Right' ),
			);

			$result = apply_filters( PT_CV_PREFIX_ . 'thumbnail_position', $thumbnail_position );

			return $result;
		}

	}

}