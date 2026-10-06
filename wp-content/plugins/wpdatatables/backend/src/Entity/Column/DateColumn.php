<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

/**
 * Date column formatter.
 *
 * Migrated from {@see \DateWDTColumn}. Handles date formatting and the
 * date cell filter hooks for frontend display.
 *
 * @package WPDataTables\Entity\Column
 */
class DateColumn extends \WDTColumn
{
    /** @var string */
    protected $_jsDataType = 'date-custom';

    /** @var string */
    protected $_dataType = 'date';

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'date';
    }

    /**
     * @param mixed $content
     *
     * @return false|mixed|string
     */
    public function prepareCellOutput($content)
    {
        $content = apply_filters(
            'wpdatatables_filter_date_cell_before_formatting',
            $content,
            $this->getParentTable()->getWpId()
        );

        if (!is_array($content)) {
            if (!empty($content) && ($content != '0000-00-00')) {
                $timestamp = is_numeric($content) ? $content : strtotime(str_replace('/', '-', $content));
                $formattedValue = date(get_option('wdtDateFormat'), $timestamp);
            } else {
                $formattedValue = '';
            }
        } elseif (!is_null($content['value'])) {
            $content['value'] = str_replace('/', '-', $content['value']);
            $formattedValue = date(get_option('wdtDateFormat'), strtotime($content['value']));
        } else {
            $formattedValue = '';
        }

        return apply_filters(
            'wpdatatables_filter_date_cell',
            $formattedValue,
            $this->getParentTable()->getWpId()
        );
    }

    /**
     * @return string
     */
    public function getGoogleChartColumnType()
    {
        return 'date';
    }
}
