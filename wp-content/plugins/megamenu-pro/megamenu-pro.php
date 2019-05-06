<?php

/*
 * Plugin Name: Max Mega Menu - Pro Addon
 * Plugin URI:  https://www.megamenu.com
 * Description: Extends the free version of Max Mega Menu with additional functionality.
 * Version:     1.7.1
 * Author:      megamenu.com
 * Author URI:  https://www.megamenu.com
 * Copyright:   2018 Tom Hemsley (https://www.megamenu.com)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access
}

if ( ! class_exists('Mega_Menu_Pro') ) :

/**
 *
 */
class Mega_Menu_Pro {


	/**
	 * @var string
	 */
	public $version = '1.7.1';


	/**
	 * Init
	 *
	 * @since 1.0
	 */
	public static function init() {

		$plugin = new self();

	}


	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {

		define( "MEGAMENU_PRO_VERSION", $this->version );
		define( "MEGAMENU_PRO_PLUGIN_FILE", __FILE__ );

		add_filter( "megamenu_versions", array( $this, 'add_version_to_header' ) );

        add_action( "admin_print_scripts-nav-menus.php", array( $this, 'enqueue_nav_menu_scripts' ) );

        add_action( "megamenu_admin_scripts", array( $this, 'enqueue_admin_scripts') );

        add_filter( "megamenu_nav_menu_objects_after", array( $this, 'apply_classes_to_menu_items' ), 7, 2 );

		add_action( "admin_notices", array( $this, 'check_megamenu_is_installed' ) );

        add_action( "wp_enqueue_scripts", array( $this, 'enqueue_public_scripts' ), 999 );

		$this->load();

	}


	/**
	 * Adds the version number to the header on the general settings page.
	 *
	 * @since 1.0
	 * @param array $versions
	 * @return array
	 */
	public function add_version_to_header( $versions ) {

		$versions['pro'] = array(
			'text' => __("Pro version", "megamenupro"),
			'version' => MEGAMENU_PRO_VERSION
		);

		return $versions;

	}


	/**
	 * Enqueue scripts
	 *
	 * @since 1.0
	 */
	public function enqueue_nav_menu_scripts() {

		if ( wp_script_is('megamenu-pro-admin') ) {
			return; // enaure scripts are only loaded once
		}

        if ( is_plugin_active( 'megamenu/megamenu.php' ) ) {
        	wp_enqueue_script( 'spectrum', MEGAMENU_BASE_URL . 'js/spectrum/spectrum.js', array( 'jquery' ), MEGAMENU_VERSION );
        	wp_enqueue_style( 'spectrum', MEGAMENU_BASE_URL . 'js/spectrum/spectrum.css', false, MEGAMENU_VERSION );

            wp_enqueue_style( 'megamenu-codemirror', MEGAMENU_BASE_URL . 'js/codemirror/codemirror.css', false, MEGAMENU_VERSION );
	        wp_enqueue_script( 'megamenu-codemirror', MEGAMENU_BASE_URL . 'js/codemirror/codemirror.js', array(), MEGAMENU_VERSION );
        }

        wp_enqueue_style( 'megamenu-genericons', plugins_url( 'icons/genericons/genericons/genericons.css' , __FILE__ ), false, MEGAMENU_PRO_VERSION );
        wp_enqueue_style( 'megamenu-fontawesome', plugins_url( 'icons/fontawesome/css/font-awesome.min.css' , __FILE__ ), false, MEGAMENU_PRO_VERSION );
        wp_enqueue_style( 'megamenu-pro-admin', plugins_url( 'assets/admin.css' , __FILE__ ), false, MEGAMENU_PRO_VERSION );

		wp_enqueue_media();

        wp_enqueue_script( 'megamenu-pro-admin', plugins_url( 'assets/admin.js' , __FILE__ ), array('jquery', 'megamenu-codemirror'), MEGAMENU_PRO_VERSION );

		$params = array(
			'file_frame_title' => __("Media Library", "megamenupro")
		);

		wp_localize_script( 'megamenu-pro-admin', 'megamenu_pro', $params );
	}


    /**
     * Enqueue required CSS and JS for Mega Menu
     *
     */
    public function enqueue_admin_scripts( $hook ) {

        wp_enqueue_media();
        wp_enqueue_style( 'megamenu-pro-admin', plugins_url( 'assets/admin.css' , __FILE__ ), false, MEGAMENU_PRO_VERSION );
        wp_enqueue_script( 'megamenu-pro-admin', plugins_url( 'assets/admin.js' , __FILE__ ), array('jquery'), MEGAMENU_PRO_VERSION );
        wp_enqueue_style( 'megamenu-fontawesome', plugins_url( 'icons/fontawesome/css/font-awesome.min.css' , __FILE__ ), false, MEGAMENU_PRO_VERSION );

        $params = array(
            'file_frame_title' => __("Media Library", "megamenupro")
        );

        wp_localize_script( 'megamenu-pro-admin', 'megamenu_pro', $params );

    }


