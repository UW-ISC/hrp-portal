<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Menu_Item_Manager' ) ) :

	/**
	 *
	 * @since 1.4
	 */
	class Mega_Menu_Menu_Item_Manager {

		var $menu_id           = 0;
		var $menu_item_id      = 0;
		var $menu_item_title   = '';
		var $menu_item_depth   = 0;
		var $menu_item_objects = array();
		var $menu_item_meta    = array();


		/**
		 * Constructor
		 *
		 * @since 1.4
		 */
		public function __construct() {
			add_action( 'wp_ajax_mm_get_lightbox_html', array( $this, 'ajax_get_lightbox_html' ) );
			add_action( 'wp_ajax_mm_get_empty_grid_column', array( $this, 'ajax_get_empty_grid_column' ) );
			add_action( 'wp_ajax_mm_get_empty_grid_row', array( $this, 'ajax_get_empty_grid_row' ) );
			add_action( 'wp_ajax_mm_save_menu_item_settings', array( $this, 'ajax_save_menu_item_settings' ) );

			add_filter( 'megamenu_tabs', array( $this, 'add_mega_menu_tab' ), 10, 5 );
			add_filter( 'megamenu_tabs', array( $this, 'add_general_settings_tab' ), 10, 5 );
			add_filter( 'megamenu_tabs', array( $this, 'add_icon_tab' ), 10, 5 );
		}


		/**
		 * Set up the class
		 *
		 * @since 1.4
		 */
		private function init() {
			if ( isset( $_POST['menu_item_id'] ) ) {
				$this->menu_item_id      = absint( $_POST['menu_item_id'] );
				$this->menu_id           = $this->get_menu_id_for_menu_item_id( $this->menu_item_id );
				$this->menu_item_objects = wp_get_nav_menu_items( $this->menu_id );
				$this->menu_item_title   = $this->get_title_for_menu_item_id( $this->menu_item_id, $this->menu_item_objects );
				$this->menu_item_depth   = $this->get_menu_item_depth( $this->menu_item_id, $this->menu_item_objects );
				$saved_settings          = array_filter( (array) get_post_meta( $this->menu_item_id, '_megamenu', true ) );
				$this->menu_item_meta    = array_merge( Mega_Menu_Nav_Menus::get_menu_item_defaults(), $saved_settings );
			}
		}

		/**
		 * Get the depth for a menu item ID
		 *
		 * @since 2.7.7
		 * @param int $menu_item_id
		 * @param array $menu_item_objects
		 * @return int
		 */
		public function get_menu_item_depth( $menu_item_id, $menu_item_objects ) {
			$parents = array();

			foreach ( $menu_item_objects as $key => $item ) {
				if ( $item->menu_item_parent == 0 ) {

					if ( $item->ID == $menu_item_id ) {
						return 0; // top level item
					}

					$parents[] = $item->ID;
				}
			}

			if ( count( $parents ) ) {
				foreach ( $menu_item_objects as $key => $item ) {
					if ( in_array( $item->menu_item_parent, $parents ) ) {
						if ( $item->ID == $menu_item_id ) {
							return 1; // second level item
						}
					}
				}
			}

			return 2; // third level item or above
		}


		/**
		 * Save custom menu item fields.
		 *
		 * @since 1.4
		 */
		public static function ajax_save_menu_item_settings() {

			check_ajax_referer( 'megamenu_edit' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$submitted_settings = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

			$menu_item_id = absint( $_POST['menu_item_id'] );

			if ( $menu_item_id > 0 && is_array( $submitted_settings ) ) {

				// only check the checkbox values if the general settings form was submitted
				if ( isset( $_POST['tab'] ) && $_POST['tab'] == 'general_settings' ) {

					$checkboxes = array( 'hide_text', 'disable_link', 'hide_arrow', 'hide_on_mobile', 'hide_on_desktop', 'close_after_click', 'hide_sub_menu_on_mobile', 'collapse_children' );

					foreach ( $checkboxes as $checkbox ) {
						if ( ! isset( $submitted_settings[ $checkbox ] ) ) {
							$submitted_settings[ $checkbox ] = 'false';
						}
					}
				}

				$submitted_settings = apply_filters( 'megamenu_menu_item_submitted_settings', $submitted_settings, $menu_item_id );

				$existing_settings = get_post_meta( $menu_item_id, '_megamenu', true );

				if ( is_array( $existing_settings ) ) {

					$submitted_settings = array_merge( $existing_settings, $submitted_settings );

				}

				update_post_meta( $menu_item_id, '_megamenu', $submitted_settings );

				do_action( 'megamenu_save_menu_item_settings', $menu_item_id );

			}

			if ( isset( $_POST['clear_cache'] ) ) {

				do_action( 'megamenu_delete_cache' );

			}

			if ( ob_get_contents() ) {
				ob_clean(); // remove any warnings or output from other plugins which may corrupt the response
			}

			wp_send_json_success();

		}


		/**
		 * Return the HTML to display in the Lightbox
		 *
		 * @since 1.4
		 * @return string
		 */
		public function ajax_get_lightbox_html() {

			check_ajax_referer( 'megamenu_edit' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$this->init();

			$response = array();

			$response['title'] = $this->menu_item_title;

			$response['active_tab'] = 'mega_menu';

			if ( $this->menu_item_depth > 0 ) {
				$response['active_tab'] = 'general_settings';
			}

			$response = apply_filters( 'megamenu_tabs', $response, $this->menu_item_id, $this->menu_id, $this->menu_item_depth, $this->menu_item_meta );

			if ( ob_get_contents() ) {
				ob_clean(); // remove any warnings or output from other plugins which may corrupt the response
			}

			wp_send_json_success( json_encode( $response ) );
		}


		/**
		 * Returns the menu ID for a specified menu item ID
		 *
		 * @since 2.7.5
		 * @param int $menu_item_id
		 * @return int $menu_id
		 */
		public function get_menu_id_for_menu_item_id( $menu_item_id ) {
			$terms   = get_the_terms( $menu_item_id, 'nav_menu' );
			$menu_id = $terms[0]->term_id;
			return $menu_id;
		}


		/**
		 * Returns the title of a given menu item ID
		 *
		 * @since 2.7.5
		 * @param int $menu_item_id
		 * @return int $menu_id
		 */
		public function get_title_for_menu_item_id( $menu_item_id, $menu_item_objects ) {
			foreach ( $menu_item_objects as $key => $item ) {
				if ( $item->ID == $menu_item_id ) {
					return $item->title;
				}
			}

			return false;
		}

		/**
		 * Return the HTML to display in the 'Mega Menu' tab
		 *
		 * @since 1.7
		 * @return array
		 */
		public function add_mega_menu_tab( $tabs, $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta ) {
			if ( $menu_item_depth > 0 ) {
				$tabs['mega_menu'] = array(
					'title'   => __( 'Sub Menu', 'megamenu' ),
					'content' => '<em>' . __( 'Mega Menus can only be created on top level menu items.', 'megamenu' ) . '</em>',
				);

				return $tabs;
			}

			$submenu_options = apply_filters(
				'megamenu_submenu_options',
				array(
					'flyout'   => __( 'Flyout Menu', 'megamenu' ),
					'grid'     => __( 'Mega Menu - Grid Layout', 'megamenu' ),
					'megamenu' => __( 'Mega Menu - Standard Layout', 'megamenu' ),
				),
				$menu_item_meta
			);

			$return = "<label for='mm_enable_mega_menu'>" . __( 'Sub menu display mode', 'megamenu' ) . '</label>';

			$return .= "<select id='mm_enable_mega_menu' name='settings[type]'>";

			foreach ( $submenu_options as $type => $label ) {
				$return .= "<option id='{$type}' value='{$type}' " . selected( $menu_item_meta['type'], $type, false ) . ">{$label}</option>";
			}
			$return .= '</select>';

			$widget_manager = new Mega_Menu_Widget_Manager();

			$all_widgets = $widget_manager->get_available_widgets();

			$return .= "<div class='mm_panel_options'>";
			$return .= "    <select id='mm_number_of_columns' name='settings[panel_columns]'>";
			$return .= "        <option value='1' " . selected( $menu_item_meta['panel_columns'], 1, false ) . '>1 ' . __( 'column', 'megamenu' ) . '</option>';
			$return .= "        <option value='2' " . selected( $menu_item_meta['panel_columns'], 2, false ) . '>2 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "        <option value='3' " . selected( $menu_item_meta['panel_columns'], 3, false ) . '>3 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "        <option value='4' " . selected( $menu_item_meta['panel_columns'], 4, false ) . '>4 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "        <option value='5' " . selected( $menu_item_meta['panel_columns'], 5, false ) . '>5 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "        <option value='6' " . selected( $menu_item_meta['panel_columns'], 6, false ) . '>6 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "        <option value='7' " . selected( $menu_item_meta['panel_columns'], 7, false ) . '>7 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "        <option value='8' " . selected( $menu_item_meta['panel_columns'], 8, false ) . '>8 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "        <option value='9' " . selected( $menu_item_meta['panel_columns'], 9, false ) . '>9 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "        <option value='10' " . selected( $menu_item_meta['panel_columns'], 10, false ) . '>10 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "        <option value='11' " . selected( $menu_item_meta['panel_columns'], 11, false ) . '>11 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "        <option value='12' " . selected( $menu_item_meta['panel_columns'], 12, false ) . '>12 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= '    </select>';

			$return .= "    <select id='mm_widget_selector'>";
			$return .= "        <option value='disabled'>" . __( 'Select a Widget to add to the panel', 'megamenu' ) . '</option>';

			foreach ( $all_widgets as $widget ) {
				$return .= "    <option value='" . $widget['value'] . "'>" . $widget['text'] . '</option>';
			}

			$return .= '    </select>';
			$return .= '</div>';

			$return .= $this->get_megamenu_html( $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta );

			$return .= $this->get_megamenu_grid_html( $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta );

			$tabs['mega_menu'] = array(
				'title'   => __( 'Mega Menu', 'megamenu' ),
				'content' => $return,
			);

			return $tabs;
		}


		/**
		 * Return the HTML for the grid layout
		 *
		 * @since 2.4
		 * @return string
		 */
		public function get_megamenu_grid_html( $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta ) {

			$css_version = get_transient( 'megamenu_css_version' );

			$return = "<div id='megamenu-grid'>";

			if ( $css_version && version_compare( $css_version, '2.3.9', '<=' ) ) {
				$link    = "<a href='" . esc_attr( admin_url( 'admin.php?page=maxmegamenu_tools' ) ) . "'>" . __( 'Mega Menu' ) . ' > ' . __( 'Tools' ) . '</a>';
				$return .= "<div class='notice notice-success'><p>";
				$return .= sprintf( __( 'Your menu CSS needs to be updated first. Please go to %s and Clear the CSS Cache (you will only need to do this once).', 'megamenu' ), $link );
				$return .= '</p></div>';
				$return .= '</div>';

				return $return;
			}

			$widget_manager = new Mega_Menu_Widget_Manager();

			$grid = $widget_manager->get_grid_widgets_and_menu_items_for_menu_id( $menu_item_id, $menu_id );

			if ( count( $grid ) ) {

				foreach ( $grid as $row => $row_data ) {

					$column_html = '';

					if ( isset( $row_data['columns'] ) && count( $row_data['columns'] ) ) {

						foreach ( $row_data['columns'] as $col => $col_data ) {
							$column_html .= $this->get_grid_column( $row_data, $col_data );
						}
					}

					$return .= $this->get_grid_row( $row_data, $column_html );

				}
			}

			$return .= "   <button class='button button-primary mega-add-row'><span class='dashicons dashicons-plus'></span>" . __( 'Row', 'megamenu' ) . '</button>';
			$return .= '</div>';

			return $return;

		}

		/**
		 * Return the HTML to display in the Lightbox
		 *
		 * @since 2.4
		 * @return string
		 */
		public function ajax_get_empty_grid_column() {

			check_ajax_referer( 'megamenu_edit' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$return = $this->get_grid_column();

			if ( ob_get_contents() ) {
				ob_clean(); // remove any warnings or output from other plugins which may corrupt the response
			}

			wp_send_json_success( $return );
		}

		/**
		 * Return the HTML to display in the Lightbox
		 *
		 * @since 2.4
		 * @return string
		 */
		public function ajax_get_empty_grid_row() {

			check_ajax_referer( 'megamenu_edit' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$column_html = $this->get_grid_column();

			$return = $this->get_grid_row( false, $column_html );

			if ( ob_get_contents() ) {
				ob_clean(); // remove any warnings or output from other plugins which may corrupt the response
			}

			wp_send_json_success( $return );
		}

		/**
		 * Return the HTML for a single row
		 *
		 * @param array $row_data
		 * @param string $column_html
		 * @since 2.4
		 * @return string
		 */
		public function get_grid_row( $row_data = false, $column_html = false ) {

			$hide_on_desktop_checked = 'false';
			$hide_on_desktop         = 'mega-enabled';

			if ( isset( $row_data['meta']['hide-on-desktop'] ) && $row_data['meta']['hide-on-desktop'] == 'true' ) {
				$hide_on_desktop         = 'mega-disabled';
				$hide_on_desktop_checked = 'true';
			}

			$hide_on_mobile_checked = 'false';
			$hide_on_mobile         = 'mega-enabled';

			if ( isset( $row_data['meta']['hide-on-mobile'] ) && $row_data['meta']['hide-on-mobile'] == 'true' ) {
				$hide_on_mobile         = 'mega-disabled';
				$hide_on_mobile_checked = 'true';
			}

			$row_columns = 12;

			if ( isset( $row_data['meta']['columns'] ) ) {
				$row_columns = intval( $row_data['meta']['columns'] );
			}

			$desktop_tooltip_visible = __( 'Row', 'megamenu' ) . ': ' . __( 'Visible on desktop', 'megamenu' );
			$desktop_tooltip_hidden  = __( 'Row', 'megamenu' ) . ': ' . __( 'Hidden on desktop', 'megamenu' );
			$mobile_tooltip_visible  = __( 'Row', 'megamenu' ) . ': ' . __( 'Visible on mobile', 'megamenu' );
			$mobile_tooltip_hidden   = __( 'Row', 'megamenu' ) . ': ' . __( 'Hidden on mobile', 'megamenu' );

			$row_class = isset( $row_data['meta']['class'] ) ? $row_data['meta']['class'] : '';

			$return  = "<div class='mega-row' data-available-cols='{$row_columns}'>";
			$return .= "    <div class='mega-row-header'>";
			$return .= "        <div class='mega-row-actions'>";
			$return .= "            <span class='dashicons dashicons-sort'></span>";
			$return .= "            <span class='dashicons dashicons-admin-generic'></span>";
			$return .= "            <span class='mega-tooltip {$hide_on_desktop}' data-tooltip-enabled='{$desktop_tooltip_visible}' data-tooltip-disabled='{$desktop_tooltip_hidden}'><span class='dashicons dashicons-desktop'></span></span>";
			$return .= "            <span class='mega-tooltip {$hide_on_mobile}' data-tooltip-enabled='{$mobile_tooltip_visible}' data-tooltip-disabled='{$mobile_tooltip_hidden}'><span class='dashicons dashicons-smartphone'></span></span>";
			$return .= "            <span class='dashicons dashicons-trash'></span>";
			$return .= '        </div>';
			$return .= "        <div class='mega-row-settings'>";
			$return .= "            <input name='mega-hide-on-mobile' type='hidden' value='{$hide_on_mobile_checked}' />";
			$return .= "            <input name='mega-hide-on-desktop' type='hidden' value='{$hide_on_desktop_checked}'/>";
			$return .= "            <div class='mega-settings-row'>";
			$return .= '                <label>' . __( 'Row class', 'megamenu' ) . '</label>';
			$return .= "                <input class='mega-row-class' type='text' value='{$row_class}' />";
			$return .= '            </div>';
			$return .= "            <div class='mega-settings-row'>";
			$return .= '                <label>' . __( 'Row columns', 'megamenu' ) . '</label>';
			$return .= "                <select class='mega-row-columns'>";
			$return .= "                    <option value='1' " . selected( $row_columns, 1, false ) . '>1 ' . __( 'column', 'megamenu' ) . '</option>';
			$return .= "                    <option value='2' " . selected( $row_columns, 2, false ) . '>2 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "                    <option value='3' " . selected( $row_columns, 3, false ) . '>3 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "                    <option value='4' " . selected( $row_columns, 4, false ) . '>4 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "                    <option value='5' " . selected( $row_columns, 5, false ) . '>5 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "                    <option value='6' " . selected( $row_columns, 6, false ) . '>6 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "                    <option value='7' " . selected( $row_columns, 7, false ) . '>7 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "                    <option value='8' " . selected( $row_columns, 8, false ) . '>8 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "                    <option value='9' " . selected( $row_columns, 9, false ) . '>9 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "                    <option value='10' " . selected( $row_columns, 10, false ) . '>10 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "                    <option value='11' " . selected( $row_columns, 11, false ) . '>11 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= "                    <option value='12' " . selected( $row_columns, 12, false ) . '>12 ' . __( 'columns', 'megamenu' ) . '</option>';
			$return .= '                </select>';
			$return .= '            </div>';
			$return .= "            <button class='button button-primary mega-save-row-settings' type='submit'>" . __( 'Save', 'megamenu' ) . '</button>';
			$return .= '        </div>';
			$return .= "        <button class='button button-primary mega-add-column'><span class='dashicons dashicons-plus'></span>" . __( 'Column', 'megamenu' ) . '</button>';
			$return .= '    </div>';
			$return .= "    <div class='error notice is-dismissible mega-too-many-cols'>";
			$return .= '        <p>' . __( 'You should rearrange the content of this row so that all columns fit onto a single line.', 'megamenu' ) . '</p>';
			$return .= '    </div>';
			$return .= "    <div class='error notice is-dismissible mega-row-is-full'>";
			$return .= '        <p>' . __( 'There is not enough space on this row to add a new column.', 'megamenu' ) . '</p>';
			$return .= '    </div>';

			$return .= $column_html;

			$return .= '</div>';

			return $return;
		}

		/**
		 * Return the HTML for an individual grid column
		 *
		 * @param array $col_data
		 * @since 2.4
		 * @return string
		 */
		public function get_grid_column( $row_data = false, $col_data = false ) {

			$col_span    = 3;
			$row_columns = 12;

			if ( isset( $row_data['meta']['columns'] ) ) {
				$row_columns = intval( $row_data['meta']['columns'] );
			}

			if ( isset( $col_data['meta']['span'] ) ) {
				$col_span = $col_data['meta']['span'];
			}

			$hide_on_desktop_checked = 'false';
			$hide_on_desktop         = 'mega-enabled';

			if ( isset( $col_data['meta']['hide-on-desktop'] ) && $col_data['meta']['hide-on-desktop'] == 'true' ) {
				$hide_on_desktop         = 'mega-disabled';
				$hide_on_desktop_checked = 'true';
			}

			$hide_on_mobile_checked = 'false';
			$hide_on_mobile         = 'mega-enabled';

			if ( isset( $col_data['meta']['hide-on-mobile'] ) && $col_data['meta']['hide-on-mobile'] == 'true' ) {
				$hide_on_mobile         = 'mega-disabled';
				$hide_on_mobile_checked = 'true';
			}

			$desktop_tooltip_visible = __( 'Column', 'megamenu' ) . ': ' . __( 'Visible on desktop', 'megamenu' );
			$desktop_tooltip_hidden  = __( 'Column', 'megamenu' ) . ': ' . __( 'Hidden on desktop', 'megamenu' );
			$mobile_tooltip_visible  = __( 'Column', 'megamenu' ) . ': ' . __( 'Visible on mobile', 'megamenu' );
			$mobile_tooltip_hidden   = __( 'Column', 'megamenu' ) . ': ' . __( 'Hidden on mobile', 'megamenu' );

			$col_class = isset( $col_data['meta']['class'] ) ? $col_data['meta']['class'] : '';

			$return  = "<div class='mega-col' data-span='{$col_span}'>";
			$return .= "    <div class='mega-col-wrap'>";
			$return .= "        <div class='mega-col-header'>";
			$return .= "            <div class='mega-col-description'>";
			$return .= "                <span class='dashicons dashicons-move'></span>";
			$return .= "                <span class='dashicons dashicons-admin-generic'></span>";
			$return .= "                <span class='mega-tooltip {$hide_on_desktop}' data-tooltip-enabled='{$desktop_tooltip_visible}' data-tooltip-disabled='{$desktop_tooltip_hidden}'><span class='dashicons dashicons-desktop'></span></span>";
			$return .= "                <span class='mega-tooltip {$hide_on_mobile}' data-tooltip-enabled='{$mobile_tooltip_visible}' data-tooltip-disabled='{$mobile_tooltip_hidden}'><span class='dashicons dashicons-smartphone'></span></span>";
			$return .= "                <span class='dashicons dashicons-trash'></span>";
			$return .= '            </div>';
			$return .= '            <div class="mega-col-actions">';
			$return .= '                <a class="mega-col-option mega-col-contract" title="' . esc_attr( __( 'Contract', 'megamenu' ) ) . '"><span class="dashicons dashicons-arrow-left-alt2"></span></a>';
			$return .= "                <span class='mega-col-cols'><span class='mega-num-cols'>{$col_span}</span><span class='mega-of'>/</span><span class='mega-num-total-cols'>" . $row_columns . '</span></span>';
			$return .= '                <a class="mega-col-options mega-col-expand" title="' . esc_attr( __( 'Expand', 'megamenu' ) ) . '"><span class="dashicons dashicons-arrow-right-alt2"></span></a>';
			$return .= '            </div>';
			$return .= '        </div>';
			$return .= "        <div class='mega-col-settings'>";
			$return .= "            <input name='mega-hide-on-mobile' type='hidden' value='{$hide_on_mobile_checked}' />";
			$return .= "            <input name='mega-hide-on-desktop' type='hidden' value='{$hide_on_desktop_checked}'/>";
			$return .= '            <label>' . __( 'Column class', 'megamenu' ) . '</label>';
			$return .= "            <input class='mega-column-class' type='text' value='{$col_class}' />";
			$return .= "            <button class='button button-primary mega-save-column-settings' type='submit'>" . __( 'Save', 'megamenu' ) . '</button>';
			$return .= '        </div>';
			$return .= "        <div class='mega-col-widgets'>";

			if ( isset( $col_data['items'] ) && count( $col_data['items'] ) ) {
				foreach ( $col_data['items'] as $item ) {
					$return .= '<div class="widget" title="' . esc_attr( $item['title'] ) . '" id="' . esc_attr( $item['id'] ) . '" data-type="' . esc_attr( $item['type'] ) . '" data-id="' . esc_attr( $item['id'] ) . '">';
					$return .= '    <div class="widget-top">';
					$return .= '        <div class="widget-title">';
					$return .= '            <h4>' . esc_html( $item['title'] ) . '</h4>';
					$return .= '            <span class="widget-desc">' . esc_html( $item['description'] ) . '</span>';
					$return .= '        </div>';
					$return .= '        <div class="widget-title-action">';
					$return .= '            <a class="widget-option widget-action" title="' . esc_attr( __( 'Edit', 'megamenu' ) ) . '"></a>';
					$return .= '        </div>';
					$return .= '    </div>';
					$return .= '    <div class="widget-inner widget-inside"></div>';
					$return .= '</div>';
				}
			}

			$return .= '        </div>';
			$return .= '    </div>';
			$return .= '</div>';

			return $return;
		}


		/**
		 * Return the HTML for the standard (non grid) mega menu builder
		 *
		 * @return string
		 */
		public function get_megamenu_html( $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta ) {

			$widget_manager = new Mega_Menu_Widget_Manager();

			$return = "<div id='widgets' data-columns='{$menu_item_meta['panel_columns']}'>";

			$items = $widget_manager->get_widgets_and_menu_items_for_menu_id( $menu_item_id, $menu_id );

			if ( count( $items ) ) {

				foreach ( $items as $item ) {
					$return .= '<div class="widget" title="' . esc_attr( $item['title'] ) . '" id="' . esc_attr( $item['id'] ) . '" data-columns="' . esc_attr( $item['columns'] ) . '" data-type="' . esc_attr( $item['type'] ) . '" data-id="' . esc_attr( $item['id'] ) . '">';
					$return .= '    <div class="widget-top">';
					$return .= '        <div class="widget-title-action">';
					$return .= '            <a class="widget-option widget-contract" title="' . esc_attr( __( 'Contract', 'megamenu' ) ) . '"></a>';
					$return .= '            <span class="widget-cols"><span class="widget-num-cols">' . $item['columns'] . '</span><span class="widget-of">/</span><span class="widget-total-cols">' . $menu_item_meta['panel_columns'] . '</span></span>';
					$return .= '            <a class="widget-option widget-expand" title="' . esc_attr( __( 'Expand', 'megamenu' ) ) . '"></a>';
					$return .= '            <a class="widget-option widget-action" title="' . esc_attr( __( 'Edit', 'megamenu' ) ) . '"></a>';
					$return .= '        </div>';
					$return .= '        <div class="widget-title">';
					$return .= '            <h4>' . esc_html( $item['title'] ) . '</h4>';
					$return .= '        </div>';
					$return .= '    </div>';
					$return .= '    <div class="widget-inner widget-inside"></div>';
					$return .= '</div>';
				}
			} else {
				$return .= "<p class='no_widgets'>" . __( 'No widgets found. Add a widget to this area using the Widget Selector (top right)', 'megamenu' ) . '</p>';
			}

			$return .= '</div>';

			return $return;
		}

		/**
		 * Return the HTML to display in the 'General Settings' tab
		 *
		 * @since 1.7
		 * @return array
		 */
		public function add_general_settings_tab( $tabs, $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta ) {

			$return  = '<form>';
			$return .= '    <input type="hidden" name="menu_item_id" value="' . esc_attr( $menu_item_id ) . '" />';
			$return .= '    <input type="hidden" name="action" value="mm_save_menu_item_settings" />';
			$return .= '    <input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'megamenu_edit' ) . '" />';
			$return .= '    <input type="hidden" name="tab" value="general_settings" />';
			$return .= '    <h4 class="first">' . __( 'Menu Item Settings', 'megamenu' ) . '</h4>';
			$return .= '    <table>';
			$return .= '        <tr>';
			$return .= '            <td class="mega-name">';
			$return .= __( 'Hide text', 'megamenu' );
			$return .= '            </td>';
			$return .= '            <td class="mega-value">';
			$return .= '                <input type="checkbox" name="settings[hide_text]" value="true" ' . checked( $menu_item_meta['hide_text'], 'true', false ) . ' />';
			$return .= '            </td>';
			$return .= '        </tr>';
			$return .= '        <tr>';
			$return .= '            <td class="mega-name">';
			$return .= __( 'Hide arrow', 'megamenu' );
			$return .= '            </td>';
			$return .= '            <td class="mega-value">';
			$return .= '                <input type="checkbox" name="settings[hide_arrow]" value="true" ' . checked( $menu_item_meta['hide_arrow'], 'true', false ) . ' />';
			$return .= '            </td>';
			$return .= '        </tr>';
			$return .= '        <tr>';
			$return .= '            <td class="mega-name">';
			$return .= __( 'Disable link', 'megamenu' );
			$return .= '            </td>';
			$return .= '            <td class="mega-value">';
			$return .= '                <input type="checkbox" name="settings[disable_link]" value="true" ' . checked( $menu_item_meta['disable_link'], 'true', false ) . ' />';
			$return .= '            </td>';
			$return .= '        </tr>';
			$return .= '        <tr>';
			$return .= '            <td class="mega-name">';
			$return .= __( 'Hide item on mobile', 'megamenu' );
			$return .= '            </td>';
			$return .= '            <td class="mega-value">';
			$return .= '                <input type="checkbox" name="settings[hide_on_mobile]" value="true" ' . checked( $menu_item_meta['hide_on_mobile'], 'true', false ) . ' />';
			$return .= '            </td>';
			$return .= '        </tr>';
			$return .= '        <tr>';
			$return .= '            <td class="mega-name">';
			$return .= __( 'Hide item on desktop', 'megamenu' );
			$return .= '            </td>';
			$return .= '            <td class="mega-value">';
			$return .= '                <input type="checkbox" name="settings[hide_on_desktop]" value="true" ' . checked( $menu_item_meta['hide_on_desktop'], 'true', false ) . ' />';
			$return .= '            </td>';
			$return .= '        </tr>';
			$return .= '            <td class="mega-name">';
			$return .= __( 'Close sub menu when clicked', 'megamenu' );
			$return .= '            </td>';
			$return .= '            <td class="mega-value">';
			$return .= '                <input type="checkbox" name="settings[close_after_click]" value="true" ' . checked( $menu_item_meta['close_after_click'], 'true', false ) . ' />';
				$return .= '            <div class="mega-description">';
				$return .= __( 'Intended for use on anchor links (e.g. #about)', 'megamenu' );
				$return .= '            </div>';			$return .= '            </td>';
			$return .= '        </tr>';
			$return .= '        <tr class="mega-menu-item-align">';
			$return .= '            <td class="mega-name">';
			$return .= __( 'Menu item align', 'megamenu' );
			$return .= '            </td>';
			$return .= '            <td class="mega-value">';

			if ( $menu_item_depth == 0 ) {

				$item_align = $menu_item_meta['item_align'];

				$float_left_display = $item_align == 'float-left' ? 'block' : 'none';
				$left_display       = $item_align == 'left' ? 'block' : 'none';
				$right_display      = $item_align == 'right' ? 'block' : 'none';

				$return .= '            <select id="mega-item-align" name="settings[item_align]">';
				$return .= '                <option value="float-left" ' . selected( $menu_item_meta['item_align'], 'float-left', false ) . '>' . __( 'Left', 'megamenu' ) . '</option>';
				$return .= '                <option value="left" ' . selected( $menu_item_meta['item_align'], 'left', false ) . '>' . __( 'Default', 'megamenu' ) . '</option>';
				$return .= '                <option value="right" ' . selected( $menu_item_meta['item_align'], 'right', false ) . '>' . __( 'Right', 'megamenu' ) . '</option>';
				$return .= '            </select>';
				$return .= '            <div class="mega-description">';
				$return .= "                    <div class='float-left' style='display:{$float_left_display}'></div>";
				$return .= "                    <div class='left' style='display:{$left_display}'>" . __( "Item will be aligned based on the 'Menu Items Align' option set in the Theme Editor", 'megamenu' ) . '</div>';
				$return .= "                    <div class='right' style='display:{$right_display}'>" . __( 'Right aligned items will appear in reverse order on the right hand side of the menu bar', 'megamenu' ) . '</div>';
				$return .= '            </div>';
			} else {
				$return .= '<em>' . __( 'Option only available for top level menu items', 'megamenu' ) . '</em>';
			}

			$return .= '            </td>';
			$return .= '        </tr>';
			$return .= '        <tr class="mega-menu-icon-position">';
			$return .= '            <td class="mega-name">';
			$return .= __( 'Icon position', 'megamenu' );
			$return .= '            </td>';
			$return .= '            <td class="mega-value">';
			$return .= '            <select name="settings[icon_position]">';
			$return .= '                <option value="left" ' . selected( $menu_item_meta['icon_position'], 'left', false ) . '>' . __( 'Left', 'megamenu' ) . '</option>';
			$return .= '                <option value="top" ' . selected( $menu_item_meta['icon_position'], 'top', false ) . '>' . __( 'Top', 'megamenu' ) . '</option>';
			$return .= '                <option value="right" ' . selected( $menu_item_meta['icon_position'], 'right', false ) . '>' . __( 'Right', 'megamenu' ) . '</option>';
			$return .= '            </select>';

			$return .= '            </td>';
			$return .= '        </tr>';

			$return .= apply_filters( 'megamenu_after_menu_item_settings', '', $tabs, $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta );

			$return .= '    </table>';

			$return .= '    <h4>' . __( 'Sub Menu Settings', 'megamenu' ) . '</h4>';

			$return .= '    <table>';
			$return .= '        <tr class="mega-sub-menu-align">';
			$return .= '            <td class="mega-name">';
			$return .= __( 'Sub menu align', 'megamenu' );
			$return .= '            </td>';
			$return .= '            <td class="mega-value">';

			if ( $menu_item_depth == 0 ) {
				$return .= '            <select name="settings[align]">';
				$return .= '                <option value="bottom-left" ' . selected( $menu_item_meta['align'], 'bottom-left', false ) . '>' . __( 'Left edge of Parent', 'megamenu' ) . '</option>';
				$return .= '                <option value="bottom-right" ' . selected( $menu_item_meta['align'], 'bottom-right', false ) . '>' . __( 'Right edge of Parent', 'megamenu' ) . '</option>';
				$return .= '            </select>';
				$return .= '            <div class="mega-description">';
				$return .= __( 'Right aligned flyout menus will expand to the left', 'megamenu' );
				$return .= '            </div>';
			} else {
				$return .= '<em>' . __( 'Option only available for top level menu items', 'megamenu' ) . '</em>';
			}

			$return .= '            </td>';
			$return .= '        </tr>';
			$return .= '        <tr>';
			$return .= '            <td class="mega-name">';
			$return .= __( 'Hide sub menu on mobile', 'megamenu' );
			$return .= '            </td>';
			$return .= '            <td class="mega-value">';
			$return .= '                <input type="checkbox" name="settings[hide_sub_menu_on_mobile]" value="true" ' . checked( $menu_item_meta['hide_sub_menu_on_mobile'], 'true', false ) . ' />';
			$return .= '            </td>';
			$return .= '        </tr>';

			if ( $menu_item_depth > 0 ) {
				$css_version = get_transient( 'megamenu_css_version' );
				$notice      = '';

				if ( $css_version && version_compare( $css_version, '2.6.1', '<' ) ) {
					$link    = "<a href='" . esc_attr( admin_url( 'admin.php?page=maxmegamenu_tools' ) ) . "'>" . __( 'Mega Menu' ) . ' > ' . __( 'Tools' ) . '</a>';
					$notice  = "<div class='notice notice-success'><p>";
					$notice .= sprintf( __( 'Your menu CSS needs to be updated before you can use the following setting. Please go to %s and Clear the CSS Cache (you will only need to do this once).', 'megamenu' ), $link );
					$notice .= '</p></div>';
					$notice .= '</div><br />';
				}

				$return .= '        <tr>';
				$return .= '            <td class="mega-name">';
				$return .= __( 'Collapse sub menu', 'megamenu' );
				$return .= '            </td>';
				$return .= '            <td class="mega-value">';
				$return .= $notice;
				$return .= '                <input type="checkbox" name="settings[collapse_children]" value="true" ' . checked( $menu_item_meta['collapse_children'], 'true', false ) . ' />';
				$return .= '                <em>' . __( 'Only applies to menu items displayed within mega sub menus.', 'megamenu' ) . '</em>';
				$return .= '            </td>';
				$return .= '        </tr>';
			}

			$return .= apply_filters( 'megamenu_after_menu_item_submenu_settings', '', $tabs, $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta );

			$return .= '    </table>';

			$return .= get_submit_button();
			$return .= '</form>';

			$tabs['general_settings'] = array(
				'title'   => __( 'Settings', 'megamenu' ),
				'content' => $return,
			);

			return $tabs;

		}


		/**
		 * Return the HTML to display in the 'menu icon' tab
		 *
		 * @since 1.7
		 * @return array
		 */
		public function add_icon_tab( $tabs, $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta ) {

			$icon_tabs = array(
				'dashicons'   => array(
					'title'   => __( 'Dashicons', 'megamenu' ),
					'active'  => ! isset( $menu_item_meta['icon'] ) || ( isset( $menu_item_meta['icon'] ) && substr( $menu_item_meta['icon'], 0, strlen( 'dash' ) ) === 'dash' || $menu_item_meta['icon'] == 'disabled' ),
					'content' => $this->dashicon_selector(),
				),
				'fontawesome' => array(
					'title'   => __( 'Font Awesome', 'megamenu' ),
					'active'  => false,
					'content' => str_replace( '{link}', "<a target='_blank' href='https://www.megamenu.com/upgrade/?utm_source=free&amp;utm_medium=icon&amp;utm_campaign=pro'>" . __( 'Max Mega Menu Pro', 'megamenu' ) . '</a>', __( 'Get access to over 400 Font Awesome Icons with {link}', 'megamenu' ) ),
				),
				'genericons'  => array(
					'title'   => __( 'Genericons', 'megamenu' ),
					'active'  => false,
					'content' => str_replace( '{link}', "<a target='_blank' href='https://www.megamenu.com/upgrade/?utm_source=free&amp;utm_medium=icon&amp;utm_campaign=pro'>" . __( 'Max Mega Menu Pro', 'megamenu' ) . '</a>', __( 'Choose from over 100 genericons with {link}', 'megamenu' ) ),
				),
				'custom'      => array(
					'title'   => __( 'Custom Icon', 'megamenu' ),
					'active'  => false,
					'content' => str_replace( '{link}', "<a target='_blank' href='https://www.megamenu.com/upgrade/?utm_source=free&amp;utm_medium=icon&amp;utm_campaign=pro'>" . __( 'Max Mega Menu Pro', 'megamenu' ) . '</a>', __( 'Select icons from your media library with {link}', 'megamenu' ) ),
				),
			);

			$icon_tabs = apply_filters( 'megamenu_icon_tabs', $icon_tabs, $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta );

			$return  = "<h4 class='first'>" . __( 'Menu Item Icon', 'megamenu' ) . '</h4>';
			$return .= "<ul class='mm_tabs horizontal'>";

			foreach ( $icon_tabs as $id => $icon_tab ) {

				$active = $icon_tab['active'] || count( $icon_tabs ) === 1 ? 'active' : '';

				$return .= "<li rel='mm_tab_{$id}' class='mm_tab_horizontal {$active}'>";
				$return .= esc_html( $icon_tab['title'] );
				$return .= '</li>';

			}

			$return .= '</ul>';

			$return .= "<input type='text' class='filter_icons' placeholder='" . __( 'Search', 'megamenu' ) . "' /><div class='clear'></div>";

			foreach ( $icon_tabs as $id => $icon_tab ) {

				$display = $icon_tab['active'] ? 'block' : 'none';

				$return .= "<div class='mm_tab_{$id}' style='display: {$display}'>";
				$return .= "    <form class='icon_selector icon_selector_{$id}'>";
				$return .= "        <input type='hidden' name='_wpnonce' value='" . wp_create_nonce( 'megamenu_edit' ) . "' />";
				$return .= "        <input type='hidden' name='menu_item_id' value='" . esc_attr( $menu_item_id ) . "' />";
				$return .= "        <input type='hidden' name='action' value='mm_save_menu_item_settings' />";
				$return .= $icon_tab['content'];
				$return .= '    </form>';
				$return .= '</div>';

			}

			$tabs['menu_icon'] = array(
				'title'   => __( 'Icon', 'megamenu' ),
				'content' => $return,
			);

			return $tabs;

		}

		/**
		 * Return the form to select a dashicon
		 *
		 * @since 1.5.2
		 * @return string
		 */
		private function dashicon_selector() {

			$return  = "<div class='disabled'><input id='disabled' class='radio' type='radio' rel='disabled' name='settings[icon]' value='disabled' " . checked( $this->menu_item_meta['icon'], 'disabled', false ) . ' />';
			$return .= "<label for='disabled'></label></div>";

			foreach ( $this->all_icons() as $code => $class ) {

				$bits = explode( '-', $code );
				$code = '&#x' . $bits[1] . '';
				$type = $bits[0];

				$return .= "<div class='{$type}'>";
				$return .= "    <input class='radio' id='{$class}' type='radio' rel='{$code}' name='settings[icon]' value='{$class}' " . checked( $this->menu_item_meta['icon'], $class, false ) . ' />';
				$return .= "    <label rel='{$code}' for='{$class}' title='{$class}'></label>";
				$return .= '</div>';

			}

			return $return;
		}


		/**
		 * List of all available DashIcon classes.
		 *
		 * @since 1.0
		 * @return array - Sorted list of icon classes
		 */
		public function all_icons() {

			$icons = array(
				'dash-f333' => 'dashicons-menu',
				'dash-f228' => 'dashicons-menu-alt',
				'dash-f329' => 'dashicons-menu-alt2',
				'dash-f349' => 'dashicons-menu-alt3',
				'dash-f319' => 'dashicons-admin-site',
				'dash-f11d' => 'dashicons-admin-site-alt',
				'dash-f11e' => 'dashicons-admin-site-alt2',
				'dash-f11f' => 'dashicons-admin-site-alt3',
				'dash-f226' => 'dashicons-dashboard',
				'dash-f109' => 'dashicons-admin-post',
				'dash-f104' => 'dashicons-admin-media',
				'dash-f103' => 'dashicons-admin-links',
				'dash-f105' => 'dashicons-admin-page',
				'dash-f101' => 'dashicons-admin-comments',
				'dash-f100' => 'dashicons-admin-appearance',
				'dash-f106' => 'dashicons-admin-plugins',
				'dash-f485' => 'dashicons-plugins-checked',
				'dash-f110' => 'dashicons-admin-users',
				'dash-f107' => 'dashicons-admin-tools',
				'dash-f108' => 'dashicons-admin-settings',
				'dash-f112' => 'dashicons-admin-network',
				'dash-f102' => 'dashicons-admin-home',
				'dash-f111' => 'dashicons-admin-generic',
				'dash-f148' => 'dashicons-admin-collapse',
				'dash-f536' => 'dashicons-filter',
				'dash-f540' => 'dashicons-admin-customizer',
				'dash-f541' => 'dashicons-admin-multisite',
				'dash-f119' => 'dashicons-welcome-write-blog',
				'dash-f133' => 'dashicons-welcome-add-page',
				'dash-f115' => 'dashicons-welcome-view-site',
				'dash-f116' => 'dashicons-welcome-widgets-menus',
				'dash-f117' => 'dashicons-welcome-comments',
				'dash-f118' => 'dashicons-welcome-learn-more',
				'dash-f123' => 'dashicons-format-aside',
				'dash-f128' => 'dashicons-format-image',
				'dash-f161' => 'dashicons-format-gallery',
				'dash-f126' => 'dashicons-format-video',
				'dash-f130' => 'dashicons-format-status',
				'dash-f122' => 'dashicons-format-quote',
				'dash-f125' => 'dashicons-format-chat',
				'dash-f127' => 'dashicons-format-audio',
				'dash-f306' => 'dashicons-camera',
				'dash-f129' => 'dashicons-camera-alt',
				'dash-f232' => 'dashicons-images-alt',
				'dash-f233' => 'dashicons-images-alt2',
				'dash-f234' => 'dashicons-video-alt',
				'dash-f235' => 'dashicons-video-alt2',
				'dash-f236' => 'dashicons-video-alt3',
				'dash-f501' => 'dashicons-media-archive',
				'dash-f500' => 'dashicons-media-audio',
				'dash-f499' => 'dashicons-media-code',
				'dash-f498' => 'dashicons-media-default',
				'dash-f497' => 'dashicons-media-document',
				'dash-f496' => 'dashicons-media-interactive',
				'dash-f495' => 'dashicons-media-spreadsheet',
				'dash-f491' => 'dashicons-media-text',
				'dash-f490' => 'dashicons-media-video',
				'dash-f492' => 'dashicons-playlist-audio',
				'dash-f493' => 'dashicons-playlist-video',
				'dash-f522' => 'dashicons-controls-play',
				'dash-f523' => 'dashicons-controls-pause',
				'dash-f519' => 'dashicons-controls-forward',
				'dash-f517' => 'dashicons-controls-skipforward',
				'dash-f518' => 'dashicons-controls-back',
				'dash-f516' => 'dashicons-controls-skipback',
				'dash-f515' => 'dashicons-controls-repeat',
				'dash-f521' => 'dashicons-controls-volumeon',
				'dash-f520' => 'dashicons-controls-volumeoff',
				'dash-f165' => 'dashicons-image-crop',
				'dash-f531' => 'dashicons-image-rotate',
				'dash-f166' => 'dashicons-image-rotate-left',
				'dash-f167' => 'dashicons-image-rotate-right',
				'dash-f168' => 'dashicons-image-flip-vertical',
				'dash-f169' => 'dashicons-image-flip-horizontal',
				'dash-f533' => 'dashicons-image-filter',
				'dash-f171' => 'dashicons-undo',
				'dash-f172' => 'dashicons-redo',
				'dash-f170' => 'dashicons-database-add',
				'dash-f17e' => 'dashicons-database',
				'dash-f17a' => 'dashicons-database-export',
				'dash-f17b' => 'dashicons-database-import',
				'dash-f17c' => 'dashicons-database-remove',
				'dash-f17d' => 'dashicons-database-view',
				'dash-f134' => 'dashicons-align-full-width',
				'dash-f10a' => 'dashicons-align-pull-left',
				'dash-f10b' => 'dashicons-align-pull-right',
				'dash-f11b' => 'dashicons-align-wide',
				'dash-f12b' => 'dashicons-block-default',
				'dash-f11a' => 'dashicons-button',
				'dash-f137' => 'dashicons-cloud-saved',
				'dash-f13b' => 'dashicons-cloud-upload',
				'dash-f13c' => 'dashicons-columns',
				'dash-f13d' => 'dashicons-cover-image',
				'dash-f11c' => 'dashicons-ellipsis',
				'dash-f13e' => 'dashicons-embed-audio',
				'dash-f13f' => 'dashicons-embed-generic',
				'dash-f144' => 'dashicons-embed-photo',
				'dash-f146' => 'dashicons-embed-post',
				'dash-f149' => 'dashicons-embed-video',
				'dash-f14a' => 'dashicons-exit',
				'dash-f10e' => 'dashicons-heading',
				'dash-f14b' => 'dashicons-html',
				'dash-f14c' => 'dashicons-info-outline',
				'dash-f10f' => 'dashicons-insert',
				'dash-f14d' => 'dashicons-insert-after',
				'dash-f14e' => 'dashicons-insert-before',
				'dash-f14f' => 'dashicons-remove',
				'dash-f15e' => 'dashicons-saved',
				'dash-f150' => 'dashicons-shortcode',
				'dash-f151' => 'dashicons-table-col-after',
				'dash-f152' => 'dashicons-table-col-before',
				'dash-f15a' => 'dashicons-table-col-delete',
				'dash-f15b' => 'dashicons-table-row-after',
				'dash-f15c' => 'dashicons-table-row-before',
				'dash-f15d' => 'dashicons-table-row-delete',
				'dash-f200' => 'dashicons-editor-bold',
				'dash-f201' => 'dashicons-editor-italic',
				'dash-f203' => 'dashicons-editor-ul',
				'dash-f204' => 'dashicons-editor-ol',
				'dash-f12c' => 'dashicons-editor-ol-rtl',
				'dash-f205' => 'dashicons-editor-quote',
				'dash-f206' => 'dashicons-editor-alignleft',
				'dash-f207' => 'dashicons-editor-aligncenter',
				'dash-f208' => 'dashicons-editor-alignright',
				'dash-f209' => 'dashicons-editor-insertmore',
				'dash-f210' => 'dashicons-editor-spellcheck',
				'dash-f211' => 'dashicons-editor-expand',
				'dash-f506' => 'dashicons-editor-contract',
				'dash-f212' => 'dashicons-editor-kitchensink',
				'dash-f213' => 'dashicons-editor-underline',
				'dash-f214' => 'dashicons-editor-justify',
				'dash-f215' => 'dashicons-editor-textcolor',
				'dash-f216' => 'dashicons-editor-paste-word',
				'dash-f217' => 'dashicons-editor-paste-text',
				'dash-f218' => 'dashicons-editor-removeformatting',
				'dash-f219' => 'dashicons-editor-video',
				'dash-f220' => 'dashicons-editor-customchar',
				'dash-f221' => 'dashicons-editor-outdent',
				'dash-f222' => 'dashicons-editor-indent',
				'dash-f223' => 'dashicons-editor-help',
				'dash-f224' => 'dashicons-editor-strikethrough',
				'dash-f225' => 'dashicons-editor-unlink',
				'dash-f320' => 'dashicons-editor-rtl',
				'dash-f10c' => 'dashicons-editor-ltr',
				'dash-f474' => 'dashicons-editor-break',
				'dash-f475' => 'dashicons-editor-code',
				'dash-f476' => 'dashicons-editor-paragraph',
				'dash-f535' => 'dashicons-editor-table',
				'dash-f135' => 'dashicons-align-left',
				'dash-f136' => 'dashicons-align-right',
				'dash-f134' => 'dashicons-align-center',
				'dash-f138' => 'dashicons-align-none',
				'dash-f160' => 'dashicons-lock',
				'dash-f528' => 'dashicons-unlock',
				'dash-f145' => 'dashicons-calendar',
				'dash-f508' => 'dashicons-calendar-alt',
				'dash-f177' => 'dashicons-visibility',
				'dash-f530' => 'dashicons-hidden',
				'dash-f173' => 'dashicons-post-status',
				'dash-f464' => 'dashicons-edit',
				'dash-f182' => 'dashicons-trash',
				'dash-f537' => 'dashicons-sticky',
				'dash-f504' => 'dashicons-external',
				'dash-f142' => 'dashicons-arrow-up',
				'dash-f140' => 'dashicons-arrow-down',
				'dash-f139' => 'dashicons-arrow-right',
				'dash-f141' => 'dashicons-arrow-left',
				'dash-f342' => 'dashicons-arrow-up-alt',
				'dash-f346' => 'dashicons-arrow-down-alt',
				'dash-f344' => 'dashicons-arrow-right-alt',
				'dash-f340' => 'dashicons-arrow-left-alt',
				'dash-f343' => 'dashicons-arrow-up-alt2',
				'dash-f347' => 'dashicons-arrow-down-alt2',
				'dash-f345' => 'dashicons-arrow-right-alt2',
				'dash-f341' => 'dashicons-arrow-left-alt2',
				'dash-f156' => 'dashicons-sort',
				'dash-f229' => 'dashicons-leftright',
				'dash-f503' => 'dashicons-randomize',
				'dash-f163' => 'dashicons-list-view',
				'dash-f164' => 'dashicons-excerpt-view',
				'dash-f509' => 'dashicons-grid-view',
				'dash-f545' => 'dashicons-move',
				'dash-f237' => 'dashicons-share',
				'dash-f240' => 'dashicons-share-alt',
				'dash-f242' => 'dashicons-share-alt2',
				'dash-f303' => 'dashicons-rss',
				'dash-f465' => 'dashicons-email',
				'dash-f466' => 'dashicons-email-alt',
				'dash-f467' => 'dashicons-email-alt2',
				'dash-f325' => 'dashicons-networking',
				'dash-f162' => 'dashicons-amazon',
				'dash-f304' => 'dashicons-facebook',
				'dash-f305' => 'dashicons-facebook-alt',
				'dash-f18b' => 'dashicons-google',
				'dash-f462' => 'dashicons-googleplus',
				'dash-f12d' => 'dashicons-instagram',
				'dash-f18d' => 'dashicons-linkedin',
				'dash-f192' => 'dashicons-pinterest',
				'dash-f19c' => 'dashicons-podio',
				'dash-f195' => 'dashicons-reddit',
				'dash-f196' => 'dashicons-spotify',
				'dash-f199' => 'dashicons-twitch',
				'dash-f301' => 'dashicons-twitter',
				'dash-f302' => 'dashicons-twitter-alt',
				'dash-f19a' => 'dashicons-whatsapp',
				'dash-f19d' => 'dashicons-xing',
				'dash-f19b' => 'dashicons-youtube',
				'dash-f308' => 'dashicons-hammer',
				'dash-f309' => 'dashicons-art',
				'dash-f310' => 'dashicons-migrate',
				'dash-f311' => 'dashicons-performance',
				'dash-f483' => 'dashicons-universal-access',
				'dash-f507' => 'dashicons-universal-access-alt',
				'dash-f486' => 'dashicons-tickets',
				'dash-f484' => 'dashicons-nametag',
				'dash-f481' => 'dashicons-clipboard',
				'dash-f487' => 'dashicons-heart',
				'dash-f488' => 'dashicons-megaphone',
				'dash-f489' => 'dashicons-schedule',
				'dash-f10d' => 'dashicons-tide',
				'dash-f124' => 'dashicons-rest-api',
				'dash-f13a' => 'dashicons-code-standards',
				'dash-f452' => 'dashicons-buddicons-activity',
				'dash-f477' => 'dashicons-buddicons-bbpress-logo',
				'dash-f448' => 'dashicons-buddicons-buddypress-logo',
				'dash-f453' => 'dashicons-buddicons-community',
				'dash-f449' => 'dashicons-buddicons-forums',
				'dash-f454' => 'dashicons-buddicons-friends',
				'dash-f456' => 'dashicons-buddicons-groups',
				'dash-f457' => 'dashicons-buddicons-pm',
				'dash-f451' => 'dashicons-buddicons-replies',
				'dash-f450' => 'dashicons-buddicons-topics',
				'dash-f455' => 'dashicons-buddicons-tracking',
				'dash-f120' => 'dashicons-wordpress',
				'dash-f324' => 'dashicons-wordpress-alt',
				'dash-f157' => 'dashicons-pressthis',
				'dash-f463' => 'dashicons-update',
				'dash-f113' => 'dashicons-update-alt',
				'dash-f180' => 'dashicons-screenoptions',
				'dash-f348' => 'dashicons-info',
				'dash-f174' => 'dashicons-cart',
				'dash-f175' => 'dashicons-feedback',
				'dash-f176' => 'dashicons-cloud',
				'dash-f326' => 'dashicons-translation',
				'dash-f323' => 'dashicons-tag',
				'dash-f318' => 'dashicons-category',
				'dash-f480' => 'dashicons-archive',
				'dash-f479' => 'dashicons-tagcloud',
				'dash-f478' => 'dashicons-text',
				'dash-f16d' => 'dashicons-bell',
				'dash-f147' => 'dashicons-yes',
				'dash-f12a' => 'dashicons-yes-alt',
				'dash-f158' => 'dashicons-no',
				'dash-f335' => 'dashicons-no-alt',
				'dash-f132' => 'dashicons-plus',
				'dash-f502' => 'dashicons-plus-alt',
				'dash-f543' => 'dashicons-plus-alt2',
				'dash-f460' => 'dashicons-minus',
				'dash-f153' => 'dashicons-dismiss',
				'dash-f159' => 'dashicons-marker',
				'dash-f155' => 'dashicons-star-filled',
				'dash-f459' => 'dashicons-star-half',
				'dash-f154' => 'dashicons-star-empty',
				'dash-f227' => 'dashicons-flag',
				'dash-f534' => 'dashicons-warning',
				'dash-f230' => 'dashicons-location',
				'dash-f231' => 'dashicons-location-alt',
				'dash-f178' => 'dashicons-vault',
				'dash-f332' => 'dashicons-shield',
				'dash-f334' => 'dashicons-shield-alt',
				'dash-f468' => 'dashicons-sos',
				'dash-f179' => 'dashicons-search',
				'dash-f181' => 'dashicons-slides',
				'dash-f121' => 'dashicons-text-page',
				'dash-f183' => 'dashicons-analytics',
				'dash-f184' => 'dashicons-chart-pie',
				'dash-f185' => 'dashicons-chart-bar',
				'dash-f238' => 'dashicons-chart-line',
				'dash-f239' => 'dashicons-chart-area',
				'dash-f307' => 'dashicons-groups',
				'dash-f338' => 'dashicons-businessman',
				'dash-f12f' => 'dashicons-businesswoman',
				'dash-f12e' => 'dashicons-businessperson',
				'dash-f336' => 'dashicons-id',
				'dash-f337' => 'dashicons-id-alt',
				'dash-f312' => 'dashicons-products',
				'dash-f313' => 'dashicons-awards',
				'dash-f314' => 'dashicons-forms',
				'dash-f473' => 'dashicons-testimonial',
				'dash-f322' => 'dashicons-portfolio',
				'dash-f330' => 'dashicons-book',
				'dash-f331' => 'dashicons-book-alt',
				'dash-f316' => 'dashicons-download',
				'dash-f317' => 'dashicons-upload',
				'dash-f321' => 'dashicons-backup',
				'dash-f469' => 'dashicons-clock',
				'dash-f339' => 'dashicons-lightbulb',
				'dash-f482' => 'dashicons-microphone',
				'dash-f472' => 'dashicons-desktop',
				'dash-f547' => 'dashicons-laptop',
				'dash-f471' => 'dashicons-tablet',
				'dash-f470' => 'dashicons-smartphone',
				'dash-f525' => 'dashicons-phone',
				'dash-f510' => 'dashicons-index-card',
				'dash-f511' => 'dashicons-carrot',
				'dash-f512' => 'dashicons-building',
				'dash-f513' => 'dashicons-store',
				'dash-f514' => 'dashicons-album',
				'dash-f527' => 'dashicons-palmtree',
				'dash-f524' => 'dashicons-tickets-alt',
				'dash-f526' => 'dashicons-money',
				'dash-f18e' => 'dashicons-money-alt',
				'dash-f328' => 'dashicons-smiley',
				'dash-f529' => 'dashicons-thumbs-up',
				'dash-f542' => 'dashicons-thumbs-down',
				'dash-f538' => 'dashicons-layout',
				'dash-f546' => 'dashicons-paperclip',
				'dash-f131' => 'dashicons-color-picker',
				'dash-f327' => 'dashicons-edit-large',
				'dash-f186' => 'dashicons-edit-page',
				'dash-f15f' => 'dashicons-airplane',
				'dash-f16a' => 'dashicons-bank',
				'dash-f16c' => 'dashicons-beer',
				'dash-f16e' => 'dashicons-calculator',
				'dash-f16b' => 'dashicons-car',
				'dash-f16f' => 'dashicons-coffee',
				'dash-f17f' => 'dashicons-drumstick',
				'dash-f187' => 'dashicons-food',
				'dash-f188' => 'dashicons-fullscreen-alt',
				'dash-f189' => 'dashicons-fullscreen-exit-alt',
				'dash-f18a' => 'dashicons-games',
				'dash-f18c' => 'dashicons-hourglass',
				'dash-f18f' => 'dashicons-open-folder',
				'dash-f190' => 'dashicons-pdf',
				'dash-f191' => 'dashicons-pets',
				'dash-f193' => 'dashicons-printer',
				'dash-f194' => 'dashicons-privacy',
				'dash-f198' => 'dashicons-superhero',
				'dash-f197' => 'dashicons-superhero-alt',
			);

			$icons = apply_filters( 'megamenu_dashicons', $icons );

			ksort( $icons );

			return $icons;
		}
	}

endif;
