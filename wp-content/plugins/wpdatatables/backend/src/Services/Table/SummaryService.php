<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Table;

use WPDataTables\Services\Tools\ToolsService;

use WPDT\PHPSQLParser\PHPSQLCreator;

/**
 * Footer-aggregate engine for tables (sum / avg / min / max).
 *
 * Owns the two aggregate code paths:
 *
 *  - {@see calcColumnsAggregateFuncs()} — the client-side path: compute the
 *    aggregates in PHP over the already-loaded data rows.
 *  - {@see computeServerSideAggregates()} — the server-side path: build and run
 *    the separate SUM/AVG/MIN/MAX queries (skipping LIMIT/ORDER/JOIN) and format
 *    the results into the DataTables JSON output.
 *
 * Both operate on the live {@see \WPDataTable} via its public accessors.
 *
 * @package WPDataTables\Services\Table
 */
class SummaryService
{
    /**
     * Compute the client-side aggregate function results over the table's
     * already-loaded data rows.
     *
     * The caller passes in the table's current `_aggregateFuncsRes` and receives
     * the updated array back (the global facade method assigns it straight back).
     *
     * @param \WPDataTable          $table
     * @param array<string, mixed>  $aggregateFuncsRes The table's current results cache.
     *
     * @return array<string, mixed> The updated results cache.
     */
    public function calcColumnsAggregateFuncs(\WPDataTable $table, array $aggregateFuncsRes)
    {
        if (empty($aggregateFuncsRes)) {
            $aggregateFuncsRes = array(
                'sum' => array(),
                'avg' => array(),
                'min' => array(),
                'max' => array()
            );
        }
        foreach ($table->getColumnKeys() as $columnKey) {
            if (
                in_array(
                    $columnKey,
                    array_unique(
                        array_merge(
                            $table->getSumColumns(),
                            $table->getAvgColumns(),
                            $table->getMinColumns(),
                            $table->getMaxColumns()
                        )
                    )
                )
            )
                foreach ($table->getDataRows() as $wdtRowDataArr) {
                    if (
                        in_array(
                            $columnKey,
                            array_unique(
                                array_merge(
                                    $table->getSumColumns(),
                                    $table->getAvgColumns()
                                )

                            )
                        )
                    ) {
                        if (!isset($aggregateFuncsRes['sum'][$columnKey])) {
                            $aggregateFuncsRes['sum'][$columnKey] = 0;
                        }

                        if ($wdtRowDataArr[$columnKey] != null && is_numeric($wdtRowDataArr[$columnKey])) {
                            $aggregateFuncsRes['sum'][$columnKey] += $wdtRowDataArr[$columnKey];
                        }
                    }
                    if (
                        in_array(
                            $columnKey,
                            $table->getMinColumns()
                        )
                    ) {
                        if (
                            !isset($aggregateFuncsRes['min'][$columnKey])
                            || ($wdtRowDataArr[$columnKey] < $aggregateFuncsRes['min'][$columnKey]
                                && is_numeric($wdtRowDataArr[$columnKey]))
                        ) {
                            $aggregateFuncsRes['min'][$columnKey] = $wdtRowDataArr[$columnKey];
                        }
                    }

                    if (
                        in_array(
                            $columnKey,
                            $table->getMaxColumns()
                        )
                    ) {
                        if (
                            !isset($aggregateFuncsRes['max'][$columnKey])
                            || ($wdtRowDataArr[$columnKey] > $aggregateFuncsRes['max'][$columnKey]
                                && is_numeric($wdtRowDataArr[$columnKey]))
                        ) {
                            $aggregateFuncsRes['max'][$columnKey] = $wdtRowDataArr[$columnKey];
                        }
                    }
                }

            if (in_array($columnKey, $table->getAvgColumns())) {
                $filteredRowsNumber = count(array_filter(array_column($table->getDataRows(), $columnKey)));
                $notNullRowNumber = $filteredRowsNumber !== 0 ? $filteredRowsNumber : count($table->getDataRows());
                $aggregateFuncsRes['avg'][$columnKey] = $aggregateFuncsRes['sum'][$columnKey] / $notNullRowNumber;
            }
        } //important

        return $aggregateFuncsRes;
    }

