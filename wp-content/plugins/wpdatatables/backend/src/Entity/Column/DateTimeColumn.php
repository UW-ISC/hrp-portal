<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

/**
 * Date-time column formatter.
 *
 * Migrated from {@see \DateTimeWDTColumn}. Handles date-time formatting and the
 * datetime cell filter hooks for frontend display.
 *
 * @package WPDataTables\Entity\Column
 */
class DateTimeColumn extends \WDTColumn
{
    /** @var string */
    protected $_jsDataType = 'date-custom';

    /** @var string */
    protected $_dataType = 'datetime';

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'datetime';
    }

    /**
     * @param mixed $content
     *
     * @return false|mixed|string
     */
    public function prepareCellOutput($content)
    {
        $content = apply_filters(
            'wpdatatables_filter_datetime_cell_before_formatting',
            $content,
            $this->getParentTable()->getWpId()
        );

        $dateTimeFormat = get_option('wdtDateFormat') . ' ' . get_option('wdtTimeFormat');

        if (!is_array($content)) {
            if (!empty($content) && ($content != '0000-00-00')) {
                $timestamp = is_numeric($content) ? $content : strtotime(str_replace('/', '-', $content));
                $formattedValue = date($dateTimeFormat, $timestamp);
            } else {
                $formattedValue = '';
            }
        } elseif (!is_null($content['value'])) {
            $content['value'] = str_replace('/', '-', $content['value']);
            $formattedValue = date($dateTimeFormat, strtotime($content['value']));
        } else {
            $formattedValue = '';
        }

        return apply_filters(
            'wpdatatables_filter_datetime_cell',
            $formattedValue,
            $this->getParentTable()->getWpId()
        );
    }

    /**
     * @return string
     */
    public function getGoogleChartColumnType()
    {
        return 'datetime';
    }
}
