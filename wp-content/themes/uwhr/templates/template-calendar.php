<?php
/**
 * Template Name: Calendar
 *
 * Template containing a Trumba calendar using their spud system. Template requires
 * a calendar name to be set in the Unit Settings page.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 0.9.0
 * @package UWHR
 */

get_header();

get_template_part( 'partials/hero', 'normal' ); ?>

<section class="uwhr-body container">
    <div class="row">

        <?php uwhr_breadcrumbs(); ?>

        <article class="uwhr-content col-lg-12">

            <?php
                while ( have_posts() ) : the_post();
                    the_title( '<h3 class="title">', '</h3>' );
                    the_content();

                    $webName = ( ! empty( get_option('unit_settings')['calendar'] ) ) ? get_option('unit_settings')['calendar'] : ''; ?>

                    <script type="text/javascript">
                        $Trumba.addSpud({
                            webName: "<?php echo esc_html($webName); ?>",
                            spudType: "main"
                        });
                    </script>

            <?php endwhile; ?>

        </article>

    </div>
</section>

<?php get_footer();
