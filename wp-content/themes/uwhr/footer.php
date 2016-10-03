<?php

/**
 * Footer
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

$menus = uwhr_get_footer_menus(); ?>

</main>

<footer class="uwhr-footer" role="contentinfo">
    <a href="//www.washington.edu" class="footer-wordmark">University of Washington</a>
    <a href="//www.washington.edu/boundless/"><p class="be-boundless">Be boundless</p></a>
    <?php echo $menus['quick-links']; ?>
    <p>&copy; <?php echo date("Y"); ?> University of Washington | Seattle, WA</p>
    <?php wp_footer(); ?>
    <!--[if lt IE 9]>
        <script src="<?php bloginfo("template_directory"); ?>/assets/ie/js/respond.min.js" type="text/javascript"></script>
        <script src="<?php bloginfo("template_directory"); ?>/assets/ie/js/rem.min.js" type="text/javascript"></script>
    <![endif]-->
</footer>

</div> <?php /* .uwhr-wrap */ ?>

</body>
</html>
