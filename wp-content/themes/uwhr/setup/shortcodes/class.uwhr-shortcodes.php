<?php

/**
 * Shortcodes
 *
 * Includes and initializes all the shortcodes assocaited with content editing and
 * the content authors. Shortcodes associated with custom functionality (ie Cards, Contacts, etc)
 * can be found in that source.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

class UWHR_Shortcodes {

    /**
     * UWHR Shortcodes Constructor
     *
     * Let's get things started!
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
    function __construct() {
        $this->includes();
        $this->initialize();
    }

    /**
     * Includes
     *
     * Grab all the files
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
    private function includes() {
        require_once('class.uwhr-accordion-shortcode.php');
        require_once('class.uwhr-children-shortcode.php');

        require_once('class.uwhr-button-shortcode.php');
        require_once('class.uw-youtube-shortcode.php');
        require_once('class.uw-trumba-shortcode.php');
    }

    /**
     * Initialize
     *
     * Initialize all the parts
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
    private function initialize() {
        $this->Accordion      = new UWHR_Accordion();
        $this->Children       = new UWHR_Children();
        $this->Button         = new UWHR_Button();
        $this->YouTube        = new UW_YouTube();
        $this->Trumba         = new UW_Trumba();
    }
}
