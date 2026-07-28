<?php
/**
 * Breakdance element for rendering a Max Mega Menu location.
 *
 * @since   3.10.5
 * @package megamenu
 */

namespace MaxMegaMenuBreakdance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use function Breakdance\Elements\c;
use function Breakdance\Elements\controlSection;

class MaxMegaMenuLocation extends \Breakdance\Elements\Element {

	public static function uiIcon() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 127 127"><g transform="translate(0,127) scale(0.1,-0.1)" fill="currentColor" stroke="none"><path d="M330 1127 l-245 -143 -7 -180 c-5 -98 -7 -253 -5 -344 l2 -165 130 -76 c295 -173 345 -204 345 -211 0 -4 24 -8 54 -8 48 0 65 7 167 66 223 129 376 224 390 240 18 21 26 593 10 637 -12 30 -73 72 -276 190 -71 42 -152 90 -179 106 -36 23 -60 31 -95 31 -41 0 -72 -16 -291 -143z m410 -77 c131 -76 141 -85 115 -105 -43 -31 -221 -125 -239 -125 -21 0 -217 112 -235 134 -8 10 -6 17 7 28 37 32 207 128 226 128 12 0 68 -27 126 -60z m-361 -279 c88 -50 181 -99 207 -110 l47 -21 121 69 c168 96 255 141 272 141 12 0 14 -38 14 -228 l0 -228 -77 -47 -78 -47 -7 146 c-3 80 -8 147 -10 149 -2 2 -53 -25 -113 -60 -61 -35 -119 -64 -129 -65 -11 0 -70 27 -132 60 -62 33 -115 60 -117 60 -3 0 -8 -63 -12 -140 -4 -77 -11 -140 -16 -140 -4 0 -39 19 -78 42 l-70 42 -3 126 c-4 182 1 340 12 340 5 0 81 -40 169 -89z m195 -458 l55 -27 41 28 c23 15 48 25 56 22 15 -6 20 -79 8 -111 -7 -18 -94 -65 -121 -65 -22 0 -83 35 -100 58 -18 22 -15 122 3 122 2 0 28 -12 58 -27z"/></g></svg>';
	}

	public static function name() {
		return 'Max Mega Menu';
	}

	public static function className() {
		return 'maxmegamenu-breakdance-location';
	}

	public static function category() {
		return 'site';
	}

	public static function slug() {
		return __CLASS__;
	}

	public static function tag() {
		return 'div';
	}

	public static function template() {
		return '%%SSR%%';
	}

	public static function contentControls() {
		$registered_menus = get_registered_nav_menus();
		$items            = [ [ 'value' => '', 'text' => __( 'Select a location', 'megamenu' ) ] ];

		foreach ( $registered_menus as $slug => $label ) {
			$dot     = max_mega_menu_is_enabled( $slug ) ? '🟢 ' : '⚫ ';
			$items[] = [ 'value' => $slug, 'text' => $dot . $label ];
		}

		return [
			controlSection(
				'menu',
				__( 'Menu', 'megamenu' ),
				[
					c(
						'location',
						__( 'Menu Location', 'megamenu' ),
						[],
						[ 'type' => 'dropdown', 'items' => $items, 'layout' => 'vertical' ],
						false,
						false
					),
				]
			),
		];
	}

	public static function defaultProperties() {
		foreach ( array_keys( get_registered_nav_menus() ) as $slug ) {
			if ( max_mega_menu_is_enabled( $slug ) ) {
				return [ 'content' => [ 'menu' => [ 'location' => $slug ] ] ];
			}
		}
		return false;
	}

	public static function nestingRule() {
		return [ 'type' => 'final' ];
	}

	public static function spacingBars() {
		return [
			[
				'location'            => 'outside-top',
				'cssProperty'         => 'margin-top',
				'affectedPropertyPath' => 'design.spacing.margin_top.%%BREAKPOINT%%',
			],
			[
				'location'            => 'outside-bottom',
				'cssProperty'         => 'margin-bottom',
				'affectedPropertyPath' => 'design.spacing.margin_bottom.%%BREAKPOINT%%',
			],
		];
	}

	public static function propertyPathsToSsrElementWhenValueChanges() {
		return [ 'content.menu.location' ];
	}
}
