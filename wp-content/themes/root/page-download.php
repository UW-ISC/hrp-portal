<?php

/**
 * Page - Download
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 1.0
 * @package UWHR-root
 */

$is_logged_in = is_user_logged_in();

if ( $is_logged_in ) {
    wp_logout();
}

get_header();

get_template_part( 'partials/hero', 'normal' );

?>

<section class="uwhr-body container">
    <div class="row">

        <?php uwhr_breadcrumbs(); ?>

        <article class="uwhr-content col-lg-12">

            <?php if ( $is_logged_in ) { ?>
                <div class="alert alert-danger" role="alert">
                    <strong>Warning!</strong> In order to get you these templates, we had to log you out of WordPress. <a href="<?php echo wp_login_url(); ?>" title="Login">Log Back In</a>
                </div>
            <?php } ?>

            <div class="row">
                <div class="col-md-4">
                    <h3>Base Template</h3>
                    <p>If this is your first time visiting this page, you'll probably want to start with this base template. The template includes everything from the root <code>html</code> to each menu and overall site structure. <a href="<?php echo network_site_url(); ?>static/template/template.php">Example</a>.</p>
                    <p>Your content can go inside the <code>article.uwhr-content</code>. Append sibling elements and adjust the <code>.col-*-*</code> class to change the width of the <code>article</code>.</p>
                    <p><a id="templateBtn" href="?get=template" class="btn btn-gold btn-sm">Get Template</a></p>
                </div>

                <div class="col-md-4">
                    <h3>Header</h3>
                    <p>This header should be inserted into the top of a UWHR page. The header includes the global bar with quick links and search bar, the main header with logo and dropdown menu, and the mobile menu and toggle.</p>
                    <p>Replace entire <code>.uwhr-headers</code> when updating.</p>
                    <p><a id="headerBtn" href="?get=header" class="btn btn-gold btn-sm">Get Header</a></p>
                </div>

                <div class="col-md-4">
                    <h3>Footer</h3>
                    <p>This is a simple UWHR footer which includes quick links menu and links to UW pages.</p>
                    <p>Replace entire <code>footer</code> when updating.</p>
                    <p><a id="footerBtn" href="?get=footer" class="btn btn-gold btn-sm">Get Footer</a></p>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <h3>Assets</h3>
                    <p>You are able to download assets needed for any UWHR web project. By default, the styles and scripts should be placed in an assets directory. And the fonts should be placed in a fonts directory in the assets directory.</p>
                    <p>The main CSS file includes UWHR styles built from a base Bootstrap 4 stylesheet. Bootstrap 4 classes remain available. Although <a href="https://bitbucket.org/uwhrmarcom/theme-uwhr/src/92aeff0fd988d85d247c88906c6819bce98c8639/assets/sass/main.scss?at=master&fileviewer=file-view-default">some components were left out</a>.</p>
                    <p>The main JS file includes all supporting scripts for any UWHR web component.</p>
                    <p>The vendor JS file includes jQuery, Backbone, Underscore and <a href="https://bitbucket.org/uwhrmarcom/theme-uwhr/src/92aeff0fd988d85d247c88906c6819bce98c8639/bower.json?fileviewer=file-view-default">more</a>.</p>
                    <p>In addition to the <a href="https://www.washington.edu/brand/graphic-elements/font-download/">UW brand fonts</a>, you will also need <a href="https://fortawesome.github.io/Font-Awesome/">Font Awesome</a> fonts.</p>
                    <p>
                        <a href="<?php echo get_template_directory_uri(); ?>/assets/main.min.css" class="btn btn-purple btn-sm">Get Styles</a>
                        <a href="<?php echo get_template_directory_uri(); ?>/assets/main.min.js" class="btn btn-purple btn-sm">Get Scripts</a>
                        <a href="<?php echo get_template_directory_uri(); ?>/assets/vendor.min.js" class="btn btn-purple btn-sm">Get Vendor Scripts</a>
                        <a href="<?php echo get_template_directory_uri(); ?>/assets/fonts" class="btn btn-purple btn-sm">Get Fonts</a>
                    </p>

                </div>
            </div>

            <?php
                if ( isset( $_GET['get'] ) ) {
                    $get = $_GET['get'];

                    echo '<h2>Get It!</h2>';

                    if ( 'template' === $get ) {
                        ob_start(); // begin collecting output
                        require( 'download/download-template.php' );
                        $contents = ob_get_clean();
                        echo '<textarea class="form-control" style="height: 500px;" onclick="this.focus();this.select()" readonly="readonly">' . htmlspecialchars($contents) . '</textarea>';
                    }

                    if ( 'header' === $get ) {
                        ob_start(); // begin collecting output
                        require( 'download/download-header.php' );
                        $contents = ob_get_clean();
                        $contents = trim(preg_replace('/\s\s+/', ' ', $contents));
                        echo '<textarea class="form-control" style="height: 500px;" onclick="this.focus();this.select()" readonly="readonly">' . htmlspecialchars($contents) . '</textarea>';
                    }

                    if ( 'footer' === $get ) {
                        ob_start(); // begin collecting output
                        require( 'download/download-footer.php' );
                        $contents = ob_get_clean();
                        $contents = trim(preg_replace('/\s\s+/', ' ', $contents));
                        $contents = str_replace('</main>', '', $contents);
                        $contents = str_replace('</div> </body>'."\n".'</html>', '', $contents);
                        echo '<textarea class="form-control" style="height: 400px;" onclick="this.focus();this.select()" readonly="readonly">' . htmlspecialchars($contents) . '</textarea>';
                    }
                }
            ?>

        </article>

    </div>
</section>

<?php get_footer();
