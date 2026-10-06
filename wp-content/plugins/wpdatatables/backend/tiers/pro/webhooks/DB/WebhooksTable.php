<?php

namespace WDTIntegration\Webhooks;

defined('ABSPATH') or die('Access denied.');

/**
 * Schema descriptor for the `wpdatatables_webhooks` table.
 */
class WebhooksTable
{
    public const TABLE = 'wpdatatables_webhooks';
    public const DB_VERSION = '1.0.0';
    public const DB_VERSION_OPTION = 'wdtWebhooksDbVersion';

    /**
     * Fully-qualified table name.
     *
     * @return string
     */
    public static function getTableName()
    {
        global $wpdb;

        return $wpdb->prefix . self::TABLE;
    }

    /**
     * CREATE TABLE statement for dbDelta().
     *
     * @return string
     */
    public static function getSchema()
    {
        $table = self::getTableName();

        return "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            table_id bigint(20) unsigned NOT NULL,
            name varchar(191) NOT NULL DEFAULT '',
            url text NOT NULL,
            method varchar(10) NOT NULL DEFAULT 'POST',
            format varchar(20) NOT NULL DEFAULT 'json',
            headers longtext NULL,
            secret varchar(255) NOT NULL DEFAULT '',
            event varchar(50) NOT NULL DEFAULT '',
            enabled tinyint(1) NOT NULL DEFAULT 1,
            last_status varchar(50) DEFAULT NULL,
            last_run_at datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY table_id (table_id),
            KEY table_event_enabled (table_id, event, enabled)
        ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }

    /**
     * Create / upgrade the table via dbDelta().
     *
     * @return void
     */
    public static function init()
    {
        if (file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        dbDelta(self::getSchema());
        update_option(self::DB_VERSION_OPTION, self::DB_VERSION);
    }

    /**
     * Drop the table.
     *
     * @return void
     */
    public static function delete()
    {
        global $wpdb;

        $table = self::getTableName();
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
        delete_option(self::DB_VERSION_OPTION);
    }

    /**
     * Ensure schema exists (safe for existing installs after update).
     *
     * @return void
     */
    public static function maybeInit()
    {
        if (get_option(self::DB_VERSION_OPTION) !== self::DB_VERSION) {
            self::init();
        }
    }
}
