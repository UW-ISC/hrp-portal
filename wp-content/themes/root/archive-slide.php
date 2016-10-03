<?php

/**
 * Archive Slide - Template
 *
 * Template used for form slides
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.3.0
 * @package UWHR-roo
 */

get_header();

$url = get_image_url_by_size( $post->ID, 'pano-small' );

?>

<section class="uwhr-hero uwhr-hero-image hero-image-height" style="background-image: url(<?php echo $url ?>);">
    <div class="row">
        <div class="container">
            <h2 class="hero-title">Slides</h2>
        </div>
    </div>
</section>

<section class="uwhr-body container">
    <div class="row">

        <?php uwhr_breadcrumbs(); ?>

        <article class="uwhr-content col-lg-12">

            <?php
                while ( have_posts() ) : the_post();
                	echo '<div class="col-md-6">';
	                    the_title('<h3>', '</h3>');
	                    $url = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID), 'pano-large' );
	                    echo '<img src="' . $url[0] . '">';
	                    echo get_post_meta( $post->ID, '_uwhr_slide_text', true );
	                    edit_post_link();
                    echo '</div>';
                endwhile;
            ?>

        </article>

    </div>
</section>

<?php get_footer();