	/**
	 * Load the plugin classes
	 *
	 * @since 1.0
	 */
	public function load() {

		$plugin_path = plugin_dir_path( __FILE__ );

		$classes = array(
			'Mega_Menu_Updater' => $plugin_path . 'updater/updater.php',
			'Mega_Menu_Sticky' => $plugin_path . 'sticky/sticky.php',
			'Mega_Menu_Google_Fonts' => $plugin_path . 'fonts/google/google-fonts.php',
			'Mega_Menu_Custom_Fonts' => $plugin_path . 'fonts/custom/custom-fonts.php',
 			'Mega_Menu_Custom_Icon' => $plugin_path . 'icons/custom/custom.php',
			'Mega_Menu_Font_Awesome' => $plugin_path . 'icons/fontawesome/fontawesome.php',
			'Mega_Menu_Genericons' => $plugin_path . 'icons/genericons/genericons.php',
			'Mega_Menu_Style_Overrides' => $plugin_path . 'style-overrides/style-overrides.php',
			'Mega_Menu_Roles' => $plugin_path . 'roles/roles.php',
			'Mega_Menu_Vertical' => $plugin_path . 'vertical/vertical.php',
			'Mega_Menu_Replacements' => $plugin_path . 'replacements/replacements.php',
            'Mega_Menu_Tabbed' => $plugin_path . 'tabbed/tabbed.php'
		);

		foreach ( $classes as $classname => $file_path ) {
			require_once( $file_path );
			new $classname;
		}

        if ( class_exists( 'Mega_Menu_Toggle_Blocks' ) ) {
            require_once( $plugin_path . 'toggle-blocks/toggle-blocks.php' );
            new Mega_Menu_Pro_Toggle_Blocks;
        }

	}

    /**
     * Ensure Max Mega Menu (free) is installed
     *
     * @since 1.3
     */
    public function check_megamenu_is_installed() {

        $path = 'megamenu/megamenu.php';

    	if ( is_plugin_active($path) ) {
    		return;
    	}

        $all_plugins = get_plugins();

		if ( isset( $all_plugins[$path] ) ) :

            $plugin = plugin_basename('megamenu/megamenu.php');

            $string = __( 'Max Mega Menu Pro requires Max Mega Menu (free). Please {activate} the Max Mega Menu plugin.', 'megamenu' );

            $link = '<a href="' . wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1', 'activate-plugin_' . $plugin ) . '" class="edit">' . __( 'activate', 'megamenu' ) . '</a>';

	    ?>

	    <div class="updated">
	        <p>
	        	<?php echo str_replace( "{activate}", $link, $string ); ?>
	        </p>
	    </div>

	    <?php

	   	else:

	    ?>
	    <div class="updated">
	        <p>
	        	<?php _e( 'Max Mega Menu Pro requires Max Mega Menu (free). Please install the Max Mega Menu plugin.', 'megamenu' ); ?>
	        </p>
	        <p class='submit'>
	        	<a href="<?php echo admin_url( "plugin-install.php?tab=search&type=term&s=max+mega+menu" ) ?>" class='button button-secondary'><?php _e("Install Max Mega Menu", "megamenupro"); ?></a>
	        </p>
	    </div>
	    <?php

	    endif;

    }

    /**
     * Apply extra classes to menu items
     *
     * @since 1.5
     * @param array $items
     * @param array $args
     * @return array
     */
    public function apply_classes_to_menu_items( $items, $args ) {

        $parents = array();

        foreach ( $items as $item ) {

            if ( $item->depth === 0 && $item->megamenu_settings['type'] == 'tabbed') {
                $item->classes[] = 'menu-megamenu';
            }

            if ( isset($item->megamenu_settings['sticky_visibility']) && $item->megamenu_settings['sticky_visibility'] == 'hide') {
                $item->classes[] = 'hide-when-sticky';
            }

            if ( isset($item->megamenu_settings['sticky_visibility']) && $item->megamenu_settings['sticky_visibility'] == 'show') {
                $item->classes[] = 'show-when-sticky';
            }

        }

        return $items;
    }


    /**
     * Enqueue scripts
     *
     * @since 1.3
     */
    public function enqueue_public_scripts() {

        wp_enqueue_script( 'megamenu-pro', plugins_url( 'assets/public.js' , __FILE__ ), array('megamenu'), MEGAMENU_PRO_VERSION, true );

    }

}

add_action( 'plugins_loaded', array( 'Mega_Menu_Pro', 'init' ), 11 );

endif;