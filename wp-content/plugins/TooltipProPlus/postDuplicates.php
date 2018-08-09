<?php

class CMTT_Post_Duplicates {

    public static $urlParameter = 'cmtt-is-duplicate';

    public static function init() {

        /*
         * Admin part
         */
        add_filter( 'wp_insert_post_empty_content', array( __CLASS__, 'on_submitted_post' ), 10, 2 );

        add_action( 'after_delete_post', array( __CLASS__, 'on_deleted_post' ) );
        add_action( 'deleted_post', array( __CLASS__, 'on_deleted_post' ) );
        add_action( 'trashed_post', array( __CLASS__, 'on_deleted_post' ) );

        add_filter( 'untrashed_post', array( __CLASS__, 'on_untrashed_post' ) );

        add_action( 'edit_form_top', array( __CLASS__, 'display_notice' ) );

        /*
         * Frontend part
         */
        add_filter( 'cmtt_glossary_index_tooltip_content', array( __CLASS__, 'add_duplicates_content' ), 15, 2 );
        add_filter( 'cmtt_term_tooltip_content', array( __CLASS__, 'add_duplicates_content' ), 15, 2 );

        add_filter( 'cmtt_add_backlink_content', array( __CLASS__, 'display_duplicates_on_term_page' ), 100, 2 );
    }

    /**
     * Add the duplicates to the content of the tooltip
     * @param type $content
     * @param type $glossary_item
     * @return type
     */
    public static function display_duplicates_on_term_page( $content, $glossary_item ) {
        $enabled = get_option( 'cmtt_alternativeMeaningsInGlossaryTermPage', '1' );
        if ( !$enabled ) {
            return $content;
        }
        $duplicates = self::find_duplicates( $glossary_item->post_title, $glossary_item->ID );

        $tag = 'div';
        if ( count( $duplicates ) > 0 ) {
            $content .= '<div class="cmtt_alternative_meanings_wrapper">';
            $content .= '<' . $tag . ' class="cmtt_related_title cmtt_related_terms_title">' . __( get_option( 'cmtt_glossary_AlternativeMeaningLabel', 'Alternative Meanings:' ), 'cm-tooltip-glossary' ) . ' </' . $tag . '>';
            $content .= '<ul class="cmtt_related">';
            foreach ( $duplicates as $key => $duplicate ) {
                /*
                 * We want to display the icon
                 */
                $title = get_the_title( $duplicate );
                $content.= '<li class="cmtt_related_item">';
                $content.= '<a href="' . get_permalink( $duplicate->ID ) . '">' . $title . '</a>';
                $content.= '<div>' . cminds_truncate( do_shortcode( $duplicate->post_content ), 2000 ) . '</div>';
                $content.= '</li>';
            }
            $content.= '</ul>';
            $content .= '</div>';
        }


        return $content;
    }

    /**
     * Add the duplicates to the content of the tooltip
     * @param type $content
     * @param type $glossary_item
     * @return type
     */
    public static function add_duplicates_content( $content, $glossary_item ) {
        $enabled = get_option( 'cmtt_alternativeMeaningsInTooltips', '1' );
        if ( !$enabled ) {
            return $content;
        }
        $duplicates = self::find_duplicates( $glossary_item->post_title, $glossary_item->ID );
        if ( !empty( $duplicates ) ) {
            $content = '<div class="cmtt_meaning_label">1</div>' . $content;
            foreach ( $duplicates as $key => $duplicateId ) {
                $duplicate = get_post( $duplicateId );
                $content .= sprintf( '<div><div class="cmtt_meaning_label">%d</div>', 2 + $key );
                $content .= (get_option( 'cmtt_glossaryExcerptHover' ) && $duplicate->post_excerpt) ? $duplicate->post_excerpt : $duplicate->post_content;
                $content .= '</div>';
            }
        }
        return $content;
    }

