<?php

/**
 * Search Options
 *
 * Creates an options page and registers some settings for the site.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.3.0
 * @package UWHR
 */

class UWHR_Search_Options {

    /**
     * Constructor
     *
     * Adds actions to the WP admin hooks to register a menu page and settings
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     */
    function __construct() {
        add_action( 'admin_menu', array( $this, 'add_page' ) );
        add_action( 'admin_init', array( $this, 'settings_init' ) );
    }

    /**
     * Add Settings Page
     *
     * Registers an Options page if on the main site only
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $UWHR
     */
    function add_page() {
        global $UWHR;

        // Only add to the main site
        if ( get_current_blog_id() != $UWHR::MAIN_BLOG_ID ) {
            return;
        }

        add_menu_page(
            'Search Options',
            'Search Options',
            'manage_options',
            'search_options',
            array( $this, 'create_search_options_page_cb' ),
            'dashicons-search',
            81
        );
    }

    /**
     * Create Search Options Page
     *
     * The callback function for creating the Options page
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     */
    function create_search_options_page_cb() {
        ?>
        <div class="wrap">
            <h2>Search Options</h2>

            <?php $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'search'; ?>

            <h2 class="nav-tab-wrapper">
                <a href="?page=search_options&tab=search" class="nav-tab <?php echo $active_tab == 'search' ? 'nav-tab-active' : ''; ?>">Search</a>
                <a href="?page=search_options&tab=weights" class="nav-tab <?php echo $active_tab == 'weights' ? 'nav-tab-active' : ''; ?>">Weights</a>
                <a href="?page=search_options&tab=ui_components" class="nav-tab <?php echo $active_tab == 'ui_components' ? 'nav-tab-active' : ''; ?>">UI Components</a>
            </h2>

            <form method="post" action="options.php">
                <?php
                    if( $active_tab == 'search' ) {
                        settings_fields( 'searchOptionsSites' );
                        do_settings_sections( 'searchOptionsSites' );
                    } else if( $active_tab == 'weights' ) {
                        settings_fields( 'searchOptionsWeights' );
                        do_settings_sections( 'searchOptionsWeights' );
                    } else if ( $active_tab == 'ui_components' ) {
                        settings_fields( 'searchOptionsUI' );
                        do_settings_sections( 'searchOptionsUI' );
                    }

                    submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Settings
     *
     * Registers the actual settings used. These are stored in the database and accessible
     * with get_option( 'search_settings_****' ).
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $UWHR
     */
    function settings_init() {
        global $UWHR;

        // Only add to the main site
        if ( get_current_blog_id() != $UWHR::MAIN_BLOG_ID ) {
            return;
        }

        /**
         * Site Options Section
         *
         * A group of checkboxes for all the sites in the multisite environment. Toggle on
         * and off what sites are searchable.
         */
        register_setting( 'searchOptionsSites', 'search_settings_sites' );

        add_settings_section(
            'searchOptionsSites_section',
            'Search',
            '',
            'searchOptionsSites'
        );

        add_settings_field(
            'sites',
            'Sites Included in Search',
            array( $this, 'searchable_sites_checkboxes_render' ),
            'searchOptionsSites',
            'searchOptionsSites_section'
        );

        add_settings_field(
            'max',
            'Maximum Number of Search Results',
            array( $this, 'max_results_input_render' ),
            'searchOptionsSites',
            'searchOptionsSites_section'
        );

        /**
         * Weight Options Section
         *
         * Inputs that allow for quickly changing the relevance weight for
         * returned search results
         */
        register_setting( 'searchOptionsWeights', 'search_settings_weights', array( $this, 'sanitize_ints' ) );

        add_settings_section(
            'searchOptionsWeight_section',
            'Weights',
            array( $this, 'weight_section_callback' ),
            'searchOptionsWeights'
        );

        add_settings_field(
            'title_rel_weight',
            'Query in Title',
            array( $this, 'weight_inputs_render' ),
            'searchOptionsWeights',
            'searchOptionsWeight_section',
            array( 'param' => 'title', 'desc' => 'The query is found in the title.' )
        );

        add_settings_field(
            'title_word_rel_weight',
            'Word in Title',
            array( $this, 'weight_inputs_render' ),
            'searchOptionsWeights',
            'searchOptionsWeight_section',
            array( 'param' => 'title_word', 'desc' => 'A word in the query is found in the title.' )
        );

        add_settings_field(
            'lead_content_rel_weight',
            'Query in Lead of Content',
            array( $this, 'weight_inputs_render' ),
            'searchOptionsWeights',
            'searchOptionsWeight_section',
            array( 'param' => 'lead_content', 'desc' => 'The query is found in the first 100 characters.' )
        );

        add_settings_field(
            'lead_content_word_rel_weight',
            'Word in Lead of Content',
            array( $this, 'weight_inputs_render' ),
            'searchOptionsWeights',
            'searchOptionsWeight_section',
            array( 'param' => 'lead_content_word', 'desc' => 'A word in the query is found the first 100 characters.' )
        );

        add_settings_field(
            'content_rel_weight',
            'Query in Content',
            array( $this, 'weight_inputs_render' ),
            'searchOptionsWeights',
            'searchOptionsWeight_section',
            array( 'param' => 'content', 'desc' => 'The query is found in the rest of the content.' )
        );

        add_settings_field(
            'content_word_rel_weight',
            'Word in Content',
            array( $this, 'weight_inputs_render' ),
            'searchOptionsWeights',
            'searchOptionsWeight_section',
            array( 'param' => 'content_word', 'desc' => 'A word in the query is found in the rest of the content.' )
        );

        add_settings_field(
            'keyword_rel_weight',
            'Tagged Keyword',
            array( $this, 'weight_inputs_render' ),
            'searchOptionsWeights',
            'searchOptionsWeight_section',
            array( 'param' => 'keyword', 'desc' => 'The result is keyword tagged with the query.' )
        );

        add_settings_field(
            'staff_rel_weight',
            'Is Staff',
            array( $this, 'weight_inputs_render' ),
            'searchOptionsWeights',
            'searchOptionsWeight_section',
            array( 'param' => 'staff', 'desc' => 'The result is a staff member.' )
        );

        /**
         * UI Options Section
         *
         * The text and order of various UI components used in search.
         */
        register_setting( 'searchOptionsUI', 'search_settings_ui', array( $this, 'sanitize_wysiwyg' ) );

        add_settings_section(
            'searchOptionsUI_section',
            'UI Components',
            array( $this, 'ui_section_callback' ),
            'searchOptionsUI'
        );

        add_settings_field(
            'search_end',
            'End of Search',
            array( $this, 'search_end_wysiwyg_editor_render' ),
            'searchOptionsUI',
            'searchOptionsUI_section'
        );
    }

    /**
     * Settings Render Callback
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     */
    function searchable_sites_checkboxes_render() {
        $options = get_option( 'search_settings_sites' );
        $option = isset( $options['sites'] ) ? $options['sites'] : array();
        $siteIDs = get_all_site_ids();

        $i = 0;
        $half = ceil( count($siteIDs) / 2 );
        foreach ( $siteIDs as $id ) {
            if ( $i == $half ) {
                echo '</td><td>';
            }

            $checked = in_array( $id, $option ) ? ' checked="checked"' : '';
            echo '<p>';
                echo '<label for="site-'.$id.'">';
                    echo '<input id="site-'.$id.'" type="checkbox" name="search_settings_sites[sites][]" value="'.$id.'"' . $checked . '>' . get_blog_details( $id )->blogname;
                echo '</label>';
            echo '</p>';

            $i++;
        }
    }

    /**
     * Settings Render Callback
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     */
    function max_results_input_render() {
        $options = get_option( 'search_settings_sites' );

        $val = isset( $options['max'] ) ? $options['max'] : 0;

        ?>
        <input type="text" size="6" name="search_settings_sites[max]" value="<?php echo $val; ?>">
        <p class="description">Enter the maximum number of results.</p>
        <?php
    }

    /**
     * Settings Render Callback
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param $args Array The passed in arguments from add_settings_field function
     */
    function weight_inputs_render( $args ) {
        $options = get_option( 'search_settings_weights' );

        $param = $args['param'];
        $desc = isset( $args['desc'] ) ? $args['desc'] : '';

        $val = isset( $options[ $param . '_rel_weight'] ) ? $options[ $param . '_rel_weight'] : 0;

        ?>
        <input type="text" size="6" name="search_settings_weights[<?php echo $param; ?>_rel_weight]" value="<?php echo $val; ?>">
        <p class="description"><?php echo $desc; ?></p>
        <?php

    }

    /**
     * Settings Render Callback
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     */
    function search_end_wysiwyg_editor_render() {
        $options = get_option( 'search_settings_ui' );

        $content = $options['search_end'];
        $editor_id = 'search_end';
        $settings = array(
            'media_buttons'     => false,
            'textarea_rows'     => 8,
            'teeny'             => true,
            'wpautop'           => true
        );

        wp_editor( $content, $editor_id, $settings );
        echo '<p class="description">This html will go inside a .col-*-* div next to the searching dubs image.</p>';
    }

    /**
     * Section Render Callback
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     */
    function weight_section_callback() {
        echo '<p>When a user searches and sorts by relevance, we return the results based on a computed relevance scale. Each multiplier adds to the total weighted value. We look for the entire search query, as well as each word of the search query if there are multiple words.</p>';
    }

    /**
     * Section Render Callback
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     */
    function ui_section_callback() {
        echo '<p>Components and text that will be displayed on the front end.</p>';
    }

    /**
     * Sanitize WYSIWYG
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param $input array Pre-sanitized inputs
     */
    function sanitize_wysiwyg( $input ) {
        $options = get_option( 'search_settings_ui' );
        $options['search_end'] = stripslashes( $_POST['search_end'] );
        return $options;
    }

    /**
     * Sanitize Integers
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param $input array Pre-sanitized inputs
     *
     * @return $options array Sanitized named array of int values
     */
    function sanitize_ints( $input ) {
        $options = get_option( 'search_settings_weights' );
        foreach( $input as $key => $value ) {
            $options[$key] = is_numeric( $input[$key] ) ? $input[$key] : $options[$key];
        }
        return $options;
    }
}
