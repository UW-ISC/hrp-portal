<?php

class CMTT_Abbreviations {

	const TABLENAME = 'glossary_abbreviations';

	public static $_error_option_name	 = '_cm_tt_error_message';
	private static $abbreviationsCache	 = null;
	public static $tableExists			 = null;

	public static function init() {
		add_action( 'admin_notices', array( get_class(), 'addErrorMessages' ) );
		add_action( 'add_meta_boxes', array( get_class(), 'abbreviation_add_meta' ) );
		add_action( 'save_post', array( get_class(), 'abbreviation_save' ) );

		add_action( 'before_delete_post', array( get_class(), 'abbreviation_delete' ) );

		add_action( 'cmtt_do_cleanup', array( __CLASS__, 'doCleanup' ) );
		add_action( 'cmtt_do_cleanup_items_after', array( __CLASS__, 'doCleanupItems' ) );

		add_action( 'cmtt_glossary_doing_search', array( __CLASS__, 'addSearchFilters' ), 10, 2 );
		add_filter( 'cmtt_glossary_index_query_args', array( __CLASS__, 'addAbbrevsArgs' ), 10, 2 );
	}

	public static function addSearchFilters( $args, $shortcodeAtts ) {
		$hideAbbreviations = !empty( $shortcodeAtts[ 'hide_abbrevs' ] );
		if ( !$hideAbbreviations ) {
            add_filter( 'cmtt_search_where_arr', array( __CLASS__, 'addWhereFilter' ), 10, 3 );
		}
	}

	public static function addWhereFilter( $whereArr, $term, $wp_query = null ) {
		global $wpdb;

		$theKey = '';
        $exact    = $wp_query->get( 'exact' );
        $exactAdd = $exact ? '' : '%';

		if ( !empty( $wp_query ) && !empty( $wp_query->meta_query ) ) {
			$metaQueryClauses = $wp_query->meta_query->get_clauses();
			if ( !empty( $metaQueryClauses ) ) {
				foreach ( $wp_query->meta_query->get_clauses() as $key => $clauseArr ) {
					if ( 'cmtt_abbreviation' == $clauseArr[ 'key' ] ) {
						$theKey = $key;
						break;
					}
				}
			}
		}
		if ( !empty( $theKey ) ) {
            $whereArr[] = $theKey . '.meta_value LIKE "' . $exactAdd . $term . $exactAdd . '"';
		}
		return $whereArr;
	}

	/**
	 * Adds the abbreviation to arguments list
	 *
	 * @param type $args
	 * @param type $shortcodeAtts
	 * @return array
	 */
	public static function addAbbrevsArgs( $args, $shortcodeAtts ) {

		if ( !empty( $shortcodeAtts[ 'search_term' ] ) ) {
			$metaQueryArgs = array(
				array(
					'relation' => 'OR',
					array(
						'key' => 'cmtt_abbreviation',
					),
					array(
						'key'		 => 'cmtt_abbreviation',
						'compare'	 => 'NOT EXISTS'
					)
				)
			);

			if ( isset( $args[ 'meta_query' ] ) ) {
				$args[ 'meta_query' ][] = $metaQueryArgs;
			} else {
				$args[ 'meta_query' ] = $metaQueryArgs;
			}
		}
		return $args;
	}

	/**
	 * Remove all abbreviation
	 */
	public static function doCleanupItems() {
		self::setAllAbbreviations( array() );
	}

	public static function doCleanup() {
		self::flushAbbreviationDb();
	}

