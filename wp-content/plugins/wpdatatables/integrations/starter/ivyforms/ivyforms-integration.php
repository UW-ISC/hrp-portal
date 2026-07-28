<?php

use IvyForms\Services\API\IvyFormsAPI;
use IvyForms\Services\Entry\Managers\EntryManager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WdtIvyFormsIntegration
{
    public static function init()
    {
        // Add ivyforms to allowed table types
        if (class_exists('WPDataTable')) {
            WPDataTable::$allowedTableTypes[] = 'ivyforms';
        }

        add_action('wpdatatables_enqueue_on_edit_page', array(__CLASS__, 'enqueueAssets'));
        add_action('wp_ajax_wpdatatables_get_ivy_forms_form_fields', array(__CLASS__, 'getIvyFormsFormFields'));
        add_action('wpdatatables_add_table_type_option', array(__CLASS__, 'addIvyFormsTableTypeOption'));
        add_action('wpdatatables_add_data_source_elements', array(__CLASS__, 'addIvyFormsOnDataSourceTab'));
        add_action('wp_ajax_wpdatatables_save_ivyforms_table_config', array('WdtIvyFormsIntegration', 'saveTableConfig'));
        add_action('wpdatatables_generate_ivyforms', array('WdtIvyFormsIntegration', 'ivyformsBasedConstruct'), 10, 3);
        add_action('wpdatatables_add_table_configuration_tab', array('WdtIvyFormsIntegration', 'addIvyformsTab'));
        add_action('wpdatatables_add_table_configuration_tabpanel', array('WdtIvyFormsIntegration', 'addIvyformsTabPanel'));
        add_filter('wpdatatables_filter_insert_table_array', array('WdtIvyFormsIntegration', 'extendTableConfig'));
        add_action('wp_ajax_ivyforms_one_click_install', array('WdtIvyFormsIntegration', 'oneClickInstallIvyForms'));
        add_action('wpdatatables_add_table_constructor_type_in_wizard', array('WdtIvyFormsIntegration', 'addNewTableTypes'));
        add_filter('wpdatatables_filter_cell_output', array('WdtIvyFormsIntegration', 'filterIvyFormsCellOutput'), 10, 3);
        add_filter('wpdatatables_filter_cell_val', array('WdtIvyFormsIntegration', 'filterIvyFormsCellVal'), 10, 2);
        add_filter('safecss_filter_attr_allow_css', array(__CLASS__, 'allowIvyformsRichTextInlineCss'), 10, 2);
    }

    /**
     * Enqueue assets for table creation wizard
     *
     * @return void
     */
    public static function enqueueAssets() {
        wp_enqueue_script(
            'wdt-ivyforms-table-creation',
            plugin_dir_url( __FILE__ ) . 'assets/js/table_creation_wizard.js',
            array( 'jquery', 'wdt-common' ),
            null,
            true
        );
        wp_enqueue_script(
            'wdt-ivyforms-table-config',
            plugin_dir_url( __FILE__ ) . 'assets/js/ivyforms_table_config_object.js',
            array( 'jquery', 'wdt-common' ),
            null,
            true
        );
    }

    /**
     * AJAX handler to get form fields for a given form ID
     *
     * @return void
     */
    public static function getIvyFormsFormFields()
    {
        $formId = intval($_POST['form_id'] ?? 0);
        if ($formId) {
            $fields = IvyFormsAPI::getFields($formId);
            $field_columns = [];
            $field_ids = [];
            foreach ($fields as $field) {
                $field_id = $field->getId();
                $field_columns[] = [
                    'id' => $field_id,
                    'label' => $field->getFieldGeneralSettings()->getLabel()
                ];
                $field_ids[] = $field_id;
            }
            $entry_columns = EntryManager::getAllEntryColumns();
            $entry_data = [];
            foreach ($entry_columns as $key => $label) {
                if (!in_array($key, $field_ids, true)) {
                    $entry_data[] = [
                        'id' => $key,
                        'label' => $label
                    ];
                }
            }
            wp_send_json_success([
                'fields' => $field_columns,
                'entry_data' => $entry_data
            ]);
        } else {
            wp_send_json_error(esc_html__('No form ID.', 'wpdatatables'));
        }
    }

    /**
     * Add IvyForms option to table type dropdown
     *
     * @return void
     */
    public static function addIvyFormsTableTypeOption()
    {
        if (isset($_GET['source']) && $_GET['source'] === 'ivyforms') {
            echo '<option value="ivyforms">IvyForms Form</option>';
        }
    }

    public static function addNewTableTypes()
    {
        ob_start();
        include 'templates/ivyforms_table_type_block.inc.php';
        $newTableTypeBlock = ob_get_contents();
        ob_end_clean();

        echo $newTableTypeBlock;
    }

    /**
     * Add IvyForms specific fields to data source tab
     *
     * @return void
     */
    public static function addIvyFormsOnDataSourceTab()
    {
        $ivyforms_installed = class_exists('IvyForms\Services\API\IvyFormsAPI') && method_exists('IvyForms\Services\API\IvyFormsAPI', 'isPluginActive') && \IvyForms\Services\API\IvyFormsAPI::isPluginActive();
        $ivyforms_needs_update = false;
        $integration_enabled = false;
        $forms_for_template = [];

        if ($ivyforms_installed) {
            if (defined('IVYFORMS_VERSION') && version_compare(IVYFORMS_VERSION, '0.5', '<')) {
                $ivyforms_needs_update = true;
            }
            if (method_exists('IvyForms\Services\API\IvyFormsAPI', 'isIntegrationEnabled')) {
                $integration_enabled = IvyFormsAPI::isIntegrationEnabled('wpdatatables');
            }
            if ($integration_enabled && method_exists('IvyForms\Services\API\IvyFormsAPI', 'getFormsWithIntegrationEnabled')) {
                $forms_for_template = IvyFormsAPI::getFormsWithIntegrationEnabled('wpdatatables');
                if (is_wp_error($forms_for_template)) {
                    $forms_for_template = [];
                }
            }
        }
        include __DIR__ . '/templates/data_source_block.inc.php';
        include __DIR__ . '/templates/fields_block.inc.php';
    }

    /**
     * Save IvyForms table config
     *
     * @return void
     */
    public static function saveTableConfig()
    {
        $nonce = sanitize_text_field($_POST['nonce'] ?? '');

        if (!current_user_can('manage_options') || !wp_verify_nonce($nonce, 'wdtEditNonce')) {
            wp_send_json_error(esc_html__('Permission denied.', 'wpdatatables'));
            exit();
        }

        $ivyFormsData = self::sanitizeIvyformsConfig(json_decode(
            stripslashes_deep($_POST['ivyforms'] ?? '{}')
        ));

        if ($ivyFormsData->formId) {
            $table = json_decode(stripslashes_deep($_POST['table']));
            $table->content = json_encode(
                array(
                    'formId' => $ivyFormsData->formId,
                    'fieldIds' => $ivyFormsData->fields
                )
            );

            WDTConfigController::saveTableConfig($table);
        } else {
            echo json_encode(array('error' => esc_html__('Form data could not be read!', 'wpdatatables')));
        }
        exit();
    }

    /**
     * Sanitize IvyForms config
     *
     * @param object $ivyFormsData
     * @return object
     */
    public static function sanitizeIvyformsConfig(object $ivyFormsData)
    {
        $sanitized = new stdClass();

        if (isset($ivyFormsData->fields)) {
            $sanitized->fields = array_map('sanitize_text_field', (array)$ivyFormsData->fields);
        } else {
            $sanitized->fields = [];
        }

        if (isset($ivyFormsData->formId)) {
            $sanitized->formId = (int)$ivyFormsData->formId;
        } else {
            $sanitized->formId = null;
        }

        if (isset($ivyFormsData->dateFrom)) {
            $sanitized->dateFrom = sanitize_text_field($ivyFormsData->dateFrom);
        } else {
            $sanitized->dateFrom = null;
        }

        if (isset($ivyFormsData->dateTo)) {
            $sanitized->dateTo = sanitize_text_field($ivyFormsData->dateTo);
        } else {
            $sanitized->dateTo = null;
        }

        if (isset($ivyFormsData->filterByUser)) {
            $sanitized->filterByUser = (int)$ivyFormsData->filterByUser;
        } else {
            $sanitized->filterByUser = null;
        }

        if (isset($ivyFormsData->filterByStarred)) {
            $sanitized->filterByStarred = (bool)$ivyFormsData->filterByStarred;
        } else {
            $sanitized->filterByStarred = false;
        }

        if (isset($ivyFormsData->filterByRead)) {
            $sanitized->filterByRead = sanitize_text_field($ivyFormsData->filterByRead);
        } else {
            $sanitized->filterByRead = null;
        }

        return $sanitized;
    }

    /**
     * Construct table data from IvyForms entries
     * @throws Exception
     */
    public static function ivyformsBasedConstruct($wpDataTable, $content, $params)
    {
        // Check if IvyFormsAPI exists and global integration is enabled
        if (!class_exists('IvyForms\Services\API\IvyFormsAPI') || !IvyFormsAPI::isIntegrationEnabled('wpdatatables')) {
            throw new WDTException(__('IvyForms must be active and wpDataTables integration must be enabled to display data.', 'wpdatatables'));
        }
        $content = json_decode($content);
        // Per-form integration check
        $formId = isset($content->formId) ? (int)$content->formId : 0;
        $isFormIntegrationEnabled = IvyFormsAPI::isIntegrationEnabledForForm($formId, 'wpdatatables');
        if (is_wp_error($isFormIntegrationEnabled)) {
            throw new WDTException(__('Error checking form integration settings: ', 'wpdatatables') . $isFormIntegrationEnabled->get_error_message());
        }
        if (!$isFormIntegrationEnabled) {
            throw new WDTException(__('wpDataTables integration is not enabled for this form. Please enable it in the form settings.', 'wpdatatables'));
        }
        /** @var WPDataTable $wpDataTable */
        if ($wpDataTable->getWpId()) {
            $table = WDTConfigController::loadTableFromDB($wpDataTable->getWpId());
            $ivyFormsData = isset($table->advanced_settings) ? json_decode($table->advanced_settings)->ivyforms : null;
        } else {
            $ivyFormsData = null;
        }
        if (empty($params['columnTitles'])) {
            $params['columnTitles'] = self::getColumnHeaders($content->formId, $content->fieldIds);
        }
        $formArray = self::generateFormArray($content, $ivyFormsData);
        self::$relaxSafecssForIvyHtml = true;
        try {
            $wpDataTable->arrayBasedConstruct($formArray, $params);
        } finally {
            self::$relaxSafecssForIvyHtml = false;
        }
    }

    /**
     * Allow IvyForms HTML field inline styles through safecss while arrayBasedConstruct runs wp_kses_post.
     *
     * Core safecss rejects declarations containing "(" (e.g. color: rgb(...)) or "&" (e.g. font-family: &quot;..."),
     * which IvyForms/Vue commonly emit.
     *
     * @param bool   $allow             Whether the CSS fragment passed core's character checks.
     * @param string $css_test_string   Single declaration under test (e.g. "color: rgb(1, 2, 3)").
     * @return bool
     */
    public static function allowIvyformsRichTextInlineCss($allow, $css_test_string) {
        if ($allow || !self::$relaxSafecssForIvyHtml) {
            return $allow;
        }

        if (!is_string($css_test_string)) {
            return false;
        }

        $t = trim($css_test_string);
        if ($t === '') {
            return false;
        }

        if (preg_match('/\b(?:url|expression|javascript|@import|behavior|-moz-binding)\s*\(/i', $t)) {
            return false;
        }

        if (preg_match(
            '/^(?:color|background-color|border-color)\s*:\s*rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(?:\s*,\s*(?:0?\.\d+|1(?:\.0)?))?\s*\)\s*$/i',
            $t
        )) {
            return true;
        }

        if (preg_match(
            '/^(?:color|background-color|border-color)\s*:\s*hsla?\(\s*[\d.]+\s*,\s*[\d.]+%\s*,\s*[\d.]+%(?:\s*,\s*(?:0?\.\d+|1(?:\.0)?))?\s*\)\s*$/i',
            $t
        )) {
            return true;
        }

        if (preg_match('/^font-family\s*:/iu', $t) && strpos($t, '&') !== false) {
            if (preg_match(
                '/^font-family\s*:\s*(?:[\p{L}\p{N}\s\-_,.\'"]|&(?:#(?:x[0-9a-f]+|[0-9]+)|[a-z]+);)+$/iu',
                $t
            )) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get form entries from IvyFormsAPI
     *
     * @param int $formId
     * @param array $criteria
     * @return array
     */
    public static function getFormEntriesFromAPI(int $formId, array $criteria = []): array
    {
        $entries = IvyFormsAPI::getFormEntries($formId, $criteria);
        return is_wp_error($entries) ? [] : $entries;
    }

    /**
     * Generate form array for wpDataTables
     */
    public static function generateFormArray($content, $ivyFormsData): array
    {
        $tableArray = [];
        $origHeaders = [];

        // Allow perPage to be set in $ivyFormsData, default to 'all' for all entries
        // Will be implemented with server-side processing later
        if (!empty($ivyFormsData) && !isset($ivyFormsData->perPage)) {
            $ivyFormsData->perPage = 'all';
        }
        $searchCriteria = self::prepareSearchCriteria($ivyFormsData);
        $entries = self::getFormEntriesFromAPI($content->formId, $searchCriteria);

        if (empty($entries)) {
            return [];
        }

        // Get form fields
        $fields = IvyFormsAPI::getFields($content->formId);
        if (is_wp_error($fields)) {
            return [];
        }
        $usedOrigHeaders = [];

        foreach ($fields as $field) {
            $fieldId = $field->getId();
            if (in_array($fieldId, $content->fieldIds)) {
                // Generate MySQL-safe column name from field ID
                $origHeader = WDTTools::generateMySQLColumnName($fieldId, $usedOrigHeaders);
                $usedOrigHeaders[] = $origHeader;
                $origHeaders[$fieldId] = $origHeader;
            }
        }

        $entryColumns = EntryManager::getAllEntryColumns();
        foreach ($entryColumns as $key => $label) {
            if (in_array($key, $content->fieldIds)) {
                $origHeader = WDTTools::generateMySQLColumnName($key, $usedOrigHeaders);
                $usedOrigHeaders[] = $origHeader;
                $origHeaders[$key] = $origHeader;
            }
        }

        // Get entry fields for all entries
        $entriesWithFields = IvyFormsAPI::getEntryFields($entries);

        // Group fields by entryId
        $fieldsByEntryId = [];
        foreach ($entriesWithFields as $field) {
            $fieldsByEntryId[$field['entryId']][] = $field;
        }

        // Process each entry
        foreach ($entries as $entry) {
            $tableArrayEntry = [];

            // Attach fields to entry
            $entry['fields'] = $fieldsByEntryId[$entry['id']] ?? [];

            // Process form fields - only selected fields
            foreach ($fields as $field) {
                $fieldId = $field->getId();
                if (in_array($fieldId, $content->fieldIds)) {
                    $fieldData = self::prepareFieldsData($field, $entry);
                    $tableArrayEntry[$origHeaders[$fieldId]] = $fieldData;
                }
            }

            // Process entry metadata columns - only selected fields
            foreach ($entryColumns as $key => $label) {
                if (in_array($key, $content->fieldIds)) {
                    $value = '';
                    switch ($key) {
                        case 'id':
                            $value = $entry['id'] ?? '';
                            break;
                        case 'dateCreated':
                            $value = $entry['dateCreated'] ?? '';
                            break;
                        case 'formId':
                            $value = $entry['formId'] ?? '';
                            break;
                        case 'userId':
                            $value = $entry['userId'] ?? '';
                            break;
                        case 'ipAddress':
                            $value = $entry['ipAddress'] ?? '';
                            break;
                        case 'userAgent':
                            $value = $entry['userAgent'] ?? '';
                            break;
                        case 'sourceURL':
                            $value = $entry['sourceURL'] ?? '';
                            break;
                        case 'starred':
                            $value = !empty($entry['starred']) ? 'Yes' : 'No';
                            break;
                        case 'status':
                            $value = $entry['status'] ?? '';
                            break;
                    }
                    $tableArrayEntry[$origHeaders[$key]] = $value;
                }
            }

            $tableArray[] = $tableArrayEntry;
        }

        return $tableArray;
    }

    /**
     * Normalize a date string to YYYY-MM-DD format
     */
    private static function normalizeDate($dateStr)
    {
        // Try ISO first
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            return $dateStr;
        }
        // Try DD/MM/YYYY
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateStr, $matches)) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
        // Try MM/DD/YYYY
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateStr, $matches)) {
            // This will be ambiguous, but fallback to DD/MM/YYYY above
            return $matches[3] . '-' . $matches[1] . '-' . $matches[2];
        }
        // Try YYYY/MM/DD
        if (preg_match('/^(\d{4})\/(\d{2})\/(\d{2})$/', $dateStr, $matches)) {
            return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
        }
        // Fallback: try strtotime
        $ts = strtotime($dateStr);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }
        // If all fails, return as is
        return $dateStr;
    }

    /**
     * Prepare search criteria for API queries
     */
    public static function prepareSearchCriteria($ivyFormsData): array
    {
        $criteria = [];
        if ($ivyFormsData === null) {
            return $criteria;
        }
        // Build filters for EntryRepository
        $filters = [];
        // Date filtering: build dateRange
        if (!empty($ivyFormsData->dateFrom) || !empty($ivyFormsData->dateTo)) {
            $dateFrom = !empty($ivyFormsData->dateFrom) ? self::normalizeDate($ivyFormsData->dateFrom) : null;
            $dateTo = !empty($ivyFormsData->dateTo) ? self::normalizeDate($ivyFormsData->dateTo) : null;
            $criteria['dateRange'] = [$dateFrom, $dateTo];
        }
        // User filtering
        if (!empty($ivyFormsData->filterByUser)) {
            $filters['userId'] = $ivyFormsData->filterByUser;
        }
        // Starred filtering
        if (!empty($ivyFormsData->filterByStarred)) {
            $filters['starred'] = true;
        }
        // Read/Unread filtering
        if (!empty($ivyFormsData->filterByRead)) {
            $filters['status'] = $ivyFormsData->filterByRead;
        }
        $criteria['filters'] = $filters;

        // Add perPage if set in ivyFormsData
        if (isset($ivyFormsData->perPage)) {
            $criteria['perPage'] = $ivyFormsData->perPage;
        }
        return $criteria;
    }

    /**
     * Add Ivyforms tab to table config UI
     */
    public static function addIvyformsTab()
    {
        ob_start();
        include __DIR__ . '/templates/ivyforms_tab.inc.php';
        $ivyTabpanel = apply_filters('wpdatatables_ivyforms_tabpanel', ob_get_contents());
        ob_end_clean();

        echo $ivyTabpanel;
    }

    /**
     * Add Ivyforms tab panel to table config UI
     */
    public static function addIvyformsTabPanel()
    {
        if (file_exists(__DIR__ . '/templates/ivyforms_tab_panel.inc.php')) {
            include __DIR__ . '/templates/ivyforms_tab_panel.inc.php';
        }
    }

    /**
     * Extend table config before saving
     */
    public static function extendTableConfig($tableArray)
    {
        if ($tableArray['table_type'] !== 'ivyforms') {
            return $tableArray;
        }

        $ivyFormsData = self::sanitizeIvyformsConfig(json_decode(
            stripslashes_deep($_POST['ivyforms'] ?? '{}')
        ));

        $advancedSettings = json_decode($tableArray['advanced_settings'] ?? '{}');
        $advancedSettings->ivyforms = array(
            'dateFrom' => $ivyFormsData->dateFrom,
            'dateTo' => $ivyFormsData->dateTo,
            'filterByUser' => $ivyFormsData->filterByUser,
            'filterByStarred' => $ivyFormsData->filterByStarred,
            'filterByRead' => $ivyFormsData->filterByRead
        );

        $tableArray['advanced_settings'] = json_encode($advancedSettings);

        return $tableArray;
    }

    /**
     * One-click install and activate IvyForms plugin
     */
    public static function oneClickInstallIvyForms()
    {
        check_ajax_referer('ivyforms_install', 'nonce');

        if (!current_user_can('install_plugins')) {
            wp_send_json_error(esc_html__('Permission denied.', 'wpdatatables'));
        }

        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/misc.php';
        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        $plugin_slug = 'ivyforms';
        $plugin_file = $plugin_slug . '/' . $plugin_slug . '.php';
        $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;

        // Check if plugin is already installed
        if (file_exists($plugin_path)) {
            // Plugin is installed, just activate it
            $activate = activate_plugin($plugin_file);

            if (is_wp_error($activate)) {
                wp_send_json_error(esc_html__('Activation failed: ', 'wpdatatables') . $activate->get_error_message());
            }

            wp_send_json_success(['message' => 'Plugin activated successfully']);
        } else {
            // Plugin is not installed, install and activate it
            include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

            $api = plugins_api('plugin_information', array('slug' => $plugin_slug, 'fields' => array('sections' => false)));

            if (is_wp_error($api)) {
                wp_send_json_error(esc_html__('Could not fetch plugin info', 'wpdatatables'));
            }

            $upgrader = new Plugin_Upgrader();
            $result = $upgrader->install($api->download_link);

            if (is_wp_error($result)) {
                wp_send_json_error(esc_html__('Install failed: ', 'wpdatatables') . $result->get_error_message());
            }

            $activate = activate_plugin($plugin_file);

            if (is_wp_error($activate)) {
                wp_send_json_error(esc_html__('Activation failed: ', 'wpdatatables') . $activate->get_error_message());
            }

            wp_send_json_success(['message' => esc_html__('Plugin installed and activated successfully', 'wpdatatables')]);
        }
    }

    /**
     * Get column headers from Ivyforms form fields
     *
     * @param int $formId
     * @param array $fieldIds
     * @return array
     */
    public static function getColumnHeaders(int $formId, array $fieldIds): array
    {
        $columnHeaders = [];

        $fields = IvyFormsAPI::getFields($formId);
        if (is_wp_error($fields)) {
            return $columnHeaders;
        }

        $usedOrigHeaders = [];

        // Process form fields - only for selected fields
        foreach ($fields as $field) {
            $fieldId = $field->getId();
            if (in_array($fieldId, $fieldIds)) {
                $label = $field->getFieldGeneralSettings()->getLabel();
                $origHeader = WDTTools::generateMySQLColumnName($fieldId, $usedOrigHeaders);
                $usedOrigHeaders[] = $origHeader;
                $columnHeaders[$origHeader] = $label;
            }
        }

        // Process entry columns - only for selected fields
        $entryColumns = EntryManager::getAllEntryColumns();
        foreach ($entryColumns as $key => $label) {
            if (in_array($key, $fieldIds)) {
                $origHeader = WDTTools::generateMySQLColumnName($key, $usedOrigHeaders);
                $usedOrigHeaders[] = $origHeader;
                $columnHeaders[$origHeader] = $label;
            }
        }

        return $columnHeaders;
    }

    /**
     * MIME types allowed for IvyForms signature data URIs (decoded bytes must match).
     *
     * @return string[]
     */
    private static function allowedSignatureImageMimeTypes(): array {
        return array(
            'image/png',
            'image/jpeg',
            'image/gif',
            'image/webp',
        );
    }

    /**
     * Normalize image MIME aliases for comparison and allowlist checks.
     *
     * @param string $mime Raw MIME string.
     * @return string
     */
    private static function normalizeSignatureImageMime(string $mime): string {
        $mime = strtolower(trim($mime));
        $aliases = array(
            'image/jpg' => 'image/jpeg',
            'image/pjpeg' => 'image/jpeg',
            'image/x-png' => 'image/png',
        );

        return isset($aliases[$mime]) ? $aliases[$mime] : $mime;
    }

    /**
     * Check if a string is a valid data-URI image with base64 payload.
     *
     * Requires header before the first comma to match `data:image/<mime>;base64`
     * so non-image payloads (e.g. application/pdf) are rejected. Decodes the payload,
     * verifies real image bytes via getimagesizefromstring, optional finfo_buffer,
     * and requires the declared header MIME to match the detected MIME and allowlist.
     *
     * @param string $data The data to check (may omit leading `data:` if it starts with `image/`).
     * @return bool
     */
    private static function isValidBase64Image($data): bool {
        if (!is_string($data)) {
            return false;
        }

        $normalized = trim($data);
        if ($normalized === '') {
            return false;
        }

        // IvyForms / browsers may store `image/png;base64,...` without the `data:` prefix.
        if (stripos($normalized, 'data:') !== 0 && stripos($normalized, 'image/') === 0) {
            $normalized = 'data:' . $normalized;
        }

        if (strpos($normalized, ',') === false) {
            return false;
        }

        list($header, $base64String) = explode(',', $normalized, 2);
        $header = trim($header);
        $base64String = trim($base64String);

        if ($header === '' || $base64String === '') {
            return false;
        }

        if (!preg_match('/^data:(image\/[^;]+);base64$/i', $header, $headerMimeMatch)) {
            return false;
        }

        $declaredMime = self::normalizeSignatureImageMime($headerMimeMatch[1]);
        $allowed = self::allowedSignatureImageMimeTypes();
        if (!in_array($declaredMime, $allowed, true)) {
            return false;
        }

        $binary = base64_decode($base64String, true);
        if ($binary === false || $binary === '') {
            return false;
        }

        if (!function_exists('getimagesizefromstring')) {
            return false;
        }

        $imageInfo = getimagesizefromstring($binary);
        if ($imageInfo === false || empty($imageInfo['mime'])) {
            return false;
        }

        $detectedMime = self::normalizeSignatureImageMime($imageInfo['mime']);
        if (!in_array($detectedMime, $allowed, true) || $detectedMime !== $declaredMime) {
            return false;
        }

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $probeMime = finfo_buffer($finfo, $binary, FILEINFO_MIME_TYPE);
                finfo_close($finfo);
                if (is_string($probeMime) && $probeMime !== '') {
                    $probeNorm = self::normalizeSignatureImageMime($probeMime);
                    if (strpos($probeNorm, 'image/') === 0) {
                        if (!in_array($probeNorm, $allowed, true) || $probeNorm !== $detectedMime) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Extract raw image source from possible signature value formats.
     */
    private static function extractSignatureImageSource($value): string {
        if (!is_string($value)) {
            return '';
        }

        $decodedValue = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $trimmedValue = trim($decodedValue);

        if ($trimmedValue === '') {
            return '';
        }

        if (preg_match('/^<img\\b[^>]*\\bsrc\\s*=\\s*(["\'])(.*?)\\1/i', $trimmedValue, $matches)) {
            return trim($matches[2]);
        }

        if (preg_match('/^<img\\b[^>]*\\bsrc\\s*=\\s*([^\s>]+)/i', $trimmedValue, $matches)) {
            return trim($matches[1], " \t\n\r\0\x0B\"'");
        }

        return $trimmedValue;
    }

    /**
     * Normalize signature data to proper data URL format
     *
     * Only prefixes `data:` when the value is already a base64 image MIME fragment
     * (`image/<subtype>;base64,...`), matching the same image data URI rules as isValidBase64Image().
     *
     * @param string $value The signature value
     * @return string Properly formatted data URL
     */
    private static function normalizeSignatureDataUrl(string $value): string {
        $value = trim($value);

        if (stripos($value, 'data:') === 0) {
            return $value;
        }

        if (preg_match('/^image\/[^;]+;base64,/i', $value)) {
            return 'data:' . $value;
        }

        return $value;
    }

    /**
     * IvyForms HTML block field type (slug is `html` in FieldType / builder).
     *
     * @param mixed $fieldType Raw type from Field::getType().
     * @return bool
     */
    private static function isHtmlFieldType($fieldType): bool {
        return is_string($fieldType) && strcasecmp(trim($fieldType), 'html') === 0;
    }

    /**
     * IvyForms signature field type.
     *
     * @param mixed $fieldType Raw type from Field::getType().
     * @return bool
     */
    private static function isSignatureFieldType($fieldType): bool {
        return is_string($fieldType) && strtolower(trim($fieldType)) === 'signature';
    }

    /**
     * Read htmlContent from field settings for HTML field type
     */
    private static function getHtmlFieldContent($field): string {
        if (!is_object($field)) {
            return '';
        }

        if (method_exists($field, 'getFieldGeneralSettings')) {
            $generalSettings = $field->getFieldGeneralSettings();

            if (is_object($generalSettings) && method_exists($generalSettings, 'getHtmlContent')) {
                return (string)$generalSettings->getHtmlContent();
            }

            if (is_object($generalSettings) && method_exists($generalSettings, 'toArray')) {
                $settingsArr = $generalSettings->toArray();
                if (isset($settingsArr['htmlContent']) && is_string($settingsArr['htmlContent'])) {
                    return $settingsArr['htmlContent'];
                }
            }
        }

        if (method_exists($field, 'toArray')) {
            $fieldArr = $field->toArray();

            if (isset($fieldArr['htmlContent']) && is_string($fieldArr['htmlContent'])) {
                return $fieldArr['htmlContent'];
            }

            if (isset($fieldArr['settings'])) {
                $settings = is_array($fieldArr['settings'])
                    ? $fieldArr['settings']
                    : json_decode((string)$fieldArr['settings'], true);

                if (is_array($settings) && isset($settings['htmlContent']) && is_string($settings['htmlContent'])) {
                    return $settings['htmlContent'];
                }
            }
        }

        return '';
    }

    /**
     * Resolve rating meta from field settings and options.
     *
     * @return array{max: float, icon: string}
     */
    private static function getRatingMeta($field): array {
        $meta = array(
            'max' => 5.0,
            'icon' => 'star',
        );

        if (!is_object($field)) {
            return $meta;
        }

        if (method_exists($field, 'getFieldAdvancedSettings')) {
            $advancedSettings = $field->getFieldAdvancedSettings();
            if (is_object($advancedSettings) && method_exists($advancedSettings, 'toArray')) {
                $advancedArr = $advancedSettings->toArray();
                if (isset($advancedArr['ratingIcon']) && is_string($advancedArr['ratingIcon']) && $advancedArr['ratingIcon'] !== '') {
                    $meta['icon'] = strtolower(trim($advancedArr['ratingIcon']));
                }
            }
        }

        if (method_exists($field, 'getFieldGeneralSettings')) {
            $generalSettings = $field->getFieldGeneralSettings();
            if (is_object($generalSettings) && method_exists($generalSettings, 'getMaxValue')) {
                $configuredMax = $generalSettings->getMaxValue();
                if (is_numeric($configuredMax) && (float)$configuredMax > 0) {
                    $meta['max'] = (float)$configuredMax;
                }
            }
        }

        $fieldId = method_exists($field, 'getId') ? (int)$field->getId() : 0;
        if ($fieldId > 0) {
            if (!array_key_exists($fieldId, self::$ratingOptionsCache)) {
                $options = IvyFormsAPI::getFieldOptions($fieldId);
                self::$ratingOptionsCache[$fieldId] = is_wp_error($options) || !is_array($options) ? array() : $options;
            }

            $options = self::$ratingOptionsCache[$fieldId];

            if (!empty($options)) {
                $numericValues = array();

                foreach ($options as $option) {
                    if (is_object($option) && method_exists($option, 'getValue')) {
                        $optionValue = $option->getValue();
                    } elseif (is_array($option) && isset($option['value'])) {
                        $optionValue = $option['value'];
                    } else {
                        $optionValue = null;
                    }

                    if (is_numeric($optionValue)) {
                        $numericValues[] = (float)$optionValue;
                    }
                }

                if (!empty($numericValues)) {
                    $maxFromOptions = max($numericValues);
                    if ($maxFromOptions > 0) {
                        $meta['max'] = $maxFromOptions;
                    }
                } else {
                    $meta['max'] = (float)count($options);
                }
            }
        }

        return $meta;
    }

    /**
     * Resolve display markup by rating icon type.
     */
    private static function getRatingIconMarkup(string $icon, bool $filled): string {
        $normalized = strtolower(trim($icon));

        if (in_array($normalized, array('heart', 'hearts'), true)) {
            return $filled ? '&#9829;' : '&#9825;';
        }

        if (in_array($normalized, array('like', 'likes', 'thumb', 'thumbs', 'thumbs-up', 'thumbs_up', 'thumb-up', 'thumb_up'), true)) {
            return '<span class="dashicons dashicons-thumbs-up" aria-hidden="true"></span>';
        }

        return $filled ? '&#9733;' : '&#9734;';
    }

    /**
     * Build rating placeholder marker from numeric value and field settings.
     */
    private static function buildRatingPlaceholder($value, $field): string {
        if (!is_numeric($value)) {
            return '';
        }

        $rating = (float)$value;
        $meta = self::getRatingMeta($field);
        $iconSlug = preg_replace('/[^a-z0-9_-]/', '', strtolower($meta['icon']));
        if ($iconSlug === '') {
            $iconSlug = 'star';
        }

        return 'WDTRTG:' . $rating . ':' . $meta['max'] . ':' . $iconSlug;
    }

    /**
     * Check if field type should be treated as rating.
     */
    private static function isRatingFieldType($fieldType): bool {
        if (!is_string($fieldType)) {
            return false;
        }

        return strtolower(trim($fieldType)) === 'rating';
    }

    /**
     * Convert a rating marker to star-based HTML output.
     */
    private static function renderRatingPlaceholder($marker): string {
        if (!is_string($marker) || !preg_match('/^WDTRTG:([0-9]+(?:\.[0-9]+)?):([0-9]+(?:\.[0-9]+)?):([a-z0-9_-]+)$/', trim($marker), $matches)) {
            return $marker;
        }

        $rating = (float)$matches[1];
        $maxRating = (float)$matches[2];
        $icon = $matches[3];

        if ($maxRating <= 0) {
            $maxRating = 5.0;
        }

        if ($rating < 0) {
            $rating = 0.0;
        }

        if ($rating > $maxRating) {
            $rating = $maxRating;
        }

        $roundedMax = (int)round($maxRating);
        if ($roundedMax < 1) {
            $roundedMax = 1;
        }

        $filled = (int)round($rating);
        if ($filled < 0) {
            $filled = 0;
        }

        if ($filled > $roundedMax) {
            $filled = $roundedMax;
        }

        $ratingLabel = rtrim(rtrim(number_format($rating, 2, '.', ''), '0'), '.');
        $maxLabel = rtrim(rtrim(number_format($maxRating, 2, '.', ''), '0'), '.');

        $iconsHtml = '';
        for ($i = 1; $i <= $roundedMax; $i++) {
            $isFilled = $i <= $filled;
            $symbols = self::getRatingIconMarkup($icon, $isFilled);
            $iconClass = $isFilled ? 'wdt-ivy-rating-icon-filled' : 'wdt-ivy-rating-icon-empty';
            $iconStyle = $isFilled ? 'color:#FFD700;' : 'color:#CCCCCC;opacity:0.35;';
            $iconsHtml .= '<span class="wdt-ivy-rating-icon ' . esc_attr($iconClass) . '" style="' . esc_attr($iconStyle) . '">' . $symbols . '</span>';
        }

        return '<span class="wdt-ivy-rating" title="' . esc_attr($ratingLabel . '/' . $maxLabel) . '">'
            . '<span class="wdt-ivy-rating-icons">' . $iconsHtml . '</span>'
            . ' <span class="wdt-ivy-rating-value">(' . esc_html($ratingLabel . '/' . $maxLabel) . ')</span>'
            . '</span>';
    }

    /**
     * In-memory cache for signature data, keyed by md5 hash.
     * Avoids passing the huge base64 string through wp_kses_post which can mangle it.
     *
     * @var array<string, string>
     */
    private static $signatureCache = [];

    /**
     * In-memory cache for rating options by field id.
     *
     * @var array<int, array>
     */
    private static $ratingOptionsCache = [];

    /**
     * Whether a wpDataTable ID is an IvyForms-backed table (cached per request).
     *
     * @var array<int, bool>
     */
    private static $ivyFormsTableTypeCache = [];

    /**
     * True only while WPDataTable::arrayBasedConstruct runs for IvyForms (wp_kses_post + safecss).
     *
     * @var bool
     */
    private static $relaxSafecssForIvyHtml = false;

    /**
     * Prepare fields data for table display
     */
    public static function prepareFieldsData($field, $entry)
    {
        $fieldId = $field->getId();
        $fieldType = $field->getType();

        // Check if entry has fields array
        if (isset($entry['fields']) && is_array($entry['fields'])) {
            foreach ($entry['fields'] as $entryField) {
                if (isset($entryField['fieldId']) && $entryField['fieldId'] == $fieldId) {
                    $fieldValue = $entryField['fieldValue'] ?? '';

                    // HTML blocks: IvyForms often stores an empty row; content lives on the field (htmlContent).
                    if (self::isHtmlFieldType($fieldType)) {
                        $trimmedHtml = is_string($fieldValue) ? trim($fieldValue) : '';
                        if ($trimmedHtml !== '') {
                            return $fieldValue;
                        }

                        return self::getHtmlFieldContent($field);
                    }

                    if (self::isRatingFieldType($fieldType)) {
                        $ratingPlaceholder = self::buildRatingPlaceholder($fieldValue, $field);
                        return $ratingPlaceholder !== '' ? $ratingPlaceholder : $fieldValue;
                    }

                    if (self::isSignatureFieldType($fieldType)) {
                        $signatureSource = self::extractSignatureImageSource($fieldValue);
                        if (self::isValidBase64Image($signatureSource)) {
                            // Signature fields: store base64 in static cache, pass only the hash.
                            // wp_kses_post (in arrayBasedConstruct) can mangle large base64 strings.
                            // The hash (plain alphanumeric) passes through untouched.
                            $normalizedSource = self::normalizeSignatureDataUrl($signatureSource);
                            $hash = md5($normalizedSource);
                            self::$signatureCache[$hash] = $normalizedSource;

                            return 'WDTSIG:' . $hash;
                        }
                    }

                    return $fieldValue;
                }
            }
        }

        if (self::isHtmlFieldType($fieldType)) {
            return self::getHtmlFieldContent($field);
        }

        return '';
    }

    /**
     * wpdatatables_filter_cell_output — replace IvyForms rating/signature placeholders with HTML.
     *
     * @param mixed  $cellOutput Cell content after column formatting.
     * @param int    $tableId wpDataTable ID.
     * @param string|null $columnName Column key (unused; kept for filter arity).
     * @return mixed
     */
    public static function filterIvyFormsCellOutput($cellOutput, $tableId, $columnName = null) {
        return self::resolveIvyFormsPlaceholderMarkers($cellOutput, $tableId);
    }

    /**
     * wpdatatables_filter_cell_val — same placeholder resolution for code paths that only apply cell_val.
     *
     * @param mixed $cellValue Cell value after prepareCellOutput.
     * @param int   $tableId wpDataTable ID.
     * @return mixed
     */
    public static function filterIvyFormsCellVal($cellValue, $tableId) {
        return self::resolveIvyFormsPlaceholderMarkers($cellValue, $tableId);
    }

    /**
     * Whether the table is IvyForms-backed (cached). Used so global cell filters skip non-Ivy tables cheaply.
     *
     * @param int $tableId Table ID from the filter.
     * @return bool
     */
    private static function isIvyFormsDataTableId($tableId) {
        $tableId = absint($tableId);
        if (!$tableId) {
            return false;
        }

        if (array_key_exists($tableId, self::$ivyFormsTableTypeCache)) {
            return self::$ivyFormsTableTypeCache[$tableId];
        }

        if (!class_exists('WDTConfigController')) {
            self::$ivyFormsTableTypeCache[$tableId] = false;
            return false;
        }

        try {
            $table = WDTConfigController::loadTableFromDB($tableId, true);
        } catch (Exception $e) {
            self::$ivyFormsTableTypeCache[$tableId] = false;
            return false;
        }

        $isIvyForms = is_object($table) && isset($table->table_type) && $table->table_type === 'ivyforms';
        self::$ivyFormsTableTypeCache[$tableId] = $isIvyForms;

        return $isIvyForms;
    }

    /**
     * Replace WDTRTG / WDTSIG markers for IvyForms tables only.
     *
     * @param mixed $cellContent Raw or formatted cell string.
     * @param int   $tableId wpDataTable ID.
     * @return mixed
     */
    private static function resolveIvyFormsPlaceholderMarkers($cellContent, $tableId) {
        if (!is_string($cellContent)) {
            return $cellContent;
        }

        $trimmed = trim($cellContent);
        $isRatingMarker = (strpos($trimmed, 'WDTRTG:') === 0);
        $isSignatureMarker = (bool) preg_match('/^WDTSIG:[a-f0-9]{32}$/', $trimmed);

        if (!$isRatingMarker && !$isSignatureMarker) {
            return $cellContent;
        }

        if (!self::isIvyFormsDataTableId($tableId)) {
            return $cellContent;
        }

        if ($isRatingMarker) {
            return self::renderRatingPlaceholder($cellContent);
        }

        return self::renderSignaturePlaceholderFromMarker($trimmed);
    }

    /**
     * Convert a WDTSIG:<hash> cell string to an <img> tag using the in-request cache.
     *
     * @param string $trimmedOutput Trimmed cell value matching WDTSIG pattern.
     * @return string
     */
    private static function renderSignaturePlaceholderFromMarker($trimmedOutput) {
        if (!preg_match('/^WDTSIG:([a-f0-9]{32})$/', $trimmedOutput, $matches)) {
            return $trimmedOutput;
        }

        $hash = $matches[1];

        if (!isset(self::$signatureCache[$hash])) {
            return '';
        }

        $dataUrl = self::normalizeSignatureDataUrl(self::$signatureCache[$hash]);
        if (!self::isValidBase64Image($dataUrl)) {
            return '';
        }

        return '<img src="' . esc_attr($dataUrl) . '" alt="Signature" class="wdt-signature-image" style="max-width:100%;height:auto;max-height:200px;border:1px solid #ddd;border-radius:4px;" />';
    }
}

add_action('init', array('WdtIvyFormsIntegration', 'init'));
