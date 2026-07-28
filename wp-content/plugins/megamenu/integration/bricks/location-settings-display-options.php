<?php
/**
 * Location settings dialog: Display Options tab — Bricks element row.
 *
 * @package megamenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prepend the Bricks element display option when Bricks integration is active.
 *
 * @param array  $settings         Location settings schema from core.
 * @param string $location         Menu location slug.
 * @param array  $plugin_settings  Full megamenu_settings option.
 * @return array
 */
function megamenu_bricks_filter_location_settings_display_module_option( $settings, $location, $plugin_settings ) {
	if ( ! apply_filters( 'megamenu_bricks_location_settings_display_module_option_enabled', true, $location, $settings, $plugin_settings ) ) {
		return $settings;
	}

	if ( empty( $settings['output_options']['settings'] ) || ! is_array( $settings['output_options']['settings'] ) ) {
		return $settings;
	}

	$default_group = [
		'priority'    => 5,
		'title'       => __( 'Bricks', 'megamenu' ),
		'description' => __( 'Display this location using the Bricks Builder.', 'megamenu' ),
		'settings'    => [
			[
				'type'  => 'megamenu_bricks_location_display_option',
				'key'   => 'megamenu_bricks_location_display_option',
				'value' => $location,
			],
		],
	];

	/**
	 * Filter the whole "Bricks" display-options group (title, description, nested settings rows).
	 *
	 * Return an empty array to skip registering this option for a location.
	 *
	 * @param array  $group             Group definition (priority, title, description, settings).
	 * @param string $location          Menu location slug.
	 * @param array  $plugin_settings   Full megamenu_settings option.
	 * @param array  $settings          Full location settings schema after core assembly.
	 */
	$group = apply_filters( 'megamenu_bricks_location_settings_display_module_option', $default_group, $location, $plugin_settings, $settings );

	if ( ! is_array( $group ) || [] === $group ) {
		return $settings;
	}

	$settings['output_options']['settings'] = array_merge(
		[ 'megamenu_bricks_element' => $group ],
		$settings['output_options']['settings']
	);

	return $settings;
}

add_filter( 'megamenu_location_settings', 'megamenu_bricks_filter_location_settings_display_module_option', 10, 3 );

/**
 * Instructional copy in the value column.
 *
 * @param string $key      Setting key.
 * @param string $location Menu location slug.
 * @param array  $setting  Full setting definition.
 */
function megamenu_bricks_action_print_location_display_option( $key, $location, $setting = [] ) {
	$loc = isset( $setting['value'] ) ? (string) $setting['value'] : (string) $location;

	/**
	 * Text shown for the Bricks display option.
	 *
	 * @param string $text     Default instructional text.
	 * @param string $loc      Location slug from the setting row.
	 * @param string $location Dialog location slug.
	 * @param array  $setting  Full setting definition.
	 */
	$text = apply_filters(
		'megamenu_bricks_location_display_option_text',
		__( "Add the 'Max Mega Menu' element to your template using the Bricks Builder.", 'megamenu' ),
		$loc,
		$location,
		$setting
	);

	$class = 'mmm-location-output-instruction mega-setting-megamenu_bricks_location_display_option';

	/**
	 * Filter the root element `class` attribute for the Bricks display option.
	 *
	 * @param string $class    CSS class list.
	 * @param string $location Menu location slug.
	 * @param array  $setting  Full setting definition.
	 */
	$class = apply_filters( 'megamenu_bricks_location_display_option_textarea_class', $class, $location, $setting );

	echo '<p class="' . esc_attr( $class ) . '">' . esc_html( $text ) . '</p>';
}

add_action( 'megamenu_print_location_option_megamenu_bricks_location_display_option', 'megamenu_bricks_action_print_location_display_option', 10, 3 );
