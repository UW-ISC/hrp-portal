<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Repository\Template;

use WPDataTables\Factory\Template\TemplateFactory;
use WPDataTables\Repository\AbstractRepository;

/**
 * Repository over the `wpdatatables_templates` table.
 *
 * Encapsulates the raw read of template rows; the per-row JSON decode stays in
 * the `WDTConfigController::loadRowsDataFromDBTemplateAll()` shim.
 *
 * @package WPDataTables\Repository\Template
 */
class TemplateRepository extends AbstractRepository implements TemplateRepositoryInterface
{
    public const FACTORY = TemplateFactory::class;

    /**
     * @param int $tableId
     *
     * @return array<int, object>
     */
    public function findRowsByTableId(int $tableId): array
    {
        $query = $this->wpdb->prepare(
            "SELECT data, content, settings FROM {$this->table} WHERE table_id = %d ORDER BY id ASC",
            $tableId
        );

        return (array) $this->wpdb->get_results($query);
    }
}
