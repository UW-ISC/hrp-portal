<?php
/**
 * Breakdance integration bootstrap. Included when the Breakdance plugin is active.
 *
 * @package megamenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/admin-notice.php';
require_once __DIR__ . '/location-settings-display-options.php';

// Breakdance fires `breakdance_loaded` inside its own `plugins_loaded` callback, which
// runs before ours (alphabetical plugin order). Registering on that hook would be too
// late. Instead, include the element class directly — Breakdance discovers elements via
// get_declared_classes() at builder-request time, so it will be found.
if ( class_exists( '\Breakdance\Elements\Element' ) ) {
	require_once __DIR__ . '/location/element.php';
}