    public static function display_notice( $post ) {
        $duplicateIds = get_post_meta( $post->ID, self::$urlParameter, true );

        if ( empty( $duplicateIds ) ) {
            return;
        }

        if ( !is_array( $duplicateIds ) ) {
            $duplicateIds = array( $duplicateIds );
        }

        $duplicatesArr = array();
        foreach ( $duplicateIds as $postId ) {
            $tooltipcontent  = get_the_excerpt( $postId );
            $duplicate       = sprintf( '<a href="%s" target="_blank" class="cmtt_field_help" title="%s">%s</a>(<a href="%s" target="_blank">%s</a>)', get_permalink( $postId ), $tooltipcontent, $postId, get_edit_post_link( $postId ), 'edit' );
            $duplicatesArr[] = $duplicate;
        }
        ob_start();
        ?>
        <div id="warning" class="notice notice-warning"><p id="is-duplicate">This post is a duplicate of: <?php echo implode( ', ', $duplicatesArr ); ?></p></div>
        <?php
        $content = ob_get_clean();
        echo $content;
    }

    public static function find_duplicates( $title, $id ) {
        $maybe_duplicates = get_posts( array(
            'post_type'    => 'glossary',
            'post_status'  => 'publish',
            'numberposts'  => -1,
            'nopaging'     => true,
            'title'        => $title,
            'post__not_in' => array( $id )
        ) );

        return $maybe_duplicates;
    }

    public static function mark_duplicates( $the_ID, $deleted = false ) {
        $post_data = get_post( $the_ID, ARRAY_A );
        if ( $post_data[ 'post_type' ] == 'glossary' && $post_data[ 'post_status' ] !== 'auto-draft' && !empty( $post_data[ 'post_title' ] ) ) {
            $maybe_duplicates = self::find_duplicates( $post_data[ 'post_title' ], $post_data[ 'ID' ] );

            if ( !empty( $maybe_duplicates ) && is_array( $maybe_duplicates ) ) {
                $duplicateIds = array_map( function($v) {
                    return $v->ID;
                }, $maybe_duplicates );
                update_post_meta( $the_ID, self::$urlParameter, $duplicateIds );

                foreach ( $duplicateIds as $ID ) {
                    $duplicateIdsCopy = $duplicateIds;
                    $duplicateIdsCopy = array_map( function ($v) use ($ID, $the_ID, $deleted) {
                        if ( $deleted ) {
                            return $v == $ID ? '' : $v;
                        } else {
                            return $v == $ID ? $the_ID : $v;
                        }
                    }, $duplicateIdsCopy );
                    update_post_meta( $ID, self::$urlParameter, array_filter( $duplicateIdsCopy ) );
                }
            }
        }
        return;
    }

    /**
     * Hook to the "wp_insert_post_empty_content" filter, since that is the only place
     * we can intercept the creation of a new post and not let it be created
     * @global type $usp_options
     * @param type $maybe_empty
     * @param type $post_data
     * @return type
     */
    public static function on_submitted_post( $maybe_empty, $post_data ) {
        $enabled = get_option( 'cmtt_alternativeMeaningsAllow', '1' );
        if ( !$enabled ) {

            if ( $post_data[ 'post_type' ] == 'glossary' && $post_data[ 'post_status' ] !== 'auto-draft' && !empty( $post_data[ 'post_title' ] ) ) {
                $maybe_duplicates = self::find_duplicates( $post_data[ 'post_title' ], $post_data[ 'ID' ] );
                if ( !empty( $maybe_duplicates ) ) {

                    /*
                     * Redirect doesn't work
                     */
//                    $location = admin_url('post-new.php?post_type=glossary');
//                    wp_redirect($location);
//                    wp_die();

                    /*
                     * Return true treats the post as empty and doesn't save
                     */
                    return true;
                }
            }
        }

        self::mark_duplicates( $post_data[ 'ID' ] );
        return $maybe_empty;
    }

    /**
     * Update post duplicates after post was untrashed
     * @param type $the_ID
     */
    public static function on_untrashed_post( $the_ID ) {
        self::mark_duplicates( $the_ID );
    }

    /**
     * Update post duplicates after post was deleted
     * @param type $the_ID
     */
    public static function on_deleted_post( $the_ID ) {
        self::mark_duplicates( $the_ID, true );
    }

}
