<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Ai;

use Exception;
use WPDataTables\Services\Connection\ConnectionService;
use wpDataTableConstructor;

/**
 * Builds a privacy-safe database schema snapshot for SQL AI prompts.
 *
 * Only table/column names and types leave the site — never credentials, row
 * data, or full query history. Caps list sizes so prompts stay bounded.
 *
 * Preferred tables (from the current query / user description / WP core) are
 * pinned to the front of the snapshot so common tables like `wp_posts` are not
 * dropped when a site has many plugin tables.
 *
 * @package WPDataTables\Services\Ai
 */
class SchemaContextService
{
    /** Max table names included in the schema snapshot. */
    const MAX_TABLES = 80;

    /** Max tables for which column metadata is fetched. */
    const MAX_COLUMN_TABLES = 12;

    /** Max columns per table in the snapshot. */
    const MAX_COLUMNS_PER_TABLE = 40;

    /**
     * WP core table suffixes pinned when using the WordPress database.
     *
     * @var array<int, string>
     */
    const WP_CORE_SUFFIXES = [
        'posts',
        'postmeta',
        'comments',
        'commentmeta',
        'terms',
        'term_taxonomy',
        'term_relationships',
        'users',
        'usermeta',
        'options',
        'links',
    ];

    /** @var ConnectionService */
    private $connectionService;

    public function __construct(ConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }

    /**
     * @param string $connection   Connection ID (empty string = WP DB).
     * @param string $currentQuery Optional SQL used to prefer relevant tables.
     * @param string $hintText     Optional natural-language hint (description / prompt).
     * @return array{
     *     vendor: string,
     *     table_prefix: string,
     *     tables: array<int, string>,
     *     columns: array<string, array<int, array{name: string, type: string}>>
     * }
     */
    public function getContext(string $connection, string $currentQuery = '', string $hintText = ''): array
    {
        global $wpdb;

        $vendor = 'mysql';
        $prefix = isset($wpdb->prefix) ? (string) $wpdb->prefix : '';
        $isWpDb = true;

        if ($connection !== '' && $this->connectionService->isSeparate($connection)) {
            $vendor = $this->connectionService->getVendor($connection);
            $prefix = '';
            $isWpDb = false;
        }

        $tables = [];
        if (class_exists(wpDataTableConstructor::class)) {
            try {
                $tables = (array) wpDataTableConstructor::listMySQLTables($connection);
            } catch (Exception $e) {
                $tables = [];
            }
        }

        $tables = array_values(array_filter(array_map('strval', $tables)));
        sort($tables, SORT_STRING);

        $preferred = $this->resolvePreferredTables(
            $tables,
            $currentQuery,
            $hintText,
            $isWpDb ? $prefix : ''
        );

        $ordered = array_values(array_unique(array_merge($preferred, $tables)));
        $tablesCapped = array_slice($ordered, 0, self::MAX_TABLES);

        // Always fetch columns for preferred tables first (not alphabetical head).
        $columnTables = array_slice(
            array_values(array_unique(array_merge(
                $preferred,
                array_slice($tablesCapped, 0, self::MAX_COLUMN_TABLES)
            ))),
            0,
            self::MAX_COLUMN_TABLES
        );

        $columns = [];
        if ($columnTables !== [] && class_exists(wpDataTableConstructor::class)) {
            try {
                $listed = wpDataTableConstructor::listMySQLColumns($columnTables, $connection);
                $sorted = isset($listed['sortedColumns']) && is_array($listed['sortedColumns'])
                    ? $listed['sortedColumns']
                    : [];
                foreach ($columnTables as $table) {
                    $columns[$table] = $this->normaliseColumns($sorted[$table] ?? []);
                }
            } catch (Exception $e) {
                $columns = [];
            }
        }

        return [
            'vendor'       => $vendor,
            'table_prefix' => $prefix,
            'tables'       => $tablesCapped,
            'columns'      => $columns,
        ];
    }

