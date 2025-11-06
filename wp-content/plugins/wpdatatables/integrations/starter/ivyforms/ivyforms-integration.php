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
        $wpDataTable->arrayBasedConstruct(self::generateFormArray($content, $ivyFormsData), $params);
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
     * Prepare fields data for table display
     */
    public static function prepareFieldsData($field, $entry)
    {
        $fieldId = $field->getId();

        // Check if entry has fields array - for now, type is not relevant
        // TODO Check for password, signature etc.
        if (isset($entry['fields']) && is_array($entry['fields'])) {
            foreach ($entry['fields'] as $entryField) {
                if (isset($entryField['fieldId']) && $entryField['fieldId'] == $fieldId) {
                    return $entryField['fieldValue'] ?? '';
                }
            }
        }

        return '';
    }
}

add_action('init', array('WdtIvyFormsIntegration', 'init'));
