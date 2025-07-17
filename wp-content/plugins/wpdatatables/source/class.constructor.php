<?php

defined('ABSPATH') or die('Access denied.');

/**
 * Class Constructor contains methods and properties for constructing the tables
 * in wpDataTables WordPress plugin
 *
 * @author cjbug@ya.ru
 *
 * @since June 2014
 */
class wpDataTableConstructor
{

    private $_name;
    private $_index;
    private $_db;
    protected $_table_data;

    protected $_column_headers;

    private $_id = 0;


    /**
     * The constructor
     *
     * @param String $connection
     */
    public function __construct($connection = null)
    {
        if (WDT_ENABLE_MYSQL && (Connection::isSeparate($connection))) {
            $this->_db = Connection::getInstance($connection);
        }
    }

    /**
     * Sets ID;
     *
     * @param int $id
     */
    public function setTableId($id)
    {
        $this->_id = $id;
    }

    /**
     * Gets table ID
     */
    public function getTableId(): int
    {
        return $this->_id;
    }

    /**
     * Generate the new unique table name (For MySQL)
     *
     * @param String $connection
     */
    public function generateTableName($connection, $id)
    {
        $this->_name = 'wpdatatable_' . $id;

        if (!(Connection::isSeparate($connection))) {
            global $wpdb;
            $this->_name = $wpdb->prefix . $this->_name;
        }

        $this->_name = apply_filters('wpdatatables_before_generate_constructed_table_name', $this->_name);

        return $this->_name;
    }

    /**
     * Helper function to prepare the filter, editor, column types, and create statement
     *
     * @param $column_header
     * @param $column
     * @param String $connection
     *
     * @return array
     * @throws Exception
     */
    public static function defineColumnProperties($column_header, $column, $connection = null): array
    {
        $columnPropertiesConstruct = new stdClass();

        $allowed_types = array(
            "VARCHAR", "INT", "BIGINT", "TINYINT", "SMALLINT",
            "MEDIUMINT", "DECIMAL", "TEXT", "DATE", "DATETIME", "TIME"
        );

        $columnTypeMapper = array(
            "string" => "VARCHAR",
            "int" => "INT",
            "float" => "DECIMAL",
            "date" => "DATE",
            "datetime" => "DATETIME",
            "time" => "TIME",
            "link" => "VARCHAR",
            "email" => "VARCHAR",
            "image" => "VARCHAR",
        );

        // Default size for supported types
        $defaultTypeSizes = array(
            "VARCHAR" => 255,
            "DECIMAL" => "10,2",
        );

        $predefined_type_in_db = $column['predefined_type_in_db'] ?? null;

        if (!in_array($predefined_type_in_db, $allowed_types, true)) {
            if (isset($column['type']) && isset($columnTypeMapper[$column['type']])) {
                $column['predefined_type_in_db'] = $columnTypeMapper[$column['type']];
            } else {
                $column['predefined_type_in_db'] = 'VARCHAR';
            }
            $column['predefined_type_value_in_db'] = 255;
        }
        if (!preg_match('/^[\d,]+$/', $column['predefined_type_value_in_db']) && $column['predefined_type_value_in_db'] !== '') {
            $column['predefined_type_in_db'] = 'VARCHAR';
            $column['predefined_type_value_in_db'] = 255;
        }

        // Determine the default size only for types that support it
        if (isset($defaultTypeSizes[$column['predefined_type_in_db']])) {
            $column['predefined_type_value_in_db'] = $defaultTypeSizes[$column['predefined_type_in_db']];
        } else {
            $column['predefined_type_value_in_db'] = null;
        }

        $columnPropertiesConstruct->vendor = Connection::getVendor($connection);
        $columnPropertiesConstruct->isMySql = $columnPropertiesConstruct->vendor === Connection::$MYSQL;
        $columnPropertiesConstruct->isMSSql = $columnPropertiesConstruct->vendor === Connection::$MSSQL;
        $columnPropertiesConstruct->isPostgreSql = $columnPropertiesConstruct->vendor === Connection::$POSTGRESQL;

        $columnPropertiesConstruct->columnQuoteStart = Connection::getLeftColumnQuote($columnPropertiesConstruct->vendor);
        $columnPropertiesConstruct->columnQuoteEnd = Connection::getRightColumnQuote($columnPropertiesConstruct->vendor);
        $columnPropertiesConstruct->nullable = '';
        $columnPropertiesConstruct->columnCollate = '';
        $columnPropertiesConstruct->columnTextType = 'TEXT';
        $columnPropertiesConstruct->columnPredefinedType = $column['predefined_type_in_db'];
        $columnPropertiesConstruct->columnPredefinedTypeValue = $column['predefined_type_value_in_db'];
        $columnPropertiesConstruct->ValueForDB = $columnPropertiesConstruct->columnPredefinedType;
        if ($columnPropertiesConstruct->columnPredefinedTypeValue) {
            $columnPropertiesConstruct->ValueForDB .= '(' . $columnPropertiesConstruct->columnPredefinedTypeValue . ')';
        }

        if ($columnPropertiesConstruct->isMySql) {
            $columnPropertiesConstruct->columnIntType = $columnPropertiesConstruct->ValueForDB;
            $columnPropertiesConstruct->columnDateTimeType = 'DATETIME';
        }
        if ($columnPropertiesConstruct->isMSSql) {
            $columnPropertiesConstruct->columnCollate = 'COLLATE Latin1_General_CS_AI';
            $columnPropertiesConstruct->columnTextType = $column['predefined_type_in_db'];
            $columnPropertiesConstruct->columnIntType = $columnPropertiesConstruct->columnPredefinedType;
            $columnPropertiesConstruct->columnDateTimeType = 'DATETIME';
            $columnPropertiesConstruct->nullable = 'NULL';
        }
        if ($columnPropertiesConstruct->isPostgreSql) {
            $columnPropertiesConstruct->columnIntType = $columnPropertiesConstruct->columnPredefinedType;
            $columnPropertiesConstruct->columnDateTimeType = 'TIMESTAMP';
            if ($columnPropertiesConstruct->columnPredefinedType == 'DATETIME') {
                $columnPropertiesConstruct->columnPredefinedType = 'TIMESTAMP';
            }
        }

        $column_header = $columnPropertiesConstruct->columnQuoteStart . $column_header . $columnPropertiesConstruct->columnQuoteEnd;
        if ($columnPropertiesConstruct->columnPredefinedType == 'VARCHAR') {
            $columnProperties = self::columnPropertiesMapper($columnPropertiesConstruct, $column_header)['VARCHAR'][$column['type']];
        } else {
            $columnProperties = self::columnPropertiesMapper($columnPropertiesConstruct, $column_header)[$column['predefined_type_in_db']];
        }
        $editingDefaultValue = '';
        if ($column['type'] === 'multiselect') {
            if (isset($column['default_value']) && is_array($column['default_value'])) {
                $editingDefaultValue = sanitize_text_field(implode('|', $column['default_value']));
            }
        } else {
            if (isset($column['default_value']))
                $editingDefaultValue = sanitize_text_field($column['default_value']);
        }

        $columnProperties['advanced_settings'] = array(
            'sorting' => 1,
            'exactFiltering' => 0,
            'rangeSlider' => 0,
            'rangeMaxValueDisplay' => 'default',
            'customMaxRangeValue' => null,
            'filterLabel' => '',
            'editingDefaultValue' => $editingDefaultValue,
            'possibleValuesAjax' => $columnProperties['column_type'] === 'string' ? 10 : -1,
            'searchInSelectBox' => 1,
            'searchInSelectBoxEditing' => 1,
            'globalSearchColumn' => 1,
            'andLogic' => 0,
        );

        $columnProperties['visible'] = 1;

        $columnProperties['create_block'] = $columnProperties['create_block'] . ' ' . $columnPropertiesConstruct->nullable;

        if (!empty($column['possible_values'])) {
            $columnProperties['advanced_settings']['possibleValuesType'] = 'list';
        }

        return apply_filters('wpdatatables_filter_column_properties', $columnProperties, $column, $connection);

    }