    /**
     * Schema snapshot tailored to a query-builder wizard type.
     *
     * @param string $builderType One of wp|mysql.
     * @param string $connection  Connection ID (empty string = WP DB).
     * @param string $hintText    User description used to prefer relevant tables.
     * @return array{
     *     vendor: string,
     *     table_prefix: string,
     *     tables: array<int, string>,
     *     columns: array<string, array<int, array{name: string, type: string}>>
     * }
     */
    public function getContextForBuilder(
        string $builderType,
        string $connection = '',
        string $hintText = ''
    ): array {
        $context = $this->getContext($connection, '', $hintText);

        if ($builderType !== 'wp') {
            return $context;
        }

        $prefix  = $context['table_prefix'];
        $allowed = [];
        foreach (self::WP_CORE_SUFFIXES as $suffix) {
            $allowed[] = $prefix . $suffix;
        }

        $context['tables'] = array_values(array_filter(
            $context['tables'],
            static function ($table) use ($allowed) {
                return in_array($table, $allowed, true);
            }
        ));

        // Ensure column metadata exists for every remaining WP core table.
        $missingColumnTables = array_values(array_diff($context['tables'], array_keys($context['columns'])));
        if ($missingColumnTables !== [] && class_exists(wpDataTableConstructor::class)) {
            try {
                $listed = wpDataTableConstructor::listMySQLColumns($missingColumnTables, $connection);
                $sorted = isset($listed['sortedColumns']) && is_array($listed['sortedColumns'])
                    ? $listed['sortedColumns']
                    : [];
                foreach ($missingColumnTables as $table) {
                    $context['columns'][$table] = $this->normaliseColumns($sorted[$table] ?? []);
                }
            } catch (Exception $e) {
                // Keep whatever columns we already have.
            }
        }

        $context['columns'] = array_intersect_key(
            $context['columns'],
            array_flip($context['tables'])
        );

        return $context;
    }

    /**
     * Prefer tables mentioned in SQL / natural language, plus WP core on WP DB.
     *
     * @param array<int, string> $knownTables
     * @param string             $currentQuery
     * @param string             $hintText
     * @param string             $prefix       Empty when not WP DB.
     * @return array<int, string>
     */
    private function resolvePreferredTables(
        array $knownTables,
        string $currentQuery,
        string $hintText,
        string $prefix
    ): array {
        $preferred = $this->tablesMentionedInQuery($currentQuery, $knownTables);
        $preferred = array_merge($preferred, $this->tablesMentionedInHint($hintText, $knownTables));

        if ($prefix !== '') {
            foreach (self::WP_CORE_SUFFIXES as $suffix) {
                $coreTable = $prefix . $suffix;
                if (in_array($coreTable, $knownTables, true)) {
                    $preferred[] = $coreTable;
                }
            }
        }

        return array_values(array_unique(array_filter($preferred)));
    }

    /**
     * Match known table names (and common aliases) inside free-text hints.
     *
     * @param string             $hint
     * @param array<int, string> $knownTables
     * @return array<int, string>
     */
    private function tablesMentionedInHint(string $hint, array $knownTables): array
    {
        if ($hint === '' || $knownTables === []) {
            return [];
        }

        $mentioned = [];
        foreach ($knownTables as $table) {
            if ($table !== '' && stripos($hint, $table) !== false) {
                $mentioned[] = $table;
            }
        }

        // Common English → WP table suffixes (prefix-agnostic match against known tables).
        $aliases = [
            'posts'    => ['post', 'posts', 'page', 'pages'],
            'postmeta' => ['meta', 'custom field', 'custom fields'],
            'comments' => ['comment', 'comments'],
            'users'    => ['user', 'users', 'author', 'authors'],
            'terms'    => ['term', 'terms', 'category', 'categories', 'tag', 'tags', 'taxonomy'],
        ];

        $hintLower = strtolower($hint);
        foreach ($knownTables as $table) {
            foreach ($aliases as $suffix => $words) {
                if (!preg_match('/(?:^|_)' . preg_quote($suffix, '/') . '$/', $table)) {
                    continue;
                }
                foreach ($words as $word) {
                    if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $hintLower)) {
                        $mentioned[] = $table;
                        break 2;
                    }
                }
            }
        }

        return array_values(array_unique($mentioned));
    }

    /**
     * @param string             $query
     * @param array<int, string> $knownTables
     * @return array<int, string>
     */
    private function tablesMentionedInQuery(string $query, array $knownTables): array
    {
        if ($query === '' || $knownTables === []) {
            return [];
        }

        $mentioned = [];
        foreach ($knownTables as $table) {
            if ($table !== '' && stripos($query, $table) !== false) {
                $mentioned[] = $table;
            }
        }

        return $mentioned;
    }

    /**
     * @param mixed $rawColumns Constructor column list for one table.
     * @return array<int, array{name: string, type: string}>
     */
    private function normaliseColumns($rawColumns): array
    {
        $out = [];
        if (!is_array($rawColumns)) {
            return $out;
        }

        foreach ($rawColumns as $column) {
            if (is_string($column)) {
                // Values are often "table.column".
                $parts = explode('.', $column);
                $name = end($parts);
                $out[] = ['name' => (string) $name, 'type' => ''];
            } elseif (is_array($column)) {
                $name = (string) ($column['Field'] ?? $column['field'] ?? $column['column_name'] ?? $column['COLUMN_NAME'] ?? '');
                $type = (string) ($column['Type'] ?? $column['type'] ?? $column['data_type'] ?? $column['DATA_TYPE'] ?? '');
                if ($name !== '') {
                    $out[] = ['name' => $name, 'type' => $type];
                }
            }

            if (count($out) >= self::MAX_COLUMNS_PER_TABLE) {
                break;
            }
        }

        return $out;
    }
}
