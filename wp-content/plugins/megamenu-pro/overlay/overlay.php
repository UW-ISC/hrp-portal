<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access
}

if ( ! class_exists('Mega_Menu_Overlay') ) :

/**
 *
 */
class Mega_Menu_Overlay {


    /**
     * Constructor
     *
     * @since 2.4
     */
    public function __construct() {

        add_filter( 'megamenu_wrap_attributes', array( $this, 'add_overlay_attribute' ), 10, 5 );
        add_filter( 'megamenu_scss_variables', array( $this, 'add_overlay_scss_vars'), 10, 4 );
        add_filter( 'megamenu_load_scss_file_contents', array( $this, 'append_overlay_scss'), 10 );
        add_filter( 'megamenu_submitted_settings_meta', array( $this, 'filter_submitted_settings'), 10);
        add_filter( 'megamenu_default_theme', array($this, 'add_theme_placeholders'), 10 );
        add_filter( 'megamenu_theme_editor_settings', array( $this, 'add_theme_editor_settings' ), 10 );
        add_filter( 'megamenu_location_settings', array( $this, 'add_location_settings' ), 10, 3 );

    }


    /**
     * Filter the settings displayed on the Mega Menu > Menu Locations/Settings page to add the overlay options
     *
     * @param array $options
     * @param string $location
     * @param array $settings
     * @return array
     * @since 2.4
     */
    public function add_location_settings($options, $location, $settings ) {

        $options['overlay'] = array(
            'priority' => 20,
            'title' => __( "Page Overlay", "megamenu-pro" ),
            'settings' => array(
                'overlay_enabled' => array(
                    'priority' => 50,
                    'title' => __( "Enabled", "megamenu-pro" ),
                    'description' => __( "Dim the page background when the menu is hovered over. The overlay color can be set under Menu Themes > General Settings.", "megamenu-pro" ),
                    'settings' => array(
                        array(
                            'title' => __("Desktop", "megamenu-pro"),
                            'type' => 'checkbox',
                            'key' => 'overlay_enabled_desktop',
                            'value' => isset($settings[$location]['overlay_enabled_desktop']) ? $settings[$location]['overlay_enabled_desktop'] : 'false'
                        ),
                        array(
                            'title' => __("Mobile", "megamenu-pro"),
                            'type' => 'checkbox',
                            'key' => 'overlay_enabled_mobile',
                            'value' => isset($settings[$location]['overlay_enabled_mobile']) ? $settings[$location]['overlay_enabled_mobile'] : 'false'
                        )
                    )
                )
            )
        );

        return $options;
    }


    /**
     * Insert theme placeholder values.
     *
     * @since 2.4
     * @param array $theme
     * @return array
     */
    public function add_theme_placeholders( $theme ) {
        $theme['overlay_color'] = 'rgba(0,0,0,0.3)';
        return $theme;
    }


    /**
     * Add page overlay settings to the theme editor
     *
     * @since 2.4
     * @param array $settings
     * @return array
     */
    public function add_theme_editor_settings( $settings ) {
        $new_settings = array(
            'overlay' => array(
                'priority' => 58,
                'title' => __( "Page Overlay", "megamenu-pro" ),
                'description' => __( "Set the page overlay colour. The Page Overlay option can be enabled in the Menu Location settings.", "megamenu-pro" ),
                'settings' => array(
                    array(
                        'title' => __( "Color", "megamenu-pro" ),
                        'type' => 'color',
                        'key' => 'overlay_color'
                    ),
                )
            )
        );

        $settings['general']['settings'] = array_merge($settings['general']['settings'], $new_settings);

        return $settings;
    }

    /**
     * Make sure 'overlay enabled' really is set to false if the checkbox is unchecked.
     * @since 2.4
     */
    public function filter_submitted_settings( $settings ) {
        if ( is_array( $settings ) ) {
            foreach ( $settings as $location => $vars ) {
                if ( ! isset( $vars['overlay_enabled_desktop'] ) ) {
                    $settings[$location]['overlay_enabled'] = 'false';
                }
                if ( ! isset( $vars['overlay_enabled_mobile'] ) ) {
                    $settings[$location]['overlay_enabled_mobile'] = 'false';
                }
            }
        }

        return $settings;
    }
    
    /**
     *
     * @since 2.4
     */
    public function add_overlay_scss_vars( $vars, $location, $theme, $menu_id ) {

        $vars['overlay_enabled_desktop'] = 'false';
        $vars['overlay_enabled_mobile'] = 'false';

        $settings = get_option('megamenu_settings');

        if ( isset( $settings[$location]['overlay_enabled_desktop'] ) && $settings[$location]['overlay_enabled_desktop'] == 'true' ) {
            $vars['overlay_enabled_desktop'] = 'true';
        }
  
        if ( isset( $settings[$location]['overlay_enabled_mobile'] ) && $settings[$location]['overlay_enabled_mobile'] == 'true' ) {
            $vars['overlay_enabled_mobile'] = 'true';
        }
  
        return $vars;
    }


    /**
     * Add the overlay CSS to the main SCSS file
     *
     * @since 2.4
     * @param string $scss
     * @return string
     */
    public function append_overlay_scss( $scss ) {

        $path = trailingslashit( plugin_dir_path( __FILE__ ) ) . 'scss/overlay.scss';

        $contents = file_get_contents( $path );

        return $scss . $contents;

    }


    /**
     * Add the overlay related attributes to the menu wrapper
     * @since 2.4
     */
    public function add_overlay_attribute( $attributes, $menu_id, $menu_settings, $settings, $current_theme_location ) {

        $attributes['data-overlay-desktop'] = isset( $menu_settings['overlay_enabled_desktop'] ) ? $menu_settings['overlay_enabled_desktop'] : 'false';
        $attributes['data-overlay-mobile'] = isset( $menu_settings['overlay_enabled_mobile'] ) ? $menu_settings['overlay_enabled_mobile'] : 'false';

        return $attributes;
    }

}

endif;