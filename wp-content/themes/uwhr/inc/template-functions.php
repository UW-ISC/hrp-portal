<?php
/**
 * Functions used throughout the UWHR theme
 *
 * Most functionality is actually built out in the class/ directory through
 * the global $UWHR Object. These functions are used in template files directly
 * and may need access to the $UWHR global.
 *
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

/*******************************************************************************/


/**
 * Header Menus
 *
 * Switches to main site and returns menus for the global navigation, dropdown, and mobile navigation.
 * Allow site managers to maintain one singular site nav structuree and persist across sites.
 *
 * @see header.php
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 *
 * @global $UWHR Object
 *
 * @return $menus array() Named array containing each menu markup
 */
if ( ! function_exists( 'uwhr_get_header_menus') ) {
    function uwhr_get_header_menus() {
        global $UWHR;

        // Get started with an empty array
        $menus = array(
            'global'        => '',
            'dropdown'      => '',
            'mobile'        => '',
            'mobile-btn'    => ''
        );

        // Switch to main site if not already there
        if ( get_current_blog_id() !== $UWHR::MAIN_BLOG_ID ) {
            switch_to_blog( $UWHR::MAIN_BLOG_ID );
        }

        // Build out the global navigation
        $menus['global'] .= '<nav class="uwhr-global-links" id="globalNav" aria-label="Global Menu" role="navigation">';

            $menus['global'] .= wp_nav_menu( array(
                'theme_location'  => $UWHR->Global_Menu->get_location(),
                'container'       => false,
                'echo'            => 0
            ) );

        $menus['global'] .= '</nav>';

        // Build out the dropdown menu
        $menus['dropdown'] .= '<nav class="uwhr-dropdowns" id="siteNav" aria-label="Main Menu" role="navigation">';

            $menus['dropdown'] .= wp_nav_menu( array(
                'theme_location'  => $UWHR->Dropdowns_Menu->get_location(),
                'container'       => false,
                'walker'          => new UWHR_Dropdowns_Walker_Menu(),
                'echo'            => 0
            ) );

        $menus['dropdown'] .= '</nav>';

        // Build out the mobile menu
        $menus['mobile'] .= '<div class="uwhr-mobile-menu"><div class="collapse" id="mobileMenu"><div class="uwhr-mobile-menu-wrapper">';

            $menus['mobile'] .= '<nav class="uwhr-accordion-menu uwhr-accordion-menu-dark" aria-label="Main Menu" role="navigation">';

                $menus['mobile'] .= wp_nav_menu( array(
                    'theme_location'  => $UWHR->Dropdowns_Menu->get_location(),
                    'container'       => false,
                    'container_class' => 'mm-panel',
                    'walker'          => new UWHR_Mobile_Dropdowns_Walker_Menu(),
                    'echo'            => 0,
                    'depth'           => 3
                ) );

            $menus['mobile'] .= '</nav>';

            $menus['mobile'] .= '<button class="uwhr-mobile-menu-global-toggle h4 text-uppercase" type="button" data-toggle="collapse" data-target="#mobileGlobalMenu" aria-expanded="false" aria-controls="mobileGlobalMenu"><i class="fa fa-plus"></i> More</button>';

            $menus['mobile'] .= '<div class="collapse" id="mobileGlobalMenu"><nav class="uwhr-global-links" aria-label="Global Menu" role="navigation">';

                $menus['mobile'] .= wp_nav_menu( array(
                    'theme_location'  => $UWHR->Global_Menu->get_location(),
                    'container'       => false,
                    'echo'            => 0
                ) );

            $menus['mobile'] .= '</nav></div>';

            $menus['mobile'] .= $UWHR->Search->UI->render_search_form( 'large m-y-2', true );

        $menus['mobile'] .= '</div></div></div>';

        // Mobile menu button
        $menus['mobile-btn'] .= '<button class="uwhr-mobile-menu-toggle" type="button" data-toggle="collapse" data-target="#mobileMenu" aria-expanded="false" aria-controls="mobileMenu">Menu <i class="fa fa-2x"></i></button>';

        ##restore_current_blog();

        return $menus;
    }
}

