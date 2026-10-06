<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

use WPDataTables\Services\Tools\ToolsService;

/**
 * Float column formatter.
 *
 * Migrated from {@see \FloatWDTColumn}. Handles decimal-place formatting
 * and the float cell filter hooks for frontend display.
 *
 * @package WPDataTables\Entity\Column
 */
class FloatColumn extends \WDTColumn
{
    /** @var string */
    protected $_jsDataType = 'formatted-num';

    /** @var string */
    protected $_dataType = 'float';

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'float';
        $this->_filterType = 'number';
        $this->addCSSClass('numdata float');
        $this->setDecimalPlaces(ToolsService::defineDefaultValue($properties, 'decimalPlaces', -1));
    }

    /**
     * @param mixed $content
     *
     * @return mixed|string
     */
    public function prepareCellOutput($content)
    {
        $content = apply_filters(
            'wpdatatables_filter_float_cell_before_formatting',
            $content,
            $this->getParentTable()->getWpId()
        );

        if ($content === '' || $content === null) {
            return '';
        }

        $numberFormat = get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1;
        $decimalPlaces = $this->getDecimalPlaces() != -1
            ? $this->getDecimalPlaces()
            : get_option('wdtDecimalPlaces');

        if ($numberFormat == 1) {
            $formattedValue = number_format(
                (float) $content,
                $decimalPlaces,
                ',',
                '.'
            );
        } else {
            $formattedValue = number_format(
                (float) $content,
                $decimalPlaces
            );
        }

        return apply_filters(
            'wpdatatables_filter_float_cell',
            $formattedValue,
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
