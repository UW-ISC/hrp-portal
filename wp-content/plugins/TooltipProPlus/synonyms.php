<?php

class CMTT_Synonyms {

    const TABLENAME          = 'glossary_synonyms';
    const ID_COL             = 'glossaryId';
    const SHORTSLUG          = 'cmtt';
    const META_KEY           = 'cmtt_synonyms';
    const META_KEY_INVISIBLE = 'cmtt_variations';
    const OPTION_KEY         = 'cmtt_all_synonyms';
    const OPTION_INVISIBLE   = 'cmtt_all_variations';

    public static $_error_option_name = '_cm_tt_error_message';
    private static $synonymsCache     = null;
    public static $tableExists        = null;
    public static $options            = false;

    public static function init() {

        self::$options = array(
            'post_type'          => 'glossary',
            'synonyms_title'     => get_option( 'cmtt_glossary_addSynonymsTitle' ),
            'error_message'      => get_option( self::$_error_option_name ),
            'add_to_item'        => get_option( 'cmtt_glossary_addSynonymsTooltip' ),
            'invisible_synonyms' => TRUE,
        );

        /*
         * Glossary Index Tooltip Content
         */
        add_filter( self::SHORTSLUG . '_tooltip_content_add', array( __CLASS__, 'outputSynonyms' ), 7, 2 );
        add_action( self::SHORTSLUG . '_glossary_doing_search', array( __CLASS__, 'addSearchFilters' ), 10, 2 );
        add_filter( self::SHORTSLUG . '_glossary_index_query_args', array( __CLASS__, 'addSynonymsArgs' ), 10, 2 );

        add_action( 'admin_notices', array( get_class(), 'addErrorMessages' ) );
        add_action( 'add_meta_boxes', array( get_class(), 'synonyms_add_meta' ) );
        add_action( 'save_post', array( get_class(), 'synonyms_save' ) );

        add_action( 'before_delete_post', array( get_class(), 'synonyms_delete' ) );

        add_action( self::SHORTSLUG . '_do_cleanup', array( __CLASS__, 'doCleanup' ) );
        add_action( self::SHORTSLUG . '_do_cleanup_items_after', array( __CLASS__, 'doCleanupItems' ) );

        add_action( 'wp_ajax_cmtt_get_external_synonym', array( __CLASS__, 'ajaxGetExternalSynonym' ) );
    }

    /**
     * Returns the external synonym
     * @param type $args
     * @param type $shortcodeAtts
     */
    public static function ajaxGetExternalSynonym() {

        $word = filter_input( INPUT_POST, 'word' );
        if ( !empty( $word ) ) {
            $apiUrl = 'http://words.bighugelabs.com/api/2';
            $apiKey = get_option( 'cmtt_glossarySynonymSuggestionsAPI' );
            if ( !empty( $apiKey ) ) {
                $url    = $apiUrl . '/' . $apiKey . '/' . $word . '/json';
                $result = wp_remote_get( $url );
                if ( !is_wp_error( $result ) ) {
                    $response = wp_remote_retrieve_body( $result );
                    if ( $result[ 'response' ][ 'code' ] !== 200 ) {
                        wp_send_json( array( 'error' => $result[ 'response' ][ 'message' ] ) );
                        exit;
                    }
                    echo $response;
                    exit;
                } else {
                    wp_send_json( array( 'error' => 'Wrong API Key!' ) );
                    exit;
                }
            } else {
                wp_send_json( array( 'error' => 'No API Key!' ) );
                exit;
            }
        }
        wp_send_json( null );
        exit;
    }

    /**
     * Ensures the synonyms are correctly displayed on the Glossary Index Page
     * @param type $args
     * @param type $shortcodeAtts
     */
    public static function addSearchFilters( $args, $shortcodeAtts ) {
        $hideSynonyms = !empty( $shortcodeAtts[ 'hide_synonyms' ] );
        if ( !$hideSynonyms ) {
            add_filter( self::SHORTSLUG . '_search_where_arr', array( __CLASS__, 'addWhereFilter' ), 10, 3 );
        }
    }

