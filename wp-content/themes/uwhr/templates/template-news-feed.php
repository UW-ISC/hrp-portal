<?php
/**
 * Template Name: News Feed
 *
 * Template with a custom query for posts tagged News.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 0.6.0
 * @package UWHR
 */

get_header();

$no_sidebar = get_post_meta( $post->ID, '_uwhr_page_no_sidebar', true ) ? get_post_meta( $post->ID, '_uwhr_page_no_sidebar', true ) : 0;

get_template_part( 'partials/hero', 'normal' ); ?>

<section class="uwhr-body container">
    <div class="row">

        <?php uwhr_breadcrumbs(); ?>

        <article class="uwhr-content col-lg-<?php echo ( ( $no_sidebar == 0 ) ? 6 : 9 ); ?> push-lg-3">

            <?php
                while ( have_posts() ) : the_post();
                    the_title( '<h3 class="title">', '</h3>' );
                    the_content();
                endwhile;

                wp_reset_postdata();

                $args = array(
                    'post_type' => 'post',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'category',
                            'field'    => 'slug',
                            'terms'    => 'news',
                        ),
                    ),
                );
                $query = new WP_Query( $args );

                if ( $query->have_posts() ) {
                    while ( $query->have_posts() ) {
                        $query->the_post();

                        echo '<h4 class="purple bold m-t-md"><a href="' . get_permalink() . '"> ' . get_the_title() . ' <small>' . get_the_date() . '</small></a></h4>';

                        the_excerpt();

                        echo '<div class="text-right"><a class="uw-btn btn-sm text-uppercase" href="' . get_permalink() . '">Read More</a></div>';

                        echo '<hr>';
                    }
                }

                wp_reset_postdata();
            ?>

        </article>

        <?php
            if ( $no_sidebar == 0 ) {
                get_sidebar();
            }

            uwhr_site_menu();
        ?>

    </div>
</section>

<?php get_footer();
