<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Table;

use WPDataTables\ValueObjects\Table\TableSettings;

/**
 * Mutable domain object for a wpDataTable.
 *
 * Wraps an immutable {@see TableSettings} value object and exposes
 * intent-revealing accessors plus `toArray()` for repository writes. This entity
 * is the persistence shape; data loading / rendering responsibilities live
 * elsewhere.
 *
 * @package WPDataTables\Entity\Table
 */
class Table
{
    /** @var TableSettings */
    private TableSettings $settings;

    public function __construct(TableSettings $settings)
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

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->settings->get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set(string $key, $value): void
    {
        $this->settings->attributes[$key] = $value;
    }

    public function getTitle(): string
    {
        return (string) $this->settings->get('title', '');
    }

    public function getTableType(): string
    {
        return (string) $this->settings->get('table_type', '');
    }

    public function getContent(): string
    {
        return (string) $this->settings->get('content', '');
    }

    public function getConnection(): ?string
    {
        $connection = $this->settings->get('connection');

        return $connection === null ? null : (string) $connection;
    }

    public function isServerSide(): bool
    {
        return (bool) $this->settings->get('server_side', 0);
    }

    public function isEditable(): bool
    {
        return (bool) $this->settings->get('editable', 0);
    }

    /**
     * @return array<string, mixed>
     */
    public function getAdvancedSettings(): array
    {
        return $this->settings->advancedSettings;
    }

    /**
     * The persisted `wpdatatables` row, keyed by column name.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $attributes = $this->settings->attributes;
        $attributes['id'] = $this->settings->id;
        $attributes['advanced_settings'] = wp_json_encode($this->settings->advancedSettings);

        return $attributes;
    }
}
