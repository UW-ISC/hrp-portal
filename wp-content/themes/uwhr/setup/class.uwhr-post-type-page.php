<?php

/**
 * Page
 *
 * Create custom taxonomies, update page admin table, etc
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

class UWHR_Page {

	function __construct() {
        add_action( 'init', array( $this, 'page_setup' ) );
		add_action( 'init', array( $this, 'create_page_keyword_taxonomy' ), 11 );
        add_action( 'manage_edit-page_columns', array( $this, 'add_columns' ) );
        add_action( 'manage_page_posts_custom_column', array( $this, 'fill_columns' ), 10, 2);
        add_filter( 'manage_edit-page_sortable_columns', array( $this, 'sort_columns' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
        add_action( 'admin_menu', array( $this, 'remove_meta_boxes' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'admin_notices', array( $this, 'thumbnail_notice_error' ) );
        add_action( 'save_post', array( $this, 'save_meta_box_data' ) );
        add_action( 'save_post', array( $this, 'bust_caches' ), 11 );

        add_action( 'pre_get_posts', array( $this, 'orberby' ) );
	}

    /**
     * Page Setup
     *
     * Add post support for excerpt and other init-y sort of things
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.4.0
     * @package UWHR
     */
    function page_setup() {
        add_post_type_support( 'page', 'excerpt' );
        add_post_type_support( 'page', 'post-formats' );
    }

    /**
     * Create page taxonomies
     *
     * Keyword taxonomies are used for search results weights
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.4.0
     * @package UWHR
     *
     * @global $UWHR Object
     */
	function create_page_keyword_taxonomy() {
        global $UWHR;

		$labels = array(
            'name'               => 'Keywords',
            'singular_name'      => 'Keyword',
            'menu_name'          => 'Keywords',
            'name_admin_bar'     => 'Keyword',
            'all_items'          => 'All Keywords',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Keyword',
            'edit_item'          => 'Edit Keyword',
            'new_item'           => 'New Keyword',
            'view_item'          => 'View Keyword',
            'search_items'       => 'Search Keywords',
            'not_found'          => 'No keywords found.',
            'not_found_in_trash' => 'No keywords found in Trash.',
            'parent_item'        => null,
            'parent_item_colon'  => null,
		);

		if ( current_user_can( $UWHR->get_edit_structure_cap() ) ) {
            $args = array(
                'hierarchical'      => false,
                'labels'            => $labels,
                'public'            => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => false,
                'rewrite'           => false,
            );
        } else {
            $args = array(
                'hierarchical'      => false,
                'labels'            => $labels,
                'public'            => true,
                'show_ui'           => false,
                'rewrite'           => false,
            );
        }

        $post_types = get_post_types( array( 'exclude_from_search' => false ) );
        unset($post_types['attachment']);

        register_taxonomy(
            'keyword',
            $post_types,
            $args
        );
	}

    /**
     * Add Columns
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param array $columns
     * @return array $columns
     *
     * @global $UWHR Object
     */
	function add_columns( $columns ) {
        global $UWHR;

		$auth = $columns['author'];
		$date = $columns['date'];
        unset($columns['author']);
        unset($columns['comments']);
        unset($columns['date']);
        $columns['featured'] = 'Featured';
        $columns['sidebar'] = 'Sidebar';
        if ( current_user_can( $UWHR->get_admin_cap() ) ) {
            $columns['menu'] = 'In Menu';
            $columns['template'] = 'Template';
        }
        $columns['author'] = $auth;
        $columns['date'] = $date;
        return $columns;
    }

    /**
     * Fill Columns
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
        if ( $colname == 'featured') {
            $featured = get_post_meta( $post_id, '_uwhr_page_featured', true );
            $order = get_post_meta( $post_id, '_uwhr_page_featured_order', true );

            if ( $featured == 1 ) {
                echo 'Order: ' . $order;
            } else {
                echo '—';
            }
        }

        if ( $colname == 'template') {
           	$templates = get_page_templates();
		   	$template = array_search( get_page_template_slug( $post_id ), $templates);
		   	echo $template ? $template : 'Default';
        }

        if ( $colname == 'sidebar') {
            $no_sidebar = get_post_meta( $post_id, '_uwhr_page_no_sidebar', true );
            echo (($no_sidebar == 0 ) ? '<span class="dashicons dashicons-yes"></span>' : '' );
        }

        if ( $colname == 'menu') {
            $exclude = get_post_meta( $post_id, '_uwhr_page_exclude_from_sidebar_menu', true );
            echo (($exclude == 0 ) ? '<span class="dashicons dashicons-yes"></span>' : '' );
        }
    }

    /**
     * Enqueue scripts for page editing
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     *
     * @param array $columns
     * @return array $columns
     */
    function sort_columns( $columns ) {
        $columns['sidebar'] = '_uwhr_page_no_sidebar';
        $columns['menu'] = '_uwhr_page_exclude_from_sidebar_menu';
        $columns['featured'] = '_uwhr_page_featured';

        return $columns;
    }

    function orberby( $query ) {
        if( ! is_admin() )
            return;

        $orderby = $query->get( 'orderby' );

        if ( substr( $orderby, 0, 5 ) === '_uwhr'  ) {
            $query->set( 'orderby', 'meta_value' );
            $query->set( 'meta_key', $orderby );
        }
    }

    /**
     * Enqueue scripts for page editing
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.5.0
     * @package UWHR
     *
     * @param $hook_suffix string The admin page hook
     */
    function admin_scripts( $hook_suffix ) {
        if ( $hook_suffix == 'post.php' || $hook_suffix == 'post-new.php' ) {
            wp_enqueue_script(
                'uwhr_page_scripts',
                get_template_directory_uri() . '/assets/admin/js/edit-page.js',
                array( 'jquery' ),
                '1.0'
            );

            wp_enqueue_style(
                'uwhr_page_styles',
                get_template_directory_uri() . '/assets/admin/css/edit-page.css'
            );
        }
    }

    /**
     * Remove Meta Boxes
     *
     * Hide metaboxes for low-level users
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $UWHR Object
     */
    function remove_meta_boxes() {
        global $UWHR;

        if ( ! current_user_can( $UWHR->get_edit_structure_cap() ) ) {
            remove_meta_box( 'pageparentdiv', 'page', 'normal' );
            remove_meta_box( 'authordiv', 'page', 'normal' );
            remove_meta_box( 'formatdiv', 'page', 'normal' );
        }

        if ( ! current_user_can( $UWHR->get_admin_cap() ) ) {
            remove_meta_box( 'slugdiv', 'page', 'normal' );
        }

        remove_meta_box( 'postcustom', 'page', 'normal' );
    }

    /**
     * Add Meta Boxes
     *
     * Add meta boxes to the post type
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $UWHR Object
     */
    function add_meta_boxes( $post_type ) {
        global $UWHR;

        if ( current_user_can( $UWHR->get_edit_structure_cap() ) OR current_user_can( $UWHR->get_publisher_role() ) ) {
            add_meta_box(
                'page_no_sidebar',
                'Right Sidebar',
                array( $this, 'render_no_sidebar_box_content' ),
                'page',
                'side',
                'low'
            );

            add_meta_box(
                'page_sidebar_content',
                'Right Sidebar Content',
                array( $this, 'render_sidebar_content_box_content'),
                'page',
                'normal',
                'low'
            );

            add_meta_box(
                'page_featured',
                'Is this a "Featured Page"?',
                array( $this, 'render_featured_box_content' ),
                'page',
                'side',
                'low'
            );

            add_meta_box(
                'page_short_title',
                'Short Title',
                array( $this, 'render_short_title_box_content' ),
                'page',
                'side'
            );

            add_meta_box(
                'page_image_focus',
                'Image Focus',
                array( $this, 'render_image_focus_box_content' ),
                'page',
                'side'
            );
        }

        if ( current_user_can( $UWHR->get_edit_structure_cap() ) ) {
            add_meta_box(
                'page_anchor_links',
                'Anchor Links',
                array( $this, 'render_anchor_linking_box_content' ),
                'page',
                'side',
                'low'
            );

            add_meta_box(
                'page_exclude_from_sidebar',
                'Exclude from Nav Menu',
                array( $this, 'render_page_exclude_from_sidebar_box_content' ),
                'page',
                'side',
                'low'
            );
        }

        // Developer Tools
        if ( current_user_can( $UWHR->get_admin_cap() ) ) {
            add_meta_box(
                'page_custom_js',
                'Load Custom JavaScript',
                array( $this, 'render_custom_js_box_content' ),
                'page',
                'side',
                'low'
            );
        }
    }

    /**
     * Render Sidebar Checkbox
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.5.0
     * @package UWHR
     *
     * @param WP_Post $post The post object.
     */
    public function render_no_sidebar_box_content( $post ) {
        // Only going to add one nonce for the entire page
        wp_nonce_field( 'uwhr_save_page_inner_custom_box', 'uwhr_save_page_inner_custom_box_nonce' );

        $data = get_post_meta( $post->ID, '_uwhr_page_no_sidebar', true );

        echo '<p>';
            echo 'Does this page require a right sidebar for secondary content? If not, the main content will stretch the width of the page.';
        echo '</p>';

        $checked = ' ';

        if ( ! empty( $data ) ) {
            $checked = ( $data == 1 ) ? ' checked=" checked" ' : ' ';
        }

        echo '<label for="pageNoSidebar"><input id="pageNoSidebar" type="checkbox" name="page_no_sidebar" value="1" ' . $checked . '/> No, this page should not have a sidebar.</label> ';
    }

    /**
     * Render Sidebar Content WYSIWYG
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param WP_Post $post The post object.
     */
    public function render_sidebar_content_box_content( $post ) {
        $data = get_post_meta( $post->ID, '_uwhr_page_sidebar_content', true );
        wp_editor( $data, $post->ID . '-editor', array( 'textarea_rows' => 10 ) );
    }

    /**
     * Render Featured Page Checkbox
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.5.0
     * @package UWHR
     *
     * @param WP_Post $post The post object.
     */
    public function render_featured_box_content( $post ) {
        $data = get_post_meta( $post->ID, '_uwhr_page_featured', true );

        echo '<p>';
            echo 'Featured pages appear on your unit homepage in the "Featured" section. Make this a Featured Page if you would like to highlight this content.';
        echo '</p>';

        $checked = '';
        $hidden = 'hidden';

        if ( ! empty($data) ) {
            $checked = ( $data == 1 ) ? ' checked=" checked" ' : ' ';
            $hidden = ' ';
        }

        echo '<label for="featuredPageCheckbox"><input id="featuredPageCheckbox" type="checkbox" name="featured_page" value="1" ' . $checked . '/> Yes, this is a featured page.</label> ';

        $data = get_post_meta( $post->ID, '_uwhr_page_quick_link_text', true );

        echo '<div id="callToActionTextContainer" class="'.$hidden.'">';
            echo '<p>';
                echo '<label for="quick_link_text">';
                echo 'Enter call to action text:';
                echo '</label> ';
            echo '</p>';

            echo '<input type="text" name="quick_link_text" class="widefat" placeholder="Explore"  value="' . esc_html( $data ) . '" />';

            echo '<p>';
                echo '<label for="featured_order">';
                echo 'Order:';
                echo '</label> ';
                echo '<select name="featured_order">';

                $options = array( 1, 2, 3 );

                $data = get_post_meta( $post->ID, '_uwhr_page_featured_order', true );
                // Retiring option 4 in the dropdown
                if ( $data == 4 ) {
                    $data = 3;
                }

                $firstPassFlag = false;
                foreach ( $options as $option ) {
                    $selected = ' ';

                    // Data is not empty, let's check it otherwise set first as default choice
                    if ( ! empty($data) ) {
                        $selected = ( $data == $option ) ? ' selected="selected" ' : ' ';
                    } else {
                        $selected = $firstPassFlag ? ' selected="selected" ' : ' ';
                    }

                    $firstPassFlag = false;
                    echo '<option value="'.$option.'"'.$selected.'>'.$option.'</option>';
                }

                echo '</select>';
            echo '</p>';
        echo '</div>';
    }

    /**
     * Render Short Title
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.9.0
     * @package UWHR
     *
     * @param WP_Post $post The post object.
     */
    public function render_short_title_box_content( $post ) {
        $data = get_post_meta( $post->ID, '_uwhr_page_short_title', true );

        echo '<p>';
            echo '<label for="pageShortTitle">';
                echo 'Short Title allows you to input a more concise title that will be displayed in the menu and breadcrumb. It’s a good option if your page titles are too long.';
            echo '</label> ';
        echo '</p>';

        echo '<input type="text" name="page_short_title" spellcheck="true" class="widefat" id="pageShortTitle" value="' . esc_attr( $data ) . '" size="50"  maxlength="25" />';
        echo '<p class="description">Optional. 25 max characters</p>';
    }

    /**
     * Render Image Focus
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.9.0
     * @package UWHR
     *
     * @param WP_Post $post The post object.
     */
    public function render_image_focus_box_content( $post ) {
        $data = get_post_meta( $post->ID, '_uwhr_page_image_focus', true );

        $image_focus_v = array(
            'Center',
            'Top',
            'Bottom'
        );

        $image_focus_h = array(
            'Center',
            'Left',
            'Right'
        );

        echo '<p>To ensure images do not get poorly cropped on varying screen sizes, select the area in your featured image that should always be the focus.</p>';

        echo '<p>Select vertical focus:</p>';

        echo '<ul>';

        $firstPassFlag = true;
        foreach ($image_focus_v as $pos) {
            $checked = ' ';

            if ( ! empty($data['vertical']) ) {
                $checked = ( $data['vertical'] == strtolower($pos) ) ? ' checked=" checked" ' : ' ';
            } else {
                $checked = $firstPassFlag ? ' checked=" checked" ' : ' ';
            }

            $firstPassFlag = false;

            echo '<li>';
                echo '<label for="pageImageFocusVertical'.$pos.'"><input id="pageImageFocusVertical'.$pos.'" type="radio" name="image_focus_vertical" value="' . strtolower($pos) . '"' . $checked . '/>' . $pos . '</label>';
            echo '</li>';
        }

        echo '</ul>';

        echo '<p>Select horizontal focus:</p>';

        echo '<ul>';

        $firstPassFlag = true;
        foreach ($image_focus_h as $pos) {
            $checked = ' ';

            if ( ! empty($data['horizontal']) ) {
                $checked = ( $data['horizontal'] == strtolower($pos) ) ? ' checked=" checked" ' : ' ';
            } else {
                $checked = $firstPassFlag ? ' checked=" checked" ' : ' ';
            }

            $firstPassFlag = false;

            echo '<li>';
                echo '<label for="pageImageFocusHorizontal'.$pos.'"><input id="pageImageFocusHorizontal'.$pos.'" type="radio" name="image_focus_horizontal" value="' . strtolower($pos) . '"' . $checked . '/>' . $pos . '</label>';
            echo '</li>';
        }
        echo '</ul>';

        echo '<p class="description">These fields are only saved if you have selected a featured image.</p>';
    }

    /**
     * Render Anchor Linking
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     *
     * @param WP_Post $post The post object.
     */
    public function render_anchor_linking_box_content( $post ) {
        $data = get_post_meta( $post->ID, '_uwhr_page_anchor_linking_active', true );

        echo '<p>';
            echo 'Do you want to display anchor links for all headings on this page?';
        echo '</p>';

        $checked = ' ';
        $hidden = 'hidden';

        if ( ! empty( $data ) ) {
            $checked = ( $data == 1 ) ? ' checked="checked" ' : ' ';
            $hidden = ' ';
        }

        echo '<label for="pageAnchorLinkingActiveCheckbox"><input id="pageAnchorLinkingActiveCheckbox" type="checkbox" name="page_anchor_linking_active" value="1" '. $checked . '/>Yes, show anchor links.</label>';

        $data = get_post_meta( $post->ID, '_uwhr_page_anchor_linking_more_btn', true );

        $checked = ' ';

        if ( ! empty( $data ) ) {
            $checked = ( $data == 1 ) ? ' checked="checked" ' : ' ';
        }

        echo '<div id="pageAnchorLinkingMoreBtnContainer" class="'.$hidden.'">';

        echo '<p>';
            echo 'By default, a more/less button hides a long list of anchor links. You can opt out of this and show all links all the time.';
        echo '</p>';

        echo '<label for="pageAnchorLinkingMoreBtnCheckbox"><input id="pageAnchorLinkingMoreBtnCheckbox" type="checkbox" name="page_anchor_linking_more_btn" value="1" '. $checked . '/>Display all links, always.</label>';

        echo '</div>';
    }

    /**
     * Render custom JS metabox
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param WP_Post $post The post object.
     */
    public function render_custom_js_box_content( $post ) {
        $data = get_post_meta( $post->ID, '_uwhr_page_custom_js', true );

        $checked = ' ';
        if ( !empty($data) ) {
            $checked = ( $data == 1 ) ? ' checked=" checked" ' : ' ';
        }

        echo '<label for="pageCustomJS"><input id="pageCustomJS" type="checkbox" name="page_custom_js" value="1" ' . $checked . '/> Yes, load a custom JS file.</label>';
        echo '<p class="description">Save a file named <em>exactly</em> the same as the page slug in the theme directory <code>assets/js/custom</code>.</p>';
    }

    /**
     * Render Exclude from Sidebar Checkbox
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.5.0
     * @package UWHR
     *
     * @param WP_Post $post The post object.
     */
    public function render_page_exclude_from_sidebar_box_content( $post ) {
        $data = get_post_meta( $post->ID, '_uwhr_page_exclude_from_sidebar_menu', true );

        echo '<p>';
            echo 'Should this page be excluded from the nav menu?';
        echo '</p>';

        $checked = ' ';

        if ( !empty($data) ) {
            $checked = ( $data == 1 ) ? ' checked=" checked" ' : ' ';
        }

        echo '<label for="pageExcludeFromSidebar"><input id="pageExcludeFromSidebar" type="checkbox" id="excludeSidebarCheckbox" name="exclude_from_sidebar" value="1" ' . $checked . '/> Yes, exclude this page from the nav menu.</label>';
    }

    /**
     * No Thumbnail Notice
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     *
     * @global $UWHR Object
     */
    function thumbnail_notice_error() {
        global $UWHR;

        if ( get_transient( 'has_post_thumbnail' ) == 1 AND get_current_blog_id() != 1 ) {
            echo '<div class="error"><p>This is a top level page or you have it set to be a featured post. Make sure you select a Featured Image if desired. Currently, the page will appear with a default image.</p></div>';
            delete_transient( 'has_post_thumbnail' );
        }
    }

    /**
     * Save the custom meta box data
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $UWHR Object
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_meta_box_data( $post_id ) {
        global $UWHR;

        // Verify nonce
        if ( ! isset( $_POST['uwhr_save_page_inner_custom_box_nonce'] ) ) {
            return $post_id;
        }

        $nonce = $_POST['uwhr_save_page_inner_custom_box_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'uwhr_save_page_inner_custom_box' ) ) {
            return $post_id;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }

        $parent_id = get_post( $post_id )->post_parent;

        if ( ( $parent_id == 0 AND ! has_post_thumbnail( $post_id ) ) OR ( isset( $_POST['featured_page'] ) AND ! has_post_thumbnail( $post_id ) ) ) {
            set_transient( 'has_post_thumbnail', 1 );
        } else {
            delete_transient( 'has_post_thumbnail' );
        }

        // Only change these things if the non-revisor metaboxes are actually on the page
        if ( current_user_can( $UWHR->get_edit_structure_cap() ) OR current_user_can( $UWHR->get_publisher_role() ) ) {

            // Update No Sidebar
            $data = ( isset( $_POST['page_no_sidebar'] ) ? 1 : 0 );
            update_post_meta( $post_id, '_uwhr_page_no_sidebar', $data );

            // Update Sidebar Content
            $data = ( isset( $_POST[$post_id . '-editor'] ) ? $_POST[$post_id . '-editor'] : '' );
            update_post_meta( $post_id, '_uwhr_page_sidebar_content', $data );

            // Update Featured Page
            $data = ( isset( $_POST['featured_page'] ) ? 1 : 0 );
            update_post_meta( $post_id, '_uwhr_page_featured', $data );

            // Update Quick Link Text
            if ( $data ) {
                $data = ( isset( $_POST['quick_link_text'] ) ? sanitize_text_field( $_POST['quick_link_text'] ) : '' );
                update_post_meta( $post_id, '_uwhr_page_quick_link_text', $data );

                update_post_meta( $post_id, '_uwhr_page_featured_order', $_POST['featured_order'] );
            }

            // Update Short Title
            if ( ! empty( $_POST['page_short_title'] ) ) {
                $data = sanitize_text_field( $_POST['page_short_title'] );
                update_post_meta( $post_id, '_uwhr_page_short_title', $data );
            } else {
                delete_post_meta($post_id, '_uwhr_page_short_title');
            }

            // Update image focus if there is a thumbnail only
            if ( has_post_thumbnail($post_id) ) {
                $data = array();
                $data['vertical'] = ( isset( $_POST['image_focus_vertical'] ) ? $_POST['image_focus_vertical'] : '' );
                $data['horizontal'] = ( isset( $_POST['image_focus_horizontal'] ) ? $_POST['image_focus_horizontal'] : '' );
                update_post_meta( $post_id, '_uwhr_page_image_focus', $data );
            } else {
                delete_post_meta($post_id, '_uwhr_page_image_focus');
            }
        }

        // Only change these things if the structure only metaboxes are actually on the page
        if ( current_user_can( $UWHR->get_edit_structure_cap() ) ) {
            // Update Anchor Linking
            if ( ! isset( $_POST['page_anchor_linking_active'] ) ) {
                $data = 0;
                delete_post_meta( $post_id, '_uwhr_page_anchor_links' );
            } else {
                $data = 1;
                $this->build_page_navigation( $post_id );
            }

            update_post_meta( $post_id, '_uwhr_page_anchor_linking_active', $data );

            // Update Anchor Link More Button
            if ( isset( $_POST['page_anchor_linking_more_btn'] ) ) {
                update_post_meta( $post_id, '_uwhr_page_anchor_linking_more_btn', 1 );
            } else {
                delete_post_meta($post_id, '_uwhr_page_anchor_linking_more_btn');
            }

            // Update Exclude from Pag Nav
            $data = ( isset( $_POST['exclude_from_sidebar'] ) ? 1 : 0 );
            update_post_meta( $post_id, '_uwhr_page_exclude_from_sidebar_menu', $data );
        }

        // Only change these things if the admin only metaboxes are actually on the page
        if ( current_user_can( $UWHR->get_admin_cap() ) ) {
            // Update Custom JS
            $data = ( isset( $_POST['page_custom_js'] ) ? 1 : 0 );
            update_post_meta( $post_id, '_uwhr_page_custom_js', $data );
        }
    }

    /**
     * Bust all page caches in the site to update menu system
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
        if ( ! isset( $_POST['uwhr_save_page_inner_custom_box_nonce'] ) ) {
            return $post_id;
        }

        $nonce = $_POST['uwhr_save_page_inner_custom_box_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'uwhr_save_page_inner_custom_box' ) ) {
            return $post_id;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            return $post_id;
        }

        // Get all page IDs that are in menu
        $IDs = array( get_option( 'page_on_front' ) );

        $args = array(
            'post_type' => 'page',
            'hierarchical' => 0,
            'meta_key' => '_uwhr_page_exclude_from_sidebar_menu',
            'meta_value' => 0,
        );
        $pages = get_pages( $args );

        foreach ( $pages as $page ) {
            $IDs[] = $page->ID;
        }

        foreach ( $IDs as $id ) {
            uwhr_flush_page_cache_by_id($id);
        }
        uwhr_flush_page_cache_by_url('/az', 1);
    }

    /**
     * Build Page Nav
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     *
     * @param $post_id int The WP Post Object id
     */
    private function build_page_navigation( $post_id ) {

        // Grab the post and post_content
        $page_data = get_post($post_id);
        $page_content = $page_data ? $page_data->post_content : '';

        $links = array();
        $results = '';
        $regex = '/<h4.*?>(.*?)<\/h4>/';

        if ( preg_match_all($regex, $page_content, $matches) ) {
            $results = $matches[1];
        } else {
            $results = '';
        }

        // Build out links named array with slug and title
        foreach ($results as $heading) {
            // Sluggify the heading
            $slug = sanitize_title($heading);

            // Store it in $links for saving
            $links[$slug] = $heading;
        }

        // Slugs are added to h4s in a filter on the_content function
        update_post_meta( $post_id, '_uwhr_page_anchor_links', $links );
    }
}

/**
 * Add External Link
 *
 * Taken straight from ACF's export PHP function
 * This function adds a field for external links when the page format is Link
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 0.7.0
 * @package UWHR
 */
if(function_exists("register_field_group"))
{
    register_field_group(array (
        'id' => 'acf_external-link',
        'title' => 'External Link',
        'fields' => array (
            array (
                'key' => 'field_56f58412534c0',
                'label' => 'External Link',
                'name' => 'external_page_link',
                'type' => 'text',
                'required' => 1,
                'default_value' => '',
                'placeholder' => 'http://',
                'prepend' => '',
                'append' => '',
                'formatting' => 'html',
                'maxlength' => '',
            ),
        ),
        'location' => array (
            array (
                array (
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'page',
                    'order_no' => 0,
                    'group_no' => 0,
                ),
                array (
                    'param' => 'post_format',
                    'operator' => '==',
                    'value' => 'link',
                    'order_no' => 1,
                    'group_no' => 0,
                ),
            ),
        ),
        'options' => array (
            'position' => 'normal',
            'layout' => 'default',
            'hide_on_screen' => array (
            ),
        ),
        'menu_order' => 1,
    ));
}
