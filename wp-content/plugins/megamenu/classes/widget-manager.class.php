<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Widget_Manager' ) ) :

	/**
	 * Processes AJAX requests from the Mega Menu panel editor and manages
	 * widget instances within the Mega Menu sidebar area.
	 *
	 * @since   1.0
	 * @package MegaMenu
	 */
	class Mega_Menu_Widget_Manager {

		/**
		 * Constructor. Registers AJAX actions and widget-persistence filters.
		 *
		 * @since 1.0
		 */
		public function __construct() {

			add_action( 'wp_ajax_megamenu_edit_widget', [ $this, 'ajax_show_widget_form' ] );
			add_action( 'wp_ajax_megamenu_edit_menu_item', [ $this, 'ajax_show_menu_item_form' ] );
			add_action( 'wp_ajax_megamenu_save_widget', [ $this, 'ajax_save_widget' ] );
			add_action( 'wp_ajax_megamenu_save_menu_item', [ $this, 'ajax_save_menu_item' ] );
			add_action( 'wp_ajax_megamenu_update_widget_columns', [ $this, 'ajax_update_widget_columns' ] );
			add_action( 'wp_ajax_megamenu_update_menu_item_columns', [ $this, 'ajax_update_menu_item_columns' ] );
			add_action( 'wp_ajax_megamenu_delete_widget', [ $this, 'ajax_delete_widget' ] );
			add_action( 'wp_ajax_megamenu_add_widget', [ $this, 'ajax_add_widget' ] );
			add_action( 'wp_ajax_megamenu_reorder_items', [ $this, 'ajax_reorder_items' ] );
			add_action( 'wp_ajax_megamenu_save_grid_data', [ $this, 'ajax_save_grid_data' ] );

			add_filter( 'widget_update_callback', [ $this, 'persist_mega_menu_widget_settings' ], 10, 4 );

			add_action( 'megamenu_after_widget_add', [ $this, 'clear_caches' ] );
			add_action( 'megamenu_after_widget_save', [ $this, 'clear_caches' ] );
			add_action( 'megamenu_after_widget_delete', [ $this, 'clear_caches' ] );

		}


		/**
		 * Ensures Mega Menu widget meta is preserved when a widget is saved.
		 *
		 * Some widgets do not base new settings on a copy of the old settings, which
		 * would lose the mega menu data. This filter restores any missing meta keys.
		 *
		 * @since 1.0
		 * @param array  $instance     The current widget instance settings.
		 * @param array  $new_instance The new settings submitted by the user.
		 * @param array  $old_instance The previous settings.
		 * @param object $that         The widget object.
		 * @return array Widget instance with mega menu meta preserved.
		 */
		public function persist_mega_menu_widget_settings( $instance, $new_instance, $old_instance, $that ) {

			if ( isset( $old_instance['mega_menu_columns'] ) && ! isset( $new_instance['mega_menu_columns'] ) ) {
				$instance['mega_menu_columns'] = $old_instance['mega_menu_columns'];
			}

			if ( isset( $old_instance['mega_menu_order'] ) && ! isset( $new_instance['mega_menu_order'] ) ) {
				$instance['mega_menu_order'] = $old_instance['mega_menu_order'];
			}

			if ( isset( $old_instance['mega_menu_parent_menu_id'] ) && ! isset( $new_instance['mega_menu_parent_menu_id'] ) ) {
				$instance['mega_menu_parent_menu_id'] = $old_instance['mega_menu_parent_menu_id'];
			}

			return $instance;
		}


		/**
		 * Display a widget settings form via AJAX.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function ajax_show_widget_form() {

			check_ajax_referer( 'megamenu_edit' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$widget_id = sanitize_text_field( $_POST['widget_id'] );

			if ( ob_get_contents() ) {
				ob_clean(); // remove any warnings or output from other plugins which may corrupt the response
			}

			wp_die( trim( $this->show_widget_form( $widget_id ) ) );

		}

		/**
		 * Display a menu item settings form via AJAX.
		 *
		 * @since 2.7
		 * @return void
		 */
		public function ajax_show_menu_item_form() {

			check_ajax_referer( 'megamenu_edit' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$menu_item_id = sanitize_text_field( $_POST['widget_id'] );

			$nonce = wp_create_nonce( 'megamenu_save_menu_item_' . $menu_item_id );

			$saved_settings = array_filter( (array) get_post_meta( $menu_item_id, '_megamenu', true ) );
			$menu_item_meta = array_merge( Mega_Menu_Nav_Menus::get_menu_item_defaults(), $saved_settings );

			if ( ob_get_contents() ) {
				ob_clean(); // remove any warnings or output from other plugins which may corrupt the response
			}

			$dialog_title = __( 'Sub menu item', 'megamenu' );
			$post         = get_post( (int) $menu_item_id );
			if ( $post && 'nav_menu_item' === $post->post_type ) {
				$menu_item_obj = wp_setup_nav_menu_item( $post );
				if ( $menu_item_obj && ! empty( $menu_item_obj->title ) ) {
					$dialog_title = $menu_item_obj->title;
				}
			}
			?>

		<form method='post'>
			<input type='hidden' name='action' value='megamenu_save_menu_item' />
			<input type='hidden' name='menu_item_id' value='<?php echo esc_attr( $menu_item_id ); ?>' />
			<input type='hidden' name='_wpnonce'  value='<?php echo esc_attr( $nonce ); ?>' />
			<div class='mega-widget-dialog-header'>
				<h2 class='mega-widget-dialog-title'><?php echo esc_html( $dialog_title ); ?></h2>
				<button type='button' class='mega-widget-dialog-close' aria-label='<?php echo esc_attr__( 'Close', 'megamenu' ); ?>'>
					<span class='dashicons dashicons-no-alt' aria-hidden='true'></span>
				</button>
			</div>
			<div class='mega-widget-content widget-content'>
				<p>
					<label><?php _e( 'Sub menu columns', 'megamenu' ); ?></label>

					<select name="settings[submenu_columns]">
						<option value='1' <?php selected( $menu_item_meta['submenu_columns'], 1, true ); ?> >1 <?php __( 'column', 'megamenu' ); ?></option>
						<option value='2' <?php selected( $menu_item_meta['submenu_columns'], 2, true ); ?> >2 <?php __( 'columns', 'megamenu' ); ?></option>
						<option value='3' <?php selected( $menu_item_meta['submenu_columns'], 3, true ); ?> >3 <?php __( 'columns', 'megamenu' ); ?></option>
						<option value='4' <?php selected( $menu_item_meta['submenu_columns'], 4, true ); ?> >4 <?php __( 'columns', 'megamenu' ); ?></option>
						<option value='5' <?php selected( $menu_item_meta['submenu_columns'], 5, true ); ?> >5 <?php __( 'columns', 'megamenu' ); ?></option>
						<option value='6' <?php selected( $menu_item_meta['submenu_columns'], 6, true ); ?> >6 <?php __( 'columns', 'megamenu' ); ?></option>
					</select>
				</p>
			</div>
			<div class='mega-widget-form-footer widget-control-actions'>
				<div class='mega-widget-controls widget-controls'>
					<a class='mega-delete' href='#delete' data-mega-tooltip='<?php echo esc_attr__( 'Delete', 'megamenu' ); ?>' data-mega-tooltip-position='right' aria-label='<?php echo esc_attr__( 'Delete', 'megamenu' ); ?>'>
						<span class='dashicons dashicons-trash' aria-hidden='true'></span>
					</a>
				</div>
				<button type="submit" name="savewidget" id="savewidget" class="button button-primary button-compact"><?php echo esc_html__( 'Save', 'megamenu' ); ?></button>
			</div>
		</form>

			<?php

		}

		/**
		 * Save a menu item's settings via AJAX.
		 *
		 * @since 2.7
		 * @return void
		 */
		public function ajax_save_menu_item() {

			$menu_item_id = absint( sanitize_text_field( $_POST['menu_item_id'] ) );

			check_ajax_referer( 'megamenu_save_menu_item_' . $menu_item_id );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$submitted_settings = isset( $_POST['settings'] ) ? $_POST['settings'] : [];

			if ( $menu_item_id > 0 && is_array( $submitted_settings ) ) {

				$existing_settings = get_post_meta( $menu_item_id, '_megamenu', true );

				if ( is_array( $existing_settings ) ) {
					$submitted_settings = array_merge( $existing_settings, $submitted_settings );
				}

				update_post_meta( $menu_item_id, '_megamenu', $submitted_settings );
			}

			$this->send_json_success( sprintf( __( 'Saved %s', 'megamenu' ), $id_base ) );

		}


		/**
		 * Save a widget via AJAX.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function ajax_save_widget() {

			$widget_id = sanitize_text_field( $_POST['widget_id'] );
			$id_base   = sanitize_text_field( $_POST['id_base'] );

			check_ajax_referer( 'megamenu_save_widget_' . $widget_id );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$saved = $this->save_widget( $id_base );

			if ( $saved ) {
				$this->send_json_success( sprintf( __( 'Saved %s', 'megamenu' ), $id_base ) );
			} else {
				$this->send_json_error( sprintf( __( 'Failed to save %s', 'megamenu' ), $id_base ) );
			}

		}


		/**
		 * Update the number of mega columns for a widget via AJAX.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function ajax_update_widget_columns() {

			check_ajax_referer( 'megamenu_edit' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$widget_id = sanitize_text_field( $_POST['id'] );
			$columns   = absint( $_POST['columns'] );

			$updated = $this->update_widget_columns( $widget_id, $columns );

			if ( $updated ) {
				$this->send_json_success( sprintf( __( 'Updated %1$s (new columns: %2$d)', 'megamenu' ), $widget_id, $columns ) );
			} else {
				$this->send_json_error( sprintf( __( 'Failed to update %s', 'megamenu' ), $widget_id ) );
			}

		}


		/**
		 * Update the number of mega columns for a menu item via AJAX.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function ajax_update_menu_item_columns() {

			check_ajax_referer( 'megamenu_edit' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$id      = absint( $_POST['id'] );
			$columns = absint( $_POST['columns'] );

			$updated = $this->update_menu_item_columns( $id, $columns );

			if ( $updated ) {
				$this->send_json_success( sprintf( __( 'Updated %1$s (new columns: %2$d)', 'megamenu' ), $id, $columns ) );
			} else {
				$this->send_json_error( sprintf( __( 'Failed to update %s', 'megamenu' ), $id ) );
			}

		}


		/**
		 * Add a widget to the mega menu panel via AJAX.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function ajax_add_widget() {

			check_ajax_referer( 'megamenu_edit' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$id_base        = sanitize_text_field( $_POST['id_base'] );
			$menu_item_id   = absint( $_POST['menu_item_id'] );
			$title          = sanitize_text_field( $_POST['title'] );
			$is_grid_widget = isset( $_POST['is_grid_widget'] ) && $_POST['is_grid_widget'] == 'true';

			$added = $this->add_widget( $id_base, $menu_item_id, $title, $is_grid_widget );

			if ( $added ) {
				$this->send_json_success( $added );
			} else {
				$this->send_json_error( sprintf( __( 'Failed to add %1$s to %2$d', 'megamenu' ), $id_base, $menu_item_id ) );
			}

		}


		/**
		 * Delete a widget via AJAX.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function ajax_delete_widget() {

			check_ajax_referer( 'megamenu_edit' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$widget_id = sanitize_text_field( $_POST['widget_id'] );

			$deleted = $this->delete_widget( $widget_id );

			if ( $deleted ) {
				$this->send_json_success( sprintf( __( 'Deleted %s', 'megamenu' ), $widget_id ) );
			} else {
				$this->send_json_error( sprintf( __( 'Failed to delete %s', 'megamenu' ), $widget_id ) );
			}

		}


		/**
		 * Reorder widgets/menu items via AJAX.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function ajax_reorder_items() {

			check_ajax_referer( 'megamenu_edit' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$items = isset( $_POST['items'] ) ? $_POST['items'] : false;

			$saved = false;

			if ( $items ) {
				$moved = $this->reorder_items( $items );
			}

			if ( $moved ) {
				$this->send_json_success( sprintf( __( 'Moved (%s)', 'megamenu' ), json_encode( $items ) ) );
			} else {
				$this->send_json_error( sprintf( __( "Didn't move items", 'megamenu' ), json_encode( $items ) ) );
			}

		}


		/**
		 * Save the grid layout data for a mega menu via AJAX.
		 *
		 * @since 2.4
		 * @return void
		 */
		public function ajax_save_grid_data() {

			check_ajax_referer( 'megamenu_edit' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$grid = isset( $_POST['grid'] ) ? $_POST['grid'] : false;
			$parent_menu_item_id = absint( $_POST['parent_menu_item'] );
			$saved = false;

			if ( is_array( $grid ) && get_post_type( $parent_menu_item_id ) == 'nav_menu_item' ) {
				$existing_settings = get_post_meta( $parent_menu_item_id, '_megamenu', true );
				$submitted_settings = array_merge( $existing_settings, [ 'grid' => $grid ] );
				update_post_meta( $parent_menu_item_id, '_megamenu', $submitted_settings );
				$saved = true;
			}

			if ( $saved ) {
				$this->send_json_success( sprintf( __( 'Saved (%s)', 'megamenu' ), json_encode( $grid ) ) );
			} else {
				$this->send_json_error( sprintf( __( "Didn't save", 'megamenu' ), json_encode( $grid ) ) );
			}

		}


		/**
		 * Returns all widgets registered in WordPress that are compatible with Mega Menu.
		 *
		 * @since 1.0
		 * @return array List of widgets, each with 'text' (name) and 'value' (id_base) keys.
		 */
		public function get_available_widgets() {
			global $wp_widget_factory;

			$widgets = [];

			foreach ( $wp_widget_factory->widgets as $widget ) {

				$disabled_widgets = [ 'maxmegamenu' ];

				$disabled_widgets = apply_filters( 'megamenu_incompatible_widgets', $disabled_widgets );

				if ( ! in_array( $widget->id_base, $disabled_widgets ) ) {

					$widgets[] = [
						'text'  => $widget->name,
						'value' => $widget->id_base,
					];

				}
			}

			uasort( $widgets, [ $this, 'sort_by_text' ] );

			return $widgets;

		}


		/**
		 * Comparison callback for sorting a 2D array by the 'text' key.
		 *
		 * @since 1.2
		 * @param array $a First widget array.
		 * @param array $b Second widget array.
		 * @return int Negative, zero, or positive for usort ordering.
		 */
		public function sort_by_text( $a, $b ) {
			return strcmp( $a['text'], $b['text'] );
		}


		/**
		 * Comparison callback for sorting a 2D array by the 'order' key.
		 *
		 * @since 2.0
		 * @param array $a First item array.
		 * @param array $b Second item array.
		 * @return int -1 if $a < $b, 1 otherwise.
		 */
		public function sort_by_order( $a, $b ) {
			if ( $a['order'] == $b['order'] ) {
				return 1;
			}

			return ( $a['order'] < $b['order'] ) ? -1 : 1;
		}


		/**
		 * Returns an array of direct child menu items for the given parent menu item.
		 *
		 * @since 1.5
		 * @param int          $parent_menu_item_id Parent menu item ID.
		 * @param int          $menu_id             Menu term ID.
		 * @param array|false  $menu_items          Pre-fetched menu items, or false to fetch fresh.
		 * @return array Map of menu item ID to item data arrays.
		 */
		private function get_second_level_menu_items( $parent_menu_item_id, $menu_id, $menu_items = false ) {

			$second_level_items = [];

			// check we're using a valid menu ID
			if ( ! is_nav_menu( $menu_id ) ) {
				return $second_level_items;
			}

			if ( ! $menu_items ) {
				$menu_items = wp_get_nav_menu_items( $menu_id );
			}

			if ( count( $menu_items ) ) {

				foreach ( $menu_items as $item ) {

					// find the child menu items
					if ( $item->menu_item_parent == $parent_menu_item_id ) {

						$saved_settings = array_filter( (array) get_post_meta( $item->ID, '_megamenu', true ) );

						$settings = array_merge( Mega_Menu_Nav_Menus::get_menu_item_defaults(), $saved_settings );

						$second_level_items[ $item->ID ] = [
							'id'      => $item->ID,
							'type'    => 'menu_item',
							'title'   => $item->title,
							'columns' => $settings['mega_menu_columns'],
							'order'   => isset( $settings['mega_menu_order'][ $parent_menu_item_id ] ) ? $settings['mega_menu_order'][ $parent_menu_item_id ] : 0,
						];

					}
				}
			}

			return $second_level_items;
		}

		/**
		 * Returns all widgets assigned to the specified parent menu item.
		 *
		 * @since 1.0
		 * @param int $parent_menu_item_id Parent menu item ID.
		 * @param int $menu_id             Menu term ID.
		 * @return array Map of widget ID to widget data arrays.
		 */
		public function get_widgets_for_menu_id( $parent_menu_item_id, $menu_id ) {

			$widgets = [];

			if ( $mega_menu_widgets = $this->get_mega_menu_sidebar_widgets() ) {

				foreach ( $mega_menu_widgets as $widget_id ) {

					$settings = $this->get_settings_for_widget_id( $widget_id );

					if ( ! isset( $settings['mega_menu_is_grid_widget'] ) && isset( $settings['mega_menu_parent_menu_id'] ) && $settings['mega_menu_parent_menu_id'] == $parent_menu_item_id ) {

						$name = $this->get_name_for_widget_id( $widget_id );

						$widgets[ $widget_id ] = [
							'id'      => $widget_id,
							'type'    => 'widget',
							'title'   => $name,
							'columns' => $settings['mega_menu_columns'],
							'order'   => isset( $settings['mega_menu_order'][ $parent_menu_item_id ] ) ? $settings['mega_menu_order'][ $parent_menu_item_id ] : 0,
						];

					}
				}
			}

			return $widgets;

		}


		/**
		 * Returns all widgets and second-level menu items for the given parent menu item.
		 * Used to populate the standard mega menu builder.
		 *
		 * @since 2.0
		 * @param int $parent_menu_item_id Parent menu item ID.
		 * @param int $menu_id             Menu term ID.
		 * @return array Combined, ordered list of menu item and widget data arrays.
		 */
		public function get_widgets_and_menu_items_for_menu_id( $parent_menu_item_id, $menu_id ) {

			$menu_items = $this->get_second_level_menu_items( $parent_menu_item_id, $menu_id );

			$widgets = $this->get_widgets_for_menu_id( $parent_menu_item_id, $menu_id );

			$items = array_merge( $menu_items, $widgets );

			$parent_settings = get_post_meta( $parent_menu_item_id, '_megamenu', true );

			$ordering = isset( $parent_settings['submenu_ordering'] ) ? $parent_settings['submenu_ordering'] : 'natural';

			if ( $ordering == 'forced' ) {

				uasort( $items, [ $this, 'sort_by_order' ] );

				$new_items = $items;
				$end_items = [];

				foreach ( $items as $key => $value ) {
					if ( $value['order'] == 0 ) {
						unset( $new_items[ $key ] );
						$end_items[] = $value;
					}
				}

				$items = array_merge( $new_items, $end_items );

			}

			return $items;
		}

		/**
		 * Return a sorted array of grid data representing rows, columns, and items.
		 *
		 * @since 2.4
		 * @param int          $parent_menu_item_id Parent menu item ID.
		 * @param int          $menu_id             Menu term ID.
		 * @param array|false  $menu_items          Pre-fetched menu items, or false to fetch fresh.
		 * @return array Nested array of rows, columns, and their widget/menu-item contents.
		 */
		public function get_grid_widgets_and_menu_items_for_menu_id( $parent_menu_item_id, $menu_id, $menu_items = false ) {

			$meta = get_post_meta( $parent_menu_item_id, '_megamenu', true );

			$saved_grid = [];

			if ( isset( $meta['grid'] ) ) {
				$saved_grid = $this->populate_saved_grid_data( $parent_menu_item_id, $menu_id, $meta['grid'], $menu_items );
			} else {
				// return empty row
				$saved_grid[0]['columns'][0]['meta']['span'] = 3;
				$saved_grid                                  = $this->populate_saved_grid_data( $parent_menu_item_id, $menu_id, $saved_grid, $menu_items );

			}

			return $saved_grid;
		}


		/**
		 * Validate and populate saved grid data, removing stale widgets/items and adding new orphaned items.
		 *
		 * Ensures widgets within the grid data still exist and that second-level menu items
		 * are still actually children of the parent within the menu structure.
		 *
		 * @since 2.4
		 * @param int          $parent_menu_item_id Parent menu item ID.
		 * @param int          $menu_id             Menu term ID.
		 * @param array        $saved_grid          Array of saved grid rows, columns, and items.
		 * @param array|false  $menu_items          Pre-fetched menu items, or false to fetch fresh.
		 * @return array Validated and populated grid data array.
		 */
		public function populate_saved_grid_data( $parent_menu_item_id, $menu_id, $saved_grid, $menu_items ) {

			$second_level_menu_items = $this->get_second_level_menu_items( $parent_menu_item_id, $menu_id, $menu_items );

			$menu_items_included = [];

			foreach ( $saved_grid as $row => $row_data ) {
				if ( isset( $row_data['columns'] ) ) {
					foreach ( $row_data['columns'] as $col => $col_data ) {
						if ( isset( $col_data['items'] ) ) {
							foreach ( $col_data['items'] as $key => $item ) {
								if ( $item['type'] == 'item' ) {
									$menu_items_included[] = $item['id'];
									$is_child_of_parent    = false;

									foreach ( $second_level_menu_items as $menu_item ) {
										if ( $menu_item['id'] == $item['id'] ) {
											$is_child_of_parent = true;
										}
									}

									if ( ! $is_child_of_parent ) {
										unset( $saved_grid[ $row ]['columns'][ $col ]['items'][ $key ] ); // menu item has been deleted or moved
									}
								} else {
									if ( ! $this->get_name_for_widget_id( $item['id'] ) ) {
										unset( $saved_grid[ $row ]['columns'][ $col ]['items'][ $key ] ); // widget no longer exists
									}
								}
							}
						}
					}
				}
			}

			// Find any second level menu items that have been added to the menu but are not yet within the grid data
			$orphaned_items = [];

			foreach ( $second_level_menu_items as $menu_item ) {
				if ( ! in_array( $menu_item['id'], $menu_items_included ) ) {
					$orphaned_items[] = $menu_item;
				}
			}

			if ( ! isset( $saved_grid[0]['columns'][0]['items'][0] ) ) {
				$index = 0; // grid is empty
			} else {
				$index = 999; // create new row
			}

			foreach ( $orphaned_items as $key => $menu_item ) {
				$saved_grid[ $index ]['columns'][0]['meta']['span']  = 3;
				$saved_grid[ $index ]['columns'][0]['items'][ $key ] = [
					'id'          => $menu_item['id'],
					'type'        => 'item',
					'title'       => $menu_item['title'],
					'description' => __( 'Menu Item', 'megamenu' ),
				];
			}

			if ( is_admin() ) {
				$saved_grid = $this->populate_grid_menu_item_titles( $saved_grid, $menu_id );
			}

			return $saved_grid;
		}


		/**
		 * Loop through the grid data and apply display titles/labels to each menu item and widget.
		 *
		 * @since 2.4
		 * @param array $saved_grid Grid data array.
		 * @param int   $menu_id    Menu term ID.
		 * @return array Grid data with title/description values populated.
		 */
		public function populate_grid_menu_item_titles( $saved_grid, $menu_id ) {

			$menu_items = wp_get_nav_menu_items( $menu_id );

			$menu_item_title_map = [];

			foreach ( $menu_items as $item ) {
				$menu_item_title_map[ $item->ID ] = $item->title;
			}

			foreach ( $saved_grid as $row => $row_data ) {
				if ( isset( $row_data['columns'] ) ) {
					foreach ( $row_data['columns'] as $col => $col_data ) {
						if ( isset( $col_data['items'] ) ) {
							foreach ( $col_data['items'] as $key => $item ) {
								if ( $item['type'] == 'item' ) {

									if ( isset( $menu_item_title_map[ $item['id'] ] ) ) {
										$title = $menu_item_title_map[ $item['id'] ];
									} else {
										$title = __( '(no label)' );
									}

									$saved_grid[ $row ]['columns'][ $col ]['items'][ $key ]['title']       = $title;
									$saved_grid[ $row ]['columns'][ $col ]['items'][ $key ]['description'] = __( 'Menu Item', 'megamenu' );
								} else {
									$saved_grid[ $row ]['columns'][ $col ]['items'][ $key ]['title']       = $this->get_title_for_widget_id( $item['id'] );
									$saved_grid[ $row ]['columns'][ $col ]['items'][ $key ]['description'] = $this->get_name_for_widget_id( $item['id'] );
								}
							}
						}
					}
				}
			}

			return $saved_grid;
		}


		/**
		 * Returns the saved settings for a widget instance from the options table.
		 *
		 * @since 1.8.1
		 * @param string $widget_id Widget ID (e.g. 'meta-3').
		 * @return array|false Widget settings array, or false if not found.
		 */
		public function get_settings_for_widget_id( $widget_id ) {

			$id_base = $this->get_id_base_for_widget_id( $widget_id );

			if ( ! $id_base ) {
				return false;
			}

			$widget_number = $this->get_widget_number_for_widget_id( $widget_id );

			$current_widgets = get_option( 'widget_' . $id_base );

			return $current_widgets[ $widget_number ];

		}

		/**
		 * Returns the numeric portion of a widget ID.
		 *
		 * @since 1.0
		 * @param string $widget_id Widget ID in id_base-number format (e.g. 'meta-3').
		 * @return int The widget number.
		 */
		public function get_widget_number_for_widget_id( $widget_id ) {

			$parts = explode( '-', $widget_id );

			return absint( end( $parts ) );

		}

		/**
		 * Returns the registered name of a widget type (e.g. "Custom HTML" or "Text").
		 *
		 * @since 1.0
		 * @param string $widget_id Widget ID in id_base-number format (e.g. 'meta-3').
		 * @return string|false Widget type name, or false if not registered.
		 */
		public function get_name_for_widget_id( $widget_id ) {
			global $wp_registered_widgets;

			if ( ! isset( $wp_registered_widgets[ $widget_id ] ) ) {
				return false;
			}

			$registered_widget = $wp_registered_widgets[ $widget_id ];

			return $registered_widget['name'];

		}


		/**
		 * Returns the display title of a widget instance (falling back to the widget type name).
		 *
		 * @since 2.4
		 * @param string $widget_id Widget ID in id_base-number format (e.g. 'meta-3').
		 * @return string|false Widget title or type name, or false if not found.
		 */
		public function get_title_for_widget_id( $widget_id ) {
			$instance = $this->get_settings_for_widget_id( $widget_id );

			if ( isset( $instance['title'] ) && strlen( $instance['title'] ) ) {
				return $instance['title'];
			}

			return $this->get_name_for_widget_id( $widget_id );

		}

		/**
		 * Returns the id_base value for a given widget ID.
		 *
		 * @since 1.0
		 * @param string $widget_id Widget ID in id_base-number format (e.g. 'meta-3').
		 * @return string|false The id_base string, or false if not found.
		 */
		public function get_id_base_for_widget_id( $widget_id ) {
			global $wp_registered_widget_controls;

			if ( ! isset( $wp_registered_widget_controls[ $widget_id ] ) ) {
				return false;
			}

			$control = $wp_registered_widget_controls[ $widget_id ];

			$id_base = isset( $control['id_base'] ) ? $control['id_base'] : $control['id'];

			return $id_base;

		}

		/**
		 * Returns the rendered HTML for a single widget instance.
		 *
		 * @since 1.0
		 * @param string $id Widget ID (e.g. 'meta-3').
		 * @return string|null Rendered widget HTML, or null if not callable.
		 */
		public function show_widget( $id ) {
			global $wp_registered_widgets;

			$params = array_merge(
				[
					array_merge(
						[
							'widget_id'   => $id,
							'widget_name' => $wp_registered_widgets[ $id ]['name'],
						]
					),
				],
				(array) $wp_registered_widgets[ $id ]['params']
			);

			$params[0]['id']            = 'mega-menu';
			$params[0]['before_title']  = apply_filters( 'megamenu_before_widget_title', '<h4 class="mega-block-title">', $wp_registered_widgets[ $id ] );
			$params[0]['after_title']   = apply_filters( 'megamenu_after_widget_title', '</h4>', $wp_registered_widgets[ $id ] );
			$params[0]['before_widget'] = apply_filters( 'megamenu_before_widget', '', $wp_registered_widgets[ $id ] );
			$params[0]['after_widget']  = apply_filters( 'megamenu_after_widget', '', $wp_registered_widgets[ $id ] );

			if ( defined( 'MEGAMENU_DYNAMIC_SIDEBAR_PARAMS' ) && MEGAMENU_DYNAMIC_SIDEBAR_PARAMS ) {
				$params[0]['before_widget'] = apply_filters( 'megamenu_before_widget', '<div id="" class="">', $wp_registered_widgets[ $id ] );
				$params[0]['after_widget']  = apply_filters( 'megamenu_after_widget', '</div>', $wp_registered_widgets[ $id ] );

				$params = apply_filters( 'dynamic_sidebar_params', $params );
			}

			$callback = $wp_registered_widgets[ $id ]['callback'];

			if ( is_callable( $callback ) ) {
				ob_start();
				call_user_func_array( $callback, $params );
				return ob_get_clean();
			}

		}


		/**
		 * Returns the CSS class name for a widget instance.
		 *
		 * @since 1.8.1
		 * @param string $id Widget ID (e.g. 'meta-3').
		 * @return string Widget classname, or empty string if not found.
		 */
		public function get_widget_class( $id ) {
			global $wp_registered_widgets;

			if ( isset( $wp_registered_widgets[ $id ]['classname'] ) ) {
				return $wp_registered_widgets[ $id ]['classname'];
			}

			return '';
		}


		/**
		 * Outputs the widget edit form HTML for the specified widget.
		 *
		 * @since 1.0
		 * @param string $widget_id Widget ID in id_base-number format (e.g. 'meta-3').
		 * @return void
		 */
		public function show_widget_form( $widget_id ) {
			global $wp_registered_widget_controls, $wp_registered_widgets;

			$control = $wp_registered_widget_controls[ $widget_id ];

			$id_base = $this->get_id_base_for_widget_id( $widget_id );

			$widget_number = $this->get_widget_number_for_widget_id( $widget_id );

			$nonce = wp_create_nonce( 'megamenu_save_widget_' . $widget_id );

			$dialog_title = isset( $wp_registered_widgets[ $widget_id ]['name'] )
				? $wp_registered_widgets[ $widget_id ]['name']
				: $id_base;

			?>

		<form method='post'>
			<input type="hidden" name="widget-id" class="mega-widget-id widget-id" value="<?php echo esc_attr( $widget_id ); ?>" />
			<input type='hidden' name='action'    value='megamenu_save_widget' />
			<input type='hidden' name='id_base'   class="id_base" value='<?php echo esc_attr( $id_base ); ?>' />
			<input type='hidden' name='widget_id' value='<?php echo esc_attr( $widget_id ); ?>' />
			<input type='hidden' name='_wpnonce'  value='<?php echo esc_attr( $nonce ); ?>' />
			<div class='mega-widget-dialog-header'>
				<h2 class='mega-widget-dialog-title'><?php echo esc_html( $dialog_title ); ?></h2>
				<button type='button' class='mega-widget-dialog-close' aria-label='<?php echo esc_attr__( 'Close', 'megamenu' ); ?>'>
					<span class='dashicons dashicons-no-alt' aria-hidden='true'></span>
				</button>
			</div>
			<div class='mega-widget-content widget-content'>
				<?php
				if ( is_callable( $control['callback'] ) ) {
					call_user_func_array( $control['callback'], $control['params'] );
				}
				?>
			</div>
			<div class='mega-widget-form-footer widget-control-actions'>
				<div class='mega-widget-controls widget-controls'>
					<a class='mega-delete' href='#delete' data-mega-tooltip='<?php echo esc_attr__( 'Delete', 'megamenu' ); ?>' data-mega-tooltip-position='right' aria-label='<?php echo esc_attr__( 'Delete', 'megamenu' ); ?>'>
						<span class='dashicons dashicons-trash' aria-hidden='true'></span>
					</a>
				</div>
				<button type="submit" name="savewidget" id="savewidget" class="button button-primary button-compact"><?php echo esc_html__( 'Save', 'megamenu' ); ?></button>
			</div>
		</form>
		

			<?php
		}


		/**
		 * Saves a widget. Calls the update callback on the widget.
		 * The callback inspects the post values and updates all widget instances which match the base ID.
		 *
		 * @since 1.0
		 * @param string $id_base - e.g. 'meta'
		 * @return bool
		 */
		public function save_widget( $id_base ) {
			global $wp_registered_widget_updates;

			$control = $wp_registered_widget_updates[ $id_base ];

			if ( is_callable( $control['callback'] ) ) {

				call_user_func_array( $control['callback'], $control['params'] );

				do_action( 'megamenu_after_widget_save' );

				return true;
			}

			return false;

		}


		/**
		 * Adds a widget to WordPress. Creates a new widget instance and adds it to the sidebar.
		 *
		 * @since 1.0
		 * @param string $id_base        Widget id_base (e.g. 'text').
		 * @param int    $menu_item_id   The parent menu item ID for the widget.
		 * @param string $title          Display title for the widget in the editor.
		 * @param bool   $is_grid_widget Whether this is a grid-layout widget.
		 * @return string The widget element HTML for the mega menu editor.
		 */
		public function add_widget( $id_base, $menu_item_id, $title, $is_grid_widget ) {

			require_once( ABSPATH . 'wp-admin/includes/widgets.php' );

			$next_id = next_widget_id_number( $id_base );

			$this->add_widget_instance( $id_base, $next_id, $menu_item_id, $is_grid_widget );

			$widget_id = $this->add_widget_to_sidebar( $id_base, $next_id );

			$return  = '<div class="mega-widget widget" data-columns="2" id="' . $widget_id . '" data-type="widget" data-id="' . $widget_id . '">';
			$return .= '    <div class="mega-widget-top widget-top">';
			$return .= '        <div class="mega-widget-title widget-title">';
			$return .= '            <h4>' . esc_html( $title ) . '</h4>';

			if ( $is_grid_widget ) {
				$return .= '            <span class="mega-widget-desc widget-desc">' . esc_html( $title ) . '</span>';
			}

			$return .= '        </div>';
			$return .= '        <div class="mega-widget-title-action widget-title-action">';

			if ( ! $is_grid_widget ) {
				$return .= '            <div class="mega-col-span">';
				$return .= '<button type="button" class="mega-widget-option mega-widget-contract widget-option widget-contract" aria-label="' . esc_attr__( 'Contract', 'megamenu' ) . '"></button>';
				$return .= '            <span class="mega-widget-cols widget-cols"><span class="mega-widget-num-cols widget-num-cols">2</span><span class="mega-widget-of widget-of">/</span><span class="mega-widget-total-cols widget-total-cols">X</span></span>';
				$return .= '<button type="button" class="mega-widget-option mega-widget-expand widget-option widget-expand" aria-label="' . esc_attr__( 'Expand', 'megamenu' ) . '"></button>';
				$return .= '            </div>';
				$return .= '            <a class="mega-widget-option mega-widget-action widget-option widget-action" title="' . esc_attr( __( 'Edit', 'megamenu' ) ) . '"></a>';
			} else {
				$return .= '            <a class="mega-widget-option mega-widget-action widget-option widget-action" title="' . esc_attr( __( 'Edit', 'megamenu' ) ) . '"></a>';
			}

			$return .= '        </div>';
			$return .= '    </div>';
			$return .= '    <div class="mega-widget-inner mega-widget-inside widget-inner widget-inside"></div>';
			$return .= '</div>';

			return $return;

		}


		/**
		 * Creates a new widget instance in the database.
		 *
		 * @since 1.0
		 * @param string $id_base        Widget id_base.
		 * @param int    $next_id        The new widget number.
		 * @param int    $menu_item_id   The parent menu item ID.
		 * @param bool   $is_grid_widget Whether this is a grid-layout widget.
		 * @return void
		 */
		private function add_widget_instance( $id_base, $next_id, $menu_item_id, $is_grid_widget ) {

			$current_widgets = get_option( 'widget_' . $id_base );

			$current_widgets[ $next_id ] = [
				'mega_menu_columns'        => 2,
				'mega_menu_parent_menu_id' => $menu_item_id,
			];

			if ( $is_grid_widget ) {
				$current_widgets[ $next_id ] = [
					'mega_menu_is_grid_widget' => 'true',
				];
			}

			update_option( 'widget_' . $id_base, $current_widgets );

		}

		/**
		 * Removes a widget instance from the database.
		 *
		 * @since 1.0
		 * @param string $widget_id Widget ID (e.g. 'meta-3').
		 * @return bool True if the widget was deleted, false if it was not found.
		 */
		private function remove_widget_instance( $widget_id ) {

			$id_base       = $this->get_id_base_for_widget_id( $widget_id );
			$widget_number = $this->get_widget_number_for_widget_id( $widget_id );

			// add blank widget
			$current_widgets = get_option( 'widget_' . $id_base );

			if ( isset( $current_widgets[ $widget_number ] ) ) {

				unset( $current_widgets[ $widget_number ] );

				update_option( 'widget_' . $id_base, $current_widgets );

				return true;

			}

			return false;

		}


		/**
		 * Updates the number of mega columns for a specified widget.
		 *
		 * @since 1.0
		 * @param string $widget_id Widget ID (e.g. 'text-3').
		 * @param int    $columns   New column span value.
		 * @return true
		 */
		public function update_widget_columns( $widget_id, $columns ) {

			$id_base = $this->get_id_base_for_widget_id( $widget_id );

			$widget_number = $this->get_widget_number_for_widget_id( $widget_id );

			$current_widgets = get_option( 'widget_' . $id_base );

			$current_widgets[ $widget_number ]['mega_menu_columns'] = absint( $columns );

			update_option( 'widget_' . $id_base, $current_widgets );

			do_action( 'megamenu_after_widget_save' );

			return true;

		}


		/**
		 * Updates the number of mega columns for a specified menu item.
		 *
		 * @since 1.10
		 * @param int $menu_item_id Menu item post ID.
		 * @param int $columns      New column span value.
		 * @return true
		 */
		public function update_menu_item_columns( $menu_item_id, $columns ) {

			$existing_settings = get_post_meta( $menu_item_id, '_megamenu', true );

			$submitted_settings = [
				'mega_menu_columns' => absint( $columns ),
			];

			if ( is_array( $existing_settings ) ) {
				$submitted_settings = array_merge( $existing_settings, $submitted_settings );
			}

			update_post_meta( $menu_item_id, '_megamenu', $submitted_settings );

			return true;

		}


		/**
		 * Updates the sort order of a specified widget within a parent menu item.
		 *
		 * @since 1.10
		 * @param string $widget_id           Widget ID (e.g. 'text-3').
		 * @param int    $order               New sort order value.
		 * @param int    $parent_menu_item_id Parent menu item ID the order applies to.
		 * @return true
		 */
		public function update_widget_order( $widget_id, $order, $parent_menu_item_id ) {

			$id_base = $this->get_id_base_for_widget_id( $widget_id );

			$widget_number = $this->get_widget_number_for_widget_id( $widget_id );

			$current_widgets = get_option( 'widget_' . $id_base );

			$current_widgets[ $widget_number ]['mega_menu_order'] = [ $parent_menu_item_id => absint( $order ) ];

			update_option( 'widget_' . $id_base, $current_widgets );

			return true;

		}


		/**
		 * Updates the sort order of a specified menu item within a parent menu item.
		 *
		 * @since 1.10
		 * @param int $menu_item_id         Menu item post ID.
		 * @param int $order                New sort order value.
		 * @param int $parent_menu_item_id  Parent menu item ID the order applies to.
		 * @return true
		 */
		public function update_menu_item_order( $menu_item_id, $order, $parent_menu_item_id ) {

			$submitted_settings['mega_menu_order'] = [ $parent_menu_item_id => absint( $order ) ];

			$existing_settings = get_post_meta( $menu_item_id, '_megamenu', true );

			if ( is_array( $existing_settings ) ) {

				$submitted_settings = array_merge( $existing_settings, $submitted_settings );

			}

			update_post_meta( $menu_item_id, '_megamenu', $submitted_settings );

			return true;

		}


		/**
		 * Deletes a widget from WordPress (removes from sidebar and from options table).
		 *
		 * @since 1.0
		 * @param string $widget_id Widget ID (e.g. 'meta-3').
		 * @return true
		 */
		public function delete_widget( $widget_id ) {

			$this->remove_widget_from_sidebar( $widget_id );
			$this->remove_widget_instance( $widget_id );

			do_action( 'megamenu_after_widget_delete' );

			return true;

		}


		/**
		 * Reorder an array of widgets and/or menu items by updating their sort order meta.
		 *
		 * @since 1.10
		 * @param array $items Array of item data, each with 'type', 'id', 'order', and 'parent_menu_item' keys.
		 * @return true
		 */
		public function reorder_items( $items ) {

			foreach ( $items as $item ) {

				if ( $item['parent_menu_item'] ) {

					$submitted_settings = [ 'submenu_ordering' => 'forced' ];

					$existing_settings = get_post_meta( $item['parent_menu_item'], '_megamenu', true );

					if ( is_array( $existing_settings ) ) {

						$submitted_settings = array_merge( $existing_settings, $submitted_settings );

					}

					update_post_meta( $item['parent_menu_item'], '_megamenu', $submitted_settings );
				}

				if ( $item['type'] == 'widget' ) {

					$this->update_widget_order( $item['id'], $item['order'], $item['parent_menu_item'] );

				}

				if ( $item['type'] == 'menu_item' ) {

					$this->update_menu_item_order( $item['id'], $item['order'], $item['parent_menu_item'] );

				}
			}

			return true;

		}


		/**
		 * Adds a widget to the Mega Menu widget sidebar.
		 *
		 * @since 1.0
		 * @param string $id_base  Widget id_base.
		 * @param int    $next_id  The new widget number.
		 * @return string The full widget ID (e.g. 'text-3').
		 */
		private function add_widget_to_sidebar( $id_base, $next_id ) {

			$widget_id = $id_base . '-' . $next_id;

			$sidebar_widgets = $this->get_mega_menu_sidebar_widgets();

			$sidebar_widgets[] = $widget_id;

			$this->set_mega_menu_sidebar_widgets( $sidebar_widgets );

			do_action( 'megamenu_after_widget_add' );

			return $widget_id;

		}


		/**
		 * Removes a widget from the Mega Menu widget sidebar.
		 *
		 * @since 1.0
		 * @param string $widget_id Widget ID to remove.
		 * @return string The widget ID that was removed.
		 */
		private function remove_widget_from_sidebar( $widget_id ) {

			$widgets = $this->get_mega_menu_sidebar_widgets();

			$new_mega_menu_widgets = [];

			foreach ( $widgets as $widget ) {

				if ( $widget != $widget_id ) {
					$new_mega_menu_widgets[] = $widget;
				}
			}

			$this->set_mega_menu_sidebar_widgets( $new_mega_menu_widgets );

			return $widget_id;

		}


		/**
		 * Returns an unfiltered array of all widgets in our sidebar
		 *
		 * @since 1.0
		 * @return array
		 */
		public function get_mega_menu_sidebar_widgets() {

			$sidebar_widgets = wp_get_sidebars_widgets();

			if ( ! isset( $sidebar_widgets['mega-menu'] ) ) {
				return false;
			}

			return $sidebar_widgets['mega-menu'];

		}


		/**
		 * Replaces the Mega Menu sidebar widget list.
		 *
		 * @since 1.0
		 * @param array $widgets Updated array of widget IDs.
		 * @return void
		 */
		private function set_mega_menu_sidebar_widgets( $widgets ) {

			$sidebar_widgets = wp_get_sidebars_widgets();

			$sidebar_widgets['mega-menu'] = $widgets;

			wp_set_sidebars_widgets( $sidebar_widgets );

		}


		/**
		 * Clear third-party widget output caches when the Mega Menu is updated.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function clear_caches() {

			// https://wordpress.org/plugins/widget-output-cache/
			if ( function_exists( 'menu_output_cache_bump' ) ) {
				menu_output_cache_bump();
			}

			// https://wordpress.org/plugins/widget-output-cache/
			if ( function_exists( 'widget_output_cache_bump' ) ) {
				widget_output_cache_bump();
			}

		}


		/**
		 * Send a JSON success response, clearing any prior output that could corrupt it.
		 *
		 * @since 1.8
		 * @param mixed $json Data to encode and send.
		 * @return void
		 */
		public function send_json_success( $json ) {
			if ( ob_get_contents() ) {
				ob_clean();
			}

			wp_send_json_success( $json );
		}


		/**
		 * Send a JSON error response, clearing any prior output that could corrupt it.
		 *
		 * @since 1.8
		 * @param mixed $json Data to encode and send.
		 * @return void
		 */
		public function send_json_error( $json ) {
			if ( ob_get_contents() ) {
				ob_clean();
			}

			wp_send_json_error( $json );
		}

	}

endif;
