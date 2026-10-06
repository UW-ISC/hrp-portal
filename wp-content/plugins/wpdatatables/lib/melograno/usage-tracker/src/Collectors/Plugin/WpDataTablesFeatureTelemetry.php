<?php

declare (strict_types=1);
namespace WPDT\Melograno\UsageTracker\Collectors\Plugin;

/**
 * wpDataTables feature telemetry: skins, table options, site settings, and usage counts.
 */
final class WpDataTablesFeatureTelemetry
{
    /**
     * WP option written by the wpDataTables host on successful file-source Apply.
     * This library only reads it at collect time (and deletes it on uninstall).
     */
    public const FILE_SOURCE_ACTIONS_OPTION = 'wpdatatables_usage_file_source_actions';
    /** @var list<string> Per-table adoption — always emit feature_metrics (existing behavior). */
    private const TABLE_METRIC_CODES = ['sorting', 'filtering', 'fixedHeader', 'fixedColumns'];
    /**
     * Count-based — emit feature_metrics only when usage_count > 0.
     *
     * @var list<string>
     */
    private const COUNT_METRIC_CODES = ['separateDbConnection', 'hiddenColumns', 'serverSideTables', 'nonServerSideTables', 'tablesUpTo10Rows', 'tablesOver10Rows', 'conditionalFormatting', 'formulaColumns', 'masterDetail', 'cascadeFiltering'];
    /**
     * Site toggles / yes-no — features only, no feature_metrics.
     *
     * @var list<string>
     */
    private const FEATURE_ONLY_CODES = ['globalTableLoader', 'globalChartLoader', 'bootstrapFrontend', 'bootstrapBackend', 'googleSheetsApi', 'stableGoogleCharts', 'stableApexCharts', 'tableManager', 'manualFileSourceImport', 'reportBuilderAddon', 'reportBuilderStandalone'];
    /** @var list<string> Candidate column names for Report Builder data-source table id. */
    private const REPORT_BUILDER_TABLE_ID_COLUMNS = ['table_id', 'wpdatatable_id'];
    /** @var list<string> */
    private const ALLOWED_SKINS = ['material', 'light', 'graphite', 'aqua', 'purple', 'dark', 'raspberry-cream', 'mojito', 'dark-mojito'];
    /**
     * Manual table "Select how to use source file data" Apply actions.
     *
     * @var list<string>
     */
    private const ALLOWED_FILE_SOURCE_ACTIONS = ['replaceTableData', 'addDataToTable', 'replaceTable'];
    public static function deleteStoredCounters() : void
    {
        if (\function_exists('delete_option')) {
            \delete_option(self::FILE_SOURCE_ACTIONS_OPTION);
        }
    }
    /**
     * @return array{
     *     skins: array<string, int>,
     *     file_source_actions: array<string, int>,
     *     features: array<string, bool>,
     *     feature_metrics: array<string, array{usage_count: int}>
     * }|null
     */
    public static function collect() : ?array
    {
        global $wpdb;
        if (!isset($wpdb) || !\is_object($wpdb) || !\method_exists($wpdb, 'get_results')) {
            return null;
        }
        $tableData = self::collectTableFeatureCounts($wpdb);
        if ($tableData === null) {
            return null;
        }
        $featureOnlyFlags = self::collectFeatureOnlyFlags($wpdb);
        $countMetrics = self::collectCountMetrics($wpdb, $tableData['serverSide'], $tableData['nonServerSide'], $tableData['addonCounts']);
        $fileSourceActions = self::collectFileSourceActionCounts();
        if ($fileSourceActions !== []) {
            $featureOnlyFlags['manualFileSourceImport'] = \true;
        }
        return self::buildResult($tableData['skins'], $fileSourceActions, $tableData['tableMetrics'], $featureOnlyFlags, $countMetrics);
    }
    /**
     * @param object $wpdb
     * @return array{
     *     skins: array<string, int>,
     *     tableMetrics: array<string, int>,
     *     addonCounts: array{masterDetail: int, cascadeFiltering: int},
     *     serverSide: int,
     *     nonServerSide: int
     * }|null
     */
    private static function collectTableFeatureCounts($wpdb) : ?array
    {
        $output = \defined('ARRAY_A') ? ARRAY_A : 'ARRAY_A';
        $rows = $wpdb->get_results('SELECT sorting, filtering, server_side, advanced_settings FROM ' . $wpdb->prefix . 'wpdatatables', $output);
        if ($rows === null) {
            if (!empty($wpdb->last_error)) {
                \error_log('[melograno/usage-tracker] wpDataTables feature telemetry failed: ' . $wpdb->last_error);
            }
            return null;
        }
        $tableMetrics = \array_fill_keys(self::TABLE_METRIC_CODES, 0);
        $addonCounts = ['masterDetail' => 0, 'cascadeFiltering' => 0];
        $skins = [];
        $serverSide = 0;
        $nonServerSide = 0;
        $defaultSkin = self::resolveDefaultSkin();
        foreach ($rows as $row) {
            $advanced = self::decodeJson($row['advanced_settings'] ?? null);
            $skin = self::resolveSkin($advanced, $defaultSkin);
            $skins[$skin] = ($skins[$skin] ?? 0) + 1;
            if (!empty($row['sorting'])) {
                $tableMetrics['sorting']++;
            }
            if (!empty($row['filtering'])) {
                $tableMetrics['filtering']++;
            }
            if (!empty($advanced['fixed_header'])) {
                $tableMetrics['fixedHeader']++;
            }
            if (!empty($advanced['fixed_columns'])) {
                $tableMetrics['fixedColumns']++;
            }
            if (!empty($advanced['masterDetail'])) {
                $addonCounts['masterDetail']++;
            }
            if (!empty($advanced['cascadeFiltering'])) {
                $addonCounts['cascadeFiltering']++;
            }
            if (!empty($row['server_side'])) {
                $serverSide++;
            } else {
                $nonServerSide++;
            }
        }
        return ['skins' => $skins, 'tableMetrics' => $tableMetrics, 'addonCounts' => $addonCounts, 'serverSide' => $serverSide, 'nonServerSide' => $nonServerSide];
    }
    /**
     * @param object $wpdb
     * @return array<string, bool>
     */
    private static function collectFeatureOnlyFlags($wpdb) : array
    {
        $flags = [];
        if (!empty(\get_option('wdtGlobalTableLoader'))) {
            $flags['globalTableLoader'] = \true;
        }
        if (!empty(\get_option('wdtGlobalChartLoader'))) {
            $flags['globalChartLoader'] = \true;
        }
        if (!empty(\get_option('wdtIncludeBootstrap'))) {
            $flags['bootstrapFrontend'] = \true;
        }
        if (!empty(\get_option('wdtIncludeBootstrapBackEnd'))) {
            $flags['bootstrapBackend'] = \true;
        }
        if (self::isGoogleSheetsApiEnabled()) {
            $flags['googleSheetsApi'] = \true;
        }
        if (!empty(\get_option('wdtGoogleStableVersion'))) {
            $flags['stableGoogleCharts'] = \true;
        }
        if (!empty(\get_option('wdtApexStableVersion'))) {
            $flags['stableApexCharts'] = \true;
        }
        if (self::hasTableManager($wpdb)) {
            $flags['tableManager'] = \true;
        }
        foreach (self::collectReportBuilderFlags($wpdb) as $code => $enabled) {
            if ($enabled) {
                $flags[$code] = \true;
            }
        }
        return $flags;
    }
    /**
     * Report Builder mode flags from saved reports (wizard step 1 data source).
     * Addon = linked wpDataTable; standalone = no table (variables / user input only).
     *
     * @param object $wpdb
     * @return array{reportBuilderAddon?: true, reportBuilderStandalone?: true}
     */
    private static function collectReportBuilderFlags($wpdb) : array
    {
        if (!\method_exists($wpdb, 'get_var')) {
            return [];
        }
        $tableName = $wpdb->prefix . 'wpdatareports';
        $existsQuery = \method_exists($wpdb, 'prepare') ? $wpdb->prepare('SHOW TABLES LIKE %s', $tableName) : "SHOW TABLES LIKE '" . \str_replace(['\\', "'"], '', $tableName) . "'";
        $exists = $wpdb->get_var($existsQuery);
        if ($exists === null || $exists === \false || $exists === '' || $exists === 0) {
            return [];
        }
        $column = self::resolveReportBuilderTableIdColumn($wpdb, $tableName);
        if ($column === null) {
            return [];
        }
        $flags = [];
        $addonExists = $wpdb->get_var('SELECT 1 FROM `' . $tableName . '` WHERE `' . $column . '` IS NOT NULL' . ' AND `' . $column . '` != \'\' AND `' . $column . '` != \'0\' LIMIT 1');
        if (!empty($addonExists)) {
            $flags['reportBuilderAddon'] = \true;
        }
        $standaloneExists = $wpdb->get_var('SELECT 1 FROM `' . $tableName . '` WHERE `' . $column . '` IS NULL' . ' OR `' . $column . '` = \'\' OR `' . $column . '` = \'0\' LIMIT 1');
        if (!empty($standaloneExists)) {
            $flags['reportBuilderStandalone'] = \true;
        }
        return $flags;
    }
    /**
     * @param object $wpdb
     */
    private static function resolveReportBuilderTableIdColumn($wpdb, string $tableName) : ?string
    {
        if (!\method_exists($wpdb, 'get_results')) {
            return null;
        }
        $output = \defined('ARRAY_A') ? ARRAY_A : 'ARRAY_A';
        $columns = $wpdb->get_results('SHOW COLUMNS FROM `' . $tableName . '`', $output);
        if (!\is_array($columns) || $columns === []) {
            if (!empty($wpdb->last_error)) {
                \error_log('[melograno/usage-tracker] wpDataTables report builder columns failed: ' . $wpdb->last_error);
            }
            return null;
        }
        $names = [];
        foreach ($columns as $column) {
            $field = $column['Field'] ?? $column['field'] ?? null;
            if (\is_string($field) && $field !== '') {
                $names[] = $field;
            }
        }
        foreach (self::REPORT_BUILDER_TABLE_ID_COLUMNS as $candidate) {
            if (\in_array($candidate, $names, \true)) {
                return $candidate;
            }
        }
        \error_log('[melograno/usage-tracker] wpDataTables report builder data-source column not found');
        return null;
    }
    /**
     * @param object $wpdb
     * @param array{masterDetail: int, cascadeFiltering: int} $addonCounts
     * @return array<string, int>
     */
    private static function collectCountMetrics($wpdb, int $serverSide, int $nonServerSide, array $addonCounts) : array
    {
        $counts = [];
        $connectionCount = self::resolveSeparateDbConnectionCount();
        if ($connectionCount > 0) {
            $counts['separateDbConnection'] = $connectionCount;
        }
        if ($serverSide > 0) {
            $counts['serverSideTables'] = $serverSide;
        }
        if ($nonServerSide > 0) {
            $counts['nonServerSideTables'] = $nonServerSide;
        }
        foreach ($addonCounts as $code => $count) {
            if ($count > 0) {
                $counts[$code] = $count;
            }
        }
        $columnAggregates = self::collectColumnAggregates($wpdb);
        foreach ($columnAggregates as $code => $count) {
            if ($count > 0) {
                $counts[$code] = $count;
            }
        }
        $sizeBuckets = self::collectTableSizeBuckets($wpdb);
        foreach ($sizeBuckets as $code => $count) {
            if ($count > 0) {
                $counts[$code] = $count;
            }
        }
        return $counts;
    }
    /**
     * @param object $wpdb
     * @return array{hiddenColumns: int, formulaColumns: int, conditionalFormatting: int}
     */
    private static function collectColumnAggregates($wpdb) : array
    {
        $defaults = ['hiddenColumns' => 0, 'formulaColumns' => 0, 'conditionalFormatting' => 0];
        if (!\method_exists($wpdb, 'get_row')) {
            return $defaults;
        }
        $output = \defined('ARRAY_A') ? ARRAY_A : 'ARRAY_A';
        $row = $wpdb->get_row('SELECT ' . 'COUNT(DISTINCT CASE WHEN visible = 0 OR column_type = \'hidden\' THEN table_id END) AS hidden_tables, ' . 'COUNT(DISTINCT CASE WHEN column_type = \'formula\' THEN table_id END) AS formula_tables, ' . 'COUNT(DISTINCT CASE WHEN formatting_rules IS NOT NULL ' . 'AND formatting_rules NOT IN (\'\', \'0\', \'[]\') THEN table_id END) AS conditional_formatting_tables ' . 'FROM ' . $wpdb->prefix . 'wpdatatables_columns', $output);
        if ($row === null) {
            if (!empty($wpdb->last_error)) {
                \error_log('[melograno/usage-tracker] wpDataTables column aggregate failed: ' . $wpdb->last_error);
            }
            return $defaults;
        }
        return ['hiddenColumns' => (int) ($row['hidden_tables'] ?? 0), 'formulaColumns' => (int) ($row['formula_tables'] ?? 0), 'conditionalFormatting' => (int) ($row['conditional_formatting_tables'] ?? 0)];
    }
    /**
     * Size buckets for simple tables only (via COUNT on wpdatatables_rows).
     * File/URL/MySQL tables are excluded to avoid loading large cache payloads into PHP.
     *
     * @param object $wpdb
     * @return array{tablesUpTo10Rows: int, tablesOver10Rows: int}
     */
    private static function collectTableSizeBuckets($wpdb) : array
    {
        $upTo10 = 0;
        $over10 = 0;
        $output = \defined('ARRAY_A') ? ARRAY_A : 'ARRAY_A';
        $simpleRows = $wpdb->get_results('SELECT t.id AS table_id, COUNT(r.id) AS row_count ' . 'FROM ' . $wpdb->prefix . 'wpdatatables t ' . 'LEFT JOIN ' . $wpdb->prefix . 'wpdatatables_rows r ON r.table_id = t.id ' . 'WHERE t.table_type = \'simple\' ' . 'GROUP BY t.id', $output);
        if (\is_array($simpleRows)) {
            foreach ($simpleRows as $row) {
                $rowCount = (int) ($row['row_count'] ?? 0);
                if ($rowCount <= 10) {
                    $upTo10++;
                } else {
                    $over10++;
                }
            }
        } elseif (!empty($wpdb->last_error)) {
            \error_log('[melograno/usage-tracker] wpDataTables simple table size bucket failed: ' . $wpdb->last_error);
        }
        return ['tablesUpTo10Rows' => $upTo10, 'tablesOver10Rows' => $over10];
    }
    /**
     * @param object $wpdb
     */
    private static function hasTableManager($wpdb) : bool
    {
        if (!\method_exists($wpdb, 'get_col')) {
            return \false;
        }
        $usermeta = isset($wpdb->usermeta) ? (string) $wpdb->usermeta : $wpdb->prefix . 'usermeta';
        $userIds = $wpdb->get_col('SELECT DISTINCT user_id FROM ' . $usermeta . ' WHERE meta_key IN (\'wpdt_table_access\', \'wpdt_chart_access\')' . ' AND meta_value IS NOT NULL AND meta_value != \'\'');
        if ($userIds === null) {
            if (!empty($wpdb->last_error)) {
                \error_log('[melograno/usage-tracker] wpDataTables manager count failed: ' . $wpdb->last_error);
            }
            return \false;
        }
        if ($userIds === []) {
            return \false;
        }
        if (!\function_exists('get_userdata')) {
            return \count($userIds) > 0;
        }
        foreach ($userIds as $userId) {
            $user = \get_userdata((int) $userId);
            if ($user === \false || $user === null) {
                continue;
            }
            $roles = isset($user->roles) && \is_array($user->roles) ? $user->roles : [];
            if (\in_array('administrator', $roles, \true)) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    private static function resolveSeparateDbConnectionCount() : int
    {
        if (!empty(\get_option('wdtUseSeparateCon'))) {
            $connections = self::decodeJsonOption('wdtSeparateCon');
            return \count($connections);
        }
        return 0;
    }
    private static function isGoogleSheetsApiEnabled() : bool
    {
        if (!\function_exists('get_option')) {
            return \false;
        }
        $settings = \get_option('wdtGoogleSettings', '');
        if ($settings === \false || $settings === null || $settings === '' || $settings === []) {
            return \false;
        }
        if (\is_string($settings)) {
            $decoded = \json_decode($settings, \true);
            if (\is_array($decoded)) {
                return $decoded !== [];
            }
            return \trim($settings) !== '';
        }
        return \is_array($settings) ? $settings !== [] : \true;
    }
    /**
     * @return array<int|string, mixed>
     */
    private static function decodeJsonOption(string $option) : array
    {
        if (!\function_exists('get_option')) {
            return [];
        }
        return self::decodeJson(\get_option($option, ''));
    }
    /**
     * @return array<string, int>
     */
    private static function collectFileSourceActionCounts() : array
    {
        $counts = self::readFileSourceActionCounts();
        $result = [];
        foreach (self::ALLOWED_FILE_SOURCE_ACTIONS as $action) {
            $count = (int) ($counts[$action] ?? 0);
            if ($count > 0) {
                $result[$action] = $count;
            }
        }
        return $result;
    }
    /**
     * @return array<string, int>
     */
    private static function readFileSourceActionCounts() : array
    {
        if (!\function_exists('get_option')) {
            return [];
        }
        $raw = \get_option(self::FILE_SOURCE_ACTIONS_OPTION, []);
        if (\is_string($raw)) {
            $decoded = \json_decode($raw, \true);
            $raw = \is_array($decoded) ? $decoded : [];
        }
        if (!\is_array($raw)) {
            return [];
        }
        $counts = [];
        foreach (self::ALLOWED_FILE_SOURCE_ACTIONS as $action) {
            $counts[$action] = \max(0, (int) ($raw[$action] ?? 0));
        }
        return $counts;
    }
    /**
     * @param array<string, int> $skins
     * @param array<string, int> $fileSourceActions
     * @param array<string, int> $tableMetrics
     * @param array<string, bool> $featureOnlyFlags
     * @param array<string, int> $countMetrics
     * @return array{
     *     skins: array<string, int>,
     *     file_source_actions: array<string, int>,
     *     features: array<string, bool>,
     *     feature_metrics: array<string, array{usage_count: int}>
     * }
     */
    private static function buildResult(array $skins, array $fileSourceActions, array $tableMetrics, array $featureOnlyFlags, array $countMetrics) : array
    {
        $features = [];
        $featureMetrics = [];
        foreach (self::TABLE_METRIC_CODES as $code) {
            $count = (int) ($tableMetrics[$code] ?? 0);
            $featureMetrics[$code] = ['usage_count' => $count];
            if ($count > 0) {
                $features[$code] = \true;
            }
        }
        foreach (self::COUNT_METRIC_CODES as $code) {
            $count = (int) ($countMetrics[$code] ?? 0);
            if ($count > 0) {
                $features[$code] = \true;
                $featureMetrics[$code] = ['usage_count' => $count];
            }
        }
        foreach (self::FEATURE_ONLY_CODES as $code) {
            if (!empty($featureOnlyFlags[$code])) {
                $features[$code] = \true;
            }
        }
        return ['skins' => $skins, 'file_source_actions' => $fileSourceActions, 'features' => $features, 'feature_metrics' => $featureMetrics];
    }
    /**
     * @param mixed $raw
     * @return array<string, mixed>
     */
    private static function decodeJson($raw) : array
    {
        if (\is_array($raw)) {
            return $raw;
        }
        if (!\is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = \json_decode($raw, \true);
        return \is_array($decoded) ? $decoded : [];
    }
    /**
     * @param array<string, mixed> $advanced
     */
    private static function resolveSkin(array $advanced, string $defaultSkin) : string
    {
        $skin = isset($advanced['tableSkin']) ? \trim((string) $advanced['tableSkin']) : '';
        if ($skin !== '' && \in_array($skin, self::ALLOWED_SKINS, \true)) {
            return $skin;
        }
        return $defaultSkin;
    }
    private static function resolveDefaultSkin() : string
    {
        $fallback = 'light';
        if (!\function_exists('get_option')) {
            return $fallback;
        }
        $option = \get_option('wdtBaseSkin', $fallback);
        $skin = \is_string($option) ? \trim($option) : $fallback;
        return \in_array($skin, self::ALLOWED_SKINS, \true) ? $skin : $fallback;
    }
}
