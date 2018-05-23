<?php
/**
 * Synchronizes Media Library values to and from post/page inserted/featured/attached images
 *
 * Adds a Tools/Insert Fixit submenu with buttons to perform the operations.
 *
 * Created for support topic "Changed ALT text doesn't not reflect in published posts"
 * opened on 6/6/2015 by "pikaren":
 * https://wordpress.org/support/topic/changed-alt-text-doesnt-not-reflect-in-published-posts
 *
 * Created for support topic "alt text reconciliation"
 * opened on 6/19/2015 by "fredmr"
 * https://wordpress.org/support/topic/alt-text-reconciliation
 *
 * Enhanced for support topic "How do I extract the id from inserted_in and featured_in?"
 * opened on 6/4/2016 by "Levy"
 * https://wordpress.org/support/topic/how-do-i-extract-the-id-from-inserted_in-and-featured_in
 *
 * Enhanced for support topic "Custom Field Question"
 * opened on 6/8/2016 by "rainydaymum"
 * https://wordpress.org/support/topic/custom-field-question-4
 *
 * Enhanced for support topic "Map Image Att. Tags to attached Post Tags"
 * opened on 8/16/2016 by "sebastianvondreyse"
 * https://wordpress.org/support/topic/map-image-att-tags-to-attached-post-tags
 *
 * Enhanced for support topic "Can you bulk update titles AND add an incrementing number?"
 * opened on 5/18/2017 by "optic"
 * https://wordpress.org/support/topic/can-you-bulk-update-titles-and-add-an-incrementing-number
 *
 * Enhanced for support topic "mla shortcode to show images of all posts of a WP category"
 * opened on 3/01/2018 by "diesel33"
 * https://wordpress.org/support/topic/mla-shortcode-to-show-images-of-all-posts-of-a-wp-category
 *
 * @package Insert Fixit
 * @version 1.09
 */

/*
Plugin Name: MLA Insert Fixit
Plugin URI: http://fairtradejudaica.org/media-library-assistant-a-wordpress-plugin/
Description: Synchronizes Media Library values to and from post/page inserted/featured/attached images
Author: David Lingren
Version: 1.09
Author URI: http://fairtradejudaica.org/our-story/staff/

Copyright 2015-2018 David Lingren

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You can get a copy of the GNU General Public License by writing to the
	Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110, USA
*/

/**
 * Class Insert Fixit implements a Tools submenu page with several image-fixing tools.
 *
 * @package Insert Fixit
 * @since 1.00
 */
class Insert_Fixit {
	/**
	 * Current version number
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const CURRENT_VERSION = '1.09';

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets and scripts
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'insertfixit-';

	/**
	 * Make "Attach" tools unconditional, i.e., overwrite existing parent values
	 *
	 * @since 1.06
	 *
	 * @var	boolean
	 */
	private static $attach_all = false;
	const INPUT_ATTACH_ALL = 'attach-all';

	/**
	 * Make "Attach" tools unconditional, i.e., overwrite existing parent values
	 *
	 * @since 1.06
	 *
	 * @var	boolean
	 */
	private static $reverse_sort = false;
	const INPUT_REVERSE_SORT = 'reverse-sort';

	/**
	 * WordPress version test for $wpdb->esc_like() Vs esc_sql()
	 *
	 * @since 1.00
	 *
	 * @var	boolean
	 */
	private static $wp_4dot0_plus = true;

	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		self::$wp_4dot0_plus = version_compare( get_bloginfo('version'), '4.0', '>=' );