/**
 * Footer Menus
 *
 * Switches to main site and returns menus for the footer menus.
 * Allow site managers to maintain one singular site nav structure and persist across sites.
 *
 * @see footer.php
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 *
 * @global $UWHR Object
 *
 * @return $menus array() Named array containing each menu markup
 */
if ( ! function_exists( 'uwhr_get_footer_menus') ) {
    function uwhr_get_footer_menus() {
        global $UWHR;

        $menus = array(
            'quick-links' => '',
        );

        // Switch to main site if not already there
        if ( get_current_blog_id() !== $UWHR::MAIN_BLOG_ID ) {
            switch_to_blog( $UWHR::MAIN_BLOG_ID );
        }

        // Build out the footer quick links
        $menus['quick-links'] .= '<nav aria-label="Footer Quick Links" role="navigation">';

            $menus['quick-links'] .= wp_nav_menu( array(
                'theme_location'    => $UWHR->Footer_Quick_Links_Menu->get_location(),
                'container'         => false,
                'menu_class'        => 'footer-links',
                'echo'              => 0,
                'depth'             => 1
            ) );

        $menus['quick-links'] .= '</nav>';

        #restore_current_blog();

        return $menus;
    }
}

/**
 * Site Menu
 *
 * Lists all pages for the current site in a sidebar menu.
 * Exclude those tagged as such.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */
if ( ! function_exists( 'uwhr_site_menu' ) ) :
    function uwhr_site_menu( $return = false ) {

        $exclude_ids = get_menu_excluded_ids();

        $pages = wp_list_pages(array(
            'title_li'      => '',
            'depth'         => 3,
            'exclude'       => implode(',', $exclude_ids),
            'walker'        => new UWHR_Site_Menu_Walker,
            'echo'          => 0
        ));

        $first_li = $return ? '' : '<li class="nav-item"><a class="nav-link first" href="'. get_bloginfo('url') . '" title="Permanent Link to ' . get_bloginfo('name') . '">' . get_bloginfo('name') . '</a></li>';

        $html = sprintf( '<ul>%s%s</ul>',
            $first_li,
            $pages
        );

        if ( empty($pages) ) {

            if ( $return ) {
                return false;
            } else {
                echo '';
            }

        } else {

            $menu = '<nav class="uwhr-accordion-menu" id="pageNav" aria-label="Site Menu" tabindex="-1">' . $html . '</nav>';

            if ( $return ) {
                return $menu;
            } else {
                echo $menu;
            }

        }
    }
endif;

/**
 * Sidebar Content
 *
 * Render custom sidebar content for each page
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.4.0
 * @package UWHR
 *
 * @global $post object Current post
 */
if ( ! function_exists( 'uwhr_sidebar_content' ) ) :
    function uwhr_sidebar_content() {
        global $post;

        if ( empty( get_post_meta( $post->ID, '_uwhr_page_sidebar_content', true ) ) ) {
            return '';
        }

        $content = get_post_meta( $post->ID, '_uwhr_page_sidebar_content', true );
        $content = apply_filters( 'the_content', $content );

        $html = '<div class="widget">' . $content . '</div>';

        echo $html;
    }
endif;

/**
 * Re-writing the parent theme's breadcrumb trail to be multi-site friendly.
 *
 * @author Thomas Winston Thorpe <twthorpe@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @since 2015-02-27
 * @package UWHR
 *
 * @global $post object Current post
 * @global $UWHR Object
 *
 * @param $class string Additional classes to add to breadcrumb nav tag
 */
