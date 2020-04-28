<?php
/**
 * /premium/related.php
 *
 * A related posts feature.
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 * @license https://wordpress.org/about/gpl/ GNU General Public License
 * @see     https://www.relevanssi.com/
 */

/**
 * Adds the the_content filter for related posts.
 *
 * If related posts are enabled and auto-append option is in use, adds the_content
 * filter. This function is called from relevanssi_premium_init().
 */
function relevanssi_related_init() {
	$related_posts_settings = get_option( 'relevanssi_related_settings', relevanssi_related_default_settings() );
	if ( isset( $related_posts_settings['enabled'] ) && 'on' === $related_posts_settings['enabled'] ) {
		/**
		 * Filters the priority for Relevanssi the_content filter.
		 *
		 * By default the relevanssi_related_posts_the_content_wrapper filter is added to
		 * the_content with priority 99. This filter can be used to alter that value.
		 *
		 * @param int Priority, default 99.
		 */
		add_filter( 'the_content', 'relevanssi_related_posts_the_content_wrapper', apply_filters( 'relevanssi_related_priority', 99 ) );
		add_action( 'transition_post_status', 'relevanssi_flush_caches_on_transition', 99, 3 );
	}
}

/**
 * Returns related posts.
 *
 * @param int     $post_id      The post ID. Default null, in which case global $post
 *                              is used.
 * @param boolean $just_objects If true, don't generate the related posts HTML code.
 *                              The transient will only contain the post objects for
 *                              the related posts. Default false.
 * @param boolean $no_template  If true, don't generate the related posts code. The
 *                              transient will not be generated, only the meta field
 *                              is generated with the post IDs. Used in the metabox
 *                              to avoid problems with running the templates in admin
 *                              context. Default false.
 *
 * @global array $relevanssi_variables The Relevanssi global variables.
 *
 * @return string The related posts HTML element.
 */
