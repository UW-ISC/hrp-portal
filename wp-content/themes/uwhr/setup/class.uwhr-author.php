<?php

/**
 * UWHR Author
 *
 * Configures the authoring experience in the UWHR websites.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.3.0
 * @package UWHR
 */

class UWHR_Author {

    function __construct() {
        add_action( 'admin_init', array( $this, 'tinymce_stylesheet' ) );
        add_filter( 'mce_buttons_2', array( $this, 'tinymce_style_button') );
        add_filter( 'tiny_mce_before_init',  array( $this, 'tinymce_init' ) );
    }

    /**
     * TinyMCE Stylesheet
     *
     * Loads a custom stylesheet for content editing
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     */
    function tinymce_stylesheet() {
        add_editor_style( get_template_directory_uri() . '/assets/admin/css/editor.css' );
    }

    /**
     * Tiny MCE Init
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.5.0
     * @package UWHR
     */
    function tinymce_style_button( $buttons ) {
        $inserted = array( 'styleselect' );
        array_splice( $buttons, 1, 0, $inserted );
        return $buttons;
    }

    /**
     * Tiny MCE Init
     *
     * Rearrange buttons in WYSIWYG Editor, edit block formatting buttons, add custom style dropdown.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.5.0
     * @package UWHR
     *
     * @global $UWHR Object
     */
    function tinymce_init( $settings ) {
        global $UWHR;

        if ( current_user_can( $UWHR->get_admin_cap() ) ) {
            $settings['toolbar1'] = 'bold,italic,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,pastetext,removeformat,charmap,outdent,indent,undo,redo,formatselect,styleselect';
            $settings['toolbar2'] = '';

            $settings['block_formats'] = "Paragraph=p;Heading=h4;Subheading=h5;Subsubheading=h6;Preformatting=pre";
        } else {
            $settings['toolbar1'] = 'bold,italic,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,pastetext,charmap,outdent,indent,undo,redo,formatselect,styleselect';
            $settings['toolbar2'] = '';

            $settings['block_formats'] = "Paragraph=p;Heading=h4;Subheading=h5;Subsubheading=h6;";
        }

        $style_formats_structure = array(
            array(
                'title' => 'Purple',
                'block' => 'h4',
                'classes' => 'purple',
                'wrapper'   => false
            ),
            array(
                'title' => 'Gold',
                'block' => 'h4',
                'classes' => 'gold',
                'wrapper'   => false
            ),
            array(
                'title' => 'Thin',
                'block' => 'h4',
                'classes' => 'thin',
                'wrapper'   => false
            ),
            array(
                'title' => 'Bold',
                'block' => 'h4',
                'classes' => 'bold',
                'wrapper'   => false
            ),
            array(
                'title' => 'Beefy',
                'block' => 'h4',
                'classes' => 'beefy',
                'wrapper'   => false
            ),
            array(
                'title' => 'Blockquote Left',
                'selector' => 'blockquote',
                'classes' => 'blockquote-left',
                'wrapper'   => false
            ),
            array(
                'title' => 'Blockquote Right',
                'selector' => 'blockquote',
                'classes' => 'blockquote-right',
                'wrapper'   => false
            ),
            array(
                'title' => 'Small',
                'inline' => 'small',
                'wrapper'   => false
            ),
            array(
                'title' => 'Code',
                'inline' => 'code'
            )
        );
        $style_formats_non_structure = array(
            array(
                'title' => 'Beefy',
                'block' => 'h4',
                'classes' => 'beefy',
                'wrapper'   => false
            ),
            array(
                'title' => 'Blockquote Left',
                'selector' => 'blockquote',
                'classes' => 'blockquote-left',
                'wrapper'   => false
            ),
            array(
                'title' => 'Blockquote Right',
                'selector' => 'blockquote',
                'classes' => 'blockquote-right',
                'wrapper'   => false
            ),
            array(
                'title' => 'Small',
                'inline' => 'small',
                'wrapper'   => false
            ),
            array(
                'title' => 'Code',
                'inline' => 'code'
            )
        );

        if ( current_user_can( $UWHR->get_edit_structure_cap() ) ) {
            $settings['style_formats'] = json_encode( $style_formats_structure );
        } else {
            $settings['style_formats'] = json_encode( $style_formats_non_structure );
        }

        return $settings;
    }
}
