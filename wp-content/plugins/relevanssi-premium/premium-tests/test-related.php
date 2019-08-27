<?php
/**
 * Class RelatedTest
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 */

/**
 * Test Relevanssi Related Posts.
 */
class RelatedTest extends WP_UnitTestCase {
	/**
	 * Number of posts generated.
	 *
	 * @var int self::$post_count
	 */
	public static $post_count;

	/**
	 * Array of related posts.
	 *
	 * @var array $related
	 */
	public static $related;

	/**
	 * Sets up the index.
	 */
	public static function wpSetUpBeforeClass() {
		relevanssi_install();

		global $wpdb, $relevanssi_variables;
		$relevanssi_table = $relevanssi_variables['relevanssi_table'];
		// phpcs:disable WordPress.WP.PreparedSQL

		// Truncate the index.
		relevanssi_truncate_index();

		$number_of_posts = 6;

		// Set up some Relevanssi settings so we know what to expect.
		$options           = relevanssi_related_default_settings();
		$options['append'] = 'post';
		$options['number'] = $number_of_posts;

		update_option( 'relevanssi_related_settings', $options );

		self::$post_count = 20;
		$post_ids         = self::factory()->post->create_many( self::$post_count );

		self::$related = array();
		$counter       = 0;
		foreach ( $post_ids as $id ) {
			if ( $counter <= $number_of_posts ) {
				$title           = 'She sells sea shells ' . $counter;
				$args            = array(
					'ID'         => $id,
					'post_title' => $title,
				);
				self::$related[] = $id;
				wp_update_post( $args );
			}
			$counter++;
		}

		// Rebuild the index.
		relevanssi_build_index( false, false, 200, false );
	}

	/**
	 * Test how related posts are generated.
	 */
	public function test_generation() {
		global $wpdb, $relevanssi_variables;
		$relevanssi_table = $relevanssi_variables['relevanssi_table'];
		// phpcs:disable WordPress.WP.PreparedSQL

		$options = get_option( 'relevanssi_related_settings' );

		$args  = array(
			'post_type'      => 'post',
			'fields'         => 'ids',
			'posts_per_page' => -1,
		);
		$posts = get_posts( $args ); // Get all posts for the wanted post types.
		$count = count( $posts );

		foreach ( $posts as $post_id ) {
			relevanssi_related_posts( $post_id );
		}

		$self_match = false;
		foreach ( self::$related as $original_post_id ) {
			$related_posts = get_post_meta( $original_post_id, '_relevanssi_related_posts', true );
			$related_posts = explode( ',', $related_posts );
			foreach ( $related_posts as $related_post_id ) {
				if ( $original_post_id === (int) $related_post_id ) {
					$self_match = true;
				}
			}
		}

		// $self_match should remain false.
		$this->assertFalse( $self_match, "A post is it's own related post." );

		// Try excluding a post and see if the generated list includes that post.
		$exclude_test_post = $posts[0];
		$related_posts     = explode( ',', get_post_meta( $exclude_test_post, '_relevanssi_related_posts', true ) );
		$a_related_post    = $related_posts[0];
		relevanssi_exclude_a_related_post( $exclude_test_post, $a_related_post );
		relevanssi_related_posts( $exclude_test_post );

		$related_posts = explode( ',', get_post_meta( $exclude_test_post, '_relevanssi_related_posts', true ) );
		$related_posts = array_flip( $related_posts );

		$this->assertFalse( isset( $related_posts[ $a_related_post ] ), 'The excluded post appears in the related posts.' );

		// Try forcing a post to the list and see what happens.
		$a_non_related_post = null;
		$include_test_post  = $posts[1];
		$related_posts      = explode( ',', get_post_meta( $include_test_post, '_relevanssi_related_posts', true ) );
		foreach ( $posts as $post ) {
			if ( ! in_array( (string) $post, $related_posts, true ) ) {
				$a_non_related_post = $post;
				break;
			}
		}
		add_post_meta( $include_test_post, '_relevanssi_related_include_ids', $a_non_related_post );
		$related_posts_content = relevanssi_related_posts( $exclude_test_post );

		$related_posts = explode( ',', get_post_meta( $exclude_test_post, '_relevanssi_related_posts', true ) );
		$related_posts = array_flip( $related_posts );

		$this->assertTrue( isset( $related_posts[ $a_non_related_post ] ), 'The included post did not appear in the related posts.' );

		// Let's look at the content. Right amount of posts?
		$count_related_posts = substr_count( $related_posts_content, '<div class="relevanssi_related_post">' );
		$this->assertEquals( $options['number'], $count_related_posts, 'Incorrect number of related posts displayed.' );

		// Is it coming from cache?
		$cache_found = strpos( $related_posts_content, 'Fetched from cache' );
		$this->assertNotEquals( false, $cache_found, "Caching doesn't work." );

		// Flush the cache. Is it uncached?
		delete_post_meta( $exclude_test_post, '_relevanssi_related_posts' );
		$related_posts_content = relevanssi_related_posts( $exclude_test_post );

		$cache_found = strpos( $related_posts_content, 'Fetched from cache' );
		$this->assertEquals( false, $cache_found, "Caching doesn't work." );
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
