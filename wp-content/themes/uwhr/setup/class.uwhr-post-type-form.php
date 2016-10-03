<?php

/**
 * Form
 *
 * Forms are a custom post type registered to manage UWHRs set of form across Units.
 *
 * Forms can be created in the Admin interface by admins only. Once a form is created metadata
 * is appended to it, including an actual PDF document. Consult UWHR Form documentation to properly
 * create and save Form files.
 *
 * Once at least one Form has been created, an speical instruction page is created for all
 * users to access. That page allows non-admin users to grab shortcode snippets to display their
 * Forms on their pages.
 *
 * Forms are searchable.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.5.0
 * @package UWHR
 */

class UWHR_Form {

    function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'manage_edit-form_columns', array( $this, 'add_columns') );
        add_action( 'manage_form_posts_custom_column', array( $this, 'fill_columns' ), 10, 2);
        add_action( 'init', array( $this, 'add_meta_boxes_at_init' ) ); // Gotta use init hook because we're using ACF
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_box_data' ) );
        add_action( 'save_post', array( $this, 'bust_caches' ), 11 );
        add_action( 'admin_menu', array( $this, 'forms_admin_page' ) );

        add_shortcode( 'form', array( $this, 'form_shortcode_handler' ) );
        add_filter( 'uwhr_search_post_types', array( $this, 'search_post_types' ) );
    }

    /**
     * Register Post Type
     *
     * Registers the form post type
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.5.0
     * @package UWHR
     *
     * @global $UWHR Object
     */
    function register_post_type() {
        global $UWHR;

        $labels = array(
            'name'                  => 'Forms',
            'singular_name'         => 'Form',
            'menu_name'             => 'Forms',
            'name_admin_bar'        => 'Form',
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New Form',
            'new_item'              => 'New Form',
            'edit_item'             => 'Edit Form',
            'view_item'             => 'View Form',
            'all_items'             => 'All Forms',
            'search_items'          => 'Search Forms',
            'parent_item_colon'     => 'Parent Forms:',
            'not_found'             => 'No forms found.',
            'not_found_in_trash'    => 'No forms found in Trash.'
        );

        $args = array(
            'labels'                => $labels,
            'description'           => 'Forms used for users to download.',
            'public'                => true,
            'exclude_from_search'   => false,
            'rewrite'               => array( 'with_front' => false ),
            'capability_type'       => 'post',
            'has_archive'           => true,
            'hierarchical'          => false,
            'menu_position'         => 20.32,
            'menu_icon'             => 'dashicons-media-document',
            'supports'              => array( 'title', 'revisions' )
        );

        if ( current_user_can( $UWHR->get_admin_cap() ) ) {
            register_post_type( 'form', $args );
        }
    }

    /**
     * Add admin table columns
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.5.0
     * @package UWHR
     *
     * @param array $columns
     *
     * @return array $columns
     */
    function add_columns( $columns ) {
        $date = $columns['date'];
        unset($columns['date']);
        $columns['file'] = 'File';
        $columns['shortcode'] = 'Shortcode';
        $columns['date'] = $date;
        return $columns;
    }

    /**
     * Fill admin table columns
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.5.0
     * @package UWHR
     *
     * @param string $colname
     * @param int $post_id
     */
    function fill_columns( $colname, $post_id ) {
        if ( $colname == 'file') {
            $file = get_form_file( $post_id );
            if ( ! empty($file) ) {
                echo '<a href="' . $file['url'] . '">View</a>';
            } else {
                echo '—';
            }
        }
        if ( $colname == 'shortcode') {
            echo '<code>[form id="' . $post_id . '"]</code>';
        }
    }

    /**
     * Add Meta Boxes
     *
     * Add meta boxes to the registered post type
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.5.0
     * @package UWHR
     */
    function add_meta_boxes_at_init( $post_type ) {
        if ( function_exists('register_field_group' ) ) {
            register_field_group(array(
                'id' => 'acf_form',
                'title' => 'Form',
                'fields' => array(
                    array (
                        'key' => 'field_573e48650a21d',
                        'label' => 'Instructions',
                        'name' => '',
                        'type' => 'message',
                        'message' => 'You must either upload a file that UWHR owns or link to an externally hosted file. Use one or the other. Will default to external file if both are present.',
                    ),
                    array(
                        'key' => 'field_56c790132c5d7',
                        'label' => 'File',
                        'name' => 'form_file',
                        'type' => 'file',
                        'instructions' => 'Upload or select a file.',
                        'save_format' => 'object',
                        'library' => 'uploadedTo',
                    ),
                    array (
                        'key' => 'field_573e4736a9b53',
                        'label' => 'External File',
                        'name' => 'external_form_file',
                        'type' => 'text',
                        'instructions' => 'Paste an external file URL.',
                        'default_value' => '',
                        'placeholder' => 'http://',
                        'prepend' => '',
                        'append' => '',
                        'formatting' => 'none',
                        'maxlength' => '',
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'form',
                            'order_no' => 0,
                            'group_no' => 0,
                        ),
                    ),
                ),
                'options' => array(
                    'position' => 'normal',
                    'layout' => 'default',
                    'hide_on_screen' => array(
                        0 => 'permalink',
                        1 => 'the_content',
                        2 => 'excerpt',
                        3 => 'custom_fields',
                        4 => 'discussion',
                        5 => 'comments',
                        6 => 'revisions',
                        7 => 'slug',
                        8 => 'author',
                        9 => 'format',
                        10 => 'featured_image',
                        11 => 'categories',
                        12 => 'tags',
                        13 => 'send-trackbacks',
                    ),
                ),
                'menu_order' => 1,
            ));
        }
    }

    /**
     * Add Meta Boxes
     *
     * Add meta boxes to the registered post type
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.5.0
     * @package UWHR
     */
    function add_meta_boxes( $post_type ) {
        add_meta_box(
            'form_short_title',
            'Short Title',
            array( $this, 'render_form_short_title_box_content'),
            'form',
            'side',
            'low'
        );

        add_meta_box(
            'form_description',
            'Description',
            array( $this, 'render_form_description_box_content'),
            'form',
            'normal',
            'low'
        );
    }

    /**
     * Render Form Short Title Text
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 1.0.6
     * @package UWHR
     *
     * @param WP_Post $post The post object.
     */
    public function render_form_short_title_box_content( $post ) {
        // Only going to add one nonce for the entire page
        wp_nonce_field( 'uwhr_save_form_inner_custom_box', 'uwhr_save_form_inner_custom_box_nonce' );

        $data = get_post_meta( $post->ID, '_uwhr_form_short_title', true );
        echo '<p>';
            echo '<label for="formShortTitle">';
                echo 'Short Title allows you to input a more concise title that will be displayed in the content whenever the form shortcode is used. It’s a good option if your form titles are too long or specific.';
            echo '</label> ';
        echo '</p>';

        echo '<input type="text" name="form_short_title" spellcheck="true" class="widefat" id="formShortTitle" value="' . esc_attr( $data ) . '" size="50"  maxlength="25" />';
        echo '<p class="description">Optional. 25 max characters</p>';
    }

    /**
     * Render Form Description Textarea
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 1.0.6
     * @package UWHR
     *
     * @param WP_Post $post The post object.
     */
    public function render_form_description_box_content( $post ) {
        $data = get_post_meta( $post->ID, '_uwhr_form_description', true );
        echo '<textarea name="form_description" spellcheck="true"  class="widefat" cols="50" rows="3" />' . esc_attr( $data )  . '</textarea>';
        echo '<p class="description">Optional. A short description of this form that will appear on search result and the forms page.</p>';
    }

    /**
     * Save the custom meta box data
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 1.0.4
     * @package UWHR
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_meta_box_data( $post_id ) {
        // Verify nonce
        if ( ! isset( $_POST['uwhr_save_form_inner_custom_box_nonce'] ) ) {
            return $post_id;
        }

        $nonce = $_POST['uwhr_save_form_inner_custom_box_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'uwhr_save_form_inner_custom_box' ) ) {
            return $post_id;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        if ( !isset($_POST['post_type']) ) {
            return $post_id;
        }

        if ( 'form' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            return $post_id;
        }

        // Update Short Title
        if ( ! empty( $_POST['form_short_title'] ) ) {
            $data = sanitize_text_field( $_POST['form_short_title'] );
            update_post_meta( $post_id, '_uwhr_form_short_title', $data );
        } else {
            delete_post_meta($post_id, '_uwhr_form_short_title');
        }

        // Update Description
        if ( ! empty($_POST['form_description']) ) {
            $data = sanitize_text_field( $_POST['form_description'] );
            update_post_meta( $post_id, '_uwhr_form_description', $data );
        } else {
            delete_post_meta( $post_id, '_uwhr_form_description' );
        }
    }

    /**
     * Bust the forms page cache when a form is updated
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 1.0.4
     * @package UWHR
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function bust_caches( $post_id ) {
        // Verify nonce
        if ( ! isset( $_POST['uwhr_save_form_inner_custom_box_nonce'] ) ) {
            return $post_id;
        }

        $nonce = $_POST['uwhr_save_form_inner_custom_box_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'uwhr_save_form_inner_custom_box' ) ) {
            return $post_id;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        if ( !isset($_POST['post_type']) ) {
            return $post_id;
        }

        if ( 'form' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            return $post_id;
        }

        uwhr_flush_page_cache_by_url('/forms', 1);
    }

    /**
     * Forms Admin Page
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     *
     * @global $UWHR Object
     */
    function forms_admin_page() {
        global $UWHR;

        if ( ! current_user_can( $UWHR->get_admin_cap() ) ) {
            add_menu_page(
                'Forms',
                'Forms',
                'read',
                'forms',
                array( $this, 'forms_admin_page_cb' ),
                'dashicons-media-document',
                22.34
            );
        } else {
            add_submenu_page(
                'edit.php?post_type=form',
                'Instructions',
                'Instructions',
                'manage_options',
                'forms-instructions',
                array( $this, 'forms_admin_page_cb' )
            );
        }
    }

    /**
     * Forms Admin Page Callback
     *
     * Display a list of all Forms and instructions for non-Admin users.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     */
    function forms_admin_page_cb() {
        $args = array(
            'posts_per_page'   => -1,
            'orderby'          => 'title',
            'order'            => 'ASC',
            'post_type'        => 'form',
            'post_status'      => 'publish'
        );
        $forms = get_posts( $args );
        ?>
            <div class="wrap">
                <h2>Forms</h2>

                <h3>List of All <?php echo get_bloginfo('name'); ?> Forms <small><a href="#formInstructions">Instructions</a></small></h3>

                <p>Below you'll find a list of all your available forms and instructions on how to embed them into your page. While you own your forms and its content, UWHR Forms are centrally managed by MCE. Any requested change (i.e. updating the form file or adding a new Form) must be submitted here.</p>

                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Form</th>
                            <th style="width: 35%;">Description</th>
                            <th>File</th>
                            <th>Shortcode</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach($forms as $form){
                                $id = $form->ID;
                                $file = get_form_file( $id );

                                echo '<tr>';
                                    echo '<td>' . $form->post_title;
                                        if ( current_user_can('manage_options') ) {
                                            echo ' <small><a href="'.get_edit_post_link( $form->ID ).'">Edit</a></small>';
                                        }
                                    echo '</td>';

                                    $desc = get_post_meta( $id, '_uwhr_form_description', true );

                                    if ( !empty($desc) ) {
                                        echo '<td>'.$desc.'</td>';
                                    } else {
                                        echo '<td></td>';
                                    }

                                    if ( isset($file) ) {
                                        echo '<td><a target="_blank" href="' . $file['url'] . '">View</a></td>';
                                    } else {
                                        echo '<td>—</td>';
                                    }
                                    echo '<td><code>[form id="' . $id . '"]</code></td>';
                                echo '</tr>';
                            }
                        ?>
                    </tbody>
                </table>

                <p><a class="button button-primary button-large" href="mailto:<?php echo ( ( isset( get_option( 'unit_settings' )['web_help_forms_email'] ) ) ? get_option( 'unit_settings' )['web_help_forms_email'] : 'uwhrweb@uw.edu' ); ?>?subject=UWHR Forms Request">Request Form Change</a></p>

                <div class="postbox" style="margin-top: 20px;" id="formInstructions">
                    <div class="inside">
                        <h3>Instructions</h3>
                        <hr>
                        <?php if ( ! empty( $forms) ) {

                            $first = $forms[0];

                            $id = $first->ID;

                            $file = get_form_file( $id );

                            $mime_type = $file['mime_type'];
                            $link_file_class = uwhr_mime_type_format($mime_type, 'link');
                            $link_file_small = uwhr_mime_type_format($mime_type, 'small');
                            ?>

                            <p>To display a Form anywhere on your page, you simply need to copy and paste the shortcode associated with that Form. This will grab the latest file uploaded to that Form and display a link for users on your page, where you pasted the shortcode.</p>
                            <h4>Example:</h4>
                            <p><code>to begin this process please download [form id="<?php echo $id; ?>"] and send back to us.</code></p>
                            <h4>will display as:</h4>
                            <p>to begin this process please download <a href="#"><?php echo $first->post_title . ' ' . $link_file_small; ?></a> and send back to us.</p>

                            <hr>

                            <h4>Change Form title:</h4>
                            <p><code>[form id="<?php echo $id; ?>" title="Create New Link Text Here"]</code></p>
                            <p><a href="#">Create New Link Text Here <?php echo $link_file_small; ?></a></p>

                            <h4>Use Icon:</h4>
                            <p><code>[form id="<?php echo $id; ?>" icon=true]</code></p>
                            <p><a class="link-file <?php echo $link_file_class; ?>" href="#"><?php echo $first->post_title; ?></a></p>

                            <?php if ( current_user_can('manage_options') ) { ?>
                                <hr>
                                <h4>Forms from Other Sites: (Admins only)</h4>
                                <p>You can grab a Form that is owned by another site by specifying that site with the site="xx" attribute. Ex. <code>[form id="123" site="1"]</code></p>
                                <hr>
                                <h4>Other Documents: (Admins only)</h4>
                                <p>Add the class <code>.link-file</code> to any link to make it look like a Form document link. You can change the icon by using <code>.link-file-word</code>, <code>.link-file-pdf</code>, and <code>.link-file-excel</code>.</p>
                                <p><code>&lt;a href="#" class="link-file link-file-word"&gt;A Link&lt;/a&gt;</code></p>
                                <a href="#" class="link-file link-file-word">A Link</a>
                            <?php } ?>

                        <?php } else { ?>
                            <p>You haven't uploaded any forms yet.</p>
                            <?php if ( current_user_can('manage_options') ) { ?>
                                <p><a class="button button-primary button-large" href="<?php echo admin_url( 'post-new.php?post_type=form' );?>">Add New Form</a></p>
                            <?php } ?>

                        <?php } ?>
                    </div>
                </div>

            </div><!-- .wrap -->
        <?php
    }

    /**
     * Forms Shortcode
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     *
     * @param $atts Array Shortcode attributes
     * @param $content string The content of the shortcode
     *
     * @return $html string
     */
    function form_shortcode_handler( $atts, $content ) {
        $attributes = shortcode_atts( array(
            'id' => 0,
            'title' => '',
            'icon' => false,
            'site' => 0
        ), $atts );

        if ( $attributes['site'] != 0 ) {
            switch_to_blog($attributes['site']);
        }

        if ( $attributes['id'] == 0 ) {
            return;
        }

        $form = get_post( $attributes['id'] );
        if (empty($form)) {
            return;
        }
        $file = get_form_file( $attributes['id'] );

        $form_title = ( ! empty(get_post_meta( $attributes['id'], '_uwhr_form_short_title', true )) ) ? get_post_meta( $attributes['id'], '_uwhr_form_short_title', true ) : $form->post_title;
        $title = ( ! empty( $attributes['title'] ) ) ? $attributes['title'] : $form_title;

        $mime_type = $file['mime_type'];
        if ( $attributes['icon'] ) {
            $link_file_class = uwhr_mime_type_format($mime_type, 'link');
        } else {
            $link_file_small = uwhr_mime_type_format($mime_type, 'small');
        }

        $html = '<a class="uwhr-form ' . ((isset($link_file_class)) ? 'link-file ' . $link_file_class : '' ) .  ' " target="_blank" href="'.$file['url'].'">' . $title . ((isset($link_file_small)) ? ( ' ' . $link_file_small ) : '' ) . '</a>';

        if ( $attributes['site'] != 0 ) {
            #restore_current_blog();
        }

        return $html;
    }

    /**
     * Add form to search post types
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     *
     * @param $post_types Array List of post type slugs
     * @return $post_types Array
     */
    function search_post_types( $post_types ) {
        $post_types[] = 'form';
        return $post_types;
    }
}

/**
 * Get Form File
 *
 * Return an external form file if linked up, otherwise return the default form file uploading
 * via ACF input meta box
 *
 * In order to return the external file, which is technially just a text field, which sanitize it to
 * a URL and then add just the URL and a default mime_type of PDF. I'm making that assumption.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 0.9.0
 * @package UWHR
 *
 * @param $id int The post id
 *
 * @return $file array|boolean The form file
 */
function get_form_file( $id ) {
    $external_file = get_field( 'external_form_file', $id );

    if ( ! empty( $external_file ) ) {
        $file = array(
            'url' => esc_url($external_file),
            'mime_type' => 'application/pdf'
        );
        return $file;
    }

    $file = get_field( 'form_file', $id );
    if ( ! empty( $file ) ) {
        return $file;
    }

    return false;
}

