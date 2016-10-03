<?php

/**
 * Archive - Template
 *
 * Template used for archives
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.3.0
 * @package UWHR
 */

get_header();

$url = get_image_url_by_size( $post->ID, 'pano-small' );

?>

<section class="uwhr-hero uwhr-hero-image hero-image-height" style="background-image: url(<?php echo $url ?>);">
    <div class="row">
        <div class="container">
            <h2 class="hero-title"><?php post_type_archive_title(); ?> Archives</h2>
        </div>
    </div>
</section>

<section class="uwhr-body container">
    <div class="row">

        <?php uwhr_breadcrumbs(); ?>

        <article class="uwhr-content col-lg-9 push-lg-3">

            <?php
                while ( have_posts() ) : the_post();
                    the_title( '<h3>', '</h3>' );
                    echo '<p>Posted on ' . get_the_date() . '</p>';
                    the_excerpt();
                endwhile;
            ?>

        </article>

        <?php uwhr_site_menu(); ?>

    </div>
</section>

<?php get_footer();
