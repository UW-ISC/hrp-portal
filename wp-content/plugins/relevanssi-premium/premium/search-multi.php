<?php
/**
 * /premium/search-multi.php
 *
 * Multisite searching logic.
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 * @license https://wordpress.org/about/gpl/ GNU General Public License
 * @see     https://www.relevanssi.com/
 */

/**
 * Does multisite searches.
 *
 * Handles the multisite searching when the "searchblogs" parameter is present.
 * Has slightly limited set of options compared to the single-site searches.
 *
 * @global $wpdb The WordPress database interface.
 * @global $relevanssi_variables The global Relevanssi variables, used for the database table names.
 *
 * @param array $multi_args Multisite search arguments. Possible parameters: 'post_type',
 * 'search_blogs', 'operator', 'meta_query', 'orderby', 'order'.
 *
 * @return array $results Hits found and other information about the result set.
 */
function relevanssi_search_multi( $multi_args ) {
	global $relevanssi_variables, $wpdb;

	/**
	 * Filters the search arguments.
	 *
	 * @param array $multi_args An associative array of the search parameters.
	 */
	$filtered_values = apply_filters( 'relevanssi_search_filters', $multi_args );

	if ( isset( $filtered_values['q'] ) ) {
		$q = $filtered_values['q'];
	} else {
		// No search term, can't proceed.
		return $hits;
	}

	$post_type = '';
	if ( isset( $filtered_values['post_type'] ) ) {
		$post_type = $filtered_values['post_type'];
	}
	$search_blogs = '';
	if ( isset( $filtered_values['search_blogs'] ) ) {
		$search_blogs = $filtered_values['search_blogs'];
	}
	$operator = '';
	if ( isset( $filtered_values['operator'] ) ) {
		$operator = $filtered_values['operator'];
	}
	$meta_query = '';
	if ( isset( $filtered_values['meta_query'] ) ) {
		$meta_query = $filtered_values['meta_query'];
	}
	$orderby = '';
	if ( isset( $filtered_values['orderby'] ) ) {
		$orderby = $filtered_values['orderby'];
	}
	$order = '';
	if ( isset( $filtered_values['order'] ) ) {
		$order = $filtered_values['order'];
	}
	$include_attachments = '';
	if ( isset( $filtered_values['include_attachments'] ) ) {
		$include_attachments = $filtered_values['include_attachments'];
	}

	$hits = array();

	$remove_stopwords = false;
	$terms            = relevanssi_tokenize( $q, $remove_stopwords );

	if ( count( $terms ) < 1 ) {
		// Tokenizer killed all the search terms.
		return $hits;
	}
	$terms = array_keys( $terms ); // Don't care about tf in query.

	$total_hits = 0;

	$title_matches   = array();
	$tag_matches     = array();
	$link_matches    = array();
	$comment_matches = array();
	$body_matches    = array();
	$scores          = array();
	$term_hits       = array();
	$hitsbyweight    = array();

	$matching_method = get_option( 'relevanssi_fuzzy' );

	if ( 'all' === $search_blogs ) {
		$raw_blog_list = get_sites( array( 'number' => 2000 ) ); // There's likely flaming death with even lower values of 'number'.
		$blog_list     = array();
		foreach ( $raw_blog_list as $blog ) {
			$blog_list[] = $blog->blog_id;
		}
		$search_blogs = implode( ',', $blog_list );
	}

	$search_blogs = explode( ',', $search_blogs );
	if ( ! is_array( $search_blogs ) ) {
		// No blogs to search, so let's quit.
		return $hits;
	}

	if ( 'always' === $matching_method ) {
		/**
		 * Filters the partial matching search query.
		 *
		 * By default partial matching matches the beginnings and the ends of the
		 * words. If you want it to match inside words, add a function to this
		 * hook that returns '(relevanssi.term LIKE '%#term#%')'.
		 *
		 * @param string The partial matching query.
		 */
		$o_term_cond = apply_filters( 'relevanssi_fuzzy_query', "(relevanssi.term LIKE '#term#%' OR relevanssi.term_reverse LIKE CONCAT(REVERSE('#term#'), '%')) " );
	} else {
		$o_term_cond = " relevanssi.term = '#term#' ";
	}

	$post_type_weights = get_option( 'relevanssi_post_type_weights' );

	$scores = array();

	$original_blog = $wpdb->blogid;
	foreach ( $search_blogs as $blogid ) {
		// Only search blogs that are publicly available (unless filter says otherwise).
		$public_status = get_blog_status( $blogid, 'public' );
		if ( null === $public_status ) {
			// Blog doesn't actually exist.
			continue;
		}
		/**
		 * Adjusts the possible values of blog public status.
		 *
		 * By default Relevanssi requires blogs to be public so they can be searched.
		 * If you want a non-public blog in the search results, make this filter
		 * return true.
		 *
		 * @param boolean $public_status Is the blog public?
		 * @param int     $blogid        Blog ID.
		 */
		if ( false === apply_filters( 'relevanssi_multisite_public_status', $public_status, $blogid ) ) {
			continue;
		}

		// Don't search blogs that are marked "spam" or "deleted".
		if ( get_blog_status( $blogid, 'spam' ) ) {
			continue;
		}
		if ( get_blog_status( $blogid, 'delete' ) ) {
			continue;
		}

		// Ok, we should have a valid blog.
		switch_to_blog( $blogid );
		$relevanssi_table = $wpdb->prefix . 'relevanssi';

		// See if Relevanssi tables exist.
		$exists = $wpdb->get_var( "SELECT count(*) FROM information_schema.TABLES WHERE (TABLE_SCHEMA = '" . DB_NAME . "') AND (TABLE_NAME = '$relevanssi_table')" ); // WPCS: unprepared SQL ok, no user-generated input.
		if ( $exists < 1 ) {
			restore_current_blog();
			continue;
		}

		$query_join         = '';
		$query_restrictions = '';

		// If $post_type is not set, see if there are post types to exclude from the search.
		// If $post_type is set, there's no need to exclude, as we only include.
		if ( ! $post_type ) {
			$negative_post_type = relevanssi_get_negative_post_type( $include_attachments );
		} else {
			$negative_post_type = null;
		}

		$non_post_post_types_array = array();
		if ( function_exists( 'relevanssi_get_non_post_post_types' ) ) {
			$non_post_post_types_array = relevanssi_get_non_post_post_types();
		}

		$non_post_post_type = null;
		$site_post_type     = null;
		if ( $post_type ) {
			if ( -1 === $post_type ) {
				$post_type = null; // Facetious sets post_type to -1 if not selected.
			}
			if ( ! is_array( $post_type ) ) {
				$post_types = explode( ',', $post_type );
			} else {
				$post_types = $post_type;
			}

			// This array will contain all regular post types involved in the search parameters.
			$post_post_types = array_diff( $post_types, $non_post_post_types_array );

			// This array has the non-post post types involved.
			$non_post_post_types = array_intersect( $post_types, $non_post_post_types_array );

			// Escape both for SQL queries, just in case.
			$non_post_post_types = esc_sql( $non_post_post_types );
			$post_types          = esc_sql( $post_post_types );

			// Implode to a parameter string, or set to NULL if empty.
			if ( count( $non_post_post_types ) ) {
				$non_post_post_type = "'" . implode( "', '", $non_post_post_types ) . "'";
			} else {
				$non_post_post_type = null;
			}
			if ( count( $post_types ) ) {
				$site_post_type = "'" . implode( "', '", $post_types ) . "'";
			} else {
				$site_post_type = null;
			}
		}

		if ( $site_post_type ) {
			// A post type is set: add a restriction.
			// Clean: $site_post_type is escaped.
			$restriction = " AND (
				relevanssi.doc IN (
					SELECT DISTINCT(posts.ID) FROM $wpdb->posts AS posts
					WHERE posts.post_type IN ( $site_post_type)
				) *np*
			)";

			// There are post types involved that are taxonomies or users, so can't
			// match to wp_posts. Add a relevanssi.type restriction.
			if ( $non_post_post_type ) {
				$restriction = str_replace( '*np*', "OR (relevanssi.type IN ( $non_post_post_type))", $restriction );
				// Clean: $non_post_post_types is escaped.
			} else {
				// No non-post post types, so remove the placeholder.
				$restriction = str_replace( '*np*', '', $restriction );
			}
			$query_restrictions .= $restriction;
		} else {
			// No regular post types.
			if ( $non_post_post_type ) {
				// But there is a non-post post type restriction.
				$query_restrictions .= " AND (relevanssi.type IN ( $non_post_post_type))";
				// Clean: $non_post_post_types is escaped.
			}
		}

		if ( $negative_post_type ) {
			$query_restrictions .= " AND ((relevanssi.doc IN (SELECT DISTINCT(posts.ID) FROM $wpdb->posts AS posts
				WHERE posts.post_type NOT IN ( $negative_post_type))) OR (relevanssi.doc = -1))";
			// Clean: $negative_post_type is escaped.
		}

		/**
		 * Filters the query restrictions in Relevanssi.
		 *
		 * Approximately the same purpose as the default 'posts_where' filter hook.
		 * Can be used to add additional query restrictions to the Relevanssi query.
		 *
		 * @param string $query_restrictions MySQL added to the Relevanssi query.
		 *
		 * @author Charles St-Pierre.
		 */
		$query_restrictions = apply_filters( 'relevanssi_where', $query_restrictions );

		// Handle the meta query.
		if ( ! empty( $meta_query ) ) {
			$meta_query_object = new WP_Meta_Query();
			$meta_query_object->parse_query_vars( array( 'meta_query' => $meta_query ) );
			$meta_sql = $meta_query_object->get_sql( 'post', 'relevanssi', 'doc' );
			if ( $meta_sql ) {
				$query_join         .= $meta_sql['join'];
				$query_restrictions .= $meta_sql['where'];
			}
		}

		// Go get the count from the options, but run the full query if it's not available.
		$doc_count = get_option( 'relevanssi_doc_count' );
		if ( ! $doc_count || $doc_count < 1 ) {
			$doc_count = relevanssi_update_doc_count();
		}

		$no_matches = true;
		if ( 'always' === $matching_method ) {
			$term_query = "(term LIKE '%#term#' OR term LIKE '#term#%') ";
		} else {
			$term_query = " term = '#term#' ";
		}

		$min_length   = get_option( 'relevanssi_min_word_length' );
		$search_again = false;
		$fuzzy        = get_option( 'relevanssi_fuzzy' );

		$content_boost = floatval( get_option( 'relevanssi_content_boost', 1 ) );
		$title_boost   = floatval( get_option( 'relevanssi_title_boost' ) );
		$link_boost    = floatval( get_option( 'relevanssi_link_boost' ) );
		$comment_boost = floatval( get_option( 'relevanssi_comment_boost' ) );

		$recency_bonus       = false;
		$recency_cutoff_date = false;
		if ( function_exists( 'relevanssi_get_recency_bonus' ) ) {
			list( $recency_bonus, $recency_cutoff_date ) = relevanssi_get_recency_bonus();
		}

		$exact_match_bonus = false;
		$exact_match_boost = 0;
		if ( 'on' === get_option( 'relevanssi_exact_match_bonus' ) ) {
			$exact_match_bonus = true;
			/**
			 * Filters the exact match bonus.
			 *
			 * @param array The title bonus under 'title' (default 5) and the content
			 * bonus under 'content' (default 2).
			 */
			$exact_match_boost = apply_filters( 'relevanssi_exact_match_bonus', array(
				'title'   => 5,
				'content' => 2,
			));
		}

		$tag = $relevanssi_variables['post_type_weight_defaults']['post_tag'];
		$cat = $relevanssi_variables['post_type_weight_defaults']['category'];
		if ( ! empty( $post_type_weights['post_tag'] ) ) {
			$tag = $post_type_weights['post_tag'];
		}
		if ( ! empty( $post_type_weights['category'] ) ) {
			$cat = $post_type_weights['category'];
		}

		$doc_weight = array();
		$term_hits  = array();

		do {
			foreach ( $terms as $term ) {
				$term_cond = relevanssi_generate_term_cond( $term, $o_term_cond );
				if ( null === $term_cond ) {
					continue;
				}
				$query = "SELECT COUNT(DISTINCT(relevanssi.doc)) FROM $relevanssi_table AS relevanssi
					$query_join WHERE $term_cond $query_restrictions";
				// Clean: $query_restrictions is escaped, $term_cond is escaped.
				/**
				 * Filters the DF query.
				 *
				 * This query is used to calculate the df for the tf * idf calculations.
				 *
				 * @param string MySQL query to filter.
				 */
				$query = apply_filters( 'relevanssi_df_query_filter', $query );

				$df = $wpdb->get_var( $query ); // WPCS: unprepared SQL ok.

				if ( $df < 1 && 'sometimes' === $fuzzy ) {
					$query = "SELECT COUNT(DISTINCT(relevanssi.doc)) FROM $relevanssi_table AS relevanssi
						$query_join WHERE (relevanssi.term LIKE '$term%'
						OR relevanssi.term_reverse LIKE CONCAT(REVERSE('$term'), '%')) $query_restrictions";
					// Clean: $query_restrictions is escaped, $term is escaped.
					/** Documented in lib/search.php. */
					$query = apply_filters( 'relevanssi_df_query_filter', $query );
					$df    = $wpdb->get_var( $query ); // WPCS: unprepared SQL ok.
				}

				$df_counts[ $term ] = $df;
			}

			// Sort the terms in ascending DF order, so that rarest terms are searched
			// for first. This is to make sure the throttle doesn't cut off posts with
			// rare search terms.
			asort( $df_counts );

			foreach ( $df_counts as $term => $df ) {
				$term_cond = relevanssi_generate_term_cond( $term, $o_term_cond );
				if ( null === $term_cond ) {
					continue;
				}

				// Clean: $term is escaped, as are $query_restrictions.
				$query = "SELECT DISTINCT(relevanssi.doc), relevanssi.*, relevanssi.title * $title_boost +
				relevanssi.content * $content_boost + relevanssi.comment * $comment_boost +
				relevanssi.tag * $tag + relevanssi.link * $link_boost +
				relevanssi.author + relevanssi.category * $cat + relevanssi.excerpt +
				relevanssi.taxonomy + relevanssi.customfield + relevanssi.mysqlcolumn AS tf
				FROM $relevanssi_table AS relevanssi $query_join WHERE $term_cond $query_restrictions";
				/**
				 * Filters the Relevanssi search query.
				 *
				 * @param string $query The Relevanssi search MySQL query.
				 */
				$query = apply_filters( 'relevanssi_query_filter', $query );

				$matches = $wpdb->get_results( $query ); // WPCS: unprepared SQL ok, all user inputs are escaped.
				if ( count( $matches ) < 1 ) {
					continue;
				} else {
					$no_matches = false;
				}

				$total_hits += count( $matches );

				if ( isset( $post_type_weights['post_tag'] ) && is_numeric( $post_type_weights['post_tag'] ) ) {
					$tag_boost = $post_type_weights['post_tag'];
				} else {
					$tag_boost = 1;
				}

				$idf = log( $doc_count / ( 1 + $df ) );
				foreach ( $matches as $match ) {
					if ( 'user' === $match->type ) {
						$match->doc = 'u_' . $match->item;
					} elseif ( 'post_type' === $match->type ) {
						$match->doc = 'p_' . $match->item;
					} elseif ( ! in_array( $match->type, array( 'post', 'attachment' ), true ) ) {
						$match->doc = '**' . $match->type . '**' . $match->item;
					}

					$match->tf =
						$match->title * $title_boost +
						$match->content +
						$match->comment * $comment_boost +
						$match->tag * $tag_boost +
						$match->link * $link_boost +
						$match->author +
						$match->category +
						$match->excerpt +
						$match->taxonomy +
						$match->customfield;

					$term_hits[ $match->doc ][ $term ] =
						$match->title +
						$match->content +
						$match->comment +
						$match->tag +
						$match->link +
						$match->author +
						$match->category +
						$match->excerpt +
						$match->taxonomy +
						$match->customfield;

					if ( $idf < 1 ) {
						$idf = 1;
					}
					$match->weight = $match->tf * $idf;

					$type = relevanssi_get_post_type( $match->doc );
					if ( ! empty( $post_type_weights[ $type ] ) ) {
						$match->weight = $match->weight * $post_type_weights[ $type ];
					}

					if ( $recency_bonus ) {
						$post = relevanssi_get_post( $match->doc );
						if ( strtotime( $post->post_date ) > $recency_cutoff_date ) {
							$match->weight = $match->weight * $recency_bonus;
						}
					}

					if ( $exact_match_bonus ) {
						$post    = relevanssi_get_post( $match->doc );
						$clean_q = str_replace( '"', '', $q );
						if ( stristr( $post->post_title, $clean_q ) !== false ) {
							$match->weight *= $exact_match_boost['title'];
						}
						if ( stristr( $post->post_content, $clean_q ) !== false ) {
							$match->weight *= $exact_match_boost['content'];
						}
					}

					/**
					 * Filters the Relevanssi post matches.
					 *
					 * This powerful filter lets you modify the $match objects, which
					 * are used to calculate the weight of the documents. The object
					 * has attributes which contain the number of hits in different
					 * categories. Post ID is $match->doc, term frequency (TF) is
					 * $match->tf and the total weight is in $match->weight. The
					 * filter is also passed $idf, which is the inverse document
					 * frequency (IDF). The weight is calculated as TF * IDF, which
					 * means you may need the IDF, if you wish to recalculate the
					 * weight for some reason. The third parameter, $term, contains
					 * the search term.
					 *
					 * @param object $match The match object, with includes all
					 * the different categories of post matches.
					 * @param int    $idf   The inverse document frequency, in
					 * case you want to recalculate TF * IDF weights.
					 * @param string $term  The search term.
					 */
					$match = apply_filters( 'relevanssi_match', $match, $idf, $term );

					if ( $match->weight <= 0 ) {
						continue; // The filters killed the match.
					}

					$doc_id = $blogid . '|' . $match->doc;

					$doc_terms[ $match->doc ][ $term ] = true; // Count how many terms are matched to a doc.
					if ( isset( $doc_weight[ $doc_id ] ) ) {
						$doc_weight[ $match->doc ] += $match->weight;
					} else {
						$doc_weight[ $match->doc ] = $match->weight;
					}
					if ( isset( $scores[ $doc_id ] ) ) {
						$scores[ $doc_id ] += $match->weight;
					} else {
						$scores[ $doc_id ] = $match->weight;
					}

					$body_matches[ $doc_id ]    = $match->content;
					$title_matches[ $doc_id ]   = $match->title;
					$link_matches[ $doc_id ]    = $match->link;
					$tag_matches[ $doc_id ]     = $match->tag;
					$comment_matches[ $doc_id ] = $match->comment;
				}
			}

			if ( $no_matches ) {
				if ( $search_again ) {
					// No hits even with partial matching.
					$search_again = false;
				} else {
					if ( 'sometimes' === $matching_method ) {
						$search_again = true;
						$term_query   = "(term LIKE '%#term#' OR term LIKE '#term#%') ";
					}
				}
			} else {
				$search_again = false;
			}
		} while ( $search_again );

		$strip_stopwords     = true;
		$terms_without_stops = array_keys( relevanssi_tokenize( implode( ' ', $terms ), $strip_stopwords ) );
		$total_terms         = count( $terms_without_stops );

		if ( isset( $doc_weight ) && count( $doc_weight ) > 0 && ! $no_matches ) {
			arsort( $doc_weight );
			$i = 0;
			foreach ( $doc_weight as $doc => $weight ) {
				if ( count( $doc_terms[ $doc ] ) < $total_terms && 'AND' === $operator ) {
					// AND operator in action: $doc didn't match all terms, so it's discarded.
					continue;
				}
				$status  = relevanssi_get_post_status( $doc );
				$post_ok = true;
				/**
				 * Filters whether the user is allowed to see the post.
				 *
				 * Can this post be included in the search results? This is the hook
				 * you’ll use if you want to add support for a membership plugin, for
				 * example. Based on the post ID, your function needs to return tru
				 *  or false.
				 *
				 * @param boolean $post_ok Can the post be shown in results?
				 * @param int     $doc     The post ID.
				 */
				$post_ok = apply_filters( 'relevanssi_post_ok', $post_ok, $doc );
				if ( $post_ok ) {
					$post_object          = relevanssi_get_multisite_post( $blogid, $doc );
					$post_object->blog_id = $blogid;

					$object_id                  = $blogid . '|' . $doc;
					$hitsbyweight[ $object_id ] = $weight;
					$post_objects[ $object_id ] = $post_object;
				}
			}
		}
		restore_current_blog();
	}

	arsort( $hitsbyweight );
	$i = 0;
	foreach ( $hitsbyweight as $hit => $weight ) {
		$hit                                   = $post_objects[ $hit ];
		$hits[ intval( $i ) ]                  = $hit;
		$hits[ intval( $i ) ]->relevance_score = round( $weight, 2 );
		$i++;
	}

	if ( count( $hits ) < 1 ) {
		if ( 'AND' === $operator && 'on' !== get_option( 'relevanssi_disable_or_fallback' ) ) {
			$or_args             = $multi_args;
			$or_args['operator'] = 'OR';
			$return              = relevanssi_search_multi( $or_args );
			$hits                = $return['hits'];
			$body_matches        = $return['body_matches'];
			$title_matches       = $return['title_matches'];
			$tag_matches         = $return['tag_matches'];
			$comment_matches     = $return['comment_matches'];
			$scores              = $return['scores'];
			$term_hits           = $return['term_hits'];
			$query               = $return['query'];
			$link_matches        = $return['link_matches'];
		}
	}

	global $wp;
	$default_order = get_option( 'relevanssi_default_orderby', 'relevance' );
	if ( empty( $orderby ) ) {
		$orderby = $default_order;
	}

	if ( is_array( $orderby ) ) {
		/**
		 * Filters the orderby parameter before Relevanssi sorts posts.
		 *
		 * @param array|string $orderby The orderby parameter, accepts both string
		 * and array format
		 */
		$orderby = apply_filters( 'relevanssi_orderby', $orderby );
		relevanssi_object_sort( $hits, $orderby, $meta_query );
	} else {
		if ( empty( $order ) ) {
			$order = 'desc';
		}

		$order                 = strtolower( $order );
		$order_accepted_values = array( 'asc', 'desc' );
		if ( ! in_array( $order, $order_accepted_values, true ) ) {
			$order = 'desc';
		}
		/**
		 * This filter is documented in premium/search.php.
		 */
		$orderby = apply_filters( 'relevanssi_orderby', $orderby );

		/**
		 * Filters the order parameter before Relevanssi sorts posts.
		 *
		 * @param string $order The order parameter, either 'asc' or 'desc'.
		 * Default 'desc'.
		 */
		$order = apply_filters( 'relevanssi_order', $order );

		if ( 'relevance' !== $orderby ) {
			// Results are by default sorted by relevance, so no need to sort for that.
			$orderby_array = array( $orderby => $order );
			relevanssi_object_sort( $hits, $orderby_array, $meta_query );
		}
	}

	$return = array(
		'hits'            => $hits,
		'body_matches'    => $body_matches,
		'title_matches'   => $title_matches,
		'tag_matches'     => $tag_matches,
		'comment_matches' => $comment_matches,
		'scores'          => $scores,
		'term_hits'       => $term_hits,
		'query'           => $q,
		'link_matches'    => $link_matches,
	);

	return $return;
}
