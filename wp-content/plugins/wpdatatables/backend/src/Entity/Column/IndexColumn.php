<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

/**
 * Index column formatter.
 *
 * Migrated from {@see \IndexWDTColumn}. Renders auto-increment row indices with
 * locale-aware thousands separators.
 *
 * @package WPDataTables\Entity\Column
 */
class IndexColumn extends \WDTColumn
{
    /** @var string */
    protected $_jsDataType = 'int';

    /** @var string */
    protected $_dataType = 'index';

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'index';
        $this->_filterType = 'none';
        $this->_inputType = 'none';
    }

    /**
     * @param mixed $content
     *
     * @return mixed|string
     */
    public function prepareCellOutput($content)
    {
        $content = apply_filters(
            'wpdatatables_filter_index_cell_before_formatting',
            $content,
            $this->getParentTable()->getWpId()
        );

        $number_format = get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1;

        if ($number_format == 1) {
            $formattedValue = number_format((int) $content);
        } else {
            $formattedValue = number_format((int) $content);
        }

        return apply_filters(
            'wpdatatables_filter_index_cell',
            $formattedValue,
            $this->getParentTable()->getWpId()
        );
    }
}
