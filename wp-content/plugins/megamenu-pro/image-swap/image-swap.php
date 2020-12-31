<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists('Mega_Menu_Image_Swap') ) :

/**
 *
 */
class Mega_Menu_Image_Swap {

	/**
	 * Constructor
	 *
	 * @since 2,2
	 */
	public function __construct() {

		if ( defined( "MEGAMENU_PRO_IMAGE_SWAP_ENABLED" ) && MEGAMENU_PRO_IMAGE_SWAP_ENABLED === false ) {
			return;
		}

		add_filter( 'megamenu_tabs', array( $this, 'add_image_swap_tab' ), 10, 5 );
		add_filter( 'megamenu_nav_menu_link_attributes', array( $this, 'add_image_swap_data_attribute' ), 10, 3 );

	}


	/**
	 * Add the 'data-image-swap-url' attribute to menu items that have an image assigned.
	 *
	 * @since 2.2
	 * @param array $atts
	 * @param object $item
	 * @param array $args
	 * @return array
	 */
	public function add_image_swap_data_attribute( $atts, $item, $args ) {
		if ( property_exists( $item, 'megamenu_settings' ) ) {
			$settings = $item->megamenu_settings;

			if ( isset( $settings['image_swap']['id'] ) ) {
				$icon_id = $settings['image_swap']['id'];
				$size = isset( $settings['image_swap']['size'] ) ? $settings['image_swap']['size'] : 'thumbnail';
				$icon_url = "";

				if ( $icon_id ) {
					$icon = wp_get_attachment_image_src( $icon_id, $size );
					$icon_url = $icon[0];
				}

				$atts['data-image-swap-url'] = $icon_url;
			}
		}

		return $atts;
	}


	/**
	 * Add the Image Swap tab to the menu item options
	 *
	 * @since 2.2
	 * @param array $tabs
	 * @param int $menu_item_id
	 * @param int $menu_id
	 * @param int $menu_item_depth
	 * @param array $menu_item_meta
	 * @return string
	 */
	public function add_image_swap_tab( $tabs, $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta ) {

		if ( $menu_item_depth == 0 ) {
			return $tabs;
		}

		$icon_id = isset( $menu_item_meta['image_swap']['id'] ) ? $menu_item_meta['image_swap']['id'] : false;
		$size    = isset( $menu_item_meta['image_swap']['size'] ) ? $menu_item_meta['image_swap']['size'] : 'thumbnail';

		$icon_url = "";

		if ( $icon_id ) {
			$icon = wp_get_attachment_image_src( $icon_id, 'thumbnail' );
			$icon_url = $icon[0];
		}

		$sizes = apply_filters(
			'image_size_names_choose',
			array(
				'thumbnail' => __( 'Thumbnail' ),
				'medium'    => __( 'Medium' ),
				'large'     => __( 'Large' ),
				'full'      => __( 'Full Size' ),
			)
		);

		$html  = "<form id='mm_image_swap'>";
		$html .= "    <input type='hidden' name='_wpnonce' value='" . wp_create_nonce('megamenu_edit') . "' />";
		$html .= "    <input type='hidden' name='menu_item_id' value='{$menu_item_id}' />";
		$html .= "    <input type='hidden' name='action' value='mm_save_menu_item_settings' />";
		$html .= "    <h4 class='first'>" . __("Image Swap", "megamenupro") . "</h4>";
		$html .= "    <p class='tab-description'>";
		$html .=          __("Select an image to display in the 'Image Swap Widget' when the users hovers over this menu item.", "megamenupro");
		$html .= "        <a href='https://www.megamenu.com/documentation/image-swap' target='_blank'>" . __("View documentation") . "</a>";
		$html .= "    </p>";
		$html .= "    <table>";
		$html .= "        <tr>";
		$html .= "            <td class='mega-name'>" . __("Image", "megamenupro") . "</td>";
		$html .= "            <td class='mega-value'>";
		$html .= "                <div class='mmm_image_selector' data-src='{$icon_url}' data-field='image_swap_id'></div>";
		$html .= "                <input type='hidden' id='image_swap_id' name='settings[image_swap][id]' value='{$icon_id}' />";
		$html .= "            </td>";
		$html .= "        </tr>";
		$html .= "        <tr>";
		$html .= "            <td class='mega-name'>" . __("Size", "megamenupro") . "</td>";
		$html .= "            <td class='mega-value'>";
		$html .= "                <select name='settings[image_swap][size]'>";

		foreach ( $sizes as $key => $value ) {
			$html .= "<option value='" . esc_attr( $key ) . "' " . selected( $size, $key, false ) . '>' . esc_html( $value ) . '</option>';
		}

		$html .= "                </select>";
		$html .= "            </td>";
		$html .= "        </tr>";
		$html .= "    </table>";
		$html .= get_submit_button( __("Save", "megamenupro") );
		$html .= "</form>";

		$tabs['image_swap'] = array(
			'title' => __("Image Swap", "megamenupro"),
			'content' => $html
		);

		return $tabs;
	}

}

endif;