<?php

use PhpOffice\PhpSpreadsheet\Shared\Date;

defined('ABSPATH') or die('Access denied.');

/**
 * Helper class for adding data from a source file
 */
class wpDataTableSourceFile
{
    private $_file;
    private $_fileSourceAction;
    private $_tableType;
    private $_namedDataArray;
    private $_headingsArray;
    private $_columnOrigHeaders;
    private $_highestRow;
    private $_highestColumn;
    private $_objReader;
    private $_tableData;
    private $_columnTypes;
    private $_columnDateInputFormat;
    private $_dataRows;
    private $_objPHPExcel;
    private $_objWorksheet;
    private $_isTableDataAnObject;
    private $_db;
    private $_isPreview = 0;
    private $_tableConnection;
    private $_insertLimiter;

    /**
     * @return string
     */
    public function getTableConnection()
    {
        return $this->_tableConnection;
    }

    /**
     * @param string $tableConnection
     */
    public function setTableConnection($tableConnection)
    {
        $this->_tableConnection = $tableConnection;
    }

    /**
     * @return mixed
     */
    public function getFileSourceAction()
    {
        return $this->_fileSourceAction;
    }

    /**
     * @param mixed $fileSourceAction
     */
    public function setFileSourceAction($fileSourceAction)
    {
        $this->_fileSourceAction = $fileSourceAction;
    }

    /**
     * @return int 1 || 0
     */
    public function getIsPreview()
    {
        return $this->_isPreview;
    }

    /**
     * @param $isPreview
     */
    public function setIsPreview($isPreview)
    {
        $this->_isPreview = $isPreview;
    }

    /**
     * @return mixed
     */
    public function getObjWorksheet()
    {
        return $this->_objWorksheet;
    }

    /**
     * @param mixed $objWorksheet
     */
    public function setObjWorksheet($objWorksheet)
    {
        $this->_objWorksheet = $objWorksheet;
    }

    /**
     * @return mixed
     */
    public function getColumnTypes()
    {
        return $this->_columnTypes;
    }

    /**
     * @param mixed $columnTypes
     */
    public function setColumnTypes($columnTypes)
    {
        $this->_columnTypes = $columnTypes;
    }

    /**
     * @return mixed
     */
    public function getColumnDateInputFormat()
    {
        return $this->_columnDateInputFormat;
    }

    /**
     * @param mixed $columnDateInputFormat
     */
    public function setColumnDateInputFormat($columnDateInputFormat)
    {
        $this->_columnDateInputFormat = $columnDateInputFormat;
    }

    /**
     * @return mixed
     */
    public function getDataRows()
    {
        return $this->_dataRows;
    }

    /**
     * @param mixed $dataRows
     */
    public function setDataRows($dataRows)
    {
        $this->_dataRows = $dataRows;
    }

    /**
     * @return mixed
     */
    public function getObjPHPExcel()
    {
        return $this->_objPHPExcel;
    }

    /**
     * @param mixed $objPHPExcel
     */
    public function setObjPHPExcel($objPHPExcel)
    {
        $this->_objPHPExcel = $objPHPExcel;
    }

    /**
     * @return int
     */
    public function getInsertLimiter()
    {
        return $this->_insertLimiter;
    }

    /**
     * @param int $insertLimiter
     */
    public function setInsertLimiter($insertLimiter)
    {
        $this->_insertLimiter = $insertLimiter;
    }


    /**
     * wpDataTableSourceFile constructor.
     * @param $file
     * @param $fileSourceAction
     * @param $tableData
     * @param $columnTypes
     * @param $columnDateInputFormat
     */
    public function __construct(
        $file,
        $tableData,
        $columnTypes = null,
        $columnDateInputFormat = null,
        $fileSourceAction = null
    )
    {
        $this->setFile($file);
        $this->setFileSourceAction($fileSourceAction);
        $this->setTableData($tableData);
        $this->setColumnTypes($columnTypes);
        $this->setColumnDateInputFormat($columnDateInputFormat);
        if (WDT_ENABLE_MYSQL && (Connection::isSeparate($tableData->connection))) {
            $this->_db = Connection::getInstance($tableData->connection);
        }
        $this->setTableConnection($tableData->connection);
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->_file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->_file = $file;
    }

