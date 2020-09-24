<?php

defined('ABSPATH') or die('Access denied.');

class FloatWDTColumn extends WDTColumn {

    protected $_jsDataType = 'formatted-num';
    protected $_dataType = 'float';

    /**
     * FloatWDTColumn constructor.
     * @param array $properties
     */
    public function __construct($properties = array()) {
        parent::__construct($properties);
        $this->_dataType = 'float';
        $this->_filterType = 'number';
        $this->addCSSClass('numdata float');
        $this->setDecimalPlaces(WDTTools::defineDefaultValue($properties, 'decimalPlaces', -1));
    }

    /**
     * @param $content
     * @return mixed|string
     */
    public function prepareCellOutput($content) {

        if ($content === '' || $content === null) {
            $content = '';
            return $content;
        }

        $numberFormat = get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1;
        $decimalPlaces = $this->getDecimalPlaces() != -1 ? $this->getDecimalPlaces() : get_option('wdtDecimalPlaces');

        if ($numberFormat == 1) {
            $formattedValue = number_format(
                (float)$content,
                $decimalPlaces,
                ',',
                '.'
            );
        } else {
            $formattedValue = number_format(
                (float)$content,
                $decimalPlaces
            );
        }

        $formattedValue = apply_filters('wpdatatables_filter_float_cell', $formattedValue, $this->getParentTable()->getWpId());
        return $formattedValue;
    }

    /**
     * @return string
     */
    public function getGoogleChartColumnType() {
        return 'number';
    }

}