    public static function addWhereFilter( $whereArr, $term, $wp_query = null ) {
        global $wpdb;

        $theKey   = '';
        $exact    = $wp_query->get( 'exact' );
        $exactAdd = $exact ? '' : '%';

        if ( !empty( $wp_query ) && !empty( $wp_query->meta_query ) ) {
            $metaQueryClauses = $wp_query->meta_query->get_clauses();
            if ( !empty( $metaQueryClauses ) ) {
                foreach ( $wp_query->meta_query->get_clauses() as $key => $clauseArr ) {
                    if ( self::META_KEY == $clauseArr[ 'key' ] ) {
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
     * Adds the synonyms to arguments list
     *
     * @param type $args
     * @param type $shortcodeAtts
     * @return array
     */
    public static function addSynonymsArgs( $args, $shortcodeAtts ) {

        if ( !empty( $shortcodeAtts[ 'search_term' ] ) ) {
            $metaQueryArgs = array(
                array(
                    'relation' => 'OR',
                    array(
                        'key' => self::META_KEY,
                    ),
                    array(
                        'key'     => self::META_KEY,
                        'compare' => 'NOT EXISTS'
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
     * Remove all synonyms
     */
    public static function doCleanupItems() {
        self::setAllSynonyms( array(), true );
        self::setAllSynonyms( array(), false );
    }

    public static function doCleanup() {
        self::flushSynonymsDb();
    }

    public static function flushSynonymsDb() {
        global $wpdb;
        $wpdb->query( 'DELETE FROM ' . $wpdb->prefix . self::TABLENAME );
    }

    /**
     * Outputs the synonyms
     *
     * @param type $content
     * @param type $item
     * @return type
     */
    public static function outputSynonyms( $content, $item ) {
        if ( 1 == self::$options[ 'add_to_item' ] ) {
            $content .= self::renderSynonyms( $item->ID );
        }
        return $content;
    }

    public static function synonyms_add_meta() {
        add_meta_box( self::META_KEY, 'Synonyms', array( get_class(), 'showMetaBox' ), self::$options[ 'post_type' ], 'side' );
        if ( self::$options[ 'invisible_synonyms' ] ) {
            add_meta_box( self::META_KEY_INVISIBLE, 'Variations', array( get_class(), 'showMetaBox2' ), self::$options[ 'post_type' ], 'side' );
        }
    }

    public static function synonyms_save( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( !isset( $_POST[ 'synonyms_noncename' ] ) || !wp_verify_nonce( $_POST[ 'synonyms_noncename' ], plugin_basename( __FILE__ ) ) ) {
            return;
        }
        if ( $_POST[ 'post_type' ] != self::$options[ 'post_type' ] || !current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
            return;
        }

        $synonyms = filter_input( INPUT_POST, 'synonyms' );
        self::setSynonyms( $post_id, $synonyms, true );

        $variations = filter_input( INPUT_POST, 'variations' );
        self::setSynonyms( $post_id, $variations, false );
    }

    public static function synonyms_delete( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( !isset( $_POST[ 'synonyms_noncename' ] ) || !wp_verify_nonce( $_POST[ 'synonyms_noncename' ], plugin_basename( __FILE__ ) ) ) {
            return;
        }
        if ( $_POST[ 'post_type' ] != self::$options[ 'post_type' ] || !current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
            return;
        }
        self::setSynonyms( $post_id, '', TRUE );
        self::setSynonyms( $post_id, '', FALSE );
        delete_post_meta( $post_id, self::META_KEY );
    }

    public static function synonymsToArray( $synonyms ) {
        if ( !is_array( $synonyms ) ) {
            $synonymsArr = str_getcsv( $synonyms );
        } else {
            $synonymsArr = $synonyms;
        }
        $synonymsArr = is_array( $synonymsArr ) ? array_map( 'trim', array_filter( $synonymsArr ) ) : array();
        return (array) $synonymsArr;
    }

    private static function _wrapWithQuoteIfNeeded( &$val ) {
        if ( strpos( $val, ',' ) !== FALSE ) {
            $val = '"' . $val . '"';
        }
    }

    public static function setSynonyms( $id, $synonyms = '', $viewable = true ) {
        $messages = '';

        $synonymsArr = self::synonymsToArray( $synonyms );

        /*
         * TODO: TEMP - remove after testing
         */
//			self::setAllSynonyms( array(), $viewable );

        /*
         * Take all of the synonyms
         */
        $allSynonymsArr = self::getAllSynonyms( $viewable );
        if ( !is_array( $allSynonymsArr ) ) {
            $allSynonymsArr = array();
        }

        /*
         * Remove the currently saved synonyms from the list
         */
        $currentSynonyms          = self::getSynonyms( $id, $viewable );
        $currentSynonymsArr       = self::synonymsToArray( $currentSynonyms );
        $otherExistingSynonymsArr = array_diff( $allSynonymsArr, $currentSynonymsArr );

        /*
         * Check if we have some duplicates in the remaining list
         */
        $existingSynonyms = array_intersect( $synonymsArr, $otherExistingSynonymsArr );
        if ( $existingSynonyms ) {

            $falseConflictingSynonyms = array();
            foreach ( $existingSynonyms as $conflictingSynonym ) {
                $post = self::checkIfPostForSynonymExists( $conflictingSynonym );
                /*
                 * Post was deleted - synonym is not truly conflicting
                 */
                if ( empty( $post ) ) {
                    $falseConflictingSynonyms[] = $conflictingSynonym;
                }
            }
            $existingSynonyms       = array_diff( $existingSynonyms, $falseConflictingSynonyms );
            $nonConflictingSynonyms = array_diff( $synonymsArr, $existingSynonyms );

            if ( !empty( $existingSynonyms ) ) {
                $messages .= sprintf( __( 'Synonym(s): "%s" is already used for one of other terms!', 'cm-tooltip-glossary' ), implode( ',', $existingSynonyms ) );
                $messages .= '<br/>';
            }

            $nonConflictingSynonyms = array_diff( $synonymsArr, $existingSynonyms );
            array_walk( $nonConflictingSynonyms, array( __CLASS__, '_wrapWithQuoteIfNeeded' ) );
            $synonyms               = implode( ',', $nonConflictingSynonyms );
            $synonymsArr            = self::synonymsToArray( $synonyms );
        }

        /*
         * Save the new complete list of synonyms
         */
        $newAllSynonyms = array_unique( array_merge( $otherExistingSynonymsArr, $synonymsArr ) );
        self::setAllSynonyms( $newAllSynonyms, $viewable );

        if ( $viewable ) {
            update_post_meta( $id, self::META_KEY, $synonyms );
        } else {
            update_post_meta( $id, self::META_KEY_INVISIBLE, $synonyms );
        }

        if ( $messages ) {
            update_option( self::$_error_option_name, $messages );
        }
    }

    public static function checkIfPostForSynonymExists( $synonym ) {
        $args = array(
            'post_type' => self::$options[ 'post_type' ]
        );

        $metaQueryArgs = array(
            array(
                array(
                    'key'     => self::META_KEY,
                    'compare' => 'LIKE',
                    'value'   => $synonym
                ),
            )
        );

        $args[ 'meta_query' ] = $metaQueryArgs;

        $query = new WP_Query( $args );
        $post  = $query->get_posts();
        return $post;
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
        $messages = self::$options[ 'error_message' ];
        if ( $messages ) {
            delete_option( self::$_error_option_name );
            echo '<div id="message" class="error">';
            echo "<p><strong>" . $messages . "</strong></p></div>";
        }
    }

    /**
     * Temporary function to be removed in the future
     *
     * @global type $wpdb
     * @param type $id
     * @deprecated since version 3.2.3
     * @param type $viewable
     */
    public static function _importOldSynonyms( $id, $viewable = true ) {
        global $wpdb;
        $synonymsArr      = array();
        static $_imported = array();

        /*
         * Avoid the endless loop
         */
        if ( !empty( $_imported[ $id ][ $viewable ] ) ) {
            return;
        }

        $_imported[ $id ][ $viewable ] = 1;

        self::_fillSynonymsCache();
        if ( !is_array( self::$synonymsCache ) ) {
            return;
        }

        foreach ( self::$synonymsCache as $synonym ) {
            if ( $synonym->{self::ID_COL} == $id && $synonym->viewable == $viewable ) {
                $synonymsArr[] = $synonym->synonym;
            }
        }

        /*
         * Something's found in the cache
         */
        if ( !empty( $synonymsArr ) ) {
            /*
             * Save in the new table
             */
            $synonyms = implode( ',', $synonymsArr );
            self::setSynonyms( $id, $synonyms, $viewable );

            /*
             * Remove the synomyms from old table
             */
            $wpdb->query( 'DELETE FROM ' . $wpdb->prefix . self::TABLENAME . ' WHERE ' . self::ID_COL . '=' . $id );

            foreach ( self::$synonymsCache as $key => $synonym ) {
                if ( $synonym->{self::ID_COL} == $id && $synonym->viewable == $viewable ) {
                    unset( self::$synonymsCache[ $key ] );
                }
            }
        }
    }

    public static function getSynonyms( $id, $viewable = true ) {

        self::_importOldSynonyms( $id, $viewable );

        if ( $viewable ) {
            $synonyms = CMTT_Pro::_get_meta( self::META_KEY, $id );
        } else {
            $synonyms = CMTT_Pro::_get_meta( self::META_KEY_INVISIBLE, $id );
        }
        if ( is_array( $synonyms ) ) {
            if ( count( $synonyms ) ) {
                $synonyms = implode( ',', $synonyms );
            } else {
                $synonyms = '';
            }
        }
        return $synonyms;
    }

    public static function getSynonymsArr( $id, $viewable = true ) {
        $synonymsArr = self::synonymsToArray( self::getSynonyms( $id, $viewable ) );
        return $synonymsArr;
    }

    public static function renderSynonyms( $id, $viewable = true ) {
        $synonyms = self::getSynonyms( $id, $viewable );
        if ( !empty( $synonyms ) ) {
            $title = self::$options[ 'synonyms_title' ];

            $html = '';
            $html .= '<div class=cmtt_synonyms_wrapper>';
            $html .= '<div class=' . self::META_KEY . '_title>' . __( $title, 'cm-tooltip-glossary' ) . ' </div><div class=cmtt_synonyms>' . esc_attr( $synonyms ) . '</div>';
            $html .= '</div>';
            return $html;
        }
    }

    /**
     * Return the list of all synonyms
     * @return array()
     */
    public static function getAllSynonyms( $viewable = true ) {
        if ( $viewable ) {
            $result = get_option( self::OPTION_KEY, array() );
        } else {
            $result = get_option( self::OPTION_INVISIBLE, array() );
        }
        return $result;
    }

    /**
     * Update the list of all synonyms
     */
    public static function setAllSynonyms( $synonyms, $viewable ) {
        if ( $viewable ) {
            $result = update_option( self::OPTION_KEY, $synonyms );
        } else {
            $result = update_option( self::OPTION_INVISIBLE, $synonyms );
        }
    }

    public static function showMetaBox( $post ) {
        wp_nonce_field( plugin_basename( __FILE__ ), 'synonyms_noncename' );
        $synonymsList = self::getSynonyms( $post->ID, true );

        $postTitle = $post->post_title;
        ?>
        Separate synonyms for this term by comma.<br/>
        <div class="cm-showhide">
            <h5 class="cm-showhide-handle">More info &rArr;</h5>
            <div class="cm-showhide-content">
                <i>
                    Remember, that one synonym cannot be connected to more than one item.
                    Plugin will look and parse the code looking for the synonyms and show the link to the current term page if it finds any.
                    Also the synonyms list will appear on the term page. The synonyms will not appear separately in the plugin index page.
                </i>
            </div>
        </div>
        <label>
            Synonyms:
            <textarea name='synonyms' style='width:100%'><?php echo esc_attr( $synonymsList ); ?></textarea>
        </label>

        <div class="cm-showhide">
            <script>
                ( function ( $ ) {
                    $( document ).ready( function () {
                        $( document ).on( 'click', '#cmtt_get_external_synonyms', function () {
                            $( '.cmtt_synonym_suggestions_wrapper' ).hide();
                            var data = {
                                action: 'cmtt_get_external_synonym',
                                word: $( '#cmtt_synonyms_external_for' ).val()
                            };
                            $.post( window.cmtt_data.ajaxurl, data, function ( response ) {
                                if ( ( typeof response !== 'undefined' ) && response !== null && ( typeof response.noun !== 'undefined' ) && response.noun.syn )
                                {
                                    $( '#cmtt_synonyms_external' ).html( response.noun.syn.join( ',' ) );
                                } else
                                {
                                    if ( typeof response.error !== 'undefined' )
                                    {
                                        $( '#cmtt_synonyms_external' ).html( '-' + response.error + '-' );
                                    } else
                                    {
                                        $( '#cmtt_synonyms_external' ).html( '-no results-' );
                                    }
                                }
                                $( '.cmtt_synonym_suggestions_wrapper' ).show();
                            }, 'json' );
                        } );
                    } );
                } )( jQuery );
            </script>
            <h5 class="cm-showhide-handle">Synonym Suggestions &rArr;</h5>
            <div class="cm-showhide-content">
                <i>
                    <strong>How to use this:</strong>
                    <ul>
                        <li>Provide the API Key in Settings: <strong>General Settings -> Metaboxes -> Synonym Suggestions API</strong></li>
                        <li>Write the word you'd like to find the synonyms for in the text box</li>
                        <li>Press "Get Synonym Suggestions" button</li>
                        <li>After a couple of seconds the results will be displayed in the textarea below.</li>
                    </ul>
                </i>
                <br/>
                <input id='cmtt_synonyms_external_for' type='text' value='<?php echo $postTitle; ?>' />
                <div id='cmtt_get_external_synonyms' class='button button-secondary'>Get Synonym Suggestions</div>
                <div class="cmtt_synonym_suggestions_wrapper" style="display:none">
                    <br/>
                    <div>
                        Synonym suggestions:
                    </div>
                    <textarea id='cmtt_synonyms_external' name='cmtt_synonyms_external' style='width:100%'></textarea>
                </div>
            </div>
        </div>
        <?php
    }

    public static function showMetaBox2( $post ) {
        $variationsList = self::getSynonyms( $post->ID, false );
        ?>
        Separate variations (singular/plural) for this term by comma.
        <div class="cm-showhide">
            <h5 class="cm-showhide-handle">More info &rArr;</h5>
            <div class="cm-showhide-content">
                <i>
                    Remember, that one variation cannot be connected to more than one item.
                    Plugin will look and parse the code looking for the variations and show the link to the current term page if it finds any.
                    Variations won't be shown to the end users. Variations will not appear separately in the plugin index page.
                </i>
            </div>
        </div>
        <textarea name='variations' style='width:100%'><?php echo esc_attr( $variationsList ); ?></textarea>
        <?php
    }

    /**
     * Deprecated
     * @global type $wpdb
     */
    private static function _fillSynonymsCache() {
        $oldSynonymsTableExists = self::_checkIfTableExists();
        if ( $oldSynonymsTableExists && self::$synonymsCache === null ) {
            global $wpdb;
            $sql    = "SELECT * FROM " . $wpdb->prefix . self::TABLENAME;
            $result = $wpdb->get_results( $sql );

            self::$synonymsCache = $result;
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
