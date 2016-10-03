<?php

/**
 * Single Card - Template
 *
 * Template used for reviewing single cards, mostly for content authors
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 1.0
 * @package UWHR-root
 */

get_header();

get_template_part( 'partials/hero', 'slim' );

?>

<section class="uwhr-body container">
    <div class="row">

        <div class="col-lg-8">
            <h2 class="sr-only">HR Quick Links</h2>
            <?php uwhr_homepage_cards(); ?>
        </div>

        <aside class="col-lg-4 p-l-lg" role="complementary">
            <?php
                while ( have_posts() ) : the_post();
                    $title = get_the_title();
                    edit_post_link( 'Return to editing the ' . $title . ' card' );
                endwhile;
            ?>
        </aside>

    </div>
</section>

<?php get_footer();
