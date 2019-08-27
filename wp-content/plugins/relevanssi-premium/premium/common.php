<?php
/**
 * /premium/common.php
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 * @license https://wordpress.org/about/gpl/ GNU General Public License
 * @see     https://www.relevanssi.com/
 */

/**
 * Returns related searches.
 *
 * Returns a list of searches related to the given search query. Example:
 *
 * relevanssi_related(get_search_query(), '<h3>Related Searches:</h3><ul><li>', '</li><li>', '</li></ul>' );
 *
 * @global $wpdb The WordPress database interface.
 * @global $relevanssi_variables The global Relevanssi variables, used for the database table names.
 *
 * @param string $query  The search query (get_search_query() is a good way to get the current query).
 * @param string $pre    What is printed before the results, default '<ul><li>'.
 * @param string $sep    The separator between individual results, default '</li><li>'.
 * @param string $post   What is printed after the results, default '</li></ul>'.
 * @param int    $number Number of related searches to show, default 5.
 *
 * @author John Blackbourn
 */
function relevanssi_related( $query, $pre = '<ul><li>', $sep = '</li><li>', $post = '</li></ul>', $number = 5 ) {
	global $wpdb, $relevanssi_variables;

	$output  = array();
	$related = array();
	$tokens  = relevanssi_tokenize( $query );
	if ( empty( $tokens ) ) {
		return;
	}

	$query_slug = sanitize_title( $query );
	$related    = get_transient( 'related-' . $query_slug );
	if ( ! $related ) {
		/**
		 * Loop over each token in the query and return logged queries which:
		 *
		 *  - Contain a matching token
		 *  - Don't match the query or the token exactly
		 *  - Have at least 2 hits
		 *  - Have been queried at least twice
		 *
		 * then order by most queried with a max of $number results.
		 */
		foreach ( $tokens as $token => $count ) {
			$escaped_token = '%' . $wpdb->esc_like( "$token" ) . '%';
			$log_table     = $relevanssi_variables['log_table'];
			$results       = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT query ' .
					"FROM $log_table " . // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					'WHERE query LIKE %s
					AND query NOT IN (%s, %s)
					AND hits > 1
					GROUP BY query
					HAVING count(query) > 1
					ORDER BY count(query) DESC
					LIMIT %d',
					$escaped_token,
					$token,
					$query,
					$number
				)
			);
			foreach ( $results as $result ) {
				$related[] = $result->query;
			}
		}
		if ( empty( $related ) ) {
			return;
		} else {
			set_transient( 'related-' . $query_slug, $related, 60 * 60 * 24 * 7 );
		}
	}

	// Order results by most matching tokens then slice to a maximum of $number results.
	$related = array_keys( array_count_values( $related ) );
	$related = array_slice( $related, 0, $number );
	foreach ( $related as $rel ) {
		$url      = add_query_arg(
			array(
				's' => rawurlencode( $rel ),
			),
			home_url()
		);
		$rel      = esc_attr( $rel );
		$output[] = "<a href='$url'>$rel</a>";
	}

	echo $pre . implode( $sep, $output ) . $post; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Replaces get_posts() in a way that handles users and taxonomy terms.
 *
 * Custom-made get_posts() replacement that creates post objects for users and
 * taxonomy terms. For regular posts, the function uses get_posts() and a caching
 * mechanism.
 *
 * @global array $relevanssi_post_array The global Relevanssi post array used as a
 *                                      cache.
 *
 * @param int $id      The post ID to fetch. If the ID begins with 'u_', it's
 * considered a user ID and if it begins with '**', it's considered a taxonomy term.
 * @param int $blog_id The blog ID, used to make caching work in multisite
 * environment. Defaults to -1, which means the blog id is not used.
 *
 * @return $post The post object for the post ID.
 */