    public static function columnPropertiesMapper($columnPropertiesConstruct, $column_header): array
    {
        $columnPropertiesMapper = [
            'VARCHAR' => [
                'string' => [
                    'editor_type' => 'text',
                    'filter_type' => 'text',
                    'column_type' => 'string',
                    'create_block' => "{$column_header}  $columnPropertiesConstruct->ValueForDB $columnPropertiesConstruct->columnCollate "
                ],
                'input' => [
                    'editor_type' => 'text',
                    'filter_type' => 'text',
                    'column_type' => 'string',
                    'create_block' => "{$column_header}  $columnPropertiesConstruct->ValueForDB $columnPropertiesConstruct->columnCollate "
                ],
                'select' => [
                    'editor_type' => 'selectbox',
                    'filter_type' => 'select',
                    'column_type' => 'string',
                    'create_block' => "{$column_header}  $columnPropertiesConstruct->ValueForDB $columnPropertiesConstruct->columnCollate "
                ],
                'memo' => [
                    'editor_type' => 'textarea',
                    'filter_type' => 'text',
                    'column_type' => 'string',
                    'create_block' => "{$column_header} $columnPropertiesConstruct->columnTextType $columnPropertiesConstruct->columnCollate "
                ],
                'multiselect' => [
                    'editor_type' => 'multi-selectbox',
                    'filter_type' => 'multiselect',
                    'column_type' => 'string',
                    'create_block' => "{$column_header}  $columnPropertiesConstruct->ValueForDB $columnPropertiesConstruct->columnCollate "
                ],
                'email' => [
                    'editor_type' => 'email',
                    'filter_type' => 'text',
                    'column_type' => 'email',
                    'create_block' => "{$column_header}  $columnPropertiesConstruct->ValueForDB $columnPropertiesConstruct->columnCollate "
                ],
                'link' => [
                    'editor_type' => 'link',
                    'filter_type' => 'text',
                    'column_type' => 'link',
                    'create_block' => "{$column_header}  $columnPropertiesConstruct->ValueForDB $columnPropertiesConstruct->columnCollate "
                ],
                'file' => [
                    'editor_type' => 'attachment',
                    'filter_type' => 'link',
                    'column_type' => 'link',
                    'create_block' => "{$column_header}  $columnPropertiesConstruct->ValueForDB $columnPropertiesConstruct->columnCollate "
                ],
                'image' => [
                    'editor_type' => 'attachment',
                    'filter_type' => 'image',
                    'column_type' => 'image',
                    'create_block' => "{$column_header}  $columnPropertiesConstruct->ValueForDB $columnPropertiesConstruct->columnCollate "
                ],
                'int' => [
                    'editor_type' => 'text',
                    'filter_type' => 'text',
                    'column_type' => 'string',
                    'create_block' => "{$column_header}  $columnPropertiesConstruct->ValueForDB $columnPropertiesConstruct->columnCollate "
                ],
                'float' => [
                    'editor_type' => 'text',
                    'filter_type' => 'text',
                    'column_type' => 'string',
                    'create_block' => "{$column_header}  $columnPropertiesConstruct->ValueForDB $columnPropertiesConstruct->columnCollate "
                ],
                'date' => [
                    'editor_type' => 'text',
                    'filter_type' => 'text',
                    'column_type' => 'string',
                    'create_block' => "{$column_header}  $columnPropertiesConstruct->ValueForDB $columnPropertiesConstruct->columnCollate "
                ],
                'datetime' => [
                    'editor_type' => 'text',
                    'filter_type' => 'text',
                    'column_type' => 'string',
                    'create_block' => "{$column_header}  $columnPropertiesConstruct->ValueForDB $columnPropertiesConstruct->columnCollate "
                ],
                'time' => [
                    'editor_type' => 'text',
                    'filter_type' => 'text',
                    'column_type' => 'string',
                    'create_block' => "{$column_header}  $columnPropertiesConstruct->ValueForDB $columnPropertiesConstruct->columnCollate "
                ]
            ],
            'INT' => [
                'editor_type' => 'text',
                'filter_type' => 'number',
                'column_type' => 'int',
                'create_block' => "{$column_header} $columnPropertiesConstruct->columnIntType "
            ],
            'BIGINT' => [
                'editor_type' => 'text',
                'filter_type' => 'number',
                'column_type' => 'int',
                'create_block' => "{$column_header} $columnPropertiesConstruct->columnIntType "
            ],
            'TINYINT' => [
                'editor_type' => 'text',
                'filter_type' => 'number',
                'column_type' => 'int',
                'create_block' => "{$column_header} $columnPropertiesConstruct->columnIntType "
            ],
            'SMALLINT' => [
                'editor_type' => 'text',
                'filter_type' => 'number',
                'column_type' => 'int',
                'create_block' => "{$column_header} $columnPropertiesConstruct->columnIntType "
            ],
            'MEDIUMINT' => [
                'editor_type' => 'text',
                'filter_type' => 'number',
                'column_type' => 'int',
                'create_block' => "{$column_header} $columnPropertiesConstruct->columnIntType "
            ],
            'DECIMAL' => [
                'editor_type' => 'text',
                'filter_type' => 'number',
                'column_type' => 'float',
                'create_block' => "{$column_header} $columnPropertiesConstruct->ValueForDB "
            ],
            'TEXT' => [
                'editor_type' => 'textarea',
                'filter_type' => 'text',
                'column_type' => 'string',
                'create_block' => "{$column_header} $columnPropertiesConstruct->columnTextType $columnPropertiesConstruct->columnCollate "
            ],
            'DATE' => [
                'editor_type' => 'date',
                'filter_type' => 'date-range',
                'column_type' => 'date',
                'create_block' => "{$column_header} DATE "
            ],
            'DATETIME' => [
                'editor_type' => 'datetime',
                'filter_type' => 'datetime-range',
                'column_type' => 'datetime',
                'create_block' => "{$column_header} $columnPropertiesConstruct->columnDateTimeType "
            ],
            'TIME' => [
                'editor_type' => 'time',
                'filter_type' => 'time-range',
                'column_type' => 'time',
                'create_block' => "{$column_header} TIME "
            ],
            'default' => [
                'editor_type' => 'text',
                'filter_type' => 'text',
                'column_type' => 'string',
                'create_block' => "{$column_header}  $columnPropertiesConstruct->columnTextType $columnPropertiesConstruct->columnCollate "
            ]
        ];

        return apply_filters('wpdatatables_filter_column_properties_mapper', $columnPropertiesMapper, $column_header, $columnPropertiesConstruct);
    }

