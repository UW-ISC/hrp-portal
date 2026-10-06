<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

/**
 * File (attachment link) column formatter.
 *
 * Legacy `file` column type mapped to link rendering with attachment input.
 * Cell output reuses {@see LinkColumn} attachment-link behaviour.
 *
 * @package WPDataTables\Entity\Column
 */
class FileColumn extends LinkColumn
{
    /**
     * @param array<string, mixed> $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);

        if ($this->getInputType() === '' || $this->getInputType() === 'text') {
            $this->setInputType('attachment');
        }
    }

    /**
     * @param mixed $content
     *
     * @return mixed|string
     */
    public function prepareCellOutput($content)
    {
        $content = apply_filters(
            'wpdatatables_filter_file_cell_before_formatting',
            $content,
            $this->getParentTable()->getWpId()
        );

        $formattedValue = parent::prepareCellOutput($content);

        return apply_filters(
            'wpdatatables_filter_file_cell',
            $formattedValue,
            $this->getParentTable()->getWpId()
        );
    }
}
