<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor Max Mega Menu Widget. Allows users to embed a Max Mega Menu
 * location within an Elementor page layout.
 *
 * @since   3.5
 * @package MegaMenu
 */
class Elementor_Max_Mega_Menu_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since 3.5
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'maxmegamenu';
	}

	/**
	 * Get widget title.
	 *
	 * @since 3.5
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Max Mega Menu', 'elementor-list-widget' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since 3.5
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-nav-menu';
	}

	/**
	 * Get widget categories.
	 *
	 * @since 3.5
	 * @access public
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'general' ];
	}

	/**
	 * Get widget keywords.
	 *
	 * @since 3.5
	 * @access public
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'menu', 'nav', 'max', 'mega', 'menu' ];
	}

	/**
	 * Get custom help URL.
	 *
	 * @since 3.5
	 * @access public
	 * @return string Widget help URL.
	 */
	public function get_custom_help_url() {
		return 'https://www.megamenu.com/documentation/elementor/';
	}

	/**
	 * Register widget controls.
	 *
	 * @since 3.5
	 * @access protected
	 * @return void
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'style_content_section',
			[
				'label' => esc_html__( 'Menu Location', 'megamenu' )
			]
		);

		$locations = get_registered_nav_menus();

		if ( ! empty( $locations ) ) {
			$this->add_control(
				'location',
				[
					'label'        => __( 'Choose Location', 'megamenu' ),
					'type'         => \Elementor\Controls_Manager::SELECT,
					'options'      => $locations,
					'default'      => array_keys( $locations )[0],
					'save_default' => true
				]
			);
		} else {
			$locations_admin = admin_url( 'admin.php?page=maxmegamenu' );
			$locations_link_processor = new WP_HTML_Tag_Processor( '<a>' . esc_html__( 'Menu Locations', 'megamenu' ) . '</a>' );
			if ( $locations_link_processor->next_tag( 'a' ) ) {
				$locations_link_processor->set_attribute( 'href', $locations_admin );
			}
			$locations_link = $locations_link_processor->get_updated_html();
			$this->add_control(
				'location',
				[
					'type'            => \Elementor\Controls_Manager::RAW_HTML,
					'raw'             => wp_kses(
						sprintf(
							/* translators: %s: link to the Menu Locations admin page */
							__( 'Go to the %s page to create your first menu location.', 'megamenu' ),
							$locations_link
						),
						[
							'a' => [
								'href' => true,
							],
						]
					),
					'separator'       => 'after',
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				]
			);
		}

		$this->end_controls_section();

	}

	/**
	 * Render list widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 3.5
	 * @access protected
	 */
	protected function render() {
		$available_menus = get_registered_nav_menus();

		if ( ! $available_menus ) {
			return;
		}

		$settings = $this->get_active_settings();

		if ( ! empty( $settings['location'] ) ) {
			wp_nav_menu(
				[
					'theme_location' => $settings['location'],
					'echo'           => true,
				]
			);
		}
	}
}