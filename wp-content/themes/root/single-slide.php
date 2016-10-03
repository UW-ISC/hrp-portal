<?php

/**
 * Single Slide - Template
 *
 * Template used for reviewing single slides, mostly for content authors
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 1.0
 * @package UWHR-root
 */

get_header();

?>

<section class="uwhr-slideshow hero-image-height-lg container-fluid" aria-labelledby="featured-item-slideshow">
    <div class="row">
        <h2 class="text-hide" id="featured-item-slideshow">Featured Item Slideshow</h2>

        <?php uwhr_homepage_slideshow(); ?>

    </div>
</section>

<section class="uwhr-body container">
    <div class="row">

        <?php uwhr_breadcrumbs(); ?>

        <article class="uwhr-content col-lg-12">

            <?php
                while ( have_posts() ) : the_post();
                    $title = get_the_title();
                    edit_post_link( 'Return to editing the ' . $title . ' slide' );

                endwhile;
            ?>

        </article>

    </div>
</section>

<?php get_footer();
