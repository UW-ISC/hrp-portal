<?php

/**
 * Shared helpers used by wpDataTables MCP abilities.
 *
 * @package wpDataTables
 */

namespace WDTMCP\Helpers\McpHelpers {

defined('ABSPATH') or die('Access denied.');

class WdtmcpAbilitiesHelper
{
    public static function getTableCustomCss(int $tableId): string
    {
        $all = get_option('wdtmcp_table_css', array());

        return isset($all[$tableId]) ? (string) $all[$tableId] : '';
    }

    public static function setTableCustomCss(int $tableId, string $css): void
    {
        $all = get_option('wdtmcp_table_css', array());

        if ('' === trim($css)) {
            unset($all[$tableId]);
        } else {
            $all[$tableId] = self::sanitizeCustomCssForOutput($css);
        }

        update_option('wdtmcp_table_css', $all);
        self::syncTableCssToGlobal();
    }

    public static function syncTableCssToGlobal(): void
    {
        $global = (string) get_option('wdtCustomCss', '');
        $global = preg_replace(
            '/\/\*\s*wdtmcp-table-\d+-start\s*\*\/[\s\S]*?\/\*\s*wdtmcp-table-\d+-end\s*\//',
            '',
            $global
        );
        $global = trim((string) $global);

        $ourCss = get_option('wdtmcp_table_css', array());
        if (empty($ourCss)) {
            update_option('wdtCustomCss', $global);
            return;
        }

        $blocks = array();
        foreach ($ourCss as $tableId => $css) {
            if ('' !== trim((string) $css)) {
                $blocks[] = "/* wdtmcp-table-{$tableId}-start */\n" . $css . "\n/* wdtmcp-table-{$tableId}-end */";
            }
        }

        $merged = $global . ('' !== $global ? "\n\n" : '') . implode("\n\n", $blocks);
        update_option('wdtCustomCss', $merged);
    }

    public static function maybeMigrateCssToGlobal(): void
    {
        if ((int) get_option('wdtmcp_css_uses_global', 0) >= 1) {
            return;
        }

        self::syncTableCssToGlobal();
        update_option('wdtmcp_css_uses_global', 1);
    }

    /**
     * Validate MCP custom_css input before storage.
     *
     * @param mixed $value Raw ability input.
     * @return string|\WP_Error Trimmed CSS string (may be empty), or error.
     */
    public static function validateCustomCssInput($value)
    {
        if (! is_string($value)) {
            return new \WP_Error(
                'wdtmcp_invalid_custom_css',
                __('custom_css must be a string.', 'wpdatatables')
            );
        }

        $css = str_replace("\0", '', trim($value));
        $max = (int) apply_filters('wdtmcp_custom_css_max_length', 100000);

        if (strlen($css) > $max) {
            return new \WP_Error(
                'wdtmcp_custom_css_too_long',
                sprintf(
                    /* translators: %d: maximum allowed character count */
                    __('custom_css exceeds the maximum length of %d characters.', 'wpdatatables'),
                    $max
                )
            );
        }

        if ('' !== $css && self::customCssContainsDisallowedInput($css)) {
            return new \WP_Error(
                'wdtmcp_invalid_custom_css',
                __('custom_css contains disallowed markup or directives.', 'wpdatatables')
            );
        }

        return $css;
    }

    /**
     * @param string $css
     * @param int    $tableId
     * @return string
     */
    public static function substituteTableIdInCustomCss(string $css, int $tableId): string
    {
        return str_replace(array('{TABLE_ID}', '{table_id}'), (string) $tableId, $css);
    }

    /**
     * Substitute table ID placeholders and sanitize CSS for storage/output.
     *
     * @param string $css
     * @param int    $tableId
     * @return string
     */
    public static function prepareTableCustomCssForStorage(string $css, int $tableId): string
    {
        return self::sanitizeCustomCssForOutput(
            self::substituteTableIdInCustomCss($css, $tableId)
        );
    }

