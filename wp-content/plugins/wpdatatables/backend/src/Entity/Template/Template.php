<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Template;

use WPDataTables\ValueObjects\Template\TemplateSettings;

/**
 * Mutable domain object for a saved simple-table template.
 *
 * Persistence shape over the `wpdatatables_templates` table. The template
 * authoring / seeding logic lives in a TemplateService.
 *
 * @package WPDataTables\Entity\Template
 */
class Template
{
    /** @var TemplateSettings */
    private TemplateSettings $settings;

    public function __construct(TemplateSettings $settings)
    {
        $this->settings = $settings;
    }

    public function getId(): int
    {
        return $this->settings->id;
    }

    public function setId(int $id): void
    {
        $this->settings->id = $id;
    }

    public function getTableId(): int
    {
        return $this->settings->tableId;
    }

    public function getTableType(): ?string
    {
        return $this->settings->tableType;
    }

    /** @return mixed */
    public function getData()
    {
        return $this->settings->data;
    }

    /** @return mixed */
    public function getContent()
    {
        return $this->settings->content;
    }

    /** @return mixed */
    public function getTemplateSettings()
    {
        return $this->settings->settings;
    }

    /**
     * The persisted `wpdatatables_templates` row, keyed by column name.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'         => $this->settings->id,
            'table_id'   => $this->settings->tableId,
            'table_type' => $this->settings->tableType,
            'data'       => wp_json_encode($this->settings->data),
            'content'    => wp_json_encode($this->settings->content),
            'settings'   => wp_json_encode($this->settings->settings),
        ];
    }
}
