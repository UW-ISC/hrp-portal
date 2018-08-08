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

	/*
	If the search query is "foo bar baz", $term_list now contains "foo", "bar",
	"baz", "foo bar", "bar baz", and "foo bar baz".
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
			$blog_id = 0;
			if ( isset( $hit->blog_id ) ) {
				// Multisite, so switch_to_blog() to correct blog and process
				// the pinned hits per blog.
				$blog_id = $hit->blog_id;
				switch_to_blog( $blog_id );
				if ( ! isset( $pins_fetched[ $blog_id ] ) ) {
					$positive_ids[ $blog_id ] = $wpdb->get_col( 'SELECT post_id FROM ' . $wpdb->prefix . "postmeta WHERE meta_key = '_relevanssi_pin' AND meta_value IN ( $term_list )" ); // WPCS: unprepared SQL ok, $term_list is escaped.
					$negative_ids[ $blog_id ] = $wpdb->get_col( 'SELECT post_id FROM ' . $wpdb->prefix . "postmeta WHERE meta_key = '_relevanssi_unpin' AND meta_value IN ( $term_list )" ); // WPCS: unprepared SQL ok, $term_list is escaped.
					$pins_fetched[ $blog_id ] = true;
				}
				restore_current_blog();
			} else {
				// Single site.
				if ( ! $pins_fetched ) {
					$positive_ids[0] = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_relevanssi_pin' AND meta_value IN ( $term_list )" ); // WPCS: unprepared SQL ok, $term_list is escaped.
					$negative_ids[0] = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_relevanssi_unpin' AND meta_value IN ( $term_list )" ); // WPCS: unprepared SQL ok, $term_list is escaped.
					$pins_fetched    = true;
				}
			}
			if ( is_array( $positive_ids[ $blog_id ] ) && count( $positive_ids[ $blog_id ] ) > 0 && in_array( $hit->ID, $positive_ids[ $blog_id ], true ) ) {
				$pinned_posts[] = $hit;
			} else {
				if ( 'on' === get_post_meta( $hit->ID, '_relevanssi_pin_for_all', true ) ) {
					$pinned_posts[] = $hit;
				} elseif ( is_array( $negative_ids[ $blog_id ] ) && count( $negative_ids[ $blog_id ] ) > 0 ) {
					if ( ! in_array( $hit->ID, $negative_ids[ $blog_id ], true ) ) {
						$other_posts[] = $hit;
					}
				} else {
					$other_posts[] = $hit;
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
