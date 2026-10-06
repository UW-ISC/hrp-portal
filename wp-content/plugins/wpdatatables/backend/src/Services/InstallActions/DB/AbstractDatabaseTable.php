<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\InstallActions\DB;

use WPDataTables\Common\Exceptions\InvalidArgumentException;

/**
 * Class AbstractDatabaseTable
 *
 * Base for the six `wpdatatables_*` table descriptors. Each concrete table
 * declares its physical name suffix in {@see static::TABLE} and the `dbDelta`
 * DDL in {@see static::getSchema()}; this base centralises name resolution,
 * creation and deletion. Mirrors ivyforms' `AbstractDatabaseTable` so the two
 * products share the install pattern, with one deviation: wpDataTables table
 * names are irregular (e.g. `wpdatacharts`, `wpdatatables` with no suffix), so
 * `TABLE` holds the full post-prefix name rather than a domain key.
 *
 * @package WPDataTables\Services\InstallActions\DB
 */
class AbstractDatabaseTable
{
    /** @var string Physical table name appended to `$wpdb->prefix`. */
    public const TABLE = '';

    /**
     * Fully-qualified table name, including the WordPress table prefix.
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getTableName(): string
    {
        if (!static::TABLE) {
            throw new InvalidArgumentException('Table name not provided.');
        }

        global $wpdb;

        return $wpdb->prefix . static::TABLE;
    }

    /**
     * The `CREATE TABLE` statement passed to `dbDelta()`. Overridden per table.
     *
     * @return string
     */
    public static function getSchema(): string
    {
        return '';
    }

    /**
     * Create / upgrade the table via `dbDelta()`.
     */
    public static function init(): void
    {
        if (file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        dbDelta(static::getSchema());
    }

    /**
     * Drop the table.
     *
     * @throws InvalidArgumentException
     */
    public static function delete(): void
    {
        global $wpdb;

        $table = static::getTableName();

        $wpdb->query("DROP TABLE IF EXISTS {$table}");
    }
}
