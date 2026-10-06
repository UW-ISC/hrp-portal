<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\ValueObjects\Template;

use WPDataTables\Common\Exceptions\InvalidArgumentException;

/**
 * Immutable settings for a persisted simple-table template row.
 *
 * Wraps the columns of the `wpdatatables_templates` table. The `data`,
 * `content` and `settings` columns each hold a JSON blob, kept decoded here.
 *
 * @package WPDataTables\ValueObjects\Template
 */
final class TemplateSettings
{
    /** @var int */
    public int $id;

    /** @var int */
    public int $tableId;

    /** @var string|null */
    public ?string $tableType;

    /** @var mixed Decoded `data` blob. */
    public $data;

    /** @var mixed Decoded `content` blob. */
    public $content;

    /** @var mixed Decoded `settings` blob. */
    public $settings;

    /**
     * @param int         $id
     * @param int         $tableId
     * @param string|null $tableType
     * @param mixed       $data
     * @param mixed       $content
     * @param mixed       $settings
     *
     * @throws InvalidArgumentException
     */
    public function __construct(int $id, int $tableId, ?string $tableType = null, $data = null, $content = null, $settings = null)
    {
        if ($id < 0) {
            throw new InvalidArgumentException('Template id must not be negative.');
        }

        $this->id = $id;
        $this->tableId = $tableId;
        $this->tableType = $tableType;
        $this->data = $data;
        $this->content = $content;
        $this->settings = $settings;
    }
}
