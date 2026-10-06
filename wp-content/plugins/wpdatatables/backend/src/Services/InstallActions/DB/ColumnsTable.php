<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\InstallActions\DB;

use WPDataTables\Common\Exceptions\InvalidArgumentException;

/**
 * Class ColumnsTable — the `wpdatatables_columns` table.
 *
 * @package WPDataTables\Services\InstallActions\DB
 */
class ColumnsTable extends AbstractDatabaseTable
{
    public const TABLE = 'wpdatatables_columns';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getSchema(): string
    {
        $table = self::getTableName();

        return "CREATE TABLE {$table} (
						id bigint(20) NOT NULL AUTO_INCREMENT,
						table_id bigint(20) NOT NULL,
						orig_header varchar(255) NOT NULL,
						display_header varchar(255) NOT NULL,
						filter_type enum('none','null_str','text','number','number-range','date-range','datetime-range','time-range','select','multiselect','checkbox') NOT NULL,
						column_type enum('autodetect','string','int','float','date','link','email','image','formula','datetime','time','masterdetail', 'select', 'cart', 'index') NOT NULL,
						input_type enum('none','text','textarea','mce-editor','date','datetime','time','link','email','selectbox','multi-selectbox','attachment') NOT NULL default 'text',
						input_mandatory tinyint(1) NOT NULL default '0',
                        id_column tinyint(1) NOT NULL default '0',
						group_column tinyint(1) NOT NULL default '0',
						sort_column tinyint(1) NOT NULL default '0',
						hide_on_phones tinyint(1) NOT NULL default '0',
						hide_on_tablets tinyint(1) NOT NULL default '0',
						visible tinyint(1) NOT NULL default '1',
						sum_column tinyint(1) NOT NULL default '0',
						skip_thousands_separator tinyint(1) NOT NULL default '0',
						width VARCHAR( 4 ) NOT NULL default '',
						possible_values TEXT NOT NULL,
						default_value TEXT NOT NULL,
						css_class VARCHAR(255) NOT NULL default '',
						text_before VARCHAR(255) NOT NULL default '',
						text_after VARCHAR(255) NOT NULL default '',
                        formatting_rules TEXT NOT NULL,
                        calc_formula TEXT NOT NULL,
						color VARCHAR(255) NOT NULL default '',
						advanced_settings TEXT NOT NULL,
						pos int(11) NOT NULL default '0',
						UNIQUE KEY id (id)
						) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
