<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

/**
 * Image column formatter.
 *
 * Migrated from {@see \ImageWDTColumn}. Handles image/lightbox rendering and
 * the image cell filter hooks for frontend display.
 *
 * @package WPDataTables\Entity\Column
 */
class ImageColumn extends \WDTColumn
{
    /** @var string */
    protected $_jsDataType = 'string';

    /** @var string */
    protected $_dataType = 'string';

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'icon';
    }

    /**
     * @param mixed $content
     *
     * @return mixed|string
     */
    public function prepareCellOutput($content)
    {
        $content = apply_filters(
            'wpdatatables_filter_image_cell_before_formatting',
            $content,
            $this->getParentTable()->getWpId()
        );

        if (empty($content)) {
            return '';
        }

        if (false !== strpos($content, '||')) {
            $parts = explode('||', $content, 2);
            $image = isset($parts[0]) ? trim($parts[0]) : '';
            $link = isset($parts[1]) ? trim($parts[1]) : '';
            $image = esc_url($image);
            $link = esc_url($link);
            if ($image === '' && $link === '') {
                $formattedValue = '';
            } elseif ($image !== '' && $link !== '') {
                $formattedValue = '<a href="' . $link . '" target="_blank" rel="lightbox[-1] noopener noreferrer">'
                    . '<img src="' . $image . '" alt="" /></a>';
            } elseif ($image !== '') {
                $formattedValue = '<img src="' . $image . '" alt="" />';
            } else {
                $formattedValue = '';
            }
        } else {
            $src = esc_url(trim($content));
            $formattedValue = $src !== '' ? '<img src="' . $src . '" alt="" />' : '';
        }

        return apply_filters(
            'wpdatatables_filter_image_cell',
            $formattedValue,
            $this->getParentTable()->getWpId()
        );
    }
}
