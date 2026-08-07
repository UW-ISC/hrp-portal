<?php

namespace WPDT;

class HTMLPurifier_HTMLModule_Tidy_Proprietary extends \WPDT\HTMLPurifier_HTMLModule_Tidy
{
    /**
     * @type string
     */
    public $name = 'Tidy_Proprietary';
    /**
     * @type string
     */
    public $defaultLevel = 'light';
    /**
     * @return array
     */
    public function makeFixes()
    {
        $r = array();
        $r['table@background'] = new \WPDT\HTMLPurifier_AttrTransform_Background();
        $r['td@background'] = new \WPDT\HTMLPurifier_AttrTransform_Background();
        $r['th@background'] = new \WPDT\HTMLPurifier_AttrTransform_Background();
        $r['tr@background'] = new \WPDT\HTMLPurifier_AttrTransform_Background();
        $r['thead@background'] = new \WPDT\HTMLPurifier_AttrTransform_Background();
        $r['tfoot@background'] = new \WPDT\HTMLPurifier_AttrTransform_Background();
        $r['tbody@background'] = new \WPDT\HTMLPurifier_AttrTransform_Background();
        $r['table@height'] = new \WPDT\HTMLPurifier_AttrTransform_Length('height');
        return $r;
    }
}
// vim: et sw=4 sts=4
