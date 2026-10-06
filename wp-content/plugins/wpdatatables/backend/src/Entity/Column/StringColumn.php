<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

use WPDataTables\Services\Tools\ToolsService;

/**
 * String column formatter.
 *
 * Migrated from {@see \StringWDTColumn}. Handles shortcode parsing and the
 * string cell filter hooks for frontend display.
 *
 * @package WPDataTables\Entity\Column
 */
class StringColumn extends \WDTColumn
{
    /** @var string */
    protected $_dataType = 'string';

    /** @var string */
    protected $_jsDataType = 'string';

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'string';
        $this->_foreignKeyRule = ToolsService::defineDefaultValue($properties, 'foreignKeyRule', null);
    }

    /**
     * @param mixed $content
     *
     * @return mixed
     */
    public function prepareCellOutput($content)
    {
        $content = apply_filters(
            'wpdatatables_filter_string_cell_before_formatting',
            $content,
            $this->getParentTable()->getWpId()
        );

        if (get_option('wdtParseShortcodes')) {
            if (!is_null($content)) {
                $content = do_shortcode($content);
            }
        }

        return apply_filters(
            'wpdatatables_filter_string_cell',
            $content,
            $this->getParentTable()->getWpId()
        );
    }
}
