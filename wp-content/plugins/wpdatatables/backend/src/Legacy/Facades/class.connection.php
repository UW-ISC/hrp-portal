<?php

use WPDataTables\Plugin\Plugin;
use WPDataTables\Services\Connection\ConnectionFactory;
use WPDataTables\Services\Connection\ConnectionService;

defined('ABSPATH') or die('Access denied.');

/**
 * Backward-compatible facade over the connection services.
 *
 * The implementation lives in {@see WPDataTables\Services\Connection\ConnectionService}
 * (metadata + identifier quoting) and {@see WPDataTables\Services\Connection\ConnectionFactory}
 * (driver instantiation + per-id cache). This class is kept as a thin facade
 * because `Connection::*` is public surface used across core and the integration
 * tiers. Every static method delegates to the container-resolved service; the
 * vendor constants remain here so existing `Connection::$MYSQL` comparisons keep working.
 */
class Connection
{
    public static $MYSQL = 'mysql';
    public static $MSSQL = 'mssql';
    public static $POSTGRESQL = 'postgresql';

    /**
     * @return ConnectionService
     */
    private static function service()
    {
        return Plugin::container()->get(ConnectionService::class);
    }

    /**
     * @return ConnectionFactory
     */
    private static function factory()
    {
        return Plugin::container()->get(ConnectionFactory::class);
    }

    /**
     * Return only one instance of separate connection
     *
     * @param $id
     */
    public static function getInstance($id = null)
    {
        return self::factory()->getInstance($id);
    }

    /**
     * Create separate connection
     */
    public static function create($id = null, $host = null, $database = null, $user = null, $password = null, $port = null, $vendor = null, $driver = null)
    {
        return self::factory()->create($id, $host, $database, $user, $password, $port, $vendor, $driver);
    }

    /**
     * Return left/right quote for table based on vendor
     *
     * @param String $vendor of the connection
     *
     * @return String
     */
    public static function getTableLeftRightQuote($vendor)
    {
        return self::service()->getTableLeftRightQuote($vendor);
    }

    /**
     * Return left quote for table column based on vendor
     *
     * @param String $vendor of the connection
     *
     * @return String
     */
    public static function getLeftColumnQuote($vendor)
    {
        return self::service()->getLeftColumnQuote($vendor);
    }

    /**
     * Return right quote for table column based on vendor
     *
     * @param String $vendor of the connection
     *
     * @return String
     */
    public static function getRightColumnQuote($vendor)
    {
        return self::service()->getRightColumnQuote($vendor);
    }

    /**
     * Quote an SQL identifier, including schema-qualified names.
     *
     * @param string $identifier Table or column identifier.
     * @param string $vendor     Connection vendor.
     *
     * @return string
     */
    public static function quoteQualifiedIdentifier($identifier, $vendor)
    {
        return self::service()->quoteQualifiedIdentifier($identifier, $vendor);
    }

    /**
     * Checks if separate connection is used
     *
     * @param String $id of the connection, in case of empty string, connection is WP MySql
     *
     * @return boolean
     * @throws Exception
     */
    public static function isSeparate($id = null)
    {
        return self::service()->isSeparate($id);
    }

    /**
     * Get type of DB (MySQL, MSSQL, PostgreSQL)
     *
     * @param String $id of the connection, in case of empty string, connection is WP MySql
     *
     * @return String
     * @throws Exception
     */
    public static function getVendor($id = null)
    {
        return self::service()->getVendor($id);
    }

    /**
     * Get name of DB
     *
     * @param String $id of the connection, in case of empty string, connection is WP MySql
     *
     * @return String
     * @throws Exception
     */
    public static function getName($id = null)
    {
        return self::service()->getName($id);
    }

    /**
     * Get all connections created in Settings
     */
    public static function getAll()
    {
        return self::service()->getAll();
    }

    /**
     * Checks if separate connection is enabled
     * @return boolean
     */
    public static function enabledSeparate()
    {
        return self::service()->enabledSeparate();
    }

    /**
     * Save all connections created in Settings
     *
     * @param $connections
     */
    public static function saveAll($connections)
    {
        self::service()->saveAll($connections);
    }
}

?>
