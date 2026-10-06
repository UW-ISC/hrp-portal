<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Table;

/**
 * Sorting seam for the server-side table engine.
 *
 * Owns the cleanly-separable parts of the DataTables server-side sort request:
 * normalising the requested direction to a SQL keyword. The ORDER BY clause
 * building (including the foreign-key JOIN handling) stays interleaved inside
 * {@see \WPDataTables\Services\DataSource\MySqlQueryDataSource::construct()};
 * this class is the seam owner the future fine split will grow into.
 *
 * @package WPDataTables\Services\Table
 */
class SortService
{
    /**
     * Normalise a raw DataTables `order[i][dir]` value to an `ASC`/`DESC`
     * SQL keyword.
     *
     * Callers gate on the `in_array($dir, ['asc','desc'])` whitelist.
     *
     * @param string $rawDirection
     *
     * @return string `ASC` or `DESC`.
     */
    public function normalizeDirection($rawDirection)
    {
        return addslashes($rawDirection) === 'asc' ? 'ASC' : 'DESC';
    }
}
