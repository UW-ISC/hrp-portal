<?php

/**
 * Serializes mcp-adapter session user-meta read-modify-write.
 *
 * @package wpDataTables
 */

namespace WDTMCP\Infrastructure\WP\MCP;

defined('ABSPATH') or die('Access denied.');

/**
 * mcp-adapter stores every session for a user under one `mcp_adapter_sessions`
 * user meta key and replaces the whole array on each write. Concurrent
 * initialize requests from this plugin and any other MCP plugin that shares
 * that key drop each other's sessions (-32005 / HTTP 404).
 *
 * WordPress user meta has no atomic RMW, so this lock wraps the first read of
 * that key in MySQL GET_LOCK and holds it until shutdown of the same request.
 * Other MCP plugins keep their own SessionManager class; they still go through
 * this metadata filter.
 */
class WdtmcpSessionMetaLock
{
    private const META_KEY = 'mcp_adapter_sessions';

    private const LOCK_TIMEOUT_SECONDS = 8;

    /**
     * Map of user ID => lock name held on this request's DB connection.
     *
     * @var array<int, string>
     */
    private static $held = array();

    /**
     * Hook metadata reads so session RMW is serialized across concurrent MCP requests.
     *
     * @return void
     */
    public static function init(): void
    {
        add_filter('get_user_metadata', array(self::class, 'onGetUserMeta'), 0, 4);
        add_action('shutdown', array(self::class, 'releaseAll'), 0);
    }

    /**
     * Acquire the per-user session lock before SessionManager reads the array.
     *
     * Returning the incoming $check (null) leaves the normal get_user_meta path intact.
     *
     * @param mixed $check Short-circuit value from earlier filters.
     * @param int   $object_id User ID.
     * @param string $meta_key Meta key being read.
     * @param bool  $single Whether a single value was requested.
     * @return mixed
     */
    public static function onGetUserMeta($check, $object_id, $meta_key, $single)
    {
        if (self::META_KEY !== $meta_key) {
            return $check;
        }

        self::acquire((int) $object_id);

        return $check;
    }

    /**
     * Release every lock taken on this request.
     *
     * @return void
     */
    public static function releaseAll(): void
    {
        if (empty(self::$held)) {
            return;
        }

        global $wpdb;

        if (!$wpdb instanceof \wpdb) {
            self::$held = array();

            return;
        }

        foreach (self::$held as $lock_name) {
            $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock_name));
        }

        self::$held = array();
    }

    /**
     * @param int $user_id User ID whose session array is being read.
     * @return void
     */
    private static function acquire(int $user_id): void
    {
        if ($user_id <= 0 || isset(self::$held[$user_id])) {
            return;
        }

        global $wpdb;

        if (!$wpdb instanceof \wpdb) {
            return;
        }

        $lock_name = self::lockName($user_id);
        $result = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT GET_LOCK(%s, %d)',
                $lock_name,
                self::LOCK_TIMEOUT_SECONDS
            )
        );

        // 1 = acquired. 0 / null = timeout or error; fail open and let JS retry.
        if ('1' === (string) $result) {
            self::$held[$user_id] = $lock_name;
            // wp_set_current_user() already primed user_meta. Without this
            // delete, the locked reader still writes a stale session array
            // and drops sessions created while it was waiting.
            wp_cache_delete($user_id, 'user_meta');
        }
    }

    /**
     * @param int $user_id User ID.
     * @return string
     */
    private static function lockName(int $user_id): string
    {
        return 'wdt_mcp_sessions_' . $user_id;
    }
}
