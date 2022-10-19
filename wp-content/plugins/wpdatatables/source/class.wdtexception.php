<?php

defined('ABSPATH') or die('Access denied.');

class WDTException extends Exception {

    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return "<br>{$this->message}\n";
    }

}

