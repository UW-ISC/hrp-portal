<?php
/**
 * Bricks Builder element for rendering a Max Mega Menu location.
 *
 * @since   3.10
 * @package megamenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MaxMegaMenu_Bricks_Location extends \Bricks\Element {

	public $category     = 'general';
	public $name         = 'maxmegamenu-location';
	public $icon         = 'ti-layout-menu-full';
	public $css_selector = '.maxmegamenu-bricks-location';

	public function get_label() {
		return esc_html__( 'Max Mega Menu', 'megamenu' );
	}

	public function get_keywords() {
		return [ 'menu', 'navigation', 'mega menu', 'nav' ];
	}

	public function set_controls() {
		$registered_menus = get_registered_nav_menus();
		$options          = [ '' => esc_html__( 'Select a location', 'megamenu' ) ];

		foreach ( $registered_menus as $slug => $label ) {
			$options[ $slug ] = $label;
		}

		$this->controls['location'] = [
			'tab'       => 'content',
			'label'     => esc_html__( 'Menu Location', 'megamenu' ),
			'type'      => 'select',
			'options'   => $options,
			'clearable' => false,
			'default'   => '',
		];
	}

	public function render() {
		$location = ! empty( $this->settings['location'] )
			? sanitize_key( (string) $this->settings['location'] )
			: '';

		if ( ! $location ) {
			echo '<p class="maxmegamenu-bricks-placeholder">'
				. esc_html__( 'Select a menu location in the element settings.', 'megamenu' )
				. '</p>';
			return;
		}

		$menu_html = wp_nav_menu( [ 'theme_location' => $location, 'echo' => false ] );

		if ( ! $menu_html ) {
			echo '<p class="maxmegamenu-bricks-placeholder">'
				. esc_html__( 'No menu assigned to this location.', 'megamenu' )
				. '</p>';
			return;
		}

		echo $menu_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
