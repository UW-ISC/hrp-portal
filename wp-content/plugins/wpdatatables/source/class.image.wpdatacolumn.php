<?php

defined('ABSPATH') or die('Access denied.');

class ImageWDTColumn extends WDTColumn
{

    protected $_jsDataType = 'string';
    protected $_dataType = 'string';

    /**
     * ImageWDTColumn constructor.
     * @param array $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'icon';
    }

    /**
     * @param $content
     * @return mixed|string
     */
    public function prepareCellOutput($content)
    {
        $content = apply_filters('wpdatatables_filter_image_cell_before_formatting', $content, $this->getParentTable()->getWpId());
        $imageName = pathinfo($content)['basename'];
        if (empty($content)) {
            return '';
        }
        if (FALSE !== strpos($content, '||')) {
            list($image, $link) = explode('||', $content);
            $image_name = pathinfo($image)['basename'];
            $formattedValue = "<a href='{$link}' target='_blank' rel='lightbox[-1]'><img src='{$image}' alt='{$image_name}'/></a>";
        } else {
            $formattedValue = "<img src='{$content}' alt='{$imageName}'/>";
        }
        return apply_filters('wpdatatables_filter_image_cell', $formattedValue, $this->getParentTable()->getWpId());
    }

}
