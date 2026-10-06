<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Connection;

use Exception;

/**
 * Connection metadata service.
 *
 * Owns the option-backed catalogue of user-defined "separate" database
 * connections and the per-vendor SQL identifier quoting rules. Driver
 * instantiation lives in {@see ConnectionFactory}. The global `Connection` class
 * is a thin shim delegating to this service, so the wide `Connection::getVendor()`
 * / `Connection::isSeparate()` call surface across core and integrations is
 * preserved.
 *
 * @package WPDataTables\Services\Connection
 */
class ConnectionService
{
    public const MYSQL = 'mysql';
    public const MSSQL = 'mssql';
    public const POSTGRESQL = 'postgresql';

    /**
     * Get all connections created in Settings.
     *
     * @return array
     */
    public function getAll(): array
    {
        return (array)json_decode(get_option('wdtSeparateCon'), true);
    }

    /**
     * Get type of DB (MySQL, MSSQL, PostgreSQL).
     *
     * @param string|null $id of the connection, empty string means WP MySQL
     *
     * @return string
     * @throws Exception
     */
    public function getVendor($id = null): string
    {
        if ($id) {
            foreach ($this->getAll() as $connection) {
                if ($connection['id'] === $id) {
                    return $connection['vendor'];
                }
            }

            throw new Exception("Connection '$id' is not defined in Settings");
        }

        return self::MYSQL;
    }

    /**
     * Get name of DB.
     *
     * @param string|null $id of the connection, empty string means WP MySQL
     *
     * @return string
     */
    public function getName($id = null): string
    {
        if ($id) {
            foreach ($this->getAll() as $connection) {
                if ($connection['id'] === $id) {
                    return $connection['name'];
                }
            }

            return 'Unknown Connection';
        }

        return 'WP Connection';
    }

    /**
     * Checks if separate connection is used.
     *
     * @param string|null $id of the connection, empty string means WP MySQL
     *
     * @return bool
     * @throws Exception
     */
    public function isSeparate($id = null): bool
    {
        if ($id && get_option('wdtUseSeparateCon')) {
            if ($id) {
                foreach ($this->getAll() as $connection) {
                    if ($connection['id'] === $id) {
                        return true;
                    }
                }

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Checks if separate connection is enabled.
     *
     * @return bool
     */
    public function enabledSeparate(): bool
    {
        return get_option('wdtUseSeparateCon') ? true : false;
    }

    /**
     * Save all connections created in Settings.
     *
     * @param mixed $connections
     *
     * @return void
     */
    public function saveAll($connections): void
    {
        update_option('wdtUseSeparateCon', true);
        update_option('wdtSeparateCon', $connections);
    }

    /**
     * Return left/right quote for table based on vendor.
     *
     * @param string $vendor of the connection
     *
     * @return string|null
     */
    public function getTableLeftRightQuote($vendor): ?string
    {
        if ($vendor === self::MYSQL) {
            return '`';
        }

        if ($vendor === self::MSSQL || $vendor === self::POSTGRESQL) {
            return '"';
        }

        return null;
    }

    /**
     * Return left quote for table column based on vendor.
     *
     * @param string $vendor of the connection
     *
     * @return string|null
     */
    public function getLeftColumnQuote($vendor): ?string
    {
        if ($vendor === self::MYSQL) {
            return '`';
        }

        if ($vendor === self::MSSQL) {
            return '[';
        }

        if ($vendor === self::POSTGRESQL) {
            return '"';
        }

        return null;
    }

    /**
     * Return right quote for table column based on vendor.
     *
     * @param string $vendor of the connection
     *
     * @return string|null
     */
    public function getRightColumnQuote($vendor): ?string
    {
        if ($vendor === self::MYSQL) {
            return '`';
        }

        if ($vendor === self::MSSQL) {
            return ']';
        }

        if ($vendor === self::POSTGRESQL) {
            return '"';
        }

        return null;
    }

    /**
     * Quote an SQL identifier, including schema-qualified names.
     *
     * @param string $identifier Table or column identifier.
     * @param string $vendor     Connection vendor.
     *
     * @return string
     */
    public function quoteQualifiedIdentifier($identifier, $vendor): string
    {
        if ($identifier === '' || $identifier === null) {
            return '';
        }

        $leftQuote = $this->getLeftColumnQuote($vendor);
        $rightQuote = $this->getRightColumnQuote($vendor);
        $quotedParts = array();

        foreach (explode('.', $identifier) as $part) {
            $quotedParts[] = $leftQuote . $part . $rightQuote;
        }

        return implode('.', $quotedParts);
    }
}
