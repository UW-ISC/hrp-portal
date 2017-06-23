<?php

class CMTT_Glossary_Plus {

    protected static $filePath = '';
    protected static $cssPath  = '';
    protected static $jsPath   = '';

    /**
     * Removes the hooks
     */
    public static function after() {
        remove_filter( 'cmtt_glossary_index_listnav_content', array( 'CMTT_Glossary_Index', 'removeListnav' ) );
    }

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
        add_action( 'init', array( __CLASS__, 'createTaxonomies' ) );
        add_action( 'init', array( __CLASS__, 'startSession' ) );
        add_action( 'cmtt_add_options', array( __CLASS__, 'addOptions' ) );
        add_action( 'cmtt_disable_parsing', array( __CLASS__, 'disableParsing' ) );
        add_action( 'cmtt_reenable_parsing', array( __CLASS__, 'reenableParsing' ) );

        add_action( 'cmtt_save_options_after', array( __CLASS__, 'flushMWCache' ), 10, 2 );
        add_action( 'cmtt_on_glossary_item_save', array( __CLASS__, 'saveAdditionalPostData' ), 10, 2 );
        add_action( 'cmtt_on_glossary_item_save_before', array( __CLASS__, 'flushSingleMWCache' ), 10, 2 );
        add_action( 'cmtt_on_glossary_item_save_before', array( __CLASS__, 'saveSelectedTermsForPage' ), 11, 2 );
        add_action( 'cmtt_on_glossary_item_save_before', array( __CLASS__, 'saveDisableRelatedPosts' ), 12, 2 );

        /*
         * FILTERS
         */
        add_filter( 'the_content', array( __CLASS__, 'addRelatedTerms' ), 21500 );
        add_filter( 'the_title', array( __CLASS__, 'addAbbreviation' ), 22000, 2 );

        add_filter( 'cmtt_post_type_args', array( __CLASS__, 'addPostTypeSupport' ) );
        add_filter( 'cmtt_edit_properties_metabox_array', array( __CLASS__, 'renderFlushButton' ) );

        if ( get_option( 'cmtt_glossaryTermsInComments', false ) ) {
            add_filter( 'comment_text', array( 'CMTT_Pro', 'cmtt_glossary_parse' ), 20000 );
        }

        /* Filter the single_template with our custom function */
        add_filter( 'single_template', array( __CLASS__, 'glossaryTermCustomTemplate' ) );

        add_filter( 'cmtt_glossary_index_shortcode_default_atts', array( __CLASS__, 'addGlossaryIndexDefaultAtts' ) );
        add_filter( 'cmtt_glossary_index_atts', array( __CLASS__, 'processGlossaryIndexShortcodeAtts' ), 5 );
        add_filter( 'cmtt_glossary_index_atts', array( __CLASS__, 'addGlossaryIndexGetAtts' ), 10 );
        add_filter( 'cmtt_glossary_index_atts', array( __CLASS__, 'addGlossaryIndexPostAtts' ), 20 );
        add_filter( 'cmtt_glossary_index_style', array( __CLASS__, 'changeGlossaryIndexStyle' ) );

        add_filter( 'cmtt_glossary_index_query_args', array( __CLASS__, 'addSearchFilter' ), 10, 2 );
        add_filter( 'cmtt_glossary_index_query_args', array( __CLASS__, 'addServerSidePaginationFilter' ), 10, 2 );

        add_action( 'cmtt_glossary_index_query_after', array( __CLASS__, 'removeServerSidePaginationFilter' ), 10, 2 );

        add_filter( 'cmtt_glossary_index_query_args', array( __CLASS__, 'addGlossaryIndexQueryArgs' ), 10, 2 );

        add_filter( 'cmtt_parser_query_args', array( __CLASS__, 'addParserQueryArgs' ), 10, 2 );

        add_filter( 'cmtt_glossary_index_before_listnav_content', array( __CLASS__, 'outputBeforeListnav' ), 10, 3 );
        add_filter( 'cmtt_glossary_index_listnav_content_inside', array( __CLASS__, 'outputListnav' ), 10, 3 );

        add_filter( 'cmtt_glossary_index_style_classes', array( __CLASS__, 'addGlossaryIndexStyles' ) );
        add_filter( 'cmtt_glossary_index_item_desc', array( __CLASS__, 'outputGlossaryIndexItemDesc' ), 10, 4 );
        add_filter( 'cmtt_glossary_index_item_desc', array( __CLASS__, 'stripDescriptionShortcode' ), 20, 4 );
        add_filter( 'cmtt_glossary_index_item_desc', array( __CLASS__, 'addGlossaryIndexDescRelated' ), 50, 4 );
        add_filter( 'cmtt_glossary_index_item_desc', array( __CLASS__, 'wrapGlossaryIndexItemDesc' ), 100, 4 );

        add_filter( 'cmtt_glossary_index_tooltip_content', array( __CLASS__, 'outputGlossaryIndexGoogleTermOnly' ), 5, 2 );
        add_filter( 'cmtt_glossary_index_tooltip_content', array( __CLASS__, 'outputGlossaryIndexGoogleTranslation' ), 25, 2 );
//        add_filter('cmtt_glossary_index_tooltip_content', array(__CLASS__, 'addGlossaryIndexMerriamWebsterDictionary'), 50, 2);
//        add_filter('cmtt_glossary_index_tooltip_content', array(__CLASS__, 'addGlossaryIndexMerriamWebsterThesaurus'), 60, 2);

        add_filter( 'cmtt_glossary_index_content_arr', array( __CLASS__, 'addAbbreviationsToGlossaryIndex' ), 10, 5 );
        add_filter( 'cmtt_glossary_index_content_arr', array( __CLASS__, 'addSynonymsToGlossaryIndex' ), 10, 5 );

        add_filter( 'cmtt_modify_listnav_counts_term', array( __CLASS__, 'addAbbreviationsCount' ), 10, 3 );
        add_filter( 'cmtt_modify_listnav_counts_term', array( __CLASS__, 'addSynonymsCount' ), 10, 3 );


        add_filter( 'cmtt_listnav_js_args', array( __CLASS__, 'addListnavArgs' ) );
        add_filter( 'cmtt-settings-tabs-array', array( __CLASS__, 'addSettingsTabs' ) );
        add_filter( 'cmtt-custom-settings-tab-content-5', array( __CLASS__, 'addAPITabContent' ) );
        add_filter( 'cmtt-custom-settings-tab-content-8', array( __CLASS__, 'addGlossaryReplacementTabContent' ) );


        add_filter( 'cmtt_add_properties_metabox', array( __CLASS__, 'addMetaboxFields' ) );
        add_action( 'cmtt_add_disables_metabox', array( __CLASS__, 'addDisablesFields' ) );
        add_action( 'cmtt_register_boxes', array( __CLASS__, 'registerMetaboxes' ) );

        add_filter( 'cmtt_disable_metabox_posttypes', array( __CLASS__, 'filterDisableMetaboxPosttypes' ) );
        add_filter( 'cmtt_add_backlink_content', array( __CLASS__, 'addBacklinkContent' ), 5, 2 );

        add_action( 'cmtt_import_glossary_item', array( __CLASS__, 'importAdditionalInfo' ), 10, 2 );

        add_filter( 'cmtt_export_header_row', array( __CLASS__, 'addExportHeaderRowFields' ) );
        add_filter( 'cmtt_export_data_row', array( __CLASS__, 'addExportDataRowFields' ), 10, 2 );

        add_action( 'cmtt_replace_template_before_synonyms', array( __CLASS__, 'applyParseCustomTermList' ), 10, 2 );
        add_action( 'cmtt_replace_template_after_synonyms', array( __CLASS__, 'applyCategoryFiltering' ), 10, 3 );

        add_action( 'cmtt_3rdparty_tooltip_content', array( __CLASS__, 'addMWToTooltipContent' ), 10, 3 );
        add_action( 'cmtt_3rdparty_tooltip_content', array( __CLASS__, 'addGlosbeToTooltipContent' ), 10, 3 );
        add_filter( 'cmtt_term_tooltip_content', array( __CLASS__, 'outputGlossaryTermTranslation' ), 25, 2 );

        add_filter( 'cmtt_term_tooltip_additional_class', array( __CLASS__, 'addCategoryClass' ), 10, 2 );
        add_filter( 'cmtt_term_tooltip_additional_class', array( __CLASS__, 'addTermAdditionalClass' ), 10, 2 );
        add_filter( 'cmtt_term_tooltip_permalink', array( __CLASS__, 'changeTermPermalink' ), 10, 2 );
        add_filter( 'cmtt_parse_space_separated_only', array( __CLASS__, 'addSupportForSpaceSepparated' ) );

        add_filter( 'cmtt_glossaryPreItemTitleContent_add', array( __CLASS__, 'addNewIconGlossaryIndex' ), 10, 2 );

        add_filter( 'cmtt_parse_addition_add', array( __CLASS__, 'addAbbreviationsToParsing' ), 10, 2 );

        add_filter( 'cmtt_tooltip_script_data', array( __CLASS__, 'addTooltipScriptData' ) );

        add_filter( 'cmtt_add_admin_menu_after_new', array( __CLASS__, 'addAdminMenuItems' ) );
        add_filter( 'cmtt_enqueueFlushRules', array( __CLASS__, 'enqueueFlushRules' ), 10, 2 );

        add_action( 'cmtt_flush_rewrite_rules', array( __CLASS__, 'createTaxonomies' ) );
        add_action( 'parent_file', array( __CLASS__, 'setCurrentMenu' ) );

        add_filter( 'cmtt_glossary_term_after_content', array( __CLASS__, 'termAddListnav' ), 20 );

        add_filter( 'cmtt_tooltip_script_data', array( __CLASS__, 'glossaryTempDisableTooltips' ), PHP_INT_MAX );

        add_action( 'cmtt_glossary_doing_search', array( __CLASS__, 'addSearchFilters' ), 10, 2 );

        add_filter( 'cmtt_runParser', array( __CLASS__, 'maybeDisableTooltips' ), 10, 4 );

        add_filter( 'cmtt_add_backlink_content', array( __CLASS__, 'displayCategoriesOnSinglePage' ) );
        add_filter( 'cmtt_add_backlink_content', array( __CLASS__, 'displayTagsOnSinglePage' ) );

        /*
         * SHORTCODES
         */
        add_shortcode( 'glossary-term', array( __CLASS__, 'glossaryTermShortcode' ) );
        add_shortcode( 'glossary-listnav', array( __CLASS__, 'glossaryListnavShortcode' ) );
        add_shortcode( 'glossary-toogle-tooltips', array( __CLASS__, 'glossaryToggleTooltips' ) );
        add_shortcode( 'glossary-toggle-theme', array( __CLASS__, 'glossaryToggleTheme' ) );

        /*
         * AJAX
         */
        add_action( 'wp_ajax_glossary_search', array( __CLASS__, 'ajaxGlossary' ) );
        add_action( 'wp_ajax_nopriv_glossary_search', array( __CLASS__, 'ajaxGlossary' ) );

