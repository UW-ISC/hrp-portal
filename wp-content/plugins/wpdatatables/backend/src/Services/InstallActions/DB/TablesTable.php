<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\InstallActions\DB;

use WPDataTables\Common\Exceptions\InvalidArgumentException;

/**
 * Class TablesTable — the main `wpdatatables` table.
 *
 * @package WPDataTables\Services\InstallActions\DB
 */
class TablesTable extends AbstractDatabaseTable
{
    public const TABLE = 'wpdatatables';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getSchema(): string
    {
        $table = self::getTableName();

        // Default connection value preserved from the legacy installer: a
        // placeholder when the separate-DB integration is enabled, empty otherwise.
        $connection = \Connection::enabledSeparate() ? 'abcdefghijk' : '';

        return "CREATE TABLE {$table} (
						id bigint(20) NOT NULL AUTO_INCREMENT,
						title varchar(255) NOT NULL,
                        show_title tinyint(1) NOT NULL default '1',
						table_type varchar(55) NOT NULL,
						file_location varchar(15) NOT NULL default '',
						connection varchar(55) NOT NULL DEFAULT '$connection',
						content text NOT NULL,
						filtering tinyint(1) NOT NULL default '1',
						filtering_form tinyint(1) NOT NULL default '0',
						cache_source_data tinyint(1) NOT NULL default '0',
						auto_update_cache tinyint(1) NOT NULL default '0',
						sorting tinyint(1) NOT NULL default '1',
						tools tinyint(1) NOT NULL default '1',
						server_side tinyint(1) NOT NULL default '0',
						editable tinyint(1) NOT NULL default '0',
						inline_editing tinyint(1) NOT NULL default '0',
						popover_tools tinyint(1) NOT NULL default '0',
						editor_roles varchar(255) NOT NULL default '',
						mysql_table_name text NOT NULL,
                        edit_only_own_rows tinyint(1) NOT NULL default 0,
                        userid_column_id int( 11 ) NOT NULL default 0,
						display_length int(3) NOT NULL default '10',
                        auto_refresh int(3) NOT NULL default 0,
						fixed_columns tinyint(1) NOT NULL default '-1',
						fixed_layout tinyint(1) NOT NULL default '0',
						responsive tinyint(1) NOT NULL default '0',
						scrollable tinyint(1) NOT NULL default '0',
						word_wrap tinyint(1) NOT NULL default '0',
						hide_before_load tinyint(1) NOT NULL default '0',
                        var1 VARCHAR( 255 ) NOT NULL default '',
                        var2 VARCHAR( 255 ) NOT NULL default '',
                        var3 VARCHAR( 255 ) NOT NULL default '',
                        var4 VARCHAR( 255 ) NOT NULL default '',
                        var5 VARCHAR( 255 ) NOT NULL default '',
                        var6 VARCHAR( 255 ) NOT NULL default '',
                        var7 VARCHAR( 255 ) NOT NULL default '',
                        var8 VARCHAR( 255 ) NOT NULL default '',
                        var9 VARCHAR( 255 ) NOT NULL default '',
                        tabletools_config VARCHAR( 255 ) NOT NULL default '',
						advanced_settings TEXT NOT NULL,
						UNIQUE KEY id (id)
						) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
