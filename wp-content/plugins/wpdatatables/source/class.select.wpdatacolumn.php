<?php

defined('ABSPATH') or die("Access denied");

class SelectWDTColumn extends WDTColumn
{

    protected $_jsDataType = 'select';
    protected $_dataType = 'select';


    /**
     * SelectWDTColumn constructor.
     *
     * @param array $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'select';
        $this->_filterType = 'none';
        $this->_sorting = false;
        $this->addCSSClass('wdt-select-column');
    }
}