    /**
     * @param string $css
     * @return bool
     */
    private static function customCssContainsDisallowedInput(string $css): bool
    {
        $patterns = array(
            '/<\s*(script|style|iframe|object|embed|link|meta|base|svg|math)\b/i',
            '/@import\b/i',
            '/\bjavascript\s*:/i',
            '/\bexpression\s*\(/i',
            '/\bbehavior\s*:/i',
            '/\b-moz-binding\s*:/i',
            '/url\s*\(\s*["\']?\s*data:/i',
        );

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $css)) {
                return true;
            }
        }

        return false;
    }

    public static function sanitizeCustomCssForOutput(string $css): string
    {
        $css = str_replace("\0", '', $css);
        $css = wp_strip_all_tags($css, true);
        $css = preg_replace('/<\/style>/i', '', $css);
        $css = preg_replace('/<script\b[^>]*>/i', '', (string) $css);
        $css = preg_replace('/<\/script>/i', '', (string) $css);
        $css = preg_replace('/@import\b[^;]*;?/i', '', (string) $css);
        $css = preg_replace('/url\s*\(\s*["\']?\s*data:[^)]*\)/i', '', (string) $css);
        $css = preg_replace('/\bjavascript\s*:/i', '', (string) $css);
        $css = preg_replace('/\bexpression\s*\(/i', '', (string) $css);
        $css = preg_replace('/\bbehavior\s*:/i', '', (string) $css);
        $css = preg_replace('/\b-moz-binding\s*:/i', '', (string) $css);

        return (string) apply_filters('wdtmcp_sanitize_custom_css', $css);
    }

    /**
     * Basic SSRF guard: whether an http(s) host may be fetched as an external remote target.
     *
     * Blocks empty host, localhost / *.localhost names, numeric IPv4/IPv6 in private, reserved,
     * loopback, and link-local ranges (via FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE).
     * Does not resolve hostnames — only literal IPs in the host part are ranged-checked.
     *
     * @param string $host Host from wp_parse_url() (typically lowercased for stable labels).
     * @return bool True if permitted; false if blocked or empty.
     */
    public static function isExternalAllowedRemoteHost(string $host): bool
    {
        $h = strtolower(trim($host));

        if ('' === $h) {
            return false;
        }

        if ('localhost' === $h) {
            return false;
        }

        $localhost_suffix = '.localhost';
        if (\strlen($h) >= \strlen($localhost_suffix) && \substr($h, -\strlen($localhost_suffix)) === $localhost_suffix) {
            return false;
        }

        $ip_candidate = $h;
        if (\strlen($h) >= 2 && '[' === $h[0] && ']' === $h[\strlen($h) - 1]) {
            $ip_candidate = \substr($h, 1, -1);
        }

        $is_ip = false !== filter_var($ip_candidate, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);
        if ($is_ip) {
            return false !== filter_var(
                $ip_candidate,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
        }

        return true;
    }

    /**
     * Integration entry-point map (same approach as get-system-info).
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function getIntegrationFileMap(): array
    {
        $starter  = defined('WDT_STARTER_INTEGRATIONS_PATH') ? WDT_STARTER_INTEGRATIONS_PATH : '';
        $standard = defined('WDT_STANDARD_INTEGRATIONS_PATH') ? WDT_STANDARD_INTEGRATIONS_PATH : '';

        return array(
            'google_sheet_api'   => array($starter, 'google-sheet-api/wdt-google-sheet-api-integration.php'),
            'update_manual_file' => array($standard, 'update-manual-from-file/wdt-update-manual-from-file-integration.php'),
        );
    }

    public static function isIntegrationFileAvailable(string $feature): bool
    {
        $map = self::getIntegrationFileMap();
        if (! isset($map[$feature])) {
            return false;
        }

        $base = $map[$feature][0];
        $file = $map[$feature][1];

        return '' !== $base && is_file($base . $file);
    }

    /**
     * Load wpDataTables classes/constants that are normally only bootstrapped in wp-admin.
     * Required for MCP/REST requests (is_admin() is false).
     *
     * @return true|WP_Error
     */
    public static function bootstrapFileAbilities()
    {
        if (! class_exists('WPDataTable')) {
            return new \WP_Error(
                'wdtmcp_missing_class',
                __('wpDataTables core class WPDataTable is not available.', 'wpdatatables')
            );
        }

        if (! class_exists('wpDataTableConstructor') && is_file(WDT_ROOT_PATH . 'source/class.constructor.php')) {
            require_once WDT_ROOT_PATH . 'source/class.constructor.php';
        }

        if (self::isIntegrationFileAvailable('update_manual_file') && ! defined('WDT_UMFF_INTEGRATION')) {
            require_once WDT_STANDARD_INTEGRATIONS_PATH . 'update-manual-from-file/wdt-update-manual-from-file-integration.php';
        }

        if (self::isIntegrationFileAvailable('google_sheet_api') && ! defined('WDT_GSAPI_INTEGRATION')) {
            require_once WDT_STARTER_INTEGRATIONS_PATH . 'google-sheet-api/wdt-google-sheet-api-integration.php';
        }

        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }

        return true;
    }

    /**
     * Store raw file bytes in the WordPress Media Library (CSV, Excel, JSON, XML).
     *
     * @param string $binary  Raw file contents.
     * @param string $filename Desired filename with extension.
     * @param string $title    Optional attachment title.
     * @return int|WP_Error Attachment ID.
     */
    public static function uploadDataFileToMediaLibrary(string $binary, string $filename, string $title = '')
    {
        if ('' === trim($binary)) {
            return new \WP_Error('wdtmcp_empty_file', __('File content is empty.', 'wpdatatables'));
        }

        $filename = sanitize_file_name($filename);
        if ('' === $filename) {
            return new \WP_Error('wdtmcp_invalid_filename', __('A valid filename is required.', 'wpdatatables'));
        }

        $max_size = wp_max_upload_size();
        if ($max_size > 0 && strlen($binary) > $max_size) {
            return new \WP_Error(
                'wdtmcp_file_too_large',
                sprintf(
                    /* translators: %s: File name. */
                    __('%s exceeds the maximum upload size for this site.', 'wpdatatables'),
                    $filename
                )
            );
        }

        $ext     = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = array('csv', 'xls', 'xlsx', 'ods', 'json', 'xml');
        if (! in_array($ext, $allowed, true)) {
            return new \WP_Error(
                'wdtmcp_invalid_type',
                __('Only csv, xls, xlsx, ods, json, and xml files are allowed.', 'wpdatatables')
            );
        }

        $upload_dir = wp_upload_dir();
        if (! empty($upload_dir['error'])) {
            return new \WP_Error('wdtmcp_upload_failed', $upload_dir['error']);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $tmp = \wp_tempnam($filename);
        if (! $tmp) {
            return new \WP_Error('wdtmcp_upload_failed', __('Could not create temporary file.', 'wpdatatables'));
        }

        $written = file_put_contents($tmp, $binary);
        if (false === $written) {
            @unlink($tmp);
            return new \WP_Error('wdtmcp_upload_failed', __('Could not write temporary file.', 'wpdatatables'));
        }

        $file_array = array(
            'name'     => $filename,
            'tmp_name' => $tmp,
        );

        $id = media_handle_sideload($file_array, 0, $title);
        @unlink($tmp);

        if (is_wp_error($id)) {
            return $id;
        }

        return (int) $id;
    }

    /**
     * Shift a CSV/Excel file so the chosen row becomes row 1 (column headers).
     *
     * wpDataTables always treats row 1 as headers. Report-style exports often have
     * title rows above the real header row (e.g. row 4).
     *
     * @param string $localPath   Readable local file path.
     * @param int    $headerRow   1-based row number that contains column headers.
     * @return string|\WP_Error Normalized temp file path, or original path when $headerRow <= 1.
     */
    public static function normalizeTabularFileHeaderRow(string $localPath, int $headerRow)
    {
        if ($headerRow <= 1) {
            return $localPath;
        }

        if (! is_readable($localPath)) {
            return new \WP_Error('wdtmcp_file_not_readable', __('Source file is not readable.', 'wpdatatables'));
        }

        if (! class_exists('WPDataTable')) {
            return new \WP_Error('wdtmcp_missing_class', __('wpDataTables core is not available.', 'wpdatatables'));
        }

        try {
            $reader      = \WPDataTable::createObjectReader($localPath);
            $spreadsheet = $reader->load($localPath);
            $sheet       = $spreadsheet->getActiveSheet();
            $highestRow  = (int) $sheet->getHighestRow();

            if ($headerRow > $highestRow) {
                return new \WP_Error(
                    'wdtmcp_invalid_header_row',
                    sprintf(
                        /* translators: 1: header row number, 2: total rows in sheet */
                        __('header_row %1$d exceeds sheet row count (%2$d).', 'wpdatatables'),
                        $headerRow,
                        $highestRow
                    )
                );
            }

            $highestColumn = $sheet->getHighestDataColumn();
            $data          = $sheet->rangeToArray(
                'A' . $headerRow . ':' . $highestColumn . $highestRow,
                null,
                true,
                true,
                false
            );

            $normalized = new \WPDT\PhpOffice\PhpSpreadsheet\Spreadsheet();
            $normalized->getActiveSheet()->fromArray($data, null, 'A1');

            $ext = strtolower((string) pathinfo($localPath, PATHINFO_EXTENSION));
            if (! in_array($ext, array('csv', 'xls', 'xlsx', 'ods'), true)) {
                $ext = 'xlsx';
            }

            require_once ABSPATH . 'wp-admin/includes/file.php';
            $tmpBase = \wp_tempnam('wdtmcp-normalized');
            if (! $tmpBase) {
                return new \WP_Error('wdtmcp_upload_failed', __('Could not create temporary file.', 'wpdatatables'));
            }

            $tmpPath = $tmpBase . '.' . $ext;
            if (! @rename($tmpBase, $tmpPath)) {
                @unlink($tmpBase);
                $tmpPath = $tmpBase;
            }

            switch ($ext) {
                case 'csv':
                    $writer = new \WPDT\PhpOffice\PhpSpreadsheet\Writer\Csv($normalized);
                    break;
                case 'xls':
                    $writer = new \WPDT\PhpOffice\PhpSpreadsheet\Writer\Xls($normalized);
                    break;
                case 'ods':
                    $writer = new \WPDT\PhpOffice\PhpSpreadsheet\Writer\Ods($normalized);
                    break;
                default:
                    $writer = new \WPDT\PhpOffice\PhpSpreadsheet\Writer\Xlsx($normalized);
                    break;
            }

            $writer->save($tmpPath);

            return $tmpPath;
        } catch (\Exception $e) {
            return new \WP_Error('wdtmcp_normalize_failed', $e->getMessage());
        }
    }

    /**
     * Resolve a media attachment to a local filesystem path when possible.
     *
     * @param int $attachment_id Attachment ID.
     * @return string|false
     */
    public static function getAttachmentLocalPath(int $attachment_id)
    {
        $path = get_attached_file($attachment_id);
        if ($path && is_readable($path)) {
            return $path;
        }

        $url = wp_get_attachment_url($attachment_id);
        if (! $url) {
            return false;
        }

        if (class_exists('wpDataTableConstructor')) {
            $resolved = wpDataTableConstructor::isUploadedFileEmpty($url);
            if (! empty($resolved) && is_readable($resolved)) {
                return $resolved;
            }
        }

        return false;
    }
}

}

