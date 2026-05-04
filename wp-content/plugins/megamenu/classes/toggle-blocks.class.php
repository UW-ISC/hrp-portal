<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'Mega_Menu_Toggle_Blocks' ) ) :

	/**
	 * Manages the mobile toggle bar blocks including the menu toggle, animated
	 * menu toggle, and spacer — handling both front-end output and admin editing.
	 *
	 * @since   2.1
	 * @package MegaMenu
	 */
	class Mega_Menu_Toggle_Blocks {

		/**
		 * Constructor. Registers actions and filters for toggle block output and saving.
		 *
		 * @since 2.1
		 */
		public function __construct() {

			add_filter( 'megamenu_scss_variables', [ $this, 'add_menu_toggle_block_vars_to_scss' ], 10, 5 );
			add_filter( 'megamenu_scss_variables', [ $this, 'add_spacer_block_vars_to_scss' ], 10, 5 );
			add_filter( 'megamenu_scss_variables', [ $this, 'add_menu_toggle_animated_block_vars_to_scss' ], 10, 5 );

			add_filter( 'megamenu_load_scss_file_contents', [ $this, 'append_scss' ], 10 );
			add_filter( 'megamenu_toggle_bar_content', [ $this, 'output_public_toggle_blocks' ], 10, 4 );

			add_action( 'wp_ajax_mm_get_toggle_block_menu_toggle', [ $this, 'output_menu_toggle_block_html' ] );
			add_action( 'megamenu_output_admin_toggle_block_menu_toggle', [ $this, 'output_menu_toggle_block_html' ], 10, 2 );
			add_action( 'megamenu_output_public_toggle_block_menu_toggle', [ $this, 'output_menu_public_toggle_block_html' ], 10, 3 );

			add_action( 'wp_ajax_mm_get_toggle_block_menu_toggle_animated', [ $this, 'output_menu_toggle_block_animated_html' ] );
			add_action( 'megamenu_output_admin_toggle_block_menu_toggle_animated', [ $this, 'output_menu_toggle_block_animated_html' ], 10, 2 );
			add_action( 'megamenu_output_public_toggle_block_menu_toggle_animated', [ $this, 'output_menu_public_toggle_block_animated_html' ], 10, 3 );

			add_action( 'wp_ajax_mm_get_toggle_block_spacer', [ $this, 'output_spacer_block_html' ] );
			add_action( 'megamenu_output_admin_toggle_block_spacer', [ $this, 'output_spacer_block_html' ], 10, 2 );

			add_action( 'megamenu_after_theme_revert', [ $this, 'revert_toggle_blocks' ] );
			add_action( 'megamenu_after_theme_save', [ $this, 'save_toggle_blocks' ] );

			add_action( 'megamenu_print_theme_option_toggle_blocks', [ $this, 'print_theme_toggle_bar_designer_option' ], 10, 2 );

			add_filter( 'megamenu_theme_editor_settings', [ $this, 'add_toggle_designer_to_theme_editor' ], 10 );

		}


		/**
		 * Output the standard menu toggle block HTML on the front end.
		 *
		 * @since 2.4.1
		 * @param string $html     Existing toggle HTML.
		 * @param array  $settings Block settings array.
		 * @param array  $args     wp_nav_menu arguments.
		 * @return string Modified toggle HTML.
		 */
		public function output_menu_public_toggle_block_html( $html, $settings, $args ) {
			$closed_text = isset( $settings['closed_text'] ) ? do_shortcode( stripslashes( $settings['closed_text'] ) ) : 'MENU';
			$open_text   = isset( $settings['open_text'] ) ? do_shortcode( stripslashes( $settings['open_text'] ) ) : 'MENU';

		    // Retrieve CSS version
		    $css_version = Mega_Menu_Style_Manager::get_css_version();
		    // Only use button HTML if CSS version is >= 3.5.1
		    if ( version_compare( $css_version, '3.5.1', '>=' ) ) {
				$html = "<button class='mega-toggle-standard mega-toggle-label'><span class='mega-toggle-label-closed'>{$closed_text}</span><span class='mega-toggle-label-open'>{$open_text}</span></button>";
				$processor = new WP_HTML_Tag_Processor( $html );
				if ( $processor->next_tag( 'button' ) ) {
					$processor->set_attribute( 'aria-haspopup', 'true' );
					$processor->set_attribute( 'aria-expanded', 'false' );
					$processor->set_attribute( 'aria-controls', 'mega-menu-' . $args['theme_location'] );
				}
				$html = $processor->get_updated_html();
		    } else {
				$html = "<span class='mega-toggle-label'><span class='mega-toggle-label-closed'>{$closed_text}</span><span class='mega-toggle-label-open'>{$open_text}</span></span>";
				$processor = new WP_HTML_Tag_Processor( $html );
				if ( $processor->next_tag( 'span' ) ) {
					$processor->set_attribute( 'role', 'button' );
					$processor->set_attribute( 'aria-haspopup', 'true' );
					$processor->set_attribute( 'aria-expanded', 'false' );
				}
				$html = $processor->get_updated_html();
		    }

			return apply_filters( 'megamenu_toggle_menu_toggle_html', $html );
		}


		/**
		 * Return the saved toggle blocks for a specified theme.
		 *
		 * @since 2.1
		 * @param string $theme_id The theme ID.
		 * @return array Array of toggle block settings.
		 */
		private function get_toggle_blocks_for_theme( $theme_id ) {

			$blocks = max_mega_menu_get_toggle_blocks();

			if ( isset( $blocks[ $theme_id ] ) ) {
				return $blocks[ $theme_id ];
			}

			$defaults[] = [
				'type'       => 'menu_toggle_animated',
				'align'      => 'right',
				'icon_scale' => isset( $settings['icon_scale'] ) && strlen( $settings['icon_scale'] ) ? $settings['icon_scale'] : '0.8',
				'icon_color' => isset( $settings['icon_color'] ) ? $settings['icon_color'] : 'rgb(221, 221, 221)',
			];

			return $defaults;

		}


		/**
		 * Return default menu toggle block settings.
		 *
		 * @since 2.1
		 * @param string $theme_id The theme ID to inherit defaults from.
		 * @return array Default menu toggle block settings.
		 */
		private function get_default_menu_toggle_block( $theme_id = 'default' ) {

			$theme_obj  = Mega_Menu_Theme::find( $theme_id );
			$menu_theme = $theme_obj->settings;

			$defaults = [
				'type'          => 'menu_toggle',
				'align'         => 'right',
				'closed_text'   => isset( $menu_theme['responsive_text'] ) ? $menu_theme['responsive_text'] : 'MENU',
				'open_text'     => isset( $menu_theme['responsive_text'] ) ? $menu_theme['responsive_text'] : 'MENU',
				'closed_icon'   => 'dash-f333',
				'open_icon'     => 'dash-f153',
				'icon_position' => 'after',
				'text_color'    => isset( $menu_theme['toggle_font_color'] ) ? $menu_theme['toggle_font_color'] : 'rgb(221, 221, 221)',
				'icon_color'    => isset( $menu_theme['toggle_font_color'] ) ? $menu_theme['toggle_font_color'] : 'rgb(221, 221, 221)',
				'text_size'     => '14px',
				'icon_size'     => '24px',
			];

			return $defaults;
		}


		/**
		 * Append toggle block HTML to the menu toggle bar on the front end.
		 *
		 * @since 2.1
		 * @param string $content  Existing toggle bar content.
		 * @param string $nav_menu Nav menu HTML output.
		 * @param array  $args     wp_nav_menu arguments.
		 * @param string $theme_id The active theme ID.
		 * @return string Updated toggle bar content with blocks appended.
		 */
		public function output_public_toggle_blocks( $content, $nav_menu, $args, $theme_id ) {

			$toggle_blocks = $this->get_toggle_blocks_for_theme( $theme_id );

			$blocks_html = '';

			if ( is_array( $toggle_blocks ) ) {
				$blocks_html = $this->get_flex_blocks_html( $toggle_blocks, $content, $nav_menu, $args, $theme_id );
			}

			$content .= $blocks_html;

			return $content;

		}

		/**
		 * Sort toggle blocks into three flex divs (left, center, right) and return combined HTML.
		 *
		 * @since 2.4.1
		 * @param array  $toggle_blocks Array of toggle block settings.
		 * @param string $content       Existing toggle bar content.
		 * @param string $nav_menu      Nav menu HTML output.
		 * @param array  $args          wp_nav_menu arguments.
		 * @param string $theme_id      The active theme ID.
		 * @return string Toggle blocks HTML divided into left/center/right wrappers.
		 */
		private function get_flex_blocks_html( $toggle_blocks, $content, $nav_menu, $args, $theme_id ) {

			$sorted_blocks = [];

			/** Sort blocks into left, center, right array **/
			foreach ( $toggle_blocks as $block_id => $block ) {
				if ( isset( $block['align'] ) ) {
					$sorted_blocks[ $block['align'] ][ $block_id ] = $block;
				} else {
					$sorted_blocks['left'][ $block_id ] = $block;
				}
			}

			$blocks_html = '<div class="mega-toggle-blocks-left">';

			if ( isset( $sorted_blocks['left'] ) ) {
				foreach ( $sorted_blocks['left'] as $block_id => $block ) {
					$blocks_html .= $this->get_toggle_block_html( $block_id, $block, $content, $nav_menu, $args, $theme_id );
				}
			}

			$blocks_html .= '</div>';

			$blocks_html .= '<div class="mega-toggle-blocks-center">';

			if ( isset( $sorted_blocks['center'] ) ) {
				foreach ( $sorted_blocks['center'] as $block_id => $block ) {
					$blocks_html .= $this->get_toggle_block_html( $block_id, $block, $content, $nav_menu, $args, $theme_id );
				}
			}

			$blocks_html .= '</div>';

			$blocks_html .= '<div class="mega-toggle-blocks-right">';

			if ( isset( $sorted_blocks['right'] ) ) {
				foreach ( $sorted_blocks['right'] as $block_id => $block ) {
					$blocks_html .= $this->get_toggle_block_html( $block_id, $block, $content, $nav_menu, $args, $theme_id );
				}
			}

			$blocks_html .= '</div>';

			return $blocks_html;
		}

		/**
		 * Generate the HTML wrapper for a single toggle block.
		 *
		 * @since 2.4.1
		 * @param int    $block_id Block index.
		 * @param array  $block    Block settings array.
		 * @param string $content  Existing toggle bar content.
		 * @param string $nav_menu Nav menu HTML output.
		 * @param array  $args     wp_nav_menu arguments.
		 * @param string $theme_id The active theme ID.
		 * @return string Complete HTML for the toggle block wrapper.
		 */
		private function get_toggle_block_html( $block_id, $block, $content, $nav_menu, $args, $theme_id ) {
			$block_html = '';

			if ( isset( $block['type'] ) ) {
				$class = 'mega-' . str_replace( '_', '-', $block['type'] ) . '-block';
			} else {
				$class = '';
			}

			$id = apply_filters( 'megamenu_toggle_block_id', 'mega-toggle-block-' . $block_id );

			$atts = [
				'class' => "mega-toggle-block {$class} mega-toggle-block-{$block_id}",
				'id'    => "mega-toggle-block-{$block_id}",
			];

			$attributes = apply_filters( 'megamenu_toggle_block_attributes', $atts, $block, $content, $nav_menu, $args, $theme_id );

			$block_html .= '<div';

			foreach ( $attributes as $attribute => $val ) {
				$block_html .= ' ' . $attribute . "='" . esc_attr( $val ) . "'";
			}
			$block_html .= '>';
			$block_html .= apply_filters( "megamenu_output_public_toggle_block_{$block['type']}", '', $block, $args );
			$block_html .= '</div>';

			return $block_html;
		}


		/**
		 * Save the toggle blocks when the theme is saved.
		 *
		 * @since 2.1
		 * @return void
		 */
		public function save_toggle_blocks() {

			$theme = esc_attr( $_POST['theme_id'] );

			$saved_blocks = max_mega_menu_get_toggle_blocks();

			if ( isset( $saved_blocks[ $theme ] ) ) {
				unset( $saved_blocks[ $theme ] );
			}

			$submitted_settings = $_POST['toggle_blocks'];

			$saved_blocks[ $theme ] = $submitted_settings;

			max_mega_menu_save_toggle_blocks( $saved_blocks );

		}


		/**
		 * Revert (delete) the saved toggle blocks when a theme is reverted.
		 *
		 * @since 2.1
		 * @return void
		 */
		public function revert_toggle_blocks() {

			$theme = esc_attr( $_GET['theme_id'] );

			$saved_toggle_blocks = max_mega_menu_get_toggle_blocks();

			if ( isset( $saved_toggle_blocks[ $theme ] ) ) {
				unset( $saved_toggle_blocks[ $theme ] );
			}

			max_mega_menu_save_toggle_blocks( $saved_toggle_blocks );
		}


		/**
		 * Add the toggle bar designer section to the theme editor settings.
		 *
		 * @since 2.1
		 * @param array $settings Theme editor settings array.
		 * @return array Updated settings with toggle bar designer added.
		 */
		public function add_toggle_designer_to_theme_editor( $settings ) {

			$settings['mobile_menu']['settings']['toggle_blocks'] = [
				'priority'    => 6,
				'title'       => __( 'Toggle Bar Designer', 'megamenu' ),
				'description' => __( 'Configure the contents of the mobile toggle bar', 'megamenu' ),
				'settings'    => [
					[
						'title' => '',
						'type'  => 'toggle_blocks',
						'key'   => 'toggle_blocks',
					],
				],
			];

			return $settings;
		}


		/**
		 * Append the toggle blocks SCSS to the main SCSS file contents.
		 *
		 * @since 2.1
		 * @param string $scss The existing SCSS content.
		 * @return string SCSS with toggle-blocks.scss appended.
		 */
		public function append_scss( $scss ) {

			$path = MEGAMENU_PATH . 'css/toggle-blocks.scss';

			$contents = file_get_contents( $path );

			return $scss . $contents;

		}


		/**
		 * Inject menu_toggle_blocks SCSS variable for all menu toggle blocks in the theme.
		 *
		 * @since 2.1
		 * @param array  $vars      Existing SCSS variables.
		 * @param string $location  Menu location slug.
		 * @param array  $theme     Theme settings array.
		 * @param int    $menu_id   Menu term ID.
		 * @param string $theme_id  The active theme ID.
		 * @return array Updated SCSS variables.
		 */
		public function add_menu_toggle_block_vars_to_scss( $vars, $location, $theme, $menu_id, $theme_id ) {

			$toggle_blocks = $this->get_toggle_blocks_for_theme( $theme_id );

			$menu_toggle_blocks = [];

			if ( is_array( $toggle_blocks ) ) {

				foreach ( $toggle_blocks as $index => $settings ) {

					if ( isset( $settings['type'] ) && $settings['type'] == 'menu_toggle' ) {

						if ( isset( $settings['closed_icon'] ) ) {
							$closed_icon_parts = explode( '-', $settings['closed_icon'] );
							$closed_icon       = end( $closed_icon_parts );
						} else {
							$closed_icon = 'disabled';
						}

						if ( isset( $settings['open_icon'] ) ) {
							$open_icon_parts = explode( '-', $settings['open_icon'] );
							$open_icon       = end( $open_icon_parts );
						} else {
							$open_icon = 'disabled';
						}

						$styles = [
							'id'            => $index,
							'align'         => isset( $settings['align'] ) ? "'" . $settings['align'] . "'" : "'right'",
							'closed_text'   => "''", // deprecated
							'open_text'     => "''", // deprecated
							'closed_icon'   => $closed_icon != 'disabled' ? "'\\" . $closed_icon . "'" : "''",
							'open_icon'     => $open_icon != 'disabled' ? "'\\" . $open_icon . "'" : "''",
							'text_color'    => isset( $settings['text_color'] ) ? $settings['text_color'] : '#fff',
							'icon_color'    => isset( $settings['icon_color'] ) ? $settings['icon_color'] : '#fff',
							'icon_position' => isset( $settings['icon_position'] ) ? "'" . $settings['icon_position'] . "'" : 'after',
							'text_size'     => isset( $settings['text_size'] ) && strlen( $settings['text_size'] ) ? $settings['text_size'] : '14px',
							'icon_size'     => isset( $settings['icon_size'] ) && strlen( $settings['icon_size'] ) ? $settings['icon_size'] : '24px',
						];

						$menu_toggle_blocks[ $index ] = $styles;
					}
				}
			}

			//$menu_toggle_blocks(
			// (123, red, 150px),
			// (456, green, null),
			// (789, blue, 90%),());
			if ( count( $menu_toggle_blocks ) ) {
				$blocks = [];

				foreach ( $menu_toggle_blocks as $id => $vals ) {
					$blocks[] = '(' . implode( ',', $vals ) . ')';
				}

				if ( defined( 'MEGAMENU_SCSS_COMPILER_COMPAT') && MEGAMENU_SCSS_COMPILER_COMPAT ) {
					$blocks[] = '()'; // add empty list item to ensure list is treated as a list in scssphp 0.0.12
				}

				$list = '(' . implode(',', $blocks) . ')';

				$vars['menu_toggle_blocks'] = $list;

			} else {

				$vars['menu_toggle_blocks'] = '()';

			}

			return $vars;
		}

		/**
		 * Inject spacer_toggle_blocks SCSS variable for all spacer blocks in the theme.
		 *
		 * @since 2.1
		 * @param array  $vars      Existing SCSS variables.
		 * @param string $location  Menu location slug.
		 * @param array  $theme     Theme settings array.
		 * @param int    $menu_id   Menu term ID.
		 * @param string $theme_id  The active theme ID.
		 * @return array Updated SCSS variables.
		 */
		public function add_spacer_block_vars_to_scss( $vars, $location, $theme, $menu_id, $theme_id ) {

			$toggle_blocks = $this->get_toggle_blocks_for_theme( $theme_id );

			$spacer_blocks = [];

			if ( is_array( $toggle_blocks ) ) {

				foreach ( $toggle_blocks as $index => $settings ) {

					if ( isset( $settings['type'] ) && $settings['type'] == 'spacer' ) {

						$styles = [
							'id'    => $index,
							'align' => isset( $settings['align'] ) ? "'" . $settings['align'] . "'" : "'right'",
							'width' => isset( $settings['width'] ) ? $settings['width'] : '0px',
						];

						$spacer_blocks[ $index ] = $styles;
					}
				}
			}

			//$menu_toggle_blocks(
			// (123, red, 150px),
			// (456, green, null),
			// (789, blue, 90%),());
			if ( count( $spacer_blocks ) ) {

				$blocks = [];

				foreach ( $spacer_blocks as $id => $vals ) {
					$blocks[] = '(' . implode( ',', $vals ) . ')';
				}

				if ( defined( 'MEGAMENU_SCSS_COMPILER_COMPAT') && MEGAMENU_SCSS_COMPILER_COMPAT ) {
					$blocks[] = '()'; // add empty list item to ensure list is treated as a list in scssphp 0.0.12
				}

				$list = '(' . implode(',', $blocks) . ')';

				$vars['spacer_toggle_blocks'] = $list;

			} else {

				$vars['spacer_toggle_blocks'] = '()';

			}

			return $vars;

		}

		/**
		 * Inject menu_toggle_animated_blocks SCSS variable for all animated toggle blocks.
		 *
		 * @since 2.5.3
		 * @param array  $vars      Existing SCSS variables.
		 * @param string $location  Menu location slug.
		 * @param array  $theme     Theme settings array.
		 * @param int    $menu_id   Menu term ID.
		 * @param string $theme_id  The active theme ID.
		 * @return array Updated SCSS variables.
		 */
		public function add_menu_toggle_animated_block_vars_to_scss( $vars, $location, $theme, $menu_id, $theme_id ) {

			$toggle_blocks = $this->get_toggle_blocks_for_theme( $theme_id );

			$menu_toggle_animated_blocks = [];

			if ( is_array( $toggle_blocks ) ) {

				foreach ( $toggle_blocks as $index => $settings ) {

					if ( isset( $settings['type'] ) && $settings['type'] == 'menu_toggle_animated' ) {

						$styles = [
							'id'         => $index,
							'icon_scale' => isset( $settings['icon_scale'] ) && strlen( $settings['icon_scale'] ) ? $settings['icon_scale'] : '0.8',
							'icon_color' => isset( $settings['icon_color'] ) ? $settings['icon_color'] : 'rgb(221, 221, 221)',
						];

						$menu_toggle_animated_blocks[ $index ] = $styles;
					}
				}
			}

			//$menu_toggle_blocks(
			// (123, red, 150px),
			// (456, green, null),
			// (789, blue, 90%),());
			if ( count( $menu_toggle_animated_blocks ) ) {
				$blocks = [];

				foreach ( $menu_toggle_animated_blocks as $id => $vals ) {
					$blocks[] = '(' . implode( ',', $vals ) . ')';
				}

				if ( defined( 'MEGAMENU_SCSS_COMPILER_COMPAT') && MEGAMENU_SCSS_COMPILER_COMPAT ) {
					$blocks[] = '()'; // add empty list item to ensure list is treated as a list in scssphp 0.0.12
				}
				
				$list = '(' . implode(',', $blocks) . ')';

				$vars['menu_toggle_animated_blocks'] = $list;

			} else {

				$vars['menu_toggle_animated_blocks'] = '()';

			}

			return $vars;

		}


		/**
		 * Output the toggle bar designer UI in the theme editor.
		 *
		 * @since 2.1
		 * @param string $key      The settings key for this option.
		 * @param string $theme_id The active theme ID.
		 * @return void
		 */
		public function print_theme_toggle_bar_designer_option( $key, $theme_id ) {

			$toggle_blocks = $this->get_toggle_blocks_for_theme( $theme_id );

			$block_types = apply_filters(
				'megamenu_registered_toggle_blocks',
				[
					'menu_toggle_animated' => __( 'Menu Toggle (Animated)', 'megamenu' ),
					'menu_toggle'          => __( 'Menu Toggle (Standard)', 'megamenu' ),
					'spacer'               => __( 'Spacer', 'megamenu' ),
				]
			);

			ksort( $block_types );
			?>

		<div class='mega-toolbar-select-field mega-toggle-block-selector-field'>
			<label for='toggle-block-selector' class='mega-short-desc'><?php esc_html_e( 'Add block to toggle bar', 'megamenu' ); ?></label>
			<select id='toggle-block-selector'>
				<option value='title'><?php echo esc_html__( 'Select…', 'megamenu' ); ?></option>

				<?php foreach ( $block_types as $block_id => $block_name ) : ?>
					<option value='<?php echo esc_attr( $block_id ); ?>'><?php echo esc_html( $block_name ); ?></option>
				<?php endforeach; ?>

				<?php if ( ! is_plugin_active( 'megamenu-pro/megamenu-pro.php' ) ) : ?>
					<option disabled="disabled">Menu Toggle (Custom) (Pro)</option>
					<option disabled="disabled">Search (Pro)</option>
					<option disabled="disabled">Logo (Pro)</option>
					<option disabled="disabled">Icon (Pro)</option>
					<option disabled="disabled">HTML (Pro)</option>
				<?php endif; ?>
			</select>
		</div>

		<div class='toggle-bar-designer'>
			<div class='mega-blocks'>
				<div class='mega-left'>
					<?php

					if ( is_array( $toggle_blocks ) ) {
						foreach ( $toggle_blocks as $block_id => $settings ) {
							if ( is_int( $block_id ) && is_array( $settings ) && isset( $settings['align'] ) && $settings['align'] == 'left' || ! isset( $settings['align'] ) ) {
								if ( isset( $settings['type'] ) ) {
									do_action( "megamenu_output_admin_toggle_block_{$settings['type']}", $block_id, $settings );
								}
							}
						}
					}

					?>
				</div>
				<div class='mega-center'>
					<?php

					if ( is_array( $toggle_blocks ) ) {
						foreach ( $toggle_blocks as $block_id => $settings ) {
							if ( is_int( $block_id ) && is_array( $settings ) && isset( $settings['align'] ) && $settings['align'] == 'center' ) {
								if ( isset( $settings['type'] ) ) {
									do_action( "megamenu_output_admin_toggle_block_{$settings['type']}", $block_id, $settings );
								}
							}
						}
					}

					?>
				</div>
				<div class='mega-right'>
					<?php

					if ( is_array( $toggle_blocks ) ) {
						foreach ( $toggle_blocks as $block_id => $settings ) {
							if ( is_int( $block_id ) && is_array( $settings ) && isset( $settings['align'] ) && $settings['align'] == 'right' ) {
								if ( isset( $settings['type'] ) ) {
									do_action( "megamenu_output_admin_toggle_block_{$settings['type']}", $block_id, $settings );
								}
							}
						}
					}

					?>
				</div>

			</div>


		</div>

		<p class='mega-info'><?php _e( 'Click on a block to edit it, or drag and drop it to resposition the block within the toggle bar', 'megamenu' ); ?></p>


			<?php
		}


		/**
		 * Output the admin HTML for the "Spacer" toggle block settings panel.
		 *
		 * @since 2.1
		 * @param int   $block_id The block index.
		 * @param array $settings Block settings, merged with defaults.
		 * @return void
		 */
		public function output_spacer_block_html( $block_id, $settings = [] ) {

			if ( empty( $settings ) ) {
				$block_id = '0';
			}

			$defaults = [
				'align' => 'right',
				'width' => '0px',
			];

			$settings = array_merge( $defaults, $settings );

			?>

		<div class='block'>
			<div class='block-title'><span title='<?php _e( 'Spacer', 'megamenu' ); ?>' class="dashicons dashicons-leftright"></span></div>
			<div class='block-settings'>
				<h3><?php _e( 'Spacer Settings', 'megamenu' ); ?></h3>
				<input type='hidden' class='type' name='toggle_blocks[<?php echo $block_id; ?>][type]' value='spacer' />
				<input type='hidden' class='align' name='toggle_blocks[<?php echo $block_id; ?>][align]' value='<?php echo $settings['align']; ?>'>
				<label>
					<?php _e( 'Width', 'megamenu' ); ?><input type='text' class='closed_text' name='toggle_blocks[<?php echo $block_id; ?>][width]' value='<?php echo $settings['width']; ?>' />
				</label>
				<?php $this->print_toggle_block_delete_control(); ?>
			</div>
		</div>

			<?php
		}


		/**
		 * Output the admin HTML for the "Menu Toggle" block settings panel.
		 *
		 * @since 2.1
		 * @param int   $block_id The block index.
		 * @param array $settings Block settings, merged with defaults.
		 * @return void
		 */
		public function output_menu_toggle_block_html( $block_id, $settings = [] ) {

			if ( empty( $settings ) ) {
				$block_id = '0';
			}

			$theme_id = 'default';

			if ( isset( $_GET['theme'] ) ) {
				$theme_id = esc_attr( $_GET['theme'] );

			}

			$defaults = $this->get_default_menu_toggle_block( $theme_id );

			$settings = array_merge( $defaults, $settings );

			?>

		<div class='block'>
			<div class='block-title'><?php _e( 'TOGGLE', 'megamenu' ); ?> <span title='<?php _e( 'Menu Toggle', 'megamenu' ); ?>' class="dashicons dashicons-menu"></span></div>
			<div class='block-settings'>
				<h3><?php _e( 'Menu Toggle Settings', 'megamenu' ); ?></h3>
				<input type='hidden' class='type' name='toggle_blocks[<?php echo $block_id; ?>][type]' value='menu_toggle' />
				<input type='hidden' class='align' name='toggle_blocks[<?php echo $block_id; ?>][align]' value='<?php echo $settings['align']; ?>'>
				<label>
					<?php _e( 'Closed Text', 'megamenu' ); ?><input type='text' class='closed_text' name='toggle_blocks[<?php echo $block_id; ?>][closed_text]' value='<?php echo stripslashes( esc_attr( $settings['closed_text'] ) ); ?>' />
				</label>
				<label>
					<?php _e( 'Open Text', 'megamenu' ); ?><input type='text' class='open_text' name='toggle_blocks[<?php echo $block_id; ?>][open_text]' value='<?php echo stripslashes( esc_attr( $settings['open_text'] ) ); ?>' />
				</label>
				<label>
					<?php _e( 'Closed Icon', 'megamenu' ); ?>
					<?php $this->print_icon_option( 'closed_icon', $block_id, $settings['closed_icon'], $this->toggle_icons() ); ?>
				</label>
				<label>
					<?php _e( 'Open Icon', 'megamenu' ); ?>
					<?php $this->print_icon_option( 'open_icon', $block_id, $settings['open_icon'], $this->toggle_icons() ); ?>
				</label>
				<label>
					<?php _e( 'Text Color', 'megamenu' ); ?>
					<?php $this->print_toggle_color_option( 'text_color', $block_id, $settings['text_color'] ); ?>
				</label>
				<label>
					<?php _e( 'Text Size', 'megamenu' ); ?><input type='text' class='text_size' name='toggle_blocks[<?php echo $block_id; ?>][text_size]' value='<?php echo stripslashes( esc_attr( $settings['text_size'] ) ); ?>' />
				</label>
				<label>
					<?php _e( 'Icon Color', 'megamenu' ); ?>
					<?php $this->print_toggle_color_option( 'icon_color', $block_id, $settings['icon_color'] ); ?>
				</label>
				<label>
					<?php _e( 'Icon Size', 'megamenu' ); ?><input type='text' class='icon_size' name='toggle_blocks[<?php echo $block_id; ?>][icon_size]' value='<?php echo stripslashes( esc_attr( $settings['icon_size'] ) ); ?>' />
				</label>
				<label>
					<?php _e( 'Icon Position', 'megamenu' ); ?><select name='toggle_blocks[<?php echo $block_id; ?>][icon_position]'>
						<option value='before' <?php selected( $settings['icon_position'], 'before' ); ?> ><?php _e( 'Before', 'megamenu' ); ?></option>
						<option value='after' <?php selected( $settings['icon_position'], 'after' ); ?> ><?php _e( 'After', 'megamenu' ); ?></option>
					</select>
				</label>
				<?php $this->print_toggle_block_delete_control(); ?>
			</div>
		</div>

			<?php
		}


		/**
		 * Output the animated menu toggle block HTML on the front end.
		 *
		 * @since 2.5.3
		 * @param string $html     Existing toggle HTML.
		 * @param array  $settings Block settings array.
		 * @param array  $args     wp_nav_menu arguments.
		 * @return string Modified toggle HTML.
		 */
		public function output_menu_public_toggle_block_animated_html( $html, $settings, $args ) {
			$style = isset( $settings['style'] ) ? $settings['style'] : 'slider';
			$label = isset( $settings['aria_label'] ) ? do_shortcode( stripslashes( $settings['aria_label'] ) ) : 'Toggle Menu';

			$html = '<button class="mega-toggle-animated mega-toggle-animated-' . esc_attr( $style ) . '" type="button">
                  <span class="mega-toggle-animated-box">
                    <span class="mega-toggle-animated-inner"></span>
                  </span>
                </button>';

			$processor = new WP_HTML_Tag_Processor( $html );
			if ( $processor->next_tag( 'button' ) ) {
				$processor->set_attribute( 'aria-label', $label );
				$processor->set_attribute( 'aria-haspopup', 'true' );
				$processor->set_attribute( 'aria-expanded', 'false' );
				$processor->set_attribute( 'aria-controls', 'mega-menu-' . $args['theme_location'] );
			}
			$html = $processor->get_updated_html();

			return apply_filters( 'megamenu_toggle_menu_toggle_animated_html', $html );

		}

		/**
		 * Output the admin HTML for the "Menu Toggle (Animated)" block settings panel.
		 *
		 * @since 2.5.3
		 * @param int   $block_id The block index.
		 * @param array $settings Block settings, merged with defaults.
		 * @return void
		 */
		public function output_menu_toggle_block_animated_html( $block_id, $settings = [] ) {

			if ( empty( $settings ) ) {
				$block_id = '0';
			}

			$defaults = [
				'icon_scale' => '0.8',
				'icon_color' => 'rgb(221, 221, 221)',
				'aria_label' => 'Toggle Menu',
			];

			$settings = array_merge( $defaults, $settings );

			?>

		<div class='block'>
			<div class='block-title'><?php _e( 'TOGGLE', 'megamenu' ); ?> <span title='<?php _e( 'Menu Toggle', 'megamenu' ); ?>' class="dashicons dashicons-menu"></span></div>
			<div class='block-settings'>
				<h3><?php _e( 'Animated Menu Toggle Settings', 'megamenu' ); ?></h3>
				<input type='hidden' class='type' name='toggle_blocks[<?php echo $block_id; ?>][type]' value='menu_toggle_animated' />
				<input type='hidden' class='align' name='toggle_blocks[<?php echo $block_id; ?>][align]' value='<?php echo $settings['align']; ?>'>
				<input type='hidden' class='style' name='toggle_blocks[<?php echo $block_id; ?>][style]' value='slider'>
				
				<label>
					<?php _e( 'Color', 'megamenu' ); ?>
					<?php $this->print_toggle_color_option( 'icon_color', $block_id, $settings['icon_color'] ); ?>
				</label>
				<label>
					<?php _e( 'Size', 'megamenu' ); ?><select name='toggle_blocks[<?php echo $block_id; ?>][icon_scale]'>
						<option value='0.6' <?php selected( $settings['icon_scale'], '0.6' ); ?> ><?php _e( 'Small', 'megamenu' ); ?></option>
						<option value='0.8' <?php selected( $settings['icon_scale'], '0.8' ); ?> ><?php _e( 'Medium', 'megamenu' ); ?></option>
						<option value='1.0' <?php selected( $settings['icon_scale'], '1.0' ); ?> ><?php _e( 'Large', 'megamenu' ); ?></option>
						<option value='1.2' <?php selected( $settings['icon_scale'], '1.2' ); ?> ><?php _e( 'X Large', 'megamenu' ); ?></option>
					</select>
				</label>
				<label>
					<?php _e( 'Label', 'megamenu' ); ?><input type='text' class='aria_label' name='toggle_blocks[<?php echo $block_id; ?>][aria_label]' value='<?php echo stripslashes( esc_attr( $settings['aria_label'] ) ); ?>' />
				</label>
				<?php $this->print_toggle_block_delete_control(); ?>
			</div>
		</div>

			<?php
		}


		/**
		 * Icon-only delete control for toggle block settings (button for native semantics; widgets still use anchor).
		 *
		 * @since 3.9.0
		 * @return void
		 */
		private function print_toggle_block_delete_control() {

			?>
		<button type="button" class="mega-delete" data-mega-tooltip="<?php echo esc_attr__( 'Delete', 'megamenu' ); ?>" data-mega-tooltip-position="right" aria-label="<?php echo esc_attr__( 'Delete', 'megamenu' ); ?>">
			<span class="dashicons dashicons-trash" aria-hidden="true"></span>
		</button>
			<?php
		}


		/**
		 * Output an icon selection dropdown for a toggle block setting.
		 *
		 * @since 2.1
		 * @param string $key      The setting key (e.g. 'closed_icon').
		 * @param int    $block_id The block index.
		 * @param string $value    The currently selected icon code.
		 * @param array  $icons    Map of icon codes to Dashicon class names.
		 * @return void
		 */
		public function print_icon_option( $key, $block_id, $value, $icons ) {

			?>
			<select class='icon_dropdown' name='toggle_blocks[<?php echo $block_id; ?>][<?php echo $key; ?>]'>
				<?php

					echo "<option value='disabled'>" . __( 'Disabled', 'megamenu' ) . '</option>';

				foreach ( $icons as $code => $class ) {
					$name = str_replace( 'dashicons-', '', $class );
					$name = ucwords( str_replace( [ '-', 'arrow' ], ' ', $name ) );
					echo "<option data-class='{$class}' value='{$code}'" . selected( $value, $code, false ) . '>' . $name . '</option>';
				}

				?>
			</select>

			<?php
		}


		/**
		 * Output a color picker input for a toggle block setting.
		 *
		 * @since 2.1
		 * @param string $key      The setting key (e.g. 'text_color').
		 * @param int    $block_id The block index.
		 * @param string $value    The currently saved color value.
		 * @return void
		 */
		public function print_toggle_color_option( $key, $block_id, $value ) {

			if ( $value == 'transparent' ) {
				$value = 'rgba(0,0,0,0)';
			}

			if ( $value == 'rgba(0,0,0,0)' ) {
				$value_text = 'transparent';
			} else {
				$value_text = $value;
			}

			echo "<input type='text' class='mega-color-picker-input' name='toggle_blocks[{$block_id}][{$key}]' value='{$value}' />";
		}


		/**
		 * List of all available toggle Dashicon classes.
		 *
		 * @since 2.1
		 * @return array Map of hex code keys to Dashicon CSS class names.
		 */
		public function toggle_icons() {

			$icons = [
				'dash-f333' => 'dashicons-menu',
				'dash-f228' => 'dashicons-menu-alt',
				'dash-f329' => 'dashicons-menu-alt2',
				'dash-f349' => 'dashicons-menu-alt3',
				'dash-f214' => 'dashicons-editor-justify',
				'dash-f158' => 'dashicons-no',
				'dash-f335' => 'dashicons-no-alt',
				'dash-f132' => 'dashicons-plus',
				'dash-f502' => 'dashicons-plus-alt',
				'dash-f460' => 'dashicons-minus',
				'dash-f153' => 'dashicons-dismiss',
				'dash-f142' => 'dashicons-arrow-up',
				'dash-f140' => 'dashicons-arrow-down',
				'dash-f342' => 'dashicons-arrow-up-alt',
				'dash-f346' => 'dashicons-arrow-down-alt',
				'dash-f343' => 'dashicons-arrow-up-alt2',
				'dash-f347' => 'dashicons-arrow-down-alt2',
			];

			$icons = apply_filters( 'megamenu_toggle_icons', $icons );

			return $icons;

		}

	}

endif;
