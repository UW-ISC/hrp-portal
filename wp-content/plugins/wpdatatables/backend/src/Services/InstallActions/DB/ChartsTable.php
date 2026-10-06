<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\InstallActions\DB;

use WPDataTables\Common\Exceptions\InvalidArgumentException;

/**
 * Class ChartsTable — the `wpdatacharts` table.
 *
 * Note the irregular physical name (`wpdatacharts`, not `wpdatatables_charts`),
 * preserved from the legacy installer.
 *
 * @package WPDataTables\Services\InstallActions\DB
 */
class ChartsTable extends AbstractDatabaseTable
{
    public const TABLE = 'wpdatacharts';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getSchema(): string
    {
        $table = self::getTableName();

        return "CREATE TABLE {$table} (
                                  id bigint(20) NOT NULL AUTO_INCREMENT,
                                  wpdatatable_id bigint(20) NOT NULL,
                                  title varchar(255) NOT NULL,
                                  engine enum('google','highcharts','chartjs','apexcharts','highstock') NOT NULL,
                                  type varchar(255) NOT NULL,
                                  json_render_data text NOT NULL,
                                  UNIQUE KEY id (id)
                                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
