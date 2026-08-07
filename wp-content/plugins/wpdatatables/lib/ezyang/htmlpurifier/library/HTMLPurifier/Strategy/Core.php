<?php

namespace WPDT;

/**
 * Core strategy composed of the big four strategies.
 */
class HTMLPurifier_Strategy_Core extends \WPDT\HTMLPurifier_Strategy_Composite
{
    public function __construct()
    {
        $this->strategies[] = new \WPDT\HTMLPurifier_Strategy_RemoveForeignElements();
        $this->strategies[] = new \WPDT\HTMLPurifier_Strategy_MakeWellFormed();
        $this->strategies[] = new \WPDT\HTMLPurifier_Strategy_FixNesting();
        $this->strategies[] = new \WPDT\HTMLPurifier_Strategy_ValidateAttributes();
    }
}
// vim: et sw=4 sts=4
