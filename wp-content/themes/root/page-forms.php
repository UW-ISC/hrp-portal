<?php

/**
 * Page - Forms
 *
 * Template to display all uploaded Forms to each unit site.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
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

                $siteIDs = get_all_site_ids();

                $allForms = array();

                foreach ( $siteIDs as $siteID ) {
                    switch_to_blog( $siteID );

                    $args = array(
                        'post_type' => 'form',
                        'orderby'   => 'title',
                        'order'     => 'ASC',
                        'posts_per_page' => 100
                    );

                    $the_query = new WP_Query( $args );

                    if ( $the_query->have_posts() ) {
                        while ( $the_query->have_posts() ) {
                            $the_query->the_post();

                            $item = array();

                            $id = get_the_id();

                            $item['id'] = $id;
                            $item['file'] = get_form_file( $id );
                            $item['post_title'] = get_the_title();
                            $item['description'] = get_post_meta( $id, '_uwhr_form_description', true );
                            $item['site'] = get_bloginfo('title');

                            $allForms[] = $item;
                        }
                    }

                    wp_reset_query();

                    #restore_current_blog();
                }

                function cmp($a, $b) {
                    return strcmp($a['post_title'], $b['post_title']);
                }

                usort( $allForms, 'cmp' );

                $html = $nav = $char = '';
                $chars = array();

                $html .= '<div class="row">';
                    $html .= '<div class="col-md-6">';

                    $i = 0;
                    $count = count($allForms);
                    $secondCol = false;
                    $azRange = range('A', 'Z');
                    array_unshift($azRange , '#');

                    foreach( $allForms as $form ) {
                        $c = substr( $form['post_title'], 0, 1);

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

                        $html .= '<h3 class="h6">';
                            $html .= '<a href="' . $form['file']['url'] . '">' . $form['post_title'] . ' ';
                                if ( isset( $form['file']['mime_type'] ) ) {
                                    $html .= uwhr_mime_type_format($form['file']['mime_type'],'small');
                                }
                            $html .= '</a>';
                            if ( ! empty($form['description']) ) {
                                $html .= '<small> - ' . $form['description'] . '</small>';
                            }
                        $html .= '</h3>';

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
