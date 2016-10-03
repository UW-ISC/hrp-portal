<?php

/**
 * 404 - Template
 *
 * Template used when a user hits a 404 error
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

get_header();

?>

<section class="container-fluid bg-purple">
    <div class="row">
        <div class="offset-xs-1 col-xs-10 offset-sm-2 col-sm-8 offset-md-3 col-md-7 offset-lg-3 col-lg-6">
            <img style="width: 100%;" src="<?php echo get_template_directory_uri() ?>/assets/images/404.png" alt="Page Not Found">
        </div>
    </div>
</section>

<section class="uwhr-body container p-t-3">
    <div class="row">
        <article class="uwhr-content col-lg-12">
            <div class="row m-b">
                <div class="col-lg-8 offset-lg-2">
                    <?php global $UWHR; $UWHR->Search->UI->render_search_form('large'); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-5 offset-lg-2">
                    <p class="h3">Dubs got to this page before you!</p>
                    <p>Don’t worry. Here are some of Dubs' other favorite pages if you feel like exploring:</p>
                </div>
                <div class="col-lg-3">
                    <ul>
                        <li><a href="<?php echo network_site_url(); ?>">Human Resources Home</a></li>
                        <li><a href="<?php echo network_site_url(); ?>benefits">Benefits</a></li>
                        <li><a href="<?php echo network_site_url(); ?>policies">Policies</a></li>
                        <li><a href="//www.washington.edu/safecampus/">SafeCampus</a></li>
                        <li><a href="//www.washington.edu/wholeu/">The Whole U</a></li>
                    </ul>
                </div>
            </div>

        </article>
    </div>
</section>

<?php get_footer();
