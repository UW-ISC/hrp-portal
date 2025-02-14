<?php

defined('ABSPATH') or die('Access denied.');

class HiddenWDTColumn extends WDTColumn
{

    protected $_dataType = 'hidden';
    protected $_jsDataType = 'string';

    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'hidden';
    }

    public function prepareCellOutput($content)
    {
        $content = apply_filters('wpdatatables_filter_hidden_cell_before_formatting', $content, $this->getEditingDefaultValue(), $this->getParentTable()->getWpId());

        if (in_array($this->getEditingDefaultValue(), ['date', 'datetime', 'time'])) {
            if (!empty($content) && ($content != '0000-00-00')) {
                $timestamp = is_numeric($content) ? $content : strtotime(str_replace('/', '-', $content));
                if ($this->getEditingDefaultValue() == 'date')
                    $content = date(get_option('wdtDateFormat'), $timestamp);
                if ($this->getEditingDefaultValue() == 'datetime')
                    $content = date(get_option('wdtDateFormat') . ' ' . get_option('wdtTimeFormat'), $timestamp);
                if ($this->getEditingDefaultValue() == 'time')
                    $content = date(get_option('wdtTimeFormat'), $timestamp);
            } else {
                $content = '';
            }
        }

        return apply_filters('wpdatatables_filter_hidden_cell', $content, $this->getEditingDefaultValue(), $this->getParentTable()->getWpId());
    }

}