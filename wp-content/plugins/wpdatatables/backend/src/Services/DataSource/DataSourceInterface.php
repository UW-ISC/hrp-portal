<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\DataSource;

use WPDataTable;

/**
 * Strategy contract for a pluggable table data source.
 *
 * Each adapter owns the acquisition of a table's raw data for one source type
 * (JSON, nested JSON, serialized PHP, Google Sheets, XML, Excel/CSV …): cache
 * lookup, remote/file fetch, and the per-type `apply_filters` hook. It then
 * hands the resulting named-data array back to the
 * {@see WPDataTable::arrayBasedConstruct()} column builder, which is the shared
 * construction core. The facade's `*BasedConstruct` methods are thin one-line
 * delegators onto these strategies.
 *
 * @package WPDataTables\Services\DataSource
 */
interface DataSourceInterface
{
    /**
     * Acquire the source data and construct the table from it.
     *
     * @param WPDataTable $table        The table being built (mutated in place
     *                                  by `arrayBasedConstruct`).
     * @param mixed       $source       The source descriptor (URL, JSON string,
     *                                  sheet URL, parsed params …) for the type.
     * @param array       $wdtParameters Construction parameters forwarded to
     *                                  `arrayBasedConstruct`.
     *
     * @return mixed Whatever `WPDataTable::arrayBasedConstruct()` returns.
     */
    public function read(WPDataTable $table, $source, array $wdtParameters = []);
}
