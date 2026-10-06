<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\InstallActions\DB;

use WPDataTables\Common\Exceptions\InvalidArgumentException;

/**
 * Class CacheTable — the `wpdatatables_cache` table.
 *
 * @package WPDataTables\Services\InstallActions\DB
 */
class CacheTable extends AbstractDatabaseTable
{
    public const TABLE = 'wpdatatables_cache';

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
                                  table_type varchar(55) NOT NULL default '',
                                  table_content text NOT NULL,
                                  auto_update tinyint(1) NOT NULL default 0,
                                  updated_time TIMESTAMP NOT NULL default CURRENT_TIMESTAMP,
                                  data LONGTEXT NOT NULL,
                                  log_errors text NOT NULL,
                                  UNIQUE KEY id (id)
                                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
