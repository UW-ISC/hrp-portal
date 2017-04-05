<?php
/**
 * Media Library Assistant Admin Columns (plugin) Support
 *
 * @package Media Library Assistant
 * @since 2.50
 */
defined( 'ABSPATH' ) or die();

if ( class_exists( 'ACP_Editing_Strategy' ) ) {
	/**
	 * Class Admin Columns Addon MLA (Media Library Assistant) Editing Strategy supports the Admin Columns plugin
	 *
	 * @package Media Library Assistant
	 * @since 2.50
	 */
	class AC_Addon_MLA_Editing_Strategy extends ACP_Editing_Strategy {
	
		/**
		 * Get the available items on the current page for passing them to JS
		 *
		 * @since 2.50
		 *
		 * @return array Items on the current page ([entry_id] => (array) [entry_data])
		 */
		public function get_rows() {
//error_log( __LINE__ . ' AC_Addon_MLA_Editing_Strategy::get_rows ', 0 );
			$table = $this->column->get_list_screen()->get_list_table();
			$table->prepare_items();
	
//error_log( __LINE__ . ' AC_Addon_MLA_Editing_Strategy::get_rows editable rows = ' . var_export( $this->get_editable_rows( $table->items ), true ), 0 );
			return $this->get_editable_rows( $table->items );
		}
	

		/**
		 * See if the user has write permission for a post/object
		 *
		 * @since 2.50
		 *
		 * @param int $post_id Object ID
		 *
		 * @return bool $post->ID when user can edit object else false.
		 */
		public function user_has_write_permission( $post_id ) {
			$post = is_a( $post_id, 'WP_Post' ) ? $post_id : get_post( $post_id );
	
			return $post && isset( $post->ID ) && current_user_can( 'edit_post', $post->ID ) ? $post->ID : false;
		}
		
		/**
		 * Updates column content from inline editing
		 *
		 * @param int $object_id
		 * @param array $args
		 */
		public function update( $id, $args ) {
//error_log( __LINE__ . " AC_Addon_MLA_Editing_Strategy::update( {$id} ) args = " . var_export( $args, true ), 0 );
return;

			$args['ID'] = $id;
	
			wp_update_post( $args );
		} // */
	}
}

/**
 * Class Admin Columns Addon MLA (Media Library Assistant) List Screen supports the Admin Columns plugin
 *
 * @package Media Library Assistant
 * @since 2.50
 */
class AC_Addon_MLA_ListScreen extends AC_ListScreen_Media {

	/**
	 * Initializes some properties, installs filters and then
	 * calls the parent constructor to set some default configs.
	 *
	 * @since 2.50
	 */
	public function __construct() {
//error_log( __LINE__ . ' AC_Addon_MLA_ListScreen::__construct ', 0 );
		parent::__construct();

		$this->set_key( 'mla-media-assistant' );
		$this->set_label( __( 'Media Library Assistant' ) );
		$this->set_singular_label( __( 'Assistant' ) );
		$this->set_screen_id( 'media_page_' . MLACore::ADMIN_PAGE_SLUG );
		$this->set_page( MLACore::ADMIN_PAGE_SLUG );

		/** @see MLA_List_Table */
		$this->set_list_table_class( 'MLA_List_Table' );
	}

	/**
	 * Contains the hook that contains the manage_value callback
	 *
	 * @since 2.50
	 */
	public function set_manage_value_callback() {
//error_log( __LINE__ . ' AC_Addon_MLA_ListScreen::set_manage_value_callback ', 0 );
		add_filter( 'mla_list_table_column_default', array( $this, 'column_default_value' ), 100, 3 );
	}

	/**
	 * Default column headers
	 *
	 * @since 2.50
	 *
	 * @return array
	 */
	public function get_column_headers() {
		if ( ! class_exists( 'MLAQuery' ) ) {
//error_log( __LINE__ . ' AC_Addon_MLA_ListScreen::get_column_headers require_once ', 0 );
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-data-query.php' );
			MLAQuery::initialize();
		}
//static $first_call = true;
//if ( $first_call ) { error_log( __LINE__ . ' AC_Addon_MLA_ListScreen::get_column_headers (first call only)', 0 ); $first_call = false; }

		return apply_filters( 'mla_list_table_get_columns', MLAQuery::$default_columns );
	}

	/**
	 * Return the column value
	 *
	 * @param string|null $content
	 * @param WP_Post $object
	 * @param string $column_name
	 *
	 * @return string|false
	 */
	public function column_default_value( $content, $post, $column_name ) {
		if ( is_null( $content ) ) {
			$content = $this->get_display_value_by_column_name( $column_name, $post->ID );
		}

//error_log( __LINE__ . " AC_Addon_MLA_ListScreen::column_default_value( $column_name ) content = " . var_export( $content, true ), 0 );
		return $content;
	}


	/**
	 * Create and return a new MLA List Table object
	 *
	 * @param array $args
	 *
	 * @return WP_List_Table|false
	 */
	public function get_list_table( $args = array() ) {
		$class = $this->get_list_table_class();
//error_log( __LINE__ . " AC_Addon_MLA_ListScreen::get_list_table( $class )", 0 );

		if ( ! class_exists( $class ) ) {
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-list-table.php' );
		}

		return new $class;
	}

}