		add_action( 'admin_menu', 'Insert_Fixit::admin_menu_action' );
		add_filter( 'mla_evaluate_custom_data_source', 'Insert_Fixit::mla_evaluate_custom_data_source', 10, 5 );
	}

	/**
	 * Add submenu page in the "Tools" section
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function admin_menu_action( ) {
		$current_page_hook = add_submenu_page( 'tools.php', 'Insert Fixit Tools', 'Insert Fixit', 'manage_options', self::SLUG_PREFIX . 'tools', 'Insert_Fixit::render_tools_page' );
		add_filter( 'plugin_action_links', 'Insert_Fixit::add_plugin_links_filter', 10, 2 );
	}

	/**
	 * Add the "Tools" link to the Plugins section entry
	 *
	 * @since 1.00
	 *
	 * @param	array 	array of links for the Plugin, e.g., "Activate"
	 * @param	string 	Directory and name of the plugin Index file
	 *
	 * @return	array	Updated array of links for the Plugin
	 */
	public static function add_plugin_links_filter( $links, $file ) {
		if ( $file == 'mla-insert-fixit.php' ) {
			$tools_link = sprintf( '<a href="%s">%s</a>', admin_url( 'tools.php?page=' . self::SLUG_PREFIX . 'tools' ), 'Tools' );
			array_unshift( $links, $tools_link );
		}

		return $links;
	}

	/**
	 * Render (echo) the "Insert Fixit" submenu in the Tools section
	 *
	 * @since 1.00
	 *
	 * @return	void Echoes HTML markup for the submenu page
	 */
	public static function render_tools_page() {
//error_log( __LINE__ . ' Insert_Fixit::render_tools_page() $_REQUEST = ' . var_export( $_REQUEST, true ), 0 );
		if ( !current_user_can( 'manage_options' ) ) {
			echo "Insert Fixit - Error</h2>\n";
			wp_die( 'You do not have permission to manage plugin settings.' );
		}

		// Extract relevant query arguments - post/page and item restrictions
		$old_post_lower = isset( $_REQUEST[ self::SLUG_PREFIX . 'old_post_lower' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'old_post_lower' ] : '';
		$post_lower = isset( $_REQUEST[ self::SLUG_PREFIX . 'post_lower' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'post_lower' ] : '';
		$old_post_upper = isset( $_REQUEST[ self::SLUG_PREFIX . 'old_post_upper' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'old_post_upper' ] : '';
		$post_upper = isset( $_REQUEST[ self::SLUG_PREFIX . 'post_upper' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'post_upper' ] : '';
		$old_attachment_lower = isset( $_REQUEST[ self::SLUG_PREFIX . 'old_attachment_lower' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'old_attachment_lower' ] : '';
		$attachment_lower = isset( $_REQUEST[ self::SLUG_PREFIX . 'attachment_lower' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'attachment_lower' ] : '';
		$old_attachment_upper = isset( $_REQUEST[ self::SLUG_PREFIX . 'old_attachment_upper' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'old_attachment_upper' ] : '';
		$attachment_upper = isset( $_REQUEST[ self::SLUG_PREFIX . 'attachment_upper' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'attachment_upper' ] : '';

		// Post/Page Insert Image Tag Attribute Modification
		$old_data_source = isset( $_REQUEST[ self::SLUG_PREFIX . 'old_data_source' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'old_data_source' ] : '';
		$data_source = isset( $_REQUEST[ self::SLUG_PREFIX . 'data_source' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'data_source' ] : '[+alt_text+]';
		$old_attribute_name = isset( $_REQUEST[ self::SLUG_PREFIX . 'old_attribute_name' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'old_attribute_name' ] : '';
		$attribute_name = isset( $_REQUEST[ self::SLUG_PREFIX . 'attribute_name' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'attribute_name' ] : 'data-pin-description';

		// Attach Media Library items
		self::$attach_all = isset( $_REQUEST[ self::SLUG_PREFIX . self::INPUT_ATTACH_ALL ] ) ? true : false;
		$attach_all_attr = self::$attach_all ? ' checked="checked" ' : ' ';
		self::$reverse_sort = isset( $_REQUEST[ self::SLUG_PREFIX . 'first_item' ] ) ? 'highest' === $_REQUEST[ self::SLUG_PREFIX . 'first_item' ] : self::$reverse_sort;
		$lowest_attr = self::$reverse_sort ? ' ' : ' selected="selected" ';
		$highest_attr = self::$reverse_sort ? ' selected="selected" ' : ' ';

		// Copy Post/Page values to inserted Media Library items
		$page_library_template = isset( $_REQUEST[ self::SLUG_PREFIX . 'page_library_template' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'page_library_template' ] : '([+page_terms:category,single+]: )([+page_title+] )[+index+]';

		// Copy Parent values to attached Media Library items
		$parent_library_template = isset( $_REQUEST[ self::SLUG_PREFIX . 'parent_library_template' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'parent_library_template' ] : '([+parent_terms:category,single+]: )([+parent_title+] )[+index+]';
		$from_parent_taxonomy = isset( $_REQUEST[ self::SLUG_PREFIX . 'from_parent_taxonomy' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'from_parent_taxonomy' ] : 'category';
		$to_item_taxonomy = isset( $_REQUEST[ self::SLUG_PREFIX . 'to_item_taxonomy' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'to_item_taxonomy' ] : 'attachment_category';

		// Copy attached Media Library item values to Parent Post/Page
		$item_taxonomy = isset( $_REQUEST[ self::SLUG_PREFIX . 'item_taxonomy' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'item_taxonomy' ] : 'attachment_tag';
		$parent_taxonomy = isset( $_REQUEST[ self::SLUG_PREFIX . 'parent_taxonomy' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'parent_taxonomy' ] : 'post_tag';

		$setting_actions = array(
			'help' => array( 'handler' => '', 'comment' => '<strong>Enter first and (optional) last ID values above to restrict tool application range</strong>. To operate on one ID, enter just the "First ID". The default is to perform the operation on <strong>all posts/pages</strong> and <strong>all Media Library items (attachments)</strong>.<br />&nbsp;<br />You can find post/page ID values by hovering over the post/page title in the "Title" column of the All Posts/All Pages submenu tables; look for the number following <code>post=</code>.<br />' ),
			'warning' => array( 'handler' => '', 'comment' => '<strong>These tools make permanent updates to your database.</strong> Make a backup before you use the tools so you can restore your old values if you don&rsquo;t like the results.' ),

			'c00' => array( 'handler' => '', 'comment' => '<h3>Copy ALT Text between Media Library items and Post/Page inserts</h3>' ),
			'ALT from Item' => array( 'handler' => '_copy_alt_from_media_library',
				'comment' => 'Copy ALT Text from Media Library item to Post/Page inserts.' ),
			'ALT to Item' => array( 'handler' => '_copy_alt_to_media_library',
				'comment' => 'Copy ALT Text from Post/Page inserts to Media Library item' ),
			'c01' => array( 'handler' => '', 'comment' => '<hr>' ),
			'c02' => array( 'handler' => '', 'comment' => '<h3>Post/Page Insert Image Tag Attribute Modification</h3>' ),
			'c03' => array( 'handler' => '', 'comment' => '<strong>NOTE:</strong> Tools in this section use the Data Source and Attribute values below.' ),
			't0101' => array( 'open' => '<table><tr>' ),
			't0102' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">Data Source</td>' ),
			't0103' => array( 'continue' => '  <td style="text-align: left; padding-right: 20px">' ),
			't0104' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . 'old_data_source" type="hidden" value="' . $data_source . '">' ),
			't0105' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . 'data_source" type="text" size="15" value="' . $data_source . '">' ),
			't0106' => array( 'continue' => '  </td>' ),
			't0107' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">Attribute Name</td>' ),
			't0108' => array( 'continue' => '  <td style="text-align: left;">' ),
			't0109' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . 'old_attribute_name" type="hidden" value="' . $attribute_name . '">' ),
			't0110' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . 'attribute_name" type="text" size="15" value="' . $attribute_name . '">' ),
			't0111' => array( 'continue' => '  </td>' ),
			't0112' => array( 'close' => '</tr></table>' ),
			'Add Attribute' => array( 'handler' => '_add_attribute',
				'comment' => 'Add an HTML attribute, e.g., data-pin-description=alt_text, to Post/Page inserts.' ),
			'Replace Attribute' => array( 'handler' => '_replace_attribute',
				'comment' => 'Replace (or add) an HTML attribute, e.g., data-pin-description=alt_text, to Post/Page inserts.' ),
			'Delete Attribute' => array( 'handler' => '_delete_attribute',
				'comment' => 'Delete an HTML attribute, e.g., data-pin-description, from Post/Page inserts' ),
			'c04' => array( 'handler' => '', 'comment' => '<hr>' ),
			'c05' => array( 'handler' => '', 'comment' => '<h3>Attach Media Library items</h3>' ),
			'c06' => array( 'handler' => '', 'comment' => '<strong>NOTE:</strong> By default, tools in this section operate only on <strong>unattached</strong> Media Library items.<br />&nbsp;' ),
			't1201' => array( 'open' => '<table><tr>' ),
			't1202' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle"><input name="' . self::SLUG_PREFIX . self::INPUT_ATTACH_ALL . '" type="checkbox"' . $attach_all_attr . 'value="' . self::INPUT_ATTACH_ALL . '"></td>' ),
			't1203' => array( 'continue' => '  <td style="text-align: left; padding-right: 5px" valign="middle">Replace existing parent</td>' ),
			't1204' => array( 'continue' => '</tr><tr>' ),
			't1205' => array( 'continue' => '  <td>&nbsp;</td><td >Check the box above to assign/reassign ALL items, not just <strong>unattached</strong> items.<br />&nbsp;</td>' ),
			't1206' => array( 'continue' => '</tr><tr>' ),
			't1207' => array( 'continue' => '  <td>&nbsp;</td>' ),
			't1208' => array( 'continue' => '  <td>' ),
			't1209' => array( 'continue' => '    <select name="' . self::SLUG_PREFIX . 'first_item">' ),
			't1210' => array( 'continue' => '      <option' . $lowest_attr . 'value="lowest">Oldest (lowest ID)</option>' ),
			't1211' => array( 'continue' => '      <option' . $highest_attr . 'value="highest">Newest (highest ID)</option>' ),
			't1212' => array( 'continue' => '    </select>' ),
			't1213' => array( 'continue' => '  </td>' ),
			't1214' => array( 'continue' => '</tr><tr>' ),
			't1215' => array( 'continue' => '  <td>&nbsp;</td><td >Select the definition of "first" item from the dropdown above.</td>' ),
			't1216' => array( 'close' => '</tr></table>&nbsp;<br>' ),
			'Attach Inserted In' => array( 'handler' => '_attach_inserted_in',
				'comment' => 'Attach items to the first Post/Page they are inserted in' ),
			'Attach Featured In' => array( 'handler' => '_attach_featured_in',
				'comment' => 'Attach items to the first Post/Page for which they are the Featured Image' ),
			'Attach Referenced In' => array( 'handler' => '_attach_referenced_in',
				'comment' => 'Attach items to the first Post/Page where they appear in a "class wp-image-" or "ids=" element' ),
			'c07' => array( 'handler' => '', 'comment' => '<hr>' ),
			'c08' => array( 'handler' => '', 'comment' => '<h3>Copy Post/Page values to inserted Media Library items</h3>' ),
			'c09' => array( 'handler' => '', 'comment' => 'This tool finds items inserted in the body of a Post or Page and composes a new Title for the items based on values in the Post/Page, adding a sequence number (<code>[+index+]</code>) to make the Title unique. The number of inserted items is available in <code>[+found_rows+]</code>.<br>&nbsp;<br><strong>NOTE:</strong> The Post to Item Title tool uses the Template value below.' ),
			't0201' => array( 'open' => '<table><tr>' ),
			't0202' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">Template</td>' ),
			't0203' => array( 'continue' => '  <td style="text-align: left">' ),
			't0205' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . 'page_library_template" type="text" size="60" value="' . $page_library_template . '">' ),
			't0206' => array( 'continue' => '  </td>' ),
			't0207' => array( 'close' => '</tr></table>' ),
			'Post to Item Title' => array( 'handler' => '_copy_post_values_to_items',
				'comment' => 'Copy "Template" value from Post/Page inserts to Media Library item' ),
			'c13' => array( 'handler' => '', 'comment' => '<hr>' ),
			'c14' => array( 'handler' => '', 'comment' => '<h3>Copy Parent values to attached Media Library items</h3>' ),
			'c15' => array( 'handler' => '', 'comment' => 'The "Parent to Item Title" tool finds items attached to a Post or Page and composes a new Title for the items based on values in the parent Post/Page, adding a sequence number (<code>[+index+]</code>) to make the Title unique. The number of attached items is available in <code>[+found_rows+]</code>.<br>&nbsp;<br><strong>NOTE:</strong> The Parent to Item Title tool uses the Template value below.' ),
			't0301' => array( 'open' => '<table><tr>' ),
			't0302' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">Template</td>' ),
			't0303' => array( 'continue' => '  <td style="text-align: left">' ),
			't0305' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . 'parent_library_template" type="text" size="60" value="' . $parent_library_template . '">' ),
			't0306' => array( 'continue' => '  </td>' ),
			't0307' => array( 'close' => '</tr></table>' ),
			'Parent to Item Title' => array( 'handler' => '_copy_parent_values_to_items',
				'comment' => 'Copy "Template" value from parent Post/Page to (attached) Media Library items' ),
			'c39' => array( 'handler' => '', 'comment' => '&nbsp;<br />The "Parent Terms to Item" tool finds items attached to a Post or Page and copies terms assigned to the parent to the items.<br><strong>NOTE:</strong> The Parent Terms to Item tool uses the Taxonomy name/slug values below.' ),
			't1401' => array( 'open' => '<table><tr>' ),
			't1407' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">from Parent Taxonomy</td>' ),
			't1408' => array( 'continue' => '  <td style="text-align: left; padding-right: 15px">' ),
			't1410' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . 'from_parent_taxonomy" type="text" size="12" value="' . $from_parent_taxonomy . '">' ),
			't1411' => array( 'continue' => '  </td>' ),
			't1402' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">to Item Taxonomy</td>' ),
			't1403' => array( 'continue' => '  <td style="text-align: left; padding-right: 15px">' ),
			't1405' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . 'to_item_taxonomy" type="text" size="12" value="' . $to_item_taxonomy . '">' ),
			't1406' => array( 'continue' => '  </td>' ),
			't1412' => array( 'continue' => '  <td style="text-align: left;">' ),
			't1413' => array( 'continue' => '    <select name="' . self::SLUG_PREFIX . 'item_add_replace">' ),
			't1414' => array( 'continue' => '      <option selected="selected" value="add">Add</option>' ),
			't1415' => array( 'continue' => '      <option value="replace">Replace</option>' ),
			't1416' => array( 'continue' => '    </select>' ),
			't1417' => array( 'continue' => '  </td>' ),
            't1418' => array( 'close' => '</tr></table>' ),
			'Parent Terms to Item' => array( 'handler' => '_copy_parent_terms_to_items',
				'comment' => 'Copy assigned terms from the parent Post/Page to attached items' ),
			'c17' => array( 'handler' => '', 'comment' => '<hr>' ),
			'c18' => array( 'handler' => '', 'comment' => '<h3>Copy attached Media Library item values to Parent Post/Page</h3>' ),
			'c19' => array( 'handler' => '', 'comment' => 'This tool finds items attached to a Post or Page and copies terms assigned to the items to the parent.' ),
			'c16' => array( 'handler' => '', 'comment' => '<br><strong>NOTE:</strong> The Item Terms to Parent tool uses the Taxonomy name/slug values below.' ),
			't0401' => array( 'open' => '<table><tr>' ),
			't0402' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">from Item Taxonomy</td>' ),
			't0403' => array( 'continue' => '  <td style="text-align: left; padding-right: 15px">' ),
			't0405' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . 'item_taxonomy" type="text" size="12" value="' . $item_taxonomy . '">' ),
			't0406' => array( 'continue' => '  </td>' ),
			't0407' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">to Parent Taxonomy</td>' ),
			't0408' => array( 'continue' => '  <td style="text-align: left; padding-right: 15px">' ),
			't0410' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . 'parent_taxonomy" type="text" size="12" value="' . $parent_taxonomy . '">' ),
			't0411' => array( 'continue' => '  </td>' ),
			't0412' => array( 'continue' => '  <td style="text-align: left;">' ),
			't0413' => array( 'continue' => '    <select name="' . self::SLUG_PREFIX . 'add_replace">' ),
			't0414' => array( 'continue' => '      <option selected="selected" value="add">Add</option>' ),
			't0415' => array( 'continue' => '      <option value="replace">Replace</option>' ),
			't0416' => array( 'continue' => '    </select>' ),
			't0417' => array( 'continue' => '  </td>' ),
            't0418' => array( 'close' => '</tr></table>' ),
			'Item Terms to Parent' => array( 'handler' => '_copy_item_terms_to_parent',
				'comment' => 'Copy assigned terms from attached items to the parent Post/Page' ),
			'c10' => array( 'handler' => '', 'comment' => '<hr>' ),
			'c11' => array( 'handler' => '', 'comment' => '<h3>Refresh Caches</h3>' ),
			'c12' => array( 'handler' => '', 'comment' => 'If you have a large number of posts/pages and/or Media Library items you can use the cache refresh operation to break up processing into smaller steps. Try clicking the "Refresh Caches" button to build these intermediate data structures and save them in the WordPress cache for fifteen minutes. That will make the "Copy", "Modification" and "Attach" operations above go quicker.<br>&nbsp;' ),
			'Refresh Caches' => array( 'handler' => '_refresh_caches',
				'comment' => 'rebuild arrays and save in cache for fifteen minutes' ),
 		);

		echo '<div class="wrap">' . "\n";
		echo "\t\t" . '<div id="icon-tools" class="icon32"><br/></div>' . "\n";
		echo "\t\t" . '<h2>Insert Fixit Tools v' . self::CURRENT_VERSION . '</h2>' . "\n";

		if ( isset( $_REQUEST[ self::SLUG_PREFIX . 'action' ] ) ) {
			$label = $_REQUEST[ self::SLUG_PREFIX . 'action' ];
			if( isset( $setting_actions[ $label ] ) ) {
				$action = $setting_actions[ $label ]['handler'];
				if ( ! empty( $action ) ) {
					if ( method_exists( 'Insert_Fixit', $action ) ) {
						echo "\t\t<p style=\"font-size: large\"><strong>Results: </strong>\n";
						echo "\t\t" . self::$action() . "</p>\n";
					} else {
						echo "\t\t<br>ERROR: handler does not exist for action: \"{$label}\"\n";
					}
				} else {
					echo "\t\t<br>ERROR: no handler for action: \"{$label}\"\n";
				}
			} else {
				echo "\t\t<br>ERROR: unknown action: \"{$label}\"\n";
			}
		}

		/*
		 * Invalidate the caches if anything has changed
		 */
		if ( $old_post_lower !== $post_lower || $old_post_upper !== $post_upper || $old_attachment_lower !== $attachment_lower || $old_attachment_upper !== $attachment_upper || $old_data_source !== $data_source || $old_attribute_name !== $attribute_name ) {
			delete_transient( self::SLUG_PREFIX . 'image_inserts' );
			delete_transient( self::SLUG_PREFIX . 'image_objects' );
		}

		echo "\t\t" . '<div style="width:700px">' . "\n";
		echo "\t\t" . '<form action="' . admin_url( 'tools.php?page=' . self::SLUG_PREFIX . 'tools' ) . '" method="post" class="' . self::SLUG_PREFIX . 'tools-form-class" id="' . self::SLUG_PREFIX . 'tools-form-id">' . "\n";
		echo "\t\t" . '    <table>' . "\n";

		echo "\t\t" . '      <tr valign="top">' . "\n";
		echo "\t\t" . '        <td width="24%" style="text-align: right; padding-right: 5px" valign="middle">First Post/Page ID</td>' . "\n";
		echo "\t\t" . '        <td width="24%" style="text-align: left;">' . "\n";
		echo "\t\t" . '          <input name="' . self::SLUG_PREFIX . 'old_post_lower" type="hidden" value="' . $post_lower . '">' . "\n";
		echo "\t\t" . '          <input name="' . self::SLUG_PREFIX . 'post_lower" type="text" size="5" value="' . $post_lower . '">' . "\n";
		echo "\t\t" . '        </td>' . "\n";
		echo "\t\t" . '        <td width="24%" style="text-align: right; padding-right: 5px" valign="middle">First Attachment ID</td>' . "\n";
		echo "\t\t" . '        <td width="24%" style="text-align: left;">' . "\n";
		echo "\t\t" . '          <input name="' . self::SLUG_PREFIX . 'old_attachment_lower" type="hidden" value="' . $attachment_lower . '">' . "\n";
		echo "\t\t" . '          <input name="' . self::SLUG_PREFIX . 'attachment_lower" type="text" size="5" value="' . $attachment_lower . '">' . "\n";
		echo "\t\t" . '        </td>' . "\n";

		echo "\t\t" . '      <tr valign="top">' . "\n";
		echo "\t\t" . '        <td width="24%" style="text-align: right; padding-right: 5px" valign="middle">Last Post/Page ID</td>' . "\n";
		echo "\t\t" . '        <td width="24%" style="text-align: left;">' . "\n";
		echo "\t\t" . '          <input name="' . self::SLUG_PREFIX . 'old_post_upper" type="hidden" value="' . $post_upper . '">' . "\n";
		echo "\t\t" . '          <input name="' . self::SLUG_PREFIX . 'post_upper" type="text" size="5" value="' . $post_upper . '">' . "\n";
		echo "\t\t" . '        </td>' . "\n";
		echo "\t\t" . '        <td width="24%" style="text-align: right; padding-right: 5px" valign="middle">Last Attachment ID</td>' . "\n";
		echo "\t\t" . '        <td width="24%" style="text-align: left;">' . "\n";
		echo "\t\t" . '          <input name="' . self::SLUG_PREFIX . 'old_attachment_upper" type="hidden" value="' . $attachment_upper . '">' . "\n";
		echo "\t\t" . '          <input name="' . self::SLUG_PREFIX . 'attachment_upper" type="text" size="5" value="' . $attachment_upper . '">' . "\n";
		echo "\t\t" . '        </td>' . "\n";
		echo "\t\t" . '    </table>' . "\n";

		echo "\t\t" . '    <table>' . "\n";

		foreach ( $setting_actions as $label => $action ) {
			if ( isset( $action['open'] ) ) {
				echo "\t\t" . '      <tr><td colspan=2 style="padding: 2px 0px;">' . "\n";
				echo "\t\t" . '        ' . $action['open'] . "\n";
			} elseif ( isset( $action['continue'] ) ) {
				echo "\t\t" . '        ' . $action['continue'] . "\n";
			} elseif ( isset( $action['close'] ) ) {
				echo "\t\t" . '        ' . $action['close'] . "\n";
				echo "\t\t" . '      </td></tr>' . "\n";
			} else {
				if ( empty( $action['handler'] ) ) {
					echo "\t\t" . '      <tr><td colspan=2 style="padding: 2px 0px;">' . $action['comment'] . "</td></tr>\n";
				} else {
					echo "\t\t" . '      <tr><td width="160px">' . "\n";
					echo "\t\t" . '        <input name="' . self::SLUG_PREFIX . 'action" type="submit" class="button-primary" style="width: 150px;" value="' . $label . '" />&nbsp;&nbsp;' . "\n";
					echo "\t\t" . '      </td><td>' . "\n";
					echo "\t\t" . '        ' . $action['comment'] . "\n";
					echo "\t\t" . '      </td></tr>' . "\n";
				}
			}
		}

		echo "\t\t" . '    </table>' . "\n";
		echo "\t\t" . '  </p>' . "\n";
		echo "\t\t" . '</form>' . "\n";
		echo "\t\t" . '</div>' . "\n";
		echo "\t\t" . '</div><!-- wrap -->' . "\n";
	}

	/**
	 * Array of post/page IDs giving attached item IDs:
	 * post/page ID => array( attachment IDs )
	 *
	 * @since 1.04
	 *
	 * @var	array
	 */
	private static $attached_items = array();

	/**
	 * Compile array of post/page IDs giving attached item IDs
 	 *
	 * @since 1.00
	 *
	 * @param	boolean	$use_cache True to use an existing cache, false to force rebuild
	 *
	 * @return	string	Cache or rebuild results
	 */
	private static function _build_attached_items_cache( $use_cache = false ) {
		global $wpdb;

		if ( $use_cache ) {
			self::$attached_items = get_transient( self::SLUG_PREFIX . 'attached_items' );
			if ( is_array( self::$attached_items ) ) {
//error_log( __LINE__ . " Insert_Fixit::_build_attached_items_cache using cached self::\$attached_items " . var_export( self::$attached_items, true ), 0 );
				return 'Using cached attached items with ' . count( self::$attached_items ) . ' post/page elements.';
			}
		}

		$return = delete_transient( self::SLUG_PREFIX . 'attached_items' );
//error_log( __LINE__ . " Insert_Fixit::_build_attached_items_cache delete_transient return = " . var_export( $return, true ), 0 );

		if ( ! empty( $_REQUEST[ self::SLUG_PREFIX . 'post_lower' ] ) ) {
			$lower_bound = (integer) $_REQUEST[ self::SLUG_PREFIX . 'post_lower' ];
		} else {
			$lower_bound = 1; // exclude unattached items (post_parent = 0)
		}

		if ( ! empty( $_REQUEST[ self::SLUG_PREFIX . 'post_upper' ] ) ) {
			$upper_bound = (integer) $_REQUEST[ self::SLUG_PREFIX . 'post_upper' ];
		} elseif ( 1 < $lower_bound ) {
			$upper_bound = $lower_bound;
		} else {
			$upper_bound = 0x7FFFFFFF;
		}

		$query = sprintf( 'SELECT ID, post_parent FROM %1$s WHERE ( ( post_type = \'attachment\' ) AND ( post_status = \'inherit\' ) AND ( post_parent >= %2$d ) AND ( post_parent <= %3$d ) ) ORDER BY ID', $wpdb->posts, $lower_bound, $upper_bound );
		$results = $wpdb->get_results( $query );
//error_log( __LINE__ . ' Insert_Fixit::_build_attached_items_cache() $results = ' . var_export( $results, true ), 0 );

		self::$attached_items = array();
		foreach ( $results as $result ) {
			self::$attached_items[ $result->post_parent ][] = $result->ID;
		}

		$return = set_transient( self::SLUG_PREFIX . 'attached_items', self::$attached_items, 900 ); // fifteen minutes
//error_log( __LINE__ . " Insert_Fixit::_build_attached_items_cache set_transient return = " . var_export( $return, true ), 0 );
//error_log( __LINE__ . " Insert_Fixit::_build_attached_items_cache self::\$attached_items " . var_export( self::$attached_items, true ), 0 );

		return 'Attached items cache refreshed with ' . count( self::$attached_items ) . ' post/page elements.';
	} // _build_attached_items_cache

	/**
	 * Array of post/page IDs giving inserted image URL and ALT Text:
	 * post/page ID => array( 
	 *      'content' => post_content,
	 *      'files' => URL to img src,
	 *      'inserts' => array( 'src', 'src_offset', 'alt', 'alt_offset' )
	 *      'replacements' => array()
	 *      )
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $image_inserts = array();

	/**
	 * Compile array of image URLs inserted in posts/pages
 	 *
	 * @since 1.00
	 *
	 * @param	boolean	$use_cache True to use an existing cache, false to force rebuild
	 *
	 * @return	string	Cache or rebuild results
	 */
	private static function _build_image_inserts_cache( $use_cache = false ) {
		global $wpdb;

		if ( $use_cache ) {
			self::$image_inserts = get_transient( self::SLUG_PREFIX . 'image_inserts' );
			if ( is_array( self::$image_inserts ) ) {
//error_log( __LINE__ . " Insert_Fixit::_build_image_inserts_cache using cached self::\$image_inserts " . var_export( self::$image_inserts, true ), 0 );
				return 'Using cached image inserts with ' . count( self::$image_inserts ) . ' post/page elements.';
			}
		}

		$return = delete_transient( self::SLUG_PREFIX . 'image_inserts' );
//error_log( __LINE__ . " Insert_Fixit::_build_image_inserts_cache delete_transient return = " . var_export( $return, true ), 0 );

		if ( ! empty( $_REQUEST[ self::SLUG_PREFIX . 'post_lower' ] ) ) {
			$lower_bound = (integer) $_REQUEST[ self::SLUG_PREFIX . 'post_lower' ];
		} else {
			$lower_bound = 0;
		}

		if ( ! empty( $_REQUEST[ self::SLUG_PREFIX . 'post_upper' ] ) ) {
			$upper_bound = (integer) $_REQUEST[ self::SLUG_PREFIX . 'post_upper' ];
		} elseif ( $lower_bound ) {
			$upper_bound = $lower_bound;
		} else {
			$upper_bound = 0x7FFFFFFF;
		}

		$query = sprintf( 'SELECT ID, post_content FROM %1$s WHERE ( post_type IN ( \'post\', \'page\' ) AND ( post_status = \'publish\' ) AND ( ID >= %2$d ) AND ( ID <= %3$d ) AND ( post_content LIKE \'%4$s\' ) ) ORDER BY ID', $wpdb->posts, $lower_bound, $upper_bound, '%<img%' );
		$results = $wpdb->get_results( $query );
//error_log( __LINE__ . ' Insert_Fixit::_build_image_inserts_cache() $results = ' . var_export( $results, true ), 0 );

		$upload_dir = wp_upload_dir();
//error_log( __LINE__ . ' Insert_Fixit::_build_image_inserts_cache() $upload_dir = ' . var_export( $upload_dir, true ), 0 );
		$upload_dir = $upload_dir['baseurl'] . '/';

		$image_inserts = array();
		foreach ( $results as $result ) {
			$match_count = preg_match_all( '/\<img[^src]*(src="([^"]*)")[^\>]*\>/', $result->post_content, $matches, PREG_OFFSET_CAPTURE );
//error_log( __LINE__ . " Insert_Fixit::_build_image_inserts_cache( {$result->ID} ) \$matches = " . var_export( $matches, true ), 0 );
			if ( $match_count ) {
				$image_inserts[ $result->ID ]['content'] = $result->post_content;

				// complete <img ... /> tag
				foreach( $matches[0] as $index => $match ) {
					$image_inserts[ $result->ID ]['inserts'][ $index ] = array( 'img' => $match[0], 'img_offset' => $match[1] );
				}

				// complete src= attribute
				foreach( $matches[1] as $index => $match ) {
					$image_inserts[ $result->ID ]['inserts'][ $index ]['src_att'] = $match[0];
					$image_inserts[ $result->ID ]['inserts'][ $index ]['src_att_offset'] = $match[1];
				}

				// src= file URL
				foreach( $matches[2] as $index => $match ) {
					$file = str_replace( $upload_dir, '', $match[0] );
					$image_inserts[ $result->ID ]['files'][] = $file;
					$image_inserts[ $result->ID ]['inserts'][ $index ]['src'] = $file;
					$image_inserts[ $result->ID ]['inserts'][ $index ]['src_offset'] = $match[1];
				}

				// alt= value if present
				foreach ( $image_inserts[ $result->ID ]['inserts'] as $index => $insert ) {
					$match_count = preg_match( '/alt="([^"]*)"/', $insert['img'], $matches, PREG_OFFSET_CAPTURE );
//error_log( __LINE__ . " Insert_Fixit::_build_image_inserts_cache( {$result->ID} ) \$matches = " . var_export( $matches, true ), 0 );
					if ( $match_count ) {
						$image_inserts[ $result->ID ]['inserts'][ $index ]['alt'] = $matches[1][0];
						$image_inserts[ $result->ID ]['inserts'][ $index ]['alt_offset'] = $insert['img_offset'] + $matches[1][1];
					}
				}

				$image_inserts[ $result->ID ]['replacements'] = array();
			}
		}

		$return = set_transient( self::SLUG_PREFIX . 'image_inserts', $image_inserts, 900 ); // fifteen minutes
//error_log( __LINE__ . " Insert_Fixit::_build_image_inserts_cache set_transient return = " . var_export( $return, true ), 0 );
//error_log( __LINE__ . " Insert_Fixit::_build_image_inserts_cache image_inserts " . var_export($image_inserts, true ), 0 );
		self::$image_inserts = $image_inserts;

		return 'Image inserts cache refreshed with ' . count( self::$image_inserts ) . ' post/page elements.';
	} // _build_image_inserts_cache

	/**
	 * Array of attachment IDs giving post_parent and Featured Image post/page IDs:
	 * attachment ID => array( 'parent' => post_parent, post/page IDs => post/page IDs  )
	 *
	 * @since 1.01
	 *
	 * @var	array
	 */
	private static $featured_objects = array();

	/**
	 * Compile array of attachment IDs used as post/page Featured Image
 	 *
	 * @since 1.01
	 *
	 * @param	boolean	$use_cache True to use an existing cache, false to force rebuild
	 * @param	boolean	$unattached_only True to index only unattached items, false to index all items
	 * @param	boolean	$reverse_sort True to sort from highest to lowest value, false to sort lowest to highest
	 *
	 * @return	string	Cache or rebuild results
	 */
	private static function _build_featured_objects_cache( $use_cache = false, $unattached_only = false, $reverse_sort ) {
		global $wpdb;

		if ( $use_cache ) {
			self::$featured_objects = get_transient( self::SLUG_PREFIX . 'featured_objects' );
			if ( is_array( self::$featured_objects ) ) {
//error_log( __LINE__ . " Insert_Fixit::_build_featured_objects_cache using cached self::\$featured_objects " . var_export( self::$featured_objects, true ), 0 );
				return 'Using cached featured objects with ' . count( self::$featured_objects ) . ' attachment elements.';
			}
		}

		$return = delete_transient( self::SLUG_PREFIX . 'featured_objects' );
//error_log( __LINE__ . " Insert_Fixit::_build_featured_objects_cache delete_transient return = " . var_export( $return, true ), 0 );

		if ( ! empty( $_REQUEST[ self::SLUG_PREFIX . 'attachment_lower' ] ) ) {
			$lower_bound = (integer) $_REQUEST[ self::SLUG_PREFIX . 'attachment_lower' ];
		} else {
			$lower_bound = 0;
		}

		if ( ! empty( $_REQUEST[ self::SLUG_PREFIX . 'attachment_upper' ] ) ) {
			$upper_bound = (integer) $_REQUEST[ self::SLUG_PREFIX . 'attachment_upper' ];
		} elseif ( $lower_bound ) {
			$upper_bound = $lower_bound;
		} else {
			$upper_bound = 0x7FFFFFFF;
		}

		$query = array();
		$query[] = "SELECT p.ID, p.post_parent, m.post_id FROM {$wpdb->postmeta} AS m INNER JOIN";

		$where = str_replace( '%', '%%', wp_post_mime_type_where( 'image', '' ) );

		if ( $unattached_only ) {
			$where .= ' AND post_parent = 0';
		}

		$query[] = "( SELECT ID, post_parent FROM {$wpdb->posts} WHERE ( ( post_type = 'attachment' ) {$where}";
		$query[] = "AND ( ID >= {$lower_bound} ) AND ( ID <= {$upper_bound} ) ) ORDER BY ID ) AS p ON m.meta_value = p.ID";
		$query[] = "WHERE m.meta_key = '_thumbnail_id'";
		$query = implode( ' ', $query );
//error_log( __LINE__ . ' Insert_Fixit::_build_featured_objects_cache() $query = ' . var_export( $query, true ), 0 );
		$results = $wpdb->get_results( $query );
//error_log( __LINE__ . ' Insert_Fixit::_build_featured_objects_cache() $results = ' . var_export( $results, true ), 0 );

		$references = array();
		if ( is_array( $results ) ) {
			foreach ( $results as $result ) {
				$references[ $result->ID ]['parent'] = $result->post_parent;
				$id = absint( $result->post_id );
				$references[ $result->ID ][ $id ] = $id;
			}

			foreach( $references as $id => $result ) {
				if ( $reverse_sort ) {
					krsort( $references[ $id ] );
				} else {
					ksort( $references[ $id ] );
				}
			}
		}

		$return = set_transient( self::SLUG_PREFIX . 'featured_objects', $references, 900 ); // fifteen minutes
//error_log( __LINE__ . " Insert_Fixit::_build_featured_objects_cache set_transient return = " . var_export( $return, true ), 0 );
//error_log( __LINE__ . " Insert_Fixit::_build_featured_objects_cache references = " . var_export( $references, true ), 0 );
		self::$featured_objects = $references;

		return 'Featured objects cache refreshed with ' . count( self::$featured_objects ) . ' attachment elements.';
	} // _build_featured_objects_cache

	/**
	 * Array of attachment IDs giving posts/pages their ID appears in
	 * attachment ID => array( 'parent' => post_parent, post/page IDs => post/page IDs )
	 *
	 * @since 1.08
	 *
	 * @var	array
	 */
	private static $item_references = array();

	/**
	 * Compile array of item IDs referenced in posts/pages by "wp-image-" or "ids="
 	 *
	 * @since 1.00
	 *
	 * @param	boolean	$use_cache True to use an existing cache, false to force rebuild
	 *
	 * @return	string	Cache or rebuild results
	 */
	private static function _build_item_references_cache( $use_cache = false ) {
		global $wpdb;

		if ( $use_cache ) {
			self::$item_references = get_transient( self::SLUG_PREFIX . 'item_references' );
			if ( is_array( self::$item_references ) ) {
//error_log( __LINE__ . " Insert_Fixit::_build_item_references_cache using cached self::\$item_references " . var_export( self::$item_references, true ), 0 );
				return 'Using cached item references with ' . count( self::$item_references ) . ' attachment elements.';
			}
		}

		$return = delete_transient( self::SLUG_PREFIX . 'item_references' );
//error_log( __LINE__ . " Insert_Fixit::_build_item_references_cache delete_transient return = " . var_export( $return, true ), 0 );

		if ( ! empty( $_REQUEST[ self::SLUG_PREFIX . 'post_lower' ] ) ) {
			$lower_bound = (integer) $_REQUEST[ self::SLUG_PREFIX . 'post_lower' ];
		} else {
			$lower_bound = 0;
		}

		if ( ! empty( $_REQUEST[ self::SLUG_PREFIX . 'post_upper' ] ) ) {
			$upper_bound = (integer) $_REQUEST[ self::SLUG_PREFIX . 'post_upper' ];
		} elseif ( $lower_bound ) {
			$upper_bound = $lower_bound;
		} else {
			$upper_bound = 0x7FFFFFFF;
		}

		$query = sprintf( 'SELECT ID, post_content FROM %1$s WHERE ( post_type IN ( \'post\', \'page\' ) AND ( post_status = \'publish\' ) AND ( ID >= %2$d ) AND ( ID <= %3$d ) AND ( ( post_content LIKE \'%4$s\' ) OR ( post_content LIKE \'%5$s\' ) ) ) ORDER BY ID', $wpdb->posts, $lower_bound, $upper_bound, '%wp-image-%', '%ids=%' );
		$results = $wpdb->get_results( $query );
//error_log( __LINE__ . ' Insert_Fixit::_build_item_references_cache() $results = ' . var_export( $results, true ), 0 );

		self::$item_references = array();
		foreach ( $results as $result ) {
			// Find the class="wp-image-" references
			$match_count = preg_match_all( '/wp-image-([0-9]{1,6})/', $result->post_content, $matches );
//error_log( __LINE__ . " Insert_Fixit::_build_item_references_cache( {$result->ID} ) \$matches = " . var_export( $matches, true ), 0 );
			if ( $match_count ) {
				foreach ( $matches[1] as $match ) {
					self::$item_references[ absint( $match ) ][ absint( $result->ID ) ] = absint( $result->ID );
				}
			}

			// Find the ids= references
			$match_count = preg_match_all( '/(\[gallery|\[mla_gallery)[^\]]*ids=([0-9,\\\'\"]*)/', $result->post_content, $matches );
//error_log( __LINE__ . " Insert_Fixit::_build_item_references_cache( {$result->ID} ) \$matches = " . var_export( $matches, true ), 0 );
			if ( $match_count ) {
				foreach ( $matches[2] as $match ) {
					$items = explode( ',', trim( $match, '\'"' ) );
					foreach ( $items as $item ) {
						self::$item_references[ absint( $item ) ][ absint( $result->ID ) ] = absint( $result->ID );
					}
				}
			}
		}

		$return = set_transient( self::SLUG_PREFIX . 'item_references', self::$item_references, 900 ); // fifteen minutes
//error_log( __LINE__ . " Insert_Fixit::_build_item_references_cache set_transient return = " . var_export( $return, true ), 0 );
//error_log( __LINE__ . " Insert_Fixit::_build_item_references_cache item_references " . var_export( self::$item_references, true ), 0 );

		return 'Item references cache refreshed with ' . count( self::$item_references ) . ' items referenced in ' . count( $results ) . ' post/page elements.';
	} // _build_item_references_cache

	/**
	 * Array of attachment IDs giving inserted image files:
	 * attachment ID => array( 'parent' => post_parent, post/page IDs => array( URLs to inserted file ) )
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $image_objects = array();

	/**
	 * Compile array of image URLs inserted in posts/pages
 	 *
	 * @since 1.00
	 *
	 * @param	boolean	$use_cache True to use an existing cache, false to force rebuild
	 * @param	boolean	$unattached_only True to index only unattached items, false to index all items
	 * @param	boolean	$reverse_sort True to sort from highest to lowest value, false to sort lowest to highest
	 * @param	boolean	$add_parent True to add post_parent to inserts array, false to omit
	 *
	 * @return	string	Cache or rebuild results
	 */
	private static function _build_image_objects_cache( $use_cache = false, $unattached_only = false, $reverse_sort = false, $add_parent = false ) {
		global $wpdb;

		if ( $use_cache ) {
			self::$image_objects = get_transient( self::SLUG_PREFIX . 'image_objects' );
			if ( is_array( self::$image_objects ) ) {
//error_log( __LINE__ . " Insert_Fixit::_build_image_objects_cache using cached self::\$image_objects " . var_export( self::$image_objects, true ), 0 );
				return 'Using cached image objects with ' . count( self::$image_objects ) . ' attachment elements.';
			}
		}

		$return = delete_transient( self::SLUG_PREFIX . 'image_objects' );
//error_log( __LINE__ . " Insert_Fixit::_build_image_objects_cache delete_transient return = " . var_export( $return, true ), 0 );

		if ( ! empty( $_REQUEST[ self::SLUG_PREFIX . 'attachment_lower' ] ) ) {
			$lower_bound = (integer) $_REQUEST[ self::SLUG_PREFIX . 'attachment_lower' ];
		} else {
			$lower_bound = 0;
		}

		if ( ! empty( $_REQUEST[ self::SLUG_PREFIX . 'attachment_upper' ] ) ) {
			$upper_bound = (integer) $_REQUEST[ self::SLUG_PREFIX . 'attachment_upper' ];
		} elseif ( $lower_bound ) {
			$upper_bound = $lower_bound;
		} else {
			$upper_bound = 0x7FFFFFFF;
		}

		$where = str_replace( '%', '%%', wp_post_mime_type_where( 'image', '' ) );

		if ( $unattached_only ) {
			$where .= ' AND post_parent = 0';
		}

		$query = sprintf( 'SELECT ID, post_parent FROM %1$s WHERE ( ( post_type = \'attachment\' ) %2$s AND ( ID >= %3$d ) AND ( ID <= %4$d ) ) ORDER BY ID', $wpdb->posts, $where, $lower_bound, $upper_bound );
		$results = $wpdb->get_results( $query );
//error_log( __LINE__ . ' Insert_Fixit::_build_image_objects_cache() $results = ' . var_export( $results, true ), 0 );

		// Load the image_inserts array
		self::_build_image_inserts_cache( true );

		$references = array();
		foreach ( $results as $result ) {
			// assemble the files
			$files = array();

			$base_file = get_metadata( 'post', $result->ID, '_wp_attached_file', true );
			if ( empty( $base_file ) ) {
				$base_file = '';
			}

			$pathinfo = pathinfo( $base_file );
			if ( ( ! isset( $pathinfo['dirname'] ) ) || '.' == $pathinfo['dirname'] ) {
				// $path = '/';
				$path = '';
			} else {
				$path = $pathinfo['dirname'] . '/';
			}

			$file = $pathinfo['basename'];

			$attachment_metadata = get_metadata( 'post', $result->ID, '_wp_attachment_metadata', true );
			if ( empty( $attachment_metadata ) ) {
				$attachment_metadata = array();
			}

			$sizes = isset( $attachment_metadata['sizes'] ) ? $attachment_metadata['sizes'] : NULL;
//error_log( __LINE__ . " Insert_Fixit::_array_image_inserts_references( {$result->ID} ) sizes = " . var_export( $sizes, true ), 0 );
			if ( ! empty( $sizes ) && is_array( $sizes ) ) {
				// Using the path and name as the array key ensures each name is added only once
				foreach ( $sizes as $size => $size_info ) {
					$files[ $path . $size_info['file'] ] = $path . $size_info['file'];
				}
			}

			if ( ! empty( $base_file ) ) {
				//$files[ $path . $base_file ] = $path . $base_file;
				$files[ $base_file ] = $base_file;
			}
//error_log( __LINE__ . " Insert_Fixit::_array_image_inserts_references( {$result->ID} ) files = " . var_export( $files, true ), 0 );

			/*
			 * inserts	Array of specific files (i.e., sizes) found in one or more posts/pages
			 *			as an image (<img>). The array key is the path and file name.
			 *			The array value is the post/page ID
			 */
			$inserts = array();

			foreach( $files as $file ) {
				foreach ( self::$image_inserts as $insert_id => $value ) {
					if ( in_array( $file, $value['files'] ) ) {
						$inserts[ $insert_id ][] = $file;
					}
				} // foreach insert
			} // foreach file

			if ( ! empty( $inserts ) ) {
				if ( $reverse_sort ) {
					krsort( $inserts );
				} else {
					ksort( $inserts );
				}

				if ( $add_parent ) {
					$inserts['parent'] = $result->post_parent;
				}

				$references[ $result->ID ] = $inserts;
			}
		} // each result

		$return = set_transient( self::SLUG_PREFIX . 'image_objects', $references, 900 ); // fifteen minutes
//error_log( __LINE__ . " Insert_Fixit::_build_image_objects_cache set_transient return = " . var_export( $return, true ), 0 );
//error_log( __LINE__ . " Insert_Fixit::_build_image_objects_cache references = " . var_export( $references, true ), 0 );
		self::$image_objects = $references;

		return 'Image objects cache refreshed with ' . count( self::$image_objects ) . ' attachment elements.';
	} // _build_image_objects_cache

	/**
	 * Array of custom data sources for template expansion
	 *
	 * @since 1.04
	 *
	 * @var	array
	 */
	private static $custom_data_sources = array();

	/**
	 * Replace custom data sources with anything found in self::$custom_data_sources array
	 *
	 * @since 1.04
	 *
	 * @param	string	NULL, indicating that by default, no custom value is available
	 * @param	integer	attachment ID for attachment-specific values
	 * @param	string 	category/scope to evaluate against: custom_field_mapping or single_attachment_mapping
	 * @param	array	data source specification ( name, *data_source, *keep_existing, *format, mla_column, quick_edit, bulk_edit, *meta_name, *option, no_null )
	 * @param	array 	_wp_attachment_metadata, default NULL (use current postmeta database value)
	 */
	public static function mla_evaluate_custom_data_source( $custom_value, $post_id, $category, $data_value, $attachment_metadata ) {
		global $post;

		//error_log( __LINE__ . " Insert_Fixit::mla_evaluate_custom_data_source( {$post_id}, {$category} ) data_value = " . var_export( $data_value, true ), 0 );
		//error_log( __LINE__ . " Insert_Fixit::mla_evaluate_custom_data_source( {$post_id}, {$category} ) attachment_metadata = " . var_export( $attachment_metadata, true ), 0 );

		if ( isset( self::$custom_data_sources[ $data_value['data_source'] ] ) ) {
			return self::$custom_data_sources[ $data_value['data_source'] ];
		} elseif ( is_object( $post ) ) {
			$key = str_replace( 'page_', 'post_', $data_value['data_source'] );
			$fields = get_object_vars( $post );

			if ( isset( $fields[ $key ] ) ) {
				return (string) $fields[ $key ];
			}
		}

		return $custom_value;
	} // mla_evaluate_custom_data_source

	/**
	 * Add, replace or delete an attribute from <img ... /> tags in a post/page
 	 *
	 * @since 1.02
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _evaluate_add_attribute( $operation = 'Add' ) {
		// Attribute name must be present
		$attribute_name = isset( $_REQUEST[ self::SLUG_PREFIX . 'attribute_name' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'attribute_name' ] : 'data-pin-description';

		$preg_pattern = '/ ' . $attribute_name . '="([^"]*)"[ ]*/';
//error_log( __LINE__ . " Insert_Fixit::_build_image_inserts_cache( {$operation} ) \$preg_pattern = " . var_export( $preg_pattern, true ), 0 );

		/*
		 * Load the image_inserts array
		 */
		self::_build_image_inserts_cache( true );

		/*
		 * Load the image_objects array
		 */
		self::_build_image_objects_cache( true );

		// Initialize statistics
		$image_inserts = count( self::$image_inserts );
		$image_objects = count( self::$image_objects );
		$updates = 0;
		$updated_posts = 0;
		$errors = 0;

		if ( 'Delete' == $operation ) {
			foreach ( self::$image_objects as $attachment_id => $references ) {
				foreach ( $references as $post_id => $files ) {
					$inserts = self::$image_inserts[ $post_id ];
					foreach ( $files as $file ) {
						foreach ( $inserts['inserts'] as $insert ) {
							if ( $file != $insert['src'] ) {
								continue;
							}

//error_log( __LINE__ . " Insert_Fixit::_evaluate_add_attribute( {$attachment_id}, {$post_id}, {$file} ) insert =  " . var_export( $insert, true ), 0 );
							$match_count = preg_match( $preg_pattern, $insert['img'], $matches, PREG_OFFSET_CAPTURE );
//error_log( __LINE__ . " Insert_Fixit::_build_image_inserts_cache( {$match_count} ) \$matches = " . var_export( $matches, true ), 0 );
							if ( $match_count ) {
								$new_tag = substr_replace( $insert['img'], ' ', $matches[0][1], strlen( $matches[0][0] ) );

								// Queue replacement
								self::$image_inserts[ $post_id ]['replacements'][ $insert['img_offset'] ] = array( 'length' => strlen( $insert['img'] ), 'text' => $new_tag );
							}
						} // foreach file
					} // foreach reference
				} // foreach insert
			}
		} else {
			// Find the data source
			if ( ! empty( $_REQUEST[ self::SLUG_PREFIX . 'data_source' ] ) ) {
				$data_source = $_REQUEST[ self::SLUG_PREFIX . 'data_source' ];

				if ( 'template:' == substr( $data_source, 0, 9 ) ) {
					$data_source = substr( $data_source, 9 );
				} else {
					$data_source = '(' . $data_source . ')';
				}
			} else {
				$data_source = '([+alt_text+])';
			}

			$data_source = array(
				'data_source' => 'template',
				'meta_name' => $data_source,
				'option' => 'text',
				'format' => 'raw',
			);
//error_log( __LINE__ . " Insert_Fixit::_evaluate_add_attribute( {$operation} ) data_source = " . var_export( $data_source, true ), 0 );

			foreach ( self::$image_objects as $attachment_id => $references ) {
				$data_value = MLAOptions::mla_get_data_source( $attachment_id, 'single_attachment_mapping', $data_source, NULL );
//error_log( __LINE__ . " Insert_Fixit::_evaluate_add_attribute( {$attachment_id} ) data_value = " . var_export( $data_value, true ), 0 );
				if ( empty( $data_value ) ) {
					$new_value = ' ';
				} else {
					$new_value = ' ' . $attribute_name . '="' . $data_value . '" ';
				}

				// Add replacements to each post/page in self::$image_inserts
				foreach ( $references as $post_id => $files ) {
					$inserts = self::$image_inserts[ $post_id ];
					foreach ( $files as $file ) {
						foreach ( $inserts['inserts'] as $insert ) {
//error_log( __LINE__ . " Insert_Fixit::_evaluate_add_attribute file test '{$file}' == " . var_export( $insert['src'], true ), 0 );
							if ( $file != $insert['src'] ) {
								continue;
							}

//error_log( __LINE__ . " Insert_Fixit::_evaluate_add_attribute( {$operation}, {$attachment_id}, {$post_id}, {$file} ) insert =  " . var_export( $insert, true ), 0 );
							$match_count = preg_match( $preg_pattern, $insert['img'], $matches, PREG_OFFSET_CAPTURE );
//error_log( __LINE__ . " Insert_Fixit::_build_image_inserts_cache( {$match_count} ) \$matches = " . var_export( $matches, true ), 0 );
							if ( $match_count ) {
								if ( 'Replace' == $operation ) {
									if ( $data_value != $matches[1][0] ) {
										$new_tag = substr_replace( $insert['img'], $new_value, $matches[0][1], strlen( $matches[0][0] ) );

										// Queue replacement
										self::$image_inserts[ $post_id ]['replacements'][ $insert['img_offset'] ] = array( 'length' => strlen( $insert['img'] ), 'text' => $new_tag );
									} // value changed
								} // found attribute
							} else {
								// add after src= attribute
								$new_offset = $insert['src_att_offset'] + strlen( $insert['src_att'] );

								// Queue replacement
								self::$image_inserts[ $post_id ]['replacements'][ $new_offset ] = array( 'length' => 0, 'text' => $new_value );							}
						} // foreach file
					} // foreach reference
				} // foreach insert
			} // foreach attachment
		} // add/replace

		// Apply replacements
		foreach ( self::$image_inserts as $post_id => $inserts ) {
			$replacements = $inserts['replacements'];
			if ( ! empty( $replacements ) ) {
				krsort( $replacements );
//error_log( __LINE__ . " Insert_Fixit::_evaluate_add_attribute( {$post_id} ) replacements =  " . var_export( $replacements, true ), 0 );
				$post_content = $inserts['content'];
				foreach ( $replacements as $offset => $replacement ) {
					$post_content = substr_replace( $post_content, $replacement['text'], $offset, $replacement['length'] );
					$updates++;
				} // foreach replacement
//error_log( __LINE__ . " Insert_Fixit::_evaluate_add_attribute( {$post_id} ) new post_content =  " . var_export( $post_content, true ), 0 );
				$new_content = array( 'ID' => $post_id, 'post_content' => $post_content );
				$result = wp_update_post( $new_content, true );
//error_log( __LINE__ . " Insert_Fixit::_evaluate_add_attribute( {$post_id} ) update result =  " . var_export( $result, true ), 0 );
				if ( is_wp_error( $result ) ) {
					$errors++;
				}

				$updated_posts++;
			} // has replacements
		} // foreach post/page

		/*
		 * Invalidate the image_inserts cache, since post/page content has changed.
		 */
		if ( $updated_posts ) {		
			delete_transient( self::SLUG_PREFIX . 'image_inserts' );
		}

		return "<br>{$operation} Attribute matched {$image_inserts} posts/pages to {$image_objects} attachments and made {$updates} update(s) in {$updated_posts} posts/pages. There were {$errors} error(s).\n";
	} // _evaluate_add_attribute

	/**
	 * Copy ALT Text from Media Library item to Post/Page inserts
 	 *
	 * @since 1.00
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _copy_alt_from_media_library() {
		/*
		 * Load the image_inserts array
		 */
		self::_build_image_inserts_cache( true );

		/*
		 * Load the image_objects array
		 */
		self::_build_image_objects_cache( true );

		// Initialize statistics
		$image_inserts = count( self::$image_inserts );
		$image_objects = count( self::$image_objects );
		$updates = 0;
		$updated_posts = 0;
		$errors = 0;

		foreach ( self::$image_objects as $attachment_id => $references ) {
			$alt_text = get_metadata( 'post', $attachment_id, '_wp_attachment_image_alt', true );
			if ( empty( $alt_text ) ) {
				$alt_text = '';
			}

			// Add replacements to each post/page in self::$image_inserts
			foreach ( $references as $post_id => $files ) {
				$inserts = self::$image_inserts[ $post_id ];
				foreach ( $files as $file ) {
					foreach ( $inserts['inserts'] as $insert ) {
//error_log( __LINE__ . " Insert_Fixit::_copy_alt_from_media_library file test '{$file}' == " . var_export( $insert['src'], true ), 0 );
						if ( $file != $insert['src'] || ! isset( $insert['alt'] ) ) {
							continue;
						}

//error_log( __LINE__ . " Insert_Fixit::_copy_alt_from_media_library ALT text test '{$alt_text}' ==  " . var_export( $insert['alt'], true ), 0 );
						if ( $alt_text == $insert['alt'] ) {
							continue;
						}

						// Queue replacement
						self::$image_inserts[ $post_id ]['replacements'][ $insert['alt_offset'] ] = array( 'length' => strlen( $insert['alt'] ), 'text' => $alt_text );
					} // foreach file
				} // foreach reference
			} // foreach insert
		} // foreach attachment

		// Apply replacements
		foreach ( self::$image_inserts as $post_id => $inserts ) {
			$replacements = $inserts['replacements'];
			if ( ! empty( $replacements ) ) {
				krsort( $replacements );
//error_log( __LINE__ . " Insert_Fixit::_copy_alt_from_media_library( {$post_id} ) replacements =  " . var_export( $replacements, true ), 0 );
				$post_content = $inserts['content'];
				foreach ( $replacements as $offset => $replacement ) {
					$post_content = substr_replace( $post_content, $replacement['text'], $offset, $replacement['length'] );
					$updates++;
				} // foreach replacement
//error_log( __LINE__ . " Insert_Fixit::_copy_alt_from_media_library( {$post_id} ) new post_content =  " . var_export( $post_content, true ), 0 );
				$new_content = array( 'ID' => $post_id, 'post_content' => $post_content );
				$result = wp_update_post( $new_content, true );
//error_log( __LINE__ . " Insert_Fixit::_copy_alt_from_media_library( {$post_id} ) update result =  " . var_export( $result, true ), 0 );
				if ( is_wp_error( $result ) ) {
					$errors++;
				}
				$updated_posts++;
			} // has replacements
		} // foreach post/page

		/*
		 * Invalidate the image_inserts cache, since post/page content has changed.
		 */
		if ( $updated_posts ) {		
			delete_transient( self::SLUG_PREFIX . 'image_inserts' );
		}

		return "<br>ALT from Item matched {$image_inserts} posts/pages to {$image_objects} attachments and made {$updates} update(s) in {$updated_posts} posts/pages. There were {$errors} error(s).\n";
	} // _copy_alt_from_media_library

	/**
	 * Copy ALT Text from Post/Page inserts to Media Library item 
 	 *
	 * @since 1.00
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _copy_alt_to_media_library() {
		/*
		 * Load the image_inserts array
		 */
		self::_build_image_inserts_cache( true );

		/*
		 * Load the image_objects array
		 */
		self::_build_image_objects_cache( true );

		$image_inserts = count( self::$image_inserts );
		$image_objects = count( self::$image_objects );
		$updated_attachments = 0;
		$errors = 0;

		foreach ( self::$image_objects as $attachment_id => $references ) {
			$alt_text = get_metadata( 'post', $attachment_id, '_wp_attachment_image_alt', true );
			if ( empty( $alt_text ) ) {
				$alt_text = '';
			}

			// Make sure the most recent changes are the last updates applied
			ksort( $references );

			// Find most recent replacement
			$replacement = NULL;
			foreach ( $references as $post_id => $files ) {
				$inserts = self::$image_inserts[ $post_id ];
				foreach ( $files as $file ) {
					foreach ( $inserts['inserts'] as $insert ) {
//error_log( __LINE__ . " Insert_Fixit::_copy_alt_to_media_library file test '{$file}' == " . var_export( $insert['src'], true ), 0 );
						if ( $file != $insert['src'] || ! isset( $insert['alt'] ) ) {
							continue;
						}

//error_log( __LINE__ . " Insert_Fixit::_copy_alt_to_media_library ALT text test '{$alt_text}' ==  " . var_export( $insert['alt'], true ), 0 );
						if ( $alt_text == $insert['alt'] ) {
							continue;
						}

						// Queue replacement
						$replacement = $insert['alt'];
					} // foreach file
				} // foreach reference
			} // foreach insert

			// Apply replacement
			if ( ! is_null( $replacement ) ) {
//error_log( __LINE__ . " Insert_Fixit::_copy_alt_to_media_library( {$attachment_id} ) replacement =  " . var_export( $replacement, true ), 0 );
				if ( update_metadata( 'post', $attachment_id, '_wp_attachment_image_alt', $replacement ) ) {
					$updated_attachments++;
				} else {
					$errors++;
				}
			}
		} // foreach attachment

		/*
		 * Invalidate the image_objects cache, since Media Library item content has changed.
		 */
		if ( $updated_attachments ) {		
			delete_transient( self::SLUG_PREFIX . 'image_objects' );
		}

		return "<br>ALT to Item matched {$image_inserts} posts/pages to {$image_objects} items and updated {$updated_attachments} items. There were {$errors} error(s).\n";
	} // _copy_alt_to_media_library

	/**
	 * Add an attribute to <img ... /> tags in a post/page
	 * @since 1.02
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _add_attribute() {
		return self::_evaluate_add_attribute( 'Add' );
	} // _add_attribute

	/**
	 * Replace (or add) an attribute to <img ... /> tags in a post/page
	 * @since 1.02
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _replace_attribute() {
		return self::_evaluate_add_attribute( 'Replace' );
	} // _replace_attribute

	/**
	 * Delete an attribute from <img ... /> tags in a post/page
	 * @since 1.02
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _delete_attribute() {
		return self::_evaluate_add_attribute( 'Delete' );
	} // _delete_attribute

	/**
	 * Attach items to the first Post/Page they are inserted in
 	 *
	 * @since 1.01
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _attach_inserted_in() {
		// Load the image_inserts array
		self::_build_image_inserts_cache( true );

		// Load the image_objects array
		self::_build_image_objects_cache( false, !self::$attach_all, self::$reverse_sort, true );

		// Initialize statistics
		$image_inserts = count( self::$image_inserts );
		$image_objects = count( self::$image_objects );
		$inserted_in = 0;
		$updated_attachments = 0;
		$errors = 0;

		foreach ( self::$image_objects as $attachment => $posts ) {
//error_log( __LINE__ . " _attach_inserted_in( {$attachment} ) posts = " . var_export( $posts, true ), 0 );
			$post_parent = $posts['parent'];
			unset( $posts['parent'] );
			$inserted_in += count( $posts );

			$keys = array_keys( $posts );
//error_log( __LINE__ . " _attach_inserted_in( {$attachment} ) keys = " . var_export( $keys, true ), 0 );
			$candidate = $keys[0];
			if ( $candidate != $post_parent ) {
				$args = array( 'ID' => $attachment, 'post_parent' => $keys[0] );
//error_log( __LINE__ . " _attach_inserted_in( {$attachment} ) args = " . var_export( $args, true ), 0 );
				if ( wp_update_post( $args ) ) {
					$updated_attachments++;
				} else {
					$errors++;
				}
			}
		}

		$unattached = self::$attach_all ? '' : 'unattached';
		return "<br>Attach Inserted In matched {$image_inserts} posts/pages with {$inserted_in} inserts to {$image_objects} {$unattached} items and updated {$updated_attachments} items. There were {$errors} error(s).\n";
	} // _attach_inserted_in

	/**
	 * Attach items to the first Post/Page for which they are the Featured Image
 	 *
	 * @since 1.01
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _attach_featured_in() {
		// Load the featured_objects array
		self::_build_featured_objects_cache( false, !self::$attach_all, self::$reverse_sort );

		// Initialize statistics
		$featured_objects = count( self::$featured_objects );
		$featured_in = 0;
		$updates = 0;
		$errors = 0;

		foreach ( self::$featured_objects as $attachment => $posts ) {
			$post_parent = $posts['parent'];
			unset( $posts['parent'] );
			$candidate = reset( $posts );
			$featured_in += count( $posts );

			if ( $candidate != $post_parent ) {
				$args = array( 'ID' => $attachment, 'post_parent' => reset( $posts ) );
				if ( wp_update_post( $args ) ) {
					$updates++;
				} else {
					$errors++;
				}
			}
		}

		$unattached = self::$attach_all ? '' : 'unattached';
		return "<br>Attach Featured In found {$featured_objects} {$unattached} items featured in {$featured_in} posts/pages and made {$updates} assignments. There were {$errors} error(s).\n";
	} // _attach_featured_in

	/**
	 * Attach items to the first Post/Page where they appear in a "class wp-image-" or "ids=" element
 	 *
	 * @since 1.08
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _attach_referenced_in() {
		// Load the self::$item_references array
		self::_build_item_references_cache( true );
		
		// Initialize statistics
		$reference_count = 0;
		$referenced_items = 0;
		$updated_attachments = 0;
		$skipped = 0;
		$errors = 0;

		foreach ( self::$item_references as $attachment_id => $references ) {
			$attachment = get_post( $attachment_id );
//error_log( __LINE__ . " _attach_referenced_in( {$attachment_id} ) attachment = " . var_export( $attachment, true ), 0 );

			// Ignore references to non-existant items
			if ( NULL === $attachment ) {
				continue;
			}
			
			if ( !self::$attach_all && $attachment->post_parent ) {
				$skipped++;
				continue;
			}

			$reference_count += count( $references );
			$referenced_items++;

			// Define "first"; oldest = ksort, newest = krsort
			if ( $reverse_sort ) {
				krsort( $references, SORT_NUMERIC );
			} else {
				ksort( $references, SORT_NUMERIC );
			}

			// extract the "first" element
			$candidate = reset( $references );
//error_log( __LINE__ . " _attach_referenced_in( {$attachment_id} ) candidate = " . var_export( $candidate, true ), 0 );
			if ( $candidate != $attachment->post_parent ) {
				$args = array( 'ID' => $attachment_id, 'post_parent' => $candidate );
//error_log( __LINE__ . " _attach_referenced_in( {$attachment_id} ) args = " . var_export( $args, true ), 0 );
				if ( wp_update_post( $args ) ) {
					$updated_attachments++;
				} else {
					$errors++;
				}
			}
		}

		$skipped = $skipped ? "skipped {$skipped} attached items, " : '';
		$unattached = self::$attach_all ? '' : 'unattached ';
		return "<br>Attach Referenced Items {$skipped}processed {$reference_count} posts/page references to {$referenced_items} {$unattached}items and updated {$updated_attachments} items. There were {$errors} error(s).\n";
	} // _attach_referenced_in

	/**
	 * Copy Post/Page values from inserts to Media Library item 
 	 *
	 * @since 1.03
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _copy_post_values_to_items() {
		global $post;

		// Load the image_inserts array
		self::_build_image_inserts_cache( true );

		// Load the image_objects array
		self::_build_image_objects_cache( true );

		$template = isset( $_REQUEST[ self::SLUG_PREFIX . 'page_library_template' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'page_library_template' ] : '([+page_terms:category,single+]: )([+page_title+] )[+index+]';

		$image_inserts = count( self::$image_inserts );
		$image_objects = count( self::$image_objects );
		$updated_attachments = 0;
		$errors = 0;

		// First, group all the inserts by the post/page ID they are in
		$post_inserts = array();
		foreach ( self::$image_objects as $attachment_id => $references ) {
			foreach ( $references as $post_id => $files ) {
				$post_inserts[ $post_id ][] = $attachment_id;
			} // each reference
		} // each attachment
//error_log( __LINE__ . " Insert_Fixit::_copy_post_values_to_items file test post_inserts = " . var_export( $post_inserts, true ), 0 );

		foreach ( $post_inserts as $post_id => $references ) {
			// Set the global $post object so page_terms: will work; get the post/page values
			$post = get_post( $post_id );
			self::$custom_data_sources['page_ID'] = (string) $post_id;
			self::$custom_data_sources['found_rows'] = (string) count( $references );

			foreach ( $references as $sequence => $attachment_id ) {
				self::$custom_data_sources['index'] = (string) 1 + $sequence;

				// Find the data source
				$data_source = array(
					'data_source' => 'template',
					'meta_name' => $template,
					'option' => 'text',
					'format' => 'raw',
				);
				$data_value = MLAOptions::mla_get_data_source( $attachment_id, 'single_attachment_mapping', $data_source, NULL );
//error_log( __LINE__ . " Insert_Fixit::_copy_post_values_to_items( {$attachment_id} ) data_value = " . var_export( $data_value, true ), 0 );

				$new_content = array( 'ID' => $attachment_id, 'post_title' => $data_value );
				$result = wp_update_post( $new_content, true );
//error_log( __LINE__ . " Insert_Fixit::_copy_post_values_to_items( {$attachment_id} ) update result =  " . var_export( $result, true ), 0 );

				if ( $result ) {
					$updated_attachments++;
				} else {
					$errors++;
				}
			} // foreach reference
		} // foreach attachment

		/*
		 * Invalidate the image_objects cache, since Media Library item content has changed.
		 */
		if ( $updated_attachments ) {		
			delete_transient( self::SLUG_PREFIX . 'image_objects' );
		}

		return "<br>Post to Item Title matched {$image_inserts} posts/pages to {$image_objects} items and updated {$updated_attachments} items. There were {$errors} error(s).\n";
	} // _copy_post_values_to_items

	/**
	 * Copy Parent values to attached Media Library items
 	 *
	 * @since 1.04
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _copy_parent_values_to_items() {
		global $post;

		self::_build_attached_items_cache();

		$template = isset( $_REQUEST[ self::SLUG_PREFIX . 'parent_library_template' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'parent_library_template' ] : '([+parent_terms:category,single+]: )([+parent_title+] )[+index+]';

		$attached_parents = count( self::$attached_items );
		$attached_items = 0;
		$updated_attachments = 0;
		$errors = 0;

		foreach ( self::$attached_items as $post_id => $attachments ) {
			// Set the global $post object so page_terms: will work; get the post/page values
			$post = get_post( $post_id );
			self::$custom_data_sources['page_ID'] = (string) $post_id;
			self::$custom_data_sources['found_rows'] = (string) count( $attachments );

			foreach ( $attachments as $sequence => $attachment_id ) {
				$attached_items++;
				self::$custom_data_sources['index'] = (string) 1 + $sequence;

				// Find the data source
				$data_source = array(
					'data_source' => 'template',
					'meta_name' => $template,
					'option' => 'text',
					'format' => 'raw',
				);
				$data_value = MLAOptions::mla_get_data_source( $attachment_id, 'single_attachment_mapping', $data_source, NULL );
//error_log( __LINE__ . " Insert_Fixit::_copy_post_values_to_items( {$attachment_id} ) data_value = " . var_export( $data_value, true ), 0 );

				$new_content = array( 'ID' => $attachment_id, 'post_title' => $data_value );
				$result = wp_update_post( $new_content, true );
//error_log( __LINE__ . " Insert_Fixit::_copy_post_values_to_items( {$attachment_id} ) update result =  " . var_export( $result, true ), 0 );

				if ( $result ) {
					$updated_attachments++;
				} else {
					$errors++;
				}
			} // foreach reference
		} // foreach attachment

		return "<br>Parent to Item Title matched {$attached_parents} posts/pages to {$attached_items} items and updated {$updated_attachments} items. There were {$errors} error(s).\n";
	} // _copy_parent_values_to_items

	/**
	 * Copy assigned terms from parent post/page to the attached items
 	 *
	 * @since 1.06
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _copy_parent_terms_to_items() {
		self::_build_attached_items_cache();

		$item_taxonomy = isset( $_REQUEST[ self::SLUG_PREFIX . 'to_item_taxonomy' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'to_item_taxonomy' ] : 'attachment_category';
		$parent_taxonomy = isset( $_REQUEST[ self::SLUG_PREFIX . 'from_parent_taxonomy' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'from_parent_taxonomy' ] : 'category';
		$append = 'add' === ( isset( $_REQUEST[ self::SLUG_PREFIX . 'item_add_replace' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'item_add_replace' ] : 'add' );

		$attached_parents = count( self::$attached_items );
		$attached_items = 0;
		$updated_parents = 0;
		$skipped = 0;
		$errors = 0;

		$taxonomies = get_object_taxonomies( 'attachment', 'objects' );
//error_log( __LINE__ . " Insert_Fixit::_copy_parent_terms_to_items attachment taxonomies = " . var_export( $taxonomies, true ), 0 );

		if ( ! isset( $taxonomies[ $item_taxonomy ] ) ) {
			return "ERROR - Item Taxonomy \"$item_taxonomy\" not valid for post_type \"attachment\".";
		}

		$item_is_hierarchical = $taxonomies[ $item_taxonomy ]->hierarchical;

		$skipped_messages = array();
		$parent_taxonomies = array();
		foreach ( self::$attached_items as $post_id => $attachments ) {
			// get the post/page object
			$post = get_post( $post_id );
//error_log( __LINE__ . " Insert_Fixit::_copy_parent_terms_to_items attachment post = " . var_export( $post, true ), 0 );

			// Exclude post_type "attachment", which is used in MLA to give thumbnails to non-image items
			if ( 'attachment' === $post->post_type ) {
				$attached_parents--;
				continue;
			}

			if ( isset( $parent_taxonomies[ $post->post_type ] ) ) {
				$taxonomies = $parent_taxonomies[ $post->post_type ];
			} else {
				$taxonomies = $parent_taxonomies[ $post->post_type ] = get_object_taxonomies( $post, 'objects' );
			}

			if ( ! isset( $taxonomies[ $parent_taxonomy ] ) ) {
				$skipped_messages[ $parent_taxonomy . $post->post_type ] = array( 'taxonomy' => $parent_taxonomy, 'post_type' => $post->post_type );
				$skipped++;
				continue;
			}
//error_log( __LINE__ . " Insert_Fixit::_copy_parent_terms_to_items taxonomies = " . var_export( $taxonomies, true ), 0 );
//error_log( __LINE__ . " Insert_Fixit::_copy_parent_terms_to_items parent_taxonomies = " . var_export( $parent_taxonomies, true ), 0 );

			$parent_terms = wp_get_object_terms( $post_id, $parent_taxonomy );
			$term_map = array();
			$item_terms = array();

			// If both taxonomies are hiearchical we must add parent term(s) before adding item terms
			if ( $item_is_hierarchical && $taxonomies[ $parent_taxonomy ]->hierarchical ) {
				$ancestors = array();
				foreach ( $parent_terms as $term ) {
					$level = 0;
					while ( $term->parent ) {
						$term = get_term( $term->parent, $parent_taxonomy );
						$ancestors[ $level++ ][ $term->term_id ] = $term;
					}
				}

				krsort( $ancestors, SORT_NUMERIC );
//error_log( __LINE__ . " Insert_Fixit::_copy_parent_terms_to_items ancestors = " . var_export( $ancestors, true ), 0 );

				foreach ( $ancestors as $level => $terms ) {
					foreach ( $terms as $term_id => $term ) {
						$ancestor = get_term_by( 'name', $term->name, $item_taxonomy );
						if ( false !== $ancestor ) {
							$term_map[ $term->term_id ] = $ancestor->term_id;
//error_log( __LINE__ . " Insert_Fixit::_copy_parent_terms_to_items( $level, $term->term_id ) found ancestor = " . var_export( $ancestor, true ), 0 );
						} else {
//error_log( __LINE__ . " Insert_Fixit::_copy_parent_terms_to_items( $level, $term->term_id ) inserting = " . var_export( $term, true ), 0 );
							if ( $term->parent && !empty( $term_map[ $term->parent ] ) ) {
								$ancestor = wp_insert_term( $term->name, $item_taxonomy, array( 'parent' => $term_map[ $term->parent ] ) );
//error_log( __LINE__ . " Insert_Fixit::_copy_parent_terms_to_items child ancestor = " . var_export( $ancestor, true ), 0 );
							} else {
								$ancestor = wp_insert_term( $term->name, $item_taxonomy );
//error_log( __LINE__ . " Insert_Fixit::_copy_parent_terms_to_items root ancestor = " . var_export( $ancestor, true ), 0 );
							}
							if ( ( ! is_wp_error( $ancestor ) ) && isset( $ancestor['term_id'] ) ) {
								$term_map[ $term->term_id ] = (integer) $ancestor['term_id'];
							}
						}
//error_log( __LINE__ . " Insert_Fixit::_copy_parent_terms_to_items updated term_map = " . var_export( $term_map, true ), 0 );
					} // foreach term
				} // foreach level
			}

			foreach ( $parent_terms as $term ) {
				if ( $item_is_hierarchical ) {
					$item_term = term_exists( $term->name, $item_taxonomy );

					if ( $item_term !== 0 && $item_term !== NULL ) {
						$item_terms[ $item_term['term_id'] ] = (integer) $item_term['term_id'];
					} else {
						if ( $term->parent && !empty( $term_map[ $term->parent ] ) ) {
							$item_term = wp_insert_term( $term->name, $item_taxonomy, array( 'parent' => $term_map[ $term->parent ] ) );
//error_log( __LINE__ . " Insert_Fixit::_copy_parent_terms_to_items child item_term = " . var_export( $item_term, true ), 0 );
						} else {
							$item_term = wp_insert_term( $term->name, $item_taxonomy );
//error_log( __LINE__ . " Insert_Fixit::_copy_parent_terms_to_items root item_term = " . var_export( $item_term, true ), 0 );
						}

						if ( ( ! is_wp_error( $item_term ) ) && isset( $item_term['term_id'] ) ) {
							$item_terms[ $item_term['term_id'] ] = (integer) $item_term['term_id'];
						}
					}
				} else {
					$item_terms[ $term->term_taxonomy_id ] = $term->name;
				}
			}
//error_log( __LINE__ . " Insert_Fixit::_copy_parent_terms_to_items( {$post_id} ) item_terms = " . var_export( $item_terms, true ), 0 );

			foreach ( $attachments as $sequence => $attachment_id ) {
				$attached_items++;

				$result = wp_set_object_terms( $attachment_id, $item_terms, $item_taxonomy, $append );
				if ( is_array( $result) ) {
					$updated_parents++;
				} else {
					$errors++;
				}
//error_log( __LINE__ . " Insert_Fixit::_copy_parent_terms_to_items( {$post_id}, {$attachment_id}, {$append} ) result = " . var_export( $result, true ), 0 );
			} // foreach attachment
		} // foreach post

		$messages = '';
		if ( !empty( $skipped_messages ) ) {
			foreach ( $skipped_messages as $skipped_message ) {
				$messages .= sprintf( '<br>Skipped taxonomy "%1$s" for post type "%2$s".', $skipped_message['taxonomy'], $skipped_message['post_type'] );
			}
		}

		// Flush the Media/Edit Taxonomy Attachments column cache; see MLAObjects in class-mla-objects.php
		delete_transient( MLA_OPTION_PREFIX . 't_term_counts_' . $item_taxonomy, $terms );

		return $messages . "<br>Item Terms to Parent matched {$attached_parents} posts/pages to {$attached_items} items and updated {$updated_parents} parent posts/pages. There were {$skipped} skipped parents and {$errors} error(s).\n";
	} // _copy_parent_terms_to_items

	/**
	 * Copy assigned terms from attached items to the parent post/page
 	 *
	 * @since 1.04
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _copy_item_terms_to_parent() {
		self::_build_attached_items_cache();

		$item_taxonomy = isset( $_REQUEST[ self::SLUG_PREFIX . 'item_taxonomy' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'item_taxonomy' ] : 'attachment_tag';
		$parent_taxonomy = isset( $_REQUEST[ self::SLUG_PREFIX . 'parent_taxonomy' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'parent_taxonomy' ] : 'post_tag';
		$append = 'add' === ( isset( $_REQUEST[ self::SLUG_PREFIX . 'add_replace' ] ) ? $_REQUEST[ self::SLUG_PREFIX . 'add_replace' ] : 'add' );

		$attached_parents = count( self::$attached_items );
		$attached_items = 0;
		$updated_parents = 0;
		$skipped = 0;
		$errors = 0;

		$taxonomies = get_object_taxonomies( 'attachment', 'objects' );
//error_log( __LINE__ . " Insert_Fixit::_copy_item_terms_to_parent attachment taxonomies = " . var_export( $taxonomies, true ), 0 );

		if ( ! isset( $taxonomies[ $item_taxonomy ] ) ) {
			return 'ERROR - Item Taxonomy not valid for post_type "attachment".';
		}

		foreach ( self::$attached_items as $post_id => $attachments ) {
			// get the post/page object
			$post = get_post( $post_id );
			$taxonomies = get_object_taxonomies( $post, 'objects' );

			if ( ! isset( $taxonomies[ $parent_taxonomy ] ) ) {
				$skipped++;
				continue;
			}

			$parent_is_hierarchical = $taxonomies[ $parent_taxonomy ]->hierarchical;

			$item_terms = array();
			foreach ( $attachments as $sequence => $attachment_id ) {
				$attached_items++;

				$terms = wp_get_object_terms( $attachment_id, $item_taxonomy );
				foreach( $terms as $term ) {
					$item_terms[ $term->term_taxonomy_id ] = $term;
				}
			} // foreach attachment
//error_log( __LINE__ . " Insert_Fixit::_copy_item_terms_to_parent( {$post_id} ) item_terms = " . var_export( $item_terms, true ), 0 );

			$parent_terms = array();
			foreach ( $item_terms as $term_taxonomy_id => $term ) {
				if ( $parent_is_hierarchical ) {
					$parent_term = term_exists( $term->name, $parent_taxonomy );

					if ( $parent_term !== 0 && $parent_term !== NULL ) {
						$parent_terms[ $parent_term['term_id'] ] = (integer) $parent_term['term_id'];
					} else {
						$parent_term = wp_insert_term( $term->name, $parent_taxonomy );
						if ( ( ! is_wp_error( $parent_term ) ) && isset( $parent_term['term_id'] ) ) {
							$parent_terms[ $parent_term['term_id'] ] = (integer) $parent_term['term_id'];
						}
					}
				} else {
					$parent_terms[ $term->term_taxonomy_id ] = $term->name;
				}
			}
//error_log( __LINE__ . " Insert_Fixit::_copy_item_terms_to_parent( {$post_id} ) parent_terms = " . var_export( $parent_terms, true ), 0 );

			$result = wp_set_object_terms( $post_id, $parent_terms, $parent_taxonomy, $append );
			if ( is_array( $result) ) {
				$updated_parents++;
			} else {
				$errors++;
			}
		} // foreach post

		return "<br>Item Terms to Parent matched {$attached_parents} posts/pages to {$attached_items} items and updated {$updated_parents} parent posts/pages. There were {$skipped} skipped parents and {$errors} error(s).\n";
	} // _copy_item_terms_to_parent

	/**
	 * Rebuild the Image Inserts and Image Objects arrays and cache them
 	 *
	 * @since 1.04
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _refresh_caches() {
		$results  = '<br>' . self::_build_image_inserts_cache();
		$results  .= '<br>' . self::_build_item_references_cache();
		return $results . '<br>' . self::_build_image_objects_cache() . "\n";
	} // _refresh_caches
} //Insert_Fixit

/*
 * Install the submenu at an early opportunity
 */
add_action('init', 'Insert_Fixit::initialize');
?>