if ( ! function_exists( 'uwhr_breadcrumbs' ) ) :
    function uwhr_breadcrumbs( $return = false ){
        global $post;
        global $UWHR;

        $ancestors = $post ? array_reverse(get_post_ancestors($post->ID)) : '';

        $html = '<li class="breadcrumb-item"><a href="' . network_site_url() . '" title="University of Washington Human Resources"><i class="fa fa-lg fa-home"></i></a></li>';

        if( get_current_blog_id() !== $UWHR::MAIN_BLOG_ID ) {
            $html .= '<li class="breadcrumb-item' . (is_front_page() ? ' active' : '') . '"><a href="' . home_url('/') . '" title="' . get_bloginfo('title') . '">' . get_bloginfo('title') . '</a></li>';
        }

        if(is_404()){
            $html .= '<li class="breadcrumb-item active">Woof!</li>';
        }
        else if(is_search()){
            $html .= '<li class="breadcrumb-item active">Search results for \'' . get_search_query() . '\'' . '</li>';
        }
        else if(is_author()){
            $author = get_queried_object();
            $html .= '<li class="breadcrumb-item active">Author: '  . $author->display_name . '</li>';
        }
        else if(get_queried_object_id() === (int) get_option('page_for_posts')){
            $html .= '<li class="breadcrumb-item active">'. get_the_title(get_queried_object_id()) . '</li>';
        }

        if(is_category() || is_single() || is_post_type_archive()){
            if(is_post_type_archive()){
                $posttype = get_post_type_object(get_post_type());
                $html .= '<li class="breadcrumb-item active">'. $posttype->labels->menu_name  . '</li>';
            }
            if(is_category()){
                $category = get_category(get_query_var('cat'));
                $html .= '<li class="breadcrumb-item active">'. get_cat_name($category->term_id) . '</li>';
            }
            if(is_single()){
                // if(has_category()){
                //     $category = array_shift(get_the_category($post->ID));
                //     $html .= '<li><a href="' . get_category_link($category->term_id) . '" title="' . get_cat_name($category->term_id) . '">' . get_cat_name($category->term_id) . '</a></li>';
                // }
                // if(is_custom_post_type()){
                //     $posttype = get_post_type_object(get_post_type());
                //     $archive_link = get_post_type_archive_link($posttype->query_var);
                //     if(!empty($archive_link)){
                //         $html .= '<li><a href="' . $archive_link . '" title="' . $posttype->labels->menu_name . '">' . $posttype->labels->menu_name . '</a></li>';
                //     }
                //     else if (!empty($posttype->rewrite['slug'])){
                //         $html .= '<li><a href="' . site_url('/' . $posttype->rewrite['slug'] . '/') . '" title="' . $posttype->labels->menu_name . '">' . $posttype->labels->menu_name  . '</a></li>';
                //     }
                // }
                $html .= '<li class="breadcrumb-item active">' . get_the_title( $post->ID ) . '</li>';
            }
        } else if(is_page()) {
            if(!is_home() || !is_front_page()){
                $ancestors[] = $post->ID;
            }
            if(!is_front_page()){
                foreach(array_filter($ancestors) as $index=>$ancestor){
                    $class      = $index+1 == count($ancestors) ? ' active' : '';
                    $page       = get_post($ancestor);
                    $url        = get_permalink($page->ID);
                    $title_attr = esc_attr($page->post_title);
                    if(!empty($class)){
                        $html .= '<li class="breadcrumb-item'. $class.'">'.get_the_short_title($page->ID).'</li>';
                    }else{
                        $html .= '<li class="breadcrumb-item"><a href="'.$url.'" title="'.$title_attr.'">'.get_the_short_title($page->ID).'</a></li>';
                    }
                }
            }
        }

        $nav = '<nav class="uwhr-breadcrumbs" role="navigation" aria-label="Breadcrumbs"><ol class="breadcrumb">' . $html . '</ol></nav>';

        if ( $return ) {
            return $nav;
        } else {
            echo $nav;
        }
    }
endif;

/**
 * Pagination
 *
 * Prints out pagination links for viewing content
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 0.7.0
 * @package UWHR
 *
 * @param $pages int Number of pages
 * @param $range int Number of button links to show
 */
