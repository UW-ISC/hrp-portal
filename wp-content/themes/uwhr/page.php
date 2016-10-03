<?php
/**
 * Page - Template
 *
 * Template used whenever viewing a single post type
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
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
                    uwhr_toc();
                    the_content();
                    edit_post_link();
                endwhile;
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
