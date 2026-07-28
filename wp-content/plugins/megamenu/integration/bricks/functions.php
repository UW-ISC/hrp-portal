<?php
/**
 * Bricks Builder integration bootstrap. Only included when the active template is 'bricks'.
 *
 * @package megamenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/admin-notice.php';
require_once __DIR__ . '/location-settings-display-options.php';

add_action(
	'init',
	function () {
		if ( ! class_exists( '\Bricks\Elements' ) ) {
			return;
		}
		\Bricks\Elements::register_element( __DIR__ . '/location/element.php' );
	},
	11
);