    /**
     * Generates and saves a new MySQL table and a new wpDataTable
     */
    public function generateManualTable($tableData)
    {
        global $wpdb;
        $this->_table_data = apply_filters_deprecated(
            'wdt_before_generate_manual_table',
            array($tableData),
            WDT_INITIAL_STARTER_VERSION,
            'wpdatatables_before_generate_manual_table'
        );
        $this->_table_data = apply_filters('wpdatatables_before_generate_manual_table', $tableData);

        // Selected Database Connection Name
        $connection = $tableData['connection'];

        // Generate the MySQL table name
        $id = $wpdb->get_var('SELECT id FROM ' . $wpdb->prefix . 'wpdatatables' . ' ORDER BY id DESC LIMIT 1') + 1;
        if (trim($tableData['name_in_database']) != "") {
            if ((int)$tableData['is_used_prefix_for_db_name']) {
                $this->_name = $wpdb->prefix . sanitize_text_field($tableData['name_in_database']);
            } else {
                $this->_name = sanitize_text_field($tableData['name_in_database']);
            }
            try {
                if (strlen($this->_name) > 63 || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $this->_name)) {
                    throw new WDTException(__('The database name must be less than 64 characters and can only contain letters, numbers, and underscores. It cannot start with a number unless the prefix is included. ', 'wpdatatables'));
                }
            } catch (WDTException $e) {
                return $e;
            }
        } else {
            $this->generateTableName($connection, $id);
        }
        // Create the wpDataTable metadata
        $wpdb->insert(
            $wpdb->prefix . "wpdatatables",
            array(
                'title' => sanitize_text_field($this->_table_data['name']),
                'table_type' => 'manual',
                'connection' => $connection,
                'content' => 'SELECT 
                              * FROM ' . $this->_name,
                'server_side' => 1,
                'mysql_table_name' => $this->_name,
                'tabletools_config' => serialize(array(
                    'print' => 1,
                    'copy' => 1,
                    'excel' => 1,
                    'csv' => 1,
                    'pdf' => 0
                )),
                'advanced_settings' => json_encode(array(
                    'show_table_description' => false,
                    'table_description' => sanitize_textarea_field($this->_table_data['table_description']),
                    'fixed_columns' => false,
                    'fixed_left_columns_number' => 0,
                    'fixed_right_columns_number' => 0,
                    'fixed_header' => false,
                )),
            )
        );

