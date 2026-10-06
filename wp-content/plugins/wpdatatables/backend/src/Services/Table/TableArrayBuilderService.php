<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Table;

use WPDataTable;
use WPExcelDataTable;
use WDTException;
use Exception;
use WPDataTables\Services\Tools\ToolsService;
use WDTColumn;

/**
 * Builds in-memory table structure from array data.
 *
 * @package WPDataTables\Services\Table
 */
class TableArrayBuilderService
{
    /**
     * @param WPDataTable $table
     * @param array       $rawDataArr
     * @param array       $wdtParameters
     *
     * @return bool
     */
    public function buildFromArray(WPDataTable $table, $rawDataArr, $wdtParameters)
    {

                if (empty($rawDataArr)) {
                    if (!isset($wdtParameters['data_types'])) {
                        $rawDataArr = array(0 => array('No data' => 'No data'));
                    } else {
                        $arrayEntry = array();
                        foreach ($wdtParameters['data_types'] as $cKey => $cType) {
                            $arrayEntry[$cKey] = $cKey;
                        }
                        $rawDataArr[] = $arrayEntry;
                    }
                    $table->setNoData(true);
                }

                $headerArr = ToolsService::extractHeaders($rawDataArr);
                //[<-- Full version insertion #09 -->]//
                if (!empty($wdtParameters['columnTitles'])) {
                    $headerArr = array_unique(
                        array_merge(
                            $headerArr,
                            array_keys($wdtParameters['columnTitles'])
                        )
                    );
                }

                $wdtColumnTypes = isset($wdtParameters['data_types']) ? $wdtParameters['data_types'] : array();

                if (empty($wdtColumnTypes)) {
                    $wdtColumnTypes = ToolsService::detectColumnDataTypes($rawDataArr, $headerArr);
                }

                if (empty($wdtColumnTypes)) {
                    foreach ($headerArr as $key) {
                        $wdtColumnTypes[$key] = 'string';
                    }
                }

                $table->getRuntimeTable()->setWdtColumnTypes($wdtColumnTypes);

                if (!$table->getNoData()) {
                    $table->setDataRows($rawDataArr);
                }

                $table->createColumnsFromArr($headerArr, $wdtParameters, $wdtColumnTypes);

                $dataRows = $table->getDataRows();

                if (empty($wdtParameters['dates_detected'])
                    && count(array_intersect(array('date', 'datetime', 'time'), $wdtColumnTypes))
                ) {
                    if (!($table instanceof WPExcelDataTable && !$table->serverSide())) {
                        foreach ($wdtColumnTypes as $key => $columnType) {
                            $currentDateFormat = isset($wdtParameters['dateInputFormat'][$key]) ? $wdtParameters['dateInputFormat'][$key] : null;
                            if (in_array($columnType, array('date', 'datetime', 'time'))) {
                                foreach ($dataRows as &$dataRow) {
                                    $dataRow[$key] = ToolsService::wdtConvertStringToUnixTimestamp($dataRow[$key], $currentDateFormat);
                                }
                                unset($dataRow);
                            }
                        }
                    }
                }

                $tableType = $wdtParameters['tableType'] ?? $table->getTableType();

                if (!in_array($tableType, array(
                        'mysql',
                        'manual'
                    )) && count(array_intersect(array('float', 'int'), $wdtColumnTypes))) {
                    $numberFormat = get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1;
                    foreach ($wdtColumnTypes as $key => $columnType) {
                        if ($columnType === 'float') {
                            foreach ($dataRows as &$dataRow) {
                                if (isset($dataRow[$key])) {
                                    if ($numberFormat == 1) {
                                        $dataRow[$key] = str_replace(',', '.', str_replace('.', '', $dataRow[$key]));
                                    } else {
                                        $dataRow[$key] = str_replace(',', '', $dataRow[$key]);
                                    }
                                }
                            }
                            unset($dataRow);
                        }
                        if ($columnType === 'int') {
                            foreach ($dataRows as &$dataRow) {
                                if (isset($dataRow[$key])) {
                                    if ($numberFormat == 1) {
                                        $dataRow[$key] = str_replace('.', '', $dataRow[$key]);
                                    } else {
                                        $dataRow[$key] = str_replace(',', '', $dataRow[$key]);
                                    }
                                }
                            }
                            unset($dataRow);
                        }
                        if ($columnType === 'string') {
                            foreach ($dataRows as &$dataRow) {
                                if (isset($dataRow[$key]) && (is_float($dataRow[$key]) || is_int($dataRow[$key]))) {
                                    $dataRow[$key] = strval($dataRow[$key]);
                                }
                            }
                            unset($dataRow);
                        }
                    }
                }
                foreach ($wdtColumnTypes as $key => $columnType) {
                    foreach ($dataRows as &$dataRow) {
                        if (isset($dataRow[$key])) {
                            $dataRow[$key] = wp_kses_post($dataRow[$key]);
                        }
                    }
                    unset($dataRow);
                }

                $table->setDataRows($dataRows);
                //[<-- Full version -->]//
                // Calculate formula columns
                if (in_array('formula', $wdtColumnTypes)) {
                    $table->calculateFormulaCells();
                }

                do_action('wpdatatables_custom_populate_cells', $table, $wdtColumnTypes);
                //[<--/ Full version -->]//

                return true;

    }

