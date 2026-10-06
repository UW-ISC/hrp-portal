<?php

namespace WDTIntegration\Webhooks;

defined('ABSPATH') or die('Access denied.');

/**
 * Data access for outbound webhooks.
 */
class WebhookRepository
{
    /**
     * @return string
     */
    private function tableName()
    {
        return WebhooksTable::getTableName();
    }

    /**
     * @param int $id
     * @return array<string, mixed>|null
     */
    public function findById($id)
    {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . $this->tableName() . ' WHERE id = %d',
                (int) $id
            ),
            ARRAY_A
        );

        return $row ? $this->hydrate($row) : null;
    }

    /**
     * @param int $tableId
     * @return array<int, array<string, mixed>>
     */
    public function findByTableId($tableId)
    {
        global $wpdb;

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM ' . $this->tableName() . ' WHERE table_id = %d ORDER BY id DESC',
                (int) $tableId
            ),
            ARRAY_A
        );

        if (empty($rows)) {
            return array();
        }

        return array_map(array($this, 'hydrate'), $rows);
    }

    /**
     * @param int $tableId
     * @param string $event
     * @return array<int, array<string, mixed>>
     */
    public function findEnabledByTableIdAndEvent($tableId, $event)
    {
        global $wpdb;

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM ' . $this->tableName() .
                ' WHERE table_id = %d AND event = %s AND enabled = 1 ORDER BY id ASC',
                (int) $tableId,
                $event
            ),
            ARRAY_A
        );

        if (empty($rows)) {
            return array();
        }

        return array_map(array($this, 'hydrate'), $rows);
    }

    /**
     * @param array<string, mixed> $data
     * @return int Insert ID
     */
    public function create(array $data)
    {
        global $wpdb;

        $now = current_time('mysql');
        $wpdb->insert(
            $this->tableName(),
            array(
                'table_id' => (int) $data['table_id'],
                'name' => $data['name'],
                'url' => $data['url'],
                'method' => $data['method'],
                'format' => $data['format'],
                'headers' => $data['headers'],
                'secret' => isset($data['secret']) ? $data['secret'] : '',
                'event' => $data['event'],
                'enabled' => !empty($data['enabled']) ? 1 : 0,
                'created_at' => $now,
                'updated_at' => $now,
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s')
        );

        return (int) $wpdb->insert_id;
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     * @return bool
     */
    public function update($id, array $data)
    {
        global $wpdb;

        $fields = array(
            'name' => $data['name'],
            'url' => $data['url'],
            'method' => $data['method'],
            'format' => $data['format'],
            'headers' => $data['headers'],
            'event' => $data['event'],
            'enabled' => !empty($data['enabled']) ? 1 : 0,
            'updated_at' => current_time('mysql'),
        );
        $formats = array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s');

        if (array_key_exists('secret', $data)) {
            $fields['secret'] = $data['secret'];
            $formats[] = '%s';
        }

        $result = $wpdb->update(
            $this->tableName(),
            $fields,
            array('id' => (int) $id),
            $formats,
            array('%d')
        );

        return $result !== false;
    }

    /**
     * @param int $id
     * @param bool $enabled
     * @return bool
     */
    public function updateStatus($id, $enabled)
    {
        global $wpdb;

        $result = $wpdb->update(
            $this->tableName(),
            array(
                'enabled' => $enabled ? 1 : 0,
                'updated_at' => current_time('mysql'),
            ),
            array('id' => (int) $id),
            array('%d', '%s'),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateLastRun($id, $status)
    {
        global $wpdb;

        $result = $wpdb->update(
            $this->tableName(),
            array(
                'last_status' => $status,
                'last_run_at' => current_time('mysql'),
            ),
            array('id' => (int) $id),
            array('%s', '%s'),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        global $wpdb;

        $result = $wpdb->delete(
            $this->tableName(),
            array('id' => (int) $id),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * @param int $tableId
     * @return bool
     */
    public function deleteByTableId($tableId)
    {
        global $wpdb;

        $result = $wpdb->delete(
            $this->tableName(),
            array('table_id' => (int) $tableId),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function hydrate(array $row)
    {
        $headers = array();
        if (!empty($row['headers'])) {
            $decoded = json_decode($row['headers'], true);
            if (is_array($decoded)) {
                $headers = $decoded;
            }
        }

        $row['id'] = (int) $row['id'];
        $row['table_id'] = (int) $row['table_id'];
        $row['enabled'] = (int) $row['enabled'] === 1;
        $row['headers'] = $headers;
        $row['has_secret'] = $row['secret'] !== '';

        return $row;
    }
}
