<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Menu_Item_Manager' ) ) :

	/**
	 * Handles AJAX requests for menu item settings, including the lightbox
	 * editor, grid elements, and saving of menu item configuration.
	 *
	 * @since   1.4
	 * @package MegaMenu
	 */
	class Mega_Menu_Menu_Item_Manager {

		/**
		 * The ID of the current menu.
		 *
		 * @var int
		 */
		public $menu_id = 0;

		/**
		 * The ID of the current menu item.
		 *
		 * @var int
		 */
		public $menu_item_id = 0;

		/**
		 * The title of the current menu item.
		 *
		 * @var string
		 */
		public $menu_item_title = '';

		/**
		 * The depth of the current menu item (0 = top level).
		 *
		 * @var int
		 */
		public $menu_item_depth = 0;

		/**
		 * All menu item objects for the current menu.
		 *
		 * @var array
		 */
		public $menu_item_objects = [];

		/**
		 * Saved meta for the current menu item.
		 *
		 * @var array
		 */
		public $menu_item_meta = [];


		/**
		 * Constructor. Registers AJAX actions and menu item tab filters.
		 *
		 * @since 1.4
		 */
		public function __construct() {
			add_action( 'wp_ajax_megamenu_get_dialog_html', [ $this, 'ajax_get_dialog_html' ] );
			add_action( 'wp_ajax_megamenu_get_empty_grid_column', [ $this, 'ajax_get_empty_grid_column' ] );
			add_action( 'wp_ajax_megamenu_get_empty_grid_row', [ $this, 'ajax_get_empty_grid_row' ] );
			add_action( 'wp_ajax_megamenu_save_menu_item_settings', [ $this, 'ajax_save_menu_item_settings' ] );
			// Pro and older integrations still POST action=mm_save_menu_item_settings; admin-ajax returns 400 if no hook exists.
			add_action( 'wp_ajax_mm_save_menu_item_settings', [ $this, 'ajax_save_menu_item_settings' ] );

			add_filter( 'megamenu_tabs', [ $this, 'add_mega_menu_tab' ], 10, 5 );
			add_filter( 'megamenu_tabs', [ $this, 'add_general_settings_tab' ], 10, 5 );
			add_filter( 'megamenu_tabs', [ $this, 'add_icon_tab' ], 10, 5 );
		}


		/**
		 * Populates instance properties from the current AJAX POST request.
		 *
		 * @since 1.4
		 * @return void
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
		 * Get the depth for a menu item ID.
		 *
		 * @since 2.7.7
		 * @param int   $menu_item_id      ID of the menu item.
		 * @param array $menu_item_objects All menu item objects for the menu.
		 * @return int Depth of the item: 0 = top level, 1 = second level, 2 = third level or deeper.
		 */
		public function get_menu_item_depth( $menu_item_id, $menu_item_objects ) {
			$parents = [];

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
		 * Save custom menu item fields via AJAX.
		 *
		 * @since 1.4
		 * @return void
		 */
		public static function ajax_save_menu_item_settings() {

			check_ajax_referer( 'megamenu_edit' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$submitted_settings = isset( $_POST['settings'] ) ? $_POST['settings'] : [];

			$menu_item_id = absint( $_POST['menu_item_id'] );

			if ( $menu_item_id > 0 && is_array( $submitted_settings ) ) {

				// only check the checkbox values if the general settings form was submitted
				if ( isset( $_POST['tab'] ) && $_POST['tab'] == 'general_settings' ) {

					$checkboxes = [ 'hide_text', 'disable_link', 'hide_arrow', 'hide_on_mobile', 'hide_on_desktop', 'close_after_click', 'hide_sub_menu_on_mobile', 'collapse_children' ];

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
		 * Returns the tab HTML for the menu item settings dialog via AJAX.
		 *
		 * @since 1.4
		 * @return void
		 */
		public function ajax_get_dialog_html() {

			check_ajax_referer( 'megamenu_edit' );

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$this->init();

			$response = [];

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
		 * Returns the menu ID for a specified menu item ID.
		 *
		 * @since 2.7.5
		 * @param int $menu_item_id ID of the menu item.
		 * @return int The parent menu's term ID.
		 */
		public function get_menu_id_for_menu_item_id( $menu_item_id ) {
			$terms   = get_the_terms( $menu_item_id, 'nav_menu' );
			$menu_id = $terms[0]->term_id;
			return $menu_id;
		}


		/**
		 * Returns the title of a given menu item.
		 *
		 * @since 2.7.5
		 * @param int   $menu_item_id      ID of the menu item.
		 * @param array $menu_item_objects All menu item objects for the menu.
		 * @return string|false The menu item title, or false if not found.
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
		 * Return the HTML to display in the 'Mega Menu' tab.
		 *
		 * @since 1.7
		 * @param array $tabs            Existing tabs array.
		 * @param int   $menu_item_id    ID of the menu item.
		 * @param int   $menu_id         ID of the menu.
		 * @param int   $menu_item_depth Depth of the menu item.
		 * @param array $menu_item_meta  Saved meta for the menu item.
		 * @return array Updated tabs array.
		 */
		public function add_mega_menu_tab( $tabs, $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta ) {
			if ( $menu_item_depth > 0 ) {
				$tabs['mega_menu'] = [
					'title'   => __( 'Sub Menu', 'megamenu' ),
					'content' => '<em>' . __( 'Mega Menus can only be created on top level menu items.', 'megamenu' ) . '</em>',
				];

				return $tabs;
			}

			$return  = $this->get_mega_submenu_toolbar_html( $menu_item_meta, $menu_item_depth );
			$return .= $this->get_megamenu_html( $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta );

			$return .= $this->get_megamenu_grid_html( $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta );

			$tabs['mega_menu'] = [
				'title'   => __( 'Mega Menu', 'megamenu' ),
				'content' => $return,
			];

			return $tabs;
		}


		/**
		 * Markup for sub menu type + panel options (`mm_*` ids, `mm_panel_options`), matching megamenu-pro.
		 * JS wraps this in `.mega-submenu-toolbar` when missing. Public for extensions.
		 *
		 * @since 3.9.0
		 * @param array $menu_item_meta   Saved mega menu meta for the menu item; must include `type`.
		 * @param int   $menu_item_depth  Item depth: 0 top level, 1 second level, 2 third level or deeper (@see get_menu_item_depth()).
		 * @return string HTML.
		 */
		public function get_mega_submenu_toolbar_html( $menu_item_meta, $menu_item_depth = 0 ) {
			$submenu_options = apply_filters(
				'megamenu_submenu_options',
				[
					'flyout'   => __( 'Flyout Menu', 'megamenu' ),
					'grid'     => __( 'Mega Menu - Grid Layout', 'megamenu' ),
					'megamenu' => __( 'Mega Menu - Standard Layout', 'megamenu' ),
				],
				$menu_item_meta,
				(int) $menu_item_depth
			);

			// Match megamenu-pro Tab Content / second-level markup (`mm_*` ids, `mm_panel_options`) so Pro + free share one DOM contract.
			$return  = "<label for='mm_enable_mega_menu'>" . esc_html__( 'Sub menu display mode', 'megamenu' ) . '</label>';
			$return .= "<select id='mm_enable_mega_menu' name='settings[type]'>";

			foreach ( $submenu_options as $type => $label ) {
				$return .= "<option id='{$type}' value='{$type}' " . selected( $menu_item_meta['type'], $type, false ) . ">{$label}</option>";
			}
			$return .= '</select>';

			$widget_manager = new Mega_Menu_Widget_Manager();

			$all_widgets = $widget_manager->get_available_widgets();

			$return .= "<div class='mm_panel_options'>";
			$return .= $this->get_panel_columns_select_markup( $menu_item_meta );
			$return .= "<select id='mm_widget_selector'>";
			$return .= "<option value='disabled'>" . esc_html__( 'Select a Widget to add to the panel', 'megamenu' ) . '</option>';

			foreach ( $all_widgets as $widget ) {
				$return .= "<option value='" . esc_attr( $widget['value'] ) . "'>" . esc_html( $widget['text'] ) . '</option>';
			}

			$return .= '</select>';
			$return .= '</div>';

			$return .= apply_filters( 'megamenu_submenu_toolbar_extra_html', '', $menu_item_meta, (int) $menu_item_depth );

			return $return;
		}


		/**
		 * Return the HTML for the grid layout mega menu builder.
		 *
		 * @since 2.4
		 * @param int   $menu_item_id    ID of the menu item.
		 * @param int   $menu_id         ID of the menu.
		 * @param int   $menu_item_depth Depth of the menu item.
		 * @param array $menu_item_meta  Saved meta for the menu item.
		 * @return string Grid layout HTML.
		 */
		public function get_megamenu_grid_html( $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta ) {

			$return = "<div id='megamenu-grid'>";

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

			$return .= "   <button class='button button-primary button-small mega-add-row'><span class='dashicons dashicons-plus'></span>" . __( 'Row', 'megamenu' ) . '</button>';
			$return .= '</div>';

			return $return;

		}

		/**
		 * Returns the HTML for an empty grid column via AJAX.
		 *
		 * @since 2.4
		 * @return void
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
		 * Returns the HTML for an empty grid row via AJAX.
		 *
		 * @since 2.4
		 * @return void
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
		 * Return the HTML for a single grid row.
		 *
		 * @since 2.4
		 * @param array|false  $row_data    Row meta data, or false for an empty row.
		 * @param string|false $column_html Pre-rendered column HTML, or false for empty.
		 * @return string Row HTML.
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
				$row_columns = (int) $row_data['meta']['columns'];
			}

			$desktop_tooltip_visible = __( 'Row', 'megamenu' ) . ': ' . __( 'Visible on desktop', 'megamenu' );
			$desktop_tooltip_hidden  = __( 'Row', 'megamenu' ) . ': ' . __( 'Hidden on desktop', 'megamenu' );
			$mobile_tooltip_visible  = __( 'Row', 'megamenu' ) . ': ' . __( 'Visible on mobile', 'megamenu' );
			$mobile_tooltip_hidden   = __( 'Row', 'megamenu' ) . ': ' . __( 'Hidden on mobile', 'megamenu' );

			$row_class = isset( $row_data['meta']['class'] ) ? $row_data['meta']['class'] : '';

			$row_tracks = (int) $row_columns;
			$return      = "<div class='mega-row' data-available-cols='" . esc_attr( (string) $row_tracks ) . "' style='" . esc_attr( '--row-tracks: ' . $row_tracks . ';' ) . "'>";
			$return .= "    <div class='mega-row-header'>";
			$return .= "        <div class='mega-row-actions' role='toolbar' aria-label='" . esc_attr__( 'Row toolbar', 'megamenu' ) . "'>";
			$return .= "            <button type='button' class='dashicons dashicons-admin-generic mega-row-header__action mega-row-header__action--settings' aria-expanded='false' aria-label='" . esc_attr__( 'Row settings', 'megamenu' ) . "'></button>";
			$return .= "            <button type='button' class='" . esc_attr( "{$hide_on_desktop} mega-row-header__action mega-row-header__action--desktop" ) . "' data-mega-tooltip data-mega-tooltip-enabled='" . esc_attr( $desktop_tooltip_visible ) . "' data-mega-tooltip-disabled='" . esc_attr( $desktop_tooltip_hidden ) . "' aria-label='" . esc_attr__( 'Toggle row visibility on desktop', 'megamenu' ) . "'><span class='dashicons dashicons-desktop' aria-hidden='true'></span></button>";
			$return .= "            <button type='button' class='" . esc_attr( "{$hide_on_mobile} mega-row-header__action mega-row-header__action--mobile" ) . "' data-mega-tooltip data-mega-tooltip-enabled='" . esc_attr( $mobile_tooltip_visible ) . "' data-mega-tooltip-disabled='" . esc_attr( $mobile_tooltip_hidden ) . "' aria-label='" . esc_attr__( 'Toggle row visibility on mobile', 'megamenu' ) . "'><span class='dashicons dashicons-smartphone' aria-hidden='true'></span></button>";
			$return .= "            <button type='button' class='dashicons dashicons-trash mega-row-header__action mega-row-header__action--delete' aria-label='" . esc_attr__( 'Delete row', 'megamenu' ) . "'></button>";
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
			$return .= '<p class="submit"><button type="submit" class="button button-primary button-small mega-save-row-settings">' . esc_html__( 'Save', 'megamenu' ) . '</button></p>';
			$return .= '        </div>';
			$return .= "        <button class='button button-primary button-small mega-add-column'><span class='dashicons dashicons-plus'></span>" . __( 'Column', 'megamenu' ) . '</button>';
			$return .= '    </div>';
			$return .= "    <div class='error notice is-dismissible mega-too-many-cols'>";
			$return .= '        <p>' . __( 'You should rearrange the content of this row so that all columns fit onto a single line.', 'megamenu' ) . '</p>';
			$return .= '    </div>';
			$return .= "    <div class='error notice is-dismissible mega-row-is-full'>";
			$return .= '        <p>' . __( 'There is not enough space on this row to add a new column.', 'megamenu' ) . '</p>';
			$return .= '    </div>';

			$return .= "    <div class='mega-row-cols'>";
			$return .= $column_html ? $column_html : '';
			$return .= '    </div>';

			$return .= '</div>';

			return $return;
		}

		/**
		 * Return the HTML for an individual grid column.
		 *
		 * @since 2.4
		 * @param array|false $row_data Row meta data, or false for a new empty row.
		 * @param array|false $col_data Column meta and widget data, or false for empty.
		 * @return string Column HTML.
		 */
		public function get_grid_column( $row_data = false, $col_data = false ) {

			$col_span    = 3;
			$row_columns = 12;

			if ( isset( $row_data['meta']['columns'] ) ) {
				$row_columns = (int) $row_data['meta']['columns'];
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

			$col_span_int = (int) $col_span;
			$total_blocks = ( is_array( $col_data ) && isset( $col_data['items'] ) && count( $col_data['items'] ) ) ? count( $col_data['items'] ) : 0;
			$return        = "<div class='mega-col' data-span='" . esc_attr( (string) $col_span_int ) . "' data-total-blocks='" . esc_attr( (string) $total_blocks ) . "' style='" . esc_attr( '--span: ' . $col_span_int . ';' ) . "'>";
			$return .= "    <div class='mega-col-wrap'>";
			$return .= "        <div class='mega-col-header'>";
			$return .= "            <div class='mega-col-actions' role='toolbar' aria-label='" . esc_attr__( 'Column toolbar', 'megamenu' ) . "'>";
			$return .= "                <button type='button' class='dashicons dashicons-admin-generic mega-col-header__action mega-col-header__action--settings' aria-expanded='false' aria-label='" . esc_attr__( 'Column settings', 'megamenu' ) . "'></button>";
			$return .= "                <button type='button' class='" . esc_attr( "{$hide_on_desktop} mega-col-header__action mega-col-header__action--desktop" ) . "' data-mega-tooltip data-mega-tooltip-enabled='" . esc_attr( $desktop_tooltip_visible ) . "' data-mega-tooltip-disabled='" . esc_attr( $desktop_tooltip_hidden ) . "' aria-label='" . esc_attr__( 'Toggle column visibility on desktop', 'megamenu' ) . "'><span class='dashicons dashicons-desktop' aria-hidden='true'></span></button>";
			$return .= "                <button type='button' class='" . esc_attr( "{$hide_on_mobile} mega-col-header__action mega-col-header__action--mobile" ) . "' data-mega-tooltip data-mega-tooltip-enabled='" . esc_attr( $mobile_tooltip_visible ) . "' data-mega-tooltip-disabled='" . esc_attr( $mobile_tooltip_hidden ) . "' aria-label='" . esc_attr__( 'Toggle column visibility on mobile', 'megamenu' ) . "'><span class='dashicons dashicons-smartphone' aria-hidden='true'></span></button>";
			$return .= "                <button type='button' class='dashicons dashicons-trash mega-col-header__action mega-col-header__action--delete' aria-label='" . esc_attr__( 'Delete column', 'megamenu' ) . "'></button>";
			$return .= '                <span class="mega-col-drag-handle" title="' . esc_attr__( 'Drag to reorder column', 'megamenu' ) . '"></span>';
			$return .= '            </div>';
			$return .= '            <div class="mega-col-span">';
			$return .= '<button type="button" class="mega-col-option mega-col-contract" aria-label="' . esc_attr__( 'Contract', 'megamenu' ) . '"><span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span></button>';
			$return .= "                <span class='mega-col-cols'><span class='mega-num-cols'>{$col_span_int}</span><span class='mega-of'>/</span><span class='mega-num-total-cols'>" . $row_columns . '</span></span>';
			$return .= '<button type="button" class="mega-col-options mega-col-expand" aria-label="' . esc_attr__( 'Expand', 'megamenu' ) . '"><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></button>';
			$return .= '            </div>';
			$return .= '        </div>';
			$return .= "        <div class='mega-col-settings'>";
			$return .= "            <input name='mega-hide-on-mobile' type='hidden' value='{$hide_on_mobile_checked}' />";
			$return .= "            <input name='mega-hide-on-desktop' type='hidden' value='{$hide_on_desktop_checked}'/>";
			$return .= '            <label>' . __( 'Column class', 'megamenu' ) . '</label>';
			$return .= "            <input class='mega-column-class' type='text' value='{$col_class}' />";
			$return .= '<p class="submit"><button type="submit" class="button button-primary button-small mega-save-column-settings">' . esc_html__( 'Save', 'megamenu' ) . '</button></p>';
			$return .= '        </div>';
			$return .= "        <div class='mega-col-widgets'>";

			if ( isset( $col_data['items'] ) && count( $col_data['items'] ) ) {
				foreach ( $col_data['items'] as $item ) {
					$return .= '<div class="mega-widget widget" id="' . esc_attr( $item['id'] ) . '" data-type="' . esc_attr( $item['type'] ) . '" data-id="' . esc_attr( $item['id'] ) . '">';
					$return .= '    <div class="mega-widget-top widget-top">';
					$return .= '        <div class="mega-widget-title widget-title">';
					$return .= '            <h4>' . esc_html( $item['title'] ) . '</h4>';
					$return .= '            <span class="mega-widget-desc widget-desc">' . esc_html( $item['description'] ) . '</span>';
					$return .= '        </div>';
					$return .= '        <div class="mega-widget-title-action widget-title-action">';
					$return .= '            <a class="mega-widget-option mega-widget-action widget-option widget-action" title="' . esc_attr( __( 'Edit', 'megamenu' ) ) . '"></a>';
					$return .= '        </div>';
					$return .= '    </div>';
					$return .= '    <div class="mega-widget-inner mega-widget-inside widget-inner widget-inside"></div>';
					$return .= '</div>';
				}
			}

			$add_widget_label = __( 'Add widget to this column', 'megamenu' );
			$return .= "            <button type='button' class='mega-col-add-widget' data-mega-tooltip='" . esc_attr( $add_widget_label ) . "' aria-label='" . esc_attr( $add_widget_label ) . "'>";
			$return .= "                <span class='dashicons dashicons-plus-alt2' aria-hidden='true'></span>";
			$return .= '            </button>';

			$return .= '        </div>';
			$return .= '    </div>';
			$return .= '</div>';

			return $return;
		}


		/**
		 * Return the HTML for the standard (non-grid) mega menu builder.
		 *
		 * @since 1.4
		 * @param int   $menu_item_id    ID of the menu item.
		 * @param int   $menu_id         ID of the menu.
		 * @param int   $menu_item_depth Depth of the menu item.
		 * @param array $menu_item_meta  Saved meta for the menu item.
		 * @return string Standard mega menu builder HTML.
		 */
		public function get_megamenu_html( $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta ) {

			$widget_manager = new Mega_Menu_Widget_Manager();

			$return = "<div id='megamenu-standard' data-columns='{$menu_item_meta['panel_columns']}'>";

			$items = $widget_manager->get_widgets_and_menu_items_for_menu_id( $menu_item_id, $menu_id );

			if ( count( $items ) ) {

				foreach ( $items as $item ) {
					$return .= '<div class="mega-widget widget" id="' . esc_attr( $item['id'] ) . '" data-columns="' . esc_attr( $item['columns'] ) . '" data-type="' . esc_attr( $item['type'] ) . '" data-id="' . esc_attr( $item['id'] ) . '">';
					$return .= '    <div class="mega-widget-top widget-top">';
					$return .= '        <div class="mega-widget-title widget-title">';
					$return .= '            <h4>' . esc_html( $item['title'] ) . '</h4>';
					$return .= '        </div>';
					$return .= '        <div class="mega-widget-title-action widget-title-action">';
					$return .= '            <div class="mega-col-span">';
					$return .= '<button type="button" class="mega-widget-option mega-widget-contract widget-option widget-contract" aria-label="' . esc_attr__( 'Contract', 'megamenu' ) . '"></button>';
					$return .= '            <span class="mega-widget-cols widget-cols"><span class="mega-widget-num-cols widget-num-cols">' . $item['columns'] . '</span><span class="mega-widget-of widget-of">/</span><span class="mega-widget-total-cols widget-total-cols">' . $menu_item_meta['panel_columns'] . '</span></span>';
					$return .= '<button type="button" class="mega-widget-option mega-widget-expand widget-option widget-expand" aria-label="' . esc_attr__( 'Expand', 'megamenu' ) . '"></button>';
					$return .= '            </div>';
					$return .= '            <a class="mega-widget-option mega-widget-action widget-option widget-action" title="' . esc_attr( __( 'Edit', 'megamenu' ) ) . '"></a>';
					$return .= '        </div>';
					$return .= '    </div>';
					$return .= '    <div class="mega-widget-inner mega-widget-inside widget-inner widget-inside"></div>';
					$return .= '</div>';
				}
			} else {
				$return .= "<p class='no_widgets'>" . __( 'No widgets found. Add a widget using the widget selector above.', 'megamenu' ) . '</p>';
			}

			$return .= '</div>';

			return $return;
		}


		/**
		 * &lt;select&gt; for settings[panel_columns] (shared option list; `mm_number_of_columns` matches megamenu-pro).
		 *
		 * @param array $menu_item_meta Menu item meta.
		 * @return string HTML.
		 */
		private function get_panel_columns_select_markup( array $menu_item_meta ) {
			$html  = "<select id='mm_number_of_columns' name='settings[panel_columns]' aria-label='" . esc_attr__( 'Number of columns', 'megamenu' ) . "'>";
			$html .= "        <option value='1' " . selected( $menu_item_meta['panel_columns'], 1, false ) . '>1 ' . __( 'column', 'megamenu' ) . '</option>';
			$html .= "        <option value='2' " . selected( $menu_item_meta['panel_columns'], 2, false ) . '>2 ' . __( 'columns', 'megamenu' ) . '</option>';
			$html .= "        <option value='3' " . selected( $menu_item_meta['panel_columns'], 3, false ) . '>3 ' . __( 'columns', 'megamenu' ) . '</option>';
			$html .= "        <option value='4' " . selected( $menu_item_meta['panel_columns'], 4, false ) . '>4 ' . __( 'columns', 'megamenu' ) . '</option>';
			$html .= "        <option value='5' " . selected( $menu_item_meta['panel_columns'], 5, false ) . '>5 ' . __( 'columns', 'megamenu' ) . '</option>';
			$html .= "        <option value='6' " . selected( $menu_item_meta['panel_columns'], 6, false ) . '>6 ' . __( 'columns', 'megamenu' ) . '</option>';
			$html .= "        <option value='7' " . selected( $menu_item_meta['panel_columns'], 7, false ) . '>7 ' . __( 'columns', 'megamenu' ) . '</option>';
			$html .= "        <option value='8' " . selected( $menu_item_meta['panel_columns'], 8, false ) . '>8 ' . __( 'columns', 'megamenu' ) . '</option>';
			$html .= "        <option value='9' " . selected( $menu_item_meta['panel_columns'], 9, false ) . '>9 ' . __( 'columns', 'megamenu' ) . '</option>';
			$html .= "        <option value='10' " . selected( $menu_item_meta['panel_columns'], 10, false ) . '>10 ' . __( 'columns', 'megamenu' ) . '</option>';
			$html .= "        <option value='11' " . selected( $menu_item_meta['panel_columns'], 11, false ) . '>11 ' . __( 'columns', 'megamenu' ) . '</option>';
			$html .= "        <option value='12' " . selected( $menu_item_meta['panel_columns'], 12, false ) . '>12 ' . __( 'columns', 'megamenu' ) . '</option>';
			$html .= '    </select>';

			return $html;
		}


		/**
		 * Pill toggle markup for menu item Settings tab checkboxes (same structure as location enable toggle).
		 *
		 * @since 3.8.2
		 * @param int    $menu_item_id Menu item post ID.
		 * @param string $setting_key   Key in the submitted settings array.
		 * @param string $current       Saved value, typically 'true' or 'false'.
		 * @return string HTML.
		 */
		private function render_menu_item_settings_pill( $menu_item_id, $setting_key, $current ) {
			$id = 'mmm-item-setting-' . sanitize_key( $setting_key ) . '-' . (int) $menu_item_id;

			$is_on           = ( 'true' === (string) $current );
			$toggle_classes  = 'components-form-toggle';
			$toggle_classes .= $is_on ? ' is-checked' : '';

			$html  = '<label class="mmm-settings-pill-field-label">';
			$html .= '<span class="' . esc_attr( $toggle_classes ) . '">';
			$html .= '<input type="checkbox" id="' . esc_attr( $id ) . '" class="components-form-toggle__input" role="switch" name="' . esc_attr( 'settings[' . $setting_key . ']' ) . '" value="true"';
			$html .= checked( $current, 'true', false );
			$html .= ' />';
			$html .= '<span class="components-form-toggle__track" aria-hidden="true"></span>';
			$html .= '<span class="components-form-toggle__thumb" aria-hidden="true"></span>';
			$html .= '</span></label>';

			return $html;
		}

		/**
		 * Return the HTML to display in the 'General Settings' tab.
		 *
		 * @since 1.7
		 * @param array $tabs            Existing tabs array.
		 * @param int   $menu_item_id    ID of the menu item.
		 * @param int   $menu_id         ID of the menu.
		 * @param int   $menu_item_depth Depth of the menu item.
		 * @param array $menu_item_meta  Saved meta for the menu item.
		 * @return array Updated tabs array.
		 */
		public function add_general_settings_tab( $tabs, $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta ) {

			$return  = '<form>';
			$return .= '    <input type="hidden" name="menu_item_id" value="' . esc_attr( $menu_item_id ) . '" />';
			$return .= '    <input type="hidden" name="action" value="megamenu_save_menu_item_settings" />';
			$return .= '    <input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'megamenu_edit' ) . '" />';
			$return .= '    <input type="hidden" name="tab" value="general_settings" />';
			$return .= '    <h4 class="first">' . __( 'Menu Item Settings', 'megamenu' ) . '</h4>';
			$return .= '    <table class="mmm-settings-table">';
			$return .= '        <tr>';
			$return .= '            <td class="mega-name">';
			$return .= '<div class="mega-name-title">' . __( 'Hide text', 'megamenu' ) . '</div>';
			$return .= '            </td>';
			$return .= '            <td class="mega-value mega-value--pill">';
			$return .= $this->render_menu_item_settings_pill( $menu_item_id, 'hide_text', $menu_item_meta['hide_text'] );
			$return .= '            </td>';
			$return .= '        </tr>';
			$return .= '        <tr>';
			$return .= '            <td class="mega-name">';
			$return .= '<div class="mega-name-title">' . __( 'Hide arrow', 'megamenu' ) . '</div>';
			$return .= '            </td>';
			$return .= '            <td class="mega-value mega-value--pill">';
			$return .= $this->render_menu_item_settings_pill( $menu_item_id, 'hide_arrow', $menu_item_meta['hide_arrow'] );
			$return .= '            </td>';
			$return .= '        </tr>';
			$return .= '        <tr>';
			$return .= '            <td class="mega-name">';
			$return .= '<div class="mega-name-title">' . __( 'Disable link', 'megamenu' ) . '</div>';
			$return .= '            </td>';
			$return .= '            <td class="mega-value mega-value--pill">';
			$return .= $this->render_menu_item_settings_pill( $menu_item_id, 'disable_link', $menu_item_meta['disable_link'] );
			$return .= '            </td>';
			$return .= '        </tr>';
			$return .= '        <tr>';
			$return .= '            <td class="mega-name">';
			$return .= '<div class="mega-name-title">' . __( 'Hide item on mobile', 'megamenu' ) . '</div>';
			$return .= '            </td>';
			$return .= '            <td class="mega-value mega-value--pill">';
			$return .= $this->render_menu_item_settings_pill( $menu_item_id, 'hide_on_mobile', $menu_item_meta['hide_on_mobile'] );
			$return .= '            </td>';
			$return .= '        </tr>';
			$return .= '        <tr>';
			$return .= '            <td class="mega-name">';
			$return .= '<div class="mega-name-title">' . __( 'Hide item on desktop', 'megamenu' ) . '</div>';
			$return .= '            </td>';
			$return .= '            <td class="mega-value mega-value--pill">';
			$return .= $this->render_menu_item_settings_pill( $menu_item_id, 'hide_on_desktop', $menu_item_meta['hide_on_desktop'] );
			$return .= '            </td>';
			$return .= '        </tr>';
			$return .= '        <tr>';
			$return .= '            <td class="mega-name">';
			$return .= '<div class="mega-name-title">' . __( 'Close sub menu when clicked', 'megamenu' ) . '</div>';
			$return .= '            </td>';
			$return .= '            <td class="mega-value mega-value--pill">';
			$return .= $this->render_menu_item_settings_pill( $menu_item_id, 'close_after_click', $menu_item_meta['close_after_click'] );
			$return .= '            <div class="mega-description">';
			$return .= __( 'Intended for use on anchor links (e.g. #about)', 'megamenu' );
			$return .= '            </div>';
			$return .= '            </td>';
			$return .= '        </tr>';
			$return .= '        <tr class="mega-menu-item-align">';
			$return .= '            <td class="mega-name">';
			$return .= '<div class="mega-name-title">' . __( 'Menu item align', 'megamenu' ) . '</div>';
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
			$return .= '<div class="mega-name-title">' . __( 'Icon position', 'megamenu' ) . '</div>';
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

			$return .= '    <table class="mmm-settings-table">';
			$return .= '        <tr class="mega-sub-menu-align">';
			$return .= '            <td class="mega-name">';
			$return .= '<div class="mega-name-title">' . __( 'Sub menu align', 'megamenu' ) . '</div>';
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
			$return .= '<div class="mega-name-title">' . __( 'Hide sub menu on mobile', 'megamenu' ) . '</div>';
			$return .= '            </td>';
			$return .= '            <td class="mega-value mega-value--pill">';
			$return .= $this->render_menu_item_settings_pill( $menu_item_id, 'hide_sub_menu_on_mobile', $menu_item_meta['hide_sub_menu_on_mobile'] );
			$return .= '            </td>';
			$return .= '        </tr>';

			if ( $menu_item_depth > 0 ) {
				$return .= '        <tr>';
				$return .= '            <td class="mega-name">';
				$return .= '<div class="mega-name-title">' . __( 'Collapse sub menu', 'megamenu' ) . '</div>';
				$return .= '            </td>';
				$return .= '            <td class="mega-value mega-value--pill">';
				$return .= $this->render_menu_item_settings_pill( $menu_item_id, 'collapse_children', $menu_item_meta['collapse_children'] );
				$return .= '                <em>' . __( 'Only applies to menu items displayed within mega sub menus.', 'megamenu' ) . '</em>';
				$return .= '            </td>';
				$return .= '        </tr>';
			}

			$return .= apply_filters( 'megamenu_after_menu_item_submenu_settings', '', $tabs, $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta );

			$return .= '    </table>';

			$return .= '<p class="submit"><button type="submit" name="submit" id="submit" class="button button-primary button-compact">' . esc_html__( 'Save Changes', 'default' ) . '</button></p>';
			$return .= '</form>';

			$tabs['general_settings'] = [
				'title'   => __( 'Settings', 'megamenu' ),
				'content' => $return,
			];

			return $tabs;

		}


		/**
		 * Returns the upgrade CTA anchor HTML for icon-tab upsell copy.
		 *
		 * @see WP_HTML_Tag_Processor
		 * @since 3.9.0
		 * @return string Markup for an external link to the Pro upgrade page.
		 */
		private function get_megamenu_icon_upgrade_link_html() {
			$url   = 'https://www.megamenu.com/upgrade/?utm_source=free&utm_medium=icon&utm_campaign=pro';
			$label = esc_html( __( 'Max Mega Menu Pro', 'megamenu' ) );

			$processor = new WP_HTML_Tag_Processor( '<a>' . $label . '</a>' );
			
			if ( $processor->next_tag( 'a' ) ) {
				$processor->set_attribute( 'href', $url );
				$processor->set_attribute( 'target', '_blank' );
				$processor->set_attribute( 'rel', 'noopener noreferrer' );
			}

			return $processor->get_updated_html();
		}


		/**
		 * Return the HTML to display in the 'Icon' tab.
		 *
		 * @since 1.7
		 * @param array $tabs            Existing tabs array.
		 * @param int   $menu_item_id    ID of the menu item.
		 * @param int   $menu_id         ID of the menu.
		 * @param int   $menu_item_depth Depth of the menu item.
		 * @param array $menu_item_meta  Saved meta for the menu item.
		 * @return array Updated tabs array.
		 */
		public function add_icon_tab( $tabs, $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta ) {

			$upgrade_link = $this->get_megamenu_icon_upgrade_link_html();

			$icon_tabs = [
				'fontawesome' => [
					'title'   => __( 'Font Awesome', 'megamenu' ),
					'active'  => false,
					'content' => '<p>' . sprintf(
						/* translators: %s: HTML link to upgrade to Max Mega Menu Pro. */
						__( 'Get access to over 400 Font Awesome Icons with %s', 'megamenu' ),
						$upgrade_link
					) . '</p>',
				],
				'custom'      => [
					'title'   => __( 'Custom Icon', 'megamenu' ),
					'active'  => false,
					'content' => '<p>' . sprintf(
						/* translators: %s: HTML link to upgrade to Max Mega Menu Pro. */
						__( 'Select icons from your media library with %s', 'megamenu' ),
						$upgrade_link
					) . '</p>',
				],
			];

			$icon_tabs = apply_filters( 'megamenu_icon_tabs', $icon_tabs, $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta );

			$return  = "<div class='menu_icon__header'>";
			$return .= "<h4 class='first'>" . __( 'Menu Item Icon', 'megamenu' ) . '</h4>';
			$return .= "<div class='megamenu_icon_tab_toolbar'>";
			$return .= "<ul class='megamenu_tabs horizontal'>";

			foreach ( $icon_tabs as $id => $icon_tab ) {

				$active = $icon_tab['active'] || count( $icon_tabs ) === 1 ? 'active' : '';

				$return .= "<li rel='megamenu_tab_{$id}' class='megamenu_tab_horizontal {$active}'>";
				$return .= esc_html( $icon_tab['title'] );
				$return .= '</li>';

			}

			$return .= '</ul>';
			$return .= "<input type='text' id='filter_icons' class='filter_icons' placeholder='" . esc_attr__( 'Search', 'megamenu' ) . "' />";
			$return .= '</div>';
			$return .= '</div>';

			foreach ( $icon_tabs as $id => $icon_tab ) {

				$display = $icon_tab['active'] ? 'block' : 'none';

				$before_form = isset( $icon_tab['before_form'] ) ? $icon_tab['before_form'] : '';

				$return .= "<div class='megamenu_tab_{$id}' style='display: {$display}'>";
				$return .= $before_form;
				$form_class = ( 'custom' === $id ) ? 'icon_selector_custom' : 'icon_selector icon_selector_' . $id;
				$return .= "    <form class='" . esc_attr( $form_class ) . "'>";
				$return .= "        <input type='hidden' name='_wpnonce' value='" . wp_create_nonce( 'megamenu_edit' ) . "' />";
				$return .= "        <input type='hidden' name='menu_item_id' value='" . esc_attr( $menu_item_id ) . "' />";
				$return .= "        <input type='hidden' name='action' value='megamenu_save_menu_item_settings' />";
				$return .= $icon_tab['content'];
				$return .= '    </form>';
				$return .= '</div>';

			}

			$icon_payload_merged = [];
			foreach ( $icon_tabs as $id => $icon_tab ) {
				if ( ! empty( $icon_tab['icon_payload'] ) && is_array( $icon_tab['icon_payload'] ) ) {
					foreach ( $icon_tab['icon_payload'] as $payload_key => $payload_val ) {
						$icon_payload_merged[ $payload_key ] = $payload_val;
					}
				}
			}

			// menu_icon may include icon_payload for lazy JS (Dashicons / Material). Pro tabs may be HTML-only with no icon_payload keys.
			$tabs['menu_icon'] = [
				'title'        => __( 'Icon', 'megamenu' ),
				'content'      => $return,
				'icon_payload' => array_merge(
					[
						'current_icon' => isset( $menu_item_meta['icon'] ) ? (string) $menu_item_meta['icon'] : 'disabled',
					],
					$icon_payload_merged
				),
			];

			return $tabs;

		}

		/**
		 * List of all available Dashicon classes.
		 *
		 * @since 1.0
		 * @return array Sorted map of hex code keys to Dashicon CSS class names.
		 */
		public function all_icons() {
			if ( class_exists( 'Mega_Menu_Dashicons' ) ) {
				return Mega_Menu_Dashicons::all_icons();
			}

			return [];
		}
	}

endif;
