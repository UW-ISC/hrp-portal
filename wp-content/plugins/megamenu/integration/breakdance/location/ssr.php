<?php
/**
 * @var array $propertiesData
 */

$location = sanitize_key( $propertiesData['content']['menu']['location'] ?? '' );

if ( ! $location ) {
	echo '<p class="maxmegamenu-breakdance-placeholder">'
		. esc_html__( 'Select a menu location in the element settings.', 'megamenu' )
		. '</p>';
} else {
	$menu_html = wp_nav_menu( [ 'theme_location' => $location, 'echo' => false, 'fallback_cb' => false ] );

	if ( ! $menu_html ) {
		echo '<p class="maxmegamenu-breakdance-placeholder">'
			. esc_html__( 'No menu assigned to this location.', 'megamenu' )
			. '</p>';
	} else {
		echo $menu_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
