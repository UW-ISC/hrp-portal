<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\InstallActions\DB;

use WPDataTables\Common\Exceptions\InvalidArgumentException;

/**
 * Class TemplatesTable — the `wpdatatables_templates` table.
 *
 * @package WPDataTables\Services\InstallActions\DB
 */
class TemplatesTable extends AbstractDatabaseTable
{
    public const TABLE = 'wpdatatables_templates';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getSchema(): string
    {
        $table = self::getTableName();

        return "CREATE TABLE {$table} (
                        id bigint(20) NOT NULL AUTO_INCREMENT,
						table_type varchar(55) NULL,
						table_id bigint(20) NOT NULL,
                        data text NOT NULL,
                        content text NOT NULL,
                        settings text NOT NULL,
                        UNIQUE KEY id (id)
						) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