        // Store the new table metadata ID
        $wpdatatable_id = $wpdb->insert_id;
        $newNameGenerated = false;
        $cnt = 1;
        while (!$newNameGenerated) {
            if ($tableData['name_in_database'] != "") {
                $checkTableQuery = "SHOW TABLES LIKE '{$this->_name}'";
                $res = $wpdb->get_results($checkTableQuery);
                if (!empty($res)) {
                    WPDataTable::deleteTable($wpdatatable_id);
                    add_action('admin_notices', function () {
                        echo '<div class="notice notice-error"><p>' . __('There was an error while trying to generate the table! Internal Server Error. Table with this name already exists in the database.', 'wpdatatables') . '</p></div>';
                    });
                }
            }
            $id = $wpdb->get_var('SELECT id FROM ' . $wpdb->prefix . 'wpdatatables' . ' ORDER BY id DESC LIMIT 1');
            $newName = 'wpdatatable_' . $id;
            $checkTableQuery = "SHOW TABLES LIKE '{$wpdb->prefix}{$newName}'";
            if (!(Connection::isSeparate($connection))) {
                $res = $wpdb->get_results($checkTableQuery);
                if (!empty($res)) {
                    $newName = 'wpdatatable_' . $id . '_' . $cnt;
                    $wpdatatable_id = $id . '_' . $cnt;
                    $checkTableQuery = "SHOW TABLES LIKE '{$newName}'";
                }
                $res = $wpdb->get_results($checkTableQuery);
            } else {
                $sql = Connection::getInstance($connection);
                $res = $sql->getRow($checkTableQuery);
                if (!empty($res)) {
                    $newName = $wpdb->prefix . 'wpdatatable_' . $id . '_' . $cnt;
                    $wpdatatable_id = $id . '_' . $cnt;
                    $checkTableQuery = "SHOW TABLES LIKE '{$newName}'";
                }
                $res = $sql->getRow($checkTableQuery);
            }
            if (!empty($res)) {
                $cnt++;
            } else {
                $newNameGenerated = true;
            }
        }
        if (strpos($wpdatatable_id, '_') !== false) {
            $wpdb->update(
                $wpdb->prefix . "wpdatatables",
                array(
                    'mysql_table_name' => $wpdb->prefix . 'wpdatatable_' . $wpdatatable_id,
                ),
                array(
                    'mysql_table_name' => $this->_name,
                )
            );
            $this->_name = $wpdb->prefix . 'wpdatatable_' . $wpdatatable_id;
            $wpdb->update(
                $wpdb->prefix . "wpdatatables",
                array(
                    'content' => 'SELECT 
                              * FROM ' . $this->_name,
                ),
                array(
                    'mysql_table_name' => $this->_name,
                )
            );
        } else if ($wpdatatable_id - $id >= 1) {
            $wpdb->update(
                $wpdb->prefix . "wpdatatables",
                array(
                    'mysql_table_name' => $wpdb->prefix . 'wpdatatable_' . $wpdatatable_id,
                ),
                array(
                    'mysql_table_name' => $this->_name,
                )
            );
            $this->_name = $wpdb->prefix . 'wpdatatable_' . $wpdatatable_id;
            $wpdb->update(
                $wpdb->prefix . "wpdatatables",
                array(
                    'content' => 'SELECT 
                              * FROM ' . $this->_name,
                ),
                array(
                    'mysql_table_name' => $this->_name,
                )
            );
        }
        $vendor = Connection::getVendor($connection);
        $isMySql = $vendor === Connection::$MYSQL;
        $isMSSql = $vendor === Connection::$MSSQL;
        $isPostgreSql = $vendor === Connection::$POSTGRESQL;

        $tableNameQuoteStart = Connection::getTableLeftRightQuote($vendor);
        $tableNameQuoteEnd = Connection::getTableLeftRightQuote($vendor);

        $origHeader = 'wdt_ID';

        // Prepare the create statement for the table itself
        if ($isMySql) {
            $create_statement = "CREATE TABLE " . $tableNameQuoteStart . $this->_name . $tableNameQuoteEnd . " (
 	 							wdt_ID INT( 11 ) NOT NULL AUTO_INCREMENT,
 	 							wdt_created_by VARCHAR(100),
 	 							wdt_created_at DATETIME,
 	 							wdt_last_edited_by VARCHAR(100),
 	 							wdt_last_edited_at DATETIME,";
        }

        if ($isMSSql) {
            $create_statement = "CREATE TABLE " . $tableNameQuoteStart . $this->_name . $tableNameQuoteEnd . " (
 	 							wdt_ID INT NOT NULL IDENTITY(1,1),
 	 							wdt_created_by VARCHAR(100) NULL,
 	 							wdt_created_at DATETIME NULL,
 	 							wdt_last_edited_by VARCHAR(100) NULL,
 	 							wdt_last_edited_at DATETIME NULL,";
        }

        if ($isPostgreSql) {
            $create_statement = "CREATE TABLE " . $tableNameQuoteStart . $this->_name . $tableNameQuoteEnd . " (
 	 							wdt_ID SERIAL,
 	 							wdt_created_by VARCHAR(100),
 	 							wdt_created_at TIMESTAMP,
 	 							wdt_last_edited_by VARCHAR(100),
 	 							wdt_last_edited_at TIMESTAMP,";
            $origHeader = 'wdt_id';
        }

