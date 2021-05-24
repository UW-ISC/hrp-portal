<?php
/**
 * Provides option management and Settings/ General and Documentation submenu pages
 *
 * This file might be shared among multiple example plugins, so load it with:
 *
 *     if ( ! class_exists( 'MLAExamplePluginSettings' ) ) {
 *         require_once( pathinfo( __FILE__, PATHINFO_DIRNAME ) . '/class-mla-example-plugin-settings.php' );
 *     }
 *
 * @package Media Library Assistant
 * @since 1.00
 */

/**
 * Class MLA Example Settings Menu adds Settings/ General and Documentation submenu pages to an example plugin
 *
 * @package MLA Example Settings Menu
 * @since 1.00
 */
class MLAExamplePluginSettings {
	/**
	 * Default values for the __construct function
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $default_arguments = array(
	               'slug_prefix' => 'example-plugin',
				   'plugin_title' => 'The Example Plugin',
				   'menu_title' => 'Example Plugin',
				   'plugin_file_name_only' => 'the-example-plugin',
				   'plugin_version' => '1.00',
				   'template_file' => 'absolute path to the template file', // e.g., dirname( __FILE__ ) . '/admin-settings-page.tpl'
				   'options' => array( 'slug' => array( 'type' => 'text|checkbox', 'default' => 'text|boolean' ) ),
				   'general_tab_values' => array(), // additional page_values for 'page-level-options' template
				   'documentation_tab_values' => array(), // page_values for 'documentation-tab' template
	               );

	/**
	 * Current values for this object instance
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private $current_arguments = array();

	/**
	 * This function sets option definitions and installs filters.
	 *
	 * @since 1.00
	 *
	 * @param	array $attr Option definitions and settings
	 */
	public function __construct( $attr ) {
		// Make sure $attr is an array, even if it's empty
		if ( empty( $attr ) ) {
			$attr = array();
		} elseif ( is_string( $attr ) ) {
			$attr = shortcode_parse_atts( $attr );
		}

		// Accept only the attributes we need and supply defaults
		$this->current_arguments = shortcode_atts( self::$default_arguments, $attr );

		// Compile the default settings
		foreach ( $this->current_arguments['options'] as $slug => $option ) {
			$this->default_settings[ $slug ] = $option['default'];
		}

		if ( is_admin() ) {
			// Add submenu page in the "Settings" section
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}
	}

	/**
	 * Add submenu page in the "Settings" section
	 *
	 * @since 1.00
	 */
	public function admin_menu() {
		/*
		 * We need a tab-specific page ID to manage the screen options on the General tab.
		 * Use the URL suffix, if present. If the URL doesn't have a tab suffix, use '-general'.
		 * This hack is required to pass the WordPress "referer" validation.
		 */
		 if ( isset( $_REQUEST['page'] ) && is_string( $_REQUEST['page'] ) && ( $this->current_arguments['slug_prefix'] . '-settings-' == substr( $_REQUEST['page'], 0, strlen( $this->current_arguments['slug_prefix'] . '-settings-' ) ) ) ) {
			$tab = substr( $_REQUEST['page'], strlen( $this->current_arguments['slug_prefix'] . '-settings-' ) );
		 } else {
			$tab = 'general';
		 }

		$tab = $this->_get_options_tablist( $tab ) ? '-' . $tab : '-general';
		add_submenu_page( 'options-general.php', $this->current_arguments['plugin_title'], $this->current_arguments['menu_title'], 'manage_options', $this->current_arguments['slug_prefix'] . '-settings' . $tab, array( $this, 'add_submenu_page' ) );
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
	}

