<?php

/**
 * UWHR Mobile Dropdown Menu Walker
 *
 * Walks through the menu tree data and outputs markup for display.
 * Accessiblity, classes, and JS ids are added where needed. Backbone's view,
 * uwhr.dropdowns.js handles the interactions.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

class UWHR_Mobile_Dropdowns_Walker_Menu extends Walker_Nav_Menu {

    /*
     * Add to the indent depth to better format rendered markup
     */
    private $indentOffset = 2;

    /*
     * Use a private variable in the class object to store the currently iterated page.
     * This allows us to use the current item to asign an ID in the start_lvl function.
     */
    private $itereeItem;

    public function start_lvl( &$output, $depth = 0, $args = array() ) {
        // Offset by one to have non-zero depths, it makes more sense to me
        $depth = $depth + 1;

        $indent = ( $depth ) ? str_repeat( "\t", $depth + $this->indentOffset ) : '';

        // Create arrays for each tags classes, or ID string
        $ul_css_class   = array( 'children', 'collapse', 'depth-' . $depth );
        $ul_id          = $this->itereeItem->post_name;

        // Get current page cause start_lvl doesn't give it to you
        $currentPage = get_post();

        // Get all child pages of iteree page
        $child_pages = get_pages( array( 'child_of' => $this->itereeItem->ID ) );

        // Check if current page is a child of iteree page, then add 'in' class toggling open menu
        if ( in_array( $currentPage, $child_pages) ) {
            $ul_css_class[] = 'in';
        }

        // Create string for writing to DOM
        $ul_css_classes = implode( ' ', $ul_css_class );

        // Markup formatting
        $format = $indent . '<ul class="%s" id="%s-mm">' . "\n";

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

    public function start_el( &$output, $item, $depth = 0, $args = array(), $current_page = 0 ) {
        // Set current page in class to use in start_lvl function
        $this->itereeItem = $item;

        // Offset by one to have non-zero depths, it makes more sense to me
        $depth = $depth + 1;

        // Get stuff and set stuff
        $item_id = $item->ID;
        $item_title = $item->title;
        $item_name = $item->post_name;
        $childrenToggle = $a_attributes = '';

        $indent = ( $depth ) ? str_repeat( "\t", $depth + $this->indentOffset ) : '';

        // Create arrays for each tags classes
        $a_css_class = array( 'page-item', 'page-item-' . $item_id );
        $li_css_class = array( 'page-item', 'page-item-' . $item_id );

        // print_r($args);

        // Check if page has children and output toggle collapse button, otherwise skip it
        if ( $args->walker->has_children ) {
            $att = 'button';
            $li_css_class[] = 'has-children';
            $a_css_class[] = 'children-toggle';
            $a_attributes .= ' data-toggle="collapse"';
            $a_attributes .= ' data-target="#' . $item_name . '-mm"';
            $a_attributes .= ' aria-controls="' . $item_name . '-mm"';
            $childrenToggle = '<i class="fa fa-2x"></i>';
        } else {
            $att = 'a';
            // Create the a tag attributes from the menu item
            $a_attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
            // $a_attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
            $a_attributes .= ! empty( $item->target )     ? '' : '';
            $a_attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
            $a_attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
            $a_attributes .= ! empty( $item->classes )    ? ' class="'  . esc_attr( implode( ' ', $item->classes ) ) .'"' : '';
        }

        // Create string for writing to DOM
        $a_css_classes = implode( ' ', $a_css_class );
        $li_css_classes = implode( ' ', $li_css_class );

        // Markup formatting
        $format = $indent . '<li class="%s"><%s %s class="%s">%s%s</%s>';

        $output .= sprintf( $format,
            $li_css_classes,
            $att,
            $a_attributes,
            $a_css_classes,
            $item_title,
            $childrenToggle,
            $att
        );
    }
}
