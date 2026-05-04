<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Widget' ) ) :

	/**
	 * WordPress widget that outputs a registered menu location using wp_nav_menu.
	 *
	 * @since   1.7.4
	 * @package MegaMenu
	 */
	class Mega_Menu_Widget extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 *
		 * @since 1.7.4
		 */
		public function __construct() {
			parent::__construct(
				'maxmegamenu', // Base ID
				__( 'Max Mega Menu', 'megamenu' ), // Name
				[ 'description' => __( 'Outputs a menu for a selected theme location.', 'megamenu' ) ] // Args
			);
		}

		/**
		 * Front-end display of widget.
		 *
		 * @since 1.7.4
		 * @see WP_Widget::widget()
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 * @return void
		 */
		public function widget( $args, $instance ) {
			if ( ! is_array( $args ) ) {
				$args = [
					'before_widget' => '',
					'after_widget'  => '',
				];
			}

			extract( $args );

			if ( isset( $instance['location'] ) ) {
				$location = $instance['location'];

				$title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : "";

				echo $before_widget;

				if ( ! empty( $title ) ) {
					echo $before_title . $title . $after_title;
				}

				if ( has_nav_menu( $location ) ) {
					 wp_nav_menu( [ 'theme_location' => $location ] );
				}

				echo $after_widget;
			}
		}

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @since 1.7.4
		 * @see WP_Widget::update()
		 * @param array   $new_instance Values just sent to be saved.
		 * @param array   $old_instance Previously saved values from database.
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance             = [];
			$instance['location'] = strip_tags( $new_instance['location'] );
			$instance['title']    = strip_tags( $new_instance['title'] );

			return $instance;
		}

		/**
		 * Back-end widget form.
		 *
		 * @since 1.7.4
		 * @see WP_Widget::form()
		 * @param array $instance Previously saved values from database.
		 * @return void
		 */
		public function form( $instance ) {

			$selected_location = 0;
			$title             = '';
			$locations         = get_registered_nav_menus();

			if ( isset( $instance['location'] ) ) {
				$selected_location = $instance['location'];
			}

			if ( isset( $instance['title'] ) ) {
				$title = $instance['title'];
			}

			?>
		<p>
			<?php if ( $locations ) { ?>
				<p>
					<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'megamenu' ); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
				</p>
				<label for="<?php echo $this->get_field_id( 'location' ); ?>"><?php _e( 'Menu Location:', 'megamenu' ); ?></label>

				<select id="<?php echo $this->get_field_id( 'location' ); ?>" name="<?php echo $this->get_field_name( 'location' ); ?>">
					<?php
					if ( $selected_location === 0 ) {
						echo "<option selected='true' disabled='disabled'>" . __( 'Select location', 'megamenu' ) . '</option>';
					}
					?>

					<?php

						$enabled_locations  = [];
						$disabled_locations = [];

					foreach ( $locations as $location => $description ) {
						if ( max_mega_menu_is_enabled( $location ) ) {
							$enabled_locations[ $location ] = $description;
						} else {
							$disabled_locations[ $location ] = $description;
						}
					}

					if ( count( $enabled_locations ) ) {
						echo "<optgroup label='&#10003; " . __( 'Active locations', 'megamenu' ) . "'>";

						foreach ( $enabled_locations as $location => $description ) {
							echo "<option value='{$location}'" . selected( $location, $selected_location ) . ">{$description}</option>";
						}

						echo '</optgroup>';
					}

					if ( count( $disabled_locations ) ) {
						echo "<optgroup label='&#x2718; " . __( 'Inactive locations', 'megamenu' ) . "'>";

						foreach ( $disabled_locations as $location => $description ) {
							echo "<option value='{$location}'" . selected( $location, $selected_location ) . ">{$description}</option>";
						}

						echo '</optgroup>';
					}
					?>
				</select>
				<?php
			} else {
				_e( 'No menu locations found', 'megamenu' );
			}
			?>
		</p>
			<?php
		}
	}

endif;