function relevanssi_premium_get_post( $id, $blog_id = -1 ) {
	global $relevanssi_post_array;
	$type = substr( $id, 0, 2 );
	switch ( $type ) {
		case 'u_':
			list( $throwaway, $id ) = explode( '_', $id );

			$user                  = get_userdata( $id );
			$post                  = new stdClass();
			$post->post_title      = $user->display_name;
			$post->post_content    = $user->description;
			$post->post_type       = 'user';
			$post->ID              = $id;
			$post->relevanssi_link = get_author_posts_url( $id );
			$post->post_status     = 'publish';
			$post->post_date       = date( 'Y-m-d H:i:s' );
			$post->post_author     = 0;
			$post->post_name       = '';
			$post->post_excerpt    = '';
			$post->comment_status  = '';
			$post->ping_status     = '';
			$post->user_id         = $id;

			/**
			 * Filters the user profile post object.
			 *
			 * After a post object is created from the user profile, it is passed
			 * through this filter so it can be modified.
			 *
			 * @param Object $post The post object.
			 */
			$post = apply_filters( 'relevanssi_user_profile_to_post', $post );
			break;
		case 'p_':
			list( $throwaway, $id ) = explode( '_', $id );

			$post_type_name        = relevanssi_get_post_type_by_id( $id );
			$post_type             = get_post_type_object( $post_type_name );
			$post                  = new stdClass();
			$post->post_title      = $post_type->label;
			$post->post_content    = $post_type->description;
			$post->post_type       = 'post_type';
			$post->ID              = $id;
			$post->relevanssi_link = get_post_type_archive_link( $post_type_name );
			$post->post_status     = 'publish';
			$post->post_date       = date( 'Y-m-d H:i:s' );
			$post->post_author     = 0;
			$post->post_name       = '';
			$post->post_excerpt    = '';
			$post->comment_status  = '';
			$post->ping_status     = '';
			$post->post_type_id    = $post_type_name;

			/**
			 * Filters the post type post object.
			 *
			 * After a post object is created from a post type, it is passed through
			 * this filter so it can be modified.
			 *
			 * @param Object $post The post object.
			 */
			$post = apply_filters( 'relevanssi_post_type_to_post', $post );
			break;
		case '**':
			list( $throwaway, $taxonomy, $id ) = explode( '**', $id );

			$term = get_term( $id, $taxonomy );
			if ( is_wp_error( $term ) ) {
				return new WP_Error( 'term_not_found', "Taxonomy term wasn't found." );
			}
			$post                  = new stdClass();
			$post->post_title      = $term->name;
			$post->post_content    = $term->description;
			$post->post_type       = $taxonomy;
			$post->ID              = -1;
			$post->post_status     = 'publish';
			$post->post_date       = date( 'Y-m-d H:i:s' );
			$post->relevanssi_link = get_term_link( $term, $taxonomy );
			$post->post_author     = 0;
			$post->post_name       = '';
			$post->post_excerpt    = '';
			$post->comment_status  = '';
			$post->ping_status     = '';
			$post->term_id         = $id;
			$post->post_parent     = $term->parent;

			/**
			 * Filters the taxonomy term post object.
			 *
			 * After a post object is created from the taxonomy term, it is passed
			 * through this filter so it can be modified.
			 *
			 * @param Object $post The post object.
			 */
			$post = apply_filters( 'relevanssi_taxonomy_term_to_post', $post );
			break;
		default:
			$cache_id = $id;
			if ( -1 !== $blog_id ) {
				$cache_id = $blog_id . '|' . $id;
			}
			if ( isset( $relevanssi_post_array[ $cache_id ] ) ) {
				// Post exists in the cache.
				$post = $relevanssi_post_array[ $cache_id ];
			} else {
				$post = get_post( $id );
			}
			if (
				'on' === get_option( 'relevanssi_link_pdf_files' )
				&& ! empty( $post->post_mime_type )
				) {
				/**
				 * Filters the URL to the attachment file.
				 *
				 * If you set the attachment indexing to index attachments that
				 * are stored outside the WP attachment system, use this filter
				 * to provide a link to the attachment.
				 *
				 * @param string The URL to the attachment file.
				 * @param int    The attachment post ID number.
				 */
				$post->relevanssi_link = apply_filters(
					'relevanssi_get_attachment_url',
					wp_get_attachment_url( $post->ID ),
					$post->ID
				);
			}
	}
	return $post;
}

