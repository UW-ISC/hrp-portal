<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\DataSource;

use WPDataTables\Services\Tools\ToolsService;

use WPDT\PHPSQLParser\PHPSQLCreator;
use WPDT\PHPSQLParser\PHPSQLParser;
use WPDT\PHPSQLParser\builders\FromBuilder;
use WPDataTable;
use WPDataTables\Services\Table\FilterService;
use WPDataTables\Services\Table\PaginationService;
use WPDataTables\Services\Table\SortService;
use WPDataTables\Services\Table\SummaryService;
use WPDataTables\Services\Table\TableService;
use WPDataTables\Services\Table\TableLoadService;
use WPDataTables\Services\Table\TableArrayBuilderService;

/**
 * SQL query data source adapter (MySQL / MSSQL / PostgreSQL).
 *
 * Owns the body of `WPDataTable::queryBasedConstruct()`: it parses and rewrites
 * the SQL via php-sql-parser, applies server-side limit/order/search/own-rows
 * clauses, executes the main query through the WP driver or a separate
 * {@see \Connection}, and either returns the DataTables server-side JSON payload
 * or hands the rows to the shared `WPDataTable::arrayBasedConstruct()` column
 * builder. The facade's `queryBasedConstruct()` is a one-line delegator onto
 * `construct()`. The footer-aggregate query building/execution and the vendor
 * LIKE helper are delegated to the {@see SummaryService} / {@see FilterService}
 * engine seams.
 *
 * @package WPDataTables\Services\DataSource
 */
class MySqlQueryDataSource implements DataSourceInterface
{
    /** @var FilterService */
    private $filterService;

    /** @var SummaryService */
    private $summaryService;

    /** @var PaginationService */
    private $paginationService;

    /** @var SortService */
    private $sortService;

    /** @var TableLoadService */
    private $tableLoadService;

    /** @var TableArrayBuilderService */
    private $tableArrayBuilderService;

    public function __construct(
        FilterService $filterService,
        SummaryService $summaryService,
        PaginationService $paginationService,
        SortService $sortService,
        TableLoadService $tableLoadService,
        TableArrayBuilderService $tableArrayBuilderService
    ) {
        $this->filterService = $filterService;
        $this->summaryService = $summaryService;
        $this->paginationService = $paginationService;
        $this->sortService = $sortService;
        $this->tableLoadService = $tableLoadService;
        $this->tableArrayBuilderService = $tableArrayBuilderService;
    }

    /**
     * {@inheritDoc}
     *
     * The generic strategy entry point; forwards to {@see construct()} with the
     * default arguments. The facade calls `construct()` directly so it can pass
     * the full four-argument signature.
     *
     * @throws \Exception
     */
    public function read(WPDataTable $table, $source, array $wdtParameters = [])
    {
        return $this->construct($table, $source, [], $wdtParameters, false);
    }