namespace {

use WDTMCP\Helpers\McpHelpers\WdtmcpAbilitiesHelper;

if (! function_exists('wdtmcp_get_table_custom_css')) {
function wdtmcp_get_table_custom_css($table_id): string
{
    return WdtmcpAbilitiesHelper::getTableCustomCss((int) $table_id);
}
}

if (! function_exists('wdtmcp_set_table_custom_css')) {
function wdtmcp_set_table_custom_css($table_id, $css): void
{
    WdtmcpAbilitiesHelper::setTableCustomCss((int) $table_id, (string) $css);
}
}

if (! function_exists('wdtmcp_sync_table_css_to_global')) {
function wdtmcp_sync_table_css_to_global(): void
{
    WdtmcpAbilitiesHelper::syncTableCssToGlobal();
}
}

if (! function_exists('wdtmcp_maybe_migrate_css_to_global')) {
function wdtmcp_maybe_migrate_css_to_global(): void
{
    WdtmcpAbilitiesHelper::maybeMigrateCssToGlobal();
}
}

if (! function_exists('wdtmcp_validate_custom_css_input')) {
function wdtmcp_validate_custom_css_input($value)
{
    return WdtmcpAbilitiesHelper::validateCustomCssInput($value);
}
}

if (! function_exists('wdtmcp_prepare_table_custom_css_for_storage')) {
function wdtmcp_prepare_table_custom_css_for_storage($css, $table_id): string
{
    return WdtmcpAbilitiesHelper::prepareTableCustomCssForStorage((string) $css, (int) $table_id);
}
}

if (! function_exists('wdtmcp_sanitize_custom_css_for_output')) {
function wdtmcp_sanitize_custom_css_for_output($css): string
{
    return WdtmcpAbilitiesHelper::sanitizeCustomCssForOutput((string) $css);
}
}

if (! function_exists('wdtmcp_bootstrap_file_abilities')) {
function wdtmcp_bootstrap_file_abilities()
{
    return WdtmcpAbilitiesHelper::bootstrapFileAbilities();
}
}

if (! function_exists('wdtmcp_is_integration_file_available')) {
function wdtmcp_is_integration_file_available($feature): bool
{
    return WdtmcpAbilitiesHelper::isIntegrationFileAvailable((string) $feature);
}
}

if (! function_exists('wdtmcp_is_external_allowed_remote_host')) {
function wdtmcp_is_external_allowed_remote_host($host): bool
{
    return WdtmcpAbilitiesHelper::isExternalAllowedRemoteHost((string) $host);
}
}

if (! function_exists('wdtmcp_upload_data_file_to_media_library')) {
function wdtmcp_upload_data_file_to_media_library($binary, $filename, $title = '')
{
    return WdtmcpAbilitiesHelper::uploadDataFileToMediaLibrary((string) $binary, (string) $filename, (string) $title);
}
}

if (! function_exists('wdtmcp_get_attachment_local_path')) {
function wdtmcp_get_attachment_local_path($attachment_id)
{
    return WdtmcpAbilitiesHelper::getAttachmentLocalPath((int) $attachment_id);
}
}

if (! function_exists('wdtmcp_normalize_tabular_file_header_row')) {
function wdtmcp_normalize_tabular_file_header_row($local_path, $header_row)
{
    return WdtmcpAbilitiesHelper::normalizeTabularFileHeaderRow((string) $local_path, (int) $header_row);
}
}

}
