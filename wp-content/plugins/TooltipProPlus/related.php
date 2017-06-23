<?php

class CMTT_Related {

	const TABLENAME = 'glossary_related';

	public static $tableExists = false;

	public static function init() {
		add_action( 'save_post', array( __CLASS__, 'triggerOnSave' ) );
		add_action( 'cmtt_do_cleanup', array( __CLASS__, 'doCleanup' ) );
		add_action( 'cmtt_do_activate', array( __CLASS__, 'install' ) );
		add_filter( 'cron_schedules', array( get_class(), 'cronAddIntervals' ) );
		add_action( 'admin_init', array( get_class(), 'reschedule' ) );
	}

	public static function install() {
		global $wpdb;
		$sql = "CREATE TABLE {$wpdb->prefix}" . self::TABLENAME . " (
            glossaryId INTEGER UNSIGNED NOT NULL,
            articleId VARCHAR(145) NOT NULL,
            PRIMARY KEY  (articleId,glossaryId),
            KEY glossaryId (glossaryId)
          );";

		include_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta( $sql );
		wp_schedule_event( current_time( 'timestamp' ), 'daily', 'glossary_daily_event' );
	}

	public static function checkIfTableExists() {
		global $wpdb;

		if ( !empty( self::$tableExists ) ) {
			return self::$tableExists;
		}

		if ( !$wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}" . self::TABLENAME . "'" ) == $wpdb->prefix . self::TABLENAME ) {
			self::install();
		}
		self::$tableExists = true;
		return self::$tableExists;
	}

	public static function doCleanup() {
		self::flushDb();
	}

	public static function flushDb() {
		global $wpdb;
		$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . self::TABLENAME );
	}

	public static function cronAddIntervals( $schedules ) {
		// add a 'weekly' interval
		$schedules[ 'weekly' ]	 = array(
			'interval'	 => 604800,
			'display'	 => __( 'Once Weekly', 'cm-tooltip-glossary' )
		);
		$schedules[ 'monthly' ]	 = array(
			'interval'	 => 2635200,
			'display'	 => __( 'Once Monthly', 'cm-tooltip-glossary' )
		);
		return $schedules;
	}

	public static function reschedule() {
		$possibleIntervals = array_keys( wp_get_schedules() );

		$newScheduleHour	 = filter_input( INPUT_POST, 'cmtt_glossary_relatedCronHour' );
		$newScheduleInterval = filter_input( INPUT_POST, 'cmtt_glossary_relatedCronInterval' );

		if ( $newScheduleHour !== NULL && $newScheduleInterval !== NULL ) {
			wp_clear_scheduled_hook( 'glossary_daily_event' );

			if ( $newScheduleInterval == 'none' ) {
				return;
			}

			if ( !in_array( $newScheduleInterval, $possibleIntervals ) ) {
				$newScheduleInterval = 'daily';
			}

			$time = strtotime( $newScheduleHour );
			if ( $time === FALSE ) {
				$time = current_time( 'timestamp' );
			}

			wp_schedule_event( $time, $newScheduleInterval, 'glossary_daily_event' );
		}
	}

	public static function updateArticleTerms( $id, $content ) {
		global $templatesArr, $wpdb;

		$templatesArr = array();
		CMTT_Pro::cmtt_glossary_parse( $content, true );

		if ( !empty( $templatesArr ) ) {
			$glossaryIds = array_keys( $templatesArr );

			$wpdb->query( "DELETE FROM " . $wpdb->prefix . self::TABLENAME . " WHERE articleId=" . $id );
			foreach ( $glossaryIds as $glossaryId ) {
				if ( $glossaryId != $id ) {
					$wpdb->insert( $wpdb->prefix . self::TABLENAME, array( 'articleId' => $id, 'glossaryId' => $glossaryId ), array( '%d', '%d' ) );
				}
			}
		}
	}

	public static function getRemainingArticlesCount() {
		$indexedIds = get_option( 'cmtt_glossary_relatedArticlesIndexedIds', array() );
		if ( !is_array( $indexedIds ) ) {
			$indexedIds = array();
		}
		$allArticles = (int) get_option( 'cmtt_glossary_relatedArticlesCrawlItems', 0 );

		/*
		 * Count the remaining items
		 */
		$remainingItemsToCrawl	 = max( 0, $allArticles - count( $indexedIds ) );
		$result					 = sprintf( __( 'Remaining articles to crawl: %d/%d', 'cm-tooltip-glossary' ), $remainingItemsToCrawl, $allArticles );

		return $result;
	}

	public static function showContinueButton() {
		$indexedIds = get_option( 'cmtt_glossary_relatedArticlesIndexedIds', array() );
		if ( !is_array( $indexedIds ) ) {
			$indexedIds = array();
		}
		$allArticles			 = (int) get_option( 'cmtt_glossary_relatedArticlesCrawlItems', 0 );
		$remainingItemsToCrawl	 = max( 0, $allArticles - count( $indexedIds ) );

		return $remainingItemsToCrawl > 0;
	}

	public static function crawlArticles( $restart = false ) {
		global $wpdb, $post;

		if ( function_exists( 'wp_suspend_cache_addition' ) ) {
			wp_suspend_cache_addition( true );
		}

		$disabled = explode( ',', ini_get( 'disable_functions' ) );
		if ( !in_array( 'set_tim_limit', $disabled ) ) {
			set_time_limit( 0 );
		}

		if ( $restart ) {
			update_option( 'cmtt_glossary_relatedArticlesIndexedIds', array() );
		}

		$chunkSize	 = (int) get_option( 'cmtt_glossary_relatedArticlesCrawlChunkSize', 500 );
		$indexedIds	 = get_option( 'cmtt_glossary_relatedArticlesIndexedIds', array() );

		/*
		 * Types of the posts to crawl
		 */
		$types = get_option( 'cmtt_glossary_showRelatedArticlesPostTypesArr', array() );
		if ( !is_array( $types ) ) {
			$types			 = array();
			$allArticleIds	 = array();
		} else {
			if ( !is_array( $indexedIds ) ) {
				$indexedIds = array();
			}

			if ( $chunkSize <= 0 || $chunkSize > 1000 ) {
				$chunkSize = 1000;
			}

			self::checkIfTableExists();

			$allArticlesArgs = array(
				'post_type'				 => $types,
				'post_status'			 => 'publish',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'suppress_filters'		 => false,
				'nopaging'				 => true,
				'numberposts'			 => -1,
				'fields'				 => 'ids'
			);
			$q				 = new WP_Query( $allArticlesArgs );
			$allArticleIds	 = $q->get_posts();
		}

		if ( $restart ) {
			/*
			 * Count the articles to crawl
			 */
			$articleCount = count( $allArticleIds );
			/*
			 * Remove indexed articles
			 */
			$wpdb->query( "TRUNCATE " . $wpdb->prefix . self::TABLENAME );
			/*
			 * Update the items to crawl
			 */
			update_option( 'cmtt_glossary_relatedArticlesCrawlItems', $articleCount );
		}

		/*
		 * Get the $currentChunk for parsing
		 */
		$remainingArticles	 = array_diff( $allArticleIds, $indexedIds );
		$currentChunk		 = array_slice( $remainingArticles, 0, $chunkSize );

		if ( !empty( $allArticleIds ) && !empty( $currentChunk ) ) {
			/*
			 * IDEA: Count all posts, divide into chunks, parse one chunk at a time.
			 *
			 * IEAD2: If start - count posts and parse first chunk, save the IDS
			 * If continue - count posts and diff with the saved IDS, parse the first chunk of the rest
			 */
			$articlesChunkArgs	 = array(
				'post__in'				 => $currentChunk,
				'post_type'				 => $types,
				'post_status'			 => 'publish',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'suppress_filters'		 => false,
				'nopaging'				 => true,
			);
			$q					 = new WP_Query( $articlesChunkArgs );
			$articles			 = $q->get_posts();

			foreach ( $articles as $article ) {
				$post = $article;
				self::updateArticleTerms( $article->ID, $article->post_content );
			}
		}

		if ( function_exists( 'wp_suspend_cache_addition' ) ) {
			wp_suspend_cache_addition( true );
		}

		/*
		 * Update the indexed ids array
		 */
		$indexedIdsUpdated = array_merge( $indexedIds, $currentChunk );

		/*
		 * Update the chunk
		 */
		update_option( 'cmtt_glossary_relatedArticlesIndexedIds', $indexedIdsUpdated );
	}

	public static function triggerOnSave( $post_id ) {
		self::checkIfTableExists();
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		$post		 = get_post( $post_id );
		$postTypes	 = get_option( 'cmtt_glossary_showRelatedArticlesPostTypesArr', array() );
		if ( (is_array( $postTypes ) && !in_array( $post->post_type, $postTypes )) || !current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( $post->post_status == 'publish' ) {
			self::updateArticleTerms( $post_id, $post->post_content );
		} else {
			global $wpdb;
			/*
			 * Clear the related terms
			 */
			$wpdb->query( "DELETE FROM " . $wpdb->prefix . self::TABLENAME . " WHERE articleId=" . $post_id );
		}
	}

	public static function getRelatedArticles( $glossaryId, $limit = 5, $type = 'all' ) {
		global $wpdb;
		$where = '';

		if ( $type == 'glossary' ) {
			$where = 'WHERE p.post_type=\'glossary\'';
		} elseif ( $type == 'others' ) {
			$where = 'WHERE p.post_type<>\'glossary\'';
		}
		$order	 = get_option( 'cmtt_glossary_relatedArticlesOrder', 'menu_order' );
		$sql	 = $wpdb->prepare( "SELECT p.ID, p.post_title, p.post_type FROM {$wpdb->prefix}" . self::TABLENAME . " g JOIN {$wpdb->posts} p ON g.articleId=p.ID AND g.glossaryId=%d " . $where . " ORDER BY " . $order . " LIMIT %d", $glossaryId, $limit );
		$results = $wpdb->get_results( $sql );

		foreach ( $results as &$result ) {
			$result->url = get_permalink( $result->ID );
		}
		return $results;
	}

	public static function getCustomRelatedArticles( $glossaryId ) {
		$results		 = array();
		$glossary_cra	 = get_post_meta( $glossaryId, '_glossary_related_article', true );
		if ( !empty( $glossary_cra ) && is_array( $glossary_cra ) ) {
			foreach ( $glossary_cra as $gc ) {
				if ( empty( $gc ) || !is_array($gc) || empty($gc[ 'name' ]) || empty($gc[ 'url' ]) ) {
					continue;
				}
				$current_row			 = new stdClass;
				$current_row->ID		 = 1;
				$current_row->post_title = $gc[ 'name' ];
				$current_row->post_type	 = 'custom_related_article';
				$current_row->url		 = $gc[ 'url' ];
				$results[]				 = $current_row;
			}
		}
		return $results;
	}

	public static function renderRelatedArticles( $glossaryId, $limitArticles = 5, $limitGlossary = 5, $heading = false ) {
		$html				 = '';
		$basicArticlesType	 = 'all';

		$disableRelatedArticlesForThisTerm	 = (bool) get_post_meta( $glossaryId, '_cmtt_disable_related_articles_for_term', true );
		$showRelatedArticles				 = (bool) get_option( 'cmtt_glossary_showRelatedArticles' );
		$showRelatedCustomArticles			 = (bool) get_option( 'cmtt_glossary_showCustomRelatedArticles' );

		/*
		 * If terms are disabled for this item specifically, or neither the custom related terms nor automated ones are enabled
		 */
		if ( $disableRelatedArticlesForThisTerm || (!$showRelatedArticles && !$showRelatedCustomArticles) ) {
			return '';
		}

		$basic_articles			 = array();
		$glossaryArticles		 = array();
		$custom_related_articles = array();

		if ( $showRelatedArticles ) {
			/*
			 * Note: The option name is wrong, but the variable and labels are right
			 */
			$showRelatedArticlesAndGlossarySeparately = get_option( 'cmtt_glossary_showRelatedArticlesMerged' );

			if ( $showRelatedArticlesAndGlossarySeparately == 1 ) {
				$basicArticlesType	 = 'others';
				$glossaryArticles	 = self::getRelatedArticles( $glossaryId, $limitGlossary, 'glossary' );
			}

			$basic_articles = self::getRelatedArticles( $glossaryId, $limitArticles, $basicArticlesType );
		}

		if ( $showRelatedCustomArticles ) {
			$custom_related_articles = self::getCustomRelatedArticles( $glossaryId );
		}

		// Retrieve custom related articles and merge them in from of auto-generated ones
		$articles	 = array_merge( $custom_related_articles, $basic_articles );
		/*
		 * Changed from 'h4' to 'div' to comply with accessibility standards
		 */
		$tag		 = 'div';
		if ( count( $articles ) > 0 ) {
			$html .= '<div class="cmtt_related_articles_wrapper">';
			$html .= '<' . $tag . ' class="cmtt_related_title cmtt_related_articles_title">' . __( get_option( 'cmtt_glossary_showRelatedArticlesTitle' ), 'cm-tooltip-glossary' ) . ' </' . $tag . '>';
			$html .= '<ul class="cmtt_related">';
			foreach ( $articles as $article ) {
				$title = $article->post_title;
				if ( get_option( 'cmtt_glossary_relatedArticlesPrefix' ) && $article->post_type == 'glossary' ) {
					$title = get_option( 'cmtt_glossary_relatedArticlesPrefix' ) . ' ' . $title;
				}
				$target = ($article->post_type == 'custom_related_article') ? (get_option( 'cmtt_glossary_customRelatedArticlesNewTab', '1' ) ? 'target="_blank"' : '') : (get_option( 'cmtt_glossary_relatedArticlesNewTab', '1' ) ? 'target="_blank"' : '');
				$html.= '<li class="cmtt_related_item"><a href="' . $article->url . '"' . $target . '>' . $title . '</a></li>';
			}
			$html.= '</ul>';
			$html .= '</div>';
		}

		if ( count( $glossaryArticles ) > 0 ) {
			$html .= '<div class="cmtt_related_terms_wrapper">';
			$html .= '<' . $tag . ' class="cmtt_related_title cmtt_related_terms_title">' . __( get_option( 'cmtt_glossary_showRelatedArticlesGlossaryTitle' ), 'cm-tooltip-glossary' ) . ' </' . $tag . '>';
			$html .= '<ul class="cmtt_related">';
			foreach ( $glossaryArticles as $article ) {
				$title = $article->post_title;
				$html.= '<li class="cmtt_related_item"><a href="' . $article->url . '">' . $title . '</a></li>';
			}
			$html.= '</ul>';
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Show the list of related terms under page/post
	 * @param type $terms
	 * @return string
	 * @since 2.3.1
	 */
	public static function renderRelatedTerms( $terms ) {
		$html		 = '';
		$permalinks	 = array();

		if ( is_array( $terms ) && count( $terms ) > 0 ) {
			$html .= '<div class="cmtt_related_linked_terms_wrapper">';
			$html .= '<div class="cmtt_related_title cmtt_related_linked_terms_title">' . __( get_option( 'cmtt_glossary_showRelatedTermsTitle' ), 'cm-tooltip-glossary' ) . '</div>';
			$html .= '<ul class="cmtt_related cmtt_related_terms">';

			foreach ( $terms as $term ) {
				$permalink = get_permalink( $term[ 'post' ]->ID );
				/*
				 * Don't show the same link multiple times for terms with different case
				 */
				if ( in_array( $permalink, $permalinks ) ) {
					continue;
				}
				$permalinks[] = $permalink;

				$title = $term[ 'post' ]->post_title;
				if ( get_option( 'cmtt_glossary_relatedArticlesPrefix' ) ) {
					$title = __( get_option( 'cmtt_glossary_relatedTermsPrefix' ), 'cm-tooltip-glossary' ) . ' ' . $title;
				}
				$html.= '<li><a href="' . $permalink . '">' . $title . '</a></li>';
			}
			$html.= '</ul>';
			$html .= '</div>';
		}

		return $html;
	}

}

add_action( 'glossary_daily_event', array( 'CMTT_Related', 'crawlArticles' ) );
