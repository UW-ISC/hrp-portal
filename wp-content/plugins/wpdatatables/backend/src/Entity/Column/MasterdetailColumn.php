<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

/**
 * Master-detail virtual column formatter.
 *
 * Core stub for the `masterdetail` column type. Row output for this column is
 * normally handled at the table level when the Master-Detail add-on is active;
 * the add-on may replace this class via `wpdatatables_column_type_class`.
 *
 * @package WPDataTables\Entity\Column
 */
class MasterdetailColumn extends \WDTColumn
{
    /** @var string */
    protected $_jsDataType = 'string';

    /** @var string */
    protected $_dataType = 'masterdetail';

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'masterdetail';
        $this->_filterType = 'none';
        $this->_inputType = 'none';
        $this->_searchable = false;
        $this->_sorting = false;
    }

    /**
     * @param mixed $content
     *
     * @return mixed|string
     */
    public function prepareCellOutput($content)
    {
        $content = apply_filters(
            'wpdatatables_filter_masterdetail_cell_before_formatting',
            $content,
            $this->getParentTable()->getWpId()
        );

        if ($content === null || $content === '') {
            $formattedValue = '';
        } else {
            $formattedValue = esc_html((string) $content);
        }

        return apply_filters(
            'wpdatatables_filter_masterdetail_cell',
            $formattedValue,
            $this->getParentTable()->getWpId()
        );
    }
}
