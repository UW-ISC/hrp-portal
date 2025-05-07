<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor Max Mega Menu Widget.
 *
 *
 * @since 3.5
 */
class Elementor_Max_Mega_Menu_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve list widget name.
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
	 * Retrieve list widget title.
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
	 * Retrieve list widget icon.
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
	 * Retrieve the list of categories the list widget belongs to.
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
	 * Retrieve the list of keywords the list widget belongs to.
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
	 * Retrieve a URL where the user can get more information about the widget.
	 *
	 * @since 3.5
	 * @access public
	 * @return string Widget help URL.
	 */
	public function get_custom_help_url() {
		return 'https://www.megamenu.com/documentation/elementor/';
	}

	/**
	 * Register list widget controls.
	 *
	 * Add input fields to allow the user to customize the widget settings.
	 *
	 * @since 3.5
	 * @access protected
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
				array(
					'label'        => __( 'Choose Location', 'megamenu' ),
					'type'         => \Elementor\Controls_Manager::SELECT,
					'options'      => $locations,
					'default'      => array_keys( $locations )[0],
					'save_default' => true
				)
			);
		} else {
			$this->add_control(
				'location',
				array(
					'type'            => \Elementor\Controls_Manager::RAW_HTML,
					'raw'             => sprintf( __( 'Go to the <a href="%s">Menu Locations</a> page to create your first menu location.', 'megamenu' ), admin_url( 'admin.php?page=maxmegamenu' ) ),
					'separator'       => 'after',
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				)
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
				array(
					'theme_location' => $settings['location'],
					'echo'           => true,
				)
			);
		}
	}
}