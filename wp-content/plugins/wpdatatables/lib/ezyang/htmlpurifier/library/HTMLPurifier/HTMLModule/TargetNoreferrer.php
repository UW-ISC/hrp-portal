<?php

namespace WPDT;

/**
 * Module adds the target-based noreferrer attribute transformation to a tags.  It
 * is enabled by HTML.TargetNoreferrer
 */
class HTMLPurifier_HTMLModule_TargetNoreferrer extends \WPDT\HTMLPurifier_HTMLModule
{
    /**
     * @type string
     */
    public $name = 'TargetNoreferrer';
    /**
     * @param HTMLPurifier_Config $config
     */
    public function setup($config)
    {
        $a = $this->addBlankElement('a');
        $a->attr_transform_post[] = new \WPDT\HTMLPurifier_AttrTransform_TargetNoreferrer();
    }
}
