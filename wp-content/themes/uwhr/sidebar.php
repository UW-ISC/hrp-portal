<?php

/**
 * Sidebar
 *
 * The default sidebar appears on the right side of pages when used.
 * Most WP widgets have been disabled, giving site admins limited UWHR
 * developed widgets.
 *
 * There is a secondary WYSIWYG for each page, intended to be used for one-off,
 * page-specific content.
 *
 * Class definition is located in class/class.uwhr-sidebar.php
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.2.0
 * @package UWHR
 */

?>

<aside class="uwhr-sidebar" role="complementary">
	<?php dynamic_sidebar( UWHR_Sidebar::ID ); ?>
	<?php uwhr_sidebar_content(); ?>
</aside>
