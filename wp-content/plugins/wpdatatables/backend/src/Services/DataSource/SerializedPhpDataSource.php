<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\DataSource;

use WPDataTable;
use WPDataTableCache;

/**
 * Serialized PHP data source adapter.
 *
 * Cache lookup, fetch+unserialize via `WPDataTable::sourceRenderData()`, the
 * `wpdatatables_filter_php_array` hook, then the shared `arrayBasedConstruct()`.
 *
 * @package WPDataTables\Services\DataSource
 */
class SerializedPhpDataSource implements DataSourceInterface
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
            $PHPArray = WPDataTable::sourceRenderData($table, 'serialized', $source);
        } else {
            $PHPArray = $cache;
        }

        $PHPArray = apply_filters('wpdatatables_filter_php_array', $PHPArray, $table->getWpId(), $source);

        return $table->arrayBasedConstruct($PHPArray, $wdtParameters);
    }
}