if ( ! function_exists( 'uwhr_pagination' ) ) :
    function uwhr_pagination($pages = '', $range = 1) {

        $showitems = ($range * 2) + 1;
        global $paged;

        if( empty($paged) ) $paged = 1;

        if($pages == ''){
            global $wp_query;
            $pages = $wp_query->max_num_pages;

            if(!$pages) {
                $pages = 1;
            }
        }

        if(1 != $pages) {

            echo '<nav class="text-center" role="navigation"><ul class="pagination pagination-sm">';

            if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo '<li class="page-item"><a class="page-link" href="'.get_pagenum_link(1).'" aria-label="First">&laquo;<span class="hidden-xs"> First</span></a></li>';

            if($paged > 1 && $showitems < $pages) echo '<li class="page-item"><a class="page-link" href="'.get_pagenum_link($paged - 1).'" aria-label="Previous">&lsaquo;<span class="hidden-xs"> Previous</span></a></li>';

            for ($i=1; $i <= $pages; $i++) {
                if ( 1 != $pages && ( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ) ) {

                    echo ($paged == $i)? '<li class="page-item active"><a class="page-link"><span>'.$i.' <span class="sr-only">(current)</span></span></a></li>'
                    :'<li class="page-item"><a class="page-link" href="'.get_pagenum_link($i).'">'.$i.'</a></li>';
                }
            }

            if ($paged < $pages && $showitems < $pages) {
                echo '<li class="page-item"><a class="page-link" href="'.get_pagenum_link($paged + 1).'"  aria-label="Next"><span class="hidden-xs">Next </span>&rsaquo;</a></li>';
            }
            if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) {
                echo '<li class="page-item"><a class="page-link" href="'.get_pagenum_link($pages).'" aria-label="Last"><span class="hidden-xs">Last </span>&raquo;</a></li>';
            }

            echo '</ul></nav>';
        }
    }
endif;

/**
 * Display Top Level Pages
 *
 * Grab all the top level pages not excluded from the menu structure
 *
 * Used on unit homepages.
 *
 * @see templates/template-unit-homepage.php
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.5.0
 * @package UWHR
 */
if ( ! function_exists( 'uwhr_top_level_pages' ) ) :
    function uwhr_top_level_pages() {
        $exclude_ids = get_menu_excluded_ids();

        $featured_pages = get_pages( array(
            'meta_key'     => '_uwhr_page_featured',
            'meta_value'   => 1,
        ));

        foreach ( $featured_pages as $p ) {
            $exclude_ids[] = $p->ID;
        }

        $top_level_pages = get_pages( array(
            'parent' => 0,
            'sort_column' => 'menu_order',
            'sort_order' => 'asc',
            'exclude' => implode(',', $exclude_ids),
        ));

        $html = '';
        foreach ( $top_level_pages as $page ) {
            $id = $page->ID;
            $title = get_the_title( $id );
            $image = wp_get_attachment_image_src( get_post_thumbnail_id($id), 'medium' );
            $image_focus = get_post_meta( $id, '_uwhr_page_image_focus', true );

            $url = ( empty($image) ) ? get_template_directory_uri() . '/assets/images/defaults/featured.jpg' : $image[0];

            if ( ! empty($image) ) {
                $image_focus = get_post_meta( $id, '_uwhr_page_image_focus', true );
                $image_focus_v = ( ! empty($image_focus) ) ? $image_focus['vertical'] : 'center';
                $image_focus_h = ( ! empty($image_focus) ) ? $image_focus['horizontal'] : 'center';
            } else {
                $image_focus_v = 'center';
                $image_focus_h = 'center';
            }

            $card_classes = array('card', 'card-fixed-height-xs', 'card-link', 'card-content');

            if ( has_post_format('link',$id) ) {
                $card_classes[] = 'card-external-link';
            }

            $html .= '<div class="col-md-6 col-xl-4">';

                $html .= '<a class="'.implode( ' ', $card_classes ).'" href="' . get_permalink( $id ) . '" title="' . $title . '">';

                    $html .= '<div class="card-img" style="background-image: url(' . $url . '); background-position: ' . esc_attr($image_focus_v)  . ' ' . esc_attr($image_focus_h) . ' ">';
                    $html .= '</div>';

                    $html .= '<div class="card-img-overlay-1">';
                        $html .= '<h3 class="card-title beefy white text-uppercase m-a-0">' . $title . '</h3>';
                        $html .= '<span class="slant xs short"></span>';
                    $html .= '</div>';

                    $excerpt = uwhr_get_the_excerpt( $id );

                    $html .= '<div class="card-img-overlay-2">';
                        $html .= '<p class="heading bold m-a-0">' . $excerpt . '</p>';
                        $html .= '<p class="text-xs-center"><i class="fa fa-arrow-circle-o-right fa-'.(($excerpt) ? 'lg' : '3x').'"></i></p>';
                    $html .= '</div>';

                $html .= '</a>';

            $html .= '</div>'; // Close .col-md-* tag
        }

        if ( has_featured_pages() ) {
            $html .= fill_unit_menu_grid( count($top_level_pages) );
        }

        echo $html;
    }