/**
 * Returns a list of indexed taxonomies.
 *
 * This will also include "user", if user profiles are indexed, and "post_type", if
 * post type archives are indexed.
 *
 * @return array $non_post_post_types_array An array of taxonomies Relevanssi is set
 * to index (and "user" or "post_type").
 */
function relevanssi_get_non_post_post_types() {
	// These post types are not posts, ie. they are taxonomy terms and user profiles.
	$non_post_post_types_array = array();
	if ( get_option( 'relevanssi_index_taxonomies' ) ) {
		$taxonomies = get_option( 'relevanssi_index_terms' );
		if ( is_array( $taxonomies ) ) {
			$non_post_post_types_array = $taxonomies;
		}
	}
	if ( get_option( 'relevanssi_index_users' ) ) {
		$non_post_post_types_array[] = 'user';
	}
	if ( get_option( 'relevanssi_index_post_type_archives' ) ) {
		$non_post_post_types_array[] = 'post_type';
	}
	return $non_post_post_types_array;
}

/**
 * Gets the PDF content for the child posts of the post.
 *
 * @global $wpdb The WordPress database interface.
 *
 * @param int $post_id The post ID of the parent post.
 *
 * @return string $pdf_content The PDF content of the child posts.
 */
function relevanssi_get_child_pdf_content( $post_id ) {
	global $wpdb;

	$post_id     = intval( $post_id );
	$pdf_content = '';

	if ( $post_id > 0 ) {
		$pdf_content = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta AS pm, $wpdb->posts AS p WHERE pm.post_id = p.ID AND p.post_parent = $post_id AND meta_key = '_relevanssi_pdf_content'" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// Only user-provided variable is $post_id, and that's from Relevanssi and sanitized as an int.
		return implode( ' ', $pdf_content );
	}

	return '';
}

/**
 * Provides the Premium version "Did you mean" recommendations.
 *
 * Provides a better version of "Did you mean" recommendations, using the spelling corrector class to generate a correct spelling.
 *
 * @global WP_Query $wp_query The query object, used to check the number of posts found.
 *
 * @param string $query The search query to correct.
 * @param string $pre   Text printed out before the suggestion.
 * @param string $post  Text printed out after the suggestion.
 * @param int    $n     Maximum number of hits before the suggestions are shown, default 5.
 */
function relevanssi_premium_didyoumean( $query, $pre, $post, $n = 5 ) {
	global $wp_query;

	$total_results = $wp_query->found_posts;
	$result        = '';

	if ( $total_results > $n ) {
		return $result;
	}

	$suggestion = relevanssi_premium_generate_suggestion( $query );
	if ( empty( $suggestion ) ) {
		$suggestion = relevanssi_simple_generate_suggestion( $query );
	}

	$result = null;
	if ( $suggestion ) {
		$url = get_bloginfo( 'url' );
		$url = esc_attr(
			add_query_arg(
				array(
					's' => rawurlencode( $suggestion ),
				),
				$url
			)
		);
		/** This filter is documented in lib/common.php */
		$url = apply_filters( 'relevanssi_didyoumean_url', $url, $query, $suggestion );

		// Escape the suggestion to avoid XSS attacks.
		$suggestion = htmlspecialchars( $suggestion );

		/** This filter is documented in lib/common.php */
		$result = apply_filters( 'relevanssi_didyoumean_suggestion', "$pre<a href='$url'>$suggestion</a>$post" );
	}
	return $result;
}

/**
 * Generates the "Did you mean" suggestion.
 *
 * Generates "Did you mean" suggestions given a query to correct, using the spelling corrector method.
 *
 * @param string $query The search query to correct.
 *
 * @return string $query Corrected query, empty if there are no corrections available.
 */