        $column_headers = array();

        $column_index = 0;

        // Add metadata for ID column
        $wpdb->insert(
            $wpdb->prefix . "wpdatatables_columns",
            array(
                'table_id' => $wpdatatable_id,
                'orig_header' => $origHeader,
                'display_header' => $origHeader,
                'filter_type' => 'none',
                'column_type' => 'int',
                'visible' => 0,
                'pos' => $column_index,
                'id_column' => 1
            )
        );
        $wpdb->insert(
            $wpdb->prefix . "wpdatatables_columns",
            array(
                'table_id' => $wpdatatable_id,
                'orig_header' => 'wdt_created_by',
                'display_header' => 'wdt_created_by',
                'filter_type' => 'none',
                'column_type' => 'string',
                'visible' => 0,
                'pos' => ++$column_index
            )
        );
        $wpdb->insert(
            $wpdb->prefix . "wpdatatables_columns",
            array(
                'table_id' => $wpdatatable_id,
                'orig_header' => 'wdt_created_at',
                'display_header' => 'wdt_created_at',
                'filter_type' => 'none',
                'column_type' => 'datetime',
                'input_type' => 'datetime',
                'visible' => 0,
                'pos' => ++$column_index
            )
        );
        $wpdb->insert(
            $wpdb->prefix . "wpdatatables_columns",
            array(
                'table_id' => $wpdatatable_id,
                'orig_header' => 'wdt_last_edited_by',
                'display_header' => 'wdt_last_edited_by',
                'filter_type' => 'none',
                'column_type' => 'string',
                'visible' => 0,
                'pos' => ++$column_index
            )
        );
        $wpdb->insert(
            $wpdb->prefix . "wpdatatables_columns",
            array(
                'table_id' => $wpdatatable_id,
                'orig_header' => 'wdt_last_edited_at',
                'display_header' => 'wdt_last_edited_at',
                'filter_type' => 'none',
                'column_type' => 'datetime',
                'input_type' => 'datetime',
                'visible' => 0,
                'pos' => ++$column_index
            )
        );

        $column_index++;

        $additional_statement = '';
        $additional_statement = apply_filters_deprecated(
            'wpdt_add_default_columns',
            array($additional_statement, $wpdatatable_id, $column_index),
            WDT_INITIAL_STARTER_VERSION,
            'wpdatatables_add_default_columns'
        );;
        $additional_statement = apply_filters('wpdatatables_add_default_columns', $additional_statement, $wpdatatable_id, $column_index);
        if (!empty($additional_statement)) {
            $create_statement .= $additional_statement['statment'];
            $column_index = $additional_statement['column_index'];
        }

        foreach ($this->_table_data['columns'] as $column) {

            $column_header = WDTTools::generateMySQLColumnName(sanitize_text_field($column['name']), $column_headers);

            $column_headers[] = $column_header;
            if (!isset($column['orig_header'])) {
                $column['orig_header'] = sanitize_text_field($column['name']);
            }
            $this->_column_headers[$column['orig_header']] = $column_header;

            $columnProperties = self::defineColumnProperties($column_header, $column, $connection);

            // Create the column metadata in WPDB
            $wpdb->insert(
                $wpdb->prefix . "wpdatatables_columns",
                array(
                    'table_id' => $wpdatatable_id,
                    'orig_header' => $column_header,
                    'display_header' => sanitize_text_field($column['name']),
                    'filter_type' => sanitize_text_field($columnProperties['filter_type']),
                    'column_type' => sanitize_text_field($columnProperties['column_type']),
                    'visible' => (int)$columnProperties['visible'],
                    'pos' => $column_index,
                    'possible_values' => str_replace(',,;,|', '|', sanitize_text_field($column['possible_values'])),
                    'advanced_settings' => json_encode($columnProperties['advanced_settings']),
                    'input_type' => sanitize_text_field($columnProperties['editor_type'])
                )
            );

            $create_statement .= $columnProperties['create_block'] . ', ';

            $column_index++;
        }

        // Add the ID unique key
        if ($isMySql) {
            $create_statement .= " UNIQUE KEY wdt_ID (wdt_ID)) CHARACTER SET=utf8 COLLATE utf8_general_ci";
        }

        if ($isMSSql) {
            $create_statement .= " CONSTRAINT UC_{$this->_name}_wdt_ID UNIQUE (wdt_ID))";
        }

        if ($isPostgreSql) {
            $create_statement .= " UNIQUE (wdt_ID))";
        }


        // Call the create statement on WPDB or on external DB if it is defined
        if (Connection::isSeparate($connection)) {
            // External DB
            $this->_db->doQuery($create_statement, array());
            if ($this->_db->getLastError() != '') {
                WPDataTable::deleteTable($wpdatatable_id);
                throw new Exception(__('There was an error when trying to create the table on MySQL side', 'wpdatatables') . ': ' . $this->_db->getLastError());
            }
        } else {
            $wpdb->query($create_statement);
            $db_error = $wpdb->last_error;
            if ($db_error != '') {
                WPDataTable::deleteTable($wpdatatable_id);
                throw new Exception(__('There was an error when trying to create the table on MySQL side', 'wpdatatables') . ': ' . $db_error);
            }
        }