endif;

/**
 * Display Unit Menu
 *
 * If a unit site has a Unit Menu set, loop over all the items,
 * pull out any references to non-WP pages, and then display those pages.
 *
 * Used on unit homepages.
 *
 * @see templates/template-unit-homepage.php
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 0.9.0
 * @package UWHR
 */
if ( ! function_exists( 'uwhr_unit_menu' ) ) :
    function uwhr_unit_menu() {
        $unit_menu_items = wp_get_nav_menu_items( 'unit-menu' );

        $pages = array();
        foreach ( $unit_menu_items as $item ) {
            if ( $item->object == 'page' ) {
                $page['ID'] = $item->object_id;
                $page['title'] = $item->title;
                $pages[] = (object) $page;
            }
        }

        $html = '';
        foreach ( $pages as $page ) {
            $id = $page->ID;
            $title = $page->title;
            $image = wp_get_attachment_image_src( get_post_thumbnail_id($id), 'medium' );

            $url = ( empty($image) ) ? get_template_directory_uri() . '/assets/images/defaults/featured.jpg' : $image[0];

            $card_classes = array('card', 'card-fixed-height-xs', 'card-link', 'card-content');

            if ( has_post_format('link',$id) ) {
                $card_classes[] = 'card-external-link';
            }

            $html .= '<div class="col-md-6 col-xl-4">';

                $html .= '<a class="'.implode( ' ', $card_classes ).'" href="' . get_permalink( $id ) . '" title="' . $title . '">';

                    $html .= '<div class="card-img" style="background-image: url(' . $url . ');">';
                    $html .= '</div>';

                    $html .= '<div class="card-img-overlay-1">';
                        $html .= '<h3 class="card-title beefy white text-uppercase m-a-0">' . $title . '</h3>';
                        $html .= '<span class="slant xs short"></span>';
                    $html .= '</div>';

                    $excerpt = uwhr_get_the_excerpt( $id );

                    $html .= '<div class="card-img-overlay-2">';
                        $html .= '<p class="heading bold m-a-0">' . $excerpt . '</p>';
                        $html .= '<p class="text-xs-center"><i class="fa fa-arrow-circle-o-right fa-'.(($excerpt) ? 'lg' : '3x').'"></i></p>';
                    $html .= '</div>';

                $html .= '</a>';

            $html .= '</div>'; // Close .col-md-* tag
        }

        if ( has_featured_pages() ) {
            $html .= fill_unit_menu_grid( count($pages) );
        }

        echo $html;
    }
endif;

/**
 * Fill Unit Homepage Grid
 *
 * In order to always have a full grid, this function returns some html
 * of unit meta information to fill up the final row of the grid
 *
 * Used on unit homepages.
 *
 * @see templates/template-unit-homepage.php
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 0.9.0
 * @package UWHR
 *
 * @param $count int The remaining number of spaces in the grid
 *
 * @return $html string The filler grid elements
 */