	public static function flushAbbreviationDb() {
		global $wpdb;
		$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . self::TABLENAME );
	}

	public static function abbreviation_add_meta() {
		add_meta_box( 'glossary_abbreviation', 'Abbreviation', array( get_class(), 'showMetaBox' ), 'glossary', 'side' );
	}

	public static function abbreviation_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( !isset( $_POST[ 'abbreviation_noncename' ] ) || !wp_verify_nonce( $_POST[ 'abbreviation_noncename' ], plugin_basename( __FILE__ ) ) ) {
			return;
		}
		if ( $_POST[ 'post_type' ] != 'glossary' || !current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		$abbreviation = trim( esc_sql( $_POST[ 'glossary_abbreviation' ] ) );

		/*
		 * Set the abbreviation if it's not empty and if it's not an abbreviation of another glossary item or a glossary item itself
		 */
		self::setAbbreviation( $post_id, $abbreviation );
	}

	public static function abbreviation_delete( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( !isset( $_POST[ 'abbreviation_noncename' ] ) || !wp_verify_nonce( $_POST[ 'abbreviation_noncename' ], plugin_basename( __FILE__ ) ) ) {
			return;
		}
		if ( $_POST[ 'post_type' ] != 'glossary' || !current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		self::setAbbreviation( $post_id, '' );
		delete_post_meta( $post_id, 'cmtt_abbreviation' );
	}

//	public static function setAbbreviation( $id, $abbreviation = '', $viewable = true ) {
//		global $wpdb;
//		$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . self::TABLENAME . ' WHERE `glossaryId`=' . $id . ' AND viewable=' . (int) $viewable );
//		if ( $abbreviation ) {
//			$wpdb->insert( $wpdb->prefix . self::TABLENAME, array( 'glossaryId' => $id, 'abbreviation' => $abbreviation, 'viewable' => (int) $viewable ) );
//		}
//	}

	public static function abbreviationsToArray( $abbreviations ) {
		if ( !is_array( $abbreviations ) ) {
			$abbreviationsArr = str_getcsv( $abbreviations );
		} else {
			$abbreviationsArr = $abbreviations;
		}
		$abbreviationsArr = is_array( $abbreviationsArr ) ? array_map( 'trim', array_filter( $abbreviationsArr ) ) : array();
		return (array) $abbreviationsArr;
	}

	private static function _wrapWithQuoteIfNeeded( &$val ) {
		if ( strpos( $val, ',' ) !== FALSE ) {
			$val = '"' . $val . '"';
		}
	}

	public static function setAbbreviation( $id, $abbreviation = '' ) {
		$messages = '';

		$abbreviationsArr = self::abbreviationsToArray( $abbreviation );
		if ( !empty( $abbreviationsArr ) ) {
			$abbreviationsArr = array_slice( $abbreviationsArr, 0, 1 );
		}

		/*
		 * TODO: TEMP - remove after testing
		 */
//			self::setAllAbbreviations( array() );

		/*
		 * Take all of the abbreviations
		 */
		$allAbbreviationsArr = self::getAllAbbreviations();
		if ( !is_array( $allAbbreviationsArr ) ) {
			$allAbbreviationsArr = array();
		}

		/*
		 * Remove the currently saved abbreviations from the list
		 */
		$currentAbbreviations			 = self::getAbbreviation( $id );
		$currentAbbreviationsArr		 = self::abbreviationsToArray( $currentAbbreviations );
		$otherExistingAbbreviationsArr	 = array_diff( $allAbbreviationsArr, $currentAbbreviationsArr );

		/*
		 * Check if we have some duplicates in the remaining list
		 */
		$existingAbbreviations = array_intersect( $abbreviationsArr, $otherExistingAbbreviationsArr );
		if ( $existingAbbreviations ) {

			$falseConflictingAbbreviations = array();
			foreach ( $existingAbbreviations as $conflictingAbbreviation ) {
				$post = self::checkIfPostForAbbreviationExists( $conflictingAbbreviation );
				/*
				 * Post was deleted - abbreviation is not truly conflicting
				 */
				if ( empty( $post ) ) {
					$falseConflictingAbbreviations[] = $conflictingAbbreviation;
				}
			}
			$existingAbbreviations		 = array_diff( $existingAbbreviations, $falseConflictingAbbreviations );
			$nonConflictingAbbreviations = array_diff( $abbreviationsArr, $existingAbbreviations );

			if ( !empty( $existingAbbreviations ) ) {
				$messages .= sprintf( __('Abbreviation(s): "%s" is already used for one of other terms!', 'cm-tooltip-glossary') .' <br/>', implode( ',', $existingAbbreviations ) );
			}

			$nonConflictingAbbreviations = array_diff( $abbreviationsArr, $existingAbbreviations );
			array_walk( $nonConflictingAbbreviations, array( __CLASS__, '_wrapWithQuoteIfNeeded' ) );
			$abbreviation				 = implode( ',', $nonConflictingAbbreviations );
			$abbreviationsArr			 = self::abbreviationsToArray( $abbreviation );
		}

		/*
		 * Save the new complete list of abbreviations
		 */
		$newAllAbbreviations = array_unique( array_merge( $otherExistingAbbreviationsArr, $abbreviationsArr ) );
		self::setAllAbbreviations( $newAllAbbreviations );

		update_post_meta( $id, 'cmtt_abbreviations', $abbreviation );

		if ( $messages ) {
			update_option( self::$_error_option_name, $messages );
		}
	}

	public static function checkIfPostForAbbreviationExists( $abbreviation ) {
		$args = array(
			'post_type' => 'glossary'
		);

		$metaQueryArgs = array(
			array(
				array(
					'key'		 => 'cmtt_abbreviations',
					'compare'	 => 'LIKE',
					'value'		 => $abbreviation
				),
			)
		);

		$args[ 'meta_query' ] = $metaQueryArgs;

		$query	 = new WP_Query( $args );
		$post	 = $query->get_posts();
		return $post;
	}

	/**
	 * Temporary function to be removed in the future
	 *
	 * @global type $wpdb
	 * @param type $id
	 * @deprecated since version 3.2.3
	 */
	public static function _importOldAbbreviations( $id ) {
		global $wpdb;
		$abbreviationsArr	 = array();
		static $_imported	 = array();

		/*
		 * Avoid the endless loop
		 */
		if ( !empty( $_imported[ $id ] ) ) {
			return;
		}

		$_imported[ $id ] = 1;

		self::_fillAbbreviationsCache();
		if ( !is_array( self::$abbreviationsCache ) ) {
			return;
		}

		foreach ( self::$abbreviationsCache as $abbreviation ) {
			if ( $abbreviation->glossaryId == $id ) {
				$abbreviationsArr[] = $abbreviation->abbreviation;
			}
		}

		/*
		 * Something's found in the cache
		 */
		if ( !empty( $abbreviationsArr ) ) {
			/*
			 * Save in the new table
			 */
			$abbreviations = implode( ',', $abbreviationsArr );
			self::setAbbreviation( $id, $abbreviations );

			/*
			 * Remove the synomyms from old table
			 */
			$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . self::TABLENAME . ' WHERE glossaryId=' . $id );

			foreach ( self::$abbreviationsCache as $key => $abbreviation ) {
				if ( $abbreviation->glossaryId == $id ) {
					unset( self::$abbreviationsCache[ $key ] );
				}
			}
		}
	}

	public static function getAbbreviation( $id ) {
		self::_importOldAbbreviations( $id );
		$abbreviations = CMTT_Pro::_get_meta( 'cmtt_abbreviations', $id );
		return $abbreviations;
	}

	public static function getAbbreviationArr( $id ) {
		$abbreviationsArr = self::abbreviationsToArray( self::getAbbreviation( $id ) );
		return $abbreviationsArr;
	}

	/**
	 * Return the list of all abbreviation
	 * @return type
	 */
	public static function getAllAbbreviations() {
		$result = get_option( 'cmtt_all_abbreviation' );
		return $result;
	}

	/**
	 * Update the list of all abbreviation
	 */
	public static function setAllAbbreviations( $abbreviation ) {
		$result = update_option( 'cmtt_all_abbreviation', $abbreviation );
		return $result;
	}

	public static function showMetaBox( $post ) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'abbreviation_noncename' );
		$abbreviation = self::getAbbreviation( $post->ID )
		?>
		Each term can have <strong>only One</strong> abbreviation!
		<div class="cm-showhide">
			<h5 class="cm-showhide-handle">More info &rArr;</h5>
			<div class="cm-showhide-content">
				<i>
					Remember, that one abbreviations cannot be connected to more than one glossary term.
					Plugin will look and parse the code looking for the abbreviation and show the link to the current term page if it finds any.
					Also the abbreviation will be displayed at the term page in the square brackets next to the term itself.
					On the glossary index page the abbreviation will be listed separately although it will point out to the same term page as the full term.
				</i>
			</div>
		</div>
		<input type="text" name="glossary_abbreviation" style="width:100%" value="<?php echo esc_attr( $abbreviation ); ?>">
		<?php
	}

	/**
	 * Generic function to show a message to the user using WP's
	 * standard CSS classes to make use of the already-defined
	 * message colour scheme.
	 *
	 * @param $message The message you want to tell the user.
	 * @param $errormsg If true, the message is an error, so use
	 * the red message style. If false, the message is a status
	 * message, so use the yellow information message style.
	 */
	public static function addErrorMessages() {
		$messages = get_option( self::$_error_option_name );
		if ( $messages ) {
			update_option( self::$_error_option_name, FALSE );
			echo '<div id="message" class="error">';
			echo "<p><strong>" . $messages . "</strong></p></div>";
		}
	}

	private static function _fillAbbreviationsCache() {
		$oldTableExists = self::_checkIfTableExists();
		if ( $oldTableExists && self::$abbreviationsCache === null ) {
			global $wpdb;
			$sql	 = "SELECT * FROM " . $wpdb->prefix . self::TABLENAME;
			$result	 = $wpdb->get_results( $sql, OBJECT_K );

			self::$abbreviationsCache = $result;
		}
	}

	private static function _checkIfTableExists() {
		global $wpdb;

		if ( null !== self::$tableExists ) {
			return self::$tableExists;
		}

		self::$tableExists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}" . self::TABLENAME . "'" ) == $wpdb->prefix . self::TABLENAME;
		return self::$tableExists;
	}

}
