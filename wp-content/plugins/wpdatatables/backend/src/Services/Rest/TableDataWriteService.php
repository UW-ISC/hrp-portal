<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Rest;

use Connection;
use DateTime;
use WPDataTables\Common\Exceptions\ForbiddenException;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Common\Exceptions\NotFoundException;
use WPDataTables\PublicApi\Services\PublicApiTableAccess;
use WPDataTables\Services\Table\TableConfigService;
use WPDataTables\Services\Tools\ToolsService;

/**
 * Shared write logic for table row REST endpoints (public API edit/delete scopes).
 *
 * v1 supports WordPress DB mysql/manual tables with frontend editing enabled.
 *
 * @package WPDataTables\Services\Rest
 */
class TableDataWriteService
{
    /** @var TableConfigService */
    private $tableConfigService;

    public function __construct(TableConfigService $tableConfigService)
    {
        $this->tableConfigService = $tableConfigService;
    }

    /**
     * @param int                  $tableId
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function createRow(int $tableId, array $row)
    {
        $context = $this->loadWritableContext($tableId);
        $formatted = $this->formatRowValues($context, $row, null);

        return $this->persistRow($context, $formatted, null);
    }

    /**
     * @param int                  $tableId
     * @param string               $rowId
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function updateRow(int $tableId, string $rowId, array $row)
    {
        if ($rowId === '') {
            throw new InvalidArgumentException('A valid row id is required.');
        }

        $context = $this->loadWritableContext($tableId);
        $formatted = $this->formatRowValues($context, $row, $rowId);

        return $this->persistRow($context, $formatted, $rowId);
    }

    /**
     * @param int    $tableId
     * @param string $rowId
     * @return void
     */
    public function deleteRow(int $tableId, string $rowId)
    {
        if ($rowId === '') {
            throw new InvalidArgumentException('A valid row id is required.');
        }

        $context = $this->loadWritableContext($tableId);
        $this->deleteRowsByIds($context, [$rowId]);
    }

    /**
     * @param int          $tableId
     * @param string[]     $rowIds
     * @return array<string, mixed>
     */
    public function deleteRows(int $tableId, array $rowIds)
    {
        $normalized = array_values(array_filter(array_map('strval', $rowIds), static function ($id) {
            return $id !== '';
        }));

        if ($normalized === []) {
            throw new InvalidArgumentException('At least one row id is required.');
        }

        $context = $this->loadWritableContext($tableId);
        $deleted = $this->deleteRowsByIds($context, $normalized);

        return [
            'deleted' => $deleted,
        ];
    }