if ( ! function_exists( 'fill_unit_menu_grid' ) ) :
    function fill_unit_menu_grid( $count ) {
        // Unit Meta Info array data storage
        // Will contain array elements with strings of html to be rendered
        $meta_info = array();

        // Check to see if there's a calendar template
        $cal_pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => 'templates/template-calendar.php'
        ));

        // Build the calendar card if needed
        if ( ! empty( get_option('unit_settings')['calendar'] ) AND $cal_pages ) {
            $cal = '<div class="col-md-6 col-xl-4">';
                $cal .= '<div class="card card-fixed-height-xs">';

                    $cal .= '<div class="card-overlay bg-lightgold text-xs-center">';
                        $cal .= '<p class="gold m-a-0"><i class="fa fa-calendar fa-3x"></i></p>';
                            $cal .= '<p class="gold bold text-lg heading m-a-0"><a class="gold" href="'.get_permalink( $cal_pages[0]->ID ).'">View Event Calendar</a></p>';
                    $cal .= '</div>';

                $cal .= '</div>';
            $cal .= '</div>';

            $meta_info[] = $cal;
        }

        // Check to see if there's a news feed template
        $news_pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => 'templates/template-news-feed.php'
        ));

        // Build the News card if needed
        if ($news_pages) {

            $news = '<div class="col-md-6 col-xl-4">';
                $news .= '<div class="card card-fixed-height-xs">';

                    $news .= '<div class="card-overlay bg-lightgold text-xs-center">';
                            $news .= '<p class="gold m-a-0"><i class="fa fa-newspaper-o fa-3x"></i></p>';
                            $news .= '<p class="gold bold text-lg heading m-a-0"><a class="gold" href="' . get_permalink( $news_pages[0]->ID ) . '">News</a></p>';
                    $news .= '</div>';

                $news .= '</div>';
            $news .= '</div>';

            $meta_info[] = $news;
        }

        // Grab stuff if the contacts plugin is installed
        if ( function_exists( 'uwhr_contacts_get_unit_meta' ) ) {
            $site_id = get_current_blog_id();
            $phone = uwhr_contacts_get_unit_meta( $site_id, 'phone');
            $fax = uwhr_contacts_get_unit_meta( $site_id, 'fax');
            $email =  uwhr_contacts_get_unit_meta( $site_id, 'email');
        } else {
            $phone = $email = '';
        }

        // Add the contacts info if it's filled in
        if ( ! empty($phone) OR ! empty($email) ) {

            $contact = '<div class="col-md-6 col-xl-4">';
                $contact .= '<div class="card card-fixed-height-xs">';

                    $contact .= '<div class="card-overlay bg-lightgold text-xs-center">';

                        $contact .= '<p class="gold m-a-0"><i class="fa fa-comment-o fa-3x"></i></p>';

                        if ( ! empty($email) ) {
                            $contact .= '<p class="gold bold text-lg heading m-a-0"><a class="gold" href="mailto:' . $email . '">' . $email . '</a></p>';
                        }
                        if ( ! empty($phone) ) {
                            $contact .= '<p class="gold bold text-lg heading m-a-0">' . $phone . '</p>';
                        }
                        if ( ! empty($fax) ) {
                            $contact .= '<p class="gold bold text-lg heading m-a-0">' . $fax . ' (Fax)</p>';
                        }
                    $contact .= '</div>';

                $contact .= '</div>';
            $contact .= '</div>';

            $meta_info[] = $contact;
        }

        $html = '';

        if ( $count % 3 === 1 ) {
            if (isset($meta_info[0])) {
                $html .= $meta_info[0];
            }

            if ( count($meta_info) >= 2 ) {
                $html .= $meta_info[1];
            }
        }

        if ( $count % 3 === 2 ) {
            if (isset($meta_info[0])) {
                $html .= $meta_info[0];
            }
        }

        return $html;
    }
endif;

/**
 * Display Unit Featured Pages
 *
 * Grab all the featured pages from the site and display in a fluid width container
 *
 * Used on unit homepages.
 *
 * @see templates/template-unit-homepage.php
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.5.0
 * @package UWHR
 */
