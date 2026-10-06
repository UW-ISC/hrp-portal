<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Column;

use WPDataTables\Services\Tools\ToolsService;

/**
 * Link column formatter.
 *
 * Migrated from {@see \LinkWDTColumn}. Handles link/button rendering with rel
 * attributes and the link cell filter hooks for frontend display.
 *
 * @package WPDataTables\Entity\Column
 */
class LinkColumn extends \WDTColumn
{
    /** @var string */
    protected $_jsDataType = 'string';

    /** @var string */
    protected $_dataType = 'string';

    /** @var string */
    protected $_linkTargetAttribute = '_self';

    /** @var int */
    protected $_linkNoFollowAttribute = 0;

    /** @var int */
    protected $_linkNoreferrerAttribute = 0;

    /** @var int */
    protected $_linkSponsoredAttribute = 0;

    /** @var int */
    protected $_linkButtonAttribute = 0;

    /** @var string */
    protected $_linkButtonLabel = '';

    /** @var string */
    protected $_linkButtonClass = '';

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'link';
        $this->setLinkTargetAttribute(ToolsService::defineDefaultValue($properties, 'linkTargetAttribute', '_self'));
        $this->setLinkNoFollowAttribute(ToolsService::defineDefaultValue($properties, 'linkNoFollowAttribute', 0));
        $this->setLinkNoreferrerAttribute(ToolsService::defineDefaultValue($properties, 'linkNoreferrerAttribute', 0));
        $this->setLinkSponsoredAttribute(ToolsService::defineDefaultValue($properties, 'linkSponsoredAttribute', 0));
        $this->setLinkButtonAttribute(ToolsService::defineDefaultValue($properties, 'linkButtonAttribute', 0));
        $this->setLinkButtonLabel(ToolsService::defineDefaultValue($properties, 'linkButtonLabel', ''));
        $this->setLinkButtonClass(ToolsService::defineDefaultValue($properties, 'linkButtonClass', ''));
    }

    /**
     * @param mixed $content
     *
     * @return mixed|string
     */
    public function prepareCellOutput($content)
    {
        $targetAttribute = esc_attr($this->getLinkTargetAttribute());
        $nofollowAttribute = ( 1 === (int) $this->getLinkNoFollowAttribute() ) ? 'nofollow' : '';
        $noreferrerAttribute = ( 1 === (int) $this->getLinkNoreferrerAttribute() ) ? 'noreferrer' : '';
        $sponsoredAttribute = ( 1 === (int) $this->getLinkSponsoredAttribute() ) ? 'sponsored' : '';
        $rel_parts = array_filter(
            array($nofollowAttribute, $noreferrerAttribute, $sponsoredAttribute)
        );
        $rel_esc = esc_attr(implode(' ', $rel_parts));
        $buttonClass_esc = esc_attr($this->getLinkButtonClass());

        $content = apply_filters(
            'wpdatatables_filter_link_cell_before_formatting',
            $content,
            $this->getParentTable()->getWpId()
        );

        if (is_null($content)) {
            $formattedValue = '';
        } elseif (strpos($content, '||') !== false) {
            $parts = explode('||', $content, 2);
            $linkPart = isset($parts[0]) ? trim($parts[0]) : '';
            $textPart = isset($parts[1]) ? trim($parts[1]) : '';
            $href = esc_url($linkPart);
            $data_content_esc = esc_attr($textPart);
            $text_html = esc_html($textPart);
            $buttonLabel = '' !== $this->getLinkButtonLabel() ? $this->getLinkButtonLabel() : $textPart;
            $buttonLabel_html = esc_html($buttonLabel);

            if ('' === $href) {
                $formattedValue = esc_html($content);
            } elseif ( 1 === (int) $this->getLinkButtonAttribute() && '' !== $textPart ) {
                $formattedValue = '<a data-content="' . $data_content_esc . '" href="' . $href . '" rel="' . $rel_esc . '" target="' . $targetAttribute . '"><button class="' . $buttonClass_esc . '">' . $buttonLabel_html . '</button></a>';
            } else {
                $formattedValue = '<a data-content="' . $data_content_esc . '" href="' . $href . '" rel="' . $rel_esc . '" target="' . $targetAttribute . '">' . $text_html . '</a>';
            }
        } elseif ( 'attachment' === $this->_inputType ) {
            $buttonLabel = '' !== $this->getLinkButtonLabel() ? $this->getLinkButtonLabel() : $content;
            $buttonLabel_html = esc_html($buttonLabel);
            $title_html = esc_html($this->_title);
            if ( empty($content) ) {
                $formattedValue = '';
            } else {
                $href = esc_url(trim($content));
                if ( '' === $href ) {
                    $formattedValue = esc_html($content);
                } elseif ( 1 === (int) $this->getLinkButtonAttribute() ) {
                    if ( '' !== $this->getLinkButtonLabel() ) {
                        $formattedValue = '<a href="' . $href . '" rel="' . $rel_esc . '" target="' . $targetAttribute . '"><button class="' . $buttonClass_esc . '">' . $buttonLabel_html . '</button></a>';
                    } else {
                        $formattedValue = '<a href="' . $href . '" rel="' . $rel_esc . '" target="' . $targetAttribute . '"><button class="' . $buttonClass_esc . '">' . $title_html . '</button></a>';
                    }
                } else {
                    $formattedValue = '<a href="' . $href . '" rel="' . $rel_esc . '" target="' . $targetAttribute . '">' . $title_html . '</a>';
                }
            }
        } elseif ( 1 === (int) $this->getLinkButtonAttribute() && '' === $content ) {
            $formattedValue = '';
        } elseif ( 1 === (int) $this->getLinkButtonAttribute() && '' !== $content ) {
            $href = esc_url(trim($content));
            $buttonLabel = '' !== $this->getLinkButtonLabel() ? $this->getLinkButtonLabel() : $content;
            $buttonLabel_html = esc_html($buttonLabel);
            if ( '' === $href ) {
                $formattedValue = $buttonLabel_html;
            } else {
                $formattedValue = '<a href="' . $href . '" rel="' . $rel_esc . '" target="' . $targetAttribute . '"><button class="' . $buttonClass_esc . '">' . $buttonLabel_html . '</button></a>';
            }
        } elseif ( '' === $content ) {
            $formattedValue = '';
        } else {
            $href = esc_url(trim($content));
            $text_html = esc_html($content);
            if ( '' === $href ) {
                $formattedValue = $text_html;
            } else {
                $formattedValue = '<a href="' . $href . '" rel="' . $rel_esc . '" target="' . $targetAttribute . '">' . $text_html . '</a>';
            }
        }

        return apply_filters(
            'wpdatatables_filter_link_cell',
            $formattedValue,
            $this->getParentTable()->getWpId()
        );
    }

    /**
     * @return string
     */
    public function getLinkTargetAttribute()
    {
        return $this->_linkTargetAttribute;
    }

    /**
     * @param string $linkTargetAttribute
     */
    public function setLinkTargetAttribute($linkTargetAttribute)
    {
        $this->_linkTargetAttribute = $linkTargetAttribute;
    }

    /**
     * @return int
     */
    public function getLinkNoFollowAttribute()
    {
        return $this->_linkNoFollowAttribute;
    }

    /**
     * @param int $linkNoFollowAttribute
     */
    public function setLinkNoFollowAttribute($linkNoFollowAttribute)
    {
        $this->_linkNoFollowAttribute = $linkNoFollowAttribute;
    }

    /**
     * @return int
     */
    public function getLinkNoreferrerAttribute()
    {
        return $this->_linkNoreferrerAttribute;
    }

    /**
     * @param int $linkNoreferrerAttribute
     */
    public function setLinkNoreferrerAttribute($linkNoreferrerAttribute)
    {
        $this->_linkNoreferrerAttribute = $linkNoreferrerAttribute;
    }

    /**
     * @return int
     */
    public function getLinkSponsoredAttribute()
    {
        return $this->_linkSponsoredAttribute;
    }

    /**
     * @param int $linkSponsoredAttribute
     */
    public function setLinkSponsoredAttribute($linkSponsoredAttribute)
    {
        $this->_linkSponsoredAttribute = $linkSponsoredAttribute;
    }

    /**
     * @return int
     */
    public function getLinkButtonAttribute()
    {
        return $this->_linkButtonAttribute;
    }

    /**
     * @param int $linkButtonAttribute
     */
    public function setLinkButtonAttribute($linkButtonAttribute)
    {
        $this->_linkButtonAttribute = $linkButtonAttribute;
    }

    /**
     * @return string
     */
    public function getLinkButtonLabel()
    {
        return $this->_linkButtonLabel;
    }

    /**
     * @param string $linkButtonLabel
     */
    public function setLinkButtonLabel($linkButtonLabel)
    {
        $this->_linkButtonLabel = $linkButtonLabel;
    }

    /**
     * @return string
     */
    public function getLinkButtonClass()
    {
        return $this->_linkButtonClass;
    }

    /**
     * @param string $linkButtonClass
     */
    public function setLinkButtonClass($linkButtonClass)
    {
        $this->_linkButtonClass = $linkButtonClass;
    }
}
