<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

/**
 * Hidden (dynamic) column formatter.
 *
 * Migrated from {@see \HiddenWDTColumn}. Values are resolved from placeholders
 * at render time and optionally formatted as date/time when configured.
 *
 * @package WPDataTables\Entity\Column
 */
class HiddenColumn extends \WDTColumn
{
    /** @var string */
    protected $_dataType = 'hidden';

    /** @var string */
    protected $_jsDataType = 'string';

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'hidden';
    }

    /**
     * @param mixed $content
     *
     * @return mixed
     */
    public function prepareCellOutput($content)
    {
        $content = apply_filters(
            'wpdatatables_filter_hidden_cell_before_formatting',
            $content,
            $this->getEditingDefaultValue(),
            $this->getParentTable()->getWpId()
        );

        if (in_array($this->getEditingDefaultValue(), ['date', 'datetime', 'time'], true)) {
            if (!empty($content) && ($content != '0000-00-00')) {
                $timestamp = is_numeric($content) ? $content : strtotime(str_replace('/', '-', $content));
                if ($this->getEditingDefaultValue() == 'date') {
                    $content = date(get_option('wdtDateFormat'), $timestamp);
                }
                if ($this->getEditingDefaultValue() == 'datetime') {
                    $content = date(get_option('wdtDateFormat') . ' ' . get_option('wdtTimeFormat'), $timestamp);
                }
                if ($this->getEditingDefaultValue() == 'time') {
                    $content = date(get_option('wdtTimeFormat'), $timestamp);
                }
            } else {
                $content = '';
            }
        }

        return apply_filters(
            'wpdatatables_filter_hidden_cell',
            $content,
            $this->getEditingDefaultValue(),
            $this->getParentTable()->getWpId()
        );
    }
}