        add_action( 'cmtt_include_files_after', array( __CLASS__, 'includeFiles' ) );
        add_action( 'cmtt_init_files_after', array( __CLASS__, 'initFiles' ) );
    }

    public static function displayCategoriesOnSinglePage( $content = '' ) {
        /* ML  - check if categories needed to be shown */
        $internalContent = '';
        $showCategories  = get_option( 'cmtt_term_show_taxonomy_glossary-categories', false );
        if ( $showCategories == 1 ) {
            $internalContent = self::displayTaxonomyOnSinglePage( 'glossary-categories' );
        }
        $position = get_option( 'cmtt_term_position_taxonomy_glossary-categories', 'top' );

        if ( !is_string( $internalContent ) ) {
            $internalContent = '';
        }
        $result = ($position !== 'bottom') ? $internalContent . $content : $content . $internalContent;
        return $result;
    }

    public static function displayTagsOnSinglePage( $content = '' ) {
        /* ML - check if tags needed to be shown */
        $internalContent = '';
        $showTags        = get_option( 'cmtt_term_show_taxonomy_glossary-tags', false );
        if ( $showTags == 1 ) {
            $internalContent = self::displayTaxonomyOnSinglePage( 'glossary-tags' );
        }
        $position = get_option( 'cmtt_term_position_taxonomy_glossary-tags', 'top' );

        if ( !is_string( $internalContent ) ) {
            $internalContent = '';
        }
        $result = ($position !== 'bottom') ? $internalContent . $content : $content . $internalContent;
        return $result;
    }

    /**
     * Custom taxonomy display added
     * @global type $wp_query
     * @global type $post
     * @param type $content
     * @return type
     */
    public static function displayTaxonomyOnSinglePage( $taxonomySlug = 'glossary-categories' ) {
        global $wp_query;
        $post         = $wp_query->post;
        $id           = $post->ID;
        $defaultLabel = 'Categories:';

        if ( !in_array( $taxonomySlug, array( 'glossary-categories', 'glossary-tags' ) ) ) {
            return '';
        }
        switch ( $taxonomySlug ) {
            case 'glossary-categories':
                $taxonomyName = 'cat';
                break;
            case 'glossary-tags':
                $taxonomyName = 'gtags';
                $defaultLabel = 'Tags:';
                break;
            default:
                $taxonomyName = $taxonomySlug;
                break;
        }

        $internalContent = '';
        $glossaryPageUrl = get_permalink( CMTT_Glossary_Index::getGlossaryIndexPageId() );

        $showTaxonomyGlobal = get_option( 'cmtt_term_show_taxonomy_' . $taxonomySlug, false );
        $showTaxonomyLocal  = get_post_meta( $id, 'cmtt_term_show_taxonomy_' . $taxonomySlug, true );
        $taxonomyTerms      = wp_get_post_terms( $id, $taxonomySlug, array() );
        $label              = get_option( 'cmtt_term_taxonomy_label_' . $taxonomySlug, $defaultLabel );

        /*
         * The taxonomy is shown when the global setting is different than single setting.
         * This allows to have an exception in both ways eg.
         * - Feature is enabled on all terms but disabled on single page
         * - Feature is disabled on all terms but enabled on single page
         */
        if ( !empty( $taxonomyTerms ) && ($showTaxonomyGlobal != $showTaxonomyLocal) ) {

            $internalContent .= '<div class="cmtt-taxonomy-single" data-glossary-url="' . $glossaryPageUrl . '">' . __( $label, 'cm-tooltip-glossary' ) . ' ';
            foreach ( $taxonomyTerms as $taxonomyTerm ) {
                $tagId                = $taxonomyTerm->term_id;
                $taxonomyContentArr[] = '<a data-tagid="' . $tagId . '" data-taxonomy-name="' . $taxonomyName . '" title="' . __( $label, 'cm-tooltip-glossary' ) . $taxonomyTerm->name . '">' . $taxonomyTerm->name . '</a>';
            }
            $internalContent .= implode( ', ', $taxonomyContentArr );
            $internalContent .= '</div>';
        }

        return $internalContent;
    }

    public static function maybeDisableTooltips( $result, $post, $content, $force ) {
        $frontendTooltipDisableMode = get_option( 'cmtt_frontendTooltipDisableMode', 'cmtooltip' );
        if ( 'parsing' === $frontendTooltipDisableMode && self::tooltipsTempDisabled() ) {
            return FALSE;
        }
        return $result;
    }

    /**
     * Start session
     */
    public static function startSession() {
        if ( !session_id() ) {
            session_start();
        }
    }

    /**
     * Modifies the Glossary query
     * @param type $args
     * @param type $shortcodeAtts
     */
    public static function addSearchFilters( $args, $shortcodeAtts ) {
        add_filter( 'posts_groupby', array( __CLASS__, 'addGroupbyFilter' ), 10, 2 );
    }

    /**
     * Adds the GROUP By statement to glossary queries
     * @global type $wpdb
     * @param type $groupby
     * @param type $wp_query_object
     * @return type
     */
    public static function addGroupbyFilter( $groupby, $wp_query_object ) {
        global $wpdb;
        $groupby = "{$wpdb->posts}.ID";
        return $groupby;
    }

    /**
     * Display the listnav od the Term page
     * @param type $content
     * @return string
     */
    public static function termAddListnav( $content ) {
        wp_enqueue_style( 'jquery-listnav-style', self::$cssPath . 'jquery.listnav.css' );

        $addListnav = get_option( 'cmtt_glossaryTermShowListnav' );
        if ( $addListnav ) {
            $listnav = self::glossaryListnavShortcode();
            $content = $listnav . $content;
        }
        return $content;
    }

    /**
     * Function allowing to disable/enable the tooltips
     * @param type $atts
     */
    public static function glossaryToggleTheme( $atts = array() ) {
        static $id = 0;

        $args = shortcode_atts( array( 'class' => '', 'label' => '' ), $atts );

        $label        = $args[ 'label' ];
        $bodyClass    = $args[ 'class' ];
        $elementClass = 'cmtt-glossary-theme-toggle';
        $elementId    = 'cmtt-glossary-theme-toggle-' . ( ++$id);

        $tooltipToggleLink = '<a id="' . $elementId . '" class="' . $elementClass . '" data-bodyclass="' . $bodyClass . '">' . $label . '</a>';

        return $tooltipToggleLink;
    }

    /**
     * Function allowing to disable/enable the tooltips
     * @param type $atts
     */
    public static function glossaryToggleTooltips( $atts = array() ) {
        $atts = shortcode_atts( array(
            'session' => FALSE
        ), $atts );

        $disableTooltip        = filter_input( INPUT_GET, 'disable_tooltips' );
        $disableTooltipSession = isset( $_SESSION[ 'cmtt_disable_tooltips' ] ) ? $_SESSION[ 'cmtt_disable_tooltips' ] : false;

        if ( $atts[ 'session' ] && '1' === $disableTooltip ) {
            /*
             * Store in session
             */
            $_SESSION[ 'cmtt_disable_tooltips' ] = $disableTooltip;
        } else {
            if ( empty( $atts[ 'session' ] ) || '0' === $disableTooltip ) {
                /*
                 * Remove from session
                 */
                unset( $_SESSION[ 'cmtt_disable_tooltips' ] );
                $disableTooltipSession = FALSE;
            }
        }

        if ( '1' === $disableTooltip || $disableTooltipSession ) {
            $tooltipToggleLink = esc_url( add_query_arg( array( 'disable_tooltips' => 0 ), remove_query_arg( 'disable_tooltips' ) ) );
            $label             = __( apply_filters( 'cmtt_tooltip_on_off_widget_enable_text', 'Enable Tooltips' ), 'cm-tooltip-glossary' );
        } else {
            $tooltipToggleLink = esc_url( add_query_arg( array( 'disable_tooltips' => 1 ) ) );
            $label             = __( apply_filters( 'cmtt_tooltip_on_off_widget_disable_text', 'Disable Tooltips' ), 'cm-tooltip-glossary' );
        }

        $labelHtml   = '';
        $widgetLabel = apply_filters( 'cmtt_tooltip_on_off_widget_label', get_option( 'cmtt_tooltipOnOffWidgetLabel', '' ) );
        if ( !empty( $label ) ) {
            $labelHtml = '<div class="cmtt-glossary-tooltip-toggle-label-wrapper"><label class="cmtt-glossary-tooltip-toggle-label">' . $widgetLabel . '</label></div>';
        }

        $tooltipToggleLink = '<div class="cmtt-glossary-tooltip-toggle-wrapper">' . $labelHtml . '<a href="' . $tooltipToggleLink . '" class="cmtt-glossary-tooltip-toggle">' . $label . '</a></div>';

        return $tooltipToggleLink;
    }

    /**
     * Function allowing to disable the tooltips if it's in the query.
     * @param array $args
     * @return array
     */
    public static function tooltipsTempDisabled() {
        $disableTooltip        = filter_input( INPUT_GET, 'disable_tooltips' );
        $sessionDisableTooltip = FALSE;

        if ( '0' === $disableTooltip ) {
            /*
             * Remove from session
             */
            unset( $_SESSION[ 'cmtt_disable_tooltips' ] );
        }

        if ( isset( $_SESSION[ 'cmtt_disable_tooltips' ] ) ) {
            $sessionDisableTooltip = $_SESSION[ 'cmtt_disable_tooltips' ];
        }

        return ( $disableTooltip || $sessionDisableTooltip );
    }

    /**
     * Function allowing to disable the tooltips if it's in the query.
     * @param array $args
     * @return array
     */
    public static function glossaryTempDisableTooltips( $args ) {

        $frontendTooltipDisableMode = get_option( 'cmtt_frontendTooltipDisableMode', 'cmtooltip' );
        if ( 'cmtooltip' !== $frontendTooltipDisableMode ) {
            return $args;
        }

        if ( CMTT_Glossary_Plus::tooltipsTempDisabled() ) {
            unset( $args[ 'cmtooltip' ] );
        }
        return $args;
    }

    /**
     * Function displaying the listnav
     */
    public static function glossaryListnavShortcode( $atts = array() ) {
        extract( shortcode_atts( array(), $atts ) );

        $letters          = (array) get_option( 'cmtt_index_letters' );
        $includeAll       = (bool) get_option( 'cmtt_index_includeAll' );
        $includeNum       = (bool) get_option( 'cmtt_index_includeNum' );
        $allLabel         = get_option( 'cmtt_index_allLabel', 'ALL' );
        $letterSize       = get_option( 'cmtt_indexLettersSize' );
        $glossaryPageLink = get_permalink( CMTT_Glossary_Index::getGlossaryIndexPageId() );

        $listNavInsideContent = '';
        $listNavInsideContent .= '<div class="glossary-container ' . $letterSize . '"><div class="ln-letters">';

        $postCounts = self::getListnavCounts( array() );

        if ( $includeAll ) {
            $postsCount    = isset( $postCounts[ 'all' ] ) ? $postCounts[ 'all' ] : 0;
            $selectedClass = '';
            $listNavInsideContent .= '<a class="ln-all ln-serv-letter' . $selectedClass . '" href="' . $glossaryPageLink . '">' . $allLabel . '</a>';
        }

        if ( $includeNum ) {
            $postsCount    = isset( $postCounts[ 'al-num' ] ) ? $postCounts[ 'al-num' ] : 0;
            $disabledClass = $postsCount == 0 ? ' ln-disabled' : '';
            $selectedClass = '';
            $link          = esc_url( add_query_arg( array( 'letter' => 'al-num' ), $glossaryPageLink ) );
            $listNavInsideContent .= '<a class="ln-_ ln-serv-letter' . $disabledClass . $selectedClass . '" href="' . $link . '">0-9</a>';
        }

        foreach ( $letters as $key => $letter ) {
            $postsCount    = isset( $postCounts[ $letter ] ) ? $postCounts[ $letter ] : 0;
            $isLast        = ($key == count( $letters ) - 1 );
            $lastClass     = $isLast ? ' ln-last' : '';
            $disabledClass = $postsCount == 0 ? ' ln-disabled' : '';
            $selectedClass = '';
            $link          = esc_url( add_query_arg( array( 'letter' => $letter ), $glossaryPageLink ) );
            $listNavInsideContent .= '<a class="lnletter-' . $letter . ' ln-serv-letter' . $lastClass . $disabledClass . $selectedClass . '" data-letter-count="' . $postsCount . '" data-letter="' . $letter . '" href="' . $glossaryPageLink . '">' . mb_strtoupper( str_replace( 'ı', 'İ', $letter ) ) . '</a>';
        }

        if ( get_option( 'cmtt_index_showCounts', '1' ) ) {
            $listNavInsideContent .= '<div class="ln-letter-count" style="position: absolute; top: 0px; left: 88px; width: 20px; display: none;"></div>';
        }
        $listNavInsideContent .= '</div></div>';

        CMTT_Glossary_Index::addScriptParams( $atts );

        return $listNavInsideContent;
    }

    /**
     * Function displaying the single term shortcode
     * [glossary-term term="term" length="100"]
     */
    public static function glossaryTermShortcode( $atts ) {
        global $post, $wp_query;

        $atts = shortcode_atts(
        array(
            'term'       => '',
            'run_filter' => '0',
            'length'     => '',
            'show_title' => '1',
        ), $atts );

        if ( empty( $atts[ 'term' ] ) ) {
            return FALSE;
        }

        $args = array(
            'post_type'   => 'glossary',
            'post_status' => 'publish',
            'name'        => mb_strtolower( trim( $atts[ 'term' ] ) )
        );

        $query = new WP_Query( $args );
        $posts = $query->get_posts();
        if ( !empty( $posts ) ) {
            $glossaryItem    = reset( $posts );
            $termTitle       = $glossaryItem->post_title;
            $termDescription = $glossaryItem->post_content;
            if ( !empty( $atts[ 'length' ] ) && is_numeric( $atts[ 'length' ] ) ) {
                $termDescription = cminds_truncate( $termDescription, $atts[ 'length' ] );
            }
        } else {
            return FALSE;
        }

        if ( $atts[ 'run_filter' ] ) {
            $oldPost    = $post;
            $oldWpQuery = $wp_query;

            $post     = $glossaryItem;
            $wp_query = $query;

            $termDescription = apply_filters( 'the_content', $termDescription );

            $post     = $oldPost;
            $wp_query = $oldWpQuery;
        }

        ob_start();
        ?>
        <div class="glossary_term">
            <?php if ( $atts[ 'show_title' ] ) : ?>
                <div class="glossary_term_title"><?php echo $termTitle; ?></div>
            <?php endif; ?>
            <div class="glossary_term_description"><?php echo $termDescription; ?></div>
        </div>
        <?php
        $tooltip = ob_get_clean();
        return $tooltip;
    }

    /**
     * Whether to enqueue flush rules or not
     */
    public static function enqueueFlushRules( $enqueeFlushRules, $post ) {
        if ( $post[ "cmtt_glossaryCategoriesPermalink" ] !== get_option( 'cmtt_glossaryCategoriesPermalink' ) ) {
            update_option( 'cmtt_glossaryCategoriesPermalink', $post[ "cmtt_glossaryCategoriesPermalink" ] );
            $enqueeFlushRules = true;
        }
        if ( $post[ "cmtt_glossaryTagsPermalink" ] !== get_option( 'cmtt_glossaryTagsPermalink' ) ) {
            update_option( 'cmtt_glossaryTagsPermalink', $post[ "cmtt_glossaryTagsPermalink" ] );
            $enqueeFlushRules = true;
        }
        return $enqueeFlushRules;
    }

    /**
     * Include new files
     */
    public static function includeFiles() {
        include_once CMTT_PLUGIN_DIR . 'glossaryReplacement.php';
        include_once CMTT_PLUGIN_DIR . "abbreviations.php";
        include_once CMTT_PLUGIN_DIR . "thirdparty.php";
        include_once CMTT_PLUGIN_DIR . "glosbe.php";
    }

    /**
     * Init new files
     */
    public static function initFiles() {
        CMTT_Glossary_Replacement::init();
        CMTT_Search_Widget::init();
        CMTT_LatestTerms_Widget::init();
        CMTT_Categories_Widget::init();
        CMTT_Abbreviations::init();
        CMTT_Mw_API::addShortcodes();
        CMTT_Google_API::addShortcodes();
        CMTT_Glosbe_API::addShortcodes();
    }

    /**
     * Add edit menu for tags and categories
     * @param array $tooltipData
     * @return type
     */
    public static function addAdminMenuItems() {
        add_submenu_page( CMTT_MENU_OPTION, 'Categories', 'Categories', 'manage_categories', 'edit-tags.php?taxonomy=glossary-categories&post_type=glossary' );
        add_submenu_page( CMTT_MENU_OPTION, 'Tags', 'Tags', 'manage_categories', 'edit-tags.php?taxonomy=glossary-tags&post_type=glossary' );
    }

    /**
     * Add tooltip script data
     * @param array $tooltipData
     * @return type
     */
    public static function addTooltipScriptData( $tooltipData ) {
        $tooltipData[ 'mobile_support' ] = (bool) get_option( 'cmtt_glossaryMobileSupport' );
        return $tooltipData;
    }

    /**
     * Add the icon for new items
     * @param string $postItemTitleContent
     * @param type $glossary_item
     * @return string
     */
    public static function addNewIconGlossaryIndex( $postItemTitleContent, $glossary_item ) {
        if ( CMTT_Glossary_Plus::isNewGlossaryItem( $glossary_item ) ) {
            $newItemTitle = apply_filters( 'cmtt_new_item_mark_title', get_option( 'cmtt_glossaryNewItemMarkTitle', __( 'New!', 'cm-tooltip-glossary' ) ) );
            $postItemTitleContent .= '<label class = "cmtt-post-format-icon cmtt-post-format-new" title = "' . $newItemTitle . '"></label>';
        }
        return $postItemTitleContent;
    }

    /**
     * Decides whether the glossaryItem can be treated as 'new' or not
     * @param type $glossaryItem
     * @return boolean
     */
    public static function isNewGlossaryItem( $glossaryItem ) {
        $maxDaysDiff = get_option( 'cmtt_glossaryNewItemMaxDays' );
        if ( !$maxDaysDiff || !is_object( $glossaryItem ) || empty( $glossaryItem->post_date ) ) {
            return FALSE;
        }

        /*
         * Required for non-logged in users
         */
        wp_enqueue_style( 'dashicons' );

        $now      = time();
        $postDate = strtotime( $glossaryItem->post_date );
        $dateDiff = $now - $postDate;

        $daysDiff = floor( $dateDiff / (60 * 60 * 24) );

        $result = $daysDiff <= $maxDaysDiff;
        return $result;
    }

    /**
     * Adds abbreviations to parsing
     * @param type $addition
     * @param type $glossary_item
     * @return type
     */
    public static function addAbbreviationsToParsing( $addition, $glossary_item ) {
        $abbreviation = CMTT_Abbreviations::getAbbreviation( $glossary_item->ID );
        if ( !empty( $abbreviation ) ) {
            $addition .= '|' . preg_quote( str_replace( '\'', '&#39;', htmlspecialchars( trim( $abbreviation ), ENT_QUOTES, 'UTF-8' ) ), '/' );
        }
        return $addition;
    }

    /**
     * Adds support for non-space-separated terms
     * @param type $permalink
     * @param type $glossary_item
     * @return type
     */
    public static function addSupportForSpaceSepparated( $spaceSeparated ) {
        return get_option( 'cmtt_glossaryOnlySpaceSeparated', $spaceSeparated );
    }

    /**
     * Adds support for terms custom link
     * @param type $permalink
     * @param type $glossary_item
     * @return type
     */
    public static function changeTermPermalink( $permalink, $glossary_item ) {
        $custom_link = CMTT_Pro::_get_meta( '_glossary_custom_link', $glossary_item->ID );
        if ( !empty( $custom_link ) ) {
            $permalink = $custom_link;
        }
        return $permalink;
    }

    /**
     * Adds the class for each category term belongs to
     * @param type $additionalClass
     * @param type $glossary_item
     * @return type
     */
    public static function addCategoryClass( $additionalClass, $glossary_item ) {
//		$terms = wp_get_post_terms( $glossary_item->ID, 'glossary-categories' );
        $terms = CMTT_Pro::_get_term( 'glossary-categories', $glossary_item->ID );
        if ( !empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $additionalClass .= ' cmtt_' . $term;
            }
        }
        return $additionalClass;
    }

    /**
     * Add support for tooltip transparency
     * @param type $additionalClass
     * @param type $glossary_item
     * @return type
     */
    public static function addTermAdditionalClass( $additionalClass, $glossary_item ) {
        $tooltipTransparency = CMTT_Pro::_get_meta( '_cmtt_disable_tooltip_background', $glossary_item->ID );
        $additionalClass .= $tooltipTransparency ? 'transparent' : '';
        return $additionalClass;
    }

    /**
     * Add Glossary Translate output to Glossary Term tooltip
     * @param type $tooltipContent
     * @param type $glossary_item
     * @return string
     */
    public static function outputGlossaryTermTranslation( $tooltipContent, $glossary_item ) {
        $excludeGoogleApi = get_post_meta( $glossary_item->ID, '_cmtt_exclude_google_api', true );

        if ( CMTT_Google_API::enabled() && !$excludeGoogleApi ) {
            if ( CMTT_Google_API::term() ) {
                $tooltipContent = CMTT_Google_API::translate( $glossary_item->post_title, $glossary_item->post_title, CMTT_Google_API::COLUMN_TITLE );
            } else {
                $translatedContent = CMTT_Google_API::translate( $tooltipContent, $glossary_item->post_title, CMTT_Google_API::COLUMN_CONTENT );
                if ( CMTT_Google_API::together() ) {
                    $tooltipContent = $tooltipContent . '<br/><br/>' . $translatedContent;
                }
            }
        }

        return $tooltipContent;
    }

    /**
     * Add Merriam-Webster output to Glossary Term tooltip
     * @param type $tooltipContent
     * @param type $glossary_item
     * @return type
     */
    public static function addMWToTooltipContent( $tooltipContent, $glossary_item, $onGlossaryIndex = false ) {
        $excludeMerriamDictionaryApi = CMTT_Pro::_get_meta( '_cmtt_exclude_merriam_api', $glossary_item->ID );

        if ( CMTT_Mw_API::dictionary_enabled() && CMTT_Mw_API::dictionary_show_in_tooltip() && !$excludeMerriamDictionaryApi ) {
            $onlyOnEmpty = CMTT_Mw_API::dictionary_only_on_empty_content();
            if ( ($onlyOnEmpty && empty( $glossary_item->post_content )) || !$onlyOnEmpty ) {
                $tooltipContent .= CMTT_Mw_API::get_dictionary( $glossary_item->post_title, $onGlossaryIndex );
            }
        }

        $excludeMerriamThesaurusApi = CMTT_Pro::_get_meta( '_cmtt_exclude_merriam_thesaurus_api', $glossary_item->ID );
        if ( CMTT_Mw_API::thesaurus_enabled() && CMTT_Mw_API::thesaurus_show_in_tooltip() && !$excludeMerriamThesaurusApi ) {
            $onlyOnEmpty = CMTT_Mw_API::thesaurus_only_on_empty_content();
            if ( ($onlyOnEmpty && empty( $glossary_item->post_content )) || !$onlyOnEmpty ) {
                $tooltipContent .= CMTT_Mw_API::get_thesaurus( $glossary_item->post_title, $onGlossaryIndex );
            }
        }

        return $tooltipContent;
    }

    /**
     * Add Glosbe output to Glossary Term tooltip
     * @param type $tooltipContent
     * @param type $glossary_item
     * @return type
     */
    public static function addGlosbeToTooltipContent( $tooltipContent, $glossary_item, $onGlossaryIndex ) {
        $excludeMerriamDictionaryApi = FALSE; // TODO: Add support for exclude
        if ( CMTT_Glosbe_API::dictionary_enabled() && CMTT_Glosbe_API::dictionary_show_in_tooltip() && !$excludeMerriamDictionaryApi ) {
            $onlyOnEmpty = CMTT_Glosbe_API::dictionary_only_on_empty_content();
            if ( ($onlyOnEmpty && empty( $glossary_item->post_content )) || !$onlyOnEmpty ) {
                $tooltipContent .= CMTT_Glosbe_API::get_dictionary( $glossary_item->post_title, $onGlossaryIndex, $glossary_item, !$onGlossaryIndex );
            }
        }

        return $tooltipContent;
    }

    /**
     * Apply whitelist/blackist of terms to parsing
     * @global type $post
     * @param type $titleIndex
     * @param type $title
     * @return type
     */
    public static function applyParseCustomTermList( $titleIndex, $title ) {
        global $post;
        static $normalizedCustomSelectedTermsList = null;

        if ( null === $normalizedCustomSelectedTermsList ) {

            $customSelectedTermsList = self::getCurrentCustomTerms( $post->ID );
            if ( !is_array( $customSelectedTermsList ) ) {
                return;
            }

            function normalizeArrayItems( $title ) {
                global $caseSensitive;
                $title = str_replace( '&#039;', "’", preg_quote( htmlspecialchars( str_replace( '"', '', trim( $title ) ), ENT_QUOTES, 'UTF-8' ), '/' ) );
                if ( !$caseSensitive ) {
                    $title = mb_strtolower( $title );
                }
                return $title;
            }

            $normalizedCustomSelectedTermsList = array_map( 'normalizeArrayItems', $customSelectedTermsList );
        }

        $customSelectedTermsListType = self::getCurrentCustomTermsType( $post->ID );

        /*
         * Whitelist means we only parse for the terms being on the list
         * Blacklist means we don't parse for the terms being on the list
         */
        if ( !empty( $normalizedCustomSelectedTermsList ) && is_array( $normalizedCustomSelectedTermsList ) ) {
            $inArray = in_array( $titleIndex, $normalizedCustomSelectedTermsList );
            if ( ($customSelectedTermsListType == 'whitelist' && !$inArray) || ($customSelectedTermsListType == 'blacklist' && $inArray) ) {
                throw new GlossaryTooltipException( $title );
            }
        }
    }

    /**
     * Apply whitelist/blackist of terms to parsing
     * @global type $post
     * @param type $titleIndex
     * @param type $title
     * @return type
     */
    public static function applyCategoryFiltering( $currentItem, $titleIndex, $title ) {
        global $post;

        $category = get_post_meta( $post->ID, '_cmcrpr_selected_category', true );

        /*
         * Whitelist means we only parse for the terms being on the list
         * Blacklist means we don't parse for the terms being on the list
         */
        if ( !empty( $category ) ) {
            $postHasTerm           = FALSE;
            $currentItemCategories = wp_get_post_terms( $currentItem->ID, CMCRPR_Base::TAXONOMY );
            foreach ( $currentItemCategories as $key => $taxonomyTerm ) {
                if ( $taxonomyTerm->term_id == $category ) {
                    $postHasTerm = TRUE;
                    break;
                }
            }

            if ( !$postHasTerm ) {
                throw new CMCRPR_Exception( $title );
            }
        }
    }

    /**
     * Export additional data
     * @param type $exportDataRow
     * @return type
     */
    public static function addExportDataRowFields( $exportDataRow, $term ) {
        $cats       = array();
        $categories = get_the_terms( $term->ID, 'glossary-categories' );
        if ( !empty( $categories ) ) {
            foreach ( $categories as $category ) {
                $cats[] = $category->name;
            }
        }

        /*
         * Tags
         */
        $tags         = array();
        $glossaryTags = get_the_terms( $term->ID, 'glossary-tags' );
        if ( !empty( $glossaryTags ) ) {
            foreach ( $glossaryTags as $tagegory ) {
                $tags[] = $tagegory->name;
            }
        }

        $abbreviation = (string) CMTT_Abbreviations::getAbbreviation( $term->ID );

        $exportDataRow[] = (isset( $cats ) && !empty( $cats )) ? implode( ',', $cats ) : '';
        $exportDataRow[] = $abbreviation;
        $exportDataRow[] = (isset( $tags ) && !empty( $tags )) ? implode( ',', $tags ) : '';

        return $exportDataRow;
    }

    /**
     * Add new items to export header
     * @param type $exportHeaderRows
     * @return type
     */
    public static function addExportHeaderRowFields( $exportHeaderRows ) {
        $exportHeaderRowsNew = array_merge( $exportHeaderRows, array(
            'Categories',
            'Abbreviation',
            'Tags',
        ) );
        return $exportHeaderRowsNew;
    }

    /**
     * Adds additional fields to import
     */
    public static function importAdditionalInfo( $item, $update ) {
        /*
         * Categories
         */
        if ( $update > 0 && !empty( $item[ 6 ] ) ) {
            $categories = explode( ',', $item[ 6 ] );
            if ( !empty( $categories ) && is_array( $categories ) ) {
                $categoriesArr = array();
                foreach ( $categories as $category ) {
                    if ( is_numeric( $category ) ) {
                        $categoriesArr[] = $category;
                    } else {
                        $term = get_term_by( 'name', $category, 'glossary-categories' );
                        if ( $term ) {
                            /*
                             * Add the category
                             */
                            $categoriesArr[] = $term->term_id;
                        } else {
                            /*
                             * Create the category
                             */
                            $result = wp_insert_term( $category, 'glossary-categories' );
                            if ( !is_a( $result, 'WP_Error' ) ) {
                                $categoriesArr[] = $result[ 'term_id' ];
                            }
                        }
                    }
                }
                wp_set_object_terms( $update, $categoriesArr, 'glossary-categories' );
            }
        }
        /*
         * Abbreviation
         */
        if ( $update > 0 && !empty( $item[ 7 ] ) ) {
            CMTT_Abbreviations::setAbbreviation( $update, $item[ 7 ], true );
        }

        /*
         * Tags
         */
        if ( $update > 0 && !empty( $item[ 8 ] ) ) {
            $tags = explode( ',', $item[ 8 ] );
            if ( !empty( $tags ) && is_array( $tags ) ) {
                $tagsArr = array();
                foreach ( $tags as $tag ) {
                    if ( is_numeric( $tag ) ) {
                        $tagsArr[] = $tag;
                    } else {
                        $term = get_term_by( 'name', $tag, 'glossary-tags' );
                        if ( $term ) {
                            /*
                             * Add the tag
                             */
                            $tagsArr[] = $term->term_id;
                        } else {
                            /*
                             * Create the tag
                             */
                            $result = wp_insert_term( $tag, 'glossary-tags' );
                            if ( !is_a( $result, 'WP_Error' ) ) {
                                $tagsArr[] = $result[ 'term_id' ];
                            }
                        }
                    }
                }
                wp_set_object_terms( $update, $tagsArr, 'glossary-tags' );
            }
        }

        /*
         * Meta
         */
        if ( $update > 0 && !empty( $item[ 9 ] ) ) {
            $newMeta = json_decode( $item[ 9 ], true );
            if ( $newMeta !== NULL && is_array( $newMeta ) ) {
                foreach ( $newMeta as $key => $value ) {
                    if ( is_array( $value ) ) {
                        foreach ( $value as $subkey => $subvalue ) {
                            $value = maybe_unserialize( $subvalue );
                        }
                    }
                    update_post_meta( $update, $key, $value );
                }
            }
        }
    }

    /**
     * Adds the backlink content
     * @param type $backlinkContent
     * @return type
     */
    public static function addBacklinkContent( $backlinkContent, $post ) {
        $onlyOnEmptyDictionary = CMTT_Mw_API::dictionary_only_on_empty_content();
        if ( ($onlyOnEmptyDictionary && empty( $post->post_content )) || !$onlyOnEmptyDictionary ) {
            $excludeMerriamDictionaryApi = get_post_meta( $post->ID, '_cmtt_exclude_merriam_api', true );
            /*
             * MW Dictionary
             */
            $MWdictionary                = (CMTT_Mw_API::dictionary_enabled() && CMTT_Mw_API::dictionary_show_in_term() && !$excludeMerriamDictionaryApi) ? CMTT_Mw_API::get_dictionary( $post->post_title ) : '';
            $backlinkContent .= $MWdictionary;
        }

        $onlyOnEmptyThesaurus = CMTT_Mw_API::thesaurus_only_on_empty_content();
        if ( ($onlyOnEmptyThesaurus && empty( $post->post_content )) || !$onlyOnEmptyThesaurus ) {
            $excludeMerriamThesaurusApi = get_post_meta( $post->ID, '_cmtt_exclude_merriam_thesaurus_api', true );
            /*
             * MW Thesaurus
             */
            $MWthesaurus                = (CMTT_Mw_API::thesaurus_enabled() && CMTT_Mw_API::thesaurus_show_in_term() && !$excludeMerriamThesaurusApi) ? CMTT_Mw_API::get_thesaurus( $post->post_title ) : '';
            $backlinkContent .= $MWthesaurus;
        }

        $onlyOnEmptyGlosbeDictionary = CMTT_Glosbe_API::dictionary_only_on_empty_content();
        if ( ($onlyOnEmptyGlosbeDictionary && empty( $post->post_content )) || !$onlyOnEmptyGlosbeDictionary ) {
            $excludeGlosbeDictionaryApi = get_post_meta( $post->ID, '_cmtt_exclude_glosbe_dictionary_api', true );
            /*
             * Glosbe dictionary
             */
            $Glosbedictionary           = (CMTT_Glosbe_API::dictionary_enabled() && CMTT_Glosbe_API::dictionary_show_in_term() && !$excludeGlosbeDictionaryApi) ? CMTT_Glosbe_API::get_dictionary( $post->post_title ) : '';
            $backlinkContent .= $Glosbedictionary;
        }

        return $backlinkContent;
    }

    /**
     * Returns the list of posttypes for which we show the disable metabox
     * @param type $postTypes
     * @return type
     */
    public static function filterDisableMetaboxPosttypes( $postTypes ) {
        if ( get_option( 'cmtt_disable_metabox_all_post_types' ) ) {
            $postTypes = get_post_types();
        }
        return $postTypes;
    }

    public static function _renderCustomRelatedArticlesMetabox( $post ) {
        // VKost - function to edit custom related pairs
        ?>
        <div id='glossary-related-article-header'>
            <span id='glossary-related-article-header-name' style="width: 40%; font-weight: bold">Name => URL</span>
        </div>
        <div id='glossary-related-article-list'>
            <?php
            $customRelatedArticles = get_post_meta( $post->ID, '_glossary_related_article', true );

            if ( !empty( $customRelatedArticles ) && is_array( $customRelatedArticles ) ) {
                foreach ( $customRelatedArticles as $key => $relatedArticle ) {
                    if ( isset( $relatedArticle[ 'name' ] ) && isset( $relatedArticle[ 'url' ] ) ) {
                        echo '<div id="custom-related-article-' . $key . '" class="custom-related-article">';
                        echo '<input type="text" name="cmtt_related_article_name[]" style="width: 40%" id="cmtt_related_article_name" value="' . $relatedArticle[ 'name' ] . '">';
                        echo '<input type="text" name="cmtt_related_article_url[]" style="width: 50%" id="cmtt_related_article_url" value="' . $relatedArticle[ 'url' ] . '">';
                        echo '<a href="#" class="cmtt_related_article_remove">Remove</a>';
                        echo "</div>";
                    }
                }
            }

            echo '<div id="custom-related-article-' . count( $customRelatedArticles ) . '" class="custom-related-article">';
            echo '<input type="text" name="cmtt_related_article_name[]" style="width: 40%" id="cmtt_related_article_name" value="" placeholder="Name">';
            echo '<input type="text" name="cmtt_related_article_url[]" style="width: 50%" id="cmtt_related_article_url" value="" placeholder="http://">';
            echo '<a href="#" class="cmtt_related_article_remove">Remove</a>';
            echo '</div>';
            ?>
        </div>
        <a id="red-add-more-rows" href="#" onclick="jQuery.fn.add_new_replacement_row(); return false;">Add more rows</a>
        <p style="clear: left;">
            <span class="howto">
                <?php _e( 'Insert Name-URL pairs that will be shown before auto-generated Related articles. Both Name and URL must be supplied.', 'cm-tooltip-glossary' ); ?><br/>
                <strong><?php _e( 'Once you finish, save the post/page to store changes', 'cm-tooltip-glossary' ); ?></strong>
            </span>
        </p><?php
    }

    public static function _renderCustomTermLinkMetabox( $post ) {
        $customLink = get_post_meta( $post->ID, '_glossary_custom_link', true );
        if ( empty( $customLink ) ) {
            $customLink = '';
        }
        echo '<input type="text" name="glossary_custom_link" style="width: 100%" id="glossary_custom_link" value="' . $customLink . '">';
        ?>
        <p style="clear: left;">
            <span class="howto">
                <?php _e( 'Insert the custom URL of the Glossary Term. This URL will be used on Glossary Index and whenever the term is highlighed instead of the link to Glossary Term Page.', 'cm-tooltip-glossary' ); ?><br/>
                <strong><?php _e( 'Once you finish, "Publish" or "Update" to store changes', 'cm-tooltip-glossary' ); ?></strong>
            </span>
        </p>
        <?php
    }

    public static function _renderSelectedTermsForPageMetabox( $post ) {
        wp_nonce_field( plugin_basename( __FILE__ ), 'glossary_customterms_noncename' );
        $currentCustomTerms = is_array( self::getCurrentCustomTerms( $post->ID ) ) ? self::getCurrentCustomTerms( $post->ID ) : array();
        $customTermsList    = implode( ', ', $currentCustomTerms );

        $currentCustomTermsType = self::getCurrentCustomTermsType( $post->ID );
        ?>
        Separate custom terms for this post/page by comma eg. term1,term2,term3<br/>
        <div class="cm-showhide">
            <h5 class="cm-showhide-handle">More info &rArr;</h5>
            <div class="cm-showhide-content">
                <i>
                    Remember: if you decide to fill this field, and select the "Whitelist" then for this page only the terms which are on this list<strong> and </strong>
                    are defined in the Glossary will be highlightened in the content if found. If you choose "Blacklist" instead, selected terms will not be highlighted on the page.
                </i>
            </div>
        </div>
        <textarea name="glossary_custom_terms" style='width:100%'><?php echo $customTermsList; ?></textarea>
        <select name="glossary_custom_terms_type">
            <option value="whitelist" <?php selected( 'whitelist', $currentCustomTermsType ); ?> >Whitelist</option>
            <option value="blacklist"<?php selected( 'blacklist', $currentCustomTermsType ); ?> >Blacklist</option>
        </select>
        <?php
    }

    public static function _renderSelectedCatsForPageMetabox( $post ) {
        wp_nonce_field( plugin_basename( __FILE__ ), 'glossary_customcats_noncename' );
        $currentCustomCats = is_array( self::getCurrentCustomCats( $post->ID ) ) ? self::getCurrentCustomCats( $post->ID ) : array( 'all' );

        $currentCustomCatsType = self::getCurrentCustomCatsType( $post->ID );
        ?>
        Hold CTRL before clicking to select multiple categories, or to unselect the category.<br/>
        <div class="cm-showhide">
            <h5 class="cm-showhide-handle">More info &rArr;</h5>
            <div class="cm-showhide-content">
                <i>
                    Remember: if you decide to fill this field, and select the "Whitelist" then for this page only the terms from cats which are on this list<strong> and </strong>
                    are defined in the Glossary will be highlightened in the content if found. If you choose "Blacklist" instead, terms from selected cats will not be highlighted on the page.
                </i>
            </div>
        </div>
        <?php
        $catSelectOutput       = '';
        $cats                  = get_terms( 'glossary-categories' );

        if ( !empty( $cats ) ) {
            $catSelectOutput .= '<select name="glossary_custom_cats[]" multiple="true">';
            if ( !empty( $cats ) ) {
                foreach ( $cats as $cat ) {
                    $selected = in_array( $cat->term_id, $currentCustomCats ) ? 'selected="selected"' : '';
                    $catSelectOutput .= '<option ' . $selected . ' value="' . $cat->term_id . '">' . $cat->name . '</option>';
                }
            }
            $catSelectOutput .= '</select>';
        }
        echo $catSelectOutput;
        ?>
        <select name="glossary_custom_cats_type">
            <option value="whitelist" <?php selected( 'whitelist', $currentCustomCatsType ); ?> >Whitelist</option>
            <option value="blacklist"<?php selected( 'blacklist', $currentCustomCatsType ); ?> >Blacklist</option>
        </select>
        <?php
    }

    /**
     * Registers new metaboxes
     */
    public static function registerMetaboxes() {
        add_meta_box( 'glossary-custom-related-articles', 'Custom Related Articles', array( __CLASS__, '_renderCustomRelatedArticlesMetabox' ), 'glossary', 'normal', 'high' );
        add_meta_box( 'glossary-link-box', 'Custom term link', array( __CLASS__, '_renderCustomTermLinkMetabox' ), 'glossary', 'normal', 'high' );

        $defaultPostTypes         = get_option( 'cmtt_allowed_terms_metabox_all_post_types' ) ? get_post_types() : array( 'post', 'page' );
        $allowedTermsBoxPostTypes = apply_filters( 'cmtt_allowed_terms_metabox_posttypes', $defaultPostTypes );
        foreach ( $allowedTermsBoxPostTypes as $postType ) {
            add_meta_box( 'glossary-selected-terms-box', 'CM Tooltip - Filter Terms', array( __CLASS__, '_renderSelectedTermsForPageMetabox' ), $postType, 'side', 'high' );
            add_meta_box( 'glossary-selected-cats-box', 'CM Tooltip - Filter Cats', array( __CLASS__, '_renderSelectedCatsForPageMetabox' ), $postType, 'side', 'high' );
        }
    }

    public static function getCurrentCustomCats( $id ) {
        $customTerms = get_post_meta( $id, 'glossary_post_page_custom_cats', true );
        return $customTerms;
    }

    public static function getCurrentCustomCatsType( $id ) {
        $customTermsType = get_post_meta( $id, 'glossary_post_page_custom_cats_type', true );
        if ( !in_array( $customTermsType, array( 'blacklist', 'whitelist' ) ) ) {
            $customTermsType = 'whitelist';
        }
        return $customTermsType;
    }

    public static function getCurrentCustomTerms( $id ) {
        $customTerms = get_post_meta( $id, 'glossary_post_page_custom_terms', true );
        return $customTerms;
    }

    public static function getCurrentCustomTermsType( $id ) {
        $customTermsType = get_post_meta( $id, 'glossary_post_page_custom_terms_type', true );
        if ( !in_array( $customTermsType, array( 'blacklist', 'whitelist' ) ) ) {
            $customTermsType = 'whitelist';
        }
        return $customTermsType;
    }

    public static function cmtt_save_selected_terms_for_page( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;
        if ( !isset( $_POST[ 'glossary_customterms_noncename' ] ) || !wp_verify_nonce( $_POST[ 'glossary_customterms_noncename' ], plugin_basename( __FILE__ ) ) )
            return;
        if ( !current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) )
            return;

        $customTerms = filter_input( INPUT_POST, 'glossary_custom_terms' );
        if ( is_string( $customTerms ) ) {
            /*
             * Needed for str_getcsv to work properly
             */
            $customTerms     = str_replace( '\\"', '"\"', $customTerms );
            $dataCustomTerms = is_array( str_getcsv( $customTerms ) ) ? array_map( 'trim', array_filter( str_getcsv( $customTerms ) ) ) : array();
            update_post_meta( $post_id, 'glossary_post_page_custom_terms', $dataCustomTerms );
        }

        $customTermsType = filter_input( INPUT_POST, 'glossary_custom_terms_type' );
        update_post_meta( $post_id, 'glossary_post_page_custom_terms_type', $customTermsType );

        /*
         * Categories filter
         */
        $customCats = filter_input( INPUT_POST, 'glossary_custom_cats', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
        update_post_meta( $post_id, 'glossary_post_page_custom_cats', $customCats );

        $customCatsType = filter_input( INPUT_POST, 'glossary_custom_cats_type' );
        update_post_meta( $post_id, 'glossary_post_page_custom_cats_type', $customCatsType );
    }

    /**
     * Adds the disables metabox fields
     * @param array $metaboxFields
     * @return type
     */
    public static function addDisablesFields( $post ) {
        $drpage                     = get_post_meta( $post->ID, '_glossary_disable_related_terms_for_page', true );
        $disableRelatedTermsForPage = (int) (!empty( $drpage ) && $drpage == 1 );

        echo '<div class="cmtt_disables_fields_metabox">';
        echo '<label for="glossary_disable_related_terms_for_page" class="blocklabel">';
        echo '<input type="checkbox" name="glossary_disable_related_terms_for_page" id="glossary_disable_related_terms_for_page" value="1" ' . ($disableRelatedTermsForPage != 0 ? ' checked ' : '') . '>';
        echo '&nbsp;&nbsp;&nbsp;Don\'t show related terms for this post/page</label>';
        echo '</div>';
    }

    /**
     * Adds the metabox fields
     * @param array $metaboxFields
     * @return type
     */
    public static function addMetaboxFields( $metaboxFields ) {
        $newMetaboxFields = array_merge( $metaboxFields, array(
            'cmtt_exclude_parsing'                   => __( 'Don\'t parse this term', 'cm-tooltip-glossary' ),
            'cmtt_exclude_tooltip'                   => __( 'Hide tooltip for this term', 'cm-tooltip-glossary' ),
            'cmtt_disable_related_articles_for_term' => __( 'Disable related articles for this term', 'cm-tooltip-glossary' ),
            'cmtt_hide_from_index'                   => __( 'Hide term from Glossary Index', 'cm-tooltip-glossary' ),
            'cmtt_exclude_google_api'                => __( 'Disable Google API for this term', 'cm-tooltip-glossary' ),
            'cmtt_exclude_merriam_api'               => __( 'Disable Merriam-Webster Dictionary API for this term', 'cm-tooltip-glossary' ),
            'cmtt_exclude_merriam_thesaurus_api'     => __( 'Disable Merriam-Webster Thesaurus API for this term', 'cm-tooltip-glossary' ),
            'cmtt_exclude_glosbe_dictionary_api'     => __( 'Disable Glosbe Dictionary API for this term', 'cm-tooltip-glossary' )
        ) );

        return $newMetaboxFields;
    }

    /**
     * Saves additional post data
     * @param array $content
     * @return type
     */
    public static function saveSelectedTermsForPage( $post_id, $post ) {
        /*
         * Add the call to function saving the selected custom terms for post/page
         */
        $defaultPostTypes         = get_option( 'cmtt_allowed_terms_metabox_all_post_types' ) ? get_post_types() : array( 'post', 'page' );
        $allowedTermsBoxPostTypes = apply_filters( 'cmtt_allowed_terms_metabox_posttypes', $defaultPostTypes );
        if ( isset( $post[ 'post_type' ] ) && in_array( $post[ 'post_type' ], $allowedTermsBoxPostTypes ) ) {
            self::cmtt_save_selected_terms_for_page( $post_id );
        }
    }

    /**
     * Saves additional post data
     * @param array $content
     * @return type
     */
    public static function saveDisableRelatedPosts( $post_id, $post ) {
        $postType            = isset( $post[ 'post_type' ] ) ? $post[ 'post_type' ] : '';
        $disableBoxPostTypes = apply_filters( 'cmtt_disable_metabox_posttypes', array( 'glossary', 'post', 'page' ) );
        if ( in_array( $postType, $disableBoxPostTypes ) ) {
            /*
             * Disables the parsing of the given page
             */
            $disableRelatedForPage = 0;
            if ( isset( $post[ "glossary_disable_related_terms_for_page" ] ) && $post[ "glossary_disable_related_terms_for_page" ] == 1 ) {
                $disableRelatedForPage = 1;
            }
            update_post_meta( $post_id, '_glossary_disable_related_terms_for_page', $disableRelatedForPage );
        }
    }

    /**
     * Saves additional post data
     * @param array $content
     * @return type
     */
    public static function flushSingleMWCache( $post_id, $post ) {
        if ( isset( $post[ 'post_type' ] ) && 'glossary' === $post[ 'post_type' ] && !empty( $post[ 'cmtt_flush_thirdparty' ] ) ) {
            $slug = basename( get_permalink( $post_id ) );
            CMTT_Mw_API::flushTermCache( $slug );
            return;
        }
    }

    /**
     * Saves additional post data
     * @param array $content
     * @return type
     */
    public static function saveAdditionalPostData( $post_id, $post ) {
        if ( isset( $post[ "glossary_custom_link" ] ) ) {
            update_post_meta( $post_id, '_glossary_custom_link', $post[ "glossary_custom_link" ] );
        }
        /*
         * Writing custom related articles in post
         */
        if ( isset( $post[ 'cmtt_related_article_name' ] ) && is_array( $post[ 'cmtt_related_article_name' ] ) ) {
            $relatedArticleNames = $post[ 'cmtt_related_article_name' ];
            $relatedArticles     = array();
            delete_post_meta( $post_id, '_glossary_related_article' );
            foreach ( $relatedArticleNames as $key => $ra_name ) {
                if ( $ra_name != '' && $post[ 'cmtt_related_article_url' ][ $key ] != '' ) {
                    $relatedArticles[] = array( 'name' => $ra_name, 'url' => $post[ 'cmtt_related_article_url' ][ $key ] );
                }
            }
            add_post_meta( $post_id, '_glossary_related_article', $relatedArticles );
        }
    }

    /**
     * Add the "API" tab content
     * @param array $content
     * @return type
     */
    public static function addAPITabContent( $content ) {
        ob_start();
        ?>
        <div class="block">
            <h3>API - Google Translate</h3>
            <table class="floated-form-table form-table">
                <tr valign="top">
                    <th scope="row">Enabled?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltip3RDGoogleEnabled" value="0" />
                        <input type="checkbox" name="cmtt_tooltip3RDGoogleEnabled" <?php checked( true, get_option( 'cmtt_tooltip3RDGoogleEnabled', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">Enabling Google Translate API</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Display translation and original text together?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltip3RDGoogleTogether" value="0" />
                        <input type="checkbox" name="cmtt_tooltip3RDGoogleTogether" <?php checked( true, get_option( 'cmtt_tooltip3RDGoogleTogether', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">Will display both translation and original test in tooltip, if not checked will only display translation</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Display translated term name in tooltip?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltip3RDGoogleTerm" value="0" />
                        <input type="checkbox" name="cmtt_tooltip3RDGoogleTerm" <?php checked( true, get_option( 'cmtt_tooltip3RDGoogleTerm', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">Will display on tooltip translated term name only and will not display term content</td>
                </tr>

                <tr valign="top">
                    <th scope="row">API Key:</th>
                    <td><input  type="text" name="cmtt_tooltip3RDGoogleApiKey" value="<?php echo get_option( 'cmtt_tooltip3RDGoogleApiKey', '' ); ?>" /></td>
                    <td colspan="2" class="cmtt_field_help_container">You need <a href="https://developers.google.com/translate/v2/pricing" target="_blank">Google API Key</a></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Source Language:</th>
                    <td>
                        <select name="cmtt_tooltip3RDGoogleSource">
                            <option value="-1">Select language</option>
                            <?php foreach ( CMTT_Google_API::getLanguages() as $lang_num => $lang ): ?>
                                <option value="<?php echo $lang_num; ?>" <?php selected( $lang_num, get_option( 'cmtt_tooltip3RDGoogleSource' ) ); ?> ><?php echo $lang[ 'name' ]; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td colspan="2" class="cmtt_field_help_container"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Target Language:</th>
                    <td>
                        <select name="cmtt_tooltip3RDGoogleTarget">
                            <option value="-1">Select language</option>
                            <?php foreach ( CMTT_Google_API::getLanguages() as $lang_num => $lang ): ?>
                                <option value="<?php echo $lang_num; ?>" <?php selected( $lang_num, get_option( 'cmtt_tooltip3RDGoogleTarget' ) ); ?> ><?php echo $lang[ 'name' ]; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td colspan="2" class="cmtt_field_help_container"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Test Google API</th>
                    <td><input type="button" value="Test Google API" id="cmtt-test-google-api" class="button"/></td>
                    <td colspan="2" class="cmtt_field_help_container">Test the Google API - result will be displayed in a popup.</td>
                </tr>
            </table>
        </div>
        <div class="block">
            <h3>API - Merriam - Webster Dictionary &amp; Thesaurus</h3>
            <table class="floated-form-table form-table">
                <tr valign="top">
                    <th scope="row">Enable Dictionary?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltip3RD_MWDictionaryEnabled" value="0" />
                        <input type="checkbox" name="cmtt_tooltip3RD_MWDictionaryEnabled" <?php checked( true, get_option( 'cmtt_tooltip3RD_MWDictionaryEnabled', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">Show definitions from MW Dictionary</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Only show Dictionary when content is empty?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltip3RD_MWDictionaryAutoContent" value="0" />
                        <input type="checkbox" name="cmtt_tooltip3RD_MWDictionaryAutoContent" <?php checked( true, get_option( 'cmtt_tooltip3RD_MWDictionaryAutoContent', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">If you select this option and the Dictionary is enabled then the Dictionary will only be shown when the content of the term is empty.
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Dictionary API Key:</th>
                    <td><input size="32" type="text" name="cmtt_tooltip3RD_MWDictionaryApiKey" value="<?php echo get_option( 'cmtt_tooltip3RD_MWDictionaryApiKey', '' ); ?>" /></td>
                    <td colspan="2" class="cmtt_field_help_container">You need <a href="http://dictionaryapi.com/products/index.htm" target="_blank">Merriam-Webster Dicitonary API Key</a></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Show Dictionary data in Tooltip?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltip3RD_MWDictionaryTooltip" value="0" />
                        <input type="checkbox" name="cmtt_tooltip3RD_MWDictionaryTooltip" <?php checked( true, get_option( 'cmtt_tooltip3RD_MWDictionaryTooltip', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">Show Dictionary in tooltip</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Show Dictionary data in Glossary term display?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltip3RD_MWDictionaryTerm" value="0" />
                        <input type="checkbox" name="cmtt_tooltip3RD_MWDictionaryTerm" <?php checked( true, get_option( 'cmtt_tooltip3RD_MWDictionaryTerm', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">Show Dictionary in Glossary term display (will remove all other content which currently exist for tooltip)</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Test Dictionary API</th>
                    <td><input type="button" value="Test Dictionary API" id="cmtt-test-dictionary-api" class="button"/></td>
                    <td colspan="2" class="cmtt_field_help_container">Test the Thesaurus API - result will be displayed in a popup.</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Enable Thesaurus?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltip3RD_MWThesaurusEnabled" value="0" />
                        <input type="checkbox" name="cmtt_tooltip3RD_MWThesaurusEnabled" <?php checked( true, get_option( 'cmtt_tooltip3RD_MWThesaurusEnabled', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">Show definitions from MW Thesaurus</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Only show Thesaurus when content is empty?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltip3RD_MWThesaurusAutoContent" value="0" />
                        <input type="checkbox" name="cmtt_tooltip3RD_MWThesaurusAutoContent" <?php checked( true, get_option( 'cmtt_tooltip3RD_MWThesaurusAutoContent', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">If you select this option and the Thesaurus is enabled then the Thesaurus output will only be shown when the content of the term is empty.
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Thesaurus API Key:</th>
                    <td><input size="32" type="text" name="cmtt_tooltip3RD_MWThesaurusApiKey" value="<?php echo get_option( 'cmtt_tooltip3RD_MWThesaurusApiKey', '' ); ?>" /></td>
                    <td colspan="2" class="cmtt_field_help_container">You need <a href="http://dictionaryapi.com/products/index.htm" target="_blank">Merriam-Webster Thesaurus API Key</a></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Show Thesaurus data in Tooltip?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltip3RD_MWThesaurusTooltip" value="0" />
                        <input type="checkbox" name="cmtt_tooltip3RD_MWThesaurusTooltip" <?php checked( true, get_option( 'cmtt_tooltip3RD_MWThesaurusTooltip', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">Show Thesaurus in tooltip</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Show Thesaurus data in Glossary term display?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltip3RD_MWThesaurusTerm" value="0" />
                        <input type="checkbox" name="cmtt_tooltip3RD_MWThesaurusTerm" <?php checked( true, get_option( 'cmtt_tooltip3RD_MWThesaurusTerm', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">Show Thesaurus in Glossary term display</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Test Thesaurus API</th>
                    <td><input type="button" value="Test Thesaurus API" id="cmtt-test-thesaurus-api" class="button"/></td>
                    <td colspan="2" class="cmtt_field_help_container">Test the Thesaurus API - result will be displayed in a popup.</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Flush the Cache?</th>
                    <td><input type="submit" name="cmtt_tooltip3RD_MWFlushcache" value="Flush cache" class="button"/></td>
                    <td colspan="2" class="cmtt_field_help_container">Flush the database of the 3rd party definitions. <strong>Warning! The glossary will load significantly slower until the cache is filled again.</strong></td>
                </tr>
            </table>
        </div>
        <div class="block">
            <h3>API - Glosbe Dictionary</h3>
            <table class="floated-form-table form-table">
                <tr valign="top">
                    <th scope="row">Enable Dictionary?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltip3RD_GlosbeDictionaryEnabled" value="0" />
                        <input type="checkbox" name="cmtt_tooltip3RD_GlosbeDictionaryEnabled" <?php checked( true, get_option( 'cmtt_tooltip3RD_GlosbeDictionaryEnabled', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">Show definitions from Glosbe Dictionary</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Only show Dictionary when content is empty?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltip3RD_GlosbeDictionaryAutoContent" value="0" />
                        <input type="checkbox" name="cmtt_tooltip3RD_GlosbeDictionaryAutoContent" <?php checked( true, get_option( 'cmtt_tooltip3RD_GlosbeDictionaryAutoContent', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">If you select this option and the Dictionary is enabled then the Dictionary will only be shown when the content of the term is empty.
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Show Dictionary data in Tooltip?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltip3RD_GlosbeDictionaryTooltip" value="0" />
                        <input type="checkbox" name="cmtt_tooltip3RD_GlosbeDictionaryTooltip" <?php checked( true, get_option( 'cmtt_tooltip3RD_GlosbeDictionaryTooltip', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">Show Dictionary in tooltip</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Show Dictionary data in Glossary term display?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltip3RD_GlosbeDictionaryTerm" value="0" />
                        <input type="checkbox" name="cmtt_tooltip3RD_GlosbeDictionaryTerm" <?php checked( true, get_option( 'cmtt_tooltip3RD_GlosbeDictionaryTerm', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">Show Dictionary in Glossary term display (will remove all other content which currently exist for tooltip)</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Test Dictionary API</th>
                    <td><input type="button" value="Test Dictionary API" id="cmtt_test_glosbe_dictionary_api" class="button"/></td>
                    <td colspan="2" class="cmtt_field_help_container">Test the Thesaurus API - result will be displayed in a popup.</td>
                </tr>
            </table>
        </div>
        <?php
        $content .= ob_get_clean();
        return $content;
    }

    /**
     * Add the "Glossary Replacement" tab content
     * @param array $content
     * @return type
     */
    public static function addGlossaryReplacementTabContent( $content ) {
        ob_start();
        ?>
        <div class="block">
            <h3>Replacements Settings</h3>
            <p>This section of the settings allows you to replace content on your pages. You can set the string which should be found and the replaced string that should be placed instead. This is only working once page is displayed and does not change the content on the database. it is a valuable tool once you want to replace html code or term found in pages with different terms</p>
            <?php
            $repl = get_option( 'cmtt_glossary_replacements', array() );
            CMTT_Glossary_Replacement::_outputReplacements( $repl, TRUE );
            ?>
        </div>
        <?php
        $content .= ob_get_clean();
        return $content;
    }

    /**
     * Add the new settings tabs
     * @param array $settingsTabs
     * @return type
     */
    public static function addSettingsTabs( $settingsTabs ) {
        $settingsTabs[ '5' ] = 'API';
        $settingsTabs[ '8' ] = 'Glossary Replacement';
        return $settingsTabs;
    }

    /**
     * Flush the Merriam-Webster cache
     */
    public static function flushMWCache( $post, $messages ) {
        if ( isset( $post[ 'cmtt_tooltip3RD_MWFlushcache' ] ) ) {
            CMTT_Mw_API::flushDatabase();
            $messages = '3rd party definitions cache database has been flushed';
        }
    }

    /**
     * Add the new listnav arguments
     * @param array $listnavArgs
     * @return type
     */
    public static function addListnavArgs( $listnavArgs ) {
        $listnavArgs[ 'nonLatinSeparate' ] = get_option( 'cmtt_index_nonLatinLetters' );
        return $listnavArgs;
    }

    /**
     * Funtion outputs the categories control
     *
     * @param type $shortcode
     * @param type $disable_listnav
     * @return string
     */
    public static function outputCategories( $shortcodeAtts, $glossary_query ) {
        $catSelectOutput = '';
        $currentCategory = (!empty( $shortcodeAtts[ 'cat' ] )) ? $shortcodeAtts[ 'cat' ] : array( 'all' );

        if ( empty( $currentCategory ) ) {
            $currentCategory = array( 'all' );
        }
        if ( !is_array( $currentCategory ) ) {
            $currentCategory = explode( ',', $currentCategory );
        }
        if ( is_array( $currentCategory ) ) {
            $currentCategoryString = implode( ',', $currentCategory );
        }
        if ( !empty( $shortcodeAtts[ 'freeze_cat' ] ) && $currentCategory !== array( 'all' ) ) {
            $catSelectOutput .= '<input type="hidden" class="glossary-categories" name="cat" value="' . $currentCategoryString . '">';
            $catSelectOutput .= '<input type="hidden" class="glossary-freeze-categories" name="freeze_cat" value="1">';
        } else {

            /*
             * Code allows to display only the relevant categories meaning that only the categories of the currently displayed items will be displayed.
             */
            $showOnlyRelevant = isset( $shortcodeAtts[ 'only_relevant_cats' ] ) ? $shortcodeAtts[ 'only_relevant_cats' ] : FALSE;
            if ( $showOnlyRelevant ) {
                $args      = isset( CMTT_Pro::$lastQueryDetails[ 'nopaging_args' ] ) ? CMTT_Pro::$lastQueryDetails[ 'nopaging_args' ] : array();
                $posts_ids = array();

                if ( !empty( $args ) && is_array( $args ) ) {
                    $glossaryIndex = CMTT_Pro::getGlossaryItems( $args );
                    foreach ( $glossaryIndex as $value ) {
                        $posts_ids[] = $value->ID;
                    }
                    $cats = wp_get_object_terms( $posts_ids, 'glossary-categories', array( 'hide_empty' => 1, 'orderby' => 'name', 'order' => 'ASC' ) );
                }
            } else {
                // Getting Glossary categories
                $cats = get_terms( 'glossary-categories' );
            }

            $glossaryCategoriesDisplayMethod = get_option( 'cmtt_glossaryCategoriesDisplayType', '1' );
            $allCategoriesLabel              = __( get_option( 'cmtt_glossary_AllCategoriesLabel', 'All categories' ), 'cm-tooltip-glossary' );

            if ( !empty( $cats ) ) {
                switch ( $glossaryCategoriesDisplayMethod ) {
                    case '0': {
                            $catSelectOutput .= '<select id="glossary-categories" class="glossary-categories">';
                            $catSelectOutput .= '<option value="all">' . $allCategoriesLabel . '</option>';
                            if ( !empty( $cats ) ) {
                                foreach ( $cats as $cat ) {
//									$selected = selected( true, ( is_numeric( $currentCategory ) && $currentCategory == $cat->term_id || $currentCategory == $cat->name ), false );
                                    $selected = is_array( $currentCategory ) && in_array( $cat->term_id, $currentCategory ) ? 'selected="selected"' : '';
                                    $catSelectOutput .= '<option ' . $selected . ' value="' . $cat->term_id . '">' . $cat->name . '</option>';
                                }
                            }
                            $catSelectOutput .= '</select>';
                            break;
                        }
                    default:
                    case '1': {
                            $categoriesLabel = __( get_option( 'cmtt_glossaryCategoriesLabel' ), 'cm-tooltip-glossary' );
                            $allClass        = ($currentCategoryString == 'all') ? 'selected' : '';

                            $catSelectOutput.= '<input type="hidden" class="glossary-categories" name="cat" value="' . $currentCategoryString . '">';
                            $catSelectOutput.= '<div class="cmtt-categories-filter">' . $categoriesLabel;
                            $catSelectOutput.= ' <a class="cmtt-glossary-category ' . $allClass . '">' . str_replace( ' ', '&nbsp;', $allCategoriesLabel ) . '</a>';

                            if ( !empty( $cats ) ) {
                                foreach ( $cats as $cat ) {
//									$isCurrentCategory	 = ( is_numeric( $currentCategory ) && $currentCategory == $cat->term_id || $currentCategory == $cat->name );
                                    $isCurrentCategory = is_array( $currentCategory ) && in_array( $cat->term_id, $currentCategory );
                                    $selectedCateClass = $isCurrentCategory ? 'selected' : '';
                                    $catSelectOutput.=' <a class="cmtt-glossary-category ' . $selectedCateClass . '" data-category-name="' . $cat->term_id . '">' . str_replace( ' ', '&nbsp;', $cat->name ) . '</a>';
                                }
                            }
                            $catSelectOutput.='</div>';

                            break;
                        }
                }
            }
        }
        return $catSelectOutput;
    }

    /**
     * Outputs the top filter
     * @param type $content
     * @param type $shortcodeAtts
     * @param type $glossary_query
     * @return type
     */
    public static function outputBeforeListnav( $content, $shortcodeAtts, $glossary_query ) {
        $additionalClass   = (!empty( $shortcodeAtts[ 'search_term' ] )) ? 'search' : '';
        $showSearchButton  = (!empty( $shortcodeAtts[ 'show_search' ] )) ? $shortcodeAtts[ 'show_search' ] : 0;
        $exactSearch       = (!empty( $shortcodeAtts[ 'exact_search' ] )) ? $shortcodeAtts[ 'exact_search' ] : 0;
        $searchLabel       = __( get_option( 'cmtt_glossary_SearchLabel', 'Search:' ), 'cm-tooltip-glossary' );
        $searchPlaceholder = __( get_option( 'cmtt_glossary_SearchPlaceholder', '' ), 'cm-tooltip-glossary' );
        $searchButtonLabel = __( get_option( 'cmtt_glossary_SearchButtonLabel', 'Search' ), 'cm-tooltip-glossary' );
        $clearLabel        = __( get_option( 'cmtt_glossary_ClearLabel', '(clear)' ), 'cm-tooltip-glossary' );
        $searchTerm        = isset( $shortcodeAtts[ 'search_term' ] ) ? $shortcodeAtts[ 'search_term' ] : '';
        ob_start();
        ?>
        <div class="progress-indicator">
            <img src="<?php echo self::$cssPath; ?>images/ajax-loader.gif" alt="AJAX progress indicator" />
        </div>
        <div class="glossary_top_filter">
            <div class="left">
                <?php
                if ( $showSearchButton ) :
                    $searchHelp = __( get_option( 'cmtt_glossarySearchHelp', 'The search returns the partial search for the given query from both the term title and description. So it will return the results even if the given query is part of the word in the description.' ), 'cm-tooltip-glossary' );
                    ?>
                    <?php if ( !empty( $searchHelp ) ) : ?>
                        <div class="cmtt_help glossary-search-helpitem" data-cmtooltip="<?php echo $searchHelp ?>"></div>
                    <?php endif; ?>
                    <span class="glossary-search-label"><?php echo $searchLabel ?></span>
                    <input value="<?php echo esc_attr( $searchTerm ) ?>" placeholder="<?php echo esc_attr( $searchPlaceholder ); ?>" class="glossary-search-term <?php echo $additionalClass ?>" name="glossary-search-term" id="glossary-search-term" />
                    <input type="button" value="<?php echo $searchButtonLabel ?>" id="glossary-search" class="glossary-search button" />
                    <a class="glossary-search-clear" title="<?php _e( 'Clear the input', 'cm-tooltip-glossary' ); ?>"><?php echo $clearLabel; ?></a>
                <?php endif; ?>
                <?php echo self::outputCategories( $shortcodeAtts, $glossary_query ); ?>
            </div>
            <?php echo apply_filters( 'cmtt_glossary_index_additional_filters_html', '', $shortcodeAtts ); ?>
        </div>
        <input type="hidden" class="cmtt-attribute-field" name="disable_listnav" value="<?php echo (int) (isset( $shortcodeAtts[ 'disable_listnav' ] ) && $shortcodeAtts[ 'disable_listnav' ]); ?>" />
        <input type="hidden" class="cmtt-attribute-field" name="exact_search" value="<?php echo (int) $exactSearch; ?>" />
        <input type="hidden" class="cmtt-attribute-field" name="show_search" value="<?php echo (int) $showSearchButton; ?>" />
        <input type="hidden" class="glossary-hide-terms" name="glossary-hide-terms" value="<?php echo (int) (isset( $shortcodeAtts[ 'hide_terms' ] ) && $shortcodeAtts[ 'hide_terms' ]); ?>" />
        <input type="hidden" class="glossary-hide-abbrevs" name="glossary-hide-abbrevs" value="<?php echo (int) (isset( $shortcodeAtts[ 'hide_abbrevs' ] ) && $shortcodeAtts[ 'hide_abbrevs' ]); ?>" />
        <input type="hidden" class="glossary-hide-synonyms" name="glossary-hide-synonyms" value="<?php echo (int) (isset( $shortcodeAtts[ 'hide_synonyms' ] ) && $shortcodeAtts[ 'hide_synonyms' ]); ?>" />
        <?php
        $content .= ob_get_clean();
        return $content;
    }

    /**
     * Add Synonyms to Glossary Index
     * @param string $glossariIndexContentArr
     * @param type $glossaryItem
     * @param type $preItemTitleContent
     * @param type $postItemTitleContent
     * @return string
     */
    public static function addSynonymsToGlossaryIndex( $glossariIndexContentArr, $glossaryItem, $preItemTitleContent,
                                                       $postItemTitleContent, $shortcodeAtts ) {
        $addSynonymsToTheGlossaryIndex = get_option( 'cmtt_glossarySynonymsInIndex', 1 );
        $hideSynonyms                  = !empty( $shortcodeAtts[ 'hide_synonyms' ] );

        if ( $hideSynonyms || !$addSynonymsToTheGlossaryIndex ) {
            return $glossariIndexContentArr;
        }
        /*
         * Add synonyms to the list
         * @since 2.6.8
         */
        $synonyms = CMTT_Synonyms::getSynonymsArr( $glossaryItem->ID );
        if ( !empty( $synonyms ) && is_array( $synonyms ) ) {
            foreach ( $synonyms as $synonym ) {
                $glossariIndexContentArr[ mb_strtolower( $synonym ) ] = $preItemTitleContent . $synonym . $postItemTitleContent;
            }
        }
        return $glossariIndexContentArr;
    }

    /**
     * Add Abbreviations to Glossary Index
     * @param string $glossariIndexContentArr
     * @param type $glossaryItem
     * @param type $preItemTitleContent
     * @param type $postItemTitleContent
     * @param array $shortcodeAtts
     * @return string
     */
    public static function addAbbreviationsToGlossaryIndex( $glossariIndexContentArr, $glossaryItem,
                                                            $preItemTitleContent, $postItemTitleContent, $shortcodeAtts ) {
        $addAbbreviationsToTheGlossaryIndex = get_option( 'cmtt_glossaryAbbreviationsInIndex', 1 );
        $hideAbbreviations                  = !empty( $shortcodeAtts[ 'hide_abbrevs' ] );

        if ( $hideAbbreviations || !$addAbbreviationsToTheGlossaryIndex ) {
            return $glossariIndexContentArr;
        }
        /*
         * Add abbreviation to the list as a separate item
         * @since 2.4.5
         */
        $abbreviation = CMTT_Abbreviations::getAbbreviation( $glossaryItem->ID );
        if ( !empty( $abbreviation ) ) {
            $glossariIndexContentArr[ mb_strtolower( $abbreviation ) ] = $preItemTitleContent . $abbreviation . $postItemTitleContent;
        }
        return $glossariIndexContentArr;
    }

    /**
     * Outputs only the Google Translate term in Glossary Index page
     * @param type $glossaryItemContent
     * @param type $glossaryItem
     * @return type
     */
    public static function outputGlossaryIndexGoogleTranslation( $glossaryItemContent, $glossaryItem ) {
        if ( CMTT_Google_API::enabled() ) {
            $glossaryItemContentTranslated = CMTT_Google_API::translate( $glossaryItemContent, $glossaryItem->post_title, CMTT_Google_API::COLUMN_CONTENT, TRUE );
            if ( CMTT_Google_API::together() ) {
                $glossaryItemContent = $glossaryItemContent . '<br/><br/>' . $glossaryItemContentTranslated;
            }
        }
        return $glossaryItemContent;
    }

    /**
     * Outputs only the Google Translate term in Glossary Index page
     * @param type $glossaryItemContent
     * @param type $glossaryItem
     * @return type
     */
    public static function outputGlossaryIndexGoogleTermOnly( $glossaryItemContent, $glossaryItem ) {
        if ( CMTT_Google_API::enabled() && CMTT_Google_API::term() ) {
            $glossaryItemContent = CMTT_Google_API::translate( $glossaryItem->post_title, $glossaryItem->post_title, CMTT_Google_API::COLUMN_TITLE, TRUE );
            remove_filter( 'cmtt_glossary_index_tooltip_content', array( 'CMTT_Glossary_Index', 'getTheTooltipContentBase' ), 10, 2 );
            remove_filter( 'cmtt_glossary_index_tooltip_content', array( 'CMTT_Pro', 'cmtt_glossary_parse_strip_shortcodes' ), 20, 2 );
            remove_filter( 'cmtt_glossary_index_tooltip_content', array( 'CMTT_Pro', 'cmtt_glossary_filterTooltipContent' ), 30, 2 );
        }
        return $glossaryItemContent;
    }

    /**
     * Allows to add Merriam-Webster Dictionary to Glossary Index term
     * @param type $glossaryItemContent
     * @param type $glossaryItem
     * @return type
     */
    public static function addGlossaryIndexMerriamWebsterDictionary( $glossaryItemContent, $glossaryItem ) {
        $excludeMerriamDictionaryApi = get_post_meta( $glossaryItem->ID, '_cmtt_exclude_merriam_api', true );
        if ( CMTT_Mw_API::dictionary_enabled() && CMTT_Mw_API::dictionary_show_in_tooltip() && !$excludeMerriamDictionaryApi ) {
            $onlyOnEmpty = CMTT_Mw_API::dictionary_only_on_empty_content();
            if ( ($onlyOnEmpty && empty( $glossaryItem->post_content )) || !$onlyOnEmpty ) {
                $glossaryItemContent .= CMTT_Mw_API::get_dictionary( $glossaryItem->post_title, true );
            }
        }

        return $glossaryItemContent;
    }

    /**
     * Allows to add Merriam-Webster Thesaurus to Glossary Index term
     * @param type $glossaryItemContent
     * @param type $glossaryItem
     * @return type
     */
    public static function addGlossaryIndexMerriamWebsterThesaurus( $glossaryItemContent, $glossaryItem ) {
        $excludeMerriamThesaurusApi = get_post_meta( $glossaryItem->ID, '_cmtt_exclude_merriam_thesaurus_api', true );
        if ( CMTT_Mw_API::thesaurus_enabled() && CMTT_Mw_API::thesaurus_show_in_tooltip() && !$excludeMerriamThesaurusApi ) {
            $onlyOnEmpty = CMTT_Mw_API::thesaurus_only_on_empty_content();
            if ( ($onlyOnEmpty && empty( $glossaryItem->post_content )) || !$onlyOnEmpty ) {
                $glossaryItemContent .= CMTT_Mw_API::get_thesaurus( $glossaryItem->post_title );
            }
        }

        return $glossaryItemContent;
    }

    /**
     * Function strips the shortcodes if the option is set
     * @param type $content
     * @return type
     */
    public static function stripDescriptionShortcode( $glossaryItemDesc, $glossary_item, $glossaryIndexStyle,
                                                      $shortcodeAtts ) {
        if ( get_option( 'cmtt_glossaryIndexDescStripShortcode' ) == 1 ) {
            $glossaryItemDesc = strip_shortcodes( $glossaryItemDesc );
        }
        return $glossaryItemDesc;
    }

    /**
     * Outputs the glossary item description
     * @param type $glossaryItemDesc
     * @param type $glossary_item
     * @param type $glossaryIndexStyle
     * @return type
     */
    public static function outputGlossaryIndexItemDesc( $glossaryItemDesc, $glossary_item, $glossaryIndexStyle,
                                                        $shortcodeAtts ) {
        if ( !empty( $shortcodeAtts[ 'no_desc' ] ) ) {
            return '';
        }

        /* 	ML */
        if ( in_array( $glossaryIndexStyle, array( 'modern-table', 'classic-definition', 'expand-style' ) ) ) {
            $glossaryItemDesc = $glossary_item->post_content;
            if ( empty( $glossaryItemDesc ) ) {
                $glossaryItemDesc = '&nbsp;';
            }
        } else if ( in_array( $glossaryIndexStyle, array( 'classic-excerpt' ) ) ) {
            $glossaryItemDesc = $glossary_item->post_excerpt;
        } else {
            $glossaryItemDesc = '';
        }

        if ( in_array( $glossaryIndexStyle, array( 'modern-table', 'classic-definition' ) ) ) {
            if ( !empty( $glossaryItemDesc ) ) {
                $stripTags = (int) get_option( 'cmtt_glossaryTooltipDescStripTags', 1 );
                if ( $stripTags ) {
                    $glossaryItemDesc = strip_tags( $glossaryItemDesc );
                }
                $glossaryDescLength = (int) get_option( 'cmtt_glossaryTooltipDescLength' );
                $permalink          = get_permalink( $glossary_item->ID );
                if ( $glossaryDescLength && $glossaryDescLength >= 30 ) {
                    $endingHtml       = get_option( 'cmtt_glossaryIndexTruncateLabel', __( '(...)' ) );
                    $glossaryItemDesc = cminds_truncate( html_entity_decode( $glossaryItemDesc ), $glossaryDescLength, $endingHtml );

                    $showReadMoreLink      = (int) get_option( 'cmtt_glossaryIndexDescReadMore', 0 );
                    $showReadMoreLinkLabel = get_option( 'cmtt_glossaryIndexDescReadMoreLabel', __( 'Read More' ) );
                    if ( $showReadMoreLink ) {
                        $linkHtml = ' - <a class="glossary-read-more-link" href="' . $permalink . '">' . $showReadMoreLinkLabel . '</a>';
                        $glossaryItemDesc .= $linkHtml;
                    }
                }
            }
        }

        return $glossaryItemDesc;
    }

    /**
     * Adds the Related Items to the glossary description
     * @param type $glossaryItemDesc
     * @param type $glossary_item
     * @param type $glossaryIndexStyle
     * @return type
     */
    public static function addGlossaryIndexDescRelated( $glossaryItemDesc, $glossary_item, $glossaryIndexStyle,
                                                        $shortcodeAtts ) {
        if ( isset( $shortcodeAtts[ 'related' ] ) && $shortcodeAtts[ 'related' ] && in_array( $glossaryIndexStyle, array( 'modern-table', 'classic-definition', 'classic-excerpt' ) ) ) {
            $relatedArticlesGlossaryCount = get_option( 'cmtt_glossary_showRelatedArticlesGlossaryCount' );
            $relatedArticlesCount         = min( get_option( 'cmtt_glossary_showRelatedArticlesCount' ), $shortcodeAtts[ 'related' ] );

            $relatedSnippet = CMTT_Related::renderRelatedArticles( $glossary_item->ID, $relatedArticlesCount, $relatedArticlesGlossaryCount, FALSE );

            if ( !empty( $relatedSnippet ) ) {
                $glossaryItemDesc .= $relatedSnippet;
            }
        }
        return $glossaryItemDesc;
    }

    /**
     * Outputs the glossary item description
     * @param type $glossaryItemDesc
     * @param type $glossary_item
     * @param type $glossaryIndexStyle
     * @return type
     */
    public static function wrapGlossaryIndexItemDesc( $glossaryItemDesc, $glossary_item, $glossaryIndexStyle,
                                                      $shortcodeAtts ) {
        $glossaryItemDesc = '<div class="glossary_itemdesc">' . $glossaryItemDesc . '</div>';
        $label            = apply_filters( 'cmtt_tooltip_back_to_top_label', get_option( 'cmtt_tooltipExpandBackToTopLabel', 'Back to Top' ) );

        if ( in_array( $glossaryIndexStyle, array( 'expand-style' ) ) ) {
            $glossaryItemDesc .= '<div class="expand-back-to-top"><a href="#top" style="box-shadow: none;">' . $label . '</a></div>';
            $glossaryItemDesc .= '<p class="expand-space"></p>';
        }

        return $glossaryItemDesc;
    }

    /**
     * Adds new display styles
     * @param type $styles
     * @return string
     */
    public static function addGlossaryIndexStyles( $styles ) {
        $styles[ 'big-tiles' ]        = 'tiles big';
        $styles[ 'classic-table' ]    = 'table classic';
        $styles[ 'modern-table' ]     = 'table modern';
        $styles[ 'sidebar-termpage' ] = 'sidebar-termpage';
        $styles[ 'expand-style' ]     = 'expand';
        $styles[ 'grid-style' ]       = 'grid';
        $styles[ 'cube-style' ]       = 'cube';
        return $styles;
    }

    /**
     * Adds the where params in the server-side pagination
     * @global type $wpdb
     * @param type $where
     * @param type $wp_query
     * @return string
     */
    public static function serverSidePaginationWhere( $where, &$wp_query ) {
        global $wpdb;
        $firstLetter     = $wp_query->get( 'first_letter' );
        $nonLatinLetters = $wp_query->get( 'nonlatin_letters' );
        if ( $firstLetter ) {
            if ( $firstLetter == 'al-num' ) {
                $where .= ' AND ' . $wpdb->posts . '.post_title REGEXP \'^[0-9]\'';
            } else {
                if ( $nonLatinLetters ) {
                    $where .= ' AND LOWER(' . $wpdb->posts . '.post_title) LIKE _utf8 \'' . esc_sql( $wpdb->esc_like( $firstLetter ) ) . '%\' collate utf8_bin';
                } else {
                    $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'' . esc_sql( $wpdb->esc_like( $firstLetter ) ) . '%\'';
                }
            }
        }
        return $where;
    }

    /**
     * Adds the server-side pagination filter and query modification args
     * @param type $args
     * @param type $shortcodeAtts
     * @return type
     */
    public static function addServerSidePaginationFilter( $args, $shortcodeAtts ) {
        if ( CMTT_Glossary_Index::isServerSide() ) {
            $initLetter              = get_option( 'cmtt_index_initLetter', '' );
            $nonLatinLetters         = (bool) get_option( 'cmtt_index_nonLatinLetters' );
            $currentlySelectedLetter = (!empty( $shortcodeAtts[ "letter" ] )) ? $shortcodeAtts[ "letter" ] : (!empty( $initLetter ) ? $initLetter : 'all');

            if ( $currentlySelectedLetter !== 'all' ) {
                add_filter( 'posts_where', array( __CLASS__, 'serverSidePaginationWhere' ), 10, 2 );
                $args[ 'first_letter' ]     = $currentlySelectedLetter;
                $args[ 'nonlatin_letters' ] = $nonLatinLetters;
            }
        }
        return $args;
    }

    /**
     * Removes the server-side pagination filter
     * @param type $args
     * @param type $shortcodeAtts
     */
    public static function removeServerSidePaginationFilter( $args, $shortcodeAtts ) {
        remove_filter( 'posts_where', array( __CLASS__, 'serverSidePaginationWhere' ), 10, 2 );
    }

    /**
     * If abbreviations are added to the Glossary Index - count them
     * @param int $counts
     * @param type $term
     * @return int
     */
    public static function addAbbreviationsCount( $counts, $term, $shortcodeAtts ) {
        $addAbbreviationsToTheGlossaryIndex = get_option( 'cmtt_glossaryAbbreviationsInIndex', 1 );
        $hideAbbreviations                  = !empty( $shortcodeAtts[ 'hide_abbrevs' ] );

        if ( $addAbbreviationsToTheGlossaryIndex && !$hideAbbreviations ) {
            $abbreviation = CMTT_Abbreviations::getAbbreviation( $term->ID );
            if ( !empty( $abbreviation ) ) {
                $nonLatinLetters = (bool) get_option( 'cmtt_index_nonLatinLetters' );

                $firstLetterOriginal = mb_substr( $abbreviation, 0, 1 );

                if ( !$nonLatinLetters ) {
                    $firstLetterOriginal = remove_accents( $firstLetterOriginal );
                }
                $firstLetter = mb_strtolower( $firstLetterOriginal );

                if ( preg_match( '/\d/', $firstLetter ) ) {
                    $firstLetter = 'al-num';
                }

                if ( !isset( $counts[ $firstLetter ] ) ) {
                    $counts[ $firstLetter ] = 0;
                }
                $counts[ $firstLetter ] ++;
                $counts[ 'all' ] ++;
            }
        }

        return $counts;
    }

    /**
     * If synonyms are added to the Glossary Index - count them
     * @param int $counts
     * @param type $term
     * @return int
     */
    public static function addSynonymsCount( $counts, $term, $shortcodeAtts ) {
        $addSynonymsToTheGlossaryIndex = get_option( 'cmtt_glossarySynonymsInIndex', 1 );
        $hideSynonyms                  = !empty( $shortcodeAtts[ 'hide_synonyms' ] );

        if ( $addSynonymsToTheGlossaryIndex && !$hideSynonyms ) {
            $synonyms = CMTT_Synonyms::getSynonymsArr( $term->ID );
            if ( !empty( $synonyms ) && is_array( $synonyms ) ) {
                $nonLatinLetters = (bool) get_option( 'cmtt_index_nonLatinLetters' );
                foreach ( $synonyms as $synonym ) {

                    $firstLetterOriginal = mb_substr( $synonym, 0, 1 );

                    if ( !$nonLatinLetters ) {
                        $firstLetterOriginal = remove_accents( $firstLetterOriginal );
                    }
                    $firstLetter = mb_strtolower( $firstLetterOriginal );

                    if ( preg_match( '/\d/', $firstLetter ) ) {
                        $firstLetter = 'al-num';
                    }

                    if ( !isset( $counts[ $firstLetter ] ) ) {
                        $counts[ $firstLetter ] = 0;
                    }
                    $counts[ $firstLetter ] ++;
                    $counts[ 'all' ] ++;
                }
            }
        }
        return $counts;
    }

    /**
     * Returns the numbers of posts for server-side pagination
     * @param type $glossaryQuery
     * @return array
     */
    public static function getListnavCounts( $shortcodeAtts ) {
        $counts          = array();
        $nonLatinLetters = (bool) get_option( 'cmtt_index_nonLatinLetters' );

        $args = isset( CMTT_Pro::$lastQueryDetails[ 'nopaging_args' ] ) ? CMTT_Pro::$lastQueryDetails[ 'nopaging_args' ] : array();

        if ( !empty( $args ) && is_array( $args ) ) {
            do_action( 'cmtt_glossary_doing_search', $args, $shortcodeAtts );
            $hideTerms = !empty( $shortcodeAtts[ 'hide_terms' ] );

//			$query			 = new WP_Query;
//			$glossaryIndex	 = $query->query( $args );
            $glossaryIndex = CMTT_Pro::getGlossaryItems( $args );

            $counts[ 'all' ] = !$hideTerms ? count( $glossaryIndex ) : 0;
            foreach ( $glossaryIndex as $term ) {
                if ( !$hideTerms ) {
                    $firstLetterOriginal = mb_substr( $term->post_title, 0, 1 );

                    if ( !$nonLatinLetters ) {
                        $firstLetterOriginal = remove_accents( $firstLetterOriginal );
                    }
                    $firstLetter = mb_strtolower( $firstLetterOriginal );

                    if ( preg_match( '/\d/', $firstLetter ) ) {
                        $firstLetter = 'al-num';
                    }

                    if ( !isset( $counts[ $firstLetter ] ) ) {
                        $counts[ $firstLetter ] = 0;
                    }
                    $counts[ $firstLetter ] ++;
                }

                $counts = apply_filters( 'cmtt_modify_listnav_counts_term', $counts, $term, $shortcodeAtts );
            }
        }

        return apply_filters( 'cmtt_modify_listnav_counts_all', $counts );
    }

    /**
     * Function outputs the ListNav for server-side pagination
     * @param type $listnavOutput
     * @param type $shortcodeAtts
     * @return string
     */
    public static function outputListnav( $listNavInsideContent, $shortcodeAtts, $glossaryQuery ) {
        if ( !CMTT_Glossary_Index::isServerSide() || (isset( $shortcodeAtts[ 'disable_listnav' ] ) && $shortcodeAtts[ 'disable_listnav' ]) ) {
            return $listNavInsideContent;
        }

        $initLetter              = get_option( 'cmtt_index_initLetter', '' );
        $currentlySelectedLetter = (!empty( $shortcodeAtts[ "letter" ] )) ? $shortcodeAtts[ "letter" ] : (!empty( $initLetter ) ? $initLetter : 'all');
        $letters                 = (array) get_option( 'cmtt_index_letters' );
        $includeAll              = (bool) get_option( 'cmtt_index_includeAll' );
        $includeNum              = (bool) get_option( 'cmtt_index_includeNum' );
        $allLabel                = get_option( 'cmtt_index_allLabel', 'ALL' );
        $glossaryPageLink        = $shortcodeAtts[ 'glossary_page_link' ];

        $postCounts = self::getListnavCounts( $shortcodeAtts );

        $listNavInsideContent .= '<div class="ln-letters">';

        if ( $includeAll ) {
            $postsCount    = $postCounts[ 'all' ];
            $selectedClass = $currentlySelectedLetter == 'all' ? ' ln-selected' : '';
            $listNavInsideContent .= '<a class="ln-all ln-serv-letter' . $selectedClass . '" data-letter="all" data-letter-count="' . $postsCount . '" href="#">' . $allLabel . '</a>';
        }

        if ( $includeNum ) {
            $postsCount    = isset( $postCounts[ 'al-num' ] ) ? $postCounts[ 'al-num' ] : 0;
            $disabledClass = $postsCount == 0 ? ' ln-disabled' : '';
            $selectedClass = $currentlySelectedLetter == 'al-num' ? ' ln-selected' : '';
            $listNavInsideContent .= '<a class="ln-_ ln-serv-letter' . $disabledClass . $selectedClass . '" data-letter-count="' . $postsCount . '" data-letter="al-num" href="' . $glossaryPageLink . '">0-9</a>';
        }

        foreach ( $letters as $key => $letter ) {
            $postsCount    = isset( $postCounts[ $letter ] ) ? $postCounts[ $letter ] : 0;
            $isLast        = ($key == count( $letters ) - 1 );
            $lastClass     = $isLast ? ' ln-last' : '';
            $disabledClass = $postsCount == 0 ? ' ln-disabled' : '';
            $selectedClass = $currentlySelectedLetter == $letter ? ' ln-selected' : '';

            $listNavInsideContent .= '<a class="lnletter-' . $letter . ' ln-serv-letter' . $lastClass . $disabledClass . $selectedClass . '" data-letter-count="' . $postsCount . '" data-letter="' . $letter . '" href="' . $glossaryPageLink . '">' . mb_strtoupper( str_replace( 'ı', 'İ', $letter ) ) . '</a>';
        }

        if ( get_option( 'cmtt_index_showCounts', '1' ) ) {
            $listNavInsideContent .= '<div class="ln-letter-count" style="position: absolute; top: 0px; left: 88px; width: 20px; display: none;"></div>';
        }
        $listNavInsideContent .= '</div>';

        return $listNavInsideContent;
    }

    /**
     * Adds the default parameters for Glossary Index shortcode
     * @param type $style
     * @return int
     */
    public static function changeGlossaryIndexStyle( $style ) {
        $newStyle = get_option( 'cmtt_glossaryDisplayStyle' );
        if ( empty( $newStyle ) ) {
            $newStyle = $style;
        }
        return $newStyle;
    }

    /**
     * Adds the default parameters for Glossary Index shortcode
     * @param type $atts
     * @return int
     */
    public static function addGlossaryIndexDefaultAtts( $atts ) {
        $atts[ 'cat' ]             = 'all';
        $atts[ 'disable_listnav' ] = !apply_filters( 'cmtt_index_enabled', get_option( 'cmtt_index_enabled', 0 ) );
        $atts[ 'title_prefix' ]    = __( 'Glossary for:', 'cm-tooltip-glossary' );
        $atts[ 'title_category' ]  = 1;
        $atts[ 'title_show' ]      = 1;
        $atts[ 'search_term' ]     = (string) filter_input( INPUT_POST, 'search_term' );

        return $atts;
    }

    /**
     * Search the Glossary Terms
     * @global type $wpdb
     * @param string $where
     * @return string
     */
    public static function searchTermsWhere( $where, &$wp_query ) {
        global $wpdb;

        $searchTerm = $wp_query->get( 'search_term' );
        $exact      = $wp_query->get( 'exact' );
        $exactAdd   = $exact ? '' : '%';

        $term     = esc_sql( $wpdb->esc_like( trim( $searchTerm ) ) );
        $whereArr = array();

        /* ML
         * having the ability to search the index page only by Title or only by description
         * or by both title and description.
         * the Admin Settings is controlling this ability
         */
        $cmtt_glossarySearchFromOptions = get_option( 'cmtt_glossarySearchFromOptions', '0' );
        switch ( $cmtt_glossarySearchFromOptions ) {
            case '0': {
                    $whereArr[] = $wpdb->posts . '.post_title LIKE "' . $exactAdd . $term . $exactAdd . '"';
                    break;
                }
            case '1': {
                    $whereArr[] = $wpdb->posts . '.post_content LIKE "' . $exactAdd . $term . $exactAdd . '%"';
                    break;
                }
            default:
            case '2': {
                    $whereArr[] = $wpdb->posts . '.post_title LIKE "' . $exactAdd . $term . $exactAdd . '%"';
                    $whereArr[] = $wpdb->posts . '.post_content LIKE "' . $exactAdd . $term . $exactAdd . '%"';
                    break;
                }
        }

        $whereArr = apply_filters( 'cmtt_search_where_arr', $whereArr, $term, $wp_query );
        $where .= ' AND ( ' . implode( ' OR ', $whereArr ) . ' )';
        return $where;
    }

    /**
     * Add the search filter to the Glossary Index query
     * @param type $args
     * @param type $shortcodeAtts
     * @return type
     */
    public static function addSearchFilter( $args, $shortcodeAtts ) {
        if ( !empty( $shortcodeAtts[ 'search_term' ] ) ) {
            $args[ 'search_term' ] = $shortcodeAtts[ 'search_term' ];
            add_filter( 'posts_where', array( __CLASS__, 'searchTermsWhere' ), 10, 2 );

            do_action( 'cmtt_glossary_doing_search', $args, $shortcodeAtts );

            /*
             * Don't add the share box on search
             */
            remove_filter( 'cmtt_glossary_index_after_content', array( 'CMTT_Pro', 'cmtt_glossaryAddShareBox' ) );
        }

        return $args;
    }

    /**
     * Remove the search filter after the Glossary Index query
     */
    public static function removeSearchFilter() {
        remove_filter( 'posts_where', array( __CLASS__, 'searchTermsWhere' ), 10 );
    }

    /**
     * Adds the Parsing query args
     * @global type $post
     * @param type $args
     * @param type $shortcodeAtts
     */
    public static function addParserQueryArgs( $args ) {
        global $post;

        if ( !empty( $post ) ) {
            $customCats     = self::getCurrentCustomCats( $post->ID );
            $customCatsType = self::getCurrentCustomCatsType( $post->ID );

            if ( is_array( $customCats ) ) {
                $tagsQuery = array(
                    'taxonomy' => 'glossary-categories',
                    'field'    => 'term_id',
                    'terms'    => $customCats,
                    'operator' => 'whitelist' === $customCatsType ? 'IN' : 'NOT IN'
                );

                if ( !empty( $args[ 'tax_query' ] ) ) {
                    $args[ 'tax_query' ][]             = $tagsQuery;
                    $args[ 'tax_query' ][ 'relation' ] = 'AND';
                } else {
                    $args[ 'tax_query' ] = array(
                        $tagsQuery
                    );
                }
            }
        }
        return $args;
    }

    /**
     * Adds the Glossary Index query args
     */
    public static function addGlossaryIndexQueryArgs( $args, $shortcodeAtts ) {
        global $post;

        $hideFromIdexEnabled = get_option( 'cmtt_enableHidingFromIndex', FALSE );

        if ( !$hideFromIdexEnabled ) {
            $metaQueryArgs = array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key'   => '_cmtt_hide_from_index',
                        'value' => '0',
                    ),
                    array(
                        'key'     => '_cmtt_hide_from_index',
                        'compare' => 'NOT EXISTS'
                    ),
                ),
            );

            if ( isset( $args[ 'meta_query' ] ) ) {
                $args[ 'meta_query' ][] = $metaQueryArgs;
            } else {
                $args[ 'meta_query' ] = $metaQueryArgs;
            }
        }

        if ( !empty( $post ) ) {
            $customCats     = self::getCurrentCustomCats( $post->ID );
            $customCatsType = self::getCurrentCustomCatsType( $post->ID );

            if ( is_array( $customCats ) ) {
                $tagsQuery = array(
                    'taxonomy' => 'glossary-categories',
                    'field'    => 'term_id',
                    'terms'    => $customCats,
                    'operator' => 'whitelist' === $customCatsType ? 'IN' : 'NOT IN'
                );

                if ( !empty( $args[ 'tax_query' ] ) ) {
                    $args[ 'tax_query' ][]             = $tagsQuery;
                    $args[ 'tax_query' ][ 'relation' ] = 'AND';
                } else {
                    $args[ 'tax_query' ] = array(
                        $tagsQuery
                    );
                }
            }
        }

        if ( isset( $shortcodeAtts[ "cat" ] ) && is_array( $shortcodeAtts[ "cat" ] ) ) {
            $tagsQuery = array(
                'taxonomy' => 'glossary-categories',
                'field'    => 'term_id',
                'terms'    => $shortcodeAtts[ "cat" ],
                'operator' => 'IN'
            );

            if ( !empty( $args[ 'tax_query' ] ) ) {
                $args[ 'tax_query' ][]             = $tagsQuery;
                $args[ 'tax_query' ][ 'relation' ] = 'AND';
            } else {
                $args[ 'tax_query' ] = array(
                    $tagsQuery
                );
            }
        }

        if ( isset( $shortcodeAtts[ "gtags" ] ) && is_array( $shortcodeAtts[ "gtags" ] ) ) {
            $tagsQuery = array(
                'taxonomy' => 'glossary-tags',
                'field'    => 'term_id',
                'terms'    => $shortcodeAtts[ "gtags" ],
                'operator' => 'IN'
            );

            if ( !empty( $args[ 'tax_query' ] ) ) {
                $args[ 'tax_query' ][]             = $tagsQuery;
                $args[ 'tax_query' ][ 'relation' ] = 'AND';
            } else {
                $args[ 'tax_query' ] = array(
                    $tagsQuery
                );
            }
        }

        return $args;
    }

    /**
     * AJAX call for the glossary
     * - takes into account all of the shortcode params
     * @global type $post
     */
    public static function ajaxGlossary() {
        $content = CMTT_Glossary_Index::glossaryShortcode();
        echo trim( $content );
        exit();
    }

    /**
     * Filter the shortcode atts with the $_GET
     * @param type $baseAtts
     * @return type
     */
    public static function processGlossaryIndexShortcodeAtts( $baseAtts ) {
        if ( !empty( $baseAtts[ 'cat' ] ) ) {
            $processAtts[ 'freeze_cat' ] = 1;
        }
        $atts = array_merge( $baseAtts, $processAtts );
        return $atts;
    }

    /**
     * Filter the shortcode atts with the $_GET
     * @param type $baseAtts
     * @return type
     */
    public static function addGlossaryIndexGetAtts( $baseAtts ) {
        $getAtts = (array) filter_input_array( INPUT_GET );
        $atts    = array_merge( $baseAtts, $getAtts );
        return $atts;
    }

    /**
     * Filter the shortcode atts with the $_POST
     * @param type $baseAtts
     * @return int
     */
    public static function addGlossaryIndexPostAtts( $baseAtts ) {
        $postAtts = (array) filter_input_array( INPUT_POST );
        $atts     = array_merge( $baseAtts, $postAtts );

        unset( $atts[ 'action' ] );

        if ( !empty( $atts[ 'search_changed' ] ) ) {
            $atts[ 'itemspage' ] = 1;
        }

        $atts = self::normalizeTaxonomyTermParameter( $atts, 'gtags', 'glossary-tags' );
        if ( 'all' != $atts[ 'cat' ] ) {
            $atts = self::normalizeTaxonomyTermParameter( $atts, 'cat', 'glossary-categories' );
        }

        return $atts;
    }

    public static function normalizeTaxonomyTermParameter( $atts, $parameterName, $taxonomyName ) {
        if ( !empty( $atts[ $parameterName ] ) && !is_array( $atts[ $parameterName ] ) ) {
            $atts[ $parameterName ] = explode( ',', $atts[ $parameterName ] );
        }

        if ( !empty( $atts[ $parameterName ] ) && is_array( $atts[ $parameterName ] ) ) {
            array_map( 'trim', $atts[ $parameterName ] );
            foreach ( $atts[ $parameterName ] as $key => $tag ) {
                if ( !is_numeric( $tag ) ) {
                    $tagObj = get_term_by( 'name', esc_attr( $tag ), $taxonomyName );
                    if ( FALSE === $tagObj ) {
                        $tagObj = get_term_by( 'slug', esc_attr( $tag ), $taxonomyName );
                    }

                    if ( is_object( $tagObj ) ) {
                        $atts[ $parameterName ][ $key ] = $tagObj->term_id;
                    }
                }
            }
            $atts[ $parameterName ] = array_filter( $atts[ $parameterName ], 'is_numeric' );
        }

        return $atts;
    }

    /**
     * Support for custom templates
     *
     * @global type $post
     * @param type $single
     * @return string
     */
    public static function glossaryTermCustomTemplate( $single ) {
        global $post;
        /* Checks for single template by post type */

        if ( $single !== 'single-glossary.php' ) {
            if ( get_option( 'cmtt_glossaryUseTemplate' ) && $post->post_type == "glossary" ) {
                $glossary_path = plugin_dir_path( __FILE__ );
                $theme_path    = get_stylesheet_directory();

                if ( file_exists( $theme_path . '/Tooltip/single-glossary.php' ) ) {
                    $single = $theme_path . '/Tooltip/single-glossary.php';
                    return $single;
                } elseif ( file_exists( $glossary_path . 'theme/Tooltip/single-glossary.php' ) ) {
                    $single = $glossary_path . 'theme/Tooltip/single-glossary.php';
                    return $single;
                }
            }
        }

        return $single;
    }

    /**
     * Add the abbreviation in the square brackets to the post's title
     * @param string $title
     * @param int $id
     * @return string
     */
    public static function addAbbreviation( $title = '', $id = null ) {
        if ( $id ) {
            $glossaryItem = get_post( $id );
            if ( $glossaryItem && 'glossary' == $glossaryItem->post_type ) {
                $abbreviation = CMTT_Abbreviations::getAbbreviation( $id );
                if ( $abbreviation ) {
                    $title .= ' [' . $abbreviation . ']';
                }
            }
        }

        return $title;
    }

    /**
     * Disable the parsing
     */
    public static function disableParsing() {
        remove_filter( 'the_content', array( __CLASS__, 'addRelatedTerms' ), 21500 );
    }

    /**
     * Reenable the parsing
     */
    public static function reenableParsing() {
        add_filter( 'the_content', array( __CLASS__, 'addRelatedTerms' ), 21500 );
    }

    /**
     * Add related terms to posts and pages
     *
     * @global type $wp_query
     * @global type $replacedTerms
     * @param type $content
     * @return type
     */
    public static function addRelatedTerms( $content = '' ) {
        global $wp_query, $replacedTerms;

        if ( !isset( $wp_query->post ) ) {
            return $content;
        }
        $post = $wp_query->post;
        $id   = $post->ID;

        $disableRelatedTermsForPage     = get_post_meta( $id, '_glossary_disable_related_terms_for_page', true );
        $disableRelatedTermsOnTermPages = ($post->post_type == 'glossary') ? get_option( 'cmtt_glossaryDisableRelatedTermsForTerms', 0 ) : 0;

        if ( is_singular() && $wp_query->is_main_query() && (get_option( 'cmtt_showRelatedTermsList' ) == 1) && !$disableRelatedTermsForPage && !$disableRelatedTermsOnTermPages ) {
            $relatedSnippet = CMTT_Related::renderRelatedTerms( $replacedTerms );
            $content .= $relatedSnippet;
        }
        return $content;
    }

    /**
     * Adds the "Flush API Cache" button
     * @param array $content
     * @return string
     */
    public static function renderFlushButton( $content ) {
        $additionalContent = '<input type="submit" name="cmtt_flush_thirdparty" value="' . __( 'Flush API Cache', 'cm-tooltip-glossary' ) . '"/>';
        $content[]         = $additionalContent;
        return $content;
    }

    /**
     * Fix the highlighting of the element in the Admin menu
     *
     * @global string $submenu_file
     * @global type $current_screen
     * @global type $pagenow
     * @param type $parent_file
     * @return type
     */
    public static function setCurrentMenu( $parent_file ) {
        global $submenu_file, $current_screen, $pagenow;
        // Set the submenu as active/current while anywhere in your Custom Post Type (nwcm_news)
        if ( $current_screen->post_type == 'glossary' ) {
            if ( $pagenow == 'edit-tags.php' ) {
                $submenu_file = 'edit-tags.php?taxonomy=' . $current_screen->taxonomy . '&post_type=' . $current_screen->post_type;
            }
            $parent_file = CMTT_MENU_OPTION;
        }
        return $parent_file;
    }

    /**
     * Create taxonomies
     */
    public static function createTaxonomies() {
        $glossaryCategoriesPermalink = get_option( 'cmtt_glossaryCategoriesPermalink', 'glossary-categories' );
        $glossaryCategoriesArgs      = array(
            'label'             => __( 'Tooltip Categories', 'cm-tooltip-glossary' ),
            'rewrite'           => array( 'slug' => $glossaryCategoriesPermalink, 'with_front' => false ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true
        );
        register_taxonomy(
        'glossary-categories', 'glossary', apply_filters( 'cmtt_taxonomy_categories_args', $glossaryCategoriesArgs )
        );

        $glossaryTagsPermalink = get_option( 'cmtt_glossaryTagsPermalink', 'glossary-tags' );
        $glossaryTagsArgs      = array(
            'label'             => __( 'Tooltip Tags', 'cm-tooltip-glossary' ),
            'rewrite'           => array( 'slug' => $glossaryTagsPermalink, 'with_front' => false ),
            'hierarchical'      => false,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true
        );
        register_taxonomy(
        'glossary-tags', 'glossary', apply_filters( 'cmtt_taxonomy_tags_args', $glossaryTagsArgs )
        );
    }

    /**
     * Adds the support for tags to "glossary" post item
     * @param type $args
     * @return type
     */
    public static function addPostTypeSupport( $args ) {
        if ( !isset( $args[ 'taxonomies' ] ) || !is_array( $args[ 'taxonomies' ] ) ) {
            $args[ 'taxonomies' ] = array( 'glossary-tags' );
        } else {
            $args[ 'taxonomies' ][] = 'glossary-tags';
        }
        return $args;
    }

    /**
     * Add new options
     */
    public static function addOptions() {
        add_option( 'cmtt_glossaryTermsInComments', 0 ); //Highlight the terms in the comments
        add_option( 'cmtt_allowed_terms_metabox_all_post_types', 0 ); //show allowed terms metabox for all post types
        add_option( 'cmtt_glossaryDisplayStyle', 'classic' ); //Select the display style of Glossary Index page
        add_option( 'cmtt_glossaryListTilesBig', 0 ); // Use big list tiles instead of small
        add_option( 'cmtt_glossary_showSearch', 1 ); //Show the search button on the index glossary page
        add_option( 'cmtt_glossaryCategoriesLabel', 'Category: ' ); //Label for the Categories on the index glossary page and term page
        add_option( 'cmtt_glossaryCategoriesDisplayType', '0' ); //Change the default method of displaying the Categories
        add_option( 'cmtt_glossaryTermShowListnav', '0' ); //If the listnav should be displayed on top of the Glossary Term page
        add_option( 'cmtt_glossaryTagsLabel', 'Tags: ' ); //Label for the Tags on the index glossary page and term page
        add_option( 'cmtt_glossary_SearchLabel', 'Search:' ); //Label for the Search button on the index glossary page
        add_option( 'cmtt_glossary_SearchButtonLabel', 'Search' ); //Label for the Search button on the index glossary page
        add_option( 'cmtt_glossary_NoResultsLabel', 'Nothing found. Please change the filters.' ); //Text for "no_results"
        add_option( 'cmtt_glossarySearchHelp', 'The search returns the partial search for the given query from both the term title and description. So it will return the results even if the given query is part of the word in the description.' );
        add_option( 'cmtt_glossary_AllCategoriesLabel', 'All Categories' ); //Text for "All Categories"
        add_option( 'cmtt_glossary_ClearLabel', '(clear)' ); //Label for the clear link on the index glossary page
        add_option( 'cmtt_indexLettersSize', 'small' ); //Size of the letters in the alphabetic index
        add_option( 'cmtt_index_initLetter', '' );
        add_option( 'cmtt_glossaryUseTemplate', 0 ); //Use the attached single term template?
        add_option( 'cmtt_glossary_relatedArticlesOrder', 'menu_order' );
        add_option( 'cmtt_glossaryDisableRelatedTermsForTerms', 0 );
        add_option( 'cmtt_glossary_showRelatedArticlesMerged', 0 );
        add_option( 'cmtt_glossary_showRelatedArticlesGlossaryTitle', 'Related Glossary Terms:' );
        /*
         * Related terms
         */
        add_option( 'cmtt_showRelatedTermsList', 1 ); //show the list of related terms under post/page
        add_option( 'cmtt_glossary_showRelatedTermsTitle', 'Related Terms:' ); //title of the "Related Terms" box
        add_option( 'cmtt_glossary_relatedTermsPrefix', __( 'Term: ', 'cm-tooltip-glossary' ) ); //prefix of the "Related Terms" item

        /*
         * Mobile support
         */
        add_option( 'cmtt_glossaryMobileSupport', 1 ); //Add the mobile support?
        add_option( 'cmtt_glossaryMobileSupportLabel', 'Term link: ' ); //Add the mobile support?

        add_option( 'cmtt_tooltipFontStyle', 'default' );
        add_option( 'cmtt_tooltipShowCloseIcon', 0 );

        add_option( 'cmtt_tooltip3RD_MWDictionaryAutoContent', 0 );
        add_option( 'cmtt_tooltip3RD_MWThesaurusAutoContent', 0 );
    }

}
