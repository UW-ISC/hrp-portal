<?php

class CMTT_Glossary_Index {

    public static $shortcodeDisplayed = false;
    protected static $filePath        = '';
    protected static $cssPath         = '';
    protected static $jsPath          = '';
    protected static $preContent      = '';

    /**
     * Adds the hooks
     */
    public static function init() {
        self::$filePath = plugin_dir_url( __FILE__ );
        self::$cssPath  = self::$filePath . 'assets/css/';
        self::$jsPath   = self::$filePath . 'assets/js/';

        /*
         * ACTIONS
         */
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'addScripts' ) );
        add_action( 'cmtt_glossary_shortcode_after', array( __CLASS__, 'addScriptParams' ) );
        add_action( 'cmtt_glossary_index_query_before', array( __CLASS__, 'outputEmbeddedScripts' ), 10, 2 );

        /*
         * FILTERS
         */

        /*
         * Glossary Index Tooltip Content
         */
        add_filter( 'cmtt_glossary_index_tooltip_content', array( __CLASS__, 'getTheTooltipContentBase' ), 10, 2 );
        add_filter( 'cmtt_glossary_index_tooltip_content', array( 'CMTT_Pro', 'cmtt_glossary_parse_strip_shortcodes' ), 20, 2 );
        add_filter( 'cmtt_glossary_index_tooltip_content', array( 'CMTT_Pro', 'cmtt_glossary_filterTooltipContent' ), 30, 2 );

        add_filter( 'cmtt_glossary_index_remove_links_to_terms', array( __CLASS__, 'removeLinksToTerms' ), 10, 2 );
        add_filter( 'cmtt_glossary_index_disable_tooltips', array( __CLASS__, 'disableTooltips' ), 10, 2 );

        add_filter( 'cmtt_glossary_index_pagination', array( __CLASS__, 'outputPagination' ), 10, 3 );

        add_filter( 'cmtt_glossary_index_listnav_content', array( __CLASS__, 'modifyListnav' ), 10, 3 );
        add_filter( 'cmtt_glossary_index_before_listnav_content', array( __CLASS__, 'modifyBeforeListnav' ), 10, 3 );
        add_filter( 'cmtt_index_term_tooltip_permalink', array( __CLASS__, 'modifyTermPermalink' ), 10, 3 );

        add_filter( 'cmtt_glossary_index_after_content', array( __CLASS__, 'wrapInMainContainer' ), 1, 3 );
        if ( get_option( 'cmtt_glossaryShowShareBox' ) == 1 ) {
            add_filter( 'cmtt_glossary_index_after_content', array( 'CMTT_Pro', 'cmtt_glossaryAddShareBox' ), 5, 3 );
        }
        add_filter( 'cmtt_glossary_index_after_content', array( __CLASS__, 'outputAdditionalHTML' ), 5, 3 );
        add_filter( 'cmtt_glossary_index_after_content', array( __CLASS__, 'wrapInStyleContainer' ), 10, 3 );
        add_filter( 'cmtt_glossary_index_after_content', array( __CLASS__, 'addReferalSnippet' ), 50, 3 );

        add_filter( 'cmtt_glossary_index_shortcode_default_atts', array( __CLASS__, 'setupDefaultGlossaryIndexAtts' ), 5 );

        add_filter( 'cmtt_tooltip_script_data', array( __CLASS__, 'tooltipsDisabledForPage' ), 50000 );
        add_filter( 'cmtt_glossary_container_additional_class', array( __CLASS__, 'addShowCountsClass' ) );

        /*
         * SHORTCODES
         */
        add_shortcode( 'glossary', array( __CLASS__, 'glossaryShortcode' ) );
        add_shortcode( 'glossary_search', array( __CLASS__, 'glossarySearchShortcode' ) );
    }

    public static function outputEmbeddedScripts( $args, $shortcodeAtts ) {
        $embeddedMode = get_option( 'cmtt_enableEmbeddedMode', false );
        if ( $embeddedMode ) {
            self::addScripts();
            self::addScriptParams( $shortcodeAtts );
        }
    }

    /**
     * Returns true if the server-side pagination is enabled
     * @return type
     */
    public static function setupDefaultGlossaryIndexAtts( $baseAtts ) {
        $defaultAtts[ 'pagination_position' ] = get_option( 'cmtt_glossaryPaginationPosition', 'bottom' );
        $atts                                 = array_merge( $baseAtts, $defaultAtts );
        return $atts;
    }

    /**
     * Returns true if the server-side pagination is enabled
     * @return type
     */
    public static function isServerSide() {
        return (bool) apply_filters( 'cmtt_is_serverside_pagination', get_option( 'cmtt_glossaryServerSidePagination' ) == 1 );
    }

    /**
     * Function serves the shortcode: [glossary]
     */
    public static function glossaryShortcode( $atts = array() ) {
        global $post;

        if ( !is_array( $atts ) ) {
            $atts = array();
        }

        if ( $post !== null ) {
            $glossaryPageLink = get_page_link( $post );
        } elseif ( !empty( $atts[ 'post_id' ] ) ) {
            $glossaryPageLink = get_permalink( $atts[ 'post_id' ] );
        } else {
            $glossaryPageLink = get_permalink( self::getGlossaryIndexPageId() );
        }

        $default_atts   = apply_filters( 'cmtt_glossary_index_shortcode_default_atts', array(
            'glossary_page_link'   => $glossaryPageLink,
            'exact_search'         => get_option( 'cmtt_index_searchExact' ),
            'only_on_search'       => get_option( 'cmtt_showOnlyOnSearch' ),
            'show_search'          => get_option( 'cmtt_glossary_showSearch', 1 ),
            'only_relevant_cats'   => get_option( 'cmtt_glossary_onlyRelevantCats', 0 ),
            'only_relevant_tags'   => get_option( 'cmtt_glossary_onlyRelevantTags', 0 ),
            'glossary_index_style' => apply_filters( 'cmtt_glossary_index_style', get_option( 'cmtt_glossaryListTiles' ) == '1' ? 'small-tiles' : 'classic'  ),
            'itemspage'            => filter_input( INPUT_GET, 'itemspage' )
        )
        );
        $shortcode_atts = apply_filters( 'cmtt_glossary_index_atts', array_merge( $default_atts, $atts ) );

        /*
         * Filtering to protect against the XSS attacks since 3.5.7
         */
        foreach ( $shortcode_atts as $key => $value ) {
            if ( is_string( $value ) ) {
                $shortcode_atts[ $key ] = filter_var( $value, FILTER_SANITIZE_STRING );
            }
        }

        do_action( 'cmtt_glossary_shortcode_before', $shortcode_atts );

        $output = self::outputGlossaryIndexPage( $shortcode_atts );

        do_action( 'cmtt_glossary_shortcode_after', $shortcode_atts, $atts );

        self::$shortcodeDisplayed = true;

        return $output;
    }

    /**
     * Function serves the shortcode: [glossary_search]
     */
    public static function glossarySearchShortcode( $atts = array() ) {
        global $post;

        if ( !is_array( $atts ) ) {
            $atts = array();
        }

        $default_atts = apply_filters( 'cmtt_glossary_search_shortcode_default_atts', array(
            'glossary_page_link' => get_permalink( self::getGlossaryIndexPageId() ),
        )
        );

        $shortcode_atts = apply_filters( 'cmtt_glossary_search_atts', array_merge( $default_atts, $atts ) );
        do_action( 'cmtt_glossary_search_shortcode_before', $shortcode_atts );
        $output         = self::outputSearch( $shortcode_atts );
        do_action( 'cmtt_glossary_search_shortcode_after', $atts );

        return $output;
    }

    /**
     * Displays the main glossary index
     *
     * @param type $shortcodeAtts
     * @return string $content
     */
    public static function outputSearch( $shortcodeAtts ) {
        global $post;

        $content = '';

        if ( $post === NULL && $shortcodeAtts[ 'post_id' ] ) {
            $post = get_post( $shortcodeAtts[ 'post_id' ] );
        }

        $content .= apply_filters( 'cmtt_glossary_search_before_content', '', $shortcodeAtts );
        $content .= '<form method="post" action="' . esc_attr( $shortcodeAtts[ 'glossary_page_link' ] ) . '">';

        $additionalClass = (!empty( $shortcodeAtts[ 'search_term' ] )) ? 'search' : '';

        $searchLabel       = __( get_option( 'cmtt_glossary_SearchLabel', 'Search:' ), 'cm-tooltip-glossary' );
        $searchPlaceholder = __( get_option( 'cmtt_glossary_SearchPlaceholder', '' ), 'cm-tooltip-glossary' );
        $searchButtonLabel = __( get_option( 'cmtt_glossary_SearchButtonLabel', 'Search' ), 'cm-tooltip-glossary' );
        $searchTerm        = isset( $shortcodeAtts[ 'search_term' ] ) ? $shortcodeAtts[ 'search_term' ] : '';
        $searchHelp        = __( get_option( 'cmtt_glossarySearchHelp', 'The search returns the partial search for the given query from both the term title and description. So it will return the results even if the given query is part of the word in the description.' ), 'cm-tooltip-glossary' );
        ob_start();
        ?>
        <?php if ( !empty( $searchHelp ) ) : ?>
            <div class="cmtt_help glossary-search-helpitem" data-cmtooltip="<?php echo $searchHelp ?>"></div>
        <?php endif; ?>
        <span class="glossary-search-label"><?php echo $searchLabel ?></span>
        <input type="search" value="<?php echo esc_attr( $searchTerm ) ?>" placeholder="<?php echo esc_attr( $searchPlaceholder ); ?>" class="glossary-search-term <?php echo esc_attr( $additionalClass ) ?>" name="search_term" id="glossary-search-term" />
        <input type="submit" value="<?php echo esc_attr( $searchButtonLabel ) ?>" id="glossary-search" class="glossary-search button" />
        <?php
        $content .= ob_get_clean();
        $content .= '</form>';
        $content = apply_filters( 'cmtt_glossary_search_after_content', $content, $shortcodeAtts );

        do_action( 'cmtt_after_glossary_search' );

        return $content;
    }

    /**
     * Function should return the ID of the Glossary Index Page
     * @since 2.7.4
     * @return type
     */
    public static function getGlossaryIndexPageId() {
        $glossaryPageID = apply_filters( 'cmtt_get_glossary_index_page_id', get_option( 'cmtt_glossaryID' ) );
        /*
         * WPML integration
         */
        if ( function_exists( 'icl_object_id' ) && defined( 'ICL_LANGUAGE_CODE' ) ) {
            $glossaryPageID = icl_object_id( $glossaryPageID, 'page', ICL_LANGUAGE_CODE );
        }
        return $glossaryPageID;
    }

    /**
     * Create the actual glossary
     * @param type $content
     * @return string
     */
    public static function lookForShortcode( $content ) {
        $currentPost    = get_post();
        $glossaryPageID = self::getGlossaryIndexPageId();

        $seo = doing_action( 'wpseo_head' );
        if ( $seo ) {
            return $content;
        }

        if ( is_numeric( $glossaryPageID ) && is_page( $glossaryPageID ) && $glossaryPageID > 0 && $currentPost && $currentPost->ID == $glossaryPageID ) {
            if ( !has_shortcode( $currentPost->post_content, 'glossary' ) ) {
                $content = $currentPost->post_content . '[glossary]';
                wp_update_post( array( 'ID' => $glossaryPageID, 'post_content' => $content ) );
            }
        }
        return $content;
    }

    /**
     * Function tries to generate the new Glossary Index Page
     */
    public static function tryGenerateGlossaryIndexPage() {
        $glossaryIndexId = self::getGlossaryIndexPageId();
        if ( $glossaryIndexId == -1 && get_post( $glossaryIndexId ) === null ) {
            $id = wp_insert_post( array(
                'post_author'  => get_current_user_id(),
                'post_status'  => 'publish',
                'post_title'   => 'Glossary',
                'post_type'    => 'page',
                'post_content' => '[glossary]'
            ) );

            if ( is_numeric( $id ) ) {
                update_option( 'cmtt_glossaryID', $id );
            }
        }
    }

    /**
     * Get the base of the Tooltip Content on Glossary Index Page
     * @param type $content
     * @param type $glossary_item
     * @return type
     */
    public static function getTheTooltipContentBase( $content, $glossary_item ) {
        $content = (get_option( 'cmtt_glossaryExcerptHover' ) && $glossary_item->post_excerpt) ? $glossary_item->post_excerpt : $glossary_item->post_content;
        return $content;
    }

    /**
     * Check whether to remove links to term pages from Glossary Index or not
     * @param type $disable
     * @param type $post
     * @return type
     */
    public static function removeLinksToTerms( $disable, $post ) {
        $removeLinksToTerms = get_option( 'cmtt_glossaryListTermLink' ) == 1;
        $linksDisabled      = FALSE;
        if ( !empty( $post ) ) {
            $linksDisabled = (1 == CMTT_Pro::_get_meta( '_glossary_disable_links_for_page', $post->ID ));
        }
        $disable = $linksDisabled || $removeLinksToTerms;
        return $disable;
    }

    /**
     * Check whether to disable the tooltips on Glossary Index page
     * @param type $disable
     * @param type $post
     * @return type
     */
    public static function disableTooltips( $disable, $post ) {
        if ( !empty( $post ) ) {
            $tooltipsDisabledGlobal = get_option( 'cmtt_glossaryTooltip' ) != 1;
            $tooltipsDisabled       = (1 == CMTT_Pro::_get_meta( '_glossary_disable_tooltip_for_page', $post->ID ));

            $disable = $tooltipsDisabled || $tooltipsDisabledGlobal;
        }
        return $disable;
    }

    /**
     * Wrap Glossary Index in styling container
     * @param type $content
     * @param type $glossaryIndexStyle
     * @return type
     */
    public static function outputAdditionalHTML( $content, $glossary_query, $shortcodeAtts ) {
        if ( !defined( 'DOING_AJAX' ) ) {
            $glossaryIndexStyle = $shortcodeAtts[ 'glossary_index_style' ];
            if ( 'sidebar-termpage' === $glossaryIndexStyle ) {
                if ( isset( $shortcodeAtts[ 'term' ] ) ) {
//					$content .= '<div class="glossary-term-content">'.  apply_filters('cmtt_single_glossary_term_definition', '', $glossary_query, $shortcodeAtts).'</div>';
                    $content .= '<div class="glossary-term-content">' . do_shortcode( apply_filters( 'cmtt_single_glossary_term_definition', '[glossary-term term="' . $shortcodeAtts[ 'term' ] . '" run_filter="1"]', $glossary_query, $shortcodeAtts ) ) . '</div>';
                } else {
                    $content .= '<div class="glossary-term-content">' . do_shortcode( apply_filters( 'cmtt_single_glossary_term_definition', 'Select the term to display its content.', $glossary_query, $shortcodeAtts ) ) . '</div>';
                }
            }
        }
        return $content;
    }

    /**
     * Wrap Glossary Index in styling container
     * @param type $content
     * @param type $glossaryIndexStyle
     * @return type
     */
    public static function wrapInStyleContainer( $content, $glossary_query, $shortcodeAtts ) {
        if ( !defined( 'DOING_AJAX' ) ) {
            $glossaryIndexStyle = $shortcodeAtts[ 'glossary_index_style' ];
            if ( $glossaryIndexStyle != 'classic' ) {
                $styles = apply_filters( 'cmtt_glossary_index_style_classes', array(
                    'small-tiles' => 'tiles'
                ) );
                if ( isset( $styles[ $glossaryIndexStyle ] ) ) {
                    $class   = $styles[ $glossaryIndexStyle ];
                    $content = '<div class="cm-glossary ' . $class . '">' . $content . '<div class="clear clearfix cmtt-clearfix"></div></div>';
                }
            }
        }
        return $content;
    }

    /**
     * Wrap Glossary Index in main container
     * @param type $content
     * @param type $glossaryIndexStyle
     * @return type
     */
    public static function addShowCountsClass( $additionalClass ) {
        $showCounts = get_option( 'cmtt_index_showCounts', '1' );
        if ( !$showCounts ) {
            $additionalClass .= 'no-counts';
        }
        return $additionalClass;
    }

    /**
     * Wrap Glossary Index in main container
     * @param type $content
     * @param type $glossaryIndexStyle
     * @return type
     */
    public static function wrapInMainContainer( $content, $glossary_query, $shortcodeAtts ) {
        if ( !defined( 'DOING_AJAX' ) ) {
            $additionalClass = apply_filters( 'cmtt_glossary_container_additional_class', '' );
            $content         = '<div class="glossary-container ' . $additionalClass . '">' . $content . '</div>';
        }
        return $content;
    }

    /**
     * Check whether to disable the tooltips on Glossary Index page
     * @param type $disable
     * @param type $post
     * @return type
     */
    public static function addReferalSnippet( $content, $glossary_query, $shortcodeAtts ) {
        if ( get_option( 'cmtt_glossaryReferral' ) == 1 && get_option( 'cmtt_glossaryAffiliateCode' ) ) {
            $content .= CMTT_Pro::cmtt_getReferralSnippet();
        }
        return $content;
    }

    /**
     * Detects the new letter in Glossary Index Page
     * @staticvar boolean $lastIndexLetter
     * @param type $glossaryItem
     * @param type $title
     * @return boolean
     */
    public static function detectStartNewIndexLetter( $glossaryItem = null, $title = null ) {
        static $lastIndexLetter = false;

        if ( ($glossaryItem && is_object( $glossaryItem ) && isset( $glossaryItem->post_title )) || ($title && is_string( $title )) ) {
            /*
             * In case the former parameter only is sent
             */
            if ( empty( $title ) && !empty( $glossaryItem ) ) {
                $title = $glossaryItem->post_title;
            }

            $newIndexLetter = mb_substr( $title, 0, 1 );

            if ( !(bool) get_option( 'cmtt_index_nonLatinLetters' ) ) {
                $newIndexLetter = remove_accents( $newIndexLetter );
            }

            if ( mb_strtolower( $newIndexLetter ) !== $lastIndexLetter ) {
                $lastIndexLetter = mb_strtolower( $newIndexLetter );
                return $lastIndexLetter;
            }
        }

        return false;
    }

    /**
     * Removes the ListNav when there's server side pagination
     * @param type $content
     * @return string
     */
    public static function removeListnav( $content ) {
        if ( self::isServerSide() ) {
            $content = '';
        }
        return $content;
    }

    /**
     * Removes the ListNav when there's server side pagination
     * @param type $content
     * @return string
     */
    public static function modifyListnav( $content, $shortcodeAtts, $glossaryQuery ) {
        if ( 'sidebar-termpage' === $shortcodeAtts[ 'glossary_index_style' ] ) {
            $content = '';
        }
        return $content;
    }

    /**
     * Removes the ListNav when there's server side pagination
     * @param type $content
     * @return string
     */
    public static function modifyBeforeListnav( $content, $shortcodeAtts, $glossaryQuery ) {
        $content .= '<input type="hidden" class="cmtt-attribute-field" name="glossary_index_style" value="' . esc_attr( $shortcodeAtts[ 'glossary_index_style' ] ) . '">';
        if ( isset( $shortcodeAtts[ 'related' ] ) ) {
            $content .= '<input type="hidden" class="cmtt-attribute-field" name="related" value="' . esc_attr( $shortcodeAtts[ 'related' ] ) . '">';
        }
        if ( 'sidebar-termpage' === $shortcodeAtts[ 'glossary_index_style' ] ) {
//			$content = '';
        }
        return $content;
    }

    /**
     * Removes the ListNav when there's server side pagination
     * @param type $content
     * @return string
     */
    public static function modifyTermPermalink( $permalink, $glossaryItem, $shortcodeAtts ) {
        if ( 'sidebar-termpage' === $shortcodeAtts[ 'glossary_index_style' ] ) {
            $name      = get_post_field( 'post_name', $glossaryItem->ID );
            $permalink = add_query_arg( array( 'term' => $name ) );
        }
        return $permalink;
    }

    /**
     * Displays the main glossary index
     *
     * @param type $shortcodeAtts
     * @return string $content
     */
    public static function outputGlossaryIndexPage( $shortcodeAtts ) {
        global $post;

        $content = '';

        $glossaryIndexContentArr = array();

        if ( $post === NULL && !empty( $shortcodeAtts[ 'post_id' ] ) ) {
            $post = get_post( $shortcodeAtts[ 'post_id' ] );
        }

        /*
         *  Checks whether to show tooltips on main glossary page or not
         */
        $tooltipsDisabled = apply_filters( 'cmtt_glossary_index_disable_tooltips', FALSE, $post );

        /*
         *  Checks whether to show links to glossary pages or not
         */
        $removeLinksToTerms = apply_filters( 'cmtt_glossary_index_remove_links_to_terms', FALSE, $post );

        /*
         * Whether the terms should be hidden
         */
        $hideTerms = !empty( $shortcodeAtts[ 'hide_terms' ] );

        /*
         * Set the display style of Glossary Index Page
         */
        $glossaryIndexStyle = $shortcodeAtts[ 'glossary_index_style' ];

        /*
         * Get the pagination position
         */
        $paginationPosition = $shortcodeAtts[ 'pagination_position' ];

        $args = array(
            'post_type'              => 'glossary',
            'post_status'            => 'publish',
            'orderby'                => 'title',
            'order'                  => 'ASC',
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'suppress_filters'       => false,
            'exact'                  => $shortcodeAtts[ 'exact_search' ]
        );

        if ( self::isServerSide() ) {
            $args[ 'posts_per_page' ] = get_option( 'cmtt_perPage' );

            /*
             * Turn off the pagination if terms are hidden, so we can fill the list with synonyms and abbreviations
             */
            if ( $args[ 'posts_per_page' ] != 0 && !$hideTerms ) {
                $currentPage = isset( $shortcodeAtts[ 'itemspage' ] ) ? $shortcodeAtts[ 'itemspage' ] : 1;
                if ( $currentPage < 1 ) {
                    $currentPage = 1;
                }
                $args[ 'paged' ] = $currentPage;
            } else {
                $args[ 'nopaging' ] = true;
            }
        } else {
            $args[ 'nopaging' ] = true;
        }

        /*
         * Added in 3.3.7 - we need a way to make sure no posts are displayed if the search_term is empty,
         * as we only want to display the Index if the user's searching
         *
         * In 3.5.0 I had to change from -1 to solve the 'fix' in WordPress
         */
        if ( $shortcodeAtts[ 'only_on_search' ] && empty( $shortcodeAtts[ 'search_term' ] ) ) {
            $args[ 'p' ] = PHP_INT_MAX;
        }

        $args = apply_filters( 'cmtt_glossary_index_query_args', $args, $shortcodeAtts );
        do_action( 'cmtt_glossary_index_query_before', $args, $shortcodeAtts );

        $glossary_index = CMTT_Pro::getGlossaryItems( $args );
        $glossary_query = CMTT_Pro::$lastQueryDetails[ 'query' ];

        do_action( 'cmtt_glossary_index_query_after', $glossary_query, $args );

        /*
         * Size of the Glossary Index Letters (defaults to 'small')
         */
        $letterSize          = get_option( 'cmtt_indexLettersSize' );
        $glossary_list_id    = apply_filters( 'cmtt_glossary_index_list_id', 'glossaryList' );
        /*
         * Style links based on option
         */
        $glossary_list_class = apply_filters( 'cmtt_glossary_index_list_class', (get_option( 'cmtt_glossaryDiffLinkClass' ) == 1) ? 'glossaryLinkMain' : 'glossaryLink'  );

        $content .= apply_filters( 'cmtt_glossary_index_before_listnav_content', '', $shortcodeAtts, $glossary_query );

        $listnavContent = '<div id="' . $glossary_list_id . '-nav" class="listNav ' . $letterSize . '" role="tablist">';
        $listnavContent .= apply_filters( 'cmtt_glossary_index_listnav_content_inside', '', $shortcodeAtts, $glossary_query );
        $listnavContent .= '</div>';

        $content .= apply_filters( 'cmtt_glossary_index_listnav_content', $listnavContent, $shortcodeAtts, $glossary_query );

        if ( self::isServerSide() && !isset( $args[ 'nopaging' ] ) && in_array( $paginationPosition, array( 'top', 'both' ) ) ) {
            $content .= apply_filters( 'cmtt_glossary_index_pagination', '', $glossary_query, $shortcodeAtts );
        }

        if ( $glossary_index ) {

            foreach ( $glossary_index as $glossaryItem ) {
                /*
                 *  Check if need to add description/excerpt on tooltip index
                 */
                $glossaryItemDesc = (get_option( 'cmtt_glossaryTooltipDesc' ) == 1) ? '<div class="glossary_itemdesc">' . strip_tags( $glossaryItem->post_content ) . '</div>' : '';
                $glossaryItemDesc = apply_filters( 'cmtt_glossary_index_item_desc', $glossaryItemDesc, $glossaryItem, $glossaryIndexStyle, $shortcodeAtts );

                $permalink = apply_filters( 'cmtt_term_tooltip_permalink', get_permalink( $glossaryItem ), $glossaryItem );

                if ( $removeLinksToTerms ) {
                    $href = '';
                    $tag  = 'span';
                } else {
                    $tag  = 'a';
                    $href = 'href="' . apply_filters( 'cmtt_index_term_tooltip_permalink', $permalink, $glossaryItem, $shortcodeAtts ) . '"';
                }

                $letterSeparatorContent = '';
                $preItemTitleContent    = '';
                $postItemTitleContent   = '';

                $liAdditionalClass = '';
                $thumbnail         = '';

                if ( get_option( 'cmtt_showFeaturedImageThumbnail', FALSE ) && in_array( $glossaryIndexStyle, array( 'classic-excerpt', 'classic-definition' ) ) ) {
                    $thumbnail = get_the_post_thumbnail( $glossaryItem->ID, array( 50, 50 ), array( 'style' => 'margin:1px 5px' ) );
                    if ( !empty( $thumbnail ) ) {
                        $liAdditionalClass = 'cmtt-has-thumbnail';
                    }
                }

                $preItemTitleContent .= '<li class="' . $liAdditionalClass . '">';
                $preItemTitleContent .= $thumbnail;

                /*
                 * Start the internal tag: span or a
                 */
                $additionalClass = apply_filters( 'cmtt_term_tooltip_additional_class', '', $glossaryItem );
                $excludeTT       = CMTT_Pro::_get_meta( '_cmtt_exclude_tooltip', $glossaryItem->ID );
                $preItemTitleContent .= '<' . $tag . ' class="' . $glossary_list_class . ' ' . $additionalClass . '" ' . $href . ' ';

                /*
                 * Add tooltip if needed (general setting enabled and page not excluded from plugin)
                 */
                if ( !$tooltipsDisabled && !$excludeTT ) {
                    $tooltipContent = apply_filters( 'cmtt_glossary_index_tooltip_content', '', $glossaryItem );
                    $tooltipContent = apply_filters( 'cmtt_3rdparty_tooltip_content', $tooltipContent, $glossaryItem, true );
                    $tooltipContent = apply_filters( 'cmtt_tooltip_content_add', $tooltipContent, $glossaryItem );
                    $preItemTitleContent .= 'data-cmtooltip="' . $tooltipContent . '"';
                }
                $preItemTitleContent .= '>';

                /*
                 * Add filter to change the content of what's before the glossary item title on the list
                 */
                $preItemTitleContent = apply_filters( 'cmtt_glossaryPreItemTitleContent_add', $preItemTitleContent, $glossaryItem );

                /*
                 * Insert post title here later on
                 */
                $postItemTitleContent .= '</' . $tag . '>';
                /*
                 * Add description if needed
                 */
                $postItemTitleContent .= $glossaryItemDesc;
                $postItemTitleContent .= '</li>';

                if ( !$hideTerms ) {
                    $glossaryIndexContentArr[ mb_strtolower( $glossaryItem->post_title ) ] = $letterSeparatorContent . $preItemTitleContent . $glossaryItem->post_title . $postItemTitleContent;
                }

                $glossaryIndexContentArr = apply_filters( 'cmtt_glossary_index_content_arr', $glossaryIndexContentArr, $glossaryItem, $preItemTitleContent, $postItemTitleContent, $shortcodeAtts );
            }

            /*
             * Don't need this later
             */
            $glossary_index = NULL;

            $content .= '<ul class="glossaryList" role="tabpanel" id="' . $glossary_list_id . '">';

            if ( extension_loaded( 'intl' ) === true ) {
                $customLocale = get_option( 'cmtt_index_locale', '' );
                $locale       = !empty( $customLocale ) ? $customLocale : get_locale();

                if ( is_object( $collator = collator_create( $locale ) ) === true ) {
                    /*
                     * Add support for natural sorting order
                     */
                    $collator->setAttribute( Collator::NUMERIC_COLLATION, Collator::ON );
                    $glossariIndexContentArrFliped   = array_flip( $glossaryIndexContentArr );
                    $glossaryIndexContentArr         = null;
                    collator_asort( $collator, $glossariIndexContentArrFliped );
                    $glossariIndexContentArrUnFliped = array_flip( $glossariIndexContentArrFliped );
                }
            } else {
                $glossariIndexContentArrUnFliped = $glossaryIndexContentArr;
                uksort( $glossariIndexContentArrUnFliped, array( __CLASS__, 'mb_string_compare' ) );
            }

            $isFirstIndexLetter = true;

            foreach ( $glossariIndexContentArrUnFliped as $key => $value ) {

                /* ML  */
                if ( in_array( $glossaryIndexStyle, array( 'classic-table', 'modern-table', 'expand-style', 'grid-style', 'cube-style' ) ) ) {
                    $newIndexLetter = self::detectStartNewIndexLetter( null, $key );

                    if ( $newIndexLetter !== false ) {
                        if ( !$isFirstIndexLetter ) {
                            $content .= '<li class="the-letter-separator"></li>';
                        }

                        $content .= '<li class="the-index-letter"><h2>' . $newIndexLetter . '</h2></li>';
                        $isFirstIndexLetter = FALSE;
                    }
                }

                $content .= $value;
            }
            $content .= '</ul>';

            if ( self::isServerSide() && !isset( $args[ 'nopaging' ] ) && in_array( $paginationPosition, array( 'bottom', 'both' ) ) ) {
                $content .= apply_filters( 'cmtt_glossary_index_pagination', '', $glossary_query, $shortcodeAtts );
            }
        } else {
            $noResultsText = __( get_option( 'cmtt_glossary_NoResultsLabel', 'Nothing found. Please change the filters.' ), 'cm-tooltip-glossary' );
            $content.= '<span class="error">' . $noResultsText . '</span>';
        }

        $content = apply_filters( 'cmtt_glossary_index_after_content', $content, $glossary_query, $shortcodeAtts );

        do_action( 'cmtt_after_glossary_index' );

        return $content;
    }

    /**
     * Outputs the pagination
     * @param type $content
     * @param type $glossary_query
     * @param type $currentPage
     * @return type
     */
    public static function outputPagination( $content, $glossary_query, $shortcodeAtts ) {
        $currentPage      = $shortcodeAtts[ 'itemspage' ];
        $glossaryPageLink = $shortcodeAtts[ 'glossary_page_link' ];

        $showPages = 11;
        $lastPage  = $glossary_query->max_num_pages;

        $prevPage = ($currentPage - 1 < 1) ? 1 : $currentPage - 1;
        $nextPage = ($currentPage + 1 > $lastPage) ? $lastPage : $currentPage + 1;

        $prevHalf = ($currentPage - ceil( $showPages / 2 )) <= 0 ? 0 : ($currentPage - ceil( $showPages / 2 ));
        $prevDiff = (ceil( $showPages / 2 ) - $currentPage >= 0) ? ceil( $showPages / 2 ) - $currentPage : 0;
        $nextHalf = ($currentPage + ceil( $showPages / 2 )) > $lastPage ? $lastPage : ($currentPage + ceil( $showPages / 2 ));

        $prevSectionPage = ($currentPage - ceil( $showPages / 2 )) < 1 ? 1 : $currentPage - ceil( $showPages / 2 );
        $nextSectionPage = ($currentPage + ceil( $showPages / 2 )) > $lastPage ? $lastPage : $currentPage + ceil( $showPages / 2 );

        $pagesStart = ($prevHalf > 0) ? $prevHalf : 1;
        $pagesEnd   = min( $nextHalf + $prevDiff, $nextSectionPage );

        $showFirst = $prevHalf > 1;
        $showLast  = $nextHalf < $lastPage;

        ob_start();
        ?>
        <ul class="pageNumbers">

            <?php
            if ( 1 != $currentPage ) :
                ?>
                <li data-page-number="<?php echo $prevPage ?>">
                    <a href="<?php echo esc_url( add_query_arg( array( 'itemspage' => $prevPage ), $glossaryPageLink ) ); ?>">&lt;&lt;</a>
                </li>
            <?php endif; ?>

            <?php
            $pageSelected = (1 == $currentPage) ? 'class="selected"' : '';
            if ( $showFirst ) :
                ?>
                <li <?php echo $pageSelected ?> data-page-number="1">
                    <a href="<?php echo esc_url( add_query_arg( array( 'itemspage' => 1 ), $glossaryPageLink ) ); ?>">1</a>
                </li>
            <?php endif; ?>

            <?php
            if ( $prevSectionPage > 1 ) :
                ?>
                <li data-page-number="<?php echo $prevSectionPage ?>">
                    <a href="<?php echo esc_url( add_query_arg( array( 'itemspage' => $prevSectionPage ), $glossaryPageLink ) ); ?>">(...)</a>
                </li>
            <?php endif; ?>

            <?php for ( $i = $pagesStart; $i <= $pagesEnd; $i++ ): ?>
                <?php $pageSelected = ($i == $currentPage) ? 'class="selected"' : '' ?>
                <li <?php echo $pageSelected ?> data-page-number="<?php echo $i ?>">
                    <a href="<?php echo esc_url( add_query_arg( array( 'itemspage' => $i ), $glossaryPageLink ) ); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php
            if ( $nextHalf !== $lastPage ) :
                ?>
                <li data-page-number="<?php echo $nextSectionPage ?>">
                    <a href="<?php echo esc_url( add_query_arg( array( 'itemspage' => $nextSectionPage ), $glossaryPageLink ) ); ?>">(...)</a>
                </li>
            <?php endif; ?>

            <?php
            $pageSelected = ($lastPage == $currentPage) ? 'class="selected"' : '';
            if ( $showLast ) :
                ?>
                <li <?php echo $pageSelected ?> data-page-number="<?php echo $lastPage ?>">
                    <a href="<?php echo esc_url( add_query_arg( array( 'itemspage' => $lastPage ), $glossaryPageLink ) ); ?>"><?php echo $lastPage ?></a>
                </li>
            <?php endif; ?>

            <?php
            if ( $lastPage != $currentPage ) :
                ?>
                <li data-page-number="<?php echo $nextPage ?>">
                    <a href="<?php echo esc_url( add_query_arg( array( 'itemspage' => ($nextPage) ), $glossaryPageLink ) ); ?>">&gt;&gt;</a>
                </li>
            <?php endif; ?>

        </ul>
        <?php
        $content.=ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Check if tooltips are disabled for given page
     * @global type $post
     * @param type $tooltipData
     * @return type
     */
    public static function tooltipsDisabledForPage( $tooltipData ) {
        global $post;
        $postId = empty( $post->ID ) ? '' : $post->ID;

        if ( !empty( $postId ) ) {
            /*
             *  Checks whether to show tooltips on this page or not
             */
            if ( self::disableTooltips( false, $post ) ) {
                unset( $tooltipData[ 'cmtooltip' ] );
            }
        }
        return $tooltipData;
    }

    public static function _scriptStyleLoader( $config, $embeddedMode = false ) {
        $stylesAndScripts = '';
        if ( !empty( $config ) ) {
            if ( !empty( $config[ 'scripts' ] ) ) {
                foreach ( $config[ 'scripts' ] as $scriptKey => $scriptData ) {
                    $scriptData = shortcode_atts( array(
                        'path'      => '',
                        'deps'      => array(),
                        'ver'       => false,
                        'in_footer' => false,
                        'localize'  => NULL,
                    ), $scriptData );

                    /*
                     * In embedded situations jQuery will most likely be on the site already, so no need to call it
                     */
                    if ( $embeddedMode && is_array( $scriptData[ 'deps' ] ) && !empty( $scriptData[ 'deps' ] ) ) {
                        foreach ( $scriptData[ 'deps' ] as $key => $value ) {
                            if ( 'jquery' === $value ) {
                                unset( $scriptData[ 'deps' ][ $key ] );
                            }
                        }
                    }
                    wp_enqueue_script( $scriptKey, $scriptData[ 'path' ], $scriptData[ 'deps' ], $scriptData[ 'ver' ], $scriptData[ 'in_footer' ] );

                    if ( !empty( $scriptData[ 'localize' ] ) && is_array( $scriptData[ 'localize' ] ) ) {
                        $scriptDataLocalize = shortcode_atts( array(
                            'var_name' => '',
                            'data'     => array()
                        ), $scriptData[ 'localize' ] );
                        wp_localize_script( $scriptKey, $scriptDataLocalize[ 'var_name' ], $scriptDataLocalize[ 'data' ] );
                    }
                }
            }

            if ( !empty( $config[ 'styles' ] ) ) {
                foreach ( $config[ 'styles' ] as $styleKey => $styleData ) {
                    wp_enqueue_style( $styleKey, $styleData[ 'path' ] );
                    /*
                     * It's WP 3.3+ function
                     */
                    if ( function_exists( 'wp_add_inline_style' ) && !empty( $styleData[ 'inline' ] ) && is_array( $styleData[ 'inline' ] ) ) {
                        wp_add_inline_style( $styleKey, $styleData[ 'inline' ][ 'data' ] );
                    }
                }
            }

            if ( $embeddedMode ) {
                ob_start();
                wp_print_scripts( array_keys( $config[ 'scripts' ] ) );
                wp_print_styles( array_keys( $config[ 'styles' ] ) );
                $stylesAndScripts = ob_get_clean();
            }
        }
        if ( !empty( $stylesAndScripts ) ) {
            self::$preContent .= $stylesAndScripts;
//			add_filter( 'the_content', array( __CLASS__, '_preContent' ), PHP_INT_MAX );
            add_filter( 'cmtt_glossary_index_after_content', array( __CLASS__, '_preContent' ), PHP_INT_MAX );
        }
        return $stylesAndScripts;
    }

    public static function _preContent( $content ) {
        if ( !defined( 'DOING_AJAX' ) ) {
            if ( !empty( self::$preContent ) && is_string( self::$preContent ) ) {
                $content = self::$preContent . $content;
            }
        }
        return $content;
    }

    /**
     * Adds the scripts which has to be included on the main glossary index page only
     */
    public static function addScripts() {
        $embeddedMode = get_option( 'cmtt_enableEmbeddedMode', false );
        $inFooter     = get_option( 'cmtt_script_in_footer', false );
        /*
         * If the embeddedMode is enabled we ignore the inFooter setting
         */
        if ( $inFooter && !$embeddedMode ) {
            add_action( 'wp_footer', array( __CLASS__, 'outputScripts' ), 9 );
        } else {
            self::outputScripts();
        }
    }

    public static function outputScripts() {
        global $post;
        static $runOnce = FALSE;
        if ( $runOnce === TRUE ) {
            return;
        }

        global $post, $replacedTerms;
        $postId = empty( $post->ID ) ? '' : $post->ID;

        $embeddedMode   = get_option( 'cmtt_enableEmbeddedMode', false );
        $inFooter       = get_option( 'cmtt_script_in_footer', false );
        $isGlossaryTerm = (!empty( $post->post_type ) && in_array( $post->post_type, array( 'glossary' ) )); //TRUE if is glossary term page, FALSE otherwise

        /*
         * If the scripts are loaded in footer and there's no tooltips found, and we're not on Glossary Term Page, we can ignore loading scripts
         */
        if ( ($inFooter && !$embeddedMode) && (empty( $replacedTerms ) && !self::$shortcodeDisplayed) && !$isGlossaryTerm ) {
            return;
        }

        $tooltipData = array();

        $tooltipArgs                              = array(
            'clickable'    => (bool) apply_filters( 'cmtt_is_tooltip_clickable', FALSE ),
            'delay'        => (int) get_option( 'cmtt_tooltipDisplayDelay', 0 ),
            'timer'        => (int) get_option( 'cmtt_tooltipHideDelay', 0 ),
            'minw'         => (int) get_option( 'cmtt_tooltipWidthMin', 200 ),
            'maxw'         => (int) get_option( 'cmtt_tooltipWidthMax', 400 ),
            'top'          => (int) get_option( 'cmtt_tooltipPositionTop' ),
            'left'         => (int) get_option( 'cmtt_tooltipPositionLeft' ),
            'endalpha'     => (int) get_option( 'cmtt_tooltipOpacity' ),
            'borderStyle'  => get_option( 'cmtt_tooltipBorderStyle' ),
            'borderWidth'  => get_option( 'cmtt_tooltipBorderWidth' ) . 'px',
            'borderColor'  => get_option( 'cmtt_tooltipBorderColor' ),
            'background'   => get_option( 'cmtt_tooltipBackground' ),
            'foreground'   => get_option( 'cmtt_tooltipForeground' ),
            'fontSize'     => get_option( 'cmtt_tooltipFontSize' ) . 'px',
            'padding'      => get_option( 'cmtt_tooltipPadding' ),
            'borderRadius' => get_option( 'cmtt_tooltipBorderRadius' ) . 'px'
        );
        $tooltipData[ 'cmtooltip' ]               = apply_filters( 'cmtt_tooltip_script_args', $tooltipArgs );
        $tooltipData[ 'ajaxurl' ]                 = admin_url( 'admin-ajax.php' );
        $tooltipData[ 'post_id' ]                 = $postId;
        $tooltipData[ 'mobile_disable_tooltips' ] = get_option( 'cmtt_glossaryMobileDisableTooltips' );
        $tooltipData[ 'tooltip_on_click' ]        = get_option( 'cmtt_glossaryShowTooltipOnClick', '0' );

        $scriptsConfig = array(
            'scripts' => array(
                'cm-modernizr-js'     => array(
                    'path'      => self::$jsPath . 'modernizr.min.js',
                    'in_footer' => $inFooter
                ),
                'tooltip-frontend-js' => array(
                    'path'      => self::$jsPath . 'tooltip.js',
                    'deps'      => array( 'jquery', 'cm-modernizr-js', 'mediaelement' ),
                    'in_footer' => $inFooter,
                    'localize'  => array(
                        'var_name' => 'cmtt_data',
                        'data'     => apply_filters( 'cmtt_tooltip_script_data', $tooltipData )
                    )
                ),
            ),
            'styles'  => array(
                'cmtooltip' => array(
                    'path'   => self::$cssPath . 'tooltip.css',
                    'inline' => array(
                        'data' => self::getDynamicCSS()
                    )
                ),
                'dashicons' => array(
                    'path' => false,
                ),
            )
        );

        $fontName = get_option( 'cmtt_tooltipFontStyle', 'default' );
        if ( is_string( $fontName ) && $fontName !== 'default' ) {
            $scriptsConfig[ 'styles' ][ 'tooltip-google-font' ] = array( 'path' => '//fonts.googleapis.com/css?family=' . $fontName );
        }

        self::_scriptStyleLoader( $scriptsConfig, $embeddedMode );
        $runOnce = TRUE;
    }

    public static function addScriptParams( $shortcodeAtts ) {
        global $post;
        static $runOnce;
        if ( $runOnce === TRUE ) {
            return;
        }

        $embeddedMode = get_option( 'cmtt_enableEmbeddedMode', false );
        $inFooter     = get_option( 'cmtt_script_in_footer', false );

        if ( !self::isServerSide() ) {
            $listnavArgs                  = array(
                'perPage'            => (int) get_option( 'cmtt_perPage', 0 ),
                'letters'            => (array) get_option( 'cmtt_index_letters' ),
                'includeNums'        => (bool) get_option( 'cmtt_index_includeNum' ),
                'includeAll'         => (bool) get_option( 'cmtt_index_includeAll' ),
                'initLetter'         => isset( $shortcodeAtts[ 'letter' ] ) ? $shortcodeAtts[ 'letter' ] : get_option( 'cmtt_index_initLetter', '' ),
                'initLetterOverride' => !empty( $shortcodeAtts[ 'letter' ] ),
                'allLabel'           => __( get_option( 'cmtt_index_allLabel', 'ALL' ), 'cm-tooltip-glossary' ),
                'noResultsLabel'     => __( get_option( 'cmtt_glossary_NoResultsLabel', 'Nothing found. Please change the filters.' ), 'cm-tooltip-glossary' ),
                'showCounts'         => (bool) get_option( 'cmtt_index_showCounts', '1' ),
                'sessionSave'        => (bool) get_option( 'cmtt_index_sessionSave', '1' ),
                'doingSearch'        => !empty( $shortcodeAtts[ 'search_term' ] ),
            );
            $tooltipData[ 'enabled' ]     = (bool) apply_filters( 'cmtt_index_enabled', get_option( 'cmtt_index_enabled', '1' ) );
            $tooltipData[ 'listnav' ]     = apply_filters( 'cmtt_listnav_js_args', $listnavArgs );
            $tooltipData[ 'list_id' ]     = apply_filters( 'cmtt_glossary_index_list_id', 'glossaryList' );
            $tooltipData[ 'fast_filter' ] = (bool) apply_filters( 'cmtt_glossary_index_fast_filter', get_option( 'cmtt_indexFastFilter', '0' ) );
        }

        $tooltipData[ 'ajaxurl' ] = admin_url( 'admin-ajax.php' );

        /*
         * post_id is either the ID of the page where post has been found or the default Glossary Index Page from settings
         */
        $tooltipData[ 'post_id' ] = !empty( $post->ID ) ? $post->ID : self::getGlossaryIndexPageId();

        $scriptsConfig = array(
            'scripts' => array(
                'cm-fastlivefilter-js' => array(
                    'path'      => self::$jsPath . 'jquery.fastLiveFilter.js',
                    'deps'      => array( 'jquery' ),
                    'in_footer' => $inFooter
                ),
                'tooltip-listnav-js'   => array(
                    'path'      => self::$jsPath . 'cm-glossary-listnav.js',
                    'deps'      => array( 'jquery', 'cm-fastlivefilter-js' ),
                    'in_footer' => $inFooter,
                    'localize'  => array(
                        'var_name' => 'cmtt_listnav_data',
                        'data'     => apply_filters( 'cmtt_listnav_script_data', $tooltipData )
                    )
                ),
            ),
            'styles'  => array(
                'jquery-listnav-style' => array(
                    'path' => self::$cssPath . 'jquery.listnav.css',
                ),
            )
        );

        self::_scriptStyleLoader( $scriptsConfig, $embeddedMode );
        $runOnce = TRUE;
    }

    /**
     * Add the dynamic CSS to reflect the styles set by the options
     * @return type
     */
    public static function getDynamicCSS() {
        ob_start();
        echo apply_filters( 'cmtt_dynamic_css_before', '' );
        ?>

        .tiles ul.glossaryList a { min-width: <?php echo get_option( 'cmtt_glossarySmallTileWidth', '85px' ); ?>; width:<?php echo get_option( 'cmtt_glossarySmallTileWidth', '85px' ); ?>;  }
        .tiles ul.glossaryList span { min-width:<?php echo get_option( 'cmtt_glossarySmallTileWidth', '85px' ); ?>; width:<?php echo get_option( 'cmtt_glossarySmallTileWidth', '85px' ); ?>;  }
        .cm-glossary.tiles.big ul.glossaryList a { min-width:<?php echo get_option( 'cmtt_glossaryBigTileWidth', '179px' ); ?>; width:<?php echo get_option( 'cmtt_glossaryBigTileWidth', '179px' ); ?> }
        .cm-glossary.tiles.big ul.glossaryList span { min-width:<?php echo get_option( 'cmtt_glossaryBigTileWidth', '179px' ); ?>; width:<?php echo get_option( 'cmtt_glossaryBigTileWidth', '179px' ); ?>; }

        span.glossaryLink, a.glossaryLink {
        border-bottom: <?php echo get_option( 'cmtt_tooltipLinkUnderlineStyle' ); ?> <?php echo get_option( 'cmtt_tooltipLinkUnderlineWidth' ); ?>px <?php echo get_option( 'cmtt_tooltipLinkUnderlineColor' ); ?> !important;
        color: <?php echo get_option( 'cmtt_tooltipLinkColor' ); ?> !important;
        }
        a.glossaryLink:hover {
        border-bottom: <?php echo get_option( 'cmtt_tooltipLinkHoverUnderlineStyle' ); ?> <?php echo get_option( 'cmtt_tooltipLinkHoverUnderlineWidth' ); ?>px <?php echo get_option( 'cmtt_tooltipLinkHoverUnderlineColor' ); ?> !important;
        color:<?php echo get_option( 'cmtt_tooltipLinkHoverColor' ); ?> !important;
        }

        <?php
        $closeIconColor = get_option( 'cmtt_tooltipCloseColor', '#222' );
        if ( !empty( $closeIconColor ) ) :
            ?>
            #tt #tt-btn-close{ color: <?php echo $closeIconColor; ?> !important}
        <?php endif; ?>

        <?php
        $closeIconSize = get_option( 'cmtt_tooltipCloseSize', '20' );
        if ( !empty( $closeIconSize ) ) :
            ?>
            #tt #tt-btn-close{
            direction: rtl;
            font-size: <?php echo $closeIconSize; ?>px !important
            }
        <?php endif; ?>

        <?php
        $tooltipTextColorOverride = get_option( 'cmtt_tooltipForegroundOverride' );
        $tooltipTextColor         = get_option( 'cmtt_tooltipForeground' );
        if ( !empty( $tooltipTextColorOverride ) ) :
            ?>
            #tt #ttcont *{color: <?php echo $tooltipTextColor; ?> !important}
        <?php endif; ?>

        <?php
        $showEmptyLetters = !get_option( 'cmtt_index_showEmpty' );
        if ( !empty( $showEmptyLetters ) ) :
            ?>
            #glossaryList-nav .ln-letters a.ln-disabled {display: none}
        <?php endif; ?>

        <?php
        $internalLinkColor = get_option( 'cmtt_tooltipInternalLinkColor' );
        if ( !empty( $internalLinkColor ) ) :
            ?>
            #tt #ttcont a{color: <?php echo $internalLinkColor; ?> !important}
        <?php endif; ?>

        <?php
        $internalEditLinkColor = get_option( 'cmtt_tooltipInternalEditLinkColor' );
        if ( !empty( $internalEditLinkColor ) ) :
            ?>
            #tt #ttcont .glossaryItemEditlink a{color: <?php echo $internalEditLinkColor; ?> !important}
        <?php endif; ?>

        <?php
        $internalMobileLinkColor = get_option( 'cmtt_tooltipInternalMobileLinkColor' );
        if ( !empty( $internalMobileLinkColor ) ) :
            ?>
            #tt #ttcont .mobile-link a{color: <?php echo $internalMobileLinkColor; ?> !important}
        <?php endif; ?>

        <?php if ( get_option( 'cmtt_tooltipShadow', 1 ) ) : ?>
            #ttcont {
            box-shadow: #<?php echo str_replace( '#', '', get_option( 'cmtt_tooltipShadowColor', '666666' ) ); ?> 0px 0px 20px;
            }
            <?php
        endif;
        echo apply_filters( 'cmtt_dynamic_css_after', '' );
        $content = ob_get_clean();

        /*
         * One can use this filter to change/remove the standard styling
         */
        $dynamicCSScontent = apply_filters( 'cmtt_dynamic_css', $content );
        return trim( $dynamicCSScontent );
    }

    /**
     * Sort array with specialchars alphabetically and maintain index
     * association.
     *
     * Example:
     *
     * $array = array('Barcelona', 'Madrid', 'Albacete', 'Álava', 'Bilbao');
     *
     * asort($array);
     * var_dump($array);
     *     => array('Albacete', 'Barcelona', 'Bilbao', 'Madrid', 'Álava')
     *
     * $array = util::array_mb_sort($array);
     * var_dump($array);
     *     => array('Álava', 'Albacete', 'Barcelona', 'Bilbao', 'Madrid')
     *
     * @param   array  $array   Array of elements to sort.
     *
     * @return  array           Sorted array
     *
     * @access  public
     *
     * @static
     */
    public static function array_mb_sort_alphabetically( array $array, $reverse = FALSE ) {
        if ( $reverse ) {
            usort( $array, array( __CLASS__, 'mb_string_compare' ) );
        } else {
            uasort( $array, array( __CLASS__, 'mb_string_compare' ) );
        }

        return $array;
    }

    /**
     * Comparaison de chaines unicode. This method can come in handy when we
     * want to use as a callback function on uasort & usort PHP functions to
     * sort arrays when you have special characters for example accents.
     *
     * @param   string  $s1  First string to compare with
     *
     * @param   string  $s2  Second string to compare with
     *
     * @return  boolean
     *
     * @access  public
     * @since   1.0.000
     * @static
     */
    public static function mb_string_compare( $s1, $s2 ) {
        return strcmp(
        iconv( 'UTF-8', 'ISO-8859-1//TRANSLIT', self::decode_characters( $s1 ) ), iconv( 'UTF-8', 'ISO-8859-1//TRANSLIT', self::decode_characters( $s2 ) ) );
    }

    /**
     * Decode a string
     *
     * @param   string  $string   Encoded string
     *
     * @return  string
     *
     * @access  public
     *
     * @static
     */
    public static function decode_characters( $string ) {
        $string = mb_convert_encoding( $string, "HTML-ENTITIES", "UTF-8" );
        $string = preg_replace( '~^(&([a-zA-Z0-9]);)~', htmlentities( '${1}' ), $string );
        return($string);
    }

}