	/**
	 * Add the "Settings" and "Guide" links to the Plugins section entry
	 *
	 * @since 1.00
	 *
	 * @param	array 	array of links for the Plugin, e.g., "Activate"
	 * @param	string 	Directory and name of the plugin Index file
	 *
	 * @return	array	Updated array of links for the Plugin
	 */
	public function plugin_action_links( $links, $file ) {
		if ( $file == $this->current_arguments['plugin_file_name_only'] . '/' . $this->current_arguments['plugin_file_name_only'] . '.php' ) {
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . $this->current_arguments['slug_prefix'] . '-settings-documentation&mla_tab=documentation' ), 'Guide' );
			array_unshift( $links, $settings_link );
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . $this->current_arguments['slug_prefix'] . '-settings-general' ), 'Settings' );
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	/**
	 * Render (echo) the example plugin's submenu in the Settings section
	 *
	 * @since 1.00
	 *
	 * @return	void Echoes HTML markup for the submenu page
	 */
	public function add_submenu_page() {
		if ( !current_user_can( 'manage_options' ) ) {
			echo '<h2>' . $this->current_arguments['plugin_title'] . " - Error</h2>\n";
			wp_die( 'You do not have permission to manage plugin settings.' );
		}

		// Load template array and initialize page-level values.
		$this->page_template_array = MLACore::mla_load_template( $this->current_arguments['template_file'], 'path' );
		$current_tab_slug = isset( $_REQUEST['mla_tab'] ) ? $_REQUEST['mla_tab']: 'general';
		$current_tab = $this->_get_options_tablist( $current_tab_slug );
		$page_values = array(
			'plugin_title' => $this->current_arguments['plugin_title'],
			'version' => 'v' . $this->current_arguments['plugin_version'],
			'messages' => '',
			'tablist' => $this->_compose_settings_tabs( $current_tab_slug ),
			'tab_content' => '',
		);

		// Compose tab content
		if ( $current_tab ) {
			if ( isset( $current_tab['render'] ) ) {
				$handler = $current_tab['render'];
				$page_content = call_user_func( array( $this, $handler ) );
			} else {
				$page_content = array( 'message' => "ERROR: Cannot render content tab {$current_tab_slug}", 'body' => '' );
			}
		} else {
			$page_content = array( 'message' => "ERROR: Unknown content tab {$current_tab_slug}", 'body' => '' );
		}

		if ( ! empty( $page_content['message'] ) ) {
			if ( false !== strpos( $page_content['message'], 'ERROR' ) ) {
				$messages_class = 'updated error';
			} else {
				$messages_class = 'updated notice is-dismissible';
			}

			$page_values['messages'] = MLAData::mla_parse_template( $this->page_template_array['messages'], array(
				 'mla_messages_class' => $messages_class ,
				 'messages' => $page_content['message'],
			) );
		}

		$page_values['tab_content'] = $page_content['body'];

		echo MLAData::mla_parse_template( $this->page_template_array['page'], $page_values );
	}

	/**
	 * Template file for the Settings page(s) and parts
	 *
	 * This array contains all of the template parts for the Settings page(s). The array is built once
	 * each page load and cached for subsequent use.
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private $page_template_array = NULL;

	/**
	 * Definitions for Settings page tab ids, titles and handlers
	 * Each tab is defined by an array with the following elements:
	 *
	 * array key => HTML id/name attribute and option database key (OMIT MLA_OPTION_PREFIX)
	 *
	 * title => tab label / heading text
	 * render => rendering function for tab messages and content. Usage:
	 *     $tab_content = ['render']();
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private $mla_tablist = array(
		'general' => array( 'title' => 'General', 'render' => '_compose_general_tab' ),
		'documentation' => array( 'title' => 'Documentation', 'render' => '_compose_documentation_tab' ),
		);

	/**
	 * Retrieve the list of options tabs or a specific tab value
	 *
	 * @since 1.00
	 *
	 * @param	string	Tab slug, to retrieve a single entry
	 *
	 * @return	array|false	The entire tablist ( $tab = NULL ), a single tab entry or false if not found/not allowed
	 */
	private function _get_options_tablist( $tab = NULL ) {
		if ( is_string( $tab ) ) {
			if ( isset( $this->mla_tablist[ $tab ] ) ) {
				$results = $this->mla_tablist[ $tab ];
			} else {
				$results = false;
			}
		} else {
			$results = $this->mla_tablist;
		}

		return $results;
	}

	/**
	 * Compose the navigation tabs for the Settings subpage
	 *
	 * @since 1.00
	 * @uses $page_template_array contains tablist and tablist-item templates
 	 *
	 * @param	string	Optional data-tab-id value for the active tab, default 'general'
	 *
	 * @return	string	HTML markup for the Settings subpage navigation tabs
	 */
	private function _compose_settings_tabs( $active_tab = 'general' ) {
		$tablist_item = $this->page_template_array['tablist-item'];
		$tabs = '';
		foreach ( $this->_get_options_tablist() as $key => $item ) {
			$item_values = array(
				'data-tab-id' => $key,
				'nav-tab-active' => ( $active_tab == $key ) ? 'nav-tab-active' : '',
				'settings-page' => $this->current_arguments['slug_prefix'] . '-settings-' . $key,
				'title' => $item['title']
			);

			$tabs .= MLAData::mla_parse_template( $tablist_item, $item_values );
		} // foreach $item

		$tablist_values = array( 'tablist' => $tabs );
		return MLAData::mla_parse_template( $this->page_template_array['tablist'], $tablist_values );
	}

	/**
	 * Compose the General tab content for the Settings subpage
	 *
	 * @since 1.00
	 * @uses $page_template_array contains tab content template(s)
 	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private function _compose_general_tab() {
		$page_content = array( 'message' => '', 'body' => '' );

		// Check for page-level Save Changes, Restore Defaults
		if ( !empty( $_REQUEST[ $this->current_arguments['slug_prefix'] . '_options_save'] ) ) {
			check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );
			$page_content = $this->_save_setting_changes();
		} elseif ( !empty( $_REQUEST[ $this->current_arguments['slug_prefix'] . '_options_reset'] ) ) {
			check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );
			$page_content = $this->_restore_setting_defaults();
		}

		if ( !empty( $page_content['body'] ) ) {
			return $page_content;
		}

		// Display the General tab
		$_SERVER['REQUEST_URI'] = remove_query_arg( array(
			$this->current_arguments['slug_prefix'] . '_options',
			'_wpnonce',
			'_wp_http_referer',
			$this->current_arguments['slug_prefix'] . '_options_save',
			$this->current_arguments['slug_prefix'] . '_options_reset',
		), $_SERVER['REQUEST_URI'] );

		// Compose page-level options
		$page_values = $this->current_arguments['general_tab_values'];
		
		foreach ( $this->current_arguments['options'] as $slug => $option ) {
			if ( 'checkbox' === $option['type'] ) {
				$page_values[ $slug . '_checked' ] = $this->get_plugin_option( $slug ) ? 'checked="checked" ' : '';
			} else {
				$page_values[ $slug  ] = $this->get_plugin_option( $slug );
			}
		}
//error_log( __LINE__ . ' MLAExamplePluginSettings::_compose_general_tab page_values = ' . var_export( $page_values, true ), 0 );

		$options_list = MLAData::mla_parse_template( $this->page_template_array['page-level-options'], $page_values );

		$form_arguments = '?page=' . $this->current_arguments['slug_prefix'] . '-settings-general&mla_tab=general';

		$page_values = array(
			'form_url' => admin_url( 'options-general.php' ) . $form_arguments,
			'_wpnonce' => wp_nonce_field( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME, true, false ),
			'options_list' => $options_list,
			'slug_prefix' => $this->current_arguments['slug_prefix'],
		);

		$page_content['body'] .= MLAData::mla_parse_template( $this->page_template_array['general-tab'], $page_values );

		return $page_content;
	}

	/**
	 * Compose the Documentation tab content for the Settings subpage
	 *
	 * @since 1.00
	 * @uses $page_template_array contains tab content template(s)
 	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private function _compose_documentation_tab() {
		$page_content = array( 'message' => '', 'body' => '' );
		$page_values = array(
		);

		$page_content['body'] = MLAData::mla_parse_template( $this->page_template_array['documentation-tab'], $this->current_arguments['documentation_tab_values'] );
		return $page_content;
	}

	/**
	 * Save settings as a WordPress wp_options entry
	 *
	 * @since 1.00
	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private function _save_setting_changes() {
		$page_content = array( 'message' => 'Settings unchanged.', 'body' => '' );

		$changed = false;
		foreach ( $this->current_arguments['options'] as $slug => $option ) {
			if ( 'checkbox' === $option['type'] ) {
				$changed |= $this->_update_plugin_option( $slug, isset( $_REQUEST[ $this->current_arguments['slug_prefix'] . '_options' ][ $slug ] ) );
			} else {
				if ( isset( $_REQUEST[ $this->current_arguments['slug_prefix'] . '_options' ][ $slug ] ) ) {
					$changed |= $this->_update_plugin_option( $slug, $_REQUEST[ $this->current_arguments['slug_prefix'] . '_options' ][ $slug ] );
				} else {
					$changed |= $this->_update_plugin_option( $slug, $option['default'] );
				}
			}
		} // foreach option

		if ( $changed ) {
			// No reason to save defaults in the database
			if ( $this->current_settings === $this->default_settings ) {
				delete_option( $this->current_arguments['slug_prefix'] . '-settings' ); 
			} else {
				$changed = update_option( $this->current_arguments['slug_prefix'] . '-settings', $this->current_settings, false );
			}

			if ( $changed ) {
				$page_content['message'] = "Settings have been updated.";
			} else {
				$page_content['message'] = "Settings updated failed.";
			}
		}

		return $page_content;		
	} // _save_setting_changes

	/**
	 * Delete the plugin's WordPress wp_options entry, restoring the default settings
	 *
	 * @since 1.00
	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private function _restore_setting_defaults() {
		$page_content = array( 'message' => 'Settings unchanged.', 'body' => '' );
		$this->current_settings = $this->default_settings;
		$changed = delete_option( $this->current_arguments['slug_prefix'] . '-settings' ); 

		if ( $changed ) {
			$page_content['message'] = "Settings have been updated.";
		}

		return $page_content;		
	} // _restore_setting_defaults

	/**
	 * Assemble the in-memory representation of the plugin settings
	 *
	 * @since 1.00
	 *
	 * @param boolean $force_refresh Optional. Force a reload of rules. Default false.
	 *
	 * @return boolean Success (true) or failure (false) of the operation
	 */
	private function _get_plugin_settings( $force_refresh = false ) {
		if ( false == $force_refresh && NULL != $this->current_settings ) {
			return true;
		}

		// Update the plugin options from the wp_options table or set defaults
		$this->current_settings = get_option( $this->current_arguments['slug_prefix'] . '-settings' );
		if ( !is_array( $this->current_settings ) ) {
			$this->current_settings = $this->default_settings;
		}

		// Initialize any new setting(s) from the default settings
		foreach ( $this->current_arguments['options'] as $slug => $option ) {
			if ( !isset( $this->current_settings[ $slug ] ) ) {
				$this->current_settings[ $slug ] = $option['default'];
			}
		}

		return true;
	}

	/**
	 * In-memory representation of the option settings
	 *
	 * @since 1.00
	 *
	 * @var array
	 */
	private $current_settings = NULL;

	/**
	 * Default processing options
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private $default_settings = array();

	/**
	 * Get a plugin option setting
	 *
	 * @since 1.00
	 *
	 * @param string	$name Option name
	 *
	 * @return	mixed	Option value, if it exists else NULL
	 */
	public function get_plugin_option( $name ) {
		if ( !$this->_get_plugin_settings() ) {
			return NULL;
		}

		if ( !isset( $this->current_settings[ $name ] ) ) {
			return NULL;
		}

		return $this->current_settings[ $name ];
	}

	/**
	 * Update a plugin option setting
	 *
	 * @since 1.00
	 *
	 * @param string $name Option name
	 * @param mixed	$new_value Option value
	 *
	 * @return mixed True if option value changed, false if value unchanged, NULL if failure
	 */
	private function _update_plugin_option( $name, $new_value ) {
		if ( !$this->_get_plugin_settings() ) {
			return NULL;
		}

		$old_value = isset( $this->current_settings[ $name ] ) ? $this->current_settings[ $name ] : NULL;

		if ( $new_value === $old_value ) {
			return false;
		}

		$this->current_settings[ $name ] = $new_value;
		return true;
	}
} // Class MLAExamplePluginSettings
?>