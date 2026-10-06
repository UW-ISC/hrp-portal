<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Rendering;

use WPDataTable;
use WPExcelDataTable;

/**
 * DataTables / Handsontable column-definition JSON builders.
 *
 * The legacy {@see \WPDataTable::getColumnDefinitions()},
 * {@see \WPDataTable::getColumnFilterDefinitions()},
 * {@see \WPDataTable::getColumnEditingDefinitions()} and the Excel-table
 * override on {@see \WPExcelDataTable::getColumnDefinitions()} are thin
 * delegators into this service.
 *
 * @package WPDataTables\Rendering
 */
class ColumnDefinitionBuilder
{
    /**
     * Build the comma-separated DataTables columnDefs JSON for a standard table.
     *
     * @param WPDataTable $table
     *
     * @return string
     */
    public function buildColumnDefinitions(WPDataTable $table)
    {
        $defs = array();
        foreach ($table->getIndexedColumns() as $key => &$dataColumn) {
            $def = $dataColumn->getColumnJSON($key);
            $def->aTargets = array($key);
            $defs[] = json_encode($def);
        }

        return implode(', ', $defs);
    }

    /**
     * Build the comma-separated advanced-filter column JSON.
     *
     * @param WPDataTable $table
     *
     * @return string
     */
    public function buildColumnFilterDefinitions(WPDataTable $table)
    {
        $columnDefinitions = array();
        foreach ($table->getIndexedColumns() as $key => $dataColumn) {
            /** @var WDTColumn $dataColumn */
            $columnDefinition = $dataColumn->getJSFilterDefinition();

            if ($table->getFilteringForm()) {
                $columnDefinition->sSelector = '#' . $table->getId() . '_' . $key . '_filter';
            }

            $columnDefinitions[] = json_encode($columnDefinition);
        }

        return implode(', ', $columnDefinitions);
    }

    /**
     * Build the comma-separated inline-editing column JSON.
     *
     * @param WPDataTable $table
     *
     * @return string
     */
    public function buildColumnEditingDefinitions(WPDataTable $table)
    {
        $columnDefinitions = array();
        foreach ($table->getIndexedColumns() as $key => $dataColumn) {
            /** @var WDTColumn $dataColumn */
            $columnDefinition = $dataColumn->getJSEditingDefinition();

            $columnDefinitions[] = json_encode($columnDefinition);
        }

        return implode(', ', $columnDefinitions);
    }

    /**
     * Build the Handsontable column definition array for an Excel/simple table.
     *
     * @param WPExcelDataTable $table
     *
     * @return array<int, mixed>
     */
    public function buildExcelColumnDefinitions(WPExcelDataTable $table)
    {
        $defs = array();
        foreach ($table->getIndexedColumns() as $key => &$dataColumn) {
            $def = $dataColumn->getColumnJSON();
            $defs[] = $def;
        }

        return $defs;
    }
}
