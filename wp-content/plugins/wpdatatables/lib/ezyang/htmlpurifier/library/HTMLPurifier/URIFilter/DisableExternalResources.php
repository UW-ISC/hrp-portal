<?php

namespace WPDT;

class HTMLPurifier_URIFilter_DisableExternalResources extends \WPDT\HTMLPurifier_URIFilter_DisableExternal
{
    /**
     * @type string
     */
    public $name = 'DisableExternalResources';
    /**
     * @param HTMLPurifier_URI $uri
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool
     */
    public function filter(&$uri, $config, $context)
    {
        if (!$context->get('EmbeddedURI', \true)) {
            return \true;
        }
        return parent::filter($uri, $config, $context);
    }
}
// vim: et sw=4 sts=4
