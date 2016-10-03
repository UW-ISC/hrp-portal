<?php

/**
 * Slide
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

class UWHR_Slide {

    public $button_text_choices;
    public $image_v_position;
    public $image_h_position;

    function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'manage_edit-slide_columns', array( $this, 'add_columns') );
        add_action( 'manage_slide_posts_custom_column', array( $this, 'fill_columns' ), 10, 2);
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this,  'save_meta_box_data' ) );

        $this->button_text_choices = array(
            'Read the Latest',
            'Learn More',
            'Explore',
            'Register for Class'
        );

        $this->image_v_position = array(
            'Center',
            'Top',
            'Bottom'
        );

        $this->image_h_position = array(
            'Center',
            'Left',
            'Right'
        );
    }

    /**
     * Register Post Type
     *
     * Registers the slide post type only for users that can edit structure
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
            'name'                  => 'Slides',
            'singular_name'         => 'Slide',
            'menu_name'             => 'Slides',
            'name_admin_bar'        => 'Slide',
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New Slide',
            'new_item'              => 'New Slide',
            'edit_item'             => 'Edit Slide',
            'view_item'             => 'View Slide',
            'all_items'             => 'All Slides',
            'search_items'          => 'Search Slides',
            'parent_item_colon'     => 'Parent Slides:',
            'not_found'             => 'No slides found.',
            'not_found_in_trash'    => 'No slides found in Trash.'
        );

        $args = array(
            'labels'                => $labels,
            'description'           => 'Slides used on the homepage of UW Human Resources.',
            'public'                => true,
            'exclude_from_search'   => true,
            'rewrite'               => array( 'with_front' => false ),
            'capability_type'       => 'post',
            'has_archive'           => true,
            'hierarchical'          => false,
            'menu_position'         => 20,
            'menu_icon'             => 'dashicons-images-alt',
            'supports'              => array( 'title', 'thumbnail', 'revisions' )
        );

        if ( current_user_can( $UWHR->get_edit_structure_cap() ) ) {
            register_post_type( 'slide', $args );
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
        $columns['color'] = 'Light/Dark';
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
            the_post_thumbnail( 'rss' );
        }

        if ( $colname == 'button_link') {
            $link = get_post_meta( $post_id, '_uwhr_slide_link', true );
            if ( $link ) {
                echo $link;
            } else {
                echo '---';
            }
        }

        if ( $colname == 'color') {
            $data = get_post_meta( $post_id, '_uwhr_slide_color', true );
            if ( $data == 'light' ) {
                echo 'Light';
            } else {
                echo 'Dark';
            }
        }

        if ( $colname == 'id') {
            echo $post_id;
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
                'slide_text',
                'Text',
                array( $this, 'render_text_box_content' ),
                'slide',
                'normal',
                'high'
            );

            add_meta_box(
                'slide_button',
                'Button Text',
                array( $this, 'render_button_meta_box_content' ),
                'slide',
                'normal',
                'core'
            );

            add_meta_box(
                'slide_color',
                'Color',
                array( $this, 'render_color_meta_box_content' ),
                'slide',
                'side',
                'core'
            );

            add_meta_box(
                'slide_image_position',
                'Image Alignment',
                array( $this, 'render_image_position_meta_box_content' ),
                'slide',
                'side',
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
    public function render_text_box_content( $post ) {
        // Only going to add one nonce for the entire page
        wp_nonce_field( 'uwhr_save_slide_inner_custom_box', 'uwhr_save_slide_inner_custom_box_nonce' );
        $data = get_post_meta( $post->ID, '_uwhr_slide_text', true );
        echo '<textarea name="slide_text" spellcheck="true"  class="widefat" placeholder="Enter some text" cols="50" rows="3" />' . esc_attr( $data )  . '</textarea>';
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

        // Button text
        $data = get_post_meta( $post->ID, '_uwhr_slide_button', true );

        echo '<p>';
            echo '<label for="slide_button">';
            echo 'Enter the button text:';
            echo '</label> ';
        echo '</p>';

        echo '<input type="text" name="slide_button" spellcheck="true" class="widefat" id="slide_button" placeholder="Enter button text"  value="' . esc_attr( $data ) . '" size="50"  maxlength="20" required />';
        echo '<p class="description">Required. 20 max characters</p>';

        // Button link
        $data = get_post_meta( $post->ID, '_uwhr_slide_link', true );

        echo '<p>';
        echo '<label for="slide_link">';
        echo 'Where do you want to send the user?';
        echo '</label> ';
        echo '</p>';

        echo '<input type="url" name="slide_link" class="widefat" id="slide_link" placeholder="http://"  value="' . esc_url( $data ) . '" />';
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
    public function render_color_meta_box_content( $post ) {

        echo '<p>';
        echo '<label for="slide_color">';
        echo 'Should the text be light or dark?';
        echo '</label> ';
        echo '</p>';

        $data = get_post_meta( $post->ID, '_uwhr_slide_color', true );

        $lightChecked = $darkChecked = '';

        // Data is not empty, let's check it otherwise set first as default choice
        if ( !empty($data) ) {
            if ( $data == 'dark' ) {
                $darkChecked = ' checked="checked"';
            }
            if ( $data == 'light' ) {
                $lightChecked = ' checked="checked"';
            }
        } else {
            $lightChecked = ' checked="checked"';
        }

        echo '<ul>';
            echo '<li>';
                echo '<input type="radio" name="slide_color" value="light"' . $lightChecked . '/>Light';
            echo '</li>';
            echo '<li>';
                echo '<input type="radio" name="slide_color" value="dark"' . $darkChecked . '/>Dark';
            echo '</li>';
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
    public function render_image_position_meta_box_content( $post ) {

        // Button text
        $data_v = get_post_meta( $post->ID, '_uwhr_slide_image_v_position', true );

        echo '<p>';
        echo '<label for="image_v_position">';
        echo 'Select vertical weight:';
        echo '</label> ';
        echo '</p>';

        echo '<ul>';

        $firstPassFlag = true;

        foreach ($this->image_v_position as $pos) {
            $checked = ' ';

            // Data is not empty, let's check it otherwise set first as default choice
            if ( !empty($data_v) ) {
                $checked = ( $data_v == strtolower($pos) ) ? ' checked=" checked" ' : ' ';
            } else {
                $checked = $firstPassFlag ? ' checked=" checked" ' : ' ';
            }

            $firstPassFlag = false;

            echo '<li>';
            echo '<input type="radio" name="image_v_position" value="' . strtolower($pos) . '"' . $checked . '/>' . $pos;
            echo '</li>';
        }
        echo '</ul>';

        // Button link
        $data_h = get_post_meta( $post->ID, '_uwhr_slide_image_h_position', true );

        echo '<p>';
        echo '<label for="image_h_position">';
        echo 'Select horizontal weight:';
        echo '</label> ';
        echo '</p>';

        echo '<ul>';

        $firstPassFlag = true;

        foreach ($this->image_h_position as $pos) {
            $checked = ' ';

            // Data is not empty, let's check it otherwise set first as default choice
            if ( !empty($data_h) ) {
                $checked = ( $data_h == strtolower($pos) ) ? ' checked=" checked" ' : ' ';
            } else {
                $checked = $firstPassFlag ? ' checked=" checked" ' : ' ';
            }

            $firstPassFlag = false;

            echo '<li>';
            echo '<input type="radio" name="image_h_position" value="' . strtolower($pos) . '"' . $checked . '/>' . $pos;
            echo '</li>';
        }
        echo '</ul>';
    }

    /**
     * Save the meta when the post is saved.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @global $UWHR Object
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_meta_box_data( $post_id ) {
        global $UWHR;

        // Verify nonces
        if ( ! isset( $_POST['uwhr_save_slide_inner_custom_box_nonce'] ) ) {
            return $post_id;
        }

        $nonce = $_POST['uwhr_save_slide_inner_custom_box_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'uwhr_save_slide_inner_custom_box' ) ) {
            return $post_id;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        if ( ! current_user_can( $UWHR->get_edit_structure_cap() ) ) {
            return $post_id;
        }

        if ( 'slide' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }

        $data = sanitize_text_field( $_POST['slide_text'] );
        update_post_meta( $post_id, '_uwhr_slide_text', $data );

        $data = ( isset( $_POST['slide_button'] ) ? $_POST['slide_button'] : '' );
        update_post_meta( $post_id, '_uwhr_slide_button', $data );

        $data = sanitize_text_field( $_POST['slide_link'] );
        update_post_meta( $post_id, '_uwhr_slide_link', $data );

        $data = ( isset( $_POST['slide_color'] ) ? $_POST['slide_color'] : '' );
        update_post_meta( $post_id, '_uwhr_slide_color', $data );

        $data = ( isset( $_POST['image_v_position'] ) ? $_POST['image_v_position'] : '' );
        update_post_meta( $post_id, '_uwhr_slide_image_v_position', $data );

        $data = ( isset( $_POST['image_h_position'] ) ? $_POST['image_h_position'] : '' );
        update_post_meta( $post_id, '_uwhr_slide_image_h_position', $data );
    }
}