    /**
     * Build the table from a SQL query.
     *
     * @param WPDataTable $table
     * @param string      $query
     * @param array       $queryParams
     * @param array       $wdtParameters
     * @param bool        $init_read
     *
     * @return mixed JSON string (server-side AJAX) or the result of
     *               `arrayBasedConstruct()` (client-side render).
     * @throws \Exception
     */
    public function construct(WPDataTable $table, $query, array $queryParams = array(), array $wdtParameters = array(), $init_read = false)
    {
        global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9, $wpdb;


        $vendor = \Connection::getVendor($table->connection);
        $isMySql = $vendor === \Connection::$MYSQL;
        $isMSSql = $vendor === \Connection::$MSSQL;
        $isPostgreSql = $vendor === \Connection::$POSTGRESQL;

        $leftSysIdentifier = \Connection::getLeftColumnQuote($vendor);
        $rightSysIdentifier = \Connection::getRightColumnQuote($vendor);

        $query = wdtSanitizeQuery($query);
        $query = ToolsService::applyPlaceholders($query);

        $parser = new PHPSQLParser(false, true);
        $creator = new PHPSQLCreator();

        $query = apply_filters('wpdatatables_filter_query_before_limit', $query, $table->getWpId());

        $parsedQuery = $parser->parse($query, true);

        $foreignKeyJoin = '';
        $parsedOrderBy = '';
        $parsedLimit = '';
        $msSqlParsedLimit = '';
        $postgreSqlParsedLimit = '';
        $parsedSearch = '';
        $msSqlParsedSearch = '';
        $postgreSqlParsedSearch = '';
        $parsedOnlyOwnRows = '';

        $tableName = isset($parsedQuery['FROM']) ? $parsedQuery['FROM'][0]['table'] : '';
        $quotedTableName = \Connection::quoteQualifiedIdentifier($tableName, $vendor);
        $countFrom = $tableName;
        $fromRef = $this->resolveFromTableReference($parsedQuery, $vendor);
        if ($fromRef['name'] !== '') {
            $tableName = $fromRef['name'];
            $quotedTableName = $fromRef['quoted'];
            $countFrom = $fromRef['countFrom'];
        }

        if (isset($parsedQuery['DROP']) ||
            isset($parsedQuery['INSERT']) ||
            isset($parsedQuery['UPDATE']) ||
            isset($parsedQuery['DELETE']) ||
            isset($parsedQuery['EXPLAIN']) ||
            isset($parsedQuery['DESCRIBE']) ||
            isset($parsedQuery['CREATE INDEX']) ||
            isset($parsedQuery['CREATE TABLE'])) {
            throw new \Exception('SQL is not valid. Commands not allowed!');
        }

        // Adding limits if necessary
        if (!empty($wdtParameters['limit']) &&
            (strpos(strtolower($query), 'limit') === false) &&
            empty($wdtParameters['disable_limit'])
        ) {
            $limitClause = $this->paginationService->buildLimitClause($vendor, null, $wdtParameters['limit'], $parser);
            $parsedLimit = $limitClause['mysql'];
            $postgreSqlParsedLimit = $limitClause['pg'];
            $msSqlParsedLimit = $limitClause['mssql'];
        }

        // Server-side requests
        if ($table->serverSide()) {

            if (!isset($_POST['draw'])) {
                if (empty($wdtParameters['disable_limit'])) {
                    $lengthValue = $table->getDisplayLength();
                    if ($lengthValue != -1) {
                        $limitClause = $this->paginationService->buildLimitClause($vendor, null, $table->getDisplayLength(), $parser);
                        $parsedLimit = $limitClause['mysql'];
                        $postgreSqlParsedLimit = $limitClause['pg'];
                        $msSqlParsedLimit = $limitClause['mssql'];
                    }
                }
            } else {
                // Server-side params
                $aColumns = array_keys($wdtParameters['columnTitles']);

                $serverSideLimits = $this->paginationService->readServerSideLimits();
                $startValue = $serverSideLimits['start'];
                $lengthValue = $serverSideLimits['length'];

                if (isset($startValue) &&
                    $lengthValue != '-1' &&
                    empty($wdtParameters['disable_limit'])
                ) {
                    $limitClause = $this->paginationService->buildLimitClause($vendor, $startValue, $lengthValue, $parser);
                    $parsedLimit = $limitClause['mysql'];
                    $postgreSqlParsedLimit = $limitClause['pg'];
                    $msSqlParsedLimit = $limitClause['mssql'];
                }

                // Adding sort parameters for AJAX if necessary
                if (isset($_POST['order'])) {
                    $orderBy = "ORDER BY  ";
                    $orderDirection = 'ASC';
                    for ($i = 0; $i < count($_POST['order']); $i++) {

                        if (isset($_POST['order'][$i]['dir']) && in_array($_POST['order'][$i]['dir'], ['asc',
                                'desc'])) {
                            $orderDirection = $this->sortService->normalizeDirection($_POST['order'][$i]['dir']);
                        }
                        if (isset($wdtParameters['foreignKeyRule'][$_POST['columns'][$_POST['order'][$i]['column']]['name']])) {
                            $foreignKeyRule = $wdtParameters['foreignKeyRule'][$_POST['columns'][$_POST['order'][$i]['column']]['name']];
                            $columnName = $_POST['columns'][$_POST['order'][$i]['column']]['name'];
                            $joinedTable = $this->tableLoadService->loadTable($foreignKeyRule->tableId);
                            $joinedTableContent = ToolsService::applyPlaceholders($joinedTable->getTableContent());
                            $storeColumn = \WDTConfigController::loadSingleColumnFromDB($foreignKeyRule->storeColumnId);
                            $displayColumn = \WDTConfigController::loadSingleColumnFromDB($foreignKeyRule->displayColumnId);
                            if ($joinedTable->getTableType() == 'mysql' || $joinedTable->getTableType() == 'manual') {
                                $foreignKeyJoin .= 'FROM LEFT JOIN (' . $joinedTableContent . ') AS wdttemptbl' . $i .
                                    ' ON ' . $tableName . '.' . $columnName . ' = wdttemptbl' . $i . '.' . $storeColumn['orig_header'] . ' ';
                                $orderBy .= 'wdttemptbl' . $i . '.' . $displayColumn['orig_header'] . ' ' . $orderDirection . ', ';
                            } else {
                                $sortedForeignRows = $joinedTable->getDataRows();
                                usort($sortedForeignRows, function ($a, $b, $displayColumn) {
                                    return $a[$displayColumn['orig_header']] > $b[$displayColumn['orig_header']];
                                });
                                $sortedForeignRows = implode(array_map($sortedForeignRows, $storeColumn['orig_header']), ', ');
                                $orderBy .= 'FIELD (' . $columnName . ', ' . $sortedForeignRows . ') ' . $orderDirection . ', ';
                            }
                        } else {
                            if (isset($aColumns[$_POST['order'][$i]['column']]))
                                $orderBy .= $leftSysIdentifier . $aColumns[$_POST['order'][$i]['column']] . "{$rightSysIdentifier} " . $orderDirection . ", ";
                        }
                    }

                    $orderBy = substr_replace($orderBy, "", -2);
                    if ($orderBy == "ORDER BY") {
                        $orderBy = "";
                    }

                    if ($vendor === \Connection::$MYSQL) {
                        $parsedOrderBy = $parser->parse($orderBy);
                    }

                    if ($vendor === \Connection::$MSSQL) {
                        $msSqlParsedOrderBy = ' ' . $orderBy;
                    }

                    if ($vendor === \Connection::$POSTGRESQL) {
                        $postgreSqlParsedOrderBy = ' ' . $orderBy;
                    }
                }

                // Global search
                $search = '';
                if (!empty($_POST['search']['value'])) {
                    $search = " (";
                    for ($i = 0; $i < count($aColumns); $i++) {
                        if (isset($_POST['columns'][$i]) && $_POST['columns'][$i]['searchable'] == "true") {
                            if (in_array($wdtParameters['data_types'][$_POST['columns'][$i]['name']], array(
                                'date',
                                'datetime',
                                'time'
                            ))) {
                                continue;
                            } else {
                                if (is_null($wdtParameters['foreignKeyRule'][$_POST['columns'][$i]['name']])) {
                                    $globalSearchValue = sanitize_text_field(wp_unslash($_POST['search']['value']));
                                    $search .= $this->getLikeExpression($vendor, $quotedTableName, $leftSysIdentifier . $aColumns[$i] . $rightSysIdentifier, $globalSearchValue, $table->connection) . ' OR ';
                                } else {
                                    $foreignKeyRule = $wdtParameters['foreignKeyRule'][$_POST['columns'][$i]['name']];
                                    $joinedTable = $this->tableLoadService->loadTable($foreignKeyRule->tableId);
                                    $distinctValues = $joinedTable->getDistinctValuesForColumns($foreignKeyRule);
                                    $distinctValues = array_map('strtolower', $distinctValues);

                                    $filteredValues = preg_grep('~' . preg_quote(strtolower($_POST['search']['value']), '~') . '~', $distinctValues);

                                    if (!empty($filteredValues)) {
                                        $search .= $quotedTableName . ".{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} IN (" . ToolsService::buildInListFromKeys($filteredValues, $table->connection) . ")  OR ";
                                    } else {
                                        $globalSearchValue = sanitize_text_field(wp_unslash($_POST['search']['value']));
                                        $search .= $quotedTableName . ".{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} = " . ToolsService::prepareSearchLiteral($globalSearchValue, $table->connection) . ' OR ';
                                    }
                                }

                            }
                        }
                    }
                    $search = substr_replace($search, "", -3);
                    $search .= ')';
                }

                // Individual column filtering
                for ($i = 0; $i < count($aColumns); $i++) {

                    $columnSearchFromTable = false;
                    $columnSearchFromDefaultValue = false;

                    //Apply placeholders when they are set in filter predefined value
                    if (isset($wdtParameters['filterDefaultValue'][$i]))
                        $wdtParameters['filterDefaultValue'][$i] = ToolsService::applyPlaceholders($wdtParameters['filterDefaultValue'][$i]);

                    if (isset($_POST['columns'][$i]['search']) &&
                        $_POST['columns'][$i]['search']['value'] != '' &&
                        $_POST['columns'][$i]['search']['value'] != '|') {
                        $columnSearchFromTable = true;
                    }
                    if (($_POST['draw'] == 1 || $columnSearchFromTable == true) &&
                        (isset($wdtParameters['filterDefaultValue'][$i]) &&
                            $wdtParameters['filterDefaultValue'][$i] !== '' &&
                            $wdtParameters['filterDefaultValue'][$i] !== '|')) {
                        $columnSearchFromDefaultValue = true;
                    }

                    if (isset($_POST['columns'][$i]) && $_POST['columns'][$i]['searchable'] == true && ($columnSearchFromTable || $columnSearchFromDefaultValue)) {

                        $columnSearch = $columnSearchFromTable ? sanitize_text_field(wp_unslash($_POST['columns'][$i]['search']['value'])) : $wdtParameters['filterDefaultValue'][$i];
                        if (!empty($search)) {
                            $search .= ' AND ';
                        }
                        if (isset($wdtParameters['filterTypes'][$aColumns[$i]])) {
                            switch ($wdtParameters['filterTypes'][$aColumns[$i]]) {
                                case 'number-range':
                                    list($left, $right) = explode('|', $columnSearch);
                                    if ($left !== '') {
                                        if (get_option('wdtNumberFormat') == 1) {
                                            $left = str_replace(',', '.', str_replace('.', '', $left));
                                        } else {
                                            $left = str_replace(',', '', $left);
                                        }
                                        $left = (float)$left;
                                        $search .= $quotedTableName . ".{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} >= $left ";
                                    }
                                    if ($right !== '') {
                                        if (get_option('wdtNumberFormat') == 1) {
                                            $right = str_replace(',', '.', str_replace('.', '', $right));
                                        } else {
                                            $right = str_replace(',', '', $right);
                                        }
                                        $right = (float)$right;
                                        if (!empty($search) && $left !== '') {
                                            $search .= ' AND ';
                                        }
                                        $search .= $quotedTableName . ".{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} <= $right ";
                                    }
                                    break;
                                case 'date-range':
                                case 'time-range':
                                case 'datetime-range':
                                    list($left, $right) = explode('|', $columnSearch);

                                    if ($left && $right) {
                                        $search .= $quotedTableName . ".{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} BETWEEN {$table->getDateTimeExpression($vendor, $wdtParameters['filterTypes'][$aColumns[$i]], $left)} AND {$table->getDateTimeExpression($vendor, $wdtParameters['filterTypes'][$aColumns[$i]], $right)} ";
                                    } elseif ($left) {
                                        $search .= $quotedTableName . ".{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} >= {$table->getDateTimeExpression($vendor, $wdtParameters['filterTypes'][$aColumns[$i]], $left)} ";
                                    } elseif ($right) {
                                        $search .= $quotedTableName . ".{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} <= {$table->getDateTimeExpression($vendor, $wdtParameters['filterTypes'][$aColumns[$i]], $right)} ";
                                    }
                                    break;
                                case 'select':
                                    if ($columnSearch == 'possibleValuesAddEmpty') {
                                        $search .= $quotedTableName . ".{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} = '' OR {$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} IS NULL";
                                    } else {
                                        if ($wdtParameters['exactFiltering'][$aColumns[$i]] == 1) {
                                            $search .= $quotedTableName . ".{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} = " . ToolsService::prepareSearchLiteral($columnSearch, $table->connection) . ' ';
                                        } else {
                                            $search .= $this->getLikeExpression($vendor, $quotedTableName, $leftSysIdentifier . $aColumns[$i] . $rightSysIdentifier, $columnSearch, $table->connection);
                                        }
                                    }
                                    break;
                                case 'checkbox':
                                case 'multiselect':
                                    if ($wdtParameters['exactFiltering'][$aColumns[$i]] == 1) {
                                        // Trim regex parts for first and last one
                                        if (strpos($columnSearch, '$') !== false) {
                                            $checkboxSearches = explode('$|^', $columnSearch);
                                            $checkboxSearches[0] = substr($checkboxSearches[0], 1);
                                            if (count($checkboxSearches) > 1) {
                                                $checkboxSearches[count($checkboxSearches) - 1] = substr($checkboxSearches[count($checkboxSearches) - 1], 0, -1);
                                            } else {
                                                $checkboxSearches[0] = substr($checkboxSearches[0], 0, -1);
                                            }
                                        } else {
                                            $checkboxSearches = explode('|', $columnSearch);
                                        }
                                    } else {
                                        if (strpos($columnSearch, '||')) {
                                            $checkboxSearches = preg_split('/(?<!\|)\|(?!\|)/', $columnSearch);
                                        } else {
                                            $checkboxSearches = explode('|', $columnSearch);
                                        }
                                    }
                                    $j = 0;
                                    $useAndExactLogic = $wdtParameters['exactFiltering'][$aColumns[$i]] == 1 && $wdtParameters['andLogic'][$aColumns[$i]] == true;
                                    $search .= $useAndExactLogic ? " (" . $quotedTableName . ".{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} = '" : " (";
                                    foreach ($checkboxSearches as $checkboxSearch) {
                                        if ($useAndExactLogic) {
                                            ++$j;
                                            $escapedCheckboxSearch = substr(
                                                ToolsService::prepareSearchLiteral($checkboxSearch, $table->connection),
                                                1,
                                                -1
                                            );
                                            if (count($checkboxSearches) != $j) {
                                                $search .= $escapedCheckboxSearch . ", ";
                                            } else {
                                                $search .= $escapedCheckboxSearch . "' ";
                                            }
                                        } else {
                                            if ($j > 0) {
                                                $search .= $wdtParameters['andLogic'][$aColumns[$i]] == true ? " AND " : " OR ";
                                            }

                                            if ($wdtParameters['exactFiltering'][$aColumns[$i]] == 1) {
                                                $search .= $quotedTableName . ".{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} = " . ToolsService::prepareSearchLiteral($checkboxSearch, $table->connection) . ' ';
                                            } else {
                                                $search .= $this->getLikeExpression($vendor, $quotedTableName, $leftSysIdentifier . $aColumns[$i] . $rightSysIdentifier, $checkboxSearch, $table->connection);
                                            }

                                            $j++;
                                        }
                                    }
                                    $search .= ") ";
                                    break;
                                case 'text':
                                case 'number':
                                    if (is_null($wdtParameters['foreignKeyRule'][$_POST['columns'][$i]['name']])) {
                                        if ($wdtParameters['exactFiltering'][$aColumns[$i]] == 1) {
                                            $search .= $quotedTableName . ".{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} = " . ToolsService::prepareSearchLiteral($columnSearch, $table->connection) . ' ';
                                        } else {
                                            if ($wdtParameters['filterTypes'][$aColumns[$i]] == 'number') {
                                                $search .= $this->getLikeExpression($vendor, $quotedTableName, $leftSysIdentifier . $aColumns[$i] . $rightSysIdentifier, $columnSearch, $table->connection, '', '%');
                                            } else {
                                                $search .= $this->getLikeExpression($vendor, $quotedTableName, $leftSysIdentifier . $aColumns[$i] . $rightSysIdentifier, $columnSearch, $table->connection);
                                            }
                                        }
                                    } else {
                                        $foreignKeyRule = $wdtParameters['foreignKeyRule'][$_POST['columns'][$i]['name']];
                                        $joinedTable = $this->tableLoadService->loadTable($foreignKeyRule->tableId);
                                        $distinctValues = $joinedTable->getDistinctValuesForColumns($foreignKeyRule);
                                        $distinctValues = array_map('strtolower', $distinctValues);

                                        if ($wdtParameters['exactFiltering'][$aColumns[$i]] == 1) {
                                            $filteredValues = preg_grep('~^' . preg_quote(strtolower($columnSearch), null) . '$~', $distinctValues);
                                        } else {
                                            $filteredValues = preg_grep('~' . preg_quote(strtolower($columnSearch), '~') . '~', $distinctValues);
                                        }

                                        if (!empty($filteredValues)) {
                                            $search .= $quotedTableName . ".{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} IN (" . ToolsService::buildInListFromKeys($filteredValues, $table->connection) . ")";
                                        } else {
                                            $columnSearch = sanitize_text_field(wp_unslash($columnSearch));
                                            $search .= $quotedTableName . ".{$leftSysIdentifier}" . $aColumns[$i] . "{$rightSysIdentifier} = " . ToolsService::prepareSearchLiteral($columnSearch, $table->connection) . ' ';
                                        }
                                    }
                                    break;
                                default:
                                    $search .= $this->getLikeExpression($vendor, $quotedTableName, $leftSysIdentifier . $aColumns[$i] . $rightSysIdentifier, $columnSearch, $table->connection);
                            }
                        }
                    }
                }

                if ($search) {
                    if ($isMySql) {
                        $parsedSearch = $parser->parse('WHERE ' . $search);
                    }

                    if ($isMSSql) {
                        $msSqlParsedSearch = ' ' . $search;
                    }

                    if ($isPostgreSql) {
                        $postgreSqlParsedSearch = ' ' . $search;
                    }
                }

            }

        }

        // Add the filtering by user ID column, if requested
        if ((!isset($_POST['showAllRows']) && $table->getOnlyOwnRows()) || (isset($_POST['showAllRows']) && $table->getOnlyOwnRows() && !$table->isShowAllRows())) {
            $userIdColumnCondition = "WHERE {$leftSysIdentifier}" . $table->getUserIdColumn() . "{$rightSysIdentifier} = " . get_current_user_id();
            $parsedOnlyOwnRows = $parser->parse($userIdColumnCondition);
        }

        // The serverside return scenario
        if ($table->isAjaxReturn()) {

            /**
             * 1. Forming the query
             */

            if ($isMySql) {
                array_unshift($parsedQuery['SELECT'], array(
                        'expr_type' => 'reserved',
                        'alias' => '',
                        'base_expr' => 'SQL_CALC_FOUND_ROWS',
                        'sub_tree' => '',
                        'delim' => ''
                    )
                );
            } else if ($isMSSql || $isPostgreSql) {
                array_unshift($parsedQuery['SELECT'], array(
                        'expr_type' => 'reserved',
                        'alias' => '',
                        'base_expr' => 'COUNT(*) OVER() as count',
                        'sub_tree' => '',
                        'delim' => ','
                    )
                );
            }

            if ($foreignKeyJoin) {

                $parsedForeignKeyJoin = $parser->parse($foreignKeyJoin);
                $parsedQuery['FROM'][] = $parsedForeignKeyJoin['FROM'][1];

                foreach ($parsedQuery['SELECT'] as &$selectClause) {
                    if ($selectClause['expr_type'] == 'colref') {
                        if (strpos($selectClause['base_expr'], '.') === false) {
                            $selectClause['base_expr'] = $tableName . '.' . $selectClause['base_expr'];
                        }
                    }
                }

                if (isset($parsedQuery['WHERE'])) {
                    foreach ($parsedQuery['WHERE'] as &$whereClause) {
                        if ($whereClause['expr_type'] == 'colref') {
                            if (strpos($whereClause['base_expr'], '.') === false) {
                                $whereClause['base_expr'] = $tableName . '.' . $whereClause['base_expr'];
                            }
                        }
                    }
                }
            }

            if ($vendor === \Connection::$MYSQL) {
                if ($parsedOrderBy) {
                    if (isset($parsedQuery['ORDER'])) {
                        array_unshift($parsedQuery['ORDER'], $parsedOrderBy['ORDER'][0]);
                    } else {
                        $parsedQuery = array_merge($parsedQuery, $parsedOrderBy);
                    }
                }

                if ($parsedSearch) {
                    if (isset($parsedQuery['WHERE'])) {
                        $parsedQuery['WHERE'][] = [
                            'expr_type' => 'operator',
                            'base_expr' => 'AND',
                            'sub_tree' => false
                        ];
                        $parsedQuery['WHERE'] = array_merge($parsedQuery['WHERE'], $parsedSearch['WHERE']);
                    } else {
                        $parsedQuery['WHERE'] = $parsedSearch['WHERE'];
                    }
                }
            }


            if ($parsedOnlyOwnRows) {
                if (isset($parsedQuery['WHERE'])) {
                    $parsedQuery['WHERE'][] = ['expr_type' => 'operator', 'base_expr' => 'AND', 'sub_tree' => false];
                    $parsedQuery['WHERE'] = array_merge($parsedQuery['WHERE'], $parsedOnlyOwnRows['WHERE']);
                } else {
                    $parsedQuery['WHERE'] = $parsedOnlyOwnRows['WHERE'];
                }
            }

            if ($isMySql) {
                if ($parsedLimit) {
                    $parsedQuery = array_merge($parsedQuery, $parsedLimit);
                }
            }

            /**
             * 2. Executing the queries
             */
            // The main query
            $query = $creator->create($parsedQuery);

            // Add Limit Rule if Vendor is MSSQL
            if ($isMSSql) {
                $query .= ($msSqlParsedSearch ? ((strpos($query, 'WHERE') || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $msSqlParsedSearch) : '') . $msSqlParsedOrderBy . $msSqlParsedLimit;
            }

            // Add Limit Rule if Vendor is PostgreSQL
            if ($isPostgreSql) {
                $query .= ($postgreSqlParsedSearch ? ((strpos($query, 'WHERE') || $parsedOnlyOwnRows ? ' AND ' : ' WHERE ') . $postgreSqlParsedSearch) : '') . $postgreSqlParsedOrderBy . $postgreSqlParsedLimit;
            }

            $query = apply_filters('wpdatatables_filter_mysql_query', $query, $table->getWpId());

            if (\Connection::isSeparate($table->connection)) {
                $main_res_dataRows = $table->getDBConnection()->getAssoc($query, $queryParams);
            } else {
                // querying using the WP driver otherwise
                $main_res_dataRows = $wpdb->get_results($query, ARRAY_A);
            }
            // result length after filtering
            if (\Connection::isSeparate($table->connection)) {
                if ($isMySql) {
                    $resultLength = $table->getDBConnection()->getField('SELECT FOUND_ROWS()');
                } elseif (($isMSSql || $isPostgreSql) && !empty($main_res_dataRows)) {
                    $resultLength = $main_res_dataRows[0]['count'];
                } else {
                    $resultLength = 0;
                }
            } else {
                // querying using the WP driver otherwise
                $resultLength = $wpdb->get_row('SELECT FOUND_ROWS()', ARRAY_A);
                $resultLength = $resultLength['FOUND_ROWS()'];
            }
            // total data length
            if (\Connection::isSeparate($table->connection)) {
                $totalLengthQuery = 'SELECT COUNT(*) FROM ' . $countFrom;
                $totalLengthQuery = apply_filters('wpdatatables_filter_total_length_query', $totalLengthQuery, $table->getWpId());
                // If "Only own rows" options is defined, do not count other user's rows
                if (isset($userIdColumnCondition)) {
                    $totalLengthQuery .= ' ' . $userIdColumnCondition;
                }
                $totalLength = $table->getDBConnection()->getField($totalLengthQuery);
            } else {
                // querying using the WP driver otherwise
                $totalLengthQuery = 'SELECT COUNT(*) as cnt_total FROM ' . $countFrom;
                $totalLengthQuery = apply_filters('wpdatatables_filter_total_length_query', $totalLengthQuery, $table->getWpId());
                // If "Only own rows" options is defined, do not count other user's rows
                if (isset($userIdColumnCondition)) {
                    $totalLengthQuery .= ' ' . $userIdColumnCondition;
                }
                $totalLength = $wpdb->get_row($totalLengthQuery, ARRAY_A);
                $totalLength = $totalLength['cnt_total'];
            }

            /**
             * 3. Forming the output
             */
            // base array
            $output = array(
                "draw" => (int)$_POST['draw'],
                "recordsTotal" => $totalLength,
                "recordsFiltered" => $resultLength ? $resultLength : 0,
                "data" => array()
            );

            // Create the supplementary array of column objects
            $colObjs = $this->tableArrayBuilderService->prepareColumns($table, $wdtParameters);

            // reformat output array and reorder as user wanted
            $output['data'] = $this->tableArrayBuilderService->prepareOutputData($table, $main_res_dataRows, $wdtParameters, $colObjs);
            $output['data'] = apply_filters('wpdatatables_custom_prepare_output_data', $output['data'], $table, $main_res_dataRows, $wdtParameters, $colObjs);

            // Footer aggregates (sum/avg/min/max) are built, executed and
            // formatted by the SummaryService engine seam.
            $output = $this->summaryService->computeServerSideAggregates(
                $table,
                $output,
                $parsedQuery,
                $creator,
                $leftSysIdentifier,
                $rightSysIdentifier,
                $wdtParameters,
                $colObjs,
                $main_res_dataRows,
                $postgreSqlParsedSearch,
                $msSqlParsedSearch,
                $parsedOnlyOwnRows,
                $isPostgreSql,
                $isMSSql
            );

            /**
             * 4. Returning the result
             */
            return json_encode($output);
        } else {

            if ($isMySql) {
                if ($parsedLimit) {
                    $parsedQuery = array_merge($parsedQuery, $parsedLimit);
                }
            }

            if ($parsedOnlyOwnRows) {
                if (isset($parsedQuery['WHERE'])) {
                    $parsedQuery['WHERE'][] = ['expr_type' => 'operator', 'base_expr' => 'AND', 'sub_tree' => false];
                    $parsedQuery['WHERE'] = array_merge($parsedQuery['WHERE'], $parsedOnlyOwnRows['WHERE']);
                } else {
                    $parsedQuery['WHERE'] = $parsedOnlyOwnRows['WHERE'];
                }
            }

            $query = $creator->create($parsedQuery);

            // Add Limit Rule if Vendor is MSSQL
            if ($isMSSql) {
                if ($msSqlParsedLimit) {
                    $defaultOrder = isset($parsedQuery['ORDER']) ? '' : ' ORDER BY(SELECT NULL) ';
                    $query .= $defaultOrder . $msSqlParsedLimit;
                }
            }

            if ($isPostgreSql) {
                if ($postgreSqlParsedLimit) {
                    $query .= $postgreSqlParsedLimit;
                }
            }

            // Getting the query result
            if (\Connection::isSeparate($table->connection)) {
                $query = apply_filters('wpdatatables_filter_mysql_query', $query, $table->getWpId());
                $res_dataRows = $table->getDBConnection()->getAssoc($query, $queryParams);
                $mysql_error = $table->getDBConnection()->getLastError();
            } else {
                $query = apply_filters('wpdatatables_filter_mysql_query', $query, $table->getWpId());
                $res_dataRows = $wpdb->get_results($query, ARRAY_A);
                $mysql_error = $wpdb->last_error;
            }

            if (is_array($res_dataRows) && count($res_dataRows) > 2000) {
                $table->enableServerProcessing();
            }

            // If this is the table initialization from WP-admin, and no data is returned, throw an exception
            if ($init_read && empty($res_dataRows)) {
                if (!strpos($mysql_error, 'doesn\'t exist')) {
                    $msg = __('No data fetched!  <br/> To create a wpDataTable, at least one row of data is required before saving. This ensures the table is generated correctly. If needed, you can remove the row later. <br/>', 'wpdatatables');
                }
                $msg .= '<br/><br/>' . __('Rendered query: ', 'wpdatatables') . '<strong>' . $query . '</strong><br/>';
                if (!empty($mysql_error)) {
                    $msg .= __(' MySQL said: ', 'wpdatatables') . $mysql_error;
                }
                throw new \Exception($msg);
            }

            // Sending the array to arrayBasedConstruct
            return $this->tableArrayBuilderService->buildFromArray($table, $res_dataRows, $wdtParameters);

        }
    }

    /**
     * Resolve the SQL qualifier for server-side filter/search column references.
     *
     * @param array  $parsedQuery Parsed SELECT statement.
     * @param string $vendor      Database vendor identifier.
     *
     * @return array{name: string, quoted: string, countFrom: string}
     */
    private function resolveFromTableReference(array $parsedQuery, $vendor)
    {
        $from = $parsedQuery['FROM'][0] ?? null;
        if (!$from) {
            return array(
                'name'      => '',
                'quoted'    => '',
                'countFrom' => '',
            );
        }

        $exprType = $from['expr_type'] ?? '';
        if ($exprType === 'subquery') {
            $alias = $from['alias']['name'] ?? '';
            // PHPSQLCreator only builds complete statements, so a bare FROM has to go through FromBuilder.
            $fromBuilder = new FromBuilder();
            $countFrom = trim(substr($fromBuilder->build(array($from)), strlen('FROM')));

            return array(
                'name'      => $alias,
                'quoted'    => \Connection::quoteQualifiedIdentifier($alias, $vendor),
                'countFrom' => $countFrom,
            );
        }

        $alias = $from['alias']['name'] ?? false;
        $table = $from['table'] ?? '';

        if ($alias) {
            return array(
                'name'      => $alias,
                'quoted'    => \Connection::quoteQualifiedIdentifier($alias, $vendor),
                'countFrom' => $table,
            );
        }

        return array(
            'name'      => $table,
            'quoted'    => \Connection::quoteQualifiedIdentifier($table, $vendor),
            'countFrom' => $table,
        );
    }

    /**
     * Return the vendor-specific LIKE expression. Thin delegator onto
     * {@see FilterService::getLikeExpression()}; kept private so the
     * WHERE-building call sites are unchanged.
     *
     * @param string $vendor
     * @param string $table
     * @param string $column
     * @param string $rawValue
     * @param mixed  $connection
     * @param string $prefix
     * @param string $suffix
     *
     * @return string|void
     */
    private function getLikeExpression(
        $vendor,
        $table,
        $column,
        $rawValue,
        $connection = null,
        $prefix = '%',
        $suffix = '%'
    )
    {
        return $this->filterService->getLikeExpression(
            $vendor,
            $table,
            $column,
            $rawValue,
            $connection,
            $prefix,
            $suffix
        );
    }
}
