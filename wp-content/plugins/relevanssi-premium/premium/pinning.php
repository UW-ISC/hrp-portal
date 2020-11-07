<?php
/**
 * /premium/pinning.php
 *
 * Pinning feature.
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 * @license https://wordpress.org/about/gpl/ GNU General Public License
 * @see     https://www.relevanssi.com/
 */

add_filter( 'relevanssi_content_to_index', 'relevanssi_add_pinned_words_to_post_content', 10, 2 );
add_filter( 'relevanssi_hits_filter', 'relevanssi_pinning' );

/**
 * Adds the pinned posts to searches.
 *
 * Finds the posts that are pinned to the search terms and adds them to the search
 * results if necessary. This function is triggered from the 'relevanssi_hits_filter'
 * filter hook.
 *
 * @global $wpdb      The WordPress database interface.
 * @global $wp_filter The global filter array.
 *
 * @param array $hits The hits found.
 *
 * @return array $hits The hits, with pinned posts.
 */
function relevanssi_pinning( $hits ) {
	global $wpdb, $wp_filter;

	// Is pinning used?
	$results = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE ( meta_key = '_relevanssi_pin' OR meta_key = '_relevanssi_unpin' OR meta_key = '_relevanssi_pin_for_all' ) AND meta_value != '' LIMIT 1" );
	if ( empty( $results ) ) {
		// No, nothing is pinned.
		return $hits;
	}

	// Disable all filter functions on 'relevanssi_stemmer'.
	if ( isset( $wp_filter['relevanssi_stemmer'] ) ) {
		$callbacks                                  = $wp_filter['relevanssi_stemmer']->callbacks;
		$wp_filter['relevanssi_stemmer']->callbacks = null;
	}

	$terms = relevanssi_tokenize( $hits[1], false );

	// Re-enable the removed filters.
	if ( isset( $wp_filter['relevanssi_stemmer'] ) ) {
		$wp_filter['relevanssi_stemmer']->callbacks = $callbacks;
	}

	$escaped_terms = array();
	foreach ( array_keys( $terms ) as $term ) {
		$escaped_terms[] = esc_sql( $term );
	}

	$term_list           = array();
	$count_escaped_terms = count( $escaped_terms );
	for ( $length = 1; $length <= $count_escaped_terms; $length++ ) {
		for ( $offset = 0; $offset <= $count_escaped_terms - $length; $offset++ ) {
			$slice       = array_slice( $escaped_terms, $offset, $length );
			$term_list[] = implode( ' ', $slice );
		}
	}

	/**
	 * Doing this instead of individual get_post_meta() calls can cut hundreds
	 * of database queries!
	 */
	$posts_pinned_for_all = array_flip(
		$wpdb->get_col(
			"SELECT post_id FROM $wpdb->postmeta
			WHERE meta_key = '_relevanssi_pin_for_all'
			AND meta_value = 'on'"
		)
	);

	/**
	 * If the search query is "foo bar baz", $term_list now contains "foo", "bar",
	 *"baz", "foo bar", "bar baz", and "foo bar baz".
	*/

	if ( is_array( $term_list ) ) {
		$term_list = implode( "','", $term_list );
		$term_list = "'$term_list'";

		$positive_ids = array();
		$negative_ids = array();

		$pins_fetched = false;
		$pinned_posts = array();
		$other_posts  = array();
		foreach ( $hits[0] as $hit ) {
			$return_id = false;
			if ( is_int( $hit ) ) {
				// Search is using fields=>ids.
				$hit       = relevanssi_get_post( $hit );
				$return_id = true;
			}
			$blog_id = 0;
			if ( isset( $hit->blog_id ) ) {
				// Multisite, so switch_to_blog() to correct blog and process
				// the pinned hits per blog.
				$blog_id = $hit->blog_id;
				switch_to_blog( $blog_id );
				if ( ! isset( $pins_fetched[ $blog_id ] ) ) {
					$positive_ids[ $blog_id ] = $wpdb->get_col( 'SELECT post_id FROM ' . $wpdb->prefix . "postmeta WHERE meta_key = '_relevanssi_pin' AND meta_value IN ( $term_list )" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$negative_ids[ $blog_id ] = $wpdb->get_col( 'SELECT post_id FROM ' . $wpdb->prefix . "postmeta WHERE meta_key = '_relevanssi_unpin' AND meta_value IN ( $term_list )" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$pins_fetched[ $blog_id ] = true;
				}
				restore_current_blog();
			} else {
				// Single site.
				if ( ! $pins_fetched ) {
					$positive_ids[0] = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_relevanssi_pin' AND meta_value IN ( $term_list )" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$negative_ids[0] = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_relevanssi_unpin' AND meta_value IN ( $term_list )" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$pins_fetched    = true;
				}
			}
			$hit_id = strval( $hit->ID ); // The IDs from the database are strings, the one from the post is an integer in some contexts.
			if ( is_array( $positive_ids[ $blog_id ] ) && count( $positive_ids[ $blog_id ] ) > 0 && in_array( $hit_id, $positive_ids[ $blog_id ], true ) ) {
				$hit->relevanssi_pinned = 1;
				if ( $return_id ) {
					$pinned_posts[] = $hit->ID;
				} else {
					$pinned_posts[] = $hit;
				}
			} else {
				if ( isset( $posts_pinned_for_all[ $hit->ID ] ) ) {
					$hit->relevanssi_pinned = 1;
					if ( $return_id ) {
						$pinned_posts[] = $hit->ID;
					} else {
						$pinned_posts[] = $hit;
					}
				} elseif ( is_array( $negative_ids[ $blog_id ] ) && count( $negative_ids[ $blog_id ] ) > 0 ) {
					if ( ! in_array( $hit_id, $negative_ids[ $blog_id ], true ) ) {
						if ( $return_id ) {
							$other_posts[] = $hit->ID;
						} else {
							$other_posts[] = $hit;
						}
					}
				} else {
					if ( $return_id ) {
						$other_posts[] = $hit->ID;
					} else {
						$other_posts[] = $hit;
					}
				}
			}
		}

		$hits[0] = array_merge( $pinned_posts, $other_posts );
	}
	return $hits;
}

/**
 * Adds pinned words to post content.
 *
 * Adds pinned terms to post content to make sure posts are found with the
 * pinned terms.
 *
 * @param string $content Post content.
 * @param object $post    The post object.
 */
function relevanssi_add_pinned_words_to_post_content( $content, $post ) {
	$pin_words = get_post_meta( $post->ID, '_relevanssi_pin', false );
	foreach ( $pin_words as $word ) {
		$content .= " $word";
	}
	return $content;
}

/**
 * Provides the pinning functionality for the admin search.
 *
 * @param object $post  The post object.
 * @param string $query The search query.
 *
 * @return array First item is a string containing the pinning buttons, the second
 *               item is a string containing the "pinned" notice if the post is
 *               pinned.
 */
function relevanssi_admin_search_pinning( $post, $query ) {
	$pinned          = '';
	$pinning_buttons = array();

	$pinned_words = array();
	if ( isset( $post->relevanssi_pinned ) ) {
		$pinned_words = get_post_meta( $post->ID, '_relevanssi_pin' );
		$pinned       = '<strong>' . __( '(pinned)', 'relevanssi' ) . '</strong>';
	}

	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return array( '', $pinned );
	}

	$tokens = relevanssi_tokenize( $query );
	foreach ( array_keys( $tokens ) as $token ) {
		if ( ! in_array( $token, $pinned_words, true ) ) {
			/* Translators: %s is the search term. */
			$pinning_button    = sprintf( '<button type="button" class="pin" data-postid="%1$d" data-keyword="%2$s">%3$s</button>', $post->ID, $token, sprintf( __( "Pin for '%s'", 'relevanssi' ), $token ) );
			$pinning_buttons[] = $pinning_button;
		} else {
			/* Translators: %s is the search term. */
			$pinning_button    = sprintf( '<button type="button" class="unpin" data-postid="%1$d" data-keyword="%2$s">%3$s</button>', $post->ID, $token, sprintf( __( "Unpin for '%s'", 'relevanssi' ), $token ) );
			$pinning_buttons[] = $pinning_button;
		}
	}
	$pinning_buttons = implode( ' ', $pinning_buttons );

	return array( $pinning_buttons, $pinned );
}
