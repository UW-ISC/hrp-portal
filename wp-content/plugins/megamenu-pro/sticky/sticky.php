<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access
}

if ( ! class_exists('Mega_Menu_Sticky') ) :

/**
 *
 */
class Mega_Menu_Sticky {


    /**
     * Constructor
     *
     * @since 1.0
     */
    public function __construct() {

        add_filter( 'megamenu_wrap_attributes', array( $this, 'add_sticky_attribute' ), 10, 5 );
        add_filter( 'megamenu_scss_variables', array( $this, 'add_sticky_scss_vars'), 10, 4 );
        add_filter( 'megamenu_load_scss_file_contents', array( $this, 'append_sticky_scss'), 10 );
        add_filter( 'megamenu_after_menu_item_settings', array( $this, 'add_menu_item_sticky_options'), 10, 6 );
        add_filter( 'megamenu_default_theme', array($this, 'add_theme_placeholders'), 10 );
        add_filter( 'megamenu_theme_editor_settings', array( $this, 'add_theme_editor_settings' ), 10 );
        add_filter( 'megamenu_location_settings', array( $this, 'add_location_settings' ), 10, 3 );

    }

    /**
     * Filter the settings displayed on the Mega Menu > Menu Locations/Settings page to add the sticky options
     *
     * @param array $options
     * @param string $location
     * @param array $settings
     * @return array
     * @since 2.0.2
     */
    public function add_location_settings($options, $location, $settings ) {

        $options['sticky'] = array(
            'priority' => 20,
            'title' => __( "Sticky", "megamenu-pro" ),
            'settings' => array(
                'sticky_enabled' => array(
                    'priority' => 50,
                    'title' => __( "Enabled", "megamenu-pro" ),
                    'description' => __( "Stick the menu for this location", "megamenu-pro" ),
                    'settings' => array(
                        array(
                            'type' => 'checkbox',
                            'key' => 'sticky_enabled',
                            'value' => $this->get_sticky_setting( $settings, $location, 'sticky_enabled' )
                        )
                    )
                ),
                'sticky_desktop' => array(
                    'priority' => 50,
                    'title' => __( "Stick on", "megamenu-pro" ),
                    'description' => __("IMPORTANT: Only enable this if your menu is not already within a sticky container.", "megamenu-pro"),
                    'settings' => array(
                        array(
                            'title' => __("Desktop", "megamenu-pro"),
                            'type' => 'checkbox',
                            'key' => 'sticky_desktop',
                            'value' => $this->get_sticky_setting( $settings, $location, 'sticky_desktop' )
                        ),
                        array(
                            'title' => __("Mobile", "megamenu-pro"),
                            'type' => 'checkbox',
                            'key' => 'sticky_mobile',
                            'value' => $this->get_sticky_setting( $settings, $location, 'sticky_mobile' )
                        )
                    )
                ),
                'sticky_opacity' => array(
                    'priority' => 50,
                    'title' => __( "Sticky Opacity", "megamenu-pro" ),
                    'description' => __("Set the transparency of the menu when sticky (values 0.2 - 1.0). Default: 1.", "megamenu-pro"),
                    'settings' => array(
                        array(
                            'type' => 'freetext',
                            'key' => 'sticky_opacity',
                            'value' => $this->get_sticky_setting( $settings, $location, 'sticky_opacity' )
                        )
                    )
                ),
                'sticky_offset' => array(
                    'priority' => 50,
                    'title' => __( "Sticky Offset", "megamenu-pro" ),
                    'description' => __("Set the distance between top of window and top of menu when the menu is stuck. Default: 0.", "megamenu-pro"),
                    'settings' => array(
                        array(
                            'type' => 'freetext',
                            'key' => 'sticky_offset',
                            'value' => $this->get_sticky_setting( $settings, $location, 'sticky_offset' )
                        )
                    )
                ),
                'sticky_expand' => array(
                    'priority' => 50,
                    'title' => __( "Expand Background", "megamenu-pro" ),
                    'description' => __("Expand the background of the menu to fill the page width once the menu becomes sticky. Only compatible with Horizontal menus.", "megamenu-pro"),
                    'settings' => array(
                        array(
                            'title' => __("Desktop", "megamenu-pro"),
                            'type' => 'checkbox',
                            'key' => 'sticky_expand',
                            'value' => $this->get_sticky_setting( $settings, $location, 'sticky_expand' )
                        ),
                        array(
                            'title' => __("Mobile", "megamenu-pro"),
                            'type' => 'checkbox',
                            'key' => 'sticky_expand_mobile',
                            'value' => $this->get_sticky_setting( $settings, $location, 'sticky_expand' )
                        )
                    )
                ),
                'sticky_hide_until_scroll_up' => array(
                    'priority' => 50,
                    'title' => __( "Hide Until Scroll Up", "megamenu-pro" ),
                    'description' => __("Hide the menu as the user scrolls down the page, and reveal the menu when the user scrolls up. Only compatible with Horizontal menus.", "megamenu-pro"),
                    'settings' => array(
                        array(
                            'title' => __("Enabled", "megamenu-pro"),
                            'type' => 'checkbox',
                            'key' => 'sticky_hide_until_scroll_up',
                            'value' => $this->get_sticky_setting($settings, $location, 'sticky_hide_until_scroll_up')
                        ),
                        array(
                            'title' => __("Tolerance", "megamenu-pro"),
                            'type' => 'freetext',
                            'key' => 'sticky_hide_until_scroll_up_tolerance',
                            'value' => $this->get_sticky_setting($settings, $location, 'sticky_hide_until_scroll_up_tolerance')
                        ),
                        array(
                            'title' => __("Offset", "megamenu-pro"),
                            'type' => 'freetext',
                            'key' => 'sticky_hide_until_scroll_up_offset',
                            'value' => $this->get_sticky_setting($settings, $location, 'sticky_hide_until_scroll_up_offset')
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
     * @since 1.6
     * @param array $theme
     * @return array
     */
    public function add_theme_placeholders( $theme ) {
        $theme['sticky_menu_height'] = 'off';
        $theme['sticky_menu_transition'] = 'off';
        $theme['sticky_menu_item_link_height'] = 'menu_item_link_height';

        return $theme;
    }


    /**
     * Add sticky menu height option to theme editor
     *
     * @since 1.6
     * @param array $settings
     * @return array
     */
    public function add_theme_editor_settings( $settings ) {
        $new_settings = array(
            'sticky_menu_item_link_height' => array(
                'priority' => 06,
                'title' => __( "Menu Height (Sticky)", "megamenu-pro" ),
                'description' => __( "The height of the menu when sticky.", "megamenu-pro" ),
                'settings' => array(
                    array(
                        'title' => __( "Enabled", "megamenu-pro" ),
                        'type' => 'checkbox',
                        'key' => 'sticky_menu_height'
                    ),
                    array(
                        'title' => __( "Height", "megamenu-pro" ),
                        'type' => 'freetext',
                        'key' => 'sticky_menu_item_link_height',
                        'validation' => 'px'
                    ),
                    array(
                        'title' => __( "Transition", "megamenu-pro" ),
                        'type' => 'checkbox',
                        'key' => 'sticky_menu_transition'
                    ),
                )
            )
        );

        $settings['menu_bar']['settings'] = array_merge($settings['menu_bar']['settings'], $new_settings);

        return $settings;
    }


    /**
     * Add sticky menu item visibility option to the individual menu item settings
     *
     * @since 1.5.2
     */
    public function add_menu_item_sticky_options( $html, $tabs, $menu_item_id, $menu_id, $menu_item_depth, $menu_item_meta ) {

        if ( !isset( $menu_item_meta['sticky_visibility'] ) ) {
            $menu_item_meta['sticky_visibility'] = 'always';
        }

        $return  = '        <tr>';
        $return .= '            <td class="mega-name">';
        $return .=                  __("Visibility in Sticky Menu", "megamenu-pro");
        $return .= '            </td>';
        $return .= '            <td class="mega-value">';
        $return .= '                <select name="settings[sticky_visibility]">';
        $return .= '                    <option value="always" ' . selected( $menu_item_meta['sticky_visibility'], 'always', false ) . '>' . __("Always show", "megamenu-pro") . '</option>';
        $return .= '                    <option value="show" ' . selected( $menu_item_meta['sticky_visibility'], 'show', false ) . '>' . __("Show only when menu is stuck", "megamenu-pro") . '</option>';
        $return .= '                    <option value="hide" ' . selected( $menu_item_meta['sticky_visibility'], 'hide', false ) . '>' . __("Hide when menu is stuck", "megamenu-pro") . '</option>';
        $return .= '                </select>';
        $return .= '            </td>';
        $return .= '        </tr>';

        $html .= $return;

        return $html;
    }


    /**
     *
     */
    public function add_sticky_scss_vars( $vars, $location, $theme, $menu_id ) {

        $settings = get_option('megamenu_settings');

        $opacity = $this->get_sticky_setting( $settings, $location, 'sticky_opacity');

        $vars['sticky_menu_opacity'] = $opacity;

        $expand = $this->get_sticky_setting( $settings, $location, 'sticky_expand');

        $vars['sticky_menu_expand'] = $expand;

        return $vars;

    }


    /**
     * Add the sticky CSS to the main SCSS file
     *
     * @since 1.0
     * @param string $scss
     * @return string
     */
    public function append_sticky_scss( $scss ) {

        $path = trailingslashit( plugin_dir_path( __FILE__ ) ) . 'scss/sticky.scss';

        $contents = file_get_contents( $path );

        return $scss . $contents;

    }


    /**
     * Add the sticky related attributes to the menu wrapper
     */
    public function add_sticky_attribute( $attributes, $menu_id, $menu_settings, $settings, $current_theme_location ) {

        if ( $this->get_sticky_setting( $settings, $current_theme_location, 'sticky_enabled') == 'true' ) {
            $attributes['data-sticky-enabled'] = 'true';
            $attributes['data-sticky-desktop'] = $this->get_sticky_setting( $settings, $current_theme_location, 'sticky_desktop' );
            $attributes['data-sticky-mobile'] = $this->get_sticky_setting( $settings, $current_theme_location, 'sticky_mobile' );
            $attributes['data-sticky-offset'] = $this->get_sticky_setting( $settings, $current_theme_location, 'sticky_offset' );
            $attributes['data-sticky-expand'] = $this->get_sticky_setting( $settings, $current_theme_location, 'sticky_expand' );
            $attributes['data-sticky-expand-mobile'] = $this->get_sticky_setting( $settings, $current_theme_location, 'sticky_expand_mobile' );

            $menu_theme = mmm_get_theme_for_location( $current_theme_location );

            if ( $menu_theme['sticky_menu_height'] == "on" && $menu_theme['sticky_menu_transition'] === "on" ) {
                $attributes['data-sticky-transition'] = 'true';
            } else {
                $attributes['data-sticky-transition'] = 'false';
            }

            if ($this->get_sticky_setting( $settings, $current_theme_location, 'sticky_hide_until_scroll_up' ) == 'true') {
                $attributes['data-sticky-hide'] = $this->get_sticky_setting( $settings, $current_theme_location, 'sticky_hide_until_scroll_up' );
                $attributes['data-sticky-hide-tolerance'] = $this->get_sticky_setting( $settings, $current_theme_location, 'sticky_hide_until_scroll_up_tolerance' );
                $attributes['data-sticky-hide-offset'] = $this->get_sticky_setting( $settings, $current_theme_location, 'sticky_hide_until_scroll_up_offset' );
            }
        }

        return $attributes;
    }



    /**
     * Return a setting, taking into account backwards compatibility (when it was only possible to make a single location sticky)
     * @since 1.4.6
     */
    private function get_sticky_setting( $saved_settings, $location, $setting ) {

        if ( isset( $saved_settings[$location][$setting] ) ) {
            return esc_attr( $saved_settings[$location][$setting] );
        }

        // backwards compatibility from this point onwards
        if ( isset($saved_settings['sticky']['location']) && $setting == 'sticky_enabled' && $location == $saved_settings['sticky']['location'] ) {
            return "true";
        }

        $old_setting_name = substr($setting, 7);

        if ( isset( $saved_settings['sticky'][$old_setting_name]) && $location == $saved_settings['sticky']['location'] ) {
            return esc_attr( $saved_settings['sticky'][$old_setting_name] );
        }
        
        if ( $setting == 'sticky_expand_mobile' && ! isset( $saved_settings[$location]['sticky_expand_mobile'] ) && isset( $saved_settings[$location]['sticky_expand'] ) ) {
            return esc_attr( $saved_settings[$location]['sticky_expand'] );
        }

        // defaults
        $defaults = array(
            'sticky_location' => 'false',
            'sticky_opacity' => '1.0',
            'sticky_desktop' => 'true',
            'sticky_mobile' => 'false',
            'sticky_offset' => '0',
            'sticky_expand' => 'false',
            'sticky_expand_mobile' => 'false',
            'sticky_hide_until_scroll_up' => 'false',
            'sticky_hide_until_scroll_up_tolerance' => '10',
            'sticky_hide_until_scroll_up_offset' => '0'
        );


        if ( isset( $defaults[$setting] ) ) {
            return esc_attr( $defaults[$setting] );
        }

        return 'false';
    }
}

endif;