function relevanssi_premium_generate_suggestion( $query ) {
	$corrected_query = '';

	if ( class_exists( 'Relevanssi_SpellCorrector' ) ) {
		$query  = htmlspecialchars_decode( $query, ENT_QUOTES );
		$tokens = relevanssi_tokenize( $query );

		$sc = new Relevanssi_SpellCorrector();

		$correct       = array();
		$exact_matches = 0;
		foreach ( array_keys( $tokens ) as $token ) {
			$token = trim( $token );
			$c     = $sc->correct( $token );
			if ( ! empty( $c ) && strval( $token ) !== $c ) {
				array_push( $correct, $c );
				$query = str_ireplace( $token, $c, $query ); // Replace misspelled word in query with suggestion.
			} elseif ( null !== $c ) {
				$exact_matches++;
			}
		}
		if ( count( $tokens ) === $exact_matches ) {
			// All tokens are correct.
			return '';
		}
		if ( count( $correct ) > 0 ) {
			// Strip quotes, because they are likely incorrect.
			$query           = str_replace( '"', '', $query );
			$corrected_query = $query;
		}
	}

	return $corrected_query;
}

/**
 * Multisite-friendly get_post().
 *
 * Gets a post using relevanssi_get_post() from the specified subsite.
 *
 * @param int $blogid The blog ID.
 * @param int $id     The post ID.
 *
 * @return object $post The post object.
 */
function relevanssi_get_multisite_post( $blogid, $id ) {
	switch_to_blog( $blogid );
	if ( ! is_numeric( mb_substr( $id, 0, 1 ) ) ) {
		// The post ID does not start with a number; this is a user or a taxonomy term,
		// so suspend cache addition to avoid getting garbage in the cache.
		wp_suspend_cache_addition( true );
	}
	$post = relevanssi_get_post( $id, $blogid );
	restore_current_blog();
	return $post;
}

/**
 * Initializes things for Relevanssi Premium.
 *
 * Adds metaboxes, depending on settings; adds synonym indexing filter if necessary and removes an unnecessary action.
 */
function relevanssi_premium_init() {
	$show_post_controls = true;
	if ( 'on' === get_option( 'relevanssi_hide_post_controls' ) ) {
		$show_post_controls = false;
		/**
		 * Adjusts the capability required to show the Relevanssi post controls for admins.
		 *
		 * @param string $capability The minimum capability required, default 'manage_options'.
		 */
		if ( 'on' === get_option( 'relevanssi_show_post_controls' ) && current_user_can( apply_filters( 'relevanssi_options_capability', 'manage_options' ) ) ) {
			$show_post_controls = true;
		}
	}
	if ( $show_post_controls ) {
		add_action( 'add_meta_boxes', 'relevanssi_add_metaboxes' );
	}

	if ( 'on' === get_option( 'relevanssi_index_synonyms' ) ) {
		add_filter( 'relevanssi_indexing_tokens', 'relevanssi_add_indexing_synonyms', 10 );
	}

	// If the relevanssi_save_postdata is not disabled, scheduled publication will swipe out the Relevanssi post controls settings.
	add_action(
		'future_to_publish',
		function( $post ) {
			remove_action( 'save_post', 'relevanssi_save_postdata' );
		}
	);

	// Add the related posts filters if necessary.
	relevanssi_related_init();
}

/**
 * Replaces the standard permalink with $post->relevanssi_link if it exists.
 *
 * Relevanssi adds a link to the user profile or taxonomy term page to $post->relevanssi_link. This function replaces permalink with that link, if it exists.
 *
 * @param string $permalink The permalink to filter.
 * @param int    $post_id   The post ID.
 *
 * @return string $permalink Modified permalink.
 */
function relevanssi_post_link_replace( $permalink, $post_id ) {
	$post = relevanssi_get_post( $post_id );
	if ( property_exists( $post, 'relevanssi_link' ) ) {
		$permalink = $post->relevanssi_link;
	}
	return $permalink;
}

