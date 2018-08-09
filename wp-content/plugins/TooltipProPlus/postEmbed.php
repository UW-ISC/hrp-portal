<?php

class CMTT_Post_Embed {

    const PARAM_EMBED        = 'embed';
    const PARAM_LENGTH_LIMIT = 'length';
    const POST_TYPE          = 'glossary';

    public static function init() {
        add_action( 'plugins_loaded', array( get_called_class(), 'plugins_loaded' ), 300 );
    }

    static function getLabel( $key ) {
        $labels = array(
            'embed_button_text'     => 'Embed',
            'embed_label'           => 'Embed Glossary Term',
            'copy_instruction'      => 'Copy the following HTML iframe code to your website:',
            'copy_clipboard_button' => 'Copy to clipboard',
        );

        return isset( $labels[ $key ] ) ? $labels[ $key ] : '';
    }

    static function plugins_loaded() {
        if ( get_option( 'cmtt_embed_enabled', '0' ) ) {
            add_filter( 'cmtt_glossary_term_after_content', array( __CLASS__, 'add_to_page' ), 20, 2 );
        }
        if ( static::isEmbed() ) {
            add_filter( 'template_include', array( get_called_class(), 'template_include' ), PHP_INT_MAX - 5 );
            add_filter( 'the_content', array( get_called_class(), 'the_content' ), PHP_INT_MAX - 5 );
            add_filter( 'body_class', array( get_called_class(), 'body_class' ), PHP_INT_MAX - 5 );
            add_filter( 'cmtt_add_backlink', '__return_false', PHP_INT_MAX - 5 );
            add_action( 'enqueue_embed_scripts', array( get_called_class(), 'add_scripts' ), PHP_INT_MAX - 5 );
            add_action( 'embed_html', array( get_called_class(), 'embed_html' ), PHP_INT_MAX - 5, 4 );
            remove_action( 'embed_footer', 'print_embed_sharing_dialog' );
            add_action( 'embed_footer', array( get_called_class(), 'embed_sharing_dialog' ) );
        }
    }

    static function embed_sharing_dialog() {
        if ( is_404() ) {
            return;
        }
        ?>
        <div class="wp-embed-share-dialog hidden" role="dialog" aria-label="<?php esc_attr_e( 'Sharing options' ); ?>">
            <div class="wp-embed-share-dialog-content">
                <div class="wp-embed-share-dialog-text">
                    <ul class="wp-embed-share-tabs" role="tablist">
                        <li class="wp-embed-share-tab-button wp-embed-share-tab-button-html" role="presentation">
                            <button type="button" role="tab" aria-controls="wp-embed-share-tab-html" aria-selected="true" tabindex="-1"><?php esc_html_e( 'HTML Embed' ); ?></button>
                        </li>
                    </ul>
                    <div id="wp-embed-share-tab-html" class="wp-embed-share-tab" role="tabpanel" aria-hidden="false">
                        <textarea class="wp-embed-share-input" aria-describedby="wp-embed-share-description-html" tabindex="0" readonly><?php echo esc_textarea( get_post_embed_html( 600, 400 ) ); ?></textarea>

                        <p class="wp-embed-share-description" id="wp-embed-share-description-html">
                            <?php _e( 'Copy and paste this code into your site to embed.' ); ?>
                            <br/>
                            <?php _e( '<strong>Note:</strong>You can replace the <strong>"X"</strong> with the number to limit the length of the description which will be displayed in the iframe.' ); ?>
                        </p>
                    </div>
                </div>

                <button type="button" class="wp-embed-share-dialog-close" aria-label="<?php esc_attr_e( 'Close sharing dialog' ); ?>">
                    <span class="dashicons dashicons-no"></span>
                </button>
            </div>
        </div>
        <?php
    }

    static function embed_html( $output, $post, $width, $height ) {
        $url    = self::getEmbedLink( null, $post );
        $output = static::_loadView( 'views/frontend/embed/iframe-template.php', compact( 'url' ) );
        return $output;
    }

    static function add_scripts() {
        wp_enqueue_style( 'dashicons' );
    }

    static function getEmbedLink( $url = null, $post = null ) {
        if ( empty( $url ) ) {
            if ( empty( $post ) ) {
                global $post;
            }
            $url = get_permalink( $post );
        }
        $url = add_query_arg( array(static::PARAM_EMBED => 1, static::PARAM_LENGTH_LIMIT => 'X'), $url );
        return $url;
    }

    static function isEmbed() {
        return (filter_input( INPUT_GET, static::PARAM_EMBED ) == 1);
    }

    static function getLengthLimit() {
        return (int)filter_input( INPUT_GET, static::PARAM_LENGTH_LIMIT );
    }

    static function isPostType() {
        global $post;
        if ( empty( $post ) ) {
            return false;
        }
        return $post->post_type == static::POST_TYPE;
    }

    static function template_include( $template ) {
        if ( self::isPostType() ) {
            $template = CMTT_PLUGIN_DIR . 'views/frontend/embed/blank-template.php';
        }
        return $template;
    }

    static function the_content( $content ) {
        global $post;
        if ( 'glossary' == $post->post_type ) {
            $lengthLimit = static::getLengthLimit();
            if ( $lengthLimit ) {
                $content = cminds_truncate( $content, $lengthLimit, '...', false, true );
            }
            $content = static::_loadView( 'views/frontend/embed/embed-single.php', compact( 'content' ) );
        }
        return $content;
    }

    static function body_class( $class ) {
        $class[] = 'cmtt-embed';
        return $class;
    }

    static function _loadView( $_view, $params = array() ) {
        ob_start();
        extract( $params );
        include CMTT_PLUGIN_DIR . $_view;
        $content = ob_get_clean();
        return $content;
    }

    static function embed_code_content( $url = null ) {
        if ( empty( $url ) ) {
            global $post;
            $url = get_permalink( $post );
        }
        $url = add_query_arg( static::PARAM_EMBED, 1, $url );

        $iframe  = static::_loadView( 'views/frontend/embed/iframe-template.php', compact( 'url' ) );
        $content = static::_loadView( 'views/frontend/embed/embed-code-modal.php', compact( 'iframe' ) );
        echo $content;
    }

    static function show_embed_code( $url = null ) {
        $url  = self::getEmbedLink( $url );
        $html = sprintf( '<button class="cmtt-embed-btn btn button" href="#" title="%s"><span class="dashicons dashicons-share-alt2"></span>%s</button>', esc_attr( static::getLabel( 'embed_label' ) ), static::getLabel( 'embed_button_text' )
        );

        $html .= static::embed_code_content( $url );
        return $html;
    }

    static function add_to_page( $content, $glossary_item ) {
        if ( !static::isEmbed() ) {
            $url     = get_permalink( $glossary_item );
            $content = static::show_embed_code( $url ) . $content;
        }
        return $content;
    }

}
