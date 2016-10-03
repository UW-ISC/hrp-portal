<?php
/**
 * Template Name: Unit Homepage
 *
 * The template for each unit's homepage.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.4.0
 * @package UWHR
 */

get_header();

get_template_part( 'partials/hero', 'normal' );

?>

<section class="uwhr-body">
    <div class="container">
        <div class="row p-b-2">

            <?php uwhr_breadcrumbs(); ?>

            <div class="uwhr-unit-menu">
                <div class="row">
                    <?php if ( has_nav_menu( 'unit-menu' ) ) {
                        uwhr_unit_menu();
                    } else {
                        uwhr_top_level_pages();
                    } ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ( has_featured_pages() ) { ?>
        <div class="uwhr-unit-featured-pages">
            <?php uwhr_unit_featured_pages(); ?>
        </div>
    <?php } else {
        get_template_part( 'partials/hero', 'slim' );
    } ?>

    <div class="uwhr-unit-content">
        <div class="row p-t-3">
            <div class="col-md-8">

                <?php
                    while ( have_posts() ) : the_post();
                        the_title( '<p class="h2 beefy text-uppercase">', '</p>' );
                        the_content();
                    endwhile;
                ?>

            </div>

            <aside class="col-md-4">
                <?php uwhr_unit_meta(); ?>
            </aside>

        </div>
    </div>
</section>

<?php get_footer();
