<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Factory\Template;

use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Entity\Template\Template;
use WPDataTables\ValueObjects\Template\TemplateSettings;

/**
 * Hydrates a {@see Template} entity from a raw `wpdatatables_templates` row.
 *
 * Referenced by `TemplateRepository::FACTORY`. Decodes the `data`, `content`
 * and `settings` JSON blobs.
 *
 * @package WPDataTables\Factory\Template
 */
class TemplateFactory
{
    /**
     * @param array<string, mixed> $data
     *
     * @return Template
     *
     * @throws InvalidArgumentException
     */
    public static function create(array $data): Template
    {
        return new Template(
            new TemplateSettings(
                (int) ($data['id'] ?? 0),
                (int) ($data['table_id'] ?? 0),
                isset($data['table_type']) ? (string) $data['table_type'] : null,
                isset($data['data']) ? json_decode((string) $data['data']) : null,
                isset($data['content']) ? json_decode((string) $data['content']) : null,
                isset($data['settings']) ? json_decode((string) $data['settings']) : null
            )
        );
    }
}
