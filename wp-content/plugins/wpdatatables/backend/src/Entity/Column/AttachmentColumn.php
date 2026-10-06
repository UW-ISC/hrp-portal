<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

/**
 * Attachment column formatter.
 *
 * Alias of the file/attachment link column. Used when the legacy generator
 * requested an `attachment` column type slug.
 *
 * @package WPDataTables\Entity\Column
 */
class AttachmentColumn extends FileColumn
{
    /**
     * @param array<string, mixed> $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->setInputType('attachment');
    }

    /**
     * @param mixed $content
     *
     * @return mixed|string
     */
    public function prepareCellOutput($content)
    {
        $content = apply_filters(
            'wpdatatables_filter_attachment_cell_before_formatting',
            $content,
            $this->getParentTable()->getWpId()
        );

        $formattedValue = parent::prepareCellOutput($content);

        return apply_filters(
            'wpdatatables_filter_attachment_cell',
            $formattedValue,
            $this->getParentTable()->getWpId()
        );
    }
}