if ( ! function_exists( 'uwhr_unit_featured_pages' ) ) :
    function uwhr_unit_featured_pages() {

        $args = array(
            'post_type' => 'page',
            'meta_query' => array(
                array(
                    'key' => '_uwhr_page_featured',
                    'value' => 1
                )
            ),
            'meta_key' => '_uwhr_page_featured_order',
            'orderby' => 'meta_value',
            'order' => 'ASC'
        );

        $the_query = new WP_Query( $args );

        if ( $the_query->have_posts() ) {

            $index = 0;
            $col_width = ( $the_query->found_posts >= 4 ) ? 3 : 4;
            $card_size = ( $the_query->found_posts >= 4 ) ? 'xl' : 'lg';

            $html = '';

            $html .= '<div class="row p-y-lg featured-section">';

                $html .= '<div class="container">';
                    $html .= '<div class="row">';
                        $html .= '<div class="col-xs-12">';
                            $html .= '<p class="h1 white beefy">Featured</p>';
                        $html .= '</div>';
                    $html .= '</div>';
                    $html .= '<div class="row">';

                    while ( $the_query->have_posts() ) {
                        $the_query->the_post();

                        if ( $index == 4 ) {
                            continue;
                        }

                        global $post;

                        $id = $post->ID;

                        $quick_link_text = get_post_meta( $id, '_uwhr_page_quick_link_text', true )
                                         ? get_post_meta( $id, '_uwhr_page_quick_link_text', true )
                                         : 'Explore';

                        $html .= '<div class="' . ( ($col_width == 3) ? 'col-lg-6 col-xl-3': 'col-lg-4' ) . '">';
                            $html .= '<article class="card card-light card-hide-text card-fixed-height-' . $card_size . '">';

                                $html .= get_the_post_thumbnail( $id, 'medium', array( 'class' => 'card-img-top' ) );

                                $html .= '<div class="card-block">';
                                    $html .= '<h3 class="card-title beefy purple"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
                                    $html .= '<p class="card-text">' . get_the_excerpt() . '</p>';
                                $html .= '</div>';

                                $html .= '<div class="card-block card-block-last">';
                                    $html .= '<a class="uw-btn btn-sm btn-white m-a-0 card-block-item-last" href="' . get_permalink() . '" title="Link to ' . esc_html( $quick_link_text ) . ', ' . get_the_title() . '">' . esc_html( $quick_link_text ) . '</a>';
                                $html .= '</div>';

                            $html .= '</article>';
                        $html .= '</div>';

                        $index++;
                    }

                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';

            echo $html;

            wp_reset_postdata();
        }
    }
endif;

/**
 * Display Unit Meta Information
 *
 * If available, display unit calendar, News Feed, and contact information.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 0.8.0
 * @package UWHR
 */
