<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Table;

use WPDataTable;
use WPDataTables\Services\DataSource\MySqlQueryDataSource;

/**
 * Server-side data-path processor.
 *
 * The entry point for query-based table construction — the frontend
 * `wpdatatables_get_data` / `get_wdtable` hot path and the admin first-load
 * preview both flow through here. For `mysql` / `manual` tables it dispatches to
 * the {@see MySqlQueryDataSource} engine, which builds and runs the SQL and
 * either returns the DataTables server-side JSON payload (when a `draw` request
 * is in flight) or hands the rows to the shared column builder for client-side
 * rendering.
 *
 * This is a thin dispatch seam so the orchestrator ({@see TableService}) and the
 * REST controllers have one place to obtain server-side data. The interleaved
 * request parsing (filter/sort/pagination) stays inside the data source; the
 * {@see FilterService}/{@see SortService}/{@see PaginationService} seams are the
 * homes the future fine split grows into.
 *
 * @package WPDataTables\Services\Table
 */
class ServerSideProcessor
{
    /** @var MySqlQueryDataSource */
    private $mysqlQueryDataSource;

    public function __construct(MySqlQueryDataSource $mysqlQueryDataSource)
    {
        $this->mysqlQueryDataSource = $mysqlQueryDataSource;
    }

    /**
     * Build the table from its query, returning either the DataTables
     * server-side JSON string or the client-side `arrayBasedConstruct()` result.
     *
     * @param WPDataTable $table
     * @param string      $query
     * @param array       $queryParams
     * @param array       $wdtParameters
     * @param bool        $init_read
     *
     * @return mixed
     * @throws \Exception
     */
    public function getData(WPDataTable $table, $query, array $queryParams = array(), array $wdtParameters = array(), $init_read = false)
    {
        return $this->mysqlQueryDataSource->construct($table, $query, $queryParams, $wdtParameters, $init_read);
    }
}
