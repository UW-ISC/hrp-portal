<?php

namespace WDTIntegration;

use Exception;
use stdClass;
use WDTConfigController;
use wpDataTableConstructor;

defined('ABSPATH') or die('Access denied.');

class wpDataTableQueryConstructor extends wpDataTableConstructor
{
    private array $_query_parameters;
    private string $type;

    /**
     * @return array
     */
    public function getQueryParameters(): array
    {
        return $this->_query_parameters;
    }

    /**
     * @param array $posts_query
     */
    public function setQueryParameters(array $posts_query): void
    {
        $this->_query_parameters = $posts_query;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }


    public function __construct($type, $connection = '', $queryParameters = array())
    {
        parent::__construct($connection);
        $this->setQueryParameters($queryParameters);
        $this->setType($type);
    }

    /**
     * @throws Exception
     */
    public function generateWdtBasedOnWpQuery($tableData): stdClass
    {
        global $wpdb;

        $table_array = array(
            'title' => sanitize_text_field($tableData['name']),
            'table_type' => $this->getType(),
            'connection' => $tableData['connection'],
            'filtering' => 1,
            'filtering_form' => 0,
            'cache_source_data' => 0,
            'auto_update_cache' => 0,
            'sorting' => 1,
            'fixed_layout' => 0,
            'responsive' => 0,
            'word_wrap' => 1,
            'tools' => 1,
            'display_length' => 10,
            'fixed_columns' => 0,
            'server_side' => 1,
            'editable' => 0,
            'editor_roles' => '',
            'mysql_table_name' => '',
            'hide_before_load' => 1,
            'tabletools_config' => serialize(array(
                'print' => 1,
                'copy' => 1,
                'excel' => 1,
                'csv' => 1,
                'pdf' => 0
            )),
            'advanced_settings' => json_encode(array(
                'show_table_description' => false,
                'table_description' => sanitize_textarea_field($tableData['table_description']),
                'fixed_columns' => false,
                'fixed_left_columns_number' => 0,
                'fixed_right_columns_number' => 0,
                'fixed_header' => false,
            )),
        );

        $table_array['content'] = json_encode($this->getQueryParameters());

        $res = WDTConfigController::tryCreateTable($this->getType(), $table_array['content'], $tableData['connection']);

        if (empty($res->error)) {
            $wpdb->insert($wpdb->prefix . 'wpdatatables', $table_array);
            $tableId = $wpdb->insert_id;
            $res->table_id = $tableId;
            WDTConfigController::saveColumns((array)null, $res->table, $res->table_id);
            $res->columns = WDTConfigController::getColumnsConfig($tableId);
            do_action('wpdatatables_after_save_table', $tableId);
        }

        return $res;
    }

}