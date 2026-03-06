<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Widget_Reusable_Block' ) ) :

	/**
	 * Outputs a reusable block
	 *
	 * Credit: Based on https://wordpress.org/plugins/block-widget/ by Maarten Menten
	 */
	class Mega_Menu_Widget_Reusable_Block extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 */
		public function __construct() {
			parent::__construct(
				'maxmegamenu_reusable_block', // Base ID
				'Block Pattern (MMM)', // Name
				array( 'description' => __( 'Outputs a saved block pattern.', 'megamenu' ) ) // Args
			);
		}


		/**
		 * Front-end display of widget.
		 *
		 * @since 2.7.4
		 * @see WP_Widget::widget()
		 * @param array   $args     Widget arguments.
		 * @param array   $instance Saved values from database.
		 */
		public function widget( $args, $instance ) {
			if ( empty( $instance['block'] ) || ! get_post_type( $instance['block'] ) ) {
				return;
			}

			extract( $args );

			$title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : "";

			echo $before_widget;

			if ( ! empty( $title ) ) {
				echo $before_title . $title . $after_title;
			}

			echo do_blocks( do_shortcode ( get_post_field( 'post_content', $instance['block'] ) ) );

			echo $after_widget;
		}


		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @since 2.7.4
		 * @see WP_Widget::update()
		 * @param array   $new_instance Values just sent to be saved.
		 * @param array   $old_instance Previously saved values from database.
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance          = array();
			$instance['block'] = ! empty( $new_instance['block'] ) ? $new_instance['block'] : 0;
			
			if ( isset ( $new_instance['title'] ) ) {
				$instance['title'] = strip_tags( $new_instance['title'] );
			}

			return $instance;
		}


		/**
		 * Back-end widget form.
		 *
		 * @since 2.7.4
		 * @see WP_Widget::form()
		 * @param array   $instance Previously saved values from database.
		 */
		public function form( $instance ) {
			$block_id = ! empty( $instance['block'] ) ? absint( $instance['block'] ) : 0;

			if ( isset( $instance['title'] ) ) {
				$title = $instance['title'];
			}


			$posts = get_posts(
				array(
					'post_type'   => 'wp_block',
					'post_status' => 'publish',
					'numberposts' => -1,
				)
			);

			// No blocks found.
			if ( empty( $posts ) ) {
				printf( '<p>%s</p>', __( 'No reusable blocks available.', 'megamenu' ) );

				return;
			}

			// Input field with id is required for WordPress to display the title in the widget header.
			?>
				<p>
					<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'megamenu' ); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
				</p>
		
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'block' ) ); ?>"><?php esc_attr_e( 'Block', 'megamenu' ); ?>:</label> 
				<select id="<?php echo esc_attr( $this->get_field_id( 'block' ) ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'block' ) ); ?>">
					<option value=""><?php esc_html_e( '- Select -', 'megamenu' ); ?></option>
					<?php foreach ( $posts as $post ) : ?>
					<option value="<?php echo esc_attr( $post->ID ); ?>"<?php selected( $post->ID, $block_id ); ?>><?php echo esc_html( $post->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<?php
		}

	}

endif;
