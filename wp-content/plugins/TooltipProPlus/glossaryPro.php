<?php

class GlossaryTooltipException extends Exception {

}

;

class CMTT_Pro {

    protected static $filePath      = '';
    protected static $cssPath       = '';
    protected static $jsPath        = '';
    protected static $messages      = '';
    public static $lastQueryDetails = array();
    public static $calledClassName;

    public static function init() {
        global $cmtt_isLicenseOk;

        self::setupConstants();

        self::includeFiles();

        self::initFiles();

        self::addOptions();

        if ( empty( self::$calledClassName ) ) {
            self::$calledClassName = __CLASS__;
        }

        $file   = basename( __FILE__ );
        $folder = basename( dirname( __FILE__ ) );
        $hook   = "in_plugin_update_message-{$folder}/{$file}";
        add_action( $hook, array( self::$calledClassName, 'cmtt_warn_on_upgrade' ) );

        self::$filePath = plugin_dir_url( __FILE__ );
        self::$cssPath  = self::$filePath . 'assets/css/';
        self::$jsPath   = self::$filePath . 'assets/js/';

        add_action( 'plugins_loaded', array( self::$calledClassName, 'loadPluginTextDomain' ) );
        add_action( 'init', array( self::$calledClassName, 'cmtt_create_post_types' ) );

        add_action( 'admin_menu', array( self::$calledClassName, 'cmtt_admin_menu' ) );
        add_action( 'admin_init', array( self::$calledClassName, 'cmtt_glossary_handleexport' ) );
        add_action( 'admin_head', array( self::$calledClassName, 'addRicheditorButtons' ) );

        add_action( 'admin_enqueue_scripts', array( self::$calledClassName, 'cmtt_glossary_admin_settings_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( self::$calledClassName, 'cmtt_glossary_admin_edit_scripts' ) );

        add_action( 'restrict_manage_posts', array( self::$calledClassName, 'cmtt_restrict_manage_posts' ) );

        add_action( 'admin_notices', array( self::$calledClassName, 'cmtt_glossary_admin_notice_wp33' ) );
        add_action( 'admin_notices', array( self::$calledClassName, 'cmtt_glossary_admin_notice_mbstring' ) );
        add_action( 'admin_notices', array( self::$calledClassName, 'cmtt_glossary_admin_notice_client_pagination' ) );
        add_action( 'admin_print_footer_scripts', array( self::$calledClassName, 'cmtt_quicktags' ) );
        add_action( 'add_meta_boxes', array( self::$calledClassName, 'cmtt_RegisterBoxes' ) );
        add_action( 'save_post', array( self::$calledClassName, 'cmtt_save_postdata' ) );
        add_action( 'update_post', array( self::$calledClassName, 'cmtt_save_postdata' ) );
        /*
         * Invalidate transients on updating/deleting terms
         */
        add_action( 'save_post', array( self::$calledClassName, 'cmtt_unset_transients' ) );
        add_action( 'delete_post', array( self::$calledClassName, 'cmtt_unset_transients' ) );

        add_action( 'wp_ajax_cmtt_get_glossary_backup', array( self::$calledClassName, 'cmtt_glossary_get_backup' ) );
        add_action( 'wp_ajax_nopriv_cmtt_get_glossary_backup', array( self::$calledClassName, 'cmtt_glossary_get_backup' ) );
        add_action( 'admin_init', array( self::$calledClassName, '_cmtt_rescheduleBackup' ) );
        add_action( 'cmtt_glossary_backup_event', array( self::$calledClassName, '_cmtt_doBackup' ) );

        add_filter( 'cmtt_settings_tooltip_tab_content_after', 'cminds_cmtt_settings_tooltip_tab_content_after' );
        add_filter( 'cmtt-custom-settings-tab-content-50', array( self::$calledClassName, 'outputLabelsSettings' ) );

        if ( $cmtt_isLicenseOk ) {
            /*
             * FILTERS
             */

            if ( get_option( 'cmtt_glossaryRemoveExcerptParsing', 1 ) == 1 ) {
                add_filter( 'get_the_excerpt', array( self::$calledClassName, 'remove_excerpt_parsing' ), 1 );
                add_filter( 'wp_trim_excerpt', array( self::$calledClassName, 'add_parsing_after_excerpt' ), 1 );
            } else {
                add_filter( 'get_the_excerpt', array( self::$calledClassName, 'cmtt_glossary_parse' ), 20000 );
            }

            add_filter( 'get_the_excerpt', array( self::$calledClassName, 'cmtt_disable_parsing' ), 1 );
            add_filter( 'wpseo_opengraph_desc', array( self::$calledClassName, 'cmtt_reenable_parsing' ), 1 );
            /*
             * Make sure parser runs before the post or page content is outputted
             */
            add_filter( 'the_content', array( self::$calledClassName, 'cmtt_glossary_parse' ), 20000 );
            add_filter( 'the_content', array( self::$calledClassName, 'removeGlossaryExclude' ), 25000 );

            add_filter( 'the_content', array( 'CMTT_Glossary_Index', 'lookForShortcode' ), 1 );
            add_filter( 'the_content', array( self::$calledClassName, 'cmtt_glossary_addBacklink' ), 21000 );

            /*
             * It's a custom filter which can be applied to create the tooltips
             */
            add_filter( 'cm_tooltip_parse', array( self::$calledClassName, 'cmtt_glossary_parse' ), 20000, 2 );
            add_filter( 'the_title', array( self::$calledClassName, 'cmtt_glossary_addTitlePrefix' ), 22000, 2 );

            if ( get_option( 'cmtt_glossaryShowShareBoxTermPage' ) == 1 ) {
                add_filter( 'cmtt_glossary_term_after_content', array( self::$calledClassName, 'cmtt_glossaryAddShareBox' ) );
            }

            add_filter( 'cmtt_tooltip_content_add', array( self::$calledClassName, 'addTitleToTooltip' ), 10, 2 );
            add_filter( 'cmtt_tooltip_content_add', array( self::$calledClassName, 'addEditlinkToTooltip' ), 10, 2 );
            add_filter( 'cmtt_tooltip_content_add', array( self::$calledClassName, 'addTermPageLinkToTooltip' ), 100, 2 );

            /*
             * Filter for the BuddyPress record
             */
            add_filter( 'bp_blogs_record_comment_post_types', array( self::$calledClassName, 'cmtt_bp_record_my_custom_post_type_comments' ) );

            add_filter( 'bp_replace_the_content', array( self::$calledClassName, 'cmtt_bp_turn_off_parsing' ) );

            add_filter( 'cmtt_is_tooltip_clickable', array( self::$calledClassName, 'isTooltipClickable' ) );

            /*
             * Tooltip Content ADD
             */
            add_filter( 'cmtt_tooltip_content_add', array( self::$calledClassName, 'cmtt_glossary_parse_strip_shortcodes' ), 4, 2 );

            /*
             * "Normal" Tooltip Content
             */
            add_filter( 'cmtt_term_tooltip_content', array( self::$calledClassName, 'getTheTooltipContentBase' ), 10, 2 );
            add_filter( 'cmtt_term_tooltip_content', array( self::$calledClassName, 'cmtt_glossary_parse_strip_shortcodes' ), 20, 2 );
            add_filter( 'cmtt_term_tooltip_content', array( self::$calledClassName, 'cmtt_glossary_filterTooltipContent' ), 30, 2 );

            add_filter( 'cmtt_parse_with_simple_function', array( self::$calledClassName, 'allowSimpleParsing' ) );

            // acf/load_value - filter for every value load
            add_filter( 'acf/load_value', array( self::$calledClassName, 'parseACFFields' ), 10, 3 );
            add_filter( 'bbp_get_reply_content', array( self::$calledClassName, 'parseBBPressFields' ) );
//			add_filter( 'bbp_get_breadcrumb', array( self::$calledClassName, 'outputGlossaryExcludeWrap' ) );

            add_filter( 'cmtt_tooltip_script_args', array( __CLASS__, 'addTooltipScriptArgs' ) );

            /*
             * Tooltips in Woocommerce short description
             */
            add_filter( 'woocommerce_short_description', array( self::$calledClassName, 'cmtt_glossary_parse' ), 20000 );

            /*
             * Tooltips in WordPress Text Widget
             */
            if ( get_option( 'cmtt_glossaryParseTextWidget', 1 ) == 1 ) {
                add_filter( 'widget_text', array( self::$calledClassName, 'cmtt_glossary_parse' ), 20000 );
            }

            add_filter( 'comments_open', array( self::$calledClassName, 'cmtt_comments_open' ), 10, 2 );
            add_filter( 'get_comments_number', array( self::$calledClassName, 'cmtt_comments_number' ), 10, 2 );

            /*
             * SHORTCODES
             */
            add_shortcode( 'cm_tooltip_link_to_term', array( self::$calledClassName, 'cmtt_link_to_term' ) );
            add_shortcode( 'cm_tooltip_parse', array( self::$calledClassName, 'cm_tooltip_parse' ) );
            /*
             * Custom tooltip shortcode
             */
            add_shortcode( 'glossary_tooltip', array( self::$calledClassName, 'cmtt_custom_tooltip_shortcode' ) );

            add_action( 'bp_before_create_group', array( self::$calledClassName, 'outputGlossaryExcludeStart' ) );
            add_action( 'bp_before_group_admin_content', array( self::$calledClassName, 'outputGlossaryExcludeStart' ), 50 );
            add_action( 'bp_attachments_avatar_check_template', array( self::$calledClassName, 'outputGlossaryExcludeStart' ), 50 );
            add_action( 'bp_before_profile_avatar_upload_content', array( self::$calledClassName, 'outputGlossaryExcludeStart' ), 50 );
            add_action( 'bp_before_profile_edit_cover_image', array( self::$calledClassName, 'outputGlossaryExcludeStart' ), 50 );

            add_action( 'bp_after_create_group', array( self::$calledClassName, 'outputGlossaryExcludeEnd' ) );
            add_action( 'bp_after_group_admin_content', array( self::$calledClassName, 'outputGlossaryExcludeEnd' ), 50 );
            add_action( 'bp_attachments_avatar_main_template', array( self::$calledClassName, 'outputGlossaryExcludeEnd' ), 50 );
            add_action( 'bp_after_profile_avatar_upload_content', array( self::$calledClassName, 'outputGlossaryExcludeEnd' ), 50 );
            add_action( 'bp_after_profile_edit_cover_image', array( self::$calledClassName, 'outputGlossaryExcludeEnd' ), 50 );

            add_action( 'cmtt_save_options_before', array( self::$calledClassName, 'flushCaps' ), 10, 2 );

            add_filter( 'cmtt_dynamic_css_before', array( __CLASS__, 'addDynamicCSS' ) );

            /*
             * Init the Glossary Index (adds hooks)
             */
            CMTT_Glossary_Index::init();
        }
    }

    /**
     * Adds more dynamic styles
     * @param type $addition
     * @param type $glossary_item
     * @return type
     */
    public static function addDynamicCSS( $dynamicCss ) {
        $mobileTermLink                   = get_option( 'cmtt_glossaryMobileSupportLabel', 'Term link: ' );
        $fontName                         = get_option( 'cmtt_tooltipFontStyle', 'default' );
        $fontFamily                       = ($fontName !== 'default') ? 'font-family: "' . $fontName . '", sans - serif;
		' : '';
        $glossaryTermTitleColorText       = str_replace( '#', '', get_option( 'cmtt_tooltipTitleColor_text' ) );
        /* ML */
        $glossaryTermTitleColorBackground = str_replace( '#', '', get_option( 'cmtt_tooltipTitleColor_background' ) );
        $paddingCont                      = get_option( 'cmtt_tooltipPadding', '2%' );

        ob_start();
        ?>
        #tt {<?php echo $fontFamily; ?>}

        <?php if ( !empty( $glossaryTermTitleColorText ) ) : ?>
            #tt #ttcont div.glossaryItemTitle {
            color: #<?php echo $glossaryTermTitleColorText; ?> !important;
            }
        <?php endif; ?>

        <?php if ( !empty( $glossaryTermTitleColorBackground ) ) : ?>
            #tt #ttcont {
            padding: 0px !important;
            }
            #tt #ttcont div.glossaryItemTitle {
            background-color: #<?php echo $glossaryTermTitleColorBackground; ?> !important;
            padding: <?php echo $paddingCont; ?> !important;
            margin: 0px !important;
            border-top: 10px solid transparent;
            border-bottom: 10px solid transparent;
            }
            #tt #ttcont div.glossaryItemBody {
            padding: <?php echo $paddingCont; ?> !important;
            }
        <?php endif; ?>

        .mobile-link a.glossaryLink {
        color: #fff !important;
        }
        .mobile-link:before{content: "<?php echo $mobileTermLink; ?> "}
        <?php
        $dynamicCss .= ob_get_clean();
        return $dynamicCss;
    }

    /**
     * Check whether the highlight only once should be enabled for the post/page
     * @global type $post
     * @param type $post
     * @return bool
     */
    public static function isHightlightOnlyOnceEnabled( $post = null ) {
        if ( empty( $post ) ) {
            global $post;
        }
        $highlightFirstOccuranceOnly = (get_option( 'cmtt_glossaryFirstOnly' ) == 1);

        if ( !empty( $post ) ) {
            /*
             * The post based checkbox can override the general setting, regardless of what it is so:
             * - if the option is enabled globally - it can disable for post
             * - if the option is disabled globally - it can enable for post
             */
            $postHighlightFirstOccurenceOverride = (bool) get_post_meta( $post->ID, '_cmtt_highlightFirstOnly', true );
            $highlightFirstOccuranceOnly         = $highlightFirstOccuranceOnly == !$postHighlightFirstOccurenceOverride;
        }
        return apply_filters( 'cmtt_highlight_first_only', $highlightFirstOccuranceOnly, $post );
    }

    /**
     * Function removing the comments functionality from the Term Page pt1.
     */
    public static function cmtt_comments_number( $count, $post_id ) {
        $removeComments = get_option( 'cmtt_glossaryRemoveCommentsTermPage', 0 );
        $_post          = get_post( $post_id );
        if ( 'glossary' === $_post->post_type ) {
            $count = 0;
        }
        return $count;
    }

    /**
     * Function removing the comments functionality from the Term Page pt2.
     */
    public static function cmtt_comments_open( $open, $post_id ) {
        $removeComments = get_option( 'cmtt_glossaryRemoveCommentsTermPage', 0 );
        $_post          = get_post( $post_id );
        if ( 'glossary' === $_post->post_type ) {
            $open = !$removeComments;
        }
        return $open;
    }

    /**
     * Load plugin's textdomain
     */
    public static function loadPluginTextDomain() {
        load_plugin_textdomain( 'cm-tooltip-glossary', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Add tooltip script args
     * @param array $tooltipArgs
     * @return type
     */
    public static function addTooltipScriptArgs( $tooltipArgs ) {
        $tooltipArgs[ 'close_button' ] = (bool) get_option( 'cmtt_tooltipShowCloseIcon' );
        return $tooltipArgs;
    }

    /**
     * Function adds the term highlighting to Advanced Custom Fields
     * @param type $value
     * @param type $post_id
     * @param type $field
     * @return type
     */
    public static function parseACFFields( $value, $post_id, $field ) {

        if ( is_admin() ) {
            return $value;
        }

        if ( !is_string( $value ) ) {
            return $value;
        }

        /*
         * Showing the tooltips on page is disabled
         */
        $parsingDisabled    = get_post_meta( $post_id, '_glossary_disable_for_page', true ) == 1;
        /*
         * Just the ACF parsing is disabled
         */
        $parsingACFDisabled = get_post_meta( $post_id, '_cmtt_disable_acf_for_page', true ) == 1;

        $disabledACFFields = get_option( 'cmtt_disableACFfields' );
        if ( !empty( $disabledACFFields ) ) {
            $disabledACFFieldsArr = explode( ',', $disabledACFFields );
            if ( !empty( $disabledACFFieldsArr ) ) {
                $disabledACFFieldsArr = array_map( 'trim', $disabledACFFieldsArr );
            }
            $isFieldDisabledName = in_array( $field[ '_name' ], $disabledACFFieldsArr );
            $isFieldDisabledKey  = in_array( $field[ 'key' ], $disabledACFFieldsArr );
            $isFieldDisabled     = $isFieldDisabledKey || $isFieldDisabledName;
        } else {
            $isFieldDisabled = false;
        }

        $parseACFFields = get_option( 'cmtt_glossaryParseACFFields' );

        if ( $parseACFFields && !$isFieldDisabled && !$parsingACFDisabled ) {

            /*
             * Limit the scope
             * 3.4.0 - added the option to change the fields being parsed
             */
            if ( !in_array( $field[ 'type' ], (array) apply_filters( 'cmtt_acf_parsed_field_types', get_option( 'cmtt_acf_parsed_field_types', array( 'text', 'wysiwyg' ) ) ) ) ) {
                return $value;
            }

            /*
             * Unwanted in some cases
             */
            if ( in_array( $field[ 'type' ], (array) apply_filters( 'cmtt_acf_remove_filters_for_type', get_option( 'cmtt_acf_remove_filters_for_type', array( 'text' ) ) ) ) ) {
                remove_filter( 'acf_the_content', 'wpautop' );
            }

            /*
             * Creates problems in some cases
             */
            remove_filter( 'acf_the_content', 'wptexturize' );
//			$value = apply_filters( 'cm_tooltip_parse', $value, true );
            $value = self::cmtt_glossary_parse( do_shortcode( $value ), !$parsingDisabled );
        }
        return $value;
    }

    /**
     * Function adds the term highlighting to bbPress fields
     * @param type $value
     * @param type $post_id
     * @param type $field
     * @return type
     */
    public static function parseBBPressFields( $value ) {
        if ( !is_string( $value ) ) {
            return $value;
        }

        $parseBBPressFields = get_option( 'cmtt_glossaryParseBBPressFields' );
        if ( $parseBBPressFields ) {
            $value = apply_filters( 'cm_tooltip_parse', $value );
        }
        return $value;
    }

    /**
     * Include the files
     */
    public static function includeFiles() {
        do_action( 'cmtt_include_files_before' );

        include_once CMTT_PLUGIN_DIR . "glossaryIndex.php";
        include_once CMTT_PLUGIN_DIR . "synonyms.php";
        include_once CMTT_PLUGIN_DIR . "related.php";
        include_once CMTT_PLUGIN_DIR . "widgets.php";
        include_once CMTT_PLUGIN_DIR . "functions.php";
        include_once CMTT_PLUGIN_DIR . "package/cminds-pro.php";
        include_once CMTT_PLUGIN_DIR . "customTemplates.php";

        do_action( 'cmtt_include_files_after' );
    }

    /**
     * Initialize the files
     */
    public static function initFiles() {
        do_action( 'cmtt_init_files_before' );

        CMTT_RandomTerms_Widget::init();

        CMTT_Synonyms::init();
        CMTT_Related::init();
        CMTT_Custom_Templates::init();

        do_action( 'cmtt_init_files_after' );
    }

    /**
     * Adds options
     */
    public static function addOptions() {
        /*
         * General settings
         */
        add_option( 'cmtt_glossaryOnMainQuery', 1 ); //Show on Main Query only
        add_option( 'cmtt_glossaryID', -1 ); //The ID of the main Glossary Page
        add_option( 'cmtt_glossaryPermalink', 'glossary' ); //Set permalink name
        add_option( 'cmtt_glossaryOnlySingle', 0 ); //Show on Home and Category Pages or just single post pages?
        add_option( 'cmtt_glossaryFirstOnly', 0 ); //Search for all occurances in a post or only one?
        add_option( 'cmtt_removeGlossaryCreateListFilter', 0 ); //Remove the Glossary Index List after first run
        add_option( 'cmtt_glossaryOnlySpaceSeparated', 1 ); //Search only for words separated by spaces
        add_option( 'cmtt_script_in_footer', 0 ); //Place the scripts in the footer not the header
        add_option( 'cmtt_glossaryOnPosttypes', array( 'post', 'page', 'glossary' ) ); //Default post types where the terms are highlighted

        add_option( 'cmtt_glossary_backup_pinprotect', '' ); //PIN Protect the backup

        add_option( 'cmtt_disable_metabox_all_post_types', 0 ); //show disable metabox for all post types
        /*
         * Glossary page styling
         */
        add_option( 'cmtt_glossaryDoubleclickEnabled', 0 );
        add_option( 'cmtt_glossaryDoubleclickService', 0 );
        /*
         * Glossary page styling
         */
        add_option( 'cmtt_glossaryShowShareBox', 0 ); //Show/hide the Share This box on top of the Glossary Index Page
        add_option( 'cmtt_glossaryShowShareBoxTermPage', 0 ); //Show/hide the Share This box on top of the Glossary Term Page
        add_option( 'cmtt_glossaryShowShareBoxLabel', 'Share This' ); //Label of the Sharing Box on the Glossary Index Page
        add_option( 'cmtt_glossaryTooltipDescLength', 300 ); //Limit the length of the definision shown on the Glossary Index Page
        add_option( 'cmtt_glossaryDiffLinkClass', 0 ); //Use different class to style glossary list
        add_option( 'cmtt_glossaryListTiles', 0 ); // Display glossary terms list as tiles
        add_option( 'cmtt_glossaryListTermLink', 0 ); //Remove links from glossary index to glossary page
        add_option( 'cmtt_index_letters', array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z' ) );
        add_option( 'cmtt_glossaryTooltipDesc', 0 ); // Display description in glossary list
        add_option( 'cmtt_glossaryTooltipDescExcerpt', 0 ); // Display excerpt in glossary list
        add_option( 'cmtt_glossaryServerSidePagination', 0 ); //paginate server side or client side (with alphabetical index)
        add_option( 'cmtt_perPage', 0 ); //pagination on "glossary page" withing alphabetical navigation
        add_option( 'cmtt_glossaryRunApiCalls', 0 ); //exclude the API calls from the glossary main page
        add_option( 'cmtt_index_includeNum', 1 );
        add_option( 'cmtt_index_includeAll', 1 );
        add_option( 'cmtt_index_showEmpty', 1 );
        add_option( 'cmtt_index_allLabel', 'ALL' );
        add_option( 'cmtt_glossary_addBackLink', 1 );
        add_option( 'cmtt_glossary_addBackLinkBottom', 1 );
        add_option( 'cmtt_glossary_backLinkText', '&laquo; Back to Glossary Index' );
        add_option( 'cmtt_glossary_backLinkBottomText', '&laquo; Back to Glossary Index' );
        /*
         * Related articles
         */
        add_option( 'cmtt_glossary_showRelatedArticles', 1 );
        add_option( 'cmtt_glossary_showRelatedArticlesCount', 5 );
        add_option( 'cmtt_glossary_showRelatedArticlesGlossaryCount', 5 );
        add_option( 'cmtt_glossary_showRelatedArticlesTitle', 'Related Articles:' );
        add_option( 'cmtt_glossary_showRelatedArticlesPostTypesArr', array( 'post', 'page', 'glossary' ) );
        add_option( 'cmtt_glossary_relatedArticlesPrefix', 'Glossary: ' );
        /*
         * Synonyms
         */
        add_option( 'cmtt_glossary_addSynonyms', 1 );
        add_option( 'cmtt_glossary_addSynonymsTitle', 'Synonyms: ' );
        add_option( 'cmtt_glossary_addSynonymsTooltip', 0 );
        /*
         * Referral
         */
        add_option( 'cmtt_glossaryReferral', false );
        add_option( 'cmtt_glossaryAffiliateCode', '' );
        /*
         * Glossary term
         */
        add_option( 'cmtt_glossaryBeforeTitle', '' ); //Text which shows up before the title on the term page
        /*
         * Tooltip content
         */
        add_option( 'cmtt_glossaryTooltip', 1 ); //Use tooltips on glossary items?
        add_option( 'cmtt_glossaryAddTermTitle', 1 ); //Add the term title to the glossary?
        add_option( 'cmtt_glossaryTooltipStripShortcode', 0 ); //Strip the shortcodes from glossary page before placing the tooltip?
        add_option( 'cmtt_glossaryFilterTooltip', 1 ); //Clean the tooltip text from uneeded chars?
        add_option( 'cmtt_glossaryFilterTooltipA', 0 ); //Clean the tooltip anchor tags
        add_option( 'cmtt_glossaryLimitTooltip', 0 ); // Limit the tooltip length  ?
        add_option( 'cmtt_glossaryTermDetailsLink', 'Term details' ); // Label of the link to term's details
        add_option( 'cmtt_glossaryExcerptHover', 0 ); //Search for all occurances in a post or only one?
        add_option( 'cmtt_glossaryProtectedTags', 1 ); //Aviod the use of Glossary in Protected tags?
        add_option( 'cmtt_glossaryCaseSensitive', 0 ); //Case sensitive?
        /*
         * Glossary link
         */
        add_option( 'cmtt_glossaryRemoveCommentsTermPage', 1 ); //Remove the comments from term page
        add_option( 'cmtt_glossaryInNewPage', 0 ); //In New Page?
        add_option( 'cmtt_glossaryTermLink', 0 ); //Remove links to glossary page
        add_option( 'cmtt_showTitleAttribute', 0 ); //show HTML title attribute
        /*
         * Tooltip styling
         */
        add_option( 'cmtt_tooltipIsClickable', 1 );
        add_option( 'cmtt_tooltipLinkUnderlineStyle', 'dotted' );
        add_option( 'cmtt_tooltipLinkUnderlineWidth', 1 );
        add_option( 'cmtt_tooltipLinkUnderlineColor', '#000000' );
        add_option( 'cmtt_tooltipLinkColor', '#000000' );
        add_option( 'cmtt_tooltipLinkHoverUnderlineStyle', 'solid' );
        add_option( 'cmtt_tooltipLinkHoverUnderlineWidth', '1' );
        add_option( 'cmtt_tooltipLinkHoverUnderlineColor', '#333333' );
        add_option( 'cmtt_tooltipLinkHoverColor', '#333333' );
        add_option( 'cmtt_tooltipBackground', '#666666' );
        add_option( 'cmtt_tooltipForeground', '#ffffff' );
        add_option( 'cmtt_tooltipOpacity', 95 );
        add_option( 'cmtt_tooltipBorderStyle', 'none' );
        add_option( 'cmtt_tooltipBorderWidth', 0 );
        add_option( 'cmtt_tooltipBorderColor', '#000000' );
        add_option( 'cmtt_tooltipPositionTop', 5 );
        add_option( 'cmtt_tooltipPositionLeft', 25 );
        add_option( 'cmtt_tooltipFontSize', 13 );
        add_option( 'cmtt_tooltipPadding', '2px 12px 3px 7px' );
        add_option( 'cmtt_tooltipBorderRadius', 6 );

        do_action( 'cmtt_add_options' );
    }

    /**
     * Setup plugin constants
     *
     * @access private
     * @since 1.1
     * @return void
     */
    public static function setupConstants() {
        /**
         * Define Plugin Directory
         *
         * @since 1.0
         */
        if ( !defined( 'CMTT_PLUGIN_DIR' ) ) {
            define( 'CMTT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        }

        /**
         * Define Plugin URL
         *
         * @since 1.0
         */
        if ( !defined( 'CMTT_PLUGIN_URL' ) ) {
            define( 'CMTT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        }

        /**
         * Define Plugin Slug name
         *
         * @since 1.0
         */
        if ( !defined( 'CMTT_SLUG_NAME' ) ) {
            define( 'CMTT_SLUG_NAME', 'cm-tooltip-glossary' );
        }

        /**
         * Define Plugin basename
         *
         * @since 1.0
         */
        if ( !defined( 'CMTT_PLUGIN' ) ) {
            define( 'CMTT_PLUGIN', plugin_basename( __FILE__ ) );
        }

        if ( !defined( 'CMTT_MENU_OPTION' ) ) {
            define( 'CMTT_MENU_OPTION', 'cmtt_menu_options' );
        }

        define( 'CMTT_ABOUT_OPTION', 'cmtt_about' );
        define( 'CMTT_EXTENSIONS_OPTION', 'cmtt_extensions' );
        define( 'CMTT_SETTINGS_OPTION', 'cmtt_settings' );
        define( 'CMTT_IMPORTEXPORT_OPTION', 'cmtt_importexport' );
        define( 'CMTT_BACKUP_FILENAME', 'exportData.csv' );

        do_action( 'cmtt_setup_constants_after' );
    }

    /**
     * Create custom post type
     */
    public static function cmtt_create_post_types() {
        $createGlossaryTermPages = (bool) get_option( 'cmtt_createGlossaryTermPages', TRUE );
        $glossaryPermalink       = get_option( 'cmtt_glossaryPermalink', 'glossary' );
        $comments                = get_option( 'cmtt_glossaryRemoveCommentsTermPage', 1 );
        /*
         * Decide whether to add RSS feeds for custom post type or not (for fixing problems with missing links in Google Webdeveloper Tools)
         */
        $addFeeds                = get_option( 'cmtt_glossaryAddFeeds', true );

        $singularName      = get_option( 'cmtt_glossaryItemSingularName', 'Glossary Item' );
        $excludeFromSearch = (bool) get_option( 'cmtt_excludeGlossaryTermPagesFromSearch', '0' );

        $args = array(
            'label'               => __( 'Glossary', 'cm-tooltip-glossary' ),
            'labels'              => array(
                'add_new_item'  => __( 'Add New Glossary Item', 'cm-tooltip-glossary' ),
                'add_new'       => __( 'Add Glossary Item', 'cm-tooltip-glossary' ),
                'edit_item'     => __( 'Edit Glossary Item', 'cm-tooltip-glossary' ),
                'view_item'     => __( 'View Glossary Item', 'cm-tooltip-glossary' ),
                'singular_name' => __( $singularName, 'cm-tooltip-glossary' ),
                'name'          => __( CMTT_NAME, 'cm-tooltip-glossary' ),
                'menu_name'     => __( 'Glossary', 'cm-tooltip-glossary' )
            ),
            'description'         => '',
            'map_meta_cap'        => true,
            'publicly_queryable'  => $createGlossaryTermPages,
            'exclude_from_search' => $excludeFromSearch,
            'public'              => $createGlossaryTermPages,
            'show_ui'             => true,
            'show_in_admin_bar'   => true,
            'show_in_menu'        => CMTT_MENU_OPTION,
            '_builtin'            => false,
            'capability_type'     => 'post',
            'capabilities'        => array(
                'edit_posts'   => 'manage_glossary',
                'create_posts' => 'manage_glossary',
            ),
            'hierarchical'        => false,
            'has_archive'         => false,
            'rewrite'             => array( 'slug' => $glossaryPermalink, 'with_front' => false, 'feeds' => true, 'feed' => true ),
            'query_var'           => true,
            'supports'            => array( 'title', 'editor', 'author', 'excerpt', 'revisions',
                'custom-fields', 'page-attributes', 'post-thumbnails', 'thumbnail' ),
        );

        if ( !$comments ) {
            $args[ 'supports' ][] = 'comments';
        }

        register_post_type( 'glossary', apply_filters( 'cmtt_post_type_args', $args ) );

        if ( $addFeeds ) {
            global $wp_rewrite;
            $wp_rewrite->extra_permastructs[ 'glossary' ] = array();
            $args                                         = (object) $args;

            $post_type    = 'glossary';
            $archive_slug = $args->rewrite[ 'slug' ];
            if ( $args->rewrite[ 'with_front' ] ) {
                $archive_slug = substr( $wp_rewrite->front, 1 ) . $archive_slug;
            } else {
                $archive_slug = $wp_rewrite->root . $archive_slug;
            }
            if ( $args->rewrite[ 'feeds' ] && $wp_rewrite->feeds ) {
                $feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
                add_rewrite_rule( "{$archive_slug}/feed/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
                add_rewrite_rule( "{$archive_slug}/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
            }

            $permastruct_args           = $args->rewrite;
            $permastruct_args[ 'feed' ] = $permastruct_args[ 'feeds' ];
            add_permastruct( $post_type, "{$args->rewrite[ 'slug' ]}/%$post_type%", $permastruct_args );
        } else {
//			add_filter( 'feed_links_show_posts_feed', array( __CLASS__, 'remove_feeds' ), PHP_INT_MAX );
//			add_filter( 'feed_links_show_comments_feed', array( __CLASS__, 'remove_feeds' ), PHP_INT_MAX );
        }
    }

    public static function remove_feeds( $feed ) {
        global $post;
        if ( !empty( $post ) && in_array( $post->post_type, array( 'glossary' ) ) ) {
            return false;
        }
        return $feed;
    }

    public static function cmtt_admin_menu() {
        global $submenu;
        $current_user = wp_get_current_user();

        add_menu_page( 'Glossary', CMTT_NAME, 'manage_glossary', CMTT_MENU_OPTION, 'edit.php?post_type=glossary', CMTT_PLUGIN_URL . 'assets/css/images/cm-glossary-tooltip-icon.png' );

//        add_submenu_page(CMTT_MENU_OPTION, 'Trash', 'Trash', 'manage_glossary', 'edit.php?post_status=trash&post_type=glossary');
        add_submenu_page( CMTT_MENU_OPTION, 'Add New', 'Add New', 'manage_glossary', 'post-new.php?post_type=glossary' );
        do_action( 'cmtt_add_admin_menu_after_new' );
        add_submenu_page( CMTT_MENU_OPTION, 'TooltipGlossary Options', 'Settings', 'manage_options', CMTT_SETTINGS_OPTION, array( self::$calledClassName, 'outputOptions' ) );
        add_submenu_page( CMTT_MENU_OPTION, 'TooltipGlossary Import/Export', 'Import/Export', 'manage_options', CMTT_IMPORTEXPORT_OPTION, array( self::$calledClassName, 'cmtt_importExport' ) );

        $glossaryItemsPerPage = get_user_meta( get_current_user_id(), 'edit_glossary_per_page', true );
        if ( $glossaryItemsPerPage && intval( $glossaryItemsPerPage ) > 100 ) {
            update_user_meta( get_current_user_id(), 'edit_glossary_per_page', 100 );
        }

        add_filter( 'views_edit-glossary', array( self::$calledClassName, 'cmtt_filter_admin_nav' ), 10, 1 );
    }

    public static function cmtt_about() {
        ob_start();
        require 'views/backend/admin_about.php';
        $content = ob_get_contents();
        ob_end_clean();
        require 'views/backend/admin_template.php';
    }

    /**
     * Shows extensions page
     */
    public static function cmtt_extensions() {
        ob_start();
        include_once 'views/backend/admin_extensions.php';
        $content = ob_get_contents();
        ob_end_clean();
        require 'views/backend/admin_template.php';
    }

    public static function cmtt_importExport() {
        $showCredentialsForm    = self::_cmtt_backupGlossary();
        $showBackupDownloadLink = self::_cmtt_getBackupGlossary( false );

        ob_start();
        include 'views/backend/admin_importexport.php';
        $content = ob_get_contents();
        ob_end_clean();
        include 'views/backend/admin_template.php';
    }

    public static function cmtt_glossary_handleexport() {
        if ( !empty( $_POST[ 'cmtt_doExport' ] ) ) {
            self::_cmtt_exportGlossary();
        } elseif ( !empty( $_POST[ 'cmtt_doImport' ] ) && !empty( $_FILES[ 'importCSV' ] ) && is_uploaded_file( $_FILES[ 'importCSV' ][ 'tmp_name' ] ) ) {
            self::_cmtt_importGlossary( $_FILES[ 'importCSV' ] );
        }
    }

    /**
     * Function enqueues the scripts and styles for the admin Settings view
     * @global type $parent_file
     * @return type
     */
    public static function cmtt_glossary_admin_settings_scripts() {
        global $parent_file;
        if ( CMTT_MENU_OPTION !== $parent_file ) {
            return;
        }

        wp_enqueue_style( 'jqueryUIStylesheet', self::$cssPath . 'jquery-ui-1.10.3.custom.css' );
        wp_enqueue_style( 'cmtooltip', self::$cssPath . 'tooltip.css' );
        wp_enqueue_style( 'wp-color-picker' );

        wp_enqueue_script( 'tooltip-admin-js', self::$jsPath . 'cm-tooltip.js', array( 'jquery', 'wp-color-picker', 'jquery-ui-core', 'jquery-ui-tooltip' ) );

        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-tooltip' );
        wp_enqueue_script( 'jquery-ui-tabs' );

        $tooltipData[ 'ajaxurl' ] = admin_url( 'admin-ajax.php' );
        wp_localize_script( 'tooltip-admin-js', 'cmtt_data', $tooltipData );
    }

    /**
     * Function outputs the scripts and styles for the edit views
     * @global type $typenow
     * @return type
     */
    public static function cmtt_glossary_admin_edit_scripts() {
        global $typenow;

        $defaultPostTypes         = get_option( 'cmtt_allowed_terms_metabox_all_post_types' ) ? get_post_types() : array( 'post', 'page' );
        $allowedTermsBoxPostTypes = apply_filters( 'cmtt_allowed_terms_metabox_posttypes', $defaultPostTypes );

        if ( !in_array( $typenow, $allowedTermsBoxPostTypes ) ) {
            return;
        }

        wp_enqueue_style( 'cmtooltip', self::$cssPath . 'tooltip.css' );
        wp_enqueue_script( 'tooltip-admin-js', self::$jsPath . 'cm-tooltip.js', array( 'jquery', 'wp-color-picker', 'jquery-ui-core', 'jquery-ui-tooltip' ) );
    }

    /**
     * Filters admin navigation menus to show horizontal link bar
     * @global string $submenu
     * @global type $plugin_page
     * @param type $views
     * @return string
     */
    public static function cmtt_filter_admin_nav( $views ) {
        global $submenu, $plugin_page;
        $scheme     = is_ssl() ? 'https://' : 'http://';
        $adminUrl   = str_replace( $scheme . $_SERVER[ 'HTTP_HOST' ], '', admin_url() );
        $currentUri = str_replace( $adminUrl, '', $_SERVER[ 'REQUEST_URI' ] );
        $submenus   = array();
        if ( isset( $submenu[ CMTT_MENU_OPTION ] ) ) {
            $thisMenu = $submenu[ CMTT_MENU_OPTION ];

            $firstMenuItem = $thisMenu[ 0 ];
            unset( $thisMenu[ 0 ] );

            $secondMenuItem = array( 'Trash', 'manage_glossary', 'edit.php?post_status=trash&post_type=glossary', 'Trash' );

            array_unshift( $thisMenu, $firstMenuItem, $secondMenuItem );

            foreach ( $thisMenu as $item ) {
                $slug                   = $item[ 2 ];
                $isCurrent              = ($slug == $plugin_page || strpos( $item[ 2 ], '.php' ) === strpos( $currentUri, '.php' ));
                $isExternalPage         = strpos( $item[ 2 ], 'http' ) !== FALSE;
                $isNotSubPage           = $isExternalPage || strpos( $item[ 2 ], '.php' ) !== FALSE;
                $url                    = $isNotSubPage ? $slug : get_admin_url( null, 'admin.php?page=' . $slug );
                $target                 = $isExternalPage ? '_blank' : '';
                $submenus[ $item[ 0 ] ] = '<a href="' . $url . '" target="' . $target . '" class="' . ($isCurrent ? 'current' : '') . '">' . $item[ 0 ] . '</a>';
            }
        }
        return $submenus;
    }

    public static function cmtt_restrict_manage_posts() {
        global $typenow, $wp_query;
        if ( $typenow == 'glossary' ) {
            $status  = get_query_var( 'post_status' );
            $options = apply_filters( 'cmtt_glossary_restrict_manage_posts', array( 'published' => 'Published', 'draft' => 'Draft', 'trash' => 'Trash' ) );

            echo '<select name="post_status">';
            foreach ( $options as $key => $label ) {
                echo '<option value="' . $key . '" ' . selected( $key, $status ) . '>' . __( $label, 'cm-tooltip-glossary' ) . '</option>';
            }
            echo '</select>';

            /*
             * create an array of taxonomy slugs you want to filter by - if you want to retrieve all taxonomies, could use get_taxonomies() to build the list
             */
            $filters = get_object_taxonomies( 'glossary' );

            foreach ( $filters as $tax_slug ) {
                // retrieve the taxonomy object
                $tax_obj  = get_taxonomy( $tax_slug );
                $tax_name = $tax_obj->labels->name;
                // retrieve array of term objects per taxonomy
                $terms    = get_terms( $tax_slug );

                $currentValue = get_query_var( $tax_slug );

                // output html for taxonomy dropdown filter
                echo '<select name="' . $tax_slug . '" id="' . $tax_slug . '" class="postform">';
                echo '<option value="">Show All ' . $tax_name . '</option>';
                foreach ( $terms as $term ) {
                    echo '<option value="' . $term->slug . '" ' . selected( $term->slug, $currentValue ) . '>' . $term->name . ' (' . $term->count . ')</option>';
                }
                echo '</select>';
            }
        }
    }

    /**
     * Displays the horizontal navigation bar
     * @global string $submenu
     * @global type $plugin_page
     */
    public static function cmtt_showNav() {
        global $submenu, $plugin_page;
        $submenus   = array();
        $scheme     = is_ssl() ? 'https://' : 'http://';
        $adminUrl   = str_replace( $scheme . $_SERVER[ 'HTTP_HOST' ], '', admin_url() );
        $currentUri = str_replace( $adminUrl, '', $_SERVER[ 'REQUEST_URI' ] );

        if ( isset( $submenu[ CMTT_MENU_OPTION ] ) ) {
            $thisMenu = $submenu[ CMTT_MENU_OPTION ];
            foreach ( $thisMenu as $item ) {
                $slug           = $item[ 2 ];
                $isCurrent      = ($slug == $plugin_page || strpos( $item[ 2 ], '.php' ) === strpos( $currentUri, '.php' ));
                $isExternalPage = strpos( $item[ 2 ], 'http' ) !== FALSE;
                $isNotSubPage   = $isExternalPage || strpos( $item[ 2 ], '.php' ) !== FALSE;
                $url            = $isNotSubPage ? $slug : get_admin_url( null, 'admin.php?page=' . $slug );
                $submenus[]     = array(
                    'link'    => $url,
                    'title'   => $item[ 0 ],
                    'current' => $isCurrent,
                    'target'  => $isExternalPage ? '_blank' : ''
                );
            }
            require('views/backend/admin_nav.php');
        }
    }

    /**
     * Returns TRUE if the tooltip should be clickable
     */
    public static function isTooltipClickable( $isClickable ) {
        $isClickableArr[ 'is_clickable' ] = (bool) get_option( 'cmtt_tooltipIsClickable' );
        $isClickableArr[ 'edit_link' ]    = (bool) get_option( 'cmtt_glossaryAddTermEditlink' ) && current_user_can( 'manage_glossary' );

        $isClickable = in_array( TRUE, $isClickableArr );
        return $isClickable;
    }

    /**
     * Adds a notice about wp version lower than required 3.3
     * @global type $wp_version
     */
    public static function cmtt_glossary_admin_notice_wp33() {
        global $wp_version;

        if ( version_compare( $wp_version, '3.3', '<' ) ) {
            $message = sprintf( __( '%s requires Wordpress version 3.3 or higher to work properly.', 'cm-tooltip-glossary' ), CMTT_NAME );
            cminds_show_message( $message, true );
        }
    }

    /**
     * Adds a notice about mbstring not being installed
     * @global type $wp_version
     */
    public static function cmtt_glossary_admin_notice_mbstring() {
        $mb_support = function_exists( 'mb_strtolower' );

        if ( !$mb_support ) {
            $message = sprintf( __( '%s since version 2.6.0 requires "mbstring" PHP extension to work! ', 'cm-tooltip-glossary' ), CMTT_NAME );
            $message .= '<a href="http://www.php.net/manual/en/mbstring.installation.php" target="_blank">(' . __( 'Installation instructions.', 'cm-tooltip-glossary' ) . '</a>';
            cminds_show_message( $message, true );
        }
    }

    /**
     * Adds a notice about too many glossary items for client pagination
     * @global type $wp_version
     */
    public static function cmtt_glossary_admin_notice_client_pagination() {
        $serverSide         = get_option( 'cmtt_glossaryServerSidePagination' );
        $glossaryItemsCount = wp_count_posts( 'glossary' );

        if ( !$serverSide && (int) $glossaryItemsCount->publish > 4000 ) {
            $message = sprintf( __( '%s has detected that your glossary has more than 4000 terms and the "Client-side" pagination has been selected.', 'cm-tooltip-glossary' ), CMTT_NAME );
            $message .= '<br/>';
            $message .= __( 'Please switch to the "Server-side" pagination to avoid slowness and problems with the server memory on the Glossary Index Page.', 'cm-tooltip-glossary' );
            cminds_show_message( $message, true );
        }
    }

    /**
     * Filters the tooltip content
     * @param type $glossaryItemContent
     * @param type $glossaryItemPermalink
     * @return type
     */
    public static function cmtt_glossary_filterTooltipContent( $glossaryItemContent, $glossaryItem ) {
        $glossaryItemPermalink = get_permalink( $glossaryItem );
        $glossaryItemContent   = str_replace( '[glossary_exclude]', '', $glossaryItemContent );
        $glossaryItemContent   = str_replace( '[/glossary_exclude]', '', $glossaryItemContent );

        if ( get_option( 'cmtt_glossaryNoFilters' ) != 1 ) {

            if ( get_option( 'cmtt_glossaryFilterTooltipImg' ) != 1 ) {
                $glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<img>' );
            }

            if ( get_option( 'cmtt_glossaryFilterTooltipA' ) != 1 ) {
                $glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<a>' );
            }

            if ( get_option( 'cmtt_glossaryFilterTooltip' ) == 1 ) {
                // remove paragraph, bad chars from tooltip text
                $glossaryItemContent = str_replace( array( chr( 10 ), chr( 13 ) ), array( '', '' ), $glossaryItemContent );
                $glossaryItemContent = str_replace( array( '</p>', '</ul>', '</li>' ), array( '<br/>', '<br/>', '<br/>' ), $glossaryItemContent );
                $glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<li>' );
                $glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<ul>' );
                $glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<p>' );
                $glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<h1>' );
                $glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<h2>' );
                $glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<h3>' );
                $glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<h4>' );
                $glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<h5>' );
                $glossaryItemContent = self::cmtt_strip_only( $glossaryItemContent, '<h6>' );
                $glossaryItemContent = htmlspecialchars( $glossaryItemContent );
                $glossaryItemContent = esc_attr( $glossaryItemContent );
                $glossaryItemContent = str_replace( "color:#000000", "color:#ffffff", $glossaryItemContent );
                $glossaryItemContent = str_replace( '\\[glossary_exclude\\]', '', $glossaryItemContent );
            } else {
                $glossaryItemContent = strtr( $glossaryItemContent, array( "\r\n" => '<br />', "\r" => '<br />', "\n" => '<br />' ) );
            }
        }

        /*
         * 10.06.2015 added check for (get_option('cmtt_createGlossaryTermPages', TRUE)
         */
        if ( (get_option( 'cmtt_createGlossaryTermPages', TRUE ) && get_option( 'cmtt_glossaryLimitTooltip' ) > 30) && (strlen( $glossaryItemContent ) > get_option( 'cmtt_glossaryLimitTooltip' )) ) {

            $glossaryItemContent = cminds_truncate( html_entity_decode( $glossaryItemContent ), get_option( 'cmtt_glossaryLimitTooltip' ), get_option( 'cmtt_glossaryLimitTooltipSymbol', '(...)' ) );
        }

        return esc_attr( $glossaryItemContent );
    }

    /**
     * Strips just one tag
     * @param type $str
     * @param type $tags
     * @param type $stripContent
     * @return type
     */
    public static function cmtt_strip_only( $str, $tags, $stripContent = false ) {
        $content = '';
        if ( !is_array( $tags ) ) {
            $tags = (strpos( $str, '>' ) !== false ? explode( '>', str_replace( '<', '', $tags ) ) : array( $tags ));
            if ( end( $tags ) == '' ) {
                array_pop( $tags );
            }
        }
        foreach ( $tags as $tag ) {
            if ( $stripContent ) {
                $content = '(.+</' . $tag . '[^>]*>|)';
            }
            $str = preg_replace( '#</?' . $tag . '[^>]*>' . $content . '#is', '', $str );
        }
        return $str;
    }

    /**
     * Disable the parsing for some reason
     * @global type $wp_query
     * @param type $smth
     * @return type
     */
    public static function remove_excerpt_parsing( $smth ) {
        remove_filter( 'the_content', array( self::$calledClassName, 'cmtt_glossary_parse' ), 20000 );
        return $smth;
    }

    /**
     * Reenable the parsing for some reason
     * @global type $wp_query
     * @param type $smth
     * @return type
     */
    public static function add_parsing_after_excerpt( $smth ) {
        add_filter( 'the_content', array( self::$calledClassName, 'cmtt_glossary_parse' ), 20000 );
        return $smth;
    }

    /**
     * Disable the parsing for some reason
     * @global type $wp_query
     * @param type $smth
     * @return type
     */
    public static function cmtt_disable_parsing( $smth ) {
        global $wp_query;
        if ( $wp_query->is_main_query() && !$wp_query->is_singular ) {  // to prevent conflict with Yost SEO
            remove_filter( 'the_content', array( self::$calledClassName, 'cmtt_glossary_parse' ), 20000 );
            remove_filter( 'the_content', array( self::$calledClassName, 'cmtt_glossary_addBacklink' ), 21000 );
            do_action( 'cmtt_disable_parsing' );
        }
        return $smth;
    }

    /**
     * Reenable the parsing for some reason
     * @global type $wp_query
     * @param type $smth
     * @return type
     */
    public static function cmtt_reenable_parsing( $smth ) {
        add_filter( 'the_content', array( self::$calledClassName, 'cmtt_glossary_parse' ), 20000 );
        add_filter( 'the_content', array( self::$calledClassName, 'cmtt_glossary_addBacklink' ), 21000 );
        do_action( 'cmtt_reenable_parsing' );
        return $smth;
    }

    /**
     * Function strips the shortcodes if the option is set
     * @param type $content
     * @return type
     */
    public static function cmtt_glossary_parse_strip_shortcodes( $content, $glossaryItem ) {
        if ( get_option( 'cmtt_glossaryTooltipStripShortcode' ) == 1 ) {
            $content = strip_shortcodes( $content );
        } else {
            $content = do_shortcode( $content );
        }

        return $content;
    }

    /**
     * Function returns TRUE if the given post should be parsed
     * @param type $post
     * @param type $force
     * @return boolean
     */
    public static function cmtt_isParsingRequired( $post, $force = false, $from_cache = false ) {
        static $requiredAtLeastOnce = false;
        if ( $from_cache ) {
            /*
             * Could be used to load JS/CSS in footer only when needed
             */
            return $requiredAtLeastOnce;
        }

        if ( $force ) {
            return TRUE;
        }

        if ( !is_object( $post ) ) {
            return FALSE;
        }

        /*
         *  Skip parsing for excluded pages and posts (except glossary pages?! - Marcin)
         */
        $parsingDisabled = get_post_meta( $post->ID, '_glossary_disable_for_page', true ) == 1;
        if ( $parsingDisabled ) {
            return FALSE;
        }

        $currentPostType             = get_post_type( $post );
        $showOnPostTypes             = get_option( 'cmtt_glossaryOnPosttypes' );
        $showOnHomepageAuthorpageEtc = (!is_page( $post ) && !is_single( $post ) && !is_singular( $post ) && get_option( 'cmtt_glossaryOnlySingle' ) == 0);
        $onMainQueryOnly             = (get_option( 'cmtt_glossaryOnMainQuery' ) == 1 ) ? is_main_query() : TRUE;
        $noHomepage                  = (get_option( 'cmtt_glossaryOnlySingle' ) == 1 && is_front_page());

        if ( !is_array( $showOnPostTypes ) ) {
            $showOnPostTypes = array();
        }
        $showOnSingleCustom = (is_singular( $post ) && in_array( $currentPostType, $showOnPostTypes ));

        $condition = ( $showOnHomepageAuthorpageEtc || ($showOnSingleCustom && !$noHomepage) );

        $result = $onMainQueryOnly && $condition;
        if ( $result ) {
            $requiredAtLeastOnce = TRUE;
        }
        $result = apply_filters( 'cmtt_isParsingRequiredResult', $result, $post, $force, $from_cache );
        return $result;
    }

    public static function prepare_parser_string_arr() {
        /*
         * Initialize $glossarySearchStringArr as empty array
         */
        global $glossaryIndexArr, $onlySynonyms, $caseSensitive;

        $glossarySearchStringArr    = array();
        $glossarySearchStringArrays = array();
        $onlySynonyms               = array();

//		$parserArrays = get_transient( $transientKey );
//		if ( $parserArrays !== false ) {
//			$glossarySearchStringArr = $parserArrays[ 'search_string' ];
//			$glossaryIndexArr		 = $parserArrays[ 'index' ];
//			return $glossarySearchStringArr;
//		}

        $args           = apply_filters( 'cmtt_parser_query_args', array(
            'nopaging'    => true,
            'numberposts' => -1,
        ) );
        $glossary_index = CMTT_Pro::getGlossaryItemsSorted( $args );

        //the tag:[glossary_exclude]+[/glossary_exclude] can be used to mark text will not be taken into account by the glossary
        if ( $glossary_index ) {

            $caseSensitive = get_option( 'cmtt_glossaryCaseSensitive', 0 );

            /*
             * The loops prepares the search query for the replacement
             */
            foreach ( $glossary_index as $glossary_item ) {
                $dontParseTerm = (bool) get_post_meta( $glossary_item->ID, '_cmtt_exclude_parsing', true );
                if ( $dontParseTerm ) {
                    continue;
                }
                $glossary_title = str_replace( '&#039;', '’', preg_quote( htmlspecialchars( trim( $glossary_item->post_title ), ENT_QUOTES, 'UTF-8' ), '/' ) );

                $addition                           = '';
                $synonymsArr                        = CMTT_Synonyms::getSynonymsArr( $glossary_item->ID, true );
                $onlySynonyms[ $glossary_item->ID ] = $synonymsArr;

                $variationsArr = CMTT_Synonyms::getSynonymsArr( $glossary_item->ID, false );
                $synonyms      = array_merge( $synonymsArr, $variationsArr );
                $synonyms2     = array();

                if ( !empty( $synonyms ) && count( $synonyms ) > 0 ) {
                    foreach ( $synonyms as $val ) {
                        $val = str_replace( '&#039;', '’', preg_quote( htmlspecialchars( trim( $val ), ENT_QUOTES, 'UTF-8' ), '/' ) );
                        if ( !empty( $val ) ) {
                            $synonyms2[] = $val;
                        }
                    }
                    if ( !empty( $synonyms2 ) ) {
                        $addition = '|' . implode( '|', $synonyms2 );
                    }
                }

                $additionFiltered = apply_filters( 'cmtt_parse_addition_add', $addition, $glossary_item );

                $glossaryIndexArrKey = $glossary_title . $additionFiltered;
                if ( !$caseSensitive ) {
                    $glossaryIndexArrKey = mb_strtolower( $glossaryIndexArrKey );
                }
                $glossarySearchStringArr[ $glossary_item->ID ] = $glossary_title . $additionFiltered;
                if ( !empty( $glossary_item->parseSeparately ) ) {
                    $glossarySearchStringArrays[] = $glossarySearchStringArr;
                    $glossarySearchStringArr      = array();
                }
                $glossaryIndexArr[ $glossaryIndexArrKey ] = $glossary_item->ID;
            }
        }


        $glossarySearchStringArrays[] = $glossarySearchStringArr;
//		$parserArrays = array(
//			'search_string'	 => $glossarySearchStringArr,
//			'index'			 => $glossaryIndexArr,
//		);
//		set_transient( $transientKey, $parserArrays, 60 * 60 * 24 * 30 );

        return $glossarySearchStringArrays;
    }

    public static function cmtt_glossary_parse( $content, $force = false ) {
        global $post, $wp_query, $caseSensitive, $replacedTerms;

        if ( apply_filters( 'cmtt_glossary_parse_post', $post, $content, $force ) === NULL ) {
            return $content;
        }

        $seo = doing_action( 'wpseo_head' );
        if ( $seo ) {
            return $content;
        }

        static $initializeReplacedTerms = TRUE;

        if ( !is_object( $post ) ) {
            $post = $wp_query->post;
        }

        $runParser = apply_filters( 'cmtt_runParser', self::cmtt_isParsingRequired( $post, $force ), $post, $content, $force );
        if ( !$runParser ) {
            return $content;
        }

        /*
         * If there's more than one query and the "Only higlight once"
         */
        if ( (get_option( 'cmtt_glossaryOnMainQuery' ) != 1 ) && self::isHightlightOnlyOnceEnabled( $post ) ) {
            $initializeReplacedTerms = true;
        }

        /*
         * Run the glossary parser
         */
        $contentHash    = md5( 'cmtt_' . $post->ID . $content );
        $contentHashKey = 'cmtt_content_hash_for_' . $post->ID;

        if ( !$force ) {
            if ( !get_option( 'cmtt_glossaryEnableCaching', FALSE ) ) {
                wp_cache_delete( $contentHash );
                $oldContentHash = (string) get_transient( $contentHashKey );
                delete_transient( $contentHash );
                if ( !empty( $oldContentHash ) ) {
                    delete_transient( $oldContentHash );
                }
                delete_transient( $contentHashKey );
            }
            $result = get_transient( $contentHash );
            if ( $result !== false ) {
                /*
                 * Need to fake that
                 */
                $replacedTerms = true;
                return $result;
            }
        }

        $glossarySearchStringArrays = self::prepare_parser_string_arr();

        /*
         * No replace required if there's no glossary items
         */
        if ( !empty( $glossarySearchStringArrays ) && is_array( $glossarySearchStringArrays ) ) {

            foreach ( $glossarySearchStringArrays as $glossarySearchStringArr ) {
                /*
                 * Don't highlight glossary term on it's own page
                 */
                if ( $post->post_type == 'glossary' ) {
                    unset( $glossarySearchStringArr[ $post->ID ] );
                }

                $excludeGlossary_regex = '/\\['   // Opening bracket
                . '(\\[?)'   // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
                . "(glossary_exclude)"   // 2: Shortcode name
                . '\\b'   // Word boundary
                . '('  // 3: Unroll the loop: Inside the opening shortcode tag
                . '[^\\]\\/]*' // Not a closing bracket or forward slash
                . '(?:'
                . '\\/(?!\\])'   // A forward slash not followed by a closing bracket
                . '[^\\]\\/]*'   // Not a closing bracket or forward slash
                . ')*?'
                . ')'
                . '(?:'
                . '(\\/)'   // 4: Self closing tag ...
                . '\\]'  // ... and closing bracket
                . '|'
                . '\\]'  // Closing bracket
                . '(?:'
                . '('   // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
                . '[^\\[]*+' // Not an opening bracket
                . '(?:'
                . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
                . '[^\\[]*+'   // Not an opening bracket
                . ')*+'
                . ')'
                . '\\[\\/\\2\\]' // Closing shortcode tag
                . ')?'
                . ')'
                . '(\\]?)/s';

                $excludeGlossaryStrs = array();

                /*
                 * Fix for the &amp; character and the AMP term
                 */
                $content = str_replace( '&#038;', '[glossary_exclude]&#038;[/glossary_exclude]', $content );

                /*
                 * Replace exclude tags and content between them in purpose to save the original text as is
                 * before glossary plug go over the content and add its code
                 * (later will be returned to the marked places in content)
                 */
                $excludeTagsCount = preg_match_all( $excludeGlossary_regex, $content, $excludeGlossaryStrs, PREG_PATTERN_ORDER );
                $i                = 0;

                if ( $excludeTagsCount > 0 ) {
                    foreach ( $excludeGlossaryStrs[ 0 ] as $excludeStr ) {
                        $content = preg_replace( $excludeGlossary_regex, '#' . $i . 'excludeGlossary', $content, 1 );
                        $i++;
                    }
                }

                $glossaryArrayChunk = apply_filters( 'cmtt_parse_array_chunk_size', 75 );
                $spaceSeparated     = apply_filters( 'cmtt_parse_space_separated_only', 1 );

                /*
                 * Initialize the array just once to make the "Highlight only the first occurance" work regardless of the filter parsing was attached to
                 */
                if ( $initializeReplacedTerms ) {
                    $replacedTerms           = array();
                    $initializeReplacedTerms = FALSE;
                }

                if ( count( $glossarySearchStringArr ) > $glossaryArrayChunk ) {
                    $chunkedGlossarySearchStringArr = array_chunk( $glossarySearchStringArr, $glossaryArrayChunk, TRUE );
                    $glossarySearchStringArr        = null;

                    foreach ( $chunkedGlossarySearchStringArr as $glossarySearchStringArrChunk ) {
                        $glossarySearchString = '/' . (($spaceSeparated) ? '(?<=\P{L}|^)(?<!(\p{N}))' : '') . '(?!(<|&lt;))(' . (!$caseSensitive ? '(?i)' : '') . implode( '|', $glossarySearchStringArrChunk ) . ')(?!(>|&gt;))' . (($spaceSeparated) ? '(?=\P{L}|$)(?!(\p{N}))' : '') . '/u';
                        $content              = self::cmtt_str_replace( $content, $glossarySearchString );
                    }
                } else {
                    $glossarySearchString = '/' . (($spaceSeparated) ? '(?<=\P{L}|^)(?<!(\p{N}))' : '') . '(?!(<|&lt;))(' . (!$caseSensitive ? '(?i)' : '') . implode( '|', $glossarySearchStringArr ) . ')(?!(>|&gt;))' . (($spaceSeparated) ? '(?=\P{L}|$)(?!(\p{N}))' : '') . '/u';
                    $content              = self::cmtt_str_replace( $content, $glossarySearchString );
                }

                if ( $excludeTagsCount > 0 ) {
                    $i = 0;
                    foreach ( $excludeGlossaryStrs[ 0 ] as $excludeStr ) {
                        $content = str_replace( '#' . $i . 'excludeGlossary', $excludeStr, $content );
                        $i++;
                    }
                    //remove all the exclude signs
                    $content = str_replace( array( '[glossary_exclude]', '[/glossary_exclude]' ), array( '', '' ), $content );
                }
            }
        }

        $content = apply_filters( 'cmtt_parsed_content', $content );

        if ( get_option( 'cmtt_glossaryEnableCaching', FALSE ) ) {
            /*
             * Cache for a month - in case invalidator function doesn't work
             * Save the content hash, so we can invalidate after content change
             */
            set_transient( $contentHashKey, $contentHash, 60 * 60 * 24 * 30 );
            $result = set_transient( $contentHash, $content, 60 * 60 * 24 * 30 );
        }

        return $content;
    }

    /**
     * Link some text/phrase to existing tooltip
     * [cm_tooltip_link_to_term term="WordPress"]Sidebars[/cm_tooltip_link_to_term]
     * @global type $cmWrapItUp
     * @param type $atts
     * @param type $content
     * @return type
     */
    public static function cmtt_link_to_term( $atts, $content = '' ) {
        $args = shortcode_atts(
        array(
            'term' => NULL
        ), $atts );

        if ( empty( $args[ 'term' ] ) ) {
            return $content;
        } else {
            $term = get_page_by_title( $args[ 'term' ], OBJECT, 'glossary' );

            if ( !empty( $term ) ) {
                global $cmtt_temporaryAdditions;

                $cmtt_temporaryAdditions[ $term->ID ][] = $content;
                add_filter( 'cmtt_parse_addition_add', array( __CLASS__, 'addTemporaryAdditions' ), 10, 2 );

                add_filter( 'cmtt_highlight_first_only', array( __CLASS__, 'overrideFirstOnly' ), 10 );
                $cmWrapItUp = true;
                $result     = apply_filters( 'cm_tooltip_parse', $content, true );
                $cmWrapItUp = false;
                remove_filter( 'cmtt_highlight_first_only', array( __CLASS__, 'overrideFirstOnly' ), 10 );
            }
        }

        return $content;
    }

    /**
     * The only reason for this function is to disable the "Highlight Only First Occurrence" for "cm_tooltip_link_to_term" shortcode
     * @param type $highlightFirstOnly
     * @return boolean
     */
    public static function overrideFirstOnly($highlightFirstOnly){
        return FALSE;
    }

    /**
     * Adds abbreviations to parsing
     * @param type $addition
     * @param type $glossary_item
     * @return type
     */
    public static function addTemporaryAdditions( $addition, $glossary_item ) {
        global $cmtt_temporaryAdditions;

        $termId            = $glossary_item->ID;
        $temporaryAddition = !empty( $cmtt_temporaryAdditions[ $termId ] ) ? implode( '|', $cmtt_temporaryAdditions[ $termId ] ) : '';

        if ( !empty( $temporaryAddition ) ) {
            $addition .= '|' . preg_quote( str_replace( '\'', '&#39;', htmlspecialchars( trim( $temporaryAddition ), ENT_QUOTES, 'UTF-8' ) ), '/' );
        }
        return $addition;
    }

    /**
     * [cm_tooltip_parse]content[/cm_tooltip_parse]
     * @param type $atts
     * @param type $content
     * @return type
     */
    public static function cm_tooltip_parse( $atts, $content = '' ) {
        global $cmWrapItUp;
        $atts = $atts;

        $cmWrapItUp = true;
        $result     = apply_filters( 'cm_tooltip_parse', $content, true );
        $cmWrapItUp = false;
        return $result;
    }

    /**
     * Replaces the matches
     * @param type $match
     * @return type
     */
    public static function cmtt_replace_matches( $match ) {
        if ( !empty( $match[ 0 ] ) ) {
            $replacementText = self::cmtt_prepareReplaceTemplate( htmlspecialchars_decode( $match[ 0 ], ENT_COMPAT ) );
            return $replacementText;
        }
    }

    /**
     * Function which prepares the templates for the glossary words found in text
     *
     * @param string $title replacement text
     * @return array|string
     */
    public static function cmtt_prepareReplaceTemplate( $title ) {
        /*
         * Placeholder for the title
         */
        $titlePlaceholder = '##TITLE_GOES_HERE##';

        /*
         * Array of glossary items, settings
         */
        global $glossaryIndexArr, $caseSensitive, $templatesArr, $removeLinksToTerms, $replacedTerms, $post;

        /*
         *  Checks whether to show tooltips on this page or not
         */
        $tooltipsDisabled = CMTT_Glossary_Index::disableTooltips( false, $post );

        /*
         *  Checks whether to show links to glossary pages or not
         */
        $linksDisabled = CMTT_Pro::_get_meta( '_glossary_disable_links_for_page', $post->ID ) == 1;


        /*
         * If TRUE then the links to glossary pages are exchanged with spans
         */
        $removeLinksToTerms = (get_option( 'cmtt_glossaryTermLink' ) == 1 || $linksDisabled || !get_option( 'cmtt_createGlossaryTermPages' ) );

        /*
         * If "Highlight first occurance only" option is set
         */
        $highlightFirstOccuranceOnly = self::isHightlightOnlyOnceEnabled( $post );

        /*
         * If it's case insensitive, then the term keys are stored as lowercased
         */
        $normalizedTitle = str_replace( '&#039;', "’", preg_quote( htmlspecialchars( trim( $title ), ENT_QUOTES, 'UTF-8' ), '/' ) );
        $titleIndex      = (!$caseSensitive) ? mb_strtolower( $normalizedTitle ) : $normalizedTitle;

        try {
            do_action( 'cmtt_replace_template_before_synonyms', $titleIndex, $title );
        } catch ( GlossaryTooltipException $ex ) {
            /*
             * Trick to stop the execution
             */
            $message = $ex->getMessage();
            return $message;
        }

        /*
         * Upgrade to make it work with synonyms
         */
        if ( $glossaryIndexArr ) {
            /*
             * First - look for exact keys
             */
            if ( array_key_exists( $titleIndex, $glossaryIndexArr ) ) {
                $glossary_item_id = $glossaryIndexArr[ $titleIndex ];
            } else {
                /*
                 * If not found - try the synonyms
                 */
                foreach ( $glossaryIndexArr as $key => $value ) {
                    /*
                     * If we find the term we make sure it's a synonym and not a part of some other term
                     */
                    if ( strstr( $key, '|' ) && strstr( $key, $titleIndex ) ) {
                        $synonymsArray = explode( '|', $key );
                        if ( in_array( $titleIndex, $synonymsArray ) ) {
                            /*
                             * $replace = Glossary Post
                             */
                            $glossary_item_id = $value;
                            break;
                        }
                    }
                }
            }
        }

        $glossary_item = get_post( $glossary_item_id );

        try {
            do_action( 'cmtt_replace_template_after_synonyms', $glossary_item, $titleIndex, $title );
        } catch ( GlossaryTooltipException $ex ) {
            /*
             * Trick to stop the execution
             */
            $message = $ex->getMessage();
            return $message;
        }

        /*
         * Error checking
         */
        if ( !is_object( $glossary_item ) ) {
            return 'Error! Post not found for word:' . $titleIndex;
        }

        $id = $glossary_item->ID;

        if ( !is_array( $replacedTerms ) ) {
            return $title;
        }

        /**
         *  If "Highlight first occurance only" option is set, we check if the post has already been highlighted
         */
        if ( $highlightFirstOccuranceOnly && is_array( $replacedTerms ) && !empty( $replacedTerms ) ) {
            foreach ( $replacedTerms as $replacedTerm ) {
                if ( $replacedTerm[ 'postID' ] == $id ) {
                    /*
                     * If the post has already been highlighted
                     */
                    return $title;
                }
            }
        }

        /*
         * Save the post item to the global array so it can be used to generate "Related Terms" list
         */
        $replacedTerms[ $title ][ 'post' ] = $glossary_item;

        /*
         * Save the post item ID to the global array so it's easy to find out if it has been highlighted in text or not
         */
        $replacedTerms[ $title ][ 'postID' ] = $id;

        /*
         * Replacement is already cached - use it
         */
        if ( !empty( $templatesArr[ $id ] ) ) {
            $templateReplaced = str_replace( $titlePlaceholder, $title, $templatesArr[ $id ] );
            return $templateReplaced;
        }

        $additionalClass = apply_filters( 'cmtt_term_tooltip_additional_class', '', $glossary_item );
        $excludeTT       = CMTT_Pro::_get_meta( '_cmtt_exclude_tooltip', $glossary_item->ID ) || $tooltipsDisabled;
        $permalink       = apply_filters( 'cmtt_term_tooltip_permalink', get_permalink( $glossary_item ), $glossary_item );

        /*
         * Open in new window
         */
        $windowTarget    = (get_option( 'cmtt_glossaryInNewPage' ) == 1) ? ' target="_blank" ' : '';
        $titleAttrPrefix = __( get_option( 'cmtt_titleAttributeLabelPrefix', 'Glossary:' ), 'cm-tooltip-glossary' );
        $titleAttr       = (get_option( 'cmtt_showTitleAttribute' ) == 1) ? ' title="' . $titleAttrPrefix . ' ' . esc_attr( $glossary_item->post_title ) . '" ' : '';

        if ( get_option( 'cmtt_glossaryTooltip' ) == 1 && $excludeTT != 1 ) {
            $tooltipContent = apply_filters( 'cmtt_term_tooltip_content', '', $glossary_item );
            /*
             * Apply filters for 3rd party widgets additions
             */
            $tooltipContent = apply_filters( 'cmtt_3rdparty_tooltip_content', $tooltipContent, $glossary_item, false );
            /*
             * Add filter to change the glossary item content on the glossary list
             */
            $tooltipContent = apply_filters( 'cmtt_tooltip_content_add', $tooltipContent, $glossary_item );

            if ( $removeLinksToTerms ) {
                $link_replace = '<span class="glossaryLink ' . $additionalClass . '" ' . $titleAttr . ' data-cmtooltip="' . $tooltipContent . '">' . $titlePlaceholder . '</span>';
            } else {
                $link_replace = '<a href="' . $permalink . '"' . $titleAttr . ' class="glossaryLink ' . $additionalClass . '" data-cmtooltip="' . $tooltipContent . '" ' . $windowTarget . '>' . $titlePlaceholder . '</a>';
            }
        } else {
            if ( $removeLinksToTerms ) {
                $link_replace = '<span  ' . $titleAttr . ' class="glossaryLink">' . $titlePlaceholder . '</span>';
            } else {
                $link_replace = '<a href="' . $permalink . '"' . $titleAttr . ' class="glossaryLink"' . $windowTarget . '>' . $titlePlaceholder . '</a>';
            }
        }

        /*
         * Save with $titlePlaceholder - for the synonyms
         */
        $templatesArr[ $id ] = $link_replace;

        /*
         * Replace it with title to show correctly for the first time
         */
        $link_replace = str_replace( $titlePlaceholder, $title, $link_replace );
        return $link_replace;
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
     * Function adds the title to the tooltip
     * @global type $wpdb
     * @param string $where
     * @return string
     */
    public static function addTitleToTooltip( $glossaryItemContent, $glossary_item ) {
        $showTitle = get_option( 'cmtt_glossaryAddTermTitle' );

        if ( $showTitle == 1 ) {
            $glossaryItemTitle   = '<div class=glossaryItemTitle>' . get_the_title( $glossary_item ) . '</div>';
            $glossaryItemBody    = '<div class=glossaryItemBody>' . $glossaryItemContent . '</div>';
            /*
             * Add the title
             */
            $glossaryItemContent = $glossaryItemTitle . $glossaryItemBody;
        }

        return $glossaryItemContent;
    }

    /**
     * Function adds the editlink
     * @return string
     */
    public static function addEditlinkToTooltip( $glossaryItemContent, $glossary_item ) {
        $showTitle = get_option( 'cmtt_glossaryAddTermEditlink' );

        if ( $showTitle == 1 && current_user_can( 'manage_glossary' ) ) {
            $link                 = '<a href=&quot;' . get_edit_post_link( $glossary_item ) . '&quot;>Edit term</a>';
            $glossaryItemEditlink = '<div class=glossaryItemEditlink>' . $link . '</div>';
            /*
             * Add the editlink
             */
            $glossaryItemContent  = $glossaryItemEditlink . $glossaryItemContent;
        }

        return $glossaryItemContent;
    }

    /**
     * Function adds the page term link at the bottom of the tooltip
     * @return string
     */
    public static function addTermPageLinkToTooltip( $glossaryItemContent, $glossary_item ) {
        $addLink = get_option( 'cmtt_glossaryAddTermPagelink' );

        if ( $addLink == 1 ) {
            $text                = __( get_option( 'cmtt_glossaryTermDetailsLink' ), 'cm-tooltip-glossary' );
            $link                = '<a class=&quot;glossaryTooltipMoreLink&quot; href=&quot;' . get_permalink( $glossary_item ) . '&quot;>' . $text . '</a>';
            $glossaryPageLink    = '<div class=&quot;glossaryTooltipMoreLinkWrapper&quot;>' . $link . '</div>';
            /*
             * Add the link
             */
            $glossaryItemContent = $glossaryItemContent . $glossaryPageLink;
        }

        return $glossaryItemContent;
    }

    /**
     * Add the social share buttons
     * @param string $content
     * @return string
     */
    public static function cmtt_glossaryAddShareBox( $content = '' ) {
        if ( !defined( 'DOING_AJAX' ) ) {
            ob_start();
            require CMTT_PLUGIN_DIR . 'views/frontend/social_share.phtml';
            $preContent = ob_get_clean();

            $content = $preContent . $content;
        }

        return $content;
    }

    /**
     * Function responsible for saving the options
     */
    public static function saveOptions() {
        $messages = '';
        $_POST    = array_map( 'stripslashes_deep', $_POST );
        $post     = $_POST;

        if ( isset( $post[ "cmtt_glossarySave" ] ) || isset( $post[ 'cmtt_glossaryRelatedRefresh' ] ) || isset( $post[ 'cmtt_glossaryRelatedRefreshContinue' ] ) ) {
            $test = check_admin_referer( 'update-options' );

            do_action( 'cmtt_save_options_before', $post, array( &$messages ) );
            $enqueeFlushRules = false;
            /*
             * Update the page options
             */
            update_option( 'cmtt_glossaryID', $post[ "cmtt_glossaryID" ] );
            CMTT_Glossary_Index::tryGenerateGlossaryIndexPage();
            if ( $post[ "cmtt_glossaryPermalink" ] !== get_option( 'cmtt_glossaryPermalink' ) ) {
                /*
                 * Update glossary post permalink
                 */
                $glossaryPost     = array(
                    'ID'        => $post[ "cmtt_glossaryID" ],
                    'post_name' => $post[ "cmtt_glossaryPermalink" ]
                );
                wp_update_post( $glossaryPost );
                $enqueeFlushRules = true;
            }

            update_option( 'cmtt_glossaryPermalink', $post[ "cmtt_glossaryPermalink" ] );

            if ( apply_filters( 'cmtt_enqueueFlushRules', $enqueeFlushRules, $post ) ) {
                self::_flush_rewrite_rules();
            }

            unset( $post[ 'cmtt_glossaryID' ], $post[ 'cmtt_glossaryPermalink' ], $post[ 'cmtt_glossarySave' ] );

            function cmtt_get_the_option_names( $k ) {
                return strpos( $k, 'cmtt_' ) === 0;
            }

            $options_names = apply_filters( 'cmtt_thirdparty_option_names', array_filter( array_keys( $post ), 'cmtt_get_the_option_names' ) );

            foreach ( $options_names as $option_name ) {
                if ( !isset( $post[ $option_name ] ) ) {
                    update_option( $option_name, 0 );
                } else {
                    if ( $option_name == 'cmtt_index_letters' ) {
                        $optionValue = explode( ',', $post[ $option_name ] );
                        $optionValue = array_map( 'mb_strtolower', $optionValue );
                    } else {
                        $optionValue = is_array( $post[ $option_name ] ) ? $post[ $option_name ] : trim( $post[ $option_name ] );
                    }
                    update_option( $option_name, self::sanitizeInput( $option_name, $optionValue ) );
                }
            }
            do_action( 'cmtt_save_options_after_on_save', $post, array( &$messages ) );
        }

        do_action( 'cmtt_save_options_after', $post, array( &$messages ) );

        if ( isset( $post[ 'cmtt_glossaryRelatedRefresh' ] ) ) {
            CMTT_Related::crawlArticles( TRUE );
            self::$messages = __( 'Related Articles Index rebuild has been started.', 'cm-tooltip-glossary' );
        }

        if ( isset( $post[ 'cmtt_glossaryRelatedRefreshContinue' ] ) ) {
            CMTT_Related::crawlArticles();
            self::$messages = __( 'Related Articles Index has been updated.', 'cm-tooltip-glossary' );
        }

        if ( isset( $post[ 'cmtt_removeAllOptions' ] ) ) {
            self::_cleanupOptions();
            self::$messages = 'CM Tooltip Glossary data options have been removed from the database.';
        }

        if ( isset( $post[ 'cmtt_removeAllItems' ] ) ) {
            self::_cleanupItems();
            self::$messages = 'CM Tooltip Glossary data terms have been removed from the database.';
        }

        return array( 'messages' => self::$messages );
    }

    /**
     * Sanitizes the inputs
     * @param type $input
     */
    public static function sanitizeInput( $optionName, $optionValue ) {
        if ( in_array( $optionName, array( 'cmtt_glossaryPermalink' ) ) ) {
            $sanitizedValue = sanitize_title( $optionValue );
        } else {
            if ( !is_array( $optionValue ) ) {
                $sanitizedValue = esc_attr( $optionValue );
            } else {
                $sanitizedValue = $optionValue;
            }
        }

        return $sanitizedValue;
    }

    /**
     * Displays the options screen
     */
    public static function outputOptions() {
        $result   = self::saveOptions();
        $messages = $result[ 'messages' ];

        ob_start();
        require('views/backend/admin_settings.php');
        $content = ob_get_contents();
        ob_end_clean();
        require('views/backend/admin_template.php');
    }

    public static function cmtt_quicktags() {
        global $post;
        ?>
        <script type="text/javascript">
            if ( typeof QTags !== "undefined" )
            {
                QTags.addButton( 'cmtt_parse', 'Glossary Parse', '[glossary_parse]', '[/glossary_parse]' );
                QTags.addButton( 'cmtt_exclude', 'Glossary Exclude', '[glossary_exclude]', '[/glossary_exclude]' );
                QTags.addButton( 'cmtt_translate', 'Glossary Translate', '[glossary_translate term=""]' );
                QTags.addButton( 'cmtt_dictionary', 'Glossary Dictionary', '[glossary_dictionary term=""]' );
                QTags.addButton( 'cmtt_thesaurus', 'Glossary Thesaurus', '[glossary_thesaurus term=""]' );
            }
        </script>
        <?php
    }

    public static function _cmtt_prepareExportGlossary() {
        $args = array(
            'post_type'   => 'glossary',
            'post_status' => 'publish',
            'nopaging'    => true,
            'orderby'     => 'ID',
            'order'       => 'ASC'
        );

        $q                         = new WP_Query( $args );
        $exportData                = array();
        $exportHeaderRow           = array(
            'Id',
            'Title',
            'Excerpt',
            'Description',
            'Synonyms',
            'Variations',
        );
        $exportHeaderRowWithMeta   = apply_filters( 'cmtt_export_header_row', $exportHeaderRow );
        $exportHeaderRowWithMeta[] = 'Meta';
        $exportData[]              = $exportHeaderRowWithMeta;

        /*
         * Get all the glossary items
         */
        foreach ( $q->get_posts() as $term ) {
            /*
             * All the meta information
             */
            $meta = get_post_meta( $term->ID, '', true );
            foreach ( $meta as $key => $value ) {
                $meta[ $key ] = is_array( $value ) ? $value[ 0 ] : $value;
            }
            $jsonEncodedMeta = json_encode( $meta );

            $exportDataRow           = array(
                $term->ID,
                $term->post_title,
                str_replace( array( "\r\n", "\n", "\r" ), array( "", "", "" ), nl2br( $term->post_excerpt ) ),
                str_replace( array( "\r\n", "\n", "\r" ), array( "", "", "" ), nl2br( $term->post_content ) ),
                CMTT_Synonyms::getSynonyms( $term->ID, true ),
                CMTT_Synonyms::getSynonyms( $term->ID, false ),
            );
            $exportDataRowWithMeta   = apply_filters( 'cmtt_export_data_row', $exportDataRow, $term );
            $exportDataRowWithMeta[] = $jsonEncodedMeta;
            $exportData[]            = $exportDataRowWithMeta;
        }

        return $exportData;
    }

    /**
     * Outputs the backup file
     */
    public static function cmtt_glossary_get_backup() {
        $pinOption = get_option( 'cmtt_glossary_backup_pinprotect', false );

        if ( !empty( $pinOption ) ) {
            $passedPin = filter_input( INPUT_GET, 'pin' );
            if ( $passedPin != $pinOption ) {
                echo 'Incorrect PIN!';
                die();
            }
        }

        $backupGlossary = self::_cmtt_getBackupGlossary( false );

        if ( $backupGlossary ) {
            $upload_dir = wp_upload_dir();
            $filepath   = trailingslashit( $upload_dir[ 'basedir' ] ) . 'cmtt/' . CMTT_BACKUP_FILENAME;

            $outstream = fopen( $filepath, 'r' );
            rewind( $outstream );

            header( 'Content-Encoding: UTF-8' );
            header( 'Content-Type: text/csv; charset=UTF-8' );
            header( 'Content-Disposition: attachment; filename=glossary_backup_' . date( 'Ymd_His', current_time( 'timestamp' ) ) . '.csv' );
            /*
             * Why including the BOM? - Marcin
             */
            echo "\xEF\xBB\xBF"; // UTF-8 BOM
            while ( !feof( $outstream ) ) {
                echo fgets( $outstream );
            }
            fclose( $outstream );
        }
        die();
    }

    /**
     * Outputs the backup glossary AJAX link
     */
    public static function _cmtt_getBackupGlossary( $protect = true ) {
        $upload_dir = wp_upload_dir();
        $filepath   = trailingslashit( $upload_dir[ 'basedir' ] ) . 'cmtt/' . CMTT_BACKUP_FILENAME;

        if ( file_exists( $filepath ) ) {
            $url = admin_url( 'admin-ajax.php?action=cmtt_get_glossary_backup' );

            if ( !$protect ) {
                $pinOption = get_option( 'cmtt_glossary_backup_pinprotect' );
                $url .= $pinOption ? '&pin=' . $pinOption : '';
            }

            return $url;
        }

        return false;
    }

    /**
     * Backups the glossary
     */
    public static function _cmtt_backupGlossary() {
        if ( empty( $_POST ) ) {
            return false;
        }

        check_admin_referer( 'cmtt_do_backup' );

        if ( isset( $_POST[ 'cmtt_doBackup' ] ) ) {
            $url = wp_nonce_url( 'admin.php?page=cmtt_importexport' );
            self::_cmtt_doBackup( $url );
        }

        return false;
    }

    /**
     * Reschedule the backup event
     * @return type
     */
    public static function _cmtt_rescheduleBackup() {
        $possibleIntervals = array_keys( wp_get_schedules() );

        $newScheduleHour     = filter_input( INPUT_POST, 'cmtt_glossary_backupCronHour' );
        $newScheduleInterval = filter_input( INPUT_POST, 'cmtt_glossary_backupCronInterval' );

        if ( $newScheduleHour !== NULL && $newScheduleInterval !== NULL ) {
            wp_clear_scheduled_hook( 'cmtt_glossary_backup_event' );

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

            wp_schedule_event( $time, $newScheduleInterval, 'cmtt_glossary_backup_event' );
        }
    }

    public static function _cmtt_doBackup( $url = null ) {
        $form_fields = array( 'cmtt_doBackup' ); // this is a list of the form field contents I want passed along between page views
        $method      = ''; // Normally you leave this an empty string and it figures it out by itself, but you can override the filesystem method here
        // check to see if we are trying to save a file

        $secureWrite = get_option( 'cmtt_glossary_backup_secure', true );

        if ( $secureWrite ) {
            if ( empty( $url ) ) {
                $url = wp_nonce_url( 'admin.php?page=cmtt_importexport' );
            }

            /** WordPress Administration File API */
            require_once(ABSPATH . 'wp-admin/includes/file.php');

            // okay, let's see about getting credentials
            if ( false === ($creds = request_filesystem_credentials( $url, $method, false, false, $form_fields ) ) ) {
                // if we get here, then we don't have credentials yet,
                // but have just produced a form for the user to fill in,
                // so stop processing for now
                return true; // stop the normal page form from displaying
            }

            // now we have some credentials, try to get the wp_filesystem running
            if ( !WP_Filesystem( $creds ) ) {
                // our credentials were no good, ask the user for them again
                request_filesystem_credentials( $url, $method, true, false, $form_fields );
                return true;
            }
        }

        // get the upload directory
        $upload_dir = wp_upload_dir();
        $filename   = trailingslashit( $upload_dir[ 'basedir' ] ) . 'cmtt/';

        if ( !file_exists( $filename ) ) {
            wp_mkdir_p( $filename );
        }

        chmod( $filename, 0775 );
        $filename .= CMTT_BACKUP_FILENAME;

        $string    = '';
        $outstream = fopen( "php://temp", 'r+' );

        $exportData = self::_cmtt_prepareExportGlossary();
        foreach ( $exportData as $line ) {
            fputcsv( $outstream, $line, ',', '"' );
        }
        rewind( $outstream );
        while ( !feof( $outstream ) ) {
            $string .= fgets( $outstream );
        }
        fclose( $outstream );

        if ( $secureWrite ) {
            /*
             * by this point, the $wp_filesystem global should be working, so let's use it to create a file
             */
            global $wp_filesystem;
            if ( !$wp_filesystem->put_contents( $filename, $string, FS_CHMOD_FILE ) ) {
                echo "error saving file!";
            }
        } else {
            file_put_contents( $filename, $string, LOCK_EX );
            chmod( $filename, 0775 );
        }
    }

    /**
     * Exports the glossary
     */
    public static function _cmtt_exportGlossary() {
        $exportData = self::_cmtt_prepareExportGlossary();

        $outstream = fopen( "php://temp", 'r+' );

        foreach ( $exportData as $line ) {
            fputcsv( $outstream, $line, ',', '"' );
        }
        rewind( $outstream );

        header( 'Content-Encoding: UTF-8' );
        header( 'Content-Type: text/csv; charset=UTF-8' );
        header( 'Content-Disposition: attachment; filename=glossary_export_' . date( 'Ymd_His', current_time( 'timestamp' ) ) . '.csv' );
        /*
         * Why including the BOM? - Marcin
         */
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        while ( !feof( $outstream ) ) {
            echo fgets( $outstream );
        }
        fclose( $outstream );
        exit;
    }

    /**
     * Imports the single glossary item
     * @global type $wpdb
     * @param type $item
     * @param type $override
     * @return boolean
     */
    public static function importGlossaryItem( $item, $override = TRUE ) {
        if ( !empty( $item ) && is_array( $item ) && count( $item ) >= 4 && !empty( $item[ 1 ] ) && !empty( $item[ 3 ] ) ) {
            global $wpdb;
            $data       = array(
                'post_title'   => $item[ 1 ],
                'post_type'    => 'glossary',
                'post_excerpt' => $item[ 2 ],
                'post_content' => $item[ 3 ],
                'post_status'  => 'publish'
            );
            $sql        = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type=%s AND post_title=%s AND post_status='publish'", 'glossary', $item[ 1 ] );
            $existingId = $wpdb->get_var( $sql );

            if ( !empty( $existingId ) ) {
                //update
                $data[ 'ID' ] = $existingId;

                if ( $override ) {
                    $update = wp_update_post( $data );
                } else {
                    $update = FALSE;
                }
            } else {
                //insert new
                $update = wp_insert_post( $data );
            }

            if ( $update > 0 && isset( $item[ 4 ] ) && isset( $item[ 5 ] ) ) {
                CMTT_Synonyms::setSynonyms( $update, $item[ 4 ], true );
                CMTT_Synonyms::setSynonyms( $update, $item[ 5 ], false );
            }

            do_action( 'cmtt_import_glossary_item', $item, $update );

            return $update;
        }

        return false;
    }

    public static function _cmtt_importGlossary( $file ) {
        $filesrc = $file[ 'tmp_name' ];
        $fp      = fopen( $filesrc, 'r' );
        $tab     = array();
        while ( !feof( $fp ) ) {
            $item  = fgetcsv( $fp, 0, ',', '"' );
            $tab[] = $item;
        }
        $numberOfElements = 0;

        remove_action( 'save_post', array( 'CMTT_Related', 'triggerOnSave' ) );
        remove_action( 'save_post', array( 'CMTTW_Related', 'triggerOnSave' ) );

        for ( $i = 1; $i < count( $tab ); $i++ ) {
            $result = self::importGlossaryItem( $tab[ $i ] );
            if ( $result !== false ) {
                $numberOfElements++;
            }
        }
        wp_redirect( esc_url( add_query_arg( array( 'msg' => 'imported', 'itemsnumber' => $numberOfElements ), $_SERVER[ 'REQUEST_URI' ] ), 303 ) );
        exit;
    }

    /**
     * Add the prefix before the title on the Glossary Term page
     * @global type $wp_query
     * @param string $title
     * @param type $id
     * @return string
     */
    public static function cmtt_glossary_addTitlePrefix( $title = '', $id = null ) {
        global $wp_query;

        if ( $id ) {
            $glossaryItem = get_post( $id );
            if ( $glossaryItem && 'glossary' == $glossaryItem->post_type && $wp_query->is_single && isset( $wp_query->query[ 'post_type' ] ) && $wp_query->query[ 'post_type' ] == 'glossary' ) {
                $prefix = get_option( 'cmtt_glossaryBeforeTitle' );
                if ( !empty( $prefix ) ) {
                    $title = '<span class=cmtt-glossary-item-title-prefix>' . __( $prefix, 'cm-tooltip-glossary' ) . '</span>' . $title;
                }
            }
        }

        return $title;
    }

    /**
     * Add the backlink on the Glossary Term page
     * @global type $wp_query
     * @param type $content
     * @return type
     */
    public static function cmtt_glossary_addBacklink( $content = '' ) {
        global $wp_query;

        if ( !isset( $wp_query->post ) ) {
            return $content;
        }
        $post = $wp_query->post;
        $id   = $post->ID;

        $disableSynonymsForThisTerm = (bool) get_post_meta( $id, '_cmtt_disable_synonyms_for_term', true );

        $onMainQueryOnly = (get_option( 'cmtt_glossaryOnMainQuery' ) == 1 ) ? is_main_query() : TRUE;

        if ( is_single() && get_query_var( 'post_type' ) == 'glossary' && $onMainQueryOnly && 'glossary' == get_post_type() ) {
            $mainPageId     = CMTT_Glossary_Index::getGlossaryIndexPageId();
            $backlink       = (get_option( 'cmtt_glossary_addBackLink' ) == 1 && $mainPageId > 0) ? '<a href="' . get_permalink( $mainPageId ) . '" class="cmtt-backlink cmtt-backlink-top">' . __( get_option( 'cmtt_glossary_backLinkText' ), 'cm-tooltip-glossary' ) . '</a>' : '';
            $backlinkBottom = (get_option( 'cmtt_glossary_addBackLinkBottom' ) == 1 && $mainPageId > 0) ? '<a href="' . get_permalink( $mainPageId ) . '" class="cmtt-backlink cmtt-backlink-bottom">' . __( get_option( 'cmtt_glossary_backLinkBottomText' ), 'cm-tooltip-glossary' ) . '</a>' : '';

            $synonymSnippet = (get_option( 'cmtt_glossary_addSynonyms' ) == 1 && !$disableSynonymsForThisTerm) ? CMTT_Synonyms::renderSynonyms( $post->ID ) : '';
            $relatedSnippet = CMTT_Related::renderRelatedArticles( $post->ID, get_option( 'cmtt_glossary_showRelatedArticlesCount' ), get_option( 'cmtt_glossary_showRelatedArticlesGlossaryCount' ) );

            $referralSnippet = (get_option( 'cmtt_glossaryReferral' ) == 1 && get_option( 'cmtt_glossaryAffiliateCode' )) ? self::cmtt_getReferralSnippet() : '';

            $contentWithoutBacklink = $content . $synonymSnippet . $relatedSnippet;

            $filteredContent = apply_filters( 'cmtt_add_backlink_content', $contentWithoutBacklink, $post );

            /*
             * If the filteredContent is not empty - we add a second backlink
             */
            if ( !empty( $filteredContent ) ) {
                $filteredContent = $filteredContent . $backlinkBottom;
            }

            /*
             * In the end add the backlink at the beginning and the referral snippet at the end
             */
            $contentWithBacklink = $backlink . $filteredContent . $referralSnippet;

            $contentWithBacklink = apply_filters( 'cmtt_glossary_term_after_content', $contentWithBacklink );

            return $contentWithBacklink;
        }

        return $content;
    }

    /**
     * Outputs the Affiliate Referral Snippet
     * @return type
     */
    public static function cmtt_getReferralSnippet() {
        ob_start();
        ?>
        <span class="glossary_referral_link">
            <a target="_blank" href="https://www.cminds.com/store/tooltipglossary/?af=<?php echo get_option( 'cmtt_glossaryAffiliateCode' ) ?>">
                <img src="https://www.cminds.com/wp-content/uploads/download_tooltip.png" width=122 height=22 alt="Download Tooltip Pro" title="Download Tooltip Pro" />
            </a>
        </span>
        <?php
        $referralSnippet = ob_get_clean();
        return $referralSnippet;
    }

    /**
     * Attaches the hooks adding the custom buttons to TinyMCE and CKeditor
     * @return type
     */
    public static function addRicheditorButtons() {
        /*
         *  check user permissions
         */
        if ( !current_user_can( 'manage_glossary' ) && !current_user_can( 'edit_pages' ) ) {
            return;
        }

        // check if WYSIWYG is enabled
        if ( 'true' == get_user_option( 'rich_editing' ) && get_option( 'cmtt_add_richedit_buttons', 1 ) ) {
            add_filter( 'mce_external_plugins', array( self::$calledClassName, 'cmtt_mcePlugin' ) );
            add_filter( 'mce_buttons', array( self::$calledClassName, 'cmtt_mceButtons' ) );

            add_filter( 'ckeditor_external_plugins', array( self::$calledClassName, 'cmtt_ckeditorPlugin' ) );
            add_filter( 'ckeditor_buttons', array( self::$calledClassName, 'cmtt_ckeditorButtons' ) );
        }
    }

    public static function cmtt_mcePlugin( $plugins ) {
        $plugins                    = (array) $plugins;
        $plugins[ 'cmtt_glossary' ] = self::$jsPath . 'editor/glossary-mce.js';
        return $plugins;
    }

    public static function cmtt_mceButtons( $buttons ) {
        array_push( $buttons, '|', 'cmtt_exclude', 'cmtt_parse' );
        return $buttons;
    }

    public static function cmtt_ckeditorPlugin( $plugins ) {
        $plugins                    = (array) $plugins;
        $plugins[ 'cmtt_glossary' ] = self::$jsPath . '/editor/ckeditor/plugin.js';
        return $plugins;
    }

    public static function cmtt_ckeditorButtons( $buttons ) {
        array_push( $buttons, 'cmtt_exclude', 'cmtt_parse' );
        return $buttons;
    }

    public static function cmtt_warn_on_upgrade() {
        ?>
        <div style="margin-top: 1em"><span style="color: red; font-size: larger">STOP!</span> Do <em>not</em> click &quot;update automatically&quot; as you will be <em>downgraded</em> to the free version of Tooltip Glossary. Instead, download the Pro update directly from <a href="http://www.cminds.com/downloads/cm-enhanced-tooltip-glossary-premium-version/">http://www.cminds.com/downloads/cm-enhanced-tooltip-glossary-premium-version/</a>.</div>
        <div style="font-size: smaller">Tooltip Glossary Pro does not use WordPress's standard update mechanism. We apologize for the inconvenience!</div>
        <?php
    }

    /**
     * Registers the metaboxes
     */
    public static function cmtt_RegisterBoxes() {
        add_meta_box( 'glossary-exclude-box', 'CM Tooltip - Term Properties', array( self::$calledClassName, 'cmtt_render_my_meta_box' ), 'glossary', 'side', 'high' );

        $defaultPostTypes    = get_option( 'cmtt_disable_metabox_all_post_types' ) ? get_post_types() : array( 'glossary', 'post', 'page' );
        $disableBoxPostTypes = apply_filters( 'cmtt_disable_metabox_posttypes', $defaultPostTypes );
        if ( !empty( $disableBoxPostTypes ) ) {
            foreach ( $disableBoxPostTypes as $postType ) {
                add_meta_box( 'glossary-disable-box', 'CM Tooltip - Disables', array( self::$calledClassName, 'cmtt_render_disable_for_page' ), $postType, 'side', 'high' );
            }
        }

        do_action( 'cmtt_register_boxes' );
    }

    public static function cmtt_render_disable_for_page( $post ) {
        $dTTpage               = get_post_meta( $post->ID, '_glossary_disable_tooltip_for_page', true );
        $disableTooltipForPage = (int) (!empty( $dTTpage ) && $dTTpage == 1 );

        $dLpage             = get_post_meta( $post->ID, '_glossary_disable_links_for_page', true );
        $disableLinkForPage = (int) (!empty( $dLpage ) && $dLpage == 1 );

        $dpage                 = get_post_meta( $post->ID, '_glossary_disable_for_page', true );
        $disableParsingForPage = (int) (!empty( $dpage ) && $dpage == 1 );

        $highlightFirst        = get_post_meta( $post->ID, '_cmtt_highlightFirstOnly', true );
        $highlightFirstForPage = (int) (!empty( $highlightFirst ) && $highlightFirst == 1 );

        $disableACF        = get_post_meta( $post->ID, '_cmtt_disable_acf_for_page', true );
        $disableACFforPage = (int) (!empty( $disableACF ) && $disableACF == 1 );

        echo '<div class="cmtt_disable_tooltip_for_page_field">';
        echo '<label for="glossary_disable_tooltip_for_page" class="blocklabel">';
        echo '<input type="checkbox" name="glossary_disable_tooltip_for_page" id="glossary_disable_tooltip_for_page" value="1" ' . checked( 1, $disableTooltipForPage, false ) . '>';
        echo '&nbsp;&nbsp;&nbsp;Don\'t show the Tooltips on this post/page</label>';
        echo '</div>';

        echo '<div class="cmtt_disable_link_for_page_field">';
        echo '<label for="glossary_disable_link_for_page" class="blocklabel">';
        echo '<input type="checkbox" name="glossary_disable_link_for_page" id="glossary_disable_link_for_page" value="1" ' . checked( 1, $disableLinkForPage, false ) . '>';
        echo '&nbsp;&nbsp;&nbsp;Don\'t show links to glossary terms on this post/page</label>';
        echo '</div>';

        echo '<div class="cmtt_disable_for_page_field">';
        echo '<label for="glossary_disable_for_page" class="blocklabel">';
        echo '<input type="checkbox" name="glossary_disable_for_page" id="glossary_disable_for_page" value="1" ' . checked( 1, $disableParsingForPage, false ) . '>';
        echo '&nbsp;&nbsp;&nbsp;Don\'t search for glossary items on this post/page</label>';
        echo '</div>';

        echo '<div class="cmtt_disable_for_page_field">';
        echo '<label for="cmtt_highlightFirstOnly" class="blocklabel">';
        echo '<input type="checkbox" name="cmtt_highlightFirstOnly" id="cmtt_highlightFirstOnly" value="1" ' . checked( 1, $highlightFirstForPage, false ) . '>';
        echo '&nbsp;&nbsp;&nbsp;Overwrite the "Hightlight Only First Occurence" setting on this page.</label>';
        echo '</div>';

        echo '<div class="cmtt_disable_for_page_field">';
        echo '<label for="cmtt_disable_acf_for_page" class="blocklabel">';
        echo '<input type="checkbox" name="cmtt_disable_acf_for_page" id="cmtt_disable_acf_for_page" value="1" ' . checked( 1, $disableACFforPage, false ) . '>';
        echo '&nbsp;&nbsp;&nbsp;Don\'t search for glossary items in ACF fields on this page.</label>';
        echo '</div>';

        do_action( 'cmtt_add_disables_metabox', $post );
    }

    public static function cmtt_glossary_meta_box_fields() {
        $metaBoxFields = apply_filters( 'cmtt_add_properties_metabox', array() );
        return $metaBoxFields;
    }

    public static function cmtt_render_my_meta_box( $post ) {
        $result = array();

        foreach ( self::cmtt_glossary_meta_box_fields() as $key => $value ) {
            $optionContent = '<div><label for="' . $key . '" class="blocklabel">';
            $fieldValue    = get_post_meta( $post->ID, '_' . $key, true );

            if ( is_string( $value ) ) {
                $optionContent .= '<input type="checkbox" name="' . $key . '" id="' . $key . '" value="1" ' . ((bool) $fieldValue ? ' checked ' : '') . '>';
                $optionContent .= '&nbsp;&nbsp;&nbsp;' . $value . '</label></div>';
            } elseif ( is_array( $value ) ) {
                $label   = isset( $value[ 'label' ] ) ? $value[ 'label' ] : __( 'No label', 'cm-tooltip-glossary' );
                $options = isset( $value[ 'options' ] ) ? $value[ 'options' ] : array( '' => __( '-no options-', 'cm-tooltip-glossary' ) );
                $optionContent .= '<select name="' . $key . '" id="' . $key . '">';
                foreach ( $options as $optionKey => $optionLabel ) {
                    $optionContent .= '<option value="' . $optionKey . '" ' . selected( $optionKey, $fieldValue, false ) . '>' . $optionLabel . '</option>';
                }

                $optionContent .= '</select>';
                $optionContent .= '&nbsp;&nbsp;&nbsp;' . $label . '</label></div>';
            }

            $result[] = $optionContent;
        }

        $result = apply_filters( 'cmtt_edit_properties_metabox_array', $result );

        echo implode( '', $result );
    }

    public static function cmtt_unset_transients( $post_id ) {
        $postType = get_post_type( $post_id );
        if ( 'glossary' != $postType ) {
            return;
        }

        /*
         * Invalidate transients
         */
        $contentHashKey = 'cmtt_content_hash_for_' . $post_id;
        $contentHash    = get_transient( $contentHashKey );
        delete_transient( $contentHashKey );
        if ( !empty( $contentHash ) ) {
            delete_transient( $contentHash );
        }

        $allArgsKey  = 'cmtt_all_args_keys';
        $allArgsKeys = get_transient( $allArgsKey );
        if ( is_array( $allArgsKeys ) ) {
            foreach ( $allArgsKeys as $argsKey ) {
                if ( is_string( $argsKey ) ) {
                    delete_transient( $argsKey );
                }
            }
            if ( !empty( $allArgsKey ) ) {
                delete_transient( $allArgsKeys );
            }
        }
    }

    public static function cmtt_save_postdata( $post_id ) {
        $post     = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
        $postType = isset( $post[ 'post_type' ] ) ? $post[ 'post_type' ] : '';

        do_action( 'cmtt_on_glossary_item_save_before', $post_id, $post );

        $disableBoxPostTypes = apply_filters( 'cmtt_disable_metabox_posttypes', array( 'glossary', 'post', 'page' ) );
        if ( in_array( $postType, $disableBoxPostTypes ) ) {
            /*
             * Disables the parsing of the given page
             */
            $disableParsingForPage = 0;
            if ( isset( $post[ "glossary_disable_for_page" ] ) && $post[ "glossary_disable_for_page" ] == 1 ) {
                $disableParsingForPage = 1;
            }
            update_post_meta( $post_id, '_glossary_disable_for_page', $disableParsingForPage );

            /*
             * Disables the showing of tooltip on given page
             */
            $disableTooltipForPage = 0;
            if ( isset( $post[ "glossary_disable_tooltip_for_page" ] ) && $post[ "glossary_disable_tooltip_for_page" ] == 1 ) {
                $disableTooltipForPage = 1;
            }
            update_post_meta( $post_id, '_glossary_disable_tooltip_for_page', $disableTooltipForPage );

            /*
             * Disables the showing of links to tooltip pages on given page
             */
            $disableLinksForPage = 0;
            if ( isset( $post[ "glossary_disable_link_for_page" ] ) && $post[ "glossary_disable_link_for_page" ] == 1 ) {
                $disableLinksForPage = 1;
            }
            update_post_meta( $post_id, '_glossary_disable_links_for_page', $disableLinksForPage );

            /*
             * Overwrite the hightlight first only setting for page
             */
            $highlightFirstOnly = 0;
            if ( isset( $post[ "cmtt_highlightFirstOnly" ] ) && $post[ "cmtt_highlightFirstOnly" ] == 1 ) {
                $highlightFirstOnly = 1;
            }
            update_post_meta( $post_id, '_cmtt_highlightFirstOnly', $highlightFirstOnly );

            /*
             * Overwrite the hightlight first only setting for page
             */
            $disableACFForPage = 0;
            if ( isset( $post[ "cmtt_disable_acf_for_page" ] ) && $post[ "cmtt_disable_acf_for_page" ] == 1 ) {
                $disableACFForPage = 1;
            }
            update_post_meta( $post_id, '_cmtt_disable_acf_for_page', $disableACFForPage );
        }

        if ( 'glossary' != $postType ) {
            return;
        }

        do_action( 'cmtt_on_glossary_item_save', $post_id, $post );

        /*
         * Part for "glossary" items only starts here
         */
        foreach ( array_keys( self::cmtt_glossary_meta_box_fields() ) as $value ) {
            $exclude_value = (isset( $post[ $value ] )) ? $post[ $value ] : 0;
            update_post_meta( $post_id, '_' . $value, $exclude_value );
        }
    }

    /**
     * Allows to choose which parser should be used
     * @param type $html
     * @param type $glossarySearchString
     * @return type
     */
    public static function cmtt_str_replace( $html, $glossarySearchString ) {
        $filter                         = current_filter();
        $parseWithSimpleFunctionFilters = apply_filters( 'cmtt_parse_with_simple_function', array() );

        $runThroughSimpleFunction = in_array( $filter, $parseWithSimpleFunctionFilters );

        if ( $runThroughSimpleFunction ) {
            return self::cmtt_simple_str_replace( $html, $glossarySearchString );
        } else {
            return self::cmtt_dom_str_replace( $html, $glossarySearchString );
        }
    }

    /**
     * Setups the filters which should use the simple parsing instead of DOM parser
     * @param type $html
     * @param type $glossarySearchString
     * @return type
     */
    public static function allowSimpleParsing( $simpleParsingList ) {
        if ( get_option( 'cmtt_disableDOMParserForACF', FALSE ) ) {
            $simpleParsingList[] = 'acf/load_value';
        }
        return $simpleParsingList;
    }

    public static function outputGlossaryExcludeWrap( $output = '' ) {
        return '[glossary_exclude]' . $output . '[/glossary_exclude]';
    }

    public static function outputGlossaryExcludeStart() {
        echo '[glossary_exclude]';
    }

    public static function outputGlossaryExcludeEnd() {
        echo '[/glossary_exclude]';
    }

    public static function removeGlossaryExclude( $content ) {
        $content = str_replace( array( '[glossary_exclude]', '[/glossary_exclude]' ), array( '', '' ), $content );
        return $content;
    }

    /**
     * New function to search the terms in the content
     *
     * @param strin $html
     * @param string $glossarySearchString
     * @since 2.3.1
     * @return type
     */
    public static function cmtt_dom_str_replace( $html, $glossarySearchString ) {
        global $cmWrapItUp;

        if ( !empty( $html ) && is_string( $html ) ) {
            if ( $cmWrapItUp ) {
                $html = '<span>' . $html . '</span>';
            }
            $dom = new DOMDocument();
            /*
             * loadXml needs properly formatted documents, so it's better to use loadHtml, but it needs a hack to properly handle UTF-8 encoding
             */
            libxml_use_internal_errors( true );
            if ( !$dom->loadHtml( mb_convert_encoding( $html, 'HTML-ENTITIES', "UTF-8" ) ) ) {
                libxml_clear_errors();
            }
            $xpath = new DOMXPath( $dom );

            /*
             * Base query NEVER parse in scripts
             */
            $query = '//text()[not(ancestor::script)][not(ancestor::style)]';
            if ( get_option( 'cmtt_glossaryProtectedTags' ) == 1 ) {
                $query .= '[not(ancestor::header)][not(ancestor::a)][not(ancestor::pre)][not(ancestor::object)][not(ancestor::h1)][not(ancestor::h2)][not(ancestor::h3)][not(ancestor::h4)][not(ancestor::h5)][not(ancestor::h6)][not(ancestor::textarea)]';
            }
            /*
             * Parsing of the Glossary Index Page
             */
            if ( get_option( 'cmtt_glossary_index_dont_parse', 1 ) == 1 ) {
                $query .= '[not(ancestor::div[@class=\'cm-tooltip\'])]';
            }
            /*
             * Parsing of the already-parsed items
             */
            $query .= '[not(ancestor::span[contains(concat(\' \', @class, \' \'), \' glossaryLink \')])]';

            /*
             * Parsing of the already-parsed items
             */
            $query .= '[not(ancestor::a[contains(concat(\' \', @class, \' \'), \' glossaryLink \')])]';

            /*
             * Parsing of the wistia videos
             */
            $query .= '[not(ancestor::div[contains(concat(\' \', @class, \' \'), \' avia_codeblock \')])]';

            /*
             * Parsing of the wp-captions
             */
            $query .= '[not(ancestor::*[contains(concat(\' \', @class, \' \'), \' wp-caption \')])]';

            /*
             * Parsing of the layerslider
             */
            $query .= '[not(ancestor::*[contains(concat(\' \', @class, \' \'), \' ls-wp-container \')])]';

            foreach ( $xpath->query( apply_filters( 'cmtt_glossary_xpath_query', $query ) ) as $node ) {
                /* @var $node DOMText */
                $replaced = preg_replace_callback( $glossarySearchString, array( self::$calledClassName, 'cmtt_replace_matches' ), htmlspecialchars( $node->wholeText, ENT_COMPAT ) );
                if ( !empty( $replaced ) ) {
                    $newNode            = $dom->createDocumentFragment();
                    $replacedShortcodes = strip_shortcodes( $replaced );
                    $result             = $newNode->appendXML( '<![CDATA[' . $replacedShortcodes . ']]>' );

                    if ( $result !== false ) {
                        $node->parentNode->replaceChild( $newNode, $node );
                    }
                }
            }

            do_action( 'cmtt_xpath_main_query_after', $xpath, $glossarySearchString, $dom );

            /*
             *  get only the body tag with its contents, then trim the body tag itself to get only the original content
             */
            $bodyNode = $xpath->query( '//body' )->item( 0 );

            if ( $bodyNode !== NULL ) {
                $newDom = new DOMDocument();
                $newDom->appendChild( $newDom->importNode( $bodyNode, TRUE ) );

                $intermalHtml = $newDom->saveHTML();
                $html         = mb_substr( trim( $intermalHtml ), 6, (mb_strlen( $intermalHtml ) - 14 ), "UTF-8" );
                /*
                 * Fixing the self-closing which is lost due to a bug in DOMDocument->saveHtml() (caused a conflict with NextGen)
                 */
                $html         = preg_replace( '#(<img[^>]*[^/])>#Ui', '$1/>', $html );
            }
        }

        if ( $cmWrapItUp ) {
            $html = mb_substr( trim( $html ), 6, (mb_strlen( $html ) - 13 ), "UTF-8" );
        }

        return $html;
    }

    /**
     * Simple function to search the terms in the content
     *
     * @param strin $html
     * @param string $glossarySearchString
     * @since 2.3.1
     * @return type
     */
    public static function cmtt_simple_str_replace( $html, $glossarySearchString ) {
        if ( !empty( $html ) && is_string( $html ) ) {
            $replaced = preg_replace_callback( $glossarySearchString, array( self::$calledClassName, 'cmtt_replace_matches' ), $html );
            $html     = $replaced;
        }

        return $html;
    }

    /**
     * BuddyPress record custom post type comments
     * @param array $post_types
     * @return string
     */
    public static function cmtt_bp_turn_off_parsing( $content ) {
        if ( !get_option( 'cmtt_glossaryParseBuddyPressPages', 1 ) ) {
            remove_filter( 'the_content', array( self::$calledClassName, 'cmtt_glossary_parse' ), 20000 );
        }
        return $content;
    }

    /**
     * BuddyPress record custom post type comments
     * @param array $post_types
     * @return string
     */
    public static function cmtt_bp_record_my_custom_post_type_comments( $post_types ) {
        $post_types[] = 'glossary';
        return $post_types;
    }

    /**
     * Adds the support for the custom tooltips
     * [glossary_tooltip content="text" dashicon="dashicons-editor-help" color="#c0c0c0" size="16px"]term[/glossary_tooltip]
     */
    public static function cmtt_custom_tooltip_shortcode( $atts, $text = '' ) {
        $content  = __( 'Use the &quot;content&quot; attribute on the shortcode to change this text', 'cm-tooltip-glossary' );
        $dashicon = '';
        extract( shortcode_atts( array( 'content' => $content, 'dashicon' => '', 'size' => '', 'color' => '' ), $atts ) );

        if ( !empty( $atts[ 'dashicon' ] ) ) {
            $style = '';
            if ( !empty( $atts[ 'size' ] ) ) {
                $style .= 'font-size:' . esc_attr( $atts[ 'size' ] ) . ';';
            }
            if ( !empty( $atts[ 'color' ] ) ) {
                $style .= 'color:' . esc_attr( $atts[ 'color' ] ) . ';';
            }
            $dashicon = '<span class="dashicons ' . $atts[ 'dashicon' ] . '" style="' . $style . 'display:inline;"></span>';
        }

        $wrappedContent = '<div class=glossaryItemBody>' . $content . '</div>';

        $tooltip                                 = '<span data-cmtooltip="' . $wrappedContent . '" class="glossaryLink">' . $dashicon . $text . '</span>';
        CMTT_Glossary_Index::$shortcodeDisplayed = 1;
        return $tooltip;
    }

    public static function outputLabelsSettings() {
        $view    = CMTT_PLUGIN_DIR . '/views/backend/settings_labels.phtml';
        ob_start();
        include $view;
        $content = ob_get_clean();
        return $content;
    }

    /**
     * Function renders (default) or returns the setttings tabs
     *
     * @param type $return
     * @return string
     */
    public static function renderSettingsTabs( $return = false ) {
        $content               = '';
        $settingsTabsArrayBase = array( '50' => 'Labels' );

        $settingsTabsArray = apply_filters( 'cmtt-settings-tabs-array', $settingsTabsArrayBase );

        if ( $settingsTabsArray ) {
            foreach ( $settingsTabsArray as $tabKey => $tabLabel ) {
                $filterName = 'cmtt-custom-settings-tab-content-' . $tabKey;

                $content .= '<div id="tabs-' . $tabKey . '">';
                $tabContent = apply_filters( $filterName, '' );
                $content .= $tabContent;
                $content .= '</div>';
            }
        }

        if ( $return ) {
            return $content;
        }
        echo $content;
    }

    /**
     * Function renders (default) or returns the setttings tabs
     *
     * @param type $return
     * @return string
     */
    public static function renderSettingsTabsControls( $return = false ) {
        $content               = '';
        $settingsTabsArrayBase = array(
            '1'  => 'General Settings',
            '2'  => 'Glossary Index Page',
            '3'  => 'Glossary Term',
            '4'  => 'Tooltip',
            '50' => 'Labels',
        );

        $settingsTabsArray = apply_filters( 'cmtt-settings-tabs-array', $settingsTabsArrayBase );

        ksort( $settingsTabsArray );

        if ( $settingsTabsArray ) {
            $content .= '<ul>';
            foreach ( $settingsTabsArray as $tabKey => $tabLabel ) {
                $content .= '<li><a href="#tabs-' . $tabKey . '">' . $tabLabel . '</a></li>';
            }
            $content .= '</ul>';
        }

        if ( $return ) {
            return $content;
        }
        echo $content;
    }

    /**
     * Returns the list of sorted glossary items
     * @staticvar array $glossary_index_full_sorted
     * @param type $args
     * @return type
     */
    public static function getGlossaryItemsSorted( $args = array() ) {
        $glossary_index_full_sorted = array();

        if ( $glossary_index_full_sorted === array() ) {
            $glossary_index             = self::getGlossaryItems( $args );
            $glossary_index_full_sorted = $glossary_index;
            uasort( $glossary_index_full_sorted, array( self::$calledClassName, '_sortByWPQueryObjectTitleLength' ) );
        }

        return apply_filters( 'cmtt_glossary_index_sorted', $glossary_index_full_sorted, $args );
    }

    /**
     * Returns the cachable array of all Glossary Terms, either sorted by title, or by title length
     *
     * @staticvar array $glossary_index
     * @staticvar array $glossary_index_sorted
     * @param type $args
     * @return type
     */
    public static function getGlossaryItems( $args = array() ) {
        global $wpdb;
        static $cache_clearing_loop_break = false;

        $glossary_index = array();

        $encodedArgs = json_encode( $args );
        $argsKey     = 'cmtt_' . md5( 'args' . $encodedArgs );
        $allArgsKey  = 'cmtt_all_args_keys';

        if ( !get_option( 'cmtt_glossaryEnableCaching', FALSE ) ) {
            delete_transient( $argsKey );
        }
        $glossaryItems = get_transient( $argsKey );
        if ( false === $glossaryItems || !isset( $glossaryItems[ 'args' ] ) || !isset( $glossaryItems[ 'args' ][ 'fields' ] ) ) {
            $defaultArgs = array(
                'post_type'              => 'glossary',
                'post_status'            => 'publish',
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'suppress_filters'       => false,
                'fields'                 => 'ids',
            );

            $queryArgs = array_merge( $defaultArgs, $args );

            $nopaging_args                  = $queryArgs;
            $nopaging_args[ 'nopaging' ]    = true;
            $nopaging_args[ 'numberposts' ] = -1;

            if ( $args === array() ) {
                $queryArgs = $nopaging_args;
            }

            $glossaryItems[ 'args' ]          = $queryArgs;
            $glossaryItems[ 'nopaging_args' ] = $nopaging_args;
            if ( $queryCacheKey                    = apply_filters( 'cmtt_glossary_items_query_cache_key', '' ) ) {
                $glossaryItems[ 'query' ] = wp_cache_get( $queryCacheKey );
                if ( false === $glossaryItems[ 'query' ] ) {
                    $glossaryItems[ 'query' ] = new WP_Query( $glossaryItems[ 'args' ] );
                    wp_cache_set( $queryCacheKey, $glossaryItems[ 'query' ], '', 3600 );
                }
            } else {
                $glossaryItems[ 'query' ] = new WP_Query( $glossaryItems[ 'args' ] );
            }

            if ( get_option( 'cmtt_glossaryEnableCaching', FALSE ) ) {
                $allArgsKeys = get_transient( $allArgsKey );
                if ( !is_array( $allArgsKeys ) ) {
                    $allArgsKeys = array();
                }
                set_transient( $argsKey, $glossaryItems, 5 * MINUTE_IN_SECONDS );

                $allArgsKeys[] = $argsKey;
                set_transient( $allArgsKey, $allArgsKeys, 60 * MINUTE_IN_SECONDS );
            }
        }

        /*
         * We need to be 100% sure that the query returns just the ids
         */
        if ( !is_object( $glossaryItems[ 'query' ] ) || !isset( $glossaryItems[ 'args' ][ 'fields' ] ) || 'ids' !== $glossaryItems[ 'args' ][ 'fields' ] ) {
            $glossaryItems[ 'args' ][ 'fields' ] = 'ids';
            $glossaryItems[ 'query' ]            = new WP_Query( $glossaryItems[ 'args' ] );
        }
        if ( $indexIdsCacheKey = apply_filters( 'cmtt_index_ids_cache_key', '' ) ) {
            $glossaryIndexIds = wp_cache_get( $indexIdsCacheKey );
            if ( false === $glossaryIndexIds ) {
                $glossaryIndexIds = $glossaryItems[ 'query' ]->get_posts();
                wp_cache_set( $indexIdsCacheKey, $glossaryIndexIds, '', 3600 );
            }
        } else {
            $glossaryIndexIds = $glossaryItems[ 'query' ]->get_posts();
        }

        /*
         * Check the result - should contain only ids since 3.3.8
         */
        if ( !$cache_clearing_loop_break && !empty( $glossaryIndexIds ) && is_array( $glossaryIndexIds ) ) {
            /*
             * Check the first item in the table - if it's object (WP_Post) then we need to clear the cache
             */
            $testGlossaryTerm = reset( $glossaryIndexIds );
            if ( is_object( $testGlossaryTerm ) && !empty( $testGlossaryTerm->ID ) ) {
                $cache_clearing_loop_break = true;
                self::cmtt_unset_transients( $testGlossaryTerm->ID );
                return self::getGlossaryItems( $args );
            }
        }

        $glossaryIndexIdsWhere = !empty( $glossaryIndexIds ) ? implode( ',', $glossaryIndexIds ) : -1;
        $sql                   = $wpdb->prepare( 'SELECT ID,post_title,post_name,post_content,post_excerpt,post_date,post_type FROM ' . $wpdb->posts . ' WHERE ID IN (' . $glossaryIndexIdsWhere . ') LIMIT %d', '999999' );
        if ( $glossaryIndexCacheKey = apply_filters( 'cmtt_glossary_index_cache_key', '' ) ) {
            $glossaryIndex = wp_cache_get( $glossaryIndexCacheKey );
            if ( false === $glossaryIndex ) {
                $glossaryIndex = $wpdb->get_results( $sql, ARRAY_A );
                wp_cache_set( $glossaryIndexCacheKey, $glossaryIndex, '', 3600 );
            }
        } else {
            $glossaryIndex = $wpdb->get_results( $sql, ARRAY_A );
        }

        foreach ( $glossaryIndex as $key => $post ) {
            $obj = (object) $post;
//			wp_cache_add( $obj->ID, $obj, 'posts' );

            $newObj           = apply_filters( 'cmtt_get_all_glossary_items_single', $obj, $post );
            $glossary_index[] = $newObj;
            unset( $glossaryIndex[ $key ] );
        }

        /*
         * Save statically
         */
        self::$lastQueryDetails = $glossaryItems;

        return $glossary_index;
    }

    public static function outputCustomPostTypesList() {
        $content = '';
        $args    = array(
            'public' => true,
//            '_builtin' => false
        );

        $output   = 'objects'; // names or objects, note names is the default
        $operator = 'and'; // 'and' or 'or'

        $post_types          = get_post_types( $args, $output, $operator );
        $selected_post_types = get_option( 'cmtt_glossaryOnPosttypes' );

        if ( !is_array( $selected_post_types ) ) {
            $selected_post_types = array();
        }

        foreach ( $post_types as $post_type ) {
            $label = $post_type->labels->singular_name . ' (' . $post_type->name . ')';
            $name  = $post_type->name;

            $content .= '<div><label><input type="checkbox" name="cmtt_glossaryOnPosttypes[]" ' . checked( true, in_array( $name, $selected_post_types ), false ) . ' value="' . $name . '" />' . $label . '</label></div>';
        }
        return $content;
    }

    public static function outputRolesList() {
        $content = '';

        $roles          = get_editable_roles();
        $selected_roles = get_option( 'cmtt_glossaryRoles', array( 'administrator', 'editor' ) );

        if ( !is_array( $selected_roles ) ) {
            $selected_roles = array();
        }

        foreach ( $roles as $role => $role_info ) {
            $label = $role . ' (' . $role_info[ 'name' ] . ')';
            $name  = $role;

            $content .= '<div><label><input type="checkbox" name="cmtt_glossaryRoles[]" ' . checked( true, in_array( $name, $selected_roles ), false ) . ' value="' . $name . '" />' . $label . '</label></div>';
        }
        return $content;
    }

    public static function outputACFTypesList( $fieldName = 'cmtt_acf_parsed_field_types',
                                               $defaultSelected = array( 'text', 'wysiwyg' ) ) {
        $content = '';

        $options  = array( 'text' => 'Text', 'textarea' => 'Textarea', 'wysiwyg' => 'WYSIWYG', 'message' => 'Message' );
        $selected = get_option( $fieldName, $defaultSelected );

        if ( !is_array( $selected ) ) {
            $selected = array();
        }

        foreach ( $options as $option => $optionInfo ) {
            $label = $optionInfo;
            $name  = $option;

            $content .= '<div><label><input type="checkbox" name="' . $fieldName . '[]" ' . checked( true, in_array( $name, $selected ), false ) . ' value="' . $name . '" />' . $label . '</label></div>';
        }
        return $content;
    }

    public static function flushCaps( $post, $messages ) {
        $oldRoles = get_option( 'cmtt_glossaryRoles', array( 'administrator', 'editor' ) );
        $newRoles = $post[ 'cmtt_glossaryRoles' ];
        if ( $oldRoles != $newRoles ) {
            self::_add_caps( $newRoles );
            self::$messages = __( 'New Role assignment has been saved!', 'cm-tooltip-glossary' );
        }
    }

    /*
     *  Sort longer titles first, so if there is collision between terms
     * (e.g., "essential fatty acid" and "fatty acid") the longer one gets created first.
     */

    public static function _sortByWPQueryObjectTitleLength( $a, $b ) {
        $sortVal = 0;
        if ( property_exists( $a, 'post_title' ) && property_exists( $b, 'post_title' ) ) {
            $sortVal = strlen( $b->post_title ) - strlen( $a->post_title );
        }
        return $sortVal;
    }

    /**
     * Function cleans up the plugin, removing the terms, resetting the options etc.
     *
     * @return string
     */
    protected static function _cleanupOptions( $force = true ) {
        /*
         * Remove the data from the other tables
         */
        do_action( 'cmtt_do_cleanup' );

        $glossaryIndexPageId = CMTT_Glossary_Index::getGlossaryIndexPageId();
        if ( !empty( $glossaryIndexPageId ) ) {
            wp_delete_post( $glossaryIndexPageId );
        }

        /*
         * Remove the options
         */
        $optionNames = wp_load_alloptions();

        function cmtt_get_the_option_names( $k ) {
            return strpos( $k, 'cmtt_' ) === 0;
        }

        $options_names = array_filter( array_keys( $optionNames ), 'cmtt_get_the_option_names' );
        foreach ( $options_names as $optionName ) {
            delete_option( $optionName );
        }
    }

    /**
     * Function cleans up the plugin, removing the terms, resetting the options etc.
     *
     * @return string
     */
    protected static function _cleanupItems( $force = true ) {

        do_action( 'cmtt_do_cleanup_items_before' );

        $glossary_index = self::getGlossaryItems();

        /*
         * Remove the glossary terms
         */
        foreach ( $glossary_index as $post ) {
            wp_delete_post( $post->ID, $force );
        }

        /*
         * Invalidate the list of all glossary items stored in cache
         */
        do_action( 'cmtt_do_cleanup_items_after' );
    }

    /**
     * Plugin activation
     */
    protected static function _activate() {
        CMTT_Glossary_Index::tryGenerateGlossaryIndexPage();
        do_action( 'cmtt_do_activate' );
    }

    /**
     * Plugin installation
     *
     * @global type $wpdb
     * @param type $networkwide
     * @return type
     */
    public static function _install( $networkwide ) {
        global $wpdb;

        if ( function_exists( 'is_multisite' ) && is_multisite() ) {
            // check if it is a network activation - if so, run the activation function for each blog id
            if ( $networkwide ) {
                $old_blog = $wpdb->blogid;
                // Get all blog ids
                $blogids  = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM {$wpdb->blogs}" ) );
                foreach ( $blogids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    self::_activate();
                    self::_add_caps();
                }
                switch_to_blog( $old_blog );
                return;
            }
        }

        self::_activate();
        self::_add_caps();
    }

    /**
     * Flushes the caps for the roles
     *
     * @global type $wp_rewrite
     */
    public static function _add_caps( $roles = array() ) {
        global $wp_roles;

        if ( class_exists( 'WP_Roles' ) ) {
            if ( !isset( $wp_roles ) ) {
                $wp_roles = new WP_Roles();
            }
        }

        /*
         * First reset the caps
         */
        $allRoles = get_editable_roles();
        foreach ( $allRoles as $role => $role_info ) {
            $wp_roles->remove_cap( $role, 'manage_glossary' );
        }

        $roles = !empty( $roles ) ? $roles : get_option( 'cmtt_glossaryRoles', array( 'administrator', 'editor' ) );

        if ( is_object( $wp_roles ) ) {

            foreach ( $roles as $role ) {
                $wp_roles->add_cap( $role, 'manage_glossary' );
            }
        }
    }

    /**
     * Flushes the rewrite rules to reflect the permalink changes automatically (if any)
     *
     * @global type $wp_rewrite
     */
    public static function _flush_rewrite_rules() {
        global $wp_rewrite;
        // First, we "add" the custom post type via the above written function.

        self::cmtt_create_post_types();

        do_action( 'cmtt_flush_rewrite_rules' );

        // Clear the permalinks
        flush_rewrite_rules();

        //Call flush_rules() as a method of the $wp_rewrite object
        $wp_rewrite->flush_rules();
    }

    public static function _get_meta( $meta_key, $id = null ) {
        global $wpdb;
        static $_cache = array();

        if ( !isset( $_cache[ $meta_key ] ) ) {
            if ( $metaCacheKey = apply_filters( 'cmtt_meta_cache_key', '', $meta_key, $id ) ) {
                $_cache[ $meta_key ] = wp_cache_get( $metaCacheKey );
                if ( false === $_cache[ $meta_key ] ) {
                    $_cache[ $meta_key ] = array_column( $wpdb->get_results( $wpdb->prepare( 'SELECT post_id,meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key="%s" LIMIT %d', $meta_key, '18446744073709551615' ), ARRAY_A ), 'meta_value', 'post_id' );
                    wp_cache_set( $metaCacheKey, $_cache[ $meta_key ], '', 3600 );
                }
            } else {
                $_cache[ $meta_key ] = array_column( $wpdb->get_results( $wpdb->prepare( 'SELECT post_id,meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key="%s" LIMIT %d', $meta_key, '18446744073709551615' ), ARRAY_A ), 'meta_value', 'post_id' );
            }
        }

        if ( null !== $id ) {
            $result = isset( $_cache[ $meta_key ][ $id ] ) ? $_cache[ $meta_key ][ $id ] : false;
        } else {
            $result = $_cache[ $meta_key ];
        }
        return $result;
    }

    public static function _get_term( $taxonomies, $id = null ) {
        global $wpdb;
        static $_cache = array();

        if ( !isset( $_cache[ $taxonomies ] ) ) {

            $taxonomies_query = "'" . $taxonomies . "'";
            $query            = "SELECT t.*, tt.*, tr.object_id as post_id FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id INNER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy IN ($taxonomies_query) LIMIT %d";
            $results          = $wpdb->get_results( $wpdb->prepare( $query, '18446744073709551615' ), ARRAY_A );
            foreach ( $results as $value ) {
                $_cache[ $taxonomies ][ $value[ 'post_id' ] ][] = $value[ 'name' ];
            }
        }

        if ( null !== $id ) {
            $result = isset( $_cache[ $taxonomies ][ $id ] ) ? $_cache[ $taxonomies ][ $id ] : false;
        } else {
            $result = $_cache[ $taxonomies ];
        }
        return $result;
    }

    /**
     * Get AJAX URL
     *
     * @since 1.3
     * @return string URL to the AJAX file to call during AJAX requests.
     */
    function _get_ajax_url() {
        $scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

        $current_url = edd_get_current_page_url();
        $ajax_url    = admin_url( 'admin-ajax.php', $scheme );

        if ( preg_match( '/^https/', $current_url ) && !preg_match( '/^https/', $ajax_url ) ) {
            $ajax_url = preg_replace( '/^http/', 'https', $ajax_url );
        }

        return apply_filters( 'cmtt_ajax_url', $ajax_url );
    }

}
