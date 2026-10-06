<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

/**
 * Integer column formatter.
 *
 * Migrated from {@see \IntWDTColumn}. Handles thousands-separator formatting
 * and the int cell filter hooks for frontend display.
 *
 * @package WPDataTables\Entity\Column
 */
class IntColumn extends \WDTColumn
{
    /** @var string */
    protected $_dataType = 'int';

    /** @var string */
    protected $_jsDataType = 'numeric';

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'int';
        $this->_jsDataType = 'formatted-num';
        $this->_filterType = 'number';
        $this->addCSSClass('numdata integer');
    }

    /**
     * @param mixed $content
     *
     * @return mixed|string
     */
    public function prepareCellOutput($content)
    {
        $content = apply_filters(
            'wpdatatables_filter_int_cell_before_formatting',
            $content,
            $this->getParentTable()->getWpId()
        );

        if ($content === '' || $content === null) {
            return '';
        }

        $number_format = get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1;
        if ($number_format == 1) {
            $content = number_format(
                (int) $content,
                0,
                ',',
                $this->isShowThousandsSeparator() ? '.' : ''
            );
        } else {
            $content = number_format(
                (int) $content,
                0,
                '.',
                $this->isShowThousandsSeparator() ? ',' : ''
            );
        }

        return apply_filters(
            'wpdatatables_filter_int_cell',
            $content,
            $this->getParentTable()->getWpId()
        );
    }

    /**
     * @return string
     */
    public function getGoogleChartColumnType()
    {
        return 'number';
    }
}
