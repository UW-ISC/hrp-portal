<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Page' ) ) :

	/**
	 * Handles the Max Mega Menu admin page shell, including the menu structure and shared header.
	 *
	 * @since   1.0
	 * @package MegaMenu
	 */
	class Mega_Menu_Page {

		/**
		 * Constructor. Registers admin menu and script enqueueing hooks.
		 *
		 * @since  1.0
		 * @return void
		 */
		public function __construct() {
			add_action( 'admin_menu', [ $this, 'megamenu_settings_page' ] );
			add_action( 'megamenu_admin_scripts', [ $this, 'enqueue_scripts' ] );
		}


		/**
		 * “Not installed” link for the Pro extension row in the header version line.
		 *
		 * @since 3.9.0
		 * @return string
		 */
		private function get_pro_extension_not_installed_link_html() {
			$processor = new WP_HTML_Tag_Processor( '<a>not installed</a>' );
			if ( $processor->next_tag( 'a' ) ) {
				$processor->set_attribute( 'href', 'https://www.megamenu.com/upgrade/?utm_source=free&utm_medium=settings&utm_campaign=pro' );
				$processor->set_attribute( 'target', '_mmmpro' );
			}
			return $processor->get_updated_html();
		}


		/**
		 * On WordPress 7.2 and later, an icon from the WordPress icon library for a page navigation tab, matching the new
		 * admin menu icons. Older versions (and tabs without one) keep their dashicon, added in CSS.
		 *
		 * @since 3.10.10
		 * @param string $tab Tab key.
		 * @return string SVG markup, or an empty string.
		 */
		private function get_page_navigation_icon( $tab ) {
			if ( ! function_exists( 'wp_is_valid_icon_name' ) || ! function_exists( 'wp_get_icon' ) ) {
				return '';
			}

			$icons = apply_filters(
				'megamenu_page_navigation_icons',
				[
					'menu_locations'   => 'core-admin/map-marker',
					'theme_editor'     => 'core-admin/brush',
					'general_settings' => 'core-admin/settings',
					'tools'            => 'core-admin/tool',
					'license'          => 'core/key',
					'licence'          => 'core/key',
				]
			);

			return isset( $icons[ $tab ] ) ? wp_get_icon( $icons[ $tab ] ) : '';
		}


		/**
		 * The admin menu icon: on WordPress 7.2 and later the outlined icon registered by Mega_Menu_Admin_Icons, otherwise
		 * the filled SVG.
		 *
		 * @since 3.10.10
		 * @return string Icon name or data URL.
		 */
		private function get_admin_menu_icon() {
			if ( function_exists( 'wp_is_valid_icon_name' ) && function_exists( 'wp_get_icon' ) && '' !== wp_get_icon( 'megamenu/logo' ) ) {
				return 'megamenu/logo';
			}

			return $this->get_admin_menu_icon_data_url();
		}


		/**
		 * The plugin icon as a single colour SVG data URL, for the top level admin menu item before WordPress 7.2
		 * (WordPress recolours it to match the admin colour scheme).
		 *
		 * @since 3.9.0
		 * @return string
		 */
		private function get_admin_menu_icon_data_url() {
			$svg = 'PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9Ii00MCAtMjIuMzcgMzQwIDM0MCI+PHBhdGggZmlsbD0iYmxhY2siIGZpbGwtcnVsZT0iZXZlbm9kZCIgZD0iTTEyMiwyLjE0QTE2LDE2IDAgMCAxIDEzOCwyLjE0TDI1Miw2Ny45NkExNiwxNiAwIDAgMSAyNjAsODEuODJMMjYwLDIxMy40NUExNiwxNiAwIDAgMSAyNTIsMjI3LjMxTDEzOCwyOTMuMTNBMTYsMTYgMCAwIDEgMTIyLDI5My4xM0w4LDIyNy4zMUExNiwxNiAwIDAgMSAwLDIxMy40NUwwLDgxLjgyQTE2LDE2IDAgMCAxIDgsNjcuOTZaTTEyOC41MCwzOC44MUEzLDMgMCAwIDEgMTMxLjUwLDM4LjgxTDE4NS41MCw2OS45OEEzLDMgMCAwIDEgMTg1LjUwLDc1LjE4TDEzMS41MCwxMDYuMzZBMywzIDAgMCAxIDEyOC41MCwxMDYuMzZMNzQuNTAsNzUuMThBMywzIDAgMCAxIDc0LjUwLDY5Ljk4Wk0zNC41MCw5Mi41MEEzLDMgMCAwIDAgMzAsOTUuMTBMMzAsMTk3Ljg3QTMsMyAwIDAgMCAzMS41MCwyMDAuNDZMNjUuNTAsMjIwLjA5QTMsMyAwIDAgMCA3MCwyMTcuNTBMNzAsMTU4LjYxQTMsMyAwIDAgMSA3NC41MCwxNTYuMDFMMTMwLDE4OC4wNUwxODUuNTAsMTU2LjAxQTMsMyAwIDAgMSAxOTAsMTU4LjYxTDE5MCwyMTcuNTBBMywzIDAgMCAwIDE5NC41MCwyMjAuMDlMMjI4LjUwLDIwMC40NkEzLDMgMCAwIDAgMjMwLDE5Ny44N0wyMzAsOTUuMTBBMywzIDAgMCAwIDIyNS41MCw5Mi41MEwxMzEuNTAsMTQ2Ljc3QTMsMyAwIDAgMSAxMjguNTAsMTQ2Ljc3Wk0xMDAsMjE2LjM0QTMsMyAwIDAgMSAxMDQuNTAsMjEzLjc0TDEzMCwyMjguNDZMMTU1LjUwLDIxMy43NEEzLDMgMCAwIDEgMTYwLDIxNi4zNEwxNjAsMjM4LjI4QTMsMyAwIDAgMSAxNTguNTAsMjQwLjg4TDEzMCwyNTcuMzNMMTAxLjUwLDI0MC44OEEzLDMgMCAwIDEgMTAwLDIzOC4yOFoiLz48L3N2Zz4=';

			return 'data:image/svg+xml;base64,' . $svg;
		}


		/**
		 * The plugin logo (icon and name) as a data URL, for the settings page header.
		 *
		 * @since 3.10.9
		 * @return string
		 */
		private function get_header_logo_data_url() {
			$svg = 'PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI5NjgiIGhlaWdodD0iMjk1IiB2aWV3Qm94PSIwIDAgOTY4IDI5NS4yNyI+PGRlZnM+PGNsaXBQYXRoIGlkPSJtbW0taGV4Ij48cGF0aCBkPSJNMTIyLDIuMTRBMTYsMTYgMCAwIDEgMTM4LDIuMTRMMjUyLDY3Ljk2QTE2LDE2IDAgMCAxIDI2MCw4MS44MkwyNjAsMjEzLjQ1QTE2LDE2IDAgMCAxIDI1MiwyMjcuMzFMMTM4LDI5My4xM0ExNiwxNiAwIDAgMSAxMjIsMjkzLjEzTDgsMjI3LjMxQTE2LDE2IDAgMCAxIDAsMjEzLjQ1TDAsODEuODJBMTYsMTYgMCAwIDEgOCw2Ny45NloiLz48L2NsaXBQYXRoPjxtYXNrIGlkPSJtbW0tY3V0IiBtYXNrVW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4PSIwIiB5PSIwIiB3aWR0aD0iMjYwIiBoZWlnaHQ9IjI5NS4yNyI+PHJlY3Qgd2lkdGg9IjI2MCIgaGVpZ2h0PSIyOTUuMjciIGZpbGw9IiNmZmYiLz48cGF0aCBmaWxsPSIjMDAwIiBkPSJNMTI4LjUwLDM4LjgxQTMsMyAwIDAgMSAxMzEuNTAsMzguODFMMTg1LjUwLDY5Ljk4QTMsMyAwIDAgMSAxODUuNTAsNzUuMThMMTMxLjUwLDEwNi4zNkEzLDMgMCAwIDEgMTI4LjUwLDEwNi4zNkw3NC41MCw3NS4xOEEzLDMgMCAwIDEgNzQuNTAsNjkuOThaTTM0LjUwLDkyLjUwQTMsMyAwIDAgMCAzMCw5NS4xMEwzMCwxOTcuODdBMywzIDAgMCAwIDMxLjUwLDIwMC40Nkw2NS41MCwyMjAuMDlBMywzIDAgMCAwIDcwLDIxNy41MEw3MCwxNTguNjFBMywzIDAgMCAxIDc0LjUwLDE1Ni4wMUwxMzAsMTg4LjA1TDE4NS41MCwxNTYuMDFBMywzIDAgMCAxIDE5MCwxNTguNjFMMTkwLDIxNy41MEEzLDMgMCAwIDAgMTk0LjUwLDIyMC4wOUwyMjguNTAsMjAwLjQ2QTMsMyAwIDAgMCAyMzAsMTk3Ljg3TDIzMCw5NS4xMEEzLDMgMCAwIDAgMjI1LjUwLDkyLjUwTDEzMS41MCwxNDYuNzdBMywzIDAgMCAxIDEyOC41MCwxNDYuNzdaTTEwMCwyMTYuMzRBMywzIDAgMCAxIDEwNC41MCwyMTMuNzRMMTMwLDIyOC40NkwxNTUuNTAsMjEzLjc0QTMsMyAwIDAgMSAxNjAsMjE2LjM0TDE2MCwyMzguMjhBMywzIDAgMCAxIDE1OC41MCwyNDAuODhMMTMwLDI1Ny4zM0wxMDEuNTAsMjQwLjg4QTMsMyAwIDAgMSAxMDAsMjM4LjI4WiIvPjwvbWFzaz48L2RlZnM+PGcgY2xpcC1wYXRoPSJ1cmwoI21tbS1oZXgpIiBtYXNrPSJ1cmwoI21tbS1jdXQpIj48cGF0aCBmaWxsPSIjMWE4MmVhIiBkPSJNMTMwLC0yLjQ4TDI2MCw3Mi41OEwxMzAsMTQ3LjY0TDAsNzIuNThaIi8+PHBhdGggZmlsbD0iIzBkNGY5MSIgZD0iTTAsNzIuNThMMTMwLDE0Ny42NEwxMzAsMjk3Ljc1TDAsMjIyLjY5WiIvPjxwYXRoIGZpbGw9IiMxNDcwZDAiIGQ9Ik0yNjAsNzIuNThMMTMwLDE0Ny42NEwxMzAsMjk3Ljc1TDI2MCwyMjIuNjlaIi8+PC9nPjxwYXRoIGZpbGw9IiMxZTI5M2IiIGQ9Ik0zMjcuMyAxMzMuMlY3Mi42SDM0Mi4xVjc4UTM0NS4yIDc0LjcgMzQ5LjUgNzIuOFEzNTMuOCA3MC45IDM1OC43IDcwLjlRMzY1LjEgNzAuOSAzNzAuNCA3My45UTM3NS43IDc2LjggMzc4LjggODEuOFEzODIuMSA3Ni44IDM4Ny4zIDczLjlRMzkyLjYgNzAuOSAzOTguOSA3MC45UTQwNS41IDcwLjkgNDExIDc0LjFRNDE2LjQgNzcuNCA0MTkuNyA4Mi44UTQyMi45IDg4LjIgNDIyLjkgOTQuOVYxMzMuMkg0MDguMVY5OC45UTQwOC4xIDk1LjMgNDA2LjQgOTIuNFE0MDQuNiA4OS40IDQwMS43IDg3LjZRMzk4LjkgODUuOSAzOTUuMyA4NS45UTM5MS44IDg1LjkgMzg4LjkgODcuNlEzODUuOSA4OS4zIDM4NC4yIDkyLjJRMzgyLjUgOTUuMiAzODIuNSA5OC45VjEzMy4ySDM2Ny43Vjk4LjlRMzY3LjcgOTUuMiAzNjYgOTIuMlEzNjQuMyA4OS4zIDM2MS4zIDg3LjZRMzU4LjQgODUuOSAzNTQuOSA4NS45UTM1MS40IDg1LjkgMzQ4LjUgODcuNlEzNDUuNSA4OS40IDM0My44IDkyLjRRMzQyLjEgOTUuMyAzNDIuMSA5OC45VjEzMy4yWk00ODAuOCA3Mi42SDQ5NS42VjEzMy4ySDQ4MC43TDQ4MC4xIDEyNS43UTQ3Ny42IDEyOS44IDQ3My41IDEzMi4zUTQ2OS40IDEzNC44IDQ2My44IDEzNC44UTQ1Ny4xIDEzNC44IDQ1MS4yIDEzMi4zUTQ0NS4zIDEyOS43IDQ0MC45IDEyNS4yUTQzNi40IDEyMC43IDQzMy45IDExNC45UTQzMS40IDEwOSA0MzEuNCAxMDIuMlE0MzEuNCA5NS43IDQzMy44IDkwUTQzNi4yIDg0LjQgNDQwLjUgODAuMVE0NDQuOCA3NS44IDQ1MC40IDczLjNRNDU2LjEgNzAuOSA0NjIuNSA3MC45UTQ2OC42IDcwLjkgNDczLjMgNzMuNVE0NzguMSA3Ni4yIDQ4MS40IDgwLjNaTTQ2My41IDEyMC42UTQ2OC4yIDEyMC42IDQ3MS44IDExOC4yUTQ3NS41IDExNS44IDQ3Ny41IDExMS44UTQ3OS42IDEwNy44IDQ3OS42IDEwMi45UTQ3OS42IDk4IDQ3Ny41IDk0UTQ3NS40IDg5LjkgNDcxLjggODcuNlE0NjguMiA4NS4yIDQ2My41IDg1LjJRNDU4LjggODUuMiA0NTQuOSA4Ny42UTQ1MS4xIDkwIDQ0OC44IDk0UTQ0Ni42IDk4IDQ0Ni42IDEwMi45UTQ0Ni42IDEwNy44IDQ0OC45IDExMS44UTQ1MS4yIDExNS44IDQ1NSAxMTguMlE0NTguOSAxMjAuNiA0NjMuNSAxMjAuNlpNNTA1LjIgMTMzLjIgNTI3LjQgMTAyLjggNTA1LjQgNzIuNUg1MjMuN0w1MzYuNSA5MC4xTDU0OS40IDcyLjVINTY3LjdMNTQ1LjcgMTAyLjhMNTY3LjkgMTMzLjJINTQ5LjZMNTM2LjUgMTE1LjNMNTIzLjUgMTMzLjJaTTMyNy4zIDIxMi43VjE1Mi4xSDMzMy44VjE2MS4zUTMzNy4xIDE1Ni4zIDM0Mi41IDE1My4zUTM0Ny44IDE1MC40IDM1NC4yIDE1MC40UTM2MS4zIDE1MC40IDM2Ny4xIDE1My45UTM3Mi45IDE1Ny4zIDM3Ni4xIDE2My4xUTM3OS4zIDE1Ny4zIDM4NS4xIDE1My45UTM5MC44IDE1MC40IDM5Ny45IDE1MC40UTQwNC44IDE1MC40IDQxMC41IDE1My43UTQxNi4yIDE1Ny4xIDQxOS41IDE2Mi44UTQyMi45IDE2OC41IDQyMi45IDE3NS40VjIxMi43SDQxNi40VjE3Ni42UTQxNi40IDE3MS40IDQxMy44IDE2Ni45UTQxMS4yIDE2Mi40IDQwNi45IDE1OS43UTQwMi42IDE1Ni45IDM5Ny40IDE1Ni45UTM5Mi4xIDE1Ni45IDM4Ny44IDE1OS41UTM4My41IDE2MiAzODEgMTY2LjRRMzc4LjQgMTcwLjkgMzc4LjQgMTc2LjZWMjEyLjdIMzcxLjhWMTc2LjZRMzcxLjggMTcwLjkgMzY5LjMgMTY2LjRRMzY2LjcgMTYyIDM2Mi40IDE1OS41UTM1OC4xIDE1Ni45IDM1Mi44IDE1Ni45UTM0Ny42IDE1Ni45IDM0My4zIDE1OS43UTMzOSAxNjIuNCAzMzYuNCAxNjYuOVEzMzMuOCAxNzEuNCAzMzMuOCAxNzYuNlYyMTIuN1pNNDYyLjkgMjE0LjNRNDU0LjYgMjE0LjMgNDQ3LjcgMjEwUTQ0MC44IDIwNS43IDQzNi43IDE5OC40UTQzMi42IDE5MS4yIDQzMi42IDE4Mi40UTQzMi42IDE3NS43IDQzNSAxNjkuOVE0MzcuMyAxNjQuMSA0NDEuNSAxNTkuN1E0NDUuNiAxNTUuMyA0NTEuMiAxNTIuOFE0NTYuNyAxNTAuNCA0NjIuOSAxNTAuNFE0NjkuNiAxNTAuNCA0NzUuMSAxNTIuOVE0ODAuNyAxNTUuNSA0ODQuOCAxNjAuMlE0ODguOSAxNjQuOCA0OTEgMTcxLjJRNDkzLjIgMTc3LjUgNDkzIDE4NUg0MzkuNFE0NDAuMSAxOTEuNSA0NDMuNCAxOTYuNlE0NDYuNiAyMDEuNyA0NTEuNyAyMDQuN1E0NTYuOCAyMDcuNyA0NjIuOSAyMDcuN1E0NjkuNyAyMDcuNyA0NzUuMSAyMDQuMlE0ODAuNSAyMDAuNiA0ODMuNyAxOTQuNEw0OTAuMyAxOTZRNDg2LjcgMjA0IDQ3OS40IDIwOS4xUTQ3MiAyMTQuMyA0NjIuOSAyMTQuM1pNNDM5LjMgMTc5LjVINDg2LjVRNDg2LjEgMTczLjIgNDgyLjkgMTY4UTQ3OS42IDE2Mi43IDQ3NC40IDE1OS42UTQ2OS4yIDE1Ni40IDQ2Mi45IDE1Ni40UTQ1Ni42IDE1Ni40IDQ1MS41IDE1OS41UTQ0Ni40IDE2Mi42IDQ0My4xIDE2Ny44UTQzOS45IDE3My4xIDQzOS4zIDE3OS41Wk01NTQuNCAxNTIuMUg1NjAuOVYyMTUuMlE1NjAuOSAyMjAuOCA1NTguNSAyMjUuOVE1NTYgMjMwLjkgNTUxLjggMjM0LjdRNTQ3LjYgMjM4LjYgNTQyLjMgMjQwLjhRNTM3IDI0MyA1MzEuNCAyNDNRNTI1IDI0MyA1MTkuMiAyNDAuMVE1MTMuMyAyMzcuMSA1MDkgMjMyLjJRNTA0LjggMjI3LjMgNTAzLjEgMjIxLjVMNTA5LjIgMjE4LjdRNTEwLjMgMjIzLjYgNTEzLjcgMjI3LjZRNTE3LjEgMjMxLjcgNTIxLjggMjM0UTUyNi41IDIzNi40IDUzMS40IDIzNi40UTUzNS43IDIzNi40IDUzOS44IDIzNC43UTU0NCAyMzMgNTQ3LjIgMjMwLjFRNTUwLjUgMjI3LjEgNTUyLjUgMjIzLjNRNTU0LjQgMjE5LjUgNTU0LjQgMjE1LjJWMjAwLjRRNTUxLjIgMjA2LjYgNTQ1LjUgMjEwLjRRNTM5LjggMjE0LjMgNTMyLjIgMjE0LjNRNTI1LjkgMjE0LjMgNTIwLjMgMjExLjhRNTE0LjcgMjA5LjMgNTEwLjUgMjA0LjlRNTA2LjMgMjAwLjUgNTAzLjkgMTk0LjdRNTAxLjUgMTg5IDUwMS41IDE4Mi40UTUwMS41IDE3NS43IDUwMy45IDE2OS45UTUwNi4zIDE2NC4yIDUxMC41IDE1OS44UTUxNC43IDE1NS40IDUyMC4zIDE1Mi45UTUyNS45IDE1MC40IDUzMi4yIDE1MC40UTUzOS44IDE1MC40IDU0NS41IDE1NC4yUTU1MS4yIDE1OCA1NTQuNCAxNjQuM1pNNTMyLjIgMjA3LjlRNTM5IDIwNy45IDU0My45IDIwNC4zUTU0OC44IDIwMC44IDU1MS40IDE5NVE1NTQgMTg5LjIgNTU0IDE4Mi40UTU1NCAxNzUuNCA1NTEuNCAxNjkuNlE1NDguNyAxNjMuOCA1NDMuOCAxNjAuM1E1MzguOSAxNTYuOCA1MzIuMiAxNTYuOFE1MjUuNSAxNTYuOCA1MjAuMSAxNjAuMlE1MTQuNiAxNjMuNyA1MTEuNCAxNjkuNVE1MDguMiAxNzUuMyA1MDguMiAxODIuNFE1MDguMiAxODkuNSA1MTEuNSAxOTUuMlE1MTQuOCAyMDEgNTIwLjIgMjA0LjRRNTI1LjcgMjA3LjkgNTMyLjIgMjA3LjlaTTYyNy44IDE1Mi4xSDYzNC4zVjIxMi43SDYyNy44TDYyNy42IDIwMC4yUTYyNC41IDIwNi41IDYxOC44IDIxMC40UTYxMy4xIDIxNC4zIDYwNS4xIDIxNC4zUTU5OC40IDIxNC4zIDU5Mi42IDIxMS44UTU4Ni44IDIwOS4zIDU4Mi40IDIwNC44UTU3OCAyMDAuNCA1NzUuNSAxOTQuNlE1NzMgMTg4LjggNTczIDE4Mi4yUTU3MyAxNzUuNiA1NzUuNSAxNjkuOFE1NzggMTY0IDU4Mi4zIDE1OS43UTU4Ni43IDE1NS4zIDU5Mi40IDE1Mi44UTU5OC4yIDE1MC40IDYwNC43IDE1MC40UTYxMi44IDE1MC40IDYxOC43IDE1NC4zUTYyNC42IDE1OC4yIDYyOCAxNjQuNVpNNjA1IDIwNy45UTYxMiAyMDcuOSA2MTcgMjA0LjRRNjIyIDIwMSA2MjQuNiAxOTUuMlE2MjcuMyAxODkuNCA2MjcuMyAxODIuNFE2MjcuMyAxNzUuMiA2MjQuNiAxNjkuNFE2MjEuOSAxNjMuNiA2MTYuOSAxNjAuMlE2MTEuOSAxNTYuOCA2MDUgMTU2LjhRNTk4IDE1Ni44IDU5Mi4yIDE2MC4yUTU4Ni41IDE2My43IDU4My4xIDE2OS41UTU3OS43IDE3NS4zIDU3OS43IDE4Mi40UTU3OS43IDE4OS41IDU4My4yIDE5NS4zUTU4Ni43IDIwMS4xIDU5Mi40IDIwNC41UTU5OC4yIDIwNy45IDYwNSAyMDcuOVpNNjY4LjEgMjEyLjdWMTUyLjFINjc0LjZWMTYxLjNRNjc3LjkgMTU2LjMgNjgzLjMgMTUzLjNRNjg4LjYgMTUwLjQgNjk1IDE1MC40UTcwMiAxNTAuNCA3MDcuOSAxNTMuOVE3MTMuNyAxNTcuMyA3MTYuOSAxNjMuMVE3MjAuMSAxNTcuMyA3MjUuOCAxNTMuOVE3MzEuNiAxNTAuNCA3MzguNiAxNTAuNFE3NDUuNiAxNTAuNCA3NTEuMiAxNTMuN1E3NTYuOSAxNTcuMSA3NjAuMyAxNjIuOFE3NjMuNyAxNjguNSA3NjMuNyAxNzUuNFYyMTIuN0g3NTcuMVYxNzYuNlE3NTcuMSAxNzEuNCA3NTQuNiAxNjYuOVE3NTIgMTYyLjQgNzQ3LjcgMTU5LjdRNzQzLjQgMTU2LjkgNzM4LjEgMTU2LjlRNzMyLjkgMTU2LjkgNzI4LjYgMTU5LjVRNzI0LjMgMTYyIDcyMS43IDE2Ni40UTcxOS4yIDE3MC45IDcxOS4yIDE3Ni42VjIxMi43SDcxMi42VjE3Ni42UTcxMi42IDE3MC45IDcxMCAxNjYuNFE3MDcuNSAxNjIgNzAzLjIgMTU5LjVRNjk4LjggMTU2LjkgNjkzLjYgMTU2LjlRNjg4LjQgMTU2LjkgNjg0LjEgMTU5LjdRNjc5LjcgMTYyLjQgNjc3LjIgMTY2LjlRNjc0LjYgMTcxLjQgNjc0LjYgMTc2LjZWMjEyLjdaTTgwMy43IDIxNC4zUTc5NS4zIDIxNC4zIDc4OC40IDIxMFE3ODEuNiAyMDUuNyA3NzcuNSAxOTguNFE3NzMuNCAxOTEuMiA3NzMuNCAxODIuNFE3NzMuNCAxNzUuNyA3NzUuNyAxNjkuOVE3NzguMSAxNjQuMSA3ODIuMyAxNTkuN1E3ODYuNCAxNTUuMyA3OTEuOSAxNTIuOFE3OTcuNCAxNTAuNCA4MDMuNyAxNTAuNFE4MTAuMyAxNTAuNCA4MTUuOSAxNTIuOVE4MjEuNSAxNTUuNSA4MjUuNiAxNjAuMlE4MjkuNyAxNjQuOCA4MzEuOCAxNzEuMlE4MzQgMTc3LjUgODMzLjggMTg1SDc4MC4yUTc4MC45IDE5MS41IDc4NC4yIDE5Ni42UTc4Ny40IDIwMS43IDc5Mi41IDIwNC43UTc5Ny42IDIwNy43IDgwMy43IDIwNy43UTgxMC40IDIwNy43IDgxNS44IDIwNC4yUTgyMS4yIDIwMC42IDgyNC41IDE5NC40TDgzMS4xIDE5NlE4MjcuNSAyMDQgODIwLjEgMjA5LjFRODEyLjggMjE0LjMgODAzLjcgMjE0LjNaTTc4MC4xIDE3OS41SDgyNy4yUTgyNi45IDE3My4yIDgyMy42IDE2OFE4MjAuNCAxNjIuNyA4MTUuMiAxNTkuNlE4MDkuOSAxNTYuNCA4MDMuNyAxNTYuNFE3OTcuNCAxNTYuNCA3OTIuMyAxNTkuNVE3ODcuMSAxNjIuNiA3ODMuOSAxNjcuOFE3ODAuNyAxNzMuMSA3ODAuMSAxNzkuNVpNODk4IDE3NS40VjIxMi43SDg5MS41VjE3Ni40UTg5MS41IDE3MSA4ODguOCAxNjYuNlE4ODYuMiAxNjIuMiA4ODEuOCAxNTkuNlE4NzcuNCAxNTYuOSA4NzIgMTU2LjlRODY2LjYgMTU2LjkgODYyLjIgMTU5LjZRODU3LjggMTYyLjIgODU1LjEgMTY2LjZRODUyLjUgMTcxIDg1Mi41IDE3Ni40VjIxMi43SDg0NlYxNTIuMUg4NTIuNVYxNjEuM1E4NTUuOCAxNTYuMyA4NjEuMiAxNTMuM1E4NjYuNiAxNTAuNCA4NzMgMTUwLjRRODc5LjkgMTUwLjQgODg1LjYgMTUzLjdRODkxLjMgMTU3LjEgODk0LjYgMTYyLjhRODk4IDE2OC41IDg5OCAxNzUuNFpNOTA4LjkgMTg5LjJWMTUyLjFIOTE1LjRWMTg4LjJROTE1LjQgMTkzLjYgOTE4LjEgMTk4UTkyMC43IDIwMi40IDkyNS4xIDIwNVE5MjkuNiAyMDcuNyA5MzQuOSAyMDcuN1E5NDAuMyAyMDcuNyA5NDQuNyAyMDVROTQ5LjEgMjAyLjQgOTUxLjcgMTk4UTk1NC40IDE5My42IDk1NC40IDE4OC4yVjE1Mi4xSDk2MC45TDk2MC45IDIxMi43SDk1NC40TDk1NC40IDIwMy4zUTk1MS4xIDIwOC4zIDk0NS43IDIxMS4zUTk0MC4zIDIxNC4zIDkzMy45IDIxNC4zUTkyNyAyMTQuMyA5MjEuMyAyMTAuOVE5MTUuNiAyMDcuNSA5MTIuMyAyMDEuOFE5MDguOSAxOTYuMSA5MDguOSAxODkuMloiLz48L3N2Zz4=';

			return 'data:image/svg+xml;base64,' . $svg;
		}


		/**
		 * Adds the "Menu Themes" menu item and page.
		 *
		 * @since  1.0
		 * @return void
		 */
		public function megamenu_settings_page() {

			$icon = $this->get_admin_menu_icon();

			$capability = apply_filters( 'megamenu_options_capability', 'edit_theme_options' );

			$page = add_menu_page( __( 'Max Mega Menu', 'megamenu' ), __( 'Mega Menu', 'megamenu' ), $capability, 'maxmegamenu', [ $this, 'page' ], $icon );

			$tabs = apply_filters( 'megamenu_menu_tabs', [] );

			foreach ( $tabs as $key => $title ) {
				if ( $key == 'menu_locations' ) {
					add_submenu_page( 'maxmegamenu', __( 'Max Mega Menu', 'megamenu' ) . ' - ' . $title, $title, $capability, 'maxmegamenu', [ $this, 'page' ] );
				} else {
					add_submenu_page( 'maxmegamenu', __( 'Max Mega Menu', 'megamenu' ) . ' - ' . $title, $title, $capability, 'maxmegamenu_' . $key, [ $this, 'page' ] );
				}
			}

		}



		/**
		 * Main settings page wrapper.
		 *
		 * @since  1.4
		 * @return void
		 */
		public function page() {

			$tab = isset( $_GET['page'] ) ? substr( $_GET['page'], 12 ) : false;

		// backwards compatibility
		if ( isset( $_GET['tab'] ) ) {
			$tab = sanitize_key( $_GET['tab'] );
		}

			if ( ! $tab ) {
				$tab = 'menu_locations';
			}

			$header_links = apply_filters(
				'megamenu_header_links',
				[
					'homepage'        => [
						'url'    => 'https://www.megamenu.com/?utm_source=free&amp;utm_medium=settings&amp;utm_campaign=pro',
						'target' => '_mmmpro',
						'text'   => __( 'Homepage', 'megamenu' ),
						'class'  => '',
					],
					'documentation'   => [
						'url'    => 'https://www.megamenu.com/documentation/installation/?utm_source=free&amp;utm_medium=settings&amp;utm_campaign=pro',
						'text'   => __( 'Documentation', 'megamenu' ),
						'target' => '_mmmpro',
						'class'  => '',
					],
					'troubleshooting' => [
						'url'    => 'https://www.megamenu.com/articles/troubleshooting/?utm_source=free&amp;utm_medium=settings&amp;utm_campaign=pro',
						'text'   => __( 'Troubleshooting', 'megamenu' ),
						'target' => '_mmmpro',
						'class'  => '',
					],
				]
			);

			if ( ! is_plugin_active( 'megamenu-pro/megamenu-pro.php' ) ) {
				$header_links['pro'] = [
					'url'                => 'https://www.megamenu.com/upgrade/?utm_source=free&amp;utm_medium=settings&amp;utm_campaign=pro',
					'target'             => '_mmmpro',
					'text'               => __( 'Upgrade to Pro', 'megamenu' ),
					'class'              => 'button button-primary button-compact',
					'show_external_icon' => true,
				];
			}

			$versions = apply_filters(
				'megamenu_versions',
				[
					'core' => [
						'version' => MEGAMENU_VERSION,
						'text'    => __( 'Core version', 'megamenu' ),
					],
					'pro'  => [
						'version' => $this->get_pro_extension_not_installed_link_html(),
						'text'    => __( 'Pro extension', 'megamenu' ),
					],
				]
			);

			?>

		<div class='megamenu_outer_wrap'>
			<div class='megamenu_header'>
				<div class='megamenu_header_left'>
					<div class='megamenu_header_title_row'>
						<h2>
							<img
								class='megamenu_header_logo'
								src='<?php echo esc_attr( $this->get_header_logo_data_url() ); ?>'
								width='144'
								height='44'
								alt='<?php esc_attr_e( 'Max Mega Menu', 'megamenu' ); ?>'
								decoding='async'
							/>
						</h2>
					</div>
					<div class='version'>
						<?php

							$total     = count( $versions );
							$count     = 0;
							$separator = ' - ';

					$allowed_version_html = [ 'a' => [ 'href' => [], 'target' => [] ] ];
					foreach ( $versions as $id => $data ) {
						echo esc_html( $data['text'] ) . ': <b>' . wp_kses( $data['version'], $allowed_version_html ) . '</b>';

							$count = $count + 1;

							if ( $total > 0 && $count != $total ) {
								echo $separator;
							}
						}
						?>
					</div>
				</div>
				<nav class='megamenu_header_right' aria-label="<?php esc_attr_e( 'Max Mega Menu links', 'megamenu' ); ?>">
					<ul>
					<?php
					foreach ( $header_links as $id => $data ) {
						$inner = esc_html( $data['text'] );
						if ( ! empty( $data['show_external_icon'] ) ) {
							$inner .= ' <span class="dashicons dashicons-external" aria-hidden="true"></span>';
						}
						$header_link_processor = new WP_HTML_Tag_Processor( '<a>' . $inner . '</a>' );
						if ( $header_link_processor->next_tag( 'a' ) ) {
							$header_link_processor->set_attribute( 'href', $data['url'] );
							$header_link_processor->set_attribute( 'target', $data['target'] );
							if ( isset( $data['class'] ) && '' !== $data['class'] ) {
								$header_link_processor->set_attribute( 'class', $data['class'] );
							}
						}
						printf(
							'<li>%s</li>',
							$header_link_processor->get_updated_html()
						);
					}
					?>
					</ul>
				</nav>

			<?php
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && isset( $_GET['debug'] ) && current_user_can( 'manage_options' ) ) {
					echo '<textarea style="width: 100%; height: 400px;">';
					echo esc_html( var_export( get_option( 'megamenu_settings' ), true ) );
					echo '</textarea>';
				}
			?>
			</div>

			<div class='megamenu_wrap megamenu-dialog-rail'>
					<div class='megamenu-dialog-tablist mega-tablist'>
						<nav class='mega-page-navigation' aria-label="<?php esc_attr_e( 'Max Mega Menu settings sections', 'megamenu' ); ?>">
						<ul>
							<?php
								$tabs = apply_filters( 'megamenu_menu_tabs', [] );

							foreach ( $tabs as $key => $title ) {
								$class = $tab === $key ? 'is-active' : '';

								if ( $key == 'menu_locations' ) {
									$args = [ 'page' => 'maxmegamenu' ];
								} else {
									$args = [ 'page' => 'maxmegamenu_' . $key ];
								}

								$url = esc_url( add_query_arg( $args, admin_url( 'admin.php' ) ) );

								$icon               = $this->get_page_navigation_icon( $key );
								$tab_link_processor = new WP_HTML_Tag_Processor( '<a>' . $icon . esc_html( $title ) . '</a>' );
								if ( $tab_link_processor->next_tag( 'a' ) ) {
									$tab_link_processor->set_attribute( 'href', $url );
									if ( $class ) {
										$tab_link_processor->set_attribute( 'class', $class );
									}
								}
								printf(
									'<li class="%s">%s</li>',
									esc_attr( $icon ? $key . ' has-icon' : $key ),
									$tab_link_processor->get_updated_html()
								);
							}
							?>
						</ul>
						</nav>
					</div>
					<div class='megamenu-dialog-panels'>
							<?php $this->print_messages(); ?>

							<?php

							$saved_settings = get_option( 'megamenu_settings' );

							if ( has_action( "megamenu_page_{$tab}" ) ) {
								do_action( "megamenu_page_{$tab}", $saved_settings );
							} else {
								do_action( 'megamenu_page_menu_locations', $saved_settings );
							}

							?>
					</div>
			</div>



		</div>

			<?php
		}


		/**
		 * Redirect and exit.
		 *
		 * @since  1.8
		 * @param  string $url URL to redirect to.
		 * @return void
		 */
	public function redirect( $url ) {
		wp_safe_redirect( $url );
		exit;
	}


		/**
		 * Display messages to the user.
		 *
		 * @since  1.0
		 * @return void
		 */
		public function print_messages() {

			if ( is_plugin_active( 'clearfy/clearfy.php' ) ) {
				if ( $clearfy_options = get_option( 'wbcr_clearfy_cache_options' ) ) {
					if ( $clearfy_options['disable_dashicons'] == true ) {
						printf(
							'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
							esc_html( __( 'Please enable Dashicons in the Clearfy plugin options. Max Mega Menu requires Dashicons.', 'megamenu' ) )
						);
					}
				}
			}

			do_action( 'megamenu_print_messages' );

		}


		/**
		 * Enqueue admin scripts.
		 *
		 * @since  1.8.3
		 * @param  string $hook The current admin page hook suffix.
		 * @return void
		 */
		public function enqueue_scripts( $hook ) {

			if ( class_exists( 'Mega_Menu_Locations' ) ) {
				Mega_Menu_Locations::register_and_localize_location_settings_dialog();
			}

			wp_deregister_style( 'select2' );
			wp_deregister_script( 'select2' );

			wp_enqueue_style( 'select2', MEGAMENU_BASE_URL . 'js/select2/select2.css', false, MEGAMENU_VERSION );
			wp_enqueue_script( 'mega-menu-select2', MEGAMENU_BASE_URL . 'js/select2/select2.js', [], MEGAMENU_VERSION );

			wp_enqueue_script( 'mega-menu-icon-selector', MEGAMENU_BASE_URL . 'js/admin/icon-selector.js', [ 'jquery' ], MEGAMENU_VERSION, true );

			wp_enqueue_style( 'mega-menu-settings', MEGAMENU_BASE_URL . 'css/admin/admin.css', array( 'wp-components' ), MEGAMENU_VERSION );

			wp_enqueue_script( 'mega-colorpicker', MEGAMENU_BASE_URL . 'js/colorpicker/colorpicker.js', [ 'jquery' ], MEGAMENU_VERSION );


			wp_localize_script(
				'spectrum',
				'megamenu_spectrum_settings',
				apply_filters( 'megamenu_spectrum_localisation', [] )
			);

			wp_enqueue_script( 'dialog-modal-expand', MEGAMENU_BASE_URL . 'js/admin/dialog-modal-expand.js', [ 'jquery' ], MEGAMENU_VERSION, true );
			wp_enqueue_script(
				'mega-menu-theme-editor',
				MEGAMENU_BASE_URL . 'js/admin/theme-editor.js',
				[
					'jquery',
					'jquery-ui-sortable',
					'mega-menu-select2',
					'mega-menu-icon-selector',
					'mega-colorpicker',
					'code-editor',
					'dialog-location-settings',
				],
				MEGAMENU_VERSION
			);

			wp_localize_script(
				'mega-menu-theme-editor',
				'megamenu_settings',
				[
					'edit_nonce'                        => wp_create_nonce( 'megamenu_edit' ),
					'saving'                            => __( 'Saving', 'megamenu' ),
					'saved'                             => __( 'Saved', 'megamenu' ),
					'confirm_destructive_action'        => __( 'Are you sure?', 'megamenu' ),
					'confirm'                           => __( 'Are you sure?', 'megamenu' ),
					'theme_save_error'                  => __( 'Error saving theme.', 'megamenu' ),
					'theme_save_error_refresh'          => __( 'Please try refreshing the page.', 'megamenu' ),
					'theme_save_error_exhausted'        => __( 'The server ran out of memory whilst trying to regenerate the menu CSS.', 'megamenu' ),
					'theme_save_error_memory_limit'     => __( 'Try disabling unusued plugins to increase the available memory. Alternatively, for details on how to increase your server memory limit see:', 'megamenu' ),
					'theme_save_error_500'              => __( 'The server returned a 500 error. The server did not provide an error message (you should find details of the error in your server error log), but this is usually due to your server memory limit being reached.', 'megamenu' ),
					'increase_memory_limit_url'         => 'http://www.wpbeginner.com/wp-tutorials/fix-wordpress-memory-exhausted-error-increase-php-memory/',
					'increase_memory_limit_anchor_text' => 'How to increase the WordPress memory limit',
					'scss_vars_error'                   => __( 'Could not load SCSS variables.', 'megamenu' ),
				]
			);

			if ( function_exists( 'wp_enqueue_code_editor' ) ) {
				wp_deregister_style( 'codemirror' );
				wp_deregister_script( 'codemirror' );

				$cm_settings['codeEditor'] = wp_enqueue_code_editor( [ 'type' => 'text/x-scss' ] );
				wp_localize_script( 'mega-menu-theme-editor', 'cm_settings', $cm_settings );
				wp_enqueue_style( 'wp-codemirror' );
			}
		}

	}

endif;
