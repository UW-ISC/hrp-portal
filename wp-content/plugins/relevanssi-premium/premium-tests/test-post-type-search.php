<?php
/**
 * Class PostTypeSearchingTest
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 */

/**
 * Test post type archive searching.
 *
 * @group post_type_search
 */
class PostTypeSearchingTest extends WP_UnitTestCase {
	/**
	 * Sets up the index.
	 */
	public static function wpSetUpBeforeClass() {
		relevanssi_install();

		// Truncate the index.
		relevanssi_truncate_index();

		// Set up some Relevanssi settings so we know what to expect.
		update_option( 'relevanssi_index_post_type_archives', 'on' );
		update_option( 'relevanssi_show_matches', 'on' );
		update_option( 'relevanssi_hilite_title', 'off' );
		update_option( 'relevanssi_excerpts', false );

		$labels = array(
			'name'               => 'Books',
			'singular_name'      => 'Book',
			'menu_name'          => 'Books',
			'name_admin_bar'     => 'Book',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Book',
			'new_item'           => 'New Book',
			'edit_item'          => 'Edit Book',
			'view_item'          => 'View Book',
			'all_items'          => 'All Books',
			'search_items'       => 'Search Books',
			'parent_item_colon'  => 'Parent Books:',
			'not_found'          => 'No books found.',
			'not_found_in_trash' => 'No books found in Trash.',
		);

		$args = array(
			'labels'             => $labels,
			'description'        => 'clever ruse',
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'book' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
		);

		register_post_type( 'book', $args );

		// Rebuild the index.
		relevanssi_build_index( false, false, 200, false );
	}

	/**
	 * Test searching process.
	 *
	 * Creates some posts, tries to find them.
	 */
	public function test_searching() {
		$args = array(
			's'           => 'ruse',
			'numberposts' => -1,
			'post_status' => 'publish',
		);

		$query = new WP_Query();
		$query->parse_query( $args );
		relevanssi_do_query( $query );

		// These should both match the number of posts in the index.
		$this->assertEquals( 1, $query->found_posts );
		$this->assertEquals( 'book', $query->posts[0]->post_type_id );
	}

	/**
	 * Test searching with a post_type parameter set to 'post_type'.
	 */
	public function test_post_type() {
		$args = array(
			's'           => 'ruse',
			'numberposts' => -1,
			'post_status' => 'publish',
			'post_type'   => 'post_type',
		);

		$query = new WP_Query();
		$query->parse_query( $args );
		relevanssi_do_query( $query );

		// These should both match the number of posts in the index.
		$this->assertEquals( 1, $query->found_posts );
		$this->assertEquals( 'book', $query->posts[0]->post_type_id );
	}

	/**
	 * Uninstalls Relevanssi.
	 */
	public static function wpTearDownAfterClass() {
		require_once dirname( dirname( __FILE__ ) ) . '/lib/uninstall.php';
		require_once dirname( dirname( __FILE__ ) ) . '/premium/uninstall.php';

		if ( function_exists( 'relevanssi_uninstall' ) ) {
			relevanssi_uninstall();
		}
		if ( function_exists( 'relevanssi_uninstall_free' ) ) {
			relevanssi_uninstall_free();
		}
	}

}
