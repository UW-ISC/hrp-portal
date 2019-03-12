<?php
/**
 * Class MuSearchTest
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 * @group   multisite
 */

/**
 * Test Relevanssi Multisite searching
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
				$wpdb->query( "DROP TABLE $table" ); // WPCS: unprepared SQL ok.
			}
		}
		$blog_ids     = self::factory()->blog->create_many( 2 );
		$network_wide = true;

		relevanssi_install( $network_wide );

		$blogs = get_sites();
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog->blog_id );

			update_option( 'relevanssi_index_fields', 'all' );

			$post_ids = self::factory()->post->create_many( 10 );

			$post_id = array_pop( $post_ids );
			update_post_meta( $post_id, 'custom_field', 'customfieldvalue' );

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
		);
		$query = new WP_Query();
		$query->parse_query( $args );
		relevanssi_do_query( $query );

		$blog_ids_present = array();
		foreach ( $query->posts as $post ) {
			$blog_ids_present[ $post->blog_id ] = true;
		}
		$this->assertEquals( 3, count( $blog_ids_present ), 'Results should span all three blogs with orderby.' );
	}

	/**
	 * Test fuzzy searching all blogs.
	 */
	public function test_fuzzy_searching_all_blogs() {
		update_option( 'relevanssi_searchblogs_all', 'on' );
		update_option( 'relevanssi_fuzzy', 'always' );

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

		$this->assertEquals( 3, $query->found_posts, 'Search should find three posts.' );

		$args  = array(
			's'              => 'customfieldvalue',
			'searchblogs'    => '1,2,3',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);
		$query = new WP_Query();
		$query->parse_query( $args );
		relevanssi_do_query( $query );

		$this->assertEquals( 3, $query->found_posts, 'Search should find three posts.' );
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
				$wpdb->query( "DROP TABLE $table" ); // WPCS: unprepared SQL ok.
			}
		}
		relevanssi_uninstall();
	}
}