        return $wpdatatable_id;

    }

    /**
     * Returns a list of tables in the chosen DB
     *
     * @return array
     */
    public static function listMySQLTables($connection): array
    {

        $tables = array();

        if (Connection::isSeparate($connection)) {
            try {
                $sql = Connection::getInstance($connection);
            } catch (Exception $ex) {
                return $tables;
            }

            $vendor = Connection::getVendor($connection);
            $isMySql = $vendor === Connection::$MYSQL;
            $isMSSql = $vendor === Connection::$MSSQL;
            $isPostgreSql = $vendor === Connection::$POSTGRESQL;

            if ($isMySql) {
                $query = 'SHOW TABLES';
            }

            if ($isMSSql) {
                $query = 'SELECT table_name FROM INFORMATION_SCHEMA.TABLES';
            }

            if ($isPostgreSql) {
                $query = "SELECT tablename FROM pg_catalog.pg_tables where schemaname = 'public'";
            }

            $result = $sql->getArray($query, array());
            if (empty($result)) {
                return $tables;
            }
        } else {
            global $wpdb;
            $result = $wpdb->get_results('SHOW TABLES', ARRAY_N);
        }
        // Formatting the result to plain array
        foreach ($result as $row) {
            $tables[] = $row[0];
        }

        return $tables;

    }

    /**
     * Return a list of columns for the selected tables
     */
    public static function listMySQLColumns($tables, $connection): array
    {
        $columns = array('allColumns' => array(), 'sortedColumns' => array());
        if (!empty($tables)) {
            if (Connection::isSeparate($connection)) {
                try {
                    $sql = Connection::getInstance($connection);
                } catch (Exception $ex) {
                    return $columns;
                }
                foreach ($tables as $table) {
                    $columns['sortedColumns'][$table] = array();

                    $vendor = Connection::getVendor($connection);
                    $isMySql = $vendor === Connection::$MYSQL;
                    $isMSSql = $vendor === Connection::$MSSQL;
                    $isPostgreSql = $vendor === Connection::$POSTGRESQL;

                    if ($isMySql) {
                        $columns_query = "SHOW COLUMNS FROM {$table}";
                    }

                    if ($isMSSql) {
                        $columns_query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table}'";
                    }

                    if ($isPostgreSql) {
                        $columns_query = "SELECT * FROM information_schema.columns WHERE table_schema = 'public' AND table_name   = '{$table}'";
                    }


                    $table_columns = $sql->getAssoc($columns_query);
                    foreach ($table_columns as $table_column) {
                        if ($isMySql) {
                            $columns['sortedColumns'][$table][] = "{$table}.{$table_column['Field']}";
                            $columns['allColumns'][] = "{$table}.{$table_column['Field']}";
                        }

                        if ($isMSSql) {
                            $columns['sortedColumns'][$table][] = "{$table}.{$table_column['COLUMN_NAME']}";
                            $columns['allColumns'][] = "{$table}.{$table_column['COLUMN_NAME']}";
                        }

                        if ($isPostgreSql) {
                            $columns['sortedColumns'][$table][] = "{$table}.{$table_column['column_name']}";
                            $columns['allColumns'][] = "{$table}.{$table_column['column_name']}";
                        }
                    }
                }
            } else {
                global $wpdb;
                foreach ($tables as $table) {
                    $columns['sortedColumns'][$table] = array();
                    $table_columns = $wpdb->get_results("SHOW COLUMNS FROM {$table};", ARRAY_A);
                    foreach ($table_columns as $table_column) {
                        $columns['sortedColumns'][$table][] = "{$table}.{$table_column['Field']}";
                        $columns['allColumns'][] = "{$table}.{$table_column['Field']}";
                    }
                }
            }
        }

        return $columns;
    }

    /**
     * Generates a table based on the provided file and shows a preview
     *
     * @param $tableData
     *
     * @return array
     * @throws WDTException
     */
    public function previewFileTable($tableData): array
    {
        try {
            if (!($file = self::isUploadedFileEmpty($tableData['file']))) {
                throw new Exception(__('Empty file', 'wpdatatables'));
            }

            $tableDataObject = json_decode(json_encode($tableData), false);
            $objSourceFile = new wpDataTableSourceFile($file, $tableDataObject);

            $objSourceFile->setIsPreview(1);
            $objSourceFile->getTableTypeFromFile();
            $objSourceFile->prepareHeadingsArray();
        } catch (Exception $e) {
            return array('result' => 'error', 'message' => $e->getMessage());
        }

        $namedDataArray = $objSourceFile->getNamedDataArray();
        $headingsArray = $objSourceFile->getHeadingsArray();

        /** @noinspection PhpUnusedLocalVariableInspection */
        $columnTypeArray = WDTTools::detectColumnDataTypes($namedDataArray, $headingsArray);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $possibleColumnTypes = WDTTools::getPossibleColumnTypes();

        $ret_val = '';

        if (!current_user_can('unfiltered_html')) {
            foreach ($namedDataArray as $key => &$nameData) {
                foreach ($headingsArray as &$heading) {
                    $heading = is_null($heading) ? sanitize_text_field($heading) : wp_kses_post($heading);
                    $nameData[$heading] = is_null($nameData[$heading]) ? sanitize_text_field($nameData[$heading]) : wp_kses_post($nameData[$heading]);
                }
            }
        }

        if (!empty($namedDataArray)) {
            ob_start();
            include(WDT_TEMPLATE_PATH . 'admin/constructor/constructor_file_preview.inc.php');
            $ret_val = ob_get_contents();
            ob_end_clean();
        }

        return array('result' => 'success', 'message' => $ret_val);

    }

    /**
     * Reads the data from file in the DB and generates a wpDataTable
     *
     * @param $tableData
     *
     * @return mixed|string
     * @throws WDTException
     * @throws Exception
     */
    public function readFileData($tableData)
    {
        $columnTypes = array();
        $columnDateInputFormat = array();
        $columnHeadersTemp = array();

        if (!($file = wpDataTableConstructor::isUploadedFileEmpty($tableData['file']))) {
            return __('Empty file', 'wpdatatables');
        }

        for ($i = 0; $i < count($tableData['columns']); $i++) {
            if ($tableData['columns'][$i]['orig_header'] == '%%NEW_COLUMN%%') {
                $tableData['columns'][$i]['orig_header'] = 'column' . $i;
            }
            $columnHeader = WDTTools::generateMySQLColumnName($tableData['columns'][$i]['orig_header'], $columnHeadersTemp);
            $columnTypes[$columnHeader] = sanitize_text_field($tableData['columns'][$i]['type']);
            $columnDateInputFormat[$columnHeader] = sanitize_text_field($tableData['columns'][$i]['dateInputFormat']);
            $columnHeadersTemp[] = $columnHeader;
        }

        $tableDataObject = json_decode(json_encode($tableData), false);
        $objSourceFile = new wpDataTableSourceFile(
            $file,
            $tableDataObject,
            $columnTypes,
            $columnDateInputFormat,
            null
        );

        $objSourceFile->getTableTypeFromFile();
        try {
            $objSourceFile->prepareHeadingsArray();
        } catch (Exception $e) {
            die($e->getMessage());
        }

        $this->_id = $this->generateManualTable($tableData);

        $this->_column_headers = apply_filters_deprecated(
            'wpdt_insert_additional_column_header',
            array($this->_column_headers),
            WDT_INITIAL_STARTER_VERSION,
            'wpdatatables_insert_additional_column_header');
        $this->_column_headers = apply_filters('wpdatatables_insert_additional_column_header', $this->_column_headers);

        $vendor = Connection::getVendor($tableData['connection']);

        $columnQuoteStart = Connection::getLeftColumnQuote($vendor);
        $columnQuoteEnd = Connection::getRightColumnQuote($vendor);

        // Insert statement default beginning
        $insert_statement_beginning = WDTConfigController::createInsertStatement(
            $this->_name,
            $this->_column_headers,
            $columnQuoteStart,
            $columnQuoteEnd
        );

        $objSourceFile->prepareInsertBlocks($insert_statement_beginning, $this->_column_headers, $this->_name, 'import');
    }

    /**
     * Helper function to determine if an uploaded file is empty and converts upload URL to Path
     *
     * @param $file
     *
     * @return mixed|string|string[]|void
     */
    public static function isUploadedFileEmpty($file)
    {
        if (!empty($file)) {
            $xls_url = urldecode(esc_url($file));
            $uploads_dir = wp_upload_dir();
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $xls_url = str_replace($uploads_dir['baseurl'], str_replace('\\', '/', $uploads_dir['basedir']), $xls_url);
            } else {
                $xls_url = str_replace($uploads_dir['baseurl'], $uploads_dir['basedir'], $xls_url);
            }

            return $xls_url;
        } else {
            return 0;
        }
    }

    /**
     * Delete a column from a manually generated table
     *
     * @param $tableId
     * @param $columnName
     *
     * @throws Exception
     */
    public static function deleteManualColumn($tableId, $columnName, $columnType = null)
    {
        global $wpdb;
        $delete_column_id = 0;
        $delete_column_index = 0;

        $tableData = WDTConfigController::loadTableFromDB($tableId);

        $existing_columns = WDTConfigController::loadColumnsFromDB($tableId);

        foreach ($existing_columns as $existing_column) {
            if ($existing_column->orig_header == $columnName) {
                $delete_column_index = $existing_column->pos;
                $delete_column_id = $existing_column->id;
                $delete_column_header = $existing_column->orig_header;
                break;
            }
        }
        foreach ($existing_columns as $existing_column) {
            $advancedSettings = json_decode($existing_column->advanced_settings);
            if (isset($advancedSettings->transformValueText) && $advancedSettings->transformValueText !== "") {
                $advancedSettings->transformValueText = str_ireplace("{" . $delete_column_header . '.value}', "", $advancedSettings->transformValueText);

                $updated_data = json_encode($advancedSettings);
                $wpdb->update(
                    $wpdb->prefix . 'wpdatatables_columns',
                    array('advanced_settings' => $updated_data),
                    array('table_id' => $tableId, 'id' => $existing_column->id)
                );
            }
        }

        $vendor = Connection::getVendor($tableData->connection);
        $columnQuoteStart = Connection::getLeftColumnQuote($vendor);
        $columnQuoteEnd = Connection::getRightColumnQuote($vendor);

        $drop_statement = "ALTER TABLE {$tableData->mysql_table_name} DROP COLUMN " . $columnQuoteStart . "{$columnName}" . $columnQuoteEnd;

        // Delete the column from the MySQL table
        if ('formula' !== $columnType && 'masterdetail' !== $columnType) {
            if (Connection::isSeparate($tableData->connection)) {
                // External DB
                $Sql = Connection::getInstance($tableData->connection);
                $Sql->doQuery($drop_statement, array());
            } else {
                $wpdb->query($drop_statement);
            }
        }

        // Delete the column from wp_wpdatatable_columns
        if ($delete_column_id != 0) {
            $wpdb->delete(
                $wpdb->prefix . 'wpdatatables_columns',
                array('id' => $delete_column_id)
            );

            // Update the order of other columns
            $update_statement = "UPDATE " . $wpdb->prefix . "wpdatatables_columns 
                                      SET pos = pos - 1 
                                      WHERE table_id = {$tableId} 
                                          AND pos >= " . (int)$delete_column_index;

            $wpdb->query($update_statement);
        }

    }

    /**
     * Add a new column to manually generated table
     */
    public static function addNewManualColumn($tableId, $column_data)
    {
        global $wpdb;

        $tableData = WDTConfigController::loadTableFromDB($tableId);
        $existing_columns = WDTConfigController::loadColumnsFromDB($tableId);
        $dateFormat = get_option('wdtDateFormat');
        $timeFormat = get_option('wdtTimeFormat');

        $existing_headers = array();
        $column_index = 0;
        foreach ($existing_columns as $existing_column) {
            $existing_headers[] = $existing_column->orig_header;
            if ($existing_column->orig_header == $column_data['insert_after']) {
                $column_index = $existing_column->pos + 1;
            }
        }

        $new_column_mysql_name = WDTTools::generateMySQLColumnName($column_data['name'], $existing_headers);
        $columnProperties = self::defineColumnProperties($new_column_mysql_name, $column_data, $tableData->connection);

        $vendor = Connection::getVendor($tableData->connection);
        $leftColumnQuote = Connection::getLeftColumnQuote($vendor);
        $rightColumnQuote = Connection::getRightColumnQuote($vendor);
        $isMySql = $vendor === Connection::$MYSQL;
        $isMSSql = $vendor === Connection::$MSSQL;
        $isPostgreSql = $vendor === Connection::$POSTGRESQL;

        // Add the column to MySQL table
        if ($isMySql) {
            $alter_table_statement = "ALTER TABLE {$tableData->mysql_table_name} 
                                        ADD COLUMN {$columnProperties['create_block']} ";

            if ($column_data['insert_after'] == '%%beginning%%') {
                $alter_table_statement .= " FIRST";
            } else if ($column_data['insert_after'] != '%%end%%') {
                $alter_table_statement .= " AFTER {$leftColumnQuote}{$column_data['insert_after']}{$rightColumnQuote}";
            }
        }

        if ($isMSSql) {
            $alter_table_statement = "ALTER TABLE {$tableData->mysql_table_name} 
                                        ADD {$columnProperties['create_block']} ";
        }

        if ($isPostgreSql) {
            $alter_table_statement = "ALTER TABLE {$tableData->mysql_table_name} 
                                        ADD COLUMN {$columnProperties['create_block']} ";
        }

        // Call the create statement on WPDB or on external DB if it is defined
        if (Connection::isSeparate($tableData->connection)) {
            // External DB
            $Sql = Connection::getInstance($tableData->connection);
            $Sql->doQuery($alter_table_statement, array());
        } else {
            $wpdb->query($alter_table_statement);
        }

        // Fill in with default value if requested
        $column_data['default_value'] = sanitize_text_field($column_data['default_value']);
        if ($column_data['fill_default'] == 1 && ($column_data['default_value'] || $column_data['type'] == 'hidden')) {

            $valueQuoute = "'";

            if ($column_data['type'] == 'date') {
                $column_data['default_value'] =
                    DateTime::createFromFormat(
                        $dateFormat,
                        $column_data['default_value']
                    )->format('Y-m-d');
            } else if ($column_data['type'] == 'datetime') {
                $column_data['default_value'] =
                    DateTime::createFromFormat(
                        $dateFormat . ' ' . $timeFormat,
                        $column_data['default_value']
                    )->format('Y-m-d H:i:s');
            } else if ($column_data['type'] == 'time') {
                $column_data['default_value'] =
                    DateTime::createFromFormat(
                        $timeFormat,
                        $column_data['default_value']
                    )->format('H:i:s');
            } else if ($column_data['type'] == 'int' || $column_data['type'] == 'float') {
                if ($isMSSql || $isPostgreSql) {
                    $valueQuoute = '';
                }
            }

            $column_data = apply_filters('wpdatatables_filter_column_data_for_new_column', $column_data, $tableData);

            $where = '';


            if ($isMySql) {
                $where = 'WHERE 1';
            }

            if ($isMSSql) {
                $where = '';
            }

            if ($isPostgreSql) {
                $where = '';
            }

            $update_fill_default = "UPDATE {$tableData->mysql_table_name} 
                                            SET {$leftColumnQuote}{$new_column_mysql_name}{$rightColumnQuote} = {$valueQuoute}{$column_data['default_value']}{$valueQuoute} 
                                            {$where}";
            if (Connection::isSeparate($tableData->connection)) {
                // External DB
                $Sql->doQuery($update_fill_default, array());
            } else {
                $wpdb->query($update_fill_default);
            }
        }

        // Move the existing columns if necessary
        if ($column_data['insert_after'] == '%%end%%') {
            $column_index = count($existing_columns);
        } else {
            $update_statement = "UPDATE " . $wpdb->prefix . "wpdatatables_columns 
                                        SET pos = pos + 1 
                                        WHERE table_id = {$tableId} 
                                            AND pos >= " . (int)$column_index;
            $wpdb->query($update_statement);
        }
        // Add the column to wp_wpdatatables_columns
        $wpdb->insert(
            $wpdb->prefix . "wpdatatables_columns",
            array(
                'table_id' => $tableId,
                'orig_header' => $new_column_mysql_name,
                'display_header' => sanitize_text_field($column_data['name']),
                'filter_type' => $columnProperties['filter_type'],
                'column_type' => $columnProperties['column_type'],
                'pos' => $column_index,
                'visible' => (int)$columnProperties['visible'],
                'possible_values' => str_replace(',,;,|', '|', sanitize_text_field($column_data['possible_values'])),
                'advanced_settings' => json_encode($columnProperties['advanced_settings']),
                'input_type' => $columnProperties['editor_type']
            )
        );
    }

}
