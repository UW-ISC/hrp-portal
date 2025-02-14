<?php

defined('ABSPATH') or die("Access denied");

class CartWDTColumn extends WDTColumn
{

    protected $_jsDataType = 'cart';
    protected $_dataType = 'cart';


    /**
     * CartWDTColumn constructor.
     *
     * @param array $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'cart';
        $this->_filterType = 'none';
        $this->_sorting = false;
        $this->addCSSClass('wdt-add-to-cart-column');
    }
}