/**
 * Fetches a list of words from the Relevanssi database for spelling corrector.
 *
 * A helper function for the spelling corrector. Fetches all the words from the
 * Relevanssi database to use as a source material for spelling suggestions.
 *
 * @global $wpdb The WordPress database interface.
 * @global $relevanssi_variables The global Relevanssi variables, used for the database table names.
 *
 * @return array $words An array of words, with the word as the key and number of occurrances as the value.
 */
function relevanssi_get_words() {
	global $wpdb, $relevanssi_variables;

	$words = get_transient( 'relevanssi_words' );

	if ( ! $words ) {
		/**
		 * The minimum limit of occurrances to include a word.
		 *
		 * To save resources, only words with more than this many occurrances are fed for
		 * the spelling corrector. If there are problems with the spelling corrector,
		 * increasing this value may fix those problems.
		 *
		 * @param int $number The number of occurrances must be more than this value,
		 * default 2.
		 */
		$count = apply_filters( 'relevanssi_get_words_having', 2 );
		if ( ! is_numeric( $count ) ) {
			$count = 2;
		}
		$q = 'SELECT term, SUM(title + content + comment + tag + link + author + category + excerpt + taxonomy + customfield) as c FROM ' . $relevanssi_variables['relevanssi_table'] . " GROUP BY term HAVING c > $count"; // Safe: $count is numeric.

		$results = $wpdb->get_results( $q ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$words = array();
		foreach ( $results as $result ) {
			$words[ $result->term ] = $result->c;
		}

		set_transient( 'relevanssi_words', $words, WEEK_IN_SECONDS );
	}

	return $words;
}

/**
 * Adds the Premium options.
 *
 * @global array $relevanssi_variables The global Relevanssi variables, used to set the link boost default.
 */
function relevanssi_premium_install() {
	global $relevanssi_variables;

	add_option( 'relevanssi_link_boost', $relevanssi_variables['link_boost_default'] );
	add_option( 'relevanssi_post_type_weights', '' );
	add_option( 'relevanssi_index_users', 'off' );
	add_option( 'relevanssi_index_subscribers', 'off' );
	add_option( 'relevanssi_index_taxonomies', 'off' );
	add_option( 'relevanssi_index_post_type_archives', 'off' );
	add_option( 'relevanssi_internal_links', 'noindex' );
	add_option( 'relevanssi_thousand_separator', '' );
	add_option( 'relevanssi_disable_shortcodes', '' );
	add_option( 'relevanssi_api_key', '' );
	add_option(
		'relevanssi_recency_bonus',
		array(
			'bonus' => '',
			'days'  => '',
		)
	);
	add_option( 'relevanssi_mysql_columns', '' );
	add_option( 'relevanssi_hide_post_controls', 'off' );
	add_option( 'relevanssi_show_post_controls', 'off' );
	add_option( 'relevanssi_index_terms', array() );
	add_option( 'relevanssi_index_synonyms', 'off' );
	add_option( 'relevanssi_index_pdf_parent', 'off' );
	add_option( 'relevanssi_read_new_files', 'off' );
	add_option( 'relevanssi_send_pdf_files', 'off' );
	add_option( 'relevanssi_link_pdf_files', 'off' );
	add_option( 'relevanssi_server_location', 'us' );
	add_option( 'relevanssi_do_not_call_home', 'off' );
	add_option( 'relevanssi_redirects', array() );
	add_option( 'relevanssi_related_settings', relevanssi_related_default_settings() );
	add_option( 'relevanssi_related_style', relevanssi_related_default_styles() );
}

/**
 * Returns the attachment reading server URL.
 *
 * Checks the correct server from 'relevanssi_server_location' option and returns the
 * correct URL from the constants.
 *
 * @return string The attachment reading server URL.
 */
function relevanssi_get_server_url() {
	$server = RELEVANSSI_US_SERVICES_URL;
	if ( 'eu' === get_option( 'relevanssi_server_location' ) ) {
		$server = RELEVANSSI_EU_SERVICES_URL;
	}
	/**
	 * Allows changing the attachment reading server URL.
	 *
	 * @param string The server URL.
	 */
	return apply_filters( 'relevanssi_attachment_server_url', $server );
}
