<?php


defined('ABSPATH') or die('Access denied.');


class IndexWDTColumn extends WDTColumn
{
    protected $_jsDataType = 'int';
    protected $_dataType = 'index';

    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'index';
        $this->_filterType = 'none';
        $this->_inputType = 'none';
    }

    public function prepareCellOutput($content)
    {
        $content = apply_filters('wpdatatables_filter_index_cell_before_formatting', $content, $this->getParentTable()->getWpId());

        $number_format = get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1;
        $decimal_places = '';

        if ($number_format == 1) {
            $formattedValue = number_format(
                (int)$content,
            );
        } else {
            $formattedValue = number_format(
                (int)$content,
            );
        }

        return apply_filters('wpdatatables_filter_index_cell', $formattedValue, $this->getParentTable()->getWpId());
    }
}