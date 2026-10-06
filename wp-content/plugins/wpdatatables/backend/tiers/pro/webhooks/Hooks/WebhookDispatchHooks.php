<?php

namespace WDTIntegration\Webhooks;

defined('ABSPATH') or die('Access denied.');

/**
 * Listens to core row-mutation hooks and schedules webhook delivery.
 */
class WebhookDispatchHooks
{
    /** @var WebhookDispatcherService */
    private $dispatcher;

    public function __construct(WebhookDispatcherService $dispatcher = null)
    {
        $this->dispatcher = $dispatcher ?: new WebhookDispatcherService();
    }

    /**
     * @return void
     */
    public function register()
    {
        add_action('wpdatatables_after_frontent_edit_row', array($this, 'onFrontendEditRow'), 10, 4);
        add_action('wpdatatables_excel_after_frontent_edit_cells', array($this, 'onExcelEditCells'), 10, 3);
        add_action('wpdatatables_after_delete_row', array($this, 'onDeleteRow'), 10, 3);
        add_action('wpdatatables_excel_after_delete_row', array($this, 'onExcelDeleteRow'), 10, 3);
        add_action('wpdatatables_excel_after_delete_all_rows', array($this, 'onExcelDeleteAllRows'), 10, 2);
        add_action('wpdatatables_after_save_row', array($this, 'onSaveRow'), 10, 4);
        add_action('wpdatatables_after_bulk_import_rows', array($this, 'onBulkImport'), 10, 3);
        add_action('wpdatatables/public_api/after_row_created', array($this, 'onPublicApiCreated'), 10, 3);
        add_action('wpdatatables/public_api/after_row_updated', array($this, 'onPublicApiUpdated'), 10, 3);
        add_action('wpdatatables/public_api/after_row_deleted', array($this, 'onPublicApiDeleted'), 10, 2);
    }

    /**
     * @param array<string, mixed> $formData
     * @param mixed $idVal
     * @param int $tableId
     * @param bool $isNew
     * @return void
     */
    public function onFrontendEditRow($formData, $idVal, $tableId, $isNew = false)
    {
        $event = $isNew ? 'row.created' : 'row.updated';
        $rowId = $isNew && isset($formData) ? $idVal : $idVal;

        // Prefer the inserted ID from success path when available.
        if ($isNew && ($idVal === '0' || $idVal === 0 || $idVal === '')) {
            return;
        }

        $this->dispatcher->schedule(
            $event,
            (int) $tableId,
            array(
                'source' => 'frontend_edit',
                'row_id' => $idVal,
                'row_data' => is_array($formData) ? $this->sanitizeRowData($formData) : array(),
                'user_id' => get_current_user_id(),
            )
        );
    }

    /**
     * @param mixed $cells
     * @param array<string, mixed> $returnResult
     * @param int $tableId
     * @return void
     */
    public function onExcelEditCells($cells, $returnResult, $tableId)
    {
        if (!empty($returnResult['error'])) {
            return;
        }

        $rowData = array();
        $changedColumns = array();
        $rowId = null;

        if (is_array($cells)) {
            foreach ($cells as $cellGroup) {
                if (!is_array($cellGroup)) {
                    continue;
                }
                foreach ($cellGroup as $column => $value) {
                    if ($column === 'wdt_ID' || $column === 'wdt_id') {
                        $rowId = $value;
                        continue;
                    }
                    $rowData[$column] = $value;
                    $changedColumns[] = $column;
                }
            }
        }

        $this->dispatcher->schedule(
            'row.updated',
            (int) $tableId,
            array(
                'source' => 'excel_edit',
                'row_id' => $rowId,
                'row_data' => $this->sanitizeRowData($rowData),
                'changed_columns' => array_values(array_unique($changedColumns)),
                'user_id' => get_current_user_id(),
            )
        );
    }

    /**
     * @param mixed $idVal
     * @param int $tableId
     * @param string $idKey
     * @return void
     */
    public function onDeleteRow($idVal, $tableId, $idKey = '')
    {
        $this->dispatcher->schedule(
            'row.deleted',
            (int) $tableId,
            array(
                'source' => 'frontend_edit',
                'row_id' => $idVal,
                'row_data' => array($idKey => $idVal),
                'user_id' => get_current_user_id(),
            )
        );
    }

    /**
     * @param mixed $rowId
     * @param int $tableId
     * @param string $error
     * @return void
     */
    public function onExcelDeleteRow($rowId, $tableId, $error = '')
    {
        if ($error !== '') {
            return;
        }

        $this->dispatcher->schedule(
            'row.deleted',
            (int) $tableId,
            array(
                'source' => 'excel_edit',
                'row_id' => $rowId,
                'user_id' => get_current_user_id(),
            )
        );
    }

