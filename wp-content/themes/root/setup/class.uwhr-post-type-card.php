<?php

/**
 * Card
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

class UWHR_Card {

    public $button_text_choices;

    function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'manage_edit-card_columns', array( $this, 'add_columns') );
        add_action( 'manage_card_posts_custom_column', array( $this, 'fill_columns' ), 10, 2);
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this,  'save_meta_box_data' ) );

        $this->subtitle_choices = array(
            'Undaunted',
            'We > Me',
            'Dare to Do',
            'Be the First',
            'Question the Answer',
            'Passion Never Rests',
            'Be A World of Good',
            'Together We Will',
            'Driven to Discover',
        );
    }

    /**
     * Register Post Type
     *
     * Registers the card post type only for users that can edit structure
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @global $UWHR Object
     */
    function register_post_type() {
        global $UWHR;

        $labels = array(
            'name'                  => 'Cards',
            'singular_name'         => 'Card',
            'menu_name'             => 'Cards',
            'name_admin_bar'        => 'Card',
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New Card',
            'new_item'              => 'New Card',
            'edit_item'             => 'Edit Card',
            'view_item'             => 'View Card',
            'all_items'             => 'All Cards',
            'search_items'          => 'Search Cards',
            'parent_item_colon'     => 'Parent Cards:',
            'not_found'             => 'No cards found.',
            'not_found_in_trash'    => 'No cards found in Trash.'
        );

        $args = array(
            'labels'                => $labels,
            'description'           => 'A flexible piece of content to display on the home page or sidebar of lower-level pages.',
            'public'                => true,
            'exclude_from_search'   => true,
            'rewrite'               => array( 'with_front' => false ),
            'capability_type'       => 'post',
            'has_archive'           => false,
            'hierarchical'          => false,
            'menu_position'         => 21,
            'menu_icon'             => 'dashicons-format-gallery',
            'supports'              => array( 'title', 'thumbnail' )
        );

        if ( current_user_can( $UWHR->get_edit_structure_cap() ) ) {
            register_post_type( 'card', $args );
        }
    }

    /**
     * Add admin table columns
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param array $columns
     *
     * @return array $columns
     */
    function add_columns( $columns ) {
        $date = $columns['date'];
        unset($columns['date']);
        $columns['image'] = 'Image';
        $columns['button_link'] = 'Link';
        $columns['date'] = $date;
        return $columns;
    }

    /**
     * Fill admin table columns
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param string $colname
     * @param int $post_id
     */
    function fill_columns( $colname, $post_id ) {
        if ( $colname == 'image' ) {
            the_post_thumbnail( 'thimble' );
        }

        if ( $colname == 'button_link') {
            $link = get_post_meta( $post_id, '_uwhr_card_link', true );
            if ( $link ) {
                echo $link;
            } else {
                echo '---';
            }
        }
    }

    /**
     * Add Meta Boxes
     *
     * Add meta boxes to the registered post type only for users that can edit structure
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @global $UWHR Object
     */
    function add_meta_boxes( $post_type ) {
        global $UWHR;

        // Only display if user has capability
        if ( current_user_can( $UWHR->get_edit_structure_cap() ) ) {

            add_meta_box(
                'card_subtitle',
                'Sub Title',
                array( $this, 'render_subtitle_box_content' ),
                'card',
                'normal',
                'high'
            );

            add_meta_box(
                'card_text',
                'Text',
                array( $this, 'render_text_box_content' ),
                'card',
                'normal',
                'high'
            );

            add_meta_box(
                'card_button',
                'Button',
                array( $this, 'render_button_meta_box_content' ),
                'card',
                'normal',
                'core'
            );
        }
    }

    /**
     * Render meta box content
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @param WP_Post $post The post object.
     */
    public function render_subtitle_box_content( $post ) {
        // Only going to add one nonce for the entire page
        wp_nonce_field( 'uwhr_save_card_inner_custom_box', 'uwhr_save_card_inner_custom_box_nonce' );
        $data = get_post_meta( $post->ID, '_uwhr_card_subtitle', true );

        echo '<p>';
            echo '<label for="card_subtitle">';
            echo 'Select a brand tenent:';
            echo '</label> ';
        echo '</p>';

        echo '<ul>';

        $firstPassFlag = true;

        foreach ($this->subtitle_choices as $subtitle) {
            $checked = ' ';

            // Data is not empty, let's check it otherwise set first as default choice
            if ( !empty($data) ) {
                $checked = ( $data == $subtitle ) ? ' checked=" checked" ' : ' ';
            } else {
                $checked = $firstPassFlag ? ' checked=" checked" ' : ' ';
            }

            $firstPassFlag = false;

            echo '<li>';
            echo '<input type="radio" name="card_subtitle" value="' . $subtitle . '"' . $checked . '/>' . $subtitle;
            echo '</li>';
        }

        echo '</ul>';
    }

    /**
     * Render meta box content
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @param WP_Post $post The post object.
     */
    public function render_text_box_content( $post ) {
        $data = get_post_meta( $post->ID, '_uwhr_card_text', true );
        echo '<textarea name="card_text" spellcheck="true"  class="widefat" placeholder="Enter some text" cols="50" rows="3" maxlength="175" required />' . esc_attr( $data )  . '</textarea>';
        echo '<p class="description">Required. 175 max characters</p>';

    }

    /**
     * Render meta box content
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @param WP_Post $post The post object.
     */
    public function render_button_meta_box_content( $post ) {
        $data = get_post_meta( $post->ID, '_uwhr_card_button', true );

        echo '<p>';
            echo '<label for="card_button">';
            echo 'Enter the button text:';
            echo '</label> ';
        echo '</p>';

        echo '<input type="text" name="card_button" spellcheck="true" class="widefat" id="card_button" placeholder="Enter button text"  value="' . esc_attr( $data ) . '" size="50"  maxlength="20" required />';
        echo '<p class="description">Required. 20 max characters</p>';

        $data = get_post_meta( $post->ID, '_uwhr_card_link', true );

        echo '<p>';
        echo '<label for="card_link">';
        echo 'Where do you want to send the user?';
        echo '</label> ';
        echo '</p>';

        echo '<input type="url" name="card_link" class="widefat" id="card_link" placeholder="http://"  value="' . esc_url( $data ) . '" />';
    }

    /**
     * Save the meta when the post is saved.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_meta_box_data( $post_id ) {

        // Verify nonce
        if ( ! isset( $_POST['uwhr_save_card_inner_custom_box_nonce'] ) ) {
            return $post_id;
        }

        $nonce = $_POST['uwhr_save_card_inner_custom_box_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'uwhr_save_card_inner_custom_box' ) ) {
            return $post_id;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        if ( 'card' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }

        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }

        $data = ( isset( $_POST['card_subtitle'] ) ? $_POST['card_subtitle'] : '' );
        update_post_meta( $post_id, '_uwhr_card_subtitle', $data );

        $data = sanitize_text_field( $_POST['card_text'] );
        update_post_meta( $post_id, '_uwhr_card_text', $data );

        $data = sanitize_text_field( $_POST['card_button'] );
        update_post_meta( $post_id, '_uwhr_card_button', $data );

        $data = sanitize_text_field( $_POST['card_link'] );
        update_post_meta( $post_id, '_uwhr_card_link', $data );
    }
}