    /**
     * @return string
     */
    public function getTableType()
    {
        return $this->_tableType;
    }

    /**
     * @param string $tableType
     */
    public function setTableType($tableType)
    {
        $this->_tableType = $tableType;
    }

    /**
     * @return mixed
     */
    public function getNamedDataArray()
    {
        return $this->_namedDataArray;
    }

    /**
     * @param mixed $namedDataArray
     */
    public function setNamedDataArray($namedDataArray)
    {
        $this->_namedDataArray = $namedDataArray;
    }

    /**
     * @return array
     */
    public function getHeadingsArray()
    {
        return $this->_headingsArray;
    }

    /**
     * @param array $headingsArray
     */
    public function setHeadingsArray($headingsArray)
    {
        $this->_headingsArray = $headingsArray;
    }

    /**
     * @return array
     */
    public function getColumnOrigHeaders()
    {
        return $this->_columnOrigHeaders;
    }

    /**
     * @param mixed $columnOrigHeaders
     */
    public function setColumnOrigHeaders($columnOrigHeaders)
    {
        $this->_columnOrigHeaders = $columnOrigHeaders;
    }

    /**
     * @return mixed
     */
    public function getHighestRow()
    {
        return $this->_highestRow;
    }

    /**
     * @param mixed $highestRow
     */
    public function setHighestRow($highestRow)
    {
        $this->_highestRow = $highestRow;
    }

    /**
     * @return mixed
     */
    public function getHighestColumn()
    {
        return $this->_highestColumn;
    }

    /**
     * @param mixed $highestColumn
     */
    public function setHighestColumn($highestColumn)
    {
        $this->_highestColumn = $highestColumn;
    }

    /**
     * @return mixed
     */
    public function getObjReader()
    {
        return $this->_objReader;
    }

    /**
     * @param mixed $objReader
     */
    public function setObjReader($objReader)
    {
        $this->_objReader = $objReader;
    }

    /**
     * @return mixed
     */
    public function getTableData()
    {
        return $this->_tableData;
    }

    /**
     * @param mixed $tableData
     */
    public function setTableData($tableData)
    {
        $this->_tableData = $tableData;
    }


    /**
     * Executes an SQL statement based on connection
     *
     * @param $statement
     * @param null $connection
     * @throws Exception
     */
    public function executeQueryStatement($statement, $connection = null)
    {
        global $wpdb;
        if (Connection::isSeparate($connection)) {
            $this->_db->doQuery($statement, array());
        } else {
            $wpdb->query($statement);
        }
    }

    /**
     * Sets table type to google or excel based on uploaded file
     */
    public function getTableTypeFromFile()
    {
        $file = strtolower($this->getFile());
        if (strpos($file, 'https://docs.google.com/spreadsheets') !== false) {
            $this->setTableType('google');
        } else if ($this->endsWith($file, '.xls') || $this->endsWith($file, '.xlsx')
            || $this->endsWith($file, '.csv') || $this->endsWith($file, '.ods')) {
            $this->setTableType('excel');
        } else {
            $this->setTableType('invalid');
        }
    }