    /**
     * Build, execute and format the server-side footer aggregates into the
     * DataTables JSON output array.
     *
     * The many parameters are the construct-local SQL state the aggregate block
     * consumes; they are threaded through from
     * `MySqlQueryDataSource::construct()`. This signature can be narrowed once the
     * server-side processor owns the parse tree.
     *
     * @param \WPDataTable        $table
     * @param array               $output                 The base output array (draw, records, data keys).
     * @param array               $parsedQuery            The parsed main query.
     * @param PHPSQLCreator       $creator
     * @param string              $leftSysIdentifier
     * @param string              $rightSysIdentifier
     * @param array               $wdtParameters
     * @param array               $colObjs
     * @param mixed               $main_res_dataRows
     * @param string              $postgreSqlParsedSearch
     * @param string              $msSqlParsedSearch
     * @param mixed               $parsedOnlyOwnRows
     * @param bool                $isPostgreSql
     * @param bool                $isMSSql
     *
     * @return array The output array with the aggregate keys populated.
     */
    public function computeServerSideAggregates(
        \WPDataTable $table,
        array $output,
        array $parsedQuery,
        PHPSQLCreator $creator,
        $leftSysIdentifier,
        $rightSysIdentifier,
        array $wdtParameters,
        array $colObjs,
        $main_res_dataRows,
        $postgreSqlParsedSearch,
        $msSqlParsedSearch,
        $parsedOnlyOwnRows,
        $isPostgreSql,
        $isMSSql
    ) {
        global $wpdb;

        // If aggregate functions are requested
        $sumColumns = $table->getSumColumns();
        $avgColumns = $table->getAvgColumns();
        $maxColumns = $table->getMaxColumns();
        $minColumns = $table->getMinColumns();

        if (!empty($sumColumns) || !empty($avgColumns) || !empty($maxColumns) || !empty($minColumns)) {
            // Remove the LIMIT, ORDER BY and JOIN from query
            $functionsParsedQuery = $parsedQuery;
            unset($functionsParsedQuery['LIMIT']);
            unset($functionsParsedQuery['ORDER']);
            $functionsParsedQuery['FROM'] = array_slice($functionsParsedQuery['FROM'], 0, 1);

            if (!empty($sumColumns) || !empty($avgColumns)) {
                $functionsParsedQuery['SELECT'] = [];
                $output['sumAvgColumns'] = array_unique(array_merge($table->getSumColumns(), $table->getAvgColumns()), SORT_REGULAR);
                $output['sumFooterColumns'] = $table->getSumFooterColumns();
                $output['avgFooterColumns'] = $table->getAvgFooterColumns();

                foreach ($output['sumAvgColumns'] as $key => $columnTitle) {
                    if (isset($sumColumns[$key]) && $wdtParameters['data_types'][$columnTitle] == 'formula') {
                        $formulaColumnTitle = $sumColumns[$key];
                        if (strpos($wdtParameters['columnFormulas'][$formulaColumnTitle], 'sec(') !== false) {
                            $wdtParameters['columnFormulas'][$formulaColumnTitle] = str_replace('sec(', '1/cos(', $wdtParameters['columnFormulas'][$formulaColumnTitle]);
                        }
                        if (strpos($wdtParameters['columnFormulas'][$formulaColumnTitle], 'csc(') !== false) {
                            $wdtParameters['columnFormulas'][$formulaColumnTitle] = str_replace('csc(', '1/sin(', $wdtParameters['columnFormulas'][$formulaColumnTitle]);
                        }
                        $headersInFormula = ToolsService::getColHeadersInFormula($wdtParameters['columnFormulas'][$formulaColumnTitle], array_keys($colObjs));
                        $headers = ToolsService::sanitizeHeaders($headersInFormula);
                        $formula = str_replace(array('$',
                            '_',
                            '&'), '', strtr($wdtParameters['columnFormulas'][$formulaColumnTitle], $headers));
                        foreach ($headers as $header_key => $header_value) {
                            if (strpos($formula, $header_value) !== false) {
                                $formula = str_replace($header_value, '  IF(' . $header_key . ' IS NULL, 0,' . $header_key . ') ', $formula);
                            }
                        }
                        array_unshift($functionsParsedQuery['SELECT'], array(
                                'expr_type' => 'colref',
                                'base_expr' => 'SUM(' . $formula . ')
                                             AS ' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier,
                                'delim' => ','
                            )
                        );
                    } else {
                        if ($wdtParameters['data_types'][$columnTitle] != 'formula') {
                            array_unshift($functionsParsedQuery['SELECT'], array(
                                    'expr_type' => 'colref',
                                    'base_expr' => 'SUM(' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier . ')
                                                 AS ' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier,
                                    'delim' => ','
                                )
                            );
                        }
                    }
                }
                $lastElementInSelect = end($functionsParsedQuery['SELECT']);
                foreach ($functionsParsedQuery['SELECT'] as $selectKeys => $selectValues) {
                    $functionsParsedQuery['SELECT'][$selectKeys]['delim'] = ',';
                    if ($selectValues == $lastElementInSelect)
                        $functionsParsedQuery['SELECT'][$selectKeys]['delim'] = '';
                }

                $sumFunctionQuery = $creator->create($functionsParsedQuery);

                if ($isPostgreSql) {
                    $sumFunctionQuery .= ($postgreSqlParsedSearch ? ((strpos($sumFunctionQuery, 'WHERE') || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $postgreSqlParsedSearch) : '');
                }

                if ($isMSSql) {
                    $sumFunctionQuery .= ($msSqlParsedSearch ? ((strpos($sumFunctionQuery, 'WHERE') || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $msSqlParsedSearch) : '');
                }

                // execute query
                if (\Connection::isSeparate($table->connection)) {
                    $sql = \Connection::getInstance($table->connection);
                    $sumRow = $sql->getRow($sumFunctionQuery);
                    $sql = null;
                } else {
                    // querying using the WP driver otherwise
                    $sumRow = $wpdb->get_row($sumFunctionQuery, ARRAY_A);
                }
                foreach ($table->getSumColumns() as $columnTitle) {
                    if (is_null($sumRow[$columnTitle])) {
                        $sumRow[$columnTitle] = 0;
                    }
                    $output['sumColumnsValues'][$columnTitle] = $colObjs[$columnTitle]->returnCellValue($sumRow[$columnTitle]);
                }
                foreach ($table->getAvgColumns() as $columnTitle) {
                    if ($wdtParameters['data_types'][$columnTitle] == 'formula') {
                        $functionsParsedQuery['SELECT'] = [];
                        $formulaColumnTitle = $columnTitle;
                        if (strpos($wdtParameters['columnFormulas'][$formulaColumnTitle], 'sec(') !== false) {
                            $wdtParameters['columnFormulas'][$formulaColumnTitle] = str_replace('sec(', '1/cos(', $wdtParameters['columnFormulas'][$formulaColumnTitle]);
                        }
                        if (strpos($wdtParameters['columnFormulas'][$formulaColumnTitle], 'csc(') !== false) {
                            $wdtParameters['columnFormulas'][$formulaColumnTitle] = str_replace('csc(', '1/sin(', $wdtParameters['columnFormulas'][$formulaColumnTitle]);
                        }
                        $headersInFormula = ToolsService::getColHeadersInFormula($wdtParameters['columnFormulas'][$formulaColumnTitle], array_keys($colObjs));
                        $headers = ToolsService::sanitizeHeaders($headersInFormula);
                        $formula = str_replace(array('$',
                            '_',
                            '&'), '', strtr($wdtParameters['columnFormulas'][$formulaColumnTitle], $headers));
                        foreach ($headers as $header_key => $header_value) {
                            if (strpos($formula, $header_value) !== false) {
                                $formula = str_replace($header_value, ' IF(' . $header_key . ' IS NULL, 0,' . $header_key . ') ', $formula);
                            }
                        }
                        array_unshift($functionsParsedQuery['SELECT'], array(
                                'expr_type' => 'colref',
                                'base_expr' => 'AVG(' . $formula . ')
                                             AS ' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier,
                                'delim' => ''
                            )
                        );
                        $avgFunctionQuery = $creator->create($functionsParsedQuery);

                        if ($isPostgreSql) {
                            $avgFunctionQuery .= ($postgreSqlParsedSearch ? ((strpos($avgFunctionQuery, 'WHERE') || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $postgreSqlParsedSearch) : '');
                        }

                        if ($isMSSql) {
                            $avgFunctionQuery .= ($msSqlParsedSearch ? ((strpos($avgFunctionQuery, 'WHERE') || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $msSqlParsedSearch) : '');
                        }

                        // execute query
                        if (\Connection::isSeparate($table->connection)) {
                            $sql = \Connection::getInstance($table->connection);
                            $avgRow = $sql->getRow($avgFunctionQuery);
                            $sql = null;
                        } else {
                            // querying using the WP driver otherwise
                            $avgRow = $wpdb->get_row($avgFunctionQuery, ARRAY_A);
                        }

                        $output['avgColumnsValues'][$columnTitle] = $colObjs[$columnTitle]->returnCellValue($avgRow[$columnTitle]);
                    } else {
                        $floatCol = new \FloatWDTColumn();

                        $floatCol->setDecimalPlaces($colObjs[$columnTitle]->getDecimalPlaces());
                        $floatCol->setParentTable($table);
                        $nonNullValues = (int)$output['recordsFiltered'];
                        foreach ($main_res_dataRows as $row) {
                            if ($row[$columnTitle] == null) {
                                $nonNullValues--;
                            }
                        }
                        $output['avgColumnsValues'][$columnTitle] = $nonNullValues != 0 ?
                            $floatCol->returnCellValue(($sumRow[$columnTitle]) / $nonNullValues) : 0;
                    }
                }
                $output['sumAvgColumns'] = array_flip($output['sumAvgColumns']);
                $output['sumFooterColumns'] = array_flip($output['sumFooterColumns']);
                $output['avgFooterColumns'] = array_flip($output['avgFooterColumns']);
            }
            if (!empty($minColumns)) {
                $functionsParsedQuery['SELECT'] = [];
                $output['minColumns'] = $table->getMinColumns();
                $output['minFooterColumns'] = $table->getMinFooterColumns();
                foreach ($output['minColumns'] as $key => $columnTitle) {
                    if ($wdtParameters['data_types'][$columnTitle] == 'formula') {
                        $formulaColumnTitle = $minColumns[$key];
                        if (strpos($wdtParameters['columnFormulas'][$formulaColumnTitle], 'sec(') !== false) {
                            $wdtParameters['columnFormulas'][$formulaColumnTitle] = str_replace('sec(', '1/cos(', $wdtParameters['columnFormulas'][$formulaColumnTitle]);
                        }
                        if (strpos($wdtParameters['columnFormulas'][$formulaColumnTitle], 'csc(') !== false) {
                            $wdtParameters['columnFormulas'][$formulaColumnTitle] = str_replace('csc(', '1/sin(', $wdtParameters['columnFormulas'][$formulaColumnTitle]);
                        }
                        $headersInFormula = ToolsService::getColHeadersInFormula($wdtParameters['columnFormulas'][$formulaColumnTitle], array_keys($colObjs));
                        $headers = ToolsService::sanitizeHeaders($headersInFormula);
                        $formula = str_replace(array('$',
                            '_',
                            '&'), '', strtr($wdtParameters['columnFormulas'][$formulaColumnTitle], $headers));
                        foreach ($headers as $header_key => $header_value) {
                            if (strpos($formula, $header_value) !== false) {
                                $formula = str_replace($header_value, ' IF(' . $header_key . ' IS NULL, 0,' . $header_key . ') ', $formula);
                            }
                        }
                        array_unshift($functionsParsedQuery['SELECT'], array(
                                'expr_type' => 'colref',
                                'base_expr' => 'MIN(' . $formula . ')
                                             AS ' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier,
                                'delim' => ','
                            )
                        );
                    } else {
                        array_unshift($functionsParsedQuery['SELECT'], array(
                                'expr_type' => 'colref',
                                'base_expr' => 'MIN(' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier . ')
                                             AS ' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier,
                                'delim' => ','
                            )
                        );
                    }

                    if ($columnTitle === end($output['minColumns'])) {
                        $functionsParsedQuery['SELECT'][$key]['delim'] = '';
                    }
                }

                $minFunctionQuery = $creator->create($functionsParsedQuery);

                if ($isPostgreSql) {
                    $minFunctionQuery .= ($postgreSqlParsedSearch ? ((strpos($minFunctionQuery, 'WHERE') || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $postgreSqlParsedSearch) : '');
                }

                if ($isMSSql) {
                    $minFunctionQuery .= ($msSqlParsedSearch ? ((strpos($sumFunctionQuery, 'WHERE') || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $msSqlParsedSearch) : '');
                }

                if (\Connection::isSeparate($table->connection)) {
                    $sql = \Connection::getInstance($table->connection);
                    $minRow = $sql->getRow($minFunctionQuery);
                    $sql = null;
                } else {
                    $minRow = $wpdb->get_row($minFunctionQuery, ARRAY_A);
                }
                foreach ($table->getMinColumns() as $columnTitle) {
                    if (is_null($minRow[$columnTitle])) {
                        $minRow[$columnTitle] = 0;
                    }
                    $output['minColumnsValues'][$columnTitle] = $colObjs[$columnTitle]->returnCellValue($minRow[$columnTitle]);
                }
                $output['minColumns'] = array_flip($output['minColumns']);
                $output['minFooterColumns'] = array_flip($output['minFooterColumns']);
            }
            if (!empty($maxColumns)) {
                $functionsParsedQuery['SELECT'] = [];
                $output['maxColumns'] = $table->getMaxColumns();
                $output['maxFooterColumns'] = $table->getMaxFooterColumns();
                foreach ($output['maxColumns'] as $key => $columnTitle) {
                    if ($wdtParameters['data_types'][$columnTitle] == 'formula') {
                        $formulaColumnTitle = $maxColumns[$key];
                        if (strpos($wdtParameters['columnFormulas'][$formulaColumnTitle], 'sec(') !== false) {
                            $wdtParameters['columnFormulas'][$formulaColumnTitle] = str_replace('sec(', '1/cos(', $wdtParameters['columnFormulas'][$formulaColumnTitle]);
                        }
                        if (strpos($wdtParameters['columnFormulas'][$formulaColumnTitle], 'csc(') !== false) {
                            $wdtParameters['columnFormulas'][$formulaColumnTitle] = str_replace('csc(', '1/sin(', $wdtParameters['columnFormulas'][$formulaColumnTitle]);
                        }
                        $headersInFormula = ToolsService::getColHeadersInFormula($wdtParameters['columnFormulas'][$formulaColumnTitle], array_keys($colObjs));
                        $headers = ToolsService::sanitizeHeaders($headersInFormula);
                        $formula = str_replace(array('$',
                            '_',
                            '&'), '', strtr($wdtParameters['columnFormulas'][$formulaColumnTitle], $headers));
                        foreach ($headers as $header_key => $header_value) {
                            if (strpos($formula, $header_value) !== false) {
                                $formula = str_replace($header_value, ' IF(' . $header_key . ' IS NULL, 0,' . $header_key . ') ', $formula);
                            }
                        }
                        array_unshift($functionsParsedQuery['SELECT'], array(
                                'expr_type' => 'colref',
                                'base_expr' => 'MAX(' . $formula . ')
                                             AS ' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier,
                                'delim' => ','
                            )
                        );
                    } else {
                        array_unshift($functionsParsedQuery['SELECT'], array(
                                'expr_type' => 'colref',
                                'base_expr' => 'MAX(' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier . ')
                                             AS ' . $leftSysIdentifier . $columnTitle . $rightSysIdentifier,
                                'delim' => ','
                            )
                        );
                    }
                    if ($columnTitle === end($output['maxColumns'])) {
                        $functionsParsedQuery['SELECT'][$key]['delim'] = '';
                    }
                }

                $maxFunctionQuery = $creator->create($functionsParsedQuery);

                if ($isPostgreSql) {
                    $maxFunctionQuery .= ($postgreSqlParsedSearch ? ((strpos($maxFunctionQuery, 'WHERE') || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $postgreSqlParsedSearch) : '');
                }

                if ($isMSSql) {
                    $maxFunctionQuery .= ($msSqlParsedSearch ? ((strpos($maxFunctionQuery, 'WHERE') || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $msSqlParsedSearch) : '');
                }

                if (\Connection::isSeparate($table->connection)) {
                    $sql = \Connection::getInstance($table->connection);
                    $maxRow = $sql->getRow($maxFunctionQuery);
                    $sql = null;
                } else {
                    $maxRow = $wpdb->get_row($maxFunctionQuery, ARRAY_A);
                }
                foreach ($table->getMaxColumns() as $columnTitle) {
                    if (is_null($maxRow[$columnTitle])) {
                        $maxRow[$columnTitle] = 0;
                    }
                    $output['maxColumnsValues'][$columnTitle] = $colObjs[$columnTitle]->returnCellValue($maxRow[$columnTitle]);
                }
                $output['maxColumns'] = array_flip($output['maxColumns']);
                $output['maxFooterColumns'] = array_flip($output['maxFooterColumns']);
            }
        }

        return $output;
    }
}
