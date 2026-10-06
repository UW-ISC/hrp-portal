<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\DataSource;

use WDTException;
use WPDataTable;
use WPDataTableCache;

/**
 * XML data source adapter.
 *
 * Cache lookup, fetch+convert via `WPDataTable::sourceRenderData()`, the
 * `wpdatatables_filter_xml_array` hook, then the shared `arrayBasedConstruct()`.
 *
 * @package WPDataTables\Services\DataSource
 */
class XmlDataSource implements DataSourceInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws WDTException
     * @throws \Exception
     */
    public function read(WPDataTable $table, $source, array $wdtParameters = [])
    {
        $cache = WPDataTableCache::maybeCache($table->getCacheSourceData(), (int)$table->getWpId());
        if (!$cache) {
            if (!$source) {
                throw new WDTException('File you provided cannot be found.');
            }
            $XMLArray = WPDataTable::sourceRenderData($table, 'xml', $source);
        } else {
            $XMLArray = $cache;
        }

        $XMLArray = apply_filters('wpdatatables_filter_xml_array', $XMLArray, $table->getWpId(), $source);

        return $table->arrayBasedConstruct($XMLArray, $wdtParameters);
    }
}