if ( ! function_exists( 'uwhr_unit_meta' ) ) :
    function uwhr_unit_meta() {

        $html = '';

        $cal_pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => 'templates/template-calendar.php'
        ));

        if ( ! empty( get_option('unit_settings')['calendar'] ) AND $cal_pages ) {

            $html .= '<div class="row">';
                $html .= '<div class="bg-lightgold m-x p-a-md clearfix text-xs-center">';
                    $html .= '<div class="col-xs-12 col-sm-3 col-md-12 col-lg-3">';
                        $html .= '<p class="gold m-a-0"><i class="fa fa-calendar fa-3x"></i></p>';
                    $html .= '</div>';
                    $html .= '<div class="col-xs-12 col-sm-9 col-md-12 col-lg-9">';
                        $html .= '<p class="gold bold text-lg heading m-a-0"><a class="gold" href="' . get_permalink( $cal_pages[0]->ID ) . '">View Event Calendar</a></p>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';

        }

        $news_pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => 'templates/template-news-feed.php'
        ));

        if ($news_pages) {

            $html .= '<div class="row m-y">';
                $html .= '<div class="bg-lightgold m-x p-a-md clearfix text-xs-center">';
                    $html .= '<div class="col-xs-12 col-sm-3 col-md-12 col-lg-3">';
                        $html .= '<p class="gold m-a-0"><i class="fa fa-newspaper-o fa-3x"></i></p>';
                    $html .= '</div>';
                    $html .= '<div class="col-xs-12 col-sm-9 col-md-12 col-lg-9">';
                        $html .= '<p class="gold bold text-lg heading m-a-0"><a class="gold" href="' . get_permalink( $news_pages[0]->ID ) . '">News</a></p>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';

        }

        // Grab stuff if the contacts plugin is installed
        if ( function_exists( 'uwhr_contacts_get_unit_meta' ) ) {
            $site_id = get_current_blog_id();
            $phone = uwhr_contacts_get_unit_meta( $site_id, 'phone');
            $fax =  uwhr_contacts_get_unit_meta( $site_id, 'fax');
            $email =  uwhr_contacts_get_unit_meta( $site_id, 'email');
        } else {
            $phone = $email = '';
        }

        if ( ! empty($phone) OR ! empty($email) ) {

            $html .= '<div class="row m-y">';
                $html .= '<div class="bg-lightgold m-x p-a-md clearfix text-xs-center">';
                    $html .= '<div class="col-xs-12 col-sm-3 col-md-12 col-lg-3">';
                        $html .= '<p class="gold m-a-0"><i class="fa fa-comment-o fa-3x"></i></p>';
                    $html .= '</div>';
                    $html .= '<div class="col-xs-12 col-sm-9 col-md-12 col-lg-9">';

                        if ( ! empty($email) ) {
                            $html .= '<p class="gold bold text-lg heading m-a-0"><a class="gold" href="mailto:' . $email . '">' . $email . '</a></p>';
                        }

                        if ( ! empty($phone) ) {
                            $html .= '<p class="gold bold text-lg heading m-a-0">' . $phone . '</p>';
                        }

                        if ( ! empty($fax) ) {
                            $html .= '<p class="gold bold text-lg heading m-a-0">' . $fax . ' (Fax)</p>';
                        }

                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';

        }

        echo $html;
    }
endif;

/**
 * Get the Excerpt
 *
 * Grab the page excerpt or return a blank string.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.4.0
 * @package UWHR
 *
 * @return array $IDs An array of all the IDs in the multisite install
 */
if ( ! function_exists( 'uwhr_get_the_excerpt' ) ) :
    function uwhr_get_the_excerpt( $id ) {
        $page = get_post( $id );
        if ( empty( $page->post_excerpt ) ) {
            return '';
        } else {
            return apply_filters( 'get_the_excerpt', $page->post_excerpt );
        }
    }
endif;

/**
 * Echo out page anchor linking
 *
 * If a page has anchor linking turned on, each stored page nav link is rendered
 * to the page including an href to every heading's id
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.2.0
 * @package UWHR
 *
 * @global $post
 */
if ( ! function_exists( 'uwhr_toc' ) ) :
    function uwhr_toc() {
        global $post;

        if ( ! get_post_meta( $post->ID, '_uwhr_page_anchor_linking_active', true ) ) {
            return;
        }

        $visible = 5;

        $html = '<div class="uwhr-toc" id="toc"><p class="h4 beefy gold">Table of Contents</p><span class="slant xs short gold"></span>';

        $no_more_btn = ( get_post_meta( $post->ID, '_uwhr_page_anchor_linking_more_btn', true ) ) ? $data = get_post_meta( $post->ID, '_uwhr_page_anchor_linking_more_btn', true ) : false;
        $links = get_post_meta( $post->ID, '_uwhr_page_anchor_links', true );

        $i = 0;
        $count = count($links);
        foreach ( $links as $slug => $heading ) {
            $html .='<p class="h6 m-a-0 thin"><a class="toc-link" href="#' . $slug . '">' . $heading . '</a></p>';
            $i++;
            if ( $i == ( $visible ) AND $count > $visible AND ! $no_more_btn ) {
                $html .= '<div class="collapse" id="tocCollapse">';
            }
        }

        if ( $count > $visible AND ! $no_more_btn ) {
            $html .= '</div>';
            $html .= '<div class="m-b-md m-t"><a class="btn btn-success btn-sm" id="tocBtn" data-toggle="collapse" href="#tocCollapse" aria-expanded="false" aria-controls="tocCollapse">View All</a></div>';
        }

        $html .= '</div>';

        echo $html;
    }
endif;
