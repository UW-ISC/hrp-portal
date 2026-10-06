<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

/**
 * Select column formatter.
 *
 * Migrated from {@see \SelectWDTColumn}. Dropdown/select columns disable
 * sorting and filtering and use a dedicated frontend CSS class.
 *
 * @package WPDataTables\Entity\Column
 */
class SelectColumn extends \WDTColumn
{
    /** @var string */
    protected $_jsDataType = 'select';

    /** @var string */
    protected $_dataType = 'select';

    /**
     * @param array<string, mixed> $properties
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
