<?php

use IvyForms\Services\API\IvyFormsAPI;
use IvyForms\Services\Entry\Managers\EntryManager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WdtIvyFormsIntegration
{
    /**
     * Column options passed into the generate action (needed for SSP output formatting).
     *
     * @var array
     */
    private static $wdtParameters = [];

    /**
     * Entry meta keys that EntryRepository can sort by.
     *
     * @var string[]
     */
    private static $sortableEntryColumns = ['id', 'dateCreated', 'dateEdited', 'formName', 'status', 'userId', 'starred'];

    /**
     * Entry meta keys mappable to IvyForms filters (exact match on entries table).
     *
     * @var string[]
     */
    private static $filterableEntryMetaColumns = ['id', 'status', 'userId', 'starred'];

    /**
     * Entry meta keys that are not IvyForms field values (skip on create/update).
     *
     * @var string[]
     */
    private static $entryMetaKeys = [
        'id',
        'dateCreated',
        'formId',
        'formName',
        'userId',
        'ipAddress',
        'userAgent',
        'sourceURL',
        'starred',
        'status',
    ];

    public static function init()
    {
        // Add ivyforms to allowed table types
        if (class_exists('WPDataTable')) {
            WPDataTable::$allowedTableTypes[] = 'ivyforms';
        }

        add_action('wpdatatables_enqueue_on_edit_page', array(__CLASS__, 'enqueueAssets'));
        add_action('wpdatatables_enqueue_on_frontend', array(__CLASS__, 'enqueueFrontendAssets'));
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
        add_action('wpdatatables_above_table_alert', array(__CLASS__, 'alertServerSide'));
        add_filter('wpdatatables_filter_cell_output', array('WdtIvyFormsIntegration', 'filterIvyFormsCellOutput'), 10, 3);
        add_filter('wpdatatables_filter_cell_val', array('WdtIvyFormsIntegration', 'filterIvyFormsCellVal'), 10, 2);
        add_filter('safecss_filter_attr_allow_css', array(__CLASS__, 'allowIvyformsRichTextInlineCss'), 10, 2);
        add_action('wpdatatables_before_frontend_edit_row', array(__CLASS__, 'handleFrontendSave'), 1, 3);
        add_action('wp_ajax_wdt_delete_ivyforms_table_row', array(__CLASS__, 'handleFrontendDelete'));
        add_action('wp_ajax_nopriv_wdt_delete_ivyforms_table_row', array(__CLASS__, 'handleFrontendDelete'));
        add_filter('wpdatatables_filter_column_before_save', array(__CLASS__, 'applyColumnEditorDefaults'), 10, 2);
        add_filter('wpdatatables_possible_values_ivyforms', array(__CLASS__, 'getPossibleIvyFormsValuesRead'), 10, 3);
        add_filter('wpdatatables_filter_table_description', array(__CLASS__, 'filterTableDescription'), 10, 3);
    }

    /**
     * Whether Editable (new/edit/delete from WDT) is allowed — requires IvyForms Pro (D2).
     *
     * @return bool
     */
    public static function canEditEntriesFromWpDataTables(): bool
    {
        if (!class_exists('IvyForms\Services\API\IvyFormsAPI') || !IvyFormsAPI::isPluginActive()) {
            return false;
        }

        if (!method_exists(IvyFormsAPI::class, 'isProPluginActive') || !IvyFormsAPI::isProPluginActive()) {
            return false;
        }

        /**
         * Filter whether WDT may expose Editable for IvyForms tables.
         *
         * @param bool $canEdit Default: IvyForms Pro is active.
         */
        return (bool) apply_filters('wpdatatables_ivyforms_can_edit_entries', true);
    }

    /**
     * Localized data for admin/frontend IvyForms editing JS.
     *
     * @return array
     */
    private static function getEditingScriptData(): array
    {
        return [
            'canEdit' => self::canEditEntriesFromWpDataTables(),
            'notice' => esc_html__(
                'Front-end editing for IvyForms tables requires IvyForms Pro.',
                'wpdatatables'
            ),
        ];
    }

    /**
     * Enqueue frontend editor adapters for IvyForms tables.
     *
     * @param WPDataTable $wpDataTable
     * @return void
     */
    public static function enqueueFrontendAssets($wpDataTable)
    {
        if (!is_object($wpDataTable) || !method_exists($wpDataTable, 'getTableType')) {
            return;
        }

        if ($wpDataTable->getTableType() !== 'ivyforms') {
            return;
        }

        wp_enqueue_script(
            'wdt-ivyforms-editing',
            plugin_dir_url(__FILE__) . 'assets/js/ivyforms_editing.js',
            ['jquery', 'wdt-wpdatatables'],
            WDT_CURRENT_VERSION,
            true
        );
    }

    /**
     * Intercept wdt_save_table_frontend for ivyforms tables before MySQL path runs.
     *
     * @param array $formData
     * @param array $returnResult
     * @param int   $tableId
     * @return void
     */
    public static function handleFrontendSave($formData, $returnResult, $tableId)
    {
        $tableId = (int) $tableId;
        if (!$tableId) {
            return;
        }

        try {
            $tableData = WDTConfigController::loadTableFromDB($tableId);
        } catch (Exception $e) {
            return;
        }

        if (!is_object($tableData) || empty($tableData->table_type) || $tableData->table_type !== 'ivyforms') {
            return;
        }

        $result = [
            'success' => '',
            'error' => '',
            'is_new' => false,
        ];

        if (empty($tableData->editable)) {
            $result['error'] = esc_html__('Editing is not enabled for this table.', 'wpdatatables');
            echo wp_json_encode($result);
            exit();
        }

        if (!is_user_logged_in()) {
            $result['error'] = esc_html__('You must be logged in to edit this table.', 'wpdatatables');
            echo wp_json_encode($result);
            exit();
        }

        if (!wdtCurrentUserCanEdit($tableData->editor_roles, $tableId)) {
            $result['error'] = esc_html__('You do not have permission to edit this table.', 'wpdatatables');
            echo wp_json_encode($result);
            exit();
        }

        if (!self::canEditEntriesFromWpDataTables()) {
            $result['error'] = esc_html__('Front-end editing for IvyForms tables requires IvyForms Pro.', 'wpdatatables');
            echo wp_json_encode($result);
            exit();
        }

        $content = json_decode($tableData->content);
        if (!is_object($content) || empty($content->formId)) {
            $result['error'] = esc_html__('Invalid IvyForms table configuration.', 'wpdatatables');
            echo wp_json_encode($result);
            exit();
        }

        $formId = (int) $content->formId;
        $isDuplicate = isset($_POST['isDuplicate']) && (
            $_POST['isDuplicate'] === true
            || $_POST['isDuplicate'] === 'true'
            || $_POST['isDuplicate'] === 1
            || $_POST['isDuplicate'] === '1'
        );

        $formData = is_array($formData) ? stripslashes_deep($formData) : [];
        unset($formData['table_id'], $formData['nonce']);

        $columnsData = WDTConfigController::loadColumnsFromDB($tableId);
        $idColumn = self::resolveEntryIdColumn($columnsData, $content);
        if ($idColumn === null) {
            $result['error'] = esc_html__('This table is missing an Entry ID column required for editing.', 'wpdatatables');
            echo wp_json_encode($result);
            exit();
        }

        $idKey = $idColumn->orig_header;
        $rawId = $formData[$idKey] ?? 0;
        $idVal = self::parseEntryIdFromFormData($rawId, $isDuplicate);
        unset($formData[$idKey]);

        $fieldValues = self::mapFormDataToIvyFormsFieldValues($formData, $content);

        if ($idVal > 0) {
            $matchingEntries = self::getFormEntriesFromAPI(
                $formId,
                [
                    'filters' => ['id' => $idVal],
                    'perPage' => 1,
                ]
            );
            if ($matchingEntries === []) {
                $result['error'] = esc_html__('Entry not found for this form.', 'wpdatatables');
                echo wp_json_encode($result);
                exit();
            }
        }

        try {
            if ($idVal > 0) {
                if (!self::hasIvyFormsFeature('edit_entries')) {
                    throw new Exception(
                        __('Updating IvyForms entries requires the edit_entries Pro feature.', 'wpdatatables')
                    );
                }
                self::updateIvyFormsEntry($idVal, $fieldValues, $formId);
                $result['success'] = $idVal;
                $result['is_new'] = false;
            } else {
                if (!self::hasIvyFormsFeature('admin_add_entry')) {
                    throw new Exception(
                        __('Creating IvyForms entries requires the admin_add_entry Pro feature.', 'wpdatatables')
                    );
                }
                $newId = self::createIvyFormsEntry($formId, $fieldValues);
                $result['success'] = $newId;
                $result['is_new'] = true;
            }
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        if ($result['error'] === '' && !empty($result['success'])) {
            self::clearFormEntriesCache($formId);
        }

        /**
         * Fires after an IvyForms entry create/update attempt from wpDataTables.
         *
         * @param array  $result
         * @param int    $tableId
         * @param int    $formId
         * @param int    $idVal Entry ID before create (0) or update target.
         * @param array  $fieldValues
         */
        do_action('wpdatatables_ivyforms_after_frontend_save', $result, $tableId, $formId, $idVal, $fieldValues);

        echo wp_json_encode($result);
        exit();
    }

    /**
     * AJAX: delete an IvyForms entry from the standard WDT delete modal.
     *
     * @return void
     */
    public static function handleFrontendDelete()
    {
        $result = [
            'success' => '',
            'error' => '',
        ];

        $tableId = isset($_POST['table_id']) ? (int) $_POST['table_id'] : 0;
        if (!$tableId || !isset($_POST['wdtNonce'])
            || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wdtNonce'])), 'wdtFrontendEditTableNonce' . $tableId)
        ) {
            $result['error'] = esc_html__('Security check failed.', 'wpdatatables');
            echo wp_json_encode($result);
            exit();
        }

        try {
            $tableData = WDTConfigController::loadTableFromDB($tableId);
        } catch (Exception $e) {
            $result['error'] = esc_html__('Table not found.', 'wpdatatables');
            echo wp_json_encode($result);
            exit();
        }

        if (!is_object($tableData) || $tableData->table_type !== 'ivyforms') {
            $result['error'] = esc_html__('Invalid table type.', 'wpdatatables');
            echo wp_json_encode($result);
            exit();
        }

        if (empty($tableData->editable) || !wdtCurrentUserCanEdit($tableData->editor_roles, $tableId)) {
            $result['error'] = esc_html__('You do not have permission to delete rows from this table.', 'wpdatatables');
            echo wp_json_encode($result);
            exit();
        }

        if (!is_user_logged_in()) {
            $result['error'] = esc_html__('You must be logged in to delete rows from this table.', 'wpdatatables');
            echo wp_json_encode($result);
            exit();
        }

        if (!self::canEditEntriesFromWpDataTables()) {
            $result['error'] = esc_html__('Front-end editing for IvyForms tables requires IvyForms Pro.', 'wpdatatables');
            echo wp_json_encode($result);
            exit();
        }

        $entryId = isset($_POST['id_val']) ? (int) $_POST['id_val'] : 0;
        if ($entryId <= 0) {
            $result['error'] = esc_html__('Invalid entry ID.', 'wpdatatables');
            echo wp_json_encode($result);
            exit();
        }

        $content = json_decode($tableData->content ?? '{}');
        $formId = is_object($content) ? (int) ($content->formId ?? 0) : 0;
        if ($formId <= 0) {
            $result['error'] = esc_html__('Invalid IvyForms table configuration.', 'wpdatatables');
            echo wp_json_encode($result);
            exit();
        }

        $matchingEntries = self::getFormEntriesFromAPI(
            $formId,
            [
                'filters' => ['id' => $entryId],
                'perPage' => 1,
            ]
        );
        if ($matchingEntries === []) {
            $result['error'] = esc_html__('Entry not found for this form.', 'wpdatatables');
            echo wp_json_encode($result);
            exit();
        }

        try {
            self::deleteIvyFormsEntry($entryId);
            self::clearFormEntriesCache($formId);
            $result['success'] = true;
            $result['error'] = '';
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        echo wp_json_encode($result);
        exit();
    }

    /**
     * Resolve the IvyForms Entry ID column from persisted WDT column config.
     *
     * Prefers the column mapped to IvyForms entry meta "id" over a generic id_column flag.
     *
     * @param array  $columnsData
     * @param object $content
     * @return object|null
     */
    private static function resolveEntryIdColumn(array $columnsData, $content): ?object
    {
        $byIdColumnFlag = null;

        foreach ($columnsData as $column) {
            if (self::mapOrigHeaderToFieldKey($column->orig_header, $content) === 'id') {
                return $column;
            }
            if (!empty($column->id_column)) {
                $byIdColumnFlag = $column;
            }
        }

        return $byIdColumnFlag;
    }

    /**
     * Parse Entry ID from frontend modal form data.
     *
     * Duplicates and new entries use 0. Missing or non-numeric values mean create (WDT legacy cast).
     *
     * @param mixed $rawId
     * @param bool  $isDuplicate
     * @return int
     */
    private static function parseEntryIdFromFormData($rawId, bool $isDuplicate): int
    {
        if ($isDuplicate) {
            return 0;
        }

        if ($rawId === null || $rawId === '' || $rawId === false) {
            return 0;
        }

        if (is_numeric($rawId)) {
            return max(0, (int) $rawId);
        }

        return 0;
    }

    /**
     * Drop request-scoped IvyForms entry caches after create/update/delete.
     *
     * @param int|null $formId
     * @return void
     */
    private static function clearFormEntriesCache(?int $formId = null): void
    {
        if ($formId === null) {
            self::$formEntriesCache = [];
            self::$distinctValuesCache = [];

            return;
        }

        unset(self::$formEntriesCache[$formId]);

        foreach (array_keys(self::$distinctValuesCache) as $cacheKey) {
            if (strpos((string) $cacheKey, $formId . ':') === 0) {
                unset(self::$distinctValuesCache[$cacheKey]);
            }
        }
    }

    /**
     * Map WDT formdata (keyed by orig_header) to IvyForms fieldId => value.
     *
     * @param array  $formData
     * @param object $content
     * @return array<string, mixed>
     */
    private static function mapFormDataToIvyFormsFieldValues(array $formData, $content): array
    {
        $values = [];

        foreach ($formData as $origHeader => $value) {
            if (!is_string($origHeader) && !is_int($origHeader)) {
                continue;
            }
            $origHeader = (string) $origHeader;
            $fieldKey = self::mapOrigHeaderToFieldKey($origHeader, $content);
            if ($fieldKey === null) {
                continue;
            }
            if (in_array($fieldKey, self::$entryMetaKeys, true)) {
                continue;
            }
            // Only persist form field values (numeric field IDs).
            if (!ctype_digit((string) $fieldKey)) {
                continue;
            }

            $fieldId = (int) $fieldKey;
            $compoundMaps = self::getCompoundFieldMapsForForm((int) $content->formId);
            // Parent name/address values are derived by IvyForms from subfields — never POST them.
            if (isset($compoundMaps['parentIds'][$fieldId])) {
                continue;
            }

            if (is_string($value)) {
                $value = wp_kses_post($value);
            }
            $values[(string) $fieldKey] = $value;
        }

        return $values;
    }

    /**
     * Nest flat compound subfield values under their parent field ID (IvyForms submission shape).
     *
     * IvyForms stores name/address parent values as nested arrays (nameField1, streetAddress, etc.).
     * wpDataTables sends flat fieldId => value from the edit modal.
     *
     * @param array $values Flat fieldId => value map.
     * @param array $formFields IvyForms field entities.
     * @return array<string, mixed>
     */
    private static function normalizeCompoundSubmissionValues(array $values, array $formFields): array
    {
        $maps = self::buildCompoundFieldMaps($formFields);
        if (empty($maps['parentIds']) || empty($maps['childToParent'])) {
            return $values;
        }

        $fieldById = [];
        foreach ($formFields as $field) {
            if (is_object($field) && method_exists($field, 'getId')) {
                $fieldById[(int) $field->getId()] = $field;
            }
        }

        $parentChildrenMap = [];
        foreach ($maps['childToParent'] as $childId => $parentId) {
            $parentChildrenMap[(int) $parentId][] = (int) $childId;
        }

        $submissionData = $values;
        $parentSubfieldKeys = \IvyForms\Common\Helpers\EntryHelper::buildParentSubfieldKeys(
            $parentChildrenMap,
            $submissionData,
            $formFields
        );

        foreach ($maps['parentIds'] as $parentId => $parentType) {
            $childIds = $parentChildrenMap[(int) $parentId] ?? [];
            if ($childIds === []) {
                continue;
            }

            $orderedChildIds = \IvyForms\Common\Helpers\NameFieldKeyHelper::sortChildIdsByDisplayOrder(
                $childIds,
                $fieldById
            );

            $parentPayload = [];
            foreach ($orderedChildIds as $position => $childId) {
                $childKey = (string) $childId;
                if (!array_key_exists($childKey, $values) && !array_key_exists($childId, $values)) {
                    continue;
                }
                $childValue = $values[$childKey] ?? $values[$childId] ?? null;
                if ($childValue === null || $childValue === '') {
                    continue;
                }

                $subKey = $parentSubfieldKeys[$childId] ?? null;
                if ($subKey === null) {
                    $subKey = self::resolveCompoundSubfieldKey(
                        $fieldById[$childId] ?? null,
                        $position,
                        $parentType
                    );
                }
                if ($subKey === null) {
                    continue;
                }
                $parentPayload[$subKey] = $childValue;
            }

            if ($parentPayload !== []) {
                $submissionData[(string) $parentId] = $parentPayload;
            }
        }

        return $submissionData;
    }

    /**
     * Resolve IvyForms compound subfield storage key for a child field.
     *
     * @param object|null $childField
     * @param int         $position Zero-based position among siblings (display order).
     * @param string      $parentType name|address
     * @return string|null
     */
    private static function resolveCompoundSubfieldKey(?object $childField, int $position, string $parentType): ?string
    {
        if ($parentType === 'name') {
            $key = \IvyForms\Common\Helpers\NameFieldKeyHelper::resolveStoredNameSubfieldKey($childField);
            return $key ?? ('nameField' . ($position + 1));
        }

        if ($parentType === 'address') {
            if ($childField !== null && method_exists($childField, 'getAdditionalProperty')) {
                $addressType = $childField->getAdditionalProperty('addressType');
                if (is_string($addressType) && $addressType !== '') {
                    return $addressType;
                }
            }
            $defaultOrder = ['streetAddress', 'addressLine2', 'city', 'state', 'zip', 'country'];
            return $defaultOrder[$position] ?? ('addressPart' . ($position + 1));
        }

        return null;
    }

    /**
     * Create an IvyForms entry
     *
     * @param int   $formId
     * @param array $values fieldId => value
     * @return int New entry ID
     * @throws Exception
     */
    private static function createIvyFormsEntry(int $formId, array $values): int
    {
        $container = self::getIvyFormsContainer();
        if ($container === null) {
            throw new Exception(__('IvyForms is not available.', 'wpdatatables'));
        }

        /** @var \IvyForms\Services\Form\FormService $formService */
        $formService = $container->get(\IvyForms\Services\Form\FormService::class);
        /** @var \IvyForms\Services\Field\FieldService $fieldService */
        $fieldService = $container->get(\IvyForms\Services\Field\FieldService::class);
        /** @var \IvyForms\Services\Entry\EntryService $entryService */
        $entryService = $container->get(\IvyForms\Services\Entry\EntryService::class);

        $form = $formService->getFormById($formId);
        if (!is_object($form)) {
            throw new Exception(__('Form not found.', 'wpdatatables'));
        }
        if (!$form->isStoreEntries()) {
            throw new Exception(__('This form does not store entries.', 'wpdatatables'));
        }

        $formFields = $fieldService->getAllFields($formId);
        $fieldService->validateFieldsType($formFields);

        $values = self::normalizeCompoundSubmissionValues($values, $formFields);

        $params = [
            'formId' => $formId,
            'values' => $values,
        ];
        $submissionData = \IvyForms\Common\Sanitizer\Sanitizer::sanitizeFormSubmissionData($params, $formFields);

        do_action('ivyforms/form/validate_submission', $submissionData, $formFields);
        do_action('ivyforms/form/before_submission', $formId, $submissionData, $formFields);

        $entryData = \IvyForms\Common\Helpers\EntryHelper::buildEntryData($formId);
        $entryObj = \IvyForms\Factory\Entry\EntryFactory::create($entryData);
        $entryId = $entryService->getEntryManager()->createEntry($entryObj);
        $entryService->getEntryFieldManager()->addEntryFields($formFields, $entryId, $submissionData);

        if (class_exists('\IvyForms\Services\Placeholder\PlaceholderService')
            && class_exists('\IvyForms\Services\Notification\NotificationService')
            && class_exists('\IvyForms\Services\Mailer\MailerService')
        ) {
            try {
                $fieldLabels = \IvyForms\Services\Placeholder\PlaceholderService::buildFieldLabels($formFields);
                $fieldData = \IvyForms\Services\Placeholder\PlaceholderService::buildFieldData($formFields, $submissionData);
                $generalData = \IvyForms\Services\Placeholder\PlaceholderService::buildGeneralData($entryId, $submissionData);
                /** @var \IvyForms\Services\Notification\NotificationService $notificationService */
                $notificationService = $container->get(\IvyForms\Services\Notification\NotificationService::class);
                /** @var \IvyForms\Services\Mailer\MailerService $mailerService */
                $mailerService = $container->get(\IvyForms\Services\Mailer\MailerService::class);
                $notificationService->processNotifications(
                    $formId,
                    $submissionData,
                    $formFields,
                    $fieldData,
                    $generalData,
                    $mailerService,
                    $fieldLabels
                );
            } catch (Exception $e) {
                // Entry is stored; notification failure should not fail the WDT save.
            }
        }

        do_action('ivyforms/form/after_submission', $formId, $submissionData, $formFields, $entryId);

        return (int) $entryId;
    }

    /**
     * Update an IvyForms entry via Pro EntryUpdateService.
     *
     * @param int   $entryId
     * @param array $values fieldId => value
     * @param int   $formId
     * @return void
     * @throws Exception
     */
    private static function updateIvyFormsEntry(int $entryId, array $values, int $formId): void
    {
        $container = self::getIvyFormsContainer();
        if ($container === null) {
            throw new Exception(__('IvyForms is not available.', 'wpdatatables'));
        }

        $updateClass = '\IvyFormsPro\Plans\Essentials\Services\Entry\EntryUpdateService';
        if (!class_exists($updateClass)) {
            throw new Exception(__('IvyForms Pro entry update is not available.', 'wpdatatables'));
        }

        /** @var \IvyForms\Services\Field\FieldService $fieldService */
        $fieldService = $container->get(\IvyForms\Services\Field\FieldService::class);
        $formFields = $fieldService->getAllFields($formId);
        $values = self::normalizeCompoundSubmissionValues($values, $formFields);

        /** @var object $updateService */
        $updateService = $container->get($updateClass);
        $updateService->update($entryId, [
            'fields' => $values,
        ]);
    }

    /**
     * Delete an IvyForms entry and its field rows.
     *
     * @param int $entryId
     * @return void
     * @throws Exception
     */
    private static function deleteIvyFormsEntry(int $entryId): void
    {
        $container = self::getIvyFormsContainer();
        if ($container === null) {
            throw new Exception(__('IvyForms is not available.', 'wpdatatables'));
        }

        /** @var \IvyForms\Services\Entry\EntryService $entryService */
        $entryService = $container->get(\IvyForms\Services\Entry\EntryService::class);
        $deletion = $entryService->getDeletionManager();
        $deleted = $deletion->deleteEntry($entryId);
        if (!$deleted) {
            throw new Exception(__('Entry could not be deleted.', 'wpdatatables'));
        }
        $deletion->deleteEntryFieldsByEntryIds([$entryId]);
    }

    /**
     * @return \IvyForms\Vendor\DI\Container|null
     */
    private static function getIvyFormsContainer()
    {
        if (!class_exists('\IvyForms\Plugin\Plugin')) {
            return null;
        }
        $plugin = \IvyForms\Plugin\Plugin::getInstance();
        if (!$plugin || empty($plugin->container)) {
            return null;
        }
        return $plugin->container;
    }

    /**
     * Check a Pro feature slug via FeatureService when available.
     *
     * @param string $featureSlug
     * @return bool
     */
    private static function hasIvyFormsFeature(string $featureSlug): bool
    {
        if (!self::canEditEntriesFromWpDataTables()) {
            return false;
        }

        $container = self::getIvyFormsContainer();
        $featureClass = '\IvyFormsPro\Services\Features\FeatureService';
        if ($container === null || !class_exists($featureClass)) {
            // Pro active but FeatureService unavailable — allow and let service assert.
            return true;
        }

        try {
            /** @var object $featureService */
            $featureService = $container->get($featureClass);
            $map = $featureService->getFeatures();
            return !empty($map['features'][$featureSlug]);
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * SSP warning alert above the table settings (complex field sort/search limits).
     *
     * @return void
     */
    public static function alertServerSide()
    {
        if (!self::shouldShowIvyFormsServerSideAlert()) {
            return;
        }

        ob_start();
        include __DIR__ . '/templates/alert_server_side.inc.php';
        $alertServerSide = apply_filters('wdt_alert_server_side_ivyforms', ob_get_contents());
        ob_end_clean();
        echo $alertServerSide;
    }

    /**
     * Whether the IvyForms SSP admin alert should render for the current table settings context.
     *
     * @return bool
     */
    private static function shouldShowIvyFormsServerSideAlert(): bool
    {
        $tableId = isset($_GET['table_id']) ? absint($_GET['table_id']) : 0;
        if ($tableId && class_exists('WDTConfigController')) {
            try {
                $tableData = WDTConfigController::loadTableFromDB($tableId);
            } catch (Exception $e) {
                return false;
            }

            return is_object($tableData)
                && ($tableData->table_type ?? '') === 'ivyforms'
                && !empty($tableData->server_side);
        }

        $source = isset($_GET['source']) ? sanitize_key(wp_unslash($_GET['source'])) : '';
        return $source === 'ivyforms';
    }

    /**
     * Expose IvyForms form context on the frontend table description JSON.
     *
     * @param object      $obj
     * @param int         $tableWpId
     * @param WPDataTable $table
     * @return object
     */
    public static function filterTableDescription($obj, $tableWpId, $table)
    {
        if (!is_object($obj) || !is_object($table) || $table->getTableType() !== 'ivyforms') {
            return $obj;
        }

        try {
            $tableData = WDTConfigController::loadTableFromDB($tableWpId);
        } catch (Exception $e) {
            return $obj;
        }

        $content = json_decode($tableData->content ?? '{}');
        if (!is_object($content) || empty($content->formId)) {
            return $obj;
        }

        $obj->ivyformsFormId = (int) $content->formId;
        $obj->ivyformsFormIdColumnIndex = -1;

        $columns = WDTConfigController::loadColumnsFromDB($tableWpId);
        foreach ($columns as $column) {
            if (self::mapOrigHeaderToFieldKey($column->orig_header, $content) === 'formId') {
                $obj->ivyformsFormIdColumnIndex = $table->getColumnHeaderOffset($column->orig_header);
                break;
            }
        }

        return $obj;
    }

    /**
     * Enqueue assets for table creation wizard / edit page
     *
     * @return void
     */
    public static function enqueueAssets() {
        wp_enqueue_script(
            'wdt-ivyforms-table-config',
            plugin_dir_url(__FILE__) . 'assets/js/ivyforms_table_config_object.js',
            ['jquery', 'wdt-common'],
            WDT_CURRENT_VERSION,
            true
        );
        wp_localize_script('wdt-ivyforms-table-config', 'wdtIvyFormsEditing', self::getEditingScriptData());

        wp_enqueue_script(
            'wdt-ivyforms-table-creation',
            plugin_dir_url(__FILE__) . 'assets/js/table_creation_wizard.js',
            ['jquery', 'wdt-common', 'wdt-ivyforms-table-config'],
            WDT_CURRENT_VERSION,
            true
        );

        // Editable preview on the constructor/edit page uses the same editor adapters as frontend.
        wp_enqueue_script(
            'wdt-ivyforms-editing',
            plugin_dir_url(__FILE__) . 'assets/js/ivyforms_editing.js',
            ['jquery', 'wdt-wpdatatables'],
            WDT_CURRENT_VERSION,
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
            if (is_wp_error($fields)) {
                wp_send_json_error(esc_html__('Could not load form fields.', 'wpdatatables'));
            }

            $field_columns = [];
            $field_ids = [];
            $usedOrigHeaders = [];
            $compoundMaps = is_array($fields)
                ? self::buildCompoundFieldMaps($fields)
                : ['parentIds' => [], 'childToParent' => [], 'parentByIndex' => []];

            $parentLabels = [];
            if (is_array($fields)) {
                foreach ($fields as $field) {
                    if (isset($compoundMaps['parentIds'][(int) $field->getId()])) {
                        $parentLabels[(int) $field->getId()] = $field->getFieldGeneralSettings()->getLabel();
                    }
                }
            }

            $childIdsByParent = [];
            foreach ($compoundMaps['childToParent'] as $childId => $parentId) {
                if (!isset($childIdsByParent[$parentId])) {
                    $childIdsByParent[$parentId] = [];
                }
                $childIdsByParent[$parentId][] = $childId;
            }

            foreach ($fields as $field) {
                $field_id = $field->getId();
                $fieldType = $field->getType();
                $origHeader = self::generateIvyFormsOrigHeader($field_id, $usedOrigHeaders);
                $usedOrigHeaders[] = $origHeader;

                $isCompoundParent = isset($compoundMaps['parentIds'][(int) $field_id]);
                $compoundParentId = $compoundMaps['childToParent'][(int) $field_id] ?? null;
                $isCompoundChild = $compoundParentId !== null;

                $label = $field->getFieldGeneralSettings()->getLabel();
                if ($isCompoundChild && isset($parentLabels[$compoundParentId])) {
                    $label = $parentLabels[$compoundParentId] . ' — ' . $label;
                }

                $columnMeta = [
                    'id' => $field_id,
                    'label' => $label,
                    'type' => $fieldType,
                    'origHeader' => $origHeader,
                    'isCompoundParent' => $isCompoundParent,
                    'isCompoundChild' => $isCompoundChild,
                ];

                if ($isCompoundParent) {
                    $columnMeta['compoundChildIds'] = $childIdsByParent[(int) $field_id] ?? [];
                }
                if ($isCompoundChild) {
                    $columnMeta['compoundParentId'] = $compoundParentId;
                }

                $field_columns[] = array_merge(
                    $columnMeta,
                    self::getEditorDefaultsForIvyFormsFieldType(
                        $fieldType,
                        $field_id,
                        $isCompoundChild
                    )
                );
                $field_ids[] = $field_id;
            }

            $entry_columns = EntryManager::getAllEntryColumns();
            $entry_data = [];
            foreach ($entry_columns as $key => $label) {
                if (!in_array($key, $field_ids, true)) {
                    $origHeader = self::generateIvyFormsOrigHeader($key, $usedOrigHeaders);
                    $usedOrigHeaders[] = $origHeader;
                    $entry_data[] = array_merge(
                        [
                            'id' => $key,
                            'label' => $label,
                            'type' => 'entry_meta',
                            'origHeader' => $origHeader,
                        ],
                        self::getEditorDefaultsForIvyFormsEntryMeta($key, false)
                    );
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

            // D2: Editable requires IvyForms Pro.
            if (!empty($table->editable) && !self::canEditEntriesFromWpDataTables()) {
                $table->editable = 0;
                $table->inline_editing = 0;
            }

            // Prevent setEditable() MySQL-name guess from persisting JSON content fragments.
            $table->mysql_table_name = '';

            $fieldIds = $ivyFormsData->fields;
            if (!empty($table->editable) && !in_array('id', $fieldIds, false)) {
                $fieldIds[] = 'id';
            }

            $selectedFieldIds = $fieldIds;

            $fieldIds = self::expandFieldIdsWithCompoundChildren(
                (int) $ivyFormsData->formId,
                $fieldIds,
                !empty($table->editable)
            );

            $table->content = json_encode(
                array(
                    'formId' => $ivyFormsData->formId,
                    'fieldIds' => $fieldIds,
                    'selectedFieldIds' => $selectedFieldIds,
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

        // Capability flag for core customBasedConstruct SSP/edit gating (not the per-table server_side toggle).
        $sanitized->hasServerSideIntegration = 1;

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
        self::$wdtParameters = $params;
        if ($wpDataTable->getWpId()) {
            $table = WDTConfigController::loadTableFromDB($wpDataTable->getWpId());
            $ivyFormsData = isset($table->advanced_settings) ? json_decode($table->advanced_settings)->ivyforms : null;
        } else {
            $ivyFormsData = null;
        }
        if (empty($params['columnTitles'])) {
            $params['columnTitles'] = self::getColumnHeaders($content->formId, $content->fieldIds);
            self::$wdtParameters = $params;
        }

        if ($wpDataTable->isAjaxReturn()) {
            self::ajaxReturnConstruct($content, $ivyFormsData, $wpDataTable);
        }

        $serverSide = $wpDataTable->serverSide();
        $formArray = self::generateFormArray($content, $ivyFormsData, $serverSide);
        self::$relaxSafecssForIvyHtml = true;
        try {
            $wpDataTable->arrayBasedConstruct($formArray, $params);
        } finally {
            self::$relaxSafecssForIvyHtml = false;
        }
    }

    /**
     * Build DataTables SSP JSON for IvyForms tables and exit.
     *
     * @param object           $content
     * @param object|null      $ivyFormsData
     * @param WPDataTable      $wpDataTable
     * @return void
     */
    public static function ajaxReturnConstruct($content, $ivyFormsData, $wpDataTable)
    {
        $formId = isset($content->formId) ? (int) $content->formId : 0;

        $baseCriteria = self::prepareSearchCriteria($ivyFormsData, $content, false);
        $filteredCriteria = self::prepareSearchCriteria($ivyFormsData, $content, true);

        $recordsTotal = self::countFormEntriesFromAPI($formId, $baseCriteria);
        $recordsFiltered = self::countFormEntriesFromAPI($formId, $filteredCriteria);

        $entriesArray = self::generateFormArray($content, $ivyFormsData, true);

        $output = [
            'draw' => isset($_POST['draw']) ? (int) $_POST['draw'] : 0,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => [],
        ];

        $colObjs = $wpDataTable->prepareColumns(self::$wdtParameters);
        $output['data'] = $wpDataTable->prepareOutputData($entriesArray, self::$wdtParameters, $colObjs);
        $output['data'] = apply_filters(
            'wpdatatables_custom_prepare_output_data',
            $output['data'],
            $wpDataTable,
            $entriesArray,
            self::$wdtParameters,
            $colObjs
        );

        $json = json_encode($output);
        $json = apply_filters('wpdatatables_filter_server_side_data', $json, $wpDataTable->getWpId(), $_GET);

        echo $json;
        exit();
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
        if (method_exists(IvyFormsAPI::class, 'searchFormEntries')) {
            $result = IvyFormsAPI::searchFormEntries($formId, $criteria);
            if (is_wp_error($result)) {
                return [];
            }
            return $result['data'] ?? [];
        }

        $entries = IvyFormsAPI::getFormEntries($formId, $criteria);
        return is_wp_error($entries) ? [] : $entries;
    }

    /**
     * Count form entries via IvyFormsAPI (prefers countFormEntries).
     *
     * @param int   $formId
     * @param array $criteria
     * @return int
     */
    public static function countFormEntriesFromAPI(int $formId, array $criteria = []): int
    {
        if (method_exists(IvyFormsAPI::class, 'countFormEntries')) {
            $count = IvyFormsAPI::countFormEntries($formId, $criteria);
            return is_wp_error($count) ? 0 : (int) $count;
        }

        if (method_exists(IvyFormsAPI::class, 'searchFormEntries')) {
            $result = IvyFormsAPI::searchFormEntries($formId, array_merge($criteria, ['page' => 1, 'perPage' => 1]));
            if (is_wp_error($result)) {
                return 0;
            }
            return (int) ($result['meta']['total'] ?? 0);
        }

        // Older IvyForms APIs expose only getFormEntries(), without count/meta.
        // Fetch the filtered result set so DataTables receives an accurate total.
        $countCriteria = array_merge($criteria, ['page' => 1, 'perPage' => 'all']);

        return count(self::getFormEntriesFromAPI($formId, $countCriteria));
    }

    /**
     * Generate form array for wpDataTables
     *
     * @param object      $content
     * @param object|null $ivyFormsData
     * @param bool        $serverSide When true, apply DataTables paging/sort/search; otherwise load all rows.
     * @return array
     */
    public static function generateFormArray($content, $ivyFormsData, bool $serverSide = false): array
    {
        $tableArray = [];
        $origHeaders = [];

        if (!$serverSide) {
            if ($ivyFormsData === null) {
                $ivyFormsData = new stdClass();
            }
            if (!isset($ivyFormsData->perPage)) {
                $ivyFormsData->perPage = 'all';
            }
        }

        $searchCriteria = self::prepareSearchCriteria($ivyFormsData, $content, $serverSide);
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
                $origHeader = self::generateIvyFormsOrigHeader($fieldId, $usedOrigHeaders);
                $usedOrigHeaders[] = $origHeader;
                $origHeaders[$fieldId] = $origHeader;
            }
        }

        $entryColumns = EntryManager::getAllEntryColumns();
        foreach ($entryColumns as $key => $label) {
            if (in_array($key, $content->fieldIds)) {
                $origHeader = self::generateIvyFormsOrigHeader($key, $usedOrigHeaders);
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
     * Prepare search criteria for API queries.
     *
     * @param object|null $ivyFormsData Table-level IvyForms filters from advanced_settings.
     * @param object|null $content      Table content (formId, fieldIds) — needed for SSP sort mapping.
     * @param bool        $applyDataTablesRequest When true, merge DataTables POST paging/sort/search.
     * @return array
     */
    public static function prepareSearchCriteria($ivyFormsData, $content = null, bool $applyDataTablesRequest = false): array
    {
        $criteria = [];
        $filters = [];

        if ($ivyFormsData !== null) {
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
            // Client-side / first-paint override
            if (isset($ivyFormsData->perPage)) {
                $criteria['perPage'] = $ivyFormsData->perPage;
            }
        }

        $criteria['filters'] = $filters;

        if ($applyDataTablesRequest) {
            $criteria = self::applyDataTablesRequestToCriteria($criteria, $content);
        } elseif (!isset($criteria['perPage'])) {
            $criteria['perPage'] = 'all';
            $criteria['page'] = 1;
        }

        return $criteria;
    }

    /**
     * Map DataTables SSP POST params onto IvyForms search criteria.
     *
     * @param array       $criteria
     * @param object|null $content
     * @return array
     */
    private static function applyDataTablesRequestToCriteria(array $criteria, $content = null): array
    {
        $start = isset($_POST['start']) ? max(0, (int) $_POST['start']) : 0;
        $length = isset($_POST['length']) ? (int) $_POST['length'] : 10;
        $maxPerPage = (int) apply_filters('wpdatatables_ivyforms_ssp_max_per_page', 1000);

        if ($length <= 0) {
            $length = 10;
        }

        $perPage = min($length, max(1, $maxPerPage));
        $criteria['perPage'] = $perPage;
        $criteria['page'] = (int) floor($start / $perPage) + 1;

        if (!empty($_POST['search']['value'])) {
            $criteria['search'] = sanitize_text_field(wp_unslash($_POST['search']['value']));
        }

        $criteria = self::applyColumnFiltersToCriteria($criteria, $content);

        $sortMapping = self::resolveSortFromDataTablesRequest($content);
        if ($sortMapping !== null) {
            if (!empty($sortMapping['orderByFieldId'])) {
                $criteria['orderByFieldId'] = (int) $sortMapping['orderByFieldId'];
                unset($criteria['orderBy']);
            } elseif (!empty($sortMapping['orderBy'])) {
                $criteria['orderBy'] = $sortMapping['orderBy'];
                unset($criteria['orderByFieldId']);
            }
            $criteria['order'] = $sortMapping['order'] ?? 'desc';
        } else {
            $criteria['orderBy'] = 'id';
            $criteria['order'] = 'desc';
        }

        return $criteria;
    }

    /**
     * Ordered orig_header list matching DataTables column indexes (uses columnOrder when present).
     *
     * @return string[]
     */
    private static function getOrderedOrigHeaders(): array
    {
        if (!empty(self::$wdtParameters['columnOrder']) && is_array(self::$wdtParameters['columnOrder'])) {
            $ordered = self::$wdtParameters['columnOrder'];
            ksort($ordered);

            return array_values($ordered);
        }

        if (!empty(self::$wdtParameters['columnTitles']) && is_array(self::$wdtParameters['columnTitles'])) {
            return array_keys(self::$wdtParameters['columnTitles']);
        }

        return [];
    }

    /**
     * Map DataTables per-column filters onto IvyForms search criteria.
     *
     * @param array       $criteria
     * @param object|null $content
     * @return array
     */
    private static function applyColumnFiltersToCriteria(array $criteria, $content = null): array
    {
        $origHeaders = self::getOrderedOrigHeaders();
        if ($origHeaders === []) {
            return $criteria;
        }

        if (!isset($criteria['filters']) || !is_array($criteria['filters'])) {
            $criteria['filters'] = [];
        }
        if (!isset($criteria['fieldFilters']) || !is_array($criteria['fieldFilters'])) {
            $criteria['fieldFilters'] = [];
        }

        $filterTypes = self::$wdtParameters['filterTypes'] ?? [];
        $exactFiltering = self::$wdtParameters['exactFiltering'] ?? [];
        $filterDefaultValues = self::$wdtParameters['filterDefaultValue'] ?? [];
        $dataTypes = self::$wdtParameters['data_types'] ?? [];

        $columnCount = count($origHeaders);
        for ($i = 0; $i < $columnCount; $i++) {
            $columnSearchFromTable = false;
            $columnSearchFromDefaultValue = false;

            if (isset($_POST['columns'][$i]['search'])
                && is_array($_POST['columns'][$i]['search'])
                && isset($_POST['columns'][$i]['search']['value'])
                && $_POST['columns'][$i]['search']['value'] !== ''
                && $_POST['columns'][$i]['search']['value'] !== '|') {
                $columnSearchFromTable = true;
            }

            if ((isset($_POST['draw']) && (int) $_POST['draw'] === 1 || $columnSearchFromTable)
                && isset($filterDefaultValues[$i])
                && $filterDefaultValues[$i] !== ''
                && $filterDefaultValues[$i] !== '|') {
                $columnSearchFromDefaultValue = true;
            }

            if (!isset($_POST['columns'][$i]['searchable'])
                || $_POST['columns'][$i]['searchable'] != true
                || (!$columnSearchFromTable && !$columnSearchFromDefaultValue)) {
                continue;
            }

            $origHeader = $origHeaders[$i];
            $columnSearch = $columnSearchFromTable
                ? sanitize_text_field(wp_unslash($_POST['columns'][$i]['search']['value']))
                : sanitize_text_field((string) $filterDefaultValues[$i]);

            $fieldKey = self::mapOrigHeaderToFieldKey($origHeader, $content);
            if ($fieldKey === null) {
                continue;
            }

            $filterType = $filterTypes[$origHeader] ?? 'text';

            if ($fieldKey === 'dateCreated') {
                self::applyDateCreatedColumnFilter($criteria, $columnSearch, $filterType);
                continue;
            }

            if (in_array($fieldKey, self::$filterableEntryMetaColumns, true)) {
                self::applyEntryMetaColumnFilter($criteria, $fieldKey, $columnSearch, $filterType, $exactFiltering[$origHeader] ?? null);
                continue;
            }

            if (!ctype_digit((string) $fieldKey)) {
                continue;
            }

            $fieldId = (int) $fieldKey;
            $isNumeric = in_array($dataTypes[$origHeader] ?? 'string', ['int', 'float'], true);

            switch ($filterType) {
                case 'number-range':
                    list($left, $right) = array_pad(explode('|', $columnSearch, 2), 2, '');
                    if ($left !== '') {
                        $criteria['fieldFilters'][] = [
                            'fieldId' => $fieldId,
                            'operator' => '>=',
                            'value' => $left,
                            'isNumeric' => true,
                        ];
                    }
                    if ($right !== '') {
                        $criteria['fieldFilters'][] = [
                            'fieldId' => $fieldId,
                            'operator' => '<=',
                            'value' => $right,
                            'isNumeric' => true,
                        ];
                    }
                    break;

                case 'date-range':
                case 'datetime-range':
                    list($left, $right) = array_pad(explode('|', $columnSearch, 2), 2, '');
                    if ($left !== '') {
                        $criteria['fieldFilters'][] = [
                            'fieldId' => $fieldId,
                            'operator' => '>=',
                            'value' => self::formatColumnFilterDateTime($left, $filterType === 'datetime-range'),
                        ];
                    }
                    if ($right !== '') {
                        $criteria['fieldFilters'][] = [
                            'fieldId' => $fieldId,
                            'operator' => '<=',
                            'value' => self::formatColumnFilterDateTime($right, $filterType === 'datetime-range', true),
                        ];
                    }
                    break;

                case 'time-range':
                    list($left, $right) = array_pad(explode('|', $columnSearch, 2), 2, '');
                    if ($left !== '') {
                        $criteria['fieldFilters'][] = [
                            'fieldId' => $fieldId,
                            'operator' => '>=',
                            'value' => self::formatColumnFilterTime($left),
                        ];
                    }
                    if ($right !== '') {
                        $criteria['fieldFilters'][] = [
                            'fieldId' => $fieldId,
                            'operator' => '<=',
                            'value' => self::formatColumnFilterTime($right),
                        ];
                    }
                    break;

                case 'checkbox':
                case 'multiselect':
                    $values = self::parseMultiSelectColumnSearch($columnSearch, !empty($exactFiltering[$origHeader]));
                    if ($values !== []) {
                        $criteria['fieldFilters'][] = [
                            'fieldId' => $fieldId,
                            'operator' => 'in',
                            'value' => $values,
                        ];
                    }
                    break;

                case 'select':
                case 'number':
                    if (!empty($exactFiltering[$origHeader]) || $filterType === 'number') {
                        foreach (preg_split('/\s+/', $columnSearch) as $value) {
                            if ($value === '') {
                                continue;
                            }
                            $criteria['fieldFilters'][] = [
                                'fieldId' => $fieldId,
                                'operator' => '=',
                                'value' => $value,
                                'isNumeric' => $filterType === 'number' || $isNumeric,
                            ];
                        }
                    } else {
                        foreach (preg_split('/\s+/', $columnSearch) as $value) {
                            if ($value === '') {
                                continue;
                            }
                            $criteria['fieldFilters'][] = [
                                'fieldId' => $fieldId,
                                'operator' => 'contains',
                                'value' => $value,
                            ];
                        }
                    }
                    break;

                case 'text':
                default:
                    if (!empty($exactFiltering[$origHeader])) {
                        $criteria['fieldFilters'][] = [
                            'fieldId' => $fieldId,
                            'operator' => '=',
                            'value' => $columnSearch,
                        ];
                    } else {
                        foreach (preg_split('/\s+/', $columnSearch) as $value) {
                            if ($value === '') {
                                continue;
                            }
                            $criteria['fieldFilters'][] = [
                                'fieldId' => $fieldId,
                                'operator' => 'contains',
                                'value' => $value,
                            ];
                        }
                    }
                    break;
            }
        }

        return $criteria;
    }

    /**
     * Apply a column filter for entry dateCreated (entry table column).
     *
     * @param array  $criteria
     * @param string $columnSearch
     * @param string $filterType
     * @return void
     */
    private static function applyDateCreatedColumnFilter(array &$criteria, string $columnSearch, string $filterType): void
    {
        list($left, $right) = array_pad(explode('|', $columnSearch, 2), 2, '');

        if ($left !== '') {
            $criteria['filters']['dateCreatedMin'] = self::formatColumnFilterDateTime(
                $left,
                in_array($filterType, ['datetime', 'datetime-range'], true)
            );
        }
        if ($right !== '') {
            $criteria['filters']['dateCreatedMax'] = self::formatColumnFilterDateTime(
                $right,
                in_array($filterType, ['datetime', 'datetime-range'], true),
                true
            );
        }
    }

    /**
     * Apply a column filter for IvyForms entry metadata (status, userId, starred, id).
     *
     * @param array       $criteria
     * @param string      $fieldKey
     * @param string      $columnSearch
     * @param string      $filterType
     * @param int|null    $exactFiltering
     * @return void
     */
    private static function applyEntryMetaColumnFilter(
        array &$criteria,
        string $fieldKey,
        string $columnSearch,
        string $filterType,
        $exactFiltering = null
    ): void {
        if ($fieldKey === 'starred') {
            $values = self::parseMultiSelectColumnSearch($columnSearch, false);
            if ($values === []) {
                $values = [$columnSearch];
            }
            // Use the first selected value when multiple are not expected.
            $selected = $values[0];
            if (strcasecmp($selected, 'Yes') === 0) {
                $criteria['filters']['starred'] = '1';
            } elseif (strcasecmp($selected, 'No') === 0) {
                $criteria['filters']['starred'] = '0';
            }
            return;
        }

        if ($fieldKey === 'status') {
            $values = self::parseMultiSelectColumnSearch($columnSearch, false);
            if ($values !== []) {
                $criteria['filters']['status'] = $values[0];
            } else {
                $criteria['filters']['status'] = $columnSearch;
            }
            return;
        }

        if ($filterType === 'number' || $fieldKey === 'id' || $fieldKey === 'userId') {
            $criteria['filters'][$fieldKey] = $columnSearch;
            return;
        }

        if (!empty($exactFiltering)) {
            $criteria['filters'][$fieldKey] = $columnSearch;
            return;
        }

        // Entry meta text columns without exact filtering — fall back to global-style contains via search.
        $existing = $criteria['search'] ?? '';
        $criteria['search'] = trim($existing === '' ? $columnSearch : $existing . ' ' . $columnSearch);
    }

    /**
     * Parse multiselect / checkbox filter values from DataTables column search string.
     *
     * @param string $columnSearch
     * @param bool   $exactFiltering
     * @return array<int, string>
     */
    private static function parseMultiSelectColumnSearch(string $columnSearch, bool $exactFiltering): array
    {
        if ($exactFiltering && strpos($columnSearch, '$') !== false) {
            $values = explode('$|^', $columnSearch);
            $values[0] = ltrim($values[0], '^');
            $lastIndex = count($values) - 1;
            if ($lastIndex >= 0) {
                $values[$lastIndex] = rtrim($values[$lastIndex], '$');
            }
        } elseif (strpos($columnSearch, '||') !== false) {
            $values = preg_split('/(?<!\|)\|(?!\|)/', $columnSearch);
        } else {
            $values = explode('|', $columnSearch);
        }

        return array_values(array_filter(array_map('trim', $values), static function ($value) {
            return $value !== '';
        }));
    }

    /**
     * Format a WDT column-filter date/datetime for IvyForms SQL comparison.
     *
     * @param string $value
     * @param bool   $includeTime
     * @param bool   $endOfDay When true and no time component, use 23:59:59.
     * @return string
     */
    private static function formatColumnFilterDateTime(string $value, bool $includeTime = false, bool $endOfDay = false): string
    {
        $dateFormat = get_option('wdtDateFormat');
        $timeFormat = get_option('wdtTimeFormat');

        if ($includeTime && class_exists('DateTime')) {
            $dateTime = DateTime::createFromFormat($dateFormat . ' ' . $timeFormat, $value);
            if ($dateTime instanceof DateTime) {
                return $dateTime->format('Y-m-d H:i:s');
            }
        }

        $normalized = self::normalizeDate($value);
        if ($includeTime) {
            return $endOfDay ? $normalized . ' 23:59:59' : $normalized . ' 00:00:00';
        }

        return $endOfDay ? $normalized . ' 23:59:59' : $normalized . ' 00:00:00';
    }

    /**
     * Format a WDT time-range filter value for IvyForms field comparison.
     *
     * @param string $value
     * @return string
     */
    private static function formatColumnFilterTime(string $value): string
    {
        if (class_exists('DateTime')) {
            $timeFormat = get_option('wdtTimeFormat');
            $dateTime = DateTime::createFromFormat($timeFormat, $value);
            if ($dateTime instanceof DateTime) {
                return $dateTime->format('H:i:s');
            }
        }

        return $value;
    }

    /**
     * Resolve IvyForms sort params from DataTables order (entry meta or field value).
     *
     * @param object|null $content
     * @return array{orderBy?: string, orderByFieldId?: int, order: string}|null
     */
    private static function resolveSortFromDataTablesRequest($content = null): ?array
    {
        if (!isset($_POST['order'][0]['column'])) {
            return null;
        }

        $origHeaders = self::getOrderedOrigHeaders();
        $columnIndex = (int) $_POST['order'][0]['column'];

        if (!isset($origHeaders[$columnIndex])) {
            return null;
        }

        $dir = 'asc';
        if (isset($_POST['order'][0]['dir']) && strtolower((string) $_POST['order'][0]['dir']) === 'desc') {
            $dir = 'desc';
        }

        $origHeader = $origHeaders[$columnIndex];
        $fieldKey = self::mapOrigHeaderToFieldKey($origHeader, $content);

        if ($fieldKey !== null && in_array($fieldKey, self::$sortableEntryColumns, true)) {
            return [
                'orderBy' => $fieldKey,
                'order' => $dir,
            ];
        }

        if ($fieldKey !== null && ctype_digit((string) $fieldKey)) {
            return [
                'orderByFieldId' => (int) $fieldKey,
                'order' => $dir,
            ];
        }

        return [
            'orderBy' => 'id',
            'order' => $dir,
        ];
    }

    /**
     * Resolve IvyForms orderBy from DataTables order column (entry meta only in v1).
     *
     * @param object|null $content
     * @return string|null
     * @deprecated Use resolveSortFromDataTablesRequest().
     */
    private static function resolveOrderByFromDataTablesRequest($content = null): ?string
    {
        $sort = self::resolveSortFromDataTablesRequest($content);

        return $sort['orderBy'] ?? null;
    }

    /**
     * Cached orig_header → fieldId maps keyed by form ID.
     *
     * @var array<int, array<string, string>>
     */
    private static $origHeaderToFieldKeyCache = [];

    /**
     * Cached form entries keyed by form ID (request-scoped).
     *
     * @var array<int, array>
     */
    private static $formEntriesCache = [];

    /**
     * Cached distinct filter values keyed by form ID + property/field ID.
     *
     * @var array<string, array<int, string>>
     */
    private static $distinctValuesCache = [];

    /**
     * Upper bound for distinct-value lists used by column filter dropdowns.
     */
    private const DISTINCT_VALUES_MAX = 500;

    /**
     * Map a column orig_header back to an IvyForms fieldId / entry meta key.
     * Mirrors generateFormArray / getColumnHeaders naming order.
     *
     * @param string      $origHeader
     * @param object|null $content
     * @return string|null
     */
    private static function mapOrigHeaderToFieldKey(string $origHeader, $content = null): ?string
    {
        if ($content === null || empty($content->formId) || empty($content->fieldIds) || !is_array($content->fieldIds)) {
            return null;
        }

        $formId = (int) $content->formId;
        if (!isset(self::$origHeaderToFieldKeyCache[$formId])) {
            self::$origHeaderToFieldKeyCache[$formId] = self::buildOrigHeaderToFieldKeyMap($content);
        }

        return self::$origHeaderToFieldKeyCache[$formId][$origHeader] ?? null;
    }

    /**
     * Generate a stable, readable orig_header for an IvyForms field or entry key.
     *
     * @param int|string $fieldKey
     * @param array      $usedOrigHeaders
     * @return string
     */
    private static function generateIvyFormsOrigHeader($fieldKey, array $usedOrigHeaders): string
    {
        $origHeader = ctype_digit((string) $fieldKey)
            ? 'ivyf_' . (int) $fieldKey
            : 'ivy_' . sanitize_key((string) $fieldKey);

        if (!in_array($origHeader, $usedOrigHeaders, true)) {
            return $origHeader;
        }

        $index = 1;
        while (in_array($origHeader . $index, $usedOrigHeaders, true)) {
            $index++;
        }

        return $origHeader . $index;
    }

    /**
     * Build the full orig_header → field key map for a form (once per request).
     *
     * @param object $content
     * @return array<string, string>
     */
    private static function buildOrigHeaderToFieldKeyMap($content): array
    {
        $map = [];
        $fieldIds = $content->fieldIds;
        $usedOrigHeaders = [];
        $legacyUsedOrigHeaders = [];

        $fields = IvyFormsAPI::getFields((int) $content->formId);
        if (!is_wp_error($fields) && is_array($fields)) {
            foreach ($fields as $field) {
                $fieldId = $field->getId();
                if (in_array($fieldId, $fieldIds, false)) {
                    $candidate = self::generateIvyFormsOrigHeader($fieldId, $usedOrigHeaders);
                    $usedOrigHeaders[] = $candidate;
                    $map[$candidate] = (string) $fieldId;

                    $legacyCandidate = WDTTools::generateMySQLColumnName($fieldId, $legacyUsedOrigHeaders);
                    $legacyUsedOrigHeaders[] = $legacyCandidate;
                    $map[$legacyCandidate] = (string) $fieldId;
                }
            }
        }

        $entryColumns = EntryManager::getAllEntryColumns();
        foreach ($entryColumns as $key => $label) {
            if (in_array($key, $fieldIds, false)) {
                $candidate = self::generateIvyFormsOrigHeader($key, $usedOrigHeaders);
                $usedOrigHeaders[] = $candidate;
                $map[$candidate] = (string) $key;

                $legacyCandidate = WDTTools::generateMySQLColumnName($key, $legacyUsedOrigHeaders);
                $legacyUsedOrigHeaders[] = $legacyCandidate;
                $map[$legacyCandidate] = (string) $key;
            }
        }

        return $map;
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
            'filterByRead' => $ivyFormsData->filterByRead,
            'hasServerSideIntegration' => 1,
        );

        $tableArray['advanced_settings'] = json_encode($advancedSettings);

        // Never store a MySQL table name for ivyforms — setEditable() can poison it by
        // slicing JSON content at the "from" inside "formId".
        $tableArray['mysql_table_name'] = '';

        // D2: Editable requires IvyForms Pro — refuse to persist editable without it.
        if (!empty($tableArray['editable']) && !self::canEditEntriesFromWpDataTables()) {
            $tableArray['editable'] = 0;
            $tableArray['inline_editing'] = 0;
        }

        // When Editable is on, ensure Entry ID is present in content fieldIds.
        if (!empty($tableArray['editable'])) {
            $content = json_decode($tableArray['content'] ?? '{}');
            if (is_object($content) && isset($content->fieldIds) && is_array($content->fieldIds)) {
                if (!in_array('id', $content->fieldIds, false)) {
                    $content->fieldIds[] = 'id';
                }
                if (!empty($content->formId)) {
                    if (!isset($content->selectedFieldIds) || !is_array($content->selectedFieldIds)) {
                        $content->selectedFieldIds = $content->fieldIds;
                    }
                    $content->fieldIds = self::expandFieldIdsWithCompoundChildren(
                        (int) $content->formId,
                        $content->fieldIds,
                        true
                    );
                }
                $tableArray['content'] = json_encode($content);
            }
        }

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
     * IvyForms field types that must never be editable in the WDT modal (v1).
     *
     * @var string[]
     */
    private static $nonEditableIvyFormsFieldTypes = [
        'file-upload',
        'signature',
        'recaptcha',
        'turnstile',
        'hcaptcha',
        'html',
        'section',
        'page',
        'product',
        'quantity',
        'total',
    ];

    /**
     * Cached compound-field maps per form ID for the current request.
     *
     * @var array<int, array{parentIds: array<int, string>, childToParent: array<int, int>, parentByIndex: array<int, int>}>
     */
    private static $compoundFieldMapsCache = [];

    /**
     * Build maps for IvyForms name/address compound fields (parent + subfields).
     *
     * @param array $fields Field entities from IvyFormsAPI::getFields().
     * @return array{parentIds: array<int, string>, childToParent: array<int, int>, parentByIndex: array<int, int>}
     */
    private static function buildCompoundFieldMaps(array $fields): array
    {
        $parentIds = [];
        $parentByIndex = [];

        foreach ($fields as $field) {
            if (!is_object($field) || !method_exists($field, 'getType') || !method_exists($field, 'getIndex')) {
                continue;
            }
            $type = strtolower(trim($field->getType()));
            if (in_array($type, ['name', 'address'], true)) {
                $id = (int) $field->getId();
                $parentIds[$id] = $type;
                $parentByIndex[(int) $field->getIndex()] = $id;
            }
        }

        $childToParent = [];
        foreach ($fields as $field) {
            if (!is_object($field) || !method_exists($field, 'getId') || !method_exists($field, 'getType')) {
                continue;
            }
            $childId = (int) $field->getId();
            $type = strtolower(trim($field->getType()));

            $parentId = method_exists($field, 'getParentId') ? $field->getParentId() : null;
            if ($parentId && isset($parentIds[(int) $parentId])) {
                $childToParent[$childId] = (int) $parentId;
                continue;
            }

            // Subfields share fieldIndex with the compound parent when parentId was lost (nested sections).
            if (!in_array($type, ['name', 'address'], true)) {
                $fieldIndex = method_exists($field, 'getIndex') ? (int) $field->getIndex() : null;
                if ($fieldIndex !== null && isset($parentByIndex[$fieldIndex])) {
                    $childToParent[$childId] = $parentByIndex[$fieldIndex];
                }
            }
        }

        return [
            'parentIds' => $parentIds,
            'childToParent' => $childToParent,
            'parentByIndex' => $parentByIndex,
        ];
    }

    /**
     * @param int $formId
     * @return array{parentIds: array<int, string>, childToParent: array<int, int>, parentByIndex: array<int, int>}
     */
    private static function getCompoundFieldMapsForForm(int $formId): array
    {
        if (isset(self::$compoundFieldMapsCache[$formId])) {
            return self::$compoundFieldMapsCache[$formId];
        }

        $fields = IvyFormsAPI::getFields($formId);
        if (is_wp_error($fields) || !is_array($fields)) {
            self::$compoundFieldMapsCache[$formId] = [
                'parentIds' => [],
                'childToParent' => [],
                'parentByIndex' => [],
            ];
            return self::$compoundFieldMapsCache[$formId];
        }

        self::$compoundFieldMapsCache[$formId] = self::buildCompoundFieldMaps($fields);
        return self::$compoundFieldMapsCache[$formId];
    }

    /**
     * Whether a field ID list contains a given numeric field ID (string or int tolerant).
     *
     * @param int   $fieldId
     * @param array $fieldIds
     * @return bool
     */
    private static function fieldIdInList(int $fieldId, array $fieldIds): bool
    {
        foreach ($fieldIds as $id) {
            if ((int) $id === $fieldId) {
                return true;
            }
        }
        return false;
    }

    /**
     * When Editable is on and a compound parent (name/address) is selected, auto-include its subfields.
     *
     * @param int   $formId
     * @param array $fieldIds
     * @param bool  $editable
     * @return array
     */
    private static function expandFieldIdsWithCompoundChildren(int $formId, array $fieldIds, bool $editable): array
    {
        if (!$editable || $formId <= 0) {
            return $fieldIds;
        }

        $maps = self::getCompoundFieldMapsForForm($formId);
        if (empty($maps['parentIds'])) {
            return $fieldIds;
        }

        $expanded = $fieldIds;
        foreach ($fieldIds as $fieldId) {
            if (!is_numeric($fieldId)) {
                continue;
            }
            $parentId = (int) $fieldId;
            if (!isset($maps['parentIds'][$parentId])) {
                continue;
            }
            foreach ($maps['childToParent'] as $childId => $childParentId) {
                if ($childParentId === $parentId && !self::fieldIdInList($childId, $expanded)) {
                    $expanded[] = $childId;
                }
            }
        }

        return $expanded;
    }

    /**
     * Apply IvyForms field-type → WDT editor defaults when columns are persisted.
     *
     * @param array $columnConfig
     * @param int   $tableId
     * @return array
     */
    public static function applyColumnEditorDefaults(array $columnConfig, $tableId)
    {
        $tableId = (int) $tableId;
        if (!$tableId || empty($columnConfig['orig_header'])) {
            return $columnConfig;
        }

        try {
            $tableData = WDTConfigController::loadTableFromDB($tableId);
        } catch (Exception $e) {
            return $columnConfig;
        }

        if (!is_object($tableData) || ($tableData->table_type ?? '') !== 'ivyforms') {
            return $columnConfig;
        }

        $content = json_decode($tableData->content ?? '{}');
        if (!is_object($content) || empty($content->formId)) {
            return $columnConfig;
        }

        $origHeader = (string) $columnConfig['orig_header'];
        $fieldKey = self::mapOrigHeaderToFieldKey($origHeader, $content);
        if ($fieldKey === null) {
            return $columnConfig;
        }

        $editable = !empty($tableData->editable);
        $isNewColumn = true;
        foreach (WDTConfigController::loadColumnsFromDB($tableId) as $existingColumn) {
            if ((string) $existingColumn->orig_header === $origHeader) {
                $isNewColumn = false;
                break;
            }
        }
        $isCompoundParent = false;
        $isCompoundChild = false;
        $formId = (int) $content->formId;
        $fieldIds = is_array($content->fieldIds ?? null) ? $content->fieldIds : [];
        $selectedFieldIds = is_array($content->selectedFieldIds ?? null) ? $content->selectedFieldIds : $fieldIds;
        $compoundMaps = self::getCompoundFieldMapsForForm($formId);

        if (in_array($fieldKey, self::$entryMetaKeys, true)) {
            $defaults = self::getEditorDefaultsForIvyFormsEntryMeta($fieldKey, $editable);
        } elseif (ctype_digit((string) $fieldKey)) {
            $fieldId = (int) $fieldKey;

            if (isset($compoundMaps['parentIds'][$fieldId])) {
                $isCompoundParent = true;
                $defaults = [
                    'column_type' => 'string',
                    'input_type' => 'none',
                    'filter_type' => 'text',
                ];
            } elseif (isset($compoundMaps['childToParent'][$fieldId])) {
                $isCompoundChild = true;
                $defaults = [
                    'column_type' => 'string',
                    'input_type' => 'text',
                    'filter_type' => 'text',
                ];
            } else {
                $fieldType = self::resolveIvyFormsFieldType($formId, $fieldId);
                if ($fieldType === null) {
                    return $columnConfig;
                }
                $defaults = self::getEditorDefaultsForIvyFormsFieldType($fieldType, $fieldId);
            }
        } else {
            return $columnConfig;
        }

        if ($editable && $fieldKey !== 'id' && !empty($columnConfig['id_column'])) {
            // Only IvyForms entry meta "id" may be the WDT ID editing column.
            $columnConfig['id_column'] = 0;
        }

        if (!$isNewColumn) {
            // Keep user column settings on updates. Only structural editor types
            // and the IvyForms entry ID invariant are integration-owned.
            if ($isCompoundParent || $isCompoundChild || ($defaults['input_type'] ?? '') === 'none') {
                $columnConfig['input_type'] = $defaults['input_type'];
            }
            if ($fieldKey === 'id' && $editable) {
                $columnConfig['visible'] = 0;
                $columnConfig['id_column'] = 1;
            }
            return $columnConfig;
        }

        $columnConfig['column_type'] = $defaults['column_type'];
        $columnConfig['input_type'] = $defaults['input_type'];

        if (!empty($defaults['filter_type'])) {
            $columnConfig['filter_type'] = $defaults['filter_type'];
        }

        if ($fieldKey === 'id' && $editable) {
            $columnConfig['visible'] = 0;
            $columnConfig['id_column'] = 1;
        } elseif ($editable && !empty($columnConfig['id_column'])) {
            // Only IvyForms entry meta "id" may be the WDT ID editing column.
            $columnConfig['id_column'] = 0;
        }

        // Column visibility follows the field picker; edit-only compound subfields stay hidden.
        if ($editable && ctype_digit((string) $fieldKey)) {
            $fieldId = (int) $fieldKey;
            if (isset($compoundMaps['childToParent'][$fieldId])) {
                if (self::fieldIdInList($fieldId, $selectedFieldIds)) {
                    $columnConfig['visible'] = 1;
                } elseif (self::fieldIdInList($fieldId, $fieldIds)) {
                    $columnConfig['visible'] = 0;
                }
            }
        }

        if (!empty($defaults['possible_values'])) {
            $columnConfig['possible_values'] = $defaults['possible_values'];
            $advancedSettings = json_decode($columnConfig['advanced_settings'] ?? '{}', true);
            if (!is_array($advancedSettings)) {
                $advancedSettings = [];
            }
            $advancedSettings['possibleValuesType'] = 'list';
            $columnConfig['advanced_settings'] = wp_json_encode($advancedSettings);
        }

        return $columnConfig;
    }

    /**
     * Resolve an IvyForms field type slug by form + field ID.
     *
     * @param int $formId
     * @param int $fieldId
     * @return string|null
     */
    private static function resolveIvyFormsFieldType(int $formId, int $fieldId): ?string
    {
        $fields = IvyFormsAPI::getFields($formId);
        if (is_wp_error($fields) || !is_array($fields)) {
            return null;
        }

        foreach ($fields as $field) {
            if ((int) $field->getId() === $fieldId) {
                return $field->getType();
            }
        }

        return null;
    }

    /**
     * WDT editor defaults for an IvyForms form field type (plan §8).
     *
     * @param string   $fieldType
     * @param int|null $fieldId Optional — hydrates possible values for choice fields.
     * @param bool     $isCompoundChild Whether this field is a name/address subfield.
     * @return array{column_type: string, input_type: string, filter_type: string, possible_values?: string}
     */
    private static function getEditorDefaultsForIvyFormsFieldType(
        string $fieldType,
        ?int $fieldId = null,
        bool $isCompoundChild = false
    ): array {
        $fieldType = strtolower(trim($fieldType));

        if ($isCompoundChild) {
            return [
                'column_type' => 'string',
                'input_type' => 'text',
                'filter_type' => 'text',
            ];
        }

        if (in_array($fieldType, ['name', 'address'], true)) {
            return [
                'column_type' => 'string',
                'input_type' => 'none',
                'filter_type' => 'text',
            ];
        }

        if (in_array($fieldType, self::$nonEditableIvyFormsFieldTypes, true)) {
            return [
                'column_type' => 'string',
                'input_type' => 'none',
                'filter_type' => 'none',
            ];
        }

        $defaults = [
            'column_type' => 'string',
            'input_type' => 'text',
            'filter_type' => 'text',
        ];

        switch ($fieldType) {
            case 'email':
                $defaults['input_type'] = 'email';
                break;
            case 'website':
                $defaults['column_type'] = 'link';
                $defaults['input_type'] = 'link';
                break;
            case 'textarea':
            case 'rich_text':
                $defaults['input_type'] = 'textarea';
                break;
            case 'number':
            case 'slider':
            case 'rating':
            case 'nps':
                $defaults['column_type'] = 'float';
                $defaults['input_type'] = 'text';
                $defaults['filter_type'] = 'number';
                break;
            case 'select':
            case 'radio':
            case 'likert':
                $defaults['input_type'] = 'selectbox';
                $defaults['filter_type'] = 'select';
                break;
            case 'checkbox':
            case 'multi-select':
            case 'gdpr':
                $defaults['input_type'] = 'multi-selectbox';
                $defaults['filter_type'] = 'multiselect';
                break;
            case 'date':
                $defaults['column_type'] = 'date';
                $defaults['input_type'] = 'date';
                $defaults['filter_type'] = 'date';
                break;
            case 'time':
                $defaults['column_type'] = 'time';
                $defaults['input_type'] = 'time';
                $defaults['filter_type'] = 'time';
                break;
            case 'phone':
            case 'text':
            default:
                break;
        }

        if ($fieldId && in_array($defaults['input_type'], ['selectbox', 'multi-selectbox'], true)) {
            $possibleValues = self::buildPossibleValuesForIvyFormsField($fieldId);
            if ($possibleValues !== '') {
                $defaults['possible_values'] = $possibleValues;
            }
        }

        return $defaults;
    }

    /**
     * WDT editor defaults for IvyForms entry meta columns.
     *
     * @param string $metaKey
     * @param bool   $editable Whether the table has Editable enabled.
     * @return array{column_type: string, input_type: string, filter_type: string}
     */
    private static function getEditorDefaultsForIvyFormsEntryMeta(string $metaKey, bool $editable): array
    {
        switch ($metaKey) {
            case 'id':
                return [
                    'column_type' => 'int',
                    'input_type' => 'none',
                    'filter_type' => 'number',
                ];
            case 'dateCreated':
                return [
                    'column_type' => 'datetime',
                    'input_type' => 'none',
                    'filter_type' => 'datetime',
                ];
            case 'userId':
            case 'formId':
                return [
                    'column_type' => 'int',
                    'input_type' => 'none',
                    'filter_type' => 'number',
                ];
            case 'status':
                return [
                    'column_type' => 'string',
                    'input_type' => 'none',
                    'filter_type' => 'select',
                    'possible_values' => 'unread|read',
                ];
            case 'starred':
                return [
                    'column_type' => 'string',
                    'input_type' => 'none',
                    'filter_type' => 'select',
                    'possible_values' => 'Yes|No',
                ];
            default:
                return [
                    'column_type' => 'string',
                    'input_type' => 'none',
                    'filter_type' => 'text',
                ];
        }
    }

    /**
     * Distinct column filter values for IvyForms SSP tables (replaces invalid SQL on JSON content).
     *
     * @param object $column RuntimeColumn instance.
     * @param bool   $filterByUserId Unused; kept for filter arity.
     * @param mixed  $tableData Unused; kept for filter arity.
     * @return array<int, string>
     */
    public static function getPossibleIvyFormsValuesRead($column, $filterByUserId, $tableData = null): array
    {
        unset($filterByUserId, $tableData);

        $parentTable = $column->getParentTable();
        if (!is_object($parentTable) || $parentTable->getTableType() !== 'ivyforms') {
            return [];
        }

        $content = json_decode($parentTable->getTableContent() ?? '{}');
        if (!is_object($content) || empty($content->formId)) {
            return [];
        }

        $fieldKey = self::mapOrigHeaderToFieldKey($column->getOriginalHeader(), $content);
        if ($fieldKey === null) {
            return [];
        }

        if ($fieldKey === 'starred') {
            return ['Yes', 'No'];
        }

        if (in_array($fieldKey, self::$entryMetaKeys, true)) {
            return self::collectDistinctEntryPropertyValues((int) $content->formId, $fieldKey);
        }

        if (ctype_digit((string) $fieldKey)) {
            return self::collectDistinctFormFieldValues((int) $content->formId, (int) $fieldKey);
        }

        return [];
    }

    /**
     * Collect distinct entry metadata values present in form entries.
     *
     * @param int    $formId
     * @param string $property Entry array key (status, userId, …).
     * @return array<int, string>
     */
    private static function collectDistinctEntryPropertyValues(int $formId, string $property): array
    {
        $cacheKey = $formId . ':entry:' . $property;
        if (isset(self::$distinctValuesCache[$cacheKey])) {
            return self::$distinctValuesCache[$cacheKey];
        }

        if (!isset(self::$formEntriesCache[$formId])) {
            self::$formEntriesCache[$formId] = self::getFormEntriesFromAPI($formId, ['perPage' => 'all']);
        }

        $entries = self::$formEntriesCache[$formId];
        $values = [];

        foreach ($entries as $entry) {
            if (count($values) >= self::DISTINCT_VALUES_MAX) {
                break;
            }

            if ($property === 'starred') {
                $value = !empty($entry['starred']) ? 'Yes' : 'No';
            } else {
                $value = isset($entry[$property]) ? (string) $entry[$property] : '';
            }

            if ($value === '' || in_array($value, $values, true)) {
                continue;
            }
            $values[] = $value;
        }

        sort($values, SORT_NATURAL | SORT_FLAG_CASE);

        self::$distinctValuesCache[$cacheKey] = $values;

        return $values;
    }

    /**
     * Collect distinct stored values for a form field across entries.
     *
     * @param int $formId
     * @param int $fieldId
     * @return array<int, string>
     */
    private static function collectDistinctFormFieldValues(int $formId, int $fieldId): array
    {
        $cacheKey = $formId . ':field:' . $fieldId;
        if (isset(self::$distinctValuesCache[$cacheKey])) {
            return self::$distinctValuesCache[$cacheKey];
        }

        if (!isset(self::$formEntriesCache[$formId])) {
            self::$formEntriesCache[$formId] = self::getFormEntriesFromAPI($formId, ['perPage' => 'all']);
        }

        $entries = self::$formEntriesCache[$formId];
        if ($entries === []) {
            self::$distinctValuesCache[$cacheKey] = [];
            return [];
        }

        $entryFields = IvyFormsAPI::getEntryFields($entries);
        $values = [];

        foreach ($entryFields as $entryField) {
            if (count($values) >= self::DISTINCT_VALUES_MAX) {
                break;
            }

            if ((int) ($entryField['fieldId'] ?? 0) !== $fieldId) {
                continue;
            }

            $value = $entryField['fieldValue'] ?? '';
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $value = trim((string) $value);
            if ($value === '' || in_array($value, $values, true)) {
                continue;
            }
            $values[] = $value;
        }

        sort($values, SORT_NATURAL | SORT_FLAG_CASE);

        self::$distinctValuesCache[$cacheKey] = $values;

        return $values;
    }

    /**
     * Build a pipe-separated possible-values list for WDT select / multi-select editors.
     *
     * @param int $fieldId
     * @return string
     */
    private static function buildPossibleValuesForIvyFormsField(int $fieldId): string
    {
        if (!method_exists(IvyFormsAPI::class, 'getFieldOptions')) {
            return '';
        }

        $options = IvyFormsAPI::getFieldOptions($fieldId);
        if (is_wp_error($options) || empty($options) || !is_array($options)) {
            return '';
        }

        $values = [];
        foreach ($options as $option) {
            if (is_object($option) && method_exists($option, 'getValue')) {
                $value = trim((string) $option->getValue());
                if ($value !== '') {
                    $values[] = $value;
                }
            }
        }

        return implode('|', array_unique($values));
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
                $origHeader = self::generateIvyFormsOrigHeader($fieldId, $usedOrigHeaders);
                $usedOrigHeaders[] = $origHeader;
                $columnHeaders[$origHeader] = $label;
            }
        }

        // Process entry columns - only for selected fields
        $entryColumns = EntryManager::getAllEntryColumns();
        foreach ($entryColumns as $key => $label) {
            if (in_array($key, $fieldIds)) {
                $origHeader = self::generateIvyFormsOrigHeader($key, $usedOrigHeaders);
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
