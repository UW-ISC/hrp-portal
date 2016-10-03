<?php

/**
 * UWHR Dropdown Menu Walker
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

class UWHR_Dropdowns_Walker_Menu extends Walker_Nav_Menu {

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
        $ul_css_class   = array( 'children', 'depth-' . $depth );
        $ul_attributes  = '';

        // Create the ul classes and tag attributes for first level dropdown menus
        if ( $depth == 1 ) {
            $ul_css_class[] = 'dropdown-popup';
            $ul_attributes  = ' aria-expanded="false" id="' . $this->itereeItem->post_name . '"';
        }

        // Create string for writing to DOM
        $ul_css_classes = implode( ' ', $ul_css_class );

        // Markup formatting
        $format = $indent . '<ul class="%s" %s>';

        $output .= sprintf( $format,
            $ul_css_classes,
            $ul_attributes
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

        $menu_group_label = false;

        // Get stuff and set stuff
        $item_id = $item->ID;
        $item_title = $item->title;
        $item_name = $item->post_name;

        // Create the a tag attributes from the menu item
        $a_attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
        // $a_attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
        $a_attributes .= ! empty( $item->target )     ? '' : '';
        $a_attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
        $a_attributes .= ! empty( $item->classes )    ? ' class="'  . esc_attr( implode( ' ', $item->classes ) ) .'"' : '';

        $indent = ( $depth ) ? str_repeat( "\t", $depth + $this->indentOffset ) : '';

        // Create arrays for each tags classes
        $li_css_class = array( 'nav-item', 'nav-item-' . $item_id );

        // Add special class to top level navs
        if ( $depth == 1 ) {
            $li_css_class[] = 'top-level-nav-item';
            $a_attributes .= 'aria-controls="' . $item_name . '" aria-haspopup="true"';
        } else {
            $a_attributes .= ! empty( $item->url ) ? ' href="' . esc_attr( $item->url ) .'"' : '';
        }

        // Check if page has children and at depth of at least one, otherwise skip it
        if ($args["walker"]->has_children && $depth > 1 ) {
            $li_css_class[] = 'has-children';
            $li_css_class[] = 'menu-group';
            $menu_group_label = true;
        }

        // Create string for writing to DOM
        $li_css_classes = implode( ' ', $li_css_class );

        // Markup formatting
        if ( $depth == 1 ) {

            $format = $indent . '<li class="%s"><button %s>%s<i class="fa fa-lg"></i></button>';

            $output .= sprintf( $format,
                $li_css_classes,
                $a_attributes,
                $item_title
            );

        } else if ( $menu_group_label ) {

            $format = $indent . '<li class="%s"><p class="menu-group-label">%s</p>';

            $output .= sprintf( $format,
                $li_css_classes,
                $item_title
            );

        } else {

            $format = $indent . '<li class="%s"><a %s>%s</a>';

            $output .= sprintf( $format,
                $li_css_classes,
                $a_attributes,
                $item_title
            );

        }
    }
}
