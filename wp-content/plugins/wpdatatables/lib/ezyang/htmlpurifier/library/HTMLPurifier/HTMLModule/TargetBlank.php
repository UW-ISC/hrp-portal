<?php

namespace WPDT;

/**
 * Module adds the target=blank attribute transformation to a tags.  It
 * is enabled by HTML.TargetBlank
 */
class HTMLPurifier_HTMLModule_TargetBlank extends \WPDT\HTMLPurifier_HTMLModule
{
    /**
     * @type string
     */
    public $name = 'TargetBlank';
    /**
     * @param HTMLPurifier_Config $config
     */
    public function setup($config)
    {
        $a = $this->addBlankElement('a');
        $a->attr_transform_post[] = new \WPDT\HTMLPurifier_AttrTransform_TargetBlank();
    }
}
// vim: et sw=4 sts=4
