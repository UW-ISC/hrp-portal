<?php

defined('ABSPATH') or die('Access denied.');

class StringWDTColumn extends WDTColumn
{

    protected $_dataType = 'string';
    protected $_jsDataType = 'string';

    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'string';
        $this->_foreignKeyRule = WDTTools::defineDefaultValue($properties, 'foreignKeyRule', null);
    }

    public function prepareCellOutput($content)
    {
        if (get_option('wdtParseShortcodes')) {
            $content = do_shortcode($content);
        }
        $content = apply_filters('wpdatatables_filter_string_cell', $content, $this->getParentTable()->getWpId());
        return $content;
    }

}