    /**
     * @return mixed|string|void
     * @throws WDTException
     * @throws Exception
     */
    public function prepareHeadingsArray()
    {
        $tableType = $this->getTableType();
        $file = $this->getFile();
        if ($tableType === 'google') {
            $namedDataArray = WPDataTable::googleRenderData($file);

            if ($this->getIsPreview()) {
                if (!empty($namedDataArray)) {
                    $namedDataArray = array_slice($namedDataArray, 0, 4);
                } else {
                    throw new WDTException(esc_html__('Google spreadsheet does not have data or could not be read. Please check data and also is URL correct and the spreadsheet is published to everyone.', 'wpdatatables'));
                }
            }
            if (empty($namedDataArray)) {
                throw new WDTException(esc_html__('There is no data in your source file. Please check your source file and try again.', 'wpdatatables'));
            }

            $headingsArray = array_keys($namedDataArray[0]);
            foreach($headingsArray as $heading){
                if ($heading === '')
                    throw new WDTException(esc_html__('One or more columns doesn\'t have a header. Please enter headers for all columns in order to proceed.'));
            }
            $highestRow = count($namedDataArray) - 1;
            $this->setNamedDataArray($namedDataArray);
            $this->setHeadingsArray($headingsArray);
            $this->setHighestRow($highestRow);
            $columnOrigHeaders = array();
            foreach ($headingsArray as $index => $heading) {
                $columnHeader = WDTTools::generateMySQLColumnName($heading, $columnOrigHeaders);
                $columnOrigHeaders[$index] = $columnHeader;
            }
            $this->setColumnOrigHeaders($columnOrigHeaders);

        } else if ($tableType === 'excel') {
            $objReader = WPDataTable::createObjectReader($file);
            $this->setObjReader($objReader);
            if ($this->getIsPreview()) {
                $filterSubset = new wpDataTableLimitReadFilter();
                $objReader->setReadFilter($filterSubset);
            }
            $objPHPExcel = $objReader->load($file);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            $highestRow = $objWorksheet->getHighestRow();
            $this->setHighestColumn($objWorksheet->getHighestDataColumn());

            $headingsArray = $objWorksheet->rangeToArray('A1:' . $this->getHighestColumn() . '1', null, true, true, true);
            foreach($headingsArray[1] as $heading){
                if ($heading === '')
                    throw new WDTException(esc_html__('One or more columns doesn\'t have a header. Please enter headers for all columns in order to proceed.'));
            }
            $headingsArray = array_map('trim', $headingsArray[1]);
            $columnOrigHeaders = array();
            if ($this->getIsPreview()) {
                $highestRow = min($highestRow, 5);
                $columnOrigHeaders = $headingsArray;
            } else {
                foreach ($headingsArray as $index => $heading) {
                    $columnHeader = WDTTools::generateMySQLColumnName($heading, $columnOrigHeaders);
                    $columnOrigHeaders[$index] = $columnHeader;
                }
                $this->setColumnOrigHeaders($columnOrigHeaders);
            }

            $r = 0;
            $namedDataArray = array();
            $dataRows = $objWorksheet->rangeToArray('A2:' . $this->getHighestColumn() . $highestRow, null, true, true, true);
            for ($row = 2; $row <= min($highestRow, 100); ++$row) {
                if (max($dataRows[$row]) !== null) {
                    ++$r;
                    foreach ($columnOrigHeaders as $dataColumnIndex => $dataColumnHeading) {
                        $dataColumnHeading = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataColumnHeading)));
                        $namedDataArray[$r][$dataColumnHeading] = trim($dataRows[$row][$dataColumnIndex]);
                    }
                }
            }
            if (empty($namedDataArray)) {
                throw new WDTException(esc_html__('There is no data in your source file. Please check your source file and try again.', 'wpdatatables'));
            }
            $this->setNamedDataArray($namedDataArray);
            $this->setObjPHPExcel($objPHPExcel);
            $this->setObjWorksheet($objWorksheet);
            $this->setHighestRow($highestRow);
            $this->setHeadingsArray($headingsArray);
        } else throw new WDTException(__('File format not supported!', 'wpdatatables'));
    }

    /**
     * Creates insert array to be used in SQL statement
     * @throws Exception
     */
    public function prepareInsertBlocks($insert_statement_beginning, $column_headers, $insertType)
    {
        $isGoogle = $this->getTableType() == 'google';
        $numberFormat = get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1;
        $insertBlocks = array();
        $r = -1;

        if (!$isGoogle) {
            $dataRows = $this->getObjWorksheet()->rangeToArray('A2:' . $this->getHighestColumn() . $this->getHighestRow(), null, true, true, true);
            $this->setDataRows($dataRows);
        }

        $this->setInsertLimiter($this->testInsertLimiter($this->getHighestRow()));

        for ($row = 0; $row <= $this->getHighestRow(); ++$row) {

            $insertArray = array();

            if (($row <= 1) && ($this->getTableType() == 'excel')) {
                continue;
            }

            $columnTypes = (array)$this->getColumnTypes();
            $dataColumnHeadingTempArr = [];

            if ($isGoogle) {

                if ($insertType == 'import') {
                    // Set all cells in the row to their defaults
                    foreach ($this->getTableData()->columns as $column) {
                        $insertArray[$column_headers[$column->orig_header]] = "'" . sanitize_text_field($column->default_value) . "'";
                    }
                }

                $headingsArray = $this->getHeadingsArray();
                foreach ($headingsArray as $dataColumnHeading) {
                    $dataColumnHeadingOriginal = $dataColumnHeading;
                    $dataColumnHeading = WDTTools::generateMySQLColumnName($dataColumnHeading, $dataColumnHeadingTempArr);
                    if ($insertType == 'import') {
                        if (!in_array($dataColumnHeading, array_values($column_headers))) {
                            continue;
                        }
                    }

                    $namedDataArray = $this->getNamedDataArray();
                    if (in_array($columnTypes[$dataColumnHeading], array('date', 'datetime', 'time'))) {
                        if ($columnTypes[$dataColumnHeading] == 'date') {
                            $date = WDTTools::wdtConvertStringToUnixTimestamp($namedDataArray[$row][$dataColumnHeadingOriginal], $this->getColumnDateInputFormat()[$dataColumnHeading]);
                            $insertArray[$dataColumnHeading] = $date ? "'" . date('Y-m-d', $date) . "'" : "NULL";
                        } elseif ($columnTypes[$dataColumnHeading] == 'datetime') {
                            $date = WDTTools::wdtConvertStringToUnixTimestamp($namedDataArray[$row][$dataColumnHeadingOriginal], $this->getColumnDateInputFormat()[$dataColumnHeading]);
                            $insertArray[$dataColumnHeading] = $date ? "'" . date('Y-m-d H:i:s', $date) . "'" : "NULL";
                        } elseif ($columnTypes[$dataColumnHeading] == 'time') {
                            $time = $namedDataArray[$row][$dataColumnHeadingOriginal];
                            $insertArray[$dataColumnHeading] = $time ? "'" . date('H:i:s', strtotime($time)) . "'" : "NULL";
                        }
                    } elseif ($columnTypes[$dataColumnHeading] == 'float') {
                        if ($numberFormat == 1) {
                            $insertArray[$dataColumnHeading] = "'" . esc_sql(str_replace(',', '.', str_replace('.', '', $namedDataArray[$row][$dataColumnHeadingOriginal]))) . "'";
                        } else {
                            $insertArray[$dataColumnHeading] = "'" . esc_sql(str_replace(',', '', $namedDataArray[$row][$dataColumnHeadingOriginal])) . "'";
                        }
                    } elseif ($columnTypes[$dataColumnHeading] == 'int') {
                        if ($numberFormat == 1) {
                            $insertArray[$dataColumnHeading] = "'" . esc_sql(str_replace('.', '', $namedDataArray[$row][$dataColumnHeadingOriginal])) . "'";
                        } else {
                            $insertArray[$dataColumnHeading] = "'" . esc_sql(str_replace(',', '', $namedDataArray[$row][$dataColumnHeadingOriginal])) . "'";
                        }
                    } else {
                        $insertArray[$dataColumnHeading] = "'" . esc_sql($namedDataArray[$row][$dataColumnHeadingOriginal]) . "'";
                    }

                    $dataColumnHeadingTempArr[] = $dataColumnHeading;
                }
            } else {
                if (max($this->getDataRows()[$row]) !== null) {
                    ++$r;

                    if ($insertType == 'import') {
                        // Set all cells in the row to their defaults
                        foreach ($this->getTableData()->columns as $column) {
                            $insertArray[$column_headers[$column->orig_header]] = "'" . sanitize_text_field($column->default_value) . "'";
                        }
                    }

                    foreach ($this->getHeadingsArray() as $dataColumnIndex => $dataColumnHeading) {

                        $dataColumnHeading = WDTTools::generateMySQLColumnName($dataColumnHeading, $dataColumnHeadingTempArr);

                        if ($insertType == 'import') {
                            if (!in_array($dataColumnHeading, array_values($column_headers))) {
                                continue;
                            }
                        }

                        $dataColumnHeading = addslashes($dataColumnHeading);
                        $dataRows = $this->getDataRows();

                        if (in_array($columnTypes[$dataColumnHeading], array('date', 'datetime', 'time'))) {
                            if ($this->getObjReader() instanceof PHPExcel_Reader_CSV) {
                                $date = WDTTools::wdtConvertStringToUnixTimestamp($dataRows[$row][$dataColumnIndex], $this->getColumnDateInputFormat()[$dataColumnHeading]);
                            } else {
                                if ($dataRows[$row][$dataColumnIndex] == null) {
                                    $date = null;
                                } else {
                                    $cell = $this->getObjPHPExcel()->getActiveSheet()->getCell($dataColumnIndex . '' . $row);
                                    if (Date::isDateTime($cell)) {
                                        $date = Date::excelToTimestamp($cell->getValue());
                                    } else {
                                        $date = WDTTools::wdtConvertStringToUnixTimestamp($dataRows[$row][$dataColumnIndex], $this->getColumnDateInputFormat()[$dataColumnHeading]);
                                    }
                                }
                            }

                            if ($columnTypes[$dataColumnHeading] == 'date') {
                                $insertArray[$dataColumnHeading] = ($date == null) ? 'NULL' : "'" . date('Y-m-d', $date) . "'";
                            } elseif ($columnTypes[$dataColumnHeading] == 'datetime') {
                                $insertArray[$dataColumnHeading] = ($date == null) ? 'NULL' : "'" . date('Y-m-d H:i:s', $date) . "'";
                            } elseif ($columnTypes[$dataColumnHeading] == 'time') {
                                $insertArray[$dataColumnHeading] = ($date == null) ? 'NULL' : "'" . date('H:i:s', $date) . "'";
                            }
                        } elseif ($columnTypes[$dataColumnHeading] == 'float' && gettype($dataRows[$row][$dataColumnIndex]) === 'string') {
                            if ($numberFormat == 1) {
                                $insertArray[$dataColumnHeading] = $dataRows[$row][$dataColumnIndex] !== null ? "'" . esc_sql(str_replace(',', '.', str_replace('.', '', $dataRows[$row][$dataColumnIndex]))) . "'" : 'NULL';
                            } else {
                                $insertArray[$dataColumnHeading] = $dataRows[$row][$dataColumnIndex] !== null ? "'" . esc_sql(str_replace(',', '', $dataRows[$row][$dataColumnIndex])) . "'" : 'NULL';
                            }
                        } elseif ($columnTypes[$dataColumnHeading] == 'int') {
                            if ($numberFormat == 1) {
                                $insertArray[$dataColumnHeading] = $dataRows[$row][$dataColumnIndex] !== null ? "'" . esc_sql(str_replace('.', '', $dataRows[$row][$dataColumnIndex])) . "'" : 'NULL';
                            } else {
                                $insertArray[$dataColumnHeading] = $dataRows[$row][$dataColumnIndex] !== null ? "'" . esc_sql(str_replace(',', '', $dataRows[$row][$dataColumnIndex])) . "'" : 'NULL';
                            }
                        } else {
                            if ($columnTypes[$dataColumnHeading] === 'float') {
                                $insertArray[$dataColumnHeading] = $dataRows[$row][$dataColumnIndex] !== null ? "'" . esc_sql($dataRows[$row][$dataColumnIndex]) . "'" : 'NULL';
                            } else {
                                $insertArray[$dataColumnHeading] = "'" . esc_sql($dataRows[$row][$dataColumnIndex]) . "'";
                            }
                        }

                        $dataColumnHeadingTempArr[] = $dataColumnHeading;
                    }
                }

            }

            if (!current_user_can('unfiltered_html')) {
                $columnHeaderTempArr = array();
                foreach ($this->getHeadingsArray() as $columnHeader) {
                    $columnHeader = WDTTools::generateMySQLColumnName($columnHeader, $columnHeaderTempArr);
                    if (!in_array($columnHeader, array_values($column_headers))) {
                        continue;
                    }
                    $insertArray[$columnHeader] = wp_kses_post($insertArray[$columnHeader]);
                    $columnHeaderTempArr[] = $columnHeader;
                }
            }

            $insertArray = apply_filters('wpdt_insert_additional_column_value', $insertArray, $row, $this->getTableType());

            if (!empty($insertArray)) {
                $insertBlocks[] = '(' . implode(', ', $insertArray) . ')';
            }

            if ($row % $this->getInsertLimiter() == 0) {
                $this->insertRowsChunk($insert_statement_beginning, $insertBlocks);
                $insertBlocks = array();
            }
        }

        //Insert the rest of the data
        $this->insertRowsChunk($insert_statement_beginning, $insertBlocks);
    }

    /**
     * Adds a new column to the table
     *
     * @param $column_data
     * @throws Exception
     */
    public function addNewColumn($column_data)
    {
        global $wpdb;
        $tableId = $this->getTableData()->id;
        $tableData = $this->getTableData();
        $column_index = $column_data['column_index'];
        $columnProperties = wpDataTableConstructor::defineColumnProperties($column_data['orig_header'], $column_data, $tableData->connection);

        $vendor = Connection::getVendor($tableData->connection);
        $isMySql = $vendor === Connection::$MYSQL;
        $isMSSql = $vendor === Connection::$MSSQL;
        $isPostgreSql = $vendor === Connection::$POSTGRESQL;

        // Add the column to MySQL table
        if ($isMySql || $isPostgreSql) {
            $alter_table_statement = "ALTER TABLE {$tableData->mysql_table_name} 
                                        ADD COLUMN {$columnProperties['create_block']} ";
        } else if ($isMSSql) {
            $alter_table_statement = "ALTER TABLE {$tableData->mysql_table_name} 
                                        ADD {$columnProperties['create_block']} ";
        }

        // Call the create statement on WPDB or on external DB if it is defined
        if (Connection::isSeparate($tableData->connection)) {
            // External DB
            $Sql = Connection::getInstance($tableData->connection);
            $Sql->doQuery($alter_table_statement, array());
        } else {
            $wpdb->query($alter_table_statement);
        }

        $update_statement = "UPDATE " . $wpdb->prefix . "wpdatatables_columns 
                                        SET pos = pos + 1 
                                        WHERE table_id = {$tableId}
                                        AND pos >= " . (int)$column_index;
        $wpdb->query($update_statement);

        // Add the column to wp_wpdatatables_columns
        $wpdb->insert(
            $wpdb->prefix . "wpdatatables_columns",
            array(
                'table_id' => $tableId,
                'orig_header' => $column_data['orig_header'],
                'display_header' => sanitize_text_field($column_data['display_header']),
                'filter_type' => $columnProperties['filter_type'],
                'column_type' => $columnProperties['column_type'],
                'pos' => $column_index,
                'advanced_settings' => json_encode($columnProperties['advanced_settings']),
                'input_type' => $columnProperties['editor_type']
            )
        );
    }

    /**
     * Create column object with default values
     *
     * @param $newColumnData
     * @param $tableId
     * @return stdClass
     */
    public function createColumnObject($newColumnData, $tableId)
    {
        $column = new stdClass();

        $column->calculateAvg = 0;
        $column->calculateMax = 0;
        $column->calculateMin = 0;
        $column->calculateTotal = 0;
        $column->checkboxesInModal = 0;
        $column->color = '';
        $column->conditional_formatting = 0;
        $column->css_class = '';
        $column->dateInputFormat = 0;
        $column->decimalPlaces = -1;
        $column->defaultSortingColumn = 0;
        $column->display_header = $newColumnData['display_header'];
        $column->editingDefaultValue = null;
        $column->editingNonEmpty = 0;
        $column->editor_type = null;
        $column->exactFiltering = 0;
        $column->filter_type = 'none';
        $column->filterDefaultValue = null;
        $column->filtering = 0;
        $column->globalSearchColumn = 0;
        $column->filterLabel = '';
        $column->searchInSelectBox = 1;
        $column->searchInSelectBoxEditing = 1;
        $column->foreignKeyRule = null;
        $column->formula = '';
        $column->groupColumn = 0;
        $column->hide_on_mobiles = 0;
        $column->hide_on_tablets = 0;
        $column->id = $tableId;
        $column->id_column = 0;
        $column->linkTargetAttribute = '_self';
        $column->linkButtonAttribute = 0;
        $column->linkNoFollowAttribute = 0;
        $column->linkNoreferrerAttribute = 0;
        $column->linkSponsoredAttribute = 0;
        $column->linkButtonLabel = null;
        $column->linkButtonClass = null;
        $column->orig_header = $newColumnData['orig_header'];
        $column->pos = $newColumnData['column_index'];
        $column->possibleValuesAddEmpty = 0;
        $column->possibleValuesType = null;
        $column->possibleValuesAjax = 10;
	    $column->column_align_fields = '';
        $column->rangeSlider = 0;
	    $column->column_align_header = '';
        $column->rangeMaxValueDisplay = 'default';
        $column->customMaxRangeValue = null;
        $column->skip_thousands_separator = 0;
        $column->sorting = 1;
        $column->text_after = '';
        $column->text_before = '';
        $column->type = $newColumnData['type'];
        $column->valuesList = null;
        $column->visible = 1;
        $column->width = 0;
	    $column->column_rotate_header_name = '';

        return $column;
    }

    /**
     * Create data from file in database
     *
     * @param $insert_statement_beginning
     * @param $insert_blocks
     *
     * @throws Exception
     */
    public function insertRowsChunk($insert_statement_beginning, $insert_blocks)
    {
        global $wpdb;

        if (count($insert_blocks) > 0) {
            $insert_statement = $insert_statement_beginning . " VALUES " . implode(', ', $insert_blocks);
            if (Connection::isSeparate($this->getTableData()->connection)) {
                // External DB
                $this->_db->doQuery($insert_statement, array());
            } else {
                $wpdb->query($insert_statement);
            }
        }
    }

    /**
     * Delete current data and columns from the table
     *
     * @throws Exception
     */
    public function maybeReplaceData($columnTypes)
    {
        if ($this->getFileSourceAction() == 'replaceTable') {
            //Delete all columns except wdt_ID
            $this->deleteAllColumnsExceptId($columnTypes);

            //Set new column types and date input format in source file object
            $this->detectNewColumnTypes();

            //Add columns from source file to table
            $this->createNewColumns($this->getColumnTypes());

            $this->getTableData()->replaceFileData = true;
        }
    }

    /**
     * Error handler for incorrect file data
     *
     * @param $columnHeaders
     * @throws WDTException
     */
    public function checkIfFileDataIsCorrect($columnHeaders)
    {

        if ($this->getTableType() === 'invalid') {
            throw new WDTException(esc_html__('The source file path or URL you\'ve provided is not valid!', 'wpdatatables'));
        }

        $headingsArray = $this->getHeadingsArray();

        if ($this->getFileSourceAction() != 'replaceTable') {
            if (count($headingsArray) !== count($columnHeaders)) {
                throw new WDTException(esc_html__('The number of columns in your file does not match the number of columns in the existing table. Please check your source file and try again.', 'wpdatatables'));
            }
        }

        if (!$this->getNamedDataArray()) {
            throw new WDTException(esc_html__('There is no data in your source file. Please check your source file and try again.', 'wpdatatables'));
        }

        $i = 0;
        $headingTempArr = [];
        foreach ($headingsArray as $heading) {
            if ($heading === '') {
                throw new WDTException(esc_html__('You are trying to insert a table without a column header. Please check your source file and try again.', 'wpdatatables'));
            }

            $heading = WDTTools::generateMySQLColumnName($heading, $headingTempArr);

            if ($this->getFileSourceAction() != 'replaceTable') {
                if ($heading !== $columnHeaders[$i++]) {
                    throw new WDTException(esc_html__('The column headers in your file do not match those in existing table. Please check your source file and try again.', 'wpdatatables'));
                }
            }

            $headingTempArr[] = $heading;
        }
    }

    /**
     * Helper function that determines if a string ends with another particular string
     *
     * @param string $haystack
     * @param string $needle
     */
    public function endsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }

    /**
     * Drops all existing table columns except wdt_ID
     *
     * @param $columnTypes
     * @throws Exception
     */
    private function deleteAllColumnsExceptId($columnTypes)
    {
        foreach ($this->getTableData()->columns as $index => $column) {
            if (strcasecmp($column->orig_header, 'wdt_ID')) {
                wpDataTableConstructor::deleteManualColumn(
                    $this->getTableData()->id,
                    $column->orig_header,
                    $columnTypes[$column->orig_header]);
                unset($this->getTableData()->columns[$index]);
            }
        }
    }

    /**
     * Sets new column types and date/time format for date/datetime/time columns
     *
     * @throws WDTException
     */
    private function detectNewColumnTypes()
    {
        $headingsArray = $this->getHeadingsArray();
        $columnOrigHeaders = $this->getColumnOrigHeaders();
        $isGoogle = $this->getTableType() == 'google';
        $columnDateInputFormat = array();

        $columnTypes = WDTTools::detectColumnDataTypes($this->getNamedDataArray(), $isGoogle ? $headingsArray : $columnOrigHeaders);

        $j = 0;
        foreach ($columnTypes as $key => $value) {
            if ($isGoogle) {
                $origKey = $this->getColumnOrigHeaders()[$j++];
                unset($columnTypes[$key]);
                $columnTypes[$origKey] = $value;
            } else {
                $origKey = $key;
            }

            if (in_array($value, array('date', 'datetime', 'time'))) {
                $dateFormat = get_option('wdtDateFormat') ? get_option('wdtDateFormat') : 'd/m/Y';
                $timeFormat = get_option('wdtTimeFormat') ? get_option('wdtTimeFormat') : 'H:i:s';
                $dateTimeFormat = $dateFormat . ' ' . $timeFormat;
                switch ($value) {
                    case 'date':
                        $columnDateInputFormat[$origKey] = $dateFormat;
                        break;
                    case 'datetime':
                        $columnDateInputFormat[$origKey] = $dateTimeFormat;
                        break;
                    default:
                        $columnDateInputFormat[$origKey] = $timeFormat;
                }
            }
        }

        $this->setColumnTypes($columnTypes);
        $this->setColumnDateInputFormat($columnDateInputFormat);
    }

    /**
     * Sets new column data
     *
     * @param $columnTypes
     * @throws Exception
     */
    private function createNewColumns($columnTypes)
    {
        $headingsArray = $this->getHeadingsArray();
        $i = 1;
        $headingTempArr = [];
        $columnHeaders = array();

        foreach ($headingsArray as $heading) {
            $dbHeading = WDTTools::generateMySQLColumnName($heading, $headingTempArr);

            $newColumnData = [];
            $newColumnData['orig_header'] = $dbHeading;
            $newColumnData['column_index'] = $i++;
            $newColumnData['display_header'] = $heading;
            $newColumnData['type'] = $columnTypes[$dbHeading];
            $newColumnData['default_value'] = '';

            $this->addNewColumn($newColumnData);
            $this->getTableData()->columns[] = $this->createColumnObject($newColumnData, $this->getTableData()->id);
            $headingTempArr[] = $dbHeading;
            $columnHeaders[] = $dbHeading;
        }
        $this->setColumnOrigHeaders($columnHeaders);
    }

    /**
     * Decide how many rows will be inserted into the table at once
     * given the number of total rows to be inserted
     *
     * @param $highestRow
     * @return int
     */
    private function testInsertLimiter($highestRow)
    {
        if ($highestRow <= 3000) {
            return 100;
        }

        return 1000;
    }

}