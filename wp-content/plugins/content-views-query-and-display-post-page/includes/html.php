<?php
/**
 * HTML output, class, id generating
 *
 * @package   PT_Content_Views
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
if ( !class_exists( 'PT_CV_Html' ) ) {

	/**
	 * @name PT_CV_Html
	 * @todo related HTML functions: Define HTML layout, Set class name...
	 */
	class PT_CV_Html {

		// Store directory of selected view_types
		static $view_type_dir	 = array();
		// Store all selected styles
		static $style			 = array();

		/**
		 * return class for Panel (Group of) group of params
		 *
		 * @return string
		 */
		static function html_panel_group_class() {
			return 'panel-group';
		}

		/**
		 * Return ID for Panel (Group of) group of params
		 *
		 * @param string $id Unique id of element
		 *
		 * @return string
		 */
		static function html_panel_group_id( $id ) {
			return 'panel-group-' . $id;
		}

		/**
		 * Return class for group of params
		 *
		 * @return string
		 */
		static function html_group_class() {
			return PT_CV_PREFIX . 'group';
		}

		/**
		 * Return ID for group of params
		 *
		 * @param string $id Unique id of element
		 *
		 * @return string
		 */
		static function html_group_id( $id ) {
			return self::html_group_class() . '-' . $id;
		}

		/**
		 * Collapse HTML
		 *
		 * @param string $parent_id Id of parent element
		 * @param string $id        Unique id of element
		 * @param string $heading   Heading text
		 * @param string $content   Content
		 * @param bool   $show      Show/hide the content
		 */
		static function html_collapse_one( $parent_id, $id, $heading, $content = '', $show = true ) {
			$class = $show ? 'in' : '';
			ob_start();
			?>
			<div class="panel panel-primary pt-accordion">
				<div class="panel-heading">
					<h4 class="panel-title" title="<?php _e( 'Click to toggle', 'content-views-query-and-display-post-page' ); ?>">
						<a class="pt-accordion-a" data-parent="#<?php echo esc_attr( $parent_id ); ?>" href="#<?php echo esc_attr( $id ); ?>">
							<?php echo $heading; ?>
						</a>
					</h4>
					<span class="pull-right clickable"><i class="glyphicon glyphicon-minus"></i></span>
				</div>
				<div id="<?php echo esc_attr( $id ); ?>" class="panel-body <?php echo esc_attr( $class ); ?>">
					<div class="panel-body">
						<?php echo $content; ?>
					</div>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * HTML content of Preview box
		 */
		static function html_preview_box() {
			ob_start();
			?>
			<div class="panel panel-default collapse <?php echo PT_CV_PREFIX; ?>wrapper" id="<?php echo PT_CV_PREFIX; ?>preview-box"></div>
			<div class="text-center hidden" style="position: absolute; left: 50%; top: 160px;">
				<?php echo self::html_loading_img(); ?>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Show loading image
		 *
		 * @param type $dimension
		 *
		 * @return type
		 */
		static function html_loading_img( $dimension = 15, $class = '' ) {
			$img = sprintf( '<img width="%1$s" height="%1$s" class="%2$s" alt="%3$s" src="%4$s" /><div class="clear %5$s"></div>', esc_attr( $dimension ), esc_attr( $class ), __( 'Loading...', 'content-views-query-and-display-post-page' ), self::loading_img_src(), PT_CV_PREFIX . 'clear-pagination' );
			return apply_filters( PT_CV_PREFIX_ . 'loading_image', $img );
		}

		static function loading_img_src() {
			return apply_filters( PT_CV_PREFIX_ . 'loading_image_url', 'data:image/gif;base64,R0lGODlhDwAPALMPAMrKygwMDJOTkz09PZWVla+vr3p6euTk5M7OzuXl5TMzMwAAAJmZmWZmZszMzP///yH/C05FVFNDQVBFMi4wAwEAAAAh+QQFCgAPACwAAAAADwAPAAAEQvDJaZaZOIcV8iQK8VRX4iTYoAwZ4iCYoAjZ4RxejhVNoT+mRGP4cyF4Pp0N98sBGIBMEMOotl6YZ3S61Bmbkm4mAgAh+QQFCgAPACwAAAAADQANAAAENPDJSRSZeA418itN8QiK8BiLITVsFiyBBIoYqnoewAD4xPw9iY4XLGYSjkQR4UAUD45DLwIAIfkEBQoADwAsAAAAAA8ACQAABC/wyVlamTi3nSdgwFNdhEJgTJoNyoB9ISYoQmdjiZPcj7EYCAeCF1gEDo4Dz2eIAAAh+QQFCgAPACwCAAAADQANAAAEM/DJBxiYeLKdX3IJZT1FU0iIg2RNKx3OkZVnZ98ToRD4MyiDnkAh6BkNC0MvsAj0kMpHBAAh+QQFCgAPACwGAAAACQAPAAAEMDC59KpFDll73HkAA2wVY5KgiK5b0RRoI6MuzG6EQqCDMlSGheEhUAgqgUUAFRySIgAh+QQFCgAPACwCAAIADQANAAAEM/DJKZNLND/kkKaHc3xk+QAMYDKsiaqmZCxGVjSFFCxB1vwy2oOgIDxuucxAMTAJFAJNBAAh+QQFCgAPACwAAAYADwAJAAAEMNAs86q1yaWwwv2Ig0jUZx3OYa4XoRAfwADXoAwfo1+CIjyFRuEho60aSNYlOPxEAAAh+QQFCgAPACwAAAIADQANAAAENPA9s4y8+IUVcqaWJ4qEQozSoAzoIyhCK2NFU2SJk0hNnyEOhKR2AzAAj4Pj4GE4W0bkJQIAOw==' );
		}

		/**
		 * Html output for button
		 *
		 * @param string $style Bootstrap type of button
		 * @param string $text  Text of button
		 * @param string $class Class of button
		 * @param string $size  Size of button
		 *
		 * @return string
		 */
		static function html_button( $style, $text = 'Button', $class = '', $size = '' ) {
			return sprintf( '<button type="button" class="btn btn-%s %s %s">%s</button>', $style, $class, $size, $text );
		}

		/**
		 * Html output for a link, but style as button
		 *
		 * @deprecated since version 2.0
		 * @param string $link  Value for href attribute of link
		 * @param string $style Bootstrap type of button
		 * @param string $text  Text of button
		 * @param string $class Class of button
		 * @param string $size  Size of button
		 *
		 * @return string
		 */
		static function link_button( $link, $style, $text = 'Button', $class = '', $size = '' ) {
			return sprintf( '<a href="%s" class="btn btn-%s %s %s">%s</a>', $link, $style, $class, $size, $text );
		}

		/**
		 * Get Output HTML of a View type
		 *
		 * @param string $view_type The view type (grid, collapse...)
		 * @param object $post      The post object
		 * @param string $style     The style of view type (main, style2...)
		 */
		static function view_type_output( $view_type, $post, $post_idx = 0, $style = 'main' ) {
			$dargs	 = PT_CV_Functions::get_global_variable( 'dargs' );
			$content = NULL;

			if ( empty( $view_type ) ) {
				return $content;
			}

			// Get view type directory
			$view_type_dir	 = apply_filters( PT_CV_PREFIX_ . 'view_type_dir', PT_CV_VIEW_TYPE_OUTPUT, $view_type ) . $view_type;
			// Compatible code for other Pro versions
			$view_type_dir	 = apply_filters( PT_CV_PREFIX_ . 'view_type_dir_special', $view_type_dir, $view_type );
			if ( strpos( $view_type_dir, $view_type . $view_type ) !== false ) {
				$view_type_dir = str_replace( $view_type . $view_type, $view_type, $view_type_dir );
			}

			// Get asset directory
			$view_type_assets_dir = apply_filters( PT_CV_PREFIX_ . 'view_type_asset', $view_type_dir, $view_type );

			if ( is_dir( $view_type_dir ) ) {
				// Store view type & asset information
				self::$view_type_dir[]	 = $view_type_assets_dir;
				self::$style[]			 = $style;

				// Generate HTML output of all content fields
				$fields_html = array();
				$other_dargs = apply_filters( PT_CV_PREFIX_ . 'dargs_others', $dargs, $post_idx );
				foreach ( (array) $other_dargs[ 'fields' ] as $field_name ) {
					// Get settings of fields
					$fargs = isset( $other_dargs[ 'field-settings' ] ) ? $other_dargs[ 'field-settings' ] : array();

					$fargs[ 'layout-format' ] = $other_dargs[ 'layout-format' ];

					// Get HTML output of field
					$item_html = self::field_item_html( $field_name, $post, $fargs );
					if ( $item_html ) {
						$fields_html[ $field_name ] = $item_html;
					}
				}

				$fields_html = apply_filters( PT_CV_PREFIX_ . 'fields_html', $fields_html, $post );
				$content	 = apply_filters( PT_CV_PREFIX_ . 'view_type_custom_output', $content, $fields_html, $post );
				if ( !$content ) {
					// Get HTML content of view type, with specific style
					$file_path = apply_filters( PT_CV_PREFIX_ . 'view_type_file', $view_type_dir . '/' . 'html' . '/' . $style . '.' . 'php' );

					if ( file_exists( $file_path ) ) {
						ob_start();
						// Include, not include_once
						include $file_path;
						$content = ob_get_clean();
					}
				}
			}

			return $content;
		}

		/**
		 * Wrap content of a item
		 *
		 * @param array  $html_item The HTML output of a item
		 * @param string $class     The extra wrapper class of a item, such as col span
		 * @param array  $post_id   The post ID
		 *
		 * @return string Full HTML output of a item
		 */
		static function content_item_wrap( $html_item, $class = '', $post_id = 0 ) {
			if ( empty( $html_item ) ) {
				return '';
			}

			$classes	 = array( $class );
			$classes[]	 = PT_CV_PREFIX . 'content-item';
			$classes[]	 = PT_CV_PREFIX . PT_CV_Functions::setting_value( PT_CV_PREFIX . 'layout-format' );
			$item_class	 = apply_filters( PT_CV_PREFIX_ . 'content_item_class', $classes, $post_id );
			$item_filter = apply_filters( PT_CV_PREFIX_ . 'content_item_filter_value', '', $post_id );

			ob_start();
			do_action( PT_CV_PREFIX_ . 'item_extra_html', $post_id );
			$html_item .= ob_get_clean();

			$result = sprintf( '<div class="%s" %s>%s</div>', esc_attr( implode( ' ', $item_class ) ), cv_sanitize_html_data( $item_filter ), force_balance_tags( $html_item ) );
			return apply_filters( PT_CV_PREFIX_ . 'item_final_html', $result, $post_id );
		}

		static function no_post_found() {
			return apply_filters( PT_CV_PREFIX_ . 'content_no_post_found_text', __( 'No posts found.' ) );
		}

		static function grid_item_wrap( $content_item ) {
			$class = PT_CV_PREFIX . 'ifield';
			return "<div class='$class'>$content_item</div>";
		}

		/**
		 * Wrap content of all items
		 *
		 * @param array $content_items The array of Raw HTML output (is not wrapped) of each item
		 * @param int   $current_page  The current page
		 * @param int   $post_per_page The number of posts per page
		 * @param int   $id            ID of View
		 *
		 * @return string Full HTML output for Content View
		 */
		static function content_items_wrap( $content_items, $current_page, $post_per_page, $id ) {
			if ( empty( $content_items ) ) {
				return PT_CV_Functions::debug_output( 'empty content_items', self::no_post_found() );
			}

			PT_CV_Functions::set_global_variable( 'content_items', $content_items );
			$dargs			 = PT_CV_Functions::get_global_variable( 'dargs' );
			$content		 = array();
			$non_paging		 = !defined( 'PT_CV_DOING_PAGINATION' );
			$before_output	 = $non_paging ? apply_filters( PT_CV_PREFIX_ . 'before_output_html', '' ) : '';
			$view_type		 = $dargs[ 'view-type' ];

			switch ( $view_type ) {
				case 'grid':
					$content_items = array_map( array( __CLASS__, 'grid_item_wrap' ), $content_items );
					PT_CV_Html_ViewType::grid_wrapper( $content_items, $content );

					break;

				case 'collapsible':
					PT_CV_Html_ViewType::collapsible_wrapper( $content_items, $content );

					break;

				case 'scrollable':
					PT_CV_Html_ViewType::scrollable_wrapper( $content_items, $content );

					break;

				default :
					foreach ( $content_items as $post_id => $content_item ) {
						$content[] = PT_CV_Html::content_item_wrap( $content_item, '', $post_id );
					}

					$content = apply_filters( PT_CV_PREFIX_ . 'content_items_wrap', $content, $content_items, $current_page, $post_per_page );

					break;
			}

			$content_list = implode( "\n", $content );

			if ( apply_filters( PT_CV_PREFIX_ . 'wrap_in_page', true ) ) {
				$cols		 = sprintf( 'data-cvc="%s"', (int) $dargs[ 'number-columns' ] );
				$page_attr	 = apply_filters( PT_CV_PREFIX_ . 'page_attr', $cols, $view_type, $content_items );
				$html		 = sprintf( '<div data-id="%s" class="%s" %s>%s</div>', esc_attr( PT_CV_PREFIX . 'page' . '-' . $current_page ), PT_CV_PREFIX . 'page', cv_sanitize_html_data( $page_attr ), $content_list );
			} else {
				$html = $content_list;
			}

			// Wrap in View
			if ( $non_paging ) {
				$use_grid	 = PT_CV_Functions::get_global_variable( 'use_grid', true );
				$view_class	 = apply_filters( PT_CV_PREFIX_ . 'view_class', array( PT_CV_PREFIX . 'view', PT_CV_PREFIX . $view_type, $use_grid ? PT_CV_PREFIX . 'colsys' : '', PT_CV_Functions::setting_value( PT_CV_PREFIX . 'lf-mobile-disable' ) ? PT_CV_PREFIX . 'nolf' : '' ) );
				$view_id	 = PT_CV_PREFIX . 'view-' . $id;
				$output		 = sprintf( '<div class="%s" id="%s">%s</div>', esc_attr( implode( ' ', array_filter( $view_class ) ) ), $view_id, $html );

				// Keep this action for CVP < 3.5
				do_action( PT_CV_PREFIX_ . 'store_view_data', $view_id );
			} else {
				$output = $html;
			}

			return $before_output . $output;
		}

		/**
		 * HTML output of a field (thumbnail, title, content, meta fields...)
		 *
		 * @param string $field_name The field name
		 * @param object $post       The post object
		 * @param array  $fargs      The array of Field settings
		 *
		 * @return string
		 */
		static function field_item_html( $field_name, $post, $fargs ) {
			if ( empty( $field_name ) ) {
				return '';
			}

			$html = '';

			switch ( $field_name ) {

				case 'thumbnail':
					if ( !empty( $fargs[ 'thumbnail' ] ) ) {
						$html = self::_field_thumbnail( $post, $fargs );
					}

					break;

				case 'title':
					$html = self::_field_title( $post, $fargs );

					break;

				case 'content':
					if ( !empty( $fargs[ 'content' ] ) ) {
						$html = self::_field_content( $post, $fargs );
					}

					break;

				case 'meta-fields':
					if ( !empty( $fargs[ 'meta-fields' ] ) ) {
						$html = self::_field_meta( $post, $fargs[ 'meta-fields' ] );
					}

					break;

				default :
					$html = apply_filters( PT_CV_PREFIX_ . 'field_item_html', $html, $field_name, $post );
					break;
			}

			return apply_filters( PT_CV_PREFIX_ . 'item_' . $field_name, $html, $post );
		}

		/**
		 * Get Title
		 *
		 * @return string
		 */
		static function _field_title( $post, $fargs ) {
			$title_class = apply_filters( PT_CV_PREFIX_ . 'field_title_class', PT_CV_PREFIX . 'title' );
			$tag		 = apply_filters( PT_CV_PREFIX_ . 'field_title_tag', 'h4' );
			$title		 = get_the_title( $post );
			if ( empty( $title ) ) {
				$title = __( '(no title)', 'content-views-query-and-display-post-page' );
			}

			$title	 = apply_filters( PT_CV_PREFIX_ . 'field_title_result', $title, $fargs, $post );
			$html	 = sprintf(
				'<%1$s class="%2$s">%3$s</%1$s>', tag_escape( $tag ), esc_attr( $title_class ), self::_field_href( $post, $title )
			);

			return $html;
		}

		/**
		 * Get content
		 *
		 * @return string
		 */
		static function _field_content( $post, $fargs ) {
			setup_postdata( $post );

			do_action( PT_CV_PREFIX_ . 'before_content' );

			$content_class	 = apply_filters( PT_CV_PREFIX_ . 'field_content_class', PT_CV_PREFIX . 'content' );
			$tag			 = apply_filters( PT_CV_PREFIX_ . 'field_content_tag', 'div' );
			$content		 = '';

			switch ( $fargs[ 'content' ][ 'show' ] ) {
				case 'excerpt':
					$length			 = (int) $fargs[ 'content' ][ 'length' ];
					$readmore_btn	 = '';
					$show_dots		 = apply_filters( PT_CV_PREFIX_ . 'field_excerpt_dots', 1, $fargs );
					$tail			 = $show_dots ? ' ...' : '';

					// Read more button
					if ( apply_filters( PT_CV_PREFIX_ . 'field_content_readmore_enable', 1, $fargs[ 'content' ] ) ) {
						$readmore_text	 = self::get_readmore_text( $fargs[ 'content' ] );
						$btn_class		 = PT_CV_PREFIX . 'readmore ' . apply_filters( PT_CV_PREFIX_ . 'field_content_readmore_class', 'btn btn-success', $fargs );
						$readmore_btn	 = self::_field_href( $post, $readmore_text, $btn_class );
						$tail .= apply_filters( PT_CV_PREFIX_ . 'field_content_readmore_seperated', '<br/>', $fargs );
					}

					// Get excerpt
					if ( $length > 0 ) {
						$GLOBALS[ 'cv_excerpt_type' ] = 'content';

						$full_excerpt = apply_filters( PT_CV_PREFIX_ . 'field_content_excerpt', get_the_content( '' ), $fargs, $post );

						if ( apply_filters( PT_CV_PREFIX_ . 'trim_excerpt', $GLOBALS[ 'cv_excerpt_type' ] == 'content' ) ) {
							$excerpt = PT_CV_Functions::cv_trim_words( $full_excerpt, $length );
						} else {
							$excerpt = $full_excerpt;
						}

						// Append readmore button
						$content = apply_filters( PT_CV_PREFIX_ . 'excerpt_html', ($show_dots ? rtrim( $excerpt, '.' ) : $excerpt) . $tail, $post ) . $readmore_btn;
					} else {
						// Display only readmore button if length <= 0
						$content = $readmore_btn;
					}

					break;

				case 'full':
					ob_start();
					the_content();
					$content = ob_get_clean();

					# Strip any raw shortcode if it is not executed
					global $cv_sc_complete;
					if ( !$cv_sc_complete ) {
						$content = PT_CV_Functions::cv_strip_shortcodes( $content, false );
					}

					break;
			}

			$content = apply_filters( PT_CV_PREFIX_ . 'field_content_final', $content, $post );
			$html	 = rtrim( $content, '.' ) ? sprintf( '<%1$s class="%2$s">%3$s</%1$s>', tag_escape( $tag ), esc_attr( $content_class ), force_balance_tags( $content ) ) : '';

			return $html;
		}

		/**
		 * Output link to item
		 */
		static function _field_href( $post, $content, $defined_class = '' ) {
			$dargs	 = PT_CV_Functions::get_global_variable( 'dargs' );
			$oargs	 = isset( $dargs[ 'other-settings' ] ) ? $dargs[ 'other-settings' ] : array();

			$open_in	 = isset( $oargs[ 'open-in' ] ) ? $oargs[ 'open-in' ] : '_blank';
			$href		 = apply_filters( PT_CV_PREFIX_ . 'field_href', get_permalink( $post->ID ), $post );
			$href_class	 = apply_filters( PT_CV_PREFIX_ . 'field_href_class', array( $open_in, $defined_class ), $oargs );
			$custom_attr = apply_filters( PT_CV_PREFIX_ . 'field_href_attrs', array(), $open_in, $oargs );
			$html		 = sprintf( '<a href="%s" class="%s" target="%s" %s>%s</a>', esc_url( $href ), esc_attr( implode( ' ', array_filter( $href_class ) ) ), esc_attr( $open_in ), cv_sanitize_html_data( implode( ' ', $custom_attr ) ), $content );

			return apply_filters( PT_CV_PREFIX_ . 'link_html', $html, array( $post, $content, $defined_class ) );
		}

		/**
		 * HTML output of thumbnail field
		 *
		 * @param object $post  The post object
		 * @param array  $_fargs The settings of this field
		 *
		 * @return string
		 */
		static function _field_thumbnail( $post, $_fargs ) {
			$layout_format = $_fargs[ 'layout-format' ];

			// Get thumbnail settings
			$fargs = $_fargs[ 'thumbnail' ];

			// Thumbnail class
			$thumbnail_position	 = 'default';
			$thumbnail_class	 = array();
			$thumbnail_class[]	 = PT_CV_PREFIX . 'thumbnail';
			$thumbnail_class[]	 = isset( $fargs[ 'style' ] ) ? $fargs[ 'style' ] : '';
			if ( $layout_format === '2-col' ) {
				$thumbnail_position	 = isset( $fargs[ 'position' ] ) ? $fargs[ 'position' ] : 'left';
				$thumbnail_class[]	 = 'pull-' . $thumbnail_position;
			}
			$gargs = array(
				'class' => apply_filters( PT_CV_PREFIX_ . 'field_thumbnail_class', implode( ' ', array_filter( $thumbnail_class ) ) ),
			);

			/**
			 * @since 1.7.5
			 * able to disable responsive image of WordPress 4.4
			 */
			if ( PT_CV_Html::is_responsive_image_disabled() ) {
				$gargs[ 'srcset' ] = 1;
			}

			// Get thumbnail dimensions
			$dimensions = (array) apply_filters( PT_CV_PREFIX_ . 'field_thumbnail_dimension_output', PT_CV_Functions::field_thumbnail_dimensions( $fargs ), $fargs );

			// Check if has thumbnail ( has_post_thumbnail doesn't works )
			$thumbnail_id	 = get_post_thumbnail_id( $post->ID );
			$html			 = '';
			if ( !empty( $thumbnail_id ) ) {
				$thumbnail_size	 = count( $dimensions ) > 1 ? $dimensions : $dimensions[ 0 ];
				$html			 = wp_get_attachment_image( (int) $thumbnail_id, $thumbnail_size, false, $gargs );
				$html			 = apply_filters( PT_CV_PREFIX_ . 'field_thumbnail_image', $html, $post, $dimensions, $fargs );
			}

			// If no thumbnail
			if ( empty( $html ) || apply_filters( PT_CV_PREFIX_ . 'force_replace_thumbnail', 0 ) ) {
				$html = apply_filters( PT_CV_PREFIX_ . 'field_thumbnail_not_found', $html, $post, $dimensions, $gargs );
			}

			// Maybe add custom wrap for image
			$html = apply_filters( PT_CV_PREFIX_ . 'field_thumbnail_image_html', $html );

			// Add link to thumbnail
			$html = self::_field_href( $post, $html, implode( ' ', array( PT_CV_PREFIX . 'href-thumbnail', PT_CV_PREFIX . 'thumb-' . $thumbnail_position ) ) );

			return $html;
		}

		/**
		 * HTML output of meta fields group
		 *
		 * @param object $post  The post object
		 * @param array  $fargs The settings of this field
		 *
		 * @return string
		 */
		static function _field_meta( $post, $fargs ) {
			$html = array();

			setup_postdata( $post );

			foreach ( $fargs as $meta => $val ) {
				if ( !$val ) {
					continue;
				}

				switch ( $meta ) {
					case 'date':
						$date_class	 = apply_filters( PT_CV_PREFIX_ . 'field_meta_class', 'entry-date', 'date' );
						$prefix_text = apply_filters( PT_CV_PREFIX_ . 'field_meta_prefix_text', '', 'date' );
						$date_format = apply_filters( PT_CV_PREFIX_ . 'field_meta_date_format', get_option( 'date_format' ) );
						$date		 = apply_filters( PT_CV_PREFIX_ . 'field_meta_date_final', mysql2date( $date_format, $post->post_date ), get_the_time( 'U' ) );

						$html[ 'date' ] = sprintf( '<span class="%s">%s <time datetime="%s">%s</time></span>', esc_attr( $date_class ), $prefix_text, esc_attr( get_the_date( 'c' ) ), esc_html( $date ) );
						break;

					case 'taxonomy':
						$term_class	 = apply_filters( PT_CV_PREFIX_ . 'field_meta_class', 'terms', 'terms' );
						$prefix_text = apply_filters( PT_CV_PREFIX_ . 'field_meta_prefix_text', '', 'terms' );

						$terms = PT_CV_Functions::post_terms( $post );
						if ( !empty( $terms ) ) {
							$term_html			 = sprintf( '<span class="%s">%s %s</span>', esc_attr( $term_class ), $prefix_text, $terms );
							$html[ 'taxonomy' ]	 = apply_filters( PT_CV_PREFIX_ . 'field_term_html', $term_html, $terms );
						}
						break;

					case 'comment':
						if ( !post_password_required() && ( comments_open() || get_comments_number() ) ) :
							$comment_class	 = apply_filters( PT_CV_PREFIX_ . 'field_meta_class', 'comments-link', 'comment' );
							$prefix_text	 = apply_filters( PT_CV_PREFIX_ . 'field_meta_prefix_text', '', 'comment' );

							ob_start();
							comments_popup_link();
							$comment_content	 = ob_get_clean();
							$comment_html		 = sprintf( '<span class="%s">%s %s</span>', esc_attr( $comment_class ), $prefix_text, $comment_content );
							$html[ 'comment' ]	 = apply_filters( PT_CV_PREFIX_ . 'field_comment_html', $comment_html, $post );
						endif;
						break;

					case 'author':
						$author_class	 = apply_filters( PT_CV_PREFIX_ . 'field_meta_class', 'author', 'author' );
						$prefix_text	 = apply_filters( PT_CV_PREFIX_ . 'field_meta_prefix_text', '', 'author' );

						$author_html		 = sprintf( '<span class="%s">%s <a href="%s" rel="author">%s</a></span>', esc_attr( $author_class ), $prefix_text, esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ), get_the_author() );
						$html[ 'author' ]	 = apply_filters( PT_CV_PREFIX_ . 'field_meta_author_html', $author_html, $post );
						break;

					default:
						break;
				}
			}

			// Merge fields, or let them as seperate items in array
			$merge_fields = apply_filters( PT_CV_PREFIX_ . 'field_meta_merge_fields', true );

			if ( $merge_fields ) {
				$result = PT_CV_Html::_field_meta_wrap( $html );
			} else {
				$result = $html;
			}

			return $result;
		}

		/**
		 * Wrap meta fields in a wrapper
		 *
		 * @param array  $meta_html Array of meta fields to wrapping
		 * @param string $seperator Seperator string when join meta fields
		 *
		 * @return string
		 */
		static function _field_meta_wrap( $meta_html, $seperator = NULL ) {
			if ( !$meta_html ) {
				return '';
			}

			$seperator	 = isset( $seperator ) ? $seperator : apply_filters( PT_CV_PREFIX_ . 'field_meta_seperator', ' / ' );
			$class		 = apply_filters( PT_CV_PREFIX_ . 'field_meta_fields_class', PT_CV_PREFIX . 'meta-fields' );
			$tag		 = apply_filters( PT_CV_PREFIX_ . 'field_meta_fields_tag', 'div' );
			$wrapper	 = sprintf( '<%1$s class="%2$s">%3$s</%1$s>', tag_escape( $tag ), esc_attr( $class ), '%s' );
			$meta_html	 = implode( $seperator, (array) apply_filters( PT_CV_PREFIX_ . 'meta_field_html', $meta_html ) );
			$html		 = !empty( $meta_html ) ? sprintf( $wrapper, $meta_html ) : '';

			return $html;
		}

		/**
		 * Output pagination
		 *
		 * @param type   $max_num_pages The total of pages
		 * @param type   $current_page  The current pages
		 * @param string $sid    View ID
		 *
		 * @return type
		 */
		static function pagination_output( $max_num_pages, $current_page, $sid ) {
			if ( !$max_num_pages || (int) $max_num_pages === 1 ) {
				return '';
			}

			$dargs			 = PT_CV_Functions::get_global_variable( 'dargs' );
			$pagination_btn	 = '';
			$type			 = isset( $dargs[ 'pagination-settings' ][ 'type' ] ) ? $dargs[ 'pagination-settings' ][ 'type' ] : 'ajax';
			$style			 = isset( $dargs[ 'pagination-settings' ][ 'style' ] ) ? $dargs[ 'pagination-settings' ][ 'style' ] : 'regular';

			if ( $type == 'normal' || $style == 'regular' ) {
				$ul_class		 = implode( ' ', array( PT_CV_PREFIX . 'pagination', PT_CV_PREFIX . $type, 'pagination' ) );
				$pagination_btn	 = sprintf( '<ul class="%s" data-totalpages="%s" data-sid="%s">%s</ul>', $ul_class, esc_attr( $max_num_pages ), esc_attr( $sid ), PT_CV_Functions::pagination( $max_num_pages, $current_page ) );
			} else {
				$pagination_btn = apply_filters( PT_CV_PREFIX_ . 'btn_more_html', $pagination_btn, $max_num_pages, $sid );
			}
			$pagination_btn .= self::html_loading_img( 15, PT_CV_PREFIX . 'spinner' );

			$class	 = esc_attr( implode( ' ', array( apply_filters( PT_CV_PREFIX_ . 'pagination_class', '' ), PT_CV_PREFIX . 'pagination-wrapper' ) ) );
			$output	 = sprintf( '<div class="%s">%s</div>', $class, $pagination_btn );

			return apply_filters( PT_CV_PREFIX_ . 'pagination_output', $output );
		}

		static function assets_of_view_types() {
			global $pt_cv_glb, $pt_cv_id;

			// If already processed | have no View on this page -> return
			if ( !empty( $pt_cv_glb[ $pt_cv_id ][ 'applied_assets' ] ) || !$pt_cv_id ) {
				return;
			}
			// Mark as processed
			$pt_cv_glb[ $pt_cv_id ][ 'applied_assets' ] = 1;

			// Link to external files
			$assets_files = apply_filters( PT_CV_PREFIX_ . 'assets_files', array() );

			if ( is_admin() ) {
				// Include assets file in Preview
				foreach ( $assets_files as $type => $srcs ) {
					foreach ( $srcs as $src ) {
						PT_CV_Asset::include_inline( 'preview', $src, $type );
					}
				}
			} else {
				// Enqueue merged asset contents
				foreach ( $assets_files as $type => $srcs ) {
					foreach ( $srcs as $src ) {
						$type		 = ( $type == 'js' ) ? 'script' : 'style';
						$function	 = "wp_enqueue_{$type}";

						if ( function_exists( $function ) ) {
							$function( PT_CV_PREFIX . $type, $src );
						}
					}
				}
			}

			// Output custom inline style for Views
			if ( apply_filters( PT_CV_PREFIX_ . 'output_view_style', 1 ) ) {
				do_action( PT_CV_PREFIX_ . 'print_view_style' );
			}
		}

		/**
		 * Scripts for Preview & WP frontend
		 */
		static function frontend_scripts() {
			PT_CV_Asset::enqueue(
				'content-views', 'script', array(
				'src'	 => plugins_url( 'public/assets/js/cv.js', PT_CV_FILE ),
				'deps'	 => array( 'jquery' ),
				)
			);

			PT_CV_Asset::localize_script(
				'content-views', PT_CV_PREFIX_UPPER . 'PUBLIC', array(
				'_prefix'			 => PT_CV_PREFIX,
				'page_to_show'		 => apply_filters( PT_CV_PREFIX_ . 'pages_to_show', 5 ),
				'_nonce'			 => wp_create_nonce( PT_CV_PREFIX_ . 'ajax_nonce' ),
				'is_admin'			 => is_admin(),
				'is_mobile'			 => apply_filters( PT_CV_PREFIX_ . 'is_mobile', wp_is_mobile() ),
				'ajaxurl'			 => admin_url( 'admin-ajax.php' ),
				'lang'				 => PT_CV_Functions::get_language(), #Get current language of site
				'loading_image_src'	 => PT_CV_Html::loading_img_src(),
				) + apply_filters( PT_CV_PREFIX_ . 'public_localize_script_extra', array() )
			);

			PT_CV_Asset::localize_script(
				array( 'content-views', 'bootstrap-admin' ), PT_CV_PREFIX_UPPER . 'PAGINATION', apply_filters( PT_CV_PREFIX_ . 'pagination_text', array(
				'first'			 => '&laquo;',
				'prev'			 => '&lsaquo;',
				'next'			 => '&rsaquo;',
				'last'			 => '&raquo;',
				'goto_first'	 => __( 'Go to first page', 'content-views-query-and-display-post-page' ),
				'goto_prev'		 => __( 'Go to previous page', 'content-views-query-and-display-post-page' ),
				'goto_next'		 => __( 'Go to next page', 'content-views-query-and-display-post-page' ),
				'goto_last'		 => __( 'Go to last page', 'content-views-query-and-display-post-page' ),
				'current_page'	 => __( 'Current page is', 'content-views-query-and-display-post-page' ),
				'goto_page'		 => __( 'Go to page', 'content-views-query-and-display-post-page' ),
				) )
			);

			// Load Pro scripts
			do_action( PT_CV_PREFIX_ . 'frontend_scripts' );
		}

		/**
		 * Styles for Preview & WP frontend
		 *
		 * @global bool $is_IE
		 */
		static function frontend_styles() {
			PT_CV_Asset::enqueue(
				'public', 'style', array(
				'src' => plugins_url( 'public/assets/css/' . (!cv_is_damaged_style() ? 'cv.css' : 'cv.im.css'), PT_CV_FILE ),
				)
			);

			// Fix bootstrap error in IE
			global $is_IE;
			if ( $is_IE ) {
				PT_CV_Asset::enqueue(
					'html5shiv', 'script', array(
					'src'	 => plugins_url( 'assets/ie-fix/html5shiv.min.js', PT_CV_FILE ),
					'ver'	 => '3.7.0',
					)
				);
				PT_CV_Asset::enqueue(
					'respond', 'script', array(
					'src'	 => plugins_url( 'assets/ie-fix/respond.js', PT_CV_FILE ),
					'ver'	 => '1.4.2',
					)
				);
			}

			// Load Pro styles
			do_action( PT_CV_PREFIX_ . 'frontend_styles' );
		}

		/**
		 * Print inline js code
		 *
		 * @param string $js The js code
		 *
		 * @return string
		 */
		static function inline_script( $js, $wrap = true, $prefix = 'inline' ) {
			// Generate random id for script tag
			$random_id = PT_CV_Functions::string_random();

			ob_start();
			?>
			<script type="text/javascript" id="<?php echo esc_attr( PT_CV_PREFIX . $prefix . '-script-' . $random_id ); ?>">
			<?php
			$newline = "\n";
			$format	 = $wrap ? "(function($){\$(function(){ {$newline}%s{$newline} });}(jQuery));" : '%s';
			printf( $format, $js );
			?>
			</script>
			<?php
			return ob_get_clean();
		}

		/**
		 * Print inline css code
		 *
		 * @param string $css The css code
		 *
		 * @return string
		 */
		static function inline_style( $css, $prefix = 'inline' ) {
			// Generate random id for style tag
			$random_id = PT_CV_Functions::string_random();

			ob_start();
			?>
			<style type="text/css" id="<?php echo esc_attr( PT_CV_PREFIX . $prefix . '-style-' . $random_id ); ?>"><?php echo '' . $css; ?></style>
			<?php
			return ob_get_clean();
		}

		static function is_responsive_image_disabled() {
			return apply_filters( PT_CV_PREFIX_ . 'disable_responsive_image', PT_CV_Functions::setting_value( PT_CV_PREFIX . 'field-thumbnail-nowprpi' ) );
		}

		/**
		 * Return Readmore text, able to get translation from CV, WP
		 *
		 * @since 1.9.1
		 * @param array $args
		 * @return string
		 */
		static function get_readmore_text( $args ) {
			$result = '';
			if ( !empty( $args[ 'readmore-text' ] ) ) {
				$result = stripslashes( cv_sanitize_tag_content( $args[ 'readmore-text' ] ) );
				// CV translation
				if ( $result === 'Read More' ) {
					$result = __( 'Read More', 'content-views-query-and-display-post-page' );
				}
			} else {
				// WP translation
				$result = ucwords( rtrim( __( 'Read more...' ), '.' ) );
			}
			return $result;
		}

	}

}