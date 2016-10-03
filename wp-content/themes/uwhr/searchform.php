<?php

/**
 * Search Form
 *
 * This is the default search form, rendered from the global $UWHR object.
 *
 * If you want to add some classes to the search form, use this code snippet
 * directly in your template instead of get_search_form()
 *
 * @see class/class.uwhr-search-ui.php
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

global $UWHR;
$UWHR->Search->UI->render_search_form();
