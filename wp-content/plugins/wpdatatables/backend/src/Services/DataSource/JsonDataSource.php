<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\DataSource;

use WPDataTable;
use WPDataTableCache;

/**
 * JSON data source adapter.
 *
 * A cache lookup, a fetch+decode via `WPDataTable::sourceRenderData()`, the
 * `wpdatatables_filter_json_array` hook, then handing off to the shared
 * `arrayBasedConstruct()` column builder.
 *
 * @package WPDataTables\Services\DataSource
 */
class JsonDataSource implements DataSourceInterface
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
            $jsonArray = WPDataTable::sourceRenderData($table, 'json', $source);
        } else {
            $jsonArray = $cache;
        }

        $jsonArray = apply_filters('wpdatatables_filter_json_array', $jsonArray, $table->getWpId(), $source);

        return $table->arrayBasedConstruct($jsonArray, $wdtParameters);
    }
}