    /**
     * @param int $tableId
     * @param array<string, mixed> $returnResult
     * @return void
     */
    public function onExcelDeleteAllRows($tableId, $returnResult)
    {
        if (!empty($returnResult['error'])) {
            return;
        }

        $rowIds = isset($returnResult['success']) && is_array($returnResult['success'])
            ? $returnResult['success']
            : array();

        foreach ($rowIds as $rowId) {
            $this->dispatcher->schedule(
                'row.deleted',
                (int) $tableId,
                array(
                    'source' => 'excel_edit',
                    'row_id' => $rowId,
                    'user_id' => get_current_user_id(),
                )
            );
        }
    }

    /**
     * Simple-table row save. Full replace saves may fire many creates; that is
     * acceptable for v1 (each inserted row is a create).
     *
     * @param int $tableId
     * @param mixed $rowId
     * @param mixed $rowData
     * @param bool $isNew
     * @return void
     */
    public function onSaveRow($tableId = 0, $rowId = null, $rowData = null, $isNew = true)
    {
        if ((int) $tableId < 1) {
            return;
        }

        $data = array();
        if (is_object($rowData) && isset($rowData->data)) {
            $decoded = json_decode(is_string($rowData->data) ? $rowData->data : wp_json_encode($rowData->data), true);
            $data = is_array($decoded) ? $decoded : array();
        } elseif (is_array($rowData)) {
            $data = $rowData;
        } elseif (is_object($rowData)) {
            $data = (array) $rowData;
        }

        $this->dispatcher->schedule(
            $isNew ? 'row.created' : 'row.updated',
            (int) $tableId,
            array(
                'source' => 'admin_simple',
                'row_id' => $rowId,
                'row_data' => $this->sanitizeRowData($data),
                'user_id' => get_current_user_id(),
            )
        );
    }

    /**
     * @param int $tableId
     * @param array<int, mixed> $importedRowIds
     * @param array<string, mixed> $context
     * @return void
     */
    public function onBulkImport($tableId, $importedRowIds = array(), $context = array())
    {
        $this->dispatcher->schedule(
            'rows.bulk_imported',
            (int) $tableId,
            array(
                'source' => 'bulk_import',
                'row_ids' => is_array($importedRowIds) ? $importedRowIds : array(),
                'count' => isset($context['count'])
                    ? (int) $context['count']
                    : (is_array($importedRowIds) ? count($importedRowIds) : 0),
                'user_id' => get_current_user_id(),
                'table' => isset($context['table']) ? $context['table'] : null,
            )
        );
    }

    /**
     * @param int $tableId
     * @param mixed $insertId
     * @param array<string, mixed> $formatted
     * @return void
     */
    public function onPublicApiCreated($tableId, $insertId, $formatted)
    {
        $this->dispatcher->schedule(
            'row.created',
            (int) $tableId,
            array(
                'source' => 'public_api',
                'row_id' => $insertId,
                'row_data' => is_array($formatted) ? $this->sanitizeRowData($formatted) : array(),
                'user_id' => get_current_user_id(),
            )
        );
    }

    /**
     * @param int $tableId
     * @param mixed $rowId
     * @param array<string, mixed> $formatted
     * @return void
     */
    public function onPublicApiUpdated($tableId, $rowId, $formatted)
    {
        $this->dispatcher->schedule(
            'row.updated',
            (int) $tableId,
            array(
                'source' => 'public_api',
                'row_id' => $rowId,
                'row_data' => is_array($formatted) ? $this->sanitizeRowData($formatted) : array(),
                'user_id' => get_current_user_id(),
            )
        );
    }

    /**
     * @param int $tableId
     * @param mixed $rowId
     * @return void
     */
    public function onPublicApiDeleted($tableId, $rowId)
    {
        $this->dispatcher->schedule(
            'row.deleted',
            (int) $tableId,
            array(
                'source' => 'public_api',
                'row_id' => $rowId,
                'user_id' => get_current_user_id(),
            )
        );
    }

    /**
     * Strip SQL-quoted wrappers from form data when present.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function sanitizeRowData(array $data)
    {
        $clean = array();
        foreach ($data as $key => $value) {
            if (is_string($value) && strlen($value) >= 2 && $value[0] === "'" && substr($value, -1) === "'") {
                $value = stripslashes(substr($value, 1, -1));
            }
            $clean[$key] = $value;
        }
        return $clean;
    }
}
