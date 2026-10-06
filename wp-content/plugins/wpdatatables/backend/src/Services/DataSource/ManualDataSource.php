<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\DataSource;

use WPDataTable;

/**
 * Manual / array data source adapter.
 *
 * Represents the "build a table from an in-memory named-data array" path
 * (manual tables and the integrations that hand wpDataTables a ready array:
 * woo-commerce, query-builder, ivyforms). The shared column builder itself —
 * `WPDataTable::arrayBasedConstruct()` — stays in the facade: it is the
 * construction core every other adapter calls back into, and it carries the
 * external free/pro build markers (`insertion #09`, the `Full version`
 * strip-block) that the release pipeline keys off inside
 * `source/class.wpdatatable.php`. This adapter therefore registers the source
 * type in the {@see DataSourceFactory} family and delegates into that builder,
 * giving the TableService a uniform way to resolve every source type.
 *
 * @package WPDataTables\Services\DataSource
 */
class ManualDataSource implements DataSourceInterface
{
    /**
     * {@inheritDoc}
     *
     * @param WPDataTable $table
     * @param mixed       $source        The raw named-data array of rows.
     * @param array       $wdtParameters
     *
     * @return mixed Whatever `WPDataTable::arrayBasedConstruct()` returns.
     */
    public function read(WPDataTable $table, $source, array $wdtParameters = [])
    {
        return $table->arrayBasedConstruct($source, $wdtParameters);
    }
}
