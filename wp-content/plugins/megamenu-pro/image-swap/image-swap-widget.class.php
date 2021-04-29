<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Widget_Image_Swap' ) ) :

	/**
	 * Outputs a reusable block
	 */
	class Mega_Menu_Widget_Image_Swap extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 */
		public function __construct() {
			parent::__construct(
				'maxmegamenu_image_swap', // Base ID
				__( 'Image Swap (MMM)', 'megamenupro' ), // Name
				array( 'description' => __( 'Outputs a placeholder image for image swap functionality.', 'megamenupro' ) ) // Args
			);
		}


		/**
		 * Front-end display of widget.
		 *
		 * @since 2.2
		 * @see WP_Widget::widget()
		 * @param array   $args     Widget arguments.
		 * @param array   $instance Saved values from database.
		 */
		public function widget( $args, $instance ) {

			if ( ! is_array( $args ) ) {
				$args = array(
					'before_widget' => '',
					'after_widget'  => '',
				);
			}

			$media_file_id   = ! empty( $instance['media_file_id'] ) ? absint( $instance['media_file_id'] ) : 0;
			$media_file_size = ! empty( $instance['media_file_size'] ) ? $instance['media_file_size'] : 'thumbnail';

			$icon_url = '';

			if ( $media_file_id ) {
				$icon     = wp_get_attachment_image_src( $media_file_id, $media_file_size );
				$icon_url = $icon[0];
			}

			extract( $args );

			echo $before_widget;

			if ( isset( $instance['title'] ) ) {
				$title = apply_filters( 'widget_title', $instance['title'] );

				if ( ! empty( $title ) ) {
					echo $before_title . $title . $after_title;
				}
			}

			echo "<img class='mega-placeholder' data-default-src='" . esc_attr( $icon_url ) . "' src='" . esc_attr( $icon_url ) . "' />";

			echo $after_widget;
		}


		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @since 2.2
		 * @see WP_Widget::update()
		 * @param array   $new_instance Values just sent to be saved.
		 * @param array   $old_instance Previously saved values from database.
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance                    = array();
			$instance['media_file_id']   = ! empty( $new_instance['media_file_id'] ) ? $new_instance['media_file_id'] : 0;
			$instance['media_file_size'] = ! empty( $new_instance['media_file_size'] ) ? $new_instance['media_file_size'] : 0;
			$instance['title']           = sanitize_text_field( $new_instance['title'] );

			return $instance;
		}


		/**
		 * Back-end widget form.
		 *
		 * @since 2.2
		 * @see WP_Widget::form()
		 * @param array   $instance Previously saved values from database.
		 */
		public function form( $instance ) {
			$media_file_id   = ! empty( $instance['media_file_id'] ) ? absint( $instance['media_file_id'] ) : 0;
			$media_file_size = ! empty( $instance['media_file_size'] ) ? $instance['media_file_size'] : 'thumbnail';
			$icon_url        = '';
			$title           = '';

			if ( $media_file_id ) {
				$icon     = wp_get_attachment_image_src( $media_file_id, 'thumbnail' );
				$icon_url = $icon[0];
			}

			if ( isset( $instance['title'] ) ) {
				$title = $instance['title'];
			}

			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'megamenu' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<p class='clear'>
				<label for="<?php echo esc_attr( $this->get_field_id( 'media_file_id' ) ); ?>"><?php esc_attr_e( 'Default image:', 'megamenupro' ); ?></label> 
				<div class='mmm_image_selector' data-src='<?php echo esc_attr( $icon_url ); ?>' data-field='<?php echo esc_attr( $this->get_field_name( 'media_file_id' ) ); ?>'></div>
				<input type='hidden' id='<?php echo esc_attr( $this->get_field_name( 'media_file_id' ) ); ?>' name='<?php echo esc_attr( $this->get_field_name( 'media_file_id' ) ); ?>' value='<?php echo esc_attr( $media_file_id ); ?>' />
			</p>
			<p class='clear'>
				<label for="<?php echo esc_attr( $this->get_field_id( 'media_file_size' ) ); ?>"><?php esc_attr_e( 'Size:', 'megamenupro' ); ?></label> 
				<select name='<?php echo esc_attr( $this->get_field_name( 'media_file_size' ) ); ?>'>
					<?php

					$sizes = apply_filters(
						'image_size_names_choose',
						array(
							'thumbnail' => __( 'Thumbnail' ),
							'medium'    => __( 'Medium' ),
							'large'     => __( 'Large' ),
							'full'      => __( 'Full Size' ),
						)
					);

					foreach ( $sizes as $key => $value ) {
						echo "<option value='" . esc_attr( $key ) . "' " . selected( $media_file_size, $key, false ) . '>' . esc_html( $value ) . '</option>';
					}

					?>
				</select>
			</p>

			<?php
		}

	}

endif;