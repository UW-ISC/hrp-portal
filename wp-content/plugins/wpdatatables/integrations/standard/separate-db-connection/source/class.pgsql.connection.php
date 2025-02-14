<?php

defined('ABSPATH') or die('Access denied.');

class PgSqlConnection
{
    private static $_instance = [];

    /**
     * Return only one instance of postgre connection
     *
     * @param $id
     */
    public static function getInstance($id = null)
    {

        if (empty(self::$_instance) || !isset(self::$_instance[$id]))
            self::$_instance[$id] = self::create($id);

        return self::$_instance[$id];
    }

    /**
     * Create postgre connection
     *
     * @param $id
     * @param $host
     * @param $database
     * @param $user
     * @param $password
     * @param $port
     */
    public static function create($id = null, $host = null, $database = null, $user = null, $password = null, $port = null)
    {
        if ($id) {
            foreach (Connection::getAll() as $conn) {
                if ($conn['id'] == $id) {
                    $host = $conn['host'];
                    $database = $conn['database'];
                    $user = $conn['user'];
                    $password = $conn['password'];
                    $port = $conn['port'];
                }
            }
        }
        return pg_connect("host=$host port=$port dbname=$database user=$user password=$password");
    }

}
