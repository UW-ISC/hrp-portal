<?php

/**
 * Homepage
 *
 * This is the main homepage of UWHR.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 1.0
 * @package UWHR-root
 */

get_header();

?>

<section class="uwhr-slideshow hero-image-height-lg">
    <div class="row">
        <h2 class="sr-only">Featured Item Slideshow</h2>
        <?php uwhr_homepage_slideshow(); ?>
    </div>
</section>

<section class="uwhr-body container">
    <div class="row">

        <div class="uwhr-quicklinks col-lg-8">
            <h2 class="sr-only">HR Quick Links</h2>
            <?php uwhr_homepage_cards(); ?>
        </div>

        <aside class="col-lg-4 p-l-lg">
            <?php uwhr_display_calendar_feed( 5 ); ?>
            <?php uwhr_display_twitter_feed( 1 ); ?>
        </aside>

    </div>
</section>

<?php get_footer();
