<?php

/**
 * Single Neo- Template
 *
 * Template used whenever viewing a single post type
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

get_header();

get_template_part( 'partials/hero', 'normal' );

?>

<section class="uwhr-body container">
    <div class="row">

        <?php uwhr_breadcrumbs(); ?>

        <article class="uwhr-content col-lg-9 push-lg-3">

            <?php
                while ( have_posts() ) : the_post();
                    the_title( '<h3 class="title">', '</h3>' );
                    the_content();
                    edit_post_link();
                endwhile;
            ?>

        </article>

        <?php uwhr_site_menu(); ?>

    </div>
</section>

<?php get_footer();