    /**
     * @param int $tableId
     * @return array<string, mixed>
     */
    private function loadWritableContext(int $tableId)
    {
        if ($tableId === 0) {
            throw new InvalidArgumentException('A valid table id is required.');
        }

        PublicApiTableAccess::assertCanAccess($tableId);

        $tableData = $this->tableConfigService->loadTableFromDB($tableId);
        if (!$tableData) {
            throw new NotFoundException('Table not found.');
        }

        if (empty($tableData->editable)) {
            throw new ForbiddenException('Table editing is not enabled for this table.');
        }

        if (empty($tableData->table_type) || !in_array($tableData->table_type, ['mysql', 'manual'], true)) {
            throw new InvalidArgumentException('Row writes are only supported for SQL and Manual tables.');
        }

        if (Connection::isSeparate($tableData->connection)) {
            throw new InvalidArgumentException('Row writes via the public API are not supported for separate database connections in v1.');
        }

        if (!empty($tableData->edit_only_own_rows)) {
            throw new InvalidArgumentException('Tables with "edit only own rows" are not supported via the public API.');
        }

        $columns = $this->tableConfigService->loadColumnsFromDB($tableId);
        if (!$columns) {
            throw new InvalidArgumentException('Table columns could not be loaded.');
        }

        $idKey = '';
        foreach ($columns as $column) {
            if (!empty($column->id_column)) {
                $idKey = $column->orig_header;
                break;
            }
        }

        if ($idKey === '') {
            throw new InvalidArgumentException('Table does not have an ID column configured.');
        }

        return [
            'tableData' => $tableData,
            'columns'   => $columns,
            'idKey'     => $idKey,
            'tableName' => ToolsService::applyPlaceholders($tableData->mysql_table_name),
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $row
     * @param string|null          $rowId
     * @return array<string, mixed>
     */
    private function formatRowValues(array $context, array $row, ?string $rowId)
    {
        $tableData = $context['tableData'];
        $columns = $context['columns'];
        $idKey = $context['idKey'];
        $formatted = [];

        $dateFormat = get_option('wdtDateFormat');
        $timeFormat = get_option('wdtTimeFormat');
        $nullValue = null;

        foreach ($columns as $column) {
            if (!empty($column->id_column)) {
                continue;
            }

            if ($column->input_type === 'none') {
                continue;
            }

            $header = $column->orig_header;
            if (!array_key_exists($header, $row)) {
                continue;
            }

            $value = $row[$header];
            if (is_array($value) || is_object($value)) {
                throw new InvalidArgumentException(sprintf('Invalid value for column "%s".', $header));
            }

            $value = $this->sanitizeCellValue($column, (string) $value);

            switch ($column->column_type) {
                case 'int':
                    $formatted[$header] = ($value === '') ? $nullValue : (int) $value;
                    break;
                case 'float':
                    $numberFormat = get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1;
                    if ($numberFormat == 1) {
                        $value = str_replace('.', '', $value);
                        $value = str_replace(',', '.', $value);
                    } else {
                        $value = str_replace(',', '', $value);
                    }
                    $formatted[$header] = ($value === '') ? $nullValue : (float) $value;
                    break;
                case 'date':
                    $formatted[$header] = $this->formatDateValue($value, $dateFormat, 'Y-m-d');
                    break;
                case 'datetime':
                    $formatted[$header] = $this->formatDateValue($value, $dateFormat . ' ' . $timeFormat, 'Y-m-d H:i:s');
                    break;
                case 'time':
                    $formatted[$header] = $this->formatDateValue($value, $timeFormat, 'H:i:s');
                    break;
                case 'email':
                    $formatted[$header] = sanitize_email($value);
                    break;
                default:
                    $formatted[$header] = sanitize_text_field($value);
                    break;
            }
        }

        if ($rowId !== null && $rowId !== '' && $rowId !== '0') {
            unset($formatted[$idKey]);
        }

        if ($formatted === [] && ($rowId === null || $rowId === '' || $rowId === '0')) {
            throw new InvalidArgumentException('Row data is required.');
        }

        /**
         * Filter row values before persisting via the public API.
         *
         * @since 7.x
         * @param array<string, mixed> $formatted
         * @param int                  $tableId
         * @param string|null          $rowId
         */
        return apply_filters('wpdatatables/public_api/format_row_before_save', $formatted, (int) $tableData->id, $rowId);
    }

    /**
     * @param string $value
     * @param string $inputFormat
     * @param string $storageFormat
     * @return string|null
     */
    private function formatDateValue(string $value, string $inputFormat, string $storageFormat)
    {
        if ($value === '') {
            return null;
        }

        $date = DateTime::createFromFormat($inputFormat, $value);
        if (!$date) {
            throw new InvalidArgumentException('Invalid date or time value supplied.');
        }

        return $date->format($storageFormat);
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $formatted
     * @param string|null          $rowId
     * @return array<string, mixed>
     */
    private function persistRow(array $context, array $formatted, ?string $rowId)
    {
        global $wpdb;

        $tableName = $context['tableName'];
        $idKey = $context['idKey'];
        $tableId = (int) $context['tableData']->id;

        if ($rowId !== null && $rowId !== '' && $rowId !== '0') {
            $whereValue = is_numeric($rowId) ? (int) $rowId : $rowId;

            if (!$this->rowExists($tableName, $idKey, $whereValue)) {
                throw new NotFoundException('Row not found.');
            }

            $result = $wpdb->update($tableName, $formatted, [$idKey => $whereValue]);

            if ($result === false) {
                if (!empty($wpdb->last_error)) {
                    error_log('wpDataTables public API update failed: ' . $wpdb->last_error);
                }

                throw new InvalidArgumentException(
                    __('There was an error trying to update the row.', 'wpdatatables')
                );
            }

            do_action('wpdatatables/public_api/after_row_updated', $tableId, $rowId, $formatted);

            return [
                'id'      => $rowId,
                'updated' => true,
            ];
        }

        $result = $wpdb->insert($tableName, $formatted);
        if ($result === false) {
            if (!empty($wpdb->last_error)) {
                error_log('wpDataTables public API insert failed: ' . $wpdb->last_error);
            }

            throw new InvalidArgumentException(
                __('There was an error trying to insert a new row.', 'wpdatatables')
            );
        }

        $insertId = $wpdb->insert_id ?: null;

        do_action('wpdatatables/public_api/after_row_created', $tableId, $insertId, $formatted);

        return [
            'id'      => $insertId,
            'created' => true,
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @param string[]             $rowIds
     * @return string[]
     */
    private function deleteRowsByIds(array $context, array $rowIds)
    {
        global $wpdb;

        $tableName = $context['tableName'];
        $idKey = $context['idKey'];
        $tableId = (int) $context['tableData']->id;
        $deleted = [];

        foreach ($rowIds as $rowId) {
            do_action('wpdatatables/public_api/before_row_deleted', $tableId, $rowId);

            $whereValue = is_numeric($rowId) ? (int) $rowId : $rowId;
            $result = $wpdb->delete($tableName, [$idKey => $whereValue]);

            if ($result === false) {
                if (!empty($wpdb->last_error)) {
                    error_log('wpDataTables public API delete failed: ' . $wpdb->last_error);
                }

                throw new InvalidArgumentException(
                    __('There was an error trying to delete the row.', 'wpdatatables')
                );
            }

            if ($result !== false && $result > 0) {
                $deleted[] = $rowId;
                do_action('wpdatatables/public_api/after_row_deleted', $tableId, $rowId);
            }
        }

        return $deleted;
    }

    /**
     * @param object $column
     * @param string $value
     * @return string
     */
    private function sanitizeCellValue($column, string $value)
    {
        switch ($column->column_type) {
            case 'email':
                return sanitize_email($value);
            case 'int':
            case 'float':
            case 'date':
            case 'datetime':
            case 'time':
                return sanitize_text_field($value);
            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * @param string       $tableName
     * @param string       $idKey
     * @param string|int   $whereValue
     * @return bool
     */
    private function rowExists(string $tableName, string $idKey, $whereValue)
    {
        global $wpdb;

        $placeholder = is_int($whereValue) ? '%d' : '%s';
        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM `{$tableName}` WHERE `{$idKey}` = {$placeholder}",
            $whereValue
        );

        return (int) $wpdb->get_var($sql) > 0;
    }
}
