<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Repository\Template;

use WPDataTables\Repository\BaseRepositoryInterface;

/**
 * Persistence contract for the `wpdatatables_templates` table.
 *
 * @package WPDataTables\Repository\Template
 */
interface TemplateRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Fetch the raw `data` / `content` / `settings` rows for a table, ordered
     * by id (legacy `loadRowsDataFromDBTemplateAll` shape).
     *
     * @param int $tableId
     *
     * @return array<int, object>
     */
    public function findRowsByTableId(int $tableId): array;
}