function relevanssi_related_posts( $post_id = null, $just_objects = false, $no_template = false ) {
	global $relevanssi_variables, $wpdb;

	if ( ! $post_id ) {
		global $post;
		$post_id = $post->ID;
		if ( ! $post_id ) {
			return '';
		}
	}

	$settings = get_option( 'relevanssi_related_settings', relevanssi_related_default_settings() );

	// For cache control: if the meta field is empty, cache has been flushed.
	$post_ids = get_post_meta( $post_id, '_relevanssi_related_posts', true );
	$related  = get_transient( 'relevanssi_related_posts_' . $post_id );

	if ( 'on' !== $settings['cache_for_admins'] && current_user_can( 'manage_options' ) ) {
		$related = null;
	}

	/**
	 * Disables the caching for related posts. Do not use unless you know exactly
	 * what you are doing.
	 *
	 * @param boolean Set true to disable caching. Default false.
	 */
	if ( apply_filters( 'relevanssi_disable_related_cache', false ) ) {
		$related = null;
	}
	if ( empty( $post_ids ) || empty( $related ) ) {
		$post_types = explode( ',', $settings['post_types'] );

		if ( 'matching_post_type' === $settings['post_types'] ) {
			$post_types = array( get_post_type( $post_id ) );
		}

		/**
		 * Runs before the related posts searches and can be used to adjust the
		 * Relevanssi settings. By default disables query logging.
		 */
		do_action( 'pre_relevanssi_related' );

		$words         = relevanssi_related_generate_keywords( $post_id );
		$related_posts = array();

		$include_ids = get_post_meta( $post_id, '_relevanssi_related_include_ids', true );
		if ( $include_ids ) {
			$related_posts = explode( ',', $include_ids );
		}

		$exclude_ids = get_post_meta( $post_id, '_relevanssi_related_exclude_ids', true );
		if ( $exclude_ids ) {
			$exclude_ids = explode( ',', $exclude_ids );
		}
		if ( ! is_array( $exclude_ids ) ) {
			$exclude_ids = array();
		}
		$exclude_ids[] = $post_id; // Always exclude the current post.

		// These posts are marked as "not related to anything".
		$global_exclude_ids = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_relevanssi_related_not_related' AND meta_value <> ''" );

		$exclude_ids = array_merge( $exclude_ids, $global_exclude_ids );
		$exclude_ids = array_keys( array_flip( $exclude_ids ) );

		$date_query = array();
		if ( isset( $settings['months'] ) && intval( $settings['months'] ) > 0 ) {
			$date_query = array(
				'after' => '-' . $settings['months'] . ' months',
			);
		}
		if ( ! empty( $words ) ) {
			$count = count( $related_posts );
			if ( $settings['number'] - $count > 0 ) {
				$args = array(
					's'              => $words,
					'posts_per_page' => $settings['number'] - $count,
					'post_type'      => $post_types,
					'post__not_in'   => $exclude_ids,
					'fields'         => 'ids',
					'operator'       => 'OR',
					'post_status'    => 'publish',
				);
				if ( $date_query ) {
					$args['date_query'] = $date_query;
				}
				$related_posts_query = new WP_Query();
				/**
				 * Filters the related posts search arguments.
				 *
				 * Notice that the defaults vary depending on which related posts query is done.
				 * Avoid overriding default values; preferably just add extra criteria.
				 *
				 * @param array  The related posts arguments.
				 * @param string Which query is run. Values include "and", "or", "random fill", random".
				 */
				$related_posts_query->parse_query( apply_filters( 'relevanssi_related_args', $args, 'or' ) );
				relevanssi_do_query( $related_posts_query );
				$related_posts = array_merge( $related_posts, $related_posts_query->posts );
			}
		}

		$tax_query = array();
		if ( 'random_cat' === $settings['notenough'] || 'random_cat' === $settings['nothing'] ) {
			$cats      = get_the_category( $post_id );
			$cat_ids   = array_map(
				function( $cat ) {
					return $cat->term_id;
				},
				$cats
			);
			$tax_query = array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'category',
					'field'    => 'term_id',
					'terms'    => $cat_ids,
					'operator' => 'IN',
				),
			);
		}
		$random = in_array( $settings['notenough'], array( 'random', 'random_cat' ), true ) ? true : false;
		if ( $random && ( null === $related_posts || count( $related_posts ) < $settings['number'] ) ) {
			// Not enough results and user wants a random fillup.
			if ( null === $related_posts ) {
				$related_posts = array();
			}
			$count = count( $related_posts );

			$args = array(
				'posts_per_page' => $settings['number'] - $count,
				'post_type'      => $post_types,
				'post__not_in'   => $exclude_ids,
				'fields'         => 'ids',
				'orderby'        => 'rand',
				'post_status'    => 'publish',
			);
			if ( $date_query ) {
				$args['date_query'] = $date_query;
			}
			if ( 'random_cat' === $settings['notenough'] ) {
				$args['tax_query'] = $tax_query;
			}
			/** Documented in premium/related.php */
			$more_related_posts = new WP_Query( apply_filters( 'relevanssi_related_args', $args, 'random fill' ) );
			$related_posts      = array_merge( $related_posts, $more_related_posts->posts );
		}
		$random = in_array( $settings['nothing'], array( 'random', 'random_cat' ), true ) ? true : false;
		if ( empty( $related_posts ) && $random ) {
			$query = new WP_Query();
			// No related posts found, user has requested random posts.
			$args = array(
				'posts_per_page' => $settings['number'],
				'post_type'      => $post_types,
				'post__not_in'   => $exclude_ids,
				'fields'         => 'ids',
				'orderby'        => 'rand',
				'post_status'    => 'publish',
			);
			if ( $date_query ) {
				$args['date_query'] = $date_query;
			}
			if ( 'random_cat' === $settings['nothing'] ) {
				$args['tax_query'] = $tax_query;
			}
			/** Documented in premium/related.php */
			$query->query( apply_filters( 'relevanssi_related_args', $args, 'random' ) );
			$related_posts = $query->posts;
		}

		if ( ! $related_posts ) {
			// For some reason nothing was found.
			$related_posts = array();
		}

		$related_posts_string = implode( ',', $related_posts );
		update_post_meta( $post_id, '_relevanssi_related_posts', $related_posts_string );

		if ( $just_objects ) {
			$related_post_objects = array();
			foreach ( $related_posts as $related_id ) {
				array_push( $related_post_objects, get_post( $related_id ) );
			}
			set_transient( 'relevanssi_related_posts_' . $post_id, $related_post_objects, WEEK_IN_SECONDS * 2 );
		} elseif ( ! $no_template ) {
			$template = locate_template( 'templates/relevanssi-related.php', false );
			if ( ! $template ) {
				$template = $relevanssi_variables['plugin_dir'] . 'premium/templates/relevanssi-related.php';
			}

			ob_start();
			include $template;
			$related = ob_get_clean();

			set_transient( 'relevanssi_related_posts_' . $post_id, $related, WEEK_IN_SECONDS * 2 );
		}
		/**
		 * Runs after the related posts searches and can be used to return the
		 * adjusted Relevanssi settings. By default re-enables query logging.
		 */
		do_action( 'post_relevanssi_related' );
	} else {
		$related .= '<!-- Fetched from cache -->';
	}

	/**
	 * Filters the related posts output.
	 *
	 * @param string The output, ready to be displayed.
	 */
	return apply_filters( 'relevanssi_related_output', $related );
}

