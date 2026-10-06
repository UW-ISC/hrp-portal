<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

use WPDataTables\Services\Tools\ToolsService;

/**
 * Formula column formatter.
 *
 * Migrated from {@see \FormulaWDTColumn}. Evaluated formula values are formatted
 * as floats with locale-aware decimal separators.
 *
 * @package WPDataTables\Entity\Column
 */
class FormulaColumn extends \WDTColumn
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
        $this->_filterType = 'none';
        $this->addCSSClass('numdata formula');
        $this->setDecimalPlaces(ToolsService::defineDefaultValue($properties, 'decimalPlaces', -1));
    }

    /**
     * @param mixed $formula
     */
    public function setFormula($formula)
    {
        $this->_formula = $formula;
    }

    /**
     * @return mixed
     */
    public function getFormula()
    {
        return $this->_formula;
    }

    /**
     * @param mixed $content
     *
     * @return mixed|string
     */
    public function prepareCellOutput($content)
    {
        $content = apply_filters(
            'wpdatatables_filter_formula_cell_before_formatting',
            $content,
            $this->getParentTable()->getWpId()
        );

        $number_format = get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1;
        $decimal_places = $this->getDecimalPlaces() != -1
            ? $this->getDecimalPlaces()
            : get_option('wdtDecimalPlaces');

        if ($number_format == 1) {
            $formattedValue = number_format(
                (float) $content,
                $decimal_places,
                ',',
                $this->isShowThousandsSeparator() ? '.' : ''
            );
        } else {
            $formattedValue = number_format(
                (float) $content,
                $decimal_places,
                '.',
                $this->isShowThousandsSeparator() ? ',' : ''
            );
        }

        return apply_filters(
            'wpdatatables_filter_formula_cell',
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
