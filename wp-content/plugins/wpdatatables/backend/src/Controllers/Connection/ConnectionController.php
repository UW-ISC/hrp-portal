<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Connection;

use Connection;
use wpDataTableConstructor;
use WPDataTables\Services\Tools\ToolsService;
use Exception;

/**
 * ConnectionController — admin-ajax handlers for the separate-database
 * connection domain (test a separate connection, list its tables, parse a
 * server name into domain/subdomain).
 *
 * The nonce/cap checks and the wire format ($_POST in, `echo`/`exit` out) keep
 * the existing admin JS unaffected. The connection logic flows through the
 * global `Connection` facade (a shim into
 * {@see \WPDataTables\Services\Connection\ConnectionService} /
 * {@see \WPDataTables\Services\Connection\ConnectionFactory}). The legacy global
 * functions remain as one-line delegators into this controller.
 *
 * Plain class by design (NOT extending the REST-shaped
 * {@see \WPDataTables\Controllers\Controller}).
 *
 * @package WPDataTables\Controllers\Connection
 */
class ConnectionController
{
    /**
     * Test the Separate connection settings.
     *
     * @return void
     */
    public function testSeparateConnectionSettings()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')) {
            exit();
        }

        $returnArray = array('success' => array(), 'errors' => array());

        $connections = Connection::getAll();

        foreach ($_POST['wdtSeparateCon'] as $separateConnection) {

            //Sanitization
            $separateConnection['host'] = sanitize_text_field($separateConnection['host']);
            $separateConnection['database'] = sanitize_text_field($separateConnection['database']);
            $separateConnection['user'] = sanitize_text_field($separateConnection['user']);
            $separateConnection['port'] = (int)($separateConnection['port']);
            $separateConnection['vendor'] = sanitize_text_field($separateConnection['vendor']);
            $separateConnection['driver'] = sanitize_text_field($separateConnection['driver']);

            try {
                $Sql = Connection::create(
                    '',
                    $separateConnection['host'],
                    $separateConnection['database'],
                    $separateConnection['user'],
                    $separateConnection['password'],
                    $separateConnection['port'],
                    $separateConnection['vendor'],
                    $separateConnection['driver']
                );
                if ($Sql->isConnected()) {
                    $returnArray['success'][] = __("Successfully connected to the {$separateConnection['vendor']} server.", 'wpdatatables');

                    $isNewConnection = true;

                    foreach ($connections as &$connection) {
                        if ($connection['id'] === $separateConnection['id']) {
                            $isNewConnection = false;

                            $connection = $separateConnection;
                        }
                    }

                    if ($isNewConnection) {
                        $connections[] = $separateConnection;
                    }
                } else {
                    $returnArray['errors'][] = __("wpDataTables could not connect to {$separateConnection['vendor']} server.", 'wpdatatables');
                }
            } catch (Exception $e) {
                $returnArray['errors'][] = __("wpDataTables could not connect to {$separateConnection['vendor']} server. {$separateConnection['vendor']} said: ", 'wpdatatables') . $e->getMessage();
            }
        }

        if (!$returnArray['errors']) {
            Connection::saveAll(json_encode($connections));
        }

        echo json_encode($returnArray);
        exit();
    }

    /**
     * Get connection tables settings.
     *
     * @return void
     */
    public function getConnectionTables()
    {
        if (!current_user_can('manage_options') ||
            !(wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')
                || wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')
                || wp_verify_nonce($_POST['wdtNonce'], 'wdtNonce'))) {
            exit();
        }
        $connection = $_POST['connection'];

        $tables = wpDataTableConstructor::listMySQLTables($connection);

        echo json_encode($tables);
        exit();
    }

    /**
     * Parse a server name into its domain / subdomain components.
     *
     * @return void
     */
    public function parseServerName()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')) {
            exit();
        }
        /** @var array $serverName */
        $serverName['domain'] = sanitize_text_field($_POST['domain']);
        $serverName['domain'] = ToolsService::getDomain($serverName['domain']);
        $serverName['subdomain'] = sanitize_text_field($_POST['subdomain']);
        $serverName['subdomain'] = ToolsService::getSubDomain($serverName['subdomain']);

        echo json_encode($serverName);

        exit();

    }
}
