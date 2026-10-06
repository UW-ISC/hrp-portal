<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

/**
 * Cart column formatter.
 *
 * Migrated from {@see \CartWDTColumn}. Add-to-cart columns disable sorting
 * and filtering and use a dedicated frontend CSS class.
 *
 * @package WPDataTables\Entity\Column
 */
class CartColumn extends \WDTColumn
{
    /** @var string */
    protected $_jsDataType = 'cart';

    /** @var string */
    protected $_dataType = 'cart';

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'cart';
        $this->_filterType = 'none';
        $this->_sorting = false;
        $this->addCSSClass('wdt-add-to-cart-column');
    }
}
