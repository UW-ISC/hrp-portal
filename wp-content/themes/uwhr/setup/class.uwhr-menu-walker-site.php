<?php

/**
 * Site Menu Walker
 *
 * Walks a sites page structure to output sibling, child and parent pages
 *
 * Extends the Walker_Page class from WordPress and iterates over each page,
 * in order, for the site. This class overrides these functions and includes:
 *
 * start_lvl:   custom classes, ids and children classes where needed, and indentation
 * end_lvl:     indentation
 * start_el:    custom classes, child toggle where needed, current page class, open toggle class, and indentation
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

class UWHR_Site_Menu_Walker extends Walker_Page {

    /*
     * Use a private variable in the class object to store the currently iterated page.
     * This allows us to use the current item to asign an ID in the start_lvl function.
     */
    private $itereePage;

    /*
     * Add to the indent depth to better format rendered markup
     */
    private $indentOffset = 2;

    public function start_lvl( &$output, $depth = 0, $args = array() ) {
        // Offset by one to have non-zero depths, it makes more sense to me
        $depth = $depth + 1;

        $indent = ( $depth ) ? str_repeat( "\t", $depth + $this->indentOffset ) : '';

        // Create arrays for each tags classes, or ID string
        $ul_css_class   = array( 'children', 'collapse', 'depth-' . $depth );
        $ul_id          = $this->itereePage->post_name;

        // Get current page cause start_lvl doesn't give it to you
        $currentPage = get_post();

        // Get all child pages of iteree page
        $child_pages = get_pages( array( 'child_of' => $this->itereePage->ID ) );

        // Check if current page is a child of iteree page or has children pages,
        // then add 'in' class toggling open menu
        if ( in_array( $currentPage, $child_pages ) OR $this->itereePage->ID === $currentPage->ID ) {
            $ul_css_class[] = 'in';
        }

        // Create string for writing to DOM
        $ul_css_classes = implode( ' ', $ul_css_class );

        // Markup formatting
        $format = "\n" . $indent . '<ul class="%s" id="%s">' . "\n";

        $output .= sprintf( $format,
            $ul_css_classes,
            $ul_id
        );
    }

    public function end_lvl( &$output, $depth = 0, $args = array() ) {
        // Offset by one to have non-zero depths, it makes more sense to me
        $depth = $depth + 1;

        $indent = str_repeat("\t", $depth + $this->indentOffset);
        $output .= "$indent</ul>";
    }

    public function start_el( &$output, $page, $depth = 0, $args = array(), $current_page_id = 0 ) {
        // Set current page in class to use in start_lvl function
        $this->itereePage = $page;

        // Offset by one to have non-zero depths, it makes more sense to me
        $depth = $depth + 1;

        // Get stuff and set stuff
        $page_id = $page->ID;
        $page_name = $page->post_name;
        $page_title = get_the_short_title($page_id);
        $page_url = get_permalink($page_id);
        $childrenToggle = $a_attributes = '';

        $indent = ( $depth ) ? str_repeat( "\t", $depth + $this->indentOffset ) : '';

        // Create array for tags classes, we'll add when needed
        $li_css_class = array( 'nav-item' );
        $a_css_class = array( 'nav-link', 'nav-link-' . $page_id );

        if ( has_post_format('link',$page_id) ) {
            $a_css_class[] = 'nav-external-link';
        }

        // Check if page has children and output toggle collapse button, otherwise skip it
        if ( $args['has_children'] == 1 ) {
            $att = 'button';
            $li_css_class[] = 'has-children';
            $a_css_class[] = 'children-toggle';
            $a_attributes .= ' data-toggle="collapse"';
            $a_attributes .= ' data-target="#' . $page_name . '"';
            $a_attributes .= ' aria-controls="' . $page_name . '"';
            $childrenToggle = '<i class="fa fa-2x"></i>';
        } else {
            $att = 'a';
            $a_attributes .= ' title="' . $page_title . '"';
            $a_attributes .= ' href="' . $page_url . '"';
        }

        // Checks if on current page
        if ( $page_id === $current_page_id ) {
            $li_css_class[] = 'current';
            $li_css_class[] = 'is-open';
            $a_attributes .= ' aria-expanded="true"';
        }

        // Get current page object
        $currentPage = get_post();

        // Get all child pages of iteree page
        $child_pages = get_pages( array( 'child_of' => $page_id ) );

        // Check if current page is a child of iteree page or has children pages,
        // then add 'open' class toggling open menu
        if ( in_array( $currentPage, $child_pages ) ) {
            $li_css_class[] = 'is-open';
            $a_attributes .= ' aria-expanded="true"';
        } else {
            if ( $args['has_children'] == 1 ) {
                $a_attributes .= ' aria-expanded="false"';
            }
        }

        // Create string for writing to DOM
        $li_css_classes = implode( ' ', $li_css_class );
        $a_css_classes = implode( ' ', $a_css_class );

        // Markup formatting
        $format = "\n" . $indent . '<li class="%s"><%s class="%s" %s>%s%s</%s>';

        $output .= sprintf( $format,
            $li_css_classes,
            $att,
            $a_css_classes,
            $a_attributes,
            $page_title,
            $childrenToggle,
            $att
        );
    }
}
