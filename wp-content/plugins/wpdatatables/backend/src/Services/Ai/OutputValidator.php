<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Ai;

/**
 * Sanitises AI responses before they reach the client.
 *
 * Two guards:
 *  - {@see normalizeColumnType()} maps any AI-invented column type back to a
 *    type wpDataTables actually understands (defaulting to `string`).
 *  - {@see filterToSchema()} strips any column reference the model invented that
 *    is not present in the real table schema (hallucination guard) — used by the
 *    Chart Suggester, where the AI must only reference existing columns.
 *  - {@see sanitizeSql()} keeps Query Assistant output to a single read-only
 *    SELECT / WITH ... SELECT statement.
 *
 * @package WPDataTables\Services\Ai
 */
class OutputValidator
{
    /** Column types wpDataTables recognises for a generated scaffold. */
    const ALLOWED_TYPES = ['string', 'int', 'float', 'date', 'datetime', 'time', 'url', 'email'];

    /**
     * Common AI / SQL aliases mapped to {@see ALLOWED_TYPES}.
     *
     * @var array<string, string>
     */
    const TYPE_ALIASES = [
        'str'       => 'string',
        'text'      => 'string',
        'varchar'   => 'string',
        'char'      => 'string',
        'integer'   => 'int',
        'whole'     => 'int',
        'numeric'   => 'float',
        'number'    => 'float',
        'decimal'   => 'float',
        'double'    => 'float',
        'real'      => 'float',
        'money'     => 'float',
        'currency'  => 'float',
        'timestamp' => 'datetime',
        'uri'       => 'url',
        'link'      => 'url',
        'mail'      => 'email',
    ];

    /**
     * Normalise a single AI-generated column descriptor.
     *
     * Coerces the `type` to an allowed value, forces the boolean flags, and
     * trims the textual fields. Unknown shapes degrade gracefully rather than
     * throwing — this runs on untrusted model output.
     *
     * @param array<string, mixed> $column
     * @return array<string, mixed>
     */
    public function normalizeColumnType(array $column): array
    {
        $type = isset($column['type']) ? strtolower((string) $column['type']) : 'string';
        if (isset(self::TYPE_ALIASES[$type])) {
            $type = self::TYPE_ALIASES[$type];
        }
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            $type = 'string';
        }

