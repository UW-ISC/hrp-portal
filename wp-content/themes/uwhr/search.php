<?php

/**
 * Search Results
 *
 * This page is rendered when a user hits enter in the search form. The template
 * calls a search results function in the UWHR_Search class which performs the
 * logic for the search
 *
 * @see class/class.uwhr-search.php
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.2.0
 * @package UWHR
 *
 * @global $UWHR;
 */

global $UWHR;
$UWHR->Search->convert_post_to_get_variables();

get_header();

$url = get_image_url_by_size( 14, 'pano-small' );

?>

<section class="uwhr-hero uwhr-hero-image hero-image-height center-v" style="background-image: url(<?php echo $url ?>);">
    <div class="row">
        <div class="container">
            <h2 class="hero-title">Search Results: <?php echo get_search_query(); ?></h2>
        </div>
    </div>
</section>

<section class="uwhr-body container">
    <div class="row">

        <?php uwhr_breadcrumbs(); ?>

        <article class="col-lg-12">
            <?php $UWHR->Search->UI->search_ui(); ?>
        </article>

    </div>
</section>

<?php get_footer();
