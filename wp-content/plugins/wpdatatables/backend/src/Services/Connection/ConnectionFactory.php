<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Connection;

use PDOSql;
use PDTSql;

/**
 * Builds external database driver instances for "separate" connections.
 *
 * Keeps a per-id instance cache so each separate connection is opened once per
 * request. The concrete driver classes (`PDTSql` for the WP/MySQL-compatible
 * path, `PDOSql` for PDO MSSQL/PostgreSQL) are provided by the
 * separate-db-connection integration and are referenced as global classes. Note
 * the intentional MSSQL fall-through: an MSSQL vendor with no recognised driver
 * falls into the PostgreSQL/PDO branch.
 *
 * @package WPDataTables\Services\Connection
 */
class ConnectionFactory
{
    /** @var ConnectionService */
    private ConnectionService $connections;

    /** @var array<string|int, mixed> */
    private array $instances = [];

    public function __construct(ConnectionService $connections)
    {
        $this->connections = $connections;
    }

    /**
     * Return only one instance of a separate connection per id.
     *
     * @param string|null $id
     *
     * @return mixed
     */
    public function getInstance($id = null)
    {
        if (empty($this->instances) || !isset($this->instances[$id])) {
            $this->instances[$id] = $this->create($id);
        }

        return $this->instances[$id];
    }

    /**
     * Create a separate connection.
     *
     * @param string|null $id
     * @param string|null $host
     * @param string|null $database
     * @param string|null $user
     * @param string|null $password
     * @param string|null $port
     * @param string|null $vendor
     * @param string|null $driver
     *
     * @return mixed
     */
    public function create($id = null, $host = null, $database = null, $user = null, $password = null, $port = null, $vendor = null, $driver = null)
    {
        if ($id) {
            foreach ($this->connections->getAll() as $connection) {
                if ($connection['id'] === $id) {
                    $host = $connection['host'];
                    $database = $connection['database'];
                    $user = $connection['user'];
                    $password = $connection['password'];
                    $port = $connection['port'];
                    $vendor = $connection['vendor'];
                    $driver = $connection['driver'];
                }
            }
        }

        switch ($vendor) {
            case ConnectionService::MSSQL:
                if (isset($driver) && $driver == 'sqlsrv') {
                    return new PDOSql($vendor, "$driver:Server=$host,$port;Database=$database", $user, $password);
                } elseif (isset($driver) && $driver == 'dblib') {
                    return new PDOSql($vendor, "$driver:version=7.0;charset=UTF-8;host=$host:$port;dbname=$database", $user, $password);
                } elseif (isset($driver) && $driver == 'odbc') {
                    return new PDOSql($vendor, "$driver:DRIVER={ODBC Driver 17 for SQL Server};Server=$host;Database=$database", $user, $password);
                }

            case ConnectionService::POSTGRESQL:
                return new PDOSql($vendor, "pgsql:host=$host;port=$port;dbname=$database", $user, $password);

            default:
                return new PDTSql($host, $database, $user, $password, $port);
        }
    }
}
