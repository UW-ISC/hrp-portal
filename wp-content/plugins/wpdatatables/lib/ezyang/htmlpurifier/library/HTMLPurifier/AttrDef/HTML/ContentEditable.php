<?php

namespace WPDT;

class HTMLPurifier_AttrDef_HTML_ContentEditable extends \WPDT\HTMLPurifier_AttrDef
{
    public function validate($string, $config, $context)
    {
        $allowed = array('false');
        if ($config->get('HTML.Trusted')) {
            $allowed = array('', 'true', 'false');
        }
        $enum = new \WPDT\HTMLPurifier_AttrDef_Enum($allowed);
        return $enum->validate($string, $config, $context);
    }
}
