<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\InstallActions\DB;

use WPDataTables\Common\Exceptions\InvalidArgumentException;

/**
 * Class RowsTable — the `wpdatatables_rows` table (manual-data rows).
 *
 * @package WPDataTables\Services\InstallActions\DB
 */
class RowsTable extends AbstractDatabaseTable
{
    public const TABLE = 'wpdatatables_rows';

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
                                  data TEXT NOT NULL,
                                  UNIQUE KEY id (id)
                                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