/**
 * Echoes out the related posts.
 *
 * @param int $post_id The post ID. Default null, in which case global $post is used.
 */
function relevanssi_the_related_posts( $post_id = null ) {
	echo relevanssi_related_posts( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Returns the related posts for the relevanssi_related_posts shortcode.
 *
 * @param array $atts The shortcode parameters; only one used is post_id, which
 * defaults to null and global $post.
 */
function relevanssi_related_posts_shortcode( $atts ) {
	$post_id = null;
	if ( isset( $atts['post_id'] ) && is_int( $atts['post_id'] ) ) {
		$post_id = $atts['post_id'];
	}
	return relevanssi_related_posts( $post_id );
}
add_shortcode( 'relevanssi_related_posts', 'relevanssi_related_posts_shortcode' );

/**
 * Sets the default settings for related posts.
 *
 * @return array Array containing the default settings.
 */
function relevanssi_related_default_settings() {
	return array(
		'enabled'          => 'off',
		'number'           => 6,
		'nothing'          => 'nothing',
		'notenough'        => 'random',
		'post_types'       => 'post',
		'keyword'          => 'title',
		'append'           => '',
		'cache_for_admins' => 'off',
		'months'           => 0,
		'restrict'         => '',
	);
}

/**
 * Sets the default styles for related posts.
 *
 * @return array Array containing the default styles.
 */
function relevanssi_related_default_styles() {
	return array(
		'width'             => 250,
		'titles'            => 'on',
		'excerpts'          => 'off',
		'thumbnails'        => 'on',
		'default_thumbnail' => '',
	);
}

/**
 * A wrapper function to attach the related posts to the_content.
 *
 * @param string $content The post content.
 *
 * @return string The post content with the related posts appended.
 */
function relevanssi_related_posts_the_content_wrapper( $content ) {
	$settings   = get_option( 'relevanssi_related_settings', relevanssi_related_default_settings() );
	$post_types = explode( ',', $settings['append'] );
	if ( is_singular() && in_the_loop() && in_array( get_post_type(), $post_types, true ) ) {
		global $post;
		if ( 'on' !== get_post_meta( $post->ID, '_relevanssi_related_no_append', true ) ) {
			$content .= relevanssi_related_posts();
		}
	}
	return $content;
}

/**
 * Generates keywords from the post.
 *
 * @param int $post_id The post ID.
 */
function relevanssi_related_generate_keywords( $post_id ) {
	global $wpdb, $relevanssi_variables;

	$settings = get_option( 'relevanssi_related_settings', relevanssi_related_default_settings() );
	$keywords = explode( ',', $settings['keyword'] );
	$restrict = explode( ',', $settings['restrict'] );

	$title_words = array();
	$tag_words   = array();
	$cat_words   = array();
	$tax_words   = array();

	foreach ( $keywords as $keyword ) {
		if ( empty( $keyword ) ) {
			continue;
		}
		if ( 'title' === $keyword ) {
			$title_words = $wpdb->get_col(
				$wpdb->prepare(
					'SELECT term FROM ' . $relevanssi_variables['relevanssi_table'] . ' WHERE doc = %d AND title > 0', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared
					$post_id
				)
			);
		} elseif ( 'post_tag' === $keyword ) {
			$tag_words = $wpdb->get_col(
				$wpdb->prepare(
					'SELECT term FROM ' . $relevanssi_variables['relevanssi_table'] . ' WHERE doc = %d AND tag > 0 ORDER BY tag DESC', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared
					$post_id
				)
			);
		} elseif ( 'category' === $keyword ) {
			$cat_words = $wpdb->get_col(
				$wpdb->prepare(
					'SELECT term FROM ' . $relevanssi_variables['relevanssi_table'] . ' WHERE doc = %d AND category > 0 ORDER BY category DESC', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared
					$post_id
				)
			);
		} else {
			$new_tax_words = $wpdb->get_col(
				$wpdb->prepare(
					'SELECT term FROM ' . $relevanssi_variables['relevanssi_table'] . ' WHERE doc = %d AND taxonomy > 0 AND taxonomy_detail LIKE %s ORDER BY taxonomy DESC', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared
					$post_id,
					'%' . $keyword . '%'
				)
			);
			$new_tax_words = array_map(
				function ( $a ) use ( $keyword ) {
					return array(
						'word'     => $a,
						'taxonomy' => $keyword,
					);
				},
				$new_tax_words
			);
			$tax_words     = array_merge( $tax_words, $new_tax_words );
		}
	}

	$custom_words = get_post_meta( $post_id, '_relevanssi_related_keywords', true );
	if ( $custom_words ) {
		$custom_words = explode( ',', $custom_words );
	} else {
		$custom_words = array();
	}

	$tax_words = array_map(
		function ( $word ) use ( $restrict ) {
			return in_array(
				$word['taxonomy'],
				$restrict,
				true
			) ? '{' . $word['taxonomy'] . ':' . $word['word'] . '}' : $word['word'];
		},
		$tax_words
	);
	if ( in_array( 'post_tag', $restrict, true ) ) {
		$tag_words = array_map(
			function ( $word ) {
				return '{post_tag:' . $word . '}';
			},
			$tag_words
		);
	}
	if ( in_array( 'category', $restrict, true ) ) {
		$cat_words = array_map(
			function ( $word ) {
				return '{category:' . $word . '}';
			},
			$cat_words
		);
	}

	$words = array_merge( $title_words, $tag_words, $cat_words, $tax_words, $custom_words );
	$words = array_keys( array_flip( $words ) );

	/**
	 * Filters the source words for related posts.
	 *
	 * This filter sees the words right before they are fed into Relevanssi to find
	 * the related posts.
	 *
	 * @param string A space-separated list of keywords for related posts.
	 * @param int    The post ID.
	 */
	return apply_filters(
		'relevanssi_related_words',
		implode( ' ', $words ),
		$post_id
	);
}

/**
 * Flushes the related post caches.
 *
 * Deletes all the _relevanssi_related_posts meta fields. This flushes the cache. The
 * actual cache is stored in transients, but we don't have a list of all the transient
 * names and one shouldn't simply remove the transients from the wp_options database
 * table, because it's possible they're not there.
 *
 * So, instead of deleting the transients, Relevanssi deletes the meta fields which
 * contain a list of post IDs (this is helpful for other uses as well), which then
 * forces a cache flush.
 *
 * @global object $wpdb The WordPress database object.
 *
 * @param int $clean_id If specified, only remove meta fields that contain this ID.
 * Default null, which flushes all caches.
 */
function relevanssi_flush_related_cache( $clean_id = null ) {
	global $wpdb;

	if ( is_int( $clean_id ) ) {
		$clean_id = '%' . $clean_id . '%';
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_relevanssi_related_posts' AND meta_value LIKE %s", $clean_id ) );
		// Not perfect, since this will match also rows where post ID contains the
		// wanted ID, but it's an acceptable minor cost for a simple solution.
	} else {
		$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_relevanssi_related_posts'" );
	}
}

/**
 * Flushes the caches when a post is made a draft or deleted.
 *
 * Called from 'transition_post_status' action hook when a post is made into a draft,
 * or deleted. Will flush the related post caches where that post appears.
 *
 * @global object $wpdb The WP database interface.
 *
 * @param string $new_status The new status.
 * @param string $old_status The old status.
 * @param object $post       The post object.
 */
function relevanssi_flush_caches_on_transition( $new_status, $old_status, $post ) {
	global $wpdb;

	// Safety check, for WordPress Editorial Calendar incompatibility.
	if ( ! isset( $post ) || ! isset( $post->ID ) ) {
		return;
	}

	if ( 'publish' !== $new_status ) {
		// The post isn't public anymore.
		relevanssi_flush_related_cache( $post->ID );
	}
}

add_action( 'pre_relevanssi_related', 'relevanssi_pre_related_posts' );
/**
 * Runs before the related posts queries and disables logging.
 */
function relevanssi_pre_related_posts() {
	// We don't want to log these queries.
	add_filter( 'relevanssi_ok_to_log', '__return_false' );
	add_filter( 'pre_option_relevanssi_searchblogs', '__return_false' );
	add_filter( 'pre_option_relevanssi_searchblogs_all', 'relevanssi_return_off' );
}

add_action( 'post_relevanssi_related', 'relevanssi_post_related_posts' );
/**
 * Runs after the related posts queries and enables logging.
 */
function relevanssi_post_related_posts() {
	remove_filter( 'relevanssi_ok_to_log', '__return_false' );
	remove_filter( 'pre_option_relevanssi_searchblogs', '__return_false' );
	remove_filter( 'pre_option_relevanssi_searchblogs_all', 'relevanssi_return_off' );
}
