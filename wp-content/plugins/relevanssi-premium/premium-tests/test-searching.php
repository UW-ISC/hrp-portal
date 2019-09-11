<?php
/**
 * Class PremiumSearchingTest
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 */

/**
 * Test Relevanssi Premium searching.
 *
 * @group premium_searching
 */
class PremiumSearchingTest extends WP_UnitTestCase {
	/**
	 * Number of posts generated.
	 *
	 * @var int self::$post_count
	 */
	public static $post_count;

	/**
	 * Number of users generated.
	 *
	 * @var int $this->user_count
	 */
	public static $user_count;

	/**
	 * Number of posts with visible custom fields.
	 *
	 * @var int $visible
	 */
	public static $visible;

	/**
	 * Number of posts that should get an AND match.
	 *
	 * @var int $and_matches
	 */
	public static $and_matches;

	/**
	 * Number of posts that have taxonomy terms.
	 *
	 * @var int $taxonomy_matches
	 */
	public static $taxonomy_matches;

	/**
	 * The main author ID for the posts.
	 *
	 * @var int $post_author_id
	 */
	public static $post_author_id;

	/**
	 * The secondary author ID for the posts.
	 *
	 * @var int $other_author_id
	 */
	public static $other_author_id;

	/**
	 * Sets up the index.
	 */
	public static function wpSetUpBeforeClass() {
		relevanssi_install();

		// Truncate the index.
		relevanssi_truncate_index();

		// Set up some Relevanssi settings so we know what to expect.
		update_option( 'relevanssi_index_fields', 'visible' );
		update_option( 'relevanssi_index_users', 'on' );
		update_option( 'relevanssi_index_subscribers', 'on' );
		update_option( 'relevanssi_index_author', 'on' );
		update_option( 'relevanssi_implicit_operator', 'AND' );
		update_option( 'relevanssi_fuzzy', 'sometimes' );
		update_option( 'relevanssi_log_queries', 'on' );
		update_option( 'relevanssi_index_taxonomies_list', array( 'post_tag', 'category' ) );
		update_option( 'relevanssi_show_matches', 'on' );
		update_option( 'relevanssi_hilite_title', 'off' );
		update_option( 'relevanssi_excerpts', false );
		update_option(
			'relevanssi_post_type_weights',
			array(
				'post'                      => 1,
				'post_tagged_with_category' => 1,
				'post_tagged_with_post_tag' => 1,
				'taxonomy_term_category'    => 1,
				'taxonomy_term_post_tag'    => 1,
			)
		);
		update_option( 'relevanssi_index_taxonomies', 'on' );
		update_option( 'relevanssi_index_terms', array( 'category', 'post_tag' ) );
		update_option(
			'relevanssi_recency_bonus',
			array(
				'bonus' => 2,
				'days'  => 10,
			)
		);

		$cat_ids    = array();
		$cat_ids[0] = wp_create_category( 'cat_foo_cat' );
		$cat_ids[1] = wp_create_category( 'cat_bar_cat' );
		$cat_ids[2] = wp_create_category( 'cat_baz_cat' );

		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $wpdb->term_taxonomy SET description=%s WHERE term_id=%d AND taxonomy=%s",
				'categorydescription',
				$cat_ids[0],
				'category'
			)
		);

		self::$post_count = 10;
		$post_ids         = self::factory()->post->create_many( self::$post_count );

		self::$user_count = 10;
		$user_ids         = self::factory()->user->create_many( self::$user_count );

		self::$post_author_id  = $user_ids[0];
		self::$other_author_id = $user_ids[1];

		$counter                = 0;
		self::$visible          = 0;
		self::$and_matches      = 0;
		self::$taxonomy_matches = 0;
		foreach ( $post_ids as $id ) {
			if ( $counter < 1 ) {
				// Make one post contain a test phrase.
				$post_content = "words Mikko's test phrase content";
				$args         = array(
					'ID'           => $id,
					'post_content' => $post_content,
				);
				wp_update_post( $args );
			}
			// Make three posts have the phrase 'displayname user' in post content.
			if ( $counter < 3 ) {
				$post     = get_post( $id );
				$content  = $post->post_content;
				$content .= ' displayname user';
				$args     = array(
					'ID'           => $id,
					'post_content' => $content,
				);
				wp_update_post( $args );
			}
			// Make five posts have the word 'buzzword' in a visible custom field and
			// rest of the posts have it in an invisible custom field. Five posts will
			// also get tags and categories 'foo', 'bar', and 'baz'.
			if ( $counter < 5 ) {
				update_post_meta( $id, '_invisible', 'buzzword' );
				update_post_meta( $id, 'keywords', 'cat dog' );
				wp_set_post_terms( $id, array( 'foo', 'bar', 'baz' ), 'post_tag', true );
				wp_set_post_terms( $id, $cat_ids, 'category', true );
				self::$and_matches++;
				self::$taxonomy_matches++;
			} else {
				update_post_meta( $id, 'visible', 'buzzword' );
				self::$visible++;
				update_post_meta( $id, 'keywords', 'cat' );
			}

			$title = substr( md5( wp_rand() ), 0, 7 );

			$post_date = date( 'Y-m-d', time() - ( $counter * MONTH_IN_SECONDS ) );

			$author_id = self::$post_author_id;
			if ( $counter < 1 ) {
				$author_id = self::$other_author_id;
			}

			// Set the post author and title.
			$args = array(
				'ID'          => $id,
				'post_author' => $author_id,
				'post_title'  => $title,
				'post_date'   => $post_date,
			);
			wp_update_post( $args );

			$counter++;
		}

		// Create two pages, make one parent of the other.
		$page_ids  = self::factory()->post->create_many( 2, array( 'post_type' => 'page' ) );
		$child_id  = array_pop( $page_ids );
		$parent_id = array_pop( $page_ids );

		$args = array(
			'ID'          => $child_id,
			'post_parent' => $parent_id,
		);
		wp_update_post( $args );

		// Name the post author 'displayname user'.
		$args = array(
			'ID'           => self::$post_author_id,
			'display_name' => 'displayname user',
			'description'  => 'displayname displayname displayname displayname displayname',
		);
		wp_update_user( $args );

		$user_object = get_user_by( 'ID', self::$post_author_id );
		$user_object->set_role( 'editor' );

		$user_object = get_user_by( 'ID', self::$other_author_id );
		$user_object->set_role( 'author' );

		// Rebuild the index.
		relevanssi_build_index( false, false, 200, false );
	}

	/**
	 * Tests user search.
	 *
	 * Should find user profiles.
	 */
	public function test_user_search() {
		// Search for "user" to find users.
		$args = array(
			's'           => 'user',
			'post_type'   => 'user',
			'numberposts' => -1,
			'post_status' => 'publish',
		);

		$query = $this::results_from_args( $args )['query'];

		// This should match the number of users.
		$this->assertEquals( self::$user_count, $query->found_posts );

		$args = array(
			's'           => 'user',
			'post_type'   => array( 'user', 'post' ),
			'numberposts' => -1,
			'post_status' => 'publish',
		);

		$query = $this::results_from_args( $args )['query'];

		// This should match the number of users and posts.
		$this->assertEquals( self::$user_count + self::$post_count, $query->found_posts );
	}

	/**
	 * Tests AND and OR search.
	 *
	 * The operator default is AND. Test that, then switch to OR and see if
	 * the results still make sense.
	 */
	public function test_operators() {
		// Search for "cat dog" with the OR operator.
		$args = array(
			's'           => 'cat dog',
			'post_type'   => 'post',
			'numberposts' => -1,
			'post_status' => 'publish',
			'operator'    => 'OR',
		);

		$query = $this::results_from_args( $args )['query'];

		// This should find all posts.
		$this->assertEquals( self::$post_count, $query->found_posts );
	}

	/**
	 * Test post pinning for single keyword and "pin for all".
	 */
	public function test_pinning() {
		// Search for "buzzword".
		$args = array(
			's'           => 'buzzword',
			'post_type'   => 'post',
			'numberposts' => -1,
			'post_status' => 'publish',
		);

		$posts = $this::results_from_args( $args )['posts'];

		$buzzword_posts = array();
		foreach ( $posts as $post ) {
			array_push( $buzzword_posts, $post->ID );
		}

		$args = array(
			'post__not_in' => $buzzword_posts,
			'numberposts'  => -1,
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'fields'       => 'ids',
		);

		// These posts don't have "buzzword".
		$non_buzzword_posts = get_posts( $args );

		// Let's pin one of those for "buzzword".
		$pinned_post_id = array_shift( $non_buzzword_posts );
		update_post_meta( $pinned_post_id, '_relevanssi_pin', 'buzzword' );

		// Reindex the post.
		relevanssi_index_doc( $pinned_post_id, true, relevanssi_get_custom_fields(), true );

		// Search for "buzzword".
		$args = array(
			's'           => 'buzzword',
			'post_type'   => 'post',
			'numberposts' => -1,
			'post_status' => 'publish',
		);

		$posts = $this::results_from_args( $args )['posts'];

		$first_result = array_shift( $posts );

		$this->assertEquals( $pinned_post_id, $first_result->ID );

		// Then unpin.
		delete_post_meta( $pinned_post_id, '_relevanssi_pin' );
		relevanssi_index_doc( $pinned_post_id, true, relevanssi_get_custom_fields(), true );

		// Let's take another post and pin it for all keywords.
		$pinned_for_all_post_id = array_shift( $non_buzzword_posts );
		update_post_meta( $pinned_for_all_post_id, '_relevanssi_pin_for_all', 'on' );
		update_post_meta( $pinned_for_all_post_id, 'visible', 'buzzword' );
		relevanssi_index_doc( $pinned_for_all_post_id, true, relevanssi_get_custom_fields(), true );

		// Search for "buzzword".
		$args = array(
			's'           => 'buzzword',
			'post_type'   => 'post',
			'numberposts' => -1,
			'post_status' => 'publish',
		);

		$posts = $this::results_from_args( $args )['posts'];

		$first_result = array_shift( $posts );

		$this->assertEquals( $pinned_for_all_post_id, $first_result->ID );

		// Search for "buzzword" using "fields" set to "ids". This should find
		// the post and not get an error.
		$args = array(
			's'           => 'buzzword',
			'post_type'   => 'post',
			'numberposts' => -1,
			'post_status' => 'publish',
			'fields'      => 'ids',
		);

		$posts = $this::results_from_args( $args )['posts'];

		$first_result = array_shift( $posts );

		// Since we're looking for "ids", we get an ID.
		$this->assertEquals( $pinned_for_all_post_id, $first_result );

		// Search for "buzzword" using "fields" set to "id=>parent". This should
		// find the post and not get an error.
		$args = array(
			's'           => 'buzzword',
			'post_type'   => 'post',
			'numberposts' => -1,
			'post_status' => 'publish',
			'fields'      => 'id=>parent',
		);

		$posts = $this::results_from_args( $args )['posts'];

		$first_result = array_shift( $posts );

		$this->assertEquals( $pinned_for_all_post_id, $first_result->ID );
	}

	/**
	 * Tests user searching with a really tight throttle.
	 *
	 * If throttle is very tight, user profiles should still be included. This didn't
	 * work properly pre 2.2.
	 */
	public function test_user_throttle() {
		add_filter(
			'pre_option_relevanssi_throttle_limit',
			function( $limit ) {
				return 4;
			}
		);

		$args = array(
			's'           => 'displayname user',
			'numberposts' => -1,
			'post_type'   => 'any',
		);

		$posts = $this::results_from_args( $args )['posts'];

		$is_user_found = false;
		foreach ( $posts as $post ) {
			if ( 'user' === $post->post_type ) {
				$is_user_found = true;
				break;
			}
		}
		$this->assertTrue(
			$is_user_found,
			'User profile should be found in search.'
		);

		add_filter(
			'pre_option_relevanssi_throttle_limit',
			function( $limit ) {
				return 2;
			}
		);

		$args = array(
			's'           => 'displayname user',
			'numberposts' => -1,
			'post_type'   => 'any',
		);

		$posts = $this::results_from_args( $args )['posts'];

		$is_user_found = false;
		foreach ( $posts as $post ) {
			if ( 'user' === $post->post_type ) {
				$is_user_found = true;
				break;
			}
		}
		$this->assertTrue(
			$is_user_found,
			'User profile should be found in search.'
		);
	}

	/**
	 * Test the NOT operator.
	 */
	public function test_not_operator() {
		$args = array(
			's'           => 'content -words',
			'post_status' => array( 'publish' ),
			'numberposts' => -1,
			'post_type'   => array( 'post' ),
		);

		$query = $this::results_from_args( $args )['query'];

		$this->assertEquals(
			9,
			$query->found_posts,
			'NOT operator should eliminate one post from the results.'
		);
	}

	/**
	 * Test the AND operator.
	 */
	public function test_and_operator() {
		$args = array(
			's'           => 'content +words',
			'post_status' => array( 'publish' ),
			'numberposts' => -1,
			'post_type'   => array( 'post' ),
			'operator'    => 'OR',
		);

		$query = $this::results_from_args( $args )['query'];

		$this->assertEquals(
			1,
			$query->found_posts,
			'AND operator should restrict the results to one post.'
		);
	}

	/**
	 * Test searching for taxonomy terms.
	 */
	public function test_taxonomy_term_search() {
		$args = array(
			's'           => 'categorydescription',
			'post_types'  => 'category',
			'numberposts' => -1,
		);

		$query = $this::results_from_args( $args )['query'];

		$this->assertEquals(
			1,
			$query->found_posts,
			'Search should find one category term.'
		);

		$args = array(
			's'           => 'baz',
			'numberposts' => -1,
		);

		$posts = $this::results_from_args( $args )['posts'];

		$tag_found = false;
		foreach ( $posts as $post ) {
			if ( 'post_tag' === $post->post_type ) {
				$tag_found = true;
			}
		}
		$this->assertTrue( $tag_found, 'A tag can be found.' );
	}

	/**
	 * Helper function that creates a WP_Query, parses the args and runs Relevanssi.
	 *
	 * @param array $args The query arguments.
	 *
	 * @return array An array containing the posts Relevanssi found and the query.
	 */
	private function results_from_args( $args ) {
		$query = new WP_Query();
		$query->parse_query( $args );
		$posts = relevanssi_do_query( $query );
		return array(
			'posts' => $posts,
			'query' => $query,
		);
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
