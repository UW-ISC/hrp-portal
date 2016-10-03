<?php

/**
 * Page - A-Z Index
 *
 * Echo out every top-level page and its children from every site in the multisite installation
 *
 * $siteIDs is the list of site IDs to index
 * $excluded_page_titles allows a dev to exclude certain pages by their title
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 1.0
 * @package UWHR-root
 */

get_header();

get_template_part( 'partials/hero', 'normal' );

?>

<section class="uwhr-body container">
    <div class="row">

        <?php uwhr_breadcrumbs(); ?>

        <article class="uwhr-content col-lg-12">

            <?php
                $siteIDs = array(1,2,3,4,5,6,7,8,9,10,12,13);
                $excluded_page_titles = array('Download');

                if ( empty($siteIDs) ) {
                    $allSites = wp_get_sites();
                    foreach($allSites as $s) {
                        $siteIDs[] = $s['blog_id'];
                    }
                }

                $allPages = array();

                foreach ( $siteIDs as $siteID ) {
                    switch_to_blog( $siteID );

                    // Build out the excluded pages ID array
                    $skip_page_ids = get_menu_excluded_ids();
                    foreach( $excluded_page_titles as $exclude_title ) {
                        if ( ! empty( get_page_by_title( $exclude_title ) ) ) {
                            $exclude_page = get_page_by_title( $exclude_title );
                            $skip_page_ids[] = $exclude_page->ID;
                        }
                    }

                    $pages = get_pages( array(
                        'hierarchical'  => 0, // False hierarchical to print out all pages alphabetically
                        'exclude'       => $skip_page_ids,
                        'parent'        => 0, // Returns only top level pages
                        'sort_column'   => 'title',
                        'sort_order'    => 'ASC'
                    ));

                    foreach( $pages as $page ) {
                        $id = $page->ID;
                        $parent_title = $page->post_title;
                        $site_title = get_bloginfo('title');

                        $item = array();
                        $item['url'] = get_permalink( $id );
                        $item['title'] = $parent_title;
                        $item['site'] = $site_title;

                        $allPages[] = $item;

                        $pages = get_pages( array(
                            'hierarchical'  => 0,
                            'parent'        => $id,
                            'sort_column'   => 'title',
                            'sort_order'    => 'ASC'
                        ));

                        foreach( $pages as $page ) {
                            $item = array();
                            $item['url'] = get_permalink( $page->ID );
                            $item['title'] = $page->post_title;
                            $item['site'] = $site_title;

                            $allPages[] = $item;
                        }
                    }

                    #restore_current_blog();
                }

                function cmp($a, $b) {
                    return strcmp($a['title'], $b['title']);
                }

                usort( $allPages, 'cmp' );

                $html = $nav = $char = '';
                $chars = array();

                $html .= '<div class="row">';
                    $html .= '<div class="col-md-6">';

                    $i = 0;
                    $count = count($allPages);
                    $secondCol = false;
                    $azRange = range('A', 'Z');
                    array_unshift($azRange , '#');

                    foreach( $allPages as $page ) {
                        $c = substr( $page['title'], 0, 1);

                        if ( ! $secondCol ) {
                            if ( $i > ($count / 2) AND $c != $char ) {
                                $html .= '</div>';
                                $html .= '<div class="col-md-6">';
                                $secondCol = true;
                            }
                        }

                        if ( is_numeric($c) ) {
                            $c = '#';
                        }

                        if ( $c != $char ) {
                            $html .= '<p class="h3 text-uppercase m-t-3" id="uwhr-az-' . $c . '">' . $c . '</p>';

                            $html .= '<span class="slant gold sm short"></span>';
                            $char = $c;
                            $chars[] = $char;
                        }
                        $html .= '<h3 class="h6"><a href="' . $page['url'] . '">' . $page['title'] . '</a></h3>';

                        $i++;
                    }

                    $html .= '</div>';
                $html .= '</div>';

                $nav .= '<p class="h4">';
                foreach( $azRange as $c ) {
                    if ( in_array( $c, $chars ) ) {
                        $nav .= '<a class="m-r no-bb display-inline-block" href="#uwhr-az-'.$c.'">'.$c.'</a>';
                    } else {
                        $nav .= '<span class="m-r display-inline-block gray-lighter">'.$c.'</span>';
                    }
                }
                $nav .= '</p>';

                echo $nav . $html;
            ?>

        </article>

    </div>
</section>

<?php get_footer();