        return [
            'name'     => isset($column['name']) ? sanitize_key((string) $column['name']) : '',
            'type'     => $type,
            'filter'   => !empty($column['filter']),
            'sortable' => !isset($column['sortable']) || !empty($column['sortable']),
            'hint'     => isset($column['hint']) ? sanitize_text_field((string) $column['hint']) : '',
        ];
    }

    /** Chart types the Chart Type Suggester may return. */
    const ALLOWED_CHART_TYPES = ['line', 'bar', 'column', 'area', 'pie', 'donut', 'scatter', 'mixed'];

    /** Render engines the Chart Type Suggester may return. */
    const ALLOWED_CHART_ENGINES = ['google', 'highcharts', 'chartjs', 'apexcharts', 'highstock'];

    /** Internal wpDataTables columns to deprioritise as chart axes. */
    const INTERNAL_CHART_COLUMNS = [
        'wdt_ID',
        'wdt_created_by',
        'wdt_created_at',
        'wdt_last_edited_by',
        'wdt_last_edited_at',
    ];

    /** Label / X-axis types accepted by the chart wizard (first series column). */
    const CHART_LABEL_TYPES = ['string', 'date', 'datetime', 'time', 'link', 'select'];

    /** Series / Y-axis types accepted by the chart wizard. */
    const CHART_SERIES_TYPES = ['int', 'float', 'formula'];

    /**
     * Drop any column name not present in the supplied allow-list of real names.
     *
     * @param array<int, string> $names      Candidate column names from the AI.
     * @param array<int, string> $allowedNames Real column names from the schema.
     * @return array<int, string> The candidates that exist in the schema, reindexed.
     */
    public function filterToSchema(array $names, array $allowedNames): array
    {
        $allowed = array_map('strval', $allowedNames);

        return array_values(array_filter(
            array_map('strval', $names),
            static function ($name) use ($allowed) {
                return in_array($name, $allowed, true);
            }
        ));
    }

    /**
     * Normalise Chart Type Suggester output and strip hallucinated column names.
     *
     * @param array<string, mixed>                            $parsed
     * @param array<int, string>                              $allowedColumns Real column orig_header values.
     * @param array<int, string>                              $allowedEngines Engines available in the wizard UI.
     * @param array<int, array{name?: string, type?: string}> $columnMeta     Optional schema rows for engine inference.
     * @param string                                          $description    Optional user intent for engine inference.
     * @return array{
     *     engine: string,
     *     chart_type: string,
     *     reason: string,
     *     x_axis: string,
     *     y_axis: array<int, string>,
     *     title: string,
     *     alternatives: array<int, array{chart_type: string, reason: string}>,
     *     warnings: array<int, string>
     * }
     */
    public function normalizeChartSuggestion(
        array $parsed,
        array $allowedColumns,
        array $allowedEngines = [],
        array $columnMeta = [],
        string $description = ''
    ): array {
        $engines = $allowedEngines !== []
            ? array_values(array_intersect(self::ALLOWED_CHART_ENGINES, $allowedEngines))
            : self::ALLOWED_CHART_ENGINES;
        if ($engines === []) {
            $engines = ['google'];
        }

        $chartType = isset($parsed['chart_type']) ? sanitize_key((string) $parsed['chart_type']) : 'column';
        if (!in_array($chartType, self::ALLOWED_CHART_TYPES, true)) {
            $chartType = 'column';
        }

        $typeByName = [];
        foreach ($columnMeta as $column) {
            if (!is_array($column) || empty($column['name'])) {
                continue;
            }
            $typeByName[(string) $column['name']] = strtolower((string) ($column['type'] ?? 'string'));
        }

        $labelColumns  = $this->columnsOfTypes($allowedColumns, $typeByName, self::CHART_LABEL_TYPES, true);
        $seriesColumns = $this->columnsOfTypes($allowedColumns, $typeByName, self::CHART_SERIES_TYPES, true);

        $xAxis = '';
        if (isset($parsed['x_axis'])) {
            $candidates = $this->filterToSchema([(string) $parsed['x_axis']], $labelColumns);
            $xAxis      = $candidates[0] ?? '';
        }

        $yAxis = $this->filterToSchema(
            array_map('strval', (array) ($parsed['y_axis'] ?? [])),
            $seriesColumns
        );
        $yAxis = array_values(array_filter($yAxis, static function ($name) use ($xAxis) {
            return $name !== $xAxis;
        }));

        if (in_array($chartType, ['pie', 'donut'], true) && count($yAxis) > 1) {
            $yAxis = array_slice($yAxis, 0, 1);
        }

        $alternatives = [];
        foreach (array_slice((array) ($parsed['alternatives'] ?? []), 0, 2) as $alt) {
            if (!is_array($alt)) {
                continue;
            }
            $altType = isset($alt['chart_type']) ? sanitize_key((string) $alt['chart_type']) : '';
            if (!in_array($altType, self::ALLOWED_CHART_TYPES, true) || $altType === $chartType) {
                continue;
            }
            $alternatives[] = [
                'chart_type' => $altType,
                'reason'     => isset($alt['reason']) ? sanitize_text_field((string) $alt['reason']) : '',
            ];
        }

        $warnings = [];
        foreach ((array) ($parsed['warnings'] ?? []) as $warning) {
            if (is_string($warning) && $warning !== '') {
                $warnings[] = sanitize_text_field($warning);
            }
        }

        if ($xAxis === '' && $labelColumns !== []) {
            $warnings[] = 'AI suggested an X-axis column that is not a valid label type (string/date/datetime/time/link).';
        }
        if ($yAxis === [] && $seriesColumns !== []) {
            $warnings[] = 'AI suggested Y-axis columns that are not numeric (int/float/formula).';
        }
        if ($seriesColumns === []) {
            $warnings[] = 'This table has no numeric (int/float/formula) columns for chart series.';
        }

        if ($xAxis === '' && $labelColumns !== []) {
            $xAxis = $labelColumns[0];
        }
        if ($yAxis === [] && $seriesColumns !== []) {
            $yAxis = [$seriesColumns[0]];
        }

        $engine = $this->resolveChartEngine(
            $parsed['engine'] ?? '',
            $engines,
            $columnMeta,
            $description,
            $xAxis,
            $yAxis
        );

        return [
            'engine'       => $engine,
            'chart_type'   => $chartType,
            'reason'       => isset($parsed['reason']) ? sanitize_textarea_field((string) $parsed['reason']) : '',
            'x_axis'       => $xAxis,
            'y_axis'       => $yAxis,
            'title'        => isset($parsed['title']) ? sanitize_text_field((string) $parsed['title']) : '',
            'alternatives' => $alternatives,
            'warnings'     => $warnings,
        ];
    }

    /**
     * Filter column names to those whose schema type is in $types.
     *
     * @param array<int, string>            $allowedColumns
     * @param array<string, string>         $typeByName
     * @param array<int, string>            $types
     * @param bool                          $skipInternal
     * @return array<int, string>
     */
    private function columnsOfTypes(
        array $allowedColumns,
        array $typeByName,
        array $types,
        bool $skipInternal = true
    ): array {
        $out = [];
        foreach ($allowedColumns as $name) {
            $name = (string) $name;
            if ($skipInternal && in_array($name, self::INTERNAL_CHART_COLUMNS, true)) {
                continue;
            }
            $type = $typeByName[$name] ?? '';
            if ($type !== '' && in_array($type, $types, true)) {
                $out[] = $name;
            }
        }

        return $out;
    }

    /**
     * Resolve exactly one engine slug from AI output, with schema-aware fallback.
     *
     * @param mixed                                               $raw
     * @param array<int, string>                                  $engines
     * @param array<int, array{name?: string, type?: string}>     $columnMeta
     * @param string                                              $description
     * @param string                                              $xAxis
     * @param array<int, string>                                  $yAxis
     */
    private function resolveChartEngine(
        $raw,
        array $engines,
        array $columnMeta,
        string $description,
        string $xAxis,
        array $yAxis
    ): string {
        $candidates = [];
        if (is_array($raw)) {
            foreach ($raw as $item) {
                if (is_string($item) || is_numeric($item)) {
                    $candidates[] = (string) $item;
                }
            }
        } elseif (is_string($raw) || is_numeric($raw)) {
            $candidates[] = (string) $raw;
        }

        $resolved = [];
        foreach ($candidates as $candidate) {
            $slug = sanitize_key($candidate);
            if (in_array($slug, $engines, true)) {
                $resolved[] = $slug;
                continue;
            }

            $normalized = strtolower(trim($candidate));
            $labelMap   = [
                'google charts'    => 'google',
                'google'           => 'google',
                'chart.js'         => 'chartjs',
                'chartjs'          => 'chartjs',
                'highcharts'       => 'highcharts',
                'highchart'        => 'highcharts',
                'highcharts stock' => 'highstock',
                'highstock'        => 'highstock',
                'apexcharts'       => 'apexcharts',
                'apex'             => 'apexcharts',
            ];
            if (isset($labelMap[$normalized]) && in_array($labelMap[$normalized], $engines, true)) {
                $resolved[] = $labelMap[$normalized];
            }
        }
        $resolved = array_values(array_unique($resolved));

        // Single clear choice from the model — keep it.
        if (count($resolved) === 1) {
            return $resolved[0];
        }

        // Empty, invalid, or multiple engines → pick from schema / intent.
        if ($columnMeta !== []) {
            return $this->inferChartEngine($columnMeta, $engines, $description, $xAxis, $yAxis);
        }

        return in_array('google', $engines, true) ? 'google' : $engines[0];
    }

    /**
     * Pick a single render engine from schema signals when the model gave nothing usable.
     *
     * @param array<int, array{name?: string, type?: string}> $columns
     * @param array<int, string>                              $engines
     * @param string                                          $description
     * @param string                                          $xAxis
     * @param array<int, string>                              $yAxis
     */
    public function inferChartEngine(
        array $columns,
        array $engines,
        string $description = '',
        string $xAxis = '',
        array $yAxis = []
    ): string {
        $engines = array_values(array_intersect(self::ALLOWED_CHART_ENGINES, $engines));
        if ($engines === []) {
            return 'google';
        }

        $typeByName = [];
        $hasDate    = false;
        $numeric    = 0;
        foreach ($columns as $column) {
            if (!is_array($column) || empty($column['name'])) {
                continue;
            }
            $name = (string) $column['name'];
            $type = strtolower((string) ($column['type'] ?? 'string'));
            $typeByName[$name] = $type;
            if (in_array($type, ['date', 'datetime', 'time'], true)) {
                $hasDate = true;
            }
            if (in_array($type, ['int', 'integer', 'float', 'decimal', 'formula', 'number'], true)) {
                $numeric++;
            }
        }

        $xType = $typeByName[$xAxis] ?? '';
        $desc  = strtolower($description);
        $timeSeriesHints = (
            in_array($xType, ['date', 'datetime', 'time'], true)
            || $hasDate
            || (bool) preg_match('/\b(stock|ohlc|price over time|time.?series|share price|ticker)\b/', $desc)
        );

        if ($timeSeriesHints && in_array('highstock', $engines, true)) {
            return 'highstock';
        }

        if ($numeric >= 2 && in_array('highcharts', $engines, true)) {
            return 'highcharts';
        }

        if (count($yAxis) > 1 && in_array('highcharts', $engines, true)) {
            return 'highcharts';
        }

        foreach (['google', 'chartjs', 'apexcharts', 'highcharts'] as $preferred) {
            if (in_array($preferred, $engines, true)) {
                return $preferred;
            }
        }

        return $engines[0];
    }

    /**
     * Sanitise an AI-produced SQL string for the query assistant.
     *
     * Strips Markdown fences, rejects multi-statements and mutating DDL/DML,
     * and requires a SELECT / WITH ... SELECT shape. Returns '' when unsafe.
     *
     * @param string $sql
     * @return string Safe SQL, or empty string when rejected.
     */
    public function sanitizeSql(string $sql): string
    {
        $text = trim($sql);

        if (strpos($text, '```') !== false) {
            $text = (string) preg_replace('/^```[a-zA-Z]*\s*/m', '', $text);
            $text = (string) preg_replace('/\s*```$/m', '', $text);
            $text = trim($text);
        }

        $body = rtrim($text, " \t\n\r\0\x0B;");
        if ($body === '') {
            return '';
        }

        if (strpos($body, ';') !== false) {
            return '';
        }

        if (!preg_match('/^\s*(SELECT|WITH)\b/i', $body)) {
            return '';
        }

        $forbidden = '/\b(INSERT\s+INTO|UPDATE\s+\S+|DELETE\s+FROM|DROP\s+(TABLE|DATABASE|INDEX|VIEW)|ALTER\s+TABLE|TRUNCATE\s+TABLE|CREATE\s+(TABLE|DATABASE|INDEX|VIEW)|GRANT\s+|REVOKE\s+|REPLACE\s+INTO|LOAD\s+DATA|INTO\s+OUTFILE|INTO\s+DUMPFILE)\b/i';
        if (preg_match($forbidden, $body)) {
            return '';
        }

        return $body;
    }

    /**
     * Default WP posts fields the GUI query builder exposes (mirrors JS defaultPostColumns).
     *
     * @var array<int, string>
     */
    const WP_DEFAULT_POST_COLUMNS = [
        'ID',
        'post_date',
        'post_date_gmt',
        'post_author',
        'post_title',
        'title_with_link_to_post',
        'thumbnail_with_link_to_post',
        'post_content',
        'post_content_limited_100_chars',
        'post_excerpt',
        'post_status',
        'comment_status',
        'ping_status',
        'post_password',
        'post_name',
        'to_ping',
        'pinged',
        'post_modified',
        'post_modified_gmt',
        'post_content_filtered',
        'post_parent',
        'guid',
        'menu_order',
        'post_type',
        'post_mime_type',
        'comment_count',
    ];

    /**
     * Normalise and validate Query Constructor Assistant output against SCHEMA
     * (MySQL) or post types (WP GUI builder).
     *
     * @param array<string, mixed>                                                                     $parsed
     * @param array{vendor: string, table_prefix: string, tables: array, columns: array}                $schemaContext
     * @param string                                                                                    $builderType   `wp` or `mysql`.
     * @param array<int, string>                                                                        $postTypes     Allowed post type slugs (WP only).
     * @return array<string, mixed>
     */
    public function normalizeQueryConstructorOutput(
        array $parsed,
        array $schemaContext,
        string $builderType = 'mysql',
        array $postTypes = []
    ): array {
        $isWp = $builderType === 'wp';

        if ($isWp) {
            $allowedTables = array_values(array_unique(array_merge(
                ['all'],
                array_map('strval', $postTypes)
            )));
            $columnMap = [];
        } else {
            $allowedTables = array_map('strval', (array) ($schemaContext['tables'] ?? []));
            $columnMap     = is_array($schemaContext['columns'] ?? null) ? $schemaContext['columns'] : [];
        }

        $tables = [];
        foreach ((array) ($parsed['tables'] ?? []) as $table) {
            if (!is_string($table) || $table === '') {
                continue;
            }
            $table = $isWp ? sanitize_key($table) : $table;
            if ($table === '') {
                continue;
            }
            // Remap accidental DB table names for the WP builder.
            if ($isWp && $this->looksLikeWpDbTable($table, (string) ($schemaContext['table_prefix'] ?? ''))) {
                $table = 'post';
            }
            if ($allowedTables === [] || in_array($table, $allowedTables, true)) {
                $tables[] = $table;
            }
        }
        $tables = array_values(array_unique($tables));

        $columns = [];
        foreach ((array) ($parsed['columns'] ?? []) as $column) {
            if (!is_array($column)) {
                continue;
            }
            $table = isset($column['table']) ? (string) $column['table'] : '';
            $name  = isset($column['column']) ? (string) $column['column'] : '';
            $alias = isset($column['alias']) ? sanitize_key((string) $column['alias']) : $name;
            if ($table === '' || $name === '') {
                continue;
            }
            if ($isWp) {
                $table = sanitize_key($table);
                if ($this->looksLikeWpDbTable($table, (string) ($schemaContext['table_prefix'] ?? ''))) {
                    $table = 'post';
                }
                if ($allowedTables !== [] && !in_array($table, $allowedTables, true)) {
                    continue;
                }
                if (!$this->isAllowedWpWizardColumn($name)) {
                    continue;
                }
            } else {
                if ($allowedTables !== [] && !in_array($table, $allowedTables, true)) {
                    continue;
                }
                if (!$this->columnExistsInSchema($table, $name, $columnMap)) {
                    continue;
                }
            }
            $columns[] = [
                'table'  => $table,
                'column' => $name,
                'alias'  => $alias !== '' ? $alias : $name,
            ];
        }

        $joins = [];
        foreach ((array) ($parsed['joins'] ?? []) as $join) {
            if (!is_array($join)) {
                continue;
            }
            $type = isset($join['type']) ? strtoupper((string) $join['type']) : 'INNER';
            if (!in_array($type, ['INNER', 'LEFT', 'RIGHT'], true)) {
                $type = 'INNER';
            }
            $joins[] = [
                'from' => isset($join['from']) ? sanitize_text_field((string) $join['from']) : '',
                'to'   => isset($join['to']) ? sanitize_text_field((string) $join['to']) : '',
                'type' => $type,
            ];
        }

        $conditions = [];
        foreach ((array) ($parsed['conditions'] ?? []) as $condition) {
            if (!is_array($condition)) {
                continue;
            }
            $conditions[] = [
                'column'   => isset($condition['column']) ? sanitize_text_field((string) $condition['column']) : '',
                'operator' => isset($condition['operator']) ? sanitize_text_field((string) $condition['operator']) : '=',
                'value'    => isset($condition['value']) ? sanitize_text_field((string) $condition['value']) : '',
            ];
        }

        $groupBy = $this->filterToSchema(
            array_map('strval', (array) ($parsed['group_by'] ?? [])),
            $this->flattenSchemaColumnRefs($columns)
        );

        $orderBy = [];
        foreach ((array) ($parsed['order_by'] ?? []) as $order) {
            if (!is_array($order)) {
                continue;
            }
            $direction = isset($order['direction']) ? strtoupper((string) $order['direction']) : 'ASC';
            if (!in_array($direction, ['ASC', 'DESC'], true)) {
                $direction = 'ASC';
            }
            $orderBy[] = [
                'column'    => isset($order['column']) ? sanitize_text_field((string) $order['column']) : '',
                'direction' => $direction,
            ];
        }

        $warnings = [];
        foreach ((array) ($parsed['warnings'] ?? []) as $warning) {
            if (is_string($warning) && $warning !== '') {
                $warnings[] = sanitize_text_field($warning);
            }
        }

        return [
            'explanation'   => isset($parsed['explanation'])
                ? sanitize_textarea_field((string) $parsed['explanation'])
                : '',
            'tables'        => $tables,
            'columns'       => $columns,
            'joins'         => $joins,
            'conditions'    => $conditions,
            'group_by'      => $groupBy,
            'order_by'      => $orderBy,
            'generated_sql' => isset($parsed['generated_sql']) ? (string) $parsed['generated_sql'] : '',
            'warnings'      => $warnings,
            'builder_type'  => isset($parsed['builder_type']) ? sanitize_key((string) $parsed['builder_type']) : '',
        ];
    }

    /**
     * @param string                                                                 $table
     * @param string                                                                 $column
     * @param array<string, array<int, array{name: string, type: string}>>          $columnMap
     * @return bool
     */
    private function columnExistsInSchema(string $table, string $column, array $columnMap): bool
    {
        if (!isset($columnMap[$table]) || !is_array($columnMap[$table])) {
            // Schema column metadata may be incomplete — allow when table is listed.
            return true;
        }

        foreach ($columnMap[$table] as $schemaColumn) {
            if (is_array($schemaColumn) && ($schemaColumn['name'] ?? '') === $column) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether a name looks like a prefixed WP core DB table (not a post type slug).
     *
     * @param string $name
     * @param string $prefix
     * @return bool
     */
    private function looksLikeWpDbTable(string $name, string $prefix): bool
    {
        $suffixes = [
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

        if ($prefix !== '' && strpos($name, $prefix) === 0) {
            $suffix = substr($name, strlen($prefix));
            return in_array($suffix, $suffixes, true);
        }

        return in_array($name, $suffixes, true) || preg_match('/^wp_/', $name) === 1;
    }

    /**
     * Allow default posts fields plus meta./taxonomy. wizard keys.
     *
     * @param string $column
     * @return bool
     */
    private function isAllowedWpWizardColumn(string $column): bool
    {
        if (in_array($column, self::WP_DEFAULT_POST_COLUMNS, true)) {
            return true;
        }

        // meta.key / taxonomy.slug — keep conservative charset.
        return (bool) preg_match('/^(meta|taxonomy)\.[A-Za-z0-9_\-]+$/', $column);
    }

    /**
     * @param array<int, array{table: string, column: string, alias: string}> $columns
     * @return array<int, string>
     */
    private function flattenSchemaColumnRefs(array $columns): array
    {
        $refs = [];
        foreach ($columns as $column) {
            $refs[] = $column['table'] . '.' . $column['column'];
            if ($column['alias'] !== '') {
                $refs[] = $column['alias'];
            }
        }

        return $refs;
    }
}
