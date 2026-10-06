<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\DataSource;

use WPDataTable;
use WPDataTableCache;

/**
 * Nested JSON data source adapter.
 *
 * Cache lookup, fetch+flatten via `WPDataTable::sourceRenderData()` (which
 * drives the `WDTNestedJson` reader), the `wpdatatables_filter_nested_json_array`
 * hook, then the shared `arrayBasedConstruct()`.
 *
 * @package WPDataTables\Services\DataSource
 */
class NestedJsonDataSource implements DataSourceInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws \Exception
     */
    public function read(WPDataTable $table, $source, array $wdtParameters = [])
    {
        $cache = WPDataTableCache::maybeCache($table->getCacheSourceData(), (int)$table->getWpId());
        if (!$cache) {
            $jsonArray = WPDataTable::sourceRenderData($table, 'nested_json', $source);
        } else {
            $jsonArray = $cache;
        }

        $jsonArray = apply_filters('wpdatatables_filter_nested_json_array', $jsonArray, $table->getWpId(), $source);

        return $table->arrayBasedConstruct($jsonArray, $wdtParameters);
    }
}
