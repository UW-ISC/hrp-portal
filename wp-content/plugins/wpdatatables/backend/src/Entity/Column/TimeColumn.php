<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

/**
 * Time column formatter.
 *
 * Migrated from {@see \TimeWDTColumn}. Handles time formatting and the
 * time cell filter hooks for frontend display.
 *
 * @package WPDataTables\Entity\Column
 */
class TimeColumn extends \WDTColumn
{
    /** @var string */
    protected $_jsDataType = 'time-custom';

    /** @var string */
    protected $_dataType = 'time';

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'time';
    }

    /**
     * @param mixed $content
     *
     * @return false|mixed|string
     */
    public function prepareCellOutput($content)
    {
        $content = apply_filters(
            'wpdatatables_filter_time_cell_before_formatting',
            $content,
            $this->getParentTable()->getWpId()
        );

        if (!is_array($content)) {
            if (!empty($content) && ($content != '0000-00-00')) {
                $timestamp = is_numeric($content) ? $content : strtotime(str_replace('/', '-', $content));
                $formattedValue = date(get_option('wdtTimeFormat'), $timestamp);
            } else {
                $formattedValue = '';
            }
        } elseif (!is_null($content['value'])) {
            $content['value'] = str_replace('/', '-', $content['value']);
            $formattedValue = date(get_option('wdtTimeFormat'), strtotime($content['value']));
        } else {
            $formattedValue = '';
        }

        return apply_filters(
            'wpdatatables_filter_time_cell',
            $formattedValue,
            $this->getParentTable()->getWpId()
        );
    }
}
