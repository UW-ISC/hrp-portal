<?php

/**
 * Prevent Max Mega Menu from unbinding the smoothscroll events added by the Zerif theme.
 *
 * @since  1.0
 * @param  array  $attributes            Menu wrapper HTML attributes.
 * @param  int    $menu_id               Menu term ID.
 * @param  array  $menu_settings         Menu-level settings.
 * @param  array  $settings              Plugin-level settings.
 * @param  string $current_theme_location Current theme location identifier.
 * @return array  Modified attributes with data-unbind set to "false".
 */
if ( ! function_exists('megamenu_dont_unbind_menu_events') ) {
    function megamenu_dont_unbind_menu_events($attributes, $menu_id, $menu_settings, $settings, $current_theme_location) {

        $attributes['data-unbind'] = "false";

        return $attributes;
    }
}
add_filter("megamenu_wrap_attributes", "megamenu_dont_unbind_menu_events", 11, 5);