    /**
     * @param WPDataTable $table
     * @param array       $wdtParameters
     *
     * @return array
     */
    public function prepareColumns(WPDataTable $table, $wdtParameters)
    {
                $colObjs = array();
                foreach ($wdtParameters['data_types'] as $dataColumn_key => $dataColumn_type) {
                    $tableColumnClass = 'WDTColumn';
                    $colObjOptions = array(
                        'title' => $wdtParameters['columnTitles'][$dataColumn_key],
                        'decimalPlaces' => $wdtParameters['decimalPlaces'][$dataColumn_key],
                        'linkTargetAttribute' => $wdtParameters['linkTargetAttribute'][$dataColumn_key],
                        'linkNoFollowAttribute' => $wdtParameters['linkNoFollowAttribute'][$dataColumn_key],
                        'linkNoreferrerAttribute' => $wdtParameters['linkNoreferrerAttribute'][$dataColumn_key],
                        'linkSponsoredAttribute' => $wdtParameters['linkSponsoredAttribute'][$dataColumn_key],
                        'linkButtonAttribute' => $wdtParameters['linkButtonAttribute'][$dataColumn_key],
                        'linkButtonLabel' => $wdtParameters['linkButtonLabel'][$dataColumn_key],
                        'linkButtonClass' => $wdtParameters['linkButtonClass'][$dataColumn_key],
                        'rangeSlider' => $wdtParameters['rangeSlider'][$dataColumn_key],
                        'rangeMaxValueDisplay' => $wdtParameters['rangeMaxValueDisplay'][$dataColumn_key],
                        'customMaxRangeValue' => $wdtParameters['customMaxRangeValue'][$dataColumn_key],
                        'editingDefaultValue' => $wdtParameters['editingDefaultValue'][$dataColumn_key]
                    );
                    $colObjOptions = apply_filters_deprecated(
                        'wpdt_filter_supplementary_array_column_object',
                        array($colObjOptions, $wdtParameters, $dataColumn_key),
                        WDT_INITIAL_STARTER_VERSION,
                        'wpdatatables_filter_supplementary_array_column_object'
                    );
                    $colObjOptions = apply_filters('wpdatatables_filter_supplementary_array_column_object', $colObjOptions, $wdtParameters, $dataColumn_key);
                    $colObjs[$dataColumn_key] = $tableColumnClass::generateColumn($dataColumn_type, $colObjOptions);
                    $colObjs[$dataColumn_key]->setInputType($wdtParameters['input_types'][$dataColumn_key]);
                    $colObjs[$dataColumn_key]->setParentTable($table);
                    if ($dataColumn_type == 'int') {
                        if (in_array($dataColumn_key, $wdtParameters['skip_thousands']) || ($dataColumn_key == $wdtParameters['idColumn'])) {
                            $colObjs[$dataColumn_key]->setShowThousandsSeparator(false);
                            $table->addColumnsThousandsSeparator($dataColumn_key, 0);
                        } else {
                            $table->addColumnsThousandsSeparator($dataColumn_key, 1);
                        }
                    }
                }

                return $colObjs;

    }

    /**
     * @param WPDataTable $table
     * @param array       $main_res_dataRows
     * @param array       $wdtParameters
     * @param array       $colObjs
     *
     * @return mixed
     * @throws WDTException
     */
    public function prepareOutputData(WPDataTable $table, $main_res_dataRows, $wdtParameters, $colObjs)
    {
                $output = [];
                // Hoisted once out of the per-cell loop below (hot path):
                // wpId is constant for the table, so read it from the runtime entity once.
                $wpId = $table->getRuntimeTable()->getWpId();
                if (!empty($main_res_dataRows)) {
                    foreach ($wdtParameters['foreignKeyRule'] as $columnKey => $foreignKeyRule) {
                        if ($foreignKeyRule != null) {
                            $foreignKeyData = $table->joinWithForeignWpDataTable($columnKey, $foreignKeyRule, $main_res_dataRows);
                            $main_res_dataRows = $foreignKeyData['dataRows'];
                        }
                    }
                    $i = (int)$_POST['start'];
                    foreach ($main_res_dataRows as $res_row) {
                        $i++;
                        $row = array();
                        foreach ($wdtParameters['columnOrder'] as $dataColumn_key) {
                            if ($wdtParameters['data_types'][$dataColumn_key] == 'formula') {
                                try {
                                    $headers = array();
                                    $headersInFormula = $table->detectHeadersInFormula($wdtParameters['columnFormulas'][$dataColumn_key], array_keys($wdtParameters['data_types']));
                                    $headers = ToolsService::sanitizeHeaders($headersInFormula);
                                    $formulaVal =
                                        WPDataTable::solveFormula(
                                            $wdtParameters['columnFormulas'][$dataColumn_key],
                                            $headers,
                                            $res_row
                                        );
                                    if ($formulaVal == 0) $formulaVal = null;
                                    $row[$dataColumn_key] = apply_filters(
                                        'wpdatatables_filter_cell_output',
                                        $colObjs[$dataColumn_key]->returnCellValue($formulaVal),
                                        $wpId,
                                        $dataColumn_key
                                    );
                                } catch (Exception $e) {
                                    $row[$dataColumn_key] = 0;
                                }
                            } else if ($wdtParameters['data_types'][$dataColumn_key] == 'index') {
                                $row[$dataColumn_key] = apply_filters('wpdatatables_filter_cell_output', $colObjs[$dataColumn_key]->returnCellValue((int)$i), $wpId, $dataColumn_key);
                            } else {
                                if ($dataColumn_key != 'masterdetail') {
                                    $row[$dataColumn_key] = apply_filters('wpdatatables_filter_cell_output', $colObjs[$dataColumn_key]->returnCellValue($res_row[$dataColumn_key]), $wpId, $dataColumn_key);
                                }

                            }
                        }
                        $output[] = $table->formatAjaxQueryResultRow($row);
                    }
                }

                return $output;

    }
}
