<?php
/**
 * Class MuSearchTest
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 */

/**
 * Test Relevanssi Multisite searching
 *
 * @group multisite
 */
class MuSearchTest extends WP_UnitTestCase {
	/**
	 * Sets up the tests.
	 */
	public static function wpSetUpBeforeClass() {
		global $wpdb;

		$tables_2 = $wpdb->get_col( "SHOW TABLES LIKE 'wptests_2_%'" );
		$tables_3 = $wpdb->get_col( "SHOW TABLES LIKE 'wptests_3_%'" );
		$tables   = array_merge( $tables_2, $tables_3 );
		if ( $tables ) {
			foreach ( $tables as $table ) {
				$wpdb->query( "DROP TABLE $table" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}
		}

		self::factory()->blog->create_many( 2 );
		$network_wide = true;
		relevanssi_install( $network_wide );

		wp_insert_site( array() );

		$blogs = get_sites();
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog->blog_id );

			update_option( 'relevanssi_index_fields', 'all' );
			update_option( 'relevanssi_excerpts', false );
			update_option( 'relevanssi_hilite_title', 'off' );

			$post_ids = self::factory()->post->create_many( 10 );
			$page_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'page' ) );

			array_map(
				function( $page_id ) {
					$args = array(
						'ID'           => $page_id,
						'post_content' => 'Page content has the word pageword.',
					);
					wp_update_post( $args );
				},
				$page_ids
			);

			$post_id = array_pop( $post_ids );
			update_post_meta( $post_id, 'custom_field', 'customfieldvalue' );
			$page_id = array_pop( $page_ids );
			update_post_meta( $page_id, 'custom_field', 'customfieldvalue' );

			relevanssi_build_index( false, false, 200, false );

			restore_current_blog();
		}
	}

	/**
	 * Test searching some blogs.
	 */
	public function test_searching_some_blogs() {
		$args  = array(
			's'              => 'content',
			'searchblogs'    => '1,2',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);
		$query = new WP_Query();
		$query->parse_query( $args );
		relevanssi_do_query( $query );

		$blog_ids_present = array();
		foreach ( $query->posts as $post ) {
			$blog_ids_present[ $post->blog_id ] = true;
		}
		$this->assertEquals( 2, count( $blog_ids_present ), 'Results should span two blogs.' );
	}

	/**
	 * Test searching all blogs.
	 */
	public function test_searching_all_blogs() {
		update_option( 'relevanssi_searchblogs_all', 'on' );

		$args  = array(
			's'              => 'content',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);
		$query = new WP_Query();
		$query->parse_query( $args );
		relevanssi_do_query( $query );

		$blog_ids_present = array();
		foreach ( $query->posts as $post ) {
			$blog_ids_present[ $post->blog_id ] = true;
		}
		$this->assertEquals( 3, count( $blog_ids_present ), 'Results should span all three blogs.' );

		$args  = array(
			's'              => 'content',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'post_title',
			'order'          => 'asc',
		);
		$query = new WP_Query();
		$query->parse_query( $args );
		relevanssi_do_query( $query );

		$blog_ids_present = array();
		foreach ( $query->posts as $post ) {
			$blog_ids_present[ $post->blog_id ] = true;
		}
		$this->assertEquals( 3, count( $blog_ids_present ), 'Results should span all three blogs with orderby.' );

		update_option( 'relevanssi_searchblogs_all', 'off' );
		update_option( 'relevanssi_searchblogs', 'all' );

		$args  = array(
			's'              => 'content',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);
		$query = new WP_Query();
		$query->parse_query( $args );
		relevanssi_do_query( $query );

		$blog_ids_present = array();
		foreach ( $query->posts as $post ) {
			$blog_ids_present[ $post->blog_id ] = true;
		}
		$this->assertEquals( 3, count( $blog_ids_present ), 'Results should span all three blogs.' );
	}

	/**
	 * Test fuzzy searching all blogs.
	 */
	public function test_fuzzy_searching_all_blogs() {
		update_option( 'relevanssi_fuzzy', 'always' );
		update_option( 'relevanssi_searchblogs_all', 'on' );
		update_option( 'relevanssi_show_matches', 'on' );

		$args  = array(
			's'              => 'conte',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);
		$query = new WP_Query();
		$query->parse_query( $args );
		relevanssi_do_query( $query );

		$blog_ids_present = array();
		foreach ( $query->posts as $post ) {
			$blog_ids_present[ $post->blog_id ] = true;
		}
		$this->assertEquals( 3, count( $blog_ids_present ), 'Results should span all three blogs.' );
	}

	/**
	 * Test post type restriction.
	 */
	public function test_search_post_type() {
		$args  = array(
			's'              => 'pageword',
			'searchblogs'    => '1,2,3',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'post_type'      => 'page',
		);
		$query = new WP_Query();
		$query->parse_query( $args );
		relevanssi_do_query( $query );

		$this->assertEquals( 30, $query->found_posts, 'Search should find ten pages per blog.' );
		array_map(
			function( $post ) {
				$this->assertEquals( 'page', $post->post_type, 'Search should only find pages.' );
			},
			$query->posts
		);

		$args['post_types'] = 'page';
		unset( $args['post_type'] );
		$query->parse_query( $args );
		relevanssi_do_query( $query );

		$this->assertEquals( 30, $query->found_posts, 'Search should find ten pages per blog.' );
		array_map(
			function( $post ) {
				$this->assertEquals( 'page', $post->post_type, 'Search should only find pages.' );
			},
			$query->posts
		);
	}

	/**
	 * Test custom field search.
	 */
	public function test_search_custom_field() {
		$args  = array(
			's'              => 'content',
			'searchblogs'    => '1,2,3',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_key'       => 'custom_field',
			'meta_value'     => 'customfieldvalue',
		);
		$query = new WP_Query();
		$query->parse_query( $args );
		relevanssi_do_query( $query );

		$this->assertEquals( 6, $query->found_posts, 'Search should find three posts.' );

		$args  = array(
			's'              => 'customfieldvalue',
			'searchblogs'    => '1,2,3',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);
		$query = new WP_Query();
		$query->parse_query( $args );
		relevanssi_do_query( $query );

		$this->assertEquals( 6, $query->found_posts, 'Search should find three posts.' );
	}

	/**
	 * Clean up.
	 */
	public static function wpTearDownAfterClass() {
		global $wpdb;
		$tables_2 = $wpdb->get_col( "SHOW TABLES LIKE 'wptests_2_%'" );
		$tables_3 = $wpdb->get_col( "SHOW TABLES LIKE 'wptests_3_%'" );
		$tables   = array_merge( $tables_2, $tables_3 );
		if ( $tables ) {
			foreach ( $tables as $table ) {
				$wpdb->query( "DROP TABLE $table" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}
		}
		require_once dirname( dirname( __FILE__ ) ) . '/lib/uninstall.php';
		require_once dirname( dirname( __FILE__ ) ) . '/premium/uninstall.php';
		relevanssi_uninstall();
	}
}
