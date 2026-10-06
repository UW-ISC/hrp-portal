<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Table;

use WPDT\PHPSQLParser\PHPSQLParser;

/**
 * Pagination seam for the server-side table engine.
 *
 * Owns the reading of the DataTables server-side pagination request
 * parameters (`start` / `length`) and the building of the vendor-specific
 * LIMIT/OFFSET clause fragments. The clause-merging/reassembly into the parsed
 * query stays inside
 * {@see \WPDataTables\Services\DataSource\MySqlQueryDataSource::construct()}
 * (it is interleaved with the WHERE/ORDER reassembly and the php-sql-parser
 * AST manipulation), as do the guard conditions that decide *whether* a limit
 * applies; only the cleanly-separable request read and fragment formatting live
 * here.
 *
 * @package WPDataTables\Services\Table
 */
class PaginationService
{
    /**
     * Read the requested server-side pagination window from the current
     * DataTables request.
     *
     * @return array{start:int, length:int}
     */
    public function readServerSideLimits()
    {
        return array(
            'start'  => isset($_POST['start']) ? (int) $_POST['start'] : 0,
            'length' => isset($_POST['length']) ? (int) $_POST['length'] : 0,
        );
    }

    /**
     * Build the vendor-specific LIMIT/OFFSET clause fragments for the server-side
     * query:
     *  - a plain `LIMIT n` (the `$wdtParameters['limit']` and display-length
     *    cases) when `$start` is null, and
     *  - an offset window (`LIMIT start, length`) for the DataTables paging case.
     *
     * The MySQL fragment is a parsed AST (merged into `$parsedQuery` by the
     * caller); the MSSQL/PostgreSQL fragments are raw clause strings appended to
     * the generated query string. Non-matching vendors return the empty string.
     *
     * @param string            $vendor the connection vendor (\Connection::$MYSQL|$MSSQL|$POSTGRESQL)
     * @param int|null          $start  the OFFSET, or null for a plain `LIMIT n`
     * @param int|string        $length the row count / LIMIT value
     * @param PHPSQLParser       $parser the shared SQL parser (for the MySQL AST)
     *
     * @return array{mysql:array|string, mssql:string, pg:string}
     */
    public function buildLimitClause($vendor, $start, $length, PHPSQLParser $parser)
    {
        $mysql = '';
        $mssql = '';
        $pg    = '';

        if ($start === null) {
            if ($vendor === \Connection::$MYSQL) {
                $mysql = $parser->parse(' LIMIT ' . $length, true);
            }

            if ($vendor === \Connection::$POSTGRESQL) {
                $pg = " LIMIT {$length}";
            }

            if ($vendor === \Connection::$MSSQL) {
                $mssql = " OFFSET 0 ROWS FETCH NEXT {$length} ROWS ONLY";
            }
        } else {
            if ($vendor === \Connection::$MYSQL) {
                $mysql = $parser->parse("LIMIT " . addslashes($start) . ", " .
                    addslashes($length), true);
            }

            if ($vendor === \Connection::$POSTGRESQL) {
                $pg = ' LIMIT ' . addslashes($length) . ' OFFSET ' . addslashes($start);
            }

            if ($vendor === \Connection::$MSSQL) {
                $mssql = ' OFFSET ' . addslashes($start) . ' ROWS FETCH NEXT ' .
                    addslashes($length) . ' ROWS ONLY';
            }
        }

        return array('mysql' => $mysql, 'mssql' => $mssql, 'pg' => $pg);
    }
}
