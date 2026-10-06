<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\DataSource;

use WPDataTable;
use WPDataTableCache;

/**
 * Google Sheets data source adapter.
 *
 * Cache lookup, fetch via `WPDataTable::sourceRenderData()` (which delegates to
 * the Google Sheets client / public-CSV path), the
 * `wpdatatables_filter_google_sheet_array` hook, then the shared
 * `arrayBasedConstruct()`.
 *
 * @package WPDataTables\Services\DataSource
 */
class GoogleSheetsDataSource implements DataSourceInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws \WDTException
     * @throws \Exception
     */
    public function read(WPDataTable $table, $source, array $wdtParameters = [])
    {
        $cache = WPDataTableCache::maybeCache($table->getCacheSourceData(), (int)$table->getWpId());
        if (!$cache) {
            $sheetArray = WPDataTable::sourceRenderData($table, 'google_spreadsheet', $source);
        } else {
            $sheetArray = $cache;
        }

        $sheetArray = apply_filters('wpdatatables_filter_google_sheet_array', $sheetArray, $table->getWpId(), $source);

        return $table->arrayBasedConstruct($sheetArray, $wdtParameters);
